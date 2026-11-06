<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT SETTINGS                                             */
/*                                                             */
/*  Admin tool for editing an existing event's settings        */
/*  (nsx_meetup_events). Every request must come from a        */
/*  logged-in user who administers the event (or is an         */
/*  overlord), and saves must carry the session's CSRF token.  */
/*  Unlike the older *Functions.php files, only the methods    */
/*  listed below can be called.                                */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/SessionBootstrap.php';
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventImages.php';

app_session_start();

const EVENT_SETTINGS_METHODS = ['RenderEditEventSettings', 'SaveEventSettings', 'UploadFrontpageImage'];

const FRONTPAGE_IMAGE_MAX_HEIGHT = 450;           // uploads are scaled down to this height, width in proportion
const FRONTPAGE_IMAGE_MAX_BYTES  = 10 * 1048576;  // 10 MB - keep in step with .user.ini and eventsettings.js
const FRONTPAGE_IMAGE_MAX_PIXELS = 24000000;      // 24 megapixels - larger images need too much memory to resize

if (isset($_POST['method']) && in_array($_POST['method'], EVENT_SETTINGS_METHODS, true)) {
    $_POST['method']();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // PHP throws away the whole request (method included) when it's over post_max_size
    EventSettingsRespond('Fail', ['The image is too large for the server to accept (limit '.ini_get('post_max_size').'). Please choose a smaller one.']);
}

