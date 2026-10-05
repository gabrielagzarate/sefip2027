<?php
class vistasScp
{
    private $princesas = array();
    public function vwTipoClc($datos, $tipo = "", $variant = "v2")
    {
        $ns = $datos['registros'];
        $checked = '';
        $por = 10;
        $wrapClass = $variant === 'v2' ? ' tcl-tipos-v2' : '';

        if ($ns != 0) {
            $por = 100 / $ns;
            $por = round($por, 2) - 1;
        } //fin

        $html = '
            <form id="myForm">
                <div class="d-flex justify-content-start flex-nowrap' . $wrapClass . '">';
        foreach ($datos['datos'] as $row) {
            if ($row['tcl_id'] == $tipo) {
                $checked = 'tcl_ida';
            } else {
                $checked = "tcl_idn";
            } //fin tcl_id

            $html .= "
            <div name = 'tcl_id'
                class = 'tcl_idfont {$checked}'
                id = '{$row['tcl_id']}'
                nombre = '{$row['tcl_nombre']}'
                tabindex = '0'>
                <label>{$row['tcl_id']}</label>
                <span class=\"tcl_nombre\">{$row['tcl_nombre']}</span>
            </div>";
        } //fi foreach

        $html .= '</div></form>';
        return $html;
    } //FIN vwTipoClc

    public function vwSoloLectura($mensaje = "")
    {
        $mensaje = $mensaje != "" ? strtoupper($mensaje) : "SOLO LECTURA";
        $html = '
            <button class="btn btn-default me-1" value="SOLO LECTURA" type="button">
                <span class="fa-solid fa-triangle-exclamation"></span>
                ' . $mensaje . '
            </button>
        ';
        return $html;
    } //FIN vwSoloLectura

    public function setPrincesas($val)
    {
        $this->princesas = $val;
    } //FIN vwTipoClc

    public function getPrincesas()
    {
        return $this->princesas;
    } //FIN vwTipoClc

    public function getF1Control($n)
    {
        switch ($n) {
            case "encProg":
                $encProg = new stdClass();
                $encProg->id = 'encProg';
                $encProg->placeholder = "Encuadre (F1/F2)";
                $encProg->required = true;
                $encProg->maxlength = 20;
                $encProg->class = 'F1Catalogo';
                return $encProg;
                break;
            case "nomProg":
                $nomProg = new stdClass();
                $nomProg->id = 'nomProg';
                $nomProg->placeholder = "Nombre Obra/Actividad";
                $nomProg->lectura = true;
                return $nomProg;
                break;
            case "encCveFin":
                $encCveFin = new stdClass();
                $encCveFin->id = 'encCveFin';
                $encCveFin->placeholder = "Clave (F1/F2)";
                $encCveFin->required = true;
                $encCveFin->maxlength = 12;
                $encCveFin->class = 'F1Catalogo';
                return $encCveFin;
                break;
            case "nomCveFin":
                $nomCveFin = new stdClass();
                $nomCveFin->id = 'nomCveFin';
                $nomCveFin->placeholder = "Nombre de la Clave";
                $nomCveFin->lectura = true;
                return $nomCveFin;
                break;
        } //FIN switch
    } //FIN vwTipoClc

    /**
     * Genera una tarjeta de opción visual (card_opcion) para menús de selección.
     * Misma estructura que WebResponse::ponCard().
     *
     * @param object $p Propiedades: id, titulo, label, img|icon, iconColor, title, class, active
     */
    public function ponCard($p): string
    {
        if (!isset($p->id)) {
            return '';
        }

        $titulo    = $p->titulo ?? '';
        $label     = $p->label ?? '';
        $img       = $p->img ?? '';
        $icon      = $p->icon ?? '';
        $iconColor = $p->iconColor ?? '#8E418D';
        $title     = isset($p->title) ? ' title="' . htmlspecialchars($p->title) . '"' : '';
        $class     = isset($p->class) ? ' ' . $p->class : '';
        $active    = !empty($p->active) ? ' active' : '';

        $html = '<div class="card_opcion' . $active . $class . '" id="' . $p->id . '"' . $title . '>';

        if ($titulo !== '') {
            $html .= '<div class="card_opcion_titulo">' . $titulo . '</div>';
        }

        $html .= '<div class="card_opcion_body">';

        if ($icon !== '') {
            $html .= '<i class="' . htmlspecialchars($icon) . ' card_opcion_icon" style="color:'
                . htmlspecialchars($iconColor) . ';" aria-hidden="true"></i>';
        } elseif ($img !== '') {
            $html .= '<img src="' . $img . '" alt="' . htmlspecialchars($label) . '">';
        }

        if ($label !== '') {
            $html .= '<span class="card_opcion_label">' . $label . '</span>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Envuelve un conjunto de tarjetas en su contenedor flex (card_opcion_wrapper).
     */
    public function ponCards(array $cards): string
    {
        $html = '<div class="card_opcion_wrapper">';
        foreach ($cards as $card) {
            $html .= $this->ponCard($card);
        }
        $html .= '</div>';
        return $html;
    }
} //FIN class vistasScp
