<?php

session_start();
error_reporting(E_ALL);
set_time_limit(0);

define('ACTIVO1', 1);
define('PASIVO2', 2);
define('HACIENDA', 3);
define('NIVEL', 0);
define('CUENTA', 1);
define('IMPORTE', 2);
define('SALDO', 3);
//librerias
require_once '../../../lib/reportetabla.1.8.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once '../../../../lib/MesNombre.php';
require_once '../../../../lib/compararCadenas.php';
require_once '../../../lib/conversionNumLetras.php';
require_once '../../../../lib/AccesoDatos.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../ent/UnidadResponsable.php';

class reportetabla extends PDF
{
    public $mes;
    public $texto;
    private $longitud = 100;
    public $longitud2 = 90;
    public $ultBorde = 0;
    public $encabezadoTabla = true;
    public $widths = '';
    public $checkCuentaOrden;
    public $anchoImagen;
    public $altoImagen;
    public $nlineas = 0;
    public $espacio;
    public $linea0;
    public function __construct()
    {
        parent::__construct();
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo']
        : $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur']
        : $_SESSION[_S]['ur_id'];
        $this->mesDesde = isset($_POST['comboMesDesde']) ? $_POST['comboMesDesde']
        : -1; //Informacion de acuerdo al mes
        $this->mesHasta = isset($_POST['comboMesHasta']) ? $_POST['comboMesHasta']
        : -1; //Informacion de acuerdo al mes
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->ceros = isset($_POST['ceros']) ? $_POST['ceros'] : '';
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $this->intLin = isset($_POST['intLin']) ? $_POST['intLin'] : '';
        $this->ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $this->numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';

        // Si no viene un número en el interlineado, ponemos el default
        if (!is_numeric($this->intLin)) {
            $this->intLin = 2.5;
        }

        $_POST['tipoReporte'] = ((isset($_POST['tipoReporte'])) ? $_POST['tipoReporte']
            : 1);

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;
        $this->checkCuentaOrden = isset($_POST['checkCuentaOrden']) ? $_POST['checkCuentaOrden']
        : -1;

        $this->ejercicio = '';

        if ($this->ejercicio == '') {
            $this->ur = new UnidadResponsable();
            $this->ur->setGpo_id($this->gpo_id);
            $this->ur->setUr_id($this->ur_id);
            $this->ur->select();
        } else {
            //obtenemos los datos de la UR del ejercicio solicitado
        }
    }

