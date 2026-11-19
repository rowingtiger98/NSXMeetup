<?php
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventImages.php';  // EventSponsorLogoDirectory(), CheckFrontpageImageUpload(), SaveFrontpageImage()

const SPONSOR_LOGO_SIZES = [  // LEVEL_Logosize => LEVEL_Logosize_Description; also the sponsor's SPONSOR_Division
    'title'  => 'Page Width',
    'large'  => '1/3 Page Width',
    'medium' => '1/4 Page Width',
    'small'  => '1/6 Page Width',
];

require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
const SPONSOR_METHODS = [
    'RenderEditIndividualSponsorshipLevelInformation' => 'event_admin',
    'RenderEditIndividualSponsorInformation'          => 'event_admin',
    'UpdateSponsorDetails'                            => 'event_admin',
    'UpdateSponsorshipLevelDetails'                   => 'event_admin',
    'UpdateSponsorshipActiveStatus'                   => 'event_admin',
    'UpdateSponsorshipLevelActiveStatus'              => 'event_admin',
    'RenderAddNewSponsorshipLevelSection'             => 'event_admin',
    'RenderAddNewSponsorSection'                      => 'event_admin',
    'EnterNewSponsorIntoDatabase'                     => 'event_admin',
    'UploadSponsorLogo'                               => 'event_admin',
    'EnterNewSponsorshipLevelIntoDatabase'            => 'event_admin',
];
EndpointDispatch(__FILE__, SPONSOR_METHODS, [  // the others that save check the token themselves
    'UpdateSponsorDetails',
]);

/* ************************************************************************ */
/* Function:    RenderEditIndividualSponsorshipLevelInformation             */
/* Description: The Edit Sponsorship Level page for one of the posted       */
/*              event's levels.                                             */
/* Returns:     JSON [HTML]                                                 */
/* ************************************************************************ */
function RenderEditIndividualSponsorshipLevelInformation() {
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
    $event_id = (int)$_POST['event_id'];
    $row = EventSponsorshipLevel(Create_SQL_PHP_DataObject(DB_NAME), $event_id, $_POST['level_id'] ?? 0);

    $output_html = '<h2>Edit Sponsorship Level Information</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    if (!$row) {
        echo json_encode([$output_html.'<p class="event_settings_error">That sponsorship level could not be found for this event.</p>']);
        return;
    }
    $level_id = (int)$row['LEVEL_ID'];

    $output_html .= '<p>Change the level\'s name, amount and logo size. Sponsors at this level are updated to match when you save.</p>';
    $output_html .= '<div id="individual_sponsorship_level_edit_container" level_id="'.$level_id.'" event_id="'.$event_id.'" csrf_token="'.$h(app_csrf_token()).'" class="row gtr-50">';
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<table>';
    $output_html .= '<tr><td class="sponsorship_level_edit_name">Sponsorship Level Name</td><td><input type="text" id="sponsorship_level_edit_infobox_name" db_key="LEVEL_Name" maxlength="64" current_value="'.$h($row['LEVEL_Name']).'" value="'.$h($row['LEVEL_Name']).'" /></td></tr>';
    $output_html .= '<tr><td class="sponsorship_level_edit_amount">Sponsorship Level Amount ($)</td><td><input type="text" id="sponsorship_level_edit_infobox_amount" db_key="LEVEL_Amount" inputmode="numeric" current_value="'.$h($row['LEVEL_Amount']).'" value="'.$h($row['LEVEL_Amount']).'" /></td></tr>';
    $output_html .= '<tr><td class="sponsorship_level_edit_logosize">Sponsorship Level Logo Size</td><td>';
    $output_html .= '<select name="sponsorship_logosize_dropdown" id="sponsorship_level_info_dropdown_logosize" current_level="'.$h($row['LEVEL_Logosize']).'">'.SponsorLogoSizeOptions($row['LEVEL_Logosize']).'</select>';
    $output_html .= '</td></tr>';
    $output_html .= '</table>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    $output_html .= '<div style="display:inline-flex;align-items:center;gap:1em;">';
    $output_html .= '<a href="#" id="save_level_changes_button" class="button primary disabled" level_id="'.$level_id.'">Save Changes</a>';
    $output_html .= '<span id="save_level_changes_status"></span>';
    $output_html .= '</div>';

    echo json_encode([$output_html]);
}

