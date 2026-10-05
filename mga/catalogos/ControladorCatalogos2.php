<?php

session_start();

require_once __DIR__ . '/../../lib/constantes.php';
require_once __DIR__ . '/../../lib/WebResponse.php';

class ControladorTest extends WebResponse
{
    private $server;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'mga/catalogos/ControladorCatalogos2.php';
        $this->server = BD_CTRL;
        $tablassinpres = [
            'cat_ClasificadorFuenteFinanciamiento',
            'cat_Fuente' . APP_EJERCICIO,
            'cat_Subfuente' . APP_EJERCICIO,
            'cat_OrigenRecurso' . APP_EJERCICIO,
            'cat_ClasificacionSuborigen',
            'cat_SuborigenRecurso' . APP_EJERCICIO,
            'cat_ClaveFinanciamiento' . APP_EJERCICIO,
        ];

        if (isset($this->prm->tabla) && in_array($this->prm->tabla, $tablassinpres)) {
            $this->server = BD_SINPRES;
        }

        switch ($_SESSION[_S]['nvj_id']) {
            case 3:
            case 4:
            case 5:
            case 7:
                switch ($this->metodo) {
                    case 'main':
                        $this->main();
                        break;
                    case 'lista':
                        $this->lista();
                        break;
                    case 'edit':
                        $this->edit();
                        break;
                    case 'abcCatalogos':
                        $this->abcCatalogos();
                        break;
                    case 'llenaTablas':
                        $this->llenaTablas();
                        break;
                    case 'HabilitaTabla':
                        $this->HabilitaTabla();
                        break;
                    case 'nuevo':
                        $this->nuevo();
                        break;
                    case 'recargaCombotest':
                        $this->recargaCombotest();
                        break;
                    case 'encuadre':
                        $this->encuadres();
                        break;
                    case 'getEncId':
                        $this->getEncId();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('');

        // Solo mostrar catálogos cuya tabla física ya exista (liberación paulatina).
        $sql = 'SELECT t.*, e.*
            FROM cat_Tablas t
            INNER JOIN enc_TablasUsuario e USING (tbl_id)
            WHERE e.usr_id = :usrId
                AND EXISTS (
                    SELECT 1
                    FROM information_schema.tables ist
                    WHERE ist.table_name = t.tbl_nombre
                    AND ist.table_schema IN ("' . BD_CTRL . '", "' . BD_SINPRES . '")
                )
            ORDER BY t.tbl_id';
        $parametros = [':usrId' => $_SESSION[_S]['usr_id']];

        if (isset($_SESSION[_S]['prm']['M']['6']['3'][TAREA_LECTURA]) && !isset($_SESSION[_S]['prm']['M']['6']['3'][TAREA_ESCRITURA])) {
            $sql .= ' AND sts_id = 1 AND sis_id = "A"';
        }

        $com = new AccesoDatos();
        $com->conectar();
        $com->setSql($sql);
        $com->consultar($parametros);
        $com->desconectar();

        $html = '
            <div id="loading">
                <div id="mensajeloading">Procesando registros....</div>
            </div>
            <div style="border-top:1px solid rgba(200,200,200,.7); border-right:1px solid rgba(200,200,200,.7);border-left:1px solid rgba(200,200,200,.7); padding:10px; font-size:14px;">Seleccione el catalogo que desea Editar</div>
            <div class="row" style="background-color:#FFF;padding:10px; border:1px solid #EAEAEA; color:#666; font-size:14px;">';

        while ($data = $com->extraer()) {
            $btn = '';
            $css = 'width:100%;';
            if (isset($_SESSION[_S]['prm']['M']['6']['3']['99'])) {
                $btn = '<button  class="btnhabilita"  sts_id="' . ($data['sts_id'] == 1 ? '2' : '1') . '" tbl_id="' . $data['tbl_id'] . '" >' . ($data['sts_id'] == 1 ? 'X' : '☺') . '</button>';
                $css = 'width:85%;';
            }

            $html .= '
                <div style="min-width: 330px; margin:5px;"class="col-xs-1 col-sm-1" title="' . $data['tbl_nombre'] . '">
                    <span style="' . $css . '" class="editatablas" registros="' . $data['tbl_registros'] . '" nombre="' . $data['tbl_nombre'] . '">' . $data['tbl_nombre'] . '</span>' . $btn . '
                </div>';
        }

        $html .= '</div>';

        $this->mixHtml($html);
        $this->mixScript('
            $(".editatablas").click(function() {
                html = "Procesando " + $(this).attr("registros") + " registros de la tabla " + $(this).attr("nombre") + "...";
                $("#mensajeloading").html(html);
                $("#loading").css("display","block");
                comunica("' . $this->pantalla . '", {
                    metodo:"lista",
                    "tabla":$(this).attr("nombre"),
                    "registros": $(this).attr("registros")
                });
            });

            $(".btnhabilita").click(function() {
                comunica("' . $this->pantalla . '", {
                    "metodo":"HabilitaTabla",
                    sts_id:$(this).attr("sts_id"),
                    tbl_id:$(this).attr("tbl_id")
                });
            });
        ');
    }

    public function lista()
    {
        error_reporting(E_ALL);
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->class = 'regresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';

        $btnnuevo = new stdClass();
        $btnnuevo->id = 'btnnuevo';
        $btnnuevo->name = 'btnnuevo';
        $btnnuevo->class = 'Nuevo';
        $btnnuevo->text = 'Nuevo';
        $btnnuevo->value = 'Nuevo';
        $btnnuevo->color = 'verde';
        $btnnuevo->icon = 'fa-solid fa-plus me-2';

        $btnBuscar = new stdClass();
        $btnBuscar->id = 'btnBuscar';
        $btnBuscar->name = 'btnBuscar';
        $btnBuscar->class = 'Buscar';
        $btnBuscar->text = 'Buscar';
        $btnBuscar->value = 'Buscar ';
        $btnBuscar->color = 'azul';
        $btnBuscar->icon = 'fa-regular fa-magnifying-glass me-2';
        $tools = '<div style="display:table-cell; padding:3px;"><br>' . $this->ponBoton($btnBuscar) . '</div>';

        $botones = $this->ponBoton($btnregresarindex);
        $botones .= $this->ponBoton($btnnuevo);
        $this->agregaTools($botones);

        if ($this->prm->tabla == 'cat_SociedadProveedor') {
            $this->prm->tabla = BD_SINPRES . '.cat_SociedadProveedor';
            $this->server = BD_SINPRES;
        }

        $datos = $this->get_TableColumns($this->server . '.' . $this->prm->tabla);
        $columns = $datos['columnas'];
        $primary = $datos['primaria'];

        $html = '<div style="border:1px solid #EAEAEA;padding:10px; font-weight:bold; font-size:14px;">CATALOGO ' . strtoupper(substr($this->prm->tabla, 4, 100)) . '</div>';

        $tabla = new stdClass(); // TABLA
        $tr = []; // ENCABEZADO
        $trbody = []; // CUERPO

        $tabla->id = 'tblListas';
        $tabla->titulo = 'Registros';
        $tabla->cabeceraFija = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablas';
        $tabla->comunica->tabla = $this->prm->tabla;
        $tabla->numFilas = 50;
        $tabla->filasAmostrar = [10, 50, 100, 150, 200, 500, 1000];

        $th = new stdClass();
        $th->texto = 'Editar';
        $th->width = '1%';
        array_push($tr, $th);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->class = 'btn-xs';
        $td->attr = new stdClass();

        foreach ($primary as $key) {
            $td->attr->$key = $key;
        }

        $td->class = 'editRegistro';
        $td->name = 'ced_id';
        $td->id = 'ced_id';
        array_push($trbody, $td);

        foreach ($columns as $val) {
            /* CABECERA DE LA COLUMNA */
            $n = $val->nombre;
            $th = new stdClass();
            $th->texto = $n;
            array_push($tr, $th);

            /* PARA LA BUSQUEDA POR LLAVE PRIMARIA */
            if ($val->llave == 'PRI') {
                $tabla->comunica->$n = '{$(\'#' . $val->nombre . '\').val()}';
                $inputUr = new stdClass();
                $inputUr->id = $val->nombre;
                $inputUr->label = $val->nombre;
                $inputUr->placeholder = $val->comentario;
                $inputUr->enDiv = true;
                $inputUr->required = true;
                $inputUr->maxLength = $val->largo;
                $tools .= '<div style="display:table-cell; padding:3px 3px 20px 2px;">' . $this->ponInput($inputUr) . '</div>';
            }

            if ($val->nombre == 'ind_nombre') {
                $tabla->comunica->$n = '{$(\'#' . $val->nombre . '\').val()}';
                $inputUr = new stdClass();
                $inputUr->id = $val->nombre;
                $inputUr->label = $val->nombre;
                $inputUr->placeholder = $val->comentario;
                $inputUr->enDiv = true;
                $inputUr->required = true;
                $inputUr->maxLength = $val->largo;
                $tools .= '<div style="display:table-cell; padding:3px 3px 20px 2px;">' . $this->ponInput($inputUr) . '</div>';
            }

            /* VALOR DE LA COLUMNA */
            $td = new stdClass();
            $td->campoTxt = $n;
            $td->align = 'left';
            $td->ajustable = true;

            if (strpos($n, 'color') !== false || strpos($n, 'linea') !== false) {
                $td->tipo = 'bolita';
                $td->color = $n;
                $td->lbl = $n;
                $td->linea = $n;
            }
            array_push($trbody, $td);
        }

        $tabla->tools = $tools;
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $html .= $this->wrPonTabla($tabla);
        $this->mixHtml($html);
        $this->mixScript('
            $("#btnregresarindex").click(function() {
                $("#toolsPantalla").html("");
                comunica("' . $this->pantalla . '",{metodo:"main"})
            });

            $("#btnnuevo").click(function() {
                comunica("' . $this->pantalla . '",{metodo:"nuevo","tabla":"' . $this->prm->tabla . '"})
            });

            $("#loading").hide();
            ' . $this->wrLlamaLlenaTabla('tblListas') . '
        ');
    }

    private function llenaTablas()
    {
        $llave = '';
        $consultatabla = 'SELECT ';
        $select = '';
        $where = '';
        $parametros = [];

        $d = $this->get_TableColumns($this->server . '.' . $this->prm->tabla);

        foreach ($d['columnas'] as $val) {
            $n = $val->nombre;
            if ($val->llave == 'PRI' || $n == 'ind_nombre') {
                $llave .= ',"' . $n . '":$(this).attr("' . $n . '")';
                if (isset($this->prm->$n) && $this->prm->$n != '') {
                    $operador = ($n == 'ind_nombre') ? 'LIKE' : '=';
                    $valor    = ($n == 'ind_nombre') ? '%' . $this->prm->$n . '%' : $this->prm->$n;

                    if ($where == '') {
                        $where .= ' WHERE ' . $n . ' ' . $operador . ' ?';
                    } else {
                        $where .= ' AND ' . $n . ' ' . $operador . ' ?';
                    }
                    array_push($parametros, $valor);
                }
            }
            if ($select == '') {
                $select .= $n;
            } else {
                $select .= ', ' . $n;
            }
        }

        $consultatabla .= $select . ' FROM ' . $this->server . '.' . $this->prm->tabla . $where;

        $this->setSql($consultatabla);
        $this->setParams($parametros);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblListas';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".editRegistro").click(function() {
                comunica("' . $this->pantalla . '",{ metodo:"edit", tabla:"' . $this->prm->tabla . '"' . $llave . ' });
            });

            $("#btnBuscar").click(function() {
                ' . $this->wrLlamaLlenaTabla('tblListas') . '
            });
        ');
    }

    private function edit()
    {
        $server = $this->server;
        $this->mixHtml('<div id="loading"><div id="mensajeloading">Procesando registros....</div></div>');
        $html = '<legend>EDITANDO REGISTRO DE LA TABLA ' . strtoupper($this->prm->tabla) . '</legend>';
        $form = new stdClass();
        $form->id = 'editacatalogo';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcCatalogos';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'C';
        $formulario = new form($form);

        $d = $this->get_TableColumns($this->server . '.' . $this->prm->tabla);
        $pref = explode('_', $this->prm->tabla);
        $pref = $pref[0] . '_';

        $sql = 'SELECT * FROM ' . $this->server . '.' . $this->prm->tabla . ' WHERE ';
        $parametros = [];
        $select = '';
        foreach ($d['primaria'] as $val) {
            if ($select == '') {
                $select .= $val . ' = ?';
            } else {
                $select .= ' AND ' . $val . ' = ?';
            }
            array_push($parametros, $this->prm->$val);
        }

        $sql .= $select . ';';

        $this->setSql($sql);
        $this->conectar();
        $this->consultar($parametros);
        $this->desconectar();
        $registro = $this->extraer();

        $tipo = '';
        $tipouser = '';
        $requerido = '';
        $largo = '';
        $deshabilitado = '';
        $ton = '';
        $webresponse = new WebResponse();

        foreach ($d['columnas'] as $val) {
            if ($val->llave == 'PRI') {
                $deshabilitado = 'readonly="readonly"';
            } else {
                $deshabilitado = '';
            }

            if (!$val->nulo) {
                $requerido = 'required="required"';
            } else {
                $requerido = '';
            }

            switch ($val->tipo) {
                case 'int':
                case 'int unsigned':
                    $tipo = 'class="form-control entero"';
                    $tipouser = 'NUMERICO';
                    $this->mixScript('
                        $("#' . $val->nombre . '").keypress(function(event) {
                            var anularTecla = false;
                            var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                            anularTecla = ((event.which < 48 || event.which > 57) && !esPermitido);
                            if (anularTecla) {
                                event.preventDefault();
                            }
                        });
                    ');
                    $ton = '';
                    break;
                case 'varchar':
                case 'char':
                    $tipo = 'class="form-control texto"';
                    $tipouser = 'LETRAS Y NUMEROS';
                    $ton = '';
                    break;
                case 'text':
                    $tipo = 'class="form-control texto"';
                    $tipouser = 'TEXTO DE GRAN LONGITUD';
                    $ton = '';
                    break;
                case 'datetime':
                case 'date':
                    $tipo = 'class="input-group date" data-date-format="dd/mm/yyyy"';
                    $tipouser = 'FECHA';
                    $ton = '<span class="input-group-btn">
                                <button class="btn btn-default" tabindex=-1 type="button">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </button>
                            </span>
                        </div>';
                    $this->mixScript('$("#' . $val->nombre . '").datepicker({"keyboardNavigation":true,"language":"es"}); ');
                    break;
                case 'decimal':
                    $tipo = 'class="form-control entero"';
                    $tipouser = 'NUMERICO CON PUNTO DECIMAL (2 DECIMALES)';
                    $this->mixScript('
                        $("#' . $val->nombre . '").keypress(function(event) {
                            var anularTecla = false;
                            var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                            anularTecla = ((event.which < 48 || event.which > 57) && !esPermitido);
                            if(event.which == 46) {
                                anularTecla = false;
                            }
                            if(anularTecla) {
                                event.preventDefault();
                            }
                        });
                    ');
                    $ton = '';
                    break;
                default:
                    $tipouser = 'UNKNOWN';
                    break;
            }

            $html .= '<div class="form-group">';
            switch ($val->nombre) {
                case 'sts_id':
                    $html .= '<label for="' . $val->nombre . '">ESTADO';
                    break;
                case 'ter_id':
                    $html .= '<label for="' . $val->nombre . '">TIPO DE COMPRA';
                    break;
                case 'ind_mediosVerificacion':
                    $html .= '<label>';
                    break;
                default:
                    $html .= '<label for="' . $val->nombre . '">' . strtoupper($val->nombre);
                    break;
            }

            $html .= '<br>
                <span style="color:RGBA(200,0,0,.5); margin-right:15px;">' . $val->comentario . '</span>
                <span style="color:#999; font-size:10px;">EL TIPO DE DATO ES ' . $tipouser . ' ' . (is_numeric($val->largo) ? 'SU LONGITUD MAXIMA ES DE ' . $val->largo : '') . '</span></label>';

            $input = true;

            if (substr($val->nombre, 4, 5) == 'color' || substr($val->nombre, 4, 5) == 'linea') {
                $input = false;
                $html .= 'ESTE INDICA EL COLOR DEL ESTADO O SEMAFORIZACIÓN YA SEA PARA EL RELLENO (COLOR) O EL BORDE (LINEA)  CUANDO SE PINTA EN UNA TABLA, NEGRO SIGNIFICA QUE ESTA VACIO    ';
                $html .= '<br><input type="color" name="' . $val->nombre . '"  value="#' . $registro[$val->nombre] . '" class="jmacolorpicker"/>';
                $html .= '</span>';
                $html .= '</label></div>';
            }

            if ($this->prm->tabla != 'cat_Estado' && $val->nombre == 'sts_id') {
                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = 'sts_id';

                if ($requerido == 'required="required"') {
                    $tipopag->required = true;
                }

                $tipopag->enDiv = true;
                $tipopag->camposVal = 'sts_id';
                $tipopag->agrupaPor = 'sts_id';
                $tipopag->campoLabelAgrupacion = 'sts_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Estado';
                $tipopag->selecciona = $registro[$val->nombre];
                $tipopag->comoAtr = 'sts_id';
                $tipopag->sql = 'SELECT sts_id,sts_nombre FROM cat_Estado;';
                $html .= $webresponse->ponCombo($tipopag) . '</div>';
            }

            if ($this->prm->tabla != 'cat_TipoCompra' && $val->nombre == 'tcp_id') {
                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = 'tcp_id';
                $tipopag->name = 'tcp_id';
                $tipopag->required = true;
                $tipopag->enDiv = true;
                $tipopag->camposVal = 'tcp_id';
                $tipopag->agrupaPor = 'tcp_id';
                $tipopag->campoLabelAgrupacion = 'tcp_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Tipo de Compra';
                $tipopag->comoAtr = 'tcp_id';
                $tipopag->sql = 'SELECT tcp_id,tcp_nombre FROM cat_TipoCompra;';
                $html .= $webresponse->ponCombo($tipopag);
            }

            $placeholder = '';

            if ($this->prm->tabla != 'cat_TipoRecurso' && ($val->nombre == 'ter_id' || $val->nombre == 'trp_id_distribucion' || $val->nombre == 'trp_id')) {
                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = $val->nombre;
                $tipopag->name = $val->nombre;
                if ($requerido == ' required="required" ') {
                    $tipopag->required = true;
                }
                $tipopag->enDiv = true;
                $tipopag->camposVal = 'ter_id';
                $tipopag->agrupaPor = 'ter_id';
                $tipopag->campoLabelAgrupacion = 'ter_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Tipo de Recurso';
                $tipopag->comoAtr = 'ter_id';
                $tipopag->selecciona = $registro[$val->nombre];
                $tipopag->sql = 'SELECT ter_id,ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso;';
                $html .= $webresponse->ponCombo($tipopag);
            }

            if ($val->llave != 'PRI' && $val->nombre != 'sts_id' && $val->nombre != 'tcp_id' && $val->nombre != 'ter_id' && $val->nombre != 'trp_id_distribucion' && $val->nombre != 'trp_id' && $val->nombre != 'gpo_id' && $val->nombre != 'ur_id' && $this->prm->tabla != 'cat_Programa') {
                $sql2 = 'SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla AND COLUMN_NAME = :columna AND REFERENCED_TABLE_NAME IS NOT NULL';
                $parametros = [':server' => $server, ':tabla' => $this->prm->tabla, ':columna' => $val->nombre];

                $nuevo = new AccesoDatos();
                $nuevo->conectar();
                $nuevo->setSql($sql2);
                $nuevo->consultar($parametros);
                $foranea = $nuevo->extraer();
                $nuevo->desconectar();

                if (isset($foranea['REFERENCED_TABLE_NAME']) && !empty($foranea['REFERENCED_TABLE_NAME'])) {
                    $nuevo->conectar();
                    $nuevo->setSql('SELECT COLUMN_NAME colNombre FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = :referencedTableName AND table_schema = :server HAVING colNombre like "%_nombre";');
                    $parametros = [':referencedTableName' => $foranea['REFERENCED_TABLE_NAME'], ':server' => $server];
                    $nuevo->consultar($parametros);
                    $campoNombre = $nuevo->extraer();
                    $campoNombre = is_array($campoNombre) ? $campoNombre['colNombre'] : '';
                    $nuevo->desconectar();
                    unset($nuevo);

                    $placeholder = '';
                    if (strlen($foranea['REFERENCED_TABLE_NAME']) > 4 && substr($foranea['REFERENCED_TABLE_NAME'], 0, 3) != 'enc' && $foranea['REFERENCED_TABLE_NAME'] != 'cg_CuentaMayor') {
                        $input = false;
                        $campo = $val->nombre;

                        switch ($campo) {
                            case 'umi_id1':
                                $campo = 'umi_id';
                                break;
                            default:
                                $campo = $val->nombre;
                        }

                        $tipopag = new stdClass();
                        $tipopag->id = $val->nombre;
                        $tipopag->name = $val->nombre;

                        $tipopag->enDiv = true;
                        $tipopag->camposVal = $campo;
                        $tipopag->agrupaPor = $campo;
                        $tipopag->campoLabelAgrupacion = substr($val->nombre, 0, 3) . '_nombre';
                        $tipopag->mensaje = 'Selecciona un dato';
                        $tipopag->selecciona = $registro[$val->nombre];
                        $tipopag->label = 'DE LA TABLA ' . strtoupper($foranea['REFERENCED_TABLE_NAME']);
                        $sql3 = 'SELECT ' . $campo . ',' . $campoNombre . ' FROM ' . $foranea['REFERENCED_TABLE_NAME'] . ';';

                        $tipopag->sql = $sql3;
                        $html .= $webresponse->ponCombo($tipopag);
                        $html .= '</div>';
                    } elseif (substr($foranea['REFERENCED_TABLE_NAME'], 0, 4) == 'enc_') {
                        $this->mixScript('
                            $("#' . $val->nombre . '").dblclick(function(){
                                if ($("#det_fs' . $foranea['REFERENCED_TABLE_NAME'] . '").length !=1) {
                                    load_viewer("' . $foranea['REFERENCED_TABLE_NAME'] . '", "editar ' . $foranea['REFERENCED_TABLE_NAME'] . '", "d");
                                    comunica("mga/ControladorCatalogos2.php", {
                                        metodo:"encuadre",
                                        tabla:"' . $foranea['REFERENCED_TABLE_NAME'] . '",
                                        server:"' . $server . '",
                                        id:$(this).val()
                                    });
                                }
                            });

                        ');
                        $placeholder = 'SI NO CONOCE EL VALOR DE DOBLE CLICK PARA EDITARLO';
                    }
                }
            }

            if ($input && $val->nombre != 'ind_mediosVerificacion') {
                $html .= $placeholder . '
                    <input id="' . $val->nombre . '"  ' . $tipo . '
                        type="text"
                        name="' . $val->nombre . '"
                        maxlength="' . $largo . '" ' . $requerido . '
                        placeholder="' . $placeholder . '" ' . $deshabilitado . '
                        value="' . $registro[$val->nombre] . '"/>
                    </div>' . $ton;
                if ($val->llave == 'PRI') {
                    $html .= '<input id="' . $val->nombre . '"  ' . $tipo . ' type="hidden" name="' . $val->nombre . '" value="' . $registro[$val->nombre] . '" />';
                }
            }
        }

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresar';
        $btnregresar->name = 'btnregresarindex';
        $btnregresar->class = 'regresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->color = 'gris';
        $btnregresar->icon = 'circle-left me-2';

        $guardar = new stdClass();
        $guardar->id = 'guardar';
        $guardar->name = 'guardar';
        $guardar->class = 'regresar';
        $guardar->text = 'Guardar';
        $guardar->color = 'verde';
        $guardar->value = 'Guardar';
        $guardar->icon = 'floppy-disk me-2';
        $guardar->type = 'submit';

        //hidden para la tabla
        $html .= '<input type="hidden" name="tabla" value="' . $this->prm->tabla . '"/>';
        $html .= '</form>';
        $botones = '';
        $botones .= $this->ponBoton($btnregresar);
        $botones .= $this->ponBoton($guardar);

        $formulario->mixHtml($html);

        $this->mixHtml($formulario->ponForm());
        $this->mixScript($webresponse->getScript());

        $html2 = '';
        $html2 .= '<br><br><br><div class="alert alert-info">LO CAMPOS DESHABILITADOS SON LLAVES QUE NO SE PUEDEN EDITAR</div>';
        $this->mixScript('
            $("#btnregresar").click(function() {
                $("#toolsPantalla").html("");
                $("#mensajeloading").html("Regresando al listado..." );
                $("#loading").show();
                comunica("' . $this->pantalla . '",{ metodo:"lista",tabla:"' . $this->prm->tabla . '" });
            });

            $("#guardar").click(function() {
                var valido = true;

                $(".form-control").each(function() {
                    if ($(this).val() == "" && $(this).attr("required") != undefined) {
                        valido = false;
                        $(this).css("border","1px solid #FF0000");
                        $(this).append("<div style=\"display:block;\">Este es requerido<div>");
                    }
                });
                if (valido == true) {
                    $("#editacatalogo").submit();
                } else {
                    notify("Existen campos vacios", "error");
                }
            });

            const catNivelMir = document.querySelector("#nim_id");
            if(catNivelMir){
                nivelMir = document.getElementById("nim_id").value;
                if(nivelMir!=""){
                    if(nivelMir == 3){
                        var select = document.getElementById("frm_id");
                        for (var i = select.options.length - 1; i >= 0; i--) {
                            var option = select.options[i];
                            if (select.options[i].value === "" || select.options[i].value === "1" || select.options[i].value === "3" || select.options[i].value === "4" || select.options[i].value === "5" || select.options[i].value === "6") {
                                option.style.display = "none";
                            }
                        }
                        var combo = document.getElementById("frm_id");
                        combo.value = "2";
                        $(".selectpicker").selectpicker("refresh");

                        var select = document.getElementById("tpi_id");
                        for (var i = select.options.length - 1; i >= 0; i--) {
                            var option = select.options[i];
                            if (select.options[i].value === "" || select.options[i].value === "2") {
                                option.style.display = "none";
                            }
                        }
                        var comboTipoIndicador = document.getElementById("tpi_id");
                        comboTipoIndicador.value = "1";
                        $(".selectpicker").selectpicker("refresh");
                    }
                    if(nivelMir == 4) {
                        var select = document.getElementById("frm_id");
                        for (var i = select.options.length - 1; i >= 0; i--) {
                            var option = select.options[i];
                            if (select.options[i].value === "" || select.options[i].value === "2" || select.options[i].value === "3" || select.options[i].value === "4" || select.options[i].value === "5" || select.options[i].value === "6") {
                                option.style.display = "none";
                            }
                        }
                        var combo = document.getElementById("frm_id");
                        combo.value = "1";
                        $(".selectpicker").selectpicker("refresh");

                        var select = document.getElementById("tpi_id");
                        for (var i = select.options.length - 1; i >= 0; i--) {
                            var option = select.options[i];
                            if (select.options[i].value === "" || select.options[i].value === "2") {
                                option.style.display = "none";
                            }
                        }
                        var comboTipoIndicador = document.getElementById("tpi_id");
                        comboTipoIndicador.value = "1";
                        $(".selectpicker").selectpicker("refresh");
                    }
                }
            }

            $(".texto").blur(function() {
                $(this).val($(this).val().toUpperCase());
            })
        ');
        $this->mixHtml($html2);
        $this->setContenedor('toolsPantalla');
        $this->mixHtml($botones);
    }

    private function abcCatalogos()
    {
        $server = $this->server;
        if ($this->prm->tabla == 'cat_SociedadProveedor' || $this->prm->tabla == BD_SINPRES . '.cat_SociedadProveedor') {
            $server = BD_SINPRES;
        }

        $tabla = $this->prm->tabla;
        $datos = $this->get_TableColumns($this->server . '.' . $tabla);

        $columnas = $datos['columnas'];
        $primaria = $datos['primaria'];
        $usr = $datos['usr'];

        $primarias = '';
        $campos = '';
        $sql = '';
        $mensaje = '';
        $usr = $datos['usr'];
        $parametros = [];

        switch ($this->prm->abc) {
            case 'A':
                $tablasAutoincrementalesProcedimiento = ['cat_ProductoFinal', 'cat_ProductoIntermedio'];
                $camposinsert = '';
                $valuesinsert = '';
                $infousr = '';
                if (!in_array($this->prm->tabla, $tablasAutoincrementalesProcedimiento)) {
                    $sqlSelect = 'SELECT * FROM ' . $this->server . '.' . $this->prm->tabla . ' WHERE ';
                    $camposSelect = '';
                    $parametros = [];

                    /* VALIDAR QUE EL REGISTRO QUE INTENTA INSERTAR NO EXISTA */
                    foreach ($primaria as $val) {
                        if ($camposSelect == '') {
                            $camposSelect .= $val . ' = ?';
                        } else {
                            $camposSelect .= ' AND ' . $val . ' = ?';
                        }
                        array_push($parametros, $this->prm->$val ?? '');
                    }

                    $conexion = new AccesoDatos();
                    $sqlSelect .= $camposSelect;
                    $conexion->conectar();
                    $conexion->setSql($sqlSelect);
                    $conexion->consultar($parametros);
                    $registros = $conexion->getNumeroRegistros();
                    $conexion->desconectar();
                    unset($conexion);

                    if ($registros > 0) {
                        $this->mixScript('notify("Este Registro ya existe", "error");');
                        return false;
                        break;
                    }
                }

                $parametros = [];

                foreach ($columnas as $val) {
                    $n = $val->nombre;

                    if (isset($this->prm->$n) && $this->prm->$n != '') {
                        if ($camposinsert == '') {
                            $camposinsert .= $n;
                            if ($val->tipo == 'date') {
                                $f = explode('/', $this->prm->$n);
                                $valuesinsert .= '(?)';
                                array_push($parametros, $f[2] . '-' . $f[1] . '-' . $f[0]);
                            } else {
                                $valuesinsert .= '?';
                                array_push($parametros, strtoupper($this->prm->$n));
                            }
                        } else {
                            $camposinsert .= ', ' . $n;
                            if (strpos($n, 'color') || strpos($n, 'linea')) {
                                $valuesinsert .= ', ?';
                                array_push($parametros, strtoupper(str_replace('#', '', $this->prm->$n)));
                            } else {
                                if ($val->tipo == 'date') {
                                    $f = explode('/', $this->prm->$n);
                                    $valuesinsert .= ', ?';
                                    array_push($parametros, $f[2] . '-' . $f[1] . '-' . $f[0]);
                                } else {
                                    $valuesinsert .= ', ?';
                                    array_push($parametros, strtoupper($this->prm->$n));
                                }
                            }
                        }
                    }
                }

                $sql = 'INSERT INTO ' . $this->server . '.' . $tabla . ' (';

                if ($usr) {
                    $sql .= $camposinsert . ', usr_id';
                } else {
                    $sql .= $camposinsert;
                }

                if ($usr) {
                    $infousr .= ', ?';
                    array_push($parametros, $_SESSION[_S]['usr_id']);
                }

                $sql .= ') VALUES (';
                $sql .= $valuesinsert . $infousr . ');';

                $mensaje = 'agregado';

                break;
            case 'C':
                $usr = false;
                $parametros = [];
                $aux = 0;

                foreach ($primaria as $pri) {
                    if ($primarias == '') {
                        $primarias .= $pri . ' = :primaria' . $aux;
                    } else {
                        $primarias .= ' AND ' . $pri . ' = :primaria' . $aux;
                    }
                    $parametros[':primaria' . $aux] = $this->prm->$pri;
                    $aux++;
                }

                $aux = 0;

                foreach ($columnas as $val) {
                    $n = $val->nombre;
                    if (!in_array($n, $primaria)) {
                        if ($n == 'usr_id') {
                            $usr = true;
                        }

                        if ($n == 'usr_fecha') {
                            $usr = true;
                        }

                        if (isset($this->prm->$n)) {
                            $valor = strtoupper($this->prm->$n);
                        }

                        if (strpos($n, 'color') || strpos($n, 'linea')) {
                            $valor = strtoupper(str_replace('#', '', $this->prm->$n));
                        }

                        if ($valor != '') {
                            if ($campos == '') {
                                $campos .= $n . ' = :campo' . $aux;
                            } else {
                                $campos .= ', ' . $n . ' = :campo' . $aux;
                            }
                            $parametros[':campo' . $aux] = strtoupper($valor);
                            $aux++;
                        }
                    }
                }

                if ($usr) {
                    $campos .= ', usr_id = :usuario';
                    $parametros[':usuario'] = $_SESSION[_S]['usr_id'];
                }

                $sql = 'UPDATE ' . $this->server . '.' . $tabla . '
                        SET ' . $campos . '
                        WHERE ' . $primarias;

                $mensaje = 'actualizado';

                break;
        }

        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $usr_id = $_SESSION[_S]['usr_id'];

        $this->conectar();
        $this->setSql('SET @bit_observacion="REGISTRO DESDE EL EDITOR DE CATALOGOS GENERAL (MGA)", @usr_id = :usrId, @usr_ip=INET_ATON(:ip);');
        $this->consultar([':usrId' => $usr_id, ':ip' => $ip]);
        $this->setSql($sql);
        $this->consultar($parametros);
        $sql2 = 'UPDATE cat_Tablas AS A
                JOIN (
                    SELECT table_name AS tbl_nombre, SUM(CASE WHEN table_schema = :server THEN table_rows ELSE NULL END) AS "Registros"
                    FROM information_schema.tables
                    WHERE table_schema = :server
                    GROUP BY table_name
                    ORDER BY table_name DESC
                ) B USING(tbl_nombre)
                SET A.tbl_registros = B.Registros;';
        $parametros2 = [':server' => $server];
        $this->setSql($sql2);
        $this->consultar($parametros2);
        $this->desconectar();

        /******************* LOG DE LAS AFECTACIONES************************************/
        $rutalog = '../catalogos/log/catalogos-' . $_SESSION[_S]['usr_id'] . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');

        foreach ($this->prm as $field => $value) {
            $tempdata .= '|' . $field . '=' . $value;
        }

        if (isset($_SESSION[_S]['ses_ip']) && !empty($_SESSION[_S]['ses_ip'])) {
            $tempdata .= '|IPADDRESS=' . $_SESSION[_S]['ses_ip'];
        }
        $tempdata .= '|USRID=' . $_SESSION[_S]['usr_id'];
        $tempdata .= '|CONSULTA=' . $sql;

        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);

        $peso = ($peso / 1024) / 1024;

        if ($peso > 8) {
            $rutamodificada = './LOGS/catalogos-' . $_SESSION[_S]['usr_id'] . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }
        /******************* fin del log************************************/

        $this->mixScript('
            notify("Se ha ' . $mensaje . ' el registro correctamente", "success");
            $("#mensajeloading").html("Regresando al listado..." );
            $("#loading").show();
            comunica("' . $this->pantalla . '",{metodo:"lista","tabla":"' . $this->prm->tabla . '"});
        ');
    }

    private function HabilitaTabla()
    {
        $sql = 'UPDATE cat_Tablas SET sts_id = :sts_id WHERE tbl_id = :tbl_id;';
        $parametros = [':sts_id' => $this->prm->sts_id, ':tbl_id' => $this->prm->tbl_id];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $this->desconectar();
        $this->mixScript('comunica("' . $this->pantalla . '", {"metodo":"main"} );');
    }

    private function nuevo()
    {
        $this->mixHtml('<div id="loading"><div id="mensajeloading">Procesando registros....</div></div>');
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->class = 'regresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->icon = 'circle-left me-2';

        $btnnuevo = new stdClass();
        $btnnuevo->id = 'btnguardar';
        $btnnuevo->name = 'btnguardar';
        $btnnuevo->class = 'guardar';
        $btnnuevo->color = 'verde';
        $btnnuevo->text = 'Guardar';
        $btnnuevo->value = 'Guardar';
        $btnnuevo->icon = 'floppy-disk me-2';

        $campossintabla = [];
        $tablaprincipal = $this->prm->tabla;

        if ($this->prm->tabla == 'cat_SociedadProveedor' || $this->prm->tabla == 'cat_ClaveFinanciamiento' . APP_EJERCICIO) {
            $this->server = BD_SINPRES;
        }

        $server = $this->server;

        $sql = 'SELECT COLUMN_NAME, ORDINAL_POSITION
                FROM information_schema.KEY_COLUMN_USAGE AS A
                WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                AND POSITION_IN_UNIQUE_CONSTRAINT IS NULL AND ORDINAL_POSITION IN (
                    SELECT MAX(ORDINAL_POSITION) AS ORDINAL_POSITION
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla);';
        $parametros = [':server' => $server, ':tabla' => $tablaprincipal];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $data = $this->extraer();
        $this->desconectar();

        $idtabla = $data['COLUMN_NAME'] ?? '';
        $tablasrelacionadas = $data['ORDINAL_POSITION'] ?? 0;

        $tablas = [];
        $campos = [];
        $combosval = [];

        $tablas[$tablasrelacionadas] = $this->prm->tabla;
        $campos[$tablasrelacionadas] = $data['COLUMN_NAME'] ?? '';
        $combosval[$tablasrelacionadas] = 0;

        $html = '';
        $ultimatabla = true;
        $this->conectar();

        if ($this->prm->tabla == 'cat_ClaveFinanciamiento' . APP_EJERCICIO || $this->prm->tabla == 'cat_SuborigenRecurso' . APP_EJERCICIO || $this->prm->tabla == 'cat_Subfuente' . APP_EJERCICIO || $this->prm->tabla == 'cat_OrigenRecurso' . APP_EJERCICIO) {
            $tablasrelacionadas = 0;
        }

        for ($i = $tablasrelacionadas; $i >= 1; $i--) {
            $tablasrelacionadas--;

            if ($ultimatabla) {
                $tablaabuscar = $tablas[$i] == '' ? $this->prm->tabla : $tablas[$i];
            } else {
                $tablaabuscar = $tablas[$i] == '' ? $this->prm->tabla : $tablas[$i + 1];
            }

            $sql = 'SELECT REFERENCED_TABLE_NAME,COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE AS A
                    WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                    AND POSITION_IN_UNIQUE_CONSTRAINT IS NOT NULL AND ORDINAL_POSITION = :tablasrelacionadas
                    AND COLUMN_NAME IN (
                        SELECT COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE AS A
                        WHERE TABLE_SCHEMA = :server
                            AND TABLE_NAME = :tabla
                            AND POSITION_IN_UNIQUE_CONSTRAINT IS  NULL
                            AND CONSTRAINT_NAME ="PRIMARY"
                            AND ORDINAL_POSITION = :tablasrelacionadas);';
            $parametros = [':server' => $server, ':tabla' => $tablaabuscar, ':tablasrelacionadas' => $tablasrelacionadas];

            $this->setSql($sql);
            $this->consultar($parametros);
            $datos = $this->extraer();

            if (!isset($datos['COLUMN_NAME']) || strlen($datos['COLUMN_NAME']) == 0 || !isset($datos['REFERENCED_TABLE_NAME']) || strlen($datos['REFERENCED_TABLE_NAME']) == 0) {
                $ultimatabla = false;
                $con = new AccesoDatos();
                $con->conectar();
                $sql = 'SELECT REFERENCED_TABLE_NAME,COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE AS A
                        WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                        AND POSITION_IN_UNIQUE_CONSTRAINT IS NULL AND ORDINAL_POSITION = :tablasrelacionadas
                        AND COLUMN_NAME IN (
                            SELECT COLUMN_NAME
                            FROM information_schema.KEY_COLUMN_USAGE AS A
                            WHERE TABLE_SCHEMA = :server
                                AND TABLE_NAME = :tabla
                                AND POSITION_IN_UNIQUE_CONSTRAINT IS  NULL
                                AND CONSTRAINT_NAME = "PRIMARY"
                                AND ORDINAL_POSITION = :tablasrelacionadas);';
                $parametros = [':server' => $server, ':tabla' => $tablaabuscar, ':tablasrelacionadas' => $tablasrelacionadas];
                $con->setSql($sql);
                $con->consultar($parametros);
                $dat = $con->extraer();

                if (isset($dat['COLUMN_NAME']) && !empty($dat['COLUMN_NAME'])) {
                    $sql = 'SELECT TABLE_NAME
                            FROM information_schema.COLUMNS
                            WHERE COLUMN_NAME = :columna AND TABLE_SCHEMA = :server AND ORDINAL_POSITION = 1;';
                    $parametros = [':columna' => $dat['COLUMN_NAME'], ':server' => $server];

                    $campos[$tablasrelacionadas] = $dat['COLUMN_NAME'];
                    $combosval[$tablasrelacionadas] = 0;

                    $con->setSql($sql);
                    $con->consultar($parametros);
                    $dat = $con->extraer();
                    $con->desconectar();
                    $tablas[$tablasrelacionadas] = $dat['TABLE_NAME'];
                    $combosval[$tablasrelacionadas] = 0;
                }
                unset($con);
            } else {
                $ultimatabla = true;
                $tablas[$tablasrelacionadas] = $datos['REFERENCED_TABLE_NAME'];
                $campos[$tablasrelacionadas] = $datos['COLUMN_NAME'];
                $combosval[$tablasrelacionadas] = 1;
            }
        }
        $this->desconectar();

        $scriptcombo = '';
        $d = 1;

        if (trim($this->prm->tabla) == 'cat_Fuente' . APP_EJERCICIO) {
            $d = 2;
        }

        $total = count($tablas) - $d;

        if ($total > 1) {
            for ($i = 1; $i <= $total; $i++) {
                if ($tablas[$i] != '') {
                    $tipopag = new stdClass();
                    $tipopag->id = $campos[$i];
                    $tipopag->name = $campos[$i];
                    $tipopag->required = true;
                    $tipopag->enDiv = true;
                    $tipopag->camposVal = $campos[$i];

                    $tipopag->agrupaPor = substr($campos[$i], 0, strpos($campos[$i], '_')) . '_nombre';
                    $tipopag->mensaje = 'Selecciona';
                    $tipopag->label = $tablas[$i];
                    $attributos = '';
                    $complementario = '';

                    for ($n = $i; $n >= 1; $n--) {
                        if ($n == $i) {
                            $complementario .= '"w_' . $campos[$n] . '":$(this).find("option:selected").val(),';
                        } else {
                            $complementario .= '"w_' . $campos[$n] . '":$(this).find("option:selected").attr("' . $campos[$n] . '"),';
                        }

                        if ($attributos == '') {
                            $attributos .= $campos[$n];
                        } else {
                            $attributos .= ',' . $campos[$n];
                        }
                    }

                    $tipopag->comoAtr = $attributos;

                    if ($i == 1) {
                        $tipopag->sql = 'SELECT ' . $campos[$i] . ',' . substr($campos[$i], 0, strpos($campos[$i], '_')) . '_nombre' . ' FROM ' . $server . '.' . $tablas[$i];
                        $html .= $this->ponCombo($tipopag);
                    } else {
                        $html .= $tipopag = $this->templateCombo($tipopag);
                    }

                    if ($i != ($total)) {
                        $scriptcombo .= '
                            $("#' . $campos[$i] . '").change(function() {
                                if ($(this).val() != "") {
                                    comunica("' . $this->pantalla . '", {
                                        "metodo": "recargaCombotest",
                                        id_combo: "' . $campos[$i + 1] . '",
                                        ' . (isset($combosval[$i + 1]) && $combosval[$i + 1] == 0 ? '"nocombo":"no",' : '') . '
                                        "tabla": "' . $tablas[$i + 1] . '",
                                        ' . $complementario . '
                                        attributos: "' . $attributos . '"
                                    });
                                }
                            });';
                    }
                } else {
                    array_push($campossintabla, $campos[$i]);
                }
            }
        }

        $form = new stdClass();
        $form->id = 'editacatalogo';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcCatalogos';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'A';
        $formulario = new form($form);

        $datos = $this->get_TableColumns($this->server . '.' . $this->prm->tabla);
        $columnas = $datos['columnas'];

        $modificaciones = [
            'cat_Indicador' => [
                ['nombre' => 'ind_id', 'accion' => 'extra', 'valor' => 'auto_increment'],
                ['nombre' => 'nim_id', 'accion' => 'llave', 'valor' => 'MUL'],
            ],
            'cat_ProductoFinal' => [
                ['nombre' => 'pdf_id', 'accion' => 'extra', 'valor' => 'auto_increment'],
            ],
            'cat_ProductoIntermedio' => [
                ['nombre' => 'pdi_id', 'accion' => 'extra', 'valor' => 'auto_increment'],
            ],
            'cat_Tema' => [
                ['nombre' => 'eje_id', 'accion' => 'llave', 'valor' => 'MUL'],
            ],
            'cat_Grupo' => [
                ['nombre' => 'ram_id', 'accion' => 'llave', 'valor' => 'MUL'],
            ],
            'cat_ObjetoGasto' => [
                ['nombre' => 'tg_id', 'accion' => 'llave', 'valor' => 'MUL'],
            ],
            'cat_UnidadResponsable' => [
                ['nombre' => 'gpo_id', 'accion' => 'llave', 'valor' => 'MUL'],
            ],
        ];

        if (array_key_exists($this->prm->tabla, $modificaciones)) {
            // Iterar sobre las columnas de la tabla actual
            foreach ($columnas as $columna) {
                // Iterar sobre las modificaciones para la tabla actual
                foreach ($modificaciones[$this->prm->tabla] as $modificacion) {
                    // Aplicar la modificación si el nombre de la columna coincide
                    if ($columna->nombre == $modificacion['nombre']) {
                        $columna->{$modificacion['accion']} = $modificacion['valor'];
                    }
                }
            }
        }

        $tipo = '';
        $tipouser = '';
        $requerido = '';
        $deshabilitado = '';
        $ton = '';
        $largo = '';
        $webresponse = new WebResponse();

        foreach ($columnas as $val) {
            $n = $val->nombre;

            if ($val->extra == 'auto_increment') {
                $val->comentario .= '[SE GENERA AUTOMATICAMENTE]';
                $deshabilitado = 'disabled="disabled"';
            } else {
                $deshabilitado = '';
            }

            if ($val->nulo == false && $val->extra != 'auto_increment') {
                $requerido = 'required="required"';
            } else {
                $requerido = '';
            }

            switch ($val->tipo) {
                case 'int':
                case 'int unsigned':
                    $tipo = 'class="form-control entero"';
                    $tipouser = 'NUMERICO';
                    $this->mixScript('
                        $("#' . $n . '").keypress(function(event) {
                            let anularTecla = false;
                            let esPermitido = Array.prototype.indexOf([0,8], event.which) >= 0;
                            anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido);

                            if (anularTecla) {
                                event.preventDefault();
                            }
                        });
                    ');
                    $ton = '';
                    break;
                case 'varchar':
                case 'char':
                    $tipo = 'class="form-control texto"';
                    $tipouser = 'LETRAS Y NUMEROS';
                    $ton = '';
                    break;
                case 'text':
                    $tipo = 'class="form-control texto"';
                    $tipouser = 'TEXTO DE GRAN LONGITUD';
                    $ton = '';
                    break;
                case 'blob':
                    $tipouser = 'COORDENADAS';
                    $ton = '';
                    break;
                case 'datetime':
                case 'date':
                    $tipo = 'class="input-group date" data-date-format="dd/mm/yyyy"';
                    $tipouser = 'FECHA';
                    $ton = '
                        <span class="input-group-btn ">
                            <button class="btn btn-default" tabindex=-1 type="button">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </button>
                        </span></div>';
                    $this->mixScript('
                    $("#' . $n . '").datepicker({
                        "keyboardNavigation": true,
                        "language": "es"
                    });');
                    break;
                case 'decimal':
                    $tipo = 'class="form-control entero"';
                    $tipouser = 'NUMERICO CON PUNTO DECIMAL (2 DECIMALES)';
                    $this->mixScript(
                        '
                        $("#' . $n . '").keypress(function(event) {
                            let anularTecla = false;
                            let esPermitido = Array.indexOf([0,8], event.which) >= 0;
                            anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido);

                            if (anularTecla) {
                                event.preventDefault();
                            }
                        });'
                    );
                    $ton = '';
                    break;
                default:
                    $tipouser = 'UNKNOWN';
                    break;
            }

            if ($n != $idtabla && $val->llave == 'PRI' && $total > 1 && !in_array($n, $campossintabla)) {
                $input = false;
            } else {
                $html .= '<div class="form-group ">';
                switch ($n) {
                    case 'sts_id':
                        $html .= '<label for="' . $n . '">ESTADO ';
                        break;
                    case 'tcp_id':
                        $html .= '<label for="' . $n . '">TIPO DE COMPRA';
                        break;
                    case 'ter_id':
                        $html .= '<label for="' . $n . '">TIPO DE RECURSO';
                        break;
                    default:
                        $html .= '<label for="' . $n . '">' . ($val->llave == 'PRI' ? strtoupper($n) : strtoupper($n));
                        break;
                }
                $html .= '<br><span style="color:RGBA(200,0,0,.5); margin-right:15px;">' . $val->comentario . '</span><span style="color:#999; font-size:10px;">EL TIPO DE DATO ES ' . $tipouser . ' ' . (is_numeric($val->largo) ? 'SU LONGITUD MAXIMA ES DE ' . $val->largo : '');
                $input = true;
            }
            if (strpos($n, 'color') || strpos($n, 'linea')) {
                $input = false;
                $html .= 'ESTE INDICA EL COLOR DEL ESTADO O SEMAFORIZACIÓN YA SEA PARA EL RELLENO (COLOR) O EL BORDE (LINEA) CUANDO SE PINTA EN UNA TABLA, NEGRO SIGNIFICA QUE ESTA VACIO<br><input type="color" name="' . $n . '" value="" class="jmacolorpicker"/></span>';
            }

            if ($this->prm->tabla != 'cat_Estado' && $n == 'sts_id') {
                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = 'sts_id';
                $tipopag->name = 'sts_id';
                $tipopag->required = true;
                $tipopag->enDiv = true;
                $tipopag->camposVal = 'sts_id';
                $tipopag->agrupaPor = 'sts_id';
                $tipopag->campoLabelAgrupacion = 'sts_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Estado';
                $tipopag->comoAtr = 'sts_id';
                $tipopag->sql = 'SELECT sts_id, sts_nombre FROM cat_Estado;';
                $html .= '</span></label>';
                $html .= $webresponse->ponCombo($tipopag);
                $html .= '</div>';
            }
            /* CAMPO RELACIONADO CON EL TIPO DE COMPRA  */
            if ($this->prm->tabla != 'cat_TipoCompra' && $n == 'tcp_id') {
                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = 'tcp_id';
                $tipopag->name = 'tcp_id';
                $tipopag->required = true;
                $tipopag->enDiv = true;
                $tipopag->camposVal = 'tcp_id';
                $tipopag->agrupaPor = 'tcp_id';
                $tipopag->campoLabelAgrupacion = 'tcp_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Tipo de Compra';
                $tipopag->comoAtr = 'tcp_id';
                $tipopag->sql = 'SELECT tcp_id, tcp_nombre FROM cat_TipoCompra;';
                $html .= '</span></label>';
                $html .= $webresponse->ponCombo($tipopag);
                $html .= '</div>';
                $this->mixScript($webresponse->getScript());
            }

            if ($this->prm->tabla != 'cat_TipoRecurso' && ($n == 'ter_id' || $n == 'trp_id_distribucion' || $n == 'trp_id')) {
                $mensaje = '';
                if ($n == 'trp_id' || $n == 'trp_id_planeacion') {
                    $mensaje = '<span style="color:#900;">Esto corresponde a Planeación, Usted No deberia modificar esta información, si no sabe que elegir aqui será mejor que lo deje en blanco este campo no es obligatorio</span>';
                }

                $input = false;
                $tipopag = new stdClass();
                $tipopag->id = $n;
                $tipopag->name = $n;
                $tipopag->required = true;
                $tipopag->enDiv = true;
                $tipopag->camposVal = 'ter_id';
                $tipopag->agrupaPor = 'ter_id';
                $tipopag->campoLabelAgrupacion = 'ter_nombre';
                $tipopag->mensaje = 'Selecciona';
                $tipopag->label = 'Tipo de Recurso';
                $tipopag->comoAtr = 'ter_id';
                $tipopag->sql = 'SELECT ter_id, ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso;';
                $html .= '</span>' . $mensaje . '</label>';
                $html .= $webresponse->ponCombo($tipopag);
                $html .= '</div>';
                $this->mixScript($webresponse->getScript());
            }

            $placeholder = '';
            /***LLAVE FOREANEA NO PRIMARIA  */
            if ($val->llave != 'PRI' && $n != 'sts_id' && $n != 'tcp_id' && $n != 'ter_id' && $n != 'trp_id_distribucion' && $n != 'trp_id') {
                $sql2 = 'SELECT REFERENCED_TABLE_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                        AND COLUMN_NAME = :columna AND REFERENCED_TABLE_NAME IS NOT NULL;';
                $parametros = [':server' => $server, ':tabla' => $this->prm->tabla, ':columna' => $n];

                $nuevo = new AccesoDatos();
                $nuevo->conectar();
                $nuevo->setSql($sql2);
                $nuevo->consultar($parametros);
                $foranea = $nuevo->extraer();
                $nuevo->desconectar();

                if (isset($foranea['REFERENCED_TABLE_NAME']) && strlen($foranea['REFERENCED_TABLE_NAME']) > 4 && substr($foranea['REFERENCED_TABLE_NAME'], 0, 4) != 'enc_' && $foranea['REFERENCED_TABLE_NAME'] != 'cg_CuentaMayor') {
                    $input = false;
                    $campo = $n;

                    switch ($campo) {
                        case 'umi_id1':
                            $campo = 'umi_id';
                            break;
                        default:
                            $campo = $n;
                            break;
                    }

                    $tipopag = new stdClass();
                    $tipopag->id = $n;
                    $tipopag->name = $n;
                    if ($val->nulo == false) {
                        $tipopag->required = true;
                    }

                    $tipopag->enDiv = true;
                    $tipopag->camposVal = $campo;
                    $tipopag->agrupaPor = $campo;
                    $tipopag->mensaje = 'Selecciona un dato';
                    $tipopag->label = 'DE LA TABLA ' . strtoupper($foranea['REFERENCED_TABLE_NAME']);
                    $tipopag->sql = 'SELECT * FROM ' . $foranea['REFERENCED_TABLE_NAME'];

                    if ($this->prm->tabla == 'cat_UnidadResponsable' && $campo == 'gpo_id') {
                        $tipopag->sql = 'SELECT gpo_id, gpo_nombre FROM ' . $foranea['REFERENCED_TABLE_NAME'];
                    }

                    $html .= '</span></label>' . $webresponse->ponCombo($tipopag) . '</div>';

                    $this->mixScript($webresponse->getScript());
                } elseif (isset($foranea['REFERENCED_TABLE_NAME']) && substr($foranea['REFERENCED_TABLE_NAME'], 0, 4) == 'enc_') {
                    $this->mixScript('
                        $("#' . $n . '").dblclick(function() {
                            if ($("#det_fs' . $foranea['REFERENCED_TABLE_NAME'] . '").length !=1) {
                                load_viewer("' . $foranea['REFERENCED_TABLE_NAME'] . '", "editar ' . $foranea['REFERENCED_TABLE_NAME'] . '", "d");
                                comunica("mga/ControladorCatalogos2.php", {
                                    metodo: "encuadre",
                                    tabla: "' . $foranea['REFERENCED_TABLE_NAME'] . '",
                                    server: "' . $server . '",
                                    id: $(this).val()
                                });
                            }
                        });
                    ');
                    $placeholder = 'SI NO CONOCE EL VALOR DE DOBLE CLICK PARA EDITARLO';
                }
            }

            if ($input) {
                $html .= '</span></label><input id = "' . $n . '" ' . $tipo . ' type="text" name="' . $n . '" maxlength="' . $largo . '" ' . $requerido . ' placeholder="' . $placeholder . '" ' . $deshabilitado . ' value = "" /></div>' . $ton;
            }
        }

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnregresarindex';
        $btnregresar->name = 'btnregresarindex';
        $btnregresar->class = 'regresar';
        $btnregresar->text = 'Regresar';
        $btnregresar->value = 'Regresar';
        $btnregresar->color = 'gris';
        $btnregresar->icon = 'circle-left me-2';

        $guardar = new stdClass();
        $guardar->id = 'btnguardar';
        $guardar->name = 'guardar';
        $guardar->class = 'regresar';
        $guardar->text = 'Guardar';
        $guardar->color = 'verde';
        $guardar->value = 'Guardar';
        $guardar->icon = 'floppy-disk me-2';
        $guardar->type = 'submit';

        $html .= '<input type="hidden" name="tabla" value="' . $this->prm->tabla . '" />';

        $html .= '</form>';
        $botones = '';
        $botones .= $this->ponBoton($btnregresar);
        $botones .= $this->ponBoton($guardar);
        $botones .= '<div class="" style="display:inline-block; width:50%; margin-left:5%;"><h4>Agregando Registro de ' . $this->prm->tabla . '</h4></div>';
        $this->agregaTools($botones);
        $formulario->mixHtml($html);

        $this->mixHtml($formulario->ponForm());
        $this->mixScript($scriptcombo);
        $this->mixScript($webresponse->getScript());
        $this->mixScript('
            $("#btnregresarindex").click(function() {
                $("#mensajeloading").html("Regresando al listado...");
                $("#loading").show();
                comunica("' . $this->pantalla . '", {
                    "metodo":"lista",
                    tabla:"' . $this->prm->tabla . '"
                });
            });

            $("#btneliminar").click(function() {
                if (confirm("Relamente desea Eliminar este Registro")) {
                    $("#editacatalogo").submit();
                }
            });

            $("#btnguardar").click(function() {
                let valido = true;
                $(".form-control").each(function() {
                    if ($(this).val() == "" && $(this).attr("required") != undefined) {
                        valido = false;
                        $(this).css("border","1px solid #FF0000");
                        $(this).append("<div style=\"display:block;\">Este es requerido<div>");
                    }
                });

                if (valido) {
                    $("#editacatalogo").submit();
                } else {
                    notify("Existen campos vacios", "error");
                }
            });

            $(".texto").blur(function() {
                $(this).val($(this).val().toUpperCase());
            })

            const catNivelMir = document.querySelector("#nim_id");
            if (catNivelMir) {
                $("#nim_id").change(function(){
                    if($(this).val()!=""){
                        if($(this).val() == 1 || $(this).val() == 2) {
                            var frmSelect = document.getElementById("frm_id");
                            if (frmSelect.value === "1" || frmSelect.value === "2") {
                                frmSelect.value = "";
                                $(".selectpicker").selectpicker("refresh");
                            }

                            var select = document.getElementById("frm_id");
                            for (var i = select.options.length - 1; i >= 0; i--) {
                                var option = select.options[i];
                                if (select.options[i].value === "1" || select.options[i].value === "2") {
                                    option.style.display = "none";
                                }

                                if (select.options[i].value === "" || select.options[i].value === "3" || select.options[i].value === "4" || select.options[i].value === "5" || select.options[i].value === "6") {
                                    option.style.display = "";
                                }

                            }
                            $(".selectpicker").selectpicker("refresh");

                            var tpiSelect = document.getElementById("tpi_id");
                            if (tpiSelect.value === "1" || tpiSelect.value === "2") {
                                tpiSelect.value = "";
                                $(".selectpicker").selectpicker("refresh");
                            }

                            var selectTipoIndicador = document.getElementById("tpi_id");
                            for (var i = selectTipoIndicador.options.length - 1; i >= 0; i--) {
                                var option = selectTipoIndicador.options[i];
                                option.style.display = "";
                            }
                            $(".selectpicker").selectpicker("refresh");
                        }
                        if($(this).val() == 3) {
                            var select = document.getElementById("frm_id");
                            for (var i = select.options.length - 1; i >= 0; i--) {
                                var option = select.options[i];
                                if (select.options[i].value === "" || select.options[i].value === "1" || select.options[i].value === "3" || select.options[i].value === "4" || select.options[i].value === "5" || select.options[i].value === "6") {
                                    option.style.display = "none";
                                }
                            }
                            var combo = document.getElementById("frm_id");
                            combo.value = "2";
                            $(".selectpicker").selectpicker("refresh");

                            var select = document.getElementById("tpi_id");
                            for (var i = select.options.length - 1; i >= 0; i--) {
                                var option = select.options[i];
                                if (select.options[i].value === "" || select.options[i].value === "2") {
                                    option.style.display = "none";
                                }
                            }
                            var comboTipoIndicador = document.getElementById("tpi_id");
                            comboTipoIndicador.value = "1";
                            $(".selectpicker").selectpicker("refresh");
                        }
                        if($(this).val() == 4) {
                            var select = document.getElementById("frm_id");
                            for (var i = select.options.length - 1; i >= 0; i--) {
                                var option = select.options[i];
                                if (select.options[i].value === "" || select.options[i].value === "2" || select.options[i].value === "3" || select.options[i].value === "4" || select.options[i].value === "5" || select.options[i].value === "6") {
                                    option.style.display = "none";
                                }
                            }
                            var combo = document.getElementById("frm_id");
                            combo.value = "1";
                            $(".selectpicker").selectpicker("refresh");

                            var select = document.getElementById("tpi_id");
                            for (var i = select.options.length - 1; i >= 0; i--) {
                                var option = select.options[i];
                                if (select.options[i].value === "" || select.options[i].value === "2") {
                                    option.style.display = "none";
                                }
                            }
                            var comboTipoIndicador = document.getElementById("tpi_id");
                            comboTipoIndicador.value = "1";
                            $(".selectpicker").selectpicker("refresh");
                        }
                    }
                }); 
            }
        ');
    }

    private function recargaCombotest()
    {
        if ($this->prm->tabla == '') {
            return false;
        }

        $combo = new stdClass();
        $where = '';
        $select = '';
        $parametros = [];

        if (isset($this->prm->nocombo) && $this->prm->nocombo == 'no') {
            foreach ($this->prm as $nombre => $valor) {
                if (substr($nombre, 0, 2) == 'w_') {
                    $select .= ',"' . $valor . '" AS "' . substr($nombre, 2, 100) . '"';
                }
            }
        } else {
            foreach ($this->prm as $nombre => $valor) {
                if (substr($nombre, 0, 2) == 'w_') {
                    if ($where == '') {
                        $where .= '  WHERE ' . substr($nombre, 2, 100) . ' = ?';
                    } else {
                        $where .= '   AND ' . substr($nombre, 2, 100) . ' = ?';
                    }
                    array_push($parametros, $valor);
                }
            }
        }

        $combo->campoLabelAgrupacion = substr($this->prm->id_combo, 0, 3) . '_nombre';
        $combo->id = $this->prm->id_combo;
        $combo->comoAtr = $this->prm->attributos;

        $m = 0;

        if (strpos($this->prm->id_combo, '_') == 2) {
            $m = 2;
        } elseif (strpos($this->prm->id_combo, '_') == 3) {
            $m = 3;
        } elseif (strpos($this->prm->id_combo, '_') == 4) {
            $m = 4;
        }

        $prex_nombre = substr($this->prm->id_combo, 0, $m);

        if (isset($this->prm->nocombo) && $this->prm->nocombo == 'no') {
            $sql = 'SELECT ' . $this->prm->id_combo . ',' . $prex_nombre . '_nombre' . $select . '
                    FROM ' . $this->server . '.' . $this->prm->tabla . ';';
        } else {
            $sql = 'SELECT ' . $this->prm->id_combo . ',' . $prex_nombre . '_nombre' . ',' . $this->prm->attributos . '
                    FROM ' . $this->server . '.' . $this->prm->tabla . ' ' . $where . ';';
        }

        $combo->sql = $sql;
        $combo->params = $parametros;
        $this->recargaCombo($combo);
    }

    private function encuadres()
    {
        $tabla = $this->prm->tabla;
        $server = $this->prm->server;
        $tblori = $tabla;
        $this->setContenedor('det_fs' . $tabla);
        $html = '';

        $sql = 'SELECT REFERENCED_TABLE_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE CONSTRAINT_SCHEMA = :server
                AND TABLE_NAME = :tabla
                AND REFERENCED_TABLE_SCHEMA IS NOT NULL
                GROUP BY REFERENCED_TABLE_NAME';
        $parametros = [':server' => $server, ':tabla' => $tabla];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $data = $this->extraer();

        $tabla = $data['REFERENCED_TABLE_NAME'] ?? '';
        $tablaprincipal = $tabla;

        $valores = [];
        if (isset($this->prm->id)) {
            $valores = $this->getEncId(['tabla' => $tblori, 'id' => $this->prm->id]);
        }

        $sql = 'SELECT COLUMN_NAME, ORDINAL_POSITION
                FROM information_schema.KEY_COLUMN_USAGE AS A
                WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                AND POSITION_IN_UNIQUE_CONSTRAINT IS NULL
                AND ORDINAL_POSITION IN (
                    SELECT MAX(ORDINAL_POSITION)ORDINAL_POSITION
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla);';
        $parametros = [':server' => $server, ':tabla' => $tablaprincipal];

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $data = $this->extraer();

        $this->desconectar();
        $tablasrelacionadas = $data['ORDINAL_POSITION'];

        $tablas = [];
        $campos = [];
        $combosval = [];

        $tablas[$tablasrelacionadas] = $tabla;
        $campos[$tablasrelacionadas] = $data['COLUMN_NAME'];
        $combosval[$tablasrelacionadas] = 0;
        $ultimatabla = true;

        $this->conectar();

        for ($i = $tablasrelacionadas; $i >= 1; $i--) {
            $tablasrelacionadas--;

            if ($ultimatabla) {
                $tablaabuscar = $tablas[$i] == '' ? $tabla : $tablas[$i];
            } else {
                $tablaabuscar = $tablas[$i] == '' ? $tabla : $tablas[$i + 1];
            }

            $sql = 'SELECT REFERENCED_TABLE_NAME, COLUMN_NAME
                    FROM information_schema.KEY_COLUMN_USAGE AS A
                    WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                    AND POSITION_IN_UNIQUE_CONSTRAINT IS NOT NULL AND ORDINAL_POSITION = :tablasrelacionadas
                    AND COLUMN_NAME IN (
                        SELECT COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE AS A
                        WHERE TABLE_SCHEMA = :server
                            AND TABLE_NAME = :tabla
                            AND POSITION_IN_UNIQUE_CONSTRAINT IS NULL
                            AND CONSTRAINT_NAME = "PRIMARY"
                            AND ORDINAL_POSITION = :tablasrelacionadas);';
            $parametros = [':server' => $server, ':tabla' => $tablaabuscar, ':tablasrelacionadas' => $tablasrelacionadas];

            $this->setSql($sql);
            $this->consultar($parametros);
            $datos = $this->extraer();

            if (strlen($datos['COLUMN_NAME']) == 0 || strlen($datos['REFERENCED_TABLE_NAME']) == 0) {
                $ultimatabla = false;
                $con = new AccesoDatos();
                $con->conectar();
                $sql = 'SELECT REFERENCED_TABLE_NAME,COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE AS A
                        WHERE TABLE_SCHEMA = :server AND TABLE_NAME = :tabla
                        AND POSITION_IN_UNIQUE_CONSTRAINT IS NULL AND ORDINAL_POSITION = :tablasrelacionadas
                        AND COLUMN_NAME IN (
                            SELECT COLUMN_NAME
                            FROM information_schema.KEY_COLUMN_USAGE AS A
                            WHERE TABLE_SCHEMA = :server
                                AND TABLE_NAME = :tabla
                                AND POSITION_IN_UNIQUE_CONSTRAINT IS  NULL
                                AND CONSTRAINT_NAME = "PRIMARY"
                                AND ORDINAL_POSITION = :tablasrelacionadas);';
                $parametros = [':server' => $server, ':tabla' => $tablaabuscar, ':tablasrelacionadas' => $tablasrelacionadas];
                $con->setSql($sql);
                $con->consultar($parametros);
                $dat = $con->extraer();

                $sql = 'SELECT TABLE_NAME
                        FROM information_schema.COLUMNS
                        WHERE COLUMN_NAME = :columna AND TABLE_SCHEMA = :server AND ORDINAL_POSITION = 1;';
                $parametros = [':columna' => $dat['COLUMN_NAME'], ':server' => $server];

                $campos[$tablasrelacionadas] = $dat['COLUMN_NAME'];
                $combosval[$tablasrelacionadas] = 0;

                $con->setSql($sql);
                $con->consultar($parametros);
                $dat = $con->extraer();
                $con->desconectar();
                $tablas[$tablasrelacionadas] = $dat['TABLE_NAME'];
                $combosval[$tablasrelacionadas] = 0;
                unset($con);
            } else {
                $ultimatabla = true;
                $tablas[$tablasrelacionadas] = $datos['REFERENCED_TABLE_NAME'];
                $campos[$tablasrelacionadas] = $datos['COLUMN_NAME'];
                $combosval[$tablasrelacionadas] = 1;
            }
        }

        $this->desconectar();
        $scriptcombo = '';

        $total = count($tablas);

        if ($total > 1) {
            for ($i = 1; $i <= $total - 1; $i++) {
                if ($tablas[$i] != '') {
                    $COMBO = new stdClass();
                    $COMBO->id = $campos[$i];
                    $COMBO->name = $campos[$i];
                    $COMBO->required = true;
                    $COMBO->enDiv = true;
                    $COMBO->camposVal = $campos[$i];
                    $COMBO->agrupaPor = substr($campos[$i], 0, strpos($campos[$i], '_')) . '_nombre';
                    $COMBO->mensaje = 'Selecciona';
                    $COMBO->label = $tablas[$i];

                    if (isset($valores[$campos[$i]])) {
                        $COMBO->selecciona = $valores[$campos[$i]];
                        $COMBO->detonar = true;
                    }

                    $attributos = '';
                    $complementario = '';

                    for ($n = $i; $n >= 1; $n--) {
                        if ($n == $i) {
                            $complementario .= '"w_' . $campos[$n] . '":$(this).find("option:selected").val(),';
                        } else {
                            $complementario .= '"w_' . $campos[$n] . '":$(this).find("option:selected").attr("' . $campos[$n] . '"),';
                        }

                        if ($attributos == '') {
                            $attributos .= $campos[$n];
                        } else {
                            $attributos .= ',' . $campos[$n];
                        }
                    }

                    $COMBO->comoAtr = $attributos;

                    if ($i == 1) {
                        $COMBO->sql = 'SELECT ' . $campos[$i] . ',' . substr($campos[$i], 0, strpos($campos[$i], '_')) . '_nombre' . ' FROM ' . $tablas[$i] . ';';
                        $html .= $this->ponCombo($COMBO);
                    } else {
                        if (count($valores) > 0) {
                            $sel = $campos[$i] . ',' . substr($campos[$i], 0, strpos($campos[$i], '_')) . '_nombre ';
                            $wh = '';
                            $tblaux = $this->get_TableColumns($this->server . '.' . $tablas[$i]);
                            $parametros = [];

                            foreach ($tblaux['primaria'] as $val) {
                                if ($val != $campos[$i]) {
                                    $sel .= ',' . $val;
                                    $wh .= ' AND ' . $val . ' = ?';
                                    array_push($parametros, $valores[$val]);
                                }
                            }

                            $tsql = 'SELECT ' . $sel . ' FROM ' . $tablas[$i];
                            $tsql .= ' WHERE ' . $campos[$i] . ' IS NOT NULL ' . $wh;
                            $COMBO->sql = $tsql;
                            $COMBO->params = $parametros;
                            $html .= $this->ponCombo($COMBO);
                        } else {
                            $html .= $COMBO = $this->templateCombo($COMBO);
                        }
                    }

                    if ($i != ($total - 1)) {
                        $scriptcombo .= '
                            $("#' . $campos[$i] . '").change(function() {
                                if ($(this).val() != "") {
                                    comunica("' . $this->pantalla . '", {
                                        "metodo": "recargaCombotest",
                                        id_combo: "' . $campos[$i + 1] . '",
                                        "tabla": "' . $tablas[$i + 1] . '",
                                        ' . $complementario . '
                                        attributos: "' . $attributos . '"
                                    });
                                }
                            });';
                    } else {
                        $scriptcombo .= '
                            $("#' . $campos[$i] . '").change(function() {
                                if ($(this).val() != "") {
                                    comunica("' . $this->pantalla . '", {
                                        "metodo": "getEncId",
                                        id_combo: "' . $campos[$i + 1] . '",
                                        "tabla": "' . $tablas[$i + 1] . '",
                                        ' . $complementario . '
                                        attributos: "' . $attributos . '",
                                        tabla: "' . $tblori . '"
                                    });
                                }
                            });';
                    }
                } else {
                    array_push($campossintabla, $campos[$i]);
                }
            }
        }
        $this->mixScript($scriptcombo);
        $this->mixHtml($html);
    }

    private function get_TableColumns($tabla)
    {
        $columns = [];
        $primary = [];

        $sql = 'SHOW FULL COLUMNS FROM ' . $tabla;
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $this->desconectar();

        $usr = false;
        $fecha = false;

        while ($data = $this->extraer()) {
            $columna = new stdClass();
            $columna->nombre = $data['Field'];
            $type = strpos($data['Type'], '(') ? substr($data['Type'], 0, strpos($data['Type'], '(')) : $data['Type'];

            $columna->tipo = $type;
            $columna->largo = substr(
                $data['Type'],
                strpos($data['Type'], '(') + 1,
                strpos($data['Type'], ')') - strpos($data['Type'], '(') - 1
            );
            $columna->nulo = $data['Null'] == 'NO' ? false : true;
            $columna->predeterminado = $data['Default'];
            $columna->comentario = $data['Comment'];
            $columna->llave = $data['Key'];
            $columna->extra = $data['Extra'];

            if ($data['Key'] == 'PRI') {
                array_push($primary, $data['Field']);
            }

            switch ($data['Field']) {
                case 'usr_id':
                    $usr = true;
                    break;
                case 'usr_fecha':
                    $fecha = true;
                    break;
                default:
                    array_push($columns, $columna);
                    break;
            }
        }

        return ['columnas' => $columns, 'primaria' => $primary, 'usr' => $usr, 'fecha' => $fecha];
    }

    private function getEncId($prm = [])
    {
        $tbla = '';
        $sql = '';
        $parametros = [];

        if (isset($prm['tabla'])) {
            $tbla = $prm['tabla'];
        } else {
            $tbla = $this->prm->tabla;
        }

        $tabla = $this->get_TableColumns($this->server . '.' . $tbla);
        $pri = $tabla['primaria'][0];

        if (!isset($prm['id'])) {
            $sql = 'SELECT ' . $pri . ' FROM ' . $tbla . ' WHERE ' . $pri . ' IS NOT NULL';

            foreach ($tabla['columnas'] as $val) {
                $col = 'w_' . $val->nombre;

                if (isset($this->prm->$col)) {
                    $sql .= ' AND ' . $val->nombre . ' = ?';
                    array_push($parametros, $this->prm->$col);
                }
            }
        } else {
            $sql = 'SELECT * FROM ' . $tbla . ' WHERE ' . $pri . ' = ?';
            array_push($parametros, $prm['id']);
        }

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($parametros);
        $res = $this->extraer();
        $this->desconectar();

        if (count($prm) > 0) {
            return $res;
        } else {
            $this->mixScript(' $("#' . $pri . '").val("' . $res[$pri] . '");');
        }
    }
}

if (isset($_SESSION[_S]['prm']['M']['6']['3'])) {
    new ControladorTest();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
