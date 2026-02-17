<?php
echo "==========================================\n";
echo "  STUDENT PAYMENT SYSTEM - DATABASE SETUP \n";
echo "==========================================\n\n";

// Configuration
$db_host = 'localhost';
$db_user_root = 'root';
$db_pass_empty = '';
$db_pass_root = 'root';
$db_name = 'student_payment_system';

// New user config
$new_user = 'student_admin';
$new_pass = 'password123';

echo "1. Attempting to connect to MySQL...\n";

// Try connecting with empty password
mysqli_report(MYSQLI_REPORT_OFF); // Disable exception throwing for connection attempts
$conn = @new mysqli($db_host, $db_user_root, $db_pass_empty);

if ($conn->connect_error) {
    echo "[INFO] Connection with empty password failed. Trying password 'root'...\n";
    $conn = @new mysqli($db_host, $db_user_root, $db_pass_root);
    
    if ($conn->connect_error) {
        echo "[ERROR] Could not connect to MySQL. Please check your XAMPP installation.\n";
        echo "Error: " . $conn->connect_error . "\n";
        exit(1);
    }
}

echo "[SUCCESS] Connected to MySQL successfully.\n\n";
echo "2. creating User '$new_user' and Database '$db_name'...\n";

// Create Database
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo " - Database '$db_name' created successfully (or already exists).\n";
} else {
    echo "[ERROR] Error creating database: " . $conn->error . "\n";
    exit(1);
}

// Create User and Grant Privileges
$queries = [
    "CREATE USER IF NOT EXISTS '$new_user'@'localhost' IDENTIFIED BY '$new_pass'",
    "CREATE USER IF NOT EXISTS '$new_user'@'127.0.0.1' IDENTIFIED BY '$new_pass'",
    "GRANT ALL PRIVILEGES ON $db_name.* TO '$new_user'@'localhost'",
    "GRANT ALL PRIVILEGES ON $db_name.* TO '$new_user'@'127.0.0.1'",
    "FLUSH PRIVILEGES"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        // success, silent
    } else {
        echo "[WARNING] Error executing query: $query\nError: " . $conn->error . "\n";
    }
}

echo " - User '$new_user' configured successfully.\n\n";

// Select the database
$conn->select_db($db_name);

echo "3. Importing Schema from database.sql...\n";

if (!file_exists('database.sql')) {
    echo "[ERROR] database.sql file not found!\n";
    exit(1);
}

$sql_content = file_get_contents('database.sql');

// Execute multi query
if ($conn->multi_query($sql_content)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    if ($conn->errno) {
         echo "[ERROR] Error importing schema: " . $conn->error . "\n";
         exit(1);
    }
} else {
    echo "[ERROR] Error importing schema: " . $conn->error . "\n";
    exit(1);
}

echo "\n[SUCCESS] Database setup complete!\n";
echo "You can now login with: admin / admin123\n";

$conn->close();
?>
