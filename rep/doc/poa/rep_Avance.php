<?php

session_start();
ini_set('memory_limit', '100M');
set_time_limit(0);
//librerias
require_once '../../../ent/Entidad.php';
require_once '../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../lib/helpers.php';

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $nombreUr;
    public $ultBorde = 0;
    public $widths = '';

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = new Usuario();
        $this->usr_id->setUsr_id($_SESSION[_S]['usr_id']);
        $this->usr_id->select();
    }

    public function Footer()
    {
        $tmpb = $this->GetBorders();
        $this->SetY(-16);
        $datos = $this->getDatos();

        $this->SetFont('Arial', '', 5);
        $this->Cell(127.5, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode('TITULAR DE LA DEPENDENCIA/ENTIDAD'), 0, 1, 'C');

        $this->Cell(127.5, 2, '', 0, 1, 'C');
        $this->SetFont('Arial', '', 5);
        $this->Cell(127.5, 2, utf8Decode($datos['ur_nombreAdministrativo']), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode($datos['ur_nombreTitular']), 0, 1, 'C');
        $this->ln(1);
        $this->SetFont('Arial', '', 5);
        $this->Cell(127.5, 2, utf8Decode($datos['ur_cargoAdministrativo']), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode($datos['ur_cargoTitular']), 0, 1, 'C');

        if ($_SESSION[_S]['nvj_id'] >= 2 && $_SESSION[_S]['nvj_id'] < 5) {
            $this->Cell(10, 10, 'USUARIO: ' . $this->usr_id->getUsuario(), 0, 0, 'L');
        }
        $hoy = date('d-m-Y');
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}' . '   ' . 'FECHA:' . $hoy . '', 0, 0, 'C');
        $this->SetBorders(0);
    }

    //fin de la funcion footer

    public function Header()
    {
        //Encabezado del documento
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
            $trim = $_POST['capturandoTri2'];
        } else {
            $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
            $trim = $_POST['capturandoTri'];
        }

        $datos = $this->getDatos();

        $this->SetMargins(7, '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(1);

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->setRouteImg2('escudo2');
        $this->Image($this->getRouteImg(), $ix + 180, $iy, '85', '24', 'JPG', '');
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $iy + 8);
        $ytmp = $this->GetY();
        $this->Cell(264, 0, utf8Decode('REPORTE DE AVANCE DE GESTIÓN'), 0, 0, 'L');

        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->Cell(264, 5, 'UR ' . $datos['ur_clave'] . ' - ' . utf8Decode($datos['ur_nombre']), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $ytmp + 5);
        $ytmp = $this->GetY();

        $tri = [' - ', 'PRIMER', 'SEGUNDO', 'TERCER', 'CUARTO', 'CUARTO'];
        $this->Cell(264, 5, $tri[$trim] . ' TRIMESTRE ' . APP_EJERCICIO, 0, 0, 'L');
        $this->SetFont('Arial', 'B', 7);
        $ytmp = $this->GetY();

        $this->SetBorders(0);
        $ix = $this->GetX();

        $this->SetBorders(0);
        $this->SetFont('Arial', 'B', 10);
        $this->ln(9);
        $this->SetBorders($this->ultBorde);
    }
}

if ($_SESSION[_S]['nvj_id'] > 2) {
    $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
    $trim = $_POST['capturandoTri2'];
} else {
    $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
    $trim = $_POST['capturandoTri'];
}

$sql = '
    SELECT gpo_id,ur_id,ue_id,prg_id,spr_id,oa_id,epg_id,
        ur_clave, ur_nombre,pdf_id,pdi_id,
        ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo,
        ur_cargoTitular, ur_objetivoGeneral, prg_nombre,
        fin, vpm_resumenNarrativo AS proposito,CONCAT(pf.pdf_id,"-",pf.pdf_nombre) AS mcomponente,
        CONCAT(ue_id,"-",ue_nombre) AS ues,
        CONCAT(pi.pdi_id,"-",pi.pdi_nombre) AS mactividad,
        CONCAT(prog.prg_id,"-",prog.prg_nombre) AS programa,
        CONCAT(prog.spr_id,"-",prog.spr_nombre) AS subprograma,
        CONCAT(prog.py_id,"-",prog.py_nombre) AS proyecto,
        CONCAT(prog.oa_id,"-",prog.oa_nombre) AS oa,
        CONCAT(eje_id,"-",eje_nombre) AS eje_nombre,
        CONCAT(tem_id,"-",tem_nombre) AS tem_nombre,
        CONCAT(obj_id,"-",obj_nombre) AS obj_nombre,
        CONCAT(est_id,"-",est_nombre) AS est_nombre,
        CONCAT(vin.lac_id,"-",lac_nombre) AS lac_nombre,
        aprobado, ampliaciones,reducciones,modificado,devengado,
        apr_trimestre1,apr_trimestre2,apr_trimestre3,apr_trimestre4,
        vig_trimestre1,vig_trimestre2,vig_trimestre3,vig_trimestre4,
        comprometido1,comprometido2,comprometido3,comprometido4,
        ejercido1,ejercido2,ejercido3,ejercido4,
        devengado1, devengado2, devengado3, devengado4,
        prgs_hombres, prgs_mujeres, prgs_total, pbf_nombre
