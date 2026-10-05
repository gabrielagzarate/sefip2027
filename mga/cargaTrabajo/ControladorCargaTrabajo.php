<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

const SUBSECRETARIA = '03'; //SUBSECRETARÍA DE EGRESOS, CONTABILIDAD Y TESORERÍA
const DIRECCION = '09'; //DIRECCIÓN DE CONTABILIDAD GUBERNAMENTAL
const SISTEMA = 'C'; // Contabilidad

class ControladorCargaTrabajo extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'mga/cargaTrabajo/ControladorCargaTrabajo.php';
        switch ($this->metodo) {
            case 'llenaTablaUsuarios':
                $this->llenaTablaUsuarios();
                break;
            case 'addCargaTrabajo':
                $this->addCargaTrabajo();
                break;
            case 'insertaCarga':
                $this->insertaCarga();
                break;
            case 'llenaTablaCarga':
                $this->llenaTablaCarga();
                break;
            case 'eliminarCarga':
                $this->eliminarCarga();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    public function main()
    {
        $html = '';
        $this->agregaTools('');

        $comboDepto = new stdClass();
        $comboDepto->label = 'Dirección';
        $comboDepto->id = 'comboDepto';
        $comboDepto->sql = '
            SELECT dir_id, dir_nombre, ssc_id
            FROM cat_Direccion
            WHERE ssc_id = ?
            AND dir_id = ?
        ';
        $comboDepto->params = [SUBSECRETARIA, DIRECCION];
        $comboDepto->comoAtr = 'ssc_id,dir_id';

        $comboEjercicio = new stdClass();
        $comboEjercicio->label = 'Seleccione un ejercicio';
        $comboEjercicio->id = 'ejercicio';
        $comboEjercicio->sql = '
            SELECT 2027
            UNION
            SELECT 2026
            UNION
            SELECT 2025
            UNION
            SELECT 2024
            UNION
            SELECT 2023
        ';
        $comboEjercicio->change = '
            if( $(this).val() != "" ){
                setTimeout(function(){
                    $("#tbl-usuarios").tabla("llenaTabla");
                },100);
            }
        ';
        $comboEjercicio->selecciona = $this->prm->ejercicio ?? '';

        $nombreUsuario = new stdClass();
        $nombreUsuario->id = 'usr_usuario';
        $nombreUsuario->label = 'Nombre del usuario';
        $nombreUsuario->placeholder = 'Escribe el nombre del usuario';

        $btnBuscarUsuario = new stdClass();
        $btnBuscarUsuario->id = 'btnBuscar';
        $btnBuscarUsuario->color = 'gris';
        $btnBuscarUsuario->value = 'Buscar usuario';
        $btnBuscarUsuario->icon = 'search me-2';

        $html .= <<<HTML
            <div class="row center mb-4">
                <div class="col text-center">
                    <h4>Carga de Trabajo</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboDepto)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboEjercicio)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponInput($nombreUsuario)}
                </div>
                
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="mt-3">
                        {$this->ponBoton($btnBuscarUsuario)}
                    </div>
                </div>
            </div>
        HTML;
        $tabla = $this->buildTablaUsuarios();
        $this->mixHtml($html . $this->wrPonTabla($tabla));
        //$this->llenaTablaUsuarios();

        $js = <<<JS
            $('#btnBuscar').on('click', function(){
                if ($('#ejercicio').val() == '') {
                    notify('Debes seleccionar un ejercicio', 'warning');
                    return;
                }
                $("#tbl-usuarios").tabla("llenaTabla");
            });
        JS;
        $this->mixScript($js);
    }

    private function buildTablaUsuarios()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-usuarios';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaUsuarios';
        $tabla->comunica->ssc_id = '{$("#comboDepto option:selected").attr("ssc_id")}';
        $tabla->comunica->dir_id = '{$("#comboDepto option:selected").attr("dir_id")}';
        $tabla->comunica->usr_usuario = '{$("#usr_usuario").val()}';
        $tabla->comunica->ejercicio = '{$("#ejercicio").val()}';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Agregar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Usuario';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Puesto';
        array_push($tr, $th);

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'pencil';
        $td->attr = new stdClass();
        $td->attr->ssc_id = 'ssc_id';
        $td->attr->dir_id = 'dir_id';
        $td->attr->usr_id = 'usr_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->usuario = 'usr_usuario';
        $td->attr->nombre = 'nombre';
        $td->color = 'gris';
        $td->class = 'btnAgregar';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_puesto';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

    private function llenaTablaUsuarios()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $usuario = '';
        $js_usr = '';
        $ssc_id = $this->prm->ssc_id ?? SUBSECRETARIA;
        $dir_id = $this->prm->dir_id ?? DIRECCION;

        $params = [$ssc_id, $dir_id];

        if (isset($this->prm->usr_usuario) && !empty($this->prm->usr_usuario)) {
            $js_usr = ',usr_usuario: "' . $this->prm->usr_usuario . '"';
            $usuario = 'AND u.usr_usuario LIKE ?';
            array_push($params, '%' . $this->prm->usr_usuario . '%');
        }

        $sql = '
            SELECT CONCAT(gpo_id, ur_id) ur, u.usr_usuario,
            CONCAT(usr_nombre, " ", usr_paterno, " ", usr_materno) nombre,
            cu.usr_puesto, ejs.ssc_id, ejs.dir_id, u.usr_id, u.gpo_id, u.ur_id
            FROM ' . $ejercicio . '.cat_Usuario u
            INNER JOIN ' . BD_USUARIO . '.cat_Usuario cu USING(usr_id)
            INNER JOIN ' . $ejercicio . '.enc_JerarquiaSefin ejs USING (ejs_id)
            INNER JOIN ' . $ejercicio . '.cat_Subsecretaria USING (ssc_id)
            INNER JOIN ' . $ejercicio . '.cat_Direccion USING (dir_id)
            WHERE cu.sts_id = 1
            AND ejs.ssc_id = ?
            AND ejs.dir_id = ?
            ' . $usuario . '
        ';
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-usuarios';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $js = <<<JS
            $('.btnAgregar').on('click', function(){
                if ($('#ejercicio').val() == '') {
                    notify('Debes seleccionar un ejercicio', 'warning');
                    return;
                }
                comunica('{$this->pantalla}', {
                    metodo: 'addCargaTrabajo',
                    usr_id: $(this).attr('usr_id'),
                    ssc_id: $(this).attr('ssc_id'),
                    dir_id: $(this).attr('dir_id'),
                    usuario: $(this).attr('usuario'),
                    nombre: $(this).attr('nombre'),
                    ejercicio: "{$this->prm->ejercicio}"
                    {$js_usr}

                });
            });
        JS;
        $this->mixScript($js);
    }

    private function addCargaTrabajo()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->icon = 'circle-left me-2';
        $btnRegresar = $this->ponBoton($btnRegresar);

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'btnEliminarTodo';
        $btnEliminar->color = 'rojo';
        $btnEliminar->value = 'Eliminar todo';
        $btnEliminar->icon = 'trash me-2';
        $btnEliminar = $this->ponBoton($btnEliminar);

        $this->agregaTools($btnRegresar . $btnEliminar);

        $comboSistema = new stdClass();
        $comboSistema->label = 'Sistema';
        $comboSistema->id = 'sis_id';
        $comboSistema->sql = '
            SELECT sis_id, sis_nombre
            FROM ' . $ejercicio . '.cat_Sistema
            WHERE sis_id = ?
        ';
        $comboSistema->params = [SISTEMA];

        $comboUr = new stdClass();
        $comboUr->label = 'Unidad Responsable';
        $comboUr->id = 'ur_id';
        $comboUr->sql = $this->dameUr($ejercicio);
        $comboUr->comoAtr = 'gpo_id,ur_id';
        $comboUr->search = 'true';
        $comboUr->params = [$this->prm->usr_id];

        $btnAgregar = new stdClass();
        $btnAgregar->id = 'btnAgregar';
        $btnAgregar->color = 'verde';
        $btnAgregar->value = 'Agregar UR';
        $btnAgregar->icon = 'plus me-2';
        $btnAgregar->class = 'me-1';

        $btnAgregarTodo = new stdClass();
        $btnAgregarTodo->id = 'btnAgregarTodo';
        $btnAgregarTodo->color = 'azul';
        $btnAgregarTodo->value = 'Agregar todas las URs';
        $btnAgregarTodo->icon = 'plus me-2';
        $btnAgregarTodo->class = 'me-1';

        $html = <<<HTML
            <div class="row center mb-4">
                <div class="col text-center">
                    <h4>Carga de trabajo para el usuario {$this->prm->nombre}</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboSistema)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboUr)}
                </div>
                
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="mt-3">
                        {$this->ponBoton($btnAgregar)}
                        {$this->ponBoton($btnAgregarTodo)}
                    </div>
                </div>
            </div>
        HTML;
        $tabla = $this->buildTablaCarga();
        $this->mixHtml($html . $this->wrPonTabla($tabla));
        $this->llenaTablaCarga();

        $js = <<<JS
            $('#btnRegresar').on('click', function(){
                comunica('{$this->pantalla}', {
                    metodo:'main',
                    ejercicio: "{$this->prm->ejercicio}"
                })
            });

            $('#btnAgregar').on('click', function(){
                if ($("#ur_id option:selected").val() == '' || $("#sis_id option:selected").val() == '') {
                    notify("Hacen falta datos por seleccionar", "warning");
                } else {
                    comunica('{$this->pantalla}', {
                        metodo:'insertaCarga',
                        gpo_id: $("#ur_id option:selected").attr("gpo_id"),
                        ur_id: $("#ur_id option:selected").attr("ur_id"),
                        usr_id: '{$this->prm->usr_id}',
                        sis_id: $("#sis_id option:selected").val(),
                        ejercicio: "{$this->prm->ejercicio}"
                    });
                }
            });

            $('#btnAgregarTodo').on('click', function() {
                alertify.confirm("<b>AGREGAR CARGA DE TRABAJO - EJERCICIO {$this->prm->ejercicio}</b>", "Confirme que desee agregar todas las URs a la carga de trabajo del usuario <b>{$this->prm->usuario}</b>", function(){
                    comunica('{$this->pantalla}', {
                        metodo:'insertaCarga',
                        usr_id: '{$this->prm->usr_id}',
                        sis_id: $("#sis_id option:selected").val(),
                        todo: 'si',
                        ejercicio: "{$this->prm->ejercicio}"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });

            $('#btnEliminarTodo').on('click', function(){
                alertify.confirm("<b>ELIMINAR TODA LA CARGA DE TRABAJO - EJERCICIO {$this->prm->ejercicio}</b>", "Confirme que desee eliminar todas la carga de trabajo del usuario <b>{$this->prm->usuario}</b>", function(){
                    comunica('{$this->pantalla}', {
                        metodo:'eliminarCarga',
                        usr_id: '{$this->prm->usr_id}',
                        sis_id: $("#sis_id option:selected").val(),
                        todo: 'si',
                        ejercicio: "{$this->prm->ejercicio}"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                
            });
        JS;
        $this->mixScript($js);
    }

    private function buildTablaCarga()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl-carga';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Sistema';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Unidad Responsable';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Eliminar';
        array_push($tr, $th);

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'sis_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'trash';
        $td->attr = new stdClass();
        $td->attr->usr_id = 'usr_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->car_id = 'car_id';
        $td->color = 'rojo';
        $td->class = 'btnEliminarCarga';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

    private function llenaTablaCarga()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $sql = '
            SELECT CONCAT(ur.gpo_id, ur.ur_id, " - ", ur_nombre) ur,
            sis_nombre, ct.gpo_id, ct.ur_id, ct.sis_id, ct.usr_id, car_id
            FROM ' . $ejercicio . '.cat_CargaTrabajo ct
            INNER JOIN ' . $ejercicio . '.cat_Sistema USING (sis_id)
            INNER JOIN ' . $ejercicio . '.cat_UnidadResponsable ur USING (gpo_id, ur_id)
            WHERE ct.usr_id = ?
        ';

        $this->setSql($sql);
        $this->setParams([$this->prm->usr_id]);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-carga';
        $nav->nf = 200;
        $this->wrLlenaTabla($nav);

        $js = <<<JS
            $('.btnEliminarCarga').on('click', function(){
                comunica('{$this->pantalla}', {
                    metodo:'eliminarCarga',
                    usr_id: $(this).attr('usr_id'),
                    gpo_id: $(this).attr('gpo_id'),
                    ur_id: $(this).attr('ur_id'),
                    car_id: $(this).attr('car_id'),
                    ejercicio: "{$this->prm->ejercicio}"
                });
            });
        JS;
        $this->mixScript($js);
    }

    private function insertaCarga()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $this->conectar();
        if (isset($this->prm->todo) && $this->prm->todo == 'si') {
            $delete = '
                DELETE FROM ' . $ejercicio . '.cat_CargaTrabajo
                WHERE usr_id = ?
                AND sis_id = ?;
            ';
            $this->setSql($delete);
            $this->consultar([$this->prm->usr_id, $this->prm->sis_id]);

            $insert = '
                INSERT INTO ' . $ejercicio . '.cat_CargaTrabajo(gpo_id, ur_id, usr_id, sis_id, usu_usu)
                SELECT gpo_id, ur_id, ?, ?, ?
                FROM ' . $ejercicio . '.cat_UnidadResponsable
                WHERE eur_id = 5;
            ';
            $this->setSql($insert);
            $this->consultar([$this->prm->usr_id, $this->prm->sis_id, getSession('usr_id')]);
            $js = <<<JS
                notify("Se agregaron todas las URs a la carga de trabajo del usuario en el ejercicio {$this->prm->ejercicio}", "success");
            JS;
            $this->mixScript($js);
        } else {
            $insert = 'INSERT INTO ' . $ejercicio . '.cat_CargaTrabajo(gpo_id, ur_id, usr_id, sis_id, usu_usu)';
            $values = 'VALUES (?, ?, ?, ?, ?)';
            $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->usr_id, $this->prm->sis_id, getSession('usr_id')];

            $this->setSql($insert . $values);
            $this->consultar($params, false);

            if ($this->getErrno() > 0) {
                $this->desconectar();
                $mensaje = $this->getError();
                $mensaje = preg_replace('/^.*?: \d+ /', '', $mensaje);
                $this->mixScript('notify("' . $mensaje . '", "warning");');
                return false;
            }

            $js = <<<JS
                notify("Registro agregado correctamente en el ejercicio {$this->prm->ejercicio}", "success");
            JS;
            $this->mixScript($js);
        }
        $this->desconectar();
        $this->recargaComboUr();
        $this->llenaTablaCarga();
    }

    public function recargaComboUr()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $comboUr = new stdClass();
        $comboUr->label = 'Unidad Responsable';
        $comboUr->id = 'ur_id';
        $comboUr->sql = $this->dameUr($ejercicio);
        $comboUr->comoAtr = 'gpo_id,ur_id';
        $comboUr->search = 'true';
        $comboUr->params = [$this->prm->usr_id];
        $this->recargaCombo($comboUr);

    }

    private function dameUr($ejercicio)
    {
        $sql = <<<SQL
            SELECT ur_clave, ur_nombre, ur.gpo_id, ur.ur_id
            FROM {$ejercicio}.cat_UnidadResponsable ur
            LEFT JOIN {$ejercicio}.cat_CargaTrabajo ct
                ON ct.gpo_id = ur.gpo_id
                AND ct.ur_id = ur.ur_id
                AND ct.usr_id = ?
            WHERE ur.gpo_id != 0
            AND ct.ur_id IS NULL
        SQL;

        return $sql;
    }

    private function eliminarCarga()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $todo = $this->prm->todo ?? 'no';
        $this->conectar();
        if ($todo == 'si') {
            $delete = 'DELETE FROM ' . $ejercicio . '.cat_CargaTrabajo WHERE usr_id = ?';
            $this->setSql($delete);
            $this->consultar([$this->prm->usr_id]);
            $js = <<<JS
                notify("Se eliminó toda la carga de trabajo del usuario en el ejercicio {$this->prm->ejercicio}", "success");
            JS;
            $this->mixScript($js);
        } else {
            $delete = '
                DELETE FROM ' . $ejercicio . '.cat_CargaTrabajo
                WHERE car_id = ?
                AND usr_id = ?
                AND gpo_id = ?
                AND ur_id = ?
            ';
            $params = [$this->prm->car_id, $this->prm->usr_id, $this->prm->gpo_id, $this->prm->ur_id];

            $this->setSql($delete);
            $this->consultar($params);

            $this->mixScript('
                notify("Se eliminó de la carga de trabajo a la UR ' . $this->prm->gpo_id . $this->prm->ur_id . ' en el ejercicio ' . $this->prm->ejercicio . '", "success");
            ');
        }
        $this->desconectar();
        $this->recargaComboUr();
        $this->llenaTablaCarga();
    }
}

if (tienePermiso('M.2.2.4')) {
    new ControladorCargaTrabajo();
} else {
    WebResponse::sinPermisos();
}
