<?php
require_once '../../../config/database.php';

$query = "SELECT c.consultant_id, u.name, c.expertise 
          FROM consultant_details c 
          INNER JOIN users u ON c.consultant_id = u.id 
          WHERE u.role = 'consultant'";
$result = $conn->query($query);

$consultants = [];
while ($row = $result->fetch_assoc()) {
    $consultants[] = $row;
}

header('Content-Type: application/json');
echo json_encode($consultants);
?>
