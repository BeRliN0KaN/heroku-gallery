<?php
if (isset($_POST['update_user'], $_GET['user_id'])) {
    $the_user_id = $_GET['user_id'];
    $user_role =$_POST['user_role'];
    $user_password_input = $_POST['password'];


    $sql = "SELECT user_password FROM tbl_users WHERE user_id = '$the_user_id'";
    $result = mysqli_query($connection, $sql);
    $row = mysqli_fetch_assoc($result);
    $storedHash = $row['user_password'];

    if (!empty($user_password_input)) {
        $password = password_hash($user_password_input, PASSWORD_DEFAULT);
    } else {
        $password = $storedHash;
    }

    $query = "UPDATE tbl_users SET ";
    $query .= "user_password='$password', ";
    $query .= "user_role='$user_role' ";
    $query .= " WHERE user_id='$the_user_id'";

    $update_user_query = mysqli_query($connection, $query);

    if (!$update_user_query) {
        die("Query Failed: " . mysqli_error($connection));
    }
    header("Location: ../backend/users.php");
    exit();
}
?>


<?php
if (isset($_GET['user_id'])) {
    $the_user_id = $_GET['user_id'];
    $query = "SELECT * FROM tbl_users WHERE user_id=$the_user_id";
    $fetch_data = mysqli_query($connection, $query);
    while ($Row = mysqli_fetch_assoc($fetch_data)) {
        $user_id = $Row['user_id'];
        $firstname = $Row['user_firstname'];
        $lastname = $Row['user_lastname'];
        $username = $Row['user_name'];
        $password = $Row['user_password'];
        $email = $Row['user_email'];
        $user_image = $Row['user_image'];
        $user_role = $Row['user_role'];

?>

        <form action="" method="post">
            <div class="py-2">
                <h5 class="card-title text-center pb-0 fs-4"><strong>Edit User</strong> </h5>
            </div>
            <div class="form-group">
                <label for="user_image" class="d-block fw-bold ms-3">User Image</label>


                <div id="preview-container">
                    <?php
                    if ($user_image == "default.jpg") {
                        // แสดงรูปภาพจากตำแหน่งที่กำหนดเมื่อไม่มีการตั้งค่าโปรไฟล์
                        echo "<img id='preview-image' src='../images/img-icon/profile.webp' alt='Preview Image' class='img-post' style='display:block; height:auto;'>";
                    } else {
                        // แสดงรูปภาพโปรไฟล์เมื่อมีการตั้งค่า
                        echo "<img id='preview-image' src='../profile/{$user_image}' alt='Preview Image' class='img-post' style='display:block; height:auto;'>";
                    }
                    ?>
                </div>


            </div>
            <div class="form-group mt-3">
                <label for="username" class="ms-3 fw-bold">Role</label>
                <select name="user_role" id="role" class="form-control mt-2" >
                    <option value="admin" <?php if ($user_role == "admin") echo "selected "; ?>>Admin</option>
                    <option value="subscriber" <?php if ($user_role == "subscriber") echo "selected "; ?>>Subscriber</option>
                </select>
            </div>


            <div class="form-group mt-3">
                <label for="username" class="ms-3 fw-bold">Username</label>
                <input type="text" class="form-control mt-2" name="username" value='<?php echo $username; ?>' readonly>
            </div>

            <div class="form-group">
                <label for="firstname" class="ms-3 fw-bold">Firstname</label>
                <input type="text" class="form-control mt-2" name="firstname" value='<?php echo $firstname; ?>' readonly>
            </div>

            <div class="form-group mt-3">
                <label for="lastname" class="ms-3 fw-bold">Lastname</label>
                <input type="text" class="form-control mt-2" name="lastname" value='<?php echo $lastname; ?>' readonly>
            </div>

            <div class="form-group mt-3">
                <label for="email" class="ms-3 fw-bold">Email</label>
                <input type="email" class="form-control mt-2" name="email" value='<?php echo $email; ?>' readonly>
            </div>

            <div class="form-group mt-3">
                <label for="password" class="ms-3 fw-bold">Password</label>
                <input type="password" class="form-control mt-2" name="password" placeholder="Enter new password">
            </div>

            <div class="form-group mt-3">
                <input type="submit" class="btn btn-primary" name="update_user" value="Update User">
            </div>
        </form>
<?php }
}
?>