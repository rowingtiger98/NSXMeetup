<?php require_once __DIR__ . '/SessionBootstrap.php'; // for app_url() ?>
<nav>
					<ul class="nav">
						<!-- <li><a href="index.php">Home</a></li> -->
						<!-- <li><a id="flipable_link" href="#details">Information</a></li> -->
						<!-- <li><a href="#photosection">Photos</a></li> -->
                        <li><a href="#contact">Contact</a></li>
                        <!-- <li><a href="pictures.php">Pictures</a></li> -->
					</ul>
				</nav>
				<div id="logo">
					<a href="index.php">
						<span class="image fit"><img src="<?php echo htmlspecialchars(app_url('assets/images/NSXMeetup-Logo-white.png')); ?>" alt="" /></span>
					</a>
				</div>
				<nav>
					<ul class="nav">
						<!-- <li><a href="pictures.php">Pictures</a></li> -->						
						<li><a id="login" href="#loginPanel">Log in</a></li>
					</ul>
				</nav>