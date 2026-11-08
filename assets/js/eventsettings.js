// -------------------------------------------------------------------------------------
// Function: 	RenderEditEventSettings
// Description: This function renders an event settings form for an event. tool picks
//              which tool card's fields are shown: 'settings' (Edit Event Settings)
//              or 'payments' (Enable / Edit Event Payment Options).
// -------------------------------------------------------------------------------------
function RenderEditEventSettings(event_id, tool) {
    console.log('Render the event', tool, 'for event id = ', event_id);
	$.ajax({
	    url: 'EVENTSettings.php',
	    type: 'POST',
	    dataType: 'json',
	    data: {method: 'RenderEditEventSettings', event_id:event_id, tool:tool},
	    success: function(results) {
            if (results[0] == 'Pass') {
                $("#main_content_container").html(results[1]);
                RenderBackButton('backtomaineventtoolset', event_id);
                document.querySelector('[id="save_event_settings_button"]').onclick = SaveEventSettings;
                if (document.querySelector('[id="event_setting_frontpage_upload"]')) { // only on the settings form
                    document.querySelector('[id="event_setting_frontpage_upload"]').onchange = UploadFrontpageImage;
                    document.querySelector('[id="event_setting_EVENT_Frontpage_Image"]').onchange = UpdateFrontpageImagePreview;
                    document.querySelector('[id="event_setting_frontpage_preview"]').onerror = HandleFrontpageImagePreviewError;
                }
                if (document.querySelector('[id="payment_methods"]')) { // only on the payment options form
                    document.querySelector('[id="event_setting_EVENT_Collect_Payments"]').onchange = TogglePaymentMethods;
                    $(".payment_method_toggle").on('change', TogglePaymentMethodDrawer);
                }
            } else {
                $("#main_content_container").html('<p class="event_settings_error">' + EscapeEventSettingsText(results[1].join(' ')) + '</p>');
            }
	    },
	    error: function() {
            $("#main_content_container").html('<p class="event_settings_error">The event settings could not be loaded. Please try again.</p>');
	    }
	  });
}

// -------------------------------------------------------------------------------------
// Function: 	SaveEventSettings
// Description: This function sends every setting on the form to be validated and saved.
// -------------------------------------------------------------------------------------
function SaveEventSettings(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }

    var form = $("#event_settings_form");
    var data = {
        method: 'SaveEventSettings',
        event_id: form.attr('event_id'),
        tool: form.attr('tool'),
        csrf_token: form.attr('csrf_token')
    };
    form.find('[db_key]').each(function() {
        var key = 'settings[' + $(this).attr('db_key') + ']';
        data[key] = (this.type === 'checkbox') ? (this.checked ? '1' : '0') : $(this).val();
    });

    var button_text = button.text();
    button.addClass('disabled').text('Saving...');
	$.ajax({
	    url: 'EVENTSettings.php',
	    type: 'POST',
	    dataType: 'json',
	    data: data,
	    success: function(results) {
            if (results[0] == 'Pass') {
                ShowEventSettingsMessages([results[1]], false);
            } else {
                ShowEventSettingsMessages(results[1], true);
            }
	    },
	    error: function() {
            ShowEventSettingsMessages(['The settings could not be saved. Please try again.'], true);
	    },
	    complete: function() {
            button.removeClass('disabled').text(button_text);
	    }
	  });
}

// -------------------------------------------------------------------------------------
// Function: 	TogglePaymentMethods
// Description: Shows the Payment Methods section only while Collect Payments is checked.
// -------------------------------------------------------------------------------------
function TogglePaymentMethods() {
    var sections = $("#payment_methods, .payment_methods_separator");
    if (this.checked) {
        sections.slideDown(200);
    } else {
        sections.slideUp(200);
    }
}

// -------------------------------------------------------------------------------------
// Function: 	TogglePaymentMethodDrawer
// Description: Opens a payment method's options drawer when its switch is turned on,
//              and closes it when turned off.
// -------------------------------------------------------------------------------------
function TogglePaymentMethodDrawer() {
    var drawer = $("#" + $(this).attr('drawer'));
    $(this).attr('aria-expanded', this.checked ? 'true' : 'false');
    if (this.checked) {
        drawer.slideDown(200);
    } else {
        drawer.slideUp(200);
    }
}

// -------------------------------------------------------------------------------------
// Function: 	ShowEventSettingsMessages
// Description: This function shows the save result (or the list of problems) above
//              the Save button.
// -------------------------------------------------------------------------------------
function ShowEventSettingsMessages(messages, is_error) {
    var html = '<ul class="' + (is_error ? 'event_settings_error' : 'event_settings_success') + '">';
    for (var i = 0; i < messages.length; i++) {
        html += '<li>' + EscapeEventSettingsText(messages[i]) + '</li>';
    }
    html += '</ul>';
    $("#event_settings_messages").html(html);
}

