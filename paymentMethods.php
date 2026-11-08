<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  PAYMENT METHODS                                            */
/*                                                             */
/*  The ways an event can let registrants pay - admins turn    */
/*  each one on in Enable / Edit Event Payment Options         */
/*  (EVENTSettings.php), and the checkout page                 */
/*  (event_checkout.php) shows a section for each one that's   */
/*  on - and the pre-pay options registrants can add to their  */
/*  registration fee.                                          */
/*                                                             */
/*-------------------------------------------------------------*/

/* id => name, and the nsx_meetup_events column that turns it on */
const PAYMENT_METHODS = [
    'stripe' => ['name' => 'Stripe', 'column' => 'EVENT_Enable_Stripe_Processing'],
    'paypal' => ['name' => 'PayPal', 'column' => 'EVENT_Enable_PayPal'],
    'venmo'  => ['name' => 'Venmo',  'column' => 'EVENT_Enable_Venmo'],
    'zelle'  => ['name' => 'Zelle',  'column' => 'EVENT_Enable_Zelle'],
];

/* Payment methods where registrants send money to the event's own account (a username, QR code
   and instructions set in the method's drawer). The admins mark registrants paid when it arrives.
   handle_pattern applies after HandleFromInput() strips any @ / link in front of the username;
   handle_how_to is shown in the drawer under "How do I find my ... username?". */
const HANDLE_PAYMENT_METHODS = [
    'venmo' => [
        'handle_column'       => 'EVENT_Venmo_Handle',
        'description_column'  => 'EVENT_Venmo_Description',
        'qr_column'           => 'EVENT_Venmo_QR_Code',
        'handle_label'        => 'Venmo Username',
        'handle_prefix'       => '@',
        'handle_pattern'      => '/^[A-Za-z0-9_-]{5,30}$/',
        'handle_max'          => 30,
        'handle_rule'         => '5-30 letters, numbers, dashes or underscores',
        'handle_example'      => '@NSX-Club',
        'handle_how_to'       => "In the Venmo app, tap the Me tab (your picture) - your username is shown under your name, starting with @. On venmo.com, it's on your profile page.",
        'description_html'    => false,  // plain text
        'default_description' => "Send the total due to the Venmo account above. In the payment note, include the event name and the name of each person you're paying for, so the organizers can match your payment to your registration.\n\nYour registration is saved now and will be marked paid once the organizers receive your payment.",
        'app_name'            => 'the Venmo app',
    ],
    'paypal' => [
        'handle_column'       => 'EVENT_PayPal_Handle',
        'description_column'  => 'EVENT_PayPal_Description',  // was EVENT_Payment_Instructions
        'qr_column'           => 'EVENT_PayPal_QR_Code',
        'handle_label'        => 'PayPal Username',
        'handle_prefix'       => '@',         // your PayPal @username and PayPal.me link (paypal.me/<username>) are the same name
        'handle_pattern'      => '/^[A-Za-z0-9]{1,20}$/',
        'handle_max'          => 20,
        'handle_rule'         => 'up to 20 letters or numbers',
        'handle_example'      => '@NSXClub or paypal.me/NSXClub',
        'handle_how_to'       => "Your PayPal @username and your PayPal.me link are the same name - enter either one. In the PayPal app, tap your profile picture: your @username is shown under your name. On paypal.com, open Settings - your PayPal.me link (paypal.me/yourname) is on your profile. Don't have one yet? Create it free at paypal.me. Use the account that should receive registration payments.",
        'description_html'    => true,   // HTML, as EVENT_Payment_Instructions was
        'default_description' => "Send the total due to the PayPal account above. In the note, include the event name and the name of each person you're paying for, so the organizers can match your payment to your registration.\n\nYour registration is saved now and will be marked paid once the organizers receive your payment.",
        'app_name'            => 'the PayPal app',
    ],
];

/* ************************************************************************ */
/* Function:    HandleFromInput                                             */
/* Description: The bare username from what an admin typed: drops a        */
/*              leading @ or a pasted link (venmo.com/..., paypal.me/...,   */
/*              paypal.com/paypalme/...).                                   */
/* ************************************************************************ */
function HandleFromInput($value) {
    $value = preg_replace('#^(https?://)?(www\.)?(venmo\.com/(u/)?|paypal\.me/|paypal\.com/paypalme/)?@?#i', '', trim((string)$value));
    return rtrim($value, '/');
}

