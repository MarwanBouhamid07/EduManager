<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Verify CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        redirect_with_message("../pages/payments.php", "Security validation failed.", "error");
    }

    // 2. Get Payment ID
    $id = $_POST['id'] ?? null;
    if (!$id) {
        redirect_with_message("../pages/payments.php", "Invalid payment ID.", "error");
    }

    // 3. Delete from Database
    try {
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = :id");
        $stmt->execute([':id' => $id]);

        redirect_with_message("../pages/payments.php", "Payment record deleted successfully.", "success");

    } catch (PDOException $e) {
        redirect_with_message("../pages/payments.php", "Database Error: " . $e->getMessage(), "error");
    }

} else {
    header("Location: ../pages/payments.php");
    exit();
}
