<?php
include('login.php'); // Includes Login Script
include('LandingFunctions.php');
include('SPONSORFunctions.php');

// Resolve login/session state before any HTML is emitted below, since
// session.php may redirect (idle timeout, missing account) - that only
// works if it runs before headers are sent.
$is_logged_in = isset($_SESSION['login_user']);
if ($is_logged_in) {
    include 'session.php';
}

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>NSX Meetup</title>
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
            <header id="header" class="alt">
            <?php
                if($is_logged_in){
                    include 'header_user.php';
                 } else {
                    include 'header_landing.php';
                 }
			?>
			</header>
	
			
        <!-- Login Modal -->	
			<section>
			  <?php include 'login_modal.php'; ?>
			</section>
			
		<!-- Banner -->
            <?php include 'banners.php'; ?>

        <!-- Page Opening Sections -->
        <section id="welcome" class="opener active">
            <div id="welcome_letter" class="inner">
                <span class="welcome-title" >NSX Meetup Events</span>
                <p>Welcome to NSX Meetup, a place where you can find some of the best Acura NSX focused events in the country. Listed below are the current events that are /
                coming up. Click on the links below each to launch individual pages for each event where you can learn about each experience and register to participate!</p>
            </div>                
        </section>

        <?php echo RenderLandingPageEvents(); ?>

        <!-- SPONSOR SECTION -->
            <section id="sponsors" class="active" style="margin-top:4em;">
                <div id="sponsor_info" class="inner">
                    <span class="welcome-title" >Thank You to Our Sponsors</span>
                    <?php NEWRenderSponsorInformation(0); ?>
                </div> 
            </section>

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
			<script src="assets/js/render.js"></script>
			
			<script>
			  $( document ).ready(function() {
				  
				// Render any news that is needed
				// RenderNewContainerContent();				
                // RenderEventStatsContent();

				var elms = document.querySelectorAll('[id^="button_action_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = HandleButtonActionClick;
				}

				var elms = document.querySelectorAll('[id^="modal_open_button_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = OpenSponsorModal;
				}
				
				var elms = document.querySelectorAll('[class="modal-close"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = CloseSponsorModal;
				}	                                   
			  });
			  
			//   document.querySelector('[id="render_atttending_page"]').onclick = RenderAttendingPage;
            //   document.querySelector('[id="render_local_attractions"]').onclick = RenderLocalAttractionsPage;
            //   document.querySelector('[id="render_agenda_page"]').onclick = RenderAgendaPage;
			  
			</script>

            <script>
                function OpenSponsorModal(event) {
                    event.preventDefault();
                    var index_counter = $(this).attr('index_counter');
                    console.log('Open the sponsor modal at index counter = ' + index_counter);
                    
                    // Display the desired Modal
                    document.getElementById("Sponsor_Modal_"+index_counter+"").style.display = "block";
                }

                function CloseSponsorModal(event) {
                    event.preventDefault();
                    var index_counter = $(this).attr('index_counter');
                    console.log('Close the sponsor modal at index counter = ' + index_counter);
                    
                    // Display the desired Modal
                    document.getElementById("Sponsor_Modal_"+index_counter+"").style.display = "none";
                }                
            </script>   
			<?php if ($is_logged_in) echo app_idle_timer_script(); ?>
	</body>
</html>
