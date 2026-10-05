<?php
session_start();

//librerias
require_once dirname(__FILE__) . "/../../../../ent/UnidadResponsable.php";
require_once dirname(__FILE__) . "/../../../../ent/UnidadEjecutora.php";
require_once dirname(__FILE__) . "/../../../../ent/enc_ClaveFinanciamiento.php";
require_once dirname(__FILE__) . "/../../../../ent/enc_Programatico.php";
require_once dirname(__FILE__) . "/../../../../lib/redondear.php";
require_once dirname(__FILE__) . "/../../../../lib/MesNombre.php";
require_once dirname(__FILE__) . "/../../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/banco/banco.php";
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';

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

$ue_id = ($ue ? $ue->getUe_id() : false);

$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

$periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != "") ? $_POST['comboMesDesde'] : -1);
$periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != "") ? $_POST['comboMesHasta'] : -1);

$niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != "") ? $_POST['checkUR'] : 1);
$niveles[2] = ($ue ? $ue->getUe_id() : -1);
$niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != "") ? $_POST['checkEP'] : -1);
$niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != "") ? $_POST['checkTG'] : -1);
$niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != "") ? $_POST['checkIP'] : -1);
$niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != "") ? $_POST['checkPG'] : -1);
$niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != "") ? $_POST['checkPE'] : -1);
$niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != "") ? $_POST['checkTR'] : -1);
$niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != "") ? $_POST['checkCF'] : -1);

$niveles[0] = ($niveles[9] > 0) ? 9 : (
    ($niveles[8] > 0) ? 7 : (
        ($niveles[7] > 0) ? 7 : (
            ($niveles[6] > 0) ? 6 : (
                ($niveles[5] > 0) ? 5 : (
                    ($niveles[4] > 0) ? 4 : (
                        ($niveles[3] > 0) ? 3 : (
                            ($niveles[2] > 0) ? 2 : 1
                        )
                    )
                )
            )
        )
    )
);

