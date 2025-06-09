<?php  
/* Page Header and navigation */
include "includes_admin/header.php";
include "includes_admin/navigation.php";
include '../includes/db.php';
?>

<!-- โหลด Face API สำหรับตรวจสอบใบหน้าในฝั่งไคลเอนต์ -->
<script defer src="/webGallery/admin/js/face-api.min.js"></script>

<br><br>

<?php
if (isset($_POST['add_user'])) {
    // ตรวจสอบฟิลด์สำคัญ
    if (
        empty(trim($_POST['username'])) ||
        empty(trim($_POST['firstname'])) ||
        empty(trim($_POST['lastname'])) ||
        empty(trim($_POST['email'])) ||
        empty($_POST['password']) ||
        empty($_POST['confirm_password'])
    ) {
        echo "<script>alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');window.history.go(-1);</script>";
        exit;
    }

    // ตรวจสอบความยาวรหัสผ่าน
    if (strlen($_POST['password']) < 8) {
        echo "<script>alert('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร');window.history.go(-1);</script>";
        exit;
    }

    // ตรวจสอบการยืนยันรหัสผ่าน
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo "<script>alert('รหัสผ่านไม่ตรงกัน');window.history.go(-1);</script>";
        exit;
    }

    // ดึงค่า
    $user_firstname = trim($_POST['firstname']);
    $user_lastname  = trim($_POST['lastname']);
    $user_name      = trim($_POST['username']);
    $user_password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_email     = trim($_POST['email']);
    $user_role      = $_POST['user_role'];

    // อัปโหลดรูปใบหน้าเพื่อปลดล็อก
    if (!empty($_FILES['face_image']['name'])) {
        $face_path  = $_FILES['face_image']['name'];
        $face_ext   = pathinfo($face_path, PATHINFO_EXTENSION);
        $face_image = 'face_' . time() . '.' . $face_ext;
        $face_tmp   = $_FILES['face_image']['tmp_name'];
        if (!file_exists('./face_uploads')) mkdir('./face_uploads', 0755, true);
        move_uploaded_file($face_tmp, "./face_uploads/$face_image");
    } else {
        echo "<script>alert('กรุณาอัปโหลดรูปใบหน้า');window.history.go(-1);</script>";
        exit;
    }

    // อัปโหลดรูปโปรไฟล์
    if (!empty($_FILES['user_image']['name'])) {
        $path            = $_FILES['user_image']['name'];
        $ext             = pathinfo($path, PATHINFO_EXTENSION);
        $user_image      = time() . '.' . $ext;
        $user_image_temp = $_FILES['user_image']['tmp_name'];
        move_uploaded_file($user_image_temp, "./profile/$user_image");
    } else {
        $user_image = "default.jpg";
    }

    // ตรวจสอบชื่อผู้ใช้ซ้ำ
    $queryExist   = "SELECT EXISTS(SELECT 1 FROM tbl_users WHERE user_name='{$user_name}') AS user";
    $fetch_data   = mysqli_query($connection, $queryExist);
    $row          = mysqli_fetch_assoc($fetch_data);
    $user_exists  = $row['user'];

    if ($user_exists == 0) {
        // บันทึกข้อมูล (ต้องมีคอลัมน์ user_face_image)
        $query  = "INSERT INTO tbl_users(user_firstname,user_lastname,user_name,user_password,user_email,user_image,user_face_image,user_role) ";
        $query .= "VALUES('{$user_firstname}','{$user_lastname}','{$user_name}','{$user_password}','{$user_email}','{$user_image}','{$face_image}','{$user_role}')";
        mysqli_query($connection, $query) or die('Query Failed: ' . mysqli_error($connection));
        header('Location: index.php'); exit;
    } else {
        echo "<script>alert('Username นี้ถูกใช้งานแล้ว');window.history.go(-1);</script>";
        exit;
    }
}
?>

