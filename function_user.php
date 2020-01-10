<?php
include 'database.php';

if($_SESSION['permission'] != USER) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "function_user";

// Crear o modificar coche
if(isset($_POST['add_car']) OR isset($_POST['update_car'])){
	$data = array();
	$data['iduser'] = $_SESSION['userid'];
	$data['chassis'] = mysqli_real_escape_string($db, $_POST['chassis']);
	$data['body'] = mysqli_real_escape_string($db, $_POST['body']);
	if(isset($_POST['add_car'])){
		if(!$r = insertar("cars", $data)) $errors[$p] = 'Error BBDD: insertar en tabla cars';
	} else {
		if(!$r = modificar("cars", $data, $_POST['idcar'])) $errors[$p] = 'Error BBDD: modificar en tabla cars';
	}
}

// Eliminar coche
if(isset($_POST['delete_car'])){
	// Si el coche está inscrito en un rally no se puede eliminar
	if (consulta('signedup', 'idrally in(SELECT id FROM '.DB_PREFIX.'rallies WHERE open=1) AND iduser='.$_SESSION['userid'].' AND idcar='.$_POST['idcar']))
		$errors[$p] = 'El coche está inscrito en un rally abierto. No se puede eliminar hasta que lo elimines de ese rally.';
	elseif(!eliminar("cars", $_POST['idcar'])) $errors[$p] = 'Error BBDD: eliminar de tabla cars';
}


// Inscribir coche en rally
if(isset($_POST['signup'])){
	// Comprobar que las inscripciones siguen abiertas:
	// es posible que que el admin haya cerrado mientras el user tenga la página abierta
	if(consulta("rallies", "id=".$_POST['idrally']." AND open=1")) {
		$data = array();
		$data['idrally'] = mysqli_real_escape_string($db, $_POST['idrally']);
		$data['iduser'] = $_SESSION['userid'];
		$data['idcar'] = mysqli_real_escape_string($db, $_POST['carid']);
		$data['datetime'] = date("Y-m-d H:i:s");
		$data['category'] = mysqli_real_escape_string($db, $_POST['category']);
	if(!$r = insertar("signedup", $data)) $errors[$p] = 'Error BBDD: insertar en tabla signedup';
	} else $errors[$p] = 'Error: El administrador ya ha cerrado las inscripciones.';
}
// Eliminar inscripción
if(isset($_GET['unsignup'])){
	if(!eliminar("signedup", $_GET['unsignup'])) $errors[$p] = 'Error BBDD: eliminar de tabla signedup';
}

$cars = consulta_multi("cars", "iduser=".$_SESSION['userid']);
$open_rallies = consulta_multi("rallies", "open=1");
$user = consulta("users", "id=".$_SESSION['userid']);

// Include common functions
include ('function_common.php');

// Condiciones que pueden impedir inscribirse en un rally
function signed_granted($rally){
	$cars_signed = consulta_multi("signedup", 'idrally='.$rally['id'].' AND iduser='.$_SESSION['userid']);
	$categoies = explode(";", $rally['categories']);
	// Ya tiene un coche inscrito en cada categoría
	if(count($cars_signed)==count($categoies)) return false;
	return true;
}
// Esciribir options de Categorias por rally
function options_categories($rally){
	$categoies = explode(";", $rally['categories']);
	foreach($categoies as $category){
		// Si el usuario ya tiene un coche inscrito en esa categoría no se muestra la categoría
		if(!consulta("signedup", 'idrally='.$rally['id'].' AND iduser='.$_SESSION['userid'].' AND category="'.$category.'"'))
			echo '<option value="'.$category.'">'.$category.'</option>';
	}
}

// Esciribir options de coches
function options_cars($cars){
	foreach($cars as $car){
		echo '<option value="'.$car['id'].'">'.$car['body'].' ('.$car['chassis'].')</option>';
	}
}

// Devuelve coches disponibles para inscripción en rally
function unsigned_cars($open_rally, $cars){
	if(count($cars)>0){
		foreach($cars as $car){
			if (!consulta('signedup', 'idrally='.$open_rally['id'].' AND iduser='.$_SESSION['userid'].' AND idcar='.$car['id']))
			$unsigned_cars[] = $car;		
		}
		if(isset($unsigned_cars)) return $unsigned_cars;
	}
	return false;
}

// Esciribir lista de coches inscritos en rally
function list_signed_cars($open_rally, $cars){
	foreach($cars as $car){	
		if($signed_car = consulta('signedup', 'idrally='.$open_rally['id'].' AND iduser='.$_SESSION['userid'].' AND idcar='.$car['id'])){
			echo '<div class="card border-left-primary shadow py-2">
					<div class="card-body">
						<div class="row no-gutters align-items-center">
							<div class="col mr-2">
								<div class="text-xs font-weight-bold text-primary text-uppercase mb-1">CATEGORÍA '.$signed_car['category'].'</div>
									<div class="h5 mb-0 font-weight-bold text-gray-800">
										'.$car['body'].' ('.$car['chassis'].')
									</div>
								</div>
							<div class="col-auto">
								<a href="user.php?unsignup='.$signed_car['id'].'" class="btn btn-danger btn-circle btn-lg">
									<i class="fas fa-trash"></i>
								</a>
							</div>
						</div>
					</div>
				</div>'; 
		}
	}
}
?>