<?php
  require_once __DIR__ . '/databaseFunctions.php';
  require_once __DIR__ . '/eventImages.php';  // EventAttractionImageDirectory(), AttractionImageURL(), CheckFrontpageImageUpload(), SaveFrontpageImage()

  // Defined before EndpointDispatch() below, which runs the requested method straight away
  const ATTRACTION_IMAGE_MAX_WIDTH  = 1200;  // hotel and attraction pictures are scaled down to fit 1200 x 800
  const ATTRACTION_IMAGE_MAX_HEIGHT = 800;

  require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
  const LOCAL_ATTRACTION_METHODS = [
      'RenderOpeningPageInformation' => 'public',
      'BuildLocalAttractionSection'  => 'public',
      'CreateNewLocalAttraction'     => 'event_admin',
      'EnterNewAttractionCategory'   => 'event_admin',
      'EnterNewAttractionDetails'    => 'admin',
      'SaveLocalAttraction'          => 'event_admin',
      'DeleteLocalAttraction'        => 'event_admin',
  ];
  EndpointDispatch(__FILE__, LOCAL_ATTRACTION_METHODS, ['EnterNewAttractionCategory', 'EnterNewAttractionDetails', 'SaveLocalAttraction', 'DeleteLocalAttraction']);
  if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__ && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // PHP throws away the whole request (method included) when it's over post_max_size
    echo json_encode(['fail', 'The picture is too large for the server to accept (limit '.ini_get('post_max_size').'). Please choose a smaller one.']);
    exit();
  }


/* ************************************************************************ */
/* Funciton:    RenderOpeningPageInformation                                 */
/* Description: This function returns the local sttractions for the topic   */
/*              selected by the user.                                       */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */  
function RenderOpeningPageInformation() {
	$event_id = (int)($_POST['event_id'] ?? 0);  // also written into the page
  $editable = $_POST['editable'];
	$results = array();	

  if ($editable == 'true') {
    $category_links  = '<a href="#" id="local_attraction_add_button" class="button primary small fit" event_id="'.$event_id.'"><i class="icon fa-plus-square" style="padding-right:0.25em;"></i>Add New Attraction</a>';
    $category_links .= '<ul id="local_attractions_category_menu">';
  } else {
    $category_links = '<ul id="local_attractions_category_menu">';
  }
  $opening_category = '';
  $category_container = '';
  $editable_attr = ($editable == 'true') ? ' editable="true"' : '';  // the admin tool shows a table with Edit buttons

	$conn = connectDB('kirbypar_nsx_meetup');
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attraction_categories` ORDER BY `Category_ID`");
	$categories = [];
	while ($result && ($row = $result->fetch_assoc())) {
        $categories[] = $row;
	}
	// The public page lists the hotels in a section of their own, above the other categories;
	// the admin tool keeps every category in its menu
	$hotel_categories = array_values(array_filter($categories, 'IsHotelCategory'));
	if ($editable != 'true') {
        $categories = array_values(array_filter($categories, function($row) { return !IsHotelCategory($row); }));
	}

  $output_html = '<div id="local_attractions_continer" class="weekend_agenda_container">';

  if($editable == 'true') {
    $output_html .= '<h2>Add / Edit Hotels and Local Attractions</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<p>Use this page to list the hotels and local attractions shown on the event\'s Local Attractions page: places to stay, restaurants,
      NSX related and non-NSX related things to see and do, and resources like Urgent Care and Mechanics. Each attraction can include its address, phone,
      email, website, distance, parking and a description. Attractions are grouped into categories, which are shared by all events. Pick a category
      on the left to see this event\'s attractions in it, or click Add New Attraction to add one, choosing New Category if none of the existing ones fit.
      Attractions in a category with "hotel" in its name are shown in a Hotels section of their own on the event\'s page.</p>';
  } else {
    if (count($hotel_categories) > 0) {
      $output_html .= HotelSection($conn, $event_id, $hotel_categories);
    }
    $output_html .= 	'<h1 class="major">Local Attractions</h1><p>Listed below are different things to see and do, both NSX related and non-NSX related,
      while you\'re in the area. There are also resources that we hope you will not need, like Urgent Care and Mechanics. If you are looking for some information 
    and don\'t see it below, please reach out and we will see what we can find for you!. </p>';
  }

  $output_html .= '<div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">';
  $output_html .= '<div id="local_attractions_menu" class="col-3 col-12-xsmall">';

	// Open on the category asked for (e.g. "hotels" from the event page's hotel card), else the first one
	$requested_category = is_string($_POST['category'] ?? null) ? $_POST['category'] : '';
	$category_names = array_column($categories, 'Category_Name');
	$opening_category = in_array($requested_category, $category_names, true) ? $requested_category : ($category_names[0] ?? '');
	if(count($categories) > 0) {
        foreach($categories as $row) {
            if($row['Category_Name'] == $opening_category) {
                $category_links .= '<li id="local_attraction_link_'.$row['Category_Name'].'" class="local_attraction_link active" event_id="'.$event_id.'"'.$editable_attr.' section_name="'.$row['Category_Name'].'">'.$row['Category_Title'].'</li>';
            } else {            
                $category_links .= '<li id="local_attraction_link_'.$row['Category_Name'].'" class="local_attraction_link" event_id="'.$event_id.'"'.$editable_attr.' section_name="'.$row['Category_Name'].'">'.$row['Category_Title'].'</li>';
            }

            if ($row['Category_Name'] == $opening_category) {
                $category_container .= '<div id="local_attraction_container_'.$row['Category_Name'].'" class="local_attraction_container active" section_name="'.$row['Category_Name'].'">';
                if ($editable == 'true') {
                    $category_container .= LocalAttractionTable($conn, $event_id, $row['Category_Name']).'</div> ';
                    continue;
                }
                $attr_result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attractions` WHERE `Event_ID` = ? AND `Attraction_Category` = ?", [(int)$event_id, $row['Category_Name']]);
                if($attr_result->num_rows > 0) {
                    while($attr_row = $attr_result->fetch_assoc()) {
                        $category_container .= BuildIndividualLocalAttaractionContainer($attr_row);
                    }
                } else {
                    $category_container .= 'There are currently no attractions in this category.';
                }
                $category_container .= '</div> ';
            }
        }       
	} 
    $output_html .= $category_links . '<ul>';
    $output_html .= '</div>';
    $output_html .= '<div id="local_attractions_content" class="col-9 col-12-xsmall">';
    $output_html .= $category_container;
    $output_html .= '</div>';                      
    $output_html .= '</div>';
    $output_html .= '</div>';
    
    closeDB($conn);
    $results[] = $output_html;
	echo json_encode($results);		  	
}

