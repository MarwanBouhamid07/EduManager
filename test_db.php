<?php
// Save this as test_db.php and run it in your browser

$host = '127.0.0.1';
$username = 'root';
// TRY THESE PASSWORDS one by one if connection fails:
$passwords_to_try = ['', 'root', 'mysql', 'admin'];

echo "<h1>Database Connection Test</h1>";

foreach ($passwords_to_try as $password) {
    try {
        echo "<p>Trying user: <b>$username</b> | password: <b>'$password'</b> ... ";
        
        $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<span style='color:green; font-weight:bold;'>CONNECTED! ✅</span></p>";
        echo "<p>Your correct password is: <b>'$password'</b></p>";
        echo "<p>Running: USE student_payment_system...</p>";
        
        $pdo->exec("USE student_payment_system");
        echo "<p style='color:green;'>Database 'student_payment_system' found! ✅</p>";
        exit(); // Stop after success
        
    } catch (PDOException $e) {
        echo "<span style='color:red;'>FAILED ❌</span> (" . $e->getMessage() . ")</p>";
    }
}

echo "<h2>Conclusion:</h2>";
echo "<p>Could not connect with common passwords. Please check your MySQL setup in XAMPP/MAMP.</p>";
?>
