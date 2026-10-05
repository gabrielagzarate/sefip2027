<?php

/**
 * Description of Exp_PoaCierre
 *
 * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Collaborators:
 * Created: 31-Agt-2018
 * Comment: -----
 * Objective: Generar archivos Excel de acuerdo a la opcion requerida.
 * Regla de Negocio: -----
 * Permits: Esta pantalla no pregunta por permisos de escritura.
 * */
session_start();

require_once __DIR__ . '/../../../lib/generaPhpspreadsheet.php';
require_once __DIR__ . '/../../../../ent/UnidadResponsable.php';
require_once __DIR__ . '/../../../../ent/UnidadEjecutora.php';
require_once __DIR__ . '/../../../../lib/poa/cierre_poa/consultaPoaCierre.php';
require_once __DIR__ . '/../../../../lib/constantes.php';
class Exp_PoaCierre extends generaPhpspreadsheet
{
    public UnidadResponsable $ur;
    public $opc_id;
    public $nombreColumnas;
    public $datos;

    public function __construct()
    {
        parent::__construct();
        $ur_id = substr($_POST['ur_id'], 1, 2);

        // Obtener la informacion del usuario.
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
        $this->ur->setUr_id($ur_id);
        $this->ur->select();

        if (isset($_SESSION[_S]['clasAdm-ue'])) {
            $ue = new UnidadEjecutora();
            $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ue->setUe_id($_SESSION[_S]['clasAdm-ue']);
            $ue->select();
        } elseif (isset($_POST['ue']) && trim($_POST['ue']) != '') {
            $ue = new UnidadEjecutora();
            $ue->setGpo_id($_SESSION[_S]['clasAdm-gpo']);
            $ue->setUr_id($_SESSION[_S]['clasAdm-ur']);
            $ue->setUe_id(trim($_POST['ue']));
            $ue->select();
        } else {
            $ue = false;
        }

        $ue_id = ($ue ? $ue->getUe_id() : false);
        $this->opc_id = (isset($_POST['opc_id']) ? $_POST['opc_id'] : '');

        $reporte['tipo'] = ((isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != '') ? $_POST['tipoReporte'] : 1);
        $reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : '';
        $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : '';
        $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : '';

        $periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != '') ? $_POST['comboMesDesde'] : -1);
        $periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != '') ? $_POST['comboMesHasta'] : -1);

        $niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != '') ? $_POST['checkUR'] : 1);
        $niveles[2] = ($ue_id != '' ? $ue_id : -1);
        $niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != '') ? $_POST['checkEP'] : -1);
        $niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != '') ? $_POST['checkTG'] : -1);
        $niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != '') ? $_POST['checkIP'] : -1);
        $niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != '') ? $_POST['checkPG'] : -1);
        $niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != '') ? $_POST['checkPE'] : -1);
        $niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != '') ? $_POST['checkTR'] : -1);
        $niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != '') ? $_POST['checkCF'] : -1);

        $niveles[0] = ($niveles[9] > 0) ? 9 : (
            ($niveles[8] > 0) ? 7 : (
                ($niveles[7] > 0) ? 8 : (
                    ($niveles[6] > 0) ? 6 : (
                        ($niveles[5] > 0) ? 5 : (
                            ($niveles[4] > 0) ? 4 : (
                                ($niveles[3] > 0) ? 3 : (
                                    ($niveles[2] > 0) ? 2 : 1
                                )
                            )
                        )
                    )
                )
            )
        );

        $estatal = (isset($_POST['checkEstatal']) && $_POST['checkEstatal'] == 1) ? true : false;
        $federal = (isset($_POST['checkFederal']) && $_POST['checkFederal'] == 1) ? true : false;
        if ($niveles[9] > 0) {
            $claveFinanciamiento['ecf_id'] = isset($_POST['cveFin']) ? $_POST['cveFin'] : ''; // busqueda por clave de financiamiento
        } else {
            $claveFinanciamiento['ecf_id'] = '';
        }
        if ($niveles[8] > 0) {
            $claveFinanciamiento['ter_id'] = ($claveFinanciamiento['ecf_id'] != '') ? '' : (($federal && $estatal) ? '' : ($federal ? 'B' : ($estatal ? 'A' : '')));
        } else {
            $claveFinanciamiento['ter_id'] = '';
        }
        if ($niveles[3] > 0 && $reporte['tipo'] == 1) {
            $programatico['epg_id'] = isset($_POST['programatico']) ? $_POST['programatico'] : '';
        } else {
            $programatico['epg_id'] = '';
        }

        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'PROGRAMA OPERATIVO ANUAL ' . APP_EJERCICIO;

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($reporte['tipo']) {
                case 2:   // Todas las URs de la carga de trabajo
                    $this->encabLin3 = 'DIRECCIÓN DE PRESUPUESTO';
                    $this->encabLin6 = 'SECTOR ';
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $this->encabLin3 = 'DIRECCIÓN DE PRESUPUESTO';
                    if (isset($_POST['nombreEspecial']) && $_POST['nombreEspecial'] != '') {
                        $this->encabLin6 = trim($_POST['nombreEspecial']);
                    } else {
                        $this->encabLin6 = '';
                    }
                    break;
                default:
                    $this->encabLin3 = 'DIRECCIÓN DE PRESUPUESTO: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
                    $this->encabLin6 = '';
                    break;
            }
        } else {
            $this->encabLin3 = 'UR: ' . $this->ur->getClave() . ' - ' . $this->ur->getNombre();
            $this->encabLin6 = '';
        }

        if ($ue) {
            $this->encabLin4 = 'UE: ' . $ue->getUe_id() . ' - ' . $ue->getNombre();
        } else {
            $this->encabLin4 = '';
        }

        switch ($this->opc_id) {
            case 1:
                $this->encabLin5 = 'CIERRE POA';
                $this->datos = consultaPoaCierre($this->ur->getGpo_id(), $this->ur->getUr_id(), $this->opc_id);
                break;
            default:
                echo 'kym->reporte indefinida';
                break;
        }

        if ($programatico['epg_id'] != '') {
            $encProg = new enc_Programatico();
            $encProg->setEpg_id($programatico['epg_id']);
            $encProg->select();

            $this->encabLin6 = 'Obra/Actividad: ' . $encProg->getClave() . ' - ' . $encProg->getNombre();
        }

        if ($claveFinanciamiento['ecf_id'] != '') {
            $cvefin = new enc_ClaveFinanciamiento();
            $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
            $cvefin->select();

            $this->encabLin7 = 'Clave de Financiamiento: ' . $cvefin->getClave() . ' - ' . $cvefin->getNombre();
        } elseif ($federal && $estatal) {
            $this->encabLin7 = '';
        } elseif ($federal) {
            $this->encabLin7 = 'Recurso Federal';
        } elseif ($estatal) {
            $this->encabLin7 = 'Recurso Estatal';
        } else {
            $this->encabLin7 = '';
        }

        $this->contenido();
    }

    public function contenido()
    {
        $encabezado = [
            'encabLin1'     => $this->encabLin1,
            'encabLin2'     => $this->encabLin2,
            'encabLin3'     => $this->encabLin3,
            'encabLin4'     => $this->encabLin4,
            'encabLin5'     => $this->encabLin5,
            'encabLin6'     => $this->encabLin6,
            'encabLin7'     => $this->encabLin7,
            'alineacionenc' => 'I',
        ];
        if ($this->datos) {
            switch ($this->opc_id) {
                case 1:
                    foreach ($this->datos as $key => $value) {
                        if (!isset($this->datos[$key]['pre_original'])) {
                            $this->datos[$key]['pre_original'] = '00.00';
                        }
                    }
                    $this->nombreColumnas = ['UR', 'UR NOMBRE', 'GRAN FUENTE', 'CLASE DE GASTO', 'TECHO FINANCIERO', 'PRESUPUESTADO', 'DIFERENCIA'];
                    $incluirColumnas = ['ur_id', 'ur_nombre', 'gran_fuente', 'clase_gasto', 'tec_techoPresupuestal', 'pre_original', 'diferencia'];
                    $tipoColumnas = ['c', 'c', 'c', 'c', 'n', 'n', 'n'];
                    $anchoColumnas = [];
                    $table = [];
                    foreach ($incluirColumnas as $key => $val) {
                        $table['nombre'][$val] = $this->nombreColumnas[$key];
                        $table['ancho'][$val] = $anchoColumnas[$key];
                        $table['campo'][$val] = $key;
                        $table['tipo'][$val] = $tipoColumnas[$key];

                        $table['tipoTabla'] = 'A';
                        $table['ancho'][$val] = 20;
                    }

                    break;
                default:
                    break;
            }
        }
        $name = $this->ur->getClave();
        $path_image = dirname(__FILE__) . '/../../../../img/Logo Institucional/' . $this->ur->getClave() . '.png';

        $tablaImagen = ['imagen' => 1];
        $tablaImagen[0] = ['ruta' => $path_image, 'nombre' => 'XXX', 'colImg' => 'D1', 'altura' => 100, 'alingXimg' => 116];
        $this->generaExcel($this->datos, $encabezado, $table, ['nombre' => $this->encabLin5, 'tablaimg' => $tablaImagen]);
    }

    public function generaExcel(
        &$dato,
        $encabezado,
        $table = [],
        $otrosParametros = [],
        $comprimir = false,
        $guardar = []
    ) {

        $size_datos = sizeof($this->datos);
        $avance_fila = 9;
        $avance_columna = 'A';
        $presupuesto = $techo = $diferencia = 0;
        for ($i = 0; $i < $size_datos; $i++) {
            $avance_fila++;
            $presupuesto += $this->datos[$i]['pre_original'];
            $techo += $this->datos[$i]['tec_techoPresupuestal'];
            $diferencia += $this->datos[$i]['diferencia'];
        }
        foreach ($this->nombreColumnas as $value) {
            $avance_columna++;
        }
        $avance_columna--;

        $this->spreadsheet->getActiveSheet()->setCellValue('A' . $avance_fila, 'Total');
        $this->spreadsheet->getActiveSheet()->setCellValue('E' . $avance_fila, $techo);
        $this->spreadsheet->getActiveSheet()->setCellValue('F' . $avance_fila, $presupuesto);
        $this->spreadsheet->getActiveSheet()->setCellValue('G' . $avance_fila, $diferencia);

        $this->spreadsheet->getActiveSheet()->getStyle('E' . $avance_fila)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        $this->spreadsheet->getActiveSheet()->getStyle('F' . $avance_fila)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        $this->spreadsheet->getActiveSheet()->getStyle('G' . $avance_fila)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

        parent::generaExcel($this->datos, $encabezado, $table, $otrosParametros);
    }
}
$exp_ajustes = new Exp_PoaCierre();
