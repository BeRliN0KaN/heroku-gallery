<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['delete'])) {
    $user_id = $_SESSION['user_id'];

    // ลบบัญชี
    $sql = "DELETE FROM tbl_users WHERE user_id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    // ทำลาย session แล้ว redirect
    session_destroy();
    echo "<script>alert('✅ ลบบัญชีเรียบร้อยแล้ว'); window.location.href='register.php';</script>";
    exit;
} else {
    header("Location: profile.php");
    exit;
}
