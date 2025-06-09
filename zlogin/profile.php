<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูล user
$sql = "SELECT * FROM tbl_users WHERE user_id = ?";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// ถ้าไม่มี user เจอ
if (!$user) {
    echo "❌ ไม่พบข้อมูลผู้ใช้";
    exit;
}
?>

<h2>ข้อมูลโปรไฟล์</h2>
<p><strong>ชื่อผู้ใช้:</strong> <?php echo htmlspecialchars($user['user_name']); ?></p>
<p><strong>อีเมล:</strong> <?php echo htmlspecialchars($user['user_email']); ?></p>
<p><strong>สถานะ:</strong> <?php echo htmlspecialchars($user['user_role']); ?></p>

<!-- ปุ่มลบบัญชี -->
<form method="post" action="delete_account.php" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบบัญชีนี้? ข้อมูลทั้งหมดจะหายไป!');">
    <button type="submit" name="delete" style="background-color: red; color: white;">ลบบัญชี</button>
</form>

<br><br>
<a href="index.php">🔙 กลับหน้าแรก</a> | 
<a href="logout.php">ออกจากระบบ</a>
