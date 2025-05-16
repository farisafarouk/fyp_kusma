<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    header("Location: ../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Consultant Appointments</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css">
  <link rel="stylesheet" href="../../assets/css/consultant_appointments.css">
  <link rel="stylesheet" href="../../assets/css/consultant_profile.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <?php include 'consultantsidebar.php'; ?>

  <div class="dashboard-content">
    <div class="dashboard-section">
      <header>
        <h1>Manage Appointments</h1>
        <div class="search-filter-bar">
          <input type="text" id="searchInput" class="search-bar" placeholder="Search by name or email...">
          <div class="filter-buttons">
            <button class="active" data-status="all">All</button>
            <button data-status="pending">Pending</button>
            <button data-status="confirmed">Confirmed</button>
            <button data-status="completed">Completed</button>
            <button data-status="canceled">Canceled</button>
          </div>
        </div>
      </header>

      <div id="appointmentsContainer" class="appointments-grid"></div>
      <p id="noResults" class="no-results" style="display: none;">No appointments found.</p>
    </div>
  </div>
</div>

<!-- Cancel Modal -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <h3>Cancel Appointment</h3>
    <div class="form-group">
      <label for="rejectReason">Reason for Cancellation</label>
      <textarea id="rejectReason" class="form-textarea" rows="4" placeholder="Why are you cancelling this appointment?"></textarea>
    </div>
    <div class="modal-actions">
      <button id="submitReject" class="btn-danger" type="button">Confirm Cancel</button>
      <button id="cancelReject" class="btn-secondary" type="button">Back</button>
    </div>
  </div>
</div>

<!-- Reschedule Modal -->
<div id="rescheduleModal" class="modal">
  <div class="modal-content">
    <h3>Reschedule Appointment</h3>
    <div class="form-group">
      <label for="rescheduleDate">Select Date</label>
      <select id="rescheduleDate" class="form-select"></select>
    </div>
    <div class="form-group">
      <label for="rescheduleTime">Select Time</label>
      <select id="rescheduleTime" class="form-select"></select>
    </div>
    <div class="form-group">
      <label for="rescheduleReason">Reason</label>
      <textarea id="rescheduleReason" class="form-textarea" rows="3" placeholder="Optional note for rescheduling..."></textarea>
    </div>
    <div class="modal-actions">
      <button id="submitReschedule" class="btn-confirm" type="button">Confirm Reschedule</button>
      <button id="cancelReschedule" class="btn-secondary" type="button">Cancel</button>
    </div>
  </div>
</div>

<script>
let appointments = [];
let selectedRejectId = null;
let selectedRescheduleId = null;

function fetchAppointments() {
  fetch('fetch_appointments.php')
    .then(res => res.json())
    .then(data => {
      appointments = data;
      renderAppointments('all');
    });
}

function renderAppointments(statusFilter) {
  const container = document.getElementById('appointmentsContainer');
  const search = document.getElementById('searchInput').value.toLowerCase();
  container.innerHTML = '';

  const filtered = appointments.filter(app => {
    const matchesStatus = statusFilter === 'all' || app.status === statusFilter;
    const matchesSearch = app.customer_name?.toLowerCase().includes(search) || app.customer_email?.toLowerCase().includes(search);
    return matchesStatus && matchesSearch;
  });

  document.getElementById('noResults').style.display = filtered.length === 0 ? 'block' : 'none';

  filtered.forEach(app => {
    const card = document.createElement('div');
    card.className = 'appointment-card';
    const readableDuration = app.duration + ' mins';

    card.innerHTML = `
      <div class="card-header">
        <h3>${app.customer_name}</h3>
        <span class="status ${app.status}">${app.status.toUpperCase()}</span>
      </div>
      <div class="card-body">
        <p><strong>Email:</strong> ${app.customer_email}</p>
        <p><strong>Date:</strong> ${app.scheduled_date}</p>
        <p><strong>Time:</strong> ${app.scheduled_time}</p>
        <p><strong>Duration:</strong> ${readableDuration}</p>
        <p><strong>Mode:</strong> ${app.appointment_mode}</p>
        ${app.status === 'canceled' && app.cancel_note ? `<p><strong>Note:</strong> ${app.cancel_note}</p>` : ''}
      </div>
      ${renderActions(app)}
    `;
    container.appendChild(card);
  });
}

function renderActions(app) {
  if (app.status === 'pending') {
    return `
      <div class="card-actions">
        <button class="btn-confirm" onclick="updateStatus(${app.id}, 'confirmed')">Confirm</button>
        <button class="btn-cancel" onclick="openRejectModal(${app.id})">Cancel</button>
      </div>
    `;
  } else if (app.status === 'confirmed') {
    return `
      <div class="card-actions">
        <button class="btn-complete" onclick="updateStatus(${app.id}, 'completed')">Complete</button>
        <button class="btn-cancel" onclick="openRescheduleModal(${app.id})">Reschedule</button>
      </div>
    `;
  }
  return '';
}

function updateStatus(id, status, cancel_note = '') {
  fetch('appointment_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, status, cancel_note })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      fetchAppointments();
      document.getElementById('rejectModal').classList.remove('show');
      document.getElementById('rescheduleModal').classList.remove('show');
      showToast(`Appointment ${status}.`);
    } else {
      alert(data.error || 'Action failed.');
    }
  });
}

