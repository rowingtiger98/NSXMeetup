<?php
  require_once __DIR__ . '/databaseFunctions.php';

  if(isset($_POST['method'])) {
    $_POST['method']();
  }

/* ************************************************************************ */
/* Funciton:    ValidateResetCodeTimeframe                         		    */
/* Description: This function returns the rendered User Account infomration.*/
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderEventReportMainBody() {
    $event_id = $_POST['event_id'];
    $results = array();

    $conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID`= %d", $event_id);
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $event_name = $row['EVENT_Name'];
        $collect_payments = ($row['EVENT_Collect_Payments'])?'yes':'no';
    } else {
        $event_name = 'Unknown Event Name';
        $collect_payments = 'no';
    }
    closeDB($conn);

    $output_html =  '<div id="main" class="wrapper" event_id="'.$event_id.'">';
    $output_html .= '<div class="inner">';

    if ($event_name != 'Unknown Event Name') {
        $output_html .= '<h1 class="major">'.$event_name.' Registration Breakdown</h1>';
        $output_html .= '<div id="back_navigation_button"></div>';
        $output_html .= RenderDemographicData($row);
        $output_html .= RenderDownloadCSVData($row['EVENT_ID'], $row['EVENT_Name']);
        $output_html .= RenderAttendanceData($row);

    } else {
        $output_html .= '<div id="back_navigation_button"></div>';
        $output_html .= '<h3>ERROR</h3>Event ID ('.$event_id.') is an unknown event ID.';
    }

    $output_html .= '</div>';
    $output_html .= '</div>';

	$results[] = $output_html;
	echo json_encode($results);
}

/* ************************************************************************ */
/* Funciton:    RenderDemographicData                            		    */
/* Description: This function returns the renders the demographic breakdown.*/
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderDemographicData($row) {
    $output_html = '';
    $drivers = [];
    $passengers = [];
    $states_represented = [];

    /* Number of Participants */
    $participants = explode(",", $row['EVENT_Participants']);
    $number_of_participants = count($participants);

    $output_html .= '<div id="event_questions_breakdown_container" class="event_breakdown_default_container">';
    $output_html .= '<h3>Event Details Breakdown</h3>';
    $output_html .= '<p>This section covers the basic event information breakdown.</p>';
    $output_html .= '<strong>Number of Participants</strong> = '.$number_of_participants;
    $output_html .= '</div>';
    return $output_html;

}

/* ************************************************************************ */
/* Funciton:    RenderDemographicData                            		    */
/* Description: This function returns the renders the demographic breakdown.*/
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderDownloadCSVData($event_id, $event_name) {
    $output_html = '';
    $output_html .= '<div id="event_csv_download_container" class="event_breakdown_default_container">';
    $output_html .= '   <div class="row gtr0">';
    $output_html .= '       <div class="col-9 col-12-small">';
    $output_html .= '           <h3>Download Registration CSV File</h3>';
    $output_html .= '       </div>';
    $output_html .= '       <div class="col-3 col-12-small">';
    $output_html .= '           <a href="#" id="download_participants_csv" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Download CSV</a>';
    $output_html .= '       </div>';    
    $output_html .= '   </div>';
    $output_html .= '</div>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderAttendanceData                             		    */
/* Description: This function returns the render the attendance breakdown.  */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderAttendanceData($row) {

    /* Need to look at each of the registrations for this event to get the  */
    /* individual registrant's information regarding the their choices and  */
    /* data.                                                                */

    $question_answers_array = [];

    $output_html = '';
    $max_number_of_columns = 0;
    $conn = connectDB('kirbypar_nsx_meetup');
	$question_query = sprintf("SELECT * from `nsx_meetup_event_questions` WHERE `EVENT_ID` = %d ORDER BY `QUESTION_Order_Number`", $row['EVENT_ID']);
	$question_results = $conn->query($question_query);
    while($question_row = $question_results->fetch_assoc()) {
        switch($question_row['QUESTION_Type']) {
            case('BOOLEAN'):
                $question_answers_array[$question_row['QUESTION_Label']] = array(
                    "yes" => array(),
                    "no" => array()
                );
                if ($max_number_of_columns < 2)  {
                    $max_number_of_columns = 2;
                } 
                break;

            case('SELECT'):
                $question_answers_array[$question_row['QUESTION_Label']] = array();
                $choices = json_decode($question_row['QUESTION_Select_Choices'], true);
                $value_counter = 0;
                foreach($choices as $key => $value) {
                    $question_answers_array[$question_row['QUESTION_Label']][$key] = array();
                    $value_counter++;
                }                
                if ($max_number_of_columns < $value_counter)  {
                    $max_number_of_columns = $value_counter;
                } 
                break;

            default:
                break;
        }
    }


    $test = explode(",", $row['EVENT_Participants']);
    $test_again = implode("','", $test);
    $participants_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN ('%s')",$test_again);
    $participant_results = $conn->query($participants_query);

    while($participant_row = $participant_results->fetch_assoc()) {
        $attendance = json_decode($participant_row['Participant_Events_Attending'], true);
        foreach($attendance as $key => $value) {
            if(array_key_exists($key, $question_answers_array)) {
                if(array_key_exists($value, $question_answers_array[$key])) {
                    array_push($question_answers_array[$key][$value], $participant_row['Participant_Name']);
                } else {
                    $output_html .= $key ."=>".$value." (DNE) = ".$value."<br>";
                }
            } else {
                $output_html .= $key ." (DNE) = ".$value."<br>";
            }
        }          
    }

    // $output_html .= 'Max Columns = '.$max_number_of_columns.'<br>';
    $output_html .= '<div id="event_questions_breakdown_container" class="event_breakdown_default_container">';
    $output_html .= '<h3>Event Registration Questions Breakdown</h3>';
    $output_html .= '<p>This section breaks down the count that each answer of your custom questions received. By clicking the Show / Hide Names \
    button, you can see or hide the names of each person that gave the respective answers.</p>';
    $output_html .= '<table>';
    $output_html .= '<thead><tr><th></th><th>Label</th><th>Question</th><th colspan="'.$max_number_of_columns.'">Answers</th></tr></thead>';
    $output_html .= '<tbody>';
    $question_results = $conn->query($question_query);
    while($question_row = $question_results->fetch_assoc()) {
        $output_html .= '<tr>';
        $output_html .= '<td width="10%"><a href="#" id="show_participants_list_button_'.$question_row['QUESTION_Label'].'" class="button primary small" action="show" label="'.$question_row['QUESTION_Label'].'">Show Names</a>';
        $output_html .= '<td width="10%">'.$question_row['QUESTION_Label'].'</td><td  width="30%">'.$question_row['QUESTION_Confirmation_Text'].'</td>';
        $column_counter = 0;
        foreach($question_answers_array[$question_row['QUESTION_Label']] as $key => $value) {
            $output_html .= '<td>';
            $output_html .= $key.' = '.count($value);
            $output_html .= '<div id="participant_names_list_'.$question_row['QUESTION_Label'].'" class="included_participants_list">';
            foreach($value as $name) {
                $output_html .= $name .'<br>';
            }
            $output_html .= '</div>';
            $output_html .= '</td>';
            $column_counter++;
        }
        while ($column_counter < $max_number_of_columns) {
            $output_html .= '<td>&nbsp;</td>';
            $column_counter++;
        }
        $output_html .= '</tr>';    
    }

    $output_html .= '</tbody></table>';
    $output_html .= '</div>';
    return $output_html;
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).
?>