<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';



include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Settings / Profile</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div class="form-container" style="max-width: 500px; margin: 0 auto; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php display_flash_message(); ?>

        <form action="../actions/update_profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
                <small style="color: #6c757d;">Leave as is to keep your current username.</small>
            </div>

            <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #eee;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem; color: var(--text-muted);">Change Password</h3>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="current_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Current Password</label>
                <input type="password" id="current_password" name="current_password" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
                <small style="color: #6c757d;">Required to verify your identity.</small>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="new_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">New Password</label>
                <input type="password" id="new_password" name="new_password" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
                <small style="color: #6c757d;">Leave blank if you don't want to change your password.</small>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="confirm_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" style="width: 100%; background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Update Profile</button>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
