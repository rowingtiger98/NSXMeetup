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
const SHARED_SPONSOR_LOGO_DIR    = 'assets/images/sponsors';   // older sponsor logos, stored by file name only
const DEFAULT_SPONSOR_LOGO       = 'sponsor-coming-soon-front.jpg';  // in SHARED_SPONSOR_LOGO_DIR, for sponsors without a logo

const FRONTPAGE_IMAGE_MAX_BYTES  = 10 * 1048576;  // 10 MB - keep in step with .user.ini and eventsettings.js
const FRONTPAGE_IMAGE_MAX_PIXELS = 24000000;      // 24 megapixels - larger images need too much memory to resize
const SPONSOR_LOGO_MAX_SIZE      = 600;           // sponsor logos are scaled to fit a 600 x 600 box

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

/* ************************************************************************ */
/* Function:    EventSponsorLogoDirectory                                   */
/* Description: The site-relative sponsor logo folder for an event, e.g.    */
/*              events/TestEvent/images/sponsors.                           */
/* Inputs:      $directory - the event's EVENT_Directory.                   */
/* Returns:     string, or '' if the directory name isn't usable.           */
/* ************************************************************************ */
function EventSponsorLogoDirectory($directory) {
    $event_dir = EventImagesDirectory($directory);
    return ($event_dir === '') ? '' : $event_dir.'/sponsors';
}

/* ************************************************************************ */
/* Function:    EventExperienceBannerDirectory                              */
/* Description: The site-relative agenda event banner folder for an event, */
/*              e.g. events/TestEvent/images/experiences.                   */
/* Inputs:      $directory - the event's EVENT_Directory.                   */
/* Returns:     string, or '' if the directory name isn't usable.           */
/* ************************************************************************ */
function EventExperienceBannerDirectory($directory) {
    $event_dir = EventImagesDirectory($directory);
    return ($event_dir === '') ? '' : $event_dir.'/experiences';
}

/* ************************************************************************ */
/* Function:    EventAttractionImageDirectory                               */
/* Description: The site-relative hotel and attraction picture folder for  */
/*              an event, e.g. events/TestEvent/images/attractions.         */
/* Inputs:      $directory - the event's EVENT_Directory.                   */
/* Returns:     string, or '' if the directory name isn't usable.           */
/* ************************************************************************ */
function EventAttractionImageDirectory($directory) {
    $event_dir = EventImagesDirectory($directory);
    return ($event_dir === '') ? '' : $event_dir.'/attractions';
}

/* ************************************************************************ */
/* Function:    AttractionImageURL                                          */
/* Description: The URL of a hotel or attraction's picture. Uploaded        */
/*              pictures are stored with their site-relative path           */
/*              (events/<dir>/images/attractions/...); older ones are a     */
/*              file name in assets/images/attractions/<event id>/.         */
/* Inputs:      $image - Attraction_Image. $event_id - its Event_ID.        */
/* Returns:     string, or '' when there's no picture                       */
/* ************************************************************************ */
function AttractionImageURL($image, $event_id) {
    $image = trim((string)$image);
    if ($image === '') {
        return '';
    }
    if (preg_match('#^events/[A-Za-z0-9_-]+/images/attractions/[A-Za-z0-9_.-]+$#', $image)) {
        $path = $image;
    } else {
        $path = 'assets/images/attractions/'.(int)$event_id.'/'.basename($image);
    }
    return function_exists('app_url') ? app_url($path) : $path;
}

/* ************************************************************************ */
/* Function:    ExperienceBannerURL                                         */
/* Description: The URL of a agenda event's banner. Uploaded banners are   */
/*              stored with their site-relative path                        */
/*              (events/<dir>/images/experiences/...); older ones are a     */
/*              file name in assets/images/.                                */
/* Inputs:      $banner - EXPERIENCE_Banner_Image.                          */
/* Returns:     string, or '' when there's no banner                        */
/* ************************************************************************ */
function ExperienceBannerURL($banner) {
    $banner = trim((string)$banner);
    if ($banner === '') {
        return '';
    }
    if (preg_match('#^events/[A-Za-z0-9_-]+/images/experiences/[A-Za-z0-9_.-]+$#', $banner)) {
        $path = $banner;
    } else {
        $path = 'assets/images/'.basename($banner);
    }
    return function_exists('app_url') ? app_url($path) : $path;
}

