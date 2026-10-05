<?php

// Le pasamos una instrucción SELECT y un array con el ancho de cada columna, y genera un archivo de excel simple
// El array y las líneas de encabezado (hasta 3) son opcioneales
// Si no le pasamos el array con el ancho de cada columna simplemente deja todo con el ancho default de excel
// El ancho que hay que ponerle es el mismo Valor que usaríamos si dentro de excel le damos Ancho - Columna - Valor
// (no es exacta la conversión, sino aproximada :D  )
// ------------------------------------------------------------------------------------------------------------------------------

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';

class query2Excel extends AccesoDatos
{
    protected $results;
    //atributos que mandan al definir el reporte
    protected $encabLin1;
    protected $encabLin2;
    protected $encabLin3;
    protected $anchoColumnas;
    protected $alineacionColumnas;
    public $params;

    public function generaExcel($sql, $attr)
    {
        foreach ($attr as $key => $val) {
            $this->$key = $val;
        }

        $this->ejecutarConsultaExcel($sql);
        $campos = $this->getNumeroCampos();
        $strAnchoColumnas = $this->xmlAnchoColumnas();
        $col_Encabezado = $this->xmlLineasEncabezado();
        [$col_title, $colAlign] = $this->xmlTitulosYAlineacion();
        $data = str_replace("\r", '', $this->xmlFilasDatos($campos, $colAlign));

        header('Content-Type: application/vnd.ms-excel;');
        header('Content-Disposition: attachment; filename=export.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        print $this->xmlEncabezadoLibro() . $strAnchoColumnas . $col_Encabezado . $col_title . $data . $this->xmlPieLibro();

        return false;
    }

    private function ejecutarConsultaExcel($sql): void
    {
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($this->params ?? []);
        $this->results = $this->getConexionActiva();
        $this->desconectar();
    }

    private function xmlAnchoColumnas(): string
    {
        $strAnchoColumnas = '';
        if (!isset($this->anchoColumnas)) {
            return $strAnchoColumnas;
        }
        for ($i = 0; $i < count($this->anchoColumnas); $i++) {
            $strAnchoColumnas .= '<ss:Column ss:Width="' . $this->anchoColumnas[$i] * 6 . '"/>';
        }

        return $strAnchoColumnas;
    }

    private function xmlLineasEncabezado(): string
    {
        $col_Encabezado = '';
        foreach (['encabLin1', 'encabLin2', 'encabLin3'] as $prop) {
            $texto = $this->$prop ?? '';
            if (trim($texto) !== '') {
                $col_Encabezado .= '<Row><Cell><Data ss:Type="String">' . $texto . '</Data></Cell></Row>';
            }
        }

        return $col_Encabezado . '<Row><Cell><Data ss:Type="String"></Data></Cell></Row>';
    }

    /** @return array{0: string, 1: array} */
    private function xmlTitulosYAlineacion(): array
    {
        $col_title = '';
        $colAlign = [];
        $i = 0;
        while ($finfo = $this->results->getResult()->getColumnMeta($i)) {
            $col_title .= '<Cell ss:StyleID="hdr"><Data ss:Type="String">' . $finfo['name'] . '</Data></Cell>';
            $colAlign[$i] = $this->tipoCeldaExcel($finfo['native_type']);
            $i++;
        }

        return ['<Row>' . $col_title . '</Row>', $colAlign];
    }

    private function tipoCeldaExcel(string $nativeType): string
    {
        return match ($nativeType) {
            'NEWDECIMAL' => 'int',
            'DATE' => 'date',
            default => 'string',
        };
    }

    private function xmlFilasDatos(int $campos, array $colAlign): string
    {
        $data = '';
        $nLin = 0;
        while ($row = $this->sacar()) {
            $line = '';
            $nLin++;
            $nStyle = (($nLin % 3 == 1) ? '2' : '1');
            for ($i = 0; $i < $campos; $i++) {
                $value = $row[$i];
                $value = str_replace('""', '"', $value);
                $value = preg_replace(sprintf('~[^%s]++~i', '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"”“\s\-/°()%'), ' ', $value);
                $value = preg_replace('/\s\s+/', ' ', $value);
                $line .= $this->xmlCelda($value, $nStyle, $colAlign[$i]);
            }
            $data .= trim('<Row>' . $line . '</Row>') . '\n';
        }

        return $data;
    }

    private function xmlCelda($value, string $nStyle, string $align): string
    {
        if (!isset($value) || $value == '') {
            return '<Cell ss:StyleID="s' . $nStyle . '"><Data ss:Type="String"></Data></Cell>';
        }

        return match ($align) {
            'int', 'real' => '<Cell ss:StyleID="sN' . $nStyle . '"><Data ss:Type="Number">' . $value . '</Data></Cell>',
            'date' => '<Cell ss:StyleID="sF' . $nStyle . '"><Data ss:Type="DateTime">' . $value . '</Data></Cell>',
            default => '<Cell ss:StyleID="s' . $nStyle . '"><Data ss:Type="String">' . $value . '</Data></Cell>',
        };
    }

    private function xmlEncabezadoLibro(): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>' .
        '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' .
        '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' .
        '<Author>sefip' . APP_EJERCICIO . '</Author>' .
        '<LastAuthor></LastAuthor>' .
        '<Company>Secretaría de Finanzas.   Gobierno del Estado de Oaxaca</Company>' .
        '</DocumentProperties>' .
        '<Styles>' .
            '<Style ss:ID="hdr">' .
                '<Alignment ss:Horizontal="Left"/>' .
                '<Font ss:Bold="1"/>' .
                '<Interior ss:Color="#91c5d4" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="sF1">' .
                '<Alignment ss:Horizontal="Left"/>' .
                '<NumberFormat ss:Format="dd/mm/yyyy"/>' .
                '<Interior ss:Color="#d5eaf0" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="sF2">' .
                '<Alignment ss:Horizontal="Left"/>' .
                '<NumberFormat ss:Format="dd/mm/yyyy"/>' .
                '<Interior ss:Color="#bcd9e1" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="sN1">' .
                '<Alignment ss:Horizontal="Right"/>' .
                '<NumberFormat ss:Format="#,##0"/>' .
                '<Interior ss:Color="#d5eaf0" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="sN2">' .
                '<Alignment ss:Horizontal="Right"/>' .
                '<NumberFormat ss:Format="#,##0"/>' .
                '<Interior ss:Color="#bcd9e1" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="s1">' .
                '<Interior ss:Color="#d5eaf0" ss:Pattern="Solid"/>' .
            '</Style>' .
            '<Style ss:ID="s2">' .
                '<Interior ss:Color="#bcd9e1" ss:Pattern="Solid"/>' .
            '</Style>			' .
        '</Styles>' .
        '<Worksheet ss:Name="sefip' . APP_EJERCICIO . '">' .
        '<Table>';
    }

    private function xmlPieLibro(): string
    {
        return '</Table>' .
        '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">' .
        '<Selected/>' .
        '</WorksheetOptions>' .
        '</Worksheet>' .
        '</Workbook>';
    }

}
