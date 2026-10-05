<?php
require_once dirname(__FILE__).'/../../RutaArchivoSefip.php';
require_once dirname(__FILE__).'/../../AccesoDatos.php';
$trimestre = ( isset($_POST['tmsc_id']) ? $_POST['tmsc_id'] : '');
$rep_id = ( isset($_POST['rep_id']) ? $_POST['rep_id'] : '');
$tipo = ( isset($_POST['tipo']) ? $_POST['tipo'] : '');
$ent_id = ( isset($_POST['ent_id']) ? $_POST['ent_id'] : 0);
$ent_idC = ( isset($_POST['ent_id']) ? $_POST['ent_id'] : "");
$gpo_idC = ( isset($_POST['gpo_id']) ? $_POST['gpo_id'] : "");
$sec_idC = ( isset($_POST['sec_id']) ? $_POST['sec_id'] : "");
$con_idC = ( isset($_POST['con_id']) ? $_POST['con_id'] : "");

$consolidado = ( isset($_POST['consolidado']) ? $_POST['consolidado'] : false);


if ($tipo == 1) {
    $extension = "pdf";
} else {
    $extension = "xlsx";
}

$nombre_Comp = "";
$carpeta = "ldf/T".$trimestre;
if($consolidado){
    if($ent_idC != ""){
        $nombre_Comp = "N2E".$ent_idC;
    }
    if($gpo_idC != ""){
        $nombre_Comp = "N1G".$gpo_idC;
    }
    if($sec_idC != ""){
        $nombre_Comp = "N3S".$sec_idC;
    }
    if($con_idC != ""){
        $nombre_Comp = "N4G".$con_idC;
    }
    $carpeta = "ldf/consolidados/T".$trimestre;
    $ur_clave = 115;
}else{
    $sql = "
        SELECT gpo_id, ur_id
        FROM ldf. vin_EntePublicoLDF
        WHERE ent_id = $ent_id AND vin_generaReporte = 1
        LIMIT 1
    ";
    
    $consultaEnt = new AccesoDatos();
    $consultaEnt->conectar();
    $consultaEnt->setSql($sql);
    $consultaEnt->consultar(); 
    $consultaEnt->desconectar();
    if ($consultaEnt->getNumeroRegistros()>0){
        $row=$consultaEnt->extraer();
       $ur_clave = $row['gpo_id'].$row['ur_id'];
    }

}
if ($rep_id == 1) {
    if ($consolidado) {
        $nombre_archivo = 'ConsolidadoEstadoSituacionFinanciera'.$nombre_Comp."T".$trimestre.".".$extension;
    } else {

        $nombre_archivo = 'EstadoSituacionFinancieraE'.$ent_id."T".$trimestre.".".$extension;
    }
}
if ($rep_id == 6) {
    if ($consolidado) {
        $nombre_archivo = 'ConsolidadoEstadoAnalitico6A'.$nombre_Comp."T".$trimestre.".".$extension;
    } else {

        $nombre_archivo = 'EstadoAnalitico6AE'.$ent_id."T".$trimestre.".".$extension;
    }
}
if ($rep_id == 7) {
    if ($consolidado) {
        $nombre_archivo = 'ConsolidadoEstadoAnalitico6B'.$nombre_Comp."T".$trimestre.".".$extension;
    } else {

        $nombre_archivo = 'EstadoAnalitico6BE'.$ent_id."T".$trimestre.".".$extension;
    }
}

$rutatem = dirname(__FILE__)."/../../../archivostemporalesPHPpreadsheet/";
$exists = is_dir($rutatem);
if (!$exists) {
    mkdir($rutatem, 0777, true);
}
$r = new RutaArchivoSefip();
$r->setBase("scg");
$r->setPath($carpeta);
$r->setAnioFichero(2020); 
$r->setUrFichero($ur_clave);
$ruta = $r->getRutaMod();
$ruta .= $nombre_archivo;
is_file($ruta);
//echo $ruta;
if (is_file($ruta)) {
    copy($ruta, $rutatem.$nombre_archivo);
} else {
    echo "no existe";
    die();
}


if (file_exists($rutatem.$nombre_archivo)) {

    if ($tipo == 1) {

        header('Content-Type: application/pdf');
        header("Content-Disposition: inline; filename= \"$nombre_archivo\" ");
        ob_end_clean();
        flush();
        readfile($rutatem.$nombre_archivo);
        unlink($rutatem.$nombre_archivo);
        exit();
    } else {
        $type = mime_content_type($rutatem.$nombre_archivo);
        $size = filesize($rutatem.$nombre_archivo);

        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Disposition: attachment; filename= \"$nombre_archivo\" ");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$size);
        // Download File
        ob_get_clean();
        //ob_end_clean();
        //flush();
        readfile($rutatem.$nombre_archivo);
        ob_end_flush();
        unlink($rutatem.$nombre_archivo);
        exit();
    }
}
?>
