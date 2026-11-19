<?php
/*-------------------------------------------------------------*/
/*                                                             */
/*  REGISTRANT HISTORY                                         */
/*                                                             */
/*  A log of changes to a registration, kept as a JSON list in */
/*  nsx_meetup_participants.Participant_Reg_History, oldest    */
/*  first. Each entry: {"at": "2026-11-09 14:03:22", "by":     */
/*  username or "Registrant", "action": "Edited", "changes":   */
/*  [{"field": "Email", "from": "...", "to": "..."}]}.         */
/*                                                             */
/*-------------------------------------------------------------*/

/* ************************************************************************ */
/* Function:    AppendRegistrantHistory                                     */
/* Description: Adds an entry to a registrant's history. A failure is       */
/*              logged but doesn't stop the change it describes.            */
/* Inputs:      $action - e.g. "Edited", "Payment received";                */
/*              $changes - [['field','from','to'], ...] (may be empty);     */
/*              $by - who made the change (defaults to the logged-in user). */
/* ************************************************************************ */
function AppendRegistrantHistory(PDO $pdo, $participant_id, $action, array $changes = [], $by = null) {
    if ($by === null) {
        $by = !empty($_SESSION['login_user']) ? (string)$_SESSION['login_user'] : 'Unknown';
    }
    $entry = ['at' => date('Y-m-d H:i:s'), 'by' => $by, 'action' => $action, 'changes' => $changes];
    try {
        $stmt = $pdo->prepare('SELECT Participant_Reg_History FROM nsx_meetup_participants WHERE Participant_ID = ?');
        $stmt->execute([(int)$participant_id]);
        $history = json_decode((string)$stmt->fetchColumn(), true);
        $history = is_array($history) ? $history : [];
        $history[] = $entry;
        $pdo->prepare('UPDATE nsx_meetup_participants SET Participant_Reg_History = ? WHERE Participant_ID = ?')
            ->execute([json_encode($history), (int)$participant_id]);
    } catch (PDOException $e) {
        error_log('AppendRegistrantHistory failed for participant '.$participant_id.': '.$e->getMessage());
    }
}

/* ************************************************************************ */
/* Function:    RegistrantHistoryChange                                     */
/* Description: One change for AppendRegistrantHistory, or null when the    */
/*              value didn't change.                                        */
/* ************************************************************************ */
function RegistrantHistoryChange($field, $from, $to) {
    $from = (string)$from;
    $to = (string)$to;
    return ($from === $to) ? null : ['field' => $field, 'from' => $from, 'to' => $to];
}

/* ************************************************************************ */
/* Function:    RenderRegistrantHistory                                     */
/* Description: A registrant's history for the Edit Information page,       */
/*              newest first.                                               */
/* Returns:     HTML                                                        */
/* ************************************************************************ */
function RenderRegistrantHistory($history_json) {
    $h = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES); };
    $history = json_decode((string)$history_json, true);
    $history = is_array($history) ? array_reverse($history) : [];

    $output_html  = '<div id="registrant_history_container" class="event_registration_sub_container active">';
    $output_html .= '  <h4>Registration History</h4>';
    if (!$history) {
        $output_html .= '  <p class="registrant_history_empty">No changes have been recorded for this registration.</p>';
        return $output_html.'</div>';
    }
    $output_html .= '  <div class="table-wrapper"><table class="registrant_history_table">';
    $output_html .= '    <thead><tr><th>When</th><th>By</th><th>Change</th></tr></thead><tbody>';
    foreach ($history as $entry) {
        $when = strtotime((string)($entry['at'] ?? ''));
        $output_html .= '<tr>';
        $output_html .= '<td class="registrant_history_when">'.($when ? $h(date('M j, Y g:i a', $when)) : '').'</td>';
        $output_html .= '<td>'.$h($entry['by'] ?? '').'</td>';
        $output_html .= '<td><b>'.$h($entry['action'] ?? '').'</b>';
        if (!empty($entry['changes']) && is_array($entry['changes'])) {
            $output_html .= '<ul class="registrant_history_changes">';
            foreach ($entry['changes'] as $change) {
                $from = (string)($change['from'] ?? '');
                $to = (string)($change['to'] ?? '');
                $output_html .= '<li>'.$h($change['field'] ?? '').': <span class="registrant_history_from">'.($from === '' ? '(blank)' : $h($from)).'</span>'
                               .' &rarr; <span class="registrant_history_to">'.($to === '' ? '(blank)' : $h($to)).'</span></li>';
            }
            $output_html .= '</ul>';
        }
        $output_html .= '</td></tr>';
    }
    $output_html .= '    </tbody></table></div>';
    return $output_html.'</div>';
}
