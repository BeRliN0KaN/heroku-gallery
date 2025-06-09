<?php
include "includes_backend/header.php";
include "includes_backend/navigation.php";
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
                "targets": [0,6,7]
            }]
        });
    });
</script>
<?php
// Delete User.
if (isset($_GET["delete"])) {
    $user_id = mysqli_real_escape_string($connection, $_GET['delete']);
    $query = "DELETE FROM tbl_users WHERE user_id=$user_id";
    $delete_query = mysqli_query($connection, $query);
    header("Location: users.php");
    if (!$delete_query) {
        die("Query Failed: " . mysqli_error($connection));
    }
}

if (isset($_POST["delete"])) {
    if (isset($_POST["checkBoxArray"])) {
        foreach ($_POST["checkBoxArray"] as $checkBoxValue) {
            $query = "DELETE FROM tbl_users WHERE user_id = $checkBoxValue";
            $update_user = mysqli_query($connection, $query);
            if (!$update_user) {
                die("Query Failed: " . mysqli_error($connection));
            }
        }
        echo "<p class='alert alert-success'>Selected users deleted successfully.</p>";
    }
}


?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>View All Users</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">View All Users</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <div class="card pt-5">
        <div class="card-body">
            <form action="" method="post">
                <table id="example" class="display" style="width:100%">
                    <div class="row d-flex align-items-center">
                        <div class="col-12">
                            <input type='submit' class='btn btn-danger' name='delete' value='Delete' onClick="return confirmDelete();">
                            <a class="btn btn-primary" href="users.php?source=add_user">Add New</a>
                        </div>
                    </div>
                    <script>
                        function confirmDelete() {
                            let checkboxes = document.querySelectorAll("input[name='checkBoxArray[]']:checked");
                            if (checkboxes.length > 0) {
                                return confirm("Are you sure you want to delete the selected items?");
                            } else {
                                alert("Please select at least one user before deleting.");
                                return false;
                            }
                        }
                    </script>
                    <thead>
                        <tr>
                            <th><input type='checkbox' id='selectAllBoxes' onclick="selectAll(this)"></th>
                            <th class="text-center">ID</th>
                            <th class="text-center">Username</th>
                            <th class="text-center">Firstname</th>
                            <th class="text-center">Lastname</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Image</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM tbl_users WHERE user_name != '" . mysqli_real_escape_string($connection, $_SESSION['username']) . "'";
                        $fetch_posts_data = mysqli_query($connection, $query);
                        while ($Row = mysqli_fetch_assoc($fetch_posts_data)) {

                            $user_id = $Row['user_id'];
                            $user_image = $Row['user_image'];
                            $user_name = $Row['user_name'];
                            $user_firstname = $Row['user_firstname'];
                            $user_lastname = $Row['user_lastname'];
                            $user_email = $Row['user_email'];

                        if($user_image == "default.jpg"){
                            $user_image = "<img src='../images/img-icon/profile.webp' width='150px' height='auto' style='object-fit: contain; text-align:center; '>";
                        }else{
                            $user_image = "<img src='../profile/{$user_image}' width='150px' height='auto' style='object-fit: contain; text-align:center; '>";
                        }
                        
                            echo "<tr>
                    <td><input type='checkbox' name='checkBoxArray[]' value='{$user_id}'></td>
                    <td>$user_id</td>
                    <td>$user_name</td>
                    <td>$user_firstname</td>
                    <td>$user_lastname</td>
                    <td>$user_email</td>
                    <td>$user_image</td>
                    <td class='text-center'>
                            <a href='users.php?source=edit_user&user_id=$user_id'><i class='bi bi-pencil-square' aria-hidden='true'></i></a> </a> |
                            <a onClick=\"javascript: return confirm('Are you sure you want to delete'); \" href='users.php?delete=$user_id'><i class='bi bi-trash' aria-hidden='true'></i></a>
                     </td>
                </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</main>

<?php include "includes_backend/footer.php" ?>

