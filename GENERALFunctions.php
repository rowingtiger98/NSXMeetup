<?php
    require_once __DIR__ . '/databaseFunctions.php';
    require_once __DIR__ . '/SessionBootstrap.php';
    require_once __DIR__ . '/paymentMethods.php';
    require_once __DIR__ . '/eventImages.php';  // ExperienceBannerURL()

    require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
    const GENERAL_METHODS = [
        'GetSectionText'                 => 'public',
        'RenderAgendaInformation'        => 'public',
        'RenderAttendingInformation'     => 'public',
        'RenderEventStatsContent'        => 'public',
        'EnterDriverInformation'         => 'public',
        'VerifyUniqueUsername'           => 'public',
        'MailerFunction'                 => 'public',
        'SendPasswordRecoveryLink'       => 'public',
        'CreateConfirmationPage'         => 'registrant',
        'DownloadParticipantInformation' => 'event_admin',
        'CreateLanyardLabelsCSVFile'     => 'event_admin',
        'CreatePhotoNumbersCSVFile'      => 'admin',
        'RenderEditNewsContainer'        => 'admin',
        'UpdateSectionTextInDatabase'    => 'admin',
        'ShowHidePageSection'            => 'admin',
    ];
    EndpointDispatch(__FILE__, GENERAL_METHODS, ['UpdateSectionTextInDatabase', 'ShowHidePageSection']);

/* ************************************************************************ */
/* Funciton:    GetFullURL                                   				*/
/* Description: This function will return the full URL, including any URL   */
/* 				parameters for the loading page.                            */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function GetFullURL() {
    $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] 
                    === 'on' ? "https" : "http") . 
                    "://" . $_SERVER['HTTP_HOST'] . 
                    $_SERVER['REQUEST_URI'];
    return $link;
}

/* ************************************************************************ */
/* Funciton:    GetEventWorkingDirectory                       				*/
/* Description: This function will return the name of an events working     */
/* 				directory.                                                  */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function GetEventWorkingDirectory() {
    $working_directory = basename(dirname($_SERVER['PHP_SELF']));;
    return $working_directory;
}


/* ************************************************************************ */
/* Funciton:    VerifyUniqueUsername                         				*/
/* Description: This function will verify that the chosen username is 		*/
/* 				unique in the system. If it is, it will return success and  */
/* 				the new user registration process will continue. 			*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function VerifyUniqueUsername() {
	$username = trim($_POST['username']);
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username` = ?", [$username]);
	if($result->num_rows > 0) {
		print 'Fail - '.htmlspecialchars($username, ENT_QUOTES).' is already taken. Please enter a different user namee';
	} else {
		print 'PASS';
	}
}

/* ************************************************************************ */
/* Funciton:    GetEventInformationFromID                     				*/
/* Description: This function wil return the row information from the data- */
/* 				base for the event passed in.                               */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function GetEventInformationFromID($event_id) {
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = ?", [(int)$event_id]);
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
	} else {
		$row = array();
	}

    return $row;
}


/* ************************************************************************ */
/* Funciton:    GetEventInformationFromDirectoryName           				*/
/* Description: This function wil return the row information from the data- */
/* 				base for the event passed in.                               */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function GetEventInformationFromDirectoryName($basename) {
    $event_id = 0;
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `EVENT_Directory` = ?", [(string)$basename]);
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
	} else {
		$row = array();
	}

	closeDB($conn);    
    return ($row);
}



/* ************************************************************************ */
/* Funciton:    CreateCarStatisticTable                       				*/
/* Description: This function return a list of the cars by color.    		*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function CreateCarStatisticTable() {
	$results = array();	
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = "SELECT `Driver_Car_Info` from `nsx_mideast_2022` WHERE `Driver_Is_NSX`= 1 AND `Driver_Role` = 'driving'";
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            /*print gettype($row['Driver_Car_Info']);*/
            $data = json_decode($row['Driver_Car_Info'], true);
            if (array_key_exists($data['car_color'], $data)) {
                $results[$data['car_color']]++;
            } else {
                $results[$data['car_color']] = 1;
            }
            /* print_r($data); 
            print $data['car_color']; */
        }
	} 

    return $results;
}

/* ************************************************************************ */
/* Funciton:    createDateRange                           				    */
/* Description: This function creates an array of dates for the event and   */
/*              returns them as an array.                                   */
/* Returns:     $dates = array of the dates spanned by the event.           */
/* ************************************************************************ */    
function createDateRange($start, $end, $format = 'Y-m-d') {
    $start  = new DateTime($start);
    $end    = new DateTime($end);
    $invert = $start > $end;

    $dates = array();
    $dates[] = $start->format($format);
    while ($start != $end) {
        $start->modify(($invert ? '-' : '+') . '1 day');
        $dates[] = $start->format($format);
    }
    return $dates;
}

/* ************************************************************************ */
/* Funciton:    CreateEventDirectory                           				*/
/* Description: This function returns saved text for a given section.  		*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */    
function CreateEventDirectory() {
	$event_name = $_POST['event_name'];
	print 'Create '.$event_name.' Event Directory, Please';
	$event_path = sprintf('events/%s',$event_name);
	if (!mkdir($event_path, 0777, true)) 
    {
      print('Folders cannot be created recursively');
    }
    else
    {
      print('Folders created recursively');
    }
}
	
/* ************************************************************************ */
/* Funciton:    GetSectionText                                 				*/
/* Description: This function returns saved text for a given section.  		*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */  
function GetSectionText() {
	$section_name = $_POST['section_name'];
	$results = array();	
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_web_sections` WHERE `SECTION_Name` = ?", [(string)$section_name]);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
		$results[] = $row['SECTION_Hide'];
		$results[] = $row['SECTION_Content'];
	} else {
		$results[] = 1;
		$results[] = 'FAIL';
	}
	closeDB($conn);
	echo json_encode($results);		  	
}
  
/* ************************************************************************ */
/* Funciton:    RenderEditNewsContainer                                 	*/
/* Description: This function renders the page to edit or remove the news   */
/* 				section of the main page.									*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */  
 function RenderEditNewsContainer() {
	$section_name = $_POST['section_name'];
	$results = array();
	
	$output_html .= '<div id="driver_confirmation_container_driver1" class="driver_confirmation_container" driver="driver1">';	
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_web_sections` WHERE `SECTION_Name` = ?", [(string)$section_name]);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
	  
	  
	    $output_html .= '<div class="row gtr-uniform">';
	    $output_html .= '	<div class="col-8 col-12-xsmall">';
	    $output_html .= '		<textarea name="message" id="edit_news_container_textarea" strlen="'.strlen($row['SECTION_Content']).'"placeholder="Enter text for the '.$section_name.' here..." rows="6"> '.$row['SECTION_Content'].'</textarea>';
	    $output_html .= '	</div>';		
	    $output_html .= '	<div class="col-4 col-12-xsmall">';
	    $output_html .= '		<a href="#" class="button fit small disabled" id="edit_news_container_textarea_button_save" section="edit_news_container_textarea" action="save">Save Changes</a><br><br>';
	    $output_html .= '		<a href="#" class="button primary fit small" state="'.($row['SECTION_Hide']==1?"hide":"show").'" id="edit_news_container_textarea_button_hide" section="edit_news_container_textarea" action="hide">'.($row['SECTION_Hide']==1?"Show":"Hide").' News Section</a>';
	    $output_html .= '	</div>';
	    $output_html .= '</div>';
	    $output_html .= '</div>';
	
	}
	closeDB($conn);
	$results[] = $output_html;
	echo json_encode($results);	
	  
 }
 
/* ************************************************************************ */
/* Funciton:    UpdateSectionTextInDatabase                                 */
/* Description: This function will update the text for a section, in the DB.*/
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function UpdateSectionTextInDatabase() {
	$section_name = $_POST['section_name'];
	$section_text = $_POST['section_text'];
	$conn = connectDB('kirbypar_nsx_meetup');
	$overall_results = 'OK';
	if (strlen($section_text) == 0) {
	    $sql_query = "UPDATE `nsx_meetup_web_sections` SET `SECTION_Hide` = 1, `SECTION_Content` = NULL WHERE `SECTION_Name` = '".$section_name."'";
        if (!(mysqli_query($conn, $sql_query))) {
	        $overall_results = 'FAIL';
		    $results = [$overall_results];
		    echo json_encode($results);
			return;
        }		
	} else {
		$sql_query = "UPDATE `nsx_meetup_web_sections` SET `SECTION_Content` = '".$section_text."' WHERE SECTION_Name = '".$section_name."'";
        if (!(mysqli_query($conn, $sql_query))) {
	        $overall_results = 'FAIL';
        }
	}
	
	$results = array();
	closeDB($conn);
	
	$results = [$overall_results];
	echo json_encode($results);	
}
 
/* ************************************************************************ */
/* Funciton:    ShowHidePageSection                                 	    */
/* Description: This function will show or hide a section of the main page. */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function ShowHidePageSection() {
	$section_name = $_POST['section_name'];
	$state = $_POST['state'];
	$SECTION_STATE = ($state == 'hide')?1:0;

	$results = array();
	
	$conn = connectDB('kirbypar_nsx_meetup');
	
	$output_html = '';
	$overall_results = 'OK';
	$sql_query = "UPDATE `nsx_meetup_web_sections` SET `SECTION_Hide` = ".$SECTION_STATE." WHERE SECTION_Name = '".$section_name."'";
    if (!(mysqli_query($conn, $sql_query))) {
	   $overall_results = 'FAIL';        
    }
	closeDB($conn);
	
	$results = [$overall_results];
	echo json_encode($results);	
}
 
