// -------------------------------------------------------------------------------------
// Function: 	RenderEditSponsorInformation
// Description: This function renders the edit sponsor information page. This page allows
//				a user to edit all the properties of existing sponsors or add a new
//				sponsor to the database.
// -------------------------------------------------------------------------------------
function RenderEditSponsorInformation() {

    $("#managed_events_selection_container").removeClass('active');
    var event_id = $("#edit_sponsor_information").attr('event_id');
    $.ajax({
      url: 'ACCOUNTManagement.php',
      type: 'POST',
      data: {method: 'RenderEditSponsorInformation', event_id:event_id},

      success: function(data) {
        console.log('Render Sponsor Information Javascript function calling PHP GENERALFunctions.')
        results = eval(data);
        $("#details_info").html(results[0]);
        
        // // Set the click handler for the submit changes button.
        // document.querySelector("[id='submit_changes_button']").onclick = SubmitChangesToDatabase;
  
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

        
      }
    });		
  }
  
// -------------------------------------------------------------------------------------
// Function: 	RenderEditIndividualSponsorInformation
// Description: This function renders the edit sponsor informationfor a single sponsor.
//              This page allows a user to edit all the properties of existing sponsors.
// -------------------------------------------------------------------------------------
  function RenderEditIndividualSponsorInformation(event) {
    console.log('Edit sponsor information button pressed for Sponsor ID = ' + $(this).attr('sponsor_id'));
    var sponsor_id = $(this).attr('sponsor_id');
    var event_id = $(this).attr('event_id');
    var description_section = 'textarea#sponsor_edit_description_'+sponsor_id;

    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        data: {method: 'RenderEditIndividualSponsorInformation', sponsor_id:sponsor_id, event_id:event_id},
        success: function(data) {
          console.log('rendering the sponsor edit section for sponsor id = '+description_section);
          console.log(data);
          results = eval(data);
        //   $("#details_info").html(results[0]);
          if (window.tinymce) {
            tinymce.remove();  // editors from a previous visit - their textareas are about to be replaced
          }
          $("#main_content_container").html(results[0]);
          RenderBackButton('backtoeditsponsors', event_id);
          if (!document.getElementById('individual_sponsor_edit_container')) {
            return;  // the sponsor couldn't be found - the page just says so
          }
          
          // // Set the click handler for the submit changes button.
          window.tinymce && tinymce.init({  // without TinyMCE the description is a plain text box
            selector: "textarea#sponsor_edit_section_"+sponsor_id+"",
            promotion: false,  // no Upgrade button
            inline: false,
            height: 500,
            width: '100%',
            toolbar: 'forecolor backcolor',
            color_map: [
              'FF0000', 'Red',
              'FFFF00', 'Yellow',
              '008000', 'Green',
              '0000FF', 'Blue'
            ],            
            // Save Changes saves the description too - it lights up once the text changes
            setup: function (editor) {
              editor.on('input change undo redo SetContent', ActivateSponsorInfoChangeButton);
            },

            plugins: [
              'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
              'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
              'insertdatetime', 'media', 'table', 'help', 'wordcount', 'autoresize'
            ],
            toolbar: 'undo redo | blocks | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
          });  

          /* Click Handler for turning on and off a sponsor */
          var elms = document.querySelectorAll("input[id^='sponsor_edit_infobox']");
          for(var i = 0; i < elms.length; i++) {
              elms[i].onkeyup = ActivateSponsorInfoChangeButton;
          }          

          document.querySelector("[id='sponsor_edit_infodropdown_level']").onchange = ActivateSponsorInfoChangeButton;
          document.querySelector("[id='sponsor_edit_section_"+sponsor_id+"']").oninput = ActivateSponsorInfoChangeButton;  // plain text box when TinyMCE isn't loaded
          document.querySelector("[id='save_changes_button']").onclick = SaveNewSponsorProperties;
          document.querySelector("[id='edit_sponsor_logo']").onchange = UploadSponsorLogo;
          
        }
      });
}

