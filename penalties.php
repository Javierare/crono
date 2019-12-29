<?php 
session_start();

include 'idioma/es.php'; // Contiene los textos traducidos al español
include 'database.php';

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "penalties.php";

// Anade nueva penalización 
if(isset($_POST['addPen'])){
  $data['name'] = utf8_decode(mysqli_real_escape_string($db, $_POST['penaltyName']));
  $data['seconds'] = mysqli_real_escape_string($db, $_POST['penaltySeconds']);
  $data['description'] = utf8_decode(mysqli_real_escape_string($db, $_POST['penaltyDesc']));
  if(!insertar("penalties", $data)) 
      $errors[$p.'-penalizaciones'] = 'Error al crear penalizacion';
}

// Elimina penalización 
if(isset($_GET['delPen'])){
  if(!eliminar("penalties", mysqli_real_escape_string($db, $_GET['delPen']))) 
      $errors[$p.'-penalizaciones'] = 'Error al eliminar penalizacion';
}

// Pintar penalizaciones con botón de eliminar
function listar_penalizaciones($penalties){
    foreach($penalties as $penalty){
        echo '<div class="card border-left-primary shadow py-0">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-ls font-weight-bold text-primary text-uppercase mb-1">'.utf8_encode($penalty['name']).' ('.$penalty['seconds'].' sec)</div>
                            <div class="mb-0 font-weight-bold text-gray-800">
                                '.utf8_encode($penalty['description']).'
                            </div>
                        </div>
                    <div class="col-auto">
                        <a href="penalties.php?delPen='.$penalty['id'].'" class="btn btn-danger btn-circle btn-lg">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>'; 
    }
}

$title = "Crono Rally 2.0 - Penalizaciones"; // Título de la página
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
          <h1 class="h3 mb-4 text-gray-800">Penalizaciones</h1>

          <p>Desde esta ficha puedes crear las penalizaciones que luego utilizarás a la hora de crear un rally.</p>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">

              <!-- Penaltes Card -->
              <!-- Ficha donde crear y eliminar penalizaciones -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Crear penalización</h6>
                </div>
                <div class="card-body">
                  <?php if(isset($errors[$p.'-penalties'])) include "errors.php"; ?>
                  <form action="penalties.php" method="post">
                    <div class="form-group">
                        <label for="penaltyName">Penalización:</label>
                        <input type="name" class="form-control" placeholder="Penalty name" name="penaltyName" required>
                    </div>
                    <div class="form-group">
                        <label for="penaltySeconds">Tiempo en segundos:</label>
                        <input type="name" class="form-control" placeholder="Seconds" name="penaltySeconds" required pattern="[0-9.]+">
                    </div>
                    <div class="form-group">
                        <label for="penaltyDesc">Condición que genera la penalización:</label>
                        <input type="name" class="form-control" placeholder="Description" name="penaltyDesc" required>
                    </div>    
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary" name="addPen">Guardar</button>
                    </div>
                  </form>
                </div>
                <!-- End Basic Card -->
              </div>
              <!-- End penalizaciones Card -->
            
            </div>
            <!-- End Columna Izqda -->

            <!-- Columna Dcha -->
            <div class="col-lg-6">

            <?php if($penalties = consulta_multi("penalties")): ?>
              <!-- Penaltes Card -->
              <!-- Ficha donde crear y eliminar penalizaciones -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Lista de penalizaciones</h6>
                </div>
                <div class="card-body">
                  <?php listar_penalizaciones($penalties); ?>                    
                </div>
                <!-- End Basic Card -->
              </div>
              <!-- End penalizaciones Card -->
            <?php endif; ?>

            </div>
            <!-- End Columna Dcha -->

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