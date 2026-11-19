<?php
require_once __DIR__ . '/databaseFunctions.php';  // PreparedQuery()
require_once __DIR__ . '/eventImages.php';  // ExperienceBannerURL()

/* ************************************************************************ */
/* Funciton:    NEWRenderAgendaInformation                               	*/
/* Description: This function renders the weekend agenda information page.  */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function NEWRenderAgendaInformation($event_data) {
	
    $event_id = $event_data['EVENT_ID'];
    $event_html = '';
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = %d ", $event_id);


	$result = $conn->query($query);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $dates = NEWcreateDateRange($row['EVENT_Start_Date'], $row['EVENT_End_Date']);
	} else {
        print 'ERROR';
    } 
	closeDB($conn);
    $output_html  = '<section id="agenda" class="opener">';
    $output_html .= '<div id="agenda_info" class="inner">';    
	$output_html .= '<div id="weekend_agenda_container" class="weekend_agenda_container">';
    $output_html .= '   <div class="row gtr-uniform">';
    $output_html .= '       <div class="col-5 col-12-small">';
	
    foreach($dates as $key => $value) {
        $day_of_week = date("l", strtotime($value));
        $output_html .= '<span class="day_header">'.$day_of_week.'</span>';
        $experiences = NEWExtractSingleDayOfExperiences($event_id, $value);
        if (count($experiences) > 0) {
            $output_html .= '<div id="friday_agenda_container" class="daily_agenda_container active"><table><tbody>';
            foreach($experiences as $key => $value) {
                $output_html .= '<tr id="event_info_row_'.$value['EXPERIENCE_Name'].'" class="event_info_row" event_name="'.$value['EXPERIENCE_Name'].'"><td class="event_column_time">'.ltrim(date('h:i', strtotime($value['EXPERIENCE_Start_Time'])), 0).' - '.ltrim(date('h:i', strtotime($value['EXPERIENCE_Stop_Time'])), 0).'</td><td class="event_column_details">'.$value['EXPERIENCE_Title'].'</td></tr>';
                $event_html .= NEWRenderEachDailyEventSection($value);
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
	$output_html .= '<h2 style="color:#ffcc00;">Events for the '.$event_data['EVENT_Name'].'</h2>';
	$output_html .= '<p>Over the Meet weekend, we tried to put together several events that will give everyone something to look forward to. All events are optional of course and welcome to any one.</p> To see more information about the events, simply move you mouse over the event itself to display everything you need to know about the event. If you have any questions about one of the events, please feel free to contact us using the form below.</p>';
	$output_html .= '</div>';	
    $output_html .= $event_html;
	$output_html .= '</div>';	
	$output_html .= '		</div>';
	$output_html .= '	</div>	';
	$output_html .= '</div>	';
    $output_html .= '</div>	';
    $output_html .= '</section>';

	$results = [$output_html];
	return $output_html;	
}


/* ************************************************************************ */
/* Funciton:    NEWcreateDateRange                           				    */
/* Description: This function creates an array of dates for the event and   */
/*              returns them as an array.                                   */
/* Returns:     $dates = array of the dates spanned by the event.           */
/* ************************************************************************ */    
function NEWcreateDateRange($start, $end, $format = 'Y-m-d') {
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
/* Funciton:    NEWExtractSingleDayOfExperiences                         	    */
/* Description: This function returns an array of all the experiences for a */
/*              single day of an event. Ordered by EVENT_Start_Time.        */
/* Returns:     Array                                        				*/
/* ************************************************************************ */
function NEWExtractSingleDayOfExperiences($event_id, $experiences_date) {

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
/* Funciton:    NEWRenderEachDailyEventSection                           	    */
/* Description: This function renders a specific event's section of HTML.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function NEWRenderEachDailyEventSection($section) {
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
?>