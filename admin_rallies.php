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
$p = "admin_rallies.php";

// Modifica o Añade nuevo rally
if(isset($_POST['add_rally']) OR isset($_POST['update_rally'])){
  // Comprobar si el rally está abierto
  if(isset($_POST['add_rally'])){
      if(isset($_POST['signupOpen'])) $open = 1;
      else $open = 0;
  }
  if(isset($_POST['update_rally'])){
      $index = 'signupOpen'.$_POST['idRally'];
      if(isset($_POST[$index])) $open = 1;
      else $open = 0;
  } 
  // Categorías: comprueba cuáles están seleccionadas
  $selectedCats = array();
  $categories = consulta_multi("categories");
  foreach($categories as $category){
      $catName = $category['name'];
      if(isset($_POST[$catName])) $selectedCats[] = $catName;
  }
  if(empty($selectedCats)) { // Si ninguna está seleccionada se muestra un error
      if(isset($_POST['add_rally'])) $errors[$p.'-nuevoRally'] = 'Para crear un nuevo rally debe seleccionar al menos una categoría';
      if(isset($_POST['update_rally'])) $errors[$p.'-listaRallies'.$_POST['idRally']] = 'Debe seleccionar al menos una categoría';
  } 
  if(isset($_POST['add_rally'])){
      if(consulta("rallies", 'name="'.mysqli_real_escape_string($db, $_POST['name']).'"')) $errors[$p.'-nuevoRally'] = 'El nombre del rally ya existe.';
  } 
  if(count($errors)==0) {
      $data = array();
      $data['name'] = mysqli_real_escape_string($db, $_POST['name']);
      $data['file'] = '';
      $data['date'] = convertDate($_POST['date']);
      $data['open'] = $open;
      $data['categories'] = implode(';', $selectedCats);
      if(isset($_POST['add_rally'])){
          if(!$r = insertar("rallies", $data)) $errors[$p.'-nuevoRally'] = 'Error BBDD: insertar en tabla rallies';}
      if(isset($_POST['update_rally'])){
          if(!$r = modificar("rallies", $data, $_POST['idRally'])) $errors[$p.'-nuevoRally'] = 'Error BBDD: modificar en tabla rallies';}
  }
}

// Eliminar rally
if(isset($_POST['delete_rally'])){
  // Comprobar que no hay inscripciones
  if(consulta("signedup", "idrally=".$_POST['idRally'])){
      $errors[$p.'-listaRallies'.$_POST['idRally']] = 'No puede eliminar el rally mientras haya pilotos inscritos. Elimine primero las inscripciones a este rally.';
  } else {
      eliminar("rallies", $_POST['idRally']);
  }
}

$rallies = consulta_multi("rallies"); // Debe ejecutarse después de "Añade nuevo rally"
$categories = consulta_multi("categories");
$open_rallies = consulta_multi("rallies", "open=1");

// Listar categorías permitidas en un rally
// Si no se especifica rally lista todas (desmarcadas)
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

// Número de inscripciones
function numero_inscritos($rally){
  if($res = consulta_especial("SELECT COUNT(*) as count FROM ".$_SESSION['prefix']."signedup WHERE idrally=".$rally['id']." GROUP BY idrally"))
      return $res[0]['count'];
  else return 0;
}

