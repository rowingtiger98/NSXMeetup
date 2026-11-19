<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  REGISTRATION FORM QUESTIONS                                */
/*                                                             */
/*  Admin tool for adding, editing, ordering and deleting an   */
/*  event's registration form questions                        */
/*  (nsx_meetup_event_questions) - the yes/no checkboxes and   */
/*  drop-downs in the form's "Other Information" section.      */
/*  Every request must come from a logged-in admin of the      */
/*  event (or an overlord), and changes must carry the         */
/*  session's CSRF token. Only the methods listed below can be */
/*  called.                                                    */
/*                                                             */
/*  Answers are stored in Participant_Events_Attending under   */
/*  the question's QUESTION_Label (a drop-down's answer is the */
/*  choice's key), so a label never changes once made, and a   */
/*  question or choice that registrants have answered can't be */
/*  deleted or have its type changed - it can be turned off.   */
/*                                                             */
/*  The list and forms use the Registration Types tool's       */
/*  styles (regtype_* classes in nsxmeetup.css).               */
/*                                                             */
/*-------------------------------------------------------------*/
require_once __DIR__ . '/eventAdmin.php';

app_session_start();

const QUESTION_METHODS = ['RenderEventQuestions', 'AddEventQuestion', 'SaveEventQuestion', 'SetEventQuestionActive', 'MoveEventQuestion', 'DeleteEventQuestion'];

const QUESTION_TYPES = ['BOOLEAN' => 'Yes / No checkbox', 'SELECT' => 'Drop-down list'];
const QUESTION_LABEL_MAX        = 32;    // QUESTION_Label is varchar(32) - made from the short name
const QUESTION_DESCRIPTION_MAX  = 218;   // QUESTION_Description is varchar(218)
const QUESTION_DEFAULT_TEXT_MAX = 32;    // QUESTION_Select_Default_Text is varchar(32)
const QUESTION_CHOICES_MAX      = 2056;  // QUESTION_Select_Choices is varchar(2056), as JSON
const QUESTION_CHOICE_TEXT_MAX  = 100;
const QUESTION_AMOUNT_MAX       = 9999.99;
const QUESTION_DEFAULT_SELECT_TEXT = 'Please select';
const QUESTION_PREVIEW = 120;  // characters of the question shown in the list

if (isset($_POST['method']) && in_array($_POST['method'], QUESTION_METHODS, true)) {
    $_POST['method']();
}

/* ************************************************************************ */
/* Function:    QuestionLoadEvent                                           */
/* Description: Loads and authorizes the posted event like                  */
/*              EventAdminLoadEvent, and for changes also checks the CSRF   */
/*              token.                                                      */
/* Inputs:      $is_change - true for requests that change data.            */
/* Returns:     [PDO, array]                                                */
/* ************************************************************************ */
function QuestionLoadEvent($is_change) {
    list($pdo, $event) = EventAdminLoadEvent();
    if ($is_change && !app_csrf_valid($_POST['csrf_token'] ?? null)) {
        EventAdminRespond('Fail', ['This page has expired. Reload the page and try again.']);
    }
    return [$pdo, $event];
}

