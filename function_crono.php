<?php
include 'database.php';

if($_SESSION['permission'] != ADMIN) {
	header('location: login.php');
	exit;
}

$errors = array();
$p = "crono";

// CARGAR ARCHIVO DE INSCRIPCIONES
// Formato del archivo:
//  - Primera línea los campos
//  - Lineas siguientes los registros
//  - Separador ";"
// Devuelve:
//  - Campos: campo[0] = campo_nombre, campo[1] = campo_body, ...
//  - Inscripciones: $inscripciones[0] =  array ($inscripcion[0] = dato_nombre, $inscripcion[1] = dato_body, ...)
function leer_inscripciones($archivo){
	if($fp = fopen($archivo, "r")){
		$n_inscripciones = 0;
		while(!feof($fp)) {
			$linea = fgets($fp);
			if(!empty($linea)){
				if(!isset($inscripciones)){
					$campos = explode(';', $linea);
					$inscripciones = array();
				} else {
					$inscripcion = array();
					$datos_linea = explode(';', $linea);
					if(count($campos)!= count($datos_linea)){
						$errors["function_crono.php"] = 'Error en la línea: '.$linea;
						return false; // A esta línea le falta algún dato. Error archivo no leído
					} else {
						foreach($datos_linea as $x => $dato){
							$index = trim($campos[$x]);
							$inscripcion[$index] = $dato;
						}
						$inscripciones[$n_inscripciones] =  $inscripcion;
						$n_inscripciones++;
					}
				}
			}
		}
		fclose($fp);
		return array($campos, $inscripciones);
	} else {
		return false;
	}
}

// Genera código JS para controlar los cronómetros y las penalizaciones
function js_crono($x=1){
	echo ('
		var timercount'.$x.' = 0;
		var timestart'.$x.'  = null;
		var timeend'.$x.'  = null;
		var timetotal'.$x.'  = null;
		
		function showtimer'.$x.'() {
			if(timercount'.$x.') {
				clearTimeout(timercount'.$x.');
				clockID = 0;
			}
			var timeend = new Date();
			var timedifference = timeend.getTime() - timestart'.$x.'.getTime();
			timeend.setTime(timedifference);
			var minutes_passed = timeend.getMinutes();
			if(minutes_passed < 10){
				minutes_passed = "0" + minutes_passed;
			}
			var seconds_passed = timeend.getSeconds();
			if(seconds_passed < 10){
				seconds_passed = "0" + seconds_passed;
			}
			document.timeform.timetextarea'.$x.'.value = minutes_passed + ":" + seconds_passed;
			timercount'.$x.' = setTimeout("showtimer'.$x.'()", 1000);
		}
		
		function sw_start'.$x.'(){
			if(!timestart'.$x.'){
				timestart'.$x.'   = new Date();
				document.timeform.timetextarea'.$x.'.value = "00:00";
			}
			timercount'.$x.'  = setTimeout("showtimer'.$x.'()", 1000);
			cambiaColorColumnas('.$x.', "sw_start", document);
			$("#ok'.$x.'").attr("class", "fas fa-exclamation-triangle");
		}
		
		function Stop'.$x.'() {
			if(timercount'.$x.') {
				clearTimeout(timercount'.$x.');
				timeend'.$x.' = new Date();
				timedifference = timeend'.$x.'.getTime() - timestart'.$x.'.getTime();
				timeend'.$x.'.setTime(timedifference);
				document.timeform.timetextarea'.$x.'.value = convertir_msmm(timeend'.$x.');
				document.timeform.tiempototal'.$x.'.value = convertir_msmm(timeend'.$x.');
				cambiaColorColumnas('.$x.', "stop", document);
				save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);
			}
		}
		
		function Reset'.$x.'() {
			timestart'.$x.' = null;
			document.timeform.timetextarea'.$x.'.value = "00:00";
			document.timeform.tiempototal'.$x.'.value = "00:00";
			cambiaColorColumnas('.$x.', "reset", document);
			save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);
		}
		
		function Penaliza'.$x.'(segundos){
			timetotal'.$x.' = new Date();
			timetotal'.$x.'.setTime(timeend'.$x.');
			// Resetea penalizaciones a 00
			if(segundos==0){
				document.timeform.penalizaciones'.$x.'.value = "00";
			}
			// Suma penalizaciones al tiempo total
			else {
				var penalizaciones = parseFloat(document.timeform.penalizaciones'.$x.'.value) + segundos;
				timetotal'.$x.'.setMilliseconds(timetotal'.$x.'.getMilliseconds() + penalizaciones*1000);
				document.timeform.penalizaciones'.$x.'.value = penalizaciones;
			}
			document.timeform.tiempototal'.$x.'.value = convertir_msmm(timetotal'.$x.');
			$("#ok'.$x.'").attr("class", "fas fa-exclamation-triangle");
			save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);
		}
		
		function Abandona'.$x.'() {
			document.timeform.tiempototal'.$x.'.value = "Abandono";
			cambiaColorColumnas('.$x.', "abandona", document);
			$("#ok'.$x.'").attr("class", "fas fa-exclamation-triangle");
			save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);
		}
		
		function ResetAbandona'.$x.'() {
			timetotal'.$x.' = new Date();
			timetotal'.$x.'.setTime(timeend'.$x.');
			var penalizaciones = parseFloat(document.timeform.penalizaciones'.$x.'.value);
			timetotal'.$x.'.setMilliseconds(timetotal'.$x.'.getMilliseconds() + penalizaciones*1000);
			document.timeform.tiempototal'.$x.'.value = convertir_msmm(timetotal'.$x.');
			//document.timeform.tiempototal'.$x.'.value = document.timeform.timetextarea'.$x.'.value;
			cambiaColorColumnas('.$x.', "stop", document);
			$("#ok'.$x.'").attr("class", "fas fa-exclamation-triangle");
			save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);
		}

	');
}

