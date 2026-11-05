<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT IMAGES                                               */
/*                                                             */
/*  Where an event's images live: events/<EVENT_Directory>/    */
/*  images/. Images shared by every event (such as the         */
/*  default front page image) stay in                          */
/*  assets/images/frontpage/.                                  */
/*                                                             */
/*-------------------------------------------------------------*/

const SHARED_FRONTPAGE_IMAGE_DIR = 'assets/images/frontpage';  // relative to the site folder

/* ************************************************************************ */
/* Function:    EventImagesDirectory                                        */
/* Description: The site-relative images folder for an event, e.g.          */
/*              events/TestEvent/images. EVENT_Directory is checked so it   */
/*              can't point outside the events folder.                      */
/* Inputs:      $directory - the event's EVENT_Directory.                   */
/* Returns:     string, or '' if the directory name isn't usable.           */
/* ************************************************************************ */
function EventImagesDirectory($directory) {
    if (!is_string($directory) || !preg_match('/^[A-Za-z0-9_-]+$/', $directory)) {
        return '';
    }
    return 'events/'.$directory.'/images';
}

/* ************************************************************************ */
/* Function:    EventFrontpageImageURL                                      */
/* Description: The site-relative URL of an event's front page image. Uses  */
/*              the event's own images folder when the file is there, and   */
/*              falls back to the shared front page images folder.          */
/* Inputs:      $directory - the event's EVENT_Directory.                   */
/*              $filename - the event's EVENT_Frontpage_Image.              */
/* Returns:     string, or '' if no image is set.                           */
/* ************************************************************************ */
function EventFrontpageImageURL($directory, $filename) {
    $filename = basename((string)$filename);
    if ($filename === '') {
        return '';
    }
    $event_dir = EventImagesDirectory($directory);
    if ($event_dir !== '' && is_file(__DIR__.'/'.$event_dir.'/'.$filename)) {
        return $event_dir.'/'.$filename;
    }
    return SHARED_FRONTPAGE_IMAGE_DIR.'/'.$filename;
}
