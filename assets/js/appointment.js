document.addEventListener("DOMContentLoaded", () => {
    const consultantSelect = document.getElementById("consultant");
    const scheduleSelect = document.getElementById("schedule");
    const appointmentMode = document.getElementById("appointmentMode");

    // Fetch consultants from the backend
    fetch("fetch_consultants.php")
        .then((response) => response.json())
        .then((consultants) => {
            consultants.forEach((consultant) => {
                const option = document.createElement("option");
                option.value = consultant.consultant_id;
                option.textContent = `${consultant.name} (${consultant.expertise})`;
                consultantSelect.appendChild(option);
            });
        });

    // Fetch available schedules for the selected consultant
    consultantSelect.addEventListener("change", () => {
        const consultantId = consultantSelect.value;
        const mode = appointmentMode.value;

        if (consultantId && mode) {
            fetch(`fetch_schedule.php?consultant_id=${consultantId}&mode=${mode}`)
                .then((response) => response.json())
                .then((schedules) => {
                    scheduleSelect.innerHTML = '<option value="" selected disabled>Select a time slot</option>';
                    schedules.forEach((schedule) => {
                        const option = document.createElement("option");
                        option.value = schedule.schedule_id;
                        option.textContent = `${schedule.day}, ${schedule.date} (${schedule.start_time} - ${schedule.end_time})`;
                        scheduleSelect.appendChild(option);
                    });
                });
        }
    });

    // Clear schedule options when appointment mode changes
    appointmentMode.addEventListener("change", () => {
        scheduleSelect.innerHTML = '<option value="" selected disabled>Select a time slot</option>';
    });
});
