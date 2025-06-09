<?php include("./includes/header.php");
include('pagination.php');
?>
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>

<body>

    <!-- Alt Services Section -->
    <section id="alt-services" class="alt-services section py-5">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-4">

                <?php
                while ($Row = mysqli_fetch_array($nquery)) {
                    $the_activity_id = $Row['activity_id'];
                    $the_activity_image = $Row['activity_image'];
                    $the_activity_date = $Row['activity_date'];
                    if ($_SESSION['lang'] == 'en') {
                        $the_activity_title = base64_decode($Row['activity_title']);
                        $the_activity_content = base64_decode($Row['activity_content']);
                    } else {
                        $the_activity_title = base64_decode($Row['activity_title_thai']);
                        $the_activity_content = base64_decode($Row['activity_content_thai']);
                    }
                ?>

                    <div class="col-md-6 col-lg-4 col-xl-3 d-flex" data-aos="zoom-in" data-wow-delay="0.1s">
                        <div class="card shadow-sm border-0 rounded-4 w-100 d-flex flex-column">
                            <div class="position-relative">
                                <img src="<?php echo "admin/activity/" . $the_activity_image; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Activity Image">
                            </div>

                            <div class="d-flex flex-column text-center">
                            <p class="text-muted small mb-3"><i class="fa-solid fa-calendar-days me-1"></i><?php echo date("d-m-Y", strtotime($the_activity_date)) ?></p>
                            </div>

                            <div class="card-body d-flex flex-column text-center">

                                <h6 class="fw-bold mb-2"><?php echo $the_activity_title; ?></h6>
                                <br>
                                
                                <!-- ปุ่มอยู่ล่างสุดเสมอ -->
                                <a href="javascript:void(0)" onclick="openPopup('<?php echo $_SESSION['lang']; ?>', '<?php echo $the_activity_id; ?>')" class="btn btn-outline-primary btn-sm mt-auto">
                                    ดูรายละเอียด <i class="fa-solid fa-circle-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php } ?>

            </div>

            <div class="mt-4" id="pagination_controls"><?php echo $paginationCtrls; ?></div>
        </div>
    </section>

    <script>
    function openPopup(lang, activityId) {
        const url = `service-activity.php?lan=${lang}&id=${activityId}`;
        const popupOptions = "width=800,height=600,scrollbars=yes,resizable=yes";
        window.open(url, "_blank", popupOptions);
    }
    </script>

</body>

<!-- Start Footer -->
<?php include("./includes/footer.php") ?>
<!-- End Footer -->