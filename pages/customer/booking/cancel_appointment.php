<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $appointment_id = $_POST['id'];

    if (!$user_id) {
        http_response_code(403);
        echo json_encode(['error' => 'User not logged in']);
        exit();
    }

    // Verify the appointment belongs to the current user
    $verify_sql = "SELECT id FROM appointments WHERE id = ? AND customer_id = ?";
    $stmt_verify = $conn->prepare($verify_sql);
    $stmt_verify->bind_param('ii', $appointment_id, $user_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized access']);
        exit();
    }

    // Update the appointment status to 'canceled'
    $update_sql = "UPDATE appointments SET status = 'canceled' WHERE id = ?";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param('i', $appointment_id);

    if ($stmt_update->execute()) {
        echo json_encode(['success' => 'Appointment canceled successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error canceling appointment']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>
