<?php
session_start();

//librerias
require_once dirname(__FILE__) . "/../../../../ent/UnidadResponsable.php";
require_once dirname(__FILE__) . "/../../../../ent/UnidadEjecutora.php";
require_once dirname(__FILE__) . "/../../../../ent/enc_ClaveFinanciamiento.php";
require_once dirname(__FILE__) . "/../../../../ent/enc_Programatico.php";
require_once dirname(__FILE__) . "/../../../../lib/redondear.php";
require_once dirname(__FILE__) . "/../../../../lib/MesNombre.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/conciliacion/conciliacion.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/conciliacion/conciliacionContable.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/conciliacion/conciliacionRectificado.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/conciliacion/conciliacionModificado.php";
require_once dirname(__FILE__) . "/../../../lib/array2Excel.php";

$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();

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
        ($niveles[7] > 0) ? 8 : (
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
if ($niveles[9] > 0) {
    $claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
} else {
    $claveFinanciamiento['ecf_id'] = '';
}
if ($niveles[8] > 0) {
    $claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A' : '')));
} else {
    $claveFinanciamiento['ter_id'] = '';
}
if ($niveles[3] > 0 && $reporte['tipo'] == 1) {
    $programatico['epg_id'] = isset($_POST['programatico']) ? $_POST['programatico'] : '';
} else {
    $programatico['epg_id'] = '';
}

if ($reporte['tipo'] != 1) {
    $ue_id = false;
    $ue = false;
    $niveles[2] = -1;
}
$mmc_id = isset($_POST['mmc_id']) ? $_POST['mmc_id'] : false;

switch ($mmc_id) {
    case 'C':
        $momento = 'DEL COMPROMETIDO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'D':
        $momento = 'DEL DEVENGADO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'E':
        $momento = 'DEL EJERCIDO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'T':
        $momento = 'DEL MINISTRADO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'P':
        $momento = 'DEL PAGADO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'R':
        $momento = 'DEL RECTIFICADO ';
        $parametroExtra['origenReporte'] = 'excel';
        // Obtenemos los datos del período solicitado
        $datos = conciliacionRectificado($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles, $parametroExtra);
        break;
    case 'M':
        $momento = 'DEL MODIFICADO ';
        // Obtenemos los datos del período solicitado
        $datos = conciliacionModificado($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', $mmc_id, $niveles);
        break;
    case 'Z':
        $momento = '';
        // Obtenemos los datos del período solicitado
        $datos = conciliacionContable($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, '', 'Z', $niveles);
        break;
    default:
        echo 'kym';
        break;
}

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL ".APP_EJERCICIO;

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
        $encabLin5 = "REPORTE DE CONCILIACIÓN " . $momento . "DEL MES DE APERTURA DE " . substr($_SESSION[_S]['ejercicio'], 0, 4) . ".";
    } else {
        $encabLin5 = "REPORTE DE CONCILIACIÓN " . $momento . "DEL MES DE " . MesNombre($periodo['mesDesde']) . " DE " . substr($_SESSION[_S]['ejercicio'], 0, 4) . ".";
    }
} else {
    $encabLin5 = "REPORTE DE CONCILIACIÓN " . $momento . "DEL PERIODO " . MesNombre($periodo['mesDesde']) . "-" . MesNombre($periodo['mesHasta']) . " DE " . substr($_SESSION[_S]['ejercicio'], 0, 4) . ".";
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
if ($niveles[2] > 0) {
    array_push($anchoColumnas, 5);
    array_push($nombreColumnas, "UE");
    array_push($incluirColumnas, "ue_id");
    array_push($tipoColumnas, "c");
}
if ($niveles[3] > 0) {
    array_push($anchoColumnas, 10);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "OBRA/ACTIVIDAD");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "oa_clave");
    array_push($incluirColumnas, "oa_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[4] > 0) {
    array_push($anchoColumnas, 5);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "TIPO DE GASTO");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "tg_id");
    array_push($incluirColumnas, "tg_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[5] > 0) {
    array_push($anchoColumnas, 5);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "IDENTIFICADOR DE PARTIDA");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "idp_id");
    array_push($incluirColumnas, "idp_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[6] > 0) {
    array_push($anchoColumnas, 5);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "PARTIDA GENÉRICA");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "pg_id");
    array_push($incluirColumnas, "pg_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[7] > 0) {
    array_push($anchoColumnas, 5);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "PARTIDA ESPECÍFICA");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "pe_id");
    array_push($incluirColumnas, "pe_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[8] > 0) {
    array_push($anchoColumnas, 5);
    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "TIPO DE RECURSO");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "ter_id");
    array_push($incluirColumnas, "ter_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}
if ($niveles[9] > 0) {
    array_push($anchoColumnas, 15);
    array_push($anchoColumnas, 30);
    array_push($nombreColumnas, "CLAVE DE FINANCIAMIENTO");
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "cfi_clave");
    array_push($incluirColumnas, "cfi_nombre");
    array_push($tipoColumnas, "c");
    array_push($tipoColumnas, "c");
}

