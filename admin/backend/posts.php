<?php
include "includes_backend/header.php";
include "includes_backend/navigation.php";
?>

<main id="main" class="main">
<div class="pagetitle">
        <h1>View All Post</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item "><a href="posts.php">View All Post</a></li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <div class="card pt-5">
        <div class="card-body">
            <?php
            if (isset($_GET['source'])) {
                $source = $_GET['source'];
            } else {
                $source = "";
            }
            switch ($source) {
                case 'add_post':
                    include "./includes_backend/add_post.php";
                    break;
                case 'edit_post':
                    include "./includes_backend/edit_post.php";
                    break;
                default:
                    include "./includes_backend/view_all_posts.php";
                    break;
            }
            ?>
        </div>
    </div>
    </div>
</main>

<?php include "includes_backend/footer.php" ?>