/* ************************************************************************ */
/* Funciton:    CreateConfirmationPageAttendColumnnsObject             	    */
/* Description: The generated confirmation page uses a key => value array   */
/*              to display the questions users answered during registration.*/
/*              This function uses the event's ID to dynamically build that */
/*              array and return it.                                        */
/* Returns:     Array of key => value pairs.                   				*/
/* ************************************************************************ */
function CreateConfirmationPageAttendColumnnsObject($event_id) {
    $attend_columns = Array();
    $conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_event_questions` WHERE `EVENT_ID`=%d", $event_id);    
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $attend_columns[$row['QUESTION_Label']] = $row['QUESTION_Confirmation_Text'];
        }
    }

    closeDB($conn);
    return $attend_columns;
}

/* ************************************************************************ */
/* Funciton:    RenderPaymentInformation                              	    */
/* Description: This function uses the driver and event information to      */
/*              render the payment due information should there be any.     */
/*              When driver one chose a pay-later method at checkout        */
/*              (Participant_Payment_Method: Venmo, PayPal) it shows that   */
/*              method's account and instructions. The processing fee       */
/*              applies to every method. $send_email - also email it to     */
/*              driver one.                                                 */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function RenderPaymentInformation($driver1, $driver2, $event_id, $send_email = true) {
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `Event_ID` = ?", [(int)$event_id]);
    $total_due = 0.00;
	if($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $driver1_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver1);
        $driver1_result = $conn->query($driver1_query);
        $driver1_row = $driver1_result->fetch_assoc();
        $driver2_row = null;
        if ($driver2 != '') {
            $driver2_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver2);
            $driver2_result = $conn->query($driver2_query);
            $driver2_row = $driver2_result->fetch_assoc();
        }

        // The pay-later method (Venmo, PayPal) driver one agreed to at checkout, if any
        $pay_later = (string)($driver1_row['Participant_Payment_Method'] ?? '');
        if (!isset(HANDLE_PAYMENT_METHODS[$pay_later])) {
            $pay_later = '';
        }

        /* Charges - worked out first, since the pay-later instructions show the total */
        $charges_html = '<table><tbody>';
        $driver1_total = ($driver1_row['Participant_Balance_Due'] / 100);
        $total_due =  $total_due + $driver1_total;
   
        $charges_html .= '<tr>';
        $charges_html .= '<td>'.$driver1_row['Participant_Name'].'\'s Amount owned based on selected events attending:</td>';
        $charges_html .= '<td style="text-align:right;">$'.number_format($driver1_total, 2).'</td>';
        $charges_html .= '</tr>';

        if ($driver2_row) {
            $driver2_total = ($driver2_row['Participant_Balance_Due'] / 100);
            $charges_html .= '<tr>';
            $charges_html .= '<td>'.$driver2_row['Participant_Name'].'\'s Amount owned based on selected events attending:</td>';
            $charges_html .= '<td style="text-align:right;">$'.number_format($driver2_total, 2).'</td>';
            $charges_html .= '</tr>';
            $total_due = $total_due + $driver2_total;            
        }

        // The processing fee applies to every payment method (same math as the checkout page)
        $service_fee = ProcessingFeeCents((int)round($total_due * 100), $row['EVENT_Processing_Percentage']) / 100;
        if ($service_fee > 0) {
            $charges_html .= '<tr>';
            $charges_html .= '<td>Processing Fee ('.FormatPercent($row['EVENT_Processing_Percentage']).'):</td>';
            $charges_html .= '<td style="text-align:right;">$'.number_format($service_fee, 2).'</td>';
            $charges_html .= '</tr>';
            $total_due = $total_due + $service_fee;                    
        }

        $charges_html .= '<tr>';
        $charges_html .= '<td style="color:#ffcc00;">Total Due for the event</td>';
        $charges_html .= '<td style="text-align:right;color:#ffcc00;font-weight:700;">$'.number_format($total_due, 2).'</td>';
        $charges_html .= '</tr>';
        $charges_html .= '</tbody></table>'; 

        /* Payment instructions */
        if ($pay_later !== '') {
            $output_html = '<h3>Payment Instructions</h3>';
            $output_html .= RenderPayLaterInstructions($pay_later, $row, array_filter([$driver1_row, $driver2_row]), (int)round($total_due * 100));
            $output_html .= '<h3>Charges Information</h3>';
        } elseif ($row['EVENT_PayPal_Description'] != '') {
            $output_html = '<h3>Payment Instructions</h3>';
            $output_html .= $row['EVENT_PayPal_Description'];
            $output_html .= '<br>';
            $output_html .= '<h3>Charges Information</h3>';
        } else {
            $output_html = '<h3>Charges Information</h3>';
        }
        $output_html .= $charges_html;

        if ($send_email) {
            SendRegistrationPaymentInformation($driver1_row['Participant_Email'], $output_html);
        }

    } else {
        $output_html = 'The event with event ID = '.$event_id.' is either not found or has duplicate database information';
        return $output_html;        
    }



    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderPayLaterInstructions                             	    */
/* Description: How to pay with the method chosen at checkout (Venmo,       */
/*              PayPal): the amount and account, the suggested payment note,*/
/*              the admins' instructions and an Open link - beside the QR   */
/*              code when there is one (4u image / 8u details, each 12u on  */
/*              small screens). Image and link URLs are absolute so they    */
/*              also work in the emailed copy.                              */
/* Inputs:      $method, $event - the event row, $participants - their rows,*/
/*              $cents - the total due.                                     */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderPayLaterInstructions($method, array $event, array $participants, $cents) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $info = HANDLE_PAYMENT_METHODS[$method];
    $name = PAYMENT_METHODS[$method]['name'];
    $handle = (string)$event[$info['handle_column']];
    $account = $info['handle_prefix'].$handle;

    $names = [];
    foreach ($participants as $participant) {
        if (empty($participant['Participant_Balance_Paid'])) {
            $names[] = $participant['Participant_Name'];
        }
    }
    $note = $event['EVENT_Name'].' - '.implode(', ', $names);

    $instructions = trim((string)$event[$info['description_column']]);
    if ($instructions === '') {
        $instructions = nl2br($h($info['default_description']));
    } elseif (!$info['description_html']) {
        $instructions = nl2br($h($instructions));
    }

    $details  = '<p>Send <b>$'.$h(number_format($cents / 100, 2)).'</b> to <b>'.$h($account).'</b> on '.$h($name).'.</p>';
    $details .= '<p class="checkout_handle_note_label">Payment note:</p>';
    $details .= '<p class="checkout_handle_note">'.$h($note).'</p>';
    $details .= '<div class="checkout_instructions">'.$instructions;
    $details .= '<p class="checkout_handle_link"><a href="'.$h(HandlePaymentLink($method, $handle, $cents, $note)).'" target="_blank" rel="noopener">Open '.$h($name).' to send your payment</a></p></div>';

    $qr_code = PaymentQRCodePath($event, $method);
    if ($qr_code === '') {
        return '<div class="pay_later_instructions">'.$details.'</div>';
    }
    $output_html  = '<div class="row gtr-uniform pay_later_instructions">';
    $output_html .= '  <div class="col-4 col-12-small checkout_handle_qr"><img src="'.$h(AbsoluteSiteURL($qr_code)).'" alt="'.$h($name).' QR code for '.$h($account).'" /><p>Scan with your phone\'s camera or '.$h($info['app_name']).'</p></div>';
    $output_html .= '  <div class="col-8 col-12-small">'.$details.'</div>';
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    CreateConfirmationPage                                 	    */
/* Description: This function sends the message from the web page.          */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function  CreateConfirmationPage() {
	// Only the registration page (backbutton 'no') emails the confirmation - not the admins' participant view
	echo json_encode(BuildConfirmationPage($_POST['driver1'], $_POST['driver2'], $_POST['event_id'], $_POST['collect_payment'], $_POST['backbutton'], $_POST['sold_out'], ($_POST['backbutton'] ?? '') === 'no'));
}

/* ************************************************************************ */
/* Funciton:    BuildConfirmationPage                                  	    */
/* Description: The registration (or wait list) confirmation for driver one */
/*              and driver two: payment information, then each person's     */
/*              details and attendance choices. Used by                     */
/*              CreateConfirmationPage (registration page) and by the       */
/*              checkout page after Complete Registration.                  */
/*              $send_email - a copy is emailed to driver one              */
/*              (SendRegistrationConfirmationEmail); pass false when        */
/*              showing the page again. Ends with a button back to the      */
/*              event's home page (not on the admins' view).                */
/* Returns:     [header HTML, confirmation HTML]                            */
/* ************************************************************************ */
function BuildConfirmationPage($driver1, $driver2, $event_id, $collect_payment, $backbutton, $sold_out, $send_email = true) {
	$result = array();
    $payment_html = '';
    $people = [];  // driver one and two's rows, for the emailed copy
	
    $page_header = ($sold_out == 'yes') ? '<h2>WAIT LIST CONFIRMATION</h2>' : '<h2>REGISTRATION CONFIRMATION</h2>';

    $attend_columns = CreateConfirmationPageAttendColumnnsObject($event_id);

    if ($collect_payment == 'yes') {

        if($backbutton != 'no') {
            $output_html = '<div id="back_navigation_button"></div>';
            $output_html .= '<div id="registration_payment_info_container" class="driver_confirmation_container">';
        } else {
            $output_html  = '<div id="registration_payment_info_container" class="driver_confirmation_container">';
        }

        if ($sold_out == 'yes') {
            $output_html .= '   <div class="row gtr-uniform">';
            $output_html .= '     <div class="col-12">';
            $output_html .= '      <span class="driver_name">Payment Information</span>';
            $output_html .= '     </div>';
            $output_html .= '     <div class="col-12">';
            $output_html .= '       <p>If a spot opens up in the event, the organizers will reach out to you at the email address provided to discuss payment information. At this time, there is no payment required to be on the waiting list.';
            $output_html .= '     </div>';        
            $output_html .= '   </div>';
            $output_html .= '</div>';
            $output_html .= '<div id="driver_confirmation_container_driver1" class="driver_confirmation_container" driver="driver1">';            
        } else {
            $output_html .= '   <div class="row gtr-uniform">';
            $output_html .= '     <div class="col-12">';
            $output_html .= '      <span class="driver_name">Payment Information</span>';
            $output_html .= '     </div>';
            $output_html .= '     <div class="col-12">';
            $payment_html = RenderPaymentInformation($driver1, $driver2, $event_id, false);
            $output_html .=         $payment_html;
            $output_html .= '     </div>';        
            $output_html .= '   </div>';
            $output_html .= '</div>';
            $output_html .= '<div id="driver_confirmation_container_driver1" class="driver_confirmation_container" driver="driver1">';
        }
    } else {
        if($backbutton != 'no') {
            $output_html = '<div id="back_navigation_button"></div>';
            $output_html .= '<div id="driver_confirmation_container_driver1" class="driver_confirmation_container" driver="driver1">';
        } else {
            $output_html = '<div id="driver_confirmation_container_driver1" class="driver_confirmation_container" driver="driver1">';
        }       

    }    

    $conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver1);
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $people[] = $row;
            $output_html .= '<div class="row gtr-uniform">';
            $output_html .= '  <div class="col-12">';
            $output_html .= '   <span class="driver_name">'.$row['Participant_Name'].' - '.$row['Participant_Role'].'';
            $output_html .= '  </div>';
            $output_html .= '  <div class="col-2 col-12-small"><span class="image fit"><img src="assets/images/nsx_colors/fullsize_'.$row['Participant_Car_Image'].'" alt="" /></span>';
    
            $car_info = json_decode($row['Participant_Car_Info'], true);
            if ($row['Participant_Is_NSX']) {
                $output_html .= '<p class="car_info">'.ucfirst($car_info['car_year']).' '.ucfirst($car_info['car_color']).' NSX</p>';
            } else {
                $output_html .= '<p class="car_info">'.ucfirst($car_info['car_year']).' '.ucfirst($car_info['car_make']).' '.ucfirst($car_info['car_model']).'</p>';
            }
            
            $output_html .= '  </div>';
            $output_html .= '  <div class="col-10 col-12-small">';			  
            $output_html .= '<div class="row gtr-uniform">';
            $output_html .= '  <div class="col-6 col-12-small">';
            $output_html .= '		<label for="Participant_Display_Name">Displayed Name</label>';
            $output_html .= '	    <input type="text" name="Participant_Display_Name" id="Participant_Display_Name" value="'.$row['Participant_Display_Name'].'" readonly />';
            $output_html .= '  </div>';			  
            $output_html .= '  <div class="col-6 col-12-small">';
            $output_html .= '		<label for="Participant_Address">Address</label>';
            $output_html .= '	    <input type="text" name="Participant_Address" id="Participant_Address" value="'.$row['Participant_Address'].':: '.$row['Participant_City'].' '.$row['Participant_State'].','.$row['Participant_Zip'].'" readonly />';
            $output_html .= '  </div>';
            $output_html .= '  <div class="col-6 col-12-small">';
            $output_html .= '		<label for="Participant_Email">Email</label>';
            $output_html .= '	    <input type="text" name="Participant_Email" id="Participant_Email" value="'.$row['Participant_Email'].'" readonly />';
            $output_html .= '  </div>';
            $output_html .= '  <div class="col-6 col-12-small">';
            $output_html .= '		<label for="Participant_Cell">Cell</label>';
            $output_html .= '	    <input type="text" name="Participant_Cell" id="Participant_Cell" value="'.$row['Participant_Cell'].'" readonly />';
            $output_html .= '  </div>';			  
            $output_html .= '</div>';
                    
            $output_html .= '<table style="margin-top:2.0em;"><thead><tr><th colspan="4">Attendance Selections Information</tr></th></thead>';
            $output_html .= '  <tbody>';
            $output_html .= '    <tr>';
            $attend_info = json_decode($row['Participant_Events_Attending'], true);			  
            $counter = 0;
            $numvar = 1;
            $num_choices = count($attend_info);
            foreach ($attend_info as $key => $value) {
                $output_html .= '<td>'.$attend_columns[$key].'</td>';
                $answer_class = ($value == 'yes')?'attending':'not_attending';
                $output_html .= '<td class="'.$answer_class.'">'.$value.'</td>';
                $counter++;	    		  
                if ($counter == 2 && $numvar != $num_choices) {
                $output_html .= '</tr><tr>';
                $counter = 0;
                }
                $numvar++;
            }
            
            if ($numvar % 2) {
                $output_html .= '</tr>';
            } else {
                $output_html .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
            }
            
            $output_html .= '  </tbody></table>';


            $output_html .= '  </div>';
            $output_html .= '</div>';

	
	} else {
		$output_html .= "There was not a driver associated with Driver ID = ".$driver1."";
	}
	$output_html .= '</div>';
	
	if ($driver2 != '') {
	    $output_html .= '<div id="driver_confirmation_container_driver2" class="driver_confirmation_container" driver="driver2">';	
	    //$query = sprintf("SELECT * from ``");
	    $conn = connectDB('kirbypar_nsx_meetup');
	    $query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver2);
	    $result = $conn->query($query);
	    if($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $people[] = $row;
	          $output_html .= '<div class="row gtr-uniform">';
	    	  $output_html .= '  <div class="col-12">';
	    	  $output_html .= '   <span class="driver_name">'.$row['Participant_Name'].' - '.$row['Participant_Role'].'';
	    	  $output_html .= '  </div>';
	    	  $output_html .= '  <div class="col-2 col-12-small"><span class="image fit"><img src="assets/images/nsx_colors/fullsize_'.$row['Participant_Car_Image'].'" alt="" /></span>';
	  
			  $car_info = json_decode($row['Participant_Car_Info'], true);
	    	  if ($row['Participant_Is_NSX']) {
				  if ($row['Participant_Role'] == 'driving') {
	    	        $output_html .= '<p class="car_info">'.ucfirst($car_info['car_year']).' '.ucfirst($car_info['car_color']).' NSX</p>';
				  }
	    	  } else {
	    	      $output_html .= '<p class="car_info">'.ucfirst($car_info['car_year']).' '.ucfirst($car_info['car_make']).' '.ucfirst($car_info['car_model']).'</p>';
	    	  }
			  
			  $output_html .= '  </div>';
	    	  $output_html .= '  <div class="col-10 col-12-small">';			  
			  $output_html .= '<div class="row gtr-uniform">';
    	      $output_html .= '  <div class="col-6 col-12-small">';
			  $output_html .= '		<label for="Participant_Display_Name">Displayed Name</label>';
    	      $output_html .= '	    <input type="text" name="Participant_Display_Name" id="Participant_Display_Name" value="'.$row['Participant_Display_Name'].'" readonly />';
    	      $output_html .= '  </div>';			  
    	      $output_html .= '  <div class="col-6 col-12-small">';
			  $output_html .= '		<label for="Participant_Address">Address</label>';
    	      $output_html .= '	    <input type="text" name="Participant_Address" id="Participant_Address" value="'.$row['Participant_Address'].':: '.$row['Participant_City'].' '.$row['Participant_State'].','.$row['Participant_Zip'].'" readonly />';
    	      $output_html .= '  </div>';
    	      $output_html .= '  <div class="col-6 col-12-small">';
			  $output_html .= '		<label for="Participant_Email">Email</label>';
    	      $output_html .= '	    <input type="text" name="Participant_Email" id="Participant_Email" value="'.$row['Participant_Email'].'" readonly />';
    	      $output_html .= '  </div>';
    	      $output_html .= '  <div class="col-6 col-12-small">';
			  $output_html .= '		<label for="Participant_Cell">Cell</label>';
    	      $output_html .= '	    <input type="text" name="Participant_Cell" id="Participant_Cell" value="'.$row['Participant_Cell'].'" readonly />';
    	      $output_html .= '  </div>';			  
			  $output_html .= '</div>';
			      	  
			  $output_html .= '<table  style="margin-top:2.0em;"><thead><tr><th colspan="4">Attendance Selections Information</tr></th></thead>';
			  $output_html .= '  <tbody>';
			  $output_html .= '    <tr>';
			  $attend_info = json_decode($row['Participant_Events_Attending'], true);			  
			  $counter = 0;
	    	  $numvar = 1;
	    	  $num_choices = count($attend_info);
	    	  foreach ($attend_info as $key => $value) {
	    		  $output_html .= '<td>'.$attend_columns[$key].'</td>';
				  $answer_class = ($value == 'yes')?'attending':'not_attending';
	    		  $output_html .= '<td class="'.$answer_class.'">'.$value.'</td>';
	    		  $counter++;   		  
	    		  if ($counter == 2 && $numvar != $num_choices) {
	    		    $output_html .= '</tr><tr>';
	    		    $counter = 0;
	    		  }
	    		  $numvar++;				  
	    	  }
	    	  
	    	  if ($numvar % 2) {
	    	      $output_html .= '</tr>';
	    	  } else {
				$output_html .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';
	    	  }
			  
	    	  $output_html .= '  </tbody></table>';
	    	  $output_html .= '  </div>';
	    	  $output_html .= '</div>';
	    
	    } else {
	    	$output_html .= "There was not a driver associated with Driver ID = ".$driver1."";
	    }
	    $output_html .= '</div>';			
	} 	
	
	$event = GetEventInformationFromID((int)$event_id);
	if ($backbutton == 'no' && !empty($event)) {
	    $output_html .= RenderEventHomeButton($event);
	}
	if ($send_email && !empty($event) && $people) {
	    SendRegistrationConfirmationEmail($event, $people, $attend_columns, $sold_out == 'yes', $collect_payment == 'yes' ? $payment_html : '');
	}

	return [$page_header, $output_html];
}

/* ************************************************************************ */
/* Funciton:    RenderEventHomeButton                                  	    */
/* Description: The button at the bottom of the confirmation that goes back */
/*              to the event's home page (events/<directory>/).             */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderEventHomeButton(array $event) {
    $directory = (string)$event['EVENT_Directory'];
    $link = preg_match('/^[A-Za-z0-9_-]+$/', $directory) ? 'events/'.$directory.'/' : 'index.php';
    return '<div class="confirmation_home_button"><a href="'.htmlspecialchars($link, ENT_QUOTES).'" class="button primary fit">Back to the '
          .htmlspecialchars((string)$event['EVENT_Name'], ENT_QUOTES).' Home Page</a></div>';
}

/* ************************************************************************ */
/* Funciton:    SendRegistrationConfirmationEmail                      	    */
/* Description: Emails driver one a copy of the registration (or wait list) */
/*              confirmation from no-reply@nsxmeetup.com: the payment       */
/*              information, then each person's details and attendance      */
/*              choices, laid out as plain tables for email programs.       */
/* Inputs:      $event - event row, $people - driver one (and two) rows,    */
/*              $attend_columns - question label => confirmation text,      */
/*              $wait_list, $payment_html - RenderPaymentInformation's HTML */
/*              ('' when the event doesn't collect payments).               */
/* ************************************************************************ */
function SendRegistrationConfirmationEmail(array $event, array $people, array $attend_columns, $wait_list, $payment_html) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $to = trim((string)$people[0]['Participant_Email']);
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('SendRegistrationConfirmationEmail: no valid email address for participant '.$people[0]['Participant_ID']);
        return;
    }
    $title = $event['EVENT_Name'].($wait_list ? ' Wait List Confirmation' : ' Registration Confirmation');
    $cell = 'style="padding:4px 8px;border-bottom:1px solid #ddd;"';

    $body  = '<html><body style="font-family:Arial,Helvetica,sans-serif;color:#222;max-width:700px;">';
    $body .= '<h2>'.$h($title).'</h2>';

    $pay_later = (string)($people[0]['Participant_Payment_Method'] ?? '');
    if (isset(PAYMENT_METHODS[$pay_later])) {
        $body .= '<p><b>Thank you! Your registration is saved, and you\'ve agreed to pay with '.$h(PAYMENT_METHODS[$pay_later]['name']).'. '
                .'Your registration will be complete once the event organizers receive and confirm your payment.</b></p>';
    }
    if ($wait_list) {
        $body .= '<h3>Payment Information</h3><p>If a spot opens up in the event, the organizers will reach out to you at this email address to discuss payment. '
                .'There is no payment required to be on the waiting list.</p>';
    } elseif ($payment_html !== '') {
        // The page's yellow (#ffcc00) is hard to read on an email's white background
        $body .= '<h3>Payment Information</h3>'.str_ireplace('#ffcc00', '#a07800', $payment_html);
    }

    foreach ($people as $person) {
        $car = json_decode((string)$person['Participant_Car_Info'], true) ?: [];
        if (!empty($person['Participant_Is_NSX'])) {
            $car_text = trim(($car['car_year'] ?? '').' '.ucfirst($car['car_color'] ?? '').' NSX');
        } else {
            $car_text = trim(($car['car_year'] ?? '').' '.ucfirst($car['car_make'] ?? '').' '.ucfirst($car['car_model'] ?? ''));
        }
        $body .= '<h3 style="margin-top:24px;">'.$h($person['Participant_Name']).' - '.$h($person['Participant_Role']).'</h3>';
        $body .= '<table style="border-collapse:collapse;">';
        foreach ([
            'Displayed Name' => $person['Participant_Display_Name'],
            'Car'            => $car_text,
            'Address'        => trim($person['Participant_Address'].', '.$person['Participant_City'].' '.$person['Participant_State'].' '.$person['Participant_Zip'], ', '),
            'Email'          => $person['Participant_Email'],
            'Cell'           => $person['Participant_Cell'],
        ] as $label => $value) {
            $body .= '<tr><td '.$cell.'><b>'.$h($label).'</b></td><td '.$cell.'>'.$h($value).'</td></tr>';
        }
        $body .= '</table>';

        $choices = json_decode((string)$person['Participant_Events_Attending'], true) ?: [];
        if ($choices) {
            $body .= '<h4>Attendance Selections</h4><table style="border-collapse:collapse;">';
            foreach ($choices as $key => $value) {
                // The confirmation text is admin-written HTML (e.g. a green price span) - sent as plain text
                $question = isset($attend_columns[$key]) ? html_entity_decode(strip_tags((string)$attend_columns[$key]), ENT_QUOTES, 'UTF-8') : $key;
                $question = trim(preg_replace('/\s+/u', ' ', $question));
                $body .= '<tr><td '.$cell.'>'.$h($question !== '' ? $question : $key).'</td><td '.$cell.'>'.$h($value).'</td></tr>';
            }
            $body .= '</table>';
        }
    }

    $directory = (string)$event['EVENT_Directory'];
    if (preg_match('/^[A-Za-z0-9_-]+$/', $directory)) {
        $body .= '<p style="margin-top:24px;"><a href="'.$h(AbsoluteSiteURL('events/'.$directory.'/')).'">Visit the '.$h($event['EVENT_Name']).' home page</a></p>';
    }
    $body .= '<p style="font-size:12px;color:#777;margin-top:24px;">This is an automated message from NSXMeetup.com. Please don\'t reply to this email - '
            .'contact general.information@nsxmeetup.com with any questions.</p>';
    $body .= '</body></html>';

    SendNoReplyEmail($to, $title, $body);
}



/* ************************************************************************ */
/* Funciton:    EnterDriverInformation                                 	    */
/* Description: This function sends the message from the web page.          */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function EnterDriverInformation() {
	$name = $_POST['name'];
	$email = $_POST['email'];
	$cell = $_POST['cell'];
	$street = $_POST['street'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$zip = $_POST['zip'];
	$driver_choices = $_POST['driver_choices'];
	$car_info = $_POST['car_info'];
	$car_generation = $_POST['car_generation'];
	$travel_with = $_POST['travel_with'];
	$role = $_POST['role'];
	$post_attend = $_POST['post_attend'];
    $social_media_info = $_POST['social_media_info'];
    $event_display_name = $_POST['event_display_name'];
    $shared_information_choices = $_POST['shared_information_choices'];
	$event_id = $_POST['event_id'];
    $balance = $_POST['balance'];
    $sold_out = $_POST['sold_out'];
    $regtype = RegistrationTypeForEvent($_POST['registration_type'] ?? '', $event_id);
    $registration_type = $regtype ? $regtype['id'] : null;
    if ($regtype) {
        // The registrant's total as shown on the form (driverX_cost): the registration type's fee plus the
        // pre-pay options they checked - worked out here from the database rather than taken from the browser
        $prepay = ChosenPrepayOptions(PrepayOptions(Create_SQL_PHP_DataObject(DB_NAME), $event_id), $driver_choices);
        $server_balance = $regtype['cost_cents'] + array_sum(array_column($prepay, 'cents'));
        if ((string)$server_balance !== (string)$balance) {
            error_log('EnterDriverInformation: the form sent a balance of '.$balance.' but the registration costs '.$server_balance.' (event '.$event_id.')');
        }
        $balance = $server_balance;
    }

	$results = array();
	
    /* -------------------------------------------------------------------------------------- */
    /* Handle the choice made by the driver as to what name they want to represent themselves */
    /* -------------------------------------------------------------------------------------- */
    if (($event_display_name != 'fullname') && ($event_display_name != 'email') && ($event_display_name != 'anonymous')) {
        
		if ($social_media_info[$event_display_name] != '') {
			$display_name = $social_media_info[$event_display_name];
		} else {
			$display_name = $name;
		}
    } else {
        switch($event_display_name) {
            case 'fullname':
                $display_name = $name;
                break;
            case 'email':
                $display_name = $email;
                break;
            case 'anonymous':
                $display_name = 'Anonymous';
                break;
            default:
                $display_name = $name;
                break;
        }		
    }

	/* ------------------------------------------------------ */
	/* Get the Driver's choices of shared contact information */
	/* ------------------------------------------------------ */
	$questions = array();
	foreach($social_media_info as $key => $value)  {
	 $questions[] = $key;
	}
	$total_questions = count($questions);
    $question_counter = 0;
	$social_media_choices = '{';
    foreach($social_media_info as $key => $value) {
	 $temp = '"'.$key.'":"'.$value.'"';
        if ($question_counter < $total_questions - 1) {
          $social_media_choices .= $temp . ',';
        } else {
          $social_media_choices .= $temp;
        }
        $question_counter++;
	}
	$social_media_choices .= '}';

	/* ------------------------------------------------------ */
	/* Get the Driver's choices of shared contact information */
	/* ------------------------------------------------------ */
	$questions = array();
	foreach($shared_information_choices as $key => $value)  {
	 $questions[] = $key;
	}
	$total_questions = count($questions);
    $question_counter = 0;
	$shared_info_choices = '{';
    foreach($shared_information_choices as $key => $value) {
	 $temp = '"'.$key.'":"'.$value.'"';
        if ($question_counter < $total_questions - 1) {
          $shared_info_choices .= $temp . ',';
        } else {
          $shared_info_choices .= $temp;
        }
        $question_counter++;
	}
	$shared_info_choices .= '}';

	/* ---------------------------------------------------- */
	/* Get the Driver's choices to the click-able questions */
	/* ---------------------------------------------------- */
	$questions = array();
	foreach($driver_choices as $key => $value)  {
	 $questions[] = $key;
	}
	$total_questions = count($questions);
    $question_counter = 0;
	$rider_choices = '{';
    foreach($driver_choices as $key => $value) {
	 $temp = '"'.$key.'":"'.$value.'"';
	 //$temp = "''".$key."'':''".$value."''";
        if ($question_counter < $total_questions - 1) {
          $rider_choices .= $temp . ',';
        } else {
          $rider_choices .= $temp;
        }
        $question_counter++;
	}
	$rider_choices .= '}';
	
	// ---------------------------------------------- //
	// Get the Car's information
	// ---------------------------------------------- //
	$car_details = array();
	foreach($car_info as $key => $value)  {
	 $car_details[] = $key;
	}
	$total_car_details = count($car_details);
    $counter = 0;
	$car_dict = '{';
    foreach($car_info as $key => $value) {
	 if ($key == 'car_color') {
		 $image_color = $value;
	 } 
	 if ($key == 'car_year') {
		 //$results[] = (int)$value;
		 if ((int)$value <= 2001) {
			 $car_version = 'popup';
		 } else if ((int)$value > 2001 && (int)$value < 2017) {
			 $car_version = 'nonpopup';
		 } else {
			 $car_version = 'new';
		 }
	 }
	 $temp = '"'.$key.'":"'.$value.'"';
	 //$temp = "''".$key."'':''".$value."''";
        if ($counter < $total_car_details - 1) {
          $car_dict .= $temp . ',';
        } else {
          $car_dict .= $temp;
        }
        $counter++;
	}
	$car_dict .= '}';	
	$is_nsx = ($car_generation == 'Other')?0:1;
	
	// ---------------------------------------------- //
	// Car Image 									  //
	// ---------------------------------------------- //
	if ($car_generation == 'Other') {
	    $car_image = $car_generation."_car.png";
	} else if ($role == 'riding') {
		$car_image = "passenger.png";
	} else if ($role == 'child') {
		$car_image = "passenger.png";        
	} else {
		$car_image = strtolower($car_generation)."_".$car_version."_".$image_color.".png";
	}



	// ---------------------------------------------- //
	// Confirmation 								  //
	// ---------------------------------------------- //
	$confirmation_number = rand(1000,9999);

	// ---------------------------------------------- //
	// Put On Event Waiting List            		  //
	// ---------------------------------------------- //
    $wait_listed = ($sold_out == 'yes') ? 1 : 0;

	// ---------------------------------------------- //
	// Check for and prevent duplicate entry 		  //
	// ---------------------------------------------- //
	$conn = connectDB('kirbypar_nsx_meetup');
	$safe_name = str_replace("'", "\'", $name);
	// Names are saved with ' as \' (below), so the check looks for that form
	$check_result = PreparedQuery($conn, "SELECT * from `nsx_meetup_participants` WHERE `Participant_Name` = ? AND `Participant_Email` = ? AND `Event_ID` = ?", [$safe_name, $email, (int)$event_id]);
	if($check_result->num_rows > 0) {
        $results[] =  'duplicate';
	    $results[] =  'It appears that '.htmlspecialchars($name, ENT_QUOTES).' has already registered for this event using email address '.htmlspecialchars($email, ENT_QUOTES).'. If this is not correct, please contact us at general_information@nsxmeetup.com and we will resolve the issue.';
        closeDB($conn);
	} else {

		$event_query_results = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = ?", [(int)$event_id]);
		$row = $event_query_results->fetch_assoc();
		$current_participants = $row['EVENT_Participants'];
				
		// ---------------------------------------------- //
		// Enter the New Entry into the NSX Meet Database //
		// ---------------------------------------------- //
		closeDB($conn);
		$safe_name = str_replace("'", "\'", $name);
		$safe_display_name = str_replace("'", "\'", $display_name);		
		$dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
		$insert_query = "INSERT INTO `nsx_meetup_participants` (`Event_ID`, `Participant_Name`, `Participant_Display_Name`, `Participant_Confirmation_Number`, `Participant_Address`, `Participant_City`, `Participant_State`, `Participant_Zip`, `Participant_Email`, `Participant_Cell`, `Participant_Is_NSX`, `Participant_Role`,`Participant_Events_Attending`, `Participant_Car_Info`, `Participant_Traveling_With`,`Participant_Display_Attending`, `Participant_Car_Image`, `Participant_Shared_Information`, `Participant_Social_Information`, `Participant_Balance_Due`, `Participant_Wait_Listed`, `Participant_Registration_Type`) VALUES (:event_id, :safe_name, :safe_display_name, :confirmation_number, :street, :city, :state, :zip, :email, :cell, :is_nsx, :role, :rider_choices, :car_dict, :travel_with, :post_attend, :car_image, :shared_information, :social_information, :balance, :wait_listed, :registration_type)";
		$sth = $dbh->prepare($insert_query);
        $sth->bindParam(':event_id', $event_id);
		$sth->bindParam(':safe_name', $safe_name);
		$sth->bindParam(':safe_display_name', $safe_display_name);
		$sth->bindParam(':confirmation_number', $confirmation_number);
		$sth->bindParam(':street', $street);
		$sth->bindParam(':city', $city);
		$sth->bindParam(':state', $state);
		$sth->bindParam(':zip', $zip);
		$sth->bindParam(':email', $email);
		$sth->bindParam(':cell', $cell);    
		$sth->bindParam(':is_nsx', $is_nsx);
		$sth->bindParam(':role', $role);
		$sth->bindParam(':rider_choices', $rider_choices);
		$sth->bindParam(':car_dict', $car_dict);
		$sth->bindParam(':travel_with', $travel_with);
		$sth->bindParam(':post_attend', $post_attend);
		$sth->bindParam(':car_image', $car_image);
		$sth->bindParam(':shared_information', $shared_info_choices);
		$sth->bindParam(':social_information', $social_media_choices);
        $sth->bindParam(':balance', $balance);
        $sth->bindParam(':wait_listed', $wait_listed);
        $sth->bindValue(':registration_type', $registration_type, ($registration_type === null) ? PDO::PARAM_NULL : PDO::PARAM_INT);
		try {
			$dbh->beginTransaction();
			$sth->execute();
			$results[] = 'success';
			$results[] = $dbh->lastInsertId();
			$new_participant_idnnum = $dbh->lastInsertId();
			$dbh->commit();
			RememberCheckoutParticipant($event_id, $new_participant_idnnum, ($_POST['new_registration'] ?? '') === 'yes');

			// Now that the person has been entered into system as a participant, the person needs to 
			// be linked with the event itself.
			if (($current_participants == '') || is_null($current_participants)){
				$new_participants = sprintf("%s",$new_participant_idnnum);
			} else {
				$new_participants_array = explode(",", $current_participants);
				$new_participants_array[] = $new_participant_idnnum;
				$new_participants = implode(",", $new_participants_array);
			}
			$eventdbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
			$update_query = "UPDATE `nsx_meetup_events` SET `EVENT_Participants`=:event_participants WHERE `EVENT_ID`=:id";
			$eventsth = $eventdbh->prepare($update_query);
			$eventsth->bindParam(':event_participants', $new_participants);
			$eventsth->bindParam(':id', $event_id);
			$eventdbh->beginTransaction();
			$eventsth->execute();
			$eventdbh->commit();					
			
		} catch(PDOExecption $e) {
		
			$dbh->rollback();
			$results[] = 'fail';
			$results[] = $e->getMessage();
		}        
	}
	echo json_encode($results);
 }
  
/* ************************************************************************ */
/* Funciton:    RegistrationTypeForEvent                               	    */
/* Description: Checks a posted registration type ID: it must be one of the */
/*              event's enabled registration types.                         */
/* Returns:     ['id' => REGTYPE_ID, 'cost_cents' => its cost in cents], or */
/*              null when none was chosen or it isn't valid (a registration */
/*              is never refused over it).                                  */
/* ************************************************************************ */
function RegistrationTypeForEvent($regtype_id, $event_id) {
    if (!is_string($regtype_id) || !ctype_digit($regtype_id)) {
        return null;
    }
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Cost FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ? AND REGTYPE_Enabled = 1');
    $stmt->execute([(int)$regtype_id, (int)$event_id]);
    $found = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$found) {
        error_log('EnterDriverInformation: registration type '.$regtype_id.' is not an enabled type for event '.$event_id);
        return null;
    }
    return ['id' => (int)$found['REGTYPE_ID'], 'cost_cents' => (int)round($found['REGTYPE_Cost'] * 100)];
}

/* ************************************************************************ */
/* Funciton:    RememberCheckoutParticipant                            	    */
/* Description: Keeps the registrants entered from this browser session,    */
/*              per event, so the checkout page (event_checkout.php) knows  */
/*              who to collect payment for - without trusting ids from the  */
/*              browser. $new_registration (driver one) starts a fresh list.*/
/* ************************************************************************ */
function RememberCheckoutParticipant($event_id, $participant_id, $new_registration) {
    app_session_start();
    $event_id = (int)$event_id;
    if ($new_registration || !isset($_SESSION['checkout_participants'][$event_id])) {
        $_SESSION['checkout_participants'][$event_id] = [];
    }
    $_SESSION['checkout_participants'][$event_id][] = (int)$participant_id;
}

/* ************************************************************************ */
/* Funciton:    guidv4                                              	    */
/* Description: This function generates a v4 UUID.                          */
/* Returns:     UUID                                           				*/
/* ************************************************************************ */
 function guidv4($data = null)
 {
     $data = $data ?? random_bytes(16);
     return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
 }


/* ************************************************************************ */
/* Funciton:    SendRegistrationPaymentInformation                     	    */
/* Description: This function sends a password recovery link to the user.   */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function SendRegistrationPaymentInformation($email_address, $message) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $to = $email_address;
    $subject = "Payment information from NSXMeetup.com";
    $email_message = $message;
    $headers = 'MIME-Version: 1.0' . "\r\n" .
                'From: general.information@nsxmeetup.com' . "\r\n" .
                'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                'Reply-To: general.information@nsxmeetup.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
            
    $status = mail($to, $subject, $email_message, $headers);
    if ($status) {
        $results =  "An email with this payment information was successfully send to: ".$email_address."";
    } else {

        $results =  "Could not send the message to ".$email_address.".";       
    }    
}

/* ************************************************************************ */
/* Funciton:    SendPasswordRecoveryLink                              	    */
/* Description: This function sends a password recovery link to the user.   */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function SendPasswordRecoveryLink() {
    $action = $_POST['action'];
    $email = $_POST['email'];
    
    $results = array();

	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username` = ?", [(string)$email]);
	if($result->num_rows > 0) {
        $UUID = guidv4(openssl_random_pseudo_bytes(16));
        if (!PreparedQuery($conn, "UPDATE `nsx_meetup_user_accounts` SET `NSX_Password_Reset_ID` = ?, `NSX_Password_Reset_Timer` = now() WHERE `NSX_Username` = ?", [$UUID, (string)$email])) {
            error_log('SendPasswordRecoveryLink: could not save the reset code for '.$email.': '.$conn->error);
            $results[] =  "failed";
            $results[] =  "The password reset link could not be created. Please try again.";
        } else {
            $password_reset_link = sprintf("https://www.nsxmeetup.com/account_reset.php?resetid=%s", $UUID);

            $ip = $_SERVER['REMOTE_ADDR'];
            $to = $email;
            $subject = "Password reset link from NSX Meetup";
            $email_message = sprintf("Thanks for reaching out to reset you NSX Meetup password. The link below will allow you to enter a new password. This link is only good for 1 hour after it was issued. IF you do not reset your password within that time, you will have to request another reset link.<br><br><h2>Reset Link</h2>Clink the link below to reset your password<br><br>%s", $password_reset_link);
            $headers = 'MIME-Version: 1.0' . "\r\n" .
                        'From: general.information@nsxmeetup.com' . "\r\n" .
                        'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
                        'Reply-To: general.information@nsxmeetup.com' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
                    
            $status = mail($to, $subject, $email_message, $headers);
            if ($status) {
                $results[] =  "success";
                $results[] =  "Thank you for your message with UUID = ".$UUID.". A representative will be in contact with you shortly.";
            } else {
                $results[] =  "failed";
                $results[] =  "Did not send the message to ".$email.".";       
            }
        }

    } else {
        $results[] =  "failed";
        $results[] =  "The NS Meetup Username of ".$query." does not appear to be a valid username. If you feel this is an error, please email us at <a href='mailto:general_information@nsxmeetup.com'>general_information@nsxmeetup.com</a>";        
    }


    closeDB($conn);
	echo json_encode($results);
}