// -------------------------------------------------------------------------------------
// Function: 	RenderEditIndividualSponsorshipLevelInformation
// Description: This function renders the edit sponsorship level informationfor a level.
//              This page allows a user to edit all the properties of existing levels.
// -------------------------------------------------------------------------------------
function RenderEditIndividualSponsorshipLevelInformation(event) {
  console.log('Edit sponsorship level information button pressed for Level ID = ' + $(this).attr('level_id'));
  var level_id = $(this).attr('level_id');
  var event_id = $(this).attr('event_id');

  $.ajax({
      url: 'sponsor_management.php',
      type: 'POST',
      data: {method: 'RenderEditIndividualSponsorshipLevelInformation', level_id:level_id},
      success: function(data) {
        console.log(data);
        results = eval(data);
        $("#main_content_container").html(results[0]);
        RenderBackButton('backtoeditsponsorshiplevels', event_id);
          console.log('HERE NOW');
          /* Click Handler for turning on and off a sponsor */
          var elms = document.querySelectorAll("input[id^='sponsorship_level_edit_infobox']");
          for(var i = 0; i < elms.length; i++) {
              elms[i].onkeyup = ActivateSponsorshipLevelInfoChangeButton;
          }          
          document.querySelector("[id='save_level_changes_button']").onclick = SaveNewSponsorshipLevelProperties;        
          document.querySelector("[id='sponsorship_level_info_dropdown_logosize']").onchange = ActivateSponsorshipLevelInfoChangeButton;
      }
    });
}


// -------------------------------------------------------------------------------------
// Function: 	RenderAddNewSponsorInformation
// Description: This function renders the add sponsor section.
// -------------------------------------------------------------------------------------
function RenderAddNewSponsorInformation(event) {
  var event_id = $(this).attr('event_id');
    console.log('Add New sponsor information to event number = ', event_id);

    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        data: {method: 'RenderAddNewSponsorSection', event_id:event_id},
        success: function(data) {
          console.log('rendering the add sponsor edit section');
          results = eval(data);
          if (window.tinymce) {
            tinymce.remove();  // editors from a previous visit - their textareas are about to be replaced
          }
          $("#main_content_container").html(results[0]);
          RenderBackButton('backtoeditsponsors', event_id);

          // No sponsorship levels yet - the page only explains that and links to the levels page
          var levels_link = document.querySelector("[id='go_to_sponsorship_levels']");
          if (levels_link) {
            levels_link.onclick = function(click) {
              click.preventDefault();
              RenderSponsorshipManagementInterface(event_id);
            };
            return;
          }
          
          // Set the click handler for the submit changes button.
          window.tinymce && tinymce.init({  // without TinyMCE the description is a plain text box
            selector: "textarea#new_sponsor_edit_section",
            promotion: false,  // no Upgrade button
            inline: false,
            height: 500,
            width: '100%',
            // save_onsavecallback: function () { SaveSponsorDesciption(tinymce.get("sponsor_edit_section_"+sponsor_id+"").getContent());},

            plugins: [
              'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
              'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
              'insertdatetime', 'media', 'table', 'help', 'wordcount', 'autoresize'
            ],
            toolbar: 'undo redo | blocks | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
          });  

          /* Click Handler for turning on and off a sponsor */
        //   var elms = document.querySelectorAll("input[id^='sponsor_edit_infobox']");
        //   for(var i = 0; i < elms.length; i++) {
        //       elms[i].onkeyup = ActivateSponsorInfoChangeButton;
        //   }          

        //   document.querySelector("[id='sponsor_edit_infodropdown_level']").onchange = ActivateSponsorInfoChangeButton;
        //   document.querySelector("[id='sponsor_edit_section_"+sponsor_id+"']").onchange = SetDescriptionSectionChangeFlag;
          document.querySelector("[id='new_sponsor_logo']").onchange = PreviewNewSponsorLogo;
          document.querySelector("[id='save_new_sponsor_information_botton']").onclick = SaveNewSponsorToDatabase;
          
        }
      });
}

// -------------------------------------------------------------------------------------
// Function: 	RenderAddNewSponsorshipLevelInformation
// Description: This function renders the add sponsor section.
// -------------------------------------------------------------------------------------
function RenderAddNewSponsorshipLevelInformation(event) {
    var event_id = $(this).attr('event_id');  
      console.log('(RenderAddNewSponsorshipLevelInformation) - Add New sponsor level information to event number = ', event_id);
  
      $.ajax({
          url: 'sponsor_management.php',
          type: 'POST',
          data: {method: 'RenderAddNewSponsorshipLevelSection', event_id:event_id},
          success: function(data) {
            console.log('rendering the add sponsor edit section');
            results = eval(data);
            $("#main_content_container").html(results[0]);
            RenderBackButton('backtoeditsponsorshiplevels', event_id);
            document.querySelector("[id='save_new_sponsorship_level_botton']").onclick = SaveNewSponsorshipLevelToDatabase;

          }
        });
  }


