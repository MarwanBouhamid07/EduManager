<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Get Student ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect_with_message('students.php', 'Invalid student ID.', 'error');
}

try {
    // 1. Fetch Student Details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student || $student['status'] === 'deleted') {
        redirect_with_message('students.php', 'Student not found.', 'error');
    }

    // 2. Fetch Payment History
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE student_id = ? ORDER BY payment_date DESC");
    $stmt->execute([$id]);
    $payments = $stmt->fetchAll();

    // 3. Billing Calculations
    $total_paid = 0;
    foreach ($payments as $p) {
        $total_paid += $p['amount'];
    }

    // Calculate months since registration
    $reg_date = new DateTime($student['registration_date']);
    $today = new DateTime();
    $interval = $reg_date->diff($today);
    $months_passed = ($interval->y * 12) + $interval->m + 1; // +1 to include current month

    $total_due = $months_passed * $student['monthly_fee'];
    $remaining_balance = $total_due - $total_paid;

} catch (PDOException $e) {
    die("Error fetching student profile: " . $e->getMessage());
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Student Profile</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1.5rem;">
        
        <!-- Left Column: Info & Stats -->
        <div class="profile-info">
            <div class="table-container" style="padding: 1.5rem; margin-bottom: 2rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">General Information</h3>
                <div style="margin-bottom: 1rem;">
                    <label style="color: var(--text-muted); font-size: 0.9rem;">Full Name</label>
                    <div style="font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="color: var(--text-muted); font-size: 0.9rem;">Course / Subject</label>
                    <div><?php echo htmlspecialchars($student['subject']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="color: var(--text-muted); font-size: 0.9rem;">Phone</label>
                    <div><?php echo htmlspecialchars($student['phone']); ?></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="color: var(--text-muted); font-size: 0.9rem;">Status</label>
                    <div>
                        <span class="badge <?php echo $student['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; background-color: <?php echo $student['status'] === 'active' ? '#d1e7dd' : '#f8d7da'; ?>; color: <?php echo $student['status'] === 'active' ? '#0f5132' : '#842029'; ?>;">
                            <?php echo ucfirst(htmlspecialchars($student['status'])); ?>
                        </span>
                    </div>
                </div>
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">
                <div style="display: flex; gap: 1rem;">
                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.9rem; background-color: var(--primary-color); color: white;">Edit Profile</a>
                </div>
            </div>

            <div class="table-container" style="padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1.5rem;">Financial Overview</h3>
                <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid var(--primary-color);">
                        <label style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase;">Monthly Fee</label>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-color);"><?php echo format_money($student['monthly_fee']); ?></div>
                    </div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid #198754;">
                        <label style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase;">Total Paid</label>
                        <div style="font-size: 1.25rem; font-weight: 700; color: #198754;"><?php echo format_money($total_paid); ?></div>
                    </div>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; border-left: 4px solid <?php echo $remaining_balance > 0 ? '#dc3545' : '#198754'; ?>;">
                        <label style="color: #6c757d; font-size: 0.8rem; text-transform: uppercase;">Balance Due</label>
                        <div style="font-size: 1.25rem; font-weight: 700; color: <?php echo $remaining_balance > 0 ? '#dc3545' : '#198754'; ?>;"><?php echo format_money($remaining_balance); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: History -->
        <div class="profile-history">
            <div class="table-container">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Payment History</h2>
                    <a href="add_payment.php?student_id=<?php echo $student['id']; ?>" class="btn-sm" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">+ New Payment</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 3rem;">No payments recorded yet.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                <td style="font-weight: 500;"><?php echo format_money($payment['amount']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['next_due_date']); ?></td>
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
