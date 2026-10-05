<?php

session_start();

set_time_limit(0);

require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/Entidad.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/UnidadEjecutora.php';
require_once '../../../../lib/WebResponse.php';

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class reportetabla extends PDF
{
    public $nombreUr;
    public $ultBorde = 0;
    public $widths = '';

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer()
    {
        $tmpb = $this->GetBorders();
        $this->SetY(-20);
        $datos = $this->getDatos();

        $this->SetFont('Arial', '', 6.5);
        $this->Cell(127.5, 2, utf8Decode('JEFE ADMINISTRATIVO'), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode('TITULAR DE LA DEPENDENCIA/ENTIDAD'), 0, 1, 'C');

        $this->Cell(127.5, 2, '', 0, 1, 'C');
        $this->Cell(127.5, 2, '', 0, 1, 'C');
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(127.5, 2, utf8Decode($datos['ur_nombreAdministrativo']), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode($datos['ur_nombreTitular']), 0, 1, 'C');
        $this->ln(1);
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(127.5, 2, utf8Decode($datos['ur_cargoAdministrativo']), 0, 0, 'C');
        $this->Cell(127.5, 2, utf8Decode($datos['ur_cargoTitular']), 0, 1, 'C');

        $hoy = date('d-m-Y');
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}' . '   ' . 'FECHA:' . $hoy . '', 0, 0, 'C');
        $this->SetBorders(0);

        if ($_SESSION[_S]['nvj_id'] > 2) {
            $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
        } else {
            $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
        }
    }

    //fin de la funcion footer

    public function Header()
    {
        //Encabezado del documento
        if ($_SESSION[_S]['nvj_id'] > 2) {
            $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
        } else {
            $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
        }

        $datos = $this->getDatos();

        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(4);

        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->Image(__DIR__ . '/../../../../img/' . LOGO_ESCUDO, $ix + 144, $iy + 6, '100', '29', 'JPG', '');
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $iy + 12);
        $ytmp = $this->GetY() + 2;
        $this->Cell(264, 5, 'REPORTE DE AVANCE DE GESTION DEL COMPONENTE', 0, 0, 'L');

        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->Cell(264, 5, 'UR ' . $datos['ur_clave'] . ' - ' . utf8Decode($datos['ur_nombre']), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $ytmp + 5);
        $ytmp = $this->GetY();

        if (isset($_POST['semestre1']) && $_POST['semestre1'] != '') {
            $_POST['capturandoTri'] = $_POST['semestre1'];
        }

        if ($_POST['capturandoTri'] == 0) {
            $_POST['capturandoTri'] = $_POST['capturandoTri2'];
        }

        $tri = [' - ', 'PRIMER', 'SEGUNDO'];
        $this->Cell(264, 5, $tri[$_POST['capturandoTri']] . ' SEMESTRE', 0, 0, 'L');

        $this->SetFont('Arial', 'B', 7);
        $ytmp = $this->GetY();

        $this->SetBorders(0);

        $ix = $this->GetX();

        $this->SetBorders(0);
        $this->SetFont('Arial', 'B', 10);
        $this->ln(13);
        $this->SetBorders($this->ultBorde);
    }
}

