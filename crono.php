<?php
session_start();

include ('idioma/es.php'); // Contiene los textos traducidos al español
include ('function_crono.php'); // Contiene las funciones y llamadas a otros archivos (database.php, config.php...)

$title = WELCOME; // Título de la página
include ('header.php');

// Comprobamos que existe el rally
if(isset($_GET['idrally'])){
  if(!$rally = consulta("rallies", "id=".$_GET['idrally'])) {
    $error_nav = "El rally ".$_GET['idrally']." no existe";
  } else {
    $idrally = $_GET['idrally'];
    $stages = consulta_multi("stages", "idrally=".$idrally." GROUP BY idstage");
    // Si no tenemos el id del tramo: es nuevo tramo
    if(!isset($_GET['idstage'])){
      // Comprobamos si hay tramos guardados
      if(!$stages) {
        $idstage = 1; // No hay: tramo 1
      } else {
        $idstage = count($stages)+1; // Sí hay: siguiente tramo
      }
      $stage_name = "Tramo ".$idstage;
    // Tenemos el id del tramo: Editar tramo
    } else {
      // Comprobamos que hay datos guardados del tramo
      if(!$stage_rows = consulta_multi("stages", "idrally=".$idrally." AND idstage=".$_GET['idstage'])) {
        $error_nav = "El tramo ".$_GET['idstage']." no se encuentra";
      } else {
        $load_stage = true;
        $idstage = $_GET['idstage'];
        $stage_name = $stage_rows[0]['stage']; 
      }
    }
    // Después de todas las comprobaciones tenemos:
    // $idrally, $idstage, $rally, [$stages, $stage_rows]
  }
} else {
  $error_nav = "Para acceder al crono hay que especificar un rally";
}

//Si todo ha ido bien: ¡ Empezamos !
if(!isset($error_nav)){
  // De momento los campos a mano. Habría que dar opción de crear los campos a voluntad
  $campos = array("Num", "Nombre", "Carroceria", "Chasis");

  // CARGAR INSCRIPCIONES DESDE BBDD
  if(!$inscripciones = consulta_especial('SELECT s.num as Num, u.name as Nombre, c.body as Carroceria, c.chassis as Chasis, s.category as categoria, u.id as id, s.id as idsignedup
                                          FROM rcm_signedup s 
                                          INNER JOIN rcm_users u ON s.iduser=u.id
                                          INNER JOIN rcm_cars c ON s.idcar=c.id
                                          WHERE idrally='.$idrally.'
                                          ORDER BY s.num')) $error_nav = "No se han podido cargar las inscripciones";
  else $n_inscritos = count($inscripciones);
}
?>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <?php //include('sidebar.php'); ?> 
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <?php //include('topbar.php'); ?>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">
          <?php
          // Si ha ocurrido algún error se muestra un modal
          if(isset($error_nav)){
            $modal = array('title'=>"Error:", 'body'=>$error_nav, 'location'=>'admin.php', 'hidden'=>'false');
            include ("modal.php");
          }  ?>

          <?php if(!isset($error_nav)): ?>

            <!-- Page Heading -->
            <h1 class="h3 mb-4 text-gray-800"><?php echo($rally['name']) ?></h1>

            <script src="funciones.js" type="text/javascript"></script>

            <script language="JavaScript">
            <?php
            // Genera el codigo JS para manejar el crono
            // Ojo! el contador comienza en 1 para hacerlo coincidir con los id de la tabla stages:
            // Así idntificadores$x = id en tabla stages
            for($x=1; $x<=$n_inscritos; $x++){
              js_crono($x);
            }
            ?>
            </script>
            <div id="resultado"></div>
            <form id="timeform" name="timeform" method="post" action= onsubmit="return validar(timeform, action)">
            <h5><?php echo($stage_name); // Nombre del tramo (automático) ?></h5> 
            <input type="hidden" name="stage" id="stage" value="<?php echo($stage_name); ?>"/>
            <input type="hidden" name="idstage" id="idstage" value="<?php echo($idstage); ?>">
            <input type="hidden" name="idrally" id="idrally" value="<?php echo($idrally); ?>"> 
            <input type="hidden" name="n_pilotos" id="n_pilotos" value="<?php echo($n_inscritos); ?>"> 
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-sm" id="dataTable" width="100%" cellspacing="0">
                  <thead>
                    <?php titulos_columnas($campos); ?>
                  </thead>
                  <tbody>
                    <?php // Inicializamos los valores de los tiempos
                          $tiempototal = "00:00";
                          $timetextarea = "00:00";
                          $penalizaciones = "0";
                          $x = 1; // Empezamos en 1 para coincidir con el id de la tabla stages
                          // Recorremos las inscripciones
                          foreach($inscripciones as $inscripcion) {
                            // Si estamos editando el tramo:
                            if (isset($load_stage)) {
                              // Recorremos la tabla stages,
                              foreach($stage_rows as $stage_row) {
                                // Si el inscrito ya tiene tiempo guardadto,
                                if ($inscripcion['idsignedup'] == $stage_row['idsignedup']) {
                                  // recuperamos los tiempos para pintarlos en el crono
                                  $timetextarea = $stage_row['time'];
                                  $penalizaciones = $stage_row['penalties'];
                                  $tiempototal = $stage_row['totaltime'];
                                break;
                                  // Si no tiene tiempo guardado iniciamos en ceros
                                } else {
                                  $tiempototal = "00:00";
                                  $timetextarea = "00:00";
                                  $penalizaciones = "0";
                                }
                              }
                            }
                            // Pintamos las filas del crono
                            filas_crono($x++, $campos, $inscripcion, $timetextarea, $penalizaciones, $tiempototal);
                          } ?> 
                  </tbody>
                </table>
              </div>
            <div class="text-center">
            <div class="form-group">
            <a href="#" class="btn btn-secondary btn-icon-split" onclick="validar(timeform, 'crono.php?idrally=<?php echo($idrally); ?>')">
              <span class="icon text-white-50">
                <i class="fas fa-arrow-right"></i>
              </span>
              <span class="text">Siguiente tramo</span>
            </a>
            </div>
            <!-- Botones para editar los tramos ya corridos -->
            <div class="form-group">
            <?php 
            if(isset($stages)){
              foreach($stages as $stage) {
                ($stage['idstage']==$idstage)? $disable = "disabled" : $disable = "";
                echo '<input type="button" class="btn btn-primary" onclick="validar(timeform, \'crono.php?idstage='.$stage['idstage'].'&idrally='.$idrally.'\')" value="'.$stage['stage'].'" '.$disable.'/>';
              }
            }
            ?>
            </div>
            </div>
            </form>

          <?php endif; ?>
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