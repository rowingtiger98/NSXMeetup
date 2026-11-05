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
    console.log("Render the locals attractions for event id = " + event_id + ' with editable = ' + editable);
	$.ajax({
	  url: 'LOCALAttractionFunctions.php',
	  type: 'POST',
	  data: {method: 'RenderOpeningPageInformation', event_id:event_id, editable:editable},
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
      console.log("Handle Menu Link Selection to create a new local attraction");
      $.ajax({
        url: 'LOCALAttractionFunctions.php',
        type: 'POST',
        data: {method: 'CreateNewLocalAttraction', event_id:event_id},
        success: function(data) {
          console.log(data);
          results = eval(data);
          html_code = results[0];
          $("#local_attractions_content").html(html_code);
          // new_local_attraction_category
          document.querySelector('[id="new_local_attraction_category"]').onchange = ReactToNewAttractionCartegorySection;
          document.querySelector('[id="submit_new_local_attraction_button"]').onclick = SubmitNewLocalAttraction;     
        }
      });      

    } else {
      console.log("Handle Menu Link Selection for section = " + section_name);
      $('[id^="local_attraction_"]').each(function () {
          $(this).removeClass('active');
      });

      $("#local_attraction_link_"+section_name+"").addClass('active');

      $.ajax({
        url: 'LOCALAttractionFunctions.php',
        type: 'POST',
        data: {method: 'BuildLocalAttractionSection', event_id:event_id, section_name:section_name},
        success: function(data) {
          results = eval(data);
          html_code = results[0];
          $("#local_attractions_content").html(html_code);

          var elms = document.querySelectorAll('[id^="show_more_attraction_information_"]');
          for (i = 0; i < elms.length; i++) {
            elms[i].onclick = ReactToShowMoreLocalInformationClick;
          }            	
        }
      });
    }    
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
  var ready = false;
  var reload_page = false;
  var update_cats = false;
  var event_id = $(this).attr('event_id');
  console.log("Submit a new local attraction to the database for event = "+event_id+"");
  attraction_data = new Object();
  attraction_data['event_id'] = $(this).attr('event_id');
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
      }
    })
    .catch((error) => {
      console.log(error)
      ready = false;
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
          console.log('Something failed in entering the new category.');
        }
      })
      .catch((error) => {
        console.log(error)
        ready = false;
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
        console.log('Something failed in entering the new category.');
      }
    })
    .catch((error) => {
      console.log(error)
      ready = false;
    }) 
  }

	if (reload_page) {
    console.log("Reload the page with category = "+attraction_data['category']+" active");
    if(update_cats) {
        var ul = document.getElementById("local_attractions_category_menu");
        var li = document.createElement('li');
        li.setAttribute('id', 'local_attraction_link_'+attraction_data['category']+'');
        li.setAttribute('class', 'local_attraction_link');
        li.setAttribute('event_id', attraction_data['event_id']);
        li.setAttribute('section_name', attraction_data['category']);
        li.innerHTML = attraction_data['category'];
        // li.appendChild(document.createTextNode(attraction_data['category']));
        ul.appendChild(li); 
        
        var elms = document.querySelectorAll('[id^="local_attraction_link_"]');
        for (i = 0; i < elms.length; i++) {
            elms[i].onclick = ReactToMenuLinkSelection;
        }  
             
    }


    $('[id^="local_attraction_"]').each(function () {
        $(this).removeClass('active');
    });

    $("#local_attraction_link_"+attraction_data['category']+"").addClass('active');

    $.ajax({
      url: 'LOCALAttractionFunctions.php',
      type: 'POST',
      data: {method: 'BuildLocalAttractionSection', event_id:event_id, section_name:attraction_data['category']},
      success: function(data) {
        results = eval(data);
        html_code = results[0];
        $("#local_attractions_content").html(html_code);

        var elms = document.querySelectorAll('[id^="show_more_attraction_information_"]');
        for (i = 0; i < elms.length; i++) {
          elms[i].onclick = ReactToShowMoreLocalInformationClick;
        }  
        
      }
    });    
  }	


}

// -------------------------------------------------------------------------------------
// Function: 	EnterNewLocalAttractionCategory
// Description: This function enters a new category into the system.
// -------------------------------------------------------------------------------------
function EnterNewLocalAttractionCategory(category_data) {
	return new Promise((resolve, reject) => {
		$.ajax({
			url: 'LOCALAttractionFunctions.php',
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
			url: 'LOCALAttractionFunctions.php',
			type: 'POST',
			data: {method: 'EnterNewAttractionDetails', attraction_data:JSON.stringify(attraction_data)},
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
	$('[id^="individual_event_child_container_'+event_name+'"]').addClass('active');
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