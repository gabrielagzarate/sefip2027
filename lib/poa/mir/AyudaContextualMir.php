<?php

/**
 * Ayuda contextual MIR — diseño tipo ficha (tarjeta en popover).
 *
 * Uso:
 *   AyudaContextualMir::th('componente', 'indicador', 'Indicador', 'text-align:center', $comId);
 *
 * Solo componente 1 por defecto. Todos: AyudaContextualMir::habilitarEnTodosLosComponentes();
 */
class AyudaContextualMir
{
    /** @var int[]|null */
    private static $componentesConAyuda = [1];

    /**
     * @var array<string, array<string, array{columna:string, que_capturar:string, accion:string}>>
     */
    private static $ayudas = [
        'componente' => [
            'indicador' => [
                'columna' => 'INDICADOR',
                'que_capturar' => 'Es una herramienta cuantitativa que permite monitorear el <strong>avance en el logro de los objetivos</strong>.',
                'accion' => 'Selecciona el recuadro para visualizar el indicador vinculado.',
            ],
            'medio_verificacion' => [
                'columna' => 'MEDIO DE VERIFICACIÓN',
                'que_capturar' => 'Indican <strong>las fuentes de información</strong> que se utilizarán para medir los indicadores y para verificar que los objetivos del programa (resumen narrativo) se lograron.',
                'accion' => 'Selecciona el recuadro para visualizar el medio de verificación.',
            ],
            'supuesto' => [
                'columna' => 'SUPUESTO',
                'que_capturar' => 'Son <strong>factores externos</strong> fuera del control de la intervención, pero <strong>que inciden en el logro de sus objetivos.</strong> Recuerda que son riesgos que se expresan como situaciones que se tienen que cumplir para alcanzar el siguiente nivel del objetivo.',
                'accion' => 'Selecciona el recuadro para capturar un supuesto.',
            ],
        ],
    ];

    public static function setComponentesConAyuda($ids)
    {
        self::$componentesConAyuda = $ids;
    }

    public static function habilitarEnTodosLosComponentes()
    {
        self::$componentesConAyuda = null;
    }

    public static function mostrarEnComponente($comId)
    {
        if (self::$componentesConAyuda === null) {
            return true;
        }

        return in_array((int) $comId, self::$componentesConAyuda, true);
    }

    public static function resolver($seccion, $campo)
    {
        $seccion = strtolower((string) $seccion);
        $campo = strtolower((string) $campo);

        if (isset(self::$ayudas[$seccion][$campo])) {
            return self::$ayudas[$seccion][$campo];
        }

        return null;
    }

    public static function th($seccion, $campo, $etiqueta, $style = 'vertical-align: middle;', $comId = null)
    {
        if ($seccion === 'componente' && $comId !== null && !self::mostrarEnComponente($comId)) {
            return '<th style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8')
                . '</th>';
        }

        $sufijo = ($seccion === 'componente' && $comId !== null) ? (string) $comId : '';
        $icono = self::icono($seccion, $campo, $sufijo);

