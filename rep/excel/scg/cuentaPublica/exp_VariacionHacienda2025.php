<?php

session_start();

ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

set_time_limit(0);

//librerias
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../lib/array2Excel.php';
require_once '../../../../lib/scg/UrNombre.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/cuentaPublica/fnc_DameArrayVariacionHacienda2025.php';

$ur = new UnidadResponsable();
$ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
$ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
$ur->select();
$ejercicio = "";
$encabLin6 = "";
$ejercicioActual = $_SESSION[_S]['ejercicioActual'];
$ejercicioAnterior = $ejercicioActual - 1;

// NOTA: Cambio en los encabezados del reporte solicitado en el Ticket #200644
$encabLin1 = "Gobierno del Estado de Oaxaca";
$encabLin3 = "Estado de Variación en la Hacienda Pública";

$tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != "") ? $_POST['tipoReporte'] : '';
$MilPesos = (isset($_POST['MilPes']) != "" ? 1000 : 1);
$mesDesde = (isset($_POST['comboMesDesde']) != "" ? $_POST['comboMesDesde'] : -1);
$mesHasta = (isset($_POST['comboMesHasta']) != "" ? $_POST['comboMesHasta'] : -1);
$grupos = (isset($_POST['grupos']) && $_POST['grupos'] != "") ? $_POST['grupos'] : "";
$excluir = (isset($_POST['excluir']) && $_POST['excluir'] != "") ? $_POST['excluir'] : "";
$cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : "";
$ceros = (isset($_POST['ceros']) && $_POST['ceros'] != "") ? $_POST['ceros'] : "";
$incCta = isset($_POST['incCta']) ? $_POST['incCta'] : "";
$ue = isset($_POST['ue']) ? $_POST['ue'] : "";
$numUe = isset($_POST['numUe']) ? $_POST['numUe'] : "";

if ($ejercicio == "") {
    $ur = new UnidadResponsable();
    $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
    $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
    $ur->select();
}

if ($_SESSION[_S]['nvj_id'] > 2) {
    switch ($tipoReporte) {
        case 2: // Todas las URs de la carga de trabajo
            $encabLin2 = "Dirección de Contabilidad Gubernamental";
            break;
        case 3: // Determinar qué incluir y qué excluir
            $encabLin2 = "Dirección de Contabilidad Gubernamental";
            $encabLin6 = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
            break;
        default:
            if ($ejercicio == "") {
                $encabLin2 = $ur->getClave() . " - " . UrNombre::toLowerCase($ur->getNombre());
            } else {
                $encabLin2 = $ur['gpo_id'] . $ur['ur_id'] . " - " . UrNombre::toLowerCase($ur['ur_nombre']);
            }
            break;
    }
} else {
    if ($ejercicio == "") {
        $encabLin2 = "UR: " . $ur->getClave() . " - " . $ur->getNombre();
    } else {
        $encabLin2 = "UR: " . $ur['gpo_id'] . $ur['ur_id'] . " - " . $ur['ur_nombre'];
    }
}

$texto2 = '';
if ($ue != '' && $numUe > 1) {
    $ejecutora = new AccesoDatos();
    $ejecutora->conectar();
    $ejecutora->setSql('
        SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $ur->getGpo_id() . ' AND ur_id = ' . $ur->getUr_id() . ' AND ue_id = ' . $ue . ';
    ');
    $ejecutora->consultar();
    $ejecutora->desconectar();
    $ue_id = $ejecutora->extraer();
    $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
}

$encabLin7 = '';
if ($ue != '') {
    $encabLin7 = $texto2;
}

$mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != "") ? $_POST['comboMesDesde'] : '';

$sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesDesde . ';';
$con = new AccesoDatos();
$con->conectar();
$con->setSql($sqlMes);
$con->consultar();
$con->desconectar();
$rowMes = $con->extraer();

$mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != "") ? $_POST['comboMesHasta'] : '';

$sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesHasta . ';';
$con2 = new AccesoDatos();
$con2->conectar();
$con2->setSql($sqlMes2);
$con2->consultar();
$con2->desconectar();
$rowMes2 = $con2->extraer();

if ($_SESSION[_S]['reportes'] == 'C93') {
    if ($mesDesde == 0 and $mesHasta == 0) {
        $encabLin4 = 'Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . $ejercicioActual;
    } else if ($mesHasta == $mesDesde) {
        $encabLin4 = 'Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $ejercicioActual;
    } else {
        if ($mesDesde == 0) {
            $encabLin4 = 'Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . $rowMes2['msc_nombre'] . ' de ' . $ejercicioActual;
        } else {
            $encabLin4 = 'Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . $rowMes2['msc_nombre'] . ' de ' . $ejercicioActual;
        }
    }
} else {
    if ($mesDesde == 0 and $mesHasta == 0) {
        $encabLin4 = 'Del mes de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
    } else if ($mesHasta == $mesDesde) {
        if ($mesDesde == 13) {
            $encabLin4 = 'Al cierre de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
        } else {
            $encabLin4 = 'Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
        }
    } else {
        if ($mesDesde == 0) {
            if ($mesDesde == 0 and $mesHasta == 13) {
                $encabLin4 = 'Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_nombre'] . ' de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
            } else {
                $encabLin4 = 'Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . $rowMes2['msc_nombre'] . ' de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
            }
        } else {
            $encabLin4 = 'Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . $rowMes2['msc_nombre'] . ' de ' . $ejercicioActual . ' y ' . $ejercicioAnterior;
        }
    }
}

