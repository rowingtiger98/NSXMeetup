<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT CHECKOUT                                             */
/*                                                             */
/*  event_checkout.php?event_id=N                              */
/*  Registration saves the registrants first (with their       */
/*  registration type and balance due, priced on the server)   */
/*  and remembers them in the session; this page lists what    */
/*  they owe and a section for each payment method the event   */
/*  has turned on (paymentMethods.php). The payment sections   */
/*  are placeholders until each method is built out.           */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/SessionBootstrap.php';
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/paymentMethods.php';
require_once __DIR__ . '/registrantHistory.php';
app_session_start();

$h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
$is_logged_in = isset($_SESSION['login_user']);

$event = null;
$event_id = filter_var($_GET['event_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($event_id !== false) {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_events WHERE EVENT_ID = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    $stmt = $pdo = null;
}

/* Change Registration: take back the unpaid registrants saved from this session, then reopen the
   registration form - register.js refills it from what was entered (RestoreRegistrationForm) */
if ($event && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_registration') {
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        header('Location: event_checkout.php?event_id='.(int)$event['EVENT_ID']);
        exit();
    }
    RemoveUnpaidCheckoutParticipants($event);
    header('Location: register_all.php?event_id='.(int)$event['EVENT_ID'].'&restore=1');
    exit();
}

/* Complete Registration: the registrants agree to pay later with the chosen method (Venmo, PayPal).
   It's recorded on them, and the page then shows the registration confirmation (emailing the payment
   information that first time only). */
if ($event && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'complete_registration') {
    $method = $_POST['payment_method'] ?? '';
    if (app_csrf_valid($_POST['csrf_token'] ?? null) && is_string($method) && PledgeablePaymentMethod($event, $method)) {
        RecordCheckoutPaymentMethod($event, $method);
        $_SESSION['checkout_confirmation_email'][(int)$event['EVENT_ID']] = true;
    }
    header('Location: event_checkout.php?event_id='.(int)$event['EVENT_ID']);
    exit();
}

$event_name = $event ? $event['EVENT_Name'] : 'Event';
$directory = $event ? (string)$event['EVENT_Directory'] : '';
$home_link = preg_match('/^[A-Za-z0-9_-]+$/', $directory) ? 'events/'.$directory.'/' : 'index.php';
$payment_methods = $event ? EnabledPaymentMethods($event) : [];

