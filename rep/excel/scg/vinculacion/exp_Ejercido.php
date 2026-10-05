<?php
session_start();

//librerias
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__) . '/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__) . '/../../../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../../../ent/scg/vinculacion/vinculacion/BancoDocumento.php';
require_once dirname(__FILE__) . '/../../../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__) . '/../../../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/vinculacion/vinculacion.php';
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
    $ue = FALSE;
}

$ue_id = ($ue ? $ue->getUe_id() : FALSE);

$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

$mmc_id = (isset($_POST['mmc_id']) ? $_POST['mmc_id'] : '');

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
    $ejercicio = $_SESSION[_S]['ejercicioActual'];
}
$miles = ((isset($_POST['checkMiles']) && trim($_POST['checkMiles']) != "") ? $_POST['checkMiles']
    : FALSE);
$movimiento = (isset($_POST['checkMovimiento']) && $_POST['checkMovimiento'] == 1)
    ? TRUE : FALSE;

$periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
    != "") ? $_POST['comboMesDesde'] : -1);
$periodo['mesHasta'] = $periodo['mesDesde'];

$niveles[1] = ((isset($_POST['checkNivel1']) && trim($_POST['checkNivel1']) != "")
    ? $_POST['checkNivel1'] : 1);
$niveles[2] = ((isset($_POST['checkNivel2']) && trim($_POST['checkNivel2']) != "")
    ? $_POST['checkNivel2'] : -1);
$niveles[3] = ((isset($_POST['checkNivel3']) && trim($_POST['checkNivel3']) != "")
    ? $_POST['checkNivel3'] : -1);
$niveles[4] = ((isset($_POST['checkNivel4']) && trim($_POST['checkNivel4']) != "")
    ? $_POST['checkNivel4'] : -1);
$niveles[5] = ((isset($_POST['checkNivel5']) && trim($_POST['checkNivel5']) != "")
    ? $_POST['checkNivel5'] : -1);
$niveles[6] = ((isset($_POST['checkNivel6']) && trim($_POST['checkNivel6']) != "")
    ? $_POST['checkNivel6'] : -1);
$niveles[7] = ((isset($_POST['checkNivel7']) && trim($_POST['checkNivel7']) != "")
    ? $_POST['checkNivel7'] : -1);
$niveles[8] = ((isset($_POST['checkNivelR']) && trim($_POST['checkNivelR']) != "")
    ? $_POST['checkNivelR'] : -1);

$niveles[0] = ($niveles[8] > 0) ? 8 : (
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
);

$gur = $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'];
$clc_id = isset($_POST['inputClc_id']) ? ($_POST['inputClc_id'] != '' ? $_POST['inputClc_id']
    : '') : '';

#opcion
$bdo_id = isset($_POST['inputBdo_id']) ? ($_POST['inputBdo_id'] != '' ? $_POST['inputBdo_id']
    : '') : '';
$rfc_id = isset($_POST['inputRfc_id']) ? ($_POST['inputRfc_id'] != '' ? $_POST['inputRfc_id']
    : '') : '';
$bdo_referencia = '';
if (isset($_POST['inputBdo_referencia']) && !empty($_POST['inputBdo_referencia'])) {
    $referencias = explode(
        ",",
        str_replace(' ', '', $_POST['inputBdo_referencia'])
    );
    foreach ($referencias as $bdo) {
        $bdo_referencia .= '"' . $bdo . '",';
    }
    $bdo_referencia = substr($bdo_referencia, 0, -1);
}

$reporte['tipo'] = ((isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '')
    ? $_POST['tipoReporte'] : 1);
$reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr']
    : "";
if ($reporte['tipo'] == 3) {
    $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
    $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir']
        : "";
}

$programatico['epg_id'] = '';

$estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? TRUE
    : FALSE;
$federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? TRUE
    : FALSE;
$claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
$claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal
    && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A' : '')));

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL " . $_SESSION[_S]['ejercicioActual'];

