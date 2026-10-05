<?php

/**
 * Description of Pdf_ResumenCLC
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 14-ene-2019
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se cambia encabezado a nuevo formato
 * Objective:  Generar Reporte de : RESUMEN DE CLC'S
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

session_start();
ini_set('memory_limit', '200M');
set_time_limit(0);

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../ent/Entidad.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';

$ur_ent = '';
$nivel = $_SESSION[_S]['nvj_id'];
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$todo = isset($_POST['SelectAll']) ? $_POST['SelectAll'] : '';
$parametrosC = [];

if ($nivel <= 2) {
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

if ($tipo !== '' && $tipo !== '0') {
    $tcl_id = 'AND tcl_id = "' . $tipo . '"';
} elseif ($todo !== '') {
    $tcl_id = '';
}

$fechas = '';
if ($inicio != '' and $termino != '') {
    $fechas = ' AND date(clc_fechaElaboracion) BETWEEN ' . $inicio . ' AND ' . $termino . '';
} else {
    $fechas = '';
}

//Extendiendo la clase pdf para escribir el encabezado y pie de página
class Pdf_ResumenCLC extends PDF
{
    public $ancho = '';
    public $imagen = '';
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
    }

    public function Footer()
    {
        $this->SetY(-13);
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
        $miconexion->setSql('SELECT ur_nombre FROM cat_UnidadResponsable where ur_clave= :ur_ent');
        $parametros = ['ur_ent' => $ur_ent];
        $miconexion->consultar($parametros);
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

        //@JORDY 24/04/2019 Ticket #904986 se cambia la estructura como estaba en 2018
        $this->Image('../../../../img/escudo.jpg', $ix, $iy - 1, '18', '25', 'jpg', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $this->SetXY($ix + 98, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 6, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $this->SetXY($ix + 81, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $this->Cell(180, 6, utf8Decode('SECRETARÍA DE FINANZAS - DIRECCIÓN DE EGRESOS ' . APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 80, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->Cell(180, 6, 'LISTADO DE CLCS DE LA FECHA ' . $inicio . ' A   ' . $termino, 0, 1, 'L');

        $this->SetXY($ix + 75, $ytmp + 4);
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
            $this->Line(8, 35, 276, 35);
            $this->ln(1);
            $this->SetWidths([7, 10, 7, 16, 19, 19, 32, 23, 38, 19, 15, 15, 15, 15, 15]);
            $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'L', 'L', 'L', 'R', 'R', 'R', 'R', 'R']);
            $this->SetFont('UniviaPro-Bold', 'B', 6);
            $tmp = $this->GetBorders();
            $this->SetBorders(0);
            $this->SetAltoLinea(2);
            $this->Row(['CLC', 'CereCo', 'No.', 'CVE. FIN.', 'FECHA', 'CLAB. INTERB.', 'TIPO', 'SUBTIPO', 'CONCEPTO', 'ESTADO', 'F.PAGO', 'AFE_PRES', 'P +', 'R +', 'IMPORTE']);
            $this->ln(3);
        } else {
            $this->ln(5);
            $this->Line(8, 35, 276, 35);
            $this->ln(1);
            $this->SetWidths([7, '', '', 20, 20, 35, 25, 65, 20, 18, 18, 18, 18]);
            $this->SetAligns(['C', '', '', 'C', 'C', 'C', 'C', 'C', 'C', 'R', 'R', 'R', 'R']);
            $this->SetFont('UniviaPro-Bold', 'B', 6);
            $tmp = $this->GetBorders();
            $this->SetBorders(0);
            $this->SetAltoLinea(2);
            $this->Row(['CLC', '', '', 'CVE. FIN.', 'FECHA', 'CLAB. INTERB.', 'TIPO', 'CONCEPTO', 'ESTADO', 'AFE_PRES', 'P +', 'R +', 'IMPORTE']);
            $this->ln(3);
        }
    }
}

$miconexion = new AccesoDatos();
$miconexion->conectar();

$miconexion->setSql('
    SELECT
        gpo_id,
        ur_id,clc_id,
        MAX(scf.cfi_clave) AS cfi_clave,
        bur_id,
        tcl_nombre,
        clc_concepto,
        DATE_FORMAT(clc_fechaElaboracion,"%Y/%m/%d") AS clc_fechaElaboracion,
        TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) AS importe,
        MAX(tes.lot_fechaPago) as lot_fechaPago,
        stc_nombre,
        clc_percepcion AS percepcion,
        clc_retencion AS retencion,
        IF(!ISNULL(MAX(sp.ncp_percepcionNeto)),
            SUM(sp.ncp_percepcionNeto),
            0.00) AS afe_presupuestal,
        ecl_nombre,
        if(clc_tipocereCo = 1 AND clc_generacionCereCo IS NOT NULL,
            "CERE",
            if( clc_tipocereCo = 2 AND clc_generacionCereCo IS NOT NULL,
                "CERECO",
                "")) AS cereco,
        if(clc_generacionCereCo IS  NOT  NULL,
            clc_cereCo,
            "") AS clc_cereCo,
        CASE
            WHEN ecl_id = 18 OR ecl_id = 17 THEN "PAGADA"
            WHEN ecl_id = 21 THEN "CANCELADA"
            WHEN ecl_id = 20 THEN "INHABILITADA"
            WHEN ecl_id BETWEEN 10 AND 16 THEN "ENVIADO A TESORERÍA"
            ELSE ecl_nombre
        END AS pagada
    FROM scp_Clc
        LEFT JOIN scp_ClcAuxiliar b USING (gpo_id,ur_id,clc_id,ecl_id)
        LEFT JOIN scp_Presupuestal sp USING(gpo_id, ur_id, clc_id,ecf_id)
        LEFT JOIN (
            SELECT
                MAX(l.lot_fechaPago) AS lot_fechaPago,
                gpo_id, ur_id, clc_id
            FROM ' . BD_TESORERIA . '.lot_Lote l
                INNER JOIN ' . BD_TESORERIA . '.lot_LoteClc lc USING ( fpa_id, msc_id, lot_ejercicio, lot_id )
            WHERE l.lot_ejercicio = ' . APP_EJERCICIO . '
            GROUP BY gpo_id, ur_id, clc_id
        )tes USING (gpo_id,ur_id,clc_id)
        INNER JOIN cat_TipoClc USING(tcl_id)
        INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
        INNER JOIN cat_EstadoClc USING (ecl_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' scf USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        LEFT JOIN cat_BancosUr USING(gpo_id,ur_id,bur_id)
    WHERE gpo_id = :gpo AND ur_id = :ur
        ' . $tcl_id . '
        ' . $fechas . '
        AND clc_id != 0
    #group by clc_id,scf.cfi_clave,tes.lot_fechaPago,sp.ncp_percepcionNeto
    group by clc_id
    order by clc_id;
');

$parametrosC += [
    'gpo' => $gpo,
    'ur'  => $ur,
];
$miconexion->consultar($parametrosC);
$miconexion->desconectar();

$pdf = new Pdf_ResumenCLC('L', 'mm', 'Letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetBorders(0);
$pdf->ln(2);
$clc = 1;

while ($row = $miconexion->extraer()) {
    if ($nivel > 2) {
        $pdf->SetWidths([7, 10, 7, 16, 19, 19, 32, 23, 38, 19, 15, 15, 15, 15, 15]);
        $pdf->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'L', 'L', 'L', 'R', 'R', 'R', 'R', 'R']);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->SetAltoLinea(3);
        $pdf->Row([$row['clc_id'], $row['cereco'], $row['clc_cereCo'], $row['cfi_clave'], fechan($row['clc_fechaElaboracion']), $row['bur_id'], utf8Decode($row['tcl_nombre']), utf8Decode($row['stc_nombre']), utf8Decode($row['clc_concepto']), utf8Decode($row['pagada']), $row['lot_fechaPago'], number_format($row['afe_presupuestal'], 2), number_format($row['percepcion'], 2), number_format($row['retencion'], 2), number_format($row['importe'], 2)]);
        $pdf->ln(2);
    } else {

        $pdf->SetWidths([7, '', '', 20, 20, 35, 25, 65, 20, 18, 18, 18, 18]);
        $pdf->SetAligns(['C', '', '', 'C', 'C', 'C', 'C', 'C', 'C', 'R', 'R', 'R', 'R']);
        $pdf->SetFont('UniviaPro-Regular', '', 6);
        $tmp = $pdf->GetBorders();
        $pdf->SetBorders(0);
        $pdf->SetAltoLinea(3);
        $pdf->Row([$row['clc_id'], '', '', $row['cfi_clave'], fechan($row['clc_fechaElaboracion']), $row['bur_id'], utf8Decode($row['tcl_nombre']), utf8Decode($row['clc_concepto']), $row['pagada'], number_format($row['afe_presupuestal'], 2), number_format($row['percepcion'], 2), number_format($row['retencion'], 2), number_format($row['importe'], 2)]);
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