/* ************************************************************************ */
/* Funciton:    MailerFunction                                       	    */
/* Description: This function sends the message from the web page.          */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
  function MailerFunction() {
	$action = $_POST['action'];
	$name = $_POST['name'];
	$email = $_POST['email'];
	$message = $_POST['message'];
    $primary_contact = $_POST['primary_contact'];
    $secondary_contact = $_POST['secondary_contact'];
	$results = array();
	
	$captcha;
	if(isset($_POST['g_recaptcha_response'])){
      $captcha=$_POST['g_recaptcha_response'];
    } else { print "NOPE";}
	
	$secretKey = RECAPTCHA_SECRET_KEY;	
	$ip = $_SERVER['REMOTE_ADDR'];
	
	$response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$captcha."&remoteip=".$ip);
    $responseKeys = json_decode($response,true);
    if(intval($responseKeys["success"]) !== 1) {
	  $results[] =  'failed';
      $results[] =  '<h3 style="color:red;">Please check the the captcha form and re-send</h3>';
    } else {	
	  switch($action) {
	  	case('general_message'):
	  	  
	  	  $ip = $_SERVER['REMOTE_ADDR'];
	  	  $to      = $primary_contact;
	  	  $subject = sprintf('Message from %s, sent from the NSX Meetup contact page.', $name);
		  $email_message = sprintf('The following message was sent via the NSX Meetup website from:<br><br> <b>Name:</b> %s <br><b>Email Address:</b> %s <br><br><b>Message:</b> %s<br><br><B>DO NOT REPLY TO THIS MESSAGE</b><br><p>To respond the to the person sending the message, simply click on their email address listed above.</p>', $name, $email, $message);
	  	  $headers = 'MIME-Version: 1.0' . "\r\n" .
	  	  			'From: general.information@nsxmeetup.com' . "\r\n" .
	  	  			'CC: '.$secondary_contact.'' . "\r\n" .
	  	  			'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
	  	  			'Reply-To: '.$primary_contact.'' . "\r\n" .
	  	  			'X-Mailer: PHP/' . phpversion();
		  		  
	  	  mail($to, $subject, $email_message, $headers);
		    $results[] =  "success";
	  	    $results[] =  "Thank you for your message. A representative will be in contact with you shortly.";
	  	break;
	  	  	
	  	default:
	  	break;
	  }
	}
	
	echo json_encode($results);
  }	
  
