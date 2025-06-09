<?php
include "includes_backend/header.php";
include "includes_backend/navigation.php";
?>

<!-- Login Form -->
<main>

  <div class="container">

    <section class="section register  d-flex flex-column align-items-center justify-content-center py-0">

      <div class="container">

        <div class="row justify-content-center">
          <div class="col-lg-6 col-md-6 d-flex flex-column align-items-center justify-content-center">
      

            <div class="card mb-3 w-75 pb-4" style="margin-top: 7rem;">

              <div class="card-body ">

                <?php
                if (isset($_GET['source'])) {
                  $source = $_GET['source'];
                } else {
                  $source = "";
                }
                switch ($source) {
                  case 'add_user':
                    include "./includes_backend/add_user.php";
                    break;
                  case 'edit_user':
                    include "./includes_backend/edit_user.php";
                    break;
                  default:
                  header("Location: view_all_users.php");
                    break;
                }
                ?>
              </div>
            </div>


          </div>
        </div>
      </div>

    </section>

  </div>
</main><!-- End #main -->

<?php include "includes_backend/footer.php" ?>