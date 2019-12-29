<?php
session_start();

include 'idioma/es.php';
include 'database.php';
include 'function_common.php';

$errors = array();
$p = "rally";
$show_difs = true; // Si quieres mostrar las columnas de dif-ant, dif-1st
$show_points = true;

$title = WELCOME; // Título de la página
include ('header.php');

// Evitar acceso a la página sin id_rally
if(!isset($_GET['idrally'])){
    $error_text = 'Debe indicarse el rally a consultar.';
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'type'=>'danger');
    include 'alert.php';
    exit();
}

$idrally = $_GET['idrally'];

// Cargar datos del rally
if(!$rally = consulta('rallies', 'id='.$idrally)){
	$error_text = 'No se encuentra el rally id:'.$idrally;
    $alert = array('title'=>"Error:", 'body'=>$error_text, 'location'=>'index.php', 'type'=>'danger');
    include 'alert.php';
    exit();
}

// Obtenemos las categorías de este rally
if(!$categories = explode(";", $rally['categories']))
	$errors['rally.php'] = 'No se pudieron obtener las categorías del rally';

// Obtener tramos
if(!$stages = consulta_multi("stages", "idrally=".$idrally." GROUP BY idstage"))
	$errors['rally.php'] = 'El rally aún no tiene datos o no se pudieron obtener.';

