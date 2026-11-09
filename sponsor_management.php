<?php
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventImages.php';  // EventSponsorLogoDirectory(), CheckFrontpageImageUpload(), SaveFrontpageImage()

require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
const SPONSOR_METHODS = [
    'RenderEditIndividualSponsorshipLevelInformation' => 'admin',
    'RenderEditIndividualSponsorInformation'          => 'event_admin',
    'UpdateSponsorDetails'                            => 'event_admin',
    'UpdateSponsorshipLevelDetails'                   => 'admin',
    'UpdateSponsorshipActiveStatus'                   => 'admin',
    'UpdateSponsorshipLevelActiveStatus'              => 'admin',
    'UpdateSponsorDescription'                        => 'admin',
    'RenderAddNewSponsorshipLevelSection'             => 'admin',
    'RenderAddNewSponsorSection'                      => 'event_admin',
    'EnterNewSponsorIntoDatabase'                     => 'event_admin',
    'UploadSponsorLogo'                               => 'event_admin',
    'EnterNewSponsorshipLevelIntoDatabase'            => 'admin',
];
EndpointDispatch(__FILE__, SPONSOR_METHODS);

/* ************************************************************************ */
/* Function:    RenderEditIndividualSponsorshipLevelInformation 			*/
/* Description: This function will takes changed data and updates the table */
/*              specified with the changred information.                    */
/* Inputs:      table_name = The table to update.                           */
/*              query_data = String of the JSON object containing the keys  */
/*              to update and the corresponding values.                     */
/* Returns:     string                                        				*/
/* ************************************************************************ */  
function RenderEditIndividualSponsorshipLevelInformation() {
    $results = array();
    $level_id = $_POST['level_id'];

	$link = connectDB('kirbypar_nsx_meetup');
    $output_html = '<h2> Edit Sponsorship Level Information</h2>';  
    $output_html .= '<p>This is the sponsorship level management interface. Here, you will be able to edit the properties of all the existing sponsorsship levels in the database. These properties include the basic things, such as name and web link information for a sponsor, to advanced details such as sposnorship level and logos.
    Additionally, you will be able to set the active status of a current or past sponsorship levels. Only active levels are displayed on the web site.</p>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>'; 
    $output_html .= '<div id="individual_sponsorship_level_edit_container" level_id="'.$level_id.'" class="row gtr-50">';

	$query = sprintf("SELECT * FROM `nsx_meetup_sponsorship_levels` WHERE `LEVEL_ID` = %d",$level_id);
	$result = $link->query($query);
    if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
	}

    /* ---------------------------------------------------------------- */
    /* Sponsorship Level Text Input information                         */
    /* ---------------------------------------------------------------- */

    $output_html .= '<table>';
    /* Sponsorship Level Name */
    $output_html .= '<tr><td class="sponsorship_level_edit_name">Sponsorship Level Name</td><td><input type="text" id="sponsorship_level_edit_infobox_name" db_key="LEVEL_Name" current_value="'.$row['LEVEL_Name'].'" value="'.$row['LEVEL_Name'].'" /></td><tr>';

    /* Sponsorship Level Amount */
    $output_html .= '<tr><td class="sponsorship_level_edit_amount">Sponsorship Level Amount</td><td><input type="text" id="sponsorship_level_edit_infobox_amount" db_key="LEVEL_Amount" current_value="'.$row['LEVEL_Amount'].'" value="'.$row['LEVEL_Amount'].'" /></td><tr>';

    /* Sponsorship Logo Size */
    $output_html .= '<tr><td class="sponsorship_level_edit_logosize">Sponsorship Level Logosize</td>';
    $output_html .= '<td>';
    $output_html .= '<select name="sponsorship_logosize_dropdown" id="sponsorship_level_info_dropdown_logosize" current_level="'.$row['LEVEL_Logosize'].'">';
    $output_html .= '   <option value="title" '.($row['LEVEL_Logosize'] == "title"?" selected":"").'>Page Width</option>';
    $output_html .= '   <option value="large" '.($row['LEVEL_Logosize'] == "large"?" selected":"").'>1/3 Page Width</option>';
    $output_html .= '   <option value="medium" '.($row['LEVEL_Logosize'] == "medium"?" selected":"").'>1/4 Page Width</option>';
    $output_html .= '   <option value="small" '.($row['LEVEL_Logosize'] == "small"?" selected":"").'>1/6 Page Width</option>';
    $output_html .= '</select>';
    $output_html .= '</td></tr>';   


    $output_html .= '</table>';

    $output_html .= '<div class="col-12 col-12-small"><a href="#" id="save_level_changes_button" class="button primary disabled" level_id="'.$level_id.'">Save Changes</a></div>';
    // $output_html .= '<div class="row gtr-50">';
    // $output_html .= '   <div class="col-4 col-4-medium col-12-small">';
 	// $output_html .= '       <a href="#" id="save_level_changes_button" table="nsx_meetup_event_info" level_id="'.$level_id.'" class="button primary disabled small">Save Changes</a>';
    // $output_html .= '   </div>';
    // $output_html .= '   <div class="col-8 col-8-medium col-12-small">';
    // $output_html .= '       <span style="padding:auto;text-align:center;" id="save_changes_button_status"></span>';
    // $output_html .= '   </div>';    
    // $output_html .= '</div>';

    $output_html .= '</div>';

	$results[] = $output_html;
	echo json_encode($results);           
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

    $output_html .= '<div class="row gtr-50">';
    $output_html .= '   <div class="col-4 col-4-medium col-12-small">';
 	$output_html .= '       <a href="#" id="save_changes_button" table="nsx_meetup_event_info" sponsor_id="'.$sponsor_id.'" class="button primary disabled small">Save Changes</a>';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-8 col-8-medium col-12-small">';
    $output_html .= '       <span style="padding:auto;text-align:center;" id="save_changes_button_status"></span>';
    $output_html .= '   </div>';    
    $output_html .= '</div>';

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

    /* Description Editor */
    // $output_html .= '<textarea name="test_description" id="sponsor_edit_description_'.$row['SPONSOR_ID'].'" changes_made="no">'.$row['SPONSOR_Description'].'</textarea>';
    $output_html .= '<textarea name="test_description" id="sponsor_edit_section_'.$sponsor_id.'" sponsor_id="'.$sponsor_id.'" changes_made="no">'.$h($row['SPONSOR_Description']).'</textarea>';

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
        'sponsor_division' => $sponsor_division
    ];

    $sql = "UPDATE `nsx_meetup_event_sponsors` SET `SPONSOR_Name`=:sponsor_name, `SPONSOR_Weblink`=:sponsor_weblink, `SPONSOR_Level`=:sponsor_level, `SPONSOR_Division`=:sponsor_division WHERE SPONSOR_ID=:sponsor_id";
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
/* Function:    UpdateSponsorshipLevelDetails                           				*/
/* Description: This function will update the sponsor details of Name,      */
/*              weblink and sponsor level                     .             */
/* Inputs:      sponsor_id = The sponsor tp update.                         */
/*              sponsor_name = Name of the sponsor.                         */
/*              sponsor_weblink = The sponsors web page link.               */
/*              sponsor_level = The level of the sponsor.                   */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */  
function UpdateSponsorshipLevelDetails() {
    $sponsorship_id = $_POST['sponsorship_id'];
    $sponsorship_name = $_POST['sponsorship_name'];
    $sponsorship_amount = $_POST['sponsorship_amount'];
    $sponsorship_logosize = $_POST['sponsorship_logosize'];
    $sponsorship_logosize_description = $_POST['sponsorship_logosize_description'];

    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');

    $data = [
        'sponsorship_id' => $sponsorship_id,
        'sponsorship_name' => $sponsorship_name,
        'sponsorship_amount' => $sponsorship_amount,
        'sponsorship_logosize' => $sponsorship_logosize,
        'sponsorship_logosize_description' => $sponsorship_logosize_description
    ];

    $sql = "UPDATE `nsx_meetup_sponsorship_levels` SET `LEVEL_Name`=:sponsorship_name, `LEVEL_Amount`=:sponsorship_amount, `LEVEL_Logosize`=:sponsorship_logosize, `LEVEL_Logosize_Description`=:sponsorship_logosize_description WHERE LEVEL_ID=:sponsorship_id";
    $query = $dbh->prepare($sql);
    try {
        $dbh->beginTransaction();
        // $sth->execute();
        $query->execute($data);    
        $results[] = 'success';
        $results[] = 'The details for '.$sponsorship_name.' were updated successfully.';
        $dbh->commit();
    } catch(PDOExecption $e) {
        $dbh->rollback();
        $results[] = 'fail';
        $results[] = $e->getMessage();
    }    

    $results[] = $output_html;
	echo json_encode($results); 
}  