        return '<th style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8')
            . ($icono ? ' ' . $icono : '')
            . '</th>';
    }

    /**
     * Botón ? que abre popover con ficha de ayuda (no tabla).
     */
    public static function icono($seccion, $campo, $sufijo = '')
    {
        $ayuda = self::resolver($seccion, $campo);
        if ($ayuda === null) {
            return '';
        }

        $contenido = htmlspecialchars(self::htmlContenido($ayuda), ENT_QUOTES, 'UTF-8');
        $id = 'mirAyuda-' . preg_replace('/[^a-z0-9_-]/i', '-', $seccion . '-' . $campo . ($sufijo ? '-' . $sufijo : ''));
        $titulo = htmlspecialchars($ayuda['columna'], ENT_QUOTES, 'UTF-8');

        return '<button type="button" class="mir-ayuda-btn" id="' . $id . '"'
            . ' data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="click"'
            . ' data-bs-placement="auto" data-bs-custom-class="mir-ayuda-popover-v2"'
            . ' data-bs-content="' . $contenido . '"'
            . ' aria-label="Ayuda sobre ' . $titulo . '">'
            . '<i class="fa-solid fa-circle-question mir-ayuda-btn__icon" aria-hidden="true"></i>'
            . '</button>';
    }

    /**
     * Ficha vertical: encabezado + qué capturar + acción.
     *
     * @param array{columna:string, que_capturar:string, accion:string} $ayuda
     */
    public static function htmlContenido(array $ayuda)
    {
        $columna = htmlspecialchars($ayuda['columna'], ENT_QUOTES, 'UTF-8');
        $accion = htmlspecialchars($ayuda['accion'], ENT_QUOTES, 'UTF-8');

        return '
<div class="mir-ayuda-ficha">
    <div class="mir-ayuda-ficha__col">
        <span class="mir-ayuda-ficha__col-name">' . $columna . '</span>
    </div>
    <div class="mir-ayuda-ficha__body">
        <div class="mir-ayuda-ficha__texto">' . $ayuda['que_capturar'] . '</div>
    </div>
    <div class="mir-ayuda-ficha__pie">
        <p class="mir-ayuda-ficha__pregunta mir-ayuda-ficha__pregunta--accion">Acción que deben realizar</p>
        <p class="mir-ayuda-ficha__accion">' . $accion . '</p>
    </div>
</div>';
    }

    public static function css()
    {
        return '
<style>
.mir-ayuda-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0 0 0 0.2rem;
    border: none;
    background: transparent;
    vertical-align: middle;
    line-height: 1;
    cursor: pointer;
    color: #9D2449;
}
.mir-ayuda-btn:hover,
.mir-ayuda-btn:focus {
    color: #6d1834;
    outline: none;
}
.mir-ayuda-btn__icon {
    font-size: 1.05rem;
}
.mir-ayuda-popover-v2 {
    --bs-popover-max-width: 420px;
    border: none;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.14);
    border-radius: 10px;
    overflow: hidden;
}
.mir-ayuda-popover-v2 .popover-header {
    display: none !important;
}
.mir-ayuda-popover-v2 .popover-body {
    padding: 0;
    background: #fff;
}
.mir-ayuda-ficha {
    font-size: 0.84rem;
    line-height: 1.45;
    color: #333;
}
.mir-ayuda-ficha__col {
    padding: 0.55rem 0.85rem;
    background: linear-gradient(135deg, #f3e8ed 0%, #faf5f7 100%);
    border-bottom: 2px solid #9D2449;
}
.mir-ayuda-ficha__col-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #9D2449;
}
.mir-ayuda-ficha__body {
    padding: 0.75rem 0.85rem;
    background: #fff;
}
.mir-ayuda-ficha__pregunta {
    margin: 0 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #555;
}
.mir-ayuda-ficha__texto {
    margin: 0;
    color: #2d2d2d;
}
.mir-ayuda-ficha__texto strong,
.mir-ayuda-ficha__texto b {
    color: #9D2449;
    font-weight: 700;
}
.mir-ayuda-ficha__pie {
    padding: 0.65rem 0.85rem 0.75rem;
    background: #fff8f0;
    border-top: 1px dashed #e0b896;
}
.mir-ayuda-ficha__pregunta--accion {
    color: #8b5a2b;
}
.mir-ayuda-ficha__accion {
    margin: 0;
    color: #4a3728;
    font-style: italic;
}
.mir-ayuda-ficha__accion::before {
    content: "\\2192 ";
    color: #c17f3a;
    font-style: normal;
    font-weight: 700;
}
</style>';
    }

    public static function js()
    {
        return '
function mirAyudaCerrarTodas(excepto) {
    document.querySelectorAll(".mir-ayuda-btn").forEach(function(btn) {
        if (excepto && btn === excepto) {
            return;
        }
        var pop = bootstrap.Popover.getInstance(btn);
        if (pop) {
            pop.hide();
        }
    });
}
function initMirAyudaContextual(root) {
    var scope = root || document;
    var plantilla = "<div class=\\"popover mir-ayuda-popover-v2\\" role=\\"tooltip\\">"
        + "<div class=\\"popover-arrow\\"></div>"
        + "<div class=\\"popover-body\\"></div></div>";
    scope.querySelectorAll(".mir-ayuda-btn[data-bs-toggle=popover]").forEach(function(el) {
        var existing = bootstrap.Popover.getInstance(el);
        if (existing) {
            existing.dispose();
        }
        el.addEventListener("click", function(e) {
            e.stopPropagation();
        });
        var pop = new bootstrap.Popover(el, {
            html: true,
            trigger: "click",
            placement: "auto",
            sanitize: false,
            customClass: "mir-ayuda-popover-v2",
            title: "",
            template: plantilla
        });
        el.addEventListener("show.bs.popover", function() {
            mirAyudaCerrarTodas(el);
        });
    });
}
document.addEventListener("click", function(e) {
    if (e.target.closest && e.target.closest(".mir-ayuda-btn")) {
        return;
    }
    document.querySelectorAll(".mir-ayuda-btn").forEach(function(btn) {
        var pop = bootstrap.Popover.getInstance(btn);
        if (!pop) {
            return;
        }
        var tip = pop.tip;
        if (!tip || !tip.contains(e.target)) {
            pop.hide();
        }
    });
});
initMirAyudaContextual();
';
    }

    public static function registrar($seccion, $campo, array $datos)
    {
        $seccion = strtolower((string) $seccion);
        $campo = strtolower((string) $campo);
        if (!isset(self::$ayudas[$seccion])) {
            self::$ayudas[$seccion] = [];
        }
        self::$ayudas[$seccion][$campo] = $datos;
    }
}
