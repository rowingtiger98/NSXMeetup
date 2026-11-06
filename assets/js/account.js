// -------------------------------------------------------------------------------------
// Function: 	WorkOnPhotoLables
// Description: This function renders the page to work on Photo information.
// -------------------------------------------------------------------------------------
function HandleMyAccountButtonActionClick(event) {
    event.preventDefault();
    var action = $(this).attr('action');
    console.log("Render the "+action+" section of the MyAccount page : HandleMyAccountButtonActionClick");
    
    $('[id^="myaccount_link_"]').each(function() {
        $(this).removeClass("myaccount_link_active");
    });

    $("#myaccount_link_"+action+"").addClass('myaccount_link_active');

    if(action == 'information') {
        console.log("RenderMyAccountInformation()");
        RenderMyAccountInformation();
    } 
    else if (action == 'attending') {
        console.log("RenderMyAccountAttending()");
        RenderMyAccountAttending();
    }
    else if(action =='managing') {
        console.log("RenderMyAccountManage()");
        RenderMyAccountManage();
    }
    else if(action == 'overlord') {
        console.log("RenderMyAccountOverLord()");
		RenderMyAccountOverLord();
    }
    else {
        console.log("ERROR CASE)");
    }
}

// -------------------------------------------------------------------------------------
// Function: 	HandleAccountManagementActionClick
// Description: This function handles all the clicks that launch management interfaces.
// -------------------------------------------------------------------------------------
function HandleAccountManagementActionClick(event) {
    event.preventDefault();
    var action = $(this).attr('action');
    console.log("Render the "+action+" section of the MyAccount page");

    //Render the find and Edit page upon click
    if (action === 'find_edit_registration') {
        var event_id = $(this).attr('event_id');
        RenderFindEditRegistration(event_id);
    }
    else if (action === 'registration_statistics_information' ){
        var event_id = $(this).attr('event_id');
        RenderRegistrationStatistics(event_id);
    }

	else if (action === 'email_list_generation' ){
        var event_id = $(this).attr('event_id');
        RenderEmailListGeneration(event_id);
    }

	else if (action === 'render_participant_information' ){
        var event_id = $(this).attr('event_id');
		var collect_payments = $(this).attr('collect_payments');
		var participant_id = $(this).attr('participant_id');
        RenderParticipantInformation(event_id, participant_id, collect_payments);
    }

	else if (action === 'sponsor_management' ){
        var event_id = $(this).attr('event_id');
		RenderSponsorManagementInterface(event_id);
    }	

	else if (action === 'sponsorship_level_management' ){
        var event_id = $(this).attr('event_id');
		RenderSponsorshipManagementInterface(event_id);
    }

	else if (action === 'edit_event_settings' ){
        var event_id = $(this).attr('event_id');
		RenderEditEventSettings(event_id, 'settings');
    }

	else if (action === 'edit_event_payments' ){
        var event_id = $(this).attr('event_id');
		RenderEditEventSettings(event_id, 'payments');
    }

	else if (action === 'edit_registration_types' ){
        var event_id = $(this).attr('event_id');
		RenderRegistrationTypes(event_id);
    }
}

// -------------------------------------------------------------------------------------
// Function: 	RenderMyAccountInformation
// Description: This function renders the user information for a signed in user.
// -------------------------------------------------------------------------------------
function RenderMyAccountInformation() {
    var user_id = $("#session_information").attr('info_one');
    var email = $("#session_information").attr('info_two');    
    console.log('Entered RenderMyAccountInformation() with user_id = '+user_id+' and email = '+email+'');

	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderUserAccountInformation', user_id:user_id, email:email},
	    success: function(data) {
	  	  console.log('Call to RenderMyAccountInformation');
	  	  results = eval(data);
          if (results[0] == 'Pass') {
            $("#main_content_container").html(results[1]);
          } else {
            console.log('fail');
          }
	    },	
	  });		
}

// -------------------------------------------------------------------------------------
// Function: 	RenderMyAccountAttending
// Description: This function renders the user information for a signed in user.
// -------------------------------------------------------------------------------------
function RenderMyAccountAttending() {
    var user_id = $("#session_information").attr('info_one');
    var email = $("#session_information").attr('info_two');    
    console.log('Entered RenderMyAccountAttending() with user_id = '+user_id+' and email = '+email+'');

	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderMyAccountAttending', user_id:user_id, email:email},
	    success: function(data) {
	  	  console.log(data);
	  	  results = eval(data);
          if (results[0] == 'Pass') {
            $("#main_content_container").html(results[1]);
          } else {
            console.log('fail');
          }
	    },	
	  });		
}

// -------------------------------------------------------------------------------------
// Function: 	RenderEmailListGeneration
// Description: This function renders the email list generation section.
// -------------------------------------------------------------------------------------
function RenderEmailListGeneration(event_id) {
    console.log('Render the email list generation for event id = ', event_id);
	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderEmailListGeneration', event_id:event_id},
	    success: function(data) {
	  	  results = eval(data);
            $("#main_content_container").html(results[0]);
            RenderBackButton('backtomaineventtoolset', event_id);

            // var elms = document.querySelectorAll('[id="download_participants_csv"]');
            // for (i = 0; i < elms.length; i++) {
            //     elms[i].onclick = DownloadParticipantInformation;
			// } 

	    },	
	  });    
}