/* ************************************************************************ */
/* Function:    QuestionPosted                                              */
/* Description: The posted question's row, if it belongs to the event.      */
/*              Ends the request otherwise.                                 */
/* Returns:     array                                                       */
/* ************************************************************************ */
function QuestionPosted(PDO $pdo, $event_id) {
    $question_id = filter_var($_POST['question_id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($question_id !== false) {
        $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_event_questions WHERE QUESTION_ID = ? AND EVENT_ID = ?');
        $stmt->execute([$question_id, $event_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
    }
    EventAdminRespond('Fail', ['That question could not be found. Reload the page and try again.']);
}

/* ************************************************************************ */
/* Function:    QuestionText                                                */
/* Description: A stored value as UTF-8. Older rows can hold Windows-1252   */
/*              text, which htmlspecialchars would turn into an empty       */
/*              string, so it's converted first.                            */
/* Returns:     string                                                      */
/* ************************************************************************ */
function QuestionText($s) {
    $s = (string)$s;
    if (function_exists('mb_check_encoding') && !mb_check_encoding($s, 'UTF-8')) {
        $s = mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
    }
    return $s;
}

/* ************************************************************************ */
/* Function:    QuestionChoices                                             */
/* Description: A drop-down question's choices, key => text.                */
/* Returns:     array                                                       */
/* ************************************************************************ */
function QuestionChoices($json) {
    $choices = json_decode((string)$json, true);
    $result = [];
    foreach ((is_array($choices) ? $choices : []) as $key => $text) {
        $result[(string)$key] = QuestionText($text);
    }
    return $result;
}

/* ************************************************************************ */
/* Function:    QuestionAnswerCounts                                        */
/* Description: How the event's registrants have answered each question,  */
/*              from Participant_Events_Attending.                          */
/* Returns:     array - label => ['answered' => registrants with an answer, */
/*              'yes' => registrants who said yes, 'values' => answer =>    */
/*              count]                                                      */
/* ************************************************************************ */
function QuestionAnswerCounts(PDO $pdo, $event_id) {
    $stmt = $pdo->prepare('SELECT Participant_Events_Attending FROM nsx_meetup_participants WHERE Event_ID = ?');
    $stmt->execute([$event_id]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $json) {
        $answers = json_decode((string)$json, true);
        if (!is_array($answers)) {
            continue;
        }
        foreach ($answers as $label => $answer) {
            if (!is_scalar($answer) || (string)$answer === '') {
                continue;
            }
            $label = (string)$label;
            $answer = (string)$answer;
            $counts[$label] = $counts[$label] ?? ['answered' => 0, 'yes' => 0, 'values' => []];
            $counts[$label]['answered']++;
            $counts[$label]['yes'] += ($answer === 'yes') ? 1 : 0;
            $counts[$label]['values'][$answer] = ($counts[$label]['values'][$answer] ?? 0) + 1;
        }
    }
    return $counts;
}

/* ************************************************************************ */
/* Function:    QuestionAnsweredCount                                       */
/* Description: How many registrants have really answered a question: for  */
/*              a yes/no question those who said yes (the form saves an     */
/*              unticked box as "no"), for a drop-down those who picked one */
/*              of its choices (not answers left from an earlier type). A   */
/*              question with any is in use - its type can't change and it  */
/*              can't be deleted.                                           */
/* Inputs:      $row - the question, $counts - its QuestionAnswerCounts     */
/*              entry.                                                      */
/* Returns:     int                                                         */
/* ************************************************************************ */
function QuestionAnsweredCount(array $row, array $counts) {
    if ($row['QUESTION_Type'] === 'BOOLEAN') {
        return (int)($counts['yes'] ?? 0);
    }
    $answered = 0;
    foreach (QuestionChoices($row['QUESTION_Select_Choices']) as $key => $text) {
        $answered += QuestionChoiceCount($counts, $key, $text);
    }
    return $answered;
}

/* ************************************************************************ */
/* Function:    QuestionChoiceCount                                         */
/* Description: How many registrants picked a choice (stored as its key,   */
/*              or by older registrations as its text).                     */
/* Returns:     int                                                         */
/* ************************************************************************ */
function QuestionChoiceCount(array $counts, $key, $text) {
    $values = $counts['values'] ?? [];
    return ($values[(string)$key] ?? 0) + (((string)$text !== (string)$key) ? ($values[(string)$text] ?? 0) : 0);
}

/* ************************************************************************ */
/* Function:    QuestionKey                                                 */
/* Description: A short key made from text ("Hilton Garden Inn" ->          */
/*              "hilton_garden_inn"), not in $taken.                        */
/* Returns:     string                                                      */
/* ************************************************************************ */
function QuestionKey($text, array $taken, $max, $fallback) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(QuestionText($text))), '_');
    if ($base === '') {
        $base = $fallback;
    } elseif (ctype_digit($base[0])) {
        $base = $fallback.'_'.$base;  // keys must not look like numbers (JSON object keys, HTML ids)
    }
    $base = rtrim(substr($base, 0, $max - 3), '_');
    $taken = array_map('strtolower', array_map('strval', $taken));

    $key = $base;
    for ($n = 2; in_array($key, $taken, true); $n++) {
        $key = $base.'_'.$n;
    }
    return $key;
}

/* ************************************************************************ */
/* Function:    QuestionUUID                                                */
/* Description: A random version 4 UUID for QUESTION_UUID.                  */
/* Returns:     string                                                      */
/* ************************************************************************ */
function QuestionUUID() {
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
}

