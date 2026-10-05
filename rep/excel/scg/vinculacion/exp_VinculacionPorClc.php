<?php

session_start();

require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/TruncateCadena.php';
require_once '../../../../lib/MesNombre.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/vinculacion/vinculacionPorClc.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';

$ejercicioActual = APP_EJERCICIO;

// Marcos: 04/abr/2018
// Para que la 116 pueda ver de otras URs
if ($_SESSION[_S]['gpo_id'] == 1 and $_SESSION[_S]['ur_id'] == 16 and isset($_POST['ur']) && $_POST['ur'] != "") {
    $gpo_id = substr($_POST['ur'], 0, 1);
    $ur_id = substr($_POST['ur'], 1, 2);
} else {
    //todos los usuario de nivel 8 deberan obedecer a la clasificacion administrativa
    $gpo_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $ur_id = $_SESSION[_S]['nvj_id'] >= 3 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
}

$ur = new UnidadResponsable();
$ur->setGpo_id($gpo_id);
$ur->setUr_id($ur_id);
$ur->select();

$clcs = isset($_POST['clc']) ? $_POST['clc'] : 'NINGUNA';
$mes = isset($_POST['mes']) ? $_POST['mes'] : 0;

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL $ejercicioActual";
$encabLin3 = "UR: " . $ur->getClave() . " - " . $ur->getNombre();
$encabLin4 = '';
$encabLin5 = "RELACIÓN DE CLCs DEL MES DE " . MesNombre($mes);
$encabLin6 = '';

$datos = vinculacionPorClc($clcs, $mes, $gpo_id, $ur_id);

$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 5);
array_push($nombreColumnas, "TIPO");
array_push($incluirColumnas, "tcl_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "CLC");
array_push($incluirColumnas, "clc_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 35);
array_push($nombreColumnas, "CONCEPTO");
array_push($incluirColumnas, "con");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "BANCO");
array_push($incluirColumnas, "nb");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 21);
array_push($nombreColumnas, "CLABE INTERBANCARIA");
array_push($incluirColumnas, "ci");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "ORI");
array_push($incluirColumnas, "ori");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 11);
array_push($nombreColumnas, "CVEFIN");
array_push($incluirColumnas, "cf");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 22);
array_push($nombreColumnas, "MEDIO PAGO");
array_push($incluirColumnas, "mp");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "POLIZA");
array_push($incluirColumnas, "poliza");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 11);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "pma_fecha");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "EDO");
array_push($incluirColumnas, "epl_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 12);
array_push($nombreColumnas, "IMPORTE");
array_push($incluirColumnas, "importe");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "MC");
array_push($incluirColumnas, "mmc_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "APG");
array_push($incluirColumnas, "apg_id");
array_push($tipoColumnas, "c");

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['textoCompleto'] = true;
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
$tablaimg[0]['ruta'] = "../../../../img/escudocolor_derecha.png";

$tablaimg[0]['colImg'] = "K1";
$tablaimg[0]['alingXimg'] = "10";

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}
// Armamos los parametros opcionales
$encabezado = array(
    'encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas,
);
$otrosParametros = array(
    'comprimir' => false, 'nombre' => "Relación de CLCs",
    'sufijoNombre' => "-" . MesNombre($mes),
    'tablaimg' => $tablaimg,
);

$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
