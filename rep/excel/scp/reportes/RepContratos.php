<?php

session_start();
error_reporting(E_ALL);
error_reporting(E_ERROR);
ini_set("memory_limit",'1G');
ini_set('max_execution_time', 0);
/* IMPORTANTE
  Este reporte al migrarse, se le deben agregar las partes para la consulta del año nuevo
  es decir, para 2022 se le agrego el UNION ALL con la consulta, esto para la hoja 2
*/

// Clase con los metodos, establecer las propiedades del documento Excel a generar y como obtener el archivo
// de salida. 
require_once dirname(__FILE__) . '/../../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../../lib/constantes.php';
include_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';


class exp_ObraPub extends Phpspreadsheet
{

    function __construct()
    {
        /*        $this->cabecera();

                $this->contenido();*/
        parent::__construct();

        $this->hoja1();

        $this->hoja2();

        $this->hoja3();

        //Se activa la hoja 1 como principal
        $this->spreadsheet->setActiveSheetIndex(0);

        // Descargar el archivo desde el navegador con su respectivo nombre.
        $this->output_web_browser("Rep_Contratos.xlsx");
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

        $tam = count($datos) + 2;

        $this->spreadsheet->getActiveSheet()->getStyle('J3:J'. $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('L3:L'. $tam)->getNumberFormat()->setFormatCode('#,##0.00');
        $this->spreadsheet->getActiveSheet()->getStyle('D3:D'. $tam)->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD2);

        //$this->spreadsheet->getActiveSheet()->getStyle('A3:' . $tam )->getAlignment()->setHorizontal('center');
        //$this->spreadsheet->getActiveSheet()->getStyle('A3:P' . $tam )->getAlignment()->setHorizontal('center');


    }

    function  encabezadoHoja1(){
        //Para hacer mas facil el acceso creo una variable para la hoja 1
        $hoja1 = $this->spreadsheet->setActiveSheetIndex(0);

        //Se le cambia el nombre a la hoja 1 (default)
        $hoja1->setTitle('Contratos');



        $this->titulos_columnas = array(
            'CVE_UNIDAD RESPONSABLE',
            'UNIDAD RESPONSABLE',
            'ESTADO',
            'FECHA CAPTURA',
            'ID CONTRATO',
            'NUMERO CONTRATO',
            'FECHA DEL CONTRATO',
            'FECHA INICIO',
            'FECHA TÉRMINO',
            'IMPORTE',
            'PORCENTAJE',
            'IMPORTE',
            'TIPO',
            'MODALIDAD',
            'CONTRATISTA',
            'RFC',
            'OBJETO DEL CONTRATO',
        );

        $hoja1
            ->fromArray(
                $this->titulos_columnas,  // Arreglo de los datos a establecer
                null,        // Los valores de esta matriz y con este valor no se estableceran
                'A2'         // Rango de celda o celda en donde deseamos que comience a pintar
            );

        $hoja1->setCellValue('K1', 'ANTICIPO');
        $hoja1->getStyle('K1' )->getBorders()->getOutline()->setBorderStyle('thin');
        $hoja1->getStyle('K1' )->getAlignment()->setHorizontal('center');
        $hoja1->mergeCells('K1:L1');
        $hoja1->mergeCells('M1:Q1');
        $hoja1->mergeCells('A1:J1');


        //Se cambio el color de la fuente
        //$hoja1->getStyle('A1:Q2')->getFont()->getColor()->setRGB('FFFFFF');
        //Se ponen en negritas
        $hoja1->getStyle('A1:Q2')->getFont()->setBold(true);

        $hoja1->getStyle('A2:Q2' )->getBorders()->getOutline()->setBorderStyle('thin');

        $hoja1->getColumnDimension('A')->setWidth(10);
        $hoja1->getColumnDimension('B')->setWidth(40);
        $hoja1->getColumnDimension('C')->setWidth(18);
        $hoja1->getColumnDimension('D')->setWidth(15);
        $hoja1->getColumnDimension('E')->setWidth(12);
        $hoja1->getColumnDimension('F')->setWidth(25);
        $hoja1->getColumnDimension('G')->setWidth(20);
        $hoja1->getColumnDimension('H')->setWidth(20);
        $hoja1->getColumnDimension('I')->setWidth(20);
        $hoja1->getColumnDimension('J')->setWidth(20);
        $hoja1->getColumnDimension('K')->setWidth(14);
        $hoja1->getColumnDimension('L')->setWidth(20);
        $hoja1->getColumnDimension('M')->setWidth(30);
        $hoja1->getColumnDimension('N')->setWidth(28);
        $hoja1->getColumnDimension('O')->setWidth(35);
        $hoja1->getColumnDimension('P')->setWidth(18);
        $hoja1->getColumnDimension('Q')->setWidth(35);


    }

