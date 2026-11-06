<?php
  require_once __DIR__ . '/databaseFunctions.php';
  require_once __DIR__ . '/eventAdmin.php'; // CreateDefaultRegistrationType()

  if(isset($_POST['method'])) {
    $_POST['method']();
  }


function EnterNewEventToDatabase() {

    $event_name = $_POST['event_name'];
    $event_directory =$_POST['event_directory'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $event_admins = $_POST['event_admins'];
    $sponsors = (($_POST['sponsors'])?1:0);
    $event_opening = $_POST['event_opening'];
    $results = Array();

    // ---------------------------------------------------------------------------- //
    // Get the USER NUMBER for each admin and verify that they are valid users.     //
    // ---------------------------------------------------------------------------- //
	$conn = connectDB('kirbypar_nsx_meetup');
    $event_admin_array = Array();
    $current_admin_events = Array();
    $messages = Array();

    $individual_admins = explode(',', $event_admins);
    foreach($individual_admins as &$value) {
        $check_query = sprintf("SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username`='%s'", trim($value));
        $check_result = $conn->query($check_query);
        if($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $event_admin_array[$row['NSX_User_ID']] = $row['NSX_Username'];
            $current_admin_events[$row['NSX_Username']] = $row['NSX_User_Events_Administered'];
            $messages[] = 'The user '.$value.' has been added as an adminstrator for the event.';
        } else {
            $messages[] = 'The email address '.$value.' was not found to be an active NSX Meetup registered user.';
        }
    }

    $admin_database_string = json_encode($event_admin_array);	
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $insert_query = "INSERT INTO `nsx_meetup_events` (`EVENT_Name`, `EVENT_Directory`, `EVENT_Start_Date`, `EVENT_End_Date`,`EVENT_Admins`, `EVENT_Participants`, `EVENT_Display_Sponsors`, `EVENT_Opening_Section`) VALUES (:event_name, :event_directory, :event_start_date, :event_end_date, :event_admins, '', :event_sponsors, :event_opening_section)";
    $sth = $dbh->prepare($insert_query);
    $sth->bindParam(':event_name', $event_name);
    $sth->bindParam(':event_directory', $event_directory);
    $sth->bindParam(':event_start_date', $start_date);
    $sth->bindParam(':event_end_date', $end_date);
    $sth->bindParam(':event_admins', $admin_database_string);
    $sth->bindParam(':event_sponsors', $sponsors);
    $sth->bindParam(':event_opening_section', $event_opening);

    try {
        $dbh->beginTransaction();
        $sth->execute();
        $results[] = 'success';
        $new_event_id_number = $dbh->lastInsertId();
        CreateDefaultRegistrationType($dbh, $new_event_id_number); // same transaction - both are added or neither
        $dbh->commit();				

        // ------------------------------------------------------------
        // Update User informtaion for events they are admins for 
        // ------------------------------------------------------------
        foreach($current_admin_events as $key => $value) {
            if (( $value == '') || is_null($value)){
                $new_admin_event = sprintf("%s",$new_event_id_number);
            } else {
                $new_admin_array = explode(",", $value);
                $new_admin_array[] = $new_event_id_number;
                $new_admin_event = implode(",", $new_admin_array);
            }        
    
            $eventdbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
            $update_query = "UPDATE `nsx_meetup_user_accounts` SET `NSX_User_Events_Administered`=:events_administered WHERE `NSX_Username`=:username";
            $eventsth = $eventdbh->prepare($update_query);
            $eventsth->bindParam(':events_administered', $new_admin_event);
            $eventsth->bindParam(':username', $key);
            $eventdbh->beginTransaction();
            $eventsth->execute();
            $eventdbh->commit();

            $results[] = $event_directory;
            $results[] = json_encode($messages);

        }


    } catch(PDOException $e) {
    
        $dbh->rollback();
        $results[] = 'fail';
        $results[] = $e->getMessage();
        $results[] = print_r($messages);
    }   

    echo json_encode($results);    
}

function CreateNewDirectoryPath() {
    $new_path = $_POST['new_path'];
    $current_directory = getcwd();
    $results = Array();
    $results[] = 'pass';
    $results[] = 'Current Directory = '.$current_directory;
    $directory_path = sprintf('%s/events/%s', $current_directory, $new_path);
    $directory_path2 = sprintf('./events/%s', $new_path);
    if (!is_dir($directory_path)) {
        mkdir($directory_path, 0755, true);
        $results[] = 'HERE 1';
    } 
    else if (!is_dir($directory_path2)){
        $results[] = 'HERE 2';
    }
    else {
        $results[] = "HERE 3";
    }

    echo json_encode($results);
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).



?>