<?php

session_start();
ini_set('memory_limit', '1G');

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida.
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';

use PhpOffice\PhpSpreadsheet\Style\Alignment;

class exp_ObraPub extends Phpspreadsheet
{
    protected $titulos_columnas;

    public function __construct()
    {
        parent::__construct();
        $this->hoja1();
        $this->spreadsheet->setActiveSheetIndex(0);
        $this->output_web_browser('Rep_ObraCvePresupuestal.xlsx');
    }

    public function hoja1()
    {

        $this->encabezadoHoja1();

        $datos = $this->consultaHoja1();
        $this->spreadsheet->getActiveSheet()->fromArrayDataExplicit($datos, null, 'A6', false, ['X']);

        $tam = count($datos) + 5;
        $this->spreadsheet->getActiveSheet()->getStyle('A6:BJ' . $tam)->getFont()->setSize('8');
        $this->spreadsheet->getActiveSheet()->getStyle('A6:BJ' . $tam)->getAlignment()->setWrapText(true);
        $this->spreadsheet->getActiveSheet()->getStyle('A6:BJ' . $tam)->getBorders()->getOutline()->setBorderStyle('thin');

        $this->spreadsheet->getActiveSheet()->getStyle('Q6:Q' . $tam)->getNumberFormat()->setFormatCode('0');
        $this->spreadsheet->getActiveSheet()->getStyle('X6:X' . $tam)->getNumberFormat()->setFormatCode('0');

        $this->spreadsheet->getActiveSheet()->getStyle('X6:X' . $tam)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $this->spreadsheet->getActiveSheet()->getStyle('AQ6:AQ' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AR6:AR' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AS6:AS' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AT6:AT' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AU6:AU' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AV6:AV' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AX6:AX' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AY6:AY' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('AZ6:AZ' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BA6:BA' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BB6:BB' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BC6:BC' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BD6:BD' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BE6:BE' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BF6:BF' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    public function encabezadoHoja1()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        $hoja1->setTitle('ObraCvePresupuestal');

        $hoja1->setCellValue('A1', 'Fecha: ' . date('Y-m-d H:i:s'));
        $hoja1->getStyle('A1')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A1')->getAlignment()->setVertical('top');
        $hoja1->getStyle('A1')->getFont()->setBold(true);
        $hoja1->getStyle('A1')->getFont()->setItalic(true);
        $hoja1->getStyle('A1')->getFont()->setSize('9');

        // Combinar celdas
        $hoja1->mergeCells('B1:BJ1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('B1', 'GOBIERNO DEL ESTADO DE OAXACA');

        $hoja1->mergeCells('B2:BJ2');
        $hoja1->setCellValue('B2', 'SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA');

        $hoja1->mergeCells('B3:BJ3');
        $hoja1->setCellValue('B3', 'PRESUPUESTO DE EGRESOS DE ' . APP_EJERCICIO);

        $hoja1->mergeCells('B4:BJ4');
        $hoja1->setCellValue('B4', 'REPORTE DE OBRA PÚBLICA POR CLAVE PRESUPUESTAL');
        //Se alinean al centro
        $hoja1->getStyle('B1:B4')->getAlignment()->setHorizontal('center');
        //Se  ponen en negritas
        $hoja1->getStyle('B1:B4')->getFont()->setBold(true);

        $this->titulos_columnas = [
            'CLAVE UR',
            'UNIDAD RESPONSABLE',
            'CLAVE UE',
            'EJECUTORA',
            'TIPO',
            'FUENTE',
            'UR',
            'EJE',
            'NOMBRE EJE',
            'CLAVE PROGRAMA',
            'DESCRIPCIÓN PROGRAMA',
            'CLAVE SUBPROGRMA',
            'DESCRIPCIÓN SUBPROGRAMA',
            'CLAVE PROYECTO',
            'DESCRIPCIÓN PROYECTO',
            'OBRA/ACTIVIDAD',
            'CLAVE DE LA OBRA',
            'CLAVE VERTIENTE',
            'DESCRIPCIÓN VERTIENTE',
            'CLAVE REFERENTE',
            'DESCRIPCIÓN REFERENTE',
            'CLAVE CONCEPTO',
            'DESCRIPCIÓN CONCEPTO',
            'No. DE PPI',
            'NOMBRE',
            'OBRA/ACCIÓN',
            'No OBRA SSP',
            'COBERTURA',
            'CLAVE R',
            'REGION',
            'CLAVE D',
            'DISTRITO',
            'CLAVE M',
            'MUNICIPIO',
            'CLAVE L',
            'LOCALIDAD',
            'MUJERES',
            'HOMBRES',
            'CLAVE PRESUPUESTAL',
            'P. ESPECIFICA',
            'CLAVE CVEFIN',
            'CFI_NOMBRE',
            'AÑO RECURSO',
            'AUTORIZADO',
            'AMPLIACIONES',
            'REDUCCIONES',
            'MODIFICADO',
            'COMPROMETIDO',
            'DEVENGADO',
            'MINISTRADO',
            'PAGADO',
            'RETENCIONES',
            'EJERCIDO',
            'POR EJERCER',
            'ANTICIPO',
            'AMORTIZACION DE ANTICIPOS',
            'ANTICIPO POR AMORTIZAR',
            'AVANCE FINANCIERO',
            'ID OBRA',
            'OFICIO AUT.',
            'OR_FECHA OFICIO AUT',
            'FECHA DE IMPORTACIÓN',
        ];

        $this->spreadsheet->getActiveSheet()->fromArray($this->titulos_columnas, null, 'A5');
        //Se cambio el color de la fuente
        $hoja1->getStyle('A5:BJ5')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A5:BJ5')->getFont()->setBold(true);
        //Se ajusta el texto
        $hoja1->getStyle('A5:BJ5')->getAlignment()->setWrapText(true);
        //Se pintan las celdas
        $hoja1->getStyle('A5:BJ5')->getFill()->setFillType('solid')->getStartColor()->setRGB('000000');
        //Se alinea al centro
        //Se cambia el tamaño de la fuente
        $hoja1->getStyle('A5:BJ5')->getFont()->setSize('9');

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(8);
        $hoja1->getColumnDimension('B')->setWidth(45);
        $hoja1->getColumnDimension('C')->setWidth(8);
        $hoja1->getColumnDimension('D')->setWidth(45);
        $hoja1->getColumnDimension('E')->setWidth(5);
        $hoja1->getColumnDimension('F')->setWidth(45);
        $hoja1->getColumnDimension('G')->setWidth(8);
        $hoja1->getColumnDimension('H')->setWidth(8);
        $hoja1->getColumnDimension('I')->setWidth(45);
        $hoja1->getColumnDimension('J')->setWidth(12);
        $hoja1->getColumnDimension('K')->setWidth(45);
        $hoja1->getColumnDimension('L')->setWidth(12);
        $hoja1->getColumnDimension('M')->setWidth(45);
        $hoja1->getColumnDimension('N')->setWidth(12);
        $hoja1->getColumnDimension('O')->setWidth(45);
        $hoja1->getColumnDimension('P')->setWidth(14);
        $hoja1->getColumnDimension('Q')->setWidth(25);
        $hoja1->getColumnDimension('R')->setWidth(14);
        $hoja1->getColumnDimension('S')->setWidth(20);
        $hoja1->getColumnDimension('T')->setWidth(19);
        $hoja1->getColumnDimension('U')->setWidth(45);
        $hoja1->getColumnDimension('V')->setWidth(24);
        $hoja1->getColumnDimension('W')->setWidth(18);
        $hoja1->getColumnDimension('X')->setWidth(20);
        $hoja1->getColumnDimension('Y')->setWidth(50);
        $hoja1->getColumnDimension('Z')->setWidth(15);
        $hoja1->getColumnDimension('AA')->setWidth(25);
        $hoja1->getColumnDimension('AB')->setWidth(12);
        $hoja1->getColumnDimension('AC')->setWidth(8);
        $hoja1->getColumnDimension('AD')->setWidth(18);
        $hoja1->getColumnDimension('AE')->setWidth(8);
        $hoja1->getColumnDimension('AF')->setWidth(18);
        $hoja1->getColumnDimension('AG')->setWidth(8);
        $hoja1->getColumnDimension('AH')->setWidth(18);
        $hoja1->getColumnDimension('AI')->setWidth(8);
        $hoja1->getColumnDimension('AJ')->setWidth(18);
        $hoja1->getColumnDimension('AK')->setWidth(10);
        $hoja1->getColumnDimension('AL')->setWidth(10);
        $hoja1->getColumnDimension('AM')->setWidth(48);
        $hoja1->getColumnDimension('AN')->setWidth(48);
        $hoja1->getColumnDimension('AO')->setWidth(14);
        $hoja1->getColumnDimension('AP')->setWidth(48);
        $hoja1->getColumnDimension('AQ')->setWidth(15);
        $hoja1->getColumnDimension('AR')->setWidth(15);
        $hoja1->getColumnDimension('AS')->setWidth(15);
        $hoja1->getColumnDimension('AT')->setWidth(15);
        $hoja1->getColumnDimension('AU')->setWidth(15);
        $hoja1->getColumnDimension('AV')->setWidth(15);
        $hoja1->getColumnDimension('AW')->setWidth(15);
        $hoja1->getColumnDimension('AX')->setWidth(15);
        $hoja1->getColumnDimension('AY')->setWidth(15);
        $hoja1->getColumnDimension('AZ')->setWidth(15);
        $hoja1->getColumnDimension('BA')->setWidth(15);
        $hoja1->getColumnDimension('BB')->setWidth(15);
        $hoja1->getColumnDimension('BC')->setWidth(15);
        $hoja1->getColumnDimension('BD')->setWidth(28);
        $hoja1->getColumnDimension('BD')->setWidth(28);
        $hoja1->getColumnDimension('BE')->setWidth(25);
        $hoja1->getColumnDimension('BF')->setWidth(20);
        $hoja1->getColumnDimension('BG')->setWidth(10);
        $hoja1->getColumnDimension('BH')->setWidth(25);
        $hoja1->getColumnDimension('BI')->setWidth(25);
        $hoja1->getColumnDimension('BJ')->setWidth(28);

        //Se ajusta el ancho de la fila 5
        $hoja1->getRowDimension('5')->setRowHeight(15);
    }

    public function consultaHoja1()
    {

        $gpo = $_SESSION[_S]['clasAdm-gpo'];
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $prg = ((isset($_POST['prg']) && $_POST['prg'] != '') ? $_POST['prg'] : '');
        $spr = ((isset($_POST['spr']) && $_POST['spr'] != '') ? $_POST['spr'] : '');
        $py = ((isset($_POST['py']) && $_POST['py'] != '') ? $_POST['py'] : '');
        $oa = ((isset($_POST['oa']) && $_POST['oa'] != '') ? $_POST['oa'] : '');
        $wh = '';
        $wh2 = '';
        $wh3 = '';
        $wh4 = '';
        $wh5 = '';
        $parametros = [];
        $param3 = [];
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue = $_SESSION[_S]['ue_id'];
                $wh .= 'gpo_id =:gpo_id  AND ur_id=:ur_id ';
                $wh5 .= 'gpo_id =:gpo_ids  AND ur_id=:ur_ids ';
                $wh2 .= 'clc.gpo_id =' . $gpo . ' AND clc.ur_id=' . $ur . ' ';
                $wh3 .= 'gpo_id =:gpo AND ur_id=:ur AND ue_id=:ue';

                $param3[':gpo'] = $gpo;
                $param3[':ur'] = $ur;
                $param3[':ue'] = $ue;

                break;
            default:
                if (! isset($_POST['todas']) || $_POST['todas'] == 0) {
                    $wh .= ' gpo_id =:gpo_id AND ur_id=:ur_id ';
                    $wh5 .= ' gpo_id =:gpo_ids AND ur_id=:ur_ids ';
                    $wh2 .= ' clc.gpo_id =' . $gpo . ' AND clc.ur_id=' . $ur . ' ';
                    $wh3 .= ' gpo_id =:gpo AND ur_id=:ur';

                    $param3[':gpo'] = $gpo;
                    $param3[':ur'] = $ur;
                }
                break;
        }

        if (! (isset($wh3) && $wh3 != '')) {
            $validador = 0;
            if (isset($prg) && $prg != '') {
                switch ($validador) {
                    case 0:
                        $wh3 .= ' prg_id=:prg ';
                        $validador = 1;
                        $param3[':prg'] = $prg;
                        break;
                }
            }
            if (isset($spr) && $spr != '') {
                switch ($validador) {
                    case 0:
                        $wh3 .= ' AND spr_id=:spr ';
                        $validador = 1;

                        break;
                    default:
                        $wh3 .= ' AND spr_id=:spr ';
                        break;

                        $param3[':spr'] = $spr;
                }
            }
            if (isset($py) && $py != '') {
                switch ($validador) {
                    case 0:
                        $wh3 .= ' py_id=:py  ';
                        $validador = 1;
                        break;
                    default:
                        $wh3 .= ' AND py_id=:py ';
                        break;

                        $param3[':py'] = $py;
                }
            }
            if (isset($oa) && $oa != '') {
                switch ($validador) {
                    case 0:
                        $wh3 .= ' oa_id=:oa ';
                        $validador = 1;
                        break;
                    default:
                        $wh3 .= ' AND oa_id=:oa ';
                        break;
                        $param3[':oa'] = $oa;
                }
            }
            if (isset($wh3) && $wh3 != '') {
                $wh4 .= ' AND ' . $wh3 . ' ';
            }
        } else {
            $wh3 .= ((isset($prg) && $prg != '') ? ' AND prg_id=:prg ' : '');
            $wh3 .= ((isset($spr) && $spr != '') ? ' AND spr_id=:spr ' : '');
            $wh3 .= ((isset($py) && $py != '') ? ' AND py_id=:py ' : '');
            $wh3 .= ((isset($oa) && $oa != '') ? ' AND oa_id=:oa ' : '');
            $param3[':prg'] = $prg;
            $param3[':spr'] = $spr;
            $param3[':py'] = $py;
            $param3[':oa'] = $oa;
        }

        $valida = ((isset($_POST['todas']) && $_POST['todas'] == 1) ? ' where toa_id=:toa_id ' . $wh4 . ' ' : ((! isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas'] == 0)) ? ' where ' . $wh3 . ' and toa_id=:toa_id  ' : ''));
        $where3 = (($wh3 == '') ? ' toa_id=:toa_id  ' : ' ' . $wh3 . ' AND toa_id=:toa_id ');
        $where = (($wh == '') ? ' ' : ' WHERE ' . $wh . ' ');
        $where2 = (($wh5 == '') ? ' ' : ' AND ' . $wh5 . ' ');

        if ($wh3 != ' ') {
            $parametros = $param3;
            $parametros[':toa_id'] = 2;
        }

        if ($where != ' ' && $where != '') {
            $parametros[':gpo_id'] = $gpo;
            $parametros[':ur_id'] = $ur;
        }

        if ($where2 != ' ') {
            $parametros[':gpo_ids'] = $gpo;
            $parametros[':ur_ids'] = $ur;
        }
        if ($valida != ' ') {
            $parametros[':toa_id'] = 2;
        }

        $query = '
        SELECT
            gpo_id,
            ur_id,
            ur_nombre,
            ue_nombre,
            prg_id,
            spr_id,
            py_id,
            oa_id,
            ue_id,
            oa_nombre,
            oa_hombres,
            oa_mujeres,
            oa_anticipo,
            retenciones,
            oa_' . APP_EJERCICIO_ANTERIOR . ',
            oa.usr_fecha,
            oa.oa_origen,
            reg_id,
            dto_id,
            mun_id,
            loc_id,
            reg_nombre,
            dto_nombre,
            mun_nombre,
            loc_nombre,
            cff_id,
            fte_id,
            sft_id,
            ori_id,
            sor_id,
            cfi_id,
            cfi_anio,
            cfi_clave,
            cfi_nombre,
            cfi_ejercicio,
            fte_nombre,
            VIGENTE,
            COMPROM,
            EJERCID,
            DEVENGADO,
            MINISTRADO,
            PAGADO,
            XDEVENGAR,
            0 AS DISPONIBLE,
            XEJERCID,
            ORIGINA,
            AMPLIACIONES,
            REDUCCIONES,
            pre_claveCompleta,
            pe_nombre,
            ore_OficioAut,
            ore_fechaOficioAut,
            oa_amortizacion,
            oa_numObra,
            (( EJERCID )/( VIGENTE )* 100 ) obr_avanceFinanciero,
            toa_id,
            ore_idMml,
            epg_id,
            ore_idObra,
            prg_nombre,
            spr_nombre,
            py_nombre,
            eje_id,
	        eje_nombre
        FROM
            (
            SELECT
                gpo_id,
                ur_id,
                epg_id,
                prg_id,
                spr_id,
                py_id,
                oa_id,
                ue_id,
                prg_nombre,
                spr_nombre,
                py_nombre,
                oa_nombre,
                oa_origen,
                oa_hombres,
                oa_mujeres,
                ( oa_anticipo + oa2.oa_anticipo' . APP_EJERCICIO_ANTERIOR . ' ) AS oa_anticipo,
                oa_amortizacion,
                reg_id,
                dto_id,
                ug.mun_id,
                loc_id,
                reg_nombre,
                dto_nombre,
                mun_nombre,
                loc_nombre,
                oa_numObra,
                toa_id,
                oa2.oa_' . APP_EJERCICIO_ANTERIOR . ',
                oa2.usr_fecha,
                eje_id,
	            eje_nombre
            FROM
                cat_ObraActividad oa2
                INNER JOIN cat_Programa USING ( gpo_id, ur_id, prg_id )
                INNER JOIN cat_Programas cp ON cp.prgs_id = prg_id
                LEFT JOIN vin_Programa USING ( prgs_id )
                LEFT JOIN enc_PedObjetivo ep USING ( epo_id )
                LEFT JOIN cat_Eje ce USING ( eje_id )
                INNER JOIN cat_Subprograma USING ( gpo_id, ur_id, prg_id, spr_id )
                INNER JOIN cat_Proyecto USING ( gpo_id, ur_id, prg_id, spr_id, py_id )
                INNER JOIN enc_UbicacionGeografica ug USING ( eug_id )
                INNER JOIN cat_Region USING ( reg_id )
                INNER JOIN cat_Distrito USING ( reg_id, dto_id )
                INNER JOIN cat_Municipio USING ( reg_id, dto_id, mun_id )
                INNER JOIN cat_Localidad USING ( reg_id, dto_id, mun_id, loc_id )
                INNER JOIN enc_Programatico USING ( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
            WHERE
                ' . $where3 . '
            ) oa
            INNER JOIN (
            SELECT
                gpo_id,
                ur_id,
                epg_id,
                prg_id,
                spr_id,
                py_id,
                oa_id,
                pg_id,
                pe_id,
                ecf_id,
                tcp_id,
                SUM( pre_vigente ) VIGENTE,
                SUM( pre_comprometido ) COMPROM,
                SUM( pre_ejercido ) EJERCID,
                SUM( pre_devengado ) DEVENGADO,
                SUM( pre_ministrado ) MINISTRADO,
                SUM( pre_pagado ) PAGADO,
                SUM( pre_vigente ) - SUM( pre_ejercido ) XEJERCID,
                SUM( pre_vigente ) - SUM( pre_devengado ) XDEVENGAR,
                SUM( pre_aprobado ) ORIGINA,
                SUM( pre_ampliacion ) AMPLIACIONES,
                SUM( pre_reduccion ) REDUCCIONES,
                pre_claveCompleta
            FROM
                presupuesto
                JOIN presupuestoVigente AS vig USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoEjercido AS eje USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoMinistrado min USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoComprometido AS com USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoDevengado AS dev USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoPagado AS pag USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                JOIN presupuestoAprobado AS apr USING ( gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id )
                ' . $where . '
            GROUP BY
                gpo_id,
                ur_id,
                ue_id,
                epg_id,
                pg_id,
                pe_id,
                ecf_id,
                tcp_id
            ) p USING ( gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id )
            INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
            INNER JOIN cat_UnidadEjecutora USING ( gpo_id, ur_id, ue_id )
            INNER JOIN cat_PartidaEspecifica USING ( pg_id, pe_id )
            INNER JOIN poa_OrigenRecurso USING ( gpo_id, ur_id, epg_id, ecf_id )
            INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
            INNER JOIN '. BD_SINPRES .'.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            INNER JOIN '. BD_SINPRES .'.cat_Fuente USING ( fte_id )
            LEFT JOIN (
            SELECT
                gpo_id,
                ur_id,
                epg_id,
                SUM( retenciones ) AS retenciones

            FROM
                (
                SELECT
                    gpo_id,
                    ur_id,
                    clc_id,
                    0 AS retenciones,
                    o.epg_id

                FROM
                    scp_Clc c
                    INNER JOIN cat_ObraClc o USING ( gpo_id, ur_id, clc_id )
                    INNER JOIN scp_Presupuestal USING ( gpo_id, ur_id, clc_id )
                WHERE
                    ecl_id NOT IN ( 1, 2, 19, 20, 21 )
                    AND tcl_id IN ( "F", "G", "H" )
                    ' . $where2 . '
                GROUP BY
                    gpo_id,
                    ur_id,
                    clc_id UNION ALL
                SELECT
                    gpo_id,
                    ur_id,
                    clc_id,
                    SUM( ncc_retencion ) retenciones,
                    o.epg_id

                FROM
                    scp_Clc c
                    INNER JOIN cat_ObraClc o USING ( gpo_id, ur_id, clc_id )
                    INNER JOIN scp_Contable USING ( gpo_id, ur_id, clc_id )
                WHERE
                    ecl_id NOT IN ( 1, 2, 19, 20, 21 )
                    AND tcl_id IN ( "F", "G", "H" )
                    ' . $where2 . '
                GROUP BY
                    gpo_id,
                    ur_id,
                    clc_id
                ) b
            GROUP BY
                gpo_id,
                ur_id,
                b.epg_id
            ) afec USING ( gpo_id, ur_id, epg_id )
            ' . $valida . '
        ORDER BY
            epg_id';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($query);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $conexionSIIP = new AccesoDatos();
        $conexionSIIP->conectar('siip_sefip');

        $datos = [];
        $reg_id = '';
        $dto_id = '';
        $mun_id = '';
        $loc_id = '';
        $cobertura = '';
        $saldoXamortizar = 0;

        while ($value = $conexion->extraer()) {
            if ($value['reg_id'] == 9) {
                $cobertura = 'ESTATAL';
                $reg = $dto = $mun = $loc = '';
            } elseif ($value['dto_id'] >= 91) {
                $cobertura = 'REGIONAL';
                $reg = $value['reg_nombre'];
                $dto = $mre = $mun = $loc = '';
                $reg_id = $value['reg_id'];
            } elseif ($value['mun_id'] >= 901) {
                $cobertura = 'DISTRITAL';
                $reg = $value['reg_nombre'];
                $dto = $value['dto_nombre'];
                $mre = $mun = $loc = '';
                $reg_id = $value['reg_id'];
                $dto_id = $value['dto_id'];
            } elseif ($value['loc_id'] == 0 || $value['loc_id'] == 999) {
                $cobertura = 'MUNICIPAL';
                $reg = $value['reg_nombre'];
                $dto = $value['dto_nombre'];
                $mre = '';
                $mun = $value['mun_nombre'];
                $loc = '';
                $reg_id = $value['reg_id'];
                $dto_id = $value['dto_id'];
                $mun_id = $value['mun_id'];
            } else {
                $cobertura = 'LOCALIDAD';
                $reg = $value['reg_nombre'];
                $dto = $value['dto_nombre'];
                $mre = '';
                $mun = $value['mun_nombre'];
                $loc = $value['loc_nombre'];
                $reg_id = $value['reg_id'];
                $dto_id = $value['dto_id'];
                $mun_id = $value['mun_id'];
                $loc_id = $value['loc_id'];
            }

            $saldoXamortizar = $value['oa_anticipo'] - ((float) $value['oa_amortizacion']);

            //------------Datos para establecer la conexion al SAI a la base de datos de SQL-------
            $ObraID = (empty($value['ore_idObra']) ? '' : ($value['ore_idObra'] > 0 ? $value['ore_idObra'] : ''));

            if ($ObraID == '') {

                $ume = [
                    'claveVertiente'       => '',
                    'vertienteConcepto'    => '',
                    'claveReferente'       => '',
                    'referenteConcepto'    => '',
                    'claveClasificacion'   => '',
                    'clasifiacionConcepto' => '',
                    'obraaccion'           => '',
                ];
            } else {
                //Consulta 2019, la consulta se hace directo a las vistas.

                $parametro = [$ObraID];
                $SQL =
                    'SELECT
                        ev.claveVertiente AS claveVertiente,         ev .vertiente AS vertienteConcepto,
                        ev.claveReferente AS claveReferente,         ev.referente AS referenteConcepto,
                        ev.claveClasificacion AS claveClasificacion, ev.clasificacion AS clasificacionConcepto,
                        ev.clasificacionTipoActividad AS obraaccion
                    FROM consulta.vw_EtapaAutorizadaSEFIP ea
                        LEFT JOIN consulta.vw_EncuadreVertienteSEFIP ev on (ea.idEncuadreVertiente = ev.idEncuadreVertiente)
                    WHERE ea.folioProgramacion = ?;';
                $conexionSIIP->setSql($SQL);
                $conexionSIIP->consultar($parametro);
                $ume = $conexionSIIP->extraer();
            }

            $dato = [
                'ur_id'                 => $value['gpo_id'] . $value['ur_id'],
                'ur_nombre'             => $value['ur_nombre'],
                'ue_id'                 => $value['ue_id'],
                'ue_nombre'             => $value['ue_nombre'],
                'toa_id'                => $value['toa_id'],
                'fte_nombre'            => $value['fte_nombre'],
                'ur_ids'                => $value['gpo_id'] . $value['ur_id'],
                'eje_id'                => $value['eje_id'],
                'eje_nombre'            => $value['eje_nombre'],
                'prg_id'                => $value['prg_id'],
                'prg_nombre'            => $value['prg_nombre'],
                'spr_id'                => $value['spr_id'],
                'spr_nombre'            => $value['spr_nombre'],
                'py_id'                 => $value['py_id'],
                'py_nombre'             => $value['py_nombre'],
                'oa_id'                 => $value['oa_id'],
                'oa_2025'               => $value['oa_2025'],
                'claveVertiente'        => $ume['claveVertiente'],
                'vertienteConcepto'     => $ume['vertienteConcepto'],
                'claveReferente'        => $ume['claveReferente'],
                'referenteConcepto'     => $ume['referenteConcepto'],
                'claveClasificacion'    => $ume['claveClasificacion'],
                'clasificacionConcepto' => $ume['clasificacionConcepto'],
                'ore_idMml'             => $value['ore_idMml'],
                'oa_nombre'             => $value['oa_nombre'],
                'obraaccion'            => $ume['obraaccion'],
                'oa_numObra'            => $value['oa_numObra'],
                'cobertura'             => $cobertura,
                'reg_id'                => $reg_id,
                'reg'                   => $reg,
                'dto_id'                => $dto_id,
                'dto'                   => $dto,
                'mun_id'                => $mun_id,
                'mun'                   => $mun,
                'loc_id'                => $loc_id,
                'loc'                   => $loc,
                'oa_mujeres'            => $value['oa_mujeres'],
                'oa_hombres'            => $value['oa_hombres'],
                'pre_claveCompleta'     => $value['pre_claveCompleta'],
                'pe_nombre'             => $value['pe_nombre'],
                'cfi_clave'             => $value['cfi_clave'],
                'cfi_nombre'            => $value['cfi_nombre'],
                'cfi_ejercicio'         => $value['cfi_ejercicio'],
                'ORIGINA'               => $value['ORIGINA'],
                'AMPLIACIONES'          => $value['AMPLIACIONES'],
                'REDUCCIONES'           => $value['REDUCCIONES'],
                'VIGENTE'               => $value['VIGENTE'],
                'COMPROM'               => $value['COMPROM'],
                'DEVENGADO'             => $value['DEVENGADO'],
                'MINISTRADO'            => $value['MINISTRADO'],
                'PAGADO'                => $value['PAGADO'],
                'retenciones'           => $value['retenciones'],
                'EJERCID'               => $value['EJERCID'],
                'XEJERCID'              => $value['XEJERCID'],
                'oa_anticipo'           => $value['oa_anticipo'],
                'oa_amortizaco'         => $value['oa_amortizacion'],
                'saldoXamortizar'       => $saldoXamortizar,
                'obr_avanceFinanciero'  => $value['obr_avanceFinanciero'],
                'ore_idObra'            => $value['ore_idObra'],
                'ore_OficioAut'         => $value['ore_OficioAut'],
                'ore_fechaOficioAut'    => $value['ore_fechaOficioAut'],
                'usr_fecha'             => $value['usr_fecha'],
            ];
            array_push($datos, $dato);
        }

        $conexionSIIP->desconectar();

        return $datos;
    }
}

$exp_obraPub = new exp_ObraPub();
