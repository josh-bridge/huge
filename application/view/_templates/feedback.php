<?php

// get the feedback (they are arrays, to make multiple positive/negative messages possible)
$feedback_positive = Session::get('feedback_positive');
$feedback_negative = Session::get('feedback_negative');
$print_r = Session::get('print_r');

// echo out positive messages
if (isset($feedback_positive)) {
    foreach ($feedback_positive as $feedback) {
        echo '<div class="feedback success">'.$feedback.'</div>';
    }
}

// echo out negative messages
if (isset($feedback_negative)) {
    foreach ($feedback_negative as $feedback) {
        echo '<div class="feedback error">'.$feedback.'</div>';
    }
}

if(isset($print_r)) {
    foreach ($print_r as $print) {
        echo '<div class="feedback error">';
        print_r($print);
        echo ' If Bool: '.($print ? 'true' : 'false').'</div>';
    }
}