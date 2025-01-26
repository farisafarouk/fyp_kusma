prepare($sql); $stmt->bind_param("i", $agent_id); $stmt->execute(); $result = $stmt->get_result(); ?>

<main class="dashboard-content">

  <section class="dashboard-section">

    <h1><i class="fas fa-bell"></i> Notifications</h1>

    <p>Stay informed with the latest updates from the admin.</p>


    <div class="notification-list">

      <?php if ($result->num_rows > 0): ?>

        <?php while ($notification = $result->fetch_assoc()): ?>

          <div class="notification-item">

            <h3><?php echo htmlspecialchars($notification['title']); ?></h3>

            <p><?php echo htmlspecialchars($notification['message']); ?></p>

            <span class="notification-time">

              <?php echo date("F j, Y, g:i a", strtotime($notification['created_at'])); ?>

            </span>

          </div>

        <?php endwhile; ?>

      <?php else: ?>

        <p class="no-notifications">No notifications at the moment.</p>

      <?php endif; ?>

    </div>

  </section>

</main>