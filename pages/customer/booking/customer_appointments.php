<?php
session_start();
require_once '../../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../../login/login.php");
  exit();
}

$customer_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT a.*, u.name AS consultant_name FROM appointments a JOIN consultants c ON a.consultant_id = c.id JOIN users u ON c.user_id = u.id WHERE a.customer_id = ? ORDER BY a.scheduled_date DESC, a.scheduled_time DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Appointments</title>
  <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
  <link rel="stylesheet" href="../../../assets/css/customer_appointments.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
          <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<?php include '../customer_navbar.php'; ?>

<div class="container">
  <div class="top-actions">
    <h1 class="page-title"><i class="fas fa-calendar-check"></i> My Appointments</h1>
    <a href="../booking/consultant_list.php" class="btn-primary">Book New Appointment</a>
  </div>

  <div class="filter-buttons">
    <button data-status="all" class="active">All</button>
    <button data-status="pending">Pending</button>
    <button data-status="confirmed">Confirmed</button>
    <button data-status="completed">Completed</button>
    <button data-status="canceled">Canceled</button>
  </div>

  <div id="appointmentContainer"></div>
  <p id="noAppointments" class="empty-message" style="display:none;">No appointments found for this category.</p>
</div>

<!-- Cancel Modal -->
<div id="cancelModal" class="modal">
  <div class="modal-content">
    <h3>Cancel Appointment</h3>
    <textarea id="cancelReason" placeholder="Reason for cancellation..." rows="4"></textarea>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="submitCancel()">Confirm Cancel</button>
      <button class="btn-reschedule" onclick="closeModal('cancelModal')">Back</button>
    </div>
  </div>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="modal">
  <div class="modal-content">
    <h3>Leave Feedback</h3>
    <div class="stars" id="ratingStars"></div>
    <textarea id="feedbackText" placeholder="Write your feedback here..." rows="4"></textarea>
    <div class="modal-actions">
      <button class="btn-reschedule" onclick="submitFeedback()">Submit Feedback</button>
      <button class="btn-cancel" onclick="closeModal('feedbackModal')">Cancel</button>
    </div>
  </div>
</div>
<!-- Reschedule Modal -->
<div id="rescheduleModal" class="modal">
  <div class="modal-content">
    <h3>Reschedule Appointment</h3>

    <label for="rescheduleReason"><strong>Reason for Rescheduling</strong></label>
    <textarea id="rescheduleReason" placeholder="Please explain why you want to reschedule..." rows="3" style="width: 100%; margin-bottom: 15px;"></textarea>

    <label><strong>Select a New Time Slot</strong></label>
    <div id="slotContainer" style="margin-bottom: 20px;">
      <div class="loading-spinner"></div>
    </div>

    <div class="modal-actions">
      <button class="btn-reschedule" id="confirmReschedule">Confirm Reschedule</button>
      <button class="btn-cancel" onclick="closeModal('rescheduleModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script>
const appointments = <?= json_encode($appointments) ?>;
let selectedCancelId = null;
let selectedRescheduleId = null;
let selectedScheduleId = null;
let selectedFeedbackId = null;
let currentRating = 0;

function renderAppointments(filter = 'all') {
  const container = document.getElementById('appointmentContainer');
  container.innerHTML = '';
  const filtered = appointments.filter(a => filter === 'all' || a.status === filter);
  document.getElementById('noAppointments').style.display = filtered.length === 0 ? 'block' : 'none';

  filtered.forEach(a => {
    const card = document.createElement('div');
    card.className = 'appointment-card';
    card.innerHTML = `
      <div class="card-header">
        <div>
          <p class="appt-label">With: <strong>${a.consultant_name}</strong></p>
          <p class="appt-mode">${a.appointment_mode.toUpperCase()} | ${a.duration} mins</p>
        </div>
        <span class="status ${a.status}">${a.status.toUpperCase()}</span>
      </div>
      <div class="card-body">
        <p><i class="far fa-calendar-alt"></i> ${a.scheduled_date} at ${a.scheduled_time}</p>
        ${a.reason_for_appointment ? `<p><strong>Reason:</strong> ${a.reason_for_appointment}</p>` : ''}
        ${a.status === 'canceled' && a.cancel_note ? `<p><strong>Cancel Note:</strong> ${a.cancel_note}</p>` : ''}
        ${a.status === 'completed' && a.feedback ? `<p><strong>Feedback:</strong> ${a.feedback}</p>` : ''}
        ${a.status === 'completed' && a.rating ? `<p><strong>Rating:</strong> ${'★'.repeat(a.rating)}${'☆'.repeat(5 - a.rating)}</p>` : ''}
      </div>
      <div class="card-actions">
        ${a.status === 'pending' ? `<button class="btn-cancel" onclick="openCancelModal(${a.id})">Cancel</button>` : ''}
        ${a.status === 'pending' ? `<button class="btn-reschedule" onclick="openRescheduleModal(${a.id}, ${a.consultant_id})">Reschedule</button>` : ''}
        ${a.status === 'completed' && !a.feedback ? `<button class="btn-feedback" onclick="openFeedbackModal(${a.id})">Leave Feedback</button>` : ''}
      </div>
    `;
    container.appendChild(card);
  });
}

