<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the consultant is logged in
if (!isset($_SESSION['consultant_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$consultant_id = $_SESSION['consultant_id'];

// Handle add/edit/delete requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    if ($action === 'add') {
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        $sql = "INSERT INTO consultant_availability (consultant_id, date, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $consultant_id, $date, $start_time, $end_time);
        $stmt->execute();
    } elseif ($action === 'edit') {
        $availability_id = $_POST['availability_id'];
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        $sql = "UPDATE consultant_availability SET date = ?, start_time = ?, end_time = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $date, $start_time, $end_time, $availability_id);
        $stmt->execute();
    } elseif ($action === 'delete') {
        $availability_id = $_POST['availability_id'];

        $sql = "DELETE FROM consultant_availability WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $availability_id);
        $stmt->execute();
    }
}

// Fetch availability data for calendar
$sql = "SELECT id, date, start_time, end_time FROM consultant_availability WHERE consultant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'start' => $row['date'] . 'T' . $row['start_time'],
        'end' => $row['date'] . 'T' . $row['end_time'],
        'title' => 'Available'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Availability</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.6/main.min.css" rel="stylesheet">
    <link href="../../assets/css/consultant_availability.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.6/main.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Consultant Availability</h1>
        <div id="calendar"></div>
        <div id="availabilityForm" class="modal">
            <div class="modal-content">
                <form id="availabilityFormDetails">
                    <input type="hidden" id="availabilityId" name="availability_id">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>
                    <label for="start_time">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" required>
                    <label for="end_time">End Time:</label>
                    <input type="time" id="end_time" name="end_time" required>
                    <input type="hidden" name="action" id="formAction" value="add">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" id="cancelForm">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let calendarEl = document.getElementById('calendar');
            let events = <?= json_encode($events); ?>;

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: events,
                selectable: true,
                select: function (info) {
                    document.getElementById('availabilityForm').style.display = 'block';
                    document.getElementById('date').value = info.startStr;
                    document.getElementById('formAction').value = 'add';
                },
                eventClick: function (info) {
                    let event = info.event;
                    document.getElementById('availabilityForm').style.display = 'block';
                    document.getElementById('availabilityId').value = event.id;
                    document.getElementById('date').value = event.startStr.split('T')[0];
                    document.getElementById('start_time').value = event.extendedProps.start_time;
                    document.getElementById('end_time').value = event.extendedProps.end_time;
                    document.getElementById('formAction').value = 'edit';
                }
            });

            calendar.render();

            document.getElementById('availabilityFormDetails').addEventListener('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(e.target);

                fetch('consultant_availability.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    location.reload();
                });
            });

            document.getElementById('cancelForm').addEventListener('click', function () {
                document.getElementById('availabilityForm').style.display = 'none';
            });
        });
    </script>
</body>
</html>
