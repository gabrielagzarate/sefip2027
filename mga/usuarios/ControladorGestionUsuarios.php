<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/Usuario.php';

class ControladorGestionUsuarios extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'mga/usuarios/ControladorGestionUsuarios.php';
        switch ($this->metodo) {
            case 'llenaTablaUsuarios':
                $this->llenaTablaUsuarios();
                break;
            case 'actualizaUsuario':
                $this->actualizaUsuario();
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

        $comboEjercicio = new stdClass();
        $comboEjercicio->label = 'Seleccione el ejercicio';
        $comboEjercicio->id = 'ejercicio';
        $comboEjercicio->sql = '
            SELECT 2027
            UNION ALL
            SELECT 2026
            UNION ALL
            SELECT 2025
            UNION ALL
            SELECT 2024
            UNION ALL 
            SELECT 2023
            UNION ALL 
            SELECT 2022
        ';
        $comboEjercicio->change = '
            if( $(this).val() != "" ){
                $("#tbl-usuarios").tabla("llenaTabla");
            }
        ';

        $comboUr = new stdClass();
        $comboUr->label = 'Seleccione una UR';
        $comboUr->id = 'ur_id';
        $comboUr->sql = '
            SELECT CONCAT(gpo_id, ur_id) ur, ur_nombre, gpo_id, ur_id
            FROM cat_UnidadResponsable
            WHERE eur_id = 5
        ';
        $comboUr->comoAtr = 'gpo_id,ur_id';
        $comboUr->search = true;

        $nombreUsuario = new stdClass();
        $nombreUsuario->id = 'usr_nombre';
        $nombreUsuario->label = 'Nombre del usuario';
        $nombreUsuario->placeholder = 'Escribe el nombre del usuario';

        $inputCorreo = new stdClass();
        $inputCorreo->id = 'usr_correo';
        $inputCorreo->label = 'Correo';
        $inputCorreo->placeholder = 'correo@oaxaca.gob.mx';

        $inputTelefono = new stdClass();
        $inputTelefono->id = 'usr_telefono';
        $inputTelefono->label = 'Teléfono';
        $inputTelefono->maxlength = 10;
        $inputTelefono->placeholder = '9511230000';

        $comboEstado = new stdClass();
        $comboEstado->label = 'Seleccione un estado';
        $comboEstado->id = 'sts_id';
        $comboEstado->sql = '
            SELECT sts_id, sts_nombre
            FROM ' . BD_USUARIO . '.cat_Estado
        ';

        $btnBuscarUsuario = new stdClass();
        $btnBuscarUsuario->id = 'btnBuscar';
        $btnBuscarUsuario->color = 'gris';
        $btnBuscarUsuario->value = 'Buscar usuario';
        $btnBuscarUsuario->icon = 'search me-2';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->color = 'azul';
        $btnLimpiar->value = 'Limpiar búsqueda';
        $btnLimpiar->icon = 'brush me-2';

        $html .= <<<HTML
            <div class="row center mb-4">
                <div class="col text-center">
                    <h4>Gestión de usuarios</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboEjercicio)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboUr)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponInput($nombreUsuario)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponInput($inputCorreo)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponInput($inputTelefono)}
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    {$this->ponCombo($comboEstado)}
                </div>
                
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="mt-3">
                        {$this->ponBoton($btnBuscarUsuario)}
                        {$this->ponBoton($btnLimpiar)}
                    </div>
                </div>
            </div>
        HTML;
        $tabla = $this->buildTablaUsuarios();
        $this->mixHtml($html . $this->wrPonTabla($tabla));

        $js = <<<JS
            $('#btnBuscar').on('click', function(){
                if ($('#ejercicio').val() == '') {
                    notify('Debes seleccionar un ejercicio', 'warning');
                    return;
                }
                $("#tbl-usuarios").tabla("llenaTabla");
            });

            $('#usr_telefono').on('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('#btnLimpiar').on('click', function(){
                $('#ejercicio').selectpicker('val', '');
                $('#ur_id').selectpicker('val', '');
                $('#usr_nombre').val("");
                $('#usr_correo').val("");
                $('#usr_telefono').val("");
                $('#sts_id').selectpicker('val', '');
                comunica('{$this->pantalla}')
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
        $tabla->comunica->ejercicio = '{$("#ejercicio").val()}';
        $tabla->comunica->gpo_id = '{$("#ur_id option:selected").attr("gpo_id")}';
        $tabla->comunica->ur_id = '{$("#ur_id option:selected").attr("ur_id")}';
        $tabla->comunica->usr_nombre = '{$("#usr_nombre").val()}';
        $tabla->comunica->usr_correo = '{$("#usr_correo").val()}';
        $tabla->comunica->usr_telefono = '{$("#usr_telefono").val()}';
        $tabla->comunica->sts_id = '{$("#sts_id").val()}';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Ver/Editar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Usuario';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Correo';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Teléfono';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Estatus';
        array_push($tr, $th);

        $trbody = [];
        $td = new stdClass();
        $td->tipo = 'boton';
        if (tienePermiso('M.9.2.2')) {
            $td->icono = 'pencil';
        } else {
            $td->icono = 'eye';
        }
        $td->attr = new stdClass();
        $td->attr->usr_id = 'usr_id';
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->usuario = 'usr_usuario';
        $td->attr->nombre = 'nombre';
        $td->color = 'gris';
        $td->class = 'btnEditar';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_usuario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_correo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usr_telefono';
        array_push($trbody, $td);

        /*estado */
        $td = new stdClass();
        $td->campoTxt = 'estado';
        $td->tipo = 'bolita';
        $td->color = 'color';
        $td->lbl = 'estado';
        $td->linea = 'color';
        $td->align = 'center';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        return $tabla;
    }

    private function llenaTablaUsuarios()
    {
        $ejercicio = 'ctrl' . $this->prm->ejercicio ?? APP_EJERCICIO;
        $where = [];
        $params = [];

        /* filtros */
        if (!empty($this->prm->usr_nombre)) {
            $where[] = 'CONCAT_WS(" ", usr_nombre, usr_paterno, usr_materno) LIKE ?';
            $params[] = '%' . $this->prm->usr_nombre . '%';
        }

        if (!empty($this->prm->gpo_id)) {
            $where[] = 'gpo_id = ?';
            $params[] = $this->prm->gpo_id;
        }

        if (!empty($this->prm->ur_id)) {
            $where[] = 'ur_id = ?';
            $params[] = $this->prm->ur_id;
        }

        if (!empty($this->prm->usr_correo)) {
            $where[] = 'usr_correo LIKE ?';
            $params[] = '%' . trim($this->prm->usr_correo) . '%';
        }

        if (!empty($this->prm->usr_telefono)) {
            $where[] = 'usr_telefono LIKE ?';
            $params[] = '%' . trim($this->prm->usr_telefono) . '%';
        }

        if (!empty($this->prm->sts_id)) {
            $where[] = 'u.sts_id = ?';
            $params[] = $this->prm->sts_id;
        }

        $sqlWhere = '';
        if (!empty($where)) {
            $sqlWhere = ' WHERE ' . implode(' AND ', $where);
        }

        $sql = '
            SELECT CONCAT(ur.gpo_id, ur.ur_id, " - ", ur_nombre) ur,
            u.usr_usuario, CONCAT(usr_nombre, " ", usr_paterno, " ",
            usr_materno) nombre, usr_correo, usr_telefono,
            sts_nombre estado, cu.gpo_id, cu.ur_id, u.usr_id,
            CASE u.sts_id
                WHEN 1 THEN "049e3c"
                WHEN 2 THEN "d21010"
                WHEN 3 THEN "e0c70c"
                ELSE "6B7280"
            END AS color
            FROM ' . BD_USUARIO . '.cat_Usuario u
            INNER JOIN ' . $ejercicio . '.cat_Usuario cu USING(usr_id)
            INNER JOIN ' . BD_USUARIO . '.cat_Estado es USING (sts_id)
            INNER JOIN cat_UnidadResponsable ur USING (gpo_id, ur_id)
            ' . $sqlWhere . '
        ';

        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-usuarios';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $js = <<<JS
            $('.btnEditar').on('click', function(){
                comunica('{$this->pantalla}', {
                    metodo: 'actualizaUsuario',
                    usr_id: $(this).attr('usr_id'),
                    gpo_id: $(this).attr('gpo_id'),
                    ur_id: $(this).attr('ur_id'),
                    usuario: $(this).attr('usr_usuario'),
                    nombre: $(this).attr('nombre')
                });
            });
        JS;
        //$this->mixScript($js);
    }

    private function actualizaUsuario()
    {
        $datosUsr = new Usuario($this->prm->usr_id);

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->icon = 'circle-left me-2';
        $btnRegresar = $this->ponBoton($btnRegresar);

        $this->agregaTools($btnRegresar);

        $html = <<<HTML
            <div class="row center mb-4">
                <div class="col text-center">
                    <h4>Editar Usuario</h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    
                </div>

                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    
                </div>
                
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="mt-3">
                        
                    </div>
                </div>
            </div>
        HTML;

        $this->mixHtml($html);
    }
}

if (tienePermiso('M.9.2.1')) {
    new ControladorGestionUsuarios();
} else {
    WebResponse::sinPermisos();
}
