<?php
session_start();

//librerias
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__).'/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__).'/../../../../ent/scg/cuentaContable/PlanCuentas.php';
require_once dirname(__FILE__).'/../../../../ent/scg/cuentaContable/CuentaCuarto.php';
require_once dirname(__FILE__).'/../../../../ent/scg/cuentaContable/CgSubcuenta.php';
require_once dirname(__FILE__).'/../../../../ent/scp/Clc.php';
require_once dirname(__FILE__).'/../../../../lib/redondear.php';
require_once dirname(__FILE__).'/../../../../lib/MesNombre.php';
require_once dirname(__FILE__).'/../../../../lib/scg/consulta/contable.php';
require_once dirname(__FILE__).'/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__)."/../../../lib/generaPhpspreadsheet.php";

// Armamos los parámetros para pedir correctamente la balanza
$ur = new UnidadResponsable( );
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();
$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];
if ($_SESSION[_S]['nvj_id'] <= 2) {
    
    $ur_ent = $_SESSION[_S]['gpo_id'].$_SESSION[_S]['ur_id'];
} else {

    $ur_ent = $_SESSION[_S]['clasAdm-ur'].$_SESSION[_S]['clasAdm-gpo'];
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
    $ue = FALSE;
}

$ue_id = ( $ue ? $ue->getUe_id() : FALSE );

