<?php
include 'database.php';
include ('function_common.php');

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "function_admin";

// Anade nueva categoria 
if(isset($_POST['addCat'])){
    $data['name'] = mysqli_real_escape_string($db, $_POST['categoryName']);
    if(!insertar("categories", $data)) 
        $errors[$p.'-categorias'] = 'Error al crear categoria';
}

// Elimina categoria 
if(isset($_GET['del_cat'])){
    if(!eliminar("categories", mysqli_real_escape_string($db, $_GET['del_cat']))) 
        $errors[$p.'-categorias'] = 'Error al eliminar categoria';
}

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

// Pintar categorías con botón de eliminar
function listar_categorias($categories){
    if($categories){ 
        foreach($categories as $category){
            echo '<div class="form-group">
                    <div class="row">
                    <div class="col-md-8">
                        <h6 class="border-bottom-danger">'.$category['name'].'</h6>
                    </div>
                    <div class="col-md-4">
                        <a href="admin.php?del_cat='.$category['id'].'" class="btn btn-danger btn-circle btn-sm">
                        <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    </div>
                </div>';
        }
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
?>
