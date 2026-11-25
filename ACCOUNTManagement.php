<?php
  require_once __DIR__ . '/databaseFunctions.php';
  require_once __DIR__ . '/eventAdmin.php';       // EventAdminAuthorize() for the registrant list
  require_once __DIR__ . '/paymentMethods.php';   // PAYMENT_METHODS names

  /* Account Information page choices (RenderUserAccountInformation / SaveMyAccountInformation) - defined
     before EndpointDispatch() below runs the posted method */
  /* The same choices as the registration form (driver1_container.php) */
  const MY_ACCOUNT_DISPLAY_NAMES = ['fullname' => 'Full Name', 'email' => 'Email Address', 'nsxprime' => 'NSX Prime Handle',
                                    'instagram' => 'Instagram Handle', 'tiktok' => 'TikTok Handle', 'twitter' => 'X Account', 'anonymous' => 'Anonymous'];
  const MY_ACCOUNT_SHARED_INFO = ['vehicle' => 'Vehicle Information', 'state' => 'State', 'email' => 'Email Address', 'facebook' => 'Facebook Link',
                                  'instagram' => 'Instagram Handle', 'tiktok' => 'TikTok Handle', 'twitter' => 'X Handle', 'nsxprime' => 'NSX Prime ID'];
  const MY_ACCOUNT_SHARED_DEFAULTS = ['vehicle' => 'yes', 'state' => 'yes'];  // what the registration form starts with checked
  const MY_ACCOUNT_STATES = ['AL','AK','AZ','AR','CA','CO','CT','DE','DC','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO',
                             'MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
  const MY_ACCOUNT_PROVINCES = ['AB','BC','MB','NB','NL','NS','NT','NU','ON','PE','QC','SK','YT'];
  const MY_ACCOUNT_CAR_COLORS = ['red','white','black','blue','orange','gray','silver','yellow','green','purple','other'];
  const MY_ACCOUNT_NSX_YEARS = ['First_Generation' => [1991, 2005], 'Second_Generation' => [2017, 2022]];  // as register.js offers them
  const MY_ACCOUNT_PASSWORD_MIN = 8;
  const MY_ACCOUNT_MAX_CARS = 10;

  require_once __DIR__ . '/endpointGuard.php';  // EndpointDispatch() - only the methods below, each for the callers it names
  const ACCOUNT_MANAGEMENT_METHODS = [
      'ValidateResetCodeTimeframe'           => 'public',
      'ResetUserPassword'                    => 'public',
      'RenderBackButton'                     => 'public',
      'UpdateUserAttendedEvents'             => 'registrant',
      'RenderUserAccountInformation'         => 'self',
      'SaveMyAccountInformation'             => 'self',
      'ChangeMyPassword'                     => 'self',
      'RenderMyAccountManage'                => 'self',
      'RenderManagementToolsSection'         => 'event_admin',
      'NewRenderFindEditRegistration'        => 'event_admin',
      'RenderEmailListGeneration'            => 'event_admin',
      'RenderEditSponsorInformation'         => 'event_admin',
      'RenderSponsorManagementInterface'     => 'event_admin',
      'RenderSponsorshipManagementInterface' => 'event_admin',
      'RenderSwapShopManagement'             => 'event_admin',
      'RenderMyAccountOverLord'              => 'overlord',
      'CreateEnterNewEventPage'              => 'overlord',
  ];
  EndpointDispatch(__FILE__, ACCOUNT_MANAGEMENT_METHODS, ['UpdateUserAttendedEvents', 'SaveMyAccountInformation', 'ChangeMyPassword']);

/* ************************************************************************ */
/* Funciton:    ValidateResetCodeTimeframe                         		*/
/* Description: This function returns the rendered User Account infomration.*/
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function ValidateResetCodeTimeframe() {
    $UUID = $_POST['UUID'];
    $output_html = '';

    $results = array();
	$conn = connectDB('kirbypar_nsx_meetup');
    $result = PreparedQuery($conn, 'SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Password_Reset_ID` = ?', [is_string($UUID) ? $UUID : '']);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();      
        $ts1 = strtotime($row['NSX_Password_Reset_Timer']);
        $ts2 = strtotime(date('Y-m-d H:i:s'));     
        $seconds_diff = $ts2 - $ts1;                            
        $time = ($seconds_diff/3600);

        if($seconds_diff <= 3600) {
            $results[] = 'valid';
            $output_html .= '<h2>NSX Meetup Password Reset</h2><p>Enter your new password in the first box and then re-enter to confirm in the second box. Click the Reset Password link at the bottom and that\'s it.</p>';
            $output_html .= '<div class="row gtr-25 gtr-uniform">';
            $output_html .= '<div class="col-6 col-12-xsmall">';
            $output_html .= '    <input type="password" name="password" id="info_text_field_newuser_password" value="" autocomplete="new-password" placeholder="Password" />';
            $output_html .= '</div>';
            $output_html .= '<div class="col-6 col-12-xsmall">';
            $output_html .= '    <input type="password" name="password2" id="info_text_field_newuser_password_confirm" value="" autocomplete="new-password" placeholder="Password Confirmation" />';
            $output_html .= '</div>';
            $output_html .= '<div class="col-12">';
            $output_html .= '    <a href="#" id="reset_password_button" action="reset_password" uuid="<?php echo $UUID; ?>" class="button primary fit">Reset Password</a></li>';
            $output_html .= '</div>'; 
            $output_html .= '</div>';           
            $results[] = $output_html;
        } else {
            $results[] = 'invalid';
            $results[] = "<h2>expired password reset code</h2><p>The password reset link you are attemping to use has a reset code that is over an hour old. This code is no longer valid. To reset your password, request another link by <a href='https://nsxmeetup.com/account_reset.php'>Clicking Here</a></p>";
        }
    } else {
        $results[] = 'used';
        $results[] = '<h2>NSX Meetup Password Reset</h2><p>The password reset link that you are attempting to use has already been accessed. This link is not longer valid. If you feel that you have reached this page in error, you can request another link by <a href="https://nsxmeetup.com/account_reset.php">Clicking Here</a> or contact us at <a href="mailto:general_information@nsxmeetup.com">general_information@nsxmeetup.com</a><br><br><span style="display:block;"><a href="index.php" class="button primary fit">Return to the Home Page</a></span>';
    }     

    closeDB($conn);
    echo json_encode($results);	
}

/* ************************************************************************ */
/* Funciton:    ResetUserPassword                                   		*/
/* Description: This function resets a users password.                      */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */  
function ResetUserPassword() {
    $password = $_POST['password'];
    $UUID = $_POST['UUID'];
    $results = array();
	$conn = connectDB('kirbypar_nsx_meetup');
    $result = PreparedQuery($conn, 'SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Password_Reset_ID` = ?', [is_string($UUID) ? $UUID : '']);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // The same one hour limit ValidateResetCodeTimeframe shows the form for
        if (!is_string($password) || $password === '' || (time() - strtotime($row['NSX_Password_Reset_Timer'])) > 3600) {
            closeDB($conn);
            echo json_encode(['fail', 'This password reset link has expired. Request another link and try again.']);
            return;
        }
        $new_password = password_hash($password, PASSWORD_BCRYPT, ["cost" => 11]);
        $user_id = $row['NSX_User_ID'];

        // Attempt an insertion with SQL injection protection
        $dbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
        $update_query = 'UPDATE `nsx_meetup_user_accounts` SET `NSX_Password` = :newpass,`NSX_Password_Reset_ID` = :nullvalue,`NSX_Password_Reset_Timer` = :nullvalue WHERE `NSX_User_ID` = :userid';
        $sth = $dbh->prepare($update_query);
        $sth->bindValue(":nullvalue", null, PDO::PARAM_STR);
        $sth->bindParam(':newpass', $new_password);  
        $sth->bindParam(':userid', $user_id, PDO::PARAM_INT);
        
        try {
            $dbh->beginTransaction();
            $sth->execute();
            $results[] = 'success';
            $results[] = 'success';
            $dbh->commit();
          } catch(PDOExecption $e) {
        
            $dbh->rollback();
            $results[] = 'fail';
            $results[] = $e->getMessage();
          }        
    }      

    closeDB($conn);
    echo json_encode($results);	
}

/* ************************************************************************ */
/* Function:    MyAccountFields                                             */
/* Description: The nsx_meetup_user_accounts columns a user can edit on     */
/*              their own Account Information page, grouped into the        */
/*              sections shown. Drives both rendering and saving. Role,     */
/*              events attended / administered and the password reset       */
/*              columns are never edited here; the password has its own     */
/*              form (ChangeMyPassword).                                    */
/*              type: text | email | state | select                         */
/*              max: the column's length                                    */
/* Returns:     array                                                       */
/* ************************************************************************ */
function MyAccountFields() {
    return [
        'Login & Contact' => [
            'NSX_Username'      => ['label' => 'Username', 'type' => 'text', 'max' => 32, 'required' => true,
                                    'help' => 'What you log in with.'],
            'NSX_Full_Name'     => ['label' => 'Full Name', 'type' => 'text', 'max' => 36, 'required' => true],
            'NSX_Email_Address' => ['label' => 'Email Address', 'type' => 'email', 'max' => 128, 'required' => true,
                                    'help' => 'Where event confirmations and password reset links are sent.'],
            'NSX_Cell_Number'   => ['label' => 'Cell Phone Number', 'type' => 'text', 'max' => 16],
        ],
        'Address' => [
            'NSX_Address' => ['label' => 'Street Address', 'type' => 'text', 'max' => 128],
            'NSX_City'    => ['label' => 'City', 'type' => 'text', 'max' => 32],
            'NSX_State'   => ['label' => 'State / Province', 'type' => 'state', 'max' => 2],
            'NSX_Zip'     => ['label' => 'Zip / Postal Code', 'type' => 'text', 'max' => 10],
        ],
        'Display & Social Media' => [
            'NSX_Event_Display_Name' => ['label' => 'Displayed Name', 'type' => 'select', 'max' => 32, 'options' => MY_ACCOUNT_DISPLAY_NAMES,
                                         'help' => 'What others see when you register for an event. If the choice you pick is blank, your full name is shown.'],
            'NSX_User_NSXPrime'      => ['label' => 'NSX Prime Handle', 'type' => 'text', 'max' => 32],
            'NSX_User_Facebook'      => ['label' => 'Facebook Link', 'type' => 'text', 'max' => 64],
            'NSX_User_Instagram'     => ['label' => 'Instagram Handle', 'type' => 'text', 'max' => 64],
            'NSX_User_TikTok'        => ['label' => 'TikTok Handle', 'type' => 'text', 'max' => 64],
            'NSX_User_Twitter'       => ['label' => 'X Handle', 'type' => 'text', 'max' => 64],
        ],
    ];
}

