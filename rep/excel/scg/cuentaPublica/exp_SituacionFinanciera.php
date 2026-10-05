<?php
session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

set_time_limit(0);

define("MENU_CUENTA_PUBLICA", "C111");
define("MENU_ESTADOS_FINANCIEROS", "C91");

//librerias
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once '../../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/Math.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';

class exp_SituacionFinanciera extends generaPhpspreadsheet
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
    public $chkEnMiles;
    public $cerosOrden;
    public $checkCuentaOrden;
    public $activo;
    public $pasivo;
    public $hacienda;
    public $ingresos;
    public $gastos;
    public $ctasOrden7;
    public $ctasOrden8;
    
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
        $this->chkEnMiles = isset($_POST['ckmiles']) ? 1000 : 1;
        $this->mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != "") ? $_POST['comboMesDesde'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != "") ? $_POST['comboMesHasta'] : '';
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != "") ? $_POST['grupos'] : "";
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != "") ? $_POST['excluir'] : "";
        $consolidado = (isset($_POST['consolidado']) && $_POST['consolidado'] != "") ? $_POST['consolidado'] : "";
        $ceros = (isset($_POST['ceros']) && $_POST['ceros'] != "") ? $_POST['ceros'] : "";
        $this->cerosOrden = (isset($_POST['cerosOrden']) && $_POST['cerosOrden'] != "") ? $_POST['cerosOrden']: 0;
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
        
        $this->checkCuentaOrden = isset($_POST['checkCuentaOrden']) ? $_POST['checkCuentaOrden']: -1;

        if ($ejercicio == "") {
            $ur = new UnidadResponsable();
            $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ur->select();
        }

        $this->encabLin3 = "Estado de Situación Financiera";
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
        
        if ($mesHasta == 13) {
            $this->encabLin4 = utf8Decode('Al mes de '. UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        } elseif ($this->mesHasta == 0) {
            $this->encabLin4 = utf8Decode('Al mes de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        } else {
            $this->encabLin4 = utf8Decode('Al ' . $rowMes2['msc_dias'] . ' de ' . UrNombre::titleCase($rowMes2['msc_nombre']) . ' de ' . substr($_SESSION[_S]['ejercicio'], 0, 4));
        }

        if ($this->chkEnMiles == 1000) {
            $this->encabLin5 = '(Cifras en Miles de Pesos)';
        } else {
            $this->encabLin5 = '(Cifras en Pesos)';
        }

        // Pedimos los datos del ejercicio actual
        // --------------------------------------------------------
        list($this->activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte, $this->ejercicioActual,$this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
        list($this->pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte, $this->ejercicioActual,$this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
        list($this->hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte, $this->ejercicioActual,$this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);

        list($this->ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte, $this->ejercicioActual,$this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
        list($this->gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte, $this->ejercicioActual,$this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);

        // Si pidieron incluir cuentas de orden...
        if ($this->checkCuentaOrden == 1) {
            list($this->ctasOrden8) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 8, $tipoReporte,$this->ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->cerosOrden, $incCta);
            list($this->ctasOrden7) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 7, $tipoReporte,$this->ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->cerosOrden, $incCta);
        }

        //obtenemos resultado del ejercicio (ahorro/desahorro)
        $natIng_Actual = $this->ingresos[4]['naturalezaCuenta'];
        $natGto_Actual = $this->gastos[5]['naturalezaCuenta'];

        $debeIng_Actual  = $this->ingresos[4]['debe'];
        $haberIng_Actual = $this->ingresos[4]['haber'];
        $DebeGto_Actual  = $this->gastos[5]['debe'];
        $haberGto_Actual = $this->gastos[5]['haber'];

        $saldoIng_Actual = (($natIng_Actual == 'D') ? ($debeIng_Actual - $haberIng_Actual) : ($haberIng_Actual - $debeIng_Actual));
        $saldoGto_Actual = (($natGto_Actual == 'D') ? ($DebeGto_Actual - $haberGto_Actual) : ($haberGto_Actual - $DebeGto_Actual));

        $result_Actual = $saldoIng_Actual - $saldoGto_Actual;

        $this->ajusteEjercicioAnterior($this->hacienda);
        $this->PonResultadoDelEjercicio($this->hacienda, $result_Actual, $this->nivel);
        $this->ajustePatrimonioGenerado($this->hacienda);

        $activo_Actual = $this->dameArrayOrdenadoConTotales($this->activo, $this->nivel);
        $pasivo_Actual = $this->dameArrayOrdenadoConTotales($this->pasivo, $this->nivel);
        $hacienda_Actual = $this->dameArrayOrdenadoConTotales($this->hacienda, $this->nivel);

        $arrayPH_Actual = $pasivo_Actual + $hacienda_Actual;

        // Para la Cuenta Pública, obtenemos también los saldos del ejercicio anterior
        // -----------------------------------------------------------------------------
        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {

            list($this->activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte,$this->ejercicioAnterior, $this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
            list($this->pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte,$this->ejercicioAnterior, $this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
            list($this->hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte,$this->ejercicioAnterior, $this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);

            list($this->ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte,$this->ejercicioAnterior, $this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);
            list($this->gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'],$_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte,$this->ejercicioAnterior, $this->chkEnMiles, $grupos, $excluir, $ceros, $incCta);

            // NOTA: Por alguna razón, para el ejercicio anterior NO checamos si pidieron cuentas de orden
            // se asume que así lo definió Contabilidad

            $natIng_Anterior = $this->ingresos[4]['naturalezaCuenta'];
            $natGto_Anterior = $this->gastos[5]['naturalezaCuenta'];

            $debeIng_Anterior = $this->ingresos[4]['debe'];
            $haberIng_Anterior = $this->ingresos[4]['haber'];
            $debeGto_Anterior = $this->gastos[5]['debe'];
            $haberGto_Anterior = $this->gastos[5]['haber'];

            if ($natIng_Anterior == 'D') {
                $result_Anterior = $debeIng_Anterior - $haberIng_Anterior;
            } else {
                $result_Anterior = $haberIng_Anterior - $debeIng_Anterior;
            }

            $saldoIng_Anterior = (($natIng_Anterior == 'D') ? ($debeIng_Anterior - $haberIng_Anterior) : ($haberIng_Anterior - $debeIng_Anterior));
            $saldoGto_Anterior = (($natGto_Anterior == 'D') ? ($debeGto_Anterior - $haberGto_Anterior) : ($haberGto_Anterior - $debeGto_Anterior));

            $result_Anterior = $saldoIng_Anterior - $saldoGto_Anterior;

            $this->ajusteEjercicioAnterior($this->hacienda);
            $this->PonResultadoDelEjercicio($this->hacienda, $result_Anterior, $this->nivel);
            $this->ajustePatrimonioGenerado($this->hacienda);
            $this->ajusteCuentasAhorro( $this->hacienda, $result_Actual, $result_Anterior );

            $activo_Anterior = $this->dameArrayOrdenadoConTotales($this->activo, $this->nivel);
            $pasivo_Anterior = $this->dameArrayOrdenadoConTotales($this->pasivo, $this->nivel);
            $hacienda_Anterior = $this->dameArrayOrdenadoConTotales($this->hacienda, $this->nivel);

            $arrayPH_Anterior = $pasivo_Anterior + $hacienda_Anterior;
        }

        #SE UNEN LOS ARREGLOS
        $arrayActivo = $this->compararCuentas($activo_Anterior, $activo_Actual);
        $arrayPasivoHacienda = $this->compararCuentas($arrayPH_Anterior, $arrayPH_Actual);

        $totalPH_Actual = 0;
        $totalPH_Anterior = 0;

        //total pasivo y hacienda
        // $totalPH_Actual = $arrayPasivoHacienda[29999]['importe_Actual'] + $arrayPasivoHacienda[39999]['importe_Actual'] + $result_Actual;
        // CAMBIO 2024: No se suma result_Actual porque ya nos aseguramos que se incluya el importe en Hacienda
        $totalPH_Actual = $arrayPasivoHacienda[29999]['importe_Actual'] + $arrayPasivoHacienda[39999]['importe_Actual'];

        //Resultados del ejercicio(se obtiene de la diferencia de los ingresos y gastos
        if ($_SESSION[_S]['reportes'] == MENU_CUENTA_PUBLICA) {

            // $totalPH_Actual = round($arrayPasivoHacienda[29999]['importe_Actual'] + $arrayPasivoHacienda[39999]['importe_Actual']+$result_Actual);
            // CAMBIO 2024: No se suma result_Actual porque ya nos aseguramos que se incluya el importe en Hacienda
            $totalPH_Actual = round($arrayPasivoHacienda[29999]['importe_Actual'] + $arrayPasivoHacienda[39999]['importe_Actual']);
            // $totalPH_Anterior = round($arrayPasivoHacienda[29999]['importe_Anterior'] + $arrayPasivoHacienda[39999]['importe_Anterior']+$result_Anterior);
            $totalPH_Anterior = round($arrayPasivoHacienda[29999]['importe_Anterior'] + $arrayPasivoHacienda[39999]['importe_Anterior']);

            //Resultados del ejercicio(se obtiene de la diferencia de los ingresos y gastos
            $arrayPasivoHacienda[50000] = array("cuenta" => '50000', "nombre" => "TOTAL DEL PASIVO Y HACIENDA PÚBLICA/PATRIMONIO","nivel" => 1, "importe_Actual" => $totalPH_Actual, "importe_Anterior" => $totalPH_Anterior);
        } else {

            $arrayPasivoHacienda[32100] = array("cuenta" => '49000', "nombre" => "RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)","nivel" => 1, "importe_Actual" => $result_Actual, "importe_Anterior" => 0);
            /* AGREGADOS EL 29-03-2011 ROLLBACK = REMOVE THE NEXT 3 INSTRUCTIONS */
                // $totalHaciendaPublicaPatrimonioGenerado = $result_Actual + $arrayPasivoHacienda[32200]['importe_Actual'];
                // $arrayPasivoHacienda[32999] = array("cuenta" => '32999', "nombre" => "TOTAL DE HACIENDA PÚBLICA/PATRIMONIO GENERADO","nivel" => 1, "importe_Actual" => $totalHaciendaPublicaPatrimonioGenerado, "importe_Anterior" => 0);

                // $totalHacienaPublicaPatrimonio = $arrayPasivoHacienda[31999]['importe_Actual'] + $arrayPasivoHacienda[32999]['importe_Actual'];
                // $arrayPasivoHacienda[39999] = array("cuenta" => '39999', "nombre" => "TOTAL DE HACIENDA PÚBLICA/PATRIMONIO","nivel" => 1, "importe_Actual" => $totalHacienaPublicaPatrimonio, "importe_Anterior" => 0);

                // $totalPasivoPatrimonioHaciendaPublica = $arrayPasivoHacienda[29999]['importe_Actual'] + $totalHacienaPublicaPatrimonio;
                // $arrayPasivoHacienda[50000] = array("cuenta" => '50000', "nombre" => "TOTAL DE PASIVO Y PATRIMONIO/HACIENDA PÚBLICA","nivel" => 1, "importe_Actual" => $totalPasivoPatrimonioHaciendaPublica, "importe_Anterior" => 0);
            /* AGREGADOS EL 29-03-2011 */
        }

        $arrayIzquierda = $arrayActivo;
        $arrayDerecha = $arrayPasivoHacienda;

        $ind = 0;

        foreach (array_keys($arrayIzquierda) as $key) {
            $datosIzquierda['C.' . $ind]['cuentaIzquierda'] = $arrayIzquierda[$key]['cuenta'];
            $datosIzquierda['C.' . $ind]['nombreIzquierda'] = $arrayIzquierda[$key]['nombre'];
            $datosIzquierda['C.' . $ind]['importeActualIzquierda'] = $arrayIzquierda[$key]['importe_Actual'];
            $datosIzquierda['C.' . $ind]['importeAnteriorIzquierda'] = $arrayIzquierda[$key]['importe_Anterior'];
            $datosIzquierda['C.' . $ind]['nivelIzquierda'] = $arrayIzquierda[$key]['nivel'];
            $ind++;
        }

        $ind = 0;

        foreach (array_keys($arrayDerecha) as $key) {
            switch($arrayDerecha[$key]['cuenta']){
                case "31999":
                case "32999":
                case "33999":
                break;
                default:
                    $datosDerecha['C.' . $ind]['cuentaDerecha'] = $arrayDerecha[$key]['cuenta'];
                    $datosDerecha['C.' . $ind]['nombreDerecha'] = $arrayDerecha[$key]['nombre'];
                    $datosDerecha['C.' . $ind]['importeActualDerecha'] = $arrayDerecha[$key]['importe_Actual'];
                    $datosDerecha['C.' . $ind]['importeAnteriorDerecha'] = $arrayDerecha[$key]['importe_Anterior'];
                    $datosDerecha['C.' . $ind]['nivelDerecha'] = $arrayDerecha[$key]['nivel'];
                break;
            }
            $ind++;
        }

        $this->datos = array_merge(array_fill_keys(array_keys($datosIzquierda), ''), array_fill_keys(array_keys($datosDerecha), ''));

        $totalImporteActualIzquierda = 0;
        $totalImporteAnteriorIzquierda = 0;
        $totalImporteActualDerecha = 0;
        $totalImporteAnteriorDerecha = 0;

        foreach (array_keys($this->datos) as $ind) {
            $this->datos[$ind] = array();

            if (isset($datosIzquierda[$ind])) {
                $this->datos[$ind] = $datosIzquierda[$ind];
                if ($this->datos[$ind]['nivelIzquierda'] == 1) {
                    $totalImporteActualIzquierda = $totalImporteActualIzquierda + $datosIzquierda[$ind]['importeActualIzquierda'];
                    $totalImporteAnteriorIzquierda = $totalImporteAnteriorIzquierda + $datosIzquierda[$ind]['importeAnteriorIzquierda'];
                }
            } else {
                $this->datos[$ind]['cuentaIzquierda'] = '';
                $this->datos[$ind]['nombreIzquierda'] = '';
                $this->datos[$ind]['importeActualIzquierda'] = '';
                $this->datos[$ind]['importeAnteriorIzquierda'] = '';
                $this->datos[$ind]['nivelIzquierda'] = '';
            }

            if (isset($datosDerecha[$ind])) {
                $this->datos[$ind] = $this->datos[$ind] + $datosDerecha[$ind];
                if ($this->datos[$ind]['nivelDerecha'] == 1) {
                    $totalImporteActualDerecha = $totalImporteActualDerecha + $datosDerecha[$ind]['importeActualDerecha'];
                    $totalImporteAnteriorDerecha = $totalImporteAnteriorDerecha + $datosDerecha[$ind]['importeAnteriorDerecha'];
                }
            } else {
                $this->datos[$ind]['cuentaDerecha'] = '';
                $this->datos[$ind]['nombreDerecha'] = '';
                $this->datos[$ind]['importeActualDerecha'] = '';
                $this->datos[$ind]['importeAnteriorDerecha'] = '';
                $this->datos[$ind]['nivelDerecha'] = '';
            }
        }

        $this->nombreArchivoReporte = 'EdoSituacionFinanciera_';
        $this->contenido();
    }


    // ============================================================================================
    // FUNCIONES DE APOYO
    // ============================================================================================


    // Igualamos la longitud del array del ejercicio anterior con el array del ejercicio actual
    function compararCuentas($array_Anterior, $array_Actual)
    {
        $arrayGlobal = array();

        //recuperamos la posicion inicial en el arreglo.
        reset($array_Anterior);
        reset($array_Actual);

        switch ($_SESSION[_S]['reportes']) {

            case MENU_ESTADOS_FINANCIEROS:

                $key_Actual = key($array_Actual);
                while ($key_Actual) {

                    //todo
                    //usar un switch aca para las operaciones que mando adriana
                    if ($key_Actual == '32100' || $key_Actual == '32200') {
                        if (!$arrayGlobal['32200']) {
                            $arrayGlobal['32200'] = [];
                        }
                        $arrayGlobal['32200']['cuenta'] = $array_Actual[$key_Actual]['cuenta'];
                        $arrayGlobal['32200']['nombre'] = $array_Actual[$key_Actual]['nombre'];
                        $arrayGlobal['32200']['nivel'] = $array_Actual[$key_Actual]['nivel'];
                        $arrayGlobal['32200']['importe_Actual'] += $array_Actual[$key_Actual]['importe'];
                        $arrayGlobal['32200']['importe_Anterior'] = 0.00;
                    } else {
                        $arrayGlobal[$key_Actual]['cuenta'] = $array_Actual[$key_Actual]['cuenta'];
                        $arrayGlobal[$key_Actual]['nombre'] = $array_Actual[$key_Actual]['nombre'];
                        $arrayGlobal[$key_Actual]['nivel'] = $array_Actual[$key_Actual]['nivel'];
                        $arrayGlobal[$key_Actual]['importe_Actual'] = $array_Actual[$key_Actual]['importe'];
                        $arrayGlobal[$key_Actual]['importe_Anterior'] = 0.00;
                    }

                    next($array_Actual);
                    $key_Actual = key($array_Actual);
                }
            break;

            case MENU_CUENTA_PUBLICA:

                $key_Actual = key($array_Actual);
                $key_Anterior = key($array_Anterior);

                while ($key_Actual || $key_Anterior) {
                    //si las cadenas son iguales
                    if (CompararCadenas((string) $key_Anterior, (string) $key_Actual) == 0) {
                        $arrayGlobal[$key_Actual]['cuenta'] = $array_Actual[$key_Actual]['cuenta'];
                        $arrayGlobal[$key_Actual]['nombre'] = $array_Actual[$key_Actual]['nombre'];
                        $arrayGlobal[$key_Actual]['nivel'] = $array_Actual[$key_Actual]['nivel'];
                        $arrayGlobal[$key_Actual]['importe_Actual'] = $array_Actual[$key_Actual]['importe'];
                        $arrayGlobal[$key_Actual]['importe_Anterior'] = $array_Anterior[$key_Actual]['importe'];

                        next($array_Actual);
                        $key_Actual = key($array_Actual);

                        next($array_Anterior);
                        $key_Anterior = key($array_Anterior);
                        //si no existe en el ejercicio ANTERIOR  pero si en el ACTUAL
                    } else if (!$key_Anterior || CompararCadenas((string) $key_Actual,(string) $key_Anterior) < 0) { //cuentas anteriores
                        $arrayGlobal[$key_Actual]['cuenta'] = $array_Actual[$key_Actual]['cuenta'];
                        $arrayGlobal[$key_Actual]['nombre'] = $array_Actual[$key_Actual]['nombre'];
                        $arrayGlobal[$key_Actual]['nivel'] = $array_Actual[$key_Actual]['nivel'];
                        $arrayGlobal[$key_Actual]['importe_Actual'] = $array_Actual[$key_Actual]['importe'];
                        $arrayGlobal[$key_Actual]['importe_Anterior'] = (isset($arrayGlobal[$key_Actual]['importe_Anterior']) ? $arrayGlobal[$key_Actual]['importe_Anterior'] : 0.00);

                        next($array_Actual);
                        $key_Actual = key($array_Actual);
                    } else {
                        //si no existe en el ejercicio ACTUAL pero si en el ANTERIOR
                        $arrayGlobal[$key_Anterior]['cuenta'] = $array_Anterior[$key_Anterior]['cuenta'];
                        $arrayGlobal[$key_Anterior]['nombre'] = $array_Anterior[$key_Anterior]['nombre'];
                        $arrayGlobal[$key_Anterior]['nivel'] = $array_Anterior[$key_Anterior]['nivel'];
                        $arrayGlobal[$key_Anterior]['importe_Actual'] = (isset($arrayGlobal[$key_Anterior]['importe_Actual']) ? $arrayGlobal[$key_Anterior]['importe_Actual'] : 0.00);
                        $arrayGlobal[$key_Anterior]['importe_Anterior'] = $array_Anterior[$key_Anterior]['importe'];

                        next($array_Anterior);
                        $key_Anterior = key($array_Anterior);
                    }
                }
                break;
        }
        return $arrayGlobal;
    }


    // Como se van agregando cuentas al array, esta función nos asegura que estará bien ordenado
    // Adicionalmente, aquí se están agregando los totales de agrupamiento
    function dameArrayOrdenadoConTotales($array, $p)
    {
        $arreglo = array();
        $total = 0;
        if (is_array($array)) {
            //naturaleza de la cuenta (activo)
            $naturalezaActivo = '';

            foreach ($array as $key => $val) {

                // Si pidieron incluir éste nivel... 
                if ($p[$array[$key]['nivel']] == 1) {
                    //Agregamos a la llave del arreglo 0's a la derecha
                    $miKey = str_pad($key, 5, '0', STR_PAD_RIGHT);

                    $arreglo[$miKey] = $array[$key];

                    //obtenemos los totales de los grupos
                    if ($arreglo[$miKey]['nivel'] == 2) {
                        $arreglo[$miKey + 999] = $array[$key];
                        $arreglo[$miKey + 999]['cuenta'] = ($miKey + 999);
                        $arreglo[$miKey + 999]['nivel'] = 2;
                        $arreglo[$miKey + 999]['nombre'] = "TOTAL DE " . $this->dame_plural_del_nombre( $array[$key]['cgp_nombre'] );
                    }
                    //obtenemos el total del genero.
                    if ($arreglo[$miKey]['nivel'] == 1) {
                        $arreglo[$miKey + 9999] = $array[$key];
                        $arreglo[$miKey + 9999]['cuenta'] = ($miKey + 9999);
                        $arreglo[$miKey + 9999]['nivel'] = 2;
                        $arreglo[$miKey + 9999]['nombre'] = "TOTAL DE " . $this->dame_plural_del_nombre( $array[$key]['ger_nombre'] );
                        if ( $arreglo[$miKey + 9999]['nombre'] == "TOTAL DE ACTIVO" ) {
                            $arreglo[$miKey + 9999]['nombre'] = "TOTAL DEL ACTIVO";
                        }
                        if ( $arreglo[$miKey + 9999]['nombre'] == "TOTAL DE PASIVO" ) {
                            $arreglo[$miKey + 9999]['nombre'] = "TOTAL DEL PASIVO";
                        }
                        if ( $arreglo[$miKey + 9999]['nombre'] == "TOTAL DE HACIENDA PÚBLICA/PATRIMONIO" ) {
                            $arreglo[$miKey + 9999]['nombre'] = "TOTAL HACIENDA PÚBLICA/PATRIMONIO";
                        }
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
                $debe = (is_numeric($arreglo[$indice]['debe']) ? $arreglo[$indice]['debe'] : 0.00);
                $haber = (is_numeric($arreglo[$indice]['haber']) ? $arreglo[$indice]['haber'] : 0.00);
                $arreglo[$indice]['importe'] = (($arreglo[$indice]['naturalezaCuenta'] == 'D') ? $debe - $haber : $haber - $debe);
                //Si es cuenta del activo y la naturaleza de la cuenta es diferente a la del genero se pone el importe en negativo.
                if ($arreglo[$indice]['ger_id'] == 1 && $arreglo[$indice]['naturalezaCuenta'] != $naturalezaActivo && $arreglo[$indice]['nivel'] >= 3) {
                    $arreglo[$indice]['importe'] = ($arreglo[$indice]['importe'] * -1);
                }
            }
            //regresamos arreglo ordenado de manera ascendente
            return $arreglo;
        }
    }


    // Entiendo que están ajustando los totales... obtienen el total de la 321 y luego
    // a todas las cuentas que empiecen con 322 se lo suman... el nombre está un poco confuso
    // porque esta función la aplican tanto al ejercicio actual como al anterior
    function ajusteEjercicioAnterior(&$hacienda)
    {
        $debeahorro = $hacienda['321']['debe'];
        $haberahorro = $hacienda['321']['haber'];
        foreach ($hacienda as $key => $val) {
            if (strlen($key) >= 3) {
                if (substr($key, 0, 3) == 322) {
                    $debeEjerciciosA = $hacienda[$key]['debe'];
                    $habeEjerciciosA = $hacienda[$key]['haber'];
                    $hacienda[$key]['debe'] = $debeEjerciciosA + $debeahorro;
                    $hacienda[$key]['haber'] = $habeEjerciciosA + $haberahorro;
                }
            }
        }
    }


    // Entiendo que están ajustando los totales... al total de la sección 3 parece que le falta 
    // lo de las cuentas 32... así que obtienen los importes y se lo suman
    function ajustePatrimonioGenerado(&$hacienda)
    {
        $debe = 0;
        $haber = 0;
        foreach ($hacienda as $key => $val) {
            if (strlen($key) == 3) {
                if (substr($key, 0, 2) == 32) {
                    $debe += $hacienda[$key]['debe'];
                    $haber += $hacienda[$key]['haber'];
                }
            }
        }
        $hacienda[32]['debe'] = $debe;
        $hacienda[32]['haber'] = $haber;

        $hacienda[3]['debe'] = $hacienda[31]['debe'] + $hacienda[32]['debe'];
        $hacienda[3]['haber'] = $hacienda[31]['haber'] + $hacienda[32]['haber'];
    }


    // Aquí insertamos el Resultado del Ejercicio del Estado de Actividades...
    // Buscamos la 321 y ahí le ponemos el importe que nos pasen
    function PonResultadoDelEjercicio(&$hacienda, $saldo, $nivel)
    {

        if ( !array_key_exists(321, $hacienda) ) {

            if ( !array_key_exists(3, $hacienda) ) {
                $hacienda[3] = [
                                'cuenta' => '3', 
                                'nombre' => '3 HACIENDA PÚBLICA/PATRIMONIO', 
                                'debe' => 0, 
                                'haber' => $saldo, 
                                'naturalezaDetalle' => 'H', 
                                'naturalezaCuenta' => 'H', 
                                'ger_id' => '3', 
                                'ger_cuenta' => '3', 
                                'ger_nombre' => '3 HACIENDA PÚBLICA/PATRIMONIO', 
                                'nivel' => 1
                            ];
            }

            if ( !array_key_exists(32, $hacienda) ) {
                $hacienda[32] = [
                                'cuenta' => '3.2', 
                                'nombre' => '3.2 HACIENDA PÚBLICA/PATRIMONIO GENERADO', 
                                'debe' => 0, 
                                'haber' => $saldo, 
                                'naturalezaDetalle' => 'H', 
                                'naturalezaCuenta' => 'H', 
                                'ger_id' => '3', 
                                'ger_cuenta' => '3', 
                                'ger_nombre' => '3 HACIENDA PÚBLICA/PATRIMONIO', 
                                'cgp_id' => '2', 
                                'cgp_cuenta' => '3.2', 
                                'cgp_nombre' => '3.2 HACIENDA PÚBLICA/PATRIMONIO GENERADO', 
                                'nivel' => 2
                            ];
            }

            // Si pidieron incluir el nivel 3, pero la 321 no hay registros aún para la 321...
            if ( $nivel[3] == 1 && !array_key_exists(321, $hacienda) ) {
                $hacienda[321] = [
                                   'cuenta' => '3.2.1', 
                                    'nombre' => '3.2.1. RESULTADOS DE EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'debe' => 0,
                                    'haber' => $saldo,
                                    'naturalezaDetalle' => 'H', 
                                    'naturalezaCuenta' => 'H', 
                                    'ger_id' => '3', 
                                    'ger_cuenta' => '3', 
                                    'ger_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO', 
                                    'cgp_id' => '2', 
                                    'cgp_cuenta' => '3.2', 
                                    'cgp_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO GENERADO', 
                                    'rub_id' => '1', 
                                    'rub_cuenta' => '3.2.1', 
                                    'rub_nombre' => 'RESULTADOS DE EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'nivel' => 3
                                ];
            }

            // Si pidieron incluir el nivel 4, pero la 321 no hay registros aún para la 321...
            if ( $nivel[4] == 1 && !array_key_exists(3210, $hacienda) ) {
                $hacienda[3210] = [
                                    'cuenta' =>  '3.2.1.0', 
                                    'nombre' =>  'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)', 
                                    'debe' =>  0, 
                                    'haber' =>  $saldo, 
                                    'naturalezaDetalle' =>  'H', 
                                    'naturalezaCuenta' =>  'H', 
                                    'ger_id' =>  '3', 
                                    'ger_cuenta' =>  '3', 
                                    'ger_nombre' =>  'HACIENDA PÚBLICA/PATRIMONIO', 
                                    'cgp_id' =>  '2', 
                                    'cgp_cuenta' =>  '3.2', 
                                    'cgp_nombre' =>  'HACIENDA PÚBLICA/PATRIMONIO GENERADO', 
                                    'rub_id' =>  '1', 
                                    'rub_cuenta' =>  '3.2.1', 
                                    'rub_nombre' =>  'RESULTADOS DE EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'cgcu_id' =>  '0', 
                                    'cgcu_cuenta' =>  '3.2.1.0', 
                                    'cgcu_nombre' =>  'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)', 
                                    'nivel' => 4
                                ];
            }

            // Si pidieron incluir el nivel 5, pero la 321 no hay registros aún para la 321...
            if ( $nivel[5] == 1 && !array_key_exists(32100, $hacienda) ) {
                $hacienda[32100] = [
                                    'cuenta' => '3.2.1.0.0', 
                                    'nombre' => 'RESULTADOS DEL EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'debe' => 0, 
                                    'haber' => $saldo, 
                                    'naturalezaDetalle' => 'H', 
                                    'naturalezaCuenta' => 'H', 
                                    'ger_id' => '3', 
                                    'ger_cuenta' => '3', 
                                    'ger_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO', 
                                    'cgp_id' => '2', 
                                    'cgp_cuenta' => '3.2', 
                                    'cgp_nombre' => 'HACIENDA PÚBLICA/PATRIMONIO GENERADO', 
                                    'rub_id' => '1', 
                                    'rub_cuenta' => '3.2.1', 
                                    'rub_nombre' => 'RESULTADOS DE EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'cgcu_id' => '0', 
                                    'cgcu_cuenta' => '3.2.1.0', 
                                    'cgcu_nombre' => 'RESULTADOS DEL EJERCICIO (AHORRO/DESAHORRO)', 
                                    'cgsc_id' => '0', 
                                    'cgsc_cuenta' => '3.2.1.0.0', 
                                    'cgsc_nombre' => 'RESULTADOS DEL EJERCICIO (AHORRO/ DESAHORRO)', 
                                    'nivel' => 5
                                ];
            }

            // Si ya existe, le ponemos fijo el saldo del Estado de Actividades que nos pasan
            // OJO: SÓLO ESTA SECCIÓN ES LA QUE ESTABA ORIGINALMENTE
        } else {

            foreach ($hacienda as $key => $val) {
                if (strlen($key) >= 3) {
                    if (substr($key, 0, 3) == 321) {
                        // cuenta acreedora
                        $hacienda[$key]['debe'] = 0;
                        $hacienda[$key]['haber'] = $saldo;
                    }
                }
            }
        }
    }


    // Esta función nos la traemos de la impresión del PDF... luego averiguamos qué hace
    function ajusteCuentasAhorro(&$hacienda, $saldoActual, $saldoAnterior)
    {

        $totalHacienda_Actual = 0;
        $totalHacienda_Anterior = 0;
        foreach ($hacienda as $key => $val) {

            if (substr($val['cuenta'], 0, 5) == 31999) {
                // cuenta acreedora
                $totalHacienda_Actual += $hacienda[$key]['importe_Actual'];
                $totalHacienda_Anterior += $hacienda[$key]['importe_Anterior'];
            }
            if (substr($val['cuenta'], 0, 3) == 321) {
                // cuenta acreedora
                $hacienda[$key]['importe_Actual'] = $saldoActual;
                $hacienda[$key]['importe_Anterior'] = $saldoAnterior;

                $totaldebehacienda = 0;
                $totalhaberhacienda = 0;
            }

            if (substr($val['cuenta'], 0, 5) == 32999) {
                // cuenta acreedora
                $hacienda[$key]['importe_Actual'] = $hacienda[$key]['importe_Actual'] + $saldoActual;
                $hacienda[$key]['importe_Anterior'] = $hacienda[$key]['importe_Anterior'] + $saldoAnterior;
                $totalHacienda_Actual += $hacienda[$key]['importe_Actual'];
                $totalHacienda_Anterior += $hacienda[$key]['importe_Anterior'];
            }
        }

        foreach ($hacienda as $key => $val) {

            if (substr($val['cuenta'], 0, 5) == 33999) {
                // cuenta acreedora
                $hacienda[$key]['importe_Actual'] = $totalHacienda_Actual;
                $hacienda[$key]['importe_Anterior'] = $totalHacienda_Anterior;
                $hacienda[$key]['nombre'] = "TOTAL DE HACIENDA PÚBLICA/PATRIMONIO";
            }
        }
    }

    function dame_plural_del_nombre( $nombre ) {
        switch ( trim( $nombre ) ) {
            case "ACTIVO CIRCULANTE":
                return "ACTIVOS CIRCULANTES";
            case "ACTIVO NO CIRCULANTE":
                return "ACTIVOS NO CIRCULANTES";
            case "PASIVO CIRCULANTE":
                return "PASIVOS CIRCULANTES";
            case "PASIVO NO CIRCULANTE":
                return "PASIVOS NO CIRCULANTES";
            default:
                return $nombre;
        }
    }

    public function contenido()
    {
        $estado = array();
        foreach ($this->datos as $value) {
            $importeActualIzquierda = in_array($value['nombreIzquierda'], ['ACTIVO', 'ACTIVO CIRCULANTE', 'ACTIVO NO CIRCULANTE']) ? '' : floatval($value['importeActualIzquierda']) / intval($this->chkEnMiles);
            $importeAnteriorIzquierda = in_array($value['nombreIzquierda'], ['ACTIVO', 'ACTIVO CIRCULANTE', 'ACTIVO NO CIRCULANTE']) ? '' : floatval($value['importeAnteriorIzquierda']) / intval($this->chkEnMiles);
            $importeActualDerecha =  in_array($value['nombreDerecha'], ['PASIVO', 'PASIVO CIRCULANTE', 'PASIVO NO CIRCULANTE', 'HACIENDA PÚBLICA/PATRIMONIO']) ? '' : floatval($value['importeActualDerecha']) / intval($this->chkEnMiles);
            $importeAnteriorDerecha = in_array($value['nombreDerecha'], ['PASIVO', 'PASIVO CIRCULANTE', 'PASIVO NO CIRCULANTE','HACIENDA PÚBLICA/PATRIMONIO']) ? '' : floatval($value['importeAnteriorDerecha']) / intval($this->chkEnMiles);

            switch($value['nombreIzquierda']){
                case 'ACTIVO':
                    $nombreIzquierda = $value['nombreIzquierda'];
                break;
                case 'ACTIVO NO CIRCULANTE':
                    $nombreIzquierda = "Activo No Circulante";
                break;
                default:
                    $nombreIzquierda = UrNombre::titleCase($value['nombreIzquierda']);
                break;
            }

            switch($value['nombreDerecha']){
                case 'PASIVO':
                case 'HACIENDA PÚBLICA/PATRIMONIO':
                    $nombreDerecha = $value['nombreDerecha'];
                break;
                case 'PASIVO NO CIRCULANTE':
                    $nombreDerecha = "Pasivo No Circulante";
                break;
                case 'TOTAL DE PASIVOS NO CIRCULANTES':
                    $nombreDerecha = "Total de Pasivos No Circulantes";
                break;
                default:
                    $nombreDerecha = UrNombre::titleCase($value['nombreDerecha']);
                break;

            }
            if(!empty($nombreIzquierda)){
                $importeActIzquierda = $importeActualIzquierda;
                $importeAntIzquierda = $importeAnteriorIzquierda;
            }else{
                $importeActIzquierda = "";
                $importeAntIzquierda = "";
            }
            array_push( $estado, array( "nombreIzquierda" => $value['nivelIzquierda']."_".$nombreIzquierda, "importeActualIzquierda" => $importeActIzquierda, "importeAnteriorIzquierda" => $importeAntIzquierda, "nombreDerecha" => $value['nivelDerecha']."_".$nombreDerecha, "importeActualDerecha" => $importeActualDerecha, "importeAnteriorDerecha" => $importeAnteriorDerecha) );
        }

        $encabezado = array('encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4' => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7' => $this->encabLin7, 'alineacionenc' => 'C');

        
        $nombreColumnas = array('Concepto', UrNombre::titleCase($this->mesNombre).' '.$this->ejercicioActual, 'Diciembre '.$this->ejercicioAnterior, 'Concepto', UrNombre::titleCase($this->mesNombre).' '.$this->ejercicioActual, 'Diciembre '.$this->ejercicioAnterior);
        $incluirColumnas = array('nombreIzquierda', 'importeActualIzquierda', 'importeAnteriorIzquierda', 'nombreDerecha', 'importeActualDerecha', 'importeAnteriorDerecha');
        $tipoColumnas = array('cI', 'i', 'i', 'cI', 'i', 'i');
        $anchoColumnas = [
            0 => 85,
            1 => 30,
            2 => 30,
            3 => 85,
            4 => 30,
            5 => 30
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

        $tablaimg[0]['colImg'] = "E1";
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
$exp = new exp_SituacionFinanciera();
