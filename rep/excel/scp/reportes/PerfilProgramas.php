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
        $this->output_web_browser("PerfilProgramas.xlsx");
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

        $this->spreadsheet->getActiveSheet()->getStyle('A2:V' . (count($datos) + 1))
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }

    function  encabezadoHoja1(){
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Fun-Prog-Pob');

        //Se agregan los encabezados

        $this->titulos_columnas = array(
            'ID PROGRAMA',
            'NOMBRE_PROGRAMA',
            'ID UR RESPONSABLE DEL PROGRAMA',
            'NOMBRE UR RESPONSABLE DEL PROGRAMA',
            'ID EJE',
            'EJE',
            'ID TEMA',
            'TEMA',
            'ID OBJETIVO',
            'OBJETIVO',
            'SECTOR',
            'SUBSECTOR',
            'TIPOLOGÍA',
            'SUBTIPOLOGÍA',
            'CLASIFICACIÓN TIPOLOGÍA',
            'FINALIDAD',
            'FUNCIÓN',
            'SUBFUNCIÓN',
            'POBLACIÓN POTENCIAL',
            'HOMBRES',
            'MUJERES',
            'TOTAL',
            
        );

        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A1'         // Rango de celda o celda en donde deseamos que comience a pintar
            );
        //Se cambio el color de la fuente
        $hoja1->getStyle('A1:V1')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:V1')->getFont()->setBold(true);
        //Se cambia el tamaño de la fuente
        $hoja1->getStyle('A1:V1')->getFont()->setSize('11');

        
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
        $hoja1->getStyle('A1:V1')->applyFromArray($styleArray);
        $hoja1->getStyle('A1:V1')->getFont()->setSize('11');
        $hoja1->getStyle('A1:V1')->getAlignment()->setWrapText(true);

        //Se define el tamaño de las columnas
        $hoja1->getColumnDimension('A')->setWidth(12);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(15);
        $hoja1->getColumnDimension('D')->setWidth(35);
        $hoja1->getColumnDimension('E')->setWidth(10);
        $hoja1->getColumnDimension('F')->setWidth(28);
        $hoja1->getColumnDimension('G')->setWidth(10);
        $hoja1->getColumnDimension('H')->setWidth(28);
        $hoja1->getColumnDimension('I')->setWidth(10);
        $hoja1->getColumnDimension('J')->setWidth(35);
        $hoja1->getColumnDimension('K')->setWidth(28);
        $hoja1->getColumnDimension('L')->setWidth(28);
        $hoja1->getColumnDimension('M')->setWidth(28);
        $hoja1->getColumnDimension('N')->setWidth(28);
        $hoja1->getColumnDimension('O')->setWidth(28);
        $hoja1->getColumnDimension('P')->setWidth(28);
        $hoja1->getColumnDimension('Q')->setWidth(28);
        $hoja1->getColumnDimension('R')->setWidth(28);
        $hoja1->getColumnDimension('S')->setWidth(28);
        $hoja1->getColumnDimension('T')->setWidth(13);
        $hoja1->getColumnDimension('U')->setWidth(13);
        $hoja1->getColumnDimension('V')->setWidth(13);
         
    }


    public function consultaHoja1(){
        $conexion = new AccesoDatos();
        $conexion->conectar();

        $conexion->setSql("
        -- PERFIL PROGRAMAS
        SELECT
            prgs_id, prgs_nombre, CONCAT(gpo_id, ur_id) as UR, ur_nombre, 
            eje_id, eje_nombre, tem_id, tem_nombre, obj_id, obj_nombre, 
            CONCAT(spe_id, ' - ',spe_nombre) as spe_id, 
            CONCAT(sbs_id, ' - ',sbs_nombre) as sbs_id,
            CONCAT(tpg_id, ' - ',tpg_nombre) as tpg_id,
            CONCAT(stp_id, ' - ',stp_nombre) as stp_id, 
            CONCAT(ctp_id, ' - ',ctp_nombre) as ctp_id,
            CONCAT(fin_id, ' - ',fin_nombre) as fin_id, 
            CONCAT(fun_id, ' - ',fun_nombre) as fun_id,
            CONCAT(sfu_id, ' - ',sfu_nombre) as sfu_id, 
            CONCAT(pbf_id, ' - ',pbf_nombre) as pbf_id, 
            prgs_hombres, prgs_mujeres, prgs_total
        FROM
            cat_Programas
            INNER JOIN vin_ProgramasMir USING ( prgs_id )
            LEFT JOIN vin_Programa USING ( prgs_id )
            LEFT JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
            LEFT JOIN enc_PedObjetivo po USING ( epo_id )
            LEFT JOIN cat_Eje USING ( eje_id )
            LEFT JOIN cat_Tema USING ( eje_id, tem_id )
            LEFT JOIN cat_Objetivo USING ( eje_id, tem_id, obj_id )
            LEFT JOIN enc_Pes USING ( spe_id, eps_id )
            LEFT JOIN enc_ProgramaPresupuestario USING ( epp_id)
            LEFT JOIN enc_Funcional USING ( efn_id )
            LEFT JOIN cat_PoblacionBeneficiada USING ( pbf_id )
            LEFT JOIN cat_SectorPes USING ( spe_id )
            LEFT JOIN cat_SubsectorPes USING ( spe_id, sbs_id )
            LEFT JOIN cat_Tipologia USING ( tpg_id )
            LEFT JOIN cat_SubTipologia USING ( tpg_id, stp_id )
            LEFT JOIN cat_ClasificacionTipologia USING ( tpg_id, stp_id, ctp_id )
            LEFT JOIN cat_Finalidad USING ( fin_id )
            LEFT JOIN cat_Funcion USING ( fin_id, fun_id )
            LEFT JOIN cat_Subfuncion USING ( fin_id, fun_id , sfu_id)
            ORDER BY prgs_id
			;");

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();

        while ($value = $conexion->extraer()) {
            $dato = array(
                'prgs_id' => $value['prgs_id'],
                'prgs_nombre' => $value['prgs_nombre'],
                'UR' => $value['UR'],
                'ur_nombre' => $value['ur_nombre'],
                'eje_id' => $value['eje_id'],
                'eje_nombre' => $value['eje_nombre'],
                'tem_id' => $value['tem_id'],
                'tem_nombre' => $value['tem_nombre'],
                'obj_id' => $value['obj_id'],
                'obj_nombre' => $value['obj_nombre'],
                'spe_id' => $value['spe_id'],
                'sbs_id' => $value['sbs_id'] ,
                'tpg_id' => $value['tpg_id'] ,
                'stp_id' => $value['stp_id'] ,
                'ctp_id' => $value['ctp_id'] ,
                'fin_id' => $value['fin_id'] ,
                'fun_id' => $value['fun_id'] ,
                'sfu_id' => $value['sfu_id'] ,
                'pbf_id' => $value['pbf_id'] ,
                'prgs_hombres' => $value['prgs_hombres'],
                'prgs_mujeres' => $value['prgs_mujeres'],
                'prgs_total' => $value['prgs_total'],
                
            );
            array_push($datos, $dato);

        }

        return $datos;
    }

}   

$exp_programas = new exp_Programas();