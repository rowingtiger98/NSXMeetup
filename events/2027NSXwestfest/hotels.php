<!DOCTYPE HTML>

<html>
	<head>
		<title>2025 NSXpedition Hotels</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="../../assets/css/main.css" />
		<link rel="stylesheet" href="../../assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">

		<!-- Header -->
			<header id="header">
				<?php include '../../header2.php'; ?>
			</header>

		<!-- Main -->
			<div id="main" class="wrapper">
				<div class="inner">
                    <span class="welcome-title" >Hotel Information</span>
                        <p>For the 2025 event, there will be two hotels used. For the Firday night stay, we are planning to use
                        the Tremont Lodge and Resort in Townsend, Tennessee. For Saturday, we are planning on staying at the Drury
                        Inn and Suites in West Knoxville, Tennessee. For more information and to boook accomidations, use the Information
                        below.</p>                        

                    <!-- Sponsors Section -->
                        <div id="sponsor_info">
                            
                            <div class="row gtr-uniform" style="margin:0.5em 0 2em 0;">
                                <div class="col-4 col-12-small">
                                <h3 style="color:#ff0000;">Tremont Lodge and Resort</h3>
                                    <p style="color: #ffcc00;">7728 E Lamar Alexander Pkwy<br>Townsend, TN 37882<br>(865) 448-3200</p><p>
                                    <strong>NOTE:</strong> The resort fee will be credited back, but the system will not allow it when booking.
                                    </p>
                                </div>
                                <div class="col-8 col-12-small">
                                    <span class="image fit"><img src="images/tremontlodgeandresort.webp"></span>
                                    <br>
                                    <a href="https://hotels.cloudbeds.com/en/reservation/8dZS4x?allotment_block_code=b020986&checkin=2025-07-11&checkout=2025-07-12&currency=usd" class="button primary small fit" target="_blank">Book at the Tremont Lodge and Resort</a>
                                </div>
                            </div>
                            
                            <div class="row gtr-uniform" style="margin:0.5em 0 2em 0;">
                                <div class="col-4 col-12-small">
                                <h3 style="color:#ff0000;">Drury Inn and Suites West Knoxville</h3>
                                    <p style="color: #ffcc00;">209 Advantage Pl, Knoxville<br> TN 37922<br>(865) 693-7277</p>
                                </div>
                                <div class="col-8 col-12-small">
                                    <span class="image fit"><img src="images/druryinnandsuites.webp"></span>
                                    <br>
                                    <a href="https://www.druryhotels.com/bookandstay/newreservation/?groupno=10156611" class="button primary small fit" target="_blank">Book at the Drury Inn and Suites</a>
                                </div>
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
					<?php include '../../contact.php'; ?>
				</div>
			</section>

		<!-- Scripts -->
			<script src="../../assets/js/jquery.min.js"></script>
			<script src="../../assets/js/browser.min.js"></script>
			<script src="../../assets/js/breakpoints.min.js"></script>
			<script src="../../assets/js/jquery.dropotron.min.js"></script>
			<script src="../../assets/js/util.js"></script>
			<script src="../../assets/js/main.js"></script>
			<script src="../../assets/js/mailer.js"></script>
			<script src="../../assets/js/render.js"></script>
			
			<script>
			  $( document ).ready(function() {
				
				// $.ajax({
				// 	url: 'GENERALFunctions.php',
				// 	type: 'POST',
				// 	data: {method: 'BuildDirectionsSections'},
				// 	success: function(data) {
				// 		//alert(data);
				// 		results = eval(data);
				// 		var friday_events = results[0];
				// 		var saturday_events = results[1];
						
				// 		$("#directions_friday_events_container").append(friday_events);
				// 		$("#directions_saturday_events_container").append(saturday_events);	
						
				// 	}
				// });
			  });
			</script>			

	</body>
</html>