<?php

/**
 * Description of Pdf_Cedula
 *
 * @author Faustino Cruz, Israel <israel.faustino@finanzasoaxaca.gob.mx>
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 20-ago-2018
 * Comment: Generación de dos nuevos Reportes dentro de ServiciosPersonales donde el ccd_id:
 * case 20 : INVENTARIO DE PLAZAS POR UNIDAD EJECUTORA
 * case 21 : DESPLIEGUE POR RELACIÓN LABORAL
 * Se cambia el nombre de la clase segun el nombre del archivo.
 * Objective: Generar los Reportes de las Cedulas de Presupuestación:
- Cedula Servicios Personales
- Cedula Alimentacion
- Cedula Telefonia
-- cve_id : 1 es convencional/tradicional
-- cve_id : 2 es celular
- Cedula Vehiculos
- Cedula Arrendamiento Y Aseguramiento De Inmuebles
- Cedula Vigilancia
- Cedula Otras Partidas
 * Regla de Negocio:
 *
 * */

session_start();
require_once __DIR__ . '/../../lib/reportetabla.1.8.php';
require_once __DIR__ . '/../../../lib/AccesoDatos.php';
require_once __DIR__ . '/../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../ent/UnidadEjecutora.php';
require_once __DIR__ . '/../../../ent/AuxiliarUnidadResponsable.php';
require_once __DIR__ . '/../../../lib/formatoTexto.php';
require_once __DIR__ . '/../../../lib/poa/presupuestacion/nivelCedula.php';
require_once __DIR__ . '/../../../ent/Usuario.php';
require_once __DIR__ . '/../../../lib/TruncateCadena.php';
require_once __DIR__ . '/../../../lib/helpers.php';
require_once __DIR__ . "/../../../lib/constantesCredenciales.php";


class Pdf_Cedula extends PDF
{
    public UnidadResponsable $ur;
    public $usr_id;
    public $ecf_id;
    public $epg_id;
    public $ue_id;
    public $ccd_id;
    public $ced_id;
    public $rla_id;
    public $cve_id;
    public $estado_costeo;
    public $firmar;
    public $ur_nombre;
    public $firma;
    public UnidadEjecutora $ue;
    public Usuario $usuario;
    public $longitud;
    public $ancho;
    public $imagen;
    public $logo;
    public $cedulaNombre;
    public $nombreUR;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ecf_id = (isset($_POST['ecf_id']) ? $_POST['ecf_id'] : '');
        $this->epg_id = (isset($_POST['epg_id']) ? $_POST['epg_id'] : '');
        $this->ue_id = (isset($_POST['ue_id']) ? $_POST['ue_id'] : '');
        $this->ccd_id = (isset($_POST['ccd_id']) ? $_POST['ccd_id'] : '');
        $this->ced_id = (isset($_POST['ced_id']) ? $_POST['ced_id'] : '');

