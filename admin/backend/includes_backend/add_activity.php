<!-- add_activity.php -->
<script src="../ckeditor/ckeditor.js"></script>
<?php
include "../../includes/db.php";

// เริ่ม session ถ้ายังไม่ถูกเริ่ม
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['create_post'])) {
    // 1) อ่านค่าสถานะกิจกรรม
    $activity_status = $_POST['activity_status'];

    // 2) เตรียมตัวแปรสำหรับรหัสผ่าน (hashed) และรหัสดิบ (plaintext)
    $hashed_password     = null;
    $plain_password      = null;

    // 3) ถ้าเลือก "Lock" ต้องกรอกรหัสผ่านก่อน
    if ($activity_status === 'Lock') {
        if (empty($_POST['activity_password'])) {
            echo "<script>
                    alert('เนื่องจากสถานะเป็น Lock จึงต้องตั้งรหัสผ่านก่อน');
                    window.history.back();
                  </script>";
            exit; // หยุด ไม่ให้ไปถึงการสร้างข้อมูล
        }
        // มีรหัสดิบ → เก็บในตัวแปร $raw_password
        $raw_password       = trim($_POST['activity_password']);
        // 3.1 เข้ารหัส Hash
        $hashed_password    = password_hash($raw_password, PASSWORD_DEFAULT);
        // 3.2 เก็บรหัสดิบแบบปลอดภัย (escape) ลง $plain_password
        $plain_password     = mysqli_real_escape_string($connection, $raw_password);
    }
    // ถ้าเป็น "Published" → ปล่อยให้ $hashed_password = null, $plain_password = null

    // 4) เก็บข้อมูลอื่น ๆ ที่เหลือ (base64_encode ตามเดิม)
    $activity_title           = base64_encode($_POST['title']);
    $activity_title_thai      = base64_encode($_POST['title_thai']);
    $activity_title_china     = base64_encode($_POST['title_china']);
    $activity_subtitle        = base64_encode($_POST['subtitle']);
    $activity_subtitle_thai   = base64_encode($_POST['subtitle_thai']);
    $activity_subtitle_china  = base64_encode($_POST['subtitle_china']);
    $activity_link_url        = mysqli_real_escape_string($connection, $_POST['link_url']);

    date_default_timezone_set("Asia/Bangkok");

    // 5) จัดการอัปโหลดรูป Activity Image หลัก
    $path                   = $_FILES['activity_image']['name'];
    $ext                    = pathinfo($path, PATHINFO_EXTENSION);
    $activity_image         = strtotime(date("Y-m-d H:i:s")) . '.' . $ext;
    $activity_image_temp    = $_FILES['activity_image']['tmp_name'];
    move_uploaded_file($activity_image_temp, "../activity/$activity_image");

    // 6) เข้ารหัส Content ต่าง ๆ
    $activity_content       = base64_encode($_POST['activity_content']);
    $activity_content_thai  = base64_encode($_POST['activity_content_thai']);
    $activity_content_china = base64_encode($_POST['activity_content_china']);
    $activity_date          = date("Y-m-d H:i:s");
    $user_id                = $_SESSION['user_id'];

    // 7) เตรียมค่า SQL สำหรับ activity_password (Hash) และ activity_password_plain (plaintext)
    if ($hashed_password !== null) {
        $password_hash_sql_value   = "'" . mysqli_real_escape_string($connection, $hashed_password) . "'";
        $password_plain_sql_value  = "'" . $plain_password . "'";
    } else {
        $password_hash_sql_value   = "NULL";
        $password_plain_sql_value  = "NULL";
    }

    // 8) สร้างคำสั่ง INSERT (เพิ่มคอลัมน์ activity_password_plain)
    $query  = "
        INSERT INTO tbl_activity (activity_title,activity_title_thai,activity_title_china,activity_date,activity_image,activity_content,activity_content_thai,activity_content_china,activity_status,activity_subtitle,activity_subtitle_thai,activity_subtitle_china,activity_link,activity_password,activity_password_plain,user_id) 
        VALUES ('{$activity_title}','{$activity_title_thai}','{$activity_title_china}','{$activity_date}','{$activity_image}','{$activity_content}','{$activity_content_thai}','{$activity_content_china}','{$activity_status}','{$activity_subtitle}','{$activity_subtitle_thai}','{$activity_subtitle_china}','{$activity_link_url}',{$password_hash_sql_value},{$password_plain_sql_value},'{$user_id}')";
    
        $create_post_query = mysqli_query($connection, $query);

    if (!$create_post_query) {
        die("Query Failed: " . mysqli_error($connection));
    }

    // 9) ดึง activity_id ที่เพิ่งแทรกเข้าไป
    $the_activity_id = mysqli_insert_id($connection);

    // 10) ถ้ามีการอัปโหลด gallery_images ให้บันทึกทีละรูป
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $gallery_images      = $_FILES['gallery_images'];
        $total_gallery_files = count($gallery_images['name']);

        for ($i = 0; $i < $total_gallery_files; $i++) {
            $gallery_image_name = time() . '_' . basename($gallery_images['name'][$i]);
            $gallery_image_tmp  = $gallery_images['tmp_name'][$i];
            move_uploaded_file($gallery_image_tmp, "../activity_gallery/$gallery_image_name");

            $query_gallery = "
                INSERT INTO tbl_activity_gallery (
                    activity_id,
                    image_name,
                    user_id
                ) VALUES (
                    '$the_activity_id',
                    '$gallery_image_name',
                    '$user_id'
                )
            ";
            mysqli_query($connection, $query_gallery);
        }
    }

    // 11) Redirect ไปหน้า activity.php เมื่อเสร็จสิ้น
    header("Location: activity.php");
    exit;
}
?>

