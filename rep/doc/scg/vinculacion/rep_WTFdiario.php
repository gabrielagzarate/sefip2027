<?php

session_start();
ini_set('memory_limit', '200M');
set_time_limit(0);

require_once('../../../lib/reportetabla.1.0.0.php');
require_once('../../../../lib/AccesoDatos.php');
require_once('../../../../ent/Entidad.php');
require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../../ent/UnidadEjecutora.php');


$clc_id = '';
if (isset($_POST['clc_id']) && $_POST['clc_id'] !== '' && $_POST['clc_id'] !== '0') {
    $clc_id = $_POST['clc_id'];
} else {
    $clc_id = 987654321;
}

//Extendiendo la clase pdf para escribir el encabezado y pie de página 
class reportetabla extends PDF
{
    function Footer()
    {
        $this->SetY(-13);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    function Header()
    {

        $ur_ent = '';
        $nivel = $_SESSION[_S]['nvj_id'];
        if ($_SESSION[_S]['nvj_id'] <= 2) {
            $gpo = $_SESSION[_S]['s24-gpo'];
            $ur = $_SESSION[_S]['s24-ur'];
            $ur_ent = $gpo . $ur;
        } else {
            $ur = $_SESSION[_S]['clasAdm-ur'];
            $gpo = $_SESSION[_S]['clasAdm-gpo'];
            $ur_ent = $gpo . $ur;
        }

        $miconexion = new AccesoDatos();
        $miconexion->conectar();
        $miconexion->setSql('select ur_nombre from cat_UnidadResponsable where ur_clave=' . $ur_ent . ' ');
        $miconexion->consultar();
        $miconexion->desconectar();
        while ($row = $miconexion->extraer()) {
            $nom_ur = $row['ur_nombre'];
        }


        $this->SetFont('Arial', 'B', 10);
        $this->Cell(180, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'C');
        $this->Cell(180, 5, mb_convert_encoding('LISTADO DIARIO CLÁSICO DE LA CLC ' . $_POST['clc_id'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Cell(180, 5, $gpo . $ur . ' - ' . mb_convert_encoding($nom_ur, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->ln(5);
    }
}


$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('SELECT 
            CONCAT( DAY( pma_fecha ), ".", LEFT( MONTHNAME( pma_fecha ), 3 ) ) AS fecha, mmc_id, 
            CONCAT( m.pot_id, "-", m.prf_id, m.pma_id ) AS poliza, m.pma_concepto, 
            CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id ) AS cuenta, 
            sum( pdx_debe ) as pdx_debe, sum(pdx_haber) as pdx_haber, pdx_clc_id_vinculacion, 
            epl_id, u.usr_usuario, m.apg_id
            FROM cg_PolizaDetalleAuxiliar p 
            JOIN cg_PolizaMaestro m USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
            LEFT JOIN cat_Usuario u ON ( p.usr_id = u.usr_id )
            WHERE p.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND p.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND ( pdx_clc_id_vinculacion = ' . $clc_id . ' OR pdx_clc_id_origen = ' . $clc_id . ' )
                AND epl_id = "N"
            GROUP BY msc_id, pot_id, pma_id, nat_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id
            ORDER BY pma_fecha, msc_id, pot_id, pma_id, nat_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cta_gpo_id, cta_ue_id;
        ');
$miconexion->consultar();
$miconexion->desconectar();
$pdf = new Reportetabla('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetBorders(0);
$pdf->ln(2);

$row = $miconexion->extraer();
$poliza = $row['poliza'];

while (true) {

    $concepto = mb_convert_encoding($row['pma_concepto'], 'ISO-8859-1', 'UTF-8');
    $poliza = $row['poliza'];
    $bolPrimeraLinea = true;
    $n++;

    $sumaDebe = 0;
    $sumaHaber = 0;

    while ($poliza == $row['poliza']) {

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetWidths(array(15, 10, 20, 30, 53, 25, 25, 12));
        $pdf->SetAligns(array('L', 'C', 'L', 'L', 'L', 'R', 'R', 'C'));
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->SetAltoLinea(3);

        if ($row['epl_id'] == "H" || $row['epl_id'] == "C") {
            $momento = $row['mmc_id'] . " * " . strtolower($row['epl_id']);
        } else {
            $momento = $row['mmc_id'];
        }


        if ($bolPrimeraLinea == true) {
            $pdf->Row(array(
                $row['fecha'], $momento, $row['poliza'], $row['usr_usuario'], $row['cuenta'],
                ($row['pdx_debe'] <> 0) ? number_format($row['pdx_debe'], 2) : '',
                ($row['pdx_haber'] <> 0) ? number_format($row['pdx_haber'], 2) : '', $row['apg_id']
            ));
        } else {
            $pdf->Row(array(
                '', '', '', '', $row['cuenta'],
                ($row['pdx_debe'] <> 0) ? number_format($row['pdx_debe'], 2) : '',
                ($row['pdx_haber'] <> 0) ? number_format($row['pdx_haber'], 2) : '', ''
            ));
        }
        $pdf->ln(1);
        $bolPrimeraLinea = false;

        $sumaDebe += $row['pdx_debe'];
        $sumaHaber += $row['pdx_haber'];

        $row = $miconexion->extraer();
    }

    $pdf->SetWidths(array(130, 25, 25, 10));
    $pdf->SetAligns(array('L', 'R', 'R', 'C'));
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Row(array($concepto, number_format($sumaDebe, 2), number_format($sumaHaber, 2), ''));
    $pdf->ln(1);
    $iy = $pdf->GetY();
    $pdf->Line(8, $iy, 200, $iy);
    $pdf->ln(2);

    if ($row == null) {
        break;
    }
}

$pdf->Output();