// True for a hotel category, i.e. one with "hotel" in its name or title (the same rule
// render.js uses to show the Hotel Reservation Link box)
function IsHotelCategory($category) {
  return stripos($category['Category_Name'].' '.$category['Category_Title'], 'hotel') !== false;
}

/* ************************************************************************ */
/* Function:    HotelSection                                                */
/* Description: The Hotels section at the top of the public Local           */
/*              Attractions page: every attraction in the event's hotel     */
/*              categories, by name.                                        */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function HotelSection($conn, $event_id, $hotel_categories) {
  $names = array_column($hotel_categories, 'Category_Name');
  $placeholders = implode(',', array_fill(0, count($names), '?'));
  $result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attractions` WHERE `Event_ID` = ? AND `Attraction_Category` IN ($placeholders) ORDER BY `Attraction_Name`", array_merge([(int)$event_id], $names));

  $output_html  = '<div id="local_attractions_hotels" style="margin-bottom:2em;">';
  $output_html .= '<h1 class="major">Hotels</h1>';
  if ($result && $result->num_rows > 0) {
    $output_html .= '<p>Here are the hotels near the event. Where a hotel has a room block for the event, use its Reserve a Room button to get the event rate.</p>';
    while ($row = $result->fetch_assoc()) {
      $output_html .= BuildIndividualLocalAttaractionContainer($row, true);
    }
  } else {
    $output_html .= '<p>Hotel information for this event is coming soon. Check back closer to the event.</p>';
  }
  $output_html .= '</div>';
  return $output_html;
}

/* ************************************************************************ */
/* Funciton:    ReBuildLocalAttractionLinks                                 */
/* Description: This function will rebuild the list of local attraction     */
/*              categories after a new one is added.                        */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */  

/* ************************************************************************ */
/* Funciton:    BuildLocalAttractionSection                                 */
/* Description: This function returns the local sttractions for the topic   */
/*              selected by the user.                                       */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */  
function BuildLocalAttractionSection() {
  $event_id = (int)($_POST['event_id'] ?? 0);
	$section_name = (string)($_POST['section_name'] ?? '');
	$results = array();	
  $output_html = '';
  $section_attr = htmlspecialchars($section_name, ENT_QUOTES);
  $output_html .= '<div id="local_attraction_container_'.$section_attr.'" class="local_attraction_container active" section_name="'.$section_attr.'">';
      
	$conn = connectDB('kirbypar_nsx_meetup');
  if (($_POST['editable'] ?? '') === 'true') {
    $output_html .= LocalAttractionTable($conn, $event_id, $section_name).'</div> ';
    closeDB($conn);
    echo json_encode([$output_html]);
    return;
  }
	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attractions` WHERE `Event_ID` = ? AND `Attraction_Category` = ?", [$event_id, $section_name]);
	if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $output_html .= BuildIndividualLocalAttaractionContainer($row);
        }
	} else {
		$output_html .= 'There are currently no attractions in this category.';
	}
    
    $output_html .= '</div> ';  
	closeDB($conn);
    $results[] = $output_html;
	echo json_encode($results);		  	
}

