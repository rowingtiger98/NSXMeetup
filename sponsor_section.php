<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  SPONSOR SECTION                                            */
/*                                                             */
/*  Included by display_event.php and indexsponsor.php to show */
/*  the sponsors for the page's event ($event_id, or           */
/*  ?event_id=), or every active sponsor when there isn't one. */
/*  The sponsors and their logos come from                     */
/*  NEWRenderSponsorInformation() in SPONSORFunctions.php.     */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/SPONSORFunctions.php';

NEWRenderSponsorInformation(isset($event_id) ? (int)$event_id : (int)($_GET['event_id'] ?? 0));