// -------------------------------------------------------------------------------------
// Function: 	PreviewNewSponsorLogo
// Description: Shows the logo chosen on the Add New Sponsor page before it's saved.
// -------------------------------------------------------------------------------------
function PreviewNewSponsorLogo() {
    var preview = $("#new_sponsor_logo_preview").empty();
    $("#save_new_sponsor_status").removeClass('event_settings_error event_settings_success').text('');
    var file = this.files[0];
    if (!file) {
        return;
    }
    if (file.size > FRONTPAGE_IMAGE_MAX_BYTES) {
        ShowUploadStatus($("#save_new_sponsor_status"), 'The logo is too large. Please choose one under 10 MB.', true);
        this.value = '';
        return;
    }
    preview.append($('<img>').attr({src: URL.createObjectURL(file), alt: 'Logo preview'}));
}

// -------------------------------------------------------------------------------------
// Function: 	SaveNewSponsorToDatabase
// Description: This function, when clicked, will save the new sponsor (and its logo,
//              if one was chosen) to the database, then return to the sponsor list.
// -------------------------------------------------------------------------------------
function SaveNewSponsorToDatabase(event) {
    event.preventDefault();
    var button = $(this);
    if (button.hasClass('disabled')) {
        return;
    }
    var container = $("#add_sponsor_container");
    var event_id = container.attr('event_id');
    var status = $("#save_new_sponsor_status");
    var active = document.querySelector('input[name="active_sponsor_radio_button"]:checked');
    var logo = document.getElementById("new_sponsor_logo").files[0];

    var data = new FormData();
    data.append('method', 'EnterNewSponsorIntoDatabase');
    data.append('event_id', event_id);
    data.append('csrf_token', container.attr('csrf_token'));
    data.append('sponsor_name', $("#sponsor_edit_infobox_name").val());
    data.append('sponsor_weblink', $("#sponsor_edit_infobox_weblink").val());
    data.append('sponsor_level', $("#sponsor_edit_infodropdown_level").val() || '');
    data.append('sponsor_active', active ? active.value : '0');
    var editor = window.tinymce && tinymce.get("new_sponsor_edit_section");
    data.append('sponsor_description', editor ? editor.getContent() : $("#new_sponsor_edit_section").val());
    if (logo) {
        data.append('sponsor_logo', logo);
    }

    button.addClass('disabled');
    ShowUploadProgress(status, logo ? 'Uploading...' : 'Saving...');
    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        dataType: 'json',
        data: data,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = $.ajaxSettings.xhr();
            if (logo && xhr.upload) {
                xhr.upload.onprogress = function(progress) {
                    if (progress.lengthComputable) {
                        var percent = Math.round(progress.loaded / progress.total * 100);
                        ShowUploadProgress(status, percent < 100 ? 'Uploading... ' + percent + '%' : 'Resizing and saving...');
                    }
                };
            }
            return xhr;
        },
        success: function(results) {
            if (results[0] == 'Pass') {
                RenderSponsorManagementInterface(event_id);
            } else {
                ShowUploadStatus(status, [].concat(results[1]).join(' '), true);
                button.removeClass('disabled');
            }
        },
        error: function(xhr, textStatus, error) {
            console.log('(SaveNewSponsorToDatabase) failed:', xhr.status, textStatus, error, xhr.responseText);
            var reason = (xhr.status === 413 || logo) ? ' If the logo is large, try a smaller one.' : '';
            ShowUploadStatus(status, 'The sponsor could not be saved.' + reason + ' Please try again.', true);
            button.removeClass('disabled');
        }
    });
}