        if ($this->ccd_id == 21) {
            $this->rla_id = $_POST['rla_id'];
        } else {
            $this->rla_id = 0;
        }
        if ($this->ccd_id == 3 || $this->ccd_id == 5) {
            $this->cve_id = $_POST['cve_id'];
        } else {
            $this->cve_id = 1;
        }
        if ($this->ur->getUr_estadoCosteo() == 1) {
            $this->estado_costeo = true;
        } else {
            $this->estado_costeo = false;
        }
        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }
        $this->ur_nombre = ($_SESSION[_S]['nvj_id'] > 3) ? 'DPR' : 'UR';

        if ($this->firmar) {
            $this->firma = new AuxiliarUnidadResponsable();
            $this->firma->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->firma->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->firma->select();
        }
        if ($_SESSION[_S]['nvj_id'] < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);

            //Obtener Ue
            if (empty($_SESSION[_S]['ue_id'])) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = 'SELECT ue_id FROM cat_Usuario WHERE usr_id= :usr';
                $parametros = [
                    'usr' => $_SESSION[_S]['usr_id'],
                ];
                $conexion->setSql($sql);
                $conexion->consultar($parametros);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $_SESSION[_S]['ue_id'];
            }

            $this->ue->setUe_id($ue);
            $this->ue->select();
        } else {
            //$this->ue
        }

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }
    }

    public function Header()
    { //Encabezado del documento

        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 55;
                $this->ancho = 125;
                $this->imagen = 190;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 190;
                $this->imagen = 255;
                $this->logo = 186;
                break;
        }

        switch ($this->ccd_id) {
            case 1:
                $this->cedulaNombre = 'ALIMENTACIÓN';
                break;
            case 2:
                $this->cedulaNombre = 'SERVICIOS PERSONALES';
                break;
            case 3:
                $this->cedulaNombre = 'TELEFONÍA';
                break;
            case 4:
                $this->cedulaNombre = 'VEHÍCULO';
                break;
            case 5:
                $this->cedulaNombre = 'ARRENDAMIENTO( TERRENOS Y EDIFICIOS )';
                break;
            case 6:
                $this->cedulaNombre = 'SEGURO/FIANZA( TERRENOS Y EDIFICIOS )';
                break;
            case 7:
                $this->cedulaNombre = 'VIGILANCIA';
                break;
            case 9:
                $this->cedulaNombre = 'OTRAS PARTIDAS';
                break;
            case 20:
                $this->cedulaNombre = 'INVENTARIO DE PLAZAS';
                break;
            case 21:
                $this->cedulaNombre = 'DESPLIEGUE POR RELACIÓN LABORAL';
                break;
            default:
                echo 'kym->cedula indefinida';
                break;
        }
        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if (!$this->firmar && $this->ur->getEur_id() < 5 && $this->estado_costeo) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 20, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 80, $iy + 220, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 140, $iy + 100, 'HOJA DE TRABAJO', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $year = APP_EJERCICIO;

        //setter images name
        $this->Image(__DIR__ . '/../../../img/' . LOGO_COLOR, $this->ccd_id == 4 ? $ix + 164 : $ix + 97, $iy - 1, '100', '18', 'PNG', '');

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
        $this->nombreUR = TruncateCadena(utf8Decode(trim($this->ur_nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), $this->longitud);

        $this->Cell(5, 5, substr($this->nombreUR, 0, $this->longitud), 0, 0, 'L');
        $this->Cell(163, 5, '', 0, 1, 'R');

        if (strlen($this->nombreUR) > $this->longitud) {
            $this->SetXY($ix + 7, $ytmp + 4);
            $this->Cell(
                5,
                5,
                substr($this->nombreUR, $this->longitud, $this->longitud),
                0,
                0,
                'L'
            );
            $this->Cell(163, 5, '', 0, 1, 'R');
        }
        if (strlen($this->nombreUR) > $this->longitud * 2) {
            $this->SetXY($ix + 7, $ytmp + 8);
            $this->Cell(
                5,
                5,
                substr($this->nombreUR, $this->longitud * 2, $this->longitud),
                0,
                0,
                'L'
            );
            $this->Cell(163, 5, '', 0, 1, 'R');
        }

        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp - 1);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode($this->cedulaNombre), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->setX(10);
        $this->setDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 12, $ytmp);

        if ($this->ccd_id != 20) {
            $this->ln(3);
            $this->setDrawColor(170, 170, 170);
            $this->SetFillColor(199, 209, 219);
            $this->setX(10);
            $this->SetWidths([30, $this->ancho, 38]);
            $this->SetAligns(['L', 'L', 'R']);
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->setAltoLinea(4);
            $this->SetRelleno('si');
            $this->Row(['CONCEPTO', utf8Decode('DESCRIPCIÓN'), 'PRESUPUESTO  $']);
            $this->ln(1);
            $this->SetAltoLinea(2);
            $this->SetRelleno('no');
        }

        if ($this->ccd_id == 21 && $this->PageNo() > 1) {
            $this->ln(2);
            $this->SetFillColor(221, 228, 233);
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
            $this->SetRelleno('no');
        }
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $pintaEncabezado = true;
        // Variables para reporte servicios personales
        $totactivas = 0;
        $totvacantes = 0;
        $totalitigio = 0;
        $totgeneral = 0;
        $relacion = '';
        $nivel = '';
        $bolrelacion = false;
        $bolnivel = false;
        $pintar = true;
        $header_relacion_laboral = false;
        $footer_relacion_laboral = false;

        while ($dato = array_pop($datos)) {
            //del 1 al 7 no se mueven, solo en caso de que cambie el formato
            //el 8 es cuando se imprime la partida especifica
            //en el 9 es el detalle de las cedulas o su agrupacion del costeo
            switch ($dato['nivel']) {
                case 1:
                    if ($this->epg_id == '') {
                        $this->CheckPageBreak(30);
                        $this->ln(1);
                        $this->SetX(10);
                        $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                        $this->SetRelleno('no');
                        $this->SetFillColor(192, 192, 192);
                        $this->SetWidths([40, $this->ancho - 2, 30]);
                        $this->SetAligns(['L', 'L', 'R']);
                        $this->Row(['', 'TOTAL UNIDAD RESPONSABLE', number_format($dato['importe'], 2)]);
                    }
                    $row = isset($dato['rows']) ? $dato['rows'] : '';
                    if ($row == 0 && $this->ccd_id == 20) {
                        $header_relacion_laboral = true;
                    }

                    break;
                case 2:
                    $this->CheckPageBreak(30);
                    $this->ln(2);
                    $this->setAltoLinea(3);
                    $this->setBorders('B');
                    $this->setDrawColor(160, 160, 160);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetFillColor(235, 235, 235);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno('no');
                    $this->setAltoLinea(1.5);
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetFillColor(192, 192, 192);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['SUBPROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['UE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PY', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['OA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([17, 18, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['CVEFIN', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PG', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->setBorders('B');
                    $this->setDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetFillColor(240, 240, 240);
                    $this->SetWidths([28, 7, 4, $this->ancho, 29]);
                    $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                    $this->Row(['PE', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 10:
                    switch ($this->ccd_id) {
                        case 1:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 50, 40, 20, 20, 20, 20]); //175
                                $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', utf8Decode('ÁREA EN QUE SE OTORGA'), 'GRUPO', 'BENEFICIARIOS', 'CUOTA', 'No DIAS', 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 50, 40, 20, 20, 20, 20]); //175
                            $this->SetAligns(['L', 'L', 'L', 'R', 'R', 'R', 'R']);
                            $this->Row(['', utf8Decode($dato['asr_nombre']), utf8Decode($dato['gbf_nombre']), number_format($dato['ali_beneficiarios'], 0), number_format($dato['ali_cuotaDiaria'], 2), $dato['ali_diasOtorgados'], number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 2:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 30, 60, 20, 40, 20]); //175
                                $this->SetAligns(['L', 'C', 'L', 'L', 'L', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'PARTIDA', 'NOMBRE', 'ESTADO', utf8Decode('RELACIÓN LABORAL'), 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 30, 60, 20, 40, 20]); //175
                            $this->SetAligns(['L', 'C', 'L', 'L', 'L', 'R']);
                            $this->Row(['', $dato['pg_id'] . ' - ' . $dato['pe_id'], utf8Decode($dato['pe_nombre'], ), utf8Decode($dato['epz_nombre']), utf8Decode($dato['rla_nombre']), number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 3:
                            switch ($dato['pe_id']) {
                                case 304:
                                    if ($pintaEncabezado) {
                                        $this->ln(2);
                                        $this->setX(10);
                                        $this->setBorders(0);
                                        $this->SetFillColor(221, 228, 233);
                                        $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                        $this->SetWidths([5, 105, 25, 20, 20]); //175
                                        $this->SetAligns(['L', 'L', 'C', 'R', 'R']);
                                        $this->SetFont('UniviaPro-Bold', 'B', 6);
                                        $this->Row(['', utf8Decode('UBICACIÓN'), utf8Decode('NÚMERO'), 'MENSUALIDAD', 'ANUAL']);
                                    }
                                    $this->CheckPageBreak(10);
                                    $this->SetFont('UniviaPro-Regular', '', 6);
                                    $this->SetWidths([5, 105, 25, 20, 20]); //175
                                    $this->SetAligns(['L', 'L', 'C', 'R', 'R']);
                                    $this->Row(['', utf8Decode($dato['ubicacion']), FormatoTexto(utf8Decode($dato['telefono']), 'telefono'), number_format($dato['mensualidad'], 2), number_format($dato['total'], 2)]);
                                    break;
                                case 305:
                                    if ($pintaEncabezado) {
                                        $this->ln(2);
                                        $this->setX(10);
                                        $this->setBorders(0);
                                        $this->SetFillColor(221, 228, 233);
                                        $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                        $this->SetWidths([5, 50, 55, 25, 20, 20]); //175
                                        $this->SetAligns(['L', 'L', 'L', 'C', 'R', 'R']);
                                        $this->SetFont('UniviaPro-Bold', 'B', 6);
                                        $this->Row(['', utf8Decode('CARGO DEL SERVIDOR'), utf8Decode('UBICACIÓN'), utf8Decode('NÚMERO'), 'MENSUALIDAD', 'ANUAL']);
                                    }
                                    $this->CheckPageBreak(10);
                                    $this->SetFont('UniviaPro-Regular', '', 6);
                                    $this->SetWidths([5, 50, 55, 25, 20, 20]); //175
                                    $this->SetAligns(['L', 'L', 'L', 'C', 'R', 'R']);
                                    $this->Row(['', utf8Decode($dato['descripcion']), utf8Decode($dato['ubicacion']), FormatoTexto(utf8Decode($dato['telefono']), 'telefono'), number_format($dato['mensualidad'], 2), number_format($dato['total'], 2)]);
                                    break;
                            }
                            $pintaEncabezado = false;
                            break;
                        case 4:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(199, 215, 219);
                                $this->RoundedRect(15, $this->GetY() - .5, 240, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 130, 80, 30]); //255
                                $this->SetAligns(['L', 'C', 'C', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', utf8Decode('Vehículo o Maquinaria'), 'Total Presupuesto', utf8Decode('Total Vehículo')]);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 240, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 30, 35, 25, 20, 18, 20, 20, 22, 20, 30]); //255
                                $this->SetAligns(['L', 'R', 'L', 'C', 'C', 'C', 'R', 'R', 'R', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'SERIE', 'USO', utf8Decode('UBICACIÓN'), 'TIPO', 'PLACA', 'IMPUESTO', 'SEGURO', 'MANTENIMIENTO', 'COMBUSTIBLE', '']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 30, 35, 25, 20, 20, 20, 20, 20, 20, 30]); //255
                            $this->SetAligns(['L', 'R', 'L', 'C', 'C', 'C', 'R', 'R', 'R', 'R', 'R']);
                            $this->Row([
                                '', utf8Decode($dato['pqv_id']), utf8Decode($dato['uso']), utf8Decode($dato['ubicacion']), utf8Decode($dato['tipo']), $dato['placas'],
                                number_format($dato['impuesto'], 2), number_format($dato['seguro'], 2), number_format($dato['mantenimiento'], 2), number_format($dato['combustible'], 2), number_format($dato['total'], 2),
                            ]);
                            $pintaEncabezado = false;
                            break;
                        case 5:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 40, 40, 20, 20, 30, 20]); //175
                                $this->SetAligns(['L', 'L', 'L', 'C', 'C', 'L', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', utf8Decode('USO'), utf8Decode('UBICACIÓN'), 'INICIO', 'FIN', 'OBSERVACIONES', 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 40, 40, 20, 20, 30, 20]); //175
                            $this->SetAligns(['L', 'L', 'L', 'C', 'C', 'L', 'R']);
                            $time = strtotime($dato['arr_inicioPeriodo']);
                            $newInicio = date('d-m-Y', $time);
                            $time = strtotime($dato['arr_terminoPeriodo']);
                            $newFin = date('d-m-Y', $time);
                            $this->Row(['', utf8Decode($dato['arr_uso']), utf8Decode($dato['arr_ubicacion']), $newInicio, $newFin, utf8Decode($dato['arr_observaciones']), number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 6:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 70, 20, 20, 20, 20, 20]); //175
                                $this->SetAligns(['L', 'L', 'C', 'C', 'L', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'BIEN QUE REQUIERE EL SEGURO', 'INICIO', utf8Decode('TÉRMINO'), 'TIPO', 'IMPORTE ', 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 70, 20, 20, 20, 20, 20]); //175
                            $this->SetAligns(['L', 'L', 'C', 'C', 'L', 'R', 'R']);
                            $sefInicioPeriodo = strtotime($dato['sef_inicioPeriodo']);
                            $inicioPeriodo = date('d-m-Y', $sefInicioPeriodo);
                            $sefTerminoPeriodo = strtotime($dato['sef_terminoPeriodo']);
                            $terminoPeriodo = date('d-m-Y', $sefTerminoPeriodo);
                            $this->Row(['', utf8Decode($dato['sef_concepto']), $inicioPeriodo, $terminoPeriodo, utf8Decode($dato['sef_tipoPago']), number_format($dato['importe'], 2), number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 7:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 70, 40, 20, 20, 20]); //175
                                $this->SetAligns(['L', 'L', 'C', 'C', 'L', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', utf8Decode('ÁREA DE VIGILANCIA'), utf8Decode('TIPO DE CONTRATACIÓN'), 'ELEMENTOS', 'MENSUAL', 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->ln(1.5);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 70, 40, 20, 20, 20]); //175
                            $this->SetAligns(['L', 'L', 'C', 'C', 'L', 'R', 'R']);
                            $this->Row(['', utf8Decode($dato['svg_area']), utf8Decode($dato['tct_nombre']), number_format($dato['cantidad'], 0), number_format($dato['tct_costoMensual'], 2), number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 9:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->setX(10);
                                $this->setBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([5, 80, 45, 25, 20]); //175
                                $this->SetAligns(['L', 'L', 'L', 'R', 'R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['', 'CONCEPTO', 'UNIDAD DE MEDIDA', 'PRECIO UNITARIO', 'TOTAL']);
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([5, 80, 45, 25, 20]); //175
                            $this->SetAligns(['L', 'L', 'L', 'R', 'R']);
                            $this->Row(['', utf8Decode($dato['otp_archivo']), utf8Decode($dato['otp_unidadMedida']), number_format($dato['otp_costo'], 2), number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 20:
                            if ($pintaEncabezado) {
                                $this->ln(4);
                                $this->setDrawColor(170, 170, 170);
                                $this->SetFillColor(199, 209, 219);
                                $this->setX(10);
                                $this->SetWidths([112, 20, 20, 20, 20]); //175
                                $this->SetAligns(['L', 'C', 'C', 'C', 'C']);
                                $this->SetFont('UniviaPro-Bold', 'B', 7);
                                $this->SetRelleno('si');
                                $this->setAltoLinea(4);
                                $this->Row([utf8Decode('RELACIÓN LABORAL'), 'ACTIVO', 'VACANTE', 'LITIGIO', 'TOTAL']);
                                $footer_relacion_laboral = true;
                            }
                            $this->CheckPageBreak(10);
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->ln(1);
                            $this->setAltoLinea(2);
                            $this->SetRelleno('no');
                            $this->setBorders('B');
                            $this->setDrawColor(200, 200, 200);
                            $this->SetWidths([111.5, 20, 20, 20, 20]); //175
                            $this->SetAligns(['L', 'C', 'C', 'C', 'C']);
                            $this->Row([utf8Decode($dato['rla_nombre']), $dato['Activas'], $dato['Vacantes'], $dato['Litigio'], $dato['total']]);
                            $totactivas += $dato['Activas'];
                            $totvacantes += $dato['Vacantes'];
                            $totalitigio += $dato['Litigio'];
                            $totgeneral += $dato['total'];
                            $pintaEncabezado = false;

                            break;
                        case 21:

                            if ($relacion != $dato['clave']) {
                                if ($bolrelacion == true) {
                                    $relacion = '';
                                    $bolrelacion = false;
                                    $nivel = '';
                                    $bolnivel = false;
                                    $pintar = true;
                                }
                                if ($dato['nivel_relacion'] == 1) {
                                    $this->CheckPageBreak(30);
                                    $this->setBorders(0);
                                    $this->SetFillColor(199, 209, 219);
                                    $this->SetRelleno('si');
                                    $this->setDrawColor(220, 220, 220);
                                    $this->SetX(10);
                                    $this->SetFont('UniviaPro-Bold', 'B', 8);
                                    $this->SetWidths([33, 80, 20, 20, 20, 20]);
                                    $this->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
                                    $this->Row([utf8Decode($dato['nrl_clave']), utf8Decode($dato['nombre']), number_format($dato['inp_activas'], 0), number_format($dato['inp_vacantes'], 0), number_format($dato['inp_litigio'], 0), number_format($dato['total'], 0)]);
                                    $relacion = $dato['clave'];
                                    $bolrelacion = true;
                                    $this->ln(2);
                                }
                            }

                            if ($nivel != $dato['clave']) {
                                if ($bolnivel == true) {
                                    $nivel = '';
                                    $bolnivel = false;
                                }

                                if ($dato['nivel_relacion'] == 2) {
                                    if ($pintar == true) {

                                        $this->SetFillColor(221, 228, 233);
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
                                        $pintar = false;
                                    }

                                    if (isset($dato['nombre'])) {
                                        $nombreDescripcion = utf8Decode($dato['nombre']);
                                    } else {
                                        $nombreDescripcion = '--';
                                    }

                                    $this->SetRelleno('no');
                                    $this->CheckPageBreak(30);
                                    $this->SetFillColor(255, 255, 255);
                                    $this->setBorders('B');
                                    $this->setDrawColor(220, 220, 220);
                                    $this->SetX(10);
                                    $this->SetAltoLinea(2);
                                    $this->SetFont('UniviaPro-Regular', '', 6);
                                    $this->SetWidths([33, 80, 20, 20, 20, 20]);
                                    $this->SetAligns(['L', 'L', 'C', 'C', 'C', 'R']);
                                    $this->Row([
                                        utf8Decode($dato['nrl_clave']), $nombreDescripcion,
                                        $dato['inp_activas'], $dato['inp_vacantes'], $dato['inp_litigio'], $dato['total'],
                                    ]);
                                    $nivel = $dato['clave'];
                                    $bolnivel = true;
                                }
                            }

                            break;
                        default:
                            echo 'kym->cedula indefinida';
                            break;
                    }
                    break;
                default:
                    echo 'kym';
                    break;
            }
        }
        // Dibujamos Row solamente para case : 20
        if ($header_relacion_laboral) {
            $this->ln(4);
            $this->setDrawColor(170, 170, 170);
            $this->SetFillColor(199, 209, 219);
            $this->setX(10);
            $this->SetWidths([112, 20, 20, 20, 20]); //175
            $this->SetAligns(['L', 'C', 'C', 'C', 'C']);
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->SetRelleno('si');
            $this->setAltoLinea(4);
            $this->Row([utf8Decode('RELACIÓN LABORAL'), 'ACTIVO', 'VACANTE', 'LITIGIO', 'TOTAL']);
            $this->ln(20);
            $footer_relacion_laboral = true;
        }
        if ($footer_relacion_laboral) {
            $this->ln(2);
            $this->setDrawColor(170, 170, 170);
            $this->SetFillColor(199, 209, 219);
            $this->setX(10);
            $this->setAltoLinea(4);
            $this->SetWidths([112, 20, 20, 20, 20]); //175
            $this->SetAligns(['L', 'C', 'C', 'C', 'C']);
            $this->SetFont('UniviaPro-Bold', 'B', 7);
            $this->SetRelleno('si');
            $this->Row(['TOTAL DE LA PLANTILLA DE PERSONAL', $totactivas, $totvacantes, $totalitigio, $totgeneral]);
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

        if (isset($this->firma)) {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);

            $y = -40;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->setX($x);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->setX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->setX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->setX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode($this->usuario->getUsr_id() . ' - ' . $this->usuario->getUsuario() . ' '), 0, 'L');

        $this->SetY($y);
        $this->setX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 57 + $distancia);
        $this->MultiCell(57, 2, utf8Decode('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');

        $this->SetY($y);
        $this->setX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->setX($x + 104 + 2 * $distancia);
        $this->MultiCell(63, 2, date('d/m/y') . ' - ' . date('H:i:s'), 0, 'R');
    }
}

$pdf = new Pdf_Cedula('L', 'mm', 'letter');
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_POA);

if ($pdf->ccd_id == 4) {
    $pdf->setOrientation('L');
} else {
    $pdf->setOrientation('P');
}

$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->setAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelCedula($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), $pdf->ue_id, $pdf->epg_id, $pdf->ecf_id, $pdf->ccd_id, $pdf->rla_id, $pdf->cve_id, $pdf->ced_id);

$pdf->Content($datos);
$pdf->Output();