// -------------------------------------------------------------------------------------
// Function: 	RenderRegistrationStatistics
// Description: This function renders the the breakdown of the registration information.
// -------------------------------------------------------------------------------------
function RenderRegistrationStatistics(event_id) {
    console.log('Render the registration statistics for event id = ', event_id);
	$.ajax({
	    url: 'REPORTINGFunctions.php',
	    type: 'POST',
	    data: {method: 'RenderEventReportMainBody', event_id:event_id},
	    success: function(data) {
	  	  results = eval(data);
            $("#main_content_container").html(results[0]);
            RenderBackButton('backtomaineventtoolset', event_id);

            var elms = document.querySelectorAll('[id^="show_participants_list_button_"]');
            for (i = 0; i < elms.length; i++) {
                elms[i].onclick = ShowAndHideIncludedParticipants;
			} 
            var elms = document.querySelectorAll('[id="download_participants_csv"]');
            for (i = 0; i < elms.length; i++) {
                elms[i].onclick = DownloadParticipantInformation;
			} 

	    },	
	  });    
}

// -------------------------------------------------------------------------------------
// Function: 	RenderSponsorManagementInterface
// Description: This function renders sponsor management interface section.
// -------------------------------------------------------------------------------------
function RenderSponsorManagementInterface(event_id) {
	active_id_number = $("#session_information").attr('info_one');
	active_user = $("#session_information").attr('info_two');
    console.log('NEW Render the sponsor management interface section for event id = ', event_id, active_id_number, active_user);
	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderSponsorManagementInterface', event_id:event_id, active_id_number:active_id_number, active_user:active_user},
	    success: function(data) {
	  	  results = eval(data);
          $("#main_content_container").html(results[0]);
          RenderBackButton('backtomaineventtoolset', event_id);

		  var elms = document.querySelectorAll("[id='edit_sponsor_button']");
		  console.log(elms.length);
		  for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = RenderEditIndividualSponsorInformation;
		  }
		  
		  /* Click Handler for turning on and off a sponsor */
		  var elms = document.querySelectorAll("input[name^='active_sponsor_radio_button']");
		  for(var i = 0; i < elms.length; i++) {
			  elms[i].onclick = HandleSponsorActivityChange;
		  }
		  document.querySelector("[id='create_new_sponsor_button']").onclick = RenderAddNewSponsorInformation;
	    },	
	  });    
}

// -------------------------------------------------------------------------------------
// Function: 	RenderSponsorshipManagementInterface
// Description: This function renders sponsor management interface section.
// -------------------------------------------------------------------------------------
function RenderSponsorshipManagementInterface(event_id) {
	active_id_number = $("#session_information").attr('info_one');
	active_user = $("#session_information").attr('info_two');
    console.log('Render the sponsorship level management interface section for event id = ', event_id, active_id_number, active_user);
	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderSponsorshipManagementInterface', event_id:event_id, active_id_number:active_id_number, active_user:active_user},
	    success: function(data) {
	  	  results = eval(data);
          $("#main_content_container").html(results[0]);
          RenderBackButton('backtomaineventtoolset', event_id);

		  var elms = document.querySelectorAll("[id='edit_sponsorship_level_button']");
		  console.log(elms.length);
		  for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = RenderEditIndividualSponsorshipLevelInformation;
		  }
		  
		  /* Click Handler for turning on and off a sponsor */
		  var elms = document.querySelectorAll("input[name^='active_sponsorship_level_radio_button']");
		  for(var i = 0; i < elms.length; i++) {
			  elms[i].onclick = HandleSponsorshipLevelActivityChange;
		  }
		  
		  document.querySelector("[id='create_new_sponsorship_level_button']").onclick = RenderAddNewSponsorshipLevelInformation;
	    },	
	  });    
}

// -------------------------------------------------------------------------------------
// Function: 	ShowAndHideIncludedParticipants
// Description: This function shows or hides the included list of participants for a 
//              given questions answer.
// -------------------------------------------------------------------------------------
function ShowAndHideIncludedParticipants(event)
 {
    event.preventDefault();
    var action = $(this).attr('action');
    var label = $(this).attr('label');
    if (action === 'show') {
        console.log('Show the participants names for the label', label);
        $("#show_participants_list_button_"+label+"").html('Hide Names');
        $("#show_participants_list_button_"+label+"").attr('action', 'hide');
	    $('[id^="participant_names_list_'+label+'"]').each(function() {
            $(this).addClass("active");
        });        
    } else {
        console.log('Hide the participants names for the label', label);
        $("#show_participants_list_button_"+label+"").html('Show Names');
        $("#show_participants_list_button_"+label+"").attr('action', 'show');
	    $('[id^="participant_names_list_'+label+'"]').each(function() {
            $(this).removeClass("active");
        });   
    }
 }

