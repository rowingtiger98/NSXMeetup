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
		<title>NSX Meetup - NSX-R</title>
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
                <h1 style="margin:0 0 .125em 0 !important;letter-spacing: 0.025em;border-bottom:1px solid white;">1993 Honda NSX-R</h1>
                <span style="font-size:1.25em;color:#ffcc00;">The Record Setting Bring-A-Trailer Auction</span>
				</div>

        <!-- Article -->
                <div class="inner" style="margin-top: 2em;" >
                <p>It all started over a 1994 Brooksland Green NSX.  I bought my first NSX in 1998.  It was a ’94 Brooklands Green NSX.  
                    My neighbor came running across the street and said “my boss has one of these, you need to meet him”.  Low and behold, 
                    his boss was none other than Dr. Wei-Shen Chin.  We met, had dinner and became lifelong friends.  We started Chin 
                    Motorsports Inc in 2000 and embarked on the joys of running the “now largest” track day organization in the US.  
                    Our track weapon of choice in the early days, my 94 NSX and Wei-Shen’s 94 NSX.  Then we moved into Formula Mazdas, 
                    Spec Miatas, a fully race prepped NSX, more Miatas, a few Lotus Exiges, and then another NSX (a 2017 Valencia Red Pearl). 
                     We had grown to love the cars and people attached to them. <br><br>
                     
                     In 2001, I sold my 94 Green NSX and bought #17 99 Zanardi NSX (still have this one).  This car was recommended to me by 
                     Wei-Shen, as he knew I was looking for a 6 speed coupe (I think he regrets not buying it himself 😊) .  Fast forward 16 
                     years and my kids college plans were now fully funded, my main job (I own a CPA firm) was flourishing and I decided it was 
                     time to begin building my dream JDM Gran Turismo garage.  Yes, I was a video game junkie and almost every car in my real garage 
                     today, was in my GT garage.  In 2016, I went on the hunt for a low mileage, all original 1999 Nissan GTR that was eligible for 
                     show and display.  In late 2017, we found one and I purchased it.  Knowing full well that I could not import it until 2020 to 
                     avoid the EPA requirements.  About 3 weeks later, my supplier in England that I had given my “wish list” of cars, calls me and 
                     said he had a car that I would definitely want.  I had just bought the 99 GTR and had only half the cash. So what do you do in 
                     that instance, you phone a friend.  I called Wei-Shen with an idea.  What if Chin and I put our money together and form an LLC, 
                     we can buy more toys.  So we formed CMS Dream Garage LLC and bought our first car together in this entity, a 1998 Subaru 22B.  
                     Then the world opened up, Chin isn’t a Nissan guy, so I bought all the Skyline GTRs, but he and I purchased a 98 Integra Type R, 
                     a 2021 Civic Type R LE, 2022 NSX Type S, 1997 Toyota Supra 6 spd, 1995 BMS M3 Lightweight and a few others still in Japan. <br><br> 
 

                    In January 2022 I traveled to London to visit Harlow Jap Autos to see a few of our cars that were there and Torque GT.  
                    They opened up the showroom and Torque GT had a 93 white NSX-R.  I tried to make a deal, but it was a no go.  Chin and tried to buy 
                    several other NSX-Rs over he past few years and either couldn’t find the right quality for the right price.  Prior to the BaT auction, 
                    I was thinking our hopes of finding a NSX-R might not happen for some time. <br><br>

                    Fast forward to October 2022, I get from the prior year NY car show that Matthew Ivanhoe, the Cultivate Collector, has acquired a 
                    few special JDM cars and they are coming to BringaTrailer.  I would have loved to been able to make a deal prior to the auction, but 
                    that was not in the cards.  So, I call Wei-Shen, tell him there are 3 cars in the lot that I want to purchase.  By the way, Chin and 
                    my agreement is that I do all the research and pricing to determine if feasible, then he splits the cost.  Simple, no fuss deal.  
                    I showed him the auction for the 93 NSX R, the 90 Nissan Pulsar GTI R Nismo and the GTR V Spec II N1.  Again, Chin isn’t a GTR guy so 
                    that one I may have to buy solo.  Big question – what are these cars going to go for?  I emailed Wei-Shen the link and he calls me and 
                    says “you know the car is GREEN.  I want a white one.”  So we banter back and forth and I explained that I believe this is one of the 6 
                    green with black roof cars and is likely the only NSX-R in this color combo.  He agrees and then reminds me the car is green and he really
                     doesn’t care for green.  Me on the other hand, my first NSX was green so this was like walking down memory lane. <br><br>

                    So for the 10 days coming up to the end of the auction, I am bidding, lurking, playing, testing the other buyers, seeing who might be my 
                    competition, and formulating with Wei-Shen what I think the final bid price might be.  Then a huge wrinkle gets thrown into the mix.  
                    The final day of auction is October 12, 2022.  Why did this day sound familiar.  Why was I marked off from work on my calendar that day?  
                    Surely I wouldn’t have marked the day off just for the auction (or would I)?  A few days before, my wife reminds me I am to take her to the 
                    hospital because she is having surgery on the 12th.  Oh no!!!  I can’t tell her I forgot, but I also can’t miss this auction.  
                    Wei-Shen is a no-go to help with showedthe auction as this is my part of the agreement to play the field.  So off I go to the hospital with 
                    my wife at 6am with laptop, cellphone, charger and ipad – just to be safe.  There is a hustle around the prep room as they had a patient 
                    that had major complications in surgery we found out later.  The whole time I am watching the clock, thinking let me just kiss my wife, 
                    wish her a happy sleep and get to the waiting room where there is a WiFi signal so I can get back into the auction.  The doc comes in about
                     9:30am and says there is a little boy in the room next to us that his surgery will be fast and did we mind if he took the child first. 
                        I’m thinking, YES!!!  Take the kid, this will delay her surgery another 45 minutes which she should still be asleep when I’m bidding
                         on the NSX.  Was this wrong? <br><br> 

                    They finally take the wife back for surgery, I give her a kiss and she smiled and said, you are going to be bidding while I’m out 
                    aren’t you?  Shyly I answered, “maybe, but I will be here when you wake up”!  Out to the waiting room I run and I set up BAT central
                     in the corner of the waiting room.  Laptop – check!  Iphone – check! IPad – check!  Hotspot – check!  Let the bidding begin.  Back and 
                     forth we go.  Bid won, bid lost.  Hey, there is a new player and oh crap, I know this bidder – they have money.  At 1:05 pm, 
                     I had reached my hard limit (or what I thought was hard limit) on the car.  I called Wei-Shen and told him we are not going to get
                      this under $280k, but I think we are close.  He asked if I thought it was worth it – of course I did and he agreed to go for it. 
                       Just then the nurse walks into the room and calls for me to tell me my wife is out of surgery and in recovery.  
                       All I could say to the nurse was “Well is she awake yet?  Please come get me when she is awake. I’m bidding on a rare car”. 
                        Looking back, I am very glad my wife wasn’t there or they were not recording that comment as I might not be alive to tell this tale. 
                         At 1:16pm I entered the final bid and watched the count down timer while grinding my teeth.  3-2-1  - checking for any other bids!!!!!  
                         Then the message we all yearn to see”  SOLD on 10/12/22 for $XXXXXX to LapRat .  What?  We won?  Soupysales failed to get his last bid 
                         in before the timer ran out as he was bidding on another car.  I’m in shock.  We actually won the 1/1 Brooklands Green NSX-R.  
                         THIS IS SO AWESOME!  I FEEL 12 AGAIN.  And just as broke now as I was when I was 12.  I texted Wei-Shen, while I was running into the
                          recovery room to kiss my beautiful wife.  She was awake when I arrived at her bedside.  In true NSX owner fashion she asked, 
                          so did you get it?  The stupid smile on my face was all the evidence she needed.  I kissed her on the cheek and told her there
                           was one more car that I was bidding on and the recovery room had no WiFi.  So for the next 20 minutes I ran back and forth between the 
                           waiting room and recovery room to win the 90 GTI R Nismo.  Needless say, we were the talk of the nurses and doctors.  Wife was fine,
                            NSX-R was on its way to my garage – all the stars had aligned and we were happy.</p>                  
                    
                                To see all the pictures from the original auction, <a href="https://bringatrailer.com/listing/1993-honda-nsx-r/" target="_blank" style="color:#ff0000;">click here</a>.
                </div>
                </section>
               
        <!-- Image Gallery -->
            <section>

            <div class="slideshow-container">
                <?php
                    if ($handle = opendir('images/nsxr')) {
                        $i = 0;
                        
                        while( ($file = readdir($handle)) !== false ) {
                            if( !in_array($file, array('.', '..')) && !is_dir($dir.$file))
                                $i++;
                        } 
                        closedir($handle);
                    }
                    $counter = 1;
                    if ($handle = opendir('images/nsxr')) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry != "." && $entry != "..") {
                                print '<div class="mySlides fade">';
                                print '<div class="numbertext">'.$counter.' / '.$i.'</div>';
                                print '<img src="images/nsxr/'.$entry.'" style="width:100%">';
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