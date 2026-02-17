<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$type = $_GET['type'] ?? '';

if ($type !== 'students' && $type !== 'payments') {
    die("Invalid export type.");
}

try {
    if ($type === 'students') {
        $filename = "students_export_" . date('Y-m-d') . ".csv";
        $stmt = $pdo->query("SELECT id, full_name, phone, email, subject, monthly_fee, registration_date, status FROM students WHERE status != 'deleted'");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $headers = ['ID', 'Full Name', 'Phone', 'Email', 'Subject', 'Monthly Fee', 'Reg Date', 'Status'];
    } else {
        $filename = "payments_export_" . date('Y-m-d') . ".csv";
        $stmt = $pdo->query("
            SELECT p.id, s.full_name, p.amount, p.payment_date, p.next_due_date, p.payment_method, p.receipt_number 
            FROM payments p 
            JOIN students s ON p.student_id = s.id 
            ORDER BY p.payment_date DESC
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $headers = ['ID', 'Student', 'Amount', 'Date', 'Next Due', 'Method', 'Receipt #'];
    }

    // Output headers for download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    die("Export failed: " . $e->getMessage());
}