if (isset($_POST['MilPes'])) {
    $encabLin5 = '(Cifras en Miles de Pesos)';
} else {
    $encabLin5 = '(Cifras en Pesos)';
}




$datos = get_machote_estado_variacion();

rellena_variacion_hacienda( $datos, $mesDesde, $mesHasta, $tipoReporte, $MilPesos, $grupos, $excluir, $ue, $incCta, $ceros );

$estado = array();

foreach( $datos as $indice=>$valor ){

    $concepto = "";

    $es_linea_separadora = false;
    if ( trim( $valor["niv1"] == "" ) && trim( $valor["niv2"] == "" ) ) {
        $es_linea_separadora = true;
        $concepto = "";
        $col1 = $col2 = $col3 = $col4 = $col5 = "";
    }

    // Por si piden en miles de pesos
    // SIEMPRE Y CUANDO NO SEA UNA LÍNEA SEPARADORA
    if ( !$es_linea_separadora ) {

        // Usamos el chr(160) porque en UTF8 se "ve" como espacio pero no es un espacio, y así
        // el FPDF no lo quita (quita todos los espacios al principio de la cadena)
        if ( $valor["niv1"] != "" ) {
            $concepto = utf8Decode($valor["niv1"]);
        } else if ( $valor["niv2"] != "" ) {
            $concepto = str_repeat( chr(160), 10 ) . utf8Decode($valor["niv2"]);
        }

    }

    array_push( $estado, array( 
        "concepto" => mb_convert_encoding($concepto, 'UTF-8', 'ISO-8859-1'),
        "col1" => $valor["col1"],
        "col2" => $valor["col2"],
        "col3" => $valor["col3"],
        "col4" => $valor["col4"],
        "col5" => $valor["col5"]
        )
    );

}

$anchoColumnas = array(  );
$nombreColumnas = array(  );
$incluirColumnas = array(  );
$tipoColumnas = array(  );

array_push( $anchoColumnas, 80 );
array_push( $nombreColumnas, "CONCEPTO" );
array_push( $incluirColumnas, "concepto" );
array_push( $tipoColumnas, "c" );

array_push( $anchoColumnas, 20 );
array_push( $nombreColumnas, 'Hacienda Pública/ Patrimonio Contribuído' );
array_push( $incluirColumnas, "col1" );
array_push( $tipoColumnas, "n" );

array_push( $anchoColumnas, 20 );
array_push( $nombreColumnas, 'Hacienda Pública/ Patrimonio Generado Ejercicios Anteriores' );
array_push( $incluirColumnas, "col2" );
array_push( $tipoColumnas, "n" );

array_push( $anchoColumnas, 20 );
array_push( $nombreColumnas, 'Hacienda Pública/ Patrimonio Generado del Ejercicio' );
array_push( $incluirColumnas, "col3" );
array_push( $tipoColumnas, "n" );

array_push( $anchoColumnas, 20 );
array_push( $nombreColumnas, 'Exceso o Insuficiencia en la Actualización de la Hacienda Pública/ Patrimonio' );
array_push( $incluirColumnas, "col4" );
array_push( $tipoColumnas, "n" );

array_push( $anchoColumnas, 20 );
array_push( $nombreColumnas, 'Total' );
array_push( $incluirColumnas, "col5" );
array_push( $tipoColumnas, "n" );

$table = array( );
foreach( $incluirColumnas as $key=>$val ){
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

// Armamos los parametros opcionales
$encabezado = array( 'encabLin1'=>$encabLin1, 'encabLin2'=>$encabLin2, 'encabLin3'=>$encabLin3, 'encabLin4'=>$encabLin4, 'encabLin5'=>$encabLin5, 'encabLin6'=>$encabLin6, 'encabLin7'=>$encabLin7, 'ancho'=>$anchoColumnas );
$otrosParametros = array( 'comprimir'=>false, 'nombre'=> "Variacion_Hacienda_" . $mesDesde . "_" . $mesHasta );

$excel = new array2Excel();
$excel->generaExcel( $estado, $encabezado, true, $table, $otrosParametros, "SEFIP2025", "#,##0" );


// Fin del código
