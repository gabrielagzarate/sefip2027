<?php

session_start();

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida.
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';

class exp_ajustes extends Phpspreadsheet
{
    private $titulosCabecera;
    private $sizeCabecera;
    public function __construct()
    {
        parent::__construct();
        // Arreglo para poner los nombres de las columnas que va tener nuestro contenido.
        // Ademas que nos servira para determinar el ancho que va a tener nuestra cabecera.
        $this->titulosCabecera = [
            'Clave',
            'C O N C E P T O', '', '', '',
            'Presupuesto Aprobado',
            'Presupuesto Modificado',
            'Presupuesto Devengado',
            'Presupuesto Ejercido',
            'Número Indicador',
            'Número Meta',
            'Meta Programada',
            'Meta Actualizada',
            'Meta Alcanzada',
            '% Alcanzado',

        ];
        // Almacenar en @size_cabecera la longitud del arreglo de los nombres de las columnas del contenido.
        $this->sizeCabecera = sizeof($this->titulosCabecera);

        $this->cabecera();

        $this->contenido();

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser('SemMetasActividad.xlsx');
    }
    public function cabecera()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Semaforización de Metas');

        /*
        Determinamos cual es la mitad de nuestro contenido en base al arreglo de las columnas de nuestro
        contenido.
         */
        $total_cabecera = ceil($this->sizeCabecera);
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
        $hoja1->mergeCells('B1:O1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('B1', 'SECRETARÍA DE FINANZAS');
        //Ponemos en negrita el texto
        $hoja1->getStyle('B1')->getFont()->setBold(true);

        $hoja1->mergeCells('B2:O2');
        $hoja1->setCellValue('B2', 'SUBSECRETARIA DE EGRESOS, CONTABILIDAD Y TESORERÍA');
        $hoja1->getStyle('B2')->getFont()->setBold(true);

        $hoja1->mergeCells('B3:O3');
        $hoja1->setCellValue('B3', 'DIRECCIÓN DE PRESUPUESTO');
        $hoja1->getStyle('B3')->getFont()->setBold(true);

        $tipoAct = '';
        $toa_id = (isset($_POST['toa_id']) && $_POST['toa_id'] != '') ? $_POST['toa_id'] : 1;
        if ($toa_id == '1') {
            $tipoAct = 'SEMAFORIZACIÓN DE METAS ACTIVIDAD TIPO 1 (GASTO DE OPERACIÓN) CON PRESUPUESTO';
        } elseif ($toa_id == '2') {
            $tipoAct = 'SEMAFORIZACIÓN DE METAS ACTIVIDAD TIPO 2 (INVERSIÓN) CON PRESUPUESTO';
        } else {
            $tipoAct = 'SEMAFORIZACIÓN DE METAS ACTIVIDAD TIPO 1 Y 2 (GASTO DE OPERACIÓN E INVERSIÓN) CON PRESUPUESTO';
        }
        $hoja1->mergeCells('B4:O4');
        $hoja1->setCellValue('B4', $tipoAct);
        $hoja1->getStyle('B4')->getFont()->setBold(true);

        //Dependiendo del trimestre se pone el titulod el encabezado
        $trimestre = '';
        $trim = (isset($_POST['trimestre']) && $_POST['trimestre'] != '') ? $_POST['trimestre'] : '1';
        if ($trim == '1') {
            $trimestre = 'PRIMER TRIMESTRE';
        } elseif ($trim == '2') {
            $trimestre = 'SEGUNDO TRIMESTRE';
        } elseif ($trim == '3') {
            $trimestre = 'TERCER TRIMESTRE';
        } elseif ($trim == '4') {
            $trimestre = 'CUARTO TRIMESTRE';
        }

        $hoja1->mergeCells('B5:O5');
        $hoja1->setCellValue('B5', $trimestre);
        $hoja1->getStyle('B5')->getFont()->setBold(true);
        //Color de la celda donde va el nombre del trimestre
        $hoja1->getStyle('B5')->getFill()->setFillType('solid')->getStartColor()->setRGB('94C35E');

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
        for ($i = 1; $i <= $this->sizeCabecera; $i++) {
            // Que cada columna no se ajuste al tamaño del texto.
            $hoja1->getColumnDimension($columna)->setAutoSize(false);
            (($this->sizeCabecera == $i) ? '' : $columna++);
        }

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
        $hoja1->getColumnDimension('J')->setWidth(12);
        $hoja1->getColumnDimension('K')->setWidth(12);
        $hoja1->getColumnDimension('L')->setWidth(12);
        $hoja1->getColumnDimension('M')->setWidth(12);
        $hoja1->getColumnDimension('N')->setWidth(12);
        $hoja1->getColumnDimension('O')->setWidth(16);

        // Establecer los nombre de las columnas, es decir las cabeceras
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulosCabecera, // Arreglo de los datos a establecer
                null, // Los valores de esta matriz y con este valor no se estableceran
                'A6' // Rango de celda o celda en donde deseamos que comience a pintar
            );

