<?php
if(isset($_POST['method'])) {
    $_POST['method']();
}

/* ************************************************************************ */
/* Funciton:    RenderLandingPageCurrentEvents                     	        */
/* Description: This function renders the all of the upcoming events as     */
/*              large 4u spaced display blocks.                             */ 
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderLandingPageEvents() {
    $current_datetime = date('Y-m-d');
    $pending_events = '';
    $past_events = '';

    $future_events = False;

    $output_html = '';
    
    $event_html = '';
	$conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_Display`=1 ORDER BY `EVENT_Start_Date` DESC");    
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        while($row =  $result->fetch_assoc()) {
            $end_date = date("Y-m-d", strtotime($row['EVENT_End_Date'])); // suppose $row['send_date']'s value is '2016-10-17 15:00'

            if(strtotime($current_datetime) >= strtotime($end_date)){
                $past_events .= '<div>';
                $past_events .= '<span class="image fit"><img src="assets/images/frontpage/'.$row['EVENT_Frontpage_Image'].'"></span>';
                if ($row['EVENT_Frontpage_Image'] == 'nsxmeetup_frontpage_default.png') {
                    $past_events .= '<h3>'.$row['EVENT_Name'].'</h3>';
                } else {
                    $past_events .= "<br>";
                }
                $past_events .= '<p>'.$row['EVENT_Frontpage_Description'].'</p>';
                $past_events .= '<a class="button primary fit" href="events/'. $row['EVENT_Directory'].'/">OPEN EVENT PAGE</a>';
                $past_events .= '</div>';
            }else{
                $future_events = True;
                $pending_events .= '<div>';
                $pending_events .= '<span class="image fit"><img src="assets/images/frontpage/'.$row['EVENT_Frontpage_Image'].'"></span>';
                if ($row['EVENT_Frontpage_Image'] == 'nsxmeetup_frontpage_default.png') {
                    $pending_events .= '<h3>'.$row['EVENT_Name'].'</h3>';
                }
                $pending_events .= '<p>'.$row['EVENT_Frontpage_Description'].'</p>';
                $pending_events .= '<a class="button primary fit" href="events/'. $row['EVENT_Directory'].'/">OPEN EVENT PAGE</a>';
                $pending_events .= '</div>';
            }
        }
	} else {
        print 'ERROR';
    } 
	closeDB($conn);

    /* -------------------------------------------------------------------------------------------- */
    /* Now that the individula events are classified into past and future events, determine what    */
    /* the headers for the page should be. If there are future events, then start with all the up   */
    /* coming events. If not, the  just focus on display the pervious events.                       */
    /* -------------------------------------------------------------------------------------------- */
    if ($future_events) {
        $output_html .= '<section id="pending_events" class="wrapper opener">';
        $output_html .= '<div id="pending_events_info" class="inner">';
        $output_html .= '<div style="text-align:center;"><h3>UPCOMING EVENTS</h3></div>';
        $output_html .= '    <div class="features-grid frontpage">';
        $output_html .= $pending_events;
        $output_html .= '    </div>';
        $output_html .= '</div>';
        $output_html .= '</section>';
    } 
        $output_html .= '<section id="past_events" class="wrapper opener">';
        $output_html .= '<div id="past_events_info" class="inner">';
        $output_html .= '<div style="text-align:center;"><h3>PREVIOUS EVENTS</h3></div>';
        $output_html .= '    <div class="features-grid frontpage">';
        $output_html .= $past_events;
        $output_html .= '    </div>';
        $output_html .= '</div>';
        $output_html .= '</section>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderLandingPagePastEvents                     	        */
/* Description: This function renders the all of the past events as         */
/*              large 4u spaced display blocks.                             */ 
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderLandingPagePastEvents() {
    $output_html = '';

    return $output_html;
}
?>