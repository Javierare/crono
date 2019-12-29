<?php  
// Convierte la fecha de formato MySQL a datepicker y biceversa
function convertDate($rallydate){
	if(strstr($rallydate, "/")){
        // Convertir la fecha a formato MySQL
        $date = explode('/', $rallydate);
        $conv_date = $date[2].'-'.$date[1].'-'.$date[0];
        return $conv_date;
    } elseif(strstr($rallydate, "-")){
        // Convertir fecha a formato datapicker
        $date = explode('-', $rallydate);
        $conv_date = $date[2].'-'.$date[1].'-'.$date[0];
        return $conv_date;
    }
    return $rallydate;
}

function sumar_tiempos($tiempo1, $tiempo2){
	$minutos1 = substr($tiempo1, 0, 2);
	$minutos2 = substr($tiempo2, 0, 2);
	$segundos1 = substr($tiempo1, 3, 2);
	$segundos2 = substr($tiempo2, 3, 2);
	$milisegundos1 = substr($tiempo1, -3);
	$milisegundos2 = substr($tiempo2, -3);
	
	$milisegundos3 = intval($milisegundos1)+intval($milisegundos2);
	if($milisegundos3>999){
		$segundos_ext = 1;
		$milisegundos3 = $milisegundos3-1000;
	} else $segundos_ext = 0;
	$segundos3 = intval($segundos1)+intval($segundos2)+$segundos_ext;
	if($segundos3>59){
		$minutos_ext = 1;
		$segundos3 = $segundos3-60;
	} else $minutos_ext = 0;
	$minutos3 = intval($minutos1)+intval($minutos2)+$minutos_ext;
	
	return sprintf("%02d:%02d.%03d", $minutos3, $segundos3, $milisegundos3);
}
// Tiempo1 - tiempo2
function restar_tiempos($tiempo1, $tiempo2){
	if($tiempo2>$tiempo1) return false; // si el tiempo2 > tiempo1 se devuelve error
	$minutos1 = substr($tiempo1, 0, 2);
	$minutos2 = substr($tiempo2, 0, 2);
	$segundos1 = substr($tiempo1, 3, 2);
	$segundos2 = substr($tiempo2, 3, 2);
	$milisegundos1 = substr($tiempo1, -3);
	$milisegundos2 = substr($tiempo2, -3);
	
	if($milisegundos1<$milisegundos2) {
		$milisegundos1 = "1" . $milisegundos1;
		$segundos_ext = 1;
	} else $segundos_ext = 0;
	$milisegundos3 = intval($milisegundos1)-intval($milisegundos2);
	
	$segundos2 = intval($segundos2) + $segundos_ext;
	if($segundos1<$segundos2){
		$segundos3 = 60 - (intval($segundos2) - intval($segundos1));
		$minutos_ext = 1;
	} else {
		$segundos3 = intval($segundos1)-intval($segundos2);
		$minutos_ext = 0;
	}
	$minutos2 = intval($minutos2) + $minutos_ext;
	$minutos3 = intval($minutos1)-intval($minutos2);
	
	if($minutos3 == 0) return sprintf("%02d.%03d", $segundos3, $milisegundos3);
	else return sprintf("%02d:%02d.%03d", $minutos3, $segundos3, $milisegundos3);
}
?> 