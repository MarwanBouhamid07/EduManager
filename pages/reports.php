<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

try {
    // 1. Monthly Revenue (Last 6 Months)
    $monthly_revenue = [];
    for ($i = 0; $i < 6; $i++) {
        $month = date('Y-m', strtotime("-$i months"));
        $month_display = date('F Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE payment_date LIKE ?");
        $stmt->execute(["$month%"]);
        $total = $stmt->fetchColumn() ?: 0;
        
        $monthly_revenue[] = [
            'month' => $month_display,
            'total' => $total
        ];
    }

    // 2. Identify Late Payments (Overdue Students)
    // Students who are active but have NO payment in the current month
    $current_month = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT s.* 
        FROM students s 
        WHERE s.status = 'active' 
        AND s.id NOT IN (
            SELECT student_id FROM payments WHERE payment_date LIKE ?
        )
    ");
    $stmt->execute(["$current_month%"]);
    $overdue_students = $stmt->fetchAll();

    // 3. Total Collections (All Time)
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments");
    $total_collections = $stmt->fetchColumn() ?: 0;

} catch (PDOException $e) {
    die("Error generating reports: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Reports & Analytics</h1>
        <div class="user-profile">
            <a href="../actions/export_csv.php?type=payments" class="btn btn-secondary" style="margin-right: 1rem; text-decoration: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; background: #eee;">Export Payments</a>
            <a href="../actions/export_csv.php?type=students" class="btn btn-secondary" style="text-decoration: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.9rem; background: #eee;">Export Students</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1.5rem;">
        
        <div>
            <div class="table-container" style="padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Revenue Summary</h3>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; text-align: center; margin-bottom: 1.5rem;">
                    <div style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Total Collections (All Time)</div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);"><?php echo format_money($total_collections); ?></div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($monthly_revenue as $data): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-bottom: 1px solid #eee;">
                        <span style="color: #444; font-weight: 500;"><?php echo $data['month']; ?></span>
                        <span style="font-weight: 600;"><?php echo format_money($data['total']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="table-container">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color);">
                    <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Overdue Payments (Current Month: <?php echo date('F'); ?>)</h2>
                    <p style="font-size: 0.85rem; color: #666; margin-top: 0.3rem;">Active students who haven't paid this month yet.</p>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Phone</th>
                            <th>Fee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($overdue_students)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 3rem;">All clear! No overdue payments for now.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($overdue_students as $student): ?>
                            <tr>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td style="color: #ef4444; font-weight: 600;"><?php echo format_money($student['monthly_fee']); ?></td>
                                <td>
                                    <a href="add_payment.php?student_id=<?php echo $student['id']; ?>" class="btn-sm" style="background: #2563eb; color: white; padding: 0.3rem 0.6rem; border-radius: 0.25rem; text-decoration: none;">Collect Now</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
