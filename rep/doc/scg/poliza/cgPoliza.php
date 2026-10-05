<?php
session_start();
set_time_limit(0);

require_once dirname(__FILE__) . "/../../../../lib/constantes.php";
require_once dirname(__FILE__) . "/../../../../lib/helpers.php";
require_once dirname(__FILE__) . "/../../../lib/fpdf_grupos.php";
require_once dirname(__FILE__) . "/../../../../lib/AccesoDatos.php";
require_once dirname(__FILE__) . "/../../../../ent/UnidadResponsable.php";
require_once dirname(__FILE__) . '/../../../../ent/Usuario.php';

class CgPoliza extends PDF_PageGroup
{
    public $datosEncabezado;
    public $datosEncabezadoExtras;
    public $datosExtraVarios;
    public $modoPoliza;
    public $tamanioNecesario;
    public $paraRevision;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'letter', $obj = null)
    {

        parent::__construct($orientation, $unit, $format, $obj);
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->tamanioNecesario = 20;

        if ($this->usr_id != '' && $this->usr_id != 0) {
            $this->usuario = new Usuario();
            $this->usuario->setUsr_id($this->usr_id);
            $this->usuario->select();
        }
    }

    function Footer($lastPage = true)
    {
        $this->SetY(-10);

        $this->SetX(5);
        $y = -10;
        $x = 19;
        $this->SetY($y);
        $this->setX($x);
        $this->Cell(57, 2, utf8Decode($this->usuario->getUsr_id() . " - " . $this->usuario->getUsuario() . " "), 0, 'L');

        $this->SetY($y - 5);
        $this->Cell(0, 10, utf8Decode('Página ') . $this->GroupPageNo() . '/' . $this->PageGroupAlias(), 0, 0, 'C');

        $x = $this->GetX() - 40;
        $this->setX($x);
        $this->Cell(0, 10, date("d/m/y") . ' - ' . date("H:i:s") . '        ', 0, 0, 'L');
    }

    function Header()
    {
        switch ($this->getOrientation()) {
            case 'P':
                $this->longitud = 60;
                $this->ancho = 175;
                $this->imagen = 195;
                $this->logo = 122;
                break;
            case 'L':
                $this->longitud = 95;
                $this->ancho = 239;
                $this->imagen = 259;
                $this->logo = 186;
                break;
        }

        if ($this->datosEncabezado['epl_id'] == "C") {
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('univiapro', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, 'CANCELADA', 45);
        }

        if ($this->datosEncabezado['epl_id'] == "H") {

            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('univiapro', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 30, $iy + 160, 'CHEQUE CANCELADO', 45);
        }

        // Si el modo de impresión de la póliza NO es normal, ponemos una marca de agua
        // que indique el tipo de póliza que es
        if ($this->modoPoliza <> "1" && $this->modoPoliza <> "") {
            $arrModos = array(
                "2" => "REGISTRO",
                "3" => "MAYOR",
                "4" => "SUMARIZADA V",
                "5" => "SUMARIZADA V+A",
                "6" => "CUARTO NIVEL"
            );
            $ix = $this->GetX();
            $iy = $this->GetY();
            $this->SetFont('univiapro', 'B', 50);
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($ix + 60, $iy + 160, $arrModos[$this->modoPoliza], 45);
        }

        $this->longitud = 60;
        $this->ancho = 175;
        $this->imagen = 195;
        $this->logo = 122;

        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->Image('../../../../img/logoOaxaca-color-fblanco_v2.png', $this->logo, $iy, 74, 22, 'png');

        $ytmp = $this->GetY();

        $this->SetFont('univiapro', 'B', 8);
        $this->SetXY($ix, $iy);
        $ytmp = $this->GetY();
        $this->Cell(195, 5, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');

        $this->SetXY($ix, $ytmp + 4);
        if ($_SESSION[_S]['nvj_id'] >= 3) {
            $ytmp = $this->GetY();
            $this->SetFont('univiapro', 'B', 7);
            $this->Cell(180, 4, utf8Decode('DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL'), 0, 1, 'L');
            $this->ln(3);
            $this->SetXY($ix, $ytmp + 4);
            $ytmp = $this->GetY();
            $this->SetFont('univiapro', '', 7);

            $this->MultiCell(100, 4, utf8Decode('UR:' . $this->datosEncabezado['gpo_id'] . $this->datosEncabezado['ur_id'] . ' -' . $this->datosEncabezado['ur_nombre']), 0, "L", 0, false);
            $this->Cell(180, 4, utf8Decode("PÓLIZA CONTABLE"));
            $this->ln(3);
            $ytmp = $this->GetY();
        } else {
            $this->Cell(195, 4, 'CONTABILIDAD GUBERNAMENTAL ' . $_SESSION[_S]['ejercicioActual'], 0, 1, 'L');
            $ytmp = $this->GetY();
            $this->SetFont('univiapro', '', 7);
            $this->MultiCell(100, 4, utf8Decode('UR:' . $this->datosEncabezado['gpo_id'] . $this->datosEncabezado['ur_id'] . ' -' . $this->datosEncabezado['ur_nombre']), 0, "L", 0, false);

            $this->MultiCell(100, 4, utf8Decode("PÓLIZA CONTABLE"), 0, "L", 0, false);
            $this->ln(3);
            $this->SetXY($ix, $ytmp + 4);
            $this->ln(7);
        }

        $this->Line($ix, $iy + 24, $this->imagen, $iy + 24);
        // Impresión de la agrupación de la póliza
        $this->setY(-13);
        $this->setX(19);
        $agrupacionTexto = $this->datosEncabezado['apg_id'] . ' - ' . $this->datosEncabezado['apg_nombre'] . (($this->datosEncabezado['mmc_id'] != 'L' && $this->datosEncabezado['mmc_id'] != 'V') ? ' / FE' : '');
        $this->Cell(57, 2, utf8Decode($agrupacionTexto), 0, 'L');
    }

    function cabecera()
    {

        if (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == "116") && (!isset($_SESSION[_S]['clasAdm-ue']) || (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] == "005"))) {
            $docto = "CereCo";
            $ori = "UR";
        } else {
            $docto = "AFECTACION";
            $ori = "TDF";
        }

        $this->setRelleno("si");
        $this->setFillColor(220, 220, 220);
        $this->SetFont('univiapro', 'B', 5);
        $this->SetTextColor(0, 0, 0);
        $this->setX(20);
        $this->SetDrawColor(180, 180, 180);
        $this->SetBorders(1);
        $this->SetWidths(array(12, 30, 45, 25, 25, 15, 15, 8));
        $this->SetAligns(array('L', 'C', 'C', 'R', 'R', 'C', 'C', 'C'));
        $this->Row(array(
            utf8Decode(''),
            utf8Decode('CUENTA'),
            utf8Decode('DESCRIPCIÓN'),
            utf8Decode('DEBE'),
            utf8Decode('HABER'),
            utf8Decode($docto),
            utf8Decode('DOCUMENTO'),
            utf8Decode($ori)
        ));
        // Marcos: 22/09/2016
        // Israel solicita que en lugar de VINCULACIÓN, la última columna diga DOCUMENTO
        $this->ln(.2);
        $this->SetTextColor(0, 0, 0);
        $this->setRelleno("no");
    }

    // Nomas porque estaba aburrido
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->SetFont('univiapro', 'B', 50);
        $this->SetTextColor(211, 211, 211);
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function totales($debe, $haber, $fin)
    {
        $this->setX(20);
        if ($fin) { /* SI ES TRUE QUIERE DECIR QUE ES EL ULTIMO REGISTRO DE LA POLIZA */
            $this->SetDrawColor(200, 200, 200);
            if (number_format($debe, "2") == number_format($haber, 2)) { /* SI LAS SUMAS SON IGUALES */
                $this->Cell(87, 4, 'SUMAS IGUALES', 0, 0, 'R', false);
            } else { /* SI LAS SUMAS NO SON IGUALES  PINTAMOS UN CUADRO DE ADVERTENCIA */
                $this->setFillColor(255, 255, 255);
                $this->Cell(87, 4, 'EXISTE UN ERROR YA QUE LAS SUMAS NO COINCIDEN', 0, 0, 'R', true);
            }
        } else {
            $this->SetDrawColor(200, 200, 200);
            $this->Cell(87, 4, 'SUMAS PARCIALES', 0, 0, 'R', false);
        }
        $this->SetFillcolor(220, 220, 220);
        $this->SetDrawColor(200, 200, 200);
        $this->Cell(25, 4, '$' . number_format($debe, 2), 1, 0, 'R', true);
        $this->Cell(25, 4, '$' . number_format($haber, 2), 1, 0, 'R', true);
        $this->Cell(15, 4, '', 0, 0, 'R', false);
        $this->Cell(15, 4, '', 0, 0, 'R', false);
    }

    function cuenta($cuenta, $nombre)
    {
        $this->setX(20);
        $this->setDrawcolor(200, 200, 200);
        $this->SetFont('univiapro', 'B', 6);
        $this->setBorders(1);
        $this->SetWidths(array(12, 30, 133));
        $this->setAligns(array('C', 'L', 'L'));
        $this->Row(array(utf8Decode($cuenta), '', utf8Decode($nombre)));
        $this->setDrawcolor(220, 220, 220);
    }

    function titulo()
    {
        $concepto = "";
        $lenconcepto = 0;
        $this->setY(35);
        $this->setDrawColor(200, 200, 200);
        $this->SetFont('univiapro', 'B', 9);
        $this->Cell(90, 4, 'Concepto', 0, 2, 'L');
        $this->SetFont('univiapro', 'B', 6.5);
        $concepto = $this->datosEncabezado['pma_concepto'];
        $this->setY(41);
        $this->MultiCell(90, 2.7, utf8Decode($this->datosEncabezado['pma_concepto']), 0, "L", 0, false);

        $this->setFillColor(230, 230, 230);

        $lenconcepto = strlen($concepto);

        $this->setY(35);
        $this->setX(115);
        $this->Cell(80, 3, 'DATOS DE LA POLIZA', 1, 2, 'C', true);
        $this->SetFont('univiapro', '', 6.5);
        $this->Cell(15, 3, 'FECHA', 'LTR', 0, 'C', 1);
        $this->Cell(35, 3, utf8Decode('ELABORÓ'), 'LTR', 0, 'C', 1);

        $this->Cell(14, 3, 'POLIZA', 'LTR', 0, 'C', 1);
        $this->Cell(8, 3, utf8Decode('MC'), 1, 0, 'C', true);
        $this->Cell(8, 3, utf8Decode('TMC'), 1, 2, 'C', true);

        $this->setX(115);
        $this->SetFont('univiapro', 'B', 6.5);         //2014-12-01
        $this->Cell(15, 3, substr($this->datosEncabezado['pma_fecha'], 8, 2) . "/" . substr($this->datosEncabezado['pma_fecha'], 5, 2) . "/" . substr($this->datosEncabezado['pma_fecha'], 0, 4), 'LBR', 0, 'C', false);
        $this->Cell(35, 3, utf8Decode($this->datosEncabezado['usr_usuario']), 'LBR', 0, 'C', false);
        $this->Cell(14, 3, $this->datosEncabezado['pot_id'] . "-" . $this->datosEncabezado['prf_id'] . $this->datosEncabezado['pma_id'], 'LBR', 0, 'C', false);
        $this->Cell(8, 3, utf8Decode($this->datosEncabezado['mmc_id']), 'LBR', 0, 'C', false);
        $this->Cell(8, 3, utf8Decode($this->datosEncabezado['tmc_id']), 'LBR', 2, 'C', false);

        // Marcos: 28/mar/2017
        // Porque luego no entiendo las vinculaciones, adicionamos éstos datos
        if ($this->paraRevision == 1) {
            $this->SetFont('univiapro', '', 6.5);
            $this->setX(115);
            $this->Cell(15, 3, 'Ejercicio', 'LTR', 0, 'C', 1);

            if ($this->datosEncabezado['mmc_id'] == "E" || $this->datosEncabezado['mmc_id'] == "F" || $this->datosEncabezado['mmc_id'] == "T" || $this->datosEncabezado['mmc_id'] == "N") {
                $this->Cell(15, 3, 'CLC Ori', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'CLC Vin', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "R" || $this->datosEncabezado['mmc_id'] == "K") {
                $this->Cell(15, 3, 'CLC Ori', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'REC', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "G") {
                $this->Cell(15, 3, 'CLC Ori', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'CERE-CO', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "B") {
                $this->Cell(15, 3, 'CLC Vin', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'CERE-CO', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "D" || $this->datosEncabezado['mmc_id'] == "P") {
                $this->Cell(30, 3, 'BDO', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "C") {
                $this->Cell(15, 3, 'CMP Ori', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'CMP Aju', 'LTR', 0, 'C', 1);
            } elseif ($this->datosEncabezado['mmc_id'] == "M") {
                $this->Cell(15, 3, 'ORIGEN', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, 'MOD', 'LTR', 0, 'C', 1);
            } else {
                $this->Cell(15, 3, '', 'LTR', 0, 'C', 1);
                $this->Cell(15, 3, '', 'LTR', 0, 'C', 1);
            }

            $this->Cell(35, 3, utf8Decode('CVE FINANC'), 1, 2, 'C', true);
            $this->setX(115);
            $this->SetFont('univiapro', 'B', 6.5);         //2014-12-01
            $this->Cell(15, 3, utf8Decode($this->datosEncabezadoExtras['ejercicio'] . $this->datosExtraVarios), 'LBR', 0, 'C', false);
            if ($this->datosEncabezado['mmc_id'] == "D" || $this->datosEncabezado['mmc_id'] == "P") {
                $this->Cell(30, 3, utf8Decode($this->datosEncabezadoExtras['clcOri'] . $this->datosExtraVarios), 'LBR', 0, 'C', false);
            } else {
                $this->Cell(15, 3, utf8Decode($this->datosEncabezadoExtras['clcOri'] . $this->datosExtraVarios), 'LBR', 0, 'C', false);
                $this->Cell(15, 3, utf8Decode($this->datosEncabezadoExtras['clcVin'] . $this->datosExtraVarios), 'LBR', 0, 'C', false);
            }

            $this->Cell(35, 3, utf8Decode($this->datosEncabezadoExtras['cf'] . $this->datosExtraVarios), 'LBR', 0, 'C', false);
            $this->setX(122);
            $this->SetFont('univiapro', 'B', 6.5);
        }
        if ($lenconcepto > 0) {
            if ($this->paraRevision == 1) {
                $lenconcepto = $lenconcepto / 60;
            } else {
                $lenconcepto = $lenconcepto / 35;
            }
        } else {
            $lenconcepto = 1;
        }
        $this->ln(4 + $lenconcepto);
    }
}
// ======================================================================================================================================================
// ======================================================================================================================================================
// INICIAMOS EL REPORTE ...
// ======================================================================================================================================================
// ======================================================================================================================================================

$datos = null;

// Marcos: 15/jun/2018
// Para la impresión de pólizas de rectificado desde el menú de FONDOS, se necesita que sea gpo + ur de la entidad
// Pero el usuario que tiene permisos para esta opción es de la 116, por lo que no salen bien
// se ajusta para que sólo en ése caso tome otra ur distinta de la de la clasificación administrativa
if ($_SESSION[_S]['clasAdm-gpo'] == 1 && $_SESSION[_S]['clasAdm-ur'] == 16) {
    // Si pasaron los parametros rg y ru.. y no están vacíos
    if ((isset($_POST['rg']) && isset($_POST['ru'])) && ($_POST['rg'] != "" && $_POST['ru'] != "")) {
        $gpo_id = $_POST['rg'];
        $ur_id = $_POST['ru'];
        // Si no vienen esos parámetros o vienen vacíos, comportamiento normal
    } else {
        $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $ur_id = $_SESSION[_S]['clasAdm-ur'];
    }
    // Si no es la 116, comportamiento normal
} else {
    $gpo_id = $_SESSION[_S]['clasAdm-gpo'];
    $ur_id = $_SESSION[_S]['clasAdm-ur'];
}

$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : "actual";   // Si no lo indican, es una sola póliza
// Marcos: 28/mar/2017
// Adicioamos campos para facilitar la revisión del origen de la póliza
$paraRevision = isset($_POST['rev']) ? $_POST['rev'] : 0;   // Si no lo indican, NO ES DE REVISIÓN
// Pueden cambiar entre imprimirla normal, a nivel de mayor solamente, o sumarizada x cuenta + naturaleza
$modoPoliza = isset($_POST['modoPoliza']) ? $_POST['modoPoliza'] : "1";   // Si no lo indican, se imprime normal
// Dependiendo del tipo es lo que quieren imprimir: actual = una poliza   sel = selección actual del libro diario   rango = rango arbitrario de pólizas separadas por comas
switch ($tipo) {
        // =================================================================================================================
        // UNA SOLA POLIZA
        // =================================================================================================================
    case "actual":
        $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "%") ? $_POST['ue'] : "001";
        $pot_id = isset($_POST['pot']) ? $_POST['pot'] : "D";
        $prf_id = isset($_POST['prf']) ? $_POST['prf'] : "0";
        $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;
        $pma_id = isset($_POST['num']) ? $_POST['num'] : 1;
        // Marcos: 06/abr/2016
        // Para imprimir de meses diferentes, cuando filtran por vinculación
        $msc_id_act = isset($_POST['mscLinAct']) ? $_POST['mscLinAct'] : $msc_id;

        // Si el mes de la póliza actualmente seleccionada es diferente, tiene prioridad
        // sobre el mes del combo
        if ($msc_id != $msc_id_act) {
            $msc_id = $msc_id_act;
        }

        $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . ' AND m.ue_id=' . $ue_id . ' AND m.pot_id="' . $pot_id . '" AND m.prf_id="' . $prf_id . '" AND m.msc_id=' . $msc_id . ' AND m.pma_id = ' . $pma_id;

        break;
        // =================================================================================================================
        // EL RANGO DEL LIBRO DIARIO QUE ESTE ACTIVO AL MOMENTO DE PEDIR ESTA OPCION
        // =================================================================================================================
    case "sel":

        // Los obligatorios arman la consulta en el whrre
        $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;
        $vinculacion = ((isset($_POST['v']) && $_POST['v'] != "") ? trim($_POST['v']) : "");

        // Los opcionales o regresan la condición, o regresan la cadena vacía para que no afecten el where
        $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "" && trim($_POST['ue']) != "%") ? ' AND m.ue_id = "' . $_POST['ue'] . '" ' : '';
        $pma_id = (isset($_POST['num']) && trim($_POST['num']) != "") ? ' AND d.pma_id >= ' . $_POST['num'] . ' ' : '';
        $usr = (isset($_POST['usr']) && trim($_POST['usr']) != "") ? ' AND ( m.usr_id = ' . $_POST['usr'] . ' OR  m.usr_idModificado = ' . $_POST['usr'] . ' ) ' : '';
        $textoBuscar = (isset($_POST['tb']) && trim($_POST['tb']) != "") ? ' AND m.pma_concepto like "%' . $_POST['tb'] . '%" ' : '';

        // Para la vinculación ( pdx_*_origen )
        $clc_v = trim(((isset($_POST['clc_v'])) ? $_POST['clc_v'] : ''));
        $afe_v = trim(((isset($_POST['afe_v'])) ? $_POST['afe_v'] : ''));
        $cmp_v = trim(((isset($_POST['cmp_v'])) ? $_POST['cmp_v'] : ''));
        $bdo_v = trim(((isset($_POST['bdo_v'])) ? $_POST['bdo_v'] : ''));

        // Para la vinculación ( pdx_*_vinculacion )
        $clc_o = trim(((isset($_POST['clc_o'])) ? $_POST['clc_o'] : ''));
        $afe_o = trim(((isset($_POST['afe_o'])) ? $_POST['afe_o'] : ''));
        $cmp_o = trim(((isset($_POST['cmp_o'])) ? $_POST['cmp_o'] : ''));
        $bdo_o = trim(((isset($_POST['bdo_o'])) ? $_POST['bdo_o'] : ''));

        // La referencia sólo es una
        $ref_v = trim(((isset($_POST['ref_v'])) ? $_POST['ref_v'] : ''));
        $andOr = trim(((isset($_POST['andOr'])) ? $_POST['andOr'] : '0'));
        $andOr = ($andOr == "0" ? "AND" : "OR");

        // Bandera para indicar si pidieron filtros por vinculación, ya que en éstos casos
        // NO limitamos por tipo de póliza
        $bolPidieronVinculacion = ($clc_v != "" || $afe_v != "" || $cmp_v != "" || $bdo_v != "" || $clc_o != "" || $afe_o != "" || $cmp_o != "" || $bdo_o != "" || $ref_v != "");

        // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
        // -------------------------------------------------------------------------------------------
        $where = "";
        $opcion = substr($_POST['pot'], 0, 2);
        $valor = substr($_POST['pot'], 3, 1);
        switch ($opcion) {
            case "tp":
                // Si filtran por vinculacion presupuestal, no pelamos éste filtro
                if ($bolPidieronVinculacion == false) {
                    $where .= " AND pot_id = '" . $valor . "' ";
                }
                break;
            case "mc":
                $where = ' AND m.mmc_id = "' . $valor . '" ';
                break;
            case "st":
                $where = ' AND m.epl_id = "' . $valor . '" ';
                break;
        }

        // Marcos: 05/abr/2016
        // Adicionamos filtros para los campos *_origen y *_vinculacion de la tabla
        // cg_PolizaDetalleAuxiliar

        if ($bolPidieronVinculacion) {
            $where .= " AND (";
        }
        if ($clc_v != "") {
            $where .= " $andOr d.pdx_clc_id_vinculacion = '$clc_v' ";
        }
        if ($afe_v != "") {
            $where .= " $andOr d.pdx_afe_id_vinculacion = '$afe_v' ";
        }
        if ($cmp_v != "") {
            $where .= " $andOr d.pdx_cmp_id_vinculacion in ( $cmp_v ) ";
        }
        if ($bdo_v != "") {
            $where .= " $andOr d.pdx_bdo_id_vinculacion = '$bdo_v' ";
        }

        if ($clc_o != "") {
            $where .= " $andOr d.pdx_clc_id_origen = '$clc_o' ";
        }
        if ($afe_o != "") {
            $where .= " $andOr d.pdx_afe_id_origen = '$afe_o' ";
        }
        if ($cmp_o != "") {
            $where .= " $andOr d.pdx_cmp_id_origen IN ( $cmp_o )";
        }
        if ($bdo_o != "") {
            $where .= " $andOr d.pdx_bdo_id_origen = '$bdo_o' ";
        }

        // El campo de REFERENCIA, si lo escriben tal y como se imprime... es tdf_id-tdf_referencia
        // así que lo separamos
        if ($ref_v != "") {
            $arrDocto = explode("-", $ref_v);
            // DEBE ser un array de 2 posiciones.   En caso contrario dejamos vacío
            if (count($arrDocto) == 2) {
                $where .= " $andOr d.tdf_id = '" . $arrDocto[0] . "' $andOr d.pdx_referencia = '" . $arrDocto[1] . "' ";
            }
        }

        if ($bolPidieronVinculacion) {
            $where .= " )";
        }

        // Aunque hasta abril del 2016 el mes era campo fijo, si pedimos filtros por
        // vinculación presupuestal, no pelaremos el mes
        if ($bolPidieronVinculacion == false) {
            $where .= ' AND msc_id = "' . $msc_id . '" ';
        }

        // Para corregir un error en la lógica del armado
        $where = str_replace("AND ( AND ", "AND ( ", $where);
        $where = str_replace("AND ( OR ", "AND ( ", $where);

        $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . $ue_id . $pma_id . $where . $usr . $textoBuscar;

        break;

        // =================================================================================================================
        // UN RANGO ARBITRARIO DE NUMEROS DE POLIZA
        // =================================================================================================================
    case "rango":
        $ue_id = (isset($_POST['ue']) && trim($_POST['ue']) != "" && trim($_POST['ue']) != "%") ? ' AND m.ue_id = "' . $_POST['ue'] . '" ' : '';
        $prf_id = isset($_POST['prf']) ? $_POST['prf'] : "0";
        $msc_id = isset($_POST['msc']) ? $_POST['msc'] : 0;

        // Si estàn pidiendo ver sólo un momento contable, un tipo de póliza o un estatus ...
        // -------------------------------------------------------------------------------------------
        $opcion = substr($_POST['pot'], 0, 2);
        $valor = substr($_POST['pot'], 3, 1);
        switch ($opcion) {
            case "tp":
                $pot_id = ' AND m.pot_id = "' . $valor . '" ';
                break;
            case "mc":
                // Si es de pagos a terceros, hay una validación extra
                if ($valor == "O") {
                    $where .= " AND ( mmc_id = 'P') ";
                    // Y por lo tanto el Pagado Pagado normal también
                } else if ($valor == "P") {
                    $where .= " AND ( mmc_id = '" . $valor . "' ) ";
                } else {
                    $where .= " AND mmc_id = '" . $valor . "' ";
                }
                break;
            case "st":
                $pot_id = ' AND m.epl_id = "' . $valor . '" ';
                break;
        }

        // Los números de póliza deben venir separados por comas
        $pma_id = isset($_POST['num']) ? $_POST['num'] : 1;

        $arrRangos = explode(',', $pma_id);

        $whereNumeros = "";

        if (count($arrRangos) > 0) {
            // Recorremos todas las combinaciones que encontremos
            foreach ($arrRangos as $v) {
                $v = trim($v);
                if ($v != "") {
                    // Si es un rango, está separado por un guion
                    if (is_numeric(strpos($v, "-"))) {
                        // Volvemos a dividir ya que pueden estar especificando un rango, pero ahora separamos por -
                        $unRango = explode('-', $v);
                        // Como resultado sólo puede ser un elemento de 2 arrays
                        // Si no da dos, no pelamos este valor
                        if (count($unRango) == 2) {
                            // Sólo totamos los enteros
                            if (is_numeric(trim($unRango[0])) && is_numeric(trim($unRango[1]))) {
                                $whereNumeros .= ' ( m.pma_id BETWEEN ' . trim($unRango[0]) . ' AND ' . trim($unRango[1]) . ' ) OR ';
                            }
                        }
                        // si es un número único de póliza
                    } else {
                        // Sólo totamos los enteros
                        if (is_numeric($v)) {
                            $whereNumeros .= ' m.pma_id = ' . $v . ' OR ';
                        }
                    }
                }
            }

            // Le quitamos el último OR
            if (substr($whereNumeros, -4, 4) == ' OR ') {
                $whereNumeros = substr($whereNumeros, 0, strlen($whereNumeros) - 4);
            }

            // Si llegamos aquí sin nada definido, tronamos el PDF para que aparezca una ventana vacía
            if (trim($whereNumeros) == "") {
                echo "Los valores de rango de polizas no fueron especificados correctamente.";
                return false;
            }

            $where = ' m.gpo_id=' . $gpo_id . ' AND m.ur_id=' . $ur_id . $ue_id . $pot_id . ' AND m.prf_id="' . $prf_id . '" AND m.msc_id=' . $msc_id . ' AND ( ' . $whereNumeros . ' )';

            // Si el rango no es válido ... un where que siempre regrese vacío
        } else {
            $where = 'd.gpo_id = 9 and d.ur_id = 99 and d.ue_id = 999';
        }
        break;
}

// Instanciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------
$pdf = new CgPoliza('L', 'mm', 'Letter');
$pdf->SetProtection(array("print", "annot-forms"), "", PDF_PASSWORD_OWNER_SCG);
$pdf->setOrientation('P');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 20);
$pdf->setAltoLinea(2);

$pdf->modoPoliza = $modoPoliza;
$pdf->paraRevision = $paraRevision;

// Obtenemos los datos del Detalle  epl_id  c:cancelada h: cheque cancelado epl_id
// --------------------------------------------------------------------------------------------------------------------------------
$var_max = 'MAX';
if ($modoPoliza == 1) {
    $var_max = '';
}
switch ($modoPoliza) {
        // Impresión a Nivel de Mayor
    case '3':
        $sql = 'SELECT m.ue_id, m.pot_id, m.prf_id, m.msc_id, m.pma_id, sCs.cgsc_cuenta AS cta,
                    MAX(sCs.cgsc_nombre), apg_id, "" as pdx_afe_id_origen,
                    sCs.cgsc_cuenta AS cuenta,
                    SUM( pdx_debe ) AS pdx_debe, SUM( pdx_haber ) AS pdx_haber, "" AS pdx_vinculacion, MAX(d.adf_id), MAX(d.pdx_id), m.epl_id, d.nat_id, d.pdx_referencia, 
                    MAX(d.tdf_id),
                    MAX(d.tdf_id) as tdf_id,
                    MAX(sCs.cgsc_nombre) AS nombreCuenta,
                    MAX(sCs.cgsc_nombre) AS cgsc_nombre,
                    MAX(d.pdx_gpo_id_origen), MAX(d.pdx_ur_id_origen), MAX(pdx_clc_cereCo_vinculacion)
                FROM cg_PolizaMaestro m
                    JOIN cg_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
                    JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                    INNER JOIN cat_CgSubcuenta sCs USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
                WHERE ' . $where . '
                    GROUP BY
                        ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia
                    ORDER BY
                        ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia;';
        break;

        // En la impresión normal y sumarizada, sólo cambia el GROUP BY
    default:
        $sql = 'SELECT m.ue_id, m.pot_id, m.prf_id, m.msc_id, m.pma_id, ' . $var_max . '(sCs.cgsc_cuenta) AS cta,
            ' . $var_max . '(sCs.cgsc_nombre) as cgsc_nombre, apg_id, ' . (($modoPoliza == "4" || $modoPoliza
            == "2") ? ' "" AS pdx_afe_id_origen' : '' . $var_max . '(pdx_afe_id_origen) ') . ', ' .
            ($modoPoliza == "6" ?
                "CONCAT( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, '.', cgc_id ) AS cuenta, "
                :
                "c.cta_cuenta AS cuenta, "
            ) . '
                ' . ($modoPoliza == "1" ? ' pdx_debe' : ' SUM( pdx_debe  ) AS pdx_debe') . ',
                ' . ($modoPoliza == "1" ? ' pdx_haber' : ' SUM( pdx_haber ) AS pdx_haber') . ',
                "" AS pdx_vinculacion, ' . $var_max . '(d.adf_id), ' . $var_max . '(d.pdx_id), ' . $var_max . '(m.epl_id), ' . $var_max . '(d.nat_id),
                ' . (($modoPoliza == "2") ? ' "" AS pdx_referencia' : ' ' . $var_max . '(d.pdx_referencia) ') . ',
                ' . $var_max . '(d.tdf_id),
                CASE ' . $var_max . '(sCs.ecu_id) WHEN "2" THEN
                CASE ' . $var_max . '(cgc_id) WHEN 1 THEN ' . $var_max . '(c.cta_nombre) ELSE ' . $var_max . '(t.cgc_nombre) END
                ELSE ' . $var_max . '(c.cta_nombre) END AS nombreCuenta,
                ' . $var_max . '(d.pdx_gpo_id_origen), ' . $var_max . '(d.pdx_ur_id_origen), ' . $var_max . '(pdx_clc_cereCo_vinculacion)
            FROM cg_PolizaMaestro m
            LEFT JOIN cg_PolizaDetalleAuxiliar d USING( gpo_id, ur_id, ue_id, pot_id, prf_id, msc_id, pma_id )
            LEFT JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            LEFT JOIN cat_CgSubcuenta sCs USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id )
            LEFT JOIN cg_CuentaCuarto t USING ( ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id )
            LEFT JOIN cg_PlanCuentas c USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
            WHERE ' . $where;

        // Marcos: 23/feb/2016
        // que dice mi mamá que siempre no:   este es el order original, lo quitamos a sugerencia de
        // Israel para dejar el segundo... pero salían descuadradas las pólizas por ranto
        // así que mejor... lo dejamos como estada... independientemente de índices y todo lo demás :D
        // Registro: no incluimos documentos fuente, y sumarizamos a nivel de registro de la cuenta
        if ($modoPoliza == "2" || $modoPoliza == "6") {
            $sql .= ' GROUP BY ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta ';
        } else {
            if ($modoPoliza <> "1") {
                $sql .= ' GROUP BY ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, pdx_referencia' . ($modoPoliza == "4" ? ' ' : ', pdx_afe_id_origen ');
            }
        }

        // Marcos, se agrega gpo y ur origen
        // 04/abr/2017
        $sql .= ' ORDER BY gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id, d.nat_id, cuenta, ' . $var_max . '(pdx_gpo_id_origen), ' . $var_max . '(pdx_ur_id_origen), ' . $var_max . '(pdx_afe_id_origen), ' . $var_max . '(pdx_bdo_id_origen), ' . $var_max . '(pdx_id);';
        break;
}