switch ($mmc_id) {
    case 'R':
        //CONTABILIDAD
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CONTABILIDAD");
        array_push($incluirColumnas, "pdx_importe");
        array_push($tipoColumnas, "n");
        //COMPROMETIDO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_ENE");
        array_push($incluirColumnas, "com_ene");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_FEB");
        array_push($incluirColumnas, "com_feb");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_MAR");
        array_push($incluirColumnas, "com_mar");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_ABR");
        array_push($incluirColumnas, "com_abr");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_MAY");
        array_push($incluirColumnas, "com_may");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_JUN");
        array_push($incluirColumnas, "com_jun");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_JUL");
        array_push($incluirColumnas, "com_jul");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_AGO");
        array_push($incluirColumnas, "com_ago");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_SEP");
        array_push($incluirColumnas, "com_sep");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_OCT");
        array_push($incluirColumnas, "com_oct");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_NOV");
        array_push($incluirColumnas, "com_nov");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COM_DIC");
        array_push($incluirColumnas, "com_dic");
        array_push($tipoColumnas, "n");
        #DEVENGADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_ENE");
        array_push($incluirColumnas, "dev_ene");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_FEB");
        array_push($incluirColumnas, "dev_feb");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_MAR");
        array_push($incluirColumnas, "dev_mar");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_ABR");
        array_push($incluirColumnas, "dev_abr");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_MAY");
        array_push($incluirColumnas, "dev_may");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_JUN");
        array_push($incluirColumnas, "dev_jun");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_JUL");
        array_push($incluirColumnas, "dev_jul");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_AGO");
        array_push($incluirColumnas, "dev_ago");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_SEP");
        array_push($incluirColumnas, "dev_sep");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_OCT");
        array_push($incluirColumnas, "dev_oct");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_NOV");
        array_push($incluirColumnas, "dev_nov");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEV_DIC");
        array_push($incluirColumnas, "dev_dic");
        array_push($tipoColumnas, "n");
        #EJERCIDO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_ENE");
        array_push($incluirColumnas, "eje_ene");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_FEB");
        array_push($incluirColumnas, "eje_feb");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_MAR");
        array_push($incluirColumnas, "eje_mar");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_ABR");
        array_push($incluirColumnas, "eje_abr");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_MAY");
        array_push($incluirColumnas, "eje_may");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_JUN");
        array_push($incluirColumnas, "eje_jun");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_JUL");
        array_push($incluirColumnas, "eje_jul");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_AGO");
        array_push($incluirColumnas, "eje_ago");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_SEP");
        array_push($incluirColumnas, "eje_sep");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_OCT");
        array_push($incluirColumnas, "eje_oct");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_NOV");
        array_push($incluirColumnas, "eje_nov");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJE_DIC");
        array_push($incluirColumnas, "eje_dic");
        array_push($tipoColumnas, "n");
        #MINISTRADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_ENE");
        array_push($incluirColumnas, "min_ene");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_FEB");
        array_push($incluirColumnas, "min_feb");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_MAR");
        array_push($incluirColumnas, "min_mar");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_ABR");
        array_push($incluirColumnas, "min_abr");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_MAY");
        array_push($incluirColumnas, "min_may");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_JUN");
        array_push($incluirColumnas, "min_jun");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_JUL");
        array_push($incluirColumnas, "min_jul");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_AGO");
        array_push($incluirColumnas, "min_ago");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_SEP");
        array_push($incluirColumnas, "min_sep");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_OCT");
        array_push($incluirColumnas, "min_oct");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_NOV");
        array_push($incluirColumnas, "min_nov");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "MIN_DIC");
        array_push($incluirColumnas, "min_dic");
        array_push($tipoColumnas, "n");
        #PAGADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_ENE");
        array_push($incluirColumnas, "pag_ene");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_FEB");
        array_push($incluirColumnas, "pag_feb");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_MAR");
        array_push($incluirColumnas, "pag_mar");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_ABR");
        array_push($incluirColumnas, "pag_abr");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_MAY");
        array_push($incluirColumnas, "pag_may");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_JUN");
        array_push($incluirColumnas, "pag_jun");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_JUL");
        array_push($incluirColumnas, "pag_jul");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_AGO");
        array_push($incluirColumnas, "pag_ago");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_SEP");
        array_push($incluirColumnas, "pag_sep");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_OCT");
        array_push($incluirColumnas, "pag_oct");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_NOV");
        array_push($incluirColumnas, "pag_nov");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAG_DIC");
        array_push($incluirColumnas, "pag_dic");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PRESUPUESTO");
        array_push($incluirColumnas, "pre_importe");
        array_push($tipoColumnas, "n");

        if ($reporte['tipo'] == 1) {
            //COMPROMISO
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CMP");
            array_push($incluirColumnas, "cmp_id");
            array_push($tipoColumnas, "c"); //*/
            //AFE
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "AFE");
            array_push($incluirColumnas, "afe_id");
            array_push($tipoColumnas, "c"); //*/
            //CLC
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CLC");
            array_push($incluirColumnas, "clc_id");
            array_push($tipoColumnas, "c"); //*/
            //FACTURA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "BDO");
            array_push($incluirColumnas, "bdo_id");
            array_push($tipoColumnas, "c"); //*/
            //RECTIFICACION
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "RCT");
            array_push($incluirColumnas, "rct_id");
            array_push($tipoColumnas, "c"); //*/
            //POLIZA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "POLIZA");
            array_push($incluirColumnas, "poliza");
            array_push($tipoColumnas, "c"); //*/

        }

        break;
    case 'M':
        //REDUCCION
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "REDUCCIÓN CONTABLE");
        array_push($incluirColumnas, "pdx_reduccion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "REDUCCIÓN PRESUPUESTAL");
        array_push($incluirColumnas, "pre_reduccion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($anchoColumnas, 15);
        //DIFERENCIA
        array_push($nombreColumnas, "DIFERENCIA REDUCCIÓN");
        array_push($incluirColumnas, "diferenciaReduccion");
        array_push($tipoColumnas, "n");
        //AMPLIACION
        array_push($nombreColumnas, "AMPLIACIÓN CONTABLE");
        array_push($incluirColumnas, "pdx_ampliacion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "AMPLIACIÓN PRESUPUESTAL");
        array_push($incluirColumnas, "pre_ampliacion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        //DIFERENCIA
        array_push($nombreColumnas, "DIFERENCIA AMPLIACIÓN");
        array_push($incluirColumnas, "diferenciaAmpliacion");
        array_push($tipoColumnas, "n");

        if ($reporte['tipo'] == 1) {
            //MODIFICACION
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "MODIFICACIÓN");
            array_push($incluirColumnas, "mod_clave");
            array_push($tipoColumnas, "c"); //*/
            //POLIZA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "POLIZA");
            array_push($incluirColumnas, "poliza");
            array_push($tipoColumnas, "c"); //*/

        }
        break;
    case 'C';
        //CONTABILIDAD
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CONTABILIDAD");
        array_push($incluirColumnas, "pdx_importe");
        array_push($tipoColumnas, "n");
        //PRESUPUESTO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PRESUPUESTO");
        array_push($incluirColumnas, "pre_importe");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "RECTIFICADO");
        array_push($incluirColumnas, "rec_importe");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        //DIFERENCIA
        array_push($nombreColumnas, "DIFERENCIA");
        array_push($incluirColumnas, "diferencia");
        array_push($tipoColumnas, "n");

        if ($reporte['tipo'] == 1) {
            //COMPROMISO
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CMP");
            array_push($incluirColumnas, "cmp_id");
            array_push($tipoColumnas, "c"); //*/
            //POLIZA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "POLIZA");
            array_push($incluirColumnas, "poliza");
            array_push($tipoColumnas, "c"); //*/

        }
        break;
    case 'Z';
        //REDUCCION
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "REDUCCIÓN CONTABLE");
        array_push($incluirColumnas, "pdx_reduccion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        //AMPLIACION
        array_push($nombreColumnas, "AMPLIACIÓN CONTABLE");
        array_push($incluirColumnas, "pdx_ampliacion");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        //COMPROMETIDO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "COMPROMETIDO");
        array_push($incluirColumnas, "pdx_comprometido");
        array_push($tipoColumnas, "n");
        //DEVENGADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DEVENGADO");
        array_push($incluirColumnas, "pdx_devengado");
        array_push($tipoColumnas, "n");
        //EJERCIDO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "EJERCIDO");
        array_push($incluirColumnas, "pdx_ejercido");
        array_push($tipoColumnas, "n");
        //PAGADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PAGADO");
        array_push($incluirColumnas, "pdx_pagado");
        array_push($tipoColumnas, "n");
        //RECTIFICADO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "RECTIFICADO");
        array_push($incluirColumnas, "pdx_rectificado");
        array_push($tipoColumnas, "n");
        if ($reporte['tipo'] == 1) {
            //MODIFICACION
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "MOD");
            array_push($incluirColumnas, "mod_clave");
            array_push($tipoColumnas, "c"); //*/
            //COMPROMISO
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CMP");
            array_push($incluirColumnas, "cmp_id");
            array_push($tipoColumnas, "c"); //*/
            //AFE
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "AFE");
            array_push($incluirColumnas, "afe_id");
            array_push($tipoColumnas, "c"); //*/
            //CLC
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CLC");
            array_push($incluirColumnas, "clc_id");
            array_push($tipoColumnas, "c"); //*/
            //FACTURA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "BDO");
            array_push($incluirColumnas, "bdo_id");
            array_push($tipoColumnas, "c"); //*/
            //RECTIFICACION
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "RCT");
            array_push($incluirColumnas, "rct_id");
            array_push($tipoColumnas, "c"); //*/
        }
        break;
    default:
        //CONTABILIDAD
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CONTABILIDAD");
        array_push($incluirColumnas, "pdx_importe");
        array_push($tipoColumnas, "n");
        //PRESUPUESTO
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PRESUPUESTO");
        array_push($incluirColumnas, "pre_importe");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "RECTIFICADO");
        array_push($incluirColumnas, "rec_importe");
        array_push($tipoColumnas, "n");
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "DIFERENCIA");
        array_push($incluirColumnas, "diferencia");
        array_push($tipoColumnas, "n");

        if ($reporte['tipo'] == 1) {
            //COMPROMISO
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CMP");
            array_push($incluirColumnas, "cmp_id");
            array_push($tipoColumnas, "c"); //*/
            //AFE
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "AFE");
            array_push($incluirColumnas, "afe_id");
            array_push($tipoColumnas, "c"); //*/
            //CLC
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "CLC");
            array_push($incluirColumnas, "clc_id");
            array_push($tipoColumnas, "c"); //*/
            //FACTURA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "BDO");
            array_push($incluirColumnas, "bdo_id");
            array_push($tipoColumnas, "c"); //*/
            //POLIZA
            array_push($anchoColumnas, 10);
            array_push($nombreColumnas, "POLIZA");
            array_push($incluirColumnas, "poliza");
            array_push($tipoColumnas, "c"); //*/

        }
}

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
    $table['color'] = 1;
}

// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3, 'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6, 'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas);
$otrosParametros = array('comprimir' => false, 'nombre' => "CONCILIACION" . $momento, 'sufijoNombre' => "-" . $periodo['mesDesde'] . "_" . $periodo['mesHasta']);

$excel = new array2Excel();
$excel->generaExcel($datos, $encabezado, true, $table, $otrosParametros);
