// The site's top folder URL, worked out from where this script was loaded
// (.../assets/js/render.js), so the calls to LOCALAttractionFunctions.php below work from
// an event's page in events/<dir>/ as well as from the top folder.
var RENDER_SITE_BASE = (document.currentScript && document.currentScript.src)
    ? document.currentScript.src.replace(/assets\/js\/render\.js(\?.*)?$/, '') : '';

// -------------------------------------------------------------------------------------
// Function: 	RenderEventStatsContent
// Description: This function renders the attending page.
// -------------------------------------------------------------------------------------
function RenderEventStatsContent() {
	//event.preventDefault();
    console.log('Testing');
    var event_id = $("#event_stats").attr('event_id');
	$.ajax({
	  url: '/GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderEventStatsContent', event_id:event_id},
	  success: function(data) {
        // console.log(data);
		results = eval(data);
        $("#event_stats").html(results[0]);	
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderAttendingPage
// Description: This function renders the attending page.
// -------------------------------------------------------------------------------------
function RenderAttendingPage() {
	//event.preventDefault();

    var event_id = $(this).attr('event_id');
    console.log("Render the particiapant for event id = " + event_id);
	$.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderAttendingInformation', event_id:event_id},
	  success: function(data) {

		results = eval(data);
        $("#flipable_link").html('Home');
        $("#flipable_link").attr('href', 'index.php');
        $("#navlink_flipable_link").html('Home');
        $("#navlink_flipable_link").attr('href', 'index.php');
		$("#welcome").removeClass('active');
		$("#banner").removeClass('active');
        $("#details").removeClass('opener');        
        $("#register_button_container").removeClass('active');
		$("#details_info").html(results[0]);

		var elms = document.querySelectorAll('[id^="more_participant_info_link_"]');
		console.log('HRE '+ elms.length);
		for (i = 0; i < elms.length; i++) {
			elms[i].onclick = ShowParticipantExtraInformation;
		}		
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderLocalAttractionsPage
// Description: This function renders the local attractions page.
// -------------------------------------------------------------------------------------
function RenderLocalAttractionsPage(event) {
    var event_id = $(this).attr('event_id');
    var editable = $(this).attr('editable');
    var category = $(this).attr('category') || '';  // the category to open on, e.g. "hotels"; the first one if blank
    console.log("Render the locals attractions for event id = " + event_id + ' with editable = ' + editable);
	$.ajax({
	  url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderOpeningPageInformation', event_id:event_id, editable:editable, category:category},
	  success: function(data) {
		results = eval(data);
        console.log('Results', results);        
        $("#flipable_link").html('Home');
        $("#flipable_link").attr('href', 'index.php');
        $("#navlink_flipable_link").html('Home');
        $("#navlink_flipable_link").attr('href', 'index.php');
		$("#welcome").removeClass('active');
		$("#banner").removeClass('active');
        $("#details").removeClass('opener');
        $("#rivernsx").removeClass('active');
        $("#batnsxr").removeClass('active'); 
        $("#hangout").removeClass('active');         
        $("#register_button_container").removeClass('active');
		$("#details_info").html(results[0]);

        var elms = document.querySelectorAll('[id^="local_attraction_link_"]');
        for (i = 0; i < elms.length; i++) {
            elms[i].onclick = ReactToMenuLinkSelection;
        }    

        var elms = document.querySelectorAll('[id^="show_more_attraction_information_"]');
        for (i = 0; i < elms.length; i++) {
          elms[i].onclick = ReactToShowMoreLocalInformationClick;
        }

	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	ReactToMenuLinkSelection
// Description: This function renders the local attractions page.
// -------------------------------------------------------------------------------------
function ReactToMenuLinkSelection(event) {
    var event_id = $(this).attr('event_id');
    var section_name = $(this).attr('section_name');

    if (section_name == 'create') {
      OpenLocalAttractionForm(event_id, '');
    } else {
      console.log("Handle Menu Link Selection for section = " + section_name);
      $('[id^="local_attraction_link_"]').removeClass('active');
      $("#local_attraction_link_"+section_name+"").addClass('active');
      LoadLocalAttractionSection(event_id, section_name, $(this).attr('editable'));
    }
}

// -------------------------------------------------------------------------------------
// Function: 	LoadLocalAttractionSection
// Description: Shows a category's attractions on the right: the event page's list, or
//              (editable = 'true') the admin tool's table with Edit buttons.
// -------------------------------------------------------------------------------------
function LoadLocalAttractionSection(event_id, section_name, editable) {
    $.ajax({
      url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
      type: 'POST',
      data: {method: 'BuildLocalAttractionSection', event_id:event_id, section_name:section_name, editable:editable || ''},
      success: function(data) {
        results = eval(data);
        $("#local_attractions_content").html(results[0]);

        var elms = document.querySelectorAll('[id^="show_more_attraction_information_"]');
        for (i = 0; i < elms.length; i++) {
          elms[i].onclick = ReactToShowMoreLocalInformationClick;
        }
      }
    });
}

// -------------------------------------------------------------------------------------
// Function: 	OpenLocalAttractionForm
// Description: Shows the attraction form on the right: empty to add one, or filled in
//              with an attraction's details when attraction_id is given.
// -------------------------------------------------------------------------------------
function OpenLocalAttractionForm(event_id, attraction_id) {
    console.log("Open the local attraction form for event = " + event_id + ", attraction = " + attraction_id);
    $.ajax({
      url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
      type: 'POST',
      data: {method: 'CreateNewLocalAttraction', event_id:event_id, attraction_id:attraction_id || ''},
      success: function(data) {
        results = eval(data);
        $("#local_attractions_content").html(results[0]);
        if (!document.getElementById("new_local_attraction_category")) {
          return; // the attraction wasn't found - the message says so
        }
        document.querySelector('[id="new_local_attraction_category"]').onchange = ReactToNewAttractionCartegorySection;
        document.querySelector('[id="new_category_name_container"]').oninput = ShowAttractionReservationLink;
        document.querySelector('[id="submit_new_local_attraction_button"]').onclick = SubmitNewLocalAttraction;
        document.querySelector('[id="cancel_local_attraction_button"]').onclick = CancelLocalAttractionForm;
        ShowAttractionReservationLink(); // an edited hotel shows its link
      }
    });
}

// -------------------------------------------------------------------------------------
// Function: 	CancelLocalAttractionForm
// Description: Closes the attraction form without saving and shows the selected
//              category's table again.
// -------------------------------------------------------------------------------------
function CancelLocalAttractionForm(event) {
    event.preventDefault();
    var link = $("li.local_attraction_link.active");
    if (link.length) {
      LoadLocalAttractionSection(link.attr('event_id'), link.attr('section_name'), link.attr('editable'));
    } else {
      $("#local_attractions_content").empty();
    }
}

// -------------------------------------------------------------------------------------
// Function: 	DeleteLocalAttraction
// Description: The first click asks for confirmation on the button itself; a second
//              click within a few seconds deletes the attraction and redraws the table.
// -------------------------------------------------------------------------------------
function DeleteLocalAttraction(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
      return;
    }
    if (!button.hasClass('regtype_confirm_delete')) {
      button.addClass('regtype_confirm_delete').text('Click again to delete');
      setTimeout(function() {
        button.removeClass('regtype_confirm_delete').text('Delete');
      }, 4000);
      return;
    }
    button.removeClass('regtype_confirm_delete').addClass('disabled').text('Deleting...');
    $.ajax({
      url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
      type: 'POST',
      dataType: 'json',
      data: {method: 'DeleteLocalAttraction', event_id:button.attr('event_id'), attraction_id:button.attr('attraction_id')},
      success: function(results) {
        if (results[0] == 'success') {
          var link = $("li.local_attraction_link.active");
          LoadLocalAttractionSection(link.attr('event_id'), link.attr('section_name'), link.attr('editable'));
        } else {
          $("#local_attraction_list_message").text([].concat(results[1]).join(' ')).show();
          button.removeClass('disabled').text('Delete');
        }
      },
      error: function() {
        $("#local_attraction_list_message").text('The attraction could not be deleted. Please try again.').show();
        button.removeClass('disabled').text('Delete');
      }
    });
}

// Shows why an attraction couldn't be saved, under the form's buttons
function ShowLocalAttractionFormMessage(message) {
    $("#local_attraction_form_message").text(message || 'The attraction could not be saved. Please try again.').show();
}

// -------------------------------------------------------------------------------------
// Function: 	ReactToShowMoreLocalInformationClick
// Description: This function shows or hide the additional local information selected.
// -------------------------------------------------------------------------------------
function ReactToShowMoreLocalInformationClick(event) {
  event.preventDefault();
  var action = $(this).attr('action');
  var attraction_id = $(this).attr('attraction_id');
  console.log('Show ' + action + ' information for attraction ID = ' + attraction_id);
  if (action == 'more') {
    $("#more_attraction_information_container_"+attraction_id+"").addClass('active');
    $("#show_more_attraction_information_"+attraction_id+"").attr('action', 'less');
    $("#show_more_attraction_information_"+attraction_id+"").html('(Show Less Information)');
  } else {
    $("#more_attraction_information_container_"+attraction_id+"").removeClass('active');
    $("#show_more_attraction_information_"+attraction_id+"").attr('action', 'more');
    $("#show_more_attraction_information_"+attraction_id+"").html('(Show More Information)');   
  }
}

// -------------------------------------------------------------------------------------
// Function: 	SubmitNewLocalAttraction
// Description: This function submits a new local attraction to the database.
// -------------------------------------------------------------------------------------
async function SubmitNewLocalAttraction(event) {
  event.preventDefault();
  var button = $(this);
  if (button.hasClass('disabled')) {
    return;
  }
  var ready = false;
  var reload_page = false;
  var update_cats = false;
  var event_id = $(this).attr('event_id');
  console.log("Submit a new local attraction to the database for event = "+event_id+"");
  attraction_data = new Object();
  attraction_data['event_id'] = $(this).attr('event_id');
  attraction_data['attraction_id'] = $(this).attr('attraction_id') || ''; // set when editing
  attraction_data['category'] = document.getElementById("new_local_attraction_category").value;
  attraction_data['name'] = $("#info_text_field_attraction_name").val();
  attraction_data['email'] = $("#info_text_field_attraction_email").val();
  attraction_data['web'] = $("#info_text_field_attraction_website").val();
  attraction_data['phone'] = $("#info_text_field_attraction_phone").val();
  attraction_data['street'] = $("#info_text_field_attraction_street").val();
  attraction_data['city'] = $("#info_text_field_attraction_city").val();
  attraction_data['zip'] = $("#info_text_field_attraction_zip").val();
  attraction_data['state'] = document.getElementById("info_select_field_attraction_state").value;
  attraction_data['description'] = document.getElementById("info_text_field_attraction_description").value;
  attraction_data['parking'] = document.getElementById("info_text_field_attraction_parking").value;
  attraction_data['additional'] = document.getElementById("info_text_field_attraction_additional").value;
  attraction_data['distance'] = $("#info_text_field_attraction_distance").val();
  // A link typed for a hotel isn't saved if the category was then changed to something else
  attraction_data['reservation_link'] = $("#info_text_field_attraction_reservation_link_container").is(':visible')
      ? $.trim($("#info_text_field_attraction_reservation_link").val() || '') : '';

  // The reservation link becomes a button on the event's page, so it has to be a web address
  var link_message = $("#info_text_field_attraction_reservation_link_message");
  if (attraction_data['reservation_link'] !== '' && !/^https?:\/\/\S+$/i.test(attraction_data['reservation_link'])) {
    link_message.text('The hotel reservation link must be a web address starting with http:// or https://').show();
    return;
  }
  link_message.hide();
  if (attraction_data['category'] == '' || attraction_data['category'] == null) {
    ShowLocalAttractionFormMessage('Select the category.');
    return;
  }
  if ($.trim(attraction_data['name']) == '') {
    ShowLocalAttractionFormMessage('Enter the attraction\'s name.');
    return;
  }
  $("#local_attraction_form_message").hide();
  var button_text = button.text();
  button.addClass('disabled').text('Saving...');


  if (attraction_data['category'] == 'new') {
    console.log('NEW');
    category_data = new Object();
    category_data['event_id'] = attraction_data['event_id'];

    console.log($("#new_category_name_container").val());
    category_data['cat_title'] = $("#new_category_name_container").val();
    var cat_name = category_data['cat_title'].replace(/\s/g, "");
    category_data['cat_name'] = cat_name.toLowerCase();

    await EnterNewLocalAttractionCategory(category_data)
    .then((data) => {
      console.log(data);
      attraction_result = eval(data);
      if(attraction_result[0] == "success") {
        console.log("New Attractino Category entered with ID = "+attraction_result[1]);
        var new_attration_id = attraction_result[1];
        attraction_data['category'] = category_data['cat_name'] = cat_name.toLowerCase();
        ready = true;
        update_cats = true;

      } else {
        ready = false;
        console.log('Something failed in entering the new category.');
        ShowLocalAttractionFormMessage(attraction_result[1]);
      }
    })
    .catch((error) => {
      console.log(error)
      ready = false;
      ShowLocalAttractionFormMessage();
    })
    
    if (ready) {
      console.log("New Category entered, now enter the attraction information");
      await EnterNewLocalAttractionDetails(attraction_data)
      .then((data) => {
        console.log(data);
        attraction_result = eval(data);
        if(attraction_result[0] == "success") {
          console.log("New Attractino entered with ID = "+attraction_result[1]);
          var new_attration_id = attraction_result[1];
          ready = true;
          reload_page=true;
        } else {
          ready = false;
          console.log('Something failed in entering the new attraction.');
          ShowLocalAttractionFormMessage(attraction_result[1]);
        }
      })
      .catch((error) => {
        console.log(error)
        ready = false;
        ShowLocalAttractionFormMessage();
      })      
    } else {
      console.log("The category creation has failed, no reason to enter the new attraction.")
    }
  } else {
    console.log("Existing Category, now enter the attraction information");
    await EnterNewLocalAttractionDetails(attraction_data)
    .then((data) => {
      console.log(data);
      attraction_result = eval(data);
      if(attraction_result[0] == "success") {
        console.log("New Attraction entered with ID = "+attraction_result[1]);
        var new_attration_id = attraction_result[1];
        ready = true;
        reload_page=true;
      } else {
        ready = false;
        console.log('Something failed in entering the new attraction.');
        ShowLocalAttractionFormMessage(attraction_result[1]);
      }
    })
    .catch((error) => {
      console.log(error)
      ready = false;
      ShowLocalAttractionFormMessage();
    }) 
  }
  button.removeClass('disabled').text(button_text);

	if (reload_page) {
    console.log("Reload the page with category = "+attraction_data['category']+" active");
    if(update_cats) {
        var ul = document.getElementById("local_attractions_category_menu");
        var li = document.createElement('li');
        li.setAttribute('id', 'local_attraction_link_'+attraction_data['category']+'');
        li.setAttribute('class', 'local_attraction_link');
        li.setAttribute('event_id', attraction_data['event_id']);
        li.setAttribute('editable', 'true');
        li.setAttribute('section_name', attraction_data['category']);
        li.textContent = category_data['cat_title'];
        // li.appendChild(document.createTextNode(attraction_data['category']));
        ul.appendChild(li); 
        
        var elms = document.querySelectorAll('[id^="local_attraction_link_"]');
        for (i = 0; i < elms.length; i++) {
            elms[i].onclick = ReactToMenuLinkSelection;
        }  
             
    }


    $('[id^="local_attraction_link_"]').removeClass('active');
    $("#local_attraction_link_"+attraction_data['category']+"").addClass('active');
    LoadLocalAttractionSection(event_id, attraction_data['category'], 'true');
  }	


}

// -------------------------------------------------------------------------------------
// Function: 	EnterNewLocalAttractionCategory
// Description: This function enters a new category into the system.
// -------------------------------------------------------------------------------------
function EnterNewLocalAttractionCategory(category_data) {
	return new Promise((resolve, reject) => {
		$.ajax({
			url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
			type: 'POST',
			data: {method: 'EnterNewAttractionCategory', event_id:category_data['event_id'], title:category_data['cat_title'], name:category_data['cat_name']},
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

// -------------------------------------------------------------------------------------
// Function: 	EnterNewLocalAttractionDetails
// Description: This function enters a new category into the system.
// -------------------------------------------------------------------------------------
function EnterNewLocalAttractionDetails(attraction_data) {

  var data_string = JSON.stringify(attraction_data);
  console.log(data_string);

	return new Promise((resolve, reject) => {
		$.ajax({
			url: RENDER_SITE_BASE + 'LOCALAttractionFunctions.php',
			type: 'POST',
			data: {method: attraction_data['attraction_id'] ? 'SaveLocalAttraction' : 'EnterNewAttractionDetails',
			       event_id:attraction_data['event_id'], attraction_data:JSON.stringify(attraction_data)},
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

// -------------------------------------------------------------------------------------
// Function: 	ReactToNewAttractionCartegorySection
// Description: This function toggels the new category name input when needed.
// -------------------------------------------------------------------------------------
function ReactToNewAttractionCartegorySection(event) {
  var category = $(this).val();
  console.log("User selected the category "+category+" for the new local attraction");
  if (category == 'new') {
    $("#new_category_name_container").addClass('active');
  } else {
    $("#new_category_name_container").removeClass('active');
  }
  ShowAttractionReservationLink();
}

// -------------------------------------------------------------------------------------
// Function: 	ShowAttractionReservationLink
// Description: Shows the Hotel Reservation Link box only when the chosen category (or
//              the name typed for a new one) is a hotel category, i.e. has "hotel" in it.
// -------------------------------------------------------------------------------------
function ShowAttractionReservationLink() {
  var select = $("#new_local_attraction_category");
  var category = (select.val() == 'new') ? $("#new_category_name_container").val()
                                          : (select.val() || '') + ' ' + select.find('option:selected').text();
  $("#info_text_field_attraction_reservation_link_container").toggle(/hotel/i.test(category));
}

// -------------------------------------------------------------------------------------
// Function: 	ToggleReadMoreLink
// Description: This function shows toggles the read more links on local attractions.
// -------------------------------------------------------------------------------------
function ToggleReadMoreLink(event) {
    var attraction_id = $(this).attr('attraction_id');
    var action = $(this).attr('action');

    // console.log("Toggle the read more link on ID = "+attraction_id+" with action = "+action+"");
    // if (action == 'more') {
    //     console.log('HERE');
    //     $('#read_more_container_'+attraction_id+'').addClass('active');
    //     $(this).siblings(".more-text").contents().unwrap();
    //     $(this).remove();
    //     // $('#read_more_link_'+attraction_id+'').attr('action', 'less');
    //     // $('#read_more_link_'+attraction_id+'').html('show less');
    //     // $(this).append(' <a href="#" id="read_more_link_'+attraction_id+'" class="read-more" action="less" attraction_id="'+attraction_id+'">show less...</a>');
        
    // } else {
    //     $('#read_more_link_'+attraction_id+'').attr('action', 'more');
    //     $('#read_more_link_'+attraction_id+'').html('show more');
    // }
    
    // $('#attract_description_'+attraction_id+'').append('<a href="#" id="read_more_link_'+attraction_id+'" class="read-more" action="less" attraction_id="'+attraction_id+'">show less...</a>');
}

// -------------------------------------------------------------------------------------
// Function: 	ShowParticipantExtraInformation
// Description: This function shows and hides the extra participant information.
// -------------------------------------------------------------------------------------
function ShowParticipantExtraInformation (event) {
	event.preventDefault();
	var participant_id = $(this).attr('participant_id');
	var action = $(this).attr('action');

	console.log(action + ' the information for participant number ' + participant_id);
	if (action == 'show') {
		$("#part_extra_info_"+participant_id+"").addClass('active');
		$("#more_participant_info_link_"+participant_id+"").addClass('active');
		$("#more_participant_info_link_"+participant_id+"").html('show less');
		$(this).attr('action', 'hide');
	} else {
		$("#part_extra_info_"+participant_id+"").removeClass('active');
		$("#more_participant_info_link_"+participant_id+"").removeClass('active');
		$("#more_participant_info_link_"+participant_id+"").html('show more');
		$(this).attr('action', 'show');		
	}
}

// -------------------------------------------------------------------------------------
// Function: 	RenderNewContainerContent
// Description: This function renders the agenda page.
// -------------------------------------------------------------------------------------
function RenderNewContainerContent() {
	
	$.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'GetSectionText', section_name:'edit_news_container_textarea'},
	  success: function(data) {
		results = eval(data);
		if (results[0] == '1') {
			$("#news").removeClass('active');
		} else {
			$("#news").addClass('active');
			$("#news_text_container").html(results[1]);
		}
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	RenderAgendaPAge
// Description: This function renders the agenda page.
// -------------------------------------------------------------------------------------
function RenderAgendaPage(event) {
	// event.preventDefault();
    var event_id = $(this).attr('event_id');
    console.log('Event ID passed in = ', event_id);

	$.ajax({
	  url: 'GENERALFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderAgendaInformation', event_id:event_id},
	  success: function(data) {
		results = eval(data);
        console.log(results)
        $("#flipable_link").html('Home');
        $("#flipable_link").attr('href', 'index.php');
        $("#navlink_flipable_link").html('Home');
        $("#navlink_flipable_link").attr('href', 'index.php');                
		$("#news").removeClass('active');
		$("#welcome").removeClass('active');
		$("#sponsors").removeClass('active');
		$("#join_nsxca").removeClass('active');
        $("#register_button_container").removeClass('active');
		$("#acuratypes").removeClass('active');
		$("#banner").removeClass('active');
        $("#details").removeClass('opener');
		$("#details_info").html(results[0]);
		
		var elms = document.querySelectorAll('tr[id^="event_info_row"]');
		for (i = 0; i < elms.length; i++) {
			elms[i].onmouseover = DisplayDailyEventInformation;
			elms[i].onclick = DisplayDailyEventInformation;
		}
	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	DisplayDailyEventInformation
// Description: This function renders the information about a daily event.
// -------------------------------------------------------------------------------------
function DisplayDailyEventInformation() {
	var event_name = $(this).attr('event_name');
	console.log(event_name);
	$('[id^="individual_event_child_container_"]').each(function() {
		$(this).removeClass('active');
	});		
	$(document.getElementById('individual_event_child_container_'+event_name)).addClass('active');  // exact id - "golf" mustn't also match "golf-2"
}

// -------------------------------------------------------------------------------------
// Function: 	ShowAndHideSwapShopItem
// Description: This function shows and and hides everything needed to display a SS item
// -------------------------------------------------------------------------------------
function ShowAndHideSwapShopItem(event) {
	event.preventDefault();
	var item_uuid = $(this).attr('item_uuid');
	$("#swap_shop_for_sale_container").removeClass('active');
	$("#swap_shop_wanted_to_buy_container").removeClass('active');
	$("#swap_shop_items_main_container").addClass('active');
	$("#swap_shop_individual_item_container_"+item_uuid+"").addClass('active');
}

// -------------------------------------------------------------------------------------
// Function: 	SwapShopBackToList
// Description: This function displays the SwapShop For Sale and Wanted to buy list
// -------------------------------------------------------------------------------------
function SwapShopBackToList(event) {
	event.preventDefault();
	$('[id^="swap_shop_individual_item_container_"]').each(function() {
		$(this).removeClass('active');
	});	
	$("#swap_shop_for_sale_container").addClass('active');
	$("#swap_shop_wanted_to_buy_container").addClass('active');
	$("#swap_shop_items_main_container").removeClass('active');
	

}