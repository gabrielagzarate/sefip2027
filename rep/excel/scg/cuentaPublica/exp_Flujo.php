<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

set_time_limit(0);

//librerias
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/reporte/fnc_Flujo.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';

class exp_Flujo extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
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
    public $genero;
    public $gen;
    public $chkEnMiles;

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

        //
        $this->encabLin1 = 'Gobierno del Estado de Oaxaca';
        $this->encabLin6 = '';
        $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
        $this->chkEnMiles = (isset($_POST['chkEnMiles']) != '' ? 1000 : 1);
        $this->mesDesde = (isset($_POST['comboMes']) && $_POST['comboMes'] != '') ? $_POST['comboMes'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';
        $this->genero = isset($_POST['comboGenero']) ? ($_POST['comboGenero'] != '' ? $_POST['comboGenero'] : 9) : 9;
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != '') ? $_POST['grupos'] : '';
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != '') ? $_POST['excluir'] : '';
        $ceros = (isset($_POST['ceros']) && $_POST['ceros'] != '') ? $_POST['ceros'] : '';
        $incCta = isset($_POST['incCta']) ? $_POST['incCta'] : '';
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

        // se rescata el sistema y la opcion
        $sis_id = isset($_POST['sis_id']) ? $_POST['sis_id'] : -1;
        $men_id = isset($_POST['men_id']) ? $_POST['men_id'] : -1;
        $opc_id = isset($_POST['opc_id']) ? $_POST['opc_id'] : -1;


        $this->encabLin3 = 'Estado de Flujos de Efectivo';

        if ($ejercicio == '') {
            $ur = new UnidadResponsable();
            $ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ur->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ur->select();
        }

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

        $this->datos = get_machote_estado_flujo_efectivo();

        $this->nombreArchivoReporte = 'EdoFlujosEfectivo_';
        $this->contenido();
    }


    public function contenido()
    {

        rellena_flujo_efectivo($this->datos, $this->mesDesde, $this->mesHasta);

        $estado = [];
        $falta_info = false;

        foreach ($this->datos as $indice => $valor) {

            $concepto = '';

            $es_linea_separadora = false;
            switch ($indice) {
                /***PRIMER NIVEL*****/
                case '1':
                    //ACTIVO
                case 'suma_FEAO':
                    //BIENES
                case '31':
                case '32':
                    //FINANCIAMIENTO
                case 'suma_FNEAI':
                case '42':
                case '43':
                    //NETO
                case 'suma_FNEAF':
                case '54':
                case 'suma_IDNEEE':
                case '56':
                case 'importe_EEEIE':
                case '58':
                case 'suma_EEEFE':
                    /************* *SEGUNDO NIVEL **************/
                    //ACTIVO
                case 'suma_FEAO_O':
                    //PASIVO
                case 'suma_FEAO_A':
                    //OPERACION
                case 'suma_FEAI_O':
                case 'suma_FEAI_A':
                    //FINANCIAMIENTO
                case 'suma_FEAF_O':
                case 'suma_FEAF_A':
                    /************* *TERCER NIVEL **************/
                    //CTAS DEL ACTIVO
                case 'importe_411':
                case 'importe_412':
                case 'importe_413':
                case 'importe_414':
                case 'importe_415':
                case 'importe_416':
                case 'importe_417':
                case 'importe_421':
                case 'importe_422':
                case 'importe_OOO':
                    //CTAS DEL PASIVO
                case 'importe_511':
                case 'importe_512':
                case 'importe_513':
                case 'importe_521':
                case 'importe_522':
                case 'importe_523':
                case 'importe_524':
                case 'importe_525':
                case 'importe_526':
                case 'importe_527':
                case 'importe_528':
                case 'importe_529':
                case 'importe_531':
                case 'importe_532':
                case 'importe_533':
                case 'importe_OAO':
                    //BIENES
                case 'importe_123_O':
                case 'importe_124_O':
                case 'importe_OOI':
                case 'importe_123_A':
                case 'importe_124_A':
                case 'importe_OOA':
                    //FINANCIAMIENTO
                case 'suma_OEN':
                case 'importe_OEF':
                case 'suma_ASD':
                case 'importe_OAF':
                    /********** CUARTO NIVEL */
                    //FINANCIAMIENTO
                case 'importe_OEN_I':
                case 'importe_OEN_E':
                case 'importe_ASD_I':
                case 'importe_ASD_E':
                    if (trim(isset($valor['niv1']) == '') && trim(isset($valor['niv2']) == '') && trim(isset($valor['niv3']) == '') && trim(isset($valor['niv4']) == '')) {
                        $es_linea_separadora = true;
                        $concepto = '';
                        $importeActual = '';
                        $importeAnterior = '';
                    }
                    break;
            }

            // Por si piden en miles de pesos
            // SIEMPRE Y CUANDO NO SEA UNA LÍNEA SEPARADORA
            if (!$es_linea_separadora) {

                // Usamos el chr(160) porque en UTF8 se "ve" como espacio pero no es un espacio, y así
                // el FPDF no lo quita (quita todos los espacios al principio de la cadena)
                switch ($indice) {
                    case '1':
                        //BIENES
                    case '31':
                    case '32':
                        //FINANCIAMIENTO
                    case '42':
                    case '43':
                        //NETO
                    case '54':
                    case '56':
                    case '58':
                        if (isset($valor['niv1']) != '') {
                            $concepto = '1_' . $valor['niv1'];
                        }
                        break;
                        //ACTIVO
                    case 'suma_FEAO':
                        //FINANCIAMIENTO
                    case 'suma_FNEAI':
                        //NETO
                    case 'suma_FNEAF':
                    case 'suma_IDNEEE':
                    case 'importe_EEEIE':
                    case 'suma_EEEFE':
                        if (isset($valor['niv1']) != '') {
                            $concepto = '1_' . $valor['niv1'];
                        }
                        if ($this->chkEnMiles == 1000) {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) / $this->chkEnMiles : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) / $this->chkEnMiles : 0;
                        } else {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) : 0;
                        }
                        break;
                        //ACTIVO
                    case 'suma_FEAO_O':
                        //PASIVO
                    case 'suma_FEAO_A':
                        //OPERACION
                    case 'suma_FEAI_O':
                    case 'suma_FEAI_A':
                        //FINANCIAMIENTO
                    case 'suma_FEAF_O':
                    case 'suma_FEAF_A':
                        if ($valor['niv2'] != '') {
                            $concepto = '2_' . $valor['niv2'];
                        }
                        if ($this->chkEnMiles == 1000) {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) / $this->chkEnMiles : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) / $this->chkEnMiles : 0;
                        } else {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) : 0;
                        }
                        break;
                        //CTAS DEL ACTIVO
                    case 'importe_411':
                    case 'importe_412':
                    case 'importe_413':
                    case 'importe_414':
                    case 'importe_415':
                    case 'importe_416':
                    case 'importe_417':
                    case 'importe_421':
                    case 'importe_422':
                    case 'importe_OOO':
                        //CTAS DEL PASIVO
                    case 'importe_511':
                    case 'importe_512':
                    case 'importe_513':
                    case 'importe_521':
                    case 'importe_522':
                    case 'importe_523':
                    case 'importe_524':
                    case 'importe_525':
                    case 'importe_526':
                    case 'importe_527':
                    case 'importe_528':
                    case 'importe_529':
                    case 'importe_531':
                    case 'importe_532':
                    case 'importe_533':
                    case 'importe_OAO':
                        //BIENES
                    case 'importe_123_O':
                    case 'importe_124_O':
                    case 'importe_OOI':
                    case 'importe_123_A':
                    case 'importe_124_A':
                    case 'importe_OOA':
                        //FINANCIAMIENTO
                    case 'importe_OEF':
                    case 'importe_OAF':
                        if ($valor['niv3'] != '') {
                            $concepto = '3_' . $valor['niv3'];
                        }
                        if ($this->chkEnMiles == 1000) {
                            $importesFormateadosActuales = isset($valor['sAct']) ? round(as_number($valor['sAct']), 2) / $this->chkEnMiles : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? round(as_number($valor['sAnt']), 2) / $this->chkEnMiles : 0;
                        } else {
                            $importesFormateadosActuales = isset($valor['sAct']) ? round(as_number($valor['sAct']), 2) : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? round(as_number($valor['sAnt']), 2) : 0;
                        }
                        break;
                        //FINANCIAMIENTO
                    case 'suma_OEN':
                    case 'suma_ASD':
                        if ($valor['niv3'] != '') {
                            $concepto = '3_' . $valor['niv3'];
                        }
                        if ($this->chkEnMiles == 1000) {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) / $this->chkEnMiles : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) / $this->chkEnMiles : 0;
                        } else {
                            $importesFormateadosActuales = isset($valor['sAct']) ? (int)as_number($valor['sAct']) : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? (int)as_number($valor['sAnt']) : 0;
                        }
                        break;
                        //FINANCIAMIENTO
                    case 'importe_OEN_I':
                    case 'importe_OEN_E':
                    case 'importe_ASD_I':
                    case 'importe_ASD_E':
                        if ($valor['niv4'] != '') {
                            $concepto = '4_' . $valor['niv4'];
                        }
                        if ($this->chkEnMiles == 1000) {
                            $importesFormateadosActuales = isset($valor['sAct']) ? round(as_number($valor['sAct']), 2) / $this->chkEnMiles : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? round(as_number($valor['sAnt']), 2) / $this->chkEnMiles : 0;
                        } else {
                            $importesFormateadosActuales = isset($valor['sAct']) ? round(as_number($valor['sAct']), 2) : 0;
                            $importesFormateadosAnteriores = isset($valor['sAnt']) ? round(as_number($valor['sAnt']), 2) : 0;
                        }
                        break;
                }

                // Por si piden en miles de pesos
                switch ($indice) {
                    case '1':
                    case '31':
                    case '32':
                    case '42':
                    case '43':
                    case '54':
                    case '56':
                    case '58':
                        $importeActual = '';
                        $importeAnterior = '';
                        break;
                    default:
                        $importeActual = $importesFormateadosActuales;
                        $importeAnterior = $importesFormateadosAnteriores;
                        break;
                }

            }

            array_push($estado, [ 'concepto' => $concepto , 'actual' => $importeActual, 'anterior' => $importeAnterior ]);

            if ($importeAnterior == 'FALTA   INFO') {
                $falta_info = true;
            }
        }

        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4'            => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7, 'alineacionenc' => 'C'];

        $nombreColumnas = ['Concepto', UrNombre::titleCase($this->mesNombre) . ' ' . $this->ejercicioActual, 'Diciembre ' . $this->ejercicioAnterior];
        $incluirColumnas = ['concepto', 'actual', 'anterior'];
        $tipoColumnas = ['cI', 'i', 'i'];
        $anchoColumnas = [
            0 => 120,
            1 => 20,
            2 => 20,
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
$exp = new exp_Flujo();
