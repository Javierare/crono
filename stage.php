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
if(!isset($_GET['idrally'])){
	$error_text = 'Debe indicarse el rally a consultar.';
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'type'=>'danger');
    include 'alert.php';
    exit();
}

$idrally = $_GET['idrally'];
// Si no se especifica idstage le damos el primero
if(!isset($_GET['idstage'])) $idstage = 1;
else $idstage = $_GET['idstage'];

// Cargar datos del rally
if(!$rally = consulta('rallies', 'id='.$idrally)){
	$error_text = 'No se encuentra el rally id:'.$idrally;
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'hidden'=>'false', 'type'=>'danger');
    include 'alert.php';
    exit();
}

// Comprobar que existen datos de tramo
if(!consulta("stages", "idrally=".$idrally." AND idstage=".$idstage)){
    $error_text = 'No hay datos para el tramo:'.$idstage;
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'hidden'=>'false', 'type'=>'danger');
    include 'alert.php';
    exit();
}

// Obtenemos las categorías de este rally
if(!$categories = explode(";", $rally['categories'])){
	$errors_text = 'No se pudieron obtener las categorías del rally';
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'hidden'=>'false', 'type'=>'danger');
    include 'alert.php';
    exit();
}

$stages = consulta_multi("stages", "idrally=".$idrally." GROUP BY idstage ORDER BY idstage ASC");

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
                                            ORDER BY sta.totaltime")) return false;
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
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <div class="d-none d-md-block pt-2"><h5><i class="fas fa-stopwatch"></i> RALLY RC CRONO 2.O.</h5></div> 
            </nav>
            <?php //include('topbar.php'); ?>
            <!-- End of Topbar -->

            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <h1 class="h3 mb-4 text-gray-800"><?php echo($rally['name']) ?></h1>

                <label for="selectCat">Categorías:</label>
                <select name="selectCat" id="selectCat" onchange="changeFunc();">
                    <option value="todas">Todas</option>
                    <?php foreach($categories as $category){
                            (isset($_GET['category']) AND $_GET['category'] == $category)? $selected = "selected" : $selected = "";
                            echo '<option value="'.$category.'" '.$selected.'>'.$category.'</option>';
                        } ?>
                    <option value="Absoluta" <?php if(isset($_GET['category']) AND $_GET['category'] == "Absoluta") echo "selected"; ?>>Absoluta</option>
                </select>
                <label for="selectStage">Tramos:</label>
                <select name="selectStage" id="selectStage" onchange="changeFunc();">
                    <?php foreach($stages as $stage){
                            ($idstage == $stage['idstage'])? $selected = "selected" : $selected = "";
                            echo '<option value="'.$stage['idstage'].'" '.$selected.'>'.$stage['stage'].'</option>';
                        } ?>
                    <option value="todos">Todos</option>
                </select>

            <?php foreach($categories as $category): ?>
            <?php if(isset($_GET['category'])) $category = $_GET['category']; // Si se especifica "categoría" se muestran la categoría y se interrumpe el foreach?>
            <?php if(isset($_GET['category']) AND $_GET['category']=="Absoluta") $category = false; // En Absoluta no se muestran los puntos ?>
            <!-- Se debe controlar que hay pilotos en esta categoría para no dar una tabla vacía -->
            <?php if($stage = stage_rows($idrally, $idstage, $category)): ?>
                <!-- DataTales Example -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo $stage[0]['stage']." ".$category ?></h6>
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
                                        } else if($row['totaltime']!="Abandono") { // Datos de diferencia de tiempos para el resto
                                            $dif_ant = "+". restar_tiempos($row['totaltime'], $ant_tiempo);
                                            $dif_1 = "+". restar_tiempos($row['totaltime'], $mejor_tiempo);
                                            $ant_tiempo = $row['totaltime'];
                                        } else { // Abandono
                                            $dif_ant = "+05.000"; // Penalización por abandono
                                            $timeAbandona = sumar_tiempos($ant_tiempo, "00:05.000");
                                            $dif_1 = "+". restar_tiempos($timeAbandona, $mejor_tiempo);
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
            <?php if(isset($_GET['category'])) break; // Si se ha especificado categoría se interrumpe el foreach ?>
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

<script>
$(document).ready(function() {
    $('table.display').DataTable();
} );

function changeFunc() {
    var selectCat = document.getElementById("selectCat");
    var selectedCat = selectCat.options[selectCat.selectedIndex].value;
    var selectStage = document.getElementById("selectStage");
    var selectedStage = selectStage.options[selectStage.selectedIndex].value;
    if(selectedStage=="todos"){
        if(selectedCat!="todas"){
            document.location.href = "rally.php?idrally=<?php echo $rally['id']; ?>&category="+selectedCat;
        } else {
            document.location.href = "rally.php?idrally=<?php echo $rally['id']; ?>";
        }
    }else if(selectedCat == "todas"){
        document.location.href = "stage.php?idrally=<?php echo $rally['id']; ?>&idstage="+selectedStage;
    } else {
        document.location.href = "stage.php?idrally=<?php echo $rally['id']; ?>&idstage="+selectedStage+"&category="+selectedCat;
    }
}
</script>
</body>

</html>