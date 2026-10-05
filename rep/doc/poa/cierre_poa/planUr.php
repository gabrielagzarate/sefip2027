<?php

/**
 * Description of Pdf_PlanUr
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de :  PLANEACIÓN INSTITUCIONAL
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
class Pdf_PlanUr extends PDF
{
    public $gpo_id = '';
    public $ur_id = '';
    public $adm_gpo;
    public $adm_ur;
    public $estadoCosteo;
    public UnidadResponsable $ur;
    public $opc;
    public $longitud;
    public $nombreUR;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        //variables de session
        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->adm_gpo);
        $this->ur->setUr_id($this->adm_ur);
        $this->ur->select();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();
        $this->opc = 1;
    } // fin __construct

    public function Footer($lastPage = true)
    {
        $row = $this->getDatos();
        $this->SetY(-30);

        if ($row['ur_nombrePlaneacion'] == '' && $row['ur_cargoPlaneacion'] == '') {

            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8decode($row['ur_cargoAdministrativo']), 0, 0, 'C');
            $this->Cell(90, 2, utf8decode($row['ur_cargoTitular']), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8decode('ELABORÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(50, 2, utf8decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(50, 2, utf8decode($row['ur_nombrePlaneacion']), 0, 0, 'C');
            $this->Cell(90, 2, utf8decode($row['ur_nombreAdministrativo']), 0, 0, 'C');
            $this->Cell(50, 2, utf8decode($row['ur_nombreTitular']), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $y = $this->getY();
            $x = $this->getX();
            $this->setX(10);
            $this->multicell(50, 3, utf8decode($row['ur_cargoPlaneacion']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $x = $this->getX();
            $this->multicell(50, 3, utf8decode($row['ur_cargoAdministrativo']), 0, 'C');
            $this->setY($y);
            $this->setX($x + 70);
            $this->multicell(50, 3, utf8decode($row['ur_cargoTitular']), 0, 'C');
        }
        $this->ln();

        $this->SetFont('univiapro', '', 5);
        $x = 0;
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8decode('' . ' - ' . '' . ' '), 0, 'L');

        $this->Cell(0, 0, utf8decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
        $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s') . '        ', 1, 0, 'R');
    } // fin Footer

    public function Header()
    { //Encabezado del documento
        $year = APP_EJERCICIO;
        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql('SELECT eur_id FROM cat_UnidadResponsable where gpo_id = ? and ur_id = ?');
        $params = [$this->adm_gpo, $this->adm_ur];
        $conexion2->consultar($params);
        $conexion2->desconectar();
        $detalle = $conexion2->extraer();

        if ($detalle['eur_id'] < 5) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        } elseif ($this->opc == 0) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'NO APLICA', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'NO APLICA', 45);
            $this->RotatedText($ix, $iy + 80, 'NO APLICA', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'NO APLICA', 45);
        }

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        //setter images name empty
        $this->setRouteImg(' ');

        $this->Image($this->getRouteImg(), $ix + 98, $iy + 1, '95', '20', 'PNG', '');

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, $year, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . $year, 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8decode('PLANEACIÓN INSTITUCIONAL'), 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $ytmp = $this->GetY();

        $this->longitud = 68;
        $this->nombreUR = TruncateCadena(utf8decode(trim('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), $this->longitud);

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 7, $ytmp + 4);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 7, $ytmp + 8);
            $this->Cell(5, 5, substr($this->nombreUR, $this->longitud * 2, $this->longitud), 0, 0, 'L');
            $this->Cell(163, 5, '', 0, 1, 'R');
        }

        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->Cell(195, 4, 'POA-01', 0, 1, 'R');

        $this->SetBorders(0);
        $this->ln(1);
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $ytmp = $this->GetY();

        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(11, $ytmp, 205, $ytmp);

        $this->ln(4);
    } // fin utf8decode
} // fin Pdf_PlanUr

//consulta
$sql = '
    SELECT
        gpo_id,ur_id,ur_nombre,ur_nombreTitular, ur_cargoTitular,ur_nombreAdministrativo,ur_cargoAdministrativo
        ,ur_inicioPeriodicoOficial,IFNULL( ur_inicioFecha, 0 ) AS ur_inicioFecha, IFNULL( ur_inicioDecretoFecha, 0 ) AS ur_inicioDecretoFecha,ur_modificacionPeriodico,ur_nombrePlaneacion,ur_cargoPlaneacion,
        IFNULL( ur_modificacionFecha, 0 ) AS ur_modificacionFecha, ur_internoArchivo, IFNULL( ur_internoFecha, 0 ) AS ur_internoFecha, IFNULL( ur_organigramaFecha, 0 ) AS ur_organigramaFecha,ur_mision,ur_vision,
        ur_objetivoGeneral,ur_objetivoEstrategico,ur_productoInstitucional,ur_poa, eur_id,ur_organigramaArchivo
    FROM cat_UnidadResponsable
    JOIN aux_UnidadResponsable USING( gpo_id, ur_id )
    WHERE gpo_id = ? and ur_id = ?;
    ';
$params = [$_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur']];
$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar($params);

/*Los reportes se diseñan para hojas tamano carta*/
$pdf = new Pdf_PlanUr();
/*Se agrega la paginacion*/
$pdf->AliasNbPages();
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', 'nUtq8Zsy42jkLvU');
if ($row = $conexion->extraer()) {
    do {
        $pdf->setDatos($row);
        $datos = $pdf->getDatos();
        /*Se debe controlar cuando se inserta una nueva pagina*/
        $pdf->AddPage();
        /*Contenido del reporte*/

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->SetBorders(0);
        $pdf->Row([utf8decode('DECRETO DE CREACIÓN')]);
        $pdf->ln(3);
        $pdf->SetFont('UniviaPro-Bold', 'B', 8);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8decode('A) Inicio de operaciones')]);

        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);

        // Por si alguna viene en nulo, las pre-procesamos
        if ($datos['ur_inicioFecha'] != 0) {
            $fechaInicio = date('d-m-Y', strtotime($datos['ur_inicioFecha']));
        } else {
            $fechaInicio = '';
        }
        if ($datos['ur_inicioDecretoFecha'] != 0) {
            $fechaDecreto = date('d-m-Y', strtotime($datos['ur_inicioDecretoFecha']));
        } else {
            $fechaDecreto = '';
        }
        if ($datos['ur_modificacionFecha'] != 0) {
            $fechaModificacion = date('d-m-Y', strtotime($datos['ur_modificacionFecha']));
        } else {
            $fechaModificacion = '';
        }
        if ($datos['ur_internoFecha'] != 0) {
            $fechaInterno = date('d-m-Y', strtotime($datos['ur_internoFecha']));
        } else {
            $fechaInterno = '';
        }
        if ($datos['ur_organigramaFecha'] != 0) {
            $fechaOrganigrama = date('d-m-Y', strtotime($datos['ur_organigramaFecha']));
        } else {
            $fechaOrganigrama = '';
        }

        $pdf->ln(2);
        $pdf->Row([utf8decode('No. de Periódico Oficial:'), utf8decode($datos['ur_inicioPeriodicoOficial']), utf8decode('Fecha de Publicación:'), $fechaInicio]);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->ln(2);
        $pdf->SetWidths([60, 80]);
        $pdf->SetAligns(['L', 'L']);

        $pdf->CheckPageBreak(25);
        $pdf->ln(3);
        $pdf->SetFont('UniviaPro-Bold', 'B', 8);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8decode('B) Modificaciones y reformas a las disposiciones del decreto de creación')]);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->ln(2);
        $pdf->Row([utf8decode('No. de Periódico Of. de la última Modificación:'), utf8decode($datos['ur_modificacionPeriodico']), utf8decode('Fecha de Publicación:'), $fechaModificacion]);
        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row(['REGLAMENTO INTERNO']);

        $pdf->CheckPageBreak(25);
        $pdf->ln(2);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([60, 70, 40, 20]);
        $pdf->SetAligns(['L', 'L', 'L', 'L']);
        $pdf->Row([utf8decode('Archivo Digital:'), utf8decode($datos['ur_internoArchivo'])]);

        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row(['ORGANIGRAMA']);

        $pdf->ln(2);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([60, 40]);
        $pdf->SetAligns(['L', 'L']);
        $pdf->Row([utf8decode('Archivo Digital:'), utf8decode($datos['ur_organigramaArchivo'])]);

        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8decode('MISIÓN')]);

        $pdf->ln(2);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->RowJustify(utf8decode(trim($datos['ur_mision'])), 195, 4);

        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8decode('VISIÓN')]);

        $pdf->ln(2);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->RowJustify(utf8decode(trim($datos['ur_vision'])), 195, 4);

        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row(['OBJETIVO GENERAL DE LA UR']);
        $pdf->ln(2);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->RowJustify(utf8decode(trim($datos['ur_objetivoGeneral'])), 195, 4);

        $pdf->ln(4);
        $pdf->CheckPageBreak(25);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row([utf8decode('OBJETIVO ESTRATÉGICO DE LA UR')]);

        $pdf->ln(2);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->RowJustify(utf8decode(trim($datos['ur_objetivoEstrategico'])), 195, 4);

        $pdf->ln(4);
        //$pdf->CheckPageBreak(3);
        $pdf->SetAutoPageBreak(true, 70);
        $pdf->SetFont('UniviaPro-Bold', 'B', 9);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->Row(['PRODUCTO INSTITUCIONAL DE LA UR']);
        $pdf->ln(2);
        $pdf->SetFont('UniviaPro-Regular', '', 7.5);
        $pdf->SetWidths([185]);
        $pdf->SetAligns(['L']);
        $pdf->RowJustify(utf8decode(trim($datos['ur_productoInstitucional'])), 195, 4);

        //  $pdf->CheckPageBreak(25);
    } while ($row = $conexion->extraer());
}

$pdf->Output();
