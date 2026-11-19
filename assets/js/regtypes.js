// -------------------------------------------------------------------------------------
// Function: 	RenderRegistrationTypes
// Description: This function renders the Add / Edit / Delete Registration Types tool
//              for an event.
// -------------------------------------------------------------------------------------
function RenderRegistrationTypes(event_id) {
    console.log('Render the registration types for event id = ', event_id);
	$.ajax({
	    url: 'REGTYPEManagement.php',
	    type: 'POST',
	    dataType: 'json',
	    data: {method: 'RenderRegistrationTypes', event_id:event_id},
	    success: function(results) {
            if (results[0] == 'Pass') {
                $("#main_content_container").html(results[1]);
                RenderBackButton('backtomaineventtoolset', event_id);
                // Delegated, so the buttons keep working after the list is redrawn
                $("#regtype_tool")
                    .on('click', '#regtype_add_button', AddRegistrationType)
                    .on('change', '.regtype_enabled_checkbox', SetRegistrationTypeEnabled)
                    .on('click', '.regtype_edit_button', OpenRegistrationTypeEdit)
                    .on('click', '.regtype_cancel_button', CloseRegistrationTypeEdit)
                    .on('click', '#regtype_edit_modal', function(event) {
                        if (event.target === this) { // a click on the dimmed background
                            CloseRegistrationTypeEdit(event);
                        }
                    })
                    .on('input', '[regtype_field="cost"]', FilterRegistrationTypeCost)
                    .on('click', '.regtype_save_button', SaveRegistrationType)
                    .on('click', '.regtype_delete_button', DeleteRegistrationType);
            } else {
                $("#main_content_container").html('<p class="event_settings_error">' + EscapeRegTypeText([].concat(results[1]).join(' ')) + '</p>');
            }
	    },
	    error: function() {
            $("#main_content_container").html('<p class="event_settings_error">The registration types could not be loaded. Please try again.</p>');
	    }
	  });
}

// -------------------------------------------------------------------------------------
// Function: 	SendRegistrationTypeRequest
// Description: This function posts a change for one registration type. fields is
//              added to the request, and the element that holds them (the Add form,
//              an edit row, or a table row) gets any error messages. on_pass is
//              called with the server's result when it succeeds, and on_done always,
//              with true if it succeeded.
// -------------------------------------------------------------------------------------
function SendRegistrationTypeRequest(method, regtype_id, fields, messages, on_pass, on_done) {
    var tool = $("#regtype_tool");
    var passed = false;
    var data = $.extend({
        method: method,
        event_id: tool.attr('event_id'),
        csrf_token: tool.attr('csrf_token'),
        regtype_id: regtype_id || ''
    }, fields);

	$.ajax({
	    url: 'REGTYPEManagement.php',
	    type: 'POST',
	    dataType: 'json',
	    data: data,
	    success: function(results) {
            if (results[0] == 'Pass') {
                passed = true;
                on_pass(results[1]);
            } else {
                ShowRegistrationTypeMessages(messages, [].concat(results[1]), true);
            }
	    },
	    error: function(xhr, status, error) {
            console.log('(' + method + ') request failed:', xhr.status, status, error, xhr.responseText);
            ShowRegistrationTypeMessages(messages, ['The request could not be completed. Please try again.'], true);
	    },
	    complete: function() {
            if (on_done) {
                on_done(passed);
            }
	    }
	  });
}

// Collects the [regtype_field] inputs inside item; checkboxes send '1' or '0'.
function RegistrationTypeFields(item) {
    var fields = {};
    item.find('[regtype_field]').each(function() {
        fields[$(this).attr('regtype_field')] = (this.type === 'checkbox') ? (this.checked ? '1' : '0') : $(this).val();
    });
    return fields;
}

// Disables button and shows working_text until the returned function is called.
function BusyRegistrationTypeButton(button, working_text) {
    var button_text = button.text();
    button.addClass('disabled').text(working_text);
    return function() {
        button.removeClass('disabled').text(button_text);
    };
}

// Keeps the Cost inputs to a positive amount: digits and one decimal point, no minus sign.
function FilterRegistrationTypeCost() {
    var cleaned = this.value.replace(/[^0-9.]/g, '');
    var point = cleaned.indexOf('.');
    if (point !== -1) {
        cleaned = cleaned.substring(0, point + 1) + cleaned.substring(point + 1).replace(/\./g, '').substring(0, 2);
    }
    if (cleaned !== this.value) {
        this.value = cleaned;
    }
}