if ($_SESSION[_S]['nvj_id'] > 2) {
    $ur = 'gpo_id = ' . $_POST['gpo_id'] . ' AND ur_id = ' . $_POST['ur_id'];
    $ur2 = 'ur.gpo_id = ' . $_POST['gpo_id'] . ' AND ur.ur_id = ' . $_POST['ur_id'];
} else {
    $ur = 'gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'];
    $ur2 = 'ur.gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur.ur_id = ' . $_SESSION[_S]['ur_id'];
}
$sql = '
    SELECT gpo_id,ur_id,prg_id,prg_nombre,spr_id,
        ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo,
        ur_cargoTitular, ur_clave, ur_nombre, fin,proposito,
        pdf_id, pdf_nombre AS componente,eje_nombre,tem_nombre, obj_nombre,
        ur_objetivoGeneral, emi_id,eje_id,tem_id,obj_id,
        aprobado,ampliaciones,reducciones, modificado, devengado,
        devTrimestre1,devTrimestre2,devTrimestre3,devTrimestre4
    FROM cat_UnidadResponsable ur
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN cat_Programa prg USING(gpo_id,ur_id)
    INNER JOIN cat_Subprograma sprg USING(gpo_id,ur_id,prg_id)
    INNER JOIN (
        SELECT prgs_id AS prg_id, MAX(mir_resumenNarrativo) AS fin, obj_descripcion,
            MAX(vpm_resumenNarrativo) AS proposito,
            eje_nombre,tem_nombre,obj_nombre, MAX(emi_id) emi_id,eje_id,tem_id,obj_id
        FROM vin_Programa
        INNER JOIN vin_ProgramasMir USING(prgs_id)
        INNER JOIN cat_Mir USING(mir_id)
        INNER JOIN enc_PedObjetivo USING (epo_id)
        INNER JOIN cat_Eje USING(eje_id)
        INNER JOIN cat_Tema t USING(eje_id,tem_id)
        INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
        GROUP BY prgs_id
    ) F USING (prg_id)
    INNER JOIN vin_ProductoIntermedioIndicador pfpi USING(pdf_id)
    INNER JOIN cat_ProductoFinal USING(pdf_id)
    INNER JOIN (
        SELECT gpo_id , ur_id , prg_id , spr_id,
            SUM(pre_aprobado) aprobado,
            SUM(pre_ampliacion) ampliaciones,
            SUM(pre_reduccion) reducciones,
            SUM(pre_vigente) modificado,
            SUM(eje_ene + eje_feb + eje_mar) trimestre1,
            SUM(eje_abr + eje_may + eje_jun) trimestre2,
            SUM(eje_jul + eje_ago + eje_sep) trimestre3,
            SUM(eje_oct + eje_nov + eje_dic) trimestre4,
            SUM(dev_ene + dev_feb + dev_mar) devTrimestre1,
            SUM(dev_abr + dev_may + dev_jun) devTrimestre2,
            SUM(dev_jul + dev_ago + dev_sep) devTrimestre3,
            SUM(dev_oct + dev_nov + dev_dic) devTrimestre4,
            SUM(pre_devengado) devengado
        FROM presupuesto
        INNER JOIN presupuestoAprobado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoVigente USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoComprometido USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoDevengado USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
        INNER JOIN presupuestoEjercido  USING(gpo_id, ur_id, ue_id, epg_id,pg_id,pe_id,ecf_id,tcp_id)
        WHERE ' . $ur . '
        GROUP BY gpo_id,ur_id,prg_id,spr_id
    )p USING (gpo_id,ur_id,prg_id,spr_id)
    WHERE ' . $ur . '
    GROUP BY gpo_id,ur_id,prg_id,spr_id
    ORDER BY prg_id , pdf_id;
';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();

$pdf = new Reportetabla('L', 'mm', 'letter');
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_POA);
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 21);
$pdf->setAltoLinea(2);

