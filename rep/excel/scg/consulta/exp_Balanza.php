<?php
//define("_S", "local17");
session_start();

//ini_set('memory_limit','600M');
//set_time_limit(0);
//librerias
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__).'/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__).'/../../../../ent/scg/cuentaContable/Rubro.php';
require_once dirname(__FILE__).'/../../../../lib/redondear.php';
require_once dirname(__FILE__).'/../../../../lib/MesNombre.php';
require_once dirname(__FILE__).'/../../../../lib/scg/consulta/balanza.php';
require_once dirname(__FILE__).'/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../lib/calculaPeso.php';

// Armamos los parámetros para pedir correctamente la balanza
$ur = new UnidadResponsable( );
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();
$ur_ent = '';

$nivel = $_SESSION[_S]['nvj_id'];
if ($nivel <= 2) {

    $ur_ent = $_SESSION[_S]['gpo_id'].$_SESSION[_S]['ur_id'];
} else {

    $ur_ent = $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'];
}
if (isset($_SESSION[_S]['clasAdm-ue'])) {
    $ue = new UnidadEjecutora( );
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
    $ue->select();
} elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
    $ue = new UnidadEjecutora( );
    $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ue->setUe_id(trim($_POST['ue']));
    $ue->select();
} else {
    $ue = false;
}

$ue_id = ( $ue ? $ue->getUe_id() : false );

