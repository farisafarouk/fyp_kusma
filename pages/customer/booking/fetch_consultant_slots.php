<?php
require_once '../../../config/database.php';

$consultant_id = $_GET['consultant_id'] ?? 0;
if (!$consultant_id) {
  echo '<div class="alert-error">Invalid consultant ID. Please try again.</div>';
  exit();
}

// Step 1: Fetch all future slots
$stmt = $conn->prepare("SELECT s.id, s.date, s.start_time, s.end_time, s.appointment_mode
FROM schedules s
WHERE s.consultant_id = ?
  AND s.date >= CURDATE()
  AND NOT EXISTS (
    SELECT 1 FROM appointments a
    WHERE a.consultant_id = s.consultant_id
      AND a.status IN ('pending', 'confirmed')
      AND a.scheduled_date = s.date
      AND (
        (s.start_time < ADDTIME(a.scheduled_time, SEC_TO_TIME(a.duration * 60)) AND s.end_time > a.scheduled_time)
      )
  )
ORDER BY s.date ASC, s.start_time ASC
");
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

// Step 2: Check if any slots exist
if ($result->num_rows === 0) {
  echo '<div class="alert-warning">No upcoming slots available for this consultant.</div>';
  exit();
}

// Step 3: Group slots by date
$grouped_slots = [];

while ($row = $result->fetch_assoc()) {
  $date_key = $row['date'];
  $grouped_slots[$date_key][] = $row;
}

// Step 4: Render only groups that have valid slot buttons
echo "<div class='slot-wrapper'>";

foreach ($grouped_slots as $date => $slots) {
  if (count($slots) > 0) {
    $formatted_date = date('l, d M Y', strtotime($date));
    echo "<div class='slot-group'>";
    echo "<h4>$formatted_date</h4><div class='slot-items'>";

    foreach ($slots as $slot) {
      $start = date('H:i', strtotime($slot['start_time']));
      $end = date('H:i', strtotime($slot['end_time']));
      $safe_mode = htmlspecialchars($slot['appointment_mode']);
      $slot_id = $slot['id'];
      echo "<button class='slot-btn' onclick=\"confirmBooking($slot_id, '$start', '$end', '$safe_mode')\">";
      echo "$start - $end <span class='badge badge-$safe_mode'>" . ucfirst($safe_mode) . "</span>";
      echo "</button>";
    }

    echo "</div></div>";
  }
}

echo "</div>"; // close .slot-wrapper
?>
