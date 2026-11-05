<?php
include('session.php');

$admin_rights =  $login_user_role;

if ($admin_rights == 'NOT SET') {
	$panel_name = 'LOGOUT Panel';
} else if ($admin_rights == 'admin' || $admin_rights == 'overlord') {
  $panel_name = 'Administrative Panel';
} else {
  $panel_name = 'Account Panel';
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>NSX Meetup Account Information.</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
        <link rel="stylesheet" href="assets/css/admintools.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">

	<!-- Header -->
			<header id="header">
				<?php include 'header3.php'; ?>
			</header>
			
			

		<!-- Main -->
			<input type="hidden" id="session_information" info_one="<?php echo $login_userid; ?>" info_two="<?php echo $login_username; ?>" />	
			<div id="main" user_role="<?php echo $login_userid; ?>" class="wrapper" style="padding:2rem 0 2rem 0;">
				<section class="wrapper" style="padding:2rem 0 2rem 0;">
					<div id="main_content_container" class="inner">
						<!-- <h3><?php echo $panel_name; ?> - <?php echo $login_username; ?> - <?php echo $admin_rights; ?> - <?php echo $login_userid; ?></h3> -->
					</div>
				</section>
			</div>

		<!-- Footer -->
			<section id="footer">

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
			<script src="assets/js/account.js"></script>
            <script src="assets/js/render.js"></script>
			<script src="assets/js/stupidtable.js"></script>
            <script src="assets/js/sponsormanagement.js"></script>
            <script src="assets/js/database_actions.js"></script>
            <script src="assets/js/backbuttonfunctions.js"></script>
            <script src="assets/js/eventsettings.js"></script>
            <script src="assets/js/tinymce/tinymce.min.js"></script>            

		<script>
		  $( document ).ready(function() {
		    var user_role = $("#main").attr('user_role');
			RenderMyAccountInformation();

			var elms = document.querySelectorAll('[id*="myaccount_link_"]');
			console.log(elms.length);
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = HandleMyAccountButtonActionClick;
			}
            
            console.log('user role = ' + user_role);
			if (user_role == 'admin') {
			  document.querySelector('[id="find_edit_registration"]').onclick = RenderFindEditRegistration;
            //   document.querySelector('[id="registration_statistics_information"]').onclick = RenderRegistrationStatistics;
			  document.querySelector('[id="display_email_address"]').onclick = RenderEmailList;
			  document.querySelector('[id="edit_news_container_textarea"]').onclick = RenderEditNewsContainer;
			//   document.querySelector('[id="download_labels_csv"]').onclick = Download_Labels_CSV;
			  document.querySelector('[id="download_photonumber_csv"]').onclick = Download_Photo_Number_CSV;
			  document.querySelector('[id="photo_numbers"]').onclick = WorkOnPhotoLables;
              
			}
		  });
		</script>
		

			
			<?php echo app_idle_timer_script(); ?>
	</body>
</html>