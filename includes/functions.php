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
    return 'MAD ' . number_format($amount, 2);
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
 * Returns 'paid', 'due soon', 'late', or 'unpaid'
 */
function get_student_payment_status($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT billing_day, monthly_fee FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        
        if (!$student) return 'unknown';

        $billing_day = (int)$student['billing_day'];
        $current_month = date('Y-m');
        $today = date('Y-m-d');
        $today_day = (int)date('d');
        
        // 1. Check if paid this month
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE student_id = ? AND payment_date LIKE ?");
        $stmt->execute([$student_id, "$current_month%"]);
        if ($stmt->fetchColumn() > 0) {
            return 'paid';
        }

        // 2. Determine due date for current month
        $billing_day_clamped = min($billing_day, 28);
        $due_this_month = date("Y-m-") . sprintf("%02d", $billing_day_clamped);

        // 3. Status logic
        if ($today > $due_this_month) {
            return 'late';
        }

        // Check for 'due soon' (within 3 days of billing day)
        $diff = $billing_day_clamped - $today_day;
        if ($diff >= 0 && $diff <= 3) {
            return 'due soon';
        }

        return 'unpaid';
    } catch (Exception $e) {
        return 'unpaid';
    }
}

/**
 * Calculate Late Months
 * Counts months since registration that are unpaid
 */
function calculate_late_months($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT registration_date, monthly_fee, billing_day FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        
        if (!$student) return 0;

        $reg_date = new DateTime($student['registration_date']);
        $today = new DateTime();
        
        // Total expected payments since registration
        $interval = $reg_date->diff($today);
        $total_months = ($interval->y * 12) + $interval->m + 1; // +1 to include current month

        // Total payments made
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE_FORMAT(payment_date, '%Y-%m')) FROM payments WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $payments_made = (int)$stmt->fetchColumn();

        $late_months = $total_months - $payments_made;
        
        // If it's early in the month (before billing day) and they haven't paid, don't count current month as "late" yet
        $today_day = (int)date('d');
        if ($today_day <= (int)$student['billing_day']) {
            // Check if they paid this month
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE student_id = ? AND payment_date LIKE ?");
            $stmt->execute([$student_id, date('Y-m') . '%']);
            if ($stmt->fetchColumn() == 0) {
                $late_months = max(0, $late_months - 1);
            }
        }

        return max(0, $late_months);
    } catch (Exception $e) {
        return 0;
    }
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
