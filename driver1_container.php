<?php 
    if(isset($_SESSION['login_user'])){
        include 'ACCOUNTManagement.php';
        $account_information = RetrieveUserInformation($_SESSION['login_user']);
        if ($account_information != 'NONE') {
            $account_user_name = $account_information->NSX_Full_Name;
            $account_user_email = $account_information->NSX_Email_Address;
            $account_user_cell_number = $account_information->NSX_Cell_Number;
            $account_user_address = $account_information->NSX_Address;
            $account_user_city = $account_information->NSX_City;
            $account_user_state = $account_information->NSX_State;
            $account_user_zip = $account_information->NSX_Zip;
            $account_selected_display_name = $account_information->NSX_Event_Display_Name;
            $account_user_facebook = $account_information->NSX_User_Facebook;
            $account_user_instagram = $account_information->NSX_User_Instagram;
            $account_user_tiktok = $account_information->NSX_User_TikTok;
            $account_user_twitter = $account_information->NSX_User_Twitter;
            $account_user_NSXPrime = $account_information->NSX_User_NSXPrime;
            $account_user_car_information = $account_information->NSX_Car_Information;
            $account_user_event_shared_information = $account_information->NSX_Event_Shared_Information;

        } else {
            $account_user_name = '';
            $account_user_email = '';
            $account_user_cell_number = '';
            $account_user_address = '';
            $account_user_city = '';
            $account_user_state = '';
            $account_user_zip = '';
            $account_selected_display_name = '';
            $account_user_facebook = '';
            $account_user_instagram = '';
            $account_user_tiktok = '';
            $account_user_twitter = '';
            $account_user_NSXPrime = '';
            $account_user_car_information = '';
            $account_user_event_shared_information = '';
        }

    } else {
        $account_user_name = '';
        $account_user_email = '';
        $account_user_cell_number = '';
        $account_user_address = '';
        $account_user_city = '';
        $account_user_state = '';
        $account_user_zip = '';
        $account_selected_display_name = '';
        $account_user_facebook = '';
        $account_user_instagram = '';
        $account_user_tiktok = '';
        $account_user_twitter = '';
        $account_user_NSXPrime = '';
        $account_user_car_information = '';
        $account_user_event_shared_information = '';        
    } 
    
    $provinces = [
        'AB' => 'Alberta',
        'BC' => 'British Columbia',
        'MB' => 'Manitoba',
        'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador',
        'NS' => 'Nova Scotia',
        'NT' => 'Northwest Territories',
        'NU' => 'Nunavut',
        'ON' => 'Ontario',
        'PE' => 'Prince Edward Island',
        'QC' => 'Quebec',
        'SK' => 'Saskatchewan',
        'YT' => 'Yukon'
    ];    
?>	