/* ************************************************************************ */
/* Function:    QuestionPostedValues                                        */
/* Description: Validates the posted question. For an existing question     */
/*              ($row), the type can't change once registrants have         */
/*              answered it, and a drop-down choice someone picked can't be */
/*              removed. Choices are posted as choice_text[] with           */
/*              choice_key[] (blank for a new choice); an existing choice   */
/*              keeps its key, so renaming it keeps its answers.            */
/* Inputs:      $row - the question being edited, or null for a new one.    */
/*              $counts - its QuestionAnswerCounts entry.                   */
/* Returns:     [values, errors]                                            */
/* ************************************************************************ */
function QuestionPostedValues($row, array $counts) {
    $text = function ($key) { return trim(is_string($_POST[$key] ?? null) ? $_POST[$key] : ''); };
    $values = [
        'instruction'  => $text('instruction'),
        'description'  => $text('description'),
        'confirmation' => $text('confirmation'),
        'type'         => $text('type'),
        'default_text' => $text('default_text'),
        'add_to_basket' => ($text('add_to_basket') === '1') ? 1 : 0,
        'amount'       => $text('amount'),
        'choices'      => null,
    ];
    $errors = [];
    $answered = ($row !== null) && QuestionAnsweredCount($row, $counts) > 0;

    if ($values['instruction'] === '') {
        $errors[] = 'Please enter the question as registrants will see it.';
    }
    if ($values['description'] === '') {
        $errors[] = 'Short name is required.';
    } elseif (mb_strlen($values['description']) > QUESTION_DESCRIPTION_MAX) {
        $errors[] = 'Short name must be '.QUESTION_DESCRIPTION_MAX.' characters or fewer.';
    }
    if ($values['confirmation'] === '') {
        $values['confirmation'] = $values['description'];
    }

    if ($answered) {
        $values['type'] = $row['QUESTION_Type'];  // answers are stored in the type's format
    } elseif (!array_key_exists($values['type'], QUESTION_TYPES)) {
        $errors[] = 'Please choose the type of question.';
    }

    // A charge: a yes/no question adds it when ticked, a drop-down when any choice is picked
    if ($values['add_to_basket']) {
        if (!preg_match('/^\d{1,4}(\.\d{1,2})?$/', ltrim($values['amount'], '$')) || (float)ltrim($values['amount'], '$') <= 0) {
            $errors[] = 'Please enter the amount to charge, from $0.01 to $'.number_format(QUESTION_AMOUNT_MAX, 2).'.';
        }
        $values['amount'] = round((float)ltrim($values['amount'], '$'), 2);
    } else {
        $values['amount'] = 0;
    }

    if ($values['type'] === 'BOOLEAN') {
        $values['default_text'] = null;
    } elseif ($values['type'] === 'SELECT') {
        if ($values['default_text'] === '') {
            $values['default_text'] = QUESTION_DEFAULT_SELECT_TEXT;
        } elseif (mb_strlen($values['default_text']) > QUESTION_DEFAULT_TEXT_MAX) {
            $errors[] = 'The drop-down\'s prompt must be '.QUESTION_DEFAULT_TEXT_MAX.' characters or fewer.';
        }

        $old_choices = ($row !== null && $row['QUESTION_Type'] === 'SELECT') ? QuestionChoices($row['QUESTION_Select_Choices']) : [];
        $posted_texts = (isset($_POST['choice_text']) && is_array($_POST['choice_text'])) ? array_values($_POST['choice_text']) : [];
        $posted_keys = (isset($_POST['choice_key']) && is_array($_POST['choice_key'])) ? array_values($_POST['choice_key']) : [];
        $choices = [];
        $seen = [];
        foreach ($posted_texts as $i => $choice_text) {
            $choice_text = trim(is_string($choice_text) ? $choice_text : '');
            if ($choice_text === '') {
                continue;
            }
            if (mb_strlen($choice_text) > QUESTION_CHOICE_TEXT_MAX) {
                $errors[] = 'Each choice must be '.QUESTION_CHOICE_TEXT_MAX.' characters or fewer.';
                continue;
            }
            if (in_array(mb_strtolower($choice_text), $seen, true)) {
                $errors[] = '"'.$choice_text.'" is listed more than once.';
                continue;
            }
            $seen[] = mb_strtolower($choice_text);
            $key = is_string($posted_keys[$i] ?? null) ? $posted_keys[$i] : '';
            if (!array_key_exists($key, $old_choices) || array_key_exists($key, $choices)) {
                $key = QuestionKey($choice_text, array_merge(array_keys($old_choices), array_keys($choices)), QUESTION_LABEL_MAX, 'choice');
            }
            $choices[$key] = $choice_text;
        }
        if (count($choices) < 2) {
            $errors[] = 'A drop-down needs at least two choices.';
        }
        foreach ($old_choices as $key => $old_text) {
            $picked = QuestionChoiceCount($counts, $key, $old_text);
            $picked_by_text = ((string)$old_text !== (string)$key) ? ($counts['values'][(string)$old_text] ?? 0) : 0;
            if (!array_key_exists($key, $choices) && $picked > 0) {
                $errors[] = '"'.$old_text.'" can\'t be removed because '.$picked.' registrant'.($picked === 1 ? ' has' : 's have').' chosen it.'.($picked_by_text > 0 ? '' : ' You can rename it instead.');
            } elseif (array_key_exists($key, $choices) && $choices[$key] !== $old_text && $picked_by_text > 0) {
                // Older registrations stored the choice's text, not its key, so renaming would lose those answers
                $errors[] = '"'.$old_text.'" can\'t be renamed because '.$picked_by_text.' older registration'.($picked_by_text === 1 ? ' stores' : 's store').' it by name.';
            }
        }
        $values['choices'] = json_encode($choices, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (strlen($values['choices']) > QUESTION_CHOICES_MAX) {
            $errors[] = 'The choices are too long to store. Please shorten or remove some.';
        }
    }

    return [$values, $errors];
}

/* ************************************************************************ */
/* Function:    RenderQuestionFields                                        */
/* Description: The inputs for one question (or the empty Add form). The    */
/*              yes/no and drop-down settings are shown for the chosen type */
/*              by questions.js.                                            */
/* Inputs:      $prefix - makes the input ids unique on the page.           */
/*              $event - the event (whether it collects payments).          */
/*              $with_active - include the Active checkbox (Add form).      */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderQuestionFields($prefix, array $event, $with_active) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $field = function ($cols, $key, $label, $input, $help = '') use ($prefix) {
        return '   <div class="'.$cols.'"><label for="'.$prefix.'_'.$key.'">'.$label.'</label>'.$input
              .($help !== '' ? '<p class="experience_field_help">'.$help.'</p>' : '').'</div>';
    };
    $type_options = '<option value="">Choose a type...</option>';
    foreach (QUESTION_TYPES as $type => $type_label) {
        $type_options .= '<option value="'.$type.'">'.$h($type_label).'</option>';
    }

    $output_html  = '<div class="row gtr-25 gtr-uniform">';
    $output_html .= $field('col-12', 'instruction', 'Question',
                           '<textarea id="'.$prefix.'_instruction" question_field="instruction" rows="2" placeholder="e.g. Will you attend the Friday night dinner?"></textarea>',
                           'Shown on the registration form next to the answer boxes. HTML is allowed.');
    $output_html .= $field('col-6 col-12-small', 'description', 'Short Name',
                           '<input type="text" id="'.$prefix.'_description" question_field="description" maxlength="'.QUESTION_DESCRIPTION_MAX.'" placeholder="e.g. Friday Dinner" />',
                           'Used in the shopping cart, registrant lists and change history.');
    $output_html .= $field('col-6 col-12-small', 'confirmation', 'Confirmation / Report Heading',
                           '<input type="text" id="'.$prefix.'_confirmation" question_field="confirmation" placeholder="Same as the short name" />',
                           'Labels the answer on the confirmation page and in reports.');
    $output_html .= $field('col-6 col-12-small', 'type', 'Type',
                           '<select id="'.$prefix.'_type" question_field="type" class="question_type_select">'.$type_options.'</select>'
                          .'<p class="experience_field_help question_type_locked" style="display:none;">Registrants have answered this question, so its type can\'t change.</p>');
    $output_html .= '   <div class="col-6 col-12-small"></div>';

    /* Charge - for either type */
    $output_html .= '   <div class="col-12 question_charge_settings" style="display:none;">';
    $output_html .= '       <div class="row gtr-25 gtr-uniform">';
    $output_html .= '           <div class="col-6 col-12-small">';
    $output_html .= '               <input type="checkbox" id="'.$prefix.'_add_to_basket" question_field="add_to_basket" class="question_basket_checkbox" />';
    $output_html .= '               <label for="'.$prefix.'_add_to_basket"><span class="question_type_text" question_type="BOOLEAN">Charge for a yes answer</span>'
                   .'<span class="question_type_text" question_type="SELECT">Charge when a choice is picked</span></label>';
    $output_html .= '               <p class="experience_field_help">'.(!empty($event['EVENT_Collect_Payments'])
                        ? 'The amount is added to the registrant\'s total when they <span class="question_type_text" question_type="BOOLEAN">tick the box</span>'
                         .'<span class="question_type_text" question_type="SELECT">pick any choice - the same amount whichever they pick</span>. Changing it later doesn\'t change balances already recorded.'
                        : 'This event doesn\'t collect payments, so nothing is charged until payments are turned on in Edit Event Settings.').'</p>';
    $output_html .= '           </div>';
    $output_html .= '           <div class="col-6 col-12-small question_amount_field">';
    $output_html .= '               <label for="'.$prefix.'_amount">Amount ($)</label>';
    $output_html .= '               <input type="text" id="'.$prefix.'_amount" question_field="amount" inputmode="decimal" placeholder="e.g. 35.00" />';
    $output_html .= '           </div>';
    $output_html .= '       </div>';
    $output_html .= '   </div>';

    /* Drop-down settings */
    $output_html .= '   <div class="col-12 question_type_settings" question_type="SELECT" style="display:none;">';
    $output_html .= '       <div class="row gtr-25 gtr-uniform">';
    $output_html .= $field('col-6 col-12-small', 'default_text', 'Prompt',
                           '<input type="text" id="'.$prefix.'_default_text" question_field="default_text" maxlength="'.QUESTION_DEFAULT_TEXT_MAX.'" placeholder="'.QUESTION_DEFAULT_SELECT_TEXT.'" />',
                           'Shown in the drop-down before a choice is made.');
    $output_html .= '           <div class="col-12">';
    $output_html .= '               <label>Choices</label>';
    $output_html .= '               <div class="question_choices"></div>';
    $output_html .= '               <a href="#" class="button small question_add_choice_button">Add Choice</a>';
    $output_html .= '               <p class="experience_field_help">At least two. A choice registrants have picked can be renamed but not removed.</p>';
    $output_html .= '           </div>';
    $output_html .= '       </div>';
    $output_html .= '   </div>';

    if ($with_active) {
        $output_html .= '   <div class="col-12">';
        $output_html .= '       <input type="checkbox" id="'.$prefix.'_active" question_field="active" checked />';
        $output_html .= '       <label for="'.$prefix.'_active">Show this question on the registration form</label>';
        $output_html .= '   </div>';
    }
    $output_html .= '</div>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    QuestionPreview                                             */
/* Description: The start of a question's text, without HTML, for the list. */
/* Returns:     string                                                      */
/* ************************************************************************ */
function QuestionPreview($instruction) {
    $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(QuestionText($instruction)), ENT_QUOTES, 'UTF-8')));
    return (mb_strlen($plain) > QUESTION_PREVIEW) ? mb_substr($plain, 0, QUESTION_PREVIEW - 3).'...' : $plain;
}

