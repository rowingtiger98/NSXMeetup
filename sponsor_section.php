<?php 
	
    if (isset($_GET['event_id'])) {
        echo 'Event ID = ' . $_GET['event_id'];
        $senna_query_query = "SELECT * from `nsx_meetup_event_sponsors` WHERE `Sponsor_Active` = 1 AND `SPONSOR_Level` = 'Senna'";
        $general_sponsor_query = "SELECT * from `nsx_meetup_event_sponsors` WHERE `Sponsor_Active` = 1";

    } else {
        echo 'Event ID is not set';
        
    }

	$modal_html = '';
    /* ------------------------------------------------ */
    /* Corporate Sponsors HTML of the site 				*/
    /* ------------------------------------------------ */
    $corporate_html = '';
	$title_corporate_html = '<div class="row">';
	$large_corporate_html = '<div class="row">';
	$medium_corporate_html = '<div class="row">';
	$small_corporate_html = '<div class="row">';
    
    /* ------------------------------------------------ */
    /* Bike Shop Sponsors HTML of the site 				*/
    /* ------------------------------------------------ */
    $bikeshop_html = '';
	
	/* ------------------------------------------------------------ */
	/* Create the Database Link and collect the sponsor information */
	/* ------------------------------------------------------------ */
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = "SELECT * from `nsx_meetup_event_sponsors` WHERE `Sponsor_Active` = 1 AND `SPONSOR_Level` = 'Senna'";
	$result = $conn->query($query);
	if($result->num_rows > 0) {
		if ($result->num_rows >= 1) {
			$title_sponsor_class='title';
			$title_sponsor_bix_size = '<div class="col-12 col-12-medium col-12-small">';
		} else {
			$title_sponsor_bix_size = '<div class="col-4 col-6-medium col-12-small">';
		}
	}
	$query = "SELECT * from `nsx_meetup_event_sponsors` WHERE `Sponsor_Active` = 1";
	$result = $conn->query($query);
	if($result->num_rows > 0) {
	  $index_counter = 1;
      while($row = $result->fetch_assoc()) {
        echo $row['SPONSOR_Event_IDs'];
		if($row['SPONSOR_Type'] == 'corporate'){
			$temp_html = '';
			$section = '';
			switch($row['SPONSOR_Division']) 
			{
			    case 'title':
					$section = 'title';
					$temp_html .= $title_sponsor_bix_size;
					if ($title_sponsor_class == 'title') {
						$temp_html .= '<article class="box post '.strtolower($row['SPONSOR_Level']).'_sponsor_box">';
						$temp_html .= '<div class="row gtr-0">';
						$temp_html .= '<div class="col-12 col-12-medium col-12-small title_sponsor_header">Mideast NSX Meet Senna Level Sponsor</div>';
						$temp_html .= '<div class="col-4 col-12-medium col-12-small">';
						$temp_html .= '<a href="'.$row['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="images/sponsors/'.$row['SPONSOR_Thumbnail_Logo'].'" alt="" /></a>';
						$temp_html .= '</div>';
						$temp_html .= '<div class="col-8 col-12-medium col-12-small title_sponsor_body">';
						$temp_html .= '<h3>'.strtoupper($row['SPONSOR_Name']).'</h3>'.$row['SPONSOR_Description'];
						$temp_html .= '<a href="'.$row['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    

						$temp_html .= '</div>';
						$temp_html .= '</div>';
						$temp_html .= '</article>';
						$temp_html .= '</div>';
					} else {
						$temp_html .= '<article class="box post '.strtolower($row['SPONSOR_Level']).'_sponsor_box">';
						$temp_html .= '<div class="row gtr-0">';
						$temp_html .= '<div class="col-12 col-12-medium col-12-small title_sponsor_header">Mideast NSX Meet Senna Level Sponsor</div>';
						$temp_html .= '<div class="col-12 col-12-medium col-12-small">';
						$temp_html .= '<a href="'.$row['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="images/sponsors/'.$row['SPONSOR_Thumbnail_Logo'].'" alt="" /></a>';
						$temp_html .= '</div>';
						$temp_html .= '<div class="col-12 col-12-medium col-12-small cotitle_sponsor_body">';
						$temp_html .= '<h3>'.$title_sponsor_class.' '.strtoupper($row['SPONSOR_Name']).'</h3>'.$row['SPONSOR_Description'];
						$temp_html .= '<a href="'.$row['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    

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
				$temp_html .= '<article class="box post '.$row['SPONSOR_Level'].'">';
				$temp_html .= '<a href="'.$row['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img src="images/sponsors/'.$row['SPONSOR_Thumbnail_Logo'].'" alt="" /></a>';
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
			$modal_html .= BuildSponsorModal($row, $index_counter);
			
		} else {
			//print 'bike_shop';
		}
		
		/* ---------------------------------------------- */
		/* Increment the index counter for the next modal */
		/* ---------------------------------------------- */
		$index_counter++;
	  }
	}
	
	$title_corporate_html .= '</div>';
	$large_corporate_html .= '</div>';
	$medium_corporate_html .= '</div>';
	$small_corporate_html .= '</div>';
	 

	/* Render the sections */
	print '<header class="major">';
	print '<p>The Mideast NSX Meet could not happen without the support of our sponsors If your company would like to join the le Tour de Femme family, download our <a href="documents/SponsorshipDocument.pdf" target="_blank">sponsorship document</a> to learn about the different opportunities we have available. (<a href="documents/SponsorshipDocument.pdf" target="_blank">click here</a>) </p>';
	print '</header>';
	print $title_corporate_html;
	print $large_corporate_html;
	print $medium_corporate_html;
	print $small_corporate_html;
	print $modal_html;
	
	
function BuildSponsorModal($row, $index_counter) {
	$output_html = '';

	$output_html .= '<div id="Sponsor_Modal_'.$index_counter.'" class="modal">';
	$output_html .= '<div class="modal-content">';
	$output_html .= '<span class="modal-close" index_counter="'.$index_counter.'">&times;</span>';

	/* -------------------------------------------- */	
	/* Output HTML for the modal section 			*/
	/* -------------------------------------------- */
	$output_html .= '<div class="row">';
	$output_html .= '<div class="col-6 col-6-medium col-12-small">';
	$output_html .= '<a href="'.$row['SPONSOR_Weblink'].'" class="image fit" target="_blank"><img style="border-radius: 0;" src="images/sponsors/'.$row['SPONSOR_Large_Logo'].'" alt="" /></a></span>';
	$output_html .= '</div>';	
	$output_html .= '<div class="col-6 col-6-medium col-12-small modal_text">';
	$output_html .= $row['SPONSOR_Description'];
    $output_html .= '<a href="'.$row['SPONSOR_Weblink'].'" target="_blank" class="button primary fit" style="margin-top: 40px;">Open Sponsor Link</a>';	    
	$output_html .= '</div>';
	$output_html .= '</div>';
	
	$output_html .= '</div>';
	$output_html .= '</div>';
	
	return $output_html;
}

?>