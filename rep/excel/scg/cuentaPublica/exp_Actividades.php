<?php
session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

set_time_limit(0);

//librerias
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/Math.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';

class exp_Actividades extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public  $encabLin4;
    public  $encabLin5;
    public  $encabLin6;
    public  $encabLin7;
    public $datos;
    public $nombreArchivoReporte;
    public $nombreColumnas;
    
    public $ur;
    public $ejercicio;
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
    public $nivel;
    public $desde;
    public $hasta;
    public $mesNombre;
    public $in;
    public $g;
    public $cuentasTotalesI;
    public $cuentasTotalesG;

    
    public function __construct()
    {
        parent::__construct();

        $ur = new UnidadResponsable();
        $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
        $ur->select();
        $ejercicio = "";
        $this->ejercicioActual = $_SESSION[_S]['ejercicioActual'];
        $this->ejercicioAnterior = $this->ejercicioActual - 1;

        // NOTA: Cambio en los encabezados del reporte solicitado en el Ticket #200644
        $this->encabLin1 = "Gobierno del Estado de Oaxaca";
        $this->encabLin6 = '';
        $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != "") ? $_POST['tipoReporte'] : '';
        $MilPesos = (isset($_POST['MilPes']) != "" ? 1000 : 1);
        $this->mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != "") ? $_POST['comboMes'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != "") ? $_POST['comboMesHasta'] : '';
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != "") ? $_POST['grupos'] : "";
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != "") ? $_POST['excluir'] : "";
        $consolidado = (isset($_POST['consolidado']) && $_POST['consolidado'] != "") ? $_POST['consolidado'] : "";
        $ceros = (isset($_POST['ceros']) && $_POST['ceros'] != "") ? $_POST['ceros'] : "";
        $incCta = isset($_POST['incCta']) ? $_POST['incCta'] : "";
        $ue = isset($_POST['ue']) ? $_POST['ue'] : "";
        $numUe = isset($_POST['numUe']) ? $_POST['numUe'] : "";
        $this->leyenda = isset($_POST['leyenda']) ? $_POST['leyenda'] : "";
        $this->firmante1 = isset($_POST['firmante1']) ? $_POST['firmante1'] : "";
        $this->firmante2 = isset($_POST['firmante2']) ? $_POST['firmante2'] : "";
        $this->firmante3 = isset($_POST['firmante3']) ? $_POST['firmante3'] : "";
        $this->cargo1 = isset($_POST['cargo1']) ? $_POST['cargo1'] : "";
        $this->cargo2 = isset($_POST['cargo2']) ? $_POST['cargo2'] : "";
        $this->cargo3 = isset($_POST['cargo3']) ? $_POST['cargo3'] : "";

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;

        if ($ejercicio == "") {
            $ur = new UnidadResponsable();
            $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ur->select();
        }

        $consolidado == "1" ? $this->encabLin3 = "Estado de Actividades Consolidado" : $this->encabLin3 = "Estado de Actividades";
        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($tipoReporte) {
                case 2: // Todas las URs de la carga de trabajo
                    $this->encabLin2 = "Dirección de Contabilidad Gubernamental";
                break;
                case 3: // Determinar qué incluir y qué excluir
                    $this->encabLin2 = "Dirección de Contabilidad Gubernamental";
                    $this->encabLin6 = isset($_POST['nombreEspecial']) ? $_POST['nombreEspecial'] : "ERROR DE PROGRAMA. NO EXISTE ENCABEZADO";
                break;
                default:
                    if ($ejercicio == "") {
                        $this->encabLin2 = $ur->getClave() . " - " . UrNombre::titleCase($ur->getNombre());
                    } else {
                        $this->encabLin2 = $ur['gpo_id'] . $ur['ur_id'] . " - " . UrNombre::titleCase($ur['ur_nombre']);
                    }
                break;
            }
        } else {

            if ($ejercicio == "") {
                $this->encabLin2 = "UR: " . $ur->getClave() . " - " . UrNombre::titleCase($ur->getNombre());
            } else {
                $this->encabLin2 = "UR: " . $ur['gpo_id'] . $ur['ur_id'] . " - " . UrNombre::titleCase($ur['ur_nombre']);
            }
        }

        $texto2 = '';
        if ($ue != '' && $numUe > 1) {
            $ejecutora = new AccesoDatos();
            $ejecutora->conectar();
            $ejecutora->setSql('SELECT ue_id,ue_nombre FROM cat_UnidadEjecutora WHERE gpo_id = ? AND ur_id = ? AND ue_id = ?;');
            $ejecutora->consultar(array($ur->getGpo_id(), $ur->getUr_id(), $ue));
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
        $con->consultar(array($mesDesde));
        $con->desconectar();
        $rowMes = $con->extraer();       

        $mesHasta = $this->mesHasta;

        $sqlMes2 = ' select msc_nombre, msc_dias from cat_MesContable where msc_id= ? ;';
        $con2 = new AccesoDatos();
        $con2->conectar();
        $con2->setSql($sqlMes2);
        $con2->consultar(array($mesHasta));
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

        if (isset($_POST['MilPes'])) {
            $this->encabLin5 = '(Cifras en Miles de Pesos)';
        } else {
            $this->encabLin5 = '(Cifras en Pesos)';
        }

        if (!($consolidado == 1)) {
            $desde = $mesDesde;
            $hasta = $mesHasta;
        } else {
            $desde = $mesHasta + 1;
            $hasta = -1;
        }


        list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, $this->ejercicioActual, $MilPesos, $grupos, $excluir, $ceros, $incCta);
        list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, $this->ejercicioActual, $MilPesos, $grupos, $excluir, $ceros, $incCta);

        list($ingresos2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 4, $tipoReporte, $this->ejercicioAnterior, $MilPesos, $grupos, $excluir, $ceros, $incCta);
        list($gastos2021) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $desde, $hasta, 5, $tipoReporte, $this->ejercicioAnterior, $MilPesos, $grupos, $excluir, $ceros, $incCta);

        $cuentaIn = '';
        $nivelIn = '';
        $importeIn = 0;
        $cuentaGa = '';
        $nivelGa = '';
        $importeGa = 0;
        $totalIn = 0;
        $totalGa = 0;

        $this->cuentasTotalesI = array(43998);
        $this->cuentasTotalesG = array(57998, 57999);
        $ing = array(); //array para informacion de ingresos  2020
        $ga = array(); // array para informacion de gastos 2020.

        $ing2 = array(); //array para informacion de ingresos 2019
        $ga2 = array(); // array para informacion de gastos 2019.

        if ($ingresos || $gastos) {
            foreach ($ingresos as $key => $valor) {
                if ($valor['nivel'] >= 1) {
                    $cuentaIn = $valor['cuenta'];
                    $nivelIn = utf8Decode($valor['nombre']);
                    if ($valor['naturalezaCuenta'] == "D") {
                        $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
                    } else {
                        $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
                    }
                } else {
                    $cuentaIn = $valor['cuenta'];
                    $nivelIn = utf8Decode($valor['nombre']);
                }
                if ($valor['nivel'] == 1) {
                    $totalIn = $totalIn + $importeIn;
                    $ing[$key]['cuenta'] = $cuentaIn;
                    $ing[$key]['nivel'] = $nivelIn;
                    $ing[$key]['importe'] = $importeIn;
                    $ing[$key]['nivelCuenta'] = $valor['nivel'];
                } else {
                    if ($this->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $this->cuentasTotalesI)) {
                        $ing[$key]['cuenta'] = $cuentaIn;
                        $ing[$key]['nivel'] = $nivelIn;
                        $ing[$key]['importe'] = $importeIn;
                        $ing[$key]['nivelCuenta'] = $valor['nivel'];
                    }
                }
            }
            $ing[43998]['cuenta'] = 43998;
            $ing[43998]['nivel'] = 'TOTAL DE INGRESOS Y OTROS BENEFICIOS';
            $ing[43998]['importe'] = $totalIn;
            $ing[43998]['nivelCuenta'] = 5;

            foreach ($gastos as $key => $valor) {
                if ($valor['nivel'] >= 1) {

                    $cuentaGa = $valor['cuenta'];
                    $nivelGa = utf8Decode($valor['nombre']);

                    if ($valor['naturalezaCuenta'] == "D") {
                        $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
                    } else {
                        $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
                    }
                } else {
                    $cuentaGa = $valor['cuenta'];
                    $nivelGa = utf8Decode($valor['nombre']);
                }
                if ($valor['nivel'] == 1) {
                    $totalGa = $totalGa + $importeGa;
                    $ga[$key]['cuenta'] = $cuentaGa;
                    $ga[$key]['nivel'] = $nivelGa;
                    $ga[$key]['importe'] = $importeGa;
                    $ga[$key]['nivelCuenta'] = $valor['nivel'];
                } else {
                    if ($this->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $this->cuentasTotalesG)) {
                        $ga[$key]['cuenta'] = $cuentaGa;
                        $ga[$key]['nivel'] = $nivelGa;
                        $ga[$key]['importe'] = $importeGa;
                        $ga[$key]['nivelCuenta'] = $valor['nivel'];
                    }
                }
            }

            $ga[57998]['cuenta'] = 57998;
            $ga[57998]['nivel'] = 'TOTAL DE GASTOS Y OTRAS PERDIDAS';
            $ga[57998]['importe'] = $totalGa;
            $ga[57998]['nivelCuenta'] = 5;

            $totalGaIn = $totalIn - $totalGa;
            if (stristr($totalGaIn, 'e')) {
                $totalGaIn = Math::redondear(Math::exp_to_dec($totalGaIn), 2);
            }
            $ga[57999]['cuenta'] = 57999;
            $ga[57999]['nivel'] = 'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)';
            $ga[57999]['importe'] = $totalGaIn;
            $ga[57999]['nivelCuenta'] = 5;
        }

        $cuentaIn = '';
        $nivelIn = '';
        $importeIn = 0;
        $cuentaGa = '';
        $nivelGa = '';
        $importeGa = 0;
        $totalIn = 0;
        $totalGa = 0;

        if ($ingresos2021 || $gastos2021) {
            foreach ($ingresos2021 as $key => $valor) {
                if ($valor['nivel'] >= 1) {
                    $cuentaIn = $valor['cuenta'];
                    $nivelIn = utf8Decode($valor['nombre']);
                    if ($valor['naturalezaCuenta'] == "D") {
                        $importeIn = ($valor['debe'] - $valor['haber']) / $MilPesos;
                    } else {
                        $importeIn = ($valor['haber'] - $valor['debe']) / $MilPesos;
                    }
                } else {
                    $cuentaIn = $valor['cuenta'];
                    $nivelIn = utf8Decode($valor['nombre']);
                }
                if ($valor['nivel'] == 1) {
                    $totalIn = $totalIn + $importeIn;
                    $ing2[$key]['cuenta'] = $cuentaIn;
                    $ing2[$key]['nivel'] = $nivelIn;
                    $ing2[$key]['importe'] = $importeIn;
                    $ing2[$key]['nivelCuenta'] = $valor['nivel'];
                } else {
                    if ($this->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $this->cuentasTotalesI)) {
                        $ing2[$key]['cuenta'] = $cuentaIn;
                        $ing2[$key]['nivel'] = $nivelIn;
                        $ing2[$key]['importe'] = $importeIn;
                        $ing2[$key]['nivelCuenta'] = $valor['nivel'];
                    }
                }
            }
            $ing2[43998]['cuenta'] = 43998;
            $ing2[43998]['nivel'] = 'TOTAL DE INGRESOS Y OTROS BENEFICIOS';
            $ing2[43998]['importe'] = $totalIn;
            $ing2[43998]['nivelCuenta'] = 5;

            foreach ($gastos2021 as $key => $valor) {
                if ($valor['nivel'] >= 1) {
                    $cuentaGa = $valor['cuenta'];
                    $nivelGa = utf8Decode($valor['nombre']);

                    if ($valor['naturalezaCuenta'] == "D") {
                        $importeGa = ($valor['debe'] - $valor['haber']) / $MilPesos;
                    } else {
                        $importeGa = ($valor['haber'] - $valor['debe']) / $MilPesos;
                    }
                } else {
                    $cuentaGa = $valor['cuenta'];
                    $nivelGa = utf8Decode($valor['nombre']);
                }
                if ($valor['nivel'] == 1) {
                    $totalGa = $totalGa + $importeGa;
                    $ga2[$key]['cuenta'] = $cuentaGa;
                    $ga2[$key]['nivel'] = $nivelGa;
                    $ga2[$key]['importe'] = $importeGa;
                    $ga2[$key]['nivelCuenta'] = $valor['nivel'];
                } else {
                    if ($this->nivel[$valor['nivel']] == 1 || in_array($valor['cuenta'], $this->cuentasTotalesG)) {
                        $ga2[$key]['cuenta'] = $cuentaGa;
                        $ga2[$key]['nivel'] = $nivelGa;
                        $ga2[$key]['importe'] = $importeGa;
                        $ga2[$key]['nivelCuenta'] = $valor['nivel'];
                    }
                }
            }

            $ga2[57998]['cuenta'] = 57998;
            $ga2[57998]['nivel'] = 'TOTAL DE GASTOS Y OTRAS PERDIDAS';
            $ga2[57998]['importe'] = $totalGa;
            $ga2[57998]['nivelCuenta'] = 5;

            $totalGaIn = $totalIn - $totalGa;
            if (stristr($totalGaIn, 'e')) {
                $totalGaIn = Math::redondear(Math::exp_to_dec($totalGaIn), 2);
            }
            $ga2[57999]['cuenta'] = 57999;
            $ga2[57999]['nivel'] = 'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)';
            $ga2[57999]['importe'] = $totalGaIn;
            $ga2[57999]['nivelCuenta'] = 5;
        }

        $keyIngresos2020 = key($ing);
        $keyIngresos2019 = key($ing2);
        $finalIng = array();

        while ($keyIngresos2020 || $keyIngresos2019) {

            if (CompararCadenas((string) $keyIngresos2020, (string) $keyIngresos2019) == 0) {

                $finalIng[$keyIngresos2020]['cuenta'] = $ing[$keyIngresos2020]['cuenta'];
                $finalIng[$keyIngresos2020]['nivel'] = $ing[$keyIngresos2020]['nivel'];
                $finalIng[$keyIngresos2020]['nivelCuenta'] = $ing[$keyIngresos2020]['nivelCuenta'];
                $finalIng[$keyIngresos2020]['importe2020'] = $ing[$keyIngresos2020]['importe'];
                $finalIng[$keyIngresos2020]['importe2019'] = $ing2[$keyIngresos2019]['importe'];

                next($ing);
                $keyIngresos2020 = key($ing);

                next($ing2);
                $keyIngresos2019 = key($ing2);
            } else if (!$keyIngresos2019 || CompararCadenas((string) $keyIngresos2020, (string) $keyIngresos2019) < 0) { //cuentas anteriores

                $finalIng[$keyIngresos2020]['cuenta'] = $ing[$keyIngresos2020]['cuenta'];
                $finalIng[$keyIngresos2020]['nivel'] = $ing[$keyIngresos2020]['nivel'];
                $finalIng[$keyIngresos2020]['nivelCuenta'] = $ing[$keyIngresos2020]['nivelCuenta'];
                $finalIng[$keyIngresos2020]['importe2020'] = $ing[$keyIngresos2020]['importe'];
                $finalIng[$keyIngresos2020]['importe2019'] = 0;

                next($ing);
                $keyIngresos2020 = key($ing);
            } else {

                $finalIng[$keyIngresos2019]['cuenta'] = $ing2[$keyIngresos2019]['cuenta'];
                $finalIng[$keyIngresos2019]['nivel'] = $ing2[$keyIngresos2019]['nivel'];
                $finalIng[$keyIngresos2019]['nivelCuenta'] = $ing2[$keyIngresos2019]['nivelCuenta'];
                $finalIng[$keyIngresos2019]['importe2020'] = (isset($finalIng[$keyIngresos2019]['importe2020']) ? $finalIng[$keyIngresos2019]['importe2020'] : 0);
                $finalIng[$keyIngresos2019]['importe2019'] = $ing2[$keyIngresos2019]['importe'];

                next($ing2);
                $keyIngresos2019 = key($ing2);
            }
        }

        $keyGastos2020 = key($ga);
        $keyGastos2019 = key($ga2);
        $finalGA = array();

        while ($keyGastos2020 || $keyGastos2019) {

            if (CompararCadenas((string) $keyGastos2020, (string) $keyGastos2019) == 0) {

                $finalGA[$keyGastos2020]['cuenta'] = $ga[$keyGastos2020]['cuenta'];
                $finalGA[$keyGastos2020]['nivel'] = $ga[$keyGastos2020]['nivel'];
                $finalGA[$keyGastos2020]['nivelCuenta'] = $ga[$keyGastos2020]['nivelCuenta'];
                $finalGA[$keyGastos2020]['importe2020'] = $ga[$keyGastos2020]['importe'];
                $finalGA[$keyGastos2020]['importe2019'] = $ga2[$keyGastos2019]['importe'];

                next($ga);
                $keyGastos2020 = key($ga);

                next($ga2);
                $keyGastos2019 = key($ga2);
            } else if (!$keyGastos2019 || CompararCadenas((string) $keyGastos2020, (string) $keyGastos2019) < 0) { //cuentas anteriores

                $finalGA[$keyGastos2020]['cuenta'] = $ga[$keyGastos2020]['cuenta'];
                $finalGA[$keyGastos2020]['nivel'] = $ga[$keyGastos2020]['nivel'];
                $finalGA[$keyGastos2020]['nivelCuenta'] = $ga[$keyGastos2020]['nivelCuenta'];
                $finalGA[$keyGastos2020]['importe2020'] = $ga[$keyGastos2020]['importe'];
                if (!isset($finalGA[$keyGastos2020]['importe2019'])) {
                    $finalGA[$keyGastos2020]['importe2019'] = 0;
                }
                next($ga);
                $keyGastos2020 = key($ga);
            } else {

                $finalGA[$keyGastos2019]['cuenta'] = $ga2[$keyGastos2019]['cuenta'];
                $finalGA[$keyGastos2019]['nivel'] = $ga2[$keyGastos2019]['nivel'];
                $finalGA[$keyGastos2019]['nivelCuenta'] = $ga2[$keyGastos2019]['nivelCuenta'];
                $finalGA[$keyGastos2019]['importe2020'] = (isset($finalGA[$keyGastos2019]['importe2020']) ? $finalGA[$keyGastos2019]['importe2020'] : 0);
                $finalGA[$keyGastos2019]['importe2019'] = $ga2[$keyGastos2019]['importe'];

                next($ga2);
                $keyGastos2019 = key($ga2);
            }
        }

        /*****QUITAR O NO LOS CEROS SEGUN EL CHECK******/
        if ($ceros == "") {
            if ($_SESSION[_S]['reportes'] == 'C92') {
                foreach ($finalIng as $indice => $val) {
                    if ($finalIng[$indice]['importe2020'] != 0) {

                        $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
                        $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
                        $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
                        $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
                        $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe2019'];
                        $finalIng[$indice]['ceros'] = 0;
                    }
                }

                foreach ($finalGA as $indice => $val) {
                    // NOTA: Cambios derivados del ticket #421264
                    $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
                    $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
                    $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
                    $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
                    $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe2019'];
                    $finalGA[$indice]['ceros'] = 0;
                }
            } else {
                foreach ($finalIng as $indice => $val) {
                    if ($finalIng[$indice]['importe2020'] != 0 || $finalIng[$indice]['importe2019'] != 0) {

                        $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
                        $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
                        $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
                        $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
                        $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe2019'];
                        $finalIng[$indice]['ceros'] = 0;
                    }
                }

                foreach ($finalGA as $indice => $val) {
                    if ($finalGA[$indice]['importe2020'] != 0 || $finalGA[$indice]['importe2019'] != 0) {

                        $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
                        $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
                        $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
                        $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
                        $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe2019'];
                        $finalGA[$indice]['ceros'] = 0;
                    }
                }
            }
        } else {

            foreach ($finalIng as $indice => $val) {

                $finalIng[$indice]['cuenta'] = $finalIng[$indice]['cuenta'];
                $finalIng[$indice]['nivel'] = $finalIng[$indice]['nivel'];
                $finalIng[$indice]['nivelCuenta'] = $finalIng[$indice]['nivelCuenta'];
                $finalIng[$indice]['importe2020'] = $finalIng[$indice]['importe2020'];
                $finalIng[$indice]['importe2019'] = $finalIng[$indice]['importe2019'];
                $finalIng[$indice]['ceros'] = 1;
            }

            foreach ($finalGA as $indice => $val) {

                $finalGA[$indice]['cuenta'] = $finalGA[$indice]['cuenta'];
                $finalGA[$indice]['nivel'] = $finalGA[$indice]['nivel'];
                $finalGA[$indice]['nivelCuenta'] = $finalGA[$indice]['nivelCuenta'];
                $finalGA[$indice]['importe2020'] = $finalGA[$indice]['importe2020'];
                $finalGA[$indice]['importe2019'] = $finalGA[$indice]['importe2019'];
                $finalGA[$indice]['ceros'] = 1;
            }
        }

        /*****ORDEN***************************/
        $sortIng = array();
        $sortIng['indice'] = [];
        foreach ($finalIng as $indice => $val) {
            $sortIng['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
        }
        array_multisort($sortIng['indice'], SORT_ASC, $finalIng);

        $sortGa = array();
        $sortGa['indice'] = [];
        foreach ($finalGA as $indice => $val) {
            $sortGa['indice'][$indice] = str_pad($indice, 5, '0', STR_PAD_RIGHT);
        }
        array_multisort($sortGa['indice'], SORT_ASC, $finalGA);

        $this->in = array_merge($finalIng); //ingresos 2020
        $this->g = array_merge($finalGA); //gastos 2020

        $this->nombreArchivoReporte = 'EdoActividades_';
        $this->contenido();
    }


    public function contenido()
    {
        $estado = array();
        $nuevo = array_merge($this->in,$this->g);
        
        foreach ($nuevo as $value) {
            if($value['nivelCuenta'] == 1){
                $value['importe2020'] = "";
                $value['importe2019'] = "";
            }else{
                $value['nivel'] =  UrNombre::titleCase(utf8Encode($value['nivel']));
            }
            
            array_push( $estado, array( "nombre" => $value['nivelCuenta']."_".$value['nivel'], "importe2020" => trim($value['importe2020']), "importe2019" => trim($value['importe2019'])));
            
        }

        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'C');

        
        $nombreColumnas = array('Concepto', UrNombre::titleCase($this->mesNombre).' '.$this->ejercicioActual, 'Diciembre '.$this->ejercicioAnterior);
        $incluirColumnas = array('nombre', 'importe2020', 'importe2019');
        $tipoColumnas = array('cI', 'i', 'i');
        $anchoColumnas = [
            0 => 130,
            1 => 30,
            2 => 30
        ];

        $table = array();
        foreach ($incluirColumnas as $key => $val) {
            $table['nombre'][$val] = $nombreColumnas[$key];            
            $table['ancho'][$val] = $anchoColumnas[$key];
            $table['campo'][$val] = $key;
            $table['tipo'][$val] = $tipoColumnas[$key];
        }

        $table['textoCompleto'] = true;
        $table['color'] = 1;
        $table['encabezadocolor'] = "BFB7B6";
        $table['line1color'] = "FFFFFF";
        $table['line2color'] = "FFFFFF";

        $tablaimg['imagen'] = 1;
        $tablaimg[0]['nombre'] = "Logo";

        $tablaimg[0]['ruta'] = dirname(__FILE__) . '/../../../../img/escudocolor_derecha.png';

        $tablaimg[0]['colImg'] = "B1";
        $tablaimg[0]['alingXimg'] = "10";

        $tablaimg[0]['altura'] = "130";
        $tablaimg[0]['rotacion'] = "0";
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }

        $this->generaEdoFinanciero($estado, $encabezado, $table, array('nombre' => $this->nombreArchivoReporte.$this->mesDesde.'_'.$this->mesHasta, 'tablaimg' => $tablaimg, 'leyenda' => $this->leyenda, 'firmante1' => $this->firmante1, 'firmante2' => $this->firmante2, 'firmante3' => $this->firmante3, 'cargo1' => $this->cargo1, 'cargo2' => $this->cargo2, 'cargo3' => $this->cargo3));
    }
}
$exp = new exp_Actividades();
