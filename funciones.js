// Función que comunica con página que procesa las inserciones en la BBDD
// PARAMETROS ENTRADA:
// fila:    número de fila en el formulario de tiempos (se añade a todos los ideentificadores)
// idstage, idrally, stage, idsignedup, time, penalties, totaltime: son los datos para guardar en la BBDD
// SALIDA:
// response: es el id de la fila insertada o modificada o "no" si falla
function save(fila, idstage, idrally, stage, idsignedup, time, penalties, totaltime) {
	var parametros = {"idstage":idstage,"idrally":idrally, "stage":stage, "idsignedup":idsignedup, "time":time, "penalties":penalties, "totaltime":totaltime};
	$.ajax({
		data:parametros,
		url:'ajax_save_stage.php',
		type: 'post',
		beforeSend: function () {
			$("#ok"+fila).attr("class", "fas fa-exclamation-triangle");
		},
		success: function (response) {
			$("#ok"+fila).attr("class", "fas fa-check"); // flag que indica que se ha guardado ok
			$("#fila"+fila).attr("class", "table-success");
			//$("#resultado").html(response); // info en pantalla de operación
		}
	});
}

// Función que valida el formulario
function validar(timeform, accion) {
  var penalizacion = 5; // De momento la misma penalización para todas las categorías
  // Comprobar que no hay ningún piloto con tiempo 00:00
  if(camposCero()){ 
  	return false;
  }
  // Obtiene las categorías del timeform
  var categorias = get_categorias();

  categorias.forEach(findAbandonos);
  function findAbandonos(categoria){
	// Calcula las penalizaciones por abandono en cada categoria
	setAbandonos(categoria, penalizacion);	  
  }
  document.timeform.action = accion;
  document.timeform.submit();
}

function validar_zround(timeform){
	document.timeform.action = "load_zround";
	document.timeform.submit();
}

/*function recarga(timeform){
	document.timeform.action = "crono";
	document.timeform.submit();
}*/
// Obtiene las categorias del timeform
function get_categorias(){
	var inscritos = document.getElementById("n_pilotos").value;
	var categorias = new Array();
	for(var i=1; i<= inscritos; i++){
		var categoria = document.getElementById("categoria"+i).value;
		existe = false;
		for (var x = 0; x < categorias.length; x+=1) {
			if(categorias[x] == categoria){
				existe = true;
				break;
			}
		}
		if(existe==false) categorias[categorias.length] = categoria;
	}
	return categorias;
}
function camposCero() {
	var resultado = false;
	var tiempos = new Array();
	//var msg = "Los siguientes pilotos no han corrido:\n";
	var inscritos = document.getElementById("n_pilotos").value;
	for(var i=1; i<= inscritos; i++){
		tiempos[i] = document.getElementById("tiempototal"+i).value;
		if(tiempos[i] == "00:00"){
			resultado = true;
			msg = "Quedan pilotos sin correr o abandonar.";
		}
	}
	if(resultado) {
		alert (msg);
		return false;
	}
	return resultado;
}

function setAbandonos(categoria, penalizacion){
	var inscritos = document.getElementById("n_pilotos").value;
	var cont = 0; // contador para camposTiemposCero
	var camposAbandono = new Array(); //Se almacenan en este array los campos con tiempo "Abandono"
	var tiempos = new Array();  // Se almacenan los tiempos para buscar el tiempo mayor
	var tiempoMayor = "00:00";   
	// Se recorren los valores de los campos "tiempototal"
	for(var i=1; i<= inscritos; i++){
		if(categoria == document.getElementById("categoria"+i).value) {
			tiempos[i] = document.getElementById("tiempototal"+i).value;
			if(tiempos[i] == "Abandono") {
				document.getElementById("timetextarea"+i).value = "Abandona";
				// Se almacenan los punteros a los campos que tienen valor = Abandono
				camposAbandono[cont] = document.getElementById("tiempototal"+i); 
				cont++;
			} else {
				if(tiempos[i] > tiempoMayor) tiempoMayor = tiempos[i]; // Se almacena en tiempos[] el tiempo mayor
			}
		}
	}
	// Si existe algún elemento del array que tiene los campos con Abandono
	// se le coloca la penalización (tiempo mayor más 5 seg.)
	if(cont > 0){
		var strMinutos = tiempoMayor.substr(0, 2);
		var minutos = parseInt(strMinutos);
		var strSegundos = tiempoMayor.substr(3, 2);
		var segundos = parseInt(strSegundos) + penalizacion; // Tiempo mayor + 5 seg.
		if(segundos>60) {
			segundos = segundos-60;
			minutos++;
		}
		if(segundos<10) strSegundos = "0" + segundos.toString();
		else strSegundos = segundos.toString();
		if(minutos<10) strMinutos = "0" + minutos.toString();
		else strMinutos = "0" + minutos.toString();
		
		var penalizacion = strMinutos + ":" + strSegundos + tiempoMayor.substr(-4);
		
		for(var x=0; x<cont; x++){
			camposAbandono[x].value = penalizacion;
		}
	}
}

function convertir_msmm(timeend){
	var minutes_passed = timeend.getMinutes();
	if(minutes_passed < 10){
		minutes_passed = "0" + minutes_passed;
	}
	var seconds_passed = timeend.getSeconds();
	if(seconds_passed < 10){
		seconds_passed = "0" + seconds_passed;
	}
	var milliseconds_passed = timeend.getMilliseconds();
	if(milliseconds_passed < 10){
		milliseconds_passed = "00" + milliseconds_passed;
	}
	else if(milliseconds_passed < 100){
		milliseconds_passed = "0" + milliseconds_passed;
	}
	return minutes_passed + ":" + seconds_passed + "." + milliseconds_passed;
}

// Cambia el color de las filas dependiendo del estado del piloto en el tramo
function cambiaColorColumnas(fila, evento, documento) {
	if(evento == "sw_start"){
		documento.getElementById("fila"+fila).className = "table-primary";
	}
	if(evento == "stop"){
		documento.getElementById("fila"+fila).className = "table-warning";
	}
	if(evento == "reset"){
		documento.getElementById("fila"+fila).className = "";
	}
	if(evento == "abandona"){
		documento.getElementById("fila"+fila).className = "table-warning";
	}
}

