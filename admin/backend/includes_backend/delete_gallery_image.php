<?php
include_once '../../config.php'; // Include your database connection file

if (isset($_GET['id']) && isset($_GET['activity_id'])) {
    $img_id = $_GET['id'];
    $activity_id = $_GET['activity_id'];

    // Fetch the image name from the database
    $query = "SELECT image_name FROM tbl_activity_gallery WHERE id = $img_id";
    $result = mysqli_query($connection, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        $image_name = $row['image_name'];
        $image_path = "../activity_gallery/$image_name";

        // Delete the image file from the server
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Delete the image record from the database
        $delete_query = "DELETE FROM tbl_activity_gallery WHERE id = $img_id";
        mysqli_query($connection, $delete_query);
    }

    // Redirect back to the edit page
    header("Location: edit_activity.php?p_id=$activity_id");
    exit();
}
?>