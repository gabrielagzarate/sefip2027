<?php

session_start();
set_time_limit(0);
//librerias

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

function condicionUr($prefijo = '')
{
    if ($_SESSION[_S]['nvj_id'] > 2) {
        $gpoId = $_POST['gpo_id'];
        $urId = $_POST['ur_id'];
    } else {
        $gpoId = $_SESSION[_S]['gpo_id'];
        $urId = $_SESSION[_S]['ur_id'];
    }

    return $prefijo . 'gpo_id = ' . $gpoId . ' AND ' . $prefijo . 'ur_id = ' . $urId;
}

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class ReporteTabla extends PDF
{
    public $nombreUr;
    public $ultBorde = 0;
    public $widths = '';
    public $ur;

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer()
    {
        $this->SetY(-20);
        $datos = $this->getDatos();

        if (empty($datos)) {
            # code...
            $datos['ur_nombreAdministrativo'] = ' ';
            $datos['ur_nombreTitular'] = ' ';
            $datos['ur_cargoAdministrativo'] = ' ';
            $datos['ur_cargoTitular'] = ' ';
        }

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
    } //fin de la funcion footer

    public function Header()
    {
        //Encabezado del documento
        $ur = condicionUr();

        $datos = $this->getDatos();

        if (empty($datos)) {
            $sql = '
                SELECT gpo_id, ur_id, ur_nombre
                FROM cat_UnidadResponsable ur
                INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                WHERE ' . $ur . ';
            ';
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql($sql);
            $conexion->consultar();
            $urRes = $conexion->extraer();
            $datos['ur_clave'] = $urRes['gpo_id'] . $urRes['ur_id'];
            $datos['ur_nombre'] = $urRes['ur_nombre'];
        }

        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(4);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->Image(__DIR__ . '/../../../../img/escudo2.jpg', $ix + 144, $iy + 6, '100', '29', 'JPG', '');

        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $iy + 12);
        $ytmp = $this->GetY() + 2;
        $this->Cell(264, 5, utf8Decode('REPORTE DE AVANCE DE GESTION DE FIN'), 0, 0, 'L');

        $this->SetXY($ix, $ytmp + 3);
        $ytmp = $this->GetY();
        $this->Cell(264, 5, 'UR ' . $datos['ur_clave'] . ' - ' . utf8Decode($datos['ur_nombre']), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY($ix, $ytmp + 5);

        if ($_POST['capturandoTri'] == 0) {
            $_POST['capturandoTri'] = $_POST['capturandoTri2'];
        }

        $this->Cell(264, 5, 'ANUAL', 0, 0, 'L');

        $this->SetFont('Arial', 'B', 7);

        $this->SetBorders(0);

        $this->SetBorders(0);
        $this->SetFont('Arial', 'B', 10);
        $this->ln(13);
        $this->SetBorders($this->ultBorde);
    }
}

$ur = condicionUr();
$ur2 = condicionUr('ur.');

$sql = '
    SELECT p.*,CONCAT(ue.ue_id, "-", ue_nombre) AS ues, ur.gpo_id,ur.ur_id, ur_nombreAdministrativo, ur_nombreTitular, ur_cargoAdministrativo,
        ur_cargoTitular, ur_clave, ur_nombre, prg_id, prg_nombre,mir_resumenNarrativo fin,
        vpm_resumenNarrativo AS proposito, pdf_id, pdf_nombre AS componente, eje_nombre,tem_nombre, obj_nombre,
        ur_objetivoGeneral,  emi_id,eje_id,tem_id,obj_id,vpm_id, est_nombre
    FROM  cat_Programa prg
    INNER JOIN cat_Subprograma sprg USING(gpo_id,ur_id,prg_id)
    INNER JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN (
        SELECT  prgs_id AS prg_id, eje_nombre,tem_nombre, obj_nombre,eje_id,tem_id,obj_id,est_id, est_nombre
        FROM vin_Programa
        INNER JOIN enc_PedObjetivo USING(epo_id)
        INNER JOIN cat_Eje USING(eje_id)
        INNER JOIN cat_Tema tem USING(eje_id,tem_id)
        INNER JOIN cat_Objetivo USING(eje_id,tem_id,obj_id)
        INNER JOIN cat_Estrategia USING(eje_id,obj_id,tem_id)
    ) AS ped USING(prg_id)
    INNER JOIN vin_ProductoIntermedioIndicador pfpi USING(pdf_id)
    INNER JOIN (SELECT vpm_id, prgs_id AS prg_id, mir_id, vpm_resumenNarrativo,emi_id,mir_resumenNarrativo,
        gpo_id,ur_id
        FROM vin_ProgramasMir
        INNER JOIN cat_Mir USING (mir_id)
    ) P USING (prg_id,gpo_id,ur_id)
    INNER JOIN cat_ProductoFinal USING(pdf_id)
    LEFT JOIN (
        SELECT gpo_id , ur_id , MAX(ue_id)ue_id , prg_id ,
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
    GROUP BY gpo_id,ur_id,prg_id
    ) p USING (gpo_id,ur_id,prg_id)
    INNER JOIN cat_UnidadEjecutora ue USING (gpo_id , ur_id, ue_id)
    WHERE ' . $ur2 . ' AND emi_id=9
    GROUP BY ur.gpo_id, ur.ur_id, vpm_id
    ORDER BY prg.prg_id , vpm_id;
