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
        redirect_with_message("../pages/add_student.php", "Security validation failed. Please try again.", "error");
    }

    // 2. Sanitize and Validate Inputs
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
        redirect_with_message("../pages/add_student.php", implode(" ", $errors), "error");
    }

    // 3. Insert into Database
    try {
        $stmt = $pdo->prepare("INSERT INTO students (full_name, phone, email, subject, monthly_fee, status, registration_date) VALUES (:full_name, :phone, :email, :subject, :monthly_fee, :status, NOW())");
        
        $stmt->execute([
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':email' => $email,
            ':subject' => $subject,
            ':monthly_fee' => $monthly_fee,
            ':status' => $status
        ]);

        // 4. Redirect on Success
        redirect_with_message("../pages/students.php", "Student added successfully!", "success");

    } catch (PDOException $e) {
        // Handle Database Errors (e.g., duplicate phone if unique constraint exists, though simple schema might not strictly enforce unique phone, usually it's good practice)
        // Log error for admin? error_log($e->getMessage());
        redirect_with_message("../pages/add_student.php", "Database Error: " . $e->getMessage(), "error");
    }

} else {
    // If accessed directly without POST
    header("Location: ../pages/add_student.php");
    exit();
}
