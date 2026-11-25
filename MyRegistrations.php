<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  MY REGISTRATIONS                                           */
/*                                                             */
/*  My Account > Events You've Registered For. A logged-in     */
/*  user's registrations - the ones their account manages      */
/*  (nsx_meetup_participants.Participant_User_ID): a list of   */
/*  upcoming and past events, a copy of each confirmation,     */
/*  and editing a registration until the day before its event  */
/*  starts. An edit is saved as the admins' Edit Information   */
/*  saves it (registrantEdit.php); the event's contacts are    */
/*  emailed what changed and any change to the cost, and the   */
/*  registrant an updated confirmation.                        */
/*                                                             */
/*  Every method is for the logged-in user's own account       */
/*  ('self' - the user_id comes from the session), and the     */
/*  save must carry the session's CSRF token.                  */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/GENERALFunctions.php';   // BuildConfirmationPage(), GetEventInformationFromID()
require_once __DIR__ . '/ACCOUNTManagement.php';  // UserAccountCars(), RenderMyAccountCar(), MY_ACCOUNT_* choices
require_once __DIR__ . '/registrantEdit.php';     // RegistrantApplyEdit(), RegistrantStoreEdit(), the edit widgets

require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
const MY_REGISTRATION_METHODS = [
    'RenderMyRegistrations'            => 'self',
    'RenderMyRegistrationConfirmation' => 'self',
    'RenderMyRegistrationEdit'         => 'self',
    'SaveMyRegistration'               => 'self',
];
EndpointDispatch(__FILE__, MY_REGISTRATION_METHODS, ['SaveMyRegistration']);

