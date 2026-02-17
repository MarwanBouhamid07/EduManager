<?php
/**
 * Validation Helpers
 */

/**
 * Validate Required Fields
 * Returns true if all fields in the array are present in the data and not empty
 */
function validate_required($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return false;
        }
    }
    return true;
}

/**
 * Validate Email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate Phone (Simple)
 */
function validate_phone($phone) {
    // Basic regex for digits, spaces, and optionally a plus sign
    return preg_match('/^[0-9\s\+]{7,15}$/', $phone);
}

/**
 * Validate Numeric
 */
function validate_numeric($value) {
    return is_numeric($value) && $value > 0;
}
?>
