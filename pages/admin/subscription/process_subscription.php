<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;

    if (!$user_id) {
        die("Invalid user ID.");
    }

    if ($action === 'extend') {
        $days = (int)$_POST['days'];
        $upload_dir = '../../../uploads/receipts/';
        $receipt_path = '';

        // Handle receipt upload
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
            $filename = uniqid() . '_' . basename($_FILES['receipt']['name']);
            $receipt_path = $upload_dir . $filename;
            move_uploaded_file($_FILES['receipt']['tmp_name'], $receipt_path);
        }

        // Get current expiry
        $res = $conn->query("SELECT subscription_expiry FROM users WHERE id = $user_id");
        $row = $res->fetch_assoc();
        $current_expiry = $row['subscription_expiry'] ?? null;

        $expiry = new DateTime($current_expiry ?? 'now');
        $expiry->modify("+$days days");
        $new_expiry = $expiry->format('Y-m-d');

        // Update
        $stmt = $conn->prepare("UPDATE users SET subscription_expiry = ?, subscription_status = 'subscribed', subscription_receipt = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_expiry, $receipt_path, $user_id);
        $stmt->execute();

        header("Location: admin_subscription.php");
        exit();

    } elseif ($action === 'stop') {
        // Fetch remaining days
        $res = $conn->query("SELECT subscription_expiry FROM users WHERE id = $user_id");
        $row = $res->fetch_assoc();
        $now = new DateTime();
        $expiry = new DateTime($row['subscription_expiry'] ?? 'now');

        $daysLeft = max(0, $expiry > $now ? $expiry->diff($now)->format("%a") : 0);

        // Update status and save remaining days
        $stmt = $conn->prepare("UPDATE users SET subscription_status = 'subscription stopped', subscription_remaining_days = ? WHERE id = ?");
        $stmt->bind_param("ii", $daysLeft, $user_id);
        $stmt->execute();

        header("Location: subscription.php");

        exit();
    } else {
        die("Invalid action.");
    }
} else {
    header("Location: subscription.php");

    exit();
}
