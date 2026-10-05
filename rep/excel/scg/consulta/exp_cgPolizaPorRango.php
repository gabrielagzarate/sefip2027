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
require_once dirname(__FILE__) . '/../../../lib/array2ExcelRango.php';
require_once dirname(__FILE__) . '/../../../../lib/calculaPeso.php';

$sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
$men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
$opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');
if (isset($_POST['check2025']) && $_POST['check2025'] == 1) {
    $ejercicio = "2025";
} elseif (isset($_POST['check2024']) && $_POST['check2024'] == 1) {
    $ejercicio = "2024";
} elseif (isset($_POST['check2023']) && $_POST['check2023'] == 1) {
    $ejercicio = "2023";
} elseif (isset($_POST['check2022']) && $_POST['check2022'] == 1) {
    $ejercicio = "2022";
} elseif (isset($_POST['check2021']) && $_POST['check2021'] == 1) {
    $ejercicio = "2021";
} elseif (isset($_POST['check2020']) && $_POST['check2020'] == 1) {
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
    $ejercicio = "2026";
}
$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];

$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "actual";   // Si no lo indican, es una sola póliza

$paraRevision = isset($_POST['rev']) ? $_POST['rev'] : 0;
$modoPoliza = isset($_POST['modoPoliza']) ? $_POST['modoPoliza'] : "1";   // Si no lo indican, se imprime normal

