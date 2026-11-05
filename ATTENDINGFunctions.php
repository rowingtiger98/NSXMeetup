<?php
/* ************************************************************************ */
/* Funciton:    NEWRenderAttendingInformation                            	    */
/* Description: This function renders the attending driver / riders page.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function NEWRenderAttendingInformation($event_data) {
	$event_id = $event_data['EVENT_ID'];
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = %s", $event_id);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
    	$row = $result->fetch_assoc();
		$registered_participants = $row['EVENT_Participants'];
	}  else {
        $output_html  = '<section id="agenda" class="opener">';
        $output_html .= '<div id="agenda_info" class="inner">';  
        $output_html .= '<p>No event with EVENT_ID = ' . $event_id . '</p>';
        $output_html .= '</div>';
        $output_html .= '</section>';
        return $output_html;       
    }

    if (strlen($registered_participants) <= 0) {
        $output_html  = '<section id="agenda" class="opener">';
        $output_html .= '<div id="agenda_info" class="inner" style="margin-top:2em;">';
        $output_html .= "<h3>Attending the " .$row['EVENT_Name'] . "</h3>";
        $output_html .= "<p>There is no one currently signed up for this event. Head back to the main page and be the first to register!</p>";  
        $output_html .= '</div>';
        $output_html .= '</section>';
        return $output_html;   

    } else {

        $output_html  = '<section id="agenda" class="opener">';
        $output_html .= '<div id="agenda_info" class="inner">';  
        $output_html .= '<table style="margin-top: 2rem;"><thead><tr><th>Name</th><th>State</th><th style="text-align:right;">Car</th></tr></thead><tbody>';
        $parts_query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID` IN  (%s)", $registered_participants);
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
                    // if ($social_information[$key] == '' && $key != 'email') {
                    //     continue;
                    // }

                    if (isset($social_information[$key]) && trim($social_information[$key]) === '' && $key !== 'email') {
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

            if (!($parts_row["Participant_Wait_Listed"])) {
                $output_html .= '<tr><td style="vertical-align:middle;" width="65%">'.$parts_row["Participant_Display_Name"].' '.$more_info_link.'<div id="part_extra_info_'.$parts_row['Participant_ID'].'" class="part_extra_info">'.$shared_html.'</div></td><td style="vertical-align:middle;" class="table_column">'.$parts_row["Participant_State"].'</td><td style="text-align:right;"   class="table_column"><img src="../../images/'.$parts_row['Participant_Car_Image'].'" /></td></tr>';
            }
            // $output_html .= '<tr><td style="vertical-align:middle;">'.$parts_row["Participant_Display_Name"].'</div></td><td style="vertical-align:middle;">'.$parts_row["Participant_State"].'</td><td style="text-align:right;"><img src="images/'.$parts_row['Driver_Car_Image'].'" /></td></tr>';

        }		
        }

        $output_html .= '</tbody></table>';
        $output_html .= '</div>';
        $output_html .= '</section>';
    }
    closeDB($conn); 
    return $output_html;                
}

?>