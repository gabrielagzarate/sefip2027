<?php

session_start();

require_once dirname(__FILE__) . '/../../lib/Phpspreadsheet.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../../ent/UnidadEjecutora.php';
require_once dirname(__FILE__) . '/../../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../../lib/mga/consultaMgaCatalogos.php';

class Exp_Asignacion_pg_ur extends Phpspreadsheet
{
    private $titulos_cabecera;
    private $size_cabecera;
    private $ur;
    private $opc_id;
    private $encabLin1;
    private $encabLin2;
    private $encabLin3;
    private $encabLin4;
    private $encabLin5;
    private $encabLin6;
    private $encabLin7;
    private $datos;
    private $t_filas;
    private $t_celdasAavanzar;
    private $columna_cuerpo;

    function __construct()
    {
        parent::__construct();
        $this->titulos_cabecera = array('PARTIDA', 'DESCRIPCIÓN', 'USADA');
        $this->size_cabecera = 0;
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
        $reporte['agrupador'] = isset($_POST['checkAgrupadoUr']) ? $_POST['checkAgrupadoUr'] : "";
        $reporte['grupos'] = isset($_POST['inputGrupos']) ? $_POST['inputGrupos'] : "";
        $reporte['excluir'] = isset($_POST['inputExcluir']) ? $_POST['inputExcluir'] : "";

        $periodo['mesDesde'] = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != "") ? $_POST['comboMesDesde'] : -1);
        $periodo['mesHasta'] = ((isset($_POST['comboMesHasta']) && trim($_POST['comboMesHasta']) != "") ? $_POST['comboMesHasta'] : -1);

        $niveles[1] = ((isset($_POST['checkUR']) && trim($_POST['checkUR']) != "") ? $_POST['checkUR'] : 1);
        $niveles[2] = ($ue_id != "" ? $ue_id : -1);
        $niveles[3] = ((isset($_POST['checkEP']) && trim($_POST['checkEP']) != "") ? $_POST['checkEP'] : -1);
        $niveles[4] = ((isset($_POST['checkTG']) && trim($_POST['checkTG']) != "") ? $_POST['checkTG'] : -1);
        $niveles[5] = ((isset($_POST['checkIP']) && trim($_POST['checkIP']) != "") ? $_POST['checkIP'] : -1);
        $niveles[6] = ((isset($_POST['checkPG']) && trim($_POST['checkPG']) != "") ? $_POST['checkPG'] : -1);
        $niveles[7] = ((isset($_POST['checkPE']) && trim($_POST['checkPE']) != "") ? $_POST['checkPE'] : -1);
        $niveles[8] = ((isset($_POST['checkTR']) && trim($_POST['checkTR']) != "") ? $_POST['checkTR'] : -1);
        $niveles[9] = ((isset($_POST['checkCF']) && trim($_POST['checkCF']) != "") ? $_POST['checkCF'] : -1);

        $niveles[0] = ($niveles[9] > 0) ? 9 : (($niveles[8] > 0) ? 7 : (($niveles[7] > 0) ? 8 : (($niveles[6] > 0) ? 6 : (($niveles[5] > 0) ? 5 : (($niveles[4] > 0) ? 4 : (($niveles[3] > 0) ? 3 : (($niveles[2] > 0) ? 2 : 1)))))));

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

        $this->encabLin1 = "GOBIERNO DEL ESTADO DE OAXACA";
        $this->encabLin2 = "PROGRAMA OPERATIVO ANUAL " . APP_EJERCICIO;

        if ($_SESSION[_S]['nvj_id'] > 2) {
            switch ($reporte['tipo']) {
                case 2:   // Todas las URs de la carga de trabajo
                    $this->encabLin3 = "DIRECCIÓN DE PRESUPUESTO";
                    $this->encabLin6 = "SECTOR ";
                    break;
                case 3:   // Determinar qué incluir y qué excluir
                    $this->encabLin3 = "DIRECCIÓN DE PRESUPUESTO";
                    if (isset($_POST['nombreEspecial']) && $_POST['nombreEspecial'] != '') {
                        $this->encabLin6 = trim($_POST['nombreEspecial']);
                    } else {
                        $this->encabLin6 = '';
                    }
                    break;
                default:
                    $this->encabLin3 = "DIRECCIÓN DE PRESUPUESTO: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
                    $this->encabLin6 = '';
                    break;
            }
        } else {
            $this->encabLin3 = "UR: " . $this->ur->getClave() . " - " . $this->ur->getNombre();
            $this->encabLin6 = '';
        }

        if ($ue) {
            $this->encabLin4 = "UE: " . $ue->getUe_id() . " - " . $ue->getNombre();
        } else {
            $this->encabLin4 = '';
        }

        switch ($this->opc_id) {
            case 2:
                $this->encabLin5 = 'PARTIDAS GENÉRICAS ASIGNADAS A LA UNIDAD RESPONSABLE';
                $this->datos = consultaMgaCatalogos($this->ur->getGpo_id(), $this->ur->getUr_id(), '', '', '', $this->opc_id);
                break;
            default:
                break;
        }

        if ($programatico['epg_id'] != '') {
            $encProg = new enc_Programatico();
            $encProg->setEpg_id($programatico['epg_id']);
            $encProg->select();
            $this->encabLin6 = "Obra/Actividad: " . $encProg->getClave() . " - " . $encProg->getNombre();
        }

        if ($claveFinanciamiento['ecf_id'] != '') {
            $cvefin = new enc_ClaveFinanciamiento();
            $cvefin->setEcf_id($claveFinanciamiento['ecf_id']);
            $cvefin->select();

            $this->encabLin7 = "Clave de Financiamiento: " . $cvefin->getClave() . " - " . $cvefin->getNombre();
        } elseif ($federal && $estatal) {
            $this->encabLin7 = '';
        } elseif ($federal) {
            $this->encabLin7 = "Recurso Federal";
        } elseif ($estatal) {
            $this->encabLin7 = "Recurso Estatal";
        } else {
            $this->encabLin7 = '';
        }

        // Determinamos la longitud mayor del campo descripcion
        // Determinamos cuantas filas son en total comenzando por la fila 7
        $this->t_filas = 7;
        $log_max_caracteres_descripcion = 0;
        $max_caracteresEnUnaCelda = 9;

        foreach ($this->datos as $value) {
            $log = strlen($value['descripcion']);
            if ($log > $log_max_caracteres_descripcion) {
                $log_max_caracteres_descripcion = $log;
                $this->t_celdasAavanzar = ceil($log_max_caracteres_descripcion / $max_caracteresEnUnaCelda);
            }

            $this->t_filas++;
        }

        $this->size_cabecera = 2;
        $this->columna_cuerpo = "B";

        for ($i = 1; $i < $this->t_celdasAavanzar; $i++) {
            $this->columna_cuerpo++;
            $this->size_cabecera++;
        }

        $this->cabecera();
        $this->contenido();
    }

    public function cabecera()
    {
        // Determinamos cual es la mitad de nuestro contenido en base al arreglo de las columnas de nuestro contenido.
        $mitad_cabecera = ceil($this->size_cabecera / 2);
        $columna = 'A';
        for ($i = 1; $i < $mitad_cabecera; $i++) {
            $columna++;
        }
        // Comenzamos a dibujar la cabecera
        // Combinar celda de A1 hasta la mitad que se determino en el ciclo for anterior. 
        for ($i = 1; $i <= 4; $i++) {
            $this->spreadsheet->setActiveSheetIndex(0)->mergeCells('A' . $i . ':' . $columna . $i);
        }
        //Establecemos la informacion a la celda combinada. 
        $this->spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', $this->encabLin1);
        $this->spreadsheet->setActiveSheetIndex(0)->setCellValue('A2', $this->encabLin2);
        $this->spreadsheet->setActiveSheetIndex(0)->setCellValue('A3', $this->encabLin3);
        $this->spreadsheet->setActiveSheetIndex(0)->setCellValue('A4', $this->encabLin5);

        $styleArray = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];
        $this->spreadsheet->getActiveSheet()->getStyle('A1:' . $columna . '4')->applyFromArray($styleArray);

        // Establecemos la imagen en la celda H1
        $name = ($this->opc_id) ? '114' : $this->ur->getClave();
        $columna++;
        $path_image = dirname(__FILE__) . "/../../../img/Logo Institucional/" . $name . ".jpg";

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath($path_image);
        $drawing->setHeight(85);
        $drawing->setCoordinates($columna . '1');
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());
    }

    public function contenido()
    {
        for ($i = 7; $i <= $this->t_filas; $i++) {
            $this->spreadsheet->setActiveSheetIndex(0)->mergeCells('B' . $i . ':' . $this->columna_cuerpo . $i);
        }
        $this->columna_cuerpo++;

        // Establecer los nombre de las columnas
        $this->spreadsheet->getActiveSheet()->setCellValue('A7', $this->titulos_cabecera[0]);
        $this->spreadsheet->getActiveSheet()->setCellValue('B7', $this->titulos_cabecera[1]);
        $this->spreadsheet->getActiveSheet()->setCellValue($this->columna_cuerpo . '7', $this->titulos_cabecera[2]);
        $styleArray = [
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => '91C5D4']
            ]
        ];
        // Aplicar estilos a las columnas de la cabecera, desde un arreglo.
        $this->spreadsheet->getActiveSheet()->getStyle('A7:' . $this->columna_cuerpo . '7')->applyFromArray($styleArray);

        // Establecer los datos del cuerpo.
        $ii = 0;
        for ($i = 8; $i <= $this->t_filas; $i++) {
            $this->spreadsheet->getActiveSheet()->setCellValue('A' . $i, $this->datos[$ii]['partida']);
            $this->spreadsheet->getActiveSheet()->setCellValue('B' . $i, $this->datos[$ii]['descripcion']);
            $this->spreadsheet->getActiveSheet()->setCellValue($this->columna_cuerpo . $i, $this->datos[$ii]['usada']);
            $ii++;
        }

        $styleArray = [
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'D5EAF0']
            ]
        ];

        $this->spreadsheet->getActiveSheet()->getStyle('A8:' . $this->columna_cuerpo . $this->t_filas)->applyFromArray($styleArray);
        $this->output_web_browser("Asignacion de partidas a una UR.xlsx");
    }
}
$exp_ajustes = new Exp_Asignacion_pg_ur();