if ($_SESSION[_S]['nvj_id'] > 2) {
    switch ($reporte['tipo']) {
        case 2:   // Todas las URs de la carga de trabajo
            $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL";
            $encabLin6 = "SECTOR ";
            break;
        case 3:   // Determinar qué incluir y qué excluir
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
    if ($periodo['mesHasta'] == 0)
        $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO DEL MES DE APERTURA $ejercicio";
    else
        $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO DEL MES DE " . MesNombre($periodo['mesDesde']) . " DE $ejercicio ";
} else {
    $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO CORRESPONDIENTE AL PERIODO " . MesNombre($periodo['mesDesde']) . "-" . MesNombre($periodo['mesHasta']) . " DE $ejercicio ";
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

if ($miles) {
    $encabLin7 = "(en miles de pesos)";
}

if ($clc_id != '') {
    $clc = new Clc();
    $clc->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $clc->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $clc->setClc_id($clc_id);
    $clc->select();
    $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO";
    $encabLin6 = "de la CLC " . $clc->getClc_id() . "-" . $clc->getConcepto();
}
if ($bdo_id != '') {
    $documento = new BancoDocumentos();
    $documento->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $documento->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $documento->setBdo_id($bdo_id);
    $documento->select();
    $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO";
    $encabLin7 = "del documento " . $documento->getRfc_id() . "-" . $documento->getReferencia();
} elseif ($rfc_id != '' && $bdo_referencia == '') {
    $proveedor = new Proveedores();
    $proveedor->setRfc_id($rfc_id);
    $proveedor->select();
    $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO";
    $encabLin7 = "del proveedor " . $proveedor->getRfc_id() . "-" . $proveedor->getNombre() . ($proveedor->getSoc_id()
        == 0 ? " " . $proveedor->getPaterno() . " " . $proveedor->getMaterno() : ", " . $proveedor->getSoc_id());
} elseif ($rfc_id != '' && $bdo_referencia != '') {
    $documento = new BancoDocumentos();
    $documento->setRfc_id($rfc_id);
    $documento->setReferencia($bdo_referencia);
    $documento->select();
    $encabLin5 = "REPORTE DE VINCULACIÓN DEL EJERCIDO";
    $encabLin7 = "del documento " . $documento->getRfc_id() . "-" . $documento->getReferencia();
}

$datos = vinculacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue_id,
    '',
    $periodo,
    '',
    $claveFinanciamiento,
    $reporte,
    FALSE,
    $mmc_id,
    $niveles,
    array(
        "gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
        "bdo_referencia" => $bdo_referencia
    )
);

// Valores para el archivo de exportación
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . $ue_id == 116001) {
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

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "RFC");
array_push($incluirColumnas, "rfc_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "TDF");
array_push($incluirColumnas, "tdf_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "REFERENCIA");
array_push($incluirColumnas, "bdo_referencia");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "FECHA REF.");
array_push($incluirColumnas, "bdo_fechaDocumento");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "CONCEPTO");
array_push($incluirColumnas, "bdo_concepto");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "IMPORTE");
array_push($incluirColumnas, "bdo_percepcionPresupuestal");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "AUTORIZACIÓN");
array_push($incluirColumnas, "fechaAutorizacion");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "CANCELACIÓN");
array_push($incluirColumnas, "fechaCancelacion");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "PÓLIZA");
array_push($incluirColumnas, "polizaAutorizado");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "fechaAutorizado");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "PÓLIZA");
array_push($incluirColumnas, "polizaCancelado");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "fechaCancelado");
array_push($tipoColumnas, "f");

$table = array();

foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['textoCompleto'] = FALSE;
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
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas
);
$otrosParametros = array(
    'comprimir' => FALSE, 'nombre' => "Ejercido",
    'sufijoNombre' => "-" . $periodo['mesDesde'] . "_" . $periodo['mesHasta'],   'tablaimg' => $tablaimg
);
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
