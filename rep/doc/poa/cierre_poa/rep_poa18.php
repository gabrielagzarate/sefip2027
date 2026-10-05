<?php

/**
 * Description of Pdf_Poa18
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 05-ago-2020
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de : CÉDULA VEHICULAR
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
class Pdf_Poa18 extends PDF
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
    public $nombreUR;
    public $longitud;

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

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

    public function cabecera()
    {
        $this->SetBorders(0);
        $this->SetFillColor(199, 209, 219);
        $this->RoundedRect(10, $this->GetY(), 261, 6, 1, 'DF', '1234');
        $this->SetWidths([144, 84, 33]);
        $this->SetAligns(['C', 'C', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetRelleno('no');
        $this->Row([utf8Decode('Vehículo o Maquinaria'), 'Total Presupuesto', utf8Decode('Total Vehículo')]);
        $this->setBorders(0);
    } // fin cabecera

    public function cabecera2()
    {
        $this->setX(10);
        $this->SetBorders(0);
        $this->SetFillColor(199, 209, 219);
        $this->RoundedRect(10, $this->GetY(), 261, 6, 1, 'DF', '1234');
        $this->SetWidths([31, 27, 20, 28, 20, 18, 18, 17, 27, 22, 33]);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'R', 'R', 'R', 'R', 'R']);
        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->Row([
            'SERIE',
            'USO',
            utf8Decode('UBICACIÓN'),
            utf8Decode('Tipo de Vehículo'),
            'TIPO',
            'PLACAS',
            'IMPUESTOS',
            'SEGUROS',
            'MANTENIMIENTO',
            'COMBUSTIBLES',
            '',
        ]);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
        $this->setBorders(0);
        $this->ln(2);
    } // fin cabecera2

    public function cambioCap($cap_id, $cap_nombre, $pg_id, $pg_nombre, $pe_id, $pe_nombre)
    {
        $this->SetBorders(0);
        $this->SetWidths([600]);
        $this->SetAligns(['L']);
        $this->Line(25, 51, 199, 51);
        $this->ln(2);
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Row([utf8Decode('CAPÍTULO: ' . $cap_id) . $cap_nombre]);
        $this->parGenEsp($pg_id, $pg_nombre, $pe_id, $pe_nombre);
    } //fin cambioCap

    public function parGenEsp($pg_id, $pg_nombre, $pe_id, $pe_nombre)
    {
        $this->SetRelleno('no');
        $this->SetBorders(0);
        $this->SetWidths([185]);
        $this->SetAligns(['L']);
        $this->SetFont('UniviaPro-Regular', '', 7.5);
        $this->Cell(20);
        $this->Row([utf8Decode('PARTIDA GENÉRICA: ') . $pg_id . ' - ' . $pg_nombre]);
        $this->Cell(24);
        $this->Row([utf8Decode('PARTIDA ESPECÍFICA: ') . $pe_id . ' - ' . $pe_nombre]);
        $this->ln(2);
    } //fin parGenEsp

    public function Footer($lastPage = true)
    {
        $datos = $this->getDatos();

        if ($datos == '') {
            $conexion3 = new AccesoDatos();
            $conexion3->conectar();
            $conexion3->setSql('
            SELECT ur_nombreAdministrativo,ur_nombreTitular,ur_cargoAdministrativo,ur_cargoTitular,ur_nombrePlaneacion,
            ur_cargoPlaneacion
            FROM aux_UnidadResponsable where gpo_id = ? and ur_id = ? ');
            $params = [$this->adm_gpo, $this->adm_ur];
            $conexion3->consultar($params);
            $conexion3->desconectar();
            $datos = $conexion3->extraer();
        }

        $this->SetY(-25);
        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->Cell(130, 2, '', 0, 1, 'C');

        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(70, 2, utf8Decode('ELABORÓ'), 0, 0, 'C');
        $this->Cell(110, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
        $this->Cell(70, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $this->Cell(70, 2, utf8Decode($datos['ur_nombrePlaneacion']), 0, 0, 'C');
        $this->Cell(110, 2, utf8Decode($datos['ur_nombreAdministrativo']), 0, 0, 'C');
        $this->Cell(70, 2, utf8Decode($datos['ur_nombreTitular']), 0, 1, 'C');

        $this->Cell(130, 2, '', 0, 1, 'C');
        $this->SetFont('UniviaPro-Bold', 'B', 6.5);
        $y = $this->getY();
        $x = $this->getX();
        $this->setX(10);
        $this->multicell(70, 3, utf8Decode($datos['ur_cargoPlaneacion']), 0, 'C');
        $this->setY($y);
        $this->setX($x + 90);
        $x = $this->getX();
        $this->multicell(70, 3, utf8Decode($datos['ur_cargoAdministrativo']), 0, 'C');
        $this->setY($y);
        $this->setX($x + 90);
        $this->multicell(70, 3, utf8Decode($datos['ur_cargoTitular']), 0, 'C');

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
        $anchoContenido = $this->getAnchoEncabezadoPoa(261);

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
        $this->pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, 'POA-18', utf8Decode('INVENTARIO DE VEHÍCULOS POR UR'), $anchoContenido, $ytmp);

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, 10 + $anchoContenido, $ytmp);
        $this->ln(4);
        $this->setDrawColor(170, 170, 170);
    } //fin Header

    public function registro($datos, $cantidades, $relleno)
    {
        $this->SetBorders(0);
        $this->SetFont('UniviaPro-Regular', '', 6);
        $this->setBorders('B');
        $this->setDrawColor(240, 240, 240);
        $this->SetRelleno($relleno);
        $this->setX(10);
        $this->setAltoLinea(2);
        $this->SetWidths([31, 27, 20, 28, 20, 18, 18, 17, 27, 22, 33]);
        $this->SetAligns(['C', 'C', 'C', 'C', 'C', 'C', 'R', 'R', 'R', 'R', 'R']);
        $this->Row([
            utf8Decode($datos['serie']),
            utf8Decode($datos['uso']),
            utf8Decode($datos['ubicacion']),
            utf8Decode($datos['tipo_vehiculo']),
            utf8Decode($datos['tipo']),
            $datos['placas'],
            number_format($cantidades['impuestos'], 2),
            number_format($cantidades['seguros'], 2),
            number_format($cantidades['mantenimiento'], 2),
            number_format($cantidades['combustibles'], 2),
            number_format($cantidades['total'], 2),
        ]);
        $this->SetRelleno('no');
        if ($this->CheckPageBreak(35)) {
            $this->cabecera2();
        }
    } // fin registro

    public function titulo($nombre, $prg_id, $prg_nombre)
    {
        $this->setBorders('B');
        $this->setDrawColor(220, 220, 220);
        $this->SetX(10);
        $this->SetRelleno('no');
        $this->SetFont('UniviaPro-Regular', '', 6);
        $this->SetWidths([26, 235]);
        $this->SetAligns(['L', 'L']);
        $this->Row([utf8Decode($nombre . ':'), utf8Decode($prg_id) . ' - ' . utf8Decode($prg_nombre)]);
        $this->setBorders(0);
        $this->SetFillColor(255, 255, 255);
    } // fin titulo

    public function reservarEspacioTotales(int $lineas): void
    {
        $this->CheckPageBreak(8 + ($lineas * 9));
    }

    public function Subtotal($texto, $par)
    {
        $this->CheckPageBreak(9);
        $this->SetBorders(0);
        $this->Cell(228, 4, utf8Decode($texto), 0, 0, 'R');
        $this->Cell(33, 4, '$' . number_format($par, 2), 0, 0, 'R', false);
        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
        $this->SetBorders(0);
        $this->ln(5);
    } //fin Subtotal

    public function subtotales($cantidades)
    {
        $this->SetBorders(0);
        $this->SetFillColor(255, 255, 255);
        $this->SetRelleno('no');
        $this->SetRelleno('si');
        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->SetWidths([144, 18, 17, 27, 22, 33]);
        $this->SetAligns(['R', 'R', 'R', 'R', 'R', 'R']);
        $this->Row([
            'Subtotales',
            number_format($cantidades['impuestos'], 2),
            number_format($cantidades['seguros'], 2),
            number_format($cantidades['mantenimiento'], 2),
            number_format($cantidades['combustibles'], 2),
            number_format($cantidades['total'], 2),
        ]);
        $this->ln(1);
        $this->SetFillColor(255, 255, 255);
    } //fin subtotales
} //fin class

/*Los reportes se diseñan para hojas tamaño carta*/
$pdf = new Pdf_Poa18('L', 'mm', 'letter');
$pdf->SetProtection(['print'], '', PDF_PASSWORD_OWNER_POA);
$pdf->setOrientation('L');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetAutoPageBreak(true, 28);