    function consultaHoja1()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();

/*        $conexion->setSql('
             SELECT gpo_id,
                   ur_id,
                   ur_nombre,
                   eip_nombre,
                   co.usr_fecha,
                   ipu_id,
                   ipu_numeroContrato,
                   ipu_fechaContrato,
                   ipu_inicio,
                   ipu_termino,
                   ipu_importeTotal,
                   ipu_porcentajeAnticipo,
                   ipu_importeAnticipo,
                   tci_nombre,
                   mdc_nombre,
                   CONCAT(rfc_nombre, CASE TRIM(IFNULL(rfc_paterno, ""))
                                          WHEN "" THEN ""
                                          ELSE concat(" ", rfc_paterno)END,
                          CASE TRIM(IFNULL(rfc_materno, ""))
                              WHEN "" THEN ""
                              ELSE concat(" ", rfc_materno) END) as nombre,
                   rfc_id                                        as rfc,
                   ipu_objetoContrato                                            as objContrato
              FROM cat_ObraActividad                           co
                       INNER JOIN enc_Programatico USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                       INNER JOIN cat_UnidadResponsable USING (gpo_id, ur_id)
                       INNER JOIN
                                  (
                                      SELECT gpo_id, ur_id, epg_id, ipu_id
                                        FROM enc_InversionObra
                                       GROUP BY gpo_id, ur_id, epg_id, ipu_id
                                  ) AS                         X USING (gpo_id, ur_id, epg_id)
                       INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
                       INNER JOIN sinpres.cat_TipoContratoInversion USING (tci_id)
                       INNER JOIN sinpres.cat_ModalidadContratacion USING (mdc_id)
                       INNER JOIN sinpres.cat_EstadoInversion USING (eip_id)
                       INNER JOIN sinpres.cat_Proveedores USING (rfc_id)
                ORDER BY gpo_id,ur_id;
			');*/

        $conexion->setSql('
        SELECT CONCAT(gpo_id, LPAD(ur_id, 2, 0))AS ur, vur_nombre,
               eip_nombre, ci.usr_fecha,
               ipu_id, ipu_numeroContrato, ipu_fechaContrato, ipu_inicio, ipu_termino, ipu_importeTotal,
               ipu_porcentajeAnticipo, ipu_importeAnticipo,
               tci_nombre, mdc_nombre,
               CONCAT(rfc_nombre, CASE TRIM(IFNULL(rfc_paterno, ""))
                                      WHEN "" THEN ""
                                      ELSE concat(" ", rfc_paterno)END,
                      CASE TRIM(IFNULL(rfc_materno, ""))
                          WHEN "" THEN ""
                          ELSE concat(" ", rfc_materno) END) as nombre,
               rfc_id as rfc,
               ipu_objetoContrato as objContrato
          FROM sinpres.cat_InversionPublica ci
                   INNER JOIN sinpres.vin_UnidadResponsable USING (vur_id)
                   INNER JOIN sinpres.cat_EstadoInversion USING (eip_id)
                   INNER JOIN sinpres.cat_Proveedores USING (rfc_id)
                   INNER JOIN sinpres.cat_TipoContratoInversion USING (tci_id)
                   INNER JOIN sinpres.cat_ModalidadContratacion USING (mdc_id)
       #  WHERE ipu_ejercicio = 2022 
         GROUP BY gpo_id, ur_id, ipu_id
         ORDER BY ipu_id;
			');

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();

        while ($value = $conexion->extraer()) {
            $dato = array(
                'ur_id' => $value['ur'],
                'ur_nombre' => $value['vur_nombre'],
                'estado' => $value['eip_nombre'],
                'fecha_captura' => $value['usr_fecha'],
                'contrato_id' => $value['ipu_id'],
                'num_contrato' => $value['ipu_numeroContrato'],
                'fecha_contrato' => $value['ipu_fechaContrato'],
                'fecha_inicio' => $value['ipu_inicio'],
                'fecha_termino' => $value['ipu_termino'],
                'importe' => $value['ipu_importeTotal'],
                'porcentaje' => $value['ipu_porcentajeAnticipo'],
                'importe_ant' => $value['ipu_importeAnticipo'],
                'tipo' => $value['tci_nombre'],
                'modalidad' => $value['mdc_nombre'],
                'nombre' => $value['nombre'],
                'rfc' => $value['rfc'],
                'obj_contrato' => $value['objContrato'],

            );
            array_push($datos, $dato);

        }

        return $datos;
    }

