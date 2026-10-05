<?php

session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);
ini_set("memory_limit",'1G');
ini_set('max_execution_time', 0);

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida. 
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';


class exp_Programas extends Phpspreadsheet
{

    function __construct()
    {
        parent::__construct();

        $this->hoja1();

        //Se activa la hoja 1 como principal
        $this->spreadsheet->setActiveSheetIndex(0);

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser("PerfilActividad.xlsx");
    }

    //Función que genera la hoja 1
    public function hoja1()
    {

        $this->encabezadoHoja1();

        $datos = $this->consultaHoja1();

        //Se añade a la hoja el arreglo con los datos obtenidos
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $datos,// Arreglo de los datos a establecer
                null,// Los valores de esta matriz y con este valor no se estableceran
                'A4'// Rango de celda o celda en donde deseamos que comience a pintar
            );

        $this->spreadsheet->getActiveSheet()->getStyle('A4:AU' . (count($datos) + 3))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->spreadsheet->getActiveSheet()->getStyle('AI4:AJ' . (count($datos) + 3))
            ->getAlignment()->setWrapText(true);
 
    }

    function  encabezadoHoja1(){
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Nivel Actividades');

        //Se agregan los encabezados
        $hoja1->mergeCells('A1:F2');
        $hoja1->mergeCells('G1:Q2');
        $hoja1->mergeCells('R1:AJ2');
        $hoja1->mergeCells('AK1:AM2');
        $hoja1->mergeCells('AN1:AO2');
        $hoja1->mergeCells('AP1:AQ2');
        $hoja1->mergeCells('AR1:AU2');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('A1', 'ALINEACIÓN PED');
        $hoja1->setCellValue('G1', 'MATRÍZ DE INDICADORES PARA RESULTADOS');
        $hoja1->setCellValue('R1', 'DATOS DEL INDICADOR');
        $hoja1->setCellValue('AK1', 'DATOS DE LA VARIABLE META');
        $hoja1->setCellValue('AN1', 'LÍNEA BASE DE LA META DEL INDICADOR');
        $hoja1->setCellValue('AP1', 'VALOR PROGRAMADO ANUAL');
        $hoja1->setCellValue('AR1', 'VALOR PROGRAMADO TRIMESTRAL (METAS)');

        $this->titulos_columnas = array(
            'ID EJE',
            'EJE',
            'ID TEMA',
            'TEMA',
            'ID OBJETIVO',
            'OBJETIVO',
            'ID UR RESPONSABLE DEL PROGRAMA',
            'NOMBRE UR RESPONSABLE DEL PROGRAMA',
            'OBJETIVO INSTITUCIONAL',
            'ID PROGRAMA',
            'PROGRAMA',
            'RESUMEN NARRATIVO DE LA MIR (NIVEL FIN)',
            'RESUMEN NARRATIVO DE LA MIR (NIVEL PROPÓSITO',
            'ID COMPONENTE',
            'COMPONENTE',
            'ID ACTIVIDAD',
            'ACTIVIDAD',
            'ID_INDICADOR',
            'NOMBRE DEL INDICADOR',
            'DEFINICIÓN DEL INDICADOR',
            'MÉTODO DE CÁLCULO',
            'ID_TIPO_INDICADOR',
            'TIPO',
            'ID_TIPO_CÁLCULO',
            'TIPO DE CÁLCULO',
            'ID_DIMENSIÓN',
            'DIMENSIÓN',
            'ID_FRECUENCIA_MEDICIÓN',
            'FRECUENCIA DE MEDICIÓN',
            'ID_SENTIDO',
            'SENTIDO',
            'ID_TIPO_VALOR',
            'TIPO VALOR',
            'ID UR PARTICIPANTE DE LA ACTIVIDAD',
            'MEDIOS DE VERIFICACIÓN/FUENTE INFORMACIÓN',
            'SUPUESTO',
            'NOMBRE DE LA VARIABLE META',
            'ID UNIDAD DE MEDIDA',
            'UNIDAD DE MEDIDA',
            'AÑO LÍNEA BASE',
            'VALOR LÍNEA BASE',
            'AÑO',
            'VALOR PROGRAMADO',
            '1er. TRIMESTRE',
            '2do. TRIMESTRE',
            '3er. TRIMESTRE',
            '4to. TRIMESTRE',
        );

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A3'         // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se cambio el color de la fuente
        $hoja1->getStyle('A1:AU3')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:AU3')->getFont()->setBold(true);
        
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
                    'argb' => '002060',
                ]
            ],
            'font' => [
                'bold' => true
            ],

        ];
        // Aplicar estilos a las columnas de la cabecera, desde un arreglo.
        $hoja1->getStyle('A1:AU3')->applyFromArray($styleArray);
        $hoja1->getStyle('A1:AU3')->getFont()->setSize('10');
        $hoja1->getStyle('A1:AU3')->getAlignment()->setWrapText(true);

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(10);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(10);
        $hoja1->getColumnDimension('D')->setWidth(35);
        $hoja1->getColumnDimension('E')->setWidth(17);
        $hoja1->getColumnDimension('F')->setWidth(35);
        $hoja1->getColumnDimension('G')->setWidth(15);
        $hoja1->getColumnDimension('H')->setWidth(28);
        $hoja1->getColumnDimension('I')->setWidth(15);
        $hoja1->getColumnDimension('J')->setWidth(13);
        $hoja1->getColumnDimension('K')->setWidth(25);
        $hoja1->getColumnDimension('L')->setWidth(28);
        $hoja1->getColumnDimension('M')->setWidth(28);
        $hoja1->getColumnDimension('N')->setWidth(14);
        $hoja1->getColumnDimension('O')->setWidth(20);
        $hoja1->getColumnDimension('P')->setWidth(16);
        $hoja1->getColumnDimension('Q')->setWidth(20);
        $hoja1->getColumnDimension('R')->setWidth(13);
        $hoja1->getColumnDimension('S')->setWidth(25);
        $hoja1->getColumnDimension('T')->setWidth(25);
        $hoja1->getColumnDimension('U')->setWidth(25);
        $hoja1->getColumnDimension('V')->setWidth(18);
        $hoja1->getColumnDimension('W')->setWidth(18);
        $hoja1->getColumnDimension('X')->setWidth(18);
        $hoja1->getColumnDimension('Y')->setWidth(18);
        $hoja1->getColumnDimension('Z')->setWidth(15);
        $hoja1->getColumnDimension('AA')->setWidth(15);
        $hoja1->getColumnDimension('AB')->setWidth(15);
        $hoja1->getColumnDimension('AC')->setWidth(12);
        $hoja1->getColumnDimension('AD')->setWidth(12);
        $hoja1->getColumnDimension('AE')->setWidth(20);
        $hoja1->getColumnDimension('AF')->setWidth(20);
        $hoja1->getColumnDimension('AG')->setWidth(8);
        $hoja1->getColumnDimension('AH')->setWidth(25);
        $hoja1->getColumnDimension('AI')->setWidth(65);
        $hoja1->getColumnDimension('AJ')->setWidth(65);
        $hoja1->getColumnDimension('AK')->setWidth(30);
        $hoja1->getColumnDimension('AL')->setWidth(15);
        $hoja1->getColumnDimension('AM')->setWidth(20);
        $hoja1->getColumnDimension('AN')->setWidth(15);
        $hoja1->getColumnDimension('AO')->setWidth(15);
        $hoja1->getColumnDimension('AP')->setWidth(15);
        $hoja1->getColumnDimension('AQ')->setWidth(15);
        $hoja1->getColumnDimension('AR')->setWidth(15);
        $hoja1->getColumnDimension('AS')->setWidth(15);
        $hoja1->getColumnDimension('AT')->setWidth(15);
        $hoja1->getColumnDimension('AU')->setWidth(15);
         
    }


    public function consultaHoja1(){
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $conexion->setSql("
        -- PERFIL Actividad
        SELECT
        eje_id,
        eje_nombre,
        tem_id,
        tem_nombre,
        obj_id,
        obj_nombre,
        CONCAT( caru.gpo_id, caru.ur_id ) AS UR,
        caru.ur_nombre as ur_nombre,
        ur_objetivoGeneral,
        catp.prgs_id as prgs_id,
        prgs_nombre,
        mir_resumenNarrativo,
        vpm_resumenNarrativo,
        csp.pdf_id as pdf_id,
        pdf_mir componente,
        vpii.pdi_id as pdi_id,
        pdi_nombre,
        ind_id,
        ind_nombreMIR,
        ind_definicionMIR,
        ind_formulaCalculo,
        tpi_id,
        tpi_nombre,
        umi_id,
        umi_nombre,
        dim_id,
        dim_nombre,
        frm_id,
        frm_nombre,
        ses_id,
        ses_nombre,
        tiv_id,
        tiv_nombre,
        CONCAT( mam.gpo_id, mam.ur_id ) AS urm,
        mvac_nombre,
        spa_nombre,
        macm_descripcion,
        umv_id,
        umv_nombre,
        macm_anio,
        macm_valor,
        '2022' AS anio,
        macm_original,
        ori_trimestre1,
        ori_trimestre2,
        ori_trimestre3,
        ori_trimestre4 
    FROM
        cat_Programas catp
        INNER JOIN vin_ProgramasMir USING ( prgs_id )
        INNER JOIN vin_Programa USING ( prgs_id )
        INNER JOIN cat_UnidadResponsable caru USING ( gpo_id, ur_id )
        INNER JOIN aux_UnidadResponsable USING ( gpo_id, ur_id )
        INNER JOIN enc_PedObjetivo po USING ( epo_id )
        INNER JOIN cat_Eje USING ( eje_id )
        INNER JOIN cat_Tema USING ( eje_id, tem_id )
        INNER JOIN cat_Objetivo USING ( eje_id, tem_id, obj_id )
        INNER JOIN cat_Mir USING ( mir_id )
        INNER JOIN cat_Subprogramas AS csp USING ( prgs_id )
        INNER JOIN enc_UrSubprogramas sp USING ( prgs_id, sprs_id )
        INNER JOIN cat_ProductoFinal USING ( pdf_id )
        INNER JOIN vin_ProductoIntermedioIndicador vpii USING ( pdf_id )
        INNER JOIN cat_ProductoIntermedio cpi USING ( pdi_id )
        INNER JOIN cat_Indicador USING ( nim_id, ind_id )
        INNER JOIN cat_TipoIndicador USING ( tpi_id )
        INNER JOIN cat_UnidadMedidaIndicador USING ( umi_id )
        INNER JOIN cat_UnidadMedidaVariable USING ( umv_id )
        INNER JOIN cat_Dimension USING ( dim_id )
        INNER JOIN cat_FrecuenciaMedicion USING ( frm_id )
        INNER JOIN cat_SentidoEsperado USING ( ses_id )
        INNER JOIN cat_TipoValor USING ( tiv_id )
        INNER JOIN vin_ProductoFinalProductoIntermedio vpfi USING ( pdf_id, pdi_id )
        INNER JOIN cat_MetaActividadMir mam USING ( pii_id )
        INNER JOIN cat_MedioVerificacionActividad AS mv ON (
            mam.gpo_id = mv.gpo_id 
            AND mam.ur_id = mv.ur_id 
            AND catp.prgs_id = mv.prgs_id 
            AND csp.sprs_id = mv.sprs_id 
            AND csp.pdf_id = mv.pdf_id 
            AND cpi.pdi_id = mv.pdi_id 
        )
        INNER JOIN cat_SupuestoActividad AS sa ON (
            mam.gpo_id = sa.gpo_id 
            AND mam.ur_id = sa.ur_id 
            AND catp.prgs_id = sa.prgs_id 
            AND csp.sprs_id = sa.sprs_id 
            AND csp.pdf_id = sa.pdf_id 
            AND cpi.pdi_id = sa.pdi_id 
        )
        INNER JOIN (
        SELECT
            macm_id,-- pii_id
            SUM( IF ( tri_id = 1, macmt_original, 0 ) ) AS ori_trimestre1,
            SUM( IF ( tri_id = 2, macmt_original, 0 ) ) AS ori_trimestre2,
            SUM( IF ( tri_id = 3, macmt_original, 0 ) ) AS ori_trimestre3,
            SUM( IF ( tri_id = 4, macmt_original, 0 ) ) AS ori_trimestre4 
        FROM
            cat_MetaActividadTrimestreMir 
            GROUP BY-- pii_id
            macm_id 
        ) matm USING ( macm_id ) -- ( pii_id )
        
    WHERE
        ( esr_id = 4 OR csp.sprs_id = 0 ) 
        AND csp.pdf_id != 0 /*	
        AND caru.gpo_id = 1 
        AND caru.ur_id = 22 
        AND catp.prgs_id = 139 
        AND vpii.pdi_id = 1912 
        AND pii_id = 477 
        */
        AND vpii.sts_id = 1 
        AND vpfi.sts_id = 1 
        AND nim_id = 4 
        AND mvac_trimestreCierre = 0 
        AND mvac_origen = 'P' 
        AND spa_trimestreCierre = 0 
        AND spa_origen = 'P' 
    GROUP BY
        eje_id,
        tem_id,
        obj_id,
        caru.gpo_id,
        caru.ur_id,
        catp.prgs_id,
        csp.sprs_id,
        mir_resumenNarrativo,
        vpm_resumenNarrativo,
        csp.pdf_id,
        vpii.pdi_id,
        ind_id,
        mam.gpo_id,
        mam.ur_id,
        mvac_id,
        spa_id,
        macm_id,
        macm_descripcion,
        macm_anio,
        macm_valor,
        macm_original 
    ORDER BY
        eje_id,
        tem_id,
        UR,
        catp.prgs_id,
        csp.pdf_id,
        vpii.pdi_id,
        ind_id,
        urm
                ;");
    
        $conexion->consultar();
        $conexion->desconectar();
        
        $eje_id = '';               // $value['eje_id']
        $prgs_id = '';      //$value['prgs_id']
        $componente = ''; //               $value['pdf_id'],
        $actividad = ''; //                 $value['pdi_id'],
        $indicador = '';  //                 $value['ind_id'],  
        $urm = ''; //  $value['urm']  
        $datos = array();

        while ($value = $conexion->extraer()) {
            $medios = $value['mvac_nombre'];
            $supuesto = $value['spa_nombre'];
            //Se suman los medios de verificacion/ supuestos, en caso de que pertenezcan al mismo indicador
            // $eje_id == $value['eje_id'] && $prgs_id == $value['prgs_id'] && $componente == $value['pdf_id']
            //&& $actividad == $value['pdi_id']
            if($componente ==  $value['pdf_id'] && $actividad == $value['pdi_id'] && $indicador == $value['ind_id'] && $urm == $value['urm']){
                $anterior = array_pop($datos);

                if ($anterior['mvac_nombre'] != $medios){
                    $m = strpos($anterior['mvac_nombre'], $medios);
                    if ( $m === false){
                        $medios = $anterior['mvac_nombre'] . "\n\n" . $medios;
                    }else{
                        $medios = $anterior['mvac_nombre'];
                    }
                }

                if ($anterior['spa_nombre'] != $medios){
                    $s = strpos($anterior['spa_nombre'], $supuesto);
                    if($s === false){
                        $supuesto = $anterior['spa_nombre'] . "\n\n" . $supuesto;
                    }else{
                        $supuesto = $anterior['spa_nombre'];
                    }
                }
                
            }
            $dato = array(
                'eje_id' => $value['eje_id'],
                'eje_nombre' => $value['eje_nombre'],
                'tem_id' => $value['tem_id'],
                'tem_nombre' => $value['tem_nombre'],
                'obj_id' => $value['obj_id'],
                'obj_nombre' => $value['obj_nombre'],
                'UR' => $value['UR'],
                'ur_nombre' => $value['ur_nombre'],
                'ur_objetivoGeneral' => $value['ur_objetivoGeneral'],
                'prgs_id' => $value['prgs_id'],
                'prgs_nombre' => $value['prgs_nombre'],
                'mir_resumenNarrativo' => $value['mir_resumenNarrativo'],
                'vpm_resumenNarrativo' => $value['vpm_resumenNarrativo'],
                'pdf_id' => $value['pdf_id'],
                'componente' => $value['componente'],
                'pdi_id' => $value['pdi_id'],
                'pdi_nombre' => $value['pdi_nombre'],
                'ind_id' => $value['ind_id'],
                'ind_nombreMIR' => $value['ind_nombreMIR'],
                'ind_definicionMIR' => $value['ind_definicionMIR'],
                'ind_formulaCalculo' => $value['ind_formulaCalculo'],
                'tpi_id' => $value['tpi_id'],
                'tpi_nombre' => $value['tpi_nombre'],
                'umi_id' => $value['umi_id'],
                'umi_nombre' => $value['umi_nombre'],
                'dim_id' => $value['dim_id'],
                'dim_nombre' => $value['dim_nombre'],
                'frm_id' => $value['frm_id'],
                'frm_nombre' => $value['frm_nombre'],
                'ses_id' => $value['ses_id'],
                'ses_nombre' => $value['ses_nombre'],
                'tiv_id' => $value['tiv_id'],
                'tiv_nombre' => $value['tiv_nombre'],
                'urm' => $value['urm'],
                'mvac_nombre' => $medios,
                'spa_nombre' => $supuesto,
                'macm_descripcion' => $value['macm_descripcion'],
                'umv_id' => $value['umv_id'],
                'umv_nombre' => $value['umv_nombre'],
                'macm_anio' => $value['macm_anio'],
                'macm_valor' => $value['macm_valor'],
                'anio' => $value['anio'],
                'macm_original' => $value['macm_original'],
                'ori_trimestre1' => $value['ori_trimestre1'],
                'ori_trimestre2' => $value['ori_trimestre2'],
                'ori_trimestre3' => $value['ori_trimestre3'],
                'ori_trimestre4' => $value['ori_trimestre4'],
            );
            array_push($datos, $dato);

            $eje_id = $value['eje_id'];               // $value['eje_id']
            $prgs_id = $value['prgs_id'];      //$value['prgs_nombre']
            $componente = $value['pdf_id']; //               $value['pdf_id'],
            $actividad = $value['pdi_id']; //                 $value['pdi_id'],
            $indicador = $value['ind_id'];  //                 $value['ind_id'],  
            $urm = $value['urm'] ; //  $value['urm'] 
        }

        return $datos;
    }

}   

$exp_componente = new exp_Programas();