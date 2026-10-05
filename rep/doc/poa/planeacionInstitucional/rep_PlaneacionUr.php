<?php
/**
 * Description of Pdf_PlanUr
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx> 2019
Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018 y actualizao el 06-Mayo-2019
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Migrate: 02-Julio-2020
 * Comment:
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Se agrega condicion $this->opc
 *         1 = Aplica, mostrar datos
 *      0 = No aplica, no mostrar datos
 *    2.-Se agrega el archivo consulta datos
 * Estado Costeo:
 * - 1 = Abierto
 * - 2 = Cerrado
 * Objective:  Generar Reporte de :  PLANEACIÓN INSTITUCIONAL
 * Regla de Negocio:
 * Esta pantalla no pregunta por permisos de escritura ó lectura
 *
 * */
session_start();

require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../../lib/poa/planeacionInstitucional/consultaPlaneacionUr.php';

class Pdf_PlaneacionUr extends PDF
{
    public $usr_id;
    public $clasAdm_gpo;
    public $clasAdm_ur;
    public $ur;
    public $estadoCosteo;
    public $opc;
    public $firmar;
    public $marca;
    public $usuario;
    public $detalle;
    public $longitud;
    public $ancho;
    public $imagen;
    public $iyt;
    public $ix;
    public $ccd_id;
    public $sql;
    public $params;
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : false;
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : false;

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->clasAdm_gpo);
        $this->ur->setUr_id($this->clasAdm_ur);
        $this->ur->select();
        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();
        $this->opc = 1;

        $this->firmar = true;
        $this->marca = ($_SERVER['SERVER_ADDR'] != "sefip.finanzasoaxaca.gob.mx") ? false : true;

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        $conexion2 = new AccesoDatos();
        $sql = ' SELECT eur_id FROM ' . BD_CTRL . '.cat_UnidadResponsable where gpo_id= ? and ur_id= ? ;';
        $params = [$this->clasAdm_gpo, $this->clasAdm_ur];
        $conexion2->conectar();
        $conexion2->setSql($sql);
        $conexion2->consultar($params);
        $conexion2->desconectar();
        $this->detalle = $conexion2->extraer();
    }
    public function Header()
    {
        $iyt = 0;
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
        }

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if ($this->detalle['eur_id'] < 5) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        } else {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'NO APLICA', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'NO APLICA', 45);
            $this->RotatedText($ix, $iy + 80, 'NO APLICA', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'NO APLICA', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->setRouteImg(" ");
        $this->Image($this->getRouteImg(), $ix + 97, $iy - 1, '95', '23', 'png', '');
        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO, 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode('PLANEACIÓN INSTITUCIONAL'), 0, 1, 'L');

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim("UR: " . $this->ur->getClave() . " - " . $this->ur->getNombre())), ($this->longitud - 10));
        $this->Cell(5, 5, substr($nombre_ur, 0, ($this->longitud - 10)), 0, 0, 'L');
        if (strlen($nombre_ur) > ($this->longitud - 10)) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10), ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud - 10) * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 2, ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud - 10) * 3) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 3, ($this->longitud - 10)), 0, 0, 'L');
        }
        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp + 4);

        $this->SetFont('UniviaPro-Bold', 'B', 6);
        $this->Cell(185, 4, 'POA-01', 0, 1, 'R');

        $this->SetBorders(0);
        $this->ln(1);
        $this->SetFont('UniviaPro-Bold', 'B', 9);
        $tmp = $this->GetBorders(0);
        $ytmp = $this->GetY();

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
    }
    public function Content($datosConsultados)
    {
        $ix = 0;
        foreach ($datosConsultados as $row) {
            $this->setDatos($row);
            $datos = $this->getDatos();
            /*Contenido del reporte*/
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->SetBorders(0);
            $this->Row(array(utf8Decode('DECRETO DE CREACIÓN')));
            $this->ln(3);
            $this->SetFont('UniviaPro-Bold', 'B', 8);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array(utf8Decode('A) Inicio de operaciones')));

            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(60, 70, 40, 20));
            $this->SetAligns(array('L', 'L', 'L', 'L'));

            // Por si alguna viene en nulo, las pre-procesamos
            if ($datos['ur_inicioFecha'] != 0) {
                $fechaInicio = date('d-m-Y', strtotime($datos['ur_inicioFecha']));
            } else {
                $fechaInicio = "";
            }
            if ($datos['ur_modificacionFecha'] != 0) {
                $fechaModificacion = date('d-m-Y', strtotime($datos['ur_modificacionFecha']));
            } else {
                $fechaModificacion = "";
            }
            if ($datos['ur_internoFecha'] != 0) {
                $fechaInterno = date('d-m-Y', strtotime($datos['ur_internoFecha']));
            } else {
                $fechaInterno = "";
            }
            if ($datos['ur_organigramaArchivo'] != 0) {
                $archivoOrganigrama = mb_convert_encoding($datos['ur_organigramaArchivo'], 'UTF-8', 'ISO-8859-1');
            } else {
                $archivoOrganigrama = "";
            }
            if ($datos['ur_internoArchivo'] != 0) {
                $archivoInterno = mb_convert_encoding($datos['ur_internoArchivo'], 'UTF-8', 'ISO-8859-1');
            } else {
                $archivoInterno = '';
            }

            $this->ln(2);
            $this->Row(array(utf8Decode('No. de Periódico Oficial:'), utf8Decode($datos['ur_inicioPeriodicoOficial']), utf8Decode('Fecha de Publicación:'), $fechaInicio));
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->ln(2);

            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 8);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array(utf8Decode('B) Modificaciones y reformas a las disposiciones del decreto de creación')));
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(60, 70, 40, 20));
            $this->SetAligns(array('L', 'L', 'L', 'L'));
            $this->ln(2);
            $this->Row(array(utf8Decode('No. de Periódico Of. de la última Modificación:'), utf8Decode($datos['ur_modificacionPeriodico']), utf8Decode('Fecha de Publicación:'), $fechaModificacion));
            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array('REGLAMENTO INTERNO'));

            $this->CheckPageBreak(25);
            $this->ln(2);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(60, 70, 40, 20));
            $this->SetAligns(array('L', 'L', 'L', 'L'));
            $this->Row(array(utf8Decode('Archivo digital:'), $archivoInterno));

            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array('ORGANIGRAMA'));

            $this->ln(2);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(60, 40));
            $this->SetAligns(array('L', 'L'));
            $this->Row(array(utf8Decode('Archivo digital:'), $archivoOrganigrama));

            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array(utf8Decode('MISIÓN')));

            $this->ln(2);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));

            $longitud = 125;
            $ytmp = $this->GetY();
            $mision = TruncateCadena(utf8Decode(trim($datos['ur_mision'])), $longitud);

            $this->Cell(5, 5, substr($mision, 0, $longitud), 0, 0, 'L');
            $this->Cell(10, 5, '', 0, 1, 'J');

            $inc = 4;
            $lengs = 125;
            for ($i = 1; $lengs <= 2500; $i++) {
                if (strlen($mision) > $longitud * $i) {
                    $this->SetXY($ix + 10, $ytmp + $inc);
                    $this->Cell(5, 5, substr($mision, $longitud * $i, $longitud), 0, 0, 'L');
                    $this->Cell(5, 5, '', 0, 1, 'J');
                }
                $inc = $inc + 4;
                $lengs = $lengs + 125;
            }

            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array(utf8Decode('VISIÓN')));

            $this->ln(2);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));

            $ytmp = $this->GetY();
            $vision = TruncateCadena(utf8Decode(trim($datos['ur_vision'])), $longitud);

            $this->Cell(5, 5, substr($vision, 0, $longitud), 0, 0, 'L');
            $this->Cell(10, 5, '', 0, 1, 'J');

            $inc2 = 4;
            $lengs2 = 125;
            for ($i = 1; $lengs2 <= 2500; $i++) {
                if (strlen($vision) > $longitud * $i) {
                    $this->SetXY($ix + 10, $ytmp + $inc2);
                    $this->Cell(5, 5, substr($vision, $longitud * $i, $longitud), 0, 0, 'L');
                    $this->Cell(5, 5, '', 0, 1, 'J');
                }
                $inc2 = $inc2 + 4;
                $lengs2 = $lengs2 + 125;
            }

            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array('OBJETIVO GENERAL DE LA UR'));
            $this->ln(2);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));

            $ytmp = $this->GetY();
            $ur_objetivoGeneral = TruncateCadena(utf8Decode(trim($datos['ur_objetivoGeneral'])), $longitud);

            $this->Cell(5, 5, substr($ur_objetivoGeneral, 0, $longitud), 0, 0, 'L');
            $this->Cell(10, 5, '', 0, 1, 'J');

            $inc3 = 4;
            $lengs3 = 125;
            for ($i = 1; $lengs3 <= 2500; $i++) {
                if (strlen($ur_objetivoGeneral) > $longitud * $i) {
                    $this->SetXY($ix + 10, $ytmp + $inc3);
                    $this->Cell(5, 5, substr($ur_objetivoGeneral, $longitud * $i, $longitud), 0, 0, 'L');
                    $this->Cell(5, 5, '', 0, 1, 'J');
                }
                $inc3 = $inc3 + 4;
                $lengs3 = $lengs3 + 125;
            }

            $this->ln(4);
            $this->CheckPageBreak(25);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array(utf8Decode('OBJETIVO ESTRATÉGICO DE LA UR')));
            $this->ln(2);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));

            $ytmp = $this->GetY();
            $ur_objetivoEstrategico = TruncateCadena(utf8Decode(trim($datos['ur_objetivoEstrategico'])), $longitud);

            $this->Cell(5, 5, substr($ur_objetivoEstrategico, 0, $longitud), 0, 0, 'L');
            $this->Cell(10, 5, '', 0, 1, 'J');

            $inc4 = 4;
            $lengs4 = 125;
            for ($i = 1; $lengs4 <= 2500; $i++) {
                if (strlen($ur_objetivoEstrategico) > $longitud * $i) {
                    $this->SetXY($ix + 10, $ytmp + $inc4);
                    $this->Cell(5, 5, substr($ur_objetivoEstrategico, $longitud * $i, $longitud), 0, 0, 'L');
                    $this->Cell(5, 5, '', 0, 1, 'J');
                }
                $inc4 = $inc4 + 4;
                $lengs4 = $lengs4 + 125;
            }

            $this->ln(4);
            $this->CheckPageBreak(30);
            $this->SetFont('UniviaPro-Bold', 'B', 9);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));
            $this->Row(array('PRODUCTO INSTITUCIONAL DE LA UR'));
            $this->ln(2);
            $this->SetFont('UniviaPro-Regular', '', 7.5);
            $this->SetWidths(array(185));
            $this->SetAligns(array('L'));

            $ytmp = $this->GetY();
            $ur_productoInstitucional = TruncateCadena(utf8Decode(trim($datos['ur_productoInstitucional'])), $longitud);

            $this->Cell(5, 5, substr($ur_productoInstitucional, 0, $longitud), 0, 0, 'L');
            $this->Cell(10, 5, '', 0, 1, 'J');

            $inc5 = 4;
            $lengs5 = 125;
            for ($i = 1; $lengs5 <= 2500; $i++) {
                if (strlen($ur_productoInstitucional) > $longitud * $i) {
                    $this->SetXY($ix + 10, $ytmp + $inc5);
                    $this->Cell(5, 5, substr($ur_productoInstitucional, $longitud * $i, $longitud), 0, 0, 'L');
                    $this->Cell(5, 5, '', 0, 1, 'J');
                }
                $inc5 = $inc5 + 4;
                $lengs5 = $lengs5 + 125;
            }

            $this->CheckPageBreak(25);
        }
    }
    public function Footer($lastPage = true)
    {
        switch ($this->getOrientation()) {
            case 'P':
                $x = 15;
                $distancia = 5;
                break;
            case 'L':
                $x = 30;
                $distancia = 25;
                break;
        }

        if ($this->ur->getNombrePlaneacion() == "" && $this->ur->getCargoPlaneacion() == "") {
            $y = -40;
            $this->SetY($y);
            $this->SetX($x);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode('REVISÓ'), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode('AUTORIZÓ'), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->ln(1);
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($this->ur->getNombreAdministrativo()), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($this->ur->getNombreTitular()), 0, 1, 'C');

            $this->Cell(130, 2, '', 0, 1, 'C');
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);
            $this->Cell(90, 2, utf8Decode($this->ur->getCargoAdministrativo()), 0, 0, 'C');
            $this->Cell(90, 2, utf8Decode($this->ur->getCargoTitular()), 0, 1, 'C');
        } else {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);

            $y = -40;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZÓ'), 0, 'C', false);

            $y = -25;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, utf8Decode($this->ur->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->ur->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->ur->getNombreTitular()), 'T', 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode($this->ur->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->ur->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->ur->getCargoTitular()), 0, 'C');

        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2,
            utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "),
            0, 'L');

        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57, 2, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2 * ($distancia - 2));
        $this->MultiCell(63, 2, date("d/m/Y") . ' - ' . date("H:i:s"), 0, 'R');
    }
}

$pdf = new Pdf_PlaneacionUr('P', 'mm', 'letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_POA);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
switch ($pdf->ccd_id) {
    default:
        $pdf->setOrientation('P');
        break;
}
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = consultaPlaneacionUr($pdf->clasAdm_gpo, $pdf->clasAdm_ur);
$pdf->Content($datos);
$pdf->Output();
