<?php
require_once '../../../config/database.php';

$consultant_id = $_GET['consultant_id'] ?? 0;
$current_appointment_id = $_GET['appointment_id'] ?? null;
if (!$consultant_id) {
  echo '<div class="alert-error">Invalid consultant ID. Please try again.</div>';
  exit();
}

// Step 1: Fetch all future slots
$query = "SELECT s.id, s.date, s.start_time, s.end_time, s.appointment_mode
FROM schedules s
WHERE s.consultant_id = ?
  AND s.date >= CURDATE()
  AND NOT EXISTS (
    SELECT 1 FROM appointments a
    WHERE a.consultant_id = s.consultant_id
      AND a.status IN ('pending', 'confirmed')
      AND a.scheduled_date = s.date
      AND (
        s.start_time < ADDTIME(a.scheduled_time, SEC_TO_TIME(a.duration * 60))
        AND s.end_time > a.scheduled_time
      )
      AND a.id != ?
  )
ORDER BY s.date ASC, s.start_time ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $consultant_id, $current_appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo '<div class="alert-warning">No upcoming slots available for this consultant.</div>';
  exit();
}

$grouped_slots = [];
while ($row = $result->fetch_assoc()) {
  $date_key = $row['date'];
  $grouped_slots[$date_key][] = $row;
}

echo "<div class='slot-wrapper'>";
foreach ($grouped_slots as $date => $slots) {
  if (count($slots) > 0) {
    $formatted_date = date('l, d M Y', strtotime($date));
    echo "<div class='slot-group'>";
    echo "<h4>$formatted_date</h4><div class='slot-items'>";
    foreach ($slots as $slot) {
      $slot_id = $slot['id'];

      // Additional check: ensure the slot is not taken (recheck duration-wise conflict)
      $check = $conn->prepare("SELECT 1 FROM appointments WHERE consultant_id = ? AND scheduled_date = ? AND status IN ('pending', 'confirmed') AND (
        ? < ADDTIME(scheduled_time, SEC_TO_TIME(duration * 60)) AND ? > scheduled_time
      ) AND id != ?");
      $check->bind_param("isssi", $consultant_id, $slot['date'], $slot['start_time'], $slot['end_time'], $current_appointment_id);
      $check->execute();
      $check_result = $check->get_result();
      if ($check_result->num_rows > 0) continue;

      $start = date('H:i', strtotime($slot['start_time']));
      $end = date('H:i', strtotime($slot['end_time']));
      $safe_mode = htmlspecialchars($slot['appointment_mode']);
      echo "<button class='slot-btn' onclick=\"confirmBooking($slot_id, '$start', '$end', '$safe_mode')\">";
      echo "$start - $end <span class='badge badge-$safe_mode'>" . ucfirst($safe_mode) . "</span>";
      echo "</button>";
    }
    echo "</div></div>";
  }
}
echo "</div>"; // close .slot-wrapper
?>
