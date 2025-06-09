<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // ตรวจสอบว่ามี user นี้แล้วหรือยัง
    $sql_check = "SELECT * FROM tbl_users WHERE user_name = ?";
    $stmt = mysqli_prepare($connection, $sql_check);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "❌ ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาเลือกชื่อใหม่";
    } else {
        $sql = "INSERT INTO tbl_users (user_name, user_password, user_email) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $email);
        mysqli_stmt_execute($stmt);

        echo "✅ สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ";
        header("refresh:2;url=login.php");
        exit;
    }
}
?>

<!-- ฟอร์มสมัครสมาชิก -->
<h2>สมัครสมาชิก</h2>
<form method="post">
    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required><br><br>
    <input type="email" name="email" placeholder="อีเมล" required><br><br>
    <input type="password" name="password" placeholder="รหัสผ่าน" required><br><br>
    <button type="submit">สมัครสมาชิก</button><br><br>
    <a href="login.php">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
</form>
