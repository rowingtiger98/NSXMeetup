<?php
// -------------------------------------------------------------------------------------
// Generic event page. Copy this file to events/<EVENT_Directory>/index.php for a new
// event - everything on it comes from the event's database row (found by the folder's
// name), so it works for any event without changes. An event can still have its own
// banners.php / still_banner.php in its folder; without them the banner shows the
// event's name and dates.
// -------------------------------------------------------------------------------------
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

// -----------------------------------------------------------------------
// Build out the information section on which event is being displayed.
// -----------------------------------------------------------------------
if(isset($_GET['event_id'])) {
    $display_error_message = False;
    $event_id = (int)$_GET['event_id'];
    $event_data = GetEventInformationFromID($event_id);
    $head_title = 'NSX MEETUP - '.$event_data['EVENT_Name'];
    $display_sponsor = $event_data['EVENT_Display_Sponsors'];
} else {
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
$event_name = htmlspecialchars((string)($event_data['EVENT_Name'] ?? ''), ENT_QUOTES);

// The event's dates for the banner, e.g. "December 6 - 8, 2026" or "July 31 - August 2, 2026"
$event_dates = '';
if (!empty($event_data['EVENT_Start_Date'])) {
    $start = strtotime($event_data['EVENT_Start_Date']);
    $end = !empty($event_data['EVENT_End_Date']) ? strtotime($event_data['EVENT_End_Date']) : $start;
    if (date('Y-m-d', $start) === date('Y-m-d', $end)) {
        $event_dates = date('F j, Y', $start);
    } elseif (date('Y-m', $start) === date('Y-m', $end)) {
        $event_dates = date('F j', $start).' - '.date('j, Y', $end);
    } elseif (date('Y', $start) === date('Y', $end)) {
        $event_dates = date('F j', $start).' - '.date('F j, Y', $end);
    } else {
        $event_dates = date('F j, Y', $start).' - '.date('F j, Y', $end);
    }
}

// The Weekend Events button goes live once the event has agenda events to show
$has_agenda = false;
if ($event_id) {
    $conn = connectDB('kirbypar_nsx_meetup');
    $result = PreparedQuery($conn, "SELECT COUNT(*) AS `shown` FROM `nsx_meetup_experiences` WHERE `EVENT_ID` = ? AND `EXPERIENCE_Display` = 1", [(int)$event_id]);
    $has_agenda = $result && ($row = $result->fetch_assoc()) && $row['shown'] > 0;
    closeDB($conn);
}

?>

<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo htmlspecialchars($head_title, ENT_QUOTES); ?></title>
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
            <header id="header" active_page="<?php echo htmlspecialchars((string)$active_page, ENT_QUOTES); ?>" logged_in="<?php echo $logged_in; ?>" class="alt">
            <?php include $header_path; ?>
			</header>

        <!-- Login Modal -->
			<section>
			  <?php include '../../login_modal.php'; ?>
			</section>

		<!-- Banner: the event's own banner files if it has them, otherwise its name and dates -->
            <?php
                if ($active_page == '' && is_file(__DIR__.'/banners.php')) {
                    include 'banners.php';
                } elseif (is_file(__DIR__.'/still_banner.php')) {
                    include 'still_banner.php';
                } else {
            ?>
            <div id="banner" class="event_banner_plain <?php echo ($active_page == '') ? 'active' : 'still'; ?>">
                <article data-position="middle right">
                    <div class="inner">
                        <h1><?php echo $event_name; ?></h1>
                        <p><?php echo htmlspecialchars($event_dates, ENT_QUOTES); ?></p>
                    </div>
                </article>
            </div>
            <?php } ?>

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
                    print NEWRenderAttendingInformation($event_data);
                    break;

                default:
                ?>
                    <!-- Event Stats -->
                    <section id="welcome" class="wrapper opener active">
                            <div id="welcome_letter" class="inner">
                                <span class="welcome-title" ><?php echo $event_name; ?></span>
                                <p><?php echo $event_data['EVENT_Opening_Section']; ?></p>
                            </div>
                        </section>

                    <!-- Event Details -->
                        <section id="details" class="wrapper opener">
                            <div id="details_info" class="inner">
                                <div class="features-grid">
                                    <!-- Hotels and Local Attractions (Add / Edit Hotels and Local Attractions tool) -->
                                    <div>
                                        <span class="number"><span class="icon solid fa-hotel"></span></span>
                                        <h3>Hotels &amp; Local Attractions</h3>
                                        <p>Looking for a place to stay, somewhere to eat or something to do while you're in the area? Click below for hotels and local attractions near the event.</p>
                                        <a href="#" id="render_local_attractions" event_id="<?php echo (int)$event_id; ?>" editable="false" class="button primary small fit">Click Here</a>
                                    </div>
                                    <!-- Schedule (Add / Edit Agenda Events tool) -->
                                    <div>
                                        <span class="number"><span class="icon solid fa-clipboard-list"></span></span>
                                        <h3>Weekend Events</h3>
                                        <p>The weekend will be filled with good people, good drives and good food and drink. For a complete itinerary, click the link below.</p>
                                        <?php if ($has_agenda) { ?>
                                        <a href="?events" class="button primary small fit">Click Here</a>
                                        <?php } else { ?>
                                        <a href="#" class="button small fit">Coming Soon</a>
                                        <?php } ?>
                                    </div>
                                    <!-- Attendees -->
                                    <div>
                                        <span class="number"><span class="icon solid fa-users"></span></span>
                                        <h3>Attendees</h3>
                                        <p>Want to see who is coming to the <?php echo $event_name; ?>? To see who is coming, and where they are coming from, click below to see a full list of drivers that have opted to share that information.</p>
                                        <a href="?attending" class="button primary small fit">Click Here</a>
                                    </div>
                                </div>
                                <ul class="actions special">
                                    <!-- <a href="../../register_all.php?event_id=<?php echo (int)$event_id; ?>" event_id="<?php echo (int)$event_id; ?>" class="button large primary fit">Register Today</a> -->
                                    <a href="#" event_id="<?php echo (int)$event_id; ?>" class="button large primary fit">Opening Soon</a>
                                </ul>
                            </div>
                        </section>

        <?php
                    break;
            }
        ?>

        <?php } ?>

        <div class="inner" style="border-bottom: 1px solid white; margin-top:2em;"></div>

        <!-- SPONSOR SECTION -->
        <?php if ($display_sponsor) { ?>
            <section id="sponsors" class="active" style="margin-top:4em;">
                <div id="sponsor_info" class="inner">
                    <span class="welcome-title" >Thank You to Our Sponsors</span>
                    <?php NEWRenderSponsorInformation($event_id); ?>
                </div>
            </section>
        <?php } ?>

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
