<?php
require_once __DIR__ . '/databaseFunctions.php';

require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
const SPONSOR_METHODS = [
    'RenderEditIndividualSponsorshipLevelInformation' => 'admin',
    'RenderEditIndividualSponsorInformation'          => 'admin',
    'UpdateSponsorDetails'                            => 'admin',
    'UpdateSponsorshipLevelDetails'                   => 'admin',
    'UpdateSponsorshipActiveStatus'                   => 'admin',
    'UpdateSponsorshipLevelActiveStatus'              => 'admin',
    'UpdateSponsorDescription'                        => 'admin',
    'RenderAddNewSponsorshipLevelSection'             => 'admin',
    'RenderAddNewSponsorSection'                      => 'admin',
    'EnterNewSponsorIntoDatabase'                     => 'admin',
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
    $results = array();
    $sponsor_id = $_POST['sponsor_id'];

	$link = connectDB('kirbypar_nsx_meetup');
	
	$output_html = '<div id="individual_sponsor_edit_container" sponsor_id="'.$sponsor_id.'" class="row gtr-50">';
    $output_html .= '<div id="back_navigation_button"></div>';   
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<p>This is the sponsor management interface. Here, you will be able to edit the properties of all the existing sponsors in the le Tour de Femme
    sponsors database. These properties include the basic things, such as name and web link information for a sponsor, to advanced details such as sposnorship level and logos.
    Additionally, you will be able to set the active status of a current or past sponsor. Only active sponsors are displayed on the web site. Finally, you will be able
    to enter details for new sponsors as we gain them.</p>';
    $output_html .= '</div>';
    $output_html .= '<div class="col-6 col-6-medium col-12-small">';
    $output_html .= '<table><thead><tr><th>Sponsor Property</th><th>Current Setting</th></tr></thead></tbody>';
    
	$query = sprintf("SELECT * FROM `nsx_meetup_event_sponsors` WHERE `SPONSOR_ID` = %d",$sponsor_id);
	$result = $link->query($query);
    if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
	}

    /* ---------------------------------------------------------------- */
    /* Sponsor Text Input information                                   */
    /* ---------------------------------------------------------------- */

    /* Sponsor Name */
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Name</td><td><input type="text" id="sponsor_edit_infobox_name" db_key="SPONSOR_Name" current_value="'.$row['SPONSOR_Name'].'" value="'.$row['SPONSOR_Name'].'" /></td><tr>';

    /* Sponsor Web Link */
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Web Page / Link</td><td><input type="text" id="sponsor_edit_infobox_weblink" db_key="SPONSOR_Weblink" current_value="'.$row['SPONSOR_Weblink'].'" value="'.$row['SPONSOR_Weblink'].'" /></td><tr>';


    /* ---------------------------------------------------------------- */
    /* Sponsor Drop Down Input information                              */
    /* ---------------------------------------------------------------- */

    /* Sponsor Level */
    $output_html .= '<tr><td class="sponsor_edit_title">Sponsor Level</td>
        <td>
        <select name="sponsor_level_dropdown" id="sponsor_edit_infodropdown_level" current_level="'.$row['SPONSOR_Level'].'">
            <option value="Senna" '.($row['SPONSOR_Level'] == "Senna"?" selected":"").'>Senna</option>
            <option value="Gold" '.($row['SPONSOR_Level'] == "Gold"?" selected":"").'>Gold</option>
            <option value="Silver" '.($row['SPONSOR_Level'] == "Silver"?" selected":"").'>Silver</option>
            <option value="Bronze" '.($row['SPONSOR_Level'] == "Bronze"?" selected":"").'>Bronze</option>
            <option value="In-Kind" '.($row['SPONSOR_Level'] == "In-Kind"?" selected":"").'>In-Kind</option>
        </select>
        </td></tr>';   




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
    // Sponsor Image Section
    $output_html .= '<div class="col-6 col-6-medium col-12-small">';
    $output_html .= '<p>Coming Soon, the ability to upload / change the sponsor logo.</p>';
    $output_html .= '</div>';

    /* Description Editor */
    // $output_html .= '<textarea name="test_description" id="sponsor_edit_description_'.$row['SPONSOR_ID'].'" changes_made="no">'.$row['SPONSOR_Description'].'</textarea>';
    $output_html .= '<textarea name="test_description" id="sponsor_edit_section_'.$row['SPONSOR_ID'].'" sponsor_id="'.$row['SPONSOR_ID'].'" changes_made="no">'.$row['SPONSOR_Description'].'</textarea>';

	closeDB($link);

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
    $sponsor_id = $_POST['sponsor_id'];
    $sponsor_name = $_POST['sponsor_name'];
    $sponsor_weblink = $_POST['sponsor_weblink'];
    $sponsor_level = $_POST['sponsor_level'];

    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');


    $logosize = [
        'Senna' => "title",
        'Gold' => "large",        
        'Silver' => "medium",
        'Bronze' => "small",        
        'In-Kind' => "small"        
    ];

    $sponsor_division = $logosize[$sponsor_level];
    $data = [
        'sponsor_id' => $sponsor_id,
        'sponsor_name' => $sponsor_name,
        'sponsor_weblink' => $sponsor_weblink,
        'sponsor_level' => $sponsor_level,
        'sponsor_division' => $sponsor_division
    ];

    $sql = "UPDATE `nsx_meetup_event_sponsors` SET `SPONSOR_Name`=:sponsor_name, `SPONSOR_Weblink`=:sponsor_weblink, `SPONSOR_Level`=:sponsor_level, `SPONSOR_Division`=:sponsor_division WHERE SPONSOR_ID=:sponsor_id";
    $query = $dbh->prepare($sql);
    try {
        $dbh->beginTransaction();
        // $sth->execute();
        $query->execute($data);    
        $results[] = 'success';
        $results[] = 'The details for '.$sponsor_name.' were updated successfully.';
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
/* Function:    RenderAddNewSponsorSection                    				*/
/* Description: This function will return the form needed to enter a new    */
/*              sponsor into the database.                                  */
/* Returns:     nothing                                        				*/
/* ************************************************************************ */
function RenderAddNewSponsorSection() {
    $results = array();

	$output_html = '<div id="add_sponsor_container" class="row gtr gtr-uniform">';

    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<p>This section allows you to add a new sponsor to the database. Sponsors can be edited and set to active / inactive at any time. Enter the 
    required information below and click save to add the sponsor to the database.<br><br>
    <span style="font-weight:800;color:#ff0000;">NOTE:</span> The interface currently does not support the uploading of images (logos). Please email Ron the image
    you want to use and he will add it to the database manually until the upload process is enabled.</p>';
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
    $output_html .= '   <option value="Title" >Title Sponsor</option>';
    $output_html .= '   <option value="Senna">Senna</option>';
    $output_html .= '   <option value="Gold">Gold</option>';
    $output_html .= '   <option value="Silver">Silver</option>';
    $output_html .= '   <option value="Bronze">Bronze</option>';
    $output_html .= '   <option value="In-Kind">In-Kind</option>';
    $output_html .= '   </select>';
    $output_html .= '</div>';  
    $output_html .= ' <div class="col-2 col-2-medium col-6-small">';
    $output_html .= 'Active Sponsor: ';
    $output_html .= '</div>';
    $output_html .= ' <div class="col-3 col-3-medium col--small">';      
    $output_html .= '<input type="radio" id="active_sponsor_on" name="active_sponsor_radio_button" value="1">';
    $output_html .= '<label for="active_sponsor_on">Yes</label>';
    $output_html .= '<input type="radio" id="active_sponsor_off" name="active_sponsor_radio_button" value="0">';
    $output_html .= '<label for="active_sponsor_off">No</label>';   
    $output_html .= '</div>';    
    $output_html .= ' <div class="col-12 col-12-medium col--small">';      
    $output_html .= '<textarea name="test_description" id="new_sponsor_edit_section"></textarea>';
    $output_html .= '</div>';
    $output_html .= '</div>';
    $output_html .= '<div id="save_status_section" style="display:inline-flex;">';
    $output_html .= '<br><a href="#" id="save_new_sponsor_information_botton" class="button primary" style="margin-top:1.5em;">Save New Sponsor</a>';
    $output_html .= "</div>";    

    $results[] = $output_html;
	echo json_encode($results);     
}

/* ************************************************************************ */
/* Funciton:    EnterInvitationIntoDatabase                            	    */
/* Description: This function creates the new user information in the LTDF  */
/*              database. If successful, return success.                    */
/* Returns:     Nothing                                        			    */
/* ************************************************************************ */
function EnterNewSponsorIntoDatabase() {
    $sponsor_name = $_POST['sponsor_name'];
    $sponsor_weblink = $_POST['sponsor_weblink'];
    $sponsor_level = $_POST['sponsor_level'];
    $sponsor_active = intval($_POST['sponsor_active']);
    $sponsor_description = $_POST['sponsor_description'];

    $logosize = [
        'Senna' => "title",
        'Gold' => "large",        
        'Silver' => "medium",
        'Bronze' => "small",        
        'In-Kind' => "small" 
    ];

    $sponsor_division = $logosize[$sponsor_level];

    try {
      $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
      $insert_query = "INSERT INTO `nsx_meetup_event_sponsors` 
      (`SPONSOR_Type`,`SPONSOR_Active`,`SPONSOR_Name`, `SPONSOR_Weblink`, `SPONSOR_Level`, `SPONSOR_Division`, `SPONSOR_Description`,`SPONSOR_Large_Logo`,`SPONSOR_Thumbnail_Logo`)
       VALUES ('corporate',:sponsor_active,:sponsor_name,:sponsor_weblink,:sponsor_level,:sponsor_division,:sponsor_description,'sponsor-coming-soon-front.jpg','sponsor-coming-soon-front.jpg')";
      $sth = $dbh->prepare($insert_query);

      $sth->bindParam(':sponsor_active', $sponsor_active);
      $sth->bindParam(':sponsor_name', $sponsor_name);
      $sth->bindParam(':sponsor_weblink', $sponsor_weblink);
      $sth->bindParam(':sponsor_level', $sponsor_level);
      $sth->bindParam(':sponsor_division', $sponsor_division);
      $sth->bindParam(':sponsor_description', $sponsor_description);    
      $dbh->beginTransaction();
      $sth->execute();
      $results[] = 'success';
      $results[] = '<div><a href="#" id="edit_sponsor_information" class="button primary">Back</a></div><div style="padding-left:1em;">New Sponsor Entered Successfully</div>';
      $dbh->commit();
      
    } 
    catch ( PDOExecption $e ) {
      $results[] = 'failed';
      $results[] = $e->getMessage();
      throw $e;
    }
      echo json_encode($results);  
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