/* ************************************************************************ */
/* Function:    SponsorLogoURL                                              */
/* Description: The URL of a sponsor logo. New logos are stored with their  */
/*              site-relative path (events/<dir>/images/sponsors/...);      */
/*              older ones are a file name in the shared sponsors folder.   */
/*              No logo gives DEFAULT_SPONSOR_LOGO.                         */
/* Inputs:      $logo - SPONSOR_Large_Logo or SPONSOR_Thumbnail_Logo.       */
/* Returns:     string                                                      */
/* ************************************************************************ */
function SponsorLogoURL($logo) {
    $logo = (string)$logo;
    if (preg_match('#^events/[A-Za-z0-9_-]+/images/sponsors/[A-Za-z0-9_.-]+$#', $logo)) {
        $path = $logo;
    } else {
        $path = SHARED_SPONSOR_LOGO_DIR.'/'.(trim($logo) !== '' ? basename($logo) : DEFAULT_SPONSOR_LOGO);
    }
    return function_exists('app_url') ? app_url($path) : $path;
}

/* ************************************************************************ */
/* Function:    CheckFrontpageImageUpload                                   */
/* Description: Checks an image upload from $_FILES. The file's contents    */
/*              decide its type - the browser-supplied name and type are    */
/*              ignored. (Adapted from ContactBook's check_book_photo_upload)*/
/* Inputs:      $file - the $_FILES entry, or null.                         */
/* Returns:     ['error' => string, 'tmp' => string, 'type' => IMAGETYPE_*] */
/* ************************************************************************ */
function CheckFrontpageImageUpload($file) {
    $none = ['error' => '', 'tmp' => '', 'type' => 0];
    $max_mb = FRONTPAGE_IMAGE_MAX_BYTES / 1048576;

    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['error' => 'Please choose an image to upload.'] + $none;
    }
    if (is_array($file['error'])) {
        return ['error' => 'Please choose a single image.'] + $none;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
        || $file['size'] > FRONTPAGE_IMAGE_MAX_BYTES) {
        return ['error' => "The image is too large. Please choose one under {$max_mb} MB."] + $none;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        error_log('CheckFrontpageImageUpload: upload failed with error code '.$file['error']);
        return ['error' => 'The image could not be uploaded. Please try again.'] + $none;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return ['error' => 'The image must be a JPEG, PNG or WebP file.'] + $none;
    }
    if ($info[0] * $info[1] > FRONTPAGE_IMAGE_MAX_PIXELS) {
        return ['error' => 'The image\'s dimensions are too large. Please choose a smaller image.'] + $none;
    }

    return ['error' => '', 'tmp' => $file['tmp_name'], 'type' => $info[2]];
}

/* ************************************************************************ */
/* Function:    SaveFrontpageImage                                          */
/* Description: Re-encodes an uploaded image into $dir as <$basename>.jpg   */
/*              (or .png for PNGs, to keep transparency), scaled down to    */
/*              $max_height (and $max_width, when given) with its           */
/*              proportions kept. Smaller images are not enlarged.          */
/*              Re-encoding also turns phone photos the right way up,       */
/*              strips hidden data such as GPS location, and drops anything */
/*              that isn't image data. (Adapted from ContactBook's          */
/*              save_book_photo)                                            */
/* Inputs:      $tmp - uploaded temp file. $type - IMAGETYPE_*.             */
/*              $dir - the full directory path to save in.                  */
/*              $basename - the filename without extension.                 */
/*              $max_height - the tallest it may be. $max_width - the       */
/*              widest it may be, or 0 for no limit.                        */
/* Returns:     The saved filename, or '' on failure.                       */
/* ************************************************************************ */
function SaveFrontpageImage($tmp, $type, $dir, $basename, $max_height, $max_width = 0) {
    switch ($type) {
        case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG:  $image = @imagecreatefrompng($tmp);  break;
        case IMAGETYPE_WEBP: $image = @imagecreatefromwebp($tmp); break;
        default:             $image = false;
    }
    if ($image === false) {
        return '';
    }

    // Phones save photos sideways and record the rotation in EXIF - apply it.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (@exif_read_data($tmp))['Orientation'] ?? 1;
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($angle !== 0) {
            $rotated = imagerotate($image, $angle, 0);
            imagedestroy($image);
            $image = $rotated;
        }
    }

    $width  = imagesx($image);
    $height = imagesy($image);
    $scale = min(1, $max_height / $height, $max_width > 0 ? $max_width / $width : 1);
    if ($scale < 1) {
        $new_width  = max(1, (int) round($width * $scale));
        $new_height = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    if ($type === IMAGETYPE_PNG) {
        $filename = $basename.'.png';
        imagesavealpha($image, true);
        $saved = imagepng($image, $dir.'/'.$filename, 6);
    } else {
        $filename = $basename.'.jpg';
        $saved = imagejpeg($image, $dir.'/'.$filename, 85);
    }
    imagedestroy($image);

    return $saved ? $filename : '';
}
