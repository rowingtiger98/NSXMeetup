<?php
/**
 * session_ping.php
 *
 * Called by assets/js/idletimer.js while a logged-in user is active on a page,
 * so a user working on one page (e.g. filling in a long admin form) isn't timed
 * out by the server. Returns {"status":"ok"} and refreshes last_activity, or
 * {"status":"expired"} if the session is gone or already idle-expired.
 */
require_once 'SessionBootstrap.php';
app_session_start();

header('Content-Type: application/json');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['login_user']) || app_session_idle_expired()) {
    echo json_encode(['status' => 'expired']);
    exit();
}

$_SESSION['last_activity'] = time();
echo json_encode(['status' => 'ok']);