<form action="" method="post" enctype="multipart/form-data" style="max-width:500px;margin:auto;padding:2rem;background:#fff;border-radius:1rem;box-shadow:0 0 20px rgba(0,0,0,0.05);">
    <h2 class="text-center fw-bold mb-4" style="color:#002060;">Register</h2>

    <!-- User Image Upload -->
    <div class="mb-3">
        <label class="form-label fw-bold">User Image</label>
        <div>
            <label for="user_image" style="cursor:pointer;color:#007bff;">เลือกไฟล์รูปภาพ <i class="bi bi-file-image"></i></label>
            <input type="file" name="user_image" id="user_image" accept="image/*" hidden>
        </div>
        <div class="mt-2"><img id="preview-image" src="#" alt="Preview" class="img-fluid mx-auto" style="max-width:150px;display:none;border-radius:.5rem;"></div>
    </div>

    <!-- Face Image Upload -->
    <div class="mb-3">
        <label class="form-label fw-bold">Face Image (ปลดล็อกแกลเลอรี่)</label><span class="text-danger">*</span>
        <div>
            <label for="face_image" style="cursor:pointer;color:#007bff;">เลือกไฟล์รูปใบหน้า <i class="bi bi-file-image"></i></label>
            <input type="file" name="face_image" id="face_image" accept="image/*" required hidden>
        </div>
        <div class="mt-2"><img id="face_preview" src="#" alt="Face Preview" class="img-fluid mx-auto" style="max-width:150px;display:none;border-radius:.5rem;"></div>
    </div>

    <!-- Hidden Role -->
    <input type="hidden" name="user_role" value="subscriber">

    <!-- Username -->
    <div class="mb-3">
        <label class="form-label fw-bold">Username</label><span class="text-danger">*</span>
        <input type="text" class="form-control" name="username" placeholder="Username" required>
    </div>
    <!-- Firstname -->
    <div class="mb-3">
        <label class="form-label fw-bold">Firstname</label><span class="text-danger">*</span>
        <input type="text" class="form-control" name="firstname" placeholder="Firstname" required>
    </div>
    <!-- Lastname -->
    <div class="mb-3">
        <label class="form-label fw-bold">Lastname</label><span class="text-danger">*</span>
        <input type="text" class="form-control" name="lastname" placeholder="Lastname" required>
    </div>
    <!-- Email -->
    <div class="mb-3">
        <label class="form-label fw-bold">Email</label><span class="text-danger">*</span>
        <input type="email" class="form-control" name="email" placeholder="Email" required>
    </div>
    <!-- Password -->
    <div class="mb-3">
        <label class="form-label fw-bold">Password</label><span class="text-danger">*</span>
        <input type="password" class="form-control" name="password" placeholder="Password 8 Characters or Longer" required minlength="8">
    </div>
    <!-- Confirm Password -->
    <div class="mb-3">
        <label class="form-label fw-bold">Confirm Password</label><span class="text-danger">*</span>
        <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required minlength="8">
    </div>

    <!-- Submit -->
    <div class="d-grid mt-4"><button type="submit" name="add_user" class="btn btn-primary">Add User</button></div>
</form>

<script>
// User image preview
document.getElementById('user_image').addEventListener('change', e => {
    const img = document.getElementById('preview-image');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = ev => { img.src = ev.target.result; img.style.display = 'block'; };
        reader.readAsDataURL(file);
    }
});

// Face image preview + validation
document.getElementById('face_image').addEventListener('change', async e => {
    const file = e.target.files[0];
    const img = document.getElementById('face_preview');
    if (file) {
        await faceapi.nets.ssdMobilenetv1.loadFromUri('/webGallery/admin/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/webGallery/admin/models');
        const bufferImg = await faceapi.bufferToImage(file);
        const detections = await faceapi.detectAllFaces(bufferImg);
        if (detections.length !== 1) {
            alert('กรุณาอัปโหลดรูปที่มีใบหน้าเดียวและชัดเจน');
            e.target.value = '';
            img.style.display = 'none';
        } else {
            const reader = new FileReader();
            reader.onload = ev => { img.src = ev.target.result; img.style.display = 'block'; };
            reader.readAsDataURL(file);
        }
    }
});
</script>

<?php include "includes_admin/footer.php"; ?>