// -------------------------------------------------------------------------------------
// Function: 	RenderMyAccountManage
// Description: This function renders the user information for a signed in user.
// -------------------------------------------------------------------------------------
function RenderMyAccountManage() {
    var user_id = $("#session_information").attr('info_one');
    var email = $("#session_information").attr('info_two');    
    console.log('Entered RenderMyAccountInformation() with user_id = '+user_id+' and email = '+email+'');

	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderMyAccountManage', user_id:user_id, email:email},
	    success: function(data) {
	  	  console.log(data);
	  	  results = eval(data);
          if (results[0] == 'Pass') {
            $("#main_content_container").html(results[1]);
          } else {
            console.log('fail');
          }

          document.querySelector('[id="managed_event_selection_dropdown"]').onchange = HandleSelectedEventToManage;

	    },	
	  });		
}

// -------------------------------------------------------------------------------------
// Function: 	HandleSelectedEventToManage
// Description: This function calls the renderer the management page for the selected 
//              event from the dropdown.
// -------------------------------------------------------------------------------------
function HandleSelectedEventToManage() {
    var event_id = $(this).val();
    RenderSelectedEventToManage(event_id);
}

// -------------------------------------------------------------------------------------
// Function: 	RenderSelectedEventToManage
// Description: This function renders the management page for the selected event.
// -------------------------------------------------------------------------------------
function RenderSelectedEventToManage(event_id) {
    // var event_id = $(this).val();
    console.log('render the management interface for the selected event = ' + event_id);
    $("#managed_event_tools_section").attr('event_id', event_id);
	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderManagementToolsSection', event_id:event_id},
	    success: function(data) {
	  	//   console.log(data);
	  	  results = eval(data);
          if (results[0] == 'Pass') {
			// $("#managed_event_tools_section").html(results[1]);
            $("#main_content_container").html(results[1]);

            var elms = document.querySelectorAll('[id="admin_event_handler"]');
			console.log('HERE', elms.length);
            for (i = 0; i < elms.length; i++) {
                elms[i].onclick = HandleAccountManagementActionClick;
			}

            document.querySelector('[id="render_local_attractions"]').onclick = RenderEditLocalAttraction;
            // document.querySelector('[id="find_edit_registration"]').onclick = RenderFindEditRegistration;
            // document.querySelector('[id="download_participant_information"]').onclick = DownloadParticipantInformation;
            // document.querySelector('[id="edit_news_container_textarea"]').onclick = RenderEditNewsContainer;
            document.querySelector('[id="download_labels_csv"]').onclick = Download_Labels_CSV;
            // document.querySelector('[id="download_photonumber_csv"]').onclick = Download_Photo_Number_CSV;
            // document.querySelector('[id="photo_numbers"]').onclick = WorkOnPhotoLables;
			// document.querySelector('[id="edit_sponsor_information"]').onclick = RenderEditSponsorInformation;
            // document.querySelector('[id="manage_swap_shop"]').onclick = Render_Swap_Shop_Manager;


          } else {
            console.log('fail');
          }
	    },	
	  });	    
}

// -------------------------------------------------------------------------------------
// Function: 	WorkOnPhotoLables
// Description: This function renders the page to work on Photo information.
// -------------------------------------------------------------------------------------
function WorkOnPhotoLables() {
  console.log('Work on photo lables page');
}


// -------------------------------------------------------------------------------------
// Function: 	Download_Labels_CSV
// Description: This function renders the find / edit participant information.
// -------------------------------------------------------------------------------------
function Download_Labels_CSV() {
  var event_id = $(this).attr('event_id');
  var event_name = $(this).attr('event_name').replace(/ /g,"_")+'_Lanyard_Labels.csv'
  console.log(event_id);
  $.ajax({
	url: 'GENERALFunctions.php',
	type: 'POST',
	data: {method: 'CreateLanyardLabelsCSVFile', event_id:event_id},
	success: function(data) {
	  console.log(data);
	  let csvContent = "data:text/csv;charset=utf-8," + data
	  
	  var encodedUri = encodeURI(csvContent);
	  var link = document.createElement("a");
	  link.setAttribute("href", encodedUri);
	  link.setAttribute("download", event_name);
	  document.body.appendChild(link); // Required for FF
	  
	  link.click();					  
	}
  });
}

// -------------------------------------------------------------------------------------
// Function: 	Download_Photo_Number_CSV
// Description: This function creates and downloads the photo numbers CSV file.
// -------------------------------------------------------------------------------------
function Download_Photo_Number_CSV() {
  $.ajax({
	url: 'GENERALFunctions.php',
	type: 'POST',
	data: {method: 'CreatePhotoNumbersCSVFile'},
	success: function(data) {
	  console.log(data);
	  let csvContent = "data:text/csv;charset=utf-8," + data
	  
	  var encodedUri = encodeURI(csvContent);
	  var link = document.createElement("a");
	  link.setAttribute("href", encodedUri);
	  link.setAttribute("download", "nsx_photo_numbers.csv");
	  document.body.appendChild(link); // Required for FF
	  
	  link.click();					  
	}
  });
}

function HandleSwapShapDisplayChange(swapRadio) {
    console.log('Swap Shop Display changing to:' + swapRadio.value);
    if (swapRadio.value === 'new') {
        console.log('Rendering the form to create a new swap shop item.');
        $("#swap_shop_manager_content").html('NEW ITEM FORM');
    } else if (swapRadio.value === 'existing') {
        console.log('Rendering the page to allow editing an existing swap shop item.');
        $("#swap_shop_manager_content").html('EDIT ITEM FORM');
    } else {
        $("#swap_shop_manager_content").html('BAD SELECTION');
    }
}