/* ************************************************************************ */
/* Function:    LocalAttractionTable                                        */
/* Description: The admin tool's view of a category: a table with a row    */
/*              per attraction of the event in it, each with Edit and       */
/*              Delete.                                                     */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function LocalAttractionTable($conn, $event_id, $category) {
  $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
  $result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attractions` WHERE `Event_ID` = ? AND `Attraction_Category` = ? ORDER BY `Attraction_Name`", [(int)$event_id, (string)$category]);
  if (!$result || $result->num_rows == 0) {
    return '<p>This event has no attractions in this category yet. Click Add New Attraction to add one.</p>';
  }

  $output_html  = '<p id="local_attraction_list_message" class="event_settings_error" style="display:none;"></p>';
  $output_html .= '<div class="table-wrapper">';
  $output_html .= '<table class="regtype_table">';
  $output_html .= '<thead><tr><th>Name</th><th>Address</th><th>Phone</th><th>Links</th><th></th></tr></thead>';
  $output_html .= '<tbody>';
  while ($row = $result->fetch_assoc()) {
    $city_line = trim($row['Attraction_City'].($row['Attraction_State'] != '' ? ', '.$row['Attraction_State'] : '').' '.$row['Attraction_Zip']);
    $links = [];
    $web = trim((string)$row['Attraction_Web']);
    if ($web !== '') {
      $links[] = '<a href="'.$h($web).'" target="_blank" rel="noopener">Web Site</a>';
    }
    if (AttractionReservationLink($row['Attraction_Hotel_Reservation_Link'] ?? '') !== '') {
      $links[] = '<a href="'.$h(AttractionReservationLink($row['Attraction_Hotel_Reservation_Link'])).'" target="_blank" rel="noopener">Reservations</a>';
    }
    $output_html .= '<tr>';
    $output_html .= '   <td>'.$h($row['Attraction_Name']).'</td>';
    $output_html .= '   <td>'.$h($row['Attraction_Street_Address']).($city_line !== '' ? '<br>'.$h($city_line) : '').'</td>';
    $output_html .= '   <td>'.$h($row['Attraction_Phone']).'</td>';
    $output_html .= '   <td>'.implode('<br>', $links).'</td>';
    $output_html .= '   <td class="regtype_actions"><a href="#" class="button small local_attraction_edit_button" event_id="'.(int)$event_id.'" attraction_id="'.(int)$row['Attraction_ID'].'">Edit</a>'
                   .' <a href="#" class="button small local_attraction_delete_button" event_id="'.(int)$event_id.'" attraction_id="'.(int)$row['Attraction_ID'].'">Delete</a></td>';
    $output_html .= '</tr>';
  }
  $output_html .= '</tbody></table></div>';
  return $output_html;
}

/* ************************************************************************ */
/* Funciton:    BuildIndividualLocalAttaractionContainer                    */
/* Description: This builds a single local attraction container from the    */
/*              row of database information.                                */
/* Inputs:      $row = Database information for a single attraction.        */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */  
function BuildIndividualLocalAttaractionContainer($row, $is_hotel = false) {

    $image_url = AttractionImageURL($row['Attraction_Image'] ?? '', $row['Event_ID']);
    $output_html = '<div id="individual_local_attraction_'.$row['Attraction_ID'].'" class="individual_local_attraction_container">';
    $output_html .= '<div class="row gtr-25 gtr-uniform">';

    if ($image_url === '') {
      $output_html .= ' <div class="col-12 col-12-xsmall">';
    } else {
      // A hotel's picture gets 4 of the 12 columns on larger screens; the picture and the
      // information each take the full width on small screens and phones
      $image_cols = $is_hotel ? 'col-4 col-12-small' : 'col-2 col-12-xsmall';
      $info_cols  = $is_hotel ? 'col-8 col-12-small hotel_information' : 'col-10 col-12-xsmall';
      $output_html .= '<div class="'.$image_cols.'">';
      $output_html .= ' <span class="image fit"><img src="'.htmlspecialchars($image_url, ENT_QUOTES).'" alt="'.htmlspecialchars((string)$row['Attraction_Name'], ENT_QUOTES).'"></span>';
      $output_html .= '</div>';
      $output_html .= '<div class="'.$info_cols.'">';
    }
    $output_html .= '  <h4>'.$row['Attraction_Name'].'</h4>';
    // A hotel's details start open, with the link to show less; other attractions start closed
    $more_action = $is_hotel ? 'less' : 'more';
    $output_html .= '  <p>'.$row['Attraction_Description'].' <a href="#" id="show_more_attraction_information_'.$row['Attraction_ID'].'" class="show_more_attraction_information_link" attraction_id="'.$row['Attraction_ID'].'" action="'.$more_action.'" style="font-size:0.8em;font-style:italic;">'.($is_hotel ? '(Show Less Information)' : '(Show More Information)').'</a></p>';
    $output_html .= AttractionReservationButton($row);
    $output_html .= '  <div id="more_attraction_information_container_'.$row['Attraction_ID'].'" class="more_attraction_information_container'.($is_hotel ? ' active' : '').'">';
    $output_html .= FormatIndividualAttractionInformation($row);
    $output_html .= '  </div>';
    $output_html .= ' </div>';
    $output_html .= ' <div class="col-12 col-12-xsmall" style="border-bottom:1px solid #ffffff;margin:0 0 0.5em 0.5em;"></div>';

    $output_html .= "</div>";
    $output_html .= "</div>";
    return $output_html;

} 

/* ************************************************************************ */
/* Funciton:    AttractionReservationButton                                 */
/* Description: The "Reserve a Room" button for a hotel with a reservation  */
/*              link (e.g. the event's discounted room block).              */
/* Returns:     HTML, or '' when there's no usable link                     */
/* ************************************************************************ */
function AttractionReservationButton($row) {
  $link = AttractionReservationLink($row['Attraction_Hotel_Reservation_Link'] ?? '');
  if ($link === '') {
    return '';
  }
  return '<p><a href="'.htmlspecialchars($link, ENT_QUOTES).'" target="_blank" rel="noopener" class="button primary small">Reserve a Room</a></p>';
}

// A reservation link trimmed, or '' if it's blank or not an http(s) address
function AttractionReservationLink($link) {
  $link = trim((string)$link);
  if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $link)) {
    return '';
  }
  return $link;
}

/* ************************************************************************ */
/* Funciton:    FormatIndividualAttractionInformation                       */
/* Description: Formats the database information for a local event.         */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */ 
function FormatIndividualAttractionInformation($row) {
  $output_html = '';
  if ($row['Attraction_Additional_Info'] != '') {
    $output_html .= '<div style="margin-bottom:0.5em;"><strong style="color:#ff0000;">Additional Information</strong></br>'.$row['Attraction_Additional_Info'].'</div>';
  }
  if ($row['Attraction_Parking'] != '') {
    $output_html .= '<div style="margin-bottom:0.5em;"><strong style="color:#ff0000;">Parking Information</strong></br>'.$row['Attraction_Parking'].'</div>';
  }
  /* Contact and Link Information */
  $output_html .= '<div class="row gtr-25 gtr-uniform">';

  $output_html .= ' <div class="col-4 col-12-xsmall">';
  $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Address</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Street_Address'].'<br>'.$row['Attraction_City'].', '.$row['Attraction_State'].' '.$row['Attraction_Zip'].'</span>';
  $output_html .= ' </div>';

  $output_html .= ' <div class="col-4 col-12-xsmall">';
  if ($row['Attraction_Phone'] != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Phone Number</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Phone'].'</span>';
  }
  if ($row['Attraction_Email'] != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Email Address</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Email'].'</span>';
  }
  $output_html .= ' </div>';

  $output_html .= ' <div class="col-4 col-12-xsmall">';
  if ($row['Attraction_Web'] != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Web Site</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'"><a href="'.$row['Attraction_Web'].'" target="_blank">Click Here</a></span>';
  }
  if ($row['Attraction_Distance'] != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Distance from Hotel</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Distance'].'</span>';
  }

  if (($row['Attraction_Google_Map_Link'] ?? '') != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Google Maps</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Google_Map_Link'].'</span>';
  }
  $output_html .= ' </div>';
  $output_html .= '</div>';
  
  return $output_html;
}



/* ************************************************************************ */
/* Funciton:    CreateNewLocalAttraction                                    */
/* Description: This builds the HTML form needed to build a new local       */
/*              attraction / category.                                      */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */ 
function CreateNewLocalAttraction() {
  $event_id = (int)($_POST['event_id'] ?? 0);
  $attraction_id = (int)($_POST['attraction_id'] ?? 0);  // set when editing an attraction
  $results = array();	

	$conn = connectDB('kirbypar_nsx_meetup');
  $attraction = [];
  if ($attraction_id > 0) {
    $result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attractions` WHERE `Attraction_ID` = ? AND `Event_ID` = ?", [$attraction_id, $event_id]);
    $attraction = $result ? ($result->fetch_assoc() ?: []) : [];
    if (!$attraction) {
      closeDB($conn);
      echo json_encode(['<p class="event_settings_error">That attraction could not be found. It may have been deleted.</p>']);
      return;
    }
  }
  // The attraction's saved value for a form field, escaped ('' when adding)
  $val = function ($column) use ($attraction) { return htmlspecialchars((string)($attraction[$column] ?? ''), ENT_QUOTES); };

  $output_html = '';
  $output_html .= '<div id="local_attraction_container_create" class="local_attraction_container active" event_id="'.$event_id.'" section_name="create">';
  $output_html .= '<h3>'.($attraction ? 'Edit '.$val('Attraction_Name') : 'Add a New Attraction').'</h3>';
  $output_html .= '<div class="row gtr-25 gtr-uniform">';

  //Get the list of categories
  $output_html .= '<div class="col-6 col-12-xsmall">';  
  $output_html .= '<select name="new_local_attraction_category" id="new_local_attraction_category" style="color:rgba(255, 255, 255, 0.7)";>';
  $output_html .= '<option value=""'.($attraction ? '' : ' selected').' disabled hidden>Select the category</option>';

	$result = PreparedQuery($conn, "SELECT * from `nsx_meetup_attraction_categories` ORDER BY `Category_ID`");
	if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $selected = ($attraction && $row['Category_Name'] === $attraction['Attraction_Category']) ? ' selected' : '';
          $output_html .= '<option value="'.$row['Category_Name'].'"'.$selected.'>'.$row['Category_Title'].'</option>';
        }
        $output_html .= '<option value="new">New Category</option>';
	} else {
		$output_html .= '<option value="new">New Category</option>';
	}
  $output_html .= '</select>';
  $output_html .= '</div>';
  $output_html .= '<div class="col-6 col-12-xsmall">'; 
  $output_html .= '<input id="new_category_name_container" class="new_category_name_container" type="text" placeholder="Enter the New Category Name"></input>'; 
  $output_html .= '</div>';  
