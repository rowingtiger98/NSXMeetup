<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  REGISTRANT MANAGEMENT                                      */
/*                                                             */
/*  Changes to an event's registrants from the Find / Edit /   */
/*  Delete Registrations tool. Every request must come from a  */
/*  logged-in admin of the event (or an overlord) and carry    */
/*  the session's CSRF token. Only the methods listed below    */
/*  can be called.                                             */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/eventAdmin.php';
require_once __DIR__ . '/paymentMethods.php';  // PrepayOptions() / ChosenPrepayOptions() for the balance
require_once __DIR__ . '/registrantHistory.php';  // AppendRegistrantHistory() - Participant_Reg_History

app_session_start();

const REGISTRANT_METHODS = ['DeleteRegistrant', 'SaveRegistrant', 'SetRegistrantPaid'];

const US_STATES = ['AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
const NSX_COLORS = ['red','white','black','blue','orange','gray','silver','yellow','green','purple','other'];

if (isset($_POST['method']) && in_array($_POST['method'], REGISTRANT_METHODS, true)) {
    $_POST['method']();
}

/* ************************************************************************ */
/* Function:    RegistrantCheckCSRF                                         */
/* Description: Ends the request unless it carries the session's CSRF       */
/*              token, logging why (none sent / none in the session /       */
/*              different) to help track down a failure.                    */
/* ************************************************************************ */
function RegistrantCheckCSRF() {
    $posted = $_POST['csrf_token'] ?? null;
    if (app_csrf_valid(is_string($posted) ? $posted : null)) {
        return;
    }
    $reason = (!is_string($posted) || $posted === '') ? 'no token was sent' : (empty($_SESSION['csrf_token']) ? 'the session has no token' : 'the token does not match the session');
    error_log('REGISTRANTManagement '.($_POST['method'] ?? '').': CSRF check failed - '.$reason.' (user '.($_SESSION['login_user'] ?? '?').')');
    EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
}

/* ************************************************************************ */
/* Function:    RegistrantPostedParticipant                                 */
/* Description: The posted participant_id if they're one of the event's     */
/*              registrants (on its participant list - older registrations  */
/*              have no Event_ID). Ends the request otherwise.              */
/* ************************************************************************ */
function RegistrantPostedParticipant(array $event) {
    $participant_id = filter_var($_POST['participant_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $listed = array_map('intval', array_filter(array_map('trim', explode(',', (string)$event['EVENT_Participants'])), 'strlen'));
    if ($participant_id === false || !in_array($participant_id, $listed, true)) {
        EventAdminRespond('Fail', ['That registration could not be found. Reload the page and try again.']);
    }
    return $participant_id;
}

/* ************************************************************************ */
/* Function:    DeleteRegistrant                                            */
/* Description: Deletes one of the posted event's registrants and takes     */
/*              them off the event's participant list (EVENT_Participants). */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function DeleteRegistrant() {
    list($pdo, $event) = EventAdminLoadEvent();
    RegistrantCheckCSRF();

    $participant_id = RegistrantPostedParticipant($event);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT Participant_Name FROM nsx_meetup_participants WHERE Participant_ID = ?');
        $stmt->execute([$participant_id]);
        $name = $stmt->fetchColumn();

        $pdo->prepare('DELETE FROM nsx_meetup_participants WHERE Participant_ID = ?')->execute([$participant_id]);

        // Re-read the list inside the transaction, so a registration made meanwhile isn't lost
        $stmt = $pdo->prepare('SELECT EVENT_Participants FROM nsx_meetup_events WHERE EVENT_ID = ?');
        $stmt->execute([$event['EVENT_ID']]);
        $remaining = array_filter(array_map('trim', explode(',', (string)$stmt->fetchColumn())), function ($id) use ($participant_id) {
            return $id !== '' && (int)$id !== $participant_id;
        });
        $pdo->prepare('UPDATE nsx_meetup_events SET EVENT_Participants = ? WHERE EVENT_ID = ?')->execute([implode(',', $remaining), $event['EVENT_ID']]);

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('DeleteRegistrant failed for participant '.$participant_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The registration could not be deleted. Please try again.']);
    }

    EventAdminRespond('Pass', ($name !== false ? $name : 'The registrant').' has been deleted.');
}

/* ************************************************************************ */
/* Function:    SaveRegistrant                                              */
/* Description: Saves an admin's changes to one registrant (the Edit        */
/*              Information page): contact details, car, answers to the     */
/*              event's questions and registration type. The car info and   */
/*              image are built here as at registration. When the           */
/*              registrant has a registration type, the balance is worked   */
/*              out again - type cost plus the checked pre-pay options, as  */
/*              at registration; without one (older registrations) it's     */
/*              left alone. A paid registrant stays paid - the response     */
/*              tells the admin when their balance changed.                 */
/* Returns:     JSON ['Pass', [messages]] or ['Fail', [messages]]           */
/* ************************************************************************ */
function SaveRegistrant() {
    list($pdo, $event) = EventAdminLoadEvent();
    RegistrantCheckCSRF();
    $participant_id = RegistrantPostedParticipant($event);

    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_participants WHERE Participant_ID = ?');
    $stmt->execute([$participant_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
        EventAdminRespond('Fail', ['That registration could not be found. Reload the page and try again.']);
    }

    $text = function ($key) { return trim(is_string($_POST[$key] ?? null) ? $_POST[$key] : ''); };
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
    if ($values['Participant_State'] !== '' && !in_array($values['Participant_State'], US_STATES, true)) {
        $errors[] = 'Choose a state from the list.';
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
    $posted_answers = (isset($_POST['answers']) && is_array($_POST['answers'])) ? $_POST['answers'] : [];
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

    /* Registration type - any of the event's types (an admin may use a disabled one) */
    $regtype_cost = null;
    $posted_type = $text('registration_type');
    if ($posted_type === '') {
        $values['Participant_Registration_Type'] = null;
    } else {
        $stmt = $pdo->prepare('SELECT REGTYPE_ID, REGTYPE_Cost FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
        $stmt->execute([(int)$posted_type, $event['EVENT_ID']]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($type) {
            $values['Participant_Registration_Type'] = (int)$type['REGTYPE_ID'];
            $regtype_cost = (int)round($type['REGTYPE_Cost'] * 100);
        } else {
            $errors[] = 'Choose one of this event\'s registration types.';
        }
    }

    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    /* Balance: type cost plus checked pre-pay options, when there's a type and the event collects payments */
    $old_balance = (int)$current['Participant_Balance_Due'];
    $new_balance = $old_balance;
    if (!empty($event['EVENT_Collect_Payments']) && $regtype_cost !== null) {
        $prepay = ChosenPrepayOptions(PrepayOptions($pdo, $event['EVENT_ID']), $answers);
        $new_balance = $regtype_cost + array_sum(array_column($prepay, 'cents'));
        $values['Participant_Balance_Due'] = $new_balance;
    }

    /* Column names come from this function, never from the request. */
    $assignments = implode(', ', array_map(function ($column) { return '`'.$column.'` = :'.$column; }, array_keys($values)));
    $stmt = $pdo->prepare('UPDATE nsx_meetup_participants SET '.$assignments.' WHERE Participant_ID = :participant_id');
    foreach ($values as $column => $value) {
        $stmt->bindValue(':'.$column, $value, $value === null ? PDO::PARAM_NULL : (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
    }
    $stmt->bindValue(':participant_id', $participant_id, PDO::PARAM_INT);
    try {
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('SaveRegistrant failed for participant '.$participant_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The registration could not be saved. Please try again.']);
    }

    $changes = RegistrantEditChanges($pdo, $current, $values, $questions);
    if ($changes) {
        AppendRegistrantHistory($pdo, $participant_id, 'Edited', $changes);
    }

    $messages = [$values['Participant_Name'].'\'s registration has been saved.'];
    if ($new_balance !== $old_balance) {
        $change = 'Balance changed from $'.number_format($old_balance / 100, 2).' to $'.number_format($new_balance / 100, 2).'.';
        if (!empty($current['Participant_Balance_Paid'])) {
            $difference = $new_balance - $old_balance;
            $change .= ' This registrant is marked paid - '.($difference > 0 ? 'collect the extra $'.number_format($difference / 100, 2) : 'refund $'.number_format(-$difference / 100, 2))
                      .', and untick Received if they now owe more.';
        }
        $messages[] = $change;
    }
    EventAdminRespond('Pass', $messages);
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

/* ************************************************************************ */
/* Function:    SetRegistrantPaid                                           */
/* Description: The Received checkbox on the registrant list: marks one of  */
/*              the posted event's registrants paid (posted paid = '1') or  */
/*              not. Saved first; when they become paid, they're then       */
/*              emailed that their payment was received                     */
/*              (SendPaymentReceivedEmail).                                 */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SetRegistrantPaid() {
    list($pdo, $event) = EventAdminLoadEvent();
    RegistrantCheckCSRF();
    $participant_id = RegistrantPostedParticipant($event);
    $paid = (($_POST['paid'] ?? '') === '1') ? 1 : 0;

    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_participants WHERE Participant_ID = ?');
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$participant) {
        EventAdminRespond('Fail', ['That registration could not be found. Reload the page and try again.']);
    }

    try {
        $pdo->prepare('UPDATE nsx_meetup_participants SET Participant_Balance_Paid = ? WHERE Participant_ID = ?')->execute([$paid, $participant_id]);
    } catch (PDOException $e) {
        error_log('SetRegistrantPaid failed for participant '.$participant_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The change could not be saved. Please try again.']);
    }

    if ($paid !== (int)!empty($participant['Participant_Balance_Paid'])) {
        AppendRegistrantHistory($pdo, $participant_id, $paid ? 'Payment marked received' : 'Payment marked not received');
    }

    if (!$paid) {
        EventAdminRespond('Pass', $participant['Participant_Name'].' is marked as not paid.');
    }
    if (!empty($participant['Participant_Balance_Paid'])) {
        EventAdminRespond('Pass', $participant['Participant_Name'].' is marked paid.'); // already was - no second email
    }
    $message = $participant['Participant_Name'].' is marked paid. ';
    $message .= SendPaymentReceivedEmail($pdo, $event, $participant)
        ? 'A payment received email was sent to '.$participant['Participant_Email'].'.'
        : 'The payment received email could not be sent to '.($participant['Participant_Email'] ?: 'them').'.';
    EventAdminRespond('Pass', $message);
}

/* ************************************************************************ */
/* Function:    SendPaymentReceivedEmail                                    */
/* Description: Emails a registrant (from no-reply@nsxmeetup.com) that the  */
/*              organizers received their payment and they're fully         */
/*              registered - with the event's name, dates, their            */
/*              registration type and a link to the event's home page.      */
/* Returns:     bool - whether the email was sent                           */
/* ************************************************************************ */
function SendPaymentReceivedEmail(PDO $pdo, array $event, array $participant) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $event_name = (string)$event['EVENT_Name'];
    $dates = EventDateRange($event['EVENT_Start_Date'] ?? null, $event['EVENT_End_Date'] ?? null);

    $regtype = '';
    if (!empty($participant['Participant_Registration_Type'])) {
        $stmt = $pdo->prepare('SELECT REGTYPE_Name FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ?');
        $stmt->execute([$participant['Participant_Registration_Type']]);
        $regtype = (string)$stmt->fetchColumn();
    }

    $body  = '<html><body style="font-family:Arial,Helvetica,sans-serif;color:#222;max-width:700px;">';
    $body .= '<p>Hello '.$h($participant['Participant_Name']).',</p>';
    $body .= '<p>This email confirms that the organizers have received your payment for <b>'.$h($event_name).'</b>'.($dates !== '' ? ' ('.$h($dates).')' : '')
            .', and you are now fully registered.</p>';
    if ($regtype !== '') {
        $body .= '<p>Registration: <b>'.$h($regtype).'</b></p>';
    }
    $body .= '<p>We look forward to seeing you there!</p>';
    $directory = (string)$event['EVENT_Directory'];
    if (preg_match('/^[A-Za-z0-9_-]+$/', $directory)) {
        $body .= '<p><a href="'.$h(AbsoluteSiteURL('events/'.$directory.'/')).'">Visit the '.$h($event_name).' home page</a> for the schedule and event information.</p>';
    }
    $body .= '<p>Regards,<br>The '.$h($event_name).' organizers</p>';
    $body .= '<p style="font-size:12px;color:#777;margin-top:24px;">This is an automated message from NSXMeetup.com. Please don\'t reply to this email - '
            .'contact general.information@nsxmeetup.com with any questions.</p>';
    $body .= '</body></html>';

    return SendNoReplyEmail(trim((string)$participant['Participant_Email']), $event_name.' - Payment Received', $body);
}

/* ************************************************************************ */
/* Function:    EventDateRange                                              */
/* Description: An event's dates for an email, e.g. "May 28-31, 2026",      */
/*              "May 30 - June 2, 2026", or just the one date.              */
/* Returns:     string, or '' without a start date                          */
/* ************************************************************************ */
function EventDateRange($start, $end) {
    $from = $start ? DateTime::createFromFormat('!Y-m-d', substr((string)$start, 0, 10)) : false;
    $to = $end ? DateTime::createFromFormat('!Y-m-d', substr((string)$end, 0, 10)) : false;
    if (!$from) {
        return '';
    }
    if (!$to || $to <= $from) {
        return $from->format('F j, Y');
    }
    if ($from->format('Y-m') === $to->format('Y-m')) {
        return $from->format('F j').'-'.$to->format('j, Y');
    }
    if ($from->format('Y') === $to->format('Y')) {
        return $from->format('F j').' - '.$to->format('F j, Y');
    }
    return $from->format('F j, Y').' - '.$to->format('F j, Y');
}