function obtenerSqlParaDatos($tipo, $modoPoliza, $gpo_id, $ur_id, $paraRevision)
{
    //obtencion de datos
    $where = '';
    switch ($tipo) {
        // =================================================================================================================
        // UNA SOLA POLIZA
        // =================================================================================================================
        case "actual":

            $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "%") ? $_POST['ue']
                : "001";
            $pot_id = isset($_POST['pot']) ? $_POST['pot'] : "D";
            $prf_id = isset($_POST['prf']) ? $_POST['prf'] : "0";
            $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;
            $pma_id = isset($_POST['num']) ? $_POST['num'] : 1;
            // Marcos: 06/abr/2016
            // Para imprimir de meses diferentes, cuando filtran por vinculación
            $msc_id_act = isset($_POST['mscLinAct']) ? $_POST['mscLinAct'] : $msc_id;

            // Si el mes de la póliza actualmente seleccionada es diferente, tiene prioridad
            // sobre el mes del combo
            if ($msc_id != $msc_id_act) $msc_id = $msc_id_act;

            $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . ' AND m.ue_id=' . $ue_id . ' AND m.pot_id="' . $pot_id . '" AND ' .
                'm.prf_id="' . $prf_id . '" AND m.msc_id=' . $msc_id . ' AND m.pma_id = ' . $pma_id;

            break;
        // =================================================================================================================
        // EL RANGO DEL LIBRO DIARIO QUE ESTE ACTIVO AL MOMENTO DE PEDIR ESTA OPCION
        // =================================================================================================================
        case "sel":

            // Los obligatorios arman la consulta en el whrre
            $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;

            // Los opcionales o regresan la condición, o regresan la cadena vacía para que no afecten el where
            $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "" && trim($_POST['ue'])
                != "%") ? ' AND m.ue_id = "' . $_POST['ue'] . '" ' : '';
            $pma_id = (isset($_POST['num']) && trim($_POST['num']) != "") ? ' AND d.pma_id >= ' . $_POST['num'] . ' '
                : '';
            $usr = (isset($_POST['usr']) && trim($_POST['usr']) != "") ? ' AND ( m.usr_id = ' . $_POST['usr'] . ' OR  m.usr_idModificado = ' . $_POST['usr'] . ' ) '
                : '';
            $textoBuscar = (isset($_POST['tb']) && trim($_POST['tb']) != "") ? ' AND m.pma_concepto like "%' . $_POST['tb'] . '%" '
                : '';

            // Para la vinculación ( pdx_*_origen )
            $clc_v = trim(((isset($_POST['clc_v'])) ? $_POST['clc_v'] : ''));
            $afe_v = trim(((isset($_POST['afe_v'])) ? $_POST['afe_v'] : ''));
            $cmp_v = trim(((isset($_POST['cmp_v'])) ? $_POST['cmp_v'] : ''));
            $bdo_v = trim(((isset($_POST['bdo_v'])) ? $_POST['bdo_v'] : ''));

            // Para la vinculación ( pdx_*_vinculacion )
            $clc_o = trim(((isset($_POST['clc_o'])) ? $_POST['clc_o'] : ''));
            $afe_o = trim(((isset($_POST['afe_o'])) ? $_POST['afe_o'] : ''));
            $cmp_o = trim(((isset($_POST['cmp_o'])) ? $_POST['cmp_o'] : ''));
            $bdo_o = trim(((isset($_POST['bdo_o'])) ? $_POST['bdo_o'] : ''));

            // La referencia sólo es una
            $ref_v = trim(((isset($_POST['ref_v'])) ? $_POST['ref_v'] : ''));
            $andOr = trim(((isset($_POST['andOr'])) ? $_POST['andOr'] : '0'));
            $andOr = ($andOr == "0" ? "AND" : "OR");

            // Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
            // NO limitamos por tipo de póliza
            if (
                $clc_v != "" || $afe_v != "" || $cmp_v != "" || $bdo_v != "" || $clc_o
                != "" || $afe_o != "" || $cmp_o != "" || $bdo_o != "" || $ref_v != ""
            ) {
                $bolPidieronVinculacion = true;
            } else {
                $bolPidieronVinculacion = false;
            }

            // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
            // -------------------------------------------------------------------------------------------
            $where = "";
            $opcion = substr($_POST['pot'], 0, 2);
            $valor = substr($_POST['pot'], 3, 1);
            switch ($opcion) {
                case "tp":
                    // Si filtran por vinculacion presupuestal, no pelamos éste filtro
                    if ($bolPidieronVinculacion == false) {
                        $where .= " AND pot_id = '" . $valor . "' ";
                    }
                    break;
                case "mc":
                    $where = ' AND m.mmc_id = "' . $valor . '" ';
                    break;
                case "st":
                    $where = ' AND m.epl_id = "' . $valor . '" ';
                    break;
            }

            if ($bolPidieronVinculacion == true) $where .= " AND (";
            if ($clc_v != "")
                $where .= " $andOr d.pdx_clc_id_vinculacion = '$clc_v' ";
            if ($afe_v != "")
                $where .= " $andOr d.pdx_afe_id_vinculacion = '$afe_v' ";
            if ($cmp_v != "")
                $where .= " $andOr d.pdx_cmp_id_vinculacion in ( $cmp_v ) ";
            if ($bdo_v != "")
                $where .= " $andOr d.pdx_bdo_id_vinculacion = '$bdo_v' ";

            if ($clc_o != "")
                $where .= " $andOr d.pdx_clc_id_origen = '$clc_o' ";
            if ($afe_o != "")
                $where .= " $andOr d.pdx_afe_id_origen = '$afe_o' ";
            if ($cmp_o != "")
                $where .= " $andOr d.pdx_cmp_id_origen IN ( $cmp_o )";
            if ($bdo_o != "")
                $where .= " $andOr d.pdx_bdo_id_origen = '$bdo_o' ";

            // El campo de REFERENCIA, si lo escriben tal y como se imprime... es tdf_id-tdf_referencia
            // así que lo separamos
            if ($ref_v != "") {
                $arrDocto = explode("-", $ref_v);
                // DEBE ser un array de 2 posiciones.   En caso contrario dejamos vacío
                if (count($arrDocto) == 2) {
                    $where .= " $andOr d.tdf_id = '" . $arrDocto[0] . "' $andOr d.pdx_referencia = '" . $arrDocto[1] . "' ";
                }
            }

            if ($bolPidieronVinculacion == true) $where .= " )";

            // Aunque hasta abril del 2016 el mes era campo fijo, si pedimos filtros por
            // vinculación presupuestal, no pelaremos el mes
            if ($bolPidieronVinculacion == false) {
                $where .= ' AND msc_id = "' . $msc_id . '" ';
            }

            // Para corregir un error en la lógica del armado
            $where = str_replace("AND ( AND ", "AND ( ", $where);
            $where = str_replace("AND ( OR ", "AND ( ", $where);

            $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . $ue_id . $pma_id .
                $where . $usr . $textoBuscar;

            break;

        // =================================================================================================================
        // UN RANGO ARBITRARIO DE NUMEROS DE POLIZA
        // =================================================================================================================
        case "rango":
            $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "" && trim($_POST['ue'])
                != "%") ? ' AND m.ue_id = "' . $_POST['ue'] . '" ' : '';
            $prf_id = isset($_POST['prf']) ? $_POST['prf'] : "0";
            $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;

            // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
            // -------------------------------------------------------------------------------------------
            $opcion = substr($_POST['pot'], 0, 2);
            $valor = substr($_POST['pot'], 3, 1);
            switch ($opcion) {
                case "tp":
                    $pot_id = ' AND m.pot_id = "' . $valor . '" ';
                    break;
                case "mc":
                    // Si es de pagos a terceros, hay una validación extra
                    if ($valor == "O") {
                        $where .= " AND ( mmc_id = 'P') ";
                        // Y por lo tanto el Pagado Pagado normal también
                    } elseif ($valor == "P") {
                        $where .= " AND ( mmc_id = '" . $valor . "' ) ";
                    } else {
                        $where .= " AND mmc_id = '" . $valor . "' ";
                    }
                    break;
                case "st":
                    $pot_id = ' AND m.epl_id = "' . $valor . '" ';
                    break;
            }


            // Los números de póliza deben venir separados por comas
            $pma_id = isset($_POST['num']) ? $_POST['num'] : 1;

            $arrRangos = explode(',', $pma_id);

            $whereNumeros = "";

            if (count($arrRangos) > 0) {
                // Recorremos todas las combinaciones que encontremos
                foreach ($arrRangos as $v) {
                    $v = trim($v);
                    if ($v != "") {
                        // Si es un rango, está separado por un guion
                        if (is_numeric(strpos($v, "-"))) {
                            // Volvemos a dividir ya que pueden estar especificando un rango, pero ahora separamos por -
                            $unRango = explode('-', $v);
                            // Como resultado sólo puede ser un elemento de 2 arrays
                            // Si no da dos, no pelamos este valor
                            if (count($unRango) == 2) {
                                // Sólo totamos los enteros
                                if (is_numeric(trim($unRango[0])) && is_numeric(trim($unRango[1]))) {
                                    $whereNumeros .= ' ( m.pma_id BETWEEN ' . trim($unRango[0]) . ' AND ' . trim($unRango[1]) . ' ) OR ';
                                }
                            }
                            // si es un número único de póliza
                        } else {
                            // Sólo totamos los enteros
                            if (is_numeric($v)) {
                                $whereNumeros .= ' m.pma_id = ' . $v . ' OR ';
                            }
                        }
                    }
                }

                // Le quitamos el último OR
                if (substr($whereNumeros, -4, 4) == ' OR ') {
                    $whereNumeros = substr(
                        $whereNumeros,
                        0,
                        strlen($whereNumeros) - 4
                    );
                }

                // Si llegamos aquí sin nada definido, tronamos el PDF para que aparezca una ventana vacía
                if (trim($whereNumeros) == "") {
                    echo "Los valores de rango de pólizas no fueron especificados correctamente.";
                    return false;
                }

                $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . $ue_id . $pot_id . ' AND ' .
                    'm.prf_id="' . $prf_id . '" AND m.msc_id=' . $msc_id . ' AND ( ' . $whereNumeros . ' )';

                // Si el rango no es válido ... un where que siempre regrese vacío
            } else {
                $where = 'd.gpo_id = 9 and d.ur_id = 99 and d.ue_id = 999';
            }
            break;
    }

    //Fin tipo de reporte
    $var_max = 'MAX';
    if ($modoPoliza == 1) {
        $var_max = '';
    }

    switch ($modoPoliza) {
        // Impresión a Nivel de Mayor
        case '3':
            $sql = 'SELECT m.ue_id, m.pot_id, m.prf_id, m.msc_id, m.pma_id, sCs.cgsc_cuenta AS cta,
            MAX(sCs.cgsc_nombre), apg_id, "" as pdx_afe_id_origen,
            sCs.cgsc_cuenta AS cuenta,
            SUM( pdx_debe ) AS pdx_debe, SUM( pdx_haber ) AS pdx_haber, "" AS pdx_vinculacion, MAX(d.adf_id), MAX(d.pdx_id), m.epl_id, d.nat_id, d.pdx_referencia, MAX(d.tdf_id),
            MAX(sCs.cgsc_nombre) AS nombreCuenta,
            MAX(d.pdx_gpo_id_origen), MAX(d.pdx_ur_id_origen), MAX(pdx_clc_cereCo_vinculacion)
            FROM cg_PolizaMaestro m
            JOIN cg_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            INNER JOIN cat_CgSubcuenta sCs USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            WHERE ' . $where . '
            GROUP BY
                ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia
            ORDER BY
                ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia;';

            break;

        // En la impresión normal y sumarizada, sólo cambia el GROUP BY
        default:
            $sql = 'SELECT  CONCAT(m.pot_id,"-",m.prf_id, m.pma_id) as poliza, m.pma_id,m.ue_id, m.pot_id, m.prf_id, m.msc_id, m.pma_id, ' . $var_max . '(sCs.cgsc_cuenta) AS cta,
                    ' . $var_max . '(sCs.cgsc_nombre), apg_id, ' . (($modoPoliza == "4" || $modoPoliza
                == "2") ? ' "" AS pdx_afe_id_origen' : '' . $var_max . '(pdx_afe_id_origen) ') . ', ' .
                ($modoPoliza == "6" ?
                    "CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '.', cgc_id ) AS cuenta, "
                    :
                    "c.cta_cuenta AS cuenta, "
                ) . '
                    ' . ($modoPoliza == "1" ? ' pdx_debe' : ' SUM( pdx_debe  ) AS pdx_debe') . ',
                    ' . ($modoPoliza == "1" ? ' pdx_haber' : ' SUM( pdx_haber ) AS pdx_haber') . ',
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_concepto) AS pdx_concepto,
                    "" AS pdx_vinculacion,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.adf_id) AS adf_id,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pdx_id) AS pdx_id,
                    m.epl_id, d.nat_id,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.rfc_id) AS rfc_id,
                    ' . (($modoPoliza == "2") ? ' "" AS pdx_referencia' : (($modoPoliza == 6) ? 'MAX' : '') . '(d.pdx_referencia) AS pdx_referencia ') . ',
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pdx_fechaReferencia) AS pdx_fechaReferencia,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.tdf_id) AS tdf_id,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(CASE sCs.ecu_id WHEN "2" THEN
                    CASE cgc_id WHEN 1 THEN c.cta_nombre ELSE t.cgc_nombre END
                    ELSE c.cta_nombre END ) AS nombreCuenta,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pdx_gpo_id_origen) AS pdx_gpo_id_origen,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pdx_ur_id_origen) AS pdx_ur_id_origen,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_clc_cereCo_vinculacion) AS pdx_clc_cereCo_vinculacion,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(ep.epg_clave) AS epg_clave,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pg_id) AS pg_id ,' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(d.pe_id) AS pe_id,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(ec.ecf_clave) AS ecf_clave,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(CONCAT(pdx_gpo_id_origen,pdx_ur_id_origen)) as grupourOrigen,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(CONCAT(pdx_gpo_id_vinculacion,pdx_ur_id_vinculacion)) as grupour,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_clc_id_vinculacion) as clc,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_afe_id_vinculacion) as afe,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_cmp_id_vinculacion) as com,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_bdo_id_vinculacion) as bdo,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(CONCAT(pdx_tipoPresupuestal,"-",pdx_mod_id_vinculacion)) as tipomod,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_rct_id_vinculacion) as rec,
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? 'MAX' : '') . '(pdx_ejercicio) as ejercicio
                    ' . (($modoPoliza == 6) ? ',MAX(pdx_afe_id_origen) as pdx_afe_id_origen' : '') . '
                    ' . (($modoPoliza == 2 || $modoPoliza == 4 || $modoPoliza == 5 || $modoPoliza == 6) ? ',MAX(pdx_bdo_id_origen) as pdx_bdo_id_origen' : '') . '
                    
                    FROM cg_PolizaMaestro m
                    LEFT JOIN cg_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
                    LEFT JOIN enc_Programatico ep USING(epg_id,gpo_id,ur_id)
                    LEFT JOIN enc_ClaveFinanciamiento ec USING(ecf_id)
                    LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    LEFT JOIN cat_CgSubcuenta sCs USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                    LEFT JOIN cg_CuentaCuarto t USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
                    LEFT JOIN cg_PlanCuentas c USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
                    WHERE ' . $where;


            // Marcos: 23/feb/2016
            // que dice mi mamá que siempre no:   este es el order original, lo quitamos a sugerencia de
            // Israel para dejar el segundo... pero salían descuadradas las pólizas por ranto
            // así que mejor... lo dejamos como estada... independientemente de índices y todo lo demás :D
            // Registro: no incluimos documentos fuente, y sumarizamos a nivel de registro de la cuenta
            if ($modoPoliza == "2" || $modoPoliza == "6") {
                $sql .= ' GROUP BY ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta ';
            } else {
                if ($modoPoliza <> "1") {
                    $sql .= ' GROUP BY ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia' . ($modoPoliza
                        == "4" ? ' ' : ', pdx_afe_id_origen ');
                }
            }

            // Marcos, se agrega gpo y ur origen
            // 04/abr/2017
            $sql .= ' ORDER BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_gpo_id_origen, pdx_ur_id_origen, pdx_afe_id_origen, pdx_bdo_id_origen, pdx_id;';

            break;
    }
    return $sql;
}

