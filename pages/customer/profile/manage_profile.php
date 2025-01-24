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

// Fetch user credentials
$sqlUser = "SELECT name, email FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userCredentials = $stmtUser->get_result()->fetch_assoc() ?? [];

// Fetch user personal details
$sqlPersonal = "SELECT * FROM personal_details WHERE user_id = ?";
$stmtPersonal = $conn->prepare($sqlPersonal);
$stmtPersonal->bind_param("i", $user_id);
$stmtPersonal->execute();
$personalDetails = $stmtPersonal->get_result()->fetch_assoc() ?? [];

// Fetch user business details
$sqlBusiness = "SELECT * FROM business_details WHERE user_id = ?";
$stmtBusiness = $conn->prepare($sqlBusiness);
$stmtBusiness->bind_param("i", $user_id);
$stmtBusiness->execute();
$businessDetails = $stmtBusiness->get_result()->fetch_assoc() ?? [];

// Fetch user education details
$sqlEducation = "SELECT * FROM education_resources WHERE user_id = ?";
$stmtEducation = $conn->prepare($sqlEducation);
$stmtEducation->bind_param("i", $user_id);
$stmtEducation->execute();
$educationDetails = $stmtEducation->get_result()->fetch_assoc() ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user credentials
    if (isset($_POST['update_credentials'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        if ($password) {
            $sqlUpdateCredentials = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdateCredentials);
            $stmtUpdate->bind_param("sssi", $name, $email, $password, $user_id);
        } else {
            $sqlUpdateCredentials = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdateCredentials);
            $stmtUpdate->bind_param("ssi", $name, $email, $user_id);
        }
        $stmtUpdate->execute();
    }

    // Update personal details
    if (isset($_POST['update_personal'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $gender = $_POST['gender'];
        $phone = $_POST['phone_number'];
        $address = $_POST['address'];

        $sqlUpdatePersonal = "UPDATE personal_details SET first_name = ?, last_name = ?, gender = ?, phone_number = ?, address = ? WHERE user_id = ?";
        $stmtUpdatePersonal = $conn->prepare($sqlUpdatePersonal);
        $stmtUpdatePersonal->bind_param("sssssi", $firstName, $lastName, $gender, $phone, $address, $user_id);
        $stmtUpdatePersonal->execute();
    }

    // Update business details
    if (isset($_POST['update_business'])) {
        $businessName = $_POST['business_name'];
        $businessType = $_POST['business_type'];
        $industry = $_POST['industry'];

        $sqlUpdateBusiness = "UPDATE business_details SET business_name = ?, business_type = ?, industry = ? WHERE user_id = ?";
        $stmtUpdateBusiness = $conn->prepare($sqlUpdateBusiness);
        $stmtUpdateBusiness->bind_param("sssi", $businessName, $businessType, $industry, $user_id);
        $stmtUpdateBusiness->execute();
    }

    // Update education details
    if (isset($_POST['update_education'])) {
        $educationType = $_POST['education_type'];
        $certificationLevel = $_POST['certification_level'];

        $sqlUpdateEducation = "UPDATE education_resources SET education_type = ?, certification_level = ? WHERE user_id = ?";
        $stmtUpdateEducation = $conn->prepare($sqlUpdateEducation);
        $stmtUpdateEducation->bind_param("ssi", $educationType, $certificationLevel, $user_id);
        $stmtUpdateEducation->execute();
    }

    header("Location: manage_profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../assets/css/customer_manageprofile.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-content">
            <h1 class="text-center mb-4">Manage Profile</h1>

            <!-- Credentials Form -->
            <div class="dashboard-section">
                <h2>Credentials</h2>
                <form method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($userCredentials['name'] ?? '') ?>" required>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($userCredentials['email'] ?? '') ?>" required>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password">
                    <button type="submit" name="update_credentials" class="btn btn-primary mt-3">Update Credentials</button>
                </form>
            </div>

            <!-- Personal Details Form -->
            <div class="dashboard-section">
                <h2>Personal Details</h2>
                <form method="POST">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($personalDetails['first_name'] ?? '') ?>" required>
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($personalDetails['last_name'] ?? '') ?>" required>
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?= ($personalDetails['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= ($personalDetails['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                    <label for="phone_number">Phone Number:</label>
                    <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($personalDetails['phone_number'] ?? '') ?>" required>
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($personalDetails['address'] ?? '') ?></textarea>
                    <button type="submit" name="update_personal" class="btn btn-primary mt-3">Update Personal Details</button>
                </form>
            </div>

            <!-- Business Details Form -->
            <div class="dashboard-section">
                <h2>Business Details</h2>
                <form method="POST">
                    <label for="business_name">Business Name:</label>
                    <input type="text" id="business_name" name="business_name" value="<?= htmlspecialchars($businessDetails['business_name'] ?? '') ?>">
                    <label for="business_type">Business Type:</label>
                    <select id="business_type" name="business_type">
                        <option value="Sole Proprietor" <?= ($businessDetails['business_type'] ?? '') === 'Sole Proprietor' ? 'selected' : '' ?>>Sole Proprietor</option>
                        <option value="Partnership" <?= ($businessDetails['business_type'] ?? '') === 'Partnership' ? 'selected' : '' ?>>Partnership</option>
                    </select>
                    <label for="industry">Industry:</label>
                    <input type="text" id="industry" name="industry" value="<?= htmlspecialchars($businessDetails['industry'] ?? '') ?>">
                    <button type="submit" name="update_business" class="btn btn-primary mt-3">Update Business Details</button>
                </form>
            </div>

            <!-- Education Details Form -->
            <div class="dashboard-section">
                <h2>Education Details</h2>
                <form method="POST">
                    <label for="education_type">Education Type:</label>
                    <select id="education_type" name="education_type" required>
                        <option value="Still Studying" <?= ($educationDetails['education_type'] ?? '') === 'Still Studying' ? 'selected' : '' ?>>Still Studying</option>
                        <option value="Graduated" <?= ($educationDetails['education_type'] ?? '') === 'Graduated' ? 'selected' : '' ?>>Graduated</option>
                    </select>
                    <label for="certification_level">Certification Level:</label>
                    <select id="certification_level" name="certification_level" required>
                        <option value="SPM / SKM" <?= ($educationDetails['certification_level'] ?? '') === 'SPM / SKM' ? 'selected' : '' ?>>SPM / SKM</option>
                        <option value="Diploma" <?= ($educationDetails['certification_level'] ?? '') === 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                        <option value="Degree" <?= ($educationDetails['certification_level'] ?? '') === 'Degree' ? 'selected' : '' ?>>Degree</option>
                        <option value="Master / PhD" <?= ($educationDetails['certification_level'] ?? '') === 'Master / PhD' ? 'selected' : '' ?>>Master / PhD</option>
                    </select>
                    <button type="submit" name="update_education" class="btn btn-primary mt-3">Update Education Details</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
