<?php

/**
 * Description of Pdf_Poa11
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 31-Jul-2018
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de : RESUMEN DEL PRESUPUESTO POR UNIDAD RESPONSABLE - IDENTIFICADOR DE PARTIDA
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once __DIR__ . '/../../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
class Pdf_Poa11 extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public $nvj;
    public $adm_gpo;
    public $adm_ur;
    public $adm_ue;
    public UnidadResponsable $ur;
    public $estadoCosteo;
    public $ur_cierreForzado;
    public $opc;
    public $longitud;
    public $nombreUR;

    public function __construct()
    {
        parent::__construct();

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        //variables de session
        $this->gpo_id = (isset($_SESSION[_S]['gpo_id']) ? $_SESSION[_S]['gpo_id'] : '');
        $this->ur_id = (isset($_SESSION[_S]['ur_id']) ? $_SESSION[_S]['ur_id'] : '');
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '');
        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '');
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '');
        $this->adm_ue = (isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : '');

        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();
        $this->ur_cierreForzado = $this->ur->getUr_cierreForzado();
        $this->opc = $_POST['opc'];
    } // fin __construct

    public function Footer($lastPage = true)
    {
        $row = $this->getDatos();
        $this->SetY(-45);

        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
            SELECT ur_nombreAdministrativo,ur_nombreTitular,ur_cargoAdministrativo, ur_cargoTitular,ur_nombrePlaneacion,ur_cargoPlaneacion
            FROM aux_UnidadResponsable where gpo_id= ? and ur_id= ? ');
            $params = [$this->gpo_id, $this->ur_id];
            $conexion3->consultar($params);
            $conexion3->desconectar();
            $row = $conexion3->extraer();
        }

        if ($row['ur_nombrePlaneacion'] == '' && $row['ur_cargoPlaneacion'] == '') {

            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(50, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(50, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(50, 3, utf8Decode($row['ur_cargoPlaneacion']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $x = $this->getX();
            $this->multicell(50, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $this->multicell(50, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $x = 0;
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode('' . ' - ' . '' . ' '), 0, 'L');

        $this->Cell(0, 0, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 1, 0, 'R');
    } // fin Footer

    public function Header()
    { //Encabezado del documento
        $year = APP_EJERCICIO;
        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $this->pintarMarcasAguaPoa(
            $this->estadoCosteo,
            $this->ur_cierreForzado,
            $this->ur->getEur_id(),
            $this->opc ?? 1
        );

        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $logoAlto = $this->pintarLogoEncabezadoPoa($ix, $iy);

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 4, $iy + 17, $year, 90);
        $this->SetTextColor(0, 0, 0);

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 4, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $ytmp = $this->GetY();

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . $year, 0, 1, 'L');
        $this->SetXY($ix + 4, $ytmp + 4);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $ytmp = $this->GetY();

        $this->longitud = 68;
        $this->nombreUR = TruncateCadena(utf8Decode(trim('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), $this->longitud);

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 4, $ytmp + 8);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }
        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-11', 'RESUMEN PRESUPUESTAL POR IDENTIFICADOR DE PARTIDA', 194, $ytmp);

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);

        $this->setX(10);
        $this->SetWidths([30, 124, 39]);
        $this->SetAligns(['L', 'L', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setBorders('');
        $this->setAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['CONCEPTO', utf8Decode('DESCRIPCIÓN'), 'PRESUPUESTO  $']);
        $this->ln(1);
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);
    } //fin Footer
} //fin class

$pdf = new Pdf_Poa11('P', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$filtro = '';
$params = [];
if ($pdf->nvj > 1) {
    $filtro = ' WHERE gpo_id = ? and ur_id = ?';
    $params = [$pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur];
} else {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? and ue_id = ?';
    $params = [$pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue];
}

$sql = '
    SELECT "UR" es,ur_clave as clave,ur_nombre as nombre,gpo_id,ur_id,"" tg_id,"" idp_id,SUM(pre_original) AS pre_original,ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,1 as nivel
    FROM presupuesto
    INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
    INNER JOIN cat_IdentificadorPartida USING(idp_id)
    JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
    ' . $filtro . '
    GROUP BY gpo_id, ur_id
    HAVING pre_original > 0
    UNION ALL
        SELECT "TIPO GASTO" es,tg_id as clave,tg_nombre as nombre,gpo_id,ur_id,tg_id,"" idp_id,SUM(pre_original) AS pre_original,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,2 as nivel
        FROM presupuesto
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_IdentificadorPartida USING(idp_id)
        INNER JOIN cat_TipoGasto USING (tg_id)
        JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,tg_id
        HAVING pre_original > 0
    UNION ALL
        SELECT "IDP" es,idp_id as clave,idp_nombre as nombre,gpo_id,ur_id,tg_id,idp_id,SUM(pre_original) AS pre_original,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,3 as nivel
        FROM presupuesto
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_IdentificadorPartida USING(idp_id)
        INNER JOIN cat_TipoGasto USING (tg_id)
        JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,tg_id,idp_id
        HAVING pre_original > 0
        ORDER BY gpo_id,ur_id,tg_id,idp_id,nivel;
    ';

$datos = new AccesoDatos();
$datos->conectar();
$datos->setSql($sql);
$datos->consultar($params);
$datos->desconectar();
$row = $datos->extraer();
$pdf->setDatos($row);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$celda = 1;
$unidad = '';
$bolunidad = false;
$gasto = '';
$bolgasto = false;
$identificador = '';
$bolidentificador = false;
if ($row) {
    do {
        $pdf->setDatos($row);
        $pdf->setAltoLinea(1.5);

        if ($unidad != $row['clave']) {
            if ($bolunidad == true) {
                $$unidad = '';
                $bolunidad = false;
                $gasto = '';
                $bolgasto = false;
                $identificador = '';
                $bolidentificador = false;
            }
            if ($row['nivel'] == 1) {
                $pdf->CheckPageBreak(30);
                $pdf->ln(1);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(192, 192, 192);
                $pdf->SetWidths([40, 124, 30]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row(['', 'TOTAL UNIDAD RESPONSABLE', number_format($row['pre_original'], 2)]);
                $bolunidad = true;
                $unidad = $row['clave'];
            }
        }
        if ($gasto != $row['clave']) {
            if ($bolgasto == true) {
                $gasto = '';
                $bolgasto = false;
                $identificador = '';
                $bolidentificador = false;
            }
            if ($row['nivel'] == 2) {
                $pdf->CheckPageBreak(30);
                $pdf->ln(4);
                $pdf->setAltoLinea(2);
                $pdf->SetX(10);
                $pdf->setBorders('B');
                $pdf->setDrawColor(160, 160, 160);
                $pdf->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(235, 235, 235);
                $pdf->SetWidths([30, 134, 30]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row([$row['clave'], utf8Decode($row['nombre']), number_format($row['pre_original'], 2)]);
                $bolgasto = true;
                $gasto = $row['clave'];
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetRelleno('no');
                $pdf->setAltoLinea(1.5);
            }
        }
        if ($identificador != $row['clave']) {
            if ($bolidentificador == true) {
                $identificador = '';
                $bolidentificador = false;
            }
            if ($row['nivel'] == 3) {
                $pdf->CheckPageBreak(30);
                $pdf->ln(4);
                $pdf->SetX(10);
                $pdf->setBorders('B');
                $pdf->setDrawColor(160, 160, 160);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(250, 250, 250);
                $pdf->SetWidths([164, 30]);
                $pdf->SetAligns(['L', 'R']);
                $pdf->Row([utf8Decode($row['nombre']), number_format($row['pre_original'], 2)]);
                $bolidentificador = true;
                $identificador = $row['clave'];
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetRelleno('no');
                $pdf->setAltoLinea(1.5);
            }
        }
    } while ($row = $datos->extraer());
}
$pdf->Output();
