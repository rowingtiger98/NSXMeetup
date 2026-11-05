<div id="event_info_container" class="event_registration_sub_container active">
    <div class="event_registration_sub_container_header">
        <div class="row gtr-uniform">
            <div id="event_registration_sub_container_header_event_info" class="col-3 col-12-xsmall">
                Other Information
            </div>
            <div class="col-9 col-12-xsmall">
                <P style="font-size:0.85em;">For each checkbox question below, click the box to indicate a YES answer. If you are registering a passenger, or another driver, please 
                also check the appropriate boxes for them as well. An empty box is assumed to mean NO. For each dropdown window, please select the best answer for yourself and, if
                applicable, the best answer for any other registrant you are entering.</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table style="margin-top:1.5em;">
                <thead>
                    <tr>
                        <th width="60%"></th>
                        <th id="column_driver1" class="table_driver active" width="15%">Driver</th>
                        <th id="column_driver2" class="table_driver" width="15%">Passenger</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- NSXCA Member -->
                    <tr>
                        <td>Check the box if you are an active NSX Club of America member?</td>
                        <td id="column_driver1" class="table_driver active">
                            <input type="checkbox" id="info_checkbox_field_driver1_nsxca_member" driver="driver1" key="nsxca_member" choice="no" name="driver1_nsxca_member">
                            <label for="info_checkbox_field_driver1_nsxca_member"></label>												
                        </td>
                        <td id="column_driver2" class="table_driver">
                            <input type="checkbox" id="info_checkbox_field_driver2_nsxca_member" driver="driver2" key="nsxca_member" choice="no" name="driver2_nsxca_member">
                            <label for="info_checkbox_field_driver2_nsxca_member"></label>													
                        </td>
                    </tr>

                    <!-- Starting Location -->
                    <tr>
                        <td>Select the point where you plan to join the group for the event.</td>
                        <td id="column_driver1" class="table_driver active">
                            <select id="info_question_select_field_driver1_starting_location" driver="driver1" key="starting_location" name="driver1_starting_location">
                                <option value="" selected disabled >Select Start</option>
                                <option value="artisticbean">Artistic Bean</option>
                                <option value="129hub">129 Hub</option>
                                <option value="overlook">The Overlook</option>
                                <option value="tapoco">Tapoco Lodge</option>
                            </select>                                                    
                        </td>
                        <td id="column_driver2" class="table_driver">
                            <select id="info_question_select_field_driver2_starting_location" driver="driver2" key="starting_location" name="driver2_starting_location">
                            <option value="" selected disabled >Select Start</option>
                                <option value="artisticbean">Artistic Bean</option>
                                <option value="129hub">129 Hub</option>
                                <option value="overlook">The Overlook</option>
                                <option value="tapoco">Tapoco Lodge</option>
                            </select>    											
                        </td>
                    </tr>	


                    <!-- Saturday Lunch -->
                    <tr>
                        <td>Check the box if you are planning to attend Saturday Lunch at Tapoca Lodge?</td>
                        <td id="column_driver1" class="table_driver active">
                            <input type="checkbox" id="info_checkbox_field_driver1_sat_lunch" driver="driver1" key="sat_lunch" choice="no" name="driver1_sat_lunch">
                            <label for="info_checkbox_field_driver1_sat_lunch"></label>												
                        </td>
                        <td id="column_driver2" class="table_driver">
                            <input type="checkbox" id="info_checkbox_field_driver2_sat_lunch" driver="driver2" key="sat_lunch" choice="no" name="driver2_sat_lunch">
                            <label for="info_checkbox_field_driver2_sat_lunch"></label>													
                        </td>
                    </tr>	
                    
                    <!-- Saturday Dinner -->
                    <tr>
                        <td>Check the box if you are planning to attend the Saturday dinner at Lakeside Tavern? </td>
                        <td id="column_driver1" class="table_driver active">
                            <input type="checkbox" id="info_checkbox_field_driver1_sat_dinner" driver="driver1" key="sat_dinner" choice="no" name="driver1_sat_dinner">
                            <label for="info_checkbox_field_driver1_sat_dinner"></label>												
                        </td>
                        <td id="column_driver2" class="table_driver">
                            <input type="checkbox" id="info_checkbox_field_driver2_sat_dinner" driver="driver2" key="sat_dinner" choice="no" name="driver2_sat_dinner">
                            <label for="info_checkbox_field_driver2_sat_dinner"></label>													
                        </td>
                    </tr>

                    <!-- Cars and Coffee -->
                    <tr>
                        <td>Check the box if you are planning to attend Sundays Cars and Coffee event?</td>
                        <td id="column_driver1" class="table_driver active">
                            <input type="checkbox" id="info_checkbox_field_driver1_carsncoffee" driver="driver1" key="carsncoffee" choice="no" name="driver1_carsncoffee">
                            <label for="info_checkbox_field_driver1_carsncoffee"></label>												
                        </td>
                        <td id="column_driver2" class="table_driver">
                            <input type="checkbox" id="info_checkbox_field_driver2_carsncoffee" driver="driver2" key="carsncoffee" choice="no" name="driver2_carsncoffee">
                            <label for="info_checkbox_field_driver2_carsncoffee"></label>													
                        </td>
                    </tr>	

                </tbody>
            </table>
        </div>								
    </div>
</div>