<?php

$datos = [];
function get_machote_estado_flujo_efectivo()
{

    return [

        '1' => [
            'niv1' => 'Flujos de Efectivo de las Actividades de Operación',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '',
            'sAnt' => '',
        ],
            'suma_FEAO_O' => [
                'niv1' => '',
                'niv2' => 'Origen',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_411' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Impuestos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_412' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Cuotas y Aportaciones de Seguridad Social',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_413' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Contribuciones de Mejoras',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_414' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Derechos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_415' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Productos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_416' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Aprovechamientos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_417' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Ingresos por Venta de Bienes y Prestación de Servicios',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_421' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Participaciones, Aportaciones, Convenios, Incentivos Derivados de la Colaboración Fiscal y Fondos Distintos de Aportaciones',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_422' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias, Asignaciones, Subsidios y Subvenciones, y Pensiones y Jubilaciones',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_OOO' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Otros Orígenes de Operación',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
            'suma_FEAO_A' => [
                'niv2' => 'Aplicación',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_511' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Servicios Personales',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_512' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Materiales y Suministros',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_513' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Servicios Generales',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_521' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias Internas y Asignaciones al Sector Público',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_522' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias al resto del Sector Público',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_523' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Subsidios y Subvenciones ',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_524' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Ayudas Sociales',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_525' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Pensiones y Jubilaciones',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_526' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias a Fideicomisos, Mandatos y Contratos Análogos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_527' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias a la Seguridad Social',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_528' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Donativos',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_529' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Transferencias al Exterior',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_531' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Participaciones ',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_532' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Aportaciones',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_533' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Convenios',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_OAO' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Otras Aplicaciones de Operación',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
            'suma_FEAO' => [
                'niv1' => 'Flujos Netos de Efectivo por Actividades de Operación',
                'niv2' => '',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
            '31' => [
                'niv1' => '',
                'niv2' => '',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '',
                'sAnt' => '',
            ],
            '32' => [
                'niv1' => 'Flujos de Efectivo de las Actividades de Inversión',
                'niv2' => '',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '',
                'sAnt' => '',
            ],
            'suma_FEAI_O' => [
                'niv1' => '',
                'niv2' => 'Origen',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_123_O' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Bienes Inmuebles, Infraestructura y Construcciones en Proceso',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_124_O' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Bienes Muebles',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_OOI' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Otros Orígenes de Inversión',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
            'suma_FEAI_A' => [
                'niv1' => '',
                'niv2' => 'Aplicación',
                'niv3' => '',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_123_A' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Bienes Inmuebles, Infraestructura y Construcciones en Proceso',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_124_A' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Bienes Muebles',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_OOA' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => 'Otras Aplicaciones de Inversión',
                    'niv4' => '',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
        'suma_FNEAI' => [
            'niv1' => 'Flujos Netos de Efectivo por Actividades de Inversión',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
        '42' => [
            'niv1' => '',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '',
            'sAnt' => '',
        ],
        '43' => [
            'niv1' => 'Flujos de Efectivo de las Actividades de Financiamiento',
        ],
        'suma_FEAF_O' => [
            'niv1' => '',
            'niv2' => 'Origen',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
            'suma_OEN' => [
                'niv1' => '',
                'niv2' => '',
                'niv3' => 'Endeudamiento Neto',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_OEN_I' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => '',
                    'niv4' => 'Interno',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_OEN_E' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => '',
                    'niv4' => 'Externo',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
            'importe_OEF' => [
                'niv1' => '',
                'niv2' => '',
                'niv3' => 'Otros Orígenes de Financiamiento',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
        'suma_FEAF_A' => [
            'niv1' => '',
            'niv2' => 'Aplicación',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
            'suma_ASD' => [
                'niv1' => '',
                'niv2' => '',
                'niv3' => 'Servicios de la Deuda',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],
                'importe_ASD_I' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => '',
                    'niv4' => 'Interno',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
                'importe_ASD_E' => [
                    'niv1' => '',
                    'niv2' => '',
                    'niv3' => '',
                    'niv4' => 'Externo',
                    'sAct' => '0.00',
                    'sAnt' => '0.00',
                ],
            'importe_OAF' => [
                'niv1' => '',
                'niv2' => '',
                'niv3' => 'Otras Aplicaciones de Financiamiento',
                'niv4' => '',
                'sAct' => '0.00',
                'sAnt' => '0.00',
            ],

        'suma_FNEAF' => [
            'niv1' => 'Flujos Netos de Efectivo por Actividades de Financiamiento',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
        '54' => [
            'niv1' => '',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '',
            'sAnt' => '',
        ],
        'suma_IDNEEE' => [
            'niv1' => 'Incremento/Disminución Neta en el Efectivo y Equivalentes al Efectivo ',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
        '56' => [
            'niv1' => '',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '',
            'sAnt' => '',
        ],
        'importe_EEEIE' => [
            'niv1' => 'Efectivo y Equivalentes al Efectivo al Inicio del Ejercicio',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],
        '58' => [
            'niv1' => '',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '',
            'sAnt' => '',
        ],
        'suma_EEEFE' => [
            'niv1' => 'Efectivo y Equivalentes al Efectivo al Final del Ejercicio',
            'niv2' => '',
            'niv3' => '',
            'niv4' => '',
            'sAct' => '0.00',
            'sAnt' => '0.00',
        ],

    ];


}

function rellena_flujo_efectivo(&$datos, $mesDesde, $mesHasta)
{

    $gpoId = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $urId = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

    $origen_periodo_act = 0;
    $origen_periodo_ant = 0;
    $aplicacion_periodo_act = 0;
    $aplicacion_periodo_ant = 0;

    dameOrigenesActividadesOperacion($mesDesde, $mesHasta, true, $datos, $origen_periodo_act);
    dameOrigenesActividadesOperacion(01, 12, false, $datos, $origen_periodo_ant);

    $datos['importe_OOO']['sAct'] = $otr_ing_op_act = dameOtrosOrigenesOperacion($mesDesde, $mesHasta, true);
    $datos['importe_OOO']['sAnt'] = $otr_ing_op_ant = dameOtrosOrigenesOperacion(01, 12, false);

    dameAplicacionActividadesOperacion($mesDesde, $mesHasta, true, $datos, $aplicacion_periodo_act);
    dameAplicacionActividadesOperacion(01, 12, false, $datos, $aplicacion_periodo_ant);

    $datos['importe_OAO']['sAct'] = $otr_apl_op_act = dameOtrasAplicacionesOperacion($mesDesde, $mesHasta, true);
    $datos['importe_OAO']['sAnt'] = $otr_apl_op_ant = dameOtrasAplicacionesOperacion(01, 12, false);

    // ---

    $datos['importe_123_O']['sAct'] = $abonos_bm_i_act = dameImporteGenCtaRubro($mesDesde, $mesHasta, true, 1, 2, 3, 'sumHaber', 'D');
    $datos['importe_123_O']['sAnt'] = $abonos_bm_i_ant = dameImporteGenCtaRubro(01, 12, false, 1, 2, 3, 'sumHaber', 'D');

    $datos['importe_124_O']['sAct'] = $abonos_b_m_act = dameImporteGenCtaRubro($mesDesde, $mesHasta, true, 1, 2, 4, 'sumHaber', 'D');
    $datos['importe_124_O']['sAnt'] = $abonos_b_m_ant = dameImporteGenCtaRubro(01, 12, false, 1, 2, 4, 'sumHaber', 'D');

    /************************************ PARA LA UR 116 (SECCION ["importe_OOI"] Y ["importe_OOA"])************************************************************ */
    $saldoIniAct = dameSumaNaturalezaPorGrupoDeCuentas(00, 00, true, 'saldo', 'D', '121', '');
    $saldoDiferenciaAct = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'saldo', 'D', '121', '');

    $saldoIniAnt = dameSumaNaturalezaPorGrupoDeCuentas(00, 00, false, 'saldo', 'D', '121', '');
    $saldoDiferenciaAnt = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'saldo', 'D', '121', '');
    /*********************************************************************************************** */

    if ($gpoId == 1 and $urId == 16) {
        //ESTA VALIDACION ES PARA LA UR 116 EN $datos["importe_OOI"]["sAct"]
        /*$datos["importe_OOI"]["sAct"] = $abonos_o_o_i_act = dameSumaNaturalezaPorGrupoDeCuentas( $mesDesde, $mesHasta, true, "saldo", "D", "121,125,127,129", "" );
        $datos["importe_OOI"]["sAnt"] = $abonos_o_o_i_ant = dameSumaNaturalezaPorGrupoDeCuentas( 01, 12, false, "saldo", "D", "121,125,127,129", "" );
        */
        if (as_number($saldoDiferenciaAct) > as_number($saldoIniAct)) {
            $datos['importe_OOI']['sAct'] = $abonos_o_o_i_act = $saldoDiferenciaAct;
        } else {
            $datos['importe_OOI']['sAct'] = $abonos_o_o_i_act = 0;
        }

        if (as_number($saldoDiferenciaAnt) > as_number($saldoIniAnt)) {
            $datos['importe_OOI']['sAnt'] = $abonos_o_o_i_ant = $saldoDiferenciaAnt;
        } else {
            $datos['importe_OOI']['sAnt'] = $abonos_o_o_i_ant = 0;
        }
    } else {
        $datos['importe_OOI']['sAct'] = $abonos_o_o_i_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumHaber', 'D', '121,125,127,129', '', '', true);
        $datos['importe_OOI']['sAnt'] = $abonos_o_o_i_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumHaber', 'D', '121,125,127,129', '', '', true);
    }

    $datos['importe_123_A']['sAct'] = $cargos_bm_i_act = dameImporteGenCtaRubro($mesDesde, $mesHasta, true, 1, 2, 3, 'saldo', 'D');
    $datos['importe_123_A']['sAnt'] = $cargos_bm_i_ant = dameImporteGenCtaRubro(01, 12, false, 1, 2, 3, 'saldo', 'D');

    //SE QUITA sólo para lo que resta del ejercicio 2025 la columna sumDebe por saldo ya que por cuestiones de cancelaciones no cuadra
    $datos['importe_124_A']['sAct'] = $cargos_b_m_act = dameImporteGenCtaRubro($mesDesde, $mesHasta, true, 1, 2, 4, 'saldo', 'D');
    $datos['importe_124_A']['sAnt'] = $cargos_b_m_ant = dameImporteGenCtaRubro(01, 12, false, 1, 2, 4, 'saldo', 'D');

    if ($gpoId == 1 and $urId == 16) {
        if (as_number($saldoDiferenciaAct) < as_number($saldoIniAct)) {
            $datos['importe_OOA']['sAct'] = $cargos_o_a_i_act = $saldoDiferenciaAct + dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumDebe', 'D', '125,127', '');
        } else {
            $datos['importe_OOA']['sAct'] = $cargos_o_a_i_act = 0;
        }

        if (as_number($saldoDiferenciaAnt) < as_number($saldoIniAnt)) {
            $datos['importe_OOA']['sAnt'] = $cargos_o_a_i_ant = $saldoDiferenciaAnt + dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumDebe', 'D', '125,127', '');
        } else {
            $datos['importe_OOA']['sAnt'] = $cargos_o_a_i_ant = 0;
        }
    } else {
        //SE QUITA sólo para lo que resta del ejercicio 2025 la columna sumDebe por saldo ya que por cuestiones de cancelaciones no cuadra
        $datos['importe_OOA']['sAct'] = $cargos_o_a_i_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'saldo', 'D', '56110', '');
        $datos['importe_OOA']['sAnt'] = $cargos_o_a_i_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'saldo', 'D', '56110', '');
    }

    // ---

    $datos['importe_OEN_I']['sAct'] = $abonos_oen_i_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumHaber', 'H', '', '2231,2233');
    $datos['importe_OEN_I']['sAnt'] = $abonos_oen_i_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumHaber', 'H', '', '2231,2233');

    $datos['importe_OEN_E']['sAct'] = $abonos_oen_e_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumHaber', 'H', '', '2232,2234');
    $datos['importe_OEN_E']['sAnt'] = $abonos_oen_e_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumHaber', 'H', '', '2232,2234');

    $datos['importe_OEF']['sAct'] = $abonos_oof_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumHaber', 'H', '', '2133,2235');
    $datos['importe_OEF']['sAnt'] = $abonos_oof_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumHaber', 'H', '', '2133,2235');

    /******** NUEVA FORMULA **********/
    //SALDOS ACTUALES
    /*$activoAplicacionActual =  dameSumaNaturalezaPorGrupoDeCuentas( $mesDesde, $mesHasta, true, "saldo", "D", "121", "" );
    $pasivoAplicacionActual =  dameSumaNaturalezaPorGrupoDeCuentas( $mesDesde, $mesHasta, true, "sumDebe", "H", "223", "" );
    $porcionAplicacionActual =  dameSumaNaturalezaPorGrupoDeCuentas( $mesDesde, $mesHasta, true, "saldo", "H", "213", "" );
    $appAplicacionActual = (as_number($activoAplicacionActual) + as_number($pasivoAplicacionActual)) - as_number($porcionAplicacionActual);
    $cargos_asd_i_act =  dameSumaNaturalezaPorGrupoDeCuentas( $mesDesde, $mesHasta, true, "sumDebe", "H", "544,545", "5411,5421,5431" ) + as_number($appAplicacionActual);

    $datos["importe_ASD_I"]["sAct"] = $cargos_asd_i_act;

    //SALDOS ANTERIOR
    $activoAplicacionAnterior =  dameSumaNaturalezaPorGrupoDeCuentas( 01, 12, false, "saldo", "D", "121", "" );
    $pasivoAplicacionAnterior =  dameSumaNaturalezaPorGrupoDeCuentas( 01, 12, false, "sumDebe", "H", "223", "" );
    $porcionAplicacionAnterior =  dameSumaNaturalezaPorGrupoDeCuentas( 01, 12, false, "saldo", "H", "213", "" );
    $appAplicacionAnterior = (as_number($activoAplicacionAnterior) + as_number($pasivoAplicacionAnterior)) - as_number($porcionAplicacionAnterior);
    $cargos_asd_i_ant =   dameSumaNaturalezaPorGrupoDeCuentas( 01, 12, false, "sumDebe", "H", "544,545", "5411,5421,5431" ) + as_number($appAplicacionAnterior);

    $datos["importe_ASD_I"]["sAnt"] = $cargos_asd_i_ant;*/
    /*********************************** */

    if ($gpoId == 1 and $urId == 16) {
        $datos['importe_ASD_I']['sAct'] = $cargos_asd_i_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'saldo', 'D', '', '2131,2141,5411,5421,5431,5441,5451');
        $datos['importe_ASD_I']['sAnt'] = $cargos_asd_i_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'saldo', 'D', '', '2131,2141,5411,5421,5431,5441,5451');
    } else {
        /******* * FORMULA ANTERIOR **********/
        $datos['importe_ASD_I']['sAct'] = $cargos_asd_i_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumDebe', 'H', '544,545', '2131,2141,5411,5421,5431');
        $datos['importe_ASD_I']['sAnt'] = $cargos_asd_i_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumDebe', 'H', '544,545', '2131,2141,5411,5421,5431');
        /*************************************/
    }

    $datos['importe_ASD_E']['sAct'] = $cargos_asd_e_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumDebe', 'H', '', '2132,2142,5412,5422,5432');
    $datos['importe_ASD_E']['sAnt'] = $cargos_asd_e_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumDebe', 'H', '', '2132,2142,5412,5422,5432');

    $datos['importe_OAF']['sAct'] = $cargos_oaf_act = dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, true, 'sumDebe', 'H', '', '2133,2235');
    $datos['importe_OAF']['sAnt'] = $cargos_oaf_ant = dameSumaNaturalezaPorGrupoDeCuentas(01, 12, false, 'sumDebe', 'H', '', '2133,2235');

    // ---

    $datos['importe_EEEIE']['sAct'] = $efvo_inicial_act = dameImporteGenCtaRubro($mesDesde, $mesHasta, true, 1, 1, 1, 'saldo', 'D', '<', true);
    $datos['importe_EEEIE']['sAnt'] = $efvo_inicial_ant = dameImporteGenCtaRubro(01, 12, false, 1, 1, 1, 'saldo', 'D', '<', true);


    /******* Sumatorias **********************/

    $serv_per_act = 0; // Initialize the variable
    $mat_sum_act = 0; // Initialize the variable
    $serv_gen_act = 0; // Initialize the variable
    $donativos_act = 0; // Initialize the variable

    /************************************************************************ */
    // Flujos de Efectivo de las Actividades de Operación / Origen
    $datos['suma_FEAO_O']['sAct'] = $flujo_ori_act_op_act = as_number($origen_periodo_act) + as_number($otr_ing_op_act);
    // Flujos de Efectivo de las Actividades de Operación / Aplicación
    $datos['suma_FEAO_A']['sAct'] = $flujo_apl_act_op_act = as_number($aplicacion_periodo_act) + as_number($otr_apl_op_act);
    // I. Flujos Netos de Efectivo por Actividades de Operación
    $datos['suma_FEAO']['sAct'] = $flujo_neto_act_op_act = as_number($flujo_ori_act_op_act) - as_number($flujo_apl_act_op_act);
    /************************************************************************** */

    /************************************************************************* */
    // Flujos de Efectivo de las Actividades de Inversión / Origen
    $datos['suma_FEAI_O']['sAct'] = $flujo_ori_act_inv_act = as_number($abonos_bm_i_act) + as_number($abonos_b_m_act) + as_number($abonos_o_o_i_act);
    // Flujos de Efectivo de las Actividades de Inversión / Aplicación
    $datos['suma_FEAI_A']['sAct'] = $flujo_apl_act_inv_act = as_number($cargos_bm_i_act) + as_number($cargos_b_m_act) + as_number($cargos_o_a_i_act);
    // II. Flujos Netos de Efectivo por Actividades de Inversión
    $datos['suma_FNEAI']['sAct'] = $flujo_neto_act_inv_act = as_number($flujo_ori_act_inv_act) - as_number($flujo_apl_act_inv_act);
    /******************************************************** */

    /********************************************************************************* */
    // Flujos de Efectivo de las Actividades de Financiamiento / Endeudamiento Neto
    $datos['suma_OEN']['sAct'] = $endeudamiento_neto_origen_act = as_number($abonos_oen_i_act) + as_number($abonos_oen_e_act);
    // Flujos de Efectivo de las Actividades de Financiamiento / Origen
    $datos['suma_FEAF_O']['sAct'] = $endeudamiento_neto_origen_act = as_number($endeudamiento_neto_origen_act) + as_number($abonos_oof_act);
    // Flujos de Efectivo de las Actividades de Financiamiento / Servicios de la Deuda
    $datos['suma_ASD']['sAct'] = $servicios_deuda_aplicacion_interno_act = as_number($cargos_asd_i_act) + as_number($cargos_asd_e_act);
    //  Flujos de Efectivo de las Actividades de Financiamiento / Aplicación
    $datos['suma_FEAF_A']['sAct'] = $feaf_aplicacion_act = as_number($servicios_deuda_aplicacion_interno_act) + as_number($cargos_oaf_act);
    /******************************************************************* */

    // III.  Flujos Netos de Efectivo por Actividades de Financiamiento
    $datos['suma_FNEAF']['sAct'] = $flujo_neto_act_fina_act = as_number($endeudamiento_neto_origen_act) - as_number($servicios_deuda_aplicacion_interno_act);

    // IV. Incremento/Disminución Neta en el Efectivo y Equivalentes al Efectivo
    $datos['suma_IDNEEE']['sAct'] = $cambio_neto_act = as_number($flujo_neto_act_op_act) + as_number($flujo_neto_act_inv_act) + as_number($flujo_neto_act_fina_act);

    // V. Efectivo y Equivalentes al Efectivo al Final del Ejercicio
    $datos['suma_EEEFE']['sAct'] = $cambio_neto_act = as_number($cambio_neto_act) + as_number($efvo_inicial_act);




    // ===========================================================================================
    // Para 2025 las URs capturarán manualmente DICIEMBRE 2024 (Definición de la Dirección de Contabilidad)
    // ===========================================================================================
    if (APP_EJERCICIO == 2025) {
        captura2024($datos, $mesDesde, $mesHasta);
    } else {
        $datos['suma_FEAO_O']['sAnt'] = $flujo_ori_act_op_ant = as_number($origen_periodo_ant) + as_number($otr_ing_op_ant);
        $serv_per_ant = 0; // Initialize the variable
        $mat_sum_ant = 0; // Initialize the variable
        $serv_gen_ant = 0; // Initialize the variable
        $donativos_ant = 0; // Initialize the variable
        $datos['suma_FEAO_A']['sAnt'] = $flujo_apl_act_op_ant = as_number($aplicacion_periodo_ant) + as_number($otr_apl_op_ant);
        $datos['suma_FEAO']['sAnt'] = $flujo_neto_act_op_ant = as_number($flujo_ori_act_op_ant) - as_number($flujo_apl_act_op_ant);
        $datos['suma_FEAI_O']['sAnt'] = $flujo_ori_act_inv_ant = as_number($abonos_bm_i_ant) + as_number($abonos_b_m_ant) + as_number($abonos_o_o_i_ant);
        $datos['suma_FEAI_A']['sAnt'] = $flujo_apl_act_inv_ant = as_number($cargos_bm_i_ant) + as_number($cargos_b_m_ant) + as_number($cargos_o_a_i_ant);
        $datos['suma_FNEAI']['sAnt'] = $flujo_neto_act_inv_ant = as_number($flujo_ori_act_inv_ant) - as_number($flujo_apl_act_inv_ant);
        $datos['suma_IDNEEE']['sAnt'] = $cambio_neto_ant = as_number($flujo_neto_act_op_ant) + as_number($flujo_neto_act_inv_ant);
        $datos['suma_EEEFE']['sAnt'] = $cambio_neto_ant = as_number($cambio_neto_ant) + as_number($efvo_inicial_ant);
        $datos['suma_ASD']['sAnt'] = $servicios_deuda_aplicacion_interno_ant = as_number($cargos_asd_i_ant) + as_number($cargos_asd_e_ant);

    }
}


// Le quitamos las comas a una cadena formateada y la regresamos como número float
function as_number($str_importe)
{
    return (float) str_replace(',', '', $str_importe);
}


// Regresamos el where de meses que se usará para obtener la información, dependiendo si
// es para la columna de perido actual, o periodo anterior
function dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual, $regla_mes = '=')
{

    $ejercicio = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;

    // grupo, ur, ue
    // Usando la vinculación de ejercicios anteriores y las cargas de trabajo
    // ===============================================================================================
    $otroWhere = '';
    $where = '';
    // Si tienen cargas de trabajo asignadas pidieron SÓLO LAS URs SELECCIONADAS...
    $tipoReporte = isset($_POST['tipoReporte']) ? $_POST['tipoReporte'] : 1;
    $grupos = isset($_POST['grupos']) ? $_POST['grupos'] : ''; // Define the $grupos variable
    if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
        // Si es EJERCICIO ACTUAL
        if ($es_periodo_actual) {
            // Si limitaron por grupo o excluyeron alguna UR
            if ($grupos != '') {
                $otroWhere = ' WHERE gpo_id IN ( ' . rtrim($grupos) . ' ) AND ';

                // Si excluyeron a alguna UR
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                if ($excluir != '') {
                    $otroWhere = ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( gpo_id, ur_id ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
                }
                // Si no usaron las campos especiales, es un where estandar
            }
            // Si es del EJERCICIO ANTERIOR
        } else {
            // Si limitaron por grupo o excluyeron alguna UR
            if ($grupos != '') {
                $otroWhere = '  WHERE ' . ($ejercicio == $ejercicioAnterior ? 'v.gpo_id_' . $ejercicioAnterior : 'gpo_id') . ' IN ( ' . rtrim($grupos) . ' ) AND ';

                // Si excluyeron a alguna UR
                $excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
                if ($excluir != '') {
                    $otroWhere = ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( ' . ($ejercicio == 'ctrl' . $ejercicioAnterior . '.' ? 'v.gpo_id_' . $ejercicioAnterior . '' : 'gpo_id') . ',' . ($ejercicio != '' ? 'v.ur_id_' . $ejercicioAnterior : 'ur_id') . ' ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
                }
            }

        }
    }

    $gpo = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
    $ur = ($_SESSION[_S]['nvj_id'] > 2) ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';


    if ($es_periodo_actual) {
        $cargasTrabajo = ' INNER JOIN ' . $ejercicio . 'cat_CargaTrabajo t USING(gpo_id,ur_id)';
        $gpoUr = "gpo_id = $gpo AND ur_id = $ur " . ($ue != '' ? ' AND ue_id = ' . $ue . '' : '');
        if ($regla_mes == '=') {
            $rango = ($mesHasta < 0 ? "msc_id < $mesDesde AND" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde AND " : "( msc_id BETWEEN $mesDesde AND $mesHasta ) AND ")) ;
        } else {
            $rango = " msc_id < $mesDesde AND";
        }
    } else {
        $cargasTrabajo = ' INNER JOIN ctrl' . $ejercicioAnterior . '.cat_CargaTrabajo t ON t.gpo_id = v.gpo_id_' . $ejercicio . ' AND t.ur_id = v.ur_id_' . $ejercicio;
        $gpoUr = 'v.gpo_id_' . $ejercicioAnterior . " = $gpo AND v.ur_id_" . $ejercicioAnterior . " = $ur " . ($ue != '' ? ' AND u.ue_id_' . $ejercicioAnterior . ' = ' . $ue : '');
        #$rango = " msc_id < 13 AND";
        if ($regla_mes == '=') {
            $rango = ($mesHasta < 0 ? "msc_id < $mesDesde AND" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde AND " : "( msc_id BETWEEN $mesDesde AND $mesHasta ) AND ")) ;
        } else {
            $rango = " msc_id < $mesDesde AND";
        }
    }

    return $where . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? " . $cargasTrabajo . " : '') . ' 
    WHERE ' : (($tipoReporte == 3) ? $otroWhere : 'WHERE ' . $gpoUr)) . ' AND
    ' . $rango . '
    ' . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '');
}


// Ejecutamos la consulta y regresamos un array con el resultado
function dame_resultado_consulta($sql, $regresa_1_linea = true)
{

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar();
    $con->desconectar();

    if ($regresa_1_linea) {

        return $con->extraer();

    } else {

        $res = [];

        while ($row = $con->extraer()) {
            $res[ $row['agrupador'] ] = $row;
        }

        return $res;

    }

}


// Determinamos la base de datos a usar
function dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual)
{

    if ($es_periodo_actual) {
        $bd = 'ctrl' . $_SESSION[_S]['ejercicioActual'];
    } else {
        if ($mesDesde == $mesHasta) {
            if ($mesDesde == 1) {
                $bd = 'ctrl' . strval(((int) $_SESSION[_S]['ejercicioActual']) - 1);
            } else {
                $bd = 'ctrl' . $_SESSION[_S]['ejercicioActual'];
            }
        } else {
            // Si están pidiendo trimestres
            if (($mesDesde == 4 && $mesHasta == 6) ||
                ($mesDesde == 7 && $mesHasta == 9) ||
                ($mesDesde == 10 && $mesHasta == 12)) {
                $bd = 'ctrl' . $_SESSION[_S]['ejercicioActual'];
            } else {
                $bd = 'ctrl' . strval(((int) $_SESSION[_S]['ejercicioActual']) - 1);
            }
        }
    }

    return $bd;
}

// IA. Origen de las Actividades de Operación
function dameOrigenesActividadesOperacion($mesDesde, $mesHasta, $es_periodo_actual, &$datos, &$origen_periodo)
{
    $resultado = [];

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual);

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);

    $periodo = ($es_periodo_actual ? 'sAct' : 'sAnt');

    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';


    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    $sql = "SELECT 
            concat( ger_id, cgp_id, rub_id ) AS agrupador, 
            SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, ( SUM( pdx_haber ) - SUM( pdx_debe ) ) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
            epl_id = 'N' 
            AND ger_id = 4
        GROUP BY ger_id, cgp_id, rub_id 
    ";

    $resultado = dame_resultado_consulta($sql, false);

    // Rellenamos el formato y acumulamos los importes
    $datos['importe_411'][  $periodo ] = isset($resultado['411']['saldo']) ? $resultado['411']['saldo'] : '0';
    $datos['importe_412'][  $periodo ] = isset($resultado['412']['saldo']) ? $resultado['412']['saldo'] : '0';
    $datos['importe_413'][  $periodo ] = isset($resultado['413']['saldo']) ? $resultado['413']['saldo'] : '0';
    $datos['importe_414'][  $periodo ] = isset($resultado['414']['saldo']) ? $resultado['414']['saldo'] : '0';
    $datos['importe_415'][  $periodo ] = isset($resultado['415']['saldo']) ? $resultado['415']['saldo'] : '0';
    $datos['importe_416'][  $periodo ] = isset($resultado['416']['saldo']) ? $resultado['416']['saldo'] : '0';
    $datos['importe_417'][  $periodo ] = isset($resultado['417']['saldo']) ? $resultado['417']['saldo'] : '0';
    $datos['importe_421'][ $periodo ] = isset($resultado['421']['saldo']) ? $resultado['421']['saldo'] : '0';
    $datos['importe_422'][ $periodo ] = isset($resultado['422']['saldo']) ? $resultado['422']['saldo'] : '0';

    $origen_periodo += as_number($datos['importe_411'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_412'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_413'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_414'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_415'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_416'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_417'][  $periodo ]);
    $origen_periodo += as_number($datos['importe_421'][ $periodo ]);
    $origen_periodo += as_number($datos['importe_422'][ $periodo ]);

}



// Ingresos por Venta de Bienes y Prestación de Servicios
function dameOtrosOrigenesOperacion($mesDesde, $mesHasta, $es_periodo_actual)
{

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual);

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);


    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    // En la definición piden el ***saldo*** de las 431 y 439
    // y APARTE piden la ***suma*** de x cuentas que ya incluyen la regla de pólizas marcadas...
    // por lo tanto son reglas separadas y eso nos obliga a hacer un UNION
    $sql = "
    WITH base AS (
        SELECT 
            SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, ( SUM( pdx_haber ) - SUM( pdx_debe ) ) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
            epl_id = 'N' 
            AND ( 
                    ( ger_id, cgp_id, rub_id ) IN ( (4,3,1), (4,3,2) , (4,3,9) ) 
                )

        UNION 

        SELECT 
            0 AS sumDebe, 0,IF(sign(SUM( a.pdx_haber ) - SUM( a.pdx_debe))=-1,0,SUM( a.pdx_haber ) - SUM( a.pdx_debe)) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar a 
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
        ( ( a.ger_id, a.cgp_id, a.rub_id ) IN ( 
                (2,1,1), (2,1,2), (2,1,5), (2,1,6), (2,1,9), (2,2,1), (2,2,2), (2,2,4), 
                #Ultima modificacion a la formula
                #(2,2,5), (1,1,2), (1,1,3), (1,1,4), (1,1,5), (1,1,9), (1,2,2)     
                (2,2,5), (2,2,6), (1,1,2), (1,1,3), (1,1,4), (1,1,5), (1,1,9), (1,2,2)        
            )
        )
        AND  ( ( a.ger_id, a.cgp_id, a.rub_id, a.cgcu_id,a.cgsc_id ) NOT IN ( 
                (2,1,1,9,0)
            )
        )
        AND epl_id NOT IN('C','H') 
        GROUP BY a.ger_id, a.cgp_id, a.rub_id  

        UNION 

        SELECT 
            0 AS sumDebe, 0,IF(sign(SUM( a.pdx_haber ) - SUM( a.pdx_debe))=-1,0,SUM( a.pdx_haber ) - SUM( a.pdx_debe)) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar a
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
        ( ( a.ger_id, a.cgp_id, a.rub_id ) IN ( 
                (3,1,1), (3,1,3), (3,2,1), (3,2,2)            
            )
        )
        AND (
            ( mmc_id NOT IN ( 'L','V' ) ) OR
            ( mmc_id IN ( 'L','V' ) AND apg_id = 'FE' )
        )
        AND epl_id NOT IN('C','H')
        GROUP BY a.ger_id, a.cgp_id, a.rub_id     
    ) 

    SELECT SUM( sumDebe ) AS sumDebe, SUM( sumHaber ) AS sumHaber, SUM( saldo ) AS saldo 
    FROM base
    ";

    return dame_resultado_consulta($sql)['saldo'];
}


// IA. Aplicación de las Actividades de Operación
function dameAplicacionActividadesOperacion($mesDesde, $mesHasta, $es_periodo_actual, &$datos, &$aplicacion_periodo)
{

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual);

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);

    $periodo = ($es_periodo_actual ? 'sAct' : 'sAnt');

    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    $sql = "SELECT 
            concat( ger_id, cgp_id, rub_id ) AS agrupador, 
            SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, ( SUM( pdx_debe ) - SUM( pdx_haber ) ) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
            epl_id = 'N' 
            AND ger_id = 5
        GROUP BY ger_id, cgp_id, rub_id 
    ";

    $resultado = dame_resultado_consulta($sql, false);

    // Rellenamos el formato y acumulamos los importes
    $datos['importe_511'][ $periodo ] = isset($resultado['511']['saldo']) ? $resultado['511']['saldo'] : '0';
    $datos['importe_512'][ $periodo ] = isset($resultado['512']['saldo']) ? $resultado['512']['saldo'] : '0';
    $datos['importe_513'][ $periodo ] = isset($resultado['513']['saldo']) ? $resultado['513']['saldo'] : '0';
    $datos['importe_521'][ $periodo ] = isset($resultado['521']['saldo']) ? $resultado['521']['saldo'] : '0';
    $datos['importe_522'][ $periodo ] = isset($resultado['522']['saldo']) ? $resultado['522']['saldo'] : '0';
    $datos['importe_523'][ $periodo ] = isset($resultado['523']['saldo']) ? $resultado['523']['saldo'] : '0';
    $datos['importe_524'][ $periodo ] = isset($resultado['524']['saldo']) ? $resultado['524']['saldo'] : '0';
    $datos['importe_525'][ $periodo ] = isset($resultado['525']['saldo']) ? $resultado['525']['saldo'] : '0';
    $datos['importe_526'][ $periodo ] = isset($resultado['526']['saldo']) ? $resultado['526']['saldo'] : '0';
    $datos['importe_527'][ $periodo ] = isset($resultado['527']['saldo']) ? $resultado['527']['saldo'] : '0';
    $datos['importe_528'][ $periodo ] = isset($resultado['528']['saldo']) ? $resultado['528']['saldo'] : '0';
    $datos['importe_529'][ $periodo ] = isset($resultado['529']['saldo']) ? $resultado['529']['saldo'] : '0';
    $datos['importe_531'][ $periodo ] = isset($resultado['531']['saldo']) ? $resultado['531']['saldo'] : '0';
    $datos['importe_532'][ $periodo ] = isset($resultado['532']['saldo']) ? $resultado['532']['saldo'] : '0';
    $datos['importe_533'][ $periodo ] = isset($resultado['533']['saldo']) ? $resultado['533']['saldo'] : '0';

    $aplicacion_periodo += as_number($datos['importe_511'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_512'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_513'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_521'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_522'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_523'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_524'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_525'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_526'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_527'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_528'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_529'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_531'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_532'][ $periodo ]);
    $aplicacion_periodo += as_number($datos['importe_533'][ $periodo ]);

}




// Otras aplicaciones de operación
function dameOtrasAplicacionesOperacion($mesDesde, $mesHasta, $es_periodo_actual)
{

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual);

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);

    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    // En la definición piden el ***saldo*** de las 431 y 439
    // y APARTE piden la ***suma*** de x cuentas que ya incluyen la regla de pólizas marcadas...
    // por lo tanto son reglas separadas y eso nos obliga a hacer un UNION
    $sql = "
    WITH base AS (
        SELECT 
            SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, ( SUM( pdx_debe ) - SUM( pdx_haber ) ) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
            epl_id = 'N' 
            AND ( 
                    ( ger_id, cgp_id, rub_id ) IN ( (5,5,9) ) 
                )

        UNION 

        SELECT 
            0 AS sumDebe, 0,IF(sign(SUM( a.pdx_debe ) - SUM( a.pdx_haber))=1,SUM( a.pdx_debe ) - SUM( a.pdx_haber),0) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar a
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
        ( ( a.ger_id, a.cgp_id, a.rub_id ) IN ( 
                (2,1,1), (2,1,2), (2,1,5), (2,1,6), (2,1,9), (2,2,1), (2,2,2), (2,2,4), (2,2,6),
                (2,2,5), (1,1,2), (1,1,3), (1,1,4), (1,1,5), (1,1,9), (1,2,2)            
            )
        )
        AND  ( ( a.ger_id, a.cgp_id, a.rub_id, a.cgcu_id,a.cgsc_id ) NOT IN ( 
                (2,1,1,9,0)
            )
        )
        AND epl_id NOT IN('C','H')
        GROUP BY a.ger_id, a.cgp_id, a.rub_id     
    
    UNION 

        SELECT 
            0 AS sumDebe, 0,IF(sign(SUM( a.pdx_debe ) - SUM( a.pdx_haber))=1,SUM( a.pdx_debe ) - SUM( a.pdx_haber),0) AS saldo
        FROM $bd.cg_PolizaDetalleAuxiliar a
        JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
        $anterior
        ( ( a.ger_id, a.cgp_id, a.rub_id ) IN ( 
                #Ultima modificacion a la formula
                #(3,1,1), (3,2,1), (3,2,2)    
                (3,1,1), (3,1,3), (3,2,1), (3,2,2)         
            )
        )
        AND (
            ( mmc_id NOT IN ( 'L','V' ) ) OR
            ( mmc_id IN ( 'L','V' ) AND apg_id = 'FE' )
        )
        AND epl_id NOT IN('C','H') 
        GROUP BY a.ger_id, a.cgp_id, a.rub_id     
    ) 

    SELECT SUM( sumDebe ) AS sumDebe, SUM( sumHaber ) AS sumHaber, SUM( saldo ) AS saldo 
    FROM base
    ";

    return dame_resultado_consulta($sql)['saldo'];
}



