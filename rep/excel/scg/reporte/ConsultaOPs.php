<?php
/**
 * ConsultaOPs
 *
 * Genera el archivo excel de las OPs pagadas y generadas
 * de acuerdo al mes seleccionado
 *
 * PHP version 5
 *
 * @category  Class
 * @package   Package
 * @author    Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * @copyright 2017 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 * @version   SVN:2017-02-10
 * @link      egresos.finanzasoaxaca.gob.mx/sinpres2017/
 */
session_start();
//Aumenta la menoria a 600M
ini_set('memory_limit', '600M');
set_time_limit(0);
require_once dirname(__FILE__) . "/../../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../lib/generaPhpspreadsheet.php";
$params = array();
$mesReporte = $_POST['mes_id'];
$secretaria = $_POST['nom_sec'];
$nombreMes = ' ';
$nivel = $_SESSION[_S]['nvj_id'];
switch ($mesReporte) {
    case '01':
        $nombreMes = 'ENERO';
        break;
    case '02':
        $nombreMes = 'FEBRERO';
        break;
    case '03':
        $nombreMes = 'MARZO';
        break;
    case '04':
        $nombreMes = 'ABRIL';
        break;
    case '05':
        $nombreMes = 'MAYO';
        break;
    case '06':
        $nombreMes = 'JUNIO';
        break;
    case '07':
        $nombreMes = 'JULIO';
        break;
    case '08':
        $nombreMes = 'AGOSTO';
        break;
    case '09':
        $nombreMes = 'SEPTIEMBRE';
        break;
    case '10':
        $nombreMes = 'OCTUBRE';
        break;
    case '11':
        $nombreMes = 'NOVIEMBRE';
        break;
    case '12':
        $nombreMes = 'DICIEMBRE';
        break;
}
// Definimos las líneas de encabezado que llevará el reporte
$encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
$encabLin2 = $secretaria;
$encabLin3 = APP_EJERCICIO;
$encabLin5 = "OPS GENERADAS Y PAGADAS EN " . $nombreMes;
// Obtenemos EN UN ARRAY los datos a imprimir
$datos = catalogo($mesReporte);
// Definimos los arrays que usaremos para configurar el reporte
$anchoColumnas = array();
$nombreColumnas = array();
$incluirColumnas = array();
$tipoColumnas = array();

// Llenamos los arrays con los parámetros del reporte
array_push($anchoColumnas, 10);
array_push($nombreColumnas, "GPO_ID");
array_push($incluirColumnas, "gpo_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "UR_ID");
array_push($incluirColumnas, "ur_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "CLC_ID");
array_push($incluirColumnas, "clc_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 10);
array_push($nombreColumnas, "UE_ID");
array_push($incluirColumnas, "ue_id");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 15);
array_push($nombreColumnas, "CLC_CERECO");
array_push($incluirColumnas, "clc_cereCo");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "CLAVE_FINANCIAMIENTO");
array_push($incluirColumnas, "clave_financiamiento");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 50);
array_push($nombreColumnas, "ESTADO_CLC");
array_push($incluirColumnas, "estado_clc");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 70);
array_push($nombreColumnas, "CLC_CONCEPTO");
array_push($incluirColumnas, "clc_concepto");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 20);
array_push($nombreColumnas, "CLC_PERCEPCION");
array_push($incluirColumnas, "clc_percepcion");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 50);
array_push($nombreColumnas, "CLC_BENEFICIARIO");
array_push($incluirColumnas, "clc_beneficiario");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "REFERENCIA_PAGO");
array_push($incluirColumnas, "referenciaPago");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "FECHA_PAGO");
array_push($incluirColumnas, "fechaPago");
array_push($tipoColumnas, "f");

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "CUENTA_CONTABLE");
array_push($incluirColumnas, "cuenta_contable");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 30);
array_push($nombreColumnas, "CUENTA_NOMBRE");
array_push($incluirColumnas, "cta_nombre");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "NCC_TIPOMOVIMIENTO");
array_push($incluirColumnas, "ncc_tipoMovimiento");
array_push($tipoColumnas, "c");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "NCC_PERCEPCION");
array_push($incluirColumnas, "ncc_percepcion");
array_push($tipoColumnas, "n");

array_push($anchoColumnas, 25);
array_push($nombreColumnas, "NCC_RETENCION");
array_push($incluirColumnas, "ncc_retencion");
array_push($tipoColumnas, "n");

// De los datos obtenidos en un array, ahora los ponemos para el excel
$table = array();
foreach ($incluirColumnas as $key => $val) {
    $table['nombre'][$val] = $nombreColumnas[$key];
    $table['ancho'][$val] = $anchoColumnas[$key];
    $table['campo'][$val] = $key;
    $table['tipo'][$val] = $tipoColumnas[$key];
}

