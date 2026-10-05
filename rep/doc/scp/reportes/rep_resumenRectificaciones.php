<?php
declare (strict_types=1);
session_start();
ini_set('memory_limit', '200M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/Entidad.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];

if ($_SESSION[_S]['nvj_id'] <= 2) {
    $gpo = $_SESSION[_S]['gpo_id'];
    $ur = $_SESSION[_S]['ur_id'];
    $ur_ent = $gpo . $ur;
} else {
    $ur = $_SESSION[_S]['clasAdm-ur'];
    $gpo = $_SESSION[_S]['clasAdm-gpo'];
    $ur_ent = $gpo . $ur;
}

$inicio = DateToQuotedMySQLDate($_POST['inicio']);
$termino = DateToQuotedMySQLDate($_POST['termino']);
$tcl_id = '';
$parametros = [];

if (isset($_POST['tipo']) && $_POST['tipo'] !== '' && $_POST['tipo'] !== '0') {
    $tcl_id = 'AND tcl_id = :tcl_id';
    $parametros += [
        'tcl_id' => $_POST['tipo'],
    ];
} elseif (isset($_POST['SelectAll']) && $_POST['SelectAll'] !== '') {
    $tcl_id = '';
}

$fechas = '';

if ($inicio != '' and $termino != '') {
    $fechas = ' AND date(rct_fechaElaboracion) BETWEEN ' . $inicio . ' AND ' . $termino . '';
} else {
    $fechas = '';
}

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class Pdf_ResumenRectificaciones extends PDF
{
    public $ancho;
    public $imagen;
    public function Footer()
    {
        $this->SetY(-13);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function Header()
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->ancho = 190;
                $this->imagen = 235;
                break;
        }

        $this->SetMargins(10, 10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $ur_ent = '';
        $nivel = $_SESSION[_S]['nvj_id'];

        if ($_SESSION[_S]['nvj_id'] <= 2) {
            $gpo = $_SESSION[_S]['gpo_id'];
            $ur = $_SESSION[_S]['ur_id'];
            $ur_ent = $gpo . $ur;
        } else {
            $ur = $_SESSION[_S]['clasAdm-ur'];
            $gpo = $_SESSION[_S]['clasAdm-gpo'];
            $ur_ent = $gpo . $ur;
        }

        $inicio = DateToQuotedMySQLDate($_POST['inicio']);
        $termino = DateToQuotedMySQLDate($_POST['termino']);

        $miconexion = new AccesoDatos();
        $miconexion->conectar();
        $miconexion->setSql('
            SELECT
                ur_nombre
            FROM cat_UnidadResponsable
            WHERE ur_clave = :ur_clave ');
        $params = [
            'ur_clave' => $ur_ent,
        ];
        $miconexion->consultar($params);
        $miconexion->desconectar();
        while ($row = $miconexion->extraer()) {
            $nom_ur = $row['ur_nombre'];
        }

        $this->SetMargins(10, 10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->Image('../../../../img/' . LOGO_COLOR, $ix + 168, $iy - 2, '95', '25', 'png', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 4, $iy + 20, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $this->SetXY($ix + 4, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 6, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $this->Cell(180, 6, 'SECRETARIA DE FINANZAS - DIRECCION DE PRESUPUESTO ' . APP_EJERCICIO . '', 0, 1, 'L');

        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(180, 6, 'LISTADO DE RECTIFICACIONES DE LA FECHA ' . $inicio . ' A   ' . $termino, 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(15, 6, $gpo . $ur . ' - ' . utf8Decode($nom_ur), 0, 0, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetBorders(0);
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $tmp = $this->GetBorders(0);
        $ytmp = $this->GetY();

        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);

        if ($nivel > 2) {
            $this->ln(5);
            $this->Line(12, 35, 270, 35);
            $this->ln(1);
            $this->SetWidths([14, 14, 23, 23, 23, 74, 23, 23, 23]);
            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'R']);
            $this->SetFont('Arial', 'B', 7);
            $tmp = $this->GetBorders();
            $this->SetBorders(0);
            $this->SetAltoLinea(2);
            $this->Row(['RECT', 'CLC', 'TIPO RECT.', 'FECHA ELAB.', 'TIPO', utf8Decode('JUSTIFICACIÓN'), 'ESTADO', 'FECHA_APLIC.', 'IMPORTE']);
            $this->Line(12, 40, 270, 40);
            $this->ln(3);
        } else {
            $this->ln(5);
            $this->Line(12, 35, 270, 35);
            $this->ln(1);
            $this->SetWidths([20, 20, 23, 23, 23, 102, 23, 23]);
            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'R']);
            $this->SetFont('Arial', 'B', 7);
            $tmp = $this->GetBorders();
            $this->SetBorders(0);
            $this->SetAltoLinea(2);
            $this->Row(['RECT.', 'CLC', 'TIPO RECT.', 'FECHA ELAB.', 'TIPO', utf8Decode('JUSTIFICACIÓN'), 'FECHA_APLIC.', 'IMPORTE']);
            $this->Line(12, 40, 270, 40);
            $this->ln(3);
        }
    }
}

$miconexion = new AccesoDatos();
$miconexion->conectar();
$miconexion->setSql('
    SELECT
        gpo_id,
        ur_id,
        rct_id,
        clc_id,
        rct_justificacion,
        DATE_FORMAT( rct_fechaAutorizacion,"%d/%m/%Y") AS rct_fechaAutorizacion,
        DATE_FORMAT( rct_fechaElaboracion,"%d/%m/%Y") AS rct_fechaElaboracion,
        rct_percepcion AS percepciones,
        rct_retencion AS  retenciones,
        rct_percepcion-rct_retencion AS total,
        tcl_nombre,
        CASE
            WHEN a.ecl_id = 1 THEN "GENERADA POR LA UR"
            WHEN a.ecl_id = 2 THEN "AUTORIZADA POR LA UR"
            WHEN a.ecl_id = 3 THEN "ENVIADA A FINANZAS"
            WHEN a.ecl_id = 4 THEN "RECIBIDA POR FINANZAS"
            WHEN a.ecl_id = 5 THEN "REVISADA POR FINANZAS"
            WHEN a.ecl_id = 6 AND b.ecl_id = 18 AND b.clc_recursoFederal = 1 THEN "APLICADA POR LA UR "
            WHEN a.ecl_id=6 AND b.ecl_id=18 AND b.clc_recursoFederal=2 THEN "APLICADA POR FINANZAS"
        END AS ecl_nombre,
        -- CONCAT(rct_dia,rct_mes,rct_anio,rct_reintegroLote,rct_fechaLote) AS lote,
        if(rct_reintegro = 1,
            "CON REINTEGRO",
            "SIN REINTEGRO") AS reintegro
    FROM scp_MaestroRectificacion a
        INNER JOIN cat_EstadoClc using(ecl_id)
        INNER JOIN scp_Clc b USING(gpo_id,ur_id,clc_id)
        INNER JOIN enc_FiltroRectificacion f USING (gpo_id,ur_id,tcl_id,stc_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
    WHERE gpo_id = :gpo AND ur_id = :ur ' . $tcl_id . ' ' . $fechas . '
    GROUP BY gpo_id,ur_id,rct_id
    ORDER BY gpo_id,ur_id,rct_id
');

$parametros += [
    'gpo' => $gpo,
    'ur'  => $ur,
];
$miconexion->consultar($parametros);
$miconexion->desconectar();

$pdf = new Pdf_ResumenRectificaciones('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetBorders(0);
$pdf->ln(4);
$clc = 1;

while ($row = $miconexion->extraer()) {
    if ($nivel > 2) {
        $pdf->SetWidths([14, 14, 23, 23, 23, 74, 23, 23, 23]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'L', 'C', 'C', 'R']);
        $pdf->SetFont('Arial', '', 7);
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->SetAltoLinea(3);
        $pdf->Row([$row['rct_id'], $row['clc_id'], $row['reintegro'], $row['rct_fechaElaboracion'], utf8Decode($row['tcl_nombre']), utf8Decode($row['rct_justificacion']), utf8Decode($row['ecl_nombre']), $row['rct_fechaAutorizacion'], number_format((float) $row['total'], 2)]);
        $pdf->ln(2);
    } else {
        $pdf->SetWidths([20, 20, 23, 23, 23, 102, 23, 23]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'L', 'C', 'R']);
        $pdf->SetFont('Arial', '', 7);
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->SetAltoLinea(3);
        $pdf->Row([$row['rct_id'], $row['clc_id'], $row['reintegro'], $row['rct_fechaElaboracion'], utf8Decode($row['tcl_nombre']), utf8Decode($row['rct_justificacion']), $row['rct_fechaAutorizacion'], number_format((float) $row['total'], 2)]);
        $pdf->ln(2);
    }
}

$pdf->Output();

function fechan($fechavieja)
{
    $d = substr($fechavieja, 8, 2);
    $m = substr($fechavieja, 5, 2);
    $a = substr($fechavieja, 0, 4);
    return $d . '/' . $m . '/' . $a;
}

?>

<?php

function MySQLDateToDate($MySQLFecha)
{
    if (($MySQLFecha == '') or ($MySQLFecha == '0000-00-00')) {
        return '';
    } else {
        return date('d/m/Y', strtotime($MySQLFecha));
    }
}

function DateToQuotedMySQLDate($Fecha)
{
    if ($Fecha != '') {
        $trozos = explode('/', $Fecha, 3);
        return "'" . $trozos[2] . '-' . $trozos[1] . '-' . $trozos[0] . "'";
    } else {
        return 'NULL';
    }
}

?>