/* ************************************************************************ */
/* Funciton:    RenderAttendingInformation                            	    */
/* Description: This function renders the attending driver / riders page.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderAttendingInformation() {
	$event_id = $_POST['event_id'];
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = ?", [(int)$event_id]);
	$registered_participants = '';
	if($result->num_rows > 0) {
    	$row = $result->fetch_assoc();
		$registered_participants = $row['EVENT_Participants'];
	}	  

	$output_html = '<table style="margin-top: 12rem;"><thead><tr><th>Name</th><th>State</th><th style="text-align:right;">Car</th></tr></thead><tbody>';
	$parts_query = "SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN (".IntegerList($registered_participants).")";
	$parts_result = $conn->query($parts_query);
	if($parts_result->num_rows > 0) {
      while($parts_row = $parts_result->fetch_assoc()) {
		$extra_shared_info = json_decode($parts_row['Participant_Shared_Information'], true);
		$social_information = json_decode($parts_row['Participant_Social_Information'], true);	  
		$shared_html = '';
		$anything_to_share = false;
		foreach ($extra_shared_info as $key => $value) {
            if ($key == 'state' || $key == 'vehicle'){
				continue;
			} 
            if ($value == 'yes') {
				$anything_to_share = true;
				if ($social_information[$key] == '' && $key != 'email') {
					continue;
				}
				switch($key) {
				  case 'email':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span> <a href="mailto:'.$parts_row['Participant_Email'].'">'.$parts_row['Participant_Email'].'</a><br>';
					break;
				  case 'nsxprime':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
					break;					
				  case 'facebook':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span> <span style="text-decoration: underline dotted white;"><a href="'.$social_information[$key].'" target="_blank">Open in new tab</a></span><br>';
					break;
				  case 'instagram':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
					break;
				  case 'tiktok':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
					break;
				  case 'twitter':
					$shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
					break;																
				  default:
					print 'Default Case';
  
				}
			}						
		}		


		// $output_html .= $shared_html;
		if ($anything_to_share) {
			$more_info_link = sprintf('<a href="#" id="more_participant_info_link_%d" class="more_participant_info_link" participant_id="%d" action="show">show more</a>', $parts_row['Participant_ID'], $parts_row['Participant_ID']);
		} else {
			$more_info_link = '';
		}
		$output_html .= '<tr><td style="vertical-align:middle;" width="65%">'.$parts_row["Participant_Display_Name"].' '.$more_info_link.'<div id="part_extra_info_'.$parts_row['Participant_ID'].'" class="part_extra_info">'.$shared_html.'</div></td><td style="vertical-align:middle;" class="table_column">'.$parts_row["Participant_State"].'</td><td style="text-align:right;"   class="table_column"><img src="assets/images/nsx_colors/'.$parts_row['Participant_Car_Image'].'" /></td></tr>';
		// $output_html .= '<tr><td style="vertical-align:middle;">'.$parts_row["Participant_Display_Name"].'</div></td><td style="vertical-align:middle;">'.$parts_row["Participant_State"].'</td><td style="text-align:right;"><img src="assets/images/nsx_colors/'.$parts_row['Driver_Car_Image'].'" /></td></tr>';

	  }		
	}
	closeDB($conn);
   	$output_html .= '</tbody></table>';
	$results = [$output_html];
	echo json_encode($results);                  
}

/* ************************************************************************ */
/* Funciton:    RenderEventStatsContent                            	        */
/* Description: This function renders the event statistics page.            */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderEventStatsContent() {
	$event_id = $_POST['event_id'];
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = ?", [(int)$event_id]);
	$registered_participants = '';
	if($result->num_rows > 0) {
    	$row = $result->fetch_assoc();
		$registered_participants = $row['EVENT_Participants'];
	}	  

    $output_html = '<div style="display:inline;">';
	$parts_query = "SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN (".IntegerList($registered_participants).")";
	$parts_result = $conn->query($parts_query);
	if($parts_result->num_rows > 0) {
      while($parts_row = $parts_result->fetch_assoc()) {
        if ($parts_row["Participant_Role"] == 'driving' && $parts_row["Participant_Is_NSX"] == 1){
            $output_html .= '<div class="nsx_image_mini"><span class="image fit"><img src="assets/images/nsx_colors/'.$parts_row['Participant_Car_Image'].'" /></span><span class="nsx_image_mini_hovertext">'.$parts_row["Participant_Display_Name"].'</span></div>';
        }
	  }		
	}
	closeDB($conn);
	$results = [$output_html];
	echo json_encode($results);                  
}

