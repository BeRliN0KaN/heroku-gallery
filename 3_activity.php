<?php
include('./includes/header.php');
include('pagination.php');
?>

<script>
  function openpopup(url) {
    window.open(url, '', 'width=auto,height=auto,status,scrollbars,resizable,modal');
  }
</script>

<body>
  <div class="container-fluid service">
    <div class="container py-5">
      <div class="text-center mx-auto mb-5" style="max-width:900px;">
        <h2 class="display-5 mb-4 fw-bold"><?= constant('page_activity_1') ?></h2>

        <input
          type="text"
          id="searchInput"
          class="form-control my-3"
          placeholder="ค้นหากิจกรรม...">

        <p class="fs-5 mb-0"><?= constant('page_activity_2') ?></p>
      </div>
      <div class="row g-4 justify-content-left">

        <?php while ($Row = mysqli_fetch_array($nquery)): ?>
          <?php
          $id     = (int)$Row['activity_id'];
          $img    = htmlspecialchars($Row['activity_image']);
          $status = $Row['activity_status'];
          $date   = date("d-m-Y", strtotime($Row['activity_date']));
          switch ($_SESSION['lang'] ?? 'th') {
            case 'en':
              $title = base64_decode($Row['activity_title']);
              break;
            case 'cn':
              $title = base64_decode($Row['activity_title_china']);
              break;
            default:
              $title = base64_decode($Row['activity_title_thai']);
          }
          ?>
          <div class="col-md-6 col-lg-4 col-xl-3 d-flex">
            <div class="service-item text-center rounded-4 p-4 d-flex flex-column w-100 shadow-lg">
              <div class="blog-img overflow-hidden" style="position:relative;">
                <img src="admin/activity/<?= $img ?>"
                  class="img-fluid"
                  style="object-fit:cover;height:200px;width:100%;"
                  alt="<?= htmlspecialchars($title) ?>">
              </div>
              <div class="service-content flex-grow-1 d-flex flex-column">
                <h6 class="my-3 fw-bold"><?= htmlspecialchars($title) ?></h6>
                <p><i class="fa-solid fa-calendar-days"></i> <?= $date ?></p>

                <?php if ($status === 'Published'): ?>
                  <button class="btn btn-primary mt-auto"
                    onclick="openpopup('service-activity.php?lan=<?= $_SESSION['lang'] ?>&id=<?= $id ?>')">
                    ดูรายละเอียด
                  </button>
                <?php else: ?>
                  <label class="btn btn-outline-primary mt-auto">
                    อัปโหลดรูปเพื่อตรวจ
                    <input type="file" class="d-none upload-input" accept="image/*" data-id="<?= $id ?>">
                  </label>
                  <div id="preview-<?= $id ?>" class="mt-2"></div>
                  <div id="result-<?= $id ?>" class="fw-bold mt-1"></div>

                  <div class="gallery-images d-none" data-id="<?= $id ?>">
                    <?php
                    $gq = "SELECT image_name FROM tbl_activity_gallery WHERE activity_id={$id}";
                    $gres = mysqli_query($connection, $gq);
                    while ($gr = mysqli_fetch_assoc($gres)) {
                      echo '<img src="admin/activity_gallery/' . $gr['image_name'] . '" '
                        . 'class="gallery-img" data-name="' . $gr['image_name'] . '">';
                    }
                    ?>
                  </div>
                <?php endif; ?>

                <!-- ปุ่มแชร์ -->
                <button
                  type="button"
                  class="btn btn-outline-secondary mt-2 share-btn"
                  data-id="<?= $id ?>">
                  <i class="fa-solid fa-share-alt"></i> แชร์
                </button>

              </div>
            </div>
          </div>
        <?php endwhile; ?>

        <div id="pagination_controls"><?= $paginationCtrls ?></div>
      </div>
    </div>
  </div>


 <!-- face-api.js -->