$sis_id = ( isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = ( isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = ( isset($_POST['opc_id']) ? $_POST['opc_id'] : '');
if (isset($_POST['check2025']) && $_POST['check2025'] == 1) {
    $ejercicio = "2025";
}elseif (isset($_POST['check2024']) && $_POST['check2024'] == 1) {
    $ejercicio = "2024"; 
}elseif (isset($_POST['check2023']) && $_POST['check2023'] == 1) {
    $ejercicio = "2023"; 
}elseif (isset($_POST['check2022']) && $_POST['check2022'] == 1) {
    $ejercicio = "2022"; 
}elseif (isset($_POST['check2021']) && $_POST['check2021'] == 1) {
    $ejercicio = "2021";
}elseif (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
    $ejercicio = "2020";
}elseif (isset($_POST['check2019']) && $_POST['check2019'] == 1) {
    $ejercicio = "2019";
}elseif (isset($_POST['check2018']) && $_POST['check2018'] == 1) {
    $ejercicio = "2018";
}elseif (isset($_POST['check2017']) && $_POST['check2017'] == 1) {
    $ejercicio = "2017";
} elseif (isset($_POST['check2016']) && $_POST['check2016'] == 1) {
    $ejercicio = "2016";
} elseif (isset($_POST['check2015']) && $_POST['check2015'] == 1) {
    $ejercicio = "2015";
} else {
    $ejercicio = APP_EJERCICIO;
}
$formato2015 = isset($_POST['checkFormato2015']) ? $_POST['checkFormato2015'] : false;
$miles = ( ( isset($_POST['checkMiles']) && trim($_POST['checkMiles']) != '' ) ? $_POST['checkMiles']
        : false );
$movimiento = ( isset($_POST['checkMovimiento']) && $_POST['checkMovimiento'] == 1 )
        ? true : false;

$periodo['mesDesde'] = ( ( isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
    != '' ) ? $_POST['comboMesDesde'] : -1 );
$periodo['mesHasta'] = ( ( isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta'])
    != '' ) ? $_POST['comboMesHasta'] : -1 );


$niveles[1] = ( ( isset($_POST['checkNivel1']) && trim($_POST['checkNivel1']) != '' )
        ? $_POST['checkNivel1'] : 1 );
$niveles[2] = ( ( isset($_POST['checkNivel2']) && trim($_POST['checkNivel2']) != '' )
        ? $_POST['checkNivel2'] : -1 );
$niveles[3] = ( ( isset($_POST['checkNivel3']) && trim($_POST['checkNivel3']) != '' )
        ? $_POST['checkNivel3'] : -1 );
$niveles[4] = ( ( isset($_POST['checkNivel4']) && trim($_POST['checkNivel4']) != '' )
        ? $_POST['checkNivel4'] : -1 );
$niveles[5] = ( ( isset($_POST['checkNivel5']) && trim($_POST['checkNivel5']) != '' )
        ? $_POST['checkNivel5'] : -1 );
$niveles[6] = ( ( isset($_POST['checkNivel6']) && trim($_POST['checkNivel6']) != '' )
        ? $_POST['checkNivel6'] : -1 );
$niveles[7] = ( ( isset($_POST['checkNivel7']) && trim($_POST['checkNivel7']) != '' )
        ? $_POST['checkNivel7'] : -1 );
$niveles[8] = ( ( isset($_POST['checkNivelR']) && trim($_POST['checkNivelR']) != '' )
        ? $_POST['checkNivelR'] : -1 );
$niveles[9] = ( ( isset($_POST['checkNivelGur']) && trim($_POST['checkNivelGur'])
    != '' ) ? $_POST['checkNivelGur'] : -1 );

$niveles[0] = ( $niveles[9] > 0 ) ? 9 : (
    ( $niveles[8] > 0 ) ? 8 : (
    ( $niveles[7] > 0 ) ? 7 : (
    ( $niveles[6] > 0 ) ? 6 : (
    ( $niveles[5] > 0 ) ? 5 : (
    ( $niveles[4] > 0 ) ? 4 : (
    ( $niveles[3] > 0 ) ? 3 : (
    ( $niveles[2] > 0 ) ? 2 : 1
    )
    )
    )
    )
    )
    )
    );

#opcion Cuenta
$busqueda['cuentaDesde'] = isset($_POST['inputCuentaDesde']) ? ( $_POST['inputCuentaDesde']
    != "" ? $_POST['inputCuentaDesde'] : 10000 ) : 10000;
$busqueda['cuentaHasta'] = isset($_POST['inputCuentaHasta']) ? ( $_POST['inputCuentaHasta']
    != "" ? $_POST['inputCuentaHasta'] : 99999 ) : 99999;


if (!$formato2015 && ($ejercicio == APP_EJERCICIO)) {

    $busqueda['tipo'] = ( isset($_POST['checkGur']) && $_POST['checkGur'] == 1 ? 4
            : ( isset($_POST['checkClc']) && $_POST['checkClc'] == 1 ? 2 : ( isset($_POST['checkBdo'])
        && $_POST['checkBdo'] == 1 ? 3 : 1 ) ) );
    #opcion UR
    $busqueda['gur'] = isset($_POST['inputGur']) ? $_POST['inputGur'] : -1; //UR a buscar
    #opcion CLC
    $busqueda['clc_id'] = isset($_POST['inputClc']) ? $_POST['inputClc'] : -1; //CLC a buscar
    #opcion Bdo
    $busqueda['bdo_id'] = isset($_POST['inputBdo']) ? $_POST['inputBdo'] : -1; //Bdo a buscar

    $reporte['tipo'] = ( ( isset($_POST['tipoReporte']) && $_POST['tipoReporte']
        != '' ) ? $_POST['tipoReporte'] : 1 );
    $reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr']
            : "";
    switch ($reporte['tipo']) {
        case 2:
            $ue = false;
            $ue_id = false;
            break;
        case 3:
            $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos']
                    : "";
            $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir']
                    : "";
            $ue = false;
            $ue_id = false;
            break;
    }

    $programatico['epg_id'] = '';
    $estatal = ( isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1 ) ? true
            : false;
    $federal = ( isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1 ) ? true
            : false;
    $claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
    $claveFinanciamiento['ter_id'] = ( $claveFinanciamiento['ecf_id'] != '' ) ? ''
            : ( ( $federal && $estatal ) ? '' : ( $federal ? 'B' : ( $estatal ? 'A'
            : '' ) ) );

    // Marcos: 18/mayo/2016
    // Adicionamos un indicador para saber si la petición viene de la pantalla balanzaDetalle
    // ya que cambia un poco el where
    $detalle = isset($_POST['detalle']) ? true : false;
} else {
    $busqueda['tipo'] = 1;

    $reporte['tipo'] = ( ( isset($_POST['tipoReporte']) && $_POST['tipoReporte']
        != '' ) ? $_POST['tipoReporte'] : 1 );
    $reporte['agrupador'] = "";
    if ($reporte['tipo'] == 3) {
        $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos']
                : "";
        $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir']
                : "";
    }

    $programatico['epg_id'] = '';
    $estatal = false;
    $federal = false;
    $claveFinanciamiento['ecf_id'] = '';
    $claveFinanciamiento['ter_id'] = '';

    $detalle = false;
}

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL ".APP_EJERCICIO;

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
            $encabLin3 = "DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL: ".$ur->getClave()." - ".$ur->getNombre();
            $encabLin6 = '';
            break;
    }
} else {
    $encabLin3 = "UR: ".$ur->getClave()." - ".$ur->getNombre();
    $encabLin6 = '';
}

