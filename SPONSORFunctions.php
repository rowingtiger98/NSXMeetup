<?php
require_once __DIR__ . '/eventImages.php';  // SponsorLogoURL()


/* ************************************************************************ */
/* Function:    NEWRenderSponsorInformation                                 */
/* Description: Prints the active sponsors for an event (SPONSOR_Event_IDs  */
/*              is a comma-separated list), or every active sponsor when    */
/*              $event_id is 0. Logos come from SponsorLogoURL() - the      */
/*              event's images/sponsors folder for uploaded logos.          */
/* ************************************************************************ */
function NEWRenderSponsorInformation($event_id) {
    $event_id = (int)$event_id;
    $sponsors_to_display = Array();
    $title_counter = 0;

    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $rows = $pdo->query('SELECT * FROM nsx_meetup_event_sponsors WHERE SPONSOR_Active = 1 ORDER BY SPONSOR_ID')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        $event_sponsored = array_map('trim', explode(',', (string)$row['SPONSOR_Event_IDs']));
        if ($event_id <= 0 || in_array((string)$event_id, $event_sponsored, true)) {
            $sponsors_to_display[] = $row;
            if ($row['SPONSOR_Division'] == 'title') {
                $title_counter++;
            }
        }
    }

    BuildSponsorHTMLSection($sponsors_to_display, $title_counter);
}

