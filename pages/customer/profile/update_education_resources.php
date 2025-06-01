<?php
session_start();
require_once '../../../config/database.php';

$user_id = $_SESSION['user_id'];

if (empty($_POST['education_type']) || empty($_POST['certification_level']) || empty($_POST['preferred_loan_range'])) {
  header("Location: manage_profile.php?status=empty");
  exit();
}

$resource_type_json = json_encode($_POST['resource_type'] ?? []);

$sql = "UPDATE education_resources SET 
    education_type=?, certification_level=?, employment_status=?, 
    resource_type=?, preferred_loan_range=?
    WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi",
    $_POST['education_type'], $_POST['certification_level'], $_POST['employment_status'],
    $resource_type_json, $_POST['preferred_loan_range'], $user_id
);
$success = $stmt->execute();

header("Location: manage_profile.php?status=" . ($success ? "success" : "error"));
exit();
?>
