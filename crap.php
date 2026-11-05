<?php
require_once __DIR__ . '/databaseFunctions.php';
// DATABASE FUNCTIONS moved to databaseFunctions.php.

  function createRange($start, $end, $format = 'Y-m-d') {
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

function ExtractSingleDayOfExperiences($event_id, $experiences_date) {

    // Build the event sections 
    $experiences = array();
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_experiences` WHERE `EVENT_ID` = %d AND `EXPERIENCE_Date` = '%s' ORDER BY `Experience_Start_Time`", $event_id, $experiences_date);
	$result = $conn->query($query);
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
	$output_html .= '<span class="image fit" style="margin-bottom:0.5em;"><img src="assets/images/'.$section['EXPERIENCE_Banner_Image'].'"></span>';
	$output_html .= '<h2 style="color:#ffcc00;">'.$section['EXPERIENCE_Title'].'</h2>';
	$output_html .= '<p>'.$section['EXPERIENCE_Description'].'</p>';
	$output_html .= '<table style="color:#FFFFFF;"><tbody>';
	$output_html .= '<tr><td class="daily_event_info">Event Date</td><td>'.$section['EXPERIENCE_Date'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Event Times</td><td>'.$section['EXPERIENCE_Start_Time'].' - '.$section['EXPERIENCE_Stop_Time'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Address</td><td>'.$section['EXPERIENCE_Location'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Web Page</td><td><a href="'.$section['EXPERIENCE_weblink'].'" target="_blank">Click Here</a></td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Phone</td><td>'.$section['EXPERIENCE_Phone'].'</td></tr>';
	$output_html .= '<tr><td class="daily_event_info">Email</td><td>'.$section['EXPERIENCE_Email'].'</td></tr>';
	$output_html .= '</tbody></table>';
	$output_html .= '</div>';
	return $output_html;
}

?>


<html>
	<head><title>Make Directory Test</title></head>
	<body>
  		<?php
        $event_id = 1;
        // $event_html = '';
		// $event_query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID`='%s'", $event_id);
        // print $event_query .'<br>';
        $conn = connectDB('kirbypar_nsx_meetup');
		// $event_query_results = $conn->query($event_query);
        // if ($event_query_results->num_rows > 0) {
        //     $row = mysqli_fetch_assoc($event_query_results);
        //     print $row['EVENT_Start_Date'];
        // } else {
        //     print 'nothing';
        // }

        // $dates = createRange($row['EVENT_Start_Date'], $row['EVENT_End_Date'], 'Y-m-d');
        // print '<br>DATES</br>';
        // print_r($dates);
        // print "<br>";
        // foreach ($dates as $key => $value) {
        //     // $unixTimeStamp = strtotime('m-d-Y', $value);
        //     $dto = date('m-d-Y', strtotime($value));
        //     // print $dto;
        //     $datOfWeek = date("l", strtotime($value));
        //     // print $value->format('m-d-Y') . ' = ';
        //     print $datOfWeek . "<br>";
        //     // $new_query = sprintf("SELECT * from `nsx_meetup_experiences` WHERE `EVENT_ID` = %d AND `EXPERIENCE_Date` = '%s' ORDER BY `Experience_Start_Time`", $event_id, $value);
        //     // print $new_query;
        //     $experiences = ExtractSingleDayOfExperiences($event_id, $value);
        //     // print count($experiences);
        //     // print_r($experiences);
        //     print '<br><br>';
        //     foreach($experiences as $key => $value) {
        //         print date('h:i', strtotime($value['EXPERIENCE_Start_Time'])) .' - ' . date('h:i', strtotime($value['EXPERIENCE_Stop_Time']));
        //         print"<br>";
        //         $event_html .= RenderEachDailyEventSection($value);
        //     }



        // }
      
        // print $event_html;


            // $new_query = sprintf("SELECT * from `nsx_meetup_experiences` WHERE `EVENT_ID` = %d AND `EXPERIENCE_Date` = %s ORDER BY `EVENT_Start_Time`", $event_id, $experience_date);
            // print $new_query;
            // $result = $conn->query($new_query);
            // if($result->num_rows > 0) {
            //     $experiences = $result->fetch_assoc();
            // } else {
            //     $experiences = array();
            // } 

        
        // $username = 'johnkomar@bellsouth.net';
        // $query = sprintf('SELECT * FROM `nsx_meetup_user_accounts` WHERE NSX_Username=\'%s\'', $username);
        // print $query;



        closeDB($conn);







    //     $period = new DatePeriod(
    //         new DateTime($row['EVENT_Start_Date']),
    //         new DateInterval('P1D'),
    //         new DateTime($row['EVENT_End_Date'])
    //    );

    //    foreach($period as $key => $value) {
    //     print $value->format('m-d-Y') . '<br>';
    //    }


        // $event_display_name = 'email';
        // $name = 'Ron Hamner';
        // $email = 'rhamner@gmail.com';
        // $user_id = 3;

        // $output_html = '';
        // $conn = connectDB('kirbypar_nsx_meetup');
        // $query = sprintf("SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_User_ID` = %d AND `NSX_Email_Address` = 'ron@grabmywheel.org'", $user_id);
        // print $query;
        // $result = $conn->query($query);
        // if($result->num_rows > 0) {
        //     $row = $result->fetch_assoc();
        //     foreach($row as $key => $value) {
        //     $output_html .= $key . ' = ' . $value;
        //     }
        // }
        // print $output_html;

        // $query = sprintf("SELECT * from `nsx_meetup_user_accounts`");
        // $result = $conn->query($query);
        // if($result->num_rows > 0) {
        //     while($row = $result->fetch_assoc()) {
        //         /*print gettype($row['Driver_Car_Info']);*/
        //         foreach($row as $key => $value) {
        //             print $key;
        //         }               
        //     }
        // } 

        // closeDB($conn);





        // if (($event_display_name != 'fullname') && ($event_display_name != 'email') && ($event_display_name != 'anonymous')) {
        //   $display_name = 'Something in the social media stuff';
        // } else {
        //   switch($event_display_name) {
        //       case 'fullname':
        //           $display_name = $name;
        //           break;
        //       case 'email':
        //           $display_name = $email;
        //           break;
        //       case 'anonymous':
        //           $display_name = 'Anonymous';
        //           break;
        //       default:
        //           $display_name = $name;
        //           break;
        //   }		
        // }

        // print '<h2>'.$display_name.'</h2>';

        // $conn = connectDB('kirbypar_nsx_meetup');
        // // $output_html = '<table style="margin-top: 12rem;"><thead><tr><th>Name</th><th>State</th><th style="text-align:right;">Car</th></tr></thead><tbody>';
        // // $parts_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN  (%s)", $registered_participants);
        // $parts_query = sprintf("SELECT * from `nsx_meetup_participants`");
        // $parts_result = $conn->query($parts_query);
        // if($parts_result->num_rows > 0) {
        //   while($parts_row = $parts_result->fetch_assoc()) {
        //   print '<br>Participant ID: ' . $parts_row['Participant_ID'].'<br>';
        //   // Build the extra information to share first
        //   $extra_shared_info = json_decode($parts_row['Participant_Shared_Information'], true);	
        //   $social_information = json_decode($parts_row['Participant_Social_Information'], true);
		  
        //   // $counter = 0;
        //   // $numvar = 1;
        //   // $num_choices = count($attend_info);
        //   $shared_html = '';
        //   $anything_to_share = false;
        //   foreach ($extra_shared_info as $key => $value) {
        //     if ($key == 'state' || $key == 'vehicle'){
        //       continue;
        //     }
        //     print $key." = ".$value."<br>";
        //     if ($value == 'yes') {
        //       $anthing_to_share = true;
        //       switch($key) {
        //         case 'email':
        //           // print ' - Share Email<br>';
        //           // print 'Entered Value = '.$parts_row['Participant_Email'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span> <a href="mailto:'.$parts_row['Participant_Email'].'">'.$parts_row['Participant_Email'].'</a><br>';
        //           break;
        //         case 'nsxprime':
        //           // print ' - Share NSX Prime<br>';
        //           // print 'Entered Value = '.$social_information['nsxprime'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
        //           break;					
        //         case 'facebook':
        //           // print ' - Share Facebook<br>';
        //           // print 'Entered Value = '.$social_information['facebook'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
        //           break;
        //         case 'instagram':
        //           // print ' - Share Facebook<br>';
        //           // print 'Entered Value = '.$social_information['instagram'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
        //           break;
        //         case 'tiktok':
        //           // print ' - Share Facebook<br>';
        //           // print 'Entered Value = '.$social_information['tiktok'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
        //           break;
        //         case 'twitter':
        //           // print ' - Share Facebook<br>';
        //           // print 'Entered Value = '.$social_information['twitter'].'<br>';
        //           $shared_html .= '<span style="color:#ffcc00;">'.ucfirst($key).': </span>'.$social_information[$key].'<br>';
        //           break;																
        //         default:
        //           print 'Default Case';
        
        //         }

        //     }
            // $shared_html .= $attend_columns[$key].' = ';
            // $answer_class = ($value == 'yes')?'share':'dont share';
            // $shared_html .= $answer_class.'<br>';
            // $counter++;
            // $numvar++;
            
            // if ($counter == 2 && $numvar != $num_choices) {
            //   $output_html .= '</tr><tr>';
            //   $counter = 0;
            // }


        //   }		
      
        //   // $output_html .= '<tr><td style=\"vertical-align:middle;\">'.$parts_row["Participant_Display_Name"].'<div id="part_extra_info" class="part_extra_info">'.$shared_html.'</div></td><td style=\"vertical-align:middle;\">'.$parts_row["Participant_State"].'</td><td style=\"text-align:right;\"><img src=\"assets/images/nsx_colors/'.$parts_row['Driver_Car_Image'].'\" /></td></tr>';
        //   // }
        //   }		
        // }

        // print $shared_html;


		?>
	</body>
	
<!-- Scripts -->
	<!-- <script src="assets/js/jquery.min.js"></script>
	<script src="assets/js/browser.min.js"></script>
	<script src="assets/js/breakpoints.min.js"></script>
	<script src="assets/js/jquery.dropotron.min.js"></script>
	<script src="assets/js/jquery.scrollex.min.js"></script>
	<script src="assets/js/util.js"></script>
	<script src="assets/js/main.js"></script>
	<script src="assets/js/mailer.js"></script>
	<script src="assets/js/render.js"></script> -->
	
</html>