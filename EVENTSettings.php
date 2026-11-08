<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  EVENT SETTINGS                                             */
/*                                                             */
/*  Admin tool for editing an existing event's settings        */
/*  (nsx_meetup_events). Every request must come from a        */
/*  logged-in user who administers the event (or is an         */
/*  overlord), and saves must carry the session's CSRF token.  */
/*  Unlike the older *Functions.php files, only the methods    */
/*  listed below can be called.                                */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/SessionBootstrap.php';
require_once __DIR__ . '/databaseFunctions.php';
require_once __DIR__ . '/eventImages.php';
require_once __DIR__ . '/eventAdmin.php';
require_once __DIR__ . '/paymentMethods.php';

app_session_start();

const EVENT_SETTINGS_METHODS = ['RenderEditEventSettings', 'SaveEventSettings', 'UploadFrontpageImage', 'UploadPaymentQRCode', 'RemovePaymentQRCode'];

const FRONTPAGE_IMAGE_MAX_HEIGHT = 450;           // uploads (front page image, payment QR codes) are scaled down to this height, width in proportion
const FRONTPAGE_IMAGE_MAX_BYTES  = 10 * 1048576;  // 10 MB - keep in step with .user.ini and eventsettings.js
const FRONTPAGE_IMAGE_MAX_PIXELS = 24000000;      // 24 megapixels - larger images need too much memory to resize

if (isset($_POST['method']) && in_array($_POST['method'], EVENT_SETTINGS_METHODS, true)) {
    $_POST['method']();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    // PHP throws away the whole request (method included) when it's over post_max_size
    EventAdminRespond('Fail', ['The image is too large for the server to accept (limit '.ini_get('post_max_size').'). Please choose a smaller one.']);
}