/* ************************************************************************ */
/* Function:    HandlePaymentLink                                           */
/* Description: A link that opens the payment to the event's account with   */
/*              the amount (and, for Venmo, the note) filled in - in the    */
/*              app on a phone.                                             */
/* Inputs:      $method - 'venmo' or 'paypal', $handle - the username,      */
/*              $cents - amount due, $note - the suggested payment note.    */
/* ************************************************************************ */
function HandlePaymentLink($method, $handle, $cents, $note) {
    $amount = number_format($cents / 100, 2, '.', '');
    if ($method === 'paypal') {
        return 'https://www.paypal.com/paypalme/'.rawurlencode($handle).'/'.$amount;
    }
    return 'https://venmo.com/'.rawurlencode($handle).'?'.http_build_query(['txn' => 'pay', 'amount' => $amount, 'note' => $note], '', '&', PHP_QUERY_RFC3986);
}

/* ************************************************************************ */
/* Function:    EnabledPaymentMethods                                       */
/* Description: The payment methods turned on for an event.                 */
/* Inputs:      $event - the event row from nsx_meetup_events.              */
/* Returns:     array - id => name, in PAYMENT_METHODS order                */
/* ************************************************************************ */
function EnabledPaymentMethods(array $event) {
    $enabled = [];
    foreach (PAYMENT_METHODS as $method => $info) {
        if (!empty($event[$info['column']])) {
            $enabled[$method] = $info['name'];
        }
    }
    return $enabled;
}

/* ************************************************************************ */
/* Function:    PrepayOptions                                               */
/* Description: The event's pre-pay options: the active yes/no questions    */
/*              (nsx_meetup_event_questions) that add their amount to the   */
/*              registrant's total when checked.                            */
/* Inputs:      $pdo - PHP Data Object, $event_id - the event's id.         */
/* Returns:     array - QUESTION_Label => ['description', 'cents']          */
/* ************************************************************************ */
function PrepayOptions(PDO $pdo, $event_id) {
    $stmt = $pdo->prepare("SELECT QUESTION_Label, QUESTION_Description, QUESTION_Amount FROM nsx_meetup_event_questions
                           WHERE EVENT_ID = ? AND QUESTION_Active = 1 AND QUESTION_Add_To_Basket = 1 AND QUESTION_Type = 'BOOLEAN'
                           ORDER BY QUESTION_Order_Number");
    $stmt->execute([(int)$event_id]);
    $options = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $options[$row['QUESTION_Label']] = [
            'description' => ($row['QUESTION_Description'] ?? '') !== '' ? $row['QUESTION_Description'] : $row['QUESTION_Label'],
            'cents'       => (int)round((float)$row['QUESTION_Amount'] * 100),
        ];
    }
    return $options;
}

/* ************************************************************************ */
/* Function:    ChosenPrepayOptions                                         */
/* Description: The pre-pay options a registrant checked.                   */
/* Inputs:      $options - from PrepayOptions(), $choices - the registrant's */
/*              answers: label => 'yes' / 'no' (as posted by the            */
/*              registration form, or decoded from                         */
/*              Participant_Events_Attending).                              */
/* Returns:     array - label => ['description', 'cents']                   */
/* ************************************************************************ */
function ChosenPrepayOptions(array $options, $choices) {
    $chosen = [];
    foreach ($options as $label => $option) {
        if (is_array($choices) && ($choices[$label] ?? '') === 'yes') {
            $chosen[$label] = $option;
        }
    }
    return $chosen;
}

/* ************************************************************************ */
/* Function:    PaymentQRCodePath                                           */
/* Description: The event's QR code image for a HANDLE_PAYMENT_METHODS      */
/*              method (a site-relative path such as                        */
/*              events/TestEvent/images/venmo_qr_20261108_120000.png), if   */
/*              it's an image in an event's images folder and the file is   */
/*              there.                                                      */
/* Returns:     string - the path, or ''                                    */
/* ************************************************************************ */
function PaymentQRCodePath(array $event, $method) {
    $path = (string)($event[HANDLE_PAYMENT_METHODS[$method]['qr_column']] ?? '');
    if (!preg_match('#^events/[A-Za-z0-9_-]+/images/[A-Za-z0-9_.-]+\.(png|jpe?g|webp)$#i', $path) || !is_file(__DIR__.'/'.$path)) {
        return '';
    }
    return $path;
}
