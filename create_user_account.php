<div id="driver_one_contact_container" class="account_creation_sub_container active">
    <div id="account_creation_sub_container_header_newuser" class="account_creation_sub_container_header">
        Account Information
        <p class="registration_text">Create a username that you will use to login to nsxmeetup.com. Usernames need to be unique in the system. All fields will become enabled, and allow for the registration process to contiune, once that the name you have selected is verified.</p>
    </div>
    <div class="row gtr-0">
        <div class="col-12 col-12-xsmall">
            <input type="text" name="name" id="info_text_field_newuser_username" value="" placeholder="User Name" />
        </div>
        <div class="col-12 col-12-xsmall" style="margin-top: 0.5em;">
            <a href="#" id="newuser_verify_unique_username_button" class="button primary small fit" style="float:right;">Verify Username</a>
        </div>
    </div>

    <div id="verified_username_further_information" class="verified_username_further_information">
        <div id="account_creation_sub_container_header_newuser" class="account_creation_sub_container_header">
            Account Password
            <p class="registration_text">Create a password for your account. Enter the password and then re-enter into the password confirmation section.</p>
        </div>                                
        <div class="row gtr-uniform">                                                            
            <div class="col-6 col-12-xsmall">
                <input type="password" name="password" id="info_text_field_newuser_password" value="" placeholder="Password" />
            </div>
            <div class="col-6 col-12-xsmall">
                <input type="password" name="password2" id="info_text_field_newuser_password_confirm" value="" placeholder="Password Confirmation" />
            </div>                                
        </div>

        <div id="account_creation_sub_container_header_newuser" class="account_creation_sub_container_header">
            User Information
            <p class="registration_text">This information will be passed onto the organizers for any NSX Meetup event that you decide to go to. The information in this section will not be made public at any time unless you choose to make it so.</p>
        </div>
        <div class="row gtr-uniform">
            <div class="col-4 col-12-xsmall">
                <input type="text" name="name" id="info_text_field_newuser_name" value="" placeholder="Name" />
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="email" id="info_text_field_newuser_email" value="" placeholder="Email Address" readonly/>
            </div>
            <div class="col-4 col-12-xsmall">
                <input type="text" name="cell" id="info_text_field_newuser_cell" value="" placeholder="Cell Phone Number" />
            </div>
            <div class="col-5 col-12-xsmall">
                <input type="text" name="street" id="info_text_field_newuser_street" value="" placeholder="Street Address" />
            </div>
            <div class="col-3 col-12-xsmall">
                <input type="text" name="city" id="info_text_field_newuser_city" value="" placeholder="City" />
            </div>
            <div class="col-2 col-12-xsmall">
                <select name="state" id="info_select_field_newuser_state" style="color:rgba(255, 255, 255, 0.7)";>
                    <option value="" selected disabled hidden>State</option>
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
                </select>								
            </div>
            <div class="col-2 col-12-xsmall">
                <input type="text" name="zip" id="info_text_field_newuser_zip" value="" placeholder="Zip" />
            </div>
        </div>

        <div id="account_creation_sub_container_header_newuser_vehicle" class="account_creation_sub_container_header">
            Vehicle Information
            <p class="registration_text">Please enter your vehiclle information. If you are driving an NSX, simply select your generation, year and color. If you are not driving an NSX, simply select "Not an NSX" and provide the make, model and year. 
            <strong>Update:</strong> We will be adding the ability to register multiple cars in the future. For now, simply enter your primary ride.</p>
        </div>

        <div class="row gtr-uniform">   
            <div id="nsx_gen_response_container_primary_select_gen" class="col-4 col-12-xsmall">
            <select id="nsx_gen_response_gen_selection_primary" name="generation_selection_vehicle_primary" vehicle="primary" current_choice="none" style="color:rgba(255, 255, 255, 0.7)";>
                <option value="" selected disabled hidden>NSX Generation</option>
                <option value="First_Generation">First Generation</option>
                <option value="Second_Generation">Second Generation</option>
                <option value="Other">Not an NSX</option>
            </select>
            </div>
            <div id="nsx_gen_response_container_primary_nsx_year"  class="col-4 col-12-xsmall generation_choice">
                <select name="model_year_newuser" id="nsx_gen_select_nsx_year_primary" style="color:rgba(255, 255, 255, 0.7)";>
                </select>
            </div>
            <div id="nsx_gen_response_container_primary_nsx_color" class="col-4 col-12-xsmall generation_choice">
                <select name="model_color_newuser" id="nsx_gen_select_nsx_color_primary" style="color:rgba(255, 255, 255, 0.7)";>
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

            <div id="nsx_gen_response_container_primary_other_make" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_make_newuser" id="nsx_gen_response_newuser_other_make" value="" placeholder="Make" />
            </div>
            <div id="nsx_gen_response_container_primary_other_model" class="col-3 col-12-xsmall generation_choice">
                <input type="text" name="other_model_newuser" id="nsx_gen_response_newuser_other_model" value="" placeholder="Model" />
            </div>
            <div id="nsx_gen_response_container_primary_other_year" class="col-2 col-12-xsmall generation_choice">
                <input type="text" name="other_year_newuser" id="nsx_gen_response_newuser_other_year" value="" placeholder="Year" />
            </div>
        </div>

        <div id="account_creation_sub_container_header_newuser_social" class="account_creation_sub_container_header">
            Social Networking Information
            <p class="registration_text">If you have any social networking account information you would like to make visable to other users, enter it here. You will be able to display or hide the this information at any time through your user's configuration interface.
        </div>
        <div class="row gtr-uniform">
            <div class="col-6 col-12-xsmall">
                <input type="text" name="nsxprime" id="optional_text_field_newuser_nsxprime" value="" placeholder="NSX Prime Handle" />
            </div>            
            <div class="col-6 col-12-xsmall">
                <input type="text" name="facebook" id="optional_text_field_newuser_facebook" value="" placeholder="Facebook" />
            </div>
            <div class="col-6 col-12-xsmall">
                <input type="text" name="instagram" id="optional_text_field_newuser_instagram" value="" placeholder="Instagram" />
            </div>
            <div class="col-6 col-12-xsmall">
                <input type="text" name="tiktok" id="optional_text_field_newuser_tiktok" value="" placeholder="TikTok" />
            </div>
            <div class="col-6 col-12-xsmall">
                <input type="text" name="twitter" id="optional_text_field_newuser_twitter" value="" placeholder="X Handle" />
            </div>                             
        </div>


        <div id="account_creation_sub_container_header_newuser_vehicle" class="account_creation_sub_container_header">
            Display Information
            <p class="registration_text">Here is where you will determine how you want the community to see you. Answer the following questions, or use the defaults, to let us know what we can share about you with the community.</p>
        </div>

        <div id="account_creation_sub_container_display_information" class="account_creation_sub_container_display_information">
            <div class="row gtr-0 account_creation_sub_container_display_information_question">
                <div class="col-12 col-12-xsmall">
                    <div class="row gtr-0 display_individual_question">
                        <div class="col-12 col-12-xsmall display_individual_sub_question">
                        Select the name you would like to be displayed for you when people see that you have registered for an event (<span style="color:#ffcc00;font-size:0.85em;">defaults to username</span>).
                        <span class="smaller_display_question_text">(<strong> NOTE: </strong>If you select an option that does not have any information, your username will be used in it's place. You can also select Anonymous.</span>)
                        </div>
                        <div class="col-12 col-12-xsmall">
                            <select name="event_display_name" id="info_select_field_newuser_event_display_name" style="color:rgba(255, 255, 255, 0.7)";>
                                <option value="username" selected disabled hidden>Select Your Displayed Name</option>
                                <option value="fullname">Full Name</option>
                                <option value="email">Email Address</option>
                                <option value="nsxprime">NSX Prime Handle</option>
                                <option value="instgram">Instagram Handle</option>
                                <option value="tiktok">TikTok Handle</option>
                                <option value="twitter">X Account</option>
                                <option value="anonymous">Anonymous</option>
                            </select>                                                     
                        </div>                                                   
                    </div>

                    <div class="row gtr-0 display_individual_question">
                        <div class="col-12 col-12-xsmall display_individual_sub_question">
                        When you register for an event, select all the aadditional information you would wish to share with other registered participants.
                        </div>
                        <div class="col-12 col-12-xsmall">
                            <input type="checkbox" id="info_field_user_display_choice_vehicle" key="vehicle" choice="yes" name="vehicle" checked>
                            <label for="info_field_user_display_choice_vehicle">Vehicle Information</label>
                            <input type="checkbox" id="info_field_user_display_choice_state" key="state" choice="yes" name="state" checked>
                            <label for="info_field_user_display_choice_state">State</label>                            
                            <input type="checkbox" id="info_field_user_display_choice_email" key="email" choice="no" name="email">
                            <label for="info_field_user_display_choice_email">Email Address</label>
                            <input type="checkbox" id="info_field_user_display_choice_facebook" key="facebook" choice="no" name="facebook">
                            <label for="info_field_user_display_choice_facebook">Facebook</label>
                            <input type="checkbox" id="info_field_user_display_choice_instagram" key="instagram" choice="no" name="instagram">
                            <label for="info_field_user_display_choice_instagram">Instagram Handle</label>
                            <input type="checkbox" id="info_field_user_display_choice_tiktok" key="tiktok" choice="no" name="tiktok">
                            <label for="info_field_user_display_choice_tiktok">TikTok Handle</label>
                            <input type="checkbox" id="info_field_user_display_choice_twitter" key="twitter" choice="no" name="twitter">
                            <label for="info_field_user_display_choice_twitter">X Account</label>
                            <input type="checkbox" id="info_field_user_display_choice_nsxprime" key="nsxprime" choice="no" name="nsxprime">
                            <label for="info_field_user_display_choice_nsxprime">NSX Prime ID</label>
                        </div>                                                   
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
