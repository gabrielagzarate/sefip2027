<?php

/**
 * Description of Pdf_MgaComplementos
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 13-Agt-2018
 * Comment: -----
 * Objective: Elabora el reporte de acuerdo a la opcion que se requiera, modificar techos, altas de previsiones
            o ajustes.
    * Regla de Negocio: -----
    * Permits: Esta pantalla no pregunta por permisos de escritura.
    * */
session_start();

require_once dirname(__FILE__) . '/../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/formatoTexto.php';
require_once dirname(__FILE__) . '/../../../lib/TruncateCadena.php';
require_once dirname(__FILE__) . '/../../../lib/mga/nivelMgaComplementos.php';
require_once dirname(__FILE__) . '/../../../ent/Usuario.php';

if (!function_exists('u8d')) {
    function u8d($value)
    {
        return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    }
}

class Pdf_MgaComplementos extends PDF
{
    public $ur;
    protected $ue;
    protected $usuario;
    protected $usr_id;
    protected $ue_id;
    protected $nvj;
    protected $adm_gpo;
    protected $adm_ur;
    public $epg_id;
    public $ecf_id;
    public $ccd_id;
    protected $estadoCosteo;
    protected $firmar;
    protected $longitud;
    protected $ancho;
    protected $imagen;
    protected $cedulaNombre;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        //variables de session
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ue_id = (isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '');
        $this->nvj = (isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : '');
        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '');
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '');

        $this->estadoCosteo = $this->ur->getUr_estadoCosteo();

        if (isset($_POST['firma']) && $_POST['firma'] != 0) {
            $this->firmar = true;
        } else {
            $this->firmar = false;
        }

        if ($this->nvj < 2) {

            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($this->adm_gpo);
            $this->ue->setUr_id($this->adm_ur);

            //Obtener Ue
            if (empty($this->ue_id)) {
                $conexion = new AccesoDatos();
                $prm = [$this->usr_id];
                $sql = 'SELECT ue_id FROM cat_Usuario WHERE usr_id = ?';
                $conexion->conectar();
                $conexion->setSql($sql);
                $conexion->consultar($prm);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $this->ue_id;
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

        $this->ecf_id = (isset($_POST['ecf_id']) ? $_POST['ecf_id'] : '');
        $this->epg_id = (isset($_POST['epg_id']) ? $_POST['epg_id'] : '');
        $this->ccd_id = (isset($_POST['ccd_id']) ? $_POST['ccd_id'] : '');

    }//fin __construct

    public function Header()
    {//Encabezado del documento
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
        switch ($this->ccd_id) {
            case 1:
                $this->cedulaNombre = 'TECHO PRESUPUESTAL';
                break;
            case 2:
                $this->cedulaNombre = 'PREVISIÓN PRESUPUESTAL';
                break;
            case 3:
                $this->cedulaNombre = 'AJUSTE PRESUPUESTAL';
                break;
        }

        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if (!$this->firmar && $this->ur->getEur_id() < 5 && $this->estadoCosteo == 1) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix + 30, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy + 80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix + 150, $iy + 80, 'HOJA DE TRABAJO', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $this->setRouteImg(($this->ccd_id == 1 || $this->ccd_id == 3) ? '114' : $this->ur->getClave());
        if ($this->ccd_id == 4) {
            $this->Image($this->getRouteImg(), $ix + 164, $iy - 1, '100', '18', 'JPG', '');
        } else {
            $this->Image($this->getRouteImg(), $ix + 97, $iy - 1, '100', '18', 'JPG', '');
        }

        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix + 5, $iy + 18, date('Y', strtotime('+1 year')), 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix + 7, $iy + 4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180, 5, u8d('PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO), 0, 1, 'L');

        $ur_nombre = ($this->nvj > 2) ? 'DPR' : 'UR';

        $this->SetXY($ix + 7, $ytmp + 4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $iyt = 4;
        $nombre_ur = TruncateCadena(u8d(trim($ur_nombre . ': ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre())), ($this->longitud - 10));
        $this->Cell(5, 5, substr($nombre_ur, 0, ($this->longitud - 10)), 0, 0, 'L');

        if (strlen($nombre_ur) > ($this->longitud - 10)) {
            $this->SetY($ytmp + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10), ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud - 10) * 2) {
            $iyt += 4;
            $this->SetY($ytmp + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 2, ($this->longitud - 10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud - 10) * 3) {
            $iyt += 4;
            $this->SetY($ytmp + $iyt);
            $this->SetX($ix + 7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud - 10) * 3, ($this->longitud - 10)), 0, 0, 'L');
        }
        $ytmp = $this->GetY();
        $this->SetXY($ix + 7, $ytmp + 4);

        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, u8d($this->cedulaNombre), 0, 1, 'L');

        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen + 33, $ytmp);

        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);
        $this->SetWidths([30,$this->ancho,38]);
        $this->SetAligns(['L','C','C','C','C','R']);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(4);
        $this->SetRelleno('si');
        $this->Row(['CONCEPTO',u8d('DESCRIPCIÓN'),'PRESUPUESTO  $']);
        $this->ln(1);

        $this->SetAltoLinea(1.5);
        $this->SetRelleno('no');
    }

    public function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $pintaEncabezado = true;
        $encabezado = null;
        $bandera = $this->subtEstFed($datos);
        $vecesF = 0;
        $vecesE = 0;
        while ($dato = array_pop($datos)) {
            //del 1 al 7 no se mueven, solo en caso de que cambie el formato
            //el 8 es cuando se imprime la partida especifica
            //en el 9 es el detalle de las cedulas o su agrupacion del costeo
            if ($dato['nivel'] === 1) {
                $encabezado = $dato;
            }
            if ($dato['nivel'] == 10) {
                $agrupar = strtoupper(substr(trim($dato['asr_nombre']), 0, 1));
                if ($agrupar === 'A') {
                    $vecesE++;
                }
                if ($agrupar === 'B') {
                    $vecesF++;
                }
            }
            $this->SetAltoLinea(1.5);
            switch ($dato['nivel']) {
                case 1:
                    if ($this->epg_id == '') {
                        $this->CheckPageBreak(30);
                        $this->ln(1);
                        $this->SetX(10);
                        $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                        $this->SetRelleno('no');
                        $this->SetWidths([40,$this->ancho - 2,30]);
                        $this->SetAligns(['L','L','R']);
                        $this->Row(['','TOTAL UNIDAD RESPONSABLE',number_format($dato['importe'], 2)]);
                    }
                    break;
                case 2:
                    $this->CheckPageBreak(30);
                    $this->ln(4);
                    $this->SetAltoLinea(3);
                    $this->SetBorders('B');
                    $this->SetDrawColor(160, 160, 160);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 7); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['PROGRAMA', $dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno('no');
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                    $this->SetRelleno('no');
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['SUBPROGRAMA',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['UE',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['PY',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['OA',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths([17,18,3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['CVEFIN',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['PG',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths([28, 7, 3,$this->ancho,30]);
                    $this->SetAligns(['L','R','C','L','R']);
                    $this->Row(['PE',$dato['clave'], '-', u8d($dato['nombre']),number_format($dato['importe'], 2)]);
                    $pintaEncabezado = true;
                    break;
                case 10:
                    switch ($this->ccd_id) {
                        case 1:
                            if ($pintaEncabezado) {
                                $this->SetAltoLinea(1.5);
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'F', '1234');
                                $this->SetWidths([  5, 70, 70, 30]);//175
                                $this->SetAligns(['L','L','L','R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['','GRAN FUENTE DE FINANCIAMIENTO','CLASE DE GASTO','IMPORTE']);
                                $this->ln(1);
                            }
                            $this->SetRelleno('no');
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([  5, 70, 70,30]);//175
                            $this->SetAligns(['L','L','L','R']);
                            $this->Row(['',u8d($dato['asr_nombre']),u8d($dato['gbf_nombre']),'$' . number_format($dato['ali_beneficiarios'], 2)]);

                            if ($bandera['estatal'] != 0 && $bandera['estatal'] == $vecesE) {
                                $this->SetRelleno('no');
                                $this->SetFont('UniviaPro-Regular', '', 6);
                                $this->SetWidths([  5, 70, 70,30]);//175
                                $this->SetAligns(['L','L','L','R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['','Subtotal Estatal','','$' . number_format($encabezado['subtotalEst'], 2)]);
                                $vecesE = 99;
                            }
                            if ($bandera['federal'] != 0 && $bandera['federal'] == $vecesF) {
                                $this->SetRelleno('no');
                                $this->SetFont('UniviaPro-Regular', '', 6);
                                $this->SetWidths([  5, 70, 70,30]);//175
                                $this->SetAligns(['L','L','L','R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['','Subtotal Federal','','$' . number_format($encabezado['subtotalFed'], 2)]);
                            }
                            $pintaEncabezado = false;
                            break;
                        case 2:
                            if ($pintaEncabezado) {
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'DF', '1234');
                                $this->SetWidths([  5, 30, 80, 20, 20, 20]);//175
                                $this->SetAligns(['L','L','L','R','R','R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['','Cve. Fin.','Nombre Clave de Financiamiento','PGen','PEsp','Importe']);
                                $this->ln(1);
                            }
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([  5, 30, 80, 20, 20, 20]);//175
                            $this->SetAligns(['L','L','L','R','R','R']);
                            $this->Row(['',u8d($dato['gbf_nombre']),$dato['ali_beneficiarios'],(($dato['ali_cuotaDiaria'] > 0) ? number_format($dato['ali_cuotaDiaria'], 0) : ''),(($dato['ali_diasOtorgados'] > 0) ? number_format($dato['ali_diasOtorgados'], 2) : ''),number_format($dato['total'], 2)]);
                            $pintaEncabezado = false;
                            break;
                        case 3:
                            if ($pintaEncabezado) {
                                $this->SetAltoLinea(1.5);
                                $this->ln(2);
                                $this->SetX(10);
                                $this->SetBorders(0);
                                $this->SetFillColor(221, 228, 233);
                                $this->RoundedRect(15, $this->GetY() - .5, 170, 4, 1, 'F', '1234');
                                $this->SetWidths([  5,9,63,9,63, 9,15]);//175
                                $this->SetAligns(['L','C','L','C','L','C','R']);
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(['','Pg',u8d('Partida genérica'),'Pe',u8d('Partida específica'),'Tipo','Importe']);
                                $this->ln(1);
                            }
                            $this->SetRelleno('no');
                            $this->SetFont('UniviaPro-Regular', '', 6);
                            $this->SetWidths([  5, 9,63,9,63,9,15]);//175
                            $this->SetAligns(['L','C','L','C','L','C','R']);
                            $this->Row(['',$dato['pg_id'],u8d($dato['pg_nombre']),$dato['pe_id'],u8d($dato['pe_nombre']),$dato['tcp_id'],'$' . number_format($dato['total'], 2) ]);
                            $pintaEncabezado = false;
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
    }

    public function subtEstFed($datos)
    {
        $agrupar = 0;
        $federal = 0;
        $estatal = 0;
        $filas = [];
        array_pop($datos);

        foreach ($datos as $valor) {
            $agrupar = strtoupper(substr(trim($valor['asr_nombre']), 0, 1));
            if ($agrupar === 'A') {
                $estatal++;
            } else {
                $federal++;
            }

            /* var_dump($valor);
            var_dump('Estatal:'.$estatal, $federal); */
        }
        $filas = [
            'federal' => $federal,
            'estatal' => $estatal,
        ];
        return $filas;
    }

    public function Footer($lastPage = true)
    {
        $x = $distancia = 0;
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

        if (isset($this->firmar)) {
            $this->SetFont('UniviaPro-Bold', 'B', 6.5);

            $y = -40;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, u8d('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, u8d('REVISÓ'), 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, u8d('AUTORIZÓ'), 0, 'C', false);

            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, u8d($this->ur->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, u8d($this->ur->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 4, u8d($this->ur->getNombreTitular()), 'T', 'C', false);

            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2 * $distancia);
            $this->MultiCell(57, 3, u8d($this->ur->getCargoTitular()), 0, 'C');
        }

        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(
            57,
            2,
            u8d($this->usuario->getUsr_id() . ' - ' . $this->usuario->getUsuario() . ' '),
            0,
            'L'
        );

        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);

        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57, 2, u8d('Página ' . $this->PageNo() . '/{nb}'), 0, 'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia, 2, '', 0, 'C', false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2 * $distancia);
        $this->MultiCell(63,2,date('d/m/Y') . ' - ' . date('H:i:s'),0,'R');
    }
}

$pdf = new Pdf_MgaComplementos('P','mm','letter');
$pdf->SetProtection(['print'],'',PDF_PASSWORD_OWNER_POA);
$pdf->AddFont('UniviaPro-Regular','','UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold','B','UniviaPro-Bold.php');
switch ($pdf->ccd_id) {
    case 1:
        $pdf->setOrientation('P');
        break;
    case 2:
        $pdf->setOrientation('P');
        break;
    case 3:
        $pdf->setOrientation('P');
        break;
    default:
        echo 'kym';
        break;
}
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true , 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelMgaComplementos($pdf->ur->getGpo_id(), $pdf->ur->getUr_id(), '', $pdf->epg_id, $pdf->ecf_id, $pdf->ccd_id);
$pdf->Content($datos);
$pdf->Output();
