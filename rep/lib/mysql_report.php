<?php

// El objetivo principal es imprimir un PDF pasándole una consulta SQL
// El ejemplo más básico de su uso sería : mysqlReport( "SELECT * FROM cualquierTabla");
// Se usa principalmente en rep/doc/rep_Reporte.php para tener un reporteador genérico

define('FPDF_FONTPATH_CG', '../../rep/fnt/');
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/helpers.php';
require_once dirname(__FILE__) . '/reportetabla.1.8.php';
require_once dirname(__FILE__) . '/../../lib/AccesoDatos.php';

class AnchosColumnaNoDefinidosException extends InvalidArgumentException
{
}

class otroPDF extends PDF
{
    public $tablewidths;
    public $headerset;
    public $footerset;
    public $fullWidth = 0;
    public $anchoColumnas;
    public $alineacionColumnas;
    public $results;
    public $numFields;
    public $sColWidth;
    public $colAlign;
    public $params; //parametros para las querys con PDO
    public $titlesPosition; //indice de cada columna
    public $encabLin1;
    public $encabLin2;
    public $encabLin3;
    public $colTitles;
    private $cellFontSize;

    // Configuración inicial de la página ( tamaño, orientación, etc )
    public function _beginpage($orientation, $size, $rotation)
    {
        $this->page++;
        if (!isset($this->pages[$this->page])) {
            // Para resolver el problema de sobreescribir una pagina si ya existe
            $this->pages[$this->page] = '';
        }
        $this->PageLinks[$this->page] = [];
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';

        // Check page size and orientation
        if ($orientation == '') {
            $orientation = $this->DefOrientation;
        } else {
            $orientation = strtoupper($orientation[0]);
        }
        if ($size == '') {
            $size = $this->DefPageSize;
        } else {
            $size = $this->_getpagesize($size);
        }
        if ($orientation != $this->CurOrientation || $size[0] != $this->CurPageSize[0] || $size[1] != $this->CurPageSize[1]) {
            // New size or orientation
            if ($orientation == 'P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w * $this->k;
            $this->hPt = $this->h * $this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if ($orientation != $this->DefOrientation || $size[0] != $this->DefPageSize[0] || $size[1] != $this->DefPageSize[1]) {
            $this->PageInfo[$this->page]['size'] = [$this->wPt, $this->hPt];
        }
        if ($rotation != 0) {
            if ($rotation % 90 != 0) {
                $this->Error('Incorrect rotation value: ' . $rotation);
            }
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    // ======================================================================================================
    // ENCABEZADO
    // Para estandarizar el encabezado con los reportes del SINPRES
    // ======================================================================================================
    public function Header()
    {
        global $maxY;

        // Si aún no existe el encabezado de ésta página
        if (!isset($this->headerset[$this->page])) {

            if (!is_array($this->tablewidths)) {
                throw new AnchosColumnaNoDefinidosException('No estan definidos los anchos de las columnas');
            }

            foreach ($this->tablewidths as $width) {
                $this->fullWidth += $width;
            }
            $this->SetY(($this->tMargin) - ($this->FontSizePt / $this->k) * 2);
            $this->cellFontSize = $this->FontSizePt ;
            $l = ($this->lMargin);

            //Encabezado del documento
            $iy = $this->GetY();
            $this->AddFont('UniviaPro-Regular', '', 'UniviaPro-Regular.php');
            $this->AddFont('UniviaPro-Bold', 'B', 'UniviaPro-Bold.php');
            // SE IMPRIME LAS IMAGENES DEL ESCUDO Y LOGO
            $extension = strtoupper(explode('.', LOGO_GRIS)[1]);
            $this->Image('../../../img/' . LOGO_GRIS, $this->fullWidth - 190, $iy - 1, 220, 55, $extension, '');

            $this->SetFont('UniviaPro-Bold', 'B', 17);//esto aqui es el numerito 2018 rotado
            // SE IMPRIME EL TITULO DE GOBIERNO DEL ....
            $this->SetTextColor(200, 200, 200);
            $this->RotatedText($l + 22, 58, APP_EJERCICIO, 90);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('UniviaPro-Bold', 'B', 6);
            // Por el momento FIJO el tamaño de la fuente, para dejar fijo este encabezado ...
            $this->SetX($l + 15);
            $this->Cell(0, 4, '', 0, 1, 'L');
            $this->SetX($l + 23);
            $this->Cell(0, 15, 'GOBIERNO DEL ESTADO DE OAXACA', 0, 1, 'L');
            $this->SetX($l + 23);
            $this->Cell(0, 14, utf8Decode($this->encabLin1 ?? ''), 0, 1, 'L');
            $this->SetX($l + 23);
            $this->Cell(0, 14, utf8Decode($this->encabLin2 ?? ''), 0, 1, 'L');
            $this->SetX($l + 23);
            $this->Cell(0, 14, utf8Decode($this->encabLin3 ?? ''), 0, 1, 'L');

            $lTop = $iy + 70;
            $ytmp = $this->GetY();

            $this->setDrawColor(200, 200, 200);
            $this->Line(28, $ytmp, 770, $ytmp);//linea horizontal anterior a las columnas
            $this->ln(7);
            // Imprimimos los encabezados de columnas ...
            $this->SetFont('UniviaPro-Regular', '', $this->cellFontSize);
            foreach ($this->colTitles as $col => $txt) {
                $this->SetXY($l, $lTop);
                $this->MultiCell($this->tablewidths[$col], $this->FontSizePt + 6, utf8Decode($txt));
                $l += $this->tablewidths[$col] ;
                $maxY = ($maxY < $this->getY()) ? $this->getY() : $maxY ;
            }

            $this->setFillColor(199, 209, 219);//color del relleno de los encabezados de columnas
            $l = ($this->lMargin);
            $this->SetFont('UniviaPro-Regular', '', $this->cellFontSize);
            foreach ($this->colTitles as $col => $txt) {
                $this->SetXY($l, $lTop);
                $alinea = (isset($this->colAlign)) ? $this->colAlign[$col] : 'L';
                $this->cell($this->tablewidths[$col], $maxY - $lTop, '', 1, 0, $alinea, 1);
                $this->SetXY($l, $lTop);
                $this->MultiCell($this->tablewidths[$col], $this->FontSizePt + 6, utf8Decode($txt), 0, $alinea);
                $l += $this->tablewidths[$col];
            }
            $this->setFillColor(255, 255, 255);

            // Marcador para indicar que ésta página ya tiene encabezado
            $this->headerset[$this->page] = 1;
        }

        $this->SetY($maxY);
    }

    // ======================================================================================================
    // PIE DE PAGINA
    // ======================================================================================================
    public function Footer()
    {
        // Check if footer for this page already exists
        if (!isset($this->footerset[$this->page])) {
            $this->SetY(-35);
            $this->Line($this->lMargin, $this->GetY(), $this->fullWidth + $this->lMargin, $this->GetY());
            $this->SetY(-25);
            //Page number
            $this->Cell(0, 0, utf8Decode('Página ') . $this->PageNo() . ' de {nb}', 0, 20, 'C');
            $this->Cell(0, 0, 'UR ' . $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'], 0, 0, 'L');
            $this->Cell(0, 0, date('d/m/y') . ' - ' . date('H:i:s'), 0, 0, 'R');
            // set footerset
            $this->footerset[$this->page] = 1;
        }
    }

    // ======================================================================================================
    // FUNCION PRINCIPAL, ENCARGADA DE IMPRIMIR EL RESULTADO DE LA CONSULTA
    // ======================================================================================================
    public function morepagestable($lineheight = 8)
    {
        // some things to set and 'remember'
        $l = $this->lMargin;
        $h = $this->GetY();
        $currpage = $this->page;
        $this->fullWidth = 0;
        $maxpage = 0;

        // Calculamos el ancho total
        foreach ($this->tablewidths as $width) {
            $this->fullWidth += $width;
        }

        // Proceso para imprimir la tabla
        // ------------------------------------------------------------------------------------
        $row = 0;
        while ($data = $this->extraerDatoReporte()) {
            $this->page = $currpage;
            // write the horizontal borders
            // write the content and remember the height of the highest col
            foreach ($data as $col => $txt) {
                $txt = str_replace('""', '"', $txt);
                $txt = preg_replace(sprintf('~[^%s]++~i', '0-9a-zA-ZÑñáéíóúÁÉÍÓÚ_.,"\s\-/°()%'), ' ', $txt);
                $txt = preg_replace('/\s\s+/', ' ', $txt);

                $this->page = $currpage;
                $this->SetXY($l, $h);
                $this->SetFont('UniviaPro-Regular', '', 9);//aqui se elige el tamaño del texto del cuerpo del documento

                // se busca el indice de la columna y se ocupa en los anchos y alineaciones
                $indice = $this->titlesPosition[$col];
                $this->MultiCell($this->tablewidths[$indice], $lineheight, utf8Decode($txt), 0, $this->colAlign[$indice]);

                $l += $this->tablewidths[$indice];

                if (!isset($tmpheight[$row . '-' . $this->page])) {
                    $tmpheight[$row . '-' . $this->page] = 0;
                }

                if ($tmpheight[$row . '-' . $this->page] < $this->GetY()) {
                    $tmpheight[$row . '-' . $this->page] = $this->GetY();
                }
                if ($this->page > $maxpage) {
                    $maxpage = $this->page;
                }
                unset($data[$col]);
            }
            // get the height we were in the last used page
            $h = $tmpheight[$row . '-' . $maxpage];
            // set the "pointer" to the left margin
            $l = $this->lMargin;
            // set the $currpage to the last page
            $currpage = $maxpage;
            unset($data[$row]);
            $row++;
        }

        // set it to the last page, if not it'll cause some problems
        $this->page = $maxpage;
    }

    // ============================================================================================
    // INICIAMOS ...
    // ============================================================================================
    public function mysqlReport($query, $attr = [])
    {
        foreach ($attr as $key => $val) {
            $this->$key = $val;
        }

        $this->ejecutarConsultaReporte($query);
        $this->sColWidth = $this->w - $this->lMargin - $this->rMargin;
        $this->configurarAnchosColumnas();
        $this->configurarTitulosYAlineacion();

        $this->AliasNbPages();
        $this->SetY($this->tMargin);
        $this->AddPage();
        $this->SetProtection(['print'], '', PDF_PASSWORD_OWNER_MYSQL_REPORT);
        $this->morepagestable($this->FontSizePt + 6); // +6 es el espaciado entre lineas
    }

    private function ejecutarConsultaReporte($query): void
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($query);
        $params = is_array($this->params) ? $this->params : [];
        $conexion->consultar($params);
        $this->results = $conexion->getConexionActiva();
        $conexion->desconectar();
        $this->numFields = $conexion->getNumeroCampos();

        if ($conexion->getNumeroRegistros() > 0) {
            return;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT "NO EXISTE INFORMACION..." AS AVISO;');
        $conexion->consultar();
        $this->results = $conexion->getConexionActiva();
        $conexion->desconectar();
        $this->numFields = $conexion->getNumeroCampos();
    }

    private function configurarAnchosColumnas(): void
    {
        if (!isset($this->anchoColumnas)) {
            $this->tablewidths = [];
            for ($i = 0; $i < $this->numFields; $i++) {
                $this->tablewidths[$i] = $this->sColWidth / $this->numFields;
            }
            return;
        }

        $this->tablewidths = $this->anchoColumnas;
        for ($i = 0; $i < count($this->tablewidths); $i++) {
            $this->tablewidths[$i] = $this->tablewidths[$i] / 100 * $this->sColWidth;
        }
    }

    private function configurarTitulosYAlineacion(): void
    {
        $usarAlineacionDefault = empty($this->alineacionColumnas);
        $i = 0;
        while ($finfo = $this->results->getResult()->getColumnMeta($i)) {
            $this->titlesPosition[$finfo['name']] = $i;
            $this->colTitles[$i] = $finfo['name'];
            if ($usarAlineacionDefault) {
                $this->colAlign[$i] = ($finfo['native_type'] == 'NEWDECIMAL') ? 'R' : 'L';
            }
            $i++;
        }
        if (!$usarAlineacionDefault) {
            $this->colAlign = $this->alineacionColumnas;
        }
    }

    public function extraerDatoReporte(int $curOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = PDO::FETCH_ORI_ABS)
    {
        if ($this->results->getResult() instanceof PDOStatement) {
            return $this->results->getResult()->fetch(PDO::FETCH_ASSOC, $curOrientation, $cursorOffset);
        }
    }
}
