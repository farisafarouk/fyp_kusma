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
  <meta charset="UTF-8" />
  <title>Receipt - KUSMA</title>
  <link rel="stylesheet" href="../../../assets/css/receipt.css" />
</head>

<body>
  <div class="receipt-container">
    <div class="receipt-edge-top">
      <svg viewBox="0 0 500 20" preserveAspectRatio="none">
        <path d="M0,10 Q20,0 40,10 T80,10 T120,10 T160,10 T200,10 T240,10 T280,10 T320,10 T360,10 T400,10 T440,10 T480,10 T500,10 L500,0 L0,0 Z" fill="#fff"/>
      </svg>
    </div>

    <div class="receipt-paper">
      <div class="receipt-header">
        <img src="../../../assets/img/kusmaa.png" alt="KUSMA Logo" class="logo" />
        <h1>Official Receipt</h1>
        <p>Payment ID: <strong>#INV<?= str_pad($payment['id'], 5, '0', STR_PAD_LEFT) ?></strong></p>
      </div>

      <div class="receipt-body">
        <h3>Billed To:</h3>
        <p>
          <?= htmlspecialchars($payment['user_name']) ?><br>
          <?= htmlspecialchars($payment['email']) ?><br>
          <?= htmlspecialchars($payment['address']) ?>, <?= htmlspecialchars($payment['city']) ?><br>
          <?= htmlspecialchars($payment['state']) ?> <?= htmlspecialchars($payment['postcode']) ?>
        </p>

        <h3>Plan:</h3>
        <p><strong>KUSMA Premium</strong> — RM<?= number_format($payment['amount'], 2) ?></p>

        <h3>Referral Code:</h3>
        <p><?= $payment['referral_code'] ? htmlspecialchars($payment['referral_code']) : 'N/A' ?></p>

        <h3>Payment Date:</h3>
        <p><?= date("F j, Y, g:i A", strtotime($payment['payment_date'])) ?></p>
      </div>

      <div class="receipt-footer">
        <p class="notice">This is a simulated receipt for testing purposes only.</p>
        <a href="generate_receipt_pdf.php?payment_id=<?= $payment_id ?>" class="btn-download">Download PDF</a>
      </div>
    </div>

    <div class="receipt-edge-bottom">
      <svg viewBox="0 0 500 20" preserveAspectRatio="none">
        <path d="M0,10 Q20,0 40,10 T80,10 T120,10 T160,10 T200,10 T240,10 T280,10 T320,10 T360,10 T400,10 T440,10 T480,10 T500,10 L500,20 L0,20 Z" fill="#fff"/>
      </svg>
    </div>
  </div>
</body>
</html>
