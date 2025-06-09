<?php
// profile.php

include "../backend/includes_backend/header.php";
include "../backend/includes_backend/navigation.php";
$messages = [];

// --- ดึงข้อมูลผู้ใช้ (GET) ---
$the_user_name = $_SESSION['username'];
$sql_user = "SELECT 
    user_firstname,
    user_lastname,
    user_name      AS user_username,
    user_email,
    user_image     AS user_image_old,
    user_face_image AS user_face_image_old,
    user_role
  FROM tbl_users
  WHERE user_name = '{$the_user_name}'";
$res_user = mysqli_query($connection, $sql_user) or die("Query Failed: " . mysqli_error($connection));
$row_user = mysqli_fetch_assoc($res_user);

// ตัวแปรเริ่มต้นสำหรับฟอร์ม
$user_firstname      = $row_user['user_firstname'];
$user_lastname       = $row_user['user_lastname'];
$user_username       = $row_user['user_username'];
$user_email          = $row_user['user_email'];
$user_image_old      = $row_user['user_image_old'];
$user_face_image_old = $row_user['user_face_image_old'];
$user_role           = $row_user['user_role'];

// --- ประมวลผล Update Profile ---
if (isset($_POST['update_profile'], $_SESSION['username'])) {
    $the_user_name    = $_SESSION['username'];
    $user_firstname   = $_POST['firstname'];
    $user_lastname    = $_POST['lastname'];
    $user_username    = $_POST['username'];
    $user_email       = $_POST['email'];
    $user_image_old   = $_POST['user_image_old'];
    $user_face_image_old = $_POST['user_face_image_old'];

    // Profile image upload
    if (!empty($_FILES['user_image']['tmp_name'])) {
        $ext        = pathinfo($_FILES['user_image']['name'], PATHINFO_EXTENSION);
        $user_image = time() . '.' . $ext;
        if ($user_image_old && file_exists("../profile/{$user_image_old}")) {
            unlink("../profile/{$user_image_old}");
        }
        move_uploaded_file($_FILES['user_image']['tmp_name'], "../profile/{$user_image}");
    } else {
        $user_image = $user_image_old;
    }

    // Face image upload
    if (!empty($_FILES['user_face_image']['name'])) {
        $face_ext         = pathinfo($_FILES['user_face_image']['name'], PATHINFO_EXTENSION);
        $user_face_image  = 'face_' . time() . '.' . $face_ext;
        if (!file_exists('../face_uploads')) mkdir('../face_uploads', 0755, true);
        if ($user_face_image_old && file_exists("../face_uploads/{$user_face_image_old}")) {
            unlink("../face_uploads/{$user_face_image_old}");
        }
        move_uploaded_file($_FILES['user_face_image']['tmp_name'], "../face_uploads/{$user_face_image}");
    } else {
        $user_face_image = $user_face_image_old;
    }

    // ถ้า username ไม่เปลี่ยน → อัปเดตเลย
    if ($the_user_name === $user_username) {
        $query = "UPDATE tbl_users SET
            user_firstname   = '{$user_firstname}',
            user_lastname    = '{$user_lastname}',
            user_email       = '{$user_email}',
            user_image       = '{$user_image}',
            user_face_image  = '{$user_face_image}'
          WHERE user_name = '{$the_user_name}'";
        mysqli_query($connection, $query) or die("Query Failed: " . mysqli_error($connection));
        $_SESSION['username']   = $user_username;
        $_SESSION['user_image'] = $user_image;
        $_SESSION['firstname']  = $user_firstname;
        $_SESSION['lastname']   = $user_lastname;
        $_SESSION['email']      = $user_email;
    } else {
        // ถ้าเปลี่ยน username → ตรวจซ้ำ
        $user_exists_q = "SELECT EXISTS(SELECT 1 FROM tbl_users WHERE user_name='{$user_username}') AS user";
        $res_exists    = mysqli_query($connection, $user_exists_q) or die("Query Failed: " . mysqli_error($connection));
        $row_exists    = mysqli_fetch_assoc($res_exists);
        if ($row_exists['user'] == 0) {
            $query = "UPDATE tbl_users SET
                user_firstname   = '{$user_firstname}',
                user_lastname    = '{$user_lastname}',
                user_name        = '{$user_username}',
                user_email       = '{$user_email}',
                user_image       = '{$user_image}',
                user_face_image  = '{$user_face_image}'
              WHERE user_name = '{$the_user_name}'";
            mysqli_query($connection, $query) or die("Query Failed: " . mysqli_error($connection));
            $_SESSION['username']   = $user_username;
            $_SESSION['user_image'] = $user_image;
            $_SESSION['firstname']  = $user_firstname;
            $_SESSION['lastname']   = $user_lastname;
            $_SESSION['email']      = $user_email;
        } else {
            $_SESSION['messages'][] = "<p class='alert alert-danger'>⚠️ Username นี้ถูกใช้งานแล้ว</p>";
        }
    }

    header("Location: ../backend/profile.php");
    exit();
}

