<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_GET['payment_id'])) {
    echo "Invalid payment ID.";
    exit();
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
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    echo "Receipt not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt - KUSMA</title>
    <link rel="stylesheet" href="../../../assets/css/receipt.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="receipt-wrapper">
        <div class="receipt-container">
            <div class="ripped-paper top"></div>

            <div class="receipt-header">
                <img src="../../../assets/img/kusmaa.png" alt="KUSMA Logo" class="logo">
                <h1>Payment Successful 🎉</h1>
                <p>Welcome to <strong>KUSMA Premium</strong></p>
                <p class="invoice">Invoice #: <strong>#INV<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></strong></p>
            </div>

            <div class="receipt-body">
                <div class="section">
                    <h3>Billed To</h3>
                    <p><?= htmlspecialchars($payment['user_name']) ?><br>
                        <?= htmlspecialchars($payment['email']) ?><br>
                        <?= htmlspecialchars($payment['address']) ?>,<br>
                        <?= htmlspecialchars($payment['city']) ?>, <?= htmlspecialchars($payment['state']) ?> <?= htmlspecialchars($payment['postcode']) ?>
                    </p>
                </div>

                <div class="section">
                    <h3>Plan</h3>
                    <p><strong>KUSMA Premium Access</strong></p>
                    <p>RM<?= number_format($payment['amount'], 2) ?></p>
                </div>

                <div class="section">
                    <h3>Referral Code</h3>
                    <p><?= $payment['referral_code'] ? htmlspecialchars($payment['referral_code']) : 'N/A' ?></p>
                </div>

                <div class="section">
                    <h3>Payment Date</h3>
                    <p><?= date("F j, Y, g:i A", strtotime($payment['payment_date'])) ?></p>
                </div>

                <div class="section">
                    <h3>Issuer</h3>
                    <p><strong>Konsortium Usahawan Madani (KUSMA)</strong><br>
                        A-02-5, Setiawangsa Business Suites,<br>
                        Taman Setiawangsa, 54200<br>
                        Wilayah Persekutuan Kuala Lumpur
                    </p>
                </div>
            </div>

            <div class="ripped-paper bottom"></div>

            <div class="receipt-footer">
                <p>This is a simulated receipt for demonstration purposes only.</p>
                <div class="receipt-actions">
                    <a href="generate_receipt_pdf.php?payment_id=<?= $payment_id ?>" class="btn-download">⬇ Download PDF</a>
                    <a href="../recommendations.php" class="btn-view">→ Access Full Recommendations</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