/* ************************************************************************ */
/* Funciton:    DownloadParticipantInformation             	    			*/
/* Description: This function quickly renders the Email List (temp hack)    */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function DownloadParticipantInformation() {
    $event_id = $_POST['event_id'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="NSXMeetupPhotoNumbers.csv"');
    $headers = array('Participant Name','Display Name','Cell Number','Email Address','Street Address','City','State','Zip','Role','Car Information','Other Information');
    $data = array();

	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID`= %d", $event_id);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
    	$row = $result->fetch_assoc();
        $event_name = $row['EVENT_Name'];
		$registered_participants = $row['EVENT_Participants'];
	}	  

	$parts_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN  (%s) ORDER BY `Participant_Name`", $registered_participants);
	$parts_result = $conn->query($parts_query);
	if($parts_result->num_rows > 0) {
        $build_header = False;
        
        while ($part_row = mysqli_fetch_assoc($parts_result)) {
            // Car Information
            $db_car = json_decode($part_row['Participant_Car_Info'], True);
            if ($part_row['Participant_Role'] == 'driving') {
                if ($part_row['Participant_Is_NSX']) {
                    $car_info = $db_car['car_year'].' '.ucwords($db_car['car_color']).' NSX';
                } else {
                    $car_info = $db_car['car_year'].' '.$db_car['car_make'].' '.$db_car['car_model'];
                }
            } else {
                $car_info = 'N/A';
            }

            // Events Attending
            $events_attending = json_decode($part_row['Participant_Events_Attending'], True);
            $events_keys = implode(',', array_keys($events_attending));
            $event_values = implode(',',array_values($events_attending));
            if (!($build_header)) {
                foreach(array_keys($events_attending) as $event_key) {
                    $headers[] = $event_key;
                }
                $data[] = implode(',', $headers);
                $build_header = True;
            }

            $data[] = ucwords(strtolower($part_row['Participant_Name'])).','.$part_row['Participant_Display_Name'].','.$part_row['Participant_Cell'].','.$part_row['Participant_Email'].','.$part_row['Participant_Address'].','.$part_row['Participant_City'].','.$part_row['Participant_State'].','.$part_row['Participant_Zip'].','.$part_row['Participant_Role'].','.$car_info.','.$part_row['Participant_Traveling_With'].','.$event_values;
        }
	}	
	closeDB($conn);
    $fp = fopen('php://output', 'wb');
    foreach ( $data as $line ) {
        $val = explode(",", $line);
        fputcsv($fp, $val);
    }
    fclose($fp);	
}
	
/* ************************************************************************ */
/* Funciton:    RenderFindEditRegistration                            	    */
/* Description: This function renders the find / edit a registration panel. */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderFindEditRegistration() {
    $event_id = $_POST['event_id'];
	$output_html = '<div id="edit_find_user_registration" class="">';
	$output_html .= '	<div id="rider_search_form_container" class="rider_search_form_container" >';
	$output_html .= '       <p class="small">To search for a participant, simply start typing their name into the box below. The list will be filtered after each entered letter.</p>';
	$output_html .= '		<input type="text" id="participant_name_search" name="participant_name_search" placeholder="Enter a name to filter the list">';
	$output_html .= '	</div>';
	
	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
			
	/************************************************/
	/* Search each registered "User" one at a time. */
	/************************************************/
	$event_query = sprintf("SELECT * FROM `nsx_meetup_events` WHERE `EVENT_ID` = %d", $event_id);
	$result = $link->query($event_query);
	if ($result->num_rows > 0) {
        $row =  mysqli_fetch_assoc($result);
        $participants = $row['EVENT_Participants'];
        $user_query = sprintf("SELECT * FROM `nsx_meetup_participants` WHERE `Participant_ID` IN (%s) ORDER BY `Participant_Name`", $participants);
        $user_result = $link->query($user_query);
        $output_html .= '<div class="event_registration_sub_container active">';
        $output_html .= '<table id="participant_checkin_table" class="alt rider_checkin_table"><thead><tr><th data-sort="string">Participant Name (Click to sort)</th></tr></thead><tbody>';
        while ($part_row = mysqli_fetch_assoc($user_result)) {
            $output_html .= '<tr name="'.$part_row['Participant_Name'].'"><td id="edit_find_participant_link" class="edit_find_column" participant_id="'.$part_row['Participant_ID'].'">'.$part_row['Participant_Name'].' ('.$part_row['Participant_Role'].')</td></tr>';
        }
        $output_html .= "</tbody></table>";
	}
	$output_html .= '</div></div>';
	
	closeDB($link);	
	
	
	$output_html .= '</div>';
	$results = [$output_html];
	echo json_encode($results);
}	
  
/* ************************************************************************ */
/* Funciton:    RenderParticipantInformation                            	*/
/* Description: This function renders the find / edit participant info.		*/
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderParticipantInformation() {  
	
	$participant_id = $_POST['participant_id'];
	$output_html = '';
	
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
		
		$output_html = '<div id="driver_one_contact_container" class="event_registration_sub_container active">';
		$output_html .= '    <div id="event_registration_sub_container_header_driver1" class="event_registration_sub_container_header">';
		$output_html .= '	</div>';
        $output_html .= '    <div class="row gtr-25 gtr-uniform">';
        $output_html .= '    	<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="name" id="info_text_field_driver1_name" value="'.$row['Participant_Name'].'" start_value="'.$row['Participant_Name'].'" database_key = "Participant_Name" placeholder="Name" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="email" id="info_text_field_driver1_email" value="'.$row['Participant_Email'].'" start_value="'.$row['Participant_Email'].'" database_key = "Participant_Email" placeholder="Email Address" />';
        $output_html .= '    	</div>';
		$output_html .= '		<div class="col-4 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="cell" id="info_text_field_driver1_cell" value="'.$row['Participant_Cell'].'" start_value="'.$row['Participant_Cell'].'" database_key = "Participant_Cell" placeholder="Cell Phone Number" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-5 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="street" id="info_text_field_driver1_street" value="'.$row['Participant_Address'].'" start_value="'.$row['Participant_Address'].'" database_key = "Participant_Address" placeholder="Street Address" />';
        $output_html .= '    	</div>';
        $output_html .= '    	<div class="col-3 col-12-xsmall">';
        $output_html .= '    		<input type="text" name="city" id="info_text_field_driver1_city" value="'.$row['Participant_City'].'" start_value="'.$row['Participant_City'].'" database_key = "Participant_City" placeholder="City" />';
        $output_html .= '    	</div>';
		$output_html .= '		<div class="col-2 col-12-xsmall">';
        $output_html .= '            <select name="state" id="info_select_field_driver1_state" start_value="'.$row['Participant_State'].'" database_key = "Participant_City"style="color:rgba(255, 255, 255, 0.7)";>';
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
        $output_html .= '    		<input type="text" name="zip" id="info_text_field_driver1_zip" value="'.$row['Participant_Zip'].'" start_value="'.$row['Participant_Zip'].'" database_key = "Participant_Zip" placeholder="Zip" />';
        $output_html .= '    	</div>';
		

		if ($row['Participant_Is_NSX']) {
		    $car_information = json_decode($row['Participant_Car_Info'], true);
			$generation_info = intval($car_information['car_year']) < 2017 ? 'First_Generation' : 'Second_Generation';
		} else {
			$generation_info = 'Other';
		}

        $output_html .= '    	<div id ="info_select_field_driver1_is_nsx" start_value="'.$row['Participant_Is_NSX'].'" database_key = "Participant_Is_NSX" class="col-4 col-12-xsmall">';
        $output_html .= '    		<select id="nsx_gen_response_gen_selection_driver1" name="generation_selection_driver1" driver="driver1" start_value="'.$generation_info.'" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>';
        $output_html .= '            	<option value="First_Generation"'.($generation_info=="First_Generation"?" selected":"").'>First Generation</option>';
        $output_html .= '            	<option value="Second_Generation"'.($generation_info=="Second_Generation"?" selected":"").'>Second Generation</option>';
        $output_html .= '            	<option value="Other"'.($generation_info=="Other"?" selected":"").'>Not an NSX</option>';
		$output_html .= '			</select>';
        $output_html .= '    	</div>';			

		
		//if ($row['Participant_Is_NSX']) {
			$output_html .= '		<div id="nsx_gen_response_container_driver1_nsx_year"  class="col-4 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?' active':'').'">';
			$output_html .= '			<!-- <input type="text" name="model_year_driver1" id="nsx_gen_response_driver1_nsx_year" value="" placeholder="Year" /> -->';
			$output_html .= '            <select name="model_year_driver1" id="nsx_gen_select_driver1_nsx_year" start_value="'.$car_information['car_year'].'" database_key = "car_year" style="color:rgba(255, 255, 255, 0.7)";>';
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
			$output_html .= '            <select name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" start_value="'.$car_information['car_color'].'" database_key = "car_color" style="color:rgba(255, 255, 255, 0.7)";>';
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
			
			$car_information = json_decode($row['Participant_Car_Info'], true);
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_make" class="col-3 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_make_driver1" id="nsx_gen_response_driver1_other_make" value="'.$car_information['car_make'].'" start_value="'.$car_information['car_make'].'" database_key = "car_make" placeholder="Make" />';
			$output_html .= '		</div>';
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_model" class="col-3 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_model_driver1" id="nsx_gen_response_driver1_other_model" value="'.$car_information['car_model'].'" start_value="'.$car_information['car_model'].'" database_key = "car_model" placeholder="Model" />';
			$output_html .= '		</div>';
			$output_html .= '		<div id="nsx_gen_response_container_driver1_other_year" class="col-2 col-12-xsmall generation_choice'.($row['Participant_Is_NSX']?'':' active').'">';
			$output_html .= '			<input type="text" name="other_year_driver1" id="nsx_gen_response_driver1_other_year" value="'.$car_information['car_year'].'" start_value="'.$car_information['car_year'].'" database_key = "car_year" placeholder="Year" />';
			$output_html .= '		</div>';

        $output_html .= '    </div>';
		$output_html .= '</div>		';

		$other_information = json_decode($row['Participant_Events_Attending'], true);
		
		$output_html .= '			    <div id="event_info_container" class="event_registration_sub_container active">';
		$output_html .= '				    <div class="event_registration_sub_container_header">';
		$output_html .= '						<div class="row gtr-uniform">';
        $output_html .= '                    	    <div id="event_registration_sub_container_header_event_info" class="col-3 col-12-xsmall">';
        $output_html .= '                    	    	Other Information';
        $output_html .= '                    	    </div>';
		$output_html .= '							<div class="col-9 col-12-xsmall">';
		$output_html .= '								<P style="font-size:0.85em;">For each question below, click the box to indicate a YES answer. If you are registering a passenger, or another driver, please also check the appropriate boxes for them as well. An empty box is assumed to mean NO.</p>';
		$output_html .= '							</div>';
		$output_html .= '					    </div>';
		$output_html .= '						<div class="table-wrapper">';
		$output_html .= '							<table style="margin-top:1.5em;">';
		$output_html .= '								<thead>';
		$output_html .= '									<tr>';
		$output_html .= '										<th width="60%"></th>';
		$output_html .= '										<th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
		$output_html .= '										<th id="column_driver2" class="table_driver" width="15%">Passenger</th>';
		$output_html .= '									</tr>';
		$output_html .= '								</thead>';
		$output_html .= '								<tbody>';
		
		$output_html .= '									<!-- NSXCA Member -->';
		$output_html .= '									<tr>';
		$output_html .= '										<td>Check the box if you are an active NSX Club of America member?</td>';
		$output_html .= '										<td id="column_driver1" class="table_driver active">';
		$output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_nsxca_member" driver="driver1" key="nsxca_member" start_value="'.$other_information['nsxca_member'].'" database_key = "nsxca_member" name="driver1_nsxca_member" '.($other_information['nsxca_member'] == 'yes'? ' checked':'').'>';
		$output_html .= '											<label for="info_checkbox_field_driver1_nsxca_member"></label>';											
		$output_html .= '										</td>';
		$output_html .= '									</tr>';
		
		$output_html .= '									<!-- Hotel Information -->';
		$output_html .= '									<tr>';
		$output_html .= '										<td>Check the box if you are staying at the Hilton Garden Inn for the event?</td>';
		$output_html .= '										<td id="column_driver1" class="table_driver active">';
		$output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_hilton" driver="driver1" key="hilton" start_value="'.$other_information['hilton'].'" database_key = "hilton" name="driver1_hilton"  '.($other_information['hilton'] == 'yes'? ' checked':'').'>';
		$output_html .= '											<label for="info_checkbox_field_driver1_hilton"></label>';												
		$output_html .= '										</td>';
		$output_html .= '									</tr>';
		$output_html .= '									<tr>';
		$output_html .= '										<td>Check the box if you are staying at the Doubletree Hotel for the event?</td>';
		$output_html .= '										<td id="column_driver1" class="table_driver active">';
		$output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_hilton" driver="driver1" key="doubletree" start_value="'.$other_information['doubletree'].'" database_key = "doubletree" name="driver1_doubletree"  '.($other_information['doubletree'] == 'yes'? ' checked':'').'>';
		$output_html .= '											<label for="info_checkbox_field_driver1_hilton"></label>';												
		$output_html .= '										</td>';
		$output_html .= '									</tr>';
        
        $output_html .= '                                  <!-- Thursday Food Truck -->';
        $output_html .= '                                  <tr>';
        $output_html .= '                                      <td>Check the box if you would be interested in having a BBQ food truck at the hotel Thursday night?</td>';
        $output_html .= '                                      <td id="column_driver1" class="table_driver active">';
        $output_html .= '                                          <input type="checkbox" id="info_checkbox_field_driver1_foodtruck" driver="driver1" key="foodtruck"  start_value="'.$other_information['foodtruck'].'" database_key = "foodtruck" name="driver1_foodtruck"  '.($other_information['foodtruck'] == 'yes'? ' checked':'').'>';
        $output_html .= '                                          <label for="info_checkbox_field_driver1_foodtruck"></label>';												
        $output_html .= '                                      </td>';
        $output_html .= '                                  </tr>';       

        $output_html .= '                                  <!-- CECC Friday Tour -->';
        $output_html .= '                                  <tr>';
        $output_html .= '                                      <td>Check the box if you are planning to go on CECC Friday Morning Tour. </td>';
        $output_html .= '                                      <td id="column_driver1" class="table_driver active">';
        $output_html .= '                                          <input type="checkbox" id="info_checkbox_field_driver1_cecc_tour" driver="driver1" key="cecc_tour"   start_value="'.$other_information['cecc_tour'].'" database_key = "cecc_tour" name="driver1_cecc_tour"  '.($other_information['cecc_tour'] == 'yes'? ' checked':'').'>';
        $output_html .= '                                          <label for="info_checkbox_field_driver1_cecc_tour"></label>';												
        $output_html .= '                                      </td>';
        $output_html .= '                                  </tr>';

		$output_html .= '									<!-- Attending Events -->';
		$output_html .= '									<tr>';
		$output_html .= '										<td>Check the box if you are planning to join Friday\'s racing at Rush Hour Go-Karting?  (<span style="color:#06fc0c;">AMAZING NEW LOCATION</span>)</td>';
		$output_html .= '										<td id="column_driver1" class="table_driver active">';
		$output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_gokarting" driver="driver1" key="gokarting" start_value="'.$other_information['gokarting'].'" database_key = "gokarting" name="driver1_gokarting"  '.($other_information['gokarting'] == 'yes'? ' checked':'').'>';
		$output_html .= '											<label for="info_checkbox_field_driver1_gokarting"></label>	';											
		$output_html .= '										</td>';
		$output_html .= '									</tr>';


		$output_html .= '									<tr>';
		$output_html .= '										<td>Check the box if you are planning to go to Ruckus Pizza on Friday Night?</td>';
		$output_html .= '										<td id="column_driver1" class="table_driver active">';
		$output_html .= '											<input type="checkbox" id="info_checkbox_field_driver1_ruckus_dinner" driver="driver1" key="ruckus" start_value="'.$other_information['ruckus'].'" database_key = "ruckus" name="driver1_ruckus" '.($other_information['ruckus'] == 'yes'? ' checked':'').'>';
		$output_html .= '											<label for="info_checkbox_field_driver1_ruckus_dinner"></label>';												
		$output_html .= '										</td>';
		$output_html .= '									</tr>';

        $output_html .= '                                   <!-- Triangle RAD Car Show -->';
        $output_html .= '                                   <tr>';
        $output_html .= '                                       <td>Check the box if you are planning to go on Saturday Morning\'s 80\'s and 90\'s RAD Car Show?</td>';
        $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_triangle_rad" driver="driver1" key="triangle_rad" start_value="'.$other_information['triangle_rad'].'" database_key = "triangle_rad" name="driver1_triangle_rad" '.($other_information['triangle_rad'] == 'yes'? ' checked':'').'>';
        $output_html .= '                                           <label for="info_checkbox_field_driver1_triangle_rad"></label>';												
        $output_html .= '                                       </td>';
        $output_html .= '                                   </tr>';       

        $output_html .= '                                   <!-- Saturday Lunch -->';
        $output_html .= '                                   <tr>';
        $output_html .= '                                       <td>Check the box if you are planning to attend Saturday Lunch and Hendrick Acura Southpoint?</td>';
        $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_sat_lunch" driver="driver1" key="sat_lunch"  start_value="'.$other_information['sat_lunch'].'" database_key = "sat_lunch" name="driver1_sat_lunch" '.($other_information['sat_lunch'] == 'yes'? ' checked':'').'>';
        $output_html .= '                                           <label for="info_checkbox_field_driver1_sat_lunch"></label>';												
        $output_html .= '                                       </td>';
        $output_html .= '                                   </tr>';
        $output_html .= '                                   <!-- Saturday Dinner -->';											
        $output_html .= '                                   <tr>';
        $output_html .= '                                       <td>Check the box if you are planning to attend Saturday evening at Craften Food Hall? </td>';
        $output_html .= '                                       <td id="column_driver1" class="table_driver active">';
        $output_html .= '                                           <input type="checkbox" id="info_checkbox_field_driver1_sat_dinner" driver="driver1" key="sat_dinner"  start_value="'.$other_information['sat_dinner'].'" database_key = "sat_dinner" name="driver1_sat_dinner" '.($other_information['sat_dinner'] == 'yes'? ' checked':'').'>';
        $output_html .= '                                           <label for="info_checkbox_field_driver1_sat_dinner"></label>';												
        $output_html .= '                                       </td>';
        $output_html .= '                                   </tr>';									
		$output_html .= '								</tbody>';
		$output_html .= '							</table>';
		$output_html .= '						</div>';								
		$output_html .= '					</div>';
		$output_html .= '				</div>';		
		
		// $row['Participant_Name'];
	}
	
	closeDB($link);	
	
	$results = [$output_html];
	echo json_encode($results);
}

/*-------------------------------------------------------------*/
/*                                                             */
/*  SWAP SHOP FUNCTIONS                                        */
/*                                                             */
/*-------------------------------------------------------------*/
/* ************************************************************************ */
/* Function:    BuildSwapShopSections                                   	*/
/* Description: This function builds the HTML for the swap shop sections.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function BuildSwapShopSections() {
	$results = [];
	$forsale_html = '<table><thead><tr><th>&nbsp;</th><th>Item Description</th><th width="0.5em">Price</th></tr></thead><tbody>';
	$wanted_to_buy_html = '<table><thead><tr><th>&nbsp;</th><th>Item Description</th><th width="0.5em">Price</th></tr></thead><tbody>';
	$individ_item_sections = '';

	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_swapshop` WHERE `SWAP_Available`=1");
	$result = $conn->query($query);
	if($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        if ($row['SWAP_Approved_Item']) {
            if($row['SWAP_Action'] == 'sell') {
                if($row['SWAP_Display']) {
                    $forsale_html .= '<tr><td><a href="#" id="swapshop_more_info_button" class="button small fit" item_uuid="'.$row['SWAP_UUID'].'">More</a></td><td class="swapshop_description">'.$row['SWAP_Title'].'</td><td>$'.$row['SWAP_Price'].'.00</td></tr>';
                    $individ_item_sections .= BuildIndividualSwapShopItemContainer($row);
                }
            } else if ($row['SWAP_Action'] == 'buy') {
                if($row['SWAP_Display']) {
                    $wanted_to_buy_html .= '<tr><td><a href="#" id="swapshop_more_info_button" class="button small fit" item_uuid="'.$row['SWAP_UUID'].'">More</a></td><td class="swapshop_description">'.$row['SWAP_Title'].'</td><td>&nbsp;</td></tr>';
                    $individ_item_sections .= BuildIndividualSwapShopItemContainer($row);
                }
            } else {
                print 'This should not be possible';
            }
        }
	  }
	}
	
	closeDB($conn);

	$forsale_html .= '</tbody></table>';
	$wanted_to_buy_html .= '</tbody></table>';
	$results[] = $forsale_html;
	$results[] = $wanted_to_buy_html;
	$results[] = $individ_item_sections;
	echo json_encode($results);	

}