$output_html .= '<div class="col-8 col-12-xsmall">';
$output_html .= '<input type="text" name="name" id="info_text_field_attraction_name" value="'.$val('Attraction_Name').'" placeholder="Name" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="distance" id="info_text_field_attraction_distance" value="'.$val('Attraction_Distance').'" placeholder="Distance To Event Site" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="website" id="info_text_field_attraction_website" value="'.$val('Attraction_Web').'" placeholder="Web Site" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="email" id="info_text_field_attraction_email" value="'.$val('Attraction_Email').'" autocomplete="off" placeholder="Email Address" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="phone" id="info_text_field_attraction_phone" value="'.$val('Attraction_Phone').'" placeholder="Phone Number" />';
$output_html .= '</div>';
$output_html .= '<div id="info_text_field_attraction_reservation_link_container" class="col-12 col-12-xsmall" style="display:none;">';  // shown for a hotel category (render.js)
$output_html .= '<input type="url" name="reservation_link" id="info_text_field_attraction_reservation_link" value="'.$val('Attraction_Hotel_Reservation_Link').'" maxlength="2048" placeholder="Hotel Reservation Link (the link the hotel gave you for the event\'s room block)" />';
$output_html .= '<p id="info_text_field_attraction_reservation_link_message" class="event_settings_error" style="display:none;"></p>';
$output_html .= '</div>';
$current_image_url = $attraction ? AttractionImageURL($attraction['Attraction_Image'] ?? '', $attraction['Event_ID']) : '';
$output_html .= '<div id="info_text_field_attraction_image_container" class="col-12 col-12-xsmall" style="display:none;">';  // shown for a hotel category (render.js)
$output_html .= '<label for="info_text_field_attraction_image">Hotel Picture</label>';
$output_html .= '<div id="local_attraction_image_preview" class="local_attraction_image_preview">'
               .($current_image_url !== '' ? '<img src="'.htmlspecialchars($current_image_url, ENT_QUOTES).'" alt="Current picture">' : '').'</div>';
