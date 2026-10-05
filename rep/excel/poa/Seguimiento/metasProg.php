<?php

session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);

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
            '',
            '', '', '', '',
            'Aprobadas',
            'Actualizadas',
            'Alcanzadas',
            'Aprobado',
            'Modificado',
            'Comprometido',
            'Devengado',
            'Ejercido',
        );
        // Almacenar en @size_cabecera la longitud del arreglo de los nombres de las columnas del contenido.
        $this->size_cabecera = sizeof($this->titulos_cabecera);

        $this->cabecera();

        $this->contenido();

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser("MetasProgramadas.xlsx");
    }
    public function cabecera()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Metas Programadas');

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
        $hoja1->setCellValue('A1', '2024');
        $hoja1->getStyle('A1')->getAlignment()->setTextRotation(90);
        $hoja1->getStyle('A1')->getFont()->getColor()->setRGB('BABABA');

        // Combinar celda de A1 hasta la mitad que se determino en el ciclo for anterior.
        $hoja1->mergeCells('B1:M1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('B1', 'SECRETARÍA DE FINANZAS');
        //Ponemos en negrita el texto
        $hoja1->getStyle('B1')->getFont()->setBold(true);

        $hoja1->mergeCells('B2:M2');
        $hoja1->setCellValue('B2', 'SUBSECRETARIA DE EGRESOS, CONTABILIDAD Y TESORERÍA');
        $hoja1->getStyle('B2')->getFont()->setBold(true);

        $hoja1->mergeCells('B3:M3');
        $hoja1->setCellValue('B3', 'DIRECCIÓN DE PRESUPUESTO');
        $hoja1->getStyle('B3')->getFont()->setBold(true);

        $tipoAct = 'REPORTE DE METAS PROGRAMADAS TRIMESTRALMENTE POR UR/PRESUPUESTO';

        $hoja1->mergeCells('B4:M4');
        $hoja1->setCellValue('B4', $tipoAct);
        $hoja1->getStyle('B4')->getFont()->setBold(true);

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

        $hoja1->mergeCells('A6:M6');
        $hoja1->setCellValue('A6', $trimestre);
        $hoja1->getStyle('A6')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A6')->getFont()->setBold(true);
        //Color de la celda donde va el nombre del trimestre
        $hoja1->getStyle('A6')->getFill()->setFillType('solid')->getStartColor()->setRGB('91C5D4');

        $hoja1->getStyle('A1')
            ->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A1')
            ->getAlignment()->setVertical('center');
        $hoja1->getStyle('A1')->getFont()->setSize('16');

        //Alineamos el texto del encabezado a la izquierda.
        $hoja1->getStyle('B1')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B2')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B3')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B4')
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $hoja1->getStyle('B5')
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
        $drawing->setHeight(65);

        //Se toma el ancho de la columna "O" que es de donde parte la imagen
        $colWith = $hoja1->getColumnDimension('M')->getWidth();
        //Se resta el ancho de la columna O al ancho de la imagen
        $drawing->setOffsetX($colWith - $drawing->getWidth());

        //Celda en donde comenzara a poner la imagen, en este caso la pongo 1 celda mas adelante (P1),
        // ya que le resto el tamaño de donde quiero que este, osea la "O1" para que se alinea a la derecha
        $drawing->setCoordinates('N1');
        //Ponemos la imagen en la hoja de trabajo actual(O se especifica una hoja).
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());

        $columna = 'A';
        for ($i = 1; $i <= $this->size_cabecera; $i++) {
            // Que cada columna no se ajuste al tamaño del texto.
            $hoja1->getColumnDimension($columna)->setAutoSize(false);
            (($this->size_cabecera == $i) ? '' : $columna++);
        }
        $columna--;

        //Aqui se define el ancho de las columnas
        $hoja1->getColumnDimension('A')->setWidth(12);
        $hoja1->getColumnDimension('B')->setWidth(16);
        $hoja1->getColumnDimension('C')->setWidth(16);
        $hoja1->getColumnDimension('D')->setWidth(16);
        $hoja1->getColumnDimension('E')->setWidth(16);
        $hoja1->getColumnDimension('F')->setWidth(16);
        $hoja1->getColumnDimension('G')->setWidth(16);
        $hoja1->getColumnDimension('H')->setWidth(16);
        $hoja1->getColumnDimension('I')->setWidth(16);
        $hoja1->getColumnDimension('J')->setWidth(16);
        $hoja1->getColumnDimension('K')->setWidth(16);
        $hoja1->getColumnDimension('L')->setWidth(16);
        $hoja1->getColumnDimension('M')->setWidth(16);

        // Establecer los nombre de las columnas, es decir las cabeceras
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_cabecera, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A8' // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se unen las celdas para la columna 
        $hoja1->mergeCells('A7:A8');
        $hoja1->setCellValue('A7', 'UR');
        $hoja1->getStyle('A7:A8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('A7')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A7')->getFont()->setBold(true);
        //Color de la celda donde va el nombre del trimestre
        $hoja1->getStyle('A7')->getFill()->setFillType('solid')->getStartColor()->setRGB('91C5D4');

        $hoja1->mergeCells('B7:E8');
        $hoja1->getStyle('B7:E8')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->setCellValue('B7', 'Unidad Responsable');
        $hoja1->getStyle('B7')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('B7')->getFont()->setBold(true);
        //Color de la celda donde va el nombre del trimestre
        $hoja1->getStyle('B7')->getFill()->setFillType('solid')->getStartColor()->setRGB('91C5D4');
        $hoja1->mergeCells('F7:H7');
        $hoja1->getStyle('F7:H7')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('F7:H8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('F7:H7')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('F7:H7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->setCellValue('F7', 'Metas');
        $hoja1->getStyle('F7')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('F7')->getFont()->setBold(true);
        $hoja1->mergeCells('I7:M7');
        $hoja1->setCellValue('I7', 'Presupuesto');
        $hoja1->getStyle('I7:M7')->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('I7:M8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('I7:M7')->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
        $hoja1->getStyle('I7:M7')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('I7')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('I7')->getFont()->setBold(true);
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
        $hoja1->getStyle('A7:M7')->applyFromArray($styleArray);
        $hoja1->getStyle('A8:M8')->applyFromArray($styleArray);
        //Tamaño de fuente
        $hoja1->getStyle('A7:M7')->getFont()->setSize('9');
        $hoja1->getStyle('A8:M8')->getFont()->setSize('9');
        //Texto autoajustado
        $hoja1->getStyle('A7:M7')->getAlignment()->setWrapText(true);
        $hoja1->getStyle('A8:M8')->getAlignment()->setWrapText(true);
        $hoja1->getStyle('F8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('G8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('I8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('J8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('K8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('L8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('M8')->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $hoja1->getStyle('F8:M8')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

    }

    public function consultaMetas()
    {

        $ur_clave = (isset($_POST['ur']) && $_POST['ur'] != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $_POST['ur'] : '';

        $trim = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '1';

        $trimestre = '';
        if (isset($_POST['trimestre']) && $_POST['trimestre'] != '') {
            switch ($_POST['trimestre']) {
                case 1:
                    $trimestre .= '
                        SUM(apr_ene+apr_feb+apr_mar) AS aprobado,
                        SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                        SUM(dev_ene+dev_feb+dev_mar) AS devengado,
                        SUM(eje_ene+eje_feb+eje_mar) AS ejercido,
                        SUM(com_ene + com_feb + com_mar) AS comprometido
                    ';
                    break;
                case 2:
                    $trimestre .= '
                        SUM(apr_abr+apr_may+apr_jun) AS aprobado,
                        SUM(vig_abr+vig_may+vig_jun) AS vigente,
                        SUM(dev_abr+dev_may+dev_jun) AS devengado,
                        SUM(eje_abr+eje_may+eje_jun) AS ejercido,
                        SUM(com_abr + com_may + com_jun) AS comprometido
                    ';
                    break;
                case 3:
                    $trimestre .= '
                        SUM(apr_jul+apr_ago+apr_sep) AS aprobado,
                        SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                        SUM(dev_jul+dev_ago+dev_sep) AS devengado,
                        SUM(eje_jul+eje_ago+eje_sep) AS ejercido,
                        SUM(com_jul + com_ago + com_sep) AS comprometido
                    ';
                    break;
                case 4:
                    $trimestre .= '
                        SUM(apr_oct+apr_nov+apr_dic) AS aprobado,
                        SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                        SUM(dev_oct+dev_nov+dev_dic) AS devengado,
                        SUM(eje_oct+eje_nov+eje_dic) AS ejercido,
                        SUM(com_oct + com_nov + com_dic) AS comprometido
                    ';
                    break;
                default:
                    break;
            }
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Consulta Actualizada
        $conexion->setSql('
            SELECT CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre AS desEntidad,
            SUM(mact_original) AS programada,
            SUM(mact_modificado) AS actualizada,SUM(mact_alcanzado) AS alcanzada,
            SUM(ppto.aprobado) AS aprobado,SUM(ppto.vigente) AS vigente,SUM(ppto.devengado) AS devengado,
            SUM(ppto.ejercido) AS ejercido,SUM(ppto.comprometido) AS comprometido
            FROM
            (
                SELECT p.gpo_id,p.ur_id,ur_nombre,p.ue_id,ue_nombre,p.prg_id,prg_nombre,p.spr_id,spr_nombre,p.py_id,py_nombre,p.oa_id,oa_nombre,ep.epg_id,toa_nombre
                FROM cat_ObraActividad AS p
                JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                JOIN cat_UnidadEjecutora USING (gpo_id,ur_id,ue_id)
                JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
                JOIN cat_Proyecto USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                JOIN cat_TipoObraActividad  AS toa USING (toa_id)
                JOIN enc_Programatico AS ep USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                WHERE  eoa_id IN (4,5) and toa_id = 1
                GROUP BY p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id
            ) AS pre
            LEFT JOIN (
                SELECT epg_id,
                ' . $trimestre . '
                FROM presupuesto
                JOIN presupuestoAprobado  AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente   AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido  AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoComprometido as comp USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                JOIN (
                    SELECT * FROM sinpres.cat_SuborigenRecurso2024 WHERE sor_ejercicio=2024
                ) AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE tcp_id<>"P"
                GROUP BY epg_id
            ) AS ppto USING (epg_id)
            LEFT JOIN cat_MetaActividadTrimestre ON pre.epg_id=cat_MetaActividadTrimestre.epg_id AND tri_id= ' . $trim . ' -- selecciona el trimestre
            LEFT JOIN vin_ProductoIntermedioIndicador USING (pii_id)
            LEFT JOIN cat_Indicador USING (nim_id,ind_id)
            WHERE  (mact_original+mact_modificado+mact_alcanzado)<>0 OR (ppto.aprobado+ppto.vigente)<>0
            GROUP BY pre.gpo_id, pre.ur_id
            ORDER BY pre.gpo_id,ur_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;
        ');

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();

        while ($row = $conexion->extraer()) {

            if ($row['aprobado'] == "") {
                $row['aprobado'] = 0;
            }
            if ($row['vigente'] == "") {
                $row['vigente'] = 0;
            }
            if ($row['devengado'] == "") {
                $row['devengado'] = 0;
            }
            if ($row['ejercido'] == "") {
                $row['ejercido'] = 0;
            }

            $dato = array(
                'ur' => $row['ur'],
                'desEntidad' => $row['desEntidad'],
                'programada' => $row['programada'],
                'actualizada' => $row['actualizada'],
                'alcanzada' => $row['alcanzada'],
                'aprobado' => $row['aprobado'],
                'vigente' => $row['vigente'],
                'comprometido' => $row['comprometido'],
                'devengado' => $row['devengado'],
                'ejercido' => $row['ejercido'],
            );

            array_push($datos, $dato);

        }

        return $datos;
    }

    public function contenido()
    {

        $fila = 9;
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        // Se almacena en la variable, el arreglo resultado de la consulta
        $datos = $this->consultaMetas();
        $ur = '';

        //En el siguiente for se hace un caso (if) por si cambia la ur, ue, prg, ...)
        foreach ($datos as $value) {
            if ($ur != $value['ur']) {
                $hoja1->setCellValue('A' . $fila, $value['ur']);
                $hoja1->setCellValue('B' . $fila, $value['desEntidad']);
                $hoja1->getStyle('A' . $fila)->getFont()->setBold(false);
                $hoja1->getStyle('B' . $fila)->getFont()->setBold(false);
                $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('center');
                $hoja1->getStyle('B' . $fila)->getAlignment()->setHorizontal('left');
                $hoja1->getStyle('A' . $fila . ':B' . $fila)->getFill();
                $hoja1->mergeCells('B' . $fila . ':E' . $fila);
                $ur = $value['ur_id'];
                $ur_nombre = $value['desEntidad'];

                $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
                $hoja1->setCellValue('F' . $fila, isset($value['programada']) ? $value['programada'] : '0.00' );
                $hoja1->getStyle('F' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->getStyle('F' . $fila)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $hoja1->setCellValue('G' . $fila, isset($value['actualizada']) ? $value['actualizada'] : '0.00');
                $hoja1->getStyle('G' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->setCellValue('H' . $fila, isset($value['alcanzada']) ? $value['alcanzada'] : '0.00');
                $hoja1->getStyle('H' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->setCellValue('I' . $fila, isset($value['aprobado']) ? $value['aprobado'] : '0.00');
                $hoja1->getStyle('I' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->getStyle('I' . $fila)->getBorders()->getLeft()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $hoja1->setCellValue('J' . $fila, isset($value['vigente']) ? $value['vigente'] : '0.00');
                $hoja1->getStyle('J' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->setCellValue('K' . $fila, isset($value['comprometido']) ? $value['comprometido'] : '0.00');
                $hoja1->getStyle('K' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->setCellValue('L' . $fila, isset($value['devengado']) ? $value['devengado'] : '0.00');
                $hoja1->getStyle('L' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $hoja1->setCellValue('M' . $fila, isset($value['ejercido']) ? $value['ejercido'] : '0.00');
                $hoja1->getStyle('M' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
                $fila++;
            }
        }
        $fila--;
        //Se pone los bordes a las celdas
        $hoja1->getStyle('A6:M' . $fila)->getBorders()->getOutline()->setBorderStyle('thick');

    }

}
$exp_ajustes = new exp_ajustes();