/* ************************************************************************ */
/* Function:    RenderQuestionList                                          */
/* Description: The event's questions as a table in form order: up/down    */
/*              buttons, an Active checkbox (saved as soon as it's          */
/*              changed), the question, type, charge, answers, and          */
/*              Edit / Delete. Each row carries its full values for the     */
/*              Edit modal.                                                 */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderQuestionList(PDO $pdo, $event_id) {
    $h = function ($s) { return htmlspecialchars(QuestionText($s), ENT_QUOTES); };
    $stmt = $pdo->prepare('SELECT * FROM nsx_meetup_event_questions WHERE EVENT_ID = ? ORDER BY QUESTION_Order_Number IS NULL, QUESTION_Order_Number, QUESTION_ID');
    $stmt->execute([$event_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$questions) {
        return '<p>This event has no registration form questions yet. Add one above.</p>';
    }
    $all_counts = QuestionAnswerCounts($pdo, $event_id);
    $last = count($questions) - 1;

    $output_html  = '<div class="table-wrapper">';
    $output_html .= '<table class="regtype_table">';
    $output_html .= '<thead><tr><th>Order</th><th>Active</th><th>Question</th><th>Type</th><th>Charge</th><th>Answers</th><th></th></tr></thead>';
    $output_html .= '<tbody>';
    foreach ($questions as $i => $row) {
        $id = (int)$row['QUESTION_ID'];
        $counts = $all_counts[(string)$row['QUESTION_Label']] ?? ['answered' => 0, 'yes' => 0, 'values' => []];
        $choices = ($row['QUESTION_Type'] === 'SELECT') ? QuestionChoices($row['QUESTION_Select_Choices']) : [];
        $choice_list = [];
        foreach ($choices as $key => $text) {
            $choice_list[] = ['key' => (string)$key, 'text' => $text, 'picked' => QuestionChoiceCount($counts, $key, $text)];
        }
        $charged = in_array($row['QUESTION_Type'], ['BOOLEAN', 'SELECT'], true) && !empty($row['QUESTION_Add_To_Basket']);
        $answered = QuestionAnsweredCount($row, $counts);
        $in_use = ($answered > 0);

        if ($row['QUESTION_Type'] === 'BOOLEAN') {
            $type_label = 'Yes / No';
            $answers_label = $answered.' yes';
        } elseif ($row['QUESTION_Type'] === 'SELECT') {
            $type_label = 'Drop-down ('.count($choices).' choices)';
            $answers_label = $answered.' answered';
        } else {
            $type_label = $row['QUESTION_Type'].' (not shown on the form)';
            $answers_label = $counts['answered'].' answered';
        }

        $output_html .= '<tr class="question_row" question_id="'.$id.'"'
                       .' question_instruction="'.$h($row['QUESTION_Instruction_Text']).'"'
                       .' question_description="'.$h($row['QUESTION_Description']).'"'
                       .' question_confirmation="'.$h($row['QUESTION_Confirmation_Text']).'"'
                       .' question_type="'.$h($row['QUESTION_Type']).'"'
                       .' question_default_text="'.$h($row['QUESTION_Select_Default_Text']).'"'
                       .' question_add_to_basket="'.($charged ? '1' : '0').'"'
                       .' question_amount="'.($charged ? number_format((float)$row['QUESTION_Amount'], 2, '.', '') : '').'"'
                       .' question_choices="'.$h(json_encode($choice_list, JSON_UNESCAPED_UNICODE)).'"'
                       .' question_answered="'.($in_use ? '1' : '0').'">';
        $output_html .= '   <td class="question_order_buttons">'
                       .($i > 0 ? '<a href="#" class="button small question_move_button" direction="up" title="Move up">&uarr;</a>' : '')
                       .($i < $last ? '<a href="#" class="button small question_move_button" direction="down" title="Move down">&darr;</a>' : '').'</td>';
        $output_html .= '   <td><input type="checkbox" id="question_'.$id.'_active" class="question_active_checkbox"'.(!empty($row['QUESTION_Active']) ? ' checked' : '').' />';
        $output_html .= '       <label for="question_'.$id.'_active" class="regtype_checkbox_label" title="Show or hide this question on the registration form"></label></td>';
        $output_html .= '   <td><strong>'.$h($row['QUESTION_Description']).'</strong><br /><span class="question_preview">'.htmlspecialchars(QuestionPreview($row['QUESTION_Instruction_Text']), ENT_QUOTES).'</span></td>';
        $output_html .= '   <td>'.$h($type_label).'</td>';
        $output_html .= '   <td>'.($charged ? '$'.number_format((float)$row['QUESTION_Amount'], 2) : '-').'</td>';
        $output_html .= '   <td>'.$h($answers_label).'</td>';
        $output_html .= '   <td class="regtype_actions"><a href="#" class="button small question_edit_button">Edit</a>'
                       .($in_use ? '' : ' <a href="#" class="button small question_delete_button">Delete</a>').'</td>';
        $output_html .= '</tr>';
    }
    $output_html .= '</tbody></table>';
    $output_html .= '</div>';
    $output_html .= '<p class="experience_field_help">A question registrants have answered (a yes, or a drop-down choice) can\'t be deleted - untick Active to take it off the form. Their answers are kept.</p>';
    return $output_html;
}

/* ************************************************************************ */
/* Function:    RenderEventQuestions                                        */
/* Description: Returns the Registration Form Questions tool for the posted */
/*              event: an Add form and the list of current questions.      */
/* Returns:     JSON ['Pass', HTML] or ['Fail', [messages]]                 */
/* ************************************************************************ */
function RenderEventQuestions() {
    list($pdo, $event) = QuestionLoadEvent(false);
    $h = function ($s) { return htmlspecialchars(QuestionText($s), ENT_QUOTES); };

    $output_html  = '<h2>'.$h($event['EVENT_Name']).' Registration Form Questions</h2>';
    $output_html .= '<div style="margin-bottom:1em;" id="back_navigation_button"></div>';
    $output_html .= '<p>These appear in the "Other Information" section of the registration form, in the order below. Each registrant answers them for themselves and anyone they register with them.</p>';
    $output_html .= '<div id="question_tool" event_id="'.(int)$event['EVENT_ID'].'" csrf_token="'.$h(app_csrf_token()).'">';

    $output_html .= '<h3>Add a Question</h3>';
    $output_html .= '<div id="question_add" class="regtype_item">';
    $output_html .= RenderQuestionFields('question_new', $event, true);
    $output_html .= '   <div class="regtype_messages"></div>';
    $output_html .= '   <a href="#" id="question_add_button" class="button primary small fit">Add Question</a>';
    $output_html .= '</div>';

    $output_html .= '<h3 style="margin-top:2em;">Current Questions</h3>';
    $output_html .= '<div id="question_list_messages" class="regtype_messages"></div>';
    $output_html .= '<div id="question_list">'.RenderQuestionList($pdo, $event['EVENT_ID']).'</div>';

    /* Edit modal - filled from the row's values by OpenEventQuestionEdit (questions.js) */
    $output_html .= '<div id="question_edit_modal" class="modal regtype_modal">';
    $output_html .= '   <div class="modal-content">';
    $output_html .= '       <span class="modal-close question_cancel_button" title="Close">&times;</span>';
    $output_html .= '       <h3>Edit Question</h3>';
    $output_html .= '       <div id="question_edit" class="regtype_item" question_id="">';
    $output_html .= RenderQuestionFields('question_edit', $event, false);
    $output_html .= '           <div class="regtype_messages"></div>';
    $output_html .= '           <div class="row gtr-25 gtr-uniform">';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button primary small fit question_save_button">Save</a></div>';
    $output_html .= '               <div class="col-6 col-12-small"><a href="#" class="button small fit question_cancel_button">Cancel</a></div>';
    $output_html .= '           </div>';
    $output_html .= '       </div>';
    $output_html .= '   </div>';
    $output_html .= '</div>';
    $output_html .= '</div>';

    EventAdminRespond('Pass', $output_html);
}

/* ************************************************************************ */
/* Function:    AddEventQuestion                                            */
/* Description: Adds a question to the end of the posted event's form. Its */
/*              label is made from the short name and never changes.        */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function AddEventQuestion() {
    list($pdo, $event) = QuestionLoadEvent(true);
    list($values, $errors) = QuestionPostedValues(null, []);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }
    $active = (($_POST['active'] ?? '1') === '0') ? 0 : 1;

    try {
        $stmt = $pdo->prepare('SELECT QUESTION_Label FROM nsx_meetup_event_questions WHERE EVENT_ID = ?');
        $stmt->execute([$event['EVENT_ID']]);
        $taken = array_filter($stmt->fetchAll(PDO::FETCH_COLUMN), 'is_string');
        // Labels already used in this event's answers stay taken, even if their question was deleted
        $taken = array_merge($taken, array_map('strval', array_keys(QuestionAnswerCounts($pdo, $event['EVENT_ID']))));
        $label = QuestionKey($values['description'], $taken, QUESTION_LABEL_MAX, 'question');

        $stmt = $pdo->prepare('SELECT COALESCE(MAX(QUESTION_Order_Number), 0) FROM nsx_meetup_event_questions WHERE EVENT_ID = ?');
        $stmt->execute([$event['EVENT_ID']]);
        $order = (int)$stmt->fetchColumn() + 1;

        $pdo->prepare('INSERT INTO nsx_meetup_event_questions (EVENT_ID, QUESTION_Label, QUESTION_Description, QUESTION_Type, QUESTION_Active, QUESTION_UUID, QUESTION_Order_Number, QUESTION_Instruction_Text, QUESTION_Confirmation_Text, QUESTION_Select_Choices, QUESTION_Select_Default_Text, QUESTION_Add_To_Basket, QUESTION_Amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$event['EVENT_ID'], $label, $values['description'], $values['type'], $active, QuestionUUID(), $order, $values['instruction'], $values['confirmation'],
                       $values['choices'], $values['default_text'], $values['add_to_basket'], $values['amount']]);
    } catch (PDOException $e) {
        error_log('AddEventQuestion failed for event '.$event['EVENT_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The question could not be added. Please try again.']);
    }

    EventAdminRespond('Pass', RenderQuestionList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SaveEventQuestion                                           */
/* Description: Saves changes to one of the posted event's questions. The   */
/*              label and order are kept.                                   */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function SaveEventQuestion() {
    list($pdo, $event) = QuestionLoadEvent(true);
    $row = QuestionPosted($pdo, $event['EVENT_ID']);
    $counts = QuestionAnswerCounts($pdo, $event['EVENT_ID'])[(string)$row['QUESTION_Label']] ?? [];
    list($values, $errors) = QuestionPostedValues($row, $counts);
    if ($errors) {
        EventAdminRespond('Fail', $errors);
    }

    try {
        $pdo->prepare('UPDATE nsx_meetup_event_questions SET QUESTION_Description = ?, QUESTION_Type = ?, QUESTION_Instruction_Text = ?, QUESTION_Confirmation_Text = ?, QUESTION_Select_Choices = ?, QUESTION_Select_Default_Text = ?, QUESTION_Add_To_Basket = ?, QUESTION_Amount = ? WHERE QUESTION_ID = ? AND EVENT_ID = ?')
            ->execute([$values['description'], $values['type'], $values['instruction'], $values['confirmation'], $values['choices'], $values['default_text'],
                       $values['add_to_basket'], $values['amount'], (int)$row['QUESTION_ID'], $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('SaveEventQuestion failed for question '.$row['QUESTION_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The question could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', RenderQuestionList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    SetEventQuestionActive                                      */
/* Description: Shows or hides one of the posted event's questions on the   */
/*              registration form (posted active = '1' or '0').             */
/* Returns:     JSON ['Pass', message] or ['Fail', [messages]]              */
/* ************************************************************************ */
function SetEventQuestionActive() {
    list($pdo, $event) = QuestionLoadEvent(true);
    $row = QuestionPosted($pdo, $event['EVENT_ID']);
    $active = (($_POST['active'] ?? '') === '1') ? 1 : 0;

    try {
        $pdo->prepare('UPDATE nsx_meetup_event_questions SET QUESTION_Active = ? WHERE QUESTION_ID = ? AND EVENT_ID = ?')
            ->execute([$active, (int)$row['QUESTION_ID'], $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('SetEventQuestionActive failed for question '.$row['QUESTION_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The change could not be saved. Please try again.']);
    }

    EventAdminRespond('Pass', QuestionText($row['QUESTION_Description']).($active ? ' is now on the registration form.' : ' is now hidden from the registration form.'));
}

/* ************************************************************************ */
/* Function:    MoveEventQuestion                                           */
/* Description: Moves one of the posted event's questions up or down one    */
/*              place (posted direction = 'up' or 'down'), renumbering the  */
/*              event's questions 1, 2, 3... in their new order.            */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function MoveEventQuestion() {
    list($pdo, $event) = QuestionLoadEvent(true);
    $row = QuestionPosted($pdo, $event['EVENT_ID']);
    $direction = $_POST['direction'] ?? '';
    if ($direction !== 'up' && $direction !== 'down') {
        EventAdminRespond('Fail', ['Please choose up or down.']);
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT QUESTION_ID FROM nsx_meetup_event_questions WHERE EVENT_ID = ? ORDER BY QUESTION_Order_Number IS NULL, QUESTION_Order_Number, QUESTION_ID');
        $stmt->execute([$event['EVENT_ID']]);
        $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        $at = array_search((int)$row['QUESTION_ID'], $ids, true);
        $to = ($direction === 'up') ? $at - 1 : $at + 1;
        if ($at !== false && $to >= 0 && $to < count($ids)) {
            list($ids[$at], $ids[$to]) = [$ids[$to], $ids[$at]];
        }
        $update = $pdo->prepare('UPDATE nsx_meetup_event_questions SET QUESTION_Order_Number = ? WHERE QUESTION_ID = ? AND EVENT_ID = ?');
        foreach ($ids as $i => $question_id) {
            $update->execute([$i + 1, $question_id, $event['EVENT_ID']]);
        }
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('MoveEventQuestion failed for question '.$row['QUESTION_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The question could not be moved. Please try again.']);
    }

    EventAdminRespond('Pass', RenderQuestionList($pdo, $event['EVENT_ID']));
}

/* ************************************************************************ */
/* Function:    DeleteEventQuestion                                         */
/* Description: Deletes one of the posted event's questions - only if no    */
/*              registrant has answered it (QuestionAnsweredCount).         */
/* Returns:     JSON ['Pass', list HTML] or ['Fail', [messages]]            */
/* ************************************************************************ */
function DeleteEventQuestion() {
    list($pdo, $event) = QuestionLoadEvent(true);
    $row = QuestionPosted($pdo, $event['EVENT_ID']);
    $answered = QuestionAnsweredCount($row, QuestionAnswerCounts($pdo, $event['EVENT_ID'])[(string)$row['QUESTION_Label']] ?? []);
    if ($answered > 0) {
        EventAdminRespond('Fail', [QuestionText($row['QUESTION_Description']).' can\'t be deleted because '.$answered.' registrant'.($answered === 1 ? ' has' : 's have').' answered it'.($row['QUESTION_Type'] === 'BOOLEAN' ? ' yes' : '').'. Untick Active to take it off the form instead.']);
    }

    try {
        $pdo->prepare('DELETE FROM nsx_meetup_event_questions WHERE QUESTION_ID = ? AND EVENT_ID = ?')->execute([(int)$row['QUESTION_ID'], $event['EVENT_ID']]);
    } catch (PDOException $e) {
        error_log('DeleteEventQuestion failed for question '.$row['QUESTION_ID'].': '.$e->getMessage());
        EventAdminRespond('Fail', ['The question could not be deleted. Please try again.']);
    }

    EventAdminRespond('Pass', RenderQuestionList($pdo, $event['EVENT_ID']));
}

?>
