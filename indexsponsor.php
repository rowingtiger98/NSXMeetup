<?php
include('login.php'); // Includes Login Script

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
		<title>NSX Meetup - My Account</title>
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
            <section id="welcome" class="wrapper opener">
                <div id="welcome_letter" class="inner">
                    <span class="welcome-title" >2024 Mideast NSX Meetup</span>
                    <!-- <p>Even though we added an extra full day of events, the 2023 Mideast NSX Meet still somehow went by too fast. 
                    Before we knew it, the weekend was gone, and it was sadly time to end what felt more like a family reunion 
                    than a car meetup. Thanks to all the participants who made the journey to North Carolina to be with us. 
                    We all know that the NSX is an amazing car, but you all are proof that what makes the NSX so special is the people
                    behind the wheel. From all of us at the Mideast NSX Meetup, <span style="color:#FFCC00;">see you in 2024</span>!</p> -->
                    <p>Save the date for the 2024 NSX Mideast Meetup in Cary, North Carolina! The event this year will be help from May 30 - June 2, 2024.</p>
                </div>                
                <!-- <div id="event_stats" event_id="1" class="event_stats_inner active"></div> -->
            </section>


		<!-- Event Details -->
        <section id="details" class="wrapper opener">
				<div id="details_info" class="inner">
					<div class="features-grid">
						<div>
							<span class="number"><span class="icon solid fa-hotel"></span></span>
							<h3>Hotel Information</h3>
							<p>This year, we have rooms blocked in both the Hilton Garden Suites and the Doubletree Hotels. These hotels are side by side. To book your room, click the link below.</p>
							<a href="hotels.php" class="button primary small fit">Book Your Room</a>
						</div>
						<!-- Schedule -->
						<div>
							<span class="number"><span class="icon solid fa-clipboard-list"></span></span>
							<h3>Weekend Events</h3>
							<p>The weekend will be filled with good people, good drives and good food and drink. For a complete itinerary, click the link below.</p>
							<a href="#" id="render_agenda_page" class="button primary small fit">Click Here</a>
							<!-- <a href="#" class="button  small fit">Coming Soon</a> -->
						</div>
						<!-- Attendees -->
						<div>
							<span class="number"><span class="icon solid fa-users"></span></span>
							<h3>Attendees</h3>
							<p>Want to see who is coming to the 2022 Meetup? To see who is coming, and where they are coming from, click below to see a full list of drivers that have opted to share that information.</p>
							<a href="#" id="render_atttending_page" event_id="2" class="button primary small fit">Click Here</a>
							<!-- <a href="#" class="button  small fit">Coming Soon</a> -->
						</div>  
						<!-- Local Attraction -->
						<div>
							<span class="number"><span class="icon solid fa-search-location"></span></span>
							<h3>Local Attractions</h3>
							<p>In this section, you'll find some of our favorite things to do in Raleigh, Cary and the surrounding areas. Click here to see other attractions (driving and non-driving), driving routes in the area and list of things you might need while visiting.</p>
							<a href="#" id="render_local_attractions" event_id="1" class="button primary small fit" editable="false">Click Here</a>
							<!-- <a href="#" class="button  small fit">Coming Soon</a> -->
						</div>                                              
						<!-- Directions -->
						<div>
							<span class="number"><span class="icon solid fa-map"></span></span>
							<h3>Directions</h3>
							<p>For driving directions to the hotel and all weekend events, click the link below.</p>
							<!-- <a href="directions.php" class="button primary small fit">Click Here</a> -->
							<a href="#" class="button  small fit">Coming Soon</a>
						</div>

						<!-- Swap Shop -->
						<div>
							<span class="number"><span class="icon solid fa-tag"></span></span>
							<h3>Swap Shop</h3>
							<p>Have something to sell at the event? List the item in our swap shop. To see available items or to offer up an item to sell, click the link below.</p>
							<!-- <a href="swapshop.php" class="button primary small fit">Click Here</a> -->
							<a href="#" class="button  small fit">Coming Soon</a>
						</div>
					</div>
					<ul class="actions special">
						<a href="register.php" event_id="2" class="button large primary fit">Register Today</a>
                        <!-- <a href="#" class="button  small fit">Registration Opening Soon</a> -->
					</ul>
				</div>
			</section> 


			<!-- Special Guest Section -->
			<section id="rivernsx" class="active" style="margin-top:2em;">
				<div class="inner">
					<span class="button fit" style="margin-bottom: 1.25em;">Special Guest at the 2024 Mideast NSX Meet</span>
                    <div class="row gtr-unform">
                    	<div class="col-4 col-12-small">
							<div style="padding:1.25em; border: 1px solid white;">
								<h3>1993 NSX-R</h3>
								<span class="image fit"><img src="assets/images/nsxrfrontpage.jpg"></span>
								<p style="padding: 1.25em 0 0 0 ;">As we all know, there is the NSX which we all know and love. And then there is the NSX-R that most can 
								only dream about. In October of 2022, <span style="color:#FFCC00;">Vince Howard</span> became the owner of a stunning 1993 Brooklands Green Pearl over black Alcantara NSX-R by winning
								a record setting NSX auction at <a href="https://bringatrailer.com/listing/1993-honda-nsx-r/" target="_blank" style="color:#ff0000;">Bring-A-Trailer</a>.<br>
								<br>
								As epic as this car is, the strory of how and why this car was won is equally, and fittingly so, epic. I encourage all of you to read the 
								story of how this car was won by clicking the link below. Vince is planning on bringing this iconic car to the 2023 Mideast NSX Meet. We hope you will all join us as we admire this incredible car.
								</p>
								<a href="nsxr.php" class="button primary fit">Click to read Vince's epic tale</a>
							</div>
						</div>
                    	<div class="col-4 col-12-small">
							<div style="padding:1.25em; border: 1px solid white;">
								<h3>Ken Sax</h3>
								<span class="image fit"><img src="assets/images/KenSaxFrontPage.jpg"></span>
								<p style="padding: 1.25em 0 0 0 ;">Without Ken Sax's passion for the NSX, it highly likely that our community would look vastly different.
								Ken Sax has be a fan of the NSX since before the NSX was even a thing! In 1986, Ken Sax put down a deposit at his local Acura dealer for the then
								rumored $25,000.00 twin-turbo "NSX" that Acura was developing. Six years after taking ownership of his NSX, in 1997, Ken led and personally 
								financed the first NSXPO hosted in Denver, Colorado.<br><br>
								While at the 2024 Mideast NSX Meet, make sure you take some time to meet Ken Sax and learn the full history of the NSX community.<br>
								<ul>
									<li>1986 – Placed a deposit with an Acura dealership for the rumored $25,000 twin-turbo NSX.</li>
									<li>December 1990 – Bought my red/ivory 1991 NSX.</li>
									<li>October 1992 – Attended the first dealer-sponsored NSX event in North America, at Lindsay Acura in Columbus, Ohio
  
 
 May 1997 – Led (and financed) the first NSXPO, NSXPO ’97, in Denver
  
 
 April 1998 – The NSX Club of America was founded by Dr. David Hwang.  keN SaX was the first treasurer of the organization and continued in that role for ten years.
  
 
 May 1998 – Led NSXPO ’98, in Columbus and Marysville, Ohio
  
 
 October 2000 – Led NSXPO 2000, in Atlanta
  
 
 October 2001 – Led NSXPO 2001, in Elkhart Lake, Wisconsin
  
 
 October 2007 – Led NSXPO 2007, in Columbus								
								</p>
							</div>
						</div>	
                    	<div class="col-4 col-12-small">
				 			<div style="padding:1.25em; border: 1px solid white;">
								<h3>1993 NSX-R</h3>
								<span class="image fit"><img src="assets/images/nsxrfrontpage.jpg"></span>
								<p style="padding: 1.25em 0 0 0 ;">As we all know, there is the NSX which we all know and love. And then there is the NSX-R that most can 
								only dream about. In October of 2022, <span style="color:#FFCC00;">Vince Howard</span> became the owner of a stunning 1993 Brooklands Green Pearl over black Alcantara NSX-R by winning
								a record setting NSX auction at <a href="https://bringatrailer.com/listing/1993-honda-nsx-r/" target="_blank" style="color:#ff0000;">Bring-A-Trailer</a>.<br>
								<br>
								As epic as this car is, the strory of how and why this car was won is equally, and fittingly so, epic. I encourage all of you to read the 
								story of how this car was won by clicking the link below. Vince is planning on bringing this iconic car to the 2023 Mideast NSX Meet. We hope you will all join us as we admire this incredible car.
								</p>
								<a href="nsxr.php" class="button primary fit">Click to read Vince's epic tale</a>
							</div>
						</div>												
					</div>	
				</div>
			</section>


        <!-- SPONSOR SECTION -->
        <section id="sponsors" class="active" style="margin-top:4em;">
				<div id="sponsor_info" class="inner">
                    <span class="welcome-title" >Our Sponsors</span>
                    <?php include 'sponsor_section.php'; ?>
                </div>
        </section>




        <!--Special Guest Section -->
		<section id="photosection" class="active" style="margin-top:6em;">
				<div class="inner">
					<span class="button fit" style="margin-bottom: 1.25em;">Special Guest at the 2024 Mideast NSX Meet</span>
                    <div class="row gtr-25 gtr-uniform" style="margin-top:0.25em;">
                    	<div class="col-3 col-12-small">                            
							<span class="image fit"style="padding:1em;"><img src="assets/images/KenSaxFrontPage.jpg"></span><br>
						</div>
                    	<div class="col-9 col-12-small">
						<h3>Ken Sax</h3>
								<p style="padding: 0 0 0 0 ;">Without Ken Sax's passion for the NSX, it highly likely that our community would look vastly different.
								Ken Sax has be a fan of the NSX since before the NSX was even a thing! In 1986, Ken Sax put down a deposit at his local Acura dealer for the then
								rumored $25,000.00 twin-turbo "NSX" that Acura was developing. Six years after taking ownership of his NSX, in 1997, Ken led and personally 
								financed the first NSXPO hosted in Denver, Colorado.<br><br>
								While at the 2024 Mideast NSX Meet, make sure you take some time to meet Ken Sax and learn the full history of the NSX community.
								<ul>
									<li>1986 – Placed a deposit with an Acura dealership for the rumored $25,000 twin-turbo NSX.</li>
									<li>December 1990 – Bought my red/ivory 1991 NSX.</li>
									<li>October 1992 – Attended the first dealer-sponsored NSX event in North America, at Lindsay Acura in Columbus, Ohio</li>
									<li>May 1997 – Led (and financed) the first NSXPO, NSXPO ’97, in Denver, Colorado.</li>
									<li>April 1998 – The NSX Club of America was founded by Dr. David Hwang.  Ken Sax was the first treasurer of the organization and continued in that role for ten years.</li>
									<li>May 1998 – Led NSXPO ’98, in Columbus and Marysville, Ohio.</li>
									<li>October 2000 – Led NSXPO 2000, in Atlanta, Georgia.</li>
									<li>October 2001 – Led NSXPO 2001, in Elkhart Lake, Wisconsin.</li>
									<li>October 2007 – Led NSXPO 2007, in Columbus, Ohio.</li>
								</ul>
                        </div>
                    	<div class="col-6 col-12-medium col-12-small">                            
							<a href="skefree/index.html" class="button primary fit" target="_blank">High Rez Group Photos (FREE)</a>
						</div>
                    	<div class="col-6 col-12-medium col-12-small">                            
							<a href="skepay/index.html" class="button primary fit" target="_blank">Low Rez Individual Sample Photos</a>
						</div>
			
						<!-- AJ Photo Section -->
                    	<div class="col-9 col-12-small">
                            <p>Every year, AJ takes some amazing pictures of our event and he has shared them with us so you can all download them. The link below
                                will open AJ's 2023 Mideast NSX Meetup gallery where you can view and save any photo you like. Also, click the Instagram icon to open AJ's 
                                Instagram page and follow him there. Thanks AJ! 
                            </p>
                            <ul class="icons">
                                <li><a href="https://www.instagram.com/thesolonsx/" class="icon brands fa-instagram" target="_blank"><span class="label">Instagram</span></a></li>
                            </ul>                            		
                        </div>							
                    	<div class="col-3 col-12-small">                            
							<span class="image fit"><img src="assets/images/solonsx.png"></span><br>

						</div>

                    	<div class="col-12 col-12-medium col-12-small">                            
							<a href="ajphotos/index.html" class="button primary fit" target="_blank">Click Here for AJ's pictures</a>
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
