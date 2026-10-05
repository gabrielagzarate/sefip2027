<?php

/**
 * Description of Pdf_Poa15
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 04-ago-2020
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de : MODALIDAD DE CONTRATACIÓN - PARTIDA ESPECÍFICA
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
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';
class Pdf_Poa15 extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public $registros = 0;
    public $adm_ur;
    public $adm_ue;
    public UnidadResponsable $ur;
    public $estadoCosteo;
    public $ur_cierreForzado;
    public $opc;
    public $nvj;
    public $adm_gpo;
    public $nombreUR;
    public $longitud;

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
        $row = '';
        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
                SELECT ur_nombreAdministrativo,ur_nombreTitular,ur_cargoAdministrativo,
                        ur_cargoTitular,ur_nombrePlaneacion,ur_cargoPlaneacion,crh_id
                FROM cat_UnidadResponsable
                INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
                where gpo_id = ? and ur_id = ?');
            $params = [$this->adm_gpo, $this->adm_ur];
            $conexion3->consultar($params);
            $conexion3->desconectar();
            $row = $conexion3->extraer();
        }

        if ($row['crh_id'] == 1) {
            $this->SetY(-45);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('AUTORIZÓ'), 0, 0, 'C');
            $this->Cell(50, 2, 'Vo. Bo.', 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($row['ur_nombreTitular']), 0, 0, 'C');
            $this->Cell(50, 2, utf8Decode('M.A. URYEL BAUTISTA VÁSQUEZ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(57, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $x = $this->getX();
            $this->multicell(57, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $this->multicell(57, 3, utf8Decode('DIRECTOR DE RECURSOS HUMANOS DE LA SECRETARÍA DE ADMINISTRACIÓN'), 0, 'C');
        } else {
            if ($row['ur_nombrePlaneacion'] != '' && $row['ur_cargoPlaneacion'] != '') {
                $this->SetY(-45);
                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                $this->Cell(50, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
                $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
                $this->Cell(50, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

                $this->Cell(130, 2, '', 0, 1, 'C');
                $this->ln(1);
                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                $this->Cell(50, 2, utf8Decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
                $this->Cell(90, 2, utf8Decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
                $this->Cell(50, 2, utf8Decode($row['ur_nombreTitular']), 0, 1, 'C');

                $this->Cell(130, 2, '', 0, 1, 'C');
                $this->SetFont('UniviaPro-Bold', 'B', 6.5);
                $y = $this->getY();
                $x = $this->getX();
                $this->setX(10);
                $this->multicell(53, 3, utf8Decode($row['ur_cargoAdministrativo']), 0, 'C');
                $this->setY($y);
                $this->setX($x + 67);
                $x = $this->getX();
                $this->multicell(60, 3, utf8Decode($row['ur_cargoPlaneacion']), 0, 'C');
                $this->setY($y);
                $this->setX($x + 73);
                $this->multicell(53, 3, utf8Decode($row['ur_cargoTitular']), 0, 'C');
            } else {
                $this->SetY(-30);
                $this->SetFont('UniviaPro-Regular', '', 6.5);

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
            }
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode('' . ' - ' . '' . ' '), 0, 'L');

        $this->Cell(0, 0, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 1, 0, 'R');
    } //fin Footer

    public function Header()
    {
        $year = APP_EJERCICIO;
        $this->SetMargins(10, '', '');
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

        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-15', utf8Decode('MODALIDAD DE CONTRATACIÓN - PARTIDA ESPECÍFICA'), 194, $ytmp);

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);

        $this->SetFillColor(199, 209, 219);
        $this->setX(10);
        $this->SetWidths([25, 138, 30]);
        $this->SetAligns(['L', 'L', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->setBorders('');
        $this->setAltoLinea(3);
        $this->SetRelleno('si');
        $this->Row(['PARTIDA', 'CONCEPTO', 'PRESUPUESTO $']);
        $this->ln(1);
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
    } // fin Header
} //fin class

$pdf = new Pdf_Poa15('P', 'mm', 'Letter');
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
    $params = [$pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur];
} else {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? and ue_id = ?';
    $params = [$pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue];
}

$sql = '
        SELECT gpo_id,ur_id,cfi_clave as clave ,cfi_nombre as nombre ,cfi_clave,"" epz_id,"" rla_id,"" pg_id,"" pe_id,SUM(mon_ene+mon_feb+mon_mar+mon_abr+mon_may+mon_jun+mon_jul+mon_ago+mon_sep+mon_oct+mon_nov+mon_dic) AS pre_original,
            ur_nombre,ur_nombreTitular,ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,ur_rh,eur_id,1 nivel
        FROM poa_ServiciosPersonales
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cfi_clave,cfi_nombre
        HAVING pre_original > 0
    UNION ALL
    SELECT gpo_id,ur_id,epz_id as clave , CASE epz_id WHEN 1 THEN "ACTIVAS" WHEN 2 THEN "VACANTES" WHEN 3 THEN "EN LITIGIO" END as nombre ,cfi_clave,epz_id,"" rla_id,"" pg_id,"" pe_id,SUM(mon_ene+mon_feb+mon_mar+mon_abr+mon_may+mon_jun+mon_jul+mon_ago+mon_sep+mon_oct+mon_nov+mon_dic) AS pre_original,
            ur_nombre,ur_nombreTitular,ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,ur_rh,eur_id,2 nivel
        FROM poa_ServiciosPersonales
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cfi_clave,epz_id
        HAVING pre_original > 0
    UNION ALL
    SELECT gpo_id,ur_id,rla_id as clave ,rla_nombre as nombre ,cfi_clave,epz_id,rla_id,"" pg_id,"" pe_id,SUM(mon_ene+mon_feb+mon_mar+mon_abr+mon_may+mon_jun+mon_jul+mon_ago+mon_sep+mon_oct+mon_nov+mon_dic) AS pre_original,
            ur_nombre,ur_nombreTitular,ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,ur_rh,eur_id,3 nivel
        FROM poa_ServiciosPersonales
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cfi_clave,epz_id,rla_id
        HAVING pre_original > 0
    UNION ALL
        SELECT gpo_id,ur_id,pe_clave  as clave ,pe_nombre as nombre ,cfi_clave,epz_id,rla_id,pg_id,pe_id,SUM(mon_ene+mon_feb+mon_mar+mon_abr+mon_may+mon_jun+mon_jul+mon_ago+mon_sep+mon_oct+mon_nov+mon_dic) AS pre_original,
            ur_nombre,ur_nombreTitular,ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,ur_rh,eur_id,4 nivel
        FROM poa_ServiciosPersonales
        INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        INNER JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
        INNER JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
        INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,cfi_clave,epz_id,rla_id,pg_id,pe_id
        HAVING pre_original > 0
        ORDER BY gpo_id,ur_id,cfi_clave,epz_id,rla_id,pg_id,pe_id;
    ';
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);
$conexion->desconectar();
$row = $conexion->extraer();
$pdf->setDatos($row);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$celda = 1;
$totactivas = 0;
$totvacantes = 0;
$totalitigio = 0;
$totgeneral = 0;

$clave = '';
$bolclave = false;
$estadoplaza = '';
$bolestadoplaza = false;
$relacion = '';
$bolrelacion = false;
$partida = '';
$bolpartida = false;
$pintar = true;
$contador = 0;
if ($row) {
    do {
        $pdf->setAltoLinea(1.5);

        if ($clave != $row['clave']) {
            if ($bolclave == true) {
                $clave = '';
                $bolclave = false;
                $estadoplaza = '';
                $bolestadoplaza = false;
                $relacion = '';
                $bolrelacion = false;
                $partida = '';
                $bolpartida = false;
                $contador++;
            }
            if ($row['nivel'] == 1) {
                //Este cambio se hizo solo para la ur 412
                if ($pdf->adm_gpo == 4 && $pdf->adm_ur == 12 && $contador >= 1) {
                    $pdf->AddPage();
                }

                $pdf->ln(1);
                $pdf->SetRelleno('no');
                $pdf->CheckPageBreak(30);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->SetWidths([163, 30]);
                $pdf->SetAligns(['L', 'R']);
                $pdf->Row([$row['clave'] . '  -  ' . utf8Decode($row['nombre']), number_format($row['pre_original'], 2)]);
                $clave = $row['clave'];
                $bolclave = true;
                $pdf->ln(1);
            }
        }
        if ($estadoplaza != $row['clave']) {
            if ($bolestadoplaza == true) {
                $estadoplaza = '';
                $bolestadoplaza = false;
                $relacion = '';
                $bolrelacion = false;
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 2) {
                $pdf->CheckPageBreak(30);
                $pdf->SetRelleno('no');
                $pdf->SetFillColor(255, 255, 255);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Regular', '', 6);
                $pdf->SetWidths([193]);
                $pdf->SetAligns(['L']);
                $pdf->Row([utf8Decode($row['nombre'])]);
                $estadoplaza = $row['clave'];
                $bolestadoplaza = true;
                $pdf->ln(1);
            }
        }
        if ($relacion != $row['clave']) {
            if ($bolrelacion == true) {
                $relacion = '';
                $bolrelacion = false;
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 3) {
                $pdf->CheckPageBreak(30);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->setBorders('B');
                $pdf->setDrawColor(220, 220, 220);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->SetWidths([193]);
                $pdf->SetAligns(['L']);
                $pdf->Row([utf8Decode($row['nombre'])]);
                $relacion = $row['clave'];
                $bolrelacion = true;
                $pdf->ln(1);
            }
        }
        if ($partida != $row['clave']) {
            if ($bolpartida == true) {
                $partida = '';
                $bolpartida = false;
            }
            if ($row['nivel'] == 4) {
                $pdf->ln(1);
                $pdf->SetRelleno('no');
                $pdf->CheckPageBreak(30);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->setBorders('B');
                $pdf->setDrawColor(220, 220, 220);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 6);
                $pdf->SetWidths([25, 138, 30]);
                $pdf->SetAligns(['L', 'L', 'R']);
                $pdf->Row([str_pad($row['pg_id'], 3, '0', STR_PAD_LEFT) . ' - ' . str_pad($row['pe_id'], 3, '0', STR_PAD_LEFT), utf8Decode($row['nombre']), number_format($row['pre_original'], 2)]);
                $pdf->ln(1);
                $partida = $row['clave'];
                $bolpartida = true;
            }
        }
    } while ($row = $conexion->extraer());
}
$pdf->Output();
