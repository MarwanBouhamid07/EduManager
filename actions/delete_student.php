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

    // 3. Delete from Database
    try {
        $stmt = $pdo->prepare("UPDATE students SET status = 'deleted' WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // 4. Redirect on Success
        redirect_with_message("../pages/students.php", "Student archived successfully!", "success");

    } catch (PDOException $e) {
        redirect_with_message("../pages/students.php", "Database Error: " . $e->getMessage(), "error");
    }

} else {
    // If accessed directly without POST
    header("Location: ../pages/students.php");
    exit();
}
