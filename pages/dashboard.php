<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/dashboard_helpers.php';
require_once '../includes/finance_functions.php';

// Fetch Financial Intelligence Data
try {
    $expected_revenue = get_expected_monthly_revenue($pdo);
    $collected_revenue = get_collected_monthly_revenue($pdo);
    $outstanding_debt = get_total_outstanding_debt($pdo);
    
    // Feature 4: Discipline Rate
    $discipline_rate = ($expected_revenue > 0) ? round(($collected_revenue / $expected_revenue) * 100) : 0;
    
    // Feature 5: Late Students Ranking
    $late_students_ranking = get_late_students_ranking($pdo, 5);
    
    // Feature 6: Monthly Revenue Chart (6 months)
    $finance_chart = get_financial_chart_data($pdo);

    // Existing context helpers
    $total_students = get_total_students($pdo);
    $recent_payments = get_recent_payments($pdo, 5);
    
    // Add data for restored charts
    $payment_status_data = get_student_payment_status_data($pdo);
    $payment_method_data = get_payment_method_stats($pdo);
} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Financial Intelligence</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Financial Analytics Cards -->
    <div class="stats-grid">
        <!-- Feature 1: Expected Revenue -->
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-title">Expected Revenue</div>
            <div class="stat-value" style="color: #1e40af;"><?php echo format_money($expected_revenue); ?></div>
            <div class="stat-change text-primary">Target for this month</div>
        </div>

        <!-- Feature 2: Collected Revenue -->
        <div class="stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-title">Collected Revenue</div>
            <div class="stat-value" style="color: #065f46;"><?php echo format_money($collected_revenue); ?></div>
            <div class="stat-change text-success">Actual receipts this month</div>
        </div>

        <!-- Feature 3: Outstanding Debt -->
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-title">Outstanding Debt</div>
            <div class="stat-value" style="color: #991b1b;"><?php echo format_money($outstanding_debt); ?></div>
            <div class="stat-change text-danger">Total unpaid revenue</div>
        </div>

        <!-- Feature 4: Discipline Rate -->
        <div class="stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-title">Payment Discipline</div>
            <div class="stat-value" style="color: #92400e;"><?php echo $discipline_rate; ?>%</div>
            <div style="width: 100%; height: 8px; background: #fee2e2; border-radius: 4px; margin-top: 10px; overflow: hidden;">
                <div style="width: <?php echo $discipline_rate; ?>%; height: 100%; background: #f59e0b;"></div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Row 1 -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        
        <!-- Feature 6: Revenue Chart -->
        <div class="table-container" style="padding: 1.5rem;">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.1rem; font-weight: 600;">Revenue Trend (Last 6 Months)</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="financeChart"></canvas>
            </div>
        </div>

        <!-- System Context info -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="table-container" style="padding: 1.5rem;">
                <h3 style="margin: 0 0 1rem; font-size: 1rem;">System Health</h3>
                <p style="margin: 0; font-size: 0.9rem; line-height: 1.5; color: #4b5563;">
                    Your collection rate is currently at <strong><?php echo $discipline_rate; ?>%</strong>. 
                    Focus on students with high outstanding debt.
                </p>
            </div>
        </div>
    </div>

    <!-- Restored Charts Row -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        <!-- RESTORED: Student Payment Status -->
        <div class="table-container" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; font-weight: 600;">Payment Status (Current Month)</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="paymentStatusChart"></canvas>
            </div>
        </div>

        <!-- RESTORED: Payment Method Layout -->
        <div class="table-container" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 1.5rem; font-size: 1.1rem; font-weight: 600;">Revenue by Payment Method</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
        
        <!-- Feature 5: Most Late Students Table ranking -->
        <div class="table-container">
            <div style="padding: 1.2rem; border-bottom: 1px solid var(--border-color);">
                <h2 style="font-size: 1.1rem; font-weight: 600; color: #ef4444; margin: 0;">Top Debtors (Ranking)</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Late Months</th>
                        <th>Total Debt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($late_students_ranking)): ?>
                        <tr><td colspan="3" style="text-align:center; padding: 2rem; color: #6b7280;">No outstanding debts found! Good job.</td></tr>
                    <?php else: ?>
                        <?php foreach ($late_students_ranking as $student): ?>
                        <tr>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 12px; font-size: 0.85rem;"><?php echo $student['late_months']; ?> months</span></td>
                            <td style="font-weight: 600; color: #dc2626;"><?php echo format_money($student['total_debt']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Activity -->
        <div class="table-container">
            <div style="padding: 1.2rem; border-bottom: 1px solid var(--border-color);">
                <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Recent Collections</h2>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_payments as $payment): ?>
                    <tr>
                        <td style="font-weight: 500;"><?php echo htmlspecialchars($payment['full_name']); ?></td>
                        <td style="font-weight: 600; color: #059669;"><?php echo format_money($payment['amount']); ?></td>
                        <td><span style="background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;"><?php echo ucfirst($payment['payment_method']); ?></span></td>
                        <td style="color: #6b7280; font-size: 0.85rem;"><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Unified Financial Charting -->
<!-- 1. Load Chart.js CDN first -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- 2. Load our custom dashboard logic -->
<!-- Use cache-buster to ensure the latest version is loaded -->
<script src="../assets/js/dashboard_charts.js?v=<?php echo time(); ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("Dashboard DOM fully loaded. Checking for chart functions...");
    
    // Safety check for functions
    if (typeof window.initFinanceChart !== 'function') {
        console.error("CRITICAL: initFinanceChart is not defined. window.initFinanceChart is:", typeof window.initFinanceChart);
        return;
    }
    console.log("Found initFinanceChart. Checking others...");
    console.log("initPaymentStatusChart type:", typeof window.initPaymentStatusChart);
    console.log("initPaymentMethodChart type:", typeof window.initPaymentMethodChart);

    // 1. Revenue Trend Chart
    try {
        const trendData = <?php echo json_encode($finance_chart); ?>;
        window.initFinanceChart(trendData.labels, trendData.values);
    } catch (e) { console.error("Trend Chart failed:", e); }

    // 2. Student Payment Status Chart
    try {
        const statusData = <?php echo json_encode($payment_status_data); ?>;
        window.initPaymentStatusChart(statusData.labels, statusData.values);
    } catch (e) { console.error("Status Chart failed:", e); }

    // 3. Payment Method Chart
    try {
        const methodData = <?php echo json_encode($payment_method_data); ?>;
        window.initPaymentMethodChart(methodData.labels, methodData.values);
    } catch (e) { console.error("Method Chart failed:", e); }
});
</script>

<?php include '../includes/footer.php'; ?>