/* ************************************************************************ */
/* Function:    EventSettingsFields                                         */
/* Description: The editable event columns, grouped into the sections shown */
/*              on the form. Drives both rendering and saving.              */
/*              type: text | textarea | html | date | money | percent |     */
/*                    checkbox | emails | image                             */
/*              null_ok: store NULL when left blank (column allows NULL)    */
/*              Each admin tool card edits its own set of fields:           */
/*              'settings' (Edit Event Settings) and 'payments' (Enable /   */
/*              Edit Event Payment Options).                                */
/* Inputs:      $tool - 'settings' or 'payments'.                           */
/* Returns:     array                                                       */
/* ************************************************************************ */
function EventSettingsFields($tool) {
    $tools = [];
    $tools['settings'] = [
        'General' => [
            'EVENT_Name'       => ['label' => 'Event Name', 'type' => 'text', 'max' => 256, 'required' => true,
                                   'help' => 'The name used throughout the site for this event.'],
            'EVENT_Start_Date' => ['label' => 'Start Date', 'type' => 'date', 'null_ok' => true,
                                   'help' => 'First day of the event.'],
            'EVENT_End_Date'   => ['label' => 'End Date', 'type' => 'date', 'null_ok' => true,
                                   'help' => 'Last day of the event. Must be on or after the start date.'],
        ],
        'Visibility & Registration' => [
            'EVENT_Display'                 => ['label' => 'Show on Front Page', 'type' => 'checkbox',
                                                'help' => 'List this event on the NSX Meetup front page.'],
            'EVENT_Sold_Out'                => ['label' => 'Sold Out', 'type' => 'checkbox',
                                                'help' => 'Mark the event as sold out on the registration page.'],
            'EVENT_Enable_Account_Creation' => ['label' => 'Offer Account Creation', 'type' => 'checkbox',
                                                'help' => 'Let people who register without logging in create an NSX Meetup account.'],
            'EVENT_Display_Sponsors'        => ['label' => 'Show Sponsors', 'type' => 'checkbox',
                                                'help' => 'Show the sponsors section on the event page.'],
        ],
        'Front Page & Event Page' => [
            'EVENT_Frontpage_Image'       => ['label' => 'Front Page Image', 'type' => 'image', 'max' => 255, 'null_ok' => true,
                                              'help' => 'Upload a new JPEG, PNG or WebP image (scaled to '.FRONTPAGE_IMAGE_MAX_HEIGHT.'px high and saved in the event\'s images folder), or enter the file name of an image already there or in the shared '.SHARED_FRONTPAGE_IMAGE_DIR.'/ folder.'],
            'EVENT_Frontpage_Description' => ['label' => 'Front Page Description', 'type' => 'html', 'null_ok' => true,
                                              'help' => 'Text shown with the event on the front page. HTML is allowed.'],
            'EVENT_Opening_Section'       => ['label' => 'Opening Section', 'type' => 'html', 'null_ok' => true,
                                              'help' => 'Opening text at the top of the event page. HTML is allowed.'],
            'EVENT_Confirmation_Special_Text' => ['label' => 'Confirmation Special Text', 'type' => 'text', 'max' => 256, 'null_ok' => true,
                                              'help' => 'Extra text included on registration confirmations.'],
        ],
        'Contacts' => [
            'EVENT_Primary_Email_Contact'   => ['label' => 'Primary Contact Email(s)', 'type' => 'emails', 'max' => 512, 'null_ok' => true,
                                                'help' => 'Where the event contact form sends messages. Separate multiple addresses with commas.'],
            'EVENT_Secondary_Email_Contact' => ['label' => 'Secondary Contact Email(s)', 'type' => 'emails', 'null_ok' => true,
                                                'help' => 'Copied on contact form messages. Separate multiple addresses with commas.'],
        ],
    ];
    $tools['payments'] = [
        'Fees & Payments' => [
            'EVENT_Base_Fee'              => ['label' => 'Base Fee ($)', 'type' => 'money',
                                              'help' => 'Registration fee per adult. Use 0 for a free event.'],
            'EVENT_Enable_Child_Discount' => ['label' => 'Enable Child Pricing', 'type' => 'checkbox',
                                              'help' => 'Offer a separate fee for children.'],
            'EVENT_Child_Base_Fee'        => ['label' => 'Child Fee ($)', 'type' => 'money', 'null_ok' => true,
                                              'help' => 'Registration fee per child, used when child pricing is enabled.'],
            'EVENT_Child_Checkbox_Text'   => ['label' => 'Child Checkbox Text', 'type' => 'text', 'max' => 128, 'null_ok' => true,
                                              'help' => 'Label for the "this person is a child" checkbox on the registration form.'],
            'EVENT_Collect_Payments'      => ['label' => 'Collect Payments', 'type' => 'checkbox',
                                              'help' => 'Collect payment as part of registration.'],
            'EVENT_Payment_Instructions'  => ['label' => 'Payment Instructions', 'type' => 'html', 'null_ok' => true,
                                              'help' => 'Shown to registrants with their balance due. HTML is allowed.'],
            'EVENT_Processing_Percentage' => ['label' => 'Processing Fee (%)', 'type' => 'percent', 'null_ok' => true,
                                              'help' => 'Service fee added to the total due, as a percentage. Leave blank for none.'],
            'EVENT_Enable_Stripe_Processing' => ['label' => 'Enable Stripe Checkout', 'type' => 'checkbox',
                                              'help' => 'Take card payments through Stripe. Requires the publishable key below.'],
            'EVENT_Stripe_Public_Key'     => ['label' => 'Stripe Publishable Key', 'type' => 'text', 'max' => 128,
                                              'help' => 'The publishable key (starts with pk_). Never enter the secret key (sk_) here.'],
        ],
    ];
    return $tools[$tool];
}

/* ************************************************************************ */
/* Function:    EventSettingsTool                                           */
/* Description: The admin tool the request is for, from the posted 'tool'   */
/*              value. Ends the request if it isn't one of the tools.       */
/* Returns:     array - ['id', 'title', 'saved'] for the tool               */
/* ************************************************************************ */
function EventSettingsTool() {
    $tools = [
        'settings' => ['title' => 'Settings',        'saved' => 'Settings saved.'],
        'payments' => ['title' => 'Payment Options', 'saved' => 'Payment options saved.'],
    ];
    $tool = $_POST['tool'] ?? 'settings';
    if (!is_string($tool) || !isset($tools[$tool])) {
        EventSettingsRespond('Fail', ['Unknown event tool.']);
    }
    return ['id' => $tool] + $tools[$tool];
}

