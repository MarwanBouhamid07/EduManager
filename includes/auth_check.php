<?php
// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Core Functions
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash_messages.php';
require_once __DIR__ . '/validation.php';

// Check Login
check_login();
?>
