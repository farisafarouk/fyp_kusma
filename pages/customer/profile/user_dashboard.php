<?php
session_start();
require_once '../../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile data
$sqlUser = "
    SELECT 
        CONCAT(pd.first_name, ' ', pd.last_name) AS name, 
        pd.birthdate, 
        pd.gender, 
        pd.bumiputera_status, 
        pd.oku_status, 
        bd.business_type 
    FROM personal_details pd
    LEFT JOIN business_details bd ON pd.user_id = bd.user_id
    WHERE pd.user_id = ?
";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc() ?? [];

// Set user name or default to "Guest"
$name = $userProfile['name'] ?? 'Guest';

// Additional logic...
?>



// Fetch personalized recommendations
$recommendations = [];
if (!empty($userProfile)) {
    $birth_date = new DateTime($userProfile['birthdate'] ?? '1900-01-01');
    $current_date = new DateTime();
    $age = $current_date->diff($birth_date)->y;

    // Eligibility filters
    $gender = $userProfile['gender'] ?? null;
    $bumiputera_status = $userProfile['bumiputera_status'] ?? null;
    $oku_status = $userProfile['oku_status'] ?? false;

    // Fetch programs
    $sqlPrograms = "
        SELECT p.name AS program_name, p.description, p.resource_types, p.loan_amount_range, p.application_link, 
               a.logo_url AS agency_logo 
        FROM programs p 
        JOIN agencies a ON p.agency_id = a.id
    ";
    $result = $conn->query($sqlPrograms);
    if ($result) {
        while ($program = $result->fetch_assoc()) {
            $criteria = json_decode($program['eligibility_criteria'], true);

            // Check criteria
            if (
                isset($criteria['age']) &&
                ($age >= $criteria['age'][0] && $age <= $criteria['age'][1]) &&
                (empty($criteria['gender']) || in_array($gender, $criteria['gender'])) &&
                (empty($criteria['bumiputera_status']) || $criteria['bumiputera_status'] === $bumiputera_status) &&
                (empty($criteria['oku_status']) || $criteria['oku_status'] === $oku_status)
            ) {
                $recommendations[] = $program;
            }
        }
    }
}

// Example notifications
$notifications = [
    ['type' => 'success', 'message' => 'Your profile has been updated successfully.'],
    ['type' => 'info', 'message' => 'New recommendations are available for you.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - KUSMA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/customer_dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">KUSMA Dashboard</a>
            <button class="btn btn-outline-primary me-2"><i class="bi bi-person-circle"></i> Profile</button>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#notificationModal">
                <i class="bi bi-bell"></i> Notifications
            </button>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Welcome Message -->
        <header class="text-center mb-5">
            <h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>
        </header>

        <!-- Personalized Recommendations -->
        <section>
            <h2 class="mb-4">Personalized Recommendations</h2>
            <div class="row">
                <?php if (empty($recommendations)): ?>
                    <p class="text-center">No recommendations available. Update your profile for better results.</p>
                <?php else: ?>
                    <?php foreach ($recommendations as $program): ?>
                        <div class="col-md-6 col-lg-4 my-3">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <img src="/fyp_kusma/<?= htmlspecialchars($program['agency_logo']) ?>" alt="Agency Logo" class="agency-logo me-3">
                                    <span><?= htmlspecialchars($program['program_name']) ?></span>
                                </div>
                                <div class="card-body">
                                    <p><?= htmlspecialchars($program['description']) ?></p>
                                    <ul>
                                        <li><strong>Type:</strong> <?= htmlspecialchars($program['resource_types']) ?></li>
                                        <li><strong>Loan Range:</strong> RM<?= htmlspecialchars($program['loan_amount_range']) ?></li>
                                    </ul>
                                    <a href="<?= htmlspecialchars($program['application_link']) ?>" class="btn btn-primary">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Notification Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <?php foreach ($notifications as $notification): ?>
                            <li class="list-group-item"><?= htmlspecialchars($notification['message']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

