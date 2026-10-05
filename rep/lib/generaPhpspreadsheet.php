<?php

set_time_limit(0);
/**
 * PHP version 5
 * Puede recibir un array "puro", o bien un array que debe ser tratado como una lista ...
 * se define en el 3er. parametro
 * Le pasamos un array de datos y un array con el ancho de cada columna, y genera un archivo de excel simple
 * El array y las líneas de encabezado (hasta 3) son opcionales
 * Si no le pasamos el array con el ancho de cada columna simplemente deja todo con el ancho default de excel
 * El ancho que hay que ponerle es el mismo Valor que usaríamos si dentro de excel le damos Ancho - Columna - Valor
 * (no es exacta la conversión, sino aproximada :D  (:________:) JCIHMARTINEZ
 * ----------------------------------------------------------------------------------------------------------
 *
 * @category  Controller
 * @package   Mypackage
 * @author    Fulanito <development@dgtid.oaxaca.gob.mx>
 * @copyright 2025 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 * @link      https://sefip.finanzasoaxaca.gob.mx/sefip2025/
 */
/**
 * EJEMPLO DEL ARRAY con especificacion de campos JCIH
 * Array(
 *     [nombre] => Array(
 *     [pg_id] => PARTIDA GENÉRICA
 *     [pg_nombre] => NOMBRE
 *     [pdx_reduccion] => REDUCCIÓN CONTABLE
 *     [diferenciaReduccion] => DIFERENCIA REDUCCIÓN
 *  )
 * ***********ALINEACION DEL ENCABEZADO
 * ejemplo
 * $encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
 *     'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
 *     'encabLin7' => $encabLin7,'alineacionenc'=>'D'); a la derecha  'alineacionenc'=>I´izquiera  'alineacionenc'=>'C  centro
 * ******* ancho de la Tabla************
 * POR defecto la tabla inicia como  $table['tipoTabla'] = 'A';
 * si se envia como  $table['tipoTabla'] = 'M' se puede enviar campos numericos , columna y combinaciones
 *      [ancho] => Array(
 *          [pg_id] => 5     --- numero de longitud
 *          [pg_nombre] =>  "A:A:20" -- si se envia por columna debera de ser en este formato el numero final es la longitud del campo A
 *          [pdx_reduccion] => "A:B:20" -- si se envia por columna debera de ser en este formato el numero final es la longitud del campo A (EMEPLO CONINACION DE COLUMNAS)
 *          [diferenciaReduccion] => 15 --- numero de longitud
 *      )
 *      [campo] => Array(
 *          [pg_id] => 0
 *          [pg_nombre] => 1
 *          [pdx_reduccion] => 2
 *          [diferenciaReduccion] => 6
 *      )
 *      [tipo] => Array(
 *          [pg_id] => c
 *          [pg_nombre] => c
 *          [pdx_reduccion] => n
 *          [diferenciaReduccion] => n
 *      )
 * *******cuando se manda llamar el color y se quire cambiar es de la siguiente forma*********
 *      $table['color'] = 1;
 *      $table['encabezadocolor'] = "BFB7B6";
 *      $table['line1color'] = "FFFFFF";
 *      $table['line2color'] = "FFFFFF";
 * ********* CAMPO ENCABEZADO ***** por defecto es S puede ser N
 *      EJEMPLO $table['encabezado'] = "S";
 *      $encabezado = array('encabLin1' => $encabLin1, 'encabLin2' => $encabLin2, 'encabLin3' => $encabLin3,
 *      'encabLin4' => $encabLin4, 'encabLin5' => $encabLin5, 'encabLin6' => $encabLin6,
 *      'encabLin7' => $encabLin7,'alineacionenc'=>"D"); ///alineacionenc ENCABEZADO ALA DERECHA EL VALOR SERA "I" para alineado ala izquierda pro defecto es Centrado
 * ************parametro imagen seenvia en parametros extra eejemplo *************
 *  'img' => "../../../../img/Logos Institucionales/101.jpg",'colImg' => "D1", 'alingXimg' => "470"
 *  'imgagua'=>"../../../../img/Logos Institucionales/101.jpg",'colImgagua'=>"A33",'alingXimgagua'=>"470","imgaguarota"=>-25,"imgaguaTamaño"=>300

 * ************************giuardar  un archivo  ejemplo de arreglo
 *  $guardar = array('guardar' => true, ------->enviar este parametro en true pra activar guardado
 * 'tipoguardado' => 2,------->  si es un tipo de guardado  2 se envian lo parametros modulo,claveunica(folder),modulo y claveUr
 * 'modulo' => "scg", ------->
 * 'claveunica' => "algo/algo",
 *  'claveUr' => 101,
 *  * 'tipoguardado' => 1,si el tipo de guardado es 1 solo se envia la ruta
 * 'ruta'=>/../../ejeplo/ejemplo);
 * *******************gUARDAR IMAGEN*******************
 * $tablaimg['imagen'] = 1;
 * $tablaimg[0]['nombre'] = "Logo";
 * $tablaimg[0]['ruta'] = "../../../../img/Logos Institucionales/".$ur->getClave().".jpg";
 * $tablaimg[0]['colImg'] = "D1";
 * $tablaimg[0]['altura'] = "50";
 * $tablaimg[0]['alingXimg'] = "470";
 * $tablaimg[0]['rotacion'] = "0";

 *
 * tipo de bode
 * BORDER_HAIR;
 * BORDER_DASHDOT
 * BORDER_MEDIUM
 * BORDER_THICK
 *
 *  */
require_once dirname(__FILE__) . '/../../lib/bootstrap_spreadsheet.php';
require_once dirname(__FILE__) . '/../../lib/FicheroSefip.php';
require_once dirname(__FILE__) . '/../../rep/lib/comprimir.php';
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';
require_once dirname(__FILE__) . '/../../lib/helpers.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

error_reporting(E_ERROR);
class generaPhpspreadsheet
{
    public array $styleArray;
    public array $styleArray1;
    public array $styleArray2;
    public array $styleArray3;
    public array $styleArrayNumero;
    public array $styleArrayFecha;
    public array $styleArrayTextoL;
    public array $styleArrayTextoR;
    public array $styleArrayTextoC;
    public array $stylEdoNivelCero;
    public array $stylEdoNivelUno;
    public array $stylEdoNivelDos;
    public array $stylEdoNivelTres;
    public array $styleFirmante;
    public array $styleArrayNumeroRedondeo;
    public array $styleEdoRedondeoUno;
    public array $styleEdoRedondeoDos;
    public string|null $encabLin1;
    public string|null $encabLin2;
    public string|null $encabLin3;
    public $ancho;
    public $alineacionenc;