<div id="driver_one_contact_container" class="event_registration_sub_container active" create_account="no">
    <div id="event_registration_sub_container_header_driver1" class="event_registration_sub_container_header">
        Driver
    </div>
    <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">
        <div class="col-4 col-12-xsmall">
            <input type="text" name="name" id="info_text_field_driver1_name" value="<?php print($account_user_name)?>" placeholder="Name" />
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="email" id="info_text_field_driver1_email" value="<?php print($account_user_email)?>" autocomplete="off" placeholder="Email Address" />
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="cell" id="info_text_field_driver1_cell" value="<?php print($account_user_cell_number)?>" placeholder="Cell Phone Number" />
        </div>
        <div class="col-5 col-12-xsmall">
            <input type="text" name="street" id="info_text_field_driver1_street" value="<?php print($account_user_address)?>" placeholder="Street Address" />
        </div>
        <div class="col-3 col-12-xsmall">
            <input type="text" name="city" id="info_text_field_driver1_city" value="<?php print($account_user_city)?>" placeholder="City" />
        </div>
        <div class="col-2 col-12-xsmall">
            <select name="state" id="info_select_field_driver1_state" style="color:rgba(255, 255, 255, 0.7)";>
                <option value="" <?php echo ($account_user_state == "") ? "selected " : "";?>disabled hidden>State / Province</option>
                <option value="AL" <?php echo ($account_user_state == "AL") ? "selected " : "";?>>AL</option>
                <option value="AK" <?php echo ($account_user_state == "AK") ? "selected " : "";?>>AK</option>
                <option value="AZ" <?php echo ($account_user_state == "AZ") ? "selected " : "";?>>AZ</option>
                <option value="AR" <?php echo ($account_user_state == "AR") ? "selected " : "";?>>AR</option>
                <option value="CA" <?php echo ($account_user_state == "CA") ? "selected " : "";?>>CA</option>
                <option value="CO" <?php echo ($account_user_state == "CO") ? "selected " : "";?>>CO</option>
                <option value="CT" <?php echo ($account_user_state == "CT") ? "selected " : "";?>>CT</option>
                <option value="DE" <?php echo ($account_user_state == "DE") ? "selected " : "";?>>DE</option>
                <option value="DC" <?php echo ($account_user_state == "DC") ? "selected " : "";?>>DC</option>
                <option value="FL" <?php echo ($account_user_state == "FL") ? "selected " : "";?>>FL</option>
                <option value="GA" <?php echo ($account_user_state == "GA") ? "selected " : "";?>>GA</option>
                <option value="HI" <?php echo ($account_user_state == "HI") ? "selected " : "";?>>HI</option>
                <option value="ID" <?php echo ($account_user_state == "ID") ? "selected " : "";?>>ID</option>
                <option value="IL" <?php echo ($account_user_state == "IL") ? "selected " : "";?>>IL</option>
                <option value="IN" <?php echo ($account_user_state == "IN") ? "selected " : "";?>>IN</option>
                <option value="IA" <?php echo ($account_user_state == "IA") ? "selected " : "";?>>IA</option>
                <option value="KS" <?php echo ($account_user_state == "KS") ? "selected " : "";?>>KS</option>
                <option value="KY" <?php echo ($account_user_state == "KY") ? "selected " : "";?>>KY</option>
                <option value="LA" <?php echo ($account_user_state == "LA") ? "selected " : "";?>>LA</option>
                <option value="ME" <?php echo ($account_user_state == "ME") ? "selected " : "";?>>ME</option>
                <option value="MD" <?php echo ($account_user_state == "MD") ? "selected " : "";?>>MD</option>
                <option value="MA" <?php echo ($account_user_state == "MA") ? "selected " : "";?>>MA</option>
                <option value="MI" <?php echo ($account_user_state == "MI") ? "selected " : "";?>>MI</option>
                <option value="MN" <?php echo ($account_user_state == "MN") ? "selected " : "";?>>MN</option>
                <option value="MS" <?php echo ($account_user_state == "MS") ? "selected " : "";?>>MS</option>
                <option value="MO" <?php echo ($account_user_state == "MO") ? "selected " : "";?>>MO</option>
                <option value="MT" <?php echo ($account_user_state == "MT") ? "selected " : "";?>>MT</option>
                <option value="NE" <?php echo ($account_user_state == "NE") ? "selected " : "";?>>NE</option>
                <option value="NV" <?php echo ($account_user_state == "NV") ? "selected " : "";?>>NV</option>
                <option value="NH" <?php echo ($account_user_state == "NH") ? "selected " : "";?>>NH</option>
                <option value="NJ" <?php echo ($account_user_state == "NJ") ? "selected " : "";?>>NJ</option>
                <option value="NM" <?php echo ($account_user_state == "NM") ? "selected " : "";?>>NM</option>
                <option value="NY" <?php echo ($account_user_state == "NY") ? "selected " : "";?>>NY</option>
                <option value="NC" <?php echo ($account_user_state == "NC") ? "selected " : "";?>>NC</option>
                <option value="ND" <?php echo ($account_user_state == "ND") ? "selected " : "";?>>ND</option>
                <option value="OH" <?php echo ($account_user_state == "OH") ? "selected " : "";?>>OH</option>
                <option value="OK" <?php echo ($account_user_state == "OK") ? "selected " : "";?>>OK</option>
                <option value="OR" <?php echo ($account_user_state == "OR") ? "selected " : "";?>>OR</option>
                <option value="PA" <?php echo ($account_user_state == "PA") ? "selected " : "";?>>PA</option>
                <option value="RI" <?php echo ($account_user_state == "RI") ? "selected " : "";?>>RI</option>
                <option value="SC" <?php echo ($account_user_state == "SC") ? "selected " : "";?>>SC</option>
                <option value="SD" <?php echo ($account_user_state == "SD") ? "selected " : "";?>>SD</option>
                <option value="TN" <?php echo ($account_user_state == "TN") ? "selected " : "";?>>TN</option>
                <option value="TX" <?php echo ($account_user_state == "TX") ? "selected " : "";?>>TX</option>
                <option value="UT" <?php echo ($account_user_state == "UT") ? "selected " : "";?>>UT</option>
                <option value="VT" <?php echo ($account_user_state == "VT") ? "selected " : "";?>>VT</option>
                <option value="VA" <?php echo ($account_user_state == "VA") ? "selected " : "";?>>VA</option>
                <option value="WA" <?php echo ($account_user_state == "WA") ? "selected " : "";?>>WA</option>
                <option value="WV" <?php echo ($account_user_state == "WV") ? "selected " : "";?>>WV</option>
                <option value="WI" <?php echo ($account_user_state == "WI") ? "selected " : "";?>>WI</option>
                <option value="WY" <?php echo ($account_user_state == "WY") ? "selected " : "";?>>WY</option>
                <option value="" disabled style="font-weight:bold; color:#000;">Province</option>
                <option value="" disabled>-------------</option>
                <option value="AB" <?php echo ($account_user_state == "AB") ? "selected " : "";?>>AB</option>
                <option value="BC" <?php echo ($account_user_state == "BC") ? "selected " : "";?>>BC</option>
                <option value="MB" <?php echo ($account_user_state == "MB") ? "selected " : "";?>>MB</option>
                <option value="NB" <?php echo ($account_user_state == "NB") ? "selected " : "";?>>NB</option>
                <option value="NL" <?php echo ($account_user_state == "NL") ? "selected " : "";?>>NL</option>
                <option value="NS" <?php echo ($account_user_state == "NS") ? "selected " : "";?>>NS</option>
                <option value="NT" <?php echo ($account_user_state == "NT") ? "selected " : "";?>>NT</option>
                <option value="NU" <?php echo ($account_user_state == "NU") ? "selected " : "";?>>NU</option>
                <option value="ON" <?php echo ($account_user_state == "ON") ? "selected " : "";?>>ON</option>
                <option value="PE" <?php echo ($account_user_state == "PE") ? "selected " : "";?>>PE</option>
                <option value="QC" <?php echo ($account_user_state == "QC") ? "selected " : "";?>>QC</option>
                <option value="SK" <?php echo ($account_user_state == "SK") ? "selected " : "";?>>SK</option>
                <option value="YT" <?php echo ($account_user_state == "YT") ? "selected " : "";?>>YT</option>





            </select>								
        </div>
        <div class="col-2 col-12-xsmall">
            <input type="text" name="zip" id="info_text_field_driver1_zip" value="<?php echo $account_user_zip; ?>" placeholder="Zip / Postal Code" />
        </div>  

        <!-- Driver One Car Information -->
        <?php if(isset($_SESSION['login_user']) && $account_user_car_information != '') { 
        
            $car_information_object = json_decode($account_user_car_information);
            if(isset($car_information_object->car_make)) {
        ?>
                <div id="nsx_gen_response_container_driver1_select_gen" class="col-4 col-12-xsmall">
                    <select id="nsx_gen_response_gen_selection_driver1" name="generation_selection_driver1" driver="driver1" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>
                        <option value="" disabled hidden>NSX Generation</option>
                        <option value="First_Generation">First Generation</option>
                        <option value="Second_Generation">Second Generation</option>
                        <option value="Other" selected>Not an NSX</option>
                    </select>
                </div>
                <div id="nsx_gen_response_container_driver1_nsx_year"  class="col-4 col-12-xsmall generation_choice">
                    <select name="model_year_driver1" id="nsx_gen_select_driver1_nsx_year" style="color:rgba(255, 255, 255, 0.7)";>
                    </select>
                </div>
                <div id="nsx_gen_response_container_driver1_nsx_color" class="col-4 col-12-xsmall generation_choice">
                    <select name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" style="color:rgba(255, 255, 255, 0.7)";>
                        <option value="" selected disabled hidden>Color</option>
                        <option value="red">Red</option>									
                        <option value="white">White</option>									
                        <option value="black">Black</option>									
                        <option value="blue">Blue</option>									
                        <option value="orange">Orange</option>									
                        <option value="gray">Gray</option>									
                        <option value="silver">Silver</option>									
                        <option value="yellow">Yellow</option>
                        <option value="green">Green</option>
                        <option value="purple">Purple</option>
                        <option value="other">Other</option>
                    </select>
                </div>                
                <div id="nsx_gen_response_container_driver1_other_make" class="col-3 col-12-xsmall generation_choice active">
                    <input type="text" name="other_make_driver1" id="nsx_gen_response_driver1_other_make" value="<?php echo $car_information_object->car_make; ?>" placeholder="Make" />
                </div>
                <div id="nsx_gen_response_container_driver1_other_model" class="col-3 col-12-xsmall generation_choice active">
                    <input type="text" name="other_model_driver1" id="nsx_gen_response_driver1_other_model" value="<?php echo $car_information_object->car_model; ?>" placeholder="Model" />
                </div>
                <div id="nsx_gen_response_container_driver1_other_year" class="col-2 col-12-xsmall generation_choice active">
                    <input type="text" name="other_year_driver1" id="nsx_gen_response_driver1_other_year" value="<?php echo $car_information_object->car_year; ?>" placeholder="Year" />
                </div>                 
        <?php                
            }  else {

                if (intval($car_information_object->car_year) <= 2016) {
                    $user_car_generation = 'First_Generation';
                } else {
                    $user_car_generation = 'Second_Generation';
                }
        ?>
                <div id="nsx_gen_response_container_driver1_select_gen" class="col-4 col-12-xsmall">
                <select id="nsx_gen_response_gen_selection_driver1" name="generation_selection_driver1" driver="driver1" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>
                    <option value="" disabled hidden>NSX Generation</option>
                    <option value="First_Generation" <?php echo ($user_car_generation == "First_Generation") ? "selected" : ""; ?>>First Generation</option>
                    <option value="Second_Generation" <?php echo ($user_car_generation == "Second_Generation") ? "selected" : ""; ?>>Second Generation</option>
                    <option value="Other">Not an NSX</option>
                </select>
                </div>
                <div id="nsx_gen_response_container_driver1_nsx_year"  class="col-4 col-12-xsmall generation_choice active">
                    <select name="model_year_driver1" id="nsx_gen_select_driver1_nsx_year" style="color:rgba(255, 255, 255, 0.7)";>
                        <option value="<?php echo $car_information_object->car_year; ?>" selected><?php echo $car_information_object->car_year; ?></option>
                    </select>
                </div>
                <div id="nsx_gen_response_container_driver1_nsx_color" class="col-4 col-12-xsmall generation_choice active">
                    <select name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" style="color:rgba(255, 255, 255, 0.7)";>
                        <option value=""disabled hidden>Color</option>
                        <option value="red" <?php echo ($car_information_object->car_color == "red") ? "selected " : "";?>>Red</option>									
                        <option value="white" <?php echo ($car_information_object->car_color == "white") ? "selected " : "";?>>White</option>									
                        <option value="black" <?php echo ($car_information_object->car_color == "black") ? "selected " : "";?>>Black</option>									
                        <option value="blue" <?php echo ($car_information_object->car_color == "blue") ? "selected " : "";?>>Blue</option>									
                        <option value="orange" <?php echo ($car_information_object->car_color == "orange") ? "selected " : "";?>>Orange</option>									
                        <option value="gray" <?php echo ($car_information_object->car_color == "gray") ? "selected " : "";?>>Gray</option>									
                        <option value="silver" <?php echo ($car_information_object->car_color == "silver") ? "selected " : "";?>>Silver</option>									
                        <option value="yellow" <?php echo ($car_information_object->car_color == "yellow") ? "selected " : "";?>>Yellow</option>
                        <option value="green" <?php echo ($car_information_object->car_color == "green") ? "selected " : "";?>>Green</option>
                        <option value="purple" <?php echo ($car_information_object->car_color == "purple") ? "selected " : "";?>>Purple</option>
                        <option value="other" <?php echo ($car_information_object->car_color == "other") ? "selected " : "";?>>Other</option>
                    </select>
                </div>

                <div id="nsx_gen_response_container_driver1_other_make" class="col-3 col-12-xsmall generation_choice">
                    <input type="text" name="other_make_driver1" id="nsx_gen_response_driver1_other_make" value="" placeholder="Make" />
                </div>
                <div id="nsx_gen_response_container_driver1_other_model" class="col-3 col-12-xsmall generation_choice">
                    <input type="text" name="other_model_driver1" id="nsx_gen_response_driver1_other_model" value="" placeholder="Model" />
                </div>
                <div id="nsx_gen_response_container_driver1_other_year" class="col-2 col-12-xsmall generation_choice">
                    <input type="text" name="other_year_driver1" id="nsx_gen_response_driver1_other_year" value="" placeholder="Year" />
                </div>        
        
        <?php
            }                 
        ?>


        <?php } else { ?>
            <div id="nsx_gen_response_container_driver1_select_gen" class="col-4 col-12-xsmall">
            <select id="nsx_gen_response_gen_selection_driver1" name="generation_selection_driver1" driver="driver1" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>
                <option value="" selected disabled hidden>NSX Generation</option>
                <option value="First_Generation">First Generation</option>
                <option value="Second_Generation">Second Generation</option>
                <option value="Other">Not an NSX</option>
            </select>
            </div>
            <div id="nsx_gen_response_container_driver1_nsx_year"  class="col-4 col-12-xsmall generation_choice">
                <select name="model_year_driver1" id="nsx_gen_select_driver1_nsx_year" style="color:rgba(255, 255, 255, 0.7)";>
                </select>
            </div>
            <div id="nsx_gen_response_container_driver1_nsx_color" class="col-4 col-12-xsmall generation_choice">
                <select name="model_color_driver1" id="nsx_gen_select_driver1_nsx_color" style="color:rgba(255, 255, 255, 0.7)";>
                    <option value="" selected disabled hidden>Color</option>
                    <option value="red">Red</option>									
                    <option value="white">White</option>									
                    <option value="black">Black</option>									
                    <option value="blue">Blue</option>									
                    <option value="orange">Orange</option>									
                    <option value="gray">Gray</option>									
                    <option value="silver">Silver</option>									
                    <option value="yellow">Yellow</option>
                    <option value="green">Green</option>
                    <option value="purple">Purple</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div id="nsx_gen_response_container_driver1_other_make" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_make_driver1" id="nsx_gen_response_driver1_other_make" value="" placeholder="Make" />
            </div>
            <div id="nsx_gen_response_container_driver1_other_model" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_model_driver1" id="nsx_gen_response_driver1_other_model" value="" placeholder="Model" />
            </div>
            <div id="nsx_gen_response_container_driver1_other_year" class="col-2 col-12-xsmall generation_choice">
                <input type="text" name="other_year_driver1" id="nsx_gen_response_driver1_other_year" value="" placeholder="Year" />
            </div>


        <?php } ?>



    </div>


    <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">        
        <!-- Driver 1 Social Media Information -->
        <div class="col-12 col-12-xsmall">
            <div id="account_creation_sub_container_header_social_driver1" class="event_registration_sub_container_header">
                Display Information
                <p class="registration_text">Select the name you would like displayed when people see that you've registered (<span style="color:#ffcc00;">defaults to Full Name</span>).
                    We wanted to give you the choice as to what information is shared with others about your participation. Below are different options you can 
                    select to share about your participation (even Anonymous). <span style="color:#ff0000;font-weight: 600;"> NOTE: </span>If you select an option that does not have any information, your full name will be used in it's place.
                </p>
            </div>
        </div>

        <div class="col-12 col-12-xsmall">
            <select name="info_select_field_driver1_event_display_name" id="info_select_field_driver1_event_display_name">
                <option value="fullname" selected disabled hidden>Select Your Displayed Name</option>
                <option value="fullname" <?php echo ($account_selected_display_name == "fullname") ? "selected " : "";?>>Full Name</option>
                <option value="email" <?php echo ($account_selected_display_name == "email") ? "selected " : "";?>>Email Address</option>
                <option value="nsxprime" <?php echo ($account_selected_display_name == "nsxprime") ? "selected " : "";?>>NSX Prime Handle</option>
                <!-- <option value="facebook" <?php echo ($account_selected_display_name == "facebook") ? "selected " : "";?>>Facebook Handle</option> -->
                <option value="instagram" <?php echo ($account_selected_display_name == "instagram") ? "selected " : "";?>>Instagram Handle</option>
                <option value="tiktok" <?php echo ($account_selected_display_name == "tiktok") ? "selected " : "";?>>TikTok Handle</option>
                <option value="twitter" <?php echo ($account_selected_display_name == "twitter") ? "selected " : "";?>>X Account</option>
                <option value="anonymous" <?php echo ($account_selected_display_name == "anonymous") ? "selected " : "";?>>Anonymous</option>
            </select>                                                     
        </div>

        <div class="col-4 col-12-xsmall">
            <input type="text" name="nsxprime" id="optional_text_field_driver1_nsxprime" value="<?php echo $account_user_NSXPrime;?>" placeholder="NSX Prime Handle" />
        </div>                                         
        <div class="col-4 col-12-xsmall">
            <input type="text" name="facebook" id="optional_text_field_driver1_facebook" value="<?php echo $account_user_facebook;?>" placeholder="Facebook Link" />
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="instagram" id="optional_text_field_driver1_instagram" value="<?php echo $account_user_instagram;?>" placeholder="Instagram Handle" />
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="tiktok" id="optional_text_field_driver1_tiktok" value="<?php echo $account_user_tiktok;?>" placeholder="TikTok Handle" />
        </div>
        <div class="col-4 col-12-xsmall">
            <input type="text" name="twitter" id="optional_text_field_driver1_twitter" value="<?php echo $account_user_twitter;?>" placeholder="X Handle" />
        </div> 
        
        <div class="col-12 col-12-xsmall display_individual_sub_question">
            When you register for an event, select all the additional information you would wish to share with other registered participants.
        </div>

        <!-- Information to share about the participant -->

        <?php if(isset($_SESSION['login_user']) && $account_user_event_shared_information != '') { 
            $contact_information_object = json_decode($account_user_event_shared_information);            
        ?>          
            <div class="col-12 col-12-xsmall">
                <input type="checkbox" id="info_field_user_display_choice_driver1_vehicle" key="vehicle" driver="driver1" choice="<?php echo $contact_information_object->vehicle; ?>" name="vehicle" <?php echo ($contact_information_object->vehicle == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_vehicle">Vehicle Information</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_state" key="state" driver="driver1" choice="<?php echo $contact_information_object->state; ?>" name="state" <?php echo ($contact_information_object->state== "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_state">State</label>                         
                <input type="checkbox" id="info_field_user_display_choice_driver1_email" key="email" driver="driver1" choice="<?php echo $contact_information_object->email; ?>" name="email" <?php echo ($contact_information_object->email == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_email">Email Address</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_facebook" key="facebook" driver="driver1" choice="<?php echo $contact_information_object->facebook; ?>" name="facebook" <?php echo ($contact_information_object->facebook == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_facebook">Facebook Link</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_instagram" key="instagram" driver="driver1" choice="<?php echo $contact_information_object->instagram; ?>" name="instagram" <?php echo ($contact_information_object->instagram == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_instagram">Instagram Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_tiktok" key="tiktok" driver="driver1" choice="<?php echo $contact_information_object->tiktok; ?>" name="tiktok" <?php echo ($contact_information_object->tiktok == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_tiktok">TikTok Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_twitter" key="twitter" driver="driver1" choice="<?php echo $contact_information_object->twitter; ?>" name="twitter" <?php echo ($contact_information_object->twitter == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_twitter">X Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_nsxprime" key="nsxprime" driver="driver1" choice="<?php echo $contact_information_object->nsxprime; ?>" name="nsxprime" <?php echo ($contact_information_object->nsxprime == "yes") ? "checked":"";?>>
                <label for="info_field_user_display_choice_driver1_nsxprime">NSX Prime ID</label>                                                             
            </div> 
        <?php } else { ?>
            <div class="col-12 col-12-xsmall">
                <input type="checkbox" id="info_field_user_display_choice_driver1_vehicle" key="vehicle" driver="driver1" choice="yes" name="vehicle" checked>
                <label for="info_field_user_display_choice_driver1_vehicle">Vehicle Information</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_state" key="state" driver="driver1" choice="yes" name="state" checked>
                <label for="info_field_user_display_choice_driver1_state">State</label>                         
                <input type="checkbox" id="info_field_user_display_choice_driver1_email" key="email" driver="driver1" choice="no" name="email">
                <label for="info_field_user_display_choice_driver1_email">Email Address</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_facebook" key="facebook" driver="driver1" choice="no" name="facebook">
                <label for="info_field_user_display_choice_driver1_facebook">Facebook Link</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_instagram" key="instagram" driver="driver1" choice="no" name="instagram">
                <label for="info_field_user_display_choice_driver1_instagram">Instagram Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_tiktok" key="tiktok" driver="driver1" choice="no" name="tiktok">
                <label for="info_field_user_display_choice_driver1_tiktok">TikTok Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_twitter" key="twitter" driver="driver1" choice="no" name="twitter">
                <label for="info_field_user_display_choice_driver1_twitter">X Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver1_nsxprime" key="nsxprime" driver="driver1" choice="no" name="nsxprime">
                <label for="info_field_user_display_choice_driver1_nsxprime">NSX Prime ID</label>                                                             
            </div> 
        <?php } ?>
         
    </div>

    <?php
        if (!(isset($_SESSION['login_user'])) && ($event_data['EVENT_Enable_Account_Creation'])) {
    ?>
        <!-- Driver One Create NSXMeetup Account -->
        <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">
            <div class="col-12 col-12-xsmall">
                <div id="account_creation_sub_container_header_driver1_nsxmeetup_accnt" class="event_registration_sub_container_header">
                    Create an NSX Meetup Account (Optional)
                    <input type="checkbox" id="info_field_create_nsxmeetup_newuser_accnt" driver="driver1" name="create_nsxmeetup_accnt">
                    <label for="info_field_create_nsxmeetup_newuser_accnt">Click this box to create an NSX Meetup User Account. Your <span style="color:#ffcc00;">EMAIL Address</span> becomes your user ID. Doing so will allow you to edit your registrations, user preferences, sign up for future events with a single click and even create your own NSX Meetup Event. </label>
                </div>
                <div id="password_information_container_driver1" class="row gtr-25 gtr-uniform nsxdriverinforcontainer createaccountcontainer">
                    <div class="col-6 col-12-xsmall">
                        <input type="password" name="password" id="info_text_field_newuser_password" value="" autocomplete="new-password" placeholder="Password" />
                    </div>
                    <div class="col-6 col-12-xsmall">
                        <input type="password" name="password2" id="info_text_field_newuser_password_confirm" value="" autocomplete="new-password" placeholder="Password Confirmation" />
                    </div>
                </div>              
            </div>
        </div> 
    <?php            
        }
    ?>


    <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">
        <!-- Driver One Register another person -->
        <div class="col-12 col-12-xsmall" style="margin-top:1em;">
			<span style="font-weight:600; color:#ffcc00;">Add a passenger or another driver (Optional)</span><br>
            <input type="checkbox" id="info_field_driver1_additional_guest" driver="driver1" cost="<?php echo $event_data['EVENT_Base_Fee']*100; ?>" name="additional_guest"><label for="info_field_driver1_additional_guest">Click the check box to register another person as either a passenger or the driver of another car.</label>
        </div>
    </div>
</div>