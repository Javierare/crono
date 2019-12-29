<?php 
session_start();

include 'idioma/es.php'; // Contiene los textos traducidos al español
include 'database.php';

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "admin.php";


$title = "Panel de administración"; // Título de la página
include ('header.php');
?>

<body id="page-top">
<!-- Datepicker -->

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <?php include('sidebar.php'); ?> 
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <?php include('topbar.php'); ?>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <h1 class="h3 mb-4 text-gray-800">Panel de administración</h1>

          <p>Desde este panel se administran los rallies.</p>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">

            </div>
            <!-- End Columna Izqda -->
          </div>
          <!-- End Row -->

        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <?php include('footer.php'); ?>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

</body>

</html>