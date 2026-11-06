<?php
require_once __DIR__ . '/databaseFunctions.php';

function GETEventID($basename) {
    $event_id = 0;
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_Directory`=%s", $basename);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $event_id = $row['EVENT_ID'];
	}
	closeDB($conn);
    return ($event_id);
}
?>
