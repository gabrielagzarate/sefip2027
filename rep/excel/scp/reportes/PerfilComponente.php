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
        $this->output_web_browser("PerfilComponente.xlsx");
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
                'A3'// Rango de celda o celda en donde deseamos que comience a pintar
            );

        $this->spreadsheet->getActiveSheet()->getStyle('A3:AV' . (count($datos) + 2))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->spreadsheet->getActiveSheet()->getStyle('AE2:AF' . (count($datos) + 2))
            ->getAlignment()->setWrapText(true);  
    }

    function  encabezadoHoja1(){
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Nivel Componentes');

        //Se agregan los encabezados
        $hoja1->mergeCells('A1:AM1');
        $hoja1->mergeCells('AN1:AQ1');
        $hoja1->mergeCells('AR1:AU1');
        //Establecemos la informacion a la celda combinada.
        $hoja1->setCellValue('AN1', 'VALOR PROGRAMADO DE LA VARIABLE META Porcentaje (Numerador) Tasa (T1)');

        $hoja1->setCellValue('AR1', 'VALOR PROGRAMADO Porcentaje (Denominador) Tasa (T0)');

        $this->titulos_columnas = array(
            'ID EJE',
            'EJE',
            'ID TEMA',
            'TEMA',
            'ID UR RESPONSABLE DEL PROGRAMA',
            'NOMBRE UR RESPONSABLE DEL PROGRAMA',
            'ID_PROG',
            'NOMBRE_PROGRAMA',
            'ID COMPONENTE',
            'COMPONENTE',
            'ID_INDICADOR',
            'NOMBRE_INDICADOR',
            'DEFINICION_INDICADOR',
            'METODO CALCULO',
            'ID_TIPO_INDICADOR',
            'TIPO_INDICADOR',
            'ID_DIMENSIÓN',
            'DIMENSION',
            'ID_FRECUENCIA_MEDICION',
            'FRECUENCIA_MEDICION',
            'ID_SENTIDO',
            'SENTIDO',
            'ID_TIPO_CALCULO',
            'TIPO_CALCULO',
            'ID_TIPO_DE_VALOR',
            'TIPO_DE_VALOR',
            'AÑO_LINEA_BASE',
            'VALOR_LINEA_BASE',
            'AÑO ACTUAL',
            'META (VALOR DESEADO)',
            'MEDIO_DE_VERIFICACION',
            'SUPUESTO',
            'ID_UR DEL COMPONENTE',
            'UR_RESPONSABLE DEL COMPONENTE',
            'NOMBRE DE LA VARIABLE META',
            'ID UNIDAD DE MEDIDA',
            'UNIDAD DE MEDIDA',
            'VALOR DESEADO DE LA VARIABLE META Porcentaje (Numerador) Tasa (Valor Año T1)',
            'VALOR DESEADO Porcentaje (Denominador) Tasa (Valor Año T0)',
            '1º Trimestre',
            '2º Trimestre',
            '3º Trimestre',
            '4º Trimestre',
            '1º Trimestre',
            '2º Trimestre',
            '3º Trimestre',
            '4º Trimestre',
        );

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A2'         // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se cambio el color de la fuente
        $hoja1->getStyle('A1:AU2')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:AU2')->getFont()->setBold(true);
        
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
        $hoja1->getStyle('A1:AU2')->applyFromArray($styleArray);
        $hoja1->getStyle('A1:AU2')->getFont()->setSize('10');
        $hoja1->getStyle('A1:AU2')->getAlignment()->setWrapText(true);

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(10);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(10);
        $hoja1->getColumnDimension('D')->setWidth(35);
        $hoja1->getColumnDimension('E')->setWidth(17);
        $hoja1->getColumnDimension('F')->setWidth(35);
        $hoja1->getColumnDimension('G')->setWidth(10);
        $hoja1->getColumnDimension('H')->setWidth(28);
        $hoja1->getColumnDimension('I')->setWidth(12);
        $hoja1->getColumnDimension('J')->setWidth(13);
        $hoja1->getColumnDimension('K')->setWidth(12);
        $hoja1->getColumnDimension('L')->setWidth(28);
        $hoja1->getColumnDimension('M')->setWidth(28);
        $hoja1->getColumnDimension('N')->setWidth(12);
        $hoja1->getColumnDimension('O')->setWidth(15);
        $hoja1->getColumnDimension('P')->setWidth(16);
        $hoja1->getColumnDimension('Q')->setWidth(13);
        $hoja1->getColumnDimension('R')->setWidth(13);
        $hoja1->getColumnDimension('S')->setWidth(15);
        $hoja1->getColumnDimension('T')->setWidth(15);
        $hoja1->getColumnDimension('U')->setWidth(15);
        $hoja1->getColumnDimension('V')->setWidth(13);
        $hoja1->getColumnDimension('W')->setWidth(18);
        $hoja1->getColumnDimension('X')->setWidth(12);
        $hoja1->getColumnDimension('Y')->setWidth(18);
        $hoja1->getColumnDimension('Z')->setWidth(10);
        $hoja1->getColumnDimension('AA')->setWidth(15);
        $hoja1->getColumnDimension('AB')->setWidth(10);
        $hoja1->getColumnDimension('AC')->setWidth(12);
        $hoja1->getColumnDimension('AD')->setWidth(12);
        $hoja1->getColumnDimension('AE')->setWidth(120);
        $hoja1->getColumnDimension('AF')->setWidth(120);
        $hoja1->getColumnDimension('AG')->setWidth(8);
        $hoja1->getColumnDimension('AH')->setWidth(28);
        $hoja1->getColumnDimension('AI')->setWidth(50);
        $hoja1->getColumnDimension('AJ')->setWidth(10);
        $hoja1->getColumnDimension('AK')->setWidth(20);
        $hoja1->getColumnDimension('AL')->setWidth(15);
        $hoja1->getColumnDimension('AM')->setWidth(15);
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
        -- PERFIL COMPONENTES
        SELECT 
            eje_id,
            eje_nombre,
            tem_id,
            tem_nombre,
            CONCAT( caru.gpo_id, caru.ur_id ) AS UR,
            caru.ur_nombre,
            catp.prgs_id,
            prgs_nombre,
            pdf_id,
            pdf_mir componente,
            ind_id,
            ind_nombreMIR,
            ind_definicionMIR,
            ind_formulaCalculo,
            tpi_id,
            tpi_nombre,
            dim_id,
            dim_nombre,
            frm_id,
            frm_nombre,
            ses_id,
            ses_nombre,
            umi_id,
            umi_nombre,
            tiv_id,
            tiv_nombre,
            CONCAT( mc.gpo_id, mc.ur_id ) urs,
            cur2.ur_nombre AS ur_nombre2,
            mco_descripcion,
            ind_nombreMIR AS nmir,
            mc.mco_lb AS mco_lb,
            ROUND( mc.mco_lbIndicador, 4 ) AS mco_lbIndicador,
            '2022' AS anio_actual,
			ROUND( mc.mco_lbValor, 4) AS mco_lbValor,
            ROUND( mc.mco_original, 4 ) AS mco_original,
			ROUND( mc.mco_anteriorIndicador, 4 ) AS mco_anteriorIndicador,
            ROUND( mc.mco_anteriorValor, 4 ) AS mco_anteriorValor,
			mc.mco_anterior,
            mvcm_nombre,
            spc_nombre,
            umv_id,
            umv_nombre,
            MAX( mcot_numeradorT2 ) AS mcot_numeradorT2,-- $trimestre2Cal1 numerador deseado
            MAX( mcot_numeradorT4 ) AS mcot_numeradorT4,-- $trimestre4Cal1
            MAX( mcot_denominadorT2 ) AS mcot_denominadorT2,-- $trimestre2Cal2 denominador deseado
            MAX( mcot_denominadorT4 ) AS mcot_denominadorT4,
            MAX( mcot_modificadoT2 ) AS mcot_modificadoT2,
            MAX( mcot_modificadoT4 ) AS mcot_modificadoT4 
        FROM
            cat_Programas catp
            INNER JOIN vin_ProgramasMir USING ( prgs_id )
            INNER JOIN vin_Programa USING ( prgs_id )
            INNER JOIN cat_UnidadResponsable caru USING ( gpo_id, ur_id )
            INNER JOIN enc_PedObjetivo po USING ( epo_id )
            INNER JOIN cat_Eje USING ( eje_id )
            INNER JOIN cat_Tema USING ( eje_id, tem_id )
            INNER JOIN cat_Subprogramas AS csp USING ( prgs_id )
            INNER JOIN enc_UrSubprogramas sp USING ( prgs_id, sprs_id )
            INNER JOIN cat_ProductoFinal USING ( pdf_id )
            INNER JOIN enc_ProductoFinalIndicador enc USING ( pdf_id )
            INNER JOIN cat_Indicador USING ( ind_id, nim_id )
            INNER JOIN cat_TipoIndicador USING ( tpi_id )
            INNER JOIN cat_UnidadMedidaIndicador USING ( umi_id )
            INNER JOIN cat_UnidadMedidaVariable USING ( umv_id )
            INNER JOIN cat_Dimension USING ( dim_id )
            INNER JOIN cat_FrecuenciaMedicion USING ( frm_id )
            INNER JOIN cat_SentidoEsperado USING ( ses_id )
            INNER JOIN cat_TipoValor USING ( tiv_id )
            INNER JOIN cat_MetaComponente AS mc USING ( epfi_id, prgs_id )
            INNER JOIN cat_MedioVerificacionComponente AS mv ON (
                mc.gpo_id = mv.gpo_id 
                AND mc.ur_id = mv.ur_id 
                AND mc.prgs_id = mv.prgs_id 
                AND mc.sprs_id = mv.sprs_id 
            )
            INNER JOIN cat_SupuestoComponente AS sc ON (
                mc.gpo_id = sc.gpo_id 
                AND mc.ur_id = sc.ur_id 
                AND mc.prgs_id = sc.prgs_id 
                AND mc.sprs_id = sc.sprs_id 
            )
            INNER JOIN cat_UnidadResponsable AS cur2 ON ( mc.gpo_id = cur2.gpo_id AND mc.ur_id = cur2.ur_id )
            INNER JOIN aux_HistoricoModificacionMetaComponente AS aux ON (
                mc.gpo_id = aux.gpo_id 
                AND mc.ur_id = aux.ur_id 
                AND mc.prgs_id = aux.prgs_id 
                AND mc.sprs_id = aux.sprs_id 
                AND mc.epfi_id = aux.epfi_id 
            ) 
        WHERE
            ( esr_id = 4 OR sp.sprs_id = 0 ) 
            AND pdf_id != 0 -- AND sp.gpo_id = 1
            AND enc.sts_id = 1 
            AND nim_id = 3 
            AND mvcm_trimestreCierre = 0 
            AND mvcm_origen = 'P' 
            AND spc_trimestreCierre = 0 
            AND spc_origen = 'P' 
            AND tri_id = 0 
        GROUP BY
            caru.gpo_id,
            caru.ur_id,
            catp.prgs_id,
            pdf_id,
            enc.ind_id,
            mc.gpo_id,
            mc.ur_id,
            mc.mco_descripcion,
            mc.mco_lb,
            mc.mco_lbIndicador,
            mc.mco_anteriorIndicador,
            mc.mco_anteriorValor,
			mc.mco_lbValor,
			mc.mco_original,
			mc.mco_anterior,
            mv.mvcm_nombre,
            sc.spc_nombre 
        ORDER BY
            eje_id,
            tem_id,
            UR,
            prgs_id,
            pdf_id,
            enc.ind_id,
            urs
                ;");
    
        $conexion->consultar();
        $conexion->desconectar();

        $prgs_id = '';      //$value['prgs_id']
        $componente = ''; //               $value['pdf_id'],
        $indicador = '';  //                 $value['ind_id'],  
        $urs = '';
        $datos = array();

        while ($value = $conexion->extraer()) {
            $medios = $value['mvcm_nombre'];
            $supuesto = $value['spc_nombre'];

            $mco_lb = "";
            $mco_lbIndicador = number_format($value['mco_lbIndicador'],4);
            $mco_original = number_format($value['mco_original'],4);
            $numerador = 0;
            $denominador = 0;
            $trimestre2V1= number_format($value['mcot_numeradorT2'],4);
            $trimestre4V1= number_format($value['mcot_numeradorT4'],4);
            $trimestre2V2= number_format($value['mcot_denominadorT2'],4);
            $trimestre4V2= number_format($value['mcot_denominadorT4'],4);

            if ($value['umi_id'] == 2 || $value['umi_id'] == 4){
                if ($value['umi_id'] == 2){
                    $mco_lbIndicador = $mco_lbIndicador . '%';
                    $mco_original = $mco_original . '%';
                }
                $mco_lb = $value['mco_lb'];
                $numerador = number_format($value['mcot_numeradorT2'] + $value['mcot_numeradorT4'],2);
                $denominador = number_format($value['mcot_denominadorT2'] + $value['mcot_denominadorT4'],2);
            }
            else if($value['umi_id'] == 3){
                $mco_lb = $value['mco_lb'];
                $numerador =  $value['mco_original'];//number_format($value['mco_original'],2);
                $denominador = '';
                $trimestre2V1= number_format($value['mcot_modificadoT2'],4);
                $trimestre4V1= number_format($value['mcot_modificadoT4'],4);
                $trimestre2V2 = '';
                $trimestre4V2 = '';
            }
            else if ($value['umi_id'] == 5){
                $mco_lb = $value['mco_anterior'];
                $mco_lbIndicador = $mco_lbIndicador . '%';
                $mco_original = number_format($value['mco_anteriorIndicador'],4) . '%';
                $numerador = number_format($value['mco_original'],2);
                $denominador = number_format($value['mco_anteriorValor'],2);
                if ($trimestre2V2 == 0){
                    $trimestre2V2 == "";
                }
            }

            if($urs == $value['urs'] && $componente ==  $value['pdf_id'] && $indicador == $value['ind_id'] && $prgs_id == $value['prgs_id']){
                $anterior = array_pop($datos);

                if ($anterior['mvcm_nombre'] != $medios){
                    $m = strpos($anterior['mvcm_nombre'], $medios);
                    if ( $m === false){
                        $medios = $anterior['mvcm_nombre'] . "\n\n" . $medios;
                    }else{
                        $medios = $anterior['mvcm_nombre'];
                    }
                }

                if ($anterior['spc_nombre'] != $medios){
                    $s = strpos($anterior['spc_nombre'], $supuesto);
                    if($s === false){
                        $supuesto = $anterior['spc_nombre'] . "\n\n" . $supuesto;
                    }else{
                        $supuesto = $anterior['spc_nombre'];
                    }
                }
                
            }

            $dato = array(
                'eje_id' => $value['eje_id'],
                'eje_nombre' => $value['eje_nombre'],
                'tem_id' => $value['tem_id'],
                'tem_nombre' => $value['tem_nombre'],
                'UR' => $value['UR'],
                'ur_nombre' => $value['ur_nombre'],
                'prgs_id' => $value['prgs_id'],
                'prgs_nombre' => $value['prgs_nombre'],
                'pdf_id' => $value['pdf_id'],
                'componente' => $value['componente'],
                'ind_id' => $value['ind_id'],
                'ind_nombreMIR' => $value['ind_nombreMIR'],
                'ind_definicionMIR' => $value['ind_definicionMIR'],
                'ind_formulaCalculo' => $value['ind_formulaCalculo'],
                'tpi_id' => $value['tpi_id'],
                'tpi_nombre' => $value['tpi_nombre'],
                'dim_id' => $value['dim_id'],
                'dim_nombre' => $value['dim_nombre'],
                'frm_id' => $value['frm_id'],
                'frm_nombre' => $value['frm_nombre'],
                'ses_id' => $value['ses_id'],
                'ses_nombre' => $value['ses_nombre'],
                'umi_id' => $value['umi_id'],
                'umi_nombre' => $value['umi_nombre'],
                'tiv_id' => $value['tiv_id'],
                'tiv_nombre' => $value['tiv_nombre'],
                'mco_lb' => $mco_lb,
                'mco_lbIndicador' =>  $mco_lbIndicador,
                'anio_actual' => $value['anio_actual'],
                'mco_original' => $mco_original,
                'mvcm_nombre' => $medios,
                'spc_nombre' => $supuesto,
                'urs' => $value['urs'],
                'ur_nombre2' => $value['ur_nombre2'],
                'mco_descripcion' => $value['mco_descripcion'],
                'umv_id' => $value['umv_id'],
                'umv_nombre' => $value['umv_nombre'],
                'numerador' => $numerador,
                'denominador' => $denominador,
                't1' => "",
                'trimestre2V1' => $trimestre2V1,
                't3' => "",
                'trimestre4V1' => $trimestre4V1,
                't5' => "",
                'trimestre2V2' => $trimestre2V2,
                't7' => "",
                'trimestre4V2' => $trimestre4V2,

            );
            array_push($datos, $dato);

            $prgs_id = $value['prgs_id'];      //$value['prgs_nombre']
            $componente = $value['pdf_id']; //               $value['pdf_id'],
            $indicador = $value['ind_id'];  //                 $value['ind_id'], 
            $urs = $value['urs'];
        }

        return $datos;
    }

}   

$exp_componente = new exp_Programas();