/* ************************************************************************ */
/* Function:    UpdateSponsorshipActiveStatus                				*/
/* Description: This function will simple changes the value of the active   */
/*              state of a sponsor based upon the radio button.             */
/* Inputs:      sponsor_id = The sponsor tp update.                         */
/*              sponsor_state = 1 = Active : 0 = Not Active                 */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */  
function UpdateSponsorshipActiveStatus() {
    $sponsor_id = $_POST['sponsor_id'];
    $sponsor_state = $_POST['sponsor_state'];
    $event_id = $_POST['event_id'];
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $data = [
        'sponsor_state' => $sponsor_state,
        'sponsor_id' => $sponsor_id,
        'event_id' => $event_id
    ];

    $sql = "UPDATE `nsx_meetup_event_sponsors` SET SPONSOR_Active=:sponsor_state WHERE SPONSOR_ID=:sponsor_id AND SPONSOR_Event_IDs=:event_id";
    $query = $dbh->prepare($sql);
    try {
        $dbh->beginTransaction();
        $query->execute($data);    
        $results[] = 'success';
        $dbh->commit();
    } catch(PDOExecption $e) {
        $dbh->rollback();
        $results[] = 'fail';
        $results[] = $e->getMessage();
    }    

    $results[] = $output_html;
	echo json_encode($results); 
}    