/* ************************************************************************ */
/* Function:    MyAccountLoadUser                                           */
/* Description: The logged-in user's full account row ('self' has already   */
/*              put their own user_id in $_POST).                           */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function MyAccountLoadUser() {
    $pdo = Create_SQL_PHP_DataObject(DB_NAME);
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_user_accounts WHERE NSX_User_ID = ?');
    $stmt->execute([(int)$_POST['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        EventAdminRespond('Fail', ['Your account could not be found. Please log in again.']);
    }
    return [$pdo, $user];
}

/* ************************************************************************ */
/* Function:    UserAccountCars                                             */
/* Description: The cars in an account's NSX_Car_Information. It holds a    */
/*              JSON list of cars; accounts saved before more than one car  */
/*              was allowed (and new ones from ACCOUNTCreation.php) hold a  */
/*              single car object, read here as a list of one. Each car is  */
/*              {car_color, car_year} for an NSX, or {car_make, car_model,  */
/*              car_year} otherwise - as registration stores them. The      */
/*              first car is the one registration fills in.                 */
/* Inputs:      $json - the column's value                                  */
/* Returns:     array of car arrays (empty if none)                         */
/* ************************************************************************ */
function UserAccountCars($json) {
    $data = json_decode((string)$json, true);
    if (!is_array($data) || !$data) {
        return [];
    }
    if (!array_key_exists(0, $data)) {
        $data = [$data];  // the single car format
    }
    return array_values(array_filter($data, function ($car) {
        return is_array($car) && (isset($car['car_make']) || (isset($car['car_year']) && $car['car_year'] !== ''));
    }));
}

/* ************************************************************************ */
/* Function:    MyAccountCarGeneration                                      */
/* Description: Which car choice a stored car is - as driver1_container.php */
/*              works it out.                                               */
/* Returns:     'First_Generation' | 'Second_Generation' | 'Other' | ''     */
/* ************************************************************************ */
function MyAccountCarGeneration(array $car) {
    if (isset($car['car_make'])) {
        return 'Other';
    }
    if (!isset($car['car_year']) || $car['car_year'] === '') {
        return '';
    }
    return ((int)$car['car_year'] <= 2016) ? 'First_Generation' : 'Second_Generation';
}

/* ************************************************************************ */
/* Function:    RenderMyAccountCar                                          */
/* Description: One car on the Account Information page: its choice (NSX    */
/*              generation or Not an NSX), then year and color or make,     */
/*              model and year, and a Remove button. account.js            */
/*              (ReactToMyAccountCarChoice) shows the right boxes for the   */
/*              choice; the ids end in the car's number to keep them unique.*/
/* Inputs:      $car - the car (empty for a new one), $n - its number, or   */
/*              '__N__' for the template account.js copies for Add a Car,   */
/*              $removable - false for the one car on a registration edit   */
/*              (MyRegistrations.php), which has no Remove button.          */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderMyAccountCar(array $car, $n, $removable = true) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $driver = 'profile'.$n;
    $generation = MyAccountCarGeneration($car);
    $is_nsx = isset(MY_ACCOUNT_NSX_YEARS[$generation]);

    $output_html  = '<div class="my_account_car payment_method" driver="'.$driver.'">';
    $output_html .= '<div class="payment_method_header"><span class="payment_method_name my_account_car_title">Car</span>';
    $output_html .= ($removable ? '<a href="#" class="button small my_account_remove_car">Remove</a>' : '').'</div>';
    $output_html .= '<div class="payment_method_drawer"><div class="row gtr-25 gtr-uniform">';
    $output_html .= '<div id="nsx_gen_response_container_'.$driver.'_select_gen" class="col-4 col-12-xsmall">';
    $output_html .= '<select id="nsx_gen_response_gen_selection_'.$driver.'" class="my_account_car_generation" driver="'.$driver.'" current_choice="'.($is_nsx ? 'nsx' : ($generation === 'Other' ? 'other' : 'none')).'">';
    $output_html .= '<option value="" disabled hidden'.($generation === '' ? ' selected' : '').'>Choose the car</option>';
    foreach (['First_Generation' => 'First Generation NSX', 'Second_Generation' => 'Second Generation NSX', 'Other' => 'Not an NSX'] as $option => $name) {
        $output_html .= '<option value="'.$option.'"'.($generation === $option ? ' selected' : '').'>'.$name.'</option>';
    }
    $output_html .= '</select></div>';
    $output_html .= '<div id="nsx_gen_response_container_'.$driver.'_nsx_year" class="col-4 col-12-xsmall generation_choice'.($is_nsx ? ' active' : '').'">';
    $output_html .= '<select id="nsx_gen_select_'.$driver.'_nsx_year" class="my_account_car_year">';
    $output_html .= '<option value="" disabled hidden'.($is_nsx ? '' : ' selected').'>Year</option>';
    if ($is_nsx) {
        list($first, $last) = MY_ACCOUNT_NSX_YEARS[$generation];
        for ($year = $first; $year <= $last; $year++) {
            $output_html .= '<option value="'.$year.'"'.((string)$year === (string)$car['car_year'] ? ' selected' : '').'>'.$year.'</option>';
        }
    }
    $output_html .= '</select></div>';
    $output_html .= '<div id="nsx_gen_response_container_'.$driver.'_nsx_color" class="col-4 col-12-xsmall generation_choice'.($is_nsx ? ' active' : '').'">';
    $output_html .= '<select id="nsx_gen_select_'.$driver.'_nsx_color" class="my_account_car_color">';
    $color = $is_nsx ? (string)($car['car_color'] ?? '') : '';
    $output_html .= '<option value="" disabled hidden'.(in_array($color, MY_ACCOUNT_CAR_COLORS, true) ? '' : ' selected').'>Color</option>';
    foreach (MY_ACCOUNT_CAR_COLORS as $option) {
        $output_html .= '<option value="'.$option.'"'.($color === $option ? ' selected' : '').'>'.ucfirst($option).'</option>';
    }
    $output_html .= '</select></div>';
    foreach (['make' => ['Make', 'col-4'], 'model' => ['Model', 'col-4'], 'year' => ['Year', 'col-4']] as $part => list($placeholder, $width)) {
        $value = ($generation === 'Other') ? (string)($car['car_'.$part] ?? '') : '';
        $output_html .= '<div id="nsx_gen_response_container_'.$driver.'_other_'.$part.'" class="'.$width.' col-12-xsmall generation_choice'.($generation === 'Other' ? ' active' : '').'">';
        $output_html .= '<input type="text" id="nsx_gen_response_'.$driver.'_other_'.$part.'" class="my_account_car_other_'.$part.'" value="'.$h($value).'" maxlength="32" placeholder="'.$placeholder.'" />';
        $output_html .= '</div>';
    }
    $output_html .= '</div></div></div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderUserAccountInformation                                */
/* Description: The Account Information page: the user's profile, car and   */
/*              sharing choices (saved by SaveMyAccountInformation), and a  */
/*              Change Password form (ChangeMyPassword). account.js wires   */
/*              up the buttons.                                             */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderUserAccountInformation() {
    list($pdo, $user) = MyAccountLoadUser();
    $pdo = null;
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $label_style = 'text-transform:uppercase;font-size: 1.1em;padding-right: 0.25em;color: #FFCC00;';

    $output_html  = '<h2>Account Information</h2>';
    $output_html .= '<p>Hello '.$h($user['NSX_Full_Name']).'. This is the information used when you register for events, the car you drive and what you share with other participants. Make your changes and click Save.</p>';
    $output_html .= '<form id="my_account_form" class="event_settings_form" onsubmit="return false;">';

    foreach (MyAccountFields() as $section => $fields) {
        $output_html .= '<h3 style="margin-top:2em;">'.$h($section).'</h3>';
        $output_html .= '<div class="row gtr-25 gtr-uniform">';
        foreach ($fields as $column => $field) {
            $id = 'my_account_'.$column;
            $value = (string)($user[$column] ?? '');
            $output_html .= '<div class="col-6 col-12-xsmall">';
            $output_html .= '<label for="'.$id.'" style="margin:0 0 0.25em 0;"><span style="'.$label_style.'">'.$h($field['label']).'</span>'.(!empty($field['required']) ? ' *' : '').'</label>';
            switch ($field['type']) {
                case 'state':
                    $output_html .= '<select id="'.$id.'" db_key="'.$column.'">';
                    $output_html .= '<option value=""'.($value === '' ? ' selected' : '').'>None</option>';
                    foreach (['State' => MY_ACCOUNT_STATES, 'Province' => MY_ACCOUNT_PROVINCES] as $group => $codes) {
                        $output_html .= '<optgroup label="'.$group.'">';
                        foreach ($codes as $code) {
                            $output_html .= '<option value="'.$code.'"'.($value === $code ? ' selected' : '').'>'.$code.'</option>';
                        }
                        $output_html .= '</optgroup>';
                    }
                    $output_html .= '</select>';
                    break;
                case 'select':
                    if (!isset($field['options'][$value])) {
                        $value = 'fullname';
                    }
                    $output_html .= '<select id="'.$id.'" db_key="'.$column.'">';
                    foreach ($field['options'] as $option => $name) {
                        $output_html .= '<option value="'.$h($option).'"'.($value === $option ? ' selected' : '').'>'.$h($name).'</option>';
                    }
                    $output_html .= '</select>';
                    break;
                default: // text, email
                    $output_html .= '<input type="'.($field['type'] === 'email' ? 'email' : 'text').'" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" maxlength="'.(int)$field['max'].'"'
                                   .($column === 'NSX_Username' ? ' autocomplete="username"' : '').' />';
            }
            if (isset($field['help'])) {
                $output_html .= '<p class="registration_text" style="margin-top:0.25em;">'.$h($field['help']).'</p>';
            }
            $output_html .= '</div>';
        }
        $output_html .= '</div>';
    }

    /* Cars - see UserAccountCars() for how they're stored */
    $cars = UserAccountCars($user['NSX_Car_Information']);
    $output_html .= '<h3 style="margin-top:2em;">Your Cars</h3>';
    $output_html .= '<p class="registration_text">Add each car you drive. Your first car is the one filled in when you register for an event.</p>';
    $output_html .= '<div id="my_account_cars" style="margin-top:0.75em;">';
    foreach ($cars as $n => $car) {
        $output_html .= RenderMyAccountCar($car, $n);
    }
    $output_html .= '</div>';
    $output_html .= '<p id="my_account_no_cars"'.($cars ? ' style="display:none;"' : '').'>No cars listed.</p>';
    $output_html .= '<template id="my_account_car_template">'.RenderMyAccountCar([], '__N__').'</template>';
    $output_html .= '<a href="#" id="my_account_add_car" class="button small" next_car="'.count($cars).'"'.(count($cars) >= MY_ACCOUNT_MAX_CARS ? ' style="display:none;"' : '').'>Add a Car</a>';
    $output_html .= ' <span class="registration_text">Up to '.MY_ACCOUNT_MAX_CARS.' cars.</span>';

    /* What's shared with other participants - stored as JSON {key: "yes" | "no"} */
    $shared = json_decode((string)$user['NSX_Event_Shared_Information'], true);
    $shared = is_array($shared) ? $shared : MY_ACCOUNT_SHARED_DEFAULTS;
    $output_html .= '<h3 style="margin-top:2em;">Shared With Other Participants</h3>';
    $output_html .= '<p class="registration_text">When you register for an event, the information checked below is shared with the other registered participants.</p>';
    $output_html .= '<div class="row gtr-25 gtr-uniform"><div class="col-12">';
    foreach (MY_ACCOUNT_SHARED_INFO as $key => $name) {
        $id = 'my_account_shared_'.$key;
        $output_html .= '<input type="checkbox" id="'.$id.'" class="my_account_shared" key="'.$key.'"'.(($shared[$key] ?? 'no') === 'yes' ? ' checked' : '').'>';
        $output_html .= '<label for="'.$id.'">'.$h($name).'</label> ';
    }
    $output_html .= '</div></div>';

    $output_html .= '<div id="my_account_messages" class="event_settings_messages" style="margin-top:1.5em;"></div>';
    $output_html .= '<a href="#" id="save_my_account_button" class="button primary fit">Save Account Information</a>';
    $output_html .= '</form>';

    /* Change Password - its own form and button */
    $output_html .= '<div style="border-bottom: 1px solid white; margin:2em 0em;"></div>';
    $output_html .= '<h3>Change Password</h3>';
    $output_html .= '<form id="my_account_password_form" class="event_settings_form" onsubmit="return false;">';
    $output_html .= '<input type="text" value="'.$h($user['NSX_Username']).'" autocomplete="username" style="display:none;" aria-hidden="true" />'; // for password managers
    $output_html .= '<div class="row gtr-25 gtr-uniform">';
    foreach (['current' => ['Current Password', 'current-password'], 'new' => ['New Password', 'new-password'], 'confirm' => ['Confirm New Password', 'new-password']] as $key => list($label, $autocomplete)) {
        $output_html .= '<div class="col-4 col-12-xsmall"><label for="my_account_password_'.$key.'" style="margin:0 0 0.25em 0;"><span style="'.$label_style.'">'.$label.'</span></label>';
        $output_html .= '<input type="password" id="my_account_password_'.$key.'" autocomplete="'.$autocomplete.'" /></div>';
    }
    $output_html .= '</div>';
    $output_html .= '<p class="registration_text" style="margin-top:0.5em;">At least '.MY_ACCOUNT_PASSWORD_MIN.' characters.</p>';
    $output_html .= '<div id="my_account_password_messages" class="event_settings_messages" style="margin-top:1em;"></div>';
    $output_html .= '<a href="#" id="change_my_password_button" class="button primary fit">Change Password</a>';
    $output_html .= '</form>';

    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    SaveMyAccountInformation                                    */
/* Description: Validates and saves the posted profile (settings[COLUMN],   */
/*              car[...], shared[key]) to the logged-in user's own account. */
/*              Nothing is saved if anything is invalid. A new username     */
/*              becomes the session's login.                                */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SaveMyAccountInformation() {
    list($pdo, $user) = MyAccountLoadUser();
    $posted = (isset($_POST['settings']) && is_array($_POST['settings'])) ? $_POST['settings'] : [];
    $values = [];
    $errors = [];

    foreach (MyAccountFields() as $fields) {
        foreach ($fields as $column => $field) {
            $raw = isset($posted[$column]) && is_string($posted[$column]) ? trim($posted[$column]) : '';
            $label = $field['label'];
            if ($raw === '' && !empty($field['required'])) {
                $errors[] = $label.' is required.';
            } elseif (mb_strlen($raw) > $field['max']) {
                $errors[] = $label.' must be '.$field['max'].' characters or fewer.';
            } elseif ($field['type'] === 'email' && $raw !== '' && !filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $label.' is not a valid email address.';
            } elseif ($field['type'] === 'state' && $raw !== '' && !in_array($raw, array_merge(MY_ACCOUNT_STATES, MY_ACCOUNT_PROVINCES), true)) {
                $errors[] = 'Choose a '.$label.' from the list.';
            } elseif ($field['type'] === 'select' && !isset($field['options'][$raw])) {
                $errors[] = 'Choose a '.$label.' from the list.';
            }
            $values[$column] = $raw;
        }
    }

    /* Username and email can't belong to another account */
    foreach (['NSX_Username' => 'That username', 'NSX_Email_Address' => 'That email address'] as $column => $name) {
        if ($values[$column] !== '' && strcasecmp($values[$column], (string)$user[$column]) !== 0) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM nsx_meetup_user_accounts WHERE '.$column.' = ? AND NSX_User_ID <> ?');
            $stmt->execute([$values[$column], (int)$user['NSX_User_ID']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = $name.' is already used by another account.';
            }
        }
    }

    /* Cars - cars[i][generation | year | color | make | model | other_year], saved in the order shown */
    $cars_post = (isset($_POST['cars']) && is_array($_POST['cars'])) ? array_values($_POST['cars']) : [];
    if (count($cars_post) > MY_ACCOUNT_MAX_CARS) {
        $errors[] = 'You can list up to '.MY_ACCOUNT_MAX_CARS.' cars.';
        $cars_post = [];
    }
    $cars = [];
    foreach ($cars_post as $i => $car_post) {
        $car_post = is_array($car_post) ? $car_post : [];
        $car_value = function ($key) use ($car_post) { return isset($car_post[$key]) && is_string($car_post[$key]) ? trim($car_post[$key]) : ''; };
        $which = 'Car '.($i + 1);
        $generation = $car_value('generation');
        if (isset(MY_ACCOUNT_NSX_YEARS[$generation])) {
            list($first, $last) = MY_ACCOUNT_NSX_YEARS[$generation];
            $year = $car_value('year');
            $color = $car_value('color');
            if (!ctype_digit($year) || (int)$year < $first || (int)$year > $last) {
                $errors[] = $which.': choose the NSX\'s year.';
            }
            if (!in_array($color, MY_ACCOUNT_CAR_COLORS, true)) {
                $errors[] = $which.': choose the NSX\'s color.';
            }
            $cars[] = ['car_color' => $color, 'car_year' => $year];
        } elseif ($generation === 'Other') {
            $other = ['car_make' => $car_value('make'), 'car_model' => $car_value('model'), 'car_year' => $car_value('other_year')];
            foreach (['car_make' => 'make', 'car_model' => 'model', 'car_year' => 'year'] as $key => $name) {
                if ($other[$key] === '') {
                    $errors[] = $which.': enter the car\'s '.$name.'.';
                } elseif (mb_strlen($other[$key]) > 32) {
                    $errors[] = $which.': the car\'s '.$name.' must be 32 characters or fewer.';
                }
            }
            $cars[] = $other;
        } else {
            $errors[] = $which.': choose the car from the list, or remove it.';
        }
    }
    $values['NSX_Car_Information'] = $cars ? json_encode($cars) : '';

    /* Shared information - every choice saved as yes / no */
    $shared_post = (isset($_POST['shared']) && is_array($_POST['shared'])) ? $_POST['shared'] : [];
    $shared = [];
    foreach (MY_ACCOUNT_SHARED_INFO as $key => $name) {
        $shared[$key] = (($shared_post[$key] ?? '') === 'yes') ? 'yes' : 'no';
    }
    $values['NSX_Event_Shared_Information'] = json_encode($shared);

    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    /* Column names come from MyAccountFields() and the two above, never from the request */
    $assignments = [];
    foreach (array_keys($values) as $column) {
        $assignments[] = '`'.$column.'` = :'.$column;
    }
    $stmt = $pdo->prepare('UPDATE nsx_meetup_user_accounts SET '.implode(', ', $assignments).' WHERE NSX_User_ID = :user_id');
    foreach ($values as $column => $value) {
        $stmt->bindValue(':'.$column, $value);
    }
    $stmt->bindValue(':user_id', (int)$user['NSX_User_ID'], PDO::PARAM_INT);
    try {
        $stmt->execute();
    } catch (PDOException $e) {
        error_log('SaveMyAccountInformation failed for user '.$user['NSX_User_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['Your account information could not be saved. Please try again.']);
    }

    if ($values['NSX_Username'] !== $user['NSX_Username']) {
        $_SESSION['login_user'] = $values['NSX_Username'];  // stay logged in under the new username
    }
    EventAdminRespond('Pass', 'Your account information has been saved.');
}

/* ************************************************************************ */
/* Function:    ChangeMyPassword                                            */
/* Description: Changes the logged-in user's password once their current    */
/*              password checks out. Also cancels any password reset link   */
/*              that's still waiting, and gives the session a new ID.       */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function ChangeMyPassword() {
    list($pdo, $user) = MyAccountLoadUser();
    $current = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
    $new = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
    $confirm = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

    if (!is_string($user['NSX_Password']) || !password_verify($current, $user['NSX_Password'])) {
        EventAdminRespond('Fail', ['Your current password is not correct.']);
    }
    $errors = [];
    if (mb_strlen($new) < MY_ACCOUNT_PASSWORD_MIN) {
        $errors[] = 'Your new password must be at least '.MY_ACCOUNT_PASSWORD_MIN.' characters.';
    } elseif (strlen($new) > 72) {
        $errors[] = 'Your new password must be 72 characters or fewer.';  // bcrypt ignores anything past 72 bytes
    }
    if ($new !== $confirm) {
        $errors[] = 'The new password and its confirmation don\'t match.';
    }
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    $stmt = $pdo->prepare('UPDATE nsx_meetup_user_accounts SET NSX_Password = ?, NSX_Password_Reset_ID = NULL, NSX_Password_Reset_Timer = NULL WHERE NSX_User_ID = ?');
    try {
        $stmt->execute([password_hash($new, PASSWORD_BCRYPT, ['cost' => 11]), (int)$user['NSX_User_ID']]);
    } catch (PDOException $e) {
        error_log('ChangeMyPassword failed for user '.$user['NSX_User_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['Your password could not be changed. Please try again.']);
    }
    session_regenerate_id(true);
    EventAdminRespond('Pass', 'Your password has been changed.');
}

/* ************************************************************************ */
/* Funciton:    RenderMyAccountManage                         				*/
/* Description: This function returns the rendered User's Managed Events.   */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */  
function RenderMyAccountManage() {
    $user_id = $_POST['user_id'];
    $user_email = $_POST['email'];
    $output_html = '';

	$results = array();	
	$conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_User_ID` = %d AND `NSX_Email_Address` = '%s'", $user_id, $user_email);
	$result = $conn->query($query);
	if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $output_html .= '<div id="managed_events_selection_container" class="managed_events_selection_container active">';
        $output_html .= "<h2>Managed Events</h2>";
        $output_html .= "<p>From the drop down list below, select the event you want to manage. If you don't see the event you are looking for, \
        please contact the event administrators for the event to add you to their admin list.</p>";

        /* Get the Events Managed List */
        if (strlen($row['NSX_User_Events_Administered']) > 0) {
            $events_query = sprintf("SELECT * from `nsx_meetup_events` WHERE `Event_ID` IN  (%s) ORDER BY `EVENT_Start_Date`", $row['NSX_User_Events_Administered']);
            $events_result = $conn->query($events_query);
            $output_html .= '  <div class="row gtr0-250">';
            $output_html .= '    <div class="col-12 col-12-small">';
            if($events_result->num_rows > 0) {
                $output_html .= '<select name="event_selection" id="managed_event_selection_dropdown">';
                while($event_row = $events_result->fetch_assoc()) {
                    $output_html .= '<option value="" selected disabled hidden>Select the event to manage</option>';
                    $output_html .= sprintf('<option value=%d>%s</option>',$event_row['EVENT_ID'], $event_row['EVENT_Name']);
                }
            } else {
                $output_html .= 'You currently have no events to manage. Please contact the event adminstrator(s) for the event(s) you wish to manage and ask them to add you to the admins list.';
            }
            $output_html .= '</select>';
            $output_html .= '    </div>';
            $output_html .= '  </div>';
            $output_html .= '</div>';
            $output_html .= '<div class="row gtr0-250">';
            $output_html .= '<div id="managed_event_tools_section" event_id="" class="col-12 col-12-small">';
            $output_html .= '</div>';
            $output_html .= '</div>';
            $results[] = 'Pass';
            $results[] = $output_html;
        } else {
            $results[] = 'Pass';
            $results[] = 'You currently have no events to manage. Please contact the event adminstrator(s) for the event(s) you wish to manage and ask them to add you to the admins list.';
        }        
	} else {
		$results[] = 'Pass';
		$results[] = "This user account or email address is causing problems in the database";
	}

    closeDB($conn);
	echo json_encode($results);		  	
}

/* ************************************************************************ */
/* Funciton:    RenderMyAccountOverLord                      				*/
/* Description: This function returns the rendered Overlord Tools.          */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */  
function RenderMyAccountOverLord() {
    $output_html = '';

	$results = array();	

    $output_html .= ' <section id="event_management_tools" event_id="'.$event_id.'">';
    $output_html .= '<div id="details_info" class="">';
    $output_html .= '<div class="features-grid">';

    /* -- Directions -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-plus-circle"></span></span>';
    $output_html .= '    <h3>Create a new event</h3>';
    $output_html .= '    <p>To create a new event hosted by NSX Meetup, simply start by clicking below.</p>';
    $output_html .= '    <button id="overlord_tools_button" class="button primary small fit" action="create_event">Click Here</button>';
    $output_html .= '</div>';

    /* -- Attendees -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-users"></span></span>';
    $output_html .= '<h3>Download All Registrations</h3>';
    $output_html .= '<p>Download a spreadsheet of all the registrations for the '.$event_name.'.</p>';
    $output_html .= '    <button id="overlord_tools_button" class="button primary small fit" action="future1">Click Here</button>';
    $output_html .= '</div>';
            
    /* -- Download Labels -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-user"></span></span>';
    $output_html .= '<h3>Download Labels Spreadsheet</h3>';
    $output_html .= '<p>Download a CSV file used to make lanyard labels</p>';
    $output_html .= '    <button id="overlord_tools_button" class="button primary small fit" action="future2">Click Here</button>';
    $output_html .= '</div>';

    $output_html .= '</div>';
    $output_html .= '</div>';
    $output_html .= '</section>';    

    $results[] = 'Pass';
    $results[] = $output_html;

	echo json_encode($results);		  	
}

/*-------------------------------------------------------------*/
/*                                                             */
/*  Event Management Tools Section                             */
/*                                                             */
/*-------------------------------------------------------------*/

/* ************************************************************************ */
/* Funciton:    RenderManagementToolsSection                   				*/
/* Description: This function returns the rendered Event Tools Section.     */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */
function  RenderManagementToolsSection () {
    $event_id = $_POST['event_id'];
    $results = array();

	$conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_events` WHERE `EVENT_ID`= %d", $event_id);
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $event_name = $row['EVENT_Name'];
        $collect_payments = ($row['EVENT_Collect_Payments'])?'yes':'no';
    } else {
        $event_name = 'Unknown Event Name';
        $collect_payments = 'no';
    }
    closeDB($conn);

    $output_html = '';

    $output_html .= ' <section id="event_management_tools" event_id="'.$event_id.'">';
    $output_html .= '<h2>'.$row['EVENT_Name'].' Management Tools</h2>';
    $output_html .= '<div id="details_info" class="">';
    $output_html .= '<div class="features-grid">';

    /* -- Edit Event Settings -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-cog"></span></span>';
    $output_html .= '    <h3>Edit Event Settings</h3>';
    $output_html .= '    <p>Change the event\'s name, dates, visibility, front page and contacts.</p>';
    $output_html .= '    <a href="#" id="admin_event_handler" action="edit_event_settings" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Enable / Edit Event Payment Options -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-credit-card"></span></span>';
    $output_html .= '    <h3>Enable / Edit Event Payment Options</h3>';
    $output_html .= '    <p>Set whether the event collects payments, the payment instructions, processing fee and Stripe checkout.</p>';
    $output_html .= '    <a href="#" id="admin_event_handler" action="edit_event_payments" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Add / Edit / Delete Registration Types -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-list"></span></span>';
    $output_html .= '    <h3>Add / Edit / Delete Registration Types</h3>';
    $output_html .= '    <p>Set up the types of registration (with their cost and description) that registrants choose from.</p>';
    $output_html .= '    <a href="#" id="admin_event_handler" action="edit_registration_types" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Find / Edit Registrations -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-user"></span></span>';
    $output_html .= '    <h3>Find / Edit / Delete Registrations</h3>';
    $output_html .= '    <p>Find, view, edit or delete a registration, see how each registrant is paying, and mark payments received. Use the filter box on the new page to quickly find the participant you need.</p>';
    $output_html .= '    <button id="admin_event_handler" action="find_edit_registration" class="button primary small fit" event_id="'.$event_id.'" collect_payment="'.$collect_payments.'">Click Here</button>';
    $output_html .= '</div>';

    /* -- Add / Edit Agenda Events -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-calendar-alt"></span></span>';
    $output_html .= '    <h3>Add / Edit Agenda Events</h3>';
    $output_html .= '    <p>Add, edit, hide or delete the events on the event\'s agenda - golf, dinners, drives - with their times, location, contact details and banner image.</p>';
    $output_html .= '    <a href="#" id="admin_event_handler" action="edit_weekend_events" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Add / Edit Registration Form Questions -- */
    $output_html .= '<div>';
    $output_html .= '    <span class="number"><span class="icon solid fa-clipboard-list"></span></span>';
    $output_html .= '    <h3>Add / Edit Registration Form Questions</h3>';
    $output_html .= '    <p>Add, edit, reorder, hide or delete the yes/no and drop-down questions on the registration form, including options that add a charge.</p>';
    $output_html .= '    <a href="#" id="admin_event_handler" action="edit_form_questions" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Add / Edit Hotels and Local Attractions -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-hotel"></span></span>';
    $output_html .= '<h3>Add / Edit Hotels and Local Attractions</h3>';
    $output_html .= '<p>Add, edit or delete the hotels and local attractions listed on the event\'s page.</p>';
    $output_html .= '<a href="#" id="render_local_attractions" class="button primary small fit" event_id="'.$event_id.'" editable="true">Click Here</a>';
    $output_html .= '</div>';

    /* -- Add / Edit Sponsorship Levels -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-layer-group"></span></span>';
    $output_html .= '<h3>Add / Edit Sponsorship Levels</h3>';
    $output_html .= '<p>In this section, you will be able to add a new sponsorship level or edit the properties of an existing level.</p>';
    $output_html .= '<a href="#" id="admin_event_handler" action="sponsorship_level_management" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Add / Edit Sponsor Information -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-handshake"></span></span>';
    $output_html .= '<h3>Add / Edit Sponsor Information</h3>';
    $output_html .= '<p>In this section, you will be able to add a new sponsor or edit the properties of an existing sponsor.</p>';
    $output_html .= '<a href="#" id="admin_event_handler" action="sponsor_management" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Attendees -- */
    // $output_html .= '<div>';
    // $output_html .= '<span class="number"><span class="icon solid fa-users"></span></span>';
    // $output_html .= '<h3>Download All Registrations</h3>';
    // $output_html .= '<p>Download a spreadsheet of all the registrations for the '.$event_name.'.</p>';
    // $output_html .= '<a href="#" id="download_participant_information" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Click Here</a>';
    // $output_html .= '</div>';

    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-users"></span></span>';
    $output_html .= '<h3>Registration Statistics and Downloadable CSV File</h3>';
    $output_html .= '<p>Breakdown of all the stats from regitrations for the '.$event_name.' and w downloadable CSV file.</p>';
    $output_html .= '<a href="#" id="admin_event_handler" action="registration_statistics_information" event_id="'.$event_id.'" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';    
            
    /* -- Download Labels -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-user"></span></span>';
    $output_html .= '<h3>Download Labels Spreadsheet</h3>';
    $output_html .= '<p>Download a CSV file used to make lanyard labels</p>';
    $output_html .= '<button id="download_labels_csv" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Click Here</button>';
    $output_html .= '</div>';



    /*-- Download Photo Numbers -- */
    // $output_html .= '<div>';
    // $output_html .= '<span class="number"><span class="icon solid fa-camera"></span></span>';
    // $output_html .= '<h3>Download Photo Numbers Spreadsheet</h3>';
    // $output_html .= '<p>Download a CSV file used to match drivers to cars in photos</p>';
    // $output_html .= '<button id="download_photonumber_csv" class="button primary small fit" event_id="'.$event_id.'">Click Here</button>';
    // $output_html .= '</div>';

    /* -- News Container -- */
    $output_html .= '<div>';
    $output_html .= '<span class="number"><span class="icon solid fa-envelope"></span></span>';
    $output_html .= '<h3>Generate EMail List</h3>';
    $output_html .= '<p>Click here to generate an comma separated list of all the registrants that you can easily copy to clipboard or cut and past into your favorite email application.</p>';
    $output_html .= '<a href="#" id="admin_event_handler" action="email_list_generation" class="button primary small fit" event_id="'.$event_id.'">Click Here</a>';
    $output_html .= '</div>';

    /* -- Swap Shop -- */
    // $output_html .= '<div>';
    // $output_html .= '<span class="number"><span class="icon solid fa-tag"></span></span>';
    // $output_html .= '<h3>Swap Shop</h3>';
    // $output_html .= '<p>Add / Edit / Delete an item </p>';
    // $output_html .= '<button id="manage_swap_shop" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Click Here</button>';
    // $output_html .= '</div>';        

    /* -- Sponsor Managememt Section -- */
    // $output_html .= '<div>';
    // $output_html .= '<span class="number"><span class="icon solid fa-user"></span></span>';
    // $output_html .= '<h3>Add / Edit Sponsor Information</h3>';
    // $output_html .= '<p>In this section, you will be able to add a new sponsor or edit the properties of an existing sponsor.</p>';
    // $output_html .= '<button id="edit_sponsor_information" class="button primary small fit" event_name="'.$event_name.'" event_id="'.$event_id.'">Click Here</button>';
    // $output_html .= '</div>';  

    /* -- Filler -- */
    // $output_html .= '<div>';
    // $output_html .= '<span class="number"><span class="icon solid fa-tag"></span></span>';
    // $output_html .= '<h3>Swap Shop</h3>';
    // $output_html .= '<p>Add / Edit / Delete an item </p>';
    // $output_html .= '<a href="#" class="button primary small fit" event_id="'.$event_id.'">Coming Soon</a>';
    // $output_html .= '</div>';   

    $output_html .= '</div>';
    $output_html .= '</div>';
    $output_html .= '</section>';

    $results[] = 'Pass';
    $results[] = $output_html;    
    echo json_encode($results);	
}

/* ************************************************************************ */
/* Funciton:    RenderManagementToolsSection                   				*/
/* Description: This function returns the rendered Event Tools Section.     */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */
function RenderEditLocalAttraction() {
    $event_id = $_POST['event_id'];
    $results = array();

    $results[] = "Print the local attractions management window for event = " . $event_id;
    
    echo json_encode($results);	

}

/* ************************************************************************ */
/* Funciton:    RetrieveUserInformation                   				    */
/* Description: This function returns a dictionary object off the users     */
/*              saved personal information.                                 */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */
function RetrieveUserInformation($username) {
	$conn = connectDB('kirbypar_nsx_meetup');
    $result = PreparedQuery($conn, "SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username` = ?", [(string)$username]);
    if($result->num_rows > 0) {
        $object = $result->fetch_object();
    } else {
        $object = "NONE";
    }
    closeDB($conn);
    return $object;
}

/* ************************************************************************ */
/* Funciton:    UpdateUserAttendedEvents                                 	    */
/* Description: This function sends the message from the web page.          */
/* Returns:     Nothing                                        				*/
/* ************************************************************************ */
function  UpdateUserAttendedEvents() {
	$driver1 = $_POST['driver1'];
	$driver2 = $_POST['driver2'];
    $event_id = (int)$_POST['event_id'];  // added to the account's list of events attended
	$result = array();
    $status = 'fail';
    $message = '';

    /* Driver One */
    $conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver1);
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $driver_1_email = $row['Participant_Email'];

        $account_results = PreparedQuery($conn, "SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username` = ?", [$driver_1_email]);
        if($account_results->num_rows > 0) {
            try {
                $account_row = $account_results->fetch_assoc();
                $existing_events_attended = $account_row['NSX_User_Events_Attended'];
                $new_event_attended = $existing_events_attended . ',' . $event_id;
                $eventdbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
                $update_query = "UPDATE `nsx_meetup_user_accounts` SET `NSX_User_Events_Attended`=:events_attended WHERE `NSX_Username`=:username";
                $eventsth = $eventdbh->prepare($update_query);
                $eventsth->bindParam(':events_attended', $new_event_attended);
                $eventsth->bindParam(':username', $driver_1_email);
                $eventdbh->beginTransaction();
                $eventsth->execute();
                $eventdbh->commit();
                $status = 'pass';
                $message .= "The account information was updated for the username = " . $driver_1_email;                
            } catch(PDOExecption $e) {
                $dbh->rollback();
                $status = 'fail';
                $message .= $e->getMessage();
            }             
            
        } else {
            $status = 'fail';
            $message .= "There does not appear to be an account for a user with the username = " . $driver_1_email;
        }        
    } else {
        $status = 'fail';
        $result[] = 'There was not a record to match the driver for some reason.';
    }

    /* Driver Two */
    if ($driver2 != '') {
        $query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `Participant_ID`=%d", $driver2);
        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $driver_2_email = $row['Participant_Email'];
    
            $account_results = PreparedQuery($conn, "SELECT * from `nsx_meetup_user_accounts` WHERE `NSX_Username` = ?", [$driver_2_email]);
            if($account_results->num_rows > 0) {
                try {
                    $account_row = $account_results->fetch_assoc();
                    $existing_events_attended = $account_row['NSX_User_Events_Attended'];
                    $new_event_attended = $existing_events_attended . ',' . $event_id;
                    $eventdbh = Create_SQL_PHP_DataObject('kirbypar_nsx_meetup');
                    $update_query = "UPDATE `nsx_meetup_user_accounts` SET `NSX_User_Events_Attended`=:events_attended WHERE `NSX_Username`=:username";
                    $eventsth = $eventdbh->prepare($update_query);
                    $eventsth->bindParam(':events_attended', $new_event_attended);
                    $eventsth->bindParam(':username', $driver_2_email);
                    $eventdbh->beginTransaction();
                    $eventsth->execute();
                    $eventdbh->commit();
                    $message .= "The account information was updated for the username = " . $driver_2_email;                
                } catch(PDOExecption $e) {
                    $dbh->rollback();
                    $status = 'fail';
                    $message .= $e->getMessage();
                }             
                
            } else {
                $status = 'fail';
                $message .= "There does not appear to be an account for a user with the username = " . $driver_2_email;
            }        
        } else {
            $status = 'fail';
            $message .= 'There was not a record to match the driver for some reason.';
        }




    } else {
        $message .= "There is no driver two.";
    }

    
    closeDB($conn);
    $results[] = $status;
    $results[] = $message;

	echo json_encode($results);

}

/* ************************************************************************ */
/* Function:    RenderEditSponsorInformation	              				*/
/* Description: This function renders the PHP code for the sponsors.		*/
/* Returns:     string                                        				*/
/* ************************************************************************ */  
function RenderEditSponsorInformation() {

    $event_id = $_POST['event_id'];

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
	$results = array();
	
	$output_html = '<div class="row gtr-0">';

    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<p>This is the sponsor management interface. Here, you will be able to edit the properties of all the existing sponsors in the le Tour de Femme
    sponsors database. These properties include everything from the basics, such as name and web link information for a sponsor, to advanced details such as sposnorship level and logos.
    Additionally, you will be able to set the active status of a current or past sponsor. Only active sponsors are displayed on the web site. Finally, you will be able
    to enter details for new sponsors as we gain them.</p>';
    $output_html .= '</div>';

    $output_html .= '<div class="col-12 col-12-small"><a href="#" id="create_new_sponsor_button" class="button primary">Add New Sponsor</a></div>';

    
	$query = "SELECT * FROM `nsx_meetup_event_sponsors`";
	$result = $link->query($query);

	if ($result->num_rows > 0) {
        $output_html .= RenderCurrentSponsorContent($event_id, $result);
	}
	
	$output_html .= '</div>';
	$output_html .= '<div class="admin_console_buttons">';
	// $output_html .= '<a href="#" id="submit_changes_button" table="letourdefemme_event_info" class="button disabled small" disabled>Submit Changes</a>';
	// $output_html .= '<a href="#" id="add_parameters_button" table="letourdefemme_event_info" class="button primary small">Add New Field</a>';
	$output_html .= '</div>';

    closeDB($link);
	$results[] = $output_html;
	echo json_encode($results); 
}

/* ************************************************************************ */
/* Function:    RenderCurrentSponsorContent	                 				*/
/* Description: This function will render the information about ALL the     */
/*              current le Tour de Femmme sponsor and give the user the     */
/*              ability to edit them.                                       */
/* Inputs:      #result = The database sponsor information.                 */
/* Returns:     html data                                     				*/
/* ************************************************************************ */  
function RenderCurrentSponsorContent($event_id, $result) {
    $output_html = '';
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<table id="sponsor_management_table"><head><tr>
                     <th>Sponsor Name</th>
                     <th>Active Sponsor</th>
                     <th>Sponsor Level</th>
                     <th></th>
                     </tr></thead><tbody>';
    
  
    while ($row = $result->fetch_array()) {
        $event_sponsored = explode(',', $row['SPONSOR_Event_IDs']);
        if (in_array($event_id, $event_sponsored)) {
            $sponsors_to_display[] = $row;

            $output_html .=  '<tr id="individual_sponsor" active_state='.$row['SPONSOR_Active'].'><td>'.$row['SPONSOR_Name'].'</td>';

            /* Setup Radio Buttons to enable / disable sponsors on the fly */
            $output_html .= '<td><input type="radio" id="active_sponsor_'.$row['SPONSOR_ID'].'_on" name="active_sponsor_radio_button_'.$row['SPONSOR_ID'].'" value="1" event_id='.$event_id.' sponsor_id='.$row['SPONSOR_ID'].' '.($row['SPONSOR_Active']?' checked':'').'>';
            $output_html .= '<label for="active_sponsor_'.$row['SPONSOR_ID'].'_on">Yes</label>';
            $output_html .= '<input type="radio" id="active_sponsor_'.$row['SPONSOR_ID'].'_off" name="active_sponsor_radio_button_'.$row['SPONSOR_ID'].'" value="0" event_id='.$event_id.' sponsor_id='.$row['SPONSOR_ID'].' '.($row['SPONSOR_Active']?'':' checked').'>';
            $output_html .= '<label for="active_sponsor_'.$row['SPONSOR_ID'].'_off">No</label></td>';       
            $output_html .=  '<td>'.$row['SPONSOR_Level'].'</td><td><a href="#" id="edit_sponsor_button" class="button small" event_id="'.(int)$event_id.'" sponsor_id="'.(int)$row['SPONSOR_ID'].'">Edit</a></td></tr>';
        }
    } 

    $output_html .= '</tbody></table>';
    $output_html .= '</div>';  
    return($output_html);
}

/* ************************************************************************ */
/* Function:    RenderCurrentSponsorshipLevelContent           				*/
/* Description: This function will render the information about ALL the     */
/*              current sponsorship levels and give the user the            */
/*              ability to edit them.                                       */
/* Inputs:      #result = The database sponsor information.                 */
/* Returns:     html data                                     				*/
/* ************************************************************************ */  
function RenderCurrentSponsorshipLevelContent($event_id, $result) {
    $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES); };
    $event_id = (int)$event_id;
    $output_html = '';
    $output_html .= '<div class="col-12 col-12-small">';
    $output_html .= '<table id="sponsorship_level_management_table"><thead><tr>
                     <th>Level Name</th>
                     <th>Active Level</th>
                     <th>Level Amount</th>
                     <th>Logo Size</th>
                     <th></th>
                     </tr></thead><tbody>';

    while ($row = $result->fetch_assoc()) {
        $id = (int)$row['LEVEL_ID'];
        $output_html .= '<tr id="individual_level" active_state="'.(int)$row['LEVEL_Active'].'">';
        $output_html .= '<td>'.$h($row['LEVEL_Name']).'</td>';

        /* Radio Buttons to enable / disable levels on the fly */
        $output_html .= '<td><input type="radio" id="active_sponsorship_level_'.$id.'_on" name="active_sponsorship_level_radio_button_'.$id.'" value="1" event_id="'.$event_id.'" level_id="'.$id.'"'.($row['LEVEL_Active'] ? ' checked' : '').'>';
        $output_html .= '<label for="active_sponsorship_level_'.$id.'_on">Yes</label>';
        $output_html .= '<input type="radio" id="active_sponsorship_level_'.$id.'_off" name="active_sponsorship_level_radio_button_'.$id.'" value="0" event_id="'.$event_id.'" level_id="'.$id.'"'.($row['LEVEL_Active'] ? '' : ' checked').'>';
        $output_html .= '<label for="active_sponsorship_level_'.$id.'_off">No</label></td>';

        $output_html .= '<td>$'.$h(number_format((float)$row['LEVEL_Amount'])).'</td>';
        $output_html .= '<td>'.$h($row['LEVEL_Logosize_Description']).'</td>';
        $output_html .= '<td><a href="#" id="edit_sponsorship_level_button" class="button small" event_id="'.$event_id.'" level_id="'.$id.'">Edit</a></td>';
        $output_html .= '</tr>';
    }

    $output_html .= '</tbody></table>';
    $output_html .= '</div>';  
    return($output_html);
}


/* ************************************************************************ */
/* Funciton:    RenderSwapShopManagement                     				*/
/* Description: This function returns the rendered Swap Shap Manager.       */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */  
function RenderSwapShopManagement() {
    $event_id = $_POST['event_id'];
    
    $output_html = '<h2>Swap Shop Manager Start</h2>';
	$output_html .= '<div id="main_swap_shop_manager_container"  style="margin-top:2em;" class="">';
    $output_html .= '   <div class="row gtr-0">';
    $output_html .= '       <div class="col-4 col-12-small">';
    $output_html .= '           <input type="radio" id="create_new_item" name="swapshopitem" value="new" onclick="HandleSwapShapDisplayChange(this);" event_id='.$event_id.' checked>';
    $output_html .= '           <label for="create_new_item">Create New Listing</label>';
    $output_html .= '       </div>';
    $output_html .= '       <div class="col-4 col-12-small">';
    $output_html .= '           <input type="radio" id="edit_existing_item" name="swapshopitem" value="existing" onclick="HandleSwapShapDisplayChange(this);" event_id='.$event_id.'>';
    $output_html .= '           <label for="edit_existing_item">Edit Existing Item</label>';
    $output_html .= '       </div>';    
    $output_html .= '   </div>';
    $output_html .= '   <div id="swap_shop_manager_content">This is the container for the needed NEW ITEM form information</div>';
    $output_html .= '</div>';
    
	$results[] = $output_html;
	echo json_encode($results);

}

/* ************************************************************************ */
/* Funciton:    RenderFindEditRegistration                            	    */
/* Description: This function renders the find / edit a registration panel. */
/* Returns:     HTML                                        				*/
/* ************************************************************************ */
function NewRenderFindEditRegistration() {
    $event_id = (int)($_POST['event_id'] ?? 0);

    // Registrants' details and payments - event admins (or overlords) only
    app_session_start();
    EventAdminAuthorize(Create_SQL_PHP_DataObject(DB_NAME), $event_id);
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };

	$output_html = '<div id="edit_find_user_registration" class="" event_id="'.$event_id.'" csrf_token="'.$h(app_csrf_token()).'">';
    $output_html .= '<h1 class="major">Registrant Search</h1>';
    $output_html .= '<div id="back_navigation_button"></div>';    
	$output_html .= '	<div id="rider_search_form_container" class="rider_search_form_container" >';
	$output_html .= '       <p class="small">To search for a participant, simply start typing their name into the box below. The list will be filtered after each entered letter. '
                   .'Click a name (or <span class="icon solid fa-search"></span>) to see and edit the registration.</p>';
	$output_html .= '		<input type="text" id="participant_name_search" name="participant_name_search" placeholder="Enter a name to filter the list">';
	$output_html .= '	</div>';
	$output_html .= '	<div id="registrant_list_messages"></div>';
	
	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
			
	/************************************************/
	/* Search each registered "User" one at a time. */
	/************************************************/
	$event_query = sprintf("SELECT * FROM `nsx_meetup_events` WHERE `EVENT_ID` = %d", $event_id);
	$result = $link->query($event_query);
	if ($result->num_rows > 0) {
        $row =  mysqli_fetch_assoc($result);
        $participant_ids = array_map('intval', array_filter(array_map('trim', explode(',', (string)$row['EVENT_Participants'])), 'strlen'));
        $collect_payments = ($row['EVENT_Collect_Payments'])?'yes':'no';
        $output_html .= '<div class="event_registration_sub_container active">';
        if (!$participant_ids) {
            $output_html .= '<p>No one has registered for this event yet.</p>';
        } else {
            // The event's registration type names, for the Registration Type column
            $stmt = Create_SQL_PHP_DataObject(DB_NAME)->prepare('SELECT REGTYPE_ID, REGTYPE_Name FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ?');
            $stmt->execute([$event_id]);
            $regtype_names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            $user_query = sprintf("SELECT * FROM `nsx_meetup_participants` WHERE `Participant_ID` IN (%s) ORDER BY `Participant_Name`", implode(',', $participant_ids));
            $user_result = $link->query($user_query);
            $output_html .= '<div class="table-wrapper">';
            $output_html .= '<table id="participant_checkin_table" class="alt rider_checkin_table registrant_table"><thead><tr>';
            $output_html .= '<th data-sort="string">Registrant (click to sort)</th><th data-sort="string">Registration Type</th><th data-sort="string">Paying With</th><th data-sort="string">Payment Status</th><th class="registrant_center">Received</th><th class="registrant_center">Delete</th>';
            $output_html .= '</tr></thead><tbody>';
            while ($part_row = mysqli_fetch_assoc($user_result)) {
                $id = (int)$part_row['Participant_ID'];
                list($status, $status_class, $status_icon) = RegistrantPaymentStatus($part_row, $collect_payments === 'yes');
                $method = (string)$part_row['Participant_Payment_Method'];
                $view_attributes = 'action="render_participant_information" event_id="'.$event_id.'" collect_payments="'.$collect_payments.'" participant_id="'.$id.'"';

                $output_html .= '<tr name="'.$h($part_row['Participant_Name']).'" registrant_id="'.$id.'">';
                $output_html .= '<td class="registrant_name_cell">';
                $output_html .= '<a href="#" class="registrant_view_icon" title="View / edit this registration" '.$view_attributes.'><span class="icon solid fa-search"></span></a> ';
                $output_html .= '<span id="edit_find_participant_link" class="edit_find_column" '.$view_attributes.'>'.$h($part_row['Participant_Name']).' ('.$h($part_row['Participant_Role']).')</span></td>';
                $regtype = $regtype_names[$part_row['Participant_Registration_Type'] ?? ''] ?? '';
                $output_html .= '<td>'.($regtype !== '' ? $h($regtype) : '<span class="registrant_none">-</span>').'</td>';
                $output_html .= '<td>'.(isset(PAYMENT_METHODS[$method]) ? $h(PAYMENT_METHODS[$method]['name']) : '<span class="registrant_none">-</span>').'</td>';
                $output_html .= '<td class="registrant_status_cell" data-sort-value="'.$h($status).'">'.RenderRegistrantStatus($part_row, $collect_payments === 'yes').'</td>';
                $output_html .= '<td class="registrant_center"><input type="checkbox" event_name="'.$h($row['EVENT_Name']).'" email_address="'.$h($part_row['Participant_Email']).'" part_name="'.$h($part_row['Participant_Name']).'" part_idnum="'.$id.'" id="participant_paid_checkbox_'.$id.'" '.($part_row['Participant_Balance_Paid']?" checked":"").' >';
                $output_html .= '<label for="participant_paid_checkbox_'.$id.'" title="Payment received"></label></td>';
                $output_html .= '<td class="registrant_center"><a href="#" class="registrant_delete_button" participant_id="'.$id.'" participant_name="'.$h($part_row['Participant_Name']).'" title="Delete this registration"><span class="icon solid fa-trash-alt"></span><span class="registrant_delete_confirm">Click again to delete</span></a></td>';
                $output_html .= '</tr>';
            }
            $output_html .= "</tbody></table></div>";
        }
	}
	$output_html .= '</div></div>';
	
    closeDB($link);	
	
	
	$output_html .= '</div>';
	$results = [$output_html];
	echo json_encode($results);
}	

