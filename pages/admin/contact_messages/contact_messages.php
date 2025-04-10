<?php
require_once '../../../config/database.php'; // Include your database connection

// Fetch contact messages from the database
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
    <link rel="stylesheet" href="../../../assets/css/admindashboard.css"> <!-- Main Admin Dashboard CSS -->
    <link rel="stylesheet" href="../../../assets/css/adminsidebar.css"> <!-- Sidebar-specific CSS -->
    <link rel="stylesheet" href="../../../assets/css/contactmessages.css"> <!-- Contact Messages CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../adminsidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-content">
            <h1><i class="fas fa-envelope"></i> Contact Us Messages</h1>
            <table class="contact-table">
                <thead>
                    <tr>
                       
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                               
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-messages">No messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