/* The registrants entered from this browser session (RememberCheckoutParticipant in GENERALFunctions.php) */
$registrants = [];
$total_due = 0;  // cents - registrations still owed, plus the processing fee
$processing_fee = 0;  // cents
$checkout_ids = $event ? array_map('intval', $_SESSION['checkout_participants'][(int)$event['EVENT_ID']] ?? []) : [];
if ($checkout_ids) {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $placeholders = implode(',', array_fill(0, count($checkout_ids), '?'));
    $stmt = $pdo->prepare('SELECT p.Participant_ID, p.Participant_Name, p.Participant_Balance_Due, p.Participant_Balance_Paid, p.Participant_Payment_Method, p.Participant_Events_Attending, r.REGTYPE_Name, r.REGTYPE_Cost
                           FROM nsx_meetup_participants p
                           LEFT JOIN nsx_meetup_registration_types r ON r.REGTYPE_ID = p.Participant_Registration_Type
                           WHERE p.Participant_ID IN ('.$placeholders.') AND p.Event_ID = ?
                           ORDER BY p.Participant_ID');
    $stmt->execute(array_merge($checkout_ids, [(int)$event['EVENT_ID']]));
    $registrants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $prepay_options = PrepayOptions($pdo, $event['EVENT_ID']);
    $pdo = null;
    foreach ($registrants as &$registrant) {
        // What makes up each registrant's balance: their registration type and the pre-pay options they checked
        $registrant['items'] = [];
        if ($registrant['REGTYPE_Name'] !== null) {
            $registrant['items'][] = ['description' => $registrant['REGTYPE_Name'], 'cents' => (int)round($registrant['REGTYPE_Cost'] * 100)];
        }
        $choices = json_decode((string)$registrant['Participant_Events_Attending'], true);
        foreach (ChosenPrepayOptions($prepay_options, $choices) as $option) {
            $registrant['items'][] = $option;
        }
    }
    unset($registrant);
    foreach ($registrants as $registrant) {
        if (empty($registrant['Participant_Balance_Paid'])) {
            $total_due += (int)$registrant['Participant_Balance_Due'];
        }
    }
    // The processing fee applies to every payment method
    $processing_fee = ProcessingFeeCents($total_due, $event['EVENT_Processing_Percentage']);
    $total_due += $processing_fee;
}
$format_cents = function ($cents) { return '$'.number_format($cents / 100, 2); };

/* After Complete Registration: every unpaid registrant has agreed to the same method */
$pledged_method = null;
$pledges = [];
foreach ($registrants as $registrant) {
    if (empty($registrant['Participant_Balance_Paid'])) {
        $pledges[] = (string)$registrant['Participant_Payment_Method'];
    }
}
$pledges = array_unique($pledges);
if (count($pledges) === 1 && PledgeablePaymentMethod($event, $pledges[0])) {
    $pledged_method = $pledges[0];
}

/* The registration confirmation, as the registration page shows it (BuildConfirmationPage in
   GENERALFunctions.php) - only loaded on this GET, so its POST method dispatch has nothing to run */
$confirmation = null;
if ($pledged_method && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    require_once __DIR__.'/GENERALFunctions.php';
    $event_id_key = (int)$event['EVENT_ID'];
    $send_email = !empty($_SESSION['checkout_confirmation_email'][$event_id_key]);
    unset($_SESSION['checkout_confirmation_email'][$event_id_key]);
    $confirmation = BuildConfirmationPage($checkout_ids[0], $checkout_ids[1] ?? '', $event_id_key, 'yes', 'no', 'no', $send_email);
}

/* ************************************************************************ */
/* Function:    PledgeablePaymentMethod                                     */
/* Description: True if $method is one registrants pay later (Venmo,        */
/*              PayPal), the event has it turned on, and its account is     */
/*              set.                                                        */
/* ************************************************************************ */
function PledgeablePaymentMethod($event, $method) {
    return $event && isset(HANDLE_PAYMENT_METHODS[$method]) && isset(EnabledPaymentMethods($event)[$method])
        && (string)$event[HANDLE_PAYMENT_METHODS[$method]['handle_column']] !== '';
}

/* ************************************************************************ */
/* Function:    RecordCheckoutPaymentMethod                                 */
/* Description: Records $method as the payment method of the unpaid         */
/*              registrants saved from this browser session for the event,  */
/*              noting it in each one's history.                            */
/* ************************************************************************ */
function RecordCheckoutPaymentMethod(array $event, $method) {
    $ids = array_map('intval', $_SESSION['checkout_participants'][(int)$event['EVENT_ID']] ?? []);
    if (!$ids) {
        return;
    }
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $where = 'Participant_ID IN ('.$placeholders.') AND Event_ID = ? AND COALESCE(Participant_Balance_Paid, 0) = 0';
    $params = array_merge($ids, [(int)$event['EVENT_ID']]);
    try {
        $stmt = $pdo->prepare('SELECT Participant_ID, Participant_Payment_Method FROM nsx_meetup_participants WHERE '.$where);
        $stmt->execute($params);
        $previous = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $pdo->prepare('UPDATE nsx_meetup_participants SET Participant_Payment_Method = ? WHERE '.$where)->execute(array_merge([$method], $params));
    } catch (PDOException $e) {
        error_log('RecordCheckoutPaymentMethod failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        return;
    }
    $name = function ($m) { return (string)$m === '' ? '' : (PAYMENT_METHODS[$m]['name'] ?? $m); };
    foreach ($previous as $participant_id => $old_method) {
        $change = RegistrantHistoryChange('Paying With', $name($old_method), $name($method));
        if ($change) {
            AppendRegistrantHistory($pdo, $participant_id, 'Completed registration at checkout', [$change], 'Registrant');
        }
    }
}

/* ************************************************************************ */
/* Function:    RenderHandlePayment                                         */
/* Description: The section for a HANDLE_PAYMENT_METHODS method (Venmo,     */
/*              PayPal): the amount due, the event's account, the QR code   */
/*              if one was uploaded, a suggested payment note (event and    */
/*              registrant names) and the admins' instructions, ending in   */
/*              an Open link to the account with the amount filled in (in   */
/*              the app on a phone). With $with_complete, the Complete      */
/*              Registration button records that they'll pay this way.      */
/*              Admins mark the registration paid when the payment arrives. */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderHandlePayment($method, array $event, $total_due, array $registrants, $with_complete = true) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $info = HANDLE_PAYMENT_METHODS[$method];
    $name = PAYMENT_METHODS[$method]['name'];
    $handle = (string)$event[$info['handle_column']];
    $account = $info['handle_prefix'].$handle;

    $names = [];
    foreach ($registrants as $registrant) {
        if (empty($registrant['Participant_Balance_Paid'])) {
            $names[] = $registrant['Participant_Name'];
        }
    }
    $note = $event['EVENT_Name'].' - '.implode(', ', $names);

    // The admins' instructions (HTML for PayPal, plain text for Venmo), or the default (plain text)
    $instructions = trim((string)$event[$info['description_column']]);
    if ($instructions === '') {
        $instructions_html = nl2br($h($info['default_description']));
    } else {
        $instructions_html = $info['description_html'] ? $instructions : nl2br($h($instructions));
    }

    $output_html  = '<p>Send <b>$'.$h(number_format($total_due / 100, 2)).'</b> to <b>'.$h($account).'</b> on '.$h($name).'.</p>';
    $qr_code = PaymentQRCodePath($event, $method);
    if ($qr_code !== '') {
        $output_html .= '<div class="checkout_handle_qr"><img src="'.$h($qr_code).'" alt="'.$h($name).' QR code for '.$h($account).'" /><p>Scan with your phone\'s camera or '.$h($info['app_name']).'</p></div>';
    }
    $output_html .= '<p class="checkout_handle_note_label">Payment note:</p>';
    $output_html .= '<p class="checkout_handle_note">'.$h($note).'</p>';
    $output_html .= '<div class="checkout_instructions">'.$instructions_html
                  .'<p class="checkout_handle_link"><a href="'.$h(HandlePaymentLink($method, $handle, $total_due, $note)).'" target="_blank" rel="noopener">Open '.$h($name).' to send your payment</a></p></div>';

    if ($with_complete) {
        $output_html .= '<form method="post" action="event_checkout.php?event_id='.(int)$event['EVENT_ID'].'" class="checkout_complete_form">';
        $output_html .= '<input type="hidden" name="action" value="complete_registration" />';
        $output_html .= '<input type="hidden" name="payment_method" value="'.$h($method).'" />';
        $output_html .= '<input type="hidden" name="csrf_token" value="'.$h(app_csrf_token()).'" />';
        $output_html .= '<p class="checkout_agreement">By clicking <b>Complete Registration</b>, you agree to send <b>$'.$h(number_format($total_due / 100, 2)).'</b> to <b>'.$h($account).'</b> on '.$h($name).'. '
                      .'Your registration is not complete until the event organizers receive and confirm your payment.</p>';
        $output_html .= '<button type="submit" class="button primary fit">Complete Registration</button>';
        $output_html .= '</form>';
    }
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RemoveUnpaidCheckoutParticipants                            */
/* Description: Deletes the registrants saved from this browser session for */
/*              the event that haven't paid, takes them off the event's     */
/*              participant list, and forgets them.                         */
/* ************************************************************************ */
function RemoveUnpaidCheckoutParticipants(array $event) {
    $event_id = (int)$event['EVENT_ID'];
    $ids = array_map('intval', $_SESSION['checkout_participants'][$event_id] ?? []);
    unset($_SESSION['checkout_participants'][$event_id]);
    if (!$ids) {
        return;
    }

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT Participant_ID FROM nsx_meetup_participants WHERE Participant_ID IN ('.$placeholders.') AND Event_ID = ? AND COALESCE(Participant_Balance_Paid, 0) = 0');
        $stmt->execute(array_merge($ids, [$event_id]));
        $remove = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        if ($remove) {
            $placeholders = implode(',', array_fill(0, count($remove), '?'));
            $pdo->prepare('DELETE FROM nsx_meetup_participants WHERE Participant_ID IN ('.$placeholders.')')->execute($remove);

            $stmt = $pdo->prepare('SELECT EVENT_Participants FROM nsx_meetup_events WHERE EVENT_ID = ?');
            $stmt->execute([$event_id]);
            $participants = array_filter(array_map('trim', explode(',', (string)$stmt->fetchColumn())), function ($id) use ($remove) {
                return $id !== '' && !in_array((int)$id, $remove, true);
            });
            $pdo->prepare('UPDATE nsx_meetup_events SET EVENT_Participants = ? WHERE EVENT_ID = ?')->execute([implode(',', $participants), $event_id]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('RemoveUnpaidCheckoutParticipants failed for event '.$event_id.': '.$e->getMessage());
    }
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $h($event_name); ?> Checkout</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css?v=<?php echo filemtime(__DIR__.'/assets/css/nsxmeetup.css'); ?>" />
	</head>
	<body class="is-preload">

		<!-- Header -->
			<header id="header">
                <nav>
                    <ul class="nav">
                        <li><a href="<?php echo $h($home_link); ?>">Home</a></li>
                    </ul>
                </nav>
                <div id="logo">
                    <a href="index.php">
                        <span class="image fit"><img src="assets/images/NSXMeetup-Logo-white.png" alt="" /></span>
                    </a>
                </div>
                <nav>
                    <ul class="nav"></ul>
                </nav>
			</header>

		<!-- Main -->
			<div id="main" class="wrapper">
				<div class="inner">
                    <h1 class="major"><?php echo $h($event_name); ?> Checkout</h1>

                    <?php if (!$event) { ?>
                        <p>That event could not be found. Please go back and try again.</p>

                    <?php } elseif (empty($event['EVENT_Collect_Payments'])) { ?>
                        <p>This event is free - there is nothing to pay.</p>

                    <?php } elseif (!$registrants) { ?>
                        <p>We couldn't find a registration to pay for. Please <a href="register_all.php?event_id=<?php echo (int)$event['EVENT_ID']; ?>">register for the event</a> first.
                        If you've already registered, please contact the event organizers about your payment.</p>

                    <?php } elseif ($total_due <= 0) { ?>
                        <p>Thank you - there is nothing left to pay for this registration.</p>

                    <?php } elseif ($confirmation) { ?>
                        <!-- After Complete Registration: the registration confirmation -->
                        <div id="event_registration_results_text"><?php echo $confirmation[0]; ?></div>
                        <p class="checkout_pending">Thank you! Your registration is saved, and you've agreed to pay with <?php echo $h(PAYMENT_METHODS[$pledged_method]['name']); ?>.
                        Your registration will be complete once the event organizers receive and confirm your payment.</p>
                        <div id="event_registration_container"><?php echo $confirmation[1]; ?></div>

                    <?php } else { ?>
                        <div class="row gtr-uniform checkout_layout">
                            <div class="col-5 col-12-medium">
                                <h3>Order Summary</h3>
                                <table class="checkout_order_table">
                                    <tbody>
                                    <?php foreach ($registrants as $registrant) { ?>
                                        <tr class="checkout_registrant">
                                            <td><?php echo $h($registrant['Participant_Name']); ?></td>
                                            <td class="checkout_amount"><?php echo empty($registrant['Participant_Balance_Paid']) ? $format_cents((int)$registrant['Participant_Balance_Due']) : 'Paid'; ?></td>
                                        </tr>
                                        <?php foreach ($registrant['items'] as $item) { ?>
                                            <tr class="checkout_item">
                                                <td><?php echo $h($item['description']); ?></td>
                                                <td class="checkout_amount"><?php echo $format_cents($item['cents']); ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <?php if ($processing_fee > 0) { ?>
                                            <tr class="checkout_fee"><td>Processing Fee (<?php echo $h(FormatPercent($event['EVENT_Processing_Percentage'])); ?>)</td><td class="checkout_amount"><?php echo $format_cents($processing_fee); ?></td></tr>
                                        <?php } ?>
                                        <tr><td>Total Due</td><td class="checkout_amount"><?php echo $format_cents($total_due); ?></td></tr>
                                    </tfoot>
                                </table>
                                <?php if (!$pledged_method) { ?>
                                <form method="post" action="event_checkout.php?event_id=<?php echo (int)$event['EVENT_ID']; ?>" class="checkout_change_form">
                                    <input type="hidden" name="action" value="change_registration" />
                                    <input type="hidden" name="csrf_token" value="<?php echo $h(app_csrf_token()); ?>" />
                                    <button type="submit" class="button small fit">Change Registration</button>
                                    <p class="checkout_change_note">Go back to the registration form with your choices filled in.</p>
                                </form>
                                <?php } ?>
                            </div>
                            <div class="col-7 col-12-medium">
                                <h3>Choose How to Pay</h3>
                                <?php if (!$payment_methods) { ?>
                                    <p>Payment options for this event haven't been set up yet. Please contact the event organizers.</p>
                                <?php } ?>
                                <?php
                                // One payment method at a time: checking one opens its drawer and unchecks / closes the
                                // others (SelectCheckoutPaymentMethod in eventcheckout.js). With only one, it starts open.
                                $only_one = (count($payment_methods) === 1);
                                ?>
                                <?php foreach ($payment_methods as $method => $name) { ?>
                                    <div id="checkout_payment_<?php echo $method; ?>" class="checkout_payment_method<?php echo $only_one ? ' selected' : ''; ?>" payment_method="<?php echo $method; ?>">
                                        <div class="checkout_payment_header">
                                            <input type="checkbox" id="checkout_payment_<?php echo $method; ?>_select" class="checkout_payment_select" drawer="checkout_payment_<?php echo $method; ?>_drawer" aria-controls="checkout_payment_<?php echo $method; ?>_drawer" aria-expanded="<?php echo $only_one ? 'true' : 'false'; ?>"<?php echo $only_one ? ' checked' : ''; ?>>
                                            <label for="checkout_payment_<?php echo $method; ?>_select">Pay with <?php echo $h($name); ?></label>
                                        </div>
                                        <div id="checkout_payment_<?php echo $method; ?>_drawer" class="checkout_payment_drawer"<?php echo $only_one ? '' : ' style="display:none;"'; ?>>
                                            <?php if (isset(HANDLE_PAYMENT_METHODS[$method]) && (string)$event[HANDLE_PAYMENT_METHODS[$method]['handle_column']] !== '') { ?>
                                                <?php echo RenderHandlePayment($method, $event, $total_due, $registrants); ?>
                                            <?php } else { ?>
                                                <p>The <?php echo $h($name); ?> payment options will go here.</p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
				</div>
			</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/eventcheckout.js?v=<?php echo filemtime(__DIR__.'/assets/js/eventcheckout.js'); ?>"></script>
			<?php if ($is_logged_in) echo app_idle_timer_script(); ?>
			<script>
				$(document).ready(function() {
					$(".checkout_payment_select").on('change', SelectCheckoutPaymentMethod);
				});
			</script>
	</body>
</html>
