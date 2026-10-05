<?php
session_start();
//Hazael Hernandez Febrero 2018
//librerias
require_once dirname(__FILE__) . "/../../../../lib/scg/reporte/asientoMayor.php";
require_once dirname(__FILE__) . "/../../../../lib/scg/reporte/asientoDetalle.php";
require_once dirname(__FILE__) . "/../../../lib/array2Excel.php";

require_once dirname(__FILE__) . '/../../../../lib/calculaPeso.php';

$mesDesde = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1);
$mesHasta = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1);

$periodo = ($mesDesde == $mesHasta) ? "AND msc_id = " . $mesDesde : "AND ( msc_id BETWEEN " . $mesDesde . " AND " . $mesHasta . " )";

/**********Maestro**********/
$datosMaestro = asientoMayor($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $periodo);

if ($datosMaestro == null) {
    echo '<script>alert("No hay registros para generar el Reporte");</script>';
    echo '<script>window.close();</script>';
    exit;
}

$pesoExcelMaestro = calcularPeso(count($datosMaestro), 23);

$anchoColumnasMaestro = array();
$nombreColumnasMaestro = array();
$incluirColumnasMaestro = array();
$tipoColumnasMaestro = array();

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "gpo_id");
array_push($incluirColumnasMaestro, "gpo_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "ur_id");
array_push($incluirColumnasMaestro, "ur_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "ue_id");
array_push($incluirColumnasMaestro, "ue_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "pot_id");
array_push($incluirColumnasMaestro, "pot_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "prf_id");
array_push($incluirColumnasMaestro, "prf_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "msc_id");
array_push($incluirColumnasMaestro, "msc_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "pol_numero");
array_push($incluirColumnasMaestro, "pol_numero");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 30);
array_push($nombreColumnasMaestro, "pol_debe");
array_push($incluirColumnasMaestro, "pol_debe");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 30);
array_push($nombreColumnasMaestro, "pol_haber");
array_push($incluirColumnasMaestro, "pol_haber");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 100);
array_push($nombreColumnasMaestro, "pol_concepto");
array_push($incluirColumnasMaestro, "pol_concepto");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 30);
array_push($nombreColumnasMaestro, "pol_fecha");
array_push($incluirColumnasMaestro, "pol_fecha");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 40);
array_push($nombreColumnasMaestro, "pol_vinculacion");
array_push($incluirColumnasMaestro, "pol_vinculacion");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 20);
array_push($nombreColumnasMaestro, "pol_tipo");
array_push($incluirColumnasMaestro, "pol_tipo");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 20);
array_push($nombreColumnasMaestro, "pol_clc");
array_push($incluirColumnasMaestro, "pol_clc");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "mmc_id");
array_push($incluirColumnasMaestro, "mmc_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "epl_id");
array_push($incluirColumnasMaestro, "epl_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "apg_id");
array_push($incluirColumnasMaestro, "apg_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 20);
array_push($nombreColumnasMaestro, "cfi_clave");
array_push($incluirColumnasMaestro, "cfi_clave");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 30);
array_push($nombreColumnasMaestro, "ore_idObra");
array_push($incluirColumnasMaestro, "ore_idObra");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 25);
array_push($nombreColumnasMaestro, "usr_idModificado");
array_push($incluirColumnasMaestro, "usr_idModificado");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 25);
array_push($nombreColumnasMaestro, "usr_fechaModificado");
array_push($incluirColumnasMaestro, "usr_fechaModificado");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 10);
array_push($nombreColumnasMaestro, "usr_id");
array_push($incluirColumnasMaestro, "usr_id");
array_push($tipoColumnasMaestro, "l");

array_push($anchoColumnasMaestro, 30);
array_push($nombreColumnasMaestro, "usr_fecha");
array_push($incluirColumnasMaestro, "usr_fecha");
array_push($tipoColumnasMaestro, "l");

$table = array();
foreach ($incluirColumnasMaestro as $key => $val) {
    $table['maestro']['nombre'][$val] = $nombreColumnasMaestro[$key];
    $table['maestro']['ancho'][$val] = $anchoColumnasMaestro[$key];
    $table['maestro']['campo'][$val] = $key;
    $table['maestro']['tipo'][$val] = $tipoColumnasMaestro[$key];
}