// --- ประมวลผล Change Password ---
if (isset($_POST['change_password'], $_SESSION['username'])) {
    if (!empty($_POST['currentpassword']) && !empty($_POST['newpassword']) && !empty($_POST['renewpassword'])) {
        $cur_pass   = $_POST['currentpassword'];
        $new_pass   = $_POST['newpassword'];
        $renew_pass = $_POST['renewpassword'];

        // ตรวจสอบความยาวขั้นต่ำ
        if (strlen($new_pass) < 8) {
            $_SESSION['messages'][] = "<p class='alert alert-danger'>⚠️ รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร</p>";
        } else if ($new_pass !== $renew_pass) {
            $_SESSION['messages'][] = "<p class='alert alert-danger'>⚠️ Passwords do not match</p>";
        } else {
            // ยืนยันรหัสผ่านปัจจุบัน
            $sql = "SELECT user_password FROM tbl_users WHERE user_name = '{$the_user_name}'";
            $res = mysqli_query($connection, $sql) or die("Query Failed: " . mysqli_error($connection));
            $row = mysqli_fetch_assoc($res);

            if (password_verify($cur_pass, $row['user_password'])) {
                $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                mysqli_query(
                    $connection,
                    "UPDATE tbl_users SET user_password='{$hash}' WHERE user_name='{$the_user_name}'"
                ) or die("Query Failed: " . mysqli_error($connection));
                $_SESSION['messages'][] = "<p class='alert alert-success'>✅ Password changed successfully</p>";
            } else {
                $_SESSION['messages'][] = "<p class='alert alert-danger'>❌ Current password is incorrect</p>";
            }
        }
    } else {
        $_SESSION['messages'][] = "<p class='alert alert-danger'>⚠️ Please fill all fields</p>";
    }
    header("Location: ../backend/profile.php");
    exit();
}