$filtro = '';
$params = [];
if ($pdf->nvj > 1) {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? ';
    $params = [$pdf->adm_gpo, $pdf->adm_ur, $pdf->adm_gpo, $pdf->adm_ur];
} else {
    $filtro = ' WHERE gpo_id = ? and ur_id = ? and ue_id = ? ';
    $params = [$pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue, $pdf->gpo_id, $pdf->ur_id, $pdf->adm_ue];
}

$sql = '  SELECT ur_poa,gpo_id,ur_id,prg_id,prg_nombre,spr_id,spr_nombre,ue_id,ue_nombre,py_id,py_nombre,oa_id,oa_nombre,
                ter_id,CONCAT(cfi_clave,"  ",cfi_nombre) AS clave_fin,epg_id,ur_nombre,
                ur_nombre,ur_nombreTitular, ur_nombreAdministrativo, ur_cargoTitular, ur_cargoAdministrativo,ur_nombrePlaneacion,ur_cargoPlaneacion,
                ecf_id, epg_id,pqv_id,usv_nombre,pqv_numeroPlacas,
                CASE tfv_id WHEN 1 THEN "Patrimonio del Estado" WHEN 2 THEN "Arrendado" ELSE "" END AS tipo_vehiculo,
                pqv_tipo,ubv_nombre, cve_id,Total,eur_id
            FROM cat_ParqueVehicular
            JOIN enc_Programatico USING(epg_id,gpo_id,ur_id)
            JOIN cat_Programa USING(gpo_id,ur_id,prg_id)
            JOIN cat_Subprograma USING(gpo_id,ur_id,prg_id,spr_id)
            JOIN cat_Proyecto USING(gpo_id,ur_id,prg_id,spr_id,py_id)
            JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            JOIN cat_UnidadEjecutora USING(gpo_id,ur_id,ue_id)
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' cs USING(cff_id, fte_id, sft_id, ori_id, sor_id )
            JOIN sinpres.cat_TipoRecurso USING (ter_id)
            JOIN cat_UnidadResponsable using(ur_id,gpo_id)
            INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)
            JOIN cat_UsoVehiculo using(usv_id)
            JOIN  cat_UbicacionVehiculo using(ubv_id)
            LEFT JOIN  (
                    SELECT cve_id,pqv_id,pg_id,sum(mon_ene+mon_feb+mon_mar+mon_abr+mon_may+mon_jun+mon_jul+mon_ago+
                            mon_sep+mon_oct+mon_nov+mon_dic) as Total , epg_id, gpo_id,ur_id,ue_id
                        FROM poa_VehiculoMaquina
                        JOIN cat_ConceptoVehiculo USING(cve_id)
                        ' . $filtro . '
                        GROUP BY pqv_id,cve_id,pg_id,epg_id,ue_id
                        HAVING Total > 0
                )AS Impuestos USING (pqv_id,gpo_id,ur_id,ue_id,epg_id)
            ' . $filtro . '
            HAVING Total >= 0
            ORDER BY  prg_id,spr_id,ue_id,py_id,oa_id,cfi_clave,cfi_nombre,pqv_id,cve_id;
    ';
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);

