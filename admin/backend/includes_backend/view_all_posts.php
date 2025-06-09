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
                "targets": [0,5,7]
            }]
        });
    });
</script>
<?php
// Delete Post.
if (isset($_GET["deletePost"])) {
    $post_id = $_GET['deletePost'];
    $post_image = $_GET['image'];
    $query = "DELETE FROM tbl_posts WHERE post_id=$post_id";
    $delete_query = mysqli_query($connection, $query);
    unlink('../images/' . $post_image);
    header("Location: posts.php");
    if (!$delete_query) {
        die("Query Failed: " . mysqli_error($connection));
    }
}

if (isset($_POST["apply"])) {
    if (isset($_POST["checkBoxArray"])) {
        foreach ($_POST["checkBoxArray"] as $checkBoxValue) {
            $bulk_option = $_POST['bulk_option'];
            switch ($bulk_option) {
                case 'Published':
                    $query = "UPDATE tbl_posts SET post_status = '$bulk_option' WHERE post_id=$checkBoxValue";
                    $update_post = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Post published successfully.</p>";
                    if (!$update_post) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                case 'Draft':
                    $query = "UPDATE tbl_posts SET post_status = '$bulk_option' WHERE post_id=$checkBoxValue";
                    $update_post = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Post draftted successfully.</p>";
                    if (!$update_post) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                case 'Delete':
                    $query = "DELETE FROM tbl_posts WHERE post_id = $checkBoxValue";
                    $update_post = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Post deleted successfully.</p>";
                    if (!$update_post) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                default:
                    echo "<p class='alert alert-danger'>Please an option.</p>";
                    break;
            }
        }
    } else {
        echo "<p class='alert alert-danger'>Please select post.</p>";
    }
}
?>

<form action="" method="POST">
    <table id="example" class="display" style="width:100%">
        <div class="row d-flex align-items-center">
            <div class="col-4">
                <select class="form-control" name="bulk_option">
                    <option value="">Select Options</option>
                    <option value="Published">Publish</option>
                    <option value="Draft">Draft</option>
                    <option value="Delete">Delete</option>
                </select>
            </div>
            <div class="col-7">
                <input type="submit" class="btn btn-success" name="apply" value="Apply">
                <a class="btn btn-primary" href="posts.php?source=add_post">Add New</a>
            </div>
        </div>
        <thead>
            <tr>
                <th><input type='checkbox' id='selectAllBoxes' onclick="selectAll(this)"></th>
                <th style="width:40px;">ID</th>
                <th style="width: 300px;">Title[EN] / Title[TH] / Title[CH]</th>
                <th style="width: 300px;">Category[EN] / Category[TH] / Category[CN]</th>
                <th>Status</th>
                <th style="width:100px">Image</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM tbl_posts order by post_id desc";
            $fetch_posts_data = mysqli_query($connection, $query);
            while ($Row = mysqli_fetch_assoc($fetch_posts_data)) {
                $the_post_id = $Row['post_id'];
                $the_post_image = $Row['post_image'];
                $the_post_title = base64_decode($Row['post_title']);
                $the_post_title_thai = base64_decode($Row['post_title_thai']);
                $the_post_title_china = base64_decode($Row['post_title_china']);
                $datetime_from_db = $Row['post_date'];

                $date = new DateTime($datetime_from_db);
                $date_time = $date->format("d/m/Y");
            ?>
                <td><input type='checkbox' name='checkBoxArray[]' value='<?php echo $the_post_id ?>'></td>
            <?php
                echo "<td>$the_post_id</td>
                    <td><a href='../post.php?lang=en&p_id=$the_post_id'><i class='bi bi-search'></i>
</i>
$the_post_title</a><br>
                     <a href='../post.php?lang=th&p_id=$the_post_id'><i class='bi bi-search'></i>$the_post_title_thai</a><br>
                    <a href='../post.php?lang=cn&p_id=$the_post_id'><i class='bi bi-search'></i>$the_post_title_china</a></td>";

                $cat_id = $Row['post_category_id'];
                $query = "SELECT * FROM tbl_categories WHERE cat_id=$cat_id";
                $fetch_cat_data = mysqli_query($connection, $query);
                while ($Cat = mysqli_fetch_assoc($fetch_cat_data)) {
                    $cat_title = $Cat["cat_title"];
                    $cat_title_thai = $Cat["cat_title_thai"];
                    $cat_title_china = $Cat['cat_title_china'];
                    if (isset($cat_title)) {
                        echo "<td>$cat_title / $cat_title_thai/$cat_title_china</td>";
                        // echo "<td>$cat_title_thai</td>";
                    }
                }
                echo "<td>{$Row['post_status']}</td>
                    <td><img src='../post/{$the_post_image}' alt='image' width='150px' height='auto' style='object-fit: contain; text-align:center; '></td>
                    <td>$date_time</td>
                    <td class='text-center'>
                        <a href='posts.php?source=edit_post&p_id=$the_post_id'><i class='bi bi-pencil-square' aria-hidden='true'></i></a> | 
                        <a onClick=\"javascript: return confirm('Are you sure you want to delete'); \" href='posts.php?deletePost=$the_post_id&image=$the_post_image'><i class='bi bi-trash' aria-hidden='true'></i></a> 
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
</form>