<?php
session_start();

//ini_set('memory_limit','600M');
//set_time_limit(0);
//librerias
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__).'/../../../../ent/enc_ClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../ent/enc_Programatico.php';
require_once dirname(__FILE__).'/../../../../ent/scp/Clc.php';
require_once dirname(__FILE__).'/../../../../ent/scg/vinculacion/vinculacion/BancoDocumento.php';
require_once dirname(__FILE__).'/../../../../ent/CatalogoProveedores.php';
require_once dirname(__FILE__).'/../../../../lib/redondear.php';
require_once dirname(__FILE__).'/../../../../lib/MesNombre.php';
require_once dirname(__FILE__).'/../../../../lib/scg/vinculacion/vinculacionProvision.php';
require_once dirname(__FILE__).'/../../../../lib/compararCadenas.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';

//echo "<pre>";print_r($_POST);echo "</pre>";die();//*
// Armamos los parámetros para pedir correctamente la balanza
$ur = new UnidadResponsable( );
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();

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
$mmc_id = ( isset($_POST['mmc_id']) ? $_POST['mmc_id'] : '');

if (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
    $ejercicio = "2020";
} elseif  (isset($_POST['check2019']) && $_POST['check2019'] == 1) {
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

$gur = $_SESSION[_S]['clasAdm-gpo'].$_SESSION[_S]['clasAdm-ur'];
$clc_id = isset($_POST['inputClc_id']) ? ( $_POST['inputClc_id'] != '' ? $_POST['inputClc_id']
        : '' ) : '';

#opcion
$bdo_id = isset($_POST['inputBdo_id']) ? ( $_POST['inputBdo_id'] != '' ? $_POST['inputBdo_id']
        : '' ) : '';
$rfc_id = isset($_POST['inputRfc_id']) ? ( $_POST['inputRfc_id'] != '' ? $_POST['inputRfc_id']
        : '' ) : '';
$bdo_referencia = '';
if (isset($_POST['inputBdo_referencia']) && !empty($_POST['inputBdo_referencia'])) {
    $referencias = explode(",",
        str_replace(' ', '', $_POST['inputBdo_referencia']));
    foreach ($referencias as $bdo) {
        $bdo_referencia .= '"'.$bdo.'",';
    }
    $bdo_referencia = substr($bdo_referencia, 0, -1);
}

$periodo['mesDesde'] = ( ( isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
    != "" ) ? $_POST['comboMesDesde'] : -1 );
$periodo['mesHasta'] = $periodo['mesDesde'];

$reporte['tipo'] = 1;
$programatico['epg_id'] = '';
$claveFinanciamiento['ecf_id'] = '';
$claveFinanciamiento['ter_id'] = '';

$niveles[9] = 9;
$niveles[8] = 0;
$niveles[7] = 0;
$niveles[6] = 6;
$niveles[5] = 0;
$niveles[4] = 0;
$niveles[3] = 0;
$niveles[2] = 0;
$niveles[1] = 1;
$detalle = FALSE;

/* echo "<gur>".$gur."<br>";
  echo "<clc_id>".$clc_id."<br>";
  echo "<bdo_id>".$bdo_id."<br>";
  echo "<rfc_id>".$rfc_id."<br>";
  echo "<bdo_referencia>".$bdo_referencia."<br>"; */

$datos = vinculacionProvision(
    $_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue_id, '',
    $periodo, '', $claveFinanciamiento, $reporte, $detalle, 'N', $niveles,
    array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
    "bdo_referencia" => $bdo_referencia)
);

$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = "CONTABILIDAD GUBERNAMENTAL " . $_SESSION[_S]['ejercicioActual'];
$encabLin3 = "UR: ".$ur->getClave()." - ".$ur->getNombre();
$encabLin4 = '';
$encabLin5 = "REPORTE DE VINCULACIÓN DE PROVISIÓN DEL MES DE ".MesNombre($periodo['mesDesde']);
$encabLin6 = '';

