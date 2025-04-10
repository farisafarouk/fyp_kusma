<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

function ageMatches($age, $criteria) {
    if (empty($criteria['age'])) return true;
    foreach ($criteria['age'] as $range) {
        [$min, $max] = explode('-', str_replace('Above ', '', $range));
        if ($age >= (int)$min && $age <= (int)$max) {
            return true;
        }
    }
    return false;
}

function formatLoanRange($min, $max) {
    return "RM " . number_format($min / 1000) . "k - RM " . number_format($max / 1000) . "k";
}

function explainMatch($criteria, $user, $age) {
    $explanations = [];
    if (!empty($criteria['gender']) && in_array($user['gender'], $criteria['gender'])) $explanations[] = "✅ You're a " . $user['gender'];
    if (!empty($criteria['bumiputera_status']) && in_array($user['bumiputera_status'], $criteria['bumiputera_status'])) $explanations[] = "✅ You're a " . $user['bumiputera_status'];
    if (isset($criteria['oku_status']) && $user['oku_status'] == $criteria['oku_status']) $explanations[] = "✅ OKU status matched";
    if (!empty($criteria['business_type']) && in_array($user['business_type'], $criteria['business_type'])) $explanations[] = "✅ Your business type qualifies";
    if (!empty($criteria['business_experience']) && in_array($user['business_experience'], $criteria['business_experience'])) $explanations[] = "✅ Your business experience qualifies";
    if (!empty($criteria['education_type']) && in_array($user['education_type'], $criteria['education_type'])) $explanations[] = "✅ Education background matches";
    if (!empty($criteria['certification_level']) && in_array($user['certification_level'], $criteria['certification_level'])) $explanations[] = "✅ Certification level matches";
    if (ageMatches($age, $criteria)) $explanations[] = "✅ You're aged $age";
    if (!empty($user['preferred_loan_range'])) $explanations[] = "✅ You prefer loans of " . $user['preferred_loan_range'];
    return $explanations;
}

$sqlUser = "SELECT u.subscription_status, pd.*, bd.*, er.*
            FROM users u
            LEFT JOIN personal_details pd ON u.id = pd.user_id
            LEFT JOIN business_details bd ON u.id = bd.user_id
            LEFT JOIN education_resources er ON u.id = er.user_id
            WHERE u.id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc();

$programs = [];
if ($userProfile) {
    $birth_date = new DateTime($userProfile['birthdate']);
    $current_date = new DateTime();
    $age = $current_date->diff($birth_date)->y;
    $subscription = $userProfile['subscription_status'];

    $sqlPrograms = "SELECT p.*, a.logo_url AS agency_logo FROM programs p JOIN agencies a ON p.agency_id = a.id";
    $result = $conn->query($sqlPrograms);

    if ($result) {
        while ($program = $result->fetch_assoc()) {
            $criteria = json_decode($program['eligibility_criteria'], true);
            $program['score'] = 0;
            $program['explanation'] = explainMatch($criteria, $userProfile, $age);
            $program['score'] = count($program['explanation']);
            if ($program['score'] > 0) {
                $programs[] = $program;
            }
        }
        usort($programs, fn($a, $b) => $b['score'] <=> $a['score']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations - KUSMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/recommendations.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">KUSMA</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="profile/user_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="profile/manage_profile.php">Manage Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="../login/login.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <header class="text-center mb-4">
        <h1 class="title">Tailored Resources Just for You</h1>
        <p class="subtitle">Explore curated opportunities designed to help you succeed.</p>
    </header>

    <div class="row" id="programCards">
        <?php if (empty($programs)): ?>
            <div class="alert alert-warning text-center">
                No resources matched your profile. Update your preferences for better results.
            </div>
        <?php else: ?>
            <?php foreach ($programs as $index => $program): ?>
                <div class="col-md-6 my-3 program-card">
                    <div class="recommendation-card">
                        <div class="recommendation-content <?= ($userProfile['subscription_status'] === 'free' && $index >= 2) ? 'blurred' : '' ?>">
                            <div class="card-header d-flex align-items-center">
                                <img src="/fyp_kusma/<?= htmlspecialchars($program['agency_logo']) ?>" alt="Agency Logo" class="agency-logo me-3">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($program['name']) ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="description"><?= htmlspecialchars($program['description']) ?></p>
                                <div class="details">
                                    <span><strong>Type:</strong> <?= htmlspecialchars($program['resource_types']) ?></span>
                                    <span><strong>Loan Range:</strong> <span class="loan-range-badge">
                                        <?= formatLoanRange($program['min_loan_amount'], $program['max_loan_amount']) ?>
                                    </span></span>
                                </div>
                                <div class="explanation-box">
                                    <strong>🔍 You qualify for this program because:</strong>
                                    <ul>
                                        <?php foreach ($program['explanation'] as $reason): ?>
                                            <li><?= $reason ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php if ($userProfile['subscription_status'] === 'free' && $index >= 2): ?>
                            <div class="lock-overlay">
                                <i class="bi bi-lock-fill"></i>
                            </div>
                        <?php endif; ?>
                        <div class="unlock-wrapper">
                            <?php if ($userProfile['subscription_status'] === 'free' && $index >= 2): ?>
                                <a href="../customer/payment/upgrade.php" class="btn btn-unlock mt-2">Unlock Full Access</a>
                            <?php else: ?>
                                <a href="<?= htmlspecialchars($program['application_link']) ?>" class="btn btn-learn-more mt-2" target="_blank">Learn More</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const value = searchInput.value.toLowerCase();
                document.querySelectorAll('.program-card').forEach(card => {
                    card.style.display = card.textContent.toLowerCase().includes(value) ? 'block' : 'none';
                });
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
