<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if request is POST and contains required fields
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON request payload
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['id'] ?? null;
    $rating = $data['rating'] ?? null;
    $feedback = $data['feedback'] ?? null;

    // Validate input data
    if (!$appointment_id || !$rating || !$feedback) {
        header("Content-Type: application/json");
        http_response_code(400);
        echo json_encode(['error' => 'All fields are required']);
        exit();
    }

    // Ensure the appointment belongs to the logged-in user and is completed
    $sql_verify = "
        SELECT id 
        FROM appointments 
        WHERE id = ? AND customer_id = ? AND status = 'completed' AND feedback IS NULL";
    $stmt_verify = $conn->prepare($sql_verify);
    $stmt_verify->bind_param('ii', $appointment_id, $user_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 0) {
        header("Content-Type: application/json");
        http_response_code(403);
        echo json_encode(['error' => 'Invalid appointment or feedback already provided']);
        exit();
    }

    // Update the appointment with the feedback and rating
    $sql_update = "
        UPDATE appointments 
        SET feedback = ?, rating = ? 
        WHERE id = ? AND customer_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('siii', $feedback, $rating, $appointment_id, $user_id);

    if ($stmt_update->execute()) {
        header("Content-Type: application/json");
        http_response_code(200);
        echo json_encode(['message' => 'Feedback submitted successfully']);
    } else {
        header("Content-Type: application/json");
        http_response_code(500);
        echo json_encode(['error' => 'Error submitting feedback']);
    }
} else {
    header("Content-Type: application/json");
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}
