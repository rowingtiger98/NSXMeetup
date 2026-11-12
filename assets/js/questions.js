// -------------------------------------------------------------------------------------
// Function: 	RenderEventQuestions
// Description: This function renders the Add / Edit Registration Form Questions tool
//              for an event (QUESTIONManagement.php).
// -------------------------------------------------------------------------------------
function RenderEventQuestions(event_id) {
    console.log('Render the registration form questions for event id = ', event_id);
	$.ajax({
	    url: 'QUESTIONManagement.php',
	    type: 'POST',
	    dataType: 'json',
	    data: {method: 'RenderEventQuestions', event_id:event_id},
	    success: function(results) {
            if (results[0] == 'Pass') {
                $("#main_content_container").html(results[1]);
                RenderBackButton('backtomaineventtoolset', event_id);
                ShowEventQuestionTypeSettings($("#question_add"));
                // Delegated, so the buttons keep working after the list is redrawn
                $("#question_tool")
                    .on('click', '#question_add_button', AddEventQuestion)
                    .on('change', '.question_type_select', function() {
                        ShowEventQuestionTypeSettings($(this).closest('.regtype_item'));
                    })
                    .on('change', '.question_basket_checkbox', function() {
                        ShowEventQuestionTypeSettings($(this).closest('.regtype_item'));
                    })
                    .on('click', '.question_add_choice_button', function(event) {
                        event.preventDefault();
                        AddEventQuestionChoice($(this).closest('.regtype_item'), '', '', 0).find('input').focus();
                    })
                    .on('click', '.question_remove_choice_button', function(event) {
                        event.preventDefault();
                        $(this).closest('.question_choice_row').remove();
                    })
                    .on('change', '.question_active_checkbox', SetEventQuestionActive)
                    .on('click', '.question_move_button', MoveEventQuestion)
                    .on('click', '.question_edit_button', OpenEventQuestionEdit)
                    .on('click', '.question_cancel_button', CloseEventQuestionEdit)
                    .on('click', '#question_edit_modal', function(event) {
                        if (event.target === this) { // a click on the dimmed background
                            CloseEventQuestionEdit(event);
                        }
                    })
                    .on('click', '.question_save_button', SaveEventQuestion)
                    .on('click', '.question_delete_button', DeleteEventQuestion);
            } else {
                $("#main_content_container").html('<p class="event_settings_error">' + EscapeRegTypeText([].concat(results[1]).join(' ')) + '</p>');
            }
	    },
	    error: function() {
            $("#main_content_container").html('<p class="event_settings_error">The registration form questions could not be loaded. Please try again.</p>');
	    }
	  });
}

// Shows the yes/no or drop-down settings for the form's chosen type, the charge
// settings (worded for the type) once a type is chosen, and the amount only when the
// question is charged. A new drop-down starts with two empty choices.
function ShowEventQuestionTypeSettings(item) {
    var type = item.find('.question_type_select').val();
    item.find('.question_type_settings, .question_type_text').each(function() {
        $(this).toggle($(this).attr('question_type') === type);
    });
    item.find('.question_charge_settings').toggle(type === 'BOOLEAN' || type === 'SELECT');
    item.find('.question_amount_field').toggle(item.find('.question_basket_checkbox').prop('checked'));
    if (type === 'SELECT' && item.find('.question_choice_row').length === 0) {
        AddEventQuestionChoice(item, '', '', 0);
        AddEventQuestionChoice(item, '', '', 0);
    }
}

// Adds a choice row to a form's drop-down choices. key is blank for a new choice; a
// choice registrants have picked shows how many and can't be removed.
function AddEventQuestionChoice(item, key, text, picked) {
    var row = $('<div class="question_choice_row"></div>').attr('choice_key', key);
    row.append($('<input type="text" class="question_choice_text" placeholder="Choice">').attr('maxlength', 100).val(text));
    if (picked > 0) {
        row.append($('<span class="question_choice_picked"></span>').text(picked + ' chose this'));
    } else {
        row.append('<a href="#" class="button small question_remove_choice_button" title="Remove this choice">Remove</a>');
    }
    item.find('.question_choices').append(row);
    return row;
}

// -------------------------------------------------------------------------------------
// Function: 	SendEventQuestionRequest
// Description: Posts a change for one question. item holds the [question_field]
//              inputs and choices to send (or is null), and messages gets any errors.
//              on_pass is called with the server's result when it succeeds, and
//              on_done always, with true if it succeeded.
// -------------------------------------------------------------------------------------
function SendEventQuestionRequest(method, question_id, item, extra, messages, on_pass, on_done) {
    var tool = $("#question_tool");
    var passed = false;
    var data = new FormData();
    data.append('method', method);
    data.append('event_id', tool.attr('event_id'));
    data.append('csrf_token', tool.attr('csrf_token'));
    data.append('question_id', question_id || '');
    if (item) {
        item.find('[question_field]').each(function() {
            data.append($(this).attr('question_field'), (this.type === 'checkbox') ? (this.checked ? '1' : '0') : $(this).val());
        });
        item.find('.question_choice_row').each(function() {
            data.append('choice_text[]', $(this).find('.question_choice_text').val());
            data.append('choice_key[]', $(this).attr('choice_key') || '');
        });
    }
    $.each(extra || {}, function(key, value) {
        data.append(key, value);
    });

	$.ajax({
	    url: 'QUESTIONManagement.php',
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
            ShowRegistrationTypeMessages(messages, ['The request could not be completed. Please try again.'], true);
	    },
	    complete: function() {
            if (on_done) {
                on_done(passed);
            }
	    }
	  });
}