    public function Footer($lastPage = true)
    {

        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');
        $row = $this->getDatos();
        $tmpb = $this->GetBorders();

        if ($this->leyenda == 1 || ($this->cargo1 != '' && $this->cargo2 != '' && $this->firmante1
            != '' && $this->firmante2 != '')) {
            $this->SetY(-30);
            $this->Cell(190, 2, utf8Decode(''), 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            if ($this->leyenda == 1) {
                $this->SetFont('univiapro', '', 7);
                if ($_SESSION[_S]['reportes'] == 'C91' && $this->mesDesde >= 1 && $this->mesHasta
                    <= 3 && ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur']
                        == 122 && $_SESSION[_S]['nvj_id'] == 2)) {
                    $this->Cell(
                        270,
                        2,
                        utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca'),
                        0,
                        0,
                        'C'
                    );
                } else {
                    $this->Cell(
                        270,
                        2,
                        utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ' . date('d') . ' de ' . MesNombre(date('m')) . ' de ' . date('Y')),
                        0,
                        0,
                        'C'
                    );
                }

                $this->Ln(2);
                $this->Cell(
                    270,
                    2,
                    utf8Decode('Bajo protesta de decir verdad declaramos que los estados financieros y sus Notas son razonablemente correctos y responsabilidad del emisor.'),
                    0,
                    0,
                    'C'
                );
                $this->Ln(1);
            }
            $this->SetFont('univiapro', 'B', 7);
            $y = $this->GetY();

            $this->Ln(1);
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo1), 0, 0, 'C');
            $this->MultiAlignCell(77, 3, utf8Decode($this->cargo2), 0, 0, 'C');
            $this->MultiAlignCell(97, 3, utf8Decode($this->cargo3), 0, 1, 'C');
            $this->Ln(4);
            $this->SetFont('univiapro', '', 7);
            $this->MultiAlignCell(
                97,
                3,
                utf8Decode($this->firmante1),
                0,
                0,
                'C'
            );
            $this->MultiAlignCell(
                77,
                3,
                utf8Decode($this->firmante2),
                0,
                0,
                'C'
            );
            $this->MultiAlignCell(
                97,
                3,
                utf8Decode($this->firmante3),
                0,
                1,
                'C'
            );
        }

        $this->SetY(-11);
        $this->SetFont('univiapro', '', 5);
        $this->Cell(
            275,
            10,
            utf8Decode('Página') . $this->PageNo() . '/{nb}',
            0,
            0,
            'C'
        );
    }

    // Marcos: 22/mar/2020
    // Intentaremos estandarizar la impresión del logo y las líneas de encabezado, para no estar inventando
    // el hilo negro en cada reporte.php
    // $orientacion: P para portrait, L para landscape
    public function drawHeadersAndLogo(
        $orientacion,
        $linea1 = '',
        $linea2 = '',
        $linea3 = '',
        $linea4 = '',
        $linea5 = '',
        $linea6 = '',
        $linea7 = ''
    ) {

        $this->ancho = 287; // 297 que es el ancho del tamaño carta apaisada - 10

        $this->anchoImagen = 74;
        $this->altoImagen = 22;
        $this->logo = ($this->ancho - $this->anchoImagen); //213;

        $ix = $this->GetX();
        $iy = $this->GetY();
        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $nLineas = 0;

        // Dibujamos el logo
        $this->Image(
            '../../../../img/logoOaxaca-color-fblanco.png',
            $this->logo,
            $iy,
            $this->anchoImagen,
            $this->altoImagen,
            'png'
        );
        // Dibujamos las líneas de encabezado
        $this->SetFont('univiapro', 'B', 7);

        if (trim($linea1) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea1, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea2) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea2, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea3) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea3, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea4) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea4, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea5) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea5, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea6) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea6, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }
        if (trim($linea7) != '') {
            $this->SetX(10);
            $this->Cell(235, 5, $linea7, 0, 0, 'C');
            $this->SetY($this->GetY() + 4);
            $nLineas++;
        }

        // Dejamos un espacio para la siguiente sección
        if ($nlineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        // Límite para asegurar la buena visilibidad del reporte y el correcto aprovechamiento de pantalla
        // Si sale sobreencimado con los encabezados de columna, tal vez haya que revisar las líneas del encabezado
        $this->setY(35);
    }

    public function Header()
    { //Encabezado del documento
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');
        $this->AddFont('univiapro');
        $row = $this->getDatos();

        $Linea0 = '';
        $Linea1 = '';
        $Linea2 = '';
        $Linea3 = '';
        $Linea4 = '';
        $Linea5 = '';
        $Linea6 = '';

        if ($_SESSION[_S]['reportes'] != 'C91') {
            $Linea0 = utf8Decode('Cuenta de la Hacienda Pública Estatal ' . substr(
                $_SESSION[_S]['ejercicio'],
                0,
                4
            ));
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {
            $linea1 = utf8Decode('Secretaría de Finanzas del Gobierno del Estado de Oaxaca');
        } else {
            $linea1 = utf8Decode('Gobierno del Estado de Oaxaca');
        }

        if ($_SESSION[_S]['nvj_id'] > 2) {

            switch ($_POST['tipoReporte']) {
                case 2: // Todas las URs de la carga de trabajo
                    $linea2 = utf8Decode('Dirección de Contabilidad Gubernamental');
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $linea2 = utf8Decode(isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial']
                        : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO');
                    break;
                default:
                    if ($this->ejercicio == '') {
                        $linea2 = utf8Decode('Dirección de Contabilidad Gubernamental: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
                    } else {
                        $linea2 = utf8Decode('Dirección de Contabilidad Gubernamental: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
                    }
                    break;
            }
        } else {
            if ($this->ejercicio == '') {
                $linea2 = utf8Decode('UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre());
            } else {
                $linea2 = utf8Decode('UR: ' . $this->ur['gpo_id'] . $this->ur['ur_id'] . ' - ' . $this->ur['ur_nombre']);
            }
        }

        $linea3 = '';
        if ($this->ue != '' && $this->numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('
                SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' AND ue_id = ' . $this->ue . ';
            ');
            $ejecutora->consultar();
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $linea3 = 'UE: ' . $ue_id['ue_id'] . ' - ' . utf8Decode($ue_id['ue_nombre']) . '';
        }

        $linea4 = utf8Decode('Estado de Situación Financiera');

        $mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '')
        ? $_POST['comboMesDesde'] : '';

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesId . ';';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar();
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '')
        ? $_POST['comboMesHasta'] : '';

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id=' . $mesHasta . ';';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar();
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        if ($_SESSION[_S]['reportes'] == 'C91') {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            } elseif (strtolower($rowMes2['msc_nombre']) == 'cierre') {
                $linea5 = utf8Decode('al cierre del ejercicio ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            } else {
                $linea5 = utf8Decode('Al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ));
            }
        } else {
            if ($this->mesDesde == 0 and $this->mesHasta == 0) {
                $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } elseif ($this->mesHasta == $this->mesDesde) {
                $linea5 = utf8Decode('Del 1° al ' . $rowMes['msc_dias'] . ' de ' . strtolower($rowMes['msc_nombre']) . ' de ' . substr(
                    $_SESSION[_S]['ejercicio'],
                    0,
                    4
                ) . ' y ' . substr($_SESSION[_S]['ejercicio'] - 1, 0, 4));
            } else {
                if ($this->mesDesde == 0) {
                    $linea5 = utf8Decode('Del mes de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr(
                        $_SESSION[_S]['ejercicio'],
                        0,
                        4
                    ) . ' y ' . substr(
                        $_SESSION[_S]['ejercicio'] - 1,
                        0,
                        4
                    ));
                } else {
                    $linea5 = utf8Decode('Del 1° de ' . strtolower($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . strtolower($rowMes2['msc_nombre']) . ' de ' . substr(
                        $_SESSION[_S]['ejercicio'],
                        0,
                        4
                    ) . ' y ' . substr(
                        $_SESSION[_S]['ejercicio'] - 1,
                        0,
                        4
                    ));
                }
            }
        }

        if (isset($_POST['ckmiles'])) {
            $linea6 = '(Miles)';
        } else {
            $linea6 = '(Pesos)';
        }

        // Dibujamos el logo y las líneas del encabezado
        $this->drawHeadersAndLogo(
            'L',
            $linea0,
            $linea1,
            $linea2,
            $linea3,
            $linea4,
            $linea5,
            $linea6,
            $linea7
        );

        // Dibujamos el encabezado del reporte en sí...
        $this->setRelleno('si');
        $this->SetFont('univiapro', 'B', 7);
        $this->SetTextColor(0, 0, 0);
        $this->setX(10);
        $this->setDrawColor(170, 170, 170);
        $this->SetFillColor(199, 209, 219);
        $this->setAltoLinea(3);
        $this->SetBorders(1);
        if ($this->encabezadoTabla) {
            if ($_SESSION[_S]['reportes'] == 'C91') { //reporte financiero
                $this->SetWidths([101.5, 33.5, 4, 101.5, 33.5]);
                $this->SetAligns(['L', 'R', 'C', 'L', 'R']);
                $this->Row([
                   utf8Decode('CONCEPTO'),
                   utf8Decode('EJERCICIO FISCAL ' . substr(
                       $_SESSION[_S]['ejercicio'],
                       0,
                       4
                   )),
                    '',
                   utf8Decode('CONCEPTO'),
                   utf8Decode('EJERCICIO FISCAL ' . substr(
                       $_SESSION[_S]['ejercicio'],
                       0,
                       4
                   )),
                ]);
            } else { // cuenta publica
                $this->SetWidths([68, 33.5, 33.5, 4, 68, 33.5, 33.5]);
                $this->SetAligns(['L', 'R', 'R', 'C', 'L', 'R', 'R']);
                $this->Row([
                   utf8Decode('CONCEPTO'),
                   utf8Decode('EJERCICIO FISCAL ' . substr(
                       $_SESSION[_S]['ejercicio'],
                       0,
                       4
                   )),
                   utf8Decode('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio']
                        - 1, 0, 4)),
                    '',
                   utf8Decode('CONCEPTO'),
                   utf8Decode('EJERCICIO FISCAL ' . substr(
                       $_SESSION[_S]['ejercicio'],
                       0,
                       4
                   )),
                   utf8Decode('EJERCICIO FISCAL ' . substr($_SESSION[_S]['ejercicio']
                        - 1, 0, 4)),
                ]);
            }
        } else {
            $this->SetFont('univiapro', 'B', 6);
            $this->Cell(
                101.5,
                $altoLinea,
                $espacio . 'CUENTAS DE ORDEN',
                0,
                0,
                'L',
                $fill
            );
            $this->Ln();
            $this->Ln();
            $this->Ln();
            $this->SetY($this->GetY() + 5);
        }
        $this->setX(10);
        $this->setRelleno('no');
        $this->SetBorders(0);
    }
}
// Iniciamos el reporte
// --------------------------------------------------------------------------------------------------------------------------------
$mesId = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde']
: '';
$mesHasta = (isset($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1;
$tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte']
: '';
$checkMiles = (isset($_POST['ckmiles'])) ? 1000 : 1;
$grupos = isset($_POST['grupos']) ? $_POST['grupos'] : '';
$excluir = isset($_POST['excluir']) ? $_POST['excluir'] : '';
$cerosOrden = (isset($_POST['cerosOrden']) && $_POST['cerosOrden'] != '') ? $_POST['cerosOrden']
: 0;

$ue = isset($_POST['ue']) ? $_POST['ue'] : '';
$pdf = new Reportetabla('L', 'mm', 'A5');
$pdf->SetProtection(['print', 'annot-forms'], '', PDF_PASSWORD_OWNER_SCG);
$desde = $mesHasta + 1;
$hasta = -1;

if ($checkMiles == 1000) {
    $texto = '(en miles de pesos)';
} else {
    $texto = '(pesos)';
}

list($activo) = consolidacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue,
    $desde,
    $hasta,
    1,
    $tipoReporte,
    '',
    $checkMiles,
    $grupos,
    $excluir,
    $pdf->ceros,
    $pdf->incCta
);
list($pasivo) = consolidacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue,
    $desde,
    $hasta,
    2,
    $tipoReporte,
    '',
    $checkMiles,
    $grupos,
    $excluir,
    $pdf->ceros,
    $pdf->incCta
);
list($hacienda) = consolidacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue,
    $desde,
    $hasta,
    3,
    $tipoReporte,
    '',
    $checkMiles,
    $grupos,
    $excluir,
    $pdf->ceros,
    $pdf->incCta
);

$llavesAPH = array_merge(
    array_keys($activo),
    array_keys($pasivo),
    array_keys($hacienda)
); // Respaldamos las llaves/indices de las 3 listas
$aPH = array_combine($llavesAPH, array_merge($activo, $pasivo, $hacienda)); // Combinamos los arreglos conservando sus llaves de origen
if ($pdf->checkCuentaOrden == 1) {
    list($ctasOrden7) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        7,
        $tipoReporte,
        '',
        $checkMiles,
        $grupos,
        $excluir,
        $cerosOrden,
        $pdf->incCta
    );
    list($ctasOrden8) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        8,
        $tipoReporte,
        '',
        $checkMiles,
        $grupos,
        $excluir,
        $cerosOrden,
        $pdf->incCta
    );
}

