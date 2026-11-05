<?php
/**
 * login.php
 *
 * Included at the top of any page that shows the login panel. Handles the
 * login form POST and sets $error / $loginerrorclass for login_modal.php.
 */
require_once 'SessionBootstrap.php';
include_once 'GENERALFunctions.php';
app_session_start();

$error = ''; // Variable To Store Error Message

// A message left by app_logout() (e.g. idle timeout) - show it once.
if (!empty($_SESSION['login_error_description'])) {
    $error = $_SESSION['login_error_description'];
    unset($_SESSION['login_error_description']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Email and password are required.';
    } else {
        $connection = Create_SQL_PHP_DataObject(DB_NAME);

        $stmt = $connection->prepare('SELECT NSX_Password FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();

        if ($row && is_string($row['NSX_Password']) && password_verify($password, $row['NSX_Password'])) {
            app_login_success($username); // Initializing Session (regenerates session ID)
            header('Location: index.php'); // Redirecting To Other Page
            exit();
        }

        // Same message for unknown user and bad password so usernames can't be probed.
        $error = 'Invalid email or password.';
    }
}

$_SESSION['login_error'] = ($error !== '');
$loginerrorclass = ($error !== '') ? ' errorevent' : ''; // keeps the login panel open to show the error
