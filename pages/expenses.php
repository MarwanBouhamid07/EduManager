<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Handle Add Expense Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_expense') {
    // Validate CSRF token here if implemented

    $category = sanitize($_POST['category'] ?? '');
    $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $description = sanitize($_POST['description'] ?? '');
    $expense_date = sanitize($_POST['expense_date'] ?? date('Y-m-d'));

    if ($category && $amount > 0 && $expense_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO expenses (category, amount, description, expense_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$category, $amount, $description, $expense_date]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Expense added successfully.'];
            header("Location: expenses.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Error adding expense: ' . $e->getMessage()];
        }
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please fill all required fields correctly.'];
    }
}

// Handle Delete Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_expense') {
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Expense deleted successfully.'];
            header("Location: expenses.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Error deleting expense: ' . $e->getMessage()];
        }
    }
}


// Fetch Expenses Filtered Logic
$month = sanitize($_GET['month'] ?? date('m'));
$year = sanitize($_GET['year'] ?? date('Y'));

try {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM expenses 
        WHERE MONTH(expense_date) = ? AND YEAR(expense_date) = ?
        ORDER BY expense_date DESC
    ");
    $stmt->execute([$month, $year]);
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total for filtered view
    $total_filtered = 0;
    foreach($expenses as $exp) {
        $total_filtered += $exp['amount'];
    }

} catch (PDOException $e) {
    die("Error fetching expenses: " . $e->getMessage());
}

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Expense Tracking</h1>
        <div class="user-profile">
            <span>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></span>
        </div>
    </div>

    <!-- Need to add flash messaging support dynamically -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?>" style="margin-bottom: 2rem;">
            <?php echo $_SESSION['flash']['message']; unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1rem;">
        
        <!-- Add Expense Form -->
        <div class="table-container" style="padding: 1.5rem; height: fit-content;">
            <h2 style="font-size: 1.2rem; margin-bottom: 1.5rem; color: var(--text-main);">Add New Expense</h2>
            
            <form action="expenses.php" method="POST">
                <input type="hidden" name="action" value="add_expense">
                
                <div class="form-group">
                    <label class="form-label" for="category">Category *</label>
                    <select class="form-control" id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Rent">Rent</option>
                        <option value="Electricity">Electricity</option>
                        <option value="Water">Water</option>
                        <option value="Internet">Internet</option>
                        <option value="Supplies">Supplies</option>
                        <option value="Salaries">Salaries</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Amount *</label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="expense_date">Date *</label>
                    <input type="date" class="form-control" id="expense_date" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description (Optional)</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Additional details..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="background-color: #ef4444; margin-top: 1rem;">Add Expense</button>
            </form>
        </div>

        <!-- Expenses List View -->
        <div class="table-container" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.2rem; color: var(--text-main); margin: 0;">Expenses: <?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h2>
                
                <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
                    <select name="month" class="form-control" style="width: auto; padding: 0.4rem;">
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?php echo sprintf("%02d", $m); ?>" <?php if($m == $month) echo 'selected'; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1, date('Y'))); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-control" style="width: auto; padding: 0.4rem;">
                        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php if($y == $year) echo 'selected'; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" style="padding: 0.4rem 1rem;">Filter</button>
                </form>
            </div>

            <div style="background-color: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #ef4444;">
                <span style="font-weight: 500; color: var(--text-muted);">Total for Period: </span>
                <span style="font-size: 1.2rem; font-weight: 700; color: #dc2626;"><?php echo function_exists('format_money') ? format_money($total_filtered) : '$' . number_format($total_filtered, 2); ?></span>
            </div>

            <?php if (empty($expenses)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <p>No expenses recorded for this period.</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($expense['expense_date'])); ?></td>
                                <td><span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.85rem;"><?php echo htmlspecialchars($expense['category']); ?></span></td>
                                <td style="color: var(--text-muted); font-size: 0.9rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($expense['description']); ?>">
                                    <?php echo htmlspecialchars($expense['description'] ?: '-'); ?>
                                </td>
                                <td style="font-weight: 600; color: #dc2626;">
                                    <?php echo function_exists('format_money') ? format_money($expense['amount']) : '$' . number_format($expense['amount'], 2); ?>
                                </td>
                                <td>
                                    <form action="expenses.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_expense">
                                        <input type="hidden" name="id" value="<?php echo $expense['id']; ?>">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.9rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