        //Se unen las celdas para la columna CONCEPTO
        $hoja1->mergeCells('B6:E6');

        // Arreglo en el que determinamos la alineacion y el relleno de las cabeceras de las columnas.
        $styleArray = [
            // Alinear el texto al centro.
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
            // Pintar con un color solido y especificamos un color.
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '91C5D4',
                ],
            ],
            'font' => [
                'bold' => true,
            ],

        ];
        // Aplicar estilos a las columnas de la cabecera, desde un arreglo.
        $hoja1->getStyle('A6:O6')->applyFromArray($styleArray);
        //Tamaño de fuente
        $hoja1->getStyle('A6:O6')->getFont()->setSize('9');
        //Texto autoajustado
        $hoja1->getStyle('A6:O6')->getAlignment()->setWrapText(true);

    }

    public function consultaMetas()
    {

        $ur_clave = (isset($_POST['ur_clave']) && $_POST['ur_clave'] != '') ? ' AND CONCAT(gpo_id,ur_id)=' . $_POST['ur_clave'] : '';
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

        $porcentaje = (isset($_POST['porcentaje']) && $_POST['porcentaje'] != '' && isset($_POST['porcentaje2']) && $_POST['porcentaje2'] != '') ?
        'AND IFNULL(ROUND(((mact_alcanzado / mact_modificado) * 100),2),0.00) BETWEEN ' . $_POST['porcentaje'] . ' AND ' . $_POST['porcentaje2'] : ' ';

        $trimestre = '';
        if (isset($_POST['trimestre']) && $_POST['trimestre'] != '') {
            switch ($_POST['trimestre']) {
                case 1:
                    $trimestre .= '
                        SUM(apr_ene+apr_feb+apr_mar) AS aprobado,
                        SUM(vig_ene+vig_feb+vig_mar) AS vigente,
                        SUM(dev_ene+dev_feb+dev_mar) AS devengado,
                        SUM(eje_ene+eje_feb+eje_mar) AS ejercido
                    ';
                    break;
                case 2:
                    $trimestre .= '
                        SUM(apr_abr+apr_may+apr_jun) AS aprobado,
                        SUM(vig_abr+vig_may+vig_jun) AS vigente,
                        SUM(dev_abr+dev_may+dev_jun) AS devengado,
                        SUM(eje_abr+eje_may+eje_jun) AS ejercido
                    ';
                    break;
                case 3:
                    $trimestre .= '
                        SUM(apr_jul+apr_ago+apr_sep) AS aprobado,
                        SUM(vig_jul+vig_ago+vig_sep) AS vigente,
                        SUM(dev_jul+dev_ago+dev_sep) AS devengado,
                        SUM(eje_jul+eje_ago+eje_sep) AS ejercido
                    ';
                    break;
                case 4:
                    $trimestre .= '
                        SUM(apr_oct+apr_nov+apr_dic) AS aprobado,
                        SUM(vig_oct+vig_nov+vig_dic) AS vigente,
                        SUM(dev_oct+dev_nov+dev_dic) AS devengado,
                        SUM(eje_oct+eje_nov+eje_dic) AS ejercido
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
            SELECT pre.epg_id,CONCAT(pre.gpo_id,pre.ur_id) AS ur,ur_nombre,
            CAST(pre.ue_id AS CHAR) AS ue_id,ue_nombre,
            CAST(pre.prg_id AS CHAR) AS prg_id, prg_nombre,
            CAST(pre.spr_id AS CHAR) AS spr_id,spr_nombre,
            CAST(pre.py_id AS CHAR) AS py_id,py_nombre,
            CAST(pre.oa_id AS CHAR) AS oa_id,oa_nombre,toa_nombre,
            cat_Indicador.nim_id,ind_id AS indicador, mac_id AS meta,mact_original AS programada,mact_modificado AS actualizada,mact_alcanzado AS alcanzada,
            IF(mact_modificado=0,0,(mact_alcanzado*100/mact_modificado)) AS porcentaje,tri_id AS trimestre,
            ppto.aprobado,ppto.vigente,ppto.devengado,ppto.ejercido
            #IFNULL(ppto.aprobado,0),IFNULL(ppto.vigente,0),IFNULL(ppto.devengado,0),IFNULL(ppto.ejercido,0)
            FROM
            ( SELECT p.gpo_id,p.ur_id,ur_nombre,p.ue_id,ue_nombre,p.prg_id,prg_nombre,p.spr_id,spr_nombre,p.py_id,py_nombre,p.oa_id,oa_nombre,ep.epg_id,toa_nombre
            FROM cat_ObraActividad AS p
                JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                JOIN cat_UnidadEjecutora   USING (gpo_id,ur_id,ue_id)
                JOIN cat_Programa          USING (gpo_id,ur_id,prg_id)
                JOIN cat_Subprograma       USING (gpo_id,ur_id,prg_id,spr_id)
                JOIN cat_Proyecto          USING (gpo_id,ur_id,prg_id,spr_id,py_id)
                JOIN cat_TipoObraActividad  AS toa USING (toa_id)
                JOIN enc_Programatico AS ep USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            WHERE toa.toa_id=1 AND eoa_id IN (4,5)    -- condiciones fijas
            ' . $ur_clave . '
            ' . $prg_id . '
            ' . $ue_id . '
            ' . $spr_id . '
            ' . $py_id . '
            ' . $oa_id . '
            GROUP BY p.gpo_id,p.ur_id,p.ue_id,p.prg_id,p.spr_id,p.py_id,p.oa_id
            ) AS pre
            LEFT JOIN (
            SELECT epg_id,MAX(ter_id),
            ' . $trimestre . '
            FROM presupuesto
                JOIN presupuestoAprobado  AS apr USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoVigente   AS vig USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoDevengado AS dev USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN presupuestoEjercido  AS eje USING(gpo_id,ur_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id)
                JOIN (SELECT * FROM sinpres.cat_SuborigenRecurso2023 WHERE sor_ejercicio=2023) AS cso USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            WHERE tcp_id<>"P"
                ' . $ur_clave . '
                ' . $ue_id . '
                ' . $prg_id . '
                ' . $spr_id . '
                ' . $py_id . '
                ' . $ter_id . '
            GROUP BY epg_id ) AS ppto USING (epg_id)
            LEFT JOIN cat_MetaActividadTrimestre  AS cmett ON pre.epg_id=cmett.epg_id AND tri_id= ' . $trim . ' -- selecciona el trimestre
            LEFT JOIN cat_MetaActividad  AS cmet ON CONCAT(cmet.pii_id,cmet.epg_id)=CONCAT(cmett.pii_id,pre.epg_id)
            LEFT JOIN vin_ProductoIntermedioIndicador  AS cpro ON cpro.pii_id=cmett.pii_id
            LEFT JOIN cat_Indicador USING (nim_id,ind_id)
            WHERE  (mact_original+mact_modificado+mact_alcanzado)<>0 OR (ppto.aprobado+ppto.vigente)<>0
            ' . $porcentaje . '
            ORDER BY pre.gpo_id,ur_id,pre.prg_id,pre.spr_id,pre.py_id,pre.oa_id;
        ');

        $conexion->consultar();
        $conexion->desconectar();

        $datos = [];

        while ($row = $conexion->extraer()) {

            if ($row['aprobado'] == '') {
                $row['aprobado'] = 0;
            }
            if ($row['vigente'] == '') {
                $row['vigente'] = 0;
            }
            if ($row['devengado'] == '') {
                $row['devengado'] = 0;
            }
            if ($row['ejercido'] == '') {
                $row['ejercido'] = 0;
            }

            $dato = [
                'ur_id'       => $row['ur'],
                'ur_nombre'   => $row['ur_nombre'],
                'ue_id'       => $row['ue_id'],
                'ue_nombre'   => $row['ue_nombre'],
                'prg_id'      => $row['prg_id'],
                'prg_nombre'  => $row['prg_nombre'],
                'spr_id'      => $row['spr_id'],
                'spr_nombre'  => $row['spr_nombre'],
                'py_id'       => $row['py_id'],
                'py_nombre'   => $row['py_nombre'],
                'oa_id'       => $row['oa_id'],
                'oa_nombre'   => $row['oa_nombre'],
                'aprobado'    => $row['aprobado'],
                'vigente'     => $row['vigente'],
                'devengado'   => $row['devengado'],
                'ejercido'    => $row['ejercido'],
                'indicador'   => $row['indicador'],
                'meta'        => $row['meta'],
                'programada'  => $row['programada'],
                'actualizada' => $row['actualizada'],
                'alcanzada'   => $row['alcanzada'],
                'porcentaje'  => $row['porcentaje'],
            ];

            array_push($datos, $dato);

        }

        return $datos;
    }

    public function contenido()
    {

        $fila = 7;
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        // Se almacena en la variable, el arreglo resultado de la consulta
        $datos = $this->consultaMetas();
        $ur = '';
        $ur_nombre = '';
        $ue = '';
        $ue_nombre = '';
        $prg = '';
        $prg_nombre = '';
        $spr = '';
        $spr_nombre = '';
        $py = '';
        $py_nombre = '';
        $oa = '';
        $oa_nombre = '';
        $indicador = '';
        //En el siguiente for se hace un caso (if) por si cambia la ur, ue, prg, ...)
        foreach ($datos as $value) {
            if ($ur != $value['ur_id']) {
                $hoja1->setCellValue('A' . $fila, $value['ur_id']);
                $hoja1->setCellValue('B' . $fila, $value['ur_nombre']);
                $hoja1->getStyle('A' . $fila)->getFont()->setBold(true);
                $hoja1->getStyle('B' . $fila)->getFont()->setBold(true);
                $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('left');
                $hoja1->getStyle('B' . $fila)->getAlignment()->setHorizontal('left');
                $hoja1->getStyle('A' . $fila . ':B' . $fila)->getFill()->setFillType('solid')->getStartColor()->setRGB('D3E0DB');
                $hoja1->mergeCells('B' . $fila . ':O' . $fila);
                $ur = $value['ur_id'];
                $ur_nombre = $value['ur_nombre'];
                $fila++;

                $this->pintaUe($value['ue_id'], $value['ue_nombre'], $fila);
                $ue = $value['ue_id'];
                $ue_nombre = $value['ue_nombre'];
                $fila++;

                $this->pintaPrg($value['prg_id'], $value['prg_nombre'], $fila);
                $prg = $value['prg_id'];
                $prg_nombre = $value['prg_nombre'];
                $fila++;

                $this->pintaSpr($value['spr_id'], $value['spr_nombre'], $fila);
                $spr = $value['spr_id'];
                $spr_nombre = $value['spr_nombre'];
                $fila++;

                $this->pintaPy($value['py_id'], $value['py_nombre'], $fila);
                $py = $value['py_id'];
                $py_nombre = $value['py_nombre'];
                $fila++;

                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif ($ue != $value['ue_id'] || $ue_nombre != $value['ue_nombre']) {
                $this->pintaUe($value['ue_id'], $value['ue_nombre'], $fila);
                $ue = $value['ue_id'];
                $ue_nombre = $value['ue_nombre'];
                $fila++;

                $this->pintaPrg($value['prg_id'], $value['prg_nombre'], $fila);
                $prg = $value['prg_id'];
                $prg_nombre = $value['prg_nombre'];
                $fila++;

                $this->pintaSpr($value['spr_id'], $value['spr_nombre'], $fila);
                $spr = $value['spr_id'];
                $spr_nombre = $value['spr_nombre'];
                $fila++;

                $this->pintaPy($value['py_id'], $value['py_nombre'], $fila);
                $py = $value['py_id'];
                $py_nombre = $value['py_nombre'];
                $fila++;

                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif ($prg != $value['prg_id'] || $prg_nombre != $value['prg_nombre']) {
                $this->pintaPrg($value['prg_id'], $value['prg_nombre'], $fila);
                $prg = $value['prg_id'];
                $prg_nombre = $value['prg_nombre'];
                $fila++;

                $this->pintaSpr($value['spr_id'], $value['spr_nombre'], $fila);
                $spr = $value['spr_id'];
                $spr_nombre = $value['spr_nombre'];
                $fila++;

                $this->pintaPy($value['py_id'], $value['py_nombre'], $fila);
                $py = $value['py_id'];
                $py_nombre = $value['py_nombre'];
                $fila++;

                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif ($spr != $value['spr_id'] || $spr_nombre != $value['spr_nombre']) {
                $this->pintaSpr($value['spr_id'], $value['spr_nombre'], $fila);
                $spr = $value['spr_id'];
                $spr_nombre = $value['spr_nombre'];
                $fila++;

                $this->pintaPy($value['py_id'], $value['py_nombre'], $fila);
                $py = $value['py_id'];
                $py_nombre = $value['py_nombre'];
                $fila++;

                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif ($py != $value['py_id'] || $py_nombre != $value['py_nombre']) {
                $this->pintaPy($value['py_id'], $value['py_nombre'], $fila);
                $py = $value['py_id'];
                $py_nombre = $value['py_nombre'];
                $fila++;

                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif ($oa != $value['oa_id'] || $oa_nombre != $value['oa_nombre']) {
                $this->pintaOa($value, $fila);
                $oa = $value['oa_id'];
                $oa_nombre = $value['oa_nombre'];
                $fila++;
                $fila++;
            } elseif (($indicador != $value['indicador'])) {
                $fila--;
                $this->pintaInd($value, $fila);
                $indicador = $value['indicador'];
                $fila++;
                $fila++;
            }

        }

        //Se pone los bordes a las celdas
        $hoja1->getStyle('A6:O' . $fila)->getBorders()->getOutline()->setBorderStyle('thin');

    }

    // Las siguientes funciones, establecen el estilo para cada caso.
    public function pintaUe(string $ue_id, string $ue_nombre, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('A' . $fila, $ue_id);
        $hoja1->setCellValue('B' . $fila, $ue_nombre);
        $hoja1->getStyle('A' . $fila)->getFont()->setBold(true);
        $hoja1->getStyle('B' . $fila)->getFont()->setBold(true);
        $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('center');
        $hoja1->mergeCells('B' . $fila . ':E' . $fila);

    }

    public function pintaPrg(string $prg_id, string $prg_nombre, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('A' . $fila, 'Prg: ' . $prg_id);
        $hoja1->setCellValue('B' . $fila, $prg_nombre);
        $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('left');
        $hoja1->mergeCells('B' . $fila . ':E' . $fila);

    }

    public function pintaSpr(string $spr_id, string $spr_nombre, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('A' . $fila, 'Spr: ' . $spr_id);
        $hoja1->setCellValue('B' . $fila, $spr_nombre);
        $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('left');
        $hoja1->mergeCells('B' . $fila . ':E' . $fila);

    }
    public function pintaPy(string $py_id, string $py_nombre, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('A' . $fila, 'Proy: ' . $py_id);
        $hoja1->setCellValue('B' . $fila, $py_nombre);
        $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('left');
        $hoja1->mergeCells('B' . $fila . ':E' . $fila);

    }

    public function pintaOa(array $obra, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('A' . $fila, 'Oa: ' . $obra['oa_id']);
        $hoja1->setCellValue('B' . $fila, $obra['oa_nombre']);
        $hoja1->getStyle('A' . $fila)->getAlignment()->setHorizontal('left');
        $hoja1->mergeCells('B' . $fila . ':E' . $fila);
        $hoja1->setCellValue('F' . $fila, $obra['aprobado']);
        $hoja1->getStyle('F' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('G' . $fila, $obra['vigente']);
        $hoja1->getStyle('G' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('H' . $fila, $obra['devengado']);
        $hoja1->getStyle('H' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('I' . $fila, $obra['ejercido']);
        $hoja1->getStyle('I' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('J' . $fila, $obra['indicador']);
        $hoja1->getStyle('J' . $fila)->getNumberFormat()->setFormatCode('#,##0');
        $hoja1->setCellValue('K' . $fila, $obra['meta']);
        $hoja1->getStyle('K' . $fila)->getNumberFormat()->setFormatCode('#,##0');
        $hoja1->setCellValue('L' . $fila, $obra['programada']);
        $hoja1->getStyle('L' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('M' . $fila, $obra['actualizada']);
        $hoja1->getStyle('M' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('N' . $fila, $obra['alcanzada']);
        $hoja1->getStyle('N' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('O' . $fila, $obra['porcentaje']);
        $hoja1->getStyle('O' . $fila)->getNumberFormat()->setFormatCode('#,##0.00000000');

    }

    public function pintaInd(array $obra, int $fila)
    {
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);
        $hoja1->setCellValue('J' . $fila, $obra['indicador']);
        $hoja1->getStyle('J' . $fila)->getNumberFormat()->setFormatCode('#,##0');
        $hoja1->setCellValue('K' . $fila, $obra['meta']);
        $hoja1->getStyle('K' . $fila)->getNumberFormat()->setFormatCode('#,##0');
        $hoja1->setCellValue('L' . $fila, $obra['programada']);
        $hoja1->getStyle('L' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('M' . $fila, $obra['actualizada']);
        $hoja1->getStyle('M' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('N' . $fila, $obra['alcanzada']);
        $hoja1->getStyle('N' . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja1->setCellValue('O' . $fila, $obra['porcentaje']);
        $hoja1->getStyle('O' . $fila)->getNumberFormat()->setFormatCode('#,##0.00000000');
    }
}
$exp_ajustes = new exp_ajustes();
