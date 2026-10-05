<?php
    /**
     * Description of Exp_CuentasMunicipios
     *
     * @author Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
     * Collaborators: 
     * Created: 10-Dic-2019
     * Comment: -----
     * Objective: Exportar datos de los municipios que tienen vinculado una cuenta, posteriormenete poner un separador 
                    para poner los municipios que aun no tengan nada vinculado y finalmente se visualizara las cuentas
                    que no estan vinculadas.
     * Regla de Negocio: -----
     * Permits: Esta pantalla no pregunta por permisos de escritura.
     * */                
session_start();
require_once dirname(__FILE__).'/../../../lib/generaPhpspreadsheet.php';
require_once dirname(__FILE__).'/../../../../lib/AccesoDatos.php';
require_once dirname(__FILE__).'/../../../../lib/tes/catalogo/consultaCuentasMunicipios.php';

class Exp_CuentasMunicipios extends generaPhpspreadsheet
{
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    protected $nombreArchivoReporte;
    protected $datos;
    protected $nombreColumnas;
    protected $table;
    protected $linea;
    protected $columna_maxima;
    protected $styleFormatCeldaCustom; 

    function __construct()
    {
        parent::__construct();
        $this->encabLin1 = 'GOBIERNO DEL ESTADO DE OAXACA';
        $this->encabLin2 = 'TESORERÍA '. APP_EJERCICIO;
        $this->encabLin3 = 'SECRETARÍA DE FINANZAS';
        $this->encabLin4 = 'REPORTE DE MUNICIPIOS';
        $this->encabLin5 = '';  
        $this->encabLin6 = '';
        $this->encabLin7 = '';
        $this->nombreArchivoReporte = 'ReporteMunicipios';
        $this->datos = consultaCuentasMunicipios(); 
        $this->cabecera();
        $this->contenido();
    }

