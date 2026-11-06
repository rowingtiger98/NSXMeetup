<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT ADMIN                                                */
/*                                                             */
/*  Shared by the event admin tool endpoints                   */
/*  (EVENTSettings.php, REGTYPEManagement.php): the JSON       */
/*  response, the login / event administrator check, and       */
/*  loading the posted event. Also the default registration    */
/*  type every new event starts with (EVENTCreation.php).      */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/SessionBootstrap.php';
require_once __DIR__ . '/databaseFunctions.php';

/* ************************************************************************ */
/* Function:    EventAdminRespond                                          */
/* Description: Sends the JSON response and ends the request.              */
/* ************************************************************************ */
function EventAdminRespond($status, $payload) {
    header('Content-Type: application/json');
    echo json_encode([$status, $payload]);
    exit();
}

/* ************************************************************************ */
/* Function:    EventAdminAuthorize                                        */
/* Description: Ends the request with a 'Fail' response unless the session  */
/*              belongs to a logged-in, non-idle overlord, or an admin who  */
/*              administers the event (listed in                            */
/*              NSX_User_Events_Administered). Uses only the session, never */
/*              browser-sent ids.                                           */
/* Inputs:      $pdo - PHP Data Object, $event_id - the event's id.         */
/* ************************************************************************ */
function EventAdminAuthorize(PDO $pdo, $event_id) {
    if (empty($_SESSION['login_user']) || app_session_idle_expired()) {
        EventAdminRespond('Fail', ['Your session has expired. Please log in again.']);
    }
    $_SESSION['last_activity'] = time();

    $stmt = $pdo->prepare('SELECT NSX_User_Role, NSX_User_Events_Administered FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
    $stmt->execute([$_SESSION['login_user']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $administered = $user ? array_map('trim', explode(',', (string)$user['NSX_User_Events_Administered'])) : [];
    $is_overlord = $user && $user['NSX_User_Role'] === 'overlord';
    $is_event_admin = $user && $user['NSX_User_Role'] === 'admin' && in_array((string)$event_id, $administered, true);
    if (!$is_overlord && !$is_event_admin) {
        EventAdminRespond('Fail', ['You are not an administrator for this event.']);
    }
}

/* ************************************************************************ */
/* Function:    EventAdminLoadEvent                                        */
/* Description: Validates the posted event id, authorizes the user, and     */
/*              returns the PDO connection and event row.                   */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function EventAdminLoadEvent() {
    $event_id = filter_var($_POST['event_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($event_id === false) {
        EventAdminRespond('Fail', ['No event was selected.']);
    }

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    EventAdminAuthorize($pdo, $event_id);

    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_events WHERE EVENT_ID = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        EventAdminRespond('Fail', ['That event could not be found.']);
    }

    return [$pdo, $event];
}

const DEFAULT_REGTYPE_NAME = 'Basic Event Registration';

/* ************************************************************************ */
/* Function:    CreateDefaultRegistrationType                               */
/* Description: Adds the registration type every new event starts with:    */
/*              "Basic Event Registration", $0.00, disabled. The event's    */
/*              admins enable it (and set its cost) when they turn on       */
/*              Collect Payments.                                           */
/* Inputs:      $pdo - PHP Data Object, $event_id - the new event's id.     */
/* ************************************************************************ */
function CreateDefaultRegistrationType(PDO $pdo, $event_id) {
    $stmt = $pdo->prepare('INSERT INTO nsx_meetup_registration_types (REGTYPE_Event_ID, REGTYPE_Name, REGTYPE_Cost, REGTYPE_Description, REGTYPE_Enabled) VALUES (?, ?, 0, NULL, 0)');
    $stmt->execute([$event_id, DEFAULT_REGTYPE_NAME]);
}
