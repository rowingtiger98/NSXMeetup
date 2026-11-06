<?php 
    include_once '../../GENERALFunctions.php';
	include_once '../../CONTACTFunctions.php';
    // print basename(dirname($_SERVER['PHP_SELF']));

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

        $event_data = GetEventInformationFromDirectoryName(basename(dirname($_SERVER['PHP_SELF'])));     
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
		<title><?php echo $head_title; ?> Directions</title>
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
                    <span class="welcome-title" >Directions and Route Map</span>
                        <p>Tentative Route Map: <a href="https://maps.app.goo.gl/DyPnBsZRKs3kxS8e7?g_st=im" target="_blank">CLICK HERE</a></p>
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
					<?php echo RenderContactInformation($event_id); ?>

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
				var elms = document.querySelectorAll('[id^="button_action_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = HandleButtonActionClick;
				}				
			  });
			</script>			

	</body>
</html>