/* ************************************************************************ */
/* Function:    EventSettingsFields                                         */
/* Description: The editable event columns, grouped into the sections shown */
/*              on the form. Drives both rendering and saving.              */
/*              type: text | textarea | html | date | money | percent |     */
/*                    checkbox | emails | image                             */
/*              null_ok: store NULL when left blank (column allows NULL)    */
/*              widths: [label, input] column widths (default [6, 6])       */
/*              Each admin tool card edits its own set of fields:           */
/*              'settings' (Edit Event Settings) and 'payments' (Enable /   */
/*              Edit Event Payment Options).                                */
/* Inputs:      $tool - 'settings' or 'payments'.                           */
/* Returns:     array                                                       */
/* ************************************************************************ */
function EventSettingsFields($tool) {
    $tools = [];
    $tools['settings'] = [
        'General' => [
            'EVENT_Name'       => ['label' => 'Event Name', 'type' => 'text', 'max' => 256, 'required' => true,
                                   'help' => 'The name used throughout the site for this event.'],
            'EVENT_Start_Date' => ['label' => 'Start Date', 'type' => 'date', 'null_ok' => true,
                                   'help' => 'First day of the event.'],
            'EVENT_End_Date'   => ['label' => 'End Date', 'type' => 'date', 'null_ok' => true,
                                   'help' => 'Last day of the event. Must be on or after the start date.'],
        ],
        'Visibility & Registration' => [
            'EVENT_Display'                 => ['label' => 'Show on Front Page', 'type' => 'checkbox',
                                                'help' => 'List this event on the NSX Meetup front page.'],
            'EVENT_Sold_Out'                => ['label' => 'Sold Out', 'type' => 'checkbox',
                                                'help' => 'Mark the event as sold out on the registration page.'],
            'EVENT_Enable_Account_Creation' => ['label' => 'Offer Account Creation', 'type' => 'checkbox',
                                                'help' => 'Let people who register without logging in create an NSX Meetup account.'],
            'EVENT_Display_Sponsors'        => ['label' => 'Show Sponsors', 'type' => 'checkbox',
                                                'help' => 'Show the sponsors section on the event page.'],
        ],
        'Front Page & Event Page' => [
            'EVENT_Frontpage_Image'       => ['label' => 'Front Page Image', 'type' => 'image', 'max' => 255, 'null_ok' => true,
                                              'help' => 'Upload a new JPEG, PNG or WebP image (scaled to '.FRONTPAGE_IMAGE_MAX_HEIGHT.'px high and saved in the event\'s images folder), or enter the file name of an image already there or in the shared '.SHARED_FRONTPAGE_IMAGE_DIR.'/ folder.'],
            'EVENT_Frontpage_Description' => ['label' => 'Front Page Description', 'type' => 'html', 'null_ok' => true,
                                              'help' => 'Text shown with the event on the front page. HTML is allowed.'],
            'EVENT_Opening_Section'       => ['label' => 'Opening Section', 'type' => 'html', 'null_ok' => true,
                                              'help' => 'Opening text at the top of the event page. HTML is allowed.'],
            'EVENT_Confirmation_Special_Text' => ['label' => 'Confirmation Special Text', 'type' => 'text', 'max' => 256, 'null_ok' => true,
                                              'help' => 'Extra text included on registration confirmations.'],
        ],
        'Contacts' => [
            'EVENT_Primary_Email_Contact'   => ['label' => 'Primary Contact Email(s)', 'type' => 'emails', 'max' => 512, 'null_ok' => true,
                                                'help' => 'Where the event contact form sends messages. Separate multiple addresses with commas.'],
            'EVENT_Secondary_Email_Contact' => ['label' => 'Secondary Contact Email(s)', 'type' => 'emails', 'null_ok' => true,
                                                'help' => 'Copied on contact form messages. Separate multiple addresses with commas.'],
        ],
    ];
    $tools['payments'] = [
        'Fees & Payments' => [
            'EVENT_Collect_Payments'      => ['label' => 'Collect Payments', 'type' => 'checkbox', 'widths' => [8, 4],
                                              'after' => 'RenderPaymentMethodOptions',
                                              'help' => 'Leave this off for a free event - registrants won\'t be asked to pay. '
                                                      .'When it\'s on, at least one registration type must be enabled. If none is when you save, the event\'s first type '
                                                      .'(every event starts with a disabled "'.DEFAULT_REGTYPE_NAME.'" type at $0.00) is enabled for you. '
                                                      .'Then go to Add / Edit / Delete Registration Types to set its cost, and add more types if you need them.'],
            'EVENT_Processing_Percentage' => ['label' => 'Processing Fee (%)', 'type' => 'percent', 'null_ok' => true,
                                              'help' => 'Service fee added to the total due, as a percentage. Leave blank for none.'],
        ],
    ];

    /* Drawn by RenderPaymentMethodOptions (under Collect Payments) instead of in the list */
    $methods = [];
    foreach (PAYMENT_METHODS as $method => $info) {
        $methods[$info['column']] = ['label' => $info['name'], 'type' => 'checkbox', 'rendered_by' => 'payment_methods'];
    }
    $methods['EVENT_Stripe_Public_Key'] = ['label' => 'Stripe Publishable Key', 'type' => 'text', 'max' => 128, 'rendered_by' => 'payment_methods',
                                           'drawer' => 'stripe',
                                           'help' => 'The publishable key (starts with pk_). Never enter the secret key (sk_) here.'];
    foreach (HANDLE_PAYMENT_METHODS as $method => $info) {
        $name = PAYMENT_METHODS[$method]['name'];
        $methods[$info['handle_column']] = ['label' => $info['handle_label'], 'type' => 'handle', 'payment_method' => $method,
                                            'max' => 100, 'null_ok' => true,  /* room for a pasted link - HandleFromInput() trims it */ 'rendered_by' => 'payment_methods',
                                            'drawer' => $method, 'placeholder' => $info['handle_example'],
                                            'help' => 'The '.$name.' account registrants pay, e.g. '.$info['handle_example'].' ('.$info['handle_rule'].').',
                                            'how_to' => ['How do I find my '.$name.' username?', $info['handle_how_to']]];
        $methods[$info['description_column']] = ['label' => $name.' Instructions', 'type' => $info['description_html'] ? 'html' : 'textarea',
                                                 'max' => 4000, 'null_ok' => true, 'rendered_by' => 'payment_methods',
                                                 'drawer' => $method, 'placeholder' => $info['default_description'],
                                                 'help' => 'Shown with the '.$name.' account and amount due on the checkout page - e.g. what to put in the payment note. '
                                                          .($info['description_html'] ? 'HTML is allowed.' : 'Plain text.').' Leave blank to use the instructions shown in the box.'];
    }
    $tools['payments']['Payment Methods'] = $methods;

    return $tools[$tool];
}

/* ************************************************************************ */
/* Function:    EventSettingsTool                                           */
/* Description: The admin tool the request is for, from the posted 'tool'   */
/*              value. Ends the request if it isn't one of the tools.       */
/* Returns:     array - ['id', 'title', 'saved'] for the tool               */
/* ************************************************************************ */
function EventSettingsTool() {
    $tools = [
        'settings' => ['title' => 'Settings',        'saved' => 'Settings saved.'],
        'payments' => ['title' => 'Payment Options', 'saved' => 'Payment options saved.'],
    ];
    $tool = $_POST['tool'] ?? 'settings';
    if (!is_string($tool) || !isset($tools[$tool])) {
        EventAdminRespond('Fail', ['Unknown event tool.']);
    }
    return ['id' => $tool] + $tools[$tool];
}

/* ************************************************************************ */
/* Function:    EventSettingsDisplayValue                                   */
/* Description: Converts a stored column value to what the form shows.      */
/*              Email columns are stored as JSON lists.                     */
/* Returns:     string                                                      */
/* ************************************************************************ */
function EventSettingsDisplayValue($field, $value) {
    if ($value === null) {
        return '';
    }
    if ($field['type'] === 'emails') {
        $list = json_decode($value, true);
        return is_array($list) ? implode(', ', $list) : $value;
    }
    return (string)$value;
}