/* ************************************************************************ */
/* Function:    EventSettingsRespond                                        */
/* Description: Sends the JSON response and ends the request.              */
/* ************************************************************************ */
function EventSettingsRespond($status, $payload) {
    header('Content-Type: application/json');
    echo json_encode([$status, $payload]);
    exit();
}

/* ************************************************************************ */
/* Function:    EventSettingsAuthorize                                      */
/* Description: Ends the request with a 'Fail' response unless the session  */
/*              belongs to a logged-in, non-idle user who administers the   */
/*              event (listed in NSX_User_Events_Administered) or is an     */
/*              overlord. Uses only the session, never browser-sent ids.    */
/* Inputs:      $pdo - PHP Data Object, $event_id - the event's id.         */
/* ************************************************************************ */
function EventSettingsAuthorize(PDO $pdo, $event_id) {
    if (empty($_SESSION['login_user']) || app_session_idle_expired()) {
        EventSettingsRespond('Fail', ['Your session has expired. Please log in again.']);
    }
    $_SESSION['last_activity'] = time();

    $stmt = $pdo->prepare('SELECT NSX_User_Role, NSX_User_Events_Administered FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
    $stmt->execute([$_SESSION['login_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $administered = $user ? array_map('trim', explode(',', (string)$user['NSX_User_Events_Administered'])) : [];
    if (!$user || ($user['NSX_User_Role'] !== 'overlord' && !in_array((string)$event_id, $administered, true))) {
        EventSettingsRespond('Fail', ['You are not an administrator for this event.']);
    }
}

/* ************************************************************************ */
/* Function:    EventSettingsLoadEvent                                      */
/* Description: Validates the posted event id, authorizes the user, and     */
/*              returns the PDO connection and event row.                   */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function EventSettingsLoadEvent() {
    $event_id = filter_var($_POST['event_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($event_id === false) {
        EventSettingsRespond('Fail', ['No event was selected.']);
    }

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    EventSettingsAuthorize($pdo, $event_id);

    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_events WHERE EVENT_ID = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        EventSettingsRespond('Fail', ['That event could not be found.']);
    }

    return [$pdo, $event];
}

/* ************************************************************************ */
/* Function:    EventSettingsDisplayValue                                   */
/* Description: Converts a stored column value to what the form shows.      */
/*              Email columns are stored as JSON lists.                     */
/* Returns:     string                                                      */
/* ************************************************************************ */
function EventSettingsDisplayValue($field, $value) {
    if ($value === null) {
        return '';
    }
    if ($field['type'] === 'emails') {
        $list = json_decode($value, true);
        return is_array($list) ? implode(', ', $list) : $value;
    }
    return (string)$value;
}

/* ************************************************************************ */
/* Function:    RenderEditEventSettings                                     */
/* Description: Returns the Edit Event Settings form for the posted event.  */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderEditEventSettings() {
    $tool = EventSettingsTool();
    list($pdo, $event) = EventSettingsLoadEvent();
    $pdo = null;

    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $label_style = 'text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;';
    $separator = '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:1em 0em;"></div></div>';

    $output_html  = '<h2>'.$h($event['EVENT_Name']).' '.$h($tool['title']).'</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<form id="event_settings_form" class="event_settings_form" event_id="'.(int)$event['EVENT_ID'].'" tool="'.$h($tool['id']).'" csrf_token="'.$h(app_csrf_token()).'" onsubmit="return false;">';

    /* Read-only details */
    $admins = json_decode((string)$event['EVENT_Admins'], true);
    $output_html .= '<h3>Event Details</h3>';
    $output_html .= '<p>Event ID: <b>'.(int)$event['EVENT_ID'].'</b><br>';
    $output_html .= 'Directory: <b>events/'.$h($event['EVENT_Directory']).'/</b><br>';
    $output_html .= 'Admins: <b>'.(is_array($admins) && $admins ? $h(implode(', ', $admins)) : 'None').'</b></p>';

    foreach (EventSettingsFields($tool['id']) as $section => $fields) {
        $output_html .= '<h3 style="margin-top:2em;">'.$h($section).'</h3>';
        $output_html .= '<div class="row gtr-25 gtr-uniform">';
        foreach ($fields as $column => $field) {
            $id = 'event_setting_'.$column;
            $value = EventSettingsDisplayValue($field, $event[$column]);

            $output_html .= '       <div class="col-6 col-12-xsmall">';
            $output_html .= '           <p><span style="'.$label_style.'">'.$h($field['label']).':</span> '.$h($field['help']).'</p>';
            $output_html .= '       </div>';
            $output_html .= '       <div class="col-6 col-12-xsmall">';
            switch ($field['type']) {
                case 'checkbox':
                    $output_html .= '<input type="checkbox" id="'.$id.'" db_key="'.$column.'"'.($value ? ' checked' : '').'>';
                    $output_html .= '<label for="'.$id.'">Yes</label>';
                    break;
                case 'html':
                case 'textarea':
                    $output_html .= '<textarea id="'.$id.'" db_key="'.$column.'" rows="6">'.$h($value).'</textarea>';
                    break;
                case 'emails':
                    $output_html .= '<textarea id="'.$id.'" db_key="'.$column.'" rows="2" placeholder="name@example.com, other@example.com">'.$h($value).'</textarea>';
                    break;
                case 'date':
                    $output_html .= '<input type="date" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" />';
                    break;
                case 'money':
                    $output_html .= '<input type="number" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" min="0" step="0.01" />';
                    break;
                case 'percent':
                    $output_html .= '<input type="number" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" min="0" max="100" step="0.01" />';
                    break;
                case 'image':
                    $output_html .= '<div class="frontpage_image_preview">';
                    $output_html .= '<img id="event_setting_frontpage_preview" src="'.$h(EventFrontpageImageURL($event['EVENT_Directory'], $value)).'" alt=""'.($value === '' ? ' style="display:none;"' : '');
                    $output_html .= ' event_image_dir="'.$h(EventImagesDirectory($event['EVENT_Directory'])).'" shared_image_dir="'.$h(SHARED_FRONTPAGE_IMAGE_DIR).'" />';
                    $output_html .= '<span id="event_setting_frontpage_preview_none"'.($value !== '' ? ' style="display:none;"' : '').'>No image set</span>';
                    $output_html .= '</div>';
                    $output_html .= '<input type="file" id="event_setting_frontpage_upload" accept="image/jpeg,image/png,image/webp" style="display:none;" />';
                    $output_html .= '<div class="row gtr-0 frontpage_image_controls">';
                    $output_html .= '<div class="col-6 col-12-small"><label for="event_setting_frontpage_upload" id="event_setting_frontpage_upload_button" class="button small fit">Upload New Image</label></div>';
                    $output_html .= '<div class="col-6 col-12-small"><input type="text" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" maxlength="'.(int)$field['max'].'" /></div>';
                    $output_html .= '</div>';
                    $output_html .= '<div id="event_setting_frontpage_upload_status" class="frontpage_upload_status"></div>';
                    break;
                default: // text
                    $max = isset($field['max']) ? ' maxlength="'.(int)$field['max'].'"' : '';
                    $output_html .= '<input type="text" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'"'.$max.' />';
            }
            $output_html .= '       </div>';
            $output_html .= $separator;
        }
        $output_html .= '</div>';
    }

    $output_html .= '<div id="event_settings_messages" class="event_settings_messages"></div>';
    $output_html .= '<a href="#" id="save_event_settings_button" class="button primary fit">Save '.$h($tool['title']).'</a>';
    $output_html .= '</form>';

    EventSettingsRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    SaveEventSettings                                           */
/* Description: Validates the posted settings (settings[COLUMN] = value)    */
/*              and updates the event. Nothing is saved if any value is     */
/*              invalid.                                                    */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SaveEventSettings() {
    $tool = EventSettingsTool();
    list($pdo, $event) = EventSettingsLoadEvent();

    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventSettingsRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }

    $posted = (isset($_POST['settings']) && is_array($_POST['settings'])) ? $_POST['settings'] : [];
    $values = [];
    $errors = [];

    foreach (EventSettingsFields($tool['id']) as $fields) {
        foreach ($fields as $column => $field) {
            $raw = isset($posted[$column]) && is_string($posted[$column]) ? trim($posted[$column]) : '';
            $label = $field['label'];
            $null_ok = !empty($field['null_ok']);

            if ($raw === '' && $field['type'] !== 'checkbox') {
                if (!empty($field['required'])) {
                    $errors[] = $label.' is required.';
                }
                $values[$column] = $null_ok ? null : (in_array($field['type'], ['money', 'percent'], true) ? 0 : '');
                continue;
            }

            switch ($field['type']) {
                case 'checkbox':
                    $values[$column] = ($raw === '1') ? 1 : 0;
                    break;

                case 'date':
                    $date = DateTime::createFromFormat('!Y-m-d', $raw);
                    if (!$date || $date->format('Y-m-d') !== $raw) {
                        $errors[] = $label.' must be a valid date (YYYY-MM-DD).';
                    }
                    $values[$column] = $raw;
                    break;

                case 'money':
                case 'percent':
                    if (!is_numeric($raw) || $raw < 0 || ($field['type'] === 'percent' && $raw > 100)) {
                        $errors[] = $label.($field['type'] === 'percent' ? ' must be a number from 0 to 100.' : ' must be a number of 0 or more.');
                    }
                    $values[$column] = round((float)$raw, 2);
                    break;

                case 'emails':
                    $list = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($list as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = $label.': "'.$email.'" is not a valid email address.';
                        }
                    }
                    $values[$column] = json_encode(array_values(array_unique($list)));
                    if (isset($field['max']) && strlen($values[$column]) > $field['max']) {
                        $errors[] = $label.' has too many addresses to store.';
                    }
                    break;

                case 'image':
                    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.(png|jpe?g|gif|webp)$/i', $raw)) {
                        $errors[] = $label.' must be just an image file name, e.g. my_event.png.';
                    }
                    $values[$column] = $raw;
                    break;

                default: // text, textarea, html
                    if (isset($field['max']) && mb_strlen($raw) > $field['max']) {
                        $errors[] = $label.' must be '.$field['max'].' characters or fewer.';
                    }
                    $values[$column] = $raw;
            }
        }
    }

    /* Rules that involve more than one field */
    if ($tool['id'] === 'settings') {
        if (!empty($values['EVENT_Start_Date']) && !empty($values['EVENT_End_Date']) && $values['EVENT_End_Date'] < $values['EVENT_Start_Date']) {
            $errors[] = 'End Date must be on or after the Start Date.';
        }
    }
    if ($tool['id'] === 'payments') {
        $stripe_key = $values['EVENT_Stripe_Public_Key'];
        if ($stripe_key !== '' && strpos($stripe_key, 'pk_') !== 0) {
            $errors[] = (strpos($stripe_key, 'sk_') === 0 || strpos($stripe_key, 'rk_') === 0)
                ? 'That is a Stripe SECRET key. Only the publishable key (pk_...) belongs here - the secret key must never be put on a web page.'
                : 'Stripe Publishable Key must start with pk_.';
        }
        if ($values['EVENT_Enable_Stripe_Processing'] && $stripe_key === '') {
            $errors[] = 'Enable Stripe Checkout requires a Stripe Publishable Key.';
        }
        if ($values['EVENT_Enable_Child_Discount'] && $values['EVENT_Child_Base_Fee'] === null) {
            $errors[] = 'Enable Child Pricing requires a Child Fee.';
        }
    }

    if ($errors) {
        EventSettingsRespond('Fail', $errors);
    }

    /* Column names come from EventSettingsFields(), never from the request. */
    $assignments = [];
    foreach (array_keys($values) as $column) {
        $assignments[] = '`'.$column.'` = :'.$column;
    }
    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET '.implode(', ', $assignments).' WHERE EVENT_ID = :event_id');
    foreach ($values as $column => $value) {
        $stmt->bindValue(':'.$column, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }
    $stmt->bindValue(':event_id', (int)$event['EVENT_ID'], PDO::PARAM_INT);

    try {
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('SaveEventSettings failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventSettingsRespond('Fail', ['The settings could not be saved. Please try again.']);
    }

    EventSettingsRespond('Pass', $tool['saved']);
}

/* ************************************************************************ */
/* Function:    UploadFrontpageImage                                        */
/* Description: Saves an uploaded front page image for the posted event     */
/*              into its events/<EVENT_Directory>/images/ folder, scaled to */
/*              FRONTPAGE_IMAGE_MAX_HEIGHT, and makes it the event's front  */
/*              page image straight away. The previously uploaded image is  */
/*              deleted.                                                    */
/* Returns:     JSON ['Pass', ['filename' => ..., 'url' => ...]] or         */
/*              ['Fail', [messages]]                                        */
/* ************************************************************************ */
function UploadFrontpageImage() {
    list($pdo, $event) = EventSettingsLoadEvent();

    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventSettingsRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }

    if (!function_exists('imagecreatetruecolor')) {
        error_log('UploadFrontpageImage: the GD extension is not available, so images cannot be resized');
        EventSettingsRespond('Fail', ['Image uploads are not available on this server (the PHP GD extension is missing).']);
    }

    $upload = CheckFrontpageImageUpload($_FILES['frontpage_image'] ?? null);
    if ($upload['error'] !== '') {
        EventSettingsRespond('Fail', [$upload['error']]);
    }

    $event_dir = EventImagesDirectory($event['EVENT_Directory']);
    if ($event_dir === '' || !is_dir(__DIR__.'/events/'.$event['EVENT_Directory'])) {
        EventSettingsRespond('Fail', ['This event\'s folder (events/'.$event['EVENT_Directory'].'/) could not be found, so the image has nowhere to go.']);
    }
    $dir = __DIR__.'/'.$event_dir;
    if (!is_dir($dir) && !mkdir($dir, 0755)) {
        error_log('UploadFrontpageImage: could not create '.$dir);
        EventSettingsRespond('Fail', ['The image could not be saved. Please try again.']);
    }

    // A new name for every upload, so browsers never show a cached older image
    $basename = 'frontpage_'.date('Ymd_His');
    $filename = SaveFrontpageImage($upload['tmp'], $upload['type'], $dir, $basename);
    if ($filename === '') {
        error_log('UploadFrontpageImage: saving failed for event '.$event['EVENT_ID']);
        EventSettingsRespond('Fail', ['The image could not be saved. Please try again.']);
    }

    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET EVENT_Frontpage_Image = ? WHERE EVENT_ID = ?');
    if (!$stmt->execute([$filename, $event['EVENT_ID']])) {
        error_log('UploadFrontpageImage: database update failed for event '.$event['EVENT_ID']);
        EventSettingsRespond('Fail', ['The image was uploaded but could not be set on the event. Please try again.']);
    }
    $stmt = null;
    $pdo = null;

    // Remove the image this one replaced - only an earlier upload (frontpage_<date>_<time>),
    // never a hand-placed image or one from the shared folder
    $previous = basename((string)$event['EVENT_Frontpage_Image']);
    if ($previous !== $filename && preg_match('/^frontpage_\d{8}_\d{6}\.(jpg|png)$/', $previous) && is_file($dir.'/'.$previous)) {
        if (!unlink($dir.'/'.$previous)) {
            error_log('UploadFrontpageImage: could not delete the previous image '.$dir.'/'.$previous);
        }
    }

    EventSettingsRespond('Pass', ['filename' => $filename, 'url' => $event_dir.'/'.$filename]);
}

/* ************************************************************************ */
/* Function:    CheckFrontpageImageUpload                                   */
/* Description: Checks an image upload from $_FILES. The file's contents    */
/*              decide its type - the browser-supplied name and type are    */
/*              ignored. (Adapted from ContactBook's check_book_photo_upload)*/
/* Inputs:      $file - the $_FILES entry, or null.                         */
/* Returns:     ['error' => string, 'tmp' => string, 'type' => IMAGETYPE_*] */
/* ************************************************************************ */
function CheckFrontpageImageUpload($file) {
    $none = ['error' => '', 'tmp' => '', 'type' => 0];
    $max_mb = FRONTPAGE_IMAGE_MAX_BYTES / 1048576;

    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['error' => 'Please choose an image to upload.'] + $none;
    }
    if (is_array($file['error'])) {
        return ['error' => 'Please choose a single image.'] + $none;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
        || $file['size'] > FRONTPAGE_IMAGE_MAX_BYTES) {
        return ['error' => "The image is too large. Please choose one under {$max_mb} MB."] + $none;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        error_log('CheckFrontpageImageUpload: upload failed with error code '.$file['error']);
        return ['error' => 'The image could not be uploaded. Please try again.'] + $none;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return ['error' => 'The image must be a JPEG, PNG or WebP file.'] + $none;
    }
    if ($info[0] * $info[1] > FRONTPAGE_IMAGE_MAX_PIXELS) {
        return ['error' => 'The image\'s dimensions are too large. Please choose a smaller image.'] + $none;
    }

    return ['error' => '', 'tmp' => $file['tmp_name'], 'type' => $info[2]];
}

/* ************************************************************************ */
/* Function:    SaveFrontpageImage                                          */
/* Description: Re-encodes an uploaded image into $dir as <$basename>.jpg   */
/*              (or .png for PNGs, to keep transparency), scaled down to    */
/*              FRONTPAGE_IMAGE_MAX_HEIGHT with the width kept in           */
/*              proportion. Smaller images are not enlarged. Re-encoding    */
/*              also turns phone photos the right way up, strips hidden     */
/*              data such as GPS location, and drops anything that isn't    */
/*              image data. (Adapted from ContactBook's save_book_photo)    */
/* Inputs:      $tmp - uploaded temp file. $type - IMAGETYPE_*.             */
/*              $dir - the full directory path to save in.                  */
/*              $basename - the filename without extension.                 */
/* Returns:     The saved filename, or '' on failure.                       */
/* ************************************************************************ */
function SaveFrontpageImage($tmp, $type, $dir, $basename) {
    switch ($type) {
        case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG:  $image = @imagecreatefrompng($tmp);  break;
        case IMAGETYPE_WEBP: $image = @imagecreatefromwebp($tmp); break;
        default:             $image = false;
    }
    if ($image === false) {
        return '';
    }

    // Phones save photos sideways and record the rotation in EXIF - apply it.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (@exif_read_data($tmp))['Orientation'] ?? 1;
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($angle !== 0) {
            $rotated = imagerotate($image, $angle, 0);
            imagedestroy($image);
            $image = $rotated;
        }
    }

    $width  = imagesx($image);
    $height = imagesy($image);
    if ($height > FRONTPAGE_IMAGE_MAX_HEIGHT) {
        $new_height = FRONTPAGE_IMAGE_MAX_HEIGHT;
        $new_width  = max(1, (int) round($width * FRONTPAGE_IMAGE_MAX_HEIGHT / $height));
        $resized = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    if ($type === IMAGETYPE_PNG) {
        $filename = $basename.'.png';
        imagesavealpha($image, true);
        $saved = imagepng($image, $dir.'/'.$filename, 6);
    } else {
        $filename = $basename.'.jpg';
        $saved = imagejpeg($image, $dir.'/'.$filename, 85);
    }
    imagedestroy($image);

    return $saved ? $filename : '';
}

