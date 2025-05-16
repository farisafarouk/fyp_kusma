<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Consultant not found']);
    exit();
}
$consultant_id = $result->fetch_assoc()['id'];

// Fetch available slots in the future
$query = "
    SELECT id, date, start_time, end_time 
    FROM schedules 
    WHERE consultant_id = ? 
    AND is_available = 1 
    AND date >= CURDATE()
    ORDER BY date ASC, start_time ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$results = $stmt->get_result();

$slots = [];
while ($row = $results->fetch_assoc()) {
    $date = $row['date'];
    if (!isset($slots[$date])) {
        $slots[$date] = [];
    }
   $slots[$date][] = [
    'id' => $row['id'], // This must be 'id' to match frontend expectations
    'start_time' => $row['start_time'],
    'end_time' => $row['end_time']
];

}

echo json_encode($slots);
?>
