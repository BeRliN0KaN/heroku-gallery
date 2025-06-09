<?php
if (isset($_POST['update_post'], $_GET['p_id'])) {
    $the_post_id = $_GET['p_id'];

    $post_title = base64_encode($_POST['title']);
    $post_title_thai = base64_encode($_POST['title_thai']);
    $post_title_china = base64_encode($_POST['title_china']);
    $post_subtitle = base64_encode($_POST['subtitle']);
    $post_subtitle_thai = base64_encode($_POST['subtitle_thai']);
    $post_subtitle_china = base64_encode($_POST['subtitle_china']);
    $post_link_url = $_POST['link_url'];
    $post_category_id = $_POST['post_category'];
    $post_status = $_POST['post_status'];

    $post_content = base64_encode($_POST['post_content']);
    $post_content_thai = base64_encode($_POST['post_content_thai']);
    $post_content_china = base64_encode($_POST['post_content_china']);
    $post_date = date("Y-m-d H:i:s"); //date('d-m-y');    

    $post_image_old = $_POST['post_image_old'];

    $post_image_temp = $_FILES['post_image']['tmp_name'];
    if (strlen($post_image_temp) > 0) {
        $path = $_FILES['post_image']['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $post_image = strtotime(date("Y-m-d H:i:s")) . '.' . $ext;

        unlink("../post/$post_image_old");
        move_uploaded_file($post_image_temp, "../post/$post_image");
    } else {
        $post_image = $post_image_old;
    }

    // Update a Post.
    $query = "UPDATE tbl_posts SET ";
    $query .= "post_category_id='$post_category_id', ";
    $query .= "post_title='$post_title', ";
    $query .= "post_title_thai='$post_title_thai', ";
    $query .= "post_title_china='$post_title_china', ";
    $query .= "post_subtitle='$post_subtitle', ";
    $query .= "post_subtitle_thai='$post_subtitle_thai', ";
    $query .= "post_subtitle_china='$post_subtitle_china', ";
    $query .= "post_link='$post_link_url', ";
    $query .= "post_date='$post_date', ";
    $query .= !empty($post_image) ? "post_image='$post_image', " : null;
    $query .= "post_content='$post_content', ";
    $query .= "post_content_thai='$post_content_thai', ";
    $query .= "post_content_china='$post_content_china', ";
    $query .= "post_status='$post_status' ";
    $query .= "WHERE post_id=$the_post_id";

    //   $query = mysqli_real_escape_string($connection, $query);
    $update_post_query = mysqli_query($connection, $query);
    if (!$update_post_query) {
        die("Query Failed: " . mysqli_error($connection));
    }
    header("Location: posts.php");
    //= echo "<p class='alert alert-success'>Post updated successfully. <a href='../post.php?p_id=$the_post_id'>View Post</a></p>";
}
?>


<?php
if (isset($_GET['p_id'])) {
    $the_post_id = $_GET['p_id'];
    $query = "SELECT * FROM tbl_posts WHERE post_id=$the_post_id";
    $fetch_data = mysqli_query($connection, $query);
    while ($Row = mysqli_fetch_assoc($fetch_data)) {
        $post_id = $Row['post_id'];
        $post_title = base64_decode($Row['post_title']);
        $post_title_thai = base64_decode($Row['post_title_thai']);
        $post_title_china = base64_decode($Row['post_title_china']);
        $post_subtitle = base64_decode($Row['post_subtitle']);
        $post_subtitle_thai = base64_decode($Row['post_subtitle_thai']);
        $post_subtitle_china = base64_decode($Row['post_subtitle_china']);
        $post_link_url = $Row['post_link'];
        $post_category_id = $Row['post_category_id'];
        $post_status = $Row['post_status'];
        $post_image_old = $Row['post_image'];
        $post_image = $Row['post_image'];
        $post_date = $Row['post_date'];
        $post_content = base64_decode($Row['post_content']);
        $post_content_thai = base64_decode($Row['post_content_thai']);
        $post_content_china = base64_decode($Row['post_content_china']);
?>
        <form action="" method="post" enctype="multipart/form-data" class="row g-3">
            <div class="form-group col-lg-12">
                <label for="post_image" class="d-block fw-bold ms-3">Post Image</label>
                <div>
                    <label for="post_image" class="upload-icon">
                        <span style="margin-left: 8px;">เลือกไฟล์รูปภาพ</span> <i class="bi bi-file-image" aria-hidden="true" style="font-size: 1.3rem;"></i>
                    </label>
                    <input type="file" name="post_image" id="post_image" style="display: none;" accept="image/*">
                    <input type="hidden" id="post_image_old" name="post_image_old" value="<?php echo $post_image_old; ?>">
                </div>
                <div id="preview-container">
                    <img id="preview-image" src='../post/<?php echo $post_image ? $post_image : '#'; ?>' alt="Preview Image" class="img-post" style="display: <?php echo $post_image ? 'block' : 'none'; ?>;">
                </div>
            </div>

            <script>
                document.getElementById('post_image').addEventListener('change', function(event) {
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
            <div class="form-group col-lg-4">
                <label class="ms-3 fw-bold" for="link">Link Url</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_link_url ?>" name="link_url">
            </div>
            <div class="form-group col-lg-4 ">
                <label class="ms-3 fw-bold " for="post_category">Post Category ID</label>
                <select class="form-control mt-2" name="post_category" id="post_category">
                    <?php
                    $query = "SELECT * FROM tbl_categories";
                    $fetch_data = mysqli_query($connection, $query);
                    while ($Row = mysqli_fetch_assoc($fetch_data)) {
                        $cat_id = $Row["cat_id"];
                        $cat_title = $Row["cat_title"];
                        $cat_title_thai = $Row["cat_title_thai"];
                        $selected = ($cat_id == $post_category_id) ? 'selected' : '';
                        if (isset($cat_title)) {
                            echo "<option value='" . $cat_id . "' " . $selected . ">" . $cat_title . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-lg-4">
                <label class="ms-3 fw-bold" for="post_status">Post Status</label>
                <select class="form-control mt-2" name="post_status" id="post_category">
                    <option value='<?php echo $post_status; ?>'><?php echo $post_status; ?></option>
                    <?php if ($post_status === "Published") { ?>
                        <option value='Draft'>Draft</option>
                    <?php } else { ?>
                        <option value='Published'>Published</option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="title">Post Title</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_title; ?>" name="title">
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="subtitle">Post subtitle</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_subtitle; ?>" name="subtitle">
            </div>
            <div class="form-group col-lg-12">
                <label id="my-ckeditor" class="ms-3 fw-bold mb-2" for="post_content">Post Content</label>
                <textarea id="editor" name="post_content" class="form-control mt-2">
                    <?php echo $post_content; ?>
                </textarea>
                <script>
                    CKEDITOR.dtd.$removeEmpty['i'] = false;
                    CKEDITOR.dtd.$removeEmpty['span'] = false;
                    CKEDITOR.replace('editor');
                    CKEDITOR.config.width = "100%";
                    CKEDITOR.config.height = "300px";
                </script>
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="title">[ภาษาไทย] Post Title</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_title_thai; ?>" name="title_thai">
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="subtitle">[ภาษาไทย] Post subtitle</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_subtitle_thai; ?>" name="subtitle_thai">
            </div>
            <div class="form-group col-lg-12">
                <label id="my-ckeditor" class="ms-3 fw-bold mb-2" for="post_content_thai">[ภาษาไทย] Post Content</label>
                <textarea id="editor2" name="post_content_thai" class="form-control mt-2">
                    <?php echo $post_content_thai; ?>
                </textarea>
                <script>
                    CKEDITOR.dtd.$removeEmpty['i'] = false;
                    CKEDITOR.dtd.$removeEmpty['span'] = false;
                    CKEDITOR.replace('editor2');
                </script>
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="title">[ภาษาจีน] Post Title</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_title_china; ?>" name="title_china">
            </div>
            <div class="form-group  col-lg-6">
                <label class="ms-3 fw-bold" for="subtitle">[ภาษาจีน] Post subtitle</label>
                <input type="text" class="form-control mt-2" value="<?php echo $post_subtitle_china; ?>" name="subtitle_china">
            </div>
            <div class="form-group col-lg-12">
                <label id="my-ckeditor" class="ms-3 fw-bold mb-2" for="post_content_caina">[ภาษาจีน] Post Content</label>
                <textarea id="editor3" name="post_content_china" class="form-control mt-2">
                         <?php echo $post_content_china; ?>
                </textarea>
                <script>
                    CKEDITOR.dtd.$removeEmpty['i'] = false;
                    CKEDITOR.dtd.$removeEmpty['span'] = false;
                    CKEDITOR.replace('editor3');
                </script>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" name="update_post" value="Update">
            </div>
        </form>
<?php
    }
}
?>