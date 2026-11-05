<!DOCTYPE HTML>

<html>
	<head>
		<title>2022 NSX Mideast Registration</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/nsxmeetup.css" />
		<script src='https://www.google.com/recaptcha/api.js'></script>
	</head>
	<body class="is-preload">

		<!-- Header -->
			<header id="header">
				<?php include 'header2.php'; ?>
			</header>

		<!-- Main -->
			<div id="main" class="wrapper">
				<div class="inner">
					<h1 class="major">NSX Meetup User Creation</h1>
						<form method="post" action="#">
							<!-- ----------------------------------------------------------------- -->
						    <!-- User Credential Creation Section                                  -->
							<!-- ----------------------------------------------------------------- -->
							<label for="credentials">User Credentials</label>
							<div id="credentials" name="credentials" class="general_container">
								<input type="hidden" id="passwords_match" value=0>
								<div class="row gtr-uniform">
                            	  <div class="col-12 col-12-xsmall">
										<input type="text" name="name" id="username" value="" placeholder="Enter User Name or Email address" />
								  </div>
								  
								  <!-- Break -->
								  <div class="col-5 col-12-xsmall">
								  	<input type="password" name="newpassword" id="newpassword" value="" placeholder="Create A New Password" />
								  </div>
								  
								  <div class="col-5 col-12-xsmall">
								  	<input type="password" name="passwordmatch" id="passwordmatch" value="" placeholder="Re-Enter Password" />
								  </div>
								  
								  <div class="col-2 col-12-xsmall">
									<input type="checkbox" id="show_passwords_check" name="show_passwords">
									<label for="show_passwords_check">SHOW</span></label>
								  </div>
								  
								  <div class="col-12 col-12-xsmall">
								    <a href="#" id="user_verify_button" class="button small special left_float isDisabled" >Check Availability</a>
								  </div>									
								</div>
							</div>
							
							<!-- ----------------------------------------------------------------- -->
						    <!-- User Information Collection Section                               -->
							<!-- ----------------------------------------------------------------- -->
						
							<div id="information" name="information" class="general_container">
							<label for="information">User Information</label>								
								<div class="row gtr-uniform">
                            	    <div class="col-6 col-12-xsmall">							
							    		<input type="text" name="name" id="name" value="" placeholder="Name" />
							    	</div>
							    	<div class="col-6 col-12-xsmall">
							    		<input type="email" name="email" id="email" value="" placeholder="Email" />
							    	</div>
							    	<!-- Break -->
							    	<div class="col-12 col-12-xsmall">
							    		<div class="select-wrapper">
							    			<select name="category" id="category">
							    				<option value="">- Category -</option>
							    				<option value="1">Manufacturing</option>
							    				<option value="1">Shipping</option>
							    				<option value="1">Administration</option>
							    				<option value="1">Human Resources</option>
							    			</select>
							    		</div>
							    	</div>
							    </div>
							</div>
						</form>					
				</div>
			</div>

		<!-- Footer -->
			<section id="footer">

			</section>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/jquery.dropotron.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>
			<script src="assets/js/mailer.js"></script>
			<script src="assets/js/register.js"></script>
					
		<!-- ------------------------------------------------------------------ -->
		<!-- User Account Availability and Creation Section                     -->
		<!-- ------------------------------------------------------------------ -->
			<!-- Scripts to Handle Password Matching -->
			<script>
			  $(document).ready(function() {
			    $("#passwordmatch").keyup(checkPasswordMatch);
			    $("#username").keyup(checkPasswordMatch);
			  });
			</script>
			
			<script>
			  function checkPasswordMatch() {
			    var newpassword = $("#newpassword").val();
			    var passwordmatch = $("#passwordmatch").val();
				if ( newpassword !== '') {
				  if (newpassword == passwordmatch && $("#username").val() !== '') {
				    $("#user_verify_button").removeClass("isDisabled");
			      } else {
				    $("#user_verify_button").addClass("isDisabled");
			      }
				}
			  }
			</script>
			
			<!-- Toggle the password input type to show or hide it -->
			<script>
			  $("#show_passwords_check").on('click', function() {
				if( $(this).is(':checked') ) {
		          alert('checked');
				  $("#newpassword").prop({type:"text"});
				  $("#passwordmatch").prop({type:"text"});
				} else {
				  alert('not checked');
				  $("#newpassword").prop({type:"password"});
				  $("#passwordmatch").prop({type:"password"});
				}
			  });
			</script>

			<!-- Scripts to Handle Username and Password Availability and Creation -->
			<script>
			  $("#user_verify_button").click(function() {
			    event.preventDefault();
				if ( $(this).hasClass("isDisabled") ) {
				  alert('Do nothing');
				} else {
				  var username = $("#username").val();
				  alert(username);
				  $("#information").toggle();
	              $.ajax({
	              	'url' : 'generalfunctions.php',
	              	'type' : 'POST',
	              	'data' : { method: 'CheckUserNameAvailability', username:username},
	              	'success' : function(data) {
					  $("#username").prop('disabled', true);
					  $("#newpassword").prop('disabled', true);
					  $("#passwordmatch").prop('disabled', true);
				      $("#user_verify_button").addClass("isAvailable");
				      $("#user_verify_button").text("Available!");
					  
					  
	              	  alert('Data: '+data);
	              	}
	              });				  
				}
			  });
			</script>
	</body>
</html>