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
		<title>NSX Meetup Account Information</title>
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
				<?php include 'header3.php'; ?>
			</header>
			
			

		<!-- Main -->
			<div id="main" user_role="<?php echo $admin_rights; ?>" class="wrapper" style="padding:2rem 0 2rem 0;">		
				<section class="wrapper" style="padding:2rem 0 2rem 0;">
					<div class="inner">
						<h3><?php echo $panel_name; ?> - <?php echo $admin_rights; ?></h3>

		<!-- Event Details -->
			<section id="details">
				<div id="details_info" class="inner">
					<div class="features-grid">

						<!-- Directions -->
						<div>
							<span class="number"><span class="icon solid fa-user"></span></span>
							<h3>Find / Edit Registrations</h3>
							<p>For driving directions to the hotel and all weekend events, click the link below.</p>
							<button id="find_edit_registration" class="button primary small fit">Click Here</button>
						</div>
						<!-- Attendees -->
						<div>
							<span class="number"><span class="icon solid fa-users"></span></span>
							<h3>Download All Registrations</h3>
							<p>Download a spreadsheet of all the registrations for the 2022 Mideast NSX Meet.</p>
							<a href="#" id="display_email_address" class="button primary small fit">Click Here</a>
						</div>
						
						<!-- Download Labels -->
						<div>
							<span class="number"><span class="icon solid fa-user"></span></span>
							<h3>Download Labels Spreadsheet</h3>
							<p>Download a CSV file used to make lanyard labels</p>
							<button id="download_labels_csv" class="button primary small fit">Click Here</button>
						</div>

						<!-- Download Photo Numbers -->
						<div>
							<span class="number"><span class="icon solid fa-camera"></span></span>
							<h3>Download Photo Numbers Spreadsheet</h3>
							<p>Download a CSV file used to match drivers to cars in photos</p>
							<button id="download_photonumber_csv" class="button primary small fit">Click Here</button>
						</div>
						
						<!-- News Container -->
						<div>
							<span class="number"><span class="icon solid fa-bullhorn"></span></span>
							<h3>News Section</h3>
							<p>Edit the content of the news section or remove it all together.</p>
							<a href="#" id="edit_news_container_textarea" class="button primary small fit">Edit / Remove</a>
						</div>
						<!-- Swap Shop -->
						<div>
							<span class="number"><span class="icon solid fa-tag"></span></span>
							<h3>Swap Shop</h3>
							<p>Add / Edit / Delete an item </p>
							<a href="#" class="button primary small fit">Coming Soon</a>
						</div>
						
						<!-- Photo Numbers -->
						<div>
							<span class="number"><span class="icon solid fa-camera"></span></span>
							<h3>Photo Numbers</h3>
							<p>Add / Edit / Email photo iinfomration </p>
							<button id="photo_numbers" class="button primary small fit">Click Here</button>
						</div>
						

					</div>
				</div>
			</section>
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
			<!-- <script src="assets/js/stupidtable.js"></script> -->

		<script>
		  $( document ).ready(function() {
		    var user_role = $("#main").attr('user_role');
			///alert(user_role);
			if (user_role == 'admin') {
			  document.querySelector('[id="find_edit_registration"]').onclick = RenderFindEditRegistration;
			  document.querySelector('[id="display_email_address"]').onclick = RenderEmailList;
			  document.querySelector('[id="edit_news_container_textarea"]').onclick = RenderEditNewsContainer;
			  document.querySelector('[id="download_labels_csv"]').onclick = Download_Labels_CSV;
			  document.querySelector('[id="download_photonumber_csv"]').onclick = Download_Photo_Number_CSV;
			  document.querySelector('[id="photo_numbers"]').onclick = WorkOnPhotoLables;
			}
		  });
		</script>
		

			
	</body>
</html>