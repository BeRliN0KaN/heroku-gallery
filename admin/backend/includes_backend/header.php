<?php
ob_start();
include '../../includes/db.php';
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: ../index.php");
}

if (isset($_GET['lang'])) {
  $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
  $_SESSION['lang'] = 'en';
}
//echo $_SESSION['lang'];
include('lang_' . $_SESSION['lang'] . '.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <!-- <script src="./js/ckeditor.js"></script> -->
  <script src="../ckeditor/ckeditor.js"></script>
  <script src="https://www.gstatic.com/charts/loader.js">
  </script>


  <title> | GALLERY | </title>

  <!-- Favicon -->
  <!-- <link href="../images/logo/wisepaq.jpg" rel="icon"> -->
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


  <!-- Vendor CSS Files -->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- data table -->
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
  <script src="https://cdn.datatables.net/2.2.2/js/dataTables.jqueryui.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/dataTables.buttons.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.jqueryui.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.2/js/buttons.colVis.min.js"></script>

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.jqueryui.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.2/css/buttons.jqueryui.css">



  <!-- Template Main CSS File -->
  <link href="../css/style.css" rel="stylesheet">


</head>

<body>
  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="#" class="logo d-flex align-items-center" style="width: 11rem;">
        <span class="toggle-sidebar-btn fs-3 ps-1">GALLERY<sub class="fs-5" style="color:#578FCA;">User</sub></span>

      </a>
      <!-- <i class="bi bi-list toggle-sidebar-btn"></i> -->
    </div><!-- End Logo -->


    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">
        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0 " href="#" data-bs-toggle="dropdown">
            <div>
              <?php
              if ($_SESSION['user_image'] == "default.jpg") {
                echo "<img src='../images/img-icon/profile.webp' alt='' class='rounded-circle' style='width: 40px; height:50px;object-fit: cover;'>";
              } else {
                echo "<img src='../profile/{$_SESSION['user_image']}' alt='' class='rounded-circle' style='width: 40px; height:50px;object-fit: cover;'>";
              }
              ?>
              <!-- <i class="bi bi-person" style="font-size: 20px;"></i> -->
            </div>
            <span class="d-none d-md-block dropdown-toggle p-2 pe-4"><?php echo $_SESSION['username'] ?> </span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6><?php echo $_SESSION['firstname'] . " " . $_SESSION['lastname'] ?></h6>
              <span><?php echo $_SESSION['user_role']; ?></span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-center" href="../../index.php">
                <i class="bi bi-display"></i>
                <span>Website</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>


            <li>
              <a class="dropdown-item d-flex align-items-center" href="../includes_admin/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->
    

    


    <!-- Language Selector (Dropdown) -->
    <div class="text-box mx-lg-4 mt-2 mt-lg-0" id="dropdown">
      <span class="text-content">
        <img id="selected-flag" src=".././images/flag1/english.png" alt="EN Flag" class="lang-select">
        <span id="current-language">EN</span>
      </span>
      <i class="arrow"></i>
      <ul class="dropdown-menu-lang">
        <li data-lang="th" onclick="change_lang('th')">
          <img src=".././images/flag1/thailand.png" alt="TH Flag" class="lang-option">
          <span>Thailand</span>
        </li>
        <li data-lang="en" onclick="change_lang('en')">
          <img src=".././images/flag1/english.png" alt="EN Flag" class="lang-option">
          <span>English</span>
        </li>
        <li data-lang="cn" onclick="change_lang('cn')">
          <img src=".././images/flag1/china.png" alt="CN Flag" class="lang-option">
          <span>China</span>
        </li>
      </ul>
    </div>

    <script>
      $(document).ready(function() {
        $(".text-box").click(function() {
          const $dropdownMenu = $(".dropdown-menu-lang");
          const $arrow = $(".arrow");
          const $textBox = $(".text-box");

          if ($dropdownMenu.is(":visible")) {
            $dropdownMenu.slideUp("fast");
            $arrow.css("transform", "rotate(-45deg)");
            $textBox.css("border", "2px solid rgb(214, 208, 208)");
          } else {
            $dropdownMenu.slideDown("fast");
            $arrow.css("transform", "rotate(135deg)");
            $textBox.css("border", "2px solid rgb(31, 30, 30)");
          }
        });

        $(".dropdown-menu-lang li").click(function(e) {
          e.stopPropagation(); // ป้องกันไม่ให้ dropdown ปิดตัวเองก่อนเวลา
          let lang = $(this).attr("data-lang");
          let flagSrc = $(this).find("img").attr("src");

          $("#current-language").text(lang.toUpperCase());
          $("#selected-flag").attr("src", flagSrc);

          $(".dropdown-menu-lang").slideUp("fast");
          $(".arrow").css("transform", "rotate(-45deg)");
          $(".text-box").css("border", "2px solid rgb(214, 208, 208)");

          change_lang(lang);
        });
      });

      function change_lang(value) {
        localStorage.setItem("lang", value);
        window.location.replace("?lang=" + value);
      }

      (function() {
        // อ่านค่าภาษาใน PHP session แล้วตั้งค่าเริ่มต้น
        const lang = "<?php echo $_SESSION['lang']; ?>";
        const currentLanguage = document.getElementById("current-language");
        const selectedFlag = document.getElementById("selected-flag");

        switch (lang) {
          case "en":
            currentLanguage.textContent = "EN";
            selectedFlag.src = ".././images/flag1/english.png";
            selectedFlag.alt = "EN Flag";
            break;
          case "cn":
            currentLanguage.textContent = "CN";
            selectedFlag.src = ".././images/flag1/china.png";
            selectedFlag.alt = "CN Flag";
            break;
          default:
            currentLanguage.textContent = "TH";
            selectedFlag.src = ".././images/flag1/thailand.png";
            selectedFlag.alt = "TH Flag";
            break;
        }
      })();
    </script>


  </header><!-- End Header -->
</body>