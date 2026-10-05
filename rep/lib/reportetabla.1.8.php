<?php
if (file_exists('../../fnt/UniviaPro-Bold.php')) {
    define('FPDF_FONTPATH', '../../fnt/');
} else if (file_exists('../../fnt/UniviaPro-Bold.php')) {
    define('FPDF_FONTPATH', '../../fnt/');
} else if (file_exists('../../../fnt/UniviaPro-Bold.php')) {
    define('FPDF_FONTPATH', '../../../fnt/');
} else if (file_exists('../../../fnt/calibrib.php')) {
    define('FPDF_FONTPATH', '../../../fnt/');
} else {
    define('FPDF_FONTPATH', '../fnt/');
}

require_once dirname(__FILE__)."/../../lib/constantes.php";
require_once('fpdf.1.84.php');
require_once('RC4.php');

class PDF extends FPDF
{
    var $widths;
    var $aligns;
    var $borders;
    var $arrayBorders;
    var $fuentes;
    var $altolinea;
    var $altoline;
    var $intercelda;
    var $espaciado = 1.7;
    var $relleno;
    var $topalign;
    var $angle = 0;
    var $yearReporte = APP_EJERCICIO;
    var $encrypted = true;  //whether document is protected
    var $Uvalue;             //U entry in pdf document
    var $Ovalue;             //O entry in pdf document
    var $Pvalue;             //P entry in pdf document
    var $enc_obj_id;         //encryption object id
    var $padding;
    var $encryption_key;
    var $routeImg;

