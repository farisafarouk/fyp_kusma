<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (isset($_GET['clear'])) {
    $clear = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    $clear->bind_param("i", $user_id);
    $clear->execute();
    header("Location: notification_cust.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notifications</title>
   
    <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
    <link rel="stylesheet" href="../../../assets/css/customer_notifications.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../customer_navbar.php'; ?>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><i class="fas fa-bell"></i> My Notifications</h1>
            <p>Below are your latest messages. You can clear them anytime.</p>
        </header>

        <div class="dashboard-sections">
            <div class="dashboard-card" style="width: 100%;">
                <div class="text-right" style="text-align: right; margin-bottom: 15px;">
                    <a href="?clear=1" class="dashboard-btn logout-btn" onclick="return confirm('Clear all notifications?')">Clear All</a>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <ul class="notification-list">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <li class="notification-item">
                                <div class="notification-message"><?= htmlspecialchars($row['message']) ?></div>
                                <div class="notification-timestamp"><?= date("F j, Y g:i A", strtotime($row['created_at'])) ?></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No notifications found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
