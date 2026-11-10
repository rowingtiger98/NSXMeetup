<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  AGENDA EVENTS                                             */
/*                                                             */
/*  Admin tool for adding, editing and deleting an event's     */
/*  agenda events (nsx_meetup_experiences) - the agenda       */
/*  shown on the event's page. Every request must come from a  */
/*  logged-in admin of the event (or an overlord), and changes */
/*  must carry the session's CSRF token. Only the methods      */
/*  listed below can be called.                                */
/*                                                             */
/*  The list and forms use the Registration Types tool's       */
/*  styles (regtype_* classes in nsxmeetup.css).               */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/eventAdmin.php';
require_once __DIR__ . '/eventImages.php';  // EventExperienceBannerDirectory(), ExperienceBannerURL(), CheckFrontpageImageUpload(), SaveFrontpageImage()

app_session_start();

const EXPERIENCE_METHODS = ['RenderWeekendEvents', 'AddWeekendEvent', 'SaveWeekendEvent', 'SetWeekendEventDisplay', 'DeleteWeekendEvent'];

const EXPERIENCE_TITLE_MAX    = 256;  // EXPERIENCE_Title is varchar(256)
const EXPERIENCE_NAME_MAX     = 32;   // EXPERIENCE_Name is varchar(32) - a short key for the agenda's HTML ids, made from the title
const EXPERIENCE_PHONE_MAX    = 32;
const EXPERIENCE_EMAIL_MAX    = 64;
const EXPERIENCE_LOCATION_MAX = 128;
const EXPERIENCE_BANNER_MAX_WIDTH  = 1200;  // banners are scaled down to fit 1200 x 800
const EXPERIENCE_BANNER_MAX_HEIGHT = 800;

if (isset($_POST['method']) && in_array($_POST['method'], EXPERIENCE_METHODS, true)) {
    $_POST['method']();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // PHP throws away the whole request (method included) when it's over post_max_size
    EventAdminRespond('Fail', ['The banner image is too large for the server to accept (limit '.ini_get('post_max_size').'). Please choose a smaller one.']);
}

