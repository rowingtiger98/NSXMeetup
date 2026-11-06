<?php
require_once 'SessionBootstrap.php';
include_once 'QUESTIONFunctions.php' ;
include_once 'CONTACTFunctions.php';
app_session_start();

if(isset($_GET['event_id'])) {
    include_once 'GENERALFunctions.php';
    $event_data = GetEventInformationFromID($_GET['event_id']);
    // Relative, so it works wherever the site is hosted (e.g. kirbypark.net/luna/)
    $directory = (string)($event_data['EVENT_Directory'] ?? '');
    $home_link = preg_match('/^[A-Za-z0-9_-]+$/', $directory) ? 'events/'.$directory.'/' : 'index.php';
    $reg_ready = True;
	// echo "Ron is working on a bug, if you see this  (04/14/25)";   
} else {
    $home_link = 'index.php';
    $reg_ready = False;
    $event_data = [
        "EVENT_ID" => 0,
        "EVENT_Name" => 'Unknown Event'
    ];
}

if(isset($_SESSION['login_user'])){
    $logged_in = 'yes';
 } else {
    $logged_in = 'no';
 }        




?>
<!DOCTYPE HTML>

<html>
	<head>
		<title><?php echo $event_data['EVENT_Name']; ?> Registration</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">

		<!-- Header -->
			<header id="header">
                <nav>
                    <ul class="nav">
                        <li><a href="<?php echo htmlspecialchars($home_link); ?>">Home</a></li>
                    </ul>
                </nav>
                <div id="logo">
                    <a href="index.php">
                        <span class="image fit"><img src="assets/images/NSXMeetup-Logo-white.png" alt="" /></span>
                    </a>
                </div>
                <nav>
                    <ul class="nav">
                    <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>                
			</header>

	            
		<!-- Main -->
			<div id="main" class="wrapper" logged_in="<?php echo $logged_in; ?>" reg_ready="<?php echo ($reg_ready)?"yes":"no"; ?>" sold_out="<?php echo ($event_data['EVENT_Sold_Out'])?"yes":"no"; ?>">
				<div class="inner">
                    <?php if ($event_data['EVENT_Sold_Out']) { ?>
                        <h1 class="major">SOLD OUT<br><?php echo $event_data['EVENT_Name']; ?> Waiting list</h1>
                    <?php } else { ?>
                        <h1 class="major"><?php echo $event_data['EVENT_Name']; ?> Registration</h1>
                    <?php } ?>
            <?php if($reg_ready) { ?>
                <?php if ($event_data['EVENT_Sold_Out']) { ?>
                    <div id="event_registration_results_text" style="text-align:center;"><p>The <?php echo $event_data['EVENT_Name']; ?> has reached capacity and is currently SOLD OUT. If you would like to be 
                    placed on the waiting list in the event that others cancel or the event's capacity is expanded, please complete the registration below. Waiting list
                    registrations are processed in the order received. If a spot opens up for the event, the organizers will reach out to you at the email address 
                    provided in your registration.</p></div>
                <?php } else { ?>
                    <div id="event_registration_results_text" style="text-align:center;"><p>In order for us to make sure we plan for the number of people coming to the event this year, please complete this simple registration form.</p></div>
                <?php } ?>

                        <div id="event_registration_container" class="event_registration_container">
                            <?php include "driver1_container.php"; ?>
                            <?php include "driver2_container.php"; ?>

                            <?php if ($event_data['EVENT_Collect_Payments']) {
                                print RenderRegistrationFeeInformation($event_data);
                            }
                            ?>
                            <?php print RenderEventSpecificQuestions($event_data); ?>
                        <div style="margin-top:1.25em;">
                            <?php if($event_data['EVENT_Collect_Payments']) { 
                                $starting_balance = 0; // the registration type drop downs add each registrant's fee (see register.js)
                                // Checkout stays disabled until each registrant has a registration type
                                // (UpdateCheckoutButtonState in register.js). Not for the waiting list.
                                $regtype_required = !$event_data['EVENT_Sold_Out'];
                            ?>
                                <!-- Handle STRIPE Credit Card Processing if it is available -->
                                <?php if($event_data['EVENT_Enable_Stripe_Processing']) { ?>
                                    <a href="#" id="stripe_checkout_button" class="button primary fit<?php echo $regtype_required ? ' disabled' : ''; ?>" requires_regtype="<?php echo $regtype_required ? 'yes' : 'no'; ?>" event_id="<?php echo $event_data['EVENT_ID']; ?>" event_name="<?php echo $event_data['EVENT_Name']; ?>" style="margin-top:1.0em;" sold_out="<?php echo ($event_data['EVENT_Sold_Out'])?"yes":"no"; ?>" reg_button_driver1_total=<?php echo $starting_balance; ?> reg_button_driver2_total=<?php echo $starting_balance; ?> guest_active="no"><?php echo ($event_data['EVENT_Sold_Out'])?"Join Waiting List":"Proceed to Checkout";?></a>
                                <?php } else { ?>
                                    <a href="#" id="main_register_button" class="button primary fit<?php echo $regtype_required ? ' disabled' : ''; ?>" requires_regtype="<?php echo $regtype_required ? 'yes' : 'no'; ?>" event_id="<?php echo $event_data['EVENT_ID']; ?>" event_name="<?php echo $event_data['EVENT_Name']; ?>" style="margin-top:1.0em;" sold_out="<?php echo ($event_data['EVENT_Sold_Out'])?"yes":"no"; ?>" reg_button_driver1_total=<?php echo $starting_balance; ?> reg_button_driver2_total=<?php echo $starting_balance; ?> guest_active="no"><?php echo ($event_data['EVENT_Sold_Out'])?"Join Waiting List":"REGISTER";?></a>
                                <?php } ?>
                            <?php } else { ?>
                                <a href="#" id="main_register_button" class="button primary fit" event_id="<?php echo $event_data['EVENT_ID']; ?>" event_name="<?php echo $event_data['EVENT_Name']; ?>" style="margin-top:1.0em;" sold_out="<?php echo ($event_data['EVENT_Sold_Out'])?"yes":"no"; ?>"><?php echo ($event_data['EVENT_Sold_Out'])?"Join Waiting List":"REGISTER";?></a>
                            <?php } ?>
                        </div>					
					</div>
				</div>
			</div>
            <?php } else { ?>
                <h3> ERROR: </h3>
                <p>'Event ID not set, do some drop down magic...'</p>

            <?php } ?>    
            <!-- Footer -->
			<section id="footer">
				<div id="contact" class="inner">
					<!-- Contact -->
					<header>
						<h2>Get in Touch</h2>
						<p>If you have questions about the event, please feel free to reach out to us. We'll get back to you as soon as possible.</p>
					</header>
					<!-- <?php //include 'contact.php'; ?> -->
                    <?php echo RenderContactInformation($event_data['EVENT_ID']); ?>
				</div>
			</section>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
			<script src="assets/js/mailer.js"></script>
			<script src="assets/js/register.js"></script>
            <script src="assets/js/checkout.js"></script>
			
			<script>
			  $( document ).ready(function() {
                var logged_in = $("#main").attr('logged_in');
                var create_account = $("#driver_one_contact_container").attr('create_account')
                console.log('Logged_in = ' + logged_in);
                console.log('Reg Ready = ', typeof($("#main").attr('reg_ready')));
                if(Boolean($("#main").attr('ready'))) {
                    console.log('Reg Ready = ', $("#main").attr('reg_ready'));
                } else {
                    console.log('Reg NOT Ready = ', $("#main").attr('reg_ready'));
                }


                // Setup a shopping basket for registration checkout.
                const shopping_cart = JSON.stringify({"basket": []});
                localStorage.setItem('shopping_cart', shopping_cart);
                console.log("Shopping Cart at DOM Read:" + localStorage.getItem('shopping_cart'));
                console.log("Driver One Base Cost = " + $("#cost_driver1").attr('cost'));
                if (parseInt($("#cost_driver1").attr('cost'), 10) > 0) {
                    console.log('Adding the Driver One base registration fee to the basket ' + $("#cost_driver1").attr('cost'))
                    shopping_cart_actions("baseRegistration", $("#cost_driver1").attr('cost'), "Event entry fee (Main Guest)", 'driver1', 'add');
                } 

				// Registration type drop downs (Event Registration Fee section)
				InitRegistrationTypeSelection();

				// Guest Two Role selection
				var elms = document.querySelectorAll('[name="driver_two_role_radio"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = ReactToDriverTwoRoleSelection;
				}
				
				// Generation Selection
				var elms = document.querySelectorAll('[name^="generation_selection_driver"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onchange = ReactToGenerationSelection;
				}				
               
				// Add additional guest to registration click handler
				document.querySelector('[id="info_field_driver1_additional_guest"]').onclick = AddAdditionalGuestToRegistration

				// Check to see if there is an existing account with this ID
				document.querySelector('[id="info_text_field_driver1_email"]').onblur = CheckForExistingAccount

				// Check to see if there is an existing account with this ID
				document.querySelector('[id="optional_text_field_driver1_facebook"]').onblur = CheckFacebookLinkFormatting
                document.querySelector('[id="optional_text_field_driver2_facebook"]').onblur = CheckFacebookLinkFormatting                

				
                if (logged_in === 'no' && create_account === 'yes') {
                    // Add additional guest to registration click handler
                    document.querySelector('[name="create_nsxmeetup_accnt"]').onclick = EnableCreateNewUserAccount		

                    // Verify that passwords match for account creation.
                    document.querySelector('[id="info_text_field_newuser_password_confirm"]').onkeyup = CheckPasswordMatch;                    
                }

				// Main Registration Button Click Handler
                // document.querySelector('[id="main_register_button"]').onclick = RegisterForTheEvent                
				const main_register_button = document.querySelector('[id="main_register_button"]')
                if (main_register_button) {
                    console.log("Activating the main resgister button.");
                    main_register_button.onclick = RegisterForTheEvent;
                }

                const stripe_checkout_button = document.querySelector('[id="stripe_checkout_button"]')
                if (stripe_checkout_button) {
                    console.log("Activating the main resgister button.");
                    stripe_checkout_button.onclick = ProcessStripeCheckOut;
                }
                
				
				// Attending choices Click Handler
				var elms = document.querySelectorAll('[id^="info_checkbox_field_driver"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = ReactToAttendanceChoice;
				}
				// Attending choices Click Handler
				var elms = document.querySelectorAll('[id^="info_field_user_display_choice_driver"]');
				console.log(elms.length);
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = ReactToSharedInformationChoice;
				}

				// Message Click Handler
				var elms = document.querySelectorAll('[id^="button_action_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = HandleButtonActionClick;
				}

			  });
			</script>			

	</body>
</html>