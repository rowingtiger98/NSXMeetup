<?php
  require_once __DIR__ . '/databaseFunctions.php';
  require_once __DIR__ . '/eventAdmin.php';  // EventAdminAuthorize() - event admins only
  require_once __DIR__ . '/registrantHistory.php';  // RenderRegistrantHistory()

  require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
  const PARTICIPANT_METHODS = [
      'RenderParticipantInformation' => 'event_admin',
  ];
  EndpointDispatch(__FILE__, PARTICIPANT_METHODS);

/* ************************************************************************ */
/* Funciton:    RenderParticipantInformation                            	*/
/* Description: This function renders the find / edit participant info.		*/
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderParticipantInformation() {  
	$event_id = (int)($_POST['event_id'] ?? 0);
	$participant_id = (int)($_POST['participant_id'] ?? 0);
    $collect_payment = (($_POST['collect_payment'] ?? '') === 'yes') ? 'yes' : 'no';

    // A registrant's details - event admins (or overlords) only
    app_session_start();
    EventAdminAuthorize(Create_SQL_PHP_DataObject(DB_NAME), $event_id);
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
	$output_html = '';
    // The current session's CSRF token for Update Information (read by UpdateParticipantInformation in account.js)
    $output_html = '<input type="hidden" id="edit_participant_csrf_token" value="'.$h(app_csrf_token()).'" />';
    $output_html .= '<div id="back_navigation_button">';
    $output_html .= '<a href="#" id="back_button_link" action="backtoparticipantsinformation" event_id="'.$event_id.'" participant_id="'.$participant_id.'" collect_payment="'.$collect_payment.'" class="button primary">Cancel</a>';
    $output_html .= '</div>';

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
			
	/************************************************/
	/* Search each registered "User" one at a time. */
	/************************************************/

	$user_query = sprintf("SELECT * FROM `nsx_meetup_participants` WHERE `Participant_ID`='%s'", $participant_id);
	$result = $link->query($user_query);
	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		
		$output_html .= '<div id="driver_one_contact_container" class="event_registration_sub_container active">';
		$output_html .= '    <div id="event_registration_sub_container_header_driver1" class="event_registration_sub_container_header">';
		$output_html .= '	</div>';
        $output_html .= '    <div class="row gtr-25 gtr-uniform">';
        $output_html .= '    	<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="name" id="info_text_field_driver1_name" value="'.$h($row['Participant_Name']).'" start_value="'.$h($row['Participant_Name']).'" database_key = "Participant_Name" placeholder="Name" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="email" id="info_text_field_driver1_email" value="'.$h($row['Participant_Email']).'" start_value="'.$h($row['Participant_Email']).'" database_key = "Participant_Email" placeholder="Email Address" />';
        $output_html .= '    	</div>';
		$output_html .= '		<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="cell" id="info_text_field_driver1_cell" value="'.$h($row['Participant_Cell']).'" start_value="'.$h($row['Participant_Cell']).'" database_key = "Participant_Cell" placeholder="Cell Phone Number" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-5 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="street" id="info_text_field_driver1_street" value="'.$h($row['Participant_Address']).'" start_value="'.$h($row['Participant_Address']).'" database_key = "Participant_Address" placeholder="Street Address" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-3 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="city" id="info_text_field_driver1_city" value="'.$h($row['Participant_City']).'" start_value="'.$h($row['Participant_City']).'" database_key = "Participant_City" placeholder="City" />';
        $output_html .= '    	</div>';
		$output_html .= '		<div class="col-2 col-12-xsmall">';
        $output_html .= '            <select name="state" id="info_select_field_driver1_state" start_value="'.$h($row['Participant_State']).'" database_key = "Participant_State" style="color:rgba(255, 255, 255, 0.7)";>';
        $output_html .= '            	<option value="AL"'.($row['Participant_State']=="AL"?" selected":"").'>AL</option>';
        $output_html .= '            	<option value="AK"'.($row['Participant_State']=="AK"?" selected":"").'>AK</option>';
        $output_html .= '            	<option value="AZ"'.($row['Participant_State']=="AZ"?" selected":"").'>AZ</option>';
        $output_html .= '            	<option value="AR"'.($row['Participant_State']=="AR"?" selected":"").'>AR</option>';
        $output_html .= '            	<option value="CA"'.($row['Participant_State']=="CA"?" selected":"").'>CA</option>';
        $output_html .= '            	<option value="CO"'.($row['Participant_State']=="CO"?" selected":"").'>CO</option>';
        $output_html .= '            	<option value="CT"'.($row['Participant_State']=="CT"?" selected":"").'>CT</option>';
        $output_html .= '            	<option value="DE"'.($row['Participant_State']=="DE"?" selected":"").'>DE</option>';
        $output_html .= '            	<option value="DC"'.($row['Participant_State']=="DC"?" selected":"").'>DC</option>';
        $output_html .= '            	<option value="FL"'.($row['Participant_State']=="FL"?" selected":"").'>FL</option>';
        $output_html .= '            	<option value="GA"'.($row['Participant_State']=="GA"?" selected":"").'>GA</option>';
        $output_html .= '            	<option value="HI"'.($row['Participant_State']=="HI"?" selected":"").'>HI</option>';
        $output_html .= '            	<option value="ID"'.($row['Participant_State']=="ID"?" selected":"").'>ID</option>';
        $output_html .= '            	<option value="IL"'.($row['Participant_State']=="IL"?" selected":"").'>IL</option>';
        $output_html .= '            	<option value="IN"'.($row['Participant_State']=="IN"?" selected":"").'>IN</option>';
        $output_html .= '            	<option value="IA"'.($row['Participant_State']=="IA"?" selected":"").'>IA</option>';
        $output_html .= '            	<option value="KS"'.($row['Participant_State']=="KS"?" selected":"").'>KS</option>';
        $output_html .= '            	<option value="KY"'.($row['Participant_State']=="KY"?" selected":"").'>KY</option>';
        $output_html .= '            	<option value="LA"'.($row['Participant_State']=="LA"?" selected":"").'>LA</option>';
        $output_html .= '            	<option value="ME"'.($row['Participant_State']=="ME"?" selected":"").'>ME</option>';
        $output_html .= '            	<option value="MD"'.($row['Participant_State']=="MD"?" selected":"").'>MD</option>';
        $output_html .= '            	<option value="MA"'.($row['Participant_State']=="MA"?" selected":"").'>MA</option>';
        $output_html .= '            	<option value="MI"'.($row['Participant_State']=="MI"?" selected":"").'>MI</option>';
        $output_html .= '            	<option value="MN"'.($row['Participant_State']=="MN"?" selected":"").'>MN</option>';
        $output_html .= '            	<option value="MS"'.($row['Participant_State']=="MS"?" selected":"").'>MS</option>';
        $output_html .= '            	<option value="MO"'.($row['Participant_State']=="MO"?" selected":"").'>MO</option>';
        $output_html .= '            	<option value="MT"'.($row['Participant_State']=="MT"?" selected":"").'>MT</option>';
        $output_html .= '            	<option value="NE"'.($row['Participant_State']=="NE"?" selected":"").'>NE</option>';
        $output_html .= '            	<option value="NV"'.($row['Participant_State']=="NV"?" selected":"").'>NV</option>';
        $output_html .= '            	<option value="NH"'.($row['Participant_State']=="NH"?" selected":"").'>NH</option>';
        $output_html .= '            	<option value="NJ"'.($row['Participant_State']=="NJ"?" selected":"").'>NJ</option>';
        $output_html .= '            	<option value="NM"'.($row['Participant_State']=="NM"?" selected":"").'>NM</option>';
        $output_html .= '            	<option value="NY"'.($row['Participant_State']=="NY"?" selected":"").'>NY</option>';
        $output_html .= '            	<option value="NC"'.($row['Participant_State']=="NC"?" selected":"").'>NC</option>';
        $output_html .= '            	<option value="ND"'.($row['Participant_State']=="ND"?" selected":"").'>ND</option>';
        $output_html .= '            	<option value="OH"'.($row['Participant_State']=="OH"?" selected":"").'>OH</option>';
        $output_html .= '            	<option value="OK"'.($row['Participant_State']=="OK"?" selected":"").'>OK</option>';
        $output_html .= '            	<option value="OR"'.($row['Participant_State']=="OR"?" selected":"").'>OR</option>';
        $output_html .= '            	<option value="PA"'.($row['Participant_State']=="PA"?" selected":"").'>PA</option>';
        $output_html .= '            	<option value="RI"'.($row['Participant_State']=="RI"?" selected":"").'>RI</option>';
        $output_html .= '            	<option value="SC"'.($row['Participant_State']=="SC"?" selected":"").'>SC</option>';
        $output_html .= '            	<option value="SD"'.($row['Participant_State']=="SD"?" selected":"").'>SD</option>';
        $output_html .= '            	<option value="TN"'.($row['Participant_State']=="TN"?" selected":"").'>TN</option>';
        $output_html .= '            	<option value="TX"'.($row['Participant_State']=="TX"?" selected":"").'>TX</option>';
        $output_html .= '            	<option value="UT"'.($row['Participant_State']=="UT"?" selected":"").'>UT</option>';
        $output_html .= '            	<option value="VT"'.($row['Participant_State']=="VT"?" selected":"").'>VT</option>';
        $output_html .= '            	<option value="VA"'.($row['Participant_State']=="VA"?" selected":"").'>VA</option>';
        $output_html .= '            	<option value="WA"'.($row['Participant_State']=="WA"?" selected":"").'>WA</option>';
        $output_html .= '            	<option value="WV"'.($row['Participant_State']=="WV"?" selected":"").'>WV</option>';
        $output_html .= '            	<option value="WI"'.($row['Participant_State']=="WI"?" selected":"").'>WI</option>';
        $output_html .= '            	<option value="WY"'.($row['Participant_State']=="WY"?" selected":"").'>WY</option>';
        $output_html .= '            </select>								';
        $output_html .= '    	</div>';
		$output_html .= '		<div class="col-2 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="zip" id="info_text_field_driver1_zip" value="'.$h($row['Participant_Zip']).'" start_value="'.$h($row['Participant_Zip']).'" database_key = "Participant_Zip" placeholder="Zip" />';
        $output_html .= '    	</div>';
		

		// Riders and children have no car of their own - no generation / year / color questions
		$is_passenger = in_array($row['Participant_Role'], ['riding', 'child'], true);
		if (!$is_passenger) {
		$car_information = (json_decode((string)$row['Participant_Car_Info'], true) ?: []) + ['car_year' => '', 'car_color' => '', 'car_make' => '', 'car_model' => ''];
		if ($row['Participant_Is_NSX']) {
			$generation_info = intval($car_information['car_year'] ?? 0) < 2017 ? 'First_Generation' : 'Second_Generation';
		} else {
			$generation_info = 'Other';
		}

        $output_html .= '    	<div id ="info_select_field_driver1_is_nsx" start_value="'.$h($row['Participant_Is_NSX']).'" database_key = "Participant_Is_NSX" class="col-4 col-12-xsmall">';
        $output_html .= '    		<select id="nsx_gen_response_gen_selection_driver1" name="generation_selection_driver1" driver="driver1" start_value="'.$generation_info.'" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>';
        $output_html .= '            	<option value="First_Generation"'.($generation_info=="First_Generation"?" selected":"").'>First Generation</option>';
        $output_html .= '            	<option value="Second_Generation"'.($generation_info=="Second_Generation"?" selected":"").'>Second Generation</option>';
        $output_html .= '            	<option value="Other"'.($generation_info=="Other"?" selected":"").'>Not an NSX</option>';
		$output_html .= '			</select>';
        $output_html .= '    	</div>';			

		
		//if ($row['Participant_Is_NSX']) {
			$output_html .= '		<div id="nsx_gen_response_container_driver1_nsx_year"  class="col-4 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?' active':'').'">';
			$output_html .= '			<!-- <input type="text" name="model_year_driver1" id="nsx_gen_response_driver1_nsx_year" value="" placeholder="Year" /> -->';
			$output_html .= '            <select name="model_year_driver1" id="nsx_gen_select_driver1_nsx_year" start_value="'.$h($car_information['car_year'] ?? '').'" database_key = "car_year" style="color:rgba(255, 255, 255, 0.7)";>';
			$output_html .= '            	<option value="">Year</option>';
			$output_html .= '            	<option value="1991"'.($car_information['car_year']=="1991"?" selected":"").'>1991</option>';
			$output_html .= '            	<option value="1992"'.($car_information['car_year']=="1992"?" selected":"").'>1992</option>';
			$output_html .= '            	<option value="1993"'.($car_information['car_year']=="1993"?" selected":"").'>1993</option>';
			$output_html .= '            	<option value="1994"'.($car_information['car_year']=="1994"?" selected":"").'>1994</option>';
			$output_html .= '            	<option value="1995"'.($car_information['car_year']=="1995"?" selected":"").'>1995</option>';
			$output_html .= '            	<option value="1996"'.($car_information['car_year']=="1996"?" selected":"").'>1996</option>';
			$output_html .= '            	<option value="1997"'.($car_information['car_year']=="1997"?" selected":"").'>1997</option>';
			$output_html .= '            	<option value="1998"'.($car_information['car_year']=="1998"?" selected":"").'>1998</option>';
			$output_html .= '            	<option value="1999"'.($car_information['car_year']=="1999"?" selected":"").'>1999</option>';
			$output_html .= '            	<option value="2000"'.($car_information['car_year']=="2000"?" selected":"").'>2000</option>';
			$output_html .= '            	<option value="2001"'.($car_information['car_year']=="2001"?" selected":"").'>2001</option>';
			$output_html .= '            	<option value="2002"'.($car_information['car_year']=="2002"?" selected":"").'>2002</option>';
			$output_html .= '            	<option value="2003"'.($car_information['car_year']=="2003"?" selected":"").'>2003</option>';
			$output_html .= '            	<option value="2004"'.($car_information['car_year']=="2004"?" selected":"").'>2004</option>';
			$output_html .= '            	<option value="2005"'.($car_information['car_year']=="2005"?" selected":"").'>2005</option>';
			$output_html .= '            	<option value="2017"'.($car_information['car_year']=="2017"?" selected":"").'>2017</option>';
			$output_html .= '            	<option value="2018"'.($car_information['car_year']=="2018"?" selected":"").'>2018</option>';
			$output_html .= '            	<option value="2019"'.($car_information['car_year']=="2019"?" selected":"").'>2019</option>';
			$output_html .= '            	<option value="2020"'.($car_information['car_year']=="2020"?" selected":"").'>2020</option>';
			$output_html .= '            	<option value="2021"'.($car_information['car_year']=="2021"?" selected":"").'>2021</option>';
			$output_html .= '            	<option value="2022"'.($car_information['car_year']=="2022"?" selected":"").'>2022</option>';
			$output_html .= '            	<option value="2023"'.($car_information['car_year']=="2023"?" selected":"").'>2023</option>';
            $output_html .= '            	<option value="2024"'.($car_information['car_year']=="2024"?" selected":"").'>2024</option>';
			$output_html .= '			</select>';
			$output_html .= '		</div>';
			$output_html .= '		<div id="nsx_gen_response_container_driver1_nsx_color" class="col-4 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?' active':'').'">';
			$output_html .= '			<!--<input type="text" name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" value="" placeholder="Color" /> -->';
			$output_html .= '            <select name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" start_value="'.$h($car_information['car_color'] ?? '').'" database_key = "car_color" style="color:rgba(255, 255, 255, 0.7)";>';
			$output_html .= '            	<option value="">Color</option>';
			$output_html .= '            	<option value="red"'.($car_information['car_color']=="red"?" selected":"").'>Red</option>';
			$output_html .= '            	<option value="white"'.($car_information['car_color']=="white"?" selected":"").'>White</option>';
			$output_html .= '            	<option value="black"'.($car_information['car_color']=="black"?" selected":"").'>Black</option>';
			$output_html .= '            	<option value="blue"'.($car_information['car_color']=="blue"?" selected":"").'>Blue</option>';
			$output_html .= '            	<option value="orange"'.($car_information['car_color']=="orange"?" selected":"").'>Orange</option>';
			$output_html .= '            	<option value="gray"'.($car_information['car_color']=="gray"?" selected":"").'>Gray</option>';
			$output_html .= '            	<option value="silver"'.($car_information['car_color']=="silver"?" selected":"").'>Silver</option>';
			$output_html .= '            	<option value="yellow"'.($car_information['car_color']=="yellow"?" selected":"").'>Yellow</option>';
			$output_html .= '            	<option value="green"'.($car_information['car_color']=="green"?" selected":"").'>Green</option>';
			$output_html .= '            	<option value="purple"'.($car_information['car_color']=="purple"?" selected":"").'>Purple</option>';
			$output_html .= '            	<option value="other"'.($car_information['car_color']=="other"?" selected":"").'>Other</option>';
			$output_html .= '			</select>';
			$output_html .= '			';
			$output_html .= '		</div>';
			
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_make" class="col-3 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_make_driver1" id="nsx_gen_response_driver1_other_make" value="'.$h($car_information['car_make'] ?? '').'" start_value="'.$h($car_information['car_make'] ?? '').'" database_key = "car_make" placeholder="Make" />';
			$output_html .= '		</div>';
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_model" class="col-3 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_model_driver1" id="nsx_gen_response_driver1_other_model" value="'.$h($car_information['car_model'] ?? '').'" start_value="'.$h($car_information['car_model'] ?? '').'" database_key = "car_model" placeholder="Model" />';
			$output_html .= '		</div>';
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_year" class="col-2 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_year_driver1" id="nsx_gen_response_driver1_other_year" value="'.$h($car_information['car_year'] ?? '').'" start_value="'.$h($car_information['car_year'] ?? '').'" database_key = "car_year" placeholder="Year" />';
			$output_html .= '		</div>';
		}

        $output_html .= '    </div>';
		$output_html .= '</div>		';

		$output_html .= RenderEditRegistrationType($event_id, $row);

		$other_information = json_decode((string)$row['Participant_Events_Attending'], true) ?: [];
        $output_html .= RenderParticipantQuestionsAndAnswers($event_id, $other_information);
        $output_html .= RenderRegistrantHistory($row['Participant_Reg_History'] ?? '');
		
		// $output_html .= '			    <div id="event_info_container" class="event_registration_sub_container active">';
		// $output_html .= '				    <div class="event_registration_sub_container_header">';
		// $output_html .= '						<div class="row gtr-uniform">';
        // $output_html .= '                    	    <div id="event_registration_sub_container_header_event_info" class="col-3 col-12-xsmall">';
        // $output_html .= '                    	    	Other Information';
        // $output_html .= '                    	    </div>';
		// $output_html .= '							<div class="col-9 col-12-xsmall">';
		// $output_html .= '								<P style="font-size:0.85em;">For each question below, click the box to indicate a YES answer. If you are registering a passenger, or another driver, please also check the appropriate boxes for them as well. An empty box is assumed to mean NO.</p>';
		// $output_html .= '							</div>';
		// $output_html .= '					    </div>';
		// $output_html .= '						<div class="table-wrapper">';
		// $output_html .= '							<table style="margin-top:1.5em;">';
		// $output_html .= '								<thead>';
		// $output_html .= '									<tr>';
		// $output_html .= '										<th width="60%"></th>';
		// $output_html .= '										<th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
		// $output_html .= '										<th id="column_driver2" class="table_driver" width="15%">Passenger</th>';
		// $output_html .= '									</tr>';
		// $output_html .= '								</thead>';
		// $output_html .= '								<tbody>';
		
		// $output_html .= '									<!-- NSXCA Member -->';
		// $output_html .= '									<tr>';
		// $output_html .= '										<td>Check the box if you are an active NSX Club of America member?</td>';
		// $output_html .= '										<td id="column_driver1" class="table_driver active">';
		// $output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_nsxca_member" driver="driver1" key="nsxca_member" start_value="'.$other_information['nsxca_member'].'" database_key = "nsxca_member" name="driver1_nsxca_member" '.($other_information['nsxca_member'] == 'yes'? ' checked':'').'>';
		// $output_html .= '											<label for="info_checkbox_field_driver1_nsxca_member"></label>';											
		// $output_html .= '										</td>';
		// $output_html .= '									</tr>';
		
		// $output_html .= '									<!-- Hotel Information -->';
		// $output_html .= '									<tr>';
		// $output_html .= '										<td>Check the box if you are staying at the Hilton Garden Inn for the event?</td>';
		// $output_html .= '										<td id="column_driver1" class="table_driver active">';
		// $output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_hilton" driver="driver1" key="hilton" start_value="'.$other_information['hilton'].'" database_key = "hilton" name="driver1_hilton"  '.($other_information['hilton'] == 'yes'? ' checked':'').'>';
		// $output_html .= '											<label for="info_checkbox_field_driver1_hilton"></label>';												
		// $output_html .= '										</td>';
		// $output_html .= '									</tr>';
		// $output_html .= '									<tr>';
		// $output_html .= '										<td>Check the box if you are staying at the Doubletree Hotel for the event?</td>';
		// $output_html .= '										<td id="column_driver1" class="table_driver active">';
		// $output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_hilton" driver="driver1" key="doubletree" start_value="'.$other_information['doubletree'].'" database_key = "doubletree" name="driver1_doubletree"  '.($other_information['doubletree'] == 'yes'? ' checked':'').'>';
		// $output_html .= '											<label for="info_checkbox_field_driver1_hilton"></label>';												
		// $output_html .= '										</td>';
		// $output_html .= '									</tr>';
        
        // $output_html .= '                                  <!-- Thursday Food Truck -->';
        // $output_html .= '                                  <tr>';
        // $output_html .= '                                      <td>Check the box if you would be interested in having a BBQ food truck at the hotel Thursday night?</td>';
        // $output_html .= '                                      <td id="column_driver1" class="table_driver active">';
        // $output_html .= '                                          <input type="checkbox" id="info_checkbox_field_driver1_foodtruck" driver="driver1" key="foodtruck"  start_value="'.$other_information['foodtruck'].'" database_key = "foodtruck" name="driver1_foodtruck"  '.($other_information['foodtruck'] == 'yes'? ' checked':'').'>';
        // $output_html .= '                                          <label for="info_checkbox_field_driver1_foodtruck"></label>';												
        // $output_html .= '                                      </td>';
        // $output_html .= '                                  </tr>';       

        // $output_html .= '                                  <!-- CECC Friday Tour -->';
        // $output_html .= '                                  <tr>';
        // $output_html .= '                                      <td>Check the box if you are planning to go on CECC Friday Morning Tour. </td>';
        // $output_html .= '                                      <td id="column_driver1" class="table_driver active">';
        // $output_html .= '                                          <input type="checkbox" id="info_checkbox_field_driver1_cecc_tour" driver="driver1" key="cecc_tour"   start_value="'.$other_information['cecc_tour'].'" database_key = "cecc_tour" name="driver1_cecc_tour"  '.($other_information['cecc_tour'] == 'yes'? ' checked':'').'>';
        // $output_html .= '                                          <label for="info_checkbox_field_driver1_cecc_tour"></label>';												
        // $output_html .= '                                      </td>';
        // $output_html .= '                                  </tr>';

		// $output_html .= '									<!-- Attending Events -->';
		// $output_html .= '									<tr>';
		// $output_html .= '										<td>Check the box if you are planning to join Friday\'s racing at Rush Hour Go-Karting?  (<span style="color:#06fc0c;">AMAZING NEW LOCATION</span>)</td>';
		// $output_html .= '										<td id="column_driver1" class="table_driver active">';
		// $output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_gokarting" driver="driver1" key="gokarting" start_value="'.$other_information['gokarting'].'" database_key = "gokarting" name="driver1_gokarting"  '.($other_information['gokarting'] == 'yes'? ' checked':'').'>';
		// $output_html .= '											<label for="info_checkbox_field_driver1_gokarting"></label>	';											
		// $output_html .= '										</td>';
		// $output_html .= '									</tr>';


		// $output_html .= '									<tr>';
		// $output_html .= '										<td>Check the box if you are planning to go to Ruckus Pizza on Friday Night?</td>';
		// $output_html .= '										<td id="column_driver1" class="table_driver active">';
		// $output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_ruckus_dinner" driver="driver1" key="ruckus" start_value="'.$other_information['ruckus'].'" database_key = "ruckus" name="driver1_ruckus" '.($other_information['ruckus'] == 'yes'? ' checked':'').'>';
		// $output_html .= '											<label for="info_checkbox_field_driver1_ruckus_dinner"></label>';												
		// $output_html .= '										</td>';
		// $output_html .= '									</tr>';

        // $output_html .= '                                   <!-- Triangle RAD Car Show -->';
        // $output_html .= '                                   <tr>';
        // $output_html .= '                                       <td>Check the box if you are planning to go on Saturday Morning\'s 80\'s and 90\'s RAD Car Show?</td>';
        // $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        // $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_triangle_rad" driver="driver1" key="triangle_rad" start_value="'.$other_information['triangle_rad'].'" database_key = "triangle_rad" name="driver1_triangle_rad" '.($other_information['triangle_rad'] == 'yes'? ' checked':'').'>';
        // $output_html .= '                                           <label for="info_checkbox_field_driver1_triangle_rad"></label>';												
        // $output_html .= '                                       </td>';
        // $output_html .= '                                   </tr>';       

        // $output_html .= '                                   <!-- Saturday Lunch -->';
        // $output_html .= '                                   <tr>';
        // $output_html .= '                                       <td>Check the box if you are planning to attend Saturday Lunch and Hendrick Acura Southpoint?</td>';
        // $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        // $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_sat_lunch" driver="driver1" key="sat_lunch"  start_value="'.$other_information['sat_lunch'].'" database_key = "sat_lunch" name="driver1_sat_lunch" '.($other_information['sat_lunch'] == 'yes'? ' checked':'').'>';
        // $output_html .= '                                           <label for="info_checkbox_field_driver1_sat_lunch"></label>';												
        // $output_html .= '                                       </td>';
        // $output_html .= '                                   </tr>';
        // $output_html .= '                                   <!-- Saturday Dinner -->';											
        // $output_html .= '                                   <tr>';
        // $output_html .= '                                       <td>Check the box if you are planning to attend Saturday evening at Craften Food Hall? </td>';
        // $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        // $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_sat_dinner" driver="driver1" key="sat_dinner"  start_value="'.$other_information['sat_dinner'].'" database_key = "sat_dinner" name="driver1_sat_dinner" '.($other_information['sat_dinner'] == 'yes'? ' checked':'').'>';
        // $output_html .= '                                           <label for="info_checkbox_field_driver1_sat_dinner"></label>';												
        // $output_html .= '                                       </td>';
        // $output_html .= '                                   </tr>';									
		// $output_html .= '								</tbody>';
		// $output_html .= '							</table>';
		// $output_html .= '						</div>';								
		// $output_html .= '					</div>';
		// $output_html .= '				</div>';		
		
		// $row['Participant_Name'];
	}
	
	closeDB($link);	
	
	$results = [$output_html];
	echo json_encode($results);
}

