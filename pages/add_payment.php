<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Fetch Active Students for Dropdown
try {
    $stmt = $pdo->prepare("SELECT id, full_name, monthly_fee FROM students WHERE status = 'active' ORDER BY full_name ASC");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching students: " . $e->getMessage());
}


?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Add Payment</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div class="form-container" style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php display_flash_message(); ?>

        <form action="../actions/add_payment.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="student_id" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Student</label>
                <select id="student_id" name="student_id" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;" onchange="updateAmount(this)">
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" data-fee="<?php echo $student['monthly_fee']; ?>">
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="amount" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Amount ($)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="payment_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="payment_method" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Method</label>
                <select id="payment_method" name="payment_method" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
                    <option value="cash">Cash</option>
                    <option value="transfer">Bank Transfer</option>
                    <option value="card">Card</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Record Payment</button>
                <a href="payments.php" class="btn btn-secondary" style="background-color: #6c757d; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 0.5rem; font-weight: 500;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<script>
function updateAmount(select) {
    // Optional: Pre-fill amount based on student's monthly fee
    const selectedOption = select.options[select.selectedIndex];
    const fee = selectedOption.getAttribute('data-fee');
    const amountInput = document.getElementById('amount');
    
    if (fee) {
        amountInput.value = fee;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
