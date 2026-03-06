<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash_messages.php';
require_once __DIR__ . '/validation.php';


check_login();
?>
