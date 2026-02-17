<?php
/**
 * Dashboard Helper Functions
 */

/**
 * Get Total Students Count
 */
function get_total_students($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    return $stmt->fetchColumn();
}

/**
 * Get Monthly Income (Current Month)
 */
function get_monthly_income($pdo) {
    $current_month_start = date('Y-m-01');
    $current_month_end = date('Y-m-t');
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE payment_date BETWEEN ? AND ?");
    $stmt->execute([$current_month_start, $current_month_end]);
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Get Late Payments Count
 * Counts students whose next_due_date has passed and are active
 */
function get_late_payments_count($pdo) {
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT student_id) 
        FROM payments 
        WHERE next_due_date < ?
    ");
    $stmt->execute([$today]);
    return $stmt->fetchColumn();
}

/**
 * Get Recent Payments
 */
function get_recent_payments($pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT p.*, s.full_name, s.subject 
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        ORDER BY p.payment_date DESC, p.created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get Recent Students
 */
function get_recent_students($pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT * FROM students 
        ORDER BY created_at DESC 
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
