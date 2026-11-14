<?php
// include('GETEventID.php'); // Includes Login Script    
include('../../login.php'); // Includes Login Script
// Resolve the logged-in user before any HTML is sent: session.php redirects (and stops)
// when the session has idled out, which only works before output starts.
if (isset($_SESSION['login_user'])) {
    include '../../session.php';
}
include_once '../../AGENDAFunctions.php';
include_once '../../ATTENDINGFunctions.php';
include_once '../../CONTACTFunctions.php';
include_once '../../SPONSORFunctions.php';

$current_page_url = GetFullURL();
$page_url = parse_url($current_page_url, PHP_URL_QUERY);
// print '<br>PAGE URL PARAMETER = '.$page_url.'<br>';
// $event_directory = GetEventWorkingDirectory();
// print '<br>Event Directory = '.$event_directory.'<br>';

if($_SESSION['login_error']){
	$loginerrorclass = ($_SESSION['login_error']) ? ' errorevent' : '';
    // print ("LOGIN ERROR" . $error);
    // print_r($_SESSION);

} else {
	$loginerrorclass = '';
    // print ("NO LOGIN ERROR");
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
    // $event_data = GETEventID(basename($_SERVER['REQUEST_URI']));
    $event_data = GetEventInformationFromDirectoryName(GetEventWorkingDirectory());     
    if (empty($event_data)) {
        $display_error_message = True;
        $head_title = 'NSX MEETUP - Unknown Event ID';
        $display_sponsor = False;
        $event_id = 0;       
    } else {

        $event_id = $event_data['EVENT_ID'];
        $display_error_message = False;
        $head_title = 'NSX MEETUP - '.$event_data['EVENT_Name'];
        $display_sponsor = $event_data['EVENT_Display_Sponsors'];        
    }
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
		<link rel="stylesheet" href="../../assets/css/main.css" />
		<link rel="stylesheet" href="../../assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">

    <?php 
        // ---------------------------------------------------------------------------------- //
        // With the addition of the multiple pages rendered from the same index page, use the //
        // header section to include the page ID and also include if the user is logged in or //
        // not. This will be used to set click handlers for various different pages.          //
        // ---------------------------------------------------------------------------------- //

        if(isset($_SESSION['login_user'])){
            $logged_in = 'yes';
            $header_path = '../../header_user.php';
            $active_page = parse_url($current_page_url, PHP_URL_QUERY);
        } else {
            $logged_in = 'no';
            switch(parse_url($current_page_url, PHP_URL_QUERY)) {
                case('events'):
                    $header_path = '../../header2.php';
                    $active_page = 'events';
                    break;

                case('attending'):
                    $header_path = '../../header2.php';
                    $active_page = 'attending';                    
                    break;

                default:
                    $header_path = '../../header.php';
                    $active_page = '';                
                    break;    
            }            
        }      
    ?>
		<!-- Header -->
            <header id="header" active_page="<?php echo $active_page; ?>" logged_in="<?php echo $logged_in; ?>" class="alt">
            <?php 
                include $header_path;
                // if(isset($_SESSION['login_user'])){
                //     include '../../session.php';
                //     include '../../header_user.php';
                //  } else {
                //     include '../../header.php';
                //  }        
			?>			  
			</header>
	
			
        <!-- Login Modal -->	
			<section>
			  <?php include '../../login_modal.php'; ?>
			</section>
			
		<!-- Banner -->
            <?php 
                if($active_page == '') {
                    include 'banners.php';
                } else {
                    include 'still_banner.php';
                }
            ?>

        <?php if($display_error_message) { ?>
            <!-- Event Stats -->            
                <section id="welcome" class="wrapper opener active">
                    <div id="welcome_letter" class="inner">
                        <span class="welcome-title" >NSX Meetup - Invalid Event</span>
                        <p>Oh No! The event that you are hoping to see is returning an error when we try to render the information about the event.
                        If you are seeing this error, please drop an email to <a href="mailto:ron@nsxmeetup.com">ron@nsxmeetup.com</a>.</p>

                    </div>                
                </section>        
        <?php } else { 
            switch(parse_url($current_page_url, PHP_URL_QUERY)) {
                case('events'):
                    print NEWRenderAgendaInformation($event_data);
                    break;
                
                case('attending'):
                    print NEWRenderAttendingInformation($event_data);;
                    break;

                default:
                ?>
                    <!-- Event Stats -->
                    <section id="welcome" class="wrapper opener active">
                            <div id="welcome_letter" class="inner">
                                <span class="welcome-title" ><?php echo $event_data['EVENT_Name']; ?></span>
                                <p><?php echo $event_data['EVENT_Opening_Section']; ?></p>
                            </div>                
                        </section>

                    <!-- Event Details -->
                        <section id="details" class="wrapper opener">
                            <div id="details_info" class="inner">
                                <div class="features-grid">
                                    <div>
                                        <span class="number"><span class="icon solid fa-hotel"></span></span>
                                        <h3>Hotel Information</h3>
                                        <p>The event does not have an official hotel or venue, but if you want to stay over night, Click below to see hotels near the Red Deer Acura dealership.</p>
                                        <a href="?attractions=hotels" id="render_local_attractions" event_id="<?php echo (int)$event_id; ?>" editable="false" category="hotels" class="button primary small fit">Find A Room</a>
                                        <!-- <a href="#" class="button  small fit">Coming Soon</a> -->

                                    </div>
                                    <div>
									    <span class="image fit"><img src="images/RedDeerFrontPage.png"></span>
                                    </div>
									
                                    <!-- Attendees -->
                                    <div>
                                        <span class="number"><span class="icon solid fa-users"></span></span>
                                        <h3>Attendees</h3>
                                        <p>Want to see who is coming to the <?php echo $event_data['EVENT_Name']; ?>? To see who is coming, and where they are coming from, click below to see a full list of drivers that have opted to share that information.</p>
                                        <a href="?attending" class="button primary small fit">Click Here</a>
                                        <!-- <a href="#" class="button  small fit">Coming Soon</a> -->
                                    </div>  

                                    <!-- Empty -->
                                    <!-- <div>
                                    </div> -->

                                    <!-- Directions -->
                                    <!--<div>
                                        <span class="number"><span class="icon solid fa-map"></span></span>
                                        <h3>Directions</h3>
                                        <p>For route information, click the link below.</p>
                                        <a href="directions.php" class="button primary small fit">Click Here</a>
                                        <a href="#" class="button  small fit">Coming Soon</a>
                                    </div> -->

                                    <!-- Empty -->
                                    <!-- <div>
                                    </div> -->
                                </div>
                                <ul class="actions special">
                                    <a href="../../register_all.php?event_id=<?php echo $event_id; ?>" event_id="<?php echo $event_id; ?>" class="button large primary fit">Register Today</a>
                                    <!-- <a href="#" class="button  small fit">Registration Opening Soon</a> -->
                                </ul>
                            </div>
                        </section>
									<section id="hangout" class="active" style="margin:1em 0 4em 0;">
				<div class="inner">
					<span class="welcome-title" >Christopher Cutler (NSXTRA)</span>				
					<div class="row gtr-uniform">
						<div class="col-3 col-12-small" style="padding-top:3.25em;">
							<span class="image fit"><img src="images/chriscutfrontpage.jpg"></span>
						</div>
						<div class="col-9 col-12-small" style="padding-top:1.6em;">
							<p style="padding: 1.25em 0 0 0 ;">On April 1st, 2024, the NSX community lost a truly inspirational member, but more so, a friend to all that knew him. Borrowing from Sheraz Bhutta's well said words, "Christopher 
							impacted every life he walked past during his time in this world. He was very empathetic, understanding, loving, would take the shirt off his back for anyone and had a heart of gold." In honor of Chris, A Chris Cut NSXtra die cast will be given away to the peoples choice car.</p>
						</div>
					</div>	
				</div>
			</section> 
                     
        <?php
                    break;
            }            
        ?>

        <?php } ?>            

        <div class="inner" style="border-bottom: 1px solid white; margin-top:2em;"></div>


        <div class="inner" style="border-bottom: 1px solid white"></div>

        <!-- SPONSOR SECTION -->
        <?php if ($display_sponsor) { ?>
            <section id="sponsors" class="active" style="margin-top:4em;">
                <div id="sponsor_info" class="inner">
                    <span class="welcome-title" >Thank You to Our Sponsors</span>                    <br><br>
                    <?php NEWRenderSponsorInformation($event_id); ?>

                </div>
            </section>
        <?php } ?>

        <div class="inner" style="border-bottom: 1px solid white"></div>

		<!-- NSXCA Membership Section -->
            <section id="welcome" class="active">
				<div id="welcome_letter" class="inner">
                    <div class="row gtr-uniform">
                    	<div class="col-12 col-12-small">
							<span class="image fit"><img src="../../assets/images/NSXCA-Logo-white.png"></span>
						</div>
						<div class="col-12 col-12-small">
							<a href="https://nsxca.org/product/nsxca-membership/" target="_blank" class="button fit" style="margin-bottom:1em;">JOIN THE NSX Club of America Today</a>
							<p>Membership is available in one, two, and three-year terms (discounts applied for two and three-year terms). Memberships can be purchased on the NSX Club of America website by <a href="https://nsxca.org/product/nsxca-membership/" target="_blank">clicking here</a></p>
							<h3>Your membership includes</h3>
							<ul id="membership_benefits_list" name="membership_benefits_list" style="list-style:circle;margin-bottom:3em;">
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
                    <?php print RenderContactInformation($event_id); ?>
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
                var active_page = $("#header").attr('active_page');
				console.log("Active Page  =", $("#header").attr('active_page'));  

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
                
                $("#render_local_attractions").on('click', function(event) {
                    event.preventDefault();
                    RenderLocalAttractionsPage.call(this, event);
                });

                // index.php?attractions opens the Hotels & Local Attractions page straight away, and
                // index.php?attractions=<category> opens it on that category, so it can be linked to
                var url_params = new URLSearchParams(window.location.search);
                if (url_params.has('attractions') && $("#render_local_attractions").length) {
                    var attractions_link = $("#render_local_attractions");
                    if (url_params.get('attractions') !== '') {
                        attractions_link.attr('category', url_params.get('attractions'));
                    }
                    RenderLocalAttractionsPage.call(attractions_link[0]);
                }

                if (active_page == 'events') {
                    var elms = document.querySelectorAll('tr[id^="event_info_row"]');
                    for (i = 0; i < elms.length; i++) {
                        elms[i].onmouseover = DisplayDailyEventInformation;
                        elms[i].onclick = DisplayDailyEventInformation;
                    }
                }
                
                if (active_page == 'attending') {
                    var elms = document.querySelectorAll('[id^="more_participant_info_link_"]');
                    for (i = 0; i < elms.length; i++) {
                        elms[i].onclick = ShowParticipantExtraInformation;
                    }	                
                }

			  });          

			  
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
