<!-- edit_activity.php -->
<?php
include "../../includes/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_user_id = $_SESSION['user_id'];

// Fetch data to populate the edit form (รวมรหัสดิบจาก activity_password_plain)
if (isset($_GET['p_id'])) {
    $the_activity_id = intval($_GET['p_id']);
    $query = "SELECT * FROM tbl_activity WHERE activity_id={$the_activity_id}";
    $fetch = mysqli_query($connection, $query);
    if ($Row = mysqli_fetch_assoc($fetch)) {
        // ถอดรหัสเนื้อหาเก่า
        $activity_title             = base64_decode($Row['activity_title']);
        $activity_title_thai        = base64_decode($Row['activity_title_thai']);
        $activity_title_china       = base64_decode($Row['activity_title_china']);
        $activity_subtitle          = base64_decode($Row['activity_subtitle']);
        $activity_subtitle_thai     = base64_decode($Row['activity_subtitle_thai']);
        $activity_subtitle_china    = base64_decode($Row['activity_subtitle_china']);
        $activity_link_url          = $Row['activity_link'];
        $activity_status            = $Row['activity_status'];
        $activity_image_old         = $Row['activity_image'];
        $activity_content           = base64_decode($Row['activity_content']);
        $activity_content_thai      = base64_decode($Row['activity_content_thai']);
        $activity_content_china     = base64_decode($Row['activity_content_china']);
        // ดึง "รหัสดิบ" (plaintext) จากคอลัมน์ใหม่
        $existing_password          = $Row['activity_password_plain'];
    }
}

