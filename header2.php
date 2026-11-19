<?php require_once __DIR__ . '/SessionBootstrap.php'; // for app_url() ?>
				<nav>
					<ul class="nav">
						<li><a href="<?php echo htmlspecialchars(app_url('index.php')); ?>">Home</a></li>  <!-- the main site, also from an event's pages -->
					</ul>
				</nav>
				<div id="logo">
					<a href="index.php">
						<span class="image fit"><img src="<?php echo htmlspecialchars(app_url('assets/images/NSXMeetup-Logo-white.png')); ?>" alt="" /></span>
					</a>
				</div>
				<nav>
					<ul class="nav">
                    <li><a href="#contact">Contact</a></li>
					</ul>
				</nav>