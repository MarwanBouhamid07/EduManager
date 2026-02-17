<?php
// Include your database configuration
require_once '../includes/auth_check.php';
require_once '../config/database.php';

try {
    // 1. Prepare query to fetch all payments and join student names
    $sql = "SELECT payments.*, students.full_name as student_name 
            FROM payments 
            JOIN students ON payments.student_id = students.id 
            ORDER BY payment_date DESC";
    
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Stop execution and show error if connection fails
    die("Data Fetch Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Records</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>

    <h2>All Payment Records</h2>

    <table>
        <thead>
            <tr>
                <th>Receipt No.</th>
                <th>Student Name</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Next Due Date</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($payments) > 0): ?>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= htmlspecialchars($payment['receipt_number']) ?></td>
                    <td><?= htmlspecialchars($payment['student_name']) ?></td>
                    <td>$<?= number_format($payment['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                    <td><?= htmlspecialchars($payment['next_due_date']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($payment['payment_method'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No payment records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>