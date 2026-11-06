<?php require_once __DIR__ . '/SessionBootstrap.php'; // for app_url() ?>
<nav>
    <ul class="nav">
        <li><a href="register.php">Register</a></li>
        <li><a href="#contact">Contact</a></li>
        <li><a href="<?php echo htmlspecialchars(app_url('MyAccountInfo.php')); ?>"><span style="color:#ffcc00;">My Account</span></a></li>
        <li><a href="<?php echo htmlspecialchars(app_url('logout.php')); ?>">Log Out</a></li>
    </ul>
</nav>