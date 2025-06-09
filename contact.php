    <!-- Google reCAPTCHA CDN -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php include("./includes/header.php") ?>

    <!-- Contact Section -->
    <section id="contact" class="contact section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p class="mb-5 fs-5"><?php echo constant('page_contact_1') ?></p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row g-4 g-lg-5">
          <div class="col-lg-5">
            <div class="info-box" data-aos="fade-up" data-aos-delay="200">
              <h3><?php echo constant('page_contact_2') ?></h3>
              <!-- <p >Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Vestibulum ante ipsum primis.</p> -->

              <div class="info-item" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box">
                  <i class="bi bi-geo-alt"></i>
                </div>
                <div class="content">
                  <h4><?php echo constant('page_footer_5') ?></h4>
                  <p><?php echo constant('address') ?></p>
                </div>
              </div>

              <div class="info-item" data-aos="fade-up" data-aos-delay="400">
                <div class="icon-box">
                  <i class="bi bi-telephone"></i>
                </div>
                <div class="content">
                  <h4><?php echo constant('page_contact_4') ?></h4>
                  <p><?php echo constant('page_footer_3') ?></p>
                  <p><?php echo constant('page_footer_3.1') ?></p>
                </div>
              </div>

              <div class="info-item" data-aos="fade-up" data-aos-delay="500">
                <div class="icon-box">
                  <i class="bi bi-envelope"></i>
                </div>
                <div class="content">
                  <h4><?php echo constant('page_contact_5') ?></h4>
                  <p><?php echo constant('page_footer_4') ?></p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="contact-form" data-aos="fade-up" data-aos-delay="300">
              <h3><?php echo constant('page_contact_6') ?></h3>
              <!-- <p>Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Vestibulum ante ipsum primis.</p> -->

              <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                <div class="row gy-4">

                  <div class="col-md-6">
                    <input type="text" name="name" class="form-control" placeholder="<?php echo constant('page_contact_7') ?>" required="">
                  </div>

                  <div class="col-md-6 ">
                    <input type="email" class="form-control" name="email" placeholder="<?php echo constant('page_contact_8') ?>" required="">
                  </div>

                  <div class="col-12">
                    <input type="text" class="form-control" name="subject" placeholder="<?php echo constant('page_contact_9') ?>" required="">
                  </div>

                  <div class="col-12">
                    <textarea class="form-control" name="message" rows="6" placeholder="<?php echo constant('page_contact_10') ?>" required=""></textarea>
                  </div>

                  <div class="col-12 text-center">
                    <div class="loading">Loading</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Your message has been sent. Thank you!</div>
                    <button type="submit" class="btn"><?php echo constant('page_contact_11') ?></button>
                  </div>

                </div>
              </form>
            </div>
          </div>

          <br>

          <!-- <div class="col-lg-12 wow fadeInUp" data-wow-delay="0.5s">
            <iframe class="rounded"
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3876.060944282686!2d100.55544057485523!3d13.714758686673306!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e2a1e14e5f1c45%3A0x96187e02d34a4e78!2sWisepaq%20Business%20Solutions%20Provider%20Co.%2CLtd.!5e0!3m2!1sen!2sth!4v1734071332417!5m2!1sen!2sth" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              frameborder="0" allowfullscreen="" aria-hidden="false" tabindex="0"></iframe>
          </div> -->
        </div>
      </div>

    </section><!-- /Contact Section -->



    <!-- Start Footer -->
    <?php include("./includes/footer.php") ?>
    <!-- End Footer -->