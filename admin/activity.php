<?php
//include "includes_admin/db.php";
include '../includes/db.php';
/* Page Header and navigation */
include "includes_admin/header.php";
include "includes_admin/navigation.php";

$errMessage = false;

?>
<!-- Page Content -->
<div class="main" id="main">
    <div class="card py-4">
        <div class="container">

            <?php
            if (isset($_GET['p_id'])) {
                $the_activity_id = $_GET['p_id'];
                $lang = $_GET['lang'];
                $query = "SELECT * FROM tbl_activity WHERE activity_id = $the_activity_id ";
                $select_all_activity_query = mysqli_query($connection, $query);

                while ($Row = mysqli_fetch_assoc($select_all_activity_query)) {
                    $activity_id = $Row['activity_id'];
                    switch ($lang) {
                        case 'en':
                            $activity_title =  base64_decode($Row['activity_title']);
                            $activity_subtitle =  base64_decode($Row['activity_subtitle']);
                            $activity_content = base64_decode($Row['activity_content']);
                            break;
                        case 'cn':
                            $activity_title =  base64_decode($Row['activity_title_china']);
                            $activity_subtitle =  base64_decode($Row['activity_subtitle_china']);
                            $activity_content = base64_decode($Row['activity_content_china']);
                            break;
                        default:
                            $activity_title =  base64_decode($Row['activity_title_thai']);
                            $activity_subtitle =  base64_decode($Row['activity_subtitle_thai']);
                            $activity_content = base64_decode($Row['activity_content_thai']);
                            break;
                    }
                    $activity_link_url = $Row['activity_link'];
                    $activity_date = $Row['activity_date'];
                    $activity_image = $Row['activity_image'];
            ?>

                    <!-- Blog Post -->

                    <!-- Title -->
                    <h1><?php echo $activity_title; ?></h1>

                    <!-- Subtitle -->
                    <h3><?php echo $activity_subtitle; ?></h3>

                    <!-- Author -->
                    <!-- <p class="lead">
                        by </a>
                    </p> -->
                    <!--Link Url-->
                    <a class="btn btn-link " href="<?php echo $activity_link_url ?>" target="_blank">click link</i></a>

                    <hr>

                    <!-- Date/Time -->
                    <p><span class="glyphicon glyphicon-time"></span> Posted on <?php echo $activity_date; ?></p>

                    <hr>
                    <div class="row">
                        <!-- Blog Post Content Column -->
                        <div class="col-lg-8">
                            <!-- Preview Image -->
                            <img class="img-fluid" src="<?php echo "activity/" . $activity_image; ?>" alt="<?php echo $activity_title; ?>">

                            <hr>

                            <!-- Post Content -->
                            <p><?php echo $activity_content; ?></p>

                            <!-- Blog Comments -->

                        </div>

                        <!-- Blog Sidebar Widgets Column -->
                        <?php
                        //      include "includes/sidebar.php"
                        ?>
                    </div>
            <?php
                }
            }
            ?>
        </div>
    </div>

    <hr>
</div>
<?php
/* Page Footer */
include "includes_admin/footer.php"
?>