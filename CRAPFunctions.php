<?php
  if(isset($_POST['method'])) {
    $_POST['method']();
  }

function BigCrap() {
    $event_id = $_POST['event_id'];
    print $event_id;
    
    // $output_html = '';

    // $output_html .= 'This is the first data';
    // $output_html .= 'This is the second data';

    // $results = array();
    // $results[] = $output_html;
    // echo json_encode($results);
}

?>