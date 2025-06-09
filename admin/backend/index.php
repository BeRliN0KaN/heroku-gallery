<!-- ======= Header ======= -->
<?php include "includes_backend/header.php" ?>
<!-- End Header -->

<!-- ======= Sidebar ======= -->
<?php include "includes_backend/navigation.php"; ?>
<!-- End Sidebar-->

<?php
// ─── จัดการลบแชร์ ───
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['deleteShare'])) {
    $delId = intval($_GET['deleteShare']);
    mysqli_query(
        $connection,
        "DELETE FROM tbl_activity_share WHERE share_id = $delId"
    );
    header("Location: index.php");
    exit();
}

// ─── ดึงข้อมูลแชร์ทั้งหมดของผู้ใช้คนนี้ ───
$currentUserId = intval($_SESSION['user_id'] ?? 0);
$sqlShare = "
  SELECT
    s.share_id,
    s.activity_id,
    u.user_name      AS shared_by,
    a.activity_title,
    s.share_date
  FROM tbl_activity_share s
  JOIN tbl_users    u ON u.user_id = s.shared_by_user_id
  JOIN tbl_activity a ON a.activity_id = s.activity_id
  WHERE s.shared_to_user_id = {$currentUserId}
  ORDER BY s.share_date DESC
";
$resShare = mysqli_query($connection, $sqlShare);
?>


