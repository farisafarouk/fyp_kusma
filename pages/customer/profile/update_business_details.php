<?php
session_start();
require_once '../../../config/database.php';

$user_id = $_SESSION['user_id'];

if (empty($_POST['business_name']) || empty($_POST['business_type'])) {
  header("Location: manage_profile.php?status=empty");
  exit();
}

$sql = "UPDATE business_details SET 
    is_registered=?, business_type=?, business_name=?, registration_number=?, 
    industry=?, premises_type=?, business_experience=?, pbt_license=?
    WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssii",
    $_POST['is_registered'], $_POST['business_type'], $_POST['business_name'],
    $_POST['registration_number'], $_POST['industry'], $_POST['premises_type'],
    $_POST['business_experience'], $_POST['pbt_license'], $user_id
);
$success = $stmt->execute();

header("Location: manage_profile.php?status=" . ($success ? "success" : "error"));
exit();
?>