function openRejectModal(id) {
  selectedRejectId = id;
  document.getElementById('rejectModal').classList.add('show');
}

document.getElementById('submitReject').onclick = () => {
  const reason = document.getElementById('rejectReason').value.trim();
  if (!reason) return alert('Please provide a reason.');
  updateStatus(selectedRejectId, 'canceled', reason);
};

document.getElementById('cancelReject').onclick = () => {
  document.getElementById('rejectModal').classList.remove('show');
};

function openRescheduleModal(id) {
  selectedRescheduleId = id;
  document.getElementById('rescheduleModal').classList.add('show');

  const appointment = appointments.find(a => a.id === id);

  if (!appointment || !appointment.consultant_id) {
    console.error("❌ Consultant ID not found for appointment ID:", id, appointment);
    alert("Unable to load available slots. Consultant ID missing.");
    return;
  }

  console.log("✅ Reschedule modal opened for appointment:", appointment.id, "Consultant ID:", appointment.consultant_id);

  const dateSelect = document.getElementById('rescheduleDate');
  const timeSelect = document.getElementById('rescheduleTime');
  const reasonBox = document.getElementById('rescheduleReason');

  dateSelect.innerHTML = '';
  timeSelect.innerHTML = '';
  reasonBox.value = '';

  fetch(`fetch_available_slots.php?consultant_id=${appointment.consultant_id}`)
    .then(res => res.json())
    .then(data => {
      const dates = Object.keys(data);
      if (dates.length === 0) {
        dateSelect.innerHTML = '<option>No available dates</option>';
        return;
      }

      // Populate date dropdown
      dates.forEach(date => {
        const opt = document.createElement('option');
        opt.value = date;
        opt.textContent = date;
        dateSelect.appendChild(opt);
      });

      // Hook time dropdown update
      dateSelect.onchange = () => updateTimeOptions(dateSelect.value, data);

      // Pre-select the original date
      dateSelect.value = appointment.scheduled_date;
      updateTimeOptions(appointment.scheduled_date, data);

      // Optional: try to select the current scheduled time
      setTimeout(() => {
        for (let opt of timeSelect.options) {
          if (opt.textContent.includes(appointment.scheduled_time)) {
            timeSelect.value = opt.value;
            break;
          }
        }
      }, 100);
    })
    .catch(error => {
      console.error("❌ Failed to fetch available slots:", error);
      alert("Error fetching available slots.");
    });
}


function updateTimeOptions(date, data) {
  const timeSelect = document.getElementById('rescheduleTime');
  timeSelect.innerHTML = '';
  (data[date] || []).forEach(slot => {
    const opt = document.createElement('option');
    opt.value = slot.id;
    opt.textContent = `${slot.start_time} - ${slot.end_time}`;
    timeSelect.appendChild(opt);
  });
}


document.getElementById('submitReschedule').onclick = () => {
  const timeSelect = document.getElementById('rescheduleTime');
  const slot_id = timeSelect.value;
  const cancel_note = document.getElementById('rescheduleReason').value.trim();

  if (!slot_id) return alert('Please select a valid time slot.');

  fetch('appointment_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: selectedRescheduleId,
      status: 'rescheduled',
      slot_id: parseInt(slot_id),
      cancel_note
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      fetchAppointments();
      document.getElementById('rescheduleModal').classList.remove('show');
      showToast("Appointment rescheduled.");
    } else {
      alert(data.error || 'Rescheduling failed.');
    }
  });
};

document.getElementById('cancelReschedule').onclick = () => {
  document.getElementById('rescheduleModal').classList.remove('show');
};

document.querySelectorAll('.filter-buttons button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.filter-buttons button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderAppointments(btn.dataset.status);
  });
});

document.getElementById('searchInput').addEventListener('input', () => {
  const active = document.querySelector('.filter-buttons button.active').dataset.status;
  renderAppointments(active);
});

function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => {
    toast.classList.remove('show');
  }, 2500);
}

fetchAppointments();
</script>

<div id="toast" class="toast"></div>

</body>
</html>
