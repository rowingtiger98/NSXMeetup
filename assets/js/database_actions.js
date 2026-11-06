// -------------------------------------------------------------------------------------
// Function: 	SubmitChangesToDatabase
// Description: This function will submit handle the user selecting to submit changes to
//				the database.  
// -------------------------------------------------------------------------------------
function SubmitChangesToDatabase(event) {
    var table_name = $(this).attr('table');
    const update_data = new Object();

    console.log('The Submit Changes button was pressed. Insert data to the table = '+table_name);

    //Depending on the table, loop through and gather the information that needs to be updated.
    switch (table_name) {
        case 'nsx_meetup_event_info':
            console.log('Getting the values for the nsx_meetup_event_info table');
            $('[id^="database_value_field"]').each(function() {
                if($(this).attr('need_update') == 'yes') {
                    console.log('the field '+$(this).attr('key_name')+' needs to be updated in the database to '+$(this).val()+'.');
                    var key = String($(this).attr('key_name'));
                    update_data[key] = String($(this).val());
                }
                $(this).addClass("active");
            });            
            break;
    
        default:
            console.log('Default Case called.');
            break;
    }

    //Convert Items to change to a string
    var query_data = (update_data);

    // Call the PHP function to convert the query to a SQL query with variables to 
    // prevent a SQL injection attempt.
    $.ajax({
        url: 'GENERALFunctions.php',
        type: 'POST',
        data: {method: 'SubmitChangedDatabaseData', table_name:table_name, query_data:query_data},
        success: function(data) {
          console.log(data);				  
        }
      });    
}