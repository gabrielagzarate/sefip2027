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

        $this->hoja1();

        //Se activa la hoja 1 como principal
        $this->spreadsheet->setActiveSheetIndex(0);

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser('Rep_Metas.xlsx');
    }

    //Función que genera la hoja 1
    public function hoja1()
    {

        $this->encabezadoHoja1();

        $datos = $this->consultaHoja1();

        //Se añade a la hoja el arreglo con los datos obtenidos
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $datos, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A2' // Rango de celda o celda en donde deseamos que comience a pintar
            );
    }

    public function encabezadoHoja1()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Metas');

        //Se agregan los encabezados

        $this->titulos_columnas = [
            'Id_UR',
            'Nombre de la Unidad Responsable',
            'Encuadre programatico',
            'PIPET',
            'CLAVE PROGRAMA',
            'DESCRIPCIÓN PROGRAMA',
            'CLAVE SUBPROGRAMA',
            'DESCRIPCIÓN SUBPROGRAMA',
            'CLAVE PROYECTO',
            'Proyecto',
            'Clave actividad',
            'Actividad',
            'pdi_id',
            'productoIntermedio',
            'ind_id',
            'indicador',
            'umi_id',
            'unidadMedidaIndicador',
            'umv_id',
            'unidadMedidaVariable',
            'mac_descripcion',
            'mac_valor',
            'mac_original',
            'mac_modificado',
            'mac_alcanzado',
            'mact_originalT1',
            'mact_modificadoT1',
            'mact_alcanzadoT1',
            'mact_originalT2',
            'mact_modificadoT2',
            'mact_alcanzadoT2',
            'mact_originalT3',
            'mact_modificadoT3',
            'mact_alcanzadoT3',
            'mact_originalT4',
            'mact_modificadoT4',
            'mact_alcanzadoT4',

        ];

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_columnas, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A1' // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se cambio el color de la fuente
        $hoja1->getStyle('A1:AK1')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:AK1')->getFont()->setBold(true);
        //Se ajusta el texto
        //  $hoja1->getStyle('A1:AK1')->getAlignment()->setWrapText(true);
        //Se pintan las celdas
        $hoja1->getStyle('A1:AK1')->getFill()->setFillType('solid')->getStartColor()->setRGB('4e954e');
        //Se alinea al centro
        //  $hoja1->getStyle('A5:BB5')->getAlignment()->setHorizontal('center');
        //Se cambia el tamaño de la fuente
        $hoja1->getStyle('A1:AK1')->getFont()->setSize('11');

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(8);
        $hoja1->getColumnDimension('B')->setWidth(35);
        $hoja1->getColumnDimension('C')->setWidth(22);
        $hoja1->getColumnDimension('D')->setWidth(20);
        $hoja1->getColumnDimension('E')->setWidth(10);
        $hoja1->getColumnDimension('F')->setWidth(22);
        $hoja1->getColumnDimension('G')->setWidth(10);
        $hoja1->getColumnDimension('H')->setWidth(22);
        $hoja1->getColumnDimension('I')->setWidth(10);
        $hoja1->getColumnDimension('J')->setWidth(22);
        $hoja1->getColumnDimension('K')->setWidth(10);
        $hoja1->getColumnDimension('L')->setWidth(30);
        $hoja1->getColumnDimension('M')->setWidth(10);
        $hoja1->getColumnDimension('N')->setWidth(26);
        $hoja1->getColumnDimension('O')->setWidth(8);
        $hoja1->getColumnDimension('P')->setWidth(25);
        $hoja1->getColumnDimension('Q')->setWidth(8);
        $hoja1->getColumnDimension('R')->setWidth(14);
        $hoja1->getColumnDimension('S')->setWidth(8);
        $hoja1->getColumnDimension('T')->setWidth(15);
        $hoja1->getColumnDimension('U')->setWidth(28);
        $hoja1->getColumnDimension('V')->setWidth(17);
        $hoja1->getColumnDimension('W')->setWidth(17);
        $hoja1->getColumnDimension('X')->setWidth(17);
        $hoja1->getColumnDimension('Y')->setWidth(17);
        $hoja1->getColumnDimension('Z')->setWidth(17);
        $hoja1->getColumnDimension('AA')->setWidth(17);
        $hoja1->getColumnDimension('AB')->setWidth(17);
        $hoja1->getColumnDimension('AC')->setWidth(17);
        $hoja1->getColumnDimension('AD')->setWidth(17);
        $hoja1->getColumnDimension('AE')->setWidth(17);
        $hoja1->getColumnDimension('AF')->setWidth(15);
        $hoja1->getColumnDimension('AG')->setWidth(17);
        $hoja1->getColumnDimension('AH')->setWidth(17);
        $hoja1->getColumnDimension('AI')->setWidth(15);
        $hoja1->getColumnDimension('AJ')->setWidth(17);
        $hoja1->getColumnDimension('AK')->setWidth(17);

    }

    public function consultaHoja1()
    {
        $hoja2 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja2->setTitle('Metas');

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

        $parametros = [];
        $param = [];
        $param1 = [];
        $param2 = [];
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

        $valida = ((isset($_POST['todas']) && $_POST['todas'] == 1) ? ' where toa_id=2 ' . $wh4 . ' ' : ((!isset($_POST['todas']) || (isset($_POST['todas']) && $_POST['todas'] == 0)) ? ' where ' . $wh3 . ' and toa_id=2 ' : ''));
        if ($valida != ' ' || $valida != '') {
            $parametros = $param3;
        }
        $query = 'SELECT
        CONCAT(gpo_id,ur_id) as ur_id,
        ur.ur_nombre,
        CONCAT(gpo_id,ur_id,ue_id,"-",prg_id,spr_id,py_id,oa_id) as enc_prog,
           ore_idMml AS PIPET,
           cpr.prg_id,
           cpr.prg_nombre,
           csp.spr_id,
           csp.spr_nombre,
           cp.py_id AS py_id,
           cp.py_nombre,
           ca.oa_id,
           ca.oa_nombre,
           cm.pii_id,
           pii.pdf_id,
           pdf.pdf_nombre AS productoFinal,
           pii.pdi_id,
           pdi.pdi_nombre AS productoIntermedio,
           pii.ind_id,
           cin.ind_nombre AS indicador,
           umi.umi_id,
           umi.umi_nombre AS unidadMedidaIndicador,
           umv.umv_id,
           umv.umv_nombre AS unidadMedidaVariable,
           cm.mac_descripcion,
           cm.mac_valor,
           cm.mac_original,
           cm.mac_modificado,
           cm.mac_alcanzado,
           T1.mact_original AS mact_originalT1,
           T1.mact_modificado AS mact_modificadoT1,
           T1.mact_alcanzado AS mact_alcanzadoT1,
           T2.mact_original AS mact_originalT2,
           T2.mact_modificado AS mact_modificadoT2,
           T2.mact_alcanzado AS mact_alcanzadoT2,
           T3.mact_original AS mact_originalT3,
           T3.mact_modificado AS mact_modificadoT3,
           T3.mact_alcanzado AS mact_alcanzadoT3,
           T4.mact_original AS mact_originalT4,
           T4.mact_modificado AS mact_modificadoT4,
           T4.mact_alcanzado AS mact_alcanzadoT4
       FROM
           (
           SELECT
               por.ore_idMml,
               por.epg_id
           FROM
               poa_OrigenRecurso por
           WHERE
               por.ore_idMml <> ""
           GROUP BY
               por.ore_idMml,
               por.epg_id
           ) AS ppi
           INNER JOIN enc_Programatico ep ON ppi.epg_id = ep.epg_id
           INNER JOIN cat_Programa cpr USING ( gpo_id, ur_id, prg_id )
           INNER JOIN cat_Subprograma csp USING ( gpo_id, ur_id, prg_id, spr_id )
           INNER JOIN cat_Proyecto cp USING ( gpo_id, ur_id, prg_id, spr_id, py_id )
           INNER JOIN cat_ObraActividad ca USING ( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
            INNER JOIN cat_UnidadEjecutora ue USING ( gpo_id, ur_id, ue_id )
           INNER JOIN cat_UnidadResponsable  ur USING ( gpo_id, ur_id )
           INNER JOIN cat_MetaActividad cm ON ep.epg_id = cm.epg_id
           INNER JOIN ( SELECT mact_original, mact_modificado, mact_alcanzado, epg_id, pii_id FROM cat_MetaActividadTrimestre WHERE tri_id = 1 ) T1 ON cm.epg_id = T1.epg_id
           AND cm.pii_id = T1.pii_id
           INNER JOIN ( SELECT mact_original, mact_modificado, mact_alcanzado, epg_id, pii_id FROM cat_MetaActividadTrimestre WHERE tri_id = 2 ) T2 ON cm.epg_id = T2.epg_id
           AND cm.pii_id = T2.pii_id
           INNER JOIN ( SELECT mact_original, mact_modificado, mact_alcanzado, epg_id, pii_id FROM cat_MetaActividadTrimestre WHERE tri_id = 3 ) T3 ON cm.epg_id = T3.epg_id
           AND cm.pii_id = T3.pii_id
           INNER JOIN ( SELECT mact_original, mact_modificado, mact_alcanzado, epg_id, pii_id FROM cat_MetaActividadTrimestre WHERE tri_id = 4 ) T4 ON cm.epg_id = T4.epg_id
           AND cm.pii_id = T4.pii_id
           INNER JOIN vin_ProductoIntermedioIndicador pii ON cm.pii_id = pii.pii_id
           INNER JOIN cat_ProductoFinal pdf ON pii.pdf_id = pdf.pdf_id
           INNER JOIN cat_ProductoIntermedio pdi ON pii.pdi_id = pdi.pdi_id
           INNER JOIN cat_Indicador cin ON pii.ind_id = cin.ind_id
           AND cin.nim_id = 4
           INNER JOIN cat_UnidadMedidaIndicador umi ON cin.umi_id = umi.umi_id
           INNER JOIN cat_UnidadMedidaVariable umv ON cin.umv_id = umv.umv_id ' . $valida;

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($query);

        $conexion->consultar($parametros);
        $conexion->desconectar();

        $datos = [];

        while ($value = $conexion->extraer()) {

            $dato = [
                'ur_id'                 => $value['ur_id'],
                'ur_nombre'             => $value['ur_nombre'],
                'enc_prog'              => $value['enc_prog'],
                'PIPET'                 => ' ' . $value['PIPET'],
                'prg_id'                => $value['prg_id'],
                'prg_nombre'            => $value['prg_nombre'],
                'spr_id'                => $value['spr_id'],
                'spr_nombre'            => $value['spr_nombre'],
                'py_id'                 => $value['py_id'],
                'py_nombre'             => $value['py_nombre'],
                'oa_id'                 => $value['oa_id'],
                'oa_nombre'             => $value['oa_nombre'],
                'pdi_id'                => $value['pdi_id'],
                'productoIntermedio'    => $value['productoIntermedio'],
                'ind_id'                => $value['ind_id'],
                'indicador'             => $value['indicador'],
                'umi_id'                => $value['umi_id'],
                'unidadMedidaIndicador' => $value['unidadMedidaIndicador'],
                'umv_id'                => $value['umv_id'],
                'unidadMedidaVariable'  => $value['unidadMedidaVariable'],
                'mac_descripcion'       => $value['mac_descripcion'],
                'mac_valor'             => $value['mac_valor'],
                'mac_original'          => $value['mac_original'],
                'mac_modificado'        => $value['mac_modificado'],
                'mac_alcanzado'         => $value['mac_alcanzado'],
                'mact_originalT1'       => $value['mact_originalT1'],
                'mact_modificadorT1'    => $value['mact_modificadoT1'],
                'mact_alcanzadoT1'      => $value['mact_alcanzadoT1'],
                'mact_originalT2'       => $value['mact_originalT2'],
                'mact_modificadorT2'    => $value['mact_modificadoT2'],
                'mact_alcanzadoT2'      => $value['mact_alcanzadoT2'],
                'mact_originalT3'       => $value['mact_originalT3'],
                'mact_modificadorT3'    => $value['mact_modificadoT3'],
                'mact_alcanzadoT3'      => $value['mact_alcanzadoT3'],
                'mact_originalT4'       => $value['mact_originalT4'],
                'mact_modificadorT4'    => $value['mact_modificadoT4'],
                'mact_alcanzadoT4'      => $value['mact_alcanzadoT4'],
            ];
            array_push($datos, $dato);

        }

        return $datos;
    }

}

$exp_obraPub = new exp_ObraPub();
