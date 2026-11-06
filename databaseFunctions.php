<?php
require_once __DIR__ . '/config.php';
/*-------------------------------------------------------------*/
/*                                                             */
/*  DATABASE FUNCTIONS                                         */
/*                                                             */
/*  Single canonical source for DB connection helpers. This    */
/*  file previously existed as near-identical copies inside    */
/*  GENERALFunctions.php, REPORTINGFunctions.php,              */
/*  EVENTCreation.php, GETEventID.php,                         */
/*  LOCALAttractionFunctions.php, crap.php, GF-Backup.php,      */
/*  PARTICIPANTManagement.php, ACCOUNTCreation.php, and         */
/*  database_client.php. Every one of those now does:          */
/*    require_once __DIR__ . '/databaseFunctions.php';         */
/*  instead of redefining these functions itself.              */
/*                                                             */
/*-------------------------------------------------------------*/

/* *********************************************************** */
/* Function:    Create_SQL_PHP_DataObject                      */
/* Description: This function will create a PHP Dataobject in  */
/*              order to help prevent a SQL injection attack.  */
/* Inputs:      $database - the database to connect to.        */
/* Returns:     $PDO - The PHP Data Object.                    */
/* *********************************************************** */
function Create_SQL_PHP_DataObject($database) {
    $host = DB_HOST;
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";

    $PDO = new PDO($dsn, DB_USER, DB_PASS);
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $PDO;
}

/* *********************************************************** */
/* Funciton:    connectDB                                      */
/* Description: This function create a MySQLi object to the    */
/*              database passed in by the caller.              */
/* Inputs:      $database - the database to connect to.        */
/* Returns:     $mysqli - the MySQLi object.                   */
/* *********************************************************** */
function connectDB($database) {

  $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, $database);
  if ($mysqli->connect_errno) {
    echo "Error: Failed to make a MySQL connection, here is why: \n";
    echo "Errno: " . $mysqli->connect_errno . "\n";
    exit;
  }

  return ($mysqli);
}

/* *********************************************************** */
/* Funciton:    selectDB                                       */
/* Description: This function selects the database.            */
/* Inputs:      $DB - The database ID to use.                  */
/* Returns:     Nothing                                        */
/* Note:        Unused anywhere in the codebase, and relies on  */
/*              the mysql_* extension removed in PHP 7 - kept   */
/*              only for parity with the code it replaces.     */
/* *********************************************************** */
function selectDB($DB) {
  $db_selected = mysql_select_db($DB);
  if (!$db_selected) {
    die('Could not select database: ' . mysql_error());
  }
}

/* *********************************************************** */
/* Funciton:    closeDB                                        */
/* Description: This function closes the database in use.      */
/* Inputs:      $DB - The database to close.                   */
/* Returns:     Nothing                                        */
/* *********************************************************** */
function closeDB($DB) {
  $DB->close();
}

/* *********************************************************** */
/* Funciton:    CreateNewTableRow                              */
/* Description: This function inserts a new row and reports    */
/*              success/failure plus the new row's insert ID.  */
/* Inputs:      $database - database to connect to.            */
/*              $table - table to insert into (unused by the   */
/*                        query itself; kept for signature      */
/*                        parity with existing call sites).     */
/*              $query - INSERT query string.                  */
/* Returns:     $res - Results array.                          */
/* *********************************************************** */
function CreateNewTableRow($database, $table, $query) {
  $debug = 0;

  $mysqli = connectDB($database);
  $res = array('success' => NULL, 'id_number' => NULL, 'error_num' => NULL, 'err_message' => NULL);
  if (!$result = $mysqli->query($query))
  {
    $res['success'] = 0;
    $res['id_number'] = 0;
    $res['error_num'] = $mysqli->errno;
    $res['err_message'] = $mysqli->error;
  } else {
    $res['success'] = 1;
    $res['id_number'] = $mysqli->insert_id;
    $res['error_num'] = '';
    $res['err_message'] = 'Success';
  }

  if($debug) {
    print "Create New Table Row Results<br>";
    print " - Success: " . $res['success'] . "<br>";
    print " - Insert ID: " . $res['id_number'] . "<br>";
    print " - Error Number " . $res['error_num'] . "<br>";
    print " - Message " . $res['err_message'] . "<br>";
  }

  closeDB($mysqli);

  return ($res);
}
?>
