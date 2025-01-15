<?php
session_start();
require_once '../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch and validate form data
    $education_type = $_POST['educationType'] ?? null;
    $certification_level = $_POST['certificationLevel'] ?? null;
    $employment_status = $_POST['employmentStatus'] ?? null;
    $resource_type = json_encode($_POST['resourceType'] ?? []); // Default to empty array
    $preferred_loan_range = $_POST['loanAmount'] ?? null;
    $urgency = $_POST['urgency'] ?? null;

    if (empty($education_type) || empty($certification_level) || empty($employment_status)) {
        die("Please fill in all required fields.");
    }

    if (!in_array($education_type, ['Still Studying', 'Graduated']) ||
        !in_array($certification_level, ['SPM / SKM', 'Diploma', 'Degree', 'Master / PhD']) ||
        !in_array($employment_status, ['Employed', 'Self-Employed', 'Unemployed', 'Student'])) {
        die("Invalid input. Please ensure all fields are filled in correctly.");
    }

    // Insert or Update Education Resources
    $sql = "
        INSERT INTO education_resources (
            user_id, education_type, certification_level, employment_status, 
            resource_type, preferred_loan_range, urgency
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?
        )
        ON DUPLICATE KEY UPDATE
            education_type = VALUES(education_type),
            certification_level = VALUES(certification_level),
            employment_status = VALUES(employment_status),
            resource_type = VALUES(resource_type),
            preferred_loan_range = VALUES(preferred_loan_range),
            urgency = VALUES(urgency)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing SQL statement: " . $conn->error);
    }
    $stmt->bind_param(
        "issssss",
        $user_id, $education_type, $certification_level, $employment_status,
        $resource_type, $preferred_loan_range, $urgency
    );

    if ($stmt->execute()) {
        // Mark form as completed
        $updateUser = "UPDATE users SET form_status = 'completed' WHERE id = ?";
        $updateStmt = $conn->prepare($updateUser);
        $updateStmt->bind_param("i", $user_id);
        $updateStmt->execute();

        header("Location: recommendations.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
