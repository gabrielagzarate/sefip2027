<?php

/**
 * Description of Pdf_Poa23
 *
 * @author ?
 * @update Vargas Gomez, Roberto
 * Created: 20-ago-2018
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de : RESUMEN DE PRESUPUESTO UNIDAD RESPONSABLE - CAPÍTULO - IDENTIFICADOR DE PARTIDA - PARTIDA
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
class Pdf_Poa23 extends PDF
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
    } //fin __construct

    public function Footer()
    {
        $row = $this->getDatos();
        $this->SetY(-23);

        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
            SELECT ur_nombreAdministrativo,ur_nombreTitular,ur_cargoAdministrativo,ur_cargoTitular,ur_nombrePlaneacion,	ur_cargoPlaneacion
            FROM aux_UnidadResponsable where  gpo_id = ? and ur_id = ? ');
            $params = [$this->adm_gpo, $this->adm_ur];
            $conexion3->consultar($params);
            $conexion3->desconectar();
            $row = $conexion3->extraer();
        }

        if ($row['ur_nombrePlaneacion'] == '' && $row['ur_cargoPlaneacion'] == '') {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(96, 2, utf8Decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(260, 2, utf8Decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 2, utf8Decode('ELABORO'), 0, 0, 'C');
            $this->Cell(110, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(70, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(70, 2, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(110, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(70, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(70, 3, utf8Decode($row['ur_cargoPlaneacion']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 90);
            $x = $this->getX();
            $this->multicell(70, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 90);
            $this->multicell(70, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
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
    } //fin Footer

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
        $anchoContenido = $this->getAnchoEncabezadoPoa(280);

        $logoAlto = $this->pintarLogoEncabezadoPoa($ix, $iy, $anchoContenido);

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
        $celdaUr = $anchoContenido - 31;

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell($celdaUr, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 4, $ytmp + 4);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0, 'L');
            $this->Cell($celdaUr, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 4, $ytmp + 8);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
            $this->Cell($celdaUr, 5, '', 0, 1, 'R');
        }
        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-23', utf8Decode('UR - CAPÍTULO - IDENTIFICADOR DE PARTIDA - PARTIDA'), $anchoContenido, $ytmp);

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 10 + $anchoContenido, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);

        $this->SetFillColor(199, 209, 219);
        $this->setX(10);
        $this->SetWidths([40, 200, 40]);
        $this->SetAligns(['L', 'L', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setBorders('');
        $this->setAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['CONCEPTO', utf8Decode('DESCRIPCIÓN'), 'PRESUPUESTO  $']);
        $this->ln(1);
        $this->setBorders(0);
        $this->SetRelleno('no');
    } //fin Header
} //fin class

$pdf = new Pdf_Poa23('L', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$filtro = '';
$params = [];
if ($pdf->nvj > 1) {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? ';
    $params = [$pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur];
} else {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? and ue_id = ? ';
    $params = [$pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue];
}

$sql = '
    SELECT "UR" es,ur_clave as clave,ur_nombre as  nombre,gpo_id,ur_id,"" cap_id,"" idp_id,"" pg_id,"" pe_id,SUM(pre_original) AS total,
        SUM(ori_ene) AS ori_ene,SUM(ori_feb) AS ori_feb,SUM(ori_mar) AS ori_mar,SUM(ori_abr) AS ori_abr,SUM(ori_may) AS ori_may,SUM(ori_jun) AS ori_jun,
        SUM(ori_jul) AS ori_jul,SUM(ori_ago) AS ori_ago,SUM(ori_sep) AS ori_sep,SUM(ori_oct) AS ori_oct,SUM(ori_nov) AS ori_nov,SUM(ori_dic) AS ori_dic,
        ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,1 as nivel
    FROM presupuesto
    INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
    INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
    INNER JOIN cat_PartidaGenerica using(pg_id)
    INNER JOIN cat_PartidaEspecifica  using (pg_id,pe_id)
    JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
    ' . $filtro . '
    GROUP BY gpo_id,ur_id
    HAVING total > 0
    UNION ALL
        SELECT "CAPITULO" es,cap_id as clave,cap_nombre as  nombre,gpo_id,ur_id,cap_id,"" idp_id,"" pg_id,"" pe_id,SUM(pre_original) AS total,
            SUM(ori_ene) AS ori_ene,SUM(ori_feb) AS ori_feb,SUM(ori_mar) AS ori_mar,SUM(ori_abr) AS ori_abr,SUM(ori_may) AS ori_may,SUM(ori_jun) AS ori_jun,
            SUM(ori_jul) AS ori_jul,SUM(ori_ago) AS ori_ago,SUM(ori_sep) AS ori_sep,SUM(ori_oct) AS ori_oct,SUM(ori_nov) AS ori_nov,SUM(ori_dic) AS ori_dic,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,2 as nivel
        FROM presupuesto
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_PartidaGenerica using(pg_id)
        INNER JOIN cat_PartidaEspecifica  using (pg_id,pe_id)
        INNER JOIN cat_Concepto  using (cto_id,cap_id)
        INNER JOIN cat_Capitulo USING(cap_id)
        JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cap_id
        HAVING total > 0
    UNION ALL
        SELECT "IDP - " es,idp_id  clave,idp_nombre as  nombre,gpo_id,ur_id,cap_id,idp_id,"" pg_id,"" pe_id,SUM(pre_original) AS total,
            SUM(ori_ene) AS ori_ene,SUM(ori_feb) AS ori_feb,SUM(ori_mar) AS ori_mar,SUM(ori_abr) AS ori_abr,SUM(ori_may) AS ori_may,SUM(ori_jun) AS ori_jun,
            SUM(ori_jul) AS ori_jul,SUM(ori_ago) AS ori_ago,SUM(ori_sep) AS ori_sep,SUM(ori_oct) AS ori_oct,SUM(ori_nov) AS ori_nov,SUM(ori_dic) AS ori_dic,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,3 as nivel
        FROM presupuesto
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_PartidaGenerica using(pg_id)
        INNER JOIN cat_PartidaEspecifica  using (pg_id,pe_id)
        INNER JOIN cat_Concepto  using (cto_id,cap_id)
        INNER JOIN cat_Capitulo USING(cap_id)
        INNER JOIN cat_IdentificadorPartida USING(idp_id)
        JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cap_id,idp_id
        HAVING total > 0
    UNION ALL
        SELECT "PARTIDA" es,pe_clave as clave,pe_nombre as  nombre,gpo_id,ur_id,cap_id,idp_id,lpad(pg_id,3,0) as pg_id,lpad(pe_id,3,0) as pe_id,SUM(pre_original) AS total,
            SUM(ori_ene) AS ori_ene,SUM(ori_feb) AS ori_feb,SUM(ori_mar) AS ori_mar,SUM(ori_abr) AS ori_abr,SUM(ori_may) AS ori_may,SUM(ori_jun) AS ori_jun,
            SUM(ori_jul) AS ori_jul,SUM(ori_ago) AS ori_ago,SUM(ori_sep) AS ori_sep,SUM(ori_oct) AS ori_oct,SUM(ori_nov) AS ori_nov,SUM(ori_dic) AS ori_dic,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,4 as nivel
        FROM presupuesto
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_PartidaGenerica using(pg_id)
        INNER JOIN cat_PartidaEspecifica  using (pg_id,pe_id)
        INNER JOIN cat_Concepto  using (cto_id,cap_id)
        INNER JOIN cat_Capitulo USING(cap_id)
        INNER JOIN cat_IdentificadorPartida USING(idp_id)
        JOIN presupuestoOriginal USING(gpo_id , ur_id , ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cap_id,idp_id,pg_id,pe_id,pe_clave,pe_nombre
        HAVING total > 0
        ORDER BY gpo_id,ur_id,cap_id,idp_id,lpad(pg_id,3,0),lpad(pe_id,3,0),nivel;
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
$unidad = '';
$bolunidad = false;
$capitulo = '';
$bolcapitulo = false;
$gasto = '';
$bolgasto = false;
$partida = '';
$bolpartida = false;
$pintaEncabezado = true;

if ($row) {
    do {
        $pdf->setDatos($row);
        $pdf->setAltoLinea(1.5);

        if ($unidad != $row['clave']) {
            if ($bolunidad == true) {
                $unidad = '';
                $bolunidad = false;
                $capitulo = '';
                $bolcapitulo = false;
                $gasto = '';
                $bolgasto = false;
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 1) {
                $pdf->CheckPageBreak(20);
                $pdf->ln(1);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(192, 192, 192);
                $pdf->SetWidths([40, 200, 40]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row(['', 'TOTAL UNIDAD RESPONSABLE', number_format($row['total'], 2)]);
                $bolunidad = true;
                $unidad = $row['clave'];
            }
        }
        if ($capitulo != $row['clave']) {
            if ($bolcapitulo == true) {
                $pintaEncabezado = true;
                $capitulo = '';
                $bolcapitulo = false;
                $gasto = '';
                $bolgasto = false;
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 2) {
                $pdf->CheckPageBreak(20);
                $pdf->ln(4);
                $pdf->setAltoLinea(3);
                $pdf->SetX(10);
                $pdf->setBorders('B');
                $pdf->setDrawColor(160, 160, 160);
                $pdf->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                $pdf->SetWidths([40, 200, 40]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row([$row['es'] . '  ' . $row['clave'], utf8Decode($row['nombre']), number_format($row['total'], 2)]);
                $bolcapitulo = true;
                $capitulo = $row['clave'];
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetRelleno('no');
                $pdf->setAltoLinea(1.5);
            }
        }
        if ($gasto != $row['clave']) {
            if ($bolgasto == true) {
                $pintaEncabezado = true;
                $gasto = '';
                $bolgasto = false;
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 3) {
                $pdf->CheckPageBreak(20);
                $pdf->setBorders(0);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(192, 192, 192);
                $pdf->SetWidths([40, 200, 40]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row([$row['es'] . ' ' . $row['clave'], utf8Decode($row['nombre']), number_format($row['total'], 2)]);
                $bolgasto = true;
                $gasto = $row['clave'];
            }
        }
        if ($partida != $row['clave']) {
            if ($bolpartida == true) {
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 4) {
                if ($pintaEncabezado) {
                    $pdf->ln(2);
                    $pdf->CheckPageBreak(20);
                    $pdf->setX(10);
                    $pdf->setBorders(0);
                    $pdf->SetRelleno('si');
                    $pdf->SetFillColor(199, 209, 219);
                    $pdf->RoundedRect(10, $pdf->GetY() - .5, 280, 4, 1, 'DF', '1234');
                    $pdf->SetWidths([46, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18]);
                    $pdf->SetAligns(['L', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);
                    $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                    $pdf->Row(['PARTIDA - CONCEPTO', 'TOTAL', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC']);
                    $pdf->setBorders(0);
                    $pdf->SetRelleno('no');
                    $pdf->SetFillColor(255, 255, 255);
                    $pdf->ln(2);
                }
                $pintaEncabezado = false;
                $pdf->CheckPageBreak(20);
                $pdf->setX(10);
                $pdf->SetRelleno('no');
                $pdf->setBorders('B');
                $pdf->setDrawColor(240, 240, 240);
                $pdf->setAltoLinea(2.5);
                $pdf->SetFont('UniviaPro-Regular', '', 5);
                $pdf->SetWidths([46, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18, 18]);
                $pdf->SetAligns(['L', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);
                $pdf->Row([$row['pg_id'] . ' - ' . $row['pe_id'] . '  ' . utf8Decode($row['nombre']), number_format($row['total'], 2), number_format($row['ori_ene'], 2), number_format($row['ori_feb'], 2), number_format($row['ori_mar'], 2), number_format($row['ori_abr'], 2), number_format($row['ori_may'], 2), number_format($row['ori_jun'], 2), number_format($row['ori_jul'], 2), number_format($row['ori_ago'], 2), number_format($row['ori_sep'], 2), number_format($row['ori_oct'], 2), number_format($row['ori_nov'], 2), number_format($row['ori_dic'], 2)]);
                $partida = $row['clave'];
                $bolpartida = true;
                $pdf->setBorders(0);
                $pdf->SetFillColor(255, 255, 255);
            }
        }
    } while ($row = $datos->extraer());
}
$pdf->Output();