// -------------------------------------------------------------------------------------
// Function: 	Render_Swap_Shop_Manager
// Description: This function renders the swap shop manager page.
// -------------------------------------------------------------------------------------
function Render_Swap_Shop_Manager() {
    var event_id = $(this).attr('event_id');
    var event_name = $(this).attr('event_name').replace(/ /g,"_")+'_Lanyard_Labels.csv'
    console.log("Render swap shop manager for event = " + event_id);
    $.ajax({
      url: 'ACCOUNTManagement.php',
      type: 'POST',
      data: {method: 'RenderSwapShopManagement', event_id:event_id},
      success: function(data) {
        console.log(data);
		results = eval(data);
		$("#details_info").html(results[0]);		  
      }
    });
  }

// -------------------------------------------------------------------------------------
// Function: 	RenderFindEditRegistration
// Description: This function renders the find / edit participant information.
// -------------------------------------------------------------------------------------
function RenderFindEditRegistration(event_id) {
    // var event_id = $(this).attr('event_id');
    console.log('Render the Find Registrations for event = ' + event_id);
	$.ajax({
	  url: 'ACCOUNTManagement.php',
	  type: 'POST',
	  data: {method: 'NewRenderFindEditRegistration', event_id:event_id},
	  success: function(data) {
        console.log(data);
		results = eval(data);
		// $("#details_info").html(results[0]);
		$("#main_content_container").html(results[0]);
		RenderBackButton('backtomaineventtoolset', event_id);
		
		document.querySelector('[id="participant_name_search"]').onkeyup = FilterParticipantSearch;
		$("table").stupidtable();

		var elms = document.querySelectorAll("[id='edit_find_participant_link']");
		console.log('RenderFindEditRegistration: Found', elms.length, 'edit_find_participant_link elements.')
		for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = HandleAccountManagementActionClick;
		}

        var elms = document.querySelectorAll("[id^='participant_paid_checkbox_']");
        for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = EmailPaymemtProcessedByAdmin;
		}		
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	EmailPaymemtProcessedByAdmin
// Description: This function emails a participant that their payment has been processed 
//              by an event admin.
// -------------------------------------------------------------------------------------
function EmailPaymemtProcessedByAdmin() {
    const event_name = $(this).attr('event_name');
    const email_address = $(this).attr('email_address');
    const participant_name = $(this).attr('part_name');
    const participant_id = $(this).attr('part_idnum');
    console.log('Called EmailPaymemtProcessedByAdmin for '+participant_name+' : '+email_address+' : '+participant_id+'');
    var checkbox = document.getElementById('participant_paid_checkbox_'+participant_id+'').checked;
    if (checkbox) {
        $.ajax({
            url: 'GENERALFunctions.php',
            type: 'POST',
            data: {method: 'SendRegistrationPaymentConfirmation', event_name:event_name, email_address:email_address, participant_name:participant_name, participant_id:participant_id},
            success: function(data) {
            console.log(data);         
            }
        });
    } else {
        $.ajax({
            url: 'GENERALFunctions.php',
            type: 'POST',
            data: {method: 'RemovePaymentConfirmation', participant_id:participant_id},
            success: function(data) {
            console.log(data);         
            }
        });
    }		

}

// -------------------------------------------------------------------------------------
// Function: 	RenderEditNewsContainer
// Description: This function renders the page to edit or remove the news section of the
//              main page.
// -------------------------------------------------------------------------------------
function RenderEditNewsContainer() {
	var section_name = $(this).attr('id');
	$.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderEditNewsContainer', section_name:section_name},
	  success: function(data) {
		 //alert(data);
		results = eval(data);
		$("#details_info").html(results[0]);
		
		document.querySelector('[id="edit_news_container_textarea"]').onkeyup = CheckForZeroLength;
		//document.querySelector('[id^="edit_news_container_textarea_button_"]').onclick = ContentSectionEditAction;
		//$("table").stupidtable();
		//
		var elms = document.querySelectorAll("[id^='edit_news_container_textarea_button_']");
		for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = ContentSectionEditAction;
		}
		
	  }
	});		
}

// -------------------------------------------------------------------------------------
// Function: 	CheckForZeroLength
// Description: This function checks for a zero length variable.
// -------------------------------------------------------------------------------------
function CheckForZeroLength() {
	var container = $(this).attr('id');
	
	text_length = $("#"+container+"").val().length;	
	console.log('Length of the '+container+' container = ' +text_length );
	
	if (container == 'edit_news_container_textarea') {
		$("#edit_news_container_textarea").attr('strlen', text_length);
		$("#edit_news_container_textarea_button_save").removeClass('disabled');
		$("#edit_news_container_textarea_button_save").addClass('primary');
	}
	
}