/* ************************************************************************ */
/* Funciton:    RegistrantPaymentStatus                                	    */
/* Description: Where a registrant's payment stands, for the registrant     */
/*              list: wait list, no charge, paid, awaiting payment (chose a */
/*              pay-later method at checkout) or no method chosen (never    */
/*              finished checkout).                                         */
/* Returns:     [label, CSS class, Font Awesome icon]                       */
/* ************************************************************************ */
function RegistrantPaymentStatus(array $participant, $collect_payments) {
    if (!empty($participant['Participant_Wait_Listed'])) {
        return ['Wait list', 'status_wait_list', 'fa-clock'];
    }
    if (!$collect_payments || (int)$participant['Participant_Balance_Due'] <= 0) {
        return ['No charge', 'status_no_charge', 'fa-minus-circle'];
    }
    if (!empty($participant['Participant_Balance_Paid'])) {
        return ['Paid', 'status_paid', 'fa-check-circle'];
    }
    if ((string)$participant['Participant_Payment_Method'] !== '') {
        return ['Awaiting payment', 'status_awaiting', 'fa-hourglass-half'];
    }
    return ['No method chosen', 'status_no_method', 'fa-exclamation-circle'];
}

/* ************************************************************************ */
/* Funciton:    RenderRegistrantStatus                                 	    */
/* Description: The status badge and amount for the registrant list. It     */
/*              also carries the unpaid status, so ticking / unticking      */
/*              Received can switch the badge without reloading            */
/*              (UpdateRegistrantStatus in account.js).                     */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderRegistrantStatus(array $participant, $collect_payments) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    list($label, $class, $icon) = RegistrantPaymentStatus($participant, $collect_payments);
    $unpaid = $participant;
    $unpaid['Participant_Balance_Paid'] = 0;
    list($unpaid_label, $unpaid_class, $unpaid_icon) = RegistrantPaymentStatus($unpaid, $collect_payments);
    $amount = ($collect_payments && (int)$participant['Participant_Balance_Due'] > 0 && empty($participant['Participant_Wait_Listed']))
        ? '<span class="registrant_amount">$'.number_format($participant['Participant_Balance_Due'] / 100, 2).'</span>' : '';

    return '<span class="registrant_status '.$class.'" unpaid_label="'.$h($unpaid_label).'" unpaid_class="'.$unpaid_class.'" unpaid_icon="'.$unpaid_icon.'">'
          .'<span class="icon solid '.$icon.'"></span> <span class="registrant_status_label">'.$h($label).'</span></span>'.$amount;
}

