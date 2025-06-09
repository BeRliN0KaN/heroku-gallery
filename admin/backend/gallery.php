<!-- ======= Header ======= -->
<?php include "includes_backend/header.php"; ?>
<!-- End Header -->

<!-- ======= Sidebar ======= -->
<?php include "includes_backend/navigation.php"; ?>
<!-- End Sidebar-->

<?php include "pagination.php"; ?>

<?php
// ดึงรูปใบหน้าที่สมัครจาก tbl_users
$uid = $_SESSION['user_id'];
$res = mysqli_query($connection, "SELECT user_face_image FROM tbl_users WHERE user_id = {$uid}");
$usr = mysqli_fetch_assoc($res);
$registeredFace = $usr['user_face_image'];
?>
<script>
  // URL รูปใบหน้าที่สมัคร
  const REGISTERED_FACE_URL = `../../admin/face_uploads/<?= $registeredFace ?>`;
</script>

<script>
  function openpopup(url) {
    window.open(url, '', 'width=auto,height=auto,status,scrollbars,resizable,modal');
  }
</script>

<body>
  <br><br>
  <div class="container-fluid service">
    <div class="container py-5">
      <div class="text-center mx-auto mb-5" style="max-width:900px;"><br><br>
        <h2 class="display-5 mb-4 fw-bold"><?= constant('page_activity_1') ?></h2>

        <input
          type="text"
          id="searchInput"
          class="form-control my-3"
          placeholder="<?= constant('page_gallery_2') ?>">

        <p class="fs-5 mb-0"><?= constant('page_activity_2') ?></p>
      </div>
      <div class="row g-4 justify-content-left">

        <?php while ($Row = mysqli_fetch_array($nquery)): ?>
          <?php
          $id               = (int)$Row['activity_id'];
          $img              = htmlspecialchars($Row['activity_image']);
          $status           = $Row['activity_status'];
          $date             = date("d-m-Y", strtotime($Row['activity_date']));
          $correct_plain_pw = $Row['activity_password_plain'] ?? '';
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
                <img src="../../admin/activity/<?= $img ?>"
                  class="img-fluid"
                  style="object-fit:cover;height:200px;width:100%;"
                  alt="<?= htmlspecialchars($title) ?>">
              </div>
              <div class="service-content flex-grow-1 d-flex flex-column">
                <h6 class="my-3 fw-bold"><?= htmlspecialchars($title) ?></h6>
                <p><i class="fa-solid fa-calendar-days"></i> <?= $date ?></p>

                <?php if ($status === 'Published'): ?>
                  <!-- Published -->
                  <button class="btn btn-primary mt-auto"
                    onclick="openpopup('service-activity.php?lan=<?= $_SESSION['lang'] ?>&id=<?= $id ?>')">
                    <?= constant('page_gallery_5') ?>
                  </button>
                <?php else: ?>
                  <!-- Locked: password + face-check -->
                  <div class="mb-2 d-flex align-items-center">
                    <input type="password"
                      id="pw_input_<?= $id ?>"
                      class="form-control form-control-sm me-2"
                      placeholder="<?= constant('page_gallery_3') ?>"
                      style="max-width:200px;">
                    <button class="btn btn-sm btn-primary btn-verify"
                      data-id="<?= $id ?>"
                      data-correct="<?= htmlspecialchars($correct_plain_pw) ?>">
                      <?= constant('page_gallery_4') ?>
                    </button>
                  </div>
                  <div id="pw_error_<?= $id ?>" class="text-danger mb-2" style="font-size:0.9rem;"></div>

                  <button class="btn btn-outline-success mt-auto btn-check-face" data-id="<?= $id ?>">
                    <?= constant('page_gallery_6') ?>
                  </button>
                  <div id="face-result-<?= $id ?>" class="fw-bold mt-2"></div>

                  <div class="gallery-images d-none" data-id="<?= $id ?>">
                    <?php
                    $gq = "SELECT image_name FROM tbl_activity_gallery WHERE activity_id={$id}";
                    $gres = mysqli_query($connection, $gq);
                    while ($gr = mysqli_fetch_assoc($gres)) {
                      echo '<img src="../../admin/activity_gallery/' . $gr['image_name'] . '" '
                        .  'class="gallery-img" data-name="' . $gr['image_name'] . '">';
                    }
                    ?>
                  </div>
                <?php endif; ?>

                <!-- Share -->
                <button type="button"
                  class="btn btn-outline-secondary mt-2 share-btn"
                  data-id="<?= $id ?>">
                  <?= constant('page_gallery_7') ?> <i class="bi bi-share"></i>
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
  <script src="../../../webGallery/admin/js/face-api.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const THRESHOLD = 0.35;

      Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri('../../../webGallery/admin/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('../../../webGallery/admin/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('../../../webGallery/admin/models')
      ]).then(async () => {
        // Prepare registered-face descriptor
        let regDescriptor = null;
        try {
          const imgReg = await faceapi.fetchImage(REGISTERED_FACE_URL);
          const dets = await faceapi.detectAllFaces(imgReg)
            .withFaceLandmarks()
            .withFaceDescriptors();
          if (dets.length === 1) regDescriptor = dets[0].descriptor;
        } catch (e) {
          console.error('Error loading registered face:', e);
        }

        // Face-check button
        document.querySelectorAll('.btn-check-face').forEach(btn => {
          btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const resultEl = document.getElementById('face-result-' + id);
            resultEl.innerText = '';
            if (!regDescriptor) {
              resultEl.innerText = '<?= constant('page_noti_gallery_3') ?>';
              return;
            }
            const labels = [];
            document.querySelectorAll(`.gallery-images[data-id='${id}'] .gallery-img`)
              .forEach(el => labels.push(el));
            if (!labels.length) {
              resultEl.innerText = '<?= constant('page_noti_gallery_3') ?>';
              return;
            }
            // build descriptors
            const descriptors = [];
            for (const el of document.querySelectorAll(`.gallery-images[data-id='${id}'] .gallery-img`)) {
              await new Promise(r => {
                if (!el.complete) el.onload = el.onerror = r;
                else r();
              });
              const det = await faceapi.detectSingleFace(el)
                .withFaceLandmarks()
                .withFaceDescriptor();
              if (det) descriptors.push(new faceapi.LabeledFaceDescriptors(el.dataset.name, [det.descriptor]));
            }
            if (!descriptors.length) {
              resultEl.innerText = '<?= constant('page_noti_gallery_3') ?>';
              return;
            }
            const matcher = new faceapi.FaceMatcher(descriptors, THRESHOLD);
            const best = matcher.findBestMatch(regDescriptor);
            if (best.label !== 'unknown') {
              resultEl.innerText = '<?= constant('page_noti_gallery_5') ?>';
              openpopup(`service-activity.php?lan=<?= $_SESSION['lang'] ?>&id=${id}`);
            } else {
              resultEl.innerText = '<?= constant('page_noti_gallery_4') ?>';
            }
          });
        });
      }).catch(err => console.error('Error loading face-api models:', err));
    });
  </script>

  <!-- Password verify -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.btn-verify').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.id;
          const correct = btn.dataset.correct;
          const inputEl = document.getElementById('pw_input_' + id);
          const errorEl = document.getElementById('pw_error_' + id);
          const entered = inputEl.value.trim();
          if (!entered) {
            errorEl.innerText = '<?= constant('page_noti_gallery_1') ?>';
            return;
          }
          if (entered === correct) {
            errorEl.innerText = '';
            openpopup(`service-activity.php?lan=<?= $_SESSION['lang'] ?>&id=${id}`);
          } else {
            errorEl.innerText = '<?= constant('page_noti_gallery_2') ?>';
          }
          inputEl.value = '';
        });
      });
    });
  </script>

  <!-- Share & highlight -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Copy link
      document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.id;
          const url = new URL(window.location.href);
          url.searchParams.set('highlight', id);
          navigator.clipboard.writeText(url.href)
            .then(() => alert('<?= constant('page_noti_gallery_6') ?>'))
            .catch(() => alert('<?= constant('page_noti_gallery_7') ?>'));
        });
      });

      // Highlight card
      const params = new URLSearchParams(window.location.search);
      const highlightId = params.get('highlight');
      if (highlightId) {
        const container = document.querySelector('.row.g-4.justify-content-left');
        const target = container.querySelector(`.share-btn[data-id="${highlightId}"]`)
          ?.closest('.col-md-6.col-lg-4.col-xl-3');
        if (target) {
          container.insertBefore(target, container.firstChild);
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          target.classList.add('border', 'border-warning', 'rounded');
        }
      }
    });
  </script>

  <!-- Search filter -->
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

<?php include "includes_backend/footer.php"; ?>