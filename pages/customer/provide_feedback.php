<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch consultants for selection
$sqlConsultants = "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM consultants";
$resultConsultants = $conn->query($sqlConsultants);
$consultants = $resultConsultants->fetch_all(MYSQLI_ASSOC);

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $consultant_id = $type === 'Consultant' ? $_POST['consultant_id'] : null;
    $feedback = $_POST['feedback'];
    $rating = $_POST['rating'];

    $sqlInsertFeedback = "
        INSERT INTO feedbacks (user_id, consultant_id, type, feedback, rating)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmtInsert = $conn->prepare($sqlInsertFeedback);
    $stmtInsert->bind_param("iissi", $user_id, $consultant_id, $type, $feedback, $rating);
    if ($stmtInsert->execute()) {
        $successMessage = "Thank you for your feedback!";
    } else {
        $errorMessage = "Something went wrong. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/provide_feedback.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Provide Feedback</h1>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label for="type" class="form-label">Feedback Type</label>
                <select id="type" name="type" class="form-select" required onchange="toggleConsultantSelect(this.value)">
                    <option value="System">System Feedback</option>
                    <option value="Consultant">Consultant Feedback</option>
                </select>
            </div>

            <div class="mb-3" id="consultantSelect" style="display: none;">
                <label for="consultant_id" class="form-label">Select Consultant</label>
                <select id="consultant_id" name="consultant_id" class="form-select">
                    <option value="">-- Select a Consultant --</option>
                    <?php foreach ($consultants as $consultant): ?>
                        <option value="<?= $consultant['id'] ?>"><?= htmlspecialchars($consultant['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="rating" class="form-label">Rating (1-5)</label>
                <select id="rating" name="rating" class="form-select" required>
                    <option value="1">1 - Very Poor</option>
                    <option value="2">2 - Poor</option>
                    <option value="3">3 - Average</option>
                    <option value="4">4 - Good</option>
                    <option value="5">5 - Excellent</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="feedback" class="form-label">Feedback</label>
                <textarea id="feedback" name="feedback" class="form-control" rows="5" placeholder="Write your feedback here..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit Feedback</button>
        </form>
    </div>

    <script>
        function toggleConsultantSelect(value) {
            const consultantSelect = document.getElementById('consultantSelect');
            consultantSelect.style.display = value === 'Consultant' ? 'block' : 'none';
        }
    </script>
</body>
</html>