function AddRegistrationType(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = $("#regtype_add");
    SendRegistrationTypeRequest('AddRegistrationType', '', RegistrationTypeFields(item), item.find('.regtype_messages'), function(list_html) {
        $("#regtype_list").html(list_html);
        item.find('[regtype_field]').not(':checkbox').val('');
        item.find(':checkbox[regtype_field]').prop('checked', true);
        ShowRegistrationTypeMessages(item.find('.regtype_messages'), ['Registration type added.'], false);
    }, BusyRegistrationTypeButton(button, 'Adding...'));
}

// -------------------------------------------------------------------------------------
// Function: 	SetRegistrationTypeEnabled
// Description: Saves a row's Enabled checkbox as soon as it changes. If the save
//              fails, the checkbox goes back to how it was.
// -------------------------------------------------------------------------------------
function SetRegistrationTypeEnabled() {
    var checkbox = $(this);
    var enabled = this.checked;
    var row = checkbox.closest('.regtype_row');
    checkbox.prop('disabled', true);
    SendRegistrationTypeRequest('SetRegistrationTypeEnabled', row.attr('regtype_id'), {enabled: enabled ? '1' : '0'}, $("#regtype_list_messages"), function(message) {
        ShowRegistrationTypeMessages($("#regtype_list_messages"), [message], false);
    }, function(passed) {
        checkbox.prop('disabled', false);
        if (!passed) {
            checkbox.prop('checked', !enabled); // the error message explains why
        }
    });
}

// -------------------------------------------------------------------------------------
// Function: 	OpenRegistrationTypeEdit
// Description: Fills the Edit modal with the clicked row's values and shows it.
// -------------------------------------------------------------------------------------
function OpenRegistrationTypeEdit(event) {
    event.preventDefault();
    var row = $(this).closest('.regtype_row');
    var item = $("#regtype_edit");
    item.attr('regtype_id', row.attr('regtype_id'));
    $("#regtype_edit_name").val(row.attr('regtype_name'));
    $("#regtype_edit_cost").val(row.attr('regtype_cost'));
    $("#regtype_edit_description").val(row.attr('regtype_description'));
    item.find('.regtype_messages').empty();
    $("#regtype_edit_modal").show();
    $("#regtype_edit_name").focus();
    $(document).on('keydown.regtype_modal', function(e) {
        if (e.key === 'Escape') {
            CloseRegistrationTypeEdit(e);
        }
    });
}

function CloseRegistrationTypeEdit(event) {
    if (event) {
        event.preventDefault();
    }
    $("#regtype_edit_modal").hide();
    $(document).off('keydown.regtype_modal');
}

function SaveRegistrationType(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = button.closest('.regtype_item');
    SendRegistrationTypeRequest('SaveRegistrationType', item.attr('regtype_id'), RegistrationTypeFields(item), item.find('.regtype_messages'), function(list_html) {
        CloseRegistrationTypeEdit();
        $("#regtype_list").html(list_html);
        ShowRegistrationTypeMessages($("#regtype_list_messages"), ['Registration type saved.'], false);
    }, BusyRegistrationTypeButton(button, 'Saving...'));
}

// -------------------------------------------------------------------------------------
// Function: 	DeleteRegistrationType
// Description: The first click asks for confirmation on the button itself; a second
//              click within a few seconds deletes the registration type.
// -------------------------------------------------------------------------------------
function DeleteRegistrationType(event) {
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
    var row = button.closest('.regtype_row');
    button.removeClass('regtype_confirm_delete').text('Delete'); // so a failed delete asks again
    SendRegistrationTypeRequest('DeleteRegistrationType', row.attr('regtype_id'), {}, $("#regtype_list_messages"), function(list_html) {
        $("#regtype_list").html(list_html);
        ShowRegistrationTypeMessages($("#regtype_list_messages"), ['Registration type deleted.'], false);
    }, BusyRegistrationTypeButton(button, 'Deleting...'));
}

function ShowRegistrationTypeMessages(container, messages, is_error) {
    var html = '<ul class="' + (is_error ? 'event_settings_error' : 'event_settings_success') + '">';
    for (var i = 0; i < messages.length; i++) {
        html += '<li>' + EscapeRegTypeText(messages[i]) + '</li>';
    }
    html += '</ul>';
    container.first().html(html);
}

function EscapeRegTypeText(text) {
    return $('<div>').text(text).html();
}
