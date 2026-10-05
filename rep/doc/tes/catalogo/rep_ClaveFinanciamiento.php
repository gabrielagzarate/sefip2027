<?php
/**
 * Description of Pdf_ClaveFinanciamiento
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 05-Sep-2019
 * Comment: -----
 * Objective: Elabora el reporte de acuerdo a la opcion que se requiera, clave financiamiento.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();

require_once dirname(__FILE__).'/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__).'/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__).'/../../../../lib/helpers.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__).'/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__).'/../../../../lib/formatoTexto.php';
require_once dirname(__FILE__).'/../../../../lib/TruncateCadena.php';
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/nivelClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/funcionesClaveFinanciamiento.php';
require_once dirname(__FILE__).'/../../../../ent/Usuario.php';
    
class Pdf_ClaveFinanciamiento extends PDF
{
    public $presupuesto;
    public $tesoreria;
    public $ue;
    public $firmar;
    public $marca;
    public $usr_id;
    public $usuario;
    public $ccd_id;
    public $primeraParteClave;
    public $segundaParteClave;
    public $tipoRecurso;
    public $firma;
    protected $longitud;
    protected $ancho;
    protected $imagen;
    protected $cedulaNombre;
    protected $epg_id;

    /**
     * Constructor
     *
     * @ccd_id  int
     * Determina que reportes se mostrara
     * 1 = Sub-Fuente
     * 2 = Origen
     * 3 = Sub-Origen
     * 4 = Clave Financiamiento
     */
    public function  __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {
        parent::__construct($orientation, $unit, $format, $obj);
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->presupuesto = (tienePermiso("T.6.3.99") || tienePermiso("S.6.4.99") || tienePermiso("C.2.11.99"));
        $this->tesoreria = (tienePermiso("T.6.3.100") || tienePermiso("S.6.4.100") || tienePermiso("C.2.11.100"));
        $this->firmar = true;
        $this->marca = ($_SERVER['SERVER_ADDR'] !="sefip.finanzasoaxaca.gob.mx") ? false: true;

        if ($_SESSION[_S]['nvj_id'] < 2) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['gpo_id']);
            $this->ue->setUr_id($_SESSION[_S]['ur_id']);

            //Obtener Ue
            if (empty($_SESSION[_S]['ue_id'])) {
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $sql = "SELECT ue_id FROM cat_Usuario WHERE usr_id = ?";
                $conexion->setSql($sql);
                $conexion->consultar([$this->usr_id]);
                $conexion->desconectar();
                $dato = $conexion->extraer();
                $ue = $dato['ue_id'];
            } else {
                $ue = $_SESSION[_S]['ue_id'];
            }
            $this->ue->setUe_id($ue);
            $this->ue->select();
        }
        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario( );
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }

        $this->primeraParteClave = (isset($_POST['primeraParteClave']) ? $_POST['primeraParteClave'] : '');
        $this->segundaParteClave = (isset($_POST['segundaParteClave']) ? $_POST['segundaParteClave'] : '');
        $this->tipoRecurso = (isset($_POST['tipoRecurso']) ? $_POST['tipoRecurso']:'');
        $this->ccd_id = (isset($_POST['ccd_id']) ? $_POST['ccd_id']:'');
    }
    function Header()
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
            default : break;
        }
        switch ($this->ccd_id) {
            case 0:
                $this->cedulaNombre = 'FUENTE DEL RECURSO';
                break;
            case 1:
                $this->cedulaNombre = 'SUB-FUENTE DEL RECURSO';
                break;
            case 2:
                $this->cedulaNombre = 'ORIGEN DEL RECURSO';
                break;
            case 3:
                $this->cedulaNombre = 'SUB-ORIGEN DEL RECURSO';
                break;
            case 4:
                $this->cedulaNombre = 'LISTA DE CLAVE FINANCIAMIENTO';
                break;
            default : break;
        }
        $this->SetMargins(10, 10, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);

        if (!$this->firmar) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix+30, $iy+160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix+150, $iy+160, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix, $iy+80, 'HOJA DE TRABAJO', 45);
            $this->RotatedText($ix+150, $iy+80, 'HOJA DE TRABAJO', 45);
        }

        if ($this->marca) {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('UniviaPro-Bold', 'B', 50);
            $this->SetTextColor(225, 225, 225);
            $this->RotatedText($ix+30, $iy+160, 'PRUEBAS', 45);
            $this->RotatedText($ix+150, $iy+160, 'PRUEBAS', 45);
            $this->RotatedText($ix, $iy+80, 'PRUEBAS', 45);
            $this->RotatedText($ix+150, $iy+80, 'PRUEBAS', 45);
        }

        $this->SetMargins(10, 1, 1, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $this->SetY(2);
        $this->ln(2);
        $ix = $this->GetX();
        $iy = $this->GetY();

        $getClave = 114;

        $this->setRouteImg($getClave);
        $this->Image($this->getRouteImg(), $ix+97, $iy-1, '100' , '18','JPG', '');
        
        $this->SetFont('UniviaPro-Bold', 'B', 16);
        $this->SetTextColor(200, 200, 200);
        $this->RotatedText($ix+5, $iy+18, APP_EJERCICIO, 90);
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->SetXY($ix+7, $iy+4);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
        $this->SetXY($ix+7, $ytmp+4);
        $ytmp = $this->GetY();
        $this->SetFont('UniviaPro-Regular', '', 8);
        $this->Cell(180,5,utf8Decode(getModuloClaveFinanciamiento()), 0, 1, 'L');

        $ur_nombre = ($_SESSION[_S]['nvj_id'] > 2) ? 'DPR' : 'UR';
        $getNombre = 'SECRETARÍA DE FINANZAS';
        $iyt = 0;
        $this->SetXY($ix+7, $ytmp+4);
        $ytmp = $this->GetY(); //Revisar, se repite innecesariamente ?
        $this->SetFont('UniviaPro-Bold', 'B', 8);

        $iyt += 16;
        $nombre_ur = TruncateCadena(utf8Decode(trim($ur_nombre . ": " . $getClave . " - " . $getNombre)), ($this->longitud-10));
        $this->Cell(5, 5, substr($nombre_ur, 0, ($this->longitud-10)), 0, 0, 'L');
        if (strlen($nombre_ur) > ($this->longitud-10)) {
            $this->SetY($iy + $iyt);
            $this->SetX($ix+7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud-10), ($this->longitud-10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud-10) * 2) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix+7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud-10) * 2, ($this->longitud-10)), 0, 0, 'L');
        }
        if (strlen($nombre_ur) > ($this->longitud-10) * 3) {
            $iyt += 4;
            $this->SetY($iy + $iyt);
            $this->SetX($ix+7);
            $this->Cell(5, 5, substr($nombre_ur, ($this->longitud-10) * 3, ($this->longitud-10)), 0, 0, 'L');
        }
        $ytmp = $this->GetY();
        $this->SetXY($ix+7, $ytmp+4);
        
        $this->SetFont('UniviaPro-Bold', 'B', 8);
        $this->Cell(180, 5, utf8Decode($this->cedulaNombre), 0, 1, 'L');
        
        $ytmp = $this->GetY();
        $this->SetX(10);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $ytmp, $this->imagen+33, $ytmp);
        
        $this->ln(4);
        $this->SetDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->SetX(10);
        $this->SetWidths(array(30, $this->ancho+38));
        $this->SetAligns(array('L', 'L'));
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->SetAltoLinea(4);
        $this->SetRelleno("si");
        $this->Row(array(utf8Decode('CONCEPTO'), utf8Decode('NOMBRE')));
        $this->ln(1);

        $this->SetAltoLinea(1.5);
        $this->SetRelleno("no");

    }
    function Content($datos)
    {
        //realizamos la impresion de acuerdo al nivel(aqui no se procesa la informacion, el array debe estar procesado y ordenado
        $pintaEncabezado = true;
        while ($dato = array_pop($datos)) {
            //del 1 al 7 no se mueven, solo en caso de que cambie el formato
            //el 8 es cuando se imprime la partida especifica
            //en el 9 es el detalle de las cedulas o su agrupacion del costeo
            $this->SetAltoLinea(1.5);
            switch ($dato['nivel']) {
                case 1:
                    if ($this->epg_id == '') {
                        $this->CheckPageBreak(30);
                        $this->ln(1);
                        $this->SetX(10);
                        $this->SetFont('UniviaPro-Bold','B',7); ///tipo de letra para cada titulo
                        $this->SetRelleno("no");
                        $this->SetWidths(array(40, $this->ancho-2, 30));
                        $this->SetAligns(array('L', 'L', 'R'));
                        $this->Row(array('', 'TOTAL UNIDAD RESPONSABLE', number_format($dato['importe'], 2)));
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
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L', 'R', 'C', 'L', 'R'));
                    $this->Row(array('PROGRAMA', $dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $this->SetFillColor(255, 255, 255);
                    $this->SetRelleno("no");
                    $pintaEncabezado = true;
                    break;
                case 3:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6); ///tipo de letra para cada titulo
                    $this->SetRelleno("no");
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('SUBPROGRAMA',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 4:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(200, 200, 200);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold','B',6);
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('UE',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 5:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('PY',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 6:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3, $this->ancho, 30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('OA',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 7:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Bold', 'B', 6);
                    $this->SetWidths( array(17,18,3,$this->ancho,30) );
                    $this->SetAligns( array('L','R','C','L','R') );
                    $this->Row(array('CVEFIN',$dato['clave'], '-', utf8Decode($dato['nombre']),number_format($dato['importe'],2)));
                    $pintaEncabezado = true;
                    break;
                case 8:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('PG',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 9:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('B');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetWidths(array(28, 7, 3,$this->ancho,30));
                    $this->SetAligns(array('L','R','C','L','R'));
                    $this->Row(array('PE',$dato['clave'], '-', utf8Decode($dato['nombre']), number_format($dato['importe'], 2)));
                    $pintaEncabezado = true;
                    break;
                case 10:
                    $this->CheckPageBreak(5);
                    if ($pintaEncabezado) {
                        $this->SetAltoLinea(1.5);
                        $this->ln(2);
                        $this->SetX(10);
                        $this->SetBorders(0);
                        $this->SetFillColor(221, 228, 233);
                        $this->RoundedRect(15, $this->GetY()-.5, 170, 4, 1, 'F', '1234');
                        if ($this->ccd_id == 4) {
                            if ($this->presupuesto) {
                                $this->SetWidths(array(15, 30, 90, 20, 20));
                                $this->SetAligns(array('L', 'L', 'L', 'L', 'L'));
                                $this->SetFont('UniviaPro-Bold','B',6);
                                $this->Row(array('',utf8Decode($dato['nombreColumna']),'NOMBRE','POA',utf8Decode('DIVISIÓN')));
                            } elseif ($this->tesoreria) {
                                $this->SetWidths(array(15, 30, 110, 20));
                                $this->SetAligns(array('L', 'L', 'L', 'L'));
                                $this->SetFont('UniviaPro-Bold','B',6);
                                $this->Row(array('',utf8Decode($dato['nombreColumna']),'NOMBRE','PDP'));
                            } else {
                                $this->SetWidths(array(15, 30, 130));
                                $this->SetAligns(array('L', 'L', 'L'));
                                $this->SetFont('UniviaPro-Bold', 'B', 6);
                                $this->Row(array('',utf8Decode($dato['nombreColumna']),'NOMBRE'));
                            }
                        } else {
                            $this->SetWidths(array(15, 30, 130));
                            $this->SetAligns(array('L', 'L', 'L'));
                            $this->SetFont('UniviaPro-Bold', 'B', 6);
                            $this->Row(array('',utf8Decode($dato['nombreColumna']),'NOMBRE'));
                        }
                        $this->ln(1);
                    }
                    $this->SetRelleno("no");
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    if ($this->ccd_id == 4) {
                        if ($this->presupuesto) {
                            $this->SetWidths(array(15, 30, 90, 20, 20));
                            $this->SetAligns(array('L', 'L', 'L', 'L', 'L'));
                            $this->Row(array('',utf8Decode($dato['clave']),utf8Decode($dato['nombre']), utf8Decode($dato['ecf_poa']), utf8Decode($dato['ecf_division'])));
                        } elseif ($this->tesoreria) {
                            $this->SetWidths(array(15, 30, 110, 20));
                            $this->SetAligns(array('L', 'L', 'L', 'L'));
                            $this->Row(array('',utf8Decode($dato['clave']),utf8Decode($dato['nombre']), utf8Decode($dato['pdp_id'])));
                        } else {
                            $this->SetWidths(array(15, 30, 130));
                            $this->SetAligns(array('L', 'L', 'L'));
                            $this->Row(array('',utf8Decode($dato['clave']),utf8Decode($dato['nombre'])));
                        }
                    } else {
                        $this->SetWidths(array(15, 30, 130));
                        $this->SetAligns(array('L', 'L', 'L'));
                        $this->Row(array('',utf8Decode($dato['clave']),utf8Decode($dato['nombre'])));
                    }
                    $pintaEncabezado = false;
                    break;
                break;
                case 11:
                    $this->CheckPageBreak(30);
                    $this->SetBorders('BT');
                    $this->SetDrawColor(220, 220, 220);
                    $this->SetX(10);
                    $this->SetFont('UniviaPro-Regular', '', 6);
                    $this->SetRelleno("no");
                    $this->SetWidths(array(30,$this->ancho+38));
                    $this->SetAligns(array('L','L'));
                    $this->Row(array(utf8Decode($dato['nombreColumna']), utf8Decode($dato['clave'].'-'.$dato['nombre'])));
                    $pintaEncabezado = true;
                break;
                default : break;
            }
        }
    }
    function Footer($lastPage = true)
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
            default : break;
        }
        
        if (isset($this->firma)) {
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
            $this->SetX($x + 114 + 2*$distancia);
            $this->MultiCell(57, 3, utf8Decode('AUTORIZÓ'), 0, 'C', false);
            
            $y = -20;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreAdministrativo()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombrePlaneacion()), 'T', 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 4, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2*$distancia);
            $this->MultiCell(57, 4, utf8Decode($this->firma->getNombreTitular()), 'T', 'C', false);
            
            $y = -15;
            $this->SetY($y);
            $this->SetX($x);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoAdministrativo()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 57);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 57 + $distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoPlaneacion()), 0, 'C');
            $this->SetY($y);
            $this->SetX($x + 114 + $distancia);
            $this->MultiCell($distancia, 3, '', 0, 'C', false);
            $this->SetY($y);
            $this->SetX($x + 114 + 2*$distancia);
            $this->MultiCell(57, 3, utf8Decode($this->firma->getCargoTitular()), 0, 'C');
        
        }
        
        $this->SetFont('univiapro', '', 5);
        $y = -7;
        $x = $x + 4;
        $this->SetY($y);
        $this->setX($x);
        $this->MultiCell(57, 2, utf8Decode($this->usuario->getUsr_id()." - ".$this->usuario->getUsuario()." "), 0, 'L');

        $this->SetY($y);
        $this->SetX($x + 57);
        $this->MultiCell($distancia,2,'',0,'C',false);

        $this->SetY($y);
        $this->SetX($x + 57 + $distancia);
        $this->MultiCell(57,2,utf8Decode('Página '.$this->PageNo().'/{nb}'),0,'C');
        $this->SetY($y);
        $this->SetX($x + 114 + $distancia);
        $this->MultiCell($distancia,2,'',0,'C',false);
        $this->SetY($y);
        $this->SetX($x + 114 + 2*($distancia-2));
        $this->MultiCell(63,2,date("d/m/Y").' - '.date("H:i:s"),0,'R');
        
    }
}

$pdf = new Pdf_ClaveFinanciamiento('P', 'mm', 'letter');
$pdf->SetProtection(array('print', 'annot-forms'), "", PDF_PASSWORD_OWNER_TES);
$pdf->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
$pdf->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
if ($pdf->ccd_id) {
    $pdf->setOrientation('P');
}
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 40);
$pdf->SetAltoLinea(1.5);
$pdf->AddPage();
$datos = nivelClaveFinanciamiento($pdf->ccd_id, $pdf->primeraParteClave, $pdf->segundaParteClave, $pdf->tipoRecurso, $pdf->presupuesto, $pdf->tesoreria);
$pdf->Content($datos);
$pdf->Output();
