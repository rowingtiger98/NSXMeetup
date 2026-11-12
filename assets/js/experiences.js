// -------------------------------------------------------------------------------------
// Function: 	RenderWeekendEvents
// Description: This function renders the Add / Edit Agenda Events tool for an event
//              (EXPERIENCEManagement.php).
// -------------------------------------------------------------------------------------
function RenderWeekendEvents(event_id) {
    console.log('Render the agenda events for event id = ', event_id);
	$.ajax({
	    url: 'EXPERIENCEManagement.php',
	    type: 'POST',
	    dataType: 'json',
	    data: {method: 'RenderWeekendEvents', event_id:event_id},
	    success: function(results) {
            if (results[0] == 'Pass') {
                $("#main_content_container").html(results[1]);
                RenderBackButton('backtomaineventtoolset', event_id);
                // Delegated, so the buttons keep working after the list is redrawn
                $("#experience_tool")
                    .on('click', '#experience_add_button', AddWeekendEvent)
                    .on('change', '.experience_display_checkbox', SetWeekendEventDisplay)
                    .on('click', '.experience_edit_button', OpenWeekendEventEdit)
                    .on('click', '.experience_cancel_button', CloseWeekendEventEdit)
                    .on('click', '#experience_edit_modal', function(event) {
                        if (event.target === this) { // a click on the dimmed background
                            CloseWeekendEventEdit(event);
                        }
                    })
                    .on('change', '[experience_file="banner"]', PreviewWeekendEventBanner)
                    .on('click', '.experience_save_button', SaveWeekendEvent)
                    .on('click', '.experience_delete_button', DeleteWeekendEvent);
            } else {
                $("#main_content_container").html('<p class="event_settings_error">' + EscapeRegTypeText([].concat(results[1]).join(' ')) + '</p>');
            }
	    },
	    error: function() {
            $("#main_content_container").html('<p class="event_settings_error">The agenda events could not be loaded. Please try again.</p>');
	    }
	  });
}

// -------------------------------------------------------------------------------------
// Function: 	SendWeekendEventRequest
// Description: Posts a change for one agenda event. item holds the [experience_field]
//              inputs and banner file to send (or is null), and messages gets any
//              errors. on_pass is called with the server's result when it succeeds,
//              and on_done always, with true if it succeeded.
// -------------------------------------------------------------------------------------
function SendWeekendEventRequest(method, experience_id, item, extra, messages, on_pass, on_done) {
    var tool = $("#experience_tool");
    var passed = false;
    var data = new FormData();
    data.append('method', method);
    data.append('event_id', tool.attr('event_id'));
    data.append('csrf_token', tool.attr('csrf_token'));
    data.append('experience_id', experience_id || '');
    if (item) {
        item.find('[experience_field]').each(function() {
            data.append($(this).attr('experience_field'), (this.type === 'checkbox') ? (this.checked ? '1' : '0') : $(this).val());
        });
        var banner = item.find('[experience_file="banner"]')[0];
        if (banner && banner.files[0]) {
            data.append('banner', banner.files[0]);
        }
    }
    $.each(extra || {}, function(key, value) {
        data.append(key, value);
    });

	$.ajax({
	    url: 'EXPERIENCEManagement.php',
	    type: 'POST',
	    dataType: 'json',
	    data: data,
	    processData: false,
	    contentType: false,
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
            var reason = (xhr.status === 413) ? ' The banner image is too large for the server to accept.' : '';
            ShowRegistrationTypeMessages(messages, ['The request could not be completed.' + reason + ' Please try again.'], true);
	    },
	    complete: function() {
            if (on_done) {
                on_done(passed);
            }
	    }
	  });
}

// Shows the banner chosen in a form before it's saved, or refuses one that's too large.
function PreviewWeekendEventBanner() {
    var item = $(this).closest('.regtype_item');
    var preview = item.find('.experience_banner_preview');
    var file = this.files[0];
    if (!file) {
        preview.empty().append(item.data('current_banner') || '');
        return;
    }
    if (file.size > FRONTPAGE_IMAGE_MAX_BYTES) {
        this.value = '';
        ShowRegistrationTypeMessages(item.find('.regtype_messages'), ['The banner image is too large. Please choose one under 10 MB.'], true);
        return;
    }
    preview.empty().append($('<img>').attr({src: URL.createObjectURL(file), alt: 'Banner preview'}));
}