/* ************************************************************************ */
/* Function:    EventSponsorshipLevel                                       */
/* Description: A sponsorship level's row, only if it belongs to $event_id. */
/* Returns:     array, or null                                              */
/* ************************************************************************ */
function EventSponsorshipLevel(PDO $pdo, $event_id, $level_id) {
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_sponsorship_levels WHERE LEVEL_ID = ? AND EVENT_ID = ?');
    $stmt->execute([(int)$level_id, (int)$event_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/* ************************************************************************ */
/* Function:    SponsorLogoSizeOptions                                      */
/* Description: The <option>s for a level's logo size dropdown.             */
/* Inputs:      $selected - the current LEVEL_Logosize, or '' for none.     */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function SponsorLogoSizeOptions($selected) {
    $html = isset(SPONSOR_LOGO_SIZES[$selected]) ? '' : '<option value="" selected disabled hidden>Select a Logo Size</option>';
    foreach (SPONSOR_LOGO_SIZES as $value => $label) {
        $html .= '<option value="'.$value.'"'.($value === $selected ? ' selected' : '').'>'.$label.'</option>';
    }
    return $html;
}

/* ************************************************************************ */
/* Function:    CheckSponsorshipLevelFields                                 */
/* Description: Checks a posted level name, amount and logo size. A name    */
/*              may not repeat another of the event's levels (sponsors are  */
/*              matched to their level by name). Ends the request with a    */
/*              'Fail' response listing every problem.                      */
/* Inputs:      $except_level_id - the level being edited (0 when adding).  */
/* Returns:     ['name' => string, 'amount' => string, 'logosize' => string]*/
/* ************************************************************************ */
function CheckSponsorshipLevelFields(PDO $pdo, $event_id, $except_level_id) {
    $name = trim((string)($_POST['sponsorship_name'] ?? ''));
    $amount = str_replace([',', '$', ' '], '', (string)($_POST['sponsorship_amount'] ?? ''));
    $amount = preg_replace('/\.0*$/', '', $amount);  // 500.00 -> 500
    $logosize = (string)($_POST['sponsorship_logosize'] ?? '');

    $errors = [];
    if ($name === '' || mb_strlen($name) > 64) {
        $errors[] = 'Please enter a level name of up to 64 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT LEVEL_ID, LEVEL_Name FROM nsx_meetup_sponsorship_levels WHERE EVENT_ID = ?');
        $stmt->execute([(int)$event_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $other) {
            if ((int)$other['LEVEL_ID'] !== (int)$except_level_id && mb_strtolower(trim($other['LEVEL_Name'])) === mb_strtolower($name)) {
                $errors[] = 'This event already has a level named "'.$name.'".';
            }
        }
    }
    if (!preg_match('/^\d{1,9}$/', $amount)) {
        $errors[] = 'Please enter the level amount in whole dollars (for example 500).';
    }
    if (!isset(SPONSOR_LOGO_SIZES[$logosize])) {
        $errors[] = 'Please select a logo size.';
    }
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }
    return ['name' => $name, 'amount' => (string)(int)$amount, 'logosize' => $logosize];
}

/* ************************************************************************ */
/* Function:    RenderEditIndividualSponsorInformation         				*/
/* Description: This function will takes changed data and updates the table */
/*              specified with the changred information.                    */
/* Inputs:      table_name = The table to update.                           */
/*              query_data = String of the JSON object containing the keys  */
/*              to update and the corresponding values.                     */
/* Returns:     string                                        				*/
/* ************************************************************************ */  
function RenderEditIndividualSponsorInformation() {
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
    $results = array();
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $event_id = (int)$_POST['event_id'];
    $row = EventSponsor($pdo, $event_id, $_POST['sponsor_id'] ?? 0);
    if (!$row) {
        echo json_encode(['<p class="event_settings_error">That sponsor could not be found for this event.</p>']);
        return;
    }
    $sponsor_id = (int)$row['SPONSOR_ID'];

	$output_html = '<div id="individual_sponsor_edit_container" sponsor_id="'.$sponsor_id.'" event_id="'.$event_id.'" csrf_token="'.$h(app_csrf_token()).'" class="row gtr-50">';
    $output_html .= '<div id="back_navigation_button"></div>';   
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<p>This is the sponsor management interface. Here, you will be able to edit the properties of all the existing sponsors in the le Tour de Femme
    sponsors database. These properties include the basic things, such as name and web link information for a sponsor, to advanced details such as sposnorship level and logos.
    Additionally, you will be able to set the active status of a current or past sponsor. Only active sponsors are displayed on the web site. Finally, you will be able
    to enter details for new sponsors as we gain them.</p>';
    $output_html .= '</div>';
    $output_html .= '<div class="col-6 col-6-medium col-12-small">';
    $output_html .= '<table><thead><tr><th>Sponsor Property</th><th>Current Setting</th></tr></thead></tbody>';
    
    /* ---------------------------------------------------------------- */
    /* Sponsor Text Input information                                   */
    /* ---------------------------------------------------------------- */

    /* Sponsor Name */
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Name</td><td><input type="text" id="sponsor_edit_infobox_name" db_key="SPONSOR_Name" current_value="'.$h($row['SPONSOR_Name']).'" value="'.$h($row['SPONSOR_Name']).'" /></td><tr>';

    /* Sponsor Web Link */
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Web Page / Link</td><td><input type="text" id="sponsor_edit_infobox_weblink" db_key="SPONSOR_Weblink" current_value="'.$h($row['SPONSOR_Weblink']).'" value="'.$h($row['SPONSOR_Weblink']).'" /></td><tr>';


    /* ---------------------------------------------------------------- */
    /* Sponsor Drop Down Input information                              */
    /* ---------------------------------------------------------------- */

    /* Sponsor Level - one of the event's active levels. A level that's no longer one of them is   */
    /* kept (value "") until another is chosen.                                                     */
    $levels = ActiveSponsorshipLevels($pdo, $event_id);
    $current_level = '';
    $level_options = '';
    foreach ($levels as $level) {
        $selected = ($level['LEVEL_Name'] === (string)$row['SPONSOR_Level'] && $current_level === '');
        if ($selected) {
            $current_level = (string)(int)$level['LEVEL_ID'];
        }
        $level_options .= '<option value="'.(int)$level['LEVEL_ID'].'"'.($selected ? ' selected' : '').'>'.$h($level['LEVEL_Name']).'</option>';
    }
    if ($current_level === '') {
        $level_options = '<option value="" selected disabled>'.$h($row['SPONSOR_Level'] !== '' ? $row['SPONSOR_Level'].' (not a current level)' : 'Select a Sponsor Level Option').'</option>'.$level_options;
    }
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Level</td><td>';
    $output_html .= '<select name="sponsor_level_dropdown" id="sponsor_edit_infodropdown_level" current_level="'.$current_level.'">'.$level_options.'</select>';
    if (!$levels) {
        $output_html .= '<p class="event_settings_error">No active sponsorship levels are set up for this event, so the level can\'t be changed. Use Add / Edit Sponsorship Levels to create one.</p>';
    }
    $output_html .= '</td></tr>';




    $output_html .= '</tbody></table>';
    $output_html .= '</div>';
    // Sponsor Logo - replaced as soon as a new one is chosen
    $output_html .= '<div class="col-6 col-6-medium col-12-small">';
    $output_html .= '<h3>Sponsor Logo</h3>';
    $output_html .= SponsorLogoInstructions('The new logo replaces the current one as soon as it uploads.');
    $output_html .= '<div id="edit_sponsor_logo_preview" class="sponsor_logo_preview">'
                   .(trim((string)$row['SPONSOR_Large_Logo']) !== '' ? '<img src="'.$h(SponsorLogoURL($row['SPONSOR_Large_Logo'])).'" alt="Current logo">' : '<p>This sponsor has no logo yet.</p>')
                   .'</div>';
    $output_html .= '<input type="file" id="edit_sponsor_logo" accept="image/jpeg,image/png,image/webp" style="margin-top:1em;">';
    $output_html .= '<div id="edit_sponsor_logo_status" class="frontpage_upload_status"></div>';
    $output_html .= '</div>';

    /* Description Editor - saved with the other details by Save Changes */
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<h3>Sponsor Description</h3>';
    $output_html .= '<p>The information you enter below will be used to describe the sponsor on the web site.</p>';
    $output_html .= '<textarea name="test_description" id="sponsor_edit_section_'.$sponsor_id.'" sponsor_id="'.$sponsor_id.'">'.$h($row['SPONSOR_Description']).'</textarea>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    $output_html .= '<div style="display:inline-flex;align-items:center;gap:1em;margin-top:1.5em;">';
 	$output_html .= '<a href="#" id="save_changes_button" sponsor_id="'.$sponsor_id.'" class="button primary disabled">Save Changes</a>';
    $output_html .= '<span id="save_changes_button_status"></span>';
    $output_html .= '</div>';

    /* Start Building the HTML to edit the page */
	$results[] = $output_html;
	echo json_encode($results); 
}


/* ************************************************************************ */
/* Function:    UpdateSponsorDetails                           				*/
/* Description: This function will update the sponsor details of Name,      */
/*              weblink and sponsor level                     .             */
/* Inputs:      sponsor_id = The sponsor tp update.                         */
/*              sponsor_name = Name of the sponsor.                         */
/*              sponsor_weblink = The sponsors web page link.               */
/*              sponsor_level = The level of the sponsor.                   */
/*              sponsor_description = The description (when sent).          */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */  
function UpdateSponsorDetails() {
    $dbh = Create_SQL_PHP_DataObject(DB_NAME);
    $event_id = (int)$_POST['event_id'];
    $sponsor = EventSponsor($dbh, $event_id, $_POST['sponsor_id'] ?? 0);
    if (!$sponsor) {
        echo json_encode(['fail', 'That sponsor could not be found for this event.']);
        return;
    }
    $sponsor_name = trim((string)($_POST['sponsor_name'] ?? ''));
    $sponsor_weblink = trim((string)($_POST['sponsor_weblink'] ?? ''));
    $level_id = (string)($_POST['sponsor_level'] ?? '');
    if ($sponsor_name === '') {
        echo json_encode(['fail', 'Please enter the sponsor\'s name.']);
        return;
    }

    // Blank keeps the sponsor's current level; otherwise it must be one of the event's active levels
    $sponsor_level = $sponsor['SPONSOR_Level'];
    $sponsor_division = $sponsor['SPONSOR_Division'];
    if ($level_id !== '') {
        $level = null;
        foreach (ActiveSponsorshipLevels($dbh, $event_id) as $row) {
            if ((string)(int)$row['LEVEL_ID'] === $level_id) {
                $level = $row;
            }
        }
        if ($level === null) {
            echo json_encode(['fail', 'Please select one of this event\'s sponsorship levels.']);
            return;
        }
        $sponsor_level = $level['LEVEL_Name'];
        $sponsor_division = $level['LEVEL_Logosize'];
    }

    $data = [
        'sponsor_id' => (int)$sponsor['SPONSOR_ID'],
        'sponsor_name' => $sponsor_name,
        'sponsor_weblink' => $sponsor_weblink,
        'sponsor_level' => $sponsor_level,
        'sponsor_division' => $sponsor_division,
        'sponsor_description' => isset($_POST['sponsor_description']) ? (string)$_POST['sponsor_description'] : (string)$sponsor['SPONSOR_Description']
    ];

    $sql = "UPDATE `nsx_meetup_event_sponsors` SET `SPONSOR_Name`=:sponsor_name, `SPONSOR_Weblink`=:sponsor_weblink, `SPONSOR_Level`=:sponsor_level, `SPONSOR_Division`=:sponsor_division, `SPONSOR_Description`=:sponsor_description WHERE SPONSOR_ID=:sponsor_id";
    try {
        $dbh->prepare($sql)->execute($data);
        $results = ['success', 'The details for '.htmlspecialchars($sponsor_name, ENT_QUOTES).' were updated successfully.'];
    } catch (PDOException $e) {
        error_log('UpdateSponsorDetails failed for sponsor '.$sponsor['SPONSOR_ID'].': '.$e->getMessage());
        $results = ['fail', 'The changes could not be saved. Please try again.'];
    }
	echo json_encode($results); 
}  

/* ************************************************************************ */
/* Function:    UpdateSponsorshipLevelDetails                               */
/* Description: Saves a level's name, amount and logo size. The event's     */
/*              sponsors at this level get the new name and logo size, so   */
/*              they stay at it.                                            */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function UpdateSponsorshipLevelDetails() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    $level = EventSponsorshipLevel($pdo, $event['EVENT_ID'], $_POST['level_id'] ?? 0);
    if (!$level) {
        EventAdminRespond('Fail', ['That sponsorship level could not be found for this event.']);
    }
    $fields = CheckSponsorshipLevelFields($pdo, $event['EVENT_ID'], $level['LEVEL_ID']);

    try {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE nsx_meetup_sponsorship_levels SET LEVEL_Name = ?, LEVEL_Amount = ?, LEVEL_Logosize = ?, LEVEL_Logosize_Description = ? WHERE LEVEL_ID = ?')
            ->execute([$fields['name'], $fields['amount'], $fields['logosize'], SPONSOR_LOGO_SIZES[$fields['logosize']], (int)$level['LEVEL_ID']]);

        $sponsors = $pdo->prepare('SELECT SPONSOR_ID, SPONSOR_Event_IDs FROM nsx_meetup_event_sponsors WHERE SPONSOR_Level = ?');
        $sponsors->execute([$level['LEVEL_Name']]);
        $update = $pdo->prepare('UPDATE nsx_meetup_event_sponsors SET SPONSOR_Level = ?, SPONSOR_Division = ? WHERE SPONSOR_ID = ?');
        foreach ($sponsors->fetchAll(PDO::FETCH_ASSOC) as $sponsor) {
            if (in_array((string)(int)$event['EVENT_ID'], array_map('trim', explode(',', (string)$sponsor['SPONSOR_Event_IDs'])), true)) {
                $update->execute([$fields['name'], $fields['logosize'], (int)$sponsor['SPONSOR_ID']]);
            }
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('UpdateSponsorshipLevelDetails failed for level '.$level['LEVEL_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The changes could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', 'The details for '.$fields['name'].' were updated successfully.');
}

/* ************************************************************************ */
/* Function:    UpdateSponsorshipActiveStatus                               */
/* Description: Turns one of the event's sponsors on or off (the Yes / No   */
/*              buttons in the sponsor list).                               */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function UpdateSponsorshipActiveStatus() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    $sponsor = EventSponsor($pdo, $event['EVENT_ID'], $_POST['sponsor_id'] ?? 0);
    if (!$sponsor) {
        EventAdminRespond('Fail', ['That sponsor could not be found for this event.']);
    }
    $active = (($_POST['sponsor_state'] ?? '') === '1') ? 1 : 0;
    $pdo->prepare('UPDATE nsx_meetup_event_sponsors SET SPONSOR_Active = ? WHERE SPONSOR_ID = ?')->execute([$active, (int)$sponsor['SPONSOR_ID']]);
    EventAdminRespond('Pass', $sponsor['SPONSOR_Name'].($active ? ' is now active.' : ' is now inactive.'));
}

/* ************************************************************************ */
/* Function:    UpdateSponsorshipLevelActiveStatus                          */
/* Description: Turns one of the event's sponsorship levels on or off (the  */
/*              Yes / No buttons in the levels list). Only active levels    */
/*              can be given to sponsors.                                   */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function UpdateSponsorshipLevelActiveStatus() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    $level = EventSponsorshipLevel($pdo, $event['EVENT_ID'], $_POST['level_id'] ?? 0);
    if (!$level) {
        EventAdminRespond('Fail', ['That sponsorship level could not be found for this event.']);
    }
    $active = (($_POST['sponsorship_state'] ?? '') === '1') ? 1 : 0;
    $pdo->prepare('UPDATE nsx_meetup_sponsorship_levels SET LEVEL_Active = ? WHERE LEVEL_ID = ?')->execute([$active, (int)$level['LEVEL_ID']]);
    EventAdminRespond('Pass', $level['LEVEL_Name'].($active ? ' is now active.' : ' is now inactive.'));
}

/* ************************************************************************ */
/* Function:    RenderAddNewSponsorshipLevelSection                         */
/* Description: The Create Sponsorship Level form for the posted event.     */
/* Returns:     JSON [HTML]                                                 */
/* ************************************************************************ */
function RenderAddNewSponsorshipLevelSection() {
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
    $event_id = (int)$_POST['event_id'];

	$output_html = '<div id="add_sponsorship_level_container">';
    $output_html .= '<h2>Create Sponsorship Level</h2><p>This section allows you to add a new sponsorship level for this event. Levels can be edited and set to active / inactive at any time. Enter the 
    required information below and click Save New Level. Only active levels can be given to sponsors.</p></div>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';

    $output_html .= '<div id="main_sponsorship_info_container" class="row gtr gtr-uniform" event_id="'.$event_id.'" csrf_token="'.$h(app_csrf_token()).'">';
    $output_html .= '   <div class="col-6 col-6-medium col-12-small">';
    $output_html .= '       <input type="text" id="sponsorship_level_infobox_name" db_key="LEVEL_Name" maxlength="64" value="" placeholder="Enter the Level Name"/>';
    $output_html .= '   </div>';  
    $output_html .= '   <div class="col-3 col-3-medium col-12-small">';
    $output_html .= '       <input type="text" id="sponsorship_level_infobox_amount" db_key="LEVEL_Amount" inputmode="numeric" value="" placeholder="Level Amount ($)"/>';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-3 col-3-medium col-12-small">';
    $output_html .= '       <select name="sponsorship_logosize_dropdown" id="sponsorship_level_info_dropdown_logosize">'.SponsorLogoSizeOptions('').'</select>';
    $output_html .= '   </div>';        
    $output_html .= '   <div class="col-2 col-2-medium col-6-small">';
    $output_html .= '       Active Level: ';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-3 col-3-medium col-6-small">';      
    $output_html .= '       <input type="radio" id="active_sponsorship_level_on" name="active_sponsorship_level_radio_button" value="1" checked>';
    $output_html .= '       <label for="active_sponsorship_level_on">Yes</label>';
    $output_html .= '       <input type="radio" id="active_sponsoship_level_off" name="active_sponsorship_level_radio_button" value="0">';
    $output_html .= '       <label for="active_sponsoship_level_off">No</label>';
    $output_html .= '   </div>';     
    $output_html .= '</div>';     
    $output_html .= '<div style="display:inline-flex;align-items:center;gap:1em;margin-top:1.5em;">';
    $output_html .= '   <a href="#" id="save_new_sponsorship_level_botton" class="button primary">Save New Level</a>';
    $output_html .= '   <span id="save_new_sponsorship_level_status"></span>';
    $output_html .= '</div>';

    echo json_encode([$output_html]);
}

/* ************************************************************************ */
/* Function:    SponsorLogoInstructions                                     */
/* Description: The help text shown above a sponsor logo upload.            */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function SponsorLogoInstructions($when_saved) {
    return '<p>Upload the sponsor\'s logo (JPEG, PNG or WebP, under '.(FRONTPAGE_IMAGE_MAX_BYTES / 1048576).' MB). Larger logos are scaled down to fit within '
          .SPONSOR_LOGO_MAX_SIZE.' x '.SPONSOR_LOGO_MAX_SIZE.' pixels, keeping their shape. '.$when_saved.'</p>';
}

/* ************************************************************************ */
/* Function:    CheckSponsorLogoUpload                                      */
/* Description: Checks the posted sponsor_logo and makes sure the event's   */
/*              sponsors folder exists. Ends the request with a 'Fail'      */
/*              response if either isn't usable.                            */
/* Returns:     null when no logo was posted, else the upload               */
/*              (CheckFrontpageImageUpload) plus 'dir' - the site-relative  */
/*              folder it goes in.                                          */
/* ************************************************************************ */
function CheckSponsorLogoUpload(array $event) {
    if (($_FILES['sponsor_logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (!function_exists('imagecreatetruecolor')) {
        error_log('CheckSponsorLogoUpload: the GD extension is not available, so logos cannot be resized');
        EventAdminRespond('Fail', ['Logo uploads are not available on this server (the PHP GD extension is missing).']);
    }
    $logo = CheckFrontpageImageUpload($_FILES['sponsor_logo']);
    if ($logo['error'] !== '') {
        EventAdminRespond('Fail', [$logo['error']]);
    }
    $logo_dir = EventSponsorLogoDirectory($event['EVENT_Directory']);
    if ($logo_dir === '' || !is_dir(__DIR__.'/events/'.$event['EVENT_Directory'])) {
        EventAdminRespond('Fail', ['This event\'s folder (events/'.$event['EVENT_Directory'].'/) could not be found, so the logo has nowhere to go.']);
    }
    if (!is_dir(__DIR__.'/'.$logo_dir) && !mkdir(__DIR__.'/'.$logo_dir, 0755, true)) {
        error_log('CheckSponsorLogoUpload: could not create '.$logo_dir);
        EventAdminRespond('Fail', ['The logo could not be saved. Please try again.']);
    }
    return $logo + ['dir' => $logo_dir];
}

/* ************************************************************************ */
/* Function:    SaveSponsorLogo                                             */
/* Description: Saves a checked logo (CheckSponsorLogoUpload) for a sponsor */
/*              as sponsor_<id>_<date>_<time>, scaled to fit                */
/*              SPONSOR_LOGO_MAX_SIZE square.                               */
/* Returns:     the site-relative path, or '' on failure                    */
/* ************************************************************************ */
function SaveSponsorLogo(array $logo, $sponsor_id) {
    $filename = SaveFrontpageImage($logo['tmp'], $logo['type'], __DIR__.'/'.$logo['dir'], 'sponsor_'.(int)$sponsor_id.'_'.date('Ymd_His'),
                                   SPONSOR_LOGO_MAX_SIZE, SPONSOR_LOGO_MAX_SIZE);
    return ($filename === '') ? '' : $logo['dir'].'/'.$filename;
}

/* ************************************************************************ */
/* Function:    UploadSponsorLogo                                           */
/* Description: Replaces an existing sponsor's logo (Edit Sponsor page).    */
/*              The previous logo file is deleted when it was an earlier    */
/*              upload for this sponsor - never a shared or older logo.     */
/* Returns:     JSON ['Pass', logo URL] or ['Fail', [messages]]             */
/* ************************************************************************ */
function UploadSponsorLogo() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    $sponsor = EventSponsor($pdo, $event['EVENT_ID'], $_POST['sponsor_id'] ?? 0);
    if (!$sponsor) {
        EventAdminRespond('Fail', ['That sponsor could not be found for this event.']);
    }
    $logo = CheckSponsorLogoUpload($event);
    if ($logo === null) {
        EventAdminRespond('Fail', ['Please choose a logo to upload.']);
    }

    $path = SaveSponsorLogo($logo, $sponsor['SPONSOR_ID']);
    if ($path === '') {
        error_log('UploadSponsorLogo: saving the logo failed for sponsor '.$sponsor['SPONSOR_ID']);
        EventAdminRespond('Fail', ['The logo could not be saved. Please try again.']);
    }
    try {
        $pdo->prepare('UPDATE nsx_meetup_event_sponsors SET SPONSOR_Large_Logo = ?, SPONSOR_Thumbnail_Logo = ? WHERE SPONSOR_ID = ?')
            ->execute([$path, $path, (int)$sponsor['SPONSOR_ID']]);
    } catch (PDOException $e) {
        unlink(__DIR__.'/'.$path);
        error_log('UploadSponsorLogo failed for sponsor '.$sponsor['SPONSOR_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The logo could not be saved. Please try again.']);
    }

    // Tidy up this sponsor's previous upload
    $own_upload = '#^events/[A-Za-z0-9_-]+/images/sponsors/sponsor_'.(int)$sponsor['SPONSOR_ID'].'_\d{8}_\d{6}\.(jpg|png)$#';
    foreach (array_unique([(string)$sponsor['SPONSOR_Large_Logo'], (string)$sponsor['SPONSOR_Thumbnail_Logo']]) as $previous) {
        if ($previous !== $path && preg_match($own_upload, $previous) && is_file(__DIR__.'/'.$previous)) {
            unlink(__DIR__.'/'.$previous);
        }
    }

    EventAdminRespond('Pass', SponsorLogoURL($path));
}

/* ************************************************************************ */
/* Function:    EventSponsor                                                */
/* Description: A sponsor's row, only if it's one of $event_id's sponsors   */
/*              (SPONSOR_Event_IDs is a comma-separated list).              */
/* Returns:     array, or null                                              */
/* ************************************************************************ */
function EventSponsor(PDO $pdo, $event_id, $sponsor_id) {
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_event_sponsors WHERE SPONSOR_ID = ?');
    $stmt->execute([(int)$sponsor_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || !in_array((string)(int)$event_id, array_map('trim', explode(',', (string)$row['SPONSOR_Event_IDs'])), true)) {
        return null;
    }
    return $row;
}

/* ************************************************************************ */
/* Function:    ActiveSponsorshipLevels                                     */
/* Description: The event's active sponsorship levels, highest amount       */
/*              first - the levels a new sponsor can be given.              */
/* Returns:     array of LEVEL_ID, LEVEL_Name, LEVEL_Logosize rows          */
/* ************************************************************************ */
function ActiveSponsorshipLevels(PDO $pdo, $event_id) {
    $stmt = $pdo->prepare('SELECT LEVEL_ID, LEVEL_Name, LEVEL_Logosize FROM nsx_meetup_sponsorship_levels WHERE EVENT_ID = ? AND LEVEL_Active = 1 ORDER BY LEVEL_Amount DESC, LEVEL_Name');
    $stmt->execute([(int)$event_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ************************************************************************ */
/* Function:    RenderAddNewSponsorSection                    				*/
/* Description: This function will return the form needed to enter a new    */
/*              sponsor into the database.                                  */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */
function RenderAddNewSponsorSection() {
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
    $results = array();
    $event_id = (int)$_POST['event_id'];
    $levels = ActiveSponsorshipLevels(Create_SQL_PHP_DataObject(DB_NAME), $event_id);

    if (!$levels) {
        $output_html  = '<div id="add_sponsor_container" class="row gtr gtr-uniform" event_id="'.$event_id.'">';
        $output_html .= '<div class="col-12 col-12-small">';
        $output_html .= '<h2>Add New Sponsor</h2>';
        $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
        $output_html .= '<p class="event_settings_error">A sponsor can\'t be added until at least one sponsorship level has been set up for this event. '
                       .'Use Add / Edit Sponsorship Levels to create a level (or set an existing level to active), then come back to add the sponsor.</p>';
        $output_html .= '<a href="#" id="go_to_sponsorship_levels" class="button primary" event_id="'.$event_id.'">Go to Sponsorship Levels</a>';
        $output_html .= '</div>';
        $output_html .= '</div>';
        $results[] = $output_html;
        echo json_encode($results);
        return;
    }

	$output_html = '<div id="add_sponsor_container" class="row gtr gtr-uniform" event_id="'.$event_id.'" csrf_token="'.$h(app_csrf_token()).'">';

    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<h2>Add New Sponsor</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<p>This section allows you to add a new sponsor to the database. Sponsors can be edited and set to active / inactive at any time. Enter the 
    required information below and click save to add the sponsor to the database.</p>';
    $output_html .= '</div>';
    $output_html .= '   <div class="col-6 col-6-medium col-12-small">';
 	$output_html .= '       <input type="text" id="sponsor_edit_infobox_name" db_key="SPONSOR_Name" value="" placeholder="Enter the Sponsor Name"/>';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-6 col-6-medium col-12-small">';
    $output_html .= '       <input type="text" id="sponsor_edit_infobox_weblink" db_key="SPONSOR_Weblink" value="" placeholder="Enter the Sponsors full webpage link"/>';
    $output_html .= '   </div>';  
    $output_html .= ' <div class="col-6 col-6-medium col-12-small">';
    $output_html .= '   <select name="sponsor_level_dropdown" id="sponsor_edit_infodropdown_level">';
    $output_html .= '   <option value="none" selected disabled hidden>Select a Sponsor Level Option</option>';
    foreach ($levels as $level) {
        $output_html .= '   <option value="'.(int)$level['LEVEL_ID'].'">'.$h($level['LEVEL_Name']).'</option>';
    }
    $output_html .= '   </select>';
    $output_html .= '</div>';  
    $output_html .= ' <div class="col-2 col-2-medium col-6-small">';
    $output_html .= 'Active Sponsor: ';
    $output_html .= '</div>';
    $output_html .= ' <div class="col-3 col-3-medium col-6-small">';      
    $output_html .= '<input type="radio" id="active_sponsor_on" name="active_sponsor_radio_button" value="1">';
    $output_html .= '<label for="active_sponsor_on">Yes</label>';
    $output_html .= '<input type="radio" id="active_sponsor_off" name="active_sponsor_radio_button" value="0">';
    $output_html .= '<label for="active_sponsor_off">No</label>';   
    $output_html .= '</div>';    

    /* Sponsor Description - half the width, full width on small screens */
    $output_html .= ' <div class="col-6 col-12-small">';      
    $output_html .= '<h3>Sponsor Description</h3>';
    $output_html .= '<p>The information you enter below will be used to describe the sponsor on the web site.</p>';
    $output_html .= '<textarea name="test_description" id="new_sponsor_edit_section"></textarea>';
    $output_html .= '</div>';

    /* Sponsor Logo */
    $output_html .= ' <div class="col-6 col-12-small">';
    $output_html .= '<h3>Sponsor Logo</h3>';
    $output_html .= SponsorLogoInstructions('The logo is saved when you click Save New Sponsor.');
    $output_html .= '<input type="file" id="new_sponsor_logo" accept="image/jpeg,image/png,image/webp">';
    $output_html .= '<div id="new_sponsor_logo_preview" class="sponsor_logo_preview"></div>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    $output_html .= '<div id="save_status_section" style="display:inline-flex;align-items:center;gap:1em;margin-top:1.5em;">';
    $output_html .= '<a href="#" id="save_new_sponsor_information_botton" class="button primary">Save New Sponsor</a>';
    $output_html .= '<span id="save_new_sponsor_status" class="frontpage_upload_status" style="margin-top:0;"></span>';
    $output_html .= "</div>";    

    $results[] = $output_html;
	echo json_encode($results);     
}

/* ************************************************************************ */
/* Function:    EnterNewSponsorIntoDatabase                                 */
/* Description: Adds a sponsor to the posted event. An uploaded logo        */
/*              (sponsor_logo) is scaled to fit SPONSOR_LOGO_MAX_SIZE       */
/*              square and saved in events/<dir>/images/sponsors/.          */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function EnterNewSponsorIntoDatabase() {
    list($dbh, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }

    $sponsor_name = trim((string)($_POST['sponsor_name'] ?? ''));
    $sponsor_weblink = trim((string)($_POST['sponsor_weblink'] ?? ''));
    $level_id = filter_var($_POST['sponsor_level'] ?? '', FILTER_VALIDATE_INT);
    $sponsor_active = intval($_POST['sponsor_active'] ?? 0) ? 1 : 0;
    $sponsor_description = (string)($_POST['sponsor_description'] ?? '');

    // The level must be one of this event's active levels; the sponsor keeps its name and logo size
    $level = null;
    foreach (ActiveSponsorshipLevels($dbh, $event['EVENT_ID']) as $row) {
        if ((int)$row['LEVEL_ID'] === $level_id) {
            $level = $row;
        }
    }

    $errors = [];
    if ($sponsor_name === '') {
        $errors[] = 'Please enter the sponsor\'s name.';
    }
    if ($level === null) {
        $errors[] = 'Please select a sponsor level.';
    }
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }
    $sponsor_level = $level['LEVEL_Name'];
    $sponsor_division = $level['LEVEL_Logosize'];

    // The logo is optional - check it before anything is saved
    $logo = CheckSponsorLogoUpload($event);

    $saved_logo = '';
    try {
        $dbh->beginTransaction();
        $insert_query = "INSERT INTO `nsx_meetup_event_sponsors` 
        (`SPONSOR_Type`,`SPONSOR_Active`,`SPONSOR_Event_IDs`,`SPONSOR_Name`, `SPONSOR_Weblink`, `SPONSOR_Level`, `SPONSOR_Division`, `SPONSOR_Description`,`SPONSOR_Large_Logo`,`SPONSOR_Thumbnail_Logo`)
         VALUES ('corporate',:sponsor_active,:event_id,:sponsor_name,:sponsor_weblink,:sponsor_level,:sponsor_division,:sponsor_description,'sponsor-coming-soon-front.jpg','sponsor-coming-soon-front.jpg')";
        $sth = $dbh->prepare($insert_query);
        $sth->execute([
            ':sponsor_active'      => $sponsor_active,
            ':event_id'            => (string)$event['EVENT_ID'],
            ':sponsor_name'        => $sponsor_name,
            ':sponsor_weblink'     => $sponsor_weblink,
            ':sponsor_level'       => $sponsor_level,
            ':sponsor_division'    => $sponsor_division,
            ':sponsor_description' => $sponsor_description,
        ]);
        $sponsor_id = (int)$dbh->lastInsertId();

        if ($logo !== null) {
            $path = SaveSponsorLogo($logo, $sponsor_id);
            if ($path === '') {
                throw new RuntimeException('saving the logo failed');
            }
            $saved_logo = __DIR__.'/'.$path;
            $dbh->prepare('UPDATE `nsx_meetup_event_sponsors` SET `SPONSOR_Large_Logo` = ?, `SPONSOR_Thumbnail_Logo` = ? WHERE `SPONSOR_ID` = ?')
                ->execute([$path, $path, $sponsor_id]);
        }
        $dbh->commit();
    } catch (Exception $e) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        if ($saved_logo !== '' && is_file($saved_logo)) {
            unlink($saved_logo);
        }
        error_log('EnterNewSponsorIntoDatabase failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The sponsor could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', 'New sponsor saved.');
}

/* ************************************************************************ */
/* Function:    EnterNewSponsorshipLevelIntoDatabase                        */
/* Description: Adds a sponsorship level to the posted event.               */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function EnterNewSponsorshipLevelIntoDatabase() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    $fields = CheckSponsorshipLevelFields($pdo, $event['EVENT_ID'], 0);
    $active = (($_POST['sponsorship_active'] ?? '1') === '0') ? 0 : 1;

    try {
        $pdo->prepare("INSERT INTO nsx_meetup_sponsorship_levels (EVENT_ID, LEVEL_Active, LEVEL_Type, LEVEL_Name, LEVEL_Amount, LEVEL_Logosize, LEVEL_Logosize_Description) VALUES (?, ?, 'corporate', ?, ?, ?, ?)")
            ->execute([(int)$event['EVENT_ID'], $active, $fields['name'], $fields['amount'], $fields['logosize'], SPONSOR_LOGO_SIZES[$fields['logosize']]]);
    } catch (PDOException $e) {
        error_log('EnterNewSponsorshipLevelIntoDatabase failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The level could not be saved. Please try again.']);
    }
    EventAdminRespond('Pass', 'New sponsorship level saved.');
}

?>