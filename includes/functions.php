<?php
// Core Helper Functions

/**
 * Sanitize Input
 * Removes whitespace and converts special characters to HTML entities
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Money
 * Formats a number as currency
 */
function format_money($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Check Login
 * Verifies if user is logged in, otherwise redirects to login page
 */
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit();
    }
}

/**
 * Generate CSRF Token
 * Generates a token if one doesn't exist
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 * Checks if the posted token matches the session token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF Token Validation Failed");
    }
    return true;
}

/**
 * Redirect with Message
 * Redirects to a page with a session message
 */
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}
/**
 * Generate Receipt Number
 * Format: RCP-YYYYMM-XXXX
 */
function generate_receipt_number($pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM payments");
        $count = $stmt->fetchColumn();
        return 'RCP-' . date('Ym') . '-' . sprintf('%04d', $count + 1);
    } catch (PDOException $e) {
        return 'RCP-' . date('YmdHis'); // Fallback
    }
}

/**
 * Get Student Payment Status
 * Returns 'paid', 'late', or 'pending'
 */
function get_student_payment_status($student_id, $pdo) {
    $current_month = date('Y-m');
    $today = date('Y-m-d');
    
    // Check if paid this month
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE student_id = ? AND payment_date LIKE ?");
    $stmt->execute([$student_id, "$current_month%"]);
    if ($stmt->fetchColumn() > 0) {
        return 'paid';
    }
    
    // Check if late (any previous payment has next_due_date < today and no payment after that)
    $stmt = $pdo->prepare("SELECT next_due_date FROM payments WHERE student_id = ? ORDER BY next_due_date DESC LIMIT 1");
    $stmt->execute([$student_id]);
    $last_due_date = $stmt->fetchColumn();
    
    if ($last_due_date && $last_due_date < $today) {
        return 'late';
    }
    
    return 'pending';
}
/**
 * Securely starts a session
 */
function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true, // Only if SSL is used, but good practice
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}
?>