// -------------------------------------------------------------------------------------
// Function: 	UploadSponsorLogo
// Description: Uploads the logo chosen on the Edit Sponsor page. It replaces the
//              sponsor's logo straight away, and the preview shows the saved logo.
// -------------------------------------------------------------------------------------
function UploadSponsorLogo() {
    var input = this;
    var file = input.files[0];
    var status = $("#edit_sponsor_logo_status");
    if (!file) {
        return;
    }
    if (file.size > FRONTPAGE_IMAGE_MAX_BYTES) {
        ShowUploadStatus(status, 'The logo is too large. Please choose one under 10 MB.', true);
        input.value = '';
        return;
    }

    var container = $("#individual_sponsor_edit_container");
    var data = new FormData();
    data.append('method', 'UploadSponsorLogo');
    data.append('event_id', container.attr('event_id'));
    data.append('sponsor_id', container.attr('sponsor_id'));
    data.append('csrf_token', container.attr('csrf_token'));
    data.append('sponsor_logo', file);

    input.disabled = true;
    ShowUploadProgress(status, 'Uploading...');
    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        dataType: 'json',
        data: data,
        processData: false,
        contentType: false,
        xhr: function() {
            var xhr = $.ajaxSettings.xhr();
            if (xhr.upload) {
                xhr.upload.onprogress = function(progress) {
                    if (progress.lengthComputable) {
                        var percent = Math.round(progress.loaded / progress.total * 100);
                        ShowUploadProgress(status, percent < 100 ? 'Uploading... ' + percent + '%' : 'Resizing and saving...');
                    }
                };
            }
            return xhr;
        },
        success: function(results) {
            if (results[0] == 'Pass') {
                $("#edit_sponsor_logo_preview").empty().append($('<img>').attr({src: results[1], alt: 'Current logo'}));
                ShowUploadStatus(status, 'Logo saved.', false);
            } else {
                ShowUploadStatus(status, [].concat(results[1]).join(' '), true);
            }
        },
        error: function(xhr, textStatus, error) {
            console.log('(UploadSponsorLogo) failed:', xhr.status, textStatus, error, xhr.responseText);
            ShowUploadStatus(status, 'The logo could not be uploaded. If it is large, try a smaller one.', true);
        },
        complete: function() {
            input.disabled = false;
            input.value = '';
        }
    });
}


// -------------------------------------------------------------------------------------
// Function: 	SaveNewSponsorshipLevelToDatabase
// Description: This function, when clicked, will save the new sponsorship level to the 
//              database (minus description information).
// -------------------------------------------------------------------------------------
function SaveNewSponsorshipLevelToDatabase() {
    var event_id = $(this).attr('event_id');
    console.log('(SaveNewSponsorshipLevelToDatabase) - Save the new sponsorship level to the database - EVENT ID = ' + event_id);
    var sponsorship_type = $("#sponsorship_level_infobox_type").val();
    var sponsorship_name = $("#sponsorship_level_infobox_name").val();
    var sponsorship_amount = $("#sponsorship_level_infobox_amount").val();
    var sponsorship_logosize = document.getElementById("sponsorship_level_info_dropdown_logosize");
    var sponsorship_logosize_description = sponsorship_logosize.options[sponsorship_logosize.selectedIndex].text;

    var sponsorship_active =  document.querySelector('input[name="active_sponsorship_level_radio_button"]:checked').value;
    console.log(event_id + ' : ' + sponsorship_type + ' : ' + sponsorship_name + ' : ' + sponsorship_amount + ' : ' + sponsorship_active + ' : ' + sponsorship_logosize.value + ' : ' + sponsorship_logosize_description);
    
    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        data: {method: 'EnterNewSponsorshipLevelIntoDatabase', event_id:event_id, sponsorship_type:sponsorship_type, sponsorship_name:sponsorship_name, sponsorship_amount:sponsorship_amount, sponsorship_active:sponsorship_active, sponsorship_logosize:sponsorship_logosize.value, sponsorship_logosize_description:sponsorship_logosize_description},
        success: function(data) {
          console.log(data);
          results = eval(data);
          if(results[0] === 'success') {
            $("#main_sponsorship_info_container").html(results[1]);

          } else {
            alert('The update failed: ' + results[1]);
          }
        }
      });    

}

// -------------------------------------------------------------------------------------
// Function: 	ActivateSponsorInfoChangeButton
// Description: This function handles the need to activate the SAVE new sponsor info in
//              the event that new information is available.
// -------------------------------------------------------------------------------------
function ActivateSponsorInfoChangeButton() {
    console.log("Check Save Button")
    ShowUploadStatus($("#save_changes_button_status"), '', false);
    var sponsorLevel = document.getElementById("sponsor_edit_infodropdown_level");
    var change_detected = false;
    $("[id^='sponsor_edit_infobox']").each(function() {
        
        // debugger
        if ($(this).val() != $(this).attr('current_value')) {
            console.log($(this).val() + ' DOES NOT MATCH ' + $(this).attr('current_value'));
            $("#save_changes_button").removeClass('disabled');
            change_detected = true;
        } else {
            console.log($(this).val() + ' = ' + $(this).attr('current_value'));
        }
    });

    console.log(sponsorLevel.value + ' with current level = ' + $("#sponsor_edit_infodropdown_level").attr('current_level'));
    if (sponsorLevel.value !== $("#sponsor_edit_infodropdown_level").attr('current_level')) {
        $("#save_changes_button").removeClass('disabled');
        change_detected = true;        
    }

    if (SponsorDescriptionChanged()) {
        $("#save_changes_button").removeClass('disabled');
        change_detected = true;
    }
    
    if (!(change_detected)) {
        console.log('Adding Disabled Back')
        $("#save_changes_button").addClass('disabled');
        change_detected = false;
    } else {
        console.log('HERE ONE');
    }
}