// Obtenemos sumaDebe, sumaHaber o saldo de una cuenta por género + grupo + rubro
function dameImporteGenCtaRubro($mesDesde, $mesHasta, $es_periodo_actual, $ger_id, $cgp_id, $rub_id, $columna, $naturaleza_cuenta, $regla_mes = '=', $solo_saldo = false)
{

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual, $regla_mes);

    if ($naturaleza_cuenta == 'D') {
        $where_saldo = '( SUM( pdx_debe ) - SUM( pdx_haber ) )';
    } else {
        $where_saldo = '( SUM( pdx_haber ) - SUM( pdx_debe ) )';
    }

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);

    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    $th = $ger_id . '.' . $cgp_id . '.' . $rub_id;

    // Si quieren el ***saldo*** de la cuenta, no hay filtros por pólizas
    if ($solo_saldo) {
        $filtro_saldo = '';
    } else {
        switch ($th) {
            case '1.2.3':
            case '1.2.4':
                if ($columna == 'sumHaber') {
                    $filtro_saldo = " AND ( 
                        ( mmc_id IN ( 'L') AND apg_id = 'FE' )
                    )";
                } else {
                    $filtro_saldo = " AND (
                        ( mmc_id NOT IN ( 'L','V' ) ) OR
                        ( mmc_id IN ( 'L','V','R' ) AND apg_id = 'FE' )
                    )";
                }
                break;
            default:
                $filtro_saldo = " AND (
                    ( mmc_id NOT IN ( 'L','V' ) ) OR
                    ( mmc_id IN ( 'L','V' ) AND apg_id = 'FE' )
                )";
                break;
        }
    }

    if ($th == '1.2.4') {
        //QUITANDO CTA 127 PARA BIENES MUEBLES
        $aplicacionBienes = "concat( ger_id, cgp_id, rub_id) IN ('124','125')";
    } else {
        $aplicacionBienes = "ger_id = $ger_id AND cgp_id = $cgp_id AND rub_id = $rub_id";
    }

    $sql = "SELECT 
        SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, $where_saldo AS saldo
    FROM $bd.cg_PolizaDetalleAuxiliar
    JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
    $anterior
        epl_id = 'N' AND epl_id  NOT IN('C','H') 
        AND  $aplicacionBienes
        $filtro_saldo
    ";

    return dame_resultado_consulta($sql)[$columna];
}