/* ************************************************************************ */
/* Function:    MyRegistrationsReady                                        */
/* Description: Whether nsx_meetup_participants has the Participant_User_ID */
/*              column yet (the database change that comes with this page). */
/* Returns:     bool                                                        */
/* ************************************************************************ */
function MyRegistrationsReady(PDO $pdo) {
    try {
        return (bool)$pdo->query("SHOW COLUMNS FROM `nsx_meetup_participants` LIKE 'Participant_User_ID'")->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/* ************************************************************************ */
/* Function:    MyRegistrationRows                                          */
/* Description: The registrations the user's account manages, each with    */
/*              its event's ID in My_Event_ID (older registrations have no  */
/*              Event_ID - they're found on the event's participant list),  */
/*              oldest first. Optionally just one event's.                  */
/* Returns:     array of participant rows                                   */
/* ************************************************************************ */
function MyRegistrationRows(PDO $pdo, $user_id, $event_id = null) {
    $sql = 'SELECT p.*, e.EVENT_ID AS My_Event_ID
            FROM nsx_meetup_participants p
            JOIN nsx_meetup_events e ON (e.EVENT_ID = p.Event_ID
                 OR (COALESCE(p.Event_ID, 0) = 0 AND FIND_IN_SET(p.Participant_ID, REPLACE(e.EVENT_Participants, \' \', \'\'))))
            WHERE p.Participant_User_ID = ?'.($event_id !== null ? ' AND e.EVENT_ID = ?' : '').'
            ORDER BY p.Participant_ID';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($event_id !== null ? [(int)$user_id, (int)$event_id] : [(int)$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ************************************************************************ */
/* Function:    MyRegistrationEditable / MyRegistrationUpcoming             */
/* Description: A registration can be edited until the day before its       */
/*              event starts. An event is upcoming (listed first, rather    */
/*              than under Past Events) until its last day is over.         */
/*              Events without dates count as upcoming and editable.        */
/* Returns:     bool                                                        */
/* ************************************************************************ */
function MyRegistrationEditable(array $event) {
    $start = (string)($event['EVENT_Start_Date'] ?? '');
    return $start === '' || date('Y-m-d') < substr($start, 0, 10);
}

function MyRegistrationUpcoming(array $event) {
    $end = (string)($event['EVENT_End_Date'] ?? '') ?: (string)($event['EVENT_Start_Date'] ?? '');
    return $end === '' || date('Y-m-d') <= substr($end, 0, 10);
}

/* ************************************************************************ */
/* Function:    MyRegistrationParties                                       */
/* Description: An event's registrations split into the groups they were    */
/*              registered in: driver one and the person registered with    */
/*              them, if any, the way the confirmation shows them. Driver   */
/*              two is saved straight after driver one, and driver one's    */
/*              Traveling_With then reads "<name> as a passenger" (whatever */
/*              driver two's role - see EnterUserToParticipantDatabase in   */
/*              register.js); a rider's reads "Riding with <name>".         */
/* Returns:     array of [driver one row, driver two row or null]           */
/* ************************************************************************ */
function MyRegistrationParties(array $rows) {
    $parties = [];
    for ($i = 0; $i < count($rows); $i++) {
        $driver1 = $rows[$i];
        $driver2 = null;
        $next = $rows[$i + 1] ?? null;
        if ($next && (int)$next['Participant_ID'] === (int)$driver1['Participant_ID'] + 1
            && (preg_match('/ as a passenger$/i', trim((string)$driver1['Participant_Traveling_With']))
                || stripos((string)$next['Participant_Traveling_With'], 'Riding with') === 0)) {
            $driver2 = $next;
            $i++;
        }
        $parties[] = [$driver1, $driver2];
    }
    return $parties;
}

/* ************************************************************************ */
/* Function:    MyRegistrationLoad                                          */
/* Description: One of the user's registrations (the posted participant_id)*/
/*              and its event. Ends the request if it isn't theirs.         */
/* Returns:     [PDO, participant row, event row]                           */
/* ************************************************************************ */
function MyRegistrationLoad() {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $participant_id = filter_var($_POST['participant_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($participant_id === false || !MyRegistrationsReady($pdo)) {
        EventAdminRespond('Fail', ['That registration could not be found.']);
    }
    foreach (MyRegistrationRows($pdo, (int)$_POST['user_id']) as $row) {
        if ((int)$row['Participant_ID'] === $participant_id) {
            $event = GetEventInformationFromID((int)$row['My_Event_ID']);
            if ($event) {
                return [$pdo, $row, $event];
            }
        }
    }
    EventAdminRespond('Fail', ['That registration could not be found.']);
}

function MyRegistrationMoney($cents) {
    return '$'.number_format((int)$cents / 100, 2);
}

function MyRegistrationDates(array $event) {
    $start = (string)($event['EVENT_Start_Date'] ?? '');
    $end = (string)($event['EVENT_End_Date'] ?? '');
    if ($start === '') {
        return '';
    }
    $from = date('F j, Y', strtotime($start));
    if ($end === '' || substr($end, 0, 10) === substr($start, 0, 10)) {
        return $from;
    }
    return date('F j', strtotime($start)).' - '.date('F j, Y', strtotime($end));
}

/* ************************************************************************ */
/* Function:    RenderMyRegistrations                                       */
/* Description: The Events You've Registered For tab: upcoming events, then */
/*              past ones. Each lists the people registered, their total    */
/*              and whether it's been paid, View Confirmation, and an Edit  */
/*              button for each person while the registration can still be */
/*              changed. account.js wires up the buttons.                   */
/* Returns:     JSON ['Pass', HTML]                                         */
/* ************************************************************************ */
function RenderMyRegistrations() {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $output_html = '<h2>Events You\'ve Registered For</h2>';
    if (!MyRegistrationsReady($pdo)) {
        EventAdminRespond('Pass', $output_html.'<p>This page isn\'t available yet. Please check back soon.</p>');
    }

    $by_event = [];
    foreach (MyRegistrationRows($pdo, (int)$_POST['user_id']) as $row) {
        $by_event[(int)$row['My_Event_ID']][] = $row;
    }
    $upcoming = $past = [];
    foreach ($by_event as $event_id => $rows) {
        $event = GetEventInformationFromID($event_id);
        if (!$event) {
            continue;
        }
        if (MyRegistrationUpcoming($event)) {
            $upcoming[] = [$event, $rows];
        } else {
            $past[] = [$event, $rows];
        }
    }
    $by_start = function ($a, $b) { return strcmp((string)$a[0]['EVENT_Start_Date'], (string)$b[0]['EVENT_Start_Date']); };
    usort($upcoming, $by_start);
    usort($past, function ($a, $b) use ($by_start) { return -$by_start($a, $b); });  // most recent first

    if (!$upcoming && !$past) {
        $output_html .= '<p>You haven\'t registered for any events with this account yet. When you register while logged in, the event shows up here.</p>';
        EventAdminRespond('Pass', $output_html);
    }

    foreach (['Upcoming Events' => $upcoming, 'Past Events' => $past] as $heading => $events) {
        if (!$events) {
            continue;
        }
        $output_html .= '<h3 style="margin-top:1.5em;">'.$heading.'</h3>';
        foreach ($events as list($event, $rows)) {
            $editable = MyRegistrationEditable($event);
            $collect = !empty($event['EVENT_Collect_Payments']);
            $output_html .= '<div class="payment_method my_registration">';
            $output_html .= '<div class="payment_method_header"><span class="payment_method_name">'.$h($event['EVENT_Name']).'</span>';
            $output_html .= '<a href="#" class="button small my_registration_view" event_id="'.(int)$event['EVENT_ID'].'">View Confirmation</a></div>';
            $output_html .= '<div class="payment_method_drawer">';
            if (MyRegistrationDates($event) !== '') {
                $output_html .= '<p class="registration_text" style="margin-bottom:0.75em;">'.$h(MyRegistrationDates($event)).'</p>';
            }
            foreach ($rows as $row) {
                $status = [];
                if (!empty($row['Participant_Wait_Listed'])) {
                    $status[] = 'Wait list';
                }
                if ($collect) {
                    $status[] = MyRegistrationMoney($row['Participant_Balance_Due']).(!empty($row['Participant_Balance_Paid']) ? ' - paid' : ' - not paid yet');
                }
                $output_html .= '<div class="row gtr-25" style="align-items:center;margin-bottom:0.5em;">';
                $output_html .= '<div class="col-8 col-12-xsmall"><b>'.$h($row['Participant_Name']).'</b> <span class="registration_text">('.$h($row['Participant_Role'])
                               .($status ? ' &middot; '.implode(' &middot; ', array_map($h, $status)) : '').')</span></div>';
                $output_html .= '<div class="col-4 col-12-xsmall" style="text-align:right;">';
                if ($editable) {
                    $output_html .= '<a href="#" class="button small primary my_registration_edit" participant_id="'.(int)$row['Participant_ID'].'">Edit</a>';
                }
                $output_html .= '</div></div>';
            }
            if (!$editable && MyRegistrationUpcoming($event)) {
                $output_html .= '<p class="registration_text">Changes can no longer be made online - please contact the event organizers.</p>';
            }
            $output_html .= '</div></div>';
        }
    }
    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    RenderMyRegistrationConfirmation                            */
/* Description: A copy of the confirmation for each of the user's           */
/*              registrations for the posted event, as shown when they      */
/*              registered (BuildConfirmationPage) - not emailed again.     */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderMyRegistrationConfirmation() {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $event_id = (int)($_POST['event_id'] ?? 0);
    $rows = MyRegistrationsReady($pdo) ? MyRegistrationRows($pdo, (int)$_POST['user_id'], $event_id) : [];
    $event = $rows ? GetEventInformationFromID($event_id) : [];
    if (!$rows || !$event) {
        EventAdminRespond('Fail', ['That registration could not be found.']);
    }
    $output_html = '<a href="#" class="button my_registrations_back" style="margin-bottom:1em;">Back to Your Events</a>';
    $output_html .= '<h2>'.htmlspecialchars($event['EVENT_Name'], ENT_QUOTES).'</h2>';
    foreach (MyRegistrationParties($rows) as list($driver1, $driver2)) {
        list($header, $html) = BuildConfirmationPage($driver1['Participant_ID'], $driver2 ? $driver2['Participant_ID'] : '', $event_id,
                                                     !empty($event['EVENT_Collect_Payments']) ? 'yes' : 'no', 'yes',
                                                     !empty($driver1['Participant_Wait_Listed']) ? 'yes' : 'no', false);
        $output_html .= $header.$html;
    }
    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    RenderMyRegistrationEdit                                    */
/* Description: The edit form for one person on one of the user's           */
/*              registrations: contact details, car (with a list of the     */
/*              user's saved cars to fill it in from), registration type    */
/*              and the event's questions. Saved by SaveMyRegistration.     */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderMyRegistrationEdit() {
    list($pdo, $row, $event) = MyRegistrationLoad();
    if (!MyRegistrationEditable($event)) {
        EventAdminRespond('Fail', ['This registration can no longer be changed online - please contact the event organizers.']);
    }
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $label_style = 'text-transform:uppercase;font-size: 1.1em;padding-right: 0.25em;color: #FFCC00;';

    $output_html  = '<a href="#" class="button my_registrations_back" style="margin-bottom:1em;">Back to Your Events</a>';
    $output_html .= '<h2>'.$h($event['EVENT_Name']).'</h2>';
    $output_html .= '<h3>Edit Registration: '.$h($row['Participant_Name']).' <span class="registration_text">('.$h($row['Participant_Role']).')</span></h3>';
    $output_html .= '<form id="my_registration_form" class="event_settings_form" participant_id="'.(int)$row['Participant_ID'].'" onsubmit="return false;">';

    /* Contact details */
    $output_html .= '<div class="row gtr-25 gtr-uniform">';
    foreach (['name' => ['Name', 'Participant_Name', 'col-4'], 'email' => ['Email Address', 'Participant_Email', 'col-4'], 'cell' => ['Cell Phone Number', 'Participant_Cell', 'col-4'],
              'street' => ['Street Address', 'Participant_Address', 'col-5'], 'city' => ['City', 'Participant_City', 'col-3']] as $key => list($label, $column, $width)) {
        $output_html .= '<div class="'.$width.' col-12-xsmall"><label for="my_reg_'.$key.'" style="margin:0 0 0.25em 0;"><span style="'.$label_style.'">'.$label.'</span></label>';
        $output_html .= '<input type="text" id="my_reg_'.$key.'" class="my_reg_field" key="'.$key.'" value="'.$h($row[$column]).'" /></div>';
    }
    $state = (string)$row['Participant_State'];
    $output_html .= '<div class="col-2 col-12-xsmall"><label for="my_reg_state" style="margin:0 0 0.25em 0;"><span style="'.$label_style.'">State</span></label>';
    $output_html .= '<select id="my_reg_state" class="my_reg_field" key="state"><option value=""'.($state === '' ? ' selected' : '').'>None</option>';
    foreach (['State' => US_STATES, 'Province' => CA_PROVINCES] as $group => $codes) {
        $output_html .= '<optgroup label="'.$group.'">';
        foreach ($codes as $code) {
            $output_html .= '<option value="'.$code.'"'.($state === $code ? ' selected' : '').'>'.$code.'</option>';
        }
        $output_html .= '</optgroup>';
    }
    $output_html .= '</select></div>';
    $output_html .= '<div class="col-2 col-12-xsmall"><label for="my_reg_zip" style="margin:0 0 0.25em 0;"><span style="'.$label_style.'">Zip</span></label>';
    $output_html .= '<input type="text" id="my_reg_zip" class="my_reg_field" key="zip" value="'.$h($row['Participant_Zip']).'" /></div>';
    $output_html .= '</div>';

    /* Car - not for riders and children (as at registration) */
    if (!in_array($row['Participant_Role'], ['riding', 'child'], true)) {
        $car = json_decode((string)$row['Participant_Car_Info'], true);
        $car = is_array($car) ? $car : [];
        $output_html .= '<h3 style="margin-top:1.5em;">Car</h3>';
        $stmt = $pdo->prepare('SELECT NSX_Car_Information FROM nsx_meetup_user_accounts WHERE NSX_User_ID = ?');
        $stmt->execute([(int)$_POST['user_id']]);
        $saved_cars = UserAccountCars($stmt->fetchColumn());
        if ($saved_cars) {
            $output_html .= '<select id="my_reg_saved_car" style="margin-bottom:0.75em;"><option value="" selected>Fill in from one of your saved cars...</option>';
            foreach ($saved_cars as $n => $saved_car) {
                $name = isset($saved_car['car_make'])
                    ? trim(($saved_car['car_year'] ?? '').' '.($saved_car['car_make'] ?? '').' '.($saved_car['car_model'] ?? ''))
                    : trim(($saved_car['car_year'] ?? '').' '.ucfirst((string)($saved_car['car_color'] ?? '')).' NSX');
                $output_html .= '<option value="'.(int)$n.'" car="'.$h(json_encode($saved_car)).'">'.$h($name).'</option>';
            }
            $output_html .= '</select>';
        }
        $output_html .= '<div id="my_reg_car">'.RenderMyAccountCar($car, 'reg', false).'</div>';
    }

    $output_html .= RenderEditRegistrationType((int)$event['EVENT_ID'], $row, true);
    if (!empty($event['EVENT_Collect_Payments']) && !empty($row['Participant_Balance_Paid'])) {
        $output_html .= '<p class="registration_text">You\'ve paid for this registration. If your total changes, the event organizers will be in touch about the difference.</p>';
    }
    $answers = json_decode((string)$row['Participant_Events_Attending'], true) ?: [];
    $output_html .= RenderParticipantQuestionsAndAnswers((int)$event['EVENT_ID'], $answers);

    $output_html .= '<div id="my_registration_messages" class="event_settings_messages" style="margin-top:1.5em;"></div>';
    $output_html .= '<a href="#" id="save_my_registration_button" class="button primary fit">Save Changes</a>';
    $output_html .= '<p class="registration_text" style="margin-top:0.5em;">The event organizers are emailed what you change, and you\'re emailed an updated confirmation.</p>';
    $output_html .= '</form>';
    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    SaveMyRegistration                                          */
/* Description: Saves the user's changes to one person on their             */
/*              registration - checked and priced as the admins' Edit       */
/*              Information is (RegistrantApplyEdit) - then emails the      */
/*              event's contacts what changed and the effect on the cost,   */
/*              and the registrant an updated confirmation, which the page  */
/*              then shows.                                                 */
/* Returns:     JSON ['Pass', {message, html}] or ['Fail', [messages]]      */
/* ************************************************************************ */
function SaveMyRegistration() {
    list($pdo, $current, $event) = MyRegistrationLoad();
    if (!MyRegistrationEditable($event)) {
        EventAdminRespond('Fail', ['This registration can no longer be changed online - please contact the event organizers.']);
    }

    $edit = RegistrantApplyEdit($pdo, $event, $current, $_POST, false);
    if ($edit['errors']) {
        EventAdminRespond('Fail', $edit['errors']);
    }
    if (!RegistrantEditChanges($pdo, $current, $edit['values'], $edit['questions'])) {
        EventAdminRespond('Pass', ['message' => 'Nothing was changed.', 'html' => '']);
    }
    $changes = RegistrantStoreEdit($pdo, (int)$current['Participant_ID'], $current, $edit, 'Edited by registrant');
    if ($changes === false) {
        EventAdminRespond('Fail', ['Your changes could not be saved. Please try again.']);
    }

    MyRegistrationNotifyOrganizers($event, $current, $edit, $changes);

    /* The updated confirmation for the group this person was registered in - shown, and emailed */
    $html = '';
    foreach (MyRegistrationParties(MyRegistrationRows($pdo, (int)$_POST['user_id'], (int)$event['EVENT_ID'])) as list($driver1, $driver2)) {
        if ((int)$driver1['Participant_ID'] === (int)$current['Participant_ID'] || ($driver2 && (int)$driver2['Participant_ID'] === (int)$current['Participant_ID'])) {
            list($header, $html) = BuildConfirmationPage($driver1['Participant_ID'], $driver2 ? $driver2['Participant_ID'] : '', (int)$event['EVENT_ID'],
                                                         !empty($event['EVENT_Collect_Payments']) ? 'yes' : 'no', 'yes',
                                                         !empty($driver1['Participant_Wait_Listed']) ? 'yes' : 'no', true, true);
            $html = $header.$html;
        }
    }

    $message = 'Your changes have been saved, and the event organizers have been told.';
    if ($edit['new_balance'] !== $edit['old_balance']) {
        $message .= ' '.$edit['values']['Participant_Name'].'\'s total changed from '.MyRegistrationMoney($edit['old_balance']).' to '.MyRegistrationMoney($edit['new_balance']).'.';
        if (!empty($current['Participant_Balance_Paid'])) {
            $message .= ' You\'ve already paid, so the organizers will be in touch about the difference.';
        }
    }
    EventAdminRespond('Pass', ['message' => $message, 'html' => $html]);
}

/* ************************************************************************ */
/* Function:    MyRegistrationNotifyOrganizers                              */
/* Description: Emails the event's primary and secondary contacts that a    */
/*              registrant changed their registration: what changed, and    */
/*              any change to what they owe - including, when they've       */
/*              already paid, how much to collect or refund.                */
/* ************************************************************************ */
function MyRegistrationNotifyOrganizers(array $event, array $current, array $edit, array $changes) {
    $recipients = [];
    foreach (['EVENT_Primary_Email_Contact', 'EVENT_Secondary_Email_Contact'] as $column) {
        $list = json_decode((string)($event[$column] ?? ''), true);
        $list = is_array($list) ? $list : preg_split('/[\s,;]+/', (string)($event[$column] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($list as $email) {
            if (is_string($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $recipients[strtolower(trim($email))] = trim($email);
            }
        }
    }
    if (!$recipients) {
        error_log('MyRegistrationNotifyOrganizers: event '.$event['EVENT_ID'].' has no contact email - participant '.$current['Participant_ID'].' changed their registration');
        return;
    }

    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $cell = 'style="padding:4px 8px;border-bottom:1px solid #ddd;vertical-align:top;"';
    $name = $edit['values']['Participant_Name'];
    $title = $event['EVENT_Name'].' - registration changed by '.$name;

    $body  = '<html><body style="font-family:Arial,Helvetica,sans-serif;color:#222;max-width:700px;">';
    $body .= '<h2>'.$h($title).'</h2>';
    $body .= '<p>'.$h($current['Participant_Name']).' ('.$h($current['Participant_Role']).') changed their registration from their NSX Meetup account'
            .(!empty($_SESSION['login_user']) ? ' ('.$h($_SESSION['login_user']).')' : '').' on '.$h(date('F j, Y \a\t g:i a')).'.'
            .(!empty($current['Participant_Wait_Listed']) ? ' They are on the wait list.' : '').'</p>';

    $body .= '<h3>What changed</h3><table style="border-collapse:collapse;">';
    $body .= '<tr><th '.$cell.' align="left">Field</th><th '.$cell.' align="left">Was</th><th '.$cell.' align="left">Now</th></tr>';
    foreach ($changes as $change) {
        $body .= '<tr><td '.$cell.'><b>'.$h($change['field']).'</b></td><td '.$cell.'>'.$h($change['from'] !== '' ? $change['from'] : '(blank)').'</td><td '.$cell.'>'.$h($change['to'] !== '' ? $change['to'] : '(blank)').'</td></tr>';
    }
    $body .= '</table>';

    if (!empty($event['EVENT_Collect_Payments'])) {
        $old = $edit['old_balance'];
        $new = $edit['new_balance'];
        $body .= '<h3>Cost</h3>';
        if ($new === $old) {
            $body .= '<p>No change - '.$h($name).'\'s registration is still '.MyRegistrationMoney($new).'.</p>';
        } else {
            $difference = $new - $old;
            $body .= '<p>'.$h($name).'\'s registration changed from <b>'.MyRegistrationMoney($old).'</b> to <b>'.MyRegistrationMoney($new).'</b> ('
                    .($difference > 0 ? 'up ' : 'down ').MyRegistrationMoney(abs($difference)).', before any processing fee).</p>';
            if (!empty($current['Participant_Balance_Paid'])) {
                $body .= '<p><b>They have already paid.</b> '.($difference > 0
                    ? 'Please collect the extra '.MyRegistrationMoney($difference).', and untick Received on the registrant list until it arrives.'
                    : 'Please refund '.MyRegistrationMoney(-$difference).'.').'</p>';
            } else {
                $body .= '<p>They haven\'t paid yet - the amount they owe is now '.MyRegistrationMoney($new).'.</p>';
            }
        }
    }
    $body .= '<p style="font-size:12px;color:#777;margin-top:24px;">This is an automated message from NSXMeetup.com. The change is also in the registrant\'s history on the Find / Edit Registrations tool.</p>';
    $body .= '</body></html>';

    foreach ($recipients as $email) {
        SendNoReplyEmail($email, $title, $body);
    }
}
