  <!-- Login Form -->
  <main>
    <div class="container">

      <section class="section register  d-flex flex-column align-items-center justify-content-center">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center">
                <div class="logo d-flex align-items-center w-auto">
                  <span class="d-none text-center d-lg-block">Login</span>
                </div>
              </div><!-- End Logo -->

              <div class="card mb-3 w-75 pb-4">

                <div class="card-body ">

                  <div class="py-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Gallery</h5>
                    <p class="text-center small">Enter your username & password to login</p>
                  </div>

                  <form class="row g-3 needs-validation" action="includes_admin/login.php" method="POST">

                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <input type="text" name="username" class="form-control" id="yourUsername" placeholder="Enter Username" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" placeholder="Enter Password" id="yourPassword" required>
                    </div>

                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit" name="login">Login</button>
                    </div>
                  </form>

                  <div class="text-center">
                    <a>Already Have Account? </a><a href="registration.php" class="small text-decoration-underline">Register</a>
                  </div>

                </div>
              </div>


            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->