while ($row = $conexion->extraer()) {
    $pdf->setDatos($row);
    $pdf->AddPage();

    //cuerpo de la tabla
    $pdf->SetBorders(0);
    $pdf->SetFont('Arial', 'B', 5);

    if ($row['prg_id'] < 100) {
        $fin = $pro = ' - ';
        $com = $row['componente'];
        $titulo = 'DEL INDICADOR';
    } else {
        $fin = $row['fin'];
        $pro = $row['proposito'];
        $com = $row['componente'];
        $titulo = 'DE LA ACTIVIDAD';
    }

    $pdf->SetWidths([21, 18, 99, 18, 99]);
    $pdf->SetAligns(['R', 'L', 'L', 'L', 'L']);
    $pdf->Row([utf8Decode('ALINEACIÓN PED:'), 'EJE: ', $row['eje_id'] . '-' . utf8Decode($row['eje_nombre']), 'PROGRAMA: ', str_pad($row['prg_id'], 2, '0', STR_PAD_LEFT) . ' - ' . utf8Decode($row['prg_nombre'])]);
    $pdf->Row(['', 'TEMA: ', $row['tem_id'] . '-' . utf8Decode($row['tem_nombre']), 'FIN: ', utf8Decode($fin)]);
    $pdf->Row([utf8Decode(''), 'OBJETIVO: ', $row['obj_id'] . '-' . utf8Decode($row['obj_nombre']), utf8Decode('PROPÓSITO: '), utf8Decode($pro)]);
    $pdf->Row(['', '', '', 'COMPONENTE: ', utf8Decode($com)]);

    $pdf->SetWidths([30, 108, 18, 99]);
    $pdf->SetAligns(['L', 'L', 'L', 'L']);
    $pdf->Row(['OBJETIVO INSTITUCIONAL: ', utf8Decode($row['ur_objetivoGeneral'])]);
    $pdf->ln(1);

    $pdf->setBorders(1);
    $pdf->setAltoLinea(2.5);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetWidths([255]);
    $pdf->SetAligns(['C']);
    $pdf->SetRelleno('si');
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Row(['PRESUPUESTO DEL COMPONENTE']);

    $pdf->SetFont('Arial', 'B', 5.5);
    $pdf->SetBorders(1);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetWidths([25.5, 25.5, 25.5, 25.5, 41, 41, 25.5, 45.5]);
    $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
    $pdf->Row(['Aprobado', utf8Decode('Ampliación'), utf8Decode('Reducción'), utf8Decode('Modificado'), 'Ejercido 1er. SEMESTRE', 'Ejercido 2do. SEMESTRE', utf8Decode('Acumulado'), utf8Decode('% de Devengado Acum. Vs. Modificado')]);
    $pdf->SetFillColor(255, 255, 255);

    //para pintar el trimestre del devengado correspondiente
    $DevengadoT1 = $DevengadoT2 = 0;
    $Devengado = 0;
    switch ($_POST['capturandoTri']) {
        case 1:
            $DevengadoT1 = $Devengado = $row['devTrimestre1'] + $row['devTrimestre2'];

            break;
        case 2:
            $DevengadoT1 = $row['devTrimestre1'] + $row['devTrimestre2'];
            $DevengadoT2 = $row['devTrimestre3'] + $row['devTrimestre4'];
            $Devengado = $row['devengado'];
            break;
    }

    $devAcMod = fdiv($Devengado, $row['modificado']) * 100;
    $pdf->Row([$row['aprobado'], $row['ampliaciones'], $row['reducciones'], $row['modificado'], $DevengadoT1, $DevengadoT2, $row['devengado'], round($devAcMod, 2)]);

    //$pdf->ln(4);
    $conexionIndicador = new AccesoDatos();
    $conexionIndicador->setSql('
        SELECT
            pdf_id, epfi_id AS eri_id,ind_id,ind_nombre,ind_definicion,ind_nombreMIR, ind_definicionMIR,ind_objetivo,ind_formulaCalculo,
            tpi_id,tpi_nombre,u.umi_id AS umi_id,u.umi_nombre AS umi_nombreI,umv_id AS umi_mir,
            umv_nombre AS umiNombre_mir,dim_id,dim_nombre,frm_id,frm_nombre,ses_id,tiv_id,
            tiv_nombre,ses_nombre,nim_id, gpo_id,ur_id, prgs_id, sprs_id,
            mco_lb AS mem_anio,mco_lbValor AS mem_valor,mco_original AS mem_valorDeseado1
        FROM enc_ProductoFinalIndicador
        JOIN cat_Indicador i USING (nim_id , ind_id)
        JOIN cat_Dimension USING (dim_id)
        JOIN cat_FrecuenciaMedicion USING (frm_id)
        JOIN cat_TipoIndicador USING (tpi_id)
        JOIN cat_UnidadMedidaIndicador u USING (umi_id)
        JOIN cat_UnidadMedidaVariable umir USING (umv_id)
        JOIN cat_TipoValor USING (tiv_id)
        JOIN cat_SentidoEsperado USING (ses_id)
        LEFT JOIN cat_MetaComponente USING(epfi_id)
        WHERE nim_id = 3
        AND pdf_id = ?
        AND gpo_id = ?
        AND ur_id = ?
        AND prgs_id= ?
        GROUP BY gpo_id,ur_id,prgs_id,sprs_id,pdf_id,epfi_id;
    ');

    $params = [
        $row['pdf_id'],
        $row['gpo_id'],
        $row['ur_id'],
        $row['prg_id'],
    ];

    $conexionIndicador->conectar();
    $conexionIndicador->consultar($params);
    $numMetas = 0;
    $mediosVerUR = $supuestoUR = '';
    $primer = true;
    while ($rowind = $conexionIndicador->extraer()) { //$conexionIndicador
        $pdf->ln(1.5);

        $pdf->setBorders(1);
        $pdf->setAltoLinea(2.5);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([255]);
        $pdf->SetAligns(['C']);
        $pdf->SetRelleno('si');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Row(['DATOS DEL INDICADOR']);

        $pdf->SetFont('Arial', 'B', 5.5);
        $pdf->ultBorde = $pdf->GetBorders();
        $pdf->SetWidths([25, 230]);
        $pdf->SetAligns(['R', 'L']);
        $pdf->SetFillColor(241, 244, 247);
        $pdf->Row(
            [
                'Nombre :',
                utf8Decode($rowind['ind_nombreMIR']),
            ]
        );
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
                utf8Decode('Método de Calculo :'),
                utf8Decode($rowind['ind_formulaCalculo']),
            ]
        );

        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([40, 40, 55, 40, 40, 40]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C']);
        $pdf->Row(['Tipo', utf8Decode('Dimensión'), utf8Decode('Frecuencia de Medición'), 'Unidad de Medida', 'Tipo de Valor', 'Sentido Esperado']);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Row([utf8Decode($rowind['tpi_nombre']), utf8Decode($rowind['dim_nombre']), utf8Decode($rowind['frm_nombre']), utf8Decode($rowind['umiNombre_mir']), $rowind['tiv_nombre'], utf8Decode($rowind['ses_nombre'])]);

        $supMeCom = new AccesoDatos();
        $supMeCom->setSql('
            SELECT
            GROUP_CONCAT(DISTINCT mvcm_nombre, "\n" ORDER BY m.gpo_id, m.ur_id SEPARATOR "|") AS mediosVerificacion,
            GROUP_CONCAT(DISTINCT spc_nombre, "\n" ORDER BY s.gpo_id, s.ur_id SEPARATOR "|") AS supuesto
            FROM cat_SupuestoComponente s
            LEFT JOIN cat_MedioVerificacionComponente m USING(gpo_id, ur_id, prgs_id, sprs_id)
            WHERE gpo_id = ? AND ur_id = ? and prgs_id= ? and sprs_id= ?
            GROUP BY gpo_id, ur_id, prgs_id, sprs_id;
        ');
        $params = [
            $row['gpo_id'],
            $row['ur_id'],
            $row['prg_id'],
            $rowind['sprs_id'],
        ];
        $supMeCom->conectar();
        $supMeCom->consultar($params);
        $supMeCom->desconectar();

        while ($supMediosC = $supMeCom->extraer()) {
            $supuestoUR = str_replace('|', "\n", $supMediosC['supuesto']);
            $mediosVerUR = str_replace('|', "\n", $supMediosC['mediosVerificacion']);
        }

        $pdf->SetBorders(1);
        $pdf->SetFillColor(199, 209, 219);
        $pdf->SetWidths([127.5, 127.5]);
        $pdf->SetAligns(['C', 'C']);
        $pdf->Row([utf8Decode('MEDIOS DE VERIFICACIÓN / FUENTE INFORMACIÓN'), 'SUPUESTO']);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetBorders(1);
        $pdf->ultBorde = $pdf->GetBorders();
        $pdf->SetWidths([127.5, 127.5]);
        $pdf->SetAligns(['C', 'C']);
        $pdf->SetBorders(1);
        $pdf->Row([utf8Decode($mediosVerUR), utf8Decode($supuestoUR)]);
        $pdf->SetBorders(0);
        $pdf->ultBorde = $pdf->GetBorders();

        $conexionmeta = new AccesoDatos();
        $conexionmeta->setSql('
            SELECT epfi_id, mco_descripcion, tiv_id,
            mco_lb AS mem_anio,mco_lbIndicador AS mem_valor,mco_original AS mem_valorDeseado1, mco_original,
            SUM(IF(tri_id = 2, mcot_numerador, NULL)) mem_trimestre2,
            SUM(IF(tri_id = 4, mcot_numerador, NULL)) mem_trimestre4,mco_modificado,
            (
                SELECT SUM(IF(tri_id = 2, mcot_numeradorT2, NULL)) mem_nuevo2
                FROM aux_HistoricoModificacionMetaComponente hmc
                WHERE hmc.epfi_id=:eri_id and hmc.gpo_id =:gpo_id AND hmc.ur_id=:ur_id
            ) as mem_modTrimestre2,
            (
                SELECT SUM(IF(tri_id = 4, mcot_numeradorT4, NULL)) mem_nuevo4
                FROM aux_HistoricoModificacionMetaComponente hmc
                WHERE hmc.epfi_id=:eri_id and hmc.gpo_id =:gpo_id AND hmc.ur_id=:ur_id
            ) as mem_modTrimestre4, mco_alcanzado,
            SUM(IF(tri_id = 1, mcot_alcanzado, NULL)) AS  mem_alcTrimestre1,
            SUM(IF(tri_id = 2, mcot_alcanzado, NULL)) AS mem_alcTrimestre2,
            SUM(IF(tri_id = 3, mcot_alcanzado, NULL)) AS mem_alcTrimestre3,
            SUM(IF(tri_id = 4, mcot_alcanzado, NULL)) AS mem_alcTrimestre4,
            "S" AS mem_tipoCalendario
            FROM cat_MetaComponente
            INNER JOIN cat_MetaComponenteTrimestre USING(epfi_id,gpo_id,ur_id,prgs_id,sprs_id)
            INNER JOIN enc_ProductoFinalIndicador USING(epfi_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_TipoValor USING(tiv_id)
            WHERE epfi_id=:eri_id and gpo_id =:gpo_id AND ur_id=:ur_id
            GROUP BY gpo_id,ur_id,prgs_id,sprs_id,epfi_id;
        ');
        $params = [
            ':eri_id' => $rowind['eri_id'],
            ':gpo_id' => $rowind['gpo_id'],
            ':ur_id'  => $rowind['ur_id'],
        ];
        $conexionmeta->conectar();
        $conexionmeta->consultar($params);
        $numMetas = 0;
        $primer = true;
        $trim1alc = 0;
        $trim2alc = 0;
        $trim3alc = 0;
        $trim4alc = 0;

        while ($rowmeta = $conexionmeta->extraer()) {

            $numMetas++;
            if ($numMetas == 3) {
                $pdf->ln(1.5);
                $numMetas = 1;
            }

            $pdf->CheckPageBreak(40);
            $pdf->ln(1.5);
            $pdf->setBorders(1);
            $pdf->setAltoLinea(2.5);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->SetWidths([255]);
            $pdf->SetAligns(['C']);
            $pdf->SetRelleno('si');
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row(['DATOS DE LA META']);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetBorders(1);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([42.5, 212.5]);
            $pdf->SetAligns(['R', 'L']);
            $pdf->Row([utf8Decode('Definición de la variable Meta:'), $rowmeta['epfi_id'] . ' - ' . utf8Decode($rowmeta['mco_descripcion'])]);
            $pdf->Row([utf8Decode('Unidad de medida:'), $rowind['umi_mir'] . ' - ' . utf8Decode($rowind['umiNombre_mir'])]);
            $pdf->SetWidths([255]);
            $pdf->SetAligns(['C']);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row([utf8Decode('LÍNEA BASE DE LA META DEL INDICADOR')]);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([20, 107.5, 20, 107.5]);
            $pdf->SetAligns(['C', 'C', 'C', 'C']);
            $pdf->Row([utf8Decode('AÑO'), $rowind['mem_anio'], 'VALOR', $rowind['mem_valorDeseado1']]);

            // detalle de meta
            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([15, 60, 60, 60, 60]);
            $pdf->SetAligns(['C', 'C', 'C', 'C', 'C']);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);
            if ($rowmeta['mem_tipoCalendario'] == 'C') {
                $sumoriginalanual = $rowmeta['mem_trimestre2'];
                $summodificadoanual = $rowmeta['mem_modTrimestre2'];
                $sumAvanceFisicoanual = $rowmeta['mem_alcTrimestre2'];
            } else {
                $sumoriginalanual = $rowmeta['mco_original'];
                $summodificadoanual = $rowmeta['mco_modificado'];
                $sumAvanceFisicoanual = $rowmeta['mco_alcanzado'];
            }
            $sumametasanual = $summodificadoanual;

            if ($sumametasanual == 0) {
                $unoporcavreportado = 0;
                $dosporcavreportado = 0;
                $tresporcavreportado = 0;
                $cuatroporcavreportado = 0;
                $porcentajeacumulado = 0;
            } else {
                $unoporcavreportado = ($rowmeta['mem_alcTrimestre1'] / $sumametasanual) * 100;
                $dosporcavreportado = ($rowmeta['mem_alcTrimestre2'] / $sumametasanual) * 100;
                $tresporcavreportado = ($rowmeta['mem_alcTrimestre3'] / $sumametasanual) * 100;
                $cuatroporcavreportado = ($rowmeta['mem_alcTrimestre4'] / $sumametasanual) * 100;

                $porcentajeacumulado = ($rowmeta['mem_tipoCalendario'] == 'C') ? ($rowmeta['mem_alcTrimestre1'] * 25) / $sumametasanual : ($rowmeta['mem_alcTrimestre1'] / $sumametasanual) * 100 +
                    ($rowmeta['mem_alcTrimestre2'] / $sumametasanual) * 100 +
                    ($rowmeta['mem_alcTrimestre3'] / $sumametasanual) * 100 +
                    ($rowmeta['mem_alcTrimestre4'] / $sumametasanual) * 100;
                $porcentajeacumulado = $porcentajeacumulado * 100;
            }

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
            $pdf->SetWidths([255]);
            $pdf->SetAligns(['C']);
            $pdf->SetFillColor(199, 209, 219);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row(['AVANCE SEMESTRAL']);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([63.75, 63.75, 63.75, 63.75]);
            $pdf->SetAligns(['C', 'C', 'C', 'C']);
            $pdf->SetBorders(1);
            $edoborde = 1;
            $pdf->Row(['', '1er.SEMESTRE', '2o.SEMESTRE', 'TOTAL AL SEMESTRE']);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();

            $pdf->SetFont('Arial', '', 5);

            $trim1ori = $rowmeta['mem_trimestre2'];
            $trim1mod = $rowmeta['mem_modTrimestre2'];
            $trim2ori = $rowmeta['mem_trimestre4'];
            $trim2mod = $rowmeta['mem_modTrimestre4'];
            $alcSem = $trim = $porcalcS1 = $porcalcS2 = '';

            switch ($_POST['capturandoTri']) {
                case 1:
                    $trim1alc = $rowmeta['mem_alcTrimestre2'];
                    $sumaalcanzados = $trim1alc;
                    $porcalcS1 = $rowmeta['mem_modTrimestre2'] != 0 ? ($rowmeta['mem_alcTrimestre2'] / $rowmeta['mem_modTrimestre2']) * 100 : 0;
                    $porcalcS2 = null;
                    $trim = 2;
                    $alcSem = $porcalcS1;
                    break;
                case 2:
                    $trim1alc = $rowmeta['mem_alcTrimestre2'];
                    $trim2alc = $rowmeta['mem_alcTrimestre4'];
                    $sumaalcanzados = $trim1alc + $trim2alc;
                    $porcalcS1 = $rowmeta['mem_modTrimestre2'] != 0 ? ($rowmeta['mem_alcTrimestre2'] / $rowmeta['mem_modTrimestre2']) * 100 : 0;
                    $porcalcS2 = $rowmeta['mem_modTrimestre4'] != 0 ? ($rowmeta['mem_alcTrimestre4'] / $rowmeta['mem_modTrimestre4']) * 100 : 0;
                    $alcSem = $porcalcS2;
                    $trim = 4;
                    break;
            }

            if ($sumametasanual == 0) {
                $porcentajeanual = 0;
                $porcentajeanualac = 0;
            } else {
                $porcentajeanualac = ($sumaalcanzados / $sumametasanual) * 100;
                $porcentajeanual = ($rowmeta['mem_tipoCalendario'] == 'C') ? (($rowmeta['mem_alcTrimestre2'] * 25) / $sumametasanual) + (($rowmeta['mem_alcTrimestre4'] * 25) / $sumametasanual) : ($rowmeta['mem_alcTrimestre' . $trim . ''] / $sumametasanual) * 100;
            }

            if ($rowmeta['tiv_id'] == 1 && ($_POST['capturandoTri'] == 1 || $_POST['capturandoTri'] == 2)) {
                $totalsemestreprog = $trim1ori + $trim2ori;
                $totalsemestreact = $trim1mod + ($trim2mod != 0 ? $trim2mod : $trim2ori);
                $totalsemestrealc = $trim1alc + $trim2alc;
            } elseif ($rowmeta['tiv_id'] == 2 && $_POST['capturandoTri'] == 1) {
                $totalsemestreprog = $trim1ori;
                $totalsemestreact = $trim1mod;
                $totalsemestrealc = $trim1alc;
            } elseif ($rowmeta['tiv_id'] == 2 && $_POST['capturandoTri'] == 2) {
                $totalsemestreprog = $trim2ori;
                $totalsemestreact = $trim2mod != 0 ? $trim2mod : $trim2ori;
                $totalsemestrealc = $trim2alc;
            }

            $porcentaje1sem = $trim1mod + (is_null($trim2mod) ? $trim2ori : $trim2mod);
            $porcentaje2sem = $trim1alc + $trim2alc;

            if ($porcentaje1sem != 0 && $porcentaje2sem != 0) {
                $porcentajesanual = ($porcentaje2sem / $porcentaje1sem) * 100;
            } else {
                $porcentajesanual = '0.00';
            }

            $semestre = 'S1 + S2';

            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([63.75, 63.75, 63.75, 31, 32.75]);
            $pdf->SetAligns(['L', 'L', 'L', 'L']);
            $pdf->SetBorders(1);
            if ($rowmeta['tiv_id'] == 1) {
                # code...
                $pdf->Row(['PROGRAMADA', $trim1ori, $trim2ori, $totalsemestreprog, $semestre]);
                $pdf->Row([is_null($trim1mod) ? '' : utf8Decode('PROGRAMACIÓN ACTUALIZADA'), $trim1mod, is_null($trim2mod) ? $trim2ori : $trim2mod, $totalsemestreact, $semestre]);
                $pdf->Row(['ALCANZADA', $trim1alc, $trim2alc, $totalsemestrealc, $semestre]);
            } elseif ($rowmeta['tiv_id'] == 2) {
                $pdf->SetWidths([63.75, 63.75, 63.75, 63.75]);
                $pdf->Row(['PROGRAMADA', $trim1ori, $trim2ori, $totalsemestreprog]);
                $pdf->Row([is_null($trim1mod) ? '' : utf8Decode('PROGRAMACIÓN ACTUALIZADA'), $trim1mod, is_null($trim2mod) ? $trim2ori : $trim2mod, $totalsemestreact]);
                $pdf->Row(['ALCANZADA', $trim1alc, $trim2alc, $totalsemestrealc]);
            }
            $pdf->SetWidths([63.75, 63.75, 63.75, 31, 32.75]);
            $pdf->Row(['PORCENTAJE ALCANZADO EN EL PERIODO', $porcalcS1, is_null($porcalcS2) ? '' : $porcalcS2, $porcentajesanual, 'Porcentaje alcanzado anual']);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);

            $pdf->ln(1);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([180, 52, 23]);
            $pdf->SetAligns(['C', 'R', 'C']);
            $pdf->SetBorders(0);
            $edoborde = 1;
            $pdf->Row(['', 'PORCENTAJE DE CUMPLIMIENTO EN EL SEMESTRE:', $alcSem . ' %']);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->ln(0);

            // fin de detalle de meta
            $primer = false;
        }
        if ($row['emi_id'] != 9) {
            $ix = $pdf->GetX();
            $iy = $pdf->GetY();
            $pdf->SetFont('Arial', 'B', 45);
            $pdf->SetTextColor(248, 000, 000);
            $pdf->RotatedText($ix, $iy, utf8Decode('MIR DEL PROGRAMA NO VALIDADA'), 30);
            $pdf->SetTextColor(0, 0, 0);
        }
        // fin de metas de una accion
        $conexionmeta->desconectar();
    } // fin $conexionIndicador
} //fin datos de la actividad
$conexion->desconectar();
$pdf->Output();