/* ************************************************************************ */
/* Funciton:    RenderEmailListGeneration                         		    */
/* Description: This function returns the rendered email list generation.   */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderEmailListGeneration() {
    $event_id = $_POST['event_id'];
    $email_list = array();

    $output_html  = '<div id="main" class="wrapper" event_id="'.$event_id.'">';
    $output_html .= '<div class="inner">';
    $output_html .= '<h1 class="major">'.$event_name.' Registration Breakdown</h1>';
    $output_html .= '<div id="back_navigation_button"></div>';


    $conn = connectDB('kirbypar_nsx_meetup');
    $query = sprintf("SELECT * from `nsx_meetup_participants` WHERE `EVENT_ID`= %d", $event_id);
    $result = $conn->query($query);
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $email_list[] = $row['Participant_Email'];
            // $output_html .= $row['Participant_Email'];
        }
        $output_html .= '<h3>EMAIL List</h3>';
        $output_html .= '<div id="participant_email_comma_sep_list" class= "participant_email_comma_sep_list" >';
        $output_html .= implode(', ', $email_list);
        $output_html .= '</div>';
        // $output_html .= '<button class="primary small" onclick="CopyToClipboard()">Copy text to Clip Board</button>';
    } else {
        $output_html .= 'Something went wrong...';
    }
    closeDB($conn);

    $output_html .= '</div>';
    $output_html .= '</div>';
	$results = [$output_html];
	echo json_encode($results);
}