if ($clc_id != '') {
    $clc = new Clc( );
    $clc->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $clc->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $clc->setClc_id($clc_id);
    $clc->select();
    //$encabLin5 = "REPORTE DE VINCULACIÓN DEL PAGADO";
    $encabLin6 = "de la CLC ".$clc->getClc_id()."-".$clc->getConcepto();
}
if ($bdo_id != '') {
    $documento = new BancoDocumentos( );
    $documento->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $documento->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $documento->setBdo_id($bdo_id);
    $documento->select();
    //$encabLin5 = "REPORTE DE VINCULACIÓN DEL PAGADO";
    $encabLin7 = "del documento ".$documento->getRfc_id()."-".$documento->getReferencia();
} elseif ($rfc_id != '' && $bdo_referencia == '') {
    $proveedor = new Proveedores( );
    $proveedor->setRfc_id($rfc_id);
    $proveedor->select();
    //$encabLin5 = "REPORTE DE VINCULACIÓN DEL PAGADO";
    $encabLin7 = "del proveedor ".$proveedor->getRfc_id()."-".$proveedor->getNombre().( $proveedor->getSoc_id()
        == 0 ? " ".$proveedor->getPaterno()." ".$proveedor->getMaterno() : ", ".$proveedor->getSoc_id() );
} elseif ($rfc_id != '' && $bdo_referencia != '') {
    $documento = new BancoDocumentos( );
    $documento->setRfc_id($rfc_id);
    $documento->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $documento->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $documento->setReferencia($bdo_referencia);
    $documento->select();
    //$encabLin5 = "REPORTE DE VINCULACIÓN DEL PAGADO";
    $encabLin7 = "del documento ".$documento->getRfc_id()."-".$documento->getReferencia();
}

//echo "<pre>"; print_r($datos); echo "</pre>"; die();

$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "AFE");
array_push($incluirColumnas, "afe_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "DOC");
array_push($incluirColumnas, "bdo_id");
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
array_push($incluirColumnas, "bdo_referencia");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "CONCEPTO");
array_push($incluirColumnas, "bdo_concepto");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "IMPORTE");
array_push($incluirColumnas, "bdo_importe");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "ESTADO");
array_push($incluirColumnas, "estado");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "AUTORIZACIÓN");
array_push($incluirColumnas, "fechaAutorizacion");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "CANCELACIÓN");
array_push($incluirColumnas, "fechaCancelacion");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "POL AUT.");
array_push($incluirColumnas, "polizaAutorizado");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "FECHA");
array_push($incluirColumnas, "fechaAutorizado");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "POL CAN.");
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
$table['textoCompleto']=FALSE;
$tablaimg['imagen'] = 1;
$tablaimg[0]['nombre'] = "Logo";
//if ($nivel >= 3) {
    $tablaimg[0]['ruta'] = "../../../../img/escudocolor_derecha.png";

    $tablaimg[0]['colImg'] = "K1";
    $tablaimg[0]['alingXimg'] = "10";
/*} else {
    $tablaimg[0]['ruta'] = "../../../../img/Logo Institucional/".$ur->getClave().".jpg";

    $tablaimg[0]['alingXimg'] = "40";
    $tablaimg[0]['colImg'] = "K1";
}*/

$tablaimg[0]['altura'] = "75";
$tablaimg[0]['rotacion'] = "0";
if (!is_file($tablaimg[0]['ruta'])) {
    $tablaimg['imagen'] = 0;
    unset($tablaimg[0]);
    // die();
}
// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
    'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
    'encabLin7' => $encabLin7, 'ancho' => $anchoColumnas);
$otrosParametros = array('comprimir' => FALSE, 'nombre' => "Provision",
    'sufijoNombre' => "-".$periodo['mesDesde']."_".$periodo['mesHasta'],
    'tablaimg' => $tablaimg);
//$excel = new array2Excel();
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);
//
?>