// Genera el título de las columnas con los campos pasados por parámetro
function titulos_columnas($campos){
	echo ('<tr>');
	foreach($campos as $campo){
		echo ('<th>'.$campo.'</th>');
	}
	echo ('	<th>Categoría</th>
			<th>Tiempo</th>
			<th>Penalizaciones</th>
			<th>Total</th>
		  </tr>
		  ');
}

// Genera las filas con los datos de los pilotos inscritos
//	 $x : 			número de fila EMPIEZA EN 1 para que coincida con el id de la tabla MySQL donde se guardan los tiempos
//	 $campos :		array con los nombres de los campos a mostrar -- array(0=>'num', 1=>'nombre', 2=>'chasis')
//   $inscripcion   array con los datos de los pilotos (tabla signedup) -- $piloto['nombre'] = "Pepe"
function filas_crono($x, $campos, $inscripcion, $timetextarea, $penalizaciones, $tiempototal){
	echo ('<tr id="fila'.$x.'">');
	foreach($campos as $campo){
		echo ('<td id="col_campo_'.$campo.$x.'">
				<label for="'.$campo.$x.'">'.utf8_encode($inscripcion[$campo]).'</label>
				<input  type="hidden" id="'.$campo.$x.'" name="'.$campo.$x.'"value="'.$inscripcion[$campo].'" /></td>
		');
	}
	echo (' <td id="col_categoria'.$x.'">
				<input type="hidden" id="categoria'.$x.'" name="categoria'.$x.'" value="'.$inscripcion['categoria'].'">'.$inscripcion['categoria'].'</td>
			<td id="col_timetextarea'.$x.'">
				<input type="text" id="timetextarea'.$x.'" name="timetextarea'.$x.'" value="'.$timetextarea.'"><br>
				<input type="button" id="start" class="btn btn-primary btn-sm" name="start" value="Start" onclick="sw_start'.$x.'()">
				<input type="button" id="stop" class="btn btn-danger btn-sm" name="stop" value="Stop" onclick="Stop'.$x.'()">
				<input type="button" id="reset" class="btn btn-dark btn-sm" name="reset" value="Reset" onclick="Reset'.$x.'();Penaliza'.$x.'(0)"></td>
			<td id="col_penalizaciones'.$x.'">
				<input type="text" id="penalizaciones'.$x.'" name="penalizaciones'.$x.'" value="'.$penalizaciones.'"><br>
				<input type="button" id="cono" class="btn btn-info btn-sm" name="cono" value="Cono" onclick="Penaliza'.$x.'(0.5)">
				<input type="button" id="recorte" class="btn btn-info btn-sm" name="recorte" value="Recorte" onclick="Penaliza'.$x.'(5)">
				<input type="button" id="cono" class="btn btn-info btn-sm" name="fin" value="+1s" onclick="Penaliza'.$x.'(1)">
				<input type="button" id="p_reset" class="btn btn-dark btn-sm" name="p_reset" value="Reset" onclick="Penaliza'.$x.'(0)">
			</td>
			<td id="col_tiempototal'.$x.'">
				<input type="text" id="tiempototal'.$x.'" name="tiempototal'.$x.'" value="'.$tiempototal.'"><br>
				<input type="button" id="abandona" class="btn btn-danger btn-sm" name="abandona" value="Abandona" onclick="Abandona'.$x.'()">
				<input type="button" id="reset" class="btn btn-dark btn-sm" name="reset" value="Reset" onclick="ResetAbandona'.$x.'()">
				<input type="hidden" id="idsignedup'.$x.'" name="idsignedup'.$x.'" value="'.$inscripcion['idsignedup'].'" />
				<input type="button" name="enviar" class="btn btn-success btn-sm" value="Enviar" href="javascript:;" onclick="
				save('.$x.',
					 $(\'#idstage\').val(),
					 $(\'#idrally\').val(),
					 $(\'#stage\').val(),
					 $(\'#idsignedup'.$x.'\').val(),
					 $(\'#timetextarea'.$x.'\').val(),
					 $(\'#penalizaciones'.$x.'\').val(),
					 $(\'#tiempototal'.$x.'\').val()
					);">
			</td>
	  </tr>
	');
}
/* Simbolos check, warning, info
<input type="text" id="tiempototal'.$x.'" name="tiempototal'.$x.'" value="'.$tiempototal.'" onchange="$(\'#resultado\').html(\'desactualizado\')">
<i class="fas fa-info-circle" id="ok'.$x.'"></i>
<br>
*/
?>