// Handle form submission for updating an activity
if (isset($_POST['update_post'], $_GET['p_id'])) {
    $the_activity_id      = intval($_GET['p_id']);
    $activity_status_new  = mysqli_real_escape_string($connection, $_POST['activity_status']);
    $raw_new_password     = isset($_POST['activity_password']) ? trim($_POST['activity_password']) : '';

    // ดึงสถานะเก่า + รหัสดิบเดิมจากคอลัมน์ activity_password_plain
    $query_status_old      = "SELECT activity_status, activity_password, activity_password_plain 
                              FROM tbl_activity 
                              WHERE activity_id={$the_activity_id}";
    $res_status_old        = mysqli_query($connection, $query_status_old);
    $row_status_old        = mysqli_fetch_assoc($res_status_old);
    $activity_status_old   = $row_status_old['activity_status'];
    $existing_hash_old     = $row_status_old['activity_password'];       // Hash เดิม (อาจใช้เก็บไว้หรือไม่)
    $existing_plain_old    = $row_status_old['activity_password_plain']; // Plaintext เดิม

    // เตรียมตัวแปรสำหรับแทรกลง SQL
    $password_hash_sql_value   = "NULL";
    $password_plain_sql_value  = "NULL";

    if ($activity_status_new === 'Lock') {
        // 1) ถ้าเปลี่ยนมาเป็น Lock
        if ($activity_status_old === 'Lock' && $raw_new_password === '') {
            // สถานะเดิมก็เป็น Lock แล้ว แล้วไม่ได้กรอกรหัสใหม่ → ใช้รหัสดิบเดิม
            if (!empty($existing_plain_old)) {
                $password_plain_sql_value = "'" . mysqli_real_escape_string($connection, $existing_plain_old) . "'";
                // ถ้าต้องเก็บ Hash ต่อเนื่อง ก็เก็บ $existing_hash_old (หรือจะ re‐hash ก็ได้ แต่ไม่บังคับ)
                $password_hash_sql_value  = "'" . mysqli_real_escape_string($connection, $existing_hash_old) . "'";
            } else {
                // ไม่มีรหัสดิบเดิม (ข้อมูลอาจพลาด) → บังคับให้กรอกใหม่
                echo "<script>
                        alert('สถานะเดิมเป็น Lock แต่ไม่มีรหัสเดิม กรุณากรอกรหัสใหม่');
                        window.history.back();
                      </script>";
                exit;
            }
        }
        elseif ($raw_new_password !== '') {
            // 2) กรอกรหัสใหม่ → ใช้รหัสใหม่ทั้ง Hash และ Plaintext
            $new_hash  = password_hash($raw_new_password, PASSWORD_DEFAULT);
            $password_hash_sql_value  = "'" . mysqli_real_escape_string($connection, $new_hash) . "'";
            $password_plain_sql_value = "'" . mysqli_real_escape_string($connection, $raw_new_password) . "'";
        }
        else {
            // 3) สถานะเดิมเป็น Published แล้วมาเปลี่ยนเป็น Lock แต่ไม่กรอกรหัส → ผิดเงื่อนไข
            echo "<script>
                    alert('เมื่อเปลี่ยนสถานะเป็น Lock จำเป็นต้องกรอกรหัสใหม่');
                    window.history.back();
                  </script>";
            exit;
        }
    } else {
        // ถ้าสถานะใหม่เป็น Published → ลบรหัสเดิมทิ้ง (ทั้ง Hash & Plaintext)
        $password_hash_sql_value  = "NULL";
        $password_plain_sql_value = "NULL";
    }

    // ถัดไป ทำการอัปเดตข้อมูลส่วนอื่น ๆ เหมือนเดิม
    $activity_title            = base64_encode($_POST['title']);
    $activity_title_thai       = base64_encode($_POST['title_thai']);
    $activity_title_china      = base64_encode($_POST['title_china']);
    $activity_subtitle         = base64_encode($_POST['subtitle']);
    $activity_subtitle_thai    = base64_encode($_POST['subtitle_thai']);
    $activity_subtitle_china   = base64_encode($_POST['subtitle_china']);
    $activity_link_url         = mysqli_real_escape_string($connection, $_POST['link_url']);
    $activity_date             = date("Y-m-d H:i:s");

    // Handle main image upload if a new file is provided
    $activity_image_old = $_POST['activity_image_old'];
    if (!empty($_FILES['activity_image']['tmp_name'])) {
        $path            = $_FILES['activity_image']['name'];
        $ext             = pathinfo($path, PATHINFO_EXTENSION);
        $activity_image  = strtotime($activity_date) . '.' . $ext;

        // ลบรูปเก่าแล้วอัปโหลดใหม่
        if (file_exists("../activity/{$activity_image_old}")) {
            unlink("../activity/{$activity_image_old}");
        }
        move_uploaded_file($_FILES['activity_image']['tmp_name'], "../activity/{$activity_image}");
    } else {
        $activity_image = $activity_image_old;
    }

    // Build UPDATE query (รวมทั้ง activity_password และ activity_password_plain)
    $query  = "UPDATE tbl_activity SET ";
    $query .= "activity_title='$activity_title', ";
    $query .= "activity_title_thai='$activity_title_thai', ";
    $query .= "activity_title_china='$activity_title_china', ";
    $query .= "activity_subtitle='$activity_subtitle', ";
    $query .= "activity_subtitle_thai='$activity_subtitle_thai', ";
    $query .= "activity_subtitle_china='$activity_subtitle_china', ";
    $query .= "activity_link='$activity_link_url', ";
    $query .= "activity_date='$activity_date', ";
    $query .= "activity_image='$activity_image', ";
    $query .= "activity_content='" . base64_encode($_POST['activity_content']) . "', ";
    $query .= "activity_content_thai='" . base64_encode($_POST['activity_content_thai']) . "', ";
    $query .= "activity_content_china='" . base64_encode($_POST['activity_content_china']) . "', ";
    $query .= "activity_status='$activity_status_new', ";
    $query .= "activity_password=$password_hash_sql_value, ";
    $query .= "activity_password_plain=$password_plain_sql_value ";

    // Apply role-based filter
    if ($_SESSION['user_role'] === 'admin') {
        $query .= "WHERE activity_id={$the_activity_id}";
    } else {
        $query .= "WHERE activity_id={$the_activity_id} AND user_id={$current_user_id}";
    }

    $update_result = mysqli_query($connection, $query);
    if (!$update_result) {
        die("Update Failed: " . mysqli_error($connection));
    }

    // Handle new gallery uploads (ตามเดิม)
    if (!empty($_FILES['gallery_images']['name'][0])) {
        foreach ($_FILES['gallery_images']['name'] as $key => $name) {
            $gallery_image_name = time() . '_' . basename($name);
            move_uploaded_file($_FILES['gallery_images']['tmp_name'][$key], "../activity_gallery/{$gallery_image_name}");
            $ins = "INSERT INTO tbl_activity_gallery (activity_id, image_name, user_id) ";
            $ins .= "VALUES ('{$the_activity_id}', '{$gallery_image_name}', '{$current_user_id}')";
            mysqli_query($connection, $ins);
        }
    }

    // Handle deletion of existing gallery images (ตามเดิม)
    if (!empty($_POST['images_to_delete'])) {
        $ids = array_map('intval', explode(',', $_POST['images_to_delete']));
        foreach ($ids as $id) {
            $sel = "SELECT image_name FROM tbl_activity_gallery WHERE gallery_id={$id}";
            $res = mysqli_query($connection, $sel);
            $row = mysqli_fetch_assoc($res);
            if (!empty($row['image_name']) && file_exists("../activity_gallery/{$row['image_name']}")) {
                unlink("../activity_gallery/{$row['image_name']}");
            }
            mysqli_query($connection, "DELETE FROM tbl_activity_gallery WHERE gallery_id={$id}");
        }
    }

    // Redirect กลับไปหน้า activity.php
    header("Location: activity.php");
    exit;
}
?>

