<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a href='../index.php'>Website</a></li>
                <li><a href='index.php'>Log in</a></li>
                <li><a href='./backend/posts.php'>Post</a></li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <div class="logo d-flex align-items-center" style="width: 11rem;">
            <span class="d-none d-lg-block">GALLERY</span>
            <sub class="fs-5" style="color:#578FCA;">Login</sub>
        </div>
    </div><!-- End Logo -->


    <nav class="header-nav ms-auto me-5">
        <ul class="d-flex align-items-center">
            <li class="nav-item dropdown pe-3"><a href='../index.php' style="text-decoration: none; color:#074799;">Website</a></li>
            <?php
    // ตรวจสอบว่า URL มี 'post.php' หรือ 'activity.php' หรือไม่
    $is_post_page = strpos($_SERVER['REQUEST_URI'], 'post.php') !== false;
    $is_activity_page = strpos($_SERVER['REQUEST_URI'], 'activity.php') !== false;
?>

<?php if ($is_post_page || $is_activity_page): ?>
      <li class="nav-item dropdown pe-3">
        <?php if ($is_post_page): ?>
            <a href="./backend/posts.php" style="text-decoration: none; color:#074799;">Post</a>
        <?php elseif ($is_activity_page): ?>
            <a href='./backend/activity.php' style="text-decoration: none; color:#074799;">Activity</a>
        <?php endif; ?>
    <?php endif; ?>
    </li>
    <?php if(isset($_SESSION['username'])){ ?>
    <li class="nav-item dropdown pe-3">
        <a href="./backend/index.php" style="text-decoration: none; color:#074799;">Dashboard</a>
    </li>
    <?php } ?>
        </ul>
    </nav><!-- End Icons Navigation -->
</header>
<!-- End Header -->