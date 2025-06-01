<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../../config/database.php';

$user_id = $_SESSION['user_id'];

// Validate required
if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['phone']) || empty($_POST['email'])) {
  header("Location: manage_profile.php?status=empty");
  exit();
}

$sql = "UPDATE personal_details SET 
    title=?, first_name=?, last_name=?, gender=?, phone_number=?, birthdate=?, 
    mykad_number=?, bumiputera_status=?, address=?, city=?, state=?, postcode=?, oku_status=? 
    WHERE user_id=?";
$stmt = $conn->prepare($sql);
$oku = isset($_POST['oku']) ? 1 : 0;

$stmt->bind_param("sssssssssssiii",
    $_POST['title'], $_POST['first_name'], $_POST['last_name'], $_POST['gender'],
    $_POST['phone'], $_POST['birthdate'], $_POST['mykad'], $_POST['bumiputera_status'],
    $_POST['address'], $_POST['city'], $_POST['state'], $_POST['postcode'],
    $oku, $user_id
);

$stmt->execute();

// Update email/password
$new_email = $_POST['email'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if (!empty($new_password) && $new_password === $confirm_password) {
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET email=?, password=? WHERE id=?");
    $stmt->bind_param("ssi", $new_email, $hashed, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
    $stmt->bind_param("si", $new_email, $user_id);
}
$success = $stmt->execute();

$_SESSION['email'] = $new_email;
header("Location: manage_profile.php?status=" . ($success ? "success" : "error"));
exit();
?>
