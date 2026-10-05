<?php
session_start();
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));
set_time_limit(0);

require_once dirname(__FILE__).'/../../../../lib/scg/consulta/DameLibroDiario.php';
require_once dirname(__FILE__).'/../../../../lib/MesNombre.php';
require_once dirname(__FILE__).'/../../../../lib/redondear.php';
require_once dirname(__FILE__).'/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__).'/../../../../lib/calculaPeso.php';
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
// Iniciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------
$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];
$ur_ent = '';

$nivel = $_SESSION[_S]['nvj_id'];
if ($nivel <= 2) {

    $ur_ent = $_SESSION[_S]['gpo_id'].$_SESSION[_S]['ur_id'];
} else {

    $ur_ent = $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'];
}
// Si NO mandan el parámetro pot, es que es todo el mes, y marcaremos porque pondremos
// en vacío ciertos parámetros aunque existan, ya que se permite ahora más filtros

$msc_id = ( isset($_POST['msc']) && $_POST['msc'] != "" ) ? $_POST['msc'] : 1;
$orden = ( isset($_POST['ord']) && $_POST['ord'] != "" ) ? $_POST['ord'] : 'or_T';
$tipo = ( isset($_POST['pot']) && $_POST['pot'] != "" ) ? $_POST['pot'] : '';
$numero = ( isset($_POST['num']) && $_POST['num'] != "" ) ? $_POST['num'] : '';
$vinculacion = ( isset($_POST['vin']) && $_POST['vin'] != "" ) ? $_POST['vin'] : '';
$ue = ( isset($_POST['ue']) && $_POST['ue'] != "" ) ? $_POST['ue'] : '';
$tipoCLC = ( isset($_POST['clc']) && $_POST['clc'] != "" ) ? $_POST['clc'] : '';
$usuario = ( isset($_POST['usr']) && $_POST['usr'] != "" ) ? $_POST['usr'] : '';
$textoBuscar = ( isset($_POST['tB']) && $_POST['tB'] != "" ) ? $_POST['tB'] : '';

// Para la vinculación ( pdx_*_origen )
$clc_v = trim(( ( isset($_POST['clc_v']) ) ? $_POST['clc_v'] : ''));
$afe_v = trim(( ( isset($_POST['afe_v']) ) ? $_POST['afe_v'] : ''));
$cmp_v = trim(( ( isset($_POST['cmp_v']) ) ? $_POST['cmp_v'] : ''));
$bdo_v = trim(( ( isset($_POST['bdo_v']) ) ? $_POST['bdo_v'] : ''));

// Para la vinculación ( pdx_*_vinculacion )
$clc_o = trim(( ( isset($_POST['clc_o']) ) ? $_POST['clc_o'] : ''));
$afe_o = trim(( ( isset($_POST['afe_o']) ) ? $_POST['afe_o'] : ''));
$cmp_o = trim(( ( isset($_POST['cmp_o']) ) ? $_POST['cmp_o'] : ''));
$bdo_o = trim(( ( isset($_POST['bdo_o']) ) ? $_POST['bdo_o'] : ''));

// La referencia sólo es una
$ref_v = trim(( ( isset($_POST['ref_v']) ) ? $_POST['ref_v'] : ''));

$andOr = trim(( ( isset($_POST['andOr']) ) ? $_POST['andOr'] : '0'));
$andOr = ( $andOr == "0" ? "AND" : "OR" );

$filtroTexto = trim(( ( isset($_POST['txt']) ) ? $_POST['txt'] : ''));

// Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
// NO limitamos por tipo de póliza
if ($clc_v != "" || $afe_v != "" || $cmp_v != "" || $bdo_v != "" || $clc_o != ""
    || $afe_o != "" || $cmp_o != "" || $bdo_o != "" || $ref_v != "") {
    $bolPidieronVinculacion = true;
} else {
    $bolPidieronVinculacion = false;
}
$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = $_SESSION[_S]['ur_nombre'];
$encabLin3 = 'LIBRO DIARIO CORRESPONDIENTE AL MES DE '.MesNombre($msc_id).' DE '.substr($_SESSION[_S]['ejercicio'],
        0, 4);



if ($_SESSION[_S]['clasAdm-gpo'] == 1 and $_SESSION[_S]['clasAdm-ur'] == 16) {
    $masDatos = ", CONCAT( da.pdx_gpo_id_origen, da.pdx_ur_id_origen ) AS urOri, da.pdx_clc_cereCo_vinculacion AS cereCo";
} else {
    $masDatos = "";
}
$datos = DameLibroDiario($msc_id, $orden, $tipo, $numero, $vinculacion, $ue,
    $tipoCLC, $usuario, $textoBuscar, $clc_v, $afe_v, $cmp_v, $bdo_v, $clc_o,
    $afe_o, $cmp_o, $bdo_o, $ref_v, $andOr, $bolPidieronVinculacion,
    $filtroTexto);

$anchoColumnas = array(10, 6, 8, 8, 80,
    15, 15, 8, 10, 8,
    11, 8, 25, 10);
$nombreColumnas = array("PÓLIZA", "UE", "MES", "DIA", "CONCEPTO",
    "DEBE", "HABER", "MC", "ESTADO MC", "ESTADO",
    "EJERCICIO", "CLC", "CLAVE DE FINANCIAMIENTO", "USUARIO");

$incluirColumnas = array("poliza", "ue", "mesPol", "dia", "concepto",
    "debe", "haber", "mc", "tmc", "edo",
    "eje", "clc", "ecf", "u");

$tipoColumnas = array("c", "c", "c", "c", "c",
    "n", "n", "c", "c", "c",
    "c", "c", "c", "c");
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'ancho' => $anchoColumnas,'alineacionenc' => 'I');

if ($_SESSION[_S]['clasAdm-gpo'] == 1 and $_SESSION[_S]['clasAdm-ur'] == 16) {
    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "UR ORI");
    array_push($incluirColumnas, "urOri");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 20);
    array_push($nombreColumnas, "CERE-CO VIN");
    array_push($incluirColumnas, "cereCo");
    array_push($tipoColumnas, "c");
}

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";

    $tablaimg[0]['ruta'] = "../../../../img/logoOaxaca-color-fblanco_v2.png";
    $tablaimg[0]['colImg'] = "L1";
    $tablaimg[0]['alingXimg'] = "80";


$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}
$otrosParametros = array('nombre' => "LibroDiario", 'sufijoNombre' => "_".$msc_id."_".substr($_SESSION[_S]['ejercicio'],
        0, 4), 'tablaimg' => $tablaimg);

$NUMREG = count($datos);
$columnas = count(array_values($table)[0]) * 2;
$peso = calcularPeso($NUMREG, $columnas);

ini_set('memory_limit', ($peso)."M");
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado,  $table, $otrosParametros);