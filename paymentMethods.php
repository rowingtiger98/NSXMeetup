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