$reporte['tipo'] = ((isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : 1);
$reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : "";
$reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
$reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : "";

$estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? true : false;
$federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? true : false;
$claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
$claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A' : '')));

if ($niveles[3] > 0 && $reporte['tipo'] == 1) {
    $programatico['epg_id'] = isset($_POST['programatico']) ? $_POST['programatico'] : '';
} else {
    $programatico['epg_id'] = '';
}

$pot_id = isset($_POST['pot_id']) ? $_POST['pot_id'] : false;

switch ($pot_id) {
    case 'E':
        $momento = 'EGRESOS';
        // Obtenemos los datos del período solicitado
        $datos = banco($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, '', '', $niveles, array());
        break;
    case 'I':
        $momento = 'INGRESOS';
        // Obtenemos los datos del período solicitado
        $datos = banco($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, $pot_id, $periodo, $programatico, $claveFinanciamiento, $reporte, '', '', $niveles, array());
        break;
    default:
        echo 'kym';
        break;
}

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL ".APP_EJERCICIO."";

if ($_SESSION[_S]['nvj_id'] > 2) {
    switch ($reporte['tipo']) {
        case 2: // Todas las URs de la carga de trabajo
            $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
            $encabLin6 = "SECTOR ";
            break;
        case 3: // Determinar qué incluir y qué excluir
            $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
            if (isset($_POST['nombreEspecial']) && $_POST['nombreEspecial'] != '') {
                $encabLin6 = trim($_POST['nombreEspecial']);
            } else {
                $encabLin6 = '';
            }
            break;
        default:
            $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL: " . $ur->getClave() . " - " . $ur->getNombre();
            $encabLin6 = '';
            break;
    }
} else {
    $encabLin3 = "UR: " . $ur->getClave() . " - " . $ur->getNombre();
    $encabLin6 = '';
}

if ($ue) {
    $encabLin4 = "UE: " . $ue->getUe_id() . " - " . $ue->getNombre();
} else {
    $encabLin4 = '';
}

if ($periodo['mesDesde'] == $periodo['mesHasta']) {
    if ($periodo['mesHasta'] == 0) {
        $encabLin5 = "REPORTE DE CONCILIACIÓN DE $momento DEL MES DE APERTURA DE " . substr($_SESSION[_S]['ejercicio'] . ".", 0, 4);
    } else {
        $encabLin5 = "REPORTE DE CONCILIACIÓN DE $momento DEL MES DE " . MesNombre($periodo['mesDesde']) . " DE " . substr($_SESSION[_S]['ejercicio'] . ".", 0, 4);
    }

} else {
    $encabLin5 = "REPORTE DE CONCILIACIÓN DE $momento DEL PERIODO " . MesNombre($periodo['mesDesde']) . "-" . MesNombre($periodo['mesHasta']) . " DE " . substr($_SESSION[_S]['ejercicio'] . ".", 0, 4);
}

if ($programatico['epg_id'] != '') {
    $encProg = new enc_Programatico();
    $encProg->setEpg_id($programatico['epg_id']);
    $encProg->select();

    $encabLin6 = "Obra/Actividad: " . $encProg->getClave() . " - " . $encProg->getNombre();

}

if ($claveFinanciamiento['ecf_id'] != '') {
    $cvefin = new enc_ClaveFinanciamiento();
    $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
    $cvefin->select();

    $encabLin7 = "Clave de Financiamiento: " . $cvefin->getClave() . " - " . $cvefin->getNombre();

} elseif ($federal && $estatal) {
    $encabLin7 = '';
} elseif ($federal) {
    $encabLin7 = "Recurso Federal";
} elseif ($estatal) {
    $encabLin7 = "Recurso Estatal";
} else {
    $encabLin7 = '';
}

// Valores para el archivo de exportación
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();
if ($reporte['tipo'] != 1) {
    if ($niveles[1] > 0) {
        array_push($anchoColumnas, 5);
        array_push($nombreColumnas, "UR");
        array_push($incluirColumnas, "ur_clave");
        array_push($tipoColumnas, "c");
    }
}
array_push($anchoColumnas, 15);
array_push($nombreColumnas, "BANCO");
array_push($incluirColumnas, "banco_nombre");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 15);
array_push($nombreColumnas, "CUENTA");
array_push($incluirColumnas, "banco_cuenta");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 40);
array_push($nombreColumnas, "ALIAS/NOMBRE");
array_push($incluirColumnas, "banco_alias");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "fechaReferencia");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 5);
array_push($nombreColumnas, "TDF");
array_push($incluirColumnas, "tdf_id");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 20);
array_push($nombreColumnas, "REFERENCIA");
array_push($incluirColumnas, "referencia");
array_push($tipoColumnas, "c");
array_push($anchoColumnas, 15);
array_push($nombreColumnas, "IMPORTE");
array_push($incluirColumnas, "pdx_importe");
array_push($tipoColumnas, "n");
//AFE
array_push($nombreColumnas, "AFE");
array_push($incluirColumnas, "afe_id");
array_push($tipoColumnas, "c"); //*/
array_push($anchoColumnas, 10);
//CLC
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "CLC");
array_push($incluirColumnas, "clc_id");
array_push($tipoColumnas, "c"); //*/
//BDO
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "BDO");
array_push($incluirColumnas, "bdo_id");
array_push($tipoColumnas, "c"); //*/
//EJERCICIO
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "EJERCICIO");
array_push($incluirColumnas, "pdx_ejercicio");
array_push($tipoColumnas, "c"); //*/
//POLIZA
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "POLIZA");
array_push($incluirColumnas, "poliza");
array_push($tipoColumnas, "c"); //*/

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['textoCompleto'] = false;

$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
if ($nivel >= 3) {
    $tablaimg[0]['ruta'] = "../../../../img/logosefin-color-fblanco.jpg";

    $tablaimg[0]['alingXimg'] = "468";
    $tablaimg[0]['colImg'] = "G1";
} else {
    $tablaimg[0]['ruta'] = "../../../../img/Logo Institucional/" . $ur->getClave() . ".jpg";

    $tablaimg[0]['alingXimg'] = "390";
    $tablaimg[0]['colImg'] = "G1";
}
$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
    // die();
}
// Armamos los parametros opcionales

$datosReporte = array();
$numero = 0;
for (; $numero < count($datos['data']); $numero++) {
    $datosReporte[$numero] = (array)$datos['data'][$numero];
}
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6, 'encabLin7' => $encabLin7,
    'ancho' => $anchoColumnas, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => false, 'nombre' => "CONCILIACION" . $momento,
    'sufijoNombre' => "-" . $periodo['mesDesde'] . "_" . $periodo['mesHasta'], 'tablaimg' => $tablaimg);
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datosReporte, $encabezado, $table, $otrosParametros);