$output_html .= '<input type="file" id="info_text_field_attraction_image" accept="image/jpeg,image/png,image/webp" />';
if ($current_image_url !== '') {
  $output_html .= '<div><input type="checkbox" id="info_checkbox_attraction_remove_image" /><label for="info_checkbox_attraction_remove_image">Remove the current picture</label></div>';
}
$output_html .= '<p id="info_text_field_attraction_image_message" class="event_settings_error" style="display:none;"></p>';
$output_html .= '</div>';
$output_html .= '<div class="col-5 col-12-xsmall">';
$output_html .= '<input type="text" name="street" id="info_text_field_attraction_street" value="'.$val('Attraction_Street_Address').'" placeholder="Street Address" />';
$output_html .= '</div>';
$output_html .= '<div class="col-3 col-12-xsmall">';
$output_html .= '<input type="text" name="city" id="info_text_field_attraction_city" value="'.$val('Attraction_City').'" placeholder="City" />';
$output_html .= '</div>';
$output_html .= '<div class="col-2 col-12-xsmall">';
$output_html .= '<select name="state" id="info_select_field_attraction_state" style="color:rgba(255, 255, 255, 0.7)";>';
$output_html .= '    <option value=""'.($attraction ? '' : ' selected').' disabled hidden>State</option>';
$output_html .= '    <option value="AL">AL</option>';
$output_html .= '    <option value="AK">AK</option>';
$output_html .= '    <option value="AZ">AZ</option>';
$output_html .= '    <option value="AR">AR</option>';
$output_html .= '    <option value="CA">CA</option>';
$output_html .= '    <option value="CO">CO</option>';
$output_html .= '    <option value="CT">CT</option>';
$output_html .= '    <option value="DE">DE</option>';
$output_html .= '    <option value="DC">DC</option>';
$output_html .= '    <option value="FL">FL</option>';
$output_html .= '    <option value="GA">GA</option>';
$output_html .= '    <option value="HI">HI</option>';
$output_html .= '    <option value="ID">ID</option>';
$output_html .= '    <option value="IL">IL</option>';
$output_html .= '    <option value="IN">IN</option>';
$output_html .= '    <option value="IA">IA</option>';
$output_html .= '    <option value="KS">KS</option>';
$output_html .= '    <option value="KY">KY</option>';
$output_html .= '    <option value="LA">LA</option>';
$output_html .= '    <option value="ME">ME</option>';
$output_html .= '    <option value="MD">MD</option>';
$output_html .= '    <option value="MA">MA</option>';
$output_html .= '    <option value="MI">MI</option>';
$output_html .= '    <option value="MN">MN</option>';
$output_html .= '    <option value="MS">MS</option>';
$output_html .= '    <option value="MO">MO</option>';
$output_html .= '    <option value="MT">MT</option>';
$output_html .= '    <option value="NE">NE</option>';
$output_html .= '    <option value="NV">NV</option>';
$output_html .= '    <option value="NH">NH</option>';
$output_html .= '    <option value="NJ">NJ</option>';
$output_html .= '    <option value="NM">NM</option>';
$output_html .= '    <option value="NY">NY</option>';
$output_html .= '    <option value="NC">NC</option>';
$output_html .= '    <option value="ND">ND</option>';
$output_html .= '    <option value="OH">OH</option>';
$output_html .= '    <option value="OK">OK</option>';
$output_html .= '    <option value="OR">OR</option>';
$output_html .= '    <option value="PA">PA</option>';
$output_html .= '    <option value="RI">RI</option>';
$output_html .= '    <option value="SC">SC</option>';
$output_html .= '    <option value="SD">SD</option>';
$output_html .= '    <option value="TN">TN</option>';
$output_html .= '    <option value="TX">TX</option>';
$output_html .= '    <option value="UT">UT</option>';
$output_html .= '    <option value="VT">VT</option>';
$output_html .= '    <option value="VA">VA</option>';
$output_html .= '    <option value="WA">WA</option>';
$output_html .= '    <option value="WV">WV</option>';
$output_html .= '    <option value="WI">WI</option>';
$output_html .= '    <option value="WY">WY</option>';
$output_html .= '</select>';								
$output_html .= '</div>';
$output_html .= '<div class="col-2 col-12-xsmall">';
$output_html .= '<input type="text" name="zip" id="info_text_field_attraction_zip" value="'.$val('Attraction_Zip').'" placeholder="Zip" />';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_description">Attraction Description</label>';
$output_html .= '<textarea id="info_text_field_attraction_description" name="info_text_field_attraction_description" rows="4" placeholder="Describe the attraction" >'.$val('Attraction_Description').'</textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_additional">Additional Information</label>';
$output_html .= '<textarea id="info_text_field_attraction_additional" name="info_text_field_attraction_additional" rows="3" placeholder="Add any other additional information that might be helpful." >'.$val('Attraction_Additional_Info').'</textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_pakring">Parking</label>';
$output_html .= '<textarea id="info_text_field_attraction_parking" name="info_text_field_attraction_pakring" rows="3" placeholder="If there is anything about you need to share about parking at this attraction, do so here." >'.$val('Attraction_Parking').'</textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<p id="local_attraction_form_message" class="event_settings_error" style="display:none;"></p>';
$output_html .= '</div>';
$output_html .= '<div class="col-6 col-12-xsmall">';
$output_html .= '<a href="#" id="submit_new_local_attraction_button" event_id="'.$event_id.'" attraction_id="'.($attraction ? $attraction_id : '').'" class="button primary fit">'.($attraction ? 'Save Changes' : 'Add Attraction').'</a>';
$output_html .= '</div>';
$output_html .= '<div class="col-6 col-12-xsmall">';
$output_html .= '<a href="#" id="cancel_local_attraction_button" class="button fit">Cancel</a>';
$output_html .= '</div>';





    
  $output_html .= '</div> ';  
  $output_html .= "</div>";  
  if ($attraction && $attraction['Attraction_State'] != '') {
    // Select the attraction's state in the list above
    $state_option = '<option value="'.$val('Attraction_State').'">';
    $output_html = str_replace($state_option, '<option value="'.$val('Attraction_State').'" selected>', $output_html);
  }
  $results[] = $output_html;
  closeDB($conn);
	echo json_encode($results);	


}

