<?php

//Inicio de sesion y llamda a librerias
session_start();
ini_set('memory_limit', '20M');
set_time_limit(0);
require_once '../../../../rep/lib/reportetabla.1.8.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../ent/Usuario.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/helpers.php';

class reportetabla extends PDF
{
    private $usr_id;
    private $longitud;
    private $ancho;
    private $imagen;

    public function __construct() //Encabezado del documento
    {
        parent::__construct();
        $this->usr_id = new Usuario();
        $this->usr_id->setUsr_id($_SESSION[_S]['usr_id']);
        $this->usr_id->select();
    }

    public function Header()
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho = 123;
                $this->imagen = 168;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 190;
                $this->imagen = 235;
                break;
        } //fin switch

        $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
        $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
        $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
        $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';
        $mod_tipoMovimiento = (isset($_POST['mod_tipoMovimiento']) && $_POST['mod_tipoMovimiento'] != '') ? $_POST['mod_tipoMovimiento'] : '';

        $objUnidadResponsable = new UnidadResponsable();
        $objUnidadResponsable->setGpo_id($gpo_id);
        $objUnidadResponsable->setUr_id($ur_id);
        $objUnidadResponsable->select();

        $datos = $this->getDatos();
        $this->SetMargins(10, '', '', '');
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        $ix = $this->GetX();
        $iy = $this->GetY();
        $iyt = $this->GetY();

        $this->setRouteImg2('escudo2');

        $this->Image($this->getRouteImg(), $ix + 164, $iy - 1, '85', '18', 'JPG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();

        $this->Cell(195, 5, utf8Decode('GOBIERNO DEL ESTADO DE OAXACA'), 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, utf8Decode('CONTROL PRESUPUESTAL ' . APP_EJERCICIO), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(10, 5, $objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id() . ' - ' . utf8Decode($objUnidadResponsable->getNombre()), 0, 0, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 2;
        $this->Cell(180, 5, utf8Decode('SOLICITUD DE ADECUACIÓN PRESUPUESTARIA'), 0, 1, 'L');

        $this->SetY($iy + 3 + $iyt);
        $this->SetX($ix + 7);

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(17);

        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->SetWidths([185]);
        $this->SetAligns(['L']);
        $this->SetBorders(0);
        $this->Row([utf8Decode('FOLIO : ' . str_pad($mod_id, 6, '0', STR_PAD_LEFT))]);

        $this->Ln();
        $this->SetXY($ix, $ytmp + 7);
        $ytmp = $this->GetY();
        $this->SetX($ix + 7);
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $datosCveFin = new AccesoDatos();
        $datosCveFin->conectar();
        $datosCveFin->setSql(
            '
            SELECT cfi_clave,cfi_nombre
            FROM enc_ClaveFinanciamiento
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE ecf_id= ?'
        );

        $datosCveFin->consultar([$datos['clavefin']]);
        $datosCveFin->desconectar();
        $cvefin = $datosCveFin->extraer();

        $this->SetXY($ix + 7, $iy + 31);
        $this->Cell(5, 5, $cvefin['cfi_clave'] . ' - ' . utf8Decode($cvefin['cfi_nombre']), 0, 1, 'L');

        $this->SetXY($ix - 70, $ytmp + 3);
        $this->Cell(34, 5, utf8Decode($datos['tmd_nombre']), 0, 1, 'C');

        $this->CheckPageBreak(25);
        $this->SetFont('UniviaPro-Regular', '', 7);
        $this->SetWidths([60, 70, 40, 20]);
        $this->SetAligns(['L', 'L', 'L', 'L']);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);

        $this->SetWidths([$this->ancho - 145, 8, 25, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(2.5);
        $this->SetRelleno('si');
        if ($datos['mov_id'] != 'C') {
            $this->Row(['CLAVE PRESUPUESTAL', 'TIPO', 'MODIFICADO IMPORTE RESULTADO', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC']);
        } else {
            $this->Row(['CLAVE PRESUPUESTAL', 'TIPO', 'MODIFICADO IMPORTE', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC']);
        }

        $this->ln(1);
        $this->SetAltoLinea(2);
        $this->SetRelleno('no');
    }

    public function Footer($lastPage = true) //Encabezado del documento
    {
        $datos = $this->getDatos();
        $this->SetY(-40);
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Número de página
        if ($lastPage) {

            switch ($_SESSION[_S]['nvj_id']) {
                case 1:case 2: //Ur y Ue

                    break;
                default: //SEFIN

                    $objUnidadResponsable = new UnidadResponsable();
                    $objUnidadResponsable->setGpo_id($_POST['mod_gpo_id']);
                    $objUnidadResponsable->setUr_id($_POST['mod_ur_id']);
                    $objUnidadResponsable->select();

                    $omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
                    $gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
                    $ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
                    $mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';

                    $nom_analista = '';
                    $cargo_analista = '';
                    $nom_departamento = '';
                    $cargo_departamento = '';
                    $nom_unidad = '';
                    $cargo_unidad = '';
                    $nom_director = '';
                    $cargo_director = '';

                    $omo_id = isset($datos['omo_id']) ? $datos['omo_id'] : '';
                    $mod_id = isset($datos['mod_id']) ? $datos['mod_id'] : 0;

                    if ($omo_id != '' || $mod_id != 0) {
                        //Obtiene las firmas
                        $sql = "
                            SELECT mod_cargo4,mod_firma4,mod_cargo5,mod_firma5,mod_cargo6,mod_firma6,
                            mod_cargo7,mod_firma7
                            FROM scp_ModificacionFirma
                            WHERE omo_id='" . $datos['omo_id'] . "' and mod_gpo_id=" .
                            $objUnidadResponsable->getGpo_id() .
                            ' and mod_ur_id=' . $objUnidadResponsable->getUr_id() .
                                ' and mod_id=' . $datos['mod_id'] . ';
                        ';

                        $firmasReportes = new AccesoDatos();
                        $firmasReportes->conectar();
                        $firmasReportes->setSql($sql);
                        $firmasReportes->consultar();
                        $datosFirma = $firmasReportes->extraer();

                        if ($firmasReportes->getNumeroRegistros() > 0) {
                            $nom_analista = $datosFirma['mod_firma4'];
                            $cargo_analista = $datosFirma['mod_cargo4'];
                            $nom_departamento = $datosFirma['mod_firma5'];
                            $cargo_departamento = $datosFirma['mod_cargo5'];
                            $nom_unidad = $datosFirma['mod_firma6'];
                            $cargo_unidad = $datosFirma['mod_cargo6'];
                            $nom_director = $datosFirma['mod_firma7'];
                            $cargo_director = $datosFirma['mod_cargo7'];
                        }
                    }

                    switch ($datos['emo_id']) {

                        case 14:
                            switch ($datos['tfm_id']) {
                                case 'A':case 'B': //Servicios personales y Gastos de operacion
                                    switch ($objUnidadResponsable->getGpo_id() . $objUnidadResponsable->getUr_id()) {
                                        case 530:
                                        case 531:
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($cargo_analista),
                                                utf8Decode($cargo_departamento),
                                                utf8Decode($cargo_unidad),
                                                utf8Decode($cargo_director),
                                            ]);

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($nom_analista),
                                                utf8Decode($nom_departamento),
                                                utf8Decode($nom_unidad),
                                                utf8Decode($nom_director),
                                            ]);
                                            break;
                                        case 105:
                                        case 540:
                                        case 553:
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($cargo_analista),
                                                utf8Decode($cargo_departamento),
                                                utf8Decode($cargo_unidad),
                                                utf8Decode($cargo_director),
                                            ]);

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($nom_analista),
                                                utf8Decode($nom_departamento),
                                                utf8Decode($nom_unidad),
                                                utf8Decode($nom_director),
                                            ]);
                                            break;
                                        default:
                                            $this->SetFont('Arial', 'B', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($cargo_analista),
                                                utf8Decode($cargo_departamento),
                                                utf8Decode($cargo_unidad),
                                                utf8Decode($cargo_director),
                                            ]);

                                            $this->SetFont('Arial', '', 7);
                                            $this->SetWidths([67, 67, 67, 67]);
                                            $this->SetAligns(['C', 'C', 'C', 'C']);
                                            $this->Row([
                                                utf8Decode($nom_analista),
                                                utf8Decode($nom_departamento),
                                                utf8Decode($nom_unidad),
                                                utf8Decode($nom_director),
                                            ]);
                                            break;
                                    }
                                    break;
                                case 'C': //Obra publica solamente planeacion.
                                    if ($_SESSION[_S]['s24-ssc'] == 2) {
                                        $this->SetXY(10, -20);
                                        $this->SetFont('Arial', 'B', 7);
                                        $this->Cell(100.5, 2, utf8Decode($cargo_analista), 0, 1, 'C', 0);

                                        $this->SetXY(97, -20);
                                        $this->Cell(92.5, 2, utf8Decode($cargo_departamento), 0, 1, 'C', 0);

                                        $this->SetXY(178, -20);
                                        $this->Cell(84.5, 2, utf8Decode($cargo_unidad), 0, 1, 'C', 0);

                                        $this->SetFont('Arial', '', 7);
                                        $this->SetXY(30, -13);
                                        $this->MultiCell(63.5, 4, utf8Decode($nom_analista), 0, 'C');

                                        $this->SetXY(93, -13);
                                        $this->MultiCell(100.5, 4, utf8Decode($nom_departamento), 0, 'C');

                                        $this->SetXY(170, -13);
                                        $this->MultiCell(100.5, 4, utf8Decode($nom_unidad), 0, 'C');
                                    } else {

                                        $this->SetFont('Arial', 'B', 7);
                                        $this->SetWidths([67, 67, 67, 67]);
                                        $this->SetAligns(['C', 'C', 'C', 'C']);
                                        $this->Row([
                                            utf8Decode($cargo_analista),
                                            utf8Decode($cargo_departamento),
                                            utf8Decode($cargo_unidad),
                                            utf8Decode($cargo_director),
                                        ]);

                                        $this->SetFont('Arial', '', 7);
                                        $this->SetWidths([67, 67, 67, 67]);
                                        $this->SetAligns(['C', 'C', 'C', 'C']);
                                        $this->Row([
                                            utf8Decode($nom_analista),
                                            utf8Decode($nom_departamento),
                                            utf8Decode($nom_unidad),
                                            utf8Decode($nom_director),
                                        ]);
                                    }

                                    break;
                            }
                            break;
                        default:

                            break;
                    }
                    break;
            }
        } else {
            $this->SetY(-20);
            $this->SetFont('Arial', 'B', 6);
            $this->Cell(10, 2, utf8Decode('CONTINUA...'), 0, 1, 'L');

            switch ($_SESSION[_S]['nvj_id']) {
                case 1:case 2: //Ur y Ue

                    break;
                default: //SEFIN
                    if ($datos['omo_id'] == 'A') {
                        $this->Cell(252, 2, utf8Decode('Rúbricas:________________________, ________________________, ________________________'), 0, 1, 'R');
                    }
                    break;
            }
        }
        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = 0;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX(4);
        date_default_timezone_set('America/Mexico_City');
        if (isset($_POST['reimprimir']) && $_POST['reimprimir'] != '') {
            $fechaRe = $_POST['reimprimir'];
        } else {
            $fechaRe = date('d/m/y') . ' - ' . date('H:i:s');
        }

        $this->Cell(89, 5, $this->usr_id->getUsr_id() . ' - ' . $this->usr_id->getUsuario(), 0, 0, 'L');
        $this->Cell(89, 5, utf8Decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
        $this->Cell(90, 5, $fechaRe, 0, 1, 'R');
    }
    // FIN footer
}

