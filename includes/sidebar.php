<aside class="sidebar">
    <div class="sidebar-header">
        EduManager
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            Dashboard
        </a>
        <a href="students.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
            Students
        </a>
        <a href="payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
            Payments
        </a>
        <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            Reports
        </a>
        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            Settings
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../actions/logout.php" class="nav-link" style="color: #ef4444;" onclick="return confirm('Are you sure you want to logout?');">
            Logout
        </a>
    </div>
</aside>
