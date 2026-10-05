<?php
session_start();
set_time_limit(0);
ini_set('memory_limit','500M');
require_once dirname(__FILE__).'/../../../../lib/constantes.php';
require_once dirname(__FILE__).'/../../../../lib/WebResponse.php';
require_once dirname(__FILE__).'/../../../../rep/lib/zip.php';
/* TODA LA LA PARATE DE ESTA PANTALLA ES CASI LO MISMO QUE EL REPORTE CON ALGUNAS EXCEPCIONES QUE SE COMENTARA PERO EL PROCESAMIENTO DE LA INFROMACION
  A IMPRIMIR ES LA MISMA QUE EL RESPORTE EN PDF. */
require_once dirname(__FILE__).'/../../../../lib/scg/reporte/cuentasRegistro.php';




$gpo_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
$ur_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
$ue_id = '';
$ue_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ue_id'] : "%";
$deue = ((isset($_POST['comboue']) && $_POST['comboue'] != "" ) ? $_POST['comboue']
        : "");
$ctainicial = ((isset($_POST['cta']) && $_POST['cta'] != "" ) ? $_POST['cta']
        : "");
$ctafinal = ((isset($_POST['cta2']) && $_POST['cta2'] != "" ) ? $_POST['cta2']
        : "");
$aplicacion = ((isset($_POST['tipo_rep']) && $_POST['tipo_rep'] != "" ) ? $_POST['tipo_rep']
        : "");

if ($aplicacion == ""){
    echo "favor de intentar de nuevo";
    die();
}
if ($_SESSION[_S]['nvj_id'] == 1) $ue_id = $_SESSION[_S]['ue_id'];
else {
    $ue_id = ( ( $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'] == 116
        && isset($_SESSION[_S]['clasAdm-ue']) && !is_null($_SESSION[_S]['clasAdm-ue']) )
            ? $_SESSION[_S]['clasAdm-ue'] : '%');
}

if ($deue != "" && $ue_id == '%') {
    $ue_id = $deue;
}
$cuentas_Reg = cuentasRegistro($gpo_id, $ur_id, $ue_id, $ctainicial, $ctafinal);

$enlace_actual = "";
       switch ($aplicacion) {
            case "pdf":
                $enlace_actual = $_SERVER['HTTP_REFERER']."/rep/doc/scg/reporte/reporte.php";
                break;
            case "excel":
                $enlace_actual = $_SERVER['HTTP_REFERER']."/rep/excel/scg/reporte/exp_Auxiliar.php";
                break;
            default : $enlace_actual = "";
        }
$opciones = $_POST;

$zip = new zip();

foreach ($cuentas_Reg as $key => $val) {

    $opciones['ctareg_id'] = $val;


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
    if ($pdf !== false) {
        switch ($aplicacion) {
            case "pdf":
                $extension = ".pdf";
                $formato = "pdf";
                break;
            case "excel":
                $extension = ".xls";
                $formato = "excel";
                break;
            default : $extension = ".txt";
        }
    } else {
        echo "ocurrio un error al recuperar el fichero 2 ".$pdf;
    }
   $nombre = str_replace(".", "_", $val);
   
    $zip->add_fileFromString($pdf, "auxiliar".$nombre.$extension);
    
}
header("Content-type: application/force-download");
header("Content-Transfer-Encoding: Binary");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=reportes_auxiliares_formato_".$formato.".zip");
echo $zip->file();
?>
	