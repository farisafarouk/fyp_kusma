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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade to Premium - KUSMA</title>
    <link rel="stylesheet" href="../../../assets/css/upgrade.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="container">
        <section class="plan-compare">
            <a href="../recommendations.php" class="back-icon">&larr;</a>
            <h2 class="section-title">Choose Your Plan</h2>
            <div class="plan-boxes">
                <div class="plan free">
                    <h3>Free Plan</h3>
                    <ul>
                        <li>🔍 View up to 2 recommendations</li>
                        <li>🚫 No access to full recommendation features</li>
                        <li>🚫 Cannot view full government-linked program list</li>
                        <li>🚫 Limited personalization</li>
                    </ul>
                </div>
                <div class="plan premium">
                    <h3>Premium Plan</h3>
                    <p class="price">RM99.90 <span>/ year</span></p>
                    <ul>
                        <li>✅ Unlimited program recommendations</li>
                        <li>✅ Full access to personalized results</li>
                        <li>✅ Access all government-linked funding/grant programs</li>
                        <li>✅ E-invoice download after payment</li>
                        <li>✅ Early access to future upgrades</li>
                    </ul>
                </div>
            </div>
            <div class="upgrade-action">
                <a href="simulate_gateway.php" class="btn">Upgrade to Premium</a>
            </div>
        </section>
    </div>
</body>

</html>
