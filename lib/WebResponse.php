<?php

declare (strict_types = 1);

require_once dirname(__FILE__) . "/constantes.php";
require_once dirname(__FILE__) . "/FicheroSefip.php";
require_once dirname(__FILE__) . "/helpers.php";

require_once dirname(__FILE__) . "/AccesoDatos.php";
require_once dirname(__FILE__) . "/consultasValidaAjax.php";
require_once dirname(__FILE__) . "/form.php";
require_once dirname(__FILE__) . "/../ent/Usuario.php";

class WebResponse extends AccesoDatos
{
    private $css = '';
    private $style = '';
    private $js = '';
    private $html = null;
    private $template = '';
    private $script = '';
    private $contenedor = 'contenedor';
    private $validacion = array(); //el indice del rol del usuario actual
    private $elemento;
    private $status;

    private $clasadmingpo;
    private $clasadminur;
    private $clasadminue;
    private $sesgpo;

    public $metodo = '';
    public $prm;
    public $idFile;
    public $pantalla;
    public $comboValorUnico = null;
    public $scriptKendo = '';
    private $idScript;
    public $file;
    protected $sesur;
    protected $scriptComboAutocomplete;
    private $sanitizer;
    public $valorSeleccionado;
    private $primerRow;
    private bool $esProduccion = false;

    public function __construct($ajax = false)
    {
        $this->esProduccion = $_SERVER['SERVER_ADDR'] === '172.17.90.88';
        if (isset($_SESSION[_S]['modulo'])) {
            $modulo = $_SESSION[_S]['modulo'];
            // Ruta real del controlador concreto (evita depender del último frame del backtrace, p. ej. index.php).
            if (get_class($this) !== 'WebResponse') {
                $initialFile = str_replace('\\', '/', (new ReflectionClass($this))->getFileName());
                $needle = '/' . $modulo . '/';
                $pos = strrpos($initialFile, $needle);
                if ($pos !== false) {
                    $strRuta = substr($initialFile, $pos + strlen($needle));
                    if ($strRuta !== '' && strpos($strRuta, '/') !== false) {
                        $this->pantalla = $modulo . '/' . $strRuta;
                    } else {
                        $this->pantalla = $modulo . '/' . get_class($this) . '.php';
                    }
                } else {
                    $this->pantalla = $modulo . '/' . get_class($this) . '.php';
                }
            } else {
                $this->pantalla = $modulo . '/' . get_class($this) . '.php';
            }
        }

        $parametros = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);

        parent::__construct($parametros);
        // Si existe algun parametro pasado por post este se almacena en la variable prm en forma de objeto (json)
        if (!empty($_POST)) {
            $this->prm = json_decode(str_replace(array('"false"', '"true"'), array('false', 'true'), json_encode($_POST)));
        } else {
            $this->prm = new stdClass();
        }

        $this->SetIdScript(""); // para el módulo de contabilidad

        if (isset($_SESSION[_S]['mod_id'])) {
            if (isset($_SESSION[_S]['modulo'])) {
                /* AYUDA PARA CAMPOS */
                $this->mixScript('
                    try {
                    $(":input:not(:button)").focusout(function(){ if( $("#ultimoCampo").length ){var a=$(this).attr("id");$("#ultimoCampo").val(a);} });
                    $(":input:not(:button)").focusin(function(){ if($("#camposAyuda").length){ var d="(" + $(this).attr("id") + ")"; var c=$("#camposAyuda").val().indexOf(d); if(c>0){ $("#tip").removeClass("gris"); } else{ $("#tip").addClass("gris"); } } });
                    } catch (e) { console.log(e.which); }
                ');
            }
        }
        $this->metodo = (isset($this->prm->metodo)) ? $this->prm->metodo : 'main';

        if ($ajax) {
            switch ($this->prm->metodoWebResponse) {
                case 'autocomplete':
                    $this->autocomplete();
                    break;
                case 'validaAjax':
                    $this->validaAjax();
                    break;
            }

            $this->returnJson();
        }
        $html = new stdClass();
        $html->tipo = '#';
        $html->codigo = '';
        $tools = new stdClass();
        $tools->tipo = '#';
        $tools->codigo = '';
        $this->html = array('contenedor' => $html, 'toolsPantalla.tools' => $tools);

        if (isset($this->prm->modalDeTabla)) {
            $this->setContenedor('bodyModalDeTabla' . $this->prm->modalDeTabla);
        } elseif (isset($this->prm->filaTabla)) {
            $this->setContenedor($this->prm->filaTabla);
        }

        // GRUPO Y UR DE LA CLASIFICACION ADMINISTRATIVA
        if (isset($_SESSION[_S]['usr_id'])) {
            $this->clasadmingpo = (isset($_SESSION[_S]['clasAdm-gpo']) && intval($_SESSION[_S]['clasAdm-gpo']) > 0 ?
                $_SESSION[_S]['clasAdm-gpo']
                : null
            );
            $this->clasadminur = (isset($_SESSION[_S]['clasAdm-ur']) && intval($_SESSION[_S]['clasAdm-ur']) > 0 ?
                $_SESSION[_S]['clasAdm-ur']
                : null
            );

            $this->clasadminur = (isset($_SESSION[_S]['clasAdm-ue']) && intval($_SESSION[_S]['clasAdm-ue']) > 0 ?
                $_SESSION[_S]['clasAdm-ue']
                : null
            );

            // GRUPO Y UR DE LA SESION DEL USUARIO
            $this->sesgpo = (isset($_SESSION[_S]['gpo_id']) && intval($_SESSION[_S]['gpo_id']) > 0 ?
                $_SESSION[_S]['gpo_id']
                : null
            );
            $this->sesur = (isset($_SESSION[_S]['ur_id']) && intval($_SESSION[_S]['ur_id']) > 0 ?
                $_SESSION[_S]['ur_id']
                : null
            );
        }
    }

    public function esProduccion(): bool
    {
        return $this->esProduccion;
    }

    public function sanitizar(string | array $cadena): string | array
    {
        return is_array($cadena) ? array_map(function ($item) {
            // return $sanitizer->purify($item);
            return htmlspecialchars(filter_var(filter_var($item, INPUT_POST), FILTER_SANITIZE_ADD_SLASHES));
        }, $cadena) : htmlspecialchars(filter_var(filter_var($cadena, INPUT_POST), FILTER_SANITIZE_ADD_SLASHES));
    }
    public function setIdScript($val) // para el módulo de contabilidad
    {
        $this->idScript = $val;
    }
    public function setContenedor($val, $tipo = '#')
    {
        $this->contenedor = $val;
        if (!isset($this->html[$val])) {
            $this->html[$val] = new stdClass();
            $this->html[$val]->tipo = $tipo;
            $this->html[$val]->codigo = '';
        }
    }
    public function setScript($val)
    {
        $this->script = $val;
    }
    public function setCss($val)
    {
        $this->css = $val;
    }
    public function setStyle($val)
    {
        $this->style = $val;
    }
    public function setJs($val)
    {
        $this->js = $val;
    }
    public function setHtml($val)
    {
        $this->html[$this->contenedor]->codigo = $val;
    }
    public function setStatus($val)
    {
        $this->status = $val;
    }
    public function setValidacion($val)
    {
        $this->validacion = $val;
    }

    public function getContenedor()
    {
        return $this->contenedor;
    }
    public function getScript()
    {
        return $this->script;
    }
    public function getCss()
    {
        return $this->css;
    }
    public function getStyle()
    {
        return $this->style;
    }
    public function getJs()
    {
        return $this->js;
    }
    public function getHtml()
    {
        return $this->html[$this->contenedor]->codigo;
    }
    public function getStatus()
    {
        return $this->status;
    }

    public function mixHtml($val)
    {
        $this->html[$this->contenedor]->codigo .= $val;
    }
    public function mixStyle($val)
    {
        $this->style .= $val;
    }
    public function mixScript($val)
    {
        $this->script .= $val;
    }
    public function preScript($val)
    {
        $this->script = $val . $this->script;
    }
    public function addTemplate($val)
    {
        $this->template .= $val;
    }
    public function mixJs($val)
    {
        $this->mixScript('myJq.addJs("' . $val . '");');
    }
    public function titulo($texto)
    {
        $this->ponBreadcrumbs($texto);
    }