// Listar inscritos
function listar_inscritos($rally){
  $signedOn = consulta_especial(' SELECT u.name, c.body, c.chassis, s.category, s.datetime, s.id
                                  FROM rcm_signedup s 
                                  INNER JOIN rcm_users u ON s.iduser=u.id
                                  INNER JOIN rcm_cars c ON s.idcar=c.id
                                  WHERE idrally='.$rally['id'].'
                                  ORDER BY s.datetime');
  $cont = 1;
  foreach($signedOn as $signed){
      $id = $signed['id'];
      echo '<tr>
              <td><div id="num'.$cont.'" hidden>'.$cont.'</div><input id="inp'.$cont.'" name="'.$id.'" type="text" value="'.$cont.'" placeholder="'.$cont.'" size="2" onchange="cambia('.$cont.');"></td>
              <td>'.utf8_encode($signed['name']).'</td>
              <td>'.utf8_encode($signed['body']).'</td>
              <td>'.utf8_encode($signed['chassis']).'</td>
              <td>'.utf8_encode($signed['category']).'</td>
          </tr>';
      $cont++;
  } 
}

$title = "Crono Rally 2.0 - Admin"; // Título de la página
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
          <h1 class="h3 mb-4 text-gray-800">Panel Administración</h1>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">
              
              <!-- Lista de Rallies Card -->
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Lista de rallies</h6>
                </div>
                <div class="card-body">
                    
                    <!-- Formulario nuevo rally -->
                    <div class="card shadow mb-4">
                      <!-- Card Header - Accordion -->
                      <a href="#nuevoRally" class="d-block card-header py-3  bg-gray-700" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="nuevoRally">
                        <h6 class="m-0 font-weight-bold text-gray-100">Crear nuevo rally</h6>
                      </a>
                      <!-- Card Content - Collapse -->
                      <div class="collapse <?php if(isset($errors[$p.'-nuevoRally'])) echo "show"; ?>" id="nuevoRally" style="">
                        <div class="card-body">
                        <?php if(isset($errors[$p.'-nuevoRally'])) include "errors.php"; ?>
                          <form action="admin_rallies.php" method="post">
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
                    </div>
                    <!-- Fin Formulario nuevo rally -->


                    <!-- Fichas de rallies -->
                    <!-- Muestra una ficha por cada rally donde poder modificar datos -->
                    <?php if($rallies): ?>
                    <?php foreach($rallies as $rally): ?>
                    <?php $rallyDate = convertDate($rally['date']);?>
                    <!-- Formulario rally -->
                    <div class="card shadow mb-4">
                      <!-- Card Header - Accordion -->
                      <a href="#collapseCard<?php echo $rally['id']; ?>" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseCard<?php echo $rally['id']; ?>">
                      <h6 class="m-0 font-weight-bold text-gray-800"><?php echo $rally['name'].' ('.$rallyDate.')'; ?></h6>
                      </a>
                      <!-- Card Content - Collapse -->
                      <div class="collapse <?php if(isset($errors[$p.'-listaRallies'.$rally['id']])) echo "show"; ?>" id="collapseCard<?php echo $rally['id']; ?>">
                        <div class="card-body">
                        <?php if(isset($errors[$p.'-listaRallies'.$rally['id']])) include "errors.php"; ?>
                          <form action="admin_rallies.php" method="post">
                            <div class="form-group">
                              <label for="name">Nombre:</label>
                              <input type="name" class="form-control" placeholder="Enter name" name="name" value="<?php echo $rally['name']; ?>">
                            </div>
                            <div class="form-group">
                              <label for="categories">Categorías permitidas:</label></br>
                                <?php rally_categories($categories, $rally); ?>
                            </div>
                            <div class="form-group">
                              <label for="date<?php echo $rally['id']; ?>">Fecha celebración:
                                <input class="datepicker form-control" type="text" id="date<?php echo $rally['id']; ?>" name="date" value="<?php echo $rallyDate; ?>" required>
                              </label>
                            </div>
                            <script>$("#date<?php echo $rally['id']; ?>").datepicker({ format: 'dd-mm-yyyy' });</script>
                            <div class="form-group">
                              <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" name="signupOpen<?php echo $rally['id']; ?>" id="signupOpen<?php echo $rally['id']; ?>" <?php echo ($rally['open'])? 'checked' : ''; ?> >
                                <label class="custom-control-label" for="signupOpen<?php echo $rally['id']; ?>">Inscripciones cerradas/abiertas</label>
                              </div>
                            </div>
                            <input type="hidden" name="idRally" value="<?php echo $rally['id']; ?>">
                            <div class="text-center">
                              <button type="submit" class="btn btn-primary" name="update_rally">Guardar</button>
                              <button type="submit" class="btn btn-danger" name="delete_rally">Eliminar</button>
                            </div>
                          </form>
                          <?php if(numero_inscritos($rally)>0): ?>
                            <?php echo 'Inscritos: '.numero_inscritos($rally); ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                    <!-- End Formulario rally -->
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- Fin Fichas de rallies -->

                </div>
              </div>
              <!-- End Lista de Rallies Card -->

              <!-- Inscritos Card -->
              <!-- Muestra una ficha por cada rally abierto con una tabla de los inscritos -->
              <?php foreach($open_rallies as $rally): ?>
              <?php if(numero_inscritos($rally)>0): ?>
              <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseCardlist<?php echo $rally['id']; ?>" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseCardlist<?php echo $rally['id']; ?>">
                  <h6 class="m-0 font-weight-bold text-primary">Lista de inscritos: <?php echo $rally['name']; ?></h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse" id="collapseCardlist<?php echo $rally['id']; ?>">
                  <div class="card-body">
                  <form id="form1" method="post" action="ajax_save_stage.php">
                  <input type="hidden" name="idrally" value="<?php echo $rally['id']; ?>">
                    <div class="table-responsive">
                      <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                          <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Carrocería</th>
                            <th>Chasis</th>
                            <th>Cat</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php listar_inscritos($rally); ?>
                        </tbody>
                      </table>
                    </div>
                    <div class="text-center">
                      <button type="submit" class="btn btn-primary btn-icon-split" name="startRally">
                        <span class="icon text-white-50"><i class="fas fa-flag"></i></span>
                        <span class="text">Comenzar rally</span>
                      </button>
                    </div>
                  </form>
                  </div>
                </div>
              </div>
              <?php endif; ?>
              <?php endforeach; ?>
              
              <!-- Page level plugins -->
              <script src="vendor/datatables/jquery.dataTables.min.js"></script>
              <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
              <!-- Page level custom scripts -->
              <script src="js/demo/datatables-demo.js"></script>
              <!-- fin Inscritos Card -->

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
  <script type="text/javascript">
  function cambia(num){
    var table = $('#dataTable').DataTable();
     table.destroy();
     table.draw();
     table.column(0).data().sort();
     texto = $("#inp"+num).val();
     $("#num"+num).text(texto);
     var table = $('#dataTable').DataTable();
     table.destroy();
     table.draw();
     table.column(0).data().sort();
}
</script>

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>

</body>

</html>