// Obtenemos la suma de movimientos deudores o acreedores para un grupo de cuentas
function dameSumaNaturalezaPorGrupoDeCuentas($mesDesde, $mesHasta, $es_periodo_actual, $columna, $naturaleza_cuenta, $grupo_de_cuentas_nivel_3, $grupo_de_cuentas_nivel_4, $regla_mes = '=', $manuales = false)
{

    $where_periodo = dame_where_periodo($mesDesde, $mesHasta, $es_periodo_actual, $regla_mes);

    if ($naturaleza_cuenta == 'D') {
        $where_saldo = '( SUM( pdx_debe ) - SUM( pdx_haber ) )';
    } else {
        $where_saldo = '( SUM( pdx_haber ) - SUM( pdx_debe ) )';
    }

    $bd = dame_base_datos_a_usar($mesDesde, $mesHasta, $es_periodo_actual);

    $ejercicioActual = APP_EJERCICIO;
    $ejercicioAnterior = APP_EJERCICIO_ANTERIOR;
    $ue = isset($_POST['ue']) ? $_POST['ue'] : '';

    if (!$es_periodo_actual) {
        $anterior = 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadResponsable_' . $ejercicioAnterior . '_' . $ejercicioActual . ' v ON v.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND v.ur_id_' . $ejercicioAnterior . ' = ur_id
            ' . ($ue != '' ? 'LEFT JOIN ' . BD_SINPRES . '.vin_UnidadEjecutora_' . $ejercicioAnterior . '_' . $ejercicioActual . ' u ON u.gpo_id_' . $ejercicioAnterior . ' = gpo_id AND u.ur_id_' . $ejercicioAnterior . ' = ur_id AND u.ue_id_' . $ejercicioAnterior . ' = ue_id ' : '') .
            $where_periodo;
    } else {
        $anterior = $where_periodo;
    }

    // Pueden enviar cuentas hasta el nivel 3, o cuentas hasta el nivel 4, así que dependiendo
    // lo que manden, armamos el WHERE
    // OJO: Por precaución, en el WHERE primero validaremos los niveles 4 y luego los 3
    $where_cuentas_nivel_3 = '';
    $where_cuentas_nivel_4 = '';
    $mmcId = '';

    if ($grupo_de_cuentas_nivel_3 != '') {
        switch ($grupo_de_cuentas_nivel_3) {
            case '56110':
                if ($grupo_de_cuentas_nivel_4 != '') {
                    $where_cuentas_nivel_3 = ' OR';
                } else {
                    $where_cuentas_nivel_3 = 'AND (';
                }
                $where_cuentas_nivel_3 .= " concat( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id) IN ( $grupo_de_cuentas_nivel_3 )";
                $where_cuentas_nivel_3 .= "OR concat( ger_id, cgp_id, rub_id) IN ('127')";
                $where_cuentas_nivel_3 .= ')';
                $mmcId = " 'L','V', 'R' ";
                break;
            default:
                if ($grupo_de_cuentas_nivel_4 != '') {
                    $where_cuentas_nivel_3 = ' OR';
                } else {
                    $where_cuentas_nivel_3 = 'AND (';
                }
                $where_cuentas_nivel_3 .= " concat( ger_id, cgp_id, rub_id ) IN ( $grupo_de_cuentas_nivel_3 )";
                $where_cuentas_nivel_3 .= ')';
                $mmcId = " 'L','V' ";
                break;
        }
    }

    if ($grupo_de_cuentas_nivel_4 != '') {
        $where_cuentas_nivel_4 = 'AND (';
        $where_cuentas_nivel_4 .= "concat( ger_id, cgp_id, rub_id, cgcu_id ) IN ( $grupo_de_cuentas_nivel_4 )";
        if ($grupo_de_cuentas_nivel_3 == '') {
            $where_cuentas_nivel_4 .= ' )';
        }
        $mmcId = " 'L','V' ";
    }

    if ($manuales) {
        //Sólo para Otros orígenes de Inversión
        $mmc_id = '';
    } else {
        $mmc_id = "( mmc_id NOT IN ( 'L','V' ) ) OR";
    }

    $sql = "SELECT 
        SUM( pdx_debe ) AS sumDebe, SUM( pdx_haber ) AS sumHaber, $where_saldo AS saldo
    FROM $bd.cg_PolizaDetalleAuxiliar
    JOIN $bd.cg_PolizaMaestro USING ( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id ) 
    $anterior
        epl_id = 'N' AND epl_id NOT IN('C','H') 
        $where_cuentas_nivel_4
        $where_cuentas_nivel_3
        AND (
            $mmc_id
            ( mmc_id IN ( $mmcId  ) AND apg_id = 'FE' )
        )  
    ";

    return dame_resultado_consulta($sql)[$columna];
}

