<?php

session_start();

//librerias
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/cuentaContable/CgSubcuenta.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/reporte/diario.php';
require_once dirname(__FILE__) . '/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';

// Armamos los parámetros para pedir correctamente la balanza
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
$nivel = $_SESSION[_S]['nvj_id'];
$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

if (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
    $ejercicio = "2020";
} elseif (isset($_POST['check2019']) && $_POST['check2019'] == 1) {
    $ejercicio = "2019";
} elseif (isset($_POST['check2018']) && $_POST['check2018'] == 1) {
    $ejercicio = "2018";
} elseif (isset($_POST['check2017']) && $_POST['check2017'] == 1) {
    $ejercicio = "2017";
} elseif (isset($_POST['check2016']) && $_POST['check2016'] == 1) {
    $ejercicio = "2016";
} elseif (isset($_POST['check2015']) && $_POST['check2015'] == 1) {
    $ejercicio = "2015";
} elseif (isset($_POST['check2014']) && $_POST['check2014'] == 1) {
    $ejercicio = "2014";
} elseif (isset($_POST['check2013']) && $_POST['check2013'] == 1) {
    $ejercicio = "2013";
} elseif (isset($_POST['check2012']) && $_POST['check2012'] == 1) {
    $ejercicio = "2012";
} else {
    $ejercicio = APP_EJERCICIO;
}

$periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != "") ? $_POST['comboMesDesde'] : -1);
$periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != "") ? $_POST['comboMesHasta'] : -1);

$detalle = ((isset($_POST['checkDetalle']) && trim($_POST['checkDetalle']) != '') ? true : false);

$pot_id['diario'] = isset($_POST['checkDiario']) ? ($_POST['checkDiario'] != '' ? 'D' : '') : '';
$pot_id['ingresos'] = isset($_POST['checkIngreso']) ? ($_POST['checkIngreso'] != '' ? 'I' : '') : '';
$pot_id['egresos'] = isset($_POST['checkEgreso']) ? ($_POST['checkEgreso'] != '' ? 'E' : '') : '';

$programatico['epg_id'] = '';

$estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? true : false;
$federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? true : false;
$claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
$claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A' : '')));

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL " . APP_EJERCICIO;

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
        $encabLin5 = "REPORTE DIARIO DEL MES DE APERTURA $ejercicio";
    } else {
        $encabLin5 = "REPORTE DIARIO DEL MES DE " . MesNombre($periodo['mesDesde']) . " DE $ejercicio ";
    }

} else {
    $encabLin5 = "REPORTE DIARIO CORRESPONDIENTE AL PERIODO " . MesNombre($periodo['mesDesde']) . "-" . MesNombre($periodo['mesHasta']) . " DE $ejercicio ";
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

$datos = diario($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, $pot_id, $periodo, $claveFinanciamiento, '', $detalle);

// Valores para el archivo de exportación
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "PÓLIZA");
array_push($incluirColumnas, "poliza");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "UE POLIZA");
array_push($incluirColumnas, "ue_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "pma_fecha");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "DESCRIPCIÓN");
array_push($incluirColumnas, "pma_concepto");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "DEBE");
array_push($incluirColumnas, "pma_debe");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "HABER");
array_push($incluirColumnas, "pma_haber");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "MOMENTO CONTABLE");
array_push($incluirColumnas, "mmc_nombre");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "ESTADO");
array_push($incluirColumnas, "epl_nombre");
array_push($tipoColumnas, "c");

if ($detalle) {
    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "UE CUENTA");
    array_push($incluirColumnas, "cta_ue_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 20);
    array_push($nombreColumnas, "CUENTA");
    array_push($incluirColumnas, "cta_cuenta");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "TDF");
    array_push($incluirColumnas, "tdf_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 20);
    array_push($nombreColumnas, "RFC");
    array_push($incluirColumnas, "rfc_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 20);
    array_push($nombreColumnas, "REFERENCIA");
    array_push($incluirColumnas, "pdx_referencia");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "FECHA REF.");
    array_push($incluirColumnas, "pdx_fechaReferencia");
    array_push($tipoColumnas, "f");

    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "DEBE");
    array_push($incluirColumnas, "pdx_debe");
    array_push($tipoColumnas, "n");

    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "HABER");
    array_push($incluirColumnas, "pdx_haber");
    array_push($tipoColumnas, "n");

    if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 116) {
        array_push($anchoColumnas, 5);
        array_push($nombreColumnas, "UR");
        array_push($incluirColumnas, "gur");
        array_push($tipoColumnas, "c");
    }
    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "CLC");
    array_push($incluirColumnas, "clc_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "AFE");
    array_push($incluirColumnas, "afe_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "BDO");
    array_push($incluirColumnas, "bdo_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "CMP");
    array_push($incluirColumnas, "cmp_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "EPG");
    array_push($incluirColumnas, "epg_clave");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "oa_nombre");
    array_push($tipoColumnas, "c");
    array_push($anchoColumnas, 5);
    array_push($nombreColumnas, "PG");
    array_push($incluirColumnas, "pg_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "pg_nombre");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 5);
    array_push($nombreColumnas, "PE");
    array_push($incluirColumnas, "pe_id");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 15);
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "pe_nombre");
    array_push($tipoColumnas, "c");
    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "CLAVE FINANCIAMIENTO");
    array_push($incluirColumnas, "ecf_clave");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "NOMBRE");
    array_push($incluirColumnas, "cfi_nombre");
    array_push($tipoColumnas, "c");

    array_push($anchoColumnas, 10);
    array_push($nombreColumnas, "EJERCICIO");
    array_push($incluirColumnas, "pdx_ejercicio");
    array_push($tipoColumnas, "c");
    //*/
}

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

$table['tipoTabla'] = "M"; 
$table['encabezado'] = 'S';
$table['color'] = 1;
$table['encabezadocolor'] = "BFB7B6";
$table['line1color'] = "FFFFFF";
$table['line2color'] = "FFFFFF";

$table['textoCompleto'] = false;
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
$tablaimg[0]['ruta'] = "../../../../img/logoOaxaca-color-fblanco_v2.png";
$tablaimg[0]['alingXimg'] = "468";
$tablaimg[0]['colImg'] = "F1";
$tablaimg[0]['altura'] = "72";
$tablaimg[0]['rotacion'] = "0";

if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}

// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3, 'encabLin4' => $encabLin4,
    'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6, 'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas, 'alineacionenc' => 'C');
$otrosParametros = array('comprimir' => false, 'nombre' => "ReporteDiario", 'sufijoNombre' => "-" . $periodo['mesDesde'] . "_" . $periodo['mesHasta'], 'tablaimg' => $tablaimg);

$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);