// -------------------------------------------------------------------------------------
// Function: 	ContentSectionEditAction
// Description: This function renders the page to edit or remove the news section of the
//              main page.
// -------------------------------------------------------------------------------------
function ContentSectionEditAction(event) {
	event.preventDefault();
	var section = $(this).attr('section');
	var action = $(this).attr('action');
	
	//News Section
	if (section == 'edit_news_container_textarea') {
		if (action == 'save') {
			console.log('Save the contents to the database');
			$("#edit_news_container_textarea_button_save").addClass('disabled');
			$("#edit_news_container_textarea_button_save").removeClass('primary');
	        $.ajax({
	          url: 'GENERALFunctions.php',
	          type: 'POST',
	          data: {method: 'UpdateSectionTextInDatabase', section_name:section, section_text:$("#edit_news_container_textarea").val()},
	          success: function(data) {
	        	results = eval(data);
	          }
	        });	
			if ($("#edit_news_container_textarea").val().length == 0) {
				$("#edit_news_container_textarea_button_hide").html('Show News Section');
			}
				
			
		} else if (action == 'hide') {
			console.log('Hide or Show = '+$(this).attr('state'));
			if ($(this).attr('state') == 'show') {
				$("#edit_news_container_textarea_button_hide").html('Show News Section');
				$("#edit_news_container_textarea_button_hide").attr('state', 'hide');
			} else {
				$("#edit_news_container_textarea_button_hide").html('Hide News Section');
				$("#edit_news_container_textarea_button_hide").attr('state', 'show');
			}
	        $.ajax({
	          url: 'GENERALFunctions.php',
	          type: 'POST',
	          data: {method: 'ShowHidePageSection', section_name:section, state:$(this).attr('state')},
	          success: function(data) {
	        	results = eval(data);
	          }
	        });			
			
				
				
		} else {
			console.log('Unknown Action');
		}
	}
}