$omo_id = (isset($_POST['omo_id']) && $_POST['omo_id'] != '') ? $_POST['omo_id'] : '';
$gpo_id = (isset($_POST['mod_gpo_id']) && $_POST['mod_gpo_id'] != '') ? $_POST['mod_gpo_id'] : '';
$ur_id = (isset($_POST['mod_ur_id']) && $_POST['mod_ur_id'] != '') ? $_POST['mod_ur_id'] : '';
$mod_id = (isset($_POST['mod_id']) && $_POST['mod_id'] != '') ? $_POST['mod_id'] : '';

$tipo = '';

switch ($_SESSION[_S]['nvj_id']) {
    case 1:case 2: //UR y Ue
        $tipo = "and tcp_id!='P'";
        break;
    default:
        $tipo = '';
        break;
}
$sql = "
select
    modificaciones.*, d.pe_nombre, pv.pre_vigente,pv.vig_ene, pv.vig_feb, pv.vig_mar, pv.vig_abr, pv.vig_may,
pv.vig_jun, pv.vig_jul, pv.vig_ago, pv.vig_sep, pv.vig_oct,pv.vig_nov, pv.vig_dic,e.pre_claveCompleta,f.tmd_nombre, o.tmd_id,o.tfm_id,
ore_oficioAut,toa_id,
case  when mod_tipoMovimiento = 'A' THEN 'AMPLIACIÓN'
    when mod_tipoMovimiento = 'R' THEN 'REDUCCIÓN'
    when mod_tipoMovimiento = '' THEN ''
