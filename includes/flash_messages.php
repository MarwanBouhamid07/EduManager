<?php
/**
 * Display Flash Message
 * Renders a consistent alert based on session message
 */
function display_flash_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'success';
        
        $bg_color = ($type === 'success') ? '#d1e7dd' : '#f8d7da';
        $text_color = ($type === 'success') ? '#0f5132' : '#842029';
        $border_color = ($type === 'success') ? '#badbcc' : '#f5c2c7';

        echo "
        <div class='alert alert-{$type}' style='margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.5rem; background-color: {$bg_color}; color: {$text_color}; border: 1px solid {$border_color};'>
            " . htmlspecialchars($message) . "
        </div>
        ";

        // Important: Clear message after display
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}
?>
