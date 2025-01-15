<?php
require_once '../../../config/database.php';

$consultant_id = $_GET['consultant_id'];
$mode = $_GET['mode'];

$query = "SELECT schedule_id, day, date, start_time, end_time 
          FROM consultant_schedule 
          WHERE consultant_id = ? AND appointment_mode = ? AND is_available = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $consultant_id, $mode);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

header('Content-Type: application/json');
echo json_encode($schedules);
?>
