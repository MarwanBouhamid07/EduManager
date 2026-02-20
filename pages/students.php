<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

// Search Logic
$search = sanitize($_GET['search'] ?? '');
$params = [];
$where = "WHERE status != 'deleted'";

if ($search) {
    $where .= " AND (full_name LIKE :search OR phone LIKE :search)";
    $params[':search'] = "%$search%";
}

// Fetch Students
try {
    $stmt = $pdo->prepare("SELECT * FROM students $where ORDER BY created_at DESC");
    $stmt->execute($params);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching students: " . $e->getMessage());
}


?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Students</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem;">
        <form method="GET" style="display: flex; gap: 0.5rem; flex: 1; max-width: 400px;">
            <input type="text" name="search" placeholder="Search by name or phone..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 0.5rem; font-size: 0.9rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1rem; border-radius: 0.5rem; background-color: var(--primary-color); color: white; border: none; cursor: pointer;">Search</button>
            <?php if ($search): ?>
                <a href="students.php" style="padding: 0.6rem; color: #666; text-decoration: none; font-size: 0.9rem;">Clear</a>
            <?php endif; ?>
        </form>
        <a href="add_student.php" class="btn btn-primary" style="background-color: var(--primary-color); color: white; padding: 0.6rem 1.2rem; text-decoration: none; border-radius: 0.5rem; font-weight: 500; font-size: 0.9rem;">+ Add Student</a>
    </div>

    <div class="table-container">
        <?php if (empty($students)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                <p style="font-size: 1.1rem; margin-bottom: 1rem;">No students found.</p>
                <p>Get started by adding a new student.</p>
            </div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Monthly Fee</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><?php echo htmlspecialchars($student['subject']); ?></td>
                            <td><?php echo format_money($student['monthly_fee']); ?></td>
                            <td>
                                <?php 
                                $payment_status = get_student_payment_status($student['id'], $pdo);
                                $status_colors = [
                                    'paid' => ['bg' => '#d1e7dd', 'text' => '#0f5132'],
                                    'due soon' => ['bg' => '#fff3cd', 'text' => '#856404'],
                                    'late' => ['bg' => '#f8d7da', 'text' => '#842029'],
                                    'unpaid' => ['bg' => '#e2e3e5', 'text' => '#383d41'],
                                    'unknown' => ['bg' => '#f8fafc', 'text' => '#64748b']
                                ];
                                $colors = $status_colors[$payment_status] ?? $status_colors['unknown'];
                                ?>
                                <span class="badge" style="padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.85rem; background-color: <?php echo $colors['bg']; ?>; color: <?php echo $colors['text']; ?>;">
                                    <?php echo ucfirst(htmlspecialchars($payment_status)); ?>
                                </span>
                            </td>
                            <td>
                                <a href="student_profile.php?id=<?php echo $student['id']; ?>" class="btn-sm" style="color: #2563eb; margin-right: 0.5rem; text-decoration: none; font-weight: 500;">Profile</a>
                                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn-sm" style="color: var(--text-color); margin-right: 0.5rem; text-decoration: none;">Edit</a>
                                <form action="../actions/delete_student.php" method="POST" style="display: inline;" onsubmit="return confirm('Archive this student? This will hide them from the list.');">
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
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