/**********Detalle**********/
$datosDetalle = asientoDetalle($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $periodo, $pesoExcelMaestro);

$anchoColumnasDetalle = array();
$nombreColumnasDetalle = array();
$incluirColumnasDetalle = array();
$tipoColumnasDetalle = array();

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "gpo_id");
array_push($incluirColumnasDetalle, "gpo_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "ur_id");
array_push($incluirColumnasDetalle, "ur_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "ue_id");
array_push($incluirColumnasDetalle, "ue_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "pot_id");
array_push($incluirColumnasDetalle, "pot_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "prf_id");
array_push($incluirColumnasDetalle, "prf_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "msc_id");
array_push($incluirColumnasDetalle, "msc_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "pol_numero");
array_push($incluirColumnasDetalle, "pol_numero");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_numero");
array_push($incluirColumnasDetalle, "dpo_numero");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "ger_id");
array_push($incluirColumnasDetalle, "ger_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cgp_id");
array_push($incluirColumnasDetalle, "cgp_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "rub_id");
array_push($incluirColumnasDetalle, "rub_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cgcu_id");
array_push($incluirColumnasDetalle, "cgcu_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cgsc_id");
array_push($incluirColumnasDetalle, "cgsc_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cta_gpo_id");
array_push($incluirColumnasDetalle, "cta_gpo_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cta_ur_id");
array_push($incluirColumnasDetalle, "cta_ur_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cta_ue_id");
array_push($incluirColumnasDetalle, "cta_ue_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cgc_id");
array_push($incluirColumnasDetalle, "cgc_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cta_id");
array_push($incluirColumnasDetalle, "cta_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_debe");
array_push($incluirColumnasDetalle, "dpo_debe");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_haber");
array_push($incluirColumnasDetalle, "dpo_haber");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_naturaleza");
array_push($incluirColumnasDetalle, "dpo_naturaleza");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_concepto");
array_push($incluirColumnasDetalle, "dpo_concepto");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "rfc_id");
array_push($incluirColumnasDetalle, "rfc_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_referencia");
array_push($incluirColumnasDetalle, "dpo_referencia");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_fechaReferencia");
array_push($incluirColumnasDetalle, "dpo_fechaReferencia");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "dpo_vinculacion");
array_push($incluirColumnasDetalle, "dpo_vinculacion");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "cfi_clave");
array_push($incluirColumnasDetalle, "cfi_clave");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "tdf_id");
array_push($incluirColumnasDetalle, "tdf_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "adf_id");
array_push($incluirColumnasDetalle, "adf_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "epl_id");
array_push($incluirColumnasDetalle, "epl_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "mmc_id");
array_push($incluirColumnasDetalle, "mmc_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "usr_idModificado");
array_push($incluirColumnasDetalle, "usr_idModificado");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "usr_fechaModificado");
array_push($incluirColumnasDetalle, "usr_fechaModificado");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "usr_id");
array_push($incluirColumnasDetalle, "usr_id");
array_push($tipoColumnasDetalle, "l");

array_push($anchoColumnasDetalle, 20);
array_push($nombreColumnasDetalle, "usr_fecha");
array_push($incluirColumnasDetalle, "usr_fecha");
array_push($tipoColumnasDetalle, "l");

foreach ($incluirColumnasDetalle as $key => $val) {
    $table['detalle']['nombre'][$val] = $nombreColumnasDetalle[$key];
    $table['detalle']['ancho'][$val] = $anchoColumnasDetalle[$key];
    $table['detalle']['campo'][$val] = $key;
    $table['detalle']['tipo'][$val] = $tipoColumnasDetalle[$key];
}

$datos = array('maestro' => $datosMaestro, 'detalle' => $datosDetalle);

// Armamos los parametros opcionales
$encabezado = array('maestro' => array(), 'detalle' => array());
$otrosParametros = array('comprimir' => false, 'nombre' => "ASIENTO", 'sufijoNombre' => "-" . $mesDesde . "_" . $mesHasta,
    'maestro' => array('nombre' => 'Maestro'), 'detalle' => array('nombre' => 'Detalle'),
    'book' => array('maestro', 'detalle'));

$excel = new array2Excel();
$excel->generaExcel($datos, $encabezado, true, $table, $otrosParametros);
