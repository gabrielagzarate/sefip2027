<?php

/*
TODAS LAS RECTIFICACIONES QUE YA ESTAN APLICADAS VAN A ESTAR EN ESTADO 6 (RECURSO FEDERAL O NORMALES).
NORMALES:
    SU ESTADO ES 1,2,3,4,5,6.
FEDERALES:
    SU ESTADO ES 1,2,5,6.
*/
if (!session_id()) {
    session_start();
}

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelScp.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaScp.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/MaestroRectificaciones.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/../../ent/scp/sp_Rectificar.php';
require_once('../../lib/Math.php');

class ControladorRectificacionClc extends WebResponse
{
    protected $usrIp;
    protected $usrId;
    protected $usrNvj;
    protected $usrGpo;
    protected $usrUr;
    protected $usrDep;
    protected $usrUni;
    protected $admGpo;
    protected $admUr;
    protected vistasScp $vista;
    protected modelScp $model;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        if (!isset($_SESSION[_S])) {
            return false;
        }
        $this->pantalla = 'scp/afectaciones/ControladorRectificacionClc.php';
        /*Variables de sesion del usuario*/
        $this->usrIp = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->usrId = $_SESSION[_S]['usr_id'];
        $this->usrNvj = $_SESSION[_S]['nvj_id'];
        $this->usrGpo = $_SESSION[_S]['gpo_id'];
        $this->usrUr = $_SESSION[_S]['ur_id'];
        $this->usrDep = isset($_SESSION[_S]['dep_id']) ? $_SESSION[_S]['dep_id'] : '';
        $this->usrUni = isset($_SESSION[_S]['uni_id']) ? $_SESSION[_S]['uni_id'] : '';
        /*Variables de sesion de calsificacion administrativa*/
        $this->admGpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->admUr = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';

        $this->vista = new vistasScp();
        $this->model = new modelScp();

