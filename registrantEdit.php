<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  REGISTRANT EDIT                                            */
/*                                                             */
/*  Shared by the admins' Edit Information tool                */
/*  (PARTICIPANTManagement.php / REGISTRANTManagement.php) and */
/*  a registrant editing their own registration from My        */
/*  Account (MyRegistrations.php): the registration type and   */
/*  question widgets, checking and saving an edit, and the     */
/*  list of what changed. Functions and constants only - no    */
/*  endpoint of its own.                                       */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/paymentMethods.php';      // PrepayOptions() / ChosenPrepayOptions() for the balance
require_once __DIR__ . '/registrantHistory.php';   // AppendRegistrantHistory(), RegistrantHistoryChange()

const US_STATES = ['AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
const CA_PROVINCES = ['AB','BC','MB','NB','NL','NS','NT','NU','ON','PE','QC','SK','YT'];  // offered on the registration form too
const NSX_COLORS = ['red','white','black','blue','orange','gray','silver','yellow','green','purple','other'];

/* ************************************************************************ */
/* Function:    RenderEditRegistrationType                                  */
/* Description: The registrant's registration type and their balance, with  */
/*              a preview of the new balance as the type and pre-pay        */
/*              options change (UpdateEditBalancePreview in account.js) -   */
/*              type cost plus checked pre-pay options, as at registration. */
/*              Only when the event collects payments. An admin can pick    */
/*              any of the event's types (or none); a registrant editing    */
/*              their own registration only the enabled ones, or the one    */
/*              they already have.                                          */
/* Inputs:      $for_registrant - true on the registrant's My Account page  */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderEditRegistrationType($event_id, array $participant, $for_registrant = false) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT EVENT_Collect_Payments FROM nsx_meetup_events WHERE EVENT_ID = ?');
    $stmt->execute([(int)$event_id]);
    if (!$stmt->fetchColumn()) {
        return '';
    }
    $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Name, REGTYPE_Cost, REGTYPE_Enabled FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ? ORDER BY REGTYPE_Name');
    $stmt->execute([(int)$event_id]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $current_type = (string)($participant['Participant_Registration_Type'] ?? '');
    $balance = (int)$participant['Participant_Balance_Due'];

    $output_html  = '<div id="edit_registration_type_container" class="event_registration_sub_container active" current_balance="'.$balance.'">';
    $output_html .= '  <div class="row gtr-25 gtr-uniform">';
    $output_html .= '    <div class="col-6 col-12-small">';
    $output_html .= '      <label for="edit_registration_type">Registration Type</label>';
    $output_html .= '      <select id="edit_registration_type">';
    if (!$for_registrant || $current_type === '') {
        $output_html .= '        <option value="" cost="">- No registration type -</option>';
    }
    foreach ($types as $type) {
        $id = (string)(int)$type['REGTYPE_ID'];
        if ($for_registrant && empty($type['REGTYPE_Enabled']) && $id !== $current_type) {
            continue;
        }
        $output_html .= '<option value="'.$id.'" cost="'.(int)round($type['REGTYPE_Cost'] * 100).'"'.($id === $current_type ? ' selected' : '').'>'
                       .$h($type['REGTYPE_Name']).' - $'.number_format((float)$type['REGTYPE_Cost'], 2).(!$for_registrant && empty($type['REGTYPE_Enabled']) ? ' (disabled)' : '').'</option>';
    }
    $output_html .= '      </select>';
    $output_html .= '    </div>';
    $output_html .= '    <div class="col-6 col-12-small">';
    $output_html .= '      <label>'.($for_registrant ? 'Your Total' : 'Balance').'</label>';
    $output_html .= '      <p id="edit_balance_preview" class="edit_balance_preview">$'.number_format($balance / 100, 2).'</p>';
    $output_html .= '    </div>';
    $output_html .= '  </div>';
    if ($for_registrant) {
        $output_html .= '  <p id="edit_balance_note" class="edit_balance_note">Your total is the registration type\'s cost plus any paid options you choose below (before any processing fee). '
                       .'If it changes, the event organizers are told so they can collect or refund the difference.</p>';
    } else {
        $output_html .= '  <p id="edit_balance_note" class="edit_balance_note">The balance is the registration type\'s cost plus the checked pre-pay options below. '
                       .($current_type === '' ? 'This registration has no registration type, so its balance won\'t be recalculated unless you choose one.' : '')
                       .(!empty($participant['Participant_Balance_Paid']) ? ' This registrant is marked paid.' : '').'</p>';
    }
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Funciton:    RenderParticipantQuestionsAndAnswers                       	*/
/* Description: This function renders the event's questions with the        */
/*              participants current answers. This is for editing.          */
/* Inputs:      event_id: The event in question.                            */
/*              current_selctions:  The participants current answers to the */
/*                                  events questions.                       */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderParticipantQuestionsAndAnswers ($event_id, $current_selections) {
    $output_html  = '    <div class="table-wrapper">';
    $output_html .= '        <table style="margin-top:1.5em;">';
    $output_html .= '            <thead>';
    $output_html .= '                <tr>';
    $output_html .= '                    <th width="60%"></th>';
    $output_html .= '                    <th id="column_driver1" class="table_driver active" width="15%">Driver</th>';
    $output_html .= '                </tr>';
    $output_html .= '            </thead>';
    $output_html .= '            <tbody>';

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
			
	/************************************************/
	/* Search each registered "User" one at a time. */
	/************************************************/

	$question_query = sprintf("SELECT * FROM `nsx_meetup_event_questions` WHERE `EVENT_ID`='%s' ORDER BY `QUESTION_Order_Number`", $event_id);
	$result = $link->query($question_query);
	if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if (!$row['QUESTION_Active']) {
                continue; // as on the registration form (answers to retired questions are kept when saving)
            }
            $answer = (string)($current_selections[$row['QUESTION_Label']] ?? '');
            switch($row['QUESTION_Type']) {
                case('BOOLEAN'):
                    $is_checked = ($answer == "yes") ? " checked" : "";
                    $output_html .= '<tr>';
                    $output_html .= '    <td>'.$row['QUESTION_Instruction_Text'].'</td>';
                    $output_html .= '    <td id="column_driver1" class="table_driver active">';
                    $output_html .= '        <input type="checkbox" accounting="'.($row['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.$row['QUESTION_Amount'].'" id="info_checkbox_field_driver1_'.$row['QUESTION_Label'].'" driver="driver1" key="'.$row['QUESTION_Label'].'" start_value="'.htmlspecialchars($answer, ENT_QUOTES).'" name="driver1_'.$row['QUESTION_Label'].'" desc="'.htmlspecialchars((string)$row['QUESTION_Description'], ENT_QUOTES).'"'.$is_checked.'>';
                    $output_html .= '        <label for="info_checkbox_field_driver1_'.$row['QUESTION_Label'].'"></label>';
                    $output_html .= '    </td>';
                    $output_html .= '</tr>    ';
                    break;

                case('SELECT'):
                    $output_html .= RenderEditSelectQuestion($row, $current_selections); 
                    break;

                default:
                    break;
            }            
        }
    }
    $output_html .= '           </tbody>';
    $output_html .= '       </table>';
    $output_html .= '   </div>';    
    return ($output_html);
}

/* ************************************************************************ */
/* Funciton:    RenderEditSelectQuestion                                   	*/
/* Description: This function renders the columns and options for a SELECT  */
/*              type question.                                              */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function RenderEditSelectQuestion($data, $current_selections) {

    $choices = json_decode((string)$data['QUESTION_Select_Choices'], true) ?: [];
    $output_html  = '<tr>';
    $output_html .= '    <td>'.$data['QUESTION_Instruction_Text'].'</td>';

    /* Driver One */
    $output_html .= '    <td id="question_driver1" class="table_driver active">';
    $output_html .= '        <select id="info_question_select_field_driver1_'.$data['QUESTION_Label'].'" accounting="'.($data['QUESTION_Add_To_Basket']?"yes":"no").'" cost="'.(float)$data['QUESTION_Amount'].'" driver="driver1" key="'.$data['QUESTION_Label'].'" name="driver1_'.$data['QUESTION_Label'].'" start_value="'.htmlspecialchars((string)($current_selections[$data['QUESTION_Label']] ?? ''), ENT_QUOTES).'" desc="'.htmlspecialchars((string)$data['QUESTION_Description'], ENT_QUOTES).'">';
    $output_html .= '            <option value="" disabled >'.htmlspecialchars((string)$data['QUESTION_Select_Default_Text'], ENT_QUOTES).'</option>';
    foreach($choices as $key => $value) {
        $current = (string)($current_selections[$data['QUESTION_Label']] ?? '');
        $selected_text = ((string)$key === $current || $value === $current) ? " selected" : "";  // stored as the key (older ones as the text)
        $output_html .= '<option value="'.htmlspecialchars((string)$key, ENT_QUOTES).'"'.$selected_text.'>'.htmlspecialchars((string)$value, ENT_QUOTES).'</option>';
    }
    $output_html .= '        </select>';
    $output_html .= '    </td>';
    $output_html .= '</tr>';

    return $output_html;
}

/* ************************************************************************ */
/* Function:    RegistrantApplyEdit                                         */
/* Description: Checks an edit of one registrant and works out what to save */
/*              - contact details, car, answers to the event's questions,   */
/*              registration type and the balance (type cost plus chosen    */
/*              pre-pay options, when the event collects payments). Used by */
/*              the admins' Edit Information (SaveRegistrant) and by a      */
/*              registrant editing their own registration from My Account   */
/*              (SaveMyRegistration in MyRegistrations.php).                */
/* Inputs:      $current - the registrant's row, $input - the posted form   */
/*              (name, email, cell, street, city, state, zip,               */
/*              car_generation, car_year, car_color, car_make, car_model,   */
/*              registration_type, answers[label]), $by_admin - an admin    */
/*              may use any of the event's types (or none); a registrant    */
/*              only an enabled type or the one they already have.          */
/* Returns:     array - errors, values (column => value), questions,        */
/*              old_balance, new_balance (cents)                            */
/* ************************************************************************ */
function RegistrantApplyEdit(PDO $pdo, array $event, array $current, array $input, $by_admin) {
    $text = function ($key) use ($input) { return trim(is_string($input[$key] ?? null) ? $input[$key] : ''); };
    $errors = [];
    $values = [
        'Participant_Name'    => $text('name'),
        'Participant_Email'   => $text('email'),
        'Participant_Cell'    => $text('cell'),
        'Participant_Address' => $text('street'),
        'Participant_City'    => $text('city'),
        'Participant_State'   => strtoupper($text('state')),
        'Participant_Zip'     => $text('zip'),
    ];
    if ($values['Participant_Name'] === '' || mb_strlen($values['Participant_Name']) > 128) {
        $errors[] = 'A name of up to 128 characters is required.';
    }
    if (!filter_var($values['Participant_Email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($values['Participant_State'] !== '' && !in_array($values['Participant_State'], array_merge(US_STATES, CA_PROVINCES), true)) {
        $errors[] = 'Choose a state or province from the list.';
    }
    foreach (['Participant_Cell' => 32, 'Participant_Address' => 128, 'Participant_City' => 64, 'Participant_Zip' => 16] as $column => $max) {
        if (mb_strlen($values[$column]) > $max) {
            $errors[] = str_replace('Participant_', '', $column).' must be '.$max.' characters or fewer.';
        }
    }

    /* Car - built as at registration (EnterDriverInformation in GENERALFunctions.php).
       Riders and children have no car questions, so their stored car is left alone. */
    $is_passenger = in_array($current['Participant_Role'], ['riding', 'child'], true);
    $generation = $text('car_generation');
    $year = $text('car_year');
    if ($is_passenger) {
        $car = null;
    } elseif ($generation === 'Other') {
        $car = ['car_make' => $text('car_make'), 'car_model' => $text('car_model'), 'car_year' => $year];
        $values['Participant_Is_NSX'] = 0;
        $values['Participant_Car_Image'] = 'Other_car.png';
        if ($car['car_make'] === '' || $car['car_model'] === '') {
            $errors[] = 'Enter the car\'s make and model.';
        }
    } elseif ($generation === 'First_Generation' || $generation === 'Second_Generation') {
        $color = strtolower($text('car_color'));
        $car = ['car_color' => $color, 'car_year' => $year];
        $values['Participant_Is_NSX'] = 1;
        if (!ctype_digit($year) || !in_array($color, NSX_COLORS, true)) {
            $errors[] = 'Choose the NSX\'s year and color.';
        }
        $version = ((int)$year <= 2001) ? 'popup' : (((int)$year < 2017) ? 'nonpopup' : 'new');
        $values['Participant_Car_Image'] = strtolower($generation).'_'.$version.'_'.$color.'.png';
    } else {
        $errors[] = 'Choose the car\'s generation.';
        $car = [];
    }
    if ($car !== null) {
        $values['Participant_Car_Info'] = json_encode($car);
    }

    /* Answers to the event's questions, merged into what's stored (answers to removed questions are kept) */
    $answers = json_decode((string)$current['Participant_Events_Attending'], true);
    $answers = is_array($answers) ? $answers : [];
    $posted_answers = (isset($input['answers']) && is_array($input['answers'])) ? $input['answers'] : [];
    $stmt = $pdo->prepare('SELECT QUESTION_Label, QUESTION_Description, QUESTION_Type, QUESTION_Select_Choices FROM nsx_meetup_event_questions WHERE EVENT_ID = ?');
    $stmt->execute([$event['EVENT_ID']]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($questions as $question) {
        $label = $question['QUESTION_Label'];
        if (!array_key_exists($label, $posted_answers) || !is_string($posted_answers[$label])) {
            continue;
        }
        $answer = $posted_answers[$label];
        if ($question['QUESTION_Type'] === 'BOOLEAN') {
            $answers[$label] = ($answer === 'yes') ? 'yes' : 'no';
        } elseif ($question['QUESTION_Type'] === 'SELECT') {
            $choices = json_decode((string)$question['QUESTION_Select_Choices'], true);
            if ($answer === '' || (is_array($choices) && array_key_exists($answer, $choices))) {
                $answers[$label] = $answer;
            }
        }
    }
    $values['Participant_Events_Attending'] = json_encode($answers);

    /* Registration type - an admin may use any of the event's types (even a disabled one) or none;
       a registrant an enabled type or the one they already have. Not on the form when the event
       doesn't collect payments, so it's left as it is. */
    $regtype_cost = null;
    $current_type = (string)($current['Participant_Registration_Type'] ?? '');
    if (!$by_admin && empty($event['EVENT_Collect_Payments'])) {
        $posted_type = $current_type;
    } else {
        $posted_type = $text('registration_type');
    }
    if ($posted_type === '') {
        if (!$by_admin && $current_type !== '') {
            $errors[] = 'Choose a registration type.';
        }
        $values['Participant_Registration_Type'] = null;
    } else {
        $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Cost, REGTYPE_Enabled FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
        $stmt->execute([(int)$posted_type, $event['EVENT_ID']]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($type && ($by_admin || !empty($type['REGTYPE_Enabled']) || (string)(int)$type['REGTYPE_ID'] === $current_type)) {
            $values['Participant_Registration_Type'] = (int)$type['REGTYPE_ID'];
            $regtype_cost = (int)round($type['REGTYPE_Cost'] * 100);
        } else {
            $errors[] = 'Choose one of this event\'s registration types.';
        }
    }

    /* Balance: type cost plus checked pre-pay options, when there's a type and the event collects payments */
    $old_balance = (int)$current['Participant_Balance_Due'];
    $new_balance = $old_balance;
    if (!$errors && !empty($event['EVENT_Collect_Payments']) && $regtype_cost !== null) {
        $prepay = ChosenPrepayOptions(PrepayOptions($pdo, $event['EVENT_ID']), $answers);
        $new_balance = $regtype_cost + array_sum(array_column($prepay, 'cents'));
        $values['Participant_Balance_Due'] = $new_balance;
    }

    return ['errors' => $errors, 'values' => $values, 'questions' => $questions, 'old_balance' => $old_balance, 'new_balance' => $new_balance];
}

/* ************************************************************************ */
/* Function:    RegistrantStoreEdit                                         */
/* Description: Saves a checked edit (RegistrantApplyEdit) and notes what   */
/*              changed in the registrant's history.                        */
/* Inputs:      $action - the history entry's action, e.g. 'Edited'         */
/* Returns:     array of changes (RegistrantEditChanges), or false if the   */
/*              save failed                                                 */
/* ************************************************************************ */
function RegistrantStoreEdit(PDO $pdo, $participant_id, array $current, array $edit, $action) {
    $values = $edit['values'];
    /* Column names come from RegistrantApplyEdit, never from the request. */
    $assignments = implode(', ', array_map(function ($column) { return '`'.$column.'` = :'.$column; }, array_keys($values)));
    $stmt = $pdo->prepare('UPDATE nsx_meetup_participants SET '.$assignments.' WHERE Participant_ID = :participant_id');
    foreach ($values as $column => $value) {
        $stmt->bindValue(':'.$column, $value, $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
    }
    $stmt->bindValue(':participant_id', (int)$participant_id, PDO::PARAM_INT);
    try {
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('RegistrantStoreEdit failed for participant '.$participant_id.': '.$e->getMessage());
        return false;
    }

    $changes = RegistrantEditChanges($pdo, $current, $values, $edit['questions']);
    if ($changes) {
        AppendRegistrantHistory($pdo, $participant_id, $action, $changes);
    }
    return $changes;
}

/* ************************************************************************ */
/* Function:    RegistrantEditChanges                                       */
/* Description: What an Edit Information save changed, in words, for the   */
/*              registrant's history: contact details, car, registration    */
/*              type, balance and answers to the event's questions.         */
/* Inputs:      $current - the row before the save, $values - the columns   */
/*              saved, $questions - the event's questions.                  */
/* Returns:     array - changes for AppendRegistrantHistory                 */
/* ************************************************************************ */
function RegistrantEditChanges(PDO $pdo, array $current, array $values, array $questions) {
    $changes = [];
    $labels = ['Participant_Name' => 'Name', 'Participant_Email' => 'Email', 'Participant_Cell' => 'Cell', 'Participant_Address' => 'Street Address',
               'Participant_City' => 'City', 'Participant_State' => 'State', 'Participant_Zip' => 'Zip'];
    foreach ($labels as $column => $label) {
        $changes[] = RegistrantHistoryChange($label, $current[$column] ?? '', $values[$column]);
    }

    $car = function ($json) {
        $info = json_decode((string)$json, true);
        $info = is_array($info) ? $info : [];
        $parts = !empty($info['car_make'])
            ? [$info['car_year'] ?? '', $info['car_make'], $info['car_model'] ?? '']
            : [$info['car_year'] ?? '', ucfirst((string)($info['car_color'] ?? '')), !empty($info['car_color']) ? 'NSX' : ''];
        return trim(implode(' ', array_filter(array_map('strval', $parts), 'strlen')));
    };
    $changes[] = RegistrantHistoryChange('Car', $car($current['Participant_Car_Info'] ?? ''), $car($values['Participant_Car_Info'] ?? $current['Participant_Car_Info'] ?? ''));

    $type_name = function ($id) use ($pdo) {
        if (empty($id)) {
            return '';
        }
        $stmt = $pdo->prepare('SELECT REGTYPE_Name FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ?');
        $stmt->execute([(int)$id]);
        $name = $stmt->fetchColumn();
        return $name !== false ? (string)$name : '#'.(int)$id;
    };
    if ((string)($current['Participant_Registration_Type'] ?? '') !== (string)$values['Participant_Registration_Type']) {
        $changes[] = RegistrantHistoryChange('Registration Type', $type_name($current['Participant_Registration_Type'] ?? null), $type_name($values['Participant_Registration_Type']));
    }

    if (array_key_exists('Participant_Balance_Due', $values)) {
        $money = function ($cents) { return '$'.number_format((int)$cents / 100, 2); };
        $changes[] = RegistrantHistoryChange('Balance', $money($current['Participant_Balance_Due'] ?? 0), $money($values['Participant_Balance_Due']));
    }

    $old_answers = json_decode((string)($current['Participant_Events_Attending'] ?? ''), true);
    $old_answers = is_array($old_answers) ? $old_answers : [];
    $new_answers = json_decode($values['Participant_Events_Attending'], true);
    foreach ($questions as $question) {
        $label = $question['QUESTION_Label'];
        $choices = ($question['QUESTION_Type'] === 'SELECT') ? json_decode((string)$question['QUESTION_Select_Choices'], true) : null;
        $boolean = ($question['QUESTION_Type'] === 'BOOLEAN');
        $answer = function ($value) use ($choices, $boolean) {
            $value = (string)$value;
            if ($boolean) {
                return ($value === 'yes') ? 'yes' : 'no'; // unanswered counts as no
            }
            return (is_array($choices) && isset($choices[$value]) && is_string($choices[$value])) ? $choices[$value] : $value;
        };
        $field = ($question['QUESTION_Description'] ?? '') !== '' ? mb_strimwidth($question['QUESTION_Description'], 0, 60, '...') : $label;
        $changes[] = RegistrantHistoryChange($field, $answer($old_answers[$label] ?? ''), $answer($new_answers[$label] ?? ''));
    }

    return array_values(array_filter($changes));
}