function obtenerDatosEncabezado($row, $gpo_id, $ur_id, $numPolizas)
{
    /*     * **** VARIABLES  DE CAMBIOS ****** */
    $poliza = "";
    $datosEncabezado = array();
    $consul = 'SELECT ur.gpo_id, ur.ur_id, ur.ur_nombre, pm.pot_id, pm.prf_id, pm.pma_id, pm.pma_fecha, pm.pma_debe, pm.pma_haber, pm.pma_concepto,
                epl_id, msc_id, mmc_id, mmc_nombre, pm.usr_id, usr_usuario, tmc_id
                    FROM cg_PolizaMaestro pm
                    JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
                    INNER JOIN cat_MomentoContable USING( mmc_id )
                    JOIN cat_Usuario u on u.usr_id=pm.usr_id
                    WHERE pm.gpo_id = ' . $gpo_id . ' 
                    AND pm.ur_id = ' . $ur_id . ' 
                    AND pm.ue_id = ' . $row['ue_id'] . '
                    AND pm.msc_id = ' . $row['msc_id'] . ' 
                    AND pm.pot_id = "' . $row['pot_id'] . '"
                    AND pm.prf_id = "' . $row['prf_id'] . '" 
                    AND pm.pma_id = ' . $row['pma_id'];

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($consul);
    $con->consultar();

    $datosEncabezado = $con->extraer();
    $con->desconectar();

    $datosextra = "";
    if (
        $datosEncabezado['mmc_id'] == "E" || $datosEncabezado['mmc_id'] == "F"
        || $datosEncabezado['mmc_id'] == "T" || $datosEncabezado['mmc_id'] == "N"
    ) {
        $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT(  pdx_clc_id_origen ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_clc_id_vinculacion) ) AS clcVin, ';
    } else
        if ($datosEncabezado['mmc_id'] == "R") {
        $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_clc_id_origen ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_rct_id_vinculacion) ) AS clcVin, ';
    } else

        if ($datosEncabezado['mmc_id'] == "K") {
        $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_origen, pdx_ur_id_origen, "
                    -", pdx_clc_id_origen ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, "
                    -" , pdx_rct_id_vinculacion) ) AS clcVin, ';
    } else
        if ($datosEncabezado['mmc_id'] == "G") {
        $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_origen, pdx_ur_id_origen, "
                    - ",  pdx_clc_id_origen ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, 
                                "-" ,pdx_clc_cereCo_vinculacion) ) AS clcVin, ';
    } else

        if ($datosEncabezado['mmc_id'] == "B") {
        $datosextra = '   max(pdx_gpo_id_vinculacion) AS clcOri,
                              max(pdx_clc_cereCo_vinculacion) AS clcVin, ';
    } else


        if ($datosEncabezado['mmc_id'] == "D" || $datosEncabezado['mmc_id'] == "P") {
        $datosextra = '  GROUP_CONCAT( DISTINCT CONCAT(  pdx_bdo_id_origen ) ) AS clcOri,
                                "" AS clcVin, ';
    } else
        if ($datosEncabezado['mmc_id'] == "C") {
        $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT(  pdx_cmp_id_origen ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_cmp_id_vinculacion) ) AS clcVin, ';
    } else
        if ($datosEncabezado['mmc_id'] == "M") {
        $datosextra = '  GROUP_CONCAT( DISTINCT CONCAT(  pdx_tipoPresupuestal ) ) AS clcOri,
                                GROUP_CONCAT( DISTINCT CONCAT( pdx_mod_id_vinculacion) ) AS clcVin, ';
    } else {

        $datosextra = '  "" AS clcOri,
                                "" AS clcVin, ';
    }


    $paraRevision = isset($_POST['rev']) ? $_POST['rev'] : 0;
    if ($paraRevision) {
        $consul2 = 'SELECT pdx_ejercicio AS ejercicio,
                            ' . $datosextra . '
                        cf.ecf_clave AS cf
                        FROM cg_PolizaDetalleAuxiliar
                        LEFT JOIN enc_ClaveFinanciamiento cf USING( ecf_id )
                        WHERE gpo_id = ' . $gpo_id . ' 
                        AND ur_id = ' . $ur_id . ' 
                        AND ue_id = ' . $row['ue_id'] . ' 
                        AND msc_id = ' . $row['msc_id'] . ' 
                        AND pot_id = "' . $row['pot_id'] . '" 
                        AND prf_id =  "' . $row['prf_id'] . '" 
                        AND pma_id = ' . $row['pma_id'] . '
                        GROUP BY pdx_ejercicio, pdx_gpo_id_origen, pdx_ur_id_origen, pdx_clc_id_origen,
                        pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion,cf.ecf_clave
                ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($consul2);
        $con2->consultar();
        if ($con2->getNumeroRegistros() > 0) {
            $datosEncabezado['Extra'] = $con2->extraer();
        } else {
            $datosEncabezado['Extra'] = [];
        }
         $con2->desconectar();
    }else{
         $datosEncabezado['Extra'] = [];
    }

    return $datosEncabezado;
}

