<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT CREATION                                             */
/*                                                             */
/*  Overlord tool for creating a new event: the database       */
/*  record (with its default registration type) and its        */
/*  events/<directory>/ folder. Every request must come from   */
/*  a logged-in overlord and carry the session's CSRF token.   */
/*  Only the methods listed below can be called.               */
/*                                                             */
/*  Responses stay in the format account.js expects:           */
/*  ['success', directory, messages] / ['pass', message], or   */
/*  ['fail', message].                                         */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/SessionBootstrap.php';
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventAdmin.php'; // CreateDefaultRegistrationType()
require_once __DIR__ . '/eventImages.php'; // EventSponsorLogoDirectory()

app_session_start();

const EVENT_CREATION_METHODS = ['EnterNewEventToDatabase', 'CreateNewDirectoryPath'];

const EVENT_NAME_MAX      = 256;  // EVENT_Name is varchar(256)
const EVENT_DIRECTORY_MAX = 32;   // EVENT_Directory is varchar(32)

if (isset($_POST['method']) && in_array($_POST['method'], EVENT_CREATION_METHODS, true)) {
    EventCreationAuthorize();
    $_POST['method']();
}

/* ************************************************************************ */
/* Function:    EventCreationFail                                           */
/* Description: Sends a 'fail' response and ends the request.               */
/* ************************************************************************ */
function EventCreationFail($message) {
    header('Content-Type: application/json');
    echo json_encode(['fail', $message]);
    exit();
}

/* ************************************************************************ */
/* Function:    EventCreationAuthorize                                      */
/* Description: Ends the request unless it comes from a logged-in, non-idle */
/*              overlord and carries the session's CSRF token.              */
/* ************************************************************************ */
function EventCreationAuthorize() {
    if (empty($_SESSION['login_user']) || app_session_idle_expired()) {
        EventCreationFail('Your session has expired. Please log in again.');
    }
    $_SESSION['last_activity'] = time();

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT NSX_User_Role FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
    $stmt->execute([$_SESSION['login_user']]);
    if ($stmt->fetchColumn() !== 'overlord') {
        EventCreationFail('Only an overlord can create events.');
    }

    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventCreationFail('This page has expired. Reload the page and try again.');
    }
}

/* ************************************************************************ */
/* Function:    ValidEventDirectoryName                                     */
/* Description: True if $directory is usable as an events/ folder name:     */
/*              letters, numbers, - and _ only, so it can't point outside   */
/*              the events folder.                                          */
/* ************************************************************************ */
function ValidEventDirectoryName($directory) {
    return is_string($directory) && strlen($directory) <= EVENT_DIRECTORY_MAX && preg_match('/^[A-Za-z0-9_-]+$/', $directory);
}

/* ************************************************************************ */
/* Function:    EventCreationDate                                           */
/* Description: Checks a posted YYYY-MM-DD date.                            */
/* Returns:     the date, null if blank, or false if invalid                */
/* ************************************************************************ */
function EventCreationDate($value) {
    $value = trim(is_string($value) ? $value : '');
    if ($value === '') {
        return null;
    }
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return ($date && $date->format('Y-m-d') === $value) ? $value : false;
}

