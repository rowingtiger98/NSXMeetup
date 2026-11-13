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

	else if (action === 'edit_weekend_events' ){
        var event_id = $(this).attr('event_id');
		RenderWeekendEvents(event_id);
    }

	else if (action === 'edit_form_questions' ){
        var event_id = $(this).attr('event_id');
		RenderEventQuestions(event_id);
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
		results = JSON.parse(data);
		if (results[0] === 'Fail') { // not an admin of this event, or the session expired
			$("#main_content_container").html('<p class="event_settings_error">' + $('<div>').text([].concat(results[1]).join(' ')).html() + '</p>');
			return;
		}
		// $("#details_info").html(results[0]);
		$("#main_content_container").html(results[0]);
		RenderBackButton('backtomaineventtoolset', event_id);
		
		document.querySelector('[id="participant_name_search"]').onkeyup = FilterParticipantSearch;
		$("table").stupidtable();

		// The name and the magnifying glass both open the registration
		var elms = document.querySelectorAll("[id='edit_find_participant_link'], .registrant_view_icon");
		console.log('RenderFindEditRegistration: Found', elms.length, 'edit_find_participant_link elements.')
		for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = HandleAccountManagementActionClick;
		}

        var elms = document.querySelectorAll("[id^='participant_paid_checkbox_']");
        for(var i = 0; i < elms.length; i++) {
			elms[i].onclick = EmailPaymemtProcessedByAdmin;
		}		
		$(".registrant_delete_button").on('click', DeleteRegistrant);
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	EmailPaymemtProcessedByAdmin
// Description: The Received checkbox on the registrant list. Saves the registrant as
//              paid / not paid (REGISTRANTManagement.php SetRegistrantPaid); marking
//              them paid also emails them that their payment was received. If the save
//              fails, the checkbox and status go back to how they were.
// -------------------------------------------------------------------------------------
function EmailPaymemtProcessedByAdmin() {
    var checkbox = this;
    var participant_id = $(this).attr('part_idnum');
    var paid = checkbox.checked;
    UpdateRegistrantStatus(participant_id, paid);
    checkbox.disabled = true;
    $.ajax({
        url: 'REGISTRANTManagement.php',
        type: 'POST',
        dataType: 'json',
        data: {method: 'SetRegistrantPaid', event_id: $("#edit_find_user_registration").attr('event_id'), participant_id: participant_id, paid: paid ? '1' : '0', csrf_token: $("#edit_find_user_registration").attr('csrf_token')},
        success: function(results) {
            if (results[0] == 'Pass') {
                ShowRegistrantListMessage(results[1], false);
            } else {
                checkbox.checked = !paid;
                UpdateRegistrantStatus(participant_id, !paid);
                ShowRegistrantListMessage([].concat(results[1]).join(' '), true);
            }
        },
        error: function() {
            checkbox.checked = !paid;
            UpdateRegistrantStatus(participant_id, !paid);
            ShowRegistrantListMessage('The change could not be saved. Please try again.', true);
        },
        complete: function() {
            checkbox.disabled = false;
        }
    });
}

// -------------------------------------------------------------------------------------
// Function: 	UpdateRegistrantStatus
// Description: Switches a registrant's Payment Status badge when Received is ticked
//              (Paid) or unticked (back to the status it had unpaid - see
//              RenderRegistrantStatus in ACCOUNTManagement.php).
// -------------------------------------------------------------------------------------
function UpdateRegistrantStatus(participant_id, paid) {
    var badge = $('tr[registrant_id="' + participant_id + '"] .registrant_status');
    if (!badge.length || badge.hasClass('status_no_charge') || badge.hasClass('status_wait_list')) {
        return;
    }
    var label = paid ? 'Paid' : badge.attr('unpaid_label');
    var css = paid ? 'status_paid' : badge.attr('unpaid_class');
    var icon = paid ? 'fa-check-circle' : badge.attr('unpaid_icon');
    badge.attr('class', 'registrant_status ' + css);
    badge.find('.icon').attr('class', 'icon solid ' + icon);
    badge.find('.registrant_status_label').text(label);
    badge.closest('td').attr('data-sort-value', label);
}