if ($ue) {
    $encabLin4 = "UE: ".$ue->getUe_id()." - ".$ue->getNombre();
} else {
    $encabLin4 = '';
}

if ($periodo['mesDesde'] == $periodo['mesHasta']) {
    if ($periodo['mesHasta'] == 0)
            $encabLin5 = "BALANZA DE COMPROBACIÓN DEL MES DE APERTURA $ejercicio";
    else
            $encabLin5 = "BALANZA DE COMPROBACIÓN DEL MES DE ".MesNombre($periodo['mesDesde'])." DE $ejercicio ";
}else {
    $encabLin5 = "BALANZA DE COMPROBACIÓN CORRESPONDIENTE AL PERIODO ".MesNombre($periodo['mesDesde'])."-".MesNombre($periodo['mesHasta'])." DE $ejercicio ";
}
if ($programatico['epg_id'] != '') {
    $encProg = new enc_Programatico();
    $encProg->setEpg_id($programatico['epg_id']);
    $encProg->select();

    $encabLin6 = "Obra/Actividad: ".$encProg->getClave()." - ".$encProg->getNombre();
}

if ($claveFinanciamiento['ecf_id'] != '') {
    $cvefin = new enc_ClaveFinanciamiento();
    $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
    $cvefin->select();

    $encabLin7 = "Clave de Financiamiento: ".$cvefin->getClave()." - ".$cvefin->getNombre();
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
if ($detalle) {
    $arrCta = explode(".", $busqueda['cuentaDesde']);
    $elementos = count($arrCta);

    if ($elementos != 3) {
        echo "verificar cuenta de mayor ";
        die();
    }
    $ger_id = $arrCta[0];
    $cgp_id = $arrCta[1];
    $rub_id = $arrCta[2];
    $rubro = new Rubro( );
    $rubro->setGer_id($ger_id);
    $rubro->setCgp_id($cgp_id);
    $rubro->setRub_id($rub_id);
    $rubro->select();

    $encabLin7 = "(del rubro ".$rubro->getCuenta()." - ".$rubro->getNombre().")";
}

$datos = balanza($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'],
    $ue_id, '', $periodo, '', $claveFinanciamiento, $reporte, $detalle, '',
    $niveles,
    array("busqueda" => $busqueda, "miles" => $miles, "movimiento" => $movimiento,
    "ejercicio" => $ejercicio));

  foreach ($datos as $key => $dato) {

        $datos[$key]['pdx_debeInicial']= $dato['pdx_debeInicial'];
        $datos[$key]['pdx_haberInicial']= $dato['pdx_haberInicial'];
        $datos[$key]['pdx_debePeriodo']= $dato['pdx_debePeriodo'];
        $datos[$key]['pdx_haberPeriodo']= $dato['pdx_haberPeriodo'];
        $datos[$key]['pdx_debeFinal']= $dato['pdx_debeFinal'];
        $datos[$key]['pdx_haberFinal']= $dato['pdx_haberFinal'];

    }

// Valores para el archivo de exportación
$anchoColumnas = array(20, 50, 15, 15, 15, 15, 15, 15, 12, 7);
$nombreColumnas = array("CUENTA", "DESCRIPCION", "SI Debe", "SI Haber", "Debe", "Haber",
    "SF Debe", "SF Haber", "NATURALEZA", "NIVEL");
$incluirColumnas = array("cuenta", "nombre", "pdx_debeInicial", "pdx_haberInicial",
    "pdx_debePeriodo", "pdx_haberPeriodo", "pdx_debeFinal", "pdx_haberFinal", "cgm_naturaleza",
    "nivel");
$tipoColumnas = array("c", "c", "n", "n", "n", "n", "n", "n", "c", "c");

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

$tablaimg['imagen'] = 1;
$tablaimg[0]['ruta'] = "../../../../img/logoOaxaca-color-fblanco_v2.png";
$tablaimg[0]['colImg'] = "F1";
$tablaimg[0]['alingXimg'] = "80";
$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";

if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);

}
// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas, 'alineacionenc' => 'C');
$otrosParametros = array('nombre' => "BALANZA", 'sufijoNombre' => "-".$periodo['mesDesde']."_".$periodo['mesHasta'], 'tablaimg' => $tablaimg);

$NUMREG = count($datos);
$columnas = count(array_values($table)[0]) * 5;

$peso = calcularPeso($NUMREG, $columnas);

if ($peso < 3 ){
    $peso =4;
}
ini_set('memory_limit', ($peso * 20)."M");
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