function EscapeEventSettingsText(text) {
    return $('<div>').text(text).html();
}

var FRONTPAGE_IMAGE_MAX_BYTES = 10 * 1048576; // keep in step with EVENTSettings.php

// -------------------------------------------------------------------------------------
// Function: 	UploadFrontpageImage
// Description: This function uploads the chosen front page image as soon as it is
//              picked. The server resizes and saves it and makes it the event's front
//              page image, then the preview shows the new image.
// -------------------------------------------------------------------------------------
function UploadFrontpageImage() {
    var input = this;
    var file = input.files[0];
    if (!file) {
        return;
    }
    if (file.size > FRONTPAGE_IMAGE_MAX_BYTES) {
        ShowFrontpageUploadStatus('The image is too large. Please choose one under 10 MB.', true);
        input.value = '';
        return;
    }

    var form = $("#event_settings_form");
    var data = new FormData();
    data.append('method', 'UploadFrontpageImage');
    data.append('event_id', form.attr('event_id'));
    data.append('csrf_token', form.attr('csrf_token'));
    data.append('frontpage_image', file);

    var button = $("#event_setting_frontpage_upload_button");
    button.addClass('disabled').text('Uploading...');
    input.disabled = true; // the button is a label for the input - this stops a second upload starting
    ShowFrontpageUploadProgress('Uploading...');
	$.ajax({
	    url: 'EVENTSettings.php',
	    type: 'POST',
	    dataType: 'json',
	    data: data,
	    processData: false,
	    contentType: false,
	    xhr: function() {
            var xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.onprogress = function(event) {
                    if (!event.lengthComputable) {
                        return;
                    }
                    var percent = Math.round(event.loaded / event.total * 100);
                    ShowFrontpageUploadProgress(percent < 100 ? 'Uploading... ' + percent + '%' : 'Resizing and saving...');
                };
            }
            return xhr;
	    },
	    success: function(results) {
            if (results[0] == 'Pass') {
                $("#event_setting_EVENT_Frontpage_Image").val(results[1].filename);
                $("#event_setting_frontpage_preview_none").hide();
                $("#event_setting_frontpage_preview").attr('src', results[1].url).show();
                ShowFrontpageUploadStatus('New front page image saved.', false);
            } else {
                ShowFrontpageUploadStatus([].concat(results[1]).join(' '), true);
            }
	    },
	    error: function(xhr, status, error) {
            console.log('(UploadFrontpageImage) upload failed:', xhr.status, status, error, xhr.responseText);
            var reason = (xhr.status === 413) ? ' The image is too large for the server to accept.' : '';
            ShowFrontpageUploadStatus('The image could not be uploaded.' + reason + ' Please try again.', true);
	    },
	    complete: function() {
            button.removeClass('disabled').text('Upload New Image');
            input.disabled = false;
            input.value = '';
	    }
	  });
}

// -------------------------------------------------------------------------------------
// Function: 	UpdateFrontpageImagePreview
// Description: This function shows the image named in the Front Page Image field.
// -------------------------------------------------------------------------------------
function UpdateFrontpageImagePreview() {
    var filename = $.trim($("#event_setting_EVENT_Frontpage_Image").val());
    if (filename === '') {
        $("#event_setting_frontpage_preview").hide().attr('src', '');
        $("#event_setting_frontpage_preview_none").text('No image set').show();
    } else {
        // Try the event's own images folder first; HandleFrontpageImagePreviewError falls back to the shared folder
        var preview = $("#event_setting_frontpage_preview");
        var folder = preview.attr('event_image_dir') || preview.attr('shared_image_dir');
        $("#event_setting_frontpage_preview_none").hide();
        preview.attr('src', folder + '/' + encodeURIComponent(filename)).show();
    }
}

function HandleFrontpageImagePreviewError() {
    var preview = $(this);
    var src = preview.attr('src');
    if (!src) {
        return;
    }
    var shared_dir = preview.attr('shared_image_dir');
    if (src.indexOf(shared_dir + '/') !== 0) {
        preview.attr('src', shared_dir + src.substring(src.lastIndexOf('/')));
        return;
    }
    preview.hide();
    $("#event_setting_frontpage_preview_none").text('Image not found in ' + (preview.attr('event_image_dir') || shared_dir) + '/').show();
}

function ShowFrontpageUploadProgress(message) {
    $("#event_setting_frontpage_upload_status")
        .removeClass('event_settings_error event_settings_success')
        .empty()
        .append('<span class="upload_spinner"></span>', $('<span>').text(message));
}

function ShowFrontpageUploadStatus(message, is_error) {
    $("#event_setting_frontpage_upload_status")
        .toggleClass('event_settings_error', is_error)
        .toggleClass('event_settings_success', !is_error && message !== '')
        .text(message);
}

