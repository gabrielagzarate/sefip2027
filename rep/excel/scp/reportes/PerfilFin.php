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
        $this->output_web_browser("PerfilFin.xlsx");
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
                'A2'// Rango de celda o celda en donde deseamos que comience a pintar
            );

        $this->spreadsheet->getActiveSheet()->getStyle('A2:AD' . (count($datos) + 1))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $this->spreadsheet->getActiveSheet()->getStyle('AC2:AD' . (count($datos) + 1))
            ->getAlignment()->setWrapText(true);
    }

    function  encabezadoHoja1(){
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Indicadores_Nivel Fin');

        //Se agregan los encabezados

        $this->titulos_columnas = array(
            'ID EJE',
            'EJE',
            'ID TEMA',
            'TEMA',
            'ID UR RESPONSABLE DEL PROGRAMA',
            'NOMBRE UR RESPONSABLE DEL PROGRAMA',
            'ID_PROG',
            'NOMBRE_PROGRAMA',
            'RESUMEN NARRATIVO DE LA MIR (NIVEL FIN)',
            'ID_INDICADOR',
            'NOMBRE_INDICADOR',
            'DEFINICION_INDICADOR',
            'METODO_CALCULO',
            'ID_TIPO_INDICADOR',
            'TIPO_INDICADOR',
            'ID_TIPO_CALCULO',
            'TIPO_CALCULO',
            'ID_DIMENSION',
            'DIMENSION',
            'ID_FRECUENCIA_MEDICION',
            'FRECUENCIA_MEDICION',
            'ID_SENTIDO',
            'SENTIDO',
            'ID_TIPO_DE_VALOR',
            'TIPO DE VALOR',
            'AÑO LINEA BASE',
            'VALOR LINEA BASE',
            'META (VALOR DESEADO)',
            'MEDIO DE VERIFICACION',
            'SUPUESTO',
        );

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A1'         // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se cambio el color de la fuente
        $hoja1->getStyle('A1:AD1')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:AD1')->getFont()->setBold(true);
        //Se cambia el tamaño de la fuente
        $hoja1->getStyle('A1:AD1')->getFont()->setSize('11');

        
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
        $hoja1->getStyle('A1:AD1')->applyFromArray($styleArray);
        $hoja1->getStyle('A1:AD1')->getFont()->setSize('10');
        $hoja1->getStyle('A1:AD1')->getAlignment()->setWrapText(true);

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(10);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(10);
        $hoja1->getColumnDimension('D')->setWidth(35);
        $hoja1->getColumnDimension('E')->setWidth(17);
        $hoja1->getColumnDimension('F')->setWidth(35);
        $hoja1->getColumnDimension('G')->setWidth(10);
        $hoja1->getColumnDimension('H')->setWidth(28);
        $hoja1->getColumnDimension('I')->setWidth(30);
        $hoja1->getColumnDimension('J')->setWidth(13);
        $hoja1->getColumnDimension('K')->setWidth(28);
        $hoja1->getColumnDimension('L')->setWidth(28);
        $hoja1->getColumnDimension('M')->setWidth(28);
        $hoja1->getColumnDimension('N')->setWidth(12);
        $hoja1->getColumnDimension('O')->setWidth(15);
        $hoja1->getColumnDimension('P')->setWidth(12);
        $hoja1->getColumnDimension('Q')->setWidth(12);
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
        $hoja1->getColumnDimension('AC')->setWidth(65);
        $hoja1->getColumnDimension('AD')->setWidth(65);
         
    }


    public function consultaHoja1(){
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $conexion->setSql("
        -- PERFIL FIN
    SELECT
        * 
    FROM
        (
        SELECT
            eje_id,
            eje_nombre,
            tem_id,
            tem_nombre,
            CONCAT( cur.gpo_id, cur.ur_id ) AS UR,
            ur_nombre,
            cp.prgs_id,
            prgs_nombre,
            mir_resumenNarrativo,
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
            mtf_anio,
            ROUND( mtf_valor, 4 ) AS mem_valor,
            ROUND( mtf_original, 4 ) AS mem_original,
            mvpm_nombre,
            spm_nombre 
        FROM
            cat_Programas cp
            INNER JOIN vin_ProgramasMir vp USING ( prgs_id )
            INNER JOIN vin_Programa USING ( prgs_id )
            INNER JOIN cat_UnidadResponsable cur USING ( gpo_id, ur_id )
            INNER JOIN enc_PedObjetivo po USING ( epo_id )
            INNER JOIN cat_Eje USING ( eje_id )
            INNER JOIN cat_Tema USING ( eje_id, tem_id )
            INNER JOIN cat_Mir USING ( mir_id )
            INNER JOIN vin_ProgramaMirIndicador v USING ( vpm_id )
            INNER JOIN cat_Indicador USING ( ind_id, nim_id )
            INNER JOIN cat_TipoIndicador USING ( tpi_id )
            INNER JOIN cat_UnidadMedidaIndicador USING ( umi_id )
            INNER JOIN cat_Dimension USING ( dim_id )
            INNER JOIN cat_FrecuenciaMedicion USING ( frm_id )
            INNER JOIN cat_SentidoEsperado USING ( ses_id )
            INNER JOIN cat_TipoValor USING ( tiv_id )
            INNER JOIN cat_MetaFin USING ( pmi_id )
            INNER JOIN cat_MedioVerificacionProgramaMir mv USING ( vpm_id, nim_id )
            INNER JOIN cat_SupuestoProgramaMir sv USING ( vpm_id, nim_id ) 
        WHERE
            v.nim_id = 1 
            AND mvpm_trimestreCierre = 0 
            AND mvpm_origen = 'P' 
            AND spm_origen = 'P' 
            AND spm_trimestreCierre = 0 
        ORDER BY
            eje_id,
            tem_id,
            UR,
            prgs_id,
            ind_id 
        ) AS q 
    GROUP BY
        eje_id,
        eje_nombre,
        tem_id,
        tem_nombre,
        UR,
        ur_nombre,
        prgs_id,
        prgs_nombre,
        mir_resumenNarrativo,
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
        mtf_anio,
        mem_valor,
        mem_original,
        mvpm_nombre,
        spm_nombre
			;");

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();
        
        $eje_id = '';               // $value['eje_id']
        $ur = '';           //$value['UR']
        $prgs_id = '';      //$value['prgs_nombre']
        $indicador = '';  //                 $value['ind_id'],  
        while ($value = $conexion->extraer()) {
            $medios = $value['mvpm_nombre'];
            $supuesto = $value['spm_nombre'];
            //
            if( $eje_id ==  $value['eje_id'] && $ur == $value['UR'] && $indicador == $value['ind_id'] && $prgs_id == $value['prgs_id']){
                $anterior = array_pop($datos);
                
                if ($anterior['mvpm_nombre'] != $medios){
                    $m = strpos($anterior['mvpm_nombre'], $medios);
                    if ( $m === false){
                        $medios = $anterior['mvpm_nombre'] . "\n\n" . $medios;
                    }else{
                        $medios = $anterior['mvpm_nombre'];
                    }
                }

                if ($anterior['spm_nombre'] != $supuesto){
                    $s = strpos($anterior['spm_nombre'], $supuesto);
                    if($s === false){
                        $supuesto = $anterior['spm_nombre'] . "\n\n" . $supuesto;
                    }else{
                        $supuesto = $anterior['spm_nombre'];
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
                'mir_resumenNarrativo' => $value['mir_resumenNarrativo'],
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
                'mtf_anio' => $value['mtf_anio'],
                'mem_valor' => $value['mem_valor'],
                'mem_original' => $value['mem_original'],
                'mvpm_nombre' => $medios,
                'spm_nombre' => $supuesto,
            );
            array_push($datos, $dato);

            $eje_id = $value['eje_id'];   
            $ur = $value['UR'];           
            $prgs_id = $value['prgs_id'];
            $indicador = $value['ind_id']; 
        }

        return $datos;
    }

}   

$exp_programas = new exp_Programas();