<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    verify_csrf_token($token);

    $user_id = $_SESSION['user_id'];
    $username = sanitize($_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($username) || empty($current_password)) {
        redirect_with_message('../pages/settings.php', 'Username and Current Password are required.', 'error');
    }

    try {
        // Fetch current user data
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($current_password, $user['password'])) {
            redirect_with_message('../pages/settings.php', 'Invalid current password.', 'error');
        }

        // Prepare Update
        $sql = "UPDATE users SET username = :username";
        $params = [':username' => $username, ':id' => $user_id];

        // If new password is provided
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                redirect_with_message('../pages/settings.php', 'New passwords do not match.', 'error');
            }
            if (strlen($new_password) < 6) {
                redirect_with_message('../pages/settings.php', 'New password must be at least 6 characters.', 'error');
            }
            $sql .= ", password = :password";
            $params[':password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update Session
        $_SESSION['username'] = $username;

        redirect_with_message('../pages/settings.php', 'Profile updated successfully.', 'success');

    } catch (PDOException $e) {
        redirect_with_message('../pages/settings.php', 'Database Error: ' . $e->getMessage(), 'error');
    }

} else {
    header("Location: ../pages/settings.php");
    exit();
}
