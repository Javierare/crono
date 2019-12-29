<?php 
session_start();

include 'idioma/es.php'; // Contiene los textos traducidos al español
include 'database.php';
include 'function_common.php';

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "new_rally.php";

if(!$categories = consulta_multi("categories")) $errors[$p.'-nuevoRally'] = 'Antes de crear un rally debe crear al menos una categoría';

// Añade nuevo rally
if(isset($_POST['add_rally'])){
    // Comprobar si el rally está abierto
    if(isset($_POST['signupOpen'])) $open = 1;
    else $open = 0;
    // Categorías: comprueba cuáles están seleccionadas
    $selectedCats = array();
    foreach($categories as $category){
        $catName = $category['name'];
        if(isset($_POST[$catName])) $selectedCats[] = $catName;
    }
    // Si ninguna está seleccionada se muestra un error
    if(empty($selectedCats)) $errors[$p.'-nuevoRally'] = 'Para crear un nuevo rally debe seleccionar al menos una categoría';
    // Comprueba que el nombre del rally no exista ya 
    if(consulta("rallies", 'name="'.mysqli_real_escape_string($db, $_POST['name']).'"')) $errors[$p.'-nuevoRally'] = 'El nombre del rally ya existe.';
    
    if(count($errors)==0) {
        $data = array();
        $data['name'] = mysqli_real_escape_string($db, $_POST['name']);
        $data['file'] = '';
        $data['date'] = convertDate($_POST['date']);
        $data['open'] = $open;
        $data['categories'] = implode(';', $selectedCats);
        $data['deputy'] = 0;
        if(!$r = insertar("rallies", $data)) $errors[$p.'-nuevoRally'] = 'Error BBDD: insertar en tabla rallies';
        else $success = '<h5>El rally ha sido creado con éxito.</h5> <p>Puede acceder a su ficha en </p><p><strong><a href="new_rallies.php?id='.$r.'">'.$_POST['name'].'</a></strong></p> <p>o desde el menú "Rallies Nuevos"</p>';
    }
} 

// Listar categorías permitidas en un rally
// Si no se especifica rally: lista todas (desmarcadas)
function rally_categories($categories, $rally=false){
  if($rally) $rally_categories = explode(";", $rally['categories']);
  else $rally_categories = array();
  foreach($categories as $category){
      if(in_array($category['name'], $rally_categories)) $checked = "checked";
      else $checked = "";
      echo '<div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="'.$category['name'].'" value="'.$category['name'].'" '.$checked.'>
              <label class="form-check-label" for="'.$category['name'].'">'.$category['name'].'</label>
            </div>';
  }
}

$title = "Crono Rally 2.0 - Nuevo Rally"; // Título de la página
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
          <h1 class="h3 mb-4 text-gray-800">Rallies nuevos</h1>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">
              
              <!-- Nuevo Rally Card -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Crear nuevo Rally</h6>
                </div>
                <div class="card-body">
                    
                    <?php if(isset($errors[$p.'-nuevoRally'])) include "errors.php"; ?>
                    <?php if(isset($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    <form action="new_rally.php" method="post">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="name" class="form-control" placeholder="Enter name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="categories">Categorías permitidas:</label></br>
                        <?php rally_categories($categories); ?>
                    </div>
                    <div class="form-group">
                        <label for="date">Fecha celebración:
                        <input class="datepicker form-control" type="text" id="date" name="date" value="<?php if(isset($_POST[$p.'-nuevoRally'])) echo "show"; ?>" required>
                        </label>
                    </div>
                    <script>$('#date').datepicker({ format: 'dd-mm-yyyy' });</script>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" name="signupOpen" id="signupOpen" checked>
                        <label class="custom-control-label" for="signupOpen">Inscripciones abiertas</label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" name="add_rally">Guardar</button>
                    </div>
                    </form>

                </div>
              </div>
              <!-- End Nuevo Rally Card -->

            </div>
            <!-- End Columna Izda -->

            <!-- Columna Dcha -->
            <div class="col-lg-6">
              
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

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>

</body>

</html>