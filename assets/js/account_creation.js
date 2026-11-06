/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CreateConfirmationPage   																    */
/* Description:	This function creates the confirmation page after a successful registration.                */
/* -------------------------------------------------------------------------------------------------------- */
function VerifyUniqueUsername(event) {
    event.preventDefault();
    console.log('Called the VerifyUniqueUsername javascript function for username = ' + $("#info_text_field_newuser_username").val());
    var username = $("#info_text_field_newuser_username").val();
    var username_field = document.getElementById('info_text_field_newuser_username');

	$.ajax({
        url: 'GENERALFunctions.php',
        type: 'POST',
        data: {method: 'VerifyUniqueUsername', username:username},
        success: function(data) {
            console.log(data)
            if (data === 'PASS') {
                $("#verified_username_further_information").addClass('active');
                $("#newuser_verify_unique_username_button").html('Available');
                $("#newuser_verify_unique_username_button").addClass('verified_button');
                $("#info_text_field_newuser_email").val(username);
                
                username_field.readOnly = true;

            } else {
                alert(data);
            }
        }
      });	    

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
/* Function:	ReactToGenerationSelection																	*/
/* Description:	This function reacts to the selection of role for the second person being registered.		*/
/* -------------------------------------------------------------------------------------------------------- */
function ReactToGenerationSelection(element) {
    console.log('React to generation selection ');
	var generation = $(this).val();
	var vehicle = $(this).attr('vehicle');
	var current_choice = $(this).attr('current_choice') 

    console.log('Generation = ' ,generation);
    console.log('Vehicle = ', vehicle);

	$('[id^="nsx_gen_response_container_'+vehicle+'_"]').each(function() {
		if(!($(this).attr('id').endsWith("_select_gen"))) {
			$(this).removeClass("active");
		}		
	});

	if (generation != 'Other') {
		YearSelectBox = document.getElementById('nsx_gen_select_nsx_year_'+vehicle)
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

		$("#nsx_gen_response_gen_selection_"+vehicle+"").attr('current_choice', 'nsx');
	    $('[id^="nsx_gen_response_container_'+vehicle+'_nsx"]').each(function() {
		  $(this).addClass("active");
	    });
	} else {
		

		$("#nsx_gen_response_gen_selection_"+vehicle+"").attr('current_choice', 'other');
	    $('[id^="nsx_gen_response_container_'+vehicle+'_other"]').each(function() {
		  $(this).addClass("active");
	    });
	}    
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CreateUserAccount       																	*/
/* Description:	This function is the main user account creation function.                           		*/
/* -------------------------------------------------------------------------------------------------------- */
async function CreateUserAccount(event) {
    event.preventDefault();
    var ready = CheckForMissingFields();
    if (ready) {
        console.log('Creating user account');
        await EnterNewUserInformation()
            .then((data) => {
                console.log(data);
                //alert(data);
                account_results = eval(data);
                alert('User Account created Successfully. You will be redirected to the main page where you can log in.');
                window.open('index.php', '_self');
            })
            .catch((error) => {
                console.log(error)
            })
        
        console.log('2');        
    } else {
        console.log('Not ready to create account');
    }
    
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CheckForMissingFields   																    */
/* Description:	This function checks to make sure all the required information is in.                       */
/* -------------------------------------------------------------------------------------------------------- */
function CheckForMissingFields() {
	var result = true;
	$('[id^="info_text_field_newuser_"]').each(function() {
		if ($(this).val().length == 0) {
			result = false;
			document.getElementById($(this).attr('id')).style.border="2px solid #ffcc00";
		} else {
			document.getElementById($(this).attr('id')).style.border="2px solid rgba(255, 255, 255, 0.25)";
		}
	});

	// Check the State Value
	var state = document.getElementById("info_select_field_newuser_state");
	if (state.value == "") {
		state.style.border="2px solid #ffcc00";
		result = false;
	} else {
		state.style.border="2px solid rgba(255, 255, 255, 0.25)";
	}

	// Check the car information if the driver is driving.
    var car = document.getElementById("nsx_gen_response_gen_selection_primary");
    if (car.value == "") {
        car.style.border="2px solid #ffcc00";
        result = false;
    } else {
        car.style.border="2px solid rgba(255, 255, 255, 0.25)";
        if (car.value != "Other") {
            var color = document.getElementById("nsx_gen_select_nsx_color_primary");
            if (color.value == "") {
                color.style.border="2px solid #ffcc00";
                result = false;
            } else {
                color.style.border="2px solid rgba(255, 255, 255, 0.25)";
            }
            var year = document.getElementById("nsx_gen_select_nsx_year_primary");
            if (year.value == "") {
                year.style.border="2px solid #ffcc00";
                result = false;
            } else {
                year.style.border="2px solid rgba(255, 255, 255, 0.25)";
            }				
        } else {
            $('[id^="nsx_gen_response_newuser_other_"]').each(function() {
                if ($(this).val().length == 0) {
                    result = false;
                    document.getElementById($(this).attr('id')).style.border="2px solid #ffcc00";
                } else {
                    document.getElementById($(this).attr('id')).style.border="2px solid rgba(255, 255, 255, 0.25)";
                }
            });
        }
    }

	return result;
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	CreateUserAccount       																	*/
/* Description:	This function will pass all the user account information entered on the screen over to a 	*/
/*              PHP function that will hash the password, create the entry in the database and return a     */
/*              status.                                                                                     */
/* -------------------------------------------------------------------------------------------------------- */
function EnterNewUserInformation() {
    var event_id = '';
    var password = $("#info_text_field_newuser_password_confirm").val();
    var name = $("#info_text_field_newuser_name").val();
    var email = $("#info_text_field_newuser_email").val();
    var cell = $("#info_text_field_newuser_cell").val();
    var street = $("#info_text_field_newuser_street").val();
    var city = $("#info_text_field_newuser_city").val();
    var zip = $("#info_text_field_newuser_zip").val();
	var state = document.getElementById("info_select_field_newuser_state");
    var event_display_name = document.getElementById("info_select_field_newuser_event_display_name")
	var ret_val = '';
	var id = 0;
	var results = new Array();  

	// --------------------------
	// Get the Car Information
	// --------------------------
	car_info = {};
	var car = document.getElementById("nsx_gen_response_gen_selection_primary");
	var car_generation = car.value;
	if (car.value != "Other") {
		var color = document.getElementById("nsx_gen_select_nsx_color_primary");
		var year = document.getElementById("nsx_gen_select_nsx_year_primary");
		car_info['car_color'] = color.value;	
		car_info['car_year'] = year.value;	
	} else {
		car_info['car_make'] = $("#nsx_gen_response_newuser_other_make").val();
		car_info['car_model'] = $("#nsx_gen_response_newuser_other_model").val();
		car_info['car_year'] = $("#nsx_gen_response_newuser_other_year").val();
	}

	// -------------------------------------
	// Get the social media information
	// -------------------------------------
	var social_media_info = {};	
	$('[id^="optional_text_field_newuser_"]').each(function() {
		social_media_info[$(this).attr('name')] = $(this).val();
        console.log($(this).attr('name') + " = " + $(this).val());
	});

	// -------------------------------------
	// Get the shared information choices
	// -------------------------------------
	var shared_information_choices = {};	
	$('[id^="info_field_user_display_choice_"]').each(function() {
		shared_information_choices[$(this).attr('key')] = $(this).attr('choice');
        console.log($(this).attr('key') + ' ' + $(this).attr('choice'));
	});

	return new Promise((resolve, reject) => {
        $.ajax({
          url: 'ACCOUNTCreation.php',
          type: 'POST',
          data: {method: 'EnterAccountInformation', event_id:event_id, name:name, password:password, email:email, cell:cell, street:street, city:city, state:state.value, zip:zip, car_generation:car_generation, car_info:car_info, social_media_info:social_media_info, event_display_name:event_display_name.value, shared_information_choices:shared_information_choices},
        //   data: {method: 'EnterDriverInformation', username:username, name:name, password:password, email:email, cell:cell, street:street, city:city, state:state.value, zip:zip, car_generation:car_generation, car_info:car_info, social_media_info:social_media_info, event_display_name:event_display_name.value, shared_information_choices:shared_information_choices},
          success: function(data) {
            //alert(data);
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
/* Function:	ReactToSharedInformationChoice   									    				    */
/* Description:	This function sets the shared event information choice for a user when clicked.             */
/* -------------------------------------------------------------------------------------------------------- */
function ReactToSharedInformationChoice() {
	var key = $(this).attr('key');
	var id_val = $(this).attr('id');
	var checkbox = document.getElementById(id_val).checked;
	if (checkbox) {	
		$("#info_field_user_display_choice_"+key).attr('choice', 'yes');
	} else {
		$("#info_field_user_display_choice_"+key).attr('choice', 'no');
	}
}

