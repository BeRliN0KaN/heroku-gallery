<?php
include "includes_backend/header.php";
include "includes_backend/navigation.php";

// Add new Category.
$error_message = "";
if (isset($_POST["submit"])) {
    $cat_title = $_POST['cat_title'];
    $cat_title_thai = $_POST['cat_title_thai'];
    $cat_title_china = $_POST['cat_title_china'];
    $cat_page = $_POST['cat_page'];
    if (!empty($cat_title) || $cat_title != "") {
        $query = "INSERT INTO tbl_categories (cat_title,cat_title_thai,cat_page,cat_title_china) VALUES('$cat_title','$cat_title_thai','$cat_page ','$cat_title_china'); ";
        $create_query = mysqli_query($connection, $query);
        if (!$create_query) {
            die("Query Failed: " . mysqli_error($connection));
        }
    } else {
        $error_message = "Category field is required.";
    }
}

// Delete Category.
if (isset($_GET["delete"])) {
    $cat_id = $_GET['delete'];
    // Check exist user.
    $exist = -1;
    $queryExist = "SELECT EXISTS(SELECT * FROM tbl_posts WHERE post_category_id = $cat_id) as exist";
    $fetch_data = mysqli_query($connection, $queryExist);
    while ($Row = mysqli_fetch_assoc($fetch_data)) {
        $exist = $Row['exist'];
    }

    if ($exist == 0) {
        $query = "DELETE FROM tbl_categories WHERE cat_id=$cat_id";
        $delete_query = mysqli_query($connection, $query);
        header("Location: categories.php");
        if (!$delete_query) {
            die("Query Failed: " . mysqli_error($connection));
        }
    } else if ($exist == 1) {
        echo "<script>alert('Found data in the system!Can not delete');</script>";
    }
}
$suscess_delete ="";
if (isset($_POST["delete"])) {
    if (isset($_POST["checkBoxArray"])) {
        foreach ($_POST["checkBoxArray"] as $checkBoxValue) {
            $query = "DELETE FROM tbl_categories WHERE cat_id = $checkBoxValue";
            $update_user = mysqli_query($connection, $query);
            if (!$update_user) {
                die("Query Failed: " . mysqli_error($connection));
            }
        }
        $suscess_delete ="<p class='alert alert-success'>Selected categories deleted successfully.</p>";
    }
}

$edit_mode = isset($_GET["edit"]);
// Update Category.
if (isset($_GET["edit"])) { // ตรวจสอบว่าเป็นโหมดแก้ไข
    $cat_id = $_GET['edit'];

    // แสดงฟอร์ม Edit Category
    if (isset($_POST["update_category"])) {
        // ถ้ามีการกด "Edit Category"
        $cat_title = $_POST["cat_title"];
        $cat_title_thai = $_POST["cat_title_thai"];
        $cat_title_china = $_POST["cat_title_china"];
        $cat_page = $_POST["cat_page"];

        // SQL UPDATE
        $query = "UPDATE tbl_categories SET cat_title='$cat_title', cat_title_thai='$cat_title_thai', cat_page='$cat_page', cat_title_china='$cat_title_china' WHERE cat_id=$cat_id";
        $update_query = mysqli_query($connection, $query);

        // ตรวจสอบว่า query สำเร็จไหม
        if (!$update_query) {
            die("Query Failed: " . mysqli_error($connection));
        }

        // Redirect ไปที่หน้า categories.php
        header("Location: categories.php");
        exit(); // ทำให้ script หยุดหลังจาก redirect
    }
}


?>
<script>
    $(document).ready(function() {
        $('#example').DataTable({
            layout: {
                topStart: {
                    buttons: ['copy', 'excel', 'pdf', 'colvis']
                }
            },
            columnDefs: [{
                "orderable": false,
                "targets": [0, 6]
            }]
        });
    });
</script>
<script>
    function toggleForms() {
        var addForm = document.getElementById("add-category-form");
        var editForm = document.getElementById("edit-category-form");

        if (editForm) {
            addForm.style.display = "none";
            editForm.style.display = "block";
        }
    }

    function showAddForm() {
        var addForm = document.getElementById("add-category-form");
        var editForm = document.getElementById("edit-category-form");

        addForm.style.display = "block";
        if (editForm) {
            editForm.style.display = "none";
        }
    }

    window.onload = function() {
        <?php if ($edit_mode) { ?>
            toggleForms();
        <?php } else { ?>
            showAddForm();
        <?php } ?>
    };
