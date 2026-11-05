<?php
require_once 'SessionBootstrap.php';
app_session_start();
if (($_GET['reason'] ?? '') === 'idle') {
    // Sent here by assets/js/idletimer.js - tell the user why they were logged out.
    app_logout(APP_HOME_URL, 'Your session has expired due to inactivity.');
}
app_logout();