';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();

$pdf = new ReporteTabla('L', 'mm', 'letter');
$pdf->SetProtection(['print', 'modify'], '', PDF_PASSWORD_OWNER_POA);
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
    $pdf->Row([utf8Decode('ALINEACIÓN PED:'), 'EJE: ', utf8Decode($row['eje_nombre']), 'PROGRAMA: ', str_pad($row['prg_id'], 2, '0', STR_PAD_LEFT) . ' - ' . utf8Decode($row['prg_nombre'])]);
    $pdf->Row(['', 'OBJETIVO: ', utf8Decode($row['obj_nombre']), 'FIN: ', utf8Decode($fin)]);
    $pdf->Row([utf8Decode('ALINEACIÓN PES:'), 'OBJETIVO: ', utf8Decode($row['obj_nombre']), '', '']);
    $pdf->Row(['', utf8Decode('ESTRATÉGIA: '), utf8Decode($row['est_nombre']), '', '']);
    $pdf->Row(['', utf8Decode('RESULTADO: '), utf8Decode($row['proposito']), '', '']);
    $pdf->SetWidths([30, 108, 18, 99]);
    $pdf->SetAligns(['L', 'L', 'L', 'L']);
    $pdf->Row(['OBJETIVO INSTITUCIONAL: ', utf8Decode($row['ur_objetivoGeneral']), 'UE: ', utf8Decode($row['ues'])]);
    $pdf->ln(1);

    $pdf->setBorders(1);
    $pdf->setAltoLinea(2.5);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetWidths([255]);
    $pdf->SetAligns(['C']);
    $pdf->SetRelleno('si');
    $pdf->SetFont('Arial', 'B', 6);
    $pdf->Row([utf8Decode('PRESUPUESTO DE FIN')]);

    $pdf->SetFont('Arial', 'B', 5.5);
    $pdf->SetBorders(1);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->SetWidths([35.5, 35.5, 35.5, 35.5, 32, 35.5, 45.5]);
    $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
    $pdf->Row(['Aprobado', utf8Decode('Ampliación'), utf8Decode('Reducción'), utf8Decode('Modificado'), APP_EJERCICIO, utf8Decode('Acumulado'), utf8Decode('% de Devengado Acum. Vs. Modificado')]);
    $pdf->SetFillColor(255, 255, 255);


    //para pintar el trimestre del devengado correspondiente
    $DevengadoT1 = $DevengadoT2 = $DevengadoT3 = $DevengadoT4 = 0;
    $DevengadoT2 = $row['devTrimestre1'] + $row['devTrimestre2'] + $row['devTrimestre3'] + $row['devTrimestre4'];

    $pdf->Row([$row['aprobado'], $row['ampliaciones'], $row['reducciones'], $row['modificado'], $DevengadoT2, $row['devengado'], ($row['modificado'] != 0 ? ($row['devTrimestre' . $_POST['capturandoTri'] . ''] / $row['modificado'] * 100) : 0)]);

    $conexionIndicador = new AccesoDatos();
    $conexionIndicador->setSql('
        SELECT f.pmi_id,ind_id,ind_nombre,ind_definicion,ind_nombreMIR, ind_definicionMIR,
            ind_objetivo,ind_formulaCalculo,tpi_id,tpi_nombre,u.umi_id as umi_id,u.umi_nombre as umi_nombreI,
            umir.umi_id as umi_mir, umir.umi_nombre as umiNombre_mir,dim_id,dim_nombre,frm_id,frm_nombre,ses_id,
            tiv_id,tiv_nombre,ses_nombre,nim_id,mvpm_nombre,spm_nombre, gpo_id,ur_id, prgs_id,
            mtf_anio,mtf_valor,mtf_original as mem_valorDeseado1
        FROM vin_ProgramaMirIndicador
        JOIN cat_Indicador i USING (nim_id , ind_id)
        JOIN cat_Dimension USING (dim_id)
        JOIN cat_FrecuenciaMedicion USING (frm_id)
        JOIN cat_TipoIndicador USING (tpi_id)
        JOIN cat_UnidadMedidaIndicador u USING (umi_id)
        JOIN cat_UnidadMedidaIndicador umir ON umir.umi_id = i.umi_id
        JOIN cat_TipoValor USING (tiv_id)
        JOIN cat_SentidoEsperado USING (ses_id)
        left join cat_MetaFin f USING (pmi_id)
        left join vin_ProgramasMir USING (vpm_id)
        left JOIN cat_MedioVerificacionProgramaMir mp USING (nim_id,vpm_id)
        left JOIN cat_SupuestoProgramaMir USING (vpm_id,nim_id)
        WHERE nim_id = 1 AND vpm_id = ?
        AND gpo_id = ? AND ur_id = ? AND prgs_id= ?
        GROUP BY pmi_id
    ');
    $params = [
        $row['vpm_id'],
        $row['gpo_id'],
        $row['ur_id'],
        $row['prg_id'],
    ];

    $conexionIndicador->conectar();
    $conexionIndicador->consultar($params);
    $numMetas = 0;
    $primer = true;
    while ($rowind = $conexionIndicador->extraer()) {
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
        $pdf->Row([utf8Decode($rowind['tpi_nombre']), utf8Decode($rowind['dim_nombre']), utf8Decode($rowind['frm_nombre']), utf8Decode($rowind['umiNombre_mir']), 'ACUMULADO', utf8Decode($rowind['ses_nombre'])]);

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
        $pdf->Row([utf8Decode($rowind['mvpm_nombre']), utf8Decode($rowind['spm_nombre'])]);
        $pdf->SetBorders(0);
        $pdf->ultBorde = $pdf->GetBorders();

        $conexionmeta = new AccesoDatos();
        $conexionmeta->setSql('
            SELECT
                mtf_id, mtf_descripcion,
                mtf_anio,mtf_valor, mtf_original as mem_valorDeseado1,
                mtf_original,
                mtf_alcanzado,
                mtf_modificado,
                mtf_tipoCalendario,
                ((mtf_alcanzado*100)/mtf_modificado) ava4,
                mtft_modificado  AS modificado, mtft_modificado
            FROM cat_MetaFin
            INNER JOIN cat_MetaFinTrimestre USING(pmi_id,mtf_id)
            INNER JOIN vin_ProgramaMirIndicador USING(pmi_id)
            INNER JOIN vin_UrProgramaMir USING(vpm_id)
            INNER JOIN cat_Indicador USING(nim_id,ind_id)
            WHERE ind_id = ? AND pmi_id= ?
            and gpo_id = ? AND ur_id= ?
        ');
        $params = [
            $rowind['ind_id'],
            $rowind['pmi_id'],
            $rowind['gpo_id'],
            $rowind['ur_id'],
        ];
        $conexionmeta->conectar();
        $conexionmeta->consultar($params);
        $numMetas = 0;
        $primer = true;

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
            $pdf->Row([utf8Decode('Definición de la variable Meta:'), $rowmeta['mtf_id'] . ' - ' . utf8Decode($rowmeta['mtf_descripcion'])]);
            $pdf->Row([utf8Decode('Unidad de medida:'), $rowind['umi_id'] . ' - ' . utf8Decode($rowind['umi_nombreI'])]);
            $pdf->SetWidths([255]);
            $pdf->SetAligns(['C']);
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->Row([utf8Decode('LÍNEA BASE DE LA META DEL INDICADOR')]);
            $pdf->SetFont('Arial', 'B', 5.5);
            $pdf->SetWidths([20, 107.5, 20, 107.5]);
            $pdf->SetAligns(['C', 'C', 'C', 'C']);
            $pdf->Row([utf8Decode('AÑO'), $rowind['mtf_anio'], 'VALOR', $rowind['mem_valorDeseado1']]);

            // detalle de meta
            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([15, 60, 60, 60, 60]);
            $pdf->SetAligns(['C', 'C', 'C', 'C', 'C']);

            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetFont('Arial', '', 5);

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
            $pdf->Row(['AVANCE']);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([127.5, 127.5]);
            $pdf->SetAligns(['C', 'C']);
            $pdf->SetBorders(1);
            $edoborde = 1;
            $pdf->Row(['', APP_EJERCICIO]);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();

            $pdf->SetFont('Arial', '', 5);

            if ($rowmeta['mtf_alcanzado'] != 0 && $rowmeta['mtf_modificado'] != 0) {
                $porcentajeAlcanzado = (($rowmeta['mtf_alcanzado'] / $rowmeta['mtft_modificado']) * 100);
            } else {
                $porcentajeAlcanzado = '0.00';
            }

            $pdf->SetBorders(1);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->SetWidths([127.5, 127.5]);
            $pdf->SetAligns(['L', 'L']);
            $pdf->SetBorders(1);

            $pdf->Row(['PROGRAMADA', $rowmeta['mtf_original']]);
            $pdf->Row(['ACTUALIZADA', $rowmeta['mtft_modificado']]);
            $pdf->Row(['ALCANZADA', $rowmeta['mtf_alcanzado']]);

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
            $pdf->Row(['', 'PORCENTAJE ALCANZADO:', $porcentajeAlcanzado . ' %']);
            $pdf->SetBorders(0);
            $pdf->ultBorde = $pdf->GetBorders();
            $pdf->ln(80);

            // fin de detalle de meta
            $primer = false;
        }
        // fin de metas de una accion
        $conexionmeta->desconectar();
    } // fin $conexionIndicador


} //fin datos de la actividad

$conexion->desconectar();
$pdf->Output();
