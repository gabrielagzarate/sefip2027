<?php

session_start();

ini_set('max_execution_time', 600); //10 minutes

DEFINE('TAMANO_FUENTE_MINUSCULAS', 5.3);
DEFINE('TAMANO_FUENTE_MAYUSCULAS', 6.3);

//librerias
// Obligatorio para que el reporteador funcione
require_once dirname(__FILE__) . '/../../../lib/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/conciliacion/consolidacion.php';
require_once dirname(__FILE__) . '/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../../lib/scg/UrNombre.php';
require_once dirname(__FILE__) . '/../../../../lib/helpers.php';

// Se incluyen los PHP necesarios en función de lo que se imprime
require_once dirname(__FILE__) . '/../../../../lib/compararCadenas.php';

require_once dirname(__FILE__) . '/../../../../lib/scg/cuentaPublica/fnc_DameArrayVariacionHacienda2025.php';

class reportetabla extends PDF
{
    private $longitud = 100;
    public $longitud2 = 75;
    public $ultBorde = 0;
    public $encabezadoTabla = true;
    public $widths = '';
    public $aligns = '';
    public $borders = '';
    public $fill = false;
    public $firmante1;
    public $firmante2;
    public $firmante3;
    public $cargo1;
    public $cargo2;
    public $cargo3;
    public $leyenda;
    public $incCta;
    public $intLin;
    public $ue;
    public $numUe;
    public $tipoReporte;
    public $mesDesde;
    public $mesHasta;
    public $linea7 = '';
    public $lineas = 0;
    public $linea3 = 0;
    public $ur;
    public $nivel;
    public $ejercicio;
    public $gpo_id;
    public $ur_id;
    public $logo;
    public $ue_id;
    public $sis_id;
    public $men_id;
    public $opc_id;
    public $ancho;
    public $imagen;
    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'letter',
        $obj = null
    ) {
        parent::__construct($orientation, $unit, $format, $obj);

        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo']
        : $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur']
        : $_SESSION[_S]['ur_id'];

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $this->ur->select();

        if (isset($_SESSION[_S]['clasAdm-ue'])) {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
            $this->ue->select();
        } elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
            $this->ue = new UnidadEjecutora();
            $this->ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $this->ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $this->ue->setUe_id(trim($_POST['ue']));
            $this->ue->select();
        } else {
            $this->ue = false;
        }

        $this->ue_id = ($this->ue ? $this->ue->getUe_id() : false);

        $this->sis_id = (isset($_POST['sis_id']) ? $_POST['sis_id'] : '');
        $this->men_id = (isset($_POST['men_id']) ? $_POST['men_id'] : '');
        $this->opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

        // -----------------------------------------------------------------------------------------
        // Paso 1: Aquí preparamos las opciones globales y variables que usaremos en la sección CONTENT()
        // -----------------------------------------------------------------------------------------
        // Variables comunes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;

        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';


        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte']
            : 1);
        $this->tipoReporte = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte']
            : 1);


        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde']
        : -1; //Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta']
        : -1; //Informacion de acuerdo al mes
        $this->ejercicio = '';
        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        } else {
            //obtenemos los datos de la UR del ejercicio solicitado
        }

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = 2.5;
        }
    }

    // Encabezado del documento
    public function Header()
    {
        $ix = 0;
        $iy = 0;

        $this->longitud = 60;
        $this->ancho = 175;
        $this->imagen = 195;
        $this->logo = 122;

        $linea1 = utf8Decode('Gobierno del Estado de Oaxaca');

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $texto = 'Dirección de Contabilidad Gubernamental';
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $texto = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial']
                    : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $texto = $this->ur->getClave() . ' - ' . UrNombre::toLowerCase($this->ur->getNombre());
                    } else {
                        $texto = $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . UrNombre::toLowerCase($this->ur['ur_nombre']);
                    }
                    break;
            }
        } else {
            if ($this->ejercicio == '') {
                $texto = ('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                    $texto = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                }
            } else {
                $texto = ('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
                if (mb_detect_encoding($texto, 'UTF-8') != 'UTF-8') {
                    $texto = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
                }
            }
        }

        $texto2 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' AND ue_id = ' . $this->ue . ';
            ');
            $ejecutora->consultar();
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
        }

        if ($_POST['tipoReporte'] != 3 && $_SESSION[_S]['nvj_id'] > 2) {
            $linea2 = utf8Decode($texto);
        }

        if ($this->ue != '' && $_SESSION[_S]['nvj_id'] > 2) {
            $linea3 = utf8Decode($texto2);
        }

        $linea4 = utf8Decode('Estado de Variación en la Hacienda Pública');
        $linea4 = utf8Decode('Estado de Variación en la Hacienda Pública');

        $mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde']
            != '') ? $_POST['comboMesDesde'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesDesde . ';';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar();
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta']
            != '') ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesHasta . ';';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar();
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        if ($this->mesDesde == 0 and $this->mesHasta == 13) {
            $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr(
                $_SESSION[_S]['ejercicio'],
                0,
                4
            ));
        } elseif (($this->mesDesde != 0 and $this->mesDesde != 13) and $this->mesHasta == 13) {

            $linea5 = utf8Decode('Del 01 de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . substr(
                $_SESSION[_S]['ejercicio'],
                0,
                4
            ));
        } elseif ($this->mesDesde == 0 and $this->mesHasta == 0) {
            $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr(
                $_SESSION[_S]['ejercicio'],
                0,
                4
            ));
        } elseif ($this->mesHasta == $this->mesDesde) {
            if ($this->mesDesde == 13 and $this->mesHasta == 13) {
                $linea5 = utf8Decode('Al ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            } else {
                $linea5 = utf8Decode('Del 01 al ' . $rowMes['msc_dias'] . ' de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            }
        } else {
            if ($this->mesDesde == 0) {
                $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            } else {
                $linea5 = utf8Decode('Del 01 de ' . ucwords(strtolower($rowMes['msc_nombre'])) . ' al ' . $rowMes2['msc_dias'] . ' de ' . ucwords(strtolower($rowMes2['msc_nombre'])) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            }

        }

        if (isset($_POST['MilPes'])) {
            $linea6 = '(Cifras en Miles de Pesos)';
            $linea6 = '(Cifras en Miles de Pesos)';
        } else {
            $linea6 = '(Cifras en Pesos)';
            $linea6 = '(Cifras en Pesos)';
        }

        if ($_POST['tipoReporte'] == 3 || $_SESSION[_S]['nvj_id']
            <= 2) {
            $linea2 = utf8Decode($texto);

            if ($this->ue != '' && $_SESSION[_S]['nvj_id'] <= 2) {
                $linea3 = utf8Decode($texto2);
            }
        }

        // Dibujamos el logo y las líneas del encabezado
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);
        $nLineas = 0;

        // Dibujamos el logo
        $this->Image(
            '../../../../img/escudocolor_derecha.png',
            $this->logo,
            $iy,
            74,
            22,
            'png'
        );
        // Dibujamos las líneas de encabezado

        $this->SetFont('univiapro', 'B', 7);

        if (trim($linea1) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea2, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim(isset($linea3)) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea4) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim(isset($linea7)) != '') {
            $this->SetX(10);
            $this->Cell(195, 5, $linea7, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

        // Dejamos un espacio para la siguiente sección
        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);

        $this->SetBorders(true);
        $this->SetRelleno(true);
        $this->SetFont('univiapro', 'B', 5);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFillColor(199, 209, 219);
        $altoLinea = $this->setAltoLinea(3);
        $espacio = 0;
        $fill = false;

        $this->setX(17);
        $this->RoundedRect(91, 44, 22, 151.7, 0, '', '');
        $this->RoundedRect(113, 44, 22, 151.7, 0, '', '');
        $this->RoundedRect(135, 44, 22, 151.7, 0, '', '');
        $this->RoundedRect(157, 44, 25, 151.7, 0, '', '');
        $this->Cell(74, 3, '', 'LRT', 0, 'C', true);
        $this->Cell(22, 3, '', 'LRT', 0, 'C', true);
        $this->Cell(22, 3, utf8Decode('Hacienda Pública/'), 'LRT', 0, 'C', true);
        $this->Cell(22, 3, utf8Decode('Hacienda Pública/'), 'LRT', 0, 'C', true);
        $this->Cell(25, 3, 'Exceso o Insuficiencia en la', 'LRT', 0, 'C', true);
        $this->Cell(19, 3, '', 'LRT', 1, 'C', true);

        $this->setX(17);
        $this->Cell(74, 3, 'Concepto', 'LR', 0, 'C', true);
        $this->Cell(22, 3, utf8Decode('Hacienda Pública/'), 'LR', 0, 'C', true);
        $this->Cell(22, 3, 'Patrimonio Generado de', 'LR', 0, 'C', true);
        $this->Cell(22, 3, 'Patrimonio Generado', 'LR', 0, 'C', true);
        $this->Cell(25, 3, utf8Decode('Actualización de la Hacienda'), 'LR', 0, 'C', true);
        $this->Cell(19, 3, 'Total', 'LR', 1, 'C', true);

        $this->setX(17);
        $this->Cell(74, 3, '', 'LRB', 0, 'C', true);
        $this->Cell(22, 3, 'Patrimonio Contribuido', 'LRB', 0, 'C', true);
        $this->Cell(22, 3, 'Ejercicios Anteriores', 'LRB', 0, 'C', true);
        $this->Cell(22, 3, 'del Ejercicio', 'LRB', 0, 'C', true);
        $this->Cell(25, 3, utf8Decode('Pública/Patrimonio'), 'LRB', 0, 'C', true);
        $this->Cell(19, 3, '', 'LRB', 1, 'C', true);

        $this->setRelleno('no');
        $this->SetBorders('');
    }

    public function Footer($lastPage = true)
    {
        $this->AddFont('Montserrat-b', '', 'Montserrat-Bold.php');
        $this->AddFont('Montserrat', '', 'Montserrat-Regular.php');

        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1
            != '' && $this->firmante2 != '')) {
            $this->SetY(-50);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(2);
        } else {
            $this->SetY(-50);
        }

        if ($lastPage) {
            if ($this->firmante1 != '') {
                $this->Ln(1);
                $this->SetX(0);
                $this->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                $this->MultiAlignCell(75, 5, utf8Decode($this->cargo1), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->cargo2), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->cargo3), 0, 1, 'C');
                $this->Ln(15);
                $this->SetX(0);
                $this->SetFont('Montserrat', '', TAMANO_FUENTE_MAYUSCULAS);
                $this->MultiAlignCell(75, 5, utf8Decode($this->firmante1), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->firmante2), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->firmante3), 0, 1, 'C');
            } else {
                $this->SetFont('Montserrat-b', '', TAMANO_FUENTE_MAYUSCULAS);
                $this->SetX(25);
                $this->Ln(1);
                $this->MultiAlignCell(75, 5, utf8Decode($this->cargo2), 0, 0, 'C');
                $this->MultiAlignCell(20, 5, utf8Decode(''), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->cargo3), 0, 1, 'C');
                $this->Ln(15);
                $this->SetX(25);
                $this->SetFont('Montserrat', '', TAMANO_FUENTE_MAYUSCULAS);
                $this->MultiAlignCell(75, 5, utf8Decode($this->firmante2), 0, 0, 'C');
                $this->MultiAlignCell(20, 5, utf8Decode(''), 0, 0, 'C');
                $this->MultiAlignCell(75, 5, utf8Decode($this->firmante3), 0, 1, 'C');
            }

        }

        $this->SetY(-9);
        $this->SetFont('univiapro', '', 5);
        $this->Cell(
            178,
            10,
            utf8Decode('Página') . $this->PageNo() . '/{nb}',
            0,
            0,
            'C'
        );
    }

}

