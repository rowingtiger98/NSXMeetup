<?php 

if(isset($_GET['resetid'])) {
    $page_type = 'SetNewPassword';
    $UUID = $_GET['resetid'];
} else {
    $page_type = 'SendResetLink';
    $UUIS = '';
}



?>

<!DOCTYPE HTML>
<html>
	<head>
		<title>NSX Meetup - <?php echo $page_type; ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">
        <input type=hidden id="page_information" uuid="<?php echo $UUID; ?>" page_type="<?php echo $page_type; ?>" />
        <?php if($page_type == 'SendResetLink') { ?>
		    <!-- Send Password Reset Link Section -->
            <section id="welcome" class="active">
				<div id="welcome_letter" class="inner">
                <div id="message_section">
                    <h2>NSX Meetup Password Reset</h2>
                    <div id="password_sending_text_container"><p>If you are having trouble remembering your password, simply enter your NSX Meetup user name in the form box below. Your username will be the email address you used to register your account with.
                        An email containing a customized link to our password reset page will be sent to your address. Open the email, click the link and enter your new password. These reset codes are good for 1 hour. 
                        If you don't reset your password within one hour of making the request, you will have to request a new code. If you have any problems, or need 
                        further assitance, simple send us an email at <a href="mailto:general_information@nsxmeetup.com">general_information@nsxmeetup.com</a> and we will contact you.</p>
                    <div class="row gtr-25 gtr-uniform">
                        <div class="col-12">
                            <input type="text" name="email" id="reset_password_email_address" value="" placeholder="Enter your email address" />
						</div>
                        <div class="col-12">
                            <a href="#" id="button_action_send_message" action="reset_password" class="button primary fit">Send Reset Code</a></li>
						</div>
                    </div></div>
					</div>
                    
				</div>
			</section>	
        <?php } else { ?>
            <!-- Set the new password section -->
            <section id="welcome" class="active">
				<div id="welcome_letter" class="inner">
                <div id="message_section">
                    <div id="reset_password_text_container"></div>
                </div>

				</div>
			</section>

        <?php } ?>
		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
			<script src="assets/js/mailer.js"></script>
			<script src="assets/js/render.js"></script>
			
			<script>
			  $( document ).ready(function() {
                var page_type = $("#page_information").attr('page_type');
                console.log(page_type);
				  
                if (page_type == 'SendResetLink') {
				var elms = document.querySelectorAll('[id^="button_action_"]');
				for (i = 0; i < elms.length; i++) {
					elms[i].onclick = HandleButtonActionClick;
				}
                } else {   
                    ValidateResetCodeTimeframe();                                   
                }

			  });
			  
			</script>

            <script>
                function ValidateResetCodeTimeframe() {
                    var UUID = $("#page_information").attr('uuid');
                    console.log(UUID);
				    $.ajax({
				      type: "POST",
				      url: "ACCOUNTManagement.php",
				      data: {method:'ValidateResetCodeTimeframe', UUID:UUID},
					  dataType: 'json',
				      success:  function(data){
						console.log(data);
                        results = eval(data);
                        var state = results[0];
                        var text = results[1];
                        
                        if (state == 'valid') {
                            $("#reset_password_text_container").html(text);
                            $("#reset_password_button").attr('uuid', UUID);
                            document.querySelector('[id="reset_password_button"]').onclick = ResetUserPassword;
                            document.querySelector('[id="info_text_field_newuser_password_confirm"]').onkeyup = CheckPasswordMatch;
                        } else if (state == 'used') {
                            $("#reset_password_text_container").html(text);
                        } else {
                            $("#reset_password_text_container").html('Invalid Action');
                        }                       
				      }
				    }); 
                }
            </script>

            <script>
                function ResetUserPassword(event) {
                    var password = $("#info_text_field_newuser_password_confirm").val();
                    var UUID = $(this).attr('uuid');
                    console.log(UUID + ':' + password + ' collected');
				    $.ajax({
				      type: "POST",
				      url: "ACCOUNTManagement.php",
				      data: {method:'ResetUserPassword', password:password, UUID:UUID},
					  dataType: 'json',
				      success:  function(data){
						console.log(data);
                        results = eval(data);
                        var state = results[0];
                        if (state == 'success') {
                            console.log('The password was updated.')
                            $("#reset_password_text_container").html('<h2>NSX Meetup Password Reset</h2><p>The password was successfully updated. You can return to the main page by clicking below and log in using your new passsword.</p><br><span style="display:block;"><a href="index.php" class="button primary fit">Return to the Home Page</a></span>');
                        } else {
                            console.log('The update failed with messsage = ' + results[1]);
                            $("#reset_password_text_container").html('<h2>NSX Meetup Password Reset</h2>The password reset has failed. For further help in resetting your password, please send an email to <a href="mailto:general_information@nsxmeetup.com">general_information@nsxmeetup.com</a> and we will reach out to you to get this resolved.<br><br><span style="display:block;"><a href="index.php" class="button primary fit">Return to the Home Page</a></span>');
                        }                       
				      }
				    });  
                }
            </script>


            <script>
                /* -------------------------------------------------------------------------------------------------------- */
                /* Function:	CheckPasswordMatch																	        */
                /* Description:	This function compares the passwords sent to the server at user creation.		            */
                /* -------------------------------------------------------------------------------------------------------- */
                function CheckPasswordMatch() {
                    var password1 = $("#info_text_field_newuser_password").val();
                    var password2 = $("#info_text_field_newuser_password_confirm").val();
                    var result = false;

                        // If password not entered
                        if (password2 == '') {
                            console.log("Please enter Confirmation Password");
                            result = false;
                        }      
                        // If Not same return False.    
                        else if (password1 != password2) {
                            console.log ("Password did not match: Please try again...")
                            document.getElementById($(this).attr('id')).style.color="#ff0000";
                            result = false;
                        }

                        // If same return True.
                        else{
                            console.log("Password Match: Welcome to GeeksforGeeks!")
                            document.getElementById($(this).attr('id')).style.color="green";
                            result = true;
                        }
			  
                    if (result == true) {
                        $("#reset_password_button").removeClass('disabled');
                        $("#reset_password_button").html('Reset Password');
                    } else {
                        $("#reset_password_button").addClass('disabled');
                        $("#reset_password_button").html('Reset Password - Password Mismatch');
                    }
                }
			</script>
	</body>
</html>