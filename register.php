<?php
require_once 'SessionBootstrap.php';
app_session_start();

if(isset($_SESSION['login_user'])){
    $logged_in = 'yes';
 } else {
    $logged_in = 'no';
 }        

?>
<!DOCTYPE HTML>

<html>
	<head>
		<title>2024 NSX Mideast Registration</title>
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
				<?php include 'header2.php'; ?>
			</header>

	            
		<!-- Main -->
			<div id="main" class="wrapper" logged_in="<?php echo $logged_in; ?>">
				<div class="inner">
					<h1 class="major">Mideast NSX Meet Registration</h1>
					<div id="event_registration_results_text" style="text-align:center;"><p>In order for us to make sure we plan for the number of people coming to the event this year, please complete this simple registration form.</p></div>
					<div id="event_registration_container" class="event_registration_container">
						<?php include "driver1_container.php"; ?>
						<?php include "driver2_container.php"; ?>
						<?php include "event_info_container.php"; ?>
						<a href="#" id="main_register_button" class="button primary fit" event_id="2" event_name="style="margin-top:1.0em;">REGISTER</a>
					
						
					</div>
				</div>
			</div>

		<!-- Footer -->
			<section id="footer">
				<div id="contact" class="inner">
					<!-- Contact -->
					<header>
						<h2>Get in Touch</h2>
						<p>If you have questions about the event, please feel free to reach out to us. We'll get back to you as soon as possible.</p>
					</header>
					<?php include 'contact.php'; ?>
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
			
			<script>
			  $( document ).ready(function() {
				
                var logged_in = $("#main").attr('logged_in');
                console.log('Logged_in = ' + logged_in);

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

				
                if (logged_in === 'no') {
                    // Add additional guest to registration click handler
                    document.querySelector('[name="create_nsxmeetup_accnt"]').onclick = EnableCreateNewUserAccount		

                    // Verify that passwords match for account creation.
                    document.querySelector('[id="info_text_field_newuser_password_confirm"]').onkeyup = CheckPasswordMatch;                    
                }

				// Main Registration Button Click Handler
				document.querySelector('[id="main_register_button"]').onclick = RegisterForTheEvent

				
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