    /* funciones de proteccion  */
    function SetProtection($permissions = array(), $user_pass = '', $owner_pass = null)
    {
        $options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32);
        $protection = 192;
        foreach ($permissions as $permission) {
            if (!isset($options[$permission]))
                $this->Error('Incorrect permission: ' . $permission);
            $protection += $options[$permission];
        }
        if ($owner_pass === null)
            $owner_pass = uniqid(rand());
        $this->encrypted = true;
        $this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08" .
            "\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
        $this->_generateencryptionkey($user_pass, $owner_pass, $protection);
    }
    /****************************************************************************
     *                                                                           *
     *                              Private methods                              *
     *                                                                           *
     ****************************************************************************/
    function _putstream($s)
    {
        if ($this->encrypted)
            $s = RC4($this->_objectkey($this->n), $s);
        parent::_putstream($s);
    }

    function _textstring($s)
    {
        if (!$this->_isascii($s))
            $s = $this->_UTF8toUTF16($s);
        if ($this->encrypted)
            $s = RC4($this->_objectkey($this->n), $s);
        return '(' . $this->_escape($s) . ')';
    }
    /**
     * Compute key depending on object number where the encrypted data is stored
     */
    function _objectkey($n)
    {
        return substr($this->_md5_16($this->encryption_key . pack('VXxx', $n)), 0, 10);
    }

    function _putresources()
    {
        parent::_putresources();
        if ($this->encrypted) {
            $this->_newobj();
            $this->enc_obj_id = $this->n;
            $this->_put('<<');
            $this->_putencryption();
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    function _putencryption()
    {
        $this->_put('/Filter /Standard');
        $this->_put('/V 1');
        $this->_put('/R 2');
        $this->_put('/O (' . $this->_escape($this->Ovalue) . ')');
        $this->_put('/U (' . $this->_escape($this->Uvalue) . ')');
        $this->_put('/P ' . $this->Pvalue);
    }

    function _puttrailer()
    {
        parent::_puttrailer();
        if ($this->encrypted) {
            $this->_put('/Encrypt ' . $this->enc_obj_id . ' 0 R');
            $this->_put('/ID [()()]');
        }
    }
    /**
     * Get MD5 as binary string
     */
    function _md5_16($string)
    {
        return pack('H*', md5($string));
    }
    /**
     * Compute O value
     */
    function _Ovalue($user_pass, $owner_pass)
    {
        $tmp = $this->_md5_16($owner_pass);
        $owner_RC4_key = substr($tmp, 0, 5);
        return RC4($owner_RC4_key, $user_pass);
    }
    /**
     * Compute U value
     */
    function _Uvalue()
    {
        return RC4($this->encryption_key, $this->padding);
    }
    /**
     * Compute encryption key
     */
    function _generateencryptionkey($user_pass, $owner_pass, $protection)
    {
        // Pad passwords
        $user_pass = substr($user_pass . $this->padding, 0, 32);
        $owner_pass = substr($owner_pass . $this->padding, 0, 32);
        // Compute O value
        $this->Ovalue = $this->_Ovalue($user_pass, $owner_pass);
        // Compute encyption key
        $tmp = $this->_md5_16($user_pass . $this->Ovalue . chr($protection) . "\xFF\xFF\xFF");
        $this->encryption_key = substr($tmp, 0, 5);
        // Compute U value
        $this->Uvalue = $this->_Uvalue();
        // Compute P value
        $this->Pvalue = - (($protection ^ 255) + 1);
    }
    /** FIN FUNCIONES DE PROTECCION  */
    function SetYear($year)
    {
        $this->yearReporte = $year;
    }

    function GetYear($type)
    {
        if ($type == 'y')
            return $this->yearReporte;
        else
            return $this->yearReporte = date("d/m/") . $this->yearReporte;
    }
    //Setter images name
    function setRouteImg($name = '')
    {
        if (trim($name) !== "") {
            $this->routeImg  = __DIR__ . "/../../img/Logo Institucional/" . $name . ".jpg";
        } else {
            $this->routeImg  = __DIR__ . "/../../img/".LOGO_COLOR;
        }
    }

    function setRouteImg2($name = '')
    {
        if (trim($name) !== "") {
            $this->routeImg  = __DIR__ . "/../../img/" . $name . ".jpg";
        } else {
            $this->routeImg  = __DIR__ . "/../../img/".LOGO_INSTITUCIONAL;
        }
    }

    function getRouteImg()
    {
        return $this->routeImg;
    }

    function SetTopAlign($f = false)
    {
        $this->topalign = $f;
    }

    function SetRelleno($f)
    {
        $this->relleno = $f;
    }

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths = $w;
    }

    function SetArrayBorders($w)
    {
        //Set the array of column widths
        $this->arrayBorders = $w;
    }

    function SetBorders($b)
    {
        //Set the border
        if ($b == 1)
            $this->borders = 'LTBR';
        else
            $this->borders = $b;
    }

    function GetBorders()
    {
        //Get the border
        return $this->borders;
    }

    function SetAltoLinea($alto)
    {
        //Set the line
        if ($alto == '')
            $this->altolinea = 2.5;
        else
            $this->altolinea = $alto;
    }

    function GetAltoLinea()
    {
        //Get the line
        return $this->altolinea;
    }

    function setEspaciado($e)
    {
        $this->espaciado = $e;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns = $a;
    }

    function setFuentes($a)
    {
        $this->fuentes = $a;
    }

    function Row($data, $border = '')
    {
        //CALCULAR EL ALTO DE LAS CELDAS QUE SE DIBUJAN
        for ($i = 0; $i < count($data); $i++) {
            $data[$i] = str_replace(chr(9), chr(32), $data[$i] ?? '');
        }

        $nb = 0;
        $cellNb = array();
        $altoline = $this->GetAltoLinea();
        $intercelda = 1;
        for ($i = 0; $i < count($data); $i++) { //
            if (!empty($this->fuentes && isset($this->fuentes[$i]))) {
                $this->SetFont($this->fuentes[$i]->font, $this->fuentes[$i]->style, $this->fuentes[$i]->size);
            }
            if (isset($this->widths[$i]) && isset($data[$i])) {
                $enebe = $this->NbLines($this->widths[$i], trim($data[$i]));
                $data[$i] = trim($data[$i]);
                array_push($cellNb, $enebe);
            }
        }
        // ALTURA MAXIMA EXPRESADA EN LINEAS  // THE MAX HEIGTH EXPRESSED ON LINES
        $nb = max($cellNb);

        if ($this->topalign == true) {
            for ($i = 0; $i < count($data); $i++) {
                $cellNb[$i] = $nb; //IGUALAMOS TODOS LOS ALTOS PARA QUE SE VEAN PAREJAS LAS CELDAS CONTIGUAS  // ESTABLISH ALL HEIGHTS AS MAXIMUM IS, JUST FOR THE CELLS SHOWS IT AS EQUAL
                $rest = $nb - $this->NbLines($this->widths[$i], $data[$i]); //CALCULAMOS CUANTAS LINEAS MENOS TIENE CADA CELDA QUE NO SEA LA MAS GRANDE  // CALCULATE THE NUMBER OF MISSING LINES BUT THE MAX
                $nk = '';
                for ($h = 1; $h <= $rest; $h++) {
                    //AGREGAMOS SALTOS DE LINEA SIMPLES PARA IGUALAR EL NUMERO DE LINEAS /// ADD THE SIMPLE LINES FOR ALL CELLS BE EQUAL
                    $nk .= '
                        ';
                }
                ///AGREGAMOS LAS LINEAS QUE LE FALTAN A CADA CELDA PARA LLEGAR AL MAXIMO  // ADD THE LINES TO EACH CELL TO REACH THE MAXIMUN HEIGHT OF THE ARRAY
                if (trim($data[$i]) <> "") {
                    $data[$i] = trim($data[$i]) . $nk;
                } else {
                    $data[$i] = $data[$i] . $nk;
                }
            }
        }
        $nb = max($cellNb);

        $h = ($altoline * $nb) + $this->espaciado;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);

        //Draw the cells of the row
        $w = $a = $x = $y = '';
        $bordeLR = $border == 'LR';
        for ($i = 0; $i < count($data); $i++) {
            $page = $this->PageNo();
            $borders = 0;
            if ($bordeLR) {
                if ($i == 0)
                    $borders = 'L';
                elseif ($i == count($data) - 1)
                    $borders = 'R';
            } else if (!empty($this->arrayBorders) && isset($this->arrayBorders[$i])) {
                $borders = $this->arrayBorders[$i];
            } else {
                $borders = $this->borders;
            }
            if (isset($this->widths[$i])) {
            $w = $this->widths[$i];
            }
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            //Print the text

            if (isset($cellNb[$i]) && $cellNb[$i] != 0) {
            $altura = (($h - $this->espaciado) / $cellNb[$i]) + ($this->espaciado / $cellNb[$i]); //
            }

            if (!empty($this->fuentes && isset($this->fuentes[$i]))) {
                $this->SetFont($this->fuentes[$i]->font, $this->fuentes[$i]->style, $this->fuentes[$i]->size);
            }

            if ($this->relleno == "si") {
                $this->MultiCell($w, $altura, $data[$i], $borders, $a, true);
            } else {
                $this->MultiCell($w, $altura, $data[$i], $borders, $a);
            }
            $this->page = $page;
            //Put the position to the right of the cell
            $this->SetXY($x + (float)$w, $y);
        }
        $this->fuentes =  NULL;
        //Go to the next line
        $this->Ln($h);
    }

    // Helper: divide un texto en las líneas que MultiCell produciría (respetando ancho y fuente actual)
