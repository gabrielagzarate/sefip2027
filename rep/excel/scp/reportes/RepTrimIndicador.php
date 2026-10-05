<?php

session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);
ini_set("memory_limit", '1G');
ini_set('max_execution_time', 0);


require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';


class exp_ObraPub extends Phpspreadsheet
{

    function __construct()
    {
     
        parent::__construct();

        $this->wh_toa = ' toa_id = ' . $_POST['toa_id'];
        $this->wh_gpo = '';
        if ($_POST['gpo_id'] != 'X' &&  (isset($_POST['ur_id']) && $_POST['ur_id'] != 'X')){
            $this->wh_gpo = ' WHERE gpo_id = '  . $_POST['gpo_id'] . ' and ur_id = ' . substr($_POST['ur_id'],1,2) ;
        }
        
        else if ($_POST['gpo_id'] == 'X'){
            $this->wh_gpo = '';
        }
        else if($_POST['gpo_id'] != 'X' && (isset($_POST['ur_id']) && $_POST['ur_id'] == 'X')){
            $this->wh_gpo = ' WHERE gpo_id= ' . $_POST['gpo_id'];
        }
        
        $this->hoja1();

        //Se activa la hoja 1 como principal
        $this->spreadsheet->setActiveSheetIndex(0);

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $nombre_rep = mb_convert_encoding("TrimIndOperativo.xls", 'ISO-8859-1', 'UTF-8');
        if ($_POST['toa_id'] == 2) {
            $nombre_rep = mb_convert_encoding("TrimIndInversion.xls", 'ISO-8859-1', 'UTF-8');
        }
        $this->output_web_browser($nombre_rep);
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

        $tam = count($datos) + 3;

        $this->spreadsheet->getActiveSheet()->getStyle('AC4:BD' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BX4:CR' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BU4:BU' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BW4:BW' . $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('BX4:CP' . $tam)->getAlignment()->setHorizontal('right');
  
    }

    function encabezadoHoja1()
    {
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Indicadores_Operativo');
        if ($_POST['toa_id'] == 2) {
            $hoja1->setTitle('Indicadores_Inversion');
        }

        //Se añade un ancho por defecto
        $hoja1->getDefaultColumnDimension()->setWidth(18);
        $hoja1->getStyle('A1:CR3')->getFont()->setSize('9');
        $hoja1->getStyle('A1:CR3')->getFill()->setFillType('solid')->getStartColor()->setRGB('8FC0DF');
        $hoja1->getStyle('A1:CR3')->getFont()->setBold(true);
        $hoja1->getStyle('A1:CR3')->getAlignment()->setHorizontal('center');
        $hoja1->getStyle('A1:CR3')->getAlignment()->setVertical('center');
        $hoja1->getStyle('A1:CR3')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $hoja1->getStyle('A1:CR3')->getAlignment()->setWrapText(true);

        $hoja1->getRowDimension('1')->setRowHeight(25);
        $hoja1->getRowDimension('2')->setRowHeight(25);

        $hoja1->setCellValue('A1', 'ALINEACIÓN PED');
        $hoja1->mergeCells('A1:N2');
        $hoja1->setCellValue('O1', 'MATRÍZ DE INDICADORES PARA RESULTADOS');
        $hoja1->mergeCells('O1:T2');
        $hoja1->setCellValue('U1', 'ESTRUCTURA PROGRAMATICA PRESUPUESTAL');
        $hoja1->mergeCells('U1:AB2');
        $hoja1->setCellValue('AC1', 'PRESUPUESTO AVANCE TRIMESTRAL');
        $hoja1->mergeCells('AC1:BD1');
        $hoja1->setCellValue('AC2', 'APROBADO');
        $hoja1->mergeCells('AC2:AG2');
        $hoja1->setCellValue('AH2', 'AMPLIACIÓN');
        $hoja1->mergeCells('AH2:AH3');
        $hoja1->setCellValue('AI2', 'REDUCCIÓN');
        $hoja1->mergeCells('AI2:AI3');
        $hoja1->setCellValue('AJ2', 'MODIFICADO');
        $hoja1->mergeCells('AJ2:AN2');
        $hoja1->setCellValue('AO2', 'COMPROMETIDO');
        $hoja1->mergeCells('AO2:AS2');
        $hoja1->setCellValue('AT2', 'DEVENGADO');
        $hoja1->mergeCells('AT2:AX2');
        $hoja1->setCellValue('AY2', 'EJERCIDO');
        $hoja1->mergeCells('AY2:BD2');
        $hoja1->setCellValue('BE1', 'DATOS DEL INDICADOR');
        $hoja1->mergeCells('BE1:BP2');
        $hoja1->setCellValue('BQ1', 'DATOS DE LA META');
        $hoja1->mergeCells('BQ1:BS2');
        $hoja1->setCellValue('BT1', 'LÍNEA BASE DE LA META DEL INDICADOR');
        $hoja1->mergeCells('BT1:BU2');
        $hoja1->setCellValue('BV1', 'VALOR PROGRAMADO ANUAL');
        $hoja1->mergeCells('BV1:BW2');
        $hoja1->setCellValue('BX1', 'AVANCE TRIMESTRAL METAS');
        $hoja1->mergeCells('BX1:CQ1');
        $hoja1->setCellValue('BX2', 'PROGRAMADA');
        $hoja1->mergeCells('BX2:CB2');
        $hoja1->setCellValue('CC2', 'PROGRAMACIÓN ACTUALIZADA');
        $hoja1->mergeCells('CC2:CG2');
        $hoja1->setCellValue('CH2', 'ALCANZADA');
        $hoja1->mergeCells('CH2:CL2');
        $hoja1->setCellValue('CM2', 'PORCENTAJE ALCANZADO TRIMESTRAL');
        $hoja1->mergeCells('CM2:CP2');
        $hoja1->setCellValue('CQ2', 'PORCENTAJE ALCANZADO ANUAL');
        $hoja1->mergeCells('CQ2:CQ3');
        $hoja1->setCellValue('CR1', 'PORCENTAJE DE CUMPLIMIENTO ALCANZADO EN EL TRIMESTRE');
        $hoja1->mergeCells('CR1:CR3');

        $this->titulos_columnas = array(
            'ID EJE',
            'EJE',
            'ID TEMA',
            'TEMA',
            'ID OBJETIVO',
            'OBJETIVO',
            'ESTRATEGIA',
            'ID LÍNEAS DE ACCIÓN',
            'LÍNEAS DE ACCIÓN',
            'ID UR',
            'NOMBRE UR',
            'OBJETIVO INSTITUCIONAL',
            'ID UE',
            'UE',
            'FIN',
            'PROPÓSITO',
            'ID COMPONENTE',
            'COMPONENTE',
            'ID ACTIVIDAD',
            'ACTIVIDAD',
            'ID PROGRAMA',
            'PROGRAMA',
            'ID SUBPROGRAMA',
            'SUBPROGRAMA',
            'ID PROYECTO',
            'PROYECTO',
            'ID OBRA/ACTIVIDAD',
            'OBRA/ACTIVIDAD',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '% de Ejercido Vs. Modificado',
            'Nombre',
            'Definición',
            'Método de Cálculo',
            'No. de Indicador',
            'Tipo',
            'Dimensión',
            'Frecuencia de Medición',
            'Forma de cálculo del indicador',
            'Tipo de Valor',
            'Sentido Esperado',
            'MEDIOS DE VERIFICACIÓN/FUENTE INFORMACIÓN',
            'SUPUESTO',
            'Definición de la variable Meta:',
            'ID Unidad de medida',
            'Unidad de medida',
            'AÑO',
            'VALOR',
            'AÑO',
            'VALOR',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'CUMPLIMIENTO PRESUPUESTAL T1+T2+T3+T4',
            '1er. TRIMESTRE',
            '2o. TRIMESTRE',
            '3er. TRIMESTRE',
            '4o. TRIMESTRE',
            'PORCENTAJE ALCANZADO ANUAL',
            'PORCENTAJE DE CUMPLIMIENTO ALCANZADO EN EL TRIMESTRE',
        );

        $hoja1
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A3'         // Rango de celda o celda en donde deseamos que comience a pintar
            );


        /*        $hoja1->setCellValue('K1', 'ANTICIPO');
                $hoja1->getStyle('K1' )->getBorders()->getOutline()->setBorderStyle('thin');
                $hoja1->getStyle('K1' )->getAlignment()->setHorizontal('center');
                $hoja1->mergeCells('K1:L1');
                $hoja1->mergeCells('M1:Q1');
                $hoja1->mergeCells('A1:J1');*/

        //Se cambio el color de la fuente
        //$hoja1->getStyle('A1:Q2')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
                
        $hoja1->getRowDimension('3')->setRowHeight(25);

        $hoja1->getColumnDimension('A')->setWidth(10);
        $hoja1->getColumnDimension('C')->setWidth(10);
        $hoja1->getColumnDimension('E')->setWidth(10);
        $hoja1->getColumnDimension('F')->setWidth(20);
        $hoja1->getColumnDimension('H')->setWidth(10);
        $hoja1->getColumnDimension('I')->setWidth(20);
        $hoja1->getColumnDimension('J')->setWidth(8);
        $hoja1->getColumnDimension('K')->setWidth(15);
        $hoja1->getColumnDimension('L')->setWidth(20);
        $hoja1->getColumnDimension('M')->setWidth(10);
        $hoja1->getColumnDimension('N')->setWidth(25);
        $hoja1->getColumnDimension('O')->setWidth(25);
        $hoja1->getColumnDimension('P')->setWidth(25);
        $hoja1->getColumnDimension('Q')->setWidth(12);
        $hoja1->getColumnDimension('R')->setWidth(25);
        $hoja1->getColumnDimension('S')->setWidth(10);
        $hoja1->getColumnDimension('T')->setWidth(20);
        $hoja1->getColumnDimension('U')->setWidth(10);
        $hoja1->getColumnDimension('V')->setWidth(25);
        $hoja1->getColumnDimension('W')->setWidth(12);
        $hoja1->getColumnDimension('X')->setWidth(20);
        $hoja1->getColumnDimension('Y')->setWidth(10);
        $hoja1->getColumnDimension('Z')->setWidth(20);
        $hoja1->getColumnDimension('AA')->setWidth(12);
        $hoja1->getColumnDimension('AB')->setWidth(20);
        $hoja1->getColumnDimension('AC')->setWidth(20);

        $hoja1->getColumnDimension('AD')->setWidth(20);
        $hoja1->getColumnDimension('AE')->setWidth(20);
        $hoja1->getColumnDimension('AF')->setWidth(20);
        $hoja1->getColumnDimension('AG')->setWidth(21);
        $hoja1->getColumnDimension('AH')->setWidth(20);
        $hoja1->getColumnDimension('AI')->setWidth(20);
        $hoja1->getColumnDimension('AJ')->setWidth(20);
        $hoja1->getColumnDimension('AK')->setWidth(20);
        $hoja1->getColumnDimension('AL')->setWidth(21);
        $hoja1->getColumnDimension('AM')->setWidth(20);
        $hoja1->getColumnDimension('AN')->setWidth(20);
        $hoja1->getColumnDimension('AO')->setWidth(20);
        $hoja1->getColumnDimension('AP')->setWidth(20);
        $hoja1->getColumnDimension('AQ')->setWidth(21);
        $hoja1->getColumnDimension('AR')->setWidth(20);
        $hoja1->getColumnDimension('AS')->setWidth(20);
        $hoja1->getColumnDimension('AT')->setWidth(20);
        $hoja1->getColumnDimension('AU')->setWidth(20);
        $hoja1->getColumnDimension('AV')->setWidth(21);
        $hoja1->getColumnDimension('AW')->setWidth(20);
        $hoja1->getColumnDimension('AX')->setWidth(20);
        $hoja1->getColumnDimension('AY')->setWidth(20);
        $hoja1->getColumnDimension('AZ')->setWidth(20);
        $hoja1->getColumnDimension('BA')->setWidth(21);
        $hoja1->getColumnDimension('BB')->setWidth(20);
        $hoja1->getColumnDimension('BC')->setWidth(20);
        $hoja1->getColumnDimension('BD')->setWidth(20);
        
        $hoja1->getColumnDimension('BE')->setWidth(20);
        $hoja1->getColumnDimension('BF')->setWidth(21);
        $hoja1->getColumnDimension('BG')->setWidth(20);
        $hoja1->getColumnDimension('BH')->setWidth(20);
        $hoja1->getColumnDimension('BI')->setWidth(20);
        $hoja1->getColumnDimension('BJ')->setWidth(20);
        $hoja1->getColumnDimension('BK')->setWidth(21);
        $hoja1->getColumnDimension('BL')->setWidth(15);
        $hoja1->getColumnDimension('BM')->setWidth(30);
        $hoja1->getColumnDimension('BN')->setWidth(30);
        $hoja1->getColumnDimension('BO')->setWidth(30);
        $hoja1->getColumnDimension('BP')->setWidth(10);
        $hoja1->getColumnDimension('BQ')->setWidth(15);
        $hoja1->getColumnDimension('BR')->setWidth(15);
        $hoja1->getColumnDimension('BS')->setWidth(15);
        $hoja1->getColumnDimension('BT')->setWidth(18);
        $hoja1->getColumnDimension('BU')->setWidth(15);
        $hoja1->getColumnDimension('BV')->setWidth(15);
        $hoja1->getColumnDimension('BW')->setWidth(25);
        $hoja1->getColumnDimension('BX')->setWidth(25);
        $hoja1->getColumnDimension('BY')->setWidth(25);
        $hoja1->getColumnDimension('BZ')->setWidth(20);
        $hoja1->getColumnDimension('CA')->setWidth(20);
        $hoja1->getColumnDimension('CB')->setWidth(20);
        $hoja1->getColumnDimension('CC')->setWidth(20);
        $hoja1->getColumnDimension('CD')->setWidth(20);
        $hoja1->getColumnDimension('CE')->setWidth(20);

        $hoja1->getColumnDimension('CF')->setWidth(20);
        $hoja1->getColumnDimension('CG')->setWidth(20);
        $hoja1->getColumnDimension('CH')->setWidth(20);
        $hoja1->getColumnDimension('CI')->setWidth(20);
        $hoja1->getColumnDimension('CJ')->setWidth(21);
        $hoja1->getColumnDimension('CK')->setWidth(20);
        $hoja1->getColumnDimension('CL')->setWidth(20);
        $hoja1->getColumnDimension('CM')->setWidth(20);
        $hoja1->getColumnDimension('CN')->setWidth(20);
        $hoja1->getColumnDimension('CO')->setWidth(21);
        $hoja1->getColumnDimension('CP')->setWidth(20);
        $hoja1->getColumnDimension('CQ')->setWidth(15);
        $hoja1->getColumnDimension('CR')->setWidth(15);

    }