// -------------------------------------------------------------------------------------
// Function: 	SponsorDescriptionTextarea / SponsorDescriptionEditor / SponsorDescriptionChanged
// Description: The Edit Sponsor page's description - its TinyMCE editor (null when
//              TinyMCE isn't loaded, and the description is a plain text box), and
//              whether it has changed since it was loaded or last saved.
// -------------------------------------------------------------------------------------
function SponsorDescriptionTextarea() {
    return document.getElementById("sponsor_edit_section_" + $("#individual_sponsor_edit_container").attr('sponsor_id'));
}

function SponsorDescriptionEditor() {
    var textarea = SponsorDescriptionTextarea();
    return (textarea && window.tinymce) ? tinymce.get(textarea.id) : null;
}

function SponsorDescriptionChanged() {
    var editor = SponsorDescriptionEditor();
    var textarea = SponsorDescriptionTextarea();
    if (editor) {
        return editor.isDirty();
    }
    return textarea ? textarea.value !== textarea.defaultValue : false;
}

// -------------------------------------------------------------------------------------
// Function: 	ActivateSponsorshipLevelInfoChangeButton
// Description: This function handles the need to activate the SAVE new sponsorship level
//              info in the event that new information is available.
// -------------------------------------------------------------------------------------
function ActivateSponsorshipLevelInfoChangeButton() {
  console.log("Check Save Button")
  $("#save_changes_button_status").html('');
  var sponsorshipLogosize = document.getElementById("sponsorship_level_info_dropdown_logosize");
  var change_detected = false;
  $("[id^='sponsorship_level_edit_infobox']").each(function() {
      
      // debugger
      if ($(this).val() != $(this).attr('current_value')) {
          console.log($(this).val() + ' DOES NOT MATCH ' + $(this).attr('current_value'));
          $("#save_level_changes_button").removeClass('disabled');
          change_detected = true;
      } else {
          console.log($(this).val() + ' = ' + $(this).attr('current_value'));
      }
  });

  console.log(sponsorshipLogosize.value + ' with current logosize = ' + $("#sponsorship_level_info_dropdown_logosize").attr('current_level'));
  if (sponsorshipLogosize.value !== $("#sponsorship_level_info_dropdown_logosize").attr('current_level')) {
      $("#save_level_changes_button").removeClass('disabled');
      change_detected = true;        
  }
  
  if (!(change_detected)) {
      console.log('Adding Disabled Back')
      $("#save_level_changes_button").addClass('disabled');
      change_detected = false;
  } else {
      console.log('HERE TWO');
  }
}

// -------------------------------------------------------------------------------------
// Function: 	SaveNewSponsorProperties
// Description: This function, when clicked, will save the new sponsor details to the 
//              database (minus description information).
// -------------------------------------------------------------------------------------
function SaveNewSponsorProperties(event) {
    event.preventDefault();  // the button is a link to "#" - don't jump to the top of the page
    if ($(this).hasClass('disabled')) {
        return;
    }
    console.log('Save the properties for sponsor ID = ' + $(this).attr('sponsor_id'));
    var sponsor_id = $(this).attr('sponsor_id');
    var sponsor_name = $("#sponsor_edit_infobox_name").val();
    var sponsor_weblink = $("#sponsor_edit_infobox_weblink").val();
    var sponsor_level = document.getElementById("sponsor_edit_infodropdown_level");
    var editor = SponsorDescriptionEditor();
    var textarea = SponsorDescriptionTextarea();
    var sponsor_description = editor ? editor.getContent() : textarea.value;

    console.log(sponsor_id + ' : ' + sponsor_name + ' : ' + sponsor_weblink + ' : ' + sponsor_level.value);
    
    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        data: {method: 'UpdateSponsorDetails', event_id:$("#individual_sponsor_edit_container").attr('event_id'), sponsor_id:sponsor_id, sponsor_name:sponsor_name, sponsor_weblink:sponsor_weblink, sponsor_level:sponsor_level.value || '', sponsor_description:sponsor_description},
        success: function(data) {
          console.log(data);
          results = eval(data);
          if(results[0] === 'success') {
            ShowUploadStatus($("#save_changes_button_status"), $('<div>').html(results[1]).text(), false);  // green
            $("#sponsor_edit_infodropdown_level").attr('current_level', sponsor_level.value);
            $("#sponsor_edit_infobox_name").attr('current_value', sponsor_name);
            $("#sponsor_edit_infobox_weblink").attr('current_value', sponsor_weblink);
            if (editor) {
              editor.setDirty(false);
            } else {
              textarea.defaultValue = sponsor_description;
            }

            $("#save_changes_button").addClass('disabled');
          } else {
            ShowUploadStatus($("#save_changes_button_status"), 'The update failed: ' + [].concat(results[1]).join(' '), true);  // red
          }
        }
      });    

}

