<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  REGISTRATION TYPES                                         */
/*                                                             */
/*  Admin tool for adding, editing and deleting an event's     */
/*  registration types (nsx_meetup_registration_types) -       */
/*  the choices registrants pick from when they register.      */
/*  Every request must come from a logged-in admin of the      */
/*  event (or an overlord), and changes must carry the         */
/*  session's CSRF token. Only the methods listed below can    */
/*  be called.                                                 */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/eventAdmin.php';

app_session_start();

const REGTYPE_METHODS = ['RenderRegistrationTypes', 'AddRegistrationType', 'SaveRegistrationType', 'SetRegistrationTypeEnabled', 'DeleteRegistrationType'];

const REGTYPE_NAME_MAX = 64;       // REGTYPE_Name is varchar(64)
const REGTYPE_COST_MAX = 999999.99;  // REGTYPE_Cost is a float, in dollars and cents
const REGTYPE_DESCRIPTION_PREVIEW = 100;  // characters of the description shown in the list

if (isset($_POST['method']) && in_array($_POST['method'], REGTYPE_METHODS, true)) {
    $_POST['method']();
}

/* ************************************************************************ */
/* Function:    RegTypeLoadEvent                                            */
/* Description: Loads and authorizes the posted event like                  */
/*              EventAdminLoadEvent, and for changes also checks the CSRF   */
/*              token.                                                      */
/* Inputs:      $is_change - true for requests that change data.            */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function RegTypeLoadEvent($is_change) {
    list($pdo, $event) = EventAdminLoadEvent();
    if ($is_change && !app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    return [$pdo, $event];
}

/* ************************************************************************ */
/* Function:    RegTypePostedID                                             */
/* Description: The posted registration type id, if it belongs to the      */
/*              event. Ends the request otherwise.                          */
/* Returns:     int                                                         */
/* ************************************************************************ */
function RegTypePostedID(PDO $pdo, $event_id) {
    $regtype_id = filter_var($_POST['regtype_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($regtype_id !== false) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
        $stmt->execute([$regtype_id, $event_id]);
        if ($stmt->fetchColumn() > 0) {
            return $regtype_id;
        }
    }
    EventAdminRespond('Fail', ['That registration type could not be found. Reload the page and try again.']);
}

/* ************************************************************************ */
/* Function:    RegTypePostedValues                                         */
/* Description: Validates the posted name, cost and description.            */
/* Inputs:      $pdo, $event_id                                             */
/*              $regtype_id - the type being edited (so its own name        */
/*              doesn't count as a duplicate), or 0 when adding.            */
/* Returns:     [values, errors]                                            */
/* ************************************************************************ */
function RegTypePostedValues(PDO $pdo, $event_id, $regtype_id) {
    $name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $cost = trim(is_string($_POST['cost'] ?? null) ? $_POST['cost'] : '');
    $description = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
    $errors = [];

    if ($name === '') {
        $errors[] = 'Name is required.';
    } elseif (mb_strlen($name) > REGTYPE_NAME_MAX) {
        $errors[] = 'Name must be '.REGTYPE_NAME_MAX.' characters or fewer.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ? AND LOWER(REGTYPE_Name) = LOWER(?) AND REGTYPE_ID <> ?');
        $stmt->execute([$event_id, $name, $regtype_id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'This event already has a registration type called "'.$name.'".';
        }
    }

    if ($cost === '') {
        $errors[] = 'Cost is required. Use 0 for a free registration type.';
    } else {
        $cost = str_replace([',', '$'], '', $cost); // allow "$1,250.00"
        if (strpos($cost, '-') !== false) {
            $errors[] = 'Cost cannot be negative.';
        } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $cost) || (float)$cost > REGTYPE_COST_MAX) {
            $errors[] = 'Cost must be an amount from 0 to '.number_format(REGTYPE_COST_MAX, 2).', e.g. 125 or 125.50.';
        }
    }

    return [['name' => $name, 'cost' => round((float)$cost, 2), 'description' => ($description === '' ? null : $description)], $errors];
}

