<?php

session_start();
require_once dirname(__FILE__) . '/../lib/constantes.php';
require_once dirname(__FILE__) . '/../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../ent/Usuario.php';
require_once dirname(__FILE__) . '/../ent/usuarios/UsuarioPrevio.php';
require_once dirname(__FILE__) . '/../ent/usuarios/SolicitudAcceso.php';
require_once dirname(__FILE__) . '/../ent/usuarios/PermisosUsuarioPrevio.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/Exception.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/SMTP.php';
require_once dirname(__FILE__) . '/../lib/scp/phpmailer/src/PHPMailer.php';
//instanciar la clase
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class ControladorTicketAdmon extends WebResponse
{
    public $ctrlEjercicioActual;
    public $escritura;

    public function __construct()
    {
        parent::__construct();
        $this->prm->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->prm->ur_id = $_SESSION[_S]['ur_id'];
        $this->escritura = isset($_SESSION[_S]['prm']['M']['9']['1']['2']) ? true : false;

        $this->pantalla = 'mga/ControladorTicketAdmon.php';
        $this->ctrlEjercicioActual = BD_CTRL;

        switch ($this->metodo) {
            case 'main':
                $this->main();
                break;
            case 'tablaTickets':
                $this->tablaTickets();
                break;
            case 'autorizar':
                $this->autorizar();
                break;
            case 'rechazar':
                $this->rechazar();
                break;
            case 'showTicket':
                $this->showTicket();
                break;
            case 'actualizarTicket':
                $this->actualizarTicket();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        // Filtros
        $botones = '';
        $botones .= <<<HTML
            <input type="search"
                id="filtroTabla"
                style="display: inline; width:300px; height: 40px;"
                class="form-control me-1"
                placeholder="Buscar dato"/>
            HTML;

        $html = '
            <input id="finicio" name="finicio" type="date" value="2025-05-01" class="form-control me-1" tabindex="20" style="float: left; width:130px">

            <input id="ffinal" name="ffinal" type="date" value=' . date('Y-m-d') . ' class="form-control me-1" tabindex="20" style="float: left; width:130px">

            <div id="flttipo_ticket" class="btn-group">
                <button id="tipo_ticket" tta="0" class="btn btn-outline-primary dropdown-toggle me-1" data-bs-toggle="dropdown" type="button">
                    Solicitudes
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="flttipo_ticket">
                    <li><a href="1" class="dropdown-item tipoDeTicket">Usuarios</a></li>
                    <li><a href="2" class="dropdown-item tipoDeTicket">Permisos</a></li>
                </ul>
            </div>

            <div id="flttipo_solicitud" class="btn-group">
                <button id="tipo_solicitud" ttf="0" class="btn btn-outline-secondary dropdown-toggle me-1" data-bs-toggle="dropdown" type="button">
                    Tipo de Solicitud
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="flttipo_solicitud">
                    <li><a href="S" class="dropdown-item tipoDeSolicitud">Sistema</a></li>
                    <li><a href="T" class="dropdown-item tipoDeSolicitud">Ticket</a></li>
                </ul>
            </div>

            <div id="fltestado_ticket" class="btn-group">
                <button id="estado_ticket" tea="0" class="btn btn-outline-info dropdown-toggle me-1" data-bs-toggle="dropdown" type="button">
                    Estado
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="fltestado_ticket">
                    <li><a href="2" class="dropdown-item tipoDeEstado">Enviadas</a></li>
                    <li><a href="3" class="dropdown-item tipoDeEstado">Aceptadas</a></li>
                    <li><a href="4" class="dropdown-item tipoDeEstado">Rechazadas</a></li>
                </ul>
            </div>

            <button id="btn_buscar" data-placement="bottom" title="Mostrar las solicitudes seleccionadas" class="btn btn-outline-success me-1"><i class="fa-solid fa-magnifying-glass me-2"></i>Buscar</button>

            <button id="btn_limpiar" data-placement="bottom" title="Limpiar los campos de filtro" class="btn btn-outline-warning "><i class="fa-solid fa-x me-2"></i>Limpiar</button>
        ';

        $this->agregaTools($botones . $html);

        // Está diseñado para trabajar a ventana expandida, asi que la expandimos por default
        // siempre y cuando no esté ya expandida
        $this->mixScript('$("#toggleCA").hide();
            if ($("#mainContainer").hasClass("panel-expand") == true) {
                $(".fa-expand").click();
            }
            $("#contenedor").css("top", "45px").css("z-index", "100");

            $(".tipoDeTicket").click( function(e) {
                $("#tipo_ticket").html( $(this).text().trim() + "<span class=\"caret\"></span>");
                $("#tipo_ticket").attr( "tta", $(this).attr("href"));
                e.preventDefault();
            });

            $(".tipoDeEstado").click( function(e) {
                $("#estado_ticket").html( $(this).text().trim() + "<span class=\"caret\"></span>");
                $("#estado_ticket").attr( "tea", $(this).attr("href"));
                e.preventDefault();
            });

            $(".tipoDeSolicitud").click( function(e) {
                $("#tipo_solicitud").html( $(this).text().trim() + "<span class=\"caret\"></span>");
                $("#tipo_solicitud").attr( "ttf", $(this).attr("href"));
                e.preventDefault();
            });
        ');
        $this->llenaAntesTablaTickets();
    }

    private function showTicket()
    {
        $usuarioPrevio = new UsuarioPrevio();
        $solicitudAcceso = new SolicitudAcceso();
        if (isset($this->prm->usp_id) && isset($this->prm->sac_id)) {
            $usuarioPrevio->setUsp_id($this->prm->usp_id);
            $usuarioPrevio->select();
            $solicitudAcceso->setSac_id($this->prm->sac_id);
            $solicitudAcceso->select();
        }
        /* creamos la caja de texto para despues pintarla
         * contiene el valor que se guarda en la bd
         */
        $Rfc = new stdClass();
        $Rfc->id = 'rfc';
        $Rfc->class = 'rfc';
        $Rfc->label = 'RFC';
        $Rfc->placeholder = 'RFC';
        $Rfc->enDiv = true;
        $Rfc->maxlength = 13;
        $Rfc->value = $usuarioPrevio->getRfc();
        $Rfc->readonly = true;

        $Usuario = new stdClass();
        $Usuario->id = 'usuario';
        $Usuario->class = 'usuario';
        $Usuario->label = 'Usuario';
        $Usuario->placeholder = 'Nombre de usuario';
        $Usuario->readonly = true;
        $Usuario->enDiv = true;
        $Usuario->maxlength = 20;
        $Usuario->value = $usuarioPrevio->getUsuario();

        $Nombre = new stdClass();
        $Nombre->id = 'nombre';
        $Nombre->name = 'nombre';
        $Nombre->label = 'Nombre';
        $Nombre->enDiv = true;
        $Nombre->maxlength = 40;
        $Nombre->value = $usuarioPrevio->getNombre();
        $Nombre->readonly = true;

        $Paterno = new stdClass();
        $Paterno->id = 'paterno';
        $Paterno->name = 'paterno';
        $Paterno->label = 'Ap. Paterno';
        $Paterno->enDiv = true;
        $Paterno->maxlength = 25;
        $Paterno->value = $usuarioPrevio->getPaterno();
        $Paterno->readonly = true;

        $Materno = new stdClass();
        $Materno->id = 'materno';
        $Materno->name = 'materno';
        $Materno->label = 'AP. Materno';
        $Materno->enDiv = true;
        $Materno->maxlength = 25;
        $Materno->value = $usuarioPrevio->getMaterno();
        $Materno->readonly = true;

        $Sexo = new stdClass();
        $Sexo->id = 'sexo';
        $Sexo->opciones = ['H' => 'Hombre', 'M' => 'Mujer'];
        $Sexo->label = 'Sexo';
        $Sexo->enDiv = true;
        $Sexo->selecciona = $usuarioPrevio->getSexo();
        $Sexo->disabled = true;

        $grupo = new stdClass();
        $grupo->sql = 'SELECT gpo_id, concat(ur_nombre) uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable WHERE gpo_id=' . $usuarioPrevio->getGpo_id() . ' AND ur_id=' . $usuarioPrevio->getUr_id() . ';';
        $grupo->id = 'gpo_id';
        $grupo->label = 'Grupo';
        $grupo->enDiv = true;
        $grupo->selecciona = $usuarioPrevio->getGpo_id();
        $grupo->disabled = true;
        if (is_null($usuarioPrevio->getGpo_id()) || empty($usuarioPrevio->getGpo_id())) {
            $grupo->type = 'hidden';
        }

        $ur = new stdClass();
        $ur->sql = 'SELECT ur_id, concat(ur_nombre) uresponsable FROM ' . $this->ctrlEjercicioActual . '.cat_UnidadResponsable WHERE gpo_id=' . $usuarioPrevio->getGpo_id() . ' AND ur_id=' . $usuarioPrevio->getUr_id() . ';';
        $ur->id = 'ur_id';
        $ur->label = 'Ur';
        $ur->enDiv = true;
        $ur->selecciona = $usuarioPrevio->getUr_id();
        $ur->disabled = true;

        $subSecre = '
            SELECT ssc_id, ssc_nombre subsecretaria
            FROM ' . $this->ctrlEjercicioActual . '.cat_Subsecretaria
            WHERE ssc_id=  ' . $usuarioPrevio->getSsc_id() . ';'
        ;

        $Subsecretaria = new stdClass();
        $Subsecretaria->sql = $subSecre;
        $Subsecretaria->id = 'ssc_id';
        $Subsecretaria->label = 'Subsecretaria';
        $Subsecretaria->enDiv = true;
        $Subsecretaria->selecciona = $usuarioPrevio->getSsc_id();
        $Subsecretaria->disabled = true;

        $Direccion = new stdClass();
        $direc = '
                SELECT dir_id, concat(dir_nombre) Direccion
                FROM ' . $this->ctrlEjercicioActual . '.cat_Direccion
                WHERE dir_id=  ' . $usuarioPrevio->getDir_id() . ';';
        $Direccion->sql = $direc;
        $Direccion->id = 'dir_id';
        $Direccion->label = 'Direccion';
        $Direccion->enDiv = true;
        $Direccion->selecciona = $usuarioPrevio->getDir_id();
        $Direccion->detonar = false;
        $Direccion->disabled = true;

        $Coordinacion = new stdClass();
        $cord = '
            SELECT uni_id, concat(uni_nombre) Direccion
            FROM ' . $this->ctrlEjercicioActual . '.cat_Unidad
            WHERE uni_id=  ' . $usuarioPrevio->getUni_id() . ';';
        $Coordinacion->sql = $cord;
        $Coordinacion->id = 'uni_id';
        $Coordinacion->label = 'Coordinacion';
        $Coordinacion->enDiv = true;
        $Coordinacion->selecciona = $usuarioPrevio->getUni_id();
        $Coordinacion->disabled = true;

        $Departamento = new stdClass();
        $dep = '
            SELECT dep_id, concat(dep_nombre) Direccion
            FROM ' . $this->ctrlEjercicioActual . '.cat_Departamento
            WHERE dep_id=  ' . $usuarioPrevio->getDep_id() . ';';

        $Departamento->sql = $dep;
        $Departamento->id = 'dep_id';
        $Departamento->label = 'Departamento';
        $Departamento->enDiv = true;
        $Departamento->soloText = true;
        $Departamento->selecciona = $usuarioPrevio->getDep_id();
        $Departamento->disabled = true;

        $Nivel = new stdClass();
        $Nivel->sql = 'SELECT nvj_id , concat(nvj_nombre) nivel FROM ' . BD_USUARIO . '.cat_NivelJerarquico  where  nvj_id = ' . $usuarioPrevio->getNvj_id() . ';';
        $Nivel->id = 'nvj_id';
        $Nivel->label = 'Nivel Jerarquico';
        $Nivel->enDiv = true;
        $Nivel->soloText = true;
        $Nivel->selecciona = $usuarioPrevio->getNvj_id();
        $Nivel->disabled = true;

        $UEjecutora = new stdClass();
        $UEjecutora->sql = 'SELECT ue_id, concat (ue_nombre) as uEjecutora FROM cat_UnidadEjecutora ';
        $UEjecutora->id = 'ue_id';
        $UEjecutora->name = 'ue_id';
        $UEjecutora->label = 'Unidad Ejecutora';
        $UEjecutora->enDiv = true;
        $UEjecutora->soloText = true;
        $UEjecutora->disabled = true;
        $UEjecutora->selecciona = $usuarioPrevio->getUe_id();

        $Perfil = new stdClass();
        $consPer = 'SELECT prf_id, concat(prf_nombre) perfil FROM ' . BD_USUARIO . ".cat_Perfil WHERE prf_id='" . $usuarioPrevio->getPrf_id() . "'";
        $Perfil->sql = $consPer;
        $Perfil->id = 'prf_id';
        $Perfil->label = 'Perfil';
        $Perfil->enDiv = true;
        $Perfil->soloText = true;
        $Perfil->disabled = true;
        $Perfil->selecciona = $usuarioPrevio->getPrf_id();

        $Puesto = new stdClass();
        $Puesto->id = 'puesto';
        $Puesto->name = 'puesto';
        $Puesto->label = 'Puesto';
        $Puesto->required = true;
        $Puesto->enDiv = true;
        $Puesto->readonly = true;
        $Puesto->value = $usuarioPrevio->getPuesto();

        $Titulo = new stdClass();
        $Titulo->id = 'titulo';
        $Titulo->name = 'titulo';
        $Titulo->label = 'Titulo';
        $Titulo->enDiv = true;
        $Titulo->readonly = true;
        $Titulo->value = $usuarioPrevio->getTitulo();

        $Email = new stdClass();
        $Email->id = 'correo';
        $Email->name = 'correo';
        $Email->label = 'Correo';
        $Email->type = 'email';
        $Email->enDiv = true;
        $Email->readonly = true;
        $Email->value = $usuarioPrevio->getCorreo();

        $Telefono = new stdClass();
        $Telefono->id = 'telefono';
        $Telefono->name = 'telefono';
        $Telefono->label = 'Telefono';
        $Telefono->formato = 'entero';
        $Telefono->enDiv = true;
        $Telefono->readonly = true;
        $Telefono->value = $usuarioPrevio->getTelefono();

        $Extension = new stdClass();
        $Extension->id = 'extension';
        $Extension->name = 'extension';
        $Extension->label = 'Extension';
        $Extension->formato = 'entero';
        $Extension->enDiv = true;
        $Extension->readonly = true;
        $Extension->value = $usuarioPrevio->getExtension();

        $Requerimientos = new stdClass();
        $Requerimientos->id = 'requerimiento';
        $Requerimientos->name = 'requerimiento';
        $Requerimientos->label = 'Requerimiento';
        $Requerimientos->enDiv = true;
        $Requerimientos->width = '300px';
        $Requerimientos->height = '100px';
        $Requerimientos->text = $solicitudAcceso->getRequerimiento();
        $Requerimientos->readonly = true;

        $Comentarios = new stdClass();
        $Comentarios->id = 'comentario';
        $Comentarios->name = 'comentario';
        $Comentarios->label = 'Comentario';
        $Comentarios->enDiv = true;
        $Comentarios->readonly = true;
        $Comentarios->width = '300px';
        $Comentarios->height = '100px';
        $Comentarios->text = $solicitudAcceso->getComentario();

        $NombreResponsable = new stdClass();
        $NombreResponsable->sql = '
            Select sac_Jefe FROM ' . BD_USUARIO . '.cat_SolicitudAcceso
            inner join ' . BD_USUARIO . '.cat_UsuarioPrevio USING (sac_id)
            WHERE usp_id =' . $usuarioPrevio->getUsp_id() . ';';
        $NombreResponsable->id = 'coordinadorTitular';
        $NombreResponsable->name = 'coordinadorTitular';
        $NombreResponsable->label = 'Responsable';
        $NombreResponsable->enDiv = true;
        $NombreResponsable->disabled = true;
        $NombreResponsable->selecciona = $solicitudAcceso->getJefe();

        $NombreJefe = new stdClass();
        $NombreJefe->sql = 'select sac_Responsable from ' . BD_USUARIO . '.cat_SolicitudAcceso
            inner join ' . BD_USUARIO . '.cat_UsuarioPrevio USING (sac_id)
            where usp_id = ' . $usuarioPrevio->getUsp_id() . ';';
        $NombreJefe->id = 'directorTitular';
        $NombreJefe->name = 'directorTitular';
        $NombreJefe->label = 'Jefe que Autoriza';
        $NombreJefe->enDiv = true;
        $NombreJefe->disabled = true;
        $NombreJefe->selecciona = $solicitudAcceso->getResponsable();

        $form = new stdClass();
        $form->id = 'frmticket';
        $form->controlador = $this->pantalla;
        $form->datosExtra = new stdClass();

        $abc = 'C';
        $bandera = 0;
        $form->datosExtra->abc = $abc;
        if ($bandera == 1) {
            $form->datosExtra->sac_id = $this->prm->sac_id;
            $form->datosExtra->usp_id = $this->prm->usp_id;
            $form->datosExtra->eso_id = $solicitudAcceso->getEso_id();
            $form->datosExtra->sefip = $solicitudAcceso->getSefip();
            $form->datosExtra->nuevo = $usuarioPrevio->getNuevo();
            $form->datosExtra->usu = $usuarioPrevio->getUsu();
        }
        $form->datosExtra->permisos = '"{objetoMeta()}"';
        $form = new form($form);

        $btnCancelarU = new stdClass();
        $btnCancelarU->value = 'Regresar';
        $btnCancelarU->id = 'cancel-ticket';
        $btnCancelarU->icon = 'circle-left me-2';
        $btnCancelarU->color = 'gris';

        $acu = '';
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] > 2) {
            $acu .= '<div class="row">';
            $acu .= (empty($usuarioPrevio->getSsc_id()) ? '' : ' <fieldset class="col-md-6">' . $this->ponCombo($Subsecretaria)) . '</fieldset> ';
            $acu .= (empty($usuarioPrevio->getDir_id()) ? '' : ' <fieldset class="col-md-6" id="Direccion">' . $this->ponCombo($Direccion)) . '</fieldset> ';
            $acu .= '</div>';
            $acu .= '<div class="row">';
            $acu .= (empty($usuarioPrevio->getUni_id())) ? '' : ' <fieldset class="col-md-6">' . $this->ponCombo($Coordinacion) . '</fieldset>';
            $acu .= (empty($usuarioPrevio->getDep_id())) ? '' : ' <fieldset class="col-md-6">' . $this->ponCombo($Departamento) . '</fieldset>';
            $acu .= '</div>';
        }
        $form->mixHtml('
            <ul class="nav nav-tabs" role="tablist">
                <li class="active liT" role="presentation">
                    <a href="#addTicket1" class="nav-link active" id="addTicket1-tab" data-bs-toggle="tab" data-bs-target="#addTicket1" type="button" role="tab" aria-selected="true">
                        Datos Generales
                    </a>
                </li>
                <li class="liT2" role="presentation">
                    <a href="#addTicket2" class="nav-link" id="addTicket2-tab" data-bs-toggle="tab" data-bs-target="#addTicket2" type="button" role="tab" aria-selected="true">
                        Permisos
                    </a>
                </li>
            </ul>
            <div class="tab-content container">
                <fieldset class="mb-4"></fieldset>
                <div class="tab-pane active" id="addTicket1" role="tabpanel" aria-labelledby="addTicket1-tab">
                    <div class="row">
                        <h3>Solicitud de Usuario</h3>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Titulo) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Nombre) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Paterno) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Materno) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Rfc) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Sexo) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($grupo) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($ur) . '
                        </fieldset>
                    </div>
                    ' . $acu . '
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Nivel) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Perfil) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset id="idUnidadEjecutora" class="col-md-12">
                        </fieldset>
                    </div>

                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Puesto) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Email) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Telefono) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Extension) . '
                        </fieldset>
                    </div>
                    <br/><br/>

                    <div class="row">
                        <fieldset class="col-md-2">
                            <input type="checkbox" id="sistema" name="sistema" class="uno" value="SEFIP" checked disabled/> SEFIP
                        </fieldset>
                    </div>

                    <br/><br>
                    <div class="row">
                        <fieldset class="col-md-12">
                            ' . $this->ponTextArea($Requerimientos) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-12">
                            ' . $this->ponTextArea($Comentarios) . '
                        </fieldset>
                    </div>
                    <br/><br/>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Usuario) . '
                        </fieldset>
                        <fieldset class="col-md-3">
                            <div class="form-group ">
                                <label class="control-label" for="fechaInicio">
                                    Vigente Desde:
                                </label>
                                <input name="fechaInicio" id="fechaInicio" type="text" required readonly class="form-control">
                            </div>
                        </fieldset>
                        <fieldset class="col-md-3">
                            <div class="form-group ">
                                <label class="control-label" for="fechaFin hasta">
                                    Vigente hasta:
                                </label>
                                <input name="fechaFin" id="fechaFin" type="text" required readonly class="form-control">
                            </div>
                        </fieldset>
                    </div>

                    <div class="row">
                        <fieldset class="col-md-6">' . $this->ponCombo($NombreResponsable) . '</fieldset>
                        <fieldset class="col-md-6">' . $this->ponCombo($NombreJefe) . '</fieldset>
                    </div>
                </div> ');

        $form->mixHtml('<div class="tab-pane" id="addTicket2" role="tabpanel" aria-labelledby="addTicket2-tab">
                    <div class="row align-items-start">
                        <h3>Permisos de usuario</h3>
                    </div>
                    ');

        // Como en la tabla cat_PermisoUsuariosPrevio está la fecha de vigencia inicial y final, y ésta es IGUAL
        // para todos los permisos, lo tomamos de ahí
        $fechaInicio = '';
        $fechaFinal = '';

        $this->conectar();
        $this->setSql('SELECT sis_id,sis_nombre, men_id, men_titulo, opc_id, opc_titulo, tar_id, if(sis_id="I","HABILITAR",tar_nombre) tar_nombre, "" usr_id,

                DATE_FORMAT( usp_fechaInicio, "%d/%m/%Y" ) AS fechaInicio,
                DATE_FORMAT( usp_fechaFin, "%d/%m/%Y" ) AS fechaFin

            FROM ' . $this->ctrlEjercicioActual . '.cat_Sistema
            JOIN ' . $this->ctrlEjercicioActual . '.cat_Opcion opc USING(sis_id)
            JOIN ' . $this->ctrlEjercicioActual . '.cat_Menu USING(sis_id,men_id)
            JOIN ' . $this->ctrlEjercicioActual . '.cat_Permisos USING(sis_id,men_id,opc_id)
            JOIN ' . $this->ctrlEjercicioActual . '.cat_Tareas USING(tar_id)
            JOIN ' . BD_USUARIO . '.cat_PermisoUsuarioPrevio pu USING(sis_id,men_id,opc_id,tar_id)
            JOIN ' . BD_USUARIO . '.cat_UsuarioPrevio USING(usp_id)
            WHERE pu.usp_id= ? ORDER BY sis_id,men_id,opc_id,tar_id');
        $parametros = [$usuarioPrevio->getUsp_id()];
        $this->consultar($parametros);

        $sis = $men = $opc = $tar = $tchk = '';
        $perVal = $perVal2 = [];
        $conVal = 0;

        $h = '<ul id="browser" class="filetree">';

        //Castro Jordy 15-03-18 Muestra los elementos de los permisos
        while ($w = $this->extraer()) {

            $fechaInicio = $w['fechaInicio'];
            $fechaFin = $w['fechaFin'];

            if ($sis != $w['sis_id']) {
                if ($sis != '') {
                    if ($men != '') {
                        if ($opc != '') {
                            $h .= '</ul></li>';
                        }
                        $opc = '';
                        $men = '';
                        $h .= '</ul></li>';
                    }
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['sis_nombre'] . '</span><ul>';
                $sis = $w['sis_id'];
            }
            if ($men != $w['men_id']) {
                if ($men != '') {
                    if ($opc != '') {
                        $h .= '</ul></li>';
                    }
                    $opc = '';
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['men_titulo'] . '</span><ul>';
                $men = $w['men_id'];
            }
            if ($opc != $w['opc_id']) {
                if ($opc != '') {
                    $h .= '</ul></li>';
                }
                $h .= '<li><span class="folder">' . $w['opc_titulo'] . '</span><ul>';
                $opc = $w['opc_id'];
            }

            // Se selecciona los permisos y se agregan los permisos
            $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" checked disabled />' . $w['tar_nombre'] . '</span></li>';
        }
        $this->desconectar();
        $h .= ' </ul></li><!--Opcion-->
                </ul></li> <!--Menu-->
                </ul><!--ul del sistema--></li>
                </ul><!--ul clase filetree-->';
        $form->mixHtml('
            ' . $h . '
            </div>
        </div>
        </div>
        ');

        $this->agregaTools($this->ponBoton($btnCancelarU));
        $this->mixHtml($form->ponForm());
        $this->mixScript('

            $("#fechaInicio").val("' . $fechaInicio . '");
            $("#fechaFin").val("' . $fechaFin . '");

            $("#cancel-ticket").click(function(){
                callbackComunica = function(){$(".li3").click();}
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    act:2
                });
                return false;
            });

            $("#browser").treeview({
                animated:"normal",
                collapsed: false
            });
        ');
    }

    private function llenaAntesTablaTickets()
    {
        $this->mixHtml('
            <div>
                <div>
                    <div id="tickets2"> </div>
                </div>
            </div>
        ');
        $this->tablaTickets();

        $this->mixScript('
            $("#btn_buscar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaTickets",
                    no: $("#no_ticket").val(),
                    finicio: $("#finicio").val(),
                    ffinal: $("#ffinal").val(),
                    estado: $("#estado_ticket").attr("tea"),
                    tipo_solicitud: $("#tipo_solicitud").attr("ttf"),
                    tipo: $("#tipo_ticket").attr( "tta" )
                });
            });

            $("#no_ticket").keypress(function(e){
                var keynum = window.event ? window.event.keyCode : e.which;
                if (keynum == 8) {
                    return true;
                } else if (keynum==95) {
                    return false;
                } else if ((keynum>=65 && keynum<=90)||(keynum>=97 && keynum<=122)) {
                    return false;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            $("#btn_limpiar").click(function(){
                $("input").val("");
                comunica("' . $this->pantalla . '",{
                    metodo:"main"
                });
            });
        ');
    }

    /* tablaTickets este metodo dibuja y llena la tabla, aqui estan los scripts de la tabla
    no de los botones de busqueda*/
    private function tablaTickets()
    {
        $this->setContenedor('tickets2');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Usr';
        $tabla->esCompacta = true;

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'tablaTickets';
        //----------------------Encabezado de la tabla ------------
        $tr = [];
        $th = new stdClass();
        $th->texto = 'Ver';
        $th->width = '40px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'No. de Solicitud';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        $th->width = '80px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Tipo Solicitud';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Puesto';
        $th->width = '80px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Correo';
        $th->width = '80px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Teléfono';
        $th->width = '80px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'No. Ticket';
        $th->width = '60px';
        array_push($tr, $th);

        //---Estado en de la solicitud
        $th = new stdClass();
        $th->texto = 'Aceptar';
        $th->width = '40px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Rechazar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Imprimir';
        $th->width = '40px';
        array_push($tr, $th);

        //----------------------Cuerpo de la tabla--------------------
        $trbody = [];
        $td = new stdClass();

        /* Start del boton Ver */
        $td = new stdClass();
        $td->id = 'verSolicitud';
        $td->tipo = 'boton';
        $td->icono = 'fa-solid fa-eye';
        $td->class = 'verSolicitud';
        $td->attr = new stdClass();
        $td->attr->tik_id = 'usp_id';
        $td->attr->sac_id = 'sac_id';
        array_push($trbody, $td);
        /* End boton Ver */

        $td = new stdClass();
        $td->campoTxt = 'sac_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'unida_resp';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usp_puesto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usp_correo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usp_telefono';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'sac_ticket';
        $td->align = 'justify';
        array_push($trbody, $td);

        /* START llenar columna aceptar solicitud */
        if (isset($_SESSION[_S]['prm']['M']['9']['1'][TAREA_ESCRITURA])) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eso_id'; //campo a comparar
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->valor = '2';
            $caso->id = 'aceptar';
            $caso->tipo = 'boton';
            $caso->class = 'aceptar';
            $caso->color = 'verde';
            $caso->icono = 'check';
            $caso->align = 'center';
            $caso->attr = new stdClass();
            $caso->attr->tik_id = 'usp_id';
            $caso->attr->sac_id = 'sac_id';
            $caso->attr->estado = 'eso_id';
            $caso->attr->tipo = 'tipo';
            $caso->attr->sac_ticket = 'sac_ticket';
            array_push($td->casos->cases, $caso);
            $caso = new stdClass();
            $caso->valor = '3';
            $caso->id = 'aceptar';
            $caso->tipo = 'txtu';
            $caso->class = 'btn-user btn-user-success';
            $caso->campoTxt = 'Estado';
            $caso->lbl = 'Estado';
            array_push($td->casos->cases, $caso);
        } else {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eso_id'; //campo a comparar
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->valor = '2';
            $caso->id = 'aceptar';
            $caso->tipo = 'txtu';
            $caso->class = 'btn-user btn-user-warning';
            $caso->campoTxt = 'Estado';
            $caso->lbl = 'Estado';
            array_push($td->casos->cases, $caso);
            $caso = new stdClass();
            $caso->valor = '3';
            $caso->id = 'aceptar';
            $caso->tipo = 'txtu';
            $caso->class = 'btn-user btn-user-success';
            $caso->campoTxt = 'Estado';
            $caso->lbl = 'Estado';
            array_push($td->casos->cases, $caso);
        }
        array_push($trbody, $td);
        /* END llenar columna aceptar solicitud */

        /* Star cancelar solicitud */
        if (isset($_SESSION[_S]['prm']['M']['9']['1'][TAREA_ESCRITURA])) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eso_id'; //campo a comparar
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->id = 'cancelar';
            $caso->valor = '2';
            $caso->tipo = 'boton';
            $caso->class = 'cancelar';
            $caso->icono = 'trash';
            $caso->color = 'rojo';
            $caso->align = 'center';
            $caso->attr = new stdClass();
            $caso->attr->tik_id = 'usp_id';
            $caso->attr->sac_id = 'sac_id';
            $caso->attr->estado = 'eso_id';
            $caso->attr->sac_ticket = 'sac_ticket';
            array_push($td->casos->cases, $caso);
            $caso = new stdClass();
            $caso->valor = '4';
            $caso->id = 'cancelar';
            $caso->tipo = 'txtu';
            $caso->class = 'btn-user btn-user-danger';
            $caso->campoTxt = 'Estado';
            $caso->lbl = 'Estado';
            array_push($td->casos->cases, $caso);
        } else {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'eso_id'; //campo a comparar
            $td->casos->cases = [];
            $caso = new stdClass();
            $caso->valor = '4';
            $caso->id = 'cancelar';
            $caso->tipo = 'txtu';
            $caso->class = 'btn-user btn-user-danger';
            $caso->campoTxt = 'Estado';
            $caso->lbl = 'Estado';
            array_push($td->casos->cases, $caso);
        }
        array_push($trbody, $td);
        /* End cancelar solicitud */

        $td = new stdClass();
        $td->id = 'verimprimir';
        $td->tipo = 'boton';
        $td->icono = 'file-pdf me-2';
        $td->class = 'btn btn-outline-danger verimprimir';
        $td->label = 'PDF';
        $td->attr = new stdClass();
        $td->attr->tik_id = 'usp_id';
        $td->attr->sac_id = 'sac_id';
        array_push($trbody, $td);

        /* End imprimir */

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $where = '';
        $no_sql = '';
        $nombre_sql = '';
        $estado_sql = '';
        $correo_sql = '';
        $tipo_sql = '';
        $inicial = '2025-05-01';
        $actual = date('Y-m-d');

        $where = 'WHERE eso_id <> 1 AND sac_ejercicio=' . APP_EJERCICIO . '';

        // solicitudes especificas
        // condicion para buscar por estado
        // 0: todas   1: Enviadas   2: Aceptadas   3: Rechazadas
        if (isset($this->prm->estado)) {
            if ($this->prm->estado != '0') {
                $where .= ' AND eso_id = ' . $this->prm->estado;
            }
        }

        // condicion para buscar por numero de ticket
        if (isset($this->prm->no) && $this->prm->no != '') {
            $where .= ' AND sac_id = ' . $this->prm->no;
        }

        //condicion para buscar por fecha
        if (isset($this->prm->finicio) && isset($this->prm->ffinal)) {
            if (($this->prm->finicio == '') && ($this->prm->ffinal == '')) {
                $where .= " AND CAST(sac_fechaAlta AS DATE) BETWEEN '" . $inicial . "' AND '" . $actual . "'";
            } else {
                $where .= " AND CAST(sac_fechaAlta AS DATE) BETWEEN '" . $this->prm->finicio . "' AND '" . $this->prm->ffinal . "'";
            }
        }

        // filtro por nombre
        if (isset($this->prm->nombre) && $this->prm->nombre != '') {
            $where .= " AND concat(usp_nombre,' ',usp_paterno,' ',usp_materno) LIKE '%" . $this->prm->nombre . "%'";
        }

        // condicion para buscar por tipo de solicitud Ticket/Sistemas
        if (isset($this->prm->tipo) && $this->prm->tipo != '') {
            if ($this->prm->tipo != '0') {
                $where .= " AND usp_nuevo = '" . $this->prm->tipo . "'";
            }
        }
        // condicion para buscar por tipo de solicitud Ticket/Sistema
        if (isset($this->prm->tipo_solicitud) && $this->prm->tipo_solicitud != '') {
            if ($this->prm->tipo_solicitud != '0') {
                $where .= " AND sac_tipo = '" . $this->prm->tipo_solicitud . "'";
            }
        }

        $where .= ')AS s ';

        $sql = '
            SELECT *
            FROM (SELECT sac_id, usp_id, concat(usp_nombre," ",usp_paterno," ",usp_materno) nombre,
            usp_puesto, usp_correo, sac_ticket, eso_id,usp_nuevo,
            CASE eso_id
            WHEN 1 THEN "GENERADO"
            WHEN 2 THEN "ENVIADO"
            WHEN 3 THEN "ACEPTADO"
            WHEN 4 THEN "RECHAZADO"
            END AS Estado,
            CASE sac_tipo
            WHEN "T" THEN "TICKET"
            WHEN "S" THEN "SISTEMA"
            END AS sac_tipo,
            CASE usp_nuevo
            WHEN 1 THEN "USUARIO"
            WHEN 2 THEN "PERMISO"
            END AS tipo,
            CONCAT (gpo_id,ur_id) as unida_resp,
            usp_telefono
            FROM ' . BD_USUARIO . '.cat_UsuarioPrevio cup
            INNER JOIN ' . BD_USUARIO . '.cat_SolicitudAcceso USING (sac_id)
            INNER JOIN ' . BD_CTRL . '.cat_UnidadResponsable USING (uur_id)
            ' . $where . '
            ;'
        ;
        $this->conectar();
        $this->setSql($sql);
        $this->consultar();
        $nRegistros = $this->getNumeroRegistros();

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : $nRegistros;
        $nav->idTabla = 'tbl-Usr';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(document).ready(function() { 
                $("#tabla-tbl-Usr").filterTable({ 
                    inputSelector: "#filtroTabla",
                    minRows: 1
                }); 
            });

            $(".verSolicitud").click( function (){
                        comunica("' . $this->pantalla . '",{
                            metodo:"showTicket",
                            usp_id: $(this).attr("tik_id"),
                            sac_id: $(this).attr("sac_id")
                        });
            });

            $(".aceptar").click(function(){
                usp_id = $(this).attr("tik_id");
                sac_id = $(this).attr("sac_id");
                tipo   = $(this).attr("tipo");
                sac_ticket = $(this).attr("sac_ticket");
                if (validateTicket(sac_ticket))
                {
                alertify.confirm("<h5>Autorizar Solicitud</h5>", "¿Desea <strong>AUTORIZAR</strong> la solicitud No. <strong> " + $(this).attr("sac_id") + "</strong>?", 
                    function(){ 
                        comunica("' . $this->pantalla . '", {
                                        metodo: "autorizar",
                                        usp_id: usp_id,
                                        sac_id: sac_id,
                                        tipo:   tipo
                                    });
                    },function (){}).set("defaultFocus", "cancel").set("labels",{ok: "Aceptar", cancel:"Cancelar"});
                }else{
                        alertify.prompt("NÚMERO DE TICKET",
                            "Ingrese el número de ticket (ej. SDS-123456 o 123456) para continuar con la autorización.",
                            "SDS-XXXXXX",
                        function(evt, value ){
                            if (validateTicket(value)){
                            comunica("' . $this->pantalla . '", {
                                        metodo: "actualizarTicket",
                                        sac_id: sac_id,
                                        sac_ticket: value.toUpperCase()
                                    });
                            }else{
                                    notify("Agregar un ticket válido", "warning");
                                    return false;
                                }
                        },function (){}).set("defaultFocus", "cancel").set("labels",{ok: "Aceptar", cancel:"Cancelar"});
                    }
            });

            function validateTicket(sac_ticket) {
                    const regexFormato = /^[A-Za-z]{3}-(?!.*000)\d{6}$/;

                    const regexNumerico = /^(?!.*000)\d{6}$/;

                    return regexFormato.test(sac_ticket) || regexNumerico.test(sac_ticket);
                }

            $(".cancelar").click( function (){
                uspId = $(this).attr("tik_id");
                sac_id = $(this).attr("sac_id");
                tipo = $(this).attr("tipo");

                 alertify.confirm("<h5>Rechazar solicitud<h5>", "¿Desea <strong>RECHAZAR</strong> la solicitud de usuario No. <strong> " + sac_id +"</strong>?",
                    function(){
                    comunica("' . $this->pantalla . '",{
                            metodo: "rechazar",
                            usp_id: uspId,
                            sac_id: sac_id,
                            tipo: tipo
                            });
                    }, function (){}).set("defaultFocus", "cancel").set("labels",{ok: "Aceptar", cancel:"Cancelar"});
            });

            $(".verimprimir").click( function (){
                    tik_id= $(this).attr("tik_id");
                    sac_id=$(this).attr("sac_id");
                    var form = $("<form>")
                            .attr("action", "rep/doc/srv/FrmAcceso.php")
                            .attr("method", "post")
                            .attr("name", "Ticket")
                            .attr("id", "Ticket")
                            .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "idTicket").attr("value", tik_id))
                                .append($("<input>").attr("type", "hidden").attr("name", "Tick").attr("value", tik_id.toString()))
                                .append($("<input>").attr("type", "hidden").attr("name", "TickSc").attr("value", sac_id.toString()))
                            $("body").append(form);
                            form.submit();
                            form.remove();
            });

            $(".reenviar").click( function (){
                    tik_id= $(this).attr("tik_id");
                    sac_id=$(this).attr("sac_id");
                    var form = $("<form>")
                            .attr("action", "rep/doc/srv/FrmAcceso.php")
                            .attr("method", "post")
                            .attr("name", "Ticket")
                            .attr("id", "Ticket")
                            .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "idTicket").attr("value", tik_id))
                                .append($("<input>").attr("type", "hidden").attr("name", "Tick").attr("value", tik_id.toString()))
                                .append($("<input>").attr("type", "hidden").attr("name", "TickSc").attr("value", sac_id.toString()))
                            $("body").append(form);
                            form.submit();
                            form.remove();
            });
        ');
    }

    private function actualizarTicket()
    {
        //bit_observacion
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $sql = '
        SET 
            @usr_id = :usr_id,
            @bit_observacion = :observacion,
            @usr_ip = INET_ATON( :ip );
        ';
        $params = [
            ':usr_id'      => $_SESSION[_S]['usr_id'],
            ':observacion' => 'ACTUALIZACIÓN DE No TICKET',
            ':ip'          => $ip,
        ];
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($params);


        $sqlT = 'UPDATE ' . BD_USUARIO . '.cat_SolicitudAcceso 
            SET sac_ticket = :sac_ticket WHERE sac_id = :sac_id;';
        $parametros = [
            ':sac_ticket' => $this->prm->sac_ticket,
            ':sac_id'     => $this->prm->sac_id,
        ];

        $this->setSql($sqlT);
        //$this->setParams($parametros);
        $this->consultar($parametros);
        $this->desconectar();

        $this->mixScript('
            notify("Ticket agregado exitosamente.","success");
        ');
        $this->llenaAntesTablaTickets();
    }
    // Autorizamos una solicitud (tanto de usuario como de permisos)
    private function autorizar()
    {
        // Por si las moscas, validamos que todos los datos necesarios existan, y no estén vacíos
        if (!isset($this->prm->sac_id) || !isset($this->prm->usp_id) || !isset($this->prm->tipo)
            || trim($this->prm->sac_id) == '' || trim($this->prm->usp_id) == '' || trim($this->prm->tipo) == ''
        ) {
            $this->mixScript('
                alertify
                .alert("Falta información. Por favor reporta éste problema con un ticket",
                function(){
                    alertify.message("OK");
                });
            ');
            return false;
        }

        //Cambia el estatus del registro
        $actualiza = new stdClass();
        $actualiza->abc = 'c';
        $actualiza->sac_id = $this->prm->sac_id;
        $actualiza->eso_id = 3; // Éste es el STATUS que se le pondrá

        // Si estamos autorizando una solicitud de usuario...
        if ($this->prm->tipo == 'USUARIO') {
            // Obtenemos los datos de la solicitud y del usuario registradas
            $usuarioPrevio = new UsuarioPrevio();
            $usuarioPrevio->setUsp_id($this->prm->usp_id);
            $usuarioPrevio->select();

            $solicitudAcceso = new SolicitudAcceso();
            $solicitudAcceso->setSac_id($this->prm->sac_id);
            $solicitudAcceso->setEjercicio(APP_EJERCICIO);
            $solicitudAcceso->select();

            // Lo buscamos en la tabla definitiva, para determinar si ya existe
            $this->conectar();
            $this->setSql('
                SELECT count(*) as veces FROM ' . BD_USUARIO . '.cat_UsuarioPrevio
                INNER JOIN ' . BD_USUARIO . '.cat_SolicitudAcceso USING(sac_id)
                WHERE uur_id= ? AND usp_ejercicio= ? and eso_id= ?
                AND usp_usuario= ?;
            ');
            $parametros = [$usuarioPrevio->getUur_id(), APP_EJERCICIO, 3, $usuarioPrevio->getUsuario()];
            $this->consultar($parametros);
            $datos = $this->extraer();
            $seCrea = true;

            if ($datos['veces'] > 0) {
                $seCrea = false;
            }

            $this->desconectar();

            if ($seCrea == true) {
                // Lo pasamos a Procedimiento Almacenado, todo el código anterior dentro de éste IF se
                // elimina, ya que es la parte que el procedimiento reemplaza

                $this->conectar();

                // Parece que habilitaron una bitácora que no estaba, porque éste día reportaron el error
                $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
                $this->conectar();
                $this->setSql('SET @usr_ip = INET_ATON( "' . $ip . '" ), @usr_id = ' . $_SESSION[_S]['usr_id'] . ', @bit_observacion = "ELIMINAR SOLICITUD"');
                $this->consultar();

                // Llamamos al procedimiento almacenado, pasándole sólamente el valor de SAC_ID
                // los otros dos valores son para la bitácora: usr_id y usr_ip
                $sql = 'CALL ctrlUsuario.sp_AutorizarSolicitudAcceso(' . $this->prm->sac_id . ', INET_ATON( "' . $ip . '" ), ' . $_SESSION[_S]['usr_id'] . ')';
                $this->setSql($sql);
                $this->consultar();

                $this->desconectar();
            }

            // Preparamos la información para enviar el correo
            $usp_id = $usuarioPrevio->getUsp_id();
            $responsable = $usuarioPrevio->getNombre() . ' ' . $usuarioPrevio->getPaterno() . ' ' . $usuarioPrevio->getMaterno();
            $usuarioTemp = $usuarioPrevio->getUsuario();
            $claveTemp = $usuarioPrevio->getClaveAcceso();
            $correoTemp = $usuarioPrevio->getCorreo();
            $gpo_id = $usuarioPrevio->getGpo_id();
            $ur_id = $usuarioPrevio->getUr_id();
            $UrTemp = $usuarioPrevio->getUr_id() . $usuarioPrevio->getGpo_id();
            $Ur = $this->dameUr($gpo_id, $ur_id);

            $pass = "select AES_DECRYPT(usp_claveAcceso,'SEFIP2027') as pass from ctrlUsuario.cat_UsuarioPrevio
            where usp_id= ?";
            $parametros = [$usp_id];

            $this->conectar();
            $this->setSql($pass);
            $this->consultar($parametros);
            $claveTemp = $this->extraer();

            $correoDA = 'select ur_correoElectronicoAdministrativo from ' . $this->ctrlEjercicioActual . '.aux_UnidadResponsable WHERE gpo_id = ? AND ur_id= ?';
            $parametros = [$gpo_id, $ur_id];

            $this->conectar();
            $this->setSql($correoDA);
            $this->consultar($parametros);
            $correoDA = $this->extraer();
            $emailDA = $correoDA['ur_correoElectronicoAdministrativo'];

            unset($usuarioPrevio);
            unset($solicitudAcceso);

            if ($seCrea) {
                $this->sendEmail($responsable, $usuarioTemp, $claveTemp['pass'], $correoTemp, $emailDA, $Ur);
                $this->mixScript('notify("Solicitud de acceso autorizada","success");');
            } else {
                $this->mixScript('
                    notify("Es necesario generar una solicitud de permisos, ya que el usuario ya existe.","error");
                ');
            }
            // Si estamos autorizando una solicitud de PERMISOS...
        } elseif ($this->prm->tipo == 'PERMISO') {
            $this->conectar();

            // Marcos: 17/jul/2018
            // Parece que habilitaron una bitácora que no estaba, porque éste día reportaron el error
            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
            $sql = 'SET @usr_ip = INET_ATON( :ip ), @usr_id = :usrid, @bit_observacion = "ACTUALIZAR SOLICITUD"';
            $parametros = [
                ':ip'    => $ip,
                ':usrid' => $_SESSION[_S]['usr_id'],
            ];
            $this->setSql($sql);
            $this->consultar($parametros);

            // Llamamos al procedimiento almacenado, pasándole sólamente el valor de SAC_ID
            // los otros dos valores son para la bitácora: usr_id y usr_ip

            $sql = 'CALL ctrlUsuario.sp_AutorizarSolicitudAcceso(' . $this->prm->sac_id . ', INET_ATON( "' . $ip . '" ), ' . $_SESSION[_S]['usr_id'] . ')';
            $this->setSql($sql);
            $this->consultar();

            $this->mixScript('notify("Los permisos se registraron existosamente.","success");');

            unset($solicitudAcceso);
            $this->desconectar();
        }
        unset($this->prm->tipo);
        $this->llenaAntesTablaTickets();
    }

    public function dameUr($gpo_id, $ur_id)
    {
        if (strlen($ur_id) == 1) {
            $ur_id = '0' . $ur_id;
        }
        return $gpo_id . $ur_id;
    }

    public function rechazar()
    {
        //Cambia el estatus del registro
        $actualiza = new stdClass();
        $actualiza->abc = 'c';
        $actualiza->sac_id = $this->prm->sac_id;
        $actualiza->eso_id = 4; // valor al que cambiara el campo de 1 a 2
        SolicitudAcceso::abc($actualiza);

        $this->mixScript('notify("La solicitud fue rechazada","error");');
        $this->llenaAntesTablaTickets();
    }

    public function sendEmail($responsable, $user, $password, $usr_email, $emailDA, $Ur)
    {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->isHtml(true);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->Username = SMTP_SISTEMA_USER;
            $mail->Password = SMTP_SISTEMA_PASS;
            $mail->From = SMTP_SISTEMA_USER;
            $mail->FromName = 'SEFIP2027';
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->addAddress($usr_email, 'Notificacion Usuarios');
            $logo = dirname(__DIR__) . '/images/logo-sistema-2027.png';
            if (is_readable($logo)) {
                $mail->addEmbeddedImage($logo, 'logo_sistema', 'logo-sistema-2027.png');
            }
            if (SERVER_PRODUCCION && !empty($emailDA) && filter_var($emailDA, FILTER_VALIDATE_EMAIL)) {
                $mail->addCC($emailDA);
            }
            $mail->Body = $this->htmlEmail($responsable, $user, $password, $Ur);
            $mail->Subject = 'Credenciales de acceso SEFIP' . APP_EJERCICIO;
            $mail->send();
            $this->mixScript('notify("Credenciales de acceso enviadas al usuario", "success");');
        } catch (PHPMailerException $e) {
            $this->logFalloEmail($e->getMessage(), $usr_email, $user);
            $this->mixScript('notify("No fue posible enviar las credenciales","error");');
        } catch (Throwable $e) {
            $this->logFalloEmail($e->getMessage(), $usr_email, $user);
            $this->mixScript('notify("No fue posible enviar las credenciales","error");');
        }
    }

    private function logFalloEmail($mensaje, $usr_email, $user)
    {
        $linea = 'CLASE[ControladorTicketAdmon]||'
            . 'ERROR[' . $mensaje . ']||'
            . 'DESTINO[' . $usr_email . ']||'
            . 'USUARIO[' . $user . ']';
        if (function_exists('escribirLogProyecto')) {
            escribirLogProyecto('errorEmail', $linea);
            return;
        }
        $dirLog = dirname(__DIR__) . '/log';
        if (!is_dir($dirLog)) {
            @mkdir($dirLog, 0777, true);
        }
        @file_put_contents(
            $dirLog . '/errorEmail-' . date('Y-m-d') . '.log',
            date('d-m-Y H:i:s') . ' ' . $linea . PHP_EOL,
            FILE_APPEND
        );
    }

    // plantilla del email
    public function htmlEmail($responsable, $user, $password, $Ur)
    {
        return '<!DOCTYPE html>
                <html style="box-sizing: border-box; font-size: 14px; margin: 0;">
                <head>
                    <meta name="viewport" content="width=device-width" />
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <div style="width:80%; margin: 1rem auto; font-family: Montserrat, tahoma, Arial, sans-serif;">
        <div style="display: flex; flex-direction: row; flex-wrap: wrap; align-items: center; padding: 1rem 0;">
            <div style="flex-grow: 3; font-size: 1.75rem; font-weight: 500; color:#9D2449;">Autorización de Usuario</div>
            <div style="flex-grow: 1; justify-self: end;">
                <a href="https://sefip.finanzasoaxaca.gob.mx/2027" target="_blank" style="display:block; float: right;">
                    <img style="border:none;" src="cid:logo_sistema" height="60" alt="SEFIP2027" /></a>
            </div>
        </div>

        <hr style="border: 0; height: 3px; width:100%; display:block; background-color:#B38E5D;"/>

        <div style="padding: 10px 0px 30px 0px; font-family:"Open Sans", Helvetica, Arial, sans-serif;color:#333;">
            <h4 style="font-size:1.25rem; font-weight:500;color:#5A472F; margin: 0px 0px 20px  0px; ">Estimado usuario: </h4>

            <p style="margin: 0px 0px 10px 0px; font-size:16px; font-weight:300; text-align:justify;">Ha sido Autorizada su solicitud de registro para ingresar al <b>Sistema Estatal de Finanzas Públicas de Oaxaca</b>.</p>
            <h6 style="margin: 0px 0px 10px 0px;font-size:16px; font-weight:300; color:#111;">Datos de solicitud de
                registro:</h6>
            <ul style="list-style:none; padding:0; margin:0 0 0 15px; font-size:16px; color:#222; ">
                <li style="margin:0 0 10px 0;">Responsable del usuario: <span span style="color:#9D2449;">' . $responsable . '</span></li>
                <li style="margin:0 0 10px 0;">UR: <span span style="color:#9D2449;">' . $Ur . '</span></li>
                <li style="margin:0 0 10px 0;">Usuario: <span span style="color:#9D2449;">' . $user . '</span></li>
                <li style="margin:0 0 10px 0;">Contrase&ntilde;a: <span span style="color:#9D2449;">' . $password . '</span></li>
            </ul>

            <p style="margin: 0px 0px 10px 0px; font-size:16px; font-weight:300; text-align:justify;">Para ingresar al sistema SEFIP2027, de click a la siguiente dirección electrónica: <span
            style="color:#9D2449;"><a href="https://sefip.finanzasoaxaca.gob.mx/2027" target="_blank" >SEFIP2027</a>
            <p style="margin: 0px 0px 10px 0px; font-size:16px; font-weight:300; text-align:justify;">Es necesario
                que realice el cambio de su contrase&ntilde;a, una vez ingresando al sistema, debido a que esta contraseña es temporal para el primer ingreso. </p>

            <p style="margin: 0px 0px 10px 0px; font-size:16px; font-weight:300; text-align: center;">Si Usted no ha
                realizado esta solicitud, haga caso omiso a este correo.</p>
        </div>

        <div
        style="margin:0 0 10px; text-align: center; font-family:arial; font-weight:normal; color:#FFF; font-size: 1rem; padding:10px 20px;  background-color:#9D2449">
        <p style="margin:0; padding:0;  color:rgba(255,255,255, 0.74);">
                "'.APP_EJERCICIO_ANTERIOR.', AÑO DEL BICENTENARIO DEL NATALICIO DE MARGARITA MAZA PARADA, EJEMPLO DE LA DIGNIDAD, LEALTAD Y SERVICIO A LA NACIÓN" </p>
        </div>
        <div
        style="margin:0 auto; font-family:arial;font-weight:normal; color:#9D2449; font-size: 12px; padding:10px 20px; border-bottom:1px solid #B38E5D; background-color:#EFE8DE;">
        <p style="margin:0; padding:0; text-align: center;color:#B38E5D;">
            <b style="color:#9D2449; font-weight: normal;">SEFIP </b> Sistema Estatal de Finanzas Públicas de Oaxaca</p>
        </div>

    </div>
        ';
    }
}

if (isset($_SESSION[_S]['prm']['M']['9']['1']['1'])) {
    new ControladorTicketAdmon();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
