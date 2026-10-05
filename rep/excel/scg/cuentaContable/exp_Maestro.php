<?php
session_start();

// Marcos: 20/02/2017
// Por cada "carpeta" de las opciones de la contabilidad, hay que crear un PHP
// para manejar las exportaciones a Excel
//librerias
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';

// Para obtener la consulta y los datos a exportar
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/cuentaContable/cuentaContable.php';

$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();
$nivel = $_SESSION[_S]['nvj_id'];
if (isset($_SESSION[_S]['clasAdm-ue'])) {
    $ue = new UnidadEjecutora();
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
    $ue->select();
} elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
    $ue = new UnidadEjecutora();
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id(trim($_POST['ue']));
    $ue->select();
} else {
    $ue = false;
}

// ------------------------------------------------------------------------------------------------------------
// Manejo de los parámetros enviados al reporte...
// ------------------------------------------------------------------------------------------------------------
$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

$seccion = trim(((isset($_POST['seccion']) && trim($_POST['seccion']) != '') ? $_POST['seccion']
    : "*** ERROR ***"));
$buscar = (isset($_POST['buscar']) ? $_POST['buscar'] : "");
$mayor = (isset($_POST['mayor']) ? $_POST['mayor'] : "");
$desde = (isset($_POST['desde']) ? $_POST['desde'] : "");
$hasta = (isset($_POST['hasta']) ? $_POST['hasta'] : "");

// ------------------------------------------------------------------------------------------------------------
// Encabezados del reporte
// ------------------------------------------------------------------------------------------------------------
$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL ". APP_EJERCICIO;

// Si lo están imprimiendo desde finanzas...
if ($_SESSION[_S]['nvj_id'] > 2) {
    $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL: " . $ur->getClave() . " - " . $ur->getNombre();
    $encabLin6 = '';
    // Si lo están imprimiendo desde la UR...
} else {
    $encabLin3 = "UR: " . $ur->getClave() . " - " . $ur->getNombre();
    $encabLin6 = '';
}

if ($ue) {
    $encabLin4 = "UE: " . $ue->getUe_id() . " - " . $ue->getNombre();
} else {
    $encabLin4 = '';
}
$encabLin5 = 'CATÁLOGO MAESTRO';
$encabLin7 = '';

// ------------------------------------------------------------------------------------------------------------
// Obtenemos los datos a imprimir, idealmente lo haremos desde el mismo lugar de donde los obtenemos para el PDF
// ------------------------------------------------------------------------------------------------------------
$datos = dameCatalogoMaestro($seccion, $buscar, $mayor, $desde, $hasta);

// ------------------------------------------------------------------------------------------------------------
// Formateamos las columnas con sus encabezados, tipo de dato y ancho
// ------------------------------------------------------------------------------------------------------------
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "CUENTA");
array_push($incluirColumnas, "cuenta");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 85);
array_push($nombreColumnas, "NOMBRE");
array_push($incluirColumnas, "nombre");
array_push($tipoColumnas, "c");

// Para cada "pestaña" debería haber una definición de $table() con los datos que le aplicarán
$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";

$tablaimg[0]['ruta'] = "../../../../img/".LOGO_COLOR;

$tablaimg[0]['alingXimg'] = "255";
$tablaimg[0]['colImg'] = "B1";

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
    
}
// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => false, 'nombre' => $encabLin5, 'tablaimg' => $tablaimg);

$excel = new generaPhpspreadsheet();

$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
