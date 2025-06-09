  <!-- ======= Footer ======= -->
  <!-- <footer id="footer" class="footer">
    <div class="copyright">
      Copyright &copy; WISEPAQ <?php echo date("Y"); ?>
    </div>
  </footer> -->
  <!-- ========= End Footer ========== -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/chart.js/chart.umd.js"></script>
  <script src="../vendor/echarts/echarts.min.js"></script>
  <script src="../vendor/quill/quill.js"></script>
  <script src="../vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../vendor/tinymce/tinymce.min.js"></script>
  <script src="../vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../js/main.js"></script>

  <!-- CKEditor 5 JavaScript -->
  <script>
      ClassicEditor
          .create(document.querySelector('#editor')).then(editor => {
              editor.editing.view.change(writer => {
                  writer.setStyle('min-height', '200px', editor.editing.view.document.getRoot());
              });
              window.editor = editor;
          })
          .catch(error => {
              console.error(error);
          });
  </script>
  <script>
      ClassicEditor
          .create(document.querySelector('#editor2')).then(editor2 => {
              editor2.editing.view.change(writer => {
                  writer.setStyle('min-height', '200px', editor2.editing.view.document.getRoot());
              });
              window.editor2 = editor2;
          })
          .catch(error => {
              console.error(error);
          });
  </script>

  <script>
      function selectAll(source) {
          let checkboxes = document.querySelectorAll("tbody input[type='checkbox']");
          checkboxes.forEach(checkbox => {
              checkbox.checked = source.checked;
          });
      }
  </script>

  </body>

  </html>