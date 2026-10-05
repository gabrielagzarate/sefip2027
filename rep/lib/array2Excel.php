<?php

    set_time_limit(0);

/**
 *PHP version 5
 *Puede recibir un array "puro", o bien un array que debe ser tratado como una lista ...
 *se define en el 3er. parametro
 * Le pasamos un array de datos y un array con el ancho de cada columna, y genera un archivo de excel simple
 * El array y las líneas de encabezado (hasta 3) son opcionales
 * Si no le pasamos el array con el ancho de cada columna simplemente deja todo con el ancho default de excel
 * El ancho que hay que ponerle es el mismo Valor que usaríamos si dentro de excel le damos Ancho - Columna - Valor
 *(no es exacta la conversión, sino aproximada :D  )
 * ----------------------------------------------------------------------------------------------------------
 *
 *@category  Controller
 *@package   Mypackage
 *@author    Fulanito <fulanito@finanzasoaxaca.gob.mx>
 *@copyright 2017 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 *@license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 *@link      https://egresos.finanzasoaxaca.gob.mx/sinpres2017/
*/

/**
 *EJEMPLO DEL ARRAY con especificacion de campos
 *Array(
    [nombre] => Array(
            [pg_id] => PARTIDA GENÉRICA
            [pg_nombre] => NOMBRE
            [pdx_reduccion] => REDUCCIÓN CONTABLE
            [diferenciaReduccion] => DIFERENCIA REDUCCIÓN
        )
    [ancho] => Array(
            [pg_id] => 5
            [pg_nombre] => 30
            [pdx_reduccion] => 15
            [diferenciaReduccion] => 15
        )
    [campo] => Array(
            [pg_id] => 0
            [pg_nombre] => 1
            [pdx_reduccion] => 2
            [diferenciaReduccion] => 6
        )
    [tipo] => Array(
            [pg_id] => c
            [pg_nombre] => c
            [pdx_reduccion] => n
            [diferenciaReduccion] => n
        )
 *)
*/

require_once dirname(__FILE__).'/../../lib/AccesoDatos.php';
require_once dirname(__FILE__).'/../../rep/lib/comprimir.php';

class array2Excel
{
    /**
     *GenerarExcel: funcion que genera el documento excel, de acuerdo a los datos recibidos
     *
     *@param array   $dato            datos para generar la tabla de excel
     *@param array   $encabezado      nombre del encabezado del excel a generar
     *@param boolean $lista           se declara la lista en false para el excel a generar
     *@param array   $table           array
     *@param array   $otrosParametros recibe parametros adicionales
     *@param formato_numero -> para los valores en cero imprime 0.00... sin embargo los Estados Financieros en 2024 los solicitan sin decimales
     *                         así que en éstos casos, permitimos pasar otro formato para los números para que se guarden y muestren correctamente
     *
     *@return boolean
    */
    protected $lineas;
    protected $campos;
    protected $ancho;
    protected $encabLin1;
    protected $encabLin2;
    protected $encabLin3;
    protected $encabLin4;
    protected $encabLin5;
    protected $encabLin6;
    protected $encabLin7;