/* ************************************************************************ */
/* Function:    RenderPaymentMethodOptions                                  */
/* Description: The Payment Methods section under Collect Payments: an      */
/*              on/off switch for each payment method (saved with the       */
/*              form) that opens a drawer for its options. Only shown while */
/*              Collect Payments is checked (eventsettings.js shows and     */
/*              hides it). Fields marked 'drawer' => <method> in            */
/*              EventSettingsFields go in that method's drawer, plus a QR   */
/*              code upload for HANDLE_PAYMENT_METHODS; the other drawers   */
/*              are placeholders for now.                                   */
/* Inputs:      $event - the event row, $separator - the row divider HTML.  */
/* Returns:     HTML (a full-width column of the settings grid)             */
/* ************************************************************************ */
function RenderPaymentMethodOptions($event, $separator) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $fields = EventSettingsFields('payments')['Payment Methods'];
    $hidden = empty($event['EVENT_Collect_Payments']) ? ' style="display:none;"' : '';

    $output_html  = '<div id="payment_methods" class="col-12 payment_methods"'.$hidden.'>';
    $output_html .= '   <p><span class="payment_methods_label">Payment Methods:</span> Turn on each way registrants can pay, then set up its options. Registrants see a section on the checkout page for each one that\'s on.</p>';
    foreach (PAYMENT_METHODS as $method => $info) {
        $toggle = 'event_setting_'.$info['column'];
        $drawer = 'payment_method_'.$method.'_drawer';
        $on = !empty($event[$info['column']]);
        $output_html .= '   <div class="payment_method" payment_method="'.$method.'">';
        $output_html .= '       <div class="payment_method_header">';
        $output_html .= '           <span class="payment_method_name">'.$h($info['name']).'</span>';
        $output_html .= '           <input type="checkbox" id="'.$toggle.'" db_key="'.$info['column'].'" class="payment_method_toggle" drawer="'.$drawer.'" aria-controls="'.$drawer.'" aria-expanded="'.($on ? 'true' : 'false').'"'.($on ? ' checked' : '').'>';
        $output_html .= '           <label for="'.$toggle.'" class="payment_method_switch" title="Turn '.$h($info['name']).' on or off"><span class="payment_method_off">Off</span><span class="payment_method_on">On</span></label>';
        $output_html .= '       </div>';
        $output_html .= '       <div id="'.$drawer.'" class="payment_method_drawer"'.($on ? '' : ' style="display:none;"').'>';
        $drawer_fields = 0;
        foreach ($fields as $column => $field) {
            if (($field['drawer'] ?? '') !== $method) {
                continue;
            }
            $id = 'event_setting_'.$column;
            $max = isset($field['max']) ? ' maxlength="'.(int)$field['max'].'"' : '';
            $placeholder = isset($field['placeholder']) ? ' placeholder="'.$h($field['placeholder']).'"' : '';
            $value = (string)($event[$column] ?? '');
            if ($field['type'] === 'handle' && $value !== '') {
                $value = HANDLE_PAYMENT_METHODS[$field['payment_method']]['handle_prefix'].$value; // stored without it
            }
            $output_html .= '           <label for="'.$id.'" class="payment_method_field_label">'.$h($field['label']).'</label>';
            if ($field['type'] === 'textarea' || $field['type'] === 'html') {
                $output_html .= '           <textarea id="'.$id.'" db_key="'.$column.'" rows="5"'.$max.$placeholder.'>'.$h($value).'</textarea>';
            } else {
                $output_html .= '           <input type="text" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'"'.$max.$placeholder.' />';
            }
            $output_html .= '           <p class="payment_method_field_help">'.$h($field['help']).'</p>';
            if ($field['type'] === 'handle') {
                // Opens the checkout page's link (HandlePaymentLink) for what's typed - see OpenPaymentTestLink in eventsettings.js
                $output_html .= '           <div class="payment_test_link_row"><a href="#" class="button small payment_test_link" payment_method="'.$h($field['payment_method']).'" handle_input="'.$id.'" note="'.$h($event['EVENT_Name'].' - Test').'">Test Link</a>';
                $output_html .= '           <span class="payment_test_link_status">Opens your '.$h(PAYMENT_METHODS[$field['payment_method']]['name']).' link in a new window with a $1.00 test amount - nothing is sent unless you confirm it there.</span></div>';
            }
            if (isset($field['how_to'])) {
                $output_html .= '           <details class="payment_method_how_to"><summary>'.$h($field['how_to'][0]).'</summary><p>'.$h($field['how_to'][1]).'</p></details>';
            }
            $drawer_fields++;
        }
        if (isset(HANDLE_PAYMENT_METHODS[$method])) {
            $output_html .= RenderPaymentQRCodeField($event, $method);
            $drawer_fields++;
        }
        if (!$drawer_fields) {
            $output_html .= '           <p>Here is where you will configure the '.$h($info['name']).' options.</p>';
        }
        $output_html .= '       </div>';
        $output_html .= '   </div>';
    }
    $output_html .= '</div>';
    $output_html .= str_replace('class="col-12 col-12-xsmall"', 'class="col-12 col-12-xsmall payment_methods_separator"'.$hidden, $separator);
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderPaymentQRCodeField                                    */
/* Description: A HANDLE_PAYMENT_METHODS method's QR code in its drawer: a  */
/*              preview, Upload (saved straight away by                     */
/*              UploadPaymentQRCode) and Remove. Not part of the form's     */
/*              save - it has no db_key.                                    */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderPaymentQRCodeField(array $event, $method) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $name = PAYMENT_METHODS[$method]['name'];
    $app = HANDLE_PAYMENT_METHODS[$method]['app_name'];
    $path = PaymentQRCodePath($event, $method);
    $id = $method.'_qr';

    $output_html  = '<label class="payment_method_field_label">'.$h($name).' QR Code</label>';
    $output_html .= '<div class="frontpage_image_preview payment_qr_preview">';
    $output_html .= '<img id="'.$id.'_preview" src="'.$h($path).'" alt="'.$h($name).' QR code"'.($path === '' ? ' style="display:none;"' : '').' />';
    $output_html .= '<span id="'.$id.'_preview_none"'.($path !== '' ? ' style="display:none;"' : '').'>No QR code uploaded</span>';
    $output_html .= '</div>';
    $output_html .= '<input type="file" id="'.$id.'_upload" class="payment_qr_upload" payment_method="'.$method.'" accept="image/jpeg,image/png,image/webp" style="display:none;" />';
    $output_html .= '<div class="row gtr-0 frontpage_image_controls">';
    $output_html .= '<div class="col-6 col-12-small"><label for="'.$id.'_upload" id="'.$id.'_upload_button" class="button small fit">Upload QR Code</label></div>';
    $output_html .= '<div class="col-6 col-12-small"><a href="#" id="'.$id.'_remove_button" class="button small fit payment_qr_remove_button" payment_method="'.$method.'"'.($path === '' ? ' style="display:none;"' : '').'>Remove QR Code</a></div>';
    $output_html .= '</div>';
    $output_html .= '<div id="'.$id.'_upload_status" class="frontpage_upload_status"></div>';
    $output_html .= '<p class="payment_method_field_help">Optional. Download your QR code from '.$h($app).' and upload it here (JPEG, PNG or WebP) - it\'s shown in the '.$h($name).' section of the checkout page, scaled to '.FRONTPAGE_IMAGE_MAX_HEIGHT.'px high. It\'s saved as soon as it uploads.</p>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderEditEventSettings                                     */
