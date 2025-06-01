<?php
session_start();
require_once '../../../config/database.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM personal_details WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$referral_code = $_GET['ref'] ?? '';
$error = '';
$prefill = function($field, $fallback = '') use ($user) {
    return htmlspecialchars($user[$field] ?? $fallback);
};

$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$years = range(date('Y'), date('Y') + 10);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $postcode = trim($_POST['postcode']);
    $card_name = trim($_POST['card_name']);
    $card_number = preg_replace('/\D/', '', $_POST['card_number']);
    $exp_month = trim($_POST['exp_month']);
    $exp_year = trim($_POST['exp_year']);
    $cvv = trim($_POST['cvv']);
    $referral_code = trim($_POST['referral_code']);

    if (!preg_match('/^[0-9]{16}$/', $card_number)) {
        $error = "Invalid card number. Must be 16 digits.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $card_name)) {
        $error = "Card name must only contain letters and spaces.";
    } elseif (!in_array($exp_month, $months)) {
        $error = "Invalid expiration month.";
    } elseif (!in_array((int)$exp_year, $years)) {
        $error = "Invalid expiration year.";
    } elseif (!preg_match('/^[0-9]{3}$/', $cvv)) {
        $error = "Invalid CVV. Must be 3 digits.";
    }

    if (!$error) {
        $amount = 99.90;
        $insert = $conn->prepare("INSERT INTO payments (user_id, amount, referral_code) VALUES (?, ?, ?)");
        $insert->bind_param("ids", $user_id, $amount, $referral_code);
        $insert->execute();
        $payment_id = $insert->insert_id;

        $conn->query("
            UPDATE users 
            SET 
                subscription_status = 'subscribed',
                subscription_expiry = IF(
                    subscription_expiry IS NOT NULL AND subscription_expiry > NOW(),
                    DATE_ADD(subscription_expiry, INTERVAL 1 YEAR),
                    DATE_ADD(NOW(), INTERVAL 1 YEAR)
                ),
                subscription_updated_at = NOW()
            WHERE id = $user_id
        ");

        if ($referral_code) {
            $ref_check = $conn->prepare("SELECT user_id FROM agents WHERE referral_code = ?");
            if (!$ref_check) {
                die("SQL prepare failed: " . $conn->error);
            }

            $ref_check->bind_param("s", $referral_code);
            $ref_check->execute();
            $ref_result = $ref_check->get_result();

            if ($ref_result->num_rows > 0) {
                $agent = $ref_result->fetch_assoc();
                $agent_id = $agent['user_id'];

                $log = $conn->prepare("INSERT INTO referrals (agent_id, customer_id, referral_code, status, commission_status) 
                                       VALUES (?, ?, ?, 'approved', 'paid')");
                $log->bind_param("iis", $agent_id, $user_id, $referral_code);
                $log->execute();

                $conn->query("UPDATE agents SET referral_earnings = referral_earnings + 1.00 WHERE user_id = $agent_id");
            }
        }

        header("Location: receipt.php?payment_id=" . $payment_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>KUSMA Payment Checkout</title>
    <link rel="stylesheet" href="../../../assets/css/simulate_gateway.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<div class="container">
    <form method="POST" onsubmit="return confirmSubmit(event)">
        <a href="upgrade.php" onclick="confirmBack(event)" class="back-icon">&larr;</a>

        <div class="row">
            <div class="column">
                <h3 class="title">Billing Address</h3>
                <div class="input-box">
                    <span>Full Name :</span>
                    <input type="text" name="full_name" value="<?= $prefill('first_name') . ' ' . $prefill('last_name') ?>" required>
                </div>
                <div class="input-box">
                    <span>Email :</span>
                    <input type="email" name="email" value="<?= $prefill('email') ?>" required>
                </div>
                <div class="input-box">
                    <span>Address :</span>
                    <input type="text" name="address" value="<?= $prefill('address') ?>" required>
                </div>
                <div class="input-box">
                    <span>City :</span>
                    <input type="text" name="city" value="<?= $prefill('city') ?>" required>
                </div>
                <div class="flex">
                    <div class="input-box">
                        <span>State :</span>
                        <input type="text" name="state" value="<?= $prefill('state') ?>" required>
                    </div>
                    <div class="input-box">
                        <span>Zip Code :</span>
                        <input type="text" name="postcode" value="<?= $prefill('postcode') ?>" required>
                    </div>
                </div>
                <div class="input-box">
                    <span>Referral Code (optional):</span>
                    <input type="text" name="referral_code" value="<?= htmlspecialchars($referral_code) ?>">
                </div>
            </div>

            <div class="column">
                <h3 class="title">Payment</h3>
                <div class="input-box">
                    <span>Cards Accepted :</span>
                    <img src="../../../assets/img/imgcards.png" alt="Cards Accepted">
                </div>
                <div class="input-box">
                    <span>Name On Card :</span>
                    <input type="text" name="card_name" value="<?= $prefill('first_name') . ' ' . $prefill('last_name') ?>" required>
                </div>
                <div class="input-box">
                    <span>Credit Card Number :</span>
                    <input type="text" name="card_number" maxlength="16" required>
                </div>
                <div class="input-box">
                    <span>Exp. Month :</span>
                    <select name="exp_month" required>
                        <option value="">-- Select Month --</option>
                        <?php foreach ($months as $month): ?>
                            <option value="<?= $month ?>"><?= $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex">
                    <div class="input-box">
                        <span>Exp. Year :</span>
                        <select name="exp_year" required>
                            <option value="">-- Select Year --</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?= $year ?>"><?= $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-box">
                        <span>CVV :</span>
                        <input type="number" name="cvv" maxlength="3" required>
                    </div>
                </div>
                <div class="input-box">
                    <strong>Total: RM99.90</strong>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert-danger">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <button type="submit" class="btn">Submit Payment</button>
    </form>
</div>
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
  <div class="spinner"></div>
  <p>Processing your payment...</p>
</div>


</body>
<script>
function confirmSubmit(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Confirm Payment',
    text: 'Are you sure you want to proceed with payment?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#5e72e4',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Yes, pay now!',
    preConfirm: () => {
      // Show the loader AFTER SweetAlert closes
      return new Promise((resolve) => {
        Swal.showLoading(); // optional: show spinner inside modal
        setTimeout(() => {
          document.getElementById('loadingOverlay').style.display = 'flex';
          resolve();
        }, 300);
      });
    }
  }).then((result) => {
    if (result.isConfirmed) {
      setTimeout(() => {
        e.target.submit(); // trigger actual form submission
      }, 800); // slight delay to let loader show
    }
  });
  return false;
}

function confirmBack(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Go Back?',
    text: 'Are you sure you want to cancel your payment? Changes will be unsaved.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#5e72e4',
    cancelButtonColor: '#aaa',
    confirmButtonText: 'Yes, go back'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = '../payment/upgrade.php';
    }
  });
}

</script>
</html>
