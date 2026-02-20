<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect_with_message('students.php', 'Invalid student ID.', 'error');
}

$student_id = (int)$_GET['id'];

// Fetch Student Details
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        redirect_with_message('students.php', 'Student not found.', 'error');
    }

    // Fetch Payment History
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY payment_date DESC");
    $stmt->execute([$student_id]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate Analytics
    $total_paid = 0;
    foreach ($payments as $p) {
        $total_paid += $p['amount'];
    }

    $late_months = calculate_late_months($student_id, $pdo);
    $current_status = get_student_payment_status($student_id, $pdo);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Student Profile</h1>
        <div class="action-buttons">
            <a href="edit_student.php?id=<?php echo $student_id; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Edit Profile</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1rem;">
        
        <!-- Student Info Card -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="table-container" style="padding: 1.5rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: #e2e8f0; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: #475569;">
                        <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                    </div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                    <span class="status-badge <?php echo 'status-' . strtolower($current_status); ?>">
                        Current: <?php echo ucfirst($current_status); ?>
                    </span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.95rem;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <span style="color: #64748b;">Phone:</span>
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($student['phone']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <span style="color: #64748b;">Email:</span>
                        <span style="font-weight: 500; font-size: 0.85rem;"><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <span style="color: #64748b;">Subject:</span>
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($student['subject']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <span style="color: #64748b;">Monthly Fee:</span>
                        <span style="font-weight: 600; color: #1e293b;"><?php echo format_money($student['monthly_fee']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem;">
                        <span style="color: #64748b;">Billing Day:</span>
                        <span style="font-weight: 500;">Day <?php echo $student['billing_day']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Joined:</span>
                        <span style="font-weight: 500;"><?php echo date('M d, Y', strtotime($student['registration_date'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Analytics Summary -->
            <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                <div class="stat-card" style="padding: 1.25rem;">
                    <div class="stat-title">Total Payments</div>
                    <div class="stat-value" style="font-size: 1.5rem; color: var(--success-color);"><?php echo format_money($total_paid); ?></div>
                    <div class="stat-change" style="color: #64748b;">All time</div>
                </div>
                <div class="stat-card" style="padding: 1.25rem;">
                    <div class="stat-title">Late Months</div>
                    <div class="stat-value" style="font-size: 1.5rem; color: <?php echo $late_months > 0 ? 'var(--danger-color)' : 'var(--success-color)'; ?>;"><?php echo $late_months; ?></div>
                    <div class="stat-change" style="color: #64748b;">Overdue months since registration</div>
                </div>
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="table-container">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Payment History</h2>
                <a href="add_payment.php?student_id=<?php echo $student_id; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; width: auto;">Add New Payment</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Receipt #</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem;">No payments recorded yet.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                            <td style="font-weight: 600;"><?php echo format_money($payment['amount']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></td>
                            <td style="color: #64748b; font-size: 0.85rem;"><?php echo htmlspecialchars($payment['receipt_number'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
