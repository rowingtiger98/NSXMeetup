<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  ENDPOINT GUARD                                             */
/*                                                             */
/*  Dispatch for the older AJAX endpoints (GENERALFunctions,   */
/*  ACCOUNTManagement, ...). Each one lists the methods its    */
/*  pages call and who may call each:                          */
/*                                                             */
/*    'public'      - anyone                                   */
/*    'registrant'  - the posted driver1 / driver2 were        */
/*                    registered from this browser session, or */
/*                    the user is an admin of the event        */
/*    'self'        - a logged-in user, for their own account  */
/*                    (user_id / email come from the session)  */
/*    'event_admin' - an admin of the posted event_id, or an   */
/*                    overlord                                 */
/*    'admin'       - any logged-in admin or overlord          */
/*    'overlord'    - an overlord                              */
/*                                                             */
/*  Any other posted method name is refused. Methods that      */
/*  change data can also be listed as needing the session's    */
/*  CSRF token (sent by assets/js/csrf.js from the page's      */
/*  csrf-token meta tag).                                      */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/eventAdmin.php';  // SessionBootstrap, databaseFunctions, EventAdminAuthorize(), EventAdminRespond()

/* ************************************************************************ */
/* Function:    EndpointDispatch                                            */
/* Description: Calls the posted method if it's in $methods and the caller  */
/*              is allowed to. Only acts when $file is the script that was  */
/*              requested - not when it's included by another page.         */
/* Inputs:      $file - the endpoint's __FILE__, $methods - name => level,  */
/*              $csrf_methods - the methods that must carry csrf_token.     */
/* ************************************************************************ */
function EndpointDispatch($file, array $methods, array $csrf_methods = []) {
    if (!isset($_POST['method']) || realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== realpath($file)) {
        return;
    }
    $method = $_POST['method'];
    if (!is_string($method) || !isset($methods[$method])) {
        error_log(basename($file).': refused unknown method '.json_encode($method).' from '.($_SERVER['REMOTE_ADDR'] ?? '?'));
        http_response_code(400);
        exit();
    }
    EndpointAuthorize($methods[$method]);
    if (in_array($method, $csrf_methods, true)) {
        app_session_start();
        if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
            EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
        }
    }
    $method();
}

/* ************************************************************************ */
/* Function:    EndpointAuthorize                                           */
/* Description: Ends the request with a 'Fail' response unless the caller   */
/*              meets $level (see the top of this file).                    */
/* ************************************************************************ */
function EndpointAuthorize($level) {
    if ($level === 'public') {
        return;
    }
    app_session_start();

    if ($level === 'event_admin') {
        EventAdminAuthorize(Create_SQL_PHP_DataObject(DB_NAME), (int)($_POST['event_id'] ?? 0));
        return;
    }
    if ($level === 'registrant') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        $registered = array_map('intval', $_SESSION['checkout_participants'][$event_id] ?? []);
        $posted = array_filter([$_POST['driver1'] ?? '', $_POST['driver2'] ?? ''], function ($id) { return (string)$id !== ''; });
        foreach ($posted as $id) {
            if (!in_array((int)$id, $registered, true)) {
                EventAdminAuthorize(Create_SQL_PHP_DataObject(DB_NAME), $event_id); // not theirs - only the event's admins
                return;
            }
        }
        return;
    }

    $user = EndpointLoggedInUser();
    if ($level === 'self') {
        // Their own account only, whatever the page sent
        $_POST['user_id'] = (string)$user['NSX_User_ID'];
        $_POST['email'] = (string)$user['NSX_Email_Address'];
        return;
    }
    if ($level === 'admin' && in_array($user['NSX_User_Role'], ['admin', 'overlord'], true)) {
        return;
    }
    if ($level === 'overlord' && $user['NSX_User_Role'] === 'overlord') {
        return;
    }
    EventAdminRespond('Fail', ['You do not have permission to do that.']);
}

/* ************************************************************************ */
/* Function:    EndpointLoggedInUser                                        */
/* Description: The logged-in, non-idle user's account row. Ends the        */
/*              request with a 'Fail' response if there isn't one.          */
/* Returns:     array - NSX_User_ID, NSX_Email_Address, NSX_User_Role       */
/* ************************************************************************ */
function EndpointLoggedInUser() {
    if (empty($_SESSION['login_user']) || app_session_idle_expired()) {
        EventAdminRespond('Fail', ['Your session has expired. Please log in again.']);
    }
    $_SESSION['last_activity'] = time();

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT NSX_User_ID, NSX_Email_Address, NSX_User_Role FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
    $stmt->execute([$_SESSION['login_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        EventAdminRespond('Fail', ['Your session has expired. Please log in again.']);
    }
    return $user;
}