</script>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Categories</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <?php echo $suscess_delete ?>
    <form action="" method="post">
        <div>
            <input type="submit" class="btn btn-danger mb-2" style="width:6rem;" name="delete" value="Delete" onClick="return confirmDelete();">
        </div>

        <script>
            function confirmDelete() {
                let checkboxes = document.querySelectorAll("input[name='checkBoxArray[]']:checked");
                if (checkboxes.length > 0) {
                    return confirm("Are you sure you want to delete the selected items?");
                } else {
                    alert("Please select at least one categories before deleting.");
                    return false;
                }
            }
        </script>
    <div class="row">
        <!-- จัดการ Add Category และ Edit Category -->
        <div class="col-xxl-3 col-md-12">
            <div id="add-category-form" class="card pt-4 ">
                <div class="card-body">
                    <h3><strong>&nbsp;Add Category</strong></h3>
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="cat_title" class="fw-bold mb-2">&nbsp;&nbsp;Category</label>
                            <input type="text" class="form-control" name="cat_title" id="">
                        </div>
                        <div class="form-group mt-3">
                            <label for="cat_title" class="fw-bold mb-2">&nbsp;&nbsp;[ภาษาไทย] Category</label>
                            <input type="text" class="form-control" name="cat_title_thai" id="">
                        </div>
                        <div class="form-group mt-3">
                            <label for="cat_title" class="fw-bold mb-2">&nbsp;&nbsp;[ภาษาจีน] Category</label>
                            <input type="text" class="form-control" name="cat_title_china" id="">
                        </div>
                        <div class="form-group mt-3">
                            <label for="cat_page" class="fw-bold mb-2">&nbsp;&nbsp;Category page</label>
                            <input type="number" class="form-control" name="cat_page" id="">
                        </div>
                        <div class="form-group mt-3">
                            <input class="btn btn-primary" type="submit" name="submit" value="  Add Category">
                        </div>
                    </form>
                </div>
            </div>

            <!-- ฟอร์ม Edit Category -->
            <form action="" method="POST">
                <?php
                if (isset($_GET['edit'])) {
                    $cat_id = $_GET['edit'];
                    $query = "SELECT * FROM tbl_categories WHERE cat_id=$cat_id";
                    $fetch_data = mysqli_query($connection, $query);
                    while ($Row = mysqli_fetch_assoc($fetch_data)) {
                        $cat_title = $Row["cat_title"];
                        $cat_title_thai = $Row["cat_title_thai"];
                        $cat_title_china = $Row["cat_title_china"];
                        $cat_page = $Row["cat_page"];
                        if (isset($cat_title)) {
                ?>
                            <div id="edit-category-form" class="card pt-4" >
                                <div class="card-body">
                                    <h3><strong>&nbsp;Edit Category</strong></h3>
                                    <div class="form-group ">
                                        <label for="cat_title" class="fw-bold mb-2">&nbsp;&nbsp;Category</label>
                                        <input type="text" value="<?php echo $cat_title; ?>" class="form-control" name="cat_title" id="">
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="cat_title_thai" class="fw-bold mb-2">&nbsp;&nbsp;[ภาษาไทย] Category</label>
                                        <input type="text" value="<?php echo $cat_title_thai; ?>" class="form-control" name="cat_title_thai" id="">
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="cat_title_thai" class="fw-bold mb-2">&nbsp;&nbsp;[ภาษาจีน] Category</label>
                                        <input type="text" value="<?php echo $cat_title_china; ?>" class="form-control" name="cat_title_china" id="">
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="cat_page" class="fw-bold mb-2">&nbsp;&nbsp; Category Page</label>
                                        <input type="number" value="<?php echo $cat_page; ?>" class="form-control" name="cat_page" id="">
                                    </div>

                                    <div class="form-group mt-3">
                                        <input class="btn btn-primary" type="submit" name="update_category" value="Edit Category">
                                    </div>
                                </div>
                            </div>
                <?php };
                    }
                }
                ?>
            </form>
            <span class=""><?php echo $error_message; ?></span>
        </div>

        <!-- จัดตาราง ID ทางขวา -->
        <div class="col-xxl-9 col-md-12">
            <div class="card pt-5">
                <div class="card-body">
                    <table id="example" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th><input type='checkbox' id='selectAllBoxes' onclick="selectAll(this)"></th>
                                <th>ID</th>
                                <th>Category</th>
                                <th>[ภาษาไทย] Category</th>
                                <th>[ภาษาจีน] Category</th>
                                <th>Category Page</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM tbl_categories";;
                            $fetch_data = mysqli_query($connection, $query);
                            while ($Row = mysqli_fetch_assoc($fetch_data)) {
                                echo "<tr>
                                <td><input type='checkbox' name='checkBoxArray[]' value='{$Row['cat_id']}'></td>
                                    <td>{$Row['cat_id']}</td>
                                    <td>{$Row['cat_title']}</td>
                                    <td>{$Row['cat_title_thai']}</td>
                                    <td>{$Row['cat_title_china']}</td>
                                    <td>{$Row['cat_page']}</td>
                                    <td>
                                        <a href='categories.php?edit={$Row['cat_id']}'><i class='bi bi-pencil-square ' aria-hidden='true'></i></a> |
                                        <a onClick=\"javascript: return confirm('Please confirm deletion');\"href='categories.php?delete={$Row['cat_id']}'><i class='bi bi-trash' aria-hidden='true'></i></a>
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </form>
</main>
<?php include "includes_backend/footer.php" ?>