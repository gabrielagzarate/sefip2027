<?php

session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);
ini_set("memory_limit", '1G');

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida.
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';

use PhpOffice\PhpSpreadsheet\Style\Alignment;

class exp_ObraPubReembolso extends Phpspreadsheet
{

    protected $titulos_columnas;

    public function __construct()
    {
        parent::__construct();
        $this->hoja1();
        $this->spreadsheet->setActiveSheetIndex(0);
        $this->output_web_browser("ObraCvePresupuestalReembolso.xlsx");
    }

    public function hoja1()
    {

        $this->encabezadoHoja1();

        $datos = $this->consultaHoja1();
        $this->spreadsheet->getActiveSheet()->fromArrayDataExplicit($datos, null, 'A6', false, ['A']);

        $tam = count($datos) + 5;
        $this->spreadsheet->getActiveSheet()->getStyle('A6:Q' . $tam)->getFont()->setSize('8');
        $this->spreadsheet->getActiveSheet()->getStyle('A6:Q' . $tam)->getAlignment()->setWrapText(true);
        $this->spreadsheet->getActiveSheet()->getStyle('A6:Q' . $tam)->getBorders()->getOutline()->setBorderStyle('thin');



        $this->spreadsheet->getActiveSheet()->getStyle('D6:D' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('E6:E' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('F6:F' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('G6:G' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('H6:H' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('I6:I' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('J6:J' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('K6:K' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('L6:L' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('M6:M' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('N6:N' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('O6:O' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('P6:P' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('Q6:Q' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    public function encabezadoHoja1()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        $hoja1->setTitle('ObraCvePresupuestalReembolso');

        $hoja1->setCellValue('A1', 'Fecha: ' . date("Y-m-d H:i:s"));
        $hoja1->getStyle('A1')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A1')->getAlignment()->setVertical('top');
        $hoja1->getStyle('A1')->getFont()->setBold(true);
        $hoja1->getStyle('A1')->getFont()->setItalic(true);
        $hoja1->getStyle('A1')->getFont()->setSize('9');

        // Combinar celdas
        $hoja1->mergeCells('B1:Q1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('B1', 'GOBIERNO DEL ESTADO DE OAXACA');

        $hoja1->mergeCells('B2:Q2');
        $hoja1->setCellValue('B2', 'SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA');

        $hoja1->mergeCells('B3:Q3');
        $hoja1->setCellValue('B3', 'PRESUPUESTO DE EGRESOS DE ' . APP_EJERCICIO);

        $hoja1->mergeCells('B4:Q4');
        $hoja1->setCellValue('B4', 'REPORTE DE OBRA PÚBLICA POR CLAVE PRESUPUESTAL REEMBOLSOS');
        //Se alinean al centro
        $hoja1->getStyle('B1:B4')->getAlignment()->setHorizontal('center');
        //Se  ponen en negritas
        $hoja1->getStyle('B1:B4')->getFont()->setBold(true);

        $this->titulos_columnas = [
            'NOMBRE',
            'CLAVE PRESUPUESTAL',
            'CLAVE CVEFIN',
            'AUTORIZADO',
            'AMPLIACIONES',
            'REDUCCIONES',
            'MODIFICADO',
            'COMPROMETIDO',
            'DEVENGADO',
            'MINISTRADO',
            'PAGADO',
            'EJERCIDO',
            'POR EJERCER',
            'ANTICIPO',
            'AMORTIZACION DE ANTICIPOS',
            'REEMBOLSOS',
            'ANTICIPO POR AMORTIZAR',
            
        ];

        $this->spreadsheet->getActiveSheet()->fromArray($this->titulos_columnas, null, 'A5');
        //Se cambio el color de la fuente
        $hoja1->getStyle('A5:Q5')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A5:Q5')->getFont()->setBold(true);
        //Se ajusta el texto
        $hoja1->getStyle('A5:Q5')->getAlignment()->setWrapText(true);
        //Se pintan las celdas
        $hoja1->getStyle('A5:Q5')->getFill()->setFillType('solid')->getStartColor()->setRGB('000000');
        //Se alinea al centro
        //Se cambia el tamaño de la fuente
        $hoja1->getStyle('A5:Q5')->getFont()->setSize('9');

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(50);
        $hoja1->getColumnDimension('B')->setWidth(30);
        $hoja1->getColumnDimension('C')->setWidth(20);
        $hoja1->getColumnDimension('D')->setWidth(20);
        $hoja1->getColumnDimension('E')->setWidth(20);
        $hoja1->getColumnDimension('F')->setWidth(20);
        $hoja1->getColumnDimension('G')->setWidth(20);
        $hoja1->getColumnDimension('H')->setWidth(20);
        $hoja1->getColumnDimension('I')->setWidth(20);
        $hoja1->getColumnDimension('J')->setWidth(20);
        $hoja1->getColumnDimension('K')->setWidth(20);
        $hoja1->getColumnDimension('L')->setWidth(20);
        $hoja1->getColumnDimension('M')->setWidth(20);
        $hoja1->getColumnDimension('N')->setWidth(20);
        $hoja1->getColumnDimension('O')->setWidth(20);
        $hoja1->getColumnDimension('P')->setWidth(20);
        $hoja1->getColumnDimension('Q')->setWidth(20);

    }

    public function consultaHoja1()
    {

        $gpo        = $_SESSION[_S]['clasAdm-gpo'];
        $ur         = $_SESSION[_S]['clasAdm-ur'];
        $prg        = ((isset($_POST['prg']) && $_POST['prg'] != '') ? $_POST['prg'] : "");
        $spr        = ((isset($_POST['spr']) && $_POST['spr'] != '') ? $_POST['spr'] : "");
        $py         = ((isset($_POST['py']) && $_POST['py'] != '') ? $_POST['py'] : "");
        $oa         = ((isset($_POST['oa']) && $_POST['oa'] != '') ? $_POST['oa'] : "");
        $wh         = '';
        $wh2        = '';
        $wh3        = '';
        $wh4        = '';
        $wh5        = '';
        $parametros = [];
        $param3     = [];
        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue = $_SESSION[_S]['ue_id'];
                $wh .= 'gpo_id =:gpo_id  AND ur_id=:ur_id ';
                $wh5 .= 'gpo_id =:gpo_ids  AND ur_id=:ur_ids ';
                $wh2 .= 'clc.gpo_id =' . $gpo . ' AND clc.ur_id=' . $ur . ' ';
                $wh3 .= 'gpo_id =:gpo AND ur_id=:ur AND ue_id=:ue';

                $param3[':gpo'] = $gpo;
                $param3[':ur']  = $ur;
                $param3[':ue']  = $ue;

                break;
            default:
                if (! isset($_POST['todas']) || $_POST['todas'] == 0) {
                    $wh .= ' gpo_id =:gpo_id AND ur_id=:ur_id ';
                    $wh5 .= ' gpo_id =:gpo_ids AND ur_id=:ur_ids ';
                    $wh2 .= ' clc.gpo_id =' . $gpo . ' AND clc.ur_id=' . $ur . ' ';
                    $wh3 .= ' gpo_id =:gpo AND ur_id=:ur';

                    $param3[':gpo'] = $gpo;
                    $param3[':ur']  = $ur;
                }
                break;
        }

        if (! (isset($wh3) && $wh3 != '')) {
            $validador = 0;
            if (isset($prg) && $prg != '') {
                switch ($validador) {
                    case 0:
                        $wh3 .= ' prg_id=:prg ';
                        $validador      = 1;
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
            $wh3 .= ((isset($prg) && $prg != '') ? ' AND prg_id=:prg ' : "");
            $wh3 .= ((isset($spr) && $spr != '') ? ' AND spr_id=:spr ' : "");
            $wh3 .= ((isset($py) && $py != '') ? ' AND py_id=:py ' : "");
            $wh3 .= ((isset($oa) && $oa != '') ? ' AND oa_id=:oa ' : "");
            $param3[':prg'] = $prg;
            $param3[':spr'] = $spr;
            $param3[':py']  = $py;
            $param3[':oa']  = $oa;
        }

        $valida = ((isset($_POST['todas']) && $_POST['todas'] == 1) ? ' where toa_id=:toa_id ' . $wh4 . ' ' : ((! isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas'] == 0)) ? ' where ' . $wh3 . ' and toa_id=:toa_id  ' : ""));
        $where3 = (($wh3 == '') ? ' toa_id=:toa_id  ' : ' ' . $wh3 . ' AND toa_id=:toa_id ');
        $where  = (($wh == '') ? ' ' : ' WHERE ' . $wh . ' ');
        $where2 = (($wh5 == '') ? ' ' : ' AND ' . $wh5 . ' ');

        if ($wh3 != " ") {
            $parametros            = $param3;
            $parametros[':toa_id'] = 2;
        }

        if ($where != " " && $where != "") {
            $parametros[':gpo_id'] = $gpo;
            $parametros[':ur_id']  = $ur;
        }

        if ($where2 != " ") {
            $parametros[':gpo_ids'] = $gpo;
            $parametros[':ur_ids']  = $ur;
        }
        if ($valida != " ") {
            $parametros[':toa_id'] = 2;
        }

        $query = '
        SELECT
            oa_nombre, 
            pre_claveCompleta,
            cfi_clave,
            ORIGINA,
            AMPLIACIONES,
            REDUCCIONES,
            VIGENTE,
            COMPROM,
            DEVENGADO,
            MINISTRADO,
            PAGADO,
            EJERCID,
            XEJERCID,
            oa_anticipo + SUM(rao_importe) as oa_anticipo,
	        oa_amortizacion,
	        SUM(rao_importe) as rao_importe,
           (oa_anticipo + SUM(rao_importe)) - oa_amortizacion - SUM(rao_importe) AS ANTICIPOXAMORT
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
            INNER JOIN scp_ReintegroAnticipoObra as r USING (gpo_id,ur_id,ue_id,epg_id,ecf_id)
            INNER JOIN cat_UnidadResponsable USING ( gpo_id, ur_id )
            INNER JOIN cat_UnidadEjecutora USING ( gpo_id, ur_id, ue_id )
            INNER JOIN cat_PartidaEspecifica USING ( pg_id, pe_id )
            INNER JOIN poa_OrigenRecurso USING ( gpo_id, ur_id, epg_id, ecf_id )
            INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
            INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            INNER JOIN sinpres.cat_Fuente USING ( fte_id )
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
       
      
        $datos = array();
        while ($value = $conexion->extraer()) {
            array_push($datos, $value);
        }
            return $datos;


    }

}

$exp_obraPub = new exp_ObraPubReembolso();