function armarColumnasTabla($anchoColumnas)
{
    // Valores para el archivo de exportación
    $nombreColumnas = array();
    $incluirColumnas = array();
    $tipoColumnas = array();

    //columnas de la tabla

    //Cuenta
    array_push($nombreColumnas, "Cuenta");
    array_push($incluirColumnas, "cuenta");
    array_push($tipoColumnas, "c");
    //Descripcion
    array_push($nombreColumnas, "Descripción");
    array_push($incluirColumnas, "nombreCuenta");
    array_push($tipoColumnas, "c");
    //Debe
    array_push($nombreColumnas, "Debe");
    array_push($incluirColumnas, "pdx_debe");
    array_push($tipoColumnas, "n");
    //Haber
    array_push($nombreColumnas, "Haber");
    array_push($incluirColumnas, "pdx_haber");
    array_push($tipoColumnas, "n");
    //Concepto
    array_push($nombreColumnas, "Concepto");
    array_push($incluirColumnas, "pdx_concepto");
    array_push($tipoColumnas, "c");
    //ADF
    array_push($nombreColumnas, "ADF");
    array_push($incluirColumnas, "adf_id");
    array_push($tipoColumnas, "c");
    //TDF
    array_push($nombreColumnas, "TDF");
    array_push($incluirColumnas, "tdf_id");
    array_push($tipoColumnas, "c");
    //RFC
    array_push($nombreColumnas, "RFC");
    array_push($incluirColumnas, "rfc_id");
    array_push($tipoColumnas, "c");
    //Referencia
    array_push($nombreColumnas, "Referencia");
    array_push($incluirColumnas, "pdx_referencia");
    array_push($tipoColumnas, "c");
    //FechaRef
    array_push($nombreColumnas, "FechaRef");
    array_push($incluirColumnas, "pdx_fechaReferencia");
    array_push($tipoColumnas, "f");
    //EncProgr
    array_push($nombreColumnas, "EncProg");
    array_push($incluirColumnas, "epg_clave");
    array_push($tipoColumnas, "c");
    //Pg
    array_push($nombreColumnas, "Pg");
    array_push($incluirColumnas, "pg_id");
    array_push($tipoColumnas, "c");
    //Pe
    array_push($nombreColumnas, "Pe");
    array_push($incluirColumnas, "pe_id");
    array_push($tipoColumnas, "c");
    //CveFin
    array_push($nombreColumnas, "CveFin");
    array_push($incluirColumnas, "ecf_clave");
    array_push($tipoColumnas, "c");
    //Grupo+urORIGEN
    array_push($nombreColumnas, "UR-Origen");
    array_push($incluirColumnas, "grupourOrigen");
    array_push($tipoColumnas, "c");
    //Grupo+ur
    array_push($nombreColumnas, "UR");
    array_push($incluirColumnas, "grupour");
    array_push($tipoColumnas, "c");
    //Clc
    array_push($nombreColumnas, "Clc");
    array_push($incluirColumnas, "clc");
    array_push($tipoColumnas, "c");
    //Afe
    array_push($nombreColumnas, "Afe");
    array_push($incluirColumnas, "afe");
    array_push($tipoColumnas, "c");
    //Com
    array_push($nombreColumnas, "Com");
    array_push($incluirColumnas, "com");
    array_push($tipoColumnas, "c");
    //Bdo
    array_push($nombreColumnas, "BDO");
    array_push($incluirColumnas, "bdo");
    array_push($tipoColumnas, "c");
    //Tipo+mod
    array_push($nombreColumnas, "Tipo+Mod");
    array_push($incluirColumnas, "tipomod");
    array_push($tipoColumnas, "c");
    //Rec
    array_push($nombreColumnas, "Rec");
    array_push($incluirColumnas, "rec");
    array_push($tipoColumnas, "c");
    //Ejericio
    array_push($nombreColumnas, "Ejercicio");
    array_push($incluirColumnas, "ejercicio");
    array_push($tipoColumnas, "c");
    $table = array();
    foreach ($incluirColumnas as $key => $val) {
        $table['nombre'][$val] = $nombreColumnas[$key];
        $table['ancho'][$val] = $anchoColumnas[$key];
        $table['campo'][$val] = $key;
        $table['tipo'][$val] = $tipoColumnas[$key];
    }
    return $table;
}

