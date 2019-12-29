<?php 
session_start();

include 'idioma/es.php'; // Contiene los textos traducidos al español
include 'database.php';

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "categories.php";

// Anade nueva categoria 
if(isset($_POST['addCat'])){
    $data['name'] = utf8_decode(mysqli_real_escape_string($db, $_POST['categoryName']));
    $data['description'] = utf8_decode(mysqli_real_escape_string($db, $_POST['categoryDesc']));
    if(!insertar("categories", $data)) 
        $errors[$p.'-categories'] = 'Error al crear categoria';
}

// Elimina categoria 
if(isset($_GET['delCat'])){
    if(!eliminar("categories", mysqli_real_escape_string($db, $_GET['delCat']))) 
        $errors[$p.'-categories'] = 'Error al eliminar categoria';
}

// Pintar categorías con botón de eliminar
function listar_categorias($categories){
    foreach($categories as $category){
        echo '<div class="card border-left-primary shadow py-0">
          <div class="card-body">
              <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                      <div class="text-ls font-weight-bold text-primary text-uppercase mb-1">'.utf8_encode($category['name']).'</div>
                          <div class="mb-0 font-weight-bold text-gray-800">
                              '.utf8_encode($category['description']).'
                          </div>
                      </div>
                  <div class="col-auto">
                      <a href="categories.php?delCat='.$category['id'].'" class="btn btn-danger btn-circle btn-lg">
                          <i class="fas fa-trash"></i>
                      </a>
                  </div>
              </div>
          </div>
      </div>'; 
    }
}

$title = "Crono Rally 2.0 - Categorías"; // Título de la página
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
          <h1 class="h3 mb-4 text-gray-800">Categorías</h1>

          <p>Desde esta ficha puedes crear categorías, por ejemplo WRC y WRC2. 
          Al crear un nuevo rally deberàs elegir las categorías permitidas para ese rally de entre todas las existentes en este listado.</p>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">

              <!-- Categories Card -->
              <!-- Ficha donde crear y eliminar categorías -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Crear nueva categoría</h6>
                </div>
                <div class="card-body">
                  <?php if(isset($errors[$p.'-categorias'])) include "errors.php"; ?>
                  <form action="categories.php" method="post">
                    <div class="form-group">
                          <label for="categoryName">Nombre</label>
                          <input type="name" class="form-control" placeholder="Category name" name="categoryName" required>
                    </div>
                    <div class="form-group">
                          <label for="categoryDesc">Descripción</label>
                          <input type="name" class="form-control" placeholder="Category description" name="categoryDesc" required>
                    </div>
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary" name="addCat">Guardar</button>
                    </div>
                  </form>
                                     
                </div>
                <!-- End Basic Card -->
              </div>
              <!-- End Categories Card -->

            </div>
            <!-- End Columna Izqda -->

            <!-- Columna Dcha -->
            <div class="col-lg-6">

            <?php if($categories = consulta_multi("categories")): ?>
              <!-- Penaltes Card -->
              <!-- Ficha donde crear y eliminar penalizaciones -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Lista de categorias</h6>
                </div>
                <div class="card-body">
                  <?php listar_categorias($categories); ?>                    
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