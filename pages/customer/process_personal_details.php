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
    // Safely extract POST values with null coalescing
    $title = $_POST['title'] ?? null;
    $first_name = $_POST['first-name'] ?? null;
    $last_name = $_POST['last-name'] ?? null;
    $gender = $_POST['gender'] ?? null;
    $email = $_POST['email'] ?? null;
    $phone_number = $_POST['phone'] ?? null;
    $birthdate = $_POST['birthdate'] ?? null;
    $mykad_number = $_POST['mykad'] ?? null;
    $bumiputera_status = $_POST['bumiputera_status'] ?? null;
    $address = $_POST['address'] ?? null;
    $city = $_POST['city'] ?? null;
    $state = $_POST['state'] ?? null;
    $postcode = $_POST['postcode'] ?? null;
    $oku_status = isset($_POST['oku']) ? 1 : 0;

    // Simple required validation
    $required_fields = [
        'Title' => $title,
        'First Name' => $first_name,
        'Last Name' => $last_name,
        'Gender' => $gender,
        'Email' => $email,
        'Phone Number' => $phone_number,
        'Birthdate' => $birthdate,
        'MyKad Number' => $mykad_number,
        'Bumiputera Status' => $bumiputera_status,
        'Address' => $address,
        'City' => $city,
        'State' => $state,
        'Postcode' => $postcode
    ];

    foreach ($required_fields as $label => $value) {
        if (empty($value)) {
            die("Error: $label is required.");
        }
    }

    // Format validation
    if (!preg_match('/^\d{10,11}$/', $phone_number)) {
        die("Error: Phone number must be 10 or 11 digits.");
    }

    if (!preg_match('/^\d{6}-?\d{2}-?\d{4}$/', $mykad_number)) {
        die("Error: Invalid MyKad format.");
    }

    // Calculate age (optional use)
    $birth_date = new DateTime($birthdate);
    $current_date = new DateTime();
    $age = $current_date->diff($birth_date)->y;

    // Insert or Update Personal Details
    $sql = "
        INSERT INTO personal_details (
            user_id, title, first_name, last_name, gender, email, phone_number, birthdate, 
            mykad_number, bumiputera_status, address, city, state, postcode, oku_status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            gender = VALUES(gender),
            email = VALUES(email), 
            phone_number = VALUES(phone_number),
            birthdate = VALUES(birthdate),
            mykad_number = VALUES(mykad_number),
            bumiputera_status = VALUES(bumiputera_status),
            address = VALUES(address),
            city = VALUES(city),
            state = VALUES(state),
            postcode = VALUES(postcode),
            oku_status = VALUES(oku_status)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssssssi",
        $user_id, $title, $first_name, $last_name, $gender, $email, $phone_number, $birthdate,
        $mykad_number, $bumiputera_status, $address, $city, $state, $postcode, $oku_status
    );

    if ($stmt->execute()) {
        // Update form_status in users table
        $update_sql = "UPDATE users SET form_status = 'business' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            die("Update SQL Error: " . $conn->error);
        }
        $update_stmt->bind_param("i", $user_id);
        $update_stmt->execute();

        // Redirect to business_details.php
        header("Location: business_details.php");
        exit();
    } else {
        die("Execution Error: " . $stmt->error);
    }
}
?>
