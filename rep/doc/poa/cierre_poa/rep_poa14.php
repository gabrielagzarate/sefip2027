<?php

session_start();
/**
 * Description of Pdf_Poa14
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
 * Objective:  Generar Reporte de : DESPLIEGUE POR RELACIÓN LABORAL
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */

ini_set('memory_limit', '20M');
set_time_limit(0);
require_once __DIR__ . '/../../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
require_once __DIR__ . '/../../../../lib/helpers.php';
class Pdf_Poa14 extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
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
        $row = $this->getDatos();
        $this->SetY(-45);

        if ($row == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
                SELECT ur_nombreAdministrativo,ur_nombreTitular,ur_cargoAdministrativo,
                        ur_cargoTitular,ur_nombrePlaneacion,ur_cargoPlaneacion
                FROM aux_UnidadResponsable where gpo_id =' . $this->adm_gpo . ' and ur_id=' . $this->adm_ur . ' ');
            $conexion3->consultar();
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
        $this->SetXY($ix + 4, $ytmp + 3.5);
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

        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-14', utf8Decode('DESPLIEGUE POR RELACIÓN LABORAL'), 194, $ytmp);
        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 203, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);

        if ($this->PageNo() != 1) {
            $this->SetFillColor(199, 209, 219);
            $this->setX(10);
            $this->SetWidths([33, 80, 20, 20, 20, 20]);
            $this->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setBorders('');
            $this->setAltoLinea(3);
            $this->SetRelleno('si');
            $this->Row(['NIVEL', utf8Decode('DESCRIPCIÓN'), 'ACTIVO', 'VACANTE', 'LITIGIO', 'TOTAL']);
            $this->ln(1);
            $this->setBorders(0);
            $this->SetFillColor(255, 255, 255);
            $this->SetRelleno('no');
        }
    } //fin Header
} // fin class

$pdf = new Pdf_Poa14('P', 'mm', 'Letter');
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
    $params = [$pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur];
} else {
    $filtro = ' WHERE gpo_id = ? and ur_id= ? and ue_id = ?';
    $params = [$pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue];
}

$sql = '
        SELECT "rla"es,rla_id as clave,rla_nombre nombre,gpo_id,ur_id,rla_id,""nrl_id,"" nrl_clave,"" nrl_descripcion,sum(inp_activas) as inp_activas,sum(inp_vacantes) as inp_vacantes,sum(inp_litigio) as inp_litigio,sum(inp_activas)+ sum(inp_vacantes)+sum(inp_litigio) AS total,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular,ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,1 as nivel
        FROM cat_NivelRelacionLaboral
        INNER JOIN enc_TabuladorUr USING(tab_id)
        INNER JOIN enc_NivelRelacionLaboral USING(rla_id,tab_id,nrl_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN cat_InventarioPlazas USING(gpo_id,ur_id,enr_id)
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,rla_id
        HAVING total > 0
    UNION ALL
        SELECT "nrl"es,nrl_id as clave,nrl_descripcion nombre,gpo_id,ur_id,rla_id, nrl_id,nrl_clave,nrl_descripcion,sum(inp_activas) as inp_activas,sum(inp_vacantes) as inp_vacantes,sum(inp_litigio) as inp_litigio,sum(inp_activas)+ sum(inp_vacantes)+sum(inp_litigio) AS total,
            ur_nombre,ur_nombreTitular, ur_nombreAdministrativo,ur_cargoTitular,ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,eur_id,2 as nivel
        FROM cat_NivelRelacionLaboral
        INNER JOIN enc_TabuladorUr USING(tab_id)
        INNER JOIN enc_NivelRelacionLaboral USING(rla_id,tab_id,nrl_id)
        INNER JOIN cat_RelacionLaboral USING(rla_id)
        INNER JOIN cat_InventarioPlazas USING(gpo_id,ur_id,enr_id)
        INNER JOIN cat_UnidadResponsable using(gpo_id,ur_id)
        INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
        ' . $filtro . '
        GROUP BY gpo_id,ur_id,rla_id,nrl_id,nivel, nrl_descripcion, nrl_clave
        HAVING total > 0
        ORDER BY gpo_id,ur_id,rla_id,nrl_id,nivel;
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
$relacion = '';
$nivel = '';
$bolrelacion = false;
$bolnivel = false;
$pintar = true;
if ($row) {
    do {
        if ($relacion != $row['clave']) {
            if ($bolrelacion == true) {
                $relacion = '';
                $bolrelacion = false;
                $nivel = '';
                $bolnivel = false;
                $pintar = true;
            }
            if ($row['nivel'] == 1) {
                $pdf->ln(2);
                $pdf->CheckPageBreak(30);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->setBorders('B');
                $pdf->setDrawColor(220, 220, 220);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Bold', 'B', 7);
                $pdf->SetWidths([33, 80, 20, 20, 20, 20]);
                $pdf->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
                $pdf->Row([utf8Decode($row['nrl_clave']), utf8Decode($row['nombre']), number_format($row['inp_activas'], 0), number_format($row['inp_vacantes'], 0), number_format($row['inp_litigio'], 0), number_format($row['total'], 0)]);
                $relacion = $row['clave'];
                $bolrelacion = true;
                $pdf->ln(2);
            }
        }
        if ($nivel != $row['clave']) {
            if ($bolnivel == true) {
                $nivel = '';
                $bolnivel = false;
            }

            if ($row['nivel'] == 2) {
                if ($pintar == true) {
                    $pdf->SetFillColor(199, 209, 219);
                    $pdf->setX(10);
                    $pdf->SetWidths([33, 80, 20, 20, 20, 20]);
                    $pdf->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
                    $pdf->SetFont('UniviaPro-Bold', 'B', 7);
                    $pdf->setBorders('');
                    $pdf->setAltoLinea(3);
                    $pdf->SetRelleno('si');
                    $pdf->Row(['NIVEL', utf8Decode('DESCRIPCIÓN'), 'ACTIVO', 'VACANTE', 'LITIGIO', 'TOTAL']);
                    $pdf->ln(1);
                    $pdf->setBorders(0);
                    $pdf->SetFillColor(255, 255, 255);
                    $pintar = false;
                    $pdf->SetRelleno('no');
                }

                $pdf->SetRelleno('no');
                $pdf->CheckPageBreak(30);
                $pdf->SetFillColor(255, 255, 255);
                $pdf->setBorders('B');
                $pdf->setDrawColor(220, 220, 220);
                $pdf->SetX(10);
                $pdf->SetFont('UniviaPro-Regular', '', 6);
                $pdf->SetWidths([33, 80, 20, 20, 20, 20]);
                $pdf->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
                $pdf->Row([utf8Decode($row['nrl_clave']), utf8Decode($row['nombre']), $row['inp_activas'], $row['inp_vacantes'], $row['inp_litigio'], $row['total']]);
                $nivel = $row['clave'];
                $bolnivel = true;
            }
        }
    } while ($row = $conexion->extraer());
}
$pdf->Output();