    public function generaExcel(&$dato, $encabezado, $lista = false, $table = array(), $otrosParametros = array(),$nombreHoja="SEFIP", $formato_numero="#,##0.00", $color_tabla = "random")
    {

        $libro['sheetBook'] = isset($otrosParametros['book']) ? true:false;
        $libro['extension'] = ".xls";
        $libro['sistema'] = $nombreHoja;
        
        if (!$libro['sheetBook']) {
            $libro['nombre'] = $otrosParametros['nombre'];
            $libro['sufijoNombre'] = isset($otrosParametros['sufijoNombre']) ?
            $otrosParametros['sufijoNombre']:'';
            $libro['ruta'] = $libro['nombre'].$libro['sufijoNombre'];
            $libro['book'][$libro['sistema']] = $libro['sistema'];
        } else {
            foreach ($otrosParametros['book'] as $key => $val) {
                $libro['book'][$val] = $val;
            }
            $libro['nombre'] = $otrosParametros['nombre'];
            $libro['sufijoNombre'] = isset($otrosParametros['sufijoNombre']) ?
            $otrosParametros['sufijoNombre']:'';
            $libro['ruta'] = $libro['nombre'].$libro['sufijoNombre'];
        }
        
        $colores = [
            "verde" => [ "encabezado_fondo" => '70ad47', "encabezado_letra" => 'ffffff', "fila1" => 'e2efda', "fila2" => 'c6e0b4'], 
            "azul" => [ "encabezado_fondo" => '4472c4', "encabezado_letra" => 'ffffff', "fila1" => 'b4c6e7', "fila2" => 'd9e1f2'], 
            "amarillo" => [ "encabezado_fondo" => 'ffc000', "encabezado_letra" => 'ffffff', "fila1" => 'ffe699', "fila2" => 'fff2cc'], 
            "gris" => [ "encabezado_fondo" => 'a5a5a5', "encabezado_letra" => 'ffffff', "fila1" => 'dbdbdb', "fila2" => 'ededed'], 
            "naranja" => [ "encabezado_fondo" => 'ed7d31', "encabezado_letra" => 'ffffff', "fila1" => 'f8cbad', "fila2" => 'fce4d6'], 
            "sin_color" => [ "encabezado_fondo" => 'ffffff', "encabezado_letra" => '000000', "fila1" => 'ffffff', "fila2" => 'ffffff']
        ];

        $color_tabla = ( $color_tabla == "random" ? array_rand( $colores, 1 ) : $color_tabla );

        $color_fondo_encabezado = $colores[$color_tabla]['encabezado_fondo'];
        $color_texto_encabezado = $colores[$color_tabla]['encabezado_letra'];
        $color_fondo_fila1 = $colores[$color_tabla]['fila1'];
        $color_fondo_fila2 = $colores[$color_tabla]['fila2'];
        
        //peso en bytes del header = 2833
        $xls_header = '
        <?xml version="1.0" encoding="utf-8"?>
        <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" 
            xmlns:x="urn:schemas-microsoft-com:office:excel" 
            xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">
            <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
                <Title>'.$libro['nombre'].'</Title>
                <Author>'.$libro['sistema'].'</Author>
                <LastAuthor>IFC</LastAuthor>
                <Company>Secretaría de Finanzas. Gobierno del Estado de Oaxaca</Company>
            </DocumentProperties>
            <Styles>
                <Style ss:ID="hdr">
                        <Alignment ss:Horizontal="Left"/>
                        <Font ss:Color="#' . $color_texto_encabezado . '" ss:Bold="1"/>
                        <Interior ss:Color="#' . $color_fondo_encabezado . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="sF1">
                        <Alignment ss:Horizontal="Left"/>
                        <NumberFormat ss:Format="dd/mm/yyyy"/>
                        <Interior ss:Color="#' . $color_fondo_fila1 . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="sF2">
                        <Alignment ss:Horizontal="Left"/>
                        <NumberFormat ss:Format="dd/mm/yyyy"/>
                        <Interior ss:Color="#' . $color_fondo_fila2 . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="sN1">
                        <Alignment ss:Horizontal="Right"/>
                        <NumberFormat ss:Format="' . $formato_numero . '"/>
                        <Interior ss:Color="#' . $color_fondo_fila1 . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="sN2">
                        <Alignment ss:Horizontal="Right"/>
                        <NumberFormat ss:Format="' . $formato_numero . '"/>
                        <Interior ss:Color="#' . $color_fondo_fila2 . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="s1">
                        <Interior ss:Color="#' . $color_fondo_fila1 . '" ss:Pattern="Solid"/>
                </Style>
                <Style ss:ID="s2">
                        <Interior ss:Color="#' . $color_fondo_fila2 . '" ss:Pattern="Solid"/>   
                </Style>
                <Style ss:ID="sE">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
                </Style>
                <Style ss:ID="cP">
                        <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
                </Style>
                <Style ss:ID="eP">
                        <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
                        <Interior ss:Color="#6fa6b7" ss:Pattern="Solid"/>
                        <Borders>
                            <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
                            <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
                        </Borders>
                </Style>
            </Styles>
        ';
        
        $uno = true;
        $xls_hoja = '';
        $nombreHoja = $libro['sistema'];
        foreach ($libro['book'] as $key => $val) {
            if ($libro['sheetBook']) {
                if ($uno) {
                    $encabezados = $encabezado;
                    $tables = $table;
                    $datos = $dato;
                }
                $nombreHoja = isset( $otrosParametros[$key]['nombre'] ) ? $otrosParametros[$key]['nombre'] : $key;
                $encabezado = $encabezados[$key];
                $table = $tables[$key];
                $dato = $datos[$key];
                $uno = false;                
            }
            // Descomponemos el array de attributos ( qué básicamente son parámetros )
            // en cada una de sus partes o elementos
            // Los parametros del array atributos son:
            // encabLin1
            // encabLin2
            // encabLin3
            // anchoColumnas : array con el ancho de cada columna a generar
            foreach ($encabezado as $key => $val) {
                $this->$key = $val;
            }
            
            $this->lineas = count($dato);
            $this->campos = count($table['campo']);
            
            // Si pasaron el array con el ancho de cada columna ...
            // La relación entre lo que obtenemos dentro de excel usando
            // Formato-Columna-Poner el ancho y los valores que aquí
            // les pasemos es aproximadamente ese valor multiplicado por6 ...
            // por eso lo que nos pasen lo multiplicamos por 6
            // Si no pasan éste valor simplemente usa el ancho default de excel
            $strAnchoColumnas = "";
            if (isset($this->ancho)) {
                for ($i = 0; $i < count($this->ancho); $i++) {
                    $strAnchoColumnas .= '<ss:Column ss:Width="'.( $this->ancho[ $i ] * 6 ).'"/>';
                }
            }
            
            // Si pasaron Líneas de Encabezado DEL REPORTE, las dibujamos una a la vez
            $col_Encabezado = "";
            if (isset($this->encabLin1) && trim($this->encabLin1) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin1.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin2) && trim($this->encabLin2) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin2.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin3) && trim($this->encabLin3) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin3.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin4) && trim($this->encabLin4) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin4.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin5) && trim($this->encabLin5) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin5.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin6) && trim($this->encabLin6) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin6.'</Data></Cell></Row>';
            }
            if (isset($this->encabLin7) && trim($this->encabLin7) != "") {
                $col_Encabezado .= '<Row><Cell ss:MergeAcross="'.($this->campos - 1)
                .'" ss:StyleID="sE"><Data ss:Type="String">'.$this->encabLin7.'</Data></Cell></Row>';
            }
            $col_Encabezado .= '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>';

            //si tiene un encabezado principal
            $encabezadoPrincipal = "";
            if(isset($otrosParametros['encabezadoPrincipal'])){
                $encabezadoPrincipal .= '<Row>';
                foreach($otrosParametros['encabezadoPrincipal'] as $key => $value){
                        $encabezadoPrincipal .= '<Cell ss:MergeAcross="'.($value["dim"]- 1).'" ss:StyleID="eP"><Data ss:Type="String">'.$value[$key].'</Data></Cell>';
                }
                $encabezadoPrincipal .= '</Row>';
            }

            //si viene la variable de datosPoliza
            $datosPoliza = "";
            if(isset($otrosParametros['datosPoliza'])){
                $datosPolizaArray = $otrosParametros['datosPoliza'];
                $datosPoliza .= '<Row>';
                $datosPoliza .= '<Cell ss:MergeAcross="13" ss:StyleID="cP"><Data ss:Type="String">Concepto</Data></Cell>';
                $datosPoliza .= '<Cell ss:MergeAcross="1" ss:StyleID="sE"><Data ss:Type="String"></Data></Cell>';
                $datosPoliza .= '<Cell ss:MergeAcross="4" ss:StyleID="sE"><Data ss:Type="String">Datos de la póliza</Data></Cell>';
                $datosPoliza .= '</Row>';

                $datosPoliza .= '<Row>';
                $datosPoliza .= '<Cell ss:MergeAcross="13" ss:StyleID="cP"><Data ss:Type="String">'.$datosPolizaArray['pma_concepto'].'</Data></Cell>';
                $datosPoliza .= '<Cell ss:MergeAcross="1" ss:StyleID="sE"><Data ss:Type="String"></Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">Fecha</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">Elaboró</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">Póliza</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">MC</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">TMC</Data></Cell>';
                $datosPoliza .= '</Row>';

                $datosPoliza .= '<Row>';
                $datosPoliza .= '<Cell ss:MergeAcross="13" ss:StyleID="sE"><Data ss:Type="String"></Data></Cell>';
                $datosPoliza .= '<Cell ss:MergeAcross="1" ss:StyleID="sE"><Data ss:Type="String"></Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">'.substr($datosPolizaArray['pma_fecha'], 8, 2)."/".substr($datosPolizaArray['pma_fecha'],5, 2)."/".substr($datosPolizaArray['pma_fecha'], 0, 4).'</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">'.$datosPolizaArray['usr_usuario'].'</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">'.$datosPolizaArray['pot_id']."-".$datosPolizaArray['prf_id'].$datosPolizaArray['pma_id'].'</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">'.$datosPolizaArray['mmc_id'].'</Data></Cell>';
                $datosPoliza .= '<Cell ss:StyleID="sE"><Data ss:Type="String">'.$datosPolizaArray['tmc_id'].'</Data></Cell>';
                $datosPoliza .= '</Row>';
                $datosPoliza .= '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>';
            }

            // Si pasaron un array con nombres de columnas ... las ponemos
            if (count($table['nombre']) > 0) {
                $col_title = "";
                foreach ($table['nombre'] as $key => $value) {
                    $col_title .= '<Cell ss:StyleID="hdr"><Data ss:Type="String">'
                    .$table['nombre'][$key].'</Data></Cell>';
                }
                $col_title = '<Row>'.$col_title.'</Row>';
                // Si no ... lo dejamos vacío
            } else {
                // Obtenemos y dibujamos la línea del encabezado
                $col_title = "";
                for ($i = 0; $i < $this->campos; $i++) {
                    $col_title .= '<Cell ss:StyleID="hdr"><Data ss:Type="String">Columna '.$i.'</Data></Cell>';
                }
                $col_title = '<Row>'.$col_title.'</Row>';
            }
            
            // Variables donde controlaremos cada linea de datos ... y su color alternado
            $data = "";
            $nLin = 0;
            
            // ---------------------------------------------------------------------------------------
            // ---------------------------------------------------------------------------------------
            // Si el array está organizado por IDs ... debemos recorrerlo por sus respectivos valores
            // ---------------------------------------------------------------------------------------
            // ---------------------------------------------------------------------------------------
            if ($lista) {
                foreach ($dato as $key => $value) {
                    $line = '';
                    
                    // Para colorear intercaladamente las líneas ...
                    $nLin ++;
                    $i = 0;
                    $nStyle = (($nLin%3 == 1) ? '2' : '1');
                    
                    // Si pasaron un array indicando qué columnas quieren incluir en el archivo ...
                    if (count($table['campo']) > 0) {
                        //recorremos el array de los campos solicitados,
                        // este array debe estar en el orden de aparicion de los campos
                        foreach ($table['campo'] as $indice => $value) {
                            // Obtenemos el valor de la celda, y tratamos de escaparle todos los caracteres especiales
                            $value = str_replace('""', '"', $dato[$key][$indice]);
                            //Se agrego la validación del carácter '|''
                            $value = preg_replace(sprintf('~[^%s]++~i', '|0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $value);
                            $value = preg_replace('/\s\s+/', ' ', $value);
                            if ((!isset($value)) or ($value == "")) {
                                $value = '<Cell ss:StyleID="s' . $nStyle . '"><Data ss:Type="String"></Data></Cell>';
                            } else {
                                switch ($table['tipo'][$indice]) {
                                    case "n":
                                        $value = '<Cell ss:StyleID="sN'.$nStyle.'"><Data ss:Type="Number">'
                                        .$value.'</Data></Cell>';
                                        break;
                                    case "f":
                                        $value = '<Cell ss:StyleID="sF'.$nStyle.'"><Data ss:Type="DateTime">'
                                        .$value.'</Data></Cell>';
                                        break;
                                    default:   // Cadena por default
                                        $value = '<Cell ss:StyleID="s'.$nStyle.'"><Data ss:Type="String">'
                                        .$value.'</Data></Cell>';
                                        break;
                                }
                            }
                            $line .= $value;
                            $i++;
                            
                        }
                    }
                    
                    $data .= trim('<Row>'.$line.'</Row>').'\n';
                }
                
                // -------------------------------------------------------------------------------------
                // -------------------------------------------------------------------------------------
                // Si es un array plano ... le damos otro tratamiento
                // -------------------------------------------------------------------------------------
                // -------------------------------------------------------------------------------------
            } else {
                for ($l = 0; $l < $this->lineas; $l++) {
                    $line = '';
                    
                    // Para colorear intercaladamente las líneas ...
                    $nLin ++;
                    $nStyle = ( ($nLin%3 == 1) ? '2' : '1' );
                    
                    for ($i = 0; $i < $this->campos; $i++) {
                        $row = $dato[$l];
                        
                        // Obtenemos el valor de la celda, y tratamos de escaparle todos los caracteres especiales
                        //$value = mysql_real_escape_string( $row[$i] );
                        $value = $row[$i];
                        $value = str_replace('""', '"', $value);
                        $value = preg_replace(sprintf('~[^%s]++~i', '|0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $value);
                        $value = preg_replace('/\s\s+/', ' ', $value);
                        
                        if ((!isset($value)) || ($value == "")) {
                            $value = '<Cell ss:StyleID="s'.$nStyle.'"><Data ss:Type="String"></Data></Cell>';
                        } else {
                            switch ($this->getTipoCampo($i)) {
                                case "int":
                                case "real":
                                    $value = '<Cell ss:StyleID="sN'.$nStyle.'"><Data ss:Type="Number">'
                                    .$value.'</Data></Cell>';
                                    break;
                                case "date":
                                    $value = '<Cell ss:StyleID="sF'.$nStyle.'"><Data ss:Type="DateTime">'
                                    .$value.'</Data></Cell>';
                                    break;
                                default:   // Cadena por default
                                    $value = '<Cell ss:StyleID="s'.$nStyle.'"><Data ss:Type="String">'
                                    .$value.'</Data></Cell>';
                                    break;
                            }
                        }
                        $line .= $value;
                    }
                    
                    $data .= trim('<Row>'.$line.'</Row>').'\n';
                }
            }
            
            $xls_hoja .= '
                <Worksheet ss:Name="'.$nombreHoja.'">
                    <Table>'.$strAnchoColumnas.$col_Encabezado.$datosPoliza.$encabezadoPrincipal.$col_title.$data.'</Table>
                    <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
                        <Selected/>
                    </WorksheetOptions>
                </Worksheet>
            ';
        }
        $xls_footer = '</Workbook>';
        
        //creamos el archivo
        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Disposition: attachment; filename=\"".$libro['ruta'].$libro['extension']."\"");        
        header("Pragma: no-cache");
        header("Expires: 0");
        
        if (isset($otrosParametros['comprimir']) && $otrosParametros['comprimir']) {
            comprimir($xls_header.$xls_hoja.$xls_footer, $libro['ruta'], $libro['extension']);
        } else {
            print $xls_header.$xls_hoja.$xls_footer;
        }
        
        return false;
    }
}
