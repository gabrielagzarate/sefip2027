<?php
declare (strict_types = 1);
session_start();
require_once '../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . "/../../../lib/generaPhpspreadsheet.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/catalogo/mesContable.php";

$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];
if ($_SESSION[_S]['nvj_id'] <= 2) {
    $gpo = $_SESSION[_S]['gpo_id'];
    $ur = $_SESSION[_S]['ur_id'];
    $ur_ent = $gpo . $ur;
} else {

    $ur = $_SESSION[_S]['clasAdm-ur'];
    $gpo = $_SESSION[_S]['clasAdm-gpo'];
    $ur_ent = $gpo . $ur;
}

$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];
$nivel = $_SESSION[_S]['nvj_id'];

$lectura = isset($_SESSION[_S]['prm']['C']['7']['2']['1']) ? true : false;
if ($nivel < 2) {
    $escritura = false;
} else {
    $escritura = isset($_SESSION[_S]['prm']['C']['7']['2']['2']) ? true : false;
}

$miconexion = new AccesoDatos();
$miconexion->setSql('select ur_nombre from cat_UnidadResponsable where ur_clave= :ur;');
$params = [':ur' => $ur_ent];
$miconexion->conectar();
$miconexion->consultar($params);
$miconexion->desconectar();
$row = $miconexion->extraer();
$nom_ur = $row['ur_nombre'];

$hoy = date("Y-m-d H:i:s");

$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];
$nivel = $_SESSION[_S]['nvj_id'];
$usr_id = $_SESSION[_S]['usr_id'];

// Datos a mostrar en el reporte
$datos = mesContable($gpo_id, $ur_id, $nivel, $usr_id);

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA";
$encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";

if ($nivel < 3) {
    $encabLin4 = $gpo . $ur . ' - ' . $nom_ur;
} else {
    $encabLin4 = "";
}

$encabLin5 = "";
$encabLin6 = "";
$encabLin7 = "";

$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

if ($nivel > 2) {

    array_push($nombreColumnas, "UR");
    array_push($incluirColumnas, "gpour_id");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 10);

    array_push($nombreColumnas, "APERTURA");
    array_push($incluirColumnas, "ems_nombre0");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "ENERO");
    array_push($incluirColumnas, "ems_nombre1");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "FEBRERO");
    array_push($incluirColumnas, "ems_nombre2");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "MARZO");
    array_push($incluirColumnas, "ems_nombre3");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "ABRIL");
    array_push($incluirColumnas, "ems_nombre4");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "MAYO");
    array_push($incluirColumnas, "ems_nombre5");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "JUNIO");
    array_push($incluirColumnas, "ems_nombre6");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "JULIO");
    array_push($incluirColumnas, "ems_nombre7");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "AGOSTO");
    array_push($incluirColumnas, "ems_nombre8");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "SEPTIEMBRE");
    array_push($incluirColumnas, "ems_nombre9");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "OCTUBRE");
    array_push($incluirColumnas, "ems_nombre10");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "NOVIEMBRE");
    array_push($incluirColumnas, "ems_nombre11");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "DICIEMBRE");
    array_push($incluirColumnas, "ems_nombre12");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);

    array_push($nombreColumnas, "CIERRE");
    array_push($incluirColumnas, "ems_nombre13");
    array_push($tipoColumnas, "cC");
    array_push($anchoColumnas, 15);
} else {

    array_push($anchoColumnas, 25);
    array_push($nombreColumnas, "MES");
    array_push($incluirColumnas, "msc_nombre");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 25);
    array_push($nombreColumnas, "FECHA APERTURA");
    array_push($incluirColumnas, "abierto");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 25);
    array_push($nombreColumnas, "FECHA CIERRE");
    array_push($incluirColumnas, "cerrado");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "ESTADO");
    array_push($incluirColumnas, "ems_nombre");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 25);
    array_push($nombreColumnas, "USUARIO");
    array_push($incluirColumnas, "usr_usuario");
    array_push($tipoColumnas, "c");
}

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    if (isset($anchoColumnas[$key])) {
        $table['ancho'][$val] = $anchoColumnas[$key];
    }
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";

$tablaimg[0]['ruta'] = "../../../../img/" . LOGO_COLOR;
$tablaimg[0]['colImg'] = "L1";
$tablaimg[0]['alingXimg'] = "80";

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}

$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => false, 'nombre' => "MesContable", 'tablaimg' => $tablaimg);

$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