private function splitTextToLines($w, $text)
{
    // Normalizar newlines
    $text = str_replace("\r", '', $text);
    $s = $text;
    $cw = $this->CurrentFont['cw'];
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $lines = array();

    $nb = strlen($s);
    $i = 0; $j = 0; $l = 0; $sep = -1;
    while ($i < $nb) {
        $c = $s[$i];
        if ($c == "\n") {
            $lines[] = substr($s, $j, $i - $j);
            $i++;
            $j = $i;
            $l = 0;
            $sep = -1;
            continue;
        }
        // si char no definido en cw, tomar ancho de espacio como fallback
        $charWidth = isset($cw[$c]) ? $cw[$c] : $cw[' '];
        $l += $charWidth;
        if ($l > $wmax) {
            if ($sep == -1) {
                // no hay separador: cortar en la posición actual
                if ($i == $j) {
                    // un caracter es mayor al ancho (raro) -> forzar avance
                    $i++;
                }
                $lines[] = substr($s, $j, $i - $j);
                $j = $i;
            } else {
                // cortar en el último espacio
                $lines[] = substr($s, $j, $sep - $j);
                $i = $sep + 1;
                $j = $i;
            }
            $l = 0;
            $sep = -1;
        } else {
            if ($c == ' ') $sep = $i;
            $i++;
        }
    }
    // resto
    if ($j < $nb) {
        $lines[] = substr($s, $j, $i - $j);
    } else if ($nb == 0) {
        $lines[] = '';
    }

    // trim final de cada linea (opcional)
    foreach ($lines as &$ln) $ln = rtrim($ln);
    return $lines;
}

