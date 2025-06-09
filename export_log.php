<?php
// export_log.php

// อ่าน JSON
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$galleryId      = $data['galleryId']        ?? '';
$processingTime = $data['processingTime']   ?? '';
$result         = $data['result']           ?? '';
$descriptor     = $data['descriptor']       ?? '';  // ค่าที่ส่งมาจาก FaceMatcher (array หรือ string)
$matchedImage   = $data['matchedImage']     ?? '';  // ชื่อไฟล์ gallery ที่ match ผ่าน

// ตั้งโฟลเดอร์ logs/ ข้าง ๆ export_log.php
$logDir  = __DIR__ . '/logs';
$logFile = $logDir . '/gallery_log.csv';

// สร้างโฟลเดอร์ถ้ายังไม่มี
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// สร้าง header ถ้าไฟล์ยังไม่มี
if (!file_exists($logFile)) {
    $h = fopen($logFile, 'w');
    // เพิ่มคอลัมน์ descriptor และ matched_image
    fputcsv($h, [
      'gallery_id',
      'processing_time_s',
      'result',
      'matched_image',
      'descriptor'
    ]);
    fclose($h);
}

// ต่อท้ายข้อมูล
$h = fopen($logFile, 'a');
// ถ้า $descriptor เป็น array ให้แปลงเป็น JSON string ก่อน
$descString = is_array($descriptor) ? json_encode($descriptor) : $descriptor;
fputcsv($h, [
    $galleryId,
    $processingTime,
    $result ? 'true' : 'false',
    $matchedImage,
    $descString
]);
fclose($h);

// ตอบกลับ JS
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);