<main id="main" class="main">

    <div class="pagetitle">
        <h1><?= constant('page_index_activity_1') ?></h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><?= constant('page_index_activity_2') ?></a></li>
                <li class="breadcrumb-item active"><?= constant('page_index_activity_1') ?></li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <!-- Left side columns -->
            <div class="col-lg-8 ">
                <div class="row">
                    <!-- Website Traffic -->
                    <div class="card">
                        <div class="card-body pb-0">
                            <h5 class="card-title"><?= constant('page_index_activity_3') ?> </h5>

                            <div id="trafficChart" style="min-height: 420px;" class="echart"></div>
                            <?php
                            $current_user_id = intval($_SESSION['user_id']);
                            $current_user_role = $_SESSION['user_role'];

                            // Posts (ไม่จำกัดเฉพาะ user เพราะ post ของทุกคนใช้ร่วมกัน)
                            $query = "SELECT * FROM tbl_posts WHERE post_status='Published'";
                            $select_active_posts = mysqli_query($connection, $query);
                            $active_posts_count = mysqli_num_rows($select_active_posts);

                            $query = "SELECT * FROM tbl_posts WHERE post_status='Draft'";
                            $select_draft_posts = mysqli_query($connection, $query);
                            $draft_posts_count = mysqli_num_rows($select_draft_posts);

                            // Categories
                            $query = "SELECT * FROM tbl_categories";
                            $select_all_categories = mysqli_query($connection, $query);
                            $categories_posts_count = mysqli_num_rows($select_all_categories);

                            // Activity (ต่างกันตาม role)
                            if ($current_user_role === 'admin') {
                                $query = "SELECT * FROM tbl_activity WHERE activity_status='Published'";
                            } else {
                                $query = "SELECT * FROM tbl_activity WHERE activity_status='Published' AND user_id = $current_user_id";
                            }
                            $select_active_activity = mysqli_query($connection, $query);
                            $active_activity_count = mysqli_num_rows($select_active_activity);

                            if ($current_user_role === 'admin') {
                                $query = "SELECT * FROM tbl_activity WHERE activity_status='Lock'";
                            } else {
                                $query = "SELECT * FROM tbl_activity WHERE activity_status='Lock' AND user_id = $current_user_id";
                            }
                            $select_draft_activity = mysqli_query($connection, $query);
                            $draft_activity_count = mysqli_num_rows($select_draft_activity);

                            // Users (เฉพาะ admin เท่านั้น)
                            $query = "SELECT * FROM tbl_users";
                            $select_all_users = mysqli_query($connection, $query);
                            $users_count = mysqli_num_rows($select_all_users);
                            ?>


                            <script>
                                document.addEventListener("DOMContentLoaded", () => {
                                    echarts.init(document.querySelector("#trafficChart")).setOption({
                                        tooltip: {
                                            trigger: 'item',
                                            formatter: '{b}: {c} ({d}%)' // แสดงชื่อ, ค่า, และเปอร์เซ็นต์
                                        },
                                        legend: {
                                            top: '2%',
                                            left: 'center'
                                        },
                                        series: [{
                                            name: 'Access From',
                                            type: 'pie',
                                            radius: ['40%', '70%'],
                                            top: '5%',
                                            avoidLabelOverlap: false,
                                            label: {
                                                show: true,
                                                position: 'outside',
                                                formatter: '{b}: {d}%'
                                            },
                                            emphasis: {
                                                label: {
                                                    show: true,
                                                    fontSize: '18',
                                                    fontWeight: 'bold'
                                                }
                                            },
                                            labelLine: {
                                                show: true
                                            },
                                            data: [<?php if ($_SESSION['user_role'] == "admin") { ?> {
                                                        value: <?php echo $active_posts_count; ?>,
                                                        name: 'Published Posts'
                                                    },
                                                    {
                                                        value: <?php echo $draft_posts_count; ?>,
                                                        name: 'Draft Posts'
                                                    },
                                                    {
                                                        value: <?php echo $categories_posts_count; ?>,
                                                        name: 'Categories Posts'
                                                    },
                                                    {
                                                        value: <?php echo $users_count; ?>,
                                                        name: 'Users'
                                                    },
                                                <?php } ?> {
                                                    value: <?php echo $active_activity_count; ?>,
                                                    name: 'Published Active'
                                                },
                                                {
                                                    value: <?php echo $draft_activity_count; ?>,
                                                    name: 'Lock Active'
                                                }
                                            ]
                                        }]
                                    });
                                });
                            </script>


                        </div>
                    </div><!-- End Website Traffic -->
                    <!-- Categories Post Card -->

                </div><!-- End Right side columns -->
            </div>
            <!-- Right side columns -->

            <div class="col-lg-4 ">
                <div class="card">

                    <div class="card-body">

                        <?php if ($resShare && mysqli_num_rows($resShare) > 0): ?>
                            <h5 class="card-title"><?php echo constant('page_index_top_1') ?></h5>
                            <?php while ($row = mysqli_fetch_assoc($resShare)):
                                $shareId = intval($row['share_id']);
                                $id       = intval($row['activity_id']);
                                $title    = base64_decode($row['activity_title']);
                                $by       = htmlspecialchars($row['shared_by']);
                                $when     = date('d/m/Y', strtotime($row['share_date']));
                            ?>
                                <div class="mb-3 p-2 border rounded">
                                    <strong><?= $title ?></strong><br>
                                    <small> <?php echo constant('page_index_top_2') ?> <?= $by ?> <?php echo constant('page_index_top_3') ?> <?= $when ?></small><br>
                                    <div class="mt-1 d-flex align-items-center">
                                        <button class="btn btn-sm btn-primary me-2"
                                            onclick="openpopup('service-activity.php?lan=<?= $_SESSION['lang'] ?>&id=<?= $id ?>')">
                                            <?php echo constant('page_index_button_7') ?>
                                        </button>
                                        <button class="btn btn-sm btn-danger py-1"
                                            onclick="if (confirm('ยืนยันลบการแชร์นี้?')) window.location='?deleteShare=<?= $shareId ?>'">
                                            <?php echo constant('page_index_button_8') ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <h5 class="card-title"><?php echo constant('page_index_top_4') ?></h5>
                        <?php endif; ?>

                    </div>

                    <script>
                        function openpopup(url) {
                            window.open(
                                url,
                                '_blank',
                                'width=800,height=600,scrollbars=yes'
                            );
                        }
                    </script>


                    <?php
                    $result = mysqli_query($connection, "SELECT COUNT(*) as total_visitors FROM tbl_site_visitors");
                    $row = mysqli_fetch_assoc($result);
                    $total_visitors = $row['total_visitors'];
                    ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            echarts.init(document.querySelector("#trafficChart")).setOption({
                                tooltip: {
                                    trigger: 'item',
                                    formatter: '{b}: {c} ({d}%)'
                                },
                                legend: {
                                    top: '2%',
                                    left: 'center'
                                },
                                series: [{
                                    name: 'Access From',
                                    type: 'pie',
                                    radius: ['40%', '70%'],
                                    top: '5%',
                                    avoidLabelOverlap: false,
                                    label: {
                                        show: true,
                                        position: 'outside',
                                        formatter: '{b}: {d}%'
                                    },
                                    emphasis: {
                                        label: {
                                            show: true,
                                            fontSize: '18',
                                            fontWeight: 'bold'
                                        }
                                    },
                                    labelLine: {
                                        show: true
                                    },
                                    data: [
                                        <?php if ($_SESSION['user_role'] == "admin") { ?> {
                                                value: <?php echo $active_posts_count; ?>,
                                                name: '<?= constant('page_index_activity_4') ?>'
                                            },
                                            {
                                                value: <?php echo $draft_posts_count; ?>,
                                                name: '<?= constant('page_index_activity_5') ?>'
                                            },
                                            {
                                                value: <?php echo $categories_posts_count; ?>,
                                                name: '<?= constant('page_index_activity_6') ?>'
                                            },
                                            {
                                                value: <?php echo $users_count; ?>,
                                                name: '<?= constant('page_index_activity_7') ?>'
                                            },
                                        <?php } ?> {
                                            value: <?php echo $active_activity_count; ?>,
                                            name: '<?= constant('page_index_activity_8') ?>'
                                        },
                                        {
                                            value: <?php echo $draft_activity_count; ?>,
                                            name: '<?= constant('page_index_activity_9') ?>'
                                        }
                                    ]
                                }]
                            });
                        });
                    </script>

                </div>
            </div>
        </div><!-- End Right side columns -->

        <!-- Start Categories -->
        <?php if ($_SESSION['user_role'] == "admin") { ?>
            <div class="col-xxl-6 col-md-12 ">
                <div class="card info-card revenue-card">

                    <div class="card-body">
                        <h5 class="card-title"><?= constant('page_index_activity_6') ?></h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-folder"></i>

                            </div>
                            <div class="ps-3">
                                <?php
                                $query = "SELECT * FROM tbl_categories";
                                $select_all_categories = mysqli_query($connection, $query);
                                $categories_posts_count = mysqli_num_rows($select_all_categories);
                                ?>
                                <h6><?php echo $categories_posts_count ?> <span><?= constant('page_index_activity_10') ?></span></h6>
                                <a href="categories.php">
                                    <span class="text-muted small pt-2 ps-1"><?= constant('page_index_button_7') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- End Categories Post Card -->

        <!-- Activitys Card -->
        <div class="col-xxl-6 col-xl-12 px-0">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title"><?= constant('page_index_activity_11') ?></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-clipboard"></i>
                        </div>
                        <div class="ps-3">
                            <?php
                            if ($_SESSION['user_role'] == 'admin') {
                                $query = "SELECT * FROM tbl_activity";
                            } else {
                                $current_user_id = intval($_SESSION['user_id']);
                                $query = "SELECT * FROM tbl_activity WHERE user_id = $current_user_id";
                            }
                            $select_activity = mysqli_query($connection, $query);
                            $activity_count = mysqli_num_rows($select_activity);
                            ?>
                            <h6><?php echo $activity_count; ?> <span><?= constant('page_index_activity_11') ?></span></h6>
                            <a href="activity.php">
                                <span class="text-muted small pt-2 ps-1"><?= constant('page_index_button_7') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Activitys Card -->

        <!-- Post Card -->
        <?php if ($_SESSION['user_role'] == "admin") { ?>
            <div class="col-xxl-6 col-md-12">
                <div class="card info-card revenue-card">

                    <div class="card-body">
                        <h5 class="card-title"><?= constant('page_index_activity_12') ?></h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="ps-3">
                                <?php
                                $query = "SELECT * FROM tbl_posts";
                                $select_all_posts = mysqli_query($connection, $query);
                                $posts_count = mysqli_num_rows($select_all_posts);

                                ?>
                                <h6><?php echo $posts_count; ?> <span><?= constant('page_index_activity_12') ?></span></h6>
                                <a href="posts.php">
                                    <span class="text-muted small pt-2 ps-1"><?= constant('page_index_button_7') ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <!-- End Poste Card -->

        <!-- User Card -->
        <?php if ($_SESSION['user_role'] == "admin") { ?>
            <div class="col-xxl-6 col-xl-12 px-0">

                <div class="card info-card customers-card">

                    <div class="card-body">

                        <h5 class="card-title"><?= constant('page_index_activity_7') ?></h5>

                        <div class="d-flex align-items-center">
                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="ps-3">
                                <?php
                                $query = "SELECT * FROM tbl_users";
                                $select_all_users = mysqli_query($connection, $query);
                                $users_count = mysqli_num_rows($select_all_users);
                                ?>
                                <h6><?php echo $users_count; ?> <span><?= constant('page_index_activity_7') ?></span></h6>
                                <a href="users.php">
                                    <span class="text-muted small pt-2 ps-1"><?= constant('page_index_button_7') ?></span>
                                </a>

                            </div>
                        </div>

                    </div>
                </div>

            </div>
        <?php } ?>
        <!-- End User Card -->
        </div>
    </section>

</main><!-- End #main -->


<?php include "includes_backend/footer.php" ?>