/* Description: Returns the Edit Event Settings form for the posted event.  */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderEditEventSettings() {
    $tool = EventSettingsTool();
    list($pdo, $event) = EventAdminLoadEvent();
    $pdo = null;

    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $label_style = 'text-transform:uppercase;font-size: 1.25em;padding-right: 0.25em;color: #FFCC00;';
    $separator = '       <div class="col-12 col-12-xsmall"><div style="border-bottom: 1px solid white; margin:1em 0em;"></div></div>';

    $output_html  = '<h2>'.$h($event['EVENT_Name']).' '.$h($tool['title']).'</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<form id="event_settings_form" class="event_settings_form" event_id="'.(int)$event['EVENT_ID'].'" tool="'.$h($tool['id']).'" csrf_token="'.$h(app_csrf_token()).'" onsubmit="return false;">';

    /* Read-only details */
    $admins = json_decode((string)$event['EVENT_Admins'], true);
    $output_html .= '<h3>Event Details</h3>';
    $output_html .= '<p>Event ID: <b>'.(int)$event['EVENT_ID'].'</b><br>';
    $output_html .= 'Directory: <b>events/'.$h($event['EVENT_Directory']).'/</b><br>';
    $output_html .= 'Admins: <b>'.(is_array($admins) && $admins ? $h(implode(', ', $admins)) : 'None').'</b></p>';

    foreach (EventSettingsFields($tool['id']) as $section => $fields) {
        $fields = array_filter($fields, function ($field) { return empty($field['rendered_by']); });
        if (!$fields) {
            continue;
        }
        $output_html .= '<h3 style="margin-top:2em;">'.$h($section).'</h3>';
        $output_html .= '<div class="row gtr-25 gtr-uniform">';
        foreach ($fields as $column => $field) {
            $id = 'event_setting_'.$column;
            $value = EventSettingsDisplayValue($field, $event[$column]);

            list($label_width, $input_width) = $field['widths'] ?? [6, 6];
            $output_html .= '       <div class="col-'.$label_width.' col-12-xsmall">';
            $output_html .= '           <p><span style="'.$label_style.'">'.$h($field['label']).':</span> '.$h($field['help']).'</p>';
            $output_html .= '       </div>';
            $output_html .= '       <div class="col-'.$input_width.' col-12-xsmall">';
            switch ($field['type']) {
                case 'checkbox':
                    $output_html .= '<input type="checkbox" id="'.$id.'" db_key="'.$column.'"'.($value ? ' checked' : '').'>';
                    $output_html .= '<label for="'.$id.'">Yes</label>';
                    break;
                case 'html':
                case 'textarea':
                    $output_html .= '<textarea id="'.$id.'" db_key="'.$column.'" rows="6">'.$h($value).'</textarea>';
                    break;
                case 'emails':
                    $output_html .= '<textarea id="'.$id.'" db_key="'.$column.'" rows="2" placeholder="name@example.com, other@example.com">'.$h($value).'</textarea>';
                    break;
                case 'date':
                    $output_html .= '<input type="date" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" />';
                    break;
                case 'money':
                    $output_html .= '<input type="number" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" min="0" step="0.01" />';
                    break;
                case 'percent':
                    $output_html .= '<input type="number" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" min="0" max="100" step="0.01" />';
                    break;
                case 'image':
                    $output_html .= '<div class="frontpage_image_preview">';
                    $output_html .= '<img id="event_setting_frontpage_preview" src="'.$h(EventFrontpageImageURL($event['EVENT_Directory'], $value)).'" alt=""'.($value === '' ? ' style="display:none;"' : '');
                    $output_html .= ' event_image_dir="'.$h(EventImagesDirectory($event['EVENT_Directory'])).'" shared_image_dir="'.$h(SHARED_FRONTPAGE_IMAGE_DIR).'" />';
                    $output_html .= '<span id="event_setting_frontpage_preview_none"'.($value !== '' ? ' style="display:none;"' : '').'>No image set</span>';
                    $output_html .= '</div>';
                    $output_html .= '<input type="file" id="event_setting_frontpage_upload" accept="image/jpeg,image/png,image/webp" style="display:none;" />';
                    $output_html .= '<div class="row gtr-0 frontpage_image_controls">';
                    $output_html .= '<div class="col-6 col-12-small"><label for="event_setting_frontpage_upload" id="event_setting_frontpage_upload_button" class="button small fit">Upload New Image</label></div>';
                    $output_html .= '<div class="col-6 col-12-small"><input type="text" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'" maxlength="'.(int)$field['max'].'" /></div>';
                    $output_html .= '</div>';
                    $output_html .= '<div id="event_setting_frontpage_upload_status" class="frontpage_upload_status"></div>';
                    break;
                default: // text
                    $max = isset($field['max']) ? ' maxlength="'.(int)$field['max'].'"' : '';
                    $output_html .= '<input type="text" id="'.$id.'" db_key="'.$column.'" value="'.$h($value).'"'.$max.' />';
            }
            $output_html .= '       </div>';
            $output_html .= $separator;
            if (!empty($field['after'])) {
                $output_html .= $field['after']($event, $separator);
            }
        }
        $output_html .= '</div>';
    }

    $output_html .= '<div id="event_settings_messages" class="event_settings_messages"></div>';
    $output_html .= '<a href="#" id="save_event_settings_button" class="button primary fit">Save '.$h($tool['title']).'</a>';
    $output_html .= '</form>';

    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    SaveEventSettings                                           */
/* Description: Validates the posted settings (settings[COLUMN] = value)    */
/*              and updates the event. Nothing is saved if any value is     */
/*              invalid.                                                    */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SaveEventSettings() {
    $tool = EventSettingsTool();
    list($pdo, $event) = EventAdminLoadEvent();

    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }

    $posted = (isset($_POST['settings']) && is_array($_POST['settings'])) ? $_POST['settings'] : [];
    $values = [];
    $errors = [];

    foreach (EventSettingsFields($tool['id']) as $fields) {
        foreach ($fields as $column => $field) {
            $raw = isset($posted[$column]) && is_string($posted[$column]) ? trim($posted[$column]) : '';
            $label = $field['label'];
            $null_ok = !empty($field['null_ok']);

            if ($raw === '' && $field['type'] !== 'checkbox') {
                if (!empty($field['required'])) {
                    $errors[] = $label.' is required.';
                }
                $values[$column] = $null_ok ? null : (in_array($field['type'], ['money', 'percent'], true) ? 0 : '');
                continue;
            }

            switch ($field['type']) {
                case 'checkbox':
                    $values[$column] = ($raw === '1') ? 1 : 0;
                    break;

                case 'date':
                    $date = DateTime::createFromFormat('!Y-m-d', $raw);
                    if (!$date || $date->format('Y-m-d') !== $raw) {
                        $errors[] = $label.' must be a valid date (YYYY-MM-DD).';
                    }
                    $values[$column] = $raw;
                    break;

                case 'money':
                case 'percent':
                    if (!is_numeric($raw) || $raw < 0 || ($field['type'] === 'percent' && $raw > 100)) {
                        $errors[] = $label.($field['type'] === 'percent' ? ' must be a number from 0 to 100.' : ' must be a number of 0 or more.');
                    }
                    $values[$column] = round((float)$raw, 2);
                    break;

                case 'emails':
                    $list = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($list as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = $label.': "'.$email.'" is not a valid email address.';
                        }
                    }
                    $values[$column] = json_encode(array_values(array_unique($list)));
                    if (isset($field['max']) && strlen($values[$column]) > $field['max']) {
                        $errors[] = $label.' has too many addresses to store.';
                    }
                    break;

                case 'image':
                    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.(png|jpe?g|gif|webp)$/i', $raw)) {
                        $errors[] = $label.' must be just an image file name, e.g. my_event.png.';
                    }
                    $values[$column] = $raw;
                    break;

                case 'handle': // a HANDLE_PAYMENT_METHODS username - stored without its @ / link
                    $info = HANDLE_PAYMENT_METHODS[$field['payment_method']];
                    $values[$column] = HandleFromInput($raw);
                    if (!preg_match($info['handle_pattern'], $values[$column])) {
                        $errors[] = $label.' must be '.$info['handle_rule'].', e.g. '.$info['handle_example'].'.';
                    }
                    break;

                default: // text, textarea, html
                    if (isset($field['max']) && mb_strlen($raw) > $field['max']) {
                        $errors[] = $label.' must be '.$field['max'].' characters or fewer.';
                    }
                    $values[$column] = $raw;
            }
        }
    }

    /* Rules that involve more than one field */
    if ($tool['id'] === 'settings') {
        if (!empty($values['EVENT_Start_Date']) && !empty($values['EVENT_End_Date']) && $values['EVENT_End_Date'] < $values['EVENT_Start_Date']) {
            $errors[] = 'End Date must be on or after the Start Date.';
        }
    }
    if ($tool['id'] === 'payments') {
        $stripe_key = $values['EVENT_Stripe_Public_Key'];
        if ($stripe_key !== '' && strpos($stripe_key, 'pk_') !== 0) {
            $errors[] = (strpos($stripe_key, 'sk_') === 0 || strpos($stripe_key, 'rk_') === 0)
                ? 'That is a Stripe SECRET key. Only the publishable key (pk_...) belongs here - the secret key must never be put on a web page.'
                : 'Stripe Publishable Key must start with pk_.';
        }
        if ($values['EVENT_Collect_Payments'] && $values['EVENT_Enable_Stripe_Processing'] && $stripe_key === '') {
            $errors[] = 'Stripe needs a Stripe Publishable Key - enter it under Stripe, or turn Stripe off.';
        }

        foreach (HANDLE_PAYMENT_METHODS as $method => $info) {
            $name = PAYMENT_METHODS[$method]['name'];
            if ($values['EVENT_Collect_Payments'] && $values[PAYMENT_METHODS[$method]['column']] && $values[$info['handle_column']] === null) {
                $errors[] = $name.' needs a '.$info['handle_label'].' - enter it under '.$name.', or turn '.$name.' off.';
            }
        }
    }

    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    /* Column names come from EventSettingsFields(), never from the request. */
    $assignments = [];
    foreach (array_keys($values) as $column) {
        $assignments[] = '`'.$column.'` = :'.$column;
    }
    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET '.implode(', ', $assignments).' WHERE EVENT_ID = :event_id');
    foreach ($values as $column => $value) {
        $stmt->bindValue(':'.$column, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    }
    $stmt->bindValue(':event_id', (int)$event['EVENT_ID'], PDO::PARAM_INT);

    $enabled_type = null;
    try {
        $pdo->beginTransaction();
        $stmt->execute();
        // Collecting payments means registrants must have a registration type to choose
        if (!empty($values['EVENT_Collect_Payments'])) {
            $enabled_type = EnsureEnabledRegistrationType($pdo, (int)$event['EVENT_ID']);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('SaveEventSettings failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The settings could not be saved. Please try again.']);
    }

    $message = $tool['saved'];
    if ($enabled_type !== null) {
        $message .= ' Collect Payments needs an enabled registration type, so "'.$enabled_type.'" was enabled. '
                  .'Check its cost in Add / Edit / Delete Registration Types.';
    }
    EventAdminRespond('Pass', $message);
}

