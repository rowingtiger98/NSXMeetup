<?php
  require_once __DIR__ . '/databaseFunctions.php';

  if(isset($_POST['method'])) {
    $_POST['method']();
  }

/* ************************************************************************ */
/* Funciton:    RenderOpeningPageInformation                                 */
/* Description: This function returns the local sttractions for the topic   */
/*              selected by the user.                                       */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */  
function RenderOpeningPageInformation() {
	$event_id = $_POST['event_id'];
  $editable = $_POST['editable'];
	$results = array();	

  if ($editable == 'true') {
    $category_links  = '<ul id="local_attractions_category_menu"><li id="local_attraction_link_create_new" class="local_attraction_link" event_id="'.$event_id.'" section_name="create"><i class="icon fa-plus-square" style="padding-right:0.25em;"></i>Add New Attraction</li>';
  } else {
    $category_links = '<ul id="local_attractions_category_menu">';
  }
  $opening_category = '';
  $category_container = '';
  
  $output_html = '<div id="local_attractions_continer" class="weekend_agenda_container">';

  if($editable == 'true') {

  } else {
    $output_html = '<div id="local_attractions_continer" class="weekend_agenda_container">';
    $output_html .= 	'<h1 class="major">Local Attrations</h1><p>Listed below are different things to see and do, both NSX related and non-NSX related,
      while you\'re in the area. There are also resources that we hope you will not need, like Urgent Care and Mechanics. If you are looking for some information 
    and don\'t see it below, please reach out and we will see what we can find for you!. </p>';
  }

  $output_html .= '<div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">';
  $output_html .= '<div id="local_attractions_menu" class="col-3 col-12-xsmall">';

	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_attraction_categories` WHERE `Event_ID`= '%s' ORDER BY `Category_ID`", $event_id);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if($opening_category == '') {
                $opening_category = $row['Category_Name'];
                $category_links .= '<li id="local_attraction_link_'.$row['Category_Name'].'" class="local_attraction_link active" event_id="'.$event_id.'" section_name="'.$row['Category_Name'].'">'.$row['Category_Title'].'</li>';
            } else {            
                $category_links .= '<li id="local_attraction_link_'.$row['Category_Name'].'" class="local_attraction_link" event_id="'.$event_id.'" section_name="'.$row['Category_Name'].'">'.$row['Category_Title'].'</li>';
            }

            if ($row['Category_Name'] == $opening_category) {
                $category_container .= '<div id="local_attraction_container_'.$row['Category_Name'].'" class="local_attraction_container active" section_name="'.$row['Category_Name'].'">';
                $attr_query = sprintf("SELECT * from `nsx_meetup_attractions` WHERE `Event_ID`= '%s' AND `Attraction_Category` = '%s'", $event_id, $row['Category_Name']);
                $attr_result = $conn->query($attr_query);
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
  $event_id = $_POST['event_id'];
	$section_name = $_POST['section_name'];
	$results = array();	
  $output_html = '';
  $output_html .= '<div id="local_attraction_container_'.$section_name.'" class="local_attraction_container active" section_name="'.$section_name.'">';
      
	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_attractions` WHERE `Event_ID`= '%s' AND `Attraction_Category` = '%s'", $event_id, $section_name);
	$result = $conn->query($query);
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
/* Funciton:    BuildIndividualLocalAttaractionContainer                    */
/* Description: This builds a single local attraction container from the    */
/*              row of database information.                                */
/* Inputs:      $row = Database information for a single attraction.        */
/* Returns:     HTML                                        				        */
/* ************************************************************************ */  
function BuildIndividualLocalAttaractionContainer($row) {

    $output_html = '<div id="individual_local_attraction_'.$row['Attraction_ID'].'" class="individual_local_attraction_container">';
    $output_html .= '<div class="row gtr-25 gtr-uniform">';

    if ($row['Attraction_Image'] == '') {
      $output_html .= ' <div class="col-12 col-12-xsmall">';
      $output_html .= '  <h4>'.$row['Attraction_Name'].'</h4>';
      $output_html .= '  <p>'.$row['Attraction_Description'].' <a href="#" id="show_more_attraction_information_'.$row['Attraction_ID'].'" class="show_more_attraction_information_link" attraction_id="'.$row['Attraction_ID'].'" action="more" style="font-size:0.8em;font-style:italic;">(Show More Information)</a></p>';
      $output_html .= '  <div id="more_attraction_information_container_'.$row['Attraction_ID'].'" class="more_attraction_information_container">';
      $output_html .= FormatIndividualAttractionInformation($row);
      $output_html .= '  </div>';
      $output_html .= ' </div>';
      $output_html .= ' <div class="col-12 col-12-xsmall" style="border-bottom:1px solid #ffffff;margin:0 0 0.5em 0.5em;"></div>';    
    } else {
      $output_html .= '<div class="col-2 col-12-xsmall">';    
      $output_html .= ' <span class="image fit"><img src="assets/images/attractions/'.$row['Event_ID'].'/'.$row['Attraction_Image'].'"></span>';
      $output_html .= '</div>';
      $output_html .= '<div class="col-10 col-12-xsmall">'; 
      $output_html .= ' <h4>'.$row['Attraction_Name'].'</h4>';
      $output_html .= ' <p>'.$row['Attraction_Description'].'  <a href="#" id="show_more_attraction_information_'.$row['Attraction_ID'].'" class="show_more_attraction_information_link" attraction_id="'.$row['Attraction_ID'].'" action="more" style="font-size:0.8em;font-style:italic;">(Show More Information)</a></p>';
      $output_html .= ' <div id="more_attraction_information_container_'.$row['Attraction_ID'].'" class="more_attraction_information_container">';
      $output_html .= FormatIndividualAttractionInformation($row);
      $output_html .= ' </div>';         
      $output_html .= '</div>';
      $output_html .= ' <div class="col-12 col-12-xsmall" style="border-bottom:1px solid #ffffff;margin:0 0 0.5em 0.5em;"></div>';  
    }
  
    $output_html .= "</div>";
    return $output_html;

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

  if ($row['Attraction_Google_Map_Link	'] != '') {
    $output_html .= ' <label class="attr_more_info_lable" for="attr_addr_'.$row['Attraction_ID'].'">Google Maps</label><span class="attr_more_info_field" name="attr_addr_'.$row['Attraction_ID'].'">'.$row['Attraction_Google_Map_Link	'].'</span>';
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
  $event_id = $_POST['event_id'];
  $section_name = $_POST['section_name'];
  $results = array();	
  $output_html = '';
  $output_html .= '<div id="local_attraction_container_create" class="local_attraction_container active" event_id="'.$event_id.'" section_name="create">';
  $output_html .= '<div class="row gtr-25 gtr-uniform">';

  //Get the list of categories
  $output_html .= '<div class="col-6 col-12-xsmall">';  
  $output_html .= '<select name="new_local_attraction_category" id="new_local_attraction_category" style="color:rgba(255, 255, 255, 0.7)";>';
  $output_html .= '<option value="" selected disabled hidden>Select the category</option>';

	$conn = connectDB('kirbypar_nsx_meetup');
	$query = sprintf("SELECT * from `nsx_meetup_attraction_categories` WHERE `Event_ID`= '%s' ORDER BY `Category_ID`", $event_id);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $output_html .= '<option value="'.$row['Category_Name'].'">'.$row['Category_Title'].'</option>';
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
$output_html .= '<input type="text" name="name" id="info_text_field_attraction_name" value="" placeholder="Name" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="distance" id="info_text_field_attraction_distance" value="" placeholder="Distance To Event Site" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="website" id="info_text_field_attraction_website" value="" placeholder="Web Site" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="email" id="info_text_field_attraction_email" value="" autocomplete="off" placeholder="Email Address" />';
$output_html .= '</div>';
$output_html .= '<div class="col-4 col-12-xsmall">';
$output_html .= '<input type="text" name="phone" id="info_text_field_attraction_phone" value="" placeholder="Phone Number" />';
$output_html .= '</div>';
$output_html .= '<div class="col-5 col-12-xsmall">';
$output_html .= '<input type="text" name="street" id="info_text_field_attraction_street" value="" placeholder="Street Address" />';
$output_html .= '</div>';
$output_html .= '<div class="col-3 col-12-xsmall">';
$output_html .= '<input type="text" name="city" id="info_text_field_attraction_city" value="" placeholder="City" />';
$output_html .= '</div>';
$output_html .= '<div class="col-2 col-12-xsmall">';
$output_html .= '<select name="state" id="info_select_field_attraction_state" style="color:rgba(255, 255, 255, 0.7)";>';
$output_html .= '    <option value="" selected disabled hidden>State</option>';
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
$output_html .= '<input type="text" name="zip" id="info_text_field_attraction_zip" value="" placeholder="Zip" />';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_description">Attraction Description</label>';
$output_html .= '<textarea id="info_text_field_attraction_description" name="info_text_field_attraction_description" rows="4" placeholder="Describe the attraction" ></textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_additional">Additional Information</label>';
$output_html .= '<textarea id="info_text_field_attraction_additional" name="info_text_field_attraction_additional" rows="3" placeholder="Add any other additional information that might be helpful." ></textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<label for="info_text_field_attraction_pakring">Parking</label>';
$output_html .= '<textarea id="info_text_field_attraction_parking" name="info_text_field_attraction_pakring" rows="3" placeholder="If there is anything about you need to share about parking at this attraction, do so here." ></textarea>';
$output_html .= '</div>';
$output_html .= '<div class="col-12 col-12-xsmall">';
$output_html .= '<a href="#" id="submit_new_local_attraction_button" event_id="'.$event_id.'" class="button primary fit">Add New Attaraction</a>';
$output_html .= '</div>';





    
  $output_html .= '</div> ';  
  $output_html .= "</div>";  
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
    $event_id = $_POST['event_id'];
    $cat_title =$_POST['title'];
    $cat_name = $_POST['name'];
    $results = array();

    // Attempt an insertion with SQL injection protection
    $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
    $insert_query = "INSERT INTO `nsx_meetup_attraction_categories` (`Event_ID`,`Category_Name`, `Category_Title`) VALUES (:event_id,:cat_name,:cat_title)";
    $sth = $dbh->prepare($insert_query);
    $sth->bindParam(':event_id', $event_id);
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

  // Attempt an insertion with SQL injection protection
  $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
  $insert_query = "INSERT INTO `nsx_meetup_attractions` (`Event_ID`, `Attraction_Name`, `Attraction_Street_Address`, `Attraction_City`, `Attraction_State`, `Attraction_Zip`, `Attraction_Phone`, `Attraction_Email`, `Attraction_Web`, `Attraction_Category`, `Attraction_Description`, `Attraction_Additional_Info`, `Attraction_Parking`, `Attraction_Distance`) VALUES (:event_id,:attr_name,:attr_addr,:attr_city,:attr_state,:attr_zip,:attr_phone,:attr_email,:attr_web,:attr_category,:attr_desc,:attr_additional,:attr_parking,:attr_distance)";
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
// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).
?>