<!DOCTYPE HTML>

<html>
	<head>
		<title>2022 NSX Mideast Pictures</title>
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
					<h1 class="major">2021 Mideast NSX Meet Pictures</h1>
					<div class="box alt">
						<div class="row gtr-50 gtr-uniform">
                        <?php
                        if ($handle = opendir('pictures')) {
                            while (false !== ($entry = readdir($handle))) {
                                if ($entry != "." && $entry != "..") {
                                    //echo "$entry\n";
                        			print '<div class="col-4"><a href="pictures/'.$entry.'" target="_blank"><span class="image fit"><img src="pictures/'.$entry.'" alt="" /></span></a></div>';
                                }
                            }
                            closedir($handle);
                        }
                        
                        ?>
						</div>
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

				// Main Registration Button Click Handler
				document.querySelector('[id="main_register_button"]').onclick = RegisterForTheEvent
				
				// Attending choices Click Handler
				var elms = document.querySelectorAll('[id^="info_checkbox_field_driver"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = ReactToAttendanceChoice;
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