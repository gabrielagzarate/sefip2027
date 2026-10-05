<?php

session_start();

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida.
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';

class exp_ajustes extends Phpspreadsheet
{

    public function __construct()
    {
        parent::__construct();
        // Arreglo para poner los nombres de las columnas que va tener nuestro contenido.
        // Ademas que nos servira para determinar el ancho que va a tener nuestra cabecera.
        $this->titulos_cabecera = array(
            'Clave',
            'Unidad Responsable',
            'Unidad Ejecutora',
            'Programa',
            'Subprograma',
            'Proyecto',
            'Actividad',
            'Definición',
            'Número Indicador',
            'Número',
            'Programada',
            'Actualizada',
            'Alcanzada',
            'Porcentaje Alcanzado',
            'Observación realizada a la UR',
        );
        // Almacenar en @size_cabecera la longitud del arreglo de los nombres de las columnas del contenido.
        $this->size_cabecera = sizeof($this->titulos_cabecera);

        $this->cabecera();

        $this->contenido();

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser("SegMetas.xlsx");
    }
    public function cabecera()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Seguimiento a Metas');

        /*
        Determinamos cual es la mitad de nuestro contenido en base al arreglo de las columnas de nuestro
        contenido.
         */
        $total_cabecera = ceil($this->size_cabecera);
        $columna = 'A';
        for ($i = $total_cabecera; $i > 0; $i--) {
            $columna++;
        }

        //CABECERA
        $hoja1->mergeCells('A1:A5');
        $hoja1->setCellValue('A1', '2023');
        $hoja1->getStyle('A1')->getAlignment()->setTextRotation(90);
        $hoja1->getStyle('A1')->getFont()->getColor()->setRGB('BABABA');

