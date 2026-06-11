<?php
require_once 'config/database.php';

try {
    // Check if 'grade' exists and 'subject' does not
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_grade = false;
    $has_subject = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'grade') $has_grade = true;
        if ($col['Field'] === 'subject') $has_subject = true;
    }
    
    if ($has_grade && !$has_subject) {
        echo "Renaming 'grade' to 'subject'...\n";
        $pdo->exec("ALTER TABLE students CHANGE COLUMN grade subject VARCHAR(100) NOT NULL");
        echo "Successfully renamed 'grade' to 'subject'.\n";
    } else if ($has_subject) {
        echo "'subject' column already exists.\n";
    } else {
        echo "Neither 'grade' nor 'subject' found. Please check table structure.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