    public function cabecera()
    {
        $this->alineacionenc = 'I';
        $this->nombreColumnas = array('CLAVE','MUNICIPIO', 'TIPO FONDO', 'ESTADO','CLABE','NÚMERO CUENTA','INSTITUCIÓN FINANCIERA');
        $incluirColumnas = array('ur_id','ur_nombre','gran_fuente','clase_gasto','tec_techoPresupuestal','pre_original','otro');
        $tipoColumnas = array("n","c", "c", "c","c","n","n");

        $this->table = array();
        foreach ($incluirColumnas as $key => $val) {
            $this->table['nombre'][$val] = $this->nombreColumnas[$key];
            $this->table['campo'][$val] = $key;
            $this->table['tipo'][$val] = $tipoColumnas[$key];
        }

        $colorenc = 'FFFFFF';
        $colorlin1 = 'FFFFFF';
        $colorlin2 = 'FFFFFF';
        $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE;

        $columna = 'A';
        $size_tableNombre = sizeof($this->table['nombre']);
        for ($i = 1; $i < $size_tableNombre; $i++) {
            $columna++;
        }

        $this->linea = 1;

        if (isset($this->encabLin1) && trim($this->encabLin1) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin1);
            $this->linea++;
        }
        if (isset($this->encabLin2) && trim($this->encabLin2) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin2);
            $this->linea++;
        }
        if (isset($this->encabLin3) && trim($this->encabLin3) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin3);
            $this->linea++;
        }
        if (isset($this->encabLin4) && trim($this->encabLin4) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin4);
            $this->linea++;
        }
        if (isset($this->encabLin5) && trim($this->encabLin5) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin5);
            $this->linea++;
        }
        if (isset($this->encabLin6) && trim($this->encabLin6) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin6);
            $this->linea++;
        }
        if (isset($this->encabLin7) && trim($this->encabLin7) != "") {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A'.$this->linea.':'.$columna.$this->linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A'.$this->linea, $this->encabLin7);
            $this->linea++;
        }

        $alineacionenc = \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER;
        if (isset($this->alineacionenc) && $this->alineacionenc == 'I') {
            $alineacionenc = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
        }
        if (isset($this->alineacionenc) && $this->alineacionenc == 'D') {
            $alineacionenc = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;
        }
        $styleArray = [
            'alignment' => [
                'horizontal' => $alineacionenc,
            ]
        ];

        $this->spreadsheet->getActiveSheet()->getStyle('A1:'.$columna.$this->linea)->applyFromArray($styleArray);

        $this->linea++;

        $columna = "A";
        $this->columna_maxima = "A";
        for ($i = 1; $i <= $size_tableNombre; $i++) {
            if($i == 1) {
                $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth(10);
            } else {
                $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setAutoSize(true);
            }
            ((sizeof($this->table['nombre']) == $i) ? '' : $columna++);
            ((sizeof($this->table['nombre']) == $i) ? '' : $this->columna_maxima++);
        }
        $this->spreadsheet->getActiveSheet()
            ->fromArray(
                $this->table['nombre'],
                NULL,
                'A'.$this->linea
            );
        $colorenc = '6FA6B7';
        $colorlin1 = ((isset($this->table['line1color']) && $this->table['line1color'] != "")
                ? $this->table['line1color'] : '91C5D4');
        $colorlin2 = ((isset($this->table['line2color']) && $this->table['line2color'] != "")
                ? $this->table['line2color'] : 'D5EAF0');

        $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;

        $this->colorEncabezado($colorenc, $estilorejilla);
        $this->colorFilas1($colorlin1, $estilorejilla);
        $this->colorFilas2($colorlin2, $estilorejilla);
        
        $this->spreadsheet->getActiveSheet()->getStyle('A'.$this->linea.':'.$columna.$this->linea)->applyFromArray($this->styleArray);
            
        $this->linea++;

        $name = 114;
        $path_image = dirname(__FILE__)."/../../../../img/Logo Institucional/".$name.".jpg";
        $tablaImagen = array("imagen"=>1);
        $tablaImagen[0] = array("ruta"=>$path_image,"nombre"=>"XXX","colImg"=>"D1","altura"=>100,"alingXimg"=>116);
        $this->ponimagen("XXX", $tablaImagen[0]["ruta"], "E1", 75, 80, 0);
    }

    public function contenido()
    {
        $separador1 = 'MUNICIPIOS QUE NO TIENEN VINCULACIÓN';
        $separador2 = 'CUENTAS BANCARIAS QUE NO TIENEN VINCULACIÓN';

        $ban_agrupadorB = true;
        $ban_agrupadorC = true;

        $columna = "A";
        $formatoCelda = new StdClass();
        $formatoCelda->{"formato"} = "formato_texto1";
        $formatoCelda->{"alineacion"} = "alineacion_izquierda";
        $color_cefra = true;
        foreach ($this->datos as $key=>$value) {
            $columna = "A";
            $this->formatCeldaCustom($formatoCelda);
            if ($this->datos[$key]['agrupador'] == 'B' && $ban_agrupadorB) {
                $columna_next = $columna;
                for ($i = 1; $i < 7 ; $i++) { 
                    $columna_next++;
                }
                $this->spreadsheet->setActiveSheetIndex(0)->mergeCells($columna.$this->linea.':'.$columna_next.$this->linea);
                $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$separador1);
                $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea.':'.$columna_next.$this->linea)->applyFromArray($this->styleArray);

                $this->linea++;
                
                $this->spreadsheet->getActiveSheet()
                    ->fromArray(
                        $this->table['nombre'],
                        NULL,
                        'A'.$this->linea
                );
                $this->spreadsheet->getActiveSheet()->getStyle('A'.$this->linea.':'.$columna_next.$this->linea)->applyFromArray($this->styleArray);
                $this->linea++;

                $ban_agrupadorB = false;
            }
            if ($this->datos[$key]['agrupador'] == 'C' && $ban_agrupadorC) {
                $columna_next = $columna;
                for ($i = 1; $i < 7 ; $i++) { 
                    $columna_next++;
                }
                $this->spreadsheet->setActiveSheetIndex(0)->mergeCells($columna.$this->linea.':'.$columna_next.$this->linea);
                $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$separador2);
                $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea.':'.$columna_next.$this->linea)->applyFromArray($this->styleArray);

                $this->linea++;
                
                $this->spreadsheet->getActiveSheet()
                    ->fromArray(
                        $this->table['nombre'],
                        NULL,
                        'A'.$this->linea
                    );
                $this->spreadsheet->getActiveSheet()->getStyle('A'.$this->linea.':'.$columna_next.$this->linea)->applyFromArray($this->styleArray);
                $this->linea++;

                $ban_agrupadorC = false;
            }
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['NUMERO_MUNICIPIO']);
            $formatoCelda->alineacion = "alineacion_izquierda";
            $this->formatCeldaCustom($formatoCelda);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['MUNICIPIO']);
            $formatoCelda->alineacion = "alineacion_justificar";
            $this->formatCeldaCustom($formatoCelda);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['TIPO_FONDO']);
            $formatoCelda->alineacion = "alineacion_izquierda";
            $this->formatCeldaCustom($formatoCelda);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['ESTATUS']);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,"'".$this->datos[$key]['CLABE']);
            $formatoCelda->alineacion = "alineacion_centro";
            $formatoCelda->formato = "formato_numero2";
            $this->formatCeldaCustom($formatoCelda);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['NO_CTA']);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->setCellValue($columna.$this->linea,$this->datos[$key]['INSTFIN']);
            $formatoCelda->alineacion = "alineacion_izquierda";
            $this->formatCeldaCustom($formatoCelda);
            $this->spreadsheet->getActiveSheet()->getStyle($columna.$this->linea)->applyFromArray($this->styleFormatCeldaCustom);
            $columna++;
            $this->spreadsheet->getActiveSheet()->getStyle('A'.$this->linea.':'.$this->columna_maxima.$this->linea)->applyFromArray((($color_cefra)?$this->styleArray1:$this->styleArray2));
            $color_cefra = ($color_cefra)?false:true;
            $this->linea++;
        }
        $this->output_web_browser($this->nombreArchivoReporte.".xlsx", false);
        $this->Terminar();
    }
    public function formatCeldaCustom($datos=false){
        $formato_numero1 = [
            'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
        ];
        $formato_numero2 = [
            'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER
        ];
        $formato_fechaYhora1 = [
            'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME,
        ];
        $formato_texto1 = [
            'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
        ];
        $alineacion_derecha = [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
        ];
        $alineacion_centro = [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ];
        $alineacion_izquierda = [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ];
        $alineacion_justificar=[
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_JUSTIFY,
        ];
        $this->styleFormatCeldaCustom = ['numberFormat' =>(isset($datos->formato) ? ${$datos->formato} : $formato_texto1),'alignment' =>(isset($datos->alineacion) ? ${$datos->alineacion} : $alineacion_centro)];
    }
}
$exp_ajustes = new Exp_CuentasMunicipios();