    public function code($cadena)
    {
        return str_replace(array('"', '=', ' ', 'q', 'A', 'y', '1', 'k', 'L', 'w', 'X', '7', 'R', 'S', '8', 'f', '_', 'O', 'J', 'z', 'b', 'M', 'Q', 'D', 'E', 'Y', 'Z', 'x', 'K', 'U', 'j', '2', 'o', 'V', 'm', 'P', 'h', 'I', 'F', 't', '3', 'p', 'H', '6', 'g', 'i', 'u', 'l', 'G', 'W', '4', 's', 'r', 'C', 'a', 'T', 'c', 'd', '5', 'v', 'N', '9', 'n', 'e', 'B', '0'), array('?!', '[]', '()', '0', 'q', 'A', 'y', '1', 'k', 'L', 'w', 'X', '7', 'R', 'S', '8', 'f', '_', 'O', 'J', 'z', 'b', 'M', 'Q', 'D', 'E', 'Y', 'Z', 'x', 'K', 'U', 'j', '2', 'o', 'V', 'm', 'P', 'h', 'I', 'F', 't', '3', 'p', 'H', '6', 'g', 'i', 'u', 'l', 'G', 'W', '4', 's', 'r', 'C', 'a', 'T', 'c', 'd', '5', 'v', 'N', '9', 'n', 'e', 'B'), $cadena);
    }
    public function decode($cadena)
    {
        return str_replace(array_reverse(array('?!', '[]', '()', '0', 'q', 'A', 'y', '1', 'k', 'L', 'w', 'X', '7', 'R', 'S', '8', 'f', '_', 'O', 'J', 'z', 'b', 'M', 'Q', 'D', 'E', 'Y', 'Z', 'x', 'K', 'U', 'j', '2', 'o', 'V', 'm', 'P', 'h', 'I', 'F', 't', '3', 'p', 'H', '6', 'g', 'i', 'u', 'l', 'G', 'W', '4', 's', 'r', 'C', 'a', 'T', 'c', 'd', '5', 'v', 'N', '9', 'n', 'e', 'B')), array_reverse(array('"', '=', ' ', 'q', 'A', 'y', '1', 'k', 'L', 'w', 'X', '7', 'R', 'S', '8', 'f', '_', 'O', 'J', 'z', 'b', 'M', 'Q', 'D', 'E', 'Y', 'Z', 'x', 'K', 'U', 'j', '2', 'o', 'V', 'm', 'P', 'h', 'I', 'F', 't', '3', 'p', 'H', '6', 'g', 'i', 'u', 'l', 'G', 'W', '4', 's', 'r', 'C', 'a', 'T', 'c', 'd', '5', 'v', 'N', '9', 'n', 'e', 'B', '0')), $cadena);
    }
    public function agregaTools($html, $barra = "")
    {
        if ($barra == "") {
            $this->html['toolsPantalla.tools']->codigo = $this->html['toolsPantalla.tools']->codigo . ' ' . $html;
        } else {
            if (!isset($this->html[$barra])) {
                $this->html[$barra] = new stdClass();
                $this->html[$barra]->tipo = "#";
                $this->html[$barra]->codigo = $html;
            } else {
                $this->html[$barra]->codigo = $this->html[$barra]->codigo . ' ' . $html;
            }
        }
    }
    public function returnJson()
    {
        $arreglo = array();
        if (!is_null($this->html)) {
            foreach ($this->html as $key => $contenedor) {
                if ($contenedor->codigo == '') {
                    unset($this->html[$key]);
                }

            }
        }

        $arreglo['html'] = $this->html;
        $arreglo['script'] = $this->script;
        if (isset($_SESSION[_S]['scriptFormulario'])) {
            $arreglo['script'] .= $_SESSION[_S]['scriptFormulario'];
        }

        $_SESSION[_S]['scriptFormulario'] = '';
        if ($this->js != '') {
            $arreglo['js'] = $this->js;
        }

        if ($this->css != '') {
            $arreglo['css'] = $this->css;
        }

        if ($this->template != '') {
            $arreglo['template'] = $this->template;
        }

        if ($this->style != '') {
            $arreglo['style'] = $this->style;
        }

        $json = new stdClass();
        $json->idFile = $this->idFile;
        $json->validacion = $this->validacion;
        $json->comunica = $arreglo;
        if ($this->status != '') {
            $json->status = $this->status;
        }

        $mensaje = '';

        if (isset($_SESSION[_S]['errorSql'])) {
            $json->errorSql = $_SESSION[_S]['errorSql'];
            //eliminar variable de session errorSql
        }
        echo json_encode($json);
        return json_encode($json);
    }
    public function asJson($string)
    {
        $json = json_decode($string);
        if (phpversion() >= '5.3.0') {
            $error = '';
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    //Sin errores
                    $error = '';
                    break;
                case JSON_ERROR_DEPTH:
                    $error = ' - Excedido tamaño máximo de la pila';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $error = ' - Desbordamiento de buffer o los modos no coinciden';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $error = ' - Encontrado carácter de control no esperado';
                    break;
                case JSON_ERROR_SYNTAX:
                    $error = ' - Error de sintaxis, JSON mal formado';
                    break;
                case JSON_ERROR_UTF8:
                    $error = ' - Caracteres UTF-8 malformados, posiblemente están mal codificados';
                    break;
                default:
                    $error = ' - Error desconocido';
                    break;
            }
            if ($error != '') {
                echo $error;
            } else {
                return $json;
            }

        } else {
            return $json;
        }

    }
    public function asObjetoJs($obj)
    {
        $de = json_encode($obj);
        return $de = str_replace(array('"{', '}"', '\\'), array('', '', ''), $de);
    }
    public function ponBoton($p)
    {
        if (!isset($p->id)) {
            print_r($p);
        }

        $this->elemento = 'Boton';
        $Type = array('button', 'reset', 'submit');
        $atributos = array('form', 'size', 'maxlength', 'pattern', 'value', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $Boton = $atributos->objeto;
        $atributos = $atributos->cadena;
        $atributos .= 'type="' . ((isset($Boton->type)) ? ((in_array($Boton->type, $Type)) ? $Boton->type : 'button') : 'button') . '" ';
        $texto = (isset($Boton->texto)) ? isset($Boton->value) : ((isset($Boton->value)) ? $Boton->value : ' ');
        $clase = $Boton->class;
        $color = (isset($Boton->color)) ? $Boton->color : "default";
        switch ($color) {
            case 'rojo':
                $color = "danger";
                break;
            case 'amarillo':
                $color = "warning";
                break;
            case 'cielo':
                $color = "info";
                break;
            case 'verde':
                $color = "success";
                break;
            case 'azul':
                $color = "primary";
                break;
            case 'gris':
                $color = "secondary me-1";
                break;
            case 'blanco':
            default:
                $color = "default";
                break;
        }

        $icon = '';
        //ALFONSO 2018-02-13 Se quita la fuente para que muestre los iconos de glyphicon2
        //if( isset($Boton->icon) ){ $icon = '<span style="font-family:\'Glyphicons Halflings\'" class="glyphicon glyphicon-'.$Boton->icon.'"></span>'; }

        if (isset($Boton->icon)) {
            $icon = '<span class="fa fa-' . $Boton->icon . '"></span>';
        }

        return '<button class="btn btn-outline-' . $color . ' ' . $clase . '" ' . $atributos . '>' . $icon . $texto . '</button>';
    }

    /**
     * Genera una tarjeta de opción visual (card_opcion) para menús de selección.
     * Estilo definido en css/styles.css.
     *
     * @param  object $p Configuración:
     *   - id     (string, requerido) ID HTML del elemento
     *   - titulo (string) Texto del header (cabecera guinda)
     *   - img    (string) Ruta de la imagen
     *   - icon   (string) Clases Font Awesome (alternativa a img)
     *   - iconColor (string) Color CSS del icono (ej. #8E418D)
     *   - label  (string) Texto debajo de la imagen
     *   - title  (string) Tooltip nativo del navegador
     *   - class  (string) Clases CSS adicionales
     *   - active (bool)   Marca la tarjeta como activa
     * @return string HTML de la tarjeta
     */
    public function ponCard($p): string
    {
        if (!isset($p->id)) {
            return '';
        }

        $titulo = $p->titulo ?? '';
        $label  = $p->label ?? '';
        $img       = $p->img ?? '';
        $icon      = $p->icon ?? '';
        $iconColor = $p->iconColor ?? '#8E418D';
        $title  = isset($p->title) ? ' title="' . htmlspecialchars($p->title) . '"' : '';
        $class  = isset($p->class) ? ' ' . $p->class : '';
        $active = !empty($p->active) ? ' active' : '';

        $attrs = '';
        if (!empty($p->attrs) && is_array($p->attrs)) {
            foreach ($p->attrs as $k => $v) {
                $attrs .= ' ' . htmlspecialchars((string) $k) . '="' . htmlspecialchars((string) $v) . '"';
            }
        } elseif (!empty($p->attrs) && is_string($p->attrs)) {
            $attrs = ' ' . $p->attrs;
        }

        $html = '<div class="card_opcion' . $active . $class . '" id="' . $p->id . '"' . $title . $attrs . '>';

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
     *
     * @param  array $cards Array de objetos stdClass (ver ponCard)
     * @return string HTML del wrapper con todas las tarjetas dentro
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

    public function ponInput($p)
    {
        $this->elemento = 'Input';
        $noType = array('file', 'image', 'radio', 'range', 'reset', 'submit');
        $atributos = array('form', 'size', 'maxlength', 'pattern', 'placeholder', 'value', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $input = $atributos->objeto;
        $atributos = $atributos->cadena;
        $atributos .= 'type="' . ((isset($input->type)) ? ((!in_array($input->type, $noType)) ? $input->type : 'text') : 'text') . '" '; //valores que puede tomar email,password,hidden,tel,text
        $atributos .= (isset($input->maxlength) && !isset($input->size)) ? 'size="' . $input->maxlength . '" ' : '';
        $readOnly = (isset($p->readonly) ? 'readonly' : '');
        $lista = '';
        $bo = $ton = '';
        $decimales = isset($p->decimales) ? $p->decimales : "2";
        if (isset($input->label)) {
            $la = '<label class="control-label" for="' . $input->id . '">' . $input->label . ' ';
            $bel = '</label>';
        } else {
            $la = $bel = '';
        }

        if ((isset($input->autocompletar) && $input->autocompletar) || (isset($input->autocomplete) && $input->autocomplete)) {
            if ((isset($input->campoVal) && isset($input->tabla)) || isset($input->lista)) {
                $atributos .= 'list="listaAutocomplete-' . $input->id . '" ';
                $lista = '<datalist id="listaAutocomplete-' . $input->id . '">';
                if (isset($input->lista)) {
                    if (is_array($input->lista)) {
                        foreach ($input->lista as $opcion) {
                            $lista .= '<option value="' . $opcion . '" />';
                        }
                    } else {
                        echo 'METODO: ' . $this->metodo . ' ERROR: la lista para autocloplete debe ser un array en el input con id=' . $input->id;
                    }

                } else {
                    $desdecaracter = (isset($input->desdecaracter)) ? $input->desdecaracter : 1;
                    $reverse = (isset($input->reverse)) ? 1 : 2;
                    $filtroUr = (isset($input->filtroUr) && is_bool($input->filtroUr)) ? $input->filtroUr : 0;
                    $filtroExtra = (isset($input->filtroExtra) && is_string($input->filtroUr)) ? $this->code($input->filtroExtra) : '';
                    $separador = (isset($input->separador)) ? $input->separador : ' - ';
                    $campoVal = $this->code($input->campoVal);
                    $camposText = (isset($input->camposText)) ? $this->code($input->camposText) : '';
                    $tabla = $this->code($input->tabla);
                    $tablas = isset($input->tablas) ? $this->code($input->tablas) : " "; /*PARA EL USO DE DOS TABLAS EN UN JOIN EN LA CONSULTA se debe enviar el USING ej. INNER JOIN cat_proveedores USING ( rfc_id )  */
                    $this->mixScript('
                        $("#' . $input->id . '").prop("cargando-autocomplete",false);
                        $("#' . $input->id . '").keyup(function(){
                            if( $(this).val().length == 0 ) $("#listaAutocomplete-' . $input->id . '").empty();
                            if( $(this).val().length >= ' . $desdecaracter . '  && $("#listaAutocomplete-' . $input->id . '").children().length == 0 && !$(this).prop("cargando-autocomplete")){
                                $(this).prop("cargando-autocomplete",true);
                                comunica("lib/WebResponse.php",{
                                    metodoWebResponse:"autocomplete",
                                    reverse:' . $reverse . ',
                                    busca:$(this).val(),
                                    v:"' . $campoVal . '",
                                    te:"' . $camposText . '",
                                    ta:"' . $tabla . '",
                                    lista:"' . $input->id . '",
                                    s:"' . $separador . '",
                                    extra:"' . $filtroExtra . '",
                                    porUr:' . $filtroUr . ',
                                    tablas:"' . $tablas . '"
                                },false,false);
                            }
                        });
                    ');
                }
                $lista .= '</datalist>';
            } else {
                echo 'METODO: ' . $this->metodo . ' ERROR: para hacer un input autocompletable es necesario especificar una lista(array) o la tabla(tabla) y el campo(campoVal) donde buscara con un query';
            }
        } elseif (isset($input->validaAjax)) {
            if (is_string($input->validaAjax) && $input->validaAjax != '') {
                if (!isset($input->datosExtra)) {
                    $input->datosExtra = new stdClass();
                }

                if (isset($input->siExiste)) {
                    $input->datosExtra->siExiste = $input->siExiste;
                }

                // $input->datosExtra->metodoWebResponse = 'validaAjax';
                // $input->datosExtra->valor = '{$(this).val()}';
                $input->datosExtra->elemento = $input->id;
                $input->datosExtra->validaCon = $input->validaAjax;
                $input->datosExtra->mensajeValido = $input->mensajeValido;
                $input->datosExtra->mensajeNoValido = $input->mensajeNoValido;
                $parametros = json_encode($input->datosExtra);
                $parametros = str_replace(array('"{', '}"'), '', $parametros);
                $this->mixScript('
                    $("#' . $input->id . '").prop("validaajax",false);
                    $("#' . $input->id . '").inputValidacionAjax({prm:' . $parametros . '});
                ');
            }
        }
        $clase = 'form-control ' . $input->class;
        $signo = '';
        $validaPaste = '';
        if (isset($input->formato)) {
            $clase .= ' form-group ' . $input->formato;
            switch ($input->formato) {
                case 'fecha':
                    $bo = '<div id="date-' . $input->id . '" class="input-group date" data-date="" data-date-format="dd/mm/yyyy">';
                    $ton = '<span class="input-group-btn">
                        <button class="btn btn-outline-secondary" tabindex=-1 type="button">
                            <i class="fa fa-calendar-days" style="font-size:15px"></i>
                        </button>
                        </span></div>';
                    $this->mixScript(' $("#date-' . $input->id
                        . '").datepicker({"keyboardNavigation":true,"language":"es"}).on("changeDate", function(e){'
                        . ((isset($input->cambioFecha)) ? $input->cambioFecha : '')
                        . '});');
                    break;
                case 'entero':
                    $this->mixScript('
                        $("#' . $input->id . '").keypress(function(event){
                            var anularTecla = false;
                            var_per = [0,8];
                            var esPermitido = var_per.indexOf( event.which ) >= 0;
                            anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                            if( anularTecla ) event.preventDefault();
                        });
                    ');
                    $validaPaste = 'if( isNaN(Number(data)) )e.preventDefault();';
                    break;
                case 'moneda':
                    $signo = ',"$ "';
                case 'numero':
                case 'decimal':
                    $validaPaste = 'if( isNaN(Number(data)) )e.preventDefault();';
                    $signo = ($signo != '') ? $signo : ', ""';
                    $this->mixScript('
                        $("#' . $input->id . '").keypress(function(event){
                            var anularTecla = false;
                            var_per  = [0,8];
                            //var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                            var esPermitido = var_per.indexOf( event.which ) >= 0;
                            if(event.which == 46)  anularTecla = $(this).val().indexOf(".") >= 0;
                            else anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                            ' . ((!empty($input->positivo) && $input->positivo === true) ? '' : '
                                if(event.which == 45){
                                    if( $(this).val() == "" ){
                                        anularTecla = false;
                                    }else if( ($(this).val().indexOf("-") >= 0 && $(this).val().length > 1) || $(this).val().length > 1)
                                        $(this).val( $(this).val() * -1 );
                                }
                            ') . '
                            if( anularTecla ) event.preventDefault();
                        });
                        $("#' . $input->id . '").blur(function(event){
                            if( $(this).val() != 0 ){
                                $(this).val( formatNumber( parseFloat( $(this).val() ).toFixed(' . $decimales . ')' . $signo . ', ' . $decimales . ') );
                            }else{
                                $(this).val( formatNumber( 0.00' . $signo . ', ' . $decimales . ' ) );
                            }
                        });
                        $("#' . $input->id . '").focus(function(event){
                            $(this).val( unformatNumber($(this).val()) );
                            if( $(this).val() == 0 ) $(this).val("");
                        });
                    ');
                    break;
            }
        }
        $this->mixScript('
            $("#' . $input->id . '").bind("paste", function(e) {
                var data = e.originalEvent.clipboardData.getData("Text");
                ' . $validaPaste . '
            });
        ');
        $input->enTd = (isset($input->enTd)) ? $input->enTd : 0;
        $input->enTd = (isset($input->enDiv)) ? 3 : $input->enTd;
        switch ($input->enTd) {
            case 1:
                return '<td><div class="form-group has-feedback">' . $la . $bel . '<input ' . $readOnly . ' class="' . $clase . '" ' . $atributos . '/>' . $lista . '</div></td>';
                break;
            case 2:
                return '<td>' . $la . $bel . '</td><td><div class="form-group has-feedback"><input  ' . $readOnly . ' class="' . $clase . '" ' . $atributos . '/>' . $lista . '</div></td>';
                break;
            case 3:
                return '<div class="form-group mb-2">' . $la . $bel . $bo . '<input ' . $readOnly . ' class="' . $clase . '" ' . $atributos . '/>' . $ton . $lista . '</div>';
                break;
            default:
                return $la . $bel . '<input ' . $readOnly . '  class="' . $clase . '" ' . $atributos . '/>' . $lista;
                break;
        }
    }
    public function ponRadio($p)
    {
        $this->elemento = 'Radio';
        $atributos = array('form', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $radio = $atributos->objeto;
        $atributos = $atributos->cadena;
        $titulo = (isset($radio->titulo)) ? $radio->titulo : '';
        $selecciona = (isset($radio->selecciona)) ? $radio->selecciona : null;
        $separador = (isset($radio->separador)) ? $radio->separador : ' ';
        $vertical = isset($radio->vertical) ? true : false;

        $rad = '';
        if (isset($radio->sql)) {
            $params = $radio->params ?? array();
            $this->conectar();
            $this->setSql($radio->sql);
            $this->consultar($params);
            if ($this->getNumeroRegistros() > 0) {
                $i = (isset($radio->cols)) ? $radio->cols : 1;
                while ($r = $this->sacar()) {
                    $val = '';
                    $desc = '';
                    $camposVal = 0;
                    if (isset($radio->camposVal)) {
                        foreach (explode(",", $radio->camposVal) as $value) {
                            $val .= $r[$value];
                        }
                    } else {
                        $val .= $r[0];
                    }

                    $rad_check = ($selecciona == $r[$camposVal]) ? 'checked="checked"' : '';
                    if (isset($radio->camposText)) {
                        foreach (explode(",", $radio->camposText) as $value) {
                            $desc .= $r[$value] . $separador;
                        }
                    } elseif (isset($r[1])) {
                        $desc .= $r[0] . $separador . $r[1];
                    } else {
                        $desc .= $r[0];
                    }

                    $attr = '';
                    if (isset($radio->comoAtr)) {
                        foreach (explode(",", $radio->comoAtr) as $atributo) {
                            $attr .= $atributo . '="' . $r[$atributo] . '"';
                        }
                    }

                    $rad .= '<td class="radio' . (($vertical) ? '' : 's') . '"><label><input ' . $attr . ' type="radio" ' . $atributos . ' value="' . $val . '" ' . $rad_check . ' />' . $desc . '</label></td>';
                    $rad .= ($vertical) ? '</tr>' : '';
                }
                $rad = '<div class="form-group">
                <table id="tablaRadio' . $radio->name . '" class="table table-bordered table-striped">
                    <caption><strong>' . $titulo . '</strong></caption>
                    <tr>' . $rad . '</tr>
                </table>';
            }
        } else {
            foreach ($radio->values as $val => $desc) {
                $rad_check = ($selecciona == $val) ? 'checked=\"checked\"' : '';
                $rad .= '<td class="radio' . (($vertical) ? '' : 's') . '"><label><input type="radio" ' . $atributos . ' value="' . $val . '" ' . $rad_check . ' />' . $desc . '</label></td>';
                $rad .= ($vertical == true) ? '</tr><tr>' : '';
            }
            $rad = '
                <table class="table table-bordered table-striped">
                    <thead><strong>' . $titulo . '</strong></thead>
                    <tr>' . $rad . '</tr>
                </table>';
        }
        return $rad;
    }

    public function ponRadioFlex($p)
    {
        $this->elemento = 'Radio';
        $atributos = array('form', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $radio = $atributos->objeto;
        $atributos = $atributos->cadena;
        $titulo = (isset($radio->titulo)) ? $radio->titulo : '';
        $selecciona = (isset($radio->selecciona)) ? $radio->selecciona : null;
        $separador = (isset($radio->separador)) ? $radio->separador : ' ';
        $vertical = isset($radio->vertical) ? true : false;

        $rad = '';
        if (isset($radio->sql)) {
            $params = $radio->params ?? array();
            $this->conectar();
            $this->setSql($radio->sql);
            $this->consultar($params);
            if ($this->getNumeroRegistros() > 0) {
                $i = (isset($radio->cols)) ? $radio->cols : 1;
                while ($r = $this->sacar()) {
                    $val = '';
                    $desc = '';
                    $camposVal = 0;
                    if (isset($radio->camposVal)) {
                        foreach (explode(",", $radio->camposVal) as $value) {
                            $val .= $r[$value];
                        }
                    } else {
                        $val .= $r[0];
                    }

                    $rad_check = ($selecciona == $r[$camposVal]) ? 'checked="checked"' : '';
                    if (isset($radio->camposText)) {
                        foreach (explode(",", $radio->camposText) as $value) {
                            $desc .= $r[$value] . $separador;
                        }
                    } elseif (isset($r[1])) {
                        $desc .= $r[0] . $separador . $r[1];
                    } else {
                        $desc .= $r[0];
                    }

                    $attr = '';
                    if (isset($radio->comoAtr)) {
                        foreach (explode(",", $radio->comoAtr) as $atributo) {
                            $attr .= $atributo . '="' . $r[$atributo] . '"';
                        }
                    }

                    $classVertical = ($vertical ? ' ;flex-direction:column;' : '');
                    $rad .= '<div  style="flex-grow:1; border:1px solid #CCC ' . $classVertical . '">
                        <label>
                            <input ' . $attr . ' type="radio" ' . $atributos . ' value="' . $val . '" ' . $rad_check . ' />' . $desc . '
                        </label>
                        </div>';
                }
                $rad = '
                <div class="form-group">
                    <div id="tablaRadio' . $radio->name . '" style="display:flex; flex-wrap: wrap; background-color:#EAEAEA" class="my-3">
                        <strong>' . $titulo . '</strong>
                        ' . $rad . '
                    </div>
                </div>';
            }
        } else {
            foreach ($radio->values as $val => $desc) {
                $rad_check = ($selecciona == $val) ? 'checked=\"checked\"' : '';
                $classVertical = ($vertical ? ' ;flex-direction:column;' : '');
                $rad .= '<div class="p-2"
                    style="flex-grow:1; border:1px solid #CCC ' . $classVertical . '">
                    <label>
                        <input type="radio" ' . $atributos . ' value="' . $val . '" ' . $rad_check . ' class="m-10" />
                        ' . $desc . '
                    </label>
                    </div>';
            }
            $rad = '
                <div>
                    <strong>' . $titulo . '</strong>
                    <div style="display:flex; flex-wrap: wrap; background-color:#EAEAEA" class="my-3">
                        ' . $rad . '
                    </div>
                </div>';
        }
        return $rad;
    }

    public function ponCheck($p)
    {
        $this->elemento = 'Check';
        $atributos = array('form', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $radio = $atributos->objeto;
        $atributos = $atributos->cadena;
        $titulo = (isset($radio->titulo)) ? $radio->titulo : '';
        $selecciona = (isset($radio->selecciona)) ? $radio->selecciona : null;
        $selecionados = (isset($radio->selecionados)) ? $radio->selecionados : array();
        $names = (isset($radio->names)) ? $radio->names : array();
        $separador = (isset($radio->separador)) ? $radio->separador : ' ';
        $vertical = isset($radio->vertical) ? true : false;
        // print_r($names);
        $rad = '';
        if (isset($radio->sql)) {
            $params = $radio->params ?? array();
            $this->conectar();
            $this->setSql($radio->sql);
            $this->consultar($params);
            if ($this->getNumeroRegistros() > 0) {
                $i = (isset($radio->cols)) ? $radio->cols : 1;
                while ($r = $this->extraer()) {
                    $val = '';
                    $desc = '';
                    $key = '';
                    $camposVal = 0;
                    if (isset($radio->camposVal)) {
                        foreach (explode(",", $radio->camposVal) as $value) {
                            $key .= $value;
                            $val .= $r[$value];
                        }
                        $rad_check = ($selecciona == $r[$key]) ? 'checked="checked"' : '';
                    } else {
                        $val .= $r[0];
                        $rad_check = ($selecciona == $r[0]) ? 'checked="checked"' : '';
                    }

                    if (isset($radio->camposText)) {
                        foreach (explode(",", $radio->camposText) as $value) {
                            $desc .= $r[$value] . $separador;
                        }
                    } elseif (isset($r[1])) {
                        $desc .= $r[0] . $separador . $r[1];
                    } else {
                        $desc .= $r[0];
                    }

                    $atributos2 = $atributos;

                    if (isset($radio->id)) {
                        $atributos2 = str_replace('id="' . $radio->id . '"', 'id="' . str_replace("[]", "", $radio->id) . '_' . $val . '"', $atributos);
                    }

                    $rad .= '<label class="' . $radio->class . ' ' . ((!$vertical) ? 'checkbox-inline' : '') . ' " ><input type="checkbox" ' . $atributos2 . ' values="' . $val . '" />' . $desc . '</label>';
                }
            }
        } else {
            foreach ($radio->values as $val => $desc) {
                $rad_check = (in_array($val, $selecionados)) ? 'checked=\"checked\"' : '';
                $name = (isset($names[$val])) ? ' name = "' . $names[$val] . '"' : '';
                $rad .= '<td><label><input type="checkbox" class="' . $radio->class . '"' . $name . ' ' . $atributos . ' value="' . $val . '" ' . $rad_check . ' />' . $desc . '</label></td>';
                $rad .= ($vertical == true) ? '</tr><tr>' : '';
            }
            $rad = '
                <table>
                    <caption>' . $titulo . '</caption>
                    <tr>' . $rad . '</tr>
                </table>';
        }
        return $rad;
    }
    public function ponCheck2($p)
    {
        if (!is_object($p)) {
            return '<script type="text/javascript" language="javascript">console.log("' . ($p->debug ? 'funcion ponCheck2:Error;' : '') . ' elemento no std Class");</script>';
            return false;
        }
        if (!isset($p->id)) {
            return '<script type="text/javascript" language="javascript">console.log(" ' . ($p->debug ? 'funcion ponCheck2:Error;' : '') . ' elemento no tiene identificador ID");</script>';
            return false;
        }
        $che = '';
        $div = isset($p->enDiv) ? $p->enDiv : true;

        if ($div) {
            $che .= '<div class="wr-checbo2-div" style="width:' . (isset($p->orientacion) ? ($p->orientacion == "H" ? '150' : '75') : '75') . 'px;">';
        }

        $che .= '    <div  class="wr-checbo2-etiqueta" style="display:table-cell; vertical-align:middle; font-size:10px">' . (isset($p->label) ? strtoupper($p->label) : '<span style="color:#999;">&nbsp;</span>') . '</div>
                        ' . (isset($p->orientacion) ? ($p->orientacion == "H" ? '' : '<br/>') : '<br/>') . '
                        ' . (isset($p->orientacion) ? ($p->orientacion == "H" ?
            ' <div style="display:table-cell; text-align:center; padding-left:5px;">' : '<div style="display:table-cell; text-align:center; width:75px;">')
            :
            '<div style="display:table-cell; text-align:center; width:75px;">') . '
                            <div id="div_' . $p->id . '" value="1" class="wr-checbo2" ' . (isset($p->checked) ? ' checked="checked" ' : '') . '>
                                <div class="' . (isset($p->checked) ? ' wr-ch2-checked ' : '') . '"><span class="fa fa-check "></span>
                                </div>
                                <input type="checkbox" style="display:none" name="' . (isset($p->name) ? $p->name : $p->id) . '" id="' . $p->id . '" value="1" ' . (isset($p->checked) ? ' checked="checked" ' : '') . '/>
                                <script type="text/javascript" language="javascript">
                                    $("#div_' . $p->id . '").click(function(e){
                                        ' . (isset($p->debug) && $p->debug ? ' console.log("Elemento:#' . $p->id . '; evento:click;"); ' : '') . '
                                        if($(this).attr("checked")){
                                            ' . (isset($p->debug) && $p->debug ? ' console.log("Elemento:#' . $p->id . '; cambio de seleccionado a no seleccionado"); ' : '') . '
                                            $("#div_' . $p->id . ' > div").removeClass("wr-ch2-checked");
                                            $(this).removeAttr("checked");
                                            $("#' . $p->id . '").removeAttr("checked");
                                        }else{
                                            ' . (isset($p->debug) && $p->debug ? ' console.log("Elemento:#' . $p->id . '; cambio de no seleccionado a seleccionado"); ' : '') . '
                                            $("#div_' . $p->id . ' > div").addClass("wr-ch2-checked");
                                            $(this).attr("checked","checked");
                                            $("#' . $p->id . '").prop("checked","checked");
                                        }
                                        ' . (isset($p->onClick) ? $p->onClick : '') . '
                                    });
                                </script>
                        </div>
                    </div>';
        if ($div) {
            $che .= '    </div>';
        }

        return $che;
    }
    public function ponOnoff($p)
    {
        if (!is_object($p)) {
            return '<script type="text/javascript" language="javascript">console.log("' . ($p->debug ? 'funcion ponOnoff:Error;' : '') . ' elemento no std Class");</script>';
            return false;
        }
        if (!isset($p->id)) {
            return '<script type="text/javascript" language="javascript">console.log(" ' . ($p->debug ? 'funcion ponOnoff:Error;' : '') . ' elemento no tiene identificador ID");</script>';
            return false;
        }
        $estilo = "padding:12px;";
        if (isset($p->estilo)) {
            switch ($p->estilo) {
                default :
                    $estilo = "padding:15px;";
                    break;
                case "estrecho":
                    $estilo = "padding:0px;";
                    break;
                case "mediano":
                    $estilo = "padding:10px;";
                    break;
                case "xx":
                    $estilo = "padding:5px;";
                    break;
            }
        }

        $onoff = '
            <div style="background-color:#333; width:' . (isset($p->width) ? $p->width : '100%') . '; ' . $estilo . ' color:#FFF;" >
                <span style="display:inline-block; vertical-align:middle">' . (isset($p->label) ? $p->label : '') . '</span>
                <div id="div_' . $p->id . '"    style="" class="wr-onoff" ' . (isset($p->checked) ? ($p->checked ? ' checked="checked"' : '') : '') . ' >
                    <span style="width:50%; text-align:center; display:inline-block">ON</span>
                    <span syle="width:47%; text-align:center; display:inline-block">OFF</span>
                    <div style="" class="wr-onoff-cover ' . (isset($p->checked) ? ($p->checked ? ' wr-onoff-checked ' : ' wr-onoff-unchecked ') : ' wr-onoff-checked ') . '">&nbsp;</div>
                    <input type="checkbox" style="display:none" name="xx" id="' . $p->id . '" value="1" ' . (isset($p->checked) ? ($p->checked ? ' checked="checked"' : '') : '') . '  />
                    <script type="text/javascript" language="javascript">
                    $("#div_' . $p->id . '").click(function(){
                        if($(this).attr("checked")){
                            $("#div_' . $p->id . ' > div").addClass("wr-onoff-unchecked").removeClass("wr-onoff-checked");
                            $(this).removeAttr("checked");
                            $("#' . $p->id . '").removeAttr("checked");
                        }else{
                            $("#div_' . $p->id . ' > div").addClass("wr-onoff-checked").removeClass("wr-onoff-unchecked");
                            $(this).attr("checked","checked");
                            $("#' . $p->id . '").attr("checked","checked");
                        }
                    });
                    </script>
                </div>
            </div>
        ';
        return $onoff;
    }
    public function ponTextarea($p)
    {
        $this->elemento = 'Textarea';
        $atributos = array('form', 'rows', 'cols', 'placeholder', 'maxlength', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $textarea = $atributos->objeto;
        $atributos = $atributos->cadena;
        $texto = (isset($textarea->text)) ? $textarea->text : ((isset($textarea->texto)) ? $textarea->texto : '');
        $clase = $textarea->class;

        $label = (isset($textarea->label)) ? '<label  class="control-label" for="' . $textarea->id . '">' . $textarea->label . '</label>' : '';
        $textarea->enTd = (isset($textarea->enTd)) ? $textarea->enTd : 0;
        $textarea->enTd = (isset($textarea->enDiv)) ? 3 : $textarea->enTd;
        switch ($textarea->enTd) {
            case 1:
                return '<td><div class="form-group has-feedback">' . $label . '<textarea ' . $atributos . '>' . $texto . '</textarea></div></td>';
                break;
            case 2:
                return '<td>' . $label . '</td><td><div class="form-group has-feedback"><textarea ' . $atributos . '>' . $texto . '</textarea></div></td>';
                break;
            case 3:
                return '<div class="form-group ">' . $label . '<textarea class="form-control ' . $clase . '" ' . $atributos . '>' . $texto . '</textarea></div>';
                break;
            default:
                return $label . '<div class="form-group "><textarea  class=" form-control ' . $clase . '" ' . $atributos . '>' . $texto . '</textarea></div >';
                break;
        }
    }
    public function ponCombo($p, $old = true)
    {
        if (!isset($p->id)) {
            print_r($p);
        }

        $this->elemento = 'Combo';
        $atributos = array('form', 'size', 'filtro', 'multiple', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos);
        $combo = $atributos->objeto;
        $atributos = $atributos->cadena;
        $selecciona = (isset($p->selecciona)) ? $p->selecciona : '';
        $multiple = (isset($p->multiple)) ? ' multiple' : '';
        $prmSelectpicker = '';
        if (isset($combo->sql)) {
            $eCombo = $this->templateCombo($p, false);
            $this->recargaCombo($p);
            return $eCombo;
        } elseif (isset($combo->opciones) && is_array($combo->opciones)) {
            $opciones = '';
            foreach ($combo->opciones as $val => $p) {
                if ($selecciona == $val) {
                    $prmSelectpicker = 'selectedText: "' . $val . '"';
                }

                $selected = ($selecciona == $val) ? 'selected ' : '';
                $opciones .= '<option value ="' . $val . '" ' . $selected . '>' . $val . ' - ' . $p . '</option>';
                $textOption = $val . ' - ' . $p;
            }
            $clase = 'class="form-control  selectpicker show-tick ' . $combo->class . '"';
            $combo->enTd = (isset($combo->enTd)) ? $combo->enTd : 0;
            $combo->enTd = (isset($combo->enDiv)) ? 3 : $combo->enTd;
            $horizontal = '';
            if (isset($combo->horizontal)) {
                $combo->enTd = 4;
                $horizontal = "class='col-md-{$combo->horizontal}'";
            }
            $label = (isset($combo->label)) ? '<label ' . $horizontal . ' for="' . $combo->id . '">' . $combo->label . '</label>' : '';
            $select = '<select data-size="5" ' . $atributos . $clase . $multiple . ' >' . $opciones . '</select>';
            $this->mixScript('$("#' . $combo->id . '").selectpicker({' . $prmSelectpicker . '});');

            switch ($combo->enTd) {
                case 0:
                    return $label . $select;
                    break;
                case 1:
                    return '<td>' . $label . $select;
                    break;
                case 2:
                    return '<td>' . $label . '</td><td>' . $select . '</td>';
                    break;
                case 3:
                    return '<div class="form-group ">' . $label . $select . '</div>';
                    break;
                case 4:
                    $horizontal = 12 - $combo->horizontal;
                    return '<div class="form-group ">' . $label . '<div class="col-md-' . $horizontal . '">' . $select . '</div></div>';
                    break;
            }
        }
    }

    public function templateCombo($p, $eventos = true)
    {
        $this->elemento = 'Combo';
        $tmpAdicional = '';
        $horizontal = '';
        $atributos = array('form', 'size', 'filtro', 'tabindex');
        $atributos = $this->recibeAtributos($p, $atributos, $eventos);
        $combo = $atributos->objeto;
        $atributos = $atributos->cadena;
        $option = (isset($combo->mensaje)) ? '<option value="">' . $combo->mensaje . '</option>' : '';
        $combo->enTd = (isset($combo->enTd)) ? $combo->enTd : 0;
        $combo->enTd = (isset($combo->enDiv)) ? 3 : $combo->enTd;
        $clase = 'class="form-control selectpicker show-tick ' . $combo->class . '"';
        $search = (isset($combo->search) && $combo->search == true) ? ' data-live-search="true" ' : '';
        $container = (isset($combo->container) && !empty($combo->container)) ? 'data-container="' . $combo->container . '" ' : '';
        $multiple = (isset($p->multiple)) ? ' multiple' : '';
        if (isset($combo->comoAtr)) {
            foreach (explode(",", $combo->comoAtr) as $atributo) {
                $tmpAdicional .= $atributo . '="${' . $atributo . '}" ';
            }
        }

        $this->mixSCript('$.template( "tmpl-combo-' . $combo->id . '", \'<option {{if disabled }} disabled {{else}} value ="${val}" ' . $tmpAdicional . ' {{if selected }} selected {{/if}} {{/if}} ><label>${texto}</label></option>\' );');
        $this->mixStyle('
            .dropdown-menu.show {/*Se agrega estilos para que evitar el desbordamiento de los items del  select cuando hay textos muy largos*/
                max-width: 100% !important;
                word-wrap: break-word !important;
            }
            .dropdown-menu .dropdown-item .text { /*Trunca texto*/
                display: inline-block !important;
                max-width: 99% !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
        ');

        $horizontal = '';
        if (isset($combo->horizontal)) {
            $combo->enTd = 4;
            $horizontal = "class='col-md-{$combo->horizontal}'";
        }

        $label = (isset($combo->label)) ? '<label ' . $horizontal . ' for="' . $combo->id . '">' . $combo->label . '</label>' : '';

        $select = '<select data-size="5" ' . $search . $container . $atributos . $clase . $multiple . ' >' . $option . '</select>';
        $label = (isset($combo->label)) ? '<label ' . $horizontal . ' for="' . $combo->id . '">' . $combo->label . '</label>' : '';
        $this->mixScript('$("#' . $combo->id . '").selectpicker();');
        switch ($combo->enTd) {
            case 0:
                return $label . $select;
                break;
            case 1:
                return '<td>' . $label . $select;
                break;
            case 2:
                return '<td>' . $label . '</td><td>' . $select . '</td>';
                break;
            case 3:
                return '<div class="form-group mb-4">' . $label . $select . '</div>';
                break;
            case 4:
                $horizontal = 12 - $combo->horizontal;
                return '<div class="form-group ">' . $label . '<div class="col-md-' . $horizontal . '">' . $select . '</div></div>';
                break;
        }
    }

    public function recargaCombo()
    {
        $opc = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $combo = array();
        $separador = (isset($opc->separador)) ? $opc->separador : ' - ';
        $selecciona = (isset($opc->selecciona)) ? $opc->selecciona : '';
        $detonar = (isset($opc->detonar)) ? $opc->detonar : true;
        $mensaje = (isset($opc->mensaje)) ? $opc->mensaje : true;
        $this->comboValorUnico = null;
        $this->valorSeleccionado = null;

        if (isset($opc->sql) && $opc->sql != '') {
            $params = $opc->params ?? array();
            $this->setSql($opc->sql);
            $this->conectar($this->getServerDefault());
            $this->consultar($params);
            $this->desconectar();
            $nr = $this->getNumeroRegistros();
            switch ($nr) {
                case 0:
                    $o = new stdClass();
                    $o->val = '';
                    $o->texto = (isset($opc->textSinRegistro)) ? $opc->textSinRegistro : 'Sin Registro';
                    if (isset($opc->comoAtr)) {
                        foreach (explode(",", $opc->comoAtr) as $key => $value) {
                            $o->$value = null;
                        }
                    }

                    array_push($combo, $o);
                    $detonar = ($detonar) ? 'true' : 'false';
                    break;
                case 1:
                    $this->comboValorUnico = true;
                default:
                    $detonar = ($detonar) ? 'true' : 'false';
                    if ($nr > 1) {
                        if (is_string($mensaje) || (is_bool($mensaje) && $mensaje)) {
                            $o = new stdClass();
                            $o->val = '';
                            $o->texto = (is_string($mensaje)) ? $mensaje : 'Seleccione una opcion de la lista';
                            array_push($combo, $o);
                        }
                    }
                    $agrupando = false;
                    $found = false;
                    while ($r = $this->sacar()) {
                        $o = new stdClass();
                        $o->val = null;
                        $habilitado = true;
                        $row = $r;
                        if (isset($opc->condiciones)) {
                            foreach ($opc->condiciones as $condicion) {
                                switch ($condicion->operador) {
                                    case '=':
                                    case '==':
                                        $habilitado = $habilitado && ($row[$condicion->campo] == $valor);
                                        break;
                                    case '!=':
                                        $habilitado = $habilitado && ($row[$condicion->campo] != $valor);
                                        break;
                                    case '>':
                                        $habilitado = $habilitado && ($row[$condicion->campo] > $valor);
                                        break;
                                    case '>=':
                                        $habilitado = $habilitado && ($row[$condicion->campo] >= $valor);
                                        break;
                                    case '<':
                                        $habilitado = $habilitado && ($row[$condicion->campo] < $valor);
                                        break;
                                    case '<=':
                                        $habilitado = $habilitado && ($row[$condicion->campo] <= $valor);
                                        break;
                                }
                            }
                        }
                        if ($habilitado) {
                            //coloca los campos atributos
                            if (isset($opc->comoAtr)) {
                                foreach (explode(",", $opc->comoAtr) as $key => $value) {
                                    $o->$value = $r[$value];
                                }
                            }

                            // coloca los campos que seran el val
                            if (isset($opc->camposVal)) {
                                foreach (explode(",", $opc->camposVal) as $value) {
                                    $o->val .= $r[$value];
                                }
                            } else {
                                $o->val .= $r[0];
                            }

                            if ($selecciona == $o->val && !$found) {
                                $o->selected = true;
                                $this->valorSeleccionado = $o->val;
                                $found = true;
                            }
                        } else {
                            $o->disabled = true;
                        }
                        $textOption = '';
                        //coloca los campos que se mostraran como texto
                        if (isset($opc->camposText)) {
                            foreach (explode(",", $opc->camposText) as $value) {
                                $textOption .= ($textOption != '') ? $separador : '';
                                $textOption .= $r[$value];
                            }
                            if (isset($opc->soloText) && is_bool($opc->soloText) && $opc->soloText) {
                                $textOption = $textOption;
                            } else {
                                $textOption = $o->val . ' - ' . $textOption;
                            }

                        } elseif (isset($r[1])) {
                            if (isset($opc->soloText) && is_bool($opc->soloText) && $opc->soloText) {
                                $textOption = $r[1];
                            } else {
                                $textOption .= $r[0] . ' - ' . $r[1];
                            }

                        } else {
                            $textOption .= $r[0];
                        }

                        $o->texto = $textOption;
                        array_push($combo, $o);
                        if ($this->comboValorUnico === true) {
                            $this->valorSeleccionado = $this->comboValorUnico = $o->val;
                            $this->mixScript(' $("#' . $opc->id . '").hide(); $("#inputCombo-' . $opc->id . '").remove(); ');
                        } else {
                            if (!is_string($mensaje) && is_null($this->valorSeleccionado) && is_bool($mensaje) && $mensaje === false) {
                                $this->valorSeleccionado = $o->val;
                            }
                            $this->comboValorUnico = null;
                        }
                    }
                    break;
            }
            $this->mixScript('
                recargaCombo("' . $opc->id . '", ' . json_encode($combo) . ', ' . $detonar . ' );
            ');
        }
    }

    public function alerta()
    {
        switch (func_num_args()) {
            case 2:
                if (is_string(func_get_arg(0)) && is_string(func_get_arg(1))) {
                    $this->mixScript('
                        notify("' . func_get_arg(0) . '","warning");
                    ');
                } else {
                    $this->mixScript('console.error("tipo de parametro no valido en funcion alerta");');
                }

                break;
            case 1:
                if (is_object(func_get_arg(0)));
                // onvertir a objeto
                elseif (is_string(func_get_arg(0))) {
                    $this->mixScript('
                        notify("' . func_get_arg(0) . '","warning");
                    ');
                } else {
                    $this->mixScript('console.error("tipo de parametro no valido en funcion alerta");');
                }

                break;
            default:;
                break;
        }
    }

    public function ponBreadcrumbs($texto)
    {
        $this->mixScript('$("nav").menusinpres("agregaBreadcrumbs",{texto:"' . strtoupper($texto) . '",funcion:function(){  while($(this).next().length){ $("nav").menusinpres("quitaBreadcrumbs"); $(this).next().remove();}$(this).remove(); $("nav").menusinpres("quitaBreadcrumbs"); comunica("' . $this->pantalla . '",' . json_encode($_POST) . ');}});');
    }

    public function ponTabla($p)
    {
        $this->elemento = 'Tabla';
        $atributos = array('form', 'size');
        $atributos = $this->recibeAtributos($p, $atributos);
        $tabla = $atributos->objeto;
        $atributos = $atributos->cadena;
        $this->mixScript('
            $("#' . $tabla->id . '").jqGrid({
                datatype: "json",
                colNames:["Ur","Nombre", "Nombre"],
                colModel:[
                    {name:"id",index:"id", width:55},
                    {name:"invdate",index:"invdate"},
                    {name:"name",index:"name asc, invdate"}
                ],
                rowNum:10,
                rowList:[10,20,30],
                pager: "#navegacion-tablaProyectos",
                sortname: "id",
                viewrecords: true,
                sortorder: "desc",
                caption:"JSON Example"
            });
            $("#tablaProyectos").jqGrid("navGrid","#pager2",{edit:false,add:false,del:false});
        ');
        return '
            <table ' . $atributos . '></table>
            <div id="navegacion-' . $tabla->id . '"></div>
        ';
    }
    /*

    file: control que permite al usuario seleccionar un archivo. Utilice el atributo accept para definir los tipos de
    archivos que el control puede seleccionar.image: Un botón gráfico de envío. Debe utilizar el atributo src para
    definir el origen de la imagen y el atributo alt para definir un texto alternativo. Puede utilizar los atributos
    de altura y anchura para definir el tamaño de la imagen en píxeles.    radio: Un botón de radio. Debe utilizar el
    atributo de valor para definir el valor presentado por este concepto. Utilice el atributo checked para indicar
    si el elemento está seleccionado por defecto. Los botones de radio que tienen el mismo valor para el atributo
    e nombre están en el mismo "grupo de botones de radio": sólo un botón de opción en un grupo puede ser seleccionado
    a la vez.
    range: HTML5 Un control para introducir un número cuyo valor exacto no es importante.
    Este tipo de control se utilizan los siguientes valores por defecto si no se especifican los atributos correspondientes:
    min: 0
    max: 100
    value: min + (max-min) / 2, o si min max es menor que min
    step: 1
    reset: Un botón que restaura el contenido de la forma a los valores predeterminados.
    submit: Un botón que envía el formulario.

    accept:Si el valor del atributo de tipo es el file, este atributo indica los tipos de archivos que el servidor acepta, de lo contrario, se ignora. El valor debe ser una lista separada por comas de único tipo de contenido especificadores:Un tipo MIME válido sin extensiones
    audio / * representa los archivos de sonido HTML5
    video / * representa los archivos de vídeo HTML5
    image / * representa los archivos de imagen HTML5

    checked Cuando el valor del atributo type es radio o casilla de verificación, la presencia de este atributo booleano indica que el control está seleccionado por defecto, de lo contrario, se ignora.
    formmethod:    Si el elemento de entrada es un botón o imagen submit, este atributo especifica el método HTTP que el navegador utiliza para enviar el formulario. Los valores posibles son: post get

    height:Si el valor del atributo de type es image, este atributo define la altura de la imagen visualizada para el botón.
    src: Si el valor del atributo de tipo es la imagen
    step: Funciona con los valores mínimo y máximo de los atributos para limitar los incrementos en la que se puede establecer un valor numérico o de fecha y hora. point number. If this attribute is not set to any, the control accepts only values at multiples of the step value greater than the minimum.

    x-moz-errormessage: Esta extensión de Mozilla le permite especificar el mensaje de error que se muestra cuando un campo no valida correctamente.
    button: Un botón con ningún comportamiento predeterminado.
    checkbox: Una casilla de verificación. Debe utilizar el atributo de valor para definir el valor presentado por este concepto. Utilice la checkedattribute para indicar si se ha seleccionado este elemento. También puede utilizar el atributo indeterminada para indicar que la casilla de verificación se encuentra en un estado indeterminado (en la mayoría de plataformas, esto dibuja una línea horizontal a través de la casilla de verificación).
     */
    public function validaFecha($fecha)
    {
        $mesEnCurso = true;
        if (count(explode('-', $fecha)) == 3) {
            list($año, $mes, $dia) = explode('-', $fecha);
            if (checkdate((int)$mes, (int)$dia, (int)$año)) {
                return '"' . $año . '-' . $mes . '-' . $dia . '"';
            } else {
                return '"' . $dia . '-' . $mes . '-' . $año . '"';
            }

        } elseif (count(explode('/', $fecha)) == 3) {
            list($año, $mes, $dia) = explode('/', $fecha);
            if (checkdate((int)$mes, (int)$dia, (int)$año)) {
                return '"' . $año . '-' . $mes . '-' . $dia . '"';
            } else {
                return '"' . $dia . '-' . $mes . '-' . $año . '"';
            }

        } else {
            return 'NULL';
        }

    }

    private function autocomplete()
    {
        $this->setContenedor('listaAutocomplete-' . $this->prm->lista);
        $val = $this->decode($this->prm->v);
        $texto = $this->decode($this->prm->te);
        $tabla = $this->decode($this->prm->ta);
        $extra = $this->decode($this->prm->extra);
        $tab = $this->decode($this->prm->tablas);
        $porUr = $this->prm->porUr;
        $separador = $this->prm->s;
        $lista = '';
        $and = '';
        if ($porUr) {
            $and = ' AND gpo_id = ' . $_SESSION[_S]['gpo_id'] . ' AND ur_id = ' . $_SESSION[_S]['ur_id'] . ' ';
        }

        if ($extra != '') {
            $and .= $extra;
        }

        if ($this->prm->reverse == 1) {
            $this->setSql('SELECT ' . $val . ((empty($texto)) ? '' : ',' . $texto) . ' FROM ' . $tabla . $tab . ' WHERE ' . $val . ' like "%' . $this->prm->busca . '" ' . $and);
        } else {
            $this->setSql('SELECT ' . $val . ((empty($texto)) ? '' : ',' . $texto) . ' FROM ' . $tabla . $tab . ' WHERE ' . $val . ' like "%' . $this->prm->busca . '%" ' . $and);
        }
        $this->conectar();
        $this->consultar();
        $this->desconectar();
        if (!empty($texto)) {
            $texto = explode(',', ($val . ',' . $texto));
        } else {
            $texto = array($val);
        }
        while ($row = $this->extraer()) {
            $opciones = '';
            foreach ($texto as $campo) {
                $opciones .= ($opciones != '') ? $separador : '';
                $opciones .= $row[$campo];
            }
            $opciones = str_replace($this->prm->busca, '<b>' . $this->prm->busca . '</b>', $opciones);
            $lista .= '<option value="' . $row[$val] . '">' . $opciones . '</option>';
        }
        $this->mixHtml($lista);
        $this->mixScript('$("#' . $this->prm->lista . '").prop("cargando-autocomplete",false);');
    }

    private function validaAjax()
    {
        if (isset($this->prm->validaCon) && $this->prm->validaCon != '') {
            $validacion = consultasValidaAjax($this->prm);
        }
        $params = $validacion->params ?? array();
        $this->setSql($validacion->sql);
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        if ($this->prm->siExiste && !($this->getNumeroRegistros() > 0)) {
            $this->mixScript('
                $("#' . $this->prm->elemento . '").prop("validaajax",false);
                $("#' . $this->prm->elemento . '").inputValidacionAjax({"valido":false});
                if( $("#' . $this->prm->elemento . '").parent().is(".form-group") ){
                    $("#' . $this->prm->elemento . '").parent().addClass("has-error has-feedback");
                    $("#' . $this->prm->elemento . '").focusout(function(){
                        $("#' . $this->prm->elemento . '").parent().removeClass("has-error has-feedback");
                    });
                }
                $("#' . $this->prm->elemento . '").before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; opacity: 0.87; position: sticky; "><div class="formErrorContent">' . $validacion->mensajeNoValido . '</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
            ');
        } elseif (!$this->prm->siExiste && ($this->getNumeroRegistros() > 0)) {
            $this->mixScript('
                if( $("#' . $this->prm->elemento . '").parent().is(".form-group") ){
                    $("#' . $this->prm->elemento . '").parent().addClass("has-error has-feedback");
                    $("#' . $this->prm->elemento . '").focusout(function(){
                        $("#' . $this->prm->elemento . '").parent().removeClass("has-error has-feedback");
                    });
                }
                $("#' . $this->prm->elemento . '").prop("validaajax",false);
                $("#' . $this->prm->elemento . '").inputValidacionAjax({"valido":false});
                $("#' . $this->prm->elemento . '").before(\'<div class="form-validation-field-2formError parentFormlogin-form formError valida-error" style="display:none; opacity: 0.87; position: sticky; "><div class="formErrorContent">' . $validacion->mensajeNoValido . '</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
            ');
        } else {
            $this->mixScript('
                $("#' . $this->prm->elemento . '").prop("validaajax",true);
                $("#' . $this->prm->elemento . '").inputValidacionAjax({"valido":true});
                $("#' . $this->prm->elemento . '").before(\'<div class="form-validation-field-2formError parentFormlogin-form formError valida-verdadero" style="display:none; opacity: 0.87; position: sticky; "><div class="formErrorContent">' . $validacion->mensajeValido . '</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
            ');
        } //valido
        $this->mixScript('
            $( ".formError" ).show("pulsate");
             setTimeout(function() { $( ".formError" ).hide("puff"); }, 5000 );
             setTimeout(function() { $( ".formError" ).remove(); }, 5000 );
        ');
    }

    private function recibeAtributos($p, $attrs, $ponEvento = true)
    {
        $atributos = new stdClass();
        $eventos = array('click', 'dblclick', 'mousedown', 'mouseup', 'keypress', 'keydown', 'keyup', 'change', 'blur');
        $atributos->cadena = '';
        if (is_object($p)) {
            $atributos->objeto = $p;
        } elseif (is_array($p)) {
            $atributos->objeto = (object) $p;
        } elseif (is_string($p)) {
            $atributos->objeto = json_decode($p);
        }

        //switch para descartar los elementos que no es necesario pedir ID
        switch ($this->elemento) {
            case 'Radio':
            case 'Check':
                $Id = 'noId';
                if (isset($atributos->objeto->id) && $this->elemento == 'Check' && strpos($atributos->objeto->id, "[]") !== false) {
                    $Id = 'id';
                }
                break;
            default:
                if (!isset($atributos->objeto->id)) {
                    echo 'no se definio el ID del elemento ' . $this->elemento . ' en metodo: ' . $this->metodo;
                    $atributos->objeto->id = 'sinID';
                };
                //switch que determina que elementos no requieren regresar el id entre los atributos
                switch ($this->elemento) {
                    default:
                        $Id = 'id';
                        break;
                }
        }
        array_push($attrs, $Id, 'name', 'style', 'title', 'required', 'readonly', 'disabled', 'errormessage');
        $atributos->objeto->name = (isset($atributos->objeto->name)) ? $atributos->objeto->name : (($Id != 'noId') ? $atributos->objeto->id : '');
        if (isset($atributos->objeto->lectura) && is_bool($atributos->objeto->lectura) && $atributos->objeto->lectura === true) {
            $atributos->objeto->readonly = true;
            $atributos->objeto->disabled = true;
        }
        foreach ($atributos->objeto as $atributo => $valor) {
            if (in_array(strtolower($atributo), $attrs)) {
                if ($this->elemento == 'Input' && $atributo == 'value' && !empty($valor)) {
                    $valor = str_replace('"', '&quot;', (string) $valor);
                }
                $atributos->cadena .= $atributo . '="' . $valor . '" ';
            } elseif (in_array(strtolower($atributo), $eventos) && $ponEvento) {
                $this->mixScript('$("#' . $atributos->objeto->id . '").' . $atributo . '(function(event){' . $valor . '});');
            }
        }
        if (isset($atributos->objeto->attr) && is_object($atributos->objeto->attr)) {
            foreach ($atributos->objeto->attr as $atributo => $valor) {
                $atributos->cadena .= $atributo . '="' . $valor . '" ';
            }
        }
        $atributos->objeto->class = (isset($atributos->objeto->class)) ? $atributos->objeto->class : '';
        return $atributos;
    }
    /* FUNCIONES PARA LA TABLA WEBRESPONSE */
    public function wrPonTabla()
    {
        $prm = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )

        : null;
        $tabla = new stdClass();
        if (isset($prm->titulo)) {
            $tabla->titulo = $prm->titulo;
        }

        if (isset($prm->id)) {
            $tabla->id = $prm->id;
        } else {
            echo 'ID en ponTabla es necesario';
        }

        if (isset($prm->busqueda)) {
            $tabla->busqueda = $prm->busqueda;
        }

        if (isset($prm->tools)) {
            $tabla->tools = $prm->tools;
        }

        if (isset($prm->pie)) {
            $tabla->pie = $prm->pie;
        }

        if (isset($prm->full)) {
            $tabla->full = $prm->full;
        }

        if (isset($prm->fixed)) {
            $tabla->fixed = $prm->fixed;
        }

        if (isset($prm->encabezado)) {
            $tabla->encabezado = $prm->encabezado;
        }

        if(isset($prm->cabeceraFija)){
            $tabla->cabeceraFija = $prm->cabeceraFija;
        }

        if (isset($prm->filasAmostrar)) {
            $tabla->filasAmostrar = $prm->filasAmostrar;
        }

        if (isset($prm->numFilas)) {
            $this->prm->nav = new stdClass();
            $this->prm->nav->nfDefault = $prm->numFilas;
            $tabla->numFilas = json_decode('{"cambio":"' . $prm->numFilas . '","actual":"0"}');
        }
        if (isset($prm->seleccionable) && is_bool($prm->seleccionable)) {
            $tabla->seleccionable = $prm->seleccionable;
        }

        if (isset($prm->multiseleccionable) && is_bool($prm->multiseleccionable)) {
            $tabla->multiseleccionable = $prm->multiseleccionable;
        }

        if (isset($prm->prmSeleccion) && is_array($prm->prmSeleccion)) {
            $tabla->prmSeleccion = $prm->prmSeleccion;
        }

        if (isset($prm->desplegable) && is_object($prm->desplegable)) {
            $tabla->desplegable = $prm->desplegable;
        }

        if (isset($prm->accion) && is_object($prm->accion)) {
            $tabla->accion = $prm->accion;
        }

        $tabla->esCompacta = (isset($prm->esCompacta) && is_bool($prm->esCompacta)) ?  $prm->esCompacta : false;
        $tabla->cabeceraFija = (isset($prm->cabeceraFija) && is_bool($prm->cabeceraFija)) ?  $prm->cabeceraFija : false;
        $tabla->trDblClick = (isset($prm->trDblClick) && is_string($prm->trDblClick)) ?  $prm->trDblClick : null;
        $tabla->inputFiltro = (isset($prm->inputFiltro) && is_string($prm->inputFiltro)) ?  str_replace('#','', $prm->inputFiltro) : null;
        $customClass = $tabla->esCompacta  ? ' td_compacto l2 ' : '';
        if (isset($prm->template)) {
            $tabla->tmp = $prm->template;
        } else {
            $tabla->tmp = '<tr class="';
            if (isset($tabla->seleccionable) && $tabla->seleccionable) {
                $tabla->tmp .= ' seleccionable ">';
            }

            if($tabla->cabeceraFija){
                $this->mixScript('setTimeout(function() {$("#' .$tabla->id . ' thead").addClass("sticky-top").css("z-index", "100");}, 100 );');
            }

            $colorSeleccion = '';
            $clase = '';
            if (isset($tabla->multiseleccionable) && $tabla->multiseleccionable) {
                $tabla->tmp .= 'multiseleccionable ';
                if (isset($prm->casoSeleccionable) && is_object($prm->casoSeleccionable)) {
                    $tabla->tmp .= $this->wrCasosPonTabla($prm->casoSeleccionable, 'seleccionable', 'noSeleccionable');
                    $colorVerdadero = (isset($prm->casoSeleccionable->colorEnVerdadero) && is_string($prm->casoSeleccionable->colorEnVerdadero)) ? $prm->casoSeleccionable->colorEnVerdadero : '';
                    $colorFalso = (isset($prm->casoSeleccionable->colorEnFalso) && is_string($prm->casoSeleccionable->colorEnFalso)) ? $prm->casoSeleccionable->colorEnFalso : '';
                    $colorSeleccion = ($colorVerdadero != '' || $colorFalso != '') ? $this->wrCasosPonTabla($prm->casoSeleccionable, ' style = " background-color : ' . $colorVerdadero . ' ;" ', ' style = " background-color : ' . $colorFalso . ' ;" ') : '';
                } else {
                    $tabla->tmp .= 'seleccionable';
                }
            }
            if (isset($prm->casosEstado) && is_object($prm->casosEstado)) {
                $generaIfClase = false;
                foreach ($prm->casosEstado->cases as $key => $val) {
                    $prm->casosEstado->cases[$key]->tipo = 'claseFila';
                    if (isset($prm->casosEstado->cases[$key]->clase)) {
                        $generaIfClase = true;
                    }

                }
                if ($generaIfClase) {
                    $tabla->tmp .= $this->wrCasosPonTabla($prm->casosEstado);
                }

                foreach ($prm->casosEstado->cases as $key => $val) {
                    $prm->casosEstado->cases[$key]->tipo = 'colorFila';
                }

                $colorSeleccion = $this->wrCasosPonTabla($prm->casosEstado);
                $clase = '';
            }
            $tabla->tmp .= '">';
            if (isset($tabla->desplegable)) {
                $tabla->tmp .= '
                    <td class="tr-desplegable warning '.$customClass.'">
                        <button class="btn btn-xs btn-outline-secondary">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </button>
                    </td >';
            }
            if (isset($tabla->accion) && (!isset($tabla->seleccionable) || (isset($tabla->seleccionable) && !$tabla->seleccionable))) {
                $tabla->tmp .= '<td class="acciones warning '.$customClass.'">';
                if (isset($tabla->accion->eliminar) && is_bool($tabla->accion->eliminar) && $tabla->accion->eliminar && (isset($tabla->multiseleccionable) || !$tabla->multiseleccionable)) {
                    $tabla->tmp .= '
                        <span class="btn btn-outline-danger btn-xs accionTabla btnDelete me-2">
                            <i class="fa-solid fa-trash"></i>
                        </span>';
                }

                if (isset($tabla->accion->editar) && is_bool($tabla->accion->editar) && $tabla->accion->editar) {
                    $tabla->tmp .= '
                        <span class="btn btn-outline-secondary btn-xs accionTabla btnEdit me-2">
                            <i class="fa-solid fa-pencil"></i>
                        </span>
                    ';
                }

                if (isset($tabla->accion->pdf) && is_bool($tabla->accion->pdf) && $tabla->accion->pdf) {
                    $tabla->tmp .= '
                        <span class="btn btn-outline-danger btn-xs accionTabla printPdf me-2">
                            <i class="fa-solid fa-file-pdf"></i>
                        </span>
                    ';
                }

                if (isset($tabla->accion->excel) && is_bool($tabla->accion->excel) && $tabla->accion->excel) {
                    $tabla->tmp .= '
                        <span class="btn btn-outline-success btn-xs accionTabla exportExcel" aria-hidden="true">
                            <i class="fa-solid fa-file-excel"></i>
                        </span>
                    ';
                }

                $tabla->tmp .= '</td >';
            }
            $cols = 0;
            foreach ($prm->cuerpo as $key => $td) {
                $cols++;
                
                $tabla->tmp .= '<td ' . $colorSeleccion . 'class="sorting_1 '.$customClass;
                if (isset($td->casos)) {
                    $tabla->tmp .= '">' . $this->wrCasosPonTabla($td->casos);
                } else {
                    $tabla->tmp .= ((isset($td->campoTxt) and is_string($td->campoTxt)) ? $td->campoTxt : '') . '">' . $this->wrElementoTdPonTabla($td);
                }
                $tabla->tmp .= '</td>';
            }
            $tabla->tmp .= '</tr>';
            if (isset($tabla->desplegable) && (isset($tabla->desplegable->enFila) && is_bool($tabla->desplegable->enFila) && $tabla->desplegable->enFila)) {
                $tabla->tmp .= '<tr class="desplegado hide"><td class="lineaDesplegado"></td><td colspan="' . $cols . '" class="contenedorDesplegable"></td></tr>';
            }
        }

        $this->mixScript('$("#' . $tabla->id . '").tabla(' . json_encode($tabla) . ');');

        if (isset($prm->comunica)) {
            $this->mixScript('                $("#' . $tabla->id . '").tabla({ prmComunica:function(){ return ' . $this->asObjetoJs($prm->comunica) . '} });            ');
        }
        return '<div id="' . $tabla->id . '" ></div>';
    }
    private function wrCasosPonTabla($casos, $textoTrue = '', $textoFalse = '')
    {
        // print_r($casos);
        if (isset($casos->cases) && is_array($casos->cases)) {
            unset($casos->operador);
            unset($casos->valor);
            $si = '{{if ';
            $colocaElse = false;
            foreach ($casos->cases as $caso) {
                // $prm->casosEstado->cases = array();
                // $caso = new stdClass() ;
                // $caso->valor = "1,3,4" ;
                // $caso->tipo = "boton";
                // $caso->color = "cielo";
                // $caso->icono = "pencil";
                // $caso->attr = new stdClass();
                // $caso->attr->oa_id = "oa_id";
                // $caso->class = "verActividad";
                // array_push($prm->casosEstado->cases,$caso)
                $casos->valor = $caso->valor;
                $casos->campo = $casos->campo;
                $si .= ($colocaElse) ? '{{else ' : '';
                $si .= $this->wrArmaIf($casos);
                $si .= '}} ' . $this->wrElementoTdPonTabla($caso);
                $colocaElse = true;
            }
            $si .= '{{/if}}';
        } else {
            $si = '{{if ' . $this->wrArmaIf($casos);

            // $si.= $casos->campo.$operador.$casos->valor;
            if ($textoTrue != '' || $textoFalse != '') {
                $si .= '}} ' . $textoTrue;
                $si .= ($textoFalse != '') ? '{{else}} ' . $textoFalse : '';
                $si .= ' {{/if}}';
            } else {
                $si .= '}} ' . $this->wrElementoTdPonTabla($casos->verdadero);
                $si .= (isset($casos->falso)) ? '{{else}} ' . $this->wrElementoTdPonTabla($casos->falso) : '';
                $si .= ' {{/if}}';
            }
        }
        return $si;
    }
    private function wrArmaIf($casos)
    {
        $operador = (isset($casos->operador)) ? $casos->operador : '==';
        $casos->valor = (string) $casos->valor;
        $valor = explode(",", $casos->valor);
        $si = '(';
        foreach ($valor as $key => $val) {
            if ($key > 0) {
                $si .= (isset($casos->tipoCondicion)) ? $casos->tipoCondicion : ' || ';
            }
            if (isset($casos->valorTipo)) {
                switch ($casos->valorTipo) {
                    case 'texto':
                    case 'string':
                        $value = '"' . $val . '"';
                        break;
                    case 'numero':
                    case 'integer':
                        $value = $val;
                        break;
                    case 'telefono':
                        $value = "(" . substr($val, 0, 3) . ")" . substr($val, 3, 3) . "-" . substr($val, 6);
                        break;
                }
            } else {
                $value = '"' . $val . '"';
            }

            $si .= $casos->campo . $operador . $value;
        }
        $si .= ')';
        if (isset($casos->ands)) {
            foreach ($casos->ands as $condicion) {
                $si .= '&& ';
                $operador = (isset($condicion->operador)) ? $condicion->operador : '==';
                $condicion->valor = (string) $condicion->valor;
                $valor = explode(",", $condicion->valor);
                $si .= '(';
                foreach ($valor as $key => $val) {
                    if ($key > 0) {
                        $si .= (isset($condicion->tipoCondicion)) ? $condicion->tipoCondicion : ' || ';
                    }
                    if (isset($condicion->valorTipo)) {
                        switch ($condicion->valorTipo) {
                            case 'texto':
                            case 'string':
                                $value = '"' . $val . '"';
                                break;
                            case 'numero':
                            case 'integer':
                                $value = $val;
                                break;
                            case 'telefono':
                                $value = "(" . substr($val, 0, 3) . ")" . substr($val, 3, 3) . "-" . substr($val, 6);
                                break;
                        }
                    } else {
                        $value = '"' . $val . '"';
                    }

                    $si .= $condicion->campo . $operador . $value;
                }
                $si .= ')';
            }
        }
        if (isset($casos->ors)) {
            foreach ($casos->ors as $condicion) {
                $si .= '|| ';
                $operador = (isset($condicion->operador)) ? $condicion->operador : '==';
                $condicion->valor = (string) $condicion->valor;
                $valor = explode(",", $condicion->valor);
                $si .= '(';
                foreach ($valor as $key => $val) {
                    if ($key > 0) {
                        $si .= (isset($condicion->tipoCondicion)) ? $condicion->tipoCondicion : ' || ';
                    }
                    if (isset($condicion->valorTipo)) {
                        switch ($condicion->valorTipo) {
                            case 'texto':
                            case 'string':
                                $value = '"' . $val . '"';
                                break;
                            case 'numero':
                            case 'integer':
                                $value = $val;
                                break;
                            case 'telefono':
                                $value = "(" . substr($val, 0, 3) . ")" . substr($val, 3, 3) . "-" . substr($val, 6);
                                break;
                        }
                    } else {
                        $value = '"' . $val . '"';
                    }

                    $si .= $condicion->campo . $operador . $value;
                }
                $si .= ')';
            }
        }
        return $si;
    }
    private function wrElementoTdPonTabla($td)
    {
        $opc = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        //      //debug
        // $firephp = FirePHP::getInstance(true);
        // $firephp->log($td , '\n\nvalode el wrelementopontabla');
        // $firephp->log($opc , '\n\nvalor de opcde el wrelementopontabla');
        //print_r($td);
        //print_r($opc);
        $checked = (isset($td->checked)) ? $td->checked : '';
        $color = (isset($td->color)) ? $td->color : '';
        $class = (isset($td->class)) ? $td->class : '';
        $formato = (isset($td->formato)) ? $td->formato : '';
        $tipo = (isset($td->tipo)) ? $td->tipo : 'texto';
        $tmp = '';
        $txt = '';
        $title = '';
        $attr = '';
        $label = '';
        $ajustable = (isset($td->ajustable)) ? $td->ajustable : false;

        $name = (isset($td->name)) ? $td->name : ''; // lemg
        //$value     = ( isset($td->value) )     ? $td->value     : '';// lemg
        $valorCombo = (isset($td->valorCombo)) ? $td->valorCombo : ''; // lemg
        $sql = (isset($td->sql)) ? $td->sql : ''; // lemg
        $params = (isset($td->params)) ? $td->params : array(); // lemg
        $conector = (isset($td->conector)) ? $td->conector : ''; // lemg
        if (isset($td->campoTxt)) {
            if (is_object($td->campoTxt)) {
                $separador = (isset($td->campoTxt->separador)) ? $td->campoTxt->separador : '';
                if (isset($td->campoTxt->campos)) {
                    if (isset($td->jsCallback)) {
                        $txt = '{{html '.$td->jsCallback.'(' . join(',', (array) $td->campoTxt->campos) . ') }}';
                    } else {
                        foreach ($td->campoTxt->campos as $varTxt) {
                            $txt .= ($txt != '') ? $separador : '';
                            $txt .= '${' . $varTxt . '}';
                        }
                    }
                }
                $txt .= (isset($td->texto)) ? $separador . $td->texto : '';
            } else if (is_string($td->campoTxt)) {
                if (isset($td->jsCallback)) {
                    if ($td->campoTxt === '*') {
                        $txt = '{{html '.$td->jsCallback.'($item.data) }}';
                    } else {
                        $txt = '{{html '.$td->jsCallback.'( '.$td->campoTxt.') }}';
                    }
                } else {
                    $txt = '${' . $td->campoTxt . '}';
                }
            }

        } elseif ($tipo == 'texto' && !isset($td->casos)) {
            echo 'El texto a mostrar en la celda es requerido';
        } else if (isset($td->texto) && $td->texto != "") {
            $txt = $td->texto;
        }
        if (isset($td->campoTitle)) {
            if (is_object($td->campoTitle)) {
                $separador = (isset($td->campoTitle->separador)) ? $td->campoTitle->separador : '';
                if (isset($td->campoTitle->campos)) {
                    foreach ($td->campoTitle->campos as $varTitle) {
                        $title .= ($title != '') ? $separador : '';
                        $title .= '${' . $varTitle . '}';
                    }
                }
                $title .= (isset($td->title)) ? $separador . $td->title : '';
            } else if (is_string($td->campoTitle)) {
                $title = '${' . $td->campoTitle . '}';
            }

        }
        if (isset($td->attr)) {
            $atributo = array_keys(get_object_vars($td->attr));
            foreach ($atributo as $attrName) {
                $attr .= $attrName . '="${' . $td->attr->$attrName . '}" ';
            }
        }
        switch ($tipo) {
            case 'boton':
                $icono = (isset($td->icono)) ? $td->icono : '';
                $txt = ($txt == '' && isset($td->texto)) ? $td->texto : $txt;
                $txt = ($txt != '' && $td->campoTxt == "") ? '<li class="' . $formato . '">' . $txt . '</li>' : $txt;
                $label = ($label == '' && isset($td->label)) ? $td->label : $label;
                $label = ($label != '') ? '<label>' . $label . '</label>' : '';
                $title = ($title == '' && isset($td->title)) ? $td->title : $title;
                $color = (isset($td->color)) ? $td->color : "default";

                switch ($color) {
                    case 'rojo':
                        $color = "danger";
                        break;
                    case 'amarillo':
                        $color = "warning";
                        break;
                    case 'cielo':
                        $color = "info";
                        break;
                    case 'verde':
                        $color = "success";
                        break;
                    case 'azul':
                        $color = "primary";
                        break;
                    case 'blanco':
                    default:
                        $color = "default";
                        break;
                }
                $tmp = '<button title="' . $title . '" class="btn btn-outline-' . ($color == 'default' ? 'secondary' : $color) . ' btn-sm button small  ' . $class . ' flat table-tooltip" ' . $attr . '>';
                $tmp .= $icono == "" ? "" : '<i class="fa fa-' . $icono . '"></i>';
                $tmp .= $txt . $label . '</button>';

                break;
            case 'botoncito':
                $icono = (isset($td->icono)) ? $td->icono : '';
                $txt = ($txt != '') ? '<li class="' . $formato . '">' . $txt . '</li>' : '';
                $label = ($label == '' && isset($td->label)) ? $td->label : $label;
                $label = ($label != '') ? '<label>' . $label . '</label>' : '';
                $tmp = '<button title="' . $title . '" class=" btn btn-xs ' . $class . '" ' . $attr . '>';
                $tmp .= $icono == "" ? '' : '<span  class="fa fa-' . $icono . '"></span >';
                $tmp .= $txt . $label . '</button>';

                break;
            case 'botonSmall':
                $icono = (isset($td->icono)) ? $td->icono : '';
                $txt = ($txt != '') ? $txt : '';
                $tmp = '<button title="' . $title . '" class=" btn btn-small ' . (isset($td->campoTxt) ? $td->campoTxt : '') . ' ' . $class . '" ' . $attr . '><span  class="fa fa-' . $icono . '"></span >' . $txt . '</button>';
                break;
            case 'bolita':
                $linea = (isset($td->linea)) ? ' border-color:#${' . $td->linea . '};' : '';
                $color = (isset($td->color)) ? ' background-color:#${' . $td->color . '};' : '';
                $width = (isset($td->width)) ? ' width:' . $td->width . ';' : ' width:15px;';
                $height = (isset($td->width)) ? ' height:' . $td->height . ';' : ' height:15px;';
                $txt = (isset($td->txt)) ? '${' . $td->txt . '}' : '';
                $lbl = (isset($td->lbl)) ? '${' . $td->lbl . '}' : '';
                $title = (isset($td->title)) ? '${' . $td->title . '}' : '';
                $tmp = '<div class="estado ' . $class . '" title="' . $title . '" style="' . $color . $linea . $width . $height . ' display: inline-block; margin-right: 5px; border-radius:50%;" >' . $txt . '</div><span>' . $lbl . '</span>';
                break;
            case 'semaforo':
                $linea = (isset($td->linea)) ? ' border-color:#${' . $td->linea . '};' : '';
                $color = (isset($td->color)) ? ' background-color:#${' . $td->color . '};' : '';
                $colorf = $color == "000000" ? ' color:#FFF;";' : 'color:#000;';
                $txt = (isset($td->txt)) ? '${' . $td->txt . '}' : '';
                $lbl = (isset($td->lbl)) ? '${' . $td->lbl . '}' : '';
                $title = (isset($td->title)) ? '${' . $td->title . '}' : '';
                $tmp = '<div style="' . $colorf . $color . $linea . ' display: block; width:100%; height:100%; padding:6px;" >' . $txt . $lbl . '</div>';
                break;
            case 'texto':
                $align = (isset($td->align)) ? $td->align : 'left';
                $tmp = '';
                if ($ajustable) {
                    $tmp = '<div style=" overflow:hidden;" class="div_ajustable">';
                }

                $tmp .= '<label data-format="data-content" title="' . $title . '" class="' . $formato . '" ' . $attr . ' style="text-align:' . $align . '; width:100%;">' 
                    . $txt 
                    . '</label>';

                if ($ajustable) {
                    $tmp .= "</div>";
                }
                
                break;
            case 'txtu':
                $tmp = '';
                if ($ajustable) {
                    $tmp = '<div style=" overflow:hidden;" class="div_ajustable">';
                }
                $tmp .= '<label data-format="data-content" title="' . $title . '" class="' . $class . '" ' . $attr . ' style="text-align: center; padding:3px 3px 4px 4px; width:100%;">' 
                    . $txt 
                    . '</label>';

                if ($ajustable) {
                    $tmp .= "</div>";
                }
                break;
            case 'link':
                $align = (isset($td->align)) ? $td->align : 'left';
                $tmp = '<div align="' . $align . '" ><a href="#" ' . $attr . ' title="' . $title . '" class="' . $td->campoTxt . ' linkDeTabla" >' . $txt . '</a></div>';
                break;
            case 'check':
                $tmp = '<input type="checkbox" title="' . $title . '" ' . $attr . ' class="' . $class . '"  ' . $checked . '><label>' . $txt . '</label>';
                break;
            case 'caja':
                $tmp = '<input type="text" title="' . $title . '" name="' . $name . '" ' . $attr . ' class="' . $class . '"><label>' . $txt . '</label>';
                break;
            //lemg elemento radio
            case 'radio':
                $tmp = '<input type="radio" title="' . $title . '" name="' . $name . '" ' . $attr . ' class="' . $class . '"><label>' . $txt . '</label>';
                break;
            //lemg elemento text del input
            case 'text':
                $tmp = '<input type="text"  title="' . $title . '" name="' . $name . '" ' . $attr . ' class="' . $class . '" value ="' . $txt . '">';
                break;
            //lemg elemento archivo
            case 'icon':
            //Marcos: para adicionar una imagen
            case 'img':
                $align = (isset($td->align)) ? $td->align : 'left';
                $tmp = '<div align="' . $align . '" ><img src="' . $td->img . '"></div>';
                break;
            case 'icono':
                $tmp = '<span class="fa fa-' . $td->icono . '"></span>';
                break;
            case 'archivo':
                $enctype = (isset($td->enctype)) ? $td->enctype : '';
                $tmp = '<input type="file" name="' . $name . '"  ' . $attr . '  class="' . $class . '" enctype="' . $enctype . '" "><label>' . $txt . '</label>';
                break;
            //lemg elemento combo
            case 'combo':
                if (isset($td->sql)) {
                    $con = new AccesoDatos();
                    $con->conectar($conector);
                    $con->setSql($sql);
                    $con->consultar($params);
                    $nr = $con->getNumeroRegistros();
                    $selectFirst = (isset($td->verPrimero)) ? $td->verPrimero : false;
                    $selected = (isset($td->selecciona)) ? $td->selecciona : ''; //campo seleccionado
                    $tmp = '<select ' . $attr . ' class="' . $class . '">';
                    $tmp .= ($nr > 1 && !$selectFirst) ? '<option value ="0">Seleccione una opcion de la lista</option>' : '';
                    while ($row = $con->sacar()) {
                        if (isset($td->selecciona)) {
                            $selecciona = '{{if ' . $td->selecciona . '=="' . $row[0] . '"}}  selected="selected"{{/if}}';
                        }
                        $tmp .= '<option value="' . $row[0] . '" ' . $selecciona . '>' . $row[0] . '-' . $row[1] . '</option>';
                    }
                    $tmp .= '</select>';
                }
                break;
            case 'claseFila':
                $tmp .= ' ' . ((isset($td->clase)) ? $td->clase : '') . ' ';
                break;
            case 'colorFila':
                $tmp .= ' style=" ' . ((isset($td->color)) ? 'background-color:' . $td->color . ';' : '') . '" ';
                break;
        }
        return $tmp;
    }
    public function wrLlamaLlenaTabla($id)
    {
        return '$("#' . $id . '").tabla("llenaTabla");';
    }
    public function wrLlenaTabla()
    {
        $nav = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )

        : null;
        $idTabla = (isset($nav->idTabla)) ? $nav->idTabla : 'tablaDefault';
        $nf = (isset($nav->nf)) ? $nav->nf : ((isset($nav->nfDefault)) ? $nav->nfDefault : 5);
        $np = (isset($nav->np)) ? $nav->np : 1;
        if ($np == -1) {
            $np = 1;
        }

        $registroN = ($np - 1) * $nf;

        // Marcos: 13 de abril del 2015
        // Para usar una conexión distinta a la default
        $conexion = (isset($nav->conexion) ? $nav->conexion : "");

        $c = $this->getConexionActiva();
        $sql = str_replace(';', '', $this->getSql());
        $params = $this->getParams() ?? array();
        $rango = new stdClass();
        $a = array();

        switch ($c->getType()) {
            case 'MySql':
                if ($nf == -1) {
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, stripos(strtoupper($sql), 'SELECT') + 6);
                } else {
                    $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, stripos(strtoupper($sql), 'SELECT') + 6) . ' LIMIT ' . $registroN . ',' . $nf . ';';
                }

                $this->setSql($sql);
                $this->conectar($conexion);
                $this->consultar($params, true, PDO::CURSOR_SCROLL);
                while ($r = $this->extraer()) {
                    if (isset($nav->explodeChar)) {
                        $c = explode($nav->explodeChar, $r[$nav->explodeCampo]);
                        if (empty($c[0])) {
                            $r[$nav->explodeCampo] = 0;
                        } else {
                            $r[$nav->explodeCampo] = count($c);
                        }

                    }
                    array_push($a, $r);
                }
                if ($this->getNumeroRegistros() > 0) {
                    // Marcos: para averiguar cuántos registros me está mostrando el ponTabla
                    $this->mixScript('
                        $("#otraInfoFooter").html("<span title=\"' . $this->getNumeroRegistros() . ' registros\">.</span>" );
                    ');
                    // mysqli_data_seek($this->getConsultaId(), 0);

                    $this->primerRow = $this->extraer(PDO::ATTR_CURSOR, 0);
                }
                $this->setSql('SELECT FOUND_ROWS() numReg;');
                $this->consultar();
                $totalRegistros = $this->sacar(PDO::ATTR_CURSOR, 0);
                if ($nf == -1) {
                    $registroN = $totalRegistros[0] - 1;
                    $nf = $totalRegistros[0];
                }
                // mysqli_data_seek($this->getConsultaId(), 0);
                $rango->total = $totalRegistros[0];
                if ($nf == 0 || empty($nf)) {
                    $nps = 0;
                } else {
                    $nps = ceil($totalRegistros[0] / $nf);
                }

                if (isset($nav->paginacion)) {
                    $np = 0;
                }
                break;
            case 'SqlServer':
                $this->setSql($sql);
                // $this->conectar();
                $this->consultar();
                $temp = array();
                while ($r = $this->extraer()) {
                    $r = array_map('htmlentities', $r);
                    array_push($temp, $r);
                }
                $rango->total = count($temp);
                $totalRegistros[0] = $rango->total;
                $nps = ceil($registroN / $nf);
                $a = array_splice($temp, $registroN, $nf);
                if (isset($nav->paginacion)) {
                    $np = 0;
                }
                break;
        }

        $rango->a = $registroN + 1;
        $rango->b = $registroN + $nf;
        $rango->b = ($rango->total < $rango->b) ? $rango->total : $rango->b;
        if ($nf == 0 || empty($nf)) {
            $nps = 0;
        } else {
            $nps = ceil($totalRegistros[0] / $nf);
        }

        $tabla = new stdClass();
        $tabla->filas = $a;
        $tabla->rango = $rango;
        if ($np == 1) {
            $tabla->numPaginas = $nps;
        }

        $this->mixScript('
            $("#' . $idTabla . '").tabla(' . json_encode($tabla) . ');');
        return $totalRegistros[0];
    }
    //FUNCIONES KENDO
    public function ponComboAutocomplete()
    {
        $opc = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        if (isset($opc->id)) {
            $eventCombo = "";
            foreach ($opc as $key => $value) {
                if ($key == 'select' or $key == 'change' or $key == 'close' or $key == 'open') {
                    $eventCombo .= "," . $key . ":" . $value;
                }
            }
            $script = '
                $("#' . $opc->id . '").kendoComboBox({
                    placeholder: "' . (isset($opc->textDefault) ? $opc->textDefault : 'Seleccionar...') . '",
                    dataTextField: "' . $opc->campoText . '",
                    dataValueField: "' . $opc->campoValor . '",
                    cascadeFrom: "' . (isset($opc->idCascada) ? $opc->idCascada : '') . '",
                    filter: "' . (isset($opc->filtro) ? 'contains' : 'none') . '",
                    dataSource: {
                        type: "odata",
                        serverFiltering: true,
                        transport: {
                            read: "' . $opc->url . '"
                            ,parameterMap: function (options, type) {
                                if (options.filter) {
                                    options.valorfiltro = options.filter.filters[0].value;
                                    options.campofiltro = options.filter.filters[0].field;
                                }
                                return kendo.data.transports["odata"].parameterMap(options, type);
                            }
                        }
                    }
                    ' . $eventCombo . '
                });
            ';
            $this->mixScript($script);
            $combo = "";
            if (isset($opc->label)) {
                $combo = '<label for="' . $opc->id . '" style="width:' . $opc->LabelWidth . '" >' . $opc->label . ':</label>&nbsp;';
            }
            $width = (isset($opc->width) ? ('style="width:' . $opc->width . ';"') : '');
            $combo .= '<input id="' . $opc->id . '" ' . $width . '/>';
            return $combo;
        }
        return '';
    }

    public function jsonKendo($array, $a = "[")
    {
        $dtaJsn = ($a == '[') ? '[' : '{';
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $dtaJsn .= '';
            } elseif ($key == 'align') {
                $dtaJsn .= 'attributes:{"class": "table-cell",style: "text-align: ' . $value . ';"},';
                continue;
            } elseif ($key == 'style') {
                $dtaJsn .= 'attributes:{"class": "table-cell",style: "' . $value . '"},';
                continue;
            } else {
                $dtaJsn .= $key . ':';
            }
            if (is_array($value)) {
                $dtaJsn .= $this->jsonKendo($value, '{');
            } else {
                if (is_string($value)) {
                    if ($key == 'click') {
                        $dtaJsn .= $value . ',';
                    } else {
                        $dtaJsn .= '"' . $value . '",';
                        //echo "aqui entro";
                    }
                } else {
                    if ($value === true) {
                        $dtaJsn .= 'true,';
                    } else if ($value === false) {
                        $dtaJsn .= 'false,';
                    } else {
                        $dtaJsn .= $value . ',';
                    }
                }
            }
        }
        $dtaJsn = trim($dtaJsn, ",");
        $dtaJsn .= ($a == '[') ? ']' : '},';
        return $dtaJsn;
    }

    public function setDataSourceKendo()
    {
        $grid = (func_num_args() > 0) ? (
            (is_object(func_get_arg(0))) ? func_get_arg(0) : ((is_array(func_get_arg(0))) ? (object) func_get_arg(0) : ((is_string(func_get_arg(0))) ? $this->asJson(func_get_arg(0)) : null))
        ) : null;

        $fields = array();
        $par = '';
        $var = '';
        if (isset($grid->setDataSource) && is_array($grid->setDataSource)) {

            foreach ($grid->setDataSource as $key => $value) {
                $var .= ' var ' . $key . '=' . $value . ' ; ';
                $par .= $key . '="+' . $key . '+"&';
            }
            $par = trim($par, '+"&');
        }
        if (strstr($grid->url, '?') === false) {
            $url = $grid->url . '?';
        } else {
            $url = $grid->url;
        }
        if ($par != '') {
            $url = '"' . $url . $par;
        } else {
            $url = '"' . $url . '"';
        }
        foreach ($grid->columna as $column) { //Se agregan los tipos de datos de la columna
            if (isset($column['field'])) {
                $fields[$column['field']] = array('type' => (isset($column['type']) ? $column['type'] : "string"));
            }
        }
        $idGrid = str_replace(array('-', '.', ' '), "_", $grid->id);
        $dataSource = $var . '
                        var dataSource_' . $idGrid . ' = new kendo.data.DataSource({
                            type: "odata",
                            transport: {
                                read: ' . $url . '
                            },
                            schema: {
                                model: {
                                    fields: ' . trim($this->jsonKendo($fields, '{'), ',') . '
                                }
                            },
                            pageSize: ' . $grid->numPag . ',
                            serverPaging: true,
                            serverFiltering: true,
                            serverSorting: true
                        });
                        ';
        if (isset($grid->setDataSource)) {
            $dataSource .= '$("#' . $grid->id . '").data("kendoGrid").setDataSource(dataSource_' . $idGrid . ');';
        }
        return $dataSource;
    }

    public function ponTablaKendo()
    {
        $grid = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )

        : null;
        $dataSource = '';

        $this->scriptKendo = '$(document).ready(function() {
                $("#' . $grid->id . '").kendoGrid({
                    height: ' . ((isset($grid->height) && $grid->height > 0) ? $grid->height : 150) . ',
                    filterable: false,
                    sortable: false,
                    pageable: ' . ((isset($grid->paginacion) && $grid->paginacion === true) ? 'true' : 'false') . ',
                    resizable:true,
                    selectable:' . ((isset($grid->select) && $grid->select === false) ? 'false' : 'true') . ',
                    columns: ' . $this->jsonKendo($grid->columna) . '
                });
            ';
        if (isset($grid->autoLoad) && $grid->autoLoad === true) {
            $idGrid = str_replace(array('-', '.', ' '), "_", $grid->id);
            $this->scriptKendo .= $this->setDataSourceKendo($grid);
            $this->scriptKendo .= '$("#' . $grid->id . '").data("kendoGrid").setDataSource(dataSource_' . $idGrid . ');';
        }
        $this->scriptKendo .= '});';
        $this->mixScript($this->scriptKendo);
        return '<div id="' . $grid->id . '"></div>';
    }

    public function ponInputDateTimeKendo()
    {
        $input = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )
        : null;

        $label = (isset($input->label)) ? $input->label : '';
        if ($label != '') {
            $required = (isset($input->required) && $input->required == true) ? 'class="required"' : '';
            $style = 'style="';
            $style .= (isset($input->LabelWidth) ? ('width:' . $input->LabelWidth . ';') : '');
            $style .= (isset($input->LabelAlign) ? ('text-align:' . $input->LabelAlign . ';') : '');
            $style .= '"';
            $html = '<label ' . $style . ' for="' . $input->field . '" ' . $required . '>' . $input->label . ':</label>&nbsp;';
        }
        $style = 'style="';
        $style .= (isset($input->width) ? ('width:' . $input->width . ';') : '');
        $style .= '"';
        $required = (isset($input->required) && $input->required == true) ? '" required validationMessage="Por favor, introduzca {0}"' : '';
        $placeholder = '';
        if (isset($input->textDefault)) {
            $placeholder = 'placeholder="' . $input->textDefault . '"';
        }
        $readOnly = '';
        if (isset($input->readOnly) && $input->readOnly == true) {
            $readOnly = 'readOnly';
        }
        $id = (isset($input->id) ? ($input->id) : ($input->field));
        $html .= '<input ' . $readOnly . ' ' . $style . ' type="text" id="' . $id . '" name="' . $input->field . '" ' . $placeholder . ' ' . $required . '/>';
        $value = '';
        if (
            isset($input->value) && $input->value != ''
            && (strstr($input->value, '0000')) === false
        ) {
            $fecha = explode('/', date("d/m/Y", $this->StrTime($input->value)));
            if (((int) $fecha[2]) > 1900) {
                $hora = explode(':', date("H:i", strtotime($input->value)));
                $value = ',value: new Date("' . $fecha[2] . '","' . ((int) $fecha[1] - 1) . '","' . $fecha[0] . '","' . $hora[0] . '","' . $hora[1] . '")';
            }
        }
        $this->mixScript('
            $("#' . $id . '").kendoDateTimePicker({
                    format: "dd/MM/yyyy hh:mm tt"
                    ' . $value . '
                });');
        return $html;
    }

    public function ponInputTextKendo()
    {
        $input = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )
        : null;

        $label = (isset($input->label)) ? $input->label : '';
        if ($label != '') {
            $required = (isset($input->required) && $input->required == true) ? 'class="required"' : '';
            $style = 'style="';
            $style .= (isset($input->LabelWidth) ? ('width:' . $input->LabelWidth . ';') : '');
            $style .= (isset($input->LabelAlign) ? ('text-align:' . $input->LabelAlign . ';') : '');
            $style .= '"';
            $html = '<label ' . $style . ' for="' . $input->field . '" ' . $required . '>' . $input->label . ':</label>&nbsp;';
        }
        $style = 'style="';
        $style .= (isset($input->width) ? ('width:' . $input->width . ';') : '');
        /*inicia-nuevo*/
        $style .= (isset($input->align) ? ('text-align:' . $input->align . ';') : '');
        /*termina-nuevo*/
        $style .= '"';
        $required = (isset($input->required) && $input->required == true) ? '" required validationMessage="Por favor, introduzca {0}"' : '';
        $class = (isset($input->class) ? ' ' . $input->class : '');
        $placeholder = '';
        if (isset($input->textDefault)) {
            $placeholder = 'placeholder="' . $input->textDefault . '"';
        }
        $readOnly = '';
        if (isset($input->readOnly) && $input->readOnly == true) {
            $readOnly = 'readOnly';
        }
        $id = (isset($input->id) ? ('id="' . $input->id . '"') : '');
        /*inicia-nuevo*/
        $atributos = '';
        if (isset($input->attrs) && (count($input->attrs) > 0)) {
            foreach ($input->attrs as $attr => $valor) {
                $atributos .= $attr . '="' . $valor . '" ';
            }
        }
        /*termina-nuevo*/
        /*modificacion en la siguiente linea */
        $html .= '<input ' . $readOnly . ' ' . $style . ' type="text" ' . $id . ' value="' . (isset($input->value) ? $input->value : '') . '" name="' . $input->field . '" ' . $placeholder . ' class="k-textbox' . $class . '"  ' . $required . ' ' . $atributos . '/>';

        return $html;
    }

    public function ponTextAreaKendo()
    {
        $input = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )
        : null;

        $label = (isset($input->label)) ? $input->label : '';
        if ($label != '') {
            $required = (isset($input->required) && $input->required == true) ? 'class="required"' : '';
            $style = 'style="';
            $style .= (isset($input->LabelWidth) ? ('width:' . $input->LabelWidth . ';') : '');
            $style .= (isset($input->LabelAlign) ? ('text-align:' . $input->LabelAlign . ';') : '');
            $style .= '"';
            $html = '<label ' . $style . ' for="' . $input->field . '" ' . $required . '>' . $input->label . ':</label>&nbsp;';
        }
        $style = 'style="';
        $style .= (isset($input->width) ? ('width:' . $input->width . ';') : '');
        $style .= '"';
        $required = (isset($input->required) && $input->required == true) ? '" required validationMessage="Por favor, introduzca {0}"' : '';
        $class = (isset($input->class) ? ' ' . $input->class : '');
        $placeholder = '';
        if (isset($input->textDefault)) {
            $placeholder = 'placeholder="' . $input->textDefault . '"';
        }
        $readOnly = '';
        if (isset($input->readOnly) && $input->readOnly == true) {
            $readOnly = 'readOnly';
        }
        $id = (isset($input->id) ? ('id="' . $input->id . '"') : '');
        $html .= '<textarea ' . $readOnly . ' ' . $style . ' ' . $id . ' name="' . $input->field . '" ' . $placeholder . ' class="k-textbox' . $class . '"  ' . $required . '>' . (isset($input->value) ? $input->value : '') . '</textarea>';

        return $html;
    }

    public function ponInputBusquedaKendo()
    {
        $input = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )
        : null;

        $label = (isset($input->label)) ? $input->label : '';
        if ($label != '') {
            $required = (isset($input->required) && $input->required == true) ? 'class="required"' : '';
            $style = 'style="';
            $style .= (isset($input->LabelWidth) ? ('width:' . $input->LabelWidth . ';') : '');
            $style .= (isset($input->LabelAlign) ? ('text-align:' . $input->LabelAlign . ';') : '');
            $style .= '"';
            $html = '<label ' . $style . ' for="' . $input->field . '" ' . $required . '>' . $input->label . ':</label>&nbsp;';
        }
        $style = '';
        $style1 = '';
        if (isset($input->width)) {
            $width = (int) str_replace('px', '', $input->width);
            $width = $width - 40;
            $style = 'style="';
            $style .= 'width:' . $width . 'px';
            $style .= '"';

            $style1 = 'style="';
            $style1 .= 'width:' . $input->width;
            $style1 .= '"';
        }

        $id = (isset($input->id) ? ('id="' . $input->id . '"') : '');
        $idB = (isset($input->id) ? ('id="busq-' . $input->id . '"') : '');

        $html .= '<span ' . $style1 . ' class="k-textbox k-space-left">';
        $html .= '<input ' . $style . ' type="text" ' . $id . ' name="' . $input->field . '"/>';
        $html .= '<a ' . $idB . ' href="#" class="k-icon k-i-search">&nbsp;</a>';
        $html .= '</span">';
        return $html;
    }

    public function createCampoFormKendo($campos, $form, $idPanel)
    {
        $defaults = $form->defaults;
        $htm = '<ul class="conten-' . $idPanel . '">';
        foreach ($campos as $items) {
            if (!isset($items['LabelWidth'])) {
                $items['LabelWidth'] = ((isset($form->defaults) && isset($form->defaults['LabelWidth'])) ? $form->defaults['LabelWidth'] : '70px');
            }
            if (!isset($items['LabelAlign'])) {
                $items['LabelAlign'] = ((isset($form->defaults) && isset($form->defaults['LabelAlign'])) ? $form->defaults['LabelAlign'] : 'left');
            }
            if (!isset($items['width'])) {
                $items['width'] = ((isset($form->defaults) && isset($form->defaults['width'])) ? $form->defaults['width'] : '100px');
            }

            if (isset($form->defaults['readOnly']) && $form->defaults['readOnly'] == true) {
                if (!isset($items['readOnly'])) {
                    $items['readOnly'] = true;
                }
            }
            if (isset($items['type']) && $items['type'] == 'empty') {
                $htm .= '<li>&nbsp; &nbsp; </li>';
            } else {
                if (isset($items['inputType']) && $items['inputType'] == 'busqueda') {
                    if (!isset($items['width'])) {
                        $items['width'] = isset($form->defaults['width']) ? $form->defaults['width'] : '150px';
                    }
                    $htm .= '<li>' . $this->ponInputBusquedaKendo($items) . '</li>';
                } elseif (isset($items['inputType']) && $items['inputType'] == 'datetime') {
                    unset($items['inputType']);
                    $htm .= '<li>' . $this->ponInputDateTimeKendo($items) . '</li>';
                } elseif (isset($items['inputType']) && $items['inputType'] == 'combo') {
                    unset($items['inputType']);
                    $htm .= '<li>' . $this->ponComboAutocomplete($items) . '</li>';
                } elseif (isset($items['inputType']) && $items['inputType'] == 'textarea') {
                    unset($items['inputType']);
                    $htm .= '<li>' . $this->ponTextAreaKendo($items) . '</li>';
                } elseif (isset($items['inputType']) && $items['inputType'] == 'label') {
                    unset($items['inputType']);
                    //if(isset($items['align']))
                    $estilo = isset($items['align']) ? 'style="height:25px;text-align:' . $items['align'] . '"' : 'style="text-align:left"';
                    $htm .= '<li ' . $estilo . '>' . $items['label'] . ':</li>';
                } else {
                    $htm .= '<li>' . $this->ponInputTextKendo($items) . '</li>';
                }
            }
        }
        $htm .= '</ul>';
        return $htm;
    }

    public function ponFormularioKendo()
    {
        $form = (func_num_args() > 0)
        ? (
            (is_object(func_get_arg(0)))
            ? func_get_arg(0)
            : ((is_array(func_get_arg(0)))
                ? (object) func_get_arg(0)
                : ((is_string(func_get_arg(0)))
                    ? $this->asJson(func_get_arg(0))
                    : null
                )
            )
        )

        : null;

        $idPanel = (isset($form->id) ? 'panel-div-' . $form->id : 'panel-div-' . date('YmdHisu'));
        $style = '
            .conten-' . $idPanel . '
            {
                loat: left;
                text-align: ' . (isset($form->alignField) ? $form->alignField : 'left') . ';
            }
            .conten-' . $idPanel . ' {/*margen fiel*/
                list-style-type: none;
                margin: ' . (isset($form->marginLeft) ? $form->marginLeft : '0') . ';
                padding: 0;
            }
            .conten-' . $idPanel . ' .required {
                font-weight: bold;
            }
            .conten-' . $idPanel . ' li
            {
                list-style: none;
                margin: ' . (isset($form->marginField) ? $form->marginField : '3px') . ' 0 0 0;
            }
            .conten-' . $idPanel . ' li label {
                display: inline-block;
                width: ' . ((isset($form->defaults) && isset($form->defaults['LabelWidth'])) ? $form->defaults['LabelWidth'] : '70px') . ';
                text-align: ' . ((isset($form->defaults) && isset($form->defaults['LabelAlign'])) ? $form->defaults['LabelAlign'] : 'left') . ';
            }

            .conten-' . $idPanel . ' li > * {
                width: ' . ((isset($form->defaults) && isset($form->defaults['width'])) ? $form->defaults['width'] : '100px') . ';
            }

            .valid {
                color: green;
            }

            .invalid {
                color: red;
            }
            span.k-tooltip {
                margin-left: 6px;
                text-align: left;
                position: absolute;
                width:auto;
            }
            .panel-info-conten{
                ' . (isset($form->styleBackground)
            ? $form->styleBackground
            : 'background-color: #E3E3E3;
                        border-color: #BBB9B9;
                        color: #2E2E2E;') . '
            }

            /*inicia-nuevo*/
            .tblform{
                margin: 2px 4px;
            }
            td.campo-form{
                padding: 4px 0;
            }
            td.campo-form label{
                display: inline-block;
                width: ' . ((isset($form->defaults) && isset($form->defaults['LabelWidth'])) ? $form->defaults['LabelWidth'] : '70px') . ';
                text-align: ' . ((isset($form->defaults) && isset($form->defaults['LabelAlign'])) ? $form->defaults['LabelAlign'] : 'left') . ';
            }
            /*termina-nuevo*/
        ';
        $this->mixStyle($style);
        $this->scriptKendo = ' var validator = $("#panel-' . $idPanel . '").kendoValidator().data("kendoValidator");';
        $htm = '<div id="panel-' . $idPanel . '" class="k-block panel-info-conten" style="width: ' . (isset($form->width) ? $form->width : '450px') . ';">';
        if (isset($form->titulo)) {
            $htm .= '<div class="k-header">' . (isset($form->titulo) ? $form->titulo : '') . '</div>';
        }
        if (isset($form->columns)) {
            $htm .= '<table width="100%" border=' . (isset($form->borderColumn) ? $form->borderColumn : '0') . '><tr>';
            foreach ($form->columns as $columns) {
                $campos = $columns['campos'];
                $htm .= '<td style="width:' . (isset($columns['width']) ? $columns['width'] : '200px') . '">';
                $htm .= $this->createCampoFormKendo($campos, $form, $idPanel);
                $htm .= '</td>';
            }
            $htm .= '</table></tr>';
        } else {
            $htm .= $this->createCampoFormKendo($form->campos, $form, $idPanel);
        }

        /*iniciia-nuevo*/
        if (isset($form->filas)) {
            $htm .= '<table width="98%" class="tblform" border=' . (isset($form->borderColumn) ? $form->borderColumn : '0') . '>';

            for ($i = 0; $i < count($form->filas); $i++) {
                $fila = $form->filas[$i];

                $htm .= '<tr>';
                if (isset($fila['campos'])) {
                    for ($j = 0; $j < count($fila['campos']); $j++) {
                        $campo = $fila['campos'][$j];
                        $anchoCelda = ((isset($campo['width'])) ? $campo['width'] : 120) + 70;

                        $colspan = (isset($campo['columnas'])) ? $campo['columnas'] : 1;
                        $htm .= '<td colspan="' . $colspan . '" class="campo-form" style="width:' . $anchoCelda . 'px">';

                        if (isset($campo['inputType']) && $campo['inputType'] == 'busqueda') {
                            if (!isset($campo['width'])) {
                                $campo['width'] = isset($form->defaults['width']) ? $form->defaults['width'] : '150px';
                            }
                            $htm .= '<span>' . $this->ponInputBusquedaKendo($campo) . '</span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'datetime') {
                            unset($campo['inputType']);
                            $htm .= '<span>' . $this->ponInputDateTimeKendo($campo) . '</span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'combo') {
                            unset($campo['inputType']);
                            $htm .= '<span>' . $this->ponComboAutocomplete($campo) . '</span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'textarea') {
                            unset($campo['inputType']);
                            $htm .= '<span>' . $this->ponTextAreaKendo($campo) . '</span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'checkbox') {
                            unset($campo['inputType']);
                            $campo['width'] = 'auto';
                            $htm .= '<span><!-- $this->ponInputCheckBox($campo) --></span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'boton') {
                            unset($campo['inputType']);
                            $htm .= '<span>' . $this->ponBoton($campo) . '</span>';
                        } elseif (isset($campo['inputType']) && $campo['inputType'] == 'label') {
                            unset($campo['inputType']);
                            $estilo = isset($campo['align'])
                            ? 'style="height:25px; text-align:' . $campo['align'] . '"'
                            : 'style="text-align:left"';
                            $htm .= '<span ' . $estilo . '>' . $campo['label'] . ':</span>';
                        } else {
                            $htm .= '<span>' . $this->ponInputTextKendo($campo) . '</span>';
                        }
                        $htm .= '</td>';
                    }
                }
                $htm .= '</tr>';
            }
            $htm .= '</table>';
        }
        /*termina-nuevo*/

        if (isset($form->columns2)) {
            $htm .= '<table width="100%" border=' . (isset($form->borderColumn) ? $form->borderColumn : '0') . '><tr>';
            foreach ($form->columns2 as $columns) {
                $campos = $columns['campos'];
                $htm .= '<td style="width:' . (isset($columns['width']) ? $columns['width'] : '200px') . '">';
                $htm .= $this->createCampoFormKendo($campos, $form, $idPanel);
                $htm .= '</td>';
            }
            $htm .= '</table></tr>';
        }
        if (isset($form->html)) {
            $htm .= '<br/>' . $form->html;
        }
        if (isset($form->titulo)) {
            $htm .= '   </div>';
        }
        $htm .= '</div>';
        $this->mixScript($this->scriptKendo);
        return $htm;
    }

    public function validar()
    {
        $opc = (func_num_args() > 0) ? func_get_arg(0) : null;
        if (is_object($opc) || is_array($opc)) {
            /*
            controlador
            datosExtra
            metodoValidador
            formId
             */
            $opc = (is_array($opc)) ? (object) func_get_arg(0) : $opc;
            if (isset($opc->controlador) && $opc->controlador != '') {
                $datosExtra = (isset($opc->datosExtra) && is_object($opc->datosExtra)) ? $opc->datosExtra : new stdClass();
                if (isset($opc->metodoValidador)) {
                    $datosExtra->metodoValidador = $opc->metodoValidador;
                }

                $de = json_encode($datosExtra);
                $de = str_replace(array('"{', '}"', '\\'), array('', '', ''), $de);
                $this->mixScript(
                    '
            $("#' . $opc->formId . '")
            .attr("method","post")
            .attr("action","' . $opc->controlador . '")
            .validationEngine({
            ajaxFormValidation: true,
            ajaxFormValidationMethod: "post",
            onAjaxFormComplete: ajaxValidationCallback,
            submitExtra:function(){return ' . $de . ';}
            });'
                );
            }
        } else {
            $this->mixScript('$("#' . $opc . '").validationEngine();');
        }

    }

    public function validado($name, $valido, $mnsg)
    {
        array_push($this->validacion, array($name, $valido, $mnsg));
    }

    public function ponFile()
    {
        $attr = '';
        $opc = ((func_num_args() > 0) ? (((is_object(func_get_arg(0))) ? func_get_arg(0) : (is_array(func_get_arg(0)))) ? (object) func_get_arg(0) : null) : null);
        $label = (isset($opc->label)) ? '<label for="' . $opc->id . '">' . $opc->label . '</label>' : '';
        $width = (isset($opc->width)) ? 'style="width:' . $opc->width . ';"' : '';
        if (!isset($opc->noTas)) {
            foreach ($opc as $key => $value) {
                if (!($key == 'onBlur' or $key == 'onClick' or $key == 'masivo' or $key == 'enRow' or $key == 'width' or $key == 'hide')) {
                    $attr .= ' ' . $key . '="' . $value . '"';
                }

            }
            if (isset($opc->enRow)) {
                switch ($opc->enRow) {
                    case 2:
                        $input = '<th align="right">' . $label . '</th><td align="left"><input name="' . $opc->id . '" ' . $attr . ' ' . $width . '/></td>';
                        break;
                    case 1:
                    default:
                        $input = '<td align="right">' . $label . '</td><td align="left"><input type="file" name="' . $opc->id . '" ' . $attr . ' ' . $width . '/></td>';
                        break;
                }
            } else {
                $input = $label . '<input type="file" name="' . $opc->id . '" ' . $attr . ' ' . $width . '/>';
            }

            if (!isset($opc->masivo)) {
                $script = '';
                $script .= (isset($opc->onClick)) ? '$("#' . $opc->id . '").click(function(){ ' . $opc->onClick . '});' : '';
                $script .= (isset($opc->onBlur)) ? '$("#' . $opc->id . '").blur(function(){ ' . $opc->onBlur . '});' : '';
                $this->mixScript($script);
            }
            if (isset($opc->hide) && isset($opc->enRow)) {
                $this->mixScript('$("#' . $opc->id . '").parent().parent().hide();');
            }

            return $input;
        } else {
            return '';
        }

    }

    public function StrTime($fecha)
    {
        if ($fecha != '') {
            if (strstr($fecha, '-') === false) {
                $fecha = explode(' ', $fecha);
                $hora = $fecha[1] . ' ' . $fecha[2];
                $fecha = explode('/', $fecha[0]);
                $fecha = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0] . ' ' . $hora;
            }
            $fecha = strtotime($fecha);
        }
        return $fecha;
    }

    public function mixScriptCmbAutocomplete()
    {
        $this->mixScript($this->scriptComboAutocomplete);
    }
    // ===================================================================================================================
    // P E R M I S O S
    // ===================================================================================================================
    // Determina si una opción a la que se está accesando tiene un permiso determinado
    // El $_POST DEBE CONTENER los elementos sis_id, men_id y opc_id, que corresponden al sistema - menú - opción configurado.
    // OJO: Esto limita la ejecución de la función llamándola con COMUNICA(), ya que los valores men_id y opc_id no existen
    //      cuando se llama directamente desde la consola.
    // PARAMETROS:
    //    $tarea: el número de la tarea ( hay constantes definidas en \lib\constantes.php )
    //    $parametro: Puede ser un array ( el array del $_POST o una cadena con el formato sistema_menu_opcion )
    //                por ejemplo, para la cadena, podría ser "C_1_3"
    // ------------------------------------------------------------------------------------------------------------------------
    public static function tienePermisos($tarea, $parametro)
    {

        $sistema = "0";
        $menu = "0";
        $opcion = "0";

        // Si pasaron el array del $_POST ...
        if (is_array($parametro)) {

            if (empty($parametro['opcion']['sis_id'])) {
                return false;
            }

            if (empty($parametro['opcion']['men_id'])) {
                return false;
            }

            if (empty($parametro['opcion']['opc_id'])) {
                return false;
            }

            $sistema = $parametro['opcion']['sis_id'];
            $menu = $parametro['opcion']['men_id'];
            $opcion = $parametro['opcion']['opc_id'];
        }

        // Si pasaron la cadena ...
        if (is_string($parametro)) {

            $arrPaso = explode("_", $parametro);

            // DEBE resultar en un array de tres posiciones
            if (count($arrPaso) != 3) {
                return false;
            }

            $sistema = $arrPaso[0];
            $menu = $arrPaso[1];
            $opcion = $arrPaso[2];
        }

        // El número de permiso para LECTURA es 1
        return isset($_SESSION[_S]['prm'][$sistema][$menu][$opcion][$tarea]) ? true : false;
    }
    public static function sinPermisos()
    {
        $json = new stdClass();
        if (isset($_SESSION[_S]['prm'])) {
            $comunica = array('script' => 'notify("No tienes permisos para realizar ésta operación ","error");');
        } else {
            $comunica = array('script' => 'document.location.href ="../index.php";');
        }

        $json->comunica = $comunica;
        echo json_encode($json);
        die();
    }
    public function wr_WebSocket($data)
    {
        $host = 'http://172.17.90.18:8088';
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:text/plain',
                'content' => $data,
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_perr_name' => false,
            ),
        );
        $context = stream_context_create($opts);
        $res = file_get_contents($host, false, $context);
    }
    public function clasAdminGpo()
    {
        return $this->clasadmingpo;
    }
    public function clasAdminUr()
    {
        return $this->clasadminur;
    }
    public function clasAdminUej()
    {
        return $this->clasadminue;
    }
    public function sesGpo()
    {
        return $this->sesgpo;
    }
    public function sesUr()
    {
        return $this->sesur;
    }
    public static function verificaPermiso($prm)
    {
        $datos = '';
        if (is_string($prm)) {
            $puntos = substr_count($prm, ".");

            if ($puntos > 1 && $puntos < 4) {
                $datos = explode(".", $prm);

                $sis = $datos[0];
                $men = $datos[1];
                $opc = $datos[2];

                if ($puntos == 2) {
                    return (
                        (isset($_SESSION[_S]['prm'][$sis][$men][$opc]) && $_SESSION[_S]['prm'][$sis][$men][$opc] == 1)
                        ? true : false
                    );
                }

                if ($puntos == 3) {
                    $tar = $datos[3];
                    return (
                        (isset($_SESSION[_S]['prm'][$sis][$men][$opc][$tar]) && $_SESSION[_S]['prm'][$sis][$men][$opc][$tar] == 1)
                        ? true : false
                    );
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param inputId  = rfc_id
     * @param attributos  string[] uno o varios de:
     * nombre,soc_id, rfc_nombre, rfc_paterno, rfc_materno, soc_nombre
     * @param inputSecundario
     *  este es para el id de otro input donde se pasara el valor del primer atributo
     *  del parametro attributos al momento de seleccionar el proveedor
     */
    public function ponF1Proveedores($inputId = 'rfc_id', $attributos = [], $inputSecundarioId = null)
    {
        $modal_id = CONTENEDOR_CATALOGO_PROVEEDORES;
        $attributes = join(",", $attributos);
        $js = <<<JS
            $("#{$inputId}").on("keydown", function(e){
                let key = e.charCode ? e.charCode: e.keyCode ? e.keyCode: 0;
                if (key === 112) {
                    e.preventDefault();
                    $("#{$modal_id}").remove();
                    $("body").append(
                        $("<div/>").prop("id","{$modal_id}")
                            .attr("aria-target", "{$inputId}")
                            .attr("aria-attributes", "{$attributes}")
                            .attr("aria-secondary", "{$inputSecundarioId}")
                    );
                    comunica("srv/CatalogoProveedores.php");
                }
            }).attr("placeholder", "F1: para buscar un proveedor");
        JS;

        return $js;
    }
    /**
     * Esta funcion obtiene los valores de this->prm filtrados segun los valores en keys, si no existe en prm
     * regresara ese valor nulo
     * @param array $keys son las llaves a buscar en this->prm
     * @return array arreglo asociativo de llave => valor en un formato :nombre => valor para usarlo en SQLs
     */
    public function getParametrosSql(array $keys)
    {
        $values = [];
        foreach($keys as $key) {
            $values[":$key"] = isset($this->prm->$key) && !empty($this->prm->$key) ? $this->prm->$key : null;
        }
    }

    /**
     * esta funcion obtiene el valor del prm y si no existe devuelve el valor por default
     * @param string $key nombre de la llave
     *  @return any el valor
     */
    public function getPrm($key, $defaultValue = null):string|int|array|object
    {
        return !isset($this->prm->$key) ? $defaultValue : $this->prm->$key;
    }
}
if (isset($_POST['metodoWebResponse'])) {
    session_start();
    new WebResponse(true);
}