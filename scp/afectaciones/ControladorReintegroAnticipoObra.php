<?php

$pathInPieces = explode(DIRECTORY_SEPARATOR, __FILE__);
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scp/ReintegroAnticipoObra.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../lib/funciones.php';
require_once dirname(__FILE__) . '/../../srv/CatalogoProgramaticoFinanciamiento.php';
class ControladorReintegroAnticipoObra extends WebResponse
{
    private $usrId;
    private $usrIp;
    private $usrNvj;
    private $usrGpo;
    private $usrUr;
    private $usrUe;
    private $admGpo;
    private $admUr;
    private $admUe;
    private $admEpg;
    private $admPrg;
    private $admSpr;
    private $admPy;
    private $admOa;
    private $cvefinTer;
    private $cvefinFte;
    private $cvefinSft;
    private $cvefinOri;
    private $cvefinSor;
    private $cvefinCso;
    private $cvefinCfi;
    private $cvefinEcf;
    private $cvefinAnio;
    private $cvefinNombre;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorReintegroAnticipoObra.php';
        $this->usrId = $_SESSION[_S]['usr_id'];
        $this->usrIp = $_SESSION[_S]['ses_ip'];
        $this->usrNvj = $_SESSION[_S]['nvj_id'];
        $this->usrGpo = $_SESSION[_S]['gpo_id'];
        $this->usrUr = $_SESSION[_S]['ur_id'];
        $this->usrUe = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : '';
        $this->admGpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->admUr = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '';
        $this->admUe = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : '';
        $this->admEpg = isset($_SESSION[_S]['clasAdm-epg']) ? $_SESSION[_S]['clasAdm-epg'] : '';
        $this->admPrg = isset($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : '';
        $this->admSpr = isset($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : '';
        $this->admPy = isset($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : '';
        $this->admOa = isset($_SESSION[_S]['clasAdm-oa']) ? $_SESSION[_S]['clasAdm-oa'] : '';
        $this->cvefinTer = isset($_SESSION[_S]['cvefin']->ter) ? $_SESSION[_S]['cvefin']->ter : '';
        $this->cvefinFte = isset($_SESSION[_S]['cvefin']->fte) ? $_SESSION[_S]['cvefin']->fte : '';
        $this->cvefinSft = isset($_SESSION[_S]['cvefin']->sft) ? $_SESSION[_S]['cvefin']->sft : '';
        $this->cvefinOri = isset($_SESSION[_S]['cvefin']->ori) ? $_SESSION[_S]['cvefin']->ori : '';
        $this->cvefinSor = isset($_SESSION[_S]['cvefin']->sor) ? $_SESSION[_S]['cvefin']->sor : '';
        $this->cvefinCso = isset($_SESSION[_S]['cvefin']->cso) ? $_SESSION[_S]['cvefin']->cso : '';
        $this->cvefinCfi = isset($_SESSION[_S]['cvefin']->cfi) ? $_SESSION[_S]['cvefin']->cfi : '';
        $this->cvefinEcf = isset($_SESSION[_S]['cvefin']->ecf) ? $_SESSION[_S]['cvefin']->ecf : '';
        $this->cvefinAnio = isset($_SESSION[_S]['cvefin']->anio) ? $_SESSION[_S]['cvefin']->anio : '';
        $this->cvefinNombre = isset($_SESSION[_S]['cvefin']->nombre) ? $_SESSION[_S]['cvefin']->nombre : '';


        //Nivel UR y unidad Ejecutora para alta de folios modulo unidad resposable
        switch ($this->usrNvj) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                switch ($this->metodo) {
                    case 'ponTablaListadoReintegros':
                        $this->ponTablaListadoReintegros();
                        break;
                    case 'addReintegro':
                        $this->addReintegro();
                        break;
                    case 'abcReintegro':
                        $this->abcReintegro();
                        break;
                    case 'datosObra':
                        $this->datosObra();
                        break;
                    case 'llenaTablaReintegros':
                        $this->llenaTablaReintegros();
                        break;
                    case 'impresionReintegro':
                        $this->impresionReintegro();
                        break;
                    case 'tareasEstados':
                        $this->tareasEstados();
                        break;
                    case 'AplicacionReintegro':
                        $this->AplicacionReintegro();
                        break;
                    case 'RechazaReintegro':
                        $this->RechazaReintegro();
                        break;
                    case 'ConsultaReintegro':
                        $this->ConsultaReintegro();
                        break;
                    case 'llenaTablaConsultaReintegro':
                        $this->llenaTablaConsultaReintegro();
                        break;
                    case 'upload':
                        $this->upload();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }
    /*********************************** FUNCIÓN QUE MUESTRA LAS TAREAS DEL REINTEGRO***********************************/
    public function main()
    {
        $this->agregaTools('');

        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        $cards = [];

        /* BOTON DONDE SE GENERAN LOS REINTEGROS POR UR */
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['65']) &&
            ($this->usrNvj == 1 || $this->usrNvj == 2)
        ) {
            $c = new stdClass();
            $c->id = 'generacion';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/generacion-ur.png';
            $c->label = 'GENERACIÓN';
            $c->title = 'Registro del Reembolso';
            $cards[] = $c;
        }

        /* BOTON DONDE SE AUTORIZAN LOS REINTEGROS SOLO UR*/
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['66']) &&
            ($this->usrNvj || $this->usrNvj == 2)
        ) {
            $c = new stdClass();
            $c->id = 'autorizacion';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/autorizacion-ur.png';
            $c->label = 'AUTORIZACIÓN';
            $c->title = 'Autorización del Reembolso';
            $cards[] = $c;
        }