// Empties the Add form after a question is added, keeping Active ticked.
function ClearEventQuestionForm(item) {
    item.find('[question_field]').not(':checkbox').val('');
    item.find(':checkbox[question_field]').prop('checked', false);
    item.find(':checkbox[question_field="active"]').prop('checked', true);
    item.find('.question_choices').empty();
    ShowEventQuestionTypeSettings(item);
}

function AddEventQuestion(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = $("#question_add");
    SendEventQuestionRequest('AddEventQuestion', '', item, {}, item.find('.regtype_messages'), function(list_html) {
        $("#question_list").html(list_html);
        ClearEventQuestionForm(item);
        ShowRegistrationTypeMessages(item.find('.regtype_messages'), ['Question added to the end of the form.'], false);
    }, BusyRegistrationTypeButton(button, 'Adding...'));
}

// -------------------------------------------------------------------------------------
// Function: 	SetEventQuestionActive
// Description: Saves a row's Active checkbox as soon as it changes. If the save fails,
//              the checkbox goes back to how it was.
// -------------------------------------------------------------------------------------
function SetEventQuestionActive() {
    var checkbox = $(this);
    var active = this.checked;
    var row = checkbox.closest('.question_row');
    checkbox.prop('disabled', true);
    SendEventQuestionRequest('SetEventQuestionActive', row.attr('question_id'), null, {active: active ? '1' : '0'}, $("#question_list_messages"), function(message) {
        ShowRegistrationTypeMessages($("#question_list_messages"), [message], false);
    }, function(passed) {
        checkbox.prop('disabled', false);
        if (!passed) {
            checkbox.prop('checked', !active); // the error message explains why
        }
    });
}

function MoveEventQuestion(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    $("#question_list .question_move_button").addClass('disabled');
    SendEventQuestionRequest('MoveEventQuestion', button.closest('.question_row').attr('question_id'), null, {direction: button.attr('direction')}, $("#question_list_messages"), function(list_html) {
        $("#question_list").html(list_html);
        $("#question_list_messages").empty();
    }, function() {
        $("#question_list .question_move_button").removeClass('disabled');
    });
}

// -------------------------------------------------------------------------------------
// Function: 	OpenEventQuestionEdit
// Description: Fills the Edit modal with the clicked row's values and shows it.
// -------------------------------------------------------------------------------------
function OpenEventQuestionEdit(event) {
    event.preventDefault();
    var row = $(this).closest('.question_row');
    var item = $("#question_edit");
    item.attr('question_id', row.attr('question_id'));
    $.each(['instruction', 'description', 'confirmation', 'type', 'default_text', 'amount'], function(i, key) {
        $("#question_edit_" + key).val(row.attr('question_' + key) || '');
    });
    $("#question_edit_add_to_basket").prop('checked', row.attr('question_add_to_basket') === '1');

    // Answers are stored in the type's format, so an answered question keeps its type
    var locked = (row.attr('question_answered') === '1');
    $("#question_edit_type").prop('disabled', locked);
    item.find('.question_type_locked').toggle(locked);

    item.find('.question_choices').empty();
    var choices = [];
    try {
        choices = JSON.parse(row.attr('question_choices') || '[]');
    } catch (e) {
        choices = [];
    }
    $.each(choices, function(i, choice) {
        AddEventQuestionChoice(item, choice.key, choice.text, choice.picked);
    });
    ShowEventQuestionTypeSettings(item);

    item.find('.regtype_messages').empty();
    $("#question_edit_modal").show();
    $("#question_edit_description").focus();
    $(document).on('keydown.question_modal', function(e) {
        if (e.key === 'Escape') {
            CloseEventQuestionEdit(e);
        }
    });
}

function CloseEventQuestionEdit(event) {
    if (event) {
        event.preventDefault();
    }
    $("#question_edit_modal").hide();
    $(document).off('keydown.question_modal');
}

function SaveEventQuestion(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var item = button.closest('.regtype_item');
    SendEventQuestionRequest('SaveEventQuestion', item.attr('question_id'), item, {}, item.find('.regtype_messages'), function(list_html) {
        CloseEventQuestionEdit();
        $("#question_list").html(list_html);
        ShowRegistrationTypeMessages($("#question_list_messages"), ['Question saved.'], false);
    }, BusyRegistrationTypeButton(button, 'Saving...'));
}

// -------------------------------------------------------------------------------------
// Function: 	DeleteEventQuestion
// Description: The first click asks for confirmation on the button itself; a second
//              click within a few seconds deletes the question.
// -------------------------------------------------------------------------------------
function DeleteEventQuestion(event) {
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
    var row = button.closest('.question_row');
    button.removeClass('regtype_confirm_delete').text('Delete'); // so a failed delete asks again
    SendEventQuestionRequest('DeleteEventQuestion', row.attr('question_id'), null, {}, $("#question_list_messages"), function(list_html) {
        $("#question_list").html(list_html);
        ShowRegistrationTypeMessages($("#question_list_messages"), ['Question deleted.'], false);
    }, BusyRegistrationTypeButton(button, 'Deleting...'));
}
