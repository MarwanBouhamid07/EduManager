<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Get Payment ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect_with_message('payments.php', 'Invalid payment ID.', 'error');
}

// Fetch Payment Details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, s.full_name 
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        redirect_with_message('payments.php', 'Payment not found.', 'error');
    }
} catch (PDOException $e) {
    die("Error fetching payment: " . $e->getMessage());
}


?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Edit Payment</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div class="form-container" style="max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <?php display_flash_message(); ?>

        <form action="../actions/update_payment.php" method="POST" onsubmit="return confirm('Are you sure you want to update this payment?');">
            <input type="hidden" name="id" value="<?php echo $payment['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted);">Student</label>
                <div style="padding: 0.75rem; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 0.25rem;">
                    <?php echo htmlspecialchars($payment['full_name']); ?>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-muted);">Receipt Number</label>
                <div style="padding: 0.75rem; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 0.25rem;">
                    <?php echo htmlspecialchars($payment['receipt_number']); ?>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="amount" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Amount ($)</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" value="<?php echo $payment['amount']; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="payment_date" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date" value="<?php echo $payment['payment_date']; ?>" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="payment_method" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Payment Method</label>
                <select id="payment_method" name="payment_method" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 0.25rem; font-size: 1rem;">
                    <option value="cash" <?php echo $payment['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="transfer" <?php echo $payment['payment_method'] === 'transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    <option value="card" <?php echo $payment['payment_method'] === 'card' ? 'selected' : ''; ?>>Card</option>
                    <option value="other" <?php echo $payment['payment_method'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.5rem; font-weight: 500; cursor: pointer;">Update Payment</button>
                <a href="payments.php" class="btn btn-secondary" style="background-color: #6c757d; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 0.5rem; font-weight: 500;">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