/* ************************************************************************ */
/* Function:    UpdateSponsorshipLevelActiveStatus                		    */
/* Description: This function will simple changes the value of the active   */
/*              state of a sponsorship level based upon the radio button.   */
/* Inputs:      level_id = The sponsorship level to update.                 */
/*              sponsorship_state = 1 = Active : 0 = Not Active             */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */  
function UpdateSponsorshipLevelActiveStatus() {
    $level_id = $_POST['level_id'];
    $sponsorship_state = $_POST['sponsorship_state'];
    $event_id = $_POST['event_id'];
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $data = [
        'sponsorship_state' => $sponsorship_state,
        'level_id' => $level_id,
        'event_id' => $event_id
    ];

    $sql = "UPDATE `nsx_meetup_sponsorship_levels` SET LEVEL_Active=:sponsorship_state WHERE LEVEL_ID=:level_id AND EVENT_ID=:event_id";
    $query = $dbh->prepare($sql);
    try {
        $dbh->beginTransaction();
        $query->execute($data);    
        $results[] = 'success';
        $dbh->commit();
    } catch(PDOExecption $e) {
        $dbh->rollback();
        $results[] = 'fail';
        $results[] = $e->getMessage();
    }    

    $results[] = $output_html;
	echo json_encode($results); 
} 

/* ************************************************************************ */
/* Function:    UpdateSponsorDescription                    				*/
/* Description: This function will update the text entered to describe a    */
/*              sponsor in the database.                                    */
/* Inputs:      sponsor_id = The sponsor tp update.                         */
/*              textdata = The data to update in text form.                 */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */  
function UpdateSponsorDescription() {
    $sponsor_id = $_POST['sponsor_id'];
    $textdata = $_POST['textdata'];
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $data = [
        'textdata' => $textdata,
        'sponsor_id' => $sponsor_id
    ];

    $sql = "UPDATE `nsx_meetup_event_sponsors` SET SPONSOR_Description=:textdata WHERE SPONSOR_ID=:sponsor_id";
    $query = $dbh->prepare($sql);
    try {
        $dbh->beginTransaction();
        $query->execute($data);    
        $results[] = 'success';
        $results[] = $query;
        $dbh->commit();
    } catch(PDOExecption $e) {
        $dbh->rollback();
        $results[] = 'fail';
        $results[] = $e->getMessage();
    }    

    $results[] = $output_html;
	echo json_encode($results); 
}  