/* ************************************************************************ */
/* Function:    UploadFrontpageImage                                        */
/* Description: Saves an uploaded front page image for the posted event     */
/*              into its events/<EVENT_Directory>/images/ folder, scaled to */
/*              FRONTPAGE_IMAGE_MAX_HEIGHT, and makes it the event's front  */
/*              page image straight away. The previously uploaded image is  */
/*              deleted.                                                    */
/* Returns:     JSON ['Pass', ['filename' => ..., 'url' => ...]] or         */
/*              ['Fail', [messages]]                                        */
/* ************************************************************************ */
function UploadFrontpageImage() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }

    // A new name for every upload, so browsers never show a cached older image
    $saved = ReceiveEventImageUpload($event, 'frontpage_image', 'frontpage_'.date('Ymd_His'));
    $filename = $saved['filename'];

    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET EVENT_Frontpage_Image = ? WHERE EVENT_ID = ?');
    if (!$stmt->execute([$filename, $event['EVENT_ID']])) {
        error_log('UploadFrontpageImage: database update failed for event '.$event['EVENT_ID']);
        EventAdminRespond('Fail', ['The image was uploaded but could not be set on the event. Please try again.']);
    }
    $stmt = null;
    $pdo = null;

    // Remove the image this one replaced - only an earlier upload (frontpage_<date>_<time>),
    // never a hand-placed image or one from the shared folder
    $dir = __DIR__.'/'.$saved['event_dir'];
    $previous = basename((string)$event['EVENT_Frontpage_Image']);
    if ($previous !== $filename && preg_match('/^frontpage_\d{8}_\d{6}\.(jpg|png)$/', $previous) && is_file($dir.'/'.$previous)) {
        if (!unlink($dir.'/'.$previous)) {
            error_log('UploadFrontpageImage: could not delete the previous image '.$dir.'/'.$previous);
        }
    }

    EventAdminRespond('Pass', ['filename' => $filename, 'url' => $saved['event_dir'].'/'.$filename]);
}