    function consultaHoja1()
    {

        $mes = date('m');
        $trimActual=floor(($mes-1) / 3)+1;
        //$trimActual= 2;
        $gpo= '';
        $ur = '';
        $conexionTrim = new AccesoDatos();
        $conexionTrim->conectar();

        $conexionTrim->setSql("
        SELECT gpo_id,ur_id,
        CASE WHEN '2022-01-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre1 THEN 1
        WHEN '2022-07-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre2 THEN 2 
        WHEN '2022-10-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre3 THEN 3 
        WHEN '2022-01-01 08:00:00'<=NOW() AND NOW()<=fca_trimestre4 THEN 4 
        ELSE 0 END AS capturandoTri,
        CASE WHEN ((total1*100)/total11) AND ((est_trimestre1=1 AND cap_trimestre1= 1) OR (est_trimestre1=0 AND cap_trimestre1= 1)) AND cap_trimestre2=0 AND cap_trimestre3=0 AND cap_trimestre4 = 0 THEN 1
        WHEN ((total2*100)/total22) AND ((est_trimestre2=1 AND cap_trimestre1= 1) OR (est_trimestre2=0 AND cap_trimestre1= 1)) AND cap_trimestre2=1 AND cap_trimestre3=0 AND cap_trimestre4 = 0  THEN 2 
        WHEN ((total3*100)/total33) AND ((est_trimestre3=1 AND cap_trimestre1= 1) OR (est_trimestre3=0 AND cap_trimestre1= 1)) AND cap_trimestre2=1 AND cap_trimestre3=1 AND cap_trimestre4 = 0  THEN 3 
        WHEN ((total4*100)/total44) AND ((est_trimestre4=1 AND cap_trimestre1= 1) OR (est_trimestre4=0 AND cap_trimestre1= 1)) AND cap_trimestre2=1 AND cap_trimestre3=1 AND cap_trimestre4 = 1  THEN 4 
        ELSE 1 END AS capturandoTri2
        FROM
        (
            SELECT gpo_id,ur_id,fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
            SUM(contador) total,
             SUM(IF(a.usr_fecha<='2022-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),contador,0)) total11,
            SUM(IF(a.usr_fecha<='2022-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),contador,0)) total22,
            SUM(IF(a.usr_fecha<='2022-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),contador,0)) total33,
            SUM(IF(a.usr_fecha<='2022-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),contador,0)) total44,
            SUM(IF(a.usr_fecha<='2022-03-31'&& (vig_trimestre1>0 || mod_trimestre1>0),cap_trimestre1,0)) total1,
            SUM(IF(a.usr_fecha<='2022-06-30'&& (vig_trimestre2>0 || mod_trimestre2>0),cap_trimestre2,0))total2,
            SUM(IF(a.usr_fecha<='2022-09-30'&& (vig_trimestre3>0 || mod_trimestre3>0),cap_trimestre3,0))total3,
            SUM(IF(a.usr_fecha<='2022-12-31'&& (vig_trimestre4>0 || mod_trimestre4>0),cap_trimestre4,0)) total4,
            SUM(alc_trimestre1) alc_trimestre1,SUM(alc_trimestre2) alc_trimestre2,
            SUM(alc_trimestre3) alc_trimestre3,SUM(alc_trimestre4) alc_trimestre4,
            SUM(mem_alcanzado) mem_alcanzado, SUM(mem_modificado) mem_modificado,ur_clave,ur_nombre, 
            est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
            MAX(cap_trimestre1) AS cap_trimestre1, MAX(cap_trimestre2) AS cap_trimestre2, 
            MAX(cap_trimestre3) AS cap_trimestre3, MAX(cap_trimestre4) AS cap_trimestre4
            FROM (
                    SELECT pii_id,1 contador, gpo_id, ur_id, ur_clave,ur_nombre,m.usr_fecha,
                    epg_id,mac_alcanzado AS mem_alcanzado,mac_modificado AS mem_modificado,
                    alc_trimestre1, alc_trimestre2, alc_trimestre3, alc_trimestre4,
                    mod_trimestre1, mod_trimestre2, mod_trimestre3, mod_trimestre4,
                    mac_original AS mem_original, ori_trimestre1, ori_trimestre2, ori_trimestre3, ori_trimestre4,
                    cap_trimestre1,cap_trimestre2,cap_trimestre3,cap_trimestre4,
                    fca_trimestre1,fca_trimestre2,fca_trimestre3,fca_trimestre4,
                    est_trimestre1, est_trimestre2, est_trimestre3, est_trimestre4,
                    SUM(vig_trimestre1) AS vig_trimestre1,SUM(vig_trimestre2) AS vig_trimestre2,
                    SUM(vig_trimestre3) AS vig_trimestre3,SUM(vig_trimestre4) AS vig_trimestre4                                    
                    FROM cat_MetaActividad m
                    INNER JOIN (
                        SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_alcanzado,NULL)) alc_trimestre1,
                        SUM(IF(tri_id=2,mact_alcanzado,NULL)) alc_trimestre2, SUM(IF(tri_id=3,mact_alcanzado,NULL)) alc_trimestre3,
                        SUM(IF(tri_id=4,mact_alcanzado,NULL)) alc_trimestre4,  SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                        SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                        SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4, SUM(IF(tri_id=1,mact_original,NULL)) ori_trimestre1,
                        SUM(IF(tri_id=2,mact_original,NULL)) ori_trimestre2, SUM(IF(tri_id=3,mact_original,NULL)) ori_trimestre3,
                        SUM(IF(tri_id=4,mact_original,NULL)) ori_trimestre4, SUM(IF(tri_id=1,mact_capturaAvance,NULL)) cap_trimestre1,
                        SUM(IF(tri_id=2,mact_capturaAvance,NULL)) cap_trimestre2, SUM(IF(tri_id=3,mact_capturaAvance,NULL)) cap_trimestre3,
                        SUM(IF(tri_id=4,mact_capturaAvance,NULL)) cap_trimestre4               
                        FROM cat_MetaActividadTrimestre
                        GROUP BY pii_id,epg_id
                    )mat USING (pii_id,epg_id)
                    INNER JOIN enc_Programatico USING(epg_id)
                    INNER JOIN (
                        SELECT gpo_id, ur_id, nim_id, MAX( IF(tri_id=1,etr_id1,NULL)) AS est_trimestre1,
                        MAX(IF(tri_id=2,etr_id1,NULL)) AS est_trimestre2, MAX(IF(tri_id=3,etr_id1,NULL)) AS est_trimestre3,
                        MAX(IF(tri_id=4,etr_id1,NULL)) AS est_trimestre4, MAX(IF(tri_id=1,tru_fechaCierre,NULL)) fca_trimestre1,
                        MAX(IF(tri_id=2,tru_fechaCierre,NULL)) fca_trimestre2, MAX(IF(tri_id=3,tru_fechaCierre,NULL)) fca_trimestre3,
                        MAX(IF(tri_id=4,tru_fechaCierre,NULL)) fca_trimestre4
                        FROM mir_TrimestreUr
                        WHERE nim_id=4
                        GROUP BY gpo_id, ur_id 
                    )est USING (gpo_id,ur_id)
                    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
                    INNER JOIN (
                        SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id, SUM(pre_vigente) AS pre_vigente,epg_id,
                        SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                        SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4
                        FROM presupuesto
                        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                        GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id                                        
                    ) AS temp USING(gpo_id,ur_id,epg_id)
                    WHERE gpo_id!=0                                     
                    GROUP BY epg_id,pii_id
            )a
            GROUP BY gpo_id,ur_id
        )b;
        ");
    
        //Consulta general, se obtienen datos de ur y presupuesto
        $conexionTrim -> consultar();
        $conexionTrim -> desconectar();

        $conexion = new AccesoDatos();
        $conexion->conectar();

        $conexion->setSql('
	SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,oa_id,epg_id,
		ur_clave, ur_nombre,pdf_id,pdi_id,
		ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo,
		ur_cargoTitular, ur_objetivoGeneral, prg_nombre,
		fin, vpm_resumenNarrativo AS proposito,
		pf.pdf_id AS componente_id,
		pf.pdf_nombre AS componente,
        ue_id AS ue_id,
        ue_nombre AS ue_nombre,
        pi.pdi_id AS actividad_id,
        pi.pdi_nombre AS actividad,
	    prog.prg_id AS programa_id,
		prog.prg_nombre AS programa,
		prog.spr_id AS subprograma_id,
		prog.spr_nombre AS subprograma,
		prog.py_id AS proyecto_id,
		prog.py_nombre AS proyecto,
		prog.oa_id AS oa_id,
		prog.oa_nombre AS oa_nombre,
        eje_id AS eje_id,
        eje_nombre AS eje_nombre,
	    tem_id AS tem_id,
	    tem_nombre AS tem_nombre,
	    obj_id AS obj_id,
	    obj_nombre AS obj_nombre,
	    CONCAT(est_id,"-",est_nombre) AS est_nombre,
	    vin.lac_id AS lac_id,
	    lac_nombre AS lac_nombre,
        aprobado, ampliaciones,reducciones,modificado,devengado,
        apr_trimestre1,apr_trimestre2,apr_trimestre3,apr_trimestre4,
        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4,
        comprometido1,comprometido2,comprometido3,comprometido4,
        ejercido1,ejercido2,ejercido3,ejercido4,
        devengado1, devengado2, devengado3, devengado4
    FROM(
        SELECT epg_id, gpo_id, ur_id,ue_id, prg_id, spr_id, py_id, oa_id, prg_nombre,
        pdf_id, spr_nombre, py_nombre, oa_nombre
        FROM cat_Programa prg
        INNER JOIN cat_Subprograma spr USING (gpo_id,ur_id,prg_id)
        INNER JOIN cat_Proyecto py USING (gpo_id,ur_id,prg_id,spr_id)
        INNER JOIN cat_ObraActividad oa USING (gpo_id,ur_id,prg_id,spr_id,py_id)
        INNER JOIN enc_Programatico eprg USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
        -- Aqui va el tipo de actividad
         WHERE ' . $this->wh_toa . ' ' . str_replace("WHERE", "and", $this->wh_gpo) . '  
    )prog
    INNER JOIN (
        SELECT pii_id,epg_id,aprobado, ampliaciones,reducciones,modificado,devengado,
        apr_trimestre1,apr_trimestre2,apr_trimestre3,apr_trimestre4,
        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4,
        comprometido1,comprometido2,comprometido3,comprometido4,
        ejercido1,ejercido2,ejercido3,ejercido4,
        devengado1, devengado2, devengado3, devengado4
        FROM (
                SELECT epg_id, gpo_id, ur_id, prg_id, spr_id, py_id, oa_id
                FROM enc_Programatico
            ' . $this->wh_gpo . ' 
            ) eprg
            LEFT JOIN cat_MetaActividad mac USING (epg_id)
            LEFT JOIN (
                SELECT pii_id, epg_id, SUM(IF(tri_id=1,mact_modificado,NULL)) mod_trimestre1,
                    SUM(IF(tri_id=2,mact_modificado,NULL)) mod_trimestre2, SUM(IF(tri_id=3,mact_modificado,NULL)) mod_trimestre3,
                    SUM(IF(tri_id=4,mact_modificado,NULL)) mod_trimestre4
                FROM cat_MetaActividadTrimestre
                GROUP BY pii_id,epg_id
            )mat USING (pii_id,epg_id)
            LEFT JOIN (
                SELECT gpo_id,ur_id,spr_id, prg_id, py_id, oa_id,epg_id,
                    SUM(pre_aprobado) AS aprobado, SUM(pre_vigente) AS modificado, SUM(pre_devengado) devengado,
                    SUM(pre_ampliacion) ampliaciones, SUM(pre_reduccion) reducciones,
                    SUM(apr_ene+apr_feb+apr_mar) apr_trimestre1, SUM(apr_abr+apr_may+apr_jun) apr_trimestre2,
                    SUM(apr_jul+apr_ago+apr_sep) apr_trimestre3, SUM(apr_oct+apr_nov+apr_dic)apr_trimestre4,
                    SUM(vig_ene+vig_feb+vig_mar) vig_trimestre1, SUM(vig_abr+vig_may+vig_jun) vig_trimestre2,
                    SUM(vig_jul+vig_ago+vig_sep) vig_trimestre3, SUM(vig_oct+vig_nov+vig_dic)vig_trimestre4,
                    SUM(com_ene+com_feb+com_mar) comprometido1,SUM(dev_ene+dev_feb+dev_mar) devengado1,
                    SUM(eje_ene+eje_feb+eje_mar) ejercido1,SUM(com_abr+com_may+com_jun) comprometido2,
                    SUM(dev_abr+dev_may+dev_jun) devengado2,SUM(eje_abr+eje_may+eje_jun) ejercido2,
                    SUM(com_jul+com_ago+com_sep) comprometido3,SUM(dev_jul+dev_ago+dev_sep) devengado3,
                    SUM(eje_jul+eje_ago+eje_sep) ejercido3,SUM(com_oct+com_nov+com_dic) comprometido4,
                    SUM(dev_oct+dev_nov+dev_dic) devengado4,SUM(eje_oct+eje_nov+eje_dic) ejercido4
                FROM presupuesto
                    INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                    INNER JOIN presupuestoEjercido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
                 WHERE tcp_id !="P" ' .  str_replace("WHERE", "and", $this->wh_gpo) . ' 

                GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
            ) AS temp USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id)
        ' . $this->wh_gpo . ' 

        GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id,pii_id
    ) meta USING(epg_id)
    INNER JOIN (SELECT epg_id,eje_id,eje_nombre,tem_id,tem_nombre,obj_id,obj_nombre,
            est_id,est_nombre,lac_id,lac_nombre,fin,vpm_resumenNarrativo
        FROM (
            SELECT epg_id,MAX(epe_id) epe_id, MAX(eps_id) eps_id,prg_id
            FROM enc_Programatico ep
            INNER JOIN vin_Programatico USING(epg_id)
        ' . $this->wh_gpo . ' 

            GROUP BY epg_id
            ) vp
            INNER JOIN enc_Ped USING(epe_id)
            INNER JOIN cat_Eje USING(eje_id)
            INNER JOIN cat_Tema t USING(eje_id, tem_id)
            INNER JOIN cat_Objetivo o USING(eje_id, tem_id, obj_id)
            INNER JOIN cat_Estrategia USING(eje_id,obj_id,est_id,tem_id)
            INNER JOIN cat_LineaAccion lac USING (eje_id,obj_id,est_id,tem_id,lac_id)
            LEFT JOIN ( SELECT prgs_id AS prg_id, obj_nombre AS fin, obj_descripcion
                        FROM vin_Programa
                            INNER JOIN enc_PedObjetivo USING (epo_id)
                            INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
                        ) F USING (prg_id)
            INNER JOIN ( SELECT vpm_id, prgs_id AS prg_id, mir_id, vpm_resumenNarrativo
                        FROM vin_ProgramasMir
                        ) P USING (prg_id)
        
    ) vin USING (epg_id)
    INNER JOIN vin_ProductoIntermedioIndicador pfpi USING(pii_id,pdf_id)
        INNER JOIN cat_ProductoFinal pf USING(pdf_id)
        INNER JOIN cat_ProductoIntermedio pi USING(pdi_id)
        INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
    ' . $this->wh_gpo . ' 

        GROUP BY prg_id,spr_id,epg_id,pdi_id,vpm_resumenNarrativo
        ORDER BY gpo_id,ur_id,prg_id,spr_id,oa_id,ue_id;
                ');

        //Consulta general, se obtienen datos de ur y presupuesto
        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();
        
        //Conexión para los indicadores
        $conexionIndicador = new AccesoDatos();
        $conexionIndicador->conectar();
        
        //Conexion para las metas
        $conexionMeta = new AccesoDatos();
        $conexionMeta->conectar();

        while ($value = $conexion->extraer()) {
           
            //Este if es para saber si se esta trabajando con la misma UR, en caso de que no se busca el capturandoTri
            if($gpo != $value['gpo_id'] & $ur != $value['ur_id']){
                //Este ciclo se realiza para saber el valor del trimestre de la ur
                while($t = $conexionTrim->extraer()){
                    if($value['gpo_id'] == $t['gpo_id'] & $value['ur_id'] == $t['ur_id']){
                        $gpo = $t['gpo_id'];
                        $ur = $t['ur_id'];
    
                        if ($t['capturandoTri'] == 0){
                            $t['capturandoTri'] = $t['capturandoTri2'];
                        }
                        $trim = $t['capturandoTri'];
                        break;
                    }
                }
            }

            $conexionIndicador->setSql('
	            SELECT pdf_id, pdi_id, ind_id, ind_nombre, ind_definicion, ind_nombreMIR, ind_definicionMIR, ind_objetivo, ind_formulaCalculo,
                tpi_id, tpi_nombre, umi_id ,umi_nombre,umv_id, umv_nombre,
                dim_id, dim_nombre, frm_id, frm_nombre, ses_id, tiv_id, tiv_nombre,
                ses_nombre, nim_id, MAX(mvac_nombre) mvac_nombre, MAX(spa_nombre) spa_nombre,
                gpo_id, ur_id, prg_id, mac_anio, mac_valor, mac_original, epg_id
                FROM vin_ProductoIntermedioIndicador pii
                JOIN cat_Indicador i USING (nim_id , ind_id)
                JOIN cat_Dimension USING (dim_id)
                JOIN cat_FrecuenciaMedicion USING (frm_id)
                JOIN cat_TipoIndicador USING (tpi_id)
                JOIN cat_UnidadMedidaIndicador u USING (umi_id)
                JOIN cat_UnidadMedidaVariable umv USING(umv_id)
                JOIN cat_TipoValor USING (tiv_id)
                JOIN cat_SentidoEsperado USING (ses_id)
                INNER JOIN cat_MetaActividad USING (pii_id)
                INNER JOIN enc_Programatico ep USING (epg_id)
                LEFT JOIN cat_MedioVerificacionActividad mva USING (pdf_id,pdi_id,gpo_id, ur_id)
                LEFT JOIN cat_SupuestoActividad sa USING (pdf_id,pdi_id,gpo_id, ur_id)
            WHERE
				nim_id = 4
				AND pdf_id =' . $value['pdf_id'] . '
				AND pdi_id =' . $value['pdi_id'] . '
                AND gpo_id = ' . $value['gpo_id'] . '  AND ur_id = ' . $value['ur_id'] . '
                AND prg_id=' . $value['prg_id'] . ' AND epg_id="' . $value['epg_id'] . '"
            GROUP BY gpo_id, ur_id, prg_id, pdf_id,pdi_id,epg_id,pii_id;
			');



            $conexionIndicador->consultar();

            $indicadores = $conexionIndicador->extraer();
    
            $conexionMeta->setSql('
            SELECT 
                mac_descripcion,
                mac_anio,
                mac_valor,
                mac_original,
                SUM(IF(tri_id = 1, mact_original, NULL)) ori_trimestre1,
                SUM(IF(tri_id = 2, mact_original, NULL)) ori_trimestre2,
                SUM(IF(tri_id = 3, mact_original, NULL)) ori_trimestre3,
                SUM(IF(tri_id = 4, mact_original, NULL)) ori_trimestre4,
                mac_modificado,
                SUM(IF(tri_id = 1, mact_modificado, NULL)) mod_trimestre1,
                SUM(IF(tri_id = 2, mact_modificado, NULL)) mod_trimestre2,
                SUM(IF(tri_id = 3, mact_modificado, NULL)) mod_trimestre3,
                SUM(IF(tri_id = 4, mact_modificado, NULL)) mod_trimestre4,
                mac_alcanzado,
                SUM(IF(tri_id = 1, mact_alcanzado, NULL)) alc_trimestre1,
                SUM(IF(tri_id = 2, mact_alcanzado, NULL)) alc_trimestre2,
                SUM(IF(tri_id = 3, mact_alcanzado, NULL)) alc_trimestre3,
                SUM(IF(tri_id = 4, mact_alcanzado, NULL)) alc_trimestre4,
                IF(mac_tipoCalendario IS NULL,"S",mac_tipoCalendario) AS mac_tipoCalendario
            FROM cat_MetaActividad	
            INNER JOIN cat_MetaActividadTrimestre USING(pii_id, epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
            INNER JOIN enc_Programatico USING(epg_id)
            WHERE gpo_id = ' . $indicadores['gpo_id'] . ' AND ur_id = ' . $indicadores['ur_id'] . ' AND ind_id IN( ' . $indicadores['ind_id'] . ' ) 
                AND pdi_id IN (' . $indicadores['pdi_id'] . ') AND epg_id=' . $indicadores['epg_id'] . '
            GROUP BY epg_id,pii_id;
            ');

            $conexionMeta->consultar();

            // Operaciones para calcular valores de las metas
            $metas = $conexionMeta->extraer();

            if ($metas['mac_tipoCalendario'] == 'C') {
                $sumoriginalanual = $metas['ori_trimestre1'];
                $summodificadoanual = $metas['mod_trimestre1'];
            } else {
                $sumoriginalanual = $metas['mac_original'];
                $summodificadoanual = $metas['mac_modificado'];
            }

            $sumametasanual = $summodificadoanual;

            
            /* % ALCANZADO TRIMESTRAL */
            $porcentajealcanzadoTrim1 = number_format(($metas['alc_trimestre1'] / $metas['mod_trimestre1']) * 100, 2);
            $porcentajealcanzadoTrim2 = number_format(($metas['alc_trimestre2'] / $metas['mod_trimestre2']) * 100, 2);
            $porcentajealcanzadoTrim3 = number_format(($metas['alc_trimestre3'] / $metas['mod_trimestre3']) * 100, 2);
            $porcentajealcanzadoTrim4 = number_format(($metas['alc_trimestre4'] / $metas['mod_trimestre4']) * 100, 2);


            $trim1alc = 0;
            $trim2alc = 0;
            $trim3alc = 0;
            $trim4alc = 0;
            $trim1ori = $metas['ori_trimestre1'];
            $trim1mod = ($trim >= 1 ? $metas['mod_trimestre1'] : "-");
            $trim2ori = $metas['ori_trimestre2'];
            $trim2mod = ($trim >= 2 ? $metas['mod_trimestre2'] : "-");
            $trim3ori = $metas['ori_trimestre3'];
            $trim3mod = ($trim >= 3 ? $metas['mod_trimestre3'] : "-");
            $trim4ori = $metas['ori_trimestre4'];
            $trim4mod = ($trim >= 4 ? $metas['mod_trimestre4'] : "-");

            $acumuladoMod = ($trim >= 1 ? $metas['mod_trimestre1'] : 0) 
                            + ($trim >= 2 ? $metas['mod_trimestre2'] : 0) 
                            + ($trim >= 3 ? $metas['mod_trimestre3'] : 0) 
                            + ($trim >= 4 ? $metas['mod_trimestre4'] : 0);
            switch ($trim) {
                case 1:
                    $trim1alc = $metas['alc_trimestre1'];
                    $sumaalcanzados = $trim1alc;
                    break;
                case 2:
                    $trim1alc = $metas['alc_trimestre1'];
                    $trim2alc = $metas['alc_trimestre2'];
                    $sumaalcanzados = $trim1alc + $trim2alc;
                    break;
                case 3:
                    $trim1alc = $metas['alc_trimestre1'];
                    $trim2alc = $metas['alc_trimestre2'];
                    $trim3alc = $metas['alc_trimestre3'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc;
                    break;
                case 4:
                    $trim1alc = $metas['alc_trimestre1'];
                    $trim2alc = $metas['alc_trimestre2'];
                    $trim3alc = $metas['alc_trimestre3'];
                    $trim4alc = $metas['alc_trimestre4'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc + $trim4alc;
                    break;
            }

            if ($sumametasanual == 0) {
                $porcentajeanual = number_format(0, 2);
            } else {
                $porcentajeanual = number_format(($sumaalcanzados / $sumametasanual) * 100, 2);
                $porcentajeanual = ($metas['mac_tipoCalendario'] == 'C') ? number_format((($metas['alc_trimestre1'] * 25 ) / $sumametasanual) + (($metas['alc_trimestre2'] * 25 ) / $sumametasanual) + (($metas['alc_trimestre3'] * 25 ) / $sumametasanual) + (($metas['alc_trimestre4'] * 25 ) / $sumametasanual), 2) : number_format(($metas['alc_trimestre' . $trim . ''] / $sumametasanual) * 100, 2);
            }

            $porcentajealcanual = number_format(($sumaalcanzados / $sumametasanual) * 100, 2);
            
            $apr1 = $value['apr_trimestre1'] == '' ? '0' : $value['apr_trimestre1'];
            $apr2 = $value['apr_trimestre2'] == '' ? '0' : $value['apr_trimestre2'];
            $apr3 = $value['apr_trimestre3'] == '' ? '0' : $value['apr_trimestre3'];
            $apr4 = $value['apr_trimestre4'] == '' ? '0' : $value['apr_trimestre4'];

            $com1 = $value['comprometido1'] == '' ? '0' : $value['comprometido1'];
            $com2 = $value['comprometido2'] == '' ? '0' : $value['comprometido2'];
            $com3 = $value['comprometido3'] == '' ? '0' : $value['comprometido3'];
            $com4 = $value['comprometido4'] == '' ? '0' : $value['comprometido4'];

            $dev1 = $value['devengado1'] == '' ? '0' : $value['devengado1'];
            $dev2 = $value['devengado2'] == '' ? '0' : $value['devengado2'];
            $dev3 = $value['devengado3'] == '' ? '0' : $value['devengado3'];
            $dev4 = $value['devengado4'] == '' ? '0' : $value['devengado4'];

            $eje1 = $value['ejercido1'] == '' ? '0' : $value['ejercido1'];
            $eje2 = $value['ejercido2'] == '' ? '0' : $value['ejercido2'];
            $eje3 = $value['ejercido3'] == '' ? '0' : $value['ejercido3'];
            $eje4 = $value['ejercido4'] == '' ? '0' : $value['ejercido4'];

            $dato = array(
                'eje_id' => $value['eje_id'],
                'eje_nombre' => $value['eje_nombre'],
                'id_tema' => $value['tem_id'],
                'tema' => $value['tem_nombre'],
                'id_objetivo' => $value['obj_id'],
                'objetivo' => $value['obj_nombre'],
                'estrategia' => $value['est_nombre'],
                'id_lineas_acc' => $value['lac_id'],
                'lineas_acc' => $value['lac_nombre'],
                'id_ur' => $value['gpo_id'] . $value['ur_id'],
                'nombre_ur' => $value['ur_nombre'],
                'obj_institucional' => $value['ur_objetivoGeneral'],
                'id_ue' => $value['ue_id'],
                'ue_nombre' => $value['ue_nombre'],
                //Aqui checamos si tiene fin la obra.
                'fin' => $value['fin'] == NULL ? 'N/A' : $value['fin'],
                'proposito' => $value['proposito'],
                'id_componente' => $value['componente_id'],
                'componente' => $value['componente'],
                'id_actividad' => $value['actividad_id'],
                'actividad' => $value['actividad'],
                'id_programa' => $value['programa_id'],
                'programa' => $value['programa'],
                'id_subprograma' => $value['subprograma_id'],
                'subprograma' => $value['subprograma'],
                'id_proyecto' => $value['proyecto_id'],
                'proyecto' => $value['proyecto'],
                'id_obra' => $value['oa_id'],
                'obra' => $value['oa_nombre'],
                'apr_trimestre1' => $apr1,
                'apr_trimestre2' => ($trimActual > 1) ? $apr2  : '',
                'apr_trimestre3' => ($trimActual > 2) ? $apr3 : '',
                'apr_trimestre4' => ($trimActual > 3) ? $apr4 : '',
                'apr_suma' => $trimActual == 1 ?  
                            ($apr1  == '' ? '0' : $apr1 ): 
                            ($trimActual == 2 ? 
                            ($apr1 + $apr2 == '' ? '0' : $apr1 + $apr2 ):
                            ( $trimActual == 3 ? 
                            ($apr1 + $apr2 + $apr3 == '' ? '0' : $apr1 + $apr2 + $apr3): 
                            ( $trimActual == 4 ? 
                            ($apr1 + $apr2 + $apr3 + $apr4 == '' ? '0' : $apr1 + $apr2 + $apr3 + $apr4):
                            ''))) ,
                'ampliaciones' => $value['ampliaciones'] == '' ? '0' : $value['ampliaciones'], 
                'reducciones' => $value['reducciones'] == '' ? '0' : $value['reducciones'], 
                'mod_trimestre1' => $value['vig_trimestre1'] == '' ? '0' : $value['vig_trimestre1'],
                'mod_trimestre2' => $value['vig_trimestre2'] == '' ? '0' : $value['vig_trimestre2'],
                'mod_trimestre3' => $value['vig_trimestre3'] == '' ? '0' : $value['vig_trimestre3'],
                'mod_trimestre4' => $value['vig_trimestre4'] == '' ? '0' : $value['vig_trimestre4'],
                'mod_suma' => ($value['vig_trimestre1'] + $value['vig_trimestre2'] + $value['vig_trimestre3'] + $value['vig_trimestre4']) == '' ? '0' :
                        $value['vig_trimestre1'] + $value['vig_trimestre2'] + $value['vig_trimestre3'] + $value['vig_trimestre4'],
                'com_trimestre1' => $com1,
                'com_trimestre2' => ($trimActual > 1) ? $com2 : '',
                'com_trimestre3' => ($trimActual > 2) ? $com3 : '',
                'com_trimestre4' => ($trimActual > 3) ? $com4 : '',                
                'com_suma' => $trimActual == 1 ?  
                            ($com1  == '' ? '0' : $com1 ) : 
                            ($trimActual == 2 ? 
                            ($com1 + $com2 == '' ? '0' : $com1 + $com2 ) : 
                            ( $trimActual == 3 ? 
                            ($com1 + $com2 + $com3 == '' ? '0' : $com1 + $com2 + $com3 ): 
                            ( $trimActual == 4 ? 
                            ($com1 + $com2 + $com3 + $com4 == '' ? '0' : $com1 + $com2 + $com3 + $com4):                                                    
                                                                                '' ))) ,
                'dev_trimestre1' => $dev1,
                'dev_trimestre2' => ($trimActual > 1) ? $dev2 : '',
                'dev_trimestre3' => ($trimActual > 2) ? $dev3 : '',
                'dev_trimestre4' => ($trimActual > 3) ? $dev4 : '',
                'dev_suma' => $trimActual == 1 ?  
                            ($dev1 == '' ? '0' : $dev1):                                                     
                            ($trimActual == 2 ? 
                            ($dev1 + $dev2 == '' ? '0' : $dev1 + $dev2):                                                     
                            ( $trimActual == 3 ? 
                            ($dev1 + $dev2 + $dev3 == '' ? '0' : $dev1 + $dev2 + $dev3):                                                     
                            ( $trimActual == 4 ? 
                            ($dev1 + $dev2 + $dev3 + $dev4 == '' ? '0' : $dev1 + $dev2 + $dev3 + $dev4):                                                     
                            ''))) ,
                 'eje_trimestre1' => $eje1,
                'eje_trimestre2' => ($trimActual > 1) ? $eje2 : '',
                'eje_trimestre3' => ($trimActual > 2) ? $eje3 : '',
                'eje_trimestre4' => ($trimActual > 3) ? $eje4 : '',
                'eje_suma' => $trimActual == 1 ?  
                            ($eje1 == '' ? '0' : $eje1):                                                     
                            ($trimActual == 2 ? 
                            ($eje1 + $eje2 == '' ? '0' : $eje1 + $eje2):                                                     
                            ( $trimActual == 3 ? 
                            ($eje1 + $eje2 + $eje3 == '' ? '0' : $eje1 + $eje2 + $eje3):                                                     
                            ( $trimActual == 4 ? 
                            ($eje1 + $eje2 + $eje3 + $eje4 == '' ? '0' : $eje1 + $eje2 + $eje3 + $eje4):                                                     
                            ''))) ,
                'eje_mod' => number_format(($value['ejercido' . $trimActual] / $value['vig_trimestre' . $trimActual]) * 100 , 2),
             
                'nombre' => $indicadores['ind_nombreMIR'],
                'def' => $indicadores['ind_definicionMIR'],
                //El valor de esta variable genera un error, por eso se le sumo un " " para evitarlo
                 'met_calculo' => ' ' . $indicadores['ind_formulaCalculo'] . ' ',
                'no_ind' => $indicadores['ind_id'],
                'tipo' => $indicadores['tpi_nombre'],              
                'dimension' => $indicadores['dim_nombre'],
                'frec_med' => $indicadores['frm_nombre'],
                'forma_calculo' => $indicadores['umi_nombre'],
                'tipo_valor' => $indicadores['tiv_nombre'],
                'sentido_esp' => $indicadores['ses_nombre'],
                'med_verificacion' => $indicadores['mvac_nombre'],
                'supuesto' => $indicadores['spa_nombre'],
                
                'def_variable' => $metas['mac_descripcion'],

                'id_unidad' => $indicadores['umv_id'],
                'unidad_medida' => $indicadores['umv_nombre'],                
                'linea_anio' => $indicadores['mac_anio'],
                'linea_valor' => $indicadores['mac_valor'],
                'valor_año' => date('Y'),
                'valor_valor' => $indicadores['mac_original'],
                
                'prog_trimestre1' => number_format($trim1ori, 2),
                'prog_trimestre2' => number_format($trim2ori, 2),
                'prog_trimestre3' => number_format($trim3ori, 2),
                'prog_trimestre4' => number_format($trim4ori, 2),
                'prog_suma' => number_format($sumoriginalanual, 2),
                'proga_trimestre1' => number_format($trim1mod, 2),
                'proga_trimestre2' => number_format($trim2mod, 2),
                'proga_trimestre3' => number_format($trim3mod, 2),
                'proga_trimestre4' => number_format($trim4mod, 2),
                'proga_suma' => number_format($sumametasanual, 2),
                'alc_trimestre1' => number_format($trim1alc, 2),
                'alc_trimestre2' => number_format($trim2alc, 2),
                'alc_trimestre3' => number_format($trim3alc, 2),
                'alc_trimestre4' => number_format($trim4alc, 2),
                'alc_suma' => number_format($sumaalcanzados, 2),
                'por_alc_trimestre1' => number_format($porcentajealcanzadoTrim1, 2),
                'por_alc_trimestre2' => number_format($porcentajealcanzadoTrim2, 2),
                'por_alc_trimestre3' => number_format($porcentajealcanzadoTrim3, 2),
                'por_alc_trimestre4' => number_format($porcentajealcanzadoTrim4, 2),
                'por_alc_suma' => number_format($porcentajealcanual, 2),
                'por_cump' =>  $porcentajeanual,



            );
            array_push($datos, $dato);
        }
        /*
            WHERE gpo_id = ' . $indicadores['gpo_id'] . ' AND ur_id = ' . $indicadores['ur_id'] . ' AND ind_id IN( ' . $indicadores['ind_id'] . ' ) 
                AND pdi_id IN (' . $indicadores['pdi_id'] . ') AND epg_id=' . $indicadores['epg_id'] . '
            GROUP BY epg_id,pii_id;
        */
        /*
                          'gpo' =>  $indicadores['gpo_id'],
                'ur'  =>  $indicadores['ur_id'],
                'ind_id' => $indicadores['ind_id'],
                 'pdi_id' => $indicadores['pdi_id'],
                 'epg_id' => $indicadores['epg_id'],   */
        $conexionIndicador->desconectar();
        $conexionMeta->desconectar();
        
        return $datos;
    }

}

$exp_obraPub = new exp_ObraPub();