function armarArregloDeDatos($conexion)
{
    $datos = [];

    while ($row = $conexion->extraer()) {
        $key = $row['poliza'];
        if (!isset($datos[$key])) {
            $datos[$key] = [];
        }
        $datos[$key][] = [
            'id' => $row['pma_id'],
            'cuenta' => $row['cuenta'],
            'nombreCuenta' => $row['nombreCuenta'],
            'pdx_debe' => $row['pdx_debe'],
            'pdx_haber' => $row['pdx_haber'],
            'pdx_concepto' => $row['pdx_concepto'],
            'adf_id' => $row['adf_id'],
            'tdf_id' => $row['tdf_id'],
            'rfc_id' => $row['rfc_id'],
            'pdx_referencia' => $row['pdx_referencia'],
            'pdx_fechaReferencia' => $row['pdx_fechaReferencia'],
            'epg_clave' => $row['epg_clave'],
            'pg_id' => $row['pg_id'],
            'pe_id' => $row['pe_id'],
            'ecf_clave' => $row['ecf_clave'],
            'grupourOrigen' => $row['grupourOrigen'],
            'grupour' => $row['grupour'],
            'clc' => $row['clc'],
            'afe' => $row['afe'],
            'com' => $row['com'],
            'bdo' => $row['bdo'],
            'tipomod' => $row['tipomod'],
            'rec' => $row['rec'],
            'ejercicio' => $row['ejercicio']
        ];
    }

    return $datos;
}
/*while ($row = $conexion->extraer()) {
    $key = $row['poliza'];
    if (!isset($agrupado[$key])) {
        $agrupado[$key] = [];
    }
    $agrupado[$key][] = $row;
}*/