// Por cada cagoria se obtienen los tramos correspondientes a este rally
function stage_rows($idrally, $idstage, $category=false){
    if($category) $and_category = ' AND sig.category = "'.$category.'"';
    else $and_category = '';
    if(!$stage = consulta_especial(" SELECT user.name, car.body, car.chassis, sta.stage, sta.totaltime, sta.idsignedup, sig.num
                                    FROM ".DB_PREFIX."stages as sta
                                    INNER JOIN ".DB_PREFIX."signedup as sig on sig.id = sta.idsignedup
                                    INNER JOIN ".DB_PREFIX."cars as car on car.id = sig.idcar
                                    INNER JOIN ".DB_PREFIX."users as user on user.id = sig.iduser
                                    WHERE sta.idrally = ".$idrally." 
                                    AND sta.idstage = ".$idstage.
                                    $and_category." 
                                    ORDER BY sta.idstage"))
    $errors['stage.php'] = 'El rally aún no tiene datos o no se pudieron obtener.';
    return $stage;
}

// Array de puntuaciones
$points = array(640, 613, 587, 562, 538, 515, 493, 472, 452, 433, 414, 396, 379, 363, 347, 332, 318, 304, 291, 278, 266);

// CREACIÓN DE COLUMNAS ********************************************************************
// key: título de la columna
// val: nombre del campo en el array de pilotos
$cols = array("Pos"=>"pos");
// Columnas opcionales que se mostrarán en la tabla (deben estar contempladas en la consulta)
$op_cols = array("num"=>"num", "Piloto"=>"name", "Carroceria"=>"body", "Chasis"=>"chassis");
// De momento las añadimos todas
$cols = array_merge($cols, $op_cols);
// Por cada tramo asigno una columna "nombre_tramo=>tiempo_total"
$cont = 1;
foreach($stages as $stage){
    $cols[$stage['stage']] = "totaltime".$cont++;
}
// Añado las columna Total
$cols['Total'] = "rallytime";
// *****************************************************************************************

// 

//$pilotos = array();
//$sum_times = array();
//Genera un array de pilotos con el resumen del rally:
function res_rally_category($idrally, $stages, $category, $op_cols, $points){
    if($category=="Absoluta") $category = false;
    $t = 1;
    $pilotos = array();
    foreach($stages as $stage){
        $stage_rows = stage_rows($idrally, $stage['idstage'], $category);
        foreach($stage_rows as $stage_row) {
            $id = $stage_row['idsignedup']; // idsignedup sirve para identificar al piloto
            if(!isset($sum_times[$id])) $sum_times[$id] = "00:00.000"; // Empieza el sumatorio en 00:00:000
            $sum_times[$id] = sumar_tiempos($sum_times[$id], $stage_row['totaltime']); // Voy sumando los tiempos de cada tramo
            //$pilotos[$id]['stage'.$t] = $stage_row['stage']; // Añado una columan por cada
            $pilotos[$id]['totaltime'.$t] = $stage_row['totaltime'];
            $pilotos[$id]['rallytime'] = $sum_times[$id];
            // Por cada columna opcional tomo su dato para guardarlo en el array $pilotos
            foreach($op_cols as $op_col) {
                $pilotos[$id][$op_col] = $stage_row[$op_col];
            }
        }
        $t++;
    }
    if(count($pilotos)==0) {
        // Es posible que exista una categoría donde no se inscribió ningún piloto,
        return false;
    } else {
        // Ordeno los pilotos por "rallytime"
        array_multisort($sum_times, SORT_ASC, $pilotos);
        // Añado la posición y los puntos
        $cont = 0;
        foreach($pilotos as $key=>$val){
            $pilotos[$key]['pos'] = $cont+1;
            $pilotos[$key]['points'] = $points[$cont++];
        }
        return $pilotos;
    }
}


// Genera el título de las columnas con los campos pasados por parámetro
function titulos_columnas($cols, $show_difs, $show_points){
    foreach($cols as $key=>$campo){
		echo ('<th>'.$key.'</th>');
    }
    if($show_difs){ // Añadimos campos difs
        echo ('<th>dif-ant</th>');
        echo ('<th>dif-1st</th>');
    }
    if($show_points){ // Añadimos campo puntos
        echo ('<th>Points</th>');
    }
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
                <div class="text-right">
                <select name="selectCat" id="selectCat" onchange="changeFunc();">
                    <option value="Todas">Todas</option>
                    <?php foreach($categories as $category){
                            (isset($_GET['category']) AND $_GET['category'] == $category)? $selected = "selected" : $selected = "";
                            echo '<option value="'.$category.'" '.$selected.'>'.$category.'</option>';
                        } ?>
                    <option value="Absoluta" <?php if(isset($_GET['category']) AND $_GET['category'] == "Absoluta") echo "selected"; ?>>Absoluta</option>
                </select>
                </div>

            <!-- Mostrar una ficha por categoría -->
            <?php foreach($categories as $category): ?>
            <?php if(isset($_GET['category'])) $category = $_GET['category']; // Si se especifica "categoría" se muestran la categoría y se interrumpe el foreach?>
            <?php if(isset($_GET['category']) AND $_GET['category']=="Absoluta") $show_points = false; // En Absoluta no se muestran los puntos ?>
            <!-- Se debe controlar que hay pilotos en esta categoría para no dar una tabla vacía -->
            <?php if($pilotos = res_rally_category($idrally, $stages, $category, $op_cols, $points)): ?>
                <!-- DataTales Example -->
                <div class="card shadow mb-4" id="<?php echo $category ?>">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo $category ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered display" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <?php titulos_columnas($cols, $show_difs, $show_points); ?>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <?php titulos_columnas($cols, $show_difs, $show_points); ?>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <?php 
                                    $pos = 1;
                                    foreach($pilotos as $piloto){ // Genera filas
                                        echo '<tr>';
                                        foreach($cols as $key=>$col){
                                            echo '<td>'.utf8_encode($piloto[$col]).'</td>'; 
                                        }
                                        if($show_difs){ // si se quiere mostrar las diferencias
                                            if($piloto['pos']==1){ // Datos para el piloto que hizo mejor tiempo
                                                $dif_ant = "";
                                                $dif_1 = "";
                                                $mejor_tiempo = $piloto['rallytime'];
                                                $ant_tiempo = $piloto['rallytime'];
                                            } else { // Datos de diferencia de tiempos para el resto
                                                $dif_ant = "+". restar_tiempos($piloto['rallytime'], $ant_tiempo);
                                                $dif_1 = "+". restar_tiempos($piloto['rallytime'], $mejor_tiempo);
                                                $ant_tiempo = $piloto['rallytime'];
                                            }
                                            $pos++; 
                                            echo('  <td>'.$dif_ant.'</td>
                                                    <td>'.$dif_1.'</td>');
                                        }
                                        if($show_points){ // si queremos mostrar los puntos
                                            echo('  <td>'.$piloto['points'].'</td>');
                                        }
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

<!-- Page level plugins -->
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- Page level custom scripts -->
<script src="js/demo/datatables-demo.js"></script>
<script>
$(document).ready(function() {
    $('table.display').DataTable();
} );

function changeFunc() {
    var selectBox = document.getElementById("selectCat");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
    if(selectedValue == "Todas"){
        document.location.href = "rally.php?idrally=<?php echo $rally['id']; ?>";
    } else {
        document.location.href = "rally.php?idrally=<?php echo $rally['id']; ?>&category="+selectedValue;
    }
}
</script>
</body>

</html>