/* ************************************************************************ */
/* Function:    ExperienceLoadEvent                                         */
/* Description: Loads and authorizes the posted event like                  */
/*              EventAdminLoadEvent, and for changes also checks the CSRF   */
/*              token.                                                      */
/* Inputs:      $is_change - true for requests that change data.            */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function ExperienceLoadEvent($is_change) {
    list($pdo, $event) = EventAdminLoadEvent();
    if ($is_change && !app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    return [$pdo, $event];
}

/* ************************************************************************ */
/* Function:    ExperiencePosted                                            */
/* Description: The posted agenda event's row, if it belongs to the event. */
/*              Ends the request otherwise.                                 */
/* Returns:     array                                                       */
/* ************************************************************************ */
function ExperiencePosted(PDO $pdo, $event_id) {
    $experience_id = filter_var($_POST['experience_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($experience_id !== false) {
        $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_experiences WHERE EXPERIENCE_ID = ? AND EVENT_ID = ?');
        $stmt->execute([$experience_id, $event_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }
    EventAdminRespond('Fail', ['That agenda event could not be found. Reload the page and try again.']);
}

/* ************************************************************************ */
/* Function:    ExperiencePostedValues                                      */
/* Description: Validates the posted title, date, times, description and    */
/*              contact details. The date must fall within the event's      */
/*              dates (the agenda only shows those days).                   */
/* Returns:     [values, errors]                                            */
/* ************************************************************************ */
function ExperiencePostedValues(array $event) {
    $text = function ($key) { return trim(is_string($_POST[$key] ?? null) ? $_POST[$key] : ''); };
    $values = [
        'title'       => $text('title'),
        'date'        => $text('date'),
        'start_time'  => $text('start_time'),
        'stop_time'   => $text('stop_time'),
        'description' => $text('description'),
        'location'    => $text('location'),
        'phone'       => $text('phone'),
        'email'       => $text('email'),
        'weblink'     => $text('weblink'),
    ];
    $errors = [];

    if ($values['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($values['title']) > EXPERIENCE_TITLE_MAX) {
        $errors[] = 'Title must be '.EXPERIENCE_TITLE_MAX.' characters or fewer.';
    }

    $date = DateTime::createFromFormat('!Y-m-d', $values['date']);
    if (!$date || $date->format('Y-m-d') !== $values['date']) {
        $errors[] = 'Please choose the date.';
    } elseif (!empty($event['EVENT_Start_Date']) && !empty($event['EVENT_End_Date'])
              && ($values['date'] < $event['EVENT_Start_Date'] || $values['date'] > $event['EVENT_End_Date'])) {
        $errors[] = 'The date must be during the event ('.date('M j', strtotime($event['EVENT_Start_Date'])).' - '.date('M j, Y', strtotime($event['EVENT_End_Date'])).').';
    }

    foreach (['start_time' => 'Start time', 'stop_time' => 'End time'] as $key => $label) {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $values[$key])) {
            $errors[] = 'Please enter the '.strtolower($label).'.';
        } else {
            $values[$key] = substr($values[$key], 0, 5).':00';
        }
    }
    if (strlen($values['start_time']) === 8 && strlen($values['stop_time']) === 8 && $values['stop_time'] <= $values['start_time']) {
        $errors[] = 'The end time must be after the start time.';
    }

    if (mb_strlen($values['location']) > EXPERIENCE_LOCATION_MAX) {
        $errors[] = 'Location must be '.EXPERIENCE_LOCATION_MAX.' characters or fewer.';
    }
    if (mb_strlen($values['phone']) > EXPERIENCE_PHONE_MAX) {
        $errors[] = 'Phone must be '.EXPERIENCE_PHONE_MAX.' characters or fewer.';
    }
    if ($values['email'] !== '' && (mb_strlen($values['email']) > EXPERIENCE_EMAIL_MAX || !filter_var($values['email'], FILTER_VALIDATE_EMAIL))) {
        $errors[] = 'Please enter a valid email address of up to '.EXPERIENCE_EMAIL_MAX.' characters, or leave it blank.';
    }
    if ($values['weblink'] !== '' && (!filter_var($values['weblink'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $values['weblink']))) {
        $errors[] = 'The web page must be a full link starting with http:// or https://, or left blank.';
    }

    foreach (['description', 'location', 'phone', 'email', 'weblink'] as $key) {
        if ($values[$key] === '') {
            $values[$key] = null;
        }
    }
    return [$values, $errors];
}

/* ************************************************************************ */
/* Function:    ExperienceUniqueName                                        */
/* Description: A short key for a new agenda event, made from its title    */
/*              (e.g. "golf-at-bella-collina"), unique within the event.    */
/*              The agenda uses it in HTML ids.                             */
/* Returns:     string                                                      */
/* ************************************************************************ */
function ExperienceUniqueName(PDO $pdo, $event_id, $title) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    $base = substr($base !== '' ? $base : 'event', 0, EXPERIENCE_NAME_MAX - 3);
    $base = rtrim($base, '-');

    $stmt = $pdo->prepare('SELECT EXPERIENCE_Name FROM nsx_meetup_experiences WHERE EVENT_ID = ?');
    $stmt->execute([$event_id]);
    $taken = array_map('strtolower', array_filter($stmt->fetchAll(PDO::FETCH_COLUMN), 'is_string'));

    $name = $base;
    for ($n = 2; in_array($name, $taken, true); $n++) {
        $name = $base.'-'.$n;
    }
    return $name;
}

/* ************************************************************************ */
/* Function:    ExperienceBannerUpload                                      */
/* Description: Checks the posted banner (if any) and makes sure the        */
/*              event's experiences folder exists. Ends the request with a  */
/*              'Fail' response if either isn't usable.                     */
/* Returns:     null when no banner was posted, else the upload             */
/*              (CheckFrontpageImageUpload) plus 'dir' - the site-relative  */
/*              folder it goes in.                                          */
/* ************************************************************************ */
function ExperienceBannerUpload(array $event) {
    if (($_FILES['banner']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!function_exists('imagecreatetruecolor')) {
        error_log('ExperienceBannerUpload: the GD extension is not available, so banners cannot be resized');
        EventAdminRespond('Fail', ['Banner uploads are not available on this server (the PHP GD extension is missing).']);
    }
    $banner = CheckFrontpageImageUpload($_FILES['banner']);
    if ($banner['error'] !== '') {
        EventAdminRespond('Fail', [$banner['error']]);
    }
    $dir = EventExperienceBannerDirectory($event['EVENT_Directory']);
    if ($dir === '' || !is_dir(__DIR__.'/events/'.$event['EVENT_Directory'])) {
        EventAdminRespond('Fail', ['This event\'s folder (events/'.$event['EVENT_Directory'].'/) could not be found, so the banner has nowhere to go.']);
    }
    if (!is_dir(__DIR__.'/'.$dir) && !mkdir(__DIR__.'/'.$dir, 0755, true)) {
        error_log('ExperienceBannerUpload: could not create '.$dir);
        EventAdminRespond('Fail', ['The banner could not be saved. Please try again.']);
    }
    return $banner + ['dir' => $dir];
}

/* ************************************************************************ */
/* Function:    SaveExperienceBanner                                        */
/* Description: Saves a checked banner (ExperienceBannerUpload) for a       */
/*              agenda event as experience_<id>_<date>_<time>, scaled to   */
/*              fit EXPERIENCE_BANNER_MAX_WIDTH x _HEIGHT.                  */
/* Returns:     the site-relative path, or '' on failure                    */
/* ************************************************************************ */
function SaveExperienceBanner(array $banner, $experience_id) {
    $filename = SaveFrontpageImage($banner['tmp'], $banner['type'], __DIR__.'/'.$banner['dir'], 'experience_'.(int)$experience_id.'_'.date('Ymd_His'),
                                   EXPERIENCE_BANNER_MAX_HEIGHT, EXPERIENCE_BANNER_MAX_WIDTH);
    return ($filename === '') ? '' : $banner['dir'].'/'.$filename;
}

/* ************************************************************************ */
/* Function:    DeleteExperienceBanner                                      */
/* Description: Deletes a agenda event's banner file - only an upload made */
/*              by this tool for that agenda event, never a shared image.  */
/* ************************************************************************ */
function DeleteExperienceBanner($path, $experience_id) {
    $path = (string)$path;
    if (preg_match('#^events/[A-Za-z0-9_-]+/images/experiences/experience_'.(int)$experience_id.'_\d{8}_\d{6}\.(jpg|png)$#', $path) && is_file(__DIR__.'/'.$path)) {
        unlink(__DIR__.'/'.$path);
    }
}

/* ************************************************************************ */
/* Function:    RenderExperienceFields                                      */
/* Description: The inputs for one agenda event (or the empty Add form).   */
/* Inputs:      $prefix - makes the input ids unique on the page.           */
/*              $event - the event (its dates limit the date picker).       */
/*              $with_display - include the Show checkbox (Add form).       */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderExperienceFields($prefix, array $event, $with_display) {
    $date_limits = (!empty($event['EVENT_Start_Date']) ? ' min="'.htmlspecialchars($event['EVENT_Start_Date'], ENT_QUOTES).'"' : '')
                  .(!empty($event['EVENT_End_Date']) ? ' max="'.htmlspecialchars($event['EVENT_End_Date'], ENT_QUOTES).'"' : '');
    $field = function ($cols, $key, $label, $input) use ($prefix) {
        return '   <div class="'.$cols.'"><label for="'.$prefix.'_'.$key.'">'.$label.'</label>'.$input.'</div>';
    };
    $text = function ($key, $max, $placeholder, $type = 'text') use ($prefix) {
        return '<input type="'.$type.'" id="'.$prefix.'_'.$key.'" experience_field="'.$key.'"'.($max ? ' maxlength="'.$max.'"' : '').' placeholder="'.$placeholder.'" />';
    };

    $output_html  = '<div class="row gtr-25 gtr-uniform">';
    $output_html .= $field('col-12', 'title', 'Title', $text('title', EXPERIENCE_TITLE_MAX, 'e.g. Golf at Bella Collina'));
    $output_html .= $field('col-4 col-12-small', 'date', 'Date', '<input type="date" id="'.$prefix.'_date" experience_field="date"'.$date_limits.' />');
    $output_html .= $field('col-4 col-12-small', 'start_time', 'Start Time', '<input type="time" id="'.$prefix.'_start_time" experience_field="start_time" />');
    $output_html .= $field('col-4 col-12-small', 'stop_time', 'End Time', '<input type="time" id="'.$prefix.'_stop_time" experience_field="stop_time" />');
    $output_html .= $field('col-12', 'description', 'Description', '<textarea id="'.$prefix.'_description" experience_field="description" rows="4" placeholder="What the event is and anything people should know. HTML is allowed."></textarea>');
    $output_html .= $field('col-6 col-12-small', 'location', 'Location / Address', $text('location', EXPERIENCE_LOCATION_MAX, 'e.g. 16300 Bella Collina Blvd, Montverde, FL'));
    $output_html .= $field('col-6 col-12-small', 'weblink', 'Web Page', $text('weblink', 0, 'https://...', 'url'));
    $output_html .= $field('col-6 col-12-small', 'phone', 'Phone', $text('phone', EXPERIENCE_PHONE_MAX, 'Optional', 'tel'));
    $output_html .= $field('col-6 col-12-small', 'email', 'Email', $text('email', EXPERIENCE_EMAIL_MAX, 'Optional', 'email'));
    $output_html .= '   <div class="col-12">';
    $output_html .= '       <label for="'.$prefix.'_banner">Banner Image</label>';
    $output_html .= '       <div class="experience_banner_preview"></div>';
    $output_html .= '       <input type="file" id="'.$prefix.'_banner" experience_file="banner" accept="image/jpeg,image/png,image/webp" />';
    $output_html .= '       <p class="experience_field_help">Optional. JPEG, PNG or WebP, under '.(FRONTPAGE_IMAGE_MAX_BYTES / 1048576).' MB. Larger images are scaled down to fit within '.EXPERIENCE_BANNER_MAX_WIDTH.' x '.EXPERIENCE_BANNER_MAX_HEIGHT.' pixels.</p>';
    if (!$with_display) {
        $output_html .= '       <div class="experience_remove_banner"><input type="checkbox" id="'.$prefix.'_remove_banner" experience_field="remove_banner" />';
        $output_html .= '       <label for="'.$prefix.'_remove_banner">Remove the current banner</label></div>';
    }
    $output_html .= '   </div>';
    if ($with_display) {
        $output_html .= '   <div class="col-12">';
        $output_html .= '       <input type="checkbox" id="'.$prefix.'_display" experience_field="display" checked />';
        $output_html .= '       <label for="'.$prefix.'_display">Show this event on the agenda</label>';
        $output_html .= '   </div>';
    }
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    ExperienceText                                              */
/* Description: A stored value as UTF-8. Older rows can hold Windows-1252   */
/*              text (curly quotes, accents), which htmlspecialchars would  */
/*              turn into an empty string, so it's converted first.         */
/* Returns:     string                                                      */
/* ************************************************************************ */
function ExperienceText($s) {
    $s = (string)$s;
    if (function_exists('mb_check_encoding') && !mb_check_encoding($s, 'UTF-8')) {
        $s = mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
    }
    return $s;
}

/* ************************************************************************ */
/* Function:    ExperienceDate                                              */
/* Description: A stored date, or '' when it's blank or a zero date         */
/*              (0000-00-00) from an older row.                             */
/* Returns:     string (YYYY-MM-DD) or ''                                   */
/* ************************************************************************ */
function ExperienceDate($date) {
    $date = substr(trim((string)$date), 0, 10);
    return (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strpos($date, '0000') !== 0 && strtotime($date) !== false) ? $date : '';
}

/* ************************************************************************ */
/* Function:    ExperienceTimeLabel                                         */
/* Description: A stored time as shown in the list, e.g. 1:30 PM.          */
/* ************************************************************************ */
function ExperienceTimeLabel($time) {
    return ($time === null || $time === '') ? '' : date('g:i A', strtotime($time));
}

/* ************************************************************************ */
/* Function:    RenderExperienceList                                        */
/* Description: The event's agenda events as a table in agenda order: a    */
/*              Show checkbox (saved as soon as it's changed), date, times, */
/*              title, location, and Edit / Delete. Each row carries its    */
/*              full values for the Edit modal.                             */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderExperienceList(PDO $pdo, $event_id) {
    $h = function ($s) { return htmlspecialchars(ExperienceText($s), ENT_QUOTES); };
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_experiences WHERE EVENT_ID = ? ORDER BY EXPERIENCE_Date, EXPERIENCE_Start_Time, EXPERIENCE_Title');
    $stmt->execute([$event_id]);
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$experiences) {
        return '<p>This event has no agenda events yet. Add one above.</p>';
    }

    $output_html  = '<div class="table-wrapper">';
    $output_html .= '<table class="regtype_table">';
    $output_html .= '<thead><tr><th>Show</th><th>Date</th><th>Time</th><th>Title</th><th>Location</th><th></th></tr></thead>';
    $output_html .= '<tbody>';
    foreach ($experiences as $row) {
        $id = (int)$row['EXPERIENCE_ID'];
        $banner = trim((string)$row['EXPERIENCE_Banner_Image']);
        $output_html .= '<tr class="experience_row" experience_id="'.$id.'"'
                       .' experience_title="'.$h($row['EXPERIENCE_Title']).'"'
                       .' experience_date="'.$h(ExperienceDate($row['EXPERIENCE_Date'])).'"'
                       .' experience_start_time="'.$h(substr((string)$row['EXPERIENCE_Start_Time'], 0, 5)).'"'
                       .' experience_stop_time="'.$h(substr((string)$row['EXPERIENCE_Stop_Time'], 0, 5)).'"'
                       .' experience_description="'.$h($row['EXPERIENCE_Description']).'"'
                       .' experience_location="'.$h($row['EXPERIENCE_Location']).'"'
                       .' experience_weblink="'.$h($row['EXPERIENCE_weblink']).'"'
                       .' experience_phone="'.$h($row['EXPERIENCE_Phone']).'"'
                       .' experience_email="'.$h($row['EXPERIENCE_Email']).'"'
                       .' experience_banner_url="'.($banner !== '' ? $h(ExperienceBannerURL($banner)) : '').'">';
        $output_html .= '   <td><input type="checkbox" id="experience_'.$id.'_display" class="experience_display_checkbox"'.(!empty($row['EXPERIENCE_Display']) ? ' checked' : '').' />';
        $output_html .= '       <label for="experience_'.$id.'_display" class="regtype_checkbox_label" title="Show or hide '.$h($row['EXPERIENCE_Title']).' on the agenda"></label></td>';
        $output_html .= '   <td>'.(ExperienceDate($row['EXPERIENCE_Date']) !== '' ? $h(date('D, M j', strtotime($row['EXPERIENCE_Date']))) : 'No date').'</td>';
        $output_html .= '   <td>'.$h(ExperienceTimeLabel($row['EXPERIENCE_Start_Time'])).($row['EXPERIENCE_Stop_Time'] ? ' - '.$h(ExperienceTimeLabel($row['EXPERIENCE_Stop_Time'])) : '').'</td>';
        $output_html .= '   <td>'.$h($row['EXPERIENCE_Title']).'</td>';
        $output_html .= '   <td>'.$h($row['EXPERIENCE_Location']).'</td>';
        $output_html .= '   <td class="regtype_actions"><a href="#" class="button small experience_edit_button">Edit</a> <a href="#" class="button small experience_delete_button">Delete</a></td>';
        $output_html .= '</tr>';
    }
    $output_html .= '</tbody></table>';
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderWeekendEvents                                         */
/* Description: Returns the Agenda Events tool for the posted event: an    */
/*              Add form and the list of current agenda events.            */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderWeekendEvents() {
    list($pdo, $event) = ExperienceLoadEvent(false);
    $h = function ($s) { return htmlspecialchars(ExperienceText($s), ENT_QUOTES); };

    $output_html  = '<h2>'.$h($event['EVENT_Name']).' Agenda Events</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<p>These make up the agenda on the event\'s page, grouped by day and in time order. Untick Show to hide an event without deleting it.</p>';
    if (empty($event['EVENT_Start_Date']) || empty($event['EVENT_End_Date'])) {
        $output_html .= '<p class="event_settings_error">This event doesn\'t have its start and end dates set, so the agenda can\'t show any days yet. Set them in Edit Event Settings.</p>';
    }
    $output_html .= '<div id="experience_tool" event_id="'.(int)$event['EVENT_ID'].'" csrf_token="'.$h(app_csrf_token()).'">';

    $output_html .= '<h3>Add a Agenda Event</h3>';
    $output_html .= '<div id="experience_add" class="regtype_item">';
    $output_html .= RenderExperienceFields('experience_new', $event, true);
    $output_html .= '   <div class="regtype_messages"></div>';
    $output_html .= '   <a href="#" id="experience_add_button" class="button primary small fit">Add Agenda Event</a>';
    $output_html .= '</div>';

    $output_html .= '<h3 style="margin-top:2em;">Current Agenda Events</h3>';
    $output_html .= '<div id="experience_list_messages" class="regtype_messages"></div>';
    $output_html .= '<div id="experience_list">'.RenderExperienceList($pdo, $event['EVENT_ID']).'</div>';

    /* Edit modal - filled from the row's values by OpenWeekendEventEdit (experiences.js) */
    $output_html .= '<div id="experience_edit_modal" class="modal regtype_modal">';
    $output_html .= '   <div class="modal-content">';
    $output_html .= '       <span class="modal-close experience_cancel_button" title="Close">&times;</span>';
    $output_html .= '       <h3>Edit Agenda Event</h3>';
    $output_html .= '       <div id="experience_edit" class="regtype_item" experience_id="">';
    $output_html .= RenderExperienceFields('experience_edit', $event, false);
    $output_html .= '           <div class="regtype_messages"></div>';
    $output_html .= '           <div class="row gtr-25 gtr-uniform">';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button primary small fit experience_save_button">Save</a></div>';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button small fit experience_cancel_button">Cancel</a></div>';
    $output_html .= '           </div>';
    $output_html .= '       </div>';
    $output_html .= '   </div>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    AddWeekendEvent                                             */
/* Description: Adds a agenda event (and its banner, if one was posted) to */
/*              the posted event.                                           */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function AddWeekendEvent() {
    list($pdo, $event) = ExperienceLoadEvent(true);
    list($values, $errors) = ExperiencePostedValues($event);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }
    $banner = ExperienceBannerUpload($event);
    $display = (($_POST['display'] ?? '1') === '0') ? 0 : 1;

    $saved_banner = '';
    try {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO nsx_meetup_experiences (EVENT_ID, EXPERIENCE_Display, EXPERIENCE_Title, EXPERIENCE_Name, EXPERIENCE_Description, EXPERIENCE_Date, EXPERIENCE_Start_Time, EXPERIENCE_Stop_Time, EXPERIENCE_Phone, EXPERIENCE_Email, EXPERIENCE_Location, EXPERIENCE_weblink) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$event['EVENT_ID'], $display, $values['title'], ExperienceUniqueName($pdo, $event['EVENT_ID'], $values['title']), $values['description'],
                       $values['date'], $values['start_time'], $values['stop_time'], $values['phone'], $values['email'], $values['location'], $values['weblink']]);
        $experience_id = (int)$pdo->lastInsertId();

        if ($banner !== null) {
            $saved_banner = SaveExperienceBanner($banner, $experience_id);
            if ($saved_banner === '') {
                throw new RuntimeException('saving the banner failed');
            }
            $pdo->prepare('UPDATE nsx_meetup_experiences SET EXPERIENCE_Banner_Image = ? WHERE EXPERIENCE_ID = ?')->execute([$saved_banner, $experience_id]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($saved_banner !== '' && is_file(__DIR__.'/'.$saved_banner)) {
            unlink(__DIR__.'/'.$saved_banner);
        }
        error_log('AddWeekendEvent failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The agenda event could not be added. Please try again.']);
    }

    EventAdminRespond('Pass', RenderExperienceList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SaveWeekendEvent                                            */
/* Description: Saves changes to one of the posted event's agenda events.  */
/*              A new banner replaces the old one; remove_banner = '1'      */
/*              clears it. The replaced banner file is deleted when this    */
/*              tool uploaded it.                                           */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function SaveWeekendEvent() {
    list($pdo, $event) = ExperienceLoadEvent(true);
    $row = ExperiencePosted($pdo, $event['EVENT_ID']);
    $experience_id = (int)$row['EXPERIENCE_ID'];
    list($values, $errors) = ExperiencePostedValues($event);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }
    $banner = ExperienceBannerUpload($event);

    $old_banner = (string)$row['EXPERIENCE_Banner_Image'];
    $new_banner = (($_POST['remove_banner'] ?? '') === '1') ? null : ($old_banner !== '' ? $old_banner : null);
    $saved_banner = '';
    if ($banner !== null) {
        $saved_banner = SaveExperienceBanner($banner, $experience_id);
        if ($saved_banner === '') {
            error_log('SaveWeekendEvent: saving the banner failed for agenda event '.$experience_id);
            EventAdminRespond('Fail', ['The banner could not be saved. Please try again.']);
        }
        $new_banner = $saved_banner;
    }

    try {
        $pdo->prepare('UPDATE nsx_meetup_experiences SET EXPERIENCE_Title = ?, EXPERIENCE_Description = ?, EXPERIENCE_Date = ?, EXPERIENCE_Start_Time = ?, EXPERIENCE_Stop_Time = ?, EXPERIENCE_Phone = ?, EXPERIENCE_Email = ?, EXPERIENCE_Location = ?, EXPERIENCE_weblink = ?, EXPERIENCE_Banner_Image = ? WHERE EXPERIENCE_ID = ? AND EVENT_ID = ?')
            ->execute([$values['title'], $values['description'], $values['date'], $values['start_time'], $values['stop_time'], $values['phone'], $values['email'],
                       $values['location'], $values['weblink'], $new_banner, $experience_id, $event['EVENT_ID']]);
    } catch (PDOException $e) {
        if ($saved_banner !== '' && is_file(__DIR__.'/'.$saved_banner)) {
            unlink(__DIR__.'/'.$saved_banner);
        }
        error_log('SaveWeekendEvent failed for agenda event '.$experience_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The agenda event could not be saved. Please try again.']);
    }
    if ($old_banner !== '' && $old_banner !== (string)$new_banner) {
        DeleteExperienceBanner($old_banner, $experience_id);
    }

    EventAdminRespond('Pass', RenderExperienceList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SetWeekendEventDisplay                                      */
/* Description: Shows or hides one of the posted event's agenda events on  */
/*              the agenda (posted display = '1' or '0').                   */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SetWeekendEventDisplay() {
    list($pdo, $event) = ExperienceLoadEvent(true);
    $row = ExperiencePosted($pdo, $event['EVENT_ID']);
    $display = (($_POST['display'] ?? '') === '1') ? 1 : 0;

    try {
        $pdo->prepare('UPDATE nsx_meetup_experiences SET EXPERIENCE_Display = ? WHERE EXPERIENCE_ID = ? AND EVENT_ID = ?')
            ->execute([$display, (int)$row['EXPERIENCE_ID'], $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('SetWeekendEventDisplay failed for agenda event '.$row['EXPERIENCE_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The change could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', $row['EXPERIENCE_Title'].($display ? ' is now shown on the agenda.' : ' is now hidden from the agenda.'));
}

/* ************************************************************************ */
/* Function:    DeleteWeekendEvent                                          */
/* Description: Deletes one of the posted event's agenda events, and its   */
/*              banner file when this tool uploaded it.                     */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function DeleteWeekendEvent() {
    list($pdo, $event) = ExperienceLoadEvent(true);
    $row = ExperiencePosted($pdo, $event['EVENT_ID']);

    try {
        $pdo->prepare('DELETE FROM nsx_meetup_experiences WHERE EXPERIENCE_ID = ? AND EVENT_ID = ?')->execute([(int)$row['EXPERIENCE_ID'], $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('DeleteWeekendEvent failed for agenda event '.$row['EXPERIENCE_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The agenda event could not be deleted. Please try again.']);
    }
    DeleteExperienceBanner($row['EXPERIENCE_Banner_Image'], $row['EXPERIENCE_ID']);

    EventAdminRespond('Pass', RenderExperienceList($pdo, $event['EVENT_ID']));
}
