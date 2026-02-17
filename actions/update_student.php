<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Enforce Authentication
check_login();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Verify CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        redirect_with_message("../pages/students.php", "Security validation failed. Please try again.", "error");
    }

    // 2. Click Student ID
    $id = $_POST['id'] ?? null;
    if (!$id) {
        redirect_with_message("../pages/students.php", "Invalid student ID.", "error");
    }

    // 3. Sanitize and Validate Inputs
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $monthly_fee = floatval($_POST['monthly_fee'] ?? 0);
    $status = sanitize($_POST['status'] ?? 'active');

    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($phone)) {
        $errors[] = "Phone Number is required.";
    }
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    if ($monthly_fee < 0) {
        $errors[] = "Monthly Fee cannot be negative.";
    }

    // Check for validation errors
    if (!empty($errors)) {
        // In a real app, we might want to redirect back to edit page with errors and old input
        // For now, redirecting to edit page with error message
        redirect_with_message("../pages/edit_student.php?id=$id", implode(" ", $errors), "error");
    }

    // 4. Update Database
    try {
        $stmt = $pdo->prepare("UPDATE students SET full_name = :full_name, phone = :phone, email = :email, subject = :subject, monthly_fee = :monthly_fee, status = :status, updated_at = NOW() WHERE id = :id");
        
        $stmt->execute([
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':subject' => $subject,
            ':monthly_fee' => $monthly_fee,
            ':status' => $status,
            ':id' => $id
        ]);

        // 5. Redirect on Success
        redirect_with_message("../pages/students.php", "Student updated successfully!", "success");

    } catch (PDOException $e) {
        redirect_with_message("../pages/edit_student.php?id=$id", "Database Error: " . $e->getMessage(), "error");
    }

} else {
    // If accessed directly without POST
    header("Location: ../pages/students.php");
    exit();
}
