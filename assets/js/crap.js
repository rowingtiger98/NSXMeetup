// -------------------------------------------------------------------------------------
// Function: 	NEWRenderAgendaPage
// Description: This function renders the agenda page.
// -------------------------------------------------------------------------------------
function NEWRenderAgendaPage(event) {
	// event.preventDefault();
    var event_id = $(this).attr('event_id');
    console.log('Event ID passed in = ', event_id);

	$.ajax({
	  url: '/public_html/nsxmeetup/CRAPFunctions.php',
	  type: 'POST',
	  data: {method: 'BigCrap', event_id:event_id},
	  success: function(data) {
        console.log(data);

	  }
	});	
}