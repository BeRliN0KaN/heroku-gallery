<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 🎉</h2>
<p>คุณเข้าสู่ระบบแล้ว!</p>

<a href="profile.php">
    <button style="padding: 10px 20px; margin: 5px;">ดูโปรไฟล์</button>
</a>

<a href="logout.php">ออกจากระบบ</a>
