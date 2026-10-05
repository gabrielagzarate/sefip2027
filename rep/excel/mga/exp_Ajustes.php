<?php

session_start();

require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/array2Excel.php';
require_once dirname(__FILE__) . '/../../../lib/mga/consultaMgaComplementosPrevisiones.php';
require_once dirname(__FILE__) . '/../../../lib/helpers.php';

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

$reporte['tipo'] = ((isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : 1);
$reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : '';
$reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : '';
$reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : '';

$periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1);
$periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1);

$niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != '') ? $_POST['checkUR'] : 1);
$niveles[2] = ($ue_id != '' ? $ue_id : -1);
$niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != '') ? $_POST['checkEP'] : -1);
$niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != '') ? $_POST['checkTG'] : -1);
$niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != '') ? $_POST['checkIP'] : -1);
$niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != '') ? $_POST['checkPG'] : -1);
$niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != '') ? $_POST['checkPE'] : -1);
$niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != '') ? $_POST['checkTR'] : -1);
$niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != '') ? $_POST['checkCF'] : -1);

$niveles[0] = ($niveles[9] > 0) ? 9 : (($niveles[8] > 0) ? 7 : (($niveles[7] > 0) ? 8 : (($niveles[6] > 0) ? 6 : (($niveles[5] > 0) ? 5 : (($niveles[4] > 0) ? 4 : (($niveles[3] > 0) ? 3 : (($niveles[2] > 0) ? 2 : 1)))))));

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

$encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
$encabLin2 = 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO;

if ($_SESSION[_S]['nvj_id'] > 2) {
    switch ($reporte['tipo']) {
        case 2: // Todas las URs de la carga de trabajo
            $encabLin3 = 'DIRECCIÓN DE PRESUPUESTO';
            $encabLin6 = 'SECTOR ';
            break;
        case 3: // Determinar qué incluir y qué excluir
            $encabLin3 = 'DIRECCIÓN DE PRESUPUESTO';
            if (isset($_POST['nombreEspecial']) && $_POST['nombreEspecial'] != '') {
                $encabLin6 = trim($_POST['nombreEspecial']);
            } else {
                $encabLin6 = '';
            }
            break;
        default:
            $encabLin3 = 'DIRECCIÓN DE PRESUPUESTO: ' . $ur->getClave() . ' - ' . $ur->getNombre();
            $encabLin6 = '';
            break;
    }
} else {
    $encabLin3 = 'UR: ' . $ur->getClave() . ' - ' . $ur->getNombre();
    $encabLin6 = '';
}

if ($ue) {
    $encabLin4 = 'UE: ' . $ue->getUe_id() . ' - ' . $ue->getNombre();
} else {
    $encabLin4 = '';
}

switch ($opc_id) {
    case 3:
        $encabLin5 = 'AJUSTES';
        $datos = consultaMgaComplementos($ur->getGpo_id(), $ur->getUr_id(), '', '', '', $opc_id);
        break;
    default:
        break;
}

if ($programatico['epg_id'] != '') {
    $encProg = new enc_Programatico();
    $encProg->setEpg_id($programatico['epg_id']);
    $encProg->select();

    $encabLin6 = 'Obra/Actividad: ' . $encProg->getClave() . ' - ' . $encProg->getNombre();
}

if ($claveFinanciamiento['ecf_id'] != '') {
    $cvefin = new enc_ClaveFinanciamiento();
    $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
    $cvefin->select();

    $encabLin7 = 'Clave de Financiamiento: ' . $cvefin->getClave() . ' - ' . $cvefin->getNombre();
} elseif ($federal && $estatal) {
    $encabLin7 = '';
} elseif ($federal) {
    $encabLin7 = 'Recurso Federal';
} elseif ($estatal) {
    $encabLin7 = 'Recurso Estatal';
} else {
    $encabLin7 = '';
}

$anchoColumnas = [];
$nombreColumnas = [];
$incluirColumnas = [];
$tipoColumnas = [];

switch ($opc_id) {
    case 3:
        array_push($anchoColumnas, 9);
        array_push($nombreColumnas, 'GPO');
        array_push($incluirColumnas, 'gpo_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 9);
        array_push($nombreColumnas, 'UR');
        array_push($incluirColumnas, 'ur_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 8);
        array_push($nombreColumnas, 'UE_ID');
        array_push($incluirColumnas, 'ue_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, 'UNIDAD EJECUTORA');
        array_push($incluirColumnas, 'ue_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 8);
        array_push($nombreColumnas, 'PRG_ID');
        array_push($incluirColumnas, 'prg_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 50);
        array_push($nombreColumnas, 'PROGRAMA');
        array_push($incluirColumnas, 'prg_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 8);
        array_push($nombreColumnas, 'SPR_ID');
        array_push($incluirColumnas, 'spr_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 30);
        array_push($nombreColumnas, 'SUBPROGRAMA');
        array_push($incluirColumnas, 'spr_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 8);
        array_push($nombreColumnas, 'PY_ID');
        array_push($incluirColumnas, 'py_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 30);
        array_push($nombreColumnas, 'PROYECTO');
        array_push($incluirColumnas, 'py_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 8);
        array_push($nombreColumnas, 'OA_ID');
        array_push($incluirColumnas, 'oa_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 30);
        array_push($nombreColumnas, 'OBRA/ACTIVIDAD');
        array_push($incluirColumnas, 'oa_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 9);
        array_push($nombreColumnas, 'PG');
        array_push($incluirColumnas, 'pg_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 40);
        array_push($nombreColumnas, 'PARTIDA GENÉRICA');
        array_push($incluirColumnas, 'pg_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 9);
        array_push($nombreColumnas, 'PE');
        array_push($incluirColumnas, 'pe_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 40);
        array_push($nombreColumnas, 'PARTIDA ESPECÍFICA');
        array_push($incluirColumnas, 'pe_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 12);
        array_push($nombreColumnas, 'CFI_CLAVE');
        array_push($incluirColumnas, 'cfi_clave');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 35);
        array_push($nombreColumnas, 'CVEFIN');
        array_push($incluirColumnas, 'cfi_nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 9);
        array_push($nombreColumnas, 'TIPO');
        array_push($incluirColumnas, 'tcp_id');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 20);
        array_push($nombreColumnas, 'IMPORTE');
        array_push($incluirColumnas, 'total');
        array_push($tipoColumnas, 'n');

        break;
    default:
        array_push($anchoColumnas, 10);
        array_push($nombreColumnas, 'CLAVE');
        array_push($incluirColumnas, 'clave');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 30);
        array_push($nombreColumnas, 'NOMBRE');
        array_push($incluirColumnas, 'nombre');
        array_push($tipoColumnas, 'c');

        array_push($anchoColumnas, 5);
        array_push($nombreColumnas, 'ALIAS');
        array_push($incluirColumnas, 'alias');
        array_push($tipoColumnas, 'c');
        break;
}

$table = [];
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

// Armamos los parametros opcionales
$encabezado = ['encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3, 'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6, 'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas];
$otrosParametros = ['comprimir' => false, 'nombre' => $encabLin5];

$excel = new array2Excel();
$excel->generaExcel($datos, $encabezado, true, $table, $otrosParametros);
