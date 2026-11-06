<?php


/* ************************************************************************ */
/* Funciton:    GetEventSpecificQuestions                               	*/
/* Description: This function returns the event specific questions that     */
/*              have been created by the event administrators as an array.  */
/* Returns:     Array                                        				*/
/* ************************************************************************ */
function GetEventSpecificQuestions($event_id) {
    $questions = Array();
    $conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_event_questions` WHERE `EVENT_ID` = %d ORDER BY `QUESTION_Order_Number`", $event_id);
	$results = $conn->query($query);
	if($results->num_rows > 0) {
        $questions['success'] = True;
        $questions['rows'] = $results;
	} else {
        $questions['success'] = False;
        $questions['rows'] = Array();
    }

	closeDB($conn);
    return $questions;
}

/* ************************************************************************ */
/* Funciton:    RenderBooleanQuestion                                      	*/
/* Description: This function renders the columns and options for a BOOLEAN */
/*              type question.                                              */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderBooleanQuestion($data) {
    $output_html  = '<tr>';
    $output_html .= '    <td>'.$data['QUESTION_Instruction_Text'].'</td>';
    $output_html .= '    <td id="question_driver1" class="table_driver active">';
    $output_html .= '        <input type="checkbox" accounting="'.($data['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.$data['QUESTION_Amount'].'" id="info_checkbox_field_driver1_'.$data['QUESTION_Label'].'" driver="driver1" key="'.$data['QUESTION_Label'].'" desc="'.$data['QUESTION_Description'].'" choice="no" name="driver1_'.$data['QUESTION_Label'].'">';
    $output_html .= '        <label for="info_checkbox_field_driver1_'.$data['QUESTION_Label'].'"></label>';
    $output_html .= '    </td>';
    $output_html .= '    <td id="question_driver2" class="table_driver">';
    $output_html .= '        <input type="checkbox" accounting="'.($data['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.$data['QUESTION_Amount'].'" id="info_checkbox_field_driver2_'.$data['QUESTION_Label'].'" driver="driver2" key="'.$data['QUESTION_Label'].'" desc="'.$data['QUESTION_Description'].'" choice="no" name="driver2_'.$data['QUESTION_Label'].'">';
    $output_html .= '        <label for="info_checkbox_field_driver2_'.$data['QUESTION_Label'].'"></label>';
    $output_html .= '    </td>';
    $output_html .= '</tr>    ';
    // $output_html .= '<tr><td colspan="2">'.print_r($data).'</td></tr>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderSelectQuestion                                       	*/
/* Description: This function renders the columns and options for a SELECT  */
/*              type question.                                              */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderSelectQuestion($data) {

    $choices = json_decode($data['QUESTION_Select_Choices'], true);
    $output_html  = '<tr>';
    $output_html .= '    <td>'.$data['QUESTION_Instruction_Text'].'</td>';

    /* Driver One */
    $output_html .= '    <td id="question_driver1" class="table_driver active">';
    $output_html .= '        <select id="info_question_select_field_driver1_'.$data['QUESTION_Label'].'" driver="driver1" key="'.$data['QUESTION_Label'].'" name="driver1_'.$data['QUESTION_Label'].'" desc="'.$data['QUESTION_Description'].'">';
    $output_html .= '            <option value="" selected disabled >'.$data['QUESTION_Select_Default_Text'].'</option>';
    foreach($choices as $key => $value) {
        $output_html .= '<option value="'.$key.'">'.$value.'</option>';
    }
    $output_html .= '        </select>';
    $output_html .= '    </td>';

    /* Driver Two */
    $output_html .= '    <td id="question_driver2" class="table_driver">';
    $output_html .= '        <select id="info_question_select_field_driver2_'.$data['QUESTION_Label'].'" driver="driver2" key="'.$data['QUESTION_Label'].'" name="driver2_'.$data['QUESTION_Label'].'" desc="'.$data['QUESTION_Description'].'">';
    $output_html .= '        <option value="" selected disabled >'.$data['QUESTION_Select_Default_Text'].'</option>';
    foreach($choices as $key => $value) {
        $output_html .= '<option value="'.$key.'">'.$value.'</option>';
    }
    $output_html .= '        </select>';
    $output_html .= '    </td>';


    $output_html .= '</tr>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderBaseFeeInformation                                   	*/
/* Description: This function renders the event base fee information for    */
/*              registrants.                                                */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderBaseFeeInformation($event_data) {
    $output_html = '<div id="event_base_fee_container" class="event_registration_sub_container active">';
    $output_html .= '<div class="event_registration_sub_container_header">';
    $output_html .= '    <div class="row gtr-uniform">';
    $output_html .= '        <div id="event_registration_sub_container_header_event_base_fee" class="col-3 col-12-xsmall">';
    $output_html .= '            Event Registration Fee';
    $output_html .= '        </div>';
    $output_html .= '        <div class="col-9 col-12-xsmall">';
    $output_html .= '           <p style="font-size:0.85em;">The '.$event_data['EVENT_Name'].' as a registration fee of $'.number_format($event_data['EVENT_Base_Fee'], 2).' dollars per registrant.
                                If there are other optional items that need to be paid for, such as extra experiences that are not covered by the base fee, then they will be added
                                to the participants total should they apply.</p>';
    $output_html .= '        </div>';
    $output_html .= '    </div>';
    $output_html .= '    <div class="table-wrapper">';
    $output_html .= '        <table style="margin-top:1.5em;">';
    $output_html .= '            <thead>';
    $output_html .= '                <tr>';
    $output_html .= '                    <th width="60%"></th>';
    $output_html .= '                    <th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
    $output_html .= '                    <th id="column_driver2" class="table_driver" width="15%">Passenger</th>';
    $output_html .= '                </tr>';
    $output_html .= '            </thead>';
    $output_html .= '            <tbody>';

        $output_html .= '<tr>';
        $output_html .= '<td>Entry Fee to attend the event.</td>';
        $output_html .= '<td id="cost_driver1" class="table_driver active" cost='.($event_data['EVENT_Base_Fee'] * 100).'>$'.number_format($event_data['EVENT_Base_Fee'], 2).'</td>';
        $output_html .= '<td id="cost_driver2" class="table_driver" cost='.($event_data['EVENT_Base_Fee'] * 100).'>$'.number_format($event_data['EVENT_Base_Fee'], 2).'</td>';
        $output_html .= '</tr>';



    $output_html .= '           </tbody>';
    $output_html .= '       </table>';
    $output_html .= '   </div>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    return $output_html;    
}
/* ************************************************************************ */
/* Function:    RenderRegistrationFeeInformation                            */
/* Description: The Event Registration Fee section for an event that        */
/*              collects payments: a drop down of the event's enabled       */
/*              registration types for each registrant. The chosen type's   */
/*              cost (in cents) goes in the cost attribute of cost_driver1  */
/*              / cost_driver2 (see ReactToRegistrationTypeSelection in     */
/*              register.js). Nothing is selected when the page loads, so   */
/*              both costs start at 0.                                      */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderRegistrationFeeInformation($event_data) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Name, REGTYPE_Cost, REGTYPE_Description FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ? AND REGTYPE_Enabled = 1 ORDER BY REGTYPE_Name');
    $stmt->execute([$event_data['EVENT_ID']]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
    $pdo = null;

    $output_html = '<div id="event_base_fee_container" class="event_registration_sub_container active">';
    $output_html .= '<div class="event_registration_sub_container_header">';
    $output_html .= '    <div class="row gtr-uniform">';
    $output_html .= '        <div id="event_registration_sub_container_header_event_base_fee" class="col-3 col-12-xsmall">';
    $output_html .= '            Event Registration Fee';
    $output_html .= '        </div>';
    $output_html .= '        <div class="col-9 col-12-xsmall">';
    if ($types) {
        $output_html .= '           <p style="font-size:0.85em;">Choose the type of registration for each person you are registering for the '.$h($event_data['EVENT_Name']).'.
                                    If there are other optional items that need to be paid for, such as extra experiences that are not covered by the registration, then they will be added
                                    to the participants total should they apply.</p>';
    } else {
        $output_html .= '           <p style="font-size:0.85em;">Registration options for the '.$h($event_data['EVENT_Name']).' have not been set up yet. Please check back soon, or contact the event organizers below.</p>';
    }
    $output_html .= '        </div>';
    $output_html .= '    </div>';
    if ($types) {
        $output_html .= '    <div class="table-wrapper">';
        $output_html .= '        <table style="margin-top:1.5em;">';
        $output_html .= '            <thead>';
        $output_html .= '                <tr>';
        $output_html .= '                    <th width="40%"></th>';
        $output_html .= '                    <th id="column_driver1" class="table_driver active" width="30%">Driver</th>';
        $output_html .= '                    <th id="column_driver2" class="table_driver" width="30%">Passenger</th>';
        $output_html .= '                </tr>';
        $output_html .= '            </thead>';
        $output_html .= '            <tbody>';
        $output_html .= '<tr>';
        $output_html .= '<td>Registration type</td>';
        foreach (['driver1' => ' active', 'driver2' => ''] as $driver => $active) {
            $output_html .= '<td id="cost_'.$driver.'" class="table_driver'.$active.'" cost="0">';
            $output_html .= '<select id="regtype_select_'.$driver.'" class="regtype_select" driver="'.$driver.'">';
            $output_html .= '<option value="" cost="0" description="" selected>- Select a registration type -</option>';
            foreach ($types as $type) {
                $output_html .= '<option value="'.(int)$type['REGTYPE_ID'].'" cost="'.(int)round($type['REGTYPE_Cost'] * 100).'" description="'.$h($type['REGTYPE_Description']).'">';
                $output_html .= $h($type['REGTYPE_Name']).' - $'.number_format((float)$type['REGTYPE_Cost'], 2).'</option>';
            }
            $output_html .= '</select>';
            $output_html .= '<div id="regtype_description_'.$driver.'" class="regtype_choice_description"></div>';
            $output_html .= '</td>';
        }
        $output_html .= '</tr>';
        $output_html .= '           </tbody>';
        $output_html .= '       </table>';
        $output_html .= '   </div>';
    }
    $output_html .= '</div>';
    $output_html .= '</div>';

    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderEventSpecificQuestions                               	*/
/* Description: This function renders the event specific questions that     */
/*              have been created by the event administrators.              */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderEventSpecificQuestions($event_data) {

    $results = GetEventSpecificQuestions($event_data['EVENT_ID']);
    if($results['success']) {
        $output_html = '<div id="event_info_container" class="event_registration_sub_container active" collect_payments="'.($event_data['EVENT_Collect_Payments']?"yes":"no").'">';
        $output_html .= '<div class="event_registration_sub_container_header">';
        $output_html .= '    <div class="row gtr-uniform">';
        $output_html .= '        <div id="event_registration_sub_container_header_event_info" class="col-3 col-12-xsmall">';
        $output_html .= '            Other Information';
        $output_html .= '        </div>';
        $output_html .= '        <div class="col-9 col-12-xsmall">';
        $output_html .= '            <p style="font-size:0.85em;">For each checkbox question below, click the box to indicate a YES answer. If you are registering a passenger, or another driver, please ';
        $output_html .= '            also check the appropriate boxes for them as well. An empty box is assumed to mean NO. For each dropdown window, please select the best answer for yourself and, if';
        $output_html .= '            applicable, the best answer for any other registrant you are entering.</p>';
        $output_html .= '        </div>';
        $output_html .= '    </div>';
        $output_html .= '    <div class="table-wrapper">';
        $output_html .= '        <table style="margin-top:1.5em;">';
        $output_html .= '            <thead>';
        $output_html .= '                <tr>';
        $output_html .= '                    <th width="60%"></th>';
        $output_html .= '                    <th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
        $output_html .= '                    <th id="column_driver2" class="table_driver" width="15%">Passenger</th>';
        $output_html .= '                </tr>';
        $output_html .= '            </thead>';
        $output_html .= '            <tbody>';
    
        while($row = $results['rows']->fetch_assoc()) {
            if (!$row['QUESTION_Active']) {
                continue;
            }
            switch($row['QUESTION_Type']) {
                case('BOOLEAN'):
                    $output_html .= RenderBooleanQuestion($row); 
                    break;

                case('SELECT'):
                    $output_html .= RenderSelectQuestion($row); 
                    break;

                default:
                    break;
            }
            // $output_html .= '<tr><td>'.$row['QUESTION_Type'].'</td><td>'.$row['QUESTION_Instruction_Text'].'</td></tr>';
        }
       
        // Render the Cost values for Drivers One and Two
        if ($event_data['EVENT_Collect_Payments']) {
            $output_html .= '<tr>';
            $output_html .= '<td>TOTAL COST to attend the event based on selected events attending.</td>';
            // Totals start at $0.00 - the registration type drop downs add each registrant's fee
            $output_html .= '<td id="cost_driver1" class="table_driver active"><span id="driver1_cost" total="0">$0.00</span></td>';
            $output_html .= '<td id="cost_driver2" class="table_driver"><span id="driver2_cost" child_discount_applied="no" total="0">$0.00</span></td>';
            $output_html .= '</tr>';
        }


        $output_html .= '           </tbody>';
        $output_html .= '       </table>';
        $output_html .= '   </div>';
        $output_html .= '</div>';
        $output_html .= '</div>';

    } else {
        $output_html = '';
    }

    return $output_html;
}	
?>