<?php
/**
 * Deprecated: these DB helpers now live in databaseFunctions.php.
 * This file is kept as a thin compatibility shim for any include of
 * 'database_client.php' still floating around, and should be retired
 * once nothing references it anymore.
 */
require_once __DIR__ . '/databaseFunctions.php';
