<?php

/* ************************************************************************ */
/* Funciton:    RenderContactInformation                                   	*/
/* Description: This function renders the contact information for an event. */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderContactInformation($event_id) {

    if ($event_id <= 0) {
        $output_html = RenderGenericContactInformationSection();
    } else {
        $conn = connectDB('kirbypar_nsx_meetup');
        $primary_email_string = '';
        $secondary_email_string = '';
        $query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID` = %d", $event_id);
        $results = $conn->query($query);
        if($results->num_rows > 0) {
            $row = $results->fetch_assoc();

            /* Primary Contact */
            if($row['EVENT_Primary_Email_Contact'] == '' || $row['EVENT_Primary_Email_Contact'] == null) {
                $output_html = RenderGenericContactInformationSection();
                return $output_html;
            } else {
                $primary_emails = json_decode($row['EVENT_Primary_Email_Contact'], true);
                $primary_email_string = implode(',', $primary_emails);
            }
            
            /* Secondary Contacts */
            if(!($row['EVENT_Secondary_Email_Contact'] == '' || $row['EVENT_Secondary_Email_Contact'] == null)) {                      
                $secondary_emails = json_decode($row['EVENT_Secondary_Email_Contact'], true);
                $secondary_email_string = implode(',', $secondary_emails);
            } 
            $output_html = RenderTargetedContactInformationSection($primary_email_string, $secondary_email_string);
        } else {
            $output_html = RenderGenericContactInformationSection();
        }
        closeDB($conn);
    }    

    return $output_html;
}


/* ************************************************************************ */
/* Funciton:    RenderTargetedContactInformationSection                    	*/
/* Description: This function renders the targeted contact information.     */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderTargetedContactInformationSection($primary_email_string, $secondary_email_string) {
    $output_html  = '<div id="message_section">';
    $output_html .= '    <div class="row gtr-uniform">';
    $output_html .= '    	<div class="col-6 col-12-xsmall">';
    $output_html .= '    		<input type="text" name="name" id="general_name" value="" placeholder="Name" />';
    $output_html .= '    	</div>';
    $output_html .= '    	<div class="col-6 col-12-xsmall">';
    $output_html .= '    		<input type="text" name="email" id="general_email" value="" placeholder="Email Address" />';
    $output_html .= '    	</div>';
    $output_html .= '    	<!-- Break -->';
    $output_html .= '    	<div class="col-12">';
    $output_html .= '    		<textarea name="message" id="general_message" placeholder="Type your message here." rows="6"></textarea>';
    $output_html .= '    	</div>';
    $output_html .= '    	<!-- Break -->';
    $output_html .= '    	<div class="g-recaptcha" data-sitekey="6Le2LnMeAAAAANjfxEG0YQt5XZub-PCUIT96DPea"></div>';
    $output_html .= '    	<div class="col-12">';
    $output_html .= '    		<ul class="actions">';
    $output_html .= '    			<li><a href="#" id="button_action_send_message" action="general_message" primary_contact="'.$primary_email_string.'" secondary_contact="'.$secondary_email_string.'" class="button primary">Send Message</a></li>';
    $output_html .= '    		</ul>';
    $output_html .= '    	</div>';
    $output_html .= '    </div>';
    $output_html .= '</div>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderGenericContactInformationSection                     	*/
/* Description: This function renders the generic contact information. In   */
/*              the event that there is a DB error, or this is not an event */
/*              specific page, then the generic information will be used.   */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderGenericContactInformationSection() {
    $output_html  = '<div id="message_section">';
    $output_html .= '    <div class="row gtr-uniform">';
    $output_html .= '    	<div class="col-6 col-12-xsmall">';
    $output_html .= '    		<input type="text" name="name" id="general_name" value="" placeholder="Name" />';
    $output_html .= '    	</div>';
    $output_html .= '    	<div class="col-6 col-12-xsmall">';
    $output_html .= '    		<input type="text" name="email" id="general_email" value="" placeholder="Email Address" />';
    $output_html .= '    	</div>';
    $output_html .= '    	<!-- Break -->';
    $output_html .= '    	<div class="col-12">';
    $output_html .= '    		<textarea name="message" id="general_message" placeholder="Type your message here." rows="6"></textarea>';
    $output_html .= '    	</div>';
    $output_html .= '    	<!-- Break -->';
    $output_html .= '    	<div class="g-recaptcha" data-sitekey="6Le2LnMeAAAAANjfxEG0YQt5XZub-PCUIT96DPea"></div>';
    $output_html .= '    	<div class="col-12">';
    $output_html .= '    		<ul class="actions">';
    $output_html .= '    			<li><a href="#" id="button_action_send_message" action="general_message" primary_contact="ron@nsxmeetup.com" secondary_contact="" class="button primary">Send Message</a></li>';
    $output_html .= '    		</ul>';
    $output_html .= '    	</div>';
    $output_html .= '    </div>';
    $output_html .= '</div>';

    return $output_html;
}
?>