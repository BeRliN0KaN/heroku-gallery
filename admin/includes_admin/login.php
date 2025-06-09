<?php
include '../../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password_input = trim($_POST['password']);

    if (empty($username) || empty($password_input)) {
        echo "<script>alert('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');window.history.go(-1);</script>";
        exit;
    }

    $username = mysqli_real_escape_string($connection, $username);
    $query = "SELECT * FROM tbl_users WHERE user_name='$username'";
    $select_user_query = mysqli_query($connection, $query);

    if (!$select_user_query) {
        die("Query Failed: " . mysqli_error($connection));
    }

    if (mysqli_num_rows($select_user_query) === 0) {
        echo "<script>alert('ไม่พบชื่อผู้ใช้ในระบบ');window.history.go(-1);</script>";
        exit;
    }

    $Row = mysqli_fetch_assoc($select_user_query);

    $user_id = $Row['user_id'];
    $user_name = $Row['user_name'];
    $user_firstname = $Row['user_firstname'];
    $user_lastname = $Row['user_lastname'];
    $user_password = $Row['user_password'];
    $user_image = $Row['user_image'];
    $user_role = $Row['user_role'];

    if ($username === $user_name && password_verify($password_input, $user_password)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $user_name;
        $_SESSION['firstname'] = $user_firstname;
        $_SESSION['lastname'] = $user_lastname;
        $_SESSION['user_image'] = $user_image;
        $_SESSION['user_role'] = $user_role;

        header("Location: ../backend/index.php");
        exit;
    } else {
        echo "<script>alert('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');window.history.go(-1);</script>";
    }
}
?>