/* ************************************************************************ */
/* Funciton:    EnterNewAttractionCategory                                  */
/* Description: This functions adds a new local attraction category to the  */
/*              database.                                                   */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */ 
function EnterNewAttractionCategory() {
    $cat_title =$_POST['title'];
    $cat_name = $_POST['name'];
    $results = array();

    // Attempt an insertion with SQL injection protection
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $insert_query = "INSERT INTO `nsx_meetup_attraction_categories` (`Category_Name`, `Category_Title`) VALUES (:cat_name,:cat_title)";
    $sth = $dbh->prepare($insert_query);
    $sth->bindParam(':cat_title', $cat_title);
    $sth->bindParam(':cat_name', $cat_name);

    try {
      $dbh->beginTransaction();
      $sth->execute();
      $results[] = 'success';
      $results[] = $dbh->lastInsertId();
      $dbh->commit();
    } catch(PDOExecption $e) {

      $dbh->rollback();
      $results[] = 'fail';
      $results[] = $e->getMessage();
    }
    
    echo json_encode($results);
}

/* ************************************************************************ */
/* Funciton:    EnterNewAttractionDetails                                   */
/* Description: This functions adds a new local attraction details to the   */
/*              database.                                                   */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */ 
function EnterNewAttractionDetails() {
  $results = array();
  $attraction_data = json_decode($_POST['attraction_data'], true);

  $reservation_link = PostedAttractionReservationLink($attraction_data);
  if ($reservation_link === false) {
    return;
  }
  $image = AttractionImageUpload((int)($attraction_data['event_id'] ?? 0));
  if ($image === false) {
    return;
  }

  // Attempt an insertion with SQL injection protection
  $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
  $insert_query = "INSERT INTO `nsx_meetup_attractions` (`Event_ID`, `Attraction_Name`, `Attraction_Street_Address`, `Attraction_City`, `Attraction_State`, `Attraction_Zip`, `Attraction_Phone`, `Attraction_Email`, `Attraction_Web`, `Attraction_Category`, `Attraction_Description`, `Attraction_Additional_Info`, `Attraction_Parking`, `Attraction_Distance`, `Attraction_Hotel_Reservation_Link`) VALUES (:event_id,:attr_name,:attr_addr,:attr_city,:attr_state,:attr_zip,:attr_phone,:attr_email,:attr_web,:attr_category,:attr_desc,:attr_additional,:attr_parking,:attr_distance,:attr_reservation_link)";
  $sth = $dbh->prepare($insert_query);

  $sth->bindParam(':event_id',$attraction_data['event_id']);
  $sth->bindParam(':attr_name',$attraction_data['name']);
  $sth->bindParam(':attr_addr',$attraction_data['street']);
  $sth->bindParam(':attr_city',$attraction_data['city']);
  $sth->bindParam(':attr_state',$attraction_data['state']);
  $sth->bindParam(':attr_zip',$attraction_data['zip']);
  $sth->bindParam(':attr_phone',$attraction_data['phone']);
  $sth->bindParam(':attr_email',$attraction_data['email']);
  $sth->bindParam(':attr_web',$attraction_data['web']);
  $sth->bindParam(':attr_category',$attraction_data['category']);
  $sth->bindParam(':attr_desc',$attraction_data['description']);
  $sth->bindParam(':attr_additional',$attraction_data['additional']);
  $sth->bindParam(':attr_parking',$attraction_data['parking']);
  $sth->bindParam(':attr_distance',$attraction_data['distance']);
  $sth->bindParam(':attr_reservation_link',$reservation_link);
  
  $saved_image = '';
  try {
    $dbh->beginTransaction();
    $sth->execute();
    $attraction_id = $dbh->lastInsertId();
    if ($image !== null) {
      $saved_image = SaveAttractionImage($image, $attraction_id);
      if ($saved_image === '') {
        throw new RuntimeException('saving the picture failed');
      }
      $dbh->prepare("UPDATE `nsx_meetup_attractions` SET `Attraction_Image` = ? WHERE `Attraction_ID` = ?")->execute([$saved_image, $attraction_id]);
    }
    $dbh->commit();
    $results[] = 'success';
    $results[] = $attraction_id;
  } catch (Exception $e) {
    $dbh->rollback();
    if ($saved_image !== '' && is_file(__DIR__.'/'.$saved_image)) {
      unlink(__DIR__.'/'.$saved_image);
    }
    error_log('EnterNewAttractionDetails: '.$e->getMessage());
    $results[] = 'fail';
    $results[] = 'The attraction could not be added. Please try again.';
  }

  echo json_encode($results);

}
/* ************************************************************************ */
/* Function:    PostedAttractionReservationLink                             */
/* Description: The posted hotel reservation link, checked. Only an http(s) */
/*              address is saved, since it becomes a link on the event's    */
/*              page.                                                       */
/* Returns:     string, or false after echoing a 'fail' response            */
/* ************************************************************************ */
function PostedAttractionReservationLink($attraction_data) {
  $reservation_link = trim((string)($attraction_data['reservation_link'] ?? ''));
  if ($reservation_link !== '' && AttractionReservationLink($reservation_link) === '') {
    echo json_encode(['fail', 'The hotel reservation link must be a web address starting with http:// or https://']);
    return false;
  }
  if (strlen($reservation_link) > 2048) {
    echo json_encode(['fail', 'The hotel reservation link is too long (2048 characters at most).']);
    return false;
  }
  return $reservation_link;
}

