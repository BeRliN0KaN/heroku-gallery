<?php
ob_start();
include '../../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: ../../../admin/login.php"); // redirect กลับ login ถ้ายังไม่ได้ login
    exit;
}

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];

// เลือก query ตามสิทธิ์
if ($current_user_role === 'admin') {
    $query = "SELECT * FROM tbl_activity ORDER BY activity_id DESC";
} else {
    $query = "SELECT * FROM tbl_activity WHERE user_id = $current_user_id ORDER BY activity_id DESC";
}
$fetch_activity_data = mysqli_query($connection, $query);
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
                "targets": [0, 4, 6]
            }]
        });
    });
</script>


<script>
  function shareActivity(activityId) {
    const username = prompt('กรอกชื่อผู้ใช้ที่ต้องการแชร์:');
    if (!username) return;

    $.post('/admin/backend/share_activity.php', {
      activity_id: activityId,
      username: username.trim()
    }, function(response) {
      alert(response.message);

      if (response.success) {
        renderShareTable(activityId, response.data);
      }
    }, 'json').fail(function() {
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    });
  }

  // ฟังก์ชันสร้างตารางแชร์
  function renderShareTable(activityId, shares) {
    let html = '<table class="table table-sm">';
    html += '<thead><tr><th>ผู้รับ</th><th>วันที่แชร์</th></tr></thead><tbody>';
    if (shares.length) {
      shares.forEach(function(item) {
        html += `<tr>
          <td>${item.shared_to}</td>
          <td>${new Date(item.share_date).toLocaleString()}</td>
        </tr>`;
      });
    } else {
      html += '<tr><td colspan="2">ยังไม่มีการแชร์</td></tr>';
    }
    html += '</tbody></table>';

    $('#barChartContainer').html(html);
  }

  // เมื่อโหลดหน้าครั้งแรก อยากให้แสดงรายการแชร์ของ activity แต่ละอันได้ด้วย
  $(document).on('click', '.bi-share', function() {
    // ปุ่มแชร์ถูกคลิกแล้วจะเรียก shareActivity -> renderShareTable
  });
</script>



<?php
// Delete Activity.
if (isset($_GET["deleteActivity"])) {
    $activity_id = intval($_GET['deleteActivity']);
    $activity_image = $_GET['image'];

    //  1. ลบรูปหลักจากโฟลเดอร์ ../activity/
    if (!empty($activity_image) && file_exists('../activity/' . $activity_image)) {
        unlink('../activity/' . $activity_image);
    }

    //  2. ดึงข้อมูลรูป gallery ที่เกี่ยวกับ activity_id นี้
    $query_gallery = "SELECT image_name FROM tbl_activity_gallery WHERE activity_id = $activity_id";
    $gallery_result = mysqli_query($connection, $query_gallery);

    if ($gallery_result) {
        while ($gallery = mysqli_fetch_assoc($gallery_result)) {
            $gallery_image = $gallery['image_name'];
            if (!empty($gallery_image) && file_exists('../activity_gallery/' . $gallery_image)) {
                unlink('../activity_gallery/' . $gallery_image); // ลบไฟล์ในโฟลเดอร์
            }
        }
    }

    // 3. ลบข้อมูลในตาราง tbl_activity_gallery
    $query_delete_gallery = "DELETE FROM tbl_activity_gallery WHERE activity_id = $activity_id";
    mysqli_query($connection, $query_delete_gallery);

    // 4. ลบข้อมูลในตาราง tbl_activity
    $query_delete_activity = "DELETE FROM tbl_activity WHERE activity_id = $activity_id";
    $delete_query = mysqli_query($connection, $query_delete_activity);

    if (!$delete_query) {
        die("Query Failed: " . mysqli_error($connection));
    }

    header("Location: activity.php");
}



if (isset($_POST["apply"])) {
    if (isset($_POST["checkBoxArray"])) {
        foreach ($_POST["checkBoxArray"] as $checkBoxValue) {
            $bulk_option = $_POST['bulk_option'];
            switch ($bulk_option) {
                case 'Published':
                    $query = "UPDATE tbl_activity SET activity_status = '$bulk_option' WHERE activity_id=$checkBoxValue";
                    $update_activity = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Activity published successfully.</p>";
                    if (!$update_activity) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                case 'Draft':
                    $query = "UPDATE tbl_activity SET activity_status = '$bulk_option' WHERE activity_id=$checkBoxValue";
                    $update_activity = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Activity Locked successfully.</p>";
                    if (!$update_activity) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                case 'Delete':
                    $query = "DELETE FROM tbl_activity WHERE activity_id = $checkBoxValue";
                    $update_activity = mysqli_query($connection, $query);
                    echo "<p class='alert alert-success'>Activity deleted successfully.</p>";
                    if (!$update_activity) {
                        die("Query Failed: " . mysqli_error($connection));
                    }
                    break;
                default:
                    echo "<p class='alert alert-danger'>Please an option.</p>";
                    break;
            }
        }
    } else {
        echo "<p class='alert alert-danger'>Please select activity.</p>";
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
                    <option value="Draft">Lock</option>
                    <option value="Delete">Delete</option>
                </select>
            </div>
            <div class="col-7">
                <input type="submit" class="btn btn-success" name="apply" value="Apply">
                <a class="btn btn-primary" href="activity.php?source=add_activity">Add New</a>
            </div>
        </div>
        <thead>
            <tr>
                <th><input type='checkbox' id='selectAllBoxes' onclick="selectAll(this)"></th>
                <th style="width:40px;">ID </th>
                <th style="width: 300px;">Title[EN] / Title[TH] / Title[CN]</th>
                <th>Status </th>
                <th style="width:100px">Image</th>
                <th>Date</th>
                <th style="width:80px">Action</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($Row = mysqli_fetch_assoc($fetch_activity_data)) {
                $the_activity_id = $Row['activity_id'];
                $the_activity_image = $Row['activity_image'];
                $the_activity_title = base64_decode($Row['activity_title']);
                $the_activity_title_thai = base64_decode($Row['activity_title_thai']);
                $the_activity_title_china = base64_decode($Row['activity_title_china']);

                echo "<tr>"; ?>
                <td><input type='checkbox' name='checkBoxArray[]' value='<?php echo $the_activity_id ?>'></td>
            <?php
                echo "<td>{$Row['activity_id']}</td>
        <td><a href='../activity.php?lang=en&p_id=$the_activity_id'>{$the_activity_title}</a>
         / <a href='../activity.php?lang=th&p_id=$the_activity_id'>{$the_activity_title_thai}</a>
         / <a href='../activity.php?lang=cn&p_id=$the_activity_id'>{$the_activity_title_china}</a>";

                $date = new DateTime($Row['activity_date']);
                $date_DMY = $date->format('d/m/Y');

                echo "<td>{$Row['activity_status']}</td>
         <td><img src='../activity/{$Row['activity_image']}' alt='image' width='150px' height='65px' style='object-fit: cover; text-align:center;'></td>
        <td>{$date_DMY}</td>
        <td class='text-center'>
            <a href='activity.php?source=edit_activity&p_id=$the_activity_id'><i class='bi bi-pencil-square ' aria-hidden='true'></i></a> | 
            <a onClick=\"javascript: return confirm('Are you sure you want to delete'); \" href='activity.php?deleteActivity=$the_activity_id&image=$the_activity_image'><i class='bi bi-trash ' aria-hidden='true'></i></a> |
             
            <a href='#' onclick=\"shareActivity($the_activity_id)\"><i class='bi bi-share' aria-hidden='true'></i></a>
        </td>
    </tr>";
            }
            ?>
        </tbody>

    </table>
</form>