    /**
     * GenerarExcel: funcion que genera el documento excel, de acuerdo a los datos recibidos
     *
     * @param array   $dato            datos para generar la tabla de excel
     * @param array   $encabezado      nombre del encabezado del excel a generar
     * @param boolean $lista           se declara la lista en false para el excel a generar
     * @param array   $table           array
     * @param array   $otrosParametros recibe parametros adicionales
     *
     * @return boolean
     */
    public $spreadsheet = null;
    public $encabLin4;
    public $encabLin5;
    public $encabLin6;
    public $encabLin7;
    public $encabLin8;
    public $encabLin9;
    public $encabLin10;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->styleArray = [];
        $this->styleArray1 = [];
        $this->styleArray2 = [];
        $this->styleArray3 = [];
        $this->styleArrayNumero = [];
        $this->styleArrayFecha = [];
        $this->styleArrayTextoL = [];
        $this->styleArrayTextoR = [];
        $this->styleArrayTextoC = [];
        $this->stylEdoNivelCero = [];
        $this->stylEdoNivelUno = [];
        $this->stylEdoNivelDos = [];
        $this->stylEdoNivelTres = [];
        $this->styleFirmante = [];
        $this->styleArrayNumeroRedondeo = [];
        $this->styleEdoRedondeoUno = [];
        $this->styleEdoRedondeoDos = [];
    }

    /**
     * GenerarExcel: funcion que genera el documento excel, de acuerdo a los datos recibidos
     *
     * @param array   $dato            datos para generar la tabla de excel
     * @param array   $encabezado      nombre del encabezado del excel a generar
     * @param boolean $lista           se declara la lista en false para el excel a generar
     * @param array   $table           array
     * @param array   $otrosParametros recibe parametros adicionales
     *
     * @return boolean
     */
    public function generaExcel(
        &$dato,
        $encabezado,
        $table = [],
        $otrosParametros = [],
        $comprimir = false,
        $guardar = []
    ) {

        $tablaextra = (isset($otrosParametros['tablaextra']) && $otrosParametros['tablaextra']
            != '' ? $otrosParametros['tablaextra'] : ['tablaextra' => 0]);
        $color = ((isset($otrosParametros['color'])) ? $otrosParametros['color']
            : 1);
        /* poner imagen normal */
        $Var_WrapText = false;
        /* Nombre reporte */
        $nombreRep = ((isset($otrosParametros['nombre']) && ($otrosParametros['nombre'])
            != '') ? $otrosParametros['nombre'] : '');

        $nombreHoja = ((isset($otrosParametros['nombrePagina']) && ($otrosParametros['nombrePagina'])
            != '') ? $otrosParametros['nombrePagina'] : 'SEFIP');

        $nombreRep .= ((isset($otrosParametros['sufijoNombre']) && ($otrosParametros['sufijoNombre'])
            != '') ? $otrosParametros['sufijoNombre'] : '');
        $color = ((isset($table['color']) && is_numeric($table['color'])) ? $table['color']
            : 1);
        $tablaextra = (isset($otrosParametros['tablaextra']) && $otrosParametros['tablaextra']
            != '' ? $otrosParametros['tablaextra'] : ['tablaextra' => 0]);

        $tablaimg = (isset($otrosParametros['tablaimg']) && $otrosParametros['tablaimg']
            != '' ? $otrosParametros['tablaimg'] : ['tablaextra' => 0]);
        $TextoCompleto = ((isset($table['textoCompleto'])) ? $table['textoCompleto']
            : true);
        if (is_bool($TextoCompleto) && $TextoCompleto == true) {
            $Var_WrapText = true;
        }
        $formatoTextoColumna = (isset($otrosParametros['formatoTexto']) && !empty($otrosParametros['formatoTexto'])) ? $otrosParametros['formatoTexto'] : array();

        foreach ($encabezado as $key => $val) {
            $this->$key = $val;
        }
        $tieneEncabezado = (isset($table['encabezado']) && $table['encabezado'] != ''
            ? $table['encabezado'] : 'S');

        $tipoTabla = (isset($table['tipoTabla']) && $table['tipoTabla'] != '' ? $table['tipoTabla']
            : 'A');
        $encabezadoTablaBorde = (isset($table['encabezadoTB']) && $table['encabezadoTB']
            != '' ? $table['encabezadoTB'] : 'N');
        $encabezadoColorBorde = (isset($table['encabezadoCB']) && $table['encabezadoCB']
            != '' ? $table['encabezadoCB'] : 'FFFFFF');
        $encabezadorBordeTipo = (isset($table['encabezadoBT']) && $table['encabezadoBT']
            != '' ? $table['encabezadoBT'] : \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        $colorenc = 'FFFFFF';
        $colorlin1 = 'FFFFFF';
        $colorlin2 = 'FFFFFF';
        $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE;

        $columna = 'A';
        for ($i = 1; $i < sizeof($table['nombre']); $i++) { //igual a 1 por que ya A tiene u valor
            $columna++;
        }
        $linea = 1;

        if (isset($this->encabLin1) && trim($this->encabLin1) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin1);
            $linea++;
        }

        if (isset($this->encabLin2) && trim($this->encabLin2) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin2);
            $linea++;
        }

        if (isset($this->encabLin3) && trim($this->encabLin3) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin3);
            $linea++;
        }

        if (isset($this->encabLin4) && trim($this->encabLin4) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin4);
            $linea++;
        }

        if (isset($this->encabLin5) && trim($this->encabLin5) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin5);
            $linea++;
        }

        if (isset($this->encabLin6) && trim($this->encabLin6) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin6);
            $linea++;
        }

        if (isset($this->encabLin7) && trim($this->encabLin7) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin7);
            $linea++;
        }

        if (isset($this->encabLin8)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin8);
            $linea++;
        }

        if (isset($this->encabLin9)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin9);
            $linea++;
        }

        if (isset($this->encabLin10)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin10);
            $linea++;
        }

                if ((isset($this->leyenda) && trim($this->leyenda) != "") &&
            (isset($this->lineaInicio) && trim($this->lineaInicio) != "") &&
            (isset($this->columna) && trim($this->columna) != "") &&
            (isset($this->lineaFin) && trim($this->lineaFin) != "")
        ) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $this->lineaInicio . ':' . $this->columna . $this->lineaFin);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $this->lineaInicio, $this->leyenda);
            $this->spreadsheet->getActiveSheet()
                ->getStyle('A' . $this->lineaInicio . ':' . $this->columna . $this->lineaFin)
                ->getAlignment()->setWrapText(true);
            $linea = $this->lineaFin;
            $linea++;
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
            ],
        ];

        $this->spreadsheet->getActiveSheet()->getStyle('A1:' . $columna . $linea)->applyFromArray($styleArray);

        if ($tablaimg['imagen'] == 1) {
            unset($tablaimg['imagen']);
            foreach ($tablaimg as $ind => $indval) {
                if (isset($indval['ruta']) && $indval['ruta'] != '') {
                    $this->ponimagen(
                        (isset($indval['nombre']) && ($indval['nombre'])
                            != '' ? $indval['nombre'] : 'logo'),
                        $indval['ruta'],
                        (isset($indval['colImg']) && ($indval['colImg'] != '') ? $indval['colImg']
                            : 'A1'),
                        (isset($indval['altura']) && is_numeric($indval['altura'])
                            ? $indval['altura'] : 100),
                        (isset($indval['alingXimg']) && is_numeric($indval['alingXimg'])
                            ? $indval['alingXimg'] : 0),
                        (isset($indval['rotacion']) && is_numeric($indval['rotacion'])
                            ? $indval['rotacion'] : 0)
                    );
                }
            }
        }

        if ($color == 1) {
            $colorenc = '6FA6B7';
            $colorenc = ((isset($table['encabezadocolor']) && $table['encabezadocolor']
                != '') ? $table['encabezadocolor'] : '6FA6B7');
            $colorlin1 = ((isset($table['line1color']) && $table['line1color'] != '')
                ? $table['line1color'] : '91C5D4');
            $colorlin2 = ((isset($table['line2color']) && $table['line2color'] != '')
                ? $table['line2color'] : 'D5EAF0');
            $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        }

        if ($encabezadoTablaBorde == 'S') {
            $this->colorEncabezadoBorde(
                $colorenc,
                $estilorejilla,
                $encabezadorBordeTipo,
                $encabezadoColorBorde
            );
        } else {
            $this->colorEncabezado($colorenc, $estilorejilla);
        }

        $this->colorFilas1($colorlin1, $estilorejilla);
        $this->colorFilas2($colorlin2, $estilorejilla);
        $limpia = [];
        $linea++; //pra no tomar encuenta el encabezado

        foreach ($dato as $key => $value) {
            // Si pasaron un array indicando qué columnas quieren incluir en el archivo ...
            if (count($table['campo']) > 0) {
                //recorremos el array de los campos solicitados,
                // este array debe estar en el orden de aparicion de los campos
                foreach ($table['campo'] as $indice => $value) {
                    // Obtenemos el valor de la celda, y tratamos de escaparle todos los caracteres especiales
                    $value = str_replace('""', '"', $dato[$key][$indice]);
                    if (mb_detect_encoding($value) == 'UTF-8') {
                        if (preg_replace('/[Ü]/u', 'U', utf8Decode($value)) == '') {
                            $value;
                        } else {
                            $value = utf8Encode($value);
                        }
                    } else {
                        $value = ($value);
                    }

                    $value = preg_replace('/[Ü]/u', 'U', $value);
                    $value = preg_replace('/[ü]/u', 'u', $value);

                    preg_replace(sprintf(
                        '~[^%s]++~i',
                        '|0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'
                    ), ' ', ($value));

                    $value = preg_replace('/\s\s+/', ' ', $value);
                    $limpia[$key][$indice] = $value;
                }
            }
        }

        $this->styleArrayNumero = [
            'numberFormat' => [
                'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];
        $this->styleArrayFecha = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];
        $this->styleArrayTextoL = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ];
        $this->styleArrayTextoC = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $this->styleArrayTextoR = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];
        // Formato NUMBER_FORMAT con Redondeo
        $this->styleArrayNumeroRedondeo = [
            'numberFormat' => [
                'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED_INTEGERS,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];
        $columna = 'A';
        $columnasNuevas = [];
        $linea = $linea + 2;
        $lineaInicia = (isset($table['lineaInicio']) && $table['lineaInicio'] != ''
            ? $table['lineaInicio'] : $linea);
        $linea = $lineaInicia;
        $t_filas = $linea;
        if ($tieneEncabezado != 'N') {
            $t_filas += count($dato);
        } else {
            $t_filas--;
            $t_filas += count($dato);
        }
        if (isset($table['ancho'])) {
            $tipoTabla = 'M';
        }
        if ($tipoTabla == 'M') {
            foreach ($table['ancho'] as $indice => $value) {
                $columnasNuevas[$indice] = $columna;
                if (is_numeric($value)) {
                    $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($value);
                } else {
                    $lineaindice = $linea;
                    $seParaColum = explode(':', $value);
                    for (; $lineaindice <= $t_filas; $lineaindice++) {
                        $this->spreadsheet->getActiveSheet()->mergeCells($seParaColum[0] . $lineaindice . ':' . $seParaColum[1] . $lineaindice);
                        $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($seParaColum[2]);
                    }
                    $columna = $seParaColum[1];
                }
                $columnasNuevas['ultimacolumna'] = $columna;
                $columna++;
            }
        } else {
            $columna = 'A';
            for ($i = 1; $i <= sizeof($table['nombre']); $i++) {
                $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setAutoSize(true);
                ((sizeof($table['nombre']) == $i) ? '' : $columna++);
            }
        }

        $lineaindice = $linea;
        if ($tipoTabla == 'M') {
            $columna = end($columnasNuevas);
        }

        for (; $lineaindice <= $t_filas; $lineaindice++) {
            if (($lineaindice % 2) == 0) {
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray1);
            } else {
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray2);
            }
        }

        $lineaindice = $linea;

        if ($tipoTabla == 'M') {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                foreach ($table['nombre'] as $indice => $value) {
                    $this->spreadsheet->getActiveSheet()->setCellValue(
                        $columnasNuevas[$indice] . $lineaindice,
                        $value
                    );
                    $this->spreadsheet->getActiveSheet()->getStyle($columnasNuevas[$indice] . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);
                    $columnafinalecabezado = $columnasNuevas['ultimacolumna'];
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnafinalecabezado . $lineaindice)->applyFromArray($this->styleArray);
                $linea++;
            }

            $imprimelinea = $linea;

            foreach ($limpia as $indice => $value) {
                foreach ($value as $key => $value2) {
                    if (!empty($formatoTextoColumna) && in_array($columnasNuevas[$key], $formatoTextoColumna, true)) {
                        $this->spreadsheet->getActiveSheet()->setCellValueExplicit(
                            $columnasNuevas[$key] . $imprimelinea,
                            (string) $value2,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                        );
                        
                        $this->spreadsheet->getActiveSheet()
                        ->getStyle($columnasNuevas[$key] . $imprimelinea)
                        ->getNumberFormat()
                        ->setFormatCode('@');
                    } else {
                        $this->spreadsheet->getActiveSheet()->setCellValue(
                            $columnasNuevas[$key] . $imprimelinea,
                            $value2
                        );
                    }

                    $this->spreadsheet->getActiveSheet()->getStyle($columnasNuevas[$key] . $imprimelinea)->getAlignment()->setWrapText($Var_WrapText);
                }
                $imprimelinea++;
            }
        } else {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                $this->spreadsheet->getActiveSheet()
                    ->fromArray(
                        $table['nombre'], // The data to set
                        null, // Array values with this value will not be set
                        'A' . $lineaindice // Top left coordinate of the worksheet range where
                        //    we want to set these values (default is A1)
                    );
                $columnaencbezado = 'A';
                for ($i = 1; $i < sizeof($table['nombre']); $i++) { //igual a 1 por que ya A tiene u valor
                    $columnaencbezado++;
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->applyFromArray($this->styleArray);
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);

                $linea++;
            }

            $this->spreadsheet->getActiveSheet()
                ->fromArray(
                    $limpia,
                    null,
                    'A' . $linea
                );

            if (!empty($formatoTextoColumna)) {
                foreach ($limpia as $indice => $value) {
                    $fila = $linea + $indice;
                    $keys = array_keys($value);
                    foreach ($formatoTextoColumna as $columna) {
                        $indiceColumna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columna) - 1;
                        if (array_key_exists($indiceColumna, $keys)) {
                            $campo = $keys[$indiceColumna];
                            $this->spreadsheet->getActiveSheet()->setCellValueExplicit(
                                $columna . $fila,
                                (string) $value[$campo],
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                            );
                        }
                    }
                }

                $ultimaFila = $linea + count($limpia) - 1;

                foreach ($formatoTextoColumna as $columna) {
                    $this->spreadsheet->getActiveSheet()->getStyle(
                        $columna . $linea . ':' . $columna . $ultimaFila
                    )
                    ->getNumberFormat()
                    ->setFormatCode('@');
                }
            }
        }

        $columna = 'A';

        foreach ($table['tipo'] as $indice => $value) {
            if ($tipoTabla == 'M') {
                $columna = $columnasNuevas[$indice];
            }

            switch ($value) {
                case 'n':
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayNumero);
                    break;
                case 'f':
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayFecha);
                    break;
                case 'cI': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoL);
                    break;
                case 'cD': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoR);
                    break;
                case 'cC': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoC);
                    break;
                case 'i': // Formato NUMBER_FORMAT con Redondeo
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayNumeroRedondeo);
                    break;
                default: // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoL);
                    break;
            }

            $columna++;
        }

        $t_filas++;
        $t_filas++;

        if ($tablaextra['tablaextra'] == 1) {
            unset($tablaextra['tablaextra']);
            foreach ($tablaextra as $ind => $indval) {
                $this->tabla2(
                    $indval['dato'],
                    $indval['table'],
                    $color,
                    $t_filas
                );
                $t_filas += count($indval['dato']);
                $t_filas++;
                $t_filas++;
            }
        }
        $this->spreadsheet->getActiveSheet()->setTitle($nombreHoja);

        if (isset($guardar['guardar']) && $guardar['guardar']) {
            if (isset($guardar['tipoguardado']) && $guardar['tipoguardado'] == 2) {
                if (isset($guardar['modulo'])) {
                    $this->guardarArchivoFueraServidor(
                        $guardar['modulo'],
                        (isset($guardar['claveunica']) ? $guardar['claveunica'] : ''),
                        (isset($guardar['claveUr']) ? $guardar['claveUr'] : ''),
                        "$nombreRep.xlsx",
                        (isset($guardar['$claveunica2']) ? $guardar['$claveunica2']
                            : ''),
                        $comprimir
                    );
                }
            } else {
                if (isset($guardar['ruta']) && $guardar['ruta'] != '') {
                    $this->guardarArchivoLocal(
                        $guardar['ruta'],
                        "$nombreRep.xlsx",
                        $comprimir
                    );
                }
            }
        }
        $this->output_web_browser("$nombreRep.xlsx", $comprimir);
        $this->Terminar();
    }

    public function tabla2($datos, $tabla2, $color, $linea)
    {
        $encabezadoTablaBorde = (isset($tabla2['encabezadoTB']) && $tabla2['encabezadoTB']
            != '' ? $tabla2['encabezadoTB'] : 'N');
        $encabezadoColorBorde = (isset($tabla2['encabezadoCB']) && $tabla2['encabezadoCB']
            != '' ? $tabla2['encabezadoCB'] : 'FFFFFF');
        $encabezadorBordeTipo = (isset($tabla2['encabezadoBT']) && $tabla2['encabezadoBT']
            != '' ? $tabla2['encabezadoBT'] : \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        $lineaInicia = (isset($tabla2['lineaInicio']) && $tabla2['lineaInicio'] != ''
            ? $tabla2['lineaInicio'] : $linea);
        $TextoCompleto = ((isset($tabla2['textoCompleto'])) ? $tabla2['textoCompleto']
            : true);
        $linea = $lineaInicia;
        $t_filas = $linea;

        if (is_bool($TextoCompleto) && $TextoCompleto == true) {
            $Var_WrapText = true;
        }
        $tieneEncabezado = (isset($tabla2['encabezado']) && $tabla2['encabezado']
            != '' ? $tabla2['encabezado'] : 'N');

        $tipoTabla = (isset($tabla2['tipoTabla']) && $tabla2['tipoTabla'] != '' ? $tabla2['tipoTabla']
            : 'A');

        if ($tieneEncabezado == 'S') {

            $t_filas += count($datos);
        } else {

            $t_filas += count($datos);
            $t_filas--;
        }

        $color = ((isset($tabla2['color']) && is_numeric($tabla2['color'])) ? $tabla2['color']
            : 1);
        $colorenc = 'FFFFFF';
        $colorlin1 = 'FFFFFF';
        $colorlin2 = 'FFFFFF';
        $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE;

        if ($color == 1) {
            $colorenc = ((isset($tabla2['encabezadocolor']) && $tabla2['encabezadocolor']
                != '') ? $tabla2['encabezadocolor'] : '6FA6B7');
            $colorlin1 = ((isset($tabla2['line1color']) && $tabla2['line1color']
                != '') ? $tabla2['line1color'] : 'D5EAF0');
            $colorlin2 = ((isset($tabla2['line2color']) && $tabla2['line2color']
                != '') ? $tabla2['line2color'] : '91C5D4');

            $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        }
        if ($encabezadoTablaBorde == 'S') {
            $this->colorEncabezadoBorde(
                $colorenc,
                $estilorejilla,
                $encabezadorBordeTipo,
                $encabezadoColorBorde
            );
        } else {
            $this->colorEncabezado($colorenc, $estilorejilla);
        }
        $this->colorFilas1($colorlin1, $estilorejilla);
        $this->colorFilas2($colorlin2, $estilorejilla);

        foreach ($datos as $key => $value) {
            // Si pasaron un array indicando qué columnas quieren incluir en el archivo ...
            if (count($tabla2['campo']) > 0) {
                //recorremos el array de los campos solicitados,
                // este array debe estar en el orden de aparicion de los campos
                foreach ($tabla2['campo'] as $indice => $value) {
                    // Obtenemos el valor de la celda, y tratamos de escaparle todos los caracteres especiales
                    $value = str_replace('""', '"', $datos[$key][$indice]);
                    //Se agrego la validación del carácter '|''
                    $value = preg_replace(sprintf(
                        '~[^%s]++~i',
                        '|0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'
                    ), ' ', $value);
                    $value = preg_replace('/\s\s+/', ' ', $value);
                    $limpia[$key][$indice] = $value;
                }
            }
        }

        $columna = 'A';
        $columnasNuevas = [];
        if (isset($tabla2['ancho'])) {
            $tipoTabla = 'M';
        }
        if ($tipoTabla == 'M') {
            foreach ($tabla2['ancho'] as $indice => $value) {
                $columnasNuevas[$indice] = $columna;
                if (is_numeric($value)) {
                    $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($value);
                } else {
                    $lineaindice = $linea;
                    $seParaColum = explode(':', $value);
                    for (; $lineaindice <= $t_filas; $lineaindice++) {
                        $this->spreadsheet->getActiveSheet()->mergeCells($seParaColum[0] . $lineaindice . ':' . $seParaColum[1] . $lineaindice);
                        $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($seParaColum[2]);
                    }
                    $columna = $seParaColum[1];
                }
                $columnasNuevas['ultimacolumna'] = $columna;
                $columna++;
            }
        } else {
            $columna = 'A';
            for ($i = 1; $i <= sizeof($tabla2['nombre']); $i++) {
                $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setAutoSize(true);
                ((sizeof($tabla2['nombre']) == $i) ? '' : $columna++);
            }
        }
        $lineaindice = $linea;

        if ($tipoTabla == 'M') {
            $columna = end($columnasNuevas);
        }
        for (; $lineaindice <= $t_filas; $lineaindice++) {
            if (($lineaindice % 2) == 0) {
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray1);
            } else {

                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray2);
            }
        }
        $lineaindice = $linea;

        if ($tipoTabla == 'M') {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                foreach ($tabla2['nombre'] as $indice => $value) {
                    $this->spreadsheet->getActiveSheet()->setCellValue(
                        $columnasNuevas[$indice] . $lineaindice,
                        $value
                    );
                    $this->spreadsheet->getActiveSheet()->getStyle($columnasNuevas[$indice] . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);
                    $columnafinalecabezado = $columnasNuevas['ultimacolumna'];
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnafinalecabezado . $lineaindice)->applyFromArray($this->styleArray);
                $linea++;
            }
            $imprimelinea = $linea;
            foreach ($limpia as $indice => $value) {
                foreach ($value as $key => $value2) {
                    $this->spreadsheet->getActiveSheet()->setCellValue(
                        $columnasNuevas[$key] . $imprimelinea,
                        $value2
                    );
                    $this->spreadsheet->getActiveSheet()->getStyle($columnasNuevas[$key] . $imprimelinea)->getAlignment()->setWrapText($Var_WrapText);
                }
                $imprimelinea++;
            }
        } else {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                $this->spreadsheet->getActiveSheet()
                    ->fromArray(
                        $tabla2['nombre'], // The data to set
                        null, // Array values with this value will not be set
                        'A' . $lineaindice // Top left coordinate of the worksheet range where
                        //    we want to set these values (default is A1)
                    );
                $columnaencbezado = 'A';
                for ($i = 1; $i < sizeof($tabla2['nombre']); $i++) { //igual a 1 por que ya A tiene u valor
                    $columnaencbezado++;
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->applyFromArray($this->styleArray);
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);
                $linea++;
            }
            $this->spreadsheet->getActiveSheet()
                ->fromArray(
                    $limpia,
                    null,
                    'A' . $linea
                );
        }

        $columna = 'A';

        foreach ($tabla2['tipo'] as $indice => $value) {
            if ($tipoTabla == 'M') {
                $columna = $columnasNuevas[$indice];
            }

            switch ($value) {
                case 'n':
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayNumero);
                    break;
                case 'f':
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayFecha);
                    break;
                case 'cI': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoL);
                    break;
                case 'cD': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoR);
                    break;
                case 'cC': // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoC);
                    break;
                default: // Cadena por default
                    $this->spreadsheet->getActiveSheet()->getStyle($columna . $linea . ':' . $columna . $t_filas)->applyFromArray($this->styleArrayTextoL);
                    break;
            }
            $columna++;
        }
    }

    public function generaEdoFinanciero(
        &$dato,
        $encabezado,
        $table = [],
        $otrosParametros = [],
        $comprimir = false,
        $guardar = []
    ) {

        $tablaextra = (isset($otrosParametros['tablaextra']) && $otrosParametros['tablaextra']
            != '' ? $otrosParametros['tablaextra'] : ['tablaextra' => 0]);
        $color = ((isset($otrosParametros['color'])) ? $otrosParametros['color']
            : 1);
        /* poner imagen normal */
        $Var_WrapText = false;
        /* Nombre reporte */
        $nombreRep = ((isset($otrosParametros['nombre']) && ($otrosParametros['nombre'])
            != '') ? $otrosParametros['nombre'] : '');

        $nombreHoja = ((isset($otrosParametros['nombrePagina']) && ($otrosParametros['nombrePagina'])
            != '') ? $otrosParametros['nombrePagina'] : 'SEFIP');

        $nombreRep .= ((isset($otrosParametros['sufijoNombre']) && ($otrosParametros['sufijoNombre'])
            != '') ? $otrosParametros['sufijoNombre'] : '');
        $color = ((isset($table['color']) && is_numeric($table['color'])) ? $table['color']
            : 1);
        $tablaextra = (isset($otrosParametros['tablaextra']) && $otrosParametros['tablaextra']
            != '' ? $otrosParametros['tablaextra'] : ['tablaextra' => 0]);

        $tablaimg = (isset($otrosParametros['tablaimg']) && $otrosParametros['tablaimg']
            != '' ? $otrosParametros['tablaimg'] : ['tablaextra' => 0]);

        $leyenda = (isset($otrosParametros['leyenda']) ? $otrosParametros['leyenda'] : '');
        $firmante1 = (isset($otrosParametros['firmante1']) ? $otrosParametros['firmante1'] : '');
        $firmante2 = (isset($otrosParametros['firmante2']) ? $otrosParametros['firmante2'] : '');
        $firmante3 = (isset($otrosParametros['firmante3']) ? $otrosParametros['firmante3'] : '');
        $cargo1 = (isset($otrosParametros['cargo1']) ? $otrosParametros['cargo1'] : '');
        $cargo2 = (isset($otrosParametros['cargo2']) ? $otrosParametros['cargo2'] : '');
        $cargo3 = (isset($otrosParametros['cargo3']) ? $otrosParametros['cargo3'] : '');

        $TextoCompleto = ((isset($table['textoCompleto'])) ? $table['textoCompleto']
            : true);
        if (is_bool($TextoCompleto) && $TextoCompleto == true) {
            $Var_WrapText = true;
        }

        foreach ($encabezado as $key => $val) {
            $this->$key = $val;
        }
        $tieneEncabezado = (isset($table['encabezado']) && $table['encabezado'] != ''
            ? $table['encabezado'] : 'S');

        $tipoTabla = (isset($table['tipoTabla']) && $table['tipoTabla'] != '' ? $table['tipoTabla']
            : 'A');
        $encabezadoTablaBorde = (isset($table['encabezadoTB']) && $table['encabezadoTB']
            != '' ? $table['encabezadoTB'] : 'N');
        $encabezadoColorBorde = (isset($table['encabezadoCB']) && $table['encabezadoCB']
            != '' ? $table['encabezadoCB'] : 'FFFFFF');
        $encabezadorBordeTipo = (isset($table['encabezadoBT']) && $table['encabezadoBT']
            != '' ? $table['encabezadoBT'] : \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

        $colorenc = 'FFFFFF';
        $colorlin1 = 'FFFFFF';
        $colorlin2 = 'FFFFFF';
        $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE;

        $columna = 'A';
        for ($i = 1; $i < sizeof($table['nombre']); $i++) { //igual a 1 por que ya A tiene u valor
            $columna++;
        }
        $linea = 1;

        if (isset($this->encabLin1) && trim($this->encabLin1) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin1);
            $linea++;
        }

        if (isset($this->encabLin2) && trim($this->encabLin2) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin2);
            $linea++;
        }

        if (isset($this->encabLin3) && trim($this->encabLin3) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin3);
            $linea++;
        }

        if (isset($this->encabLin4) && trim($this->encabLin4) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin4);
            $linea++;
        }

        if (isset($this->encabLin5) && trim($this->encabLin5) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin5);
            $linea++;
        }

        if (isset($this->encabLin6) && trim($this->encabLin6) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin6);
            $linea++;
        }

        if (isset($this->encabLin7) && trim($this->encabLin7) != '') {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin7);
            $linea++;
        }

        if (isset($this->encabLin8)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin8);
            $linea++;
        }

        if (isset($this->encabLin9)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin9);
            $linea++;
        }

        if (isset($this->encabLin10)) {
            $this->spreadsheet->setActiveSheetIndex(0)
                ->mergeCells('A' . $linea . ':' . $columna . $linea);
            $this->spreadsheet->setActiveSheetIndex(0)
                ->setCellValue('A' . $linea, $this->encabLin10);
            $linea++;
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
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        $this->spreadsheet->getActiveSheet()->getStyle('A1:' . $columna . $linea)->applyFromArray($styleArray);

        if ($tablaimg['imagen'] == 1) {
            unset($tablaimg['imagen']);
            foreach ($tablaimg as $ind => $indval) {
                if (isset($indval['ruta']) && $indval['ruta'] != '') {
                    $this->ponimagen(
                        (isset($indval['nombre']) && ($indval['nombre'])
                            != '' ? $indval['nombre'] : 'logo'),
                        $indval['ruta'],
                        (isset($indval['colImg']) && ($indval['colImg'] != '') ? $indval['colImg']
                            : 'A1'),
                        (isset($indval['altura']) && is_numeric($indval['altura'])
                            ? $indval['altura'] : 100),
                        (isset($indval['alingXimg']) && is_numeric($indval['alingXimg'])
                            ? $indval['alingXimg'] : 0),
                        (isset($indval['rotacion']) && is_numeric($indval['rotacion'])
                            ? $indval['rotacion'] : 0)
                    );
                }
            }
        }

        if ($color == 1) {
            $colorenc = '6FA6B7';
            $colorenc = ((isset($table['encabezadocolor']) && $table['encabezadocolor']
                != '') ? $table['encabezadocolor'] : '6FA6B7');
            $colorlin1 = ((isset($table['line1color']) && $table['line1color'] != '')
                ? $table['line1color'] : '91C5D4');
            $colorlin2 = ((isset($table['line2color']) && $table['line2color'] != '')
                ? $table['line2color'] : 'D5EAF0');
            $estilorejilla = \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID;
        }

        if ($encabezadoTablaBorde == 'S') {
            $this->colorEncabezadoBorde(
                $colorenc,
                $estilorejilla,
                $encabezadorBordeTipo
            );
        } else {
            $this->colorEdoFinanciero($colorenc, $estilorejilla);
        }

        $this->styleArray1 = [
            'fill' => [
                'fillType'   => $estilorejilla,
                'startColor' => [
                    'argb' => $colorlin1,
                ],
            ],
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
                'vertical' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
            ],
        ];

        $this->styleArray2 = [
            'fill' => [
                'fillType'   => $estilorejilla,
                'startColor' => [
                    'argb' => $colorlin1,
                ],
            ],
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
                'vertical' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
            ],
        ];


        $this->styleArray3 = [
            'fill' => [
                'fillType'   => $estilorejilla,
                'startColor' => [
                    'argb' => $colorlin1,
                ],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        $limpia = [];
        $linea++; //pra no tomar encuenta el encabezado

        foreach ($dato as $key => $value) {
            // Si pasaron un array indicando qué columnas quieren incluir en el archivo ...
            if (count($table['campo']) > 0) {
                //recorremos el array de los campos solicitados,
                // este array debe estar en el orden de aparicion de los campos
                foreach ($table['campo'] as $indice => $value) {
                    // Obtenemos el valor de la celda, y tratamos de escaparle todos los caracteres especiales
                    $value = str_replace('""', '"', $dato[$key][$indice]);
                    if (mb_detect_encoding($value) == 'UTF-8') {
                        if (preg_replace('/[Ü]/u', 'U', utf8Decode($value)) == '') {
                            $value;
                        } else {
                            $value = utf8Encode($value);
                        }
                    } else {
                        $value = ($value);
                    }

                    $value = preg_replace('/[Ü]/u', 'U', $value);
                    $value = preg_replace('/[ü]/u', 'u', $value);

                    preg_replace(sprintf(
                        '~[^%s]++~i',
                        '|0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'
                    ), ' ', ($value));

                    $value = preg_replace('/\s\s+/', ' ', $value);
                    $limpia[$key][$indice] = $value;
                }
            }
        }

        //FIRMANTES
        $this->styleFirmante = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'indent'     => 10, // Aumentar este número para más sangría
            ],
        ];
        //NIVEL 0 PLAN CE CUENTAS
        $this->stylEdoNivelCero = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'indent'     => 10, // Aumentar este número para más sangría
            ],
            'font' => [
                'bold' => true,
            ],
        ];
        //NIVEL 1 PLAN DE CUENTAS
        $this->stylEdoNivelUno = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent'     => 0, // Aumentar este número para más sangría
            ],
            'font' => [
                'bold' => true,
            ],
        ];
        //NIVEL 2 PLAN DE CUENTAS
        $this->stylEdoNivelDos = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent'     => 2, // Aumentar este número para más sangría
            ],
            'font' => [
                'bold' => true,
            ],
        ];
        //NIVEL 3 PLAN DE CUENTAS
        $this->stylEdoNivelTres = [
            'numberFormat' => [
                'code' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'indent'     => 3, // Aumentar este número para más sangría
            ],
        ];

        // Formato NUMBER_FORMAT con Redondeo
        $this->styleEdoRedondeoUno = [
            'numberFormat' => [
                'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED_INTEGERS,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
            'font' => [
                'bold' => true,
            ],
        ];

        // Formato NUMBER_FORMAT con Redondeo
        $this->styleEdoRedondeoDos = [
            'numberFormat' => [
                'formatCode' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED_INTEGERS,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ];

        $columna = 'A';
        $columnasNuevas = [];
        $linea = $linea + 2;
        $lineaInicia = (isset($table['lineaInicio']) && $table['lineaInicio'] != ''
            ? $table['lineaInicio'] : $linea);
        $linea = $lineaInicia;
        $t_filas = $linea;
        if ($tieneEncabezado != 'N') {
            $t_filas += count($dato);
        } else {
            $t_filas--;
            $t_filas += count($dato);
        }
        if (isset($table['ancho'])) {
            $tipoTabla = 'M';
        }
        if ($tipoTabla == 'M') {
            foreach ($table['ancho'] as $indice => $value) {
                $columnasNuevas[$indice] = $columna;
                if (is_numeric($value)) {
                    $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($value);
                } else {
                    $lineaindice = $linea;
                    $seParaColum = explode(':', $value);
                    for (; $lineaindice <= $t_filas; $lineaindice++) {
                        $this->spreadsheet->getActiveSheet()->mergeCells($seParaColum[0] . $lineaindice . ':' . $seParaColum[1] . $lineaindice);
                        $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setWidth($seParaColum[2]);
                    }
                    $columna = $seParaColum[1];
                }
                $columnasNuevas['ultimacolumna'] = $columna;
                $columna++;
            }
        } else {
            $columna = 'A';
            for ($i = 1; $i <= sizeof($table['nombre']); $i++) {
                $this->spreadsheet->getActiveSheet()->getColumnDimension($columna)->setAutoSize(true);
                ((sizeof($table['nombre']) == $i) ? '' : $columna++);
            }
        }

        $lineaindice = $linea;
        if ($tipoTabla == 'M') {
            $columna = end($columnasNuevas);
        }

        for (; $lineaindice <= $t_filas; $lineaindice++) {
            if (($lineaindice % 2) == 0) {
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray1);
            } else {
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columna . $lineaindice)->applyFromArray($this->styleArray2);
            }
        }

        $lineaindice = $linea;

        if ($tipoTabla == 'M') {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                foreach ($table['nombre'] as $indice => $value) {
                    $this->spreadsheet->getActiveSheet()->setCellValue(
                        $columnasNuevas[$indice] . $lineaindice,
                        $value
                    );
                    $this->spreadsheet->getActiveSheet()->getStyle($columnasNuevas[$indice] . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);
                    $columnafinalecabezado = $columnasNuevas['ultimacolumna'];
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnafinalecabezado . $lineaindice)->applyFromArray($this->styleArray);
                $linea++;
            }

            $imprimelinea = $linea;
            foreach ($limpia as $indice => $value) {
                foreach ($value as $key => $value2) {
                    $valor = explode('_', $value2);
                    if (str_contains($value2, '_')) {
                        $value2 = $valor[1];
                    }

                    $this->spreadsheet->getActiveSheet()->setCellValue(
                        $columnasNuevas[$key] . $imprimelinea,
                        $value2
                    );
                    switch ($valor[0]) {
                        case 9:   //PARA DEUDA PUBLICA
                            $columna = 'A';
                            foreach ($table['tipo'] as $i => $v) {
                                if ($tipoTabla == 'M') {
                                    $columna = $columnasNuevas[$i];
                                }

                                switch ($v) {
                                    case 'cI': // Cadena por default
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->stylEdoNivelCero);
                                        break;
                                    case 'i': // Formato NUMBER_FORMAT con Redondeo
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->styleEdoRedondeoUno);
                                        break;
                                }

                                $columna++;
                            }
                            break;
                        case 1:
                        case 5:
                            $columna = 'A';
                            foreach ($table['tipo'] as $i => $v) {
                                if ($tipoTabla == 'M') {
                                    $columna = $columnasNuevas[$i];
                                }
                                switch ($v) {
                                    case 'cI': // Cadena por default
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->stylEdoNivelUno);
                                        break;
                                    case 'i': // Formato NUMBER_FORMAT con Redondeo
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->styleEdoRedondeoUno);
                                        break;
                                }

                                $columna++;
                            }
                            break;
                        case 2:
                            $columna = 'A';
                            foreach ($table['tipo'] as $i => $v) {
                                if ($tipoTabla == 'M') {
                                    $columna = $columnasNuevas[$i];
                                }
                                switch ($v) {
                                    case 'cI': // Cadena por default
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->stylEdoNivelDos);
                                        break;
                                    case 'i': // Formato NUMBER_FORMAT con Redondeo
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->styleEdoRedondeoUno);
                                        break;
                                }

                                $columna++;
                            }
                            break;
                        case 3:
                        case 4:
                            $columna = 'A';
                            foreach ($table['tipo'] as $i => $v) {
                                if ($tipoTabla == 'M') {
                                    $columna = $columnasNuevas[$i];
                                }
                                switch ($v) {
                                    case 'cI': // Cadena por default
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->stylEdoNivelTres);
                                        break;
                                    case 'i': // Formato NUMBER_FORMAT con Redondeo
                                        $this->spreadsheet->getActiveSheet()->getStyle($columna . $imprimelinea . ':' . $columna . $imprimelinea)->applyFromArray($this->styleEdoRedondeoDos);
                                        break;
                                }

                                $columna++;
                            }
                            break;
                    }
                }
                $imprimelinea++;
            }

            // IMPRIMIR LA LEYENDA
            foreach ($table['nombre'] as $indice => $value) {
                $columnafinalecabezado = $columnasNuevas['ultimacolumna'];
            }

            if (!empty($leyenda)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    'A' . $imprimelinea,
                    'Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.'
                );
            }

            $this->spreadsheet->getActiveSheet()->getStyle('A' . $imprimelinea . ':' . $columnafinalecabezado . $imprimelinea)->applyFromArray($this->styleArray3);

            //FIRMAS
            if (!empty($cargo1)) {
                $colFirma1 = 'A';
                $colFirma2 = 'B';
                $colFirma3 = 'C';
            } else {
                $colFirma2 = 'A';
                $colFirma3 = 'B';
            }
            if (!empty($cargo1)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma1 . $imprimelinea + 3,
                    $cargo1
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma1 . $imprimelinea + 3)->applyFromArray($this->stylEdoNivelCero);
            }

            if (!empty($firmante1)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma1 . $imprimelinea + 8,
                    $firmante1
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma1 . $imprimelinea + 8)->applyFromArray($this->styleFirmante);
            }


            if (!empty($cargo2)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma2 . $imprimelinea + 3,
                    $cargo2
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma2 . $imprimelinea + 3)->applyFromArray($this->stylEdoNivelCero);
            }

            if (!empty($firmante2)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma2 . $imprimelinea + 8,
                    $firmante2
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma2 . $imprimelinea + 8)->applyFromArray($this->styleFirmante);
            }

            if (!empty($cargo3)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma3 . $imprimelinea + 3,
                    $cargo3
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma3 . $imprimelinea + 3)->applyFromArray($this->stylEdoNivelCero);
            }

            if (!empty($firmante3)) {
                $this->spreadsheet->getActiveSheet()->setCellValue(
                    $colFirma3 . $imprimelinea + 8,
                    $firmante3
                );
                $this->spreadsheet->getActiveSheet()->getStyle($colFirma3 . $imprimelinea + 8)->applyFromArray($this->styleFirmante);
            }


        } else {
            if ($tieneEncabezado == 'S') {
                // Si pasaron un array con nombres de columnas ... las ponemos
                $this->spreadsheet->getActiveSheet()
                    ->fromArray(
                        $table['nombre'], // The data to set
                        null, // Array values with this value will not be set
                        'A' . $lineaindice // Top left coordinate of the worksheet range where
                        //    we want to set these values (default is A1)
                    );
                $columnaencbezado = 'A';
                for ($i = 1; $i < sizeof($table['nombre']); $i++) { //igual a 1 por que ya A tiene u valor
                    $columnaencbezado++;
                }
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->applyFromArray($this->styleArray);
                $this->spreadsheet->getActiveSheet()->getStyle('A' . $lineaindice . ':' . $columnaencbezado . $lineaindice)->getAlignment()->setWrapText($Var_WrapText);

                $linea++;
            }

            $this->spreadsheet->getActiveSheet()
                ->fromArray(
                    $limpia,
                    null,
                    'A' . $linea
                );
        }

        $t_filas++;
        $t_filas++;

        if ($tablaextra['tablaextra'] == 1) {
            unset($tablaextra['tablaextra']);
            foreach ($tablaextra as $ind => $indval) {
                $this->tabla2(
                    $indval['dato'],
                    $indval['table'],
                    $color,
                    $t_filas
                );
                $t_filas += count($indval['dato']);
                $t_filas++;
                $t_filas++;
            }
        }
        $this->spreadsheet->getActiveSheet()->setTitle($nombreHoja);

        if (isset($guardar['guardar']) && $guardar['guardar']) {
            if (isset($guardar['tipoguardado']) && $guardar['tipoguardado'] == 2) {
                if (isset($guardar['modulo'])) {
                    $this->guardarArchivoFueraServidor(
                        $guardar['modulo'],
                        (isset($guardar['claveunica']) ? $guardar['claveunica'] : ''),
                        (isset($guardar['claveUr']) ? $guardar['claveUr'] : ''),
                        "$nombreRep.xlsx",
                        (isset($guardar['$claveunica2']) ? $guardar['$claveunica2']
                            : ''),
                        $comprimir
                    );
                }
            } else {
                if (isset($guardar['ruta']) && $guardar['ruta'] != '') {
                    $this->guardarArchivoLocal(
                        $guardar['ruta'],
                        "$nombreRep.xlsx",
                        $comprimir
                    );
                }
            }
        }
        $this->output_web_browser("$nombreRep.xlsx", $comprimir);
        $this->Terminar();
    }

    public function colorEncabezado($color, $tipo_rejilla)
    {
        $this->styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => $tipo_rejilla,
                'startColor' => [
                    'argb' => $color,
                ],
            ],
        ];
    }

    public function colorEdoFinanciero($color, $tipo_rejilla)
    {
        $this->styleArray = [
            'alignment' => [
                'vertical'   => PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType'   => $tipo_rejilla,
                'startColor' => [
                    'argb' => $color,
                ],
            ],
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => '0000000'],
                ],
            ],
        ];
    }

    public function colorEncabezadoBorde(
        $color,
        $tipo_rejilla,
        $tipoBorde
    ) {
        $this->styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => $tipo_rejilla,
                'startColor' => [
                    'argb' => $color,
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => $tipoBorde,
                    'color'       => ['argb' => '0000000'],
                ],
            ],
        ];
    }

    public function colorFilas1($color, $tipo_rejilla)
    {
        $this->styleArray1 = [
            'fill' => [
                'fillType'   => $tipo_rejilla,
                'startColor' => [
                    'argb' => $color,
                ],
            ],
        ];
    }

    public function colorFilas2($color, $tipo_rejilla)
    {
        $this->styleArray2 = [
            'fill' => [
                'fillType'   => $tipo_rejilla,
                'startColor' => [
                    'argb' => $color,
                ],
            ],
        ];
    }

    public function output_web_browser(
        $nombre_archivo = 'Predeterminado.xlsx',
        $comprimir = false,
        $extension = 'Xlsx'
    ) {
        if (!$comprimir) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
            header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header('Pragma: public'); // HTTP/1.0

            $writer = IOFactory::createWriter($this->spreadsheet, $extension);
            $writer->save('php://output');
        } else {
            $rutatem = dirname(__FILE__) . '/../../archivostemporalesPHPpreadsheet/';
            $exists = is_dir($rutatem);
            if (!$exists) {
                mkdir($rutatem, 0777, true);
            }
            $excel_file_tmp = tempnam($rutatem, 'spr');
            $writer = IOFactory::createWriter($this->spreadsheet, $extension);
            $writer->save($excel_file_tmp);
            comprimir(
                file_get_contents($excel_file_tmp),
                $nombre_archivo,
                '.xlsx'
            );
            unlink($excel_file_tmp);
        }
        $this->Terminar();
    }

    public function guardarArchivoLocal(
        $ruta,
        $nombre_archivo = 'Predeterminado.xlsx',
        $comprimir = false,
        $extension = 'Xlsx'
    ) {
        // Redirect output to a client’s web browser (Xlsx)
        $rutatem = dirname(__FILE__) . $ruta;
        $rutatem = str_replace('\\', '/', $rutatem);
        $exists = is_dir($rutatem);

        if (!$exists) {
            mkdir($rutatem, 0777, true);
        }
        $writer = IOFactory::createWriter($this->spreadsheet, $extension);
        if (!$comprimir) {
            $writer->save($rutatem . $nombre_archivo);
        } else {

            $descargar = false;
            $excel_file_tmp = tempnam($rutatem, 'spr');
            $writer = IOFactory::createWriter($this->spreadsheet, $extension);
            $writer->save($excel_file_tmp);
            $archivo = comprimir(
                file_get_contents($excel_file_tmp),
                $nombre_archivo,
                '.xlsx',
                '',
                $descargar
            );
            unlink($excel_file_tmp);
            $control = fopen($rutatem . $nombre_archivo . '.zip', 'w+');
            fwrite($control, $archivo->file()); // Grabas
            fclose($control);
        }
    }

    public function guardarArchivoFueraServidor(
        $modulo,
        $claveunica,
        $claveUr,
        $nombre_archivo = 'Predeterminado.xlsx',
        $claveunica2 = '',
        $comprimir = false,
        $extension = 'Xlsx'
    ) {
        // Redirect output to a client’s web browser (Xlsx)
        $rutatem = dirname(__FILE__) . '/../../archivostemporalesPHPpreadsheet/';
        $exists = is_dir($rutatem);
        if (!$exists) {
            mkdir($rutatem, 0777, true);
        }
        $writer = IOFactory::createWriter($this->spreadsheet, $extension);
        $rutatem = str_replace('\\', '/', $rutatem);

        if (!$comprimir) {
            $writer->save($rutatem . $nombre_archivo);
        } else {
            $descargar = false;
            $excel_file_tmp = tempnam($rutatem, 'spr');
            $writer = IOFactory::createWriter($this->spreadsheet, $extension);
            $writer->save($excel_file_tmp);
            $archivo = comprimir(
                file_get_contents($excel_file_tmp),
                $nombre_archivo,
                '.xlsx',
                '',
                $descargar
            );
            unlink($excel_file_tmp);
        }

        $ur_clave = $claveUr;
        if ($comprimir) {
            $name = $rutatem . $nombre_archivo . '.zip';
            $control = fopen($rutatem . $nombre_archivo . '.zip', 'w+');
            fwrite($control, $archivo->file()); // Grabas
            fclose($control);
            $tipos = ['zip'];
            $nombre_archivo .= '.zip';
        } else {
            $name = $rutatem . $nombre_archivo;
            $tipos = ['xlsx'];
        }

        $up = new FicheroSefip();
        $up->setTipos($tipos);
        $up->setModulo($modulo);
        $up->setfile("$nombre_archivo");
        $up->setRuta(($claveunica !== '' ? $claveunica . '/' : '') .
            ($claveunica2 !== '' ? $claveunica2 . '/' : ''));
        $up->setUrFichero($ur_clave);
        $up->setAnioFichero(APP_EJERCICIO);

        $file = ['name' => $name, 'tmp_name' => $name, 'eslocal' => true];

        if (is_file($name)) {
            $up->setFichero($file);
            if ($up->upload()) {
                unlink($name);
            }
        }
    }

    public function ponimagen(
        $nombre,
        $rutaimg,
        $colImg,
        $altura,
        $alingXimg,
        $rotacion
    ) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName($nombre);
        $drawing->setDescription($nombre);
        $drawing->setPath($rutaimg);
        $drawing->setHeight($altura);
        $drawing->setCoordinates($colImg);
        $drawing->setOffsetX($alingXimg);
        $drawing->setRotation($rotacion);
        $drawing->setWorksheet($this->spreadsheet->getActiveSheet());
    }

    public function Terminar()
    {
        exit;
    }
}