// Función principal: Row que imprime en chunks respetando altura uniforme y flujo entre páginas
function Row2($data, $border = '', $paddingVertical = 1.5)
{
    // Reemplazar tabs
    for ($i = 0; $i < count($data); $i++) {
        $data[$i] = str_replace(chr(9), chr(32), $data[$i]);
    }

    $altoline = $this->GetAltoLinea(); // altura por línea
    $cols = count($data);

    // Preparar: dividir cada columna en líneas reales según la fuente/anchos actuales
    $allLines = array();
    $totalLines = array();
    for ($i = 0; $i < $cols; $i++) {
        // aplicar fuente por celda si existe
        if (!empty($this->fuentes && isset($this->fuentes[$i]))) {
            $this->SetFont($this->fuentes[$i]->font, $this->fuentes[$i]->style, $this->fuentes[$i]->size);
        }
        $w = $this->widths[$i];
        $lines = $this->splitTextToLines($w, trim($data[$i]));
        $allLines[$i] = $lines;
        $totalLines[$i] = count($lines);
    }

    // índices de progreso por columna (qué línea hemos impreso)
    $pos = array_fill(0, $cols, 0);

    // mientras haya texto por imprimir en alguna columna
    while (true) {
        $more = false;
        foreach ($pos as $p => $v) if ($v < $totalLines[$p]) { $more = true; break; }
        if (!$more) break;

        // espacio restante en página
        $yInicio = $this->GetY();
        $espacioRestante = $this->h - $this->bMargin - $yInicio;

        // cuantas lineas caben en esta página (respetando padding)
        $linesFit = floor(($espacioRestante - 2 * $paddingVertical) / $altoline);
        if ($linesFit < 1) {
            // no cabe ni una línea -> nueva página
            $this->AddPage($this->CurOrientation);
            $yInicio = $this->GetY();
            $espacioRestante = $this->h - $this->bMargin - $yInicio;
            $linesFit = floor(($espacioRestante - 2 * $paddingVertical) / $altoline);
            if ($linesFit < 1) $linesFit = 1; // forzar al menos 1
        }

        // determinar cuántas líneas imprimiremos por columna en este chunk
        $linesToPrint = array();
        for ($i = 0; $i < $cols; $i++) {
            $remaining = $totalLines[$i] - $pos[$i];
            $linesToPrint[$i] = min($remaining, $linesFit);
        }

        // para mantener altura uniforme, usamos el máximo de linesToPrint
        $chunkLines = max($linesToPrint);
        if ($chunkLines < 1) $chunkLines = 1;

        // preparar textos del chunk (por columna), uniendo líneas con "\n"
        $chunkTexts = array();
        for ($i = 0; $i < $cols; $i++) {
            $take = min($chunkLines, $totalLines[$i] - $pos[$i]);
            if ($take > 0) {
                $slice = array_slice($allLines[$i], $pos[$i], $take);
                $chunkTexts[$i] = implode("\n", $slice);
            } else {
                $chunkTexts[$i] = '';
            }
        }

        // Altura del chunk (uniforme): chunkLines * altoline + 2*padding
        $hChunk = ($chunkLines * $altoline) + (2 * $paddingVertical);

        // Si no cabe por alguna razón (pequeño margen), forzar nueva página
        if ($yInicio + $hChunk > $this->h - $this->bMargin) {
            $this->AddPage($this->CurOrientation);
            $yInicio = $this->GetY();
        }

        // --- Imprimir el chunk: dibujar celdas con borde y texto ---
        $xStart = $this->GetX();
        $yStart = $this->GetY();

        for ($i = 0; $i < $cols; $i++) {
            $x = $this->GetX();
            $y = $this->GetY();
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $fill = ($this->relleno == "si");

            // decidir borde por celda (manteniendo compatibilidad con arrayBorders / LR / default)
            if ($border == 'LR') {
                if ($i == 0) $borders = 'L';
                elseif ($i == $cols - 1) $borders = 'R';
                else $borders = '';
            } elseif (!empty($this->arrayBorders) && isset($this->arrayBorders[$i])) {
                $borders = $this->arrayBorders[$i];
            } else {
                $borders = $this->borders;
            }

            // dibujar rectángulo (borde) del tamaño del chunk
            $this->Rect($x, $y, $w, $hChunk);

            // aplicar fuente por celda si corresponde (importante por si son diferentes)
            if (!empty($this->fuentes && isset($this->fuentes[$i]))) {
                $this->SetFont($this->fuentes[$i]->font, $this->fuentes[$i]->style, $this->fuentes[$i]->size);
            }

            // escribir el texto del chunk con padding superior
            $this->SetXY($x, $y + $paddingVertical);
            if ($fill) {
                $this->MultiCell($w, $altoline, $chunkTexts[$i], 0, $a, true);
            } else {
                $this->MultiCell($w, $altoline, $chunkTexts[$i], 0, $a);
            }

            // volver a la línea de inicio para siguiente columna
            $this->SetXY($x + $w, $yStart);
        }

        // avanzar posiciones de líneas impresas
        for ($i = 0; $i < $cols; $i++) {
            $pos[$i] += min($chunkLines, $totalLines[$i] - ($pos[$i]));
        }

        // finalmente, mover cursor al final del chunk (para la siguiente iteración/fila)
        $this->SetY($yStart + $hChunk);
    }

    // limpiar fuentes temporales
    $this->fuentes = NULL;
}


    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            return true;
        }
    }

    function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ((float)$w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234')
    {

        /*
            x, y: top left corner of the rectangle.
            w, h: width and height.
            r: radius of the rounded corners.
            style: same as Rect(): F, D (default), FD or DF.
            angle: numbers of the corners to be rounded: 1, 2, 3, 4 or any combination (1=top left, 2=top right, 3=bottom right, 4=bottom left).                
        */
        $k = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' or $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m', ($x + $r) * $k, ($hp - $y) * $k)); //

        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - $y) * $k)); //
        if (strpos($angle, '2') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $y) * $k));
        else
            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);

        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '3') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x + $w) * $k, ($hp - ($y + $h)) * $k));
        else
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2f %.2f l', $xc * $k, ($hp - ($y + $h)) * $k));
        if (strpos($angle, '4') === false)
            $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - ($y + $h)) * $k));
        else
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);

        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $yc) * $k));
        if (strpos($angle, '1') === false) {
            $this->_out(sprintf('%.2f %.2f l', ($x) * $k, ($hp - $y) * $k));
            $this->_out(sprintf('%.2f %.2f l', ($x + $r) * $k, ($hp - $y) * $k));
        } else
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf(
            '%.2f %.2f %.2f %.2f %.2f %.2f c ',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    function pintarMarcaAgua($texto)
    {
        $ix = (float) $this->GetX();
        $iy = (float) $this->GetY();
        $this->SetFont('UniviaPro-Bold', 'B', 50);
        $this->SetTextColor(225, 225, 225);
        $offsets = array(array(30, 160), array(150, 160), array(0, 80), array(150, 80));
        foreach ($offsets as $offset) {
            $this->RotatedText($ix + $offset[0], $iy + $offset[1], $texto, 45);
        }
        $this->SetTextColor(0, 0, 0);
    }

    function pintarMarcasAguaPoa($estadoCosteo, $ur_cierreForzado, $eurId, $opc = 1)
    {
        if ($estadoCosteo == 2 && $ur_cierreForzado == 2) {
            $this->pintarMarcaAgua('SIN VALIDEZ');
        }

        if ($eurId < 5 && $estadoCosteo == 1) {
            $this->pintarMarcaAgua('HOJA DE TRABAJO');
        } elseif ($opc == 0 && $estadoCosteo == 2) {
            $this->pintarMarcaAgua('NO APLICA');
        }
    }

    function getAnchoEncabezadoPoa($anchoTabla = null)
    {
        $lMargin = (float) $this->lMargin;
        $anchoBordePagina = (float) $this->w - $lMargin;

        if ($anchoTabla === null) {
            $rMargin = is_numeric($this->rMargin) ? (float) $this->rMargin : $lMargin;

            return (float) $this->w - $lMargin - $rMargin;
        }

        $anchoTabla = (float) $anchoTabla;
        if ($lMargin + $anchoTabla <= (float) $this->w) {
            return $anchoTabla;
        }

        return $anchoBordePagina;
    }

    function pintarLogoEncabezadoPoa($ix, $iy, $anchoContenido = 194)
    {
        $logoAncho = 82;
        $logoAlto = 23;
        $logoX = $ix + $anchoContenido - $logoAncho;
        $this->setRouteImg(' ');
        $this->Image($this->getRouteImg(), $logoX, $iy + 1, (string) $logoAncho, (string) $logoAlto, 'PNG', '');

        return $logoAlto;
    }

    function pintarCodigoSubtituloPoa($ix, $iy, $logoAlto, $codigo, $subtitulo, $anchoContenido = 194, $ytmpUr = null)
    {
        if ($ytmpUr !== null) {
            $poaY = max((float) $this->GetY() + 2, (float) $ytmpUr + 8);
        } else {
            $poaY = max((float) $this->GetY() + 2, $iy + 1 + $logoAlto + 2);
        }

        $this->SetXY($ix, $poaY);
        $this->SetFont('UniviaPro-Bold', 'B', 7);
        $this->Cell($anchoContenido, 5, $codigo, 0, 1, 'R');
        $this->SetXY($ix, $this->GetY() + 2);
        $this->SetFont('UniviaPro-Bold', 'B', 10);
        $this->Cell($anchoContenido, 5, $subtitulo, 0, 1, 'C');
        $this->ln(2);
    }

    function RowJustify($text, $w, $h)
    {
        $tab_paragraphe = explode("\n", $text);
        $nb_paragraphe = count($tab_paragraphe);
        $j = 0;

        while ($j < $nb_paragraphe) {

            $paragraphe = $tab_paragraphe[$j];
            $tab_mot = explode(' ', $paragraphe);
            $nb_mot = count($tab_mot);

            $tab_mot2 = array();
            $k = 0;
            $l = 0;
            while ($k < $nb_mot) {

                $len_mot = strlen($tab_mot[$k]);
                if ($len_mot < ($w - 5)) {
                    $tab_mot2[$l] = $tab_mot[$k];
                    $l++;
                } else {
                    $m = 0;
                    $chaine_lettre = '';
                    while ($m < $len_mot) {

                        $lettre = substr($tab_mot[$k], $m, 1);
                        $len_chaine_lettre = $this->GetStringWidth($chaine_lettre . $lettre);

                        if ($len_chaine_lettre > ($w - 7)) {
                            $tab_mot2[$l] = $chaine_lettre . '-';
                            $chaine_lettre = $lettre;
                            $l++;
                        } else {
                            $chaine_lettre .= $lettre;
                        }
                        $m++;
                    }
                    if ($chaine_lettre) {
                        $tab_mot2[$l] = $chaine_lettre;
                        $l++;
                    }
                }
                $k++;
            }

            $nb_mot = count($tab_mot2);
            $i = 0;
            $ligne = '';
            while ($i < $nb_mot) {

                $mot = $tab_mot2[$i];
                $len_ligne = $this->GetStringWidth($ligne . ' ' . $mot);

                if ($len_ligne > ($w - 5)) {

                    $len_ligne = $this->GetStringWidth($ligne);
                    $nb_carac = strlen($ligne);
                    $ecart = (($w - 2) - $len_ligne) / $nb_carac;
                    $this->_out(sprintf('BT %.3F Tc ET', $ecart * $this->k));
                    $this->MultiCell($w, $h, $ligne);
                    $ligne = $mot;
                } else {

                    if ($ligne) {
                        $ligne .= ' ' . $mot;
                    } else {
                        $ligne = $mot;
                    }
                }
                $i++;
            }

            $this->_out('BT 0 Tc ET');
            $this->MultiCell($w, $h, $ligne);
            $j++;
        }
    }
}