if ($_SESSION[_S]['reportes'] == 'C111') {

    list($activo2020) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        1,
        $tipoReporte,
        2020,
        $checkMiles,
        $grupos,
        $excluir,
        $pdf->ceros,
        $pdf->incCta
    );
    list($pasivo2020) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        2,
        $tipoReporte,
        2020,
        $checkMiles,
        $grupos,
        $excluir,
        $pdf->ceros,
        $pdf->incCta
    );
    list($hacienda2020) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        3,
        $tipoReporte,
        2020,
        $checkMiles,
        $grupos,
        $excluir,
        $pdf->ceros,
        $pdf->incCta
    );

    $llavesAPH2020 = array_merge(
        array_keys($activo2020),
        array_keys($pasivo2020),
        array_keys($hacienda2020)
    ); // Respaldamos las llaves/indices de las 3 listas
    $aPH2020 = array_combine(
        $llavesAPH2020,
        array_merge($activo2020, $pasivo2020, $hacienda2020)
    ); // Combinamos los arreglos conservando sus llaves de origen

    list($ingresos2020) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        4,
        $tipoReporte,
        2020,
        $checkMiles,
        $grupos,
        $excluir,
        $pdf->ceros,
        $pdf->incCta
    );
    list($gastos2020) = consolidacion(
        $_SESSION[_S]['clasAdm-gpo'],
        $_SESSION[_S]['clasAdm-ur'],
        $ue,
        $desde,
        $hasta,
        5,
        $tipoReporte,
        2020,
        $checkMiles,
        $grupos,
        $excluir,
        $pdf->ceros,
        $pdf->incCta
    );
}

list($ingresos2022) = consolidacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue,
    $desde,
    $hasta,
    4,
    $tipoReporte,
    '',
    $checkMiles,
    $grupos,
    $excluir,
    $pdf->ceros,
    $pdf->incCta
);
list($gastos2022) = consolidacion(
    $_SESSION[_S]['clasAdm-gpo'],
    $_SESSION[_S]['clasAdm-ur'],
    $ue,
    $desde,
    $hasta,
    5,
    $tipoReporte,
    '',
    $checkMiles,
    $grupos,
    $excluir,
    $pdf->ceros,
    $pdf->incCta
);

$pdf->setOrientation('L');
$pdf->AliasNbPages();
$pdf->texto = $texto;
$pdf->AddPage();
$pdf->setRelleno('no');
$pdf->AddFont('univiapro', '');
$pdf->AddFont('univiapro', 'B');
$pdf->AddFont('univiapro');
$pdf->SetFont('univiapro', '', 7);

if ($_SESSION[_S]['reportes'] == 'C91') {
    $pdf->SetWidths([101.5, 33.5, 4, 101.5, 33.5]);
    $pdf->SetAligns(['L', 'R', 'C', 'L', 'R']);
} else {
    $pdf->SetWidths([68, 33.5, 33.5, 4, 68, 33.5, 33.5]);
    $pdf->SetAligns(['L', 'R', 'R', 'C', 'L', 'R', 'R']);
}

$totalA = 0;
$totalP = 0;
$totalH = 0;

$totalActivo2020 = $totalActivo2022 = $totalPasivo2020 = $totalPasivo2022 = $totalHacienda2020
= $totalHacienda2022 = 0;

$cuentasTotalesA = [11999, 12999, 13999];
$cuentasTotalesP = [21999, 22999, 23999, 31999, 32999, 33999, 34999, 35999,
    36999];

$totalActivo2022 = (($activo[1]['naturalezaCuenta'] == 'D') ? $activo[1]['debe']
    - $activo[1]['haber'] : $activo[1]['haber'] - $activo[1]['debe']);
$totalPasivo2022 = (($pasivo[2]['naturalezaCuenta'] == 'D') ? $pasivo[2]['debe']
    - $pasivo[2]['haber'] : $pasivo[2]['haber'] - $pasivo[2]['debe']);
$totalHacienda2022 = (($hacienda[3]['naturalezaCuenta'] == 'D') ? $hacienda[3]['debe']
    - $hacienda[3]['haber'] : $hacienda[3]['haber'] - $hacienda[3]['debe']);

if ($_SESSION[_S]['reportes'] == 'C111') {

    $totalActivo2020 = (($activo2020[1]['naturalezaCuenta'] == 'D') ? $activo2020[1]['debe']
        - $activo2020[1]['haber'] : $activo2020[1]['haber'] - $activo2020[1]['debe']);

    $totalPasivo2020 = (($pasivo2020[2]['naturalezaCuenta'] == 'D') ? $pasivo2020[2]['debe']
        - $pasivo2020[2]['haber'] : $pasivo2020[2]['haber'] - $pasivo2020[2]['debe']);
    $totalHacienda2020 = (($hacienda2020[3]['naturalezaCuenta'] == 'D') ? $hacienda2020[3]['debe']
        - $hacienda2020[3]['haber'] : $hacienda2020[3]['haber'] - $hacienda2020[3]['debe']);
}

$aPH[23999] = ['cuenta' => '23999', 'nombre' => 'TOTAL PASIVO', 'naturalezaCuenta' => $pasivo[2]['naturalezaCuenta'],
    'naturalezaDetalle' => '23999', 'debe' => $pasivo[2]['debe'], 'haber' => $pasivo[2]['haber'],
    'ger_id'            => 2, 'nivel' => 5]; // Sacamos el total del pasivo de acuerdo a la suma realizada anteriomente.
$aPH[34999] = ['cuenta' => '33999', 'nombre' => 'TOTAL HACIENDA', 'naturalezaCuenta' => $hacienda[3]['naturalezaCuenta'],
    'naturalezaDetalle' => '33999', 'debe' => $hacienda[3]['debe'], 'haber' => $hacienda[3]['haber'],
    'ger_id'            => 3, 'nivel' => 5]; //obtenemos el total de hacienda de la suma realizada anteriormente.

$cuentasNoValidas = [11999, 12999, 13999, 21999, 22999, 23999, 31999, 32999,
    33999, 34999, 35999, 36999]; // creamos array de las cuentas ficticias creadas.