/* ************************************************************************ */
/* Funciton:    RenderSponsorManagementInterface                   		    */
/* Description: This function returns Sponsor Management Interface.         */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderSponsorManagementInterface() {

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
	$results = array();

    $event_id = $_POST['event_id'];
    $active_user = $_POST['active_user'];
    $active_user_id = $_POST['active_id_number'];
	$output_html = '<div id="sponsor_management_interface" class="" event_id="'.(int)$event_id.'" csrf_token="'.htmlspecialchars(app_csrf_token(), ENT_QUOTES).'">';
    $output_html .= '<h1 class="major">Sponsor Management</h1>';
    $output_html .= '<div id="back_navigation_button" class="back_navigation_button"></div>';    
    $output_html .= '<p id="sponsor_list_status"></p>';
	$output_html .= '<p>This is the sponsor management interface. Here, you will be able to edit the properties of all the existing sponsors in the le Tour de Femme
    sponsors database. These properties include everything from the basics, such as name and web link information for a sponsor, to advanced details such as sposnorship level and logos.
    Additionally, you will be able to set the active status of a current or past sponsor. Only active sponsors are displayed on the web site. Finally, you will be able
    to enter details for new sponsors as we gain them.</p>';
	
	$query = "SELECT * FROM `nsx_meetup_event_sponsors`";
	$result = $link->query($query);

	$output_html .= '<div class="admin_console_buttons">';
	// $output_html .= '<a href="#" id="submit_changes_button" table="letourdefemme_event_info" class="button disabled small" disabled>Submit Changes</a>';
	// $output_html .= '<a href="#" id="add_parameters_button" table="letourdefemme_event_info" class="button primary small">Add New Field</a>';
	$output_html .= '</div>';

    $output_html .= '<div id="sponorsors_for_current_event" class="sponorsor_management_container active">';
	if ($result->num_rows > 0) {
        $output_html .= RenderCurrentSponsorContent($event_id, $result);
	}
    $output_html .= '</div>';

	$output_html .= '</div>';
    $output_html .= '<div class="col-12 col-12-small"><a href="#" id="create_new_sponsor_button" class="button primary" event_id="'.$event_id.'">Add New Sponsor</a></div>';


    closeDB($link);
	$results[] = $output_html;
	echo json_encode($results); 

}

