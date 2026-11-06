<?php
/**
 * config.example.php
 *
 * Copy this file to config.php and fill in the real values. config.php is
 * git-ignored so database credentials never end up in the repository.
 */

// Database (Bluehost cPanel > MySQL Databases)
const DB_HOST = 'localhost';
const DB_NAME = 'kirbypar_nsx_meetup';
const DB_USER = 'cpaneluser_dbuser';
const DB_PASS = 'change-me';

// Session storage. Point this at a directory OUTSIDE public_html so session
// files can't be fetched over the web, e.g. '/home2/cpaneluser/sessions/nsxmeetup'.
// Leave empty to use PHP's default session path.
const APP_SESSION_PATH = '';

// Where logout sends the user.
const APP_HOME_URL = 'index.php';

// Seconds a logged-in user can be idle before being logged out (optional - defaults to 1800).
const APP_IDLE_TIMEOUT = 300;

// Google reCAPTCHA secret key (server-side verification only - the public site
// key stays in the page markup). https://www.google.com/recaptcha/admin
const RECAPTCHA_SECRET_KEY = 'change-me';