$sis_id = ( isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = ( isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = ( isset($_POST['opc_id']) ? $_POST['opc_id'] : '');
$ecf = ((isset($_POST['ecf_id']) && $_POST['ecf_id'] != "") ? $_POST['ecf_id'] : "" );

if (isset($_POST['check2025']) && $_POST['check2025'] == 1) {
    $ejercicio = "2025";
}elseif (isset($_POST['check2024']) && $_POST['check2024'] == 1) {
    $ejercicio = "2024";
}elseif  (isset($_POST['check2023']) && $_POST['check2023'] == 1) {
    $ejercicio = "2023";
}elseif  (isset($_POST['check2022']) && $_POST['check2022'] == 1) {
    $ejercicio = "2022";
}elseif  (isset($_POST['check2021']) && $_POST['check2021'] == 1) {
    $ejercicio = "2021";
}elseif  (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
    $ejercicio = "2020";
}elseif  (isset($_POST['check2019']) && $_POST['check2019'] == 1) {
    $ejercicio = "2019";
}elseif (isset($_POST['check2018']) && $_POST['check2018'] == 1) {
    $ejercicio = "2018";
}elseif (isset($_POST['check2017']) && $_POST['check2017'] == 1) {
    $ejercicio = "2016";
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
    $ejercicio = "2026";
}

$miles = ( ( isset($_POST['checkMiles']) && trim($_POST['checkMiles']) != "" ) ? $_POST['checkMiles']
        : false );
$movimiento = ( isset($_POST['checkMovimiento']) && $_POST['checkMovimiento'] == 1 )
        ? true : false;

$periodo['mesDesde'] = ( ( isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
    != "" ) ? $_POST['comboMesDesde'] : -1 );
$periodo['mesHasta'] = $periodo['mesDesde'];

$niveles[1] = ( ( isset($_POST['checkNivel1']) && trim($_POST['checkNivel1']) != "" )
        ? $_POST['checkNivel1'] : 1 );
$niveles[2] = ( ( isset($_POST['checkNivel2']) && trim($_POST['checkNivel2']) != "" )
        ? $_POST['checkNivel2'] : -1 );
$niveles[3] = ( ( isset($_POST['checkNivel3']) && trim($_POST['checkNivel3']) != "" )
        ? $_POST['checkNivel3'] : -1 );
$niveles[4] = ( ( isset($_POST['checkNivel4']) && trim($_POST['checkNivel4']) != "" )
        ? $_POST['checkNivel4'] : -1 );
$niveles[5] = ( ( isset($_POST['checkNivel5']) && trim($_POST['checkNivel5']) != "" )
        ? $_POST['checkNivel5'] : -1 );
$niveles[6] = ( ( isset($_POST['checkNivel6']) && trim($_POST['checkNivel6']) != "" )
        ? $_POST['checkNivel6'] : -1 );
$niveles[7] = ( ( isset($_POST['checkNivel7']) && trim($_POST['checkNivel7']) != "" )
        ? $_POST['checkNivel7'] : -1 );
$niveles[8] = ( ( isset($_POST['checkNivelR']) && trim($_POST['checkNivelR']) != "" )
        ? $_POST['checkNivelR'] : -1 );

$niveles[0] = ( $niveles[8] > 0 ) ? 8 : (
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
    );

$inputGur = isset($_POST['inputGur']) ? ( $_POST['inputGur'] != '' ? $_POST['inputGur']
        : '' ) : '';
$inputClc_id = isset($_POST['inputClc_id']) ? ( $_POST['inputClc_id'] != '' ? $_POST['inputClc_id']
        : '' ) : '';

#opcion Cuenta
$busqueda['cuentaMayor'] = isset($_POST['inputCuentaMayor']) ? ( $_POST['inputCuentaMayor']
    != '' ? $_POST['inputCuentaMayor'] : '' ) : '';
$busqueda['cuentaSefin'] = isset($_POST['inputCuentaSefin']) ? ( $_POST['inputCuentaSefin']
    != '' ? $_POST['inputCuentaSefin'] : '' ) : '';
$busqueda['cuentaRegistro'] = isset($_POST['inputCuentaRegistro']) ? ( $_POST['inputCuentaRegistro']
    != '' ? $_POST['inputCuentaRegistro'] : '' ) : '';

$reporte['tipo'] = ( ( isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '' )
        ? $_POST['tipoReporte'] : 1 );
$reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr']
        : "";
if ($reporte['tipo'] == 3) {
    $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
    $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir']
            : "";
}

$programatico['epg_id'] = '';

$estatal = ( isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1 ) ? true
        : false;
$federal = ( isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1 ) ? true
        : false;
$claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
$claveFinanciamiento['ter_id'] = ( $claveFinanciamiento['ecf_id'] != '' ) ? '' : ( ( $federal
    && $estatal ) ? '' : ( $federal ? 'B' : ( $estatal ? 'A' : '' ) ) );

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL " . APP_EJERCICIO;

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
            $encabLin5 = "LIBRO MAYOR DEL MES DE APERTURA $ejercicio";
    else
            $encabLin5 = "LIBRO MAYOR DEL MES DE ".MesNombre($periodo['mesDesde'])." DE $ejercicio ";
}else {
    $encabLin5 = "LIBRO MAYOR CORRESPONDIENTE AL PERIODO ".MesNombre($periodo['mesDesde'])."-".MesNombre($periodo['mesHasta'])." DE $ejercicio ";
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

if ($busqueda['cuentaRegistro'] != '') {
    $arrCta = explode(".", $busqueda['cuentaMayor']);
    $elementos = count($arrCta);
    if ($elementos != 5) {
        echo "verificar cuenta de mayor ";
        die();
    }

    $ger_id = $arrCta[0];
    $cgp_id = $arrCta[1];
    $rub_id = $arrCta[2];
    $cgcu_id = $arrCta[3];
    $cgsc_id = $arrCta[4];
    $cgc_id = $busqueda['cuentaSefin'];
    $cta_id = $busqueda['cuentaRegistro'];
    $planCuentas = new PlanCuentas( );
    $planCuentas->setCta_gpo_id($ur->getGpo_id());
    $planCuentas->setCta_ur_id($ur->getUr_id());
    $planCuentas->setCta_ue_id($ue_id ? $ue_id : $ur->getUeContable() );
    $planCuentas->setGer_id($ger_id);
    $planCuentas->setCgp_id($cgp_id);
    $planCuentas->setRub_id($rub_id);
    $planCuentas->setCgcu_id($cgcu_id);
    $planCuentas->setCgsc_id($cgsc_id);
    $planCuentas->setCgc_id($cgc_id);
    $planCuentas->setCta_id($cta_id);
    $planCuentas->select();

    $encabLin7 = "de la cuenta ".$planCuentas->getCuenta()."-".$planCuentas->getNombre();
} elseif ($busqueda['cuentaSefin'] != '') {
    $arrCta = explode(".", $busqueda['cuentaMayor']);
    $elementos = count($arrCta);
    if ($elementos != 5) {
        echo "verificar cuenta de mayor ";
        die();
    }

    $ger_id = $arrCta[0];
    $cgp_id = $arrCta[1];
    $rub_id = $arrCta[2];
    $cgcu_id = $arrCta[3];
    $cgsc_id = $arrCta[4];
    $cgc_id = $busqueda['cuentaSefin'];
    $sefin = new CuentaCuarto( );
    $sefin->setGer_id($ger_id);
    $sefin->setCgp_id($cgp_id);
    $sefin->setRub_id($rub_id);
    $sefin->setCgcu_id($cgcu_id);
    $sefin->setCgsc_id($cgsc_id);
    $sefin->setCgc_id($cgc_id);
    $sefin->select();

    $encabLin7 = "de la cuenta ".$sefin->getCuenta()."-".$sefin->getNombre();
} elseif ($busqueda['cuentaMayor'] != '') {
    $arrCta = explode(".", $busqueda['cuentaMayor']);
    $elementos = count($arrCta);
    if ($elementos != 5) {
        echo "verificar cuenta de mayor ";
        die();
    }

    $ger_id = $arrCta[0];
    $cgp_id = $arrCta[1];
    $rub_id = $arrCta[2];
    $cgcu_id = $arrCta[3];
    $cgsc_id = $arrCta[4];

    $cgsubcuenta = new CgSubcuenta( );
    $cgsubcuenta->setGer_id($ger_id);
    $cgsubcuenta->setCgp_id($cgp_id);
    $cgsubcuenta->setRub_id($rub_id);
    $cgsubcuenta->setCgcu_id($cgcu_id);
    $cgsubcuenta->setCgsc_id($cgsc_id);
    $cgsubcuenta->select();

    $encabLin7 = "de la cuenta ".$cgsubcuenta->getCuenta()."-".$cgsubcuenta->getNombre();
}
if ($inputClc_id != '') {
    $clc = new Clc( );
    $clc->setGpo_id($ur->getGpo_id());
    $clc->setUr_id($ur->getUr_id());
    $clc->setClc_id($inputClc_id);
    $clc->select();

    $encabLin7 .= " de la CLC ".$clc->getClc_id()."-".$clc->getConcepto();
}

$datos = contable($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'],
    $ue_id, '', $periodo, '', $ecf, $reporte, FALSE, '', $niveles,
    array("busqueda" => $busqueda, "gur" => $inputGur, "clc_id" => $inputClc_id));

// Valores para el archivo de exportación
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "PÓLIZA");
array_push($incluirColumnas, "poliza");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "pma_fecha");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "UE CUENTA");
array_push($incluirColumnas, "cta_ue_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "CUENTA");
array_push($incluirColumnas, "cta_cuenta");
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

if ($_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'] == 116) {
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
array_push($nombreColumnas, "TG");
array_push($incluirColumnas, "tg_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "NOMBRE");
array_push($incluirColumnas, "tg_nombre");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 5);
array_push($nombreColumnas, "IDP");
array_push($incluirColumnas, "idp_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "NOMBRE");
array_push($incluirColumnas, "idp_nombre");
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

array_push($anchoColumnas, 5);
array_push($nombreColumnas, "TR");
array_push($incluirColumnas, "ter_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "NOMBRE");
array_push($incluirColumnas, "ter_nombre");
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

$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}
$table['textoCompleto']=FALSE;
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";

    $tablaimg[0]['ruta'] = "../../../../img/logoOaxaca-color-fblanco_v2.png";
    $tablaimg[0]['colImg'] = "L1";
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
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => FALSE,
    'nombre' => "LibroMayor", 'sufijoNombre' => "-".$periodo['mesDesde']."_".$periodo['mesHasta'],
    'tablaimg' => $tablaimg);
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
