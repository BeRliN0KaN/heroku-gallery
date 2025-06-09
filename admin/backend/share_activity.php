<?php
// share_activity.php
header('Content-Type: application/json');
require '../../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success'=>false,'message'=>'คุณต้องล็อกอินก่อน']);
  exit;
}

$fromUser    = $_SESSION['user_id'];
$activityId  = intval($_POST['activity_id'] ?? 0);
$username    = trim(mysqli_real_escape_string($connection, $_POST['username'] ?? ''));

if ($activityId <= 0 || $username === '') {
  echo json_encode(['success'=>false,'message'=>'ข้อมูลไม่ครบถ้วน']);
  exit;
}

// หา user_id จาก username
$sqlUser = "SELECT user_id FROM tbl_users WHERE user_name = '$username' LIMIT 1";
$resUser = mysqli_query($connection, $sqlUser);
if (!$resUser || mysqli_num_rows($resUser) === 0) {
  echo json_encode(['success'=>false,'message'=>"ไม่พบผู้ใช้ “$username” ในระบบ"]);
  exit;
}
$rowUser = mysqli_fetch_assoc($resUser);
$toUser = (int)$rowUser['user_id'];

// บันทึกการแชร์
$sqlInsert = "
  INSERT INTO tbl_activity_share
    (activity_id, shared_by_user_id, shared_to_user_id)
  VALUES
    ($activityId, $fromUser, $toUser)
";
if (!mysqli_query($connection, $sqlInsert)) {
  echo json_encode(['success'=>false,'message'=>'เกิดข้อผิดพลาด: '.mysqli_error($connection)]);
  exit;
}

// ดึงรายการแชร์ทั้งหมดของ activity นี้ (ลำดับล่าสุดก่อน)
$sqlList = "
  SELECT s.share_date, u.user_name AS shared_to
    FROM tbl_activity_share s
    JOIN tbl_users u ON u.user_id = s.shared_to_user_id
    WHERE s.activity_id = $activityId
    ORDER BY s.share_date DESC
";
$resList = mysqli_query($connection, $sqlList);
$shares = [];
while ($r = mysqli_fetch_assoc($resList)) {
  $shares[] = [
    'shared_to'  => $r['shared_to'],
    'share_date' => $r['share_date'],
  ];
}

// ส่งกลับทั้ง message และ data
echo json_encode([
  'success' => true,
  'message' => "แชร์เรียบร้อยไปยัง $username",
  'data'    => $shares
]);
