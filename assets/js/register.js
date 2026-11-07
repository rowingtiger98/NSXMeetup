/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckFacebookLinkFormatting 																*/
/* Description:	This function checks to see if the user entered a valid facebook link.              		*/
/* -------------------------------------------------------------------------------------------------------- */
function CheckFacebookLinkFormatting() {
	var content = $(this).val();
	if(content == '') {
		console.log("Nothing here, nothing to worry about");
	} else {
		var result = content.includes('facebook.com/');
		if(!(result)) {
			alert("'"+content +"' does not appear to be the full link to your facebook profile.Please enter the full link. \n\nExample: http://www.facebook.com/<profile_name> \n\nYou can also cut and paste the link from your browser that's open to your facebook profile.")
		}
	}
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckForExistingAccount 																    */
/* Description:	This function looks to see if a user already has an account when registering for an event.  */
/* -------------------------------------------------------------------------------------------------------- */
function CheckForExistingAccount() {
	console.log("check to see if the email adddress = "+$(this).val()+" already exist");
	var email = $(this).val();
	$.ajax({
	    url: 'ACCOUNTCreation.php',
	    type: 'POST',
	    data: {method: 'CheckForExistingUserAccount', email:email},
	    success: function(data) {
	  	//   alert(data);
		//   resolve(data);
	  	//   results = eval(data);
	    },	
	  });		
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	LoadDriveOneNSXChoces   																	*/
/* Description:	This function reacts to the register button be clicked. This is the mail click handler.		*/
/* -------------------------------------------------------------------------------------------------------- */
function LoadDriveOneNSXChoces(event) {
	console.log("User selected the car type for Driver 1");
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	RegisterForTheEvent																			*/
/* Description:	This function reacts to the register button be clicked. This is the mail click handler.		*/
/* -------------------------------------------------------------------------------------------------------- */
async function RegisterForTheEvent() {

	$("#event_registration_results_text").html("<h2>Processing....</h2>");
	var event_id = $(this).attr('event_id');
    var sold_out = $(this).attr('sold_out');

	// When the user clicks to register, we need to see if this person...
	// 1. Is a new user and wants to create and account
	// 2. Is a user that wants to just register as a guest
	// 3. Is a user that has already logged in and is registering for a new event. 

	// ----------------------------------------------------------------------------------------------
	// Register for the event as a guest
	// In this case, an account is going to be made for the individual, but the account type is
	// GUEST and there will be no password information saved. In the event that someone tries 
	// to log with this account, the attemp (for now) will fail with the appropriate message.
	// ----------------------------------------------------------------------------------------------
	var create_account = $("#driver_one_contact_container").attr('create_account');
	console.log('Create an account at signup = ' + create_account);

	var enter_results = false;
	var id = 0;
	console.log('Checking for missing fields');
	var ready = CheckForMissingFields();
	var new_user_ids = {'driver1':'', 'driver2':''};
	var entered = false;

	if (ready) {
		if (create_account == 'yes') {
			console.log('Create an account within the system');
			await EnterNewUserInformation(event_id)
				.then((data) => {
					console.log(data);
					account_results = eval(data);
					if(account_results[0] == "success") {
						console.log("New User entered into the NSX Meetup User Table with ID = "+account_results[1]);
						var user_account_id = account_results[1];
					} else {
						ready = false;
						console.log('Something failed in entering the user into the NSX users table.');
					}
				})
				.catch((error) => {
					console.log(error)
					ready = false;
				})			
		}
	}

	if(ready) {
		await EnterUserToParticipantDatabase('driver1', 'driving', event_id)
			.then((data) => {
				console.log(data);
				driver1_results = eval(data);
				if (driver1_results[0] === "success") {
					console.log('driver1 success');
					new_user_ids['driver1'] = driver1_results[1]
					entered = true;
				} else if (driver1_results[0] === "duplicate") {
				    alert(driver1_results[1]);
				    console.log(driver1_results[1]);
				} else {
					console.log('driver1 failure');
				}
			})
			.catch((error) => {
				console.log(error)
			})
		
		var additional_guest = document.getElementById('info_field_driver1_additional_guest').checked;
		if (additional_guest) {
			console.log('checking on driver 2');
			var role = $("#driver_two_role_selection_container").attr('role');
		        await EnterUserToParticipantDatabase('driver2', role, event_id)
		        	.then((data) => {
		        		driver2_results = eval(data);
		        		if (driver2_results[0] === "success") {
		        			console.log('driver2 success');
		        			new_user_ids['driver2'] = driver2_results[1]
		        			entered = true;
		        		} else if (driver2_results[0] === "duplicate") {
				            alert(driver2_results[1]);
				            console.log(driver2_results[1]);
				        } else {
		        			console.log('driver2 failure');
		        		}
		        		results = eval(data);
		        		console.log('driver2 = '+results[1]);
		        	})
		        	.catch((error) => {
		        		console.log(error)
		        	})						
		}		
		
		if(entered) {
			console.log("Calling CreateConfirmationPage with ids = " + new_user_ids)
			CreateConfirmationPage(new_user_ids, event_id, sold_out);

            console.log("Checking to see if these entrants have existing NSXMeetup accounts to updated their events attended");
            UpdateUserAttendedEvents(event_id, new_user_ids);
				
		} 
		else {
			alert('database entry failed');
		}
	} else {
		alert('Some of the required information is missing, please go back and fill in any box outlined in yellow.');
	}
	console.log('3');

}


/* -------------------------------------------------------------------------------------------------------- */
/* Function:	EnableCreateNewUserAccount         									    				    */
/* Description:	This functiondisplays or hides the option to create a user account from within an events    */
/*              registration page. This is separate from the option to create a user account directly.      */
/* -------------------------------------------------------------------------------------------------------- */
function EnableCreateNewUserAccount(event) {
    console.log('Create new user account from within event registration.');
	var checkbox = document.getElementById('info_field_create_nsxmeetup_newuser_accnt').checked;
	// Element is currently un-checked and has been checked
	if (checkbox) {
		$("#password_information_container_driver1").addClass("active");
        $("#driver_one_contact_container").attr('create_account', 'yes');
        $("#main_register_button").addClass('disabled');
        $("#main_register_button").html('Register - Password Mismatch');
		
	// Element is currently checked and has been un-checked
	} else {
		$("#password_information_container_driver1").removeClass("active");
        $("#driver_one_contact_container").attr('create_account', 'no');
        $("#main_register_button").removeClass('disabled');
        $("#main_register_button").html('Register');
        document.getElementById('info_text_field_newuser_password').value = '';
        document.getElementById('info_text_field_newuser_password_confirm').value = '' 
        document.getElementById('info_text_field_newuser_password_confirm').style.color="inherit";       
	}    
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CreateConfirmationPage   																    */
/* Description:	This function creates the confirmation page after a successful registration.                */
/* -------------------------------------------------------------------------------------------------------- */
function CreateConfirmationPage(new_user_ids, event_id, sold_out) {
	console.log('Create a confirmation page for user id for driver1 = '+new_user_ids['driver1']+ ' and driver2 = ' + new_user_ids['driver2'] + 'For event ID = '+event_id + ' Sold Out = '+sold_out);
    var collect_payment = $("#event_info_container").attr('collect_payments');

	return new Promise((resolve, reject) => {
	  $.ajax({
	    url: 'GENERALFunctions.php',
	    type: 'POST',
	    data: {method: 'CreateConfirmationPage', driver1:new_user_ids['driver1'], driver2:new_user_ids['driver2'], event_id:event_id, collect_payment:collect_payment, backbutton:'no', sold_out:sold_out},
	    success: function(data) {
		  resolve(data);
	  	  results = eval(data);
		  $("#event_registration_results_text").html(results[0]);
		  $("#event_registration_container").html(results[1]);
	    },
        error: function (error) {
          reject(error)
        },		
	  });	
	});	
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	UpdateUserAttendedEvents   																    */
/* Description:	This function creates the confirmation page after a successful registration.                */
/* -------------------------------------------------------------------------------------------------------- */
function UpdateUserAttendedEvents(event_id, new_user_ids) {
	console.log('checking for existing accounts for for driver1 = '+new_user_ids['driver1']+ ' and driver2 = ' + new_user_ids['driver2']);
	return new Promise((resolve, reject) => {
	  $.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'UpdateUserAttendedEvents', driver1:new_user_ids['driver1'], driver2:new_user_ids['driver2'], event_id:event_id},
	    success: function(data) {
		  resolve(data);
	  	  results = eval(data);
	    },
        error: function (error) {
          reject(error)
        },		
	  });	
	});	
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckForMissingFields   																    */
/* Description:	This function checks to make sure all the required information is in.                       */
/* -------------------------------------------------------------------------------------------------------- */
function CheckForMissingFields() {
	results = true;
	results = CheckForMissingDriverFields('driver1', 'driving');

	// Check to see 

	// If Driver One is done, check to see if there is a second entry.
	if (results){
	    var additional_guest = document.getElementById('info_field_driver1_additional_guest').checked;
	    if (additional_guest) {
	    	var role = $("#driver_two_role_selection_container").attr('role');
	    	results = CheckForMissingDriverFields('driver2', role);
	    }
	}
	
	return results
}


/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckPasswordMatch																	        */
/* Description:	This function compares the passwords sent to the server at user creation.		            */
/* -------------------------------------------------------------------------------------------------------- */
function CheckPasswordMatch() {
    var password1 = $("#info_text_field_newuser_password").val();
    var password2 = $("#info_text_field_newuser_password_confirm").val();
    var result = false;

        // If password not entered
        if (password2 == '') {
            console.log("Please enter Confirmation Password");
            result = false;
        }      
        // If Not same return False.    
        else if (password1 != password2) {
            console.log ("Password did not match: Please try again...")
            document.getElementById($(this).attr('id')).style.color="#ff0000";
            result = false;
        }

        // If same return True.
        else{
            console.log("Password Match: Welcome to GeeksforGeeks!")
            document.getElementById($(this).attr('id')).style.color="green";
            result = true;
        }

    if (result == true) {
        $("#main_register_button").removeClass('disabled');
        $("#main_register_button").html('Register');
    } else {
        $("#main_register_button").addClass('disabled');
        $("#main_register_button").html('Register - Password Mismatch');
    }
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToGuestTypeChange   																    */
/* Description:	This function adjust the total owed based upon a difference in base registration cost.      */
/* -------------------------------------------------------------------------------------------------------- */
function ReactToGuestTypeChange() {
	var current_subtotal = parseFloat($("#diver2_cost").attr('total'));
	console.log('Reg Change: starting cost for driver 2 = ' + current_subtotal);
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToAttendanceChoice   																    */
/* Description:	This function sets the attendance choice for a driver when clicked.                         */
/* -------------------------------------------------------------------------------------------------------- */
function ReactToAttendanceChoice() {
	var driver = $(this).attr('driver');
    var collect_payments = $("#event_info_container").attr('collect_payments');
	var key = $(this).attr('key');
	var id_val = $(this).attr('id');
    var accountable = $(this).attr('accounting');
    var cost = parseFloat($(this).attr('cost'));
	var checkbox = document.getElementById(id_val).checked;
    var description = $(this).attr('desc');

    var current_subtotal = parseFloat($("#"+driver+"_cost").attr('total'));

	if (checkbox) {	
		$("#info_checkbox_field_"+driver+"_"+key).attr('choice', 'yes');
        console.log('Add ', driver, ' : ', key, ' : ', accountable, ' : ', current_subtotal);
        if(collect_payments === 'yes' && accountable === 'yes') {
            // var new_subtotal = current_subtotal + cost;
            // console.log("The newly increased sub total = ", new_subtotal.toFixed(2));
            // $("#"+driver+"_cost").attr('total', new_subtotal.toFixed(2));
            // $("#"+driver+"_cost").html('$'+new_subtotal.toFixed(2));
            // $("#main_register_button").attr('reg_button_'+driver+'_total', new_subtotal.toFixed(2));
            shopping_cart_actions(key, cost * 100, description, driver, 'add');
			update_driver_totals(driver);
        }
	} else {
		$("#info_checkbox_field_"+driver+"_"+key).attr('choice', 'no');
        console.log('Remove ', driver, ' : ', key, ' : ', accountable, ' : ', current_subtotal);
        if(collect_payments === 'yes' && accountable === 'yes') {
            // var new_subtotal = current_subtotal - cost;
            // console.log("The newly descreaded sub total = ", new_subtotal.toFixed(2));
            // $("#"+driver+"_cost").attr('total', new_subtotal.toFixed(2));
            // $("#"+driver+"_cost").html('$'+new_subtotal.toFixed(2));
            // $("#main_register_button").attr('reg_button_'+driver+'_total', new_subtotal.toFixed(2));
            shopping_cart_actions(key, cost * 100, description, driver, 'remove');
			update_driver_totals(driver);

        }        
	}
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToSharedInformationChoice   														    */
/* Description:	This function sets the shared information choice for a driver when clicked.                 */
/* -------------------------------------------------------------------------------------------------------- */
function ReactToSharedInformationChoice() {
	var driver = $(this).attr('driver');
	var key = $(this).attr('key');
	var id_val = $(this).attr('id');
	var checkbox = document.getElementById(id_val).checked;
	if (checkbox) {	
		$("#info_field_user_display_choice_"+driver+"_"+key).attr('choice', 'yes');
	} else {
		$("#info_field_user_display_choice_"+driver+"_"+key).attr('choice', 'no');
	}
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CreateUserAccount       																	*/
/* Description:	This function will pass all the user account information entered on the screen over to a 	*/
/*              PHP function that will hash the password, create the entry in the database and return a     */
/*              status.                                                                                     */
/* -------------------------------------------------------------------------------------------------------- */
function EnterNewUserInformation(event_id) {
	console.log('Entered into EnterNewUserInformation in register.js');
    var password = $("#info_text_field_newuser_password_confirm").val();
    var name = $("#info_text_field_driver1_name").val();
    var email = $("#info_text_field_driver1_email").val();
    var cell = $("#info_text_field_driver1_cell").val();
    var street = $("#info_text_field_driver1_street").val();
    var city = $("#info_text_field_driver1_city").val();
    var zip = $("#info_text_field_driver1_zip").val();
	var state = document.getElementById("info_select_field_driver1_state");
    var event_display_name = document.getElementById("info_select_field_driver1_event_display_name")
	var ret_val = '';
	var id = 0;
	var results = new Array();  

	// --------------------------
	// Get the Car Information
	// --------------------------
	car_info = {};
	var car = document.getElementById("nsx_gen_response_gen_selection_driver1");
	var car_generation = car.value;
	if (car.value != "Other") {
		var color = document.getElementById("nsx_gen_select_driver1_nsx_color");
		var year = document.getElementById("nsx_gen_select_driver1_nsx_year");
		car_info['car_color'] = color.value;	
		car_info['car_year'] = year.value;	
	} else {
		car_info['car_make'] = $("#nsx_gen_response_driver1_other_make").val();
		car_info['car_model'] = $("#nsx_gen_response_driver1_other_model").val();
		car_info['car_year'] = $("#nsx_gen_response_driver1_other_year").val();
	}

	// -------------------------------------
	// Get the social media information
	// -------------------------------------
	var social_media_info = {};	
	$('[id^="optional_text_field_driver1_"]').each(function() {
		social_media_info[$(this).attr('name')] = $(this).val();
        console.log($(this).attr('name') + " = " + $(this).val());
	});

	// -------------------------------------
	// Get the shared information choices
	// -------------------------------------
	var shared_information_choices = {};	
	$('[id^="info_field_user_display_choice_driver1_"]').each(function() {
		shared_information_choices[$(this).attr('key')] = $(this).attr('choice');
        console.log($(this).attr('key') + ' ' + $(this).attr('choice'));
	});


	return new Promise((resolve, reject) => {
		$.ajax({
			url: 'ACCOUNTCreation.php',
			type: 'POST',
			data: {method: 'EnterAccountInformation', event_id:event_id, name:name, password:password, email:email, cell:cell, street:street, city:city, state:state.value, zip:zip, car_generation:car_generation, car_info:car_info, social_media_info:social_media_info, event_display_name:event_display_name.value, shared_information_choices:shared_information_choices},
			success: function(data) {
			console.log(data);
			resolve(data);
				results = eval(data);
				var state = results[0];
				if (state == 'fail') {
				results.push('fail');
				results.push(results[1]);
				} else {
				results.push('success');
				results.push(results[1]);
				}
			},
			error: function (error) {
			reject(error)
			},		
		});
	});	  
}


/* -------------------------------------------------------------------------------------------------------- */
/* Function:	EnterUserToParticipantDatabase   														    */
/* Description:	This function enters the registrants information into the database            				*/
/* -------------------------------------------------------------------------------------------------------- */
function EnterUserToParticipantDatabase(driver, role, event_id) {
    var name = $("#info_text_field_"+driver+"_name").val();
    var email = $("#info_text_field_"+driver+"_email").val();
    var cell = $("#info_text_field_"+driver+"_cell").val();
    var street = $("#info_text_field_"+driver+"_street").val();
    var city = $("#info_text_field_"+driver+"_city").val();
    var zip = $("#info_text_field_"+driver+"_zip").val();
	var state = document.getElementById("info_select_field_"+driver+"_state");
	var event_display_name = document.getElementById("info_select_field_"+driver+"_event_display_name");
    var collect_payment = $("#event_info_container").attr('collect_payments');
    var sold_out = $("#main").attr('sold_out');
	var ret_val = '';
	var id = 0;
	var results = new Array();
	
    console.log("Sold Out = ", sold_out);

	// --------------------------
	// Get the Car Information
	// --------------------------
	car_info = {};
	var car = document.getElementById("nsx_gen_response_gen_selection_"+driver+"");
	var car_generation = car.value;
	if (car.value != "Other") {
		var color = document.getElementById("nsx_gen_select_"+driver+"_nsx_color");
		var year = document.getElementById("nsx_gen_select_"+driver+"_nsx_year");	
		car_info['car_color'] = color.value;	
		car_info['car_year'] = year.value;	
	} else {
		
		car_info['car_make'] = $("#nsx_gen_response_"+driver+"_other_make").val();
		car_info['car_model'] = $("#nsx_gen_response_"+driver+"_other_model").val();
		car_info['car_year'] = $("#nsx_gen_response_"+driver+"_other_year").val();
	}

	// --------------------------------------------
	// Get the drivers checkbox attendance choices
	// --------------------------------------------
	var driver_choices = {};	
	$('[id^="info_checkbox_field_'+driver+'_"]').each(function() {
		driver_choices[$(this).attr('key')] = $(this).attr('choice');
	});
    
	$('[id^="info_question_select_field_'+driver+'_"]').each(function() {     
        console.log('Starting Lovetion:', $(this).attr('key'));
        let temp_id = $(this).attr('id');
        console.log('This is the element ID = ', temp_id);
        var starting_choice = document.getElementById(temp_id);
        console.log(starting_choice.value);
		driver_choices[$(this).attr('key')] = starting_choice.value;
	});

    var elms = document.querySelectorAll('[id^="info_question_select_field_"]');
    console.log(elms.length)
    // for (i = 0; i < elms.length; i++) {
    //     elms[i].onclick = HandleButtonActionClick;
    // }


	// -------------------------------------
	// Get the social media information
	// -------------------------------------
	var social_media_info = {};	
	$('[id^="optional_text_field_'+driver+'_"]').each(function() {
		social_media_info[$(this).attr('name')] = $(this).val();
        console.log($(this).attr('name') + " = " + $(this).val());
	});

	// -------------------------------------
	// Get the shared information choices
	// -------------------------------------
	var shared_information_choices = {};	
	$('[id^="info_field_user_display_choice_'+driver+'_"]').each(function() {
		shared_information_choices[$(this).attr('key')] = $(this).attr('choice');
        console.log($(this).attr('key') + ' ' + $(this).attr('choice'));
	});


	// -----------------------------------------
	// check for additional Guest information
	// -----------------------------------------
	if (driver == 'driver1') {
	    var checkbox = document.getElementById('info_field_driver1_additional_guest').checked;
	    if (checkbox) {
	    	var travel_with = $("#info_text_field_driver2_name").val() + ' as a passenger';
		} else {
			var travel_with = 'N/A';
		}
	} else {
		if ($("#driver_two_role_selection_container").attr('role') == 'riding') {
			var travel_with = 'Riding with ' + $("#info_text_field_driver1_name").val();
		} else {
			var travel_with = 'N/A';
		}
	}

	// -------------------------------------------------------------------------------------------
	// Check to see if the registration is ok with us listing their names as coming to the event.
	// -------------------------------------------------------------------------------------------
	// var checkbox = document.getElementById('info_checkbox_field_'+driver+'_attending').checked;
	// if (checkbox) {
	var post_attend = 1;
	// } else {
	// 	var post_attend = 0;
	// }	

    if (collect_payment === 'yes') {
        if (driver == 'driver1') {
            console.log("Driver 1 Owes: ", $("#main_register_button").attr('reg_button_driver1_total'));
            var balance = $("#main_register_button").attr('reg_button_driver1_total');
        } else {
            console.log("Driver 2 Owes: ", $("#main_register_button").attr('reg_button_driver2_total'));
            var balance = $("#main_register_button").attr('reg_button_driver2_total');
        }
    } else {
        var balance = 0.00;
    }

	// The registration type chosen in the Event Registration Fee section (only there when the event collects payments)
	var registration_type = $("#regtype_select_"+driver).val() || '';

	console.log('Entering '  +driver);

	return new Promise((resolve, reject) => {
	  $.ajax({
	    url: 'GENERALFunctions.php',
	    type: 'POST',
	    data: {method: 'EnterDriverInformation', name:name, email:email, cell:cell, street:street, city:city, state:state.value, zip:zip, car_info:car_info, driver_choices:driver_choices, role:role, travel_with:travel_with, car_generation:car_generation, post_attend:post_attend, social_media_info:social_media_info, event_display_name:event_display_name.value, shared_information_choices:shared_information_choices, event_id:event_id, balance:balance, sold_out:sold_out, registration_type:registration_type},
	    success: function(data) {
	  	//   alert(data);
		  resolve(data);
	  	  results = eval(data);
	  	  var state = results[0];
	  	  if (state == 'fail') {
	  	    results.push('fail');
	  	    results.push(results[1]);
	  	  } else {
	  	    results.push('success');
	  	    results.push(results[1]);
	  	  }
	    },
        error: function (error) {
          reject(error)
        },		
	  });	
	});
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckForMissingDriverFields																    */
/* Description:	This function checks to make sure all the driver / passenger's required information is in.  */
/* -------------------------------------------------------------------------------------------------------- */
function CheckForMissingDriverFields(driver, role) {
	var result = true;
	$('[id^="info_text_field_'+driver+'_"]').each(function() {
		if ($(this).val().length == 0) {
			console.log($(this).attr('id'));
			result = false;
			document.getElementById($(this).attr('id')).style.border="2px solid #ffcc00";
		} else {
			document.getElementById($(this).attr('id')).style.border="2px solid rgba(255, 255, 255, 0.25)";
		}
	});

	// Check the State Value
	var state = document.getElementById("info_select_field_"+driver+"_state");
	if (state.value == "") {
		state.style.border="2px solid #ffcc00";
		result = false;
	} else {
		state.style.border="2px solid rgba(255, 255, 255, 0.25)";
	}

	// Check the car information if the driver is driving.
	if (role == 'driving') {
	    var car = document.getElementById("nsx_gen_response_gen_selection_"+driver+"");
	    if (car.value == "") {
	    	car.style.border="2px solid #ffcc00";
	    	result = false;
	    } else {
	    	car.style.border="2px solid rgba(255, 255, 255, 0.25)";
	    	if (car.value != "Other") {
	            var color = document.getElementById("nsx_gen_select_"+driver+"_nsx_color");
	            if (color.value == "") {
	            	color.style.border="2px solid #ffcc00";
	            	result = false;
	            } else {
	            	color.style.border="2px solid rgba(255, 255, 255, 0.25)";
	            }
	            var year = document.getElementById("nsx_gen_select_"+driver+"_nsx_year");
	            if (year.value == "") {
	            	year.style.border="2px solid #ffcc00";
	            	result = false;
	            } else {
	            	year.style.border="2px solid rgba(255, 255, 255, 0.25)";
	            }				
	    	} else {
	    		$('[id^="nsx_gen_response_'+driver+'_other_"]').each(function() {
	    			if ($(this).val().length == 0) {
	    				result = false;
	    				document.getElementById($(this).attr('id')).style.border="2px solid #ffcc00";
	    			} else {
	    				document.getElementById($(this).attr('id')).style.border="2px solid rgba(255, 255, 255, 0.25)";
	    			}
	    		});
	    	}
	    }
	}
	return result;
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToDriverTwoRoleSelection																*/
/* Description:	This function reacts to the selection of role for the second person being registered.		*/
/* -------------------------------------------------------------------------------------------------------- */
function ReactToDriverTwoRoleSelection() {
  var role = $(this).val();
  var child_discount_active = $("#driver2_cost").attr('child_discount_applied');

  console.log('ReactToDriverTwoRoleSelection : Starting shopping cart:' + localStorage.getItem('shopping_cart'));

  $("#driver_two_role_selection_container").attr('role', role);
  if (role == "riding") {
	  $("#event_registration_sub_container_header_driver2").html('Passenger');
      $('[id="column_driver2"]').each(function() {
        $(this).html('Passenger');
      });      
	  $("#nsx_gen_response_container_driver2_select_gen").removeClass('active');
	  $('[id^="nsx_gen_response_container_driver2_nsx"]').each(function() {
		  $(this).removeClass("active");
	  });
	  $("#event_registration_sub_container_header_driver1").html('Driver');
      $('[id="column_driver1"]').each(function() {
        $(this).html('Driver');
      });
	  $("#driver2_cost").attr('cost', $(this).attr('cost'));
	  if (child_discount_active == 'yes') {
		console.log ("ReactToDriverTwoRoleSelection : Swiching registration cost from child to passenger.")
		shopping_cart_actions('childRegistration', $(this).attr('cost') * 100, 'Childrens Base Registration Fee', 'driver2', 'remove');
		shopping_cart_actions('guestRegistration', $(this).attr('cost') * 100, 'Additional Guest Base Registration Fee', 'driver2', 'add');
		$("#info_field_driver1_additional_guest").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").html('$'+$(this).attr('cost')+'.00');
		update_driver_totals('driver2');		
	  } else {
		console.log ("ReactToDriverTwoRoleSelection : No change in base reg needed.");
	  }

	} else if (role == "child") {
	  $("#event_registration_sub_container_header_driver2").html('Child');
      $('[id="column_driver2"]').each(function() {
        $(this).html('Child');
      });      
	  $("#nsx_gen_response_container_driver2_select_gen").removeClass('active');
	  $('[id^="nsx_gen_response_container_driver2_nsx"]').each(function() {
		  $(this).removeClass("active");
	  });
	  $("#event_registration_sub_container_header_driver1").html('Driver');
      $('[id="column_driver1"]').each(function() {
        $(this).html('Driver');
      });
	  $("#driver2_cost").attr('cost', $(this).attr('cost'));
	  if (child_discount_active == 'no') {
		console.log ("ReactToDriverTwoRoleSelection : Swiching registration cost from base to child.")
		shopping_cart_actions('childRegistration', $(this).attr('cost') * 100, 'Childrens Base Registration Fee', 'driver2', 'add');
		shopping_cart_actions('guestRegistration', $(this).attr('cost') * 100, 'Additional Guest Base Registration Fee', 'driver2', 'remove');
		$("#driver2_cost").attr('child_discount_applied', 'yes');
		$("#info_field_driver1_additional_guest").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").html('$'+$(this).attr('cost')+'.00');
		update_driver_totals('driver2');
	  } else {
		console.log ("ReactToDriverTwoRoleSelection : No change in child reg needed.");
	  }	  	        
	        
  } else {
	  $("#event_registration_sub_container_header_driver2").html('Driver Two');
      $('[id="column_driver2"]').each(function() {
        $(this).html('Driver Two');
      });      
	  $("#nsx_gen_response_container_driver2_select_gen").addClass('active');
	  $("#event_registration_sub_container_header_driver1").html('Driver One');
      $('[id="column_driver1"]').each(function() {
        $(this).html('Driver One');	                
    });      
	$("#driver2_cost").attr('cost', $(this).attr('cost'));
	$("#driver2_cost").attr('child_discount_applied', 'no');
	if (child_discount_active == 'yes') {
		console.log ("ReactToDriverTwoRoleSelection : Swiching registration cost from child to driver 2.")
		shopping_cart_actions('childRegistration', $(this).attr('cost') * 100, 'Childrens Base Registration Fee', 'driver2', 'remove');
		shopping_cart_actions('guestRegistration', $(this).attr('cost') * 100, 'Additional Guest Base Registration Fee', 'driver2', 'add');
		$("#driver2_cost").attr('child_discount_applied', 'no');
		$("#info_field_driver1_additional_guest").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").attr('cost', $(this).attr('cost')*100);
		$("#cost_driver2").html('$'+$(this).attr('cost')+'.00');
		update_driver_totals('driver2');
	} else {
	console.log ("ReactToDriverTwoRoleSelection : No change in base reg needed.");
	}		
  }  
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToGenerationSelection																	*/
/* Description:	This function reacts to the selection of role for the second person being registered.		*/
/* -------------------------------------------------------------------------------------------------------- */
function ReactToGenerationSelection(element) {
	var generation = $(this).val();
	var driver = $(this).attr('driver');
	var current_choice = $(this).attr('current_choice') 

	$('[id^="nsx_gen_response_container_'+driver+'_"]').each(function() {
		if(!($(this).attr('id').endsWith("_select_gen"))) {
			$(this).removeClass("active");
		}		
	});

	if (generation != 'Other') {
		YearSelectBox = document.getElementById('nsx_gen_select_'+driver+'_nsx_year')
		while (YearSelectBox.options.length > 0) {
			YearSelectBox.remove(0);
		}
		var default_opt = document.createElement('option');
		default_opt.selected = true;
		default_opt.value = '';
		default_opt.innerHTML = 'Year';
		default_opt.disabled = true;
		default_opt.hidden = true;
		YearSelectBox.appendChild(default_opt);

		if (generation == 'Second_Generation') {
			for (var i = 2017; i<=2022; i++){
				var opt = document.createElement('option');
				opt.value = i;
				opt.innerHTML = i;
				YearSelectBox.appendChild(opt);
			}
		} else {
			for (var i = 1991; i<=2005; i++){
				var opt = document.createElement('option');
				opt.value = i;
				opt.innerHTML = i;
				YearSelectBox.appendChild(opt);
			}
		}

		$("#nsx_gen_response_gen_selection_"+driver+"").attr('current_choice', 'nsx');
	    $('[id^="nsx_gen_response_container_'+driver+'_nsx"]').each(function() {
		  $(this).addClass("active");
	    });
	} else {
		

		$("#nsx_gen_response_gen_selection_"+driver+"").attr('current_choice', 'other');
	    $('[id^="nsx_gen_response_container_'+driver+'_other"]').each(function() {
		  $(this).addClass("active");
	    });
	}
}
	
/* -------------------------------------------------------------------------------------------------------- */
/* Function:	AddAdditionalGuestToRegistration															*/
/* Description:	This function reacts to a driver wanting to register another guest.							*/
/* -------------------------------------------------------------------------------------------------------- */	
function AddAdditionalGuestToRegistration() {
	console.log('AddAdditionalGuestToRegistration : Starting shopping cart:' + localStorage.getItem('shopping_cart'));
	var checkbox = document.getElementById('info_field_driver1_additional_guest').checked;
	var child_discount_active = $("#driver2_cost").attr('child_discount_applied');

	// Element is currently un-checked and has been checked
	if (checkbox) {
		$("#driver_two_contact_container").addClass("active");
		var role = $("#driver_two_role_selection_container").attr('role');
		if (role == 'driving') {
			$("#event_registration_sub_container_header_driver1").html('Driver One');
			// $("#column_driver1").html('Driver One');
            $('[id="column_driver1"]').each(function() {
                $(this).html('Driver One');
            });
		}
	    $('[id="column_driver2"]').each(function() {
		  $(this).addClass("active");
	    });
	    $('[id="cost_driver2"]').each(function() {
            $(this).addClass("active");
        });  
	    $('[id="question_driver2"]').each(function() {
            $(this).addClass("active");
        });

		if (child_discount_active == 'yes') {
			shopping_cart_actions('childRegistration', $("#info_field_driver1_additional_guest").attr('cost'), 'Childrens Base Registration Fee', 'driver2', 'add');
		} else {
			shopping_cart_actions('guestRegistration', $("#info_field_driver1_additional_guest").attr('cost'), 'Additional Guest Base Registration Fee', 'driver2', 'add');
		}

		$("#main_register_button").attr('guest_active', 'yes');

	// Element is currently checked and has been un-checked
	} else {
		$("#driver_two_contact_container").removeClass("active");
		$("#event_registration_sub_container_header_driver1").html('Driver');
		// $("#column_driver1").html('Driver');
        $('[id="column_driver1"]').each(function() {
            $(this).html('Driver');
        });

	    $('[id="column_driver2"]').each(function() {
		  $(this).removeClass("active");
	    });
	    $('[id="cost_driver2"]').each(function() {
            $(this).removeClass("active");
        }); 
	    $('[id="question_driver2"]').each(function() {
            $(this).removeClass("active");
        });

		if (child_discount_active == 'yes') {
			shopping_cart_actions('childRegistration', $("#info_field_driver1_additional_guest").attr('cost'), 'Childrens Base Registration Fee', 'driver2', 'remove');
		} else {
			shopping_cart_actions('guestRegistration', $("#info_field_driver1_additional_guest").attr('cost'), 'Additional Guest Base Registration Fee', 'driver2', 'remove');
		}
		$("#main_register_button").attr('guest_active', 'no');

	}
	console.log('AddAdditionalGuestToRegistration : Ending shopping cart:' + localStorage.getItem('shopping_cart'));

}


/* -------------------------------------------------------------------------------------------------------- */
/* Function:	InitRegistrationTypeSelection																*/
/* Description:	Hooks up the registration type drop downs in the Event Registration Fee section (only shown	*/
/*				when the event collects payments). The additional guest checkbox adds driver two's fee to	*/
/*				the cart, so it starts with driver two's cost (set when there's only one type).				*/
/* -------------------------------------------------------------------------------------------------------- */
function InitRegistrationTypeSelection() {
	$('.regtype_select').on('change', ReactToRegistrationTypeSelection);
	if ($("#regtype_select_driver2").length) {
		$("#info_field_driver1_additional_guest").attr('cost', $("#cost_driver2").attr('cost'));
	}

	// Driver two needs a registration type too once they're added
	$("#info_field_driver1_additional_guest").on('change', UpdateCheckoutButtonState);

	// Other code (e.g. the password match check) also turns the register button's 'disabled' class
	// on and off, so the click itself is stopped here - before the button's own onclick runs.
	document.addEventListener('click', function(event) {
		var button = event.target.closest('[requires_regtype="yes"]');
		if (button && !RegistrationTypesComplete()) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
	}, true);

	UpdateCheckoutButtonState();
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	RegistrationTypesComplete																	*/
/* Description:	True when driver one has a registration type and, if driver two has been added, driver two	*/
/*				does too. False when the event has no registration types to choose from.					*/
/* -------------------------------------------------------------------------------------------------------- */
function RegistrationTypesComplete() {
	if (!$("#regtype_select_driver1").length) {
		return false;
	}
	if ($("#regtype_select_driver1").val() === '') {
		return false;
	}
	if ($("#info_field_driver1_additional_guest").is(':checked') && $("#regtype_select_driver2").val() === '') {
		return false;
	}
	return true;
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	UpdateCheckoutButtonState																	*/
/* Description:	Enables the checkout / register button (when the event collects payments) only once every	*/
/*				registrant has a registration type.														*/
/* -------------------------------------------------------------------------------------------------------- */
function UpdateCheckoutButtonState() {
	$('[requires_regtype="yes"]').toggleClass('disabled', !RegistrationTypesComplete());
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToRegistrationTypeSelection															*/
/* Description:	Puts the chosen registration type's cost (in cents) in the cost attribute of the matching	*/
/*				cost_driverX cell and swaps that registrant's base fee in the shopping cart.				*/
/* -------------------------------------------------------------------------------------------------------- */
function ReactToRegistrationTypeSelection() {
	var driver = $(this).attr('driver');
	var option = $(this).find('option:selected');
	var cost = parseInt(option.attr('cost'), 10) || 0;

	$("#cost_" + driver).attr('cost', cost);
	// The description may contain HTML - show it as plain text (DOMParser doesn't run scripts or load images)
	var description = new DOMParser().parseFromString(option.attr('description') || '', 'text/html').body.textContent;
	$("#regtype_description_" + driver).text(description);

	if (driver === 'driver1') {
		shopping_cart_actions('baseRegistration', 0, '', 'driver1', 'remove');
		if (cost > 0) {
			shopping_cart_actions('baseRegistration', cost, 'Event entry fee (Main Guest) - ' + option.text(), 'driver1', 'add');
		}
		update_driver_totals('driver1');
	} else {
		// Used when the additional guest is added; if they already are, swap their fee now
		$("#info_field_driver1_additional_guest").attr('cost', cost);
		if ($("#info_field_driver1_additional_guest").is(':checked')) {
			shopping_cart_actions('guestRegistration', 0, '', 'driver2', 'remove');
			shopping_cart_actions('guestRegistration', cost, 'Additional Guest Base Registration Fee - ' + option.text(), 'driver2', 'add');
			update_driver_totals('driver2');
		}
	}
	UpdateCheckoutButtonState();
}