/* ************************************************************************ */
/* Function:    PostedQRPaymentMethod                                       */
/* Description: The posted payment_method, if it's one with a QR code       */
/*              (HANDLE_PAYMENT_METHODS). Ends the request otherwise.       */
/* ************************************************************************ */
function PostedQRPaymentMethod() {
    $method = $_POST['payment_method'] ?? '';
    if (!is_string($method) || !isset(HANDLE_PAYMENT_METHODS[$method])) {
        EventAdminRespond('Fail', ['Unknown payment method.']);
    }
    return $method;
}

/* ************************************************************************ */
/* Function:    UploadPaymentQRCode                                         */
/* Description: Saves an uploaded QR code image for the posted payment      */
/*              method (resized like the front page image) in the event's   */
/*              images folder and stores its site-relative path in the      */
/*              method's qr_column, replacing any earlier upload.           */
/* Returns:     JSON ['Pass', ['path']] or ['Fail', [messages]]             */
/* ************************************************************************ */
function UploadPaymentQRCode() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }
    $method = PostedQRPaymentMethod();
    $column = HANDLE_PAYMENT_METHODS[$method]['qr_column'];

    $saved = ReceiveEventImageUpload($event, 'qr_code', $method.'_qr_'.date('Ymd_His'));
    $path = $saved['event_dir'].'/'.$saved['filename'];

    /* Column names come from HANDLE_PAYMENT_METHODS, never from the request. */
    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET `'.$column.'` = ? WHERE EVENT_ID = ?');
    if (!$stmt->execute([$path, $event['EVENT_ID']])) {
        error_log('UploadPaymentQRCode: database update failed for '.$method.', event '.$event['EVENT_ID']);
        EventAdminRespond('Fail', ['The QR code was uploaded but could not be set on the event. Please try again.']);
    }
    DeleteReplacedPaymentQRCode($event, $method, $path);

    EventAdminRespond('Pass', ['path' => $path]);
}