// --- ประมวลผล Delete Account ---
if (isset($_POST['delete'], $_SESSION['username'])) {
    $the_user_name = $_SESSION['username'];
    $sql = "SELECT user_id, user_image, user_face_image FROM tbl_users WHERE user_name='{$the_user_name}'";
    $r   = mysqli_query($connection, $sql) or die("Query Failed: " . mysqli_error($connection));
    $u   = mysqli_fetch_assoc($r);

    mysqli_query($connection, "DELETE FROM tbl_activity WHERE user_id={$u['user_id']}")
        or die("Query Failed: " . mysqli_error($connection));
    if (mysqli_query($connection, "DELETE FROM tbl_users WHERE user_id={$u['user_id']}")) {
        if ($u['user_image'] !== 'default.jpg' && file_exists("../profile/{$u['user_image']}")) {
            unlink("../profile/{$u['user_image']}");
        }
        if ($u['user_face_image'] && file_exists("../face_uploads/{$u['user_face_image']}")) {
            unlink("../face_uploads/{$u['user_face_image']}");
        }
        session_destroy();
        header("Location: ../index.php");
        exit();
    } else {
        die("Query Failed: " . mysqli_error($connection));
    }
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Profile</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </nav>
    </div>

    <?php if (!empty($_SESSION['messages'])): ?>
        <?php foreach ($_SESSION['messages'] as $msg): ?>
            <?= $msg ?>
        <?php endforeach;
        unset($_SESSION['messages']); ?>
    <?php endif; ?>

    <section class="section profile">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <?php
                        $img_src = ($_SESSION['user_image'] === 'default.jpg')
                            ? "../images/img-icon/profile.webp"
                            : "../profile/{$_SESSION['user_image']}";
                        ?>
                        <img src="<?= $img_src ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;">
                        <h2><?= htmlspecialchars($_SESSION['username']) ?></h2>
                        <span><?= htmlspecialchars($user_role) ?></span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body pt-3">
                        <ul class="nav nav-tabs nav-tabs-bordered">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-2">

                            <!-- Overview -->
                            <div class="tab-pane fade show active profile-overview" id="profile-overview">
                                <h5 class="card-title">Profile Details</h5>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Username</div>
                                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user_username) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">First Name</div>
                                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user_firstname) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Last Name</div>
                                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user_lastname) ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-3 col-md-4 label">Email</div>
                                    <div class="col-lg-9 col-md-8"><?= htmlspecialchars($user_email) ?></div>
                                </div>
                                <form action="" method="post" class="text-end mt-3">
                                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure?');">
                                        Delete Account
                                    </button>
                                </form>
                            </div>

                            <!-- Edit Profile -->
                            <div class="tab-pane fade profile-edit pt-3" id="profile-edit">
                                <form action="" method="post" enctype="multipart/form-data">
                                    <!-- Profile Image -->
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div id="preview-container">
                                                <img id="preview-image" src="../profile/<?= htmlspecialchars($user_image_old) ?>"
                                                    class="img-post" style="max-width:150px;display:block;">
                                            </div>
                                            <input type="hidden" name="user_image_old" value="<?= htmlspecialchars($user_image_old) ?>">
                                            <label for="user_image" class="upload-icon mt-2" style="cursor:pointer;">
                                                เลือกไฟล์รูปภาพ <i class="bi bi-file-image"></i>
                                                <input type="file" name="user_image" id="user_image" accept="image/*" style="display:none;">
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Face Image -->
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Face Image</label>
                                        <div class="col-md-8 col-lg-9">
                                            <div id="preview-face-container">
                                                <?php if ($user_face_image_old && file_exists("../face_uploads/{$user_face_image_old}")): ?>
                                                    <img id="preview-face"
                                                        src="../face_uploads/<?= htmlspecialchars($user_face_image_old) ?>"
                                                        class="img-post" style="max-width:150px;display:block;">
                                                <?php endif; ?>
                                            </div>
                                            <input type="hidden" name="user_face_image_old"
                                                value="<?= htmlspecialchars($user_face_image_old) ?>">
                                            <label for="face_image" class="upload-icon mt-2" style="cursor:pointer;">
                                                เลือกไฟล์รูปใบหน้า <i class="bi bi-file-image"></i>
                                                <input type="file" name="user_face_image" id="face_image" accept="image/*" style="display:none;">
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Text Inputs -->
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Username</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="username" type="text" class="form-control" value="<?= htmlspecialchars($user_username) ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">First Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="firstname" type="text" class="form-control" value="<?= htmlspecialchars($user_firstname) ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Last Name</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="lastname" type="text" class="form-control" value="<?= htmlspecialchars($user_lastname) ?>">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Change Password -->
                            <div class="tab-pane fade pt-3" id="profile-change-password">
                                <form action="" method="post">
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="currentpassword" type="password" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="newpassword" type="password" class="form-control" minlength="8" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input name="renewpassword" type="password" class="form-control" minlength="8" required>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include "../backend/includes_backend/footer.php"; ?>

<script>
    // Preview profile image
    document.getElementById('user_image').addEventListener('change', e => {
        const file = e.target.files[0],
            img = document.getElementById('preview-image');
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => img.src = ev.target.result;
            reader.readAsDataURL(file);
        }
    });
    // Preview face image
    document.getElementById('face_image').addEventListener('change', e => {
        const file = e.target.files[0],
            container = document.getElementById('preview-face-container');
        let img = document.getElementById('preview-face');
        if (!img) {
            img = document.createElement('img');
            img.id = 'preview-face';
            img.className = 'img-post';
            img.style.maxWidth = '150px';
            img.style.display = 'block';
            container.appendChild(img);
        }
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => img.src = ev.target.result;
            reader.readAsDataURL(file);
        }
    });
</script>