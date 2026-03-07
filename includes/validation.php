<?php

function validate_required($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return false;
        }
    }
    return true;
}


function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}


function validate_phone($phone) {
    return preg_match('/^[0-9\s\+]{7,15}$/', $phone);
}


function validate_numeric($value) {
    return is_numeric($value) && $value > 0;
}
?>
