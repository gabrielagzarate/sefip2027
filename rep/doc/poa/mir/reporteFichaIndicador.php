<?php
/**
 * ReporteFichaIndicadorr
 *
 * Permite generar Reporte de la Ficha del indicadores por programa
 * incluyendo los 3 primeros niveles de la MIR en Formato PDF
 *
 * PHP version 7
 *
 * @category  Consulta
 * @package   Package
 * @author    Yuridia Esmeralda Santiago Martínez <yuridia.santiago@finanzasoaxaca.gob.mx>
 * @copyright 2020 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 */
session_start();
ini_set('memory_limit', '20M');

set_time_limit(0);

//  LIBRERIAS
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/helpers.php';
configurarErroresPhp();

class reportetabla extends PDF
{

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer($lastPage = true)
    {
        $this->SetY(-15);
        $this->Cell(260, 5, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    public function Header($lastPage = true) //Encabezado del documento

    {
        $this->SetMargins(10, 40, 10);
        $this->SetAutoPageBreak(true,25); 
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->setRouteImg2(" ");
        $this->Image($this->getRouteImg(), $ix + 155, $iy - 20, '110', '15', 'JPG', '');

        $this->SetFont('calibrib', 'B', 11);
        $this->SetXY($ix + 4, $iy);
        $ytmp = $this->GetY();

        $this->Cell(200, 5, APP_EJERCICIO .' FICHA DEL INDICADOR', 0, 1, 'C');
        $this->SetXY($ix + 4, $ytmp + 13);
        $ytmp = $this->GetY();

        $this->SetBorders(0);
        $this->ln(0);
        $this->SetFont('Calibri', 'B', 11);
        $tmp = $this->GetBorders(0);
        $ytmp = $this->GetY();

        $this->setDrawColor(200, 200, 200);

        $this->ln(0);
    }
}

//CONSULTA PARA INDICADORES NIVEL FIN
$sql = "SELECT prgs_id, prgs_nombre,ind_id, ind_nombreMIR,ind_definicion,ind_formulaCalculo,
        ses_id,	ses_nombre, tiv_id, tiv_nombre, dim_id, dim_nombre, tpi_id, tpi_nombre,
        umi_id,umi_nombre, frm_id, frm_nombre, ind_nombre,mtf_anio,mtf_valor,mtf_original,
        ind_definicionMIR, pmi_id, vpm_id
    FROM cat_Programas p
    INNER JOIN vin_ProgramasMir USING(prgs_id)
    INNER JOIN (SELECT vpm_id, ind_id, ind_nombreMIR,ind_definicion,ind_formulaCalculo,ses_id,
            ses_nombre, tiv_id, tiv_nombre, dim_id, dim_nombre, tpi_id, tpi_nombre,
            umi_id,umi_nombre, frm_id, frm_nombre,ind_nombre,mtf_anio,mtf_valor,mtf_original,pmi_id,
            ind_definicionMIR
        FROM vin_ProgramaMirIndicador vin
        INNER JOIN cat_Indicador USING(nim_id,ind_id)
        INNER JOIN cat_SentidoEsperado USING (ses_id)
        INNER JOIN cat_TipoValor USING (tiv_id)
        INNER JOIN cat_Dimension USING (dim_id)
        INNER JOIN cat_TipoIndicador USING (tpi_id)
        INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
        INNER JOIN cat_FrecuenciaMedicion USING (frm_id)
        LEFT JOIN cat_MetaFin f USING(pmi_id)
        WHERE vin.sts_id=1 AND nim_id=1
    ) vin USING(vpm_id)
    WHERE prgs_id= ? AND mtf_original >= 0
    ORDER BY ind_id ASC
    ;";
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar([$_SESSION[_S]['clasAdm-prg']]);
$conexion->desconectar();

$pdf = new reportetabla();
$pdf->SetProtection(array("print"), "", PDF_PASSWORD_OWNER_POA);
/* Se agrega la paginacion */
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->AddFont('calibrib', 'B', 'calibrib.php');
$pdf->AddFont('Calibri', '', 'Calibri.php');

$pdf->SetMargins(10, 40 , 10);
//Nueva pagina
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();
while ($row = $conexion->extraer()) {
    $pdf->setDatos($row);
    $datos = $pdf->getDatos();
    /* Array de Fuentes  */
    $f = new stdClass();
    $f->font = 'calibrib';
    $f->style = 'B';
    $f->size = '11';
    $fuentes = array($f);
    $f = new stdClass();
    $f->font = 'Calibri';
    $f->style = '';
    $f->size = '11';
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'calibrib';
    $f->style = 'B';
    $f->size = '11';
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'calibrib';
    $f->style = 'B';
    $f->size = '11';
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'Calibri';
    $f->style = '';
    $f->size = '11';
    array_push($fuentes, $f);

    $f = new stdClass();
    $f->font = 'Calibri';
    $f->style = '';
    $f->size = '11';
    array_push($fuentes, $f);

    /* Contenido del reporte */
    //DATOS DEL PROGRAMA
    $pdf->SetRelleno("si");
    $pdf->CheckPageBreak(10);
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setAltoLinea(3);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('calibrib', 'B', 11);

    $mediosFin = '';
    $supMeF = new AccesoDatos();
        $supMeF->setSql('
            SELECT *
            FROM cat_MedioVerificacionProgramaMir m
            WHERE vpm_id=? AND nim_id=1 AND pmi_id = ? AND mvpm_trimestreCierre = 0;
        ');
        $parametros = [
            $row['vpm_id'],
            $row['pmi_id']
        ];
        $supMeF->conectar();
        $supMeF->consultar($parametros);
        $supMeF->desconectar();

        while ($MediosF = $supMeF->extraer()) {
            $mediosFin .= $MediosF['mvpm_nombre']. "\n"."\n";
        }

    // NIVEL FIN
    $pdf->SetWidths(array(50, 10, 198));
    $pdf->Cell(0, 0, '', 0, 1, 1, 0);
    $pdf->SetArrayBorders(array('TLB', 'TB', 'TRB'));
    $pdf->Row(array('NOMBRE DEL PROGRAMA:', str_pad($datos['prgs_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datos['prgs_nombre']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NOMBRE DEL INDICADOR:', str_pad($datos['ind_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datos['ind_nombreMIR']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NIVEL DEL INDICADOR:', "", strtoupper(utf8Decode("FIN"))));
    $pdf->Cell(0, 6, utf8Decode(''), 0, 1, 1, 0);

    // DATOS DEL INDICADOR
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setAltoLinea(3);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
    $pdf->SetWidths(array(126, 6, 126));
    $pdf->Row(array(utf8Decode('DEFINICIÓN DEL INDICADOR:'), '', utf8Decode('DIMENSIÓN A MEDIR:')));
    $pdf->SetArrayBorders(array('LR', '', 'LR'));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ind_definicionMIR']), '', utf8Decode($datos['dim_id'] . "-" . $datos['dim_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('MÉTODO DE CÁLCULO DEL INDICADOR:'), '', utf8Decode('TIPO DE INDICADOR PARA RESULTADOS:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ind_formulaCalculo']), '', utf8Decode($datos['tpi_id'] . "-" . $datos['tpi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('SENTIDO DEL INDICADOR:'), '', utf8Decode('UNIDAD DE MEDIDA:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ses_id'] . "-" . $datos['ses_nombre']), '', utf8Decode($datos['umi_id'] . "-" . $datos['umi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('TIPO DE VALOR DE LA META:'), '', utf8Decode('FRECUENCIA DE MEDICIÓN:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['tiv_id'] . "-" . $datos['tiv_nombre']), '', utf8Decode($datos['frm_id'] . "-" . $datos['frm_nombre'])));
    $pdf->SetArrayBorders(array('T', '', 'T'));
    $pdf->Row(array("", "", '', "", ""));

    //DATOS DE LA META
    $pdf->CheckPageBreak(10);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(126, 8, utf8Decode('LÍNEA BASE'), 'LRT', 0, 'C', 1);
    $pdf->Cell(6, 8, utf8Decode(''), 0, 0, 1, 0);
    $pdf->Cell(126, 8, utf8Decode('META'), 'LRT', 1, 'C', 1);
    $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
    $pdf->SetWidths(array(15, 111, 6, 15, 111));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array("VALOR: ", $datos['mtf_valor'], '', 'VALOR: ', $datos['mtf_original']));
    $pdf->SetArrayBorders(array('LB', 'RB', '', 'LB', 'RB'));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array(utf8Decode('AÑO:'), $datos['mtf_anio'], '', utf8Decode('AÑO:'), APP_EJERCICIO));
    $pdf->Cell(0, 5, utf8Decode(''), 0, 1, 1, 0);

    //MEDIOS DE VERIFICACIÓN
    $pdf->CheckPageBreak(10);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(258, 7, utf8Decode('MEDIO DE VERIFICACIÓN DEL INDICADOR:'), 'RLT', 1, 1, 0);
    $pdf->SetFont('Calibri', '', 11);
    $pdf->MultiCell(258, 4, utf8Decode(mb_strtoupper(str_replace('', '\n', $mediosFin))), 'RL', 0, 0);
    $pdf->Cell(258, 5, utf8Decode(''), "T", 1, 1, 0);
    $pdf->AddPage();

    //CONSULTAR UR RESPONSABLE Y PARTICIPANTES
    $sqlUrs = "SELECT prgs_id,vpm.gpo_id,vpm.ur_id,u.ur_nombre nombre, GROUP_CONCAT(UR,' ',urs.ur_nombre,'\n' SEPARATOR '|') participantes
            FROM vin_ProgramasMir vpm
            INNER JOIN (SELECT vpm_id, prgs_id, CONCAT(gpo_id,ur_id) ur, ur_nombre
                        FROM vin_UrProgramaMir
                        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                        )urs USING (prgs_id, vpm_id)
                INNER JOIN cat_UnidadResponsable u USING (gpo_id,ur_id)
                WHERE prgs_id= ?
                GROUP BY vpm.gpo_id, vpm.ur_id;";
}
if ($row != '') {
    $conexion->conectar();
    $conexion->setSql($sqlUrs);
    $conexion->consultar([$_SESSION[_S]['clasAdm-prg']]);
    $conexion->desconectar();
    while ($row = $conexion->extraer()) {
        $pdf->SetFont('calibrib', 'B', 11);
        $pdf->Cell(258, 8, utf8Decode('UR RESPONSABLE:'), 'RLT', 1, 1, 0);
        $pdf->SetFont('Calibri', '', 11);
        $pdf->Cell(258, 4, utf8Decode($row['gpo_id'] . $row['ur_id'] . " " . $row['nombre']), 'RL', 1, 1, 0);
        $pdf->Cell(258, 3, utf8Decode(''), 'LR', 1, 1, 0);
        $pdf->SetFont('calibrib', 'B', 11);
        $pdf->Cell(258, 8, utf8Decode('UR´S PARTICIPANTES:'), 'RL', 1, 1, 0);
        $pdf->SetFont('Calibri', '', 11);
        $pdf->MultiCell(258, 3, utf8Decode(str_replace('|', "\n", $row['participantes'])), 'RLB', 1, 0);
        $pdf->Cell(258, 10, utf8Decode(''), 0, 1, 1, 0);
    }
}

/* Array de Fuentes  */
$f = new stdClass();
$f->font = 'calibrib';
$f->style = 'B';
$f->size = '11';
$fuentes = array($f);
$f = new stdClass();
$f->font = 'Calibri';
$f->style = '';
$f->size = '11';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'calibrib';
$f->style = 'B';
$f->size = '11';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'calibrib';
$f->style = 'B';
$f->size = '11';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'Calibri';
$f->style = '';
$f->size = '11';
array_push($fuentes, $f);

$f = new stdClass();
$f->font = 'Calibri';
$f->style = '';
$f->size = '11';
array_push($fuentes, $f);

/* INICIA NIVEL PROPOSITO */

//CONSULTA PARA INDICADORES NIVEL PROPOSITO
$sqlProposito = "
    SELECT v.prgs_id, vpm_id, prgs_nombre,ind_id, ind_nombreMIR,ind_definicion,ind_formulaCalculo,
    ses_id,	ses_nombre, tiv_id, tiv_nombre, dim_id, dim_nombre, tpi_id, tpi_nombre,
    umi_id,umi_nombre, frm_id, frm_nombre, ind_nombre,
    mpr_lb AS mem_anio,mpr_original AS mem_original,mpr_lbIndicador as mlb_valorIndicador,
        mpr_anteriorValor as mlb_valorT0,mpr_anterior AS mlb_anioT0,mpr_anteriorIndicador AS mvp_valorIndicador,
        mpr_lbValor AS mem_valor, ind_definicionMIR, mpr_numerador, mprt_denominador, vin.vpm_id, vin.pmi_id
    FROM cat_Programas 
    INNER JOIN vin_ProgramaMirIndicador vin
    INNER JOIN vin_ProgramasMir v USING(vpm_id)
    INNER JOIN cat_Indicador USING(nim_id,ind_id)
    INNER JOIN cat_SentidoEsperado USING (ses_id)
    INNER JOIN cat_TipoValor USING (tiv_id)
    INNER JOIN cat_Dimension USING (dim_id)
    INNER JOIN cat_TipoIndicador USING (tpi_id)
    INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
    INNER JOIN cat_FrecuenciaMedicion USING (frm_id)
    LEFT JOIN cat_MetaProposito p USING(pmi_id)
    LEFT JOIN cat_MetaPropositoTrimestre USING(pmi_id)
    WHERE v.prgs_id= ? AND vin.trim_0=1 AND nim_id=2 AND mpr_original >= 0
    GROUP BY ind_id
    ORDER BY ind_id ASC
;";

$conexion->conectar();
$conexion->setSql($sqlProposito);
$conexion->consultar([$_SESSION[_S]['clasAdm-prg']]);
$conexion->desconectar();
while ($datos = $conexion->extraer()) {
    //INDICADORES NIVEL PROPOSITO
    $pdf->SetRelleno("si");
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setAltoLinea(3);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->SetWidths(array(50, 10, 196));
    $pdf->SetArrayBorders(array('TLB', 'TB', 'TRB'));
    $pdf->Cell(0, 0, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NOMBRE DEL PROGRAMA:', str_pad($datos['prgs_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datos['prgs_nombre']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NOMBRE DEL INDICADOR:', str_pad($datos['ind_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datos['ind_nombreMIR']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NIVEL DEL INDICADOR:', "", strtoupper(utf8Decode("PROPÓSITO"))));
    $pdf->Cell(0, 6, utf8Decode(''), 0, 1, 1, 0);

    //DETALLE DEL INDICADOR
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setAltoLinea(3);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
    $pdf->SetWidths(array(125, 6, 125));
    $pdf->Row(array(utf8Decode('DEFINICIÓN DEL INDICADOR:'), '', utf8Decode('DIMENSIÓN A MEDIR:')));
    $pdf->SetArrayBorders(array('LR', '', 'LR'));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ind_definicionMIR']), '', utf8Decode($datos['dim_id'] . "-" . $datos['dim_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('MÉTODO DE CÁLCULO DEL INDICADOR:'), '', utf8Decode('TIPO DE INDICADOR PARA RESULTADOS:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ind_formulaCalculo']), '', utf8Decode($datos['tpi_id'] . "-" . $datos['tpi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('SENTIDO DEL INDICADOR:'), '', utf8Decode('UNIDAD DE MEDIDA:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ses_id'] . "-" . $datos['ses_nombre']), '', utf8Decode($datos['umi_id'] . "-" . $datos['umi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('TIPO DE VALOR DE LA META:'), '', utf8Decode('FRECUENCIA DE MEDICIÓN:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['tiv_id'] . "-" . $datos['tiv_nombre']), '', utf8Decode($datos['frm_id'] . "-" . $datos['frm_nombre'])));
    $pdf->SetArrayBorders(array('T', '', 'T'));
    $pdf->Row(array("", "", '', "", ""));
    $pdf->CheckPageBreak(10);

    $mediosVerPUR = '';
    $supMePro = new AccesoDatos();
        $supMePro->setSql('
            SELECT *
            FROM cat_MedioVerificacionProgramaMir m
            WHERE vpm_id=? AND nim_id=2 AND pmi_id = ? AND mvpm_trimestreCierre = 0;
        ');
        $parametros = [
            $datos['vpm_id'],
            $datos['pmi_id']
        ];
        $supMePro->conectar();
        $supMePro->consultar($parametros);
        $supMePro->desconectar();

        while ($MediosP = $supMePro->extraer()) {
            $mediosVerPUR .= $MediosP['mvpm_nombre']. "\n"."\n";
        }

    //El valor de la meta depende de la unidad de medida
    switch ($datos['umi_id']) {
        case 2:case 3:case 4: //PORCENTAJE Y RAZÓN
            $aniolb = $datos['mem_anio'];
            $valorlb = $datos['mlb_valorIndicador'];
            $valorMeta = $datos['mvp_valorIndicador'];
            break;
        case 5: // TASA
            $aniolb = $datos['mlb_anioT0'];
            $valorlb = $datos['mlb_valorIndicador'];
            $valorMeta = $datos['mvp_valorIndicador'];
            break;
        default:
            $aniolb = $datos['mem_anio'];
            $valorlb = $datos['mpr_numerador'];
            $valorMeta = $datos['mem_original'];
            break;
    }
    //DATOS DE LA META
    $pdf->CheckPageBreak(10);
    $pdf->setX(10);

    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(125, 8, utf8Decode('LÍNEA BASE'), 'LRT', 0, 'C', 1);
    $pdf->Cell(6, 8, utf8Decode(''), 0, 0, 1, 0);
    $pdf->Cell(125, 8, utf8Decode('META'), 'LRT', 1, 'C', 1);
    $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
    $pdf->SetWidths(array(15, 110, 6, 15, 110));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array("VALOR: ", $valorlb, '', 'VALOR: ', $valorMeta));
    $pdf->SetArrayBorders(array('LB', 'RB', '', 'LB', 'RB'));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array(utf8Decode("AÑO: "), $aniolb, '', utf8Decode('AÑO:'), APP_EJERCICIO));
    $pdf->Cell(0, 5, utf8Decode(''), 0, 1, 1, 0);

    //MEDIOS DE VERIFICACIÓN
    $pdf->CheckPageBreak(10);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(256, 7, utf8Decode('MEDIO DE VERIFICACIÓN DEL INDICADOR :'), 'RLT', 1, 1, 0);
    $pdf->SetFont('Calibri', '', 11);
    $pdf->MultiCell(256, 4, utf8Decode(mb_strtoupper(str_replace('|', "\n", $mediosVerPUR))), 'RL', 0, 0);
    $pdf->Cell(256, 2, utf8Decode(''), "T", 1, 1, 0);
    $pdf->AddPage();
}

// NIVEL COMPONENTE

$sqlComp = "
    SELECT p.prgs_id, prgs_nombre, sp.sprs_id, pdf_id, e.epfi_id, ind_id,
    m.gpo_id, m.ur_id, IF(tiv_id=1,(mcot_denominadorT2+mcot_denominadorT4),mcot_denominadorT4) as meta,
    ind_nombreMIR, ind_definicion, ind_formulaCalculo,tpi_id,tpi_nombre, dim_id, dim_nombre, frm_id, ind_definicionMIR,
    frm_nombre, ses_id, ses_nombre,MAX( mco_descripcion) AS mem_descripcion, ind_nombre, tiv_id, tiv_nombre, umi_id,
    umi_nombre,  CONCAT(m.gpo_id, m.ur_id) ur, mco_descripcion AS descripcion, m.mco_lb, m.mco_lbIndicador,
    m.mco_lbValor, m.mco_original, m.mco_anteriorIndicador,m.mco_anterior, aux.mco_numerador
    FROM  cat_Programas p
    INNER JOIN cat_Subprogramas sp ON p.prgs_id=sp.prgs_id
    INNER JOIN cat_ProductoFinal USING (pdf_id)
    INNER JOIN enc_ProductoFinalIndicador e USING (pdf_id)
    LEFT JOIN cat_MetaComponente m ON p.prgs_id = m.prgs_id AND sp.sprs_id=m.sprs_id AND e.epfi_id=m.epfi_id
    LEFT JOIN aux_HistoricoModificacionMetaComponente aux ON p.prgs_id = aux.prgs_id AND sp.sprs_id=aux.sprs_id AND e.epfi_id=aux.epfi_id AND m.gpo_id = aux.gpo_id AND m.ur_id = aux.ur_id
    LEFT JOIN cat_Indicador USING(nim_id,ind_id)
    LEFT JOIN cat_TipoIndicador USING(tpi_id)
    INNER JOIN cat_UnidadMedidaIndicador USING (umi_id)
    LEFT JOIN cat_Dimension USING(dim_id)
    LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
    LEFT JOIN cat_SentidoEsperado USING(ses_id)
    INNEr JOIN cat_TipoValor USING(tiv_id)
    WHERE p.prgs_id = :prgs_id AND (esr_id=4 OR sp.sprs_id=0) AND pdf_id!=0 AND e.sts_id=1 AND m.mco_original >= 0
    GROUP BY p.prgs_id,m.sprs_id,pdf_id,e.epfi_id,ind_id,m.gpo_id, m.ur_id
    ORDER BY epfi_orden, m.sprs_id, pdf_id, ind_id,m.gpo_id, m.ur_id;
";
$parametros = [
    ":prgs_id" => $_SESSION[_S]['clasAdm-prg']
];
$conexion->conectar();
$conexion->setSql($sqlComp);
$conexion->consultar($parametros);
$conexion->desconectar();

$numIndicadoresCom = 1;
$contInd = 1;

while ($datos = $conexion->extraer()) {

    $pdf_id = $datos['pdf_id'];
    //INDICADORES NIVEL COMPONENTE
    $pdf->SetRelleno("si");
    $pdf->SetFillColor(199, 209, 219);
    $pdf->setAltoLinea(3);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(0, 0, utf8Decode(''), 0, 1, 1, 0);
    $pdf->SetArrayBorders(array('TLB', 'TB', 'TRB'));
    $pdf->SetWidths(array(50, 10, 196));
    $pdf->Row(array('NOMBRE DEL PROGRAMA:', str_pad($datos['prgs_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datos['prgs_nombre']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NOMBRE DEL INDICADOR:', str_pad($datos['ind_id'], 2, "0", STR_PAD_LEFT), utf8Decode(mb_strtoupper($datos['ind_nombreMIR']))));
    $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
    $pdf->Row(array('NIVEL DEL INDICADOR:', '', strtoupper(utf8Decode("COMPONENTE ").$datos['pdf_id'])));
    $pdf->Cell(0, 6, utf8Decode(''), 0, 1, 1, 0);

    //DETALLE DEL INDICADOR
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(10);
    $pdf->setAltoLinea(3);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
    $pdf->SetWidths(array(125, 6, 125));
    $pdf->Row(array(utf8Decode('DEFINICIÓN DEL INDICADOR:'), '', utf8Decode('DIMENSIÓN A MEDIR:')));
    $pdf->SetArrayBorders(array('LR', '', 'LR'));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode(mb_strtoupper($datos['ind_definicionMIR'])), '', utf8Decode($datos['dim_id'] . "-" . $datos['dim_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('MÉTODO DE CÁLCULO DEL INDICADOR:'), '', utf8Decode('TIPO DE INDICADOR PARA RESULTADOS:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ind_formulaCalculo']), '', utf8Decode($datos['tpi_id'] . "-" . $datos['tpi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('SENTIDO DEL INDICADOR:'), '', utf8Decode('UNIDAD DE MEDIDA:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode($datos['ses_id'] . "-" . $datos['ses_nombre']), '', utf8Decode($datos['umi_id'] . "-" . $datos['umi_nombre'])));
    $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('TIPO DE VALOR DE LA META:'), '', utf8Decode('FRECUENCIA DE MEDICIÓN:')));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->SetArrayBorders(array('LRB', '', 'LRB'));
    $pdf->Row(array(utf8Decode($datos['tiv_id'] . "-" . $datos['tiv_nombre']), '', utf8Decode($datos['frm_id'] . "-" . $datos['frm_nombre'])));
    $pdf->SetArrayBorders(array('', '', ''));
    $pdf->setAltoLinea(2);
    $pdf->Row(array("", "", '', "", ""));

    //DETALLE DE  LAS METAS

    $pdf->CheckPageBreak(10);
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->Cell(125, 8, utf8Decode('LÍNEA BASE'), 'LRT', 0, 'C', 1);
    $pdf->Cell(6, 8, utf8Decode(''), 0, 0, 1, 0);
    $pdf->Cell(125, 8, utf8Decode('META'), 'LRT', 1, 'C', 1);

    //El valor de la meta depende de la unidad de medida
    switch ($datos['umi_id']) {
        case 5: // TASA
            $aniolb = $datos['mco_anterior'];
            $valorlb = $datos['mco_lbIndicador'];
            $valorMeta = $datos['mco_original'];
            break;
        default:
            $aniolb = $datos['mco_lb'];
            $valorlb = $datos['mco_numerador'];
            $valorMeta = $datos['meta'];
            break;
    }

    //DATOS DE LA META

    $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
    $pdf->SetWidths(array(15, 110, 6, 15, 110));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array("UR: ", utf8Decode($datos['ur']), '', 'UR: ', utf8Decode($datos['ur'])));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array("VALOR: ", utf8Decode($valorlb), '', 'VALOR: ', utf8Decode($valorMeta)));
    $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
    $pdf->setFuentes($fuentes);
    $pdf->Row(array(utf8Decode("AÑO: "), utf8Decode($aniolb), '', utf8Decode('AÑO:'), APP_EJERCICIO));
    $pdf->Row(array("", "", '', "", ""));
    $pdf->SetArrayBorders(array('T', 'T', '', 'T', 'T'));
    $pdf->Row(array("", "", '', "", ""));

    $mediosVerUR = '';

    $supMeCom = new AccesoDatos();
    $supMeCom->setSql('
        SELECT *
        FROM cat_MedioVerificacionComponente m
        WHERE prgs_id = :prg_id AND sprs_id = :spr_id AND epfi_id = :epfi_id
        AND (mvcm_id IS NOT NULL) AND mvcm_trimestreCierre IN(0)
        ORDER BY gpo_id, ur_id;
    ');
    $parametros = [
        ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
        ':epfi_id' => $datos['epfi_id'],
        ':spr_id' => $datos['sprs_id']
    ];
    $supMeCom->conectar();
    $supMeCom->consultar($parametros);
    $supMeCom->desconectar();

    while ($MediosC = $supMeCom->extraer()) {
        $mediosVerUR .= "UR " . $MediosC['gpo_id']. $MediosC['ur_id'] .": " . $MediosC['mvcm_nombre']. "\n" . "\n";
    }

    $prodFinal = $datos['pdf_id'];

    $indicadorCom = new AccesoDatos();
    $indicadorCom->conectar();
    $indicadorCom->setSql('
        SELECT COUNT(*) AS numInd FROM enc_ProductoFinalIndicador
        INNER JOIN cat_MetaComponente USING(epfi_id)
        WHERE pdf_id = ?
        AND sts_id = ?;
    ');
    $parametros = [
        $prodFinal,
        1
    ];
    $indicadorCom->consultar($parametros);
    $datos = $indicadorCom->extraer();
    $numIndicadoresCom = $datos['numInd'];
    $indicadorCom->desconectar();

    //MEDIOS DE VERIFICACIÓN
    $pdf->SetRelleno("no");
    $pdf->CheckPageBreak(15);
    $pdf->setAltoLinea(4);
    $pdf->SetAligns(array('L'));
    $pdf->SetFont('calibrib', 'B', 11);
    $pdf->SetArrayBorders(array('TLR'));
    $pdf->SetWidths(array(256));
    $pdf->CheckPageBreak(10);
    $pdf->Row(array(utf8Decode('MEDIO DE VERIFICACIÓN DEL INDICADOR:')));
    $pdf->SetArrayBorders(array('LBR'));
    $pdf->SetFont('Calibri', '', 11);
    $pdf->Row(array(utf8Decode(mb_strtoupper($mediosVerUR))));
    $pdf->AddPage();

    //MEDIOS DE VERIFICACIÓN
    if ($contInd == $numIndicadoresCom) {

        $contAct = 0;
        $numMetas = 0;
        // NIVEL ACTIVIDADES

        $sqlAct = "
            SELECT p.prgs_id, p.prgs_nombre, sprs_id, pii_id, v.pdf_id, pi.pdi_id, pdi_mir, ind_id, ind_nombre, macm_descripcion, gpo_id, ur_id,
            ind_nombreMIR, ind_definicion, ind_definicionMIR, ind_formulaCalculo, tpi_id, tpi_nombre, dim_id, dim_nombre, frm_id, frm_nombre, ses_id, ses_nombre,
            umi_id, umi_nombre, tiv_id, tiv_nombre, mediosAct, pii_orden,CONCAT(gpo_id, ur_id) ur, ma.macm_lb, ma.macm_lbValor, ma.macm_lbIndicador,  ma.macm_anio,
            IF(tiv_id=1,(macm_denominadorT1+macm_denominadorT2+macm_denominadorT3+macm_denominadorT4),macm_denominadorT4) as meta, aux.macm_numerador
            FROM cat_Programas p
            INNER JOIN cat_Subprogramas sp ON p.prgs_id=sp.prgs_id
            INNER JOIN cat_ProductoIntermedio pi
            INNER JOIN vin_ProductoIntermedioIndicador v USING(pdi_id)
            INNER JOIN vin_ProductoFinalProductoIntermedio vpfi on sp.pdf_id=vpfi.pdf_id AND pi.pdi_id=vpfi.pdi_id
            INNER JOIN cat_MetaActividadMir ma USING(pii_id)
            LEFT JOIN aux_HistoricoMetaActividadMir aux USING(gpo_id, ur_id, pii_id)
            LEFT JOIN cat_Indicador USING(nim_id,ind_id)
            LEFT JOIN cat_TipoIndicador USING(tpi_id)
            LEFT JOIN cat_Dimension USING(dim_id)
            LEFT JOIN cat_FrecuenciaMedicion USING(frm_id)
            LEFT JOIN cat_SentidoEsperado USING(ses_id)
            LEFT JOIN cat_UnidadMedidaIndicador USING(umi_id)
            LEFT JOIN cat_TipoValor USING(tiv_id)
            LEFT JOIN (
                SELECT mv.pdf_id,pdi_id,GROUP_CONCAT( mvac_nombre,' UR:',gpo_id,ur_id ,'\n'  SEPARATOR '|') mediosAct
                FROM cat_MedioVerificacionActividad mv
                INNER JOIN cat_Subprogramas USING (prgs_id, sprs_id)
                WHERE prgs_id= :prgs_id
                GROUP BY pdf_id, pdi_id
                ORDER BY mv.pdf_id
            )Mev ON v.pdf_id=Mev.pdf_id AND v.pdi_id=Mev.pdi_id
            WHERE p.prgs_id = :prgs_id AND v.pdf_id = :pdf_id AND v.sts_id= 1 AND vpfi.sts_id= 1 AND ma.macm_original >= 0
            GROUP BY pii_id, pdf_id, pdi_id, ind_id, ma.gpo_id, ma.ur_id
            ORDER BY pii_orden,pdf_id, pdi_id,ind_id;
        ";
        $parametros = [
            ':prgs_id' => $_SESSION[_S]['clasAdm-prg'],
            ':pdf_id' => $pdf_id
        ];
        $conexionA = new AccesoDatos();
        $conexionA->conectar();
        $conexionA->setSql($sqlAct);
        $conexionA->consultar($parametros);
        $conexionA->desconectar();
        $numMetas = $conexionA->getNumeroRegistros();
        $actAnt = '';
        while ($datosA = $conexionA->extraer()) {
            //INDICADORES NIVEL ACTIVIDAD
            $pdf->SetRelleno("si");
            $pdf->SetFillColor(199, 209, 219);
            $pdf->setAltoLinea(3);
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->Cell(0, 0, utf8Decode(''), 0, 1, 1, 0);
            $pdf->SetArrayBorders(array('TLB', 'TB', 'TRB'));
            $pdf->SetWidths(array(50, 10, 196));
            $pdf->Row(array('NOMBRE DEL PROGRAMA:', str_pad($datosA['prgs_id'], 2, "0", STR_PAD_LEFT), strtoupper(utf8Decode($datosA['prgs_nombre']))));
            $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
            $pdf->Row(array('NOMBRE DEL INDICADOR:', str_pad($datosA['ind_id'], 2, "0", STR_PAD_LEFT), utf8Decode(mb_strtoupper($datosA['ind_nombreMIR']))));
            $pdf->Cell(0, 3, utf8Decode(''), 0, 1, 1, 0);
            $pdf->Row(array('NIVEL DEL INDICADOR: ', '', strtoupper(utf8Decode("actividad ")).$datosA['pdi_id']));
            $pdf->Cell(0, 6, utf8Decode(''), 0, 1, 1, 0);

            //DETALLE DEL INDICADOR
            $pdf->SetRelleno("no");
            $pdf->CheckPageBreak(10);
            $pdf->setAltoLinea(3);
            $pdf->SetAligns(array('L'));
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->SetArrayBorders(array('TLR', '', 'TLR'));
            $pdf->SetWidths(array(125, 6, 125));
            $pdf->Row(array(utf8Decode('DEFINICIÓN DEL INDICADOR:'), '', utf8Decode('DIMENSIÓN A MEDIR:')));
            $pdf->SetArrayBorders(array('LR', '', 'LR'));
            $pdf->SetFont('Calibri', '', 11);
            $pdf->Row(array(utf8Decode(mb_strtoupper($datosA['ind_definicionMIR'])), '', utf8Decode($datosA['dim_id'] . "-" . $datosA['dim_nombre'])));
            $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->CheckPageBreak(10);
            $pdf->Row(array(utf8Decode('MÉTODO DE CÁLCULO DEL INDICADOR:'), '', utf8Decode('TIPO DE INDICADOR PARA RESULTADOS:')));
            $pdf->SetFont('Calibri', '', 11);
            $pdf->Row(array(utf8Decode($datosA['ind_formulaCalculo']), '', utf8Decode($datosA['tpi_id'] . "-" . $datosA['tpi_nombre'])));
            $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->CheckPageBreak(10);
            $pdf->Row(array(utf8Decode('SENTIDO DEL INDICADOR:'), '', utf8Decode('UNIDAD DE MEDIDA:')));
            $pdf->SetFont('Calibri', '', 11);
            $pdf->Row(array(utf8Decode($datosA['ses_id'] . "-" . $datosA['ses_nombre']), '', utf8Decode($datosA['umi_id'] . "-" . $datosA['umi_nombre'])));
            $pdf->Row(array(utf8Decode(""), '', utf8Decode("")));
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->CheckPageBreak(10);
            $pdf->Row(array(utf8Decode('TIPO DE VALOR DE LA META:'), '', utf8Decode('FRECUENCIA DE MEDICIÓN:')));
            $pdf->SetFont('Calibri', '', 11);
            $pdf->SetArrayBorders(array('LRB', '', 'LRB'));
            $pdf->Row(array(utf8Decode($datosA['tiv_id'] . "-" . $datosA['tiv_nombre']), '', utf8Decode($datosA['frm_id'] . "-" . $datosA['frm_nombre'])));
            $pdf->SetArrayBorders(array('', '', ''));
            $pdf->setAltoLinea(2);
            $pdf->Row(array("", "", '', "", ""));

            //DETALLE DE  LAS METAS

            $pdf->CheckPageBreak(10);
            $pdf->SetFont('calibrib', 'B', 11);
            $pdf->Cell(125, 8, utf8Decode('LÍNEA BASE'), 'LRT', 0, 'C', 1);
            $pdf->Cell(6, 8, utf8Decode(''), 0, 0, 1, 0);
            $pdf->Cell(125, 8, utf8Decode('META'), 'LRT', 1, 'C', 1);

            //El valor de la meta depende de la unidad de medida
            switch ($datosA['umi_id']) {
                case 5: // TASA
                    $aniolb = $datosA['macm_anterior'];
                    $valorlb = $datosA['macm_lbIndicador'];
                    $valorMetaA = $datosA['macm_original'];
                    break;
                default:
                    $aniolb = $datosA['macm_lb'];
                    $valorlb = $datosA['macm_numerador'];
                    $valorMetaA = $datosA['meta'];
                    break;
            }

            //DATOS DE LA META

            $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
            $pdf->SetWidths(array(15, 110, 6, 15, 110));
            $pdf->setFuentes($fuentes);
            $pdf->Row(array("UR: ", utf8Decode($datosA['ur']), '', 'UR: ', utf8Decode($datosA['ur'])));
            $pdf->setFuentes($fuentes);
            $pdf->Row(array("VALOR: ", utf8Decode($valorlb), '', 'VALOR: ', utf8Decode($valorMetaA)));
            $pdf->SetArrayBorders(array('L', 'R', '', 'L', 'R'));
            $pdf->setFuentes($fuentes);
            $pdf->Row(array(utf8Decode("AÑO: "), utf8Decode($aniolb), '', utf8Decode('AÑO:'), APP_EJERCICIO));
            $pdf->Row(array("", "", '', "", ""));

            $pdf->SetArrayBorders(array('T', 'T', '', 'T', 'T'));
            $pdf->Row(array("", "", '', "", ""));

        $mediosVerURAct = '';

        $supMeAct = new AccesoDatos();
        $supMeAct->setSql('
            SELECT gpo_id, ur_id,mvac_nombre
            FROM cat_MedioVerificacionActividad
            WHERE pdf_id = ? AND pdi_id = ? aND pii_id = ? AND mvac_trimestreCierre = ?
            ORDER BY pdf_id, pdi_id
        ');
        $parametros = [
            $datosA['pdf_id'],
            $datosA['pdi_id'],
            $datosA['pii_id'],
            0
        ];
        $supMeAct->conectar();
        $supMeAct->consultar($parametros);
        $supMeAct->desconectar();

        while ($MediosA = $supMeAct->extraer()) {
            $mediosVerURAct .= "UR " . $MediosA['gpo_id'] . $MediosA['ur_id'] . " " . $MediosA['mvac_nombre'] . "\n" . "\n";
        }

        //MEDIOS DE VERIFICACIÓN
        $pdf->SetRelleno("no");
        $pdf->CheckPageBreak(15);
        $pdf->setAltoLinea(4);
        $pdf->SetAligns(array('L'));
        $pdf->SetFont('calibrib', 'B', 11);
        $pdf->SetArrayBorders(array('TLR'));
        $pdf->SetWidths(array(256));
        $pdf->CheckPageBreak(10);
        $pdf->Row(array(utf8Decode('MEDIO DE VERIFICACIÓN DEL INDICADOR:')));
        $pdf->SetArrayBorders(array('LBR'));
        $pdf->SetFont('Calibri', '', 11);
        $pdf->Row(array(utf8Decode(mb_strtoupper($mediosVerURAct))));
        if ($contAct != $numMetas) {
            $pdf->AddPage();
        }
        $contAct ++;
        $contInd = 0;
        }
    }
    $contInd++;
}

$pdf->Output();