/* ************************************************************************ */
/* Funciton:    RenderEditRegistrationType                                  */
/* Description: The registrant's registration type (any of the event's      */
/*              types) and their balance, with a preview of the new balance */
/*              as the type and pre-pay options change (UpdateEditBalance-  */
/*              Preview in account.js) - type cost plus checked pre-pay     */
/*              options, as at registration. Only when the event collects   */
/*              payments.                                                   */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderEditRegistrationType($event_id, array $participant) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT EVENT_Collect_Payments FROM nsx_meetup_events WHERE EVENT_ID = ?');
    $stmt->execute([(int)$event_id]);
    if (!$stmt->fetchColumn()) {
        return '';
    }
    $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Name, REGTYPE_Cost, REGTYPE_Enabled FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ? ORDER BY REGTYPE_Name');
    $stmt->execute([(int)$event_id]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $current_type = (string)($participant['Participant_Registration_Type'] ?? '');
    $balance = (int)$participant['Participant_Balance_Due'];

    $output_html  = '<div id="edit_registration_type_container" class="event_registration_sub_container active" current_balance="'.$balance.'">';
    $output_html .= '  <div class="row gtr-25 gtr-uniform">';
    $output_html .= '    <div class="col-6 col-12-small">';
    $output_html .= '      <label for="edit_registration_type">Registration Type</label>';
    $output_html .= '      <select id="edit_registration_type">';
    $output_html .= '        <option value="" cost="">- No registration type -</option>';
    foreach ($types as $type) {
        $id = (string)(int)$type['REGTYPE_ID'];
        $output_html .= '<option value="'.$id.'" cost="'.(int)round($type['REGTYPE_Cost'] * 100).'"'.($id === $current_type ? ' selected' : '').'>'
                       .$h($type['REGTYPE_Name']).' - $'.number_format((float)$type['REGTYPE_Cost'], 2).(empty($type['REGTYPE_Enabled']) ? ' (disabled)' : '').'</option>';
    }
    $output_html .= '      </select>';
    $output_html .= '    </div>';
    $output_html .= '    <div class="col-6 col-12-small">';
    $output_html .= '      <label>Balance</label>';
    $output_html .= '      <p id="edit_balance_preview" class="edit_balance_preview">$'.number_format($balance / 100, 2).'</p>';
    $output_html .= '    </div>';
    $output_html .= '  </div>';
    $output_html .= '  <p id="edit_balance_note" class="edit_balance_note">The balance is the registration type\'s cost plus the checked pre-pay options below. '
                   .($current_type === '' ? 'This registration has no registration type, so its balance won\'t be recalculated unless you choose one.' : '')
                   .(!empty($participant['Participant_Balance_Paid']) ? ' This registrant is marked paid.' : '').'</p>';
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderParticipantQuestionsAndAnswers                       	*/
/* Description: This function renders the event's questions with the        */
/*              participants current answers. This is for editing.          */
/* Inputs:      event_id: The event in question.                            */
/*              current_selctions:  The participants current answers to the */
/*                                  events questions.                       */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderParticipantQuestionsAndAnswers ($event_id, $current_selections) {
    $output_html  = '    <div class="table-wrapper">';
    $output_html .= '        <table style="margin-top:1.5em;">';
    $output_html .= '            <thead>';
    $output_html .= '                <tr>';
    $output_html .= '                    <th width="60%"></th>';
    $output_html .= '                    <th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
    $output_html .= '                </tr>';
    $output_html .= '            </thead>';
    $output_html .= '            <tbody>';

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
			
	/************************************************/
	/* Search each registered "User" one at a time. */
	/************************************************/

	$question_query = sprintf("SELECT * FROM `nsx_meetup_event_questions` WHERE `EVENT_ID`='%s' ORDER BY `QUESTION_Order_Number`", $event_id);
	$result = $link->query($question_query);
	if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if (!$row['QUESTION_Active']) {
                continue; // as on the registration form (answers to retired questions are kept when saving)
            }
            $answer = (string)($current_selections[$row['QUESTION_Label']] ?? '');
            switch($row['QUESTION_Type']) {
                case('BOOLEAN'):
                    $is_checked = ($answer == "yes") ? " checked" : "";
                    $output_html .= '<tr>';
                    $output_html .= '    <td>'.$row['QUESTION_Instruction_Text'].'</td>';
                    $output_html .= '    <td id="column_driver1" class="table_driver active">';
                    $output_html .= '        <input type="checkbox" accounting="'.($row['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.$row['QUESTION_Amount'].'" id="info_checkbox_field_driver1_'.$row['QUESTION_Label'].'" driver="driver1" key="'.$row['QUESTION_Label'].'" start_value="'.htmlspecialchars($answer, ENT_QUOTES).'" name="driver1_'.$row['QUESTION_Label'].'" desc="'.htmlspecialchars((string)$row['QUESTION_Description'], ENT_QUOTES).'"'.$is_checked.'>';
                    $output_html .= '        <label for="info_checkbox_field_driver1_'.$row['QUESTION_Label'].'"></label>';
                    $output_html .= '    </td>';
                    $output_html .= '</tr>    ';
                    break;

                case('SELECT'):
                    $output_html .= RenderEditSelectQuestion($row, $current_selections); 
                    break;

                default:
                    break;
            }            
        }
    }
    $output_html .= '           </tbody>';
    $output_html .= '       </table>';
    $output_html .= '   </div>';    
    return ($output_html);
}

/* ************************************************************************ */
/* Funciton:    RenderEditSelectQuestion                                   	*/
/* Description: This function renders the columns and options for a SELECT  */
/*              type question.                                              */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderEditSelectQuestion($data, $current_selections) {

    $choices = json_decode((string)$data['QUESTION_Select_Choices'], true) ?: [];
    $output_html  = '<tr>';
    $output_html .= '    <td>'.$data['QUESTION_Instruction_Text'].'</td>';

    /* Driver One */
    $output_html .= '    <td id="question_driver1" class="table_driver active">';
    $output_html .= '        <select id="info_question_select_field_driver1_'.$data['QUESTION_Label'].'" accounting="'.($data['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.(float)$data['QUESTION_Amount'].'" driver="driver1" key="'.$data['QUESTION_Label'].'" name="driver1_'.$data['QUESTION_Label'].'" start_value="'.htmlspecialchars((string)($current_selections[$data['QUESTION_Label']] ?? ''), ENT_QUOTES).'" desc="'.htmlspecialchars((string)$data['QUESTION_Description'], ENT_QUOTES).'">';
    $output_html .= '            <option value="" disabled >'.htmlspecialchars((string)$data['QUESTION_Select_Default_Text'], ENT_QUOTES).'</option>';
    foreach($choices as $key => $value) {
        $current = (string)($current_selections[$data['QUESTION_Label']] ?? '');
        $selected_text = ((string)$key === $current || $value === $current) ? " selected" : "";  // stored as the key (older ones as the text)
        $output_html .= '<option value="'.htmlspecialchars((string)$key, ENT_QUOTES).'"'.$selected_text.'>'.htmlspecialchars((string)$value, ENT_QUOTES).'</option>';
    }
    $output_html .= '        </select>';
    $output_html .= '    </td>';
    $output_html .= '</tr>';

    return $output_html;
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).

?>