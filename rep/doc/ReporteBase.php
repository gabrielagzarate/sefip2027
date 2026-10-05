<?php

/**
 * Autor: Edgar Barrita
 * Fecha: 31/01/2024
 * Comentario: Clase que genera de forma adecuada la estructura base de encabezados y pies de página de los reportes, tomando en cuenta orientación.
 * Objetivo: Que todos los reportes hereden de la clase ReporteBase para que lo relacionado a Header y Footer se maneje aquí y en las clases hijas solo tengan la información propia del reporte.
 */

require_once('../../../../ent/UnidadResponsable.php');
require_once('../../../lib/reportetabla.1.8.php');

class ReporteBase extends PDF
{
    private $nombreReporte = 'Reporte';
    private $ejercicio = 0;
    private $gpoId = 0;
    private $urId = 0;
    private $leyenda = false;
    private $importesEnMiles = false;
    private $logo = '';
    private $firmantes = array();
    private $ur = null;
    private $ue = null;
    private $numUe = 0;
    private $strFecha = '';
    private $tipoReporte = 1;
    private $lineasEncabezadoDocumento = array();
    private $encabezadoTabla = array();

    public function  __construct()
    {
        parent::__construct();

        $this->ejercicio = $this->getEjercicioActual();

        $this->gpoId = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->urId = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];

        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->getGpoId());
        $this->ur->setUr_id($this->getUrId());
        $this->ur->select();
    }

    public function setNombreReporte($nombre)
    {
        $this->nombreReporte = (!is_null($nombre) && strlen($nombre) > 0) ? utf8Decode($nombre) : $this->nombreReporte;
        $this->SetTitle($this->getNombreReporte());
    }

    public function setEjercicio($ejercicio)
    {
        $this->ejercicio = $ejercicio ?? $this->ejercicio;
    }

    public function addFirmante($nombre, $cargo)
    {
        if (!empty($nombre) && !empty($cargo)) {
            $this->firmantes[] = ['nombre' => $nombre, 'cargo' => $cargo];
        }
    }

    public function setFirmantes($firmantes)
    {
        if (is_array($firmantes)) {
            $this->firmantes = array_filter($firmantes, function ($firmante) {
                return is_array($firmante) && count($firmante) == 2 && !empty($firmante['nombre']) && !empty($firmante['cargo']);
            });
        }
    }

    public function setLeyenda($leyenda)
    {
        $this->leyenda = (bool) $leyenda;
    }

    public function setImporteEnMiles($importesEnMiles)
    {
        $this->importesEnMiles = (bool) $importesEnMiles;
    }

    public function setLogo($logo)
    {
        $this->logo = (!is_null($logo) && strlen($logo) > 5) ? $logo : $this->logo;
    }

    public function setUe($ue)
    {
        $this->ue = $ue ?? $this->ue;
    }

    public function setNumUe($numUe)
    {
        $this->numUe = (!is_null($numUe) && is_int($numUe) && $numUe > 0) ? (int) $numUe : $this->numUe;
    }

    public function setStrFecha($strFecha)
    {
        if (!empty($strFecha)) {
            $this->strFecha = $strFecha;
        } else {
            if ($this->getEjercicio() < $this->getEjercicioActual()) {
                $this->strFecha = 'Al 31 de diciembre de ' . $this->getEjercicio();
            } else {
                date_default_timezone_set('America/Mexico_City');
                $this->strFecha = 'Al ' . date('d') . ' de ' . strtolower($this->getNombreDelMes(date('m'))) . ' de ' . date('Y');
            }
        }
    }

    public function setLineasEncabezadoDocumento($lineasEncabezado)
    {
        $this->lineasEncabezadoDocumento = is_array($lineasEncabezado) ? $lineasEncabezado : $this->lineasEncabezadoDocumento;
    }

    public function addLineaEncabezadoDocumento($lineaEncabezado)
    {
        if (!empty($lineaEncabezado)) {
            $this->lineasEncabezadoDocumento[] = $lineaEncabezado;
        }
    }

    public function setEncabezadoTabla($encabezado)
    {
        if (!is_array($encabezado)) {
            return false;
        }

        $keys = ['widths', 'aligns', 'titles'];

        foreach ($keys as $key) {
            if (!isset($encabezado[$key]) || empty($encabezado[$key])) {
                return false;
            }
        }

        if (count($encabezado['widths']) == count($encabezado['aligns']) && count($encabezado['widths']) == count($encabezado['titles'])) {
            $this->encabezadoTabla = $encabezado;
        }
    }

    public function setTipoReporte($tipoReporte)
    {
        if (!is_null($tipoReporte) && is_int($tipoReporte) && $tipoReporte > 0) {
            $this->tipoReporte = (int) $tipoReporte;
        }
    }

    function getNombreReporte(): string
    {
        return $this->nombreReporte;
    }

    function getEjercicio(): int
    {
        return $this->ejercicio;
    }

    function getGpoId(): int
    {
        return $this->gpoId;
    }

    function getUrId(): int
    {
        return $this->urId;
    }

    function showLeyenda(): bool
    {
        return $this->leyenda;
    }

    function isImporteEnMiles(): bool
    {
        return $this->importesEnMiles;
    }

    function getLogo(): string
    {
        return $this->logo;
    }

    function getFirmantes(): array
    {
        return $this->firmantes;
    }

    function getFirmante($indice): array
    {
        return $this->firmantes[$indice] ?? array();
    }

    function getUr(): UnidadResponsable
    {
        return $this->ur;
    }

    function getUe()
    {
        return $this->ue;
    }

    function getNumUe(): int
    {
        return $this->numUe;
    }

    function getStrFecha(): string
    {
        if (empty($this->strFecha)) {
            if ($this->getEjercicio() != $this->getEjercicioActual()) {
                return 'Al 31 de diciembre de ' . $this->getEjercicio();
            } else {
                date_default_timezone_set('America/Mexico_City');
                return 'Al ' . date('d') . ' de ' . strtolower($this->getNombreDelMes(date('m'))) . ' de ' . date('Y');
            }
        }
        return $this->strFecha;
    }

    function getTipoReporte(): int
    {
        return $this->tipoReporte;
    }

    function getEjercicioActual(): int
    {
        return (int) $_SESSION[_S]['ejercicioActual'];
    }

    function getEjercicioAnterior(): int
    {
        return $this->getEjercicioActual() - 1;
    }

    function getLineasEncabezadoDocumento(): array
    {
        return $this->lineasEncabezadoDocumento;
    }

    function getEncabezadoTabla(): array
    {
        return $this->encabezadoTabla;
    }

    public function Footer($lastPage = true)
    {
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');

        $firmantes = $this->getFirmantes();
        $numFirmantes = count($firmantes);

        if ($this->showLeyenda() || $numFirmantes) {
            $this->SetY(-30);
            $this->Cell(190, 2, '', 0, 0, 'C');
            $this->Ln(5);
        } else {
            $this->SetY(-25);
        }

        if ($lastPage) {
            if ($this->leyenda) {
                $this->SetFont('univiapro', '', 7);
                $this->Cell(270, 2, utf8Decode('Lugar y fecha de generación: Reyes Mantecón, Oaxaca; a ' . date('d') . ' de ' . strtolower($this->getNombreDelMes(date('m'))) . ' de ' . date('Y')), 0, 0, 'C');
                $this->Ln(2);
                $this->Cell(270, 2, 'Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor.', 0, 0, 'C');
                $this->Ln(1);
            }

            $this->Ln(2);
            $this->SetFont('univiapro', 'B', 7);

            if ($numFirmantes > 0) {
                $this->SetX(0);
                $ancho = ($this->GetPageWidth()) / $numFirmantes;

                foreach ($firmantes as $firmante) {
                    $this->MultiAlignCell($ancho, 3, utf8Decode($firmante['cargo']), 0, 0, 'C');
                }

                $this->Ln(4);
                $this->SetX(0);
                $this->SetFont('univiapro', '', 7);

                foreach ($firmantes as $firmante) {
                    $this->MultiAlignCell($ancho, 3, utf8Decode($firmante['nombre']), 0, 0, 'C');
                }
            }
        }

        $numeroPagina = utf8Decode('Página ') . $this->PageNo() . '/{nb}';
        $this->SetY(-11);
        $this->SetFont('univiapro', '', 6);
        $this->Text(($this->GetPageWidth() - $this->GetStringWidth($numeroPagina)) / 2, $this->GetY(), $numeroPagina);
    }

    public function drawHeadersAndLogo($lineas)
    {
        $archivoLogo = strlen($this->getLogo()) > 5 ? __DIR__ . '/../../img/' . $this->getLogo() : null;
        $limiteMargenDerecho = $this->GetPageWidth() - 10;
        $anchoArchivoLogo = $altoArchivoLogo = $tipoArchivoLogo = null;
        $anchoImagen = null;
        $altoImagen = null;

        if (!is_null($archivoLogo) && file_exists($archivoLogo)) {
            $imageSize = getimagesize($archivoLogo);
            $anchoArchivoLogo = $imageSize[0];
            $altoArchivoLogo = $imageSize[1];
            $tipoArchivoLogo = str_replace('image/', '', $imageSize['mime']);
            $anchoImagen = (intval($this->GetPageWidth()) == 297) ? 74 : 50;
            $altoImagen = $altoArchivoLogo / ($anchoArchivoLogo / $anchoImagen);
            $limiteMargenDerecho -= $anchoImagen;
        }

        $this->SetMargins(20, 10, 20);
        $this->SetXY(20, 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->SetBorders(0);

        $nLineas = 0;

        if (!is_null($archivoLogo) && file_exists($archivoLogo)) {
            $this->Image($archivoLogo, $limiteMargenDerecho, $this->GetY(), $anchoImagen, $altoImagen, $tipoArchivoLogo);
        }

        $this->SetFont('univiapro', 'B', 7);

        foreach ($lineas as $linea) {
            if (trim($linea) != '') {
                $this->SetX(10);
                $this->Cell($limiteMargenDerecho - 10, 5, $linea, 0, 0, 'C');
                $this->SetY($this->GetY() + 4);
                $nLineas++;
            }
        }

        if ($nLineas <= 4) {
            $this->SetY($this->GetY() + 2);
            $this->ln(5);
        }

        $this->setY($this->getY() + 5);
    }

    function Header()
    {
        $this->AddFont('univiapro', '');
        $this->AddFont('univiapro', 'B');

        $lineasEncabezadoDocumento = $this->getLineasEncabezadoDocumento();
        if (!empty($lineasEncabezadoDocumento)) {
            $this->drawHeadersAndLogo($lineasEncabezadoDocumento);
        } else {
            $this->drawHeadersAndLogo($this->getDefaultHeaders());
        }

        $encabezadoTabla = $this->getEncabezadoTabla();
        if (!empty($encabezadoTabla)) {
            $this->setHeaderSettings($encabezadoTabla);
            $this->Row($encabezadoTabla['titles']);
        }

        $this->setX(10);
        $this->setRelleno('no');
        $this->SetBorders(0);
    }

    private function setHeaderSettings($encabezadoTabla)
    {
        $this->setX(10);
        $this->setRelleno($encabezadoTabla['relleno'] ?? 'si');
        $this->setAltoLinea($encabezadoTabla['altoLinea'] ?? 5);
        $this->SetBorders($encabezadoTabla['border'] ?? 1);

        if (isset($encabezadoTabla['font'][0], $encabezadoTabla['font'][1], $encabezadoTabla['font'][2])) {
            $this->SetFont(...$encabezadoTabla['font']);
        }

        if (isset($encabezadoTabla['textColor'][0], $encabezadoTabla['textColor'][1], $encabezadoTabla['textColor'][2])) {
            $this->SetTextColor(...$encabezadoTabla['textColor']);
        }

        if (isset($encabezadoTabla['drawColor'][0], $encabezadoTabla['drawColor'][1], $encabezadoTabla['drawColor'][2])) {
            $this->setDrawColor(...$encabezadoTabla['drawColor']);
        }

        if (isset($encabezadoTabla['fillColor'][0], $encabezadoTabla['fillColor'][1], $encabezadoTabla['fillColor'][2])) {
            $this->SetFillColor(...$encabezadoTabla['fillColor']);
        }

        $this->SetWidths($encabezadoTabla['widths']);
        $this->SetAligns($encabezadoTabla['aligns']);
    }

    private function getDefaultHeaders()
    {
        return array(
            ($_SESSION[_S]['reportes'] != 'C96') ? utf8Decode('Cuenta de la Hacienda Pública Estatal ' . $this->getEjercicioActual()) : '',
            ($_SESSION[_S]['nvj_id'] > 2) ? utf8Decode('Secretaría de Finanzas del Gobierno del Estado de Oaxaca') : 'Gobierno del Estado de Oaxaca',
            ($_SESSION[_S]['nvj_id'] > 2) ? utf8Decode('Dirección de Contabilidad Gubernamental: ' . $this->getUr()->getClave() . ' - ' . $this->getUr()->getNombre()) : utf8Decode('UR: ' . $this->getUr()->getClave() . ' - ' . $this->getUr()->getNombre()),
            $this->getNombreReporte(),
            $this->getStrFecha(),
            $this->isImporteEnMiles() ? '(MILES DE PESOS)' : '(PESOS)'
        );
    }

    private function getNombreDelMes($mes)
    {
        $meses = [
            0 => 'APERTURA',
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
            13 => 'CIERRE'
        ];

        return isset($meses[intval($mes)]) ? $meses[intval($mes)] : '';
    }
}
