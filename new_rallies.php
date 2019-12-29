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
$p = "new_rallies.php";

// Elimina inscripción
if(isset($_GET['delSignedup'])){
    if(!eliminar("signedup", $_GET['delSignedup'])) {
        $errors[$p.'-delSignedup'.$_GET['idrally']] = "No se ha podido eliminar la inscripción";
        $collapseCardlist[$_GET['idrally']] = "show";
    }
}

// Abre la ficha
if(isset($_GET['collapseCardlist'])){
    $collapseCardlist[$_GET['collapseCardlist']] = "show";
}


// Modifica rally
if(isset($_POST['update_rally'])){
    // Comprobar si el rally está abierto
    $index = 'signupOpen'.$_POST['idRally'];
    if(isset($_POST[$index])) $open = 1;
    else $open = 0;
    // Categorías: comprueba cuáles están seleccionadas
    $selectedCats = array();
    $categories = consulta_multi("categories");
    foreach($categories as $category){
        $catName = $category['name'];
        if(isset($_POST[$catName])) $selectedCats[] = $catName;
    }
    // Si ninguna está seleccionada se muestra un error
    if(empty($selectedCats)) $errors[$p.$_POST['idRally']] = 'Debe seleccionar al menos una categoría';
    
  if(count($errors)==0) {
      $data = array();
      $data['name'] = mysqli_real_escape_string($db, $_POST['name']);
      $data['file'] = '';
      $data['date'] = convertDate($_POST['date']);
      $data['open'] = $open;
      $data['categories'] = implode(';', $selectedCats);
      $data['deputy'] = 0;
      if(!$r = modificar("rallies", $data, $_POST['idRally'])) $errors[$p.$_POST['idRally']] = 'Error BBDD: modificar en tabla rallies';
      else $success = '<h5>El rally ha sido modificado con éxito.</h5>';
  }
}

// Eliminar rally
if(isset($_POST['delete_rally'])){
  // Comprobar que no hay inscripciones
  if(consulta("signedup", "idrally=".$_POST['idRally'])){
      $errors[$p.'-listaRallies'.$_POST['idRally']] = 'No puede eliminar el rally mientras haya pilotos inscritos. Elimine primero las inscripciones a este rally.';
  } else {
      if(eliminar("rallies", $_POST['idRally'])) $success = '<h5>Correcto:</h5> <p>El rally ha sido eliminado con éxito.</p>';
      else $errors[$p.$_POST['idRally']] = '<h5>Error:</h5> <p>El rally no pudo ser eliminado</p>';
  }
}

$rallies = consulta_multi("rallies", "deputy=0"); // Debe ejecutarse después de "Añade nuevo rally"
$categories = consulta_multi("categories");

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
              <td><a href="#" onclick="delSignedup('.$id.','.$rally['id'].')" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></a></td>
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
          <h1 class="h3 mb-4 text-gray-800">Rallies nuevos</h1>

          <!-- Row -->
          <div class="row">
            <!-- Columna Izda -->
            <div class="col-lg-6">
              
                <!-- Muestra una ficha por cada rally donde poder modificar datos -->
                <?php if($rallies): ?>
                    <?php foreach($rallies as $rally): ?>
                    <?php $rallyDate = convertDate($rally['date']);?>
                    <!-- Lista de Rallies Card -->
                    <div class="card shadow mb-4" id="card<?php echo $rally['id']; ?>">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><?php echo $rally['name']; ?></h6>
                        </div>
                        <div class="card-body">
                            <?php if(isset($errors[$p.'-listaRallies'.$rally['id']])) include "errors.php"; ?>
                            <form action="new_rallies.php" method="post">
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
                    <!-- End Formulario rally -->
                    <?php endforeach; ?>
                <?php endif; ?>

            <!-- End Columna Izda -->
            </div>

            <!-- Columna Dcha -->
            <div class="col-lg-6">
                <!-- Inscritos Card -->
                <!-- Muestra una ficha por cada rally abierto con una tabla de los inscritos -->
                <?php foreach($rallies as $rally): ?>
                <?php if(numero_inscritos($rally)>0): ?>
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapseCardlist<?php echo $rally['id']; ?>" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseCardlist<?php echo $rally['id']; ?>">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo $rally['name']; ?>: Inscritos <?php echo numero_inscritos($rally); ?></h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse<?php if(isset($collapseCardlist[$rally['id']])) echo " show"; ?>" id="collapseCardlist<?php echo $rally['id']; ?>">
                    <div class="card-body">
                        <?php if(isset($errors[$p.'-delSignedup'.$rally['id']])) include "errors.php"; ?>
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
                                        <th></th>
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
            <!-- End Columna Dcha -->
            </div>

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
function delSignedup(id, idrally){
    if(confirm("Desea eliminar esta inscripción?"))
        document.location.href = "new_rallies.php?delSignedup="+id+"&idrally="+idrally;
}
</script>

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>

</body>

</html>