foreach ($aPH as $key => $value) {

    if ($aPH[$key]['nivel'] == 2) {
        switch ($aPH[$key]['ger_id']) {
            case '1':
                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = ['cuenta' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'nombre'                                                          => 'Total de ' . $aPH[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH[$key]['naturalezaCuenta'],
                    'naturalezaDetalle'                                               => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'debe'                                                            => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                    'ger_id'                                                          => 1, 'nivel' => 5];
                break;
            case '2':
                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = ['cuenta' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'nombre'                                                          => 'Total de ' . $aPH[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH[$key]['naturalezaCuenta'],
                    'naturalezaDetalle'                                               => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'debe'                                                            => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                    'ger_id'                                                          => 2, 'nivel' => 5];
                break;
            case '3':
                $aPH[$aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999'] = ['cuenta' => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'nombre'                                                          => 'Total de ' . $aPH[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH[$key]['naturalezaCuenta'],
                    'naturalezaDetalle'                                               => $aPH[$key]['ger_id'] . $aPH[$key]['cgp_id'] . '999',
                    'debe'                                                            => $aPH[$key]['debe'], 'haber' => $aPH[$key]['haber'],
                    'ger_id'                                                          => 3, 'nivel' => 5];
                break;
        }
    }
    $aPH[$key]['naturalezaDetalle'] = str_replace('.', '', $aPH[$key]['cuenta']); //Respaldamos en naturalezaDetalle el la cuenta sin puntos
    $aPH[$key]['cuenta'] = str_replace('.', '', $aPH[$key]['cuenta']); // A cuenta le quitamos los puntos.
    $aPH[$key]['cuenta'] = str_pad($aPH[$key]['cuenta'], 5, '0', STR_PAD_RIGHT); // llenamos de 0's a la derecha en el campo cuenta para poder ordenarlo de acuerdo a ese criterio
}

$estadoConsolidado = []; //array para informacion de activo
$estadoConsolidadoP = []; // array para informacion de pasivo y hacienda.

$naturalezaGenero = $aPH[1]['naturalezaCuenta'];
$debe = 0;
$haber = 0;

foreach ($aPH as $i => $v) {

    if (isset($v['ger_id'])) {
        switch ($v['ger_id']) {
            case 1: //ACTIVO
                if ($pdf->nivel[$v['nivel']] == 1 || in_array(
                    $v['cuenta'],
                    $cuentasTotalesA
                )) {
                    $estadoConsolidado[$i]['cuenta'] = $v['naturalezaDetalle'];
                    $estadoConsolidado[$i]['orden'] = $v['cuenta'];
                    $estadoConsolidado[$i]['nivelCuenta'] = $v['nivel'];
                    $estadoConsolidado[$i]['nombre'] = $v['nombre'];

                    $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                    $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                    $estadoConsolidado[$i]['importe'] = (($v['naturalezaCuenta']
                        == 'D') ? $debe - $haber : $haber - $debe);

                    if ($v['nivel'] >= 3) {
                        $estadoConsolidado[$i]['importe'] = (($v['naturalezaCuenta']
                            != $naturalezaGenero) ? $estadoConsolidado[$i]['importe']
                             * -1 : $estadoConsolidado[$i]['importe']);
                    }

                    $estadoConsolidado[$i]['tipo'] = 'A';
                }
                break;
            case 2: //PASIVO
            case 3: //HACIENDA
                $debe = 0;
                $haber = 0;
                if ($pdf->nivel[$v['nivel']] == 1 || in_array(
                    $v['cuenta'],
                    $cuentasTotalesP
                )) {

                    $estadoConsolidadoP[$i]['cuenta'] = $v['naturalezaDetalle'];
                    $estadoConsolidadoP[$i]['orden'] = $v['cuenta'];
                    $estadoConsolidadoP[$i]['nivelCuenta'] = $v['nivel'];
                    $estadoConsolidadoP[$i]['nombre'] = $v['nombre'];

                    $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                    $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                    $estadoConsolidadoP[$i]['importe'] = (($v['naturalezaCuenta']
                        == 'D') ? $debe - $haber : $haber - $debe);
                    $estadoConsolidadoP[$i]['tipo'] = 'PH';
                }

                break;
        }
    }
}

$estadoConsolidado2020 = []; //array para informacion de activo
$estadoConsolidadoP2020 = []; // array para informacion de pasivo y hacienda.
$debe = 0;
$haber = 0;

if ($_SESSION[_S]['reportes'] == 'C111') {

    $aPH2020[23999] = ['cuenta' => '23999', 'nombre' => 'TOTAL PASIVO', 'naturalezaCuenta' => $pasivo2020[2]['naturalezaCuenta'],
        'naturalezaDetalle'     => '23999', 'debe' => $pasivo2020[2]['debe'], 'haber' => $pasivo2020[2]['haber'],
        'ger_id'                => 2, 'nivel' => 5]; // Sacamos el total del pasivo de acuerdo a la suma realizada anteriomente.
    $aPH2020[34999] = ['cuenta' => '33999', 'nombre' => 'TOTAL HACIENDA', 'naturalezaCuenta' => $hacienda2020[3]['naturalezaCuenta'],
        'naturalezaDetalle'     => '33999', 'debe' => $hacienda2020[3]['debe'], 'haber' => $hacienda2020[3]['haber'],
        'ger_id'                => 3, 'nivel' => 5]; //obtenemos el total de hacienda de la suma realizada anteriormente.

    foreach ($aPH2020 as $key => $value) {

        if ($aPH2020[$key]['nivel'] == 2) {
            switch ($aPH2020[$key]['ger_id']) {
                case '1': // Activo

                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999']
                    = ['cuenta'             => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'nombre'            => 'Total de ' . $aPH2020[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH2020[$key]['naturalezaCuenta'],
                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                        'ger_id'            => 1, 'nivel' => 5];
                    break;
                case '2': //Pasivo
                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999']
                    = ['cuenta'             => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'nombre'            => 'Total de ' . $aPH2020[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH2020[$key]['naturalezaCuenta'],
                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                        'ger_id'            => 2, 'nivel' => 5];
                    break;
                case '3': //Hacienda
                    $aPH2020[$aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999']
                    = ['cuenta'             => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'nombre'            => 'Total de ' . $aPH2020[$key]['nombre'] . '', 'naturalezaCuenta' => $aPH2020[$key]['naturalezaCuenta'],
                        'naturalezaDetalle' => $aPH2020[$key]['ger_id'] . $aPH2020[$key]['cgp_id'] . '999',
                        'debe'              => $aPH2020[$key]['debe'], 'haber' => $aPH2020[$key]['haber'],
                        'ger_id'            => 3, 'nivel' => 5];
                    break;
            }
        }
        $aPH2020[$key]['naturalezaDetalle'] = str_replace(
            '.',
            '',
            $aPH2020[$key]['cuenta']
        ); //Respaldamos en naturalezaDetalle el la cuenta sin puntos
        $aPH2020[$key]['cuenta'] = str_replace('.', '', $aPH2020[$key]['cuenta']); // A cuenta le quitamos los puntos.
        $aPH2020[$key]['cuenta'] = str_pad(
            $aPH2020[$key]['cuenta'],
            5,
            '0',
            STR_PAD_RIGHT
        ); // llenamos de 0's a la derecha en el campo cuenta para poder ordenarlo de acuerdo a ese criterio
    }

    $naturalezaGenero2020 = $aPH2020[1]['naturalezaCuenta'];

    foreach ($aPH2020 as $i => $v) {

        if (isset($v['ger_id'])) {
            switch ($v['ger_id']) {
                case 1:

                    if ($pdf->nivel[$v['nivel']] == 1 || in_array(
                        $v['cuenta'],
                        $cuentasTotalesA
                    )) {
                        $estadoConsolidado2020[$i]['cuenta'] = $v['naturalezaDetalle'];
                        $estadoConsolidado2020[$i]['orden'] = $v['cuenta'];
                        $estadoConsolidado2020[$i]['nivelCuenta'] = $v['nivel'];
                        $estadoConsolidado2020[$i]['nombre'] = $v['nombre'];

                        $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                        $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                        $estadoConsolidado2020[$i]['importe'] = (($v['naturalezaCuenta']
                            == 'D') ? $debe - $haber : $haber - $debe);

                        if ($v['nivel'] >= 3) {
                            $estadoConsolidado2020[$i]['importe'] = (($v['naturalezaCuenta']
                                != $naturalezaGenero2020) ? $estadoConsolidado2020[$i]['importe']
                                 * -1 : $estadoConsolidado2020[$i]['importe']);
                        }

                        $estadoConsolidado2020[$i]['tipo'] = 'A';
                    }
                    break;
                case 2:
                case 3:
                    $debe = 0;
                    $haber = 0;

                    if ($pdf->nivel[$v['nivel']] == 1 || in_array(
                        $v['cuenta'],
                        $cuentasTotalesP
                    )) {
                        $estadoConsolidadoP2020[$i]['cuenta'] = $v['naturalezaDetalle'];
                        $estadoConsolidadoP2020[$i]['orden'] = $v['cuenta'];
                        $estadoConsolidadoP2020[$i]['nivelCuenta'] = $v['nivel'];
                        $estadoConsolidadoP2020[$i]['nombre'] = $v['nombre'];

                        $debe = (is_numeric($v['debe']) ? $v['debe'] : 0.00);
                        $haber = (is_numeric($v['haber']) ? $v['haber'] : 0.00);

                        $estadoConsolidadoP2020[$i]['importe'] = (($v['naturalezaCuenta']
                            == 'D') ? $debe - $haber : $haber - $debe);
                        $estadoConsolidadoP2020[$i]['tipo'] = 'PH';
                    }
                    break;
            }
        }
    }
    $keyActivo2020 = key($estadoConsolidado2020);
}

$keyActivo2022 = key($estadoConsolidado);
$finalAct = [];

switch ($_SESSION[_S]['reportes']) {

    case 'C91': //estados financieros

        while ($keyActivo2022) {
            $finalAct[$keyActivo2022]['cuenta'] = $estadoConsolidado[$keyActivo2022]['cuenta'];
            $finalAct[$keyActivo2022]['nombre'] = $estadoConsolidado[$keyActivo2022]['nombre'];
            $finalAct[$keyActivo2022]['orden'] = $estadoConsolidado[$keyActivo2022]['orden'];
            $finalAct[$keyActivo2022]['nivelCuenta'] = $estadoConsolidado[$keyActivo2022]['nivelCuenta'];
            $finalAct[$keyActivo2022]['importe2022'] = $estadoConsolidado[$keyActivo2022]['importe'];
            $finalAct[$keyActivo2022]['importe2020'] = 0.00;

            next($estadoConsolidado);
            $keyActivo2022 = key($estadoConsolidado);
        }
        break;
    case 'C111': //cuenta publica

        while ($keyActivo2022 || $keyActivo2020) {

            if (CompararCadenas(
                (string) $keyActivo2022,
                (string) $keyActivo2020
            ) == 0) {

                $finalAct[$keyActivo2022]['cuenta'] = $estadoConsolidado[$keyActivo2022]['cuenta'];
                $finalAct[$keyActivo2022]['nombre'] = $estadoConsolidado[$keyActivo2022]['nombre'];
                $finalAct[$keyActivo2022]['orden'] = $estadoConsolidado[$keyActivo2022]['orden'];
                $finalAct[$keyActivo2022]['nivelCuenta'] = $estadoConsolidado[$keyActivo2022]['nivelCuenta'];
                $finalAct[$keyActivo2022]['importe2022'] = $estadoConsolidado[$keyActivo2022]['importe'];
                $finalAct[$keyActivo2022]['importe2020'] = $estadoConsolidado2020[$keyActivo2020]['importe'];

                next($estadoConsolidado);
                $keyActivo2022 = key($estadoConsolidado);

                next($estadoConsolidado2020);
                $keyActivo2020 = key($estadoConsolidado2020);
            } elseif (!$keyActivo2020 || CompararCadenas(
                (string) $keyActivo2022,
                (string) $keyActivo2020
            ) < 0) { //cuentas anteriores
                $finalAct[$keyActivo2022]['cuenta'] = $estadoConsolidado[$keyActivo2022]['cuenta'];
                $finalAct[$keyActivo2022]['nombre'] = $estadoConsolidado[$keyActivo2022]['nombre'];
                $finalAct[$keyActivo2022]['orden'] = $estadoConsolidado[$keyActivo2022]['orden'];
                $finalAct[$keyActivo2022]['nivelCuenta'] = $estadoConsolidado[$keyActivo2022]['nivelCuenta'];
                $finalAct[$keyActivo2022]['importe2022'] = $estadoConsolidado[$keyActivo2022]['importe'];
                $finalAct[$keyActivo2022]['importe2020'] = (isset($finalAct[$keyActivo2022]['importe2020'])
                    ? $finalAct[$keyActivo2022]['importe2020'] : 0.00);

                next($estadoConsolidado);
                $keyActivo2022 = key($estadoConsolidado);
            } else {

                $finalAct[$keyActivo2020]['cuenta'] = $estadoConsolidado2020[$keyActivo2020]['cuenta'];
                $finalAct[$keyActivo2020]['nombre'] = $estadoConsolidado2020[$keyActivo2020]['nombre'];
                $finalAct[$keyActivo2020]['orden'] = $estadoConsolidado2020[$keyActivo2020]['orden'];
                $finalAct[$keyActivo2020]['nivelCuenta'] = $estadoConsolidado2020[$keyActivo2020]['nivelCuenta'];
                $finalAct[$keyActivo2020]['importe2022'] = (isset($finalAct[$keyActivo2020]['importe2022'])
                    ? $finalAct[$keyActivo2020]['importe2022'] : 0.00);
                $finalAct[$keyActivo2020]['importe2020'] = $estadoConsolidado2020[$keyActivo2020]['importe'];

                next($estadoConsolidado2020);
                $keyActivo2020 = key($estadoConsolidado2020);
            }
        }
        break;
}

$keyPH2022 = key($estadoConsolidadoP);
$keyPH2020 = key($estadoConsolidadoP2020);
$finalPH = [];

switch ($_SESSION[_S]['reportes']) {

    case 'C91':
        while ($keyPH2022) {

            $finalPH[$keyPH2022]['cuenta'] = $estadoConsolidadoP[$keyPH2022]['cuenta'];
            $finalPH[$keyPH2022]['nombre'] = $estadoConsolidadoP[$keyPH2022]['nombre'];
            $finalPH[$keyPH2022]['orden'] = $estadoConsolidadoP[$keyPH2022]['orden'];
            $finalPH[$keyPH2022]['nivelCuenta'] = $estadoConsolidadoP[$keyPH2022]['nivelCuenta'];
            $finalPH[$keyPH2022]['importe2022'] = $estadoConsolidadoP[$keyPH2022]['importe'];
            $finalPH[$keyPH2022]['importe2020'] = 0.00;

            next($estadoConsolidadoP);
            $keyPH2022 = key($estadoConsolidadoP);
        }
        break;
    case 'C111':
        while ($keyPH2022 || $keyPH2020) {

            if (CompararCadenas((string) $keyPH2022, (string) $keyPH2020) == 0) {

                $finalPH[$keyPH2022]['cuenta'] = $estadoConsolidadoP[$keyPH2022]['cuenta'];
                $finalPH[$keyPH2022]['nombre'] = $estadoConsolidadoP[$keyPH2022]['nombre'];
                $finalPH[$keyPH2022]['orden'] = $estadoConsolidadoP[$keyPH2022]['orden'];
                $finalPH[$keyPH2022]['nivelCuenta'] = $estadoConsolidadoP[$keyPH2022]['nivelCuenta'];
                $finalPH[$keyPH2022]['importe2022'] = $estadoConsolidadoP[$keyPH2022]['importe'];
                $finalPH[$keyPH2022]['importe2020'] = $estadoConsolidadoP2020[$keyPH2020]['importe'];

                next($estadoConsolidadoP);
                $keyPH2022 = key($estadoConsolidadoP);

                next($estadoConsolidadoP2020);
                $keyPH2020 = key($estadoConsolidadoP2020);
            } elseif (!$keyPH2020 || CompararCadenas(
                (string) $keyPH2022,
                (string) $keyPH2020
            ) < 0) { //cuentas anteriores
                $finalPH[$keyPH2022]['cuenta'] = $estadoConsolidadoP[$keyPH2022]['cuenta'];
                $finalPH[$keyPH2022]['nombre'] = $estadoConsolidadoP[$keyPH2022]['nombre'];
                $finalPH[$keyPH2022]['orden'] = $estadoConsolidadoP[$keyPH2022]['orden'];
                $finalPH[$keyPH2022]['nivelCuenta'] = $estadoConsolidadoP[$keyPH2022]['nivelCuenta'];
                $finalPH[$keyPH2022]['importe2022'] = $estadoConsolidadoP[$keyPH2022]['importe'];
                $finalPH[$keyPH2022]['importe2020'] = (isset($finalPH[$keyPH2022]['importe2020'])
                    ? $finalPH[$keyPH2022]['importe2020'] : 0.00);

                next($estadoConsolidadoP);
                $keyPH2022 = key($estadoConsolidadoP);
            } else {

                $finalPH[$keyPH2020]['cuenta'] = $estadoConsolidadoP2020[$keyPH2020]['cuenta'];
                $finalPH[$keyPH2020]['nombre'] = $estadoConsolidadoP2020[$keyPH2020]['nombre'];
                $finalPH[$keyPH2020]['orden'] = $estadoConsolidadoP2020[$keyPH2020]['orden'];
                $finalPH[$keyPH2020]['nivelCuenta'] = $estadoConsolidadoP2020[$keyPH2020]['nivelCuenta'];
                $finalPH[$keyPH2020]['importe2022'] = (isset($finalPH[$keyPH2020]['importe2022'])
                    ? $finalPH[$keyPH2020]['importe2022'] : 0.00);
                $finalPH[$keyPH2020]['importe2020'] = $estadoConsolidadoP2020[$keyPH2020]['importe'];

                next($estadoConsolidadoP2020);
                $keyPH2020 = key($estadoConsolidadoP2020);
            }
        }
        break;
}

$sort = [];
foreach ($finalAct as $indice => $val) {
    $sort['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
}
array_multisort($sort['indice'], SORT_ASC, $finalAct); //Ordenamos de acuerdo al valor de la cuenta 2020.

$sort2 = [];
foreach ($finalPH as $indice => $val) {
    $sort2['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
}
array_multisort($sort2['indice'], SORT_ASC, $finalPH); //Ordenamos de acuerdo al valor de la cuenta 2018.

$max = max(count($finalAct), count($finalPH));

$max = $max += 2; // incrementamos a 2 el valor de max para pintar los totales globales.

if (count($finalAct) < $max) {
    $ini = count($finalAct);
    for ($i = $ini; $i < $max; $i++) {
        $finalAct[$i] = ['cuenta' => '', 'nombre' => '', 'importe2022' => '',
            'importe2020'         => '', 'nivelCuenta' => 5]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
    }
}
// si el pasivo+hacienda tiene menos elementos que el activo, lo igualamos
if (count($finalPH) < $max) {
    $ini = count($finalPH);
    for ($i = $ini; $i < $max; $i++) {
        $finalPH[$i] = ['cuenta' => '', 'nombre' => '', 'importe2022' => '',
            'importe2020'        => '', 'nivelCuenta' => 5]; // se rellenan con arreglos vacios, esto para igualar los arreglos.
    }
}

// Ponemos los totales globales
if ($_SESSION[_S]['reportes'] == 'C111') {
    $finalAct[$max - 1] = ['cuenta' => '13999', 'nombre' => 'TOTAL DE ACTIVOS',
        'importe2022'               => $totalActivo2022, 'importe2020' => $totalActivo2020, 'nivelCuenta' => 5];
}
// Se guardan en la ultima posicion el total del activo
else {
    $finalAct[$max - 1] = ['cuenta' => '13999', 'nombre' => 'TOTAL DE ACTIVOS',
        'importe2022'               => $totalActivo2022, 'nivelCuenta' => 5];
}
// Se guardan en la ultima posicion el total del activo

$ad2022I = (($ingresos2022[4]['naturalezaCuenta'] == 'D') ? ($ingresos2022[4]['debe']
    - $ingresos2022[4]['haber']) : ($ingresos2022[4]['haber'] - $ingresos2022[4]['debe']));
$ad2022G = (($gastos2022[5]['naturalezaCuenta'] == 'D') ? ($gastos2022[5]['debe']
    - $gastos2022[5]['haber']) : ($gastos2022[5]['haber'] - $gastos2022[5]['debe']));

if ($_SESSION[_S]['reportes'] == 'C111') {

    $ad2020I = (($ingresos2020[4]['naturalezaCuenta'] == 'D') ? ($ingresos2020[4]['debe']
        - $ingresos2020[4]['haber']) : ($ingresos2020[4]['haber'] - $ingresos2020[4]['debe']));
    $ad2020G = (($gastos2020[5]['naturalezaCuenta'] == 'D') ? ($gastos2020[5]['debe']
        - $gastos2020[5]['haber']) : ($gastos2020[5]['haber'] - $gastos2020[5]['debe']));
}
$ad2022 = $ad2022I - $ad2022G;
$ad2020 = $ad2020I - $ad2020G;

if ($_SESSION[_S]['reportes'] == 'C111') {
    $finalPH[$max - 2] = ['cuenta' => '35999', 'nombre' => 'Resultados del Ejercicio (Ahorro/Desahorro)',
        'importe2022'              => $ad2022, 'importe2020' => $ad2020, 'nivelCuenta' => 5]; // Se guardan en la ultima posicion el total del pasivo + hacienda.
    $finalPH[$max - 1] = ['cuenta'                     => '36999', 'nombre' => 'Total de Pasivo y Patrimonio/Hacienda Pública',
        'importe2022'                                  => $totalPasivo2022 + $totalHacienda2022 + $ad2022, 'importe2020' => $totalPasivo2020
         + $totalHacienda2020 + $ad2020, 'nivelCuenta' => 5]; // Se guardan en la ultima posicion el total del pasivo + hacienda.
} else {
    $finalPH[$max - 2] = ['cuenta' => '35999', 'nombre' => 'Resultados del Ejercicio (Ahorro/Desahorro)',
        'importe2022'              => $ad2022, 'nivelCuenta' => 5]; // Se guardan en la ultima posicion el total del pasivo + hacienda.
    $finalPH[$max - 1] = ['cuenta' => '36999', 'nombre' => 'Total de Pasivo y Patrimonio/Hacienda Pública',
        'importe2022'              => $totalPasivo2022 + $totalHacienda2022 + $ad2022, 'nivelCuenta' => 5];
}
$fill = false;
$altoLinea = $pdf->intLin;
$relleno = false; // Para determinar si se colorea de gris el fondo de la línea

$pdf->SetFillColor(192, 192, 192);

for ($x = 0; $x <= $max; $x++) {

    $nivelA = '';
    $cuentaA = '';
    $importeA = '';
    $importeA2020 = '';
    $nivelP = '';
    $cuentaP = '';
    $importeP = '';
    $importeP2020 = '';

    if (isset($finalAct[$x])) {
        if (substr($finalAct[$x]['cuenta'], $finalAct[$x]['nivelCuenta'] - 1, 1)
            != 0) {
            if ($pdf->nivel[$finalAct[$x]['nivelCuenta']] == 1 || in_array(
                $finalAct[$x]['cuenta'],
                $cuentasTotalesA
            )) {
                $nivelA = ((!in_array($finalAct[$x]['cuenta'], $cuentasNoValidas))
                    ? $finalAct[$x]['cuenta'] : '');
                $cuentaA = utf8Decode($finalAct[$x]['nombre']);
                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $importeA = (($finalAct[$x]['importe2022'] != 0) ? number_format(round($finalAct[$x]['importe2022']
                        / $checkMiles, 0), 0) : (($finalAct[$x]['cuenta']
                        != '') ? number_format(0, 0) : ''));
                    $importeA2020 = (($finalAct[$x]['importe2020'] != 0) ? number_format(round($finalAct[$x]['importe2020']
                        / $checkMiles, 0), 0) : (($finalAct[$x]['cuenta']
                        != '') ? number_format(0, 0) : ''));
                } else {
                    $importeA = (($finalAct[$x]['importe2022'] != 0) ? number_format($finalAct[$x]['importe2022']
                        / $checkMiles, 2) : (($finalAct[$x]['cuenta'] != '')
                        ? number_format(0, 0) : ''));
                    $importeA2020 = (($finalAct[$x]['importe2020'] != 0) ? number_format($finalAct[$x]['importe2020']
                        / $checkMiles, 2) : (($finalAct[$x]['cuenta'] != '')
                        ? number_format(0, 0) : ''));
                }
            }

            $importeA = (($finalAct[$x]['nivelCuenta'] != 1 && $finalAct[$x]['nivelCuenta']
                != 2 && $cuentaA != '') ? $importeA : '');
            $importeA2020 = (($finalAct[$x]['nivelCuenta'] != 1 && $finalAct[$x]['nivelCuenta']
                != 2 && $cuentaA != '') ? $importeA2020 : '');
        }
    }

    if (isset($finalPH[$x])) {
        if (substr($finalPH[$x]['cuenta'], $finalPH[$x]['nivelCuenta'] - 1, 1) != 0) {
            if ($pdf->nivel[$finalPH[$x]['nivelCuenta']] == 1 || in_array(
                $finalPH[$x]['cuenta'],
                $cuentasTotalesP
            )) {
                $nivelP = ((!in_array($finalPH[$x]['cuenta'], $cuentasNoValidas))
                    ? $finalPH[$x]['cuenta'] : '');
                $cuentaP = utf8Decode($finalPH[$x]['nombre']);
                if ($_SESSION[_S]['reportes'] == 'C111') {
                    $importeP = (($finalPH[$x]['importe2022'] != 0) ? number_format(round($finalPH[$x]['importe2022']
                        / $checkMiles, 0), 0) : (($finalPH[$x]['cuenta']
                        != '') ? number_format(0, 0) : ''));
                    $importeP2020 = (($finalPH[$x]['importe2020'] != 0) ? number_format(round($finalPH[$x]['importe2020']
                        / $checkMiles, 0), 0) : (($finalPH[$x]['cuenta']
                        != '') ? number_format(0, 0) : ''));
                } else {
                    $importeP = (($finalPH[$x]['importe2022'] != 0) ? number_format($finalPH[$x]['importe2022']
                        / $checkMiles, 2) : (($finalPH[$x]['cuenta'] != '') ? number_format(
                            0,
                            0
                        ) : ''));
                    $importeP2020 = (($finalPH[$x]['importe2020'] != 0) ? number_format($finalPH[$x]['importe2020']
                        / $checkMiles, 2) : (($finalPH[$x]['cuenta'] != '') ? number_format(
                            0,
                            0
                        ) : ''));
                }
            }
            $importeP = (($finalPH[$x]['nivelCuenta'] != 1 && $finalPH[$x]['nivelCuenta']
                != 2 && $cuentaP != '') ? $importeP : '');
            $importeP2020 = (($finalPH[$x]['nivelCuenta'] != 1 && $finalPH[$x]['nivelCuenta']
                != 2 && $cuentaP != '') ? $importeP2020 : '');
        }
    }

    $espacio = '';
    if ($cuentaA != '' || $cuentaP != '') {
        if ($_SESSION[_S]['reportes'] == 'C111') {
            $pdf->longitud2 = 54;
        }

        switch ($finalAct[$x]['nivelCuenta']) {
            case 2:
                $pdf->SetFont('univiapro', 'B', 5.4);
                $espacio = '   ';
                break;
            case 3:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '      ';
                break;
            case 4:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '         ';
                break;
            case 5:
                if (in_array($finalAct[$x]['cuenta'], $cuentasTotalesA)) {
                    $pdf->SetFont('univiapro', 'B', 6);
                }
                $espacio = '            ';
                break;
            default:
                $pdf->SetFont('univiapro', '', 6);
                break;
        }

        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
        // independientemente de la configuración que traiga
        // VALIDAMOS LA PARTE DE ACTIVO
        if (trim($cuentaA) != '') {
            $fill = $relleno;
        } else {
            $fill = false;
        }

        // ==============================================================================
        // ==============================================================================
        // AQUÍ EMPIEZA EL REPORTE
        // ==============================================================================
        // ==============================================================================

        $pdf->setX(10);

        if ($_SESSION[_S]['reportes'] == 'C91') {
            $pdf->Cell(
                101.5,
                $altoLinea,
                $espacio . substr($cuentaA, 0, $pdf->longitud2),
                0,
                0,
                'L',
                $fill
            );
            $pdf->Cell(33.5, $altoLinea, $importeA, 0, 0, 'R', $fill);
        } else {
            $pdf->Cell(
                68,
                $altoLinea,
                $espacio . substr($cuentaA, 0, $pdf->longitud2),
                0,
                0,
                'L',
                $fill
            );
            $pdf->Cell(33.5, $altoLinea, $importeA, 0, 0, 'R', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeA2020, 0, 0, 'R', $fill);
        }
        // Líneas verticales separadora
        $pdf->Cell(4, $altoLinea, '', 'LR', 0, 'R', $fill);

        switch ($finalPH[$x]['nivelCuenta']) {
            case 2:
                $pdf->SetFont('univiapro', 'B', 5.4);
                $espacio = '   ';
                break;
            case 3:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '      ';
                break;
            case 4:
                $pdf->SetFont('univiapro', '', 6);
                $espacio = '         ';
                break;
            case 5:
                if (in_array($finalPH[$x]['cuenta'], $cuentasTotalesP)) {
                    $pdf->SetFont('univiapro', 'B', 6);
                }
                $espacio = '            ';
                break;
            default:
                $pdf->SetFont('univiapro', '', 6);
                break;
        }

        // Si hay datos en la línea... se rellena el fondo... si no hay datos no
        // independientemente de la configuración que traiga
        // VALIDAMOS LA PARTE DE PASIVO
        if (trim($cuentaP) != '') {
            $fill = $relleno;
        } else {
            $fill = false;
        }

        if ($_SESSION[_S]['reportes'] == 'C91') {
            $pdf->Cell(
                101.5,
                $altoLinea,
                $espacio . substr($cuentaP, 0, $pdf->longitud2),
                0,
                0,
                'L',
                $fill
            );
            $pdf->Cell(33.5, $altoLinea, $importeP, 0, 1, 'R', $fill);
        } else {
            $pdf->Cell(
                68,
                $altoLinea,
                $espacio . substr($cuentaP, 0, $pdf->longitud2),
                0,
                0,
                'L',
                $fill
            );
            $pdf->Cell(33.5, $altoLinea, $importeP, 0, 0, 'R', $fill);
            $pdf->Cell(33.5, $altoLinea, $importeP2020, 0, 1, 'R', $fill);
        }
        $con2 = $con = 1;

        if (strlen($cuentaA) > $pdf->longitud2 * $con2 || strlen($cuentaP) > $pdf->longitud2
             * $con) {

            switch ($finalAct[$x]['nivelCuenta']) {
                case 2:
                    $pdf->SetFont('univiapro', 'B', 5.4);
                    $espacio = '   ';
                    break;
                case 3:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '      ';
                    break;
                case 4:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '         ';
                    break;
                case 5:
                    if (in_array($finalAct[$x]['cuenta'], $cuentasTotalesA)) {
                        $pdf->SetFont('univiapro', 'B', 6);
                    }
                    $espacio = '            ';
                    break;
                default:
                    $pdf->SetFont('univiapro', '', 6);
                    break;
            }

            if ($_SESSION[_S]['reportes'] == 'C91') {
                $pdf->Cell(
                    101.5,
                    $altoLinea,
                    $espacio . substr(
                        $cuentaA,
                        $pdf->longitud2 * $con2,
                        $pdf->longitud2
                    ),
                    0,
                    0,
                    'L',
                    $fill
                );
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
            } else {
                $pdf->Cell(
                    68,
                    $altoLinea,
                    $espacio . substr(
                        $cuentaA,
                        $pdf->longitud2 * $con2,
                        $pdf->longitud2
                    ),
                    0,
                    0,
                    'L',
                    $fill
                );
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
            }
            $pdf->Cell(4, $altoLinea, '', 'LR', 0, 'R', $fill);

            switch ($finalPH[$x]['nivelCuenta']) {
                case 2:
                    $pdf->SetFont('univiapro', 'B', 5.4);
                    $espacio = '   ';
                    break;
                case 3:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '      ';
                    break;
                case 4:
                    $pdf->SetFont('univiapro', '', 6);
                    $espacio = '         ';
                    break;
                case 5:
                    if (in_array($finalPH[$x]['cuenta'], $cuentasTotalesP)) {
                        $pdf->SetFont('univiapro', 'B', 6);
                    }
                    $espacio = '            ';
                    break;
                default:
                    $pdf->SetFont('univiapro', '', 6);
                    break;
            }

            if ($_SESSION[_S]['reportes'] == 'C91') {
                $pdf->Cell(
                    101.5,
                    $altoLinea,
                    $espacio . substr(
                        $cuentaP,
                        $pdf->longitud2 * $con,
                        $pdf->longitud2
                    ),
                    0,
                    0,
                    'L',
                    $fill
                );
                $pdf->Cell(33.5, $altoLinea, '', 0, 1, 'C', $fill);
            } else {
                $pdf->Cell(
                    68,
                    $altoLinea,
                    $espacio . substr(
                        $cuentaP,
                        $pdf->longitud2 * $con,
                        $pdf->longitud2
                    ),
                    0,
                    0,
                    'L',
                    $fill
                );
                $pdf->Cell(33.5, $altoLinea, '', 0, 0, 'R', $fill);
                $pdf->Cell(33.5, $altoLinea, '', 0, 1, 'C', $fill);
            }

            $con2++;
            $con++;
        }

        $relleno = ($pdf->incCta == '') ? false : !$relleno; // Para determinar si se colorea de gris el fondo de la línea
    }

    if ($pdf->getY() > 175) {
        $pdf->addPage();
    }
}

if ($pdf->checkCuentaOrden == 1) {
    $pdf->Ln();
    $pdf->Ln();
    $pdf->Ln();
    $pdf->SetFont('univiapro', 'B', 6);
    $espacio = '            ';
    $espacio = '';
    $pdf->setX(15);

    $pdf->Cell(101.5, $altoLinea, $espacio . 'CUENTAS DE ORDEN', 0, 0, 'L', $fill);
    $pdf->Ln();
    $nivelesOrden[1] = 0;
    $nivelesOrden[2] = 0;
    $nivelesOrden[3] = 0;
    $nivelesOrden[4] = 0;
    $nivelesOrden[5] = 1;
    $ctasOrden = array_merge(
        devuelveArrayOrdenado($ctasOrden7, $nivelesOrden),
        devuelveArrayOrdenado($ctasOrden8, $nivelesOrden)
    );

    foreach ($ctasOrden as $cuentas) {
        $pdf->SetFont('univiapro', '', 6);
        $pdf->setX(15);
        if ($cuentas['nivel'] == 5) {
            $cuentaOrden = $cuentas['cgsc_cuenta'];
            if ($cuentaOrden != '8.1.1.0.0') {
                $pdf->Cell(
                    150,
                    $altoLinea,
                    utf8Decode($espacio . $cuentas['cgsc_nombre']),
                    0,
                    0,
                    'L',
                    $fill
                );
                $importeCta0rden = (($cuentas['importe'] != 0) ? number_format($cuentas['importe']
                    / $checkMiles, 2) : (($cuentas['importe'] != '') ? number_format(
                        0,
                        0
                    ) : '0'));
                $pdf->Cell(
                    50.5,
                    $altoLinea,
                    $espacio . ($importeCta0rden),
                    0,
                    0,
                    'R',
                    $fill
                );
                $pdf->Ln();
            }
        }

        if ($pdf->getY() > 175) {
            $pdf->encabezadoTabla = false;
            $pdf->addPage();
        }
    }
}

//FUNCION PARA DEVOLVER ARRAY ORDENADO
function devuelveArrayOrdenado($array, $p)
{
    $arreglo = [];
    $total = 0;
    if (is_array($array)) {
        //naturaleza de la cuenta (activo)
        $naturalezaActivo = '';

        foreach ($array as $key => $val) {
            if ($p[$array[$key]['nivel']] == 1) {
                //Agregamos a la llave del arreglo 0's a la derecha
                $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)] = $array[$key];

                //obtenemos los totales de los grupos
                if ($arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)]['nivel'] == 2) {
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 999] = $array[$key];
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 999]['cuenta']
                    = (str_pad($key, 5, '0', STR_PAD_RIGHT) + 999);
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 999]['nivel']
                    = 1;
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 999]['nombre']
                    = 'TOTAL DE ' . $array[$key]['cgp_nombre'];
                }
                //obtenemos el total del genero.
                if ($arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)]['nivel'] == 1) {
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 9999] = $array[$key];
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 9999]['cuenta']
                    = (str_pad($key, 5, '0', STR_PAD_RIGHT) + 9999);
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 9999]['nivel']
                    = 1;
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT) + 9999]['nombre']
                    = 'TOTAL DE ' . $array[$key]['ger_nombre'];
                }
            }
        }

        //ordenamos arreglo
        ksort($arreglo);

        foreach ($arreglo as $indice => $val) {

            if ($arreglo[$indice]['nivel'] == 1 && $arreglo[$indice]['ger_id'] == 1) {
                $naturalezaActivo = $arreglo[$indice]['naturalezaCuenta'];
            }

            //obtenemos los importes, si la naturaleza de la cuenta es Debe entonces se resta debe - haber, sino lo contrario
            $debe = (is_numeric($arreglo[$indice]['debe']) ? $arreglo[$indice]['debe']
                : 0.00);
            $haber = (is_numeric($arreglo[$indice]['haber']) ? $arreglo[$indice]['haber']
                : 0.00);
            $arreglo[$indice]['importe'] = (($arreglo[$indice]['naturalezaCuenta']
                == 'D') ? $debe - $haber : $haber - $debe);
            //Si es cuenta del activo y la naturaleza de la cuenta es diferente a la del genero se pone el importe en negativo.
            if ($arreglo[$indice]['ger_id'] == 1 && $arreglo[$indice]['naturalezaCuenta']
                != $naturalezaActivo && $arreglo[$indice]['nivel'] >= 3) {
                $arreglo[$indice]['importe'] = ($arreglo[$indice]['importe']
                     * -1);
            }

        }

        //regresamos arreglo ordenado de manera ascendente
        return $arreglo;
    }
}
$pdf->Output();

// Fin del código fuente
