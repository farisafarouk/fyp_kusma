<?php
session_start();
require_once '../../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch data
$sql = "
    SELECT u.name, u.email, pd.first_name, pd.last_name, pd.gender, pd.phone_number, 
           pd.address, pd.city, pd.state, pd.postcode, bd.business_name, bd.business_type,
           ed.education_type, ed.certification_level
    FROM users u
    LEFT JOIN personal_details pd ON u.id = pd.user_id
    LEFT JOIN business_details bd ON u.id = bd.user_id
    LEFT JOIN education_resources ed ON u.id = ed.user_id
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc() ?? [];

// Handle updates (in-line PHP for simplicity)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? null;

    if ($section === 'credentials') {
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        $sqlUpdate = $password
            ? "UPDATE users SET email = ?, password = ? WHERE id = ?"
            : "UPDATE users SET email = ? WHERE id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $password ? $stmt->bind_param("ssi", $email, $password, $user_id) : $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
    } elseif ($section === 'personal') {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $gender = $_POST['gender'];
        $phone_number = $_POST['phone_number'];
        $address = $_POST['address'];

        $sqlUpdate = "UPDATE personal_details SET first_name = ?, last_name = ?, gender = ?, phone_number = ?, address = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("sssssi", $first_name, $last_name, $gender, $phone_number, $address, $user_id);
        $stmt->execute();
    } elseif ($section === 'business') {
        $business_name = $_POST['business_name'];
        $business_type = $_POST['business_type'];

        $sqlUpdate = "UPDATE business_details SET business_name = ?, business_type = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssi", $business_name, $business_type, $user_id);
        $stmt->execute();
    } elseif ($section === 'education') {
        $education_type = $_POST['education_type'];
        $certification_level = $_POST['certification_level'];

        $sqlUpdate = "UPDATE education_resources SET education_type = ?, certification_level = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssi", $education_type, $certification_level, $user_id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/manage_profile.css">
</head>
<body>
<div class="container">
    <h1>Manage Profile</h1>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="credentials-tab" data-bs-toggle="tab" href="#credentials" role="tab" aria-controls="credentials" aria-selected="true">Credentials</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="personal-tab" data-bs-toggle="tab" href="#personal" role="tab" aria-controls="personal" aria-selected="false">Personal Details</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="business-tab" data-bs-toggle="tab" href="#business" role="tab" aria-controls="business" aria-selected="false">Business Details</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="education-tab" data-bs-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">Education Details</a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        <!-- Credentials Tab -->
        <div class="tab-pane fade show active" id="credentials" role="tabpanel" aria-labelledby="credentials-tab">
            <h2>Update Credentials</h2>
            <form>
                <!-- Fields for name, email, and password -->
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter a new password">
                <button type="submit">Update</button>
            </form>
        </div>

        <!-- Personal Details Tab -->
        <div class="tab-pane fade" id="personal" role="tabpanel" aria-labelledby="personal-tab">
            <h2>Update Personal Details</h2>
            <form>
                <!-- Personal details fields -->
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first-name" placeholder="Enter your first name">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last-name" placeholder="Enter your last name">
                <button type="submit">Update</button>
            </form>
        </div>

        <!-- Business Details Tab -->
        <div class="tab-pane fade" id="business" role="tabpanel" aria-labelledby="business-tab">
            <h2>Update Business Details</h2>
            <form>
                <!-- Business details fields -->
                <label for="business-name">Business Name</label>
                <input type="text" id="business-name" name="business-name" placeholder="Enter your business name">
                <label for="business-type">Business Type</label>
                <select id="business-type" name="business-type">
                    <option value="sole">Sole Proprietor</option>
                    <option value="partnership">Partnership</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>

        <!-- Education Details Tab -->
        <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
            <h2>Update Education Details</h2>
            <form>
                <!-- Education details fields -->
                <label for="education-type">Education Type</label>
                <select id="education-type" name="education-type">
                    <option value="studying">Still Studying</option>
                    <option value="graduated">Graduated</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</div>

</html>
