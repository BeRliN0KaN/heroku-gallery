<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM tbl_users WHERE user_name = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['user_password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['user_role'] = $row['user_role'];

            header("Location: index.php");
            exit;
        } else {
            echo "❌ รหัสผ่านไม่ถูกต้อง";
        }
    } else {
        echo "❌ ไม่พบผู้ใช้นี้";
    }
}
?>

<!-- ฟอร์มล็อกอิน -->
<h2>เข้าสู่ระบบ</h2>
<form method="post">
    <input type="text" name="username" placeholder="ชื่อผู้ใช้" required><br><br>
    <input type="password" name="password" placeholder="รหัสผ่าน" required><br><br>
    <button type="submit">เข้าสู่ระบบ</button><br><br>
    <a href="register.php">ยังไม่มีบัญชี? สมัครสมาชิก</a>
</form>
