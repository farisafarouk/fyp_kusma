<?php
session_start();
require_once '../../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../../login/login.php");
  exit();
}

$consultants = $conn->query("SELECT c.id, u.name, c.expertise, c.rate_per_hour FROM consultants c JOIN users u ON c.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Consultants</title>
  <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
  <link rel="stylesheet" href="../../../assets/css/consultant_list.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<?php include '../customer_navbar.php'; ?>

<div class="container">
  <h1 class="page-title"><i class="fas fa-user-tie"></i> Browse Consultants</h1>
  <div class="consultant-grid">
    <?php while ($row = $consultants->fetch_assoc()): ?>
      <div class="consultant-card">
        <h3><?= htmlspecialchars($row['name']) ?></h3>
        <p><strong>Expertise:</strong> <?= htmlspecialchars($row['expertise']) ?></p>
        <p><strong>Rate:</strong> RM <?= htmlspecialchars($row['rate_per_hour']) ?> / hour</p>
        <button class="btn-view" data-id="<?= $row['id'] ?>">View Availability</button>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- Slot Modal -->
<div class="modal" id="slotModal">
  <div class="modal-content centered">
    <span class="close" onclick="closeModal('slotModal')">&times;</span>
    <h2>Available Slots</h2>
    <div id="slotContainer">
      <div class="loading-spinner"></div>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmModal">
  <div class="modal-content centered">
    <span class="close" onclick="closeModal('confirmModal')">&times;</span>
    <h2>Confirm Appointment</h2>
    <p id="confirmText"></p>
    <div class="modal-actions">
      <button class="btn-confirm" id="confirmBtn" onclick="submitBooking()">Yes, Confirm</button>
      <button class="btn-cancel" onclick="closeModal('confirmModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- Toast Message -->
<div id="toast" class="toast"></div>

<script>
let selectedSlotId = null;
let selectedTime = '';
let selectedEnd = '';
let selectedMode = '';

function confirmBooking(slotId, startTime, endTime, mode) {
  selectedSlotId = slotId;
  selectedTime = startTime;
  selectedEnd = endTime;
  selectedMode = mode;
  document.getElementById('confirmText').textContent = `Confirm appointment on ${startTime} to ${endTime} (${mode})?`;
  document.getElementById('confirmModal').style.display = 'flex';
  document.getElementById('confirmBtn').disabled = false;
}

function submitBooking() {
  const btn = document.getElementById('confirmBtn');
  btn.disabled = true;

  if (!selectedSlotId) {
    showToast('Please select a valid time slot.', 'error');
    return;
  }

  fetch('save_appointment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ slot_id: selectedSlotId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      closeModal('confirmModal');
      closeModal('slotModal');
    } else {
      showToast(data.message, 'error');
    }
  })
  .catch(() => {
    showToast('Booking failed. Try again later.', 'error');
  })
  .finally(() => {
    btn.disabled = false;
  });
}

function showToast(message, type) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = `toast show ${type}`;
  setTimeout(() => toast.className = 'toast', 3000);
}

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

document.querySelectorAll('.btn-view').forEach(btn => {
  btn.addEventListener('click', () => {
    const consultantId = btn.dataset.id;
    document.getElementById('slotModal').style.display = 'flex';
    document.getElementById('slotContainer').innerHTML = '<div class="loading-spinner"></div>';

    fetch('fetch_consultant_slots.php?consultant_id=' + consultantId)
      .then(res => res.text())
      .then(html => {
        document.getElementById('slotContainer').innerHTML = html;
      });
  });
});
</script>
</body>
</html>
