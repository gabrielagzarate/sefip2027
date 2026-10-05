<?php
session_start();
set_time_limit(0);
ini_set('memory_limit','500M');
require_once dirname(__FILE__).'/../../../../lib/constantes.php';
require_once dirname(__FILE__).'/../../../../lib/WebResponse.php';
require_once dirname(__FILE__).'/../../../../rep/lib/zip.php';
require_once dirname(__FILE__).'/../../../../lib/poa/reporte/URsConsolidadoAvanceGestion.php';
require_once dirname(__FILE__).'/convertirExcelHtmlACsv.php';

$usr_id = isset($_SESSION[_S]['usr_id']) ? true : false;
if ($usr_id == false){
    echo "Ocurrio un error, intente de nuevo";
    die();
}

$aplicacion = ((isset($_POST['tipo']) && $_POST['tipo'] != "" ) ? $_POST['tipo'] : "");
$trimestre = $_POST['capturandoTri'];
$anio = APP_EJERCICIO;
$nombre = "AvanceGestionT".$trimestre."_".$anio."_";

$dataURs = URsConsolidadoAvanceGestion();

$enlace_actual = "";
switch ($aplicacion) {
    case "pdf":
        $enlace_actual = $_SERVER['HTTP_REFERER']."/rep/doc/poa/rep_AvanceMasivo.php";
        break;
    case "excel":
        $enlace_actual = $_SERVER['HTTP_REFERER']."/rep/excel/poa/exp_AvanceMasivo.php";
        break;
    default : $enlace_actual = "";
}

$opciones = $_POST;
$zip = new zip();

foreach ($dataURs as $key => $val) {

    $opciones['gpo_id'] = substr($val, 0, 1);
    $opciones['ur_id'] = substr($val, -2);
    $opciones['capturandoTri'] = $trimestre;
    $postdata = http_build_query($opciones);

    $opts = array
        (
        'http' => array
            (
            'method' => 'POST',
            'header' => 'Cookie: '.$_SERVER['HTTP_COOKIE']."\r\n".'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata
        ),
        "ssl" => array
            (
            "verify_peer" => false,
            "verify_peer_name" => false
        )
    );

    $context = stream_context_create($opts);
    session_write_close(); // unlock the file
    $pdf = file_get_contents($enlace_actual, false, $context);
    session_start(); // Lock the file
    $ur = $val;

    if ($pdf !== false) {
        switch ($aplicacion) {
            case "pdf":
                $extension = ".pdf";
                $formato = "pdf";
                 $zip->add_fileFromString($pdf,  "PDF/".$nombre.$ur.$extension);
                break;
            case "excel":
                $extension = ".xls";
                $formato = "excel_csv";
                $csvContenido = convertirExcelHtmlACsv($pdf);
                $zip->add_fileFromString($pdf, "EXCEL/".$nombre.$ur.$extension);
                $zip->add_fileFromString($csvContenido, "CSV/".$nombre.$ur.".csv");
                break;
            default: 
                $extension = ".txt";
                break;
        }
    } else {
        echo "ocurrio un error al recuperar el fichero 2 ".$pdf;
    }
}
header("Content-type: application/force-download");
header("Content-Transfer-Encoding: Binary");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=".$nombre.$formato.".zip");
echo $zip->file();
?>
