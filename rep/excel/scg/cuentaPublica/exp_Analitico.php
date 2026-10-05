<?php

session_start();
ini_set('memory_limit', '200M');
ini_set('max_execution_time', 300);

set_time_limit(0);

//librerias
require_once '../../../../ent/UnidadResponsable.php';
require_once '../../../../lib/compararCadenas.php';
require_once '../../../../lib/scg/conciliacion/consolidacion.php';
require_once dirname(__FILE__) . '/../../../lib/generaPhpspreadsheet.php';
require_once '../../../../lib/constantes.php';
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';

class exp_Analitico extends generaPhpspreadsheet
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
    public $naturalezaGenero;
    public $registros;
    public $sort;

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

        // NOTA: Cambio en los encabezados del reporte solicitado en el Ticket #200644
        $this->gen = '';
        switch ($this->genero) {
            case 1:
                $this->gen = 'del Activo';
                break;
            case 2:
                $this->gen = 'del Pasivo';
                break;
            case 3:
                $this->gen = 'de la Hacienda Pública/Patrimonio';
                break;
            case 4:
                $this->gen = 'de Ingresos y Otros beneficios';
                break;
            case 5:
                $this->gen = 'de Gastos y Otras Perdidas';
                break;
        }

        $this->encabLin3 = 'Estado Analítico ' . $this->gen . '';

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

        // Obtenemos la balanza de comprobación del período solicitado
        list($this->datos) = balanzaConsolidacion($_SESSION[_S]['clasAdm-gpo'], $_SESSION[_S]['clasAdm-ur'], $ue, $mesDesde, $mesHasta, $this->genero, $tipoReporte, $ejercicio, '', $grupos, $excluir, $ceros, $incCta);

        $this->naturalezaGenero = $this->datos[$this->genero]['naturalezaCuenta'];
        $this->registros = [];
        $this->sort = [];
        $this->sort['indice'] = [];
        foreach ($this->datos as $i => $val) {
            $this->sort['indice'][$i] = str_pad($i, 5, '0', STR_PAD_RIGHT);
        }
        array_multisort($this->sort['indice'], SORT_ASC, $this->datos);
        if ($this->datos) {
            foreach ($this->datos as $indice => $valor) {
                if ($this->nivel[$valor['nivel']] == 1) {
                    $this->registros[$indice]['nombre'] = utf8Decode(trim($valor['nombre']));
                    if ($valor['naturalezaCuenta'] == 'D') {
                        if ($valor['nivel'] >= 1 || $valor['cuenta'] == '') {
                            $this->registros[$indice]['inicial'] = (($valor['debeI'] - $valor['haberI']) / $this->chkEnMiles);
                            $this->registros[$indice]['debe'] = (($valor['debeM']) / $this->chkEnMiles);
                            $this->registros[$indice]['haber'] = (($valor['haberM']) / $this->chkEnMiles);
                            $this->registros[$indice]['final'] = ($this->registros[$indice]['inicial'] + $this->registros[$indice]['debe'] - $this->registros[$indice]['haber']);
                            $this->registros[$indice]['flujo'] = ($this->registros[$indice]['final'] - $this->registros[$indice]['inicial']);
                            $this->registros[$indice]['nivel'] = $valor['nivel'];
                        } else {
                            $this->registros[$indice]['inicial'] = '';
                            $this->registros[$indice]['debe'] = '';
                            $this->registros[$indice]['haber'] = '';
                            $this->registros[$indice]['final'] = '';
                            $this->registros[$indice]['flujo'] = '';
                            $this->registros[$indice]['nivel'] = '';
                        }
                    } else {
                        if ($valor['nivel'] >= 1 || $valor['cuenta'] == '') {
                            $this->registros[$indice]['inicial'] = (($valor['haberI'] - $valor['debeI']) / $this->chkEnMiles);
                            $this->registros[$indice]['debe'] = (($valor['debeM']) / $this->chkEnMiles);
                            $this->registros[$indice]['haber'] = (($valor['haberM']) / $this->chkEnMiles);
                            $this->registros[$indice]['final'] = ($this->registros[$indice]['inicial'] - $this->registros[$indice]['debe'] + $this->registros[$indice]['haber']);
                            $this->registros[$indice]['flujo'] = ($this->registros[$indice]['final'] - $this->registros[$indice]['inicial']);
                            $this->registros[$indice]['nivel'] = $valor['nivel'];
                        } else {
                            $this->registros[$indice]['inicial'] = '';
                            $this->registros[$indice]['debe'] = '';
                            $this->registros[$indice]['haber'] = '';
                            $this->registros[$indice]['final'] = '';
                            $this->registros[$indice]['flujo'] = '';
                            $this->registros[$indice]['nivel'] = '';
                        }
                    }

                    if ($valor['nivel'] >= 3) {
                        $this->registros[$indice]['inicial'] = (($valor['naturalezaCuenta'] != $this->naturalezaGenero) ? $this->registros[$indice]['inicial'] * -1 : $this->registros[$indice]['inicial']);
                        $this->registros[$indice]['final'] = (($valor['naturalezaCuenta'] != $this->naturalezaGenero) ? $this->registros[$indice]['final'] * -1 : $this->registros[$indice]['final']);
                        $this->registros[$indice]['flujo'] = ($this->registros[$indice]['final'] - $this->registros[$indice]['inicial']);
                        $this->registros[$indice]['nivel'] = $valor['nivel'];
                    }
                }
            }
        } else {
            $this->registros[0]['nombre'] = 'NO HAY REGISTROS';
            $this->registros[0]['inicial'] = '';
            $this->registros[0]['debe'] = '';
            $this->registros[0]['haber'] = '';
            $this->registros[0]['final'] = '';
            $this->registros[0]['flujo'] = '';
        }

        $this->nombreArchivoReporte = 'EdoAnaliticoActivo_';
        $this->contenido();
    }


    public function contenido()
    {
        $estado = [];

        foreach ($this->registros as $key => $value) {
            switch ($value['nombre']) {
                case 'ACTIVO':
                    $descripcion = $value['nombre'];
                    break;
                case 'ACTIVO NO CIRCULANTE':
                    $descripcion = 'Activo No Circulante';
                    break;
                default:
                    $descripcion = UrNombre::conceptosPlan($value['nombre']);
                    break;
            }
            array_push($estado, [ 'descripcion' => $value['nivel'] . '_' . $descripcion , 'inicial' => $value['inicial'], 'debe' => $value['debe'], 'haber' => $value['haber'], 'final' => $value['final'], 'flujo' => $value['flujo']]);
        }

        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4'            => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7, 'alineacionenc' => 'C'];

        $nombreColumnas = ['Descripción', 'Saldo Inicial', 'Cargos del Periodo', 'Abonos del Periodo', 'Saldo Final', 'Variación del Periodo'];
        $incluirColumnas = ['descripcion', 'inicial', 'debe', 'haber', 'final', 'flujo'];
        $tipoColumnas = ['cI', 'i', 'i', 'i', 'i', 'i'];
        $anchoColumnas = [
            0 => 90,
            1 => 20,
            2 => 15,
            3 => 15,
            4 => 20,
            5 => 15,
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

        $tablaimg[0]['colImg'] = '1';
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
$exp = new exp_Analitico();
