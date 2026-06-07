      </div><!-- /.container-fluid -->
      </div><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <footer class="main-footer">
        <strong><?= APP_NAME ?></strong> &mdash; Clinic Management System
        <div class="float-right d-none d-sm-inline-block">
          <b>Version</b> 1.0
        </div>
      </footer>
      <aside class="control-sidebar control-sidebar-dark"></aside>
      </div><!-- ./wrapper -->

      <!-- jQuery -->
      <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>

      <!-- Bootstrap 4 -->
      <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

      <!-- AdminLTE -->
      <script src="<?= BASE_URL ?>/public/assets/adminlte/dist/js/adminlte.min.js"></script>

      <!-- DataTables -->
      <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
      <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

      <!-- Chart.js -->
      <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/chart.js/Chart.min.js"></script>

      <!-- Auto-dismiss alerts -->
      <script>
        setTimeout(function() {
          $('.alert').fadeTo(500, 0).slideUp(500, function() {
            $(this).remove();
          });
        }, 5000);
      </script>

      <?php if (!empty($extraJs)) echo $extraJs; ?>
      </body>

      </html>