FROM(
    SELECT epg_id, gpo_id, ur_id,ue_id, prg_id, spr_id, py_id, oa_id, prg_nombre,
    pdf_id, spr_nombre, py_nombre, oa_nombre, prgs_hombres, prgs_mujeres, prgs_total, pbf_nombre
    FROM cat_Programa prg
    INNER JOIN cat_Subprograma spr USING (gpo_id,ur_id,prg_id)
    INNER JOIN cat_Proyecto py USING (gpo_id,ur_id,prg_id,spr_id)
    INNER JOIN cat_ObraActividad oa USING (gpo_id,ur_id,prg_id,spr_id,py_id)
    INNER JOIN enc_Programatico eprg USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
    INNER JOIN (
    SELECT prgs_id AS prg_id, prgs_hombres, prgs_mujeres, prgs_total, pbf_nombre
    FROM cat_Programas
    INNER JOIN cat_PoblacionBeneficiada USING(pbf_id)
    )pob USING(prg_id)
    WHERE ' . $ur . '
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
            WHERE ' . $ur . '
        ) eprg
        INNER JOIN cat_ObraActividad USING(gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
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
            WHERE ' . $ur . '
            GROUP BY gpo_id, ur_id, prg_id, spr_id, py_id, oa_id, epg_id
        ) AS temp USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id)
    WHERE ' . $ur . '
    AND(vig_trimestre' . $trim . '>0 || mod_trimestre' . $trim . ' >0)
    AND ((eoa_id=5 AND toa_id=2) OR (eoa_id=4 AND toa_id=1))
    GROUP BY gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,epg_id,pii_id
) meta USING(epg_id)
INNER JOIN (SELECT epg_id,eje_id,eje_nombre,tem_id,tem_nombre,obj_id,obj_nombre,
        est_id,est_nombre,lac_id,lac_nombre,fin,vpm_resumenNarrativo
    FROM (
        SELECT epg_id,MAX(epe_id) epe_id, MAX(eps_id) eps_id,prg_id
        FROM enc_Programatico ep
        INNER JOIN vin_Programatico USING(epg_id)
        WHERE ' . $ur . '
        GROUP BY epg_id
        ) vp
        INNER JOIN enc_Ped USING(epe_id)
        INNER JOIN cat_Eje USING(eje_id)
        INNER JOIN cat_Tema t USING(eje_id, tem_id)
        INNER JOIN cat_Objetivo o USING(eje_id, tem_id, obj_id)
        INNER JOIN cat_Estrategia USING(eje_id,obj_id,est_id,tem_id)
        INNER JOIN cat_LineaAccion lac USING (eje_id,obj_id,est_id,tem_id,lac_id)
        LEFT JOIN ( SELECT prgs_id AS prg_id, obj_descripcion
                    FROM vin_Programa
                        INNER JOIN enc_PedObjetivo USING (epo_id)
                        INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
                    ) F USING (prg_id)
        INNER JOIN ( SELECT vpm_id, prgs_id AS prg_id, mir_id, vpm_resumenNarrativo,
                    mir_resumenNarrativo AS fin
                    FROM cat_Mir
                    INNER JOIN vin_ProgramasMir USING (mir_id)
                    ) P USING (prg_id)

) vin USING (epg_id)
INNER JOIN vin_ProductoIntermedioIndicador pfpi USING(pii_id,pdf_id)
    INNER JOIN cat_ProductoFinal pf USING(pdf_id)
    INNER JOIN cat_ProductoIntermedio pi USING(pdi_id)
    INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
    WHERE ' . $ur . '
    GROUP BY prg_id,spr_id,epg_id,pdi_id,vpm_resumenNarrativo,fin
    ORDER BY gpo_id,ur_id,prg_id,spr_id,oa_id,ue_id
    ;