<!-- The HTML form rendering -->
<?php if (isset($Row)): ?>
<form action="" method="post" enctype="multipart/form-data" class="row g-3">
    <!-- Main Image Upload & Preview -->
    <div class="form-group col-lg-12">
        <label for="activity_image" class="d-block ms-3 fw-bold">Activity Image</label>
        <label for="activity_image" class="upload-icon">
            <span style="margin-left: 8px;">เลือกไฟล์รูปภาพ</span>
            <i class="bi bi-file-image" style="font-size: 1.3rem;"></i>
        </label>
        <input type="file" name="activity_image" id="activity_image" style="display: none;" accept="image/*">
        <input type="hidden" name="activity_image_old" value="<?php echo $activity_image_old; ?>">
        <div id="preview-container">
            <img id="preview-image" src="../activity/<?php echo $activity_image_old; ?>" alt="Preview Image" class="img-post" style="display: block;">
        </div>
    </div>
    <script>
        document.getElementById('activity_image').addEventListener('change', function(event) {
            const previewImage = document.getElementById('preview-image');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <!-- Link and Status -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="link_url">Link Url</label>
        <input type="text" class="form-control mt-2" name="link_url" value="<?php echo htmlspecialchars($activity_link_url); ?>">
    </div>
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="activity_status">Activity Status</label>
        <select class="form-control mt-2" name="activity_status" id="activity_status">
            <option value="<?php echo $activity_status; ?>"><?php echo $activity_status; ?></option>
            <?php if ($activity_status === 'Published'): ?>
                <option value="Lock">Lock</option>
            <?php else: ?>
                <option value="Published">Published</option>
            <?php endif; ?>
        </select>
    </div>

    <!-- Activity Password (แสดง plaintext เมื่อสถานะเดิมเป็น Lock) -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="activity_password">Activity Password</label>
        <input
            type="text"
            class="form-control mt-2"
            name="activity_password"
            id="activity_password"
            placeholder="กรอกรหัสผ่าน (เฉพาะกรณี Lock)"
            value="<?php echo ($activity_status === 'Lock') 
                            ? htmlspecialchars($existing_password) 
                            : ''; ?>"
        >
    </div>

    <!-- Titles and Subtitles -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="title">Activity Title</label>
        <input type="text" class="form-control mt-2" name="title" value="<?php echo htmlspecialchars($activity_title); ?>">
    </div>
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="subtitle">Activity Subtitle</label>
        <input type="text" class="form-control mt-2" name="subtitle" value="<?php echo htmlspecialchars($activity_subtitle); ?>">
    </div>

    <!-- Content Editors -->
    <div class="form-group col-lg-12">
        <label class="fw-bold ms-3 mb-3" for="activity_content">Activity Content</label>
        <textarea id="editor" name="activity_content" class="form-control mt-2"><?php echo htmlspecialchars($activity_content); ?></textarea>
        <script>
            CKEDITOR.replace('editor', { width: '100%', height: '300px' });
        </script>
    </div>

    <!-- Thai & Chinese Fields -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3">[ภาษาไทย] Title</label>
        <input type="text" class="form-control mt-2" name="title_thai" value="<?php echo htmlspecialchars($activity_title_thai); ?>">
    </div>
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3">[ภาษาไทย] Subtitle</label>
        <input type="text" class="form-control mt-2" name="subtitle_thai" value="<?php echo htmlspecialchars($activity_subtitle_thai); ?>">
    </div>
    <div class="form-group col-lg-12">
        <label class="fw-bold	ms-3 mb-3">[ภาษาไทย] Content</label>
        <textarea id="editor2" name="activity_content_thai" class="form-control mt-2"><?php echo htmlspecialchars($activity_content_thai); ?></textarea>
        <script>
            CKEDITOR.replace('editor2');
        </script>
    </div>

    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3">[ภาษาจีน] Title</label>
        <input type="text" class="form-control mt-2" name="title_china" value="<?php echo htmlspecialchars($activity_title_china); ?>">
    </div>
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3">[ภาษาจีน] Subtitle</label>
        <input type="text" class="form-control	mt-2" name="subtitle_china" value="<?php echo htmlspecialchars($activity_subtitle_china); ?>">
    </div>
    <div class="form-group col-lg-12">
        <label class="fw-bold	ms-3 mb-3">[ภาษาจีน] Content</label>
        <textarea id="editor3" name="activity_content_china" class="form-control	mt-2"><?php echo htmlspecialchars($activity_content_china); ?></textarea>
        <script>
            CKEDITOR.replace('editor3');
        </script>
    </div>

    <!-- Existing Gallery & Upload New -->
    <style>
        #existing-gallery, #gallery-preview { display: flex; flex-wrap: wrap; gap: 16px; }
        .preview-box { width: 160px; height: 200px; background: #f8f9fa; border-radius: 12px; padding: 10px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .preview-box img { max-width: 100%; max-height: 100%; border-radius: 8px; object-fit: contain; }
        .remove-btn { position: absolute; top: 5px; right: 5px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 16px; line-height: 24px; text-align: center; }
    </style>

    <div class="form-group col-lg-12">
        <label class="fw-bold	ms-3">Current Gallery Images</label>
        <div id="existing-gallery">
            <?php
            $query_gallery_show = "SELECT * FROM tbl_activity_gallery WHERE activity_id = {$the_activity_id}";
            $gallery_result = mysqli_query($connection, $query_gallery_show);
            while ($gallery = mysqli_fetch_assoc($gallery_result)) {
            ?>
                <div class="preview-box existing-image" data-gallery-id="<?php echo $gallery['gallery_id']; ?>">
                    <img src="../activity_gallery/<?php echo $gallery['image_name']; ?>" alt="">
                    <button type="button" class="remove-btn" onclick="markImageForDeletion(this)">&times;</button>
                </div>
            <?php } ?>
        </div>
    </div>
    <input type="hidden" name="images_to_delete" id="images_to_delete" value="">

    <div class="form-group col-lg-12">
        <label for="gallery_images" class="fw-bold ms-3">Upload New Gallery Images</label>
        <input type="file" name="gallery_images[]" id="gallery_images" multiple accept="image/*" class="form-control">
    </div>
    <div id="gallery-preview" class="row mt-3"></div>

    <script>
        let galleryFiles = [], imagesToDelete = [];
        document.getElementById('gallery_images').addEventListener('change', function(e) {
            galleryFiles = galleryFiles.concat(Array.from(e.target.files));
            renderGalleryPreview();
        });
        function renderGalleryPreview() {
            const container = document.getElementById('gallery-preview');
            container.innerHTML = '';
            galleryFiles.forEach((file, i) => {
                const reader = new FileReader();
                reader.onload = e => {
                    const box = document.createElement('div');
                    box.className = 'preview-box';
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'remove-btn';
                    btn.innerHTML = '&times;';
                    btn.onclick = () => { galleryFiles.splice(i,1); renderGalleryPreview(); };
                    box.append(img, btn);
                    container.append(box);
                };
                reader.readAsDataURL(file);
            });
        }
        function markImageForDeletion(btn) {
            const box = btn.parentElement;
            const id = box.getAttribute('data-gallery-id');
            imagesToDelete.push(id);
            document.getElementById('images_to_delete').value = imagesToDelete.join(',');
            box.style.opacity = '0.5';
            btn.remove();
        }
        document.querySelector('form').addEventListener('submit', () => {
            const dt = new DataTransfer();
            galleryFiles.forEach(f => dt.items.add(f));
            document.getElementById('gallery_images').files = dt.files;
        });
    </script>

    <!-- ซ่อน/แสดงช่องรหัสผ่านตามสถานะอัตโนมัติ -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('activity_status');
            const passwordFieldWrapper = document.getElementById('activity_password').closest('.form-group');

            function togglePasswordField() {
                if (statusSelect.value === 'Lock') {
                    passwordFieldWrapper.style.display = 'block';
                } else {
                    passwordFieldWrapper.style.display = 'none';
                }
            }

            statusSelect.addEventListener('change', togglePasswordField);
            // เรียกครั้งแรกเพื่อซ่อนถ้าเลือก Published เริ่มต้น
            togglePasswordField();
        });
    </script>

    <!-- ปุ่ม Update -->
    <div class="form-group col-lg-12">
        <input type="submit" class="btn btn-primary" name="update_post" value="Update">
    </div>
</form>
<?php endif; ?>