// Armamos los parametros opcionales
$encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin5' => $encabLin5, 'ancho' => $anchoColumnas, 'alineacionenc' => 'I');
$otrosParametros = array('comprimir' => false, 'nombre' => $encabLin5, 'alineacionenc' => 'I');

$otrosParametros = array('comprimir' => false, 'nombre' => $encabLin5);

// Generamos el excel
$excel = new generaPhpspreadsheet();
$excel->generaExcel($datos, $encabezado, $table, $otrosParametros);

/**
 *Obtenemos los datos a exportar, y los colocamos en un array
 *
 *@param mes $mes numero que corresponde al mes seleccionado
 *
 *@return array  de datos
 */
function catalogo($mes)
{
    $sql = "
        SELECT c.gpo_id, c.ur_id, c.clc_id, c.ue_id, clc_cereCo,
            CONCAT( fte_id, sft_id, ori_id, sor_id, cff_id, cfi_id, cfi_anio ) AS claveFinanciamiento,
            ecl_nombre AS estadoClc, clc_concepto, clc_percepcion, clc_beneficiario,
            -- CONCAT( dle.loe_dia, dle.loe_mes, dle.loe_anio, dle.loe_cereCo, dle.loe_id ) AS loteEgresos,
            IFNULL(min.bdm_referencia,'') referenciaPago, DATE(bdm_fechaAutorizado) AS fechaPago,
            CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id,
                cta_ue_id, cgc_id, cta_id ) AS cuentaContable,cta_nombre,
                ncc_tipoMovimiento, ncc_percepcion, ncc_retencion
        FROM scp_Clc c
            INNER JOIN scp_ClcAuxiliar USING( gpo_id, ur_id, clc_id )
            --  INNER JOIN scp_DetalleLoteEgresos dle USING( gpo_id, ur_id, clc_id )
            INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
            INNER JOIN cat_EstadoClc ec ON( c.ecl_id = ec.ecl_id )
            INNER JOIN scp_Contable USING( gpo_id, ur_id, clc_id )
            INNER JOIN cg_PlanCuentas USING (cta_gpo_id, cta_ur_id,cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
            INNER JOIN " . BD_TESORERIA . ".lot_LoteClc ltclc USING( gpo_id, ur_id, clc_id )
            INNER JOIN " . BD_TESORERIA . ".lot_Lote lt on lt.fpa_id= ltclc.fpa_id and lt.msc_id=ltclc.msc_id
                AND lt.lot_ejercicio=ltclc.lot_ejercicio
                and lt.lot_id=ltclc.lot_id
            LEFT JOIN " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_" . APP_EJERCICIO . " min USING( gpo_id, ur_id, clc_id,afe_id,bdo_id )
        WHERE c.gpo_id = :gpo_id AND c.ur_id = :ur_id
            AND c.ue_id = :ue_id
            AND c.clc_tipoCereCo = :tipoCereCo
            AND lot_ejr= :lot_ejr
            AND MONTH( clc_fechaEnvio ) = :mes ;
    ";
    $params[':gpo_id'] = 1;
    $params[':ur_id'] = 16;
    $params[':ue_id'] = 6;
    $params[':tipoCereCo'] = 2;
    $params[':lot_ejr'] = APP_EJERCICIO;
    $params[':mes'] = $mes;
    //variable de conexion para los saldos iniciales
    $catalogo = new AccesoDatos();
    $catalogo->conectar();
    $catalogo->setSql($sql);
    $catalogo->consultar($params);
    $catalogo->desconectar();

    $datos = array();

    while ($rowCatalogo = $catalogo->extraer()) {
        // imprimir todos los datos en el excel
        $datos[] = array(
            'gpo_id' => $rowCatalogo['gpo_id'],
            'ur_id' => $rowCatalogo['ur_id'],
            'clc_id' => $rowCatalogo['clc_id'],
            'ue_id' => $rowCatalogo['ue_id'],
            'clc_cereCo' => $rowCatalogo['clc_cereCo'],
            'clave_financiamiento' => $rowCatalogo['claveFinanciamiento'],
            'estado_clc' => $rowCatalogo['estadoClc'],
            'clc_concepto' => $rowCatalogo['clc_concepto'],
            'clc_percepcion' => $rowCatalogo['clc_percepcion'],
            'clc_beneficiario' => $rowCatalogo['clc_beneficiario'],
            'referenciaPago' => $rowCatalogo['referenciaPago'],
            'fechaPago' => $rowCatalogo['fechaPago'],
            'cuenta_contable' => $rowCatalogo['cuentaContable'] . " ",
            'cta_nombre' => $rowCatalogo['cta_nombre'],
            'ncc_tipoMovimiento' => $rowCatalogo['ncc_tipoMovimiento'],
            'ncc_percepcion' => $rowCatalogo['ncc_percepcion'],
            'ncc_retencion' => $rowCatalogo['ncc_retencion'],
        );
    }

    return $datos;

}
