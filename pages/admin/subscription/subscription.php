<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Stop plan logic (retain days, update status)
    if (isset($_POST['delete_subscription'])) {
        $res = $conn->query("SELECT subscription_expiry FROM users WHERE id = $user_id");
        $row = $res->fetch_assoc();
        $now = new DateTime();
        $expiry = new DateTime($row['subscription_expiry']);
        $daysLeft = max(0, $expiry > $now ? $expiry->diff($now)->format("%a") : 0);

        $stmt = $conn->prepare("
            UPDATE users 
            SET 
                subscription_status = 'subscription stopped', 
                subscription_remaining_days = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $daysLeft, $user_id);
        $stmt->execute();
    }

    // Extend plan
    if (isset($_POST['extend_subscription'])) {
        $days = (int)$_POST['days'];
        $upload_dir = '../../../uploads/receipts/';
        $receipt_path = '';

        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === 0) {
            $filename = uniqid() . '_' . basename($_FILES['receipt']['name']);
            $receipt_path = $upload_dir . $filename;
            move_uploaded_file($_FILES['receipt']['tmp_name'], $receipt_path);
        }

        $res = $conn->query("SELECT subscription_expiry FROM users WHERE id = $user_id");
        $row = $res->fetch_assoc();
        $new_expiry = new DateTime($row['subscription_expiry'] ?? 'now');
        $new_expiry->modify("+$days days");
        $new_expiry_str = $new_expiry->format('Y-m-d');

        $stmt = $conn->prepare("UPDATE users SET subscription_expiry = ?, subscription_status = 'subscribed', subscription_receipt = ? WHERE id = ?");
        $stmt->bind_param("ssi", $new_expiry_str, $receipt_path, $user_id);
        $stmt->execute();
    }
}

$subscriptions = $conn->query("
    SELECT id, name, email, subscription_status, subscription_expiry, subscription_remaining_days, subscription_receipt 
    FROM users 
    WHERE role = 'customer' 
    ORDER BY subscription_expiry DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription Management</title>
    <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
    <link rel="stylesheet" href="../../../assets/css/admin_subscription.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>
    <main class="dashboard-content">
        <section class="dashboard-section">
            <h1><i class="fas fa-user-cog"></i> Subscription Management</h1>
            <p>Extend, stop, or manage customer subscription plans.</p>

            <table class="contact-table">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                    <th>Days Left</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $subscriptions->fetch_assoc()):
                    $expiry = $row['subscription_expiry'] ? new DateTime($row['subscription_expiry']) : null;
                    $now = new DateTime();
                    $daysLeft = $expiry && $expiry > $now ? $expiry->diff($now)->format("%a") : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <?php
                        if ($row['subscription_status'] === 'subscription stopped') {
                            echo '<span class="subscription-stopped">Subscription Stopped</span>';
                        } elseif ($row['subscription_status'] === 'subscribed') {
                            echo 'Subscribed';
                        } else {
                            echo 'Free';
                        }
                        ?>
                    </td>
                    <td><?= $row['subscription_expiry'] ?? '-' ?></td>
                    <td class="<?= $row['subscription_status'] === 'subscription stopped' ? 'subscription-stopped' : '' ?>">
                        <?php
                        if ($row['subscription_status'] === 'subscription stopped') {
                            echo $row['subscription_remaining_days'] . " days (stopped)";
                        } else {
                            echo $daysLeft . " days";
                        }
                        ?>
                    </td>
                    <td>
                        <div class="button-group">
                            <button class="action-btn edit-btn" onclick="toggleEdit(<?= $row['id'] ?>)">Edit</button>

                            <form method="POST" onsubmit="return confirm('Stop this subscription and retain remaining days?');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_subscription" class="action-btn reset">Stop Plan</button>
                            </form>
                        </div>

                        <div class="edit-panel" id="edit-panel-<?= $row['id'] ?>">
                            <form method="POST" enctype="multipart/form-data" class="edit-form" onsubmit="return confirm('Extend subscription?');">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <label>Extend by:</label>
                                <select name="days" required>
                                    <option value="365">1 Year</option>
                                    <option value="730">2 Years</option>
                                    <option value="1095">3 Years</option>
                                </select>
                                <input type="file" name="receipt" accept="application/pdf" required>
                                <div class="button-group">
                                    <button type="submit" name="extend_subscription" class="action-btn save">Save</button>
                                    <button type="button" class="action-btn cancel" onclick="toggleEdit(<?= $row['id'] ?>)">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<script>
function toggleEdit(id) {
    document.querySelectorAll('.edit-panel').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('edit-panel-' + id);
    if (panel) panel.style.display = 'block';
}
</script>
</body>
</html>