/* ************************************************************************ */
/* Function:    RemovePaymentQRCode                                         */
/* Description: Clears the posted payment method's QR code and deletes the  */
/*              image.                                                      */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function RemovePaymentQRCode() {
    list($pdo, $event) = EventAdminLoadEvent();
    if (!app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This form has expired. Reload the page and try again.']);
    }
    $method = PostedQRPaymentMethod();
    $column = HANDLE_PAYMENT_METHODS[$method]['qr_column'];

    $stmt = $pdo->prepare('UPDATE nsx_meetup_events SET `'.$column.'` = NULL WHERE EVENT_ID = ?');
    if (!$stmt->execute([$event['EVENT_ID']])) {
        EventAdminRespond('Fail', ['The QR code could not be removed. Please try again.']);
    }
    DeleteReplacedPaymentQRCode($event, $method, '');

    EventAdminRespond('Pass', PAYMENT_METHODS[$method]['name'].' QR code removed.');
}

/* ************************************************************************ */
/* Function:    DeleteReplacedPaymentQRCode                                 */
/* Description: Deletes the event's previous QR code image for $method      */
/*              unless it is $keep - only an earlier upload in the event's  */
/*              images folder (<method>_qr_<date>_<time>), never another    */
/*              file.                                                       */
/* ************************************************************************ */
function DeleteReplacedPaymentQRCode(array $event, $method, $keep) {
    $previous = (string)$event[HANDLE_PAYMENT_METHODS[$method]['qr_column']];
    $event_dir = EventImagesDirectory($event['EVENT_Directory']);
    if ($previous === '' || $previous === $keep || $event_dir === ''
        || !preg_match('#^'.preg_quote($event_dir.'/'.$method, '#').'_qr_\d{8}_\d{6}\.(jpg|png)$#', $previous)
        || !is_file(__DIR__.'/'.$previous)) {
        return;
    }
    if (!unlink(__DIR__.'/'.$previous)) {
        error_log('DeleteReplacedPaymentQRCode: could not delete '.$previous);
    }
}

/* ************************************************************************ */
/* Function:    ReceiveEventImageUpload                                     */
/* Description: Checks the uploaded image in $_FILES[$file_field], scales   */
/*              it to FRONTPAGE_IMAGE_MAX_HEIGHT and saves it in the        */
/*              event's images folder (events/<dir>/images/) as $basename   */
/*              .jpg or .png. Ends the request with a 'Fail' response if    */
/*              anything goes wrong.                                        */
/* Returns:     ['event_dir' => site-relative folder, 'filename' => name]   */
/* ************************************************************************ */
function ReceiveEventImageUpload(array $event, $file_field, $basename) {
    if (!function_exists('imagecreatetruecolor')) {
        error_log('ReceiveEventImageUpload: the GD extension is not available, so images cannot be resized');
        EventAdminRespond('Fail', ['Image uploads are not available on this server (the PHP GD extension is missing).']);
    }

    $upload = CheckFrontpageImageUpload($_FILES[$file_field] ?? null);
    if ($upload['error'] !== '') {
        EventAdminRespond('Fail', [$upload['error']]);
    }

    $event_dir = EventImagesDirectory($event['EVENT_Directory']);
    if ($event_dir === '' || !is_dir(__DIR__.'/events/'.$event['EVENT_Directory'])) {
        EventAdminRespond('Fail', ['This event\'s folder (events/'.$event['EVENT_Directory'].'/) could not be found, so the image has nowhere to go.']);
    }
    $dir = __DIR__.'/'.$event_dir;
    if (!is_dir($dir) && !mkdir($dir, 0755)) {
        error_log('ReceiveEventImageUpload: could not create '.$dir);
        EventAdminRespond('Fail', ['The image could not be saved. Please try again.']);
    }

    $filename = SaveFrontpageImage($upload['tmp'], $upload['type'], $dir, $basename);
    if ($filename === '') {
        error_log('ReceiveEventImageUpload: saving '.$file_field.' failed for event '.$event['EVENT_ID']);
        EventAdminRespond('Fail', ['The image could not be saved. Please try again.']);
    }
    return ['event_dir' => $event_dir, 'filename' => $filename];
}

