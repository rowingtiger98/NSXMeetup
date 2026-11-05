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
		<title>NSX Meetup - River NSX</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
    <style>
* {box-sizing: border-box}
body {font-family: Verdana, sans-serif; margin:0}
.mySlides {display: none}
img {vertical-align: middle;}

/* Slideshow container */
.slideshow-container {
  max-width: 1000px;
  position: relative;
  margin: auto;
}

/* Next & previous buttons */
.prev, .next {
  cursor: pointer;
  position: absolute;
  top: 50%;
  width: auto;
  padding: 16px;
  margin-top: -22px;
  color: white;
  font-weight: bold;
  font-size: 18px;
  transition: 0.6s ease;
  border-radius: 0 3px 3px 0;
  user-select: none;
  background-color: rgba(0,0,0,0.8);
}

/* Position the "next button" to the right */
.next {
  right: 0;
  border-radius: 3px 0 0 3px;
}

/* On hover, add a black background color with a little bit see-through */
.prev:hover, .next:hover {
  background-color: rgba(0,0,0,0.8);
}

/* Caption text */
.text {
  color: #f2f2f2;
  font-size: 15px;
  padding: 8px 12px;
  position: absolute;
  bottom: 8px;
  width: 100%;
  text-align: center;
}

/* Number text (1/3 etc) */
.numbertext {
  color: #f2f2f2;
  font-size: 12px;
  padding: 8px 12px;
  position: absolute;
  top: 0;
}

/* The dots/bullets/indicators */
.dot {
  cursor: pointer;
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  transition: background-color 0.6s ease;
}

.active, .dot:hover {
  background-color: #717171;
}

/* Fading animation */
.fade {
  animation-name: fade;
  animation-duration: 1.5s;
}

@keyframes fade {
  from {opacity: .4} 
  to {opacity: 1}
}

/* On smaller screens, decrease text size */
@media only screen and (max-width: 300px) {
  .prev, .next,.text {font-size: 11px}
}
</style>


	<body class="is-preload">

		<!-- Header -->
            <header id="header" class="alt">
            <?php 
                if($is_logged_in){
                    include 'header_user_simple.php';
                 } else {
                    include 'header_simple.php';
                 }        
			?>			  
			</header>
	
			
        <!-- Login Modal -->	
			<section>
			  <?php include 'login_modal.php'; ?>
			</section>
			

        <!-- TITLE -->
            <section id="title" class="wrapper" style="margin-top:6em;">
				<div id="details_info" class="inner" style="text-align:center;">
                <h1 style="margin:0 0 .125em 0 !important;letter-spacing: 0.025em;border-bottom:1px solid white;">The River NSX</h1>
                <span style="font-size:1.25em;color:#ffcc00;">The Cheapest NSX Ever Sold? $8,500! Here’s Why</span>
				</div>

        <!-- Article -->
                <div class="inner" style="margin-top: 2em;" >
                 <p> Who has ever heard of an NSX selling for $8,500 in the 21st century?  Anybody? Nope!  – Not until a stolen NSX which sat in 
                    the bottom of the Yadkin River (North Carolina) for 15+ years 
                    sold to <a href="https://www.facebook.com/HelixAutoWorks/" target="_blank" style="color:#ff0000;">
                        Helix Auto Works</a> in Maryland.
The ‘River NSX’ has gone viral in just over a week after <a href="https://www.facebook.com/NSXGIRL/posts/223973706812571" target="_blank" style="color:#ff0000;">Becca Nicole Johnson</a> 
posted about the incredible find on her <a href="https://www.facebook.com/NSXGIRL/posts/223973706812571" target="_blank" style="color:#ff0000;">Facebook</a>.  
With nearly 800 shares, it sent the NSX into an online-frenzy with car enthusiasts all around the world!
The backstory for the infamous car is that it was accidentally found in 2019 during a completely unrelated 
<a href="https://www.youtube.com/watch?v=cAfYtXB-qN4" target="_blank" style="color:#ff0000;">missing person’s investigation</a> and was also pulled from the river!</p>
<p>
As one can imagine, the poor JDM Legend has seen better days.  Who could be a better group to bring this NSX back to its previous glory than Helix Auto Works! It’s not going to be an easy task, but it sure is going to be a fun challenge!
                </p><p>
Do you want to keep an eye on the ‘River NSX’ Saga?  Make sure to follow Helix Auto Works on Facebook and Instagram to stay up to date.  You don’t want to miss a beat in this badass project! 
                </p>
<div style="text-align:right;font-style:italic;color:#ffcc00;">"We’re gonna try to either restore it or build a fun shop car we take to the track!” – Jay Bozeman</div>
                </div>
                </section>
               
        <!-- Image Gallery -->
            <section>

            <div class="slideshow-container">
                <?php
                    if ($handle = opendir('images/rivernsx')) {
                        $i = 0;
                        
                        while( ($file = readdir($handle)) !== false ) {
                            if( !in_array($file, array('.', '..')) && !is_dir($dir.$file))
                                $i++;
                        } 
                        closedir($handle);
                    }
                    $counter = 1;
                    if ($handle = opendir('images/rivernsx')) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry != "." && $entry != "..") {
                                print '<div class="mySlides fade">';
                                print '<div class="numbertext">'.$counter.' / '.$i.'</div>';
                                print '<img src="images/rivernsx/'.$entry.'" style="width:100%">';
                                print '</div>';
                                $counter++;
                            }
                            
                        }                      
                        closedir($handle);
                    }



                print '<a class="prev" onclick="plusSlides(-1)">❮</a>';
                print '<a class="next" onclick="plusSlides(1)">❯</a>';
                print '<br><br>';
                $dot_counter = 1;
                print '<div style="text-align:center">';
                while($dot_counter < $counter) {
                    print '<span class="dot" onclick="currentSlide('.$dot_counter.')"></span>';
                    $dot_counter++;
                } 
                print '</div>';
   
                ?>    
                <br><br>        
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
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {slideIndex = 1}    
  if (n < 1) {slideIndex = slides.length}
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";  
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex-1].style.display = "block";  
  dots[slideIndex-1].className += " active";
}
</script>			
	</body>
</html>