function anchoColumnas()
{
    // Valores para el archivo de exportación
    $anchoColumnas = array();
    //columnas de la tabla
    //Cuenta
    array_push($anchoColumnas, 25);

    //Descripcion
    array_push($anchoColumnas, 60);

    //Debe
    array_push($anchoColumnas, 15);

    //Haber
    array_push($anchoColumnas, 15);
    //Concepto
    array_push($anchoColumnas, 30);
    //ADF
    array_push($anchoColumnas, 10);

    //TDF
    array_push($anchoColumnas, 10);

    //RFC
    array_push($anchoColumnas, 13);

    //Referencia
    array_push($anchoColumnas, 13);

    //FechaRef
    array_push($anchoColumnas, 12);

    //EncProgr
    array_push($anchoColumnas, 13);

    //Pg
    array_push($anchoColumnas, 8);

    //Pe
    array_push($anchoColumnas, 8);

    //CveFin
    array_push($anchoColumnas, 13);

    //Grupo+ur
    array_push($anchoColumnas, 8);

    //Clc
    array_push($anchoColumnas, 8);

    //Afe
    array_push($anchoColumnas, 8);

    //Com
    array_push($anchoColumnas, 8);

    //Bdo
    array_push($anchoColumnas, 10);

    //Tipo+mod
    array_push($anchoColumnas, 8);

    //Rec
    array_push($anchoColumnas, 8);

    //Ejericio
    array_push($anchoColumnas, 8);

    array_push($anchoColumnas, 8);
    return $anchoColumnas;
}
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql(obtenerSqlParaDatos(
    $tipo,
    $modoPoliza,
    $gpo_id,
    $ur_id,
    $paraRevision
));
$conexion->consultar();

