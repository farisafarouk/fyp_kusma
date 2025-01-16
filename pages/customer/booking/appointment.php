<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Appointment Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/customer_appointment.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Consultant Appointment Booking</h1>
        <p>Book an appointment with our consultants for personalized guidance.</p>

        <form id="bookingForm" action="process_appointment.php" method="POST">
            <!-- Consultant Selection -->
            <div class="mb-3">
                <label for="consultant" class="form-label">Select Consultant</label>
                <select class="form-select" id="consultant" name="consultant_id" required>
                    <option value="" selected disabled>Choose a consultant</option>
                    <!-- Populated dynamically -->
                </select>
            </div>

            <!-- Appointment Mode -->
            <div class="mb-3">
                <label for="appointmentMode" class="form-label">Appointment Mode</label>
                <select class="form-select" id="appointmentMode" name="appointment_mode" required>
                    <option value="" selected disabled>Select Appointment Mode</option>
                    <option value="online">Online</option>
                    <option value="in-person">In-Person</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>

            <!-- Available Schedule -->
            <div class="mb-3">
                <label for="schedule" class="form-label">Available Schedule</label>
                <select class="form-select" id="schedule" name="schedule_id" required>
                    <option value="" selected disabled>Select a time slot</option>
                    <!-- Populated dynamically -->
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-100">Book Appointment</button>
        </form>
    </div>

    <script src="../../../assets/js/appointment.js"></script>
</body>
</html>
