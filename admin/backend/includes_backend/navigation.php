<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link " href="index.php">
        <i class="bi bi-grid"></i>
        <span><?= constant('page_index_Navi_1') ?></span>
      </a>
    </li>
    <!-- End Dashboard Nav -->

    <!-- Start Categories -->
    <?php if ($_SESSION['user_role'] == "admin") { ?>
    <li class="nav-item">
      <a class="nav-link collapsed" href="categories.php">
        <i class="bi bi-folder"></i>
        <span><?= constant('page_index_Navi_2') ?></span>
      </a>
    </li>
    <?php } ?>
    <!-- End Categories -->


    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#activity-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-clipboard"></i></i><span><?= constant('page_index_Navi_3') ?></span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="activity-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="activity.php?source=add_activity">
            <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_3.1') ?></span>
          </a>
        </li>
        <li>
          <a href="activity.php">
            <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_3.2') ?></span>
          </a>
        </li>
      </ul>
    </li>
    <!-- End Components Nav -->

    <?php if ($_SESSION['user_role'] == "admin") { ?>
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#post" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark-text"></i><span><?= constant('page_index_Navi_4') ?></span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="post" class="nav-content collapse " data-bs-parent="#sidebar-nav">
        <li>
          <a href="posts.php?source=add_post">
            <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_4.1') ?></span>
          </a>
        </li>
        <li>
          <a href="posts.php">
            <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_4.2') ?></span>
          </a>
        </li>
      </ul>
    </li>
    <?php } ?>
    <!-- End Components Nav -->

    <?php if ($_SESSION['user_role'] == "admin") { ?>
      <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#user-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people"></i><span><?= constant('page_index_Navi_5') ?></span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="user-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
          <li>
            <a href="users.php?source=add_user">
              <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_5.1') ?></span>
            </a>
          </li>
          <li>
            <a href="users.php">
              <i class="bi bi-circle"></i><span><?= constant('page_index_Navi_5.2') ?></span>
            </a>
          </li>
        </ul>
      </li>
      <!-- End Components Nav -->
    <?php } ?>

    <li class="nav-item">
      <a class="nav-link " href="gallery.php">
        <i class="bi bi-images"></i>
        <span><?= constant('page_index_Navi_6') ?></span>
      </a>
    </li>


  </ul>

</aside>