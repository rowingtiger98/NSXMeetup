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

$event_name = $event ? $event['EVENT_Name'] : 'Event';
$directory = $event ? (string)$event['EVENT_Directory'] : '';
$home_link = preg_match('/^[A-Za-z0-9_-]+$/', $directory) ? 'events/'.$directory.'/' : 'index.php';
$payment_methods = $event ? EnabledPaymentMethods($event) : [];

/* The registrants entered from this browser session (RememberCheckoutParticipant in GENERALFunctions.php) */
$registrants = [];
$total_due = 0;  // cents
$checkout_ids = $event ? array_map('intval', $_SESSION['checkout_participants'][(int)$event['EVENT_ID']] ?? []) : [];
if ($checkout_ids) {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $placeholders = implode(',', array_fill(0, count($checkout_ids), '?'));
    $stmt = $pdo->prepare('SELECT p.Participant_ID, p.Participant_Name, p.Participant_Balance_Due, p.Participant_Balance_Paid, p.Participant_Events_Attending, r.REGTYPE_Name, r.REGTYPE_Cost
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
}
$format_cents = function ($cents) { return '$'.number_format($cents / 100, 2); };

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
                                        <tr><td>Total Due</td><td class="checkout_amount"><?php echo $format_cents($total_due); ?></td></tr>
                                    </tfoot>
                                </table>
                                <form method="post" action="event_checkout.php?event_id=<?php echo (int)$event['EVENT_ID']; ?>" class="checkout_change_form">
                                    <input type="hidden" name="action" value="change_registration" />
                                    <input type="hidden" name="csrf_token" value="<?php echo $h(app_csrf_token()); ?>" />
                                    <button type="submit" class="button small fit">Change Registration</button>
                                    <p class="checkout_change_note">Go back to the registration form with your choices filled in.</p>
                                </form>
                                <?php if (trim((string)$event['EVENT_Payment_Instructions']) !== '') { ?>
                                    <div class="checkout_instructions"><?php echo $event['EVENT_Payment_Instructions']; /* HTML entered by the event's admins */ ?></div>
                                <?php } ?>
                            </div>
                            <div class="col-7 col-12-medium">
                                <h3>Choose How to Pay</h3>
                                <?php if (!$payment_methods) { ?>
                                    <p>Payment options for this event haven't been set up yet. Please contact the event organizers.</p>
                                <?php } ?>
                                <?php foreach ($payment_methods as $method => $name) { ?>
                                    <div id="checkout_payment_<?php echo $method; ?>" class="checkout_payment_method" payment_method="<?php echo $method; ?>">
                                        <h4><?php echo $h($name); ?></h4>
                                        <p>The <?php echo $h($name); ?> payment options will go here.</p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
				</div>
			</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<?php if ($is_logged_in) echo app_idle_timer_script(); ?>
	</body>
</html>
