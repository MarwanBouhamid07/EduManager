<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($token);

    // Get Inputs
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';

    // Validate Inputs
    if (!$student_id || !$amount || $amount <= 0 || !$payment_date) {
        redirect_with_message('pages/add_payment.php', 'Please fill in all required fields correctly.', 'error');
    }

    try {
        // Calculate Next Due Date (Payment Date + 30 Days)
        $date = new DateTime($payment_date);
        $date->modify('+30 days');
        $next_due_date = $date->format('Y-m-d');

        // Generate Unique Receipt Number
        $receipt_number = generate_receipt_number($pdo);

        // Insert Payment
        $sql = "INSERT INTO payments (student_id, amount, payment_date, next_due_date, payment_method, receipt_number) 
                VALUES (:student_id, :amount, :payment_date, :next_due_date, :payment_method, :receipt_number)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $student_id,
            ':amount' => $amount,
            ':payment_date' => $payment_date,
            ':next_due_date' => $next_due_date,
            ':payment_method' => $payment_method,
            ':receipt_number' => $receipt_number
        ]);

        redirect_with_message('../pages/payments.php', 'Payment recorded successfully. Receipt generated.', 'success');

    } catch (PDOException $e) {
        // Log error in production, show friendly message here
        redirect_with_message('../pages/add_payment.php', "Database Error: " . $e->getMessage(), 'error');
    }

} else {
    // Not a POST request
    header("Location: ../pages/payments.php");
    exit();
}