        /* BOTON DONDE SE ENVIAN LOS REINTEGROS SOLO UR */
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['67']) &&
            ($this->usrNvj == 1 || $this->usrNvj == 2)
        ) {
            $c = new stdClass();
            $c->id = 'enviar';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/enviar-ur.png';
            $c->label = 'ENVÍO';
            $c->title = 'Envio del Reembolso';
            $cards[] = $c;

            /* BOTON PARA LA IMPRESIÓN DE LOS REEMBOLSOS AÚN CUANDO YA SE HAYAN ENVIADO LOS REEMBOLSOS, LA UR PODRÁ REALIZAR SU IMPRESIÓN  */
            $c = new stdClass();
            $c->id = 'impresion';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/impresion-ur.png';
            $c->label = 'IMPRESIÓN';
            $c->title = 'Impresión de Reembolso';
            $cards[] = $c;
        }

        /* BOTON PARA LA RECEPCION DE REINTEGROS POR PARTE DE EGRESOS */
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['68'])
            && ($this->usrNvj > 2)
        ) {
            $c = new stdClass();
            $c->id = 'recepcion';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/recepcion_egresos.png';
            $c->label = 'RECEPCIÓN';
            $c->title = 'Recepción del Reembolso';
            $cards[] = $c;
        }

        /* BOTON PARA LA REVISION DE REINTEGROS POR PARTE DE EGRESOS */
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['69'])
            && ($this->usrNvj > 2)
        ) {
            $c = new stdClass();
            $c->id = 'revision';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/autorizacion-ur.png';
            $c->label = 'REVISIÓN';
            $c->title = 'Revisión del Reembolso';
            $cards[] = $c;
        }

        /* BOTON PARA LA APLICACION  DE REINTEGROS POR PARTE DE EGRESOS */
        if (
            isset($_SESSION[_S]['prm']['S']['2']['19']['70'])
            && ($this->usrNvj == 4)
        ) {
            $c = new stdClass();
            $c->id = 'aplicacion';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/aplicacion-egresos.png';
            $c->label = 'APLICACIÓN';
            $c->title = 'Aplicación del Reembolso';
            $cards[] = $c;
        }

        /* BOTON PARA LA CONSULTA DE REINTEGROS */
        if ($this->usrNvj >= 4) {
            $c = new stdClass();
            $c->id = 'consulta';
            $c->titulo = 'REEMBOLSO';
            $c->img = 'img/consulta-egresos.png';
            $c->label = 'CONSULTA';
            $c->title = 'Consulta de Reembolso';
            $cards[] = $c;
        }

        $this->mixHtml($this->ponCards($cards));

        $this->mixScript('
            limpiaClasAdmin();
            $("#generacion").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"generacion"
                });
                return false;
            });

            $("#autorizacion").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"autorizacion"
                });
                return false;
            });

            $("#enviar").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"enviar"
                });
                return false;
            });
            
            
            $("#recepcion").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"recepcion"
                });
                return false;
            });
                
            $("#revision").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"revision"
                });
                return false;
            });

            $("#aplicacion").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"aplicacion"
                });
                return false;
            });
            
            $("#impresion").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ponTablaListadoReintegros",
                    tarea:"impresion"
                });
                return false;
            });
            
            $("#consulta").click(function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"ConsultaReintegro",
                    tarea:"consulta"
                });
                return false;
            });
        ');
        return false;
    }
    /**************************** TABLA PRINCIPAL QUE MUESTRA LOS REINTEGROS ***************************/
    private function ponTablaListadoReintegros()
    {
        $tabla = new stdClass();
        $tabla->id = 'tbl_Reintegros';


        if ($this->prm->tarea == 'generacion') {
            $tabla->titulo = 'Reembolsos de Anticipos de Obra de la Unidad Responsable';
        }
        if ($this->prm->tarea == 'autorizacion') {
            $tabla->titulo = 'Autorización de Reembolsos';
        }
        if ($this->prm->tarea == 'enviar') {
            $tabla->titulo = 'Envío de Reembolsos';
        }
        if ($this->prm->tarea == 'impresion') {
            $tabla->titulo = 'Reembolsos de Anticipo de Obras de Ejercicios Anteriores';
        }
        if ($this->prm->tarea == 'recepcion') {
            $tabla->titulo = 'Recepción de Reembolsos';
        }
        if ($this->prm->tarea == 'revision') {
            $tabla->titulo = 'Revisión de Reembolsos';
        }
        if ($this->prm->tarea == 'aplicacion') {
            $tabla->titulo = 'Aplicación de Reembolsos';
        }

        $tools = '<div class="row">';
        $tools .= '
        <div class="form-floating mb-3 col-md-2 col-xs-12">
            <input type="text" class="form-control" id="ur" placeholder="">
            <label for="ur" class="mx-2">UR</label>
        </div>
        
        <!--
        <fieldset class="col-md-2 col-xs-12">
            UR <input type="text" maxlength="3" id="ur" class="solonumero form-control">
        </fieldset>
        -->
        ';
        $tools .= '
        <div class="form-floating mb-3 col-md-2 col-xs-12">
            <input type="text" class="form-control" id="rao_id" placeholder="">
            <label for="rao_id" class="mx-2">ID</label>
        </div>
        <!--
            <fieldset class="col-md-2 col-xs-12">
                ID <input type="text" id="rao_id" class="solonumero form-control">
            </fieldset>
        -->
        ';
        $tools .= '
        <div class="form-floating mb-3 col-md-2 col-xs-12">
            <input type="text" class="form-control" id="cvefin" placeholder="">
            <label for="cvefin" class="mx-2">Clave de Financiamiento</label>
        </div>
        <!--
            <fieldset class="col-md-2 col-xs-12">
                Clavefin<input type="text" id="cvefin" class="form-control" maxlength="9">
            </fieldset>
        -->
        ';
        $tools .= '
            <fieldset class="mb-3 col-md-1 col-xs-12">
                <button id="buscar" class="btn btn-test btn-outline-secondary">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar
                </button>
            </fieldset>
        ';
        $tools .= '
            <fieldset class="mb-3 col-md-1 col-xs-12">
                <button id="limpiar" class="btn btn-test btn-outline-secondary">
                    <i class="fa-solid fa-broom me-2"></i>Limpiar
                </button>
            </fieldset>
        ';
        $tools .= '</div>';


        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = 'scp/afectaciones/ControladorReintegroAnticipoObra.php';
        $tabla->comunica->metodo = 'llenaTablaReintegros';
        $tabla->comunica->tarea = $this->prm->tarea;


        if ($this->usrNvj > 2) {
            $tabla->tools = $tools;
            $tabla->comunica->gpour = '{$("#ur").val()}';
            $tabla->comunica->rao_id = '{$("#rao_id").val()}';
            $tabla->comunica->cvefin = '{$("#cvefin").val()}';
        }

        /******+************************* SE INICIA LA TABLA ***************************************/
        $tr = [];

        if ($this->prm->tarea == 'recepcion' && $this->usrNvj > 2) {
            $th = new stdClass();
            $th->width = '1%';
            $th->align = 'center';
            $th->texto = '';
            array_push($tr, $th);
        }

        /*  VER/EDITAR */

        $th = new stdClass();
        $th->width = '4%';
        $th->align = 'center';
        $th->texto = 'Ver';
        if ($this->usrNvj <= 2) {
            $th->texto = 'Ver / Editar';
        }
        if ($this->prm->tarea == 'impresion') {
            $th->texto = 'Acción';
        }
        array_push($tr, $th);

        if ($this->usrNvj > 2) {
            $th = new stdClass();
            $th->width = '5%';
            $th->align = 'center';
            $th->texto = 'UR';
            array_push($tr, $th);
        }

        //N° de Reintegro
        $th = new stdClass();
        $th->width = '3%';
        $th->align = 'center';
        $th->texto = 'ID';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '5%';
        $th->align = 'center';
        $th->texto = 'Obra';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '52%';
        $th->align = 'center';
        $th->texto = 'Nombre';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Clave Fin';
        array_push($tr, $th);

        $th = new stdClass();
        $th->width = '10%';
        $th->align = 'center';
        $th->texto = 'Fecha Reembolso';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '14%';
        $th->align = 'center';
        $th->texto = 'Importe';
        array_push($tr, $th);

        /************************ SE INICIA EL CUERPO DE LA TABLA ***************************************/

        $trbody = [];

        if ($this->prm->tarea == 'recepcion' && $this->usrNvj > 2) {
            $td = new stdClass();
            $td->tipo = 'check';
            $td->attr = new stdClass();
            $td->attr->rao_id = 'rao_id';
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
            $td->class = 'recibirreintegro';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->attr = new stdClass();
        $td->attr->rao_id = 'rao_id';
        if ($this->usrNvj > 2) {
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
        }
        $td->align = 'center';
        $td->class = 'EditarReintegro btn btn-xs';
        $td->icono = 'pencil';
        if ($this->prm->tarea != 'generacion') {
            $td->icono = 'solid fa-eye';
        }
        if ($this->prm->tarea == 'enviar') {
            $td->icono = 'solid fa-eye';
        }
        if ($this->prm->tarea == 'impresion') {
            $td->icono = 'solid fa-print';
            $td->class = 'imprimeReintegro';
        }
        if ($this->usrNvj > 2) {
            $td->icono = 'solid fa-eye';
        }

        array_push($trbody, $td);

        if ($this->usrNvj > 2) {
            $td = new stdClass();
            $td->align = 'center';
            $td->campoTxt = 'gpour';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'ecf_clave';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_fechaReintegro';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');

        $botones = '
            <button id="btnRegresarIndex" type="button" class="btn btn-test btn-outline-secondary me-1" title="Regresar al Menú Principal">
                <i class="fa fa-circle-left me-2"></i>Regresar
            </button>
        ';

        switch ($this->prm->tarea) {
            case 'generacion':
                $botones .= '
                    <button id="addReintegro" type="button" class="btn btn-test btn-outline-primary nuevo" title="Registrar nuevo Reembolso">
                        <i class="fa fa-plus me-2"></i>Nuevo Reembolso
                    </button>
                ';
                $this->mixScript('
                    $("#addReintegro").click (function(){
                        comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                            "metodo":"addReintegro",
                            "tarea":"' . $this->prm->tarea . '"
                        });
                        return false;
                    });
                ');
                break;
            case 'recepcion':
                if ($this->usrNvj > 2) {
                    $botones .= '
                        <button id="recibirtodas" type="button" class="btn btn-test btn-outline-success me-1" title="Recibir todos los seleciconados">
                            <i class="fa-solid fa-download me-2"></i>Recibir
                        </button>
                    ';
                }
                break;
        }
        $this->agregaTools($botones);
        $script = '';
        $this->llenaTablaReintegros();

        $this->mixScript($script . '
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
            
            $("#limpiar").click(function(){
                if($("#ur").val()!="" ||  $("#rao_id").val()!="" ||	$("#cvefin").val()!=""){
                    $("#ur").val("");
                    $("#rao_id").val("");
                    $("#cvefin").val("");
                    ' . $this->wrLlamaLlenaTabla('tbl_Reintegros') . '
                }
            });
            
            $("#buscar").click(function(){
                if ($("#ur").val() != "" ||  $("#rao_id").val() != "" || $("#cvefin").val() != "") {
                console.log("asbbb");
                    ' . $this->wrLlamaLlenaTabla('tbl_Reintegros') . '
                }
            });

            $("#btnRegresarIndex").click (function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"main"
                });
            });
                
            $("#recibirtodas").click (function(){
                cuantas = 0;
                datos = new Array();
                mensaje="";
                $(".recibirreintegro").each(function(){
                    if($(this).is(":checked")){
                        cuantas ++;
                        datos.push({"gpo_id":$(this).attr("gpo_id"),"ur_id":$(this).attr("ur_id"),"rao_id":$(this).attr("rao_id")});
                        
                    }
                });

                if (cuantas == 0) {
                    notify("Debe seleccionar al menos un reembolso para ser recibido","error");
                    return false;
                } else {
                    switch(cuantas) {
                        case 1:
                            mensaje = "Esta seguro de recibir el Reembolso seleccionado";
                            break;
                        case 2:
                            mensaje = "Esta seguro de recibir los dos Reembolsos seleccionados";
                            break;
                        default:
                            mensaje = "Esta seguro de recibir los " + cuantas + " Reembolsos seleccionados";
                            break;
                    }

                    alertify.confirm(mensaje, "Recibir Reembolso", function(){
                        comunica("' . $this->pantalla . '",{
                            "datos":datos,
                            metodo:"tareasEstados",
                            tarea:"' . $this->prm->tarea . '"
                        });
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                }
            });
        ');
    }
    /************************ FUNCIÓN QUE LLENA LA TABLE DEL REINTEGRO****************************/
    private function llenaTablaReintegros()
    {
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;

        if (isset($this->prm->gpour) && strlen($this->prm->gpour) == 3 && $this->usrNvj > 2) {
            $gpo_id = substr($this->prm->gpour, 0, 1);
            $ur_id = substr($this->prm->gpour, 1, 2);
        }

        $wh = '';
        $params = [];
        $sql = '
            SELECT 
                gpo_id,ur_id,CONCAT(gpo_id,ur_id) AS gpour,rao_id,oa_id,oa_nombre,
                ecf_clave,rao_anioReintegro,rao_fechaReintegro,rao_importe,era_id
            FROM scp_ReintegroAnticipoObra
                INNER JOIN enc_Programatico USING (gpo_id,ur_id,epg_id)
                INNER JOIN cat_ObraActividad USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)';

        if ($this->usrNvj == 1 || $this->usrNvj == 2 || (isset($this->prm->gpour)
            && strlen($this->prm->gpour) == 3 && $this->usrNvj > 2)) {
            $wh .= 'WHERE gpo_id = :gpo_id AND ur_id = :ur_id';
            $params[':gpo_id'] = $gpo_id;
            $params[':ur_id'] = $ur_id;
        }

        switch ($this->prm->tarea) {
            case 'generacion':
                if ($wh == '') {
                    $wh .= ' WHERE era_id IN (1,7)';
                } else {
                    $wh .= ' AND era_id IN (1,7)';
                }
                break;
            case 'autorizacion':
                if ($wh == '') {
                    $wh .= ' WHERE era_id= 1';
                } else {
                    $wh .= ' AND era_id= 1';
                }
                break;
            case 'enviar':
                if ($wh == '') {
                    $wh .= ' WHERE era_id= 2 ';
                } else {
                    $wh .= ' AND era_id=2 ';
                }
                break;
            case 'impresion':
                if ($wh == '') {
                    $wh .= ' WHERE era_id IN (2,3)';
                } else {
                    $wh .= ' AND era_id IN (2,3)';
                }
                break;
            case 'recepcion':
                if ($wh == '') {
                    $wh .= ' WHERE era_id= 3 ';
                } else {
                    $wh .= ' AND era_id=3 ';
                }
                break;
            case 'revision':
                if ($wh == '') {
                    $wh .= ' WHERE era_id= 4 ';
                } else {
                    $wh .= ' AND era_id=4 ';
                }
                break;
            case 'aplicacion':
                if ($wh == '') {
                    $wh .= ' WHERE era_id= 5 ';
                } else {
                    $wh .= ' AND era_id=5 ';
                }
                break;
        }

        if (
            isset($this->prm->cvefin) && $this->prm->cvefin != '' &&
            strlen($this->prm->cvefin) == 9 && $this->usrNvj > 2
        ) {
            if ($wh == '') {
                $wh .= ' WHERE CONCAT(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) = :cvefin';
                $params[':cvefin'] = $this->prm->cvefin;
            } else {
                $wh .= ' AND CONCAT(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio) = :cvefin ';
                $params[':cvefin'] = $this->prm->cvefin;
            }
        }

        if (isset($this->prm->rao_id) && $this->prm->rao_id != '' && $this->usrNvj > 2) {
            if ($wh == '') {
                $wh .= ' WHERE rao_id = :rao_id';
                $params[':rao_id'] = $this->prm->rao_id;
            } else {
                $wh .= '  AND rao_id = :rao_id';
                $params[':rao_id'] = $this->prm->rao_id;
            }
        }
        $this->setParams($params);
        $this->setSql($sql . $wh);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_Reintegros';
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".EditarReintegro").click(function() {
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    "metodo":"addReintegro",
                    tarea:"' . $this->prm->tarea . '",
                    "rao_id":$(this).attr("rao_id"),
                    ' . ($this->usrNvj > 2 ? 'gpo_id:$(this).attr("gpo_id"),ur_id:$(this).attr("ur_id"),' : '') . '
                    abc:"c"
                });
            });
            
            $(".imprimeReintegro").click(function(){
                inputUr=$("#inputUr").val();
                var form = $("<form>")
                .attr("action", "rep/doc/scp/afectaciones/ReintegroAnticipoObra.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "rao_id").attr("value",$(this).attr("rao_id") ))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }
    /********************************* FUNCIÓN QUE REGISTRA UN REINTEGRO*******************************/
    private function addReintegro()
    {
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;

        $ab = '';

        $this->mixScript(' 
            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
        
            var progID = "encProg";
            var progNom = "nomProg";
            var cveFID = "encCveFin";
            var cveFNom = "nomCveFin";
        ');

        $UndadREs = new UnidadResponsable();
        $ReintegroAnticipoObra = null;

        if (isset($this->prm->rao_id)) {
            $abc = 'c';
            if ($this->usrNvj < 3) {
                $this->prm->gpo_id = $gpo_id;
                $this->prm->ur_id = $ur_id;
            }
            $ReintegroAnticipoObra = new ReintegroAnticipoObra($this->prm);
        } else {
            $this->prm->gpo_id = $gpo_id;
            $this->prm->ur_id = $ur_id;
            $abc = 'a';
            $ReintegroAnticipoObra = new ReintegroAnticipoObra();
            $UndadREs->setGpo_id($gpo_id);
            $UndadREs->setUr_id($ur_id);
            $UndadREs->select();
            $this->prm->nombreAdministrativo = $UndadREs->getNombreAdministrativo();
            $this->prm->cargoAdministrativo = $UndadREs->getCargoAdministrativo();
            $this->prm->nombreTitular = $UndadREs->getNombreTitular();
            $this->prm->cargoTitular = $UndadREs->getCargoTitular();
        }

        $html = '';

        $encProg = new stdClass();
        $encProg->id = 'encProg';
        $encProg->placeholder = 'Encuadre (F1/F2)';
        $encProg->required = true;
        $encProg->maxlength = 20;
        $encProg->class = 'F1Catalogo';

        $nomProg = new stdClass();
        $nomProg->id = 'nomProg';
        $nomProg->placeholder = 'Nombre Obra/Actividad';
        $nomProg->lectura = true;

        $encCveFin = new stdClass();
        $encCveFin->id = 'encCveFin';
        $encCveFin->placeholder = 'Clave (F1/F2)';
        $encCveFin->required = true;
        $encCveFin->maxlength = 12;
        $encCveFin->class = 'F1Catalogo';

        $nomCveFin = new stdClass();
        $nomCveFin->id = 'nomCveFin';
        $nomCveFin->placeholder = 'Nombre de la Clave';
        $nomCveFin->lectura = true;

        $progFin = new stdClass();

        if ($abc == 'c') {
            $_SESSION[_S]['clasAdm-ue'] = $ReintegroAnticipoObra->getUe_id();
            $progFin->epg_id = $ReintegroAnticipoObra->getEpg_id();
            $progFin->ecf_id = $ReintegroAnticipoObra->getEcf_id();
        }

        switch ($ReintegroAnticipoObra->getEra_id()) {
            case 7:
                $sql = ' 
                SELECT 
                    bit_observacion 
                FROM ' . BD_BITACORA . '.bit_ReintegroAnticipo 
                WHERE rao_id = ?
                    AND era_id = 7 
                ORDER BY usr_fecha 
                DESC LIMIT 1';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar([$this->prm->rao_id]);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $r = $this->extraer();
                    $html .= '
                    <div class="alert alert-danger" role="alert">
                    <p>' . $r['bit_observacion'] . '</p>
                    </div>
                ';
                }
        }

        /* ----------- PARA VALIDAR IMPORTES DE  REINTEGRO CON ANTICPO DE OBRA ( SOLO ANDREA)-----------*/
        if ($this->prm->tarea == 'aplicacion' || $this->prm->tarea == 'revision') {
            $sql = '
                SELECT (oa_anticipo + oa_anticipo' . APP_EJERCICIO_ANTERIOR . ') as oa_anticipo, oa_nombre
                FROM enc_Programatico 
                INNER JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id) 
                WHERE epg_id = ?;
            ';

            $c = new AccesoDatos();
            $c->conectar();
            $c->setSql($sql);
            $c->consultar([$ReintegroAnticipoObra->getEpg_id()]);
            $data = $c->extraer();
            if ($ReintegroAnticipoObra->getImporte() > $data['oa_anticipo']) {
                $html .= '<div class="alert alert-danger">Atencion el Reembolso supera el importe por amortizar de la obra ' . $data['oa_nombre'] . ' $' . number_format($data['oa_anticipo']) . '</div>';
            }
            $c->desconectar();
            unset($c);
        }

        $progFin->filtro = 'Reintegro';
        $html .= '<div id="contenedorCFModal"></div>' . CatalogoProgramaticoFinanciamiento::ponF1($progFin) . '</div>';
        $cveAdmitiva = new stdClass();
        $cveAdmitiva->filtro = 'poa';
        $cveAdmitiva->toa_id = 2;
        $cveAdmitiva->oa_anticipo = true;
        $cveAdmitiva->oa_origen = 'R';

        if ($abc == 'c') {
            if ($ReintegroAnticipoObra->getEpg_id() <> '') {
                $this->conectar();
                $this->setSql('
                    SELECT prg_id,spr_id,py_id,oa_id 
                    FROM enc_Programatico 
                    WHERE epg_id = ?');
                $this->consultar([$ReintegroAnticipoObra->getEpg_id()]);
                $this->desconectar();
                $epg = $this->extraer();
                $cveAdmitiva->prg_id = $epg['prg_id'];
                $cveAdmitiva->spr_id = $epg['spr_id'];
                $cveAdmitiva->py_id = $epg['py_id'];
                $cveAdmitiva->oa_id = $epg['oa_id'];
                $cveAdmitiva->ue_id = $ReintegroAnticipoObra->getUe_id();
                $cveAdmitiva->lectura = true;
            }
        }

        $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));

        /* Se muestra el número el nombre de la obra de inversion por lo que se esta solicitando el recurso  */
        $obra = new stdClass();
        $obra->id = 'NomObra';
        $obra->label = 'N° de Obra y Nombre';
        $obra->readonly = true;
        $obra->class = 'habilitar';
        $obra->enDiv = true;
        $obra->maxlength = 15;

        /* Se muestra el numero de oficio que se elaboro para autorizar la obra( para obras de refrendo lo asigna egreso ypara obras del ejercicio presente lo asigna planeacion) */
        $oficioAutorizacion = new stdClass();
        $oficioAutorizacion->id = 'oficioaut';
        $oficioAutorizacion->label = 'Oficio de Autorización';
        $oficioAutorizacion->class = 'habilitar';
        $oficioAutorizacion->readonly = true;
        $oficioAutorizacion->enDiv = true;
        $oficioAutorizacion->maxlength = 15;

        /* Se muestra el numero que originalmente le fue asignado ala obra por planeacion */
        $NumObra = new stdClass();
        $NumObra->id = 'NumObra';
        $NumObra->label = 'N° de Obra SPPP/SPIP:';
        $NumObra->class = 'habilitar';
        $NumObra->readonly = true;
        $NumObra->enDiv = true;
        $NumObra->maxlength = 15;

        /* Se muestra la subfuente  */
        $subfuente = new stdClass();
        $subfuente->id = 'subfuente';
        $subfuente->label = 'Subfuente';
        $subfuente->class = 'habilitar';
        $subfuente->enDiv = true;
        $subfuente->readonly = true;
        $subfuente->maxlength = 15;

        /* Muestra la aportacion de acuerdo a lo que seleccionaron en la clave de financiamiento  */
        $aportacion = new stdClass();
        $aportacion->id = 'aportacion';
        $aportacion->label = 'Aportación';
        $aportacion->class = 'habilitar';
        $aportacion->enDiv = true;
        $aportacion->readonly = true;
        $aportacion->maxlength = 15;

        /* Numero del contrato de la obra se aumento el numero */
        $obracontrato = new stdClass();
        $obracontrato->id = 'obracontrato';
        $obracontrato->label = 'Obra por contrato  no.:';
        $obracontrato->placeholder = 'Número del contrato de la obra';
        $obracontrato->class = 'habilitar';
        $obracontrato->enDiv = true;
        $obracontrato->maxlength = 50;

        /*Es el año de la clave de financiamiento que se esta usando   */
        $ejercicio = new stdClass();
        $ejercicio->id = 'ejercicio';
        $ejercicio->label = 'Ejercicio';
        $ejercicio->class = 'habilitar';
        $ejercicio->enDiv = true;
        $ejercicio->readonly = true;
        $ejercicio->maxlength = 15;

        /*Numero de obra del ejercicio Anterior  */
        $referencia = new stdClass();
        $referencia->id = 'referencia';
        $referencia->label = 'Referencia';
        $referencia->class = 'habilitar';
        $referencia->enDiv = true;
        $referencia->readonly = true;
        $referencia->maxlength = 30;
        if ($this->prm->tarea != 'generacion') {
            $referencia->readonly = true;
        }

        /* Se muestra el numero de oficio que se elaboro para autorizar la obra( para obras de refrendo lo asigna egreso ypara obras del ejercicio presente lo asigna planeacion) */
        $oficioReintegro = new stdClass();
        $oficioReintegro->id = 'oficioReintegro';
        $oficioReintegro->label = 'Oficio del Reembolso';
        $oficioReintegro->title = 'ACUSE DEL OFICIO  MEDIANTE EL CUAL SE INFORMÓ EL REEMBOLSO A LA TESORERÍA.';
        $oficioReintegro->class = 'habilitar';
        $oficioReintegro->value = $ReintegroAnticipoObra->getOficio();
        $oficioReintegro->enDiv = true;
        $oficioReintegro->required = true;
        $oficioReintegro->maxlength = 20;
        if ($this->prm->tarea != 'generacion') {
            $oficioReintegro->readonly = true;
        }

        $FechaReintgro = new stdClass();
        $FechaReintgro->id = 'FechaReintgro';
        $FechaReintgro->name = 'FechaReintgro';
        $FechaReintgro->label = 'Fecha del Reembolso';
        $FechaReintgro->class = 'datepicker habilitar';
        $FechaReintgro->value = $ReintegroAnticipoObra->getFechaReintegro();
        $FechaReintgro->enDiv = true;
        $FechaReintgro->formato = 'fecha';
        $FechaReintgro->placeholder = date('Y-d-m');
        if ($this->usrNvj > 2) {
            $FechaReintgro->readonly = true;
        }

        $comboDoc = new stdClass();
        $comboDoc->id = 'comboDoc';
        $comboDoc->name = 'comboDoc';
        $comboDoc->mensaje = 'Selecciona';
        $comboDoc->required = true;
        $comboDoc->label = 'Tipo de Docto. de Pago';
        $comboDoc->class = 'habilitar';
        $comboDoc->selecciona = $ReintegroAnticipoObra->getTdf_id();
        $comboDoc->comoAtr = 'tdf_id';
        $comboDoc->enDiv = true;
        $comboDoc->sql = '
            SELECT * 
            FROM cat_TipoDocumentoFuente 
            WHERE tdf_id IN ("FD","SPEI")';

        /* Se muestra la referencia del Documento de Pago */
        $RefDocPago = new stdClass();
        $RefDocPago->id = 'RefDocPago';
        $RefDocPago->label = 'Referencia del Docto. de Pago';
        $RefDocPago->required = true;
        $RefDocPago->class = 'habilitar';
        $RefDocPago->value = $ReintegroAnticipoObra->getDocumentoPago();
        $RefDocPago->enDiv = true;
        $RefDocPago->maxlength = 15;
        if ($this->prm->tarea != 'generacion') {
            $RefDocPago->readonly = true;
        }

        $importeOAnt = new StdClass();
        $importeOAnt->id = 'importeOAnt';
        $importeOAnt->class = 'habilitar';
        $importeOAnt->label = 'Importe máximo a Reembolsar';
        if ($this->prm->tarea == 'consulta') {
            $importeOAnt->label = 'Saldo de Anticipo por Amortizar';
        }
        $importeOAnt->required = true;
        $importeOAnt->enDiv = true;
        $importeOAnt->formato = 'moneda';
        $importeOAnt->readonly = true;
        //$importeOAnt->disable = true;
        $importeOAnt = $this->ponInput($importeOAnt);

        $importe = new StdClass();
        $importe->id = 'importe';
        $importe->class = 'habilitar';
        $importe->value = $ReintegroAnticipoObra->getImporte();
        $importe->label = 'Importe del Reembolso';
        $importe->required = true;
        $importe->enDiv = true;
        $importe->formato = 'moneda';
        if ($this->prm->tarea != 'generacion') {
            $importe->readonly = true;
            //$importe->disable = true;
        }
        $importe = $this->ponInput($importe);


        /* ESTE ES EL INPUTE DEL IMPORTE DEL ANTICIPO MENOS EL IMPORTE CAPTURADO */
        $SaldoAntAmort = new StdClass();
        $SaldoAntAmort->id = 'SaldoAntAmort';
        $SaldoAntAmort->class = 'habilitar';
        $SaldoAntAmort->label = 'Saldo de Anticipo por Amortizar';
        $SaldoAntAmort->required = true;
        $SaldoAntAmort->enDiv = true;
        $SaldoAntAmort->formato = 'moneda';
        $SaldoAntAmort->readonly = true;
        //$SaldoAntAmort->disable = true;
        $SaldoAntAmort = $this->ponInput($SaldoAntAmort);

        $administrativo = new StdClass();
        $administrativo->id = 'administrativo';
        $administrativo->label = 'Jefe Administrativo';
        $administrativo->enDiv = true;
        $administrativo->readonly = true;
        if ($abc == 'a') {
            $administrativo->value = $this->prm->nombreAdministrativo;
        } else {
            $administrativo->value = $ReintegroAnticipoObra->getNombreAdministrativo();
        }

        $cargoAdmitivo = new StdClass();
        $cargoAdmitivo->id = 'cargoAdmitivo';
        $cargoAdmitivo->label = 'Cargo Administrativo';
        $cargoAdmitivo->enDiv = true;
        $cargoAdmitivo->readonly = true;
        if ($abc == 'a') {
            $cargoAdmitivo->value = $this->prm->cargoAdministrativo;
        } else {
            $cargoAdmitivo->value = $ReintegroAnticipoObra->getCargoAdministrativo();
        }
        $titular = new StdClass();
        $titular->id = 'titular';
        $titular->label = 'Nombre del Titular';
        $titular->enDiv = true;
        $titular->readonly = true;
        if ($abc == 'a') {
            $titular->value = $this->prm->nombreTitular;
        } else {
            $titular->value = $ReintegroAnticipoObra->getNombreTitular();
        }

        $cargoTitular = new StdClass();
        $cargoTitular->id = 'cargoTitular';
        $cargoTitular->label = 'Cargo del Titular';
        $cargoTitular->enDiv = true;
        $cargoTitular->readonly = true;
        if ($abc == 'a') {
            $cargoTitular->value = $this->prm->cargoTitular;
        } else {
            $cargoTitular->value = $ReintegroAnticipoObra->getCargoTitular();
        }

        $html .= '
            <legend align="center">Información de la  Obra</legend><br>
                <div class="row">
                    <fieldset class="col-md-6">
                    ' . $this->ponInput($obra) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                    ' . $this->ponInput($NumObra) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                    ' . $this->ponInput($oficioAutorizacion) . '
                    </fieldset>
                </div>

                <div class="row">	
                    <fieldset class="col-md-3">
                    ' . $this->ponInput($subfuente) . '
                    </fieldset>

                    <fieldset class="col-md-3">
                    ' . $this->ponInput($aportacion) . '
                    </fieldset>
                    <fieldset class="col-md-3">
                    ' . $this->ponInput($ejercicio) . '
                    </fieldset>

                    <fieldset class="col-md-3">
                    ' . $this->ponInput($referencia) . '
                    </fieldset>
                </div>
        ';
        $this->mixStyle('
            .quitarArchivo{
                transition:all ease-in-out .5s;color:#3a87ad; background-color:#FFF;display:inline-block; vertical-align:top;padding:1px 5px;border:1px solid #FFF; cursor:default; border-radius:3px; margin-left:5px;
            }
            .quitarArchivo:hover{
                background-color:#900;
                color:#FFF;
            }
        ');
        $html .= '
            <div class="row mb-4">
                <fieldset class="col-md-6 col-xs-12">
                    <legend align="center" >Información del Reembolso</legend>
                    ' . $this->ponInput($oficioReintegro) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">
                    <legend>' . ($ReintegroAnticipoObra->getEra_id() > 1 ? '' : 'Cargue el') . ' oficio del Reembolso</legend>
                    <div id="dropzone">
                        <div style="width:100%;">
        ';
        /*  SI EL ESTADO ES 1  O EL REGISTRO ES NUEVO SE MUESTRA EL INPUT PARA CARGAR EL DOCUMENTO */
        if (
            $ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7
            || $ReintegroAnticipoObra->getEra_id() == ''
        ) {
            $html .= '<input type="file" equis="' . $ReintegroAnticipoObra->getArchivoOficio() . '" name ="objetivosFile" id="nombreArchObj" data-url="scp/afectaciones/ControladorReintegroAnticipoObra.php" data-form-data=\'{"metodo":"upload","origen":"objetivosFile"}\' >';
        }

        $html .= '<div id="resultado1" style="color:#3a87ad">';

        if ($ReintegroAnticipoObra->getArchivoOficio() != '') {
            $html .= '<a href="scp/afectaciones/docReintegro/' . $ReintegroAnticipoObra->getGpo_id() .
                $ReintegroAnticipoObra->getUr_id() . '/' . $ReintegroAnticipoObra->getArchivoOficio() . '" target="_blank">' .
                $ReintegroAnticipoObra->getArchivoOficio() . '</a>';
            /*  SI EL ESTADO ES 1  Y ES EDICION SE MUESTRA EL BOTON QUITAR PARA EL DOCUMENT YA CARGADO */
            if (($ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7)
                && $this->prm->tarea == 'generacion'
            ) {
                $html .= '<div class="quitarArchivo" id="quitaprimero">X Quitar</div>';
            }
        }

        $html .= '</div>';
        /*  SI EL ESTADO ES 1  O EL REGISTRO ES NUEVO SE MUESTRA EL PROGRESS BAR PARA EL INPUT DE CARGAR EL DOCUMENTO */
        if (
            $ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7
            || $ReintegroAnticipoObra->getEra_id() == ''
        ) {
            $html .= '
                <div id="progressObj" class="progress progress-striped" style="margin-bottom:0px">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        <span class="sr-only">100% Complete</span>
                    </div>
                </div>
            ';
        }
        $html .= '</div></div>
                </fieldset>
                
                <fieldset class="col-md-6 col-xs-12">
                    ' . $this->ponCombo($comboDoc) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">
                ' . $this->ponInput($RefDocPago) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6 mb-2">
                    <legend>' . ($ReintegroAnticipoObra->getEra_id() > 1 ? '' : 'Cargue') . ' Documento de Pago</legend>
                    <div id="dropzone">
                        <div style="width:100%;">
        ';
        if (
            $ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7
            || $ReintegroAnticipoObra->getEra_id() == ''
        ) {
            $html .= '<input equis="' . $ReintegroAnticipoObra->getArchivoDocumentoPago() . '"  type="file" name ="doctoPago" id="nombreArchObj2"  data-url="scp/afectaciones/ControladorReintegroAnticipoObra.php" data-form-data=\'{"metodo":"upload","origen":"doctoPago"}\' >';
        }
        $html .= '<div id="resultado2" style="color:#3a87ad">';

        if ($ReintegroAnticipoObra->getArchivoDocumentoPago() != '') {
            $html .= '<a href="scp/afectaciones/docReintegro/' . $ReintegroAnticipoObra->getGpo_id() . $ReintegroAnticipoObra->getUr_id() .
                '/' . $ReintegroAnticipoObra->getArchivoDocumentoPago() . '" target="_blank">
                ' . $ReintegroAnticipoObra->getArchivoDocumentoPago() . '</a>';
            if (($ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7)
                && $this->prm->tarea == 'generacion'
            ) {
                $html .= '<div class="quitarArchivo" id="quitasegundo">X Quitar</div>';
            }
        }

        $html .= '</div>';

        if (
            $ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7
            || $ReintegroAnticipoObra->getEra_id() == ''
        ) {
            $html .= '
                <div id="progressObj2" class="progress progress-striped" style="margin-bottom:0px">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                        <span class="sr-only">100% Complete</span>
                    </div>
                </div>
            ';
        }

        $html .= '</div></div>
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">
                    ' . $importeOAnt . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-12">
                    ' . $importe . '
                </fieldset>';
        if ($this->prm->tarea != 'consulta') {
            $html .= '
                    <fieldset class="col-md-6 col-xs-12">
                    ' . $SaldoAntAmort . '
                    </fieldset>';
        }
        $html .= '
                <fieldset class="col-md-6 col-xs-12">
                    ' . $this->ponInput($FechaReintgro) . '
                </fieldset>
            </div>
                
        ';

        $html .= '
            <legend align="center">Firmas que debe tener el Reembolso</legend><br>
            <div class="row">
                <fieldset class="col-md-6">
                ' . $this->ponInput($titular) . '
                </fieldset>
                <fieldset class="col-md-6">
                ' . $this->ponInput($administrativo) . '
                </fieldset>
            </div>	
            <div class="row">
                <fieldset class="col-md-6">
                ' . $this->ponInput($cargoTitular) . '
                </fieldset>
                <fieldset class="col-md-6">
                ' . $this->ponInput($cargoAdmitivo) . '
                </fieldset>
            </div>
        ';

        $botones = '';
        /* -----------  botones de REGRESAR, GENERAR, ACTUALIZAR -------------------------------------------------------*/
        $botones .= '
            <button id="btnRegresarIndex" type="button" class="btn btn-test btn-outline-secondary me-1" title="Regresar a la Lista de Reembolsos">
                <i class="fa fa-circle-left me-2"></i>Regresar
            </button>
        ';
        if ($this->prm->tarea == 'generacion') {
            $botones .= '
                <button id="btnGuardaReintegro" type="button" form="Reintegro" class="btn btn-test btn-outline-success me-1" title="' . ($abc == 'a' ? 'Guardar Reembolso' : 'Actualizar Reembolso') . '">
                    <i class="fa fa-floppy-disk me-2"></i>' . ($abc == 'a' ? 'Guardar' : 'Actualizar') . '
                </button>
            ';
            if ($ReintegroAnticipoObra->getEra_id() == 1 || $ReintegroAnticipoObra->getEra_id() == 7) {
                $botones .= '
                    <button id="btnEliminar" type="button" form="Reintegro" class="btn btn-test btn-outline-danger " title="Eliminar Reembolso">
                        <i class="fa fa-trash me-2"></i>' . ($abc == 'a' ? 'Guardar' : 'Eliminar') . '
                    </button>
                ';
            }
        }

        $html .= '

            <div class="modal" tabindex="-1" id="rechazarReembolso">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reembolso - Motivo de Cancelado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <textarea id="motivoRechazo" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                    <button type="button" id="btnRechazaCta" class="btn btn-outline-success">Aceptar</button>
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                    </div>
                </div>
            </div>
        ';
        /* ----------- pra los botones de Autorización, Envio, RECEPCION --------------------*/
        $botongral = '';
        $btonRechazo = '';
        switch ($this->prm->tarea) {
            case 'autorizacion':
                $botones .= '
                    <button id="btnAutorizar" type="button" class="autorizar btn btn-test btn-outline-success" title="Autorizar Reembolso">
                        <i class="fa-solid fa-circle-check me-2"></i>Autorizar
                    </button>';
                break;
            case 'enviar':
                $botones .= '
                    <button id="btnEnviar" type="button" class="enviar btn btn-test btn-outline-success me-1" title="Enviar Reembolso">
                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar
                    </button>';
                $btonRechazo .= '
                    <button id="btnRechazarUr" type="button" class="rechazar btn btn-test btn-outline-danger" title="Cancelar Reembolso">
                        <i class="fa-solid fa-circle-xmark me-2"></i>Cancelar
                    </button>';
                break;
            case 'recepcion':
                $botongral .= '
                    <button id="RecibelReintegro" type="button" class="recibir btn btn-test btn-outline-success me-1" title="Recibir Reembolso">
                        <i class="fa-solid fa-circle-check me-2"></i>Recibir
                    </button>';
                if ($_SESSION[_S]['prm']['S']['2']['19']['71']) {
                    $btonRechazo .= '
                        <button id="btnRechazar" type="button" class="rechazar btn btn-test btn-outline-danger" title="Cancelar Reembolso">
                            <i class="fa-solid fa-circle-xmark me-2"></i>Cancelar
                        </button>';
                }
                break;
            case 'revision':
                $botongral .= '
                    <button id="RevisaReintegro" type="button" class="revisar btn btn-test btn-outline-success me-1" title="Revisar Reembolso">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Revisar
                    </button>';
                if ($_SESSION[_S]['prm']['S']['2']['19']['71']) {
                    $btonRechazo .= '
                        <button id="btnRechazar" type="button" class="rechazar btn btn-test btn-outline-danger me-1" title="Cancelar Reembolso">
                            <i class="fa-solid fa-circle-xmark me-2"></i>Cancelar
                        </button>';
                }
                break;
            case 'aplicacion':
                $botongral .= '
                    <button id="AplicarReintegro" type="button" class="aplicar btn btn-test btn-outline-success me-1" title="Aplicar Reembolso">
                        <i class="fa-solid fa-spell-check me-2"></i>Aplicar
                    </button>';
                if ($_SESSION[_S]['prm']['S']['2']['19']['71']) {
                    $btonRechazo .= '
                        <button id="btnRechazar" type="button" class="rechazar btn btn-test btn-outline-danger" title="Cancelar Reembolso">
                            <i class="fa-solid fa-circle-xmark me-2"></i>Cancelar
                        </button>';
                }
                break;
        }
        $this->mixHtml($html);

        $this->agregaTools($botones . $botongral . $btonRechazo);
        if ($this->usrNvj > 2) {
            $this->mixScript('
                $("#RecibelReintegro").click(function(){
                    alertify.confirm("Recibir Reembolso", "¿Esta seguro de Recibir el Reembolso?", function(){
                        comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                            metodo:"tareasEstados",
                            rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                            "tarea":"' . $this->prm->tarea . '",
                            gpo_id:' . $this->prm->gpo_id . ',
                            ur_id:' . $this->prm->ur_id . '
                        });
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
            ');
        }

        if ($this->usrNvj > 2) {
            $this->mixScript('
                $("#RevisaReintegro").click(function(){
                    alertify.confirm("Revisar Reembolso", "¿Esta seguro de Revisar el Reembolso?", function(){
                        comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                            metodo:"tareasEstados",
                            rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                            "tarea":"' . $this->prm->tarea . '",
                            gpo_id:' . $this->prm->gpo_id . ',
                            ur_id:' . $this->prm->ur_id . '
                        });
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
            ');
        }

        if ($this->prm->tarea == 'generacion') {
            $this->mixScript('
                $("#btnEliminar").click(function(){
                    alertify.confirm("Eliminar Reembolso", "¿Esta seguro de eliminar el Reembolso?", function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"abcReintegro",
                            abc:"b",
                            rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '"
                        });
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
            
            ');
        }

        if ($this->usrNvj > 3) {
            $this->mixScript('
                $("#AplicarReintegro").click(function(){
                    alertify.confirm("¿Esta seguro de Aplicar el Reembolso?", "Aplicar Reembolso", function(){
                        comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                            metodo:"AplicacionReintegro",
                            rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                            "tarea":"' . $this->prm->tarea . '",
                            gpo_id:' . $this->prm->gpo_id . ',
                            ur_id:' . $this->prm->ur_id . ',
                            rao_importe:unformatNumber($("#importe").val()),
                            oa_anticipo:unformatNumber($("#importeOAnt").val())
                        });
                    },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                });
            ');
        }

        if ($this->usrNvj == 2) {
            $this->mixScript('
                $("#btnRechazarUr").click(function(){
                    $("#rechazarReembolso").modal("show");
                });
            
                $("#btnRechazaCta").click(function(){
                    $("#rechazarReembolso").modal("hide");
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        metodo:"RechazaReintegro",
                        "tarea":"' . $this->prm->tarea . '",
                        rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                        gpo_id:' . $this->prm->gpo_id . ',
                        ur_id:' . $this->prm->ur_id . ',
                        motivoRechazo:$("#motivoRechazo").val()
                    });
                    $("#rechazarReembolso").remove();
                    $(".modal-backdrop").remove();
                });
            ');
        }

        if ($this->usrNvj > 3) {
            $this->mixScript('
                $("#btnRechazar").click(function(){
                    $("#rechazarReembolso").modal("show");
                });
                $("#btnRechazaCta").click(function(){
                    $("#rechazarReembolso").modal("hide");
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        metodo:"RechazaReintegro",
                        "tarea":"' . $this->prm->tarea . '",
                        rao_id:"' . $this->prm->rao_id . '",
                            gpo_id:' . $this->prm->gpo_id . ',
                            ur_id:' . $this->prm->ur_id . ',
                            motivoRechazo:$("#motivoRechazo").val()
                    });
                });
            ');
        }

        if ($abc == 'c') {
            $this->prm->epg_id = $ReintegroAnticipoObra->getEpg_id();
            $this->prm->ecf_id = $ReintegroAnticipoObra->getEcf_id();
            $this->datosObra();
        }
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { 
                e.preventDefault(); 
                $("#homeMenu").click();
                e.stopPropagation();
            });

            $("#NomObra").blur(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"datosObra"
                });
            });
            $("#homeMenu").click(function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });

            $("#btnRegresarIndex").click (function(){
                $("div[aria-describedby=cargaDatosProgFin]").html("");
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        metodo:"' . ($this->prm->tarea != 'consulta' ? 'ponTablaListadoReintegros' : 'ConsultaReintegro') . '",
                        tarea:"' . $this->prm->tarea . '",
                        
                    });
                return false;
            });
            
            $("#importe,#importeOAnt,#SaldoAntAmort").blur(function(){
                calculaImportes();
            });
            
            $("#importe,#importeOAnt,#SaldoAntAmort").blur();
            
            function calculaImportes(){
                
                var importeOAnt = unformatNumber($("#importeOAnt").val()).toFixed(2);
                var importe = unformatNumber($("#importe").val()).toFixed(2);
                
                importeOAnt = parseFloat(importeOAnt);
                importe = parseFloat(importe);
                
                if (importe > importeOAnt) {
                    notify("El importe capturado, supera el importe por amortizar de la obra","error");
                    $("#SaldoAntAmort").val("0.00 ");
                    $("#importe").val("0.00 ")
                    return false;
                }
                
                SaldoAntAmort = importeOAnt-importe;
                SaldoAntAmort = formatNumber(SaldoAntAmort);
                $("#SaldoAntAmort").val("$ "+ SaldoAntAmort);
                return true;
            }

            $(".quitarArchivo").click(function(){
                if($(this).attr("id")=="quitaprimero"){
                    $("#resultado1").html("");
                    $("#nombreArchObj").attr("equis","");
                }else{
                    $("#resultado2").html("");
                    $("#nombreArchObj2").attr("equis","");
                }
            });

            $("#btnGuardaReintegro").click(function(){
                var n = $("#FechaReintgro").val().split("/");
                ok = false;
                if (n[2] < ' . APP_EJERCICIO . '){
                    ok = true;
                } else if (n[2] = ' . APP_EJERCICIO . ') {
                    if (n[1] < ' . intval(date('m')) . ') {
                        ok = true;
                    } else if (n[1] == ' . intval(date('m')) . ') {
                        if(n[0] <= ' . intval(date('d')) . ') {
                            ok = true;
                        } else {
                            notify("El dia no puede ser mayor al dia de hoy","error");
                            return false;
                        }
                    } else {
                        notify("El mes no puede ser mayor al actual","error");
                        return false;
                    }
                } else if (n[2] > ' . APP_EJERCICIO . ') {
                    notify("El año es incorrecto, no puede ser mayor al actual","error");
                    return false;
                }
                
                if (!calculaImportes()) {
                    notify("Error en los Importes","error");
                    return false;
                } 
                var NomObra = $("#NomObra").val();
                if (NomObra === "") {
                    notify("No hay obra seleccionada", "error");
                    $("#NomObra").addClass("input-error").trigger("blur");
                    ok = false;
                }

                // Quitar el ofuscado cuando el usuario escribe
                $("#NomObra").on("input", function () {
                    if ($(this).val() !== "") {
                        $(this).removeClass("input-error");
                    }
                });
                
                if (ok) {
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        "metodo":"abcReintegro",
                        ' . (isset($this->prm->rao_id) ? 'rao_id:' . $this->prm->rao_id . ', ' : '') . '
                        oficio:$("#oficioReintegro").val(),
                        tdf_id:$("#comboDoc").find("option:selected").val(),
                        documentoPago:$("#RefDocPago").val(),
                        fechaReintegro:$("#FechaReintgro").val(),
                        importe:unformatNumber($("#importe").val()),
                        abc:"' . $abc . '",
                        archivoOficio:$("#nombreArchObj").attr("equis"),
                        archivoDocumentoPago:$("#nombreArchObj2").attr("equis"),
                        anioReintegro:' . APP_EJERCICIO . '
                    },true);
                }
            });
                
            $("#btnAutorizar").click(function(){
                alertify.confirm("Autorizar Reembolso", "¿Esta seguro de Autorizar el Reembolso?", function(){
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        metodo:"tareasEstados",
                        rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                        tarea:"' . $this->prm->tarea . '"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
            
            $("#btnEnviar").click(function(){
                alertify.confirm("Enviar Reembolso", "¿Esta seguro de Enviar el Reembolso para su recepción?", function(){
                    comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                        metodo:"tareasEstados",
                        rao_id:"' . (isset($this->prm->rao_id) ? $this->prm->rao_id : '') . '",
                        tarea:"' . $this->prm->tarea . '"
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        
            $("#nombreArchObj, #nombreArchObj2").fileupload({
                add: function(e, data) {
                    var tipos =["APPLICATION/PDF"];
                    if(data.originalFiles[0][\'type\'].length && tipos.indexOf(data.originalFiles[0][\'type\'].toUpperCase())== -1) {
                        notify("El tipo de archivo no es permitido, solo PDF","error");
                        return false;
                    }
                    if( data.originalFiles[0][\'size\'] > 20971520) {
                        notify("El archivo es demasiado grande para subirlo al servidor, solo PDF","error");
                        return false;
                    }
                    data.submit();
                },
                dropZone: $("#dropzone"),
                autoUpload:false,
                dataType:"json",
                done:function(e, data){
                },
                progress: function(e, data){
                    var progress = parseInt( data.loaded / data.total * 100,10);
                    if($(this).attr("id")=="nombreArchObj2"){
                        $("#progressObj2 .progress-bar").css("width", progress + "%" ).attr("aria-valuenow",progress);
                    }else{
                        $("#progressObj .progress-bar").css("width", progress + "%" ).attr("aria-valuenow",progress);
                    }
                    
                },
                success:function(data){
                    comunicaCallback(data.comunica);	
                }
            });
        ');
    }
    /***************************** FUNCIÓN QUE GUARDA, ELIMINA Y ACTUALIZA EL REINTEGRO***************************/
    private function abcReintegro()
    {
        /** función que devuelve los datos de la obra y clave de financiamiento **/
        if ($this->usrNvj > 2) {
            $this->mixScript('notify("Intento Ilegal de guardar/actualizar un Reembolso","error");
            $("div[aria-describedby=cargaDatosProgFin]").html("");
            ');
            return false;
        }
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;
        $ue_id = $this->usrNvj == 1 ? $this->usrUe : $this->admUe;
        $varRein = '';
        $sql = '';

        $UndadREs = new UnidadResponsable();
        $UndadREs->setGpo_id($gpo_id);
        $UndadREs->setUr_id($ur_id);

        $UndadREs->select();
        $this->prm->gpo_id = $gpo_id;
        $this->prm->ur_id = $ur_id;
        $this->prm->ue_id = $ue_id;
        $this->prm->nombreAdministrativo = $UndadREs->getNombreAdministrativo();
        $this->prm->cargoAdministrativo = $UndadREs->getCargoAdministrativo();
        $this->prm->nombreTitular = $UndadREs->getNombreTitular();
        $this->prm->cargoTitular = $UndadREs->getCargoTitular();
        $this->prm->fechaElaboracion = date('Y-m-d H:i:s');

        /******************* ABCdeREEMBOLSO************************************/
        $rutalog = 'log/ABCdeREEMBOLSO-' . $this->usrId . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');
        foreach ($this->prm as $field => $value) {
            if (!is_object($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            }
        }

        $tempdata .= '|IPADDRESS=' . $this->usrIp;
        $tempdata .= '|USRID=' . $this->usrId;
        $tempdata .= '|CONSULTA=' . $sql . '';

        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);

        $peso = ($peso / 1024) / 1024;

        if ($peso > 8) {
            $rutamodificada = 'log/CambiodeUsoClc-' . $this->usrId . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }

        /******************* fin del log************************************/

        switch ($this->prm->abc) {
            case 'a': //para guardar un nuevo registro
                $sql = '
                    SELECT 
                        gpo_id,ur_id,oa_id,oa_nombre,oa_numObra,
                        oa_origen,toa_id,epg_id,coo_anticipo AS oa_anticipo
                    FROM enc_Programatico 
                        INNER JOIN cat_ObraActividad OA USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                        INNER JOIN cat_ComplementoObra USING (gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id)
                    WHERE epg_id = ?
                        AND ecf_id = ?
                ';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar([$this->admEpg, $this->cvefinEcf]);
                $datos = $this->extraer();

                if ($this->prm->archivoOficio == '' || $this->prm->archivoDocumentoPago == '') {
                    $this->mixScript('notify("Es obligatorio adjuntar los 2 archivos requeridos ","error");');
                    return false;
                }
                if ($this->prm->importe > $datos['oa_anticipo']) {
                    $this->mixScript('notify("El importe debe ser menor o igual al anticipo $' .
                        $datos['oa_anticipo'] . '","error");');
                    return false;
                }
                $this->desconectar();


                $this->prm->epg_id = $this->admEpg;
                $this->prm->ecf_id = $this->cvefinEcf;
                $this->prm->era_id = 1;
                $varRein = 'guardó';

                $rao_id = ReintegroAnticipoObra::abc($this->prm);

                $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
                $usr_id = $this->usrId;
                $motivoRechazo = 'ELABORADO POR LA UR DESDE PANTALLA S.2.19';
                $era_id = '1';
                $motivoRechazo = 'GENERADO POR LA UR DESDE PANTALLA S.2.19';
                $colorNotify = 'success';

                $this->conectar();
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_ReintegroAnticipo (gpo_id,ur_id,rao_id,era_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES(?, ?, ?, ?, ?, INET_ATON(?), ?, NOW())';
                $params = [$gpo_id, $ur_id, $rao_id, $era_id, $motivoRechazo, $ip, $usr_id];
                $this->setSql($sql);
                $this->consultar($params);


                break;
            case 'c': //actualiza un registro
                if (
                    $this->prm->archivoOficio != '' && $this->prm->archivoDocumentoPago == ''
                    || $this->prm->archivoOficio == '' && $this->prm->archivoDocumentoPago != ''
                ) {
                    $this->mixScript('notify("Debe cargar los 2 archivos requeridos o puede omitir ambos","error");');
                    return false;
                }

                $varRein = 'actualizó';
                $colorNotify = 'success';

                $sql = '
                    SELECT 
                        gpo_id,ur_id,oa_id,oa_nombre,
                        oa_numObra,oa_origen,toa_id,
                        epg_id,coo_anticipo AS oa_anticipo
                    FROM enc_Programatico 
                        INNER JOIN cat_ObraActividad OA USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                        INNER JOIN cat_ComplementoObra USING (gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id)
                    WHERE epg_id = ?
                        AND ecf_id = ?';
                $this->conectar();
                $this->setSql($sql);
                $this->consultar([$this->admEpg, $this->cvefinEcf]);
                $datos = $this->extraer();

                if ($this->prm->archivoOficio == '' && $this->prm->archivoDocumentoPago == '') {
                    $sql = '
                        SELECT 
                            rao_archivoOficio,rao_archivoDocumentoPago 
                        FROM scp_ReintegroAnticipoObra
                        WHERE gpo_id  = ?
                            AND ur_id = ?
                            AND rao_id = ?';
                    $this->setSql($sql);
                    $this->consultar([$gpo_id, $ur_id, $this->prm->rao_id]);
                    $f = $this->extraer();
                    if ($f['rao_archivoOficio'] != '') {
                        $arc = './docReintegro/' . $gpo_id . $ur_id . '/' . $f['rao_archivoOficio'];
                        if (!unlink($arc)) {
                            $this->mixScript(' notify("Error al borrar el archivo ' . $arc . '","error",false);');
                            return false;
                        }
                    }

                    if ($f['rao_archivoDocumentoPago'] != '') {
                        $arc = './docReintegro/' . $gpo_id . $ur_id . '/' . $f['rao_archivoDocumentoPago'];
                        if (!unlink($arc)) {
                            $this->mixScript(' notify("Error al borrar el archivo' . $arc . '","error",false);');
                            return false;
                        }
                    }
                    $sql = '
                        UPDATE scp_ReintegroAnticipoObra 
                        SET rao_archivoOficio = NULL, rao_archivoDocumentoPago = NULL
                        WHERE rao_id = ?
                    ';

                    $this->setSql($sql);
                    $this->consultar([$this->prm->rao_id]);
                }

                $sql = '
                    UPDATE scp_ReintegroAnticipoObra 
                    SET era_id = 1
                    WHERE rao_id = ?';

                $this->setSql($sql);
                $this->consultar([$this->prm->rao_id]);
                $this->desconectar();

                if ($this->prm->importe > $datos['oa_anticipo']) {
                    $this->mixScript('
                        notify("El importe debe ser menor o igual al anticipo $' . $datos['oa_anticipo'] . '","error");
                    ');
                    return false;
                }

                ReintegroAnticipoObra::abc($this->prm);

                break;
            case 'b':
                $reintegro = new ReintegroAnticipoObra();
                $reintegro->setGpo_id($this->admGpo);
                $reintegro->setUr_id($this->admUr);
                $reintegro->setRao_id($this->prm->rao_id);
                $reintegro->select();

                if ($reintegro->getEra_id() > 1 && $reintegro->getEra_id() < 7) {
                    $this->mixScript('
                        notify ("El estado del reintegro no permite la eliminación","error");
                    ');
                    return false;
                }
                $file1 = './docReintegro/' . $this->admGpo . $this->admUr . '/' . $reintegro->getArchivoOficio();
                $file2 = './docReintegro/' . $this->admGpo . $this->admUr . '/' . $reintegro->getArchivoDocumentoPago();
                if (file_exists($file1)) {
                    unlink($file1);
                }
                if (file_exists($file2)) {
                    unlink($file2);
                }
                $reintegro->delete();
                unset($reintegro);
                $varRein = 'eliminó';
                $colorNotify = 'error';

                $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
                $usr_id = $this->usrId;
                $era_id = '1';
                $motivoRechazo = 'ELIMINADO POR LA UR';

                $this->conectar();
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_ReintegroAnticipo (gpo_id,ur_id,rao_id,era_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES(?, ?, ?, ?, ?, INET_ATON(?), ?,NOW())';
                $param = [$gpo_id, $ur_id, $this->prm->rao_id, $era_id, $motivoRechazo, $ip, $usr_id];
                $this->setSql($sql);
                $this->consultar($param);
                break;
        }

        $this->mixScript('
            notify ("El registro se ' . $varRein . ' exitosamente","' . $colorNotify . '");
            $("div[aria-describedby=cargaDatosProgFin]").html("");
                comunica("' . $this->pantalla . '",{
                    "metodo":"ponTablaListadoReintegros",
                    "tarea":"generacion"
            });
        ');
    }
    /******************************* FUNCIÓN QUE REGISTRA UN REINTEGRO**************************/
    private function datosObra()
    {
        $epg_id = $this->prm->epg_id ?? $this->admEpg;
        $ecf_id = $this->prm->ecf_id ?? $this->cvefinEcf;
        $SaldoAnticipo = 0;

        $sql = '
            SELECT gpo_id,ur_id,oa_id,oa_nombre,oa_numObra,oa_origen,toa_id,
                epg_id,coo_anticipo,Reem,Amortizacion,
                (coo_anticipo-Amortizacion) AS Dif
            FROM enc_Programatico 
                INNER JOIN cat_ObraActividad OA USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                INNER JOIN cat_ComplementoObra USING ( gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id )
                INNER JOIN poa_OrigenRecurso USING(gpo_id,ur_id,epg_id,ecf_id)
                LEFT JOIN(
                    SELECT 
                        gpo_id,ur_id,epg_id,ecf_id, 
                        SUM(rao_importe) AS Reem
                    FROM scp_ReintegroAnticipoObra
                    WHERE epg_id = :epg_id
                        AND ecf_id = :ecf_id 
                        AND era_id = 6
                    GROUP BY gpo_id,ur_id,epg_id,ecf_id
                )AP USING(gpo_id,ur_id,epg_id,ecf_id)
                LEFT JOIN(
                    SELECT 
                        gpo_id,ur_id,epg_id,ecf_id, 
                        SUM(ncc_retencion) AS Amortizacion
                    FROM scp_Contable
                        INNER JOIN scp_Afectacion USING(gpo_id, ur_id,clc_id,afe_id)
                        INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id,ecf_id,tcl_id)
                    WHERE ecl_id IN(10,11,12,13,16) 
                        AND (concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) = "21194005" 
                        OR concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) = "11342001")
                        AND tcl_id IN ("F","G","H")
                        AND epg_id = :epg_id
                        AND ecf_id = :ecf_id
                    GROUP BY gpo_id,ur_id,epg_id,ecf_id
                )AM USING(gpo_id,ur_id,epg_id,ecf_id)
            WHERE epg_id = :epg_id
                AND ecf_id=:ecf_id';

        $params = [':epg_id' => $epg_id, ':ecf_id' => $ecf_id];
        //print_r($params);die;
        $c = new AccesoDatos();
        $c->conectar();
        $c->setSql($sql);
        $c->consultar($params);
        if ($c->getNumeroRegistros() < 1) {
            $this->mixScript('notify("La obra no se refrendó con esta clave de financiamiento","error");');
            return false;
        }
        $datos = $c->extraer();
        $c->desconectar();

        /**  si la obra toa_id != 2 entonces mensaje diciendo que la obra no es de inversion        **/
        if ($datos['toa_id'] != 2) {
            $this->mixScript('notify("La Obra no es de Inversión","error");');
            return false;
        }
        /**  si la obra oa_origen != "R" entonces mensaje diciendo que la obra no es de refrendo        **/
        if ($datos['oa_origen'] != 'R') {
            $this->mixScript('notify("La Obra no es de Refrendo","error");');
            return false;
        }
        /**  si la obra oa_anticipo ==0 entonces mensaje diciendo que la obra no tiene importe por reintegrar.        **/
        if ($datos['coo_anticipo'] == 0) {
            $this->mixScript('notify("La Obra no tiene importe por reintegrar","error");');
            return false;
        }

        /********Consulta para llenar la información de los campos de la Obra******/

        $sql = '
            SELECT 
                gpo_id,ur_id,ore_oficioAut,sft_nombre,ter_nombre,cfi_anio,cfi_id
            FROM poa_OrigenRecurso 
                INNER JOIN enc_ClaveFinanciamiento USING( ecf_id )
                INNER JOIN sinpres.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                INNER JOIN sinpres.cat_TipoRecurso USING( ter_id )
                INNER JOIN sinpres.cat_Subfuente' . APP_EJERCICIO . ' USING( fte_id,sft_id)
            WHERE epg_id = ?
                AND ecf_id = ?';
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$epg_id, $ecf_id]);
        $datos1 = $this->extraer();
        $this->desconectar();

        $SaldoAnticipo = round($datos['coo_anticipo'] - ($datos['Reem'] + $datos['Amortizacion']), 2);

        $this->mixScript('
            $("#NomObra").val("' . $datos['oa_id'] . ' ' . str_replace('"', '', $datos['oa_nombre']) . '");
            $("#NumObra").val("' . $datos['oa_numObra'] . '");
            $("#oficioaut").val("' . $datos1['ore_oficioAut'] . '");
            $("#subfuente").val("' . $datos1['sft_nombre'] . '");
            $("#aportacion").val("' . $datos1['ter_nombre'] . '");
            $("#ejercicio").val("' . $datos1['cfi_anio'] . '");
            $("#referencia").val("' . $datos1['cfi_id'] . '");
            $("#importeOAnt").val("' . $SaldoAnticipo . '");
        ');
    }
    /************************************************* IMPRESIÓN DEL REINTEGRO************************************************/
    private function impresionReintegro()
    {
        $this->mixScript('
            $(".ImprimirReintegro").click(function(){
                inputUr=$("#inputUr").val();
                var form = $("<form>")
                    .attr("action", "rep/doc/scp/afectaciones/bancosUr.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "inputUr").attr("value", inputUr))
                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    }
    /************************************************* IMPRESIÓN DEL REINTEGRO************************************************/
    private function tareasEstados()
    {
        /******************* ABCdeREEMBOLSO************************************/
        $rutalog = './log/logTAREASREEMBOLSO-' . $this->usrId . '.txt';
        $file = fopen($rutalog, 'a');
        $tempdata = '';
        $hoy = date('Y-m-d H-i-s');
        foreach ($this->prm as $field => $value) {
            if (!is_object($value) && !is_array($value)) {
                $tempdata .= '|' . $field . '=' . $value;
            }
        }

        $tempdata .= '|IPADDRESS=' . $this->usrIp;
        $tempdata .= '|USRID=' . $this->usrId;


        fwrite($file, 'Registro de ' . $hoy . ':' . $tempdata . PHP_EOL);
        fclose($file);

        $peso = filesize($rutalog);
        $peso = ($peso / 1024) / 1024;

        if ($peso > 8) {
            $rutamodificada = 'log/CambiodeUsoClc-' . $this->usrId . $hoy . '.txt';
            rename($rutalog, $rutamodificada);
            $file = fopen($rutalog, 'a');
            fwrite($file, 'Creado:' . $hoy . PHP_EOL);
            fclose($file);
        }

        /******************* fin del log************************************/
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;

        $varEstadoRein = '';
        $motivoRechazo = '';
        $era_id = '';

        switch ($this->prm->tarea) {
            case 'autorizacion':
                $era_id = '2';
                $estado = ' 2, rao_fechaAutorizacion = NOW() ';
                $varEstadoRein = ' autorizado ';
                $motivoRechazo = 'AUTORIZADO POR LA UR DESDE PANTALLA S.2.19';
                break;
            case 'enviar':
                $era_id = '3';
                $estado = ' 3, rao_fechaEnvio = NOW() ';
                $varEstadoRein = ' enviado ';
                $motivoRechazo = 'ENVIADO POR LA UR DESDE PANTALLA S.2.19';
                break;
            case 'recepcion':
                if ($this->usrNvj > 2 && $_SESSION[_S]['prm']['S']['2']['19']['68']) {
                    $era_id = '4';
                    $estado = ' 4 ';
                    $varEstadoRein = ' recibido ';
                    $motivoRechazo = 'RECIBIDO POR SEFIN DESDE PANTALLA S.2.19';
                } else {
                    return false;
                }
                break;
            case 'revision':
                if ($this->usrNvj > 2 && $_SESSION[_S]['prm']['S']['2']['19']['69']) {
                    $era_id = '5';
                    $estado = ' 5 ';
                    $varEstadoRein = ' revisado ';
                    $motivoRechazo = 'REVISADO POR SEFIN POR SEFIN DESDE PANTALLA S.2.19';
                } else {
                    return false;
                }
                break;
        }

        $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
        $usr_id = $this->usrId;
        $this->conectar();

        if (isset($this->prm->datos) && $this->prm->datos != null) {
            foreach ($this->prm->datos as $valor) {
                $sql = '
                    UPDATE scp_ReintegroAnticipoObra 
                    SET era_id = ? 
                    WHERE gpo_id = ?
                    AND ur_id = ?
                    AND rao_id = ?';
                $this->setSql($sql);
                $this->consultar([$era_id, $valor->gpo_id, $valor->ur_id, $valor->rao_id]);
                $sql = '
                    INSERT INTO ' . BD_BITACORA . '.bit_ReintegroAnticipo (gpo_id,ur_id,rao_id,era_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES(?, ?, ?, ?, ?, INET_ATON(?), ? ,NOW())
                ';
                $paramI = [$valor->gpo_id, $valor->ur_id, $valor->rao_id, $era_id, $motivoRechazo, $ip, $usr_id];
                $this->setSql($sql);
                $this->consultar($paramI);
            }
        } else {
            if ($this->usrNvj > 2) {
                $gpo_id = $this->prm->gpo_id;
                $ur_id = $this->prm->ur_id;
            }
            $sql = '
                UPDATE scp_ReintegroAnticipoObra 
                SET era_id = ?
                WHERE gpo_id = ?
                AND ur_id = ?
                AND rao_id = ?
            ';
            $this->setSql($sql);
            $this->consultar([$era_id, $gpo_id, $ur_id, $this->prm->rao_id]);
            $sql = '
                INSERT INTO ' . BD_BITACORA . '.bit_ReintegroAnticipo (gpo_id,ur_id,rao_id,era_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                VALUES(?, ?, ?, ?, ?, INET_ATON(?), ?,NOW())
            ';
            $paramI = [$gpo_id, $ur_id, $this->prm->rao_id, $era_id, $motivoRechazo, $ip, $usr_id];
            $this->setSql($sql);
            $this->consultar($paramI);
        }
        $this->mixScript('
            notify ("El Reembolso ha sido ' . $varEstadoRein . ' exitosamente");
            comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                "metodo":"ponTablaListadoReintegros",
                "tarea":"' . $this->prm->tarea . '"
            });
        ');
    }
    /************************************************* RECHAZAR LOS REINTEGROS************************************************/
    private function AplicacionReintegro()
    {
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;

        if ($this->usrNvj > 2) {
            $gpo_id = $this->prm->gpo_id;
            $ur_id = $this->prm->ur_id;
        }

        $this->conectar();
        $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
        $usr_id = $this->usrId;

        $sql = '
            SET 
            @usr_id = ?,
            @usr_ip = INET_ATON(?),
            @bit_observacion = "SE APLICA REINTEGRO";
        ';
        $this->setSql($sql);
        $this->consultar([$usr_id, $ip]);
        $sql = ' 
            CALL sp_AplicaReintegro' . APP_EJERCICIO . '(?, ?, ?, INET_ATON(?), ?);
        ';
        $this->setSql($sql);
        $this->consultar([$gpo_id, $ur_id, $this->prm->rao_id, $ip, $usr_id]);

        $this->setSql('SELECT @banderaPresupuesto AS ban, @mensajePresupuesto AS mensaje;');
        $this->consultar();
        $res = $this->extraer();

        $this->desconectar();

        if ($res['ban']) {
            $this->mixScript('
            notify ("El Reembolso ha sido Aplicado exitosamente","success");
            comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                "metodo":"ponTablaListadoReintegros",
                "tarea":"' . $this->prm->tarea . '"
            });
        ');
        } else {
            $this->mixScript('
            notify ("El Reembolso no fue aplicado","danger");
            comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                "metodo":"ponTablaListadoReintegros",
                "tarea":"' . $this->prm->tarea . '"
            });
        ');
        }
    }
    /************************************************* RECHAZAR LOS REINTEGROS************************************************/
    private function RechazaReintegro()
    {
        $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
        $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;

        if ($this->usrNvj > 2) {
            $gpo_id = $this->prm->gpo_id;
            $ur_id = $this->prm->ur_id;
        }

        $varEstadoRein = 'Cancelado';
        $era_id = 7;
        $this->conectar();

        switch ($this->prm->tarea) {
            case 'enviar':
                $RechazoMot = 'CANCELADO EN EL ENVÍO POR.-';

                if ($this->usrNvj <= 2 && $_SESSION[_S]['prm']['S']['2']['19']['67']) {
                } else {
                    return false;
                }
                break;
            case 'recepcion':
                $RechazoMot = 'CANCELADO EN LA RECEPCIÓN POR.-';
                if ($this->usrNvj > 2 && $_SESSION[_S]['prm']['S']['2']['19']['68']) {
                } else {
                    return false;
                }
                break;
            case 'revision':
                $RechazoMot = 'CANCELADO EN LA REVISIÓN POR.-';

                if ($this->usrNvj > 2 && $_SESSION[_S]['prm']['S']['2']['19']['69']) {
                } else {
                    return false;
                }
                break;
            case 'aplicacion':
                $RechazoMot = 'CANCELADO EN LA APLICACION POR.-';

                if ($this->usrNvj > 2 && $_SESSION[_S]['prm']['S']['2']['19']['70']) {
                } else {
                    return false;
                }
                break;
        }

        $sql = '
            UPDATE scp_ReintegroAnticipoObra
            SET era_id = 7
            WHERE gpo_id = ?
                AND ur_id = ?
                AND rao_id = ?
                AND era_id < 6
        ';
        $this->setSql($sql);
        $this->consultar([$gpo_id, $ur_id, $this->prm->rao_id]);

        $ip = (isset($this->usrIp) && $this->usrIp != '::1') ? $this->usrIp : '127.0.0.1';
        $usr_id = $this->usrId;

        $sql = '
            INSERT INTO ' . BD_BITACORA . '.bit_ReintegroAnticipo (gpo_id,ur_id,rao_id,era_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            VALUES(?, ?, ?, ?, "' . $RechazoMot . ' - ' . strtoupper($this->prm->motivoRechazo) . '", INET_ATON(?), ?,NOW())
        ';
        $params = [$gpo_id, $ur_id, $this->prm->rao_id, $era_id, $ip, $usr_id];
        $this->setSql($sql);
        $this->consultar($params);

        $this->mixScript('
            notify ("El Reembolso ha sido ' . $varEstadoRein . '","error");
            comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                "metodo":"ponTablaListadoReintegros",
                "tarea":"' . $this->prm->tarea . '"
            });
        ');
    }
    /*********************************** CONSULTAS DE LOS REINTEGROS*********************************/
    private function ConsultaReintegro()
    {
        $inputNumRao = new stdClass();
        $inputNumRao->id = 'inputNumRao';
        $inputNumRao->label = 'Número de Reembolso';
        $inputNumRao->placeholder = '000000';
        $inputNumRao->enDiv = true;
        $inputNumRao->class = 'limpiar';
        $inputNumRao->required = true;
        $inputNumRao->maxLength = 6;
        $inputNumRao->formato = 'entero';

        $inputUr = new stdClass();
        $inputUr->id = 'inputUr';
        $inputUr->label = 'UR';
        $inputUr->placeholder = '000';
        $inputUr->enDiv = true;
        $inputUr->value = (isset($this->prm->ur_clave)) ? $this->prm->ur_clave : '';
        $inputUr->class = 'limpiar';
        $inputUr->required = true;
        $inputUr->maxLength = 3;
        $inputUr->formato = 'entero';

        $btnBuscarCta = new stdClass();
        $btnBuscarCta->id = 'btnBuscarReembolso';
        $btnBuscarCta->text = 'Buscar';
        $btnBuscarCta->value = ' Buscar ';
        $btnBuscarCta->color = 'azul';
        $btnBuscarCta->icon = 'solid fa-magnifying-glass';

        $clean = new stdClass();
        $clean->id = 'clean';
        $clean->text = 'Limpiar';
        $clean->value = ' Limpiar';
        $clean->color = 'verde';
        $clean->icon = 'solid fa-arrows-rotate';

        $filtro = '
            <div class="row">
                <fieldset class="col-md-4 col-xs-2">
                    ' . $this->ponInput($inputUr) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    ' . $this->ponInput($inputNumRao) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                ' . $this->ponBoton($btnBuscarCta) . '  ' . $this->ponBoton($clean) . '
                </fieldset>
            </div>
        ';
        $script = '
            $("#inputUr, #inputNumRao").keypress(function(event){
                var anularTecla = false;
                var esPermitido = [0, 8].indexOf(event.which) >= 0;
                anularTecla = ((event.which < 48 || event.which > 57) && !esPermitido) ;
                if( anularTecla ) event.preventDefault();
            });
        ';

        $tabla = new stdClass();
        $tabla->id = 'tbl_Reintegros';
        $tabla->titulo = 'Consulta de Reembolsos';
        $tabla->comunica = new stdClass();
        $tabla->comunica->rao_id = '{$(\'#inputNumRao\').val()}';
        $tabla->comunica->gpour = '{$(\'#inputUr\').val()}';
        $tabla->comunica->pantalla = 'scp/afectaciones/ControladorReintegroAnticipoObra.php';
        if ($this->usrNvj > 3) {
            $tabla->tools = $filtro;
        }
        $tabla->comunica->metodo = 'llenaTablaConsultaReintegro';
        $tabla->comunica->tarea = $this->prm->tarea;

        $tr = [];
        /******+************************* SE INICIA LA TABLA ***************************************/
        //Ver/Editar
        $th = new stdClass();
        $th->width = '4%';
        $th->align = 'center';
        $th->texto = 'Ver';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '4%';
        $th->align = 'center';
        $th->texto = 'Imprimir';
        array_push($tr, $th);
        if ($this->usrNvj > 2) {
            $th = new stdClass();
            $th->width = '4%';
            $th->align = 'center';
            $th->texto = 'UR';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->width = '3%';
        $th->align = 'center';
        $th->texto = 'N° Reemb.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '40%';
        $th->align = 'center';
        $th->texto = 'Obra';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '7%';
        $th->align = 'center';
        $th->texto = 'Oficio Autorización';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '7%';
        $th->align = 'center';
        $th->texto = 'Clave Fin';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '7%';
        $th->align = 'center';
        $th->texto = 'Estado';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '8%';
        $th->align = 'center';
        $th->texto = 'Imp. de Ant/Obra';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '8%';
        $th->align = 'center';
        $th->texto = 'Imp. de Reembolso';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '8%';
        $th->align = 'center';
        $th->texto = 'Sal.Pend.x Amortizar';
        array_push($tr, $th);

        $trbody = [];
        /************************ SE INICIA EL CUERPO DE LA TABLA ***************************************/

        /*ver editar*/
        $td = new stdClass();
        $td->attr = new stdClass();

        if ($this->usrNvj > 2) {
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
        }

        $td->tipo = 'boton';
        $td->attr->rao_id = 'rao_id';
        $td->align = 'center';
        $td->class = 'consultaRentgro';
        $td->icono = 'solid fa-eye';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->attr = new stdClass();

        if ($this->usrNvj > 2) {
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
        }

        $td->tipo = 'boton';
        $td->attr->gpour = 'gpour';
        $td->attr->rao_id = 'rao_id';
        $td->align = 'center';
        $td->class = 'imprimeReintegro';
        $td->icono = 'solid fa-print';
        array_push($trbody, $td);

        if ($this->usrNvj > 2) {
            /*N° de Reintegro*/
            $td = new stdClass();
            $td->align = 'center';
            $td->campoTxt = 'gpour';
            array_push($trbody, $td);
        }

        /*N° de Reintegro*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_id';
        array_push($trbody, $td);
        /*Nombre*/
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'obra';
        array_push($trbody, $td);
        /*FechaAnt*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_oficio';
        array_push($trbody, $td);
        /*CveFin*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'ecf_clave';
        array_push($trbody, $td);
        /*Estado*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'era_nombre';
        array_push($trbody, $td);
        /*Sal.Anticipo*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'SaldoObra';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        /*Import.Reem*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'rao_importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);
        /*Sal.pend.xAmor*/
        $td = new stdClass();
        $td->align = 'center';
        $td->campoTxt = 'Disponible';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tabla) . '</div>');

        $botones = '
            <button id="btnRegresarIndex" type="button" class="btn btn-test btn-outline-secondary me-1" title="Regresar al Menú Principal">
                <i class="fa fa-circle-left me-2"></i>Regresar
            </button>
        ';

        $this->agregaTools($botones);

        $this->llenaTablaConsultaReintegro();
        $this->mixScript($script . '
            // Para que cuando entre y salga de la pantalla donde pide los encuadres CA y CF no salga vacía la segunda vez
            $(".breadcrumbs").click( function(e) {
                $("div[aria-describedby=cargaDatosProgFin]").html("");
            });
            
            $("#btnBuscarReembolso").click (function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaConsultaReintegro",
                    gpour:$("#inputUr").val(),
                    rao_id:$("#inputNumRao").val(),
                    tarea:"consulta"
                });
                return false;
            });
            
            $("#clean").click(function(){
                $(".limpiar").val("");
                    comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaConsultaReintegro"
                    
                });
                return false;
            });
            
            $("#btnRegresarIndex").click (function(){
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    metodo:"main",
                });
                return false;
            });
        ');
    }
    /************************************* CONSULTAS DE LOS REINTEGROS*****************************/
    private function upload()
    {
        $ruta = './docReintegro/' . $this->admGpo . $this->admUr;
        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }
        $novalidos = [' ', "'", '!', '#', '$', '%', '&', '(', ')', '=', '¡', '¿', '+', '}', '{', '[', ']', '°', '¬', '~', '`', '^'];

        $ruta .= '/';
        $extp = ['PDF']; /// EXTENSIONES PERMITIDAS PARA SUBIR AL SERVIDOR (ARREGLO)

        foreach ($_FILES as $file) {

            $ext = strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $extp)) {
                $this->mixScript('notify("EL tipo de fichero no es permitido, solo ficheros PDF","error");');
                return false;
            }
            $nombre = strtoupper(str_replace($novalidos, '_', $file['name'], $count)); // QUITAMOS ESPACIOS EN BLANCO DEL NOMBRE DEL ARCHIVO
            if ($count == (strlen($nombre) - 4)) {
                $this->mixScript('notify("El nombre  ' . $file['name'] . '   es invalido","error");');
                return false;
            }
            if ($count > 0) {
                $this->mixScript('notify("Sí existen los siguientes caracteres !#$%&()= \'¡¿+}{[]°¬~`^ son reemplazados por _, incluyendo espacios en blanco.","warning",false);');
            }

            $archivo = $ruta . $nombre; // GENERAMOS LA RUTA DE GUARDADO
            if (file_exists($archivo)) {
                $this->mixScript('notify("El archivo ya existe, cambie de nombre e intentelo de nuevo","warning");');
                return false;
            }
            move_uploaded_file($file['tmp_name'], $archivo);
            $donde = $resultado = '';
            if ($this->prm->origen == 'objetivosFile') {
                $donde = 'nombreArchObj';
                $resultado = 'resultado1';
            } else {
                $donde = 'nombreArchObj2';
                $resultado = 'resultado2';
            }
            $this->mixScript('	
                $("#' . $donde . '").attr("equis","' . $nombre . '");
                $("#' . $resultado . '").html("' . $nombre . ' cargado exitosamente");
            ');
        }
    }
    /********************************* CONSULTAS DE LOS REINTEGROS**********************************/
    private function llenaTablaConsultaReintegro()
    {
        $gpo_id = $ur_id = '';
        $params = [];

        if ($this->usrNvj < 3) {
            $gpo_id = $this->usrNvj == 1 ? $this->usrGpo : $this->admGpo;
            $ur_id = $this->usrNvj == 1 ? $this->usrUr : $this->admUr;
        }

        if (isset($this->prm->gpour) && strlen($this->prm->gpour) == 3 && $this->usrNvj > 2) {
            $gpo_id = substr($this->prm->gpour, 0, 1);
            $ur_id = substr($this->prm->gpour, 1, 2);
        }
        $wh = '';

        if ($this->usrNvj == 1 || $this->usrNvj == 2 || (isset($this->prm->gpour)
            && strlen($this->prm->gpour) == 3 && $this->usrNvj > 2)) {
            $wh .= 'WHERE gpo_id = :gpo_id AND ur_id = :ur_id';
            $params[':gpo_id'] = $gpo_id;
            $params[':ur_id'] = $ur_id;
        }
        $sql = '
            SELECT gpo_id,ur_id,CONCAT(gpo_id,ur_id) AS gpour,rao_id,CONCAT(oa_id, " - " ,oa_nombre) AS obra,ecf_clave,
            rao_oficio,era_nombre,(coo_anticipo - IF(Amortizacion IS NULL,0,Amortizacion)) AS SaldoObra , rao_importe,
                coo_anticipo - (IF(Amortizacion IS NULL,0,Amortizacion) + IF(Reem IS NULL,0,Reem)) AS Disponible
            FROM scp_ReintegroAnticipoObra  
            INNER JOIN enc_Programatico USING(gpo_id,ur_id,epg_id)
            INNER JOIN cat_ObraActividad USING( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
            INNER JOIN sinpres.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING ( cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio )
            INNER JOIN cat_ComplementoObra USING ( gpo_id,ur_id,epg_id,prg_id,spr_id,py_id,oa_id,ecf_id )
            INNER JOIN cat_EstadoReintegro USING ( era_id )
            LEFT JOIN(
                    SELECT gpo_id,ur_id,epg_id,ecf_id, SUM(rao_importe) AS Reem
                    FROM scp_ReintegroAnticipoObra
                    WHERE era_id = 6
                    GROUP BY gpo_id,ur_id,epg_id,ecf_id
                )AP USING(gpo_id,ur_id,epg_id,ecf_id)
            LEFT JOIN(
                SELECT gpo_id,ur_id,epg_id,ecf_id, SUM(ncc_retencion) AS Amortizacion
                FROM scp_Contable
                    INNER JOIN scp_Afectacion USING(gpo_id, ur_id,clc_id,afe_id) 
                    INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id,ecf_id,tcl_id)
                WHERE  ecl_id IN (10,11,12,13,16) 
                    AND (concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) = "21194005" 
                        OR concat(ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) = "11342001")
                    AND tcl_id IN ("F","G","H")
                GROUP BY gpo_id,ur_id,epg_id,ecf_id
                )AM USING(gpo_id,ur_id,epg_id,ecf_id)
        ';

        if (isset($this->prm->rao_id) && is_numeric($this->prm->rao_id)) {
            if ($wh == '') {
                $wh .= ' WHERE rao_id = :rao_id';
                $params[':rao_id'] = $this->prm->rao_id;
            } else {
                $wh .= ' AND rao_id = :rao_id';
                $params[':rao_id'] = $this->prm->rao_id;
            }
        }

        switch (isset($this->prm->tarea) && $this->prm->tarea != '') {
            case 'consulta':
                if ($wh == '') {
                    $wh .= ' WHERE era_id >= 6 ';
                } else {
                    $wh .= ' AND era_id >= 6 ';
                }
                break;
        }
        $this->setParams($params);
        $this->setSql($sql . $wh);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_Reintegros';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".consultaRentgro").click(function() {
                comunica("scp/afectaciones/ControladorReintegroAnticipoObra.php",{
                    "metodo":"addReintegro",
                    "rao_id":$(this).attr("rao_id"),
                    ' . ($this->usrNvj > 2 ? 'gpo_id:$(this).attr("gpo_id"),ur_id:$(this).attr("ur_id"),' : '') . '
                    abc:"c",
                    tarea:"consulta"
                });
                return false;
            });
            $(".imprimeReintegro").click(function(){
                inputUr=$("#inputUr").val();
                var form = $("<form>")
                .attr("action", "rep/doc/scp/afectaciones/ReintegroAnticipoObra.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "gpour").attr("value",$(this).attr("gpour") ))
                .append($("<input>").attr("type", "hidden").attr("name", "rao_id").attr("value",$(this).attr("rao_id") ))
                .append($("<input>").attr("type", "hidden").attr("name", "origen").attr("value","consulta" ))
                $("body").append(form);
                form.submit();
                form.remove();
            });
            
        ');
    }
}
if (isset($_SESSION[_S]['prm']['S']['2']['19'])) {
    // Permisos del usuario que puede ver este controlador.
    new ControladorReintegroAnticipoObra();
} else {
    WebResponse::sinPermisos();
}
