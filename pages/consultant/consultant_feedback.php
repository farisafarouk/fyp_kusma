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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Consultant Feedback</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css" />
  <link rel="stylesheet" href="../..//assets/css/consultant_feedback.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<div class="dashboard-container">
  <?php include 'consultantsidebar.php'; ?>
  <div class="dashboard-content">
    <section class="dashboard-section">
      <header>
        <h1><i class="fas fa-star"></i> Feedback & Ratings</h1>
        <p class="muted">View and filter feedback from your completed appointments.</p>
      </header>

      <div class="feedback-filter">
        <select id="ratingFilter">
          <option value="">All Ratings</option>
          <option value="5">5 Stars</option>
          <option value="4">4 Stars</option>
          <option value="3">3 Stars</option>
          <option value="2">2 Stars</option>
          <option value="1">1 Star</option>
        </select>
        <input type="date" id="fromDate">
        <span>to</span>
        <input type="date" id="toDate">
        <button onclick="loadFeedback()">Apply Filters</button>
      </div>

      <div id="feedbackContainer" class="feedback-list"></div>
      <p id="noFeedback" class="no-feedback" style="display: none;">No feedback found.</p>
    </section>
  </div>
</div>


<script>
function loadFeedback() {
  const rating = document.getElementById('ratingFilter').value;
  const from = document.getElementById('fromDate').value;
  const to = document.getElementById('toDate').value;

  const params = new URLSearchParams();
  if (rating) params.append('rating', rating);
  if (from && to) {
    params.append('from', from);
    params.append('to', to);
  }

  fetch('fetch_feedback.php?' + params.toString())
    .then(res => res.json())
    .then(data => {
      const container = document.getElementById('feedbackContainer');
      container.innerHTML = '';

      if (!data.length) {
        document.getElementById('noFeedback').style.display = 'block';
        return;
      }
      document.getElementById('noFeedback').style.display = 'none';

      data.forEach(fb => {
        const entry = document.createElement('div');
        entry.className = 'feedback-card';
        entry.innerHTML = `
          <div class="card-header">
            <h4>${fb.customer_name}</h4>
            <span class="rating">${'★'.repeat(fb.rating)}${'☆'.repeat(5 - fb.rating)}</span>
          </div>
          <p class="feedback-date">${fb.scheduled_date}</p>
          <p class="feedback-text">${fb.feedback || '<em>No written feedback provided.</em>'}</p>
        `;
        container.appendChild(entry);
      });
    });
}

window.onload = loadFeedback;
</script>
</body>
</html>
