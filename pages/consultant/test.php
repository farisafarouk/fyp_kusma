<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test Calendar</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <style>
    body {
      font-family: sans-serif;
      padding: 40px;
      background: #f6f6f6;
    }
    #calendar {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      min-height: 600px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div id="calendar"></div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      console.log("✅ FullCalendar is running");
      const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'timeGridWeek',
        events: [
          { title: 'Available Slot', start: '2025-05-08T10:00:00', end: '2025-05-08T11:00:00' }
        ]
      });
      calendar.render();
    });
  </script>
</body>
</html>