/*Se agrega la paginacion*/
$pdf->AliasNbPages();
$totaltotales = 0;
$totaloa = 0;
$totalpy = 0;
$totalue = 0;
$totalspr = 0;
$totalprg = 0;
$totalaoa = 0;

$row = $conexion->extraer();
if ($row) {
    $pdf->setDatos($row);
    $datos = $pdf->getDatos();
    $pdf->AddPage();

    $pdf->titulo('PROGRAMA', $row['prg_id'], $row['prg_nombre']);
    $pdf->ln(2);
    $pdf->titulo('SUBPROGRAMA', $row['spr_id'], $row['spr_nombre']);
    $pdf->ln(2);
    $pdf->titulo('UE', $row['ue_id'], $row['ue_nombre']);
    $pdf->ln(2);
    $pdf->titulo('PY', $row['py_id'], $row['py_nombre']);
    $pdf->ln(2);
    $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
    $pdf->ln(2);
    $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);
    $pdf->CheckPageBreak(39);
    $pdf->setAltoLinea(4);

    $datos = ['serie' => '', 'uso' => '', 'ubicacion' => '', 'tipo_vehiculo' => '', 'tipo' => '', 'placas' => ''];
    $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $total = $totalvehiculo = $celda = 0;
    $impuestos = $seguros = $mantenimiento = $combustibles = $total = 0;
    $serie = $row['pqv_id'];
    $oa_id = $row['oa_id'];
    $py_id = $row['py_id'];
    $ue_id = $row['ue_id'];
    $spr_id = $row['spr_id'];
    $prg_id = $row['prg_id'];

    $totaltotales = 0;
    $totaloa = 0;
    $totalpy = 0;
    $totalue = 0;
    $totalspr = 0;
    $totalprg = 0;

    $pdf->ln(2);
    $pdf->cabecera();
    $pdf->cabecera2();

    do {
        $pdf->CheckPageBreak(39);

        if ($prg_id != $row['prg_id']) { // cambio de programa
            $spr_id = $row['spr_id'];
            $serie = $row['pqv_id'];
            $prg_id = $row['prg_id'];
            $ue_id = $row['ue_id'];
            $py_id = $row['py_id'];
            $oa_id = $row['oa_id'];

            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            }
            $celda = 0;

            $cantidades = [];
            $cantidades['impuestos'] = $totalimpuestos;
            $cantidades['seguros'] = $totalseguros;
            $cantidades['mantenimiento'] = $totalmantenimiento;
            $cantidades['combustibles'] = $totalcombustible;
            $cantidades['total'] = $totalvehiculo;

            $totaloa += $totalvehiculo;
            $totalpy += $totaloa;
            $totalue += $totalpy;
            $totalspr += $totalue;
            $totalprg += $totalspr;

            $pdf->reservarEspacioTotales(5);
            $pdf->subtotales($cantidades);
            $pdf->Subtotal('Total Obra/Actividad', $totaloa);
            $pdf->Subtotal('Total Proyecto', $totalpy);
            $pdf->Subtotal('Total Unidad Ejecutora', $totalue);
            $pdf->Subtotal('Total Subprograma', $totalspr);
            $pdf->Subtotal('Total Programa', $totalprg);

            $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $totalvehiculo = 0;
            $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            $totaloa = 0;
            $totalpy = 0;
            $totalue = 0;
            $totalspr = 0;
            $totalprg = 0;

            $pdf->CheckPageBreak(41);
            $pdf->titulo('PROGRAMA', $row['prg_id'], $row['prg_nombre']);
            $pdf->ln(2);
            $pdf->titulo('SUBPROGRAMA', $row['spr_id'], $row['spr_nombre']);
            $pdf->ln(2);
            $pdf->titulo('UE', $row['ue_id'], $row['ue_nombre']);
            $pdf->ln(2);
            $pdf->titulo('PY', $row['py_id'], $row['py_nombre']);
            $pdf->ln(2);
            $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
            $pdf->ln(2);
            $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);

            $pdf->CheckPageBreak(39);
            $pdf->cabecera();
            $pdf->cabecera2();
        } elseif ($spr_id != $row['spr_id']) { //cambio de subprograma
            $spr_id = $row['spr_id'];
            $ue_id = $row['ue_id'];
            $py_id = $row['py_id'];
            $oa_id = $row['oa_id'];
            $serie = $row['pqv_id'];

            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            } //fin if
            $celda = 0;

            $cantidades = [];
            $cantidades['impuestos'] = $totalimpuestos;
            $cantidades['seguros'] = $totalseguros;
            $cantidades['mantenimiento'] = $totalmantenimiento;
            $cantidades['combustibles'] = $totalcombustible;
            $cantidades['total'] = $totalvehiculo;

            $totaloa += $totalvehiculo;
            $totalpy += $totaloa;
            $totalue += $totalpy;
            $totalspr += $totalue;
            $totalprg += $totalspr;

            $pdf->reservarEspacioTotales(4);
            $pdf->subtotales($cantidades);
            $pdf->Subtotal('Total Obra/Actividad', $totaloa);
            $pdf->Subtotal('Total Proyecto', $totalpy);
            $pdf->Subtotal('Total Unidad Ejecutora', $totalue);
            $pdf->Subtotal('Total Subprograma', $totalspr);

            $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $totalvehiculo = 0;
            $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            $totaloa = 0;
            $totalpy = 0;
            $totalue = 0;
            $totalspr = 0;

            $pdf->titulo('SUBPROGRAMA', $row['spr_id'], $row['spr_nombre']);
            $pdf->titulo('UE', $row['ue_id'], $row['ue_nombre']);
            $pdf->titulo('PY', $row['py_id'], $row['py_nombre']);
            $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
            $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);

            $pdf->CheckPageBreak(39);
            $pdf->cabecera();
            $pdf->cabecera2();
        } elseif ($ue_id != $row['ue_id']) { //cambio de unidad ejecutora
            $ue_id = $row['ue_id'];
            $py_id = $row['py_id'];
            $oa_id = $row['oa_id'];
            $serie = $row['pqv_id'];

            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            }
            $celda = 0;

            $cantidades = [];
            $cantidades['impuestos'] = $totalimpuestos;
            $cantidades['seguros'] = $totalseguros;
            $cantidades['mantenimiento'] = $totalmantenimiento;
            $cantidades['combustibles'] = $totalcombustible;
            $cantidades['total'] = $totalvehiculo;

            $totaloa += $totalvehiculo;
            $totalpy += $totaloa;
            $totalue += $totalpy;
            $totalspr += $totalue;

            $pdf->reservarEspacioTotales(3);
            $pdf->subtotales($cantidades);
            $pdf->Subtotal('Total Obra/Actividad', $totaloa);
            $pdf->Subtotal('Total Proyecto', $totalpy);
            $pdf->Subtotal('Total Unidad Ejecutora', $totalue);

            $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $totalvehiculo = 0;
            $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            $totaloa = 0;
            $totalpy = 0;
            $totalue = 0;

            $pdf->titulo('UE', $row['ue_id'], $row['ue_nombre']);
            $pdf->titulo('PY', $row['py_id'], $row['py_nombre']);
            $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
            $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);
            $pdf->ln(2);

            $pdf->CheckPageBreak(39);
            $pdf->cabecera();
            $pdf->cabecera2();
        } elseif ($py_id != $row['py_id']) { //cambio de proyecto
            $py_id = $row['py_id'];
            $oa_id = $row['oa_id'];
            $serie = $row['pqv_id'];

            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            }
            $celda = 0;

            $cantidades = [];
            $cantidades['impuestos'] = $totalimpuestos;
            $cantidades['seguros'] = $totalseguros;
            $cantidades['mantenimiento'] = $totalmantenimiento;
            $cantidades['combustibles'] = $totalcombustible;
            $cantidades['total'] = $totalvehiculo;
            $totaloa += $totalvehiculo;
            $totalpy += $totalaoa;
            $totalue += $totalpy;

            $pdf->reservarEspacioTotales(2);
            $pdf->subtotales($cantidades);
            $pdf->Subtotal('Total Obra/Actividad', $totaloa);
            $pdf->Subtotal('Total Proyecto', $totalpy);

            $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $totalvehiculo = 0;
            $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            $totaloa = 0;
            $totalpy = 0;

            $pdf->titulo('PY', $row['py_id'], $row['py_nombre']);
            $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
            $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);

            $pdf->CheckPageBreak(39);
            $pdf->cabecera();
            $pdf->cabecera2();
        } elseif ($oa_id != $row['oa_id']) { //cambio de obra actividad
            $oa_id = $row['oa_id'];
            $serie = $row['pqv_id'];

            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            }
            $celda = 0;

            $cantidades = [];
            $cantidades['impuestos'] = $totalimpuestos;
            $cantidades['seguros'] = $totalseguros;
            $cantidades['mantenimiento'] = $totalmantenimiento;
            $cantidades['combustibles'] = $totalcombustible;
            $cantidades['total'] = $totalvehiculo;
            $totaloa += $totalvehiculo;
            $totalpy += $totaloa;

            $pdf->reservarEspacioTotales(1);
            $pdf->subtotales($cantidades);
            $pdf->Subtotal('Total Obra/Actividad', $totaloa);
            $totalimpuestos = $totalseguros = $totalmantenimiento = $totalcombustible = $totalvehiculo = 0;
            $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            $totaloa = 0;

            $pdf->titulo('OA', $row['oa_id'], $row['oa_nombre']);
            $pdf->titulo('CVFIN', $row['ter_id'], $row['clave_fin']);
            $pdf->cabecera();
            $pdf->cabecera2();
        }

        /*escribir el registro despues de los totales  */
        if ($serie != $row['pqv_id']) {

            $serie = $row['pqv_id'];
            $total = $impuestos + $mantenimiento + $seguros + $combustibles;
            $totaltotales += $total;
            $cantidades = [];
            $cantidades['impuestos'] = $impuestos;
            $cantidades['seguros'] = $seguros;
            $cantidades['mantenimiento'] = $mantenimiento;
            $cantidades['combustibles'] = $combustibles;
            $cantidades['total'] = $total;

            $totalvehiculo += $total;

            if ($total != 0) {
                if ($celda == 0) {
                    $rell = 'no';
                    $celda = 1;
                } else {
                    $rell = 'si';
                    $celda = 0;
                }

                $pdf->registro($datos, $cantidades, $rell);

                $totalimpuestos += $impuestos;
                $totalseguros += $seguros;
                $totalmantenimiento += $mantenimiento;
                $totalcombustible += $combustibles;
                $total = $impuestos = $seguros = $mantenimiento = $combustibles = 0;
            }
        } // fin if

        $datos['serie'] = $row['pqv_id'];
        $datos['uso'] = $row['usv_nombre'];
        $datos['ubicacion'] = $row['ubv_nombre'];
        $datos['tipo_vehiculo'] = $row['tipo_vehiculo'];
        $datos['tipo'] = $row['pqv_tipo'];
        $datos['placas'] = $row['pqv_numeroPlacas'];
        $n = ($row['cve_id']);
        switch ($n) {
            case 1:
                $impuestos = $row['Total'];
                break;
            case 2:
            case 3:
                $seguros += $row['Total'];
                break;
            case 4:
            case 6:
                $mantenimiento += $row['Total'];
                break;
            case 5:
                $combustibles = $row['Total'];
                break;
        } //fin switch

    } while ($row = $conexion->extraer());

    $total = $impuestos + $mantenimiento + $seguros + $combustibles;
    $cantidades = [];
    $cantidades['impuestos'] = $impuestos;
    $cantidades['seguros'] = $seguros;
    $cantidades['mantenimiento'] = $mantenimiento;
    $cantidades['combustibles'] = $combustibles;
    $cantidades['total'] = $total;

    if ($total != 0) {
        if ($celda == 0) {
            $rell = 'no';
            $celda = 1;
        } else {
            $rell = 'si';
            $celda = 0;
        }
        $pdf->registro($datos, $cantidades, $rell);
    }

    $totalvehiculo += $total;
    $totalimpuestos += $impuestos;
    $totalseguros += $seguros;
    $totalmantenimiento += $mantenimiento;
    $totalcombustible += $combustibles;

    $cantidades = [];
    $cantidades['impuestos'] = $totalimpuestos;
    $cantidades['seguros'] = $totalseguros;
    $cantidades['mantenimiento'] = $totalmantenimiento;
    $cantidades['combustibles'] = $totalcombustible;
    $cantidades['total'] = $totalvehiculo;
    $totaloa += $totalvehiculo;
    $totalpy += $totaloa;
    $totalue += $totalpy;
    $totalspr += $totalue;
    $totalprg += $totalspr;
    $totaltotales += $total;

    $pdf->reservarEspacioTotales(6);
    $pdf->subtotales($cantidades);
    $pdf->Subtotal('Total Obra', $totaloa);
    $pdf->Subtotal('Total Proyecto', $totalpy);
    $pdf->Subtotal('Total Unidad Ejecutora', $totalue);
    $pdf->Subtotal('Total Subprograma', $totalspr);
    $pdf->Subtotal('Total Programa', $totalprg);
    $pdf->Subtotal('Total ', $totaltotales);
}
$pdf->Output();
