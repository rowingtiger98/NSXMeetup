<?php
/**
 * SessionBootstrap.php
 *
 * Centralized session configuration + lifecycle for the NSX Meetup site.
 * Replaces the copy-pasted session_save_path()/ini_set() blocks previously
 * duplicated across login.php, session.php, logout.php, and register*.php.
 *
 * Usage:
 *   require_once 'SessionBootstrap.php';
 *   app_session_start();          // on every page that touches $_SESSION
 *   app_require_login();          // on pages that require an active login (redirects if idle/absent)
 *   app_login_success($username); // call right after password_verify() succeeds
 *   app_logout();                 // full teardown; used by logout.php
 */
require_once __DIR__ . '/config.php';

const APP_SESSION_NAME   = 'nsxmeetup_session';
const APP_GC_MAXLIFETIME = 7200;  // 2 hours - matches NSXCA's sec_session.php
if (!defined('APP_IDLE_TIMEOUT')) {
    define('APP_IDLE_TIMEOUT', 1800); // idle timeout (seconds) for a logged-in user - override in config.php
}
const APP_GC_PROBABILITY = 1;
const APP_GC_DIVISOR     = 100;   // 1% chance/request - set explicitly rather than left to host default

/**
 * Configures session storage/GC/cookie behavior consistently and starts the session.
 * Safe to call multiple times per request (e.g. from a page that also includes session.php).
 */
function app_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // already started elsewhere this request - avoid double-start warnings
    }

    if (APP_SESSION_PATH !== '') {
        session_save_path(APP_SESSION_PATH);
    }
    ini_set('session.gc_maxlifetime', APP_GC_MAXLIFETIME);
    ini_set('session.gc_probability', APP_GC_PROBABILITY);
    ini_set('session.gc_divisor', APP_GC_DIVISOR);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $params['lifetime'],
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name(APP_SESSION_NAME);
    session_start();
}

/**
 * Call immediately after credentials are verified in login.php.
 * Regenerates the session ID (fixation protection, and drops the old session
 * file) before writing authenticated state into $_SESSION.
 */
function app_login_success(string $username): void
{
    session_regenerate_id(true);
    $_SESSION['login_user']    = $username;
    $_SESSION['login_error']   = false;
    $_SESSION['last_activity'] = time();
}

/**
 * Call at the top of any page that requires an authenticated user.
 * Enforces the idle timeout and bounces anonymous/expired sessions.
 */
function app_require_login(string $redirectTo = 'index.php'): void
{
    if (empty($_SESSION['login_user'])) {
        header('Location: ' . $redirectTo);
        exit();
    }

    if (app_session_idle_expired()) {
        app_logout($redirectTo, 'Your session has expired due to inactivity.');
    }

    $_SESSION['last_activity'] = time();
}

/**
 * The page the login form was posted from (its action is "#", so that's this
 * request's own URL, query string included). Only a path on this site is
 * allowed - anything else, e.g. "//other-site.com", falls back to index.php.
 */
function app_login_return_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if ($uri === '' || $uri[0] !== '/' || (isset($uri[1]) && ($uri[1] === '/' || $uri[1] === '\\'))) {
        return 'index.php';
    }
    return $uri;
}

/**
 * URL of a file in the site's top folder (where this file lives), e.g.
 * app_url('MyAccountInfo.php') gives "/luna/MyAccountInfo.php" when the site is
 * hosted at kirbypark.net/luna/. Use it for links in shared headers, which are
 * included from pages in subfolders (events/<dir>/) as well as the top folder.
 * The site's URL path is worked out from the running script, so it follows the
 * site wherever it's hosted. Falls back to the plain relative path if it can't be.
 */
function app_url(string $path): string
{
    static $base = null;
    if ($base === null) {
        $base = false;
        $root       = realpath(__DIR__);
        $script_dir = realpath(dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $url_dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($root && $script_dir && strpos($script_dir . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR) === 0) {
            $sub = str_replace('\\', '/', substr($script_dir, strlen($root))); // '' or e.g. '/events/TestEvent'
            if ($sub === '') {
                $base = $url_dir;
            } elseif (substr($url_dir, -strlen($sub)) === $sub) {
                $base = substr($url_dir, 0, -strlen($sub));
            }
        }
    }
    return ($base === false) ? $path : $base . '/' . ltrim($path, '/');
}

/**
 * True if the logged-in user has been idle longer than APP_IDLE_TIMEOUT.
 */
function app_session_idle_expired(): bool
{
    return isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > APP_IDLE_TIMEOUT);
}

/**
 * Full teardown: clears session data, expires the cookie client-side, and
 * destroys the server-side session file. Used by logout.php and by
 * app_require_login() on idle timeout.
 */
function app_logout(string $redirectTo = APP_HOME_URL, ?string $message = null): void
{
    $_SESSION = [];

    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );

    session_destroy();

    if ($message !== null) {
        // Stash on a fresh session so the login form can show it once.
        session_start();
        $_SESSION['login_error_description'] = $message;
        $_SESSION['login_error'] = true;
    }

    header('Location: ' . $redirectTo);
    exit();
}

/**
 * The <script> tag for the browser-side idle timer (assets/js/idletimer.js). Echo it
 * just before </body> on any page shown to a logged-in user. The timer logs the user
 * out after APP_IDLE_TIMEOUT seconds with no mouse/keyboard activity, even on pages
 * that never reload (e.g. the AJAX-driven admin tools), and pings session_ping.php
 * while the user is active so the server-side timeout stays in step.
 */
function app_idle_timer_script(): string
{
    return '<script src="assets/js/idletimer.js" data-timeout="' . (int)APP_IDLE_TIMEOUT
         . '" data-ping-url="session_ping.php" data-logout-url="logout.php?reason=idle"></script>' . "\n";
}

/**
 * Returns this session's CSRF token, creating it on first use. Put it in a hidden
 * "csrf_token" field on every form that changes data, and check it with
 * app_csrf_valid() when the form is posted, so other sites can't submit the form
 * using a logged-in user's session.
 */
function app_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function app_csrf_valid(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