/* ************************************************************************ */
/* Function:    SaveLocalAttraction                                         */
/* Description: Saves changes to one of the posted event's attractions.     */
/* Returns:     JSON ['success', id] or ['fail', message]                   */
/* ************************************************************************ */
function SaveLocalAttraction() {
  $event_id = (int)($_POST['event_id'] ?? 0);
  $attraction_data = json_decode((string)($_POST['attraction_data'] ?? ''), true);
  if (!is_array($attraction_data)) {
    echo json_encode(['fail', 'The attraction could not be read. Please try again.']);
    return;
  }
  $attraction_id = (int)($attraction_data['attraction_id'] ?? 0);
  $name = trim((string)($attraction_data['name'] ?? ''));
  $category = trim((string)($attraction_data['category'] ?? ''));
  if ($name === '' || $category === '') {
    echo json_encode(['fail', 'Every attraction needs a name and a category.']);
    return;
  }
  $reservation_link = PostedAttractionReservationLink($attraction_data);
  if ($reservation_link === false) {
    return;
  }

  $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
  $sth = $dbh->prepare("SELECT `Attraction_Image` FROM `nsx_meetup_attractions` WHERE `Attraction_ID` = ? AND `Event_ID` = ?");
  $sth->execute([$attraction_id, $event_id]);
  $old_image = $sth->fetchColumn();
  if ($old_image === false) {
    echo json_encode(['fail', 'That attraction could not be found. It may have been deleted.']);
    return;
  }
  $old_image = (string)$old_image;
  $image = AttractionImageUpload($event_id);
  if ($image === false) {
    return;
  }
  $new_image = (($_POST['remove_image'] ?? '') === '1') ? '' : $old_image;
  $saved_image = '';
  if ($image !== null) {
    $saved_image = SaveAttractionImage($image, $attraction_id);
    if ($saved_image === '') {
      echo json_encode(['fail', 'The picture could not be saved. Please try again.']);
      return;
    }
    $new_image = $saved_image;
  }

  $field = function ($key) use ($attraction_data) { return trim((string)($attraction_data[$key] ?? '')); };
  $sth = $dbh->prepare("UPDATE `nsx_meetup_attractions` SET `Attraction_Name` = ?, `Attraction_Street_Address` = ?, `Attraction_City` = ?, `Attraction_State` = ?, `Attraction_Zip` = ?, `Attraction_Phone` = ?, `Attraction_Email` = ?, `Attraction_Web` = ?, `Attraction_Category` = ?, `Attraction_Description` = ?, `Attraction_Additional_Info` = ?, `Attraction_Parking` = ?, `Attraction_Distance` = ?, `Attraction_Hotel_Reservation_Link` = ?, `Attraction_Image` = ? WHERE `Attraction_ID` = ? AND `Event_ID` = ?");
  try {
    $sth->execute([$name, $field('street'), $field('city'), $field('state'), $field('zip'), $field('phone'), $field('email'), $field('web'), $category,
                   $field('description'), $field('additional'), $field('parking'), $field('distance'), $reservation_link, $new_image, $attraction_id, $event_id]);
  } catch (PDOException $e) {
    if ($saved_image !== '' && is_file(__DIR__.'/'.$saved_image)) {
      unlink(__DIR__.'/'.$saved_image);
    }
    error_log('SaveLocalAttraction: '.$e->getMessage());
    echo json_encode(['fail', 'The attraction could not be saved. Please try again.']);
    return;
  }
  if ($old_image !== '' && $old_image !== $new_image) {
    DeleteAttractionImage($old_image, $attraction_id);
  }
  echo json_encode(['success', $attraction_id]);
}

