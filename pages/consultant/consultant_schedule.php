<?php
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8" />
  <title>Consultant Schedule - KUSMA</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../../assets/css/consultant_schedule.css" />
</head>
<body>

<div class="calendar-wrapper">
  <header class="calendar-header">
    <h1>My Availability Scheduling</h1>
    <div class="calendar-controls">
      <a href="consultantdashboard.php" class="btn-dashboard"><i class="bi bi-house-fill"></i> Dashboard</a>
      <button id="addSlotBtn" class="btn-add-slot"><i class="bi bi-plus-lg"></i> Add Slot</button>
    </div>
  </header>

 <div class="calendar-scroll">
  <div id="inlineAlert" class="inline-success d-none"></div>
  <div id="calendar"></div>
</div>


<!-- Slot Modal -->
<div id="slotModal" class="modal">
  <div class="modal-content">
    <h3>Manage Slot</h3>
    <form id="slotForm">
      <input type="hidden" id="slotId">
      <label>Start Time</label>
      <input type="datetime-local" id="start" required>
      <label>Duration</label>
      <select id="duration">
        <option value="30">30 minutes</option>
        <option value="60">1 hour</option>
        <option value="90">1.5 hours</option>
        <option value="120">2 hours</option>
      </select>
      <label>Mode</label>
      <select id="mode" required>
        <option value="online">Online</option>
        <option value="in-person">In-Person</option>
        <option value="hybrid">Hybrid</option>
      </select>
      <label>Recurrence</label>
      <select id="recurrence">
        <option value="none">None</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
      </select>
      <label>Repeat Until (optional)</label>
      <input type="date" id="repeatUntil">
      <div class="modal-actions">
        <button type="submit" class="btn-submit">Save</button>
        <button type="button" id="cancelModal" class="btn-cancel">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
  <div class="modal-content">
    <h3>Delete Slot</h3>
    <p>Do you want to delete just this slot or all future slots?</p>
    <div class="modal-actions">
      <button id="confirmDeleteThis" class="btn-submit">This Slot Only</button>
      <button id="confirmDeleteFuture" class="btn-cancel">This & Future</button>
      <button id="cancelDeleteModal" class="btn-cancel">Cancel</button>
    </div>
  </div>
</div>

<!-- Edit Confirmation Modal -->
<div id="editConfirmModal" class="modal">
  <div class="modal-content">
    <h3>Edit Recurring Slot</h3>
    <p>Do you want to edit just this slot or all future slots?</p>
    <div class="modal-actions">
      <button id="confirmEditThis" class="btn-submit">This Slot Only</button>
      <button id="confirmEditFuture" class="btn-cancel">This & Future</button>
      <button id="cancelEditModal" class="btn-cancel">Cancel</button>
    </div>
  </div>
</div>

<!-- Floating Menu -->
<div id="floatingMenu" class="floating-menu d-none">
  <button id="editSlotBtn"><i class="bi bi-pencil-square"></i> Edit</button>
  <button id="deleteSlotBtn"><i class="bi bi-trash"></i> Delete</button>
</div>

