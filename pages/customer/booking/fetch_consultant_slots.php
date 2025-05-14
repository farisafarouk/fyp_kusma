<?php
require_once '../../../config/database.php';

$consultant_id = $_GET['consultant_id'] ?? 0;
if (!$consultant_id) {
  echo '<div class="alert-error">Invalid consultant ID. Please try again.</div>';
  exit();
}

$stmt = $conn->prepare("SELECT id, date, start_time, end_time, appointment_mode FROM schedules WHERE consultant_id = ? AND date >= CURDATE() ORDER BY date ASC, start_time ASC");
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo '<div class="alert-warning">No upcoming slots available for this consultant.</div>';
  exit();
}

$current_date = '';
echo "<div class='slot-wrapper'>";

while ($row = $result->fetch_assoc()) {
  $start = date('H:i', strtotime($row['start_time']));
  $end = date('H:i', strtotime($row['end_time']));
  $date = date('l, d M Y', strtotime($row['date']));

  if ($current_date !== $date) {
    if ($current_date !== '') echo '</div>';
    echo "<div class='slot-group'><h4>$date</h4><div class='slot-items'>";
    $current_date = $date;
  }

  $safe_mode = htmlspecialchars($row['appointment_mode']);
  echo "<button class='slot-btn' onclick=\"confirmBooking({$row['id']}, '$start', '$end', '$safe_mode')\">$start - $end <span class='badge badge-{$safe_mode}'>" . ucfirst($safe_mode) . "</span></button>";
}

echo '</div></div>';
