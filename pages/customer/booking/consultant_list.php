<?php
require_once '../../../config/database.php';

// Fetch consultants with schedules and profile pictures
$query = "
    SELECT 
        u.id AS consultant_id,
        u.name,
        u.email,
        u.profile_picture,
        cd.expertise,
        cd.rate,
        cs.day,
        cs.date,
        cs.start_time,
        cs.end_time,
        cs.appointment_mode
    FROM 
        users u
    INNER JOIN 
        consultant_details cd ON u.id = cd.consultant_id
    LEFT JOIN 
        consultant_schedule cs ON u.id = cs.consultant_id
    WHERE 
        u.role = 'consultant'
    ORDER BY 
        u.name, cs.date, cs.start_time
";

$result = $conn->query($query);

$consultants = [];
while ($row = $result->fetch_assoc()) {
    $consultantId = $row['consultant_id'];
    if (!isset($consultants[$consultantId])) {
        $consultants[$consultantId] = [
            'id' => $row['consultant_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'profile_picture' => $row['profile_picture'],
            'expertise' => $row['expertise'],
            'rate' => $row['rate'],
            'schedules' => [],
        ];
    }

    if (!empty($row['day'])) {
        $consultants[$consultantId]['schedules'][] = [
            'day' => $row['day'],
            'date' => $row['date'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'appointment_mode' => $row['appointment_mode'],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultants</title>
    <link rel="stylesheet" href="../../../assets/css/consultant_list.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Meet Our Consultants</h1>
            <p>Find the right consultant for your business needs and book an appointment.</p>
        </header>
        <div class="consultant-grid">
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <div class="card-header">
                        <img src="../../../uploads/profile_pictures/<?= htmlspecialchars($consultant['profile_picture']) ?>" alt="Profile Picture of <?= htmlspecialchars($consultant['name']) ?>">
                    </div>
                    <div class="card-body">
                        <h2><?= htmlspecialchars($consultant['name']) ?></h2>
                        <p class="expertise"><?= htmlspecialchars($consultant['expertise']) ?></p>
                        <p class="rate">RM<?= number_format($consultant['rate'], 2) ?>/hr</p>
                        <p class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($consultant['email']) ?></p>
                        <div class="schedule">
                            <p><strong>Available Schedules:</strong></p>
                            <?php if (!empty($consultant['schedules'])): ?>
                                <ul>
                                    <?php foreach ($consultant['schedules'] as $schedule): ?>
                                        <li>
                                            <?= htmlspecialchars($schedule['day']) ?>, 
                                            <?= htmlspecialchars($schedule['date']) ?>: 
                                            <?= htmlspecialchars($schedule['start_time']) ?> - 
                                            <?= htmlspecialchars($schedule['end_time']) ?> 
                                            (<?= htmlspecialchars($schedule['appointment_mode']) ?>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No available schedules.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="booking_page.php?consultant_id=<?= $consultant['id'] ?>" class="book-btn">Book Now</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
