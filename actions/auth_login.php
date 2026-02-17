<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Verify CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($token);

    // Get and sanitize inputs
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../pages/login.php");
        exit();
    }

    try {
        // 1. Rate Limiting Check
        $max_attempts = 5;
        $lockout_time = 60; // seconds
        
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= $max_attempts) {
            $time_since_last = time() - $_SESSION['last_attempt_time'];
            if ($time_since_last < $lockout_time) {
                $_SESSION['error'] = "Too many login attempts. Please wait " . ($lockout_time - $time_since_last) . " seconds.";
                header("Location: ../pages/login.php");
                exit();
            } else {
                // Reset after lockout
                $_SESSION['login_attempts'] = 0;
            }
        }

        // 2. Fetch user by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // 3. Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Login Success
            session_regenerate_id(true); // Prevent Session Fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Clear attempts
            unset($_SESSION['login_attempts'], $_SESSION['last_attempt_time']);
            
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            // Login Failed
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt_time'] = time();
            
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: ../pages/login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "System Error: " . $e->getMessage();
        header("Location: ../pages/login.php");
        exit();
    }

} else {
    // If accessed without POST, redirect to login
    header("Location: ../pages/login.php");
    exit();
}
?>