//calcula el espacio requerido
$numPolizas = $conexion->getNumeroRegistros();
$numColumnas = $conexion->getNumeroCampos();
$pesoExcel = calcularPeso($numPolizas, $numColumnas);
$conexion->desconectar();

$conexion2 = new AccesoDatos();
$conexion2->conectar();
$conexion2->setSql(obtenerSqlParaDatos(
    $tipo,
    $modoPoliza,
    $gpo_id,
    $ur_id,
    $paraRevision
));

$conexion2->consultar();
$conexion2->desconectar();

$agrupado = [];
$encabezado = [];
$table = [];
$datosEncabezadoArray = [];
$datos = armarArregloDeDatos($conexion2);
$book = [];
while ($row = $conexion->extraer()) {

    $key = $row['poliza'];
    if (!isset($agrupado[$key])) {
        $agrupado[$key] = [];

        //obtiene los datos de encabezado

        $datosEncabezado = obtenerDatosEncabezado($row, $gpo_id, $ur_id, $numPolizas);
        array_push($datosEncabezadoArray, $datosEncabezado);
        //fin obtención de datos
        $encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $encabLin2 = "CONTABILIDAD GUBERNAMENTAL ".APP_EJERCICIO;
        $encabLin3 = 'UR:' . $datosEncabezado['gpo_id'] . $datosEncabezado['ur_id'] . ' -' . $datosEncabezado['ur_nombre'];

        $anchoColumnas = anchoColumnas();
        $table[$key][] = armarColumnasTabla($anchoColumnas);

        //arma el arreglo de datos


        // Armamos los parametros opcionales
        $encabezado[$key][] = [
            'encabLin1' => $encabLin1,
            'encabLin2' => $encabLin2,
            'encabLin3' => $encabLin3,
            'ancho' => $anchoColumnas
        ];
        $encabezadoPrincipal = array(
            'col1' => array('col1' => 'Detalle Póliza', 'dim' => 10),
            'col2' => array('col2' => 'Clave Pres', 'dim' => 4),
            'col3' => array(
                'col3' => 'Vinculación póliza',
                'dim' => 9
            )
        );
        array_push($book, $key);

        //----------------------------------------------------------------------
    }
    $agrupado[$key][] = $row;
}

$otrosParametros = [
    'comprimir' => false,
    'nombre' => "cgPoliza",
    'encabezadoPrincipal' => $encabezadoPrincipal,
    'datosPoliza' => $datosEncabezadoArray,
    'book' => $book,
];

$excel = new array2Excel();
$excel->generaExcel($datos, $encabezado, true, $table, $otrosParametros);