// -------------------------------------------------------------------------------------
// Function: 	SaveNewSponsorshipLevelProperties
// Description: This function, when clicked, will save the new sponsorship level details 
//              to the database.
// -------------------------------------------------------------------------------------
function SaveNewSponsorshipLevelProperties() {
  console.log('Save the new values for sponsorship level ID = ' + $(this).attr('level_id'));
  var sponsorship_id = $(this).attr('level_id');
  var sponsorship_name = $("#sponsorship_level_edit_infobox_name").val();
  var sponsorship_amount = $("#sponsorship_level_edit_infobox_amount").val();
  var sponsorship_logosize = document.getElementById("sponsorship_level_info_dropdown_logosize");
  var sponsorship_logosize_description = sponsorship_logosize.options[sponsorship_logosize.selectedIndex].text;

  console.log(sponsorship_id + ' : ' + sponsorship_name + ' : ' + sponsorship_amount + ' : ' + sponsorship_logosize.value + ' : ' + sponsorship_logosize_description);
  
  $.ajax({
      url: 'sponsor_management.php',
      type: 'POST',
      data: {method: 'UpdateSponsorshipLevelDetails', sponsorship_id:sponsorship_id, sponsorship_name:sponsorship_name, sponsorship_amount:sponsorship_amount, sponsorship_logosize:sponsorship_logosize.value, sponsorship_logosize_description:sponsorship_logosize_description},
      success: function(data) {
        console.log(data);
        results = eval(data);
        if(results[0] === 'success') {
          $("#individual_sponsorship_level_edit_container").html(results[1]);
        } else {
          alert('The update failed: ' + results[1]);
          $("#individual_sponsorship_level_edit_container").html(results[1]);
        }
        $("#save_level_changes_button").addClass('disabled');
      }
    });    

}

// -------------------------------------------------------------------------------------
// Function: 	HandleSponsorActivityChange
// Description: This functionsets the active state of the selected sponsor.
// -------------------------------------------------------------------------------------
function HandleSponsorActivityChange(event) {
    var sponsor_id = $(this).attr('sponsor_id');
    var sponsor_state = $(this).val();
    var event_id = $(this).attr('event_id');

    console.log('Change the state for sponsor = ',sponsor_id, 'for event = ',event_id);

    
    $.ajax({
        url: 'sponsor_management.php',
        type: 'POST',
        data: {method: 'UpdateSponsorshipActiveStatus', sponsor_id:sponsor_id, sponsor_state:sponsor_state, event_id:event_id},
        success: function(data) {
          console.log('Set the sponsor ID = '+sponsor_id+' to the value = '+sponsor_state+'')
          results = eval(data);
          //$("#details_info").html(results[0]);
          
          // // Set the click handler for the submit changes button.
          // document.querySelector("[id='submit_changes_button']").onclick = SubmitChangesToDatabase;
        }
      });    
}

// -------------------------------------------------------------------------------------
// Function: 	HandleSponsorshipLevelActivityChange
// Description: This functionsets the active state of the selected sponsorship level.
// -------------------------------------------------------------------------------------
function HandleSponsorshipLevelActivityChange(event) {
  var level_id = $(this).attr('level_id');
  var sponsorship_state = $(this).val();
  var event_id = $(this).attr('event_id');

  console.log('Change the state for sponsor = ',level_id, 'for event = ',event_id);

  
  $.ajax({
      url: 'sponsor_management.php',
      type: 'POST',
      data: {method: 'UpdateSponsorshipLevelActiveStatus', level_id:level_id, sponsorship_state:sponsorship_state, event_id:event_id},
      success: function(data) {
        console.log('Set the level ID = '+level_id+' to the value = '+sponsorship_state+'')
        results = eval(data);
      }
    });    
}

function RonDoingStuff() {
  console.log('Ron was here');
}