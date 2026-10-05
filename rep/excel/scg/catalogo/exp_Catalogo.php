<?php
declare (strict_types = 1);
session_start();

//librerias
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/catalogo/catalogo.php';

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

$reporte['tipo'] = ((isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '')
    ? $_POST['tipoReporte'] : 1);
$reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr']
: "";
$reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
$reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : "";

$periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
    != "") ? $_POST['comboMesDesde'] : -1);
$periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta'])
    != "") ? $_POST['comboMesHasta'] : -1);

$niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != "") ? $_POST['checkUR']
    : 1);
$niveles[2] = ($ue_id != "" ? $ue_id : -1);
$niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != "") ? $_POST['checkEP']
    : -1);
$niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != "") ? $_POST['checkTG']
    : -1);
$niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != "") ? $_POST['checkIP']
    : -1);
$niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != "") ? $_POST['checkPG']
    : -1);
$niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != "") ? $_POST['checkPE']
    : -1);
$niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != "") ? $_POST['checkTR']
    : -1);
$niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != "") ? $_POST['checkCF']
    : -1);

$niveles[0] = ($niveles[9] > 0) ? 9 : (
    ($niveles[8] > 0) ? 7 : (
        ($niveles[7] > 0) ? 8 : (
            ($niveles[6] > 0) ? 6 : (
                ($niveles[5] > 0) ? 5 : (
                    ($niveles[4] > 0) ? 4 : (
                        ($niveles[3] > 0) ? 3 : (
                            ($niveles[2] > 0) ? 2 : 1)))))));

$estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? true
: false;
$federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? true
: false;
if ($niveles[9] > 0) {
    $claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
} else {
    $claveFinanciamiento['ecf_id'] = '';
}
if ($niveles[8] > 0) {
    $claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? ''
    : (($federal && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A'
        : '')));
} else {
    $claveFinanciamiento['ter_id'] = '';
}
if ($niveles[3] > 0 && $reporte['tipo'] == 1) {
    $programatico['epg_id'] = isset($_POST['programatico']) ? $_POST['programatico']
    : '';
} else {
    $programatico['epg_id'] = '';
}

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

switch ($opc_id) {
    case 1:
        $encabLin5 = 'MES CONTABLE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 2:
        $encabLin5 = 'AGRUPACIÓN DE CUENTA CONTABLE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 3:
        $encabLin5 = 'TIPO DE DOCUMENTO FUENTE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 4:
        $encabLin5 = 'AGRUPACIÓN DE DOCUMENTO FUENTE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 5:
        $encabLin5 = 'AGRUPACIÓN DE PÓLIZA CONTABLE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 6:
        $encabLin5 = 'PREFIJO';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 7:
        $encabLin5 = 'MOMENTO CONTABLE';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 8:
        $encabLin5 = 'VINCULACIÓN COG';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 9:
        $encabLin5 = 'VINCULACIÓN RETENCIÓN';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    case 10:
        $encabLin5 = 'PROVEEDOR';
        $datos = catalogo($ur->getGpo_id(), $ur->getUr_id(), '', '', '',
            array("sis_id" => $sis_id, "men_id" => $men_id, "opc_id" => $opc_id));
        break;
    default:
        echo "kym->reporte indefinida";
        break;
}
$encabLin5 = 'CATÁLOGO ' . $encabLin5;

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

switch ($opc_id) {
    case 8:
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "PARTIDA GENÉRICA");
        array_push($incluirColumnas, "pg_id");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "pg_nombre");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 10);
        array_push($nombreColumnas, "TIPO");
        array_push($incluirColumnas, "cog_tipo");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CUENTA");
        array_push($incluirColumnas, "cog_cuenta");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "cgsc_nombre"); //h
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CONTRA CUENTA");
        array_push($incluirColumnas, "cog_contracuenta");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "cgm_contranombre");
        array_push($tipoColumnas, "c");
        break;
    case 9:
        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "RETENCIÓN");
        array_push($incluirColumnas, "cgc_cuenta");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "cgc_nombre");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 15);
        array_push($nombreColumnas, "CUENTA");
        array_push($incluirColumnas, "cta_id");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "cta_nombre");
        array_push($tipoColumnas, "c");
        break;
    case 2:
        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "CLAVE");
        array_push($incluirColumnas, "clave");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "nombre");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "DESCRIPCIÓN");
        array_push($incluirColumnas, "descrip");
        array_push($tipoColumnas, "c");
        break;

    default:
        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "CLAVE");
        array_push($incluirColumnas, "clave");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "NOMBRE");
        array_push($incluirColumnas, "nombre");
        array_push($tipoColumnas, "c");

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, "ALIAS");
        array_push($incluirColumnas, "alias");
        array_push($tipoColumnas, "c");
        break;
}
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";

$tablaimg[0]['ruta'] = "../../../../img/" . LOGO_COLOR;
switch ($opc_id) {
    case 8:
        $tablaimg[0]['colImg'] = "G1";
        $tablaimg[0]['alingXimg'] = "10";
        break;
    case 9:
        $tablaimg[0]['colImg'] = "D1";
        $tablaimg[0]['alingXimg'] = "10";
        break;
    default:
        $tablaimg[0]['colImg'] = "C1";
        $tablaimg[0]['alingXimg'] = "10";
        break;
}

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
}
$table = array();
$table['tipoTabla'] = 'M';
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => false, 'nombre' => $encabLin5, 'tablaimg' => $tablaimg);

$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
