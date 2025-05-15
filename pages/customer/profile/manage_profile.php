<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch functions
function fetchRecord($conn, $table, $user_id) {
    $sql = "SELECT * FROM $table WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$personal = fetchRecord($conn, "personal_details", $user_id);
$business = fetchRecord($conn, "business_details", $user_id);
$education = fetchRecord($conn, "education_resources", $user_id);

// Get email
$emailQuery = $conn->prepare("SELECT email FROM users WHERE id = ?");
$emailQuery->bind_param("i", $user_id);
$emailQuery->execute();
$emailResult = $emailQuery->get_result()->fetch_assoc();
$currentEmail = $emailResult['email'] ?? '-';
$_SESSION['email'] = $currentEmail;

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'];

    if ($section === 'personal') {
        $sql = "UPDATE personal_details SET first_name=?, last_name=?, phone_number=?, address=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $_POST['first_name'], $_POST['last_name'], $_POST['phone'], $_POST['address'], $user_id);
        $stmt->execute();

        $new_email = $_POST['email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $sql = "UPDATE users SET email = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $new_email, $hashed, $user_id);
            }
        } else {
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_email, $user_id);
        }
        $stmt->execute();
        $_SESSION['email'] = $new_email;

    } elseif ($section === 'business') {
        $sql = "UPDATE business_details SET business_type=?, business_name=?, industry=?, business_experience=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $_POST['business_type'], $_POST['business_name'], $_POST['industry'], $_POST['experience'], $user_id);
        $stmt->execute();

    } elseif ($section === 'education') {
        $sql = "UPDATE education_resources SET education_type=?, certification_level=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $_POST['education_type'], $_POST['certification'], $user_id);
        $stmt->execute();
    }

    header("Location: manage_profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Profile</title>
    
    
    <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
    <link rel="stylesheet" href="../../../assets/css/customer_manageprofile.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Icons -->
   
  </head>
<body>
<?php include '../customer_navbar.php'; ?>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1>Manage Profile</h1>
        <p>View and edit your personal, business, and education details.</p>
    </header>

    <div class="dashboard-sections">
        <!-- Personal -->
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-user-circle"></i></div>
            <div class="card-content">
                <h2>Personal Information</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($personal['first_name'] . ' ' . $personal['last_name']) ?><br>
                   <strong>Phone:</strong> <?= htmlspecialchars($personal['phone_number']) ?><br>
                   <strong>Address:</strong> <?= htmlspecialchars($personal['address']) ?><br>
                   <strong>Email:</strong> <?= htmlspecialchars($currentEmail) ?>
                </p>
                <button class="dashboard-btn" onclick="openModal('personalModal')">Edit</button>
            </div>
        </div>

        <!-- Business -->
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-briefcase"></i></div>
            <div class="card-content">
                <h2>Business Info</h2>
                <p><strong>Type:</strong> <?= htmlspecialchars($business['business_type']) ?><br>
                   <strong>Name:</strong> <?= htmlspecialchars($business['business_name']) ?><br>
                   <strong>Industry:</strong> <?= htmlspecialchars($business['industry']) ?><br>
                   <strong>Experience:</strong> <?= htmlspecialchars($business['business_experience']) ?> 
                </p>
                <button class="dashboard-btn" onclick="openModal('businessModal')">Edit</button>
            </div>
        </div>

        <!-- Education -->
        <div class="dashboard-card">
            <div class="card-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="card-content">
                <h2>Education</h2>
                <p><strong>Type:</strong> <?= htmlspecialchars($education['education_type']) ?><br>
                   <strong>Level:</strong> <?= htmlspecialchars($education['certification_level']) ?>
                </p>
                <p>     </p>
                <p>     </p>
                <button class="dashboard-btn" onclick="openModal('educationModal')">Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'update_profile.php'; ?>

<script>
function openModal(id) {
    document.getElementById(id).style.display = "flex";
}
function closeModal(id) {
    document.getElementById(id).style.display = "none";
}
</script>
</body>
</html>