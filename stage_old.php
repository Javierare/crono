<?php
session_start();

include 'idioma/es.php';
include 'database.php';
include 'function_common.php';

$errors = array();
$p = "satage";

$title = WELCOME; // Título de la página
include ('header.php');

// Evitar acceso a la página sin idrally o idstage
if(!isset($_GET['idrally']) OR !isset($_GET['idstage'])){
	$errors[] = 'Debe indicarse el rally y el tramo a consultar.';
} else {
    $idrally = $_GET['idrally'];
    $idstage = $_GET['idstage'];
    // Cargar datos del rally
    if(!$rally = consulta('rallies', 'id='.$idrally)) {
        $errors[] = 'No se encuentra el rally id:'.$idrally;
    } else {
        // Obtenemos las categorías de este rally
        if(!$categories = explode(";", $rally['categories']))
        	$errors[] = 'No se pudieron obtener las categorías del rally';
    }
}

// Obtener datos de tramo por categoría o absolutos
function stage_rows($idrally, $idstage, $category=false){
    if($category) $and_category = ' AND sig.category = "'.$category.'"';
    else $and_category = '';
    if(!$stage = consulta_especial(" SELECT sta.idstage, sta.idrally, sta.stage, sta.time, sta.penalties, sta.totaltime,
                                            sig.category, sig.num, user.name, car.body, car.chassis 
                                            FROM ".DB_PREFIX."stages as sta
                                            INNER JOIN ".DB_PREFIX."signedup as sig on sig.id = sta.idsignedup
                                            INNER JOIN ".DB_PREFIX."cars as car on car.id = sig.idcar
                                            INNER JOIN ".DB_PREFIX."users as user on user.id = sig.iduser
                                            WHERE sta.idrally = ".$idrally." 
                                            AND sta.idstage = ".$idstage.
                                            $and_category." 
                                            ORDER BY sta.totaltime"))
    $errors[] = 'El rally aún no tiene datos o no se pudieron obtener.';
    return $stage;
}

$cols = array("num"=>"num", "Piloto"=>"name", "Carroceria"=>"body", "Chasis"=>"chassis", 
            "Categoria"=>"category", "Tiempo"=>"time", "Penalizaciones"=>"penalties", "Total"=>"totaltime");

// Genera el título de las columnas con los campos pasados por parámetro
function titulos_columnas($cols){
    echo ('<th>pos</th>');
	foreach($cols as $key=>$campo){
		echo ('<th>'.$key.'</th>');
    }
    echo ('<th>dif-pre</th>');
    echo ('<th>dif-1st</th>');
}
if(count($errors)){
    $error_text = "";
    foreach($errors as $error) {$error_text .= $error;}
    $modal = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'login.php', 'hidden'=>'false');
    include 'modal.php';
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

                <!-- Page Heading -->
                <h1 class="h3 mb-4 text-gray-800"><?php echo($rally['name']) ?></h1>

            <?php foreach($categories as $category): ?>
            <!-- Se debe controlar que hay pilotos en esta categoría para no dar una tabla vacía -->
            <?php if($stage = stage_rows($idrally, $idstage, $category)): ?>
                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo $category ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered display" id="" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <?php titulos_columnas($cols); ?>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <?php titulos_columnas($cols); ?>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php 
                                    $pos = 1;
                                    foreach($stage as $row){ // Genera filas
                                        echo '<tr>';
                                        echo '   <td>'.$pos.'</td>';
                                        foreach($cols as $col){ // Genera columnas
                                            echo '<td>'.utf8_encode($row[$col]).'</td>'; 
                                        }
                                        if($pos==1){ // Datos para el piloto que hizo mejor tiempo
                                            $dif_ant = "";
                                            $dif_1 = "";
                                            $mejor_tiempo = $row['totaltime'];
                                            $ant_tiempo = $row['totaltime'];
                                        } else { // Datos de diferencia de tiempos para el resto
                                            $dif_ant = "+". restar_tiempos($row['totaltime'], $ant_tiempo);
                                            $dif_1 = "+". restar_tiempos($row['totaltime'], $mejor_tiempo);
                                            $ant_tiempo = $row['totaltime'];
                                        }
                                        $pos++; 
                                        echo('  <td>'.$dif_ant.'</td>
                                                <td>'.$dif_1.'</td>');
                                        echo '</tr>';
                                    } ?>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php endforeach; ?>


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

<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.js"></script>

<!-- Page level custom scripts -->
<!-- <script src="js/demo/datatables-demo.js"></script> -->
<script>
$(document).ready(function() {
    $('table.display').DataTable();
} );
</script>

</body>

</html>