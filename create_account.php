<!DOCTYPE HTML>

<html>
	<head>
		<title>2022 NSX Mideast Registration</title>
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
			<div id="main" class="wrapper">
				<div class="inner">
					<h1 class="major">NSX Meetup Registration</h1>
					<div id="event_registration_results_text" style="text-align:center;"><p>Use the form below to create a basic user account at NSX Meetup. Once you have an account established, you will be able to register for any NSX event with the simple cilck of a single button.</p></div>
					<div id="event_registration_container" class="event_registration_container">
						<?php include "create_user_account.php"; ?>
						<a href="#" id="main_register_button" class="button primary fit disabled" style="margin-top:1.0em;" >REGISTER</a>
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
            <script src="assets/js/account_creation.js"></script>
			
			<script>
			  $( document ).ready(function() {

                // Verify that your username is unique in the system.
				document.querySelector('[id="newuser_verify_unique_username_button"]').onclick = VerifyUniqueUsername;

                document.querySelector('[id="info_text_field_newuser_password_confirm"]').onkeyup = CheckPasswordMatch;

				// Generation Selection
				var elms = document.querySelectorAll('[name^="generation_selection_vehicle_"]');
				for (i = 0; i < elms.length; i++) {
			    		elms[i].onchange = ReactToGenerationSelection;
				}				
				  
				// Add additional guest to registration click handler
				//document.querySelector('[id="info_field_driver1_additional_guest"]').onclick = AddAdditionalGuestToRegistration

				//Main Account Creation Button Click Handler
				document.querySelector('[id="main_register_button"]').onclick = CreateUserAccount
							
				// Message Click Handler
				//var elms = document.querySelectorAll('[id^="button_action_"]');
				//for (i = 0; i < elms.length; i++) {
				//	elms[i].onclick = HandleButtonActionClick;
				//}
				// Attending choices Click Handler

				var elms = document.querySelectorAll('[id^="info_field_user_display_choice_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = ReactToSharedInformationChoice;
				}

			  });
			</script>			

	</body>
</html>