        // Combinar celda de A1 hasta la mitad que se determino en el ciclo for anterior.
        $hoja1->mergeCells('B1:' . $columna . '1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('B1', 'SECRETARÍA DE FINANZAS');
        //Ponemos en negrita el texto
        $hoja1->getStyle('B1')->getFont()->setBold(true);

        $hoja1->mergeCells('B2:' . $columna . '2');
        $hoja1->setCellValue('B2', 'SUBSECRETARIA DE EGRESOS, CONTABILIDAD Y TESORERÍA');
        $hoja1->getStyle('B2')->getFont()->setBold(true);

        $hoja1->mergeCells('B3:' . $columna . '3');
        $hoja1->setCellValue('B3', 'DIRECCIÓN DE PRESUPUESTO');
        $hoja1->getStyle('B3')->getFont()->setBold(true);

        $hoja1->mergeCells('B4:' . $columna . '4');
        $hoja1->setCellValue('B4', 'SEGUIMIENTO AL CUMPLIMIENTO DE METAS');
        $hoja1->getStyle('B4')->getFont()->setBold(true);

        $tipoAct = '';
        $toa_id = (isset($_POST['toa_id']) && $_POST['toa_id'] != '') ? $_POST['toa_id'] : 1;
        if ($toa_id == '1') {
            $tipoAct = 'ACTIVIDADES TIPO 1 (GASTO DE OPERACIÓN)';
        } else if ($toa_id == '2') {
            $tipoAct = 'ACTIVIDADES TIPO 2 (INVERSIÓN)';
        } else {
            $tipoAct = 'ACTIVIDADES TIPO 1 Y 2 (GASTO DE OPERACIÓN E INVERSIÓN)';
        }

        $hoja1->mergeCells('B5:' . $columna . '5');
        $hoja1->setCellValue('B5', $tipoAct);
        $hoja1->getStyle('B5')->getFont()->setBold(true);

        //Dependiendo del trimestre se pone el titulod el encabezado
        $trimestre = '';
        $trim = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '1';
        if ($trim == '1') {
            $trimestre = 'PRIMER TRIMESTRE';
        } else if ($trim == '2') {
            $trimestre = 'SEGUNDO TRIMESTRE';
        } else if ($trim == '3') {
            $trimestre = 'TERCER TRIMESTRE';
        } else if ($trim == '4') {
            $trimestre = 'CUARTO TRIMESTRE';
        }

        $hoja1->mergeCells('A7:O7');
        $hoja1->setCellValue('A7', $trimestre);
        $hoja1->getStyle('A7')->getFont()->setBold(true);

        $hoja1->getStyle('A1')
            ->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A1')
            ->getAlignment()->setVertical('center');
        $hoja1->getStyle('A1')->getFont()->setSize('16');
        //Alineamos el texto a la izquierda.
        $hoja1->getStyle('B1')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B2')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B3')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B4')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B5')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('A7')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        //Variable para operar una imagen
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        //Establecemos un nombre a nuestra imagen.
        $drawing->setName('Logo');
        //Establecemos una descripcion a nuestra imagen
        $drawing->setDescription('Logo');
        $drawing->setPath(dirname(__FILE__) . '/../../../../img/Logo_Institucional.jpg');
        //Tamañao de la imagen
        $drawing->getResizeProportional(true);
        $drawing->setHeight(80);

        //Se toma el ancho de la columna "O" que es de donde parte la imagen
        $colWith = $hoja1->getColumnDimension('O')->getWidth();
        //Se resta el ancho de la columna O al ancho de la imagen
        $drawing->setOffsetX($colWith - $drawing->getWidth());

        //Celda en donde comenzara a poner la imagen, en este caso la pongo 1 celda mas adelante (P1),
        // ya que le resto el tamaño de donde quiero que este, osea la "O1" para que se alinea a la derecha
        $drawing->setCoordinates('P1');
        //Ponemos la imagen en la hoja de trabajo actual(O se especifica una hoja).
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());

        $columna = 'A';
        for ($i = 1; $i <= $this->size_cabecera; $i++) {
            // Que cada columna no se ajuste al tamaño del texto.
            $hoja1->getColumnDimension($columna)->setAutoSize(false);
            $hoja1->mergeCells($columna . '8:' . $columna . '9');

            (($this->size_cabecera == $i) ? '' : $columna++);
        }
        $columna--;

        //Aqui se define el ancho de las columnas
        $hoja1->getColumnDimension('A')->setWidth(6);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(9.5);
        $hoja1->getColumnDimension('D')->setWidth(9.5);
        $hoja1->getColumnDimension('E')->setWidth(9.5);
        $hoja1->getColumnDimension('F')->setWidth(9.5);
        $hoja1->getColumnDimension('G')->setWidth(9.5);
        $hoja1->getColumnDimension('H')->setWidth(45);
        $hoja1->getColumnDimension('I')->setWidth(9.5);
        $hoja1->getColumnDimension('J')->setWidth(9.5);
        $hoja1->getColumnDimension('K')->setWidth(9.5);
        $hoja1->getColumnDimension('L')->setWidth(9.5);
        $hoja1->getColumnDimension('M')->setWidth(9.5);
        $hoja1->getColumnDimension('N')->setWidth(9.5);
        $hoja1->getColumnDimension('O')->setWidth(50);

        //La parte donde va la meta y sus componentes, se acomoda diferente ya que el diseño lo pide
        //Se separan las celdas
        $hoja1->unmergeCells('J8:J9');
        $hoja1->unmergeCells('K8:K9');
        $hoja1->unmergeCells('L8:L9');
        $hoja1->unmergeCells('M8:M9');
        $hoja1->mergeCells('J8:M8');

        // Establecer los nombre de las columnas, es decir las cabeceras
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_cabecera, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A8' // Rango de celda o celda en donde deseamos que comience a pintar
            );

        $hoja1->setCellValue('J8', 'Meta');
        $hoja1->setCellValue('J9', 'Número');
        $hoja1->setCellValue('K9', 'Programada');
        $hoja1->setCellValue('L9', 'Actualizada');
        $hoja1->setCellValue('M9', 'Alcanzada');

        // Arreglo en el que determinamos la alineacion y el relleno de las cabeceras de las columnas.
        $styleArray = [
            // Alinear el texto al centro.
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            // Pintar con un color solido y especificamos un color.
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '91C5D4',
                ],
            ],
            'font' => [
                'bold' => true,
            ],

        ];
        // Aplicar estilos a las columnas de la cabecera, desde un arreglo.
        $hoja1->getStyle('A8:O9')->applyFromArray($styleArray);
        $hoja1->getStyle('A8:O9')->getFont()->setSize('9');
        $hoja1->getStyle('A8:O9')->getAlignment()->setWrapText(true);

        //Color de la celda donde va el nombre del trimestre
        $hoja1->getStyle('A7')->getFill()->setFillType('solid')->getStartColor()->setRGB('D3E0DB');

    }

    public function consultaMetas()
    {

        $ur_clave = (isset($_POST['ur_clave']) && $_POST['ur_clave'] != '') ? '=' . $_POST['ur_clave'] : '';
        $ur_clave2 = (isset($_POST['ur_clave']) && $_POST['ur_clave'] != '') ? 'WHERE CONCAT(progra.gpo_id,progra.ur_id)=' . $_POST['ur_clave'] : '';
        $ue_id = (isset($_POST['ue_id']) && $_POST['ue_id'] != '') ? ' AND ue_id=' . $_POST['ue_id'] : '';
        $prg_id = (isset($_POST['prg_id']) && $_POST['prg_id'] != '') ? ' AND prg_id=' . $_POST['prg_id'] : '';
        $spr_id = (isset($_POST['spr_id']) && $_POST['spr_id'] != '') ? ' AND spr_id=' . $_POST['spr_id'] : '';
        $oa_id = (isset($_POST['oa_id']) && $_POST['oa_id'] != '') ? ' AND oa_id=' . $_POST['oa_id'] : '';
        $py_id = (isset($_POST['py_id']) && $_POST['py_id'] != '') ? ' AND py_id=' . $_POST['py_id'] : '';

        $ter_id = '';
        if ((isset($_POST['ter_id']) && $_POST['ter_id'] != '')) {
            if ($_POST['ter_id'] != 'N') {
                $ter_id .= ' AND ter_id = "' . $_POST['ter_id'] . '"';
            }

        }
        $trim = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '1';
        $porcentaje = (isset($_POST['porcentaje']) && $_POST['porcentaje'] != '' && isset($_POST['porcentaje2']) && $_POST['porcentaje2'] == "") ?
        'AND IFNULL(ROUND(((mact_alcanzado / mact_modificado) * 100),2),0.00) BETWEEN ' . $_POST['porcentaje'] . ' AND ' . $_POST['porcentaje2'] . ' ' : ' ';

        switch ($trim) {
            case 1:
                $and = "presupuesto.usr_fecha <= '2023-03-31' ";
                $presupuesto = " (SUM(apr_ene)+ SUM(apr_feb)+ SUM(apr_mar)) AS aprobado,
                                (SUM(vig_ene) + SUM(vig_feb) + SUM(vig_mar)) AS vigente";
                break;
            case 2:
                $and = "presupuesto.usr_fecha <= '2023-06-30' ";
                $presupuesto = " (SUM(apr_abr)+SUM(apr_may)+ SUM(apr_jun)) AS aprobado,
                                (SUM(vig_abr) + SUM(vig_may) + SUM(vig_jun)) AS vigente ";
                break;
            case 3:
                $and = "presupuesto.usr_fecha <= '2023-09-30' ";
                $presupuesto = " (SUM(apr_jul)+SUM(apr_ago)+ SUM(apr_sep)) AS aprobado,
                                    (SUM(vig_jul) + SUM(vig_ago) + SUM(vig_sep)) AS vigente";
                break;
            case 4:
                $and = "presupuesto.usr_fecha <= '2023-12-31' ";
                $presupuesto = " (SUM(apr_oct) + SUM(apr_nov) + SUM(apr_dic)) AS aprobado,
                                (SUM(vig_oct) + SUM(vig_nov) + SUM(vig_dic)) AS vigente,
                                (SUM(com_oct) + SUM(com_nov) + SUM(com_dic)) AS comprometido,
                                (SUM(dev_oct) + SUM(dev_nov) + SUM(dev_dic )) AS devengado,
                                (SUM(eje_oct) + SUM(eje_nov) + SUM(eje_dic)) AS ejercido ";
                break;
        }

        $sql = "select *from (
            SELECT
            progra.epg_id,CONCAT(progra.gpo_id,progra.ur_id) AS ur,ur_nombre,
            CAST(ue.ue_id AS CHAR) AS ue_id,ue_nombre,
            CAST(pr.prg_id AS CHAR) AS prg_id, prg_nombre AS desPrograma,
            CAST(subp.spr_id AS CHAR) AS spr_id,spr_nombre AS desSubprograma,
            CAST(pry.py_id AS CHAR) AS py_id,py_nombre AS desProyecto,
            CAST(obact.oa_id AS CHAR) AS oa_id,oa_nombre,toa_nombre,
            cat_Indicador.nim_id,ind_id AS indicador, mac_id AS meta,mact_original AS programada,mact_modificado AS actualizada,mact_alcanzado AS alcanzada,
            IF(mact_modificado=0,0,(mact_alcanzado*100/mact_modificado)) AS porcentaje,
            msj_id,hms_mensaje,
            ifnull(aprobado,0) as aprobado,
            ifnull(vigente,0) as vigente
            FROM
            cat_MetaActividad  AS cmet
            LEFT JOIN cat_MetaActividadTrimestre  AS cmett ON cmet.epg_id=cmett.epg_id AND cmet.pii_id=cmett.pii_id AND tri_id= " . $trim . "
            LEFT JOIN vin_ProductoIntermedioIndicador  AS cpro ON cpro.pii_id=cmett.pii_id
            JOIN cat_Indicador USING (nim_id,ind_id)
            LEFT JOIN
            (SELECT cat_m.epg_id,cat_m.msj_id,cat_m.pii_id,hms_trimestre,hms_mensaje  FROM cat_MensajeSeguimiento AS cat_m
            JOIN cat_HistorialMensajeSeguimiento AS cat_h ON cat_m.msj_id=cat_h.msj_id AND eme_id IN (2,4,5,6) AND tim_id=1
            AND cat_h.hms_trimestre= " . $trim . "  )
            AS msj ON cmet.epg_id=msj.epg_id  AND cpro.pii_id=msj.pii_id
                INNER JOIN enc_Programatico  progra on cmet.epg_id = progra.epg_id
                JOIN cat_UnidadResponsable   ur on progra.gpo_id = ur.gpo_id and progra.ur_id = ur.ur_id
                JOIN cat_Programa          pr on progra.gpo_id = pr.gpo_id and progra.ur_id = pr.ur_id and progra.prg_id = pr.prg_id
                JOIN cat_Subprograma       subp on progra.gpo_id = subp.gpo_id and progra.ur_id = subp.ur_id and progra.prg_id = subp.prg_id and progra.spr_id = subp.spr_id
                JOIN cat_Proyecto          pry on progra.gpo_id = pry.gpo_id and progra.ur_id = pry.ur_id and progra.prg_id = pry.prg_id and progra.spr_id = pry.spr_id and progra.py_id = pry.py_id
                JOIN (SELECT gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,oa_nombre,toa_id,eoa_id,ue_id FROM cat_ObraActividad WHERE toa_id=1 and eoa_id IN (4,5)) obact on progra.gpo_id = obact.gpo_id and progra.ur_id = obact.ur_id and progra.prg_id = obact.prg_id and progra.spr_id = obact.spr_id
                and progra.py_id = obact.py_id and progra.oa_id = obact.oa_id
                JOIN cat_UnidadEjecutora   ue on obact.gpo_id = ue.gpo_id and obact.ur_id = ue.ur_id and obact.ue_id = ue.ue_id
                JOIN cat_TipoObraActividad toa on obact.toa_id = toa.toa_id
                LEFT JOIN (select prg_id, spr_id,py_id, oa_id, epg_id,
                " . $presupuesto . "
            from presupuesto
            JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
            JOIN (SELECT cff_id,fte_id,sft_id,ori_id,sor_id FROM sinpres.cat_SuborigenRecurso2023 WHERE sor_ejercicio=2023) AS cso
            WHERE CONCAT(gpo_id,ur_id)	" . $ur_clave . "
            " . $oa_id . "
            " . $prg_id . "
            " . $spr_id . "
            " . $py_id . "
            " . $ue_id . "
            " . $ter_id . "

            group by prg_id, spr_id,py_id, oa_id, epg_id
            )presu ON cmet.epg_id = presu.epg_id
            " . $ur_clave2 . "
            ORDER BY CONCAT(progra.gpo_id,progra.ur_id),pr.prg_id,subp.spr_id,pry.py_id,obact.oa_id) con
            where programada > 0 OR actualizada > 0
            and aprobado > 0 OR vigente >0;";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();

        while ($row = $conexion->extraer()) {
            $dato = array(
                'ur' => $row['ur'],
                'ur_nombre' => $row['ur_nombre'],
                'ue_id' => $row['ue_id'],
                'prg_id' => $row['prg_id'],
                'spr_id' => $row['spr_id'],
                'py_id' => $row['py_id'],
                'oa_id' => $row['oa_id'],
                'oa_nombre' => $row['oa_nombre'],
                'indicador' => $row['indicador'],
                'meta' => $row['meta'],
                'programada' => $row['programada'],
                'actualizada' => $row['actualizada'],
                'alcanzada' => $row['alcanzada'],
                'porcentaje' => $row['porcentaje'],
                'hms_mensaje' => $row['hms_mensaje'],
            );

            array_push($datos, $dato);
        }
        return $datos;
    }

    public function contenido()
    {
        $datos = $this->consultaMetas();

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $datos, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A10' // Rango de celda o celda en donde deseamos que comience a pintar
            );

        //Texto ajustado // Se le suma +9 porque es la diferencia de celdas donde se inicia
        $this->spreadsheet->getActiveSheet()->getStyle('A10:O' . (count($datos) + 9))
            ->getAlignment()->setWrapText(true);
        //Alineacion al centro vertical y horizontalmente
        $this->spreadsheet->getActiveSheet()->getStyle('A10:O' . (count($datos) + 9))
            ->getAlignment()->setHorizontal('center');
        $this->spreadsheet->getActiveSheet()->getStyle('A10:O' . (count($datos) + 9))
            ->getAlignment()->setVertical('center');
    }
}
$exp_ajustes = new exp_ajustes();