<script src="/webGallery/admin/js/face-api.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const THRESHOLD = 0.35;

  // โหลดโมเดลให้ครบก่อนใช้งาน
  Promise.all([
    faceapi.nets.ssdMobilenetv1.loadFromUri('/webGallery/admin/models'),
    faceapi.nets.faceLandmark68Net.loadFromUri('/webGallery/admin/models'),
    faceapi.nets.faceRecognitionNet.loadFromUri('/webGallery/admin/models')
  ]).then(() => {

    document.querySelectorAll('.upload-input').forEach(input => {
      input.addEventListener('change', async e => {
        const file = e.target.files[0];
        const id   = e.target.dataset.id;
        const preview = document.getElementById('preview-' + id);
        const resultEl = document.getElementById('result-' + id);
        preview.innerHTML = '';
        resultEl.innerText = '';
        if (!file) return;

        // 1. แสดง preview
        const reader = new FileReader();
        reader.onload = ev => {
          preview.innerHTML = `<img src="${ev.target.result}" 
                                  style="max-width:150px;
                                         border:1px solid #ccc;
                                         border-radius:8px;">`;
        };
        reader.readAsDataURL(file);

        // 2. เริ่มจับเวลา (ms)
        const t0 = performance.now();

        // 3. ตรวจจับใบหน้าในภาพอัปโหลด
        const imgUp = await faceapi.bufferToImage(file);
        if (!imgUp.complete) {
          await new Promise(r => { imgUp.onload = r; imgUp.onerror = r; });
        }
        const detectionsAll = await faceapi.detectAllFaces(imgUp)
                                           .withFaceLandmarks()
                                           .withFaceDescriptors();

        // 4. ถ้าไม่เจอใบหน้าเดี่ยวชัดเจน → บันทึก false + return
        if (detectionsAll.length !== 1) {
          const durationErr = ((performance.now() - t0) / 1000).toFixed(3);
          // ส่ง log ว่า failed
          fetch('/webGallery/export_log.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              galleryId: id,
              processingTime: durationErr,
              result: false,
              matchedImage: '',
              descriptor: []
            })
          });
          resultEl.innerText = '❌ ภาพใบหน้าไม่ชัดเจน หรือเจอหลายใบหน้า';
          preview.innerHTML = '';
          return;
        }
        const detUp = detectionsAll[0];

        // 5. สร้าง descriptors จาก gallery images
        const labeledFD = [];
        const galleryEls = document.querySelectorAll(`.gallery-images[data-id='${id}'] .gallery-img`);
        for (const el of galleryEls) {
          if (!el.complete) {
            await new Promise(r => { el.onload = r; el.onerror = r; });
          }
          if (el.naturalWidth === 0) continue;
          const dets = await faceapi.detectAllFaces(el)
                                     .withFaceLandmarks()
                                     .withFaceDescriptors();
          dets.forEach((d, idx) => {
            labeledFD.push(new faceapi.LabeledFaceDescriptors(
              `${el.dataset.name}_${idx}`,
              [d.descriptor]
            ));
          });
        }

        // 6. จับคู่ใบหน้า
        const matcher = new faceapi.FaceMatcher(labeledFD, THRESHOLD);
        const best    = matcher.findBestMatch(detUp.descriptor);

        // 7. จบจับเวลา (s)
        const duration = ((performance.now() - t0) / 1000).toFixed(3);

        // 8. ประมวลผลผลลัพธ์
        const success      = best.label !== 'unknown';
        const matchedImage = success
          ? best.label.replace(/_[0-9]+$/, '')
          : '';
        const descriptorArr = Array.from(detUp.descriptor);

        // 9. ส่ง log ครบ 5 ฟิลด์
        fetch('/webGallery/export_log.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            galleryId:      id,
            processingTime: duration,
            result:         success,
            matchedImage:   matchedImage,
            descriptor:     descriptorArr
          })
        }).catch(err => console.error('export_log error:', err));

        // 10. แสดงผลบนหน้า
        if (success) {
          resultEl.innerText = '✔️ ผ่านการตรวจ';
          preview.innerHTML = '';
          const lang = '<?= $_SESSION['lang'] ?>';
          openpopup(`service-activity.php?lan=${lang}&id=${id}`);
        } else {
          resultEl.innerText = '❌ ไม่พบใน Gallery นี้';
          preview.innerHTML = '';
        }
      });
    });

  }).catch(err => {
    console.error('Error loading face-api models:', err);
  });
});
</script>



  <!-- สคริปต์ปุ่มแชร์ & ไฮไลต์ -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // คัดลอกลิงก์แชร์
      document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.id;
          const url = new URL(window.location.href);
          url.searchParams.set('highlight', id);
          navigator.clipboard.writeText(url.href)
            .then(() => alert('คัดลอกลิงก์แชร์สำเร็จ!'))
            .catch(() => alert('ไม่สามารถคัดลอกลิงก์ได้'));
        });
      });

      // ไฮไลต์เมื่อมีพารามิเตอร์ highlight
      const params = new URLSearchParams(window.location.search);
      const highlightId = params.get('highlight');
      if (highlightId) {
        const container = document.querySelector('.row.g-4.justify-content-left');
        const targetCol = container.querySelector(`.share-btn[data-id="${highlightId}"]`)
          ?.closest('.col-md-6.col-lg-4.col-xl-3');
        if (targetCol) {
          container.insertBefore(targetCol, container.firstChild);
          targetCol.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          targetCol.classList.add('border', 'border-warning', 'rounded');
        }
      }
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const searchInput = document.getElementById('searchInput');
      const container = document.querySelector('.row.g-4.justify-content-left');

      if (!searchInput || !container) return;

      searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const cols = Array.from(container.children)
          .filter(el => el.querySelector('.service-item'));
        const visible = [];

        cols.forEach(col => {
          const titleEl = col.querySelector('h6');
          const titleText = titleEl ? titleEl.textContent.toLowerCase() : '';
          if (filter === '' || titleText.includes(filter)) {
            col.style.display = '';
            visible.push(col);
          } else {
            col.style.display = 'none';
          }
        });

        // ถ้ามีการค้นหา และผลลัพธ์อย่างน้อยหนึ่งรายการ
        if (filter !== '' && visible.length) {
          container.insertBefore(visible[0], container.firstChild);
        }
      });
    });
  </script>


</body>
<?php include("./includes/footer.php") ?>