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
require_once __DIR__ . '/registrantEdit.php';     // RegistrantApplyEdit() / RegistrantStoreEdit(), US_STATES

app_session_start();

const REGISTRANT_METHODS = ['DeleteRegistrant', 'SaveRegistrant', 'SetRegistrantPaid'];


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

    $edit = RegistrantApplyEdit($pdo, $event, $current, $_POST, true);
    if ($edit['errors']) {
        EventAdminRespond('Fail', $edit['errors']);
    }
    if (RegistrantStoreEdit($pdo, $participant_id, $current, $edit, 'Edited') === false) {
        EventAdminRespond('Fail', ['The registration could not be saved. Please try again.']);
    }
    $values = $edit['values'];
    $old_balance = $edit['old_balance'];
    $new_balance = $edit['new_balance'];

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
