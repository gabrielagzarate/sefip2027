<?php
/**
 * Usuario: evenegass
 * Fecha Actualización: 14/11/2018 12:00 PM
 * Comentarios: Se agrego la linea para establecer la proteccion mediante contraseña y se dio formato Psr1
 */

session_start();
ini_set('memory_limit', '30M');
set_time_limit(0);
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . "../../../../../lib/helpers.php";

class ReporteAfectacion extends PDF
{
    public function __construct()
    {
        parent::__construct();
        switch ($_SESSION[_S]['nvj_id']) {
            case '1':
                return false;
                break;

        }
    }

    public function Footer($lastPage = true)
    {
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();
        $this->SetY(-20);
        $this->SetY(-20);
        $this->Cell(0, 10, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 1, 0, 'R');
    }

    public function Header()
    {
        $izquierda = 5;
        $row = $this->getDatos();
        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $row = $this->getDatos();
        $this->SetMargins(15, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->Image('../../../../img/logosefin-color-fblanco.png', $ix + 170, $iy, '99', '22', 'PNG', '');

        $this->SetFont('Arial', 'B', 8);
        $this->SetXY($izquierda, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 4, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($izquierda, $ytmp + 4);
        $this->Cell(180, 4, utf8Decode('SECRETARÍA DE FINANZAS'), 0, 1, 'L');
        $this->ln(3);
        $this->SetXY($izquierda, $ytmp + 8);
        $ytmp = $this->GetY();
        $this->SetFont('Arial', 'B', 8);
        if ($row) {
            $this->Cell(180, 4,
                'UR ' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] . ":" . utf8Decode($row['ur_nombre']), 0, 1,
                'L');
            $this->SetXY($ix + 17, $ytmp + 3.5);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(180, 4, 'AFECTACIONES DE ' . utf8Decode($row['tcl_nombre']), 0, 1, 'C');
        }
        $this->Line($izquierda, 26, 291, 26);
        $this->ln(6);
    }

    public function tituloAfe($row)
    {
        $this->setFillColor(210, 210, 210);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 7);
        $this->setDrawColor(0, 0, 0);
        $this->setBorders("b");
        $this->setX(5);
        $this->Cell(10, 5, utf8Decode('No:' . $row['afe_id']), "B", 0, 'C', true);
        $this->setX(15);
        $this->Cell(40, 5, 'FECHA: ' . substr($row['afe_fechaElaboracion'], 0, 10), "B", 0, 'L', true);

        $periodo = $row['afe_fechaInicio'] . ' - ' . $row['afe_fechaFin'];
        $data = 'OBRA/ACTIVIDAD: ' . $row['oa_id'] . " - " . $row['oa_nombre'] . ($row['oa_numObra'] != "" ? 'NÚMERO DE OBRA '
            . $row['oa_numObra'] : '');

        $this->setX(50);
        $this->Cell(185, 5, utf8Decode($data), "B", 0, 'L', true);
        $this->setX(235);
        $this->Cell(56, 5, utf8Decode($periodo != " - " ? "PERIODO " . $periodo : ""), "B", 1, 'C', true);
        $this->setBorders(0);
        $r = hexdec(substr($row['eaf_color'], 0, 2));
        $g = hexdec(substr($row['eaf_color'], 2, 2));
        $b = hexdec(substr($row['eaf_color'], 4, 2));

        $this->SetFont('Arial', '', 7);
        $this->setFillColor(227, 227, 227);
        $this->setTextColor(100, 100, 115);

        if ($row['tem_carga'] == "") {
            $this->setX(5);
            $this->Cell(145, 4, utf8Decode("SUBTIPO:" . $row['stc_nombre']), 0, 0, 'L', true);
            $this->setFillColor($r, $g, $b);
            $this->setTextColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 7);
            $this->setX(150);
            $this->Cell(141, 4, utf8Decode('ESTADO DE LA AFECTACIÓN:' . $row['eaf_id'] . " - " . $row['eaf_nombre']),
                0, 0, 'C', true);
        } else {
            $this->setX(5);
            $this->Cell(95, 4, utf8Decode("SUBTIPO:" . $row['stc_nombre']), 0, 0, 'L', true);
            $this->Cell(50, 4, utf8Decode("CARGA MASIVA:" . $row['tem_carga']), 0, 0, 'L', true);
            $this->setFillColor($r, $g, $b);
            $this->setTextColor(0, 0, 0);
            $this->SetFont('Arial', 'B', 7);
            $this->setX(150);
            $this->Cell(141, 4, utf8Decode('ESTADO DE LA AFECTACIÓN:' . $row['eaf_id'] . " - " . $row['eaf_nombre']),
                0, 0, 'C', true);
        }
        $this->ln(4);
        $this->setFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
    }

    public function cabecera()
    {
        $this->SetFont('Arial', 'B', 6.5);
        $this->SetFillColor(255, 255, 255);
        $this->setX(5);
        //6 EN EL 5
        $this->SetWidths(array(10, 4, 10, 43, 7, 50, 21, 14, 37, 20, 24, 24, 22));
        $this->SetDrawColor(180, 180, 180);
        $this->SetBorders("TB");
        $this->setTextColor(0, 0, 0);
        $this->SetAligns(array('L', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $this->Row(array(
            utf8Decode('N°'),
            utf8Decode('T'),
            utf8Decode('CMP'),
            utf8Decode('CLAVE'),
            utf8Decode('PG PE'),
            utf8Decode('DESCRIPCIÓN'),
            utf8Decode('REF.'),
            utf8Decode('FECHA'),
            utf8Decode('PROVEEDOR'),
            utf8Decode('R.F.C.'),
            utf8Decode('PERCEPCIONES+'),
            utf8Decode('RETENCIONES -'),
            utf8Decode('I.V.A'),
        ));
        $this->setTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->ln(.2);
    }

    public function totales($mensaje, $total)
    {
        $this->SetFillColor(230, 230, 230);
        $this->SetRelleno("no");
        if (substr($mensaje, 0, 6) === "AFECTA") {
            $this->SetFillColor(180, 180, 180);
            $this->SetTextColor(0, 0, 0);
            $this->SetRelleno("si");
        }
        $this->SetDrawColor(220, 220, 220);
        $this->setX(5);
        $this->SetBorders("T");
        $this->SetWidths(array(254, 32));
        $this->SetAligns(array('R', 'R'));
        $this->SetFont('Arial', 'B', 6);
        $this->Row(array('TOTAL ' . utf8Decode($mensaje), "$" . number_format($total, 2)));
        $this->SetRelleno("no");
        $this->SetTextColor(0, 0, 0);
    }
}

$pdf = new ReporteAfectacion('L', 'mm', 'Letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_SCP);
$pdf->SetAltoLinea(1.1);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 15);
$gpo_id = $_SESSION[_S]['clasAdm-gpo'];
$ur_id = $_SESSION[_S]['clasAdm-ur'];

$sql = '
    SELECT ur_nombre, a.afe_id, DATE_FORMAT(afe_fechaElaboracion, "%d/%m/%Y") afe_fechaElaboracion,
            Tipo, tcl_id, tcl_nombre, oa_id, oa_nombre, Numero, Clave, Concepto, Referencia, FechaReferencia,
            rfc_id, rfc_nombre, rfc_paterno, rfc_materno, soc_nombre, Percepcion, Retencion, iva,
            pg_id, pe_id, oa_numObra, ConceptoN, eaf_id, eaf_nombre, eaf_color, afe_fechaInicio,
            afe_fechaFin, stc_nombre, Nombre, cmp_id, a.tem_carga
    FROM scp_Afectacion a
        INNER JOIN cat_EstadoAfectacion USING (eaf_id)
        INNER JOIN cat_TipoClc using (tcl_id)
        INNER JOIN cat_SubtipoClc using (tcl_id, stc_id)
        LEFT JOIN  scp_Clc USING (gpo_id, ur_id, clc_id, tcl_id)
        JOIN cat_UnidadResponsable using (gpo_id, ur_id)
        LEFT JOIN  enc_Programatico USING (gpo_id, ur_id, epg_id)
        LEFT JOIN  cat_ObraActividad USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
        LEFT JOIN
            (
                SELECT c.gpo_id, c.ur_id, afe_id, "C" AS Tipo, ncc_id as Numero, ncc_percepcion AS "Percepcion",
                    ncc_retencion AS "Retencion", ncc_concepto AS Concepto, "N/A" AS iva, ncc_referencia as Referencia,
                    ncc_fechaReferencia as FechaReferencia, "N" as pg_id, "A" as pe_id, c.rfc_id, rfc_nombre,
                    rfc_paterno, rfc_materno, soc_nombre, c.usr_fecha AS Fecha, "Contable" as ConceptoN,
                    concat(ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, ".", cta_gpo_id, cta_ur_id, ".", cta_ue_id, ".", cgc_id, ".", cta_id)AS Clave,
                    cta_nombre AS Nombre, "NA" AS cmp_id
                FROM scp_Contable c
                INNER JOIN cg_PlanCuentas USING (ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ur_id, cta_ue_id, cgc_id, cta_id)
                INNER JOIN cat_Proveedor USING (rfc_id)
                INNER JOIN ' . BD_SINPRES . '.cat_Proveedores USING (rfc_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SociedadProveedor USING (soc_id)
                WHERE c.gpo_id = :gpo_id AND c.ur_id = :ur_id
                
                UNION ALL 
                
                SELECT p.gpo_id, p.ur_id, afe_id, "P" AS Tipo, ncp_id as Numero, ncp_percepcionNeto AS "Percepcion",
                    "0" AS "Retencion", ncp_concepto AS Concepto, ncp_percepcionIva as iva,
                    ncp_referencia as Referencia, ncp_fechaReferencia as FechaReferencia, pg_id, pe_id,
                    p.rfc_id, rfc_nombre, rfc_paterno, rfc_materno, soc_nombre, p.usr_fecha AS Fecha, pe_nombre as ConceptoN,
                    CONCAT(p.gpo_id, p.ur_id, ue_id, "-", prg_id, spr_id, py_id, oa_id, "-", pg_id, pe_id, cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio) AS Clave, "" AS Nombre,
                    cmp_id
                FROM scp_Presupuestal p
                INNER JOIN enc_Programatico using (epg_id)
                INNER JOIN enc_ClaveFinanciamiento using (ecf_id)
                INNER JOIN cat_Proveedor USING (rfc_id)
                INNER JOIN sinpres.cat_Proveedores USING (rfc_id)
                LEFT JOIN sinpres.cat_SociedadProveedor USING (soc_id)
                JOIN cat_PartidaEspecifica USING (pg_id, pe_id)
                WHERE p.gpo_id = :gpo_id AND p.ur_id = :ur_id
            ) AS tabla USING (gpo_id,ur_id,afe_id)
        WHERE a.gpo_id = :gpo_id AND a.ur_id = :ur_id
        AND tcl_id = :tcl_id
    ';
$params = [
    ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
    ':ur_id' => $_SESSION[_S]['clasAdm-ur'],
    ':tcl_id' => $_GET['tipo']
];

if (isset($_GET['in'])) {
    $sql .= ' AND a.afe_id BETWEEN ' . $_GET['in'] . ' AND ' . $_GET['fi'];
}
if ($_SESSION[_S]['nvj_id'] == 1) {
    $sql .= (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "" ? ' AND a.ue_id = '.$_SESSION[_S]['clasAdm-ue'] : '');
}
$sql .= ' ORDER BY a.afe_fechaElaboracion,afe_id,Numero';

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();
if ($row) {
    $pdf->setDatos($row);
    $pdf->AddPage();

    $afe = $row['afe_id'];
    $tot_percepciones = 0;
    $tot_retenciones = 0;
    $total_numero = 0;
    $celda = 1;
    $records = 0;

    $pdf->tituloAfe($row);
    $pdf->cabecera();

    do {
        if ($afe != $row['afe_id']) {
            if ($pdf->CheckPageBreak(15)) {
                $pdf->cabecera();
            }
            if ($records != 0) {
                $pdf->totales('PERCEPCIONES', $tot_percepciones);
                if ($tot_retenciones != 0) {
                    $pdf->totales('RETENCIONES', $tot_retenciones);
                }

                $pdf->totales('AFECTACIÓN ' . $afe, $total_numero);

                $tot_percepciones = 0;
                $tot_retenciones = 0;
                $tot_retenciones = 0;
                $total_numero = 0;
                $celda = 1;
            } else {
                $pdf->totales('NO HAY NUMEROS DE CONTROL REGISTRADOS PARA LA AFECTACION' . $afe, 0);
                $tot_percepciones = 0;
                $tot_retenciones = 0;
                $total_numero = 0;
                $celda = 1;
            }
            $afe = $row['afe_id'];
            $records = 0;
            $pdf->ln(4);

            if ($pdf->CheckPageBreak(15)) {
                $pdf->AddPage();
            }

            $pdf->tituloAfe($row);
            $pdf->cabecera();

            $afe = $row['afe_id'];
        }

        if ($row['Numero'] != null) {

            if ($pdf->CheckPageBreak(15)) {
                $pdf->tituloAfe($row);
                $pdf->cabecera();
                $pdf->setX(5);
            }

            $records++;
            if ($celda == 1) {
                $pdf->SetRelleno("no");
                $celda = 0;
            } else {
                $pdf->SetRelleno("si");
                $celda = 1;
            }

            $pdf->SetFont('Arial', '', 6);
            $pdf->SetFillColor(245, 245, 245);
            $pdf->setX(5);
            $pdf->SetWidths(array(10, 4, 10, 43, 7, 50, 21, 14, 37, 20, 24, 24, 22));
            $pdf->SetDrawColor(235, 235, 235);
            $pdf->SetBorders(1);
            $pdf->SetAligns(array('L', 'C', 'L', 'L', 'L', 'C', 'C', 'L', 'L', 'R', 'R', 'R', 'R'));
            $pdf->Row(array(
                utf8Decode($row['Numero']),
                utf8Decode($row['Tipo']),
                utf8Decode($row['cmp_id']),
                utf8Decode($row['Clave'] . '            ' . $row['Nombre']),
                utf8Decode($row['pg_id'] . ' ' . $row['pe_id']),
                $row['Tipo'] == "P" ? utf8Decode($row['ConceptoN']) : utf8Decode($row['Concepto']),
                utf8Decode($row['Referencia']),
                utf8Decode($row['FechaReferencia']),
                utf8Decode($row['rfc_nombre'] . ' ' . $row['rfc_paterno'] . ' ' . $row['rfc_materno'] . ' ' . $row['soc_nombre']),
                utf8Decode($row['rfc_id']),
                utf8Decode("$" . number_format($row['Percepcion'], 2)),
                utf8Decode("$" . number_format($row['Retencion'], 2)),
                $row['iva'] == "N/A" ? $row['iva'] : "$" . number_format($row['iva'], 2),
            ));
        }
        $tot_percepciones += $row['Percepcion'];
        $tot_retenciones += $row['Retencion'];
        $total_numero += ($row['Percepcion'] - $row['Retencion']);
    } while ($row = $conexion->extraer());

    if ($records != 0) {
        $pdf->totales('PERCEPCIONES', $tot_percepciones);
        $pdf->totales('RETENCIONES', $tot_retenciones);
        $pdf->totales('AFECTACIÓN ' . $afe, $total_numero);
        $tot_percepciones = 0;
        $tot_retenciones = 0;
        $total_numero = 0;
        $celda = 1;
    } else {
        $pdf->totales('NO HAY NUMEROS DE CONTROL REGISTRADOS PARA LA AFECTACION ' . $afe, 0);
    }
}

$pdf->Output();