// -------------------------------------------------------------------------------------
// Function: 	FilterParticipantSearch
// Description: This function filters the participants list.
// -------------------------------------------------------------------------------------
function FilterParticipantSearch () {
    var value = $(this).val().toLowerCase();
    $("#participant_checkin_table > tbody > tr").filter(function() {
    var name = $(this).attr('name');
  	    $('[name="'+name+'"]').toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	ReactToGenerationSelection																	*/
/* Description:	This function reacts to the selection of role for the second person being registered.		*/
/* -------------------------------------------------------------------------------------------------------- */
function ReactToGenerationSelection(element) {
	var generation = $(this).val();
	var driver = 'driver1';
	var current_choice = $(this).attr('current_choice') 

	$('[id^="nsx_gen_response_container_'+driver+'_"]').each(function() {
		if(!($(this).attr('id').endsWith("_select_gen"))) {
			$(this).removeClass("active");
		}		
	});

	if (generation != 'Other') {
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


// -------------------------------------------------------------------------------------
// Function: 	RenderParticipantInformation
// Description: This function renders the participant information and allows editing.
// -------------------------------------------------------------------------------------
function RenderParticipantInformation(event_id, participant_id, collect_payment) {
	console.log('Render participant information (556)');
	// var participant_id = $(this).attr('participant_id');
    // var event_id = $(this).attr('event_id');
    // var collect_payment = $(this).attr('collect_payments');
    var back_button_action = 'findandeditparticipant';
	console.log(participant_id, ":", event_id, ":", collect_payment);
	$.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'CreateConfirmationPage', driver1:participant_id, driver2:'', event_id:event_id, collect_payment:collect_payment, backbutton:"yes", sold_out:"no"},
	  success: function(data) {
		results = eval(data);
		// $("#details_info").html(results[0]);
		// $("#details_info").append('<button class="primary fit" id="edit_participant_information_button" event_id="'+event_id+'" participant_id="'+participant_id+'" collect_payment="'+collect_payment+'" backbutton="no" style="margin-top:1.0em;">Edit Information</button>');
				
		$("#main_content_container").html(results[1]);
		$("#main_content_container").append('<button class="primary fit" id="edit_participant_information_button" event_id="'+event_id+'" participant_id="'+participant_id+'" collect_payments="'+collect_payment+'" backbutton="no" style="margin-top:1.0em;">Edit Information</button>');
		
        RenderBackButton(back_button_action, event_id);

		document.querySelector('[id="edit_participant_information_button"]').onclick = RenderEditParticipantInformation;

	  }
	});	
}



// -------------------------------------------------------------------------------------
// Function: 	RenderEditParticipantInformation
// Description: This function renders the participant information and allows editing.
// -------------------------------------------------------------------------------------
function RenderEditParticipantInformation() {
	console.log('Render Edit participant information');
	var participant_id = $(this).attr('participant_id');
    var event_id = $(this).attr('event_id');
    var collect_payment = $(this).attr('collect_payments');
	console.log(participant_id, ":", event_id, ":", collect_payment);	
	$.ajax({
	  url: 'PARTICIPANTManagement.php',
	  type: 'POST',
	  data: {method: 'RenderParticipantInformation', event_id:event_id, participant_id:participant_id, collect_payment:collect_payment},
	  success: function(data) {
		results = eval(data);
		$("#main_content_container").html(results[0]);
		$("#main_content_container").append('<button class="primary fit" id="update_participant_information_button" participant_id="'+participant_id+'" event_id="'+event_id+'" collect_payments="'+collect_payment+'" style="margin-top:1.0em;">Update Information</button>');
		document.querySelector('[id="update_participant_information_button"]').onclick = UpdateParticipantInformation;
        // RenderBackButton('backtofindeditregistration', event_id);
		document.querySelector('[id="back_button_link"]').onclick = HandleBackButtonClick;
		// Generation Selection
		var elms = document.querySelectorAll('[name^="generation_selection_driver"]');
		for (i = 0; i < elms.length; i++) {
			elms[i].onchange = ReactToGenerationSelection;
		}		
	  }
	});	
}

// -------------------------------------------------------------------------------------f
// Function: 	UpdateParticipantInformation
// Description: This function renders the participant information and allows editing.
// -------------------------------------------------------------------------------------
function UpdateParticipantInformation() {
	console.log('Update information');
	var participant_id = $("#update_participant_information_button").attr('participant_id');
    var event_id = $(this).attr('event_id');
    var collect_payment = $(this).attr('collect_payments');
	console.log(participant_id, ":", event_id, ":", collect_payment);	
	var update_items = new Object();
	var car_information = new Object();
	var checkbox_information = new Object();
	var update_participant_info = false;
	
	// Check Text Fields
	$('[id^="info_text_field_driver1_"]').each(function() {
		if ($(this).attr('start_value') == $(this).val()) {
			console.log('Value = '+$(this).attr('database_key') + ' Do not update');
		} else {
			update_participant_info = true;
			console.log('Value = '+$(this).attr('database_key') + 'Update');
			update_items[$(this).attr('database_key')] = $(this).val();
		}
	});
	
	// Check State Select Values
	var state = document.getElementById("info_select_field_driver1_state");
	if (state.getAttribute('start_value') != state.value) {
		update_participant_info = true;
		update_items[state.getAttribute('database_key')] = state.value;
	}

	// Look for a change in the type of car selected.
	var generation = document.getElementById("nsx_gen_response_gen_selection_driver1");
	if (generation.getAttribute('start_value') != generation.value) {
		console.log('Different car all together selected');
		update_participant_info = true;
		if (generation.value == 'Other') {
			update_items['Driver_Is_NSX'] = 0;
			var car_make  = document.getElementById("nsx_gen_response_driver1_other_make");
			var car_model = document.getElementById("nsx_gen_response_driver1_other_model");
			var car_year  = document.getElementById("nsx_gen_response_driver1_other_year");
			car_information['car_make'] = car_make.value;
			car_information['car_model'] = car_model.value;
			car_information['car_year'] = car_year.value;
			console.log(JSON.stringify(car_information));
			update_items['Driver_Car_Info'] = JSON.stringify(car_information);
			update_items['Driver_Car_Image'] = 'Other_car.png';
			
		} else {
			update_items['Driver_Is_NSX'] = 1;
			var car_year = document.getElementById("nsx_gen_select_driver1_nsx_year");
			var car_color = document.getElementById("nsx_gen_select_driver1_nsx_color");
			car_information['car_year'] = car_year.value;
			car_information['car_color'] = car_color.value;
			console.log(JSON.stringify(car_information));
			update_items['Driver_Car_Info'] = JSON.stringify(car_information);
			if (generation.value == 'First_Generation') {
				if (parseInt(car_information['car_year']) <= 2001) {
					update_items['Driver_Car_Image'] = (generation.value).toLowerCase()+'_popup_'+car_information['car_color'].toLowerCase()+'.png';
				} else {
					//alert(car_information['car_color']);
					update_items['Driver_Car_Image'] = (generation.value).toLowerCase()+'_nonpopup_'+car_information['car_color'].toLowerCase()+'.png';
				}
			} else {
				update_items['Driver_Car_Image'] = (generation.value).toLowerCase()+'_new_'+car_information['car_color'].toLowerCase()+'.png';
			}			
		}
		
	// Otherwise, look for changes in the car details
	} else {
		var need_update = false;
		// Look for NSX Changes
		if (generation.value != 'Other') {
			$('[id^="nsx_gen_select_driver1_nsx_"]').each(function() {
				var elem = document.getElementById($(this).attr('id'));
				console.log($(this).attr('id') + ' : Element Value = ' + elem.value);
				if (elem.getAttribute('start_value') != elem.value) {
					need_update = true;
					update_participant_info = true;
				}
				car_information[elem.getAttribute('database_key')] = elem.value;
			});
			
			if (need_update) {
				if (generation.value == 'First_Generation') {
					if (parseInt(car_information['car_year']) <= 2001) {
						update_items['Participant_Car_Image'] = (generation.value).toLowerCase()+'_popup_'+car_information['car_color'].toLowerCase()+'.png';
					} else {
						alert(car_information['car_color']);
						update_items['Participant_Car_Image'] = (generation.value).toLowerCase()+'_nonpopup_'+car_information['car_color'].toLowerCase()+'.png';
					}
				} else {
					update_items['Participant_Car_Image'] = (generation.value).toLowerCase()+'_new_'+car_information['car_color'].toLowerCase()+'.png';
				}				
			 }			
			
		} else {
		// Look for Other Car Changes
			$('[id^="nsx_gen_response_driver1_other_"]').each(function() {
				if ($(this).attr('start_value') != $(this).val()) {
					need_update = true;
					update_participant_info = true;
				}
				car_information[$(this).attr('database_key')] = $(this).val();
			});
			
			if (need_update) {
				update_items['Participant_Car_Image'] = 'Other_car.png';
			}
		}
		
		if (need_update) {
			console.log(JSON.stringify(car_information));
			update_items['Participant_Car_Info'] = JSON.stringify(car_information);
		}
	}
	
	// look for updates in the Participant options Selections
	var checkbox_update = false;
	$('[id^="info_checkbox_field_driver1_"]').each(function () {
		console.log($(this).attr('key') + ' = ' + $(this).attr('start_value'));
		var checkbox = document.getElementById($(this).attr('id')).checked;
		if(checkbox) {
			$new_state = "yes";
		} else {
			$new_state = "no";
		}
		
		if ($new_state != $(this).attr('start_value')) {
			checkbox_update = true;
			update_participant_info = true;
		}
		checkbox_information[$(this).attr('key')] = $new_state;		
	});
	
	if (checkbox_update) {
		console.log(JSON.stringify(checkbox_information));
		update_items['Participant_Events_Attending'] = JSON.stringify(checkbox_information);
	}	
	
	console.log("my object: %o", update_items)
	
	if (update_participant_info) {
	    $.ajax({
	      url: 'GENERALFunctions.php',
	      type: 'POST',
	      data: {method: 'UpdateParticipantInformation', participant_id:participant_id, update_items:update_items},
	      success: function(data) {
			console.log(data);
	    	results = eval(data);
			console.log(results[0]);
			if (results[0] == 'FAIL') {
				alert('The database update failed.');
			} else {
	            $.ajax({
	              url: 'GENERALFunctions.php',
	              type: 'POST',
	              //data: {method: 'RenderParticipantInformation', driver_id:driver_id},
	              data: {method: 'CreateConfirmationPage', driver1:participant_id, driver2:''},
	              success: function(data) {
	            	results = eval(data);
	            	$("#details_info").html(results[0]);
	            	$("#details_info").append('<button class="primary fit" id="edit_participant_information_button" participant_id="'+participant_id+'" style="margin-top:1.0em;">Edit Information</button>');
	            	
	            	document.querySelector('[id="edit_participant_information_button"]').onclick = RenderEditParticipantInformation;
                
	              }
	            });
			}
	      }
	    });		
	} else {
	    $.ajax({
	      url: 'GENERALFunctions.php',
	      type: 'POST',
	      //data: {method: 'RenderParticipantInformation', driver_id:driver_id},
	      data: {method: 'CreateConfirmationPage', driver1:driver_id, driver2:''},
	      success: function(data) {
	    	results = eval(data);
	    	$("#details_info").html(results[0]);
	    	$("#details_info").append('<button class="primary fit" id="edit_participant_information_button" driver_id="'+driver_id+'" style="margin-top:1.0em;">Edit Information</button>');
	    	
	    	document.querySelector('[id="edit_participant_information_button"]').onclick = RenderEditParticipantInformation;
        
	      }
	    });
	}
}

// -------------------------------------------------------------------------------------
// Function: 	DownloadParticipantInformation
// Description: This function renders the participant information and allows editing.
// -------------------------------------------------------------------------------------
function DownloadParticipantInformation(event) {
    event.preventDefault();
    var event_id = $(this).attr('event_id');
    var event_name = $(this).attr('event_name').replace(/ /g,"_")+'_Participants.csv'
    console.log(event_name);
	    $.ajax({
	      url: 'GENERALFunctions.php',
	      type: 'POST',
	      data: {method: 'DownloadParticipantInformation', event_id:event_id},
	      success: function(data) {
            console.log(data);
            let csvContent = "data:text/csv;charset=utf-8," + data
            
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", event_name);
            document.body.appendChild(link); // Required for FF
            
            link.click();	 
	      }
	    });	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderEditLocalAttraction 
// Description: This function renders the page to edit / add / delete a local attraction.
// -------------------------------------------------------------------------------------
function RenderEditLocalAttraction(event) {
    var event_id = $(this).attr('event_id');
    console.log("Render the main local attractions window for event = " + event_id);
    $.ajax({
        url: 'LOCALAttractionFunctions.php',
        type: 'POST',
        data: {method: 'RenderOpeningPageInformation', event_id:event_id, editable:true},
        success: function(data) {
          results = eval(data);
          $("#details_info").html(results[0]);
		  
		  var elms = document.querySelectorAll('[id^="local_attraction_link_"]');
		  for (i = 0; i < elms.length; i++) {
			  elms[i].onclick = ReactToMenuLinkSelection;
		  }  
		  var carLmt = 300;
		  // Text to show when text is collapsed
		  var readMoreTxt = " ...read more";
		  // Text to show when text is expanded
		  var readLessTxt = " read less";
  
		  $('[id^="attract_description_"]').each(function () {
			  if ($(this).find(".first-section").length)
				 return;
	 
			  var allstr = $(this).text();
			  if (allstr.length > carLmt) {
				 var firstSet = allstr.substring(0, carLmt);
				 var secdHalf = allstr.substring(carLmt, allstr.length);
				 var strtoadd = firstSet + "<span class='second-section'>" + secdHalf + "</span><span class='read-more'  title='Click to Show More'>" + readMoreTxt + "</span><span class='read-less' title='Click to Show Less'>" + readLessTxt + "</span>";
				 $(this).html(strtoadd);
			  }
		   });
  
		   $(document).on("click", ".read-more,.read-less", function () {
			  $(this).closest(".add-read-more").toggleClass("show-less-content show-more-content");
		   });		  		  
        }
      });	    
}
// -------------------------------------------------------------------------------------
// OVERLORD TOOLSET
//-------------------------------------------------------------------------------------


// -------------------------------------------------------------------------------------
// Function: 	RenderMyAccountOverLord
// Description: This function renders the user information for a signed in user.
// -------------------------------------------------------------------------------------
function RenderMyAccountOverLord() {
    var user_id = $("#session_information").attr('info_one');
    var email = $("#session_information").attr('info_two');    
    console.log('Entered RenderMyAccountOverLord() with user_id = '+user_id+' and email = '+email+'');

	$.ajax({
	    url: 'ACCOUNTManagement.php',
	    type: 'POST',
	    data: {method: 'RenderMyAccountOverLord'},
	    success: function(data) {
	  	  console.log(data);
	  	  results = eval(data);
          if (results[0] == 'Pass') {
            $("#main_content_container").html(results[1]);
          } else {
            console.log('fail');
          }

		  var elms = document.querySelectorAll("[id='overlord_tools_button']");
		  for(var i = 0; i < elms.length; i++) {
			  elms[i].onclick = RenderOverLordTool;
		  }		  
	    },	
	  });		
}

// -------------------------------------------------------------------------------------
// Function: 	RenderOverLordTool
// Description: This function renders the individual OVERLORD tools as clicked.
// -------------------------------------------------------------------------------------
function RenderOverLordTool() {
	var action = $(this).attr('action');
	console.log(action);
	if (action == 'create_event') {
		$.ajax({
			url: 'ACCOUNTManagement.php',
			type: 'POST',
			data: {method: 'CreateEnterNewEventPage'},
			success: function(data) {
			  results = eval(data);
			  $("#details_info").html(results[0]);

			  document.querySelector('[id="create_new_event_button"]').onclick = ValidateAndEnterNewEventToDatabase;

			}
		  });	
	} else {
		console.log('Unsupported Option');
	}
}

// -------------------------------------------------------------------------------------
// Function: 	EnterNewEventToDatabase
// Description: This function enters a new event into the database.
// -------------------------------------------------------------------------------------
async function ValidateAndEnterNewEventToDatabase() {
	// $("#main_event_creation_container").html("<h2>Processing....</h2>");
	console.log('Enter a new event into the database.')
	entered = false;
	await EnterNewEventToDatabase()
	.then((data) => {
		console.log(data);
		database_results = eval(data);
		if (database_results[0] === "success") {
			console.log('Event successfully created...');
			new_path = database_results[1]
			entered = true;
		} else {
			console.log('driver1 failure');
			entered = false;
		}
	})
	.catch((error) => {
		console.log(error)
	})
	
	if (entered) {
		console.log('The event was successfully created, now, create the directory path at /events/', new_path);
		await CreateNewDirectoryPath(new_path)
		.then((data) => {
			console.log(data);
			create_path_results = eval(data);
			// if (database_results[0] === "success") {
			// 	console.log('Event successfully created...');
			// 	new_path = database_results[1]
			// 	entered = true;
			// } else {
			// 	console.log('driver1 failure');
			// 	entered = false;
			// }
		})
		.catch((error) => {
			console.log(error)
		})

	} else {
		console.log('The event was NOT created successfully, do NOT create the event directory')
	}
}

/* -------------------------------------------------------------------------------------------------------- */
/* Function:	EnterNewEventToDatabase			   														    */
/* Description:	This function enters the new event into the database            							*/
/* -------------------------------------------------------------------------------------------------------- */
function EnterNewEventToDatabase() {
    var event_name = $("#info_text_field_event_name").val();
    var event_directory = $("#info_text_field_event_directory").val();
    var start_date = $("#info_text_field_event_start_date").val();
	var end_date = $("#info_text_field_event_end_date").val();	
    var event_admins = document.getElementById("info_text_field_event_admins").value;
	var sponsors = document.getElementById('info_checkbox_field_activate_sponsors').checked;
	var event_opening = document.getElementById("info_text_field_event_opening").value;	

	return new Promise((resolve, reject) => {
		$.ajax({
		  url: 'EVENTCreation.php',
		  type: 'POST',
		  data: {method: 'EnterNewEventToDatabase', csrf_token:$("#csrf_token_field").val(), event_name:event_name, event_directory:event_directory, start_date:start_date, end_date:end_date, event_admins:event_admins, sponsors:sponsors, event_opening:event_opening},
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
/* Function:	EnterNewEventToDatabase			   														    */
/* Description:	This function enters the new event into the database            							*/
/* -------------------------------------------------------------------------------------------------------- */
function CreateNewDirectoryPath(new_path) {
	return new Promise((resolve, reject) => {
		$.ajax({
		  url: 'EVENTCreation.php',
		  type: 'POST',
		  data: {method: 'CreateNewDirectoryPath', csrf_token:$("#csrf_token_field").val(), new_path:new_path},
		  success: function(data) {
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

