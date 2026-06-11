<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if composer vendor autoload exists
if (!file_exists('../vendor/autoload.php')) {
    die("Error: DomPDF is not installed. Please run 'composer require dompdf/dompdf' in the project root.");
}
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$payment_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$payment_id) {
    die("Invalid payment ID.");
}

// Fetch Payment Data securely using Prepared Statements
try {
    $stmt = $pdo->prepare("
        SELECT p.*, s.full_name, s.subject, s.phone
        FROM payments p 
        JOIN students s ON p.student_id = s.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        die("Payment record not found.");
    }
} catch (PDOException $e) {
    die("Error fetching payment: " . $e->getMessage());
}

// Construct PDF HTML Template Details
$_amount = number_format($payment['amount'], 2) . ' MAD';
$_date = date('F d, Y', strtotime($payment['payment_date']));
$_receipt_no = htmlspecialchars($payment['receipt_number']);
$_student_name = htmlspecialchars($payment['full_name']);
$_subject = htmlspecialchars($payment['subject']);
$_method = ucfirst(htmlspecialchars($payment['payment_method']));

// HTML Template
$html = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - {$_receipt_no}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .receipt-container {
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #2563eb;
            padding: 40px;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 16px;
        }
        .details-section {
            margin-bottom: 40px;
        }
        .details-section table {
            width: 100%;
        }
        .details-section td {
            padding: 8px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            display: inline-block;
            width: 130px;
        }
        .table-payment {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .table-payment th, .table-payment td {
            border: 1px solid #d1d5db;
            padding: 12px;
            text-align: left;
        }
        .table-payment th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .text-right {
            text-align: right !important;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 14px;
            color: #4b5563;
        }
        .signature-area {
            margin-top: 60px;
            display: inline-block;
            border-top: 1px solid #9ca3af;
            padding-top: 10px;
            width: 250px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header">
        <h1>EduManager Center</h1>
        <p>Invoice / Receipt</p>
    </div>

    <div class="details-section">
        <table>
            <tr>
                <td style="width: 50%;">
                    <div><span class="info-label">Transaction ID:</span> {$_receipt_no}</div>
                    <div><span class="info-label">Payment Date:</span> {$_date}</div>
                    <div><span class="info-label">Payment Method:</span> {$_method}</div>
                </td>
                <td style="width: 50%;">
                    <div><span class="info-label">Receipt To:</span></div>
                    <div><strong>{$_student_name}</strong></div>
                    <div>Course: {$_subject}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-payment">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Monthly Tuition Fee - {$_subject}</td>
                <td class="text-right">{$_amount}</td>
            </tr>
            <tr class="total-row">
                <td class="text-right">Total Paid:</td>
                <td class="text-right">{$_amount}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>This is a system generated invoice/receipt.</p>
        <p>Thank you for your business!</p>
        <div class="signature-area">
            Authorized Signature
        </div>
    </div>
</div>

</body>
</html>
EOD;

// Setup DomPDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Setup Paper size (A4) and Orientation (portrait)
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Clean output buffer before sending headers
if (ob_get_length()) {
    ob_end_clean();
}

// Generate an appropriate file name
$safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $payment['full_name']);
$filename = "Payment_Receipt_{$safe_name}.pdf";

// Output the generated PDF to Browser (Attachment => 1 forces download, 0 opens in browser)
$dompdf->stream($filename, ["Attachment" => 1]);
exit();