//Consulta para Captura Ejercicio 2024
function captura2024(&$datos, $mesDesde, $mesHasta)
{
    $where_periodo = dame_where_periodo(01, 12, true);

    $sql = 'SELECT msc_id as meses 
        FROM ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . " 
        $where_periodo usr_id IS NOT NULL
        ";

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar();
    $r = $con->extraer();
    $con->desconectar();

    if ($con->getNumeroRegistros() == 0) {
        foreach ($datos as $clave => $valor) {
            if (substr($clave, 0, 5) == 'suma_' || substr($clave, 0, 8) == 'importe_') {
                $datos[$clave]['sAnt'] = 'FALTA  CAPTURA DICIEMBRE ' . APP_EJERCICIO;
            }
        }
        return;
    }

    // Si todos los meses del periodo ya están autorizados, procedemos
    // ===============================================================
    $sql = 'SELECT 
        suma_FEAO_O, 
        importe_411, 
        importe_412, 
        importe_413, 
        importe_414, 
        importe_415, 
        importe_416, 
        importe_417, 
        importe_421, 
        importe_422, 
        importe_OOO, 
        suma_FEAO_A, 
        importe_511, 
        importe_512, 
        importe_513, 
        importe_521, 
        importe_522, 
        importe_523, 
        importe_524, 
        importe_525, 
        importe_526, 
        importe_527, 
        importe_528, 
        importe_529, 
        importe_531, 
        importe_532, 
        importe_533, 
        importe_OAO, 
        suma_FEAO, 
        suma_FEAI_O, 
        importe_123_O, 
        importe_124_O, 
        importe_OOI, 
        suma_FEAI_A, 
        importe_123_A, 
        importe_124_A, 
        importe_OOA, 
        suma_FNEAI, 
        suma_FEAF_O, 
        suma_OEN, 
        importe_OEN_I, 
        importe_OEN_E, 
        importe_OEF, 
        suma_FEAF_A, 
        suma_ASD, 
        importe_ASD_I, 
        importe_ASD_E, 
        importe_OAF, 
        suma_FNEAF, 
        suma_IDNEEE, 
        importe_EEEIE, 
        suma_EEEFE 
    FROM ' . BD_CTRL . '.cg_FlujoEfectivo' . APP_EJERCICIO_ANTERIOR . "
    WHERE $where_periodo
    ";

    $con = new AccesoDatos();
    $con->conectar();
    $con->setSql($sql);
    $con->consultar();
    $r = $con->extraer();
    $con->desconectar();

    foreach ($r as $clave => $valor) {
        if (substr($clave, 0, 5) == 'suma_' || substr($clave, 0, 8) == 'importe_') {
            $datos[$clave]['sAnt'] = $valor;
        }
    }
    return;
}

// Fin del código