$mesDesde = (isset($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde']
: -1;
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta']
: -1;
$tipoReporte = (isset($_POST['tipoReporte']) != '') ? $_POST['tipoReporte']
: '';
$MilPesos = (isset($_POST['MilPes']) != '') ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$ue = isset($_POST['ue']) ? $_POST['ue'] : '';
$incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';

// En el ticket #291730, definen que SIEMPRE se incluirán todas las cuentas
// incluyendo aquellas cuyo saldo es cero
$ceros = 1;



$pdf = new reportetabla('P', 'mm', 'Letter');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$pdf->SetTitle('Edo_Variacion_Hacienda_Publica');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetDrawColor(0, 0, 0);
$pdf->SetFillColor(224, 224, 224);

$fill = false;

$datos = get_machote_estado_variacion();

rellena_variacion_hacienda($datos, $mesDesde, $mesHasta, $tipoReporte, $MilPesos, $grupos, $excluir, $ue, $incCta, $ceros);

$altoLinea = $pdf->intLin;
$pdf->SetAutoPageBreak(true, 30);
$lineas = 0;
foreach ($datos as $indice => $valor) {

    $pdf->SetX(17);

    $concepto = '';

    $font_size = 6;

    $es_linea_separadora = false;
    if (trim($valor['niv1'] == '') && trim($valor['niv2'] == '')) {
        $es_linea_separadora = true;
        $concepto = '';
        $col1 = $col2 = $col3 = $col4 = $col5 = '';
    }

    // Por si piden en miles de pesos
    // SIEMPRE Y CUANDO NO SEA UNA LÍNEA SEPARADORA
    if (!$es_linea_separadora) {

        // Usamos el chr(160) porque en UTF8 se "ve" como espacio pero no es un espacio, y así
        // el FPDF no lo quita (quita todos los espacios al principio de la cadena)
        if ($valor['niv1'] != '') {
            $pdf->SetFont('univiapro', 'B', $font_size);
            $concepto = utf8Decode($valor['niv1']);
        } elseif ($valor['niv2'] != '') {
            $pdf->SetFont('univiapro', '', $font_size);
            $concepto = chr(160) . '      ' . utf8Decode($valor['niv2']);
        }

        $col1 = $valor['col1'];
        $col2 = $valor['col2'];
        $col3 = $valor['col3'];
        $col4 = $valor['col4'];
        $col5 = $valor['col5'];

    }

    $pdf->SetRelleno($fill);

    $pdf->SetWidths([74, 22, 22, 22, 25, 19]);
    $pdf->SetAligns(['L', 'R', 'R', 'R', 'R', 'R']);
    $pdf->Row([ $concepto, $col1, $col2, $col3, $col4, $col5 ], 'LR');
    $lineas++;
}
$pdf->SetX(17);
$pdf->cell(184, 0, '', 'B', 0, 'C', $fill);
$pdf->Ln(1);
$pdf->SetX(16);
$pdf->Cell(184,3, utf8Decode('Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'), 0, 0, 'L',$fill);
$pdf->Output();

// Fin del código fuente