/* ************************************************************************ */
/* Function:    DeleteLocalAttraction                                       */
/* Description: Deletes one of the posted event's attractions.              */
/* Returns:     JSON ['success', id] or ['fail', message]                   */
/* ************************************************************************ */
function DeleteLocalAttraction() {
  $event_id = (int)($_POST['event_id'] ?? 0);
  $attraction_id = (int)($_POST['attraction_id'] ?? 0);

  $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
  try {
    $sth = $dbh->prepare("SELECT `Attraction_Image` FROM `nsx_meetup_attractions` WHERE `Attraction_ID` = ? AND `Event_ID` = ?");
    $sth->execute([$attraction_id, $event_id]);
    $old_image = (string)$sth->fetchColumn();
    $sth = $dbh->prepare("DELETE FROM `nsx_meetup_attractions` WHERE `Attraction_ID` = ? AND `Event_ID` = ?");
    $sth->execute([$attraction_id, $event_id]);
  } catch (PDOException $e) {
    error_log('DeleteLocalAttraction: '.$e->getMessage());
    echo json_encode(['fail', 'The attraction could not be deleted. Please try again.']);
    return;
  }
  if ($sth->rowCount() == 0) {
    echo json_encode(['fail', 'That attraction could not be found. It may already have been deleted.']);
    return;
  }
  if ($old_image !== '') {
    DeleteAttractionImage($old_image, $attraction_id);
  }
  echo json_encode(['success', $attraction_id]);
}

/* ************************************************************************ */
/* Function:    AttractionImageUpload                                       */
/* Description: Checks the posted picture (if any) and makes sure the       */
/*              event's attractions picture folder exists.                  */
/* Returns:     null when no picture was posted, false after echoing a      */
/*              'fail' response, else the upload (CheckFrontpageImageUpload)*/
/*              plus 'dir' - the site-relative folder it goes in.           */
/* ************************************************************************ */
function AttractionImageUpload($event_id) {
  if (($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  if (!function_exists('imagecreatetruecolor')) {
    error_log('AttractionImageUpload: the GD extension is not available, so pictures cannot be resized');
    echo json_encode(['fail', 'Picture uploads are not available on this server (the PHP GD extension is missing).']);
    return false;
  }
  $image = CheckFrontpageImageUpload($_FILES['image']);
  if ($image['error'] !== '') {
    echo json_encode(['fail', $image['error']]);
    return false;
  }
  $sth = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup')->prepare("SELECT `EVENT_Directory` FROM `nsx_meetup_events` WHERE `EVENT_ID` = ?");
  $sth->execute([(int)$event_id]);
  $directory = (string)$sth->fetchColumn();
  $dir = EventAttractionImageDirectory($directory);
  if ($dir === '' || !is_dir(__DIR__.'/events/'.$directory)) {
    echo json_encode(['fail', 'This event\'s folder (events/'.$directory.'/) could not be found, so the picture has nowhere to go.']);
    return false;
  }
  if (!is_dir(__DIR__.'/'.$dir) && !mkdir(__DIR__.'/'.$dir, 0755, true)) {
    error_log('AttractionImageUpload: could not create '.$dir);
    echo json_encode(['fail', 'The picture could not be saved. Please try again.']);
    return false;
  }
  return $image + ['dir' => $dir];
}

/* ************************************************************************ */
/* Function:    SaveAttractionImage                                         */
/* Description: Saves a checked picture (AttractionImageUpload) for an      */
/*              attraction as attraction_<id>_<date>_<time>, scaled to fit  */
/*              ATTRACTION_IMAGE_MAX_WIDTH x _HEIGHT.                       */
/* Returns:     the site-relative path, or '' on failure                    */
/* ************************************************************************ */
function SaveAttractionImage(array $image, $attraction_id) {
  $filename = SaveFrontpageImage($image['tmp'], $image['type'], __DIR__.'/'.$image['dir'], 'attraction_'.(int)$attraction_id.'_'.date('Ymd_His'),
                                 ATTRACTION_IMAGE_MAX_HEIGHT, ATTRACTION_IMAGE_MAX_WIDTH);
  return ($filename === '') ? '' : $image['dir'].'/'.$filename;
}

/* ************************************************************************ */
/* Function:    DeleteAttractionImage                                       */
/* Description: Deletes an attraction's picture file - only an upload made  */
/*              by this tool for that attraction, never a shared image.     */
/* ************************************************************************ */
function DeleteAttractionImage($path, $attraction_id) {
  $path = (string)$path;
  if (preg_match('#^events/[A-Za-z0-9_-]+/images/attractions/attraction_'.(int)$attraction_id.'_\d{8}_\d{6}\.(jpg|png)$#', $path) && is_file(__DIR__.'/'.$path)) {
    unlink(__DIR__.'/'.$path);
  }
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).
?>