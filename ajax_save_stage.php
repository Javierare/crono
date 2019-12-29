<?php
session_start();
include("database.php");
if($_SESSION['permission'] != ADMIN) {
    exit;
}
if(isset($_POST["idrally"]) AND 
    isset($_POST["idstage"]) AND 
    isset($_POST["stage"]) AND 
    isset($_POST["idsignedup"]) AND 
    isset($_POST["time"]) AND 
    isset($_POST["penalties"]) AND 
    isset($_POST["totaltime"]))
    {
        $data['idrally'] = $_POST["idrally"];
        $data['idstage'] = $_POST["idstage"];
        $data['stage'] = $_POST["stage"];
        $data['idsignedup'] = $_POST["idsignedup"];
        $data['time'] = $_POST["time"];
        $data['penalties'] = $_POST["penalties"];
        $data['totaltime'] = $_POST["totaltime"];
        if($res = consulta("stages", "idstage=".$_POST["idstage"]." AND idrally=".$_POST["idrally"]." AND idsignedup=".$_POST["idsignedup"])){
            if(modificar("stages", $data, $res["id"])) echo ("Modificado id: ".$res["id"]);
            else echo ("No modificado id: ".$res["id"]);
        } else {
            if($id = insertar("stages", $data)) echo ("Insertado id: ".$id);
            else echo ("No insertado");
        }
    }
// Llamada desde admin para guardar los num de la lista de inscritos
// y para cerrar las inscripciones
if(isset($_POST["startRally"])) {
    $signedups = consulta_multi("signedup", "idrally=".$_POST["idrally"]);
    foreach($signedups as $signedup){
        $data['num'] = $_POST[$signedup['id']];
        modificar("signedup", $data, $signedup['id']);
    }
    $data = array("open"=>0, "deputy"=>1);
    modificar("rallies", $data, $_POST["idrally"]);
    // Empezamos el rally
    header ('Location: crono.php?idrally='.$_POST["idrally"]);
}
?>