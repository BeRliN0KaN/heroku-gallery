<?php
include '../../../includes/db.php';  // เชื่อมต่อฐานข้อมูล

$sql = "SELECT device_type, COUNT(*) AS total FROM tbl_site_visitors GROUP BY device_type";
$result = $connection->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// ส่งข้อมูลในรูปแบบ JSON
header('Content-Type: application/json');
echo json_encode($data);
?>