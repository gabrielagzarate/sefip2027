<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

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

class exp_CambioSituacionFinanciera extends generaPhpspreadsheet
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
    public $nivel;
    public $desde;
    public $hasta;
    public $mesNombre;
    public $chkEnMiles;
    public $cerosOrden;
    public $checkCuentaOrden;
    public $activo;
    public $pasivo;
    public $pasivoHacienda;
    public $result2016;
    public $result2015;

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
        $this->chkEnMiles = isset($_POST['ckmiles']) ? 1000 : 1;
        $this->mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != '') ? $_POST['grupos'] : '';
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != '') ? $_POST['excluir'] : '';
        $consolidado = (isset($_POST['consolidado']) && $_POST['consolidado'] != '') ? $_POST['consolidado'] : '';
        $this->ceros = (isset($_POST['ceros']) && $_POST['ceros'] != '') ? $_POST['ceros'] : '';
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

        $this->nivel[1] = isset($_POST['checkNivel1']) ? $_POST['checkNivel1'] : -1;
        $this->nivel[2] = isset($_POST['checkNivel2']) ? $_POST['checkNivel2'] : -1;
        $this->nivel[3] = isset($_POST['checkNivel3']) ? $_POST['checkNivel3'] : -1;
        $this->nivel[4] = isset($_POST['checkNivel4']) ? $_POST['checkNivel4'] : -1;
        $this->nivel[5] = isset($_POST['checkNivel5']) ? $_POST['checkNivel5'] : -1;

        if ($ejercicio == '') {
            $ur = new UnidadResponsable();
            $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ur->select();
        }

        $this->encabLin3 = 'Estado de Cambios en la Situación Financiera';
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

        $activo2020 = [];
        $arrayPH2020 = [];

        $ejercicioActual = $_SESSION[_S]['ejercicioActual'];

        list($activo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 1, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        list($pasivo) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 2, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        list($hacienda) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 3, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);

        list($ingresos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 4, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        list($gastos) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesHasta + 1, -1, 5, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);

        //obtenemos resultado del ejercicio (ahorro/desahorro)
        $result2021 = 0;
        if (count($ingresos) > 0 && count($gastos) > 0) {
            $natIng2021 = $ingresos[4]['naturalezaCuenta'];
            $natGas2021 = $gastos[5]['naturalezaCuenta'];

            $debeI2021 = $ingresos[4]['debe'];
            $haberI2021 = $ingresos[4]['haber'];
            $debeG2021 = $gastos[5]['debe'];
            $haberG2021 = $gastos[5]['haber'];

            if ($natIng2021 == 'D') {
                $result2021 = $debeI2021 - $haberI2021;
            } else {
                $result2021 = $haberI2021 - $debeI2021;
            }

        }

        list($activo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 1, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        list($pasivo15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 2, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        list($hacienda15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesDesde - 1, 3, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);

        // AUNQUE LA REGLA ES QUITAR ÉSTOS COMENTARIOS, SE PIDE ESPERAR AL SEGUNDO TRIMESTRE
        // DEL 2024, YA QUE HAYA PASADO LA REVISIÓN DE CONTABILIDAD, PARA NO PERDER
        // EL HILO DE CÓMO SE OBTIENE EL RESULTADO DEL EJERCICIO DEL PERIODO ANTERIOR

        // list($ingresos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesHasta -1, 4, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);
        // list($gastos15) = consolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, 0, $mesHasta -1, 5, $tipoReporte, $ejercicioActual, $this->chkEnMiles, $grupos, $excluir, $this->ceros, $this->incCta);

        $result2020 = 0;

        $activo2020 = $this->devuelveArrayOrdenado($activo15, $this->nivel);
        $pasivo2020 = $this->devuelveArrayOrdenado($pasivo15, $this->nivel);
        $hacienda2020 = $this->devuelveArrayOrdenado($hacienda15, $this->nivel);


        $arrayPH2020 = $pasivo2020 + $hacienda2020;

        $activo2021 = $this->devuelveArrayOrdenado($activo, $this->nivel);
        $pasivo2021 = $this->devuelveArrayOrdenado($pasivo, $this->nivel);
        $hacienda2021 = $this->devuelveArrayOrdenado($hacienda, $this->nivel);

        #SE UNEN LOS ARREGLOS
        $arrayPH2021 = $pasivo2021 + $hacienda2021;

        $arrayActivo = $this->compararCuentas($activo2020, $activo2021);
        $arrayPasivoHacienda = $this->compararCuentas($arrayPH2020, $arrayPH2021);

        /*
        REALIZAMOS LAS OPERACIONES CORRESPONDIENTES PARA OBTENER EL ORIGEN Y LA APLICACION
        Para el activo, será origen cuando la diferencia de 2020-2018 sea negativo, en caso contrario será aplicacion
        Para el Pasivo y Hacienda, cuando la diferencia de 2020-2018 sea negativo será aplicación, caso contrario será origen.

        Los totales de los niveles 2 será la sumatoria que le corresponda a cada grupo
         */

        //activo
        $diferencia = $totalOrigenActivo = $totalAplicacionActivo = $totalOrigenPH = $totalAplicacionPH = $sumaO = $sumaA = 0;
        $grupo = '';

        foreach ($arrayActivo as $act => $val) {
            $arrayActivo[$act] = $arrayActivo[$act];
            $diferencia = ($arrayActivo[$act]['importe2021'] - $arrayActivo[$act]['importe2020']);
            if ($diferencia < 0) {
                $arrayActivo[$act]['origen'] = ($diferencia) * -1;
                $arrayActivo[$act]['aplicacion'] = 0;
            } else {
                $arrayActivo[$act]['origen'] = 0;
                $arrayActivo[$act]['aplicacion'] = $diferencia;
            }

            //reseteamos los valores del nivel 2 para obtener la suma original del origen y aplicacion
            if ($arrayActivo[$act]['nivel'] <= 2) {
                $arrayActivo[$act]['origen'] = 0;
                $arrayActivo[$act]['aplicacion'] = 0;
            }
            //obtenemos los totales del activo
            $totalOrigenActivo += $arrayActivo[$act]['origen'];
            $totalAplicacionActivo += $arrayActivo[$act]['aplicacion'];
        }

        //obtenemos los totales de los grupos del activo

        foreach ($arrayActivo as $tot => $val) {
            //obtenemos las llaves de los grupos
            if ($arrayActivo[$tot]['nivel'] == 3) {
                $reemplazaPunto = str_replace(['.'], [''], $arrayActivo[$tot]['cuenta']);
                $subs = substr($reemplazaPunto, 0, 2);
                $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
                $llaveRubro = $tot;
            }
            //ponemos el total global del activo
            if ($arrayActivo[$tot]['nivel'] == 1) {
                $arrayActivo[$tot]['origen'] = $totalOrigenActivo;
                $arrayActivo[$tot]['aplicacion'] = $totalAplicacionActivo;
            }

            //obtenemos el total de cada grupo.
            if ($arrayActivo[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
                $arrayActivo[$llaveGrupo]['origen'] += $arrayActivo[$tot]['origen'];
                $arrayActivo[$llaveGrupo]['aplicacion'] += $arrayActivo[$tot]['aplicacion'];
            }
        }

        //obteniendo origen y aplicacion de todos los niveles de pasivo + hacienda
        foreach ($arrayPasivoHacienda as $pas => $val) {
            $arrayPasivoHacienda[$pas] = $arrayPasivoHacienda[$pas];
            $diferencia = ($arrayPasivoHacienda[$pas]['importe2021'] - $arrayPasivoHacienda[$pas]['importe2020']);
            if ($diferencia < 0) {
                $arrayPasivoHacienda[$pas]['origen'] = 0;
                $arrayPasivoHacienda[$pas]['aplicacion'] = ($diferencia) * -1;
            } else {
                $arrayPasivoHacienda[$pas]['origen'] = $diferencia;
                $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
            }
            //reseteamos el valor de los niveles 1 y 2, menos el registro de resultado del ejercicio.
            if ($arrayPasivoHacienda[$pas]['nivel'] <= 2 && $pas != 49000) {
                $arrayPasivoHacienda[$pas]['origen'] = 0;
                $arrayPasivoHacienda[$pas]['aplicacion'] = 0;
            }
        }

        //obtenemos los totales de los grupos del pasivo + hacienda
        foreach ($arrayPasivoHacienda as $tot => $val) {
            //obtenemos las llaves de los grupos nivel 2
            if ($arrayPasivoHacienda[$tot]['nivel'] == 3) {
                $reemplazaPunto = str_replace(['.'], [''], $arrayPasivoHacienda[$tot]['cuenta']);
                $subs = substr($reemplazaPunto, 0, 2);
                $llaveGrupo = str_pad($subs, 5, '0', STR_PAD_RIGHT) + 000;
                $llaveRubro = $tot;
            }
            //obtenemos el total de cada grupo. nivel 2
            if ($arrayPasivoHacienda[$tot]['nivel'] == 3 && substr($llaveGrupo, 0, 2) == substr($llaveRubro, 0, 2)) {
                $arrayPasivoHacienda[$llaveGrupo]['origen'] += $arrayPasivoHacienda[$llaveRubro]['origen'];
                $arrayPasivoHacienda[$llaveGrupo]['aplicacion'] += $arrayPasivoHacienda[$llaveRubro]['aplicacion'];
            }

            //obtenemos el total global de pasivo + hacienda
            $totalOrigenPH += $arrayPasivoHacienda[$tot]['origen'];
            $totalAplicacionPH += $arrayPasivoHacienda[$tot]['aplicacion'];
        }

        //obtenemos los totales de los generos
        foreach ($arrayPasivoHacienda as $key => $val) {
            //obtenemos las llaves de los generos nivel 1
            if ($arrayPasivoHacienda[$key]['nivel'] == 2) {
                $reemplazaPunto2 = str_replace(['.'], [''], $arrayPasivoHacienda[$key]['cuenta']);
                $subs2 = substr($reemplazaPunto2, 0, 1);
                $llaveGenero = str_pad($subs2, 5, '0', STR_PAD_RIGHT) + 0000;
                $llaveGrupo2 = $key;
            }
            //obtenemos el total de cada genero. nivel 1
            if ($arrayPasivoHacienda[$key]['nivel'] == 2 && substr($llaveGenero, 0, 1) == substr($llaveGrupo2, 0, 1)) {
                $arrayPasivoHacienda[$llaveGenero]['origen'] += $arrayPasivoHacienda[$llaveGrupo2]['origen'];
                $arrayPasivoHacienda[$llaveGenero]['aplicacion'] += $arrayPasivoHacienda[$llaveGrupo2]['aplicacion'];
            }
        }

        // Para incluir el resultado del ejercicio tomando la información
        // siguiendo las reglas del Estado de Actividades, según requerimiento
        // del ticket #SDS-611738
        $saldo_final = $hacienda2021[32100]['importe'];
        $saldo_inicial = $hacienda2020[32100]['importe'];
        $resultado_periodo = ($haberI2021 - $debeG2021);
        $importe_final = ($saldo_final - $saldo_inicial) + $resultado_periodo;
        if ($importe_final >= 0) {
            $origen_321 = $importe_final;
            $aplicacion_321 = 0;
        } else {
            $origen_321 = 0;
            $aplicacion_321 = $importe_final;
        }

        // Si tenemos la cuenta desde la balanza, ignoramos ese importe y ponemos
        // el que solicitan
        if (isset($arrayPasivoHacienda[32100])) {
            $arrayPasivoHacienda[32100]['origen'] = $origen_321;
            $arrayPasivoHacienda[32100]['aplicacion'] = $aplicacion_321;
            // Si no la tenemos, la creamos
        } else {
            $arrayPasivoHacienda[32100] = [
                'cuenta'      => '3.2.1',
                'nombre'      => 'Resultados del Ejercicio (Ahorro/Desahorro)' ,
                'nivel'       => 3,
                'importe2021' => 0,
                'importe2020' => 0,
                'origen'      => $origen_321,
                'aplicacion'  => $aplicacion_321,
            ];
        }

        // Actualizamos las sumas de origen y aplicación de la 32000
        $suma_origen_32 = 0;
        $suma_aplicacion_32 = 0;

        foreach ($arrayPasivoHacienda as $key => $val) {
            if (substr($key, 0, 2) == '32' &&
                substr($key, 2, 1) != '0'
            ) {
                $suma_origen_32 += $val['origen'];
                $suma_aplicacion_32 += $val['aplicacion'];
            }
        }

        $arrayPasivoHacienda[32000]['origen'] = $suma_origen_32;
        $arrayPasivoHacienda[32000]['aplicacion'] = $suma_aplicacion_32;

        $this->activo = $arrayActivo;
        $this->pasivoHacienda = $arrayPasivoHacienda;

        $this->nombreArchivoReporte = 'EdoCambioSituacionFinanciera_';
        $this->contenido();
    }


    // ============================================================================================
    // FUNCIONES DE APOYO
    // ============================================================================================


    public function compararCuentas($array2020, $array2021)
    {

        $arrayGlobal = [];
        //recuperamos la posicion inicial en el arreglo.
        reset($array2020);
        reset($array2021);

        $key2021 = key($array2021);
        $key2020 = key($array2020);

        while ($key2021 || $key2020) {
            //si las cadenas son iguales
            if (CompararCadenas((string) $key2020, (string) $key2021) == 0) {
                $arrayGlobal[$key2021]['cuenta'] = $array2021[$key2021]['cuenta'];
                $arrayGlobal[$key2021]['nombre'] = $array2021[$key2021]['nombre'];
                $arrayGlobal[$key2021]['nivel'] = $array2021[$key2021]['nivel'];
                $arrayGlobal[$key2021]['importe2021'] = $array2021[$key2021]['importe'];
                $arrayGlobal[$key2021]['importe2020'] = $array2020[$key2021]['importe'];

                next($array2021);
                $key2021 = key($array2021);

                next($array2020);
                $key2020 = key($array2020);
                //si no existe 2018 pero si en 2020
            } elseif (!$key2020 || CompararCadenas(
                (string) $key2021,
                (string) $key2020
            ) < 0) { //cuentas anteriores
                $arrayGlobal[$key2021]['cuenta'] = $array2021[$key2021]['cuenta'];
                $arrayGlobal[$key2021]['nombre'] = $array2021[$key2021]['nombre'];
                $arrayGlobal[$key2021]['nivel'] = $array2021[$key2021]['nivel'];
                $arrayGlobal[$key2021]['importe2021'] = $array2021[$key2021]['importe'];
                $arrayGlobal[$key2021]['importe2020'] = (isset($arrayGlobal[$key2021]['importe2020']) ? $arrayGlobal[$key2021]['importe2020'] : 0.00);

                next($array2021);
                $key2021 = key($array2021);
            } else {
                //si no existe en 2020 pero si en 2018
                $arrayGlobal[$key2020]['cuenta'] = $array2020[$key2020]['cuenta'];
                $arrayGlobal[$key2020]['nombre'] = $array2020[$key2020]['nombre'];
                $arrayGlobal[$key2020]['nivel'] = $array2020[$key2020]['nivel'];
                $arrayGlobal[$key2020]['importe2021'] = (isset($arrayGlobal[$key2020]['importe2021']) ? $arrayGlobal[$key2020]['importe2021'] : 0.00);
                $arrayGlobal[$key2020]['importe2020'] = $array2020[$key2020]['importe'];

                next($array2020);
                $key2020 = key($array2020);
            }
        }

        return $arrayGlobal;
    }
    //FUNCION PARA DEVOLVER ARRAY ORDENADO
    public function devuelveArrayOrdenado($array, $p)
    {
        $arreglo = [];
        if (is_array($array)) {
            //naturaleza de la cuenta (activo)
            $naturalezaActivo = '';

            foreach ($array as $key => $val) {
                if ($p[$array[$key]['nivel']] == 1) {
                    //Agregamos a la llave del arreglo 0's a la derecha
                    $arreglo[str_pad($key, 5, '0', STR_PAD_RIGHT)] = $array[$key];
                }
            }

            //ordenamos arreglo
            ksort($arreglo);

            $debe = $haber = 0;
            foreach ($arreglo as $indice => $val) {
                if ($arreglo[$indice]['nivel'] == 1 && $arreglo[$indice]['ger_id'] == 1) {
                    $naturalezaActivo = $arreglo[$indice]['naturalezaCuenta'];
                }

                //obtenemos los importes, si la naturaleza de la cuenta es Debe entonces se resta debe - haber, sino lo contrario
                $debe = (is_numeric($arreglo[$indice]['debe']) ? $arreglo[$indice]['debe'] : 0.00);
                $haber = (is_numeric($arreglo[$indice]['haber']) ? $arreglo[$indice]['haber'] : 0.00);
                $arreglo[$indice]['importe'] = (($arreglo[$indice]['naturalezaCuenta'] == 'D') ? ($debe - $haber) : ($haber - $debe));
                //Si es cuenta del activo y la naturaleza de la cuenta es diferente a la del genero se pone el importe en negativo.
                if ($arreglo[$indice]['ger_id'] == 1 && $arreglo[$indice]['naturalezaCuenta'] != $naturalezaActivo && $arreglo[$indice]['nivel'] >= 3) {
                    $arreglo[$indice]['importe'] = ($arreglo[$indice]['importe'] * -1);
                }
            }

            //regresamos arreglo ordenado de manera ascendente
            return $arreglo;
        }
    }

    public function contenido()
    {
        reset($this->activo);
        reset($this->pasivoHacienda);
        $keyActivo = key($this->activo);
        $keyPasivoHacienda = key($this->pasivoHacienda);

        $estado = [];
        for ($i = 0; $i < 2; $i++) {

            if ($i == 0) {
                $key = $keyActivo;
                $array = $this->activo;
            } else {
                $key = $keyPasivoHacienda;
                $array = $this->pasivoHacienda;
            }

            while ($key) {
                $nivel = $array[$key]['nivel'];
                /*if ($nivel != 1) {
                    $nombre = utf8Decode(UrNombre::titleCase($array[$key]['nombre']));
                } else {
                        $nombre = utf8Decode($array[$key]['nombre']);
                }*/
                $origen = $array[$key]['origen'];
                $origen = (isset($origen) ? round($origen / $this->chkEnMiles, 0) : 0);
                $aplicacion = $array[$key]['aplicacion'];
                $aplicacion = (isset($aplicacion) ? round($aplicacion / $this->chkEnMiles, 0) : 0);

                switch ($array[$key]['nombre']) {
                    case 'ACTIVO':
                    case 'PASIVO':
                    case 'HACIENDA PÚBLICA/PATRIMONIO':
                        $nombre = $array[$key]['nombre'];
                        break;
                    case 'ACTIVO NO CIRCULANTE':
                        $nombre = 'Activo No Circulante';
                        break;
                    case 'PASIVO NO CIRCULANTE':
                        $nombre = 'Pasivo No Circulante';
                        break;
                    default:
                        $nombre = UrNombre::titleCase(utf8Encode($array[$key]['nombre']));
                        break;
                }
                array_push($estado, [ 'nombre' => $nivel . '_' . $nombre, 'origen' => $origen, 'aplicacion' => $aplicacion]);

                next($array);
                $key = key($array);
            }
        }

        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4'            => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7, 'alineacionenc' => 'C'];


        $nombreColumnas = ['Concepto','Origen', 'Aplicación'];
        $incluirColumnas = ['nombre', 'origen', 'aplicacion'];
        $tipoColumnas = ['cI', 'i', 'i'];
        $anchoColumnas = [
            0 => 100,
            1 => 30,
            2 => 30,
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

        $tablaimg[0]['colImg'] = 'B1';
        $tablaimg[0]['alingXimg'] = '10';

        $tablaimg[0]['altura'] = '130';
        $tablaimg[0]['rotacion'] = '0';
        if (!is_file($tablaimg[0]['ruta'])) {
            $tablaimg['imagen'] = 0;
            unset($tablaimg[0]);
        }

        $this->generaEdoFinanciero($estado, $encabezado, $table, ['nombre' => $this->nombreArchivoReporte . $this->mesDesde . '_' . $this->mesHasta, 'tablaimg' => $tablaimg, 'leyenda' => $this->leyenda, 'firmante1' => $this->firmante1, 'firmante2' => $this->firmante2, 'firmante3' => $this->firmante3, 'cargo1' => $this->cargo1, 'cargo2' => $this->cargo2, 'cargo3' => $this->cargo3]);
    }
}
$exp = new exp_CambioSituacionFinanciera();
