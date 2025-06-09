<?php
if (isset($_POST['add_user'])) {
    $user_firstname = $_POST['firstname'];
    $user_lastname = $_POST['lastname'];
    $user_name = $_POST['username'];
    $user_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_email = $_POST['email'];
    $user_role = $_POST['user_role'];

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพหรือไม่
    if (!empty($_FILES['user_image']['name'])) {
        $path = $_FILES['user_image']['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $user_image = strtotime(date("Y-m-d H:i:s")) . '.' . $ext;
        $user_image_temp = $_FILES['user_image']['tmp_name'];

        move_uploaded_file($user_image_temp, "../profile/$user_image");
    } else {
        // ถ้าไม่มีการอัปโหลดรูป ให้ใช้ค่าเริ่มต้น
        $user_image = "default.jpg";
    }
    // Check exist user.
    $user = 1;
    $queryExist = "SELECT EXISTS(SELECT * FROM tbl_users WHERE user_name = '$user_name') as user";
    $fetch_data = mysqli_query($connection, $queryExist);
    while ($Row = mysqli_fetch_assoc($fetch_data)) {
        $user = $Row['user'];
    }

    if ($user == 0) {

        // Add new user.
        $query = "INSERT INTO tbl_users(user_firstname, user_lastname, user_name, user_password, user_email,user_image,user_role) ";
        $query .= "VALUES('{$user_firstname}', '{$user_lastname}', '{$user_name}', '{$user_password}', '{$user_email}','{$user_image}','{$user_role}')";

        $create_user_query = mysqli_query($connection, $query);
        if (!$create_user_query) {
            die("Query Failed: " . mysqli_error($connection));
        }
        header("Location: ../backend/users.php");
        echo "User Created " . "<a href='users.php'>View Users</a>";
    } else {
        echo "<script>alert('This user already in the system!');window.history.go(-1);</script>";
    }
}



?>

<form action="" method="post" enctype="multipart/form-data">
    <div class="py-2">
        <h5 class="card-title text-center pb-0 fs-4"><strong>Add User</strong> </h5>
    </div>
    <div class="form-group">
        <label for="user_image" class="d-block fw-bold ms-3">User Image</label>
        <div>
            <label for="user_image" class="upload-icon">
                <span style="margin-left: 8px ;">เลือกไฟล์รูปภาพ</span> <i class="bi bi-file-image" aria-hidden="true" style="font-size: 1.3rem;"></i>
            </label>
        </div>
        <input type="file" name="user_image" id="user_image" style="display: none;" accept="image/*">

        <div id="preview-container">
            <img id="preview-image" src="#" alt="Preview Image" class="img-post">
        </div>
    </div>

    <script>
        document.getElementById('user_image').addEventListener('change', function(event) {
            const previewImage = document.getElementById('preview-image');
            const file = event.target.files[0]; // ดึงไฟล์ที่เลือก
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result; // แสดงรูปใน img
                    previewImage.style.display = 'block'; // ทำให้ img ปรากฏ
                };
                reader.readAsDataURL(file); // อ่านไฟล์เป็น Data URL
            }
        });
    </script>
    <div class="form-group mt-3">
        <label for="username" class="ms-3 fw-bold">Role</label>
        <select  id="role" class="form-control mt-2" name="user_role">
            <option value="subscribe">Select Role</option>
            <option value="admin">Admin</option>
            <option value="subscriber">Subscriber</option>
        </select>
    </div>
    <!-- <div class="form-group mt-3" style="display: none;">
        <label for="role" class="ms-3 fw-bold">Role</label>
        <select id="role" class="form-control mt-2" name="user_role">
            <option value="subscriber" selected>Subscriber</option>
            <option value="admin">Admin</option>
        </select>
    </div> -->


    <div class="form-group mt-3">
        <label for="username" class="ms-3 fw-bold">Username</label>
        <input type="text" class="form-control mt-2" name="username">
    </div>

    <div class="form-group mt-3">
        <label for="firstname" class="ms-3 fw-bold">Firstname</label>
        <input type="text" class="form-control  mt-2" name="firstname">
    </div>

    <div class="form-group mt-3">
        <label for="lastname" class="ms-3 fw-bold">Lastname</label>
        <input type="text" class="form-control mt-2" name="lastname">
    </div>

    <div class="form-group mt-3">
        <label for="email" class="ms-3 fw-bold">Email</label>
        <input type="email" class="form-control mt-2" name="email">
    </div>

    <div class="form-group mt-3">
        <label for="password" class="ms-3 fw-bold">Password</label>
        <input type="password" class="form-control mt-2" name="password">
    </div>

    <div class="form-group mt-3">
        <input type="submit" class="btn btn-primary" name="add_user" value="Add User">
    </div>
</form>