/* ************************************************************************ */
/* Function:    BuildIndividualSwapShopItemContainer                      	*/
/* Description: This function builds individual containers for items.       */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function BuildIndividualSwapShopItemContainer($row) {

	if($row['SWAP_Price'] != "") {
		$price = "$".$row['SWAP_Price'].".00 - ";
	} else {
		$price = "";
	}
	
	$output_html = '<div id="swap_shop_individual_item_container_'.$row['SWAP_UUID'].'" class="swap_shop_individual_item_container" uuid="'.$row['SWAP_UUID'].'" action="'.$row['SWAP_Action'].'">';
	$output_html .= '<h2>'.$price.''.$row['SWAP_Title'].'<a href="#" id="swapshop_back_to_list_button" class="button primary small" style="float:right;">Back To The List</a></h2>';
	$output_html .= '<table class="swap_shop_table"><tbody>';
	$output_html .= '<tr><td class="swap_shop_column_title" width="1.5em;">Seller</td><td class="swap_shop_column_information">'.$row['SWAP_Contact'].'</td></tr>';
	$output_html .= '<tr><td class="swap_shop_column_title" width="1.5em;">Contact</td><td class="swap_shop_column_information">'.($row['SWAP_Email'] != ""?$row['SWAP_Email']:'&nbsp;').'</td></tr>';
	$output_html .= '<tr><td class="swap_shop_column_title" width="1.5em;">Description</td><td class="swap_shop_column_information">'.($row['SWAP_Description'] != ""?$row['SWAP_Description']:'&nbsp;').'</td></tr>';
	$output_html .= '<tr><td class="swap_shop_column_title" width="1.5em;">Web Links</td><td class="swap_shop_column_information">'.($row['SWAP_Weblink'] != ""?$row['SWAP_Weblink']:'&nbsp;').'</td></tr>';
	$output_html .= '</tbody></table>';
	
	
	if ($row['SWAP_Pictures'] != "") {
		$pictures = explode(',', $row['SWAP_Pictures']);
		
		
		$output_html .= '<div class="box alt">';
		$output_html .= '	<div class="row gtr-50 gtr-uniform">';
		
		foreach ($pictures as $pic) {
			$output_html .= '<div class="col-4"><span class="image fit"><img src="assets/images/swap/'.$pic.'" alt="" /></span></div>';
		}
		
		$output_html .= '	</div>';
		$output_html .= '</div>';
		

	}
	$output_html .= '</div>';
	return $output_html;
	
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).