/* ************************************************************************ */
/* Function:    RenderRegTypeFields                                         */
/* Description: The name, cost and description inputs for one registration */
/*              type (or the empty Add form).                               */
/* Inputs:      $prefix - makes the input ids unique on the page.           */
/*              $type - the row, or null for the Add form.                  */
/*              $with_enabled - include the Enabled checkbox (Add form).    */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderRegTypeFields($prefix, $type, $with_enabled) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $output_html  = '<div class="row gtr-25 gtr-uniform">';
    $output_html .= '   <div class="col-8 col-12-small">';
    $output_html .= '       <label for="'.$prefix.'_name">Name</label>';
    $output_html .= '       <input type="text" id="'.$prefix.'_name" regtype_field="name" maxlength="'.REGTYPE_NAME_MAX.'" value="'.$h($type['REGTYPE_Name'] ?? '').'" placeholder="e.g. Driver, Passenger, Saturday Only" />';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-4 col-12-small">';
    $output_html .= '       <label for="'.$prefix.'_cost">Cost ($)</label>';
    $output_html .= '       <input type="text" id="'.$prefix.'_cost" regtype_field="cost" inputmode="decimal" maxlength="10" value="'.$h(RegTypeCostValue($type['REGTYPE_Cost'] ?? null)).'" placeholder="0.00" />';
    $output_html .= '   </div>';
    $output_html .= '   <div class="col-12">';
    $output_html .= '       <label for="'.$prefix.'_description">Description</label>';
    $output_html .= '       <textarea id="'.$prefix.'_description" regtype_field="description" rows="3" placeholder="What this registration includes. HTML is allowed.">'.$h($type['REGTYPE_Description'] ?? '').'</textarea>';
    $output_html .= '   </div>';
    if ($with_enabled) {
        $output_html .= '   <div class="col-12">';
        $output_html .= '       <input type="checkbox" id="'.$prefix.'_enabled" regtype_field="enabled" checked />';
        $output_html .= '       <label for="'.$prefix.'_enabled">Enable this registration type</label>';
        $output_html .= '   </div>';
    }
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RegTypeCostValue                                            */
/* Description: A stored cost as it goes in the Cost input, e.g. 125.50.    */
/* Returns:     string ('' if there's no cost)                              */
/* ************************************************************************ */
function RegTypeCostValue($cost) {
    return ($cost === null || $cost === '') ? '' : number_format((float)$cost, 2, '.', '');
}

/* ************************************************************************ */
/* Function:    RegTypeDescriptionPreview                                   */
/* Description: The description as plain text, cut to                      */
/*              REGTYPE_DESCRIPTION_PREVIEW characters for the list.        */
/* Returns:     string (not escaped)                                        */
/* ************************************************************************ */
function RegTypeDescriptionPreview($description) {
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string)$description), ENT_QUOTES, 'UTF-8')));
    if (mb_strlen($text) > REGTYPE_DESCRIPTION_PREVIEW) {
        $text = rtrim(mb_substr($text, 0, REGTYPE_DESCRIPTION_PREVIEW)).'...';
    }
    return $text;
}

/* ************************************************************************ */
/* Function:    RenderRegTypeList                                           */
/* Description: The event's current registration types as a table, one row */
/*              each: an Enabled checkbox (saved as soon as it's changed),  */
/*              name, cost, a short description, and Edit / Delete. Each    */
/*              row carries its full values for the Edit modal.             */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderRegTypeList(PDO $pdo, $event_id) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_registration_types WHERE REGTYPE_Event_ID = ? ORDER BY REGTYPE_Name');
    $stmt->execute([$event_id]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$types) {
        return '<p>This event has no registration types yet. Add one above.</p>';
    }

    $output_html  = '<div class="table-wrapper">';
    $output_html .= '<table class="regtype_table">';
    $output_html .= '<thead><tr><th>Enabled</th><th>Name</th><th>Cost</th><th>Description</th><th></th></tr></thead>';
    $output_html .= '<tbody>';
    foreach ($types as $type) {
        $id = (int)$type['REGTYPE_ID'];
        $output_html .= '<tr class="regtype_row" regtype_id="'.$id.'" regtype_name="'.$h($type['REGTYPE_Name']).'" regtype_cost="'.$h(RegTypeCostValue($type['REGTYPE_Cost'])).'" regtype_description="'.$h($type['REGTYPE_Description']).'">';
        $output_html .= '   <td><input type="checkbox" id="regtype_'.$id.'_enabled" class="regtype_enabled_checkbox"'.(!empty($type['REGTYPE_Enabled']) ? ' checked' : '').' />';
        $output_html .= '       <label for="regtype_'.$id.'_enabled" class="regtype_checkbox_label" title="Enable or disable '.$h($type['REGTYPE_Name']).'"></label></td>';
        $output_html .= '   <td>'.$h($type['REGTYPE_Name']).'</td>';
        $output_html .= '   <td>$'.number_format((float)$type['REGTYPE_Cost'], 2).'</td>';
        $output_html .= '   <td>'.$h(RegTypeDescriptionPreview($type['REGTYPE_Description'])).'</td>';
        $output_html .= '   <td class="regtype_actions"><a href="#" class="button small regtype_edit_button">Edit</a> <a href="#" class="button small regtype_delete_button">Delete</a></td>';
        $output_html .= '</tr>';
    }
    $output_html .= '</tbody></table>';
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderRegistrationTypes                                     */
/* Description: Returns the Registration Types tool for the posted event:   */
/*              an Add form and the list of current types.                  */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderRegistrationTypes() {
    list($pdo, $event) = RegTypeLoadEvent(false);
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };

    $output_html  = '<h2>'.$h($event['EVENT_Name']).' Registration Types</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<p>Registrants choose one of these types when they register for the event.</p>';
    $output_html .= '<div id="regtype_tool" event_id="'.(int)$event['EVENT_ID'].'" csrf_token="'.$h(app_csrf_token()).'">';

    $output_html .= '<h3>Add a Registration Type</h3>';
    $output_html .= '<div id="regtype_add" class="regtype_item">';
    $output_html .= RenderRegTypeFields('regtype_new', null, true);
    $output_html .= '   <div class="regtype_messages"></div>';
    $output_html .= '   <a href="#" id="regtype_add_button" class="button primary small fit">Add Registration Type</a>';
    $output_html .= '</div>';

    $output_html .= '<h3 style="margin-top:2em;">Current Registration Types</h3>';
    $output_html .= '<div id="regtype_list_messages" class="regtype_messages"></div>';
    $output_html .= '<div id="regtype_list">'.RenderRegTypeList($pdo, $event['EVENT_ID']).'</div>';

    /* Edit modal - filled from the row's values by OpenRegistrationTypeEdit (regtypes.js) */
    $output_html .= '<div id="regtype_edit_modal" class="modal regtype_modal">';
    $output_html .= '   <div class="modal-content">';
    $output_html .= '       <span class="modal-close regtype_cancel_button" title="Close">&times;</span>';
    $output_html .= '       <h3>Edit Registration Type</h3>';
    $output_html .= '       <div id="regtype_edit" class="regtype_item" regtype_id="">';
    $output_html .= RenderRegTypeFields('regtype_edit', null, false);
    $output_html .= '           <div class="regtype_messages"></div>';
    $output_html .= '           <div class="row gtr-25 gtr-uniform">';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button primary small fit regtype_save_button">Save</a></div>';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button small fit regtype_cancel_button">Cancel</a></div>';
    $output_html .= '           </div>';
    $output_html .= '       </div>';
    $output_html .= '   </div>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    AddRegistrationType                                         */