/* ************************************************************************ */
/* Function:    RenderAddNewSponsorshipLevelSection            				*/
/* Description: This function will return the form needed to enter a new    */
/*              sponsorship level into the database.                        */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderAddNewSponsorshipLevelSection() {
    $results = array();
    $event_id = $_POST['event_id'];
	
	$output_html = '<div id="add_sponsorship_level_container">';
    $output_html .= '<h2> Create Sponsorship Levels</h2><p>This section allows you to add a new sponsorship level to the database. Levels can be edited and set to active / inactive at any time. Enter the 
    required information below and click save to add the sponsor to the database.</p></div>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';

    $output_html .= '<div id="main_sponsorship_info_container" class="row gtr gtr-uniform">';
    $output_html .= '   <div class="col-6 col-6-medium col-12-small">';
 	$output_html .= '       <input type="text" id="sponsorship_level_infobox_type" db_key="LEVEL_Type" value="Corporate" readonly hidden />';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-6 col-6-medium col-12-small">';
    $output_html .= '       <input type="text" id="sponsorship_level_infobox_name" db_key="LEVEL_Name" value="" placeholder="Enter the Level Name"/>';
    $output_html .= '   </div>';  
    $output_html .= '   <div class="col-3 col-3-medium col-12-small">';
    $output_html .= '       <input type="text" id="sponsorship_level_infobox_amount" db_key="LEVEL_Amount" value="" placeholder="Enter the Level Amount"/>';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-3 col-3-medium col-12-small">';
    $output_html .= '       <select name="sponsorship_logosize_dropdown" id="sponsorship_level_info_dropdown_logosize">';
    $output_html .= '           <option value="none" selected disabled hidden>Select a Logo Size</option>';
    $output_html .= '           <option value="title">Page Width</option>';
    $output_html .= '           <option value="large">1/3 Page Width</option>';
    $output_html .= '           <option value="medium">1/4 Page Width</option>';
    $output_html .= '           <option value="small">1/6 Page Width</option>';
    $output_html .= '       </select>';
    $output_html .= '   </div>';        
    $output_html .= '   <div class="col-2 col-2-medium col-6-small">';
    $output_html .= '       Active Level: ';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-3 col-3-medium col-6-small">';      
    $output_html .= '       <input type="radio" id="active_sponsorship_level_on" name="active_sponsorship_level_radio_button" value="1">';
    $output_html .= '       <label for="active_sponsorship_level_on">Yes</label>';
    $output_html .= '       <input type="radio" id="active_sponsoship_level_off" name="active_sponsorship_level_radio_button" value="0">';
    $output_html .= '       <label for="active_sponsoship_level_off">No</label>';
    $output_html .= '   </div>';     
    $output_html .= '   <div class="col-12 col-12-medium col-12-small">';
    $output_html .= '       <a href="#" id="save_new_sponsorship_level_botton" event_id="'.$event_id.'" class="button primary" style="margin-top:1.5em;">Save New Level</a>';
    $output_html .= '   </div>'; 
    $output_html .= '</div>';     

    $results[] = $output_html;
	echo json_encode($results);     
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
/* Funciton:    EnterNewSponsorshipLevelIntoDatabase                   	    */
/* Description: This function creates the sponsorship level for the         */
/*              database. If successful, return success.                    */
/* Returns:     Nothing                                        			    */
/* ************************************************************************ */
function EnterNewSponsorshipLevelIntoDatabase() {
    $event_id = $_POST['event_id'];
    $sponsorship_type = $_POST['sponsorship_type'];
    $sponsorship_name = $_POST['sponsorship_name'];
    $sponsorship_amount = $_POST['sponsorship_amount'];
    $sponsorship_active = intval($_POST['sponsorship_active']);
    $sponsorship_logosize = $_POST['sponsorship_logosize'];
    $sponsorship_logosize_description = $_POST['sponsorship_logosize_description'];


    try {
      $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
      $insert_query = "INSERT INTO `nsx_meetup_sponsorship_levels` 
      (`EVENT_ID`,`LEVEL_Active`,`LEVEL_Type`, `LEVEL_Name`,`LEVEL_Amount`,`LEVEL_Logosize`,`LEVEL_Logosize_Description`)
       VALUES (:event_id,:sponsorship_active,'corporate',:sponsorship_name,:sponsorship_amount,:sponsorship_logosize,:sponsorship_logosize_description)";
      $sth = $dbh->prepare($insert_query);

      $sth->bindParam(':event_id', $event_id);
      $sth->bindParam(':sponsorship_active', $sponsorship_active);
      $sth->bindParam(':sponsorship_name', $sponsorship_name);
      $sth->bindParam(':sponsorship_amount', $sponsorship_amount);
      $sth->bindParam(':sponsorship_logosize', $sponsorship_logosize);
      $sth->bindParam(':sponsorship_logosize_description', $sponsorship_logosize_description);
      $dbh->beginTransaction();
      $sth->execute();
      $results[] = 'success';
      $results[] = '<span style="color:#ffcc00;">New Sponsor Entered Successfully</span>';
      $dbh->commit();
      
    } 
    catch ( PDOExecption $e ) {
      $results[] = 'failed';
      $results[] = $e->getMessage();
      throw $e;
    }
      echo json_encode($results);  
  }

?>