<form action="" method="post" enctype="multipart/form-data" class="row g-3">
    <!-- Activity Image -->
    <div class="form-group col-lg-12">
        <label for="activity_image" class="d-block ms-3 fw-bold">Activity Image (รูปหลัก)</label>
        <div>
            <label for="activity_image" class="upload-icon">
                <span style="margin-left: 8px;">เลือกไฟล์รูปภาพ</span>
                <i class="bi bi-file-image" style="font-size: 1.3rem;"></i>
            </label>
            <input
                type="file"
                name="activity_image"
                id="activity_image"
                style="display: none;"
                accept="image/*"
            >
        </div>
        <div id="preview-container">
            <img id="preview-image" src="#" alt="Preview Image" class="img-post" style="display:none;">
        </div>
    </div>

    <script>
        document.getElementById('activity_image').addEventListener('change', function(event) {
            const previewImage = document.getElementById('preview-image');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <!-- Link URL -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="link_url">Link Url</label>
        <input type="text" class="form-control mt-2" name="link_url">
    </div>

    <!-- Activity Status -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="activity_status">Activity Status</label>
        <select
            class="form-control mt-2"
            name="activity_status"
            id="activity_status"
        >
            <option value="Published">Published</option>
            <option value="Lock">Lock</option>
        </select>
    </div>

    <!-- Activity Password -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="activity_password">Activity Password</label>
        <input
            type="password"
            class="form-control mt-2"
            name="activity_password"
            id="activity_password"
            placeholder="กรอกรหัสผ่าน (เฉพาะกรณี Lock)"
        >
    </div>

    <!-- Activity Title -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="title">Activity Title</label>
        <input type="text" class="form-control mt-2" name="title">
    </div>

    <!-- Activity Subtitle -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="subtitle">Activity Subtitle</label>
        <input type="text" class="form-control mt-2" name="subtitle">
    </div>

    <!-- Activity Content (English) -->
    <div class="form-group col-lg-12">
        <label class="fw-bold ms-3 mb-3" for="activity_content">Activity Content</label>
        <textarea id="editor" name="activity_content" class="form-control mt-2">This is some sample content.</textarea>
        <script>
            CKEDITOR.replace('editor');
            CKEDITOR.config.width = "100%";
            CKEDITOR.config.height = "300px";
        </script>
    </div>

    <!-- Activity Title (Thai) -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="title_thai">[ภาษาไทย] Activity Title</label>
        <input type="text" class="form-control mt-2" name="title_thai">
    </div>

    <!-- Activity Subtitle (Thai) -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="subtitle_thai">[ภาษาไทย] Activity Subtitle</label>
        <input type="text" class="form-control mt-2" name="subtitle_thai">
    </div>

    <!-- Activity Content (Thai) -->
    <div class="form-group col-lg-12">
        <label class="fw-bold ms-3 mb-3" for="activity_content_thai">[ภาษาไทย] Activity Content</label>
        <textarea id="editor2" name="activity_content_thai" class="form-control mt-2">นี่คือเนื้อหาตัวอย่างบางส่วน.</textarea>
        <script>
            CKEDITOR.replace('editor2');
        </script>
    </div>

    <!-- Activity Title (Chinese) -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="title_china">[ภาษาจีน] Activity Title</label>
        <input type="text" class="form-control mt-2" name="title_china">
    </div>

    <!-- Activity Subtitle (Chinese) -->
    <div class="form-group col-lg-6">
        <label class="fw-bold ms-3" for="subtitle_china">[ภาษาจีน] Activity Subtitle</label>
        <input type="text" class="form-control mt-2" name="subtitle_china">
    </div>

    <!-- Activity Content (Chinese) -->
    <div class="form-group col-lg-12">
        <label class="fw-bold ms-3 mb-3" for="activity_content_china">[ภาษาจีน] Activity Content</label>
        <textarea id="editor3" name="activity_content_china" class="form-control mt-2">这是一些示例内容。</textarea>
        <script>
            CKEDITOR.replace('editor3');
        </script>
    </div>

    <!-- Styles สำหรับ Gallery Preview -->
    <style>
        #gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .preview-box {
            width: 160px;
            height: 200px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }
        .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            line-height: 24px;
            text-align: center;
        }
    </style>

    <!-- Activity Gallery Images -->
    <div class="form-group col-lg-12">
        <label for="gallery_images" class="d-block ms-3 fw-bold">Activity Gallery Images (หลายรูป)</label>
        <input
            type="file"
            name="gallery_images[]"
            id="gallery_images"
            multiple
            accept="image/*"
            class="form-control"
        >
    </div>
    <div id="gallery-preview" class="row mt-3"></div>

    <script>
        // โค้ด JavaScript สำหรับ preview รูปหลายรูป
        let galleryFiles = [];
        document.getElementById('gallery_images').addEventListener('change', function(event) {
            const files = Array.from(event.target.files);
            galleryFiles = galleryFiles.concat(files);
            renderGalleryPreview();
        });
        function renderGalleryPreview() {
            const previewContainer = document.getElementById('gallery-preview');
            previewContainer.innerHTML = '';
            galleryFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('preview-box');

                    const img = document.createElement('img');
                    img.src = e.target.result;

                    const removeBtn = document.createElement('button');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.type = 'button';
                    removeBtn.classList.add('remove-btn');
                    removeBtn.onclick = function() {
                        galleryFiles.splice(index, 1);
                        renderGalleryPreview();
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    previewContainer.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }
        document.querySelector('form').addEventListener('submit', function(event) {
            const dataTransfer = new DataTransfer();
            galleryFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            document.getElementById('gallery_images').files = dataTransfer.files;
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

    <!-- ปุ่ม Publish -->
    <div class="form-group col-lg-12">
        <input type="submit" class="btn btn-primary" name="create_post" value="Publish">
    </div>
</form>
