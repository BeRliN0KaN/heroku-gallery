<?php
// get_descriptors.php
// คืนค่าชุด descriptors ของแต่ละภาพใน activity specified

// แสดง error เพื่อดีบักชั่วคราว
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตั้ง header ให้เป็น JSON
header('Content-Type: application/json');

// เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/includes/db.php';

// ตรวจสอบการเชื่อมต่อ
if (!isset($connection) || !$connection) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// รับ activity_id
if (!isset($_GET['activity_id']) || !ctype_digit($_GET['activity_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid activity_id']);
    exit;
}
$activityId = intval($_GET['activity_id']);

// Query ทั้งหมด ไม่กรอง descriptors NULL เพื่อดูผลลัพธ์
$sql = "SELECT image_name, descriptors FROM tbl_activity_gallery WHERE activity_id = {$activityId}";
$res = mysqli_query($connection, $sql);
if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($connection)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $descJson = $row['descriptors'];
    $descArr  = json_decode($descJson, true);
    if (!is_array($descArr)) {
        // กรณี NULL หรือ ไม่ใช่ JSON ให้เซ็ตเป็น empty array
        $descArr = [];
    }
    $data[] = [
        'label'       => $row['image_name'],
        'descriptors' => $descArr
    ];
}

// คืนค่า JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