// -------------------------------------------------------------------------------------
// Function: 	DeleteRegistrant
// Description: The first click on a registrant's trash can asks for confirmation on the
//              button itself; a second click within a few seconds deletes the
//              registration (REGISTRANTManagement.php) and removes its row.
// -------------------------------------------------------------------------------------
function DeleteRegistrant(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    if (!button.hasClass('confirming')) {
        button.addClass('confirming');
        setTimeout(function() {
            button.removeClass('confirming');
        }, 4000);
        return;
    }
    button.removeClass('confirming').addClass('disabled');

    var row = button.closest('tr');
    $.ajax({
        url: 'REGISTRANTManagement.php',
        type: 'POST',
        dataType: 'json',
        data: {method: 'DeleteRegistrant', event_id: $("#edit_find_user_registration").attr('event_id'), participant_id: button.attr('participant_id'), csrf_token: $("#edit_find_user_registration").attr('csrf_token')},
        success: function(results) {
            if (results[0] == 'Pass') {
                ShowRegistrantListMessage(results[1], false);
                row.fadeOut(300, function() { row.remove(); });
            } else {
                ShowRegistrantListMessage([].concat(results[1]).join(' '), true);
            }
        },
        error: function() {
            ShowRegistrantListMessage('The registration could not be deleted. Please try again.', true);
        },
        complete: function() {
            button.removeClass('disabled');
        }
    });
}