';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();

$pdf = new Reportetabla('L', 'mm', 'letter');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setAltoLinea(2);

$contador = 0;
//declaración de valores para la bandera
$concact = '';
$banconcat = 0;
$prg_id_anterior = $act_anterior = $oa_anterior = '';
while ($row = $conexion->extraer()) {
    $prg_id = $row['prg_id'];
    $mactividad = $row['mactividad'];
    $obra = $row['oa'];
    //Condición para determinar si existe fin
    if ($row['fin'] == null) {
        $fin = 'N/A';
    } else {
        $fin = $row['fin'];
    }

    //Condición para determinar si existe proposito
    if ($row['proposito'] == null) {
        $pro = 'N/A';
    } else {
        $pro = $row['proposito'];
    }

    //Inicia cuerpo de impresión
    $pdf->setDatos($row);
    $pdf->AddPage();
    //cuerpo de la tabla
    $pdf->SetBorders(0);
    $pdf->SetFont('Arial', 'B', 5);
    //compara la bandera de impresión de encabezado 1 Imprime encabezado / 0 no imprime
    //Impresión de la Alineación PED y MIR
    if ($prg_id != $prg_id_anterior) {
        //Encabezados
        $pdf->SetFillColor(199, 209, 219);
        if ($pdf->PageNo() == 1) {
            $pdf->Cell(25, 4, utf8Decode('OBJETIVO INSTITUCIONAL:'), 0, 0, 'L', 1);
            $pdf->MultiCell(240, 3, utf8Decode($row['ur_objetivoGeneral']), 0, 'L', 0);
        }

        $pdf->Cell(25, 3, utf8Decode('ALINEACIÓN PED:'), 0, 0, 'L', 1);
        $pdf->Cell(124, 3, utf8Decode(''), 0, 0, 'L', 0);
        $pdf->Cell(116, 3, utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS:'), 0, 1, 'C', 1);
        //Datos del encabezado
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetWidths([25, 120, 4, 18, 98]);
        $pdf->SetAligns(['L', 'J', 'L']);
        $pdf->Row(['EJE: ', utf8Decode($row['eje_nombre']), '', 'FIN: ', utf8Decode($fin)]);
        $pdf->Row(['TEMA: ', utf8Decode($row['tem_nombre']), '', '', '']);
        $pdf->Row(['OBJETIVO: ', utf8Decode($row['obj_nombre']), '', utf8Decode('PROPÓSITO: '), strtoupper(utf8Decode($pro))]);
        $pdf->Row(['ESTRATEGIA: ', utf8Decode($row['est_nombre']), '', 'COMPONENTE: ', utf8Decode($row['mcomponente'])]);
        $pdf->Row([utf8Decode('LÍNEAS DE ACCIÓN: '), utf8Decode($row['lac_nombre']), '', 'ACTIVIDAD: ', utf8Decode($row['mactividad'])]);

        $pdf->SetWidths([30, 227]);
        $pdf->SetAligns(['L', 'J']);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['UE: ', utf8Decode($row['ues'])]);
        $pdf->ln(1);
    } else {
        if ($mactividad != $act_anterior) {
            //Encabezados
            $pdf->SetFillColor(199, 209, 219);
            $pdf->Cell(25, 6, utf8Decode('ALINEACIÓN PED:'), 0, 0, 'L', 1);
            $pdf->Cell(124, 6, utf8Decode(''), 0, 0, 'L', 0);
            $pdf->Cell(108, 6, utf8Decode('MATRÍZ DE INDICADORES PARA RESULTADOS:'), 0, 1, 'C', 1);

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetWidths([25, 120, 4, 18, 53]);
            $pdf->SetAligns(['L', 'J', 'L']);
            $pdf->Row(['ESTRATEGIA: ', utf8Decode($row['est_nombre']), '', 'COMPONENTE: ', utf8Decode($row['mcomponente'])]);
            $pdf->Row([utf8Decode('LÍNEAS DE ACCIÓN: '), utf8Decode($row['lac_nombre']), '', 'ACTIVIDAD: ', utf8Decode($row['mactividad'])]);
            $pdf->SetWidths([30, 227]);
            $pdf->SetAligns(['L', 'J']);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row(['UE: ', utf8Decode($row['ues'])]);
            $pdf->ln(1);
        } else {
            $pdf->SetWidths([30, 227]);
            $pdf->SetAligns(['L', 'J']);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row(['UE: ', utf8Decode($row['ues'])]);
            $pdf->ln(1);
        }
    }
    $prg_id_anterior = $prg_id;
    //Tabla de estructura programatica
    if ($obra != $oa_anterior) {
        $pdf->setBorders(1);
        $pdf->setAltoLinea(1.5);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([265]);
        $pdf->SetAligns(['C']);
        $pdf->SetRelleno('si');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['ESTRUCTURA PROGRAMATICA ']);
        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([59, 59, 68, 79]);
        $pdf->SetAligns(['C', 'C', 'C', 'C']);
        $pdf->Row(['PROGRAMA', 'SUBPROGRAMA', 'PROYECTO', 'OBRA/ACTIVIDAD']);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Row([utf8Decode($row['programa']), utf8Decode($row['subprograma']), utf8Decode($row['proyecto']), utf8Decode($row['oa'])]);
        $pdf->SetFillColor(255, 255, 255);

        // POBLACIÓN OBJETIVO TOMADA DE LOS DATOS DEL PROGRAMA
        $pdf->SetWidths([29.5, 29.5, 59, 24.5, 24.5, 24.5, 24.5, 24.5, 24.5]);
        $pdf->Row([utf8Decode('POBLACIÓN O ÁREA DE ENFOQUE POTENCIAL'), utf8Decode('ATRIBUTO DE LA POBLACIÓN O ÁREA DE ENFOQUE:'), utf8Decode($row['pbf_nombre']), 'HOMBRES:', utf8Decode($row['prgs_hombres']), 'MUJERES:', utf8Decode($row['prgs_mujeres']), 'TOTAL:', utf8Decode($row['prgs_total'])]);
        $pdf->ln(1);
        //tabla de presupuesto
        $pdf->setBorders(1);
        $pdf->setAltoLinea(1.5);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([265]);
        $pdf->SetAligns(['C']);
        $pdf->SetRelleno('si');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['PRESUPUESTO ANUAL']);
        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([25.5, 25.5, 25.5, 25.5, 42, 42, 39.5, 39.5]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
        $pdf->Row([
            'Aprobado',
            utf8Decode('Ampliación'),
            utf8Decode('Reducción'),
            utf8Decode('Modificado'),
            'EJERCIDO T1',
            'EJERCIDO T2',
            'EJERCIDO T3',
            'EJERCIDO T4',
        ]);
        $pdf->SetWidths([25.5, 25.5, 25.5, 25.5, 42, 42, 39.5, 39.5]);
        $pdf->SetRelleno('no');
        $pdf->SetBorders('LR');
        //para pintar el trimestre del devengado correspondiente
        $ejercidoT1 = $ejercidoT2 = $ejercidoT3 = $ejercidoT4 = 0;
        switch ($trim) {
            case 1:
                $ejercidoT1 = $row['ejercido1'];
                break;
            case 2:
                $ejercidoT1 = $row['ejercido1'];
                $ejercidoT2 = $row['ejercido2'];
                break;
            case 3:
                $ejercidoT1 = $row['ejercido1'];
                $ejercidoT2 = $row['ejercido2'];
                $ejercidoT3 = $row['ejercido3'];
                break;
            case 4:
                $ejercidoT1 = $row['ejercido1'];
                $ejercidoT2 = $row['ejercido2'];
                $ejercidoT3 = $row['ejercido3'];
                $ejercidoT4 = $row['ejercido4'];
                break;
        }
        $pdf->Row(['', '', '', '', $ejercidoT1, $ejercidoT2, $ejercidoT3, $ejercidoT4]);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'R', 'C']);
        $pdf->SetArrayBorders(['LR', 'LR', 'LR', 'LR', 'LT', 'RT']);

        //para pintar el trimestre del devengado correspondiente
        $DevengadoT1 = $DevengadoT2 = $DevengadoT3 = $DevengadoT4 = $totalEjercido = 0;
        switch ($trim) {
            case 1:
                $DevengadoT1 = $row['devengado1'];
                $totalEjercido = $row['ejercido1'];
                break;
            case 2:
                $DevengadoT1 = $row['devengado1'];
                $DevengadoT2 = $row['devengado2'];
                $totalEjercido = $row['ejercido1'] + $row['ejercido2'];
                break;
            case 3:
                $DevengadoT1 = $row['devengado1'];
                $DevengadoT2 = $row['devengado2'];
                $DevengadoT3 = $row['devengado3'];
                $totalEjercido = $row['ejercido1'] + $row['ejercido2'] + $row['ejercido3'];
                break;
            case 4:
                $DevengadoT1 = $row['devengado1'];
                $DevengadoT2 = $row['devengado2'];
                $DevengadoT3 = $row['devengado3'];
                $DevengadoT4 = $row['devengado4'];
                $totalEjercido = $row['ejercido1'] + $row['ejercido2'] + $row['ejercido3'] + $row['ejercido4'];
                break;
        }
        $pdf->SetWidths([25.5, 25.5, 25.5, 25.5, 123.5, 39.5]);

        $pdf->Row([$row['aprobado'], $row['ampliaciones'], $row['reducciones'], $row['modificado'], 'TOTAL EJERCIDO AL TRIMESTRE VIGENTE', $totalEjercido]);
        $pdf->SetArrayBorders(null);

        $pdf->setBorders(1);
        $pdf->SetWidths([265]);
        $pdf->SetAligns(['C']);
        $pdf->SetRelleno('si');
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['PRESUPUESTO TRIMESTRAL DE LA ACTIVIDAD']);

        if ($row['vig_trimestre' . $trim . ''] != 0) {
            $ejeVSmod = (floatval($row['ejercido' . $trim . '']) / floatval($row['vig_trimestre' . $trim])) * 100;
        } else {
            $ejeVSmod = 0;
        }


        $pdf->SetWidths([51, 51, 42, 42, 39.5, 39.5]);
        $pdf->SetRelleno('no');
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Row([
            'Aprobado',
            utf8Decode('Modificado'),
            utf8Decode('Comprometido'),
            utf8Decode('Devengado'),
            'Ejercido',
            utf8Decode('% de Ejercido Vs. Modificado'),
        ]);
        $pdf->Row([
            $row['apr_trimestre' . $trim],
            $row['vig_trimestre' . $trim],
            $row['comprometido' . $trim],
            $row['devengado' . $trim],
            $row['ejercido' . $trim],
            $ejeVSmod
        ]);
        $$oa_anterior = $obra;
    }
    $act_anterior = $mactividad;

    $conexionIndicador = new AccesoDatos();
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
            AND pdf_id = ?
            AND pdi_id = ?
            AND gpo_id = ?
            AND ur_id = ?
            AND prg_id= ?
            AND epg_id= ?
        GROUP BY gpo_id, ur_id, prg_id, pdf_id,pdi_id,epg_id,pii_id
        ;
    ');

    $params = [
        $row['pdf_id'],
        $row['pdi_id'],
        $row['gpo_id'],
        $row['ur_id'],
        $row['prg_id'],
        $row['epg_id'],
    ];

    $conexionIndicador->conectar();
    $conexionIndicador->consultar($params);
    $numMetas = 0;
    $primer = true;
    while ($rowind = $conexionIndicador->extraer()) { //$conexionIndicador
        $pdf->setBorders(1);
        $pdf->setAltoLinea(1.5);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([265]);
        $pdf->SetAligns(['C']);
        $pdf->SetRelleno('si');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['DATOS DEL INDICADOR']);

        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->ultBorde = $pdf->GetBorders();
        $pdf->SetWidths([25, 107.5, 25, 107.5]);
        $pdf->SetAligns(['R', 'L']);
        $pdf->SetFillColor(241, 244, 247);
        $pdf->Row(
            [
                'Nombre :',
                utf8Decode($rowind['ind_nombreMIR']),
                utf8Decode('Número de indicador :'),
                utf8Decode($rowind['ind_id']),
            ]
        );
        $pdf->SetWidths([25, 240]);
        $pdf->SetFillColor(239, 247, 255);
        $pdf->Row(
            [
                utf8Decode('Definición :'),
                utf8Decode($rowind['ind_definicionMIR']),
            ]
        );
        $pdf->SetFillColor(241, 244, 247);
        $pdf->Row(
            [
                utf8Decode('Método de Cálculo :'),
                utf8Decode($rowind['ind_formulaCalculo']),
            ]
        );

        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([42, 42, 55, 42, 42, 42]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C']);
        $pdf->Row(['Tipo', utf8Decode('Dimensión'), utf8Decode('Frecuencia de Medición'), utf8Decode('Forma de cálculo del indicador '), 'Tipo de Valor', 'Sentido Esperado']);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Row([utf8Decode($rowind['tpi_nombre']), utf8Decode($rowind['dim_nombre']), utf8Decode($rowind['frm_nombre']), utf8Decode($rowind['umi_nombre']), utf8Decode($rowind['tiv_nombre']), utf8Decode($rowind['ses_nombre'])]);

        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([132.5, 132.5]);
        $pdf->SetAligns(['C', 'C']);
        $pdf->Row([utf8Decode('MEDIOS DE VERIFICACIÓN / FUENTE INFORMACIÓN'), 'SUPUESTO']);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetBorders(1);
        $pdf->ultBorde = $pdf->GetBorders();
        $pdf->SetWidths([132.5, 132.5]);
        $pdf->SetAligns(['C', 'C']);
        $pdf->SetBorders(1);
        $pdf->Row([utf8Decode($rowind['mvac_nombre']), utf8Decode($rowind['spa_nombre'])]);

        $pdf->SetBorders(0);
        $pdf->ultBorde = $pdf->GetBorders();

        $conexionmeta = new AccesoDatos();
        $conexionmeta->setSql('
                SELECT
                    mac_descripcion,
                    macm_anio,
                    macm_valor,
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
                    IF(mac_tipoCalendario IS NULL,"S",mac_tipoCalendario) AS mac_tipoCalendario,
                    epg_id
                FROM cat_MetaActividad ma
                INNER JOIN cat_MetaActividadTrimestre USING(pii_id, epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING(pii_id)
                INNER JOIN enc_Programatico USING(epg_id)
                LEFT JOIN cat_MetaActividadMir USING (pii_id,gpo_id,ur_id)
                WHERE gpo_id = ? AND ur_id = ? AND ind_id IN(?)
                    AND pdi_id IN (?) AND epg_id= ?
                GROUP BY epg_id,pii_id,ma.macm_id;
            ');

        $params = [
            $rowind['gpo_id'],
            $rowind['ur_id'],
            $rowind['ind_id'],
            $rowind['pdi_id'],
            $rowind['epg_id'],
        ];

        $conexionmeta->conectar();
        $conexionmeta->consultar($params);
        $numMetas = 0;
        $primer = true;
        $numeroMeta = 0;
        $epg_id = 0;
        $sumametasanual = 0;

        while ($rowmeta = $conexionmeta->extraer()) {

            $numMetas++;
            if ($numMetas == 3) {

                $pdf->ln(1.5);
                $numMetas = 1;
            }

            $numeroMeta++;

            $pdf->CheckPageBreak(40);
            $pdf->setBorders(1);
            $pdf->setAltoLinea(1.5);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->SetWidths([265]);
            $pdf->SetAligns(['C']);
            $pdf->SetRelleno('si');
            $pdf->SetFont('Arial', 'B', 6);

            $pdf->Row(['DATOS DE LA META']);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetBorders(1);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([42.5, 222.5]);
            $pdf->SetAligns(['R', 'L']);
            $pdf->Row([utf8Decode('Definición de la variable Meta:'), $numeroMeta . ' - ' . utf8Decode($rowmeta['mac_descripcion'])]);
            $pdf->Row([utf8Decode('Unidad de medida:'), $rowind['umv_id'] . ' - ' . utf8Decode($rowind['umv_nombre'])]);
            $pdf->SetWidths([132.5, 132.5]);
            $pdf->SetAligns(['C', 'C']);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->Row([utf8Decode('LÍNEA BASE'), utf8Decode('VALOR PROGRAMADO ANUAL')]);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([20, 46.5, 20, 46, 20, 46.5, 20, 46]);
            $pdf->SetAligns(['C', 'C', 'C', 'C']);
            $pdf->Row([utf8Decode('AÑO'), $rowmeta['macm_anio'], 'VALOR', $rowmeta['macm_valor'], utf8Decode('AÑO'), APP_EJERCICIO, 'VALOR', $rowind['mac_original']]);

            // detalle de meta
            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([15, 60, 60, 60, 60]);
            $pdf->SetAligns(['C', 'C', 'C', 'C', 'C']);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);
            if ($rowmeta['mac_tipoCalendario'] == 'C') {
                $sumoriginalanual = $rowmeta['ori_trimestre1'];
                $summodificadoanual = $rowmeta['mod_trimestre1'];
                //mientras el 1 pero este trimestre se vera reflejado dependiendo el trimestre activo.
                $sumAvanceFisicoanual = $rowmeta['alc_trimestre1'];
            } else {
                $sumoriginalanual = $rowmeta['mac_original'];
                $summodificadoanual = $rowmeta['mac_modificado'];
                $sumAvanceFisicoanual = $rowmeta['mac_alcanzado'];
            }

            if ($sumametasanual == 0) {
                $unoporcavreportado = 0;
                $dosporcavreportado = 0;
                $tresporcavreportado = 0;
                $cuatroporcavreportado = 0;
                $porcentajeacumulado = 0;
            } else {
                $unoporcavreportado = ($rowmeta['alc_trimestre1'] / $sumametasanual) * 100;
                $dosporcavreportado = ($rowmeta['alc_trimestre2'] / $sumametasanual) * 100;
                $tresporcavreportado = ($rowmeta['alc_trimestre3'] / $sumametasanual) * 100;
                $cuatroporcavreportado = ($rowmeta['alc_trimestre4'] / $sumametasanual) * 100;

                $porcentajeacumulado = ($rowmeta['mac_tipoCalendario'] == 'C') ? ($rowmeta['alc_trimestre1'] * 25) / $sumametasanual : ($rowmeta['alc_trimestre1'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre2'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre3'] / $sumametasanual) * 100 +
                    ($rowmeta['alc_trimestre4'] / $sumametasanual) * 100;
                $porcentajeacumulado = $porcentajeacumulado * 100;
            }

            /* % ALCANZADO TRIMESTRAL */
            $porcentajealcanzadoTrim1 = $rowmeta['mod_trimestre1'] != 0 ? ($rowmeta['alc_trimestre1'] / $rowmeta['mod_trimestre1']) * 100 : 0;
            $porcentajealcanzadoTrim2 = $rowmeta['mod_trimestre2'] != 0 ? ($rowmeta['alc_trimestre2'] / $rowmeta['mod_trimestre2']) * 100 : 0;
            $porcentajealcanzadoTrim3 = $rowmeta['mod_trimestre3'] != 0 ? ($rowmeta['alc_trimestre3'] / $rowmeta['mod_trimestre3']) * 100 : 0;
            $porcentajealcanzadoTrim4 = $rowmeta['mod_trimestre4'] != 0 ? ($rowmeta['alc_trimestre4'] / $rowmeta['mod_trimestre4']) * 100 : 0;

            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([15, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L', 'L']);
            $pdf->SetBorders(1);
            $edoborde = 1;

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);

            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([256]);
            $pdf->SetAligns(['C']);
            $pdf->SetBorders(1);
            $edoborde = 1;

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();

            $pdf->SetBorders(1);
            $pdf->SetWidths([265]);
            $pdf->SetAligns(['C']);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->SetFont('Arial', 'B', 6);

            $pdf->Row(['AVANCE TRIMESTRAL OBRA/ACTIVIDAD']);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([20, 44.5, 44.5, 44.5, 44.5, 67]);
            $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C']);
            $pdf->SetBorders(1);
            $edoborde = 1;
            $pdf->Row(['METAS', '1er.TRIMESTRE', '2o.TRIMESTRE', '3er.TRIMESTRE', '4o.TRIMESTRE', 'CUMPLIMIENTO DE METAS']);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();

            $pdf->SetFont('Arial', '', 5);

            $trim1alc = 0;
            $trim2alc = 0;
            $trim3alc = 0;
            $trim4alc = 0;
            $trim1ori = $rowmeta['ori_trimestre1'];
            $trim1mod = ($trim >= 1 ? $rowmeta['mod_trimestre1'] : '-');
            $trim2ori = $rowmeta['ori_trimestre2'];
            $trim2mod = ($trim >= 2 ? $rowmeta['mod_trimestre2'] : '-');
            $trim3ori = $rowmeta['ori_trimestre3'];
            $trim3mod = ($trim >= 3 ? $rowmeta['mod_trimestre3'] : '-');
            $trim4ori = $rowmeta['ori_trimestre4'];
            $trim4mod = ($trim >= 4 ? $rowmeta['mod_trimestre4'] : '-');

            $acumuladoMod = ($trim >= 1 ? $rowmeta['mod_trimestre1'] : 0) + ($trim >= 2 ? $rowmeta['mod_trimestre2'] : 0) + ($trim >= 3 ? $rowmeta['mod_trimestre3'] : 0) + ($trim >= 4 ? $rowmeta['mod_trimestre4'] : 0);
            $alcTrim = 0;
            switch ($trim) {
                case 1:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $sumaalcanzados = $trim1alc;
                    $sumametasanual = $rowmeta['mod_trimestre1'];
                    $alcTrim = $porcentajealcanzadoTrim1;
                    break;
                case 2:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $sumaalcanzados = $trim1alc + $trim2alc;
                    $sumametasanual = $rowmeta['mod_trimestre1'] + $rowmeta['mod_trimestre2'];
                    $alcTrim = $porcentajealcanzadoTrim2;
                    break;
                case 3:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $trim3alc = $rowmeta['alc_trimestre3'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc;
                    $sumametasanual = $rowmeta['mod_trimestre1'] + $rowmeta['mod_trimestre2'] + $rowmeta['mod_trimestre3'];
                    $alcTrim = $porcentajealcanzadoTrim3;
                    break;
                case 4:
                    $trim1alc = $rowmeta['alc_trimestre1'];
                    $trim2alc = $rowmeta['alc_trimestre2'];
                    $trim3alc = $rowmeta['alc_trimestre3'];
                    $trim4alc = $rowmeta['alc_trimestre4'];
                    $sumaalcanzados = $trim1alc + $trim2alc + $trim3alc + $trim4alc;
                    $sumametasanual = $rowmeta['mod_trimestre1'] + $rowmeta['mod_trimestre2'] + $rowmeta['mod_trimestre3'] + $rowmeta['mod_trimestre4'];
                    $alcTrim = $porcentajealcanzadoTrim4;
                    break;
            }

            if ($sumametasanual == 0) {
                $porcentajeanual = 0;
            } else {
                $porcentajeanual = ($sumaalcanzados / $sumametasanual) * 100;
                $porcentajeanual = ($rowmeta['mac_tipoCalendario'] == 'C') ? (($rowmeta['alc_trimestre1'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre2'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre3'] * 25) / $sumametasanual) + (($rowmeta['alc_trimestre4'] * 25) / $sumametasanual) : ($rowmeta['alc_trimestre' . $trim . ''] / $sumametasanual) * 100;
            }

            if ($rowmeta['mac_modificado'] != 0) {
                $porcentajealcanual = ($sumaalcanzados / $rowmeta['mac_modificado']) * 100;
            } else {
                $porcentajealcanual = 0;
            }

            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([20, 44.5, 44.5, 44.5, 44.5, 27, 40]);
            $pdf->SetAligns(['L', 'L', 'L', 'L', 'L', 'L', 'R']);
            $pdf->SetBorders(1);

            $trimestres = 'T1+T2+T3+T4';

            $pdf->Row([utf8Decode('PROGRAMACIÓN INICIAL'), $trim1ori, $trim2ori, $trim3ori, $trim4ori, $trimestres , $sumoriginalanual]);
            $pdf->Row([utf8Decode('PROGRAMACIÓN ACTUALIZADA'), floatval($trim1mod), floatval($trim2mod), floatval($trim3mod), floatval($trim4mod), $trimestres, floatval($sumametasanual)]);
            $pdf->Row(['METAS ALCANZADAS', $trim1alc, $trim2alc, $trim3alc, $trim4alc, $trimestres, $sumaalcanzados]);
            $pdf->Row([
                'PORCENTAJE ALCANZADO TRIMESTRAL',
                floatval($porcentajealcanzadoTrim1),
                floatval($porcentajealcanzadoTrim2),
                floatval($porcentajealcanzadoTrim3),
                floatval($porcentajealcanzadoTrim4),
                'PORCENTAJE ALCANZADO ANUAL',
                floatval($porcentajealcanual)
            ]);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);

            $pdf->ln(1);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([150, 82, 23]);
            $pdf->SetAligns(['C', 'R', 'C']);
            $pdf->SetBorders(0);
            $edoborde = 1;
            $pdf->Row(['', 'PORCENTAJE DE CUMPLIMIENTO ALCANZADO EN EL TRIMESTRE:', $alcTrim . ' %']);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->ln(2);

            // fin de detalle de meta
            $primer = false;
            $epg_id = $rowmeta['epg_id'];
            if ($epg_id != $rowmeta['epg_id']) {
                $numeroMeta = 0;
            }
        }
        // fin de metas de una accion
        $conexionmeta->desconectar();
    } // fin $conexionIndicador
} //fin datos de la actividad

$conexion->desconectar();
$pdf->Output();
