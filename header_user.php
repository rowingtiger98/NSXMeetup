<?php require_once __DIR__ . '/SessionBootstrap.php'; // for app_url() ?>
<nav>
    <ul class="nav">
        <li><a href="index.php">Home</a></li>
        <li><a id="flipable_link" href="#details">Information</a></li>
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
            <li><a href="<?php echo htmlspecialchars(app_url('MyAccountInfo.php')); ?>"><span style="color:#ffcc00;">My Account</span></a></li>
            <li><a href="<?php echo htmlspecialchars(app_url('logout.php')); ?>">Log Out</a></li>
        </ul>
</nav>