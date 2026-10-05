<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

set_time_limit(0);

//librerias
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/Math.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';

class exp_AnaliticoDeudaOtrosPasivos extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $nombreArchivoReporte;
    public $ur;
    public $ejercicioActual;
    public $ejercicioAnterior;
    public $tipoReporte;
    public $MilPesos;
    public $mesDesde ;
    public $mesHasta;
    public $grupos;
    public $excluir;
    public $consolidado;
    public $ceros;
    public $incCta;
    public $ue;
    public $numUe;
    public $leyenda;
    public $firmante1;
    public $firmante2;
    public $firmante3;
    public $cargo1;
    public $cargo2;
    public $cargo3;
    public $desde;
    public $hasta;
    public $mesNombre;
    public $chkEnMiles;
    public $cerosOrden;
    public $arrayApe2014;
    public $arrayDesdeHasta2014;
    public $cuentasDeuda;
    public $moneda;
    public $pais;
    public $res2131;
    public $res223;
    public $res2116;
    public $saldoInicial;
    public $saldoFInal;

    public function __construct()
    {
        parent::__construct();

        $ur = new UnidadResponsable();
        $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $ur->select();
        $ejercicio = '';
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        $this->ejercicioAnterior = $this->ejercicioActual - 1;

        // NOTA: Cambio en los encabezados del reporte solicitado en el Ticket #200644
        $this->encabLin1 = 'Gobierno del Estado de Oaxaca';
        $this->encabLin6 = '';
        $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
        $this->chkEnMiles = isset($_POST['MilPes']) ? 1000 : 1;
        $this->mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != '') ? $_POST['grupos'] : '';
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != '') ? $_POST['excluir'] : '';
        $consolidado = (isset($_POST['consolidado']) && $_POST['consolidado'] != '') ? $_POST['consolidado'] : '';
        $ceros = (isset($_POST['ceros']) && $_POST['ceros'] != '') ? $_POST['ceros'] : '';
        $this->cerosOrden = (isset($_POST['cerosOrden']) && $_POST['cerosOrden'] != '') ? $_POST['cerosOrden'] : 0;
        $this->incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
        $ue = isset($_POST['ue']) ? $_POST['ue'] : '';
        $numUe = isset($_POST['numUe']) ? $_POST['numUe'] : '';
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : '';
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : '';
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : '';
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : '';
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : '';
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : '';
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : '';

        if ($ejercicio == '') {
            $ur = new UnidadResponsable();
            $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ur->select();
        }

        $this->encabLin3 = 'Estado Analítico de la Deuda y Otros Pasivos';
        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($tipoReporte) {
                case 2: // Todas las URs de la carga de trabajo
                    $this->encabLin2 = 'Dirección de Contabilidad Gubernamental';
                    break;
                case 3: // Determinar qué incluir y qué excluir
                    $this->encabLin2 = 'Dirección de Contabilidad Gubernamental';
                    $this->encabLin6 = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : 'ERROR DE PROGRAMA. NO EXISTE ENCABEZADO';
                    break;
                default:
                    if ($ejercicio == '') {
                        $this->encabLin2 = $ur->getClave() . ' - ' . UrNombre::titleCase($ur->getNombre());
                    } else {
                        $this->encabLin2 = $ur['gpo_id'] . $ur['ur_id'] . ' - ' . UrNombre::titleCase($ur['ur_nombre']);
                    }
                    break;
            }
        } else {

            if ($ejercicio == '') {
                $this->encabLin2 = 'UR: ' . $ur->getClave() . ' - ' . UrNombre::titleCase($ur->getNombre());
            } else {
                $this->encabLin2 = 'UR: ' . $ur['gpo_id'] . $ur['ur_id'] . ' - ' . UrNombre::titleCase($ur['ur_nombre']);
            }
        }

        $texto2 = '';
        if ($ue != '' && $numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?;');
            $ejecutora->consultar([$ur->getGpo_id(), $ur->getUr_id(), $ue]);
            $ejecutora->desconectar();
            $ue_id = $ejecutora->extraer();
            $texto2 = 'UE: ' . $ue_id['ue_id'] . ' - ' . $ue_id['ue_nombre'] . '';
        }
        $this->encabLin7 = '';
        if ($ue != '') {
            $this->encabLin7 = $texto2;
        }

        $mesDesde = $this->mesDesde;

        $sqlMes = ' select msc_nombre, msc_dias from cat_MesContable where msc_id = ?;';
        $con = new AccesoDatos();
        $con->conectar();
        $con->setSql($sqlMes);
        $con->consultar([$mesDesde]);
        $con->desconectar();
        $rowMes = $con->extraer();

        $mesHasta = $this->mesHasta;

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar([$mesHasta]);
        $con2->desconectar();
        $rowMes2 = $con2->extraer();

        $this->mesNombre = strtoupper($rowMes2['msc_nombre']) ;

        if ($_SESSION[_S]['reportes'] == 'C92') {
            if ($mesDesde == 0 and $mesHasta == 0) {
                $this->encabLin4 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' ' . $this->ejercicioActual);
            } elseif ($mesDesde == 13 and $mesHasta == 13) {
                $this->encabLin4 = utf8Decode('Al cierre del ejercicio ' . $this->ejercicioActual);
            } elseif ($mesHasta == $mesDesde) {
                $this->encabLin4 = 'Del 1° al ' . $rowMes['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . $this->ejercicioActual;
            } else {
                if ($mesDesde == 0) {
                    $this->encabLin4 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual);
                } elseif ($mesHasta == 13) {
                    $this->encabLin4 = 'Del 1° de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al cierre del ejercicio ' . $this->ejercicioActual;
                } else {
                    $this->encabLin4 = 'Del 1° de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual;
                }
            }
        } else {
            if ($mesDesde == 0 and $mesHasta == 13) {
                $this->encabLin4 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual);
            } elseif (($mesDesde != 0 and $mesDesde != 13) and $mesHasta == 13) {
                $this->encabLin4 = 'Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual;
            } elseif ($mesDesde == 0 and $mesHasta == 0) {
                $this->encabLin4 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' ' . $this->ejercicioActual);
            } elseif ($mesHasta == $mesDesde) {
                if ($mesDesde == 13 and $mesHasta == 13) {
                    $this->encabLin4 = utf8Decode('Al ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . $this->ejercicioActual);
                } else {
                    $this->encabLin4 = 'Del 01 al ' . $rowMes['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' de ' . $this->ejercicioActual;
                }
            } else {
                if ($mesDesde == 0) {
                    $this->encabLin4 = utf8Decode('Del mes de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual);
                } else {
                    $this->encabLin4 = 'Del 01 de ' . UrNombre::titleCase($rowMes['msc_nombre']) . ' al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . $this->ejercicioActual;
                }
            }
        }

        if ($this->chkEnMiles == 1000) {
            $this->encabLin5 = '(Cifras en Miles de Pesos)';
        } else {
            $this->encabLin5 = '(Cifras en Pesos)';
        }

        $this->arrayApe2014 = $this->consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, substr($_SESSION[_S]['ejercicio'], 0, 4), $tipoReporte, $grupos, $excluir, $this->incCta);
        $this->arrayDesdeHasta2014 = $this->consultaCuentas($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, substr($_SESSION[_S]['ejercicio'], 0, 4), $tipoReporte, $grupos, $excluir, $this->incCta);

        $res2131 = ($this->arrayApe2014[2131]['resultado'] + $this->arrayDesdeHasta2014[2131]['resultado']);
        $res223 = ($this->arrayApe2014[223]['resultado'] + $this->arrayDesdeHasta2014[223]['resultado']);
        $res2116 = ($this->arrayApe2014[2116]['resultado'] + $this->arrayDesdeHasta2014[2116]['resultado']);

        $totalApe = ($this->arrayApe2014[2131]['resultado'] + $this->arrayApe2014[223]['resultado'] + $this->arrayApe2014[2116]['resultado']) / $this->chkEnMiles;
        $totalPeriodo = ($res2131 + $res223 + $res2116) / $this->chkEnMiles;

        $this->cuentasDeuda[1] = [
                'nombre'       => '1_DEUDA PÚBLICA',
                'nivel'        => 1,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        //DEUDA A CORTO PLAZO
        $this->cuentasDeuda[2] = [
                'nombre'       => '9_Corto Plazo',
                'nivel'        => 9,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[3] = [
                'nombre'       => '2_Deuda Interna',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];

        /*****************VALIDACIONES PARA COLUMNAS DE SALDOS (CTA 2131)**********************/
        // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
        if ($this->incCta == '1') {
            $strPasoTexto = '2.2.3 Instituciones de Crédito';
        } else {
            $strPasoTexto = 'Instituciones de Crédito';
        }

        if (($this->arrayApe2014[2131]['resultado'] > 0) || ($res2131 > 0)) {
            $moneda = 'Pesos';
            $pais = utf8Decode('México');
        } else {
            $moneda = '';
            $pais = '';
        }

        //Residuo
        if (($this->arrayApe2014[2131]['resultado'] / $this->chkEnMiles) == 0) {
            $decimales = 0;
        } else {
            $decimales = 0;
        }
        $saldoInicial = round($this->arrayApe2014[2131]['resultado'] / $this->chkEnMiles, $decimales);

        //Residuo
        if (($res2131 / $this->chkEnMiles) == 0) {
            $decimales = 0;
        } else {
            $decimales = 0;
        }
        $saldoFinal = round($res2131 / $this->chkEnMiles, 0);

        $this->cuentasDeuda[4] = [
                'nombre'       => '3_' . $strPasoTexto,
                'nivel'        => 3,
                'moneda'       => $moneda,
                'institucion'  => $pais,
                'saldoInicial' => $saldoInicial,
                'saldoFinal'   => $saldoFinal,
            ];
        $this->cuentasDeuda[5] = [
                'nombre'       => '3_Títulos y Valores',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[6] = [
                'nombre'       => '3_Arrendamientos Financieros',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[7] = [
                'nombre'       => '',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[8] = [
                'nombre'       => '2_Deuda Externa',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[9] = [
                'nombre'       => '3_Organismos Financieros Internacionales',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[10] = [
                'nombre'       => '3_Deuda Bilateral',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[11] = [
                'nombre'       => '3_Títulos y Valores',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[12] = [
                'nombre'       => '3_Arrendamientos Financieros',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[13] = [
                'nombre'       => '',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[14] = [
                'nombre'       => '2_Subtotal de la Deuda Pública a Corto Plazo',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => $saldoInicial,
                'saldoFinal'   => $saldoFinal,
            ];
        $this->cuentasDeuda[15] = [
                'nombre'       => '',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        //DEUDA A LARGO PLAZO
        $this->cuentasDeuda[16] = [
                'nombre'       => '9_Largo Plazo',
                'nivel'        => 9,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[17] = [
                'nombre'       => '2_Deuda Interna',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        /*****************VALIDACIONES PARA COLUMNAS DE SALDOS (CTA 223)**********************/
        // Si quieren -para efectos de validación- incluir la cuenta en el nombre...
        if ($this->incCta == '1') {
            $strPasoTexto = '2.2.3 Instituciones de Crédito';
        } else {
            $strPasoTexto = 'Instituciones de Crédito';
        }

        if (($this->arrayApe2014[223]['resultado'] > 0) || ($res223 > 0)) {
            $moneda = 'Pesos';
            $pais = utf8Decode('México');
        } else {
            $moneda = '';
            $pais = '';
        }

        //Residuo
        if (($this->arrayApe2014[223]['resultado'] / $this->chkEnMiles) == 0) {
            $decimales = 0;
        } else {
            $decimales = 0;
        }
        $saldoInicial = round($this->arrayApe2014[223]['resultado'] / $this->chkEnMiles, $decimales);

        //Residuo
        if (($res223 / $this->chkEnMiles) == 0) {
            $decimales = 0;
        } else {
            $decimales = 0;
        }
        $saldoFinal = round($res223 / $this->chkEnMiles, 0);

        $this->cuentasDeuda[18] = [
                'nombre'       => '3_' . $strPasoTexto,
                'nivel'        => 3,
                'moneda'       => $moneda,
                'institucion'  => $pais,
                'saldoInicial' => $saldoInicial,
                'saldoFinal'   => $saldoFinal,
            ];
        $this->cuentasDeuda[19] = [
                'nombre'       => '3_Títulos y Valores',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[20] = [
                'nombre'       => '3_Arrendamientos Financieros',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[21] = [
                'nombre'       => '',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[22] = [
                'nombre'       => '2_Deuda Externa',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[23] = [
                'nombre'       => '3_Organismos Financieros Internacionales',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[24] = [
                'nombre'       => '3_Deuda Bilateral',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[25] = [
                'nombre'       => '3_Títulos y Valores',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[26] = [
                'nombre'       => '3_Arrendamientos Financieros',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => 0,
                'saldoFinal'   => 0,
            ];
        $this->cuentasDeuda[27] = [
                'nombre'       => '',
                'nivel'        => 3,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[28] = [
                'nombre'       => '2_Subtotal de la Deuda Pública a Largo Plazo',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => $saldoInicial,
                'saldoFinal'   => $saldoFinal,
            ];
        $this->cuentasDeuda[29] = [
                'nombre'       => '',
                'nivel'        => 2,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        /*********  TOTAL OTRO PASIVOS ********************/
        $otrosPasInicial = round($this->arrayApe2014[2116]['resultado'] / $this->chkEnMiles, 2);
        $otrosPasFinal = round($res2116 / $this->chkEnMiles, 2);
        /******************************** */
        $this->cuentasDeuda[30] = [
                'nombre'       => '1_Total de Otros Pasivos',
                'nivel'        => 1,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => $otrosPasInicial,
                'saldoFinal'   => $otrosPasFinal,
            ];
        $this->cuentasDeuda[31] = [
                'nombre'       => '',
                'nivel'        => 1,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => '',
                'saldoFinal'   => '',
            ];
        $this->cuentasDeuda[32] = [
                'nombre'       => '1_Total de Deuda Pública y Otros Pasivos',
                'nivel'        => 1,
                'moneda'       => '',
                'institucion'  => '',
                'saldoInicial' => round($totalApe, 2),
                'saldoFinal'   => round($totalPeriodo, 0),
            ];

        $this->nombreArchivoReporte = 'EdoAnaliticoDeudaYOtrosPasivos_';
        $this->contenido();
    }


    // ============================================================================================
    // FUNCIONES DE APOYO
    // ============================================================================================


    public function consultaCuentas($gpo_id, $ur_id, $ue, $mesDesde, $mesHasta, $ejercicio, $tipoReporte, $grupos, $excluir, $incCta)
    {

        if ($tipoReporte == '') {
            $tipoReporte = 1;
        }

        $ejercicio2 = (($ejercicio != '') ? 'ctrl' . $ejercicio . '.' : '');

        $otroWhere = '';
        if ($tipoReporte == 3 && $_SESSION[_S]['nvj_id'] >= 5) {
            // Si limitaron por grupo o excluyeron alguna UR
            if ($grupos != '') {
                $otroWhere .= '  WHERE gpo_id IN ( ' . rtrim($grupos) . ' ) AND ';
                // Si excluyeron a alguna UR
                if ($excluir != '') {
                    $otroWhere .= ' ' . ($grupos != '' ? '' : ' WHERE ') . ' CONCAT( gpo_id, ur_id ) NOT IN ( ' . rtrim($excluir) . ' ) AND ';
                }
                // Si no usaron las campos especiales, es un where estandar
            }
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("
            SELECT cuenta, debe, haber, cgm_naturaleza FROM (
                SELECT if(isnull(MAX(cuenta)),'2131',MAX(cuenta)) cuenta,ger_id,cgp_id,rub_id,cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                    FROM cat_CgSubcuenta sc
                        LEFT JOIN(
                            SELECT '2131' cuenta,ger_id,cgp_id,rub_id,cgcu_id, SUM(pdx_debe) debe, SUM(pdx_haber) haber, MAX(nat_id)
                            FROM " . $ejercicio2 . 'cg_PolizaDetalleAuxiliar d
                            INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                            ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                            ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                                        AND ')) . '
                                        ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                                        AND ger_id=2 AND cgp_id=1 AND rub_id=3 # AND cgcu_id=1
                                        AND epl_id NOT IN ('C','H')
                                        " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                                        )d USING (ger_id , cgp_id , rub_id , cgcu_id)
                                        WHERE ger_id=2 AND cgp_id=1 AND rub_id=3 AND cgcu_id=1
                                        GROUP BY ger_id,cgp_id,rub_id,cgcu_id
                                        UNION ALL
                                            SELECT if(isnull(MAX(cuenta)),'223',MAX(cuenta)) cuenta, ger_id,cgp_id,rub_id,'' cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                                            FROM cat_CgSubcuenta sc
                                            LEFT JOIN(
                                                SELECT '223' cuenta,ger_id,cgp_id,rub_id,MAX(cgcu_id), SUM(pdx_debe) debe, SUM(pdx_haber) haber, MAX(nat_id)
                                                FROM " . $ejercicio2 . 'cg_PolizaDetalleAuxiliar d
                                                INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                                                ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                                                ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                                                AND')) . '
                                                ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                                                AND ger_id=2 and cgp_id=2 and rub_id=3 #and cgcu_id=1
                                                AND epl_id NOT IN ('C','H')
                                                " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . "
                                                )d USING (ger_id , cgp_id , rub_id)
                                                WHERE ger_id=2 AND cgp_id=2 AND rub_id=3
                                                GROUP BY ger_id,cgp_id,rub_id
                                        UNION ALL
                                            SELECT if(isnull(MAX(cuenta)),'2116',MAX(cuenta)) cuenta, ger_id,cgp_id,rub_id,cgcu_id, IFNULL( MAX(debe), 0 ) debe, IFNULL( MAX(haber), 0 ) haber, MAX(sc.nat_id) cgm_naturaleza
                                            FROM cat_CgSubcuenta sc
                                            LEFT JOIN(
                                                SELECT '2116' cuenta,2 as ger_id, 1 as cgp_id, 1 as rub_id, 6 as cgcu_id, SUM(pdx_debe) debe, SUM(pdx_haber) haber, MAX(nat_id)
                                                FROM " . $ejercicio2 . 'cg_PolizaDetalleAuxiliar d
                                                INNER JOIN cg_PolizaMaestro USING(gpo_id,ur_id,ue_id,msc_id,pot_id,prf_id,pma_id)
                                                ' . ($tipoReporte == 2 ? ($_SESSION[_S]['nvj_id'] < 6 ? '   INNER JOIN cat_CargaTrabajo t USING( gpo_id, ur_id )
                                                ' : '') . ' WHERE ' : (($tipoReporte == 3) ? $otroWhere : "WHERE gpo_id = $gpo_id AND ur_id = $ur_id " . ($ue != '' ? ' AND d.ue_id = ' . $ue . '' : '') . '
                                                AND')) . '
                                                ' . ($mesDesde < 0 ? "msc_id <= $mesHasta" : ($mesDesde == $mesHasta ? "msc_id = $mesDesde" : "( msc_id BETWEEN $mesDesde AND $mesHasta )")) . "
                                                # AND ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                                                AND(
                                                    (ger_id=2 and cgp_id=1 and rub_id in (1,2,5,6,7,9) ) # and cgcu_id=6
                                                        OR  (ger_id=2 and cgp_id=2 and rub_id in (1,2,4,5,6) )
                                                    ) # and cgcu_id=6
                                                AND epl_id NOT IN ('C','H')
                                                " . ($tipoReporte == 2 && $_SESSION[_S]['nvj_id'] < 6 ? 'AND t.usr_id = ' . $_SESSION[_S]['usr_id'] : '') . '
                                            )d USING (ger_id , cgp_id , rub_id , cgcu_id)
                                                WHERE ger_id=2 and cgp_id=1 and rub_id=1 and cgcu_id=6
                                                GROUP BY ger_id,cgp_id,rub_id,cgcu_id
            )cuenta;
        ');
        $conexion->consultar();
        $conexion->desconectar();

        $registro = [];

        while ($rowRegistro = $conexion->extraer()) {

            $key = $rowRegistro['cuenta'];

            $registro[$key]['cuenta'] = $rowRegistro['cuenta'];
            $registro[$key]['debe'] = $rowRegistro['debe'];
            $registro[$key]['haber'] = $rowRegistro['haber'];
            $registro[$key]['naturalezaCuenta'] = $rowRegistro['cgm_naturaleza'];
            $registro[$key]['resultado'] = (($registro[$key]['naturalezaCuenta'] == 'H') ? $registro[$key]['haber'] - $registro[$key]['debe'] : $registro[$key]['debe'] - $registro[$key]['haber']);
        }
        return $registro;
    }


    public function contenido()
    {

        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4'            => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7, 'alineacionenc' => 'C'];


        $nombreColumnas = ['Denominación de las Deudas','Moneda de Contratación', 'Institución o País Acreedor', 'Saldo Inicial del Periodo', 'Saldo  Final  del  Periodo'];
        $incluirColumnas = ['nombre', 'moneda', 'institucion', 'saldoInicial', 'saldoFinal'];
        $tipoColumnas = ['cI', 'cI', 'cI', 'i', 'i'];
        $anchoColumnas = [
            0 => 100,
            1 => 20,
            2 => 20,
            3 => 20,
            4 => 20,
        ];

        $table = [];
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $nombreColumnas[$key];
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $table['textoCompleto'] = true;
        $table['color'] = 1;
        $table['encabezadocolor'] = 'BFB7B6';
        $table['line1color'] = 'FFFFFF';
        $table['line2color'] = 'FFFFFF';

        $tablaimg['imagen'] = 1;
        $tablaimg[0]['nombre'] = 'Logo';

        $tablaimg[0]['ruta'] = dirname(__FILE__) . '/../../../../img/escudocolor_derecha.png';

        $tablaimg[0]['colImg'] = 'C1';
        $tablaimg[0]['alingXimg'] = '10';

        $tablaimg[0]['altura'] = '130';
        $tablaimg[0]['rotacion'] = '0';
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }

        $this->generaEdoFinanciero($this->cuentasDeuda, $encabezado, $table, ['nombre' => $this->nombreArchivoReporte . $this->mesDesde . '_' . $this->mesHasta, 'tablaimg' => $tablaimg, 'leyenda' => $this->leyenda, 'firmante1' => $this->firmante1, 'firmante2' => $this->firmante2, 'firmante3' => $this->firmante3, 'cargo1' => $this->cargo1, 'cargo2' => $this->cargo2, 'cargo3' => $this->cargo3]);
    }
}
$exp = new exp_AnaliticoDeudaOtrosPasivos();
