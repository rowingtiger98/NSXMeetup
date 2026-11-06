<?php
  require_once __DIR__ . '/databaseFunctions.php';

  if(isset($_POST['method'])) {
    $_POST['method']();
  }

/* ******************************************************************************************************** */
/* Funciton:    CheckForExistingUserAccount                           	                                    */
/* Description: This function does a quick check to see if the user already has an account that they could  */
/*              log into and save themselves some time.
/* Returns:     state                                        				                                */
/* ******************************************************************************************************** */
function CheckForExistingUserAccount() {
    $user_email = $_POST['email'];
    print 'Checking for exsiting account for '.$user_email.'';
}


/* ******************************************************************************************************** */
/* Funciton:    EnterAccountInformation                               	                                    */
/* Description: This function enters a new user into the NSX Meetup system.                                 */
/* Returns:     Nothing                                        				                                */
/* ******************************************************************************************************** */
function DebugEnterDriverInformation() {

    print ("Email Address = ".$_POST['email']);

}

/* ******************************************************************************************************** */
/* Funciton:    EnterAccountInformation                               	                                    */
/* Description: This function enters a new user into the NSX Meetup system.                                 */
/* Returns:     Nothing                                        				                                */
/* ******************************************************************************************************** */
function EnterAccountInformation() {
  $event_id = $_POST['event_id'];
  $username = $_POST['email'];
  $name = $_POST['name'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ["cost" => 11]);
  $email = $_POST['email'];
  $cell = $_POST['cell'];
  $street = $_POST['street'];
  $city = $_POST['city'];
  $state = $_POST['state'];
  $zip = $_POST['zip'];
  $car_info = $_POST['car_info'];
  $car_generation = $_POST['car_generation'];
  $social_media_info = $_POST['social_media_info'];
  $event_display_name = $_POST['event_display_name'];
  $shared_information_choices = $_POST['shared_information_choices'];

	// ---------------------------------------------- //
	// Get the Social Media Information
	// ---------------------------------------------- //
	$social_details = array();
	foreach($social_media_info as $key => $value)  {
        $social_details[] = $key;
    }
	
    $total_social_details = count($social_details);
    $counter = 0;
	$social_dict = '{';
    foreach($social_media_info as $key => $value) {
        switch ($key) {
        case 'facebook':
            $facebook = $value;
            break;
        case 'instagram':
            $instagram = $value;
            break;
        case 'tiktok':
            $tiktok = $value;
            break;
        case 'twitter':
            $twitter = $value;
            break;
        case 'nsxprime':
            $nsxprime = $value;
            break;
        default:
            break;
        }

        $temp = '"'.$key.'":"'.$value.'"';
        if ($counter < $total_social_details - 1) {
        $social_dict .= $temp . ',';
        } else {
        $social_dict .= $temp;
        }
        $counter++;
    }
    $social_dict .= '}';

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
            if ((int)$value <= 2001) {
                $car_version = 'popup';
            } else if ((int)$value > 2001 && (int)$value < 2017) {
                $car_version = 'nonpopup';
            } else {
                $car_version = 'new';
            }
        }
        $temp = '"'.$key.'":"'.$value.'"';
        if ($counter < $total_car_details - 1) {
        $car_dict .= $temp . ',';
        } else {
        $car_dict .= $temp;
        }
        $counter++;
    }
    $car_dict .= '}';

  // ---------------------------------------------- //
	// Get the Sharable Information
	// ---------------------------------------------- //
	$shared_information = array();
	foreach($shared_information_choices as $key => $value)  {
	 $shared_information[] = $key;
	}

	$total_choices = count($shared_information);
  $question_counter = 0;
	$shared_dict = '{';
    foreach($shared_information_choices as $key => $value) {
	    $temp = '"'.$key.'":"'.$value.'"';
      if ($question_counter < $total_choices - 1) {
        $shared_dict .= $temp . ',';
      } else {
        $shared_dict .= $temp;
      }
      $question_counter++;
	}
	$shared_dict .= '}';

  // --------------------------------------------------
  // Add this event to the list of attended events
  // --------------------------------------------------
  $user_events_attended = sprintf("%s",$event_id);

  // ----------------------------------------------- //
  // Enter the New User into the NSX Meetup Database //
  // ----------------------------------------------- //
  $safe_name = str_replace("'", "\'", $name);
  $safe_username = str_replace("'", "\'", $username);
	// $insert_query = sprintf("INSERT INTO `nsx_meetup_user_accounts` (`NSX_Username`,`NSX_Password`,`NSX_User_Role`,`NSX_Full_Name`,`NSX_Email_Address`,`NSX_Cell_Number`,`NSX_Address`,`NSX_City`,`NSX_State`,`NSX_Zip`,`NSX_Event_Display_Name`,`NSX_User_Facebook`,`NSX_User_Instagram`,`NSX_User_TikTok`,`NSX_User_Twitter`,`NSX_Car_Information`,`NSX_Event_Shared_Information`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",  $safe_username, $password, 'user', $safe_name, $email, $cell, $street, $city, $state, $zip, $event_display_name, $facebook, $instagram, $tiktok, $twitter, $car_dict, $shared_dict);
	// $enter = CreateNewTableRow('kirbypar_nsx_meetup', 'nsx_meetup_user_accounts', $insert_query);

  // Attempt an insertion with SQL injection protection
  $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
  $insert_query = "INSERT INTO `nsx_meetup_user_accounts` (`NSX_Username`,`NSX_Password`,`NSX_User_Role`,`NSX_Full_Name`,`NSX_Email_Address`,`NSX_Cell_Number`,`NSX_Address`,`NSX_City`,`NSX_State`,`NSX_Zip`,`NSX_Event_Display_Name`,`NSX_User_Facebook`,`NSX_User_Instagram`,`NSX_User_TikTok`,`NSX_User_Twitter`,`NSX_Car_Information`,`NSX_Event_Shared_Information`, `NSX_User_Events_Attended`) VALUES (:safe_username,:password,'user',:safe_name,:email,:cell,:street,:city,:state,:zip,:event_display_name,:facebook,:instagram,:tiktok,:twitter,:car_dict,:shared_dict, :user_events)";
  $sth = $dbh->prepare($insert_query);
  $sth->bindParam(':safe_username', $safe_username);
  $sth->bindParam(':password', $password);
  $sth->bindParam(':safe_name', $safe_name);
  $sth->bindParam(':email', $email);
  $sth->bindParam(':cell', $cell);
  $sth->bindParam(':street', $street);
  $sth->bindParam(':city', $city);
  $sth->bindParam(':state', $state);
  $sth->bindParam(':zip', $zip);
  $sth->bindParam(':event_display_name', $event_display_name);
  $sth->bindParam(':facebook', $facebook);
  $sth->bindParam(':instagram', $instagram);
  $sth->bindParam(':tiktok', $tiktok);
  $sth->bindParam(':twitter', $twitter);
  $sth->bindParam(':car_dict', $car_dict);
  $sth->bindParam(':shared_dict', $shared_dict);
  $sth->bindParam(':user_events', $user_events_attended);

  

  try {
    $dbh->beginTransaction();
    $sth->execute();
    $results[] = 'success';
    $results[] = $dbh->lastInsertId();
    $dbh->commit();
  } catch(PDOExecption $e) {

    $dbh->rollback();
    $results[] = 'fail';
    $results[] = $e->getMessage();
  }

	echo json_encode($results);

}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).

?>