<?php
/* Contiene las funciones para conectar con la BBDD MySQL*/
include ('config.php'); // Contiene configuraciones

// FUNCION PARA CONECTAR
// ENTRADA:
//  - Host
//  - User
//  - Pass
//  - BD Name
$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
	$errors['database.php'] = "Failed to connect to MySQL: " . mysqli_connect_error();
	exit();
}

// FUNCION PARA CONSULTAS CON SALIDA MODULADA Y ENTRADA ASISTIDA
// ENTRADA:
//  - tabla
//  - [consulta]: sentencia SQL después del WHERE
//  - [tipo]: tipo de array generado (MYSQLI_ASSOC por defecto)
//  - [prefix]: prefijo de la tabla
// SALIDA:
//     - Falso si no encuentro ninguna fila
//     - La fila si sólo encuentro una
//     - Un array de filas si encuentro más de una
function consulta($tabla, $consulta = 1, $tipo = MYSQLI_ASSOC, $prefix=DB_PREFIX){
	global $db;
	//echo('SELECT * FROM '.$prefix.$tabla.' WHERE '.$consulta.'</br>');
	if($res = mysqli_query($db,'SELECT * FROM '.$prefix.$tabla.' WHERE '.$consulta)){
		$filas = array();
		while($fila = mysqli_fetch_array($res, $tipo)){
			$filas[] = $fila;
		}
		if(count($filas)>1)	return $filas;
		elseif (count($filas)==1) return $filas[0];
	} else return false;
	mysqli_free_result($res);
}

// FUNCION PARA CONSULTAS SIN MODULAR LA SALIDA Y ENTRADA ASISTIDA
// ENTRADA:
//  - tabla
//  - [consulta]: sentencia SQL después del WHERE
//  - [tipo]: tipo de array generado (MYSQLI_ASSOC por defecto)
//  - [prefix]: prefijo de la tabla
// SALIDA:
//     - Falso si no encuentro ninguna fila
//     - Un array de filas si encuentro una O más
function consulta_multi($tabla, $consulta = 1, $tipo = MYSQLI_ASSOC, $prefix=DB_PREFIX){
	global $db;
	//echo('SELECT * FROM '.$prefix.$tabla.' WHERE '.$consulta.'</br>');
	if($res = mysqli_query($db,'SELECT * FROM '.$prefix.$tabla.' WHERE '.$consulta)){
		$filas = array();
		while($fila = mysqli_fetch_array($res, $tipo)){
			$filas[] = $fila;
		}
		return $filas;
	} else return false;
	mysqli_free_result($res);
}

// FUNCION PARA EJECUTAR CUALQUIER OPERACIÓN EN LA BBDD
// *********** ¡ Ojo con el prefix ! **************
// ENTRADA:
//  - tabla
//  - [consulta]: sentencia SQL completa
//  - [tipo]: tipo de array generado (MYSQLI_ASSOC por defecto)
// SALIDA:
//     - Falso si no encuentro ninguna fila
//     - Un array de filas si encuentro una O más
function consulta_especial($consulta, $tipo = MYSQLI_ASSOC){
	//echo($consulta.'</br>');
	global $db;
	if($res = mysqli_query($db, $consulta)){
		$filas = array();
		while($fila = mysqli_fetch_array($res, $tipo)){
			$filas[] = $fila;
		}
		return $filas;
	} else return false;
	mysqli_free_result($res);
}

// FUNCION PARA ELIMINAR UNA FILA
// ENTRADA:
//  - tabla
//  - id: identificador de la fila a eliminar
//  - [prefix]: prefijo de la tabla
// SALIDA:
//     - Falso si falla
//     - True si todo va bien
function eliminar($tabla, $id, $prefix=DB_PREFIX){
	global $db;
	if (mysqli_query($db,'DELETE FROM '.$prefix.$tabla.' WHERE id='.$id)){
		return true;
	} else {
		return false;
	}
}

// FUNCION PARA INSERTAR UNA FILA
// ENTRADA:
//  - tabla
//  - data: $data['campo'] = "valor" - Array donde el indice sea el nombre del campo de la tabla
//  - [prefix]: prefijo de la tabla
// SALIDA:
//     - Falso si falla
//     - El id de la fila insertada si todo va bien
function insertar($tabla, $data, $prefix=DB_PREFIX){
	global $db;
	$valores = array();
	foreach ($data as $key => $valor){
		if(!is_numeric($valor)) $valores[$key] = '"'.$valor.'"';
		else $valores[$key] = $valor;
	}
	$str_campos = implode(',', array_keys($valores));
	$str_valores = implode(',', $valores);
	//echo 'INSERT INTO '.$prefix.$tabla.' ('.$str_campos.') VALUES ('.$str_valores.');';
	if ($r = mysqli_query($db,'INSERT INTO '.$prefix.$tabla.' ('.$str_campos.') VALUES ('.$str_valores.');')){
		return mysqli_insert_id($db);
	} else {
		return false;
	}
}

// FUNCION PARA MODIFICAR UNA FILA
// ENTRADA:
//  - tabla
//  - data: $data['campo'] = "valor" - Array donde el indice sea el nombre del campo de la tabla
//  - id: identificador de la fila a modificar
//  - [prefix]: prefijo de la tabla
// SALIDA:
//     - Falso si falla
//     - El id de la fila insertada si todo va bien
//     - True si todo va bien
function modificar($tabla, $data, $id, $prefix=DB_PREFIX){
	global $db;
	$valores = array();
	foreach ($data as $key => $valor){
		if(!is_numeric($valor)) $valores[$key] = $key.'="'.$valor.'"';
		else $valores[$key] = $key.'='.$valor;
	}
	$str_valores = implode(',', $valores);
	//echo ('UPDATE '.$prefix.$tabla.' SET '.$str_valores.' WHERE id = '.$id.';');
	if ($r = mysqli_query($db,'UPDATE '.$prefix.$tabla.' SET '.$str_valores.' WHERE id = '.$id.';')){
		return true;
	} else {
		return false;
	}
}
?>