<?php
/**
 * Financial Intelligence Functions for EduManager
 */

require_once __DIR__ . '/functions.php';

/**
 * FEATURE 1: Expected Monthly Revenue
 * Calculated based on the monthly_fee of all active students
 */
function get_expected_monthly_revenue($pdo) {
    $stmt = $pdo->query("SELECT SUM(monthly_fee) FROM students WHERE status = 'active'");
    return (float)($stmt->fetchColumn() ?: 0);
}

/**
 * FEATURE 2: Collected Revenue
 * Sum of payments received in the current month/year
 */
function get_collected_monthly_revenue($pdo) {
    $month = date('m');
    $year = date('Y');
    $stmt = $pdo->prepare("
        SELECT SUM(amount) 
        FROM payments 
        WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?
    ");
    $stmt->execute([$month, $year]);
    return (float)($stmt->fetchColumn() ?: 0);
}

/**
 * FEATURE 3: Lost Revenue (Outstanding Debt)
 * Logic: (months_enrolled) - (months_paid)
 */
function get_total_outstanding_debt($pdo) {
    $stmt = $pdo->query("SELECT id, monthly_fee, registration_date FROM students WHERE status = 'active'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_debt = 0;
    foreach ($students as $student) {
        $late_months = calculate_late_months_logic($student, $pdo);
        $total_debt += ($late_months * $student['monthly_fee']);
    }
    return (float)$total_debt;
}

/**
 * Helper: Calculate late months for a student
 */
function calculate_late_months_logic($student, $pdo) {
    $reg_date = new DateTime($student['registration_date']);
    $today = new DateTime();
    
    // Total months including current
    $interval = $reg_date->diff($today);
    $total_months = ($interval->y * 12) + $interval->m + 1;

    // Total payments made (count unique months paid)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT DATE_FORMAT(payment_date, '%Y-%m')) FROM payments WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    $payments_made = (int)$stmt->fetchColumn();

    return max(0, $total_months - $payments_made);
}

/**
 * FEATURE 5: Most Late Students Table Ranking
 */
function get_late_students_ranking($pdo, $limit = 5) {
    $stmt = $pdo->query("SELECT id, full_name, monthly_fee, registration_date FROM students WHERE status = 'active'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $ranking = [];
    foreach ($students as $student) {
        $late_months = calculate_late_months_logic($student, $pdo);
        if ($late_months > 0) {
            $ranking[] = [
                'full_name' => $student['full_name'],
                'late_months' => $late_months,
                'total_debt' => $late_months * $student['monthly_fee']
            ];
        }
    }
    
    // Sort by total_debt DESC
    usort($ranking, function($a, $b) {
        return $b['total_debt'] <=> $a['total_debt'];
    });
    
    return array_slice($ranking, 0, $limit);
}

/**
 * FEATURE 6: Monthly Revenue Chart Data (Last 6 Months)
 */
function get_financial_chart_data($pdo) {
    $labels = [];
    $values = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $labels[] = date('M Y', strtotime("-$i months"));
        
        $stmt = $pdo->prepare("
            SELECT SUM(amount) 
            FROM payments 
            WHERE payment_date LIKE ?
        ");
        $stmt->execute(["$date%"]);
        $values[] = (float)($stmt->fetchColumn() ?: 0);
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

/**
 * RESTORED FEATURE: Student Payment Status (Paid vs Unpaid for current month)
 */
function get_student_payment_status_data($pdo) {
    $month = date('m');
    $year = date('Y');
    
    // Total Active Students
    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'active'");
    $total_active = (int)$stmt->fetchColumn();
    
    // Students who paid this month
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT student_id) 
        FROM payments 
        WHERE MONTH(payment_date) = ? AND YEAR(payment_date) = ?
    ");
    $stmt->execute([$month, $year]);
    $paid_count = (int)$stmt->fetchColumn();
    
    $unpaid_count = max(0, $total_active - $paid_count);
    
    // Default if no data
    if ($total_active === 0) {
        return [
            'labels' => ['No Students'],
            'values' => [0, 0]
        ];
    }

    return [
        'labels' => ['Paid Students', 'Unpaid Students'],
        'values' => [$paid_count, $unpaid_count]
    ];
}

/**
 * RESTORED FEATURE: Payment Method Distribution
 */
function get_payment_method_stats($pdo) {
    $stmt = $pdo->query("
        SELECT payment_method, SUM(amount) as total 
        FROM payments 
        GROUP BY payment_method 
        ORDER BY total DESC
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $labels = [];
    $values = [];
    
    if (empty($results)) {
        return [
            'labels' => ['No Data'],
            'values' => [0]
        ];
    }

    foreach ($results as $row) {
        $labels[] = ucfirst($row['payment_method'] ?: 'Other');
        $values[] = (float)$row['total'];
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}
