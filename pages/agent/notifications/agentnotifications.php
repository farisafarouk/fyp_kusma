<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle clear
if (isset($_GET['clear'])) {
    $clear = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
    $clear->bind_param("i", $user_id);
    $clear->execute();
    header("Location: agentnotifications.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Notifications</title>
    <link rel="stylesheet" href="../../../assets/css/agent_sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/agent_referral.css">
    <link rel="stylesheet" href="../../../assets/css/agent_notifications.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php include '../agentsidebar.php'; ?>

    <main class="dashboard-content">
        <section class="dashboard-section">
        <h1><i class="fas fa-bell"></i> My Notifications</h1>
<p>Below are your latest messages. You can clear them anytime.</p>


            <div class="text-right" style="text-align: right; margin-bottom: 20px;">


                <a href="?clear=1" class="dashboard-btn delete" onclick="return confirm('Clear all notifications?')">Clear All</a>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <ul class="notification-list">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li>
                            <div class="notification-message"><?= htmlspecialchars($row['message']) ?></div>
                            <div class="notification-timestamp"><?= date("F j, Y g:i A", strtotime($row['created_at'])) ?></div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No notifications found.</p>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
