<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

require_once '../includes/dashboard_helpers.php';

// Fetch Dashboard Data using Helpers
try {
    $total_students = get_total_students($pdo);
    $monthly_income = get_monthly_income($pdo);
    $late_payments = get_late_payments_count($pdo);
    $recent_payments = get_recent_payments($pdo, 5);
    $recent_students = get_recent_students($pdo, 5);
} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Dashboard</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total Students</div>
            <div class="stat-value"><?php echo $total_students; ?></div>
            <div class="stat-change text-success">Registered</div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Income This Month</div>
            <div class="stat-value"><?php echo format_money($monthly_income); ?></div>
            <div class="stat-change text-success"><?php echo date('F Y'); ?></div>
        </div>

        <div class="stat-card">
            <div class="stat-title">Late Payments</div>
            <div class="stat-value" style="color: var(--danger-color);"><?php echo $late_payments; ?></div>
            <div class="stat-change text-danger">Action required</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        <!-- Recent Payments -->
        <div class="table-container">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Recent Payments</h2>
                <a href="payments.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">View All</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_payments)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">No entries.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recent_payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                            <td style="font-weight: 500;"><?php echo format_money($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Students -->
        <div class="table-container">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">New Students</h2>
                <a href="students.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem;">View All</a>
            </div>
            <table class="data-table">
                <tbody>
                    <?php if (empty($recent_students)): ?>
                    <tr>
                        <td style="text-align: center; color: var(--text-muted); padding: 2rem;">No new students.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recent_students as $student): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($student['subject']); ?></div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