function BuildSponsorHTMLSection($sponsors, $title_sponsors) {

    /* ------------------------------------------------ */
    /* MODAL HTML Section start                         */
    /* ------------------------------------------------ */
    $modal_html = '';

    /* ------------------------------------------------ */
    /* Corporate Sponsors HTML of the site 				*/
    /* ------------------------------------------------ */
    $corporate_html = '';
	$title_corporate_html = '<div class="row">';
	$large_corporate_html = '<div class="row">';
	$medium_corporate_html = '<div class="row">';
	$small_corporate_html = '<div class="row">';

    if ($title_sponsors >= 1) {
        $title_sponsor_class='title';
        $title_sponsor_box_size = '<div class="col-12 col-12-medium col-12-small">';
    } else {
        $title_sponsor_box_size = '<div class="col-4 col-6-medium col-12-small">';
    }

    foreach($sponsors as $key => $value) {
        
        $index_counter = $key + 1;
            $temp_html = '';
            $section = '';
            switch($value['SPONSOR_Division']) 
            {
                case 'title':
                    $section = 'title';
                    $temp_html .= $title_sponsor_box_size;
                    if ($title_sponsor_class == 'title') {
                        $temp_html .= '<article class="box post '.strtolower($value['SPONSOR_Level']).'_sponsor_box">';
                        $temp_html .= '<div class="row gtr-0">';
                        $temp_html .= '<div class="col-12 col-12-medium col-12-small title_sponsor_header">'.htmlspecialchars($value['SPONSOR_Level'], ENT_QUOTES).' Level Sponsor</div>';
                        $temp_html .= '<div class="col-4 col-12-medium col-12-small">';
                        $temp_html .= '<a href="'.$value['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="'.htmlspecialchars(SponsorLogoURL($value['SPONSOR_Thumbnail_Logo']), ENT_QUOTES).'" alt="" /></a>';
                        $temp_html .= '</div>';
                        $temp_html .= '<div class="col-8 col-12-medium col-12-small title_sponsor_body">';
                        $temp_html .= '<h3>'.strtoupper($value['SPONSOR_Name']).'</h3>'.$value['SPONSOR_Description'];
                        $temp_html .= '<a href="'.$value['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    

                        $temp_html .= '</div>';
                        $temp_html .= '</div>';
                        $temp_html .= '</article>';
                        $temp_html .= '</div>';
                    } else {
                        $temp_html .= '<article class="box post '.strtolower($value['SPONSOR_Level']).'_sponsor_box">';
                        $temp_html .= '<div class="row gtr-0">';
                        $temp_html .= '<div class="col-12 col-12-medium col-12-small title_sponsor_header">Mideast NSX Meet Senna Level Sponsor</div>';
                        $temp_html .= '<div class="col-12 col-12-medium col-12-small">';
                        $temp_html .= '<a href="'.$value['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="'.htmlspecialchars(SponsorLogoURL($value['SPONSOR_Thumbnail_Logo']), ENT_QUOTES).'" alt="" /></a>';
                        $temp_html .= '</div>';
                        $temp_html .= '<div class="col-12 col-12-medium col-12-small cotitle_sponsor_body">';
                        $temp_html .= '<h3>'.$title_sponsor_class.' '.strtoupper($value['SPONSOR_Name']).'</h3>'.$value['SPONSOR_Description'];
                        $temp_html .= '<a href="'.$value['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    

                        $temp_html .= '</div>';
                        $temp_html .= '</div>';
                        $temp_html .= '</article>';
                        $temp_html .= '</div>';
                    }
                    
                    break;				
                case 'large':
                    $section = 'large';
                    $temp_html .= '<div class="col-4 col-6-medium col-12-small">';
                    break;
                case 'medium':
                    $section = 'medium';
                    $temp_html .= '<div class="col-3 col-6-medium col-12-small">';
                    break;
                case 'small';
                    $section = 'small';
                    $temp_html .= '<div class="col-2 col-6-medium col-12-small">';
                    break;
                default:
                    $temp_html .= '<div class="col-2 col-6-medium col-12-small">';
                    break;
            }
            
            if ($section != 'title') {
                $temp_html .= '<article class="box post '.$value['SPONSOR_Level'].'">';
                $temp_html .= '<a href="'.$value['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="'.htmlspecialchars(SponsorLogoURL($value['SPONSOR_Thumbnail_Logo']), ENT_QUOTES).'" alt="" /></a>';
                $temp_html .= '<ul class="actions special" style="margin-top:10px;">';
                $temp_html .= '<li><a href="#" id="modal_open_button_'.$index_counter.'" class="button small sponsor_button" index_counter="'.$index_counter.'">Learn More</a></li>';
                $temp_html .= '</ul>';
                $temp_html .= '</article>';
                $temp_html .= '</div>';	
            }

            switch($section) {
            case 'title':
            $title_corporate_html .= $temp_html;
            break;

            case 'large':
            $large_corporate_html .= $temp_html;
            break;
            
            case 'medium':
            $medium_corporate_html .= $temp_html;
            break;
            
            case 'small':
            $small_corporate_html .= $temp_html;
            break;
            
            default:
            print 'ERROR';
            break;
        }
        
          
        /* -------------------------------------------- */
        /* Build the Modal information for the sponsor. */
        /* -------------------------------------------- */
        $modal_html .= BuildSponsorModal($value, $index_counter);
    }

	$title_corporate_html .= '</div>';
	$large_corporate_html .= '</div>';
	$medium_corporate_html .= '</div>';
	$small_corporate_html .= '</div>';
	 

	/* Render the sections */
	print $title_corporate_html;
	print $large_corporate_html;
	print $medium_corporate_html;
	print $small_corporate_html;
	print $modal_html;

}

function BuildSponsorModal($value, $index_counter) {
	$output_html = '';

	$output_html .= '<div id="Sponsor_Modal_'.$index_counter.'" class="modal">';
	$output_html .= '<div class="modal-content">';
	$output_html .= '<span class="modal-close" index_counter="'.$index_counter.'">&times;</span>';

	/* -------------------------------------------- */	
	/* Output HTML for the modal section 			*/
	/* -------------------------------------------- */
	$output_html .= '<div class="row">';
	$output_html .= '<div class="col-6 col-6-medium col-12-small">';
	$output_html .= '<a href="'.$value['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img style="border-radius: 0;" src="'.htmlspecialchars(SponsorLogoURL($value['SPONSOR_Large_Logo']), ENT_QUOTES).'" alt="" /></a></span>';
	$output_html .= '</div>';	
	$output_html .= '<div class="col-6 col-6-medium col-12-small modal_text">';
	$output_html .= $value['SPONSOR_Description'];
    $output_html .= '<a href="'.$value['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    
	$output_html .= '</div>';
	$output_html .= '</div>';
	
	$output_html .= '</div>';
	$output_html .= '</div>';
	
	return $output_html;
}

?>