END AS tipo_Movimiento
    from
(
    select
    b.omo_id, b.mod_id, b.mod_concepto,b.emo_id,a.mov_id, a.dmo_importe,b.enc_oop, b.mod_tipoMovimiento,
        a.dmo_ene, a.dmo_feb, a.dmo_mar, a.dmo_abr, a.dmo_may, a.dmo_jun, a.dmo_jul,
        a.dmo_ago, a.dmo_sep, a.dmo_oct, a.dmo_nov, a.dmo_dic,
        a.gpo_id, a.ur_id, a.ue_id, a.epg_id, a.pg_id, a.pe_id, a.ecf_id,a.tcp_id,b.ecf_id as clavefin,
        a.vig_ene pa_ene, a.vig_feb pa_feb, a.vig_mar pa_mar, a.vig_abr pa_abr, a.vig_may pa_may,
    a.vig_jun pa_jun, a.vig_jul pa_jul, a.vig_ago pa_ago, a.vig_sep pa_sep, a.vig_oct pa_oct,
        a.vig_nov pa_nov, a.vig_dic pa_dic, a.pre_vigente pa_importe,b.mod_ue_id,mod_Subsecretaria
    from scp_MaestroModificacion b
    INNER JOIN scp_DetalleModificacion a using (omo_id,mod_gpo_id,mod_ur_id,mod_id)
    where omo_id = :omo and mod_gpo_id= :gpo and mod_ur_id= :ur and mod_id= :mod
) modificaciones
INNER JOIN cat_PartidaEspecifica d using(pe_id,pg_id)
INNER JOIN presupuesto e using (gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
    JOIN presupuestoVigente pv USING(gpo_id,ur_id,ue_id,epg_id, pg_id, pe_id,ecf_id,tcp_id)
INNER JOIN poa_OrigenRecurso using(gpo_id,ur_id,epg_id,ecf_id)
INNER JOIN cat_ObraActividad using(gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id)
INNER JOIN enc_OrigenOperacion o using(omo_id,enc_oop)
INNER JOIN cat_TipoModificacion f using(tmd_id)
;
";

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar([':omo' => $omo_id,':gpo' => $gpo_id,':ur' => $ur_id,':mod' => $mod_id . $tipo]);
$conexion->desconectar();

$pdf = new Reportetabla('L', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCP);
$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');

$fill = false;
$datos = $conexion->extraer();
$pdf->setDatos($datos);
$pdf->AddPage();
$total_a = 0;
$total_r = 0;

do {
    switch ($datos['mov_id']) {
        case 'A':
            $tipomov = '(Amp.)';
            break;
        case 'R':
            $tipomov = '(Red.)';
            break;
        case 'C':
            $tipomov = '(Cal.)';
            break;
        default:
            $tipomov = '(XXX)';
            break;
    }

    $pdf->SetFont('univiapro', '', 5);
    $pdf->SetAltoLinea(1);
    $pdf->SetWidths([47, 8, 18, 17, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
    $pdf->SetAligns(['L', 'C', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R', 'R']);

    switch ($datos['emo_id']) {
        case 14: //Autorizado
            $pdf->Row([$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'],
                $tipomov, number_format($datos['pa_importe'], 2),
                number_format($datos['pa_ene'], 2), number_format($datos['pa_feb'], 2),
                number_format($datos['pa_mar'], 2), number_format($datos['pa_abr'], 2),
                number_format($datos['pa_may'], 2), number_format($datos['pa_jun'], 2),
                number_format($datos['pa_jul'], 2), number_format($datos['pa_ago'], 2),
                number_format($datos['pa_sep'], 2), number_format($datos['pa_oct'], 2),
                number_format($datos['pa_nov'], 2), number_format($datos['pa_dic'], 2)]);
            break;
        default: //En estado 1,3,6,9
            $pdf->Row([$datos['pre_claveCompleta'] . ' - ' . $datos['tcp_id'],
                $tipomov, number_format($datos['pre_vigente'], 2),
                number_format($datos['vig_ene'], 2), number_format($datos['vig_feb'], 2),
                number_format($datos['vig_mar'], 2), number_format($datos['vig_abr'], 2),
                number_format($datos['vig_may'], 2), number_format($datos['vig_jun'], 2),
                number_format($datos['vig_jul'], 2), number_format($datos['vig_ago'], 2),
                number_format($datos['vig_sep'], 2), number_format($datos['vig_oct'], 2),
                number_format($datos['vig_nov'], 2), number_format($datos['vig_dic'], 2)]);
            break;
    }

    $pdf->SetAltoLinea(1.8);
    $pdf->Row([utf8Decode($datos['pe_nombre']),
        '', number_format($datos['dmo_importe'], 2),
        number_format($datos['dmo_ene'], 2),
        number_format($datos['dmo_feb'], 2),
        number_format($datos['dmo_mar'], 2),
        number_format($datos['dmo_abr'], 2),
        number_format($datos['dmo_may'], 2),
        number_format($datos['dmo_jun'], 2),
        number_format($datos['dmo_jul'], 2),
        number_format($datos['dmo_ago'], 2),
        number_format($datos['dmo_sep'], 2),
        number_format($datos['dmo_oct'], 2),
        number_format($datos['dmo_nov'], 2),
        number_format($datos['dmo_dic'], 2)]);
    $pdf->SetAltoLinea(1);

    switch ($datos['emo_id']) {
        case 14: //Autorizado
            $pdf->Row(['', '', ($datos['mov_id'] == 'A') ? number_format($datos['pa_importe'] + $datos['dmo_importe'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_importe'] - $datos['dmo_importe'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_ene'] + $datos['dmo_ene'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_ene'] - $datos['dmo_ene'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_feb'] + $datos['dmo_feb'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_feb'] - $datos['dmo_feb'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_mar'] + $datos['dmo_mar'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_mar'] - $datos['dmo_mar'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_abr'] + $datos['dmo_abr'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_abr'] - $datos['dmo_abr'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_may'] + $datos['dmo_may'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_may'] - $datos['dmo_may'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_jun'] + $datos['dmo_jun'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_jun'] - $datos['dmo_jun'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_jul'] + $datos['dmo_jul'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_jul'] - $datos['dmo_jul'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_ago'] + $datos['dmo_ago'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_ago'] - $datos['dmo_ago'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_sep'] + $datos['dmo_sep'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_sep'] - $datos['dmo_sep'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_oct'] + $datos['dmo_oct'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_oct'] - $datos['dmo_oct'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_nov'] + $datos['dmo_nov'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_nov'] - $datos['dmo_nov'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['pa_dic'] + $datos['dmo_dic'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pa_dic'] - $datos['dmo_dic'], 2) : '')]);
            break;
        default: //1,3,6,9
            $pdf->Row(['', '', ($datos['mov_id'] == 'A') ? number_format($datos['pre_vigente'] + $datos['dmo_importe'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['pre_vigente'] - $datos['dmo_importe'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_ene'] + $datos['dmo_ene'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_ene'] - $datos['dmo_ene'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_feb'] + $datos['dmo_feb'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_feb'] - $datos['dmo_feb'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_mar'] + $datos['dmo_mar'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_mar'] - $datos['dmo_mar'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_abr'] + $datos['dmo_abr'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_abr'] - $datos['dmo_abr'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_may'] + $datos['dmo_may'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_may'] - $datos['dmo_may'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_jun'] + $datos['dmo_jun'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_jun'] - $datos['dmo_jun'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_jul'] + $datos['dmo_jul'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_jul'] - $datos['dmo_jul'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_ago'] + $datos['dmo_ago'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_ago'] - $datos['dmo_ago'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_sep'] + $datos['dmo_sep'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_sep'] - $datos['dmo_sep'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_oct'] + $datos['dmo_oct'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_oct'] - $datos['dmo_oct'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_nov'] + $datos['dmo_nov'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_nov'] - $datos['dmo_nov'], 2) : ''),
                ($datos['mov_id'] == 'A') ? number_format($datos['vig_dic'] + $datos['dmo_dic'], 2) : (($datos['mov_id'] == 'R') ? number_format($datos['vig_dic'] - $datos['dmo_dic'], 2) : '')]);
            break;
    }
    $xtm = $pdf->getX();
    $ytm = $pdf->getY();
    $pdf->ln(1);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->setDrawColor(220, 220, 220);
    $pdf->SetLineWidth(.5);
    $pdf->Line($xtm, $ytm, 268, $ytm);

    switch ($datos['mov_id']) {
        case 'A':case 'C': //si es ampliacion o calendario
            $total_a = $total_a + $datos['dmo_importe'];
            break;
        default: //si es reduccion;
            $total_r = $total_r + $datos['dmo_importe'];
            break;
    }

    $pdf->Ln(1);
    if ($pdf->getY() > 160) {
        $pdf->addPage();
    }
    if ($fill) {
        $fill = false;
    }

} while ($datos = $conexion->extraer());
$datos = $pdf->getDatos();
switch ($datos['tmd_id']) {
    case 'A': //traspaso intrainstitucional(ampliación,reducción)
        $tipom = 'TOTAL AMPLIACIÓN INTRAINSTITUCIONAL:' . '    ' . number_format($total_a, 2) . '   ' . 'TOTAL REDUCCIÓN INTRAINSTITUCIONAL:' . '    ' . number_format($total_r, 2);
        break;
    case 'B': //traspaso interinstitucional
        if ($datos['mod_tipoMovimiento'] == 'A') { //traspaso interinstitucional "ampliacion"
            switch ($datos['emo_id']) {
                case 6:
                case 11:
                case 14:
                case 15:
                    if ($_SESSION[_S]['nvj_id'] >= 3) {
                        $tipom = 'TOTAL AMPLIACIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_a, 2) . '   ' . 'TOTAL REDUCCIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_r, 2);
                    } else {
                        $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_a, 2);
                    }
                    break;
                default:
                    $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_a, 2);
                    break;
            }
        }
        if ($datos['mod_tipoMovimiento'] == 'R') { //traspaso interinstitucional "reducciÓn"
            switch ($datos['emo_id']) {
                case 6:
                case 11:
                case 14:
                case 15:
                case 17:
                    if ($_SESSION[_S]['nvj_id'] >= 3) {
                        $tipom = 'TOTAL AMPLIACIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_a, 2) . '   ' . 'TOTAL REDUCCIÓN INTERINSTITUCIONAL:' . '    ' . number_format($total_r, 2);
                    } else {
                        $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_r, 2);
                    }

                    break;
                default:
                    $tipom = 'TOTAL TRASPASO INTERINSTITUCIONAL:' . '   ' . number_format($total_r, 2);
                    break;
            }
        }
        break;
    case 'C':$tipom = 'TOTAL CALENDARIZADO:' . '   ' . number_format($total_a, 2);
        break;
    case 'D':$tipom = 'TOTAL AMPLIACIONES:' . '   ' . number_format($total_a, 2);
        break;
    case 'E':$tipom = 'TOTAL REDUCCIONES:' . '   ' . number_format($total_r, 2);
        break;
    default:$tipom = '';
        break;
}
$pdf->SetAltoLinea(2.5);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([300]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode($tipom)]);

$datos = $pdf->getDatos();
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetWidths([200]);
$pdf->SetAligns(['L']);
$pdf->SetBorders(0);
$pdf->Row([utf8Decode('JUSTIFICACIÓN:') . '  ' . utf8Decode($datos['mod_concepto'])]);

$pdf->Output();