/* ************************************************************************ */
/* Function:    CheckFrontpageImageUpload                                   */
/* Description: Checks an image upload from $_FILES. The file's contents    */
/*              decide its type - the browser-supplied name and type are    */
/*              ignored. (Adapted from ContactBook's check_book_photo_upload)*/
/* Inputs:      $file - the $_FILES entry, or null.                         */
/* Returns:     ['error' => string, 'tmp' => string, 'type' => IMAGETYPE_*] */
/* ************************************************************************ */
function CheckFrontpageImageUpload($file) {
    $none = ['error' => '', 'tmp' => '', 'type' => 0];
    $max_mb = FRONTPAGE_IMAGE_MAX_BYTES / 1048576;

    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['error' => 'Please choose an image to upload.'] + $none;
    }
    if (is_array($file['error'])) {
        return ['error' => 'Please choose a single image.'] + $none;
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
        || $file['size'] > FRONTPAGE_IMAGE_MAX_BYTES) {
        return ['error' => "The image is too large. Please choose one under {$max_mb} MB."] + $none;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        error_log('CheckFrontpageImageUpload: upload failed with error code '.$file['error']);
        return ['error' => 'The image could not be uploaded. Please try again.'] + $none;
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return ['error' => 'The image must be a JPEG, PNG or WebP file.'] + $none;
    }
    if ($info[0] * $info[1] > FRONTPAGE_IMAGE_MAX_PIXELS) {
        return ['error' => 'The image\'s dimensions are too large. Please choose a smaller image.'] + $none;
    }

    return ['error' => '', 'tmp' => $file['tmp_name'], 'type' => $info[2]];
}

/* ************************************************************************ */
/* Function:    SaveFrontpageImage                                          */
/* Description: Re-encodes an uploaded image into $dir as <$basename>.jpg   */
/*              (or .png for PNGs, to keep transparency), scaled down to    */
/*              FRONTPAGE_IMAGE_MAX_HEIGHT with the width kept in           */
/*              proportion. Smaller images are not enlarged. Re-encoding    */
/*              also turns phone photos the right way up, strips hidden     */
/*              data such as GPS location, and drops anything that isn't    */
/*              image data. (Adapted from ContactBook's save_book_photo)    */
/* Inputs:      $tmp - uploaded temp file. $type - IMAGETYPE_*.             */
/*              $dir - the full directory path to save in.                  */
/*              $basename - the filename without extension.                 */
/* Returns:     The saved filename, or '' on failure.                       */
/* ************************************************************************ */
function SaveFrontpageImage($tmp, $type, $dir, $basename) {
    switch ($type) {
        case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($tmp); break;
        case IMAGETYPE_PNG:  $image = @imagecreatefrompng($tmp);  break;
        case IMAGETYPE_WEBP: $image = @imagecreatefromwebp($tmp); break;
        default:             $image = false;
    }
    if ($image === false) {
        return '';
    }

    // Phones save photos sideways and record the rotation in EXIF - apply it.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (@exif_read_data($tmp))['Orientation'] ?? 1;
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }
        $angle = [3 => 180, 4 => 180, 5 => 270, 6 => 270, 7 => 90, 8 => 90][$orientation] ?? 0;
        if ($angle !== 0) {
            $rotated = imagerotate($image, $angle, 0);
            imagedestroy($image);
            $image = $rotated;
        }
    }

    $width  = imagesx($image);
    $height = imagesy($image);
    if ($height > FRONTPAGE_IMAGE_MAX_HEIGHT) {
        $new_height = FRONTPAGE_IMAGE_MAX_HEIGHT;
        $new_width  = max(1, (int) round($width * FRONTPAGE_IMAGE_MAX_HEIGHT / $height));
        $resized = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    if ($type === IMAGETYPE_PNG) {
        $filename = $basename.'.png';
        imagesavealpha($image, true);
        $saved = imagepng($image, $dir.'/'.$filename, 6);
    } else {
        $filename = $basename.'.jpg';
        $saved = imagejpeg($image, $dir.'/'.$filename, 85);
    }
    imagedestroy($image);

    return $saved ? $filename : '';
}

