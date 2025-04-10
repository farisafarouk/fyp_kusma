<?php
session_start();
require_once '../../../config/database.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
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

    // Validate
    $valid_months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    $current_year = (int)date('Y');
    $future_years = range($current_year, $current_year + 10);

    if (!preg_match('/^[0-9]{16}$/', $card_number)) {
        $error = "Invalid card number. Must be 16 digits.";
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $card_name)) {
        $error = "Card name must only contain letters and spaces.";
    } elseif (!in_array($exp_month, $valid_months)) {
        $error = "Invalid expiration month.";
    } elseif (!in_array((int)$exp_year, $future_years)) {
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

        $conn->query("UPDATE users SET subscription_status = 'subscribed' WHERE id = $user_id");

        if ($referral_code) {
            $referral = $conn->prepare("
                SELECT user_id FROM agents 
                WHERE user_id = (SELECT id FROM users WHERE id = ?)
            ");
            $referral->bind_param("i", $user_id);
            $referral->execute();
            $agent = $referral->get_result()->fetch_assoc();

            if ($agent) {
                $agent_id = $agent['user_id'];
                $log = $conn->prepare("INSERT INTO referrals (agent_user_id, referred_user_id, payment_id) VALUES (?, ?, ?)");
                $log->bind_param("iii", $agent_id, $user_id, $payment_id);
                $log->execute();
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
    <meta charset="UTF-8">
    <title>KUSMA Payment Checkout</title>
    <link rel="stylesheet" href="../../../assets/css/simulate_gateway.css">
</head>
<body>
    <div class="container">
        <form method="POST">
            <a href="../payment/upgrade.php" class="back-icon">&larr;</a>

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
                        <input type="text" name="card_name" placeholder="Mr. <?= $prefill('first_name') ?>" required>
                    </div>
                    <div class="input-box">
                        <span>Credit Card Number :</span>
                        <input type="text" name="card_number" maxlength="16" required>
                    </div>
                    <div class="input-box">
                        <span>Exp. Month :</span>
                        <input type="text" name="exp_month" placeholder="August" required>
                    </div>
                    <div class="flex">
                        <div class="input-box">
                            <span>Exp. Year :</span>
                            <input type="number" name="exp_year" placeholder="<?= date('Y') + 1 ?>" required>
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
</body>
</html>