/* ************************************************************************ */
/* Funciton:    RenderSponsorshipManagementInterface                   		*/
/* Description: This function returns Sponsorship Level Management          */
/*              Interface.                                                  */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */ 
function RenderSponsorshipManagementInterface() {

	/***********************/
	/* Setup DB Connection */
	/***********************/
	$link = connectDB('kirbypar_nsx_meetup');
	$results = array();

    $event_id = $_POST['event_id'];
    $active_user = $_POST['active_user'];
    $active_user_id = $_POST['active_id_number'];
    $event_id = (int)$event_id;
	$output_html = '<div id="sponsorship_level_management_interface" class="" event_id="'.$event_id.'" csrf_token="'.htmlspecialchars(app_csrf_token(), ENT_QUOTES).'">';
    $output_html .= '<h1 class="major">Sponsorship Level Management</h1>';
    $output_html .= '<div id="back_navigation_button" class="back_navigation_button"></div>';    
	$output_html .= '<p>This is the sponsorship level management interface. Here you can add this event\'s sponsorship levels and edit their name, amount and logo size. 
    Only active levels can be given to sponsors - set a level to inactive to stop it being offered.</p>';
    $output_html .= '<p id="sponsorship_level_list_status"></p>';
	
	$result = PreparedQuery($link, "SELECT * FROM `nsx_meetup_sponsorship_levels` WHERE `EVENT_ID` = ? ORDER BY `LEVEL_Amount` DESC", [$event_id]);

	// $output_html .= '<div class="admin_console_buttons">';
	// // $output_html .= '<a href="#" id="submit_changes_button" table="letourdefemme_event_info" class="button disabled small" disabled>Submit Changes</a>';
	// // $output_html .= '<a href="#" id="add_parameters_button" table="letourdefemme_event_info" class="button primary small">Add New Field</a>';
	// $output_html .= '</div>';

    $output_html .= '<div id="sponsorship_levels_for_current_event" class="sponorsor_management_container active">';
	if ($result && $result->num_rows > 0) {
        $output_html .= RenderCurrentSponsorshipLevelContent($event_id, $result);
	} else {
        $output_html .= '<p>No sponsorship levels have been set up for this event yet.</p>';
	}
    $output_html .= '</div>';

    // $output_html .= '<div id="sponorsors_from_past_events" class="sponorsor_management_container">';
	// if ($result->num_rows > 0) {
    //     $output_html .= RenderPreviousSponsorContent($event_id, $active_user, $active_user_id);
	// }
    // $output_html .= '</div>';
	
	$output_html .= '</div>';
    $output_html .= '<div class="col-12 col-12-small"><a href="#" id="create_new_sponsorship_level_button" class="button primary" event_id="'.$event_id.'">Add New Sponsorship Level</a></div>';


    closeDB($link);
	$results[] = $output_html;
	echo json_encode($results); 

}
/*-------------------------------------------------------------*/
/*                                                             */
/*  OVERLORD FUNCTIONS                                         */
/*                                                             */
/*-------------------------------------------------------------*/
/* ************************************************************************ */
/* Funciton:    CreateEnterNewEventPage                     				*/
/* Description: This function returns the rendered create new event page.   */
/* Returns:     HTML                                                		*/
/* ************************************************************************ */  
function CreateEnterNewEventPage() {
    
    $output_html = '<h2>Create a New NSX Meetup Event</h2>';
	$output_html .= '<div id="main_event_creation_container"  style="margin-top:2em;" class="">';
    $output_html .= '    <div class="row gtr-25 gtr-uniform ">';

    /* Event Name */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Event Name:</span>
    Enter the name of the event you are creating. This name is what will be used in all the print on the main web page.</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <input type="text" name="event_name" id="info_text_field_event_name" value="" placeholder="Enter Event Name..." />';
    $output_html .= '       </div>';

    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';

    /* Event Directory */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Event Directory Name:</span>
    Enter the name of the event directory to be created in the file system. This name will be the last part of the URL link. No spaces or special characters are allowed.</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <input type="text" name="event_directory" id="info_text_field_event_directory" value="" placeholder="Enter Directory Name..." />';
    $output_html .= '       </div>';

    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';

    /* Start and End Date */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Starting and Ending Date:</span>
    Enter the starting and Ending dates for the event in the format YYYY-MM-DD. (Calendar selection to be added later.)</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-3 col-12-xsmall">';
    $output_html .= '           <input type="text" name="event_start_date" id="info_text_field_event_start_date" value="" placeholder="Start Date." />';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-3 col-12-xsmall">';
    $output_html .= '           <input type="text" name="event_end_date" id="info_text_field_event_end_date" value="" placeholder="End Date" />';
    $output_html .= '       </div>';  

    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';


    /* Event Directory */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Event Admins:</span>
    Enter the email address(es) for any and all registered NSX Meetup members that are going to be given administrative rights for this event. You can add
    multiple email addresses, just separate them with a comma.</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <textarea name="test_description" id="info_text_field_event_admins" ></textarea>';
    $output_html .= '       </div>';


    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';

    /* Include Sponsor Section */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Include Sponsor Section:</span>';
    $output_html .= '           Check the box if you plan to have a sponsors section and would like to have access rights to manage sponsor
    information built into the admin interface.</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <input type="checkbox" id="info_checkbox_field_activate_sponsors" key="active_sponsor" choice="no" name="activate_sponsors">';
    $output_html .= '           <label for="info_checkbox_field_activate_sponsors"></label>';	
    $output_html .= '       </div>';

    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';


    /* Event Directory */
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <p><span style="text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;">Opening Section Text:</span>
    In this section, enter the text that you want to have in place for the opening of the event page. This text is editable at a later time, so anything will do.</p>';
    $output_html .= '       </div>';    
    $output_html .= '       <div class="col-6 col-12-xsmall">';
    $output_html .= '           <textarea name="test_description" rows="10" id="info_text_field_event_opening" ></textarea>';
    $output_html .= '       </div>';
    
    /* Separator */
    $output_html .= '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:2em 0em;"></div></div>';

    $output_html .= '       <a href="#" id="create_new_event_button" class="button primary fit">Create New Event</a>';
    $output_html .= '   </div>';
    $output_html .= '</div>';
    
	$results[] = $output_html;
	echo json_encode($results);
}

