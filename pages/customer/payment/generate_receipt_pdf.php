<?php
require '../../../vendor/autoload.php'; // Composer autoload
require_once '../../../config/database.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['payment_id'])) {
    die("Invalid Payment ID");
}

$payment_id = (int)$_GET['payment_id'];

$sql = "
SELECT p.*, u.name AS user_name, pd.email, pd.address, pd.city, pd.state, pd.postcode
FROM payments p
JOIN users u ON p.user_id = u.id
JOIN personal_details pd ON pd.user_id = u.id
WHERE p.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    die("Receipt not found.");
}

// Path for logo (absolute path)
$logoPath = $_SERVER['DOCUMENT_ROOT'] . "/fyp_kusma/assets/img/kusmaa.png";

// Ensure file exists
if (!file_exists($logoPath)) {
    die("Logo file not found at: " . $logoPath);
}

// Create DomPDF instance
$options = new Options();
$options->set('isRemoteEnabled', true); // Allow remote resources
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);

// Format the HTML for the receipt
$html = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            padding: 20px;
            max-width: 375px;
            margin: 0 auto;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 1.4rem;
            margin: 10px 0;
            font-family: 'Roboto', sans-serif;
        }

        .header p {
            font-size: 0.9rem;
            color: #444;
            font-family: 'Roboto', sans-serif;
        }

        .section {
            margin-bottom: 15px;
        }

        .section h3 {
            font-size: 14px;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .section p {
            margin: 0;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
        }

        .footer p {
            margin: 0;
        }

        .company-info {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 15px;
        }

        .company-info h3 {
            margin-bottom: 5px;
            text-align: center;
            font-size: 14px;
        }

        .company-info p {
            margin: 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='header'>
        <img src='file://{$logoPath}' alt='KUSMA'>
        <h1>Official Receipt</h1>
        <p>Payment ID: <strong>#INV" . str_pad($payment['id'], 5, '0', STR_PAD_LEFT) . "</strong></p>
    </div>

    <div class='company-info'>
        <h3>Konsortium Usahawan Madani (KUSMA)</h3>
        <p>A - 02 - 5, Setiawangsa Business Suites, Taman Setiawangsa,</p>
        <p>54200, Wilayah Persekutuan Kuala Lumpur</p>
    </div>

    <div class='section'>
        <h3>Billed To:</h3>
        <p>{$payment['user_name']}</p>
        <p>{$payment['email']}</p>
        <p>{$payment['address']}, {$payment['city']},</p>
        <p>{$payment['state']} {$payment['postcode']}</p>
    </div>

    <div class='section'>
        <h3>Plan:</h3>
        <p><strong>KUSMA Premium</strong> — RM" . number_format($payment['amount'], 2) . "</p>
    </div>

    <div class='section'>
        <h3>Referral Code:</h3>
        <p>" . ($payment['referral_code'] ? htmlspecialchars($payment['referral_code']) : 'N/A') . "</p>
    </div>

    <div class='section'>
        <h3>Payment Date:</h3>
        <p>" . date("F j, Y, g:i A", strtotime($payment['payment_date'])) . "</p>
    </div>

    <div class='footer'>
        <p>This is a simulated receipt for testing purposes only.</p>
        <p>Thank you for supporting KUSMA.</p>
    </div>
</body>
</html>
";

// Load HTML content into DomPDF
$dompdf->loadHtml($html);

// Set paper size (A5)
$dompdf->setPaper('A5', 'portrait');

// Render PDF (first pass)
$dompdf->render();

// Stream the generated PDF (output to browser)
$dompdf->stream("receipt_KUSMA_{$payment_id}.pdf", ["Attachment" => false]);

?>
