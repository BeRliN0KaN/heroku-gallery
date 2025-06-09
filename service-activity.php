<?php include("./includes/db.php") ?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Details & Gallery</title>

  <!-- โหลด Bootstrap 5.3 จาก CDN เพื่อใช้ UI -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 20px;
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    #myBtn {
      display: none;
      position: fixed;
      bottom: 20px;
      right: 30px;
      z-index: 99;
      font-size: 18px;
      border: none;
      outline: none;
      background-color: red;
      color: white;
      cursor: pointer;
      padding: 15px;
      border-radius: 4px;
    }
    #myBtn:hover { background-color: #555; }

    .gallery-img {
      width: 100%;
      height: 250px;
      object-fit: contain;
      border-radius: 8px;
      background-color: #f8f9fa;
      transition: transform 0.3s ease;
      cursor: pointer;
      -webkit-user-drag: none;
    }

    .gallery-item p { font-size: 16px; color: #000; }

    /* ปุ่มเลื่อนภาพ ชิดขอบ และจางลงเมื่อไม่ได้โฟกัส */
    .modal-body {
      position: relative;
    }
    .modal-body .nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0,0,0,0.4);
      border: none;
      color: #fff;
      font-size: 2rem;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      z-index: 10;
      cursor: pointer;
      opacity: 0.3;
      transition: opacity 0.3s ease;
    }
    /* ชิดขอบมากขึ้น */
    .modal-body .prev-btn { left: 0.5rem; }
    .modal-body .next-btn { right: 0.5rem; }
    /* เมื่อชี้ที่ปุ่ม ทำให้ความเข้มขึ้น */
    .modal-body .nav-btn:hover {
      opacity: 1;
    }
  </style>
</head>
<body class="service-details-page" onblur="self.close();">
  <a href="JavaScript:window.close()">Close[X]</a>
  <button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>

  <script>
    let mybutton = document.getElementById("myBtn");
    window.onscroll = scrollFunction;
    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        mybutton.style.display = "block";
      } else {
        mybutton.style.display = "none";
      }
    }
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
  </script>

  <!-- เนื้อหาหลักของกิจกรรม -->
  <section id="service-details" class="service-details section">
    <div class="container">
      <div class="row">
        <?php
          $the_activity_id = $_GET['id'];
          $lan = $_GET['lan'];
          $query = "SELECT * FROM tbl_activity WHERE activity_id = $the_activity_id AND activity_status IN ('Published','Lock') ORDER BY activity_id DESC";
          $fetch_posts_data = mysqli_query($connection, $query);
          while ($Row = mysqli_fetch_assoc($fetch_posts_data)) {
            if ($lan == 'en') {
              $the_activity_title = base64_decode($Row['activity_title']);
              $the_activity_content = base64_decode($Row['activity_content']);
            } elseif ($lan == 'th') {
              $the_activity_title = base64_decode($Row['activity_title_thai']);
              $the_activity_content = base64_decode($Row['activity_content_thai']);
            } else {
              $the_activity_title = base64_decode($Row['activity_title_china']);
              $the_activity_content = base64_decode($Row['activity_content_china']);
            }
        ?>
        <div class="rounded-4 service-item text-center p-4 flex-column align-items-center mt-5">
          <h3 class="mb-3"><?php echo htmlspecialchars($the_activity_title); ?></h3>
          <div class="blog-item">
            <p style="width:100%">
              <?php echo $the_activity_content ?>
            </p>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- แสดงรูป Gallery -->
  <section id="service-gallery" class="service-gallery section py-5">
    <div class="container">
      <h2 class="text-center mb-4">Gallery</h2>
      <div class="row">
        <?php
          if (isset($_GET['id'])) {
            $the_activity_id = intval($_GET['id']);
            $query = "SELECT * FROM tbl_activity_gallery WHERE activity_id = $the_activity_id";
            $result = mysqli_query($connection, $query);
            if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                $gallery_image = htmlspecialchars($row['image_name']);
                echo "
                <div class='col-3 text-center mb-4'>
                  <div class='gallery-item'>
                    <img src='admin/activity_gallery/$gallery_image' class='img-fluid gallery-img' alt='Gallery Image'>
                  </div>
                </div>";
              }
            } else {
              echo "<p class='text-center'>No images found in the gallery.</p>";
            }
          } else {
            echo "<p class='text-center'>Invalid activity ID.</p>";
          }
        ?>
      </div>
    </div>
  </section>

  <!-- Bootstrap modal for enlarged image -->
  <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-body p-0">
          <!-- ปุ่ม Previous -->
          <button class="nav-btn prev-btn" id="modalPrevBtn">&#10094;</button>
          <!-- ภาพขยาย -->
          <img src="" id="modalImage" class="img-fluid w-100" alt="Expanded Image" draggable="false" style="-webkit-user-drag: none; user-select: none;">
          <!-- ปุ่ม Next -->
          <button class="nav-btn next-btn" id="modalNextBtn">&#10095;</button>
        </div>
      </div>
    </div>
  </div>

  <!-- include Bootstrap JS bundle for modal functionality -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // เก็บ list ของทุกภาพใน gallery
    const galleryImgs = Array.from(document.querySelectorAll('.gallery-img'));
    let currentIndex = 0;
    const modalImage = document.getElementById('modalImage');
    const galleryModal = new bootstrap.Modal(document.getElementById('galleryModal'));
    const prevBtn = document.getElementById('modalPrevBtn');
    const nextBtn = document.getElementById('modalNextBtn');

    function showModalAt(index) {
      currentIndex = (index + galleryImgs.length) % galleryImgs.length;
      modalImage.src = galleryImgs[currentIndex].src;
      galleryModal.show();
    }

    galleryImgs.forEach((img, idx) => {
      img.addEventListener('click', () => {
        showModalAt(idx);
      });
    });

    prevBtn.addEventListener('click', () => {
      showModalAt(currentIndex - 1);
    });

    nextBtn.addEventListener('click', () => {
      showModalAt(currentIndex + 1);
    });

    // ป้องกันคลิกขวา & dragstart บนทั้ง gallery-img และ modalImage
    document.addEventListener('contextmenu', function(e) {
      if (e.target.classList.contains('gallery-img') || e.target.id === 'modalImage') {
        e.preventDefault();
      }
    });
    document.querySelectorAll('.gallery-img, #modalImage').forEach(el => {
      el.addEventListener('dragstart', e => e.preventDefault());
    });

    // บล็อกคีย์ลัดและการ copy เมื่อ modal เปิด
    document.addEventListener('keydown', function(e) {
      const modalOpen = document.getElementById('galleryModal').classList.contains('show');
      if (!modalOpen) return;
      // Ctrl+S, Ctrl+U, Ctrl+C, Ctrl+Shift+I, PrintScreen, Windows+Shift+S
      if (
        (e.ctrlKey && (['s','u','c'].includes(e.key.toLowerCase()) || (e.shiftKey && e.key === 'I'))) ||
        e.key === 'PrintScreen' ||
        (e.metaKey && e.shiftKey && e.key.toLowerCase() === 's')
      ) {
        e.preventDefault();
      }
    });
    document.addEventListener('copy', function(e) {
      if (document.getElementById('galleryModal').classList.contains('show')) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
