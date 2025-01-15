<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch form data
    $is_registered = isset($_POST['businessRegistered']) && $_POST['businessRegistered'] === 'Yes' ? 1 : 0;
    $business_type = $is_registered ? ($_POST['businessType'] ?? 'None') : 'None';
    $business_name = $is_registered ? ($_POST['businessName'] ?? null) : null;
    $registration_number = $is_registered ? ($_POST['businessReg'] ?? null) : null;
    $industry = $is_registered ? ($_POST['industry'] ?? null) : null;
    $premises_type = $is_registered ? ($_POST['businessPremises'] ?? 'Online') : 'Online';
    $business_experience = $is_registered ? ($_POST['businessExperience'] ?? 'None') : 'None';
    $pbt_license = $is_registered ? ($_POST['pbt_license'] ?? 0) : 0;

    // Validate required fields if business is registered
    if ($is_registered) {
        if (empty($business_type) || empty($business_name) || empty($registration_number)) {
            die("Please fill in all required business details.");
        }
    }

    // Insert or Update Business Details
    $sql = "
        INSERT INTO business_details (
            user_id, is_registered, business_type, business_name, registration_number, 
            industry, premises_type, business_experience, pbt_license
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
            is_registered = VALUES(is_registered),
            business_type = VALUES(business_type),
            business_name = VALUES(business_name),
            registration_number = VALUES(registration_number),
            industry = VALUES(industry),
            premises_type = VALUES(premises_type),
            business_experience = VALUES(business_experience),
            pbt_license = VALUES(pbt_license)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param(
        "iissssssi",
        $user_id, $is_registered, $business_type, $business_name, $registration_number,
        $industry, $premises_type, $business_experience, $pbt_license
    );

    if ($stmt->execute()) {
        // Update form_status in the users table
        $update_sql = "UPDATE users SET form_status = 'education' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Update SQL Error: " . $conn->error);
        }
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();

        // Redirect to the next step (education details)
        header("Location: edu_resources.php");
        exit();
    } else {
        echo "<p class='text-danger'>Failed to save business details. Please try again later.</p>";
    }
} else {
    header("Location: business_details.php");
    exit();
}
?>
