<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Filter Logic
$btn_month = $_GET['month'] ?? '';
$btn_method = $_GET['method'] ?? '';
$params = [];
$where_clauses = [];

if ($btn_month) {
    $where_clauses[] = "p.payment_date LIKE :month";
    $params[':month'] = "$btn_month%";
}

if ($btn_method) {
    $where_clauses[] = "p.payment_method = :method";
    $params[':method'] = $btn_method;
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Fetch Payments with Student Info
try {
    $stmt = $pdo->prepare("
        SELECT p.*, s.full_name 
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        $where_sql
        ORDER BY p.payment_date DESC, p.created_at DESC
    ");
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching payments: " . $e->getMessage());
}



$current_date = date('Y-m-d');
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Payments</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="action-bar" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
        <form method="GET" style="display: flex; gap: 0.5rem; align-items: flex-end; flex: 1;">
            <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                <label style="font-size: 0.8rem; color: #666;">Month</label>
                <input type="month" name="month" value="<?php echo htmlspecialchars($btn_month); ?>" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 0.9rem;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.3rem;">
                <label style="font-size: 0.8rem; color: #666;">Method</label>
                <select name="method" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 0.9rem; background: white;">
                    <option value="">All Methods</option>
                    <option value="cash" <?php echo $btn_method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="transfer" <?php echo $btn_method === 'transfer' ? 'selected' : ''; ?>>Transfer</option>
                    <option value="card" <?php echo $btn_method === 'card' ? 'selected' : ''; ?>>Card</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1rem; border-radius: 0.5rem; background-color: var(--primary-color); color: white; border: none; cursor: pointer;">Filter</button>
            <?php if ($btn_month || $btn_method): ?>
                <a href="payments.php" style="padding: 0.6rem; color: #666; text-decoration: none; font-size: 0.9rem;">Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_payment.php" class="btn btn-primary" style="background-color: var(--primary-color); color: white; padding: 0.6rem 1.2rem; text-decoration: none; border-radius: 0.5rem; font-weight: 500; font-size: 0.9rem;">+ Add Payment</a>
    </div>

    <div class="table-container">
        <?php if (empty($payments)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <p style="font-size: 1.1rem; margin-bottom: 1rem;">No payments found.</p>
                <p>Record your first payment to get started.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Next Due Date</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <?php 
                            $is_late = $payment['next_due_date'] < $current_date; 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['receipt_number']); ?></td>
                            <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                            <td><?php echo format_money($payment['amount']); ?></td>
                            <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($payment['next_due_date']); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($payment['payment_method'])); ?></td>
                            <td>
                                <?php 
                                    $payment_status = get_student_payment_status($payment['student_id'], $pdo);
                                    if ($payment_status === 'paid'): 
                                ?>
                                    <span class="badge badge-success" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; background-color: #d1e7dd; color: #0f5132;">Paid</span>
                                <?php elseif ($payment_status === 'late'): ?>
                                    <span class="badge badge-danger" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; background-color: #f8d7da; color: #842029;">Late</span>
                                <?php else: ?>
                                    <span class="badge" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; background-color: #fff3cd; color: #664d03;">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_payment.php?id=<?php echo $payment['id']; ?>" class="btn-sm" style="color: var(--text-color); margin-right: 0.5rem; text-decoration: none;">Edit</a>
                                <form action="../actions/delete_payment.php" method="POST" style="display: inline;" onsubmit="return confirm('Delete this payment record? This action cannot be undone.');">
                                    <input type="hidden" name="id" value="<?php echo $payment['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <button type="submit" class="btn-sm" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0; font-size: inherit; font-family: inherit;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
