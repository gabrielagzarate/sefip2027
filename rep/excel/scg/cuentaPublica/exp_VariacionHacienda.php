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
require_once '../../../../lib/helpers.php';
require_once '../../../../lib/scg/UrNombre.php';
require_once '../../../../lib/scg/cuentaPublica/fnc_DameArrayVariacionHacienda.php';

class exp_VariacionHacienda extends generaPhpspreadsheet
{
    public string | null $encabLin1;
    public string | null $encabLin2;
    public string | null $encabLin3;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $datos;
    public $machote;
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
    public $cc;
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
        $this->encabLin3 = 'Estado de Variación en la Hacienda Pública';
        $this->encabLin6 = '';
        $tipoReporte = (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : '';
        $MilPesos = (isset($_POST['MilPes']) != '' ? 1000 : 1);
        $this->mesDesde = (isset($_POST['comboMesDesde']) && $_POST['comboMesDesde'] != '') ? $_POST['comboMesDesde'] : '';
        $this->mesHasta = (isset($_POST['comboMesHasta']) && $_POST['comboMesHasta'] != '') ? $_POST['comboMesHasta'] : '';
        $grupos = (isset($_POST['grupos']) && $_POST['grupos'] != '') ? $_POST['grupos'] : '';
        $excluir = (isset($_POST['excluir']) && $_POST['excluir'] != '') ? $_POST['excluir'] : '';
        $cc = isset($_POST['cc']) && $_POST['cc'] == 1 ? $_POST['cc'] : '';
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

        if (isset($_POST['MilPes'])) {
            $this->encabLin5 = '(Cifras en Miles de Pesos)';
        } else {
            $this->encabLin5 = '(Cifras en Pesos)';
        }


        $this->datos = get_machote_estado_variacion();

        rellena_variacion_hacienda($this->datos, $mesDesde, $mesHasta, $tipoReporte, $MilPesos, $grupos, $excluir, $ue, $incCta, $ceros);

        $this->nombreArchivoReporte = 'EdoVariacionHacienda_';
        $this->contenido();
    }


    public function contenido()
    {
        $estado = [];

        foreach ($this->datos as $indice => $valor) {

            $concepto = $valor['niv2'] != '' ? '3_' . $valor['niv2'] : '1_' . $valor['niv1'];

            array_push($estado, [ 'concepto' => $concepto, 'col1' => $valor['col1'], 'col2' => $valor['col2'], 'col3' => $valor['col3'], 'col4' => $valor['col4'], 'col5' => $valor['col5']]);
        }

        $encabezado = ['encabLin1' => $this->encabLin1, 'encabLin2' => $this->encabLin2, 'encabLin3' => $this->encabLin3,
            'encabLin4'            => $this->encabLin4, 'encabLin5' => $this->encabLin5, 'encabLin6' => $this->encabLin6,
            'encabLin7'            => $this->encabLin7, 'alineacionenc' => 'C'];

        $nombreColumnas = ['Concepto', 'Hacienda Pública/ Patrimonio Contribuído', 'Hacienda Pública/ Patrimonio Generado Ejercicios Anteriores', 'Hacienda Pública/ Patrimonio Generado del Ejercicio', 'Exceso o Insuficiencia en la Actualización de la Hacienda Pública/ Patrimonio', 'Total'];
        $incluirColumnas = ['concepto', 'col1', 'col2', 'col3', 'col4', 'col5'];
        $tipoColumnas = ['cI', 'i', 'i', 'i', 'i', 'i'];
        $anchoColumnas = [
            0 => 90,
            1 => 30,
            2 => 30,
            3 => 30,
            4 => 30,
            5 => 30,
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

        $tablaimg[0]['colImg'] = 'E1';
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
$exp = new exp_VariacionHacienda();