/* ************************************************************************ */
/* Function:    EnterNewEventToDatabase                                     */
/* Description: Creates the event, its default registration type, and adds */
/*              the event to each listed admin's events administered.      */
/* Returns:     JSON ['success', directory, messages JSON] or               */
/*              ['fail', message]                                           */
/* ************************************************************************ */
function EnterNewEventToDatabase() {
    $event_name      = trim(is_string($_POST['event_name'] ?? null) ? $_POST['event_name'] : '');
    $event_directory = trim(is_string($_POST['event_directory'] ?? null) ? $_POST['event_directory'] : '');
    $start_date      = EventCreationDate($_POST['start_date'] ?? '');
    $end_date        = EventCreationDate($_POST['end_date'] ?? '');
    $event_admins    = is_string($_POST['event_admins'] ?? null) ? $_POST['event_admins'] : '';
    $sponsors        = (($_POST['sponsors'] ?? '') === 'true') ? 1 : 0;  // jQuery sends the checkbox state as 'true' / 'false'
    $event_opening   = is_string($_POST['event_opening'] ?? null) ? $_POST['event_opening'] : '';

    if ($event_name === '' || mb_strlen($event_name) > EVENT_NAME_MAX) {
        EventCreationFail('An event name of up to '.EVENT_NAME_MAX.' characters is required.');
    }
    if (!ValidEventDirectoryName($event_directory)) {
        EventCreationFail('The directory name must be up to '.EVENT_DIRECTORY_MAX.' letters, numbers, dashes or underscores (no spaces).');
    }
    if ($start_date === false || $end_date === false) {
        EventCreationFail('The start and end dates must be valid dates.');
    }
    if ($start_date !== null && $end_date !== null && $end_date < $start_date) {
        EventCreationFail('The end date must be on or after the start date.');
    }

    $dbh = Create_SQL_PHP_DataObject(DB_NAME);

    $stmt = $dbh->prepare('SELECT COUNT(*) FROM nsx_meetup_events WHERE EVENT_Directory = ?');
    $stmt->execute([$event_directory]);
    if ($stmt->fetchColumn() > 0) {
        EventCreationFail('The directory name "'.$event_directory.'" is already used by another event.');
    }

    // ---------------------------------------------------------------------------- //
    // Get the USER NUMBER for each admin and verify that they are valid users.     //
    // ---------------------------------------------------------------------------- //
    $event_admin_array = [];
    $current_admin_events = [];
    $messages = [];

    $stmt = $dbh->prepare('SELECT NSX_User_ID, NSX_Username, NSX_User_Events_Administered FROM nsx_meetup_user_accounts WHERE NSX_Username = ?');
    foreach (explode(',', $event_admins) as $value) {
        $value = trim($value);
        if ($value === '') {
            continue;
        }
        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $event_admin_array[$row['NSX_User_ID']] = $row['NSX_Username'];
            $current_admin_events[$row['NSX_Username']] = $row['NSX_User_Events_Administered'];
            $messages[] = 'The user '.$value.' has been added as an adminstrator for the event.';
        } else {
            $messages[] = 'The email address '.$value.' was not found to be an active NSX Meetup registered user.';
        }
    }

    try {
        // The event, its default registration type and the admins' event lists - all or nothing
        $dbh->beginTransaction();

        $insert = $dbh->prepare('INSERT INTO `nsx_meetup_events` (`EVENT_Name`, `EVENT_Directory`, `EVENT_Start_Date`, `EVENT_End_Date`, `EVENT_Admins`, `EVENT_Participants`, `EVENT_Display_Sponsors`, `EVENT_Opening_Section`) VALUES (?, ?, ?, ?, ?, \'\', ?, ?)');
        $insert->execute([$event_name, $event_directory, $start_date, $end_date, json_encode($event_admin_array), $sponsors, $event_opening]);
        $new_event_id_number = $dbh->lastInsertId();

        CreateDefaultRegistrationType($dbh, $new_event_id_number);

        // ------------------------------------------------------------
        // Update User informtaion for events they are admins for
        // ------------------------------------------------------------
        $update = $dbh->prepare('UPDATE `nsx_meetup_user_accounts` SET `NSX_User_Events_Administered` = ? WHERE `NSX_Username` = ?');
        foreach ($current_admin_events as $username => $administered) {
            $events = ($administered === null || trim($administered) === '') ? [] : array_map('trim', explode(',', $administered));
            $events[] = (string)$new_event_id_number;
            $update->execute([implode(',', $events), $username]);
        }

        $dbh->commit();
    } catch (PDOException $e) {
        if ($dbh->inTransaction()) {
            $dbh->rollBack();
        }
        error_log('EnterNewEventToDatabase failed: '.$e->getMessage());
        EventCreationFail('The event could not be created. Please try again.');
    }

    header('Content-Type: application/json');
    echo json_encode(['success', $event_directory, json_encode($messages)]);
}

/* ************************************************************************ */
/* Function:    CreateNewDirectoryPath                                      */
/* Description: Creates events/<new_path>/, with its images/sponsors/      */
/*              folder for sponsor logos and an index.php copied from       */
/*              templates/event_index.php, for an event that's already in   */
/*              the database (EnterNewEventToDatabase runs first). An       */
/*              index.php that's already there is left alone.               */
/* Returns:     JSON ['pass', message] or ['fail', message]                 */
/* ************************************************************************ */
function CreateNewDirectoryPath() {
    $new_path = is_string($_POST['new_path'] ?? null) ? trim($_POST['new_path']) : '';
    if (!ValidEventDirectoryName($new_path)) {
        EventCreationFail('That is not a valid event directory name.');
    }

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM nsx_meetup_events WHERE EVENT_Directory = ?');
    $stmt->execute([$new_path]);
    if ($stmt->fetchColumn() == 0) {
        EventCreationFail('No event uses the directory "'.$new_path.'".');
    }

    $directory_path = __DIR__.'/events/'.$new_path;
    if (!is_dir($directory_path) && !mkdir($directory_path, 0755)) {
        error_log('CreateNewDirectoryPath: could not create '.$directory_path);
        EventCreationFail('The folder events/'.$new_path.'/ could not be created.');
    }
    $sponsor_path = __DIR__.'/'.EventSponsorLogoDirectory($new_path);
    if (!is_dir($sponsor_path) && !mkdir($sponsor_path, 0755, true)) {
        error_log('CreateNewDirectoryPath: could not create '.$sponsor_path);
        EventCreationFail('The folder events/'.$new_path.'/ was created, but its images/sponsors/ folder could not be.');
    }
    $index_path = $directory_path.'/index.php';
    if (!file_exists($index_path) && !copy(__DIR__.'/templates/event_index.php', $index_path)) {
        error_log('CreateNewDirectoryPath: could not copy templates/event_index.php to '.$index_path);
        EventCreationFail('The folder events/'.$new_path.'/ was created, but its index.php page could not be copied from templates/event_index.php.');
    }

    header('Content-Type: application/json');
    echo json_encode(['pass', 'Created events/'.$new_path.'/ with its index.php page and images/sponsors/ folder']);
}