$conexion = new AccesoDatos();
$conexion->conectar();
$conexion->setSql($sql);
$conexion->consultar();
$numPolizas = $conexion->getNumeroRegistros();
$conexion->desconectar();

$row = $conexion->extraer();

/* * **** VARIABLES  DE MONTOS ******* */
$totaldebe = $totalhaber = 0;
/* * **** VARIABLES  DE CAMBIOS ****** */
$mil = "";
$celda = 0;
$poliza = "";

/* * ***** FIN DE VARIABLES ******* */

do {
    /*     * *************** SI CAMBIA EL NUMERO DE POLIZA ************ */
    if (($poliza <> $row['pot_id'] . $row['prf_id'] . $row['pma_id']) || ($numPolizas == 0)) {
        if ($numPolizas == 0) {
            $consul = 'SELECT "" as gpo_id, "" as ur_id, "" as ur_nombre, "" as pot_id, "" as prf_id, "" as pma_id, "" as pma_fecha, 0 as pma_debe, 0 as pma_haber, "*** LA POLIZA NO CONTIENE NINGUNA LINEA ***" as pma_concepto, "" as apg_id, "" as epl_id, "" as msc_id, "" as mmc_id, "" as mmc_nombre, "" as usr_id, "" as usr_usuario, "" as tmc_id';
        } else {
            $consul = 'SELECT ur.gpo_id, ur.ur_id, ur.ur_nombre, pm.pot_id, pm.prf_id, pm.pma_id, pm.pma_fecha, pm.pma_debe, pm.pma_haber, pm.pma_concepto, epl_id, msc_id, mmc_id, mmc_nombre, pm.usr_id, usr_usuario,tmc_id, ap.apg_id, ap.apg_nombre
                    FROM cg_PolizaMaestro pm
                    JOIN cat_UnidadResponsable ur USING(gpo_id,ur_id)
                    INNER JOIN cat_MomentoContable USING( mmc_id )
                    INNER JOIN cat_AgrupacionPoliza AS ap USING (apg_id)
                    JOIN cat_Usuario u on u.usr_id=pm.usr_id
                    WHERE pm.gpo_id = ' . $gpo_id . ' AND pm.ur_id = ' . $ur_id . ' AND pm.ue_id = ' . $row['ue_id'] . '
                    AND pm.msc_id = ' . $row['msc_id'] . ' AND pm.pot_id = "' . $row['pot_id'] . '"
                    AND pm.prf_id = "' . $row['prf_id'] . '" AND pm.pma_id = ' . $row['pma_id'];
        }

        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($consul);
        $con->consultar();

        $pdf->datosEncabezado = $con->extraer();
        $con->desconectar();

        $datosextra = "";
        if ($pdf->datosEncabezado['mmc_id'] == "E" || $pdf->datosEncabezado['mmc_id'] == "F" || $pdf->datosEncabezado['mmc_id'] == "T" || $pdf->datosEncabezado['mmc_id'] == "N") {
            $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT(  pdx_clc_id_origen ) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_clc_id_vinculacion) ) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "R") {
            $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_clc_id_origen ) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_rct_id_vinculacion) ) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "K") {
            $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_origen, pdx_ur_id_origen, " -", pdx_clc_id_origen ) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, " -" , pdx_rct_id_vinculacion) ) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "G") {
            $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT( pdx_gpo_id_origen, pdx_ur_id_origen) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_clc_cereCo_vinculacion) ) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "B") {
            $datosextra = '   max(pdx_gpo_id_vinculacion) AS clcOri,
                              max(pdx_clc_cereCo_vinculacion) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "D" || $pdf->datosEncabezado['mmc_id'] == "P") {
            $datosextra = '  GROUP_CONCAT( DISTINCT CONCAT(  pdx_bdo_id_origen ) ) AS clcOri,
                            "" AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "C") {
            $datosextra = '   GROUP_CONCAT( DISTINCT CONCAT(  pdx_cmp_id_origen ) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_cmp_id_vinculacion) ) AS clcVin, ';
        } elseif ($pdf->datosEncabezado['mmc_id'] == "M") {
            $datosextra = '  GROUP_CONCAT( DISTINCT CONCAT(  pdx_tipoPresupuestal ) ) AS clcOri,
                            GROUP_CONCAT( DISTINCT CONCAT( pdx_mod_id_vinculacion) ) AS clcVin, ';
        } else {
            $datosextra = '  "" AS clcOri,
                            "" AS clcVin, ';
        }

        // Marcos: 28/mar/2017
        // Porque no le entiendo o no encuentro rapido de dónde viene la póliza, cuando una afectación se ha usado en otras CLC que al final
        // se desecharon, adiciono éstos campos
        if ($pdf->paraRevision == 1) {
            if ($numPolizas == 0) {
                $consul2 = 'SELECT "" as ejercicio, "" as clcOri, "" as clcVin, "" as cf;';
            } else {
                // Marcos: 16/06/2017
                // se adiciona LEFT JOIN a enc_ClaveFinanciamiento, porque cuando es de ejercicios
                // anteriores la información, no necesariamente lo encontrará
                $consul2 = 'SELECT pdx_ejercicio AS ejercicio,
                            ' . $datosextra . '
                        cf.ecf_clave AS cf
                        FROM cg_PolizaDetalleAuxiliar
                        LEFT JOIN enc_ClaveFinanciamiento cf USING( ecf_id )
                        WHERE gpo_id = ' . $gpo_id . ' AND ur_id = ' . $ur_id . ' AND ue_id = ' . $row['ue_id'] . ' AND msc_id = ' . $row['msc_id'] . ' AND
                        pot_id = "' . $row['pot_id'] . '" AND prf_id =  "' . $row['prf_id'] . '" AND pma_id = ' . $row['pma_id'] . '
                        GROUP BY pdx_ejercicio, pdx_gpo_id_origen, pdx_ur_id_origen, pdx_clc_id_origen,
                        pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion, pdx_clc_id_vinculacion,cf.ecf_clave
                ;';
            }
            // Marcos: 08/mayo/2017
            // Se le quitó el HAVING porque Israel comenta que no sale en la póliza del comprometido, y esa condición
            // pide clcOri
            $con2 = new AccesoDatos();
            $con2->conectar();
            $con2->setSql($consul2);
            $con2->consultar();

            $veces = $con2->getNumeroRegistros();
            $pdf->datosExtraVarios = (($veces > 1) ? "*" : "");

            $pdf->datosEncabezadoExtras = $con2->extraer();
            $con2->desconectar();
        }

        // La primera vez no imprimimos totales de la anterior
        if ($poliza != "") {
            $pdf->totales($totaldebe, $totalhaber, true); // PINTA LOS TOTALES DE LA POLIZA ANTERIOR
        }
        $pdf->StartPageGroup();
        $pdf->AddPage();       //AGREGA UNA PAGINA /SALTO DE PAGINA
        $totaldebe = $totalhaber = 0;     // REINICIA LOS IMPORTES
        $pdf->titulo();     //ESCRIBE EL TITULO DE LA POLIZA (DATOS DE LA POLIZA |VINCULACION|CLAVE DE FINANCIAMIENTO|CONCEPTO)
        $pdf->cabecera();      //DIBUJA LA CABECERA DE LAS COLUMNAS (CUENTA|DESCRIPCION|DEBE|HABER|VINCULACION)

        $poliza = $row['pot_id'] . $row['prf_id'] . $row['pma_id'];
        $mil = "";
    }

    /*     * ***************PINTA NOMBRE DE CUENTA PRIMER NIVEL **************** */
    if ($mil <> $row['cta']) {         //VERIFICA EL CAMBIO DE CUENTA DE PRIMER NIVEL
        if ($pdf->GetY() + 13 > $pdf->getPageBreakTrigger()) {  //PRIMERO VERIFICA QUE TENGA ESPACION EN LA PAGINA
            $pdf->totales($totaldebe, $totalhaber, false); //SI NO ENTONCES PINTA LOS TOTALES PARCIALES
        }
        if ($pdf->CheckPageBreak($pdf->tamanioNecesario)) {      //SI EL FIN DE PAGINA ES TRUE ENTONCES PINTA TITULO DE LA POLIZA
            $pdf->titulo();      // Y PINTA LA CABECERA DE LAS COLUMNAS
            $pdf->cabecera();
        }
        $celda = 0;
        // Estaba imprimiendo la cuenta a cuarto nivel
        $pdf->cuenta($row['cta'], $row['cgsc_nombre']);  //PINTA EL NOMBRE DE LA CUENTA DE PRIMER NIVEL
        $mil = $row['cta'];
    }

    /*     * ************** CHECA EL FINAL DE PAGINA PARA DIBUJAR LOS TOTALES PARCIALES *************** */
    if ($pdf->GetY() + 13 > $pdf->getPageBreakTrigger()) {
        $pdf->totales($totaldebe, $totalhaber, false);  ///VERIFICA EL FINAL DE LA PAGINA Y SI ES TRUE ENTONCES PINTA LOS TOTALES PARCIALES ANTES DEL SALTO DE PAGINA
    }
    if ($pdf->CheckPageBreak($pdf->tamanioNecesario)) {
        $pdf->titulo();
        $pdf->cabecera();
    }

    /*     * **************PINTA REGISTRO ************ */
    $pdf->setX(20);
    $pdf->setFillColor(230, 230, 230);
    $pdf->setDrawcolor(200, 200, 200);
    $pdf->SetFont('univiapro', '', 6);
    $pdf->setBorders(1);

    if (($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == "116") && (!isset($_SESSION[_S]['clasAdm-ue']) || (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] == "005"))) {
        $vin = utf8Decode($row['pdx_clc_cereCo_vinculacion']);
        $ori = $row['pdx_gpo_id_origen'] . $row['pdx_ur_id_origen'];
        $doc = ($row['pdx_referencia'] == "") ? '' : $row['tdf_id'] . "-" . $row['pdx_referencia'];
    } else {
        $vin = utf8Decode($row['pdx_afe_id_origen']);
        $ori = ($row['pdx_referencia'] == "") ? '' : $row['tdf_id'];
        $doc = ($row['pdx_referencia'] == "") ? '' : $row['pdx_referencia'];
    }

    $pdf->SetWidths(array(12, 30, 45, 25, 25, 15, 15, 8));
    $pdf->setAligns(array('C', 'L', 'L', 'R', 'R', 'C', 'C', 'C'));
    $pdf->Row(array(
        utf8Decode(''),
        utf8Decode($row['cuenta']),
        utf8Decode($row['nombreCuenta']),
        ($row['pdx_debe'] <> 0) ? number_format($row['pdx_debe'], 2) : '',
        ($row['pdx_haber'] <> 0) ? number_format($row['pdx_haber'], 2) : '',
        $vin,
        $doc,
        $ori
    ));

    /*     * ********SUMA DE DEBE Y HABER *********** */
    $totaldebe += $row['pdx_debe'];
    $totalhaber += $row['pdx_haber'];
} while ($row = $conexion->extraer());

$pdf->totales($totaldebe, $totalhaber, true);
$pdf->Output();