<!-- JS Logic -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const modal = document.getElementById('slotModal');
  const form = document.getElementById('slotForm');
  const alertBox = document.getElementById('inlineAlert');
  const floatingMenu = document.getElementById('floatingMenu');
  const confirmDeleteModal = document.getElementById('deleteConfirmModal');
  const confirmEditModal = document.getElementById('editConfirmModal');

  const addBtn = document.getElementById('addSlotBtn');
  const cancelBtn = document.getElementById('cancelModal');
  const editBtn = document.getElementById('editSlotBtn');
  const deleteBtn = document.getElementById('deleteSlotBtn');
  const confirmDeleteThis = document.getElementById('confirmDeleteThis');
  const confirmDeleteFuture = document.getElementById('confirmDeleteFuture');
  const confirmEditThis = document.getElementById('confirmEditThis');
  const confirmEditFuture = document.getElementById('confirmEditFuture');
  const cancelEditModal = document.getElementById('cancelEditModal');
  const cancelDeleteModal = document.getElementById('cancelDeleteModal');

  const startInput = document.getElementById('start');
  const durationInput = document.getElementById('duration');
  const modeInput = document.getElementById('mode');
  const recurrenceInput = document.getElementById('recurrence');
  const repeatUntilInput = document.getElementById('repeatUntil');
  const slotIdInput = document.getElementById('slotId');

  let selectedEvent = null;

  // ✅ Disable repeatUntil when recurrence is 'none'
  recurrenceInput.addEventListener('change', () => {
    const isNone = recurrenceInput.value === 'none';
    repeatUntilInput.disabled = isNone;
    if (isNone) repeatUntilInput.value = '';
  });

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    nowIndicator: true,
    timeZone: 'local',
    slotMinTime: '06:00:00',
    slotMaxTime: '22:30:00',
    scrollTime: '07:00:00',
    slotDuration: '00:30:00',
    selectable: true,
    editable: true,
    eventOverlap: false,
    eventDurationEditable: true,
    events: 'fetch_schedule.php',
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    select(info) {
      resetForm();
      selectedEvent = null;
      startInput.value = new Date(info.start.getTime() - (info.start.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
      form.dataset.scope = 'single';
      modal.classList.add('show');
      startInput.focus(); // ✅ Autofocus
    },

    eventClick(info) {
      selectedEvent = info.event;
      const { clientX, clientY } = info.jsEvent; // ✅ Better menu placement
      floatingMenu.style.top = `${clientY + window.scrollY}px`;
      floatingMenu.style.left = `${clientX}px`;
      floatingMenu.classList.remove('d-none');
    },

    eventDrop(info) {
      const ev = info.event;
      const format = (date) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
      };
      const payload = [{
        id: ev.id,
        start: format(ev.start),
        end: format(ev.end),
        mode: ev.extendedProps.mode || 'online',
        recurring: ev.extendedProps.recurring || false,
        pattern: ev.extendedProps.pattern || 'none',
        repeat_until: null,
        scope: 'single'
      }];

      fetch('save_schedule.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          calendar.refetchEvents();
          showAlert("Slot moved and saved.");
        } else {
          showAlert(data.error || "Failed to update slot.", true);
        }
      });
    },

    eventContent(arg) {
  const { mode, recurring, pattern } = arg.event.extendedProps;
  const duration = (arg.event.end - arg.event.start) / 60000;
  const wrapper = document.createElement('div');
  wrapper.className = "event-card" + (duration <= 60 ? " short-slot" : "");

  wrapper.innerHTML = `
    <div class="event-title">Available</div>
    <div class="event-badges">
      ${recurring && pattern !== 'none' ? `<span class="badge badge-${pattern}">${pattern}</span>` : ''}
      ${mode ? `<span class="badge badge-${mode}">${mode}</span>` : ''}
    </div>`;
  return { domNodes: [wrapper] };
}

  });

  calendar.render();

  addBtn.onclick = () => {
    resetForm();
    form.dataset.scope = 'single';
    modal.classList.add('show');
    startInput.focus(); // ✅ Auto-focus
  };

  cancelBtn.onclick = () => modal.classList.remove('show');
  cancelEditModal.onclick = () => confirmEditModal.classList.remove('show');
  cancelDeleteModal.onclick = () => confirmDeleteModal.classList.remove('show');

  editBtn.onclick = () => {
    if (!selectedEvent) return;
    floatingMenu.classList.add('d-none');
    if (selectedEvent.extendedProps.recurring) {
      confirmEditModal.classList.add('show');
    } else {
      showEditForm('single');
    }
  };

  confirmEditThis.onclick = () => {
    showEditForm('single');
    confirmEditModal.classList.remove('show');
  };

  confirmEditFuture.onclick = () => {
    showEditForm('future');
    confirmEditModal.classList.remove('show');
  };

  function showEditForm(scope) {
    if (!selectedEvent) return;
    const start = new Date(selectedEvent.start.getTime() - (selectedEvent.start.getTimezoneOffset() * 60000));
    slotIdInput.value = selectedEvent.id;
    startInput.value = start.toISOString().slice(0, 16);
    durationInput.value = Math.round((selectedEvent.end - selectedEvent.start) / 60000);
    modeInput.value = selectedEvent.extendedProps.mode || 'online';
    recurrenceInput.value = selectedEvent.extendedProps.pattern || 'none';
    repeatUntilInput.value = '';
    form.dataset.scope = scope;
    modal.classList.add('show');
    startInput.focus(); // ✅ Auto-focus on edit
  }

  // Triggered when the ⋮ delete button is clicked
deleteBtn.onclick = () => {
  if (!selectedEvent) return;
  floatingMenu.classList.add('d-none');
  if (selectedEvent.extendedProps.recurring) {
    confirmDeleteModal.classList.add('show');
  } else {
    deleteSlot('single');
  }
};

// Confirm modal actions
confirmDeleteThis.onclick = () => {
  deleteSlot('single');
  confirmDeleteModal.classList.remove('show');
};

confirmDeleteFuture.onclick = () => {
  deleteSlot('future');
  confirmDeleteModal.classList.remove('show');
};

cancelDeleteModal.onclick = () => {
  confirmDeleteModal.classList.remove('show');
};

// Actual delete logic
function deleteSlot(scope) {
  fetch('delete_schedule.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: selectedEvent.id,
      scope: scope
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      calendar.refetchEvents();
      showAlert(scope === 'single' ? 'Slot deleted.' : 'Recurring slots deleted.');
    } else {
      showAlert(data.error || 'Failed to delete slot.', true);
    }
  })
  .catch(err => {
    console.error('Delete failed:', err);
    showAlert("Server error", true);
  });
}


  form.onsubmit = function (e) {
    e.preventDefault();

    const start = new Date(startInput.value);
    const duration = parseInt(durationInput.value);
    const end = new Date(start.getTime() + duration * 60000);
    const toISOStringLocal = (d) =>
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}T${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;

    const payload = {
      id: slotIdInput.value || null,
      start: toISOStringLocal(start),
      end: toISOStringLocal(end),
      mode: modeInput.value,
      recurring: recurrenceInput.value !== 'none',
      pattern: recurrenceInput.value,
      repeat_until: repeatUntilInput.value,
      scope: form.dataset.scope || 'single'
    };

    fetch('save_schedule.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify([payload])
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        calendar.refetchEvents();
        modal.classList.remove('show');
        showAlert("Slot saved successfully.");
      } else {
        showAlert(data.error || 'Failed to save slot.', true);
      }
    });
  };

  function resetForm() {
    form.reset();
    slotIdInput.value = '';
    durationInput.value = '30';
  }

  function showAlert(msg, error = false) {
    alertBox.textContent = msg;
    alertBox.className = error ? 'inline-alert error' : 'inline-success';
    alertBox.classList.remove('d-none');
    setTimeout(() => alertBox.classList.add('d-none'), 3000);
  }

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#floatingMenu') && !e.target.closest('.fc-event')) {
      floatingMenu.classList.add('d-none');
    }
  });
});

</script>

</body>
</html>
