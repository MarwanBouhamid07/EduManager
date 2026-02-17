<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Enforce Authentication
check_login();


?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Add Student</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div class="form-container" style="max-width: 600px; margin: 2rem auto; padding: 2rem; background: var(--bg-card); border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        
        <?php display_flash_message(); ?>

        <form action="../actions/add_student.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="full_name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name <span style="color: var(--danger-color);">*</span></label>
                <input type="text" id="full_name" name="full_name" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Phone Number <span style="color: var(--danger-color);">*</span></label>
                <input type="tel" id="phone" name="phone" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject / Course <span style="color: var(--danger-color);">*</span></label>
                <input type="text" id="subject" name="subject" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="monthly_fee" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Monthly Fee <span style="color: var(--danger-color);">*</span></label>
                <input type="number" id="monthly_fee" name="monthly_fee" step="0.01" min="0" required class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem;">
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="status" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Status</label>
                <select id="status" name="status" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 0.5rem; background-color: var(--bg-card);">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Save Student</button>
                <a href="students.php" class="btn btn-secondary" style="background-color: var(--bg-body); color: var(--text-color); padding: 0.75rem 1.5rem; text-decoration: none; border: 1px solid var(--border-color); border-radius: 0.5rem; font-weight: 500;">Cancel</a>
            </div>

        </form>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
