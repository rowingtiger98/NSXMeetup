<?php
	include 'generalfunctions.php';
?>

<!DOCTYPE html>

<html>
	<head>
		<title>GulleyWasher - Create User</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/custom.css" />
		<!--[if lte IE 9]><link rel="stylesheet" href="assets/css/ie9.css" /><![endif]-->
		<!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
	</head>
	<body>

		<!-- Header -->
			<header id="header">
				<h1><a href="index.php">GulleyWasher <span>Lottery Pool Manager</span></a></h1>
				<a href="#openModalFilter1">Log In</a>
				<a href="#menu">Menu</a>
			</header>

		<!-- Menu -->
			<nav id="menu">
				<ul class="links">
					<li><a href="index.php">Home</a></li>
					<li><a href="elements.html">Elements</a></li>
				</ul>
				<!-- <ul class="actions vertical">
					<li><a href="logout.php" class="button fit special">Log Out</a></li>
				</ul> -->
			</nav>

		<!-- Main -->
			<article id="main">

				<section class="wrapper">
					<div class="inner">
						<h3>Account Creation</h3>
						<hr>				
						<form method="post" action="#">
							<!-- ----------------------------------------------------------------- -->
						    <!-- User Credential Creation Section                                  -->
							<!-- ----------------------------------------------------------------- -->
							<label for="credentials">User Credentials</label>
							<div id="credentials" name="credentials" class="general_container">
								<input type="hidden" id="passwords_match" value=0>
								<div class="row uniform">
								  <div class="12u$">
										<input type="text" name="name" id="username" value="" placeholder="Enter User Name or Email address" />
								  </div>
								  <!-- Break -->
								
								  <div class="6u 12u$(xsmall)">
								  	<input type="password" name="newpassword" id="newpassword" value="" placeholder="Create A New Password" />
								  </div>									
								  <div class="6u 12u$(xsmall)">
								  	<input type="password" name="passwordmatch" id="passwordmatch" value="" placeholder="Re-Enter Password" />
								  </div>
								  <div class="6u 12u$(xsmall)">
									<input type="checkbox" id="show_passwords_check" name="show_passwords">
									<label for="show_passwords_check">Show Passwords</label>
								  </div>
								  <div class="6u 12u$(xsmall)">
								    <a href="#" id="user_verify_button" class="button small special left_float isDisabled" >Check Availability</a>
								  </div>									
								</div>
							</div>
							
							<!-- ----------------------------------------------------------------- -->
						    <!-- User Information Collection Section                               -->
							<!-- ----------------------------------------------------------------- -->
							<label for="information">User Information</label>							
							<div id="information" name="information" class="general_container">
							<div class="row uniform">
														
							
								<div class="6u 12u$(xsmall)">
									<input type="text" name="name" id="name" value="" placeholder="Name" />
								</div>
								<div class="6u$ 12u$(xsmall)">
									<input type="email" name="email" id="email" value="" placeholder="Email" />
								</div>
								<!-- Break -->
								<div class="12u$">
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
								<!-- Break -->
								<div class="4u 12u$(small)">
									<input type="radio" id="priority-low" name="priority" checked>
									<label for="priority-low">Low Priority</label>
								</div>
								<div class="4u 12u$(small)">
									<input type="radio" id="priority-normal" name="priority">
									<label for="priority-normal">Normal Priority</label>
								</div>
								<div class="4u$ 12u$(small)">
									<input type="radio" id="priority-high" name="priority">
									<label for="priority-high">High Priority</label>
								</div>
								<!-- Break -->
								<div class="6u 12u$(small)">
									<input type="checkbox" id="copy" name="copy">
									<label for="copy">Email me a copy of this message</label>
								</div>
								<div class="6u$ 12u$(small)">
									<input type="checkbox" id="human" name="human" checked>
									<label for="human">I am a human and not a robot</label>
								</div>
								<!-- Break -->
								<div class="12u$">
									<textarea name="message" id="message" placeholder="Enter your message" rows="6"></textarea>
								</div>
								<!-- Break -->
								<div class="12u$">
									<ul class="actions">
										<li><input type="submit" value="Send Message" class="special" /></li>
										<li><input type="reset" value="Reset" /></li>
									</ul>
								</div>
							</div>
							</div>
						</form>
										
										
										

					</div>
				</section>
			</article>

		<!-- Footer -->
			<footer id="footer">
				<div class="inner">
					<section class="about">
						<h4 class="major">Magna Aliquam Feugiat</h4>
						<p>Etiam finibus pharetra purus, imperdiet sagittis mauris hendrerit vitae. In feugiat ante elementum nulla arcu. Maecenas vulputate faucibus, convallis ligula ipsum dolor feugiat tempus adipiscing.</p>
						<ul class="actions">
							<li><a class="button">Learn more</a></li>
						</ul>
					</section>
					<section class="contact-info">
						<h4 class="major">Get in Touch</h4>
						<ul class="contact">
							<li class="fa-phone">(000) 000-0000</li>
							<li class="fa-envelope"><a href="#">information@untitled.tld</a></li>
							<li class="fa-twitter"><a href="#">@untitled-tld</a></li>
							<li class="fa-facebook"><a href="#">facebook.com/untitled</a></li>
						</ul>
						<ul class="contact">
							<li class="fa-home">Untitled Corp<br />1234 Fictional Road<br />Suite 5432<br />Nashville, TN 00000<br />USA</li>
						</ul>
					</section>
				</div>
				<div class="copyright">
					<p>&copy; Untitled Corp. All rights reserved.</p>
				</div>
			</footer>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
			<script src="assets/js/util.js"></script>
			<!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
			<script src="assets/js/main.js"></script>
		
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