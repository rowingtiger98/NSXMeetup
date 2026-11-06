<?php

require_once 'vendor/autoload.php';
$test_variable = getenv('TEST_VARIABLE');
$stripeSecretKey = getenv('STRPSECKEY');
\Stripe\Stripe::setApiKey($stripeSecretKey);

// if(isset($_GET['event_id'])) {
//     include_once 'GENERALFunctions.php';
//     $event_data = GetEventInformationFromID($_GET['event_id']);
//     $home_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/events&bsol;" . $event_data['EVENT_Directory'];
//     $reg_ready = True;
// 	// echo "Ron is working on a bug, if you see this  (04/14/25)";   
// } else {
//     $reg_ready = False;
//     $event_data = [
//         "EVENT_ID" => 0,
//         "EVENT_Name" => 'Unknown Event'
//     ];
// }

?>

<html>
	<head>
		<title>NSX Meetup Checkout Page</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
        <link rel="stylesheet" href="assets/css/stripCheckout.css" />
        <script src="https://js.stripe.com/clover/stripe.js"></script>
        <script src="assets/js/stripeCheckout.js" defer></script>
	</head>
	<body>
        <div id="main" class="wrapper">
            <div class="inner">        
                <div class="row gtr-uniform" style="margin:0.5em 0 2em 0;">
                    <div class="col-6 col-12-small">
                        <h3 style="color:#ff0000;">DoubleTree by Hilton</h3>
                        <div id="shopping_cart" class="shopping cart"> Loading... </div>
                    </div>
                    <div class="col-6 col-12-small">
                        <form id="payment-form">
                            <div id="address-element"></div>
                            <div id="payment-element">
                            </div>
                            <button id="submit">
                                <div class="spinner hidden" id="spinner"></div>
                                <span id="button-text">Pay now</span>
                            </button>
                            <div id="payment-message" class="hidden"></div>
                        </form>
                    </div>
                </div>
            </div>   
        </div>
                                          
    <!-- Scripts -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/checkout.js"></script>

        
        <script>
            $( document ).ready(function() {

                var itemized_list = DisplayShoppingCartAtCheckout();
                console.log('Itemized List = ' + itemized_list);
            
            });
        </script>			

	</body>
</html>