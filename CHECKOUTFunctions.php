<?php

    // The Stripe library (Composer's vendor/ folder) isn't in the repo - it's installed on the server
    const STRIPE_AUTOLOAD = __DIR__.'/vendor/autoload.php';
    const STRIPE_MINIMUM_CHARGE = 50;  // cents - Stripe won't charge less than $0.50

    if (isset($_POST['method']) && in_array($_POST['method'], ['DisplayShoppingCartAtCheckout', 'CreateStripeIntent'], true)) {
        $_POST['method']();
    }


    function DisplayShoppingCartAtCheckout() {
        $html_output = '<table>';
        $shopping_cart_string = $_POST['items'];
        $items = json_decode($shopping_cart_string, true);
        foreach ($items["basket"] as $item) {
            $html_output .= '<tr><td>'.$item['description'].'</td><td>'.$item['amount'].'</td></tr>';
        }
        $html_output .= '</table>';

        echo $html_output;
    }




    // Calculate the order total on the server to prevent
    // people from directly manipulating the amount on the client
    function calculateOrderAmount(array $items): int {
        $total = 0;
        foreach($items as $item) {
          $total += (int)($item->amount ?? 0);
        }
        return $total;
    }

    function CreateStripeIntent() {
        $results = array();
        try {
            if (!is_file(STRIPE_AUTOLOAD)) {
                throw new RuntimeException('The Stripe library is not installed on the server (vendor/autoload.php is missing).');
            }
            require_once STRIPE_AUTOLOAD;

            $stripeSecretKey = getenv('STRPSECKEY');
            if (!$stripeSecretKey) {
                throw new RuntimeException('The Stripe secret key is not set on the server (STRPSECKEY environment variable).');
            }
            $stripe = new \Stripe\StripeClient($stripeSecretKey);

            $items = json_decode($_POST['items'] ?? '');
            if (!isset($items->basket) || !is_array($items->basket)) {
                throw new RuntimeException('The shopping cart is empty or unreadable.');
            }
            $total = calculateOrderAmount($items->basket);
            if ($total < STRIPE_MINIMUM_CHARGE) {
                throw new RuntimeException('The total is $'.number_format($total / 100, 2).' - Stripe can only charge $0.50 or more.');
            }
            // Create a PaymentIntent with amount and currency
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $total,
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            if (($paymentIntent->client_secret != null) && ($paymentIntent->client_secret != '')) {
                $results[] = 'OK';
                $results[] = $paymentIntent->client_secret;
            } else {
                $results[] = 'FAIL';
                $results[] = 'Client Secret was either null or an empty string';
            }

        
        } catch (Throwable $e) {  // Stripe's own errors are Exceptions, not Errors
            error_log('CreateStripeIntent failed: '.get_class($e).': '.$e->getMessage());
            $results[] = 'FAIL';
            $results[] = $e->getMessage();
        }

        echo json_encode($results);        
    }

?>