function HandleButtonActionClick(event) {
    event.preventDefault();
    var action = $(this).attr('action');
    console.log(action);

    if (action == 'general_message') {
      var ready = true;
      $('[id^="general_"]').each( function() {
        var id = $(this).attr('id');
        var elm = document.getElementById(""+id+"").value;
        if (elm.length <= 0) {
          document.getElementById(""+id+"").style.border = "1px solid red";
          ready = false;
        } else {
          document.getElementById(""+id+"").style.border = "1px solid #e6e6e6"; 
        }
      });					
        
      var name = $("#general_name").val();
      var email = $("#general_email").val();
      var message = document.getElementById("general_message").value;
      var primary_contact = $(this).attr('primary_contact');
      var secondary_contact = $(this).attr('secondary_contact');				  
      var g_recaptcha_response = document.getElementById('g-recaptcha-response').value;
      if(ready) {					    
        $.ajax({
          type: "POST",
          url: "/GENERALFunctions.php",
          data: {method:'MailerFunction', action:action, name:name, email:email, message:message, g_recaptcha_response:g_recaptcha_response, primary_contact:primary_contact, secondary_contact:secondary_contact},
          dataType: 'json',
          success:  function(data){
            console.log(data);
            if (data[0] == 'success') {
              $("#message_section").html(data[1]);
            } else {
              $("#message_section").append(data[1]);
            }
          }
        });
      }
    } else if (action == 'reset_password') {
        console.log('Send reset code');
        var ready = true;

          var elm = document.getElementById("reset_password_email_address").value;
          if (elm.length <= 0) {
            document.getElementById("reset_password_email_address").style.border = "1px solid red";
            ready = false;
          } else {
            document.getElementById("reset_password_email_address").style.border = "1px solid #e6e6e6";
            var email = $("#reset_password_email_address").val();

          }

          if(ready) {
            console.log('Sending message');					    
            $.ajax({
              type: "POST",
              url: "/GENERALFunctions.php",
              data: {method:'SendPasswordRecoveryLink', action:action, email:email},
              dataType: 'json',
              success:  function(data){
                console.log(data);
                results = eval(data);
                if (results[0] == 'success') {
                  console.log('Worked');
                  $("#password_sending_text_container").html('A message was successfully sent to the email address (user id) that you provided. The password reset link is only valid for 60 minutes. IF you do not reset your password within that time, you will need to request a new reset link.<br><br><span style="display:block;"><a href="index.php" class="button primary fit">Return to the Home Page</a></span>');
                } else {
                  console.log('FAILED');
                  $("#password_sending_text_container").html('The password reset link failed to send to the email address that you provided. For further help in resetting your password, please send an email to <a href="mailto:general_information@nsxmeetup.com">general_information@nsxmeetup.com</a>.<br><br><span style="display:block;"><a href="index.php" class="button primary fit">Return to the Home Page</a></span>');

                }
              }
            });
          }                   
    } else {
        console.log ('Unknown mailer type');
    }
  }