        switch ($this->usrNvj) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                switch ($this->metodo) {
                    case 'PantallaInicial':
                    case 'listaRectificaciones':
                        $this->listaRectificaciones();
                        break;
                    case 'llenaTablaClc':
                        $this->llenaTablaClc();
                        break;
                    case 'addModRectificacion':
                        $this->addModRectificacion();
                        break;
                    case 'llenaTablaRectificacionMovimientos':
                        $this->llenaTablaRectificacionMovimientos();
                        break;
                    case 'autorizarRectificacion':
                        $this->autorizarRectificacion();
                        break;
                    case 'EnviarRectificacion':
                        $this->cambiaEstadoRectificacion(3, 'ENVIADA A LA SEFIN', 'Rectificacion (es) Enviada (s) ', 'success');
                        break;
                    case 'cancelarRectificacionUr':
                        $this->cambiaEstadoRectificacion(1, 'CANCELADA POR LA UR', 'Rectificacion (es) Rechazada (s)', 'error');
                        break;
                    case 'recepcionarRectificacion':
                        $this->cambiaEstadoRectificacion(4, 'RECEPCIONADA POR LA SEFIN', 'Rectificacion (es) Recibida (s)', 'success');
                        break;
                    case 'revisarRectificacion':
                        $this->cambiaEstadoRectificacion(5, 'REVISADA POR LA SEFIN', 'Rectificacion (es) Revisada (s)', 'success');
                        break;
                    case 'rechazarRectificacion':
                        $this->cambiaEstadoRectificacion(1, 'RECHAZADO POR LA SEFIN', 'Rectificacion (es) Rechazada (s)', 'warning');
                        break;
                    case 'cancelarRectificacion':
                        $this->cambiaEstadoRectificacion(1, 'CANCELADA POR LA SEFIN', 'Rectificacion (es) Rechazada (s)', 'error');
                        break;
                    case 'cancelarRectificacionRf':
                        $this->cambiaEstadoRectificacion(1, 'CANCELADA POR LA UR', 'Rectificacion (es) Rechazada (s)', 'error');
                        break;
                    case 'abcRectificacion':
                        $this->abcRectificacion();
                        break;
                    case 'muestraNumerosControl':
                        $this->muestraNumerosControl();
                        break;
                    case 'llenaTablaNumerosControl':
                        $this->llenaTablaNumerosControl();
                        break;
                    case 'guardarMovimiento':
                        $this->guardarMovimiento();
                        break;
                    case 'quitaMovimiento':
                        $this->quitaMovimiento();
                        break;
                    case 'detalleNumeroControl':
                        $this->detalleNumeroControl();
                        break;
                    case 'guardaRectificacion':
                        $this->guardaRectificacion();
                        break;
                    case 'actualizaImporte':
                        $this->actualizaImporte();
                        break;
                    case 'aplicarRectificacion':
                        $this->aplicarRectificacion();
                        break;
                    case 'llenaComboUr':
                        if ($this->usrGpo . $this->usrUr == '118') {
                            $this->llenaComboUr();
                        }
                        break;
                    case 'verificaClc':
                        $this->verificaClc();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
        }
        $this->returnJson();
    }
    /* EN ESTA FUNCION SE VAN AGREGAR LAS DIFERENTES TAREAS QUE SE REALIZAN CON LAS CLC'S  A NIVEL UR(GENERACION, AUTORIZACION,ENVIO ) Y EN LA PARTE DE EGRESOS LA GESTION(RECEPCION,VALIDACION Y AUTORIZACION)*/
    private function main()
    {
        $this->agregaTools('');
        $script = '';
        #Para colocar los botones de submenu de rectificaciones
        #Si es de nivel inferior al 3 (analista) unicamente busca los permisos de generar(20), autorizar(21) y Enviar(22) en caso contrario buscara recepcion(24), revision(25) y aplicacion(26)
        $tareas = ($this->usrNvj < 3) ? '20,21,22,76' : '24,25,26';
        $this->setSql('
            SELECT
                tar_id,tar_nombre
            FROM cat_Tareas
            WHERE tar_id IN(' . $tareas . ')');
        $this->conectar();
        $this->consultar();
        #arreglo de nombre de la imagen correspondiente a cada permiso
        $imgBoton = [
            '20' => 'clc_generacion',
            '21' => 'clc_autorizacion',
            '22' => 'clc_envio-aplicacion',
            '24' => 'clc_recepcion',
            '25' => 'clc_autorizacion',
            '26' => 'clc_envio-aplicacion',
            '76' => 'clc_ejercer',
        ];
        $cards = [];
        $texto2 = '';
        while ($r = $this->extraer()) {
            if (isset($_SESSION[_S]['prm']['S']['2']['14'][$r['tar_id']])) {
                $texto = str_replace([" DE CLC'S"," DE CLC's",' CLC A SEFIN'], '', $r['tar_nombre']);
                $tarea = str_replace(' ', '', $texto);
                $texto = str_replace('CLC DEL  RECURSO FEDERAL', 'APLICAR', $texto);

                if ($r['tar_id'] == 76) {
                    $texto = 'PROCESO DE CLC';
                    $texto2 = 'DE RECURSO FEDERAL';
                } else {
                    $texto2 = '';
                }

                $c = new stdClass();
                $c->id = $tarea;
                $c->titulo = $texto;
                $c->img = 'img/img.php?img=' . $imgBoton[$r['tar_id']];
                $c->label = $texto2 !== '' ? $texto2 : $texto;
                $c->title = $texto;
                $cards[] = $c;

                #Evento click del boton colocado
                $script .= '
                    $("#' . $tarea . '").click(function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"listaRectificaciones",
                            tareaclc:"' . (strtolower(str_replace('Ó', 'o', $tarea))) . '"
                        });
                        return false;
                    });
                ';
            }
        }
        $this->desconectar();
        $this->mixHtml($this->ponCards($cards));
        $this->mixScript($script);
    }

    private function listaRectificaciones()
    {
        $script = '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc)) ? $this->prm->tareaclc : '';
        $this->titulo($this->prm->tareaclc);

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresarIndex';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->class = 'me-1';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->icon = 'circle-left me-2';
        $btnregresarindex->click = '
            comunica("' . $this->pantalla . '",{
                metodo : "main"
            });
        ';
        $btnregresarindex = $this->ponBoton($btnregresarindex);

        #se declara funcion para validar que se ha seleccionado un tipo de CLC
        $script = '
            function validaForm(){
                r = "";
                $(".tcl_idfont").each(function(){
                    if($(this).hasClass("tcl_ida"))
                        r = $(this).attr("id");
                });
                if (r =="") {
                    notify("Debe seleccionar un tipo de CLC!!","warning");
                    return false;
                }else{
                    console.log(r);
                    return r;
                }
            }
        ';

        $btnTarea = '';
        switch ($this->prm->tareaclc) {
            case 'procesodeclcderecursofederal':
            case 'procesodeclcdelrecursofederal':
            case 'aplicacion':
            default:
                $btnTarea = new stdClass();
                $btnTarea->id = 'btn' . $this->prm->tareaclc;
                $btnTarea->color = 'verde';
                break;
        }

        $btnTareaR = new stdClass();
        $btnTareaR->id = 'btnRechaza' . $this->prm->tareaclc;
        $btnTareaR->color = 'rojo';

        switch ($this->prm->tareaclc) {
            case 'generacion':
                $btnTarea->value = ' Nuevo';
                $btnTarea->class = 'me-1';
                $btnTarea->color = 'azul';
                $btnTarea->icon = 'solid fa-plus me-2';
                $btnTarea->click = '
                    if( n = validaForm() ){
                        comunica("' . $this->pantalla . '",{
                            metodo : "addModRectificacion",
                            tcl_id : n,
                            tareaclc : "' . $this->prm->tareaclc . '"
                        });
                    }
                ';
                $btnTareaR = '';
                break;
            case 'autorizacion':
                $btnTarea->value = ' Autorizar';
                $btnTarea->class = 'me-1';
                $btnTarea->color = 'verde';
                $btnTarea->icon = 'regular fa-circle-check me-2';
                $btnTarea->click = '
                if( n = validaForm() )
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Autorizar Rectificaciones",
                            "¿Realmente desea Autorizar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "autorizarRectificacion",
                                    tcl_id : n,
                                    tareaclc: "autorizacion",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR = '';
                break;
            case 'enviar':
                $btnTarea->value = ' Enviar';
                $btnTarea->class = 'me-1';
                $btnTarea->color = 'verde';
                $btnTarea->icon = 'regular fa-envelope me-2';
                $btnTarea->click = '
                if( n = validaForm() )
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Enviar Rectificaciones",
                            "¿Realmente desea Enviar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "EnviarRectificacion",
                                    tcl_id : n,
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';

                $btnTareaR->value = ' Rechazar';
                $btnTareaR->class = 'me-1';
                $btnTareaR->color = 'rojo';
                $btnTareaR->icon = 'solid fa-circle-minus me-2';
                $btnTareaR->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Rechazar Rectificaciones",
                            "¿Realmente desea Rechazar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "cancelarRectificacionUr",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            },
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR = $this->ponBoton($btnTareaR);

                break;
            case 'procesodeclcderecursofederal': case 'procesodeclcdelrecursofederal':
                // Se comenta porque aun no se permitirá la aplicacion y envío de rectificaciones.
                $btnTarea->value = ' Aplicar Rectificacion';
                $btnTarea->icon = 'solid fa-check me-2';
                $btnTarea->color = 'azul';
                $btnTarea->class = 'me-1';
                $btnTarea->click = '
                if( n = validaForm() )
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Aplicar Rectificaciones",
                            "¿Realmente desea Aplicar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "aplicarRectificacion",
                                    tcl_id : n,
                                    federal : true,
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR->value = ' Cancelar';
                $btnTareaR->icon = 'solid fa-arrow-right-to-bracket me-2';
                $btnTareaR->color = 'rojo';
                $btnTareaR->class = 'me-1';
                $btnTareaR->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Rechazar Rectificaciones",
                            "¿Realmente desea Rechazar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "cancelarRectificacionRf",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR = $this->ponBoton($btnTareaR);
                break;
            case 'recepcion':
                $btnTarea->value = ' Recepcionar';
                $btnTarea->class = 'me-1';
                $btnTarea->icon = 'solid fa-arrow-right-to-bracket me-2';
                $btnTarea->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Recepcionar Rectificaciones",
                            "¿Realmente desea Recepcionar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "recepcionarRectificacion",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR->value = ' Rechazar';
                $btnTareaR->class = 'me-1';
                $btnTareaR->icon = 'solid fa-arrow-right-to-bracket me-2';
                $btnTareaR->color = 'rojo';
                $btnTareaR->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Rechazar Rectificaciones",
                            "¿Realmente desea Rechazar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "rechazarRectificacion",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                ';
                $btnTareaR = $this->ponBoton($btnTareaR);
                $estadoRct = 3;
                break;
            case 'revision':
                $btnTarea->value = ' Validar';
                $btnTarea->class = 'me-1';
                $btnTarea->icon = 'solid fa-check me-2';
                $btnTarea->color = 'verde';
                $btnTarea->click = '
                if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                    alertify.confirm(
                        "Validar Rectificaciones",
                        "¿Realmente desea Validar la(s) Rectificacion(es)?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo : "revisarRectificacion",
                                autorizarect : seleccionados
                            });
                        },
                        function(){
                        }
                    ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-success");
                            }
                }
                ';
                $btnTareaR->value = ' Rechazar';
                $btnTareaR->class = 'me-1';
                $btnTareaR->icon = 'solid fa-arrow-right-to-bracket me-2';
                $btnTareaR->color = 'rojo';
                $btnTareaR->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Rechazar Rectificaciones",
                            "¿Realmente desea Rechazar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "cancelarRectificacion",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-success");
                            }
                    }
                ';
                $btnTareaR = $this->ponBoton($btnTareaR);
                $estadoRct = 4;
                break;
            case 'aplicacion':
                $btnTarea->value = ' Aplicar';
                $btnTarea->class = 'me-1';
                $btnTarea->icon = 'solid fa-thumbs-up me-2';
                $btnTarea->color = 'verde';
                $btnTarea->click = '
                if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                    alertify.confirm(
                        "Aplicar Rectificaciones",
                        "¿Realmente desea Aplicar la(s) Rectificacion(es)?",
                        function(){
                            comunica("' . $this->pantalla . '",{
                                metodo : "aplicarRectificacion",
                                autorizarect : seleccionados
                            });
                        },
                        function(){
                        }
                    ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                    const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-success");
                            }
                }
                ';
                $btnTareaR->value = ' Rechazar';
                $btnTareaR->class = 'me-1';
                $btnTareaR->icon = 'solid fa-arrow-right-to-bracket me-2';
                $btnTareaR->color = 'rojo';
                $btnTareaR->click = '
                    if( seleccionados = $("#tbl-Rectificacion").tabla("getFilaSeleccion") ){
                        alertify.confirm(
                            "Rechazar Rectificaciones",
                            "¿Realmente desea Rechazar la(s) Rectificacion(es)?",
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo : "cancelarRectificacion",
                                    autorizarect : seleccionados
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Aceptar", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-success");
                            }
                    }
                ';
                $btnTareaR = $this->ponBoton($btnTareaR);
                $estadoRct = 5;
                break;

        }
        if (!is_object($btnTarea)) {
            $boton = '';
        } else {
            $boton = $this->ponBoton($btnTarea);
        }

        $this->agregaTools($btnregresarindex . ' ' . $boton . ' ' . $btnTareaR);

        $tabla = new stdClass();
        $tabla->id = 'tbl-Rectificacion';
        $tabla->titulo = 'Rectificaciones';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->tareaclc = $this->prm->tareaclc;
        $tabla->numFilas = 6;
        $tabla->filasAmostrar = [5,10,15,20,50];

        $checked = $filtro = $html = '';

        if ($this->usrNvj < 3) {
            $datos = $this->model->getTiposClc();
            if ($datos['registros'] == 1) {
                $this->prm->tcl_id = $datos['datos'][0]['tcl_id'];
            }
            $this->prm->tcl_id = isset($this->prm->tcl_id) ? $this->prm->tcl_id : '';
            $radios = $this->vista->vwTipoClc($datos, $this->prm->tcl_id);
            $tabla->tools = $radios;
            $tabla->comunica->tcl_id = '{(validaForm())}';
            $script .= '
                $(".tcl_idfont").click(function(){
                    if(!$(this).hasClass( "tcl_ida" ) ){
                        $(".tcl_idfont").removeClass("tcl_ida");
                        $(".tcl_idfont").removeClass("loadingclas");
                        $(".tcl_idfont").addClass("tcl_idn");
                        $(".tcl_idfont").removeAttr("checked");
                        $(this).removeClass("tcl_idn");
                        $(this).addClass("tcl_ida");
                        $(this).addClass("loadingclas");
                        ' . $this->wrLlamaLlenaTabla('tbl-Rectificacion') . '
                    }
                    console.log("' . $this->prm->tcl_id . '");
                });
            ';
        } else {

            $tabla->comunica->gpoid = $this->usrGpo;
            $tabla->comunica->urid = $this->usrUr;
            $tabla->comunica->numrct = '{$("#numrct").val()}';

            $urConRectificacion = new stdClass();
            $urConRectificacion->id = 'urClave_id';
            $urConRectificacion->enDiv = true;
            $urConRectificacion->comoAtr = 'gpo_id,ur_id';
            $urConRectificacion->mensaje = 'Unidad Responsable';

            //Se encarga de checar las UR disponibles
            $urConRectificacion->sql = '
                SELECT distinct ur_clave,ur_nombre,gpo_id,ur_id
                FROM cat_UnidadResponsable
                JOIN ' . BD_CTRL . '.scp_MaestroRectificacion r USING(gpo_id,ur_id)
                JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
                left JOIN ' . BD_CTRL . '.enc_FiltroRectificacion USING(gpo_id,ur_id,tcl_id,stc_id)
                WHERE r.ecl_id = ' . $estadoRct;

            $numrct = new stdClass();
            $numrct->id = 'numrct';
            $numrct->enDiv = true;
            $numrct->maxlength = 6;
            $numrct->search = true;

            $filtro = '
                <div class="row">
                    <fieldset class="col-md-5 mb-4">
                        <label>UR:</label>
                        <div>
                            ' . $this->templateCombo($urConRectificacion) . '
                        </div>
                    </fieldset>
                    <fieldset class="col-md-3 mb-4">
                        <label>Numero de Rectificación:</label>
                        <div>
                            ' . $this->ponInput($numrct) . '
                        </div>
                    </fieldset>
                </div>
            ';

            $script .= '
                $("#urClave_id").change(function(){
                    if( $(this).val() != "" || $("#numrct").val() != ""){
                        ' . $this->wrLlamaLlenaTabla('tbl-Rectificacion') . '
                    }
                });
                $("#numrct").keypress(function(event){
                    var anularTecla = false;
                    var esPermitido = Array.indexOf([0,8], event.which) >= 0;
                    anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                    if( anularTecla ) event.preventDefault();
                });
                $("#numrct").blur(function(){
                    if( $("#urClave_id").val() != "" || $(this).val() != ""){
                        ' . $this->wrLlamaLlenaTabla('tbl-Rectificacion') . '
                    }
                });
            ';
            $tabla->tools = $filtro;
        }

        if ($this->prm->tareaclc != 'recepcion') {
            $tabla->accion = new stdClass();
            $tabla->accion->prm = ['rct_id','clc_id','rct_reintegro','tcl_id'];
            if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj > 2) {
                array_push($tabla->accion->prm, 'gpo_id');
                array_push($tabla->accion->prm, 'ur_id');
            }
            $tabla->accion->editar = true;
            $tabla->accion->metodoEditar = 'addModRectificacion';
            $tabla->accion->pdf = true;
            $tabla->accion->urlPdf = 'rep/doc/scp/afectaciones/rep_RectificacionClc.php';
        }

        $tabla->prmSeleccion = ['gpo_id','ur_id','rct_id','tipo','clc_id','recursoFederal'];

        $mensaje = false;
        if ($this->prm->tareaclc != 'generacion') {
            $mensaje = true;
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'iguales';
            $tabla->casoSeleccionable->valor = '1';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(255, 0, 0, 0.20)';
            $tabla->multiseleccionable = true;
        }

        $tr = [];

        if ($this->usrNvj > 2) {
            $th = new stdClass();
            $th->texto = 'UR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CLC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'TIPO';
            array_push($tr, $th);
        }
        /* NUMERO DE RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'RECTIFICACION';
        array_push($tr, $th);
        /* NUMERO DE RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'FECHA ELABORACIÓN';
        array_push($tr, $th);
        /* JUSTIFIACION DE LA RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'JUSTIFICACION';
        array_push($tr, $th);
        /* TOTAL RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'TOTAL A RECTIFICAR';
        array_push($tr, $th);
        /* PERCEPCIONES DE LA RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'PERCEPCIONES';
        array_push($tr, $th);
        /* RETENCIONES DE LA RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'RETENCIONES';
        array_push($tr, $th);
        /* IMPORTE DE LA RECTIFICACION */
        $th = new stdClass();
        $th->texto = 'TOTAL NETO';
        array_push($tr, $th);

        $trbody = [];

        if ($this->usrNvj > 2) {
            //TIPO DE CLC
            $td = new stdClass();
            $td->campoTxt = 'urClave';
            array_push($trbody, $td);
            //TIPO DE CLC
            $td = new stdClass();
            $td->campoTxt = 'clc_id';
            array_push($trbody, $td);
            //TIPO DE CLC
            $td = new stdClass();
            $td->campoTxt = 'tclid';
            array_push($trbody, $td);
        }
        //NUMERO DE CLC
        $td = new stdClass();
        $td->campoTxt = 'rct_id';
        array_push($trbody, $td);
        //FECHA DE CREACION
        $td = new stdClass();
        $td->campoTxt = 'rct_fechaElaboracion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'rct_justificacion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'rct_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        //PERCEPCIONES
        $td = new stdClass();
        $td->campoTxt = 'rct_percepcion';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        //RETENCIONES
        $td = new stdClass();
        $td->campoTxt = 'rct_retencion';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        //RFC IMPORTE
        $td = new stdClass();
        $td->campoTxt = 'importeNeto';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        if ($mensaje) {
            $html .= '
                <p/>
                <p/>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Nota</h3>
                    </div>
                    <div class="row col-12 col-md-12">
                    <label style="color:red;">
                            Las rectificaciones con reintegro podrán ser seleccionadas solo si el importe total de la rectificación coincide con el importe total neto de los movimientos de la rectificación.
                            <p/>Ejemplo:
                        </label>
                        <table class = "table table-bordered">
                            <thead>
                                <tr class="warning">
                                    <th class="success">TOTAL A RECTIFICAR</th>
                                    <th class="success">PERCEPCIONES</th>
                                    <th class="success">RETENCIONES</th>
                                    <th class="success">TOTAL NETO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <td>$ 100.00</td>
                                <td>$ 200.00</td>
                                <td>$ 100.00</td>
                                <td>$ 100.00</td>
                            </tbody>
                        </table>
                    </div>
                </div>
            ';
        }

        $this->mixHtml($this->wrPonTabla($tabla) . $html);
        $this->mixScript($script);

        if (!empty($urConRectificacion)) {
            $this->recargaCombo($urConRectificacion);
        }
        if (isset($this->prm->tcl_id) || $this->usrNvj > 2) {
            $this->llenaTablaClc();
        }
    }

    private function llenaTablaClc()
    {
        $estado = $filtro = '';
        $having = '';
        $join = '';

        if (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') {
            switch ($this->prm->tareaclc) {

                case 'generacion': case 'autorizacion':
                    $estado = 'AND r.ecl_id = 1 ';
                    break;
                case 'enviar':
                    $estado = 'AND r.ecl_id = 2 ';
                    $having = 'HAVING recursoFederal = 0';
                    break;
                case 'procesodeclcderecursofederal': case 'procesodeclcdelrecursofederal':
                    $estado = 'AND r.ecl_id = 2 ';
                    $having = 'HAVING recursoFederal = 1';
                    break;
                case 'recepcion':
                    $estado = 'AND r.ecl_id = 3 ';
                    break;
                case 'revision':
                    $estado = 'AND r.ecl_id = 4 ';
                    break;
                case 'aplicacion':
                    $estado = 'AND r.ecl_id = 5 AND clc_recursoFederal = 2';
                    break;
            }
        }

        $params = [];
        if ($this->usrNvj > 2) {

            $join = 'INNER JOIN ' . BD_CTRL . '.enc_FiltroRectificacion USING(gpo_id,ur_id,tcl_id,stc_id)';
            $filtro = 'rct_gpo IS NULL AND rct_ur IS NULL ' . $estado ;
            if ($this->prm->tareaclc != 'recepcion') {
                array_push($params, $this->usrDep);
                $filtro .= ' AND dep_id = ? ';
            } elseif ($this->usrUni == 17) {
                $filtro .= 'AND dep_id IN (44,45,46)' ;
            } else {
                $filtro .= 'AND dep_id IN (49,48,50)';
            }
            if (isset($this->prm->numrct) && $this->prm->numrct != '') {
                array_push($params, $this->prm->numrct);
                $filtro .= ' AND rct_id = ? ';
            }

        } else {
            if ($this->usrGpo . $this->usrUr == '118') {
                array_push($params, $this->usrGpo, $this->usrUr, $this->usrGpo, $this->usrUr);
                $filtro = '( (gpo_id = ? AND ur_id = ?) OR ( rct_gpo = ? AND rct_ur = ?) ) ' . $estado . ' ';
            } else {
                array_push($params, $this->usrGpo, $this->usrUr);
                $filtro = 'gpo_id = ? AND ur_id = ? ' . $estado . ' AND rct_gpo IS NULL AND rct_ur IS NULL ';
            }
            array_push($params, $this->prm->tcl_id);
            $filtro .= 'AND tcl_id = ? ';
        }
        $this->conectar();

        //Verifica el tipo de cuenta de la de la CLC en base a la tabla ctrl'. APP_EJERCICIO .'.scp_MaestroRectificacion
        $this->setSql('
            SELECT
                CONCAT(gpo_id,ur_id) urClave,gpo_id,ur_id,rct_id,tcl_id tclid,tcl_id,
                DATE_FORMAT(rct_fechaElaboracion, "%d/%m/%Y") rct_fechaElaboracion,
                rct_justificacion,rct_importe,rct_reintegro,rct_percepcion,rct_retencion,
                rct_percepcion - rct_retencion importeNeto,
                ( (rct_importe = (rct_percepcion - rct_retencion))
                AND (rct_percepcion - rct_retencion) OR rct_reintegro = 2 ) OR  rct_reintegro = 3 iguales,
                clc_id,
                GROUP_CONCAT(
                    DISTINCT CASE
                        WHEN(
                            ( ncc_amortizado = 1 AND (ncc_nccamortiza = 0 OR ISNULL(ncc_nccamortiza) ) )
                            OR( ger_id = 2 AND cgp_id = 1 AND rub_id = 1
                                AND (
                                    ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 9 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                )
                            )
                        )THEN "ORIGEN"
                        WHEN ncc_amortizado = 2 AND (isnull(ncc_nccamortiza) OR ncc_nccamortiza = 0) THEN "NORMAL"
                        WHEN(
                            (ncc_amortizado = 2 AND (ncc_nccamortiza != 0 AND NOT ISNULL(ncc_nccamortiza) ) )
                            OR(
                                ger_id = 2 AND cgp_id = 1 AND rub_id = 1
                                AND(
                                    ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                )
                            )
                        )THEN "PAGADORA"
                    END
                ) tipo,
                if(clc_recursoFederal = 1,1,0) recursoFederal
            FROM ' . BD_CTRL . '.scp_MaestroRectificacion r
                INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
                INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                ' . $join . '
                LEFT JOIN scp_Contable USING(gpo_id,ur_id,clc_id)
                LEFT JOIN ' . BD_CTRL . '.scp_RectificacionContable USING(gpo_id,ur_id,rct_id,clc_id,afe_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
            WHERE ' . $filtro . '
            GROUP BY gpo_id,ur_id,rct_id
            ' . $having . '
        ');
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 5 ;
        $nav->idTabla = 'tbl-Rectificacion';

        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".tcl_ida").removeClass("loadingclas");
        ');
    }

    private function addModRectificacion()
    {
        $ur318 = $this->usrGpo . $this->usrUr == '118';
        $tcl = (empty($this->prm->tcl_id)) ? $this->prm->tcl_id : $this->prm->tcl_id;

        $btnregresar = new stdClass();
        $btnregresar->id = 'btnRegresarIndex';
        $btnregresar->value = ' Regresar';
        $btnregresar->color = 'gris';
        $btnregresar->icon = 'circle-left me-2';
        $btnregresar->click = '
            $("nav").menusinpres("quitaBreadcrumbs").menusinpres("quitaBreadcrumbs");
            comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tcl_id:"' . $tcl . '",
                tareaclc:"' . $this->prm->tareaclc . '"
            });
        ';
        $btnregresar = $this->ponBoton($btnregresar);
        $this->agregaTools($btnregresar);

        $rectificacion = new stdClass();
        $rct = new MaestroRectificacion();
        $bandera = true;
        $abc = 'a';
        $numrct = '';

        if (!empty($this->prm->rct_id)) {

            $this->titulo('Modificacion de Rectificacion ' . $this->prm->rct_id);

            if ($ur318 || $this->usrNvj > 2) {
                $rct->setGpo_id($this->prm->gpo_id);
                $rct->setUr_id($this->prm->ur_id);
            } else {
                $rct->setGpo_id($this->admGpo);
                $rct->setUr_id($this->admUr);
            }

            $rct->setRct_id($this->prm->rct_id);
            $rct->select();
            $rectificacion->selecciona = $this->prm->clc_id;
            $this->prm->reintegro = $rct->getReintegro();

            $abc = 'c';
            $params = [$rct->getGpo_id(), $rct->getUr_id(), $rct->getRct_id(), $rct->getGpo_id(), $rct->getUr_id(), $rct->getRct_id()];
            $this->conectar();
            //Consulta las dos tablas para verificar si existe algun valor en una de las dos tablas con respecto a los ids correspondientes
            $this->setSql('SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncp_clave as clave,ncp_id as numcontrol
                FROM ' . BD_CTRL . '.scp_RectificacionPresupuestal
                WHERE gpo_id = ? 
                AND ur_id = ? 
                AND rct_id = ? 
                UNION ALL
                SELECT gpo_id,ur_id,clc_id,afe_id,rct_id,ncc_clave as clave,ncc_id as numcontrol
                FROM ' . BD_CTRL . '.scp_RectificacionContable
                WHERE gpo_id = ? 
                AND ur_id = ? 
                AND rct_id = ? ;
            ');
            $this->consultar($params);

            $this->desconectar();
            $numrct = new stdClass();
            $numrct->id = 'numrct';
            $numrct->label = 'Numero de Rectificación';
            $numrct->readonly = true;

            $numrct->enDiv = true;
            $numrct->maxlength = 6;
            $numrct->value = $rct->getRct_id();
            $numrct = $this->ponInput($numrct);
            if ($this->getNumeroRegistros() != 0) {
                $this->mixScript('
                    if($("input[name=tipo_Pago]:checked").val()!=""){
                        $("input[name=tipo_Pago]").prop("disabled",true);
                    }
                ');
                $bandera = false;
            }
        } else {
            $this->titulo('Nueva Rectificacion');
        }

        $bandera = $bandera && $this->usrNvj < 3;

        $rectificacion->label = 'CLC a Rectificar';
        $rectificacion->id = 'clc_id';
        $rectificacion->required = true;
        $rectificacion->name = 'clc_id';
        $rectificacion->enDiv = true;
        $rectificacion->soloText = true;
        $rectificacion->comoAtr = 'recursoFederal';
        $rectificacion->search = true;
        $rectificacion->change = '
            if ($(this).find("option:checked").attr("recursoFederal") == "1") {
                ajustarOpcionesReintegro(2);
                $("input[name=tipo_Pago][value=2]").attr("checked", "true");
                $("input[name=tipo_Pago][value=1]").parent().parent().hide();
            }
            else {
                $("input[name=tipo_Pago][value=1]").parent().parent().show();
            }
        ';
        $rectificacion->mensaje = 'Numero de CLC';
        $params = [];
        if ($this->usrNvj < 3) {
            $params[':gpo_id'] = $this->usrGpo;
            $params[':ur_id'] = $this->usrUr;
            $whereUr = 'c.gpo_id = :gpo_id AND c.ur_id = :ur_id ';
        } else {
            $params[':gpo_id'] = $rct->getGpo_id();
            $params[':ur_id'] = $rct->getUr_id();
            $whereUr = 'c.gpo_id = :gpo_id AND c.ur_id = :ur_id ';
        }

        $gpoAbc = ($this->usrNvj < 3) ? $this->usrGpo : $rct->getGpo_id();
        $urAbc = ($this->usrNvj < 3) ? $this->usrUr : $rct->getUr_id();

        $gpo_id = $ur_id = '';

        if ($this->usrNvj < 3) {
            $gpo_id = $this->usrGpo;
            $ur_id = $this->usrUr;
        } else {
            $gpo_id = $this->admGpo;
            $ur_id = $this->admUr;
        }
        switch ($this->usrGpo . $this->usrUr) {
            default:{
                $rectificacion->sql = '
                    SELECT clc_id,clc_id,SUM( if(r.ecl_id = 6,1,0) ) rectificacionConcluida,
                        SUM( if(r.ecl_id != 6,1,0) ) rectificacionEnTramite,
                        if(clc_recursoFederal=1,1,0) recursoFederal' . ($abc == 'a' ? ',bdo_estadoPago ' : ' ') . '
                    FROM scp_Clc c
                    INNER JOIN scp_ClcAuxiliar b using(gpo_id,ur_id,clc_id,ecl_id)
                    ' . ($abc == 'a' ?
                        'INNER JOIN (
                        SELECT gpo_id,ur_id,clc_id,MIN(bdo_estadoPago) as bdo_estadoPago
                        FROM scp_Afectacion
                        INNER JOIN (
                            SELECT c.gpo_id,c.ur_id,clc_id,afe_id,MIN(bdo_estadoPago) as bdo_estadoPago
                            FROM scp_Contable c
                            INNER JOIN cat_BancoDocumentos USING(bdo_id)
                            WHERE c.gpo_id = :gpo and c.ur_id = :ur
                            group by gpo_id,ur_id,clc_id,afe_id
                            UNION ALL
                            SELECT p.gpo_id,p.ur_id,clc_id,afe_id,MIN(bdo_estadoPago) as bdo_estadoPago
                            FROM scp_Presupuestal p
                            INNER JOIN cat_BancoDocumentos USING(bdo_id)
                            WHERE p.gpo_id = :gpo and p.ur_id = :ur
                            group by gpo_id,ur_id,clc_id,afe_id
                        )control USING(gpo_id,ur_id,clc_id,afe_id)
                        GROUP BY gpo_id,ur_id,clc_id
                    )afe USING(gpo_id,ur_id,clc_id)
                    ' : '') . '
                    LEFT JOIN ' . BD_CTRL . '.scp_MaestroRectificacion r using (gpo_id,ur_id,clc_id)
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
                    JOIN cat_BancosUr USING(bur_id)
                    WHERE ' . $whereUr . ' 
                    AND b.ecl_id in (18)
                    AND not(mpc_id = 3 
                    AND clc_rectificacion IS NOT NULL 
                    AND clc_rectificacion !=0)
                    AND tcl_id = :tcl_id ' . ((!empty($this->prm->rct_id)) ? ' AND rct_id = :rct_id ' : '') . '
                    AND bur_fondoRotatorio = "N" AND (clc_rectificacion IS NULL OR clc_rectificacion=0)
                    and mpc_id NOT IN (5,2)
                    GROUP BY c.gpo_id,c.ur_id,clc_id
                    ' . ((!empty($this->prm->rct_id)) ? ($abc == 'a' ? 'HAVING bdo_estadoPago=3 ' : ' ') : ' HAVING rectificacionConcluida < 2 AND rectificacionEnTramite=0 ' . ($abc == 'a' ? '
                    AND bdo_estadoPago=3 ' : ' ')) . ';
                ';

                if ($abc == 'a') {
                    $params[':gpo'] = $gpo_id;
                    $params[':ur'] = $ur_id;
                }
                $params[':tcl_id'] = $tcl;
                if (!empty($this->prm->rct_id)) {
                    $params[':rct_id'] = $this->prm->rct_id;
                }

                $rectificacion->params = $params;
                $rectificacion = $this->ponCombo($rectificacion);
                break;
            }
        }
        /*FECHA DE ELABORACION DE LA RECTIFICACION */
        $hoy = date('Y-m-d');
        $anio = date('Y');

        $date = ((int) APP_EJERCICIO) + 1;
        if ($anio == $date) {
            $hoy = APP_EJERCICIO . '-12-31';
        }

        $fechacreacion = new StdClass();
        $fechacreacion->id = 'fechacreacion';
        $fechacreacion->label = 'Fecha de Elaboracion';
        $fechacreacion->enDiv = true;
        $fechacreacion->readonly = true;

        if (!$bandera) {
            $fechacreacion->readonly = true;
        }
        if ($abc == 'a') {
            $fechacreacion->value = $hoy;
            $btnReportePagadora = '';
        } else {

            $fechacreacion->value = $rct->getFechaElaboracion();
            $btnReportePagadora = new stdClass();
            $btnReportePagadora->id = 'excelPagadoras';
            $btnReportePagadora->color = 'verde';
            $btnReportePagadora->icon = 'solid fa-file-excel me-2';
            $btnReportePagadora->class = 'me-1';
            $btnReportePagadora->value = '';
            $btnReportePagadora->click = '
                var $formulario = $("<form>")
                    .attr("action", "rep/excel/scp/afectaciones/ExportaReportePagadoras.php")
                    .attr("method", "post")
                    .attr("target", "_blank");
                $formulario.append($("<input>").attr("type", "hidden").attr("name", "rct_id").val("' . $this->prm->rct_id . '"));
                ' . (($this->usrNvj > 2 || $this->usrGpo . $this->usrUr == '118') ? '
                $formulario.append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val("' . $rct->getGpo_id() . '"));
                $formulario.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val("' . $rct->getUr_id() . '"));
                ' : '') . '
                $("body").append($formulario);
                $formulario.submit();
                $formulario.remove();
            ';
            $btnReportePagadora->attr = new stdClass();
            $btnReportePagadora = '';
            $this->mixScript('$("#excelPagadoras").hide().find("span").removeClass("glyphicon");');
        }

        /* CONCEPTO DE PAGO DE  LA CLC */
        $justificacion = new stdClass();
        $justificacion->id = 'justificacion';
        $justificacion->name = 'justificacion';
        $justificacion->label = 'Justificación de la Rectificación';
        $justificacion->placeholder = 'Justificación de la Rectificación';
        $justificacion->enDiv = true;
        $justificacion->class = 'habilitar';
        $justificacion->required = true;
        $justificacion->width = '400px';
        $justificacion->class = 'anulaenter habilitar';
        $justificacion->height = '150px';
        $justificacion->maxlength = '600' ;

        if (!$bandera) {
            $justificacion->readonly = true;
        }
        $justificacion->text = $rct->getJustificacion();

        $tipo = new StdClass();
        $tipo->class = 'radio habilitar';
        $tipo->titulo = 'Tipo de rectificación';
        $tipo->name = 'tipo_Pago';
        $tipo->values = [1 => 'CON REINTEGRO',2 => 'SIN REINTEGRO'];

        $tipo->cols = 3;
        $tipo->required = true;
        if (!$bandera) {
            $tipo->readonly = true;
        }
        $tipo->selecciona = ($rct->getReintegro() != '' && $rct->getReintegro() > 1) ? 2 : 1;
        $tipo = $this->ponRadio($tipo);

        $comprobante = new stdClass();
        $comprobante->id = 'comprobante';
        $comprobante->label = 'Número de Comprobante';
        $comprobante->placeholder = 'Número de Comprobante';
        $comprobante->enDiv = true;
        $comprobante->required = true;
        $comprobante->class = 'habilitar';
        $comprobante->maxlength = 20;

        if (!$bandera) {
            $comprobante->readonly = true;
        }

        $comprobante->value = $rct->getNumeroComprobante();

        $totalRct = new stdClass();
        $totalRct->id = 'totalRct';
        $totalRct->label = 'Importe Total a Rectificar';
        $totalRct->placeholder = 'Importe Total a Rectificar';
        $totalRct->enDiv = true;
        $totalRct->positivo = true;
        $totalRct->required = true;
        $totalRct->class = 'habilitar';
        $totalRct->maxlength = 50;

        if (!$bandera && $this->usrNvj < 3) {
            $totalRct->blur = '
                comunica("' . $this->pantalla . '",{
                    metodo:"actualizaImporte",
                    importe:unformatNumber($(this).val()),
                    rct_id:' . $this->prm->rct_id . ',
                    gpo_id:' . $rct->getGpo_id() . ',
                    ur_id:' . $rct->getUr_id() . '
                });
            ';
        }
        $totalRct->formato = 'moneda';
        $totalRct->value = $rct->getImporte();

        /*FECHA DE COMPROBANTE */
        $fechaComp = new stdClass();
        $fechaComp->id = 'fechaComp';
        $fechaComp->class = 'fecha habilitar';
        $fechaComp->label = 'Fecha de Comprobante';
        $fechaComp->placeholder = 'Fecha de Comprobante';
        $fechaComp->enDiv = true;
        $fechaComp->formato = 'fecha';
        $fechaComp->labelClass = '_32 txtL';
        $fechaComp->required = true;

        if (!$bandera || $this->usrNvj > 2) {
            $fechaComp->readonly = true;
        }

        $fechaComp->value = $rct->getFechaComprobante();

        $guardar = $eliminar = '';
        if ($bandera == true && $this->usrNvj < 3) {
            if ($abc == 'c') {
                $btnEliminar = new stdClass();
                $btnEliminar->id = 'delete';
                $btnEliminar->color = 'rojo';
                $btnEliminar->class = 'me-1';
                $btnEliminar->icon = 'trash me-2';
                $btnEliminar->value = ' Eliminar';
                $btnEliminar->attr = new stdClass();
                $eliminar = $this->ponBoton($btnEliminar);
            }
            $btnGuardar = new stdClass();
            $btnGuardar->color = 'verde';
            $btnGuardar->class = 'me-1';
            $btnGuardar->icon = 'floppy-disk me-2';
            $btnGuardar->id = 'guardar';
            $btnGuardar->value = ' Guardar';
            $guardar = $this->ponBoton($btnGuardar);
        }

        $clcRect = new stdClass();
        $clcRect->id = 'clcRect';
        $clcRect->label = 'Verificar CLC';
        $clcRect->placeholder = 'Ingresa CLC';
        $clcRect->enDiv = true;
        $clcRect->maxlength = 6;

        $this->agregaTools($guardar . $eliminar . $btnReportePagadora);

        $html = '
            <form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">
            <legend>Registro de Rectificación</legend>
            <div class="row">
                <fieldset class="col-md-12 col-xs-12 mb-4">
                    ' . $tipo . '
                </fieldset>
            </div>
            <div class="row mb-4">
        ';
        /*NUMERO DE LA UR PARA LA 118*/
        if ($this->usrGpo == 3 and $this->usrUr == 18) {
            $numUr = new stdClass();
            $numUr->id = 'numUr';
            $numUr->label = 'Número de UR';
            $numUr->class = 'habilitar';
            $numUr->enDiv = true;
            $numUr->maxlength = 3;
            $numUr->blur = '
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaComboUr",
                    ur:$(this).val(),
                    tcl_id:"' . $tcl . '"
                }); ';
            if ($abc == 'c') {
                $numUr->value = $rct->getGpo_id() . $rct->getUr_id();
                $numUr->readonly = true;
            }

            $html .= '<fieldset class="col-md-2 col-xs-2 mb-4"> ' . $this->ponInput($numUr) . ' </fieldset> ';
        }

        $html .= '
            <fieldset class="col-md-2 col-xs-2"> ' . $rectificacion . ' </fieldset>
            <fieldset class="col-md-2 col-xs-2"> ' . $this->ponInput($fechacreacion) . ' </fieldset>
            <fieldset class="col-md-6 col-xs-6" id="verificar">
            </fieldset>
            </div>
            <div class="row mb-4" id="datosReintegro">
                <fieldset class="col-md-2 col-xs-2"> ' . $this->ponInput($totalRct) . ' </fieldset>
                <fieldset class="col-md-2 col-xs-2"> ' . $this->ponInput($comprobante) . ' </fieldset>
                <fieldset class="col-md-2 col-xs-2"> ' . $this->ponInput($fechaComp) . ' </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-8 col-xs-8"> ' . $this->ponTextarea($justificacion) . ' </fieldset>
            </div>
            </form>
        ';
        $this->mixHtml($html);

        if ($abc == 'c') {
            $this->mixHtml('
                <div class="row mb-4">
                    <div align="center"></div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-xs-12">
                        <div  id="rectificacionMov">' . $this->tablaRectificacionMovimientos(false) . '</div>
                    </div>
                </div>
            ');
        } else {
            $this->mixHtml('
                </br>
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12 mb-4" style="color:#D90000" data-toggle="tooltip" data-placement="top" title="Si la CLC no aparece en el combo, ingresa el número para verificar porque no puedes realizar la Rectificación">
                        <legend><small>¿Por que no muestra mi CLC para rectificar?</small></legend>
                        <div class="col-md-2 col-xs-2">' . $this->ponInput($clcRect) . '</div>
                    </fieldset>
                </div>
            ');
        }

        $modP = (!empty($this->prm->rct_id)) ? '$("input[name=tipo_Pago]:checked").click();' : '';

        $this->mixScript('
            function ajustarOpcionesReintegro(reintegro) {
                switch (reintegro) {
                    case 1:
                        $("#datosReintegro").show();
                        $("#datosReintegro *:disabled").attr("required","required").removeAttr("disabled");
                        break;
                    
                    case 2:
                        $("#datosReintegro *:required").attr("disabled","disabled").removeAttr("required");
                        $("#datosReintegro").hide();
                        break;
                }
            }
            $("input[name=tipo_Pago]").click(function(){
                ajustarOpcionesReintegro(parseInt($("input[name=tipo_Pago]:checked").val()));
            });
            ' . $modP . '
        ');

        $rct_id = (isset($this->prm->rct_id) && $this->prm->rct_id != '') ? 'obj.rct_id = ' . $this->prm->rct_id . '; ' : '';

        $this->mixScript('
            $("#clcRect").blur(function(){
                if($("#clcRect").val()!=""){
                    comunica("' . $this->pantalla . '",{
                    metodo:"verificaClc",
                    clc_id:$("#clcRect").val(),
                    tcl_id:"' . $tcl . '"
                    });
                }
            })
            
            $("#guardar,#delete").unbind("click");
            $("#guardar").click(function(){
                $(this).prop("disabled",true);
                guardar();
                return false;
            });
            
            $("#delete").click(function(){
                eliminar();
                return false;
            });
            
            function guardar(){
                var obj = {"metodo":"abcRectificacion"};
                obj.abc = "' . $abc . '";
                ' . $rct_id . '
                obj.gpoAbc = " ' . $gpoAbc . '";
                obj.urAbc = " ' . $urAbc . '";
                obj.tareaclc = "' . $this->prm->tareaclc . '";
                obj.tcl_id = "' . $this->prm->tcl_id . '";
                obj.clc_id = $("#clc_id").val();
                obj.justificacion = $("#justificacion").val();
                obj.fechaElaboracion = $("#fechacreacion").val();
                obj.numUr = $("#numUr").val();
                if( $("#clc_id").find("option:checked").attr("recursoFederal") == "1"){
                    $("input[name=tipo_Pago][value=2]").click().click();
                }
                switch ($("input[name=tipo_Pago]:checked").val()){
                    case "1":
                        obj.reintegro =$("input[name=tipo_Pago]:checked").val();
                        obj.importe =parseFloat(unformatNumber($("#totalRct").val()).toFixed(2));
                        obj.fechaComprobante = $("#fechaComp").val();
                        obj.numeroComprobante = $("#comprobante").val();
                    break;
                    case "2":
                        obj.reintegro =$("input[name=tipo_Pago]:checked").val();
                    break;
                }
                if(!comunica("' . $this->pantalla . '", obj,true ))
                $(this).prop("disabled",false);
            }
            
            function eliminar(){
                var elim= confirm("Estas seguro de Eliminar la Rectificación ' . ($rct->getRct_id()) . ' ");
                if(elim){
                    comunica("' . $this->pantalla . '",{
                        metodo:"abcRectificacion",
                        abc:"b",
                        numUr:$("#numUr").val(),
                        "rct_id":"' . ($rct->getRct_id()) . '",
                        "tareaclc":"' . $this->prm->tareaclc . '"
                        ' . (isset($this->prm->tipo) ?
                        ',"tipo":"' . $this->prm->tipo . '"' : '') . '
                    });
                }
            }
        ');
    }

    private function tablaRectificacionMovimientos()
    {
        if ($this->prm->tareaclc == 'generacion' && $this->usrNvj < 3) {
            $btnMovimientos = new stdClass();
            $btnMovimientos->id = 'movimientos';
            $btnMovimientos->title = 'Agregar movimiento a la rectificacion ' . $this->prm->rct_id;
            $btnMovimientos->icon = 'solid fa-plus me-2';
            $btnMovimientos->class = 'me-1';
            $btnMovimientos->color = 'verde';
            $btnMovimientos->value = ' Agregar';
            $btnMovimientos = $this->ponBoton($btnMovimientos);
        } else {
            $btnMovimientos = '';
        }

        if ($this->prm->tareaclc == 'generacion' && $this->usrNvj < 3) {
            $btnEliminar = new stdClass();
            $btnEliminar->id = 'btnEliminar';
            $btnEliminar->icon = 'solid fa-trash-can me-2';
            $btnEliminar->class = 'delete';
            $btnEliminar->color = 'rojo';
            $btnEliminar->value = ' Eliminar';
            $btnEliminar = $this->ponBoton($btnEliminar);
        } else {
            $btnEliminar = '';
        }

        if (!isset($this->prm->gpo_id)) {
            $this->prm->gpo_id = '';
        }
        if (!isset($this->prm->ur_id)) {
            $this->prm->ur_id = '';
        }
        if (!isset($this->prm->rct_gpo)) {
            $this->prm->rct_gpo = '';
        }
        if (!isset($this->prm->rct_ur)) {
            $this->prm->rct_ur = '';
        }

        $tabla = new stdClass();
        $tabla->titulo = 'Movimientos agregados a la Rectificación ';
        $tabla->id = 'aclc';
        $tabla->tools = '<div style="float:right;">' . $btnMovimientos . $btnEliminar . '</div>';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'AFEC.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'No CONTROL';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = '+/-';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaRectificacionMovimientos';
        $tabla->comunica->tareaclc = $this->prm->tareaclc;
        $tabla->comunica->clc_id = $this->prm->clc_id;
        $tabla->comunica->tcl_id = $this->prm->tcl_id;
        $tabla->comunica->rct_id = $this->prm->rct_id;
        $tabla->comunica->gpo_id = $this->prm->gpo_id;
        $tabla->comunica->ur_id = $this->prm->ur_id;
        $tabla->numFilas = 10;
        $tabla->cuerpo = [];

        $tabla->desplegable = new stdClass();
        $tabla->desplegable->metodo = 'detalleNumeroControl';
        $tabla->desplegable->titulo = 'N° Control <label id =""></label>';
        $tabla->desplegable->encabezado = 'Ver';
        $tabla->desplegable->enFila = false;
        $tabla->desplegable->prm = ['tipo_numcontrol','numcontrol','rct_reintegro','rct_id','clc_id','tareaclc','gpo_id','ur_id'];

        $tabla->multiseleccionable = true;

        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'numcontrol';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo_numcontrol';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'tipo_Movimiento';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'total';
        $td->formato = 'moneda';
        array_push($tabla->cuerpo, $td);


        $html = $this->wrPonTabla($tabla);

        if ($this->prm->tareaclc == 'generacion') {
            $this->mixScript('
                $("#movimientos").click(function(){
                    var obj = {"metodo":"muestraNumerosControl"};
                    obj.rct_id = "' . $this->prm->rct_id . '";
                    obj.gpo_id = "' . $this->prm->gpo_id . '";
                    obj.ur_id = "' . $this->prm->ur_id . '";
                    obj.rct_gpo = "' . $this->prm->rct_gpo . '";
                    obj.rct_ur = "' . $this->prm->rct_ur . '";
                    obj.clc_id = "' . $this->prm->clc_id . '";
                    if(!comunica("' . $this->pantalla . '", obj))
                        $(this).removeAttr("disabled");	
                    return false;
                });
            ');
        }

        $this->llenaTablaRectificacionMovimientos();

        $this->mixScript('
                $("#btnEliminar").click(function(){
                    if (seleccion = $("#aclc").tabla("getFilaSeleccion")){
                        envio = "";
                        mensaje="";
                        contador=0;
                        contador2=0;
                        seleccion.forEach(function(element){
                            envio +=  element.numcontrol + "-" + element.tipo_numcontrol + "/";
                            contador++;
                        });
                        
                        contador3 = contador; 
                        
                        seleccion.forEach(function(element){
                            if (contador3 > 1){
                                if (contador2==0){
                                    mensaje += " " + element.numcontrol + " , ";
                                } else {
                                    mensaje += " " + element.numcontrol + " , ";
                                }
                            } else {
                                mensaje += " " + element.numcontrol + " ";
                            }
                            contador2++;
                            contador3--;
                            
                        });
                        
                        alertify.confirm(
                            "ELIMINAR MOVIMIENTOS",
                            "Confirme la eliminación de movimiento(s) agregado(s) : "  + mensaje,
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo: "quitaMovimiento",
                                    seleccionados: envio,
                                    no_seleccionados: contador,
                                    rct_id: "' . $this->prm->rct_id . '",
                                    clc_id: "' . $this->prm->clc_id . '",
                                    gpo_id: "' . $this->prm->gpo_id . '",
                                    ur_id: "' . $this->prm->ur_id . '"
                                });
                            },
                            function(){
                            }
                        ).set("defaultFocus", "cancel").set("labels", {ok: "Proceder", cancel: "Cancelar"});
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                                    if (btnAceptar) {
                                        btnAceptar.classList.remove("btn-primary");
                                        btnAceptar.classList.add("btn-success");
                                    }
                    }
                });
            ');
        return $html;
    }

    private function llenaTablaRectificacionMovimientos()
    {
        $this->prm->rct_reintegro = (isset($this->prm->rct_reintegro)) ? $this->prm->rct_reintegro : '';
        $params = [];
        if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj > 2) {
            array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
            $filtro = 'WHERE  gpo_id = ? and ur_id = ? ' ;
        } else {
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = ' WHERE  gpo_id = ? and ur_id = ? ';
        }

        $this->conectar();
        $params2 = [];
        $params2 = array_merge($params, [$this->prm->rct_id], $params, [$this->prm->rct_id], $params, [$this->prm->rct_id]);
        $this->setSql('
            SELECT gpo_id, ur_id, rct_id, numero_rectifiacacion, 
                clc_id, afe_id, numcontrol, iva_id, importeIva, percepciones, retenciones, 
                importeOtras,  SUM(total) total, tipo_Movimiento, tipo_numcontrol, editado,
                rct_reintegro, tcl_id, "' . $this->prm->tareaclc . '" tareaclc, tipo, rct_reintegro
            FROM ' . BD_CTRL . '.scp_MaestroRectificacion
                INNER JOIN(
                    SELECT  gpo_id,ur_id,rct_id,rcc_id  as numero_rectifiacacion,clc_id,afe_id,ncc_id as numcontrol,
                        rc.ncc_referencia  as referencia,rc.ncc_fechaReferencia  as fechaReferencia,0 as percepcionImporteBruto,
                        0 as iva_id,0 as importeIva,rc.ncc_percepcion as percepciones,rc.ncc_retencion as retenciones,
                        rcc_importeOtras as importeOtras , 
                        rc.ncc_tipoMovimiento as tipo_Movimiento,"C" as tipo_numcontrol,rcc_editado as editado,
                        IF(rc.ncc_tipoMovimiento = "-" , rc.ncc_retencion * -1, rc.ncc_percepcion - rc.ncc_retencion) AS total,
                        case 
                            when ncc_amortizado = 1 AND (isnull(ncc_nccamortiza) OR ncc_nccamortiza = 0) then "ORIGEN"
                            when ncc_amortizado = 2 AND (isnull(ncc_nccamortiza) OR ncc_nccamortiza = 0) then "NORMAL"
                            when ncc_amortizado = 2 AND ( ncc_nccamortiza <> 0 ) then "PAGADORA"
                        end tipo
                    FROM ' . BD_CTRL . '.scp_RectificacionContable rc
                    INNER JOIN scp_Contable c USING(gpo_id,ur_id,clc_id,afe_id,ncc_id)
                    ' . $filtro . ' AND rct_id = ?
                    UNION ALL
                    SELECT gpo_id,ur_id,rct_id,rcp_id as numero_rectifiacacion,clc_id,afe_id,ncp_id as numcontrol,
                        ncp_referencia as referencia,ncp_fechaReferencia as fechaReferencia,
                        ncp_percepcionImporteBruto as percepcionImporteBruto,
                        iva_id as iva_id,ncp_importeIva as importeIva,ncp_percepcionNeto as percepciones,0 retenciones,
                        rcp_importeOtras as importeOtras,
                        "+" as tipo_Movimiento,"P" as tipo_numcontrol,rcp_editado as editado,
                        ncp_percepcionNeto as total,"" tipo
                    FROM ' . BD_CTRL . '.scp_RectificacionPresupuestal
                    ' . $filtro . ' AND rct_id = ?
                )MAESTRO USING (gpo_id,ur_id,rct_id,clc_id)
                INNER JOIN scp_Clc USING (gpo_id,ur_id,clc_id) 
            ' . $filtro . ' AND rct_id = ?
            -- GROUP BY gpo_id,ur_id,rct_id,afe_id,numcontrol
            GROUP BY gpo_id, ur_id, rct_id, afe_id, numcontrol, numero_rectifiacacion, iva_id, importeIva, percepciones, 
                retenciones, importeOtras, tipo_Movimiento, tipo_numcontrol, editado, tipo
            ORDER BY tipo_numcontrol desc, numcontrol
        ');
        $this->setParams($params2);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'aclc';

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                $(".habilitar").prop("disabled",false);
                
                $("#guardar,#delete").unbind("click");
                $("#guardar").click(function(){
                    guardar();
                    return false;
                });
                
                $("#delete").click(function(){
                    eliminar();
                    return false;
                });
            ');
        } else {
            $this->mixScript('
                $(".habilitar").prop("disabled",true);
                $("#delete,#guardar").remove();
            ');
            array_push($params, $this->prm->rct_id);
            $this->setSql('
                SELECT ncc_amortizado,IF(ISNULL(ncc_nccamortiza),0,ncc_nccamortiza) ncc_nccamortiza
                FROM  ' . BD_CTRL . '.scp_RectificacionContable
                JOIN scp_Contable ncc USING(gpo_id,ur_id,clc_id,afe_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,rfc_id)
                ' . $filtro . ' AND rct_id = ?
                HAVING 
                (ncc_amortizado = 1 AND ncc_nccamortiza = 0 )OR
                (ncc_amortizado = 2 AND ncc_nccamortiza != 0 )
                limit 1
                ;
            ');

            $this->consultar($params);
            if ($this->getNumeroRegistros() == 1) {
                $this->mixScript('$("#excelPagadoras").show();');
            } else {
                $this->mixScript('$("#excelPagadoras").hide();');
            }

        }
    }

    private function muestraNumerosControl()
    {
        if ($this->usrNvj < 3) {
            $this->setContenedor('divContenedorGeneralModal');
            $tabla = new stdClass();
            $tabla->id = 'listaNumControl';
            $tabla->titulo = 'Listado de Numeros de control de la CLC ' . $this->prm->clc_id;
            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $this->pantalla;
            $tabla->comunica->metodo = 'llenaTablaNumerosControl';
            $tabla->comunica->clc_id = $this->prm->clc_id;
            $tabla->comunica->gpo_id = $this->prm->gpo_id;
            $tabla->comunica->ur_id = $this->prm->ur_id;
            $tabla->comunica->rct_id = $this->prm->rct_id;
            $tabla->numFilas = 20;
            $tabla->multiseleccionable = true;

            $tr = [];

            $th = new stdClass();
            $th->texto = 'AFECTACION';
            $th->width = '5px';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'TIPO NO. CONTROL';
            $th->width = '110px';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'NO. CONTROL';
            $th->width = '80px';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CLAVE';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'RFC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'REFERENCIA';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'FECHA REFERENCIA';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = '+/-';
            $th->width = '5px';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'IMPORTE';
            array_push($tr, $th);

            $tabla->encabezado = [$tr];

            $tabla->cuerpo = [];

            $td = new stdClass();
            $td->campoTxt = 'afe_id';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'tipoControl';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'numcontrol';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'clave';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'rfc_id';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'referencia';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'fechareferencia';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'tipo_movimiento';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'total';
            $td->formato = 'moneda';
            array_push($tabla->cuerpo, $td);

            $this->mixHtml('
                <div id="modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">' . $this->wrPonTabla($tabla) . '</div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-outline-success ajustar" id="asignar">Agregar</button>
                            <button type="button" class="btn btn-outline-danger me-1 cerrar" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');
            $this->llenaTablaNumerosControl();
            $this->mixScript('
                $(".cerrar").click(function(){ 
                    $("#modal").remove();
                });
                $("#asignar").click(function() {
                    if( seleccionados = $("#listaNumControl").tabla("getFilaSeleccion") ){
                        comunica("' . $this->pantalla . '",{
                            metodo:"guardarMovimiento",
                            rct_id:' . $this->prm->rct_id . ',
                            clc_id:' . $this->prm->clc_id . ',
                            numerosControl:seleccionados
                            ' . (($this->usrGpo . $this->usrUr == '118') ? ',gpo_id : ' . $this->prm->gpo_id . ',ur_id : ' . $this->prm->ur_id : '') . '
                        });
                    }
                    return false;
                });
                $("#modal").modal();
                $("#modal").on("hidden.bs.modal", function (e) {
                    $("#modal").remove();
                })
            ');
        }
    }

    private function llenaTablaNumerosControl()
    {
        $params = [];
        if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj > 2) {
            array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        } else {
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        }
        /* El having de la cuenta se aumento porque toda aquella CLC  */
        $params2 = [];
        $params2 = array_merge($params, [$this->prm->rct_id], $params, [$this->prm->clc_id], $params, [$this->prm->rct_id], $params, [$this->prm->clc_id]);
        $this->setSql('
            SELECT nc.*
            FROM (
                SELECT
                    afe_id,"" tcl_id,
                    "Presupuestal" tipoControl,
                    ncp_id numcontrol,
                    CONCAT(gpo_id,ur_id,ue_id,"-",prg_id,spr_id,py_id,oa_id,"-",pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio,tcp_id) clave,
                    rfc_id,
                    ncp_referencia referencia,
                    ncp_fechaReferencia fechareferencia,
                    "+" tipo_movimiento,
                    ncp_percepcionNeto total,bdo_id,
                    0 cuenta
                FROM scp_Presupuestal
                LEFT JOIN (
                    SELECT ncp_id,rcp_id
                    FROM scp_RectificacionPresupuestal 
                    WHERE ' . $filtro . ' AND rct_id = ?  
                )rnp USING(ncp_id)
                JOIN enc_Programatico USING(gpo_id,ur_id,epg_id)
                JOIN enc_ClaveFinanciamiento USING(ecf_id)
                WHERE ' . $filtro . ' 
                    AND clc_id = ? 
                    AND ISNULL(rcp_id)
                UNION
                SELECT
                    afe_id,tcl_id,
                    "Contable" tipoControl,
                    ncc_id numcontrol,
                    CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,".",cta_gpo_id,cta_ur_id,".",cta_ue_id,".",cgc_id,".",cta_id) clave,
                    rfc_id,
                    ncc_referencia referencia,
                    ncc_fechaReferencia fechareferencia,
                    ncc_tipomovimiento tipomovimiento,
                    IF(ncc_tipomovimiento="+",ncc_percepcion,ncc_retencion) total,bdo_id,
                    IF(ger_id=2 AND cgp_id=1 AND rub_id=1 AND cgcu_id=7 AND cgsc_id=1 AND cgc_id=018 AND ter_id="A",1,0) AS cuenta
                FROM scp_Contable
                JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                INNER JOIN enc_ClaveFinanciamiento Using(ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id)
                LEFT JOIN (
                    SELECT ncc_id,rcc_id
                    FROM ' . BD_CTRL . '.scp_RectificacionContable
                    WHERE ' . $filtro . ' 
                        AND rct_id = ?
                )rnc USING(ncc_id)
                WHERE ' . $filtro . ' 
                    AND clc_id = ? 
                    AND ISNULL(rcc_id)
                )nc
                JOIN cat_BancoDocumentos USING(bdo_id)
                HAVING cuenta=0
            ;
        ');

        $this->setParams($params2);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listaNumControl';
        return $this->wrLlenaTabla($nav);
    }

    private function guardarMovimiento()
    {
        $ncps = $nccs = '';
        $params = [];
        if ($this->usrNvj < 3) {
            $ip = $this->usrIp;

            array_push($params, $this->usrGpo, $this->usrUr, $this->prm->rct_id);
            $ur = ' gpo_id = ? AND ur_id = ? ';

            $this->conectar();
            $this->setSql('
                SELECT ecl_id
                FROM ' . BD_CTRL . '.scp_MaestroRectificacion
                WHERE ' . $ur . '
                AND rct_id = ?
                AND ecl_id IN(1);
            ');
            $this->consultar($params);

            $paramsP = [];
            $paramsC = [];
            if ($this->getNumeroRegistros() > 0) {
                foreach ($this->prm->numerosControl as $nc) {
                    switch (strtoupper($nc->tipoControl)) {

                        case 'PRESUPUESTAL':
                            $ncps .= ($ncps == '') ? '' : ', ';
                            array_push($paramsP, $nc->numcontrol);
                            $ncps .= '?';
                            break;
                        case 'CONTABLE':
                            $nccs .= ($nccs == '') ? '' : ', ';
                            array_push($paramsC, $nc->numcontrol);
                            $nccs .= '?';
                            break;
                    }
                }

                if ($ncps != '') {
                    array_unshift($paramsP, $this->usrGpo, $this->usrUr, $this->prm->clc_id);
                    $this->setSql('
                        INSERT INTO scp_RectificacionPresupuestal(gpo_id,ur_id,rct_id,clc_id,afe_id,ncp_id,ue_id,epg_id,pg_id,pe_id,ecf_id,tcp_id,rfc_id,bdo_id,ncp_clave,ncp_concepto,ncp_referencia,ncp_fechaReferencia,ncp_percepcionImporteBruto,iva_id,ncp_importeIva,ncp_percepcionNeto,ncp_fechaInicio,ncp_fechaFin,rcp_importeOtras,usr_id)
                        SELECT gpo_id,ur_id,' . $this->prm->rct_id . ' rct_id,clc_id,afe_id,ncp_id,p.ue_id,p.epg_id,pg_id,pe_id,p.ecf_id,p.tcp_id,rfc_id,bdo_id,"",ncp_concepto,ncp_referencia,ncp_fechaReferencia,0,iva_id,0,0,afe_fechaInicio,afe_fechaFin,0,' . $this->usrId . '
                        FROM scp_Presupuestal p
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        WHERE  ' . $ur . ' AND clc_id = ? AND ncp_id IN(' . $ncps . ');
                    ');
                    $this->consultar($paramsP);
                }

                if ($nccs != '') {
                    array_unshift($paramsC, $this->usrGpo, $this->usrUr, $this->prm->clc_id);
                    $this->setSql('
                        INSERT INTO ' . BD_CTRL . '.scp_RectificacionContable (gpo_id,ur_id,rct_id,clc_id,afe_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,rfc_id,bdo_id,ncc_clave,ncc_concepto,ncc_referencia,ncc_fechaReferencia,ncc_percepcion,ncc_retencion,ncc_tipoMovimiento,ncc_fechaInicio,ncc_fechaFin,rcc_importeOtras,usr_id)
                        SELECT gpo_id,ur_id,' . $this->prm->rct_id . ' rct_id,clc_id,afe_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,rfc_id,bdo_id,"",ncc_concepto,ncc_referencia,ncc_fechaReferencia,0,0,ncc_tipoMovimiento,afe_fechaInicio,afe_fechaFin,0,' . $this->usrId . '
                        FROM scp_Contable
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        WHERE  ' . $ur . ' 
                        AND clc_id = ? 
                        AND ncc_id  IN(' . $nccs . ');
                    ');
                    $this->consultar($paramsC);
                }

                $observacion = ($ncps != '') ? 'NUMEROS DE CONTROL PRESUPUESTAL ' . $ncps : '';
                $observacion .= ($observacion != '' && $nccs != '') ? ' y los ' : '';
                $observacion .= ($nccs != '') ? 'NUMEROS DE CONTROL CONTABLES ' . $nccs : '';
                $params = [($this->usrNvj <= 2 ? $this->usrGpo : $this->admGpo), ($this->usrNvj <= 2 ? $this->usrUr : $this->admUr), $this->prm->rct_id, $this->prm->clc_id, $observacion, $ip, $this->usrId];
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion (gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id) 
                    VALUES (?,?,?,?,1,?,
                        INET_ATON(?),?)';
                $this->setSql($sql);
                $this->consultar($params);

            } else {
                $this->mixScript('notify("No se pueden agregar numeros de control de la rectificacion ' . $this->prm->rct_id . '","error");');
            }
            $this->desconectar();
            $this->mixScript('$("#modal").remove();');
            $this->prm->tareaclc = 'generacion';
            $this->actualizaImportes();
            $this->mixScript($this->wrLlamaLlenaTabla('aclc') . '
                $("#bpaclc1").click();
            ');

        }
    }

    private function quitaMovimiento()
    {

        if ($this->usrNvj < 3) {

            $this->conectar();
            $params = [];
            if ($this->usrGpo . $this->usrUr == 118) {
                array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
                $ur = 'gpo_id = ? AND ur_id = ? ';
            } else {
                array_push($params, $this->usrGpo, $this->usrUr);
                $ur = 'gpo_id = ? AND ur_id = ? ';
            }
            array_push($params, $this->prm->rct_id);
            $this->setSql('
                SELECT 
                    ecl_id 
                FROM ' . BD_CTRL . '.scp_MaestroRectificacion 
                WHERE ' . $ur . ' AND rct_id = ? AND ecl_id IN(1);
            ');
            $this->consultar($params);
            $this->desconectar();
            $ecl = $this->extraer();
            array_push($params, $this->prm->clc_id);
            if ($this->getNumeroRegistros() > 0) {
                $contador = 0;
                if (isset($this->prm->seleccionados)) {

                    $arr1 = explode('/', $this->prm->seleccionados);

                    do {
                        $paramsP = $params;
                        $paramsC = $params;
                        $ncps = $nccs = '';
                        $arr2 = '';
                        $arr2 = explode('-', $arr1[$contador]);
                        for ($i = 0; $i < 2; $i++) {
                            $this->mixScript('console.log("' . $arr2[$i] . '");');
                        }

                        switch (strtoupper($arr2[1])) {
                            case 'P':
                                array_push($paramsP, $arr2[0]);
                                $ncps .= '?';
                                break;
                            case 'C':
                                array_push($paramsC, $arr2[0]);
                                $nccs .= '?';
                                break;
                        }

                        $this->conectar();
                        if ($ncps != '') {
                            $this->setSql('
                                DELETE FROM scp_RectificacionPresupuestal 
                                WHERE ' . $ur . ' AND rct_id = ? 
                                AND clc_id = ?                                  
                                AND ncp_id IN (' . $ncps . ');
                            ');
                            $this->consultar($paramsP);
                        }

                        if ($nccs != '') {
                            $this->setSql('
                                DELETE FROM ' . BD_CTRL . '.scp_RectificacionContable 
                                WHERE  ' . $ur . ' 
                                AND rct_id = ? 
                                AND clc_id = ?
                                AND ncc_id IN (' . $nccs . ');
                            ');
                            $this->consultar($paramsC);
                        }
                        $this->desconectar();

                        $contador++;
                    } while ($contador < $this->prm->no_seleccionados);

                    if ($this->prm->no_seleccionados > 1) {

                        $this->mixScript('notify("Se eliminaron correctamente los movimientos agregados");');
                    } else {
                        $this->mixScript('notify("Se eliminó correctamente el movimiento agregado");');
                    }
                    $this->mixScript($this->wrLlamaLlenaTabla('aclc') . '
                        $("#bpaclc1").click();
                    ');
                }
            } else {
                $this->mixScript('notify("No se puede eliminar numeros de control de la rectificacion ' . $this->prm->rct_id . '","error");');
            }
            $this->actualizaImportes();
        }
    }

    private function actualizaImportes()
    {
        if ($this->usrGpo . $this->usrUr == '118') {
            $params = [$this->prm->gpo_id, $this->prm->ur_id];
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        } else {
            $params = [$this->usrGpo, $this->usrUr];
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        }
        $params2 = array_merge($params, [$this->prm->rct_id], $params, [$this->prm->rct_id], $params, [$this->prm->rct_id]);

        $this->conectar();
        $this->setSql('
            UPDATE ' . BD_CTRL . '.scp_MaestroRectificacion
            LEFT JOIN(
                SELECT gpo_id,ur_id,rct_id,SUM(percepcion)percepcion, SUM(retencion)retencion
                FROM (
                    SELECT gpo_id,ur_id,rct_id,ncp_percepcionNeto percepcion, 0 retencion
                    FROM ' . BD_CTRL . '.scp_RectificacionPresupuestal 
                    WHERE ' . $filtro . ' AND rct_id = ?
                    UNION ALL
                    SELECT gpo_id,ur_id,rct_id,ncc_percepcion percepcion,ncc_retencion retencion
                    FROM ' . BD_CTRL . '.scp_RectificacionContable 
                    WHERE ' . $filtro . ' AND rct_id = ?
                )mov
                GROUP BY gpo_id,ur_id,rct_id
            )movimientos USING(gpo_id,ur_id,rct_id)
            SET  rct_percepcion = IF(percepcion IS NULL, 0, percepcion),rct_retencion = IF(retencion IS NULL, 0, retencion)
            WHERE ' . $filtro . ' AND rct_id = ? ;
        ');

        $this->consultar($params2);
        $this->desconectar();
    }

    private function actualizaImporte()
    {
        if ($this->usrNvj < 3) {

            if ($this->usrGpo . $this->usrUr == '118') {
                $params = [$this->prm->gpo_id, $this->prm->ur_id];
                $filtro = 'gpo_id = ? AND ur_id = ? ';
            } else {
                $params = [$this->usrGpo, $this->usrUr];
                $filtro = 'gpo_id = ? AND ur_id = ? ';
            }
            $params2 = array_merge($params, [$this->prm->rct_id], $params, [$this->prm->rct_id]);
            $this->conectar();
            $this->setSql('
                SELECT SUM(percepcion) - SUM(retencion) total
                FROM (
                SELECT gpo_id,ur_id,rct_id,ncp_percepcionNeto percepcion, 0 retencion 
                FROM ' . BD_CTRL . '.scp_RectificacionPresupuestal 
                WHERE ' . $filtro . ' AND rct_id = ?
                UNION ALL
                SELECT gpo_id,ur_id,rct_id,ncc_percepcion percepcion, ncc_retencion retencion 
                FROM ' . BD_CTRL . '.scp_RectificacionContable 
                WHERE ' . $filtro . ' AND rct_id = ?
                )a GROUP BY gpo_id,ur_id,rct_id;
            ');
            $this->consultar($params2);
            $importe = $this->extraer();
            $this->desconectar();
            if ($importe['total'] <= $this->prm->importe) {
                $this->prm->abc = 'c';
                $this->abcRectificacion();
            } else {
                $this->mixScript('notify("El importe no puede ser menor al total neto de los movimientos de la rectificacion","warning")');
            }
        }
    }

    private function abcRectificacion()
    {

        $estado = '';
        $ejecutaABC = true;
        $mensaje = '';
        $ip = $this->usrIp;
        $params = [];

        // el grupo 1 y la ur 18 pertenece a la secretaria de administracion - administracion de recursos humanos
        if ($this->usrGpo . $this->usrUr == '118') {
            $this->prm->gpo_id = $this->usrGpo;
            $this->prm->ur_id = $this->usrUr;
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = 'gpo_id = ? AND ur_id = ? ' . $estado . ' AND rct_gpo IS NULL AND rct_ur IS NULL ';
        } else {
            $this->prm->gpo_id = $this->usrGpo;
            $this->prm->ur_id = $this->usrUr;
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = '
                gpo_id = ? 
                AND ur_id = ? 
                ' . $estado . ' 
                AND rct_gpo IS NULL 
                AND rct_ur IS NULL 
            ';
        }
        if (isset($this->prm->gpo)) {
            unset($this->prm->gpo);
        }
        if (isset($this->prm->ur)) {
            unset($this->prm->ur);
        }
        if (isset($this->prm->reintegro) && $this->prm->reintegro == 2) {
            $this->prm->numeroComprobante = '0';
            $this->prm->fechaComprobante = 'NULL';
        }

        $this->conectar();

        //04-03-2019 @Jordy se verifica si es de recurso federal o estatal
        if (isset($this->prm->abc) && $this->prm->abc == 'a' && $this->usrNvj <= 2) {
            // 04-03-2019 @Jordy si recursoFederal es 1 = federal y si es 0 = estatal
            $isFederal = new AccesoDatos();
            $isFederal->conectar();
            $params2 = [$this->prm->gpoAbc, $this->prm->urAbc, $this->prm->clc_id];
            $isFederal->setSql('
                SELECT 
                    IF(clc_recursoFederal = 1,1,0) AS recursoFederal
                FROM scp_Clc
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND clc_id = ? ');
            $isFederal->consultar($params2);
            $tempFederal = $isFederal->extraer();
            if ($isFederal->getNumeroRegistros() == 0) {
                $this->mixScript(' notify("No se encuentra la CLC, no se puede seguir con el proceso de rectificacion","warning"); ');
                return false;
            }
            $this->prm->federal = $tempFederal['recursoFederal'] ;
            $isFederal->desconectar();
            unset($this->prm->gpoAbc);
            unset($this->prm->urAbc);
            unset($tempFederal);
            unset($isFederal);
        } elseif (isset($this->prm->federal) && !is_null($this->prm->federal)) {
            unset($this->prm->federal);
        }

        $listaNuevamente = true;
        if ($this->prm->abc != 'a') {
            array_push($params, $this->prm->rct_id);
            $this->setSql(
                '
                SELECT ecl_id,clc_id 
                FROM ' . BD_CTRL . '.scp_MaestroRectificacion 
                WHERE ' . $filtro . ' 
                AND rct_id = ? '
            );
            $this->consultar($params);
            $estado = $this->extraer();
            $this->prm->clc_id = (empty($this->prm->clc_id)) ? $estado['clc_id'] : $this->prm->clc_id;
            $ejecutaABC = (int) $estado['ecl_id'] == 1;
            array_pop($params);
        }
        $ejecutaABC = true;
        if ($ejecutaABC) {
            switch ($this->prm->abc) {
                case 'a':
                    $numeroRct = ($this->usrGpo . $this->usrUr == '118') ? '' : 'rectificacionConcluida < 2 AND';
                    //Verifica que el SCP tenga el estado de pagado.
                    array_push($params, $this->prm->tcl_id, $this->prm->clc_id);
                    $this->setSql('SELECT
                            tcl_id,clc_id,SUM( if(r.ecl_id = 6,1,0) ) rectificacionConcluida,
                            SUM( if(r.ecl_id != 6,1,0) ) rectificacionEnTramite,c.ue_id
                        FROM scp_Clc c
                        LEFT JOIN ' . BD_CTRL . '.scp_MaestroRectificacion r using (gpo_id,ur_id,clc_id)
                        WHERE ' . $filtro . ' 
                        AND c.ecl_id in (18) 
                        AND tcl_id = ? 
                        AND clc_id = ?
                        GROUP BY gpo_id,ur_id,clc_id
                        HAVING ' . $numeroRct . '  rectificacionEnTramite=0;
                    ');
                    $this->consultar($params);
                    array_pop($params);
                    array_pop($params);
                    if ($this->getNumeroRegistros() > 0) {

                        $ue = $this->extraer();

                        if ($this->usrGpo . $this->usrUr == '116') {
                            $this->prm->ue_id = $ue['ue_id'];
                        }

                        $rct_id = MaestroRectificacion::abc($this->prm);

                        $observacion = 'RECTIFICACIÓN GENERADA POR LA UR';
                        $mensaje = 'generada correctamente';

                    } else {
                        $this->mixScript('notify("La CLC no es valida para Rectificar","error");');
                    }
                    break;
                case 'b':
                    //Elimina
                    array_push($params, $this->prm->rct_id);
                    $this->setSql(' 
                        DELETE FROM ' . BD_CTRL . '.scp_MaestroRectificacion 
                        WHERE ' . $filtro . ' 
                        AND rct_id = ? ; 
                    ');
                    $this->consultar($params);
                    $observacion = 'RECTIFICACIÓN ELIMINADA POR LA UR';
                    $mensaje = 'eliminada';
                    break;
                case 'c':
                    //Actualiza
                    $observacion = 'RECTIFICACIÓN MODIFICADA POR LA UR';
                    $mensaje = 'actualizada';
                    $listaNuevamente = false;
                    $rct_id = MaestroRectificacion::abc($this->prm);
                    break;
            }
            $this->prm->rct_id = (empty($rct_id)) ? $this->prm->rct_id : $rct_id;
            $this->conectar();
            if ($this->usrNvj <= 2) {
                $params = [$this->usrGpo, $this->usrUr];
            } else {
                $params = [$this->admGpo, $this->admUr];
            }
            array_push($params, $this->prm->rct_id, $this->prm->clc_id, $observacion, $ip, $this->usrId);
            $this->setSql('
                INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion ( gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                VALUES(?,?,?,?,1,?, INET_ATON(?),?,NOW());
            ');
            $this->consultar($params);
            $this->desconectar();

            if ($mensaje != '') {
                $this->mixScript(' notify("La rectificación ' . $this->prm->rct_id . ' ha sido ' . $mensaje . '","success"); ');
            }
            $this->mixScript(' $("nav").menusinpres("quitaBreadcrumbs").menusinpres("quitaBreadcrumbs"); ');
        }

        if ($listaNuevamente) {
            $this->listaRectificaciones();
        }
    }

    private function detalleNumeroControl()
    {
        $datosImportes = $filtro = $prm = '';
        $tMov = '';
        $params = $params2 = $paramsPrep = $paramsCon = [];
        $this->prm->tipo_numcontrol = isset($this->prm->tipo_numcontrol) ? $this->prm->tipo_numcontrol : '';
        $this->prm->tcl_id = (isset($this->prm->tcl_id) && $this->prm->tcl_id != '') ? $this->prm->tcl_id : '';
        if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj) {
            array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
            $prm = 'gpo_id : ' . $this->prm->gpo_id . ', ur_id : ' . $this->prm->ur_id . ',';
        } else {
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        }
        $lectura = $this->prm->tareaclc != 'generacion';
        $this->conectar();

        switch ($this->prm->tipo_numcontrol) {
            case 'P':
                $params2 = array_merge($params, [$this->prm->rct_id], $params, [$this->prm->rct_id, $this->prm->numcontrol], $params, [$this->prm->rct_id, $this->prm->numcontrol]);
                $this->setSql('
                    SELECT rcp_id numcontrol, rcp.ncp_concepto, rcp.ncp_fechaReferencia fechaReferencia,
                        rcp.ncp_referencia referencia,rcp.ncp_percepcionNeto importerectificado,rcp.ncp_fechaInicio
                        fechaInicio,rcp.ncp_fechaFin fechaFin, rct_importe, ncp.ncp_percepcionBruto,ncp.iva_id,ncp.ncp_percepcionIva,
                        ncp.ncp_percepcionNeto,if(ISNULL(importeRectificion1),0,importeRectificion1) importeRectificion1,
                        pe_nombre nombre_clave, 
                        CONCAT(gpo_id,ur_id,rcp.ue_id,"-",prg_id,spr_id,py_id,oa_id,"-",pg_id,pe_id,cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) clave,
                        iva_porcentaje, rfc_id,rfc_nombre
                    FROM scp_RectificacionPresupuestal rcp
                    JOIN( SELECT gpo_id,ur_id,rct_id,rct_importe FROM ' . BD_CTRL . '.scp_MaestroRectificacion 
                    WHERE ' . $filtro . ' AND rct_id = ? )maestro USING(gpo_id,ur_id,rct_id)
                    JOIN scp_Presupuestal ncp USING(gpo_id,ur_id,ue_id,epg_id,ncp_id,pg_id,pe_id,ecf_id,rfc_id,clc_id)
                    LEFT JOIN( SELECT gpo_id,ur_id,ncp_id,ncp_percepcionNeto importeRectificion1 FROM scp_RectificacionPresupuestal 
                    WHERE  ' . $filtro . ' AND rct_id != ? AND ncp_id =  ? )rct1 USING(gpo_id,ur_id,ncp_id)
                    JOIN cat_PartidaEspecifica USING(pg_id,pe_id)
                    JOIN enc_Programatico USING(gpo_id,ur_id,epg_id)
                    JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    JOIN
                        (
                            SELECT rfc_id,CONCAT(rfc_nombre," ",rfc_paterno," ",rfc_materno) rfc_nombre
                            FROM ' . BD_SINPRES . '.cat_Proveedores
                            INNER  JOIN cat_Proveedor USING(rfc_id)
                        ) prov USING(rfc_id)
                    JOIN cat_Iva iva ON(ncp.iva_id = iva.iva_id)
                    WHERE ' . $filtro . ' AND rct_id = ? AND ncp_id = ?;
                ');

                $this->mixScript('$("#modalDeTablaaclc .modal-title").html("Numero de Control <label>PRESUPUESTAL ' . $this->prm->numcontrol . '</label>");');
                $this->consultar($params2);
                $datosRct = $this->extraer();
                $datosImportes = ($this->prm->tcl_id == 'A') ? '' : '
                    <tr>
                        <th class="table-warning">Importe</th><td>$</td>
                        <td style="text-align: right;">' . number_format($datosRct['ncp_percepcionBruto'], 2) . '</td>
                    </tr><tr>
                        <th class="table-warning">iva</th>
                        <td></td><td>' . $datosRct['iva_porcentaje'] . '</td>
                    </tr><tr>
                        <th class="table-warning">Importe Iva</th><td>$</td>
                        <td style="text-align: right;">' . number_format($datosRct['ncp_percepcionIva'], 2) . '</td>
                    </tr>
                ';
                $datosImportes .= '
                    <tr>
                        <th class="table-warning" width="160px">Importe Neto</th><td width="3px">$</td>
                        <td style="text-align: right;">' . number_format($datosRct['ncp_percepcionNeto'], 2) . '</td>
                    </tr>
                ';
                $importeDisponible = $datosRct['ncp_percepcionNeto'] - $datosRct['importeRectificion1'];
                $primeraRectificacion = $datosRct['importeRectificion1'];
                $importeOriginal = $datosRct['ncp_percepcionNeto'];
                $importeRectificado = $datosRct['importerectificado'];
                $parametros = 'monto: unformatNumber( $("#importeRectificado").val() )';
                array_push($paramsPrep, $this->prm->numcontrol, $datosRct['numcontrol']);
                $filtroControlPresupuestal = ' AND ncp_id != ? AND rcp_id != ? ';
                $filtroControlContable = '';
                break;
            case 'C':
                $params2 = [];
                $params2 = array_merge($params, [$this->prm->clc_id, $this->prm->numcontrol, $this->prm->rct_id], $params, [$this->prm->clc_id, $this->prm->numcontrol, $this->prm->rct_id]);
                $this->setSql('SELECT
                        IF(ISNULL(ncc_nccamortiza),0,ncc_nccamortiza) ncc_nccamortiza,ncc_amortizado,
                        rcc_id numcontrol,
                        rcc.ncc_concepto,
                        rcc.ncc_fechaReferencia fechaReferencia,rcc.ncc_referencia referencia,rcc.ncc_retencion retencion,rcc.ncc_percepcion percepcion,
                        rcc.ncc_fechaInicio fechaInicio,rcc.ncc_fechaFin fechaFin,
                        rct_importe,
                        ncc.ncc_tipoMovimiento,
                        ncc.ncc_retencion,ncc.ncc_percepcion,
                        if(ISNULL(retencionRectificacion1),0,retencionRectificacion1) retencionRectificacion1,
                        if(ISNULL(percepcionRectificion1),0,percepcionRectificion1) percepcionRectificion1,
                        cta_nombre nombre_clave, CONCAT(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id) clave,
                        rfc_id,rfc_nombre
                    FROM ' . BD_CTRL . '.scp_RectificacionContable rcc
                    LEFT JOIN(
                        SELECT gpo_id,ur_id,ncc_id,ncc_percepcion percepcionRectificion1, ncc_retencion retencionRectificacion1
                        FROM ' . BD_CTRL . '.scp_RectificacionContable
                        WHERE ' . $filtro . '
                        AND clc_id = ?
                        AND ncc_id = ?
                        AND rct_id != ?
                    )rct1 USING(gpo_id,ur_id,ncc_id)
                    JOIN cg_PlanCuentas USING(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id)
                    JOIN ' . BD_CTRL . '.scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id,clc_id)
                    JOIN scp_Contable ncc USING(gpo_id,ur_id,clc_id,afe_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cta_gpo_id,cta_ur_id,cta_ue_id,cgc_id,cta_id,rfc_id)
                    JOIN(
                            SELECT rfc_id,CONCAT(rfc_nombre," ",rfc_paterno," ",rfc_materno) rfc_nombre
                            FROM ' . BD_SINPRES . '.cat_Proveedores
                            INNER  JOIN cat_Proveedor USING(rfc_id)
                        ) prov USING(rfc_id)
                    WHERE ' . $filtro . '
                    AND clc_id = ? 
                    AND ncc_id = ? 
                    AND rct_id = ?;
                ');

                $this->mixScript('$("#modalDeTablaaclc .modal-title").html("Numero de Control <label>CONTABLE ' . $this->prm->numcontrol . '</label>");');
                $this->consultar($params2);
                $datosRct = $this->extraer();

                switch ($datosRct['ncc_tipoMovimiento']) {
                    case '+':
                        $tMov = 'Percepcion';
                        $importeOriginal = $datosRct['ncc_percepcion'];
                        $importeDisponible = $datosRct['ncc_percepcion'] - $datosRct['percepcionRectificion1'];
                        $primeraRectificacion = $datosRct['percepcionRectificion1'];
                        $importeRectificado = $datosRct['percepcion'];
                        $parametros = 'percepcion: unformatNumber( $("#importeRectificado").val() ),retencion:0';
                        break;
                    case '-':
                        $tMov = 'Retencion';
                        $importeOriginal = $datosRct['ncc_retencion'];
                        $importeDisponible = $datosRct['ncc_retencion'] - $datosRct['retencionRectificacion1'];
                        $primeraRectificacion = $datosRct['retencionRectificacion1'];
                        $importeRectificado = $datosRct['retencion'];
                        $parametros = 'retencion: unformatNumber( $("#importeRectificado").val() ), percepcion:0';
                        break;
                }

                $datosImportes = '
                    <tr>
                        <th class="table-warning">' . $tMov . '</th><td  width="3px">$</td>
                        <td style="text-align: right;">' . number_format($importeOriginal, 2) . '</td>
                    </tr>
                ';
                array_push($paramsCon, $this->prm->numcontrol, $datosRct['numcontrol']);
                $filtroControlContable = ' AND ncc_id != ? AND rcc_id != ? ';
                $filtroControlPresupuestal = '';
                break;
        }
        $params2 = array_merge($params, [$this->prm->rct_id], $paramsPrep, $params, [$this->prm->rct_id], $paramsCon);
        #consulta que calcula el total rectificado en los numeros de control omitiendo el que se vizualiza
        $this->setSql('SELECT SUM(percepcion)  - sum(retencion) totalRectificado
            FROM(
                SELECT sum(ncp_percepcionNeto) percepcion, 0 retencion
                FROM ' . BD_CTRL . '.scp_RectificacionPresupuestal
                WHERE  ' . $filtro . '
                AND rct_id = ? ' . $filtroControlPresupuestal . '
                GROUP BY gpo_id,ur_id,rct_id
                UNION ALL
                SELECT sum(ncc_percepcion) percepcion, sum(ncc_retencion) retencion 
                FROM ' . BD_CTRL . '.scp_RectificacionContable
                WHERE  ' . $filtro . '
                AND rct_id = ? ' . $filtroControlContable . '
                GROUP BY gpo_id,ur_id,rct_id
            )importe
        ');
        $this->consultar($params2);

        $totalRectificado = $this->extraer();
        $this->desconectar();

        $disponible1 = $datosRct['rct_importe'] - $totalRectificado['totalRectificado'];

        if ($disponible1 == 0 && $this->prm->tipo_numcontrol == 'C' && $datosRct['ncc_tipoMovimiento']) {
            $disponible1 = $totalRectificado['totalRectificado'];
        }

        $referencia = new stdClass();
        $referencia->id = 'ncp_referencia';
        $referencia->label = 'Referencia';
        $referencia->readonly = true;
        $referencia->value = $datosRct['referencia'];
        $referencia->maxlength = 80;
        $reintegro = '';

        if (isset($this->prm->tcl_id) && $this->prm->tcl_id == 'A') {
            $fechaReferencia = '';
            $periodoIni = new stdClass();
            $periodoIni->id = 'ncp_fechaInicio';
            $periodoIni->label = 'Fecha Inicial';
            $periodoIni->formato = 'fecha';
            $periodoIni->required = true;
            $periodoIni->readonly = true;
            $periodoIni->enDiv = true;
            $periodoIni->value = $this->mysqldate_to_date($datosRct['fechaInicio']);
            $periodoIni = '<fieldset class="col-md-2">' . $this->ponInput($periodoIni) . '</fieldset>';

            $periodoFin = new stdClass();
            $periodoFin->id = 'ncp_fechaFin';
            $periodoFin->label = 'Fecha Final';
            $periodoFin->readonly = true;
            $periodoFin->enDiv = true;
            $periodoFin->required = true;
            $periodoFin->formato = 'fecha';
            $periodoFin->value = $this->mysqldate_to_date($datosRct['fechaFin']);
            $periodoFin = '<fieldset class="col-md-2">' . $this->ponInput($periodoFin) . '</fieldset>';
            $parametros .= ',fechainicial: $("#ncp_fechaInicio").val(),fechafin: $("#ncp_fechaFin").val()';
        } else {
            $periodoIni = '';
            $periodoFin = '';
            $fechaReferencia = new stdClass();
            $fechaReferencia->id = 'ncp_fechaReferencia';
            $fechaReferencia->label = 'Fecha de la Referencia';
            $fechaReferencia->formato = 'fecha';
            $fechaReferencia->enDiv = true;
            $fechaReferencia->readonly = true;
            $fechaReferencia->required = true;
            $fechaReferencia->value = $this->mysqldate_to_date($datosRct['fechaReferencia']);
            $fechaReferencia = '<fieldset class="col-md-2">' . $this->ponInput($fechaReferencia) . '</fieldset>';
            $parametros .= ',fechareferencia: $("#ncp_fechaReferencia").val()';
        }

        $importe = new stdClass();
        $importe->id = 'importeRectificado';
        $importe->label = 'Monto a disminuir';
        $importe->enDiv = true;
        $importe->required = true;
        $importe->formato = 'moneda';
        $importe->positivo = true;
        $importe->value = $importeRectificado;
        $importe->blur = ' validaImporte($(this)); ';

        $montoLimite = '
            var montoLimite = formatNumber(parseFloat(montoDisponibleRectificacion).toFixed(2),"","");
            var trLimite = "#disponibleRectificacion";
            
            calculaPorRectificar();
            
            if( montoDisponibleNumControl < montoDisponibleRectificacion ){
                montoLimite = montoDisponibleNumControl;
                trLimite = "#disponibleNumControl";
            }
        ';

        if ($lectura) {
            $importe->lectura = true;
            $referencia->lectura = true;
            $btnGuardar = '';
        } else {
            $montoDisponibleRectificacion = ($this->prm->rct_reintegro != 1) ?
            ($importeOriginal - $primeraRectificacion) : ($disponible1);
            $this->mixScript('
                var calculaPorRectificar = function (){console.log("hi");};
                function validaImporte(elemento){
                    var montoNumControl = unformatNumber( elemento.val() );
                    var montoDisponibleRectificacion = ' . ($montoDisponibleRectificacion) . ' ;
                    var montoDisponibleNumControl = ' . ($importeOriginal - $primeraRectificacion) . ';
                    ' . $montoLimite . '
                    if( montoNumControl ' . (($tMov == 'Retencion') ? '* -1 ' : '') . ' > montoLimite ){
                        notify("El monto ingresado supera el importe disponible a rectificar","error");
                        var warningMonto = setInterval(function () { $(trLimite).toggleClass("danger"); }, 200);
                        setTimeout(function () { clearInterval(warningMonto); }, 3000);
                        setTimeout(function () { clearInterval(warningMonto); $(trLimite).removeClass("danger");}, 3500);
                        return false;
                    }else if( montoNumControl == 0){
                        return false;
                    }else return true;
                }
                $("#importeRectificado").attr("data-required","funcion[validaImporte]");
            ');

            $btnGuardar = new stdClass();
            $btnGuardar->color = 'verde';
            $btnGuardar->class = 'me-1';
            $btnGuardar->icon = 'floppy-disk me-2';
            $btnGuardar->id = 'guardarCambio';
            $btnGuardar->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"guardaRectificacion",
                    ' . $prm . '
                    tipo:"' . $this->prm->tipo_numcontrol . '",
                    rct_id:' . $this->prm->rct_id . ',
                    tcl_id:"' . $this->prm->tcl_id . '",
                    tareaclc:"' . $this->prm->tareaclc . '",
                    numcontrol:' . $datosRct['numcontrol'] . ',
                    referencia: $("#ncp_referencia").val(),
                    ' . (isset($this->prm->numcontrol) && $this->prm->numcontrol != '' ?
                    'ncp_id: ' . $this->prm->numcontrol . ',' : '') . '
                    ' . $parametros . '
                },true);
            ';
            $btnGuardar->value = ' Guardar';
            $btnGuardar = $this->ponBoton($btnGuardar);
        }

        if ($this->prm->rct_reintegro != 1) {
            $parametros .= ',rct_reintegro : 3';
            $montoLimite = '
                calculaPorRectificar();
                montoLimite = montoDisponibleNumControl;
                trLimite = "#disponibleNumControl";
            ';
            $referencia = '';
            $datosRectificacion = '';
        } else {
            $datosRectificacion = '
                <tr>
                    <th>Importe total a rectificar</th>
                    <td>$</td>
                    <td style="text-align: right;">' . number_format($datosRct['rct_importe'], 2) . '</td>
                </tr><tr>
                    <th>total rectificado <small>(otros mov.)</small></th>
                    <td>$</td>
                    <td style="text-align: right;">' . number_format($totalRectificado['totalRectificado'], 2) . '</td>
                </tr><tr id="disponibleRectificacion">
                    <th>Disponible por rectificar</th>
                    <td>$</td>
                    <td style="text-align: right;">' . number_format($disponible1, 2) . '</td>
                </tr>
            ';
            $referencia = '<fieldset class="col-md-12"> ' . $this->ponInput($referencia) . ' </fieldset>';
        }
        $reintegro = '';
        $numeroRectificacion = '';

        $this->mixHtml('
            <div class="row">
                <fieldset class="col-md-8 mb-4">
                    <p>
                        Rectificacion: <label>' . $this->prm->rct_id . '</label>
                    </p>
                    <p>
                        Clave : <label>' . $datosRct['clave'] . '</label>
                        <label>' . $datosRct['nombre_clave'] . '</label>
                    </p>
                    <p>
                        CLC : <label>' . $this->prm->clc_id . '</label>
                        Concepto: <label>' . (isset($datosRct['ncp_concepto']) ? $datosRct['ncp_concepto'] : '') . '</label>
                    </p>
                    <p>
                        Proveedor : <label>' . $datosRct['rfc_id'] . ' - ' . $datosRct['rfc_nombre'] . '</label>
                    </p>
                </fieldset>
                <fieldset class="col-md-4">
                    <table class="table table-bordered">
                        ' . $datosImportes . '
                        <tr>
                            <th class="table-warning">Primera rectificacion</th><td>$</td>
                            <td style="text-align: right;">' . number_format($primeraRectificacion, 2) . '</td>
                        </tr><tr id="disponibleNumControl">
                            <th class="table-warning">Disponible a disminuir</th><td>$</td>
                            <td style="text-align: right;">' . number_format($importeDisponible, 2) . '</td>
                        </tr>' . $datosRectificacion . '
                    </table>
                </fieldset>
            </div>
            <fieldset>
                <legend>' . $numeroRectificacion . '</legend>
                ' . $reintegro . '
                <div class="row"></div>
                <div class="row">
                    <fieldset class="col-md-3 mb-4"> ' . $this->ponInput($importe) . ' </fieldset>
                    ' . $referencia . '
                    ' . $periodoIni . $periodoFin . '
                </div>
            </fieldset>
            ' . $btnGuardar . '
        ');
        $params = [];
        if ($this->prm->tipo_numcontrol == 'C') {
            # ncc_amortizado = 1 y ncc_nccamortiza = 0 ORIGEN
            # ncc_amortizado = 2 y ncc_nccamortiza = 0 NORMAL
            # ncc_amortizado = 2 y ncc_nccamortiza != 0 PAGADORA,AMORTIZADORA
            $consultar = true;

            switch ($datosRct['ncc_amortizado'] . $datosRct['ncc_nccamortiza']) {
                case '10':#se busca la o las pagadoras relacionadas a este numero de control
                    array_push($params, $this->prm->numcontrol);
                    $where = 'AND ncc_nccamortiza = ? ';
                    break;
                case '20':#En caso de ser normal no se efectua ninguna busqueda
                    $consultar = false;
                    break;
                default:#se busca la original vinculada a
                    array_push($params, $datosRct['ncc_nccamortiza']);
                    $where = 'AND ncc_id = ? ';
                    break;
            }

            if ($consultar) {
                $this->mixHtml('
                    <table class = "table table-bordered table-warning"">
                        <caption class="warning"><h4>Numero(s) de control de la CLC Pagadora/Amortizadora</h4></caption>
                        <thead>
                            <tr class="table-warning">
                                <th>Tipo Clc</th>
                                <th>CLC</th>
                                <th>Afectacion</th>
                                <th>Num. Control</th>
                                <th>Percepción</th>
                                <th>Retención</th>
                                <th>Rectificado</th>
                                <th>Pagado - rectificacion</th>
                                <th>Importe a rectificar</th>
                            </tr>
                        </thead>
                        </tbody>
                ');
                $this->conectar();
                $this->setSql('
                    SELECT
                        gpo_id,ur_id,clc_id,afe_id,ncc_id,tcl_id,tcl_nombre,
                        c.ncc_tipoMovimiento,c.ncc_retencion,c.ncc_percepcion,
                        if(c.ncc_tipoMovimiento = "+",c.ncc_percepcion, c.ncc_retencion) importeOriginal,
                        SUM( if(rc.ncc_tipoMovimiento = "+",rc.ncc_percepcion, IF(ISNULL(rcc_id),0,rc.ncc_retencion) ) ) importeRectificado
                    FROM scp_Contable c
                    JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,afe_id)
                    JOIN cat_TipoClc USING(tcl_id)
                    LEFT JOIN ' . BD_CTRL . '.scp_RectificacionContable rc USING(gpo_id,ur_id,clc_id,afe_id,ncc_id)
                    WHERE eaf_id = "A" ' . $where . '
                    GROUP BY gpo_id,ur_id,clc_id,afe_id,ncc_id;
                ');

                $this->consultar($params);
                $percepcionTotal = 0;
                $retencionTotal = 0;
                $rectificacionTotal = 0;

                while ($datosPagadora = $this->extraer()) {
                    $percepcionTotal += $datosPagadora['ncc_percepcion'];
                    $retencionTotal += $datosPagadora['ncc_retencion'];
                    $rectificacionTotal += $datosPagadora['importeRectificado'];
                    $porRectificar = ($datosPagadora['importeOriginal'] - $datosPagadora['importeRectificado'] > $importeDisponible - $importeRectificado) ?
                    (($datosPagadora['importeOriginal'] - $datosPagadora['importeRectificado']) - ($importeDisponible - $importeRectificado)) : 0;

                    $this->mixHtml('
                        <tr>
                            <td>' . $datosPagadora['tcl_id'] . ' - ' . $datosPagadora['tcl_nombre'] . '</td>
                            <td>' . $datosPagadora['clc_id'] . '</td>
                            <td>' . $datosPagadora['afe_id'] . '</td>
                            <td>' . $datosPagadora['ncc_id'] . '</td>
                            <td>' . number_format($datosPagadora['ncc_percepcion'], 2) . '</td>
                            <td>' . number_format($datosPagadora['ncc_retencion'], 2) . '</td>
                            <td>' . number_format($datosPagadora['importeRectificado'], 2) . '</td>
                            <td>' . number_format(($datosPagadora['importeOriginal'] - $datosPagadora['importeRectificado']), 2) . '</td>
                            <td id="porRectificar" ' . (($porRectificar > 0) ?
                            'class="error"' : '') . '>' . number_format($porRectificar, 2) . '</td>
                        </tr>
                        <tr>
                        </tr>
                    ');
                }

                $this->desconectar();

                $this->mixHtml('
                        </tbody>
                    </table>
                    <row><label id="mensajito" class="control-label" style="float:right; color:#FF0000;"></label></row>
                ');

                $importeOriginal = ($percepcionTotal > 0) ? $percepcionTotal : $retencionTotal;

                $this->mixScript('
                    calculaPorRectificar = function (){
                        var importeRectificado = unformatNumber( $("#importeRectificado").val() );
                        var importeDisponible = ' . $importeDisponible . ' - importeRectificado;
                        if(' . ($importeOriginal - $rectificacionTotal) . ' > importeDisponible ){
                            $("#mensajito").html("Es necesario Rectificar la CLC Pagadora/Amortizadora");
                            var a = ' . ($importeOriginal - $rectificacionTotal) . ';
                            var b = importeDisponible;
                            var r = a-b;
                            $("#porRectificar").addClass("error").text( formatNumber( r.toFixed(2) ) );
                            var warningporRectificar = setInterval(function () { $("#porRectificar").toggleClass("error"); }, 200);
                            setTimeout(function () { clearInterval(warningporRectificar); }, 2000);
                            setTimeout(function () { clearInterval(warningporRectificar); $("#porRectificar").addClass("error")}, 2300);
                        }else{
                            $("#mensajito").html("");
                            $("#porRectificar").removeClass("error").text( formatNumber(0) );
                        }
                    }
                ');
            }
        }
    }

    private function guardaRectificacion()
    {
        $ncp = isset($this->prm->ncp_id) && $this->prm->ncp_id != '' ? $this->prm->ncp_id : 0;
        $params = $params2 = [];
        $consultaP = $consultaC = '';

        if ($this->usrGpo . $this->usrUr == '118') {
            array_push($params, $this->prm->gpo_id, $this->prm->ur_id);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        } else {
            array_push($params, $this->usrGpo, $this->usrUr);
            $filtro = 'gpo_id = ? AND ur_id = ? ';
        }

        if (!isset($this->prm->fechainicial)) {
            $this->prm->fechainicial = '';
        }

        if (!isset($this->prm->fechafin)) {
            $this->prm->fechafin = '';
        }
        /*VALIDAMOS QUE LOS IMPORTES A RECTIFICAR NO SEAN NEGATIVOS*/
        if (isset($this->prm->monto) && bccomp($this->prm->monto, 0.00, 2) < 0) {
            $this->mixScript('notify("El importe a rectificar no puede ser negativo","error");');
            return false;
        }
        if (isset($this->prm->percepcion) && bccomp($this->prm->percepcion, 0.00, 2) < 0) {
            $this->mixScript('notify("El importe a rectificar no puede ser negativo","error");');
            return false;
        }
        if (isset($this->prm->retencion) && bccomp($this->prm->retencion, 0.00, 2) < 0) {
            $this->mixScript('notify("El importe a rectificar no puede ser negativo","error");');
            return false;
        }

        $this->conectar();
        switch (strtoupper($this->prm->tipo)) {
            case 'P':
                /*VERIFICAMOS SI LOS IMPORTES A RECTIFICAR NO SUPERAN AL IMPORTE AFECTADO DEL NUMERO DE CONTROL PRESUPUESTAL*/
                $this->setSql('
                    SELECT ncp_percepcionNeto
                    FROM scp_Presupuestal
                    WHERE ' . $filtro . '
                    AND ncp_id = ?
                ');
                array_push($params, $ncp);
                $this->consultar($params);
                $extrae = $this->extraer();
                $percepcionNeto = $extrae['ncp_percepcionNeto'];

                if (bccomp($this->prm->monto, $percepcionNeto, 2) == 1) {
                    $this->mixScript('
                        notify("El importe a rectificar excede el importe afectado del número de control","error");
                    ');
                    return false;
                }
                array_push($params2, $this->prm->monto);
                if (isset($this->prm->referencia)) {
                    array_push($params2, $this->prm->referencia);
                    $consultaP .= ', ncp_Referencia = ? ';
                }
                if ($this->prm->tcl_id == 'A') {
                    array_push($params2, $this->date_to_mysqldate($this->prm->fechainicial), $this->date_to_mysqldate($this->prm->fechafin));
                    $consultaP .= ', ncp_fechaInicio = ?, ncp_fechaFin = ? ';
                }
                array_pop($params);
                $params2 = array_merge($params2, $params, [$this->prm->rct_id, $this->prm->numcontrol]);
                $this->setSql('
                    UPDATE scp_RectificacionPresupuestal 
                    JOIN ' . BD_CTRL . '.scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id)
                    SET ncp_percepcionNeto = ? ' . $consultaP . '
                    WHERE ' . $filtro . '
                    AND rct_id = ?
                    AND rcp_id = ?
                    AND ecl_id = 1
                ');
                break;
            case 'C':
                array_push($params, $ncp);
                $this->setSql('
                    SELECT ncc_percepcion,ncc_retencion 
                    FROM scp_Contable
                    WHERE ' . $filtro . '
                    AND ncc_id = ?
                ');
                $this->consultar($params);
                $extrae = $this->extraer();
                $percepcion = $extrae['ncc_percepcion'];
                $retencion = $extrae['ncc_retencion'];
                /*VERIFICAMOS SI LOS IMPORTES A RECTIFICAR DE PERCEPCION Y RETENCION NO EXCEDEN EL IMPORTE ORIGINAL DEL NUMERO DE CONTROL CONTABLE*/
                if (bccomp($this->prm->percepcion, $percepcion, 2) == 1 || bccomp($this->prm->retencion, $retencion, 2) == 1) {
                    $this->mixScript('
                        notify("El importe a rectificar excede el importe afectado del número de control","error");
                    ');
                    return false;
                }

                array_pop($params);
                $params2 = [];
                array_push($params2, $this->prm->percepcion, $this->prm->retencion);
                if (isset($this->prm->referencia)) {
                    array_push($params2, $this->prm->referencia);
                    $consultaC .= ', ncc_Referencia = ?';
                }
                if ($this->prm->tcl_id == 'A') {
                    array_push($params2, $this->date_to_mysqldate($this->prm->fechainicial), $this->date_to_mysqldate($this->prm->fechafin));
                    $consultaC .= ', ncc_fechaInicio = ?, ncc_fechaFin = ?';
                }
                $params2 = array_merge($params2, $params, [$this->prm->rct_id, $this->prm->numcontrol]);
                $this->setSql('
                    UPDATE ' . BD_CTRL . '.scp_RectificacionContable
                    JOIN ' . BD_CTRL . '.scp_MaestroRectificacion USING(gpo_id,ur_id,rct_id)
                    SET ncc_percepcion = ?,
                        ncc_retencion = ? ' . $consultaC . '
                    WHERE ' . $filtro . ' 
                    AND rct_id = ? 
                    AND rcc_id = ?  
                    AND ecl_id = 1
                ');
                break;
        }
        // update a maestro rectificacion
        $this->consultar($params2);

        if (isset($this->prm->rct_reintegro)) {
            array_unshift($params, $this->prm->rct_reintegro);
            array_push($params, $this->prm->rct_id);
            $this->setSql('
                UPDATE ' . BD_CTRL . '.scp_MaestroRectificacion
                SET rct_reintegro = ?
                WHERE ' . $filtro . ' AND rct_id = ?;
            ');
            $this->consultar($params);
        }
        $this->desconectar();
        $this->actualizaImportes();

        $this->mixScript('
            $("#modalDeTablaaclc").remove();
            notify("Se han guardado los cambios correctamente","success");
        ');

        $this->prm->nav = new stdClass();
        $this->prm->nav->nfDefault = 10;

        $this->llenaTablaRectificacionMovimientos();
    }

    private function autorizarRectificacion()
    {
        $html = '';
        $html2 = '';
        $valido = true;
        $arrayRectificaciones = [];
        $rec = $where = '';
        $botonesAgregados = false;
        $this->conectar();
        // 04-03-2019 @Jordy la variable de $this->prm->autorizarec solamente se manda a llamar cuando
        // $this->usrNvj es mayor a 2 osea cuando es un ussuario de SEFIN
        foreach ($this->prm->autorizarect as $rectificacion) {
            if ($this->usrGpo . $this->usrUr == '118') {
                $filtroUr = 'gpo_id = ' . $rectificacion->gpo_id . '
                AND ur_id = ' . $rectificacion->ur_id;
            } else {
                $filtroUr = 'gpo_id = ' . $this->usrGpo . '
                AND ur_id = ' . $this->usrUr;
            }
            #bandera que sirve para comprobar si se rectifica la pagadora de una original o viceversa
            $consultar = true;
            $tipo = str_replace(['NORMAL',','], '', $rectificacion->tipo);

            switch ($tipo) {
                case 'ORIGEN':
                    $filtroOrigen = 'rct_id = ' . $rectificacion->rct_id . ' AND ';
                    $filtroPagadora = '';
                    $joinP = '(
                        SELECT gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,
                        ncc_tipoMovimiento,ncc_percepcion,ncc_retencion
                        FROM scp_MaestroRectificacion
                        JOIN scp_RectificacionContable USING(gpo_id,ur_id,rct_id,clc_id)
                        WHERE ecl_id = 6 AND ' . $filtroUr . '
                    )';
                    break;
                case 'PAGADORA':
                    $filtroPagadora = 'rct_id = ' . $rectificacion->rct_id . ' AND ';
                    $filtroOrigen = '';
                    $where = 'WHERE importe_pagadora - importeRectificacion_pagadora > importe_origen';
                    $joinP = 'scp_RectificacionContable';
                    break;
                default:
                    #el default hace referencia que la rectificacion es de una clc normal
                    $consultar = false;
                    break;
            }

            if ($consultar) {
                $this->setSql('
                    SELECT gpo_origen, ur_origen, tcl_origen, afe_origen, clc_origen, ncc_origen, percepcion_origen,
                        retencion_origen, importe_origen, importeRectificacion_origen, importeAutorizado_origen,
                        gpo_pagadora, ur_pagadora, tcl_pagadora, afe_pagadora, clc_pagadora, ncc_pagadora,
                        ncc_nccamortiza, percepcion_pagadora, retencion_pagadora, importe_pagadora, importeRectificacion_pagadora
                    FROM(
                        SELECT gpo_id gpo_origen, ur_id ur_origen, tcl_id tcl_origen, afe_id afe_origen,
                            clc_id clc_origen, ncc_id ncc_origen, co.ncc_percepcion AS percepcion_origen,
                            co.ncc_retencion AS retencion_origen,
                            IF( co.ncc_tipoMovimiento = "+", co.ncc_percepcion, co.ncc_retencion ) AS importe_origen,
                            SUM( IF( rco.ncc_tipoMovimiento = "+", rco.ncc_percepcion, rco.ncc_retencion ) ) AS importeRectificacion_origen
                        FROM scp_Contable co
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        LEFT JOIN scp_RectificacionContable rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
                        WHERE ' . $filtroOrigen . '
                            ' . $filtroUr . ' 
                            AND (
                                ( ncc_amortizado = 1 AND (ncc_nccamortiza = 0 OR ISNULL(ncc_nccamortiza) ) )
                                OR	( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 AND (
                                    ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 9 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                    )
                                )
                            ) AND eaf_id != "C"
                        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
                    )origen
                    /* Se agrega una subconsulta para agregar y contemplar el importe rectificado ya autorizado de otras clcs 09/09/24 @JMATAA */
                    LEFT JOIN (
                        SELECT ncc_id, SUM( IF ( co.ncc_tipoMovimiento = "+", co.ncc_percepcion, co.ncc_retencion ) ) AS importeAutorizado_origen 
                        FROM scp_RectificacionContable co 
                        INNER JOIN scp_MaestroRectificacion USING (gpo_id, ur_id, rct_id,clc_id) 
                        WHERE ' . $filtroUr . '
                        AND ecl_id = 6
                        GROUP BY gpo_id, ur_id, clc_id, afe_id, ncc_id
                        ) autorizadas on autorizadas.ncc_id = origen.ncc_origen 
                    JOIN(
                        SELECT gpo_id gpo_pagadora, ur_id ur_pagadora, tcl_id tcl_pagadora, afe_id afe_pagadora,
                            clc_id clc_pagadora, ncc_id ncc_pagadora, ncc_nccamortiza, co.ncc_percepcion 
                            percepcion_pagadora, co.ncc_retencion retencion_pagadora,
                            CASE co.ncc_tipoMovimiento
                                WHEN "+" THEN if( isnull(co.ncc_percepcion), 0, co.ncc_percepcion )
                                WHEN "-" THEN if( isnull(co.ncc_retencion),0,co.ncc_retencion)
                            END as importe_pagadora, 
                            SUM(CASE rco.ncc_tipoMovimiento
                                    WHEN "+" THEN if( isnull(rco.ncc_percepcion),0,rco.ncc_percepcion)
                                    WHEN "-" THEN if( isnull(rco.ncc_retencion),0,rco.ncc_retencion)
                                    else 0
                            END) importeRectificacion_pagadora
                        FROM scp_Contable co
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        LEFT JOIN ' . $joinP . ' rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
                        WHERE ' . $filtroPagadora . '
                            (
                                (ncc_amortizado = 2 AND (ncc_nccamortiza != 0 AND NOT ISNULL(ncc_nccamortiza) ) )
                                OR( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 AND(
                                        ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                        OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                    )
                                )
                            ) AND eaf_id != "C"
                        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
                    )pagadora ON(ncc_nccamortiza = ncc_origen)
                    ' . $where . ';
                ');
                $this->consultar();
                $conexion = new AccesoDatos();
                $conexion->conectar();
                $conexion->setSql('
                    SELECT count(ncc_origen) as numero,ncc_origen
                    FROM (
                        SELECT gpo_id gpo_origen, ur_id ur_origen, tcl_id tcl_origen, afe_id afe_origen,
                            clc_id clc_origen, ncc_id ncc_origen, co.ncc_percepcion percepcion_origen,
                            co.ncc_retencion retencion_origen,
                            IF( co.ncc_tipoMovimiento = "+", co.ncc_percepcion, co.ncc_retencion ) importe_origen,
                            SUM( IF( rco.ncc_tipoMovimiento = "+", rco.ncc_percepcion, rco.ncc_retencion ) ) importeRectificacion_origen
                        FROM scp_Contable co
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        LEFT JOIN scp_RectificacionContable rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
                        WHERE ' . $filtroOrigen . '
                            ' . $filtroUr . ' 
                            AND
                            (
                                ( ncc_amortizado = 1 AND (ncc_nccamortiza = 0 OR ISNULL(ncc_nccamortiza) ) ) 
                                OR	( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 AND (
                                    ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 9 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                    OR ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "+" )
                                    OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                    )
                                )
                            ) AND eaf_id != "C"
                        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
                    )origen
                    INNER JOIN(
                        SELECT
                            gpo_id gpo_pagadora,
                            ur_id ur_pagadora,
                            tcl_id tcl_pagadora,
                            afe_id afe_pagadora,
                            clc_id clc_pagadora,
                            ncc_id ncc_pagadora,
                            ncc_nccamortiza,
                            co.ncc_percepcion percepcion_pagadora,
                            co.ncc_retencion retencion_pagadora,
                            CASE co.ncc_tipoMovimiento
                                WHEN "+" THEN if( isnull(co.ncc_percepcion), 0, co.ncc_percepcion )
                                WHEN "-" THEN if( isnull(co.ncc_retencion),0,co.ncc_retencion)
                            END as importe_pagadora,
                            SUM(CASE rco.ncc_tipoMovimiento
                                    WHEN "+" THEN if( isnull(rco.ncc_percepcion),0,rco.ncc_percepcion)
                                    WHEN "-" THEN if( isnull(rco.ncc_retencion),0,rco.ncc_retencion)
                                    else 0
                            END) importeRectificacion_pagadora
                        FROM scp_Contable co
                        JOIN scp_Afectacion USING(gpo_id,ur_id,afe_id,clc_id)
                        LEFT JOIN ' . $joinP . ' rco USING(gpo_id,ur_id,afe_id,clc_id,ncc_id,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,ncc_tipoMovimiento)
                        
                        WHERE ' . $filtroPagadora . '
                            (
                                (ncc_amortizado = 2 AND (ncc_nccamortiza != 0 AND NOT ISNULL(ncc_nccamortiza) ) )
                                OR( ger_id = 2 AND cgp_id = 1 AND rub_id = 1 AND(
                                        ( tcl_id = "H" AND cgcu_id = 9 AND cgsc_id = 4 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "D" AND cgcu_id = 9 AND cgsc_id = 2 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "E" AND cgcu_id = 9 AND cgsc_id = 1 AND ncc_tipomovimiento = "-" )
                                        OR ( tcl_id = "F" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                        OR ( tcl_id = "G" AND cgcu_id = 7 AND cgsc_id = 1 AND ncc_tipomovimiento = "+" )
                                    )
                                )
                            ) AND eaf_id != "C"
                        GROUP BY gpo_id, ur_id,clc_id,afe_id, ncc_id
                    )pagadora ON(ncc_nccamortiza = ncc_origen)
                    GROUP by ncc_origen;
                ');

                $conexion->consultar();
                $conexion->desconectar();
                $dat = $conexion->extraer();
                $pinta = true;

                #Si regresa por lo menos un registro es por que la original tiene pagadora
                if ($this->getNumeroRegistros() > 0) {
                    if (!$botonesAgregados) {
                        $btnRegresar = new stdClass();
                        $btnRegresar->id = 'btnRegresar';
                        $btnRegresar->value = ' Regresar';
                        $btnRegresar->class = 'me-1';
                        $btnRegresar->icon = 'circle-left me-2';
                        $btnRegresar->color = 'gris';
                        $btnRegresar->click = '
                            comunica("' . $this->pantalla . '",{
                                metodo: "PantallaInicial",
                                tcl_id: "' . $this->prm->tcl_id . '",
                                tareaclc: "' . $this->prm->tareaclc . '"
                            });
                        ';

                        $this->agregaTools($this->ponBoton($btnRegresar));
                        $btnReportePagadora = new stdClass();
                        $btnReportePagadora->id = 'excelPagadoras';
                        $btnReportePagadora->value = ' Exportar';
                        $btnReportePagadora->color = 'verde';
                        $btnReportePagadora->class = 'me-1';
                        $btnReportePagadora->icon = 'solid fa-file-excel me-2';

                        $this->agregaTools($this->ponBoton($btnReportePagadora));
                        $botonesAgregados = true;
                    }

                    $this->mixScript('
                        $("#excelPagadoras").click(function () {
                            var $formulario = $("<form>")
                                .attr("action", "rep/excel/scp/afectaciones/ExportaReportePagadoras.php")
                                .attr("method", "post")
                                .attr("target", "_blank");
                            $formulario.append($("<input>").attr("type", "hidden").attr("name", "rct_id").val("' . $this->prm->autorizarect[0]->rct_id . '"));
                            $formulario.append($("<input>").attr("type", "hidden").attr("name", "origen").val("' . $filtroOrigen . '"));
                            $formulario.append($("<input>").attr("type", "hidden").attr("name", "pagadora").val("' . $filtroPagadora . '"));
                            ' . (($this->usrNvj > 2 || $this->usrGpo . $this->usrUr == '118') ? '
                            $formulario.append($("<input>").attr("type", "hidden").attr("name", "gpo_id").val("' . $rectificacion->gpo_id . '"));
                            $formulario.append($("<input>").attr("type", "hidden").attr("name", "ur_id").val("' . $rectificacion->ur_id . '"));
                            ' : '') . '
                            $("body").append($formulario);
                            $formulario.submit();
                            $formulario.remove();
                        });
                    ');

                    $this->mixScript('$("#excelPagadoras span").removeClass("glyphicon");');
                    $bolcontrol = $boldetalle = false;
                    $varcontrol = $html3 = '';
                    $netrectificado = $porRectificar = $acum = 0;

                    $header = '<table class = "table table-bordered class="warning"">
                        <thead>
                            <tr>
                                <th colspan="8" class="success">CLC de ORIGEN</th>
                            </tr>
                            <tr class="warning">
                                <th class="success">CLC</th>
                                <th class="success">Tipo</th>
                                <th class="success">Afectacion</th>
                                <th class="success">Num. Control</th>
                                <th class="success">Percepción</th>
                                <th class="success">Retención</th>
                                <th class="success">Rectificado</th>
                                <th class="success">Original - Rectificacion</th>
                            </tr>		
                        </thead>
                    ';

                    $header2 = '<table class = "table table-bordered class="warning"">
                        <thead>
                            <tr>
                                <th colspan="9" class="table-warning">CLC PAGADORA</th>
                            </tr>
                            <tr>
                                <th>UR</th>
                                <th>CLC</th>
                                <th>Tipo</th>
                                <th>Afectacion</th>
                                <th>Num. Control</th>
                                <th>Percepción</th>
                                <th>Retención</th>
                                <th>Rectificado</th>
                                <th>Original - Rectificacion</th>
                            <tr>
                        </thead>
                    ';

                    while ($row = $this->extraer()) {
                        //guardamos el número de control en la variable
                        $numcontrol = ((empty($row['ncc_origen'])) ? $row['ncc_nccamortiza'] : $row['ncc_origen']);

                        $netOrigen = $row['importe_origen'];
                        /*Se suma (['importeAutorizado_origen']) al importe a rectificar, el importe rectificado ya autorizadopara y hacer la diferencia correcta entre el importe del num control que se esta rectificando con los importes autorizados y el importe que se esta rectificando 09/09/24 @JMATAA solo aplica para el caso origen. TO DO: Contemplar las pagadoras en caso de que se requiera*/
                        $netoOriginal = Math::redondear($row['importe_origen'], 2) - (Math::redondear($row['importeRectificacion_origen'], 2) + Math::redondear($row['importeAutorizado_origen'], 2));
                        $netoPagadora = Math::redondear($row['importe_pagadora'], 2) - Math::redondear($row['importeRectificacion_pagadora'], 2);

                        if ($varcontrol != $numcontrol) {
                            /* Pintamos el header de la CLC de origen  */
                            if ($bolcontrol == false) {
                                $html .= $header;
                            }
                            $html .= '
                                <tr >
                                    <td class="success">' . $row['clc_origen'] . '</td>
                                    <td class="success">' . $row['tcl_origen'] . '</td>
                                    <td class="success">' . $row['afe_origen'] . '</td>
                                    <td class="success">' . ((empty($row['ncc_origen'])) ? $row['ncc_nccamortiza'] : $row['ncc_origen']) . '</td>
                                    <td class="success">' . number_format($row['percepcion_origen'], 2) . '</td>
                                    <td class="success">' . number_format($row['retencion_origen'], 2) . '</td>
                                    <td class="success">' . number_format($row['importeRectificacion_origen'], 2) . '</td>
                                    <td class="success">' . number_format($netoOriginal, 2) . '</td>
                                </tr>
                            ';
                            $varcontrol = $numcontrol;
                            $bolcontrol = true;
                        }

                        $acumlulador = $varcontrol;
                        $netPagador = 0;
                        $netPagador += $row['importe_pagadora'] ;
                        $netrectificado += $row['importeRectificacion_pagadora'] ;
                        $porRectificar = Math::redondear($netoPagadora, 2) - Math::redondear($netoOriginal, 2);

                        if ($porRectificar == 0) {
                            $valido = $valido && true;
                        } else {
                            $valido = false;
                        }

                        if ($acumlulador == $numcontrol) {

                            if ($boldetalle == false) {
                                $html2 .= $header2;
                            }

                            $boldetalle = true;

                            $html2 .= '
                                <tr>
                                    <td >' . $row['gpo_pagadora'] . $row['ur_pagadora'] . '</td>
                                    <td >' . $row['clc_pagadora'] . '</td>
                                    <td >' . $row['tcl_pagadora'] . '</td>
                                    <td >' . $row['afe_pagadora'] . '</td>
                                    <td >' . $row['ncc_pagadora'] . '</td>
                                    <td >' . number_format($row['percepcion_pagadora'], 2) . '</td>
                                    <td >' . number_format($row['retencion_pagadora'], 2) . '</td>
                                    <td >' . number_format($row['importeRectificacion_pagadora'], 2) . '</td>
                                    <td >' . number_format($netoPagadora, 2) . '</td>
                                </tr>
                            ';
                        } else {
                            $bolcontrol = false;
                        }

                        if ($dat['numero'] == 1) {
                            $pinta = true;
                        } else {
                            if ($numcontrol == $dat['ncc_origen']) {
                                $dat['numero'] = $dat['numero'] - $acum;
                                if ($dat['numero'] > 1) {
                                    $pinta = false;
                                    $acum = $dat['numero'] - 1;
                                } else {
                                    $pinta = true;
                                }
                            } else {
                                $acum = 0;
                            }
                        }

                        if ($pinta) {
                            $html3 .= '
                                <table class = "table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="table-success"> Neto del N° de control ' . $numcontrol . ' </th>
                                            <th class="table-success"> Neto - Rectificado</th>
                                            <th class="table-success"> Disponible a Rectificar</th>
                                            <th class="table-warning"> Neto Pagador</th>
                                            <th class="table-warning"> Neto Recticado</th>
                                            <th class="table-warning"> Neto Pagado</th>
                                            
                                            <th class="table-info"> NetoPagado - Disponible a Rectificar </th>
                                            <th class="table-danger">  Por Rectificar   </th>
                                        </tr>
                                        <tr>
                                            <td> ' . number_format($netOrigen, 2) . ' </td>
                                            <td> ' . number_format($row['importeRectificacion_origen'], 2) . ' </td>
                                            <td> ' . number_format($netoOriginal, 2) . ' </td>
                                            
                                            <td> ' . number_format($netPagador, 2) . ' </td>
                                            <td> ' . number_format($netrectificado, 2) . ' </td>
                                            <td> ' . number_format($netoPagadora, 2) . ' </td>
                                            
                                            <td> ' . number_format($netoOriginal, 2) . ' - ' . number_format($netoPagadora, 2) . '  </td>
                                            <td> ' . number_format($porRectificar, 2) . ' </td>
                                        </tr>
                                    </thead>
                                </table>
                            ';
                            $netPagador = 0;
                            $netrectificado = 0;
                            $porRectificar = 0;
                        }
                    }
                    $html .= '</table>';
                    $html2 .= '</table>';

                    $this->mixHtml('' . $html . ' ' . $html2 . '  ' . $html3 . ' ');

                } else {
                    $valido = true;
                }
            }

            $this->setSql('
                SELECT
                    gpo_id,ur_id,rct_id,rct_importe, 
                    SUM(percepcion) - SUM(retencion) AS rct_neto
                FROM scp_MaestroRectificacion
                INNER JOIN(
                    SELECT
                        gpo_id,ur_id,rct_id,ncc_percepcion percepcion,ncc_retencion retencion
                    FROM scp_RectificacionContable
                    WHERE ' . $filtroUr . '
                    AND rct_id = ' . $rectificacion->rct_id . '
                    UNION ALL
                    SELECT
                        gpo_id,ur_id,rct_id,ncp_percepcionNeto percepcion,0 retencion
                    FROM scp_RectificacionPresupuestal
                    WHERE ' . $filtroUr . '
                    AND rct_id = ' . $rectificacion->rct_id . '
                )detalle USING(gpo_id,ur_id,rct_id)
                WHERE ' . $filtroUr . '
                AND rct_id = ' . $rectificacion->rct_id . '
                AND rct_reintegro!=2
                GROUP BY gpo_id,ur_id,rct_id
                HAVING rct_neto != rct_importe;
            ');
            $this->consultar();

            if ($this->getNumeroRegistros() == 0) {
                $valido = $valido && true;
            } else {
                $valido = false;
                $this->mixScript('
                    notify("El importe de la rectificacion no coincide con el importe total neto de los movimientos","warning");
                ');
            }

            //Para cersiorarse de que los documentos utilizados en la clc a rectificar esten pagados se hace la siguiente consulta, de no ser asi no dejaría avanzar.
            $this->setSql('
                SELECT bdo_id
                FROM scp_RectificacionPresupuestal R 
                    INNER JOIN cat_BancoDocumentos B USING(gpo_id,ur_id,bdo_id) 
                WHERE ' . $filtroUr . '
                    AND rct_id = ' . $rectificacion->rct_id . ' 
                    AND bdo_estadoPago != 3 
                LIMIT 1 
                ;
            ');

            $this->consultar();
            if ($this->getNumeroRegistros() > 0) {
                $valido = false;
                $this->mixScript('notify("No se puede rectificar la CLC por tener un documento no pagado","warning");');
                return false;
            }
            /*VERIFICAR SI LA RECTIFICACION TIENE MOVIMIENTOS*/
            $tieneMovimientos = $this->tieneMovimientos($filtroUr, $rectificacion);
            /*SI TIENE MOVIMIENTOS PASA LA VALIDACIÓN*/
            if (!$tieneMovimientos) {
                array_push($arrayRectificaciones, $rectificacion->rct_id);
            }

        }

        if (count($arrayRectificaciones) > 0) {
            $valido = false;
            foreach ($arrayRectificaciones as $val) {
                if ($rec != '') {
                    $rec .= ',';
                }
                $rec .= $val;
            }
            $this->mixScript('
                notify("No es posible autorizar la(s) rectificacion(es) ' . $rec . ' ya que no tiene(n) movimientos, quitar de la selección e intentarlo nuevamente","error",false);
            ');
            return false;
        }

        if ($valido) {
            foreach ($this->prm->autorizarect as $rectificacion) {
                $this->setSql('
                    UPDATE scp_MaestroRectificacion 
                    SET ecl_id = 2
                    WHERE ' . $filtroUr . '
                    AND rct_id = ' . $rectificacion->rct_id . ';
                ');
                $this->consultar();
                $ip = $this->usrIp;
                $ur1 = ($this->usrNvj <= 2 ? $this->usrGpo : $this->admGpo) . ',' . ($this->usrNvj <= 2 ? $this->usrUr : $this->admUr) . ',';
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion (gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id)
                    VALUES ( ' . $ur1 . $rectificacion->rct_id . ',' . $rectificacion->clc_id . ',2,"RECTIFICACION AUTORIZADA POR LA UR",INET_ATON("' . $ip . '"),
                    ' . $this->usrId . ')';
                $this->setSql($sql);
                $this->mixScript('notify("Rectificacion autorizada","success");');
                $this->consultar();
            }
        }
        $this->desconectar();
        $this->mixScript($this->wrLlamaLlenaTabla('tbl-Rectificacion'));
    }

    private function tieneMovimientos($filtroUr, $rectificacion)
    {
        $valido = false;
        $this->setSql('
            SELECT gpo_id,ur_id,rct_id
            FROM scp_MaestroRectificacion
                INNER JOIN(
                    SELECT
                        gpo_id,ur_id,rct_id
                    FROM scp_RectificacionContable
                    WHERE ' . $filtroUr . '
                        AND rct_id = ' . $rectificacion->rct_id . '
                    UNION ALL
                    SELECT
                        gpo_id,ur_id,rct_id
                    FROM scp_RectificacionPresupuestal
                    WHERE ' . $filtroUr . '
                        AND rct_id = ' . $rectificacion->rct_id . '
                )detalle USING(gpo_id,ur_id,rct_id)
            WHERE ' . $filtroUr . ' 
            AND rct_id = ' . $rectificacion->rct_id . '
            GROUP BY gpo_id,ur_id,rct_id
            LIMIT 1;
        ');

        $this->consultar();
        /*SI TIENE MOVIMIENTOS PASA LA VALIDACIÓN*/
        if ($this->getNumeroRegistros() > 0) {
            $valido = true;
        }
        return $valido;
    }

    private function cambiaEstadoRectificacion($estado, $descBit = '', $alerta = '', $color = 'warning')
    {
        $this->conectar();
        $lista = $rec = $filtroUr = '';
        $tieneMovimientos = $valido = true;
        $arrayRectificaciones = [];

        foreach ($this->prm->autorizarect as $rectificacion) {
            if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj > 2) {
                $filtroUr = 'gpo_id = ' . $rectificacion->gpo_id . ' AND ur_id = ' . $rectificacion->ur_id;
            } else {
                $filtroUr = 'gpo_id=' . $this->usrGpo . ' AND ur_id = ' . $this->usrUr;
            }

            if ($estado != 1) {
                $tieneMovimientos = $this->tieneMovimientos($filtroUr, $rectificacion);
            }

            if (!$tieneMovimientos) {
                array_push($arrayRectificaciones, $rectificacion);
            }
        }

        if (count($arrayRectificaciones) > 0) {
            $valido = false;
            foreach ($arrayRectificaciones['rct_id'] as $val) {
                if ($rec != '') {
                    $rec .= ',';
                }
                $rec .= $val;
            }
            $this->mixScript('
                notify("La(s) rectificacion(es) ' . $rec . ' no tiene(n) movimiento(s), quitar de la selección e intentarlo nuevamente","error",false);
            ');
            return false;
        }

        if ($valido) {
            foreach ($this->prm->autorizarect as $rectificacion) {
                $this->setSql('
                    UPDATE scp_MaestroRectificacion
                    SET ecl_id = ?
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND rct_id = ?;
                ');
                $this->consultar([$estado, $rectificacion->gpo_id, $rectificacion->ur_id, $rectificacion->rct_id]);
                $ip = $this->usrIp;

                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion
                        (gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id)
                    VALUES (?, ?, ?, ?, ?, ?,INET_ATON(?), ?);
                ';
                $iparams = [$rectificacion->gpo_id, $rectificacion->ur_id, $rectificacion->rct_id, $rectificacion->clc_id, $estado, $descBit, $ip, $this->usrId];
                $this->setSql($sql);
                $this->consultar($iparams);
                $lista .= $rectificacion->rct_id . ',';
            }
        }

        $this->mixScript('
            notify("' . $alerta . ' ' . $lista . ' " );
        ');
        $this->desconectar();
        $this->mixScript($this->wrLlamaLlenaTabla('tbl-Rectificacion'));
    }

    private function aplicarRectificacion()
    {
        $this->conectar();
        $recti = $mensaje = $rec = $filtroUr = $tieneMovimientos = '';
        $arrayRectificaciones = [];
        $valido = true;
        foreach ($this->prm->autorizarect as $rectificacion) {
            if ($this->usrGpo . $this->usrUr == '118' || $this->usrNvj > 2) {
                $filtroUr = 'gpo_id = ' . $rectificacion->gpo_id . ' AND ur_id = ' . $rectificacion->ur_id;
            } else {
                $filtroUr = 'gpo_id = ' . $this->usrGpo . ' AND ur_id = ' . $this->usrUr;
            }
            //VERIFICAMOS SI LAS RECTIFICACIONES TIENEN MOVIMIENTOS
            $tieneMovimientos = $this->tieneMovimientos($filtroUr, $rectificacion);

            if (!$tieneMovimientos) {
                array_push($arrayRectificaciones, $rectificacion);
            }
        }
        // SI HAY RECTIFICACIONES SIN MOVIMIENTOS
        if (count($arrayRectificaciones) > 0) {
            $valido = false;
            foreach ($arrayRectificaciones['rct_id'] as $val) {
                if ($rec != '') {
                    $rec .= ',';
                }
                $rec .= $val;
            }
            $this->mixScript('
                notify("No es posible aplicar la(s) rectificacion(es) ' . $rec . ' ya que no tiene(n) movimiento(s) quitar de la selección e intentarlo nuevamente","error",false);
            ');
            return false;
        }
        if ($valido) {
            foreach ($this->prm->autorizarect as $rectificacion) {
                $recti .= $rectificacion->rct_id . ',';
                if ($rectificacion->recursoFederal == 1) {
                    $this->setSql('
                        UPDATE scp_MaestroRectificacion
                        SET ecl_id = 5
                        WHERE gpo_id = ?
                        AND ur_id = ?
                        AND rct_id = ?
                        AND ecl_id = 2;
                    ');
                    $this->consultar([$rectificacion->gpo_id, $rectificacion->ur_id, $rectificacion->rct_id]);
                    $sql = '
                        INSERT INTO ' . BD_BITACORA . '.bit_Rectificacion(gpo_id,ur_id,rct_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id)
                        VALUES (?, ?, ?, ?, 5, "ACTUALIZADA PARA RECTIFICAR CLC DE RECURSO FEDERAL POR UR", INET_ATON(?), ?);
                    ';
                    $pinsert = [$rectificacion->gpo_id, $rectificacion->ur_id, $rectificacion->rct_id, $rectificacion->clc_id,$this->usrIp, $this->usrId];
                    $this->setSql($sql);
                    $this->consultar($pinsert);
                    $mensaje = 'La Rectificacion (es) ' . $recti . ' de Recurso Federal ha sido aplicada ';
                } else {
                    $mensaje = 'La Rectificacion (es) ' . $recti . ' ha sido aplicada ';
                }
                $datosSP = new stdClass();
                $datosSP->gpo_id = $rectificacion->gpo_id;
                $datosSP->ur_id = $rectificacion->ur_id;
                $datosSP->rct_id = $rectificacion->rct_id;
                $datosSP->ejercicio = APP_EJERCICIO;
                $datosSP->bit_observacion = 'CLC RECTIFICADA DESDE PANTALLA S_2_14';
                $datosSP->sis_id = 'SCP';
                $respuesta = sp_Rectificar::call($datosSP);
                if ($respuesta->bandera != 1) {
                    $this->mixScript('notify("' . $respuesta->mensaje . '", "error");');
                    return false;
                } else {
                    $this->mixScript('notify("' . $respuesta->mensaje . '", "success");');
                }
            }
        }
        $this->desconectar();
        $this->mixScript($this->wrLlamaLlenaTabla('tbl-Rectificacion'));
    }

    private function llenaComboUr()
    {
        $gpo = substr($this->prm->ur, 0, 1);
        $ur = substr($this->prm->ur, 1, 2);

        $rectificacion = new stdClass();
        $rectificacion->id = 'clc_id';
        $rectificacion->soloText = true;
        $rectificacion->comoAtr = 'recursoFederal';
        $rectificacion->mensaje = 'numero de CLC';

        $params = [$gpo, $ur, $this->prm->tcl_id];
        //ALFONSO 2018-02-22 Se eliminaron las condiciones relacionadas con clave de financiamiento ya que estaban obsoletas.
        $rectificacion->sql = "
            SELECT clc_id, clc_id, SUM(if(r.ecl_id = 6, 1, 0)) rectificacionConcluida, SUM(if(r.ecl_id != 6, 1, 0)) rectificacionEnTramite,
                /* Si es de servicios personales(A) subtipo pensiones(2) y las UR especificadas se trata como normal */
                IF((tcl_id, stc_id) IN('A', 2) AND CONCAT(c.gpo_id, c.ur_id) IN('202', '302', '303', '304', '306', '307', '308', '309', '310', '324', '333', '334', '471'), 0,
                IF(ter_id = 'B' AND CONCAT(c.gpo_id, c.ur_id) NOT IN('315', '801', '802'), 0, 1)) recursoFederal
            FROM " . BD_CTRL . '.scp_Clc c
            LEFT JOIN ' . BD_CTRL . '.scp_MaestroRectificacion r using (gpo_id,ur_id,clc_id)
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . " USING(cff_id,fte_id,sft_id,ori_id)
            JOIN cat_BancosUr USING(bur_id)
            WHERE c.gpo_id = ? AND c.ur_id = ?
            AND c.ecl_id in (18) AND mpc_id != 3 AND tcl_id = ?
            AND bur_fondoRotatorio = 'N'  AND clc_rectificacion = 0
            GROUP BY c.gpo_id, c.ur_id, clc_id
            HAVING rectificacionConcluida < 2 AND rectificacionEnTramite=0;
        ";
        $rectificacion->params = $params;
        $this->recargaCombo($rectificacion);
    }

    private function verificaClc()
    {

        $mensaje = $clase = $modal = '';
        $this->setContenedor('divContenedorGeneralModal');
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        $this->prm->tcl_id = (isset($this->prm->tcl_id)) ? $this->prm->tcl_id : '';
        $script = '';
        $gpo_id = $this->usrGpo;
        $ur_id = $this->usrUr;

        if ($this->prm->clc_id != '') {

            $conexion = new AccesoDatos();
            $conexion->conectar();
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':clc_id' => $this->prm->clc_id];
            $conexion->setSql('
                SELECT
                    c.ecl_id,clc_id,SUM(if(r.ecl_id = 6,1,0)) AS rectificacionConcluida,
                    SUM(if(r.ecl_id != 6,1,0)) AS rectificacionEnTramite,
                    bur_fondoRotatorio,mpc_id,clc_rectificacion, tcl_id, 
                    tcl_nombre,bdo_estadoPago
                FROM scp_Clc c
                INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
                INNER JOIN (
                    SELECT gpo_id,ur_id,clc_id,MIN(bdo_estadoPago) AS bdo_estadoPago 
                    FROM scp_Afectacion
                    INNER JOIN (
                        SELECT 
                            c.gpo_id,c.ur_id,clc_id,afe_id,MIN(bdo_estadoPago) AS bdo_estadoPago 
                        FROM scp_Contable c
                        INNER JOIN cat_BancoDocumentos USING(bdo_id)
                        WHERE c.gpo_id = :gpo_id
                            AND c.ur_id = :ur_id
                            AND clc_id = :clc_id
                        GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                        UNION ALL
                        SELECT 
                            p.gpo_id,p.ur_id,clc_id,afe_id,MIN(bdo_estadoPago) AS bdo_estadoPago
                        FROM scp_Presupuestal p
                        INNER JOIN cat_BancoDocumentos USING(bdo_id)
                        WHERE p.gpo_id = :gpo_id
                            AND p.ur_id = :ur_id
                            AND clc_id = :clc_id
                        GROUP BY gpo_id, ur_id, clc_id, afe_id, bdo_id
                    )control USING(gpo_id,ur_id,clc_id,afe_id)
                    GROUP BY gpo_id, ur_id, clc_id
                )afe USING(gpo_id,ur_id,clc_id)
                INNER JOIN cat_TipoClc USING(tcl_id)
                LEFT JOIN ' . BD_CTRL . '.scp_MaestroRectificacion r using (gpo_id,ur_id,clc_id)
                INNER JOIN cat_BancosUr USING(bur_id)
                WHERE c.gpo_id = :gpo_id AND c.ur_id = :ur_id AND clc_id = :clc_id
                GROUP BY c.gpo_id,c.ur_id,clc_id;
            ');

            $conexion->consultar($params);
            $row = $conexion->extraer();
            $conexion->desconectar();
            $registros = $conexion->getNumeroRegistros();

            $c2 = new AccesoDatos();
            $c2->conectar();
            $params = [(int)$gpo_id, (int)$ur_id, (int)$this->prm->clc_id];
            $c2->setSql('
                SELECT
                    IF(ger_id = 2 AND cgp_id = 1 AND rub_id = 1 AND cgcu_id = 7 AND cgsc_id = 1 AND cgc_id = 018 AND ter_id = "A",1,0) AS cuenta
                FROM scp_Clc
                    INNER JOIN scp_ClcAuxiliar USING(gpo_id,ur_id,clc_id,ecl_id)
                    INNER JOIN scp_Afectacion using(gpo_id,ur_id,clc_id,ecf_id)
                    INNER JOIN scp_Contable using(gpo_id,ur_id,clc_id,afe_id) 
                    INNER JOIN enc_ClaveFinanciamiento Using(ecf_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING( fte_id, sft_id, ori_id, sor_id)
                WHERE gpo_id = ? AND ur_id = ? AND clc_id = ?
                HAVING cuenta=1
            ');

            $c2->consultar($params);

            $dat = $c2->extraer();
            $registros2 = $c2->getNumeroRegistros();
            $modal = '
                <div id="modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog" style="width: 50% !IMPORTANT;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4>Detalle de la CLC</h4>
                                <button type="button" class="btn-close cerrar" data-bs-dismiss="modal" aria-hidden="true"></button>
                            </div>
                            <div class="modal-body">
                                <form id="Mensaje">
            ';

            if ($row && $row['mpc_id'] == 5) {
                $mensaje = ' NO PUEDE SER RECTIFICADA PORQUE LA CLC ES DE MODALIDAD DE PAGO PDP';
                $clase = 'danger';
            } elseif ($row) {
                switch ($row['ecl_id']) {
                    case 1: case 2: case 3: case 4: case 5: case 6: case 7: case 8: case 9: case 10: case 11: case 12: case 13: case 14: case 15: case 16: case 19:
                        $mensaje = ' NO PUEDE SER RECTIFICADA PORQUE LA CLC NO ESTÁ PAGADA';
                        $clase = 'danger';
                        break;
                    case 21:
                        $mensaje = ' NO PUEDE SER RECTIFICADA PORQUE LA CLC ESTÁ CANCELADA';
                        $clase = 'danger';
                        break;
                    case 18:
                        $clase = 'warning';
                        if ($row['bur_fondoRotatorio'] == 'S') {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC ES DE FONDO ROTATORIO';
                        } elseif ($row['mpc_id'] == 3 && $row['clc_rectificacion'] != 0) {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC ESTÁ VINCULADA A UNA RECTIFICACION';
                        } elseif ($row['rectificacionConcluida'] == 2) {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC YA SE HA RECTIFICADO EN DOS OCASIONES';
                        } elseif ($row['rectificacionEnTramite'] > 0) {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC TIENE UNA RECTIFICACION EN PROCESO';
                        } elseif ($row['bdo_estadoPago'] != 3) {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE TIENE DOCUMENTOS QUE NO ESTAN PAGADOS';
                        } elseif ($row['mpc_id'] == 2) {
                            $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC ES UNA LINEA DE CAPTURA';
                        } else {
                            if ($registros2 > 0) {
                                $clase = 'warning';
                                $mensaje = ' NO PUEDE SER RECTIFICADA DEBIDO A QUE LA CLC TIENE LA CUENTA CONTABLE 21171-018. FAVOR DE COMUNICARSE CON SU ANALISTA PARA VERIFICAR ESTA INFORMACIÓN';

                            } else {
                                $mensaje = ' SI PUEDE SER RECTIFICADA BUSCALA EN EL COMBO EN EL TIPO DE CLC CORRESPONDIENTE';
                                $clase = 'info';
                            }
                        }
                        break;
                    case 17:
                        $mensaje = ' NO PUEDE SER RECTIFICADA PORQUE TIENES QUE REALIZAR EL REGISTRO DE PAGO EN EL MÓDULO DE CONTABILIDAD';
                        $clase = 'warning';
                        break;
                    case 20:
                        $mensaje = ' NO PUEDE SER RECTIFICADA PORQUE LA CLC ESTÁ INHABILITADA';
                        $clase = 'danger';
                        break;
                    default:
                        $mensaje = ' NO ENCONTRADA';
                        $clase = 'danger';
                        break;
                }
            } else {
                $mensaje = ' NO ENCONTRADA';
                $clase = 'danger';
            }
            $modal .= '
                <div class="row">
                    <fieldset  class="col-md-12 col-xs-12 mb-4">
                        <div class="alert alert-' . $clase . ' mb-4" role="alert">
                            <b>LA CLC ' . $this->prm->clc_id . ' ' . ($row ? $row['tcl_nombre'] : '') . ' ' . $mensaje . '</b>
                    </fieldset>
                </div>
            ';
            $modal .= '
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary cerrar" data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </form>
                            </div><!-- /.modal-content -->
                        </div><!-- /.modal-dialog -->
                    </div>
                </div>
            ';

            $this->mixHtml($modal);
            $this->mixScript($script);

            $this->mixScript('
                $("#modal").modal();
                $(".cerrar").click(function(){ 
                    $("#modal").remove();
                });
            ');
        } else {
            $this->mixScript('notify("No existe número de CLC a Verificar","warning");');
            return false;
        }
    }
}

if (isset($_SESSION[_S]['prm']['S']['2']['14'])) {
    new ControladorRectificacionClc();
} else {
    WebResponse::sinPermisos();
}