/*-------------------------------------------------------------*/
/*                                                             */
/* CSV File Generation Functions 							   */
/*                                                             */
/*-------------------------------------------------------------*/
function CreateLanyardLabelsCSVFile() {
	
    $event_id = $_POST['event_id'];
	/***********************/
	/* Setup DB Connection */
	/***********************/
	$conn = connectDB('kirbypar_nsx_meetup');

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample.csv"');
    $data = array('First_Name,Last_Name,City,State,Bid_Number');


	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID`= %d", $event_id);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
    	$row = $result->fetch_assoc();
        $event_name = $row['EVENT_Name'];
		$registered_participants = $row['EVENT_Participants'];
        $parts_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN  (%s) ORDER BY `Participant_Name`", $registered_participants);
        $parts_result = $conn->query($parts_query);
        if($parts_result->num_rows > 0) {        
            while ($part_row = mysqli_fetch_assoc($parts_result)) {
                $name_explode = explode(' ', $part_row['Participant_Name']);
                $data[] = ucwords(strtolower($name_explode[0])).','.ucwords(strtolower(end($name_explode))).','.trim($part_row['Participant_City']).','.$part_row['Participant_State'].','.$part_row['Participant_ID'];
            }
        }
    }
	
	closeDB($conn);
    
    $fp = fopen('php://output', 'wb');
    foreach ( $data as $line ) {
        $val = explode(",", $line);
        fputcsv($fp, $val);
    }
    fclose($fp);	

}

/*-------------------------------------------------------------*/
/*                                                             */
/* CSV File Generation Functions 							   */
/*                                                             */
/*-------------------------------------------------------------*/
function CreatePhotoNumbersCSVFile() {
	
	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="NSXMeetupPhotoNumbers.csv"');
    $data = array('PhotoNumber,First_Name,Last_Name,Email_Address');

	$user_query = "SELECT * FROM nsx_mideast_2022";
	$result = $link->query($user_query);
	if ($result->num_rows > 0) {
	  while ($row = mysqli_fetch_assoc($result)) {
		$name_explode = explode(' ', $row['Driver_Name']);
		if ($row['Driver_Role'] == 'driving') {
			$data[] = ucwords($row['Driver_Photo_Number'].','.ucwords(strtolower($name_explode[0]))).','.ucwords(strtolower($name_explode[1])).','.$row['Driver_Email'];
		}
	  }
	}
	
	closeDB($link);
    
    $fp = fopen('php://output', 'wb');
    foreach ( $data as $line ) {
        $val = explode(",", $line);
        fputcsv($fp, $val);
    }
    fclose($fp);	
	
}


function Crap() {
    $output_html = '';
    $event_html = '';

    $event_id = $_POST['event_id'];
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = %d ", $event_id);
    $results = [$query];
	// $result = $conn->query($query);
	// if($result->num_rows > 0) {
    //     $row = $result->fetch_assoc();
    //     $dates = createDateRange($row['EVENT_Start_Date'], $row['EVENT_End_Date']);
	// } else {
    //     print 'ERROR';
    // } 
	// closeDB($conn); 

	// $output_html  = '<div id="weekend_agenda_container" class="weekend_agenda_container">';
    // $output_html .= '   <div class="row gtr-uniform">';
    // $output_html .= '       <div class="col-5 col-12-small">';
	
    // foreach($dates as $key => $value) {
    //     $day_of_week = date("l", strtotime($value));
    //     $output_html .= '<span class="day_header">'.$day_of_week.'</span>';
    //     $experiences = ExtractSingleDayOfExperiences($event_id, $value);
    //     if (count($experiences) > 0) {
    //         $output_html .= '<div id="friday_agenda_container" class="daily_agenda_container active"><table><tbody>';
    //         foreach($experiences as $key => $value) {
    //             $output_html .= '<tr id="event_info_row_'.$value['EXPERIENCE_Name'].'" class="event_info_row" event_name="'.$value['EXPERIENCE_Name'].'"><td class="event_column_time">'.ltrim(date('h:i', strtotime($value['EXPERIENCE_Start_Time'])), 0).' - '.ltrim(date('h:i', strtotime($value['EXPERIENCE_Stop_Time'])), 0).'</td><td class="event_column_details">'.$value['EXPERIENCE_Title'].'</td></tr>';
    //             $event_html .= RenderEachDailyEventSection($value);
    //         }
    //         $output_html .= '</tbody></table></div>';

    //     } else {
    //         $output_html .= '';
    //     }
    // }

    // $output_html .= '       </div>';
    // $output_html .= '<div class="col-7 col-12-small">';
	// $output_html .= '<div id="individual_event_parent_container" class="individual_event_parent_container">';
	// $output_html .= '<div id="individual_event_child_container_opening" class="individual_event_child_container active">';
	// $output_html .= '<h2 style="color:#ffcc00;">Events for the 2024 Mideast NSX Meet</h2>';
	// $output_html .= '<p>Over the Meet weekend, we tried to put together several events that will give everyone something to look forward to. All events are optional of course and welcome to any one.</p> To see more information about the events, simply move you mouse over the event itself to display everything you need to know about the event. If you have any questions about one of the events, please feel free to contact us using the form below.</p>';
	// $output_html .= '</div>';	
    // $output_html .= $event_html;
	// $output_html .= '</div>';	
	// $output_html .= '		</div>';
	// $output_html .= '	</div>	';
	// $output_html .= '</div>	';

	// $results = [$output_html];
	echo json_encode($results);	
}

/* ************************************************************************ */
/* Funciton:    RenderAgendaInformation                               	    */
/* Description: This function renders the weekend agenda information page.  */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderAgendaInformation() {
	
    $event_id = $_POST['event_id'];
    // $event_id = "2";
    $event_html = '';
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = %d ", $event_id);


	$result = $conn->query($query);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dates = createDateRange($row['EVENT_Start_Date'], $row['EVENT_End_Date']);
	} else {
        print 'ERROR';
    } 
	closeDB($conn);
    
	$output_html  = '<div id="weekend_agenda_container" class="weekend_agenda_container">';
    $output_html .= '   <div class="row gtr-uniform">';
    $output_html .= '       <div class="col-5 col-12-small">';
	
    foreach($dates as $key => $value) {
        $day_of_week = date("l", strtotime($value));
        $output_html .= '<span class="day_header">'.$day_of_week.'</span>';
        $experiences = ExtractSingleDayOfExperiences($event_id, $value);
        if (count($experiences) > 0) {
            $output_html .= '<div id="friday_agenda_container" class="daily_agenda_container active"><table><tbody>';
            foreach($experiences as $key => $value) {
                $output_html .= '<tr id="event_info_row_'.$value['EXPERIENCE_Name'].'" class="event_info_row" event_name="'.$value['EXPERIENCE_Name'].'"><td class="event_column_time">'.ltrim(date('h:i', strtotime($value['EXPERIENCE_Start_Time'])), 0).' - '.ltrim(date('h:i', strtotime($value['EXPERIENCE_Stop_Time'])), 0).'</td><td class="event_column_details">'.$value['EXPERIENCE_Title'].'</td></tr>';
                $event_html .= RenderEachDailyEventSection($value);
            }
            $output_html .= '</tbody></table></div>';

        } else {
            $output_html .= '';
        }
    }

    $output_html .= '       </div>';
    $output_html .= '<div class="col-7 col-12-small">';
	$output_html .= '<div id="individual_event_parent_container" class="individual_event_parent_container">';
	$output_html .= '<div id="individual_event_child_container_opening" class="individual_event_child_container active">';
	$output_html .= '<h2 style="color:#ffcc00;">Events for the 2024 Mideast NSX Meet</h2>';
	$output_html .= '<p>Over the Meet weekend, we tried to put together several events that will give everyone something to look forward to. All events are optional of course and welcome to any one.</p> To see more information about the events, simply move you mouse over the event itself to display everything you need to know about the event. If you have any questions about one of the events, please feel free to contact us using the form below.</p>';
	$output_html .= '</div>';	
    $output_html .= $event_html;
	$output_html .= '</div>';	
	$output_html .= '		</div>';
	$output_html .= '	</div>	';
	$output_html .= '</div>	';

	$results = [$output_html];
	echo json_encode($results);	
}


/* ************************************************************************ */
/* Funciton:    ExtractSingleDayOfExperiences                         	    */
/* Description: This function returns an array of all the experiences for a */
/*              single day of an event. Ordered by EVENT_Start_Time.        */
/* Returns:     Array                                        				*/
/* ************************************************************************ */
function ExtractSingleDayOfExperiences($event_id, $experiences_date) {

    // Build the event sections 
    $experiences = array();
	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_experiences` WHERE `EVENT_ID` = ? AND `EXPERIENCE_Date` = ? AND `EXPERIENCE_Display` = 1 ORDER BY `Experience_Start_Time`", [(int)$event_id, $experiences_date]);
	if($result->num_rows > 0) {
        while($row =  $result->fetch_assoc()) {
            $experiences[] = $row;
        }
	} 
	closeDB($conn);
    return $experiences;
}
/* ************************************************************************ */
/* Funciton:    RenderEachDailyEventSection                           	    */
/* Description: This function renders a specific event's section of HTML.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderEachDailyEventSection($section) {
	$output_html = '<div id="individual_event_child_container_'.$section['EXPERIENCE_Name'].'" class="individual_event_child_container">';
	$banner_url = ExperienceBannerURL($section['EXPERIENCE_Banner_Image']);
	if ($banner_url !== '') {
		$output_html .= '<span class="image fit" style="margin-bottom:0.5em;"><img src="'.htmlspecialchars($banner_url, ENT_QUOTES).'"></span>';
	}
	$output_html .= '<h2 style="color:#ffcc00;">'.$section['EXPERIENCE_Title'].'</h2>';
	$output_html .= '<p>'.$section['EXPERIENCE_Description'].'</p>';
	$output_html .= '<table style="color:#FFFFFF;"><tbody>';
	$output_html .= '<tr><td class="daily_event_info">Event Date</td><td>'.date('m - d - Y', strtotime($section['EXPERIENCE_Date'])).'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Event Times</td><td>'.ltrim(date('h:i A', strtotime($section['EXPERIENCE_Start_Time'])), 0).' - '.ltrim(date('h:i A', strtotime($section['EXPERIENCE_Stop_Time'])), 0).'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Address</td><td>'.$section['EXPERIENCE_Location'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Web Page</td><td><a href="'.$section['EXPERIENCE_weblink'].'" target="_blank">Click Here</a></td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Phone</td><td>'.$section['EXPERIENCE_Phone'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Email</td><td>'.$section['EXPERIENCE_Email'].'</td></tr>';
	$output_html .= '</tbody></table>';
	$output_html .= '</div>';
	return $output_html;
}

/* ******************************************************************************** */
/* Funciton:    RenderLocalAttractionsSections                        	    		*/
/* Description: This function renders a provided section of the local attactions.   */
/* Returns:     HTML                                        						*/
/* ******************************************************************************** */
function RenderLocalAttractionsSections() {
	$event_id = $_POST['event_id'];
	$section_name = $_POST['section_name'];
	$results = array();
	
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_attractions` WHERE `Event_ID` = %d AND `Attraction_Category` = '%s'", $event_id, $section_name);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        while($row =  $result->fetch_assoc()) {
            $experiences[] = $row;
        }
	} 
	closeDB($conn);	
	echo json_encode($results);	
}
?>
