<?php
    $admin_rights =  $login_user_role;
	$navigation_tabs = '';
	switch($admin_rights) {
		case 'overlord':
	        $navigation_tabs .= '<nav>';
			$navigation_tabs .= '	<ul class="nav">';
            // $navigation_tabs .= '		<li><a href="index.php"><i class="icon solid fa-home fa-2x"></i></a></li>';
            $navigation_tabs .= '		<li><a href="index.php">Home</a></li>';
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_information" class="myaccount_link_active" action="information">Account Information</a></li>';							
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_attending" class="" action="attending">Events You\'ve Registered For</a></li>';
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_managing"  class="" action="managing">Events You Manage</a></li>';
			$navigation_tabs .= '		<li><a href="#" id="myaccount_link_overlord" class="" action="overlord">Overlord Tools</a></li>';
            $navigation_tabs .= '		<li><a href="logout.php">Log Out</a></li>';
            $navigation_tabs .= '	</ul>';
            $navigation_tabs .= '</nav>';						
            break;
        default:
            $navigation_tabs .= '<nav>';
            $navigation_tabs .= '	<ul class="nav">';
            // $navigation_tabs .= '		<li><a href="index.php"><i class="icon solid fa-home fa-2x"></i></a></li>';
            $navigation_tabs .= '		<li><a href="index.php">Home</a></li>';
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_information" class="myaccount_link_active" action="information">Account Information</a></li>';							
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_attending" class="" action="attending">Events You\'ve Registered For</a></li>';
            $navigation_tabs .= '		<li><a href="#" id="myaccount_link_managing"  class="" action="managing">Events You Manage</a></li>';
            $navigation_tabs .= '		<li><a href="logout.php">Log Out</a></li>';
            $navigation_tabs .= '	</ul>';
            $navigation_tabs .= '</nav>';	
            break;
    }
				
					
					print $navigation_tabs;
				?>                