// -------------------------------------------------------------------------------------
// Function: 	RenderBackButton
// Description: This function filters the participants list.
// -------------------------------------------------------------------------------------
function RenderBackButton (action, event_id) {
    console.log('Render the action ', action);
	$.ajax({
	  url: 'ACCOUNTManagement.php',
	  type: 'POST',
	  data: {method: 'RenderBackButton', action:action, event_id:event_id},
	  success: function(data) {
		results = eval(data);
		$("#back_navigation_button").html(results[0]);
        document.querySelector('[id="back_button_link"]').onclick = HandleBackButtonClick;

	  }
	});	
}

// -------------------------------------------------------------------------------------
// Function: 	HandleBackButtonClick
// Description: This function handles clicking the back button on the management interface.
// -------------------------------------------------------------------------------------
function HandleBackButtonClick() {
    var action = $(this).attr('action');
    console.log('(HandleBackButtonClick) - Handle the',action,'action being clicked');

    if (action === 'findandeditparticipant') {
        var event_id = $(this).attr('event_id');
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id);
        RenderFindEditRegistration(event_id);
    }
    else if (action === 'backtomaineventtoolset') {
        var event_id = $(this).attr('event_id');
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id);
        RenderSelectedEventToManage(event_id);
    }
    else if (action === 'backtofindeditregistration') {
        var event_id = $(this).attr('event_id');
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id);
        RenderFindEditRegistration(event_id);        
    }
    else if (action === 'backtoeditsponsorshiplevels') {
        var event_id = $(this).attr('event_id');
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id);
        RenderSponsorshipManagementInterface(event_id);        
    }
    else if (action === 'backtoeditsponsors') {
        var event_id = $(this).attr('event_id');
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id);
        RenderSponsorManagementInterface(event_id);
    }
    else if (action === 'backtoparticipantsinformation') {
        var event_id = $(this).attr('event_id');
        var participant_id = $(this).attr('participant_id');
        var collect_payments = $(this).attr('collect_payment')
        console.log('Going to handle the back button click for', action, 'for EVENT ID = ', event_id, 'and Partifcipant ID = ', participant_id);
        RenderParticipantInformation(event_id, participant_id, collect_payments);
        ;        
    }    
    
}