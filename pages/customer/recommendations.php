<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if ageMatches exists to avoid redeclaration
if (!function_exists('ageMatches')) {
    function ageMatches($age, $criteria) {
        if (empty($criteria['age'])) return true;
        foreach ($criteria['age'] as $range) {
            [$min, $max] = explode('-', $range);
            if ($age >= (int)$min && $age <= (int)$max) {
                return true;
            }
        }
        return false;
    }
}

// Fetch user profile data
$sqlUser = "
    SELECT pd.*, bd.*, er.* 
    FROM personal_details pd
    LEFT JOIN business_details bd ON pd.user_id = bd.user_id
    LEFT JOIN education_resources er ON pd.user_id = er.user_id
    WHERE pd.user_id = ?
";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc();

$programs = [];


if ($userProfile) {
    // Calculate user age
    $birth_date = new DateTime($userProfile['birthdate']);
    $current_date = new DateTime();
    $age = $current_date->diff($birth_date)->y;

    // Prepare eligibility filters
    $gender = $userProfile['gender'];
    $bumiputera_status = $userProfile['bumiputera_status'];
    $oku_status = $userProfile['oku_status'] ? true : false;
    $business_type = $userProfile['business_type'] ?? null;
    $business_experience = $userProfile['business_experience'] ?? 'None';
    $education_type = $userProfile['education_type'] ?? null;
    $certification_level = $userProfile['certification_level'] ?? null;
    $loan_amount_range = $userProfile['preferred_loan_range'] ?? null;

    // Fetch all programs and their corresponding agency logos
    $sqlPrograms = "
        SELECT p.*, a.logo_url AS agency_logo 
        FROM programs p 
        JOIN agencies a ON p.agency_id = a.id
    ";
    $result = $conn->query($sqlPrograms);

    if ($result) {
        while ($program = $result->fetch_assoc()) {
            $criteria = json_decode($program['eligibility_criteria'], true);

            if (
                ageMatches($age, $criteria) &&
                (!isset($criteria['gender']) || in_array($gender, $criteria['gender'])) &&
                (!isset($criteria['bumiputera_status']) || in_array($bumiputera_status, $criteria['bumiputera_status'])) &&
                (!isset($criteria['oku_status']) || $oku_status === $criteria['oku_status']) &&
                (!isset($criteria['business_type']) || in_array($business_type, $criteria['business_type'])) &&
                (!isset($criteria['business_experience']) || in_array($business_experience, $criteria['business_experience'])) &&
                (!isset($criteria['education_type']) || in_array($education_type, $criteria['education_type'])) &&
                (!isset($criteria['certification_level']) || in_array($certification_level, $criteria['certification_level'])) &&
                (!isset($criteria['loan_amount_range']) || $loan_amount_range === $criteria['loan_amount_range'])
            ) {
                $programs[] = $program;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalized Recommendations - KUSMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/recommendations.css">
</head>
<body>
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1 class="title">Tailored Resources Just for You</h1>
            <p class="subtitle">Explore curated opportunities designed to help you succeed.</p>
        </header>

       <!-- Loading Spinner -->
<div id="loading">
    <div class="loader"></div>
    <p>Finding the best resources for you...</p>
</div>


        <!-- Recommendations Section -->
        <div id="recommendations" style="display: none;">
            <div class="row">
                <?php if (empty($programs)): ?>
                    <div class="alert alert-warning text-center">
                        No resources matched your profile. Update your preferences for better results.
                    </div>
                <?php else: ?>
                    <?php foreach ($programs as $program): ?>
                        <div class="col-md-6 my-3">
                            <div class="recommendation-card">
                                <div class="card-header d-flex align-items-center">
                                    <img src="/fyp_kusma/<?= htmlspecialchars($program['agency_logo']) ?>" alt="Agency Logo" class="agency-logo me-3">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($program['name']) ?></h5>
                                </div>
                                <div class="card-body">
                                    <p class="description"><?= htmlspecialchars($program['description']) ?></p>
                                    <div class="details">
                                        <span><strong>Type:</strong> <?= htmlspecialchars($program['resource_types']) ?></span>
                                        <span><strong>Loan Range (RM):</strong> <?= htmlspecialchars($program['loan_amount_range']) ?></span>
                                    </div>
                                    <a href="<?= htmlspecialchars($program['application_link']) ?>" class="btn btn-learn-more" target="_blank">Learn More</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('recommendations').style.display = 'block';
            }, 2000);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
