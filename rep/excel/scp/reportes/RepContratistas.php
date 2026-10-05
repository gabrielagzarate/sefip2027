<?php

session_start();
ini_set('memory_limit', '1G');
ini_set('max_execution_time', 0);

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida.
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';

class exp_ObraPub extends Phpspreadsheet
{
    public function __construct()
    {
        parent::__construct();

        $this->hoja2();

        //Se activa la hoja 1 como principal
        $this->spreadsheet->setActiveSheetIndex(0);

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser('Rep_Contratistas.xlsx');
    }

    //Función que genera la hoja 1

    public function hoja2()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 2
        $hoja2 = $this->spreadsheet->setActiveSheetIndex(0);

        $datos = $this->consultaHoja2();

        //Se añade a la hoja el arreglo con los datos obtenidos
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $datos, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A6' // Rango de celda o celda en donde deseamos que comience a pintar
            );

        $tam = count($datos) + 5;

        //Estilo para los datos añadidos
        $hoja2->getStyle('A6:F' . $tam)->getFont()->setSize('8');
        $hoja2->getStyle('A6:F' . $tam)->getAlignment()->setWrapText(true);
        $hoja2->getStyle('A6:F' . $tam)->getBorders()->getOutline()->setBorderStyle('thin');

    }

    public function consultaHoja2()
    {
        $hoja2 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja2->setTitle('Contratistas');

        //Se agregan los encabezados
        $hoja2->setCellValue('A1', 'Fecha: ' . date('Y-m-d H:i:s'));
        $hoja2->getStyle('A1')->getAlignment()->setHorizontal('center');
        $hoja2->getStyle('A1')->getAlignment()->setVertical('top');
        $hoja2->getStyle('A1')->getFont()->setBold(true);
        $hoja2->getStyle('A1')->getFont()->setItalic(true);
        $hoja2->getStyle('A1')->getFont()->setSize('9');

        // Combinar celdas
        $hoja2->mergeCells('B1:F1');
        //Establecemos la informacion a la celda combinada.
        $hoja2->setCellValue('B1', 'GOBIERNO DEL ESTADO DE OAXACA');

        $hoja2->mergeCells('B2:F2');
        $hoja2->setCellValue('B2', 'SECRETARIA DE FINANZAS DEL ESTADO DE OAXACA');

        $hoja2->mergeCells('B3:F3');
        $hoja2->setCellValue('B3', 'PRESUPUESTO DE EGRESOS DE ' . APP_EJERCICIO);

        $hoja2->mergeCells('B4:F4');
        $hoja2->setCellValue('B4', 'CONTRATISTAS');
        //Se alinean al centro
        $hoja2->getStyle('B1:B4')->getAlignment()->setHorizontal('center');
        //Se  ponen en negritas
        $hoja2->getStyle('B1:B4')->getFont()->setBold(true);

        $hoja2->setCellValue('A5', 'EJECUTORA');
        $hoja2->setCellValue('B5', 'UR');
        $hoja2->setCellValue('C5', 'No. DE PPI');
        $hoja2->setCellValue('D5', 'NOMBRE');
        $hoja2->setCellValue('E5', 'CONTRATISTA');
        $hoja2->setCellValue('F5', 'CLAVE PRESUPUESTAL');

        $hoja2->getStyle('A5:F5')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja2->getStyle('A5:F5')->getFont()->setBold(true);
        $hoja2->getStyle('A5:F5')->getFill()->setFillType('solid')->getStartColor()->setRGB('000000');
        //Se alinea al centro
        $hoja2->getStyle('A5:F5')->getAlignment()->setHorizontal('center');
        //Se cambia el tamaño de la fuente
        $hoja2->getStyle('A5:F5')->getFont()->setSize('9');

        $hoja2->getColumnDimension('A')->setWidth(48);
        $hoja2->getColumnDimension('B')->setWidth(10);
        $hoja2->getColumnDimension('C')->setWidth(22);
        $hoja2->getColumnDimension('D')->setWidth(48);
        $hoja2->getColumnDimension('E')->setWidth(45);
        $hoja2->getColumnDimension('F')->setWidth(30);

        $gpo = $_SESSION[_S]['clasAdm-gpo'];
        $ur = $_SESSION[_S]['clasAdm-ur'];
        $prg = ((isset($_POST['prg']) && $_POST['prg'] != '') ? $_POST['prg'] : '');
        $spr = ((isset($_POST['spr']) && $_POST['spr'] != '') ? $_POST['spr'] : '');
        $py = ((isset($_POST['py']) && $_POST['py'] != '') ? $_POST['py'] : '');
        $oa = ((isset($_POST['oa']) && $_POST['oa'] != '') ? $_POST['oa'] : '');
        $wh = '';
        $wh3 = '';
        $wh4 = '';
        $parametros = [];
        $param3 = [];

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
                $ue = $_SESSION[_S]['ue_id'];
                $wh .= 'gpo_id =:gpo AND ur_id=:ur ';
                $wh3 .= 'gpo_id =:gpo AND ur_id=:ur AND ue_id=:ue ';
                $param3[':gpo'] = $gpo;
                $param3[':ur'] = $ur;
                $param3[':ue'] = $ue;
                break;
            default:
                if (!isset($_POST['todas']) || $_POST['todas'] == 0) {
                    $wh .= ' gpo_id =:gpo AND ur_id=:ur ';
                    $wh3 .= ' gpo_id =:gpo AND ur_id=:ur ';

                    $param3[':gpo'] = $gpo;
                    $param3[':ur'] = $ur;
                }
                break;
        }

        if (!(isset($wh3) && $wh3 != '')) {
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
                        $wh3 .= ' py_id=:py ';
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

            if ((isset($prg) && $prg != '')) {
                $param3[':prg'] = $prg;
            }
            if ((isset($spr) && $spr != '')) {
                $param3[':spr'] = $spr;
            }
            if ((isset($py) && $py != '')) {
                $param3[':py'] = $py;
            }
            if ((isset($oa) && $oa != '')) {
                $param3[':oa'] = $oa;
            }

        }

        $where = (($wh == '') ? ' ' : ' WHERE ' . $wh . ' ');
        $where2 = (($wh == '') ? ' ' : ' AND ' . $wh . ' ');
        $where3 = (($wh3 == '') ? ' toa_id=:toa_id  ' : ' ' . $wh3 . ' AND toa_id=:toa_id ');
        $valida = ((isset($_POST['todas']) && $_POST['todas'] == 1) ? ' where toa_id=:toa_id ' . $wh4 . ' ' : ((!isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas'] == 0)) ? ' where ' . $wh3 . ' and toa_id=:toa_id ' : ''));

        if ($wh3 != ' ' || $wh3 != '') {
            $parametros = $param3;
            $parametros[':toa_id'] = 2;
        }

        if ($where != ' ' && $where != '') {
            $parametros[':gpo_id'] = $gpo;
            $parametros[':ur_id'] = $ur;
        }

        if ($valida != ' ' || $valida != '') {
            $parametros[':toa_id'] = 2;
        }

        $query = 'SELECT
                ue_nombre,
                gpo_id,
                ur_id,
                ue_id,
                ore_idMml,
                oa_nombre,
                nombre,
                pre_claveCompleta
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
                    oa_nombre,
                    oa_origen,
                    oa_amortizacion,
                    oa_numObra,
                    toa_id,
                    rfc_id,
                    CONCAT(
                        rfc_nombre,
                    CASE
                            TRIM(
                            IFNULL( rfc_paterno, "" ))
                            WHEN "" THEN
                            "" ELSE concat( " ", rfc_paterno )
                        END,
                    CASE
                            TRIM(
                            IFNULL( rfc_materno, "" ))
                            WHEN "" THEN
                            "" ELSE concat( " ", rfc_materno )
                        END
                        ) AS nombre
                    FROM
                        cat_ObraActividad oa2
                        INNER JOIN cat_Programa USING ( gpo_id, ur_id, prg_id )
                        INNER JOIN cat_Subprograma USING ( gpo_id, ur_id, prg_id, spr_id )
                        INNER JOIN cat_Proyecto USING ( gpo_id, ur_id, prg_id, spr_id, py_id )
                        INNER JOIN enc_Programatico USING ( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                        LEFT JOIN (
                        SELECT
                            gpo_id,
                            ur_id,
                            epg_id,
                            SUM( rao_importe ) AS Reembolso
                        FROM
                            scp_ReintegroAnticipoObra
                        WHERE era_id = 6
                            ' . $where2 . '
                        GROUP BY
                            gpo_id,
                            ur_id,
                            epg_id
                        ) R USING ( gpo_id, ur_id, epg_id )
                        LEFT JOIN ( SELECT gpo_id, ur_id, epg_id, ipu_id FROM enc_InversionObra GROUP BY gpo_id, ur_id, epg_id, ipu_id ) AS X USING ( gpo_id, ur_id, epg_id )
                        LEFT JOIN sinpres.cat_InversionPublica i USING ( ipu_id )
                        LEFT JOIN sinpres.cat_Proveedores prov USING ( rfc_id )
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
                    INNER JOIN poa_OrigenRecurso USING ( gpo_id, ur_id, epg_id, ecf_id )
                    INNER JOIN enc_ClaveFinanciamiento USING ( ecf_id )
                    INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                    ' . $valida . '
            ORDER BY
                epg_id;';

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($query);
        $conexion->consultar($parametros);
        $conexion->desconectar();

        $datos = [];

        while ($value = $conexion->extraer()) {

            $dato = [
                'ue_nombre'         => $value['ue_nombre'],
                'ue_id'             => $value['gpo_id'] . $value['ur_id'],
                'ore_idMml'         => ' ' . $value['ore_idMml'] . ' ',
                'oa_nombre'         => $value['oa_nombre'],
                'nombre'            => $value['nombre'],
                'pre_claveCompleta' => $value['pre_claveCompleta'],
            ];
            array_push($datos, $dato);

        }

        return $datos;
    }

}
$exp_obraPub = new exp_ObraPub();
