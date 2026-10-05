<?php

/*

TODA la interacción con el navegador del usuario DEBE estar en éste archivo

 */

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class vistaTicket extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        // Las llamadas a métodos deben apuntar al controlador, no a ésta visca
        $this->pantalla = "srv/ControladorTicket.php";
    }

    // Pantalla principal, tanto para solicitudes de Usuario como de Permisos
    public function dibujaPantallaPrincipal($prm, $sql)
    {
        if (isset($prm->act) && $prm->act == "2") {
            // Si viene de PERMISOS, no dibujamos el botón de adicionar usuario
        } else {
            $btnNewTicket = new stdClass();
            $btnNewTicket->id = 'new-ticket';
            $btnNewTicket->icon = 'plus me-2';
            $btnNewTicket->color = 'azul';
            $btnNewTicket->text = 'Solicitud de Usuario';
            $btnNewTicket->type = 'button';
            $btnNewTicket->value = 'Solicitud de Usuario';

            $this->mixScript('
                $("#new-ticket").click(function(){
                    comunica("' . $this->pantalla . '", {metodo:"AddTicket"});
                });
            ');

            $this->agregaTools($this->ponBoton($btnNewTicket));
        }
        // Para los botones regresar, identificamos si estamos hablando de USUARIOS o PERMISOS
        // y seleccionamos el tabulador correcto
        if ((isset($prm->act) && $prm->act == "2") || (isset($prm->esperm) && strtolower($prm->esperm) == "si")) {
            $usr = "";
            $pms = "active";
        } else {
            $usr = "active";
            $pms = "";
        }
        $this->setContenedor("contenedor");
        $this->mixHtml(
            '<div class="navbar navbar-expand-lg navbar-dark bg-primary" id="page-tabs">
                <div class="container-fluid">
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item first ' . $usr . '" aria-current="page">
                                <a href="usr" class="nav-link ' . $usr . '">
                                    Solicitudes de Usuario
                                </a>
                            </li>
                            <li class="nav-item last ' . $pms . '">
                                <a href="per" class="nav-link ' . $pms . ' ">
                                    Solicitudes de Permisos
                                </a>
                                <div class="last"></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>');

        $this->mixHtml('
            <div class="tab-content container">
                <fieldset>
                    <div id="tickets"> </div>
                </fieldset>
            </div>
        ');

        // Está diseñado para trabajar a ventana expandida, asi que la expandimos por default
        // siempre y cuando no esté ya expandida
        $this->mixScript('$("#toggleCA").hide();$("#techos").hide();
            if ($("#mainContainer").hasClass("panel-expand") == true) {
                $(".fa-expand").click();
            }
            $("#contenedor").css("top", "45px").css("z-index", "100");

            $("#page-tabs a").click(function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");

                // Si quieren solicitudes de usuario...
                if (seleccionado.attr("href") == "usr") {
                    comunica("' . $this->pantalla . '",{"metodo":"main"});
                // Si quieren solicitudes de permisos...
                } else {
                    comunica("' . $this->pantalla . '",{"metodo":"updPermisos"});
                }
                e.stopPropagation();
                return false;
            });
        ');

        // En ninguna de éstas funciones "subllamadas" pondrems el returnJson... porque ambas terminan aqui y aquí ya está
        // Si estamos regresando de un botón REGRESAR desde Permisos...
        $esDePermisos = (isset($this->prm->esperm) ? $this->prm->esperm : '');
        $this->tablaSolicitudes($esDePermisos, $sql);
        $this->returnJson();
    }

    // Llenamos la tabla con los datos de las solicitudes
    public function llenaTablaSolicitudes()
    {
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : -1;
        $nav->idTabla = 'tbl-Tik';
        $this->wrLlenaTabla($nav);

        $this->mixScript('$(".tabla-footer").hide();
            $(".seleccionaUser").click( function (){
                if ($(this).attr("tipo") == "Usuario") {
                    comunica("' . $this->pantalla . '",{
                        metodo:"AddTicket",
                        usp_id: $(this).attr("tik_id"),
                        sac_id: $(this).attr("sac_id"),
                        act:1
                    });
                } else if ($(this).attr("tipo") == "Permiso") {
                    comunica("' . $this->pantalla . '",{
                        metodo:"AddTicket",
                        usp_id: $(this).attr("tik_id"),
                        sac_id: $(this).attr("sac_id"),
                        edPerm: "si",
                        act:2
                    });
                }
            });

            $(".clonar").click( function () {
                notify("Puedo escribir los versos más tristes ésta noche...", "success")
            });

            $(".enviar").click( function () {
                sac = $(this).attr("sac_id");
                if ($(this).attr("estado")=="1") { // GENERADO
                    alertify.confirm("<h5>Enviar Solicitud</h5>", "¿Confirma que desea enviar la solicitud No. <strong>" + sac +"</strong> para su autorización?",
                    function(){
                    comunica("' . $this->pantalla . '",{
                            metodo: "enviarSolicitudParaAutorizacion",
                            sac_id: sac
                            });
                    }, function (){}).set("defaultFocus", "cancel").set("labels",{ok: "Aceptar", cancel:"Cancelar"});
                } else {
                    if ($(this).attr("noTicket") != "0" ) {
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
                    } else {
                        notify("Falta Asignar Ticket","error");
                    }
                }
            });
        ');
    }
    // Metodo de un nuevo ticket
    // Registrar una nueva solicitud: de alta de usuario o de modificación de permisos
    // edPem SÓLO VIENE cuando es desde una Solicitud de Permiso... NUNCA vendrá si es desde una solicitud de usuario
    public function muestraPantallaRegistro($abc, $bandera, &$usuarioPrevio, &$solicitudAcceso,
        $grupoSQL, $urSQL, $subSecretariaSQL, $direccionSQL, $coordinadorSQL, $departamentoSQL,
        $nivelJerarquicoSQL, $perfilSQL, $responsableSQL, $directorTitularSQL, &$dataPermisosPadre,
        &$permisos1, &$permisos2, &$permisos3, $fechaInicio, $fechaFinal, $clave) {
        $this->mixScript('
        alertify
                .alert("<h5>Aviso Importante</h5>",
                    "Si no cuenta con un número de ticket, puede dejar este campo vacío.",
                    function(){
                        alertify.message("OK");
                });
        ');
        
        $ticketId = new stdClass();
        $ticketId->id = 'usp_id';
        $ticketId->label = '';
        $ticketId->enDiv = true;
        $ticketId->readonly = true;
        $ticketId->type = 'hidden';

        $sts = new stdClass();
        $sts->id = 'sts_id';
        $sts->label = '';
        $sts->enDiv = true;
        $sts->readonly = true;
        $sts->type = 'hidden';

        $TId = $this->ponInput($ticketId);
        $stat = $this->ponInput($sts);

        $Rfc = new stdClass();
        $Rfc->id = 'rfc';
        $Rfc->class = 'rfc';
        $Rfc->label = 'RFC *';
        $Rfc->class = 'obligatorio delNombre';
        $Rfc->required = true;
        $Rfc->placeholder = 'RFC';
        $Rfc->enDiv = true;
        $Rfc->maxlength = 13; /* total de datos que acepta el campo */
        $Rfc->value = $usuarioPrevio->getRfc();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Rfc->disabled = true;
        }

        $Usuario = new stdClass();
        $Usuario->id = 'usuario';
        $Usuario->class = 'usuario';
        $Usuario->label = 'Usuario *';
        $Usuario->required = true;
        $Usuario->class = 'obligatorio';
        $Usuario->placeholder = 'Nombre de usuario';
        $Usuario->readonly = true;
        $Usuario->enDiv = true;
        $Usuario->maxlength = 20;
        $Usuario->value = $usuarioPrevio->getUsuario();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Usuario->disabled = true;
        }

        $Pass = new stdClass();
        $Pass->id = 'clave';
        $Pass->label = 'Contraseña generada automáticamente';
        $Pass->required = true;
        $Pass->class = 'obligatorio';
        $Pass->value = $usuarioPrevio->getClave();
        $Pass->placeholder = 'LA CONTRASEÑA DEBE SER DE 10 CARACTERES';
        $Pass->readonly = true;
        $Pass->maxlength = 10;
        $Pass->type = 'password';

        $Pass1 = new stdClass();
        $Pass1->id = 'confirmar';
        $Pass1->label = 'Contraseña generada automáticamente*';
        $Pass1->class = 'obligatorio';
        $Pass1->required = true;
        $Pass1->value = $usuarioPrevio->getClave();
        $Pass1->placeholder = 'LA CONTRASEÑA DEBE SER DE 10 CARACTERES';
        $Pass1->readonly = true;
        $Pass1->maxlength = 10;
        $Pass1->type = 'password';

        $Nombre = new stdClass();
        $Nombre->id = 'nombre';
        $Nombre->name = 'nombre';
        $Nombre->label = 'Nombre *';
        $Nombre->class = 'obligatorio delNombre';
        $Nombre->required = true;
        $Nombre->enDiv = true;
        $Nombre->maxlength = 40;
        $Nombre->value = $usuarioPrevio->getNombre();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Nombre->disabled = true;
        }

        $Paterno = new stdClass();
        $Paterno->id = 'paterno';
        $Paterno->name = 'paterno';
        $Paterno->label = 'Apellido Paterno *';
        $Paterno->class = 'obligatorio delNombre';
        $Paterno->required = true;
        $Paterno->enDiv = true;
        $Paterno->maxlength = 25;
        $Paterno->value = $usuarioPrevio->getPaterno();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Paterno->disabled = true;
        }

        $Materno = new stdClass();
        $Materno->id = 'materno';
        $Materno->name = 'materno';
        $Materno->label = 'Apellido Materno *';
        $Materno->class = 'obligatorio delNombre';
        $Materno->required = true;
        $Materno->enDiv = true;
        $Materno->maxlength = 25;
        $Materno->value = $usuarioPrevio->getMaterno();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Materno->disabled = true;
        }

        $Sexo = new stdClass();
        $Sexo->id = 'sexo';
        $Sexo->opciones = array("H" => "Hombre", "M" => "Mujer");
        $Sexo->label = 'Sexo *';
        $Sexo->class = 'obligatorio';
        $Sexo->enDiv = true;
        $Sexo->required = true;
        $Sexo->selecciona = $usuarioPrevio->getSexo();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Sexo->disabled = true;
        }

        $grupo = new stdClass();
        $grupo->sql = $grupoSQL;
        $grupo->id = 'gpo_id';
        $grupo->label = 'Grupo *';
        $grupo->class = 'obligatorio';
        $grupo->required = true;
        $grupo->enDiv = true;
        $grupo->selecciona = $usuarioPrevio->getGpo_id();
        $grupo->detonar = false;
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || ((isset($this->prm->edPerm) && $this->prm->edPerm == "si"))) {
            $grupo->disabled = true;
        }

        $ur = new stdClass();
        $ur->sql = $urSQL;
        $ur->id = 'ur_id';
        $ur->label = 'Ur *';
        $ur->required = true;
        $ur->enDiv = true;
        $ur->selecciona = $usuarioPrevio->getUr_id();
        $ur->detonar = false;
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $ur->disabled = true;
        }

        $Subsecretaria = new stdClass();
        $Subsecretaria->sql = $subSecretariaSQL;
        $Subsecretaria->id = 'ssc_id';
        $Subsecretaria->label = 'Subsecretaría *';
        $Subsecretaria->class = 'obligatorio';
        $Subsecretaria->required = true;
        $Subsecretaria->enDiv = true;
        $Subsecretaria->detonar = false;
        $Subsecretaria->selecciona = $usuarioPrevio->getSsc_id();
        $Subsecretaria->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo:"recargarDireccion",
                    ssc_id:$("#ssc_id").val()
                });
            }';

        $Direccion = new stdClass();
        $Direccion->sql = $direccionSQL;
        $Direccion->id = 'dir_id';
        $Direccion->label = 'Dirección *';
        $Direccion->class = 'obligatorio';
        $Direccion->required = true;
        $Direccion->enDiv = true;
        $Direccion->detonar = false;
        $Direccion->selecciona = $usuarioPrevio->getDir_id();
        $Direccion->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo:"recargarUnidad",
                    dir_id:$("#dir_id").val()
                });
            }';

        $Coordinacion = new stdClass();
        $Coordinacion->sql = $coordinadorSQL;
        $Coordinacion->id = 'uni_id';
        $Coordinacion->label = 'Coordinación *';
        $Coordinacion->class = 'obligatorio';
        $Coordinacion->required = true;
        $Coordinacion->enDiv = true;
        $Coordinacion->detonar = false; //permite ya no ejecutar la funcion change
        $Coordinacion->selecciona = $usuarioPrevio->getUni_id();
        //en esta funcion change lo que estoy mandando es el uni_id de la unidad par cargar el departamento correspondiente en el combo
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] > 5) {
            $Coordinacion->change = '
                if( $(this).val() != "" ){
                comunica("' . $this->pantalla . '",{
                    metodo:"recargarDepartamento",
                        ssc_id:$("#ssc_id").val(),
                        dir_id:$("#dir_id").val(),
                        uni_id:$("#uni_id").val()
                    });
                }'
            ;
        }

        $Departamento = new stdClass();
        $Departamento->sql = $departamentoSQL;
        $Departamento->id = 'dep_id';
        $Departamento->label = 'Departamento *';
        $Departamento->class = 'obligatorio';
        $Departamento->required = true;
        $Departamento->enDiv = true;
        $Departamento->soloText = true;
        $Departamento->selecciona = $usuarioPrevio->getDep_id();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Departamento->disabled = true;
        }
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Departamento->disabled = true;
        }

        $Nivel = new stdClass();
        $Nivel->sql = $nivelJerarquicoSQL;
        $Nivel->id = 'nvj_id';
        $Nivel->label = 'Nivel Jerárquico *';
        $Nivel->class = 'obligatorio';
        $Nivel->required = true;
        $Nivel->enDiv = true;
        $Nivel->soloText = true;
        $Nivel->selecciona = $usuarioPrevio->getNvj_id();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Nivel->disabled = true;
        }

        $UEjecutora = new stdClass();
        $UEjecutora->id = 'ue_id';
        $UEjecutora->name = 'ue_id';
        $UEjecutora->label = 'Unidad Ejecutora *';
        $UEjecutora->class = 'obligatorio';
        $UEjecutora->required = true;
        $UEjecutora->enDiv = true;

        $Perfil = new stdClass();
        $Perfil->sql = $perfilSQL;
        $Perfil->id = 'prf_id';
        $Perfil->label = 'Perfil *';
        $Perfil->class = 'obligatorio';
        $Perfil->required = true;
        $Perfil->enDiv = true;
        $Perfil->soloText = true;
        $Perfil->selecciona = $usuarioPrevio->getPrf_id();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Perfil->disabled = true;
        }

        $Puesto = new stdClass();
        $Puesto->id = 'puesto';
        $Puesto->name = 'puesto';
        $Puesto->label = 'Puesto *';
        $Puesto->class = 'obligatorio';
        $Puesto->placeholder = 'Cargo del usuario';
        $Puesto->required = true;
        $Puesto->enDiv = true;
        $Puesto->value = $usuarioPrevio->getPuesto();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Puesto->disabled = true;
        }

        $Sac_ticket = new stdClass();
        $Sac_ticket->id = 'sac_ticket';
        $Sac_ticket->name = 'sac_ticket';
        $Sac_ticket->label = 'Número de ticket (NO ES OBLIGATORIO)';
        $Sac_ticket->placeholder = 'SDS-XXXXXX';
        $Sac_ticket->title = 'Puede generar un ticket posteriormente';
        $Sac_ticket->enDiv = true;
        $Sac_ticket->maxlength = 10;
        $Sac_ticket->value = $solicitudAcceso->getSac_ticket();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Sac_ticket->disabled = true;
        }

        $Ubicacion = new stdClass();
        $Ubicacion->id = 'titulo';
        $Ubicacion->name = 'titulo';
        $Ubicacion->label = 'Título académico *';
        $Ubicacion->class = 'obligatorio';
        $Ubicacion->placeholder = 'Título académico';
        $Ubicacion->required = true;
        $Ubicacion->enDiv = true;
        $Ubicacion->maxlength = 6;
        $Ubicacion->value = $usuarioPrevio->getTitulo();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Ubicacion->disabled = true;
        }

        $Email = new stdClass();
        $Email->id = 'correo';
        $Email->name = 'correo';
        $Email->label = 'Correo *';
        $Email->class = 'obligatorio';
        $Email->placeholder = 'Correo Institucional';
        $Email->title = 'Ingresar un correo activo';
        $Email->required = true;
        $Email->type = 'email';
        $Email->value = $usuarioPrevio->getCorreo();
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Email->disabled = true;
        }

        $Telefono = new stdClass();
        $Telefono->id = 'telefono';
        $Telefono->name = 'telefono';
        $Telefono->label = 'Teléfono *';
        $Telefono->class = 'obligatorio';
        $Telefono->required = true;
        $Telefono->maxlength = 10;
        $Telefono->formato = 'entero';
        $Telefono->enDiv = true;
        $Telefono->value = $usuarioPrevio->getTelefono();

        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Telefono->disabled = true;
        }

        $Extension = new stdClass();
        $Extension->id = 'extension';
        $Extension->name = 'extension';
        $Extension->label = 'Extensión';
        $Extension->maxlength = 5;
        $Extension->formato = 'entero';
        $Extension->enDiv = true;
        $Extension->value = $usuarioPrevio->getExtension();

        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Extension->disabled = true;
        }

        $Requerimientos = new stdClass();
        $Requerimientos->id = 'requerimiento';
        $Requerimientos->name = 'requerimiento';
        $Requerimientos->label = 'Requerimiento *';
        $Requerimientos->class = 'obligatorio';
        $Requerimientos->required = true;
        $Requerimientos->enDiv = true;
        $Requerimientos->maxlength = 500;
        $Requerimientos->width = '300px';
        $Requerimientos->height = '100px';
        $Requerimientos->text = $solicitudAcceso->getRequerimiento();

        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Requerimientos->disabled = true;
        }

        $Comentarios = new stdClass();
        $Comentarios->id = 'comentario';
        $Comentarios->name = 'comentario';
        $Comentarios->label = 'Comentario';
        $Comentarios->enDiv = true;
        $Comentarios->maxlength = 500;
        $Comentarios->width = '300px';
        $Comentarios->height = '100px';
        $Comentarios->text = $solicitudAcceso->getComentario();

        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $Comentarios->disabled = true;
        }

        $NombreResponsable = new stdClass();
        $NombreResponsable->sql = $responsableSQL;
        $NombreResponsable->id = 'responsable';
        $NombreResponsable->name = 'responsable';
        $NombreResponsable->label = 'Responsable *';
        $NombreResponsable->class = 'obligatorio';
        $NombreResponsable->required = true;
        $NombreResponsable->enDiv = true;
        $NombreResponsable->selecciona = $solicitudAcceso->getJefe();
        $NombreResponsable->detonar = false;
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            if ($usuarioPrevio->getNvj_id() <= 2) {
                $NombreResponsable->disabled = true;
            }
        }

        $NombreJefe = new stdClass();
        $NombreJefe->sql = $directorTitularSQL;
        $NombreJefe->id = 'jefe';
        $NombreJefe->name = 'jefe';
        $NombreJefe->label = 'Jefe que Autoriza *';
        $NombreJefe->enDiv = true;
        $NombreJefe->selecciona = $solicitudAcceso->getResponsable();
        $NombreJefe->detonar = false;
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            if ($usuarioPrevio->getNvj_id() <= 2) {
                $NombreJefe->disabled = true;
            }
        }

        $form = new stdClass();
        $form->id = 'frmticket';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcSolicitud';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        if (isset($this->prm->esperm) && ($bandera == 0) && ($this->prm->esperm == "si")) {
            $form->datosExtra->usp_id = $usuarioPrevio->getUsp_id();
            $form->datosExtra->uur_id = $usuarioPrevio->getUur_id();
            $form->datosExtra->gpo_id = $usuarioPrevio->getGpo_id();
            $form->datosExtra->ur_id = $usuarioPrevio->getUr_id();
            $form->datosExtra->ue_id = $usuarioPrevio->getUe_id();
            $form->datosExtra->nvj_id = $usuarioPrevio->getNvj_id();
            $form->datosExtra->ejs_id = $usuarioPrevio->getEjs_id();
            $form->datosExtra->rfc = $usuarioPrevio->getRfc();
            $form->datosExtra->usuario = $usuarioPrevio->getUsuario();
            $form->datosExtra->clave = $usuarioPrevio->getClave();
            $form->datosExtra->nombre = $usuarioPrevio->getNombre();
            $form->datosExtra->paterno = $usuarioPrevio->getPaterno();
            $form->datosExtra->materno = $usuarioPrevio->getMaterno();
            $form->datosExtra->sexo = $usuarioPrevio->getSexo();
            $form->datosExtra->puesto = $usuarioPrevio->getPuesto();
            $form->datosExtra->titulo = $usuarioPrevio->getTitulo();
            $form->datosExtra->correo = $usuarioPrevio->getCorreo();
            $form->datosExtra->telefono = $usuarioPrevio->getTelefono();
            $form->datosExtra->extension = $usuarioPrevio->getExtension();
            $form->datosExtra->nuevo = $usuarioPrevio->getNuevo();
            $form->datosExtra->usu = $usuarioPrevio->getUsu();
            $form->datosExtra->sac_id = $usuarioPrevio->getSac_id();
            $form->datosExtra->sts_id = $usuarioPrevio->getSts_id();
            $form->datosExtra->prf_id = $usuarioPrevio->getPrf_id();
            $form->datosExtra->sac_ticket = "SDS-XXXXXX";
            $form->datosExtra->requerimiento = $solicitudAcceso->getRequerimiento();
            $form->datosExtra->comentario = $solicitudAcceso->getComentario();

            /* if ($form->datosExtra->eso_id == '') {
                $form->datosExtra->prf_id = '0';
            } else {
                $form->datosExtra->prf_id = $usuarioPrevio->getPrf_id();
            } */

            $form->datosExtra->esPermiso = "si";
            $form->datosExtra->jefe = '"{datosJefe()}"';
            $form->datosExtra->responsable = '"{datosResponsable()}"';
            $form->datosExtra->eso_id = "1";
        } elseif ($bandera == 0) {
            $form->datosExtra->jefe = '"{datosJefe()}"';
            $form->datosExtra->responsable = '"{datosResponsable()}"';
        } elseif ($bandera == 1) {
            $form->datosExtra->sac_id = $solicitudAcceso->getSac_id();
            $form->datosExtra->usp_id = $usuarioPrevio->getUsp_id();
            $form->datosExtra->eso_id = $solicitudAcceso->getEso_id();
            $form->datosExtra->sefip = $solicitudAcceso->getSefip();
            $form->datosExtra->nuevo = $usuarioPrevio->getNuevo();
            $form->datosExtra->usu = $usuarioPrevio->getUsu();
            if (isset($this->prm->edPerm) && $this->prm->edPerm == "si") {
                $form->datosExtra->esPermiso = "si";
                $form->datosExtra->nvj_id = $usuarioPrevio->getNvj_id();
            }
        }
        // Para determinar si activamos a no campos, cuando se dibujan en html
        if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $esDisabled = "disabled";
        } else {
            $esDisabled = "";
        }

        $form->datosExtra->permisos = '"{objetoMeta()}"';
        $form->datosExtra->errorCorreo = '"{errorCorreo()}"';
        $form->datosExtra->errorTelefono = '"{errorTelefono()}"';
        $form = new form($form);

        $btnCancelarU = new stdClass();
        $btnCancelarU->value = 'Regresar';
        $btnCancelarU->color = 'gris';
        $btnCancelarU->id = 'cancel-ticket';
        $btnCancelarU->icon = 'circle-left me-2';

        if (isset($tik_id)) {
            $btnGuardarU = new stdClass();
            $btnGuardarU->id = 'save-ticket';
            $btnGuardarU->value = 'Guardar';
            $btnGuardarU->icon = 'floppy-disk me-2';
            $btnGuardarU->color = 'verde';
            $btnGuardarU->text = '';
            $btnGuardarU->form = 'frmticket';
            $btnGuardarU->type = 'submit';
            $btnGuardarU->class = "me-1";
        } else {
            $btnGuardarU = '';
            $btnGuardarU = new stdClass();
            $btnGuardarU->id = 'save-ticket';
            $btnGuardarU->value = 'Guardar';
            $btnGuardarU->color = 'verde';
            $btnGuardarU->icon = 'floppy-disk me-2';
            $btnGuardarU->text = 'Guardar';
            $btnGuardarU->form = 'frmticket';
            $btnGuardarU->type = 'submit';
            $btnGuardarU->class = "me-1";
        }

        $btnReset = new stdClass();
        $btnReset->value = 'Limpiar';
        $btnReset->color = 'amarillo';
        $btnReset->type = 'reset';
        $btnReset->id = 'reset-ticket';
        $btnReset->form = 'frmticket';
        $acu = '';
        if (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] > 2) {
            $acu .= '<div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Subsecretaria) . '
                        </fieldset>
                        <fieldset class="col-md-6" id="Direccion">
                            ' . $this->ponCombo($Direccion) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Coordinacion) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($Departamento) . '
                        </fieldset>
                    </div>';
        }
        $tituloHtml = '';
        $contraHtml = '';
        if ((isset($this->prm->esperm) && $this->prm->esperm = "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
            $tituloHtml = "<h3>Solicitud de Permiso(s)</h3>";
        } else {
            $tituloHtml = "<h3>Solicitud de Alta de Usuario</h3>";
            $contraHtml = '
                        <div class="row">
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($Pass) . '
                            </fieldset>
                            <fieldset class="col-md-6">
                                ' . $this->ponInput($Pass1) . '
                            </fieldset>
                        </div>';
        }
        $form->mixHtml('
            <div class="tab-content container">
                <div class="tab-pane active mb-4" id="addTicket1" role="tabpanel" aria-labelledby="addTicket1-tab">
                    <div class="row">
                        <legend>
                            ' . $tituloHtml . '
                        </legend>
                    </div>' . $TId . $stat . '
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Email) . '
                        </fieldset>
                    </div>
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Sac_ticket) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponInput($Ubicacion) . '
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
                            ' . $this->ponInput($Materno) .
                        '</fieldset>
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
                                <div id="date-fechaInicio" class="input-group date" data-date="" data-date-format="dd/mm/yyyy">
                                    <input class="form-control datepicker form-group fecha" id="fechaInicio" readonly="1"  name="fechaInicio" value=".date("d/m/Y")."  type="text">
                                    <span class="input-group-btn "><button class="btn btn-default" tabindex="-1" type="button"><span class="fa fa-calendar"></span></button></span>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="col-md-3">
                            <div class="form-group ">
                                <label class="control-label" for="fechaFin">
                                    Vigente hasta:
                                </label>
                                <div id="date-fechaFin" class="input-group date" data-date="" data-date-format="dd/mm/yyyy">
                                    <input class="form-control datepicker form-group fecha" readonly="1" id="fechaFin" name="fechaFin" value="31/12/'.APP_EJERCICIO.'" type="text">
                                    <span class="input-group-btn "><button class="btn btn-default" tabindex="-1" type="button"><span class="fa fa-calendar"></span></button></span>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    ' . $contraHtml . '
                    <div class="row">
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($NombreResponsable) . '
                        </fieldset>
                        <fieldset class="col-md-6">
                            ' . $this->ponCombo($NombreJefe) . '
                        </fieldset>
                    </div>
                </div> ');

        $form->mixHtml('<div class="tab-pane" id="addTicket2" role="tabpanel" aria-labelledby="addTicket2-tab">
                    <div class="row align-items-start">
                    <div class="mb-4">
                        <h3 align="left mb-4">Permisos de usuario</h3>
                        <div class="mb-4">
                            <button class="btn btn-success" id="permisosOn"><i class="fa fa-check-circle me-2"></i>Marcar Todo</button>
                            <button class="btn btn-warning" id="permisosOff"><i class="fa fa-trash me-2"></i>Desmarcar Todo</button>
                        </div>
                    </div>
                    ');

        $h = '<ul id="browser" class="filetree mb-4">';
        $permisosTemporal = '';

        // Marcos: 05/jul/2018
        // Si es una nueva SOLICITUD DE PERMISOS, y el hijo ya tiene todos los permisos del padre
        // Mostramos un mensaje de que la solicitud no procede
        //Castro Jordy 15-03-18 Muestra los elementos de los permisos
        $sis = '';
        $men = '';
        $opc = '';
        foreach ($dataPermisosPadre as $w) {
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
            $tchk = '';
            //Jordy Castro 20-03-18 se checan los permisos ya existentes anteriormente en la solicitud y se muestran en el formulario
            $usPer = "";

            // Si viene desde una solicitud de Usuario ... y es una modificación...
            if ((isset($this->prm->usp_id) && $bandera == 1)) {
                for ($i = 0; $i < count($permisos1); $i++) {
                    if (($permisos1[$i]['sis_id'] == $w['sis_id']) && ($permisos1[$i]['men_id'] == $w['men_id']) &&
                        ($permisos1[$i]['opc_id'] == $w['opc_id']) && ($permisos1[$i]['tar_id'] == $w['tar_id'])) {
                        $tchk = 'checked="checked"';
                        // Se selecciona los permismos y se agregan los permisoss
                        $usPer = $permisos1[$i]['sis_id'] . ":" . $permisos1[$i]['men_id'] . ":" . $permisos1[$i]['opc_id'] . ":" . $permisos1[$i]['tar_id'];
                    }
                }
            }
            // Si es una Solicitud de Permisos
            // O estamos modificando los permisos de una Solicitud de Usuario
            if ((isset($this->prm->esperm) && $this->prm->esperm == "si") || (isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
                // Marcos: 05/jul/2018
                // Para no meter ruido al código (que aún no lo entiendo del todo) dejaremos tal cual
                // cuando sea una MODIFICACIÓN de permisos en una solicitud de Usuario
                if ((isset($this->prm->edPerm) && $this->prm->edPerm == "si")) {
                    $aux = "no";
                    for ($i = 0; $i < count($permisos3); $i++) {
                        if (($permisos3[$i]['sis_id'] == $w['sis_id']) && ($permisos3[$i]['men_id'] == $w['men_id']) &&
                            ($permisos3[$i]['opc_id'] == $w['opc_id']) && ($permisos3[$i]['tar_id'] == $w['tar_id'])) {
                            // Se selecciona los permismos y se agregan los permisoss
                            $aux == "si";
                        }
                    }
                    if ($aux == "no") {
                        $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' . $w['tar_nombre'] . '</span></li>';
                    }
                    $aux = "";
                    // Marcos: 05/jul/2018
                    // Y aquí va el código que usaremos cuando se trate de una nueva Solicitud de Permisos
                } else {
                    // En $permisos3 están los permisos del usuario que está solicitando
                    // nuevos permisos
                    $bolYaExiste = false;
                    for ($i = 0; $i < count($permisos3); $i++) {
                        // Si el WHILE para TODOS LOS PERMISOS DEL PADRE es igual a uno de los permisos
                        // que AÚN NO TIENE el hijo, lo dibujamos
                        if (($permisos3[$i]['sis_id'] == $w['sis_id']) && ($permisos3[$i]['men_id'] == $w['men_id']) && ($permisos3[$i]['opc_id'] == $w['opc_id']) && ($permisos3[$i]['tar_id'] == $w['tar_id'])) {
                            $tchk = 'checked="checked"';
                            $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' ." ". $w['tar_nombre'] . '</span></li>';
                            $bolYaExiste = true;
                        }
                    }
                    if ($bolYaExiste == false) {
                        $tchk = '';
                        $h .= '<li><span class="file" style="color:red"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] ." ". '" />' . $w['tar_nombre'] . '</span></li>';
                    }
                }
            } else {
                $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' ." ". $w['tar_nombre'] . '</span></li>';
            }

            $this->mixScript('
                var prm="";
                var usr_permisos = new Array();
                usrper = "";
                //Castro Jordy 14/05/2018 agrega a los permisos a la variable
                $("#tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '").click(function(){
                    // Si está marcado, lo adicionamos a la cadena
                    if ($(this).is(":checked")) {
                        usrper += $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id") + ";";
                    // Si no, lo quitamos de la cadena (por si existe)
                    } else {
                        usp = $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id")+";";
                        usrper = usrper.replace(usp,"");
                    }
                });
            ');
        }

        // Marcos: 07/jun/2018
        // Si bandera == 1 (que sospecho es un cambio) ponemos los permisos en la variable javascript
        // Marcos: 31/oct/2018
        // Bandera 0 es una alta de solicitud de cambios, por lo que también en éste caso armamos la cadena
        if (($bandera == 0) || ($bandera == 1)) {
            $this->mixScript('
                usrper="";
                $(".addPermiso").each(function(){
                    if( $(this).is(":checked") ) {
                        usrper += $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id") + ";";
                    }
                });
            ');
        }

        // Marcos: 08/jun/2018
        // Para mostrarles visualmente que el campo es obligatorio, lo pintamos de rojo al abandonarlo
        // y detectar que está vacío
        $this->mixScript('
            $("#date-fechaInicio").datepicker({"keyboardNavigation":true,"language":"es"}).on("changeDate", function(e){});
            $("#date-fechaFin").datepicker({"keyboardNavigation":true,"language":"es"}).on("changeDate", function(e){});

            $("#contenedor").css("top", "105px").css("z-index", "100");

            $(".obligatorio").blur( function (e){
                e.preventDefault();
                if ( $(this).val().trim() == "" ) {
                    $(this).addClass("error-notification");
                } else {
                    $(this).removeClass("error-notification");
                }
                e.stopPropagation();
            });
            function objetoMeta(){
                var stringObj = "";
                stringObj += usrper.substring(0,(usrper.length - 1))
                return stringObj;
            }

            $("#fechaInicio").val("' . $fechaInicio . '");
            $("#fechaFin").val("' . $fechaFinal . '");

            $("#clave").val("' . $clave . '");
            $("#confirmar").val("' . $clave . '");
        ');

        // Marcos: 08/jun/2018
        // Validamos si ya existe combinando NOMBRE + PATERNO + MATERNO -> RFC
        // Las reglas de negocio están como comentarios en la función que valida
        $this->mixScript('
            $(".delNombre").blur( function (e){
                e.preventDefault();
                var strPasoNombre = $("#nombre").val().trim().toUpperCase();
                var strPasoPaterno = $("#paterno").val().trim().toUpperCase();
                var strPasoMaterno = $("#materno").val().trim().toUpperCase();
                var strPasoRFC = $("#rfc").val().trim().toUpperCase();
                // Cómo todos éstos campos son obligatorios, sólo validamos cuando todos tengan valores
                /*
                if ( strPasoNombre != "" && strPasoPaterno != "" && strPasoMaterno != "" && strPasoRFC != "" ) {
                    comunica("' . $this->pantalla . '", {
                        metodo: "validaNuevoUsuario",
                        nombre: strPasoNombre,
                        paterno: strPasoPaterno,
                        materno: strPasoMaterno,
                        rfc: strPasoRFC
                    });
                }
                */
                e.stopPropagation();
            });
        ');

        // Marcos: 07/jun/2018
        // Para marcar/desmarcar todo
        $this->mixScript('
            $("#permisosOn").click(  function (e){
                e.preventDefault();
                $(".addPermiso").prop("checked", true);
                // Llenamos la variable que almacena los permisos
                usrper="";
                $(".addPermiso").each(function(){
                    // Si está marcado, lo adicionamos a la cadena
                    if ($(this).is(":checked")) {
                        usrper += $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id") + ";";
                    // Si no, lo quitamos de la cadena (por si existe)
                    } else {
                        usp = $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id")+";";
                        usrper = usrper.replace(usp,"");
                    }
                });
                e.stopPropagation();
            });
            $("#permisosOff").click( function (e){
                e.preventDefault();
                $(".addPermiso").prop("checked", false );
                // limpiamos la variable que almacena los permisos
                usrper = "";
                e.stopPropagation();
            });
        ');

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

        // script si es registro nuevo recarga jefe
        $script = '';
        $script .= '
            function datosJefe(){
                if ($("#jefe").val() != "") {
                    return $("#jefe").val();
                }
                return "";
            }
            function datosResponsable(){
                if ($("#responsable").val() != "") {
                    return $("#responsable").val();
                }
                return "";
            }
        ';

        $this->agregaTools($this->ponBoton($btnCancelarU) . ' ' . $this->ponBoton($btnGuardarU));

        // si es edicion agregamos el btn eliminar
        if (isset($_SESSION[_S]['nvj_id']) && $bandera != 0) {
            // boton para eliminar la solicitud de permiso
            $btnEliminar = new stdClass();
            $btnEliminar->id = 'delete-ticket';
            $btnEliminar->value = 'Eliminar';
            $btnEliminar->icon = 'trash me-2';
            $btnEliminar->text = 'Eliminar';
            $btnEliminar->class = 'me-1';
            $btnEliminar->color = 'rojo';
            $btnEliminar->type = 'button';
            // evento al dar click en el btn
            $script .= '
                $("#delete-ticket").click( function (){
                    alertify.confirm("<h5>Eliminar Solicitud</h5>", "¿Esta seguro que desea eliminar está solicitud?",
                        function(){
                        comunica("' . $this->pantalla . '", {
                                    metodo: "eliminaSolicitud",
                                    usp_id: "' . $usuarioPrevio->getUsp_id() . '",
                                    sac_id: "' . $solicitudAcceso->getSac_id() . '"
                                });
                        }, function (){}).set("defaultFocus", "cancel").set("labels",{ok: "Aceptar", cancel:"Cancelar"});
                });
            ';
            $this->agregaTools($this->ponBoton($btnEliminar));
        }

        // Marcos: 12/jun/2018
        // Ponemos los tabs en la seccion de botones, para que el desplazamiento de pantalla no los afecte
        $tabs = '<ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class=" nav-item active liT" role="presentation">
                        <button class="nav-link active" id="addTicket1-tab" data-bs-toggle="tab" data-bs-target="#addTicket1" type="button" role="tab" aria-selected="true">
                            Datos Generales
                        </button>
                    </li>
                    <li class="liT2">
                        <button class="nav-link" id="addTicket2-tab" data-bs-toggle="tab" data-bs-target="#addTicket2" type="button" role="tab" aria-selected="true">                       
                            Permisos
                        </button>
                    </li>
                </ul>';

        // Marcos: 07/junio/2018
        // El botón LIMPIAR lo separamos, ya que por error lo pulse y limpie todos mis campos en más de
        // una ocasión
        $this->agregaTools('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
            '<br />' . $tabs
        );

        $this->mixScript($script);
        $this->mixHtml($form->ponForm());

        $dominiosPermitidos = [];
        $sql = 'SELECT dmi_dominio, dmi_activo from '.BD_SINPRES.'.cat_DominioInstitucional
        WHERE dmi_activo = ?;';
        $param = [1];
        $this->conectar();
        $this->setSql($sql);
        $this->consultar($param);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            while ($data = $this->extraer()) 
            {
                $dominiosPermitidos[] = $data['dmi_dominio'];
            }
        }

        $this->mixScript('
            $("select#nvj_id").on("change", function(){
                valor = $(this).val();
                if (valor == 1) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"rellenarUnidadEjecutora",
                        ue_id:"' . $usuarioPrevio->getUe_id() . '",
                        agregar:"si",
                        esPerm:"' . (isset($this->prm->esperm) ? 'si' : 'no') . '",
                        gpo_id:"' . $usuarioPrevio->getGpo_id() . '",
                        ur_id:"' . $usuarioPrevio->getUr_id() . '"
                    })
                    $("#idUnidadEjecutora").show();
                } else {
                    $("select#ue_id").val("");
                    $("#ue_id").removeAttr("required");
                    $("#idUnidadEjecutora").hide();
                }
            });
            $("select#nvj_id").hide("change", function(){
                valor = $(this).val();
                if (valor == 1) {
                    comunica("' . $this->pantalla . '",{
                        metodo:"rellenarUnidadEjecutora",
                        ue_id:"' . $usuarioPrevio->getUe_id() . '",
                        agregar:"si",
                        esPerm:"' . (isset($this->prm->esperm) ? 'si' : 'no') . '",
                        gpo_id:"' . $usuarioPrevio->getGpo_id() . '",
                        ur_id:"' . $usuarioPrevio->getUr_id() . '"
                    })
                    $("#idUnidadEjecutora").show();
                } else {
                    $("select#ue_id").val("");
                    $("#ue_id").removeAttr("required");
                    $("#idUnidadEjecutora").hide();

                }
            });
            $("#cancel-ticket").click(function(){
            ' . ((isset($this->prm->edPerm) && $this->prm->edPerm == "si") ? '
                callbackComunica = function(){$(".li3").click();}
                comunica("' . $this->pantalla . '",{
                    metodo:"updPermisos",
                });
            ' : '
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    act:' . (isset($this->prm->act) && $this->prm->act != "" ? $this->prm->act : '1') . ',
                    esperm: "' . (isset($this->prm->edPerm) && $this->prm->edPerm != "" ? $this->prm->edPerm : '') . '"
                });
                return false;
            ') . '
            });
            $("#browser").treeview({
                animated:"normal",
                collapsed: false
            });

            // Castro Jordy 14-03-18 se checa que en estos campos sean los caracteres necesarios para el campo

                $("#sac_ticket").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    if ( validateTicket( valor ) ) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                    }
                }
                });

                // Para la validación del ticket
                function validateTicket(Nticket) {
                    var re = /^[A-Za-z]{3}-(?!.*000)\d{6}$/;
                    ///^[A-Za-z]{3}-\d{6}$/;
                    return re.test(Nticket);
                }

            $("#paterno,#materno").keypress(function(e) {
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum==8){ // el borrado
                    return true;
                } else if (keynum==9){ // TABULADOR
                    return true;
                }else if (keynum==95){ // se denega el guion bajo
                    return false;
                }else if (keynum==241 || keynum==209){ // las enie
                    return true;
                }else if (keynum>=48 && keynum<=57){ // que no sean numeros
                    return false;
                // Marcos: 02/jul/2018 -> permitimos acentos : áéíóúÁÉÍÓÚ
                } else if ( keynum == 160 || keynum == 130 || keynum == 161 || keynum == 162 || keynum == 163 || keynum == 181 || keynum == 144 || keynum == 214 || keynum == 224 || keynum == 233 ) {
                    return false;
                }

                if(/\w/.test(String.fromCharCode(keynum))){
                    return true;
                }else{
                    return false;
                }
            });

                $("#paterno,#materno").on("paste", function (e) {
                    e.preventDefault();
                    $("#errorApellido").text("No está permitido pegar texto en este campo.");
                });

            $("#nombre").keypress(function(e) {
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum==32){//espacios
                    var miniBand = $("#nombre").val();
                    if (miniBand.indexOf(" ") == -1) {
                        return true;
                    } else {
                        return false;
                    }
                } else if (keynum==8) { // el borrado
                    return true;
                } else if (keynum==9) { // TABULADOR
                    return true;
                } else if (keynum==95) { // se denega el guion bajo
                    return false;
                } else if (keynum==241 || keynum==209) { // las enie
                    return true;
                } else if (keynum>=48 && keynum<=57) { // que no sean numeros
                    return false;
                // Marcos: 02/jul/2018 -> permitimos acentos : áéíóúÁÉÍÓÚ
                } else if (keynum == 160 || keynum == 130 || keynum == 161 || keynum == 162 || keynum == 163 || keynum == 181 || keynum == 144 || keynum == 214 || keynum == 224 || keynum == 233) {
                    return false;
                }

                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            // Para la validación del rfc ( son válidos a 10 o 13 posiciones )
            function validateRFC(rfc) {
                if (rfc.trim().length == 13) {
                    var re = /^([A-ZÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])([A-Z]|[0-9]){2}([A]|[0-9]){1})?$/;
                } else {
                    var re = /^([A-ZÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1]))?$/;
                }
                return re.test(rfc);
            }

            // Validamos el RFC
            $("#rfc").blur(function(){
                valor = $(this).val().toUpperCase().trim();
                if (valor != "") {
                    if (validateRFC( valor )) {
                        $(this).css("color", "green");
                    } else {
                        $(this).css("color", "red");
                        notify("El RFC no es válido (Ejemplos válidos: CADA830401, CADA830401CS1)", "error");
                        $("#rfc").val("");
                        $(this).focus();
                        setTimeout(function() { $( "#rfc" ).focus(); }, 300 );
                    }
                }
            });

            $("#rfc,#clave,#confirmar").keypress(function(e) {
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum == 8) {
                    return true;
                } else if (keynum==9) {  // tabulador
                    return true;
                } else if (keynum==95) {
                    return false;
                } else if (keynum==241 || keynum==209) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            $("#titulo").keypress(function(e) {
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum==32) {//espacios
                    return true;
                } else if (keynum==8) { // el borrado
                    return true;
                } else if (keynum==9) { // TABULADOR
                    return true;
                } else if (keynum==95) { // se denega el guion bajo
                    return false;
                } else if (keynum==241 || keynum==209) { // las enie
                    return true;
                } else if (keynum>=48 && keynum<=57) { // que no sean numeros
                    return false;
                } else if (keynum==46) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            // Marcos: 11/jul/2018
            // reporta Carmen que una coma hace pelotas el controlador, así que adicionamos validación al correo
            // Validamos el email
            let bandera = true;
            $("#correo").blur(function(){
                valor = $(this).val().trim();
                if ( valor != "" ) {
                    let dominiosPermitidos = ' . json_encode($dominiosPermitidos) . ';
                    let dominiosExcluidos = ["gmail", "hotmail", "yahoo", "outlook","icloud"];
                    const correo = valor;
                    let dominio = correo.split("@")[1]?.toLowerCase() ?? "";

                    if (/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+(\.[a-zA-Z]{2,})+$/.test(correo) || dominiosExcluidos.some(d => dominio.includes(d.toLowerCase())))
                    {   
                        dominio = correo.split("@")[1].toLowerCase();
                        if (dominiosPermitidos.some(d => dominio.endsWith(d.toLowerCase())) || dominiosPermitidos.some(d => dominio === d.toLowerCase()))
                        {   
                            $(this).css("color", "green");
                            $.ajax({
                                data : { "metodo": "validaNuevoCorreo", "correo": correo},
                                url:  "' . $this->pantalla . '",
                                type: "post",
                                success: function ( response ) {
                                    var json = $.parseJSON(response);
                                    if ( json.bandera == 1 ) {
                                        bandera = false;
                                        notify("El correo electrónico está asociado a más de un usuario, regularice la información para poder continuar","error");
                                        $(this).css("color", "red");
                                        $("#correo").focus();
                                        comunica("' . $this->pantalla . '",{"metodo":"main"});
                                    } else if ( json.bandera == 2 ) {
                                        bandera = false;
                                        notify("El número de teléfono está asociado a más de un usuario, regularice la información para poder continuar","error");
                                        $(this).css("color", "red");
                                        $("#correo").focus();
                                        comunica("' . $this->pantalla . '",{"metodo":"main"});
                                    } else if ( json.bandera == 3 ) {
                                        //No es posible precargar los datos asociados al usuario
                                        bandera = true;
                                    } else {                                        
                                        bandera = true;
                                        if(json.usu > 0) {
                                            $.ajax({
                                                data : { "metodo": "dameDatosUsuario", "usr": json.usu},
                                                url:  "' . $this->pantalla . '",
                                                type: "post",
                                                success: function ( response ) {
                                                    var json = $.parseJSON(response);
                                                    $("#correo").prop("readonly", true);
                                                    $("#titulo").val(json.usr_titulo);
                                                    $("#titulo").removeClass("error-notification");
                                                    $("#titulo").prop("readonly", true);
                                                    $("#nombre").val(json.usr_nombre);
                                                    $("#nombre").prop("readonly", true);
                                                    $("#paterno").val(json.usr_paterno);
                                                    $("#paterno").prop("readonly", true);
                                                    $("#materno").val(json.usr_materno);
                                                    $("#materno").prop("readonly", true);
                                                    $("#sexo").val(json.usr_sexo[0]).selectpicker("refresh");
                                                    bloquearCombo("sexo");
                                                    $("#nvj_id").val(json.nvj_id).selectpicker("refresh");
                                                    bloquearCombo("nvj_id");
                                                    if (json.nvj_id == 1) {
                                                        comunica("' . $this->pantalla . '",{
                                                            metodo:"rellenarUnidadEjecutora",
                                                            ue_id:"' . $usuarioPrevio->getUe_id() . '",
                                                            agregar:"si",
                                                            esPerm:"' . (isset($this->prm->esperm) ? 'si' : 'no') . '",
                                                            gpo_id:"' . $usuarioPrevio->getGpo_id() . '",
                                                            ur_id:"' . $usuarioPrevio->getUr_id() . '"
                                                        })
                                                        $("#idUnidadEjecutora").show();
                                                    } else {
                                                        $("select#ue_id").val("");
                                                        $("#ue_id").removeAttr("required");
                                                        $("#idUnidadEjecutora").hide();
                                                    }
                                                    $("#prf_id").val(json.prf_id).selectpicker("refresh");
                                                    bloquearCombo("prf_id");
                                                    $("#puesto").val(json.usr_puesto);
                                                    $("#puesto").prop("readonly", true);
                                                    $("#telefono").val(json.usr_telefono);
                                                    $("#telefono").prop("readonly", true);
                                                    $("#extension").val(json.usr_extension);
                                                    $("#extension").prop("readonly", true);
                                                    $("#rfc").val(json.usr_rfc);
                                                    $("#rfc").prop("readonly", true);

                                                    var nombre = json.usr_nombre;
                                                    var paterno = json.usr_paterno;
                                                    var materno = json.usr_materno;
                                                    var res = nombre.substr(0, 1);
                                                    var res2 = materno.substr(0, 1);
                                                    var paterno = paterno.substr(0,18);
                                                    var paterno = paterno.replaceAll(" ","");
                                                    document.getElementById("usuario").value = res+paterno.trim()+res2;

                                                    function bloquearCombo(idCombo) {
                                                        let valorSeleccionado = $("#" + idCombo).val();
                                                        let textoSeleccionado = $("#" + idCombo + " option:selected").text();
                                                        $("#" + idCombo).empty().append(
                                                            `<option value="${valorSeleccionado}">
                                                                ${textoSeleccionado}
                                                            </option>`
                                                        );
                                                        $("#" + idCombo).selectpicker("refresh");
                                                        $("#" + idCombo).css({
                                                            "pointer-events": "none",
                                                            "background-color": "#e9ecef"
                                                        });
                                                    }   
                                                }
                                            });

                                        }
                                    }
                                }
                            });

                        }else{
                            $(this).css("color", "red");
                            notify("Favor de agregar un correo válido y/o institucional", "error");
                            bandera = false;

                        }
                    }else {
                        $(this).css("color", "red");
                        notify("Favor de agregar un correo válido y/o institucional", "error");
                        bandera = false;
                    }
                }
            });

            //Validamos el telefono
            let telValido = true;
            $("#telefono").blur(function(){
                telefono = $(this).val().trim();
                const numeroLimpio = telefono.replace(/\D/g, "");
                const telefonoValido =/^\d{10}$/.test(numeroLimpio)
                && !/^(\d)\1{9}$/.test(numeroLimpio)
                && numeroLimpio[0] !== "0"
                && !/(.)\1{4,}/.test(numeroLimpio)
                && numeroLimpio[0] !== "1";

                if (!telefonoValido) {
                    telValido = false;
                    $("#telefono").css("border-color", "red");
                    notify("Se aceptan únicamente números y debe tener exactamente 10 dígitos.", "error");
                }else {
                    //Verificamos si el telefono ya esta registrado
                    $.ajax({
                        data : { "metodo": "validaNuevoTelefono", "telefono": telefono},
                        url:  "' . $this->pantalla . '",
                        type: "post",
                        success: function ( response ) {
                            var json = $.parseJSON(response);
                            if ( json == 1 ) {
                                telValido = false;
                                notify("El número de teléfono está asociado a más de un usuario, regularice la información para poder continuar","error");
                                 $("#telefono").css("border-color", "red");
                                $("#correo").focus();
                            } else {
                                telValido = true;
                            }
                        }
                    });
                    $("#telefono").css("border-color", "green");
                }
            });

            function errorCorreo(){
                return bandera;
            }

            function errorTelefono(){
                return telValido;
            }

            $("#telefono,extension").keypress(function(e){
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum == 8) {
                    return true;
                } else if (keynum==9) { // tabulador
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

            $("#puesto").keypress(function(e) {
                var keynum = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if (keynum==32) {//espacios
                    return true;
                } else if (keynum==8) { // el borrado
                    return true;
                } else if (keynum==9) { // tabulador
                    return true;
                } else if (keynum==95) { // se denega el guion bajo
                    return false;
                } else if (keynum==241 || keynum==209) { // las enie
                    return true;
                } else if (keynum>=48 && keynum<=57) { // que no sean numeros
                    return false;
                } else if (keynum==46) {
                    return true;
                }
                if (/\w/.test(String.fromCharCode(keynum))) {
                    return true;
                } else {
                    return false;
                }
            });

            $("#requerimientos, #comentarios").keypress(function(event){
                tecla = event.which ? event.which : event.which ? event.which : event.charCode;
                patron = /[0-9\A-Za-zñÑ.,:"" \u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00f1\u00d1\u00FC\u00DC\u0008\u0009]+$/;
                caracter = String.fromCharCode(tecla);
                return patron.test(caracter);
            });

            // Castro Jordy 16-03-18 se checan las contraseñas que no esten vacias
            $("#clave").blur(function(){
                if ($("#clave").val() == "") {
                    notify("La contraseña esta vacia","warning");
                    return false;
                }
            });
            $("#clave").change(function(){
                if ($("#clave").val().length < 10) {
                    notify("La contraseña no cumple con la longitud mínima 10","warning");
                    return false;
                }
            });

            $("#confirmar").blur(function(){
                if ($("#confirmar").val() == "") {
                    notify("La contraseña esta vacia","warning");
                    return false;
                }
            });
            $("#confirmar").change(function(){
                if ($("#confirmar").val().length < 10) {
                    notify("La contraseña no cumple con la longitud mínima","warning");
                    return false;
                }
            });

            // Reemplazamos de una cadena las vocales acentuadas por vocales normales
            function quitaAcentos( strCadena ) {

                strCadena = strCadena.replace( "á", "a");
                strCadena = strCadena.replace( "é", "e");
                strCadena = strCadena.replace( "í", "i");
                strCadena = strCadena.replace( "ó", "o");
                strCadena = strCadena.replace( "ú", "u");
                strCadena = strCadena.replace( "Á", "A");
                strCadena = strCadena.replace( "É", "E");
                strCadena = strCadena.replace( "Í", "I");
                strCadena = strCadena.replace( "Ó", "O");
                strCadena = strCadena.replace( "Ú", "U");

                return strCadena;
            }

            $(document).ready(function(){
                $("#nombre,#paterno,#materno").keyup(function(){
                    var nombre = quitaAcentos( document.getElementById("nombre").value );
                    var paterno = quitaAcentos( document.getElementById("paterno").value );
                    var materno = quitaAcentos( document.getElementById("materno").value );
                    var res = nombre.substr(0, 1);
                    var res2 = materno.substr(0, 1);
                    var paterno = paterno.substr(0,18);
                    var paterno = paterno.replaceAll(" ","");
                    document.getElementById("usuario").value = res+paterno.trim()+res2;
                });

            });

            $("#nombre,#paterno,#materno,#rfc").blur(function(){
                var nombre = document.getElementById("nombre").value;
                var paterno = document.getElementById("paterno").value;
                var materno = document.getElementById("materno").value;
                var rfc = document.getElementById("rfc").value;

                if($(this).val()!=""){

                    $.ajax({
                        data : { "metodo": "valNombre", "nom": nombre, "ap": paterno, "am": materno, "rfc": rfc},
                        url:  "' . $this->pantalla . '",
                        type: "post",
                        success: function ( response ) {
                            var json = $.parseJSON(response);
                            if ( json == 1 ) {
                                notify("El registro ya existe","warning");
                                $("#usr_usuario").focus();
                            }
                        }
                    });
                }
            });

            //Se cheque si el nombre ya existe de usuario ya existe
            $("#materno,#paterno,#nombre,#rfc").blur(function(){
                var user = document.getElementById("usuario").value;
                if (user != "") {
                    $.ajax({
                        data : { "metodo": "validaDatosUsuario", "usp_usuario": user, abc:"' . $abc . '" },
                        url:  "' . $this->pantalla . '",
                        type: "post",
                        success: function ( response ) {
                            var json = $.parseJSON(response);
                            if ( json == 1 ) {
                                notify("El nombre de usuario ya existe, solicite permisos adicionales","warning");
                                $("#usr_usuario").focus();
                                comunica("' . $this->pantalla . '",{"metodo":"updPermisos"});
                            }
                        }
                    });

                }
            });

            setTimeout( function() { $( "#titulo" ).focus() }, 100 );
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Si al guardar los datos de una solicitud, detectamos que no definieron ningún permiso
    // avisamos
    public function avisoFaltanPermisos()
    {
        $btnAnterior = new stdClass();
        $btnAnterior->value = 'Anterior';
        $btnAnterior->id = 'anterior';
        $this->mixScript('
            notify("Debes seleccionar un permiso","warning");
            $("#anterior").remove();
            $("#addTicket1").removeClass("active");
            $("#addTicket2").addClass("active");
            $(".liT").removeClass("active");
            $(".liT2").addClass("active");
            $("#save-ticket").text("Guardar");
            $(".btns").append(\'' . $this->ponBoton($btnAnterior) . '\');
            $("#anterior").click(function(){
                $("#addTicket2").removeClass("active");
                $("#addTicket1").addClass("active");
                $(".liT2").removeClass("active");
                $(".liT").addClass("active");
                $("#save-ticket").text("Continuar");
                $("#anterior").remove();
            });
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Mostramos un mensaje al usuario, en base al código que nos envíen
    public function muestraMensaje($arrMensaje)
    {
        $codigo = $arrMensaje['resultado'];
        $mensaje = $arrMensaje['mensaje'];

        switch ($codigo) {
            case "ok_1": // al dar de alta una solicitud
                $this->mixScript('
                    notify("' . $mensaje . '","success");
                    callbackComunica = function(){$(".li3").click();}
                        comunica("' . $this->pantalla . '",{
                        metodo:"main"
                    });
                ');
                break;
            case "ok_2": // Al agregar solicitud + permisos... también al eliminar solicitud
                $this->mixScript('
                    notify("' . $mensaje . '","success");
                    callbackComunica = function(){$(".li3").click();}
                        comunica("' . $this->pantalla . '",{
                        metodo:"main"
                    });
                ');
                break;
            case "ok_3": // al guardar exitosamente un cambio
                $this->mixScript('
                    notify("' . $mensaje . '","success");
                    $("document").ready(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"main"
                        });
                    });
                ');
                break;
            case "guardarPermisos": // Mensaje al guardar permisos
                $this->mixScript('
                    notify("Ticket agregado correctamente","success");
                    callbackComunica = function(){$(".li3").click();}
                    comunica("' . $this->pantalla . '",{
                        metodo:"main",
                        act:2
                    });
                ');
                break;
            case "err_1": // error al grabar un cambio
                $btnAnterior = new stdClass();
                $btnAnterior->value = 'Anterior';
                $btnAnterior->id = 'anterior';
                $this->mixScript('
                    notify("' . $mensaje . '","danger");
                    $("#anterior").remove();
                    $("#addTicket1").removeClass("active");
                    $("#addTicket2").addClass("active");
                    $(".liT").removeClass("active");
                    $(".liT2").addClass("active");
                    $("#save-ticket").text("Guardar");
                    $(".btns").append(\'' . $this->ponBoton($btnAnterior) . '\');
                    $("#anterior").click(function(){
                    $("#addTicket2").removeClass("active");
                    $("#addTicket1").addClass("active");
                    $(".liT2").removeClass("active");
                    $(".liT").addClass("active");
                    $("#save-ticket").text("Continuar");
                    $("#anterior").remove();
                    });
                ');
                break;
            case "err_msg": // genérico, para mostrar una alerta de error
                $this->mixScript('notify("' . $mensaje . '","error");');
                break;
            case "ok_msg": // genérico, para mostrar una alerta de success
                $this->mixScript('notify("' . $mensaje . '","danger");');
                break;
            default:
                $this->mixScript('notify("Atención: ' . $codigo . " -> " . $mensaje . '","danger");');
                break;
        }
        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Una vez enviada la solicitud de autorización, actualizamos la pantalla
    public function enviarSolicitudParaAutorizacion($prm, $sql)
    {   
                    $this->mixScript('notify("Solicitud de usuario enviada para su validación y autorización");');

        // Volvemos a cargar la tabla de solicitudes
        $esDePermisos = (isset($prm->esperm) ? $prm->esperm : '');
        $this->tablaSolicitudes($esDePermisos, $sql);
    }

    // Preparamos el esqueleto para la tabla de tickets
    // Sólo preparamos el esqueleto... los datos para llenarla van en otra parte
    public function tablaSolicitudes($esperm, $sql)
    {   
        // Lo ponemos aquí porque se usa en llenaTablaSolicitudes... consecuencias de un
        // código tamaloso
        $this->setSql($sql);

        $this->setContenedor('tickets');

        $tabla = new stdClass();
        $tabla->id = 'tbl-Tik';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'main';
        $tr = array();
        $th = new stdClass();
        $th->texto = 'Editar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Enviar / Imprimir';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'N° de Solicitud';
        $th->width = '80px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Usuario';
        $th->width = '110px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ur';
        $th->width = '45px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Puesto';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Correo';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Estado';
        $th->width = '60px';
        array_push($tr, $th);

        $trbody = array();

        /* Star boton Editar */
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = "eso_id"; //campo de la tabla
        $td->casos->cases = array();

        $caso = new stdClass();
        $caso->valor = '1'; //valor que se compara con el campo de la tabla
        $caso->tipo = "boton";
        $caso->icono = "edit";
        $caso->class = "seleccionaUser";
        $caso->attr = new stdClass();
        $caso->attr->tik_id = "usp_id";
        $caso->attr->sac_id = "sac_id";
        $caso->attr->tipo = "tipo";
        $caso->attr->act = "1";

        array_push($td->casos->cases, $caso);

        array_push($trbody, $td);
        /* Fin del botón Editar */

        /* Star llenar columna enviar / imprimir */
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'eso_id'; //campo de la tabla
        $td->casos->cases = array();

        $caso = new stdClass();
        $caso->valor = '1';
        $caso->tipo = "boton";
        $caso->id = "boton";
        $caso->class = 'enviar';
        $caso->color = 'verde';
        $caso->title = "Enviar solicitud de acceso";
        $caso->icono = "plane";
        $caso->attr = new stdClass();
        $caso->attr->tik_id = "usp_id";
        $caso->attr->sac_id = "sac_id";
        $caso->attr->estado = "eso_id";
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '2';
        $caso->tipo = "boton";
        $caso->id = "boton";
        $caso->class = 'enviar';
        $caso->color = 'rojo';
        $caso->label = "PDF";
        $caso->title = "Favor imprimir, firmar y sellar";
        $caso->icono = "file-pdf me-2";
        $caso->attr = new stdClass();
        $caso->attr->tik_id = "usp_id";
        $caso->attr->sac_id = "sac_id";
        $caso->attr->noTicket = "usp_id";
        $caso->attr->tipo = "tipo";
        $caso->attr->estado = "eso_id";
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '3';
        $caso->tipo = "boton";
        $caso->id = "boton";
        $caso->color = 'rojo';
        $caso->class = 'enviar';
        $caso->label = "PDF";
        $caso->title = "Solicitud autorizada";
        $caso->icono = "file-pdf me-2";
        $caso->attr = new stdClass();
        $caso->attr->tik_id = "usp_id";
        $caso->attr->sac_id = "sac_id";
        $caso->attr->noTicket = "usp_id";
        $caso->attr->tipo = "tipo";
        $caso->attr->estado = "eso_id";
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '4';
        $caso->tipo = "boton";
        $caso->id = "boton";
        $caso->class = 'enviar';
        $caso->label = "PDF";
        $caso->title = "Solicitud de acceso rechazado";
        $caso->icono = "file-pdf me-2";
        $caso->attr = new stdClass();
        $caso->attr->tik_id = "usp_id";
        $caso->attr->sac_id = "sac_id";
        $caso->attr->noTicket = "usp_id";
        $caso->attr->tipo = "tipo";
        $caso->attr->estado = "eso_id";
        array_push($td->casos->cases, $caso);

        array_push($trbody, $td);
        /* End llenar columna enviar / imprimir */

        /* End boton Editar */
        $td = new stdClass();
        $td->campoTxt = 'sac_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usuario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usp_puesto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'usp_correo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'eso_id'; //campo de la tabla
        $td->casos->cases = array();
        $caso = new stdClass();
        $caso->valor = '1';
        $caso->tipo = "txtu";
        $caso->id = "texto";
        $caso->disable = true;
        $caso->class = 'btn-user btn-user-primary';
        $caso->campoTxt = 'Estado';
        array_push($td->casos->cases, $caso);
        $caso = new stdClass();
        $caso->valor = '2';
        $caso->tipo = "txtu";
        $caso->id = "boton";
        $caso->disable = true;
        $caso->class = 'btn-user btn-user-warning';
        $caso->campoTxt = 'Estado';
        array_push($td->casos->cases, $caso);
        $caso = new stdClass();
        $caso->valor = '3';
        $caso->tipo = "txtu";
        $caso->id = "boton";
        $caso->disable = true;
        $caso->class = 'btn-user btn-user-success';
        $caso->campoTxt = 'Estado';
        array_push($td->casos->cases, $caso);
        $caso = new stdClass();
        $caso->valor = '4';
        $caso->tipo = "txtu";
        $caso->id = "boton";
        $caso->disable = true;
        $caso->class = 'btn-user btn-user-danger';
        $caso->campoTxt = 'Estado';
        array_push($td->casos->cases, $caso);

        $caso = new stdClass();
        $caso->valor = '5';
        $caso->tipo = "txtu";
        $caso->id = "boton";
        $caso->class = 'btn-user btn-user-danger';
        $caso->campoTxt = 'Estado';
        array_push($td->casos->cases, $caso);

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaSolicitudes((isset($esperm) ? $esperm : ''));
    }

    //Castro Jordy 14-03-2018 Metodo para sobre escribir la unidad ejecutora de la solicitud de Usuario
    public function rellenarUnidadEjecutora($sql)
    {
        if (trim($sql) != "") {
            $this->setContenedor('idUnidadEjecutora');
            $UEjecutora = new StdClass();
            $UEjecutora->id = 'ue_id';
            $UEjecutora->name = 'ue_id';
            $UEjecutora->label = 'Unidad Ejecutora *';
            $UEjecutora->class = 'obligatorio';
            $UEjecutora->required = true;
            $UEjecutora->enDiv = true;
            $UEjecutora->soloText = true;
            $UEjecutora->sql = $sql;
            $UEjecutora->selecciona = $this->prm->ue_id;
            $this->mixHtml($this->ponCombo($UEjecutora));
        }

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    //Castro Jordy 2/04/18 Metodo para los permisos
    public function updPermisos()
    {
        $btnNewTicketPermisos = new stdClass();
        $btnNewTicketPermisos->id = 'new-ticket-permisos';
        $btnNewTicketPermisos->class = 'btn btn-test';
        $btnNewTicketPermisos->icon = 'plus me-2';
        $btnNewTicketPermisos->color = 'azul';
        $btnNewTicketPermisos->text = 'Solicitud de Permisos';
        $btnNewTicketPermisos->type = 'button';
        $btnNewTicketPermisos->value = 'Solicitud de Permisos';

        $this->agregaTools(' ' . $this->ponBoton($btnNewTicketPermisos));

        $this->mixScript('
            $(document).ready(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"main",
                    esperm:"Si",
                    act: 2
                });
            });
            $("#new-ticket-permisos").click( function (){
                comunica("' . $this->pantalla . '",{
                        metodo:"llenaTablaUsuarios",
                        esperm:"si"
                    });
                $("#tituloPantalla").html("");
                $("#tituloPantalla").text("Solicitud de Alta de Usuario");
                    return false;
            });
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Pantalla para adicionar permisos de usuarios
    public function addPermisosUsuarios($prm, $sql)
    {
        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'cancel-ticket-permisos';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->icon = 'circle-left me-2';
        $this->agregaTools($this->ponBoton($btnRegresarIndex));

        $this->setContenedor('tickets');
        $tabla = new stdClass();
        $tabla->id = 'tbl-Usr';
        $tabla->titulo = "Usuarios dados de alta";

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        $tr = array();
        $th = new stdClass();
        $th->texto = 'Seleccionar';
        $th->width = '60px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Ur';
        $th->width = '50px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $trbody = array();

        $td = new stdClass();
        $td->tipo = "boton";
        $td->icono = "edit";
        $td->class = "seleccionaUser";
        $td->attr = new stdClass();
        $td->attr->usr_id = "usr_id";
        $td->attr->act = "2";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->setSql($sql);

        $nav = (isset($prm->nav)) ? $prm->nav : new stdClass();
        $nav->nf = isset($prm->nav->nf) ? $prm->nav->nf : -1;
        $nav->idTabla = 'tbl-Usr';
        $this->wrLlenaTabla($nav);

        $this->mixScript('$(".tabla-footer").hide();
            $("#cancel-ticket-permisos").click(function(){
                callbackComunica = function(){$(".li3").click();}
                comunica("' . $this->pantalla . '",{
                    metodo:"updPermisos",
                });
                return false;
            });

            $(".seleccionaUser").click( function (){
                comunica("' . $this->pantalla . '",{
                    metodo:"AddTicket",
                    esperm:"si",
                    usr_id:$(this).attr("usr_id")
                });
            });
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    /*Castro Jordy 10/04/2018 Estos metodos se dejaron faltan corroborar si funcionan como deberian*/
    public function recargarUr($sql)
    {
        $ur = new StdClass();
        $ur->id = 'gpo_id';
        $ur->name = 'grupo';
        $ur->enDiv = true;
        $ur->label = 'Grupo *';
        $ur->class = 'obligatorio';
        $ur->required = true;
        $ur->sql = $sql;
        $this->recargaCombo($ur);

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    public function recargarDireccion($sql)
    {
        $Direccion = new StdClass();
        $Direccion->id = 'dir_id';
        $Direccion->name = 'direccion';
        $Direccion->enDiv = true;
        $Direccion->label = 'Direccion *';
        $Direccion->class = 'obligatorio';
        $Direccion->required = true;
        $Direccion->sql = $sql;
        $this->recargaCombo($Direccion);

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    public function recargarUnidad($sql)
    {
        $Coordinacion = new StdClass();
        $Coordinacion->id = 'uni_id';
        $Coordinacion->name = 'coordinacion';
        $Coordinacion->enDiv = true;
        $Coordinacion->label = 'Coordinacion *';
        $Coordinacion->class = 'obligatorio';
        $Coordinacion->required = true;
        $Coordinacion->sql = $sql;
        $this->recargaCombo($Coordinacion);

        // OJO: como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();

    }

    public function recargarDepartamento($sql)
    {
        $Departamento = new StdClass();
        $Departamento->sql = $sql;
        $Departamento->id = 'dep_id';
        $Departamento->label = 'Departamento *';
        $Departamento->class = 'obligatorio';
        $Departamento->required = true;
        $Departamento->enDiv = true;
        $Departamento->soloText = true;
        $this->recargaCombo($Departamento);

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    public function recargarJefe($sql)
    {
        $NombreJefe = new StdClass();
        $NombreJefe->sql = $sql;
        $NombreJefe->id = 'Responsable';
        $NombreJefe->label = 'Jefe que Autoriza *';
        $NombreJefe->class = 'obligatorio';
        $NombreJefe->required = true;
        $NombreJefe->enDiv = true;
        $NombreJefe->soloText = true;
        $this->recargaCombo($NombreJefe);

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    public function llenaUpdPermiso($prm, $sql)
    {
        $this->setSql($sql);
        $nav = (isset($prm->nav)) ? $prm->nav : new stdClass();
        $nav->nf = isset($prm->nav->nf) ? $prm->nav->nf : 5;
        $nav->idTabla = 'tbl-updPermiso';

        $this->wrLlenaTabla($nav);

        $this->mixScript('
                $(".asignaPermiso").click( function (){
                comunica("' . $this->pantalla . '",{
                        metodo:"solPermisos",
                        usr_id: $(this).attr("tik_id")
                    });
                    return false;
                });
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Pantala para presentar las solicitudes de permisos a usuarios existentes
    public function solPermisos($prm, &$dataPermisos)
    {
        $idUsuario = (isset($prm->usr_id) ? $prm->usr_id : '');

        $abc = 'a';
        $btnCancelarU = new stdClass();
        $btnCancelarU->value = 'Regresar';
        $btnCancelarU->color = 'gris';
        $btnCancelarU->id = 'cancel-ticket';
        $btnCancelarU->icon = 'circle-left me-2';

        $btnGuardarU = new stdClass();
        $btnGuardarU->id = 'guardaPermisos';
        $btnGuardarU->value = 'Guardar';
        $btnGuardarU->color = 'verde';
        $btnGuardarU->icon = 'fa fa-floppy-disk me-2';
        $btnGuardarU->text = 'Guardar';
        $btnGuardarU->form = 'frmPermisos';
        $btnGuardarU->type = 'submit';

        $form = new stdClass();
        $form->id = 'frmPermisos';
        $form->controlador = $this->pantalla;
        $form->metodo = 'guardarPermisos';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = $abc;
        $form->datosExtra->permisos = '"{objetoMeta()}"';
        $form->datosExtra->idUser = $idUsuario;
        $form = new form($form);

        $form->mixHtml('
            <div class="tab-content container">
                <div class="tab-pane active" id="addTicket1">
                    <div class="row">
                        <legend>Solicitud de Cambio a Permisos</legend>
                    </div>
                </div>
            </div>
        ');

        $this->agregaTools($this->ponBoton($btnCancelarU) . ' ' . $this->ponBoton($btnGuardarU));

        $this->mixHtml($form->ponForm());

        $usPer = '';
        $sis = $men = $opc = $tar = $tchk = '';
        $h = '<ul id="browser" class="filetree">';
        foreach ($dataPermisos as $w) {
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
            $tchk = '';

            $usSplit = explode(";", $usPer);

            $id = "tar_id" . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'];

            $count = 0;
            foreach ($usSplit as $val) {
                $exi = "tar_id" . str_replace(":", "", $val);

                if ($id == $exi) {
                    $tchk = 'checked="checked"';
                    $count = 1;
                } else {
                    if ($count == 0) {
                        $tchk = '';
                    }
                }
            }

            unset($val);

            if (isset($w['nanais']) && $w['nanais'] != '') {
                $tchk = 'checked="checked"';
            } else {
                $tchk = '';
            }

            $h .= '<li><span class="file"><input ' . $tchk . ' type="checkbox" class="addPermiso" id="tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" value="' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '" sis_id = "' . $w['sis_id'] . '" men_id="' . $w['men_id'] . '" opc_id="' . $w['opc_id'] . '" tar_id="' . $w['tar_id'] . '" />' . $w['tar_nombre'] . '</span></li>';

            $this->mixScript('
                var prm="";
                var usr_permisos = new Array();
                var usrper = "' . $usPer . ';";
                var usp = "";
                $("#tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . '").click(function(){
                    if($("#tar_id' . $w['sis_id'] . $w['men_id'] . $w['opc_id'] . $w['tar_id'] . ':checked").length) {
                        usp = $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id");
                        usrper = usrper + usp + ";";
                    }else {
                        usp = $(this).attr("sis_id")+":"+$(this).attr("men_id")+":"+$(this).attr("opc_id")+":"+$(this).attr("tar_id");
                        usrper = usrper.replace(usp+";","");
                    }
                });
                function objetoMeta(){
                    var stringObj = "[";
                    stringObj += usrper.substring(0,(usrper.length - 1))
                    stringObj += "]";
                    return stringObj;
                }
            ');
        }
        $h .= ' </ul></li><!--Opcion-->
                </ul></li> <!--Menu-->
                </ul><!--ul del sistema--></li>
                </ul><!--ul clase filetree-->';

        $this->mixHtml('' . $h . '');

        $this->mixScript('
            $("#browser").treeview({
                animated:"normal",
                collapsed: false
            });
        ');

        $this->mixScript('
            $("#cancel-ticket").click(function(){
                comunica("' . $this->pantalla . '",{ metodo:"main" });
                return false;
            });
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

    // Avisamos que el usuario ya existe
    public function avisaUsuarioYaExiste($nombre, $paterno, $materno, $rfc)
    {
        $this->mixScript('
            bootbox.dialog({
                message: "El usuario ' . $nombre . ' ' . $paterno . ' ' . $materno . ' con RFC ' . $rfc . ' ya existe.",
                title: "EL USUARIO YA EXISTE",
                buttons: { aceptar : { label: "Aceptar", className: "btn-primary" } }
            });
            $("#nombre").val("");
            $("#paterno").val("");
            $("#materno").val("");
            $("#rfc").val("");
            $("#nombre").setFocus();
        ');

        // Como en la vista estará el renderizado de la pantalla, TODA función que dibuje en pantalla
        // deberá terminar con ésta instrucción
        $this->returnJson();
    }

}

// Fin del código
