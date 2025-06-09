  <!-- Start Header -->
  <?php include("./includes/header.php") ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@v2.8.6/dist/cookieconsent.css" />
  <!-- End Header -->

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container">
        <div class="row gy-4">
          <div class="container" data-aos="fade-up" data-aos-delay="100">

            <?php
            $query = "SELECT * FROM tbl_posts inner join tbl_categories on tbl_categories.cat_id = tbl_posts.post_category_id   where tbl_categories.cat_page=1 and tbl_categories.cat_id=1 AND tbl_posts.post_status='Published'";
            $fetch_posts_data = mysqli_query($connection, $query);
            while ($Row = mysqli_fetch_assoc($fetch_posts_data)) {
              $the_post_id = $Row['post_id'];
              $the_post_image = $Row['post_image'];
              $lang = $_SESSION['lang'];
              switch ($lang) {
                case 'en':
                  $the_post_title = base64_decode($Row['post_title']);
                  $the_post_content = base64_decode($Row['post_content']);
                  break;
                case 'cn':
                  $the_post_title = base64_decode($Row['post_title_china']);
                  $the_post_content = base64_decode($Row['post_content_china']);
                  break;
                default:
                  $the_post_title = base64_decode($Row['post_title_thai']);
                  $the_post_content = base64_decode($Row['post_content_thai']);
                  break;
              }
            ?>
            <?php } ?>
          </div>

          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
            <h1><?php echo $the_post_title ?></h1>
            <p ><?php echo $the_post_content ?></p>
          </div>
          <div class="col-lg-6 order-1 order-lg-2 hero-img">
            <img src="<?php echo "admin/post/" . $the_post_image; ?>" class="img-fluid animated" alt="">
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->




  <!-- Alt Services Section -->
<section id="alt-services" class="alt-services section">
  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="row gy-4">
      <?php
        // ดึงข้อมูลเฉพาะ category ที่ต้องการ (cat_page=1 และ cat_id=22) แล้ววนลูปแสดงผลงานทั้งหมด
        $query = "
          SELECT *
            FROM tbl_posts
            INNER JOIN tbl_categories
              ON tbl_categories.cat_id = tbl_posts.post_category_id
           WHERE tbl_categories.cat_page = 1
             AND tbl_categories.cat_id   = 22
             AND tbl_posts.post_status   = 'Published'
        ";
        $fetch_posts_data = mysqli_query($connection, $query);

        // ตัวแปรสำหรับจัดการค่า data-aos-delay
        $delay = 200;

        while ($Row = mysqli_fetch_assoc($fetch_posts_data)) {
          $the_post_id    = $Row['post_id'];
          $the_post_image = $Row['post_image'];

          // เลือกภาษาตาม $_SESSION['lang']
          $lang = $_SESSION['lang'] ?? 'th';
          switch ($lang) {
            case 'en':
              $the_post_title   = base64_decode($Row['post_title']);
              $the_post_content = base64_decode($Row['post_content']);
              break;
            case 'cn':
              $the_post_title   = base64_decode($Row['post_title_china']);
              $the_post_content = base64_decode($Row['post_content_china']);
              break;
            default:
              $the_post_title   = base64_decode($Row['post_title_thai']);
              $the_post_content = base64_decode($Row['post_content_thai']);
              break;
          }
          $post_link_url = $Row['post_link'];
      ?>
        <!-- ใส่คลาส d-flex ให้ col-lg-6 เพื่อให้คอลัมน์เป็น flex-container -->
        <div class="col-lg-6 d-flex" data-aos="zoom-in" data-aos-delay="<?php echo $delay; ?>">
          <!-- ใส่ h-100 ให้ service-item ให้ยืดความสูงเต็ม parent -->
          <div class="service-item position-relative h-50 d-flex flex-column">
            <!-- ส่วนรูปภาพ -->
            <div class="img mb-3">
              <a href="<?php echo $post_link_url; ?>">
              <img src="<?php echo "admin/post/" . $the_post_image; ?>" class="img-fluid" alt="">
              </a>
            </div>

            <!-- ส่วนรายละเอียด ใช้ flex-grow-1 เพื่อให้เบียดเต็มแนวตั้งหากเนื้อหายาวไม่เท่ากัน -->
            <div class="details flex-grow-1">
              <h3><?php echo $the_post_title; ?></h3>
              <p><?php echo $the_post_content; ?></p>
            </div>
          </div>
        </div><!-- End Service Item -->
      <?php
          // เพิ่มค่า delay ทีละ 100ms สำหรับ item ถัดไป
          $delay += 100;
        } // end while
      ?>
    </div>
  </div>
</section><!-- /Alt Services Section -->



  </main>

  <!-- Footer Start -->
  <?php include("./includes/footer.php") ?>
  <!-- Footer End -->