// Empties the Add form after an agenda event is added, keeping Show ticked.
function ClearWeekendEventForm(item) {
    item.find('[experience_field]').not(':checkbox').val('');
    item.find(':checkbox[experience_field]').prop('checked', true);
    item.find('[experience_file]').val('');
    item.find('.experience_banner_preview').empty();
}

function AddWeekendEvent(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = $("#experience_add");
    SendWeekendEventRequest('AddWeekendEvent', '', item, {}, item.find('.regtype_messages'), function(list_html) {
        $("#experience_list").html(list_html);
        ClearWeekendEventForm(item);
        ShowRegistrationTypeMessages(item.find('.regtype_messages'), ['Agenda event added.'], false);
    }, BusyRegistrationTypeButton(button, 'Adding...'));
}

// -------------------------------------------------------------------------------------
// Function: 	SetWeekendEventDisplay
// Description: Saves a row's Show checkbox as soon as it changes. If the save fails,
//              the checkbox goes back to how it was.
// -------------------------------------------------------------------------------------
function SetWeekendEventDisplay() {
    var checkbox = $(this);
    var display = this.checked;
    var row = checkbox.closest('.experience_row');
    checkbox.prop('disabled', true);
    SendWeekendEventRequest('SetWeekendEventDisplay', row.attr('experience_id'), null, {display: display ? '1' : '0'}, $("#experience_list_messages"), function(message) {
        ShowRegistrationTypeMessages($("#experience_list_messages"), [message], false);
    }, function(passed) {
        checkbox.prop('disabled', false);
        if (!passed) {
            checkbox.prop('checked', !display); // the error message explains why
        }
    });
}

// -------------------------------------------------------------------------------------
// Function: 	OpenWeekendEventEdit
// Description: Fills the Edit modal with the clicked row's values and shows it.
// -------------------------------------------------------------------------------------
function OpenWeekendEventEdit(event) {
    event.preventDefault();
    var row = $(this).closest('.experience_row');
    var item = $("#experience_edit");
    item.attr('experience_id', row.attr('experience_id'));
    $.each(['title', 'date', 'start_time', 'stop_time', 'description', 'location', 'weblink', 'phone', 'email'], function(i, key) {
        $("#experience_edit_" + key).val(row.attr('experience_' + key) || '');
    });
    $("#experience_edit_remove_banner").prop('checked', false);
    item.find('[experience_file]').val('');

    var banner_url = row.attr('experience_banner_url');
    var current_banner = banner_url ? $('<img>').attr({src: banner_url, alt: 'Current banner'}) : $('<p>').text('No banner image yet.');
    item.data('current_banner', current_banner);
    item.find('.experience_banner_preview').empty().append(current_banner);
    item.find('.experience_remove_banner').toggle(!!banner_url);

    item.find('.regtype_messages').empty();
    $("#experience_edit_modal").show();
    $("#experience_edit_title").focus();
    $(document).on('keydown.experience_modal', function(e) {
        if (e.key === 'Escape') {
            CloseWeekendEventEdit(e);
        }
    });
}

function CloseWeekendEventEdit(event) {
    if (event) {
        event.preventDefault();
    }
    $("#experience_edit_modal").hide();
    $(document).off('keydown.experience_modal');
}

function SaveWeekendEvent(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = button.closest('.regtype_item');
    SendWeekendEventRequest('SaveWeekendEvent', item.attr('experience_id'), item, {}, item.find('.regtype_messages'), function(list_html) {
        CloseWeekendEventEdit();
        $("#experience_list").html(list_html);
        ShowRegistrationTypeMessages($("#experience_list_messages"), ['Agenda event saved.'], false);
    }, BusyRegistrationTypeButton(button, 'Saving...'));
}

// -------------------------------------------------------------------------------------
// Function: 	DeleteWeekendEvent
// Description: The first click asks for confirmation on the button itself; a second
//              click within a few seconds deletes the agenda event.
// -------------------------------------------------------------------------------------
function DeleteWeekendEvent(event) {
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
    var row = button.closest('.experience_row');
    button.removeClass('regtype_confirm_delete').text('Delete'); // so a failed delete asks again
    SendWeekendEventRequest('DeleteWeekendEvent', row.attr('experience_id'), null, {}, $("#experience_list_messages"), function(list_html) {
        $("#experience_list").html(list_html);
        ShowRegistrationTypeMessages($("#experience_list_messages"), ['Agenda event deleted.'], false);
    }, BusyRegistrationTypeButton(button, 'Deleting...'));
}
