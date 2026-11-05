    <!-- Driver Two Container -->
    <div id="driver_two_contact_container" class="event_registration_sub_container">
        <div class="event_registration_sub_container_header">
            <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">
                <div id="event_registration_sub_container_header_driver2" class="col-3 col-12-xsmall">
                    Passenger
                </div>
                <div id="driver_two_role_selection_container" role="riding" class="col-9 col-12-xsmall">
                        <input type="radio" id="driver_two_role_passenger" name="driver_two_role_radio" cost="<?php echo $event_data['EVENT_Base_Fee']; ?>" value="riding" checked>
                        <label for="driver_two_role_passenger">Passenger</label>
                        <input type="radio" id="driver_two_role_driver" name="driver_two_role_radio" cost="<?php echo $event_data['EVENT_Base_Fee']; ?>" value="driving">
                        <label for="driver_two_role_driver">Driving their own car</label>
                        <?php if ($event_data['EVENT_Enable_Child_Discount']) { ?>
                            <input type="radio" id="driver_two_role_child" name="driver_two_role_radio" cost="<?php echo $event_data['EVENT_Child_Base_Fee']; ?>" value="child">
                            <label for="driver_two_role_child"><?php echo $event_data['EVENT_Child_Checkbox_Text']; ?></label>
                        <?php } ?>



                </div>								
            </div>
            
        </div>
        <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">
            <div class="col-4 col-12-xsmall">
                <input type="text" name="name" id="info_text_field_driver2_name" value="" placeholder="Name" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="email" id="info_text_field_driver2_email" value="" placeholder="Email Address" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="cell" id="info_text_field_driver2_cell" value="" placeholder="Cell Phone Number" />
            </div>
            <div class="col-5 col-12-xsmall">
                <input type="text" name="street" id="info_text_field_driver2_street" value="" placeholder="Street Address" />
            </div>
            <div class="col-3 col-12-xsmall">
                <input type="text" name="city" id="info_text_field_driver2_city" value="" placeholder="City" />
            </div>
            <div class="col-2 col-12-xsmall">
                <select name="state" id="info_select_field_driver2_state"  style="color:rgba(255, 255, 255, 0.7)";>
                    <option value="" selected disabled hidden>State / Province</option>
                    <option value="AL">AL</option>
                    <option value="AK">AK</option>
                    <option value="AZ">AZ</option>
                    <option value="AR">AR</option>
                    <option value="CA">CA</option>
                    <option value="CO">CO</option>
                    <option value="CT">CT</option>
                    <option value="DE">DE</option>
                    <option value="DC">DC</option>
                    <option value="FL">FL</option>
                    <option value="GA">GA</option>
                    <option value="HI">HI</option>
                    <option value="ID">ID</option>
                    <option value="IL">IL</option>
                    <option value="IN">IN</option>
                    <option value="IA">IA</option>
                    <option value="KS">KS</option>
                    <option value="KY">KY</option>
                    <option value="LA">LA</option>
                    <option value="ME">ME</option>
                    <option value="MD">MD</option>
                    <option value="MA">MA</option>
                    <option value="MI">MI</option>
                    <option value="MN">MN</option>
                    <option value="MS">MS</option>
                    <option value="MO">MO</option>
                    <option value="MT">MT</option>
                    <option value="NE">NE</option>
                    <option value="NV">NV</option>
                    <option value="NH">NH</option>
                    <option value="NJ">NJ</option>
                    <option value="NM">NM</option>
                    <option value="NY">NY</option>
                    <option value="NC">NC</option>
                    <option value="ND">ND</option>
                    <option value="OH">OH</option>
                    <option value="OK">OK</option>
                    <option value="OR">OR</option>
                    <option value="PA">PA</option>
                    <option value="RI">RI</option>
                    <option value="SC">SC</option>
                    <option value="SD">SD</option>
                    <option value="TN">TN</option>
                    <option value="TX">TX</option>
                    <option value="UT">UT</option>
                    <option value="VT">VT</option>
                    <option value="VA">VA</option>
                    <option value="WA">WA</option>
                    <option value="WV">WV</option>
                    <option value="WI">WI</option>
                    <option value="WY">WY</option>
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
                <input type="text" name="zip" id="info_text_field_driver2_zip" value="" placeholder="Zip / Postal Code" />
            </div>

            <!-- Driver Twp Car Information -->
            <div id="nsx_gen_response_container_driver2_select_gen" class="col-4 col-12-xsmall generation_choice">
                <select id="nsx_gen_response_gen_selection_driver2" name="generation_selection_driver2" driver="driver2" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>
                    <option value="" selected disabled hidden>NSX Generation</option>
                    <option value="First_Generation">First Generation</option>
                    <option value="Second_Generation">Second Generation</option>
                    <option value="Other">Not an NSX</option>
                </select>
            </div>
            <div id="nsx_gen_response_container_driver2_nsx_year"  class="col-4 col-12-xsmall generation_choice">
                <!-- <input type="text" name="model_year_driver2" id="nsx_gen_response_driver2_nsx_year" value="" placeholder="Year" /> -->
                <select name="model_year_driver2" id="nsx_gen_select_driver2_nsx_year" style="color:rgba(255, 255, 255, 0.7)";>
                    <!-- <option value="" selected disabled hidden>Year</option>
                    <option value="1991">1991</option>									
                    <option value="1992">1992</option>									
                    <option value="1993">1993</option>									
                    <option value="1994">1994</option>									
                    <option value="1995">1995</option>									
                    <option value="1996">1996</option>									
                    <option value="1997">1997</option>									
                    <option value="1998">1998</option>									
                    <option value="1999">1999</option>									
                    <option value="2000">2000</option>									
                    <option value="2001">2001</option>									
                    <option value="2002">2002</option>									
                    <option value="2003">2003</option>									
                    <option value="2004">2004</option>									
                    <option value="2005">2005</option>									
                    <option value="2017">2017</option>									
                    <option value="2018">2018</option>									
                    <option value="2019">2019</option>									
                    <option value="2020">2020</option>									
                    <option value="2021">2021</option>									
                    <option value="2022">2022</option>									
                    <option value="2023">2023</option>									 -->
                </select>									
            </div>
            <div id="nsx_gen_response_container_driver2_nsx_color" class="col-4 col-12-xsmall generation_choice">
                <!-- <input type="text" name="model_color_driver2" id="nsx_gen_response_driver2_nsx_color" value="" placeholder="Color" /> -->
                <select name="model_color_driver2" id="nsx_gen_select_driver2_nsx_color" style="color:rgba(255, 255, 255, 0.7)";>
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
            <div id="nsx_gen_response_container_driver2_other_make" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_make_driver2" id="nsx_gen_response_driver2_other_make" value="" placeholder="Make" />
            </div>
            <div id="nsx_gen_response_container_driver2_other_model" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_model_driver2" id="nsx_gen_response_driver2_other_model" value="" placeholder="Model" />
            </div>
            <div id="nsx_gen_response_container_driver2_other_year" class="col-2 col-12-xsmall generation_choice">
                <input type="text" name="other_year_driver2" id="nsx_gen_response_driver2_other_year" value="" placeholder="Year" />
            </div>								
        </div>
        <div class="row gtr-25 gtr-uniform nsxdriverinforcontainer">        
            <!-- Driver 2 Social Media Information -->
            <div class="col-12 col-12-xsmall">
                <div id="account_creation_sub_container_header_social_driver2" class="event_registration_sub_container_header">
                    Display Information
                    <p class="registration_text">Select the name you would like displayed when people see that you've registered (<span style="color:#ffcc00;">defaults to Full Name</span>).
                        We wanted to give you the choice as to what information is shared with others about your participation. Below are different options you can 
                        select to share about your participation (even Anonymous). <span style="color:#ff0000;font-weight: 600;"> NOTE: </span>If you select an option that does not have any information, your full name will be used in it's place.
                    </p>
                </div>
            </div>

            <div class="col-12 col-12-xsmall">
                <select name="info_select_field_driver2_event_display_name" id="info_select_field_driver2_event_display_name">
                    <option value="fullname" selected disabled hidden>Select Your Displayed Name</option>
                    <option value="fullname">Full Name</option>
                    <option value="email">Email Address</option>
                    <option value="nsxprime">NSX Prime Handle</option>
                    <!-- <option value="facebook">Facebook Handle</option> -->
                    <option value="instagram">Instagram Handle</option>
                    <option value="tiktok">TikTok Handle</option>
                    <option value="twitter">X Account</option>
                    <option value="anonymous">Anonymous</option>
                </select>                                                     
            </div>

            <div class="col-4 col-12-xsmall">
                <input type="text" name="nsxprime" id="optional_text_field_driver2_nsxprime" value="" placeholder="NSX Prime Handle" />
            </div>                                         
            <div class="col-4 col-12-xsmall">
                <input type="text" name="facebook" id="optional_text_field_driver2_facebook" value="" placeholder="Facebook Link" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="instagram" id="optional_text_field_driver2_instagram" value="" placeholder="Instagram Handle" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="tiktok" id="optional_text_field_driver2_tiktok" value="" placeholder="TikTok Handle" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="twitter" id="optional_text_field_driver2_twitter" value="" placeholder="X Handle" />
            </div> 
            
            <div class="col-12 col-12-xsmall display_individual_sub_question">
                When you register for an event, select all the aadditional information you would wish to share with other registered participants.
            </div>
            <div class="col-12 col-12-xsmall">
                <input type="checkbox" id="info_field_user_display_choice_driver2_vehicle" key="vehicle" driver="driver2" choice="yes" name="vehicle" checked>
                <label for="info_field_user_display_choice_driver2_vehicle">Vehicle Information</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_state" key="state" driver="driver2" choice="yes" name="state" checked>
                <label for="info_field_user_display_choice_driver2_state">State</label>                             
                <input type="checkbox" id="info_field_user_display_choice_driver2_email" key="email" driver="driver2" choice="no" name="email">
                <label for="info_field_user_display_choice_driver2_email">Email Address</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_facebook" key="facebook" driver="driver2" choice="no" name="facebook">
                <label for="info_field_user_display_choice_driver2_facebook">Facebook Link</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_instagram" key="instagram" driver="driver2" choice="no" name="instagram">
                <label for="info_field_user_display_choice_driver2_instagram">Instagram Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_tiktok" key="tiktok" driver="driver2" choice="no" name="tiktok">
                <label for="info_field_user_display_choice_driver2_tiktok">TikTok Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_twitter" key="twitter" driver="driver2" choice="no" name="twitter">
                <label for="info_field_user_display_choice_driver2_twitter">X Handle</label>
                <input type="checkbox" id="info_field_user_display_choice_driver2_nsxprime" key="nsxprime" driver="driver2" choice="no" name="nsxprime">
                <label for="info_field_user_display_choice_driver2_nsxprime">NSX Prime ID</label>                                                             
            </div>          
        </div>
    </div>                        