/*-------------------------------------------------------------*/
/*                                                             */
/*  Back Button Functions                                      */
/*                                                             */
/*-------------------------------------------------------------*/
/* ************************************************************************ */
/* Funciton:    RenderBackButton                         				    */
/* Description: This function returns the rendered User's Registered Events.*/
/* Returns:     HTML                                                				*/
/* ************************************************************************ */  
function RenderBackButton() {
    $action = $_POST['action'];
    $output_html = '';
    switch ($action) {
        case 'findandeditparticipant':
            $output_html = '<a href="#" id="back_button_link" action="'.$action.'" event_id="'.$_POST['event_id'].'"class="button primary">Back to Find and Edit Participants</a>';
            break;
        case 'backtomaineventtoolset':
            $output_html = '<a href="#" id="back_button_link" action="'.$action.'" event_id="'.$_POST['event_id'].'"class="button primary">Back to Event Tools Page</a>';
            break;
        case 'backtofindeditregistration':
            $output_html = '<a href="#" id="back_button_link" action="'.$action.'" event_id="'.$_POST['event_id'].'"class="button primary">Back to List of Participants</a>';
            break;            
        case 'backtoeditsponsorshiplevels':
            $output_html = '<a href="#" id="back_button_link" action="'.$action.'" event_id="'.$_POST['event_id'].'"class="button primary">Back to Sponsorship Levels</a>';
            break;
        case 'backtoeditsponsors':
            $output_html = '<a href="#" id="back_button_link" action="'.$action.'" event_id="'.(int)$_POST['event_id'].'" class="button primary">Back to Sponsor Information</a>';
            break;
        default:
            $output_html = '<a href="#" id="back_button_link" action="default" class="button primary">Default Back Button</a>';
    }

    $results[] = $output_html;
    echo json_encode($results);
}

// DATABASE FUNCTIONS moved to databaseFunctions.php (required at the top of this file).
// These were previously redefined here under an ACCT_MGMT_ prefix solely to avoid
// a "cannot redeclare function" fatal when this file is included alongside
// GENERALFunctions.php (via driver1_container.php). require_once in
// databaseFunctions.php makes that workaround unnecessary.
?>