function ShowRegistrantListMessage(message, is_error) {
    $("#registrant_list_messages").html($('<p>').addClass(is_error ? 'event_settings_error' : 'event_settings_success').text(message));
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
function RenderParticipantInformation(event_id, participant_id, collect_payment, messages) {
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
		if (messages && messages.length) { // e.g. after Update Information: saved, and any balance change
			var list = $('<ul class="event_settings_success registrant_saved_messages">');
			messages.forEach(function(message) { list.append($('<li>').text(message)); });
			$("#main_content_container").prepend(list);
		}
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
		results = JSON.parse(data);
		if (results[0] === 'Fail') { // not an admin of this event, or the session expired
			$("#main_content_container").html('<p class="event_settings_error">' + $('<div>').text([].concat(results[1]).join(' ')).html() + '</p>');
			return;
		}
		$("#main_content_container").html(results[0]);
		$("#main_content_container").append('<div id="edit_participant_messages"></div>');
		$("#main_content_container").append('<button class="primary fit" id="update_participant_information_button" participant_id="'+participant_id+'" event_id="'+event_id+'" collect_payments="'+collect_payment+'" style="margin-top:1.0em;">Update Information</button>');
		document.querySelector('[id="update_participant_information_button"]').onclick = UpdateParticipantInformation;
		// Balance preview: registration type and the pre-pay option boxes
		$("#edit_registration_type").on('change', UpdateEditBalancePreview);
		$('[id^="info_checkbox_field_driver1_"]').on('change', UpdateEditBalancePreview);
		$('[id^="info_question_select_field_driver1_"]').on('change', UpdateEditBalancePreview);
		UpdateEditBalancePreview();
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

// -------------------------------------------------------------------------------------
// Function: 	UpdateParticipantInformation
// Description: Sends the Edit Information page to REGISTRANTManagement.php
//              (SaveRegistrant), which checks it, builds the car details and works out
//              the balance again. Then goes back to the registrant's page with the
//              result - including any balance change.
// -------------------------------------------------------------------------------------
function UpdateParticipantInformation(event) {
	if (event) {
		event.preventDefault();
	}
	var button = $("#update_participant_information_button");
	if (button.hasClass('disabled')) {
		return;
	}
	var participant_id = button.attr('participant_id');
	var event_id = button.attr('event_id');
	var collect_payment = button.attr('collect_payments');

	var data = {
		method: 'SaveRegistrant',
		event_id: event_id,
		participant_id: participant_id,
		csrf_token: $("#edit_participant_csrf_token").val(),  // sent with the form, so it matches the current session
		name: $("#info_text_field_driver1_name").val(),
		email: $("#info_text_field_driver1_email").val(),
		cell: $("#info_text_field_driver1_cell").val(),
		street: $("#info_text_field_driver1_street").val(),
		city: $("#info_text_field_driver1_city").val(),
		state: $("#info_select_field_driver1_state").val(),
		zip: $("#info_text_field_driver1_zip").val()
	};
	// Riders and children have no car questions on the page - their car isn't sent
	if ($("#nsx_gen_response_gen_selection_driver1").length) {
		data.car_generation = $("#nsx_gen_response_gen_selection_driver1").val();
	}
	if (data.car_generation === 'Other') {
		data.car_make = $("#nsx_gen_response_driver1_other_make").val();
		data.car_model = $("#nsx_gen_response_driver1_other_model").val();
		data.car_year = $("#nsx_gen_response_driver1_other_year").val();
	} else if (data.car_generation) {
		data.car_year = $("#nsx_gen_select_driver1_nsx_year").val();
		data.car_color = $("#nsx_gen_select_driver1_nsx_color").val();
	}
	// The registration type is only on the page when the event collects payments
	if ($("#edit_registration_type").length) {
		data.registration_type = $("#edit_registration_type").val();
	}
	// Answers to the event's questions: yes / no boxes and drop downs
	$('[id^="info_checkbox_field_driver1_"]').each(function() {
		data['answers[' + $(this).attr('key') + ']'] = this.checked ? 'yes' : 'no';
	});
	$('[id^="info_question_select_field_driver1_"]').each(function() {
		data['answers[' + $(this).attr('key') + ']'] = $(this).val() || '';
	});

	button.addClass('disabled').text('Saving...');
	$.ajax({
		url: 'REGISTRANTManagement.php',
		type: 'POST',
		dataType: 'json',
		data: data,
		success: function(results) {
			if (results[0] == 'Pass') {
				RenderParticipantInformation(event_id, participant_id, collect_payment, results[1]);
			} else {
				ShowEditParticipantMessages([].concat(results[1]), true);
			}
		},
		error: function() {
			ShowEditParticipantMessages(['The registration could not be saved. Please try again.'], true);
		},
		complete: function() {
			button.removeClass('disabled').text('Update Information');
		}
	});
}

function ShowEditParticipantMessages(messages, is_error) {
	var list = $('<ul>').addClass(is_error ? 'event_settings_error' : 'event_settings_success');
	messages.forEach(function(message) {
		list.append($('<li>').text(message));
	});
	$("#edit_participant_messages").html(list);
}

// -------------------------------------------------------------------------------------
// Function: 	UpdateEditBalancePreview
// Description: On the Edit Information page, shows what the balance will become: the
//              chosen registration type's cost plus the pre-pay options chosen (boxes
//              and drop-downs with accounting="yes"), as SaveRegistrant works it out. Without a
//              registration type the balance isn't recalculated.
// -------------------------------------------------------------------------------------
function UpdateEditBalancePreview() {
	var container = $("#edit_registration_type_container");
	if (!container.length) {
		return;
	}
	var current = parseInt(container.attr('current_balance'), 10) || 0;
	var type_cost = $("#edit_registration_type option:selected").attr('cost');
	var preview = $("#edit_balance_preview");
	var format = function(cents) { return '$' + (cents / 100).toFixed(2); };

	if (type_cost === undefined || type_cost === '') {
		preview.text(format(current) + ' (not recalculated)');
		return;
	}
	var balance = parseInt(type_cost, 10) || 0;
	$('[id^="info_checkbox_field_driver1_"][accounting="yes"]').each(function() {
		if (this.checked) {
			balance += Math.round((parseFloat($(this).attr('cost')) || 0) * 100);
		}
	});
	// A charged drop-down adds its amount when any choice is picked
	$('[id^="info_question_select_field_driver1_"][accounting="yes"]').each(function() {
		if ($(this).val()) {
			balance += Math.round((parseFloat($(this).attr('cost')) || 0) * 100);
		}
	});
	preview.empty();
	if (balance === current) {
		preview.text(format(balance));
	} else {
		preview.append($('<span class="edit_balance_old">').text(format(current)), ' → ', $('<span class="edit_balance_new">').text(format(balance)));
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
          $("#main_content_container").html(results[0]);
          RenderBackButton('backtomaineventtoolset', event_id);
		  
		  var elms = document.querySelectorAll('[id^="local_attraction_link_"]');
		  for (i = 0; i < elms.length; i++) {
			  elms[i].onclick = ReactToMenuLinkSelection;
		  }
		  $("#local_attraction_add_button").on('click', function(e) {
			  e.preventDefault();
			  OpenLocalAttractionForm(event_id, '');
		  });
		  // Delegated, so the Edit buttons keep working after a category's table is redrawn
		  $("#local_attractions_content").on('click', '.local_attraction_edit_button', function(e) {
			  e.preventDefault();
			  OpenLocalAttractionForm(event_id, $(this).attr('attraction_id'));
		  });
		  $("#local_attractions_content").on('click', '.local_attraction_delete_button', DeleteLocalAttraction);
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

