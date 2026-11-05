<?php
include('login.php'); // Includes Login Script

// Resolve login/session state before any HTML is emitted below, since
// session.php may redirect (idle timeout, missing account) - that only
// works if it runs before headers are sent.
$is_logged_in = isset($_SESSION['login_user']);
if ($is_logged_in) {
    include 'session.php';
}

// -----------------------------------------------------------------------
// Build out the information section on which event is being displayed.
// -----------------------------------------------------------------------
if(isset($_GET['event_id'])) {
    $display_error_message = False;
    $event_id = $_GET['event_id'];
    $event_data = GetEventInformationFromID($event_id);
    $head_title = $event_data['EVENT_Name'];
    $display_sponsor = $event_data['EVENT_Display_Sponsors'];
} else {
    $display_error_message = True;
    $head_title = 'NSX MEETUP - Unknown Event ID';
    $display_sponsor - False;
}

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $head_title; ?></title>
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
                    include 'header.php';
                 }        
			?>			  
			</header>
	
			
        <!-- Login Modal -->	
			<section>
			  <?php include 'login_modal.php'; ?>
			</section>
			
		<!-- Banner -->
            <?php include 'banners.php'; ?>


		<!-- Event Stats -->
            <section id="welcome" class="wrapper opener active">
                <?php if($display_error_message) { ?>
                    <div id="welcome_letter" class="inner">
                        <span class="welcome-title" >NSX Meetup - Invalid Event</span>
                        <p>Oh No! The event that you are hoping to see is returning an error when we try to render the information about the event.
                        If you are seeing this error, please drop an email to <a href="mailto:ron@nsxmeetup.com">ron@nsxmeetup.com</a>.</p>
                    </div>                
                    <!--<div id="event_stats" event_id="2" class="event_stats_inner active"></div>-->                        
                <?php } else { ?>
                    <div id="welcome_letter" class="inner">
                        <span class="welcome-title" ><?php echo $head_title; ?></span>
                        <p><?php echo $event_data['EVENT_Opening_Section']; ?></p>
                    </div>                
                    <!--<div id="event_stats" event_id="2" class="event_stats_inner active"></div>-->
                <?php } ?>
            </section>

		<!-- Chris Cut GoFund Me Section Hangout -->
			<section id="hangout" class="active" style="margin:1em 0 4em 0;">
				<div class="inner">
					<span class="welcome-title" >Christopher Cutler (NSXTRA)</span>				
					<div class="row gtr-uniform">
						<div class="col-3 col-12-small" style="padding-top:3.25em;">
							<span class="image fit"><img src="images/chriscutfrontpage.jpg"></span>
						</div>
						<div class="col-9 col-12-small" style="padding-top:1.6em;">
							<p style="padding: 1.25em 0 0 0 ;">On April 1st, 2024, the NSX community lost a truly inspirational member, but more so, a friend all that knew home. Borrowing from Sheraz Bhutta's well said words, "Christopher 
							impacted every life he walked past during his time in this world. He was very empathetic, understanding, loving, would take the shirt off his back for anyone and had a heart of gold." Efforts are being made to 
							help cover the unexpected cost of a memorial service for Chris. Below is a link to the GoFund me page setup to try and provide him a proper farewell. If you can give, please do.
							<br><br><a href="https://www.gofundme.com/f/christopher-cutler-nsxtra?utm_campaign=p_cp+share-sheet&utm_medium=copy_link_all&utm_source=customer" class="button primary fit" target="_blank">Donate Here</a></p>
						</div>
					</div>	
				</div>
			</section>  


        <div class="inner" style="border-bottom: 1px solid white; margin-top:2em;"></div>

        <!-- SPONSOR SECTION -->
        <?php if ($display_sponsor) { ?>
            <section id="sponsors" class="active" style="margin-top:4em;">
                <div id="sponsor_info" class="inner">
                    <span class="welcome-title" >Thank You to Our Sponsors</span>
                    <?php include 'sponsor_section.php'; ?>
                </div>
            </section>
        <?php } ?>


        <div class="inner" style="border-bottom: 1px solid white"></div>

        <!--Photos Section -->
			<section id="photosection" class="active" style="margin-top:6em;">
				<div class="inner">
                    <span class="welcome-title" >2024 NSX Meetup Photographs</span>
					<p>Photos are coming soon!</p>
                    <!-- <div class="row gtr-25 gtr-uniform" style="margin-top:0.25em;">
                    	<div class="col-3 col-12-small">                            
							<span class="image fit"><img src="images/SKEWhitelogo.png"></span><br>
                            <ul class="icons">
                                <li><a href="http://instagram.com/ske.photos" class="icon brands fa-instagram" target="_blank"><span class="label">Instagram</span></a></li>
                                <li><a href="https://www.facebook.com/ske.photos.fb" class="icon brands fa-facebook" target="_blank"><span class="label">Facebook</span></a></li>
                                <li><a href="https://ske.photos/" class="icon solid fa-globe" target="_blank"><span class="label">Globe</span></a></li>
                            </ul>
						</div>
                    	<div class="col-9 col-12-small">
                            <p>At the 2023 Mideast NSX Meetup, we had the priviledge of Sam Evans snapping photos for us. It was one of those great life conincedences where 
                                Sam just happened to walk by the hotel when the first two NSX's arrived. Little did he know at the time just how many more would be coming in. 
                                With Sam being a car enthusiast himself, the opportunity to snap so many spcial cars was too much to pass up. To our amazing beneift, Sam hung 
                            out all weekend and has provided hundred of photos for us. There are two category of pictures below; group photos and individual photos. Sam has graciously
                            <span style="color:#ff0000;">DONATED</span> the high resolution group photos, <span style="color:#ff0000;">FREE</span> to download. The high resolution individual files are available to purchase through his site. You 
                        can see low resolution examples by clicking the link below.</output>
                            </p>		
                        </div>
                    	<div class="col-6 col-12-medium col-12-small">                            
							<a href="skefree/index.html" class="button primary fit" target="_blank">High Rez Group Photos (FREE)</a>
						</div>
                    	<div class="col-6 col-12-medium col-12-small">                            
							<a href="skepay/index.html" class="button primary fit" target="_blank">Low Rez Individual Sample Photos</a>
						</div>
			
                    	<div class="col-3 col-12-small">                            
							<span class="image fit"><img src="images/solonsx.png"></span><br>

						</div>
                    	<div class="col-9 col-12-small">
                            <p>Every year, AJ takes some amazing pictures of our event and he has shared them with us so you can all download them. The link below
                                will open AJ's 2023 Mideast NSX Meetup gallery where you can view and save any photo you like. Also, click the Instagram icon to open AJ's 
                                Instagram page and follow him there. Thanks AJ! 
                            </p>
                            <ul class="icons">
                                <li><a href="https://www.instagram.com/thesolonsx/" class="icon brands fa-instagram" target="_blank"><span class="label">Instagram</span></a></li>
                            </ul>                            		
                        </div>	
                    	<div class="col-12 col-12-medium col-12-small">                            
							<a href="ajphotos/index.html" class="button primary fit" target="_blank">Click Here for AJ's pictures</a>
						</div>                        				
					</div>	 -->
				</div>
			</section>
      
		<div class="inner" style="border-bottom: 1px solid white"></div>

		<!-- NSXCA Membership Section -->
            <section id="welcome" class="active">
				<div id="welcome_letter" class="inner">
                    <div class="row gtr-uniform">
                    	<div class="col-12 col-12-small">
							<span class="image fit"><img src="images/NSXCA-Logo-white.png"></span>
						</div>
						<div class="col-12 col-12-small">
							<a href="https://nsxca.org/product/nsxca-membership/" target="_blank" class="button fit" style="margin-bottom:1em;">JOIN THE NSX Club of America Today</a>
							<p>Membership is available in one, two, and three-year terms (discounts applied for two and three-year terms). Memberships can be purchased on the NSX Club of America website by <a href="https://nsxca.org/product/nsxca-membership/" target="_blank">clicking here</a></p>
							<label for="membership_benefits_list">Your membership includes</label>
							<ul id="membership_benefits_list" style="list-style:circle;margin-bottom:3em;">
                                <li><span style="color:#ff0000;font-weight:600;">NSX Driver</span> – printed full color quarterly magazine NSX Driver mailed to your door – shipped within one quarter of receiving your membership. Click here for a sample!</li>
                                <li><span style="color:#ff0000;font-weight:600;">NSX Driver Digital Edition</span> – read NSX Driver online, including the last issue and select archived issues – available immediately online.</li>
                                <li><span style="color:#ff0000;font-weight:600;">NSXCA windshield decal & printed NSXCA Membership Card</span> – shipped once per year with NSX Driver. An electronic copy is available immediately for download online.</li>
                                <li><span style="color:#ff0000;font-weight:600;">NSXPO</span> – access to the annual gathering of NSX owners.</li>
                                <li><span style="color:#ff0000;font-weight:600;">Discounts</span> – discounts available at participating vendors</li>
							</ul>
						</div>
					</div>
                    
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
				RenderNewContainerContent();				
                RenderEventStatsContent();

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
			  
			  document.querySelector('[id="render_atttending_page"]').onclick = RenderAttendingPage;
              document.querySelector('[id="render_local_attractions"]').onclick = RenderLocalAttractionsPage;
              document.querySelector('[id="render_agenda_page"]').onclick = RenderAgendaPage;
			  
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
	</body>
</html>
