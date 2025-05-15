document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const modal = document.getElementById('slotModal');
  const form = document.getElementById('slotForm');
  const addBtn = document.getElementById('openModalBtn');
  const cancelBtn = document.getElementById('cancelModal');
  const alertBox = document.getElementById('inlineAlert');
  const floatingMenu = document.getElementById('floatingMenu');
  const deleteModal = document.getElementById('deleteModal');
  const deleteThisBtn = document.getElementById('deleteThis');
  const deleteFutureBtn = document.getElementById('deleteFuture');
  const cancelDeleteBtn = document.getElementById('cancelDeleteModal');

  const startInput = document.getElementById('start');
  const durationInput = document.getElementById('duration');
  const modeInput = document.getElementById('mode');
  const recurrenceInput = document.getElementById('recurrence');
  const repeatUntilInput = document.getElementById('repeatUntil');
  const slotIdInput = document.getElementById('slotId');

  let selectedEvent = null;
  let deleteTargetId = null;
  let deleteTargetRecurring = false;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'timeGridWeek',
    selectable: true,
    editable: true,
    nowIndicator: true,
    slotMinTime: '08:00:00',
    slotMaxTime: '20:00:00',
    eventOverlap: false,
    events: 'fetch_schedule.php',

    select(info) {
      resetForm();
      slotIdInput.value = '';
      startInput.value = info.startStr.slice(0, 16);
      durationInput.value = '30';
      modal.classList.add('show');
    },

    eventClick(info) {
      selectedEvent = info.event;
      const { clientX: x, clientY: y } = info.jsEvent;
      floatingMenu.style.top = `${y}px`;
      floatingMenu.style.left = `${x}px`;
      floatingMenu.classList.remove('d-none');
    },

    eventDrop(info) {
      const ev = info.event;
      const payload = [{
        id: ev.id,
        start: ev.start.toISOString(),
        end: ev.end.toISOString(),
        mode: ev.extendedProps.mode,
        recurring: ev.extendedProps.recurring,
        pattern: ev.extendedProps.pattern,
        repeat_until: null
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
            showAlert('Slot updated.');
          }
        });
    },

    eventContent(arg) {
      const container = document.createElement('div');
      const { mode, recurring, pattern } = arg.event.extendedProps;
      container.innerHTML = `
        <div class="event-card">
          <div class="event-title">Available</div>
          <div class="event-badges">
            ${recurring && pattern !== 'none' ? `<span class="badge badge-${pattern}">${pattern}</span>` : ''}
            ${mode ? `<span class="badge badge-${mode}">${mode}</span>` : ''}
          </div>
        </div>`;
      return { domNodes: [container] };
    }
  });

  calendar.render();

  addBtn.addEventListener('click', () => {
    resetForm();
    slotIdInput.value = '';
    modal.classList.add('show');
  });

  cancelBtn.addEventListener('click', () => {
    modal.classList.remove('show');
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const start = new Date(startInput.value);
    const durationMinutes = parseInt(durationInput.value, 10);
    const end = new Date(start.getTime() + durationMinutes * 60000);

    const slot = [{
      id: slotIdInput.value || null,
      start: start.toISOString(),
      end: end.toISOString(),
      mode: modeInput.value,
      recurring: recurrenceInput.value !== 'none',
      pattern: recurrenceInput.value,
      repeat_until: repeatUntilInput.value
    }];

    fetch('save_schedule.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(slot)
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          calendar.refetchEvents();
          modal.classList.remove('show');
          showAlert('Slot saved successfully.');
        } else {
          showAlert(data.error || 'Failed to save.', true);
        }
      });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.floating-menu') && !e.target.closest('.event-card')) {
      floatingMenu.classList.add('d-none');
    }
  });

  document.getElementById('editSlotBtn').addEventListener('click', function () {
    if (!selectedEvent) return;
    resetForm();
    modal.classList.add('show');
    slotIdInput.value = selectedEvent.id;
    startInput.value = selectedEvent.start.toISOString().slice(0, 16);
    modeInput.value = selectedEvent.extendedProps.mode || 'online';
    recurrenceInput.value = selectedEvent.extendedProps.pattern || 'none';
    durationInput.value = '30';
    floatingMenu.classList.add('d-none');
  });

  document.getElementById('deleteSlotBtn').addEventListener('click', function () {
    if (!selectedEvent) return;
    deleteTargetId = selectedEvent.id;
    deleteTargetRecurring = selectedEvent.extendedProps.recurring === 1;
    if (deleteTargetRecurring) {
      deleteModal.classList.add('show');
    } else {
      deleteSlot('only');
    }
    floatingMenu.classList.add('d-none');
  });

  deleteThisBtn.onclick = () => {
    deleteSlot('only');
    deleteModal.classList.remove('show');
  };

  deleteFutureBtn.onclick = () => {
    deleteSlot('future');
    deleteModal.classList.remove('show');
  };

  cancelDeleteBtn.onclick = () => {
    deleteModal.classList.remove('show');
  };

  function deleteSlot(scope) {
    fetch('delete_schedule.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: deleteTargetId, scope })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          calendar.refetchEvents();
          showAlert('Slot deleted.');
        } else {
          showAlert('Failed to delete.', true);
        }
      });
  }

  function resetForm() {
    form.reset();
    repeatUntilInput.value = '';
    durationInput.value = '30';
  }

  function showAlert(msg, error = false) {
    alertBox.textContent = msg;
    alertBox.className = error ? 'inline-error' : 'inline-success';
    alertBox.classList.remove('d-none');
    setTimeout(() => alertBox.classList.add('d-none'), 3000);
  }
});
