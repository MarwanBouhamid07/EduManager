<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($token);

    // Get Inputs
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // Validate Inputs
    if (!$id || !$amount || $amount <= 0 || !$payment_date || !$payment_method) {
        redirect_with_message("../pages/edit_payment.php?id=$id", 'Please fill in all required fields correctly.', 'error');
    }

    try {
        // Calculate Next Due Date (Payment Date + 30 Days)
        $date = new DateTime($payment_date);
        $date->modify('+30 days');
        $next_due_date = $date->format('Y-m-d');

        // Update Payment
        $sql = "UPDATE payments 
                SET amount = :amount, 
                    payment_date = :payment_date, 
                    next_due_date = :next_due_date, 
                    payment_method = :payment_method 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':amount' => $amount,
            ':payment_date' => $payment_date,
            ':next_due_date' => $next_due_date,
            ':payment_method' => $payment_method,
            ':id' => $id
        ]);

        redirect_with_message('../pages/payments.php', 'Payment updated successfully.', 'success');

    } catch (PDOException $e) {
        // Log error in production, show friendly message here
        redirect_with_message("../pages/edit_payment.php?id=$id", "Database Error: " . $e->getMessage(), 'error');
    }

} else {
    // Not a POST request
    header("Location: ../pages/payments.php");
    exit();
}