/* Description: Adds a registration type to the posted event.               */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function AddRegistrationType() {
    list($pdo, $event) = RegTypeLoadEvent(true);
    list($values, $errors) = RegTypePostedValues($pdo, $event['EVENT_ID'], 0);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    $enabled = (($_POST['enabled'] ?? '') === '1') ? 1 : 0;
    $stmt = $pdo->prepare('INSERT INTO nsx_meetup_registration_types (REGTYPE_Event_ID, REGTYPE_Name, REGTYPE_Cost, REGTYPE_Description, REGTYPE_Enabled) VALUES (?, ?, ?, ?, ?)');
    try {
        $stmt->execute([$event['EVENT_ID'], $values['name'], $values['cost'], $values['description'], $enabled]);
    } catch (PDOException $e) {
        error_log('AddRegistrationType failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The registration type could not be added. Please try again.']);
    }

    EventAdminRespond('Pass', RenderRegTypeList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SaveRegistrationType                                        */
/* Description: Saves changes to one of the posted event's registration     */
/*              types.                                                      */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function SaveRegistrationType() {
    list($pdo, $event) = RegTypeLoadEvent(true);
    $regtype_id = RegTypePostedID($pdo, $event['EVENT_ID']);
    list($values, $errors) = RegTypePostedValues($pdo, $event['EVENT_ID'], $regtype_id);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    $stmt = $pdo->prepare('UPDATE nsx_meetup_registration_types SET REGTYPE_Name = ?, REGTYPE_Cost = ?, REGTYPE_Description = ? WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
    try {
        $stmt->execute([$values['name'], $values['cost'], $values['description'], $regtype_id, $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('SaveRegistrationType failed for type '.$regtype_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The registration type could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', RenderRegTypeList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SetRegistrationTypeEnabled                                  */
/* Description: Turns one of the posted event's registration types on or    */
/*              off (posted enabled = '1' or '0').                          */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SetRegistrationTypeEnabled() {
    list($pdo, $event) = RegTypeLoadEvent(true);
    $regtype_id = RegTypePostedID($pdo, $event['EVENT_ID']);
    $enabled = (($_POST['enabled'] ?? '') === '1') ? 1 : 0;

    $stmt = $pdo->prepare('UPDATE nsx_meetup_registration_types SET REGTYPE_Enabled = ? WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
    try {
        $stmt->execute([$enabled, $regtype_id, $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('SetRegistrationTypeEnabled failed for type '.$regtype_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The change could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', $enabled ? 'Registration type enabled.' : 'Registration type disabled.');
}

/* ************************************************************************ */
/* Function:    DeleteRegistrationType                                      */
/* Description: Deletes one of the posted event's registration types.       */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function DeleteRegistrationType() {
    list($pdo, $event) = RegTypeLoadEvent(true);
    $regtype_id = RegTypePostedID($pdo, $event['EVENT_ID']);

    $stmt = $pdo->prepare('DELETE FROM nsx_meetup_registration_types WHERE REGTYPE_ID = ? AND REGTYPE_Event_ID = ?');
    try {
        $stmt->execute([$regtype_id, $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('DeleteRegistrationType failed for type '.$regtype_id.': '.$e->getMessage());
        EventAdminRespond('Fail', ['The registration type could not be deleted. Please try again.']);
    }

    EventAdminRespond('Pass', RenderRegTypeList($pdo, $event['EVENT_ID']));
}
