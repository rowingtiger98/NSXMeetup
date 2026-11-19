<?php
// -------------------------------------------------------------------------------------
// The site's front page banner (index.php): up to 3 images from each event that hasn't
// finished, picked with Front Page in its Edit Event Settings > Banner Images, with the
// event's name and dates - topped up to 6 slides with the images in images/banners/
// under "Welcome to NSX Meetup". At most 9 slides. See SiteBannerSlides() in
// eventImages.php.
// -------------------------------------------------------------------------------------
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventImages.php';

try {
    $site_banner_slides = SiteBannerSlides(Create_SQL_PHP_DataObject(DB_NAME));
} catch (PDOException $e) {
    // e.g. the database is busy - the page still shows, with just the images/banners slides
    error_log('banners.php: '.$e->getMessage());
    $site_banner_slides = SiteBannerSlides(null);
}
$site_banner_h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
?>
<!-- Banner -->
<?php if ($site_banner_slides) { ?>
<div id="banner" class="active">
    <?php foreach ($site_banner_slides as $slide) { ?>
    <article data-position="center">
        <div class="inner">
            <img src="<?php echo $site_banner_h($slide['image']); ?>" alt="" />
            <h1><?php echo $site_banner_h($slide['title']); ?></h1>
            <?php if ($slide['dates'] !== '') { ?><p><?php echo $site_banner_h($slide['dates']); ?></p><?php } ?>
        </div>
    </article>
    <?php } ?>
</div>
<?php } else { ?>
<div id="banner" class="event_banner_plain active">
    <article data-position="center">
        <div class="inner">
            <h1>Welcome to NSX Meetup</h1>
        </div>
    </article>
</div>
<?php } ?>