    public function hoja2()
    {
        // Crear nueva hoja de trabajo
        $nuevaHoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($this->spreadsheet, 'Obras - contratos');

        // Se agrega la nueva hoja
        $this->spreadsheet->addSheet($nuevaHoja, 1);

        //Para hacer mas facil el acceso creo una variable para la hoja 2
        $hoja2 = $this->spreadsheet->setActiveSheetIndex(1);

        $datos = $this->consultaHoja2();

        //Se añade a la hoja el arreglo con los datos obtenidos
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $datos,// Arreglo de los datos a establecer
                null,// Los valores de esta matriz y con este valor no se estableceran
                'A2'// Rango de celda o celda en donde deseamos que comience a pintar
            );

        $tam = count($datos) + 1;

        $this->spreadsheet->getActiveSheet()->getStyle('G1:G'. $tam)->getNumberFormat()->setFormatCode('#,##0.00');

    }

    public function consultaHoja2(){
        $hoja2 = $this->spreadsheet->setActiveSheetIndex(1);

        $hoja2->setCellValue('A1', 'ID CONTRATO');
        $hoja2->setCellValue('B1', 'NÚMERO CONTRATO');
        $hoja2->setCellValue('C1', 'NÚMERO OBRA');
        $hoja2->setCellValue('D1', 'NOMBRE DE LA OBRA');
        $hoja2->setCellValue('E1', 'CVEFIN');
        $hoja2->setCellValue('F1', 'DESCRIPCION CVEFIN');
        $hoja2->setCellValue('G1', 'PRESUPUESTO');
        $hoja2->setCellValue('H1', 'OFICIO AUTORIZACIÓN');
        $hoja2->setCellValue('I1', 'FECHA DE AUTORIZACIÓN');
        $hoja2->setCellValue('J1', 'PIP');
        $hoja2->setCellValue('K1', 'AÑO DE LA OBRA');

        $hoja2->getStyle('A1:M1')->getFont()->setBold(true);

        $hoja2->getColumnDimension('A')->setWidth(13);
        $hoja2->getColumnDimension('B') ->setWidth(28);
        $hoja2->getColumnDimension('C')->setWidth(20);
        $hoja2->getColumnDimension('D')->setWidth(35);
        $hoja2->getColumnDimension('E')->setWidth(20);
        $hoja2->getColumnDimension('F')->setWidth(30);
        $hoja2->getColumnDimension('G')->setWidth(18);
        $hoja2->getColumnDimension('H')->setWidth(30);
        $hoja2->getColumnDimension('I')->setWidth(22);
        $hoja2->getColumnDimension('J')->setWidth(30);
        $hoja2->getColumnDimension('K')->setWidth(10);

        $conexion = new AccesoDatos();
        $conexion->conectar();

        //Para pruebas en local y agilizar tiempo solo probar con 2019, para producción descomentar lo que falte de la consulta
        $conexion->setSql('
        SELECT ipu_id,
            ipu_numeroContrato,
            LPAD(prg_id, 3, 0) AS prg_id,
            LPAD(spr_id, 2, 0) AS spr_id,
            LPAD(py_id, 3, 0) AS py_id,
            LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
            ecf_id, cfi_clave, cfi_nombre,
            presupuesto,
            ore_oficioAut, ore_fechaOficioAut, ore_idMml,
            eio_ejercicio
    FROM sinpres.enc_InversionObras                  a
                INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
                INNER JOIN ctrl2023.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2023.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2023.enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2022 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2023.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN
                        (
                            SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                    oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id,
                                    cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2023.presupuesto
                                        JOIN ctrl2023.presupuestoVigente AS vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                        cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                        )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
    
    WHERE eio_ejercicio = 2022
    /*agregado a inicios 2022, para consultar info 2021*/   
    UNION ALL
            SELECT ipu_id,
            ipu_numeroContrato,
            LPAD(prg_id, 3, 0) AS prg_id,
            LPAD(spr_id, 2, 0) AS spr_id,
            LPAD(py_id, 3, 0) AS py_id,
            LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
            ecf_id, cfi_clave, cfi_nombre,
            presupuesto,
            ore_oficioAut, ore_fechaOficioAut, ore_idMml,
            eio_ejercicio
    FROM sinpres.enc_InversionObras                  a
                INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
                INNER JOIN ctrl2021.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2021.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2021.enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2021 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2021.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN
                        (
                            SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                    oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id,
                                    cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2021.presupuesto
                                        JOIN ctrl2021.presupuestoVigente AS vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                        cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                        )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
    
    WHERE eio_ejercicio = 2021
    /*fin*/

    UNION ALL
            SELECT ipu_id,
            ipu_numeroContrato,
            LPAD(prg_id, 3, 0) AS prg_id,
            LPAD(spr_id, 2, 0) AS spr_id,
            LPAD(py_id, 3, 0) AS py_id,
            LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
            ecf_id, cfi_clave, cfi_nombre,
            presupuesto,
            ore_oficioAut, ore_fechaOficioAut, ore_idMml,
            eio_ejercicio
    FROM sinpres.enc_InversionObras                  a
                INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
                INNER JOIN ctrl2020.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2020.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2020.enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2020 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2020.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN
                        (
                            SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                    oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id,
                                    cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2020.presupuesto
                                        JOIN ctrl2020.presupuestoVigente AS vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                        cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                        )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
    
    WHERE eio_ejercicio = 2020
        UNION ALL
            SELECT ipu_id,
            ipu_numeroContrato,
            LPAD(prg_id, 3, 0) AS prg_id,
            LPAD(spr_id, 2, 0) AS spr_id,
            LPAD(py_id, 3, 0) AS py_id,
            LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
            ecf_id, cfi_clave, cfi_nombre,
            presupuesto,
            ore_oficioAut, ore_fechaOficioAut, ore_idMml,
            eio_ejercicio
    FROM sinpres.enc_InversionObras                  a
                INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
                INNER JOIN ctrl2019.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2019.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                INNER JOIN ctrl2019.enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2019 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2019.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
                INNER JOIN
                        (
                            SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                    oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id,
                                    cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2019.presupuesto
                                        JOIN ctrl2019.presupuestoVigente AS vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                                GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                        cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                        )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
    
    WHERE eio_ejercicio = 2019
        UNION ALL
     SELECT ipu_id,
           ipu_numeroContrato,
           LPAD(prg_id, 3, 0) AS prg_id,
           LPAD(spr_id, 2, 0) AS spr_id,
           LPAD(py_id, 3, 0) AS py_id,
           LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
           ecf_id, cfi_clave, cfi_nombre,
           presupuesto,
           ore_oficioAut, ore_fechaOficioAut, ore_idMml,
           eio_ejercicio
      FROM sinpres.enc_InversionObras                  a
               INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
               INNER JOIN ctrl2018.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2018.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2018.enc_ClaveFinanciamiento USING (ecf_id)
               INNER JOIN sinpres.cat_ClaveFinanciamiento2018 USING (cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
               INNER JOIN ctrl2018.poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2020.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
               INNER JOIN
                          (
                              SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                     oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id,
                                     cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2018.presupuesto
                                         JOIN ctrl2018.presupuestoVigente AS vig USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
                               GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                        cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
                          )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id, cff_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
     
     WHERE eio_ejercicio = 2018 
     -- ¿Al subir a produccion la siguiente parte debe estar descomentada.?
     -- Verificar hasta cuando ejercicios anteriores se deben mostrar 
     /*
    UNION ALL
       SELECT ipu_id,
           ipu_numeroContrato,
           LPAD(prg_id, 3, 0) AS prg_id,
           LPAD(spr_id, 2, 0) AS spr_id,
           LPAD(py_id, 3, 0) AS py_id,
           LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
           ecf_id, cfi_clave, cfi_nombre,
           presupuesto,
           ore_oficioAut, ore_fechaOficioAut, ore_idMml,
           eio_ejercicio
      FROM sinpres.enc_InversionObras                  a
               INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
               INNER JOIN ctrl2017.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2017.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2017.enc_ClaveFinanciamiento USING (ecf_id)
               INNER JOIN sinpres.cat_ClaveFinanciamiento USING ( fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
               INNER JOIN ctrl2017.poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2020.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
               INNER JOIN
                          (
                              SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                     oa_id, sft_id, ori_id, sor_id, cfi_id,
                                     cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2017.presupuesto
                               GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                         sft_id, ori_id, sor_id, cfi_id, cfi_anio
                          )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id,  sft_id, ori_id, sor_id, cfi_id, cfi_anio)
     
     WHERE eio_ejercicio = 2017 
    UNION ALL
   SELECT ipu_id,
           ipu_numeroContrato,
           LPAD(prg_id, 3, 0) AS prg_id,
           LPAD(spr_id, 2, 0) AS spr_id,
           LPAD(py_id, 3, 0) AS py_id,
           LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
           ecf_id, cfi_clave, cfi_nombre,
           presupuesto,
           ore_oficioAut, ore_fechaOficioAut, ore_idMml,
           eio_ejercicio
      FROM sinpres.enc_InversionObras                  a
               INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
               INNER JOIN ctrl2016.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2016.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2016.enc_ClaveFinanciamiento USING (ecf_id)
               INNER JOIN sinpres.cat_ClaveFinanciamiento USING ( fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
               INNER JOIN ctrl2016.poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2020.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
               INNER JOIN
                          (
                              SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                     oa_id, sft_id, ori_id, sor_id, cfi_id,
                                     cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2016.presupuesto
                               GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                         sft_id, ori_id, sor_id, cfi_id, cfi_anio
                          )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id,  sft_id, ori_id, sor_id, cfi_id, cfi_anio)
     
     WHERE eio_ejercicio = 2016 
    UNION ALL 
       SELECT ipu_id,
           ipu_numeroContrato,
           LPAD(prg_id, 3, 0) AS prg_id,
           LPAD(spr_id, 2, 0) AS spr_id,
           LPAD(py_id, 3, 0) AS py_id,
           LPAD(oa_id, 3, 0) AS oa_id, oa_nombre,
           ecf_id, cfi_clave, cfi_nombre,
           presupuesto,
           ore_oficioAut, ore_fechaOficioAut, ore_idMml,
           eio_ejercicio
      FROM sinpres.enc_InversionObras                  a
               INNER JOIN sinpres.cat_InversionPublica i USING (ipu_id)
               INNER JOIN ctrl2015.enc_Programatico USING (epg_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2015.cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
               INNER JOIN ctrl2015.enc_ClaveFinanciamiento USING (ecf_id)
               INNER JOIN sinpres.cat_ClaveFinanciamiento USING ( fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
               INNER JOIN ctrl2015.poa_OrigenRecurso USING (gpo_id, ur_id, epg_id, ecf_id) --    INNER JOIN ctrl2020.presupuesto USING( gpo_id,ur_id, epg_id, ecf_id, prg_id,spr_id,py_id,oa_id ,cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
               INNER JOIN
                          (
                              SELECT gpo_id, ur_id, epg_id, prg_id, spr_id, py_id,
                                     oa_id, sft_id, ori_id, sor_id, cfi_id,
                                     cfi_anio, SUM(pre_vigente) presupuesto
                    
                                FROM ctrl2015.presupuesto
                               GROUP BY gpo_id, ur_id, epg_id, prg_id, spr_id, py_id, oa_id,
                                         sft_id, ori_id, sor_id, cfi_id, cfi_anio
                          )    p USING (gpo_id, ur_id, epg_id,  prg_id, spr_id, py_id, oa_id,  sft_id, ori_id, sor_id, cfi_id, cfi_anio)
     
     WHERE eio_ejercicio = 2015   
     */
    ORDER BY ipu_id;
			');

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();

        while ($value = $conexion->extraer()) {
            $dato = array(
                'ipu_id' => $value['ipu_id'] ,
                'ipu_num' => $value['ipu_numeroContrato'],
                'numero_obra' => $value['prg_id'] . '-' . $value['spr_id'] . '-' . $value['py_id'] . '-' .$value['oa_id'],
                'oa_nombre' => $value['oa_nombre'],
                'cfi_clave' => $value['cfi_clave'],
                'cfi_nombre' => $value['cfi_nombre'],
                'presupuesto' => $value['presupuesto'],

                'ore_OficioAut' => $value['ore_oficioAut'],
                'ore_fechaOficioAut' => $value['ore_fechaOficioAut'],
                'ore_idMml' => ' ' . $value['ore_idMml'],
                'eio_ejercicio' => ' ' . $value['eio_ejercicio'],

            );
            array_push($datos, $dato);

        }

        return $datos;
    }

    public function hoja3()
    {
        // Crear nueva hoja de trabajo
        $nuevaHoja = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($this->spreadsheet, 'Retenciones Contratos');

        // Se agrega la nueva hoja
        $this->spreadsheet->addSheet($nuevaHoja, 2);

        //Para hacer mas facil el acceso creo una variable para la hoja 2
        $hoja = $this->spreadsheet->setActiveSheetIndex(2);

        $datos = $this->consultaHoja3();

        //Se añade a la hoja el arreglo con los datos obtenidos
        $hoja
            ->fromArray(
                $datos,// Arreglo de los datos a establecer
                null,// Los valores de esta matriz y con este valor no se estableceran
                'A2'// Rango de celda o celda en donde deseamos que comience a pintar
            );


    }

    public function consultaHoja3(){
        $hoja = $this->spreadsheet->setActiveSheetIndex(2);

        $hoja->setCellValue('A1', 'ID CONTRATO');
        $hoja->setCellValue('B1', 'NUMERO CONTRATO');
        $hoja->setCellValue('C1', 'NÚMERO');
        $hoja->setCellValue('D1', 'TIPO RETENCIÓN');
        $hoja->setCellValue('E1', 'CUENTA');
        $hoja->setCellValue('F1', 'NOMBRE');
        $hoja->setCellValue('G1', 'EJERCICIO');

        //Se ponen en negritas
        $hoja->getStyle('A1:G1')->getFont()->setBold(true);

        $hoja->getColumnDimension('A')->setWidth(20);
        $hoja->getColumnDimension('B')->setWidth(28);
        $hoja->getColumnDimension('C')->setWidth(10);
        $hoja->getColumnDimension('D')->setWidth(35);
        $hoja->getColumnDimension('E')->setWidth(17);
        $hoja->getColumnDimension('F')->setWidth(30);
        $hoja->getColumnDimension('G')->setWidth(15);

        $conexion = new AccesoDatos();
        $conexion->conectar();


        $conexion->setSql('
        SELECT ipu_id,ipu_numeroContrato,tre_nombre,cgc_cuenta,cgc_nombre,eir_ejercicio
                        FROM	sinpres.cat_InversionPublica ci
                        INNER JOIN sinpres.enc_InversionPublicaRetencion USING (ipu_id)
                        INNER JOIN sinpres.cat_RetencionCuenta USING( tre_id,rcu_id )
                        INNER JOIN sinpres.cat_TipoRetencion USING( tre_id)
                        LEFT JOIN cg_CuentaCuarto USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id)
                         # WHERE ipu_ejercicio = 2019
                    ORDER BY ipu_id;

			');

        $conexion->consultar();
        $conexion->desconectar();

        $datos = array();
        $numero = 0;
        $ipu_id = '';
        while ($value = $conexion->extraer()) {

            if($ipu_id == $value['ipu_id']){
                $numero++;
            }
            else{
                $numero = 1;
            }

            $dato = array(
                'ipu_id' => $value['ipu_id'],
                'ipu_numeroContrato' => $value['ipu_numeroContrato'],
                'numero' => $numero,
                'tre_nombre' => $value['tre_nombre'],
                'cgc_cuenta' => $value['cgc_cuenta'],
                'cgc_nombre' => $value['cgc_nombre'],
                'eir_ejercicio' => $value['eir_ejercicio'],

            );
            array_push($datos, $dato);

            $ipu_id = $value['ipu_id'];
        }

        return $datos;

    }
}

$exp_obraPub = new exp_ObraPub();