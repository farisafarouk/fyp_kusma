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
    $resource_type_array = $_POST['resourceType'] ?? [];
    $preferred_loan_range = $_POST['loanAmount'] ?? null;
    $urgency = $_POST['urgency'] ?? null;

    // Validate required dropdowns
    if (
        empty($education_type) ||
        empty($certification_level) ||
        empty($employment_status) ||
        empty($urgency)
    ) {
        die("Error: Please fill in all required fields.");
    }

    // Validate checkbox selection
    if (!is_array($resource_type_array) || count($resource_type_array) === 0) {
        die("Error: Please select at least one resource type.");
    }

    // Validate value sets
    $valid_edu = ['Still Studying', 'Graduated'];
    $valid_cert = ['SPM / SKM', 'Diploma', 'Degree', 'Master / PhD'];
    $valid_emp = ['Employed', 'Self-Employed', 'Unemployed', 'Student'];
    $valid_urgency = ['Immediate', '1-3 Months', 'More than 3 Months'];

    if (
        !in_array($education_type, $valid_edu) ||
        !in_array($certification_level, $valid_cert) ||
        !in_array($employment_status, $valid_emp) ||
        !in_array($urgency, $valid_urgency)
    ) {
        die("Error: Invalid dropdown value submitted.");
    }

    // JSON encode the selected resources
    $resource_type = json_encode($resource_type_array);

    // SQL insert/update
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
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param(
        "issssss",
        $user_id, $education_type, $certification_level, $employment_status,
        $resource_type, $preferred_loan_range, $urgency
    );

    if ($stmt->execute()) {
        // Update form status
        $update_sql = "UPDATE users SET form_status = 'completed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt) {
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
        }

        header("Location: recommendations.php");
        exit();
    } else {
        die("Execution Error: " . $stmt->error);
    }
}
?>
