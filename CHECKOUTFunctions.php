<?php

    require_once './vendor/autoload.php';
    // require_once '../secrets.php';
    // $stripeSecretKey = getenv('STRPSECKEY');
    // $stripe = new \Stripe\StripeClient($stripeSecretKey);

    if(isset($_POST['method'])) {
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
          $total += $item->amount;
        }
        return $total;
    }

    function CreateStripeIntent() {
        $stripeSecretKey = getenv('STRPSECKEY');
        $stripe = new \Stripe\StripeClient($stripeSecretKey);        
        $results = array();
        try {
            $items = json_decode($_POST['items']);
            $total = calculateOrderAmount($items->basket);
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

        
        } catch (Error $e) {
            $results[] = 'FAIL';
            $results[] = $e->getMessage();
        }

        echo json_encode($results);        
    }

?>