function openCancelModal(id) {
  selectedCancelId = id;
  document.getElementById('cancelReason').value = '';
  document.getElementById('cancelModal').style.display = 'flex';
}

function submitCancel() {
  const reason = document.getElementById('cancelReason').value.trim();
  if (!reason) return showToast('Please provide a reason for cancellation.', 'error');
  fetch('cancel_appointment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: selectedCancelId, reason })
  })
  .then(res => res.json())
  .then(data => {
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1200);
  });
}

function openRescheduleModal(id, consultant_id) {
  selectedRescheduleId = id;
  selectedScheduleId = null;
  document.getElementById('rescheduleReason').value = '';
  document.getElementById('rescheduleModal').style.display = 'flex';
  document.getElementById('slotContainer').innerHTML = '<div class="loading-spinner"></div>';

  fetch('../booking/fetch_consultant_slots.php?consultant_id=' + consultant_id)
    .then(res => res.text())
    .then(html => {
      document.getElementById('slotContainer').innerHTML = html;
    });
}
function confirmBooking(slotId) {
  selectedScheduleId = slotId;

  // Remove 'selected' class from all slot buttons
  document.querySelectorAll('.slot-btn').forEach(btn => btn.classList.remove('selected'));

  // Add 'selected' class to the clicked one
  const selectedBtn = document.querySelector(`.slot-btn[data-slot-id="${slotId}"]`);
  if (selectedBtn) {
    selectedBtn.classList.add('selected');
  }
}


document.getElementById('confirmReschedule').onclick = () => {
  const reason = document.getElementById('rescheduleReason').value.trim();
  if (!selectedScheduleId || !reason) {
    showToast('Please select a slot and provide a reason.', 'error');
    return;
  }

  fetch('reschedule_appointment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      original_id: selectedRescheduleId,
      new_schedule_id: selectedScheduleId,
      reason: reason
    })
  })
  .then(res => res.json())
  .then(data => {
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1200);
  });
};

function openFeedbackModal(id) {
  selectedFeedbackId = id;
  currentRating = 0;
  document.getElementById('feedbackText').value = '';
  renderStars();
  document.getElementById('feedbackModal').style.display = 'flex';
}

function renderStars() {
  const stars = document.getElementById('ratingStars');
  stars.innerHTML = '';
  for (let i = 1; i <= 5; i++) {
    const star = document.createElement('i');
    star.className = i <= currentRating ? 'fas fa-star' : 'far fa-star';
    star.style.cursor = 'pointer';
    star.onclick = () => {
      currentRating = i;
      renderStars();
    };
    stars.appendChild(star);
  }
}

function submitFeedback() {
  const feedback = document.getElementById('feedbackText').value.trim();
  if (!currentRating || !feedback) {
    return showToast('Please provide a rating and feedback.', 'error');
  }
  fetch('submit_feedback.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: selectedFeedbackId, rating: currentRating, feedback })
  })
  .then(res => res.json())
  .then(data => {
    showToast(data.message, data.success ? 'success' : 'error');
    if (data.success) setTimeout(() => location.reload(), 1200);
  });
}

document.querySelectorAll('.filter-buttons button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-buttons button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderAppointments(btn.dataset.status);
  });
});

function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function showToast(msg, type) {
  const toast = document.getElementById('toast');
  toast.textContent = msg;
  toast.className = `toast show ${type}`;
  setTimeout(() => toast.className = 'toast', 3000);
}

renderAppointments();
</script>

</body>
</html>
