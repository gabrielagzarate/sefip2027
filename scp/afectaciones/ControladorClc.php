<?php

session_start();
define('_img_', 'ok');

if ($_SERVER['SERVER_ADDR'] == '172.17.90.17') {
    error_reporting(E_ERROR);
} else {
    error_reporting(E_ALL);
}

require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scp/vistaClc.php';
require_once dirname(__FILE__) . '/../../lib/scp/modelClc.php';
require_once dirname(__FILE__) . '/../../ent/scp/Clc.php';
require_once dirname(__FILE__) . '/../../ent/scp/ObraClc.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ObjetoGasto.php';
require_once dirname(__FILE__) . '/ValidaGarantias.php';

class ControladorClc extends WebResponse
{
    public $gpo_id;
    public $ur_id;
    public UnidadResponsable $ur;
    public vistaClc $vista;
    public modelClc $model;
    public $grupo2;
    public $usr_id;
    public $usr_ip;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        $this->pantalla = 'scp/afectaciones/ControladorClc.php';
        $this->gpo_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['gpo_id'] : $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] < 3 ? $_SESSION[_S]['ur_id'] : $_SESSION[_S]['clasAdm-ur'];
        $this->ur = new UnidadResponsable();
        $this->ur->setGpo_id($this->gpo_id);
        $this->ur->setUr_id($this->ur_id);
        $this->ur->select();
        $this->vista = new vistaClc();
        $this->model = new modelClc();
        $this->grupo2 = [105, 530, 531, 540, 553];
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                switch ($this->metodo) {
                    case 'cargaDatosObra':
                        $this->cargaDatosObra();
                        break;
                    case 'addModClc':
                        $this->addModClc();
                        break;
                    case 'abcClc':
                        $this->abcClc();
                        break;
                    case 'comboNomina':
                        $this->comboNomina();
                        break;
                    case 'llenaTablaClc':
                        $this->llenaTablaClc();
                        break;
                    case 'TipoFiltroCuenta':
                        $this->TipoFiltroCuenta();
                        break;
                    case 'TablaAfectaciones':
                        $this->TablaAfectaciones();
                        break;
                    case 'llenaTablaAfectaciones':
                        $this->llenaTablaAfectaciones();
                        break;
                    case 'TablaClcAfectacion':
                        $this->TablaClcAfectacion();
                        break;
                    case 'llenaTablaClcAfectacion':
                        $this->llenaTablaClcAfectacion();
                        break;
                    case 'GuardarAfectacion':
                        $this->GuardarAfectacion();
                        break;
                    case 'SepararAfectacion':
                        $this->SepararAfectacion();
                        break;
                    case 'PantallaInicial':
                        $this->PantallaInicial();
                        break;
                    case 'AutorizarClc':
                        $this->AutorizarClc();
                        break;
                    case 'EnviarClc':
                        $this->EnviarClc();
                        break;
                    case 'modalidadPago':
                        $this->modalidadPago();
                        break;
                    case 'tipoCuenta':
                        $this->tipoCuenta();
                        break;
                    case 'ClcDesfadas':
                        $this->ClcDesfadas();
                        break;
                    case 'ValidarClcAjuste':
                        $this->ValidarClcAjuste();
                        break;
                    case 'VerificaContratos':
                        $this->VerificaContratos();
                        break;
                    case 'ValidarRectificacion':
                        $this->ValidarRectificacion();
                        break;
                    case 'listadoClc':
                        $this->listadoClc();
                        break;
                    case 'llenaTablaListadoClc':
                        $this->llenaTablaListadoClc();
                        break;
                    case 'RecepcionClc':
                        $this->RecepcionClc();
                        break;
                    case 'AplicarClcSefin':
                        $this->AplicarClcSefin();
                        break;
                    case 'ListaRecepcion':
                        $this->ListaRecepcion();
                        break;
                    case 'llenaListaRecepcion':
                        $this->llenaListaRecepcion();
                        break;
                    case 'GeneraLista':
                        $this->GeneraLista();
                        break;
                    case 'llenaGeneraLista':
                        $this->llenaGeneraLista();
                        break;
                    case 'DatosClc':
                        $this->DatosClc();
                        break;
                    case 'validaCLCPagoDirecto':
                        $this->validaCLCPagoDirecto();
                        break;
                    default:
                        if (count($_SESSION[_S]['prm']['S']['2']['2']) == 1 && $_SESSION[_S]['nvj_id'] > 2) {
                            $_SESSION[_S]['permisos'] = 1;
                            $tarea = key($_SESSION[_S]['prm']['S']['2']['2']);
                            switch ($tarea) {
                                case '24':
                                    $this->prm->tareaclc = 'recepcion';
                                    if ($_SESSION[_S]['uni_id'] == 17) {
                                        $this->GeneraLista();
                                    } else {
                                        $this->listadoClc();
                                    }

                                    break;
                                case '25':
                                    $this->prm->tareaclc = 'revision';
                                    $this->listadoClc();
                                    break;
                                case '26':
                                    $this->prm->tareaclc = 'aplicacion';
                                    $this->listadoClc();
                                    break;
                                case '50':
                                    $this->listadoClc();
                                    break;
                            }
                        } else {
                            unset($_SESSION[_S]['permisos']);
                            $this->main();
                        }
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $cards = [];
        $extraHtml = '';
        $unico = '';
        $permisos = count($_SESSION[_S]['prm']['S']['2']['2']);
        $metodo = $mensaje = '';

        $this->setSql('
            SELECT clc_id
            FROM scp_Clc
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id !=0
            ANd ecl_id < 3
            ANd month(clc_fechaElaboracion) < month(now())
        ');
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id];
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $totalRegistros = $this->getNumeroRegistros();

        if (isset($_SESSION[_S]['prm']['S']['2']['2']['20'])) {
            /* ESTE PROCESOS ELIMINA LAS CLC´S DE MESES PASADOS VERIFICAR SI ES QUE SE VA A USAR PARA ESTE 2015 */
            if ($totalRegistros != 0) {
                $c = new stdClass();
                $c->id = 'desfasadas';
                $c->titulo = 'Desfasadas';
                $c->img = 'img/img.php?img=clc_desfasadas';
                $c->label = 'CLC´S DESFASADAS';
                $c->title = 'CLCs desfasadas';
                $cards[] = $c;
            }

            $c = new stdClass();
            $c->id = 'generacion';
            $c->titulo = 'Generacion';
            $c->img = 'img/img.php?img=clc_generacion';
            $c->label = 'GENERACIÓN';
            $c->title = 'Generación de CLCs';
            $cards[] = $c;
        }
        /* BOTON DONDE SE AUTORIZAN LAS CLC'S SOLO UR */
        if (isset($_SESSION[_S]['prm']['S']['2']['2']['21'])) {
            $c = new stdClass();
            $c->id = 'autorizacion';
            $c->titulo = 'Autorización';
            $c->img = 'img/img.php?img=clc_autorizacion';
            $c->label = 'AUTORIZACIÓN';
            $c->title = 'Autorización de CLCs';
            $cards[] = $c;
        }
        /* BOTON DONDE SE ENVIAN LAS CLC'S SOLO UR */
        if (isset($_SESSION[_S]['prm']['S']['2']['2']['22'])) {
            $c = new stdClass();
            $c->id = 'enviar';
            $c->titulo = 'Enviar';
            $c->img = 'img/img.php?img=clc_envio-aplicacion';
            $c->label = 'ENVÍO';
            $c->title = 'Envío de CLCs';
            $cards[] = $c;

            /* Boton para la impresión de la CLC  aun cuando ya se haya enviado la CLC la ur podra realziar su impresión  */
            $c = new stdClass();
            $c->id = 'impresion';
            $c->titulo = 'Impresión';
            $c->img = 'img/img.php?img=clc_impresion';
            $c->label = 'IMPRESIÓN';
            $c->title = 'Impresión de CLCs';
            $cards[] = $c;
        }

        if (isset($_SESSION[_S]['prm']['S']['2']['2']['76'])) {
            $c = new stdClass();
            $c->id = 'ejercer';
            $c->titulo = 'Ejercer';
            $c->img = 'img/img.php?img=clc_ejercer';
            $c->label = 'EJERCER';
            $c->title = 'Ejercer CLCs';
            $cards[] = $c;

            $c = new stdClass();
            $c->id = 'federales';
            $c->titulo = 'Recurso Federal';
            $c->img = 'img/img.php?img=clc_federal';
            $c->label = 'RECURSO FEDERAL';
            $c->title = 'Recurso Federal';
            $cards[] = $c;

            $mensaje .= '<div class="" role="alert"><b>
            <ul type=disk>PROCESO DE RECURSO FEDERAL
                <li>Botón (Generación) Se genera la CLC.</li>
                <li>Botón (Autorización) Se autoriza la CLC.</li>
                <li>Botón (Ejercer) Se ejerce la CLC.</li>
                <li>Botón (Recurso Federal) Se imprime la CLC.</li>
                <li>Si su CLC no aparece en el botón Ejercer,favor de comunicarse con su analista en la Dirección de Egresos.</li>
                <li>Recuerde que una vez que se ejerce la CLC no hay rechazo.</li>
            </ul>
            </div><br>';
        }

        /* BOTON PARA LA RECEPCION DE CLC'S POR PARTE DE EGRESOS */
        if (isset($_SESSION[_S]['prm']['S']['2']['2']['24'])) {
            $metodo = '';
            if ($_SESSION[_S]['uni_id'] == 17) {
                $metodo = 'metodo:"GeneraLista",';
            } else {
                $metodo = 'metodo:"listadoClc",';
            }

            $c = new stdClass();
            $c->id = 'recepcion';
            $c->titulo = 'Recepción';
            $c->img = 'img/img.php?img=clc_recepcion';
            $c->label = 'RECEPCIÓN';
            $c->title = 'Recepción de CLCs';
            $cards[] = $c;

            $unico = ($unico == '') && $permisos == 1 ? '$("#recepcion").click();' : ';';
        }
        /* BOTON PARA LA REVISION DE CLC POR PARTE DE EGRESOS */
        if (isset($_SESSION[_S]['prm']['S']['2']['2']['25'])) {
            $metodo = '';
            if ($_SESSION[_S]['uni_id'] == 17) {
                $metodo = 'metodo:"GeneraLista",';
            } else {
                $metodo = 'metodo:"listadoClc",';
            }

            $c = new stdClass();
            $c->id = 'revision';
            $c->titulo = 'Revisión';
            $c->img = 'img/img.php?img=clc_autorizacion';
            $c->label = 'REVISIÓN';
            $c->title = 'Revisión de CLCs';
            $cards[] = $c;
        }
        /* BOTON PARA LA APLICACION  DE CLC´S POR PARTE DE EGRESOS */
        if (isset($_SESSION[_S]['prm']['S']['2']['2']['26'])) {
            $c = new stdClass();
            $c->id = 'aplicacion';
            $c->titulo = 'Aplicación';
            $c->img = 'img/img.php?img=clc_envio-aplicacion';
            $c->label = 'APLICACIÓN';
            $c->title = 'Aplicación de CLCs';
            $cards[] = $c;
        }

        if (isset($_SESSION[_S]['prm']['S']['2']['2']['50'])) {
            $c = new stdClass();
            $c->id = 'listado';
            $c->titulo = 'Listado';
            $c->img = 'img/img.php?img=clc_listado';
            $c->label = 'LISTADO';
            $c->title = 'Listado de CLCs';
            $cards[] = $c;
        }

        if ($_SESSION[_S]['nvj_id'] < 3 && $totalRegistros != 0) {
            $extraHtml .= '
                <div class="alert alert-info" role="alert">
                    <b>Al presionar el módulo de CLC´S DESFASADAS, el usuario podrá inhabilitar todas aquellas CLC´S  que fueron generadas en meses pasados, al realizar este proceso la afectaciones son separadas para que el Usuario proceda a cancelar las Afectaciones y regresar el devengado al Presupuesto</b>
                </div>
            ';
        }
        $extraHtml .= $mensaje;
        $this->mixHtml($this->ponCards($cards) . $extraHtml);

        $this->mixScript('
            $("#toolsPantalla").html("");
            $("#desfasadas").click(function() {
                alertify.confirm(
                    "Confirmación de Inhabilitación",  // Título
                    "Estás a punto de inhabilitar CLC\'s generadas en meses anteriores.<br>¿Quieres continuar?",
                    function() {
                        // Acción si el usuario acepta
                        comunica("' . $this->pantalla . '", { 
                        metodo: "ClcDesfadas" });
                    },
                    undefined
                ).set("defaultFocus","cancel")
                .set("labels", {
                    ok: "Aceptar", cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-outline-success");
                }
                    const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }

                return false; // para evitar cualquier acción predeterminada
            });

            $("#generacion").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"generacion"
                });
                return false;
            });

            $("#autorizacion").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"autorizacion"
                });
                return false;
            });

            $("#enviar").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"enviar"
                });
                return false;
            });


            $("#recepcion").click(function(){
                comunica("' . $this->pantalla . '",{
                ' . $metodo . '
                tareaclc:"recepcion"
                });
                return false;
            });

            $("#revision").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"listadoClc",
                tareaclc:"revision"
                });
                return false;
            });

            $("#aplicacion").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"listadoClc",
                tareaclc:"aplicacion"
                });
                return false;
            });

            $("#Listas").click(function(){
                comunica("' . $this->pantalla . '",{
                ' . $metodo . '
                tareaclc:"impresion"
                });
                return false;
            })

            $("#listado").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"ListaRecepcion"
                });
                return false;
            });

            $("#impresion").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"impresion"
                });
                return false;
            });

            $("#federales").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"federales"
                });
                return false;
            });

            $("#ejercer").click(function(){
                comunica("' . $this->pantalla . '",{
                metodo:"PantallaInicial",
                tareaclc:"ejercer"
                });
                return false;
            });
            ' . $unico . '

        ');
        return false;
    }

    public function PantallaInicial()
    {
        $permisos = $extra = '';
        $this->mixScript('$("' . (($_SESSION[_S]['nvj_id'] == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();');
        $this->prm->tareaclc = (isset($this->prm->tareaclc)) ? $this->prm->tareaclc : '';
        $this->prm->nombre = (isset($this->prm->nombre)) ? $this->prm->nombre : '';
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $this->mixScript('
            function validaTip(){
                var s = "";
                $(".tcl_idfont").each(function(){
                    if ($(this).hasClass("tcl_ida")) {
                        s =$(this).attr("id");
                    }
                });
                if (s =="") {
                    notify("Debe seleccionar un tipo de CLC!!","warning", true);
                    return false
                } else {
                    return s;
                }
            }
        ');

        $and = $sql = '';

        /* PARA EL 2016 LA UR 315 PARA A SER LA 116,ESTA UTILIZA LA CARGA DE TRABAJO POR USUARIOS */
        if ($this->gpo_id . $this->ur_id == 116) {
            $sql = 'LEFT join cat_CargaTrabajo ct using(gpo_id,ur_id,ue_id)';
            $and = 'and ue_id= ' . $_SESSION[_S]['clasAdm-ue'] . '';
            $ue_id = $_SESSION[_S]['nvj_id'] == 1 ? $_SESSION[_S]['ue_id'] : $_SESSION[_S]['clasAdm-ue'];
            switch ($ue_id) {
                case '001':
                case '002':
                    $and = ' AND tcl_id ="M"';
                    break;
                case '005':
                case '006':
                    $and = ' AND tcl_id ="L"';
                    break;
                case '004':
                    $and = ' AND tcl_id ="J"';
                    break;
                case '003':
                    $and = ' AND tcl_id ="K"';
                    break;
                default:
                    $and = ' AND tcl_id ="x"';
                    break;
            }
        }

        $datos = $this->model->getTiposClc();
        if ($datos['registros'] == 1) {
            $this->prm->tipo = $datos['datos'][0]['tcl_id'];
        }

        $radios = $this->vista->vwTipoClc($datos, $this->prm->tipo);

        $tabla = new stdClass();
        $tabla->id = 'tbl-clcs';
        $tabla->titulo = 'Cuenta por Liquidar Certificada';
        $tabla->tools = $radios;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->tareaclc = $this->prm->tareaclc;
        $tabla->comunica->tipo = '{validaTip()}';

        $tabla->numFilas = 50;
        $tabla->accion = new stdClass();

        if ($this->prm->tareaclc == 'generacion') {
            $tabla->accion->editar = true;
        }
        $tabla->accion->metodoEditar = 'addModClc';
        $tabla->accion->pdf = true;
        $tabla->accion->urlPdf = 'rep/doc/rep_clc.php';
        $tabla->accion->excel = true;
        $tabla->accion->urlExcel = 'rep/excel/scp/afectaciones/repClc.php';

        if ($this->prm->tareaclc == 'autorizacion') {
            $tabla->multiseleccionable = true;
            $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'clc_id', 'tcl_id', 'stc_id', 'ecf_id'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'ecl_id';
            $tabla->casoSeleccionable->valor = '1';
            $tabla->casoSeleccionable->ands = [];
            $and = new stdClass();
            $and->campo = 'importe2';
            $and->operador = '!=';
            $and->valor = '0';
            $and->valorTipo = 'numero';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casoSeleccionable->ands, $and);
        }

        if ($this->prm->tareaclc == 'enviar' || $this->prm->tareaclc == 'ejercer') {
            $tabla->multiseleccionable = true;
            $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'clc_id', 'ecf_id', 'tcl_id', 'stc_id', 'clc_recursoFederal'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'ecl_id';
            $tabla->casoSeleccionable->valor = '2';
            $tabla->casoSeleccionable->ands = [];
            $and = new stdClass();
            $and->campo = 'importe2';
            $and->operador = '!=';
            $and->valor = '0';
            $and->valorTipo = 'numero';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casoSeleccionable->ands, $and);
        }

        $tr = [];
        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);
        /* FECHA DE ELABORACION */
        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);
        /* CLAVE DE FINANCIAMIENTO */
        $th = new stdClass();
        $th->texto = 'CVEFIN';
        array_push($tr, $th);
        /* TIPO DE LA CLC */
        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);
        /* CUENTA BANCARIA */
        $th = new stdClass();
        $th->texto = 'CLABE INTERBANCARIA';
        array_push($tr, $th);
        /* PERCEPCIONES DE LA  CLC */
        $th = new stdClass();
        $th->texto = 'PERCEPCIONES';
        array_push($tr, $th);
        /* RETENCIONES DE LA CLC */
        $th = new stdClass();
        $th->texto = 'RETENCIONES';
        array_push($tr, $th);
        /* IMPORTE DE LA CLC */
        $th = new stdClass();
        $th->texto = 'IMPORTE NETO';
        array_push($tr, $th);

        $tdAjuste = ''; //PARA LAS UR'S DE AJUSTE
        $tdAjuste = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];
        if ($tdAjuste == '430' || $tdAjuste == '431' || $tdAjuste == '439' || $tdAjuste == '474') {
            $th = new stdClass();
            $th->texto = 'CLC AJUSTE';
            array_push($tr, $th);
        }

        $trbody = [];

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_fechaElaboracion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecf_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'subtipo';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_percepcion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_retencion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'importe';
        array_push($trbody, $td);
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';

        $nuevo = new stdClass();
        $nuevo->value = 'Nuevo';
        $nuevo->id = 'nuevo';
        $nuevo->name = 'nuevo';
        $nuevo->class = 'nuevo';
        $nuevo->color = 'azul';
        $nuevo->icon = 'solid fa-plus me-2';

        $autorizar = new stdClass();
        $autorizar->value = 'Autorizar';
        $autorizar->id = 'autorizar';
        $autorizar->name = 'autorizar';
        $autorizar->class = 'autorizar';
        $autorizar->color = 'verde';
        $autorizar->icon = 'regular fa-circle-check me-2';

        $enviar = new stdClass();
        if ($this->prm->tareaclc == 'ejercer') {
            $enviar->value = 'Ejercer';
        } else {
            $enviar->value = 'Enviar';
        }

        $enviar->id = 'enviar';
        $enviar->name = 'enviar';
        $enviar->class = 'enviar me-1';
        $enviar->color = 'verde';
        $enviar->icon = 'paper-plane me-2';

        $enviar->attr = new stdClass();
        $enviar->attr->gpo_id = 'gpo_id';
        $enviar->attr->ur_id = 'ur_id';
        $enviar->attr->clc_id = 'clc_id';

        $btnRechazar = new stdClass();
        $btnRechazar->id = 'btnRechazar';
        $btnRechazar->name = 'btnRechazar';
        $btnRechazar->class = 'rechazar';
        $btnRechazar->color = 'rojo';
        $btnRechazar->value = 'Rechazar';
        $btnRechazar->icon = 'times me-2 ';

        $this->mixHtml($this->wrPonTabla($tabla));

        if ($this->prm->tareaclc == 'generacion') {
            $permisos .= '' . $this->ponBoton($nuevo) . '';
        }
        if ($this->prm->tareaclc == 'autorizacion') {
            $permisos .= '' . $this->ponBoton($autorizar) . '';
        }
        if ($this->prm->tareaclc == 'enviar' || $this->prm->tareaclc == 'ejercer') {
            $permisos .= ' ' . $this->ponBoton($enviar) . '  ' . $this->ponBoton($btnRechazar) . '';
        }

        $this->mixHtml(
            '<form  id="imprep" action="rep/doc/rep_clc.php" method="post" target="_blank">
                <input id="gpo_id" name="gpo_id" type="hidden" ">
                <input id="ur_id" name="ur_id" type="hidden" ">
                <input id="clc_id" name="clc_id" type="hidden" ">
                <input id="enviado" name="enviado" type="hidden" ">
            </form>'
        );

        $this->agregaTools(' ' . $this->ponBoton($btnregresarindex) . ' ' . $permisos . ' ');
        if ($this->prm->tipo != '') {
            $this->mixScript($this->wrLlamaLlenaTabla('tbl-clcs'));
        }

        $this->mixScript('
            function show(id,id_name){
                if (!$("#"+id).hasClass("tcl_ida")) {
                    $(".tcl_idfont").removeClass("tcl_ida");
                    $(".tcl_idfont").removeClass("loadingclas");
                    $(".tcl_idfont").addClass("tcl_idn");
                    $(".tcl_idfont").removeAttr("checked");
                    $("#"+ id).removeClass("tcl_idn");
                    $("#"+id).addClass("loadingclas");
                    if (comunica("' . $this->pantalla . '",{metodo:"llenaTablaClc",tipo:id,tareaclc:"' . $this->prm->tareaclc . '",nombre:id_name},false,false)) {
                        $("#"+id).addClass("tcl_ida");
                    }
                }
            }

            $(".tcl_idfont").click(function(){
                if ($(this).hasClass("tcl_idb")){
                    notify("Este tipo de CLC no esta disponible","error");
                } else {
                    show($(this).attr("id"),$(this).attr("nombre"));
                }
            });

            $(".nuevo").click(function(){
                if (s=validaTip()) {
                    comunica("' . $this->pantalla . '",{
                    metodo:"addModClc",
                    tcl_id:s,
                    tareaclc:"' . $this->prm->tareaclc . '"
                    });
                }
            });

            $("#btnregresarindex").click(function(){
                comunica("' . $this->pantalla . '");
            });

            $("#autorizar").click(function() {
                if (s=validaTip()) {
                    if (seleccionados = $("#tbl-clcs").tabla("getFilaSeleccion")) {
                        n = seleccionados.length;
                        mensaje = "";
                        if(n==1)
                            mensaje ="Confirme la Autorización de la CLC "+seleccionados[0].clc_id ;
                        else
                            mensaje ="Confirme la Recepción de las "+ n +" CLCs seleccionadas ";

                        alertify.confirm("AUTORIZAR CLC", mensaje,
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"AutorizarClc",
                                    tcl_id:s,
                                    tareaclc:"' . $this->prm->tareaclc . '",
                                    autorizarclcs:seleccionados
                                    }
                                );
                            },undefined
                        ).set("defaultFocus","cancel")
                        .set("labels", {
                            ok: "Aceptar", cancel: "Cancelar"
                        });
                        const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                        if (btnAceptar) {
                            btnAceptar.classList.remove("btn-primary");
                            btnAceptar.classList.add("btn-outline-success");
                        }
                        const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                        if (cancela) {
                            cancela.classList.remove("btn-danger");
                            cancela.classList.add("btn-outline-danger");
                        }
                    }
                }
            });

            $("#enviar").click(function() {
                if (s=validaTip()) {
                    if (seleccionados = $("#tbl-clcs").tabla("getFilaSeleccion")) {
                        n = seleccionados.length;
                        mensaje = "";
                        if(n==1)
                            mensaje ="Confirme el envío de la CLC "+seleccionados[0].clc_id ;
                        else
                            mensaje ="Confirme el envío de las "+ n +" CLCs seleccionadas ";

                        $("#gpo_id").val( seleccionados[0].gpo_id);
                        $("#ur_id").val( seleccionados[0].ur_id);
                        $("#clc_id").val( seleccionados[0].clc_id);

                        alertify.confirm("AUTORIZAR CLC", mensaje,
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"EnviarClc",
                                    tcl_id:s,
                                    tareaclc:"' . $this->prm->tareaclc . '",
                                    enviarclcs:seleccionados
                                });
                            },
                            function(){},
                        ).set("defaultFocus","cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
                    }
                }
            });

            $("#btnRechazar").click(function() {
                if (s=validaTip()) {
                    if ( seleccionados = $("#tbl-clcs").tabla("getFilaSeleccion") ){
                        comunica("' . $this->pantalla . '",{
                        metodo:"EnviarClc",
                        tcl_id:s,
                        tareaclc:"' . $this->prm->tareaclc . '",
                        rechazarclcs:seleccionados
                        });
                    }
                }
            });

        ');
    }

    private function llenaTablaClc()
    {
        $wf = '';
        $ban = 0;
        if (isset($this->prm->tipo) and $this->prm->tipo != '') {
            $wf .= ' AND tcl_id="' . $this->prm->tipo . '"';
        }

        if (isset($this->prm->tareaclc) and $this->prm->tareaclc != '') {
            switch ($this->prm->tareaclc) {
                case 'generacion':
                    $wf .= ' AND  scp_Clc.ecl_id = 1';
                    break;
                case 'impresion':
                    $ban = 1;
                    $wf .= ' AND  scp_ClcAuxiliar.ecl_id = 3 AND clc_recursoFederal = 2';
                    break;
                case 'federales':
                    $ban = 1;
                    $wf .= ' AND scp_ClcAuxiliar.ecl_id = 17  AND  clc_recursoFederal = 1';
                    break;
                case 'autorizacion':
                    $wf .= ' AND scp_Clc.ecl_id = 1  AND month(clc_fechaElaboracion) = ' . date('n') . '';
                    break;
                case 'enviar':
                    $wf .= ' AND scp_Clc.ecl_id = 2  AND month(clc_fechaElaboracion) = ' . date('n') . '  AND clc_recursoFederal = 2 ';
                    break;
                case 'ejercer':
                    $wf .= ' AND  scp_Clc.ecl_id = 2  AND month(clc_fechaElaboracion) = ' . date('n') . ' AND  clc_recursoFederal = 1';
                    break;
            }
        }

        $wf .= ' AND gpo_id=' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id=' . $_SESSION[_S]['clasAdm-ur'] . ' ';
        $this->conectar();

        if ($ban == 0) {
            $sql = '
                SELECT
                gpo_id,ur_id,clc_id,
                DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                month(clc_fechaElaboracion) AS mes,
                tcl_id,stc_id,tcl_nombre,
                bur_id,clc_percepcion,clc_retencion,
                epg_id,
                if(stc_id != null OR stc_id != ""
                ,stc_nombre
                ,tcl_nombre) AS subtipo,tcp_id,
                TRUNCATE(clc_percepcion,2)-TRUNCATE(clc_retencion,2) AS importe,
                clc_percepcion + clc_retencion AS importe2,
                scp_Clc.ecl_id,ecf_id,stc_id,
                tno_id,mno_id,smn_id,
                mpc_id,clc_recursoFederal,clc_rectificacion,
                ecf_clave
                # obr_id
                FROM scp_Clc
                INNER JOIN ' . BD_SINPRES . '.enc_ClaveFinanciamiento USING (ecf_id)
                LEFT JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id)
                LEFT JOIN cat_ObraClc USING (gpo_id,ur_id,clc_id)
                INNER JOIN cat_TipoClc USING (tcl_id)
                LEFT JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                WHERE
                clc_id != 0
                AND ocl_id = 1 ' . $wf . '
                ORDER BY clc_id
                ';
        } else {
            $sql = '
                SELECT
                gpo_id,ur_id,clc_id,
                DATE_FORMAT(clc_fechaElaboracion,"%d/%m/%Y") AS clc_fechaElaboracion,
                month(clc_fechaElaboracion) AS mes,
                tcl_id,stc_id,tcl_nombre,
                bur_id,clc_percepcion,
                clc_retencion,epg_id,
                if(stc_id != null OR stc_id != ""
                ,stc_nombre
                ,tcl_nombre) AS subtipo,tcp_id,
                TRUNCATE(clc_percepcion,2)-TRUNCATE(clc_retencion,2) AS importe,
                clc_percepcion + clc_retencion AS importe2,
                scp_Clc.ecl_id,ecf_id,stc_id,
                tno_id,mno_id,smn_id,mpc_id,
                clc_recursoFederal,clc_rectificacion,
                ecf_clave
                FROM scp_Clc
                #INNER JOIN ' . BD_SINPRES . '.enc_ClaveFinanciamiento USING ( ecf_id )
                LEFT JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id)
                LEFT JOIN cat_ObraClc USING (gpo_id,ur_id,clc_id)
                INNER JOIN cat_TipoClc USING (tcl_id)
                LEFT JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                WHERE
                clc_id != 0
                AND ocl_id = 1 ' . $wf . '
                ORDER BY clc_id';
        }
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-clcs';
        $nav->nf = 50;

        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify( "No hay registros", "warning");');
        }
        $this->mixScript('$(".tcl_ida").removeClass("loadingclas");');
    }

    public function listadoClc()
    {
        $ur_clave = '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc)) ? $this->prm->tareaclc : '';
        $this->prm->gpo_id = (isset($this->prm->gpo_id)) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = (isset($this->prm->ur_id)) ? $this->prm->ur_id : '';

        /* Filtro de listado */
        if ($this->prm->gpo_id != '' && $this->prm->ur_id != '') {
            $this->prm->ur_id = str_pad($this->prm->ur_id, 2, '0', STR_PAD_LEFT);
            $ur_clave = $this->prm->gpo_id . $this->prm->ur_id;
        }

        $clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';

        $filtro = '';
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->icon = 'solid fa-circle-left me-2';

        $btnbuscar = new stdClass();
        $btnbuscar->id = 'buscar';
        $btnbuscar->color = 'gris';
        $btnbuscar->name = 'buscar';
        $btnbuscar->icon = 'search me-2';
        $btnbuscar->value = 'Buscar';

        $RechazarCarga = '';

        $filtro .= '
            <script type="text/javascript" languaje="javascript" src="js/jma_viewer.js"></script>

            <div class="col-md-12 col-xs-12">
            <div class="row">
            <div class="col-md-2">
                <label class="sr-only" for="ur_clave">UR</label>
                <input type="text" class="form-control" name="ur_clave" id="ur_clave" placeholder="CLAVE UR" value="' . $ur_clave . '" />
            </div>
            <div class="col-md-2">
                <label class="sr-only" for="clc_id">CLC</label>
                <input type="text" class="form-control" name="clc_id" id="clc_id" placeholder="No. CLC" value="' . $clc_id . '" />
            </div>
            <div class="col-md-2">
            ' . $this->ponBoton($btnbuscar) . '
            </div>
            </div>
        ';
        // /* DEBERA SER UNA TAREA  */
        if ($_SESSION[_S]['usr_id'] == -1) {
            $RechazarCarga = new stdClass();
            $RechazarCarga->id = 'RechazarCarga';
            $RechazarCarga->color = 'rojo';
            $RechazarCarga->icon = 'times me-2';
            $RechazarCarga->value = 'Rechazar carga a ';
            $RechazarCarga = $this->ponBoton($RechazarCarga);

            $filtro .= '
                <div class="col-md-3">
                    <label class="sr-only" for="clc_id">CLC</label>
                    <input type="text" class="form-control" name="tem_carga" id="tem_carga" placeholder="No. Carga" value="" />
                </div>
            ';
        }

        $filtro .= '
            ' . $RechazarCarga . '
            </div>
            <br>
        ';
        $tarea = '';

        switch ($this->prm->tareaclc) {
            case 'recepcion':
                $tarea = '( RECEPCI&Oacute;N)';
                break;
            case 'revision':
                $tarea = '( REVISI&Oacute;N)';
                break;
            case 'aplicacion':
                $tarea = '( APLICACI&Oacute;N)';
                break;
        }

        $tabla = new stdClass();
        $tabla->id = 'tbl-ListadoClcs';
        $tabla->tools = $filtro;
        $tabla->titulo = "Listado de CLC'S " . $tarea;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaListadoClc';
        $tabla->comunica->tareaclc = $this->prm->tareaclc;
        $tabla->comunica->ur_clave = '{$("#ur_clave").val()}';
        $tabla->comunica->clc_id = '{$("#clc_id").val()}';
        $tabla->numFilas = 150;
        $tabla->filasAmostrar = [10, 50, 100, 150];

        if ($this->prm->tareaclc == 'recepcion') {
            $tabla->multiseleccionable = true;
            $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'clc_id'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'ecl_id';
            $tabla->casoSeleccionable->valor = '3';
            $tabla->casoSeleccionable->ands = [];
            $and = new stdClass();
            $and->campo = 'importe2';
            $and->operador = '!=';
            $and->valor = '0';
            $and->valorTipo = 'numero';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casoSeleccionable->ands, $and);
        }

        $trHead = [];
        $trBody = [];

        if ($this->prm->tareaclc == 'revision') {
            $th = new stdClass();
            $th->align = 'center';
            $th->texto = 'Revisión';
            array_push($trHead, $th);
        }

        if ($this->prm->tareaclc == 'aplicacion') {
            $tabla->multiseleccionable = true;
            $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'clc_id'];
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = 'ecl_id';
            $tabla->casoSeleccionable->valor = '5';
            $tabla->casoSeleccionable->ands = [];
            $and = new stdClass();
            $and->campo = 'importe2';
            $and->operador = '!=';
            $and->valor = '0';
            $and->valorTipo = 'numero';
            $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
            array_push($tabla->casoSeleccionable->ands, $and);
        }

        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'UR';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'CLC';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Concepto';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Fecha Env.';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Tipo';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Modalidad Pago';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Cta. Bancaria';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Beneficiario';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Percepciones';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Retenciones';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Importe Neto';
        array_push($trHead, $th);
        $th = new stdClass();
        $th->align = 'center';
        $th->texto = 'Estado';
        array_push($trHead, $th);

        if ($this->prm->tareaclc == 'revision') {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = 'check';
            $td->color = 'verde';
            $td->class = 'detalle';
            $td->attr = new stdClass();
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
            $td->attr->clc_id = 'clc_id';
            $td->attr->stc_id = 'stc_id';
            $td->attr->mpc_id = 'mpc_id';
            $td->attr->ecf_id = 'ecf_id';
            $td->attr->tipo = 'tcl_id';
            array_push($trBody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($trBody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        $td->ajustable = true;
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_fechaEnvio';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_id';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'mpc_nombre';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_beneficiario';
        $td->ajustable = true;
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_percepcion';
        $td->formato = 'moneda';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_retencion';
        $td->formato = 'moneda';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_importeneto';
        $td->formato = 'moneda';
        array_push($trBody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trBody, $td);

        $tabla->encabezado = [$trHead];
        $tabla->cuerpo = $trBody;

        $this->mixHtml('
            <div class="col-md-12 col-xs-12">
                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');

        $this->mixScript('
            $("#btnregresarindex").click(function(){
                comunica("' . $this->pantalla . '");
            });

            $("#RechazarCarga").click(function(){
                if ($("#tem_carga").val()!="") {
                    comunica("' . $this->pantalla . '",{
                        metodo:"RegresarClcVentanilla",
                        tareaclc:"rechazarCarga",
                        tem_carga:$("#tem_carga").val()
                    });
                } else {
                    notify("Ingresa el Número de la carga","warning");
                    return false;
                }
            });');

        $btnRecepcionar = new stdClass();
        $btnRecepcionar->id = 'btnRecepcionar';
        $btnRecepcionar->name = 'btnRecepcionar';
        $btnRecepcionar->color = 'verde';
        $btnRecepcionar->class = 'btn btn-test recibir me-1';
        $btnRecepcionar->value = 'Recibir';
        $btnRecepcionar->icon = 'solid fa-check me-2';

        $btnRechazar = new stdClass();
        $btnRechazar->id = 'btnRechazar';
        $btnRechazar->name = 'btnRechazar';
        $btnRechazar->class = 'rechazar';
        $btnRechazar->color = 'rojo';
        $btnRechazar->value = 'Rechazar';
        $btnRechazar->icon = 'times me-2';

        $btnAplicar = new stdClass();
        $btnAplicar->id = 'btnAplicar';
        $btnAplicar->name = 'btnAplicar';
        $btnAplicar->class = 'me-1';
        $btnAplicar->color = 'verde';
        $btnAplicar->value = 'Aplicar';
        $btnAplicar->icon = 'solid fa-check me-2';

        $botones = '';

        $this->mixScript(' $("#buscar").click(function(event){ ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . ' }); ');
        switch ($this->prm->tareaclc) {
            case 'recepcion':
                $this->agregaTools(' ' . $this->ponBoton($btnregresarindex) . ' ' . $this->ponBoton($btnRecepcionar) . ' ' . $this->ponBoton($btnRechazar));
                $this->mixScript('
                    $("#btnRecepcionar").click(function() {
                        if (seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")) {
                            n = seleccionados.length;
                            mensaje = "";
                            if(n==1)
                                mensaje ="Confirme la Recepción de la CLC "+seleccionados[0].clc_id +" de la UR " + seleccionados[0].gpo_id + seleccionados[0].ur_id ;
                            else
                                mensaje ="Confirme la Recepción de las "+ n +" CLCs seleccionadas ";
                            alertify.confirm("REVISAR CLC", mensaje,
                                function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"RecepcionClc",
                                        tareaclc:"' . $this->prm->tareaclc . '",
                                        recepcionclcs:seleccionados
                                    });
                                },undefined
                            ).set("defaultFocus","cancel")
                            .set("labels", {
                                ok: "Aceptar", cancel: "Cancelar"
                            });
                            const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-outline-success");
                            }
                            const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                            if (cancela) {
                                cancela.classList.remove("btn-danger");
                                cancela.classList.add("btn-outline-danger");
                            }
                        }
                    });

                    $("#btnRechazar").click(function() {
                        if (seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")) {
                            comunica("' . $this->pantalla . '",{
                            metodo:"RecepcionClc",
                            tareaclc:"' . $this->prm->tareaclc . '",
                            rechazoclcs:seleccionados
                            });
                        }
                    });
                ');
                break;
            case 'revision':
                $this->agregaTools(' ' . $this->ponBoton($btnregresarindex) . '');
                break;
            case 'aplicacion':
                $this->agregaTools(' ' . $this->ponBoton($btnregresarindex) . ' ' . $this->ponBoton($btnAplicar) . ' ' . $this->ponBoton($btnRechazar) . ' ');
                $this->mixScript('
                    $("#btnAplicar").click(function() {
                        if (seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")) {
                            n = seleccionados.length;
                            mensaje = "";
                            if(n==1)
                                mensaje ="Confirme la Aplicación de la CLC "+seleccionados[0].clc_id +" de la UR " + seleccionados[0].gpo_id + seleccionados[0].ur_id ;
                            else
                                mensaje ="Confirme la Aplicación de las "+ n +" CLCs seleccionadas ";
                            alertify.confirm("APLICAR CLC", mensaje,
                                function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo:"AplicarClcSefin",
                                        tareaclc:"' . $this->prm->tareaclc . '",
                                        aplicacionclcs:seleccionados
                                    });
                                },undefined
                            ).set("defaultFocus","cancel")
                            .set("labels", {
                                ok: "Aceptar", cancel: "Cancelar"
                            });
                            const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-outline-success");
                            }
                                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                            if (cancela) {
                                cancela.classList.remove("btn-danger");
                                cancela.classList.add("btn-outline-danger");
                            }
                        }
                    });

                    $("#btnRechazar").click(function() {
                        if (seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")) {
                            n = seleccionados.length;
                            mensaje = "";
                            if(n==1)
                                mensaje ="Confirme el rechazo de la CLC "+seleccionados[0].clc_id +" de la UR " + seleccionados[0].gpo_id + seleccionados[0].ur_id ;
                            else
                                mensaje ="Confirme el rechazo de las "+ n +" CLCs seleccionadas ";
                        alertify.confirm("RECHAZAR CLC", mensaje,
                            function(){
                                comunica("' . $this->pantalla . '",{
                                    metodo:"AplicarClcSefin",
                                    tareaclc:"' . $this->prm->tareaclc . '",
                                    rechazoclcs:seleccionados
                                });
                            },undefined
                            ).set("defaultFocus","cancel")
                            .set("labels", {
                                ok: "Aceptar", cancel: "Cancelar"
                            });
                            const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                            if (btnAceptar) {
                                btnAceptar.classList.remove("btn-primary");
                                btnAceptar.classList.add("btn-outline-success");
                            }
                                const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                            if (cancela) {
                                cancela.classList.remove("btn-danger");
                                cancela.classList.add("btn-outline-danger");
                            }
                        }
                    });
                ');
                break;
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $botones . '</div>');
        if ($this->prm->gpo_id != '' && $this->prm->ur_id != '') {
            $this->llenaTablaListadoClc();
        }
    }

    public function llenaTablaListadoClc()
    {
        $extra = $departamentos = '';
        $mes = date('n');
        $where = '';
        if (isset($this->prm->ur_clave) and $this->prm->ur_clave != '') {
            $where = ' WHERE gpo_id=' . (substr($this->prm->ur_clave, 0, 1)) . ' and ur_id=' . (substr($this->prm->ur_clave, 1, 2));
        }

        if (isset($this->prm->clc_id) and $this->prm->clc_id != '') {
            $where .= ($where == '') ? 'WHERE clc_id=' . $this->prm->clc_id . ' ' : ' AND clc_id = ' . $this->prm->clc_id . ' ';
        }

        switch ($this->prm->tareaclc) {
            case 'recepcion':
                $where .= ($where == '') ? ' WHERE ecl_id=3 AND month(clc_fechaEnvio)= ' . $mes . ' ' : ' AND ecl_id=3 AND month(clc_fechaEnvio)= ' . $mes . ' ';
                break;
            case 'revision':
                if ($_SESSION[_S]['uni_id'] == 18 && $_SESSION[_S]['dep_id'] != 48 && $_SESSION[_S]['dep_id'] != 49) {
                    $departamentos = '  AND dep_id in(48,49) ';
                } elseif ($_SESSION[_S]['uni_id'] == 17 && $_SESSION[_S]['dep_id'] != 44 && $_SESSION[_S]['dep_id'] != 45 && $_SESSION[_S]['dep_id'] != 46) {
                    $departamentos = '  AND dep_id in(44,45,46) ';
                } else {
                    $departamentos = ' AND dep_id="' . $_SESSION[_S]['dep_id'] . '" ';
                    if ($_SESSION[_S]['usr_id'] == 118) {
                        $departamentos = ' AND dep_id IN(44,45,46,48,49)';
                    }

                }
                $where .= ($where == '') ? ' WHERE ecl_id=4 ' . $departamentos . ' ' : ' AND ecl_id=4 ' . $departamentos . ' ';
                break;
            case 'aplicacion':

                if ($_SESSION[_S]['uni_id'] == 18 && $_SESSION[_S]['dep_id'] != 48 && $_SESSION[_S]['dep_id'] != 49) {
                    $departamentos = '  AND dep_id in(48,49) ';
                } elseif ($_SESSION[_S]['uni_id'] == 17 && $_SESSION[_S]['dep_id'] != 44 && $_SESSION[_S]['dep_id'] != 45 && $_SESSION[_S]['dep_id'] != 46) {
                    $departamentos = '  AND dep_id in(44,45,46) ';
                } else {
                    $departamentos = ' AND dep_id="' . $_SESSION[_S]['dep_id'] . '" ';
                    if ($_SESSION[_S]['usr_id'] == 118) {
                        $departamentos = ' AND dep_id IN(44,45,46,48,49)';
                    }

                }
                $where .= ($where == '') ? ' WHERE ecl_id=5 ' . $departamentos . ' ' : ' AND ecl_id=5 ' . $departamentos . ' ';
                break;
        }

        $sql = '
            SELECT
            gpo_id,ur_id,clc_id,
            DATE_FORMAT(clc_fechaEnvio,"%d/%m/%Y") AS clc_fechaEnvio,
            clc_concepto,clc_beneficiario,ecl_id,
            ecl_nombre,scp_Clc.ecf_id,stc_id,
            mpc_id, mpc_nombre,
            clc_percepcion + clc_retencion AS importe2,
            bur_id, scp_Clc.tcl_id,tcl_nombre,
            clc_percepcion,clc_retencion,TRUNCATE(clc_percepcion,2)-TRUNCATE(clc_retencion,2) AS clc_importeneto,
            clc_recursoFederal AS dep_nombre
            FROM scp_Clc
            INNER JOIN scp_ClcAuxiliar USING (gpo_id,ur_id,clc_id,ecl_id)
            INNER JOIN cat_EstadoClc USING (ecl_id)
            INNER JOIN cat_ModoPagoClc USING (mpc_id)
            INNER JOIN cat_TipoClc USING (tcl_id)
            INNER JOIN enc_FiltroClc USING (gpo_id, ur_id, tcl_id,stc_id,mno_id)
            INNER JOIN cat_Departamento USING (dep_id)
            ' . $where . '
            AND clc_recursoFederal = 2 AND clc_id != 0
            GROUP BY gpo_id,ur_id,clc_id,tcl_nombre
            ORDER BY gpo_id,ur_id,clc_id;
            ';
        $this->conectar();
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ListadoClcs';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 10;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify( "No hay registros", "warning");');
        }

        $this->mixScript('
            $(".detalle").click(function() {
                id = $(this).attr("gpo_id")+$(this).attr("ur_id")+$(this).attr("clc_id");
                men = "REVISION DE LA CLC "+ $(this).attr("clc_id") + "DE LA UR " + $(this).attr("gpo_id")+ $(this).attr("ur_id");

                $(this).parent().parent().attr("id","tr"+id);
                load_viewer(id,men,"div","",1030, true);
                comunica("scp/afectaciones/ControladorClcFinanzas.php",{
                    metodo:"DetalleRevisionClc",
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    clc_id:$(this).attr("clc_id"),
                    stc_id:$(this).attr("stc_id"),
                    tipo:$(this).attr("tipo"),
                    mpc_id:$(this).attr("mpc_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    tareaclc:"' . $this->prm->tareaclc . '"
                });
                return false;
            });
        ');
    }

    public function RecepcionClc()
    {
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $this->prm->llenar = (isset($this->prm->llenar) && $this->prm->llenar != '') ? $this->prm->llenar : 0;
        $cedula = '';

        $clc = [];

        if (isset($this->prm->recepcionclcs) && $this->prm->recepcionclcs != '') {
            foreach ($this->prm->recepcionclcs as $idrecepcion) {
                $grupo = $idrecepcion->gpo_id;
                $ur = $idrecepcion->ur_id;
                $cedula .= $idrecepcion->clc_id . ',';
                array_push($clc, $idrecepcion->clc_id);

                $this->conectar();
                $this->setSql('
                    SET @usr_id = ?,
                    @usr_ip = INET_ATON(?),
                    @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                ');
                $params = [$this->usr_id, $this->usr_ip];
                $this->consultar($params);

                $this->setSql('
                    UPDATE scp_ClcAuxiliar
                    SET ecl_id = 4
                    WHERE gpo_id= ?
                    AND ur_id= ?
                    AND clc_id= ?
                    AND ecl_id = 3 ;
                ');
                $params = [$idrecepcion->gpo_id, $idrecepcion->ur_id, $idrecepcion->clc_id];
                $this->consultar($params);

                $this->conectar();
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (?,?,?,4,"RECIBIDA POR LA SEFIN",INET_ATON(?),?,NOW());
                    ');
                $params = [$idrecepcion->gpo_id, $idrecepcion->ur_id, $idrecepcion->clc_id, $this->usr_ip, $this->usr_id];
                $this->consultar($params);
                $this->desconectar();

                $this->prm->gpo_id = $grupo;
                $this->prm->ur_id = $ur;
            }
            asort($clc, SORT_NUMERIC);
            $cedula = implode(',', $clc);
            if (count($clc) == 1) {
                $this->mixScript('
                    notify( "La CLC ' . $cedula . ' ha  sido recibida correctamente ","success",false);
                ');
            } else {
                $this->mixScript('
                    notify( "Las CLC(S) ' . $cedula . ' ha (n)  sido recibida (s)  correctamente ","success",false);
                ');
            }

            if ($this->prm->llenar == 1) {
                $this->llenaGeneraLista();
            } else {
                $this->llenaTablaListadoClc();
            }
        } elseif (isset($this->prm->rechazoclcs) && $this->prm->rechazoclcs != '') {
            $total = 0;
            foreach ($this->prm->rechazoclcs as $idrechazo) {
                $grupo = $idrechazo->gpo_id;
                $ur = $idrechazo->ur_id;
                $cedula .= $idrechazo->clc_id . ',';
                $this->conectar();

                $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                $usr_id = $_SESSION[_S]['usr_id'];
                $sqlDatos = '
                    SET
                        @usr_ip =INET_ATON(?),
                        @sp = 1,
                        @usr_id = ?,
                        @bit_observacion="RECHAZO DE CLC DESDE PANTALLA S_2_2[' . __FUNCTION__ . '][' . __LINE__ . ']";
                    ';
                $params = [$this->usr_ip, $this->usr_id];
                $this->setSql($sqlDatos);
                $this->consultar($params);

                $this->setSql('CALL sp_RechazarCLC' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?)');
                $params = [$idrechazo->gpo_id, $idrechazo->ur_id, $idrechazo->clc_id, $this->usr_ip, $this->usr_id];
                $this->consultar($params);

                $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
                $this->consultar();
                $rowmensaje = $this->extraer();
                if ($rowmensaje['bandera'] != 1) {
                    $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","error"); ');
                } else {
                    $total++;
                }
                $this->desconectar();
            }

            $this->prm->gpo_id = $grupo;
            $this->prm->ur_id = $ur;
            if ($total > 0) {
                $this->mixScript('notify( "Las CLC(S) ' . $cedula . ' ha (n)  sido rechazada (s)  correctamente ","success",false);');
            }

            if ($this->prm->llenar == 1) {
                $this->llenaGeneraLista();
            } else {
                $this->llenaTablaListadoClc();
            }
        }
    }

    public function AplicarClcSefin()
    {
        $ur = $grupo = $cedula = '';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';

        if (isset($this->prm->aplicacionclcs) && $this->prm->aplicacionclcs != '') {
            foreach ($this->prm->aplicacionclcs as $idaplicacion) {
                $grupo = $idaplicacion->gpo_id;
                $ur = $idaplicacion->ur_id;
                $cedula .= $idaplicacion->clc_id . ',';

                $this->conectar();
                $this->setSql('SET @usr_id = ? ,@usr_ip = INET_ATON(?),@bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";');
                $params = [$this->usr_id, $this->usr_ip];
                $this->consultar($params);
                $this->setSql('
                    UPDATE scp_Clc
                    SET ecl_id = 6, clc_fechaAutorizacion = NOW()
                    WHERE
                    gpo_id= ?
                    AND ur_id= ?
                    AND clc_id= ?
                    AND ecl_id = 5;
                ');
                $params = [$idaplicacion->gpo_id, $idaplicacion->ur_id, $idaplicacion->clc_id];
                $this->consultar($params);

                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (?,?,?,6,"CLC APLICADA POR LA SEFIN",INET_ATON(?),?,NOW());
                ');
                $params = [$idaplicacion->gpo_id, $idaplicacion->ur_id, $idaplicacion->clc_id, $ip, $user];
                $this->consultar($params);
                $this->desconectar();
            }
            $this->prm->gpo_id = $grupo;
            $this->prm->ur_id = $ur;
            $this->mixScript('notify( "Las CLC(S) ' . $cedula . ' ha (n)  sido aplicada (s)  correctamente ","success");'
                . $this->wrLlamaLlenaTabla('tbl-ListadoClcs'));
        }

        if (isset($this->prm->rechazoclcs) && $this->prm->rechazoclcs != '') {
            foreach ($this->prm->rechazoclcs as $idrechazo) {
                $grupo = $idrechazo->gpo_id;
                $ur = $idrechazo->ur_id;
                $cedula .= $idrechazo->clc_id . ',';
                $this->conectar();

                $sqlDatos = '
                    SET
                    @sp = 1,
                    @usr_ip = INET_ATON(?),
                    @usr_id = ? ,
                    @bit_observacion = "RECHAZO DE CLC DESDE PANTALLA S_2_2[' . __FUNCTION__ . '][' . __LINE__ . ']";
                ';
                $params = [$this->usr_ip, $this->usr_id];
                $this->setSql($sqlDatos);
                $this->consultar($params);

                $this->setSql('CALL sp_RechazarCLC' . APP_EJERCICIO . '(?,?,?,INET_ATON(?),?)');
                $params = [$idrechazo->gpo_id, $idrechazo->ur_id, $idrechazo->clc_id, $this->usr_ip, $this->usr_id];
                $this->consultar($params);

                $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
                $this->consultar();
                $rowmensaje = $this->extraer();
                if ($rowmensaje['bandera'] != 1) {
                    $this->mixScript(' notify(" ' . $rowmensaje['mensaje'] . ' ","error"); ');
                    return false;
                }
                $this->desconectar();
            }
            $this->prm->gpo_id = $grupo;
            $this->prm->ur_id = $ur;
            $this->mixScript('notify( "Las CLC(S) ' . $cedula . ' ha (n)  sido rechazada (s)  correctamente ","success");
                ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs'));
        }
    }

    private function GeneraLista()
    {
        $html = '';
        $select = $wh = '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        if ($_SESSION[_S]['uni_id'] == 17) {
            $wh = 'dep_id in(44,45,46)';
            $select = 'CASE dep_id
                when 44 then "NATIVIDAD"
                when 45 then "GABRIELA"
                when 46 then "ALBERTO IMELDO" ';
        } else {
            $wh = 'dep_id in(48,49,50)';
            $select = 'CASE dep_id
                when 44 then "NATIVIDAD"
                when 45 then "GABRIELA"
                when 46 then "ALBERTO IMELDO" ';
        }

        $depto = new stdClass();
        $depto->id = 'depto';
        $depto->class = 'limpiar';
        $depto->form = 'enviaPost';
        $depto->detonar = false;
        $depto->sql = '
            SELECT dep_id,' . $select . '
            END as dep_nombre
            FROM cat_Departamento where ' . $wh . ';
        ';
        $depto->label = 'DEPARTAMENTO';
        $depto->comoAtr = 'dep_id';
        $depto->change = '
            comunica("' . $this->pantalla . '",{
                metodo:"DatosClc",
                dep_id:$("#depto").val()
            });
        ';
        $depto->enRow = 1;
        $depto = $this->ponCombo($depto);

        $estado = new stdClass();
        $estado->id = 'ecl_id';
        $estado->class = 'limpiar';
        $estado->form = 'enviaPost';
        $estado->sql = '
            SELECT ecl_id, CASE ecl_id when 3 then " EN VENTANILLA" when 4 then "RECEPCIONADAS" END ecl_nombre
            FROM cat_EstadoClc
            WHERE ecl_id in(3,4);
        ';
        $estado->label = 'ESTADO';
        $estado->enRow = 1;
        $estado = $this->ponCombo($estado);

        $Fechalista = new stdClass();
        $Fechalista->id = 'Fechalista';
        $Fechalista->name = 'Fechalista';
        $Fechalista->label = 'Fecha de la lista';
        $Fechalista->class = 'datepicker limpiar';
        $Fechalista->enDiv = true;
        $Fechalista->formato = 'fecha';
        $Fechalista->form = 'enviaPost';
        $Fechalista->placeholder = date('Y-d-m');
        $scriptFecha = $this->getScript();

        $combo = new stdClass();
        $combo->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc;';
        $combo->label = 'SELECCIONA EL TIPO DE CLC';
        $combo->id = 'tcl_id';
        $combo->name = 'tcl_id';
        $combo->form = 'enviaPost';
        $combo->mensaje = false;
        $combo->enDiv = true;
        $combo->detonar = false;
        $comboClc = $this->templateCombo($combo);

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'Número de Clc';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'Ingresa el número de CLC';

        $bxClc->enDiv = true;

        $bxUr = new stdClass();
        $bxUr->id = 'bxUr';
        $bxUr->name = 'bxUr';

        if (isset($this->prm->gpo_id) && $this->prm->gpo_id != '' && isset($this->prm->ur_id) && $this->prm->ur_id != '') {
            $this->prm->ur_id = str_pad($this->prm->ur_id, 2, '0', STR_PAD_LEFT);
            $bxUr->value = $this->prm->gpo_id . $this->prm->ur_id;
        }

        $bxUr->label = 'UR';
        $bxUr->class = 'limpiar';
        $bxUr->placeholder = 'Ingresa la UR';
        $bxUr->enDiv = true;

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'Número de Clc';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'Ingresa el número de CLC';

        $btnConsulta = new stdClass();
        $btnConsulta->id = 'btnConsulta';
        $btnConsulta->class = 'me-1';
        $btnConsulta->value = 'Buscar';
        $btnConsulta->value = 'Buscar';
        $btnConsulta->text = 'Buscar';
        $btnConsulta->color = 'gris';
        $btnConsulta->icon = 'search me-2';

        $lista = new stdClass();
        $lista->id = 'lista';
        $lista->class = 'btn btn-test';
        $lista->type = 'submit';
        $lista->value = 'Imprimir Lista';
        $lista->icon = 'solid fa-file-pdf me-2';
        $lista->color = 'rojo';

        $clean = new stdClass();
        $clean->class = 'me-1';
        $clean->value = 'Limpiar';
        $clean->id = 'clean';
        $clean->color = 'cielo';
        $clean->icon = 'refresh me-2';

        $ListadoRecibidas = new stdClass();
        $ListadoRecibidas->id = 'ListadoRecibidas';
        $ListadoRecibidas->color = 'verde';
        $ListadoRecibidas->class = 'btn';
        $ListadoRecibidas->icon = 'solid fa-file-excel me-2';
        $ListadoRecibidas->value = 'Listado del dia';
        $ListadoRecibidas->click = '
            if ($("#Fechalista").val() != "") {
                form = $("<form>")  .attr("action","rep/excel/scp/afectaciones/RepClcVentanilla.php")
                    .attr("method","post")
                    .attr( "target","_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "Fechalista").val( $("#Fechalista").val() ));
                $("body").append(form);
                form.submit();
                form.remove();
            } else {
                notify("Elije la fecha","warning");
                return false;
            }
        ';
        $ListadoRecibidas = $this->ponBoton($ListadoRecibidas);

        $html .= '
            <div class="row col-md-12 col-xs-12 mb-3">
                <fieldset class="col-md-4 col-xs-4">
                ' . $depto . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                ' . $comboClc . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                ' . $estado . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-3">
                <fieldset class="col-md-3 col-xs-3">
                    ' . $this->ponInput($Fechalista) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <label for="bxClc" class="control-label">Hora Inicio </label>
                    <input id="horaInicio" class="form-control limpiar" type="time" form="enviaPost"  name="horaI" placeholder="00:00:00" max="23:59:59" min="09:00:00" step="1" novalidate="false">
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <label for="bxClc" class="control-label">Hora Final </label>
                    <input id="horaFin" class="form-control limpiar" type="time" form="enviaPost" name="horaF" placeholder="00:00:00"  max="23:59:59" min="09:00:00" step="1"  novalidate="false">
                </fieldset>
                <fieldset  class="col-md-2 col-xs-2">
                    <label class="control-label">Reporte por fecha y hora </label>
                    ' . $this->ponBoton($lista) . '
                </fieldset>
                <fieldset  class="col-md-2 col-xs-2">
                    <label class="control-label">Reporte por fecha </label>
                        ' . $ListadoRecibidas . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-3">
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxUr) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxClc) . '
                </fieldset>
                <fieldset  class="col-md-6 col-xs-6">
                <label class="control-label">Busqueda</label>
                <br>
                    ' . $this->ponBoton($btnConsulta) . ' ' . $this->ponBoton($clean) . '
                </fieldset>
            </div>
        ';

        $scriptFecha = $this->getScript();
        $tabla = new stdClass();

        $tabla->id = 'tbl-ListadoClcs';
        $tabla->tools = $html;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->Fechalista = '{$("#Fechalista").val()}';
        $tabla->comunica->metodo = 'llenaGeneraLista';
        $tabla->comunica->bxUr = '{$("#bxUr").val()}';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->depto = '{$("#depto").val()}';
        $tabla->comunica->tipo = '{$("#tcl_id").val()}';
        $tabla->comunica->estado = '{$("#ecl_id").val()}';
        $tabla->comunica->horaInicio = '{$("#horaInicio").val()}';
        $tabla->comunica->horaFin = '{$("#horaFin").val()}';
        $tabla->numFilas = 150;
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'clc_id'];
        $tabla->casoSeleccionable = new stdClass();
        $tabla->casoSeleccionable->campo = 'ecl_id';
        $tabla->casoSeleccionable->valor = '3';
        $tabla->casoSeleccionable->ands = [];
        $and = new stdClass();
        $and->campo = 'importe2';
        $and->operador = '!=';
        $and->valor = '0';
        $and->valorTipo = 'numero';
        $tabla->casoSeleccionable->colorEnFalso = 'rgba(8, 255, 18, 0.20)';
        array_push($tabla->casoSeleccionable->ands, $and);

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Concepto';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Fecha Env. ';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Tipo';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Beneficiario';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Percepciones';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Retenciones';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Neto';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        $th->align = 'center';
        array_push($tr, $th);

        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_fechaEnvio';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_beneficiario';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_percepcion';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';
        $td = new stdClass();
        $td->campoTxt = 'clc_retencion';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';
        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';
        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        $td->align = 'center';
        array_push($tabla->cuerpo, $td);

        $tabla->encabezado = [$tr];
        $this->mixHtml($this->wrPonTabla($tabla));

        $btnRecibir = new stdClass();
        $btnRecibir->id = 'btnRecibir';
        $btnRecibir->name = 'btnRecibir';
        $btnRecibir->color = 'verde';
        $btnRecibir->class = 'btn btn-test recibir me-1';
        $btnRecibir->value = 'Recibir';
        $btnRecibir->icon = 'solid fa-check me-2';

        $btnRechazar = new stdClass();
        $btnRechazar->id = 'btnRechazar';
        $btnRechazar->name = 'btnRechazar';
        $btnRechazar->class = 'rechazar';
        $btnRechazar->color = 'rojo';
        $btnRechazar->value = 'Rechazar';
        $btnRechazar->icon = 'times me-2';

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->text = 'Regresar';
        if ($_SESSION[_S]['prm'] != 1) {
            $btnRegresarIndex = $this->ponBoton($btnRegresarIndex);
        } else {
            $btnRegresarIndex = '';
        }

        if (!isset($scriptFecha)) {
            $scriptFecha = '';
        }

        if (!isset($scriptFecha2)) {
            $scriptFecha2 = '';
        }

        $this->mixScript($scriptFecha . $scriptFecha2 . '
            $("#btnConsulta").click(function(){

                if ($("#Fechalista").val() != "") {
                    if ($("#ecl_id").val() == "") {
                        notify("Selecciona el estado de la CLC","error");
                        return false;
                    } else if ($("#depto").val() == "") {
                        notify("Selecciona el Departamento","error");
                        return false;
                    } else if ($("#tcl_id").val() == "" || $("#tcl_id").val() == null) {
                        notify("Selecciona almenos un tipo de CLC","error");
                        return false;
                    } else {
                        ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . '
                    }
                } else {
                    if ($("#ecl_id").val()!="") {
                        ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . '
                    } else {
                        notify("Selecciona el estado de la CLC","error");
                        return false;
                    }
                }
            });

            $("#clean").click(function(){
                $(".limpiar").val("");
                $("#depto").val("");
                $("#depto").change();
                $("#tcl_id").val("");
                $("#tcl_id").change();
                comunica("' . $this->pantalla . '",{
                    metodo:"GeneraLista",
                });
                return false;
            });

            $("#lista").click(function() {
                if ($("#ecl_id").val() == "") {
                        notify("Selecciona el estado de la CLC","error");
                        return false;
                }
                if ($("#depto").val() == "") {
                    notify("Selecciona el Departamento","error");
                    return false;
                } else if ($("#tcl_id").val() == "" || $("#tcl_id").val() == null) {
                    notify("Selecciona almenos un tipo de CLC","error");
                    return false;
                } else if ($("#Fechalista").val() == "") {
                    notify("Selecciona la fecha de impresión","error");
                    return false;
                } else {
                    $("#divContenedorGeneralModal").html(
                        $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal")
                            .html(
                                $("<div/>").addClass("modal-dialog")
                                    .html(
                                        $("<div/>").addClass("modal-content")
                                            .html(
                                                $("<div/>").addClass("modal-header")
                                                    .html( $("<h4/>").addClass("modal-title").text("Elegir el tamaño de la fuente"))
                                                    .append( $("<button/>").addClass("btn-close").attr("type","button").text("").click(function(){ $("#myModal").remove(); return false;}))
                                            ).append(
                                                $("<div/>").addClass("modal-body")
                                                    .html( $("<div/>").addClass("from-group")
                                                        .html( $("<label/>").attr("for","size").text("Tamaño de fuente para generar el reporte solicitado:") )
                                                        .append( $("<select>").attr("name","size").attr("form","enviaPost").attr("id","size").attr("required","1").addClass("form-control show-tick").css("width","70px")
                                                                .append($("<option/>").attr("value","6.5").text("6.5"))
                                                                .append($("<option/>").attr("value","7").text("7"))
                                                                .append($("<option/>").attr("value","7.5").text("7.5"))
                                                                .append($("<option/>").attr("value","8").text("8"))
                                                                .append($("<option/>").attr("value","8.5").text("8.5"))
                                                                .append($("<option/>").attr("value","9").text("9"))
                                                                .append($("<option/>").attr("value","9.5").text("9.5"))
                                                                .append($("<option/>").attr("value","10").text("10"))
                                                                .append($("<option/>").attr("value","10.5").text("10.5"))
                                                                .append($("<option/>").attr("value","11").text("11"))
                                                                .append($("<option/>").attr("value","11.5").text("11.5"))
                                                                .append($("<option/>").attr("value","12").text("12"))
                                                                .append($("<option/>").attr("value","12.5").text("12.5"))
                                                                .append($("<option/>").attr("value","13").text("13"))
                                                                .append($("<option/>").attr("value","13.5").text("13.5"))
                                                                .append($("<option/>").attr("value","14").text("14"))
                                                                .append($("<option/>").attr("value","14.5").text("14.5"))
                                                                .append($("<option/>").attr("value","15").text("15"))
                                                                .append($("<option/>").attr("value","15.5").text("15.5"))
                                                                .append($("<option/>").attr("value","16").text("16"))
                                                        )
                                                    )
                                            ).append( $("<div/>").addClass("modal-footer")
                                                .html( $("<button/>").addClass("btn btn-outline-success").attr("id","Enviar").addClass("btn").text("Generar Reporte")
                                                    .click(function(){
                                                        var form = $("<form>")
                                                        .attr("action", "rep/doc/scp/afectaciones/rep_ClcsRecibidas.php")
                                                        .attr("method", "post")
                                                        .attr("target", "_blank")
                                                        .attr("id", "enviaPost");
                                                        $("body").append(form);

                                                        form.submit();
                                                        form.remove();
                                                        $("#myModal").remove();
                                                        return false;
                                                    })
                                                )
                                                .append( $("<button/>").addClass("btn btn-outline-danger").text("Cancelar").click(function(){ $("#myModal").remove(); return false;}) )
                                            )
                                    )
                            )
                    );
                }
            });

            $("#btnRegresarIndex").click (function(){
                comunica("' . $this->pantalla . '",{metodo:"main"});
                return false;
            });

        ');

        $this->mixScript(' $("#buscar").click(function(event){ ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . ' }); ');
        $this->agregaTools(' ' . $btnRegresarIndex . ' ' . $this->ponBoton($btnRecibir) . ' ' . $this->ponBoton($btnRechazar) . ' ');
        $this->mixScript('
            $("#btnRecibir").click(function() {
                if ($("#Fechalista").val()=="") {
                    if (seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")) {
                        comunica("' . $this->pantalla . '",{
                        metodo:"RecepcionClc",
                        tareaclc:"' . $this->prm->tareaclc . '",
                        recepcionclcs:seleccionados,
                        llenar:true
                        });
                    }
                } else {
                    notify( "La fecha de Lista solo es para Generación  de la Lista de CLC´S  Recibidas", "error");
                    return false;
                }
            });

            $("#btnRechazar").click(function() {
                if ($("#Fechalista").val()=="") {
                    if(seleccionados = $("#tbl-ListadoClcs").tabla("getFilaSeleccion")){
                        comunica("' . $this->pantalla . '",{
                        metodo:"RecepcionClc",
                        tareaclc:"' . $this->prm->tareaclc . '",
                        rechazoclcs:seleccionados,
                        llenar:true
                        });
                    }
                } else {
                    notify( "La fecha de Lista solo es para Generación  de la Lista de CLC´S  Recibidas", "error");
                    return false;
                }
            });
        ');
    }

    private function llenaGeneraLista()
    {
        $select = $wf = '';
        $this->conectar();
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : ''; //Tipos de CLC
        $depto = isset($this->prm->depto) ? $this->prm->depto : ''; //Departamentos
        $this->prm->Fechalista = isset($this->prm->Fechalista) ? $this->prm->Fechalista : 0; // fecha de la lista
        $estado = isset($this->prm->estado) ? $this->prm->estado : ''; // Como se pueden hacer impresiones de dias anteriores mandamos el estado
        $horaInicio = isset($this->prm->horaInicio) ? $this->prm->horaInicio : 0;
        $horaFin = isset($this->prm->horaFin) ? $this->prm->horaFin : 0;

        $inn = $in = '';
        $inicio = $fin = '';

        $wf .= ' WHERE clc_id!=0 ';

        if ($depto != '') {
            $wf .= ' AND dep_id = ' . $depto . ' ';
        }

        if ($tipo != '') {
            switch ($tipo) {
                case 'A':
                    if ($depto != '') {
                        switch ($depto) {
                            case 44:
                            case 45:
                                $wf .= ' AND  (tcl_id="A" OR  (tcl_id="C" AND stc_id=01 )) ';
                                break;
                        }
                    }
                    break;
                case 'B':
                    if ($depto != '') {
                        switch ($depto) {
                            case 44:
                                $wf .= ' AND (tcl_id in("B","D","E","I","J","K","L","M") OR (tcl_id="C" AND stc_id=02)) ';
                                break;
                            case 45:
                                $wf .= ' AND (tcl_id in("B","E","D") OR (tcl_id="C" AND stc_id= 02)) ';
                                break;
                        }
                    }
                    break;
                case 'D':
                    if ($depto != '') {
                        switch ($depto) {
                            case 46:
                                $wf .= ' AND (tcl_id in("D","F","G","H") OR (tcl_id="C" AND stc_id in (03,04))) ';
                                break;
                        }
                    }
                    break;
            }
        }

        $eclbit = '';
        if ($estado != '' && $estado == 4) {
            $eclbit = 'where ecl_id=4';
        } else {
            $eclbit = 'where ecl_id=3';
        }

        if ($this->prm->Fechalista != '' && $this->prm->Fechalista != 0) {
            $this->prm->Fechalista = $this->date_to_mysqldate($this->prm->Fechalista);
            $select = '
                max(FechaProceso) AS FechaProceso,max(hora) AS hora,group_concat(clc_id order by clc_id) as recibida,
                SUM(TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2)) as importe,sum(clc_percepcion) as clc_percepcion,sum(clc_retencion) as clc_retencion,
                ';
            $join = '
                JOIN (
                SELECT gpo_id,ur_id,ecl_id, DATE_FORMAT(MAX(usr_fecha),"%Y/%m/%d %H:%i:%S") AS FechaProceso,clc_id,hour(usr_fecha) as hora
                FROM ' . BD_BITACORA . '.bit_Clc
                ' . $eclbit . ' and  DATE(usr_fecha)= "' . $this->prm->Fechalista . '"
                group by gpo_id,ur_id,clc_id,usr_fecha
                order by gpo_id,ur_id,clc_id,usr_fecha
                )b using(gpo_id,ur_id,clc_id)
                ';

            if ($estado != '' && $estado >= 4) {
                if ($horaInicio != 0 && $horaFin != 0) {
                    $wf .= ' AND c.ecl_id >= 4 AND  DATE_FORMAT(FechaProceso,"%Y/%m/%d %H:%i:%S") BETWEEN  "' . $this->prm->Fechalista . ' ' . $horaInicio . '"  AND  "' . $this->prm->Fechalista . ' ' . $horaFin . '" ';
                } else {
                    $wf .= ' AND c.ecl_id >= 4 AND  DATE(FechaProceso)= "' . $this->prm->Fechalista . '" ';
                }
            } else {

                if ($horaInicio != 0 && $horaFin != 0) {
                    $wf .= ' AND c.ecl_id >=4 AND  DATE_FORMAT(FechaProceso,"%Y/%m/%d %H:%i:%S") BETWEEN  "' . $this->prm->Fechalista . ' ' . $horaInicio . '"  AND  "' . $this->prm->Fechalista . ' ' . $horaFin . '" ';
                } else {
                    $wf .= ' AND c.ecl_id=3 and  DATE(FechaProceso)= "' . $this->prm->Fechalista . '" ';
                }
            }

            $group = '
                GROUP by gpo_id,ur_id,clc_id,tcl_nombre
                ORDER by hora,gpo_id,ur_id,FechaProceso
                ';
        } else {

            if (isset($this->prm->bxUr) and $this->prm->bxUr != '') {
                $ur = substr($this->prm->bxUr, 1, 2);
                $gpo_id = substr($this->prm->bxUr, 0, 1);
                $wf .= ' AND gpo_id=' . $gpo_id . ' AND  ur_id="' . $ur . '" ';
            }

            if (isset($this->prm->bxClc) and $this->prm->bxClc != '') {
                $wf .= ' AND clc_id= ' . $this->prm->bxClc . ' ';
            }

            $select = '
                if(c.ecl_id=3 ,a.clc_id,"X")recibida,DATE_FORMAT(MAX(FechaProceso),"%d/%m/%Y") FechaProceso,
                TRUNCATE(clc_percepcion,2) - TRUNCATE(clc_retencion,2) as importe,clc_percepcion,clc_retencion,
            ';
            $join = '
                JOIN (
                SELECT gpo_id,ur_id,clc_id,ecl_id,usr_fecha as FechaProceso
                FROM ' . BD_BITACORA . '.bit_Clc ' . $eclbit . '
                ORDER BY gpo_id,ur_id,usr_fecha
                ) b using(gpo_id,ur_id,clc_id)
            ';

            if ($estado != '' && $estado >= 4) {
                $wf .= ' AND c.ecl_id >= 4 ';
            } else {
                $wf .= ' AND c.ecl_id=3 ';
            }
            $group = '
                GROUP by gpo_id,ur_id,clc_id,tcl_nombre
                ORDER by gpo_id,ur_id,clc_id
            ';
        }

        $sql = '
            SELECT gpo_id,ur_id,a.clc_id,
            if(clc_tipocereCo=1 ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))cereco, tcl_id,tcl_nombre,clc_concepto,
            b.ecl_id,ecl_nombre,' . $select . '
            clc_beneficiario,DATE_FORMAT(clc_fechaEnvio,"%d/%m/%Y") clc_fechaEnvio,
            clc_percepcion + clc_retencion as importe2,clc_recursoFederal
            FROM scp_Clc a
            INNER JOIN scp_ClcAuxiliar c USING (gpo_id,ur_id,clc_id,ecl_id )
            JOIN cat_UnidadResponsable using(gpo_id,ur_id)
            JOIN enc_ClaveFinanciamiento using(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id )
            JOIN ' . BD_SINPRES . '.cat_TipoRecurso t using(ter_id)
            JOIN cat_EstadoClc using(ecl_id)
            JOIN cat_TipoClc using(tcl_id)
            ' . $join . '
            JOIN enc_FiltroClc USING(gpo_id, ur_id, tcl_id,stc_id,mno_id)
            ' . $wf . '  AND clc_recursoFederal=2 AND clc_id!=0
            ' . $group . '
            ';

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ListadoClcs';
        $nav->nfDefault = 100;
        $this->wrLlenaTabla($nav);
    }

    private function DatosClc()
    {
        $filtro = '';
        if (isset($this->prm->dep_id) and $this->prm->dep_id != '') {
            switch ($this->prm->dep_id) {
                case 44:
                    $filtro = ' where tcl_id in("A","B")';
                    break;
                case 45:
                    $filtro = ' where tcl_id in("A","B")';
                    break;
                case 46:
                    $filtro = ' where tcl_id in("D") ';
                    break;
            }
        }

        $combo = new stdClass();
        $combo->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc ' . $filtro . '';
        $combo->id = 'tcl_id';
        $combo->detonar = false;
        $combo->mensaje = false;
        $this->recargaCombo($combo);
    }

    private function ListaRecepcion()
    {
        $html = '';
        $dep_id = ($_SESSION[_S]['usr_id'] == 118 ? '18,17' : intval($_SESSION[_S]['uni_id']));

        $depto = new stdClass();
        $depto->id = 'depto';
        $depto->class = 'limpiar';
        $depto->sql = 'select dep_id,dep_nombre from cat_Departamento where uni_id IN (' . $dep_id . ')';
        $depto->label = 'DEPARTAMENTO';
        $depto->form = 'enviaPost';
        $depto->enRow = 1;
        $depto = $this->ponCombo($depto);

        $estado = new stdClass();
        $estado->id = 'ecl_id';
        $estado->class = 'limpiar';
        $estado->form = 'enviaPost';
        $estado->sql = '
            SELECT ecl_id,
            CASE ecl_id
                WHEN 1 THEN "Rechazadas"
                WHEN 5 THEN "Revisadas"
                WHEN 6 THEN "Aplicadas"
            END
            ecl_nombre
            FROM cat_EstadoClc
            WHERE ecl_id in(1,5,6)
        ';
        $estado->label = 'ESTADO CLC';
        $estado->enRow = 1;
        $estado = $this->ponCombo($estado);

        $Fechainicio = new stdClass();
        $Fechainicio->id = 'Fechainicio';
        $Fechainicio->name = 'Fechainicio';
        $Fechainicio->label = 'PERÍODO INICIAL';
        $Fechainicio->class = 'datepicker limpiar';
        $Fechainicio->enDiv = true;
        $Fechainicio->formato = 'fecha';
        $Fechainicio->form = 'enviaPost';
        $Fechainicio->placeholder = date('Y-d-m');
        $scriptFecha = $this->getScript();

        $Fechafinal = new stdClass();
        $Fechafinal->id = 'Fechafinal';
        $Fechafinal->name = 'Fechafinal';
        $Fechafinal->label = 'PERÍODO FINAL';
        $Fechafinal->class = 'datepicker limpiar';
        $Fechafinal->enDiv = true;
        $Fechafinal->formato = 'fecha';
        $Fechafinal->form = 'enviaPost';
        $Fechafinal->placeholder = date('Y-d-m');
        $scriptFecha = $this->getScript();

        $recurso = new stdClass();
        $recurso->id = 'recurso';
        $recurso->class = 'limpiar';
        $recurso->form = 'enviaPost';
        $recurso->sql = 'SELECT ter_id,ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso;';
        $recurso->label = 'SELECCIONA EL TIPO DE RECURSO';
        $recurso->enRow = 1;
        $recurso = $this->ponCombo($recurso);
        $filtro = '';

        switch ($_SESSION[_S]['dep_id']) {
            case 44: //DEPARTAMENTO EDNA
                $filtro = ' where tcl_id in("A","C","B","D","E","I","J","K","L","M"); ';
                $stClc = 'WHERE tcl_id="C" AND stc_id in (01,02,05);';
                break;
            case 45: //DEPARTAMENTO HECTOR
                $filtro = ' where tcl_id in("A","C","B","E","D"); ';
                $stClc = 'WHERE tcl_id="C" AND stc_id in (01,02);';
                break;
            case 46: //DEPARTAMENTO ANDREA
                $filtro = ' where tcl_id in("D","F","G","H","I"); ';
                $stClc = 'WHERE tcl_id="C" AND stc_id in (03,04);';
                break;
        }
        $combo = new stdClass();
        $combo->sql = 'SELECT tcl_id,tcl_nombre FROM cat_TipoClc ' . $filtro . '';
        $combo->label = 'SELECCIONA EL TIPO DE CLC';
        $combo->id = 'tcl_id';
        $combo->name = 'tcl_id[]';
        $combo->form = 'enviaPost';
        $combo->mensaje = false;
        $combo->enDiv = true;
        $combo->detonar = false;
        $combo->multiple = 'multiple';
        $comboClc = $this->ponCombo($combo);

        $stClc = '';
        $combo = new stdClass();
        $combo->sql = 'SELECT stc_id,stc_nombre FROM cat_SubtipoClc ' . $stClc . '';
        $combo->label = 'SELECCIONA EL SUBTIPO DE CLC';
        $combo->id = 'stc_id';
        $combo->name = 'stc_id[]';
        $combo->form = 'enviaPost';
        $combo->mensaje = false;
        $combo->enDiv = true;
        $combo->detonar = false;
        $combo->multiple = 'multiple';

        $bxClc = new stdClass();
        $bxClc->id = 'bxClc';
        $bxClc->name = 'bxClc';
        $bxClc->label = 'NÚMERO DE CLC';
        $bxClc->class = 'limpiar';
        $bxClc->placeholder = 'Ingresa el número de CLC';
        $bxClc->form = 'enviaPost';

        $bxClc->enDiv = true;

        $bxUr = new stdClass();
        $bxUr->id = 'bxUr';
        $bxUr->name = 'bxUr';
        $this->prm->gpo_id = '';
        if ($this->prm->gpo_id != '' && $this->prm->ur_id != '') {
            $this->prm->ur_id = str_pad($this->prm->ur_id, 2, '0', STR_PAD_LEFT);
            $bxUr->value = $this->prm->gpo_id . $this->prm->ur_id;
        }
        $bxUr->label = 'UR';
        $bxUr->class = 'limpiar';
        $bxUr->placeholder = 'INGRESE LA UR';
        $bxUr->form = 'enviaPost';
        $bxUr->enDiv = true;

        $btnConsulta = new stdClass();
        $btnConsulta->id = 'btnConsulta';
        $btnConsulta->class = 'me-1';
        $btnConsulta->value = 'Buscar';
        $btnConsulta->text = 'Buscar';
        $btnConsulta->color = 'gris';
        $btnConsulta->icon = 'search me-2';

        $lista = new stdClass();
        $lista->id = 'lista';
        $lista->type = 'submit';
        $lista->color = 'rojo';
        $lista->class = 'me-1';
        $lista->title = 'Exporta los registros a PDF';
        $lista->icon = 'solid fa-file-pdf';

        $exportar = new stdClass();
        $exportar->id = 'exportar';
        $exportar->type = 'submit';
        $exportar->class = 'me-1';
        $exportar->color = 'verde';
        $exportar->title = 'Exporta los registros a EXCEL';
        $exportar->icon = 'solid fa-file-excel';

        $clean = new stdClass();
        $clean->value = 'Limpiar';
        $clean->color = 'cielo';
        $clean->class = 'me-1';
        $clean->id = 'clean';
        $clean->icon = 'refresh me-2';

        $html = '
            <div class="row mb-2">
                <fieldset class="col-md-4 col-xs-4" >
                ' . $recurso . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4" >
                ' . $estado . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" >
                ' . $this->ponInput($Fechainicio) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" >
                ' . $this->ponInput($Fechafinal) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-8 col-xs-8">
                ' . $depto . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxUr) . '
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    ' . $this->ponInput($bxClc) . '
                </fieldset>
            </div>
            <div class="row mb-2">
                <fieldset class="col-md-12 col-xs-12">
                ' . $comboClc . '
                </fieldset>
            </div>
            <div class="row mb-4">
                <fieldset class="col-md-4">
                    <input form= "enviaPost" type="checkbox" style="color:#0431B4; margin-right:7px;" name="check1" id="myCheck">Seleccionar todos los registros</input>
                </fieldset>
            </div>
            <div>
                <fieldset class="col-md-12" style="background-color:#F2F5A9;height:20px;display:none" id="error">
                    <label for="error" style="font-size:medium;">Todos los registros han sido seleccionados, al exportar el proceso tardará...
                    </label>
                </fieldset>
            </div>
        ';

        $scriptFecha = $this->getScript();
        $scriptFecha2 = $this->getScript();

        $tabla = new stdClass();

        $tabla->id = 'tbl-ListadoClcs';
        $tabla->tools = $html;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->Fechainicio = '{$("#Fechainicio").val()}';
        $tabla->comunica->Fechafinal = '{$("#Fechafinal").val()}';
        $tabla->comunica->estado = '{$("#ecl_id").val()}';
        $tabla->comunica->metodo = 'llenaListaRecepcion';
        $tabla->comunica->bxUr = '{$("#bxUr").val()}';
        $tabla->comunica->bxClc = '{$("#bxClc").val()}';
        $tabla->comunica->depto = '{$("#depto").val()}';
        $tabla->comunica->recurso = '{$("#recurso").val()}';
        $tabla->comunica->tipo = '{$("#tcl_id").val()}';
        $tabla->numFilas = 150;

        $tr = [];

        $th = new stdClass();
        $th->texto = 'SEL.';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'UR';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA ENVIO ';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLC';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLABE';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CERE(CO)';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'ESTADO';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PERCEPCION';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RETENCION';
        $th->align = 'center';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->align = 'center';
        array_push($tr, $th);

        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->tipo = 'check';
        $td->name = 'micheck';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->class = 'clcagre clcsepa seleccionar';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_fechaEnvio';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cereco';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc_percepcion';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';
        $td = new stdClass();
        $td->campoTxt = 'clc_retencion';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';
        $td = new stdClass();
        $td->campoTxt = 'importe';
        array_push($tabla->cuerpo, $td);
        $td->formato = 'moneda';

        $tabla->encabezado = [$tr];
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->recargaCombo($depto);

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->text = 'Regresar';

        $this->mixScript('
                $("#btnRegresarIndex").click (function(){
                    comunica("' . $this->pantalla . '",{metodo:"main"});
                    return false;
                });
            ');
        if (!isset($scriptFecha)) {
            $scriptFecha = '';
        }

        if (!isset($scriptFecha2)) {
            $scriptFecha2 = '';
        }

        $this->mixScript($scriptFecha . $scriptFecha2 . '
                var datos = new Array();
                $(".seleccionar").attr("name","arreglo[]").attr("form","enviaPost"); //Se crea un formulario de los registros seleccionados para exportar a excel

                $("#btnConsulta").click(function(){
                    //alert("Cuando seleccionas periodos puedes imprimir reportes de CLC S Revisadas o Aplicadas   \n Los filtros Departamento, Tipo de Recurso,Estad oCLC y Tipo de CLC son obligatorios");
                    if ($("#depto").val()=="") {
                        notify("Selecciona el Departamento","error");
                        return false;
                    } else if ($("#ecl_id").val()=="") {
                        notify("Selecciona el estado de la CLC","error");
                        return false;
                    } else if ($("#tcl_id").val()=="" || $("#tcl_id").val()==null) {
                        notify("Selecciona almenos un tipo de CLC","error");
                        return false;
                    } else {
                        ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . '
                    }

                });

                $("#clean").click(function(){
                    $(".limpiar").val("");
                    $("#recurso").val("");
                    $("#recurso").change();
                    $("#tcl_id").val("");
                    $("#tcl_id").change();
                    $("#ecl_id").val("");
                    $("#ecl_id").change();
                    comunica("' . $this->pantalla . '",{
                        metodo:"ListaRecepcion",
                    });
                    return false;
                });

                //combo donde indico que se han seleccionado todos los registros
                $("#myCheck").click(function(){
                    if ($("#myCheck").is(":checked")) {
                        $(".seleccionar").each(function() {
                            $("#error").css("display","block")
                            $(this).removeAttr("checked");
                        });
                    }
                    else {
                        $("#error").css("display","none")
                    }
                });

                //imprime en pdf
                $("#lista").click(function(){
                    if ($("#myCheck").is(":checked")) {
                        var todos = true;
                        $(".seleccionar:checked").each(function() {
                            datos.push({
                                "clc":$(this).attr("clc_id"),
                                "gpo_id":$(this).attr("gpo_id"),
                                "ur_id":$(this).attr("ur_id")
                            });
                            $(this).val($(this).attr("clc_id")+","+
                                $(this).attr("gpo_id")+$(this).attr("ur_id")
                            );
                        });
                    } else {
                        var checados = false;
                        $(".seleccionar:checked").each(function() {
                            if ($(".seleccionar").is(":checked")) {
                                datos.push({
                                    "clc":$(this).attr("clc_id"),
                                    "gpo_id":$(this).attr("gpo_id"),
                                    "ur_id":$(this).attr("ur_id")
                                });
                                $(this).val($(this).attr("clc_id")+","+
                                    $(this).attr("gpo_id")+$(this).attr("ur_id")
                                );
                                checados = true;
                            }
                        });

                    }

                    if (todos == true || checados == true) {
                        if ($(".seleccionar:checked").length > 10 || todos == true) {
                            var form = $("<form>")
                            .attr("action", "rep/doc/scp/afectaciones/rep_ClcAplicadas.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "enviaPost");
                            $("body").append(form);
                            form.submit();
                            form.remove();
                        } else {
                            var form = $("<form>")
                            .attr("action", "rep/doc/scp/afectaciones/rep_ClcAplicadas.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .attr("id", "enviaPost");
                            $("body").append(form);
                            form.submit();
                            form.remove();
                        }
                    } else {
                        notify("Debe seleccionar por lo menos un registro","warning");
                    }

                });

                //para exportar en excel
                $("#exportar").click(function(){
                    if ($("#myCheck").is(":checked")) {
                        var todos = true;
                        $(".seleccionar:checked").each(function() {
                            datos.push({
                                "clc":$(this).attr("clc_id"),
                                "gpo_id":$(this).attr("gpo_id"),
                                "ur_id":$(this).attr("ur_id")
                            });
                            $(this).val($(this).attr("clc_id")+","+
                                $(this).attr("gpo_id")+$(this).attr("ur_id")
                            );
                        });
                    } else {
                        var checados = false;
                        $(".seleccionar:checked").each(function() {
                            if ($(".seleccionar").is(":checked")) {
                                datos.push({
                                    "clc":$(this).attr("clc_id"),
                                    "gpo_id":$(this).attr("gpo_id"),
                                    "ur_id":$(this).attr("ur_id")
                                });
                                $(this).val($(this).attr("clc_id")+","+
                                    $(this).attr("gpo_id")+$(this).attr("ur_id")
                                );
                                checados = true;
                            }
                        });
                    }
                    //se envian los datos para exportar a excel ya sean todos o los seleccionados
                    if (todos == true || checados == true) {
                        if ($(".seleccionar:checked").length > 10 || todos == true) {
                            var form = $("<form>")
                                .attr("action", "rep/excel/scp/afectaciones/rep_ListadoClc.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .attr("id", "enviaPost");
                                $("body").append(form);
                                form.submit();
                                form.remove();
                        } else {
                            var form = $("<form>")
                                .attr("action", "rep/excel/scp/afectaciones/rep_ListadoClc.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .attr("id", "enviaPost");
                                $("body").append(form);
                                form.submit();
                                form.remove();
                        }
                    } else {
                        notify("Debe seleccionar por lo menos un registro");
                    }
                });
            ');

        $ListadoRecibidas = new stdClass();
        $ListadoRecibidas->id = 'ListadoRecibidas';
        $ListadoRecibidas->color = 'gris';
        $ListadoRecibidas->value = "CLC'S de Ventanila";
        $ListadoRecibidas->icon = 'solid fa-file-lines me-2';
        $ListadoRecibidas->click = '
            if ($("#tcl_id").val()!="" && $("#tcl_id").val()!=null) {
                var form = $("<form>")
                .attr("action", "rep/excel/scp/afectaciones/rep_ClcsRecibidasDepto.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .attr("id", "enviaPost");
                $("body").append(form);
                form.submit();
                form.remove();
            } else {
                notify("Elije el tipo de CLC","warning");
                return false;
            }
        ';
        $ListadoRecibidas = $this->ponBoton($ListadoRecibidas);

        $this->mixScript(' $("#buscar").click(function(event){ ' . $this->wrLlamaLlenaTabla('tbl-ListadoClcs') . ' }); ');
        $this->agregaTools(' ' . $this->ponBoton($btnRegresarIndex) . ' ' . $this->ponBoton($btnConsulta) . ' ' . $this->ponBoton($clean) . '  ' . $this->ponBoton($lista) . ' ' . $this->ponBoton($exportar) . ' ' . $ListadoRecibidas);
    }

    private function llenaListaRecepcion()
    {
        $select = $wf = '';
        $this->conectar();
        $tipo = isset($this->prm->tipo) ? $this->prm->tipo : '';
        $depto = isset($this->prm->depto) ? $this->prm->depto : '';
        $recurso = isset($this->prm->recurso) ? $this->prm->recurso : '';
        $gpo_ur = isset($this->prm->bxUr) ? $this->prm->bxUr : '';
        $clc_id = isset($this->prm->bxClc) ? $this->prm->bxClc : '';
        $bitacora = $in = '';

        switch ($_SESSION[_S]['dep_id']) {
            case 44: //DEPARTAMENTO EDNA
                $stClc = ' tcl_id="C" AND stc_id in (01,02,05)';
                break;
            case 45: //DEPARTAMENTO HECTOR
                $stClc = ' tcl_id="C" AND stc_id in (01,02)';
                break;
            case 46: //DEPARTAMENTO ANDREA
                $stClc = ' tcl_id="C" AND stc_id in (03,04)';
                break;
        }

        if (count($tipo) > 0 && $tipo != '') {
            foreach ($tipo as $key => $val) {
                $in .= '"' . $val . '",';
            }
            $in = trim($in, ',');
            if ($in != '') {
                if ($in == 'C') {
                    $wf .= ($wf == '') ? 'where' . $stClc : ' and ' . $stClc;
                } else {
                    $wf .= ($wf == '') ? 'where tcl_id in (' . $in . ') ' : ' and tcl_id in (' . $in . ') ';
                }
            }
        }
        $extra = '';
        if ($depto != '') {
            if ($depto == 45) {
                $extra = 'OR (dep_id=49 and gpo_id=4 and ur_id=31 and a.ecl_id=6) ';
            }
            $wf .= ($wf == '') ? 'where dep_id = ' . $depto . ' ' : ' and dep_id = ' . $depto . ' ';
        }

        if ($recurso != '') {
            $wf .= ($wf == '') ? 'where ter_id = "' . $recurso . '" ' : ' and ter_id = "' . $recurso . '" ';
        }

        if ($gpo_ur != '') {
            $wf .= ($wf == '') ? 'where concat(gpo_id,ur_id) = ' . $gpo_ur . ' ' : ' and concat(gpo_id,ur_id) = ' . $gpo_ur . ' ';
        }

        if ($clc_id != '') {
            $wf .= ($wf == '') ? 'where clc_id = ' . $clc_id . ' ' : ' and clc_id = ' . $clc_id . ' ';
        }

        $this->prm->Fechainicio = $this->date_to_mysqldate($this->prm->Fechainicio);
        $this->prm->Fechafinal = $this->date_to_mysqldate($this->prm->Fechafinal);

        if ($this->prm->estado == 5) {
            $estado = ' a.ecl_id = 5 ';
        } elseif ($this->prm->estado == 6) {
            $estado = ' a.ecl_id = 6 ';
        } else {
            $estado = ' a.ecl_id = 1 ';
            $bitacora = ' AND bit_observacion = "CLC RECHAZADA"';
        }

        $select = 'DATE_FORMAT(FechaProceso,"%d/%m/%Y") FechaProceso,aplicadas,';
        $join = '
            JOIN (
            SELECT gpo_id,ur_id,ecl_id,usr_fecha as FechaProceso,
            clc_id as aplicadas,clc_id
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE ecl_id=' . $this->prm->estado . $bitacora . '
            group by gpo_id,ur_id,clc_id,usr_fecha
            order by gpo_id,ur_id,usr_fecha
            )b using(gpo_id,ur_id,clc_id)
        ';

        if ($this->prm->Fechainicio != '' && $this->prm->Fechainicio != 0 && $this->prm->Fechainicio != 'NULL'
            && $this->prm->Fechafinal != '' && $this->prm->Fechafinal != 0 && $this->prm->Fechafinal != 'NULL') {
            if ($this->prm->Fechainicio == $this->prm->Fechafinal) {
                $periodo = ' DATE(FechaProceso) = "' . $this->prm->Fechainicio . '"';
            } else {
                $periodo = ' ( DATE(FechaProceso) BETWEEN "' . $this->prm->Fechainicio . '" AND "' . $this->prm->Fechafinal . '" ) ';
            }

            $wf .= ($wf == '') ? 'where ' . $periodo . ' ' : '  and ' . $periodo . ' ';
        } else {
            $periodo = ' DATE(FechaProceso) = "' . date('Y-m-d') . '"';
            $wf .= ($wf == '') ? 'where ' . $periodo . ' ' : '  and ' . $periodo . ' ';
        }

        if ($this->prm->estado != '') {
            $wf .= ($wf == '') ? 'where ' . $estado . ' ' : ' and ' . $estado . ' ';
        }

        $group = '
            GROUP by gpo_id,ur_id,clc_id,bur_id,tcl_id,tcl_nombre,ecl_id,ecl_nombre,aplicadas,
            mpc_nombre,FechaProceso,clc_percepcion,clc_retencion,bur_id
            ORDER by gpo_id,ur_id,clc_id,FechaProceso
            ';

        $wf .= ($wf == '') ? ' where clc_id!=0 ' : ' and clc_id!=0   ' . $extra . ' ';

        $sql = '
            SELECT DISTINCT gpo_id,ur_id,a.clc_id,
            if(clc_tipocereCo=1 ,"CERE",if( clc_tipocereCo=2, "CERECO","SIN CERE(CO)"))cereco, tcl_id,tcl_nombre,
            b.ecl_id,ecl_nombre,' . $select . '
            TRUNCATE(clc_percepcion,2)-TRUNCATE(clc_retencion,2) AS importe,
            clc_concepto,DATE_FORMAT(clc_fechaEnvio,"%d/%m/%Y") clc_fechaEnvio,mpc_nombre,clc_percepcion,clc_retencion,bur_id
            FROM scp_Clc a
            JOIN cat_UnidadResponsable using(gpo_id,ur_id)
            JOIN enc_ClaveFinanciamiento using(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING( cff_id,fte_id, sft_id, ori_id, sor_id )
            JOIN ' . BD_SINPRES . '.cat_TipoRecurso t using(ter_id)
            JOIN cat_EstadoClc using(ecl_id)
            JOIN cat_ModoPagoClc using(mpc_id)
            JOIN cat_TipoClc using(tcl_id)
            ' . $join . '
            JOIN enc_FiltroClc USING(gpo_id, ur_id, tcl_id,stc_id)
            ' . $wf . '
            ' . $group . '
        ';
        $this->setSql($sql);
        $this->consultar();
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-ListadoClcs';
        $nav->nfDefault = 150;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No hay registros","warning");
            ');
        } else {
            $this->mixScript('
                $("#loading").hide();

                $(".seleccionar").attr("name","datos[]").attr("form","enviaPost");

                //si se encuentra seleccionada la opcion de seleccioar todos los registros y selecciona un registro de la tabla
                //se desmarcara la opcion.
                $(".seleccionar").click(function(){
                $("#myCheck").removeAttr("checked");
                });
                ');
        }
    }

    private function comboNomina()
    {
        $ur = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];
        $wh = '';
        if ($ur != 104) {
            $wh = 'AND mno_id!=6';
        } else {
            $wh = '';
        }
        $combo = new stdClass();
        switch ($this->prm->id) {
            case 1:
                $combo->sql = 'SELECT smn_id,smn_nombre FROM cat_SubmodalidadNomina where mno_id=' . $this->prm->mno_id . '';
                break;
            case 2:
                $combo->sql = '
                    SELECT mno_id,mno_nombre
                    FROM cat_ModalidadNomina
                    JOIN enc_ModalidadSubtipoClc USING(mno_id)
                    WHERE stc_id = ' . ((empty($this->prm->stc_id)) ? 0 : $this->prm->stc_id) . ' ' . $wh . '
                    ';
                break;
        }
        $combo->comoAtr = 'mno_id';
        $combo->id = $this->prm->combo;
        $this->recargaCombo($combo);

        $this->modalidadPago();
    }

    private function modalidadPago()
    {
        $this->prm->ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : '';
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $this->setContenedor('modPago_tipoCta');

        $ur = $this->ur;
        $clc = new Clc();
        if (!empty($this->prm->clc_id)) {
            $clc->setGpo_id($this->prm->gpo_id);
            $clc->setUr_id($this->prm->ur_id);
            $clc->setClc_id($this->prm->clc_id);
            $clc->select();
            $abc = 'c';
        } else {
            $abc = 'a';
        }

        /* ESTE BOTON ELIGE LA MODALIDAD DE PAGO DE ACUERDO AL TIPO DE CLC LA CONSULTA YA DEVUELVE PARA QUE UR'S ESTA LIBERADO LO DE CADENA PRODUCTIVA */
        $adultos = $this->gpo_id . $this->ur_id;
        $mpc_id = (isset($this->prm->stc_id) && ($this->prm->stc_id != 6) && ($this->prm->tipo == 'A')) ? ' and mpc_id!=2' : ''; // si es el subtipo 6 impuesto 2% sobre nomina no se muestra el tipo de pago de linea de captura
        $directa = (isset($this->prm->tipo) && ($this->prm->tipo == 'G') && ($adultos != '530')) ? ' and mpc_id!=2' : '';
        $modpago = new StdClass();
        $muns = '';
        if ($adultos == '902' && $this->prm->stc_id == '04' && $this->prm->tipo == 'I') {
            $muns = ' AND mpc_id IN(1,3)';
        }
        $cobroCoactivo = '';
        if ($this->prm->tipo == 'M' && $this->prm->stc_id == 16) {
            $cobroCoactivo = ' AND mpc_id IN(5)';
        }

        $modpago->class = 'radio';
        $modpago->name = 'modP';
        $modpago->sql = '
            SELECT mpc_id,mpc_nombre
            FROM cat_ModoPagoClc
            WHERE mpc_id
            IN(
            SELECT IF(ur_cadenaProductiva=1,mpc_id,IF(mpc_id=4,0,mpc_id ) )mpc_id
            FROM enc_FiltroClc
            JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
            JOIN enc_TipoClcPago USING(tcl_id)
            where gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id= ' . $_SESSION[_S]['ur_id'] . '
            and tcl_id="' . $this->prm->tipo . '" ' . $mpc_id . ' ' . $directa . ' ' . $muns . ' ' . $cobroCoactivo . '
            );
            ';

        $modpago->cols = 3;
        $modpago->required = true;
        if ($cobroCoactivo) {
            $modpago->selecciona = 5;
        } else {
            $modpago->selecciona = (($clc->getMpc_id() != '')) ? $clc->getMpc_id() : 1;
        }

        // El tipo de pago de SPEI  o CHEQUE se habilita para todas los tipos y Subtipos de CLC con el modo de pago tradicional
        $tipo_Pago = new StdClass();
        $tipo_Pago->class = 'radio';
        $tipo_Pago->name = 'tipo_Pago';
        $tipo_Pago->values = [1 => 'SPEI', 2 => 'CHEQUE'];
        $tipo_Pago->cols = 3;
        $tipo_Pago->required = true;
        if ($clc->getFormaPago() != '') {
            $tipo_Pago->selecciona = (($clc->getFormaPago() == 'S') ? 1 : 2);
        } else {
            $tipo_Pago->selecciona = 1;
        }
        $tipo_Pago = $this->ponRadio($tipo_Pago);

        $this->mixHtml(
            '
            <legend align="center" style="color:#FF4000">Modalidad de Pago</legend>
                <div class="row">
                    <fieldset class="col-md-12 habilitar">
                    ' . $this->ponRadio($modpago) . '
                    </fieldset>

                    <fieldset class="col-md-12 habilitar" id="cheque">
                    ' . $tipo_Pago . '
                    </fieldset>
                </div>'
        );

        $this->tipoCuenta();

        $filt = '';
        $scriptSTC = ''; //Muestra Claves de Financiamiento de acuerdo al bloqueo de Egresos
        if ((($this->prm->tipo != 'B' && $this->prm->tipo != 'F' && $this->prm->tipo != 'G' && $this->prm->tipo != 'H') && ($abc == 'a' || (isset($this->prm->cambio) && $this->prm->cambio == true)) && $this->prm->stc_id != '')) {
            //sel e agrego todas las claves de financiamiento
            switch ($this->prm->tipo) {
                case 'A':
                case 'I':
                    $filt = 'presupuesto';
                    break;
                case 'C':
                    $filt = 'PagosATerceros';
                    break;
                case 'D':
                    $filt = 'ministracion';
                    break;
                case 'E':
                case 'M':
                case 'J':
                    $filt = 'general';
                    break;
                case 'K':
                case 'L':
                    $filt = 'presupuestoUe';
                    break;
            }

            if ($this->prm->tipo != 'I' || $this->prm->stc_id != '04') {
                $scriptSTC = '
                    comunica("srv/ClasificacionFinanciamiento.php",{
                        metodoCveFin:"cargaComboCveFin",
                        tipo:"' . $this->prm->tipo . '",
                        stc_id:"' . $this->prm->stc_id . '",
                        filtro:"' . $filt . '",
                        ecf_id:"' . $clc->getEcf_id() . '",
                        tarea:"verificar"
                    });
                ';
            }
        }

        $valor = $financiera = '';
        switch ($adultos) {
            case '602':
            case '603':
                switch ($this->prm->tipo) {
                    case 'F':
                    case 'G':
                    case 'H':
                        $valor = (($abc == 'a') ? 'NACIONAL FINANCIERA S.N.C.' : $clc->getBeneficiario());
                        $financiera = '$("#beneur").val("' . str_replace('"', '', $valor) . '");';
                        break;
                    case 'I':
                        $valor = (($abc == 'a') ? ' ' : $clc->getBeneficiario());
                        $financiera = '$("#beneur").val("' . str_replace('"', '', $valor) . '");';
                        break;
                    default:
                        $financiera = '$("#beneur").val("' . str_replace('"', '', $ur->getNombre()) . '");';
                        break;
                }
                break;
            default:
                $financiera = '$("#benecap").val("' . str_replace('"', '', $clc->getBeneficiario() ?? '') . '");';
                break;
        }
        if (isset($this->prm->tipo) && $this->prm->tipo == 'I') {
            // si es tradicional, spei, y propia se bloquea bloquea el nombre del beneficiario.
            $this->mixScript('
                $("input[name=tipoC],input[name=modP],input[name=tipo_Pago]").change(function () {
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        $("#municipio").attr("readonly","readonly");
                    } else {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").removeAttr("readonly");
                        }
                    }
                });

                $(document).ready(function (){
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").attr("readonly","readonly");
                        }
                    }
                });
            ');
        }
        if (isset($this->prm->tipo) && $this->prm->tipo == 'I') {
            // si es tradicional, spei, y propia se bloquea bloquea el nombre del beneficiario.
            $this->mixScript('
                $("input[name=tipoC],input[name=modP],input[name=tipo_Pago]").change(function () {
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        $("#municipio").attr("readonly","readonly");
                    } else {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").removeAttr("readonly");
                        }
                    }
                });

                $(document).ready(function (){
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").attr("readonly","readonly");
                        }
                }});
            ');
        }

        $this->mixScript('

            $("#hidelinea").hide();
            $("#hidecadena").hide();

            function tipoCta(){
                comunica("' . $this->pantalla . '",{
                    metodo:"TipoFiltroCuenta",
                    ucb_id:$("input[name=tipoC]:checked").val(),
                    mpc_id:$("input[name=modP]:checked").val(),
                    tipo_Pago:$("input[name=tipo_Pago]:checked").val(),
                    stc_id:"' . $this->prm->stc_id . '",
                    tipo:"' . $this->prm->tipo . '",
                    abc:"' . $abc . '"' . (!empty($clc->getBur_id()) ? ', bur_id: "' . $clc->getBur_id() . '"' : '') . '
                });
            }

            function TipoPago(){
                switch ($("input[name=modP]:checked").val()) {
                    case "1":
                        $("#hidelinea").hide();
                        $("#hidecadena").hide();
                        $("#hidecadena fieldset *:required").attr("disabled","disabled").removeAttr("required");
                        $("#cheque").show();
                        $("#cheque *:disabled").attr("required","required").removeAttr("disabled");
                        ' . $financiera . '
                        $("input[name=tipo_Pago]").first().click();
                        $("input[name=tipoC]").removeAttr("disabled", "true");
                        $("input[name=tipoC]").removeAttr("disabled", "true");
                        if ( ( "' . $this->prm->tipo . '" =="K" )) {
                            $("#divbenebur").show();
                            $("#divbeneburpdp").hide();
                            $("#ben_nombre").attr("required","required");
                        }
                        if ( ( "' . $this->prm->tipo . '" =="I" )) {

                            $("#municipio").attr("readonly",false);
                        }
                        break;
                    case "2":
                        if (($("#stc_id").find("option:selected").val()==6) || ( "' . $this->prm->tipo . '" =="B" )) {
                            $("#hidelinea").hide();
                            $("#benecap").val("GOBIERNO DEL ESTADO DE OAXACA");
                            $("#beneur").val("GOBIERNO DEL ESTADO DE OAXACA");
                            $("#benecap").attr("readonly",true);
                            $("#beneur").attr("readonly",true);
                        } else {
                            $("#hidelinea").show();
                            $("#hidelinea fieldset *:disabled").attr("required","required").removeAttr("disabled");
                        }
                        $("#hidecadena").hide();
                        $("#hidecadena fieldset *:required").attr("disabled","disabled").removeAttr("required");
                        $("#cheque").hide();
                        $("#cheque *:required").attr("disabled","disabled").removeAttr("required");
                        $("input[name=tipo_Pago]:checked").removeAttr("checked");
                        $("input[name=tipoC]").removeAttr("disabled", "true");
                        $("input[name=tipoC]").removeAttr("disabled", "true");
                        if ( ( "' . $this->prm->tipo . '" =="K" )) {
                            $("#divbenebur").show();
                            $("#divbeneburpdp").hide();
                            $("#ben_nombre").attr("required","required");
                        }
                        if ( ( "' . $this->prm->tipo . '" =="I" )) {

                            $("#municipio").attr("readonly",false);
                        }
                        break;
                    case "3":
                        $("#hidelinea").hide();
                        $("#hidecadena").hide();
                        $("#hidecadena fieldset *:required").attr("disabled","disabled").removeAttr("required");
                        $("#cheque").hide();
                        $("#cheque *:required").attr("disabled","disabled").removeAttr("required");
                        $("#benecap").attr("readonly",false);
                        $("#beneur").val("' . str_replace('"', '', $ur->getNombre()) . '");
                        $("input[name=tipo_Pago]:checked").removeAttr("checked");
                        $("input[name=tipoC]").removeAttr("disabled", "true");
                        if ( ( "' . $this->prm->tipo . '" =="K" )) {
                            $("#divbenebur").show();
                            $("#divbeneburpdp").hide();
                            $("#ben_nombre").attr("required","required");
                        }
                        if ( ( "' . $this->prm->tipo . '" =="I" )) {

                            $("#municipio").attr("readonly",false);
                        }
                        break;
                    case "4":
                    case "5":
                        $("#hidelinea").hide();
                        $("#hidecadena").hide();
                        $("#hidecadena fieldset *:required").attr("disabled","disabled").removeAttr("required");
                        $("#cheque").show();
                        $("#cheque *:disabled").attr("required","required").removeAttr("disabled");
                        ' . $financiera . '
                        $("#benecap").attr("readonly","readonly");
                        $("#beneur").attr("readonly","readonly");
                        $("input[name=tipo_Pago]").first().prop("checked", true);
                        $("input[name=tipoC][value=4]").prop("checked", true);
                        $("input[name=tipoC]").attr("disabled", "true");
                        $("input[name=tipo_Pago]").attr("disabled", "true");
                        if ( ( "' . $this->prm->tipo . '" =="K" )) {
                            $("#ben_nombre").removeAttr("required");
                            $("#divbenebur").hide();
                            $("#divbeneburpdp").show();
                        }
                        if ( ( "' . $this->prm->tipo . '" =="I" )) {

                            $("#beneficiarioP").attr("readonly","readonly");
                            $("#municipio").attr("readonly","readonly");
                        }
                        break;
                }
            }

            ' . $scriptSTC . '
            $("input[name=modP]").click(function(){
                TipoPago();
                tipoCta();
            });

            ' . (($abc == 'a') ? '$("input[name=modP]:checked").click();' : '') . '

            $("input[name=tipo_Pago]").click(function(){
                tipoCta();
            });

            ' . (($abc == 'c' && $this->prm->tipo == 'I' && $this->prm->stc_id == 01) ? '
            $("input[name=tipo_Pago]").each(function(){
                    if($(this).is(":checked")){
                        tipoCta();
                    }

            });' : '') . '

        ');
    }

    private function tipoCuenta()
    {
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';

        $clc = new Clc();
        if (!empty($this->prm->clc_id)) {
            $clc->setGpo_id($this->prm->gpo_id);
            $clc->setUr_id($this->prm->ur_id);
            $clc->setClc_id($this->prm->clc_id);
            $clc->select();
            $abc = 'c';
        } else {
            $abc = 'a';
        }

        $tipoCuenta = new StdClass();
        $tipoCuenta->class = 'radio habilitar';
        $tipoCuenta->id = 'tipoC';
        $tipoCuenta->name = 'tipoC';
        $tipoCuenta->cols = 2;
        $tipoCuenta->required = true;
        switch ($this->prm->tipo) {
            case 'D':
                if ($clc->getUcb_id() != '') {
                    $tipoCuenta->selecciona = $clc->getUcb_id();
                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria where ucb_id=1';
                } else {
                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria where ucb_id=1';
                    $tipoCuenta->selecciona = '1';
                }
                break;
            case 'E':
                if ($this->prm->stc_id == 01) {
                    $fil = ' WHERE ucb_id in(1,2,3)';
                } else {
                    $fil = ' WHERE ucb_id=1 ';
                }

                if ($clc->getUcb_id() != '') {

                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria ' . $fil . ' ';
                    $tipoCuenta->selecciona = $clc->getUcb_id();
                } else {

                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria ' . $fil . '';
                    $tipoCuenta->selecciona = '1';
                }
                break;
            default:

                if ($clc->getUcb_id() != '') {
                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria where ucb_id in (1,2,3,4)';
                    $tipoCuenta->selecciona = $clc->getUcb_id();
                } else {
                    $tipoCuenta->sql = 'select ucb_id, ucb_nombre from cat_UsoCuentaBancaria  where ucb_id in (1,2,3,4) ';
                    $tipoCuenta->selecciona = '1';
                }
                break;
        }

        $this->mixHtml('
            <legend align="center">Elige el filtro de la Cuenta</legend>
            <div class="row">
                <fieldset class="col-md-12 habilitar">
                    ' . $this->ponRadio($tipoCuenta) . '
                </fieldset>
            </div>
        ');

        $this->mixScript('
            $("input[name=tipoC]").click(function(){
                tipoCta();
            });
        ');
    }

    private function TipoFiltroCuenta()
    {
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        $this->prm->gpo_id = $this->gpo_id;
        $this->prm->ur_id = $this->ur_id;
        $clc = new Clc();

        if (!empty($this->prm->clc_id)) {
            $clc->setGpo_id($this->prm->gpo_id);
            $clc->setUr_id($this->prm->ur_id);
            $clc->setClc_id($this->prm->clc_id);
            $clc->select();
        }

        $banur = new stdClass();
        $ChequeSpei = (isset($this->prm->tipo_Pago)) ? $this->prm->tipo_Pago : '';
        $_SESSION[_S]['clasAdm-ue'] = (isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : null);

        /* Cuentas Bancarias que se pueden utillzar por tipo de CLC */
        $prms = [
            'tipo'       => $this->prm->tipo,
            'stc_id'     => $this->prm->stc_id,
            'ucb_id'     => $this->prm->ucb_id,
            'mpc_id'     => $this->prm->mpc_id,
            'ChequeSpei' => $ChequeSpei,
            'ue_id'      => $_SESSION[_S]['clasAdm-ue']];

        $bursql = $this->model->getSqlCuenta($prms);
        $banur->sql = $bursql;
        $banur->id = 'bur_id';
        $banur->comoAtr = 'bur_CuentaBancaria,bur_id,iba_nombre';
        $banur->mensaje = 'ELIGE LA CLABE';
        if (isset($this->prm->bur_id)) {
            $banur->selecciona = $this->prm->bur_id;
        }
        $this->recargaCombo($banur);

        //Descomentar esta seccion cuando se libere lo de pago directo de gastos de operacion
        // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
        // se agrega en la misma funcionalidad que el pago directo a proveedor ticket 638123
        if ($this->prm->mpc_id == '5' || $this->prm->mpc_id == '4') {
            $script = '   $("#benecap").attr("readonly","readonly");
                $("#beneur").attr("readonly","readonly");
                $("#beneur").val("");
                $("#inputRect").remove();
                $("#clcRect").prop("checked",false);
                $("#clcRect").attr("disabled",true);';

            if ($this->prm->tipo == 'I') {
                $script .= '$("#beneficiarioP").val("");';
            }
        } else {
            $script = ' $("#benecap").removeAttr("readonly");
                $("#beneur").removeAttr("readonly");
                $("#clcRect").removeAttr("disabled");';

            if ($this->prm->tipo == 'I' && $this->prm->stc_id != '04') {
                $script .= '$("#beneficiarioP").val("Municipios");';
            }
        }

        if (isset($this->prm->tipo) && $this->prm->tipo == 'I') {
            // si es tradicional, spei, y propia se bloquea bloquea el nombre del beneficiario.
            $this->mixScript('
                $("input[name=tipoC],input[name=modP],input[name=tipo_Pago]").change(function () {
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        $("#municipio").attr("readonly","readonly");
                    } else {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").removeAttr("readonly");
                        }
                    }
                });

                $(document).ready(function (){
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").attr("readonly","readonly");
                        }
                }});
            ');
        }

        $this->mixScript('' . $script . '');
    }

    public function addModClc()
    {
        $this->prm->clc_fechaAjuste = (isset($this->prm->clc_fechaAjuste)) ? $this->prm->clc_fechaAjuste : '';
        $this->prm->tno_id = (isset($this->prm->tno_id)) ? $this->prm->tno_id : '';
        $this->prm->mno_id = (isset($this->prm->mno_id)) ? $this->prm->mno_id : '';
        $this->prm->smn_id = (isset($this->prm->smn_id)) ? $this->prm->smn_id : '';
        $this->prm->tareaclc = (isset($this->prm->tareaclc)) ? $this->prm->tareaclc : '';
        $this->prm->ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : '';
        $this->prm->tipo = (isset($this->prm->tcl_id)) ? $this->prm->tcl_id : '';
        $this->prm->tcp_id = (isset($this->prm->tcp_id)) ? $this->prm->tcp_id : '';
        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $this->prm->obr_id = (isset($this->prm->obr_id)) ? $this->prm->obr_id : '';
        $this->prm->epg_id = ((isset($this->prm->epg_id) && $this->prm->epg_id != '') ? $this->prm->epg_id : '');
        $this->prm->mpc_id = ((isset($this->prm->mpc_id) && $this->prm->mpc_id != '') ? $this->prm->mpc_id : '');
        $this->prm->clc_rectificacion = ((isset($this->prm->clc_rectificacion) && $this->prm->clc_rectificacion != '') ? $this->prm->clc_rectificacion : 0);
        $this->prm->bur_id = ((isset($this->prm->bur_id) && $this->prm->bur_id != '') ? $this->prm->bur_id : 0);

        if ($this->prm->epg_id != '') {
            $this->prm->epg_id;
        } elseif (isset($_SESSION[_S]['clasAdm-epg']) && $_SESSION[_S]['clasAdm-epg'] != '') {
            $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
        }

        $ur = $this->ur;
        $cveFin = new stdClass();
        $cveFin->horizontal = 2;

        $bandera = false;
        $banderaGuardar = false;
        $paso = false;
        $abc = '';
        $clc = new Clc();
        $obr = new ObraClc();

        if (!empty($this->prm->clc_id)) {
            $clc->setGpo_id($this->prm->gpo_id);
            $clc->setUr_id($this->prm->ur_id);
            $clc->setClc_id($this->prm->clc_id);
            $clc->select();

            switch ($this->prm->tipo) {
                case 'A':
                case 'C':
                case 'F':
                case 'G':
                case 'H':
                case 'I':
                case 'M':
                    $this->prm->tipo_Pago = (($clc->getFormaPago() == 'S')) ? 1 : 2;
                    break;
                default:
                    $this->prm->tipo_Pago = '';
                    break;
            }

            if ($clc->getEpg_id() != '') {
                $obr->setGpo_id($this->prm->gpo_id);
                $obr->setUr_id($this->prm->ur_id);
                $obr->setClc_id($this->prm->clc_id);
                $obr->select();
            }

            $abc = 'c';

            if ($this->prm->tareaclc == 'impresion') {
                $bandera = false;
                $banderaGuardar = false;
                $this->mixScript('
                        $("#afectacion").remove();
                        $("#separar").remove();
                    ');
            }

            $this->conectar();
            $this->setSql('
                SELECT * FROM scp_Afectacion
                WHERE gpo_id= :gpo_id
                AND ur_id= :ur_id
                AND clc_id= :clc_id;
                ');
            $params = [
                ':gpo_id' => ($clc->getGpo_id()),
                ':ur_id'  => ($clc->getUr_id()),
                ':clc_id' => ($clc->getClc_id())];
            $this->consultar($params);
            $this->desconectar();

            $ecf_id = $clc->getEcf_id();
            $cveFin->ecf_id = $ecf_id;

            if ($this->getNumeroRegistros() != 0) {
                $this->mixScript('notify("No puedes Editar la CLC ' . ($clc->getClc_id()) . '  tienes que separar la(s) Afectacion(es) para editar o eliminar","error");
                    ');

                $cveFin->lectura = true;
                $cveFin->selecciona = $clc->getEcf_id();
                $bandera = false;
                $banderaGuardar = false;
            } else {
                /* LAS CLC'S  que registren un estado mayor o igual a 3 en la bitacora ya no se van a eliminar */
                $this->conectar();
                $this->setSql('
                    SELECT *
                    FROM ' . BD_BITACORA . '.bit_Clc
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id
                    AND ecl_id>=3
                    limit 1
                ');
                $params = [
                    ':gpo_id' => ($clc->getGpo_id()),
                    ':ur_id'  => ($clc->getUr_id()),
                    ':clc_id' => ($clc->getClc_id())];
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $bandera = false;
                    $paso = true;
                    $banderaGuardar = true;
                } else {
                    $bandera = true;
                    $paso = true;
                    $banderaGuardar = true;
                }
            }
        } else {
            $clc->setClc_id('');
            $abc = 'a';
            $this->prm->clc_id = '';
            $bandera = true;
            $banderaGuardar = true;
        }

        /* NUMERO DE CLC CUANDO ES EDITAR */
        $numclc = new stdClass();
        $numclc->id = 'numclc';
        $numclc->label = 'Numero de CLC';
        $numclc->readonly = true;
        $numclc->enDiv = true;
        $numclc->maxlength = 6;
        if ($abc == 'a') {
            $numclc = $this->ponInput($numclc);
            $numclc = '';
        } else {
            $numclc->value = $clc->getClc_id();
            $numclc = $this->ponInput($numclc);
        }
        /* INPUT QUE LLEVA LA FECHA DE CREACION DE LA CLC PARA MOSTRAR AL USUARIO */
        $hoy = date('Y-m-d');

        $fechacreacion = new StdClass();
        $fechacreacion->id = 'fechacreacion';
        $fechacreacion->label = 'Fecha de Elaboracion';
        $fechacreacion->enDiv = true;
        $fechacreacion->readonly = true;
        if ($abc == 'a') {
            $fechacreacion->value = $hoy;
        } else {
            $fechacreacion->value = $clc->getFechaElaboracion();
        }

        /* CONCEPTO DE PAGO DE  LA CLC */
        $concepto = new stdClass();
        $concepto->id = 'concepto';
        $concepto->name = 'concepto';
        $concepto->label = 'Concepto de pago de la CLC';
        $concepto->placeholder = 'Concepto de pago de la CLC';
        $concepto->enDiv = true;
        $concepto->required = true;
        $concepto->width = '400px';
        $concepto->class = 'anulaenter habilitar';
        $concepto->height = '150px';
        $concepto->maxlength = '600';
        $concepto->text = $clc->getConcepto();

        /* CONCEPTO DE PAGO DE  LA CLC PARA LAS CLC´S  DE OBRA */
        $conceptoObra = new stdClass();
        $conceptoObra->id = 'conceptoObra';
        $concepto->name = 'conceptoObra';
        if ($abc == 'a') {
            $conceptoObra->label = 'Complemento de concepto pago de la CLC';
        } else {
            $conceptoObra->label = 'Concepto  de Pago de la CLC';
        }
        $conceptoObra->enDiv = true;
        $conceptoObra->required = true;

        $conceptoObra->width = '400px';
        $conceptoObra->class = 'anulaenter habilitar';
        $conceptoObra->height = '150px';
        $conceptoObra->maxlength = '600';
        $conceptoObra->text = htmlspecialchars($clc->getConcepto() ?? '');

        $estimacion = new stdClass();
        $estimacion->id = 'estimacion';
        $estimacion->label = 'Concepto de pago de la CLC';
        $estimacion->enDiv = true;
        $estimacion->maxlength = 100;
        if ($abc == 'a') {
            $estimacion = $this->ponInput($estimacion);
        } else {
            $estimacion = '';
        }
        /* INPUT QUE LLEVA EL VALOR DE LA MODALIDAD DE LA NOMINA */
        $wh = '';

        $claveUR = $ur->getClave();
        if ($claveUR != 104) {
            $wh = 'AND mno_id!=6';
        } else {
            $wh = '';
        } //304 en 2015 104 2016 solo esta UR puede ver la modalidad 6

        $modalidad = new stdClass();
        $modalidad->id = 'mno_id';
        $modalidad->name = 'control';
        $modalidad->required = true;
        $modalidad->change = '
                if ($(this).find("option:selected").val() ==6) {
                    document.getElementById("submod").style.display="block";
                    $("#smn_id").attr("required","1");
                } else {
                    document.getElementById("submod").style.display="none";
                    $("#smn_id").removeAttr("required");
                }
            ';
        $modalidad->enDiv = true;
        $modalidad->class = 'habilitar';
        $modalidad->mensaje = 'Selecciona una Modalidad';
        $modalidad->label = 'Modalidad';
        $modalidad->comoAtr = 'mno_id';

        $administrativo = new StdClass();
        $administrativo->id = 'administrativo';
        $administrativo->label = 'Jefe Administrativo';
        $administrativo->enDiv = true;
        $administrativo->readonly = true;
        $administrativo->value = $ur->getNombreAdministrativo();

        $cargoAdmitivo = new StdClass();
        $cargoAdmitivo->id = 'cargoAdmitivo';
        $cargoAdmitivo->label = 'Cargo Administrativo';
        $cargoAdmitivo->enDiv = true;
        $cargoAdmitivo->readonly = true;
        $cargoAdmitivo->value = $ur->getCargoAdministrativo();

        $titular = new StdClass();
        $titular->id = 'titular';
        $titular->label = 'Nombre del Titular';
        $titular->enDiv = true;
        $titular->readonly = true;
        $titular->value = $ur->getNombreTitular();

        $cargoTitular = new StdClass();
        $cargoTitular->id = 'cargoTitular';
        $cargoTitular->label = 'Cargo del Titular';
        $cargoTitular->enDiv = true;
        $cargoTitular->readonly = true;
        $cargoTitular->value = $ur->getCargoTitular();

        $objUrSecretario = new UnidadResponsable();
        $objUrSecretario->setGpo_id(1);
        $objUrSecretario->setUr_id(18);
        $objUrSecretario->select();

        $secretario = new StdClass();
        $secretario->id = 'secretario';
        $secretario->label = 'Secretario de Administración';
        $secretario->enDiv = true;
        $secretario->readonly = true;
        $secretario->value = $objUrSecretario->getNombreTitular();

        /* ESTA VARIABLE CONTIENE EL CHECK PARA AGREGAR LA FIRMA DEL SECRETARIO */
        $htmlfirma = '';
        /* LA VARIABLE claveUR CONTIENE LOS DIREFERENTES CASOS PARA LOS GRUPOS DE LA UR´S YA QUE NO TODAS TIENEN MODALIDAD Y LA 474,430,431,439 SOLO TIENEN DOS FIRMAS */
        $claveUR = '';
        $claveUR = $ur->getClave();
        /* SUBTIPO DE LA CLC */
        $subtipo = new stdClass();
        $subtipo->id = 'stc_id';
        $subtipo->name = 'control';
        $subtipo->required = true;
        $subtipo->enDiv = true;
        $subtipo->class = 'habilitar';
        $subtipo->campoLabelAgrupacion = 'stc_nombre';
        $subtipo->mensaje = 'Selecciona';
        $subtipo->label = 'Subtipo de CLC';
        $subtipo->comoAtr = 'stc_id';
        $subtipo->search = true;
        $subtipo->detonar = $abc === 'a';
        $subtipo->selecciona = $clc->getStc_id();

        $grupo = 0;
        if (in_array($claveUR, $this->grupo2)) {
            switch ($this->prm->tipo) {
                case 'A': //S.P.
                    $filtro = 'stc_id IN (8,6,15,17)';
                    break;
                case 'C': //Pagos a terceros
                    $filtro = 'stc_id IN (1,2,3,4)';
                    break;
                case 'M':
                    $filtro = 'stc_id between 1 AND 11';
                    break;
                case 'I':
                    $filtro = 'stc_id NOT IN(4)';
                    break;
                default:
                    $filtro = 'stc_id IN (1,2)';
                    break;
            }
            $modalidad = '';
            $htmlfirma = '';
            $grupo = 2;
        } else {
            $grupo = 1;
            if ($clc->getMno_id() != '') {
                $modalidad->sql = 'SELECT mno_id,mno_nombre FROM cat_ModalidadNomina JOIN enc_ModalidadSubtipoClc USING(mno_id)
                    WHERE tcl_id="' . $this->prm->tipo . '" AND stc_id=' . $clc->getStc_id() . ' ' . $wh . ' ';
                $modalidad->selecciona = $clc->getMno_id();
                $modalidad = $this->ponCombo($modalidad);
            } else {
                $modalidad = $this->templateCombo($modalidad);
            }
            $filtro = 'stc_id!=18';
        }

        $firmaSecretario = '
            if ($(this).find("option:selected").val()==1) {
                $("#firmaSecretario").css("display","block");
            } else {
                $("#firmaSecretario").css("display","none");
            }
        ';

        $fondoRota = ''; //Variable que sirve para que las Claves de anticipo y fondo rotatorio se detonen de  acuerdo al subtipo de CLC
        if ($this->prm->tipo == 'E') {
            $fondoRota = 'cambio:true, ';
        }

        $modPagos = '
            comunica("' . $this->pantalla . '",{
                metodo:"modalidadPago",
                ucb_id:$("input[name=modC]").val(),
                stc_id:$(this).val(),
                ' . $fondoRota . '
                tipo:"' . $this->prm->tipo . '",
                ' . ((isset($this->prm->clc_id) && $this->prm->clc_id != '') ? 'clc_id:' . $this->prm->clc_id . ',' : '') . '
                ' . ((isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? 'gpo_id:' . $this->prm->gpo_id . ',' : '') . '
                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ? 'ur_id:' . $this->prm->ur_id . '' : '') . '
            });
        ';

        /*VERIFICAR SI APLICA EN TODOS LOS CASOS, YA QUE EN EL 2017 ESTABA COMENTADO POR ALGUNA RAZÓN QUE DESCONOZCO*/
        // $clicCuenta = ' TipoPago(); ';
        $clicCuenta = '';

        switch ($this->prm->tipo) {
            case 'A':
                if ($bandera == true) { // Si es nuevo y/o no tiene afectaciones.
                    $subtipo->change = '
                        if($(this).find("option:selected").val() != ""){

                            comunica("' . $this->pantalla . '",{
                                metodo:"comboNomina",
                                stc_id:$("#stc_id").find("option:selected").attr("stc_id"),
                                id:2,
                                tipo:"' . $this->prm->tipo . '",
                                ' . ((isset($this->prm->clc_id) && $this->prm->clc_id != '') ? 'clc_id:' . $this->prm->clc_id . ',' : '') . '
                                ' . ((isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? 'gpo_id:' . $this->prm->gpo_id . ',' : '') . '
                                ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ? 'ur_id:' . $this->prm->ur_id . ',' : '') . '
                                combo:"mno_id"
                            });

                        }
                    ';
                }

                if ($abc == 'c') {
                    $this->modalidadPago();
                    $this->setContenedor('contenedor');
                }
                break;
            case 'B':
            case 'F':
            case 'G':
            case 'H':
                $this->modalidadPago();
                $this->setContenedor('contenedor');
                break;
            default:
                //'.$clicCuenta.' me marcaba como indefinido
                if ($bandera == true) { // Si es nuevo y/o no tiene afectaciones.  //  si es el subtipo 1 de pagos a terceros pone la firma
                    $subtipo->change = '
                        if($(this).find("option:selected").val() != ""){
                            ' . $firmaSecretario . '
                            ' . $modPagos . '
                        }
                    ';
                }
                if ($abc == 'c') {
                    $this->modalidadPago();
                    $this->setContenedor('contenedor');
                }
                break;
        }
        if ($abc == 'a') {
            $subtipo->sql = '
                SELECT stc_id,stc_nombre
                FROM cat_SubtipoClc
                INNER JOIN enc_FiltroClc USING(tcl_id,stc_id)
                WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id= ' . $_SESSION[_S]['ur_id'] . ' AND tcl_id="' . $this->prm->tipo . '" AND ' . $filtro . '
                GROUP BY tcl_id,stc_id
                ORDER BY stc_id
                ';
        } else {
            $subtipo->sql = '
            SELECT stc_id,stc_nombre
            FROM cat_SubtipoClc
            INNER JOIN enc_FiltroClc USING(tcl_id,stc_id)
            WHERE gpo_id=' . $_SESSION[_S]['gpo_id'] . ' and ur_id= ' . $_SESSION[_S]['ur_id'] . ' AND tcl_id="' . $this->prm->tipo . '" AND ' . $filtro . '
            GROUP BY tcl_id,stc_id
            ORDER BY stc_id
            ';
        }

        /* TIPO DE NOMINA  */
        $tipoNom = new stdClass();
        $tipoNom->id = 'tno_id';
        $tipoNom->name = 'control';
        $tipoNom->required = true;
        $tipoNom->enDiv = true;
        $tipoNom->class = 'habilitar';
        $tipoNom->sql = 'SELECT tno_id,tno_nombre FROM cat_TipoNomina';
        $tipoNom->mensaje = 'Selecciona  un tipo de nomina';
        $tipoNom->label = 'Tipo de Nomina';
        $tipoNom->selecciona = $clc->getTno_id();

        /* SUMMODALIDAD DE HABERES */
        $submod = new stdClass();
        $submod->id = 'smn_id';
        $submod->name = 'control';
        $submod->enDiv = true;
        $submod->class = 'habilitar';
        $submod->camposVal = 'smn_id';
        $submod->agrupaPor = 'smn_id';
        $submod->campoLabelAgrupacion = 'smn_nombre';
        $submod->mensaje = 'Selecciona';
        $submod->label = 'Tipo de Haberes';
        $submod->comoAtr = 'smn_id';
        $submod->sql = 'Select smn_id,smn_nombre from cat_SubmodalidadNomina where smn_id!=0 ;';
        $submod->selecciona = $clc->getSmn_id();

        /* DE ACUERDO AL FILTRO DEL USO DE LA CUENTA BANCARIA SE SELECCIONA LA CLABE INTERBANCARIA */
        $banur = new stdClass();
        $banur->id = 'bur_id';
        $banur->name = 'bur_id';
        $banur->required = true;
        $banur->enDiv = true;
        $banur->class = 'habilitar';
        $banur->soloText = true;
        $banur->camposText = 'bur_id,bur_alias';
        $banur->comoAtr = 'bur_CuentaBancaria,bur_id,iba_nombre';
        $banur->mensaje = 'Elige la clabe Interbancaria';
        $banur->label = 'Elige la clabe Interbancaria';
        $banur->search = true;
        $banur->change = '
            $("#cuenta").val($(this).find("option:selected").attr("bur_CuentaBancaria"));
            $("#nombrebanco").val($(this).find("option:selected").attr("iba_nombre"));
            ';
        $filtro = $ucb_id = '';
        $ChequeSpei = (isset($this->prm->tipo_Pago)) ? $this->prm->tipo_Pago : '';
        if ($abc == 'c') {
            switch ($clc->getFormaPago()) {
                case 'S':
                    $ChequeSpei = 1;
                    break;
                default:
                    $ChequeSpei = 2;
                    break;
            }
        }
        $ucb_id = ' ucb_id= ' . $clc->getUcb_id() . '';

        /* Uso de cuentas bancarias por tipo de CLC */

        if ($clc->getBur_id() != '') {
            $prms = [
                'tipo'       => $this->prm->tipo,
                'stc_id'     => $this->prm->stc_id,
                'ucb_id'     => $clc->getUcb_id(),
                'mpc_id'     => $this->prm->mpc_id,
                'ChequeSpei' => $ChequeSpei,
                'ue_id'      => $clc->getUe_id(),
            ];
            $bursql = $this->model->getSqlCuenta($prms);
            $banur->sql = $bursql;
            $banur->selecciona = $clc->getBur_id();
            $banur = $this->ponCombo($banur);
        } else {
            $banur = $this->templateCombo($banur);
        }

        /* DEL VALOR QUE TIENE EL COMBO banur_id EN SU CAMPO bur_id SE SELECCIONA EL NUMERO DE CUENTA EL CUAL SE MUESTRA EN EL CAMPO CUENTA */
        $cuenta = new stdClass();
        $cuenta->id = 'cuenta';
        $cuenta->label = 'Numero de Cuenta';
        $cuenta->placeholder = 'Cuenta';
        $cuenta->readonly = true;
        $cuenta->enDiv = true;
        $cuenta->class = 'habilitar';
        $cuenta->maxlength = 11;
        /* DEL VALOR QUE TIENE LA CUENTA EN UNA FUNCIONA SE HACE UNA CONSULTA CON LA CUAL OBTENEMOS EL NOMBRE DEL BANCO */
        $nombrebanco = new stdClass();
        $nombrebanco->id = 'nombrebanco';
        $nombrebanco->label = 'Nombre del Banco';
        $nombrebanco->placeholder = 'Nombre del Banco';
        $nombrebanco->readonly = true;
        $nombrebanco->class = 'habilitar';
        $nombrebanco->enDiv = true;
        $nombrebanco->maxlength = 50;
        /* LINEA DE CAPTURA */

        $linea = new stdClass();
        $linea->id = 'linea';
        $linea->label = 'Linea de captura';
        $linea->class = 'habilitar';
        $linea->enDiv = true;
        $linea->maxlength = 20;
        if ($clc->getLineaCaptura() != '') {
            $linea->value = $clc->getLineaCaptura();
        }
        /* CADENA PRODUCTIVA */
        $cadena = new stdClass();
        $cadena->id = 'cadena';
        $cadena->label = 'Pago por cadena productiva';
        $cadena->class = 'habilitar';
        $cadena->enDiv = true;
        $cadena->maxlength = 20;
        $cadena->disabled = true;
        $cadena->value = 'INTERMEDIARIO FINANCIERO';

        /* CATALOGO DE BENEFICIARIOS PARA LA CLC DE BURSATILIZACION  */
        $benebur = new stdClass();
        $benebur->sql = 'SELECT ben_nombre from cat_Beneficiario;';
        $benebur->id = 'ben_nombre';
        $benebur->name = 'ben_nombre';
        $benebur->required = true;
        $benebur->class = 'habilitar';
        $benebur->comoAtr = 'ben_nombre';
        $benebur->mensaje = 'SELECCIONA';
        $benebur->label = 'Seleccion el beneficiario';
        /*Beneficiario PDP para BURSATILIZACION*/
        $beneburpdp = new stdClass();
        $beneburpdp->id = 'beneburpdp';
        $beneburpdp->label = 'Nombre del Beneficiario';
        $beneburpdp->class = 'habilitar';
        $beneburpdp->enDiv = true;
        $beneburpdp->disabled = true;
        $beneburpdp->maxlength = 200;
        $beneburpdp->value = $clc->getBeneficiario();
        if ($clc->getBeneficiario() != '') {
            $benebur->selecciona = $clc->getBeneficiario();
        }

        /* BENEFIARIO CUANDO ES LA MISMA UNIDAD RESPONSABLE */
        $num_ur = $ur->getGpo_id() . $ur->getUr_id();

        $beneur = new stdClass();
        $beneur->id = 'beneur';
        $beneur->label = 'Nombre del Beneficiario';
        $beneur->enDiv = true;
        $beneur->maxlength = 100;

        switch ($num_ur) {
            case '602':
            case '603':
                switch ($this->prm->tipo) {
                    case 'F':
                    case 'G':
                    case 'H':

                        $beneur->value = (($abc == 'a') ? 'NACIONAL FINANCIERA S.N.C.' : $clc->getBeneficiario());
                        break;
                    default:
                        $beneur->value = $ur->getNombre();
                        $beneur->readonly = true;
                        break;
                }
                break;
            default:
                switch ($this->prm->tipo) {
                    case 'F':
                    case 'G':
                    case 'H':

                        $beneur->value = $ur->getNombre();
                        break;
                    default:
                        $beneur->value = $ur->getNombre();
                        $beneur->readonly = true;
                        break;
                }
                break;
        }

        /* BENEFICIARIO CUANDO LA UR CAPTURA */
        $benecap = new stdClass();
        $benecap->id = 'benecap';
        $benecap->label = 'Nombre del Beneficiario';
        $benecap->class = 'habilitar';
        $benecap->enDiv = true;
        $benecap->maxlength = 200;
        $benecap->value = $clc->getBeneficiario();
        // Si la clc es de gastos de operacion y el uso de la cuenta de pago directo, automaticamente el valor a tomar es el nombre del proveedor.

        //Descomentar esta seccion cuando se libere lo de pago directo de gastos de operacion
        // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
        // se agrega en la misma funcionalidad de pago directo a proveedor ticket 638123
        if ($this->prm->mpc_id == 5 && $this->prm->mpc_id == 4) {
            $benecap->value = $clc->getBeneficiario();
            $beneur->value = $clc->getBeneficiario();
            $benecap->readonly = true;
            $beneur->readonly = true;
        }

        if ($this->prm->epg_id != '') {
            $consulta = '';
            if ($this->prm->ecf_id != '') {
                $consulta = 'and ecf_id= ' . $this->prm->ecf_id . '';
            }
            $acceso = new AccesoDAtos();
            $acceso->setSql('
                SELECT oa_numobra, ore_oficioaut, oa_nombre, sft_nombre, ter_nombre, cfi_anio, oa_' . APP_EJERCICIO_ANTERIOR . '
                FROM cat_ObraActividad
                INNER JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
                INNER JOIN poa_OrigenRecurso USING (epg_id)
                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id)
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
                INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING (ter_id)
                WHERE
                epg_id = ?
                ' . $consulta . '
                ');

            $acceso->conectar();
            $acceso->consultar([$this->prm->epg_id]);
            $acceso->desconectar();
            $datosObra = $acceso->extraer();
        }
        /* Se muestra el número el nombre de la obra de inversion por lo que se esta solicitando el recurso  */
        $obra = new stdClass();
        $obra->id = 'cumplimiento';
        $obra->label = 'N° de Obra y Nombre';
        $obra->readonly = true;
        $obra->class = 'habilitar';
        $obra->enDiv = true;
        $obra->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['oa_nombre']) ? $datosObra['oa_nombre'] : '') : '');
        $obra->maxlength = 15;

        /* Se muestra el numero de oficio que se elaboro para autorizar la obra( para obras de refrendo lo asigna egreso ypara obras del ejercicio presente lo asigna planeacion) */
        $oficioAutorizacion = new stdClass();
        $oficioAutorizacion->id = 'oficioaut';
        $oficioAutorizacion->label = 'Oficio de Autorización';
        $oficioAutorizacion->class = 'habilitar';
        $oficioAutorizacion->readonly = true;
        $oficioAutorizacion->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['ore_oficioaut']) ? $datosObra['ore_oficioaut'] : '') : '');
        $oficioAutorizacion->enDiv = true;
        $oficioAutorizacion->maxlength = 15;

        $NumObra = new stdClass();
        $NumObra->id = 'numobra';
        $NumObra->label = 'No. Obrasspp:';
        $NumObra->class = 'habilitar';
        $NumObra->readonly = true;
        $NumObra->enDiv = true;
        $NumObra->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['oa_numobra']) ? $datosObra['oa_numobra'] : '') : '');
        $NumObra->maxlength = 15;

        /* Se muestra la subfuente  */
        $subfuente = new stdClass();
        $subfuente->id = 'subfuente';
        $subfuente->label = 'Subfuente';
        $subfuente->class = 'habilitar';
        $subfuente->enDiv = true;
        $subfuente->readonly = true;
        $subfuente->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['sft_nombre']) ? $datosObra['sft_nombre'] : '') : '');
        $subfuente->maxlength = 15;

        /* Muestra la aportacion de acuerdo a lo que seleccionaron en la clave de financiamiento  */
        $aportacion = new stdClass();
        $aportacion->id = 'aportacion';
        $aportacion->label = 'Aportación';
        $aportacion->class = 'habilitar';
        $aportacion->enDiv = true;
        $aportacion->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['ter_nombre']) ? $datosObra['ter_nombre'] : '') : '');
        $aportacion->readonly = true;
        $aportacion->maxlength = 15;

        /* Numero del contrato de la obra se aumento el numero */
        $obracontrato = new stdClass();
        $obracontrato->id = 'obracontrato';
        $obracontrato->label = 'Obra por contrato  no.:';
        $obracontrato->placeholder = 'Número del contrato de la obra';
        $obracontrato->class = 'habilitar';
        $obracontrato->enDiv = true;
        $obracontrato->readonly = true;
        $obracontrato->maxlength = 50;
        $obracontrato->value = $obr->getContrato();

        /* Es el año de la clave de financiamiento que se esta usando   */
        $ejercicio = new stdClass();
        $ejercicio->id = 'ejercicio';
        $ejercicio->label = 'Ejercicio';
        $ejercicio->class = 'habilitar';
        $ejercicio->enDiv = true;
        $ejercicio->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['cfi_anio']) ? $datosObra['cfi_anio'] : '') : '');
        $ejercicio->readonly = true;
        $ejercicio->maxlength = 15;

        /* Numero de obra del ejercicio Anterior  */
        $referencia = new stdClass();
        $referencia->id = 'referencia';
        $referencia->label = 'Referencia';
        $referencia->class = 'habilitar';
        $referencia->enDiv = true;
        $referencia->value = (($this->prm->epg_id != '') ? '' . (isset($datosObra['oa_' . APP_EJERCICIO_ANTERIOR]) ? ($datosObra['oa_' . APP_EJERCICIO_ANTERIOR]) : '') : '');
        $referencia->readonly = true;
        $referencia->maxlength = 30;

        /* Se captura que porcentaje de la obra avanzará con el recurso solicitado */
        $avancefisico = new stdClass();
        $avancefisico->id = 'avancefisico';
        $avancefisico->label = 'Avance Fisico %';
        $avancefisico->placeholder = 'Avance Fisico';
        $avancefisico->class = 'habilitar';
        $avancefisico->enDiv = true;
        $avancefisico->maxlength = 15;
        $avancefisico->value = $obr->getAvanceFisico();

        /* Se captura que porcentaje acumulado  del presupuesto se ejercerá con el recurso solicitado */
        $avancefinanciero = new stdClass();
        $avancefinanciero->id = 'avancefinanciero';
        $avancefinanciero->label = 'Avance Financiero%';
        $avancefinanciero->placeholder = 'Avance Financiero';
        $avancefinanciero->class = 'habilitar';
        $avancefinanciero->enDiv = true;
        $avancefinanciero->maxlength = 15;
        $avancefinanciero->value = $obr->getAvanceFinanciero();

        /* Numero de fianzas que garantizan ek cumplimiento del contrato */
        $fianza = new stdClass();
        $fianza->id = 'fianza';
        $fianza->label = 'Fianza de Cumplimiento';
        $fianza->placeholder = 'Fianza de Cumplimiento';
        $fianza->class = 'habilitar';
        $fianza->enDiv = true;
        $fianza->readonly = true;
        $fianza->maxlength = 20;
        $fianza->value = $obr->getCumplimiento();

        /* Numero de fianza mediante cual el contratista responderá de los defectos, vicios ocultoso cualquier otra responsabilidad en que hubiera incurrido */
        $vicios = new stdClass();
        $vicios->id = 'vicios';
        $vicios->label = 'Fianza de vicios ocultos';
        $vicios->placeholder = 'Fianza de vicios ocultos';
        $vicios->class = 'habilitar';
        $vicios->enDiv = true;
        $vicios->readonly = true;
        $vicios->maxlength = 50;
        $vicios->value = $obr->getVicios();

        /* Indica documentacion Anexa */
        $documentacion = new stdClass();
        $documentacion->id = 'documentacion';
        $documentacion->name = 'documentacion';
        $documentacion->label = 'Indica documentacion Anexa';
        $documentacion->enDiv = true;
        $documentacion->placeholder = 'Indica documentacion Anexa';
        $documentacion->width = '400px';
        $documentacion->class = 'habilitar';
        $documentacion->class = 'anulaenter habilitar';
        $documentacion->height = '150px';
        $documentacion->maxlength = '600';
        $documentacion->text = $obr->getDocumentacion();

        /* Numero de fianza que la debida inversion y amortizacion del anticipo */
        $anticipo = new stdClass();
        $anticipo->id = 'anticipo';
        $anticipo->label = 'Fianza de anticipos';
        $anticipo->placeholder = 'Fianza de anticipos';
        $anticipo->class = 'habilitar';
        $anticipo->enDiv = true;
        $anticipo->readonly = true;
        $anticipo->maxlength = 50;
        $anticipo->value = $obr->getAnticipo();

        $stc = '';
        $finiquito = new stdClass();
        $finiquito->id = 'finiquito';
        $finiquito->name = 'control';
        $finiquito->required = true;
        $finiquito->enDiv = true;
        $finiquito->class = 'habilitar';
        switch ($this->prm->tipo) {
            case 'F':
                $finiquito->change = '
                    if($(this).val() != "" && $(this).val() != 02 && validaClasADmin()) {
                        comunica("' . $this->pantalla . '",{
                            metodo: "VerificaContratos",
                            "tipo":"' . $this->prm->tipo . '",
                            "abc":"' . $abc . '",
                            ' . ($abc == 'c' ? 'clc_id:' . $this->prm->clc_id . ',' : '') . '
                            ' . (($obr->getIpu_id() != '') ? 'ipu_id:' . $obr->getIpu_id() . ',' : '') . '
                            "stc_id":$("#finiquito").val()
                        });
                    } else {
                        $("#BancoContratos").empty();
                        if( $(this).val() != "" && validaClasADmin() ){
                            comunica("' . $this->pantalla . '",{
                                metodo: "cargaDatosObra",
                                "abc":"' . $abc . '",
                                "tipo":"' . $this->prm->tipo . '",
                                "stc_id":$("#finiquito").val()
                            });
                        }
                    }
                    ';
                break;
            case 'G':
                $finiquito->change = '
                    if( $(this).val() != "" && $(this).val() != 00 && validaClasADmin()) {
                        comunica("' . $this->pantalla . '",{
                            metodo: "VerificaContratos",
                            "tipo":"' . $this->prm->tipo . '",
                            "abc":"' . $abc . '",
                            ' . ($abc == 'c' ? 'clc_id:' . $this->prm->clc_id . ',' : '') . '
                            ' . (($obr->getIpu_id() != '') ? 'ipu_id:' . $obr->getIpu_id() . ',' : '') . '
                            "stc_id":$("#finiquito").val()
                        });
                    } else {
                        $("#BancoContratos").empty();
                        if ($(this).val() != "" && validaClasADmin()) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "cargaDatosObra",
                                "abc":"' . $abc . '",
                                "tipo":"' . $this->prm->tipo . '",
                                "stc_id":$("#finiquito").val()
                            });
                        }
                    }
                ';
                break;
            case 'H':
                if ($claveUR == 902) {
                    $finiquito->change = '
                        if ($(this).val() != "" && $(this).val() != 01 && validaClasADmin()) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "VerificaContratos",
                                "tipo":"' . $this->prm->tipo . '",
                                "abc":"' . $abc . '",
                                ' . (($obr->getIpu_id() != '') ? 'ipu_id:' . $obr->getIpu_id() . ',' : '') . '
                                "stc_id":$("#finiquito").val()
                            });
                        } else {
                            $("#BancoContratos").empty();
                            if ($(this).val() != "" && validaClasADmin()) {
                                comunica("' . $this->pantalla . '",{
                                    metodo: "cargaDatosObra",
                                    "abc":"' . $abc . '",
                                    "tipo":"' . $this->prm->tipo . '",
                                    "stc_id":$("#finiquito").val()
                                });
                            }
                        }
                    ';
                } else {
                    $stc = ' and stc_id=00 ';
                    if ($abc == 'c') {
                        $finiquito->change = '
                            if (validaClasADmin()) {
                                comunica("' . $this->pantalla . '",{
                                    metodo: "VerificaContratos",
                                    "tipo":"' . $this->prm->tipo . '",
                                    "abc":"' . $abc . '",
                                    ' . (($obr->getIpu_id() != '') ? 'ipu_id:' . $obr->getIpu_id() . ',' : '') . '
                                    ' . (($this->prm->clc_id != '') ? 'clc_id:"' . $this->prm->clc_id . '",' : '') . '
                                    "stc_id":$("#finiquito").val()
                                });
                            }
                        ';
                    }
                }
                break;
        }
        $finiquito->sql = 'select stc_id,stc_nombre from cat_SubtipoClc where tcl_id="' . $this->prm->tipo . '" ' . $stc . ';';
        $finiquito->mensaje = 'Selecciona el tipo de CLC';
        $finiquito->label = 'Tipo de CLC';
        $finiquito->selecciona = $clc->getStc_id();

        /* BENEFICIARIO PARA LOS MUNICIPIOS */
        $beneficiarioMun = new stdClass();
        $beneficiarioMun->id = 'municipio';
        $beneficiarioMun->label = 'Nombre del Municipio que es el Beneficiario';
        $beneficiarioMun->autocomplete = true;
        $beneficiarioMun->tabla = 'cat_Municipio';
        $beneficiarioMun->campoVal = 'mun_nombre';
        $beneficiarioMun->desdecaracter = 4;
        $beneficiarioMun->enDiv = true;
        $beneficiarioMun->value = $clc->getBeneficiario();

        $beneficiarioP = new stdClass();
        $beneficiarioP->id = 'beneficiarioP';
        $beneficiarioP->label = 'Beneficiario';
        $beneficiarioP->class = 'habilitar';
        $beneficiarioP->value = (($abc == 'a') ? 'Municipios' : $clc->getBeneficiario());
        $beneficiarioP->enDiv = true;
        $beneficiarioP->readonly = true;
        $beneficiarioP->maxlength = 50;

        /* FIRMA DEL PRESIDENTE */
        $presidente = new stdClass();
        $presidente->id = 'presidente';
        $presidente->label = 'Director de Evaluación de la Inversión Pública';
        $presidente->class = 'habilitar';
        $presidente->enDiv = true;
        $presidente->maxlength = 70;
        $presidente->readonly = true;
        $presidente->value = $ur->getNombreTitular();

        $presidentecargo = new stdClass();
        $presidentecargo->id = 'presidentecargo';
        $presidentecargo->label = 'Cargo';
        $presidentecargo->class = 'habilitar';
        $presidentecargo->enDiv = true;
        $presidentecargo->readonly = true;
        $presidentecargo->maxlength = 70;
        $presidentecargo->value = $ur->getCargoTitular();

        /* FIRMA DEL TESORERO */
        $tesorero = new stdClass();
        $tesorero->id = 'tesorero';
        $tesorero->label = 'Coordinador de Seguimiento Financiero y Programático';
        $tesorero->class = 'habilitar';
        $tesorero->enDiv = true;
        $tesorero->readonly = true;
        $tesorero->maxlength = 70;
        $tesorero->value = $ur->getNombreAdministrativo();

        $tesorerocargo = new stdClass();
        $tesorerocargo->id = 'tesorerocargo';
        $tesorerocargo->label = 'Cargo';
        $tesorerocargo->class = 'habilitar';
        $tesorerocargo->enDiv = true;
        $tesorerocargo->readonly = true;
        $tesorerocargo->maxlength = 70;
        $tesorerocargo->value = $ur->getCargoAdministrativo();

        /* REFERENCIA ALFANUMERICA DEL CREDITO */
        $referenciaC = new stdClass();
        $referenciaC->id = 'referenciaC';
        $referenciaC->label = 'Referencia';
        $referenciaC->placeholder = 'referencia';
        $referenciaC->class = 'habilitar';
        $referenciaC->enDiv = true;
        $referenciaC->maxlength = 30;

        /* DEUDA PUBLICA  J  */
        $adminJ = new stdClass();
        $adminJ->id = 'adminJ';
        $adminJ->label = 'Jefe Administrativo';
        $adminJ->value = (($abc == 'a') ? $ur->getNombreAdministrativo() : $clc->getNombreAdministrativo());
        $adminJ->class = 'habilitar';
        $adminJ->readonly = true;
        $adminJ->enDiv = true;
        $adminJ->maxlength = 70;
        /* cargo adminJ */
        $cargoadminJ = new stdClass();
        $cargoadminJ->id = 'cargoadminJ';
        $cargoadminJ->label = 'Cargo Administrativo';
        $cargoadminJ->readonly = true;
        $cargoadminJ->value = (($abc == 'a') ? $ur->getCargoAdministrativo() : $clc->getCargoAdministrativo());
        $cargoadminJ->class = 'habilitar';
        $cargoadminJ->enDiv = true;
        $cargoadminJ->maxlength = 70;

        /* DEUDA PUBLICA  J TITULAR  */
        $tituj = new stdClass();
        $tituj->id = 'tituj';
        $tituj->readonly = true;
        $tituj->label = 'Titular de la Dependencia';
        $tituj->value = (($abc == 'a') ? $ur->getNombreTitular() : $clc->getNombreTitular());
        $tituj->class = 'habilitar';
        $tituj->enDiv = true;
        $tituj->maxlength = 70;

        $cartituj = new stdClass();
        $cartituj->id = 'cartituj';
        $cartituj->readonly = true;
        $cartituj->label = 'Cargo Titular';
        $cartituj->value = (($abc == 'a') ? $ur->getCargoTitular() : $clc->getCargoTitular());
        $cartituj->class = 'habilitar';
        $cartituj->enDiv = true;
        $cartituj->maxlength = 70;

        $guardar = '';
        $eliminar = '';
        if ($bandera == true) {
            $btnEliminar = new stdClass();
            $btnEliminar->id = 'delete';
            $btnEliminar->class = 'delete';
            $btnEliminar->color = 'rojo';
            $btnEliminar->value = 'Eliminar';
            $btnEliminar->icon = 'solid fa-trash me-2';
            $btnEliminar->text = 'Eliminar';
            $btnEliminar->attr = new stdClass();
            if ($abc == 'c') {
                $eliminar = $this->ponBoton($btnEliminar);
            } else {
                $eliminar = '';
            }
        }

        if ($banderaGuardar == true) {
            $btnGuardar = new stdClass();
            $btnGuardar->color = 'verde';
            $btnGuardar->icon = 'solid fa-floppy-disk me-2';
            $btnGuardar->id = 'guardar';
            $btnGuardar->name = 'guardar';
            $btnGuardar->value = 'Guardar';
            $btnGuardar->class = 'me-1';
            $guardar = $this->ponBoton($btnGuardar);
        }

        $btnRegresar = new stdClass();
        $btnRegresar->id = 'regresar';
        $btnRegresar->name = 'regresar';
        $btnRegresar->color = 'gris';
        $btnRegresar->icon = 'solid fa-circle-left me-2';
        $btnRegresar->value = 'Regresar';

        $btnAfectaciones = new stdClass();
        $btnAfectaciones->id = 'afectacion';
        $btnAfectaciones->name = 'afectacion';
        $btnAfectaciones->value = 'Ver Afectaciones';
        $btnAfectaciones->color = 'azul';
        $btnAfectaciones->icon = 'glyphicon glyphicon-hand-plus';

        $cveAdmitiva = new stdClass();
        $html = '';
        $nomina = '';
        /* ESTA VARIABLE ESTA GUARDANDO LA MODALIDAD DE PAGO */
        $pago = '';
        $beneficiario = '';
        $obras = '';
        $prueba = '';
        $firmas = '';
        $cheque = '';

        $cheque .= '<div id="modPago_tipo"></div>';
        $html .= '<form role="form" id="frmAlimentacion" name="frmAlimentacion" action="' . $this->pantalla . '">';

        $deudaPub = $complementoSubtipo = $TipCompra = $complementoCuenta = '';
        $varBeneficiario = $this->ponInput($benecap);

        switch ($this->prm->tipo) {
            case 'A':
                $complementoCuenta = '
                    <fieldset class="col-md-6 col-xs-6" id="hidelinea" >
                        ' . $this->ponInput($linea) . '
                    </fieldset>
                ';
                $complementoSubtipo = '
                    <fieldset class="col-md-6 col-xs-6 mb-4" id="mod">
                        ' . $modalidad . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6 mb-4">
                        ' . $this->ponCombo($tipoNom) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6" id="submod" style="display:' . ($clc->getSmn_id() != '' ? 'block' : 'none') . '">
                        ' . $this->ponCombo($submod) . '
                    </fieldset>
                ';
                break;
            case 'B':
                $tipoCompra = new stdClass();
                $tipoCompra->id = 'tcp_id';
                $tipoCompra->name = 'control';
                $tipoCompra->required = true;
                $tipoCompra->enDiv = true;
                $tipoCompra->class = 'habilitar';
                $tipoCompra->sql = 'SELECT tcp_id,tcp_nombre FROM cat_TipoCompra WHERE tcp_id!="P"';
                $tipoCompra->mensaje = 'Selecciona  un tipo de Compra';
                $tipoCompra->label = 'Tipo de Compra';
                if ($clc->getTcp_id() != '') {
                    $tipoCompra->sql = 'SELECT tcp_id,tcp_nombre FROM cat_TipoCompra where  tcp_id!="P"';
                    $tipoCompra->selecciona = $clc->getTcp_id();
                } else {
                    $tipoCompra->selecciona = '1';
                }
                $complementoCuenta = '
                    <fieldset id="hidecadena" class="col-md-6">
                    ' . $this->ponInput($cadena) . '
                    </fieldset>
                ';
                $TipCompra = '
                    <div class="row col-md-12 col-xs-12">
                        <fieldset class="col-md-6 col-xs-6">
                            ' . $this->ponCombo($tipoCompra) . '
                        </fieldset>
                    </div>
                ';

                break;
            case 'C':
                $complementoCuenta = '
                    <fieldset class="col-md-6 col-xs-6" id="hidelinea" >
                        ' . $this->ponInput($linea) . '
                    </fieldset>
                ';
                break;
            case 'D':
                $varBeneficiario = $this->ponInput($beneur);
                break;
            case 'F':
            case 'G':
            case 'H':

                if ($abc == 'a') {
                    $cveFin->inicial = true;
                } else {
                    $cveFin->lectura = true;
                }

                $verificar = '';
                $ver_1 = '';
                if ($this->prm->tipo != 'H') {
                    $ver_1 = ' $("#finiquito").val("").change().removeAttr("disabled","disabled")';
                }
                if ($claveUR != 902) {
                    $verificar = '
                            if( validaClasADmin() ){
                                ' . $ver_1 . '
                                comunica("' . $this->pantalla . '",{
                                        metodo: "VerificaContratos",
                                        "tipo":"' . $this->prm->tipo . '",
                                        "stc_id":$("#finiquito").val()
                                });
                            }
                        ';
                }
                /* El encuadre programatico solo son requeridas en las clc´s de Inversion publica */
                $cveAdmitiva->toa = 2;
                $cveAdmitiva->filtro = 'poa';
                if ($abc == 'c') {
                    $this->conectar();
                    $this->setSql('
                        SELECT prg_id,spr_id,py_id,oa_id
                        FROM enc_Programatico
                        WHERE epg_id= ?
                    ');
                    $this->consultar([$clc->getEpg_id()]);
                    $this->desconectar();
                    $epg = $this->extraer();
                    $cveAdmitiva->prg_id = $epg['prg_id'];
                    $cveAdmitiva->spr_id = $epg['spr_id'];
                    $cveAdmitiva->py_id = $epg['py_id'];
                    $cveAdmitiva->oa_id = $epg['oa_id'];
                    $cveAdmitiva->ue_id = $clc->getUe_id();
                    $cveAdmitiva->lectura = true;
                } else {
                    $cveAdmitiva->change = '
                        comunica("srv/ClasificacionFinanciamiento.php",{
                            metodoCveFin:"cargaComboCveFin",
                            filtro:"presupuesto",
                            tipo:"' . $this->prm->tipo . '"
                        });
                        ' . $verificar . '
                    ';
                }
                $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));

                if ($claveUR == 902) {
                    $varBeneficiario = $this->ponInput($beneficiarioMun);
                } else {
                    $varBeneficiario = $this->ponInput($beneur);
                }

                break;
            case 'I':
                if ($abc == 'a') {
                    $cveFin->inicial = true;
                } else {
                    $cveFin->lectura = true;
                }

                if ($claveUR == '902') {
                    $verificar = '
                        $("#stc_id").change();
                    ';
                    $cveAdmitiva->toa = 2;
                    $cveAdmitiva->filtro = 'poa';
                    if ($abc == 'c') {
                        $this->conectar();
                        $this->setSql('
                            SELECT prg_id,spr_id,py_id,oa_id
                            FROM enc_Programatico
                            WHERE epg_id= ?
                        ');
                        $this->consultar([$clc->getEpg_id()]);
                        $this->desconectar();
                        $epg = $this->extraer();
                        $cveAdmitiva->prg_id = $epg['prg_id'];
                        $cveAdmitiva->spr_id = $epg['spr_id'];
                        $cveAdmitiva->py_id = $epg['py_id'];
                        $cveAdmitiva->oa_id = $epg['oa_id'];
                        $cveAdmitiva->ue_id = $clc->getUe_id();
                        $cveAdmitiva->lectura = true;
                    } else {
                        $cveAdmitiva->change = '
                            comunica("srv/ClasificacionFinanciamiento.php",{
                                metodoCveFin:"cargaComboCveFin",
                                filtro:"presupuesto",
                                tipo:"' . $this->prm->tipo . '"
                                });
                            ' . $verificar . '
                        ';
                    }
                    $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($cveAdmitiva));
                    $varBeneficiario = $this->ponInput($beneficiarioMun);
                }
                break;
            case 'J':
                $deudaPub = '
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($referenciaC) . '
                    </fieldset>
                ';
                $varBeneficiario = $this->ponInput($benecap);
                break;
            case 'K':
                $varBeneficiario = '<div id="divbenebur">';
                $varBeneficiario .= $this->ponCombo($benebur);
                $varBeneficiario .= '</div>';
                $varBeneficiario .= '<div id= "divbeneburpdp" style="display:none" >';
                $varBeneficiario .= $this->ponInput($beneburpdp);
                $varBeneficiario .= '</div>';
                break;
            case 'L':
                $complementoCuenta = '
                        <fieldset class="col-md-6 col-xs-6" id="hidelinea" >
                            ' . $this->ponInput($linea) . '
                        </fieldset>
                    ';
                $varBeneficiario = $this->ponInput($benecap);
                break;
            case 'M':
                $complementoCuenta = '
                        <fieldset class="col-md-6 col-xs-6" id="hidelinea" >
                            ' . $this->ponInput($linea) . '
                        </fieldset>
                    ';
                $varBeneficiario = $this->ponInput($benecap);
                break;
        }

        $SubtipoClc = '
            <legend align="center" style="color:#FF4000">Datos </legend><hr/>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-6 col-xs-6">
                ' . $this->ponCombo($subtipo) . '
                </fieldset>
                ' . $complementoSubtipo . '
                ' . $deudaPub . '
            </div>
        ';

        $DatosBanco = '
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-6 col-xs-6">
                ' . $banur . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6 mb-4">
                ' . $this->ponInput($cuenta) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-6 col-xs-6 mb-4">
                ' . $this->ponInput($nombrebanco) . '
                </fieldset>
                ' . $complementoCuenta . '
            </div>
        ';

        $varDocumentacion = $var1 = $var2 = $var3 = $var4 = '';
        if ($claveUR == 902) {
            $var1 = $this->ponInput($presidente);
            $var2 = $this->ponInput($presidentecargo);
            $var3 = $this->ponInput($tesorero);
            $var4 = $this->ponInput($tesorerocargo);
            $varDocumentacion = '
                <fieldset class="col-md-6 col-xs-6">
                ' . $this->ponTextArea($documentacion) . '
                </fieldset>
            ';
        } else {
            $var1 = $this->ponInput($administrativo);
            $var2 = $this->ponInput($titular);
            $var3 = $this->ponInput($cargoAdmitivo);
            $var4 = $this->ponInput($cargoTitular);
        }
        $firmasObra = '
            <legend align="center" style="color:#FF4000">Firmas</legend><hr/>
                <div class="row col-md-12 col-xs-12 mb-2">
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $var1 . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $var2 . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12 mb-2">
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $var3 . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $var4 . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12 mb-2">
                    <fieldset class="col-md-6 col-xs-6 nom_secretario">
                    </fieldset>
                </div>
        ';

        $beneficiario = '
            <legend align="center" style="color:#FF4000">Beneficiario</legend><hr/>
            <div class="row col-md-12 col-xs-12 mb-4">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $varBeneficiario . '
                </fieldset>
            </div>
            ' . $TipCompra . '
        ';

        $grupo == 1 ? $htmlfirma = '
            <div id="firmaSecretario" style="display:block">
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-7 col-xs-12">
                        <legend align="center" style="color:#FF4000">Firma del secretario de Administración?
                            <input type="checkbox" class="habilitar" id="clc_firmas" ' . (($clc->getFirmas() != '' && $clc->getFirmas() == 3) ? 'checked' : '') . '/></legend><hr/>
                    </fieldset>
                </div>
            </div>' : '<div id="firmaSecretario" style="display:none"></div>';
        $firmaSecretarioAdm = $htmlfirma;

        $firmasClc = '
            <legend align="center" style="color:#FF4000">Firmas</legend><hr/>
            <div class="row col-md-12 col-xs-12 mb-2">
                <fieldset class="col-md-4 col-xs-4">
                ' . $this->ponInput($administrativo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                ' . $this->ponInput($titular) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4 nom_secretario">
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-2">
                <fieldset class="col-md-4 col-xs-4">
                ' . $this->ponInput($cargoAdmitivo) . '
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                ' . $this->ponInput($cargoTitular) . '
                </fieldset>
            </div>
        ';

        $varInversion2 = $varInversion1 = '';
        switch ($this->prm->tipo) {
            case 'F':
                $varInversion1 = '
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($obracontrato) . '
                    </fieldset>
                ';
                $varInversion2 = '
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($vicios) . '
                    </fieldset>
                ';
                break;
            case 'G':
                break;
            case 'H':
                $varInversion1 = '
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($obracontrato) . '
                    </fieldset>
                ';
                $varInversion2 = '
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($anticipo) . '
                    </fieldset>
                ';
                break;
        }

        $obras = '
            <legend align="center" style="color:#FF4000">Datos de la Obra</legend>
            <div class="row col-md-12 col-xs-12 mb-2">
                ' . $varInversion1 . '
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($avancefisico) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-2">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($avancefinanciero) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                ' . $this->ponInput($fianza) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12 mb-2">
                ' . $varInversion2 . '
                ' . $varDocumentacion . '
            </div>
        ';

        if ($this->prm->tipo == 'I') {
            $obras = '
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-6 col-xs-6">
                        ' . $this->ponInput($avancefinanciero) . '
                    </fieldset>
                </div>

                <div class="row col-md-12 col-xs-12">
                    ' . $varDocumentacion . '
                </div>
                <br>
            ';
        }

        $obras2 = '
            <legend align="center" style="color:#FF4000">Información de la  Obra</legend>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 col-xs-12">
                    ' . $this->ponInput($obra) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($referencia) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($NumObra) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($oficioAutorizacion) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($subfuente) . '
                </fieldset>
            </div>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($aportacion) . '
                </fieldset>
                <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($ejercicio) . '
                </fieldset>
            </div>
        ';

        switch ($this->prm->tipo) {
            case 'A':{
                $cveFin->filtro = 'presupuesto';
                if ($abc == 'a') {
                    $cveFin->inicial = true;
                } else {
                    $cveFin->lectura = true;
                }

                $nomina = $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = $firmaSecretarioAdm;
                $firmas = $firmasClc;
                $obras = '';
            }
                break;
            case 'B':{
                $cveFin->filtro = 'presupuesto';
                $cveFin->tipo = $this->prm->tipo;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
            }
                break;
            case 'C':{
                if ($abc == 'a') {
                    $cveFin->inicial = true;
                } else {
                    $cveFin->lectura = true;
                }

                $cveFin->tipo = $this->prm->tipo;
                $cveFin->filtro = 'PagosATerceros';
                $nomina = $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = $firmaSecretarioAdm;
                $firmas = $firmasObra;
                $obras = '';
            }
                break;
                /* ACUERDOS DE MINISTRACION */
            case 'D':{
                if ($abc == 'a') {
                    $cveFin->inicial = true;
                } else {
                    $cveFin->lectura = true;
                }

                $cveFin->filtro = 'ministracion';
                $nomina = $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
            }
                break;
                /* Anticipos / Fondo rotatorio */
            case 'E':{
                if ($abc == 'a') {
                    if ($abc == 'a') {
                        $cveFin->inicial = true;
                    } else {
                        $cveFin->lectura = true;
                    }
                }

                $cveFin->filtro = 'general';
                $nomina = $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
                $cveFin->tipo = $this->prm->tipo;
                $cveFin->stc_id = $this->prm->stc_id;
            }
                break;
                /* INVERSIÓN PUBLICA */
            case 'F':
            case 'G':
            case 'H':{
                $pago = $DatosBanco;
                $obras = $obras;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasObra;
            }
                break;

                /* MUNICIPIOS */
            case 'I':
                $cveFin->filtro = 'presupuesto';
                $nomina .= $SubtipoClc;
                $pago = $DatosBanco;

                $beneficiario = '
                <legend align="center" style="color:#FF4000">Beneficiario</legend>
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-12 col-xs-12 benef2 mb-2" style="' . (($abc == 'c') ? 'display:block;' : 'display:none;') . '">
                    ' . $this->ponInput($beneficiarioMun) . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12 mb-4">
                    <fieldset class="col-md-12 col-xs-12 benef1">
                    </fieldset>
                </div>
                ';
                $this->mixScript('
                    $("#stc_id").change(function(){
                        var stc = parseInt($("#stc_id").val());

                        comunica("' . $this->pantalla . '",{
                            metodo:"modalidadPago",
                            ucb_id:$("input[name=modC]").val(),
                            stc_id:$(this).val(),
                            tipo:"' . $this->prm->tipo . '",
                            ' . ((isset($this->prm->clc_id) && $this->prm->clc_id != '') ? 'clc_id:' . $this->prm->clc_id . ',' : '') . '
                            ' . ((isset($this->prm->gpo_id) && $this->prm->gpo_id != '') ? 'gpo_id:' . $this->prm->gpo_id . ',' : '') . '
                            ' . ((isset($this->prm->ur_id) && $this->prm->ur_id != '') ? 'ur_id:' . $this->prm->ur_id . '' : '') . '
                        });

                        if (stc == 4) {
                            $("#datosAdicionalesTipoI04").show();
                        }
                        else {
                            $("#datosAdicionalesTipoI04").hide();
                        }

                        switch(stc){
                            case 1:
                                $(".benef2").css("display","none");
                                $(".benef1").html(\'' . $this->ponInput($beneficiarioP) . '\');
                                break;
                            case 4:
                                $(".benef2").css("display","none");
                                $(".benef1").html(\'' . $this->ponInput($beneficiarioP) . '\');
                                $("#beneficiarioP").val($("#comboue option:selected").text().substr(6));

                                comunica("' . $this->pantalla . '",{
                                        metodo: "cargaDatosObra",
                                        abc: "' . $abc . '",
                                        tipo: "' . $this->prm->tipo . '",
                                        stc_id: $(this).val()
                                });

                                break;
                            case 2:
                                $(".benef1").html("");
                                $(".benef2").css("display","block");
                                break;
                        }
                    });
                ');
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '
                    <div id="datosAdicionalesTipoI04" style="display: none;">
                        ' . $obras2 . $obras . '
                    </div>
                ';
                break;
                /* DEUDA PUBLICA */
            case 'J':{

                $cveFin->filtro = 'presupuestoUe';
                $nomina .= $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas .= '
                <legend align="center" style="color:#FF4000">Firmas que debe tener la CLC</legend>
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($adminJ) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($cargoadminJ) . '
                    </fieldset>
                </div>
                <div class="row col-md-12 col-xs-12">
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($tituj) . '
                    </fieldset>
                    <fieldset class="col-md-6 col-xs-6">
                    ' . $this->ponInput($cartituj) . '
                    </fieldset>
                </div>
                ';
                $obras = '';
            }
                break;
                /* BURSATILIZACION */
            case 'K':{
                $cveFin->filtro = 'presupuestoUe';
                $cveFin->tipo = $this->prm->tipo;
                $nomina .= $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
            }
                break;
                /* TRANSFERENCIAS */
            case 'L':{
                $cveFin->filtro = 'presupuestoUe';
                $nomina .= $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
            }
                break;
                /* PAGOS A TERCEROS OTROS */
            case 'M':{
                $cveFin->filtro = 'general';
                $nomina .= $SubtipoClc;
                $pago = $DatosBanco;
                $beneficiario = $beneficiario;
                $firmaSecretarioAdm = '';
                $firmas = $firmasClc;
                $obras = '';
                $cveFin->tipo = $this->prm->tipo;
                $cveFin->stc_id = $this->prm->stc_id;
            }
                break;
        }

        $cveFin->selecciona = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : '';
        $mensajes = $Garantias = '';
        $docG = '';
        if ($this->prm->tipo == 'F') {
            if ($obr->getOrigen() == 1) {
                $docG = 'checked';
            }
            $Garantias = '
                <div class="row col-md-12 col-xs-12">
                    <fieldset  class="col-md-4 col-xs-4 mb-2">
                        <input ' . $docG . ' type="checkbox" class="habilitar me-2" id="DocGarantias" value="1"><b><font color="#006CD9">
                        Garantias enviadas en ejercicios anteriores<b></font>
                    </fieldset>
                    <fieldset  class="col-md-8 col-xs-8 mb-2">
                        <font color="#D90000">"Recuerde que las Afectaciones deben estar Autorizadas para poder visualizar los Contratos"<b></font>
                    </fieldset>
                </div>
            ';
        }

        switch ($this->prm->tipo) {
            case 'F':
            case 'G':
            case 'H':
                $mensajes = '
                    <div class="row col-md-12 col-xs-12 mb-4">
                        <fieldset  class="col-md-8 col-xs-8">
                            <font color="#D90000"><b>"PARA QUE SE MUESTREN LAS CLAVES DE FINANCIAMIENTO PRIMERO DEBE SELECCIONAR EL ENCUADRE PROGRAMÁTICO"<b></font>
                        </fieldset>
                    </div>
                ';

                break;
            case 'B':
                $mensajes = '';
                break;
            default:
                $mensajes = '
                    <div class="row col-md-12 col-xs-12 mb-4">
                        <fieldset  class="col-md-8 col-xs-8">
                            <font color="#D90000"><b>"PARA QUE SE MUESTREN LAS CLAVES DE FINANCIAMIENTO  PRIMERO DEBE SELECCIONAR EL SUBTIPO DE CLC"<b></font>
                        </fieldset>
                    </div>
                ';
                break;
        }

        $html .= ' ' . ClasificacionFinanciamiento::creaComboCveFin($cveFin) . ' ';
        $html .= '<legend align="center" style="color:#FF4000">Datos generales</legend><hr/>';
        $html .= $Garantias; // ES B vacio
        $html .= $mensajes;
        $html .= '
            <div class="row col-md-12 col-xs-12 mb-4">
            <fieldset  class="col-md-2 col-xs-2">
                ' . $this->ponInput($fechacreacion) . '
            </fieldset>
        ';

        switch ($this->prm->tipo) {
            case 'F':
            case 'G':
            case 'H':
                $html .= '
                    <fieldset  class="col-md-3 col-xs-3">
                        ' . $this->ponCombo($finiquito) . '
                    </fieldset>
                    <fieldset class="col-md-4 col-xs-4" id="BancoContratos" style="z-index:999999;">
                    </fieldset>
                ';
                break;
        }

        if ($abc == 'c') {
            $input = '<input id="inputRect" type="text" class="form-control" value="' . $clc->getRectificacion() . '">';
            $this->mixScript('
                $("#inputRect").attr("readonly", true);
                $("#clcRect").attr("disabled", true);
            ');
            $checked = 'checked = true';
        } else {
            $input = '';
            $checked = '';
        }

        $html .= '
            <div class="col-md-3 col-xs-3 mt-3">
                <div class="input-group" id="checkAjuste">
                    <span class="btn btn-outline-secondary">
                        <input type="checkbox" id="clcRect" ' . $checked . ' class="me-1"><b>CLC de Rectificación</b>
                    </span>
                    ' . $input . '
                </div>
            </div>
        ';

        $this->mixScript('
            $("#clcRect").click(function(){
                if ($("#clcRect").is(":checked")) {
                    $("#checkAjuste").append(
                        $("<input/>").attr("id","inputRect").attr("type","text").addClass("form-control")
                        .blur(function(){
                            comunica("' . $this->pantalla . '",{
                                "metodo":"ValidarRectificacion",
                                rectificacion : $("#inputRect").val()
                            });
                        })
                    );
                } else {
                    $("#inputRect").remove();
                }
            });
        ');

        $html .= '
            <fieldset  class="col-md-2 col-xs-2">
                    ' . $numclc . '
            </fieldset>
            </div>';

        switch ($this->prm->tipo) {
            case 'F':
            case 'G':
            case 'H':
                $html .= '
                    <div class="row col-md-12 col-xs-12 mb-2">
                        <fieldset class="col-md-12 col-xs-12">
                        ' . $estimacion . '
                        </fieldset>
                    </div>
                    <div class="row col-md-12 col-xs-12 mb-4">
                        <fieldset class="col-md-10 col-xs-10">
                        ' . $this->ponTextarea($conceptoObra) . '
                        </fieldset>
                    </div>
                ';
                break;
            case 'I':
                if ($claveUR == '902') {
                    $html .= '
                        <div class="row col-md-12 col-xs-12">
                            <fieldset class="col-md-12 col-xs-12">
                            ' . $estimacion . '
                            </fieldset>
                        </div>
                        <div class="row col-md-12 col-xs-12">
                            <fieldset class="col-md-10 col-xs-10">
                            ' . $this->ponTextarea($conceptoObra) . '
                            </fieldset>
                        </div>
                        <br>
                    ';
                } else {
                    $html .= '
                        <div class="row col-md-12 col-xs-12 mb-2">
                            <fieldset class="col-md-10 col-xs-10">
                            ' . $this->ponTextarea($concepto) . '
                            </fieldset>
                        </div>
                        <br>
                    ';
                }
                break;

            default:
                $html .= '
                    <div class="row col-md-12 col-xs-12 mb-4">
                        <fieldset class="col-md-10 col-xs-10">
                        ' . $this->ponTextarea($concepto) . '
                        </fieldset>
                    </div>
                ';
                break;
        }

        $html .= $nomina;
        $html .= $cheque;
        $html .= '<div id="modPago_tipoCta"></div>';
        $html .= $pago;
        $html .= $obras;
        $html .= $beneficiario;
        $html .= $firmaSecretarioAdm;
        $html .= $firmas;
        $html .= '
            <br>
            <div class="row col-md-12 col-xs-12">
                <fieldset class="col-md-12 botones">
                </fieldset>
            </div>
            <br>
        ';

        $this->mixHtml($html);
        $this->agregaTools(' ' . $this->ponBoton($btnRegresar) . ' ' . $guardar . ' ' . $eliminar . '');

        /** @JORDY Validación para mostrar el boton de las afecatciones, si no corresponde al mes no se pone */

        if ($abc == 'c' && date('n') == (substr($clc->getFechaElaboracion(), 3, 2))) {
            $this->mixHtml('<div class="row"><div align="center">' . $this->ponBoton($btnAfectaciones) . '</div></div>');
        };
        /** ---------------------------------------------------------------------------------------- */
        if ($abc == 'c') {
            $this->mixHtml('
                <table class="table table-striped">
                <div class="row">
                <div class="col-md-6">
                <legend style="color:#FF4000">Afectaciones de la CLC</legend>
                <div  id="clcafectaciones">');
            $this->TablaClcAfectacion(false);
        }
        $this->mixScript('
                $("#afectacion").attr("ecf_id","' . $this->prm->ecf_id . '");
            ');
        $this->mixHtml('    </div>');
        if ($abc == 'c') {
            $this->mixHtml('</div>
                <div class="col-md-6">
                <legend style="color:#FF4000">Listado de Afectaciones</legend>
                <div  id="listAfectaciones"></div>
                </div>
                </div>
                </table>
                ');
        }

        $this->mixScript($clicCuenta . '
            /* EN ESTA PARTE CUANDO SE SELECCIONA LA MODALIDAD SI ES DE HABERES EL NUMERO 6 SE OCULTAN PARA LAS UR´S QUE NO TIENEN LA DISPONIBILDIAD DE LA MISMA */

            $("#bur_id").change();

            if($("#clc_firmas").is(":checked")){
                $(".nom_secretario").html(\'' . $this->ponInput($secretario) . '\')
            }else if(!$("#clc_firmas").is(":checked")){
                $(".nom_secretario").html("");
            }

            $("#clc_firmas").click(function(){
                if($("#clc_firmas").is(":checked")){
                    $(".nom_secretario").html(\'' . $this->ponInput($secretario) . '\')
                }else if(!$("#clc_firmas").is(":checked")){
                    $(".nom_secretario").html("");
                }
            });

        ');

        /* CUANDO EDITAMOS ENVIAMOS LA VARIABLE SI ES QUE EXISTE Y SI NO LA DEJAMOS EN '' */
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? 'obj.clc_id = ' . $this->prm->clc_id . '; ' : '';
        $document = (isset($claveUR) && $claveUR == '902') ? 'obj.obr_documentacion = $("#documentacion").val(); ' : '';
        $ecf_id = (isset($this->prm->ecf_id) && $this->prm->ecf_id != '') ? 'obj.ecf_id = $(this).attr("ecf_id"); ' : 'obj.ecf_id = $("#cveFin_id").val();';
        $obr_id = (isset($this->prm->obr_id) && $this->prm->obr_id != '') ? 'obj.obr_id = ' . $this->prm->obr_id . '; ' : '';
        $obra = (isset($this->prm->obr_id) && $this->prm->obr_id != '') ? ' "obra": ' . $this->prm->obr_id . ' ' : '';
        if ((isset($this->prm->smn_id) && $this->prm->smn_id != '')) {
            $smn_id = 'obj.smn_id = ' . $this->prm->smn_id . '; ';
        } else {
            $smn_id = (isset($grupo) && $grupo == 1) ? 'if( $("#smn_id").length){ obj.smn_id = $("#smn_id").val(); }else{ obj.smn_id = 0; } ' : 'obj.smn_id = 0;';
        }
        $mno_id = (isset($grupo) && $grupo == 1) ? 'if( $("#mno_id").length){ obj.mno_id = $("#mno_id").val(); }else{ obj.mno_id = 0; } ' : 'obj.mno_id = 0;';

        $pdp = '';

        // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
        // se agrega en la misma consulta que el pago directo a proveedor ticket 638123
        if (isset($this->prm->mpc_id) && ($this->prm->mpc_id == 5 || $this->prm->mpc_id == 4)) {
            $pdp = '$("input[name=tipoC]").attr("disabled", "true");
                $("input[name=tipo_Pago]").attr("disabled", "true");

                $("#benecap").attr("readonly","readonly");
                $("#beneur").attr("readonly","readonly");
                $("#inputRect").remove();
                $("#clcRect").prop("checked",false);
                $("#clcRect").attr("disabled",true);';

            if ($this->prm->tipo == 'K') {
                $pdp .= '
                    $("#divbeneburpdp").show();
                    $("#divbenebur").hide();
                    $("#ben_nombre").removeAttr("required");';
            }
            if ($this->prm->tipo == 'I') {
                $pdp .= '$("#municipio").attr("readonly","readonly");';
            }
        }

        if (isset($this->prm->tipo) && $this->prm->tipo == 'I') {
            // si es tradicional, spei, y propia se bloquea bloquea el nombre del beneficiario.
            $this->mixScript('
                $("input[name=tipoC],input[name=modP],input[name=tipo_Pago]").change(function () {
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        $("#municipio").attr("readonly","readonly");
                    } else {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").removeAttr("readonly");
                        }
                    }
                });

                $(document).ready(function (){
                    var tipoM = $("input[name=modP]:checked").val();
                    var tipoP = $("input[name=tipo_Pago]:checked").val();
                    var tipoFil = $("input[name=tipoC]:checked").val();
                    var subClc = $("#stc_id").val();
                    if (tipoM == 1 && tipoP == 1 && tipoFil == 1 && subClc == 1) {
                        if (tipoM != 5 && tipoM != 4) {
                            $("#municipio").attr("readonly","readonly");
                        }
                }});
            ');
        }

        $this->mixScript('
            ' . $pdp . '
            function guardar(){
                var obj = {"metodo":"abcClc"};
                obj.abc = "' . $abc . '";
                fecha = ' . ($abc == 'c' ? '$("#fechacreacion").val()' : "'now()'") . ';

                switch ("' . $this->prm->tipo . '") {
                    case "A" :
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.ecl_id =1;
                        obj.ocl_id =1;
                        obj.tipoCereCo =1;
                        obj.fechaElaboracion = fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        ' . $clc_id . '
                        ' . $smn_id . '
                        obj.beneficiario = $("#benecap").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.tcl_id ="A";
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.nombreAdministracion = (($("#secretario").length) ? $("#secretario").val():"");
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        obj.stc_id = $("#stc_id").val();
                        obj.tno_id = $("#tno_id").val();
                        ' . $mno_id . '
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                            break;
                            case "2":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.lineaCaptura = $("#linea").val();
                            break;
                            case "3":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            break;
                            case "4":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            break;
                            case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "B":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.ecl_id =1;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.tipoCereCo =1;
                        obj.beneficiario = $("#benecap").val();
                        obj.tcp_id = $("#tcp_id").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.stc_id =0;
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="B";
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.nombreAdministracion = (($("#secretario").length) ? $("#secretario").val():"");
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);

                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago =  "S";
                                break;
                            case "5":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago =  "S";
                                break;
                        }

                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "C":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =2;
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#stc_id").val();
                        obj.beneficiario = $("#benecap").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="C";
                        obj.nombreAdministrativo = ($("#administrativo").length) ? $("#administrativo").val() : $("#presidente").val();
                        obj.cargoAdministrativo = ($("#cargoAdmitivo").length) ? $("#cargoAdmitivo").val() : $("#presidentecargo").val();
                        obj.nombreTitular = ($("#titular").length) ? $("#titular").val() : $("#tesorero").val();
                        obj.cargoTitular = ($("#cargoTitular").length) ? $("#cargoTitular").val() : $("#tesorerocargo").val();
                        obj.nombreAdministracion = (($("#secretario").length) ? $("#secretario").val():"");
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if(!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "D":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.ecl_id =1;
                        obj.tipoCereCo =2;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#stc_id").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="D";
                        obj.mpc_id = $("input[name=modP]:checked").val();
                        obj.mpc_id = $("input[name=modP]:checked").val();
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.beneficiario = $("#beneur").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        if ($("input[name=modP]:checked").val()==5) {
                            obj.formaPago =  "S";
                        } else {
                            obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");

                        return false;
                        break;
                    case "E":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =2;
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#stc_id").val();
                        obj.beneficiario = $("#benecap").val();
                        obj.ecf_id = $("#cveFin_id :selected").attr("ecf_id");
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        obj.tcl_id ="E";
                        obj.mpc_id = $("input[name=modP]:checked").val();
                        obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "F":
                        if ($("#estimacion").length) {
                            obj.concepto = $("#estimacion").val() +" "+  $("#conceptoObra").val();
                        } else {
                            obj.concepto = $("#conceptoObra").val();
                        }
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        ' . $obr_id . '
                        obj.ecl_id =1;
                        obj.tipoCereCo =1;
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#finiquito").val();
                        if ($("#finiquito").val()!=3) {
                            obj.ipu_id = $("#ipu_id").val();
                        }
                        obj.Garantias=$("#DocGarantias").is(":checked");
                        obj.obr_contrato = $("#obracontrato").val();
                        obj.obr_avanceFisico = $("#avancefisico").val();
                        obj.obr_avanceFinanciero = $("#avancefinanciero").val();
                        obj.obr_cumplimiento = $("#fianza").val();
                        obj.obr_vicios = $("#vicios").val();
                        ' . $document . '
                        obj.nombreAdministrativo = ($("#administrativo").length) ? $("#administrativo").val() : $("#presidente").val();
                        obj.cargoAdministrativo = ($("#cargoAdmitivo").length) ? $("#cargoAdmitivo").val() : $("#presidentecargo").val();
                        obj.nombreTitular = ($("#titular").length) ? $("#titular").val() : $("#tesorero").val();
                        obj.cargoTitular = ($("#cargoTitular").length) ? $("#cargoTitular").val() : $("#tesorerocargo").val();
                        obj.beneficiario = ($("#beneur").length) ? $("#beneur").val() : $("#municipio").val();
                        obj.ecf_id = $("#cveFin_id option:selected").attr("ecf_id");
                        obj.firmas =($("#beneur").length) ? 2 : 2;
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        obj.tcl_id ="F";
                        obj.origen = ($("#DocGarantias").is(":checked")) ? 1 : 0 ;
                        obj.mpc_id = $("input[name=modP]:checked").val();
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                            break;
                            case "2":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.lineaCaptura = $("#linea").val();
                            break;
                            case "3":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            break;
                            case "4":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            break;
                            case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "G":
                        if($("#estimacion").length)
                        obj.concepto = $("#estimacion").val() +" "+  $("#conceptoObra").val();
                        else
                        obj.concepto = $("#conceptoObra").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =1;
                        if ($("#finiquito").val()!=0) {
                            obj.ipu_id = $("#ipu_id").val();
                        }
                        ' . $obr_id . '
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#finiquito").val();
                        if ($("#finiquito").val()!=0) {
                            obj.ipu_id = $("#ipu_id").val();
                        }
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.obr_avanceFisico = $("#avancefisico").val();
                        obj.obr_avanceFinanciero = $("#avancefinanciero").val();
                        obj.obr_cumplimiento = $("#fianza").val();
                        ' . $document . '
                        obj.beneficiario = ($("#beneur").length) ? $("#beneur").val() : $("#municipio").val();
                        obj.nombreAdministrativo = ($("#administrativo").length) ? $("#administrativo").val() : $("#presidente").val();
                        obj.cargoAdministrativo = ($("#cargoAdmitivo").length) ? $("#cargoAdmitivo").val() : $("#presidentecargo").val();
                        obj.nombreTitular = ($("#titular").length) ? $("#titular").val() : $("#tesorero").val();
                        obj.cargoTitular = ($("#cargoTitular").length) ? $("#cargoTitular").val() : $("#tesorerocargo").val();
                        obj.ecf_id = $("#cveFin_id option:selected").attr("ecf_id");
                        obj.firmas =($("#beneur").length) ? 2 : 2;
                        obj.tcl_id ="G";
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        obj.mpc_id = $("input[name=modP]:checked").val();
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                                case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }

                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "H":
                        if ($("#estimacion").length) {
                            obj.concepto = $("#estimacion").val() +" "+  $("#conceptoObra").val();
                        } else {
                            obj.concepto = $("#conceptoObra").val();
                        }
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =2;
                        ' . $obr_id . '
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        obj.stc_id = $("#finiquito").val();
                        if ($("#fbeneurbenecapiniquito").val()!=01) {
                            obj.ipu_id = $("#ipu_id").val();
                        }
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.obr_avanceFisico = $("#avancefisico").val();
                        obj.obr_avanceFinanciero = $("#avancefinanciero").val();
                        obj.obr_contrato = $("#obracontrato").val();
                        obj.obr_cumplimiento = $("#fianza").val();
                        obj.obr_anticipo = $("#anticipo").val();
                        ' . $document . '
                        obj.nombreAdministrativo = ($("#administrativo").length) ? $("#administrativo").val() : $("#presidente").val();
                        obj.cargoAdministrativo = ($("#cargoAdmitivo").length) ? $("#cargoAdmitivo").val() : $("#presidentecargo").val();
                        obj.nombreTitular = ($("#titular").length) ? $("#titular").val() : $("#tesorero").val();
                        obj.cargoTitular = ($("#cargoTitular").length) ? $("#cargoTitular").val() : $("#tesorerocargo").val();
                        obj.beneficiario = ($("#beneur").length) ? $("#beneur").val() : $("#municipio").val();
                        obj.ecf_id = $("#cveFin_id option:selected").attr("ecf_id");
                        obj.firmas =($("#beneur").length) ? 2 : 2;
                        obj.tcl_id ="H";
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                                case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "I":
                        if ($("#stc_id").val() == 4) {
                            if ($("#estimacion").length) {
                                obj.concepto = $("#estimacion").val() +" "+  $("#conceptoObra").val();
                            } else {
                                obj.concepto = $("#conceptoObra").val();
                            }
                        }
                        else {
                            obj.concepto = $("#concepto").val();
                        }
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        ' . $clc_id . '
                        ' . $obr_id . '
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.obr_avanceFinanciero = $("#avancefinanciero").val();
                        ' . $document . '
                        obj.ocl_id =1;
                        if ($("#stc_id").val()==1 || $("#stc_id").val()==3 || $("#stc_id").val()==4) {
                            obj.tipoCereCo =1;
                        }
                        else {
                            obj.tipoCereCo =2;
                        }
                        obj.stc_id = $("#stc_id").val();
                        obj.beneficiario = ($("#beneficiarioP").length) ? $("#beneficiarioP").val() : $("#municipio").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.tcl_id ="I";
                        switch ($("input[name=modP]:checked").val()){
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                                case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "J":
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        if ($("#stc_id").val()==1) {
                            obj.tipoCereCo =1;
                        } else {
                            obj.tipoCereCo =2;
                        }
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        ' . ((isset($_SESSION[_S]['clasAdm-ue'])) ? 'obj.ue_id=' . $_SESSION[_S]['clasAdm-ue'] . ';' : '') . '
                        obj.stc_id = $("#stc_id").val();
                        obj.referencia = $("#referenciaC").val();
                        obj.nombreAdministrativo = $("#adminJ").val();
                        obj.cargoAdministrativo = $("#cargoadminJ").val();
                        obj.nombreTitular = $("#tituj").val();
                        obj.cargoTitular = $("#cartituj").val();
                        obj.beneficiario = $("#benecap").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="J";
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                                case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "K":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        if ($("#stc_id").val()==1) {
                            obj.tipoCereCo =1;
                        } else {
                            obj.tipoCereCo =2;
                        }
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        ' . ((isset($_SESSION[_S]['clasAdm-ue'])) ? 'obj.ue_id=' . $_SESSION[_S]['clasAdm-ue'] . ';' : '') . '
                        obj.stc_id = $("#stc_id").val();
                        obj.referencia = $("#referenciaC").val();
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.beneficiario = $("#ben_nombre").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="K";
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                                case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            obj.beneficiario = $("#beneburpdp").val();
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "L":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =1;
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        ' . ((isset($_SESSION[_S]['clasAdm-ue'])) ? 'obj.ue_id=' . $_SESSION[_S]['clasAdm-ue'] . ';' : '') . '
                        obj.stc_id = $("#stc_id").val();
                        obj.referencia = $("#referenciaC").val();
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.beneficiario = $("#benecap").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="L";
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }
                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                    case "M":
                        obj.concepto = $("#concepto").val();
                        obj.bur_id = $("#bur_id").val();
                        obj.fechaElaboracion =fecha;
                        obj.tareaclc ="' . $this->prm->tareaclc . '";
                        obj.ecl_id =1;
                        obj.tipoCereCo =2;
                        ' . $clc_id . '
                        obj.ocl_id =1;
                        ' . ((isset($_SESSION[_S]['clasAdm-ue'])) ? 'obj.ue_id=' . $_SESSION[_S]['clasAdm-ue'] . ';' : '') . '
                        obj.stc_id = $("#stc_id").val();
                        obj.referencia = $("#referenciaC").val();
                        obj.nombreAdministrativo = $("#administrativo").val();
                        obj.cargoAdministrativo = $("#cargoAdmitivo").val();
                        obj.nombreTitular = $("#titular").val();
                        obj.cargoTitular = $("#cargoTitular").val();
                        obj.beneficiario = $("#benecap").val();
                        obj.ecf_id = $("#cveFin_id").val();
                        obj.firmas = ($("#clc_firmas").is(":checked")) ? 3 : 2;
                        obj.tcl_id ="M";
                        obj.rectificacion = ($("#inputRect").val()!="" ? $("#inputRect").val():0 );
                        obj.ajuste = (($("#inputAjuste").length) ? $("#inputAjuste").val():0 );
                        obj.fechaAjuste = (($("#fechaAjuste").length) ? $("#fechaAjuste").val():null);
                        switch ($("input[name=modP]:checked").val()) {
                            case "1":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.formaPago = ($("input[name=tipo_Pago]:checked").val()==1)? "S":"P";
                                break;
                            case "2":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                obj.lineaCaptura = $("#linea").val();
                                break;
                            case "3":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "4":
                                obj.mpc_id = $("input[name=modP]:checked").val();
                                break;
                            case "5":
                            obj.mpc_id = $("input[name=modP]:checked").val();
                            obj.formaPago =  "S";
                            break;
                        }

                        if (!comunica("' . $this->pantalla . '", obj,true ))
                        $(this).removeAttr("disabled");
                        return false;
                        break;
                }

            }
            $("#guardar").click(guardar);

            $("#delete").click(function(){
                var elim= alertify.confirm("ELIMINAR COMPROMISO","Estas seguro de Eliminar la CLC ' . ($clc->getClc_id()) . ' ",
                    function(){ 
                        comunica("' . $this->pantalla . '",{
                        metodo:"abcClc",
                        abc:"b",
                        "clc_id":"' . ($clc->getClc_id()) . '",
                        "tareaclc":"' . $this->prm->tareaclc . '",
                        "tipo":"' . $this->prm->tipo . '",
                        "tcl_id":"' . $this->prm->tipo . '",
                        "stc_id":$("#stc_id").val(),
                        "ecf_id":$("#cveFin_id").val(),
                        ' . $obra . '
                        });
                    },
                    undefined
                ).set("defaultFocus","cancel")
                .set("labels", {
                    ok: "Aceptar", cancel: "Cancelar"
                });
                const btnAceptar = document.querySelector(".ajs-button.btn.btn-primary");
                if (btnAceptar) {
                    btnAceptar.classList.remove("btn-primary");
                    btnAceptar.classList.add("btn-outline-success");
                }
                    const cancela = document.querySelector(".ajs-button.btn.btn-danger");
                if (cancela) {
                    cancela.classList.remove("btn-danger");
                    cancela.classList.add("btn-outline-danger");
                }
                return false;
            });

            $("#regresar").click(function(){
                $("nav").menusinpres("quitaBreadcrumbs");
                comunica("' . $this->pantalla . '",{
                    metodo:"PantallaInicial",
                    tipo:"' . $this->prm->tipo . '",
                    tareaclc:"' . $this->prm->tareaclc . '"
                });
            });

            /* SOLO FUNCIONBA CUANDO ES EDITAR YA QUE TENEMOS UN NUMERO DE CLC Y CON EL CLICK MOSTRAMOS LAS AFECTACIONES DISPONIBLES PARA EL TIPO DE CLC */
            $("#afectacion").click(function(){
                var obj = {"metodo":"TablaAfectaciones"};
                obj.tipo = "' . $this->prm->tipo . '";
                obj.tno_id = "' . $this->prm->tno_id . '";
                obj.mno_id = "' . $this->prm->mno_id . '";
                obj.smn_id = "' . $this->prm->smn_id . '";
                obj.tcp_id = "' . $this->prm->tcp_id . '";
                obj.bur_id = "' . $this->prm->bur_id . '";
                obj.mpc_id = "' . $this->prm->mpc_id . '";
                obj.clc_fechaElaboracion = "' . $clc->getFechaElaboracion() . '";
                obj.clc_id = "' . $clc->getClc_id() . '";
                obj.clc_fechaAjuste = "' . $this->prm->clc_fechaAjuste . '";
                obj.clc_rectificacion = "' . $this->prm->clc_rectificacion . '";
                obj.grupo = ' . $grupo . ';
                obj.abc = "' . $abc . '";
                obj.stc_id = ' . ((isset($this->prm->stc_id) && $this->prm->stc_id != '') ? $this->prm->stc_id : '') . '
                ' . $clc_id . '
                ' . $ecf_id . '
                if (!comunica("' . $this->pantalla . '", obj,true ))
                $(this).removeAttr("disabled");
                return false;
            });
        ');
        $this->mixScript('
            $("#edo_id").change(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"CargaMunicipio",
                    edo_id:$("#edo_id").val()
                });

            });
        ');

        if ($abc = 'c' && $this->prm->tipo == 'I' && $clc->getStc_id() == '04') {
            $this->mixScript('
                $("#stc_id").change();
            ');
        }
    }

    public function abcClc()
    {
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->prm->gpo_id = $this->gpo_id;
        $this->prm->ur_id = $this->ur_id;

        if (isset($this->prm->concepto) && $this->prm->concepto != '') {
            $this->prm->concepto = str_replace("'", "\\'", $this->prm->concepto);
        }
        if (isset($this->prm->beneficiario) && $this->prm->beneficiario != '') {
            $this->prm->beneficiario = str_replace("'", "\\'", $this->prm->beneficiario);
        }

        if ($this->prm->abc != 'b') {
            if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == '116') {
                if ($_SESSION[_S]['nvj_id'] > 1 && !isset($this->prm->ue_id)) {
                    if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
                        $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                    } else {
                        $this->mixScript('notify("La unidad Ejecutora es obligatoria para su UR","error");');
                        return false;
                    }
                } else {
                    $this->prm->ue_id = $_SESSION[_S]['ue_id'];
                }
            }

            $opc = new stdClass();
            $opc->sis_id = 'S';
            $opc->men_id = '2';
            $opc->opc_id = '2';
            $bloqueo = $this->model->gtBloqueos($this->prm->tcl_id, $this->prm->stc_id, $this->prm->ecf_id, $opc);
            if ($bloqueo->valido == 0) {
                $this->mixScript(' notify("' . $bloqueo->mensaje . ' ","error",false); ');
                return false;
            }

            //consultamos si la cuenta bancaria utilizada en la clc es de fondo rotatorio y la clc es de gastos de operacion, si es asi se marca como fondo rotatorio
            $this->prm->fondoRotatorio = 'N';
            if (isset($this->prm->tcl_id) && $this->prm->tcl_id == 'B') {
                $this->conectar();
                $this->setSql('
                    SELECT bur_fondoRotatorio
                    FROM cat_BancosUr
                    WHERE bur_id= ?
                ');
                $this->consultar([$this->prm->bur_id]);
                $this->desconectar();
                $fondo = $this->extraer();
                $this->prm->fondoRotatorio = $fondo['bur_fondoRotatorio'];
            }

            // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
            // se agrega en la misma funcionalidad del pago directo a proveedor del ticket 638123

            if ($this->prm->mpc_id == 5 || $this->prm->mpc_id == 4) {
                $men = '';
                if ($this->prm->fondoRotatorio != 'N') {
                    switch ($this->prm->mpc_id) {
                        case 5:
                            $men = 'La CLC se está generando por Modalidad de Pago PDP este metodo no funciona con cuentas  de Fondo Rotatorio';
                            break;
                        case 4:
                            $men = 'La CLC se está generando por Modalidad de Cadena Productiva este metodo no funciona con cuentas  de Fondo Rotatorio';
                            break;
                    }
                    $this->mixScript(' notify("' . $men . '","error");');
                    return false;
                }
            }

            $this->conectar();
            $this->setSql('
                SELECT ecf_id, IF(ter_id="B",IF(ISNULL(enc.ecf_id),"1","2"),"2")es,ter_id,pdp_id
                FROM enc_ClaveFinanciamiento
                INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING( cff_id,fte_id, sft_id, ori_id, sor_id)
                LEFT  JOIN(
                    SELECT ecf_id FROM
                    enc_ExcepcionesRecursoFederal
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND tcl_id= :tcl_id
                    AND stc_id= :stc_id
                    AND ecf_id= :ecf_id
                )enc USING(ecf_id)
                WHERE sts_id=1
                AND ecf_id= :ecf_id
            ');
            $men = '';
            $params = [
                ':gpo_id' => $this->prm->gpo_id,
                ':ur_id'  => $this->prm->ur_id,
                ':tcl_id' => $this->prm->tcl_id,
                ':stc_id' => $this->prm->stc_id,
                ':ecf_id' => $this->prm->ecf_id];
            $this->consultar($params);
            $rw = $this->extraer();

            $especialesFederal = ['116', '901', '902'];
            $unidadResp = $_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'];

            if ($this->prm->mpc_id == 5 || $this->prm->mpc_id == 4) {
                if ($rw['pdp_id'] != 1) {
                    switch ($this->prm->mpc_id) {
                        case 5:
                            $men = 'La CLC se está generando por Modalidad de Pago PDP la Clave de Financiamiento que se utiliza no esta disponible para esta Modalidad';
                            break;
                        case 4:
                            $men = 'La CLC se está generando por Modalidad de Cadena Productiva la Clave de Financiamiento que se utiliza no esta disponible para esta Modalidad';
                            break;
                    }
                    $this->mixScript(' notify("' . $men . '","error");');
                    return false;
                }
                $this->prm->formaPago = 'S';
            } else {

                if ((in_array($unidadResp, $especialesFederal))) {
                    $this->prm->recursoFederal = '2';
                } else {
                    $this->prm->recursoFederal = $rw['es'];
                }

                $this->desconectar();

                if (!(in_array($unidadResp, $especialesFederal))) {

                    if ($rw['es'] == 2 && $rw['ter_id'] == 'B') {
                        $men = 'La CLC se está generando con una Clave de Financiamiento de Recurso Federal y el proceso va a hacer normal';
                    } elseif ($rw['es'] == 1 && $rw['ter_id'] == 'B') {
                        if ($this->prm->bur_id != 000000000000000000) {
                            $this->mixScript(' notify("No puedes usar una Cuenta Bancaria, tienes que usar la Cuenta 000000000000000000  ","error"); ');
                            return false;
                        }

                        $men = 'La CLC se está generando con una Clave de Financiamiento de Recurso Federal y el proceso se genera por medio de la UR';
                    }
                }

                if ($men != '') {
                    $this->mixScript('notify("' . $men . '","warning");');
                }
            }
        }

        switch ($this->prm->abc) {
            case 'a':
                if (isset($this->prm->ajuste) and $this->prm->ajuste != '' and $this->prm->ajuste != 0) {
                    if ($this->prm->mpc_id != 3) {
                        $this->mixScript('notify( "El tipo de pago debe ser sin flujo de efectivo ", "error");');
                        break;
                    }
                }

                if (isset($this->prm->rectificacion) and $this->prm->rectificacion != '' and $this->prm->rectificacion != 0) {
                    $sql = '
                        SELECT rct_reintegro
                        FROM scp_MaestroRectificacion
                        WHERE gpo_id= :gpo_id
                        AND  ur_id= :ur_id
                        AND rct_id= :rct_id;
                    ';
                    $d = new AccesoDatos();
                    $d->conectar();
                    $d->setSql($sql);
                    $params = [
                        ':gpo_id' => $this->prm->gpo_id,
                        ':ur_id'  => $this->prm->ur_id,
                        ':rct_id' => $this->prm->rectificacion];
                    $d->consultar($params);
                    $d->desconectar();
                    $dx = $d->extraer();
                    if ($d->getNumeroRegistros() > 0) {
                        if ($dx['rct_reintegro'] == 2) {
                            if ($this->prm->mpc_id != 3) {
                                $this->mixScript('
                                    notify( "El tipo de pago debe ser sin flujo de efectivo ", "error");
                                ');
                                return false;
                            }
                        }
                    }
                }

                switch ($this->prm->tcl_id) {
                    case 'F':
                    case 'G':
                    case 'H':
                        if (($this->prm->tcl_id == 'F' && $this->prm->stc_id != 02) || ($this->prm->tcl_id == 'G' && $this->prm->stc_id == 01) || ($this->prm->tcl_id == 'H' && $this->prm->stc_id == 00)) {
                            if (($this->prm->tcl_id == 'F' && $this->prm->stc_id != 02 && $this->prm->Garantias == 0) || ($this->prm->tcl_id == 'G' && $this->prm->stc_id == 01) || ($this->prm->tcl_id == 'H' && $this->prm->stc_id == 00)) {
                                $contratos = ValidaGarantias::ValidaGarantia($_SESSION[_S]['clasAdm-epg'], $this->prm->tcl_id, $this->prm->stc_id, $this->prm->ipu_id);
                                if (isset($contratos->valido) && $contratos->valido == false) {
                                    switch ($contratos->error) {
                                        case 3:
                                            break;
                                        default:
                                            $this->mixScript(' notify(" ' . $contratos->mensaje . '","warning"); ');
                                            return;
                                            break;
                                    }
                                }
                            }
                        }

                        $this->prm->origen = (isset($this->prm->origen)) ? $this->prm->origen : '0';
                        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '0';
                        $this->prm->contrato = (isset($this->prm->obr_contrato)) ? $this->prm->obr_contrato : ' ';
                        $this->prm->avanceFisico = (isset($this->prm->obr_avanceFisico) && !empty($this->prm->obr_avanceFisico)) ? $this->prm->obr_avanceFisico : '0';

                        $this->prm->avanceFinanciero = (isset($this->prm->obr_avanceFinanciero) && !empty($this->prm->obr_avanceFinanciero)) ? $this->prm->obr_avanceFinanciero : '0';
                        $this->prm->cumplimiento = (isset($this->prm->obr_cumplimiento) && !empty($this->prm->obr_cumplimiento)) ? $this->prm->obr_cumplimiento : ' ';
                        if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 902) {
                            $this->prm->vicios = (isset($this->prm->obr_vicios) && !empty($this->prm->obr_documentacion)) ? $this->prm->obr_vicios : ' ';
                        } else {
                            $this->prm->vicios = (isset($this->prm->obr_vicios) && $this->prm->obr_vicios != '') ? $this->prm->obr_vicios : ' ';
                        }
                        $this->prm->documentacion = (isset($this->prm->obr_documentacion) && !empty($this->prm->obr_documentacion)) ? $this->prm->obr_documentacion : '  ';
                        $this->prm->anticipo = (isset($this->prm->obr_anticipo) && !empty($this->prm->obr_anticipo)) ? $this->prm->obr_anticipo : ' ';
                        $this->prm->fianza = (isset($this->prm->fianza)) ? $this->prm->fianza : '  ';
                        $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
                        $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                        if ($this->prm->epg_id == '' || $this->prm->epg_id == null) {
                            $this->mixScript('notify("Error 0 en el Encuadre Programatico","error");');
                            return;
                        }

                        /* Se ha implementado que si surge un error por un campo indefinido el insert de CLC No se realice mendiante el envio
                        False en la entidad de la segunda table en esta caso de ObraCLC
                         */
                        $this->prm->clc_id = Clc::abc($this->prm);
                        $n = ObraClc::abc($this->prm);

                        if (is_int($n)) {
                            $this->prm->abc = 'b';
                            Clc::abc($this->prm);
                            $this->mixScript('notify(" Error número ' . $n . ', favor de enviar un ticket con el número de error ","error");');
                            return false;
                        }

                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id)
                            VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC GENERADA POR LA UR",INET_ATON(:ip),:usr_id) ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $this->prm->clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);

                        $this->mixScript('
                            notify( "CLC ' . $this->prm->clc_id . ' generada correctamente ", "success");
                            comunica("' . $this->pantalla . '",{
                                metodo:"PantallaInicial",
                                tipo:"' . $this->prm->tcl_id . '",
                                tareaclc:"' . $this->prm->tareaclc . '"
                            });

                        $("' . (($_SESSION[_S]['nvj_id'] == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
                        ');
                        break;
                    case 'I':
                        $clc_id = 0;
                        if ($this->prm->stc_id == '04') {
                            $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
                            $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                            $this->prm->clc_id = Clc::abc($this->prm);
                            $clc_id = $this->prm->clc_id;
                            //Datos para la obra
                            $this->prm->origen = '0';
                            $this->prm->ipu_id = '0';
                            $this->prm->contrato = '0';
                            $this->prm->avanceFisico = '0';
                            $this->prm->avanceFinanciero = (!empty($this->prm->obr_avanceFinanciero)) ? $this->prm->obr_avanceFinanciero : '0';
                            $this->prm->cumplimiento = '0';
                            $this->prm->vicios = '0';
                            $this->prm->documentacion = (!empty($this->prm->obr_documentacion)) ? $this->prm->obr_documentacion : '0';
                            $this->prm->anticipo = '0';
                            $this->prm->fianza = '0';
                            $n = ObraClc::abc($this->prm);
                        } else {
                            $clc_id = Clc::abc($this->prm);
                        }
                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                            VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC GENERADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                        ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);

                        $this->mixScript('
                        notify( "CLC ' . $clc_id . ' generada correctamente ", "success");
                        comunica("' . $this->pantalla . '",{
                        metodo:"PantallaInicial",
                        tipo:"' . $this->prm->tcl_id . '",
                        tareaclc:"' . $this->prm->tareaclc . '"
                        });
                        ');
                        $this->desconectar();
                        break;
                    default:
                        $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                        $clc_id = Clc::abc($this->prm);
                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                            VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC GENERADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                        ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);

                        $this->mixScript('
                        notify( "CLC ' . $clc_id . ' generada correctamente ", "success");
                        comunica("' . $this->pantalla . '",{
                        metodo:"PantallaInicial",
                        tipo:"' . $this->prm->tcl_id . '",
                        tareaclc:"' . $this->prm->tareaclc . '"
                        });
                        ');
                        $this->desconectar();
                        break;
                }
                break;
            case 'c':
                $this->prm->observacion = 'SE ACTUALIZA LA CLC DESDE LA PANTALLA';

                if (isset($this->prm->fechaElaboracion)) {
                    unset($this->prm->fechaElaboracion);
                }

                switch ($this->prm->tcl_id) {
                    case 'F':
                    case 'G':
                    case 'H':
                        $this->prm->origen = (isset($this->prm->origen)) ? $this->prm->origen : '0';
                        $this->prm->ipu_id = (isset($this->prm->ipu_id)) ? $this->prm->ipu_id : '0';
                        $this->prm->contrato = (isset($this->prm->obr_contrato)) ? $this->prm->obr_contrato : ' ';
                        $this->prm->avanceFisico = (isset($this->prm->obr_avanceFisico) && !empty($this->prm->obr_avanceFisico)) ? $this->prm->obr_avanceFisico : '0';

                        $this->prm->avanceFinanciero = (isset($this->prm->obr_avanceFinanciero) && !empty($this->prm->obr_avanceFinanciero)) ? $this->prm->obr_avanceFinanciero : '0';
                        $this->prm->cumplimiento = (isset($this->prm->obr_cumplimiento) && !empty($this->prm->obr_cumplimiento)) ? $this->prm->obr_cumplimiento : ' ';
                        if ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == 902) {
                            $this->prm->vicios = (isset($this->prm->obr_vicios) && !empty($this->prm->obr_documentacion)) ? $this->prm->obr_vicios : ' ';
                        } else {
                            $this->prm->vicios = (isset($this->prm->obr_vicios) && $this->prm->obr_vicios != '') ? $this->prm->obr_vicios : ' ';
                        }
                        $this->prm->documentacion = (isset($this->prm->obr_documentacion) && !empty($this->prm->obr_documentacion)) ? $this->prm->obr_documentacion : '  ';
                        $this->prm->anticipo = (isset($this->prm->obr_anticipo) && !empty($this->prm->obr_anticipo)) ? $this->prm->obr_anticipo : ' ';
                        $this->prm->fianza = (isset($this->prm->fianza)) ? $this->prm->fianza : '  ';
                        $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
                        $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                        if ($this->prm->epg_id == '' || $this->prm->epg_id == null) {
                            $this->mixScript('notify("Error 0 en el Encuadre Programatico","error");');
                            return;
                        }

                        Clc::abc($this->prm);
                        ObraClc::abc($this->prm);

                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                            VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC ACTUALIZADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                        ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $this->prm->clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);
                        $this->desconectar();

                        $this->mixScript('
                            notify( "CLC ' . $this->prm->clc_id . ' Actualizada correctamente ", "success");
                            comunica("' . $this->pantalla . '",{
                                metodo:"PantallaInicial",
                                tipo:"' . $this->prm->tcl_id . '",
                                tareaclc:"' . $this->prm->tareaclc . '"
                            });
                            $("' . (($_SESSION[_S]['nvj_id'] == 1) ? '' : '#clasadminue,') . '#clasadminprg,#clasadminspr,#clasadminpy,#clasadminoa").empty();
                        ');
                        break;
                    case 'I':
                        $clc_id = Clc::abc($this->prm);
                        if ($this->prm->stc_id) {
                            $this->prm->epg_id = $_SESSION[_S]['clasAdm-epg'];
                            $this->prm->ue_id = $_SESSION[_S]['clasAdm-ue'];
                            if (($this->prm->epg_id == '' || $this->prm->epg_id == null) && ($this->prm->gpo_id == '9' && $this->prm->ur_id == '02')) {
                                $this->mixScript('notify("Error 0 en el Encuadre Programatico","error");');
                                return;
                            }
                            $this->prm->avanceFinanciero = (!empty($this->prm->obr_avanceFinanciero)) ? $this->prm->obr_avanceFinanciero : '0';
                            $this->prm->documentacion = (!empty($this->prm->obr_documentacion)) ? $this->prm->obr_documentacion : '0';
                            ObraClc::abc($this->prm);
                        }
                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                            VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC ACTUALIZADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                        ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $this->prm->clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);
                        $this->desconectar();

                        $this->mixScript('
                            notify( "La CLC:  ' . $this->prm->clc_id . '  se actualizó correctamente", "success");
                            comunica("' . $this->pantalla . '",{
                                metodo:"PantallaInicial",
                                tipo:"' . $this->prm->tcl_id . '",
                                tareaclc:"' . $this->prm->tareaclc . '"
                            });
                        ');
                        break;
                    default:

                        $clc_id = Clc::abc($this->prm);
                        $this->conectar();
                        $this->setSql('
                            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                            values (:gpo_id,:ur_id,:clc_id,1,"CLC ACTUALIZADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                        ');
                        $params = [
                            ':gpo_id' => $this->prm->gpo_id,
                            ':ur_id'  => $this->prm->ur_id,
                            ':clc_id' => $this->prm->clc_id,
                            ':ip'     => $ip,
                            ':usr_id' => $user];
                        $this->consultar($params);
                        $this->desconectar();

                        $this->mixScript('
                            notify( "La CLC:  ' . $this->prm->clc_id . '  se actualizó correctamente", "success");
                            comunica("' . $this->pantalla . '",{
                                metodo:"PantallaInicial",
                                tipo:"' . $this->prm->tcl_id . '",
                                tareaclc:"' . $this->prm->tareaclc . '"
                            });
                        ');
                        break;
                }
                break;
            case 'b':

                $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
                $this->prm->tareaclc = (isset($this->prm->tareaclc)) ? $this->prm->tareaclc : '';
                $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';
                $this->conectar();
                $this->setSql('
                    SELECT * FROM scp_Afectacion
                    WHERE gpo_id= :gpo_id
                    AND ur_id = :ur_id
                    AND clc_id= :clc_id
                    limit 1;
                ');
                $params = [
                    ':gpo_id' => $this->prm->gpo_id,
                    ':ur_id'  => $this->prm->ur_id,
                    ':clc_id' => $this->prm->clc_id];
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() != 0) {
                    $this->mixScript('notify( "No puedes eliminar la clc ' . $this->prm->clc_id . ' ya tiene una o mas afectaciones", "success");');
                    $this->main();
                } else {
                    switch ($this->prm->tipo) {

                        case 'F':
                        case 'G':
                        case 'H':
                        case 'I':

                            ObraClc::abc($this->prm);
                            Clc::abc($this->prm);

                            $this->conectar();
                            $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                                VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC ELIMINADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                            ');
                            $params = [
                                ':gpo_id' => $this->prm->gpo_id,
                                ':ur_id'  => $this->prm->ur_id,
                                ':clc_id' => $this->prm->clc_id,
                                ':ip'     => $ip,
                                ':usr_id' => $user];
                            $this->consultar($params);
                            $this->desconectar();

                            $this->mixScript('
                                notify( "Se eliminó exitosamente la CLC: ' . $this->prm->clc_id . '", "success");
                                comunica("' . $this->pantalla . '",{
                                    metodo:"PantallaInicial",
                                    tipo:"' . $this->prm->tipo . '",
                                    tareaclc:"' . $this->prm->tareaclc . '"
                                });
                            ');
                            break;
                        default:
                            Clc::abc($this->prm);
                            $this->conectar();
                            $this->setSql('
                                INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                                values (:gpo_id,:ur_id,:clc_id,1,"CLC ELIMINADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                            ');
                            $params = [
                                ':gpo_id' => $this->prm->gpo_id,
                                ':ur_id'  => $this->prm->ur_id,
                                ':clc_id' => $this->prm->clc_id,
                                ':ip'     => $ip,
                                ':usr_id' => $user];
                            $this->consultar($params);
                            $this->desconectar();

                            $this->mixScript('
                                notify( "Se eliminó exitosamente la CLC: ' . $this->prm->clc_id . '", "success");
                                comunica("' . $this->pantalla . '",{
                                metodo:"PantallaInicial",
                                tipo:"' . $this->prm->tipo . '",
                                tareaclc:"' . $this->prm->tareaclc . '"
                                });
                            ');
                            break;
                    }
                }
                break;
        }
    }

    public function TablaAfectaciones()
    {
        /** @JORDY Validación para mostrar el boton de las afecatciones, si no corresponde al mes no se pone */
        if (date('n') == (substr($this->prm->clc_fechaElaboracion, 3, 2))) {
            $this->setContenedor('listAfectaciones');
            $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';
            $this->prm->mpc_id = (isset($this->prm->mpc_id)) ? $this->prm->mpc_id : '';
            $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
            $this->prm->ecf_id = (isset($this->prm->ecf_id)) ? $this->prm->ecf_id : '';
            $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
            $this->prm->tcp_id = (isset($this->prm->tcp_id)) ? $this->prm->tcp_id : '';
            $this->prm->abc = (isset($this->prm->abc)) ? $this->prm->abc : '';

            $radios = '<td style="padding:4px; text-align:center; font-size:16px;"><input type="checkbox" id="SelectAll"> Seleccionar Todas</input></td>';

            $asignar = new stdClass();
            $asignar->id = 'asignar';
            $asignar->name = 'asignar';
            $asignar->value = 'Asignar';
            $asignar->color = 'verde';

            $tabla = new stdClass();
            $tabla->tools = $radios;
            $tabla->id = 'listAfe';
            $tr = [];

            $th = new stdClass();
            $th->texto = 'ASIGNAR';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'AFEC.';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'EST.PROG.';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'CVEFIN';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'FECHA';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'PERCEPCION';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'RETENCION';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'IMPORTE';
            array_push($tr, $th);

            $tabla->encabezado = [$tr];
            $tabla->comunica = new stdClass();
            $tabla->comunica->pantalla = $this->pantalla;
            $tabla->comunica->metodo = 'llenaTablaAfectaciones';
            $tabla->comunica->tipo = $this->prm->tipo;
            $tabla->comunica->clc_id = $this->prm->clc_id;
            $tabla->comunica->ecf_id = $this->prm->ecf_id;
            $tabla->comunica->stc_id = $this->prm->stc_id;
            $tabla->comunica->tcp_id = $this->prm->tcp_id;
            $tabla->comunica->mpc_id = $this->prm->mpc_id;
            $tabla->comunica->grupo = $this->prm->grupo;
            $tabla->comunica->bur_id = $this->prm->bur_id;
            $tabla->numFilas = 100;
            $tabla->cuerpo = [];

            $td = new stdClass();
            $td->tipo = 'check';
            $td->attr = new stdClass();
            $td->attr->gpo_id = 'gpo_id';
            $td->attr->ur_id = 'ur_id';
            $td->attr->afe_id = 'afe_id';
            $td->color = 'grey';
            $td->class = 'seleccionar';
            array_push($tabla->cuerpo, $td);

            $td = new stdClass();
            $td->campoTxt = 'afe_id';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = new stdClass();
            $td->campoTxt->campos = ['gpo_id', 'ur_id', 'prg_id', 'spr_id', 'py_id', 'oa_id'];
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'cfi_clave';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'afe_fechaElaboracion';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'percepcion';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'retencion';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'importe';
            array_push($tabla->cuerpo, $td);

            $this->mixHtml($this->wrPonTabla($tabla));

            $this->mixHtml($this->ponBoton($asignar));
            $this->mixScript('
                $("#asignar").attr("clc_id","' . $this->prm->clc_id . '");
                $("#asignar").attr("tipo","' . $this->prm->tipo . '");
                $("#asignar").attr("stc_id","' . $this->prm->stc_id . '");
                $("#asignar").attr("tcp_id","' . $this->prm->tcp_id . '");
                $("#asignar").attr("ecf_id","' . $this->prm->ecf_id . '");
                $("#asignar").attr("tno_id","' . $this->prm->tno_id . '");
                $("#asignar").attr("abc","' . $this->prm->abc . '");
                $("#asignar").attr("mpc_id","' . $this->prm->mpc_id . '");
                $("#asignar").attr("bur_id","' . $this->prm->bur_id . '");
                ');

            $this->mixScript('
                $("#asignar").click(function() {
                    var carga = new Array();
                    $(".seleccionar:checked").each(
                    function(){
                        var reg = $(this).attr("afe_id");
                        carga.push( reg );
                    });
                    if (carga.length > 0) {
                        comunica("' . $this->pantalla . '",{
                            metodo:"GuardarAfectacion",
                            carga:carga,
                            paso:"true",
                            clc_id:$(this).attr("clc_id"),
                            tipo:$(this).attr("tipo"),
                            stc_id:$(this).attr("stc_id"),
                            tcp_id:$(this).attr("tcp_id"),
                            ecf_id:$(this).attr("ecf_id"),
                            tno_id:$(this).attr("tno_id"),
                            mpc_id:$(this).attr("mpc_id"),
                            bur_id:$(this).attr("bur_id"),
                            abc:$(this).attr("abc")
                        });

                        $(".habilitar").attr("disabled","disabled");
                    } else {
                        notify( "Debes seleccionar un número de Afectacion", "warning");
                    }
                    return false;
                });

                $("#SelectAll").click(function(){
                    if ($(this).is(":checked")) {
                        $(".seleccionar").prop( "checked", true );
                    } else {
                        $(".seleccionar").prop( "checked", false );
                    }
                });

            ');

            $this->llenaTablaAfectaciones();
        }
    }

    private function llenaTablaAfectaciones()
    {
        $partidas = $gastos = $wh = '';
        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $innerbur_id = '';
        $campobur_id = '';
        $weherbur_id = '';
        $stc = '';
        $tdf = '';
        $tdf2 = '';

        switch ($this->prm->tipo) {
            case 'A':
                if (isset($this->prm->grupo) && $this->prm->grupo == 1) {
                    if (isset($this->prm->stc_id) and $this->prm->stc_id != '') {
                        $wh .= ($wh == '') ? 'and stc_id=' . $this->prm->stc_id . '' : ' and stc_id=' . $this->prm->stc_id . '';
                    }
                    if (isset($this->prm->tno_id) and $this->prm->tno_id != '') {
                        $wh .= ($wh == '') ? 'and tno_id=' . $this->prm->tno_id . '' : ' and tno_id=' . $this->prm->tno_id . '';
                    }

                    if (isset($this->prm->mno_id) and $this->prm->mno_id != '') {
                        switch ($this->prm->mno_id) {
                            case 9:
                                $wh .= ($wh == '') ? 'and mno_id in (1,2,3,4,5,6,7,9)' : ' and mno_id in (1,2,3,4,5,6,7,9)';
                                break;
                            default:
                                $wh .= ($wh == '') ? 'and mno_id=' . $this->prm->mno_id . '' : ' and mno_id=' . $this->prm->mno_id . '';
                                break;
                        }
                    }
                    if (isset($this->prm->smn_id) and $this->prm->smn_id != '') {
                        $wh .= ($wh == '') ? 'and smn_id=' . $this->prm->smn_id . '' : ' and smn_id=' . $this->prm->smn_id . '';
                    }
                } else {
                    if (isset($this->prm->stc_id)) {

                        switch ($this->prm->stc_id) {
                            case 6:
                                $stc = ' and stc_id=6';
                                break;

                            case 8:
                                $stc = ' and stc_id=8';
                                break;
                            case 17:
                                $stc = ' and stc_id in (3,5,10,11)';
                                break;
                            case 15:
                                $stc = ' and stc_id=15';
                                break;
                        }
                    }
                }
                break;
            case 'B':
                if ($this->prm->mpc_id == 2) {
                    $wh = 'and tcp_id="' . $this->prm->tcp_id . '"';
                    $gastos = 'LEFT JOIN scp_Presupuestal USING(gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id)';

                    $partidas = ' AND  ( (pe_id=328 AND length (ncp_referencia)=11) OR ( (pe_id=388 AND length (ncp_referencia)=10) ) OR (pe_id=476 AND length(ncp_referencia)=11))';
                }
                if ($this->prm->mpc_id == 4) {
                    // @jordy 30-10-2018 se agrego esta excepcion para la UR 531 conforme a la partidas
                    // generica 441 y partida especifica 449
                    // En el caso de cadenas productivas solo tiene que tener un documento dentro de las afectaciones
                    $wh = 'AND tcp_id = "' . $this->prm->tcp_id . '"';
                    $tdf2 = 'AND R IS NULL  AND C < 2';
                    if ($this->ur->getGpo_id() == 5 && $this->ur->getUr_id() == 31) {
                        $partidas = 'AND pg_id = 441 AND pe_id = 449';
                    }
                } else {
                    $wh = 'and tcp_id="' . $this->prm->tcp_id . '"';
                }
                break;
            case 'C':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'D':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'E':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'F':
                $wh = 'and stc_id=' . $this->prm->stc_id . ' and epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '';
                break;
            case 'G':
                if ($this->prm->mpc_id == 2) {
                    $wh = 'and epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '';
                    $gastos = 'LEFT JOIN scp_Presupuestal USING(gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id)';
                    /* LAS UR 430  puede afetar la partida generica 411 con la partida especifica 394 y las 388 */
                    if ($this->gpo_id . $this->ur_id == '530') {
                        $partidas = ' AND  (  (pe_id in (720,394)  AND length (ncp_referencia)=11) OR ( (pe_id=388 AND length (ncp_referencia)=10) )  )';
                    } else {
                        $partidas = ' AND  pe_id =720  AND length (ncp_referencia)=11';
                    }
                } else {
                    $wh = 'and epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '';
                }
                break;
            case 'H':
                $wh = 'and epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '';
                break;
            case 'I':
                $wh = 'and stc_id=' . $this->prm->stc_id . '' . PHP_EOL;
                if ($this->prm->stc_id == '04') {
                    $wh .= 'AND  epg_id=' . $_SESSION[_S]['clasAdm-epg'] . '';
                }

                break;
            case 'J':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'K':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'L':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
            case 'M':
                $wh = 'and stc_id=' . $this->prm->stc_id . '';
                break;
        }
        $wf = '';
        if (isset($this->prm->tipo) and $this->prm->tipo != '') {
            $wf .= ($wf == '') ? 'WHERE tcl_id="' . $this->prm->tipo . '"' : ' and tcl_id="' . $this->prm->tipo . '"';
        }
        if (isset($this->prm->ecf_id) and $this->prm->ecf_id != '') {
            $wf .= ($wf == '') ? 'WHERE ecf_id="' . $this->prm->ecf_id . '"' : ' and ecf_id="' . $this->prm->ecf_id . '"';
        }

        $wf .= ($wf == '') ? 'WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . '  ' : '  AND gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . ' ';

        $trimestre = ['(1,2,3)', '(4,5,6)', '(7,8,9)', '(10,11,12)'];
        $biactual = ceil(date('n') / 3) - 1;
        $fechaAfectacion = 'AND month(afe_fechaElaboracion) in ' . $trimestre[$biactual] . '';

        $fondoR = '';
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT bur_fondoRotatorio
            FROM cat_BancosUr
            WHERE bur_id = ?
        ');
        $conexion->consultar([$this->prm->bur_id]);
        $conexion->desconectar();
        $reg = $conexion->extraer();

        $mensajeAlerta = 'No hay Afectaciones';

        if ($reg['bur_fondoRotatorio'] == 'S' && ($this->prm->tipo == 'B')) {
            $fondoR = ' AND afe_fondoRotatorio="S" ';
            $mensajeAlerta = 'No hay Afectaciones para recuperación de fondo rotatorio';
        } else {
            $fondoR = ' AND afe_fondoRotatorio="N" ';
        }

        if ($this->prm->mpc_id == 5 || $this->prm->mpc_id == 4) {
            $innerbur_id = ' INNER JOIN vin_BancosUrProveedor USING(rfc_id) ';
            $campobur_id = ' ,rfc_id ';
            $weherbur_id = ' AND bur_id = ' . $this->prm->bur_id . ' ';

            if ($reg['bur_fondoRotatorio'] == 'S') {
                $men = 'La CLC se está generando por Modalidad de Pago PDP este metodo no funciona con cuentas  de Fondo Rotatorio';

                $this->mixScript(' notify("' . $men . '","error");');
                return false;
            }
        }

        /* Validación para el uso de documentos de tipo rectificación   */
        if (isset($this->prm->clc_rectificacion) and $this->prm->clc_rectificacion != '' and $this->prm->clc_rectificacion != 0) {
            $tdf = '  AND R >0 AND C IS NULL  ';
        } else {
            $tdf = '  AND R IS NULL  AND C >0   ';
        }

        $this->conectar();
        $sql = '
            SELECT
            gpo_id,ur_id,ue_id,prg_id,spr_id,py_id,oa_id,clc_id,afe_id,epg_id,ecf_id,tcl_id,max(cfi_clave) as cfi_clave,
            stc_id,tno_id,mno_id,smn_id,afe_percepcion AS percepcion,
            afe_retencion AS retencion,truncate(afe_percepcion,2) - truncate(afe_retencion,2) AS importe,
            DATE_FORMAT( afe_fechaElaboracion, "%d/%m/%Y") afe_fechaElaboracion,C,R
            FROM scp_Afectacion
            LEFT JOIN (
            SELECT gpo_id,ur_id,afe_id,SUM(C) AS C
            FROM(
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C
            FROM scp_Contable
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id ' . $campobur_id . ')
            ' . $innerbur_id . '
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND tdf_id != "RCT"
            ' . $weherbur_id . '
            GROUP BY afe_id,tdf_id
            UNION ALL
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C
            FROM scp_Presupuestal
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id ' . $campobur_id . ')
            ' . $innerbur_id . '
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND tdf_id != "RCT"
            ' . $weherbur_id . '
            GROUP BY afe_id,tdf_id
            )v
            GROUP BY gpo_id,ur_id,afe_id
            )N USING ( gpo_id,ur_id,afe_id )
            LEFT JOIN (
            SELECT gpo_id,ur_id,afe_id,SUM(C) AS R
            FROM(
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C
            FROM scp_Contable
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id ' . $campobur_id . ')
            ' . $innerbur_id . '
            WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND tdf_id ="RCT"
            ' . $weherbur_id . '
            GROUP BY afe_id,tdf_id
            UNION ALL
            SELECT gpo_id,ur_id,afe_id,COUNT(bdo_id) C
            FROM scp_Presupuestal
            INNER JOIN cat_BancoDocumentos USING ( gpo_id,ur_id,bdo_id ' . $campobur_id . ')
            ' . $innerbur_id . '
            WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND tdf_id ="RCT"
            ' . $weherbur_id . '
            GROUP BY afe_id,tdf_id
            )v
            GROUP BY gpo_id,ur_id,afe_id
            )R USING ( gpo_id,ur_id,afe_id )
            LEFT JOIN scp_Presupuestal USING(gpo_id,ur_id,clc_id,afe_id,epg_id,ecf_id,ue_id,tcp_id)
            LEFT JOIN enc_Programatico USING(epg_id,gpo_id,ur_id)
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN cat_ObraActividad USING(gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,ue_id)
            ' . $wf . '
            ' . $wh . '
            AND clc_id = 0
            ' . $stc . '
            ' . $partidas . '
            ' . $fechaAfectacion . '
            AND (afe_percepcion != 0 or afe_retencion != 0)
            AND  eaf_id = "A"
            ' . $fondoR . '
            ' . $tdf . '
            ' . $tdf2 . '
            GROUP BY afe_id; ';
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id];
        $this->setParams($params);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'listAfe';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 100;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("' . $mensajeAlerta . '","warning");');
        }
    }

    public function TablaClcAfectacion($contenedor = true)
    {
        if ($contenedor === true) {
            $this->setContenedor('clcafectaciones');
        }

        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : '';
        $this->prm->clc_id = (isset($this->prm->clc_id)) ? $this->prm->clc_id : '';
        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $this->prm->tcp_id = (isset($this->prm->tcp_id)) ? $this->prm->tcp_id : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id)) ? $this->prm->mpc_id : '';
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';

        $separar = new stdClass();
        $separar->id = 'separar';
        $separar->text = 'separar';
        $separar->name = 'separar';
        $separar->value = 'separar';
        $separar->color = 'rojo';

        $radios = '<td style="padding:4px; text-align:center; font-size:16px;"><input type="checkbox" id="SelectTodas"> Seleccionar Todas</input></td>';

        $tabla = new stdClass();
        $tabla->tools = $radios;
        $tabla->id = 'aclc';
        $tr = [];

        $th = new stdClass();
        $th->texto = 'SEPARAR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'AFEC.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EST.PROG.';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CVEFIN';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'PERCEPCION';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'RETENCION';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClcAfectacion';
        $tabla->comunica->tipo = $this->prm->tipo;
        $tabla->comunica->clc_id = $this->prm->clc_id;
        $tabla->comunica->stc_id = $this->prm->stc_id;
        $tabla->comunica->tcp_id = $this->prm->tcp_id;
        $tabla->comunica->mpc_id = $this->prm->mpc_id;
        $tabla->comunica->bur_id = $this->prm->bur_id;
        $tabla->comunica->ecf_id = $this->prm->ecf_id;
        $tabla->numFilas = 50;
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->tipo = 'check';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->afe_id = 'afe_id';
        $td->color = 'grey';
        $td->class = 'seleccionar';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id', 'prg_id', 'spr_id', 'py_id', 'oa_id'];
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_fechaElaboracion';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_percepcion';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_retencion';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'importe';
        array_push($tabla->cuerpo, $td);
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->mixHtml($this->ponBoton($separar));
        $this->mixScript('
            $("#separar").attr("clc_id","' . $this->prm->clc_id . '");
            $("#separar").attr("tipo","' . $this->prm->tipo . '");
            $("#separar").attr("stc_id","' . $this->prm->stc_id . '");
            $("#separar").attr("tcp_id","' . $this->prm->tcp_id . '");
            $("#separar").attr("ecf_id","' . $this->prm->ecf_id . '");
            $("#separar").attr("tno_id","' . $this->prm->tno_id . '");
            $("#separar").attr("mpc_id","' . $this->prm->mpc_id . '");
            $("#separar").attr("bur_id","' . $this->prm->bur_id . '");
        ');

        $this->mixScript('
            $("#separar").click(function() {
                var carga = new Array();
                $(".seleccionar:checked").each(
                function(){
                    var reg = $(this).attr("afe_id");
                    carga.push( reg );
                });
                if (carga.length > 0) {
                    comunica("' . $this->pantalla . '",{
                    metodo:"SepararAfectacion",
                    carga:carga,
                    clc_id:$(this).attr("clc_id"),
                    tipo:$(this).attr("tipo"),
                    stc_id:$(this).attr("stc_id"),
                    tcp_id:$(this).attr("tcp_id"),
                    ecf_id:$(this).attr("ecf_id"),
                    tno_id:$(this).attr("tno_id"),
                    mpc_id:$(this).attr("mpc_id"),
                    bur_id:$(this).attr("bur_id")
                    });

                    /* LA CLASE HABILITAR ES PARA PODER CAPTURAR LA INFORMACION DE LA CLC CUANDO YA NO TIENE AFECTACION  AL IGUAL SE MUESTRA LOS BOTONES GUARDAR Y ELIMINAR */
                    $(".habilitar").attr("disabled",false);
                    $("#delete").show();
                    $("#guardar").show();
                } else {
                    notify( "Debes seleccionar un número de Afectacion", "warning");
                }
                return false;
            });

            $("#SelectTodas").click(function(){
            if ($(this).is(":checked")) {
                $(".seleccionar").prop( "checked", true );
            } else {
                $(".seleccionar").prop( "checked", false );
            }
            });

        ');

        $this->llenaTablaClcAfectacion();
    }

    private function llenaTablaClcAfectacion()
    {
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $this->prm->abc = (isset($this->prm->abc)) ? $this->prm->abc : '';
        $this->prm->abc = (isset($this->prm->abc)) ? $this->prm->abc : '';
        $this->conectar();

        $sql = '
            SELECT gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,afe_id,a.ue_id,
            DATE_FORMAT( afe_fechaElaboracion,"%d/%m/%Y") afe_fechaElaboracion,
            clc_id,afe_id,cfi_clave,afe_percepcion,truncate(afe_percepcion,2) - truncate(afe_retencion,2) AS importe
            FROM scp_Clc a
            JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,tcl_id,ecf_id)
            JOIN enc_ClaveFinanciamiento USING(ecf_id)
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            LEFT JOIN enc_Programatico USING(epg_id,gpo_id,ur_id)
            WHERE tcl_id =:tcl_id
            AND gpo_id= :gpo_id
            AND ur_id = :ur_id
            AND clc_id = :clc_id;
            ';
        $params = [
            ':tcl_id' => $this->prm->tipo,
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':clc_id' => $this->prm->clc_id];
        $this->setParams($params);
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'aclc';
        $nav->nf = 50;

        if ($this->wrLlenaTabla($nav) == 0) {
        } else {
            $this->mixScript(' $(".habilitar").attr("disabled","disabled"); ');
        }

        if ($this->prm->tipo == 'A' && $this->prm->stc_id == 6 && $this->prm->abc == 'c' || ($this->prm->tipo == 'A' && $this->prm->stc_id == 6 && $this->prm->abc == '') || ($this->prm->tipo == 'B' && $this->prm->mpc_id == 2 && $this->prm->abc == 'c') || ($this->prm->tipo == 'B' && $this->prm->mpc_id == 2 && $this->prm->abc == '') || ($this->prm->tipo == 'G' && $this->prm->mpc_id == 2 && $this->prm->abc == 'c') || ($this->prm->tipo == 'G' && $this->prm->mpc_id == 2 && $this->prm->abc == '')) {

            $this->setSql('
                SELECT gpo_id,ur_id,clc_id,afe_id,tcl_id,count(referencia),referencia,ecf_id
                FROM  scp_Clc
                JOIN scp_Afectacion USING(gpo_id,ur_id,clc_id,tcl_id,ecf_id)
                LEFT JOIN
                (
                SELECT gpo_id,ur_id,clc_id,afe_id,numcontrol,referencia
                FROM (
                SELECT gpo_id,ur_id, clc_id, afe_id,ncp_id numcontrol,
                ncp_referencia referencia,ncp_fechaReferencia fecha_referencia
                FROM scp_Presupuestal
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id
                UNION
                SELECT gpo_id,ur_id, clc_id, afe_id,ncc_id numcontrol,
                ncc_referencia referencia,ncc_fechaReferencia fecha_referencia
                FROM scp_Contable
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id
                ) nc
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id
                )GNC USING(gpo_id,ur_id,clc_id,afe_id)
                WHERE gpo_id= :gpo_id AND ur_id= :ur_id AND clc_id= :clc_id AND ecf_id= :ecf_id
                group by referencia
                ');
            $params = [
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id,
                ':clc_id' => $this->prm->clc_id,
                ':ecf_id' => $this->prm->ecf_id];
            $this->consultar($params);
            $row = $this->extraer();

            switch ($this->getNumeroRegistros()) {

                case 0:
                    $this->setSql('
                        SET @usr_id = :usr_id,
                        @usr_ip = INET_ATON(:ip),
                        @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                    ');
                    $params = [
                        ':usr_id' => $this->usr_id,
                        ':ip'     => $this->usr_ip];
                    $this->consultar($params);
                    $this->setSql('
                        UPDATE scp_Clc
                        SET clc_lineaCaptura=""
                        WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id=:clc_id;
                    ');
                    $params = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':clc_id' => $this->prm->clc_id];
                    if ($_SESSION[_S]['mst'] === false) {
                        $this->consultar($params);
                    }

                    break;
                case 1:
                    $this->setSql('
                        SET @usr_id = :usr_id,
                        @usr_ip = INET_ATON(:ip),
                        @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                    ');
                    $params = [
                        ':usr_id' => $this->usr_id,
                        ':ip'     => $this->usr_ip];
                    $this->consultar($params);
                    $this->setSql('
                        UPDATE scp_Clc
                        SET clc_lineaCaptura= :clc_lineaCaptura
                        WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id = :clc_id;
                    ');
                    $params = [
                        ':clc_lineaCaptura' => $row['referencia'],
                        ':gpo_id'           => $gpo_id,
                        ':ur_id'            => $ur_id,
                        ':clc_id'           => $this->prm->clc_id];
                    if ($_SESSION[_S]['mst'] === false) {
                        $this->consultar($params);
                    }

                    break;
                default:
                    $this->setSql('
                        SET @usr_id = :usr_id,
                        @usr_ip = INET_ATON(:ip),
                        @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                    ');
                    $params = [
                        ':usr_id' => $this->usr_id,
                        ':ip'     => $this->usr_ip];
                    $this->consultar($params);
                    $this->setSql('
                        UPDATE scp_Clc
                        SET clc_lineaCaptura="XXXXXXXXXXX"
                        WHERE gpo_id= :gpo_id and ur_id= :ur_id and clc_id = :clc_id;
                    ');
                    $params = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':clc_id' => $this->prm->clc_id];
                    if ($_SESSION[_S]['mst'] === false) {
                        $this->consultar($params);
                    }

                    break;
            }
            $this->desconectar();
        }
    }

    private function GuardarAfectacion()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $ur = $this->gpo_id . $this->ur_id;
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $this->prm->paso = (isset($this->prm->paso)) ? true : false;
        $this->prm->abc = (isset($this->prm->abc)) ? $this->prm->abc : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id)) ? $this->prm->mpc_id : '';
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';

        /*
        14-02-2019 @Jordy en el caso de municipios para la modalidad de pago Tradicional se validará si las afectaciones
        contiene el rfc_id de la cuenta bancaria por parte de las tablas de tesoreria cuando cumplan lo siguiente:
        la clc debe ser de municipios I
        su modalidad de pago debe ser Tradicional y por SPEI
        el filtro de la cuenta debe ser propia
        la clave interbancaria debe ser diferente de la 000000000000000000
        y la afectacion debe solamente tener el rfc correspondiente a la cuenta bancaria en todos sus aspectos contables
        y presupuestales

         */
        $tempBene = '';
        $clc = new Clc();

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('
            SELECT ecl_id
            FROM scp_Clc
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id = :clc_id
        ');
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':clc_id' => $this->prm->clc_id];
        $conexion->consultar($params);
        $conexion->desconectar();
        $reg = $conexion->extraer();

        if ($reg['ecl_id'] > 1) {
            $this->mixScript('notify("La CLC ya está autorizada. No puede agregar afectaciones.","error");');
            return;
        }

        if (!empty($this->prm->clc_id)) {
            $clc->setGpo_id($this->gpo_id);
            $clc->setUr_id($this->ur_id);
            $clc->setClc_id($this->prm->clc_id);
            $clc->select();

            if ((!empty($clc->getTcl_id()) && $clc->getTcl_id() == 'I') && (!empty($clc->getMpc_id()) && $clc->getMpc_id() == '1') && (!empty($clc->getBur_id()) && $clc->getBur_id() != '000000000000000000')) {
                $usoCuenta = [];
                $conexionUcb = new AccesoDatos();
                $conexionUcb->conectar();
                $conexionUcb->setSql('
                    SELECT ucb_id, bur_beneficiario AS clc_beneficiario
                    FROM cat_BancosUr
                    INNER JOIN cat_InstitucionesBancarias cib USING (iba_id)
                    LEFT JOIN vin_BancosUrTipoClc USING (bur_id)
                    WHERE bur_id = :bur_id
                    AND gpo_id = :gpo_id
                    AND ur_id = :ur_id
                ');
                $params = [
                    ':bur_id' => $clc->getBur_id(),
                    ':gpo_id' => $this->gpo_id,
                    ':ur_id'  => $this->ur_id];
                $conexionUcb->consultar($params);
                if ($conexionUcb->getNumeroRegistros() != 0) {
                    $usoCuenta = $conexionUcb->extraer();
                    $conexionUcb->desconectar();
                } else {
                    $conexionUcb->desconectar();
                    $this->mixScript('notify( "No se puede asignar la(s) afectacion(es) a la CLC", "success");');
                    return false;
                }

                if ($clc->getFormaPago() == 'S' && $usoCuenta['ucb_id'] == '1') {
                    if ($ur != 902) {
                        $cuentaBancaria = [];
                        $conexionRfc = new AccesoDatos();
                        $conexionRfc->conectar();
                        $conexionRfc->setSql('
                            SELECT rfc_id
                            FROM tesoreria.ban_CuentaBancariaBeneficiario
                            INNER JOIN tesoreria.vin_BeneficiarioMunicipio vbm USING (cbb_id)
                            INNER JOIN  tesoreria.cat_MunicipioProveedor USING (mun_id)
                            WHERE cbb_id = ?
                            AND cbb_anio = ' . APP_EJERCICIO . '
                            AND vbm.sts_id = 1;
                        ');
                        $conexionRfc->consultar([$clc->getBur_id()]);
                        if ($conexionRfc->getNumeroRegistros() != 0) {
                            $cuentaBancaria = $conexionRfc->extraer();
                            $conexionRfc->desconectar();
                        } else {
                            $conexionRfc->desconectar();
                            $this->mixScript('notify( "No se puede asignar la(s) afectacion(es) a la CLC", "success");');
                            return false;
                        }
                    }
                    // se va a validar la cuenta bancaria
                    // se va a buscar solamente las afetaciones que cumplan con solo tener el rfc correspondiente a las de municipio
                    $afec = '';
                    if (sizeof($this->prm->carga) != 0) {
                        for ($i = 0; $i < sizeof($this->prm->carga); $i++) {
                            $i2 = $i + 1;
                            if ($i2 == sizeof($this->prm->carga)) {
                                $afec .= $this->prm->carga[$i];
                            } else {
                                $afec .= $this->prm->carga[$i] . ',';
                            }
                        }
                    } else {
                        $this->mixScript('notify( "Debes seleccionar un número de Afectacion", "warning");');
                        return false;
                    }
                    $conexionAfectacion = new AccesoDatos();
                    $conexionAfectacion->conectar();
                    $conexionAfectacion->setSql('
                        SELECT gpo_id, ur_id, afe_id
                        FROM scp_Afectacion
                        LEFT JOIN (
                            SELECT gpo_id,ur_id,afe_id,c.rfc_id AS rfc_id, d.rfc_id AS docRfc
                            FROM scp_Contable c
                            INNER JOIN cat_BancoDocumentos d USING ( gpo_id,ur_id,bdo_id )
                            WHERE gpo_id = ? AND ur_id = ?
                            AND ( c.rfc_id != ?
                            OR d.rfc_id != ? )
                            AND afe_id in (' . $afec . ')
                            GROUP BY afe_id,tdf_id,c.rfc_id, d.rfc_id
                            UNION ALL
                            SELECT gpo_id,ur_id,afe_id,p.rfc_id AS rfc_id, d.rfc_id AS docRfc
                            FROM scp_Presupuestal p
                            INNER JOIN cat_BancoDocumentos d USING ( gpo_id,ur_id,bdo_id )
                            WHERE gpo_id = ? AND ur_id = ?
                            AND (p.rfc_id != ?
                            OR d.rfc_id != ? )
                            AND afe_id in (' . $afec . ')
                            GROUP BY afe_id,tdf_id,p.rfc_id,d.rfc_id
                        )afectaciones USING ( gpo_id,ur_id,afe_id )
                        WHERE gpo_id = 9
                        AND ur_id = 1
                        AND afe_id in (' . $afec . ')
                        AND eaf_id = "A"
                        AND rfc_id != ?
                        GROUP BY gpo_id, ur_id,afe_id;
                    ');
                    $params = [$this->gpo_id, $this->ur_id, $cuentaBancaria['rfc_id'], $cuentaBancaria['rfc_id'],
                        $this->gpo_id, $this->ur_id, $cuentaBancaria['rfc_id'], $cuentaBancaria['rfc_id'], $cuentaBancaria['rfc_id']];
                    $conexionAfectacion->consultar($params);
                    unset($afec);
                    $afec = [];
                    $mensajeAfect = '';
                    while ($tempAfec = $conexionAfectacion->extraer()) {
                        array_push($afec, $tempAfec);
                        $mensajeAfect .= ' ' . $tempAfec['afe_id'] . ', ';
                    }
                    $conexionAfectacion->desconectar();
                    if (sizeof($afec) != 0) {
                        $tmpCarga = $this->prm->carga;
                        unset($this->prm->carga);
                        $this->prm->carga = [];
                        $noAplica = false;
                        for ($i = 0; $i < sizeof($tmpCarga); $i++) {
                            for ($i2 = 0; $i2 < sizeof($afec); $i2++) {
                                if ($tmpCarga[$i] == $afec[$i2]['afe_id']) {
                                    $noAplica = true;
                                }
                            }
                            if (!$noAplica) {
                                array_push($this->prm->carga, $tmpCarga[$i]);
                            }
                        }
                        unset($tmpCarga);
                    }
                    if (sizeof($this->prm->carga) == 0) {
                        $this->mixScript('notify( "La(s) afectacion(es): ' . $mensajeAfect . ' no se puede(n) agregar por que no pertenece(n) al RFC ' . $cuentaBancaria['rfc_id'] . ' de la cuenta bancaria", "warning");');
                        return false;
                    } else {
                        $tempBene = '$("#municipio").val("' . $usoCuenta['clc_beneficiario'] . '");';
                    }
                }
            }
        }

        $this->conectar();
        $this->setSql('
            SELECT
            month(clc_fechaElaboracion) AS mes
            FROM scp_Clc
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id = :clc_id;
        ');
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':clc_id' => $clc_id];
        $this->consultar($params);
        $registros = $this->extraer();
        $this->desconectar();

        /** @JORDY Validación para mostrar el boton de las afecatciones, si no corresponde al mes no se guarda */
        if ((int) date('n') == (int) $registros['mes']) {

            if (count($this->prm->carga) == 0) {
                $this->mixScript('notify( "Debes seleccionar un número de Afectacion", "warning");');
            }

            $r = '';

            if (count($this->prm->carga) > 0) {
                $r .= '(' . implode(',', $this->prm->carga) . ')';
            }

            if (count($this->prm->carga) > 0 && $this->prm->carga != '') {
                foreach ($this->prm->carga as $key => $carga) {
                    $this->conectar();
                    $this->setSql('
                        INSERT INTO ' . BD_BITACORA . '.bit_Afectacion(gpo_id,ur_id,clc_id,afe_id,eaf_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                        VALUES (:gpo_id,:ur_id,:clc_id,:cga_id,"A","AFECTACION AGREGADA ",INET_ATON(:ip),:usr_id,NOW())
                        ');
                    $params = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':clc_id' => $clc_id,
                        ':cga_id' => $carga,
                        ':ip'     => $ip,
                        ':usr_id' => $user];
                    $this->consultar($params);
                    $this->desconectar();
                }
            }
            $params = [];
            $this->conectar();
            $this->setSql('
                SET
                @usr_id = :usr_id,
                @usr_ip = INET_ATON(:ip),
                @bit_observacion = "AFECTACION ASIGNADA";
            ');
            $params = [
                ':usr_id' => $user,
                ':ip'     => $ip];
            $this->consultar($params);
            $this->setSql('
                UPDATE scp_Afectacion
                SET clc_id= :clc_id
                WHERE gpo_id= :gpo_id
                AND ur_id= :ur_id
                AND clc_id=0
                AND afe_id in ' . $r . ';
                ');
            $params = [
                ':clc_id' => $clc_id,
                ':gpo_id' => $gpo_id,
                ':ur_id'  => $ur_id];
            $this->consultar($params);
            $this->desconectar();

            //Si la clc es de gastos de operacion, verificamos si es de recurso estatal, entonces deberemos asignar como beneficiario el nombre del proveedor

            //Descomentar esta parte para realizar la validación para pago directo

            // @JORDY 26-10-2018 debido a que la modalidad de pago de cadena productiva es igual que al pago directo por PDP
            // se agrega en la mismo funcionamiento de pago directo a proveedor ticket 638123
            $beneficiario = '';

            if ($this->prm->mpc_id == 5 || $this->prm->mpc_id == 4) {
                $this->conectar();
                $this->setSql('
                    SELECT ter_id,ucb_id,clc_beneficiario,IFNULL(bur_id,"") AS  bur_id,rfc_id,bur_beneficiario AS rfc_nombre
                    FROM scp_Clc c
                    INNER JOIN cat_BancosUr USING(bur_id)
                    INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id )
                    INNER JOIN vin_BancosUrProveedor using(bur_id)
                    WHERE c.gpo_id= :gpo_id
                    AND c.ur_id= :ur_id
                    AND clc_id= :clc_id;
                ');
                $params = [
                    ':gpo_id' => $gpo_id,
                    ':ur_id'  => $ur_id,
                    ':clc_id' => $clc_id];
                $this->consultar($params);
                $this->desconectar();
                $tr = $this->extraer();
                $bur_idclc = $tr['bur_id'];
                $rfc_idclc = $tr['rfc_id'];
                $rfc_nombreclc = $tr['rfc_nombre'];

                $this->setContenedor('divContenedorGeneralModal');
                $html = '<table class="table table table-bordered table-hover" >
                                <tr class="warning">
                                    <th class="warning" style="text-align:center" align="left" colspan="1">Afectacion</th>
                                    <th class="warning" style="text-align:center" align="left" colspan="5">Mensaje de Error</th>
                                </tr>';
                $columafecta = '';

                $this->conectar();
                $this->setSql('
                    SELECT
                    afe_id,rfc_id,rfc_nombre,IFNULL(a.bur_id,"") AS bur_id
                    FROM ' . BD_SINPRES . '.cat_Proveedores
                    INNER JOIN(
                    SELECT rfc_id,afe_id
                    FROM scp_Contable a
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id
                    GROUP BY afe_id,rfc_id,bdo_id

                    UNION ALL
                    SELECT rfc_id,afe_id
                    FROM scp_Presupuestal a
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id
                    GROUP BY afe_id, rfc_id,bdo_id

                    )P USING(rfc_id)
                    LEFT JOIN vin_BancosUrProveedor a USING(rfc_id)
                    WHERE rfc_id!= :rfc_id OR ifnull(a.bur_id,"")=""
                    GROUP BY afe_id,rfc_id,rfc_nombre,bur_id;

                ');
                $params = [
                    ':gpo_id' => $gpo_id,
                    ':ur_id'  => $ur_id,
                    ':clc_id' => $clc_id,
                    ':rfc_id' => $rfc_idclc];
                $this->consultar($params);
                $this->desconectar();

                $afecnoentra = '';
                $afecentra = $r;

                while ($prov = $this->extraer()) {
                    if (strcmp($prov['bur_id'], $bur_idclc) != 0) {

                        $columafecta .= '<tr>';

                        if ($afecnoentra == '') {
                            $afecnoentra .= $prov['afe_id'];
                        } else {
                            $afecnoentra .= ',' . $prov['afe_id'];
                        }

                        $afecentra = str_replace($prov['afe_id'] . ',', '', $afecentra);
                        $afecentra = str_replace($prov['afe_id'], '', $afecentra);
                        $columafecta .= '<td>' . $prov['afe_id'] . '</td>';
                        $columafecta .= '<td>El proveedor utilizado en la afectación no corresponde al vinculado a la cuenta bancaria seleccionada en la  CLC</td>';
                        $columafecta .= '</tr>';
                    }

                    if (strcmp($prov['bur_id'], '') == 0) {
                        $columafecta .= '<tr>';

                        if ($afecnoentra == '') {
                            $afecnoentra .= $prov['afe_id'];
                        } else {
                            $afecnoentra .= ',' . $prov['afe_id'];
                        }
                        $afecentra = str_replace($prov['afe_id'] . ',', '', $afecentra);
                        $afecentra = str_replace($prov['afe_id'], '', $afecentra);
                        $columafecta .= '<td>' . $prov['afe_id'] . '</td>';
                        $columafecta .= '<td>El proveedor utilizado en la afectación no esta  vinculado a una cuenta bancaria </td>';
                        $columafecta .= '<tr>';
                    }

                    if (strcmp($prov['rfc_id'], $rfc_idclc) != 0) {
                        $columafecta .= '<tr>';
                        if ($afecnoentra == '') {
                            $afecnoentra .= $prov['afe_id'];
                        } else {
                            $afecnoentra .= ',' . $prov['afe_id'];
                        }
                        $afecentra = str_replace($prov['afe_id'] . ',', '', $afecentra);
                        $afecentra = str_replace($prov['afe_id'], '', $afecentra);
                        $columafecta .= '<td>' . $prov['afe_id'] . '</td>';
                        $columafecta .= '<td>El proveedor debe ser único para este tipo de CLC</td>';
                        $columafecta .= '</tr>';
                    }
                }

                if ($afecnoentra != '') {

                    $this->conectar();
                    $this->setSql('SET @usr_id = :usr,@usr_ip = INET_ATON(:ip);');
                    $params = [
                        ':usr' => $user,
                        ':ip'  => $ip];
                    $this->consultar($params);
                    $this->setSql('
                        UPDATE scp_Afectacion
                        SET clc_id=0
                        WHERE gpo_id= :gpo_id
                        AND ur_id= :ur_id
                        AND afe_id in (' . $afecnoentra . ')
                        AND clc_id= :clc_id
                    ');
                    $params = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':clc_id' => $clc_id];
                    $this->consultar($params);
                    $this->desconectar();
                }

                if ($rfc_nombreclc != '') {

                    $this->conectar();
                    $this->setSql('
                        SELECT SUM(afec) as  numafec
                        FROM (
                            SELECT COUNT(afe_id) as afec
                            FROM scp_Contable a
                            WHERE gpo_id= ? and ur_id= ? and clc_id= ?
                            GROUP BY afe_id
                            UNION ALL
                            SELECT COUNT(afe_id) as afec
                            FROM scp_Presupuestal a
                            WHERE gpo_id= ? and ur_id= ? and clc_id= ?
                        GROUP BY afe_id) x;
                    ');
                    $params = [$gpo_id, $ur_id, $clc_id, $gpo_id, $ur_id, $clc_id];
                    $this->consultar($params);
                    $this->desconectar();
                    $numafect = $this->extraer();
                    if ($numafect['numafec'] > 0) {
                        $this->conectar();
                        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                        $this->setSql('
                            SET @usr_id = :usr_id ,
                            @usr_ip = INET_ATON(:ip),
                            @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                        ');
                        $params = [
                            ':usr_id' => $this->usr_id,
                            ':ip'     => $this->usr_ip,
                        ];
                        $this->consultar($params);
                        $this->setSql('
                            UPDATE scp_Clc
                            SET clc_beneficiario = :beneficiario
                            WHERE gpo_id= :gpo_id
                            AND ur_id= :ur_id
                            AND clc_id= :clc_id ;
                        ');
                        $params = [
                            ':beneficiario' => ($rfc_nombreclc != '' ? $rfc_nombreclc : ' '),
                            ':gpo_id'       => $_SESSION[_S]['gpo_id'],
                            ':ur_id'        => $_SESSION[_S]['ur_id'],
                            ':clc_id'       => $this->prm->clc_id,
                        ];
                        $this->consultar($params);
                        $this->desconectar();
                    }
                }

                if ($columafecta != '') {
                    $html .= $columafecta . '</table>';
                    $this->mixHtml('
                        <div id="myModal" class="modal fade modal-al80porciento">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                                        <h4 class="modal-title" id="myModalLabel">Errores de Vinculación</h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">' . $html . '</div>
                                        <div class="modal-footer">
                                        </div>
                                    </div>
                                </div><!-- /.modal-content -->
                            </div><!-- /.modal-dialog -->
                        </div>
                    ');
                    $this->mixScript('
                    $("#myModal").modal();
                    $("#myModal").on("hidden.bs.modal", function (e) {
                        $("#myModal").remove();
                    });

                ');
                }
            }
            //Pasar este script cuando se descomente la parte de pagos a terceros

            $pdpben = '';
            $rfc_nombreclc = '';
            if ($tipo == 'K') {
                $pdpben = '$("#beneburpdp").val("' . $rfc_nombreclc . '");';
            } elseif ($tipo == 'D' || $tipo == 'F' || $tipo == 'G' || $tipo == 'H') {
                $pdpben = '$("#beneur").val("' . $rfc_nombreclc . '");';
            } elseif ($tipo == 'I') {
                $pdpben = '$("#beneficiarioP").val("' . $rfc_nombreclc . '");';
                $pdpben .= '$("#municipio").val("' . $rfc_nombreclc . '");';
            } else {
                $pdpben = '$("#benecap").val("' . $rfc_nombreclc . '");';
            }

            if (isset($tempBene) && $tempBene != '') {
                $this->conectar();
                $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
                $this->setSql('
                    SET
                    @usr_id = :usr_id,
                    @usr_ip = INET_ATON(:ip),
                    @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                ');
                $params = [
                    ':usr_id' => $this->usr_id,
                    ':ip'     => $this->usr_ip,
                ];
                $this->consultar($params);
                $this->setSql('
                    UPDATE scp_Clc
                    SET clc_beneficiario = :beneficiario
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id;
                ');
                $params = [
                    ':beneficiario' => $usoCuenta['clc_beneficiario'],
                    ':gpo_id'       => $_SESSION[_S]['gpo_id'],
                    ':ur_id'        => $_SESSION[_S]['ur_id'],
                    ':clc_id'       => $this->prm->clc_id,
                ];
                $this->consultar($params);
                $this->desconectar();
            }
            $afecentra = $r;
            $this->mixScript('
            ' . ($rfc_nombreclc != '' ? $pdpben : '') . '
                ' . $tempBene . '
                notify( "Se han agregado correctamente las afectaciones ' . $afecentra . '  a la CLC ' . $clc_id . '", "success");
                comunica("srv/ClasificacionFinanciamiento.php",{
                    metodoCveFin:"cargaComboCveFin",
                    filtro:"presupuesto",
                    selecciona:$("#cveFin_id option:selected").attr("ecf_id"),
                    ecf_id:$("#cveFin_id option:selected").val(),
                    lectura:true
                });
                $("#guardar,#delete").remove();
            ');
            $this->llenaTablaClcAfectacion();
            $this->llenaTablaAfectaciones();
        }
    }

    private function SepararAfectacion()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $gpo_id = $_SESSION[_S]['gpo_id'];
        $ur_id = $_SESSION[_S]['ur_id'];
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '') ? $this->prm->clc_id : '';
        $this->prm->stc_id = (isset($this->prm->stc_id)) ? $this->prm->stc_id : '';
        $this->prm->tcp_id = (isset($this->prm->tcp_id)) ? $this->prm->tcp_id : '';
        $this->prm->mpc_id = (isset($this->prm->mpc_id)) ? $this->prm->mpc_id : '';
        $this->prm->bur_id = (isset($this->prm->bur_id)) ? $this->prm->bur_id : '';

        $btnEliminar = new stdClass();
        $btnEliminar->id = 'delete';
        $btnEliminar->color = 'rojo';
        $btnEliminar->class = 'delete';
        $btnEliminar->value = 'Eliminar';
        $btnEliminar->icon = 'solid fa-trash me-2';
        $btnEliminar->text = 'Eliminar';
        $btnEliminar->attr = new stdClass();
        $eliminar = $this->ponBoton($btnEliminar);

        $btnGuardar = new stdClass();
        $btnGuardar->color = 'verde';
        $btnGuardar->icon = 'solid fa-floppy-disk me-2';
        $btnGuardar->id = 'guardar';
        $btnGuardar->name = 'guardar';
        $btnGuardar->value = 'Guardar';
        $btnGuardar->class = 'me-1';
        $guardar = $this->ponBoton($btnGuardar);

        $this->conectar();
        $this->setSql('
            SELECT *
            FROM ' . BD_BITACORA . '.bit_Clc
            WHERE gpo_id= :gpo_id
            AND ur_id= :ur_id
            AND clc_id= :clc_id
            AND ecl_id>=3 limit 1
        ');
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':clc_id' => $clc_id];
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            $eliminar = '';
        }

        if (count($this->prm->carga) == 0) {
            $this->mixScript('notify( "Debes seleccionar una Afectacion", "warning");');
        }

        $r = '';

        if (count($this->prm->carga) > 0) {
            $r .= '(' . implode(',', $this->prm->carga) . ')';
        }

        if (count($this->prm->carga) > 0 && $this->prm->carga != '') {
            foreach ($this->prm->carga as $key => $carga) {
                $this->conectar();
                $this->setSql('SET @usr_id = :usr,@usr_ip = INET_ATON(:ip);');
                $params = [
                    ':ip'  => $ip,
                    ':usr' => $user];
                $this->consultar($params);
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_Afectacion(gpo_id,ur_id,clc_id,afe_id,eaf_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (:gpo_id,:ur_id,:clc_id,:carga,"A","AFECTACION SEPARADA  ",INET_ATON(:ip),:usr_id,NOW())
                ');
                $params = [
                    ':gpo_id' => $gpo_id,
                    ':ur_id'  => $ur_id,
                    ':clc_id' => $clc_id,
                    ':carga'  => $carga,
                    ':ip'     => $ip,
                    ':usr_id' => $user];
                $this->consultar($params);
                $this->desconectar();
            }
        }

        $this->conectar();
        $this->setSql('
            SET
            @usr_id = :usr_id,
            @usr_ip = INET_ATON(:ip),
            @bit_observacion = "AFECTACION SEPARADA";
        ');
        $params = [
            ':usr_id' => $user,
            ':ip'     => $ip];
        $this->consultar($params);
        $this->setSql('
            UPDATE scp_Afectacion
            SET clc_id=0
            WHERE gpo_id= :gpo_id
            AND ur_id= :ur_id
            AND clc_id= :clc_id
            AND afe_id in ' . $r . '
        ');
        $params = [
            ':gpo_id' => $gpo_id,
            ':ur_id'  => $ur_id,
            ':clc_id' => $clc_id];
        $this->consultar($params);
        $this->desconectar();
        $this->mixScript('
            notify( "Se han separado correctamente la(s) afectacion(es) ' . $r . '  a la CLC ' . $clc_id . '", "warning");
            $("#cveFin_id").css("display","block");
            $("#inputCombo-cveFin_id").hide();
            comunica("srv/ClasificacionFinanciamiento.php",{
                metodoCveFin:"cargaComboCveFin",
                filtro:"presupuesto",
                selecciona:$("#cveFin_id option:selected").attr("ecf_id")
            });

            $("#guardar").click(guardar);
            $("#regresar").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo:"PantallaInicial",
                    tipo:"' . $this->prm->tipo . '",
                    tareaclc:"generacion"
                });
            });
        ');
        $this->agregaTools($guardar . ' ' . $eliminar);
        $this->llenaTablaAfectaciones();
        $this->llenaTablaClcAfectacion();
    }

    public function AutorizarClc()
    {
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $user = $_SESSION[_S]['usr_id'];
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tcl_id) && $this->prm->tcl_id != '') ? $this->prm->tcl_id : '';
        $cedula = $r = '';
        $rpdp = '';

        $r .= '(';
        $rpdp .= '(';
        $columaCLCnoAuto = '';
        $rclc = '';
        $rclc1 = '';

        foreach ($this->prm->autorizarclcs as $objeto) {
            $rclc1 .= $objeto->clc_id;
            $opc = new stdClass();
            $opc->sis_id = 'S';
            $opc->men_id = '2';
            $opc->opc_id = '2';
            $bloqueo = $this->model->gtBloqueos($objeto->tcl_id, $objeto->stc_id, $objeto->ecf_id, $opc);

            if ($bloqueo->valido == 0) {
                $this->mixScript(' notify("' . $bloqueo->mensaje . ' ","error",false); ');
                return false;
            }

            $this->prm->clc_id = $objeto->clc_id;
            $verificarClc = $this->validaCLCPagoDirecto();
            $verificarAnticipo = $this->ValidaAnticipoPorAmortizar($objeto->tcl_id);

            if ($verificarClc->valido == true && $verificarAnticipo->valido == true) {
                //Si la clc cumple con las reglas de validación para pago directo o la regla de anticipos por amortizar, se incluyen para actualizar su estado a validadas
                if ($r == '(') {
                    $r .= $objeto->clc_id;
                } else {
                    $r .= ',' . $objeto->clc_id;
                }

                if ($verificarClc->mpcidclc == 5) {
                    if ($rpdp == '(') {
                        $rpdp .= $objeto->clc_id;
                    } else {
                        $rpdp .= ',' . $objeto->clc_id;
                    }

                }
            } else {
                $rclc .= $objeto->clc_id;
                if (!$verificarClc->valido) {
                    $columaCLCnoAuto .= $verificarClc->mensaje;
                }
                if (!$verificarAnticipo->valido) {
                    $columaCLCnoAuto .= $verificarAnticipo->mensaje;
                }
            }

        }

        $r .= ')';
        $rpdp .= ')';

        $this->setContenedor('divContenedorGeneralModal');

        $html = '<table class="table table table-bordered table-hover" >
                    <tr class="warning">
                        <th class="warning" style="text-align:center" align="left" colspan="1">No. CLC</th>
                        <th class="warning" style="text-align:center" align="left" colspan="5">Mensaje de Error</th>
                    </tr>';

        if ($columaCLCnoAuto != '') {
            $html .= $columaCLCnoAuto . '</table>';
            $this->mixHtml('
            <div id="ModalCLCauto" class="modal fade modal-al80porciento">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                            <h4 class="modal-title" id="myModalLabel">Errores De Autorización</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">' . $html . '</div>
                            <div class="modal-footer">
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>');

            $this->mixScript('
                $("#ModalCLCauto").modal();
                $("#ModalCLCauto").on("hidden.bs.modal", function (e) {
                    $("#ModalCLCauto").remove();
                });
                $("#ModalCLCauto").modal("show");
            ');

        }

        if ($rclc1 == '') {
            if ($r == '()') {
                $this->mixScript('notify("Debe Seleccionar una CLC","warning");');
                return;
            }
        } elseif ($rclc1 != '' && $r == '()') {
            return;
        }

        $rclc = $r;
        $this->conectar();
        $this->setSql('
            SET @usr_id = :usr_id,
            @usr_ip = INET_ATON(:ip),
            @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
        ');
        $params = [
            ':usr_id' => $this->usr_id,
            ':ip'     => $this->usr_ip,
        ];
        $this->consultar($params);
        $this->setSql('
            UPDATE scp_Clc
            SET ecl_id = 2
            WHERE
            gpo_id = ?
            AND ur_id = ?
            AND ecl_id = 1
            AND (clc_percepcion + clc_retencion!=0)
            AND clc_id in ' . $r . ';
            ');
        $paramsclc = [$gpo_id, $ur_id];
        $sqldocumentos = '
            UPDATE cat_BancoDocumentos doc
            INNER JOIN
            ( SELECT gpo_id,ur_id,bdo_id
            FROM scp_Contable a
            INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
            WHERE gpo_id= ? and ur_id= ? and clc_id in ?
            GROUP BY bdo_id
            UNION ALL
            SELECT gpo_id,ur_id,bdo_id
            FROM scp_Presupuestal a
            INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
            WHERE gpo_id= ? and ur_id= ? and clc_id in ?
            GROUP BY bdo_id
            ) m USING ( gpo_id,ur_id, bdo_id)
            SET bdo_pdp = "S"
            WHERE doc.gpo_id = m.gpo_id
            AND doc.ur_id = m.ur_id
            AND doc.bdo_id = m.bdo_id;
            ';
        $paramsdoc = [$gpo_id, $ur_id, $rpdp, $gpo_id, $ur_id, $rpdp];
        $r = '';
        foreach ($this->prm->autorizarclcs as $objeto) {
            $r .= ($r != '') ? ',' : '';
            $cedula .= $objeto->clc_id . ',';
            $r .= '( ' . $gpo_id . ',' . $ur_id . ',' . $objeto->clc_id . ',2,"CLC AUTORIZADA POR LA UR",INET_ATON("' . $ip . '"),' . $user . ',NOW() )';
        }
        $this->consultar($paramsclc);

        $this->setSql('
            INSERT INTO ' . BD_BITACORA . '.bit_Clc(gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
            VALUES ' . $r . '
        ');
        $this->consultar();

        if ($rpdp != '()') {
            $this->setSql('SET @bit_observacion = "BLOQUEO POR USO EN PDP"');
            $this->consultar();
            $this->setSql($sqldocumentos);
            $this->consultar($paramsdoc);
        }

        $this->desconectar();
        $this->mixScript('
            notify("La(s) CLC(s) ' . $rclc . ' han sido autorizada(s) correctamente", "success");
            comunica("' . $this->pantalla . '",{
                metodo:"llenaTablaClc",
                tipo:"' . $this->prm->tipo . '",
                tareaclc:"' . $this->prm->tareaclc . '"
            });
        ');
    }

    private function validaCLCPagoDirecto()
    {
        $retorno = new stdClass();
        $retorno->valido = true;
        $retorno->mensaje = '';
        $retorno->mpcidclc = 1;

        $this->conectar();
        $this->setSql('
            SELECT ter_id,ucb_id,clc_beneficiario,mpc_id,pdp_id
            FROM scp_Clc c
            INNER JOIN cat_BancosUr USING(bur_id)
            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id,fte_id,sft_id,ori_id,sor_id)
            WHERE c.gpo_id= :gpo_id
            AND c.ur_id= :ur_id
            AND clc_id= :clc_id;
            ');
        $params = [
            ':gpo_id' => $this->gpo_id,
            ':ur_id'  => $this->ur_id,
            ':clc_id' => $this->prm->clc_id];
        $this->consultar($params);
        $this->desconectar();
        $tr = $this->extraer();

        $retorno->mpcidclc = $tr['mpc_id'];
        switch ($tr['mpc_id']) {
            case '4':
            case '5':
                //VERIFICAMOS QUE LAS AFECTACIONES AGREGADAS SUMEN LA TOTALIDAD DEL DOCUMENTO
                if ($tr['pdp_id'] == 1) {
                    $res = $this->model->f_validaPdp(['gpo_id' => $this->gpo_id, 'ur_id' => $this->ur_id, 'clc_id' => $this->prm->clc_id]);
                    $rfc = '';
                    foreach ($res['datos'] as $val) {
                        if (bccomp($val['IMPORTE_AFECTACION'], $val['IMPORTE_DOC'], 2) != 0) {
                            $retorno->valido = false;
                            $retorno->error = 1;
                            $mensaje1 = 'La CLC ' . $this->prm->clc_id . ' no cubre el importe total del documento ' . $val['bdo_id'] . ', favor de verificarlo';
                            $this->mixScript('notify("' . $mensaje1 . '","error");');
                        }

                        if (strcmp($val['rfc_id'], $rfc) != 0 && $rfc != '') {
                            $retorno->valido = false;
                            $retorno->error = 3;
                            $mensaje1 = 'Hay mas de un rfc utilizado en la CLC lo cual es incorrecto, debe generar una clc por proveedor';
                            $this->mixScript('notify("' . $mensaje1 . '","error");');
                        }
                        if (strcmp(strtoupper($val['clc_beneficiario']), strtoupper($val['rfc_nombre'])) != 0) {
                            $retorno->valido = false;
                            $retorno->error = 2;
                            $mensaje1 = 'El beneficiario de la CLC es incorrecto ';
                            $this->mixScript('notify("' . $mensaje1 . '","error");');
                        }
                        $rfc = $val['rfc_id'];
                    }
                    return $retorno;
                } else {
                    $retorno->valido = false;
                    $retorno->error = 4;
                    $mensaje1 = 'La CLC ' . $this->prm->clc_id . ' se está generando por Modalidad de Pago PDP la Clave de Financiamiento que se utiliza no esta disponible para esta Modalidad';
                    $this->mixScript('notify("' . $mensaje1 . '","error");');
                    return $retorno;
                }
                break;
            default:
                break;
        }
        return $retorno;
    }

    public function EnviarClc()
    {
        $gpo_id = $this->gpo_id;
        $ur_id = $this->ur_id;
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $this->prm->tareaclc = (isset($this->prm->tareaclc) && $this->prm->tareaclc != '') ? $this->prm->tareaclc : '';
        $this->prm->tipo = (isset($this->prm->tipo) && $this->prm->tipo != '') ? $this->prm->tipo : '';
        $bandera = false;
        $mensaje = '';
        $cedula = $r = '';
        if (isset($this->prm->enviarclcs) && count($this->prm->enviarclcs) > 0) {
            foreach ($this->prm->enviarclcs as $idautorizadas) {
                $opc = new stdClass();
                $opc->sis_id = 'S';
                $opc->men_id = '2';
                $opc->opc_id = '2';
                $bloqueo = $this->model->gtBloqueos(
                    $idautorizadas->tcl_id,
                    $idautorizadas->stc_id,
                    $idautorizadas->ecf_id,
                    $opc
                );

                if ($bloqueo->valido == 0) {
                    $this->mixScript(' notify("' . $bloqueo->mensaje . ' ","error",false); ');
                    return false;
                }

                if (($idautorizadas->clc_recursoFederal == '1' && isset($_SESSION[_S]['prm']['S']['2']['2']['76']))
                    || ($idautorizadas->clc_recursoFederal == '2')) {
                    $this->conectar();
                    $sqlDatos = ' SET @usr_ip =INET_ATON(:ip),@usr_id = :usr ,@bit_observacion="AUTORIZACIÓN DE CLC DESDE PANTALLA S_2_2";';
                    $this->setSql($sqlDatos);
                    $params = [
                        ':ip'  => $ip,
                        ':usr' => $user];
                    $this->consultar($params);

                    $cedula .= $idautorizadas->clc_id . ',';
                    $sql = 'CALL sp_AutorizarCLC' . APP_EJERCICIO . '(:gpo_id,:ur_id,:clc_id,INET_ATON(:ip),:usr_id);';
                    $params = [
                        ':gpo_id' => $gpo_id,
                        ':ur_id'  => $ur_id,
                        ':clc_id' => $idautorizadas->clc_id,
                        ':ip'     => $ip,
                        ':usr_id' => $user];
                    $this->setSql($sql);
                    $this->consultar($params);
                    $this->setSql('SELECT @mensajePresupuesto as mensaje,@banderaPresupuesto as bandera');
                    $this->consultar();
                    $rowmensaje = $this->extraer();

                    if ($rowmensaje['bandera'] != 1) {
                        $this->mixScript(' notify("' . $rowmensaje['mensaje'] . '","error"); ');
                        return false;
                    }

                    $this->desconectar();
                } else {
                    $this->mixScript('notify("No tienes el permiso para procesar CLC´S de Recurso Federal","error",false);');
                    return false;
                }

                if ($idautorizadas->clc_recursoFederal == '1') {
                    $bandera = true;
                }
            }

            if ($bandera == true) {
                $mensaje = 'notify( "El proceco de la(s) CLC(s) ' . $cedula . ' de Recurso Federal se ha generado exitosamente ", "success");';
            } else {
                $mensaje = 'notify( "La(s) CLC(s) ' . $cedula . ' ha(n) sido enviadas (s)  correctamente ", "success");';
            }

            $this->mixScript('
                ' . $mensaje . '
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaClc",
                    tipo:"' . $this->prm->tcl_id . '",
                    tareaclc:"' . $this->prm->tareaclc . '"
                });
            ');
        } elseif (isset($this->prm->rechazarclcs) && count($this->prm->rechazarclcs) > 0) {

            foreach ($this->prm->rechazarclcs as $idrechazadas) {
                $cedula .= $idrechazadas->clc_id . ',';
                $this->conectar();
                $this->setSql('
                    SET @usr_id = :usr,
                    @usr_ip = INET_ATON(:ip),
                    @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
                ');
                $params = [
                    ':usr' => $this->usr_id,
                    ':ip'  => $this->usr_ip];
                $this->consultar($params);
                $this->setSql('
                    UPDATE scp_Clc
                    SET ecl_id = 1
                    WHERE gpo_id = :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id
                    AND ecl_id = 2;
                ');
                $params = [
                    ':gpo_id' => $idrechazadas->gpo_id,
                    ':ur_id'  => $idrechazadas->ur_id,
                    ':clc_id' => $idrechazadas->clc_id];
                $this->consultar($params);
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_Clc
                    (gpo_id,ur_id,clc_id,ecl_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    VALUES (:gpo_id,:ur_id,:clc_id,1,"CLC RECHAZADA POR LA UR",INET_ATON(:ip),:usr_id,NOW());
                ');
                $params = [
                    ':gpo_id' => $idrechazadas->gpo_id,
                    ':ur_id'  => $idrechazadas->ur_id,
                    ':clc_id' => $idrechazadas->clc_id,
                    ':ip'     => $ip,
                    ':usr_id' => $user];
                $this->consultar($params);

                $this->setSql('SET @bit_observacion = "LIBERA DOCUMENTO PDP POR CLC RECHAZADA UR"');
                $this->consultar();
                $sqldocumentos = '
                    UPDATE cat_BancoDocumentos doc
                    INNER JOIN (
                        SELECT gpo_id,ur_id,bdo_id
                        FROM scp_Contable a
                        INNER JOIN cat_BancoDocumentos b USING (gpo_id,ur_id,bdo_id)
                        WHERE gpo_id = :gpo_id
                        AND ur_id = :ur_id
                        AND clc_id = :clc_id
                        GROUP BY bdo_id
                        UNION ALL
                        SELECT gpo_id,ur_id,bdo_id
                        FROM scp_Presupuestal a
                        INNER JOIN cat_BancoDocumentos b USING (gpo_id,ur_id,bdo_id)
                        WHERE gpo_id = :gpo_id
                        AND ur_id = :ur_id
                        AND clc_id = :clc_id
                        GROUP BY bdo_id
                    ) AS b USING (gpo_id,ur_id,bdo_id)
                    SET bdo_pdp = "N"
                    WHERE doc.gpo_id = b.gpo_id
                    AND doc.ur_id = b.ur_id
                    AND doc.bdo_id = b.bdo_id;
                    ';
                $params = [
                    ':gpo_id' => $idrechazadas->gpo_id,
                    ':ur_id'  => $idrechazadas->ur_id,
                    ':clc_id' => $idrechazadas->clc_id];
                $this->setSql($sqldocumentos);
                $this->consultar($params);
                $this->desconectar();
            }

            $this->mixScript('
                notify( "La CLC (s) ' . $cedula . '  ha (n) rechazada (s) correctamente", "success");
                comunica("' . $this->pantalla . '",{
                    metodo:"llenaTablaClc",
                    tipo:"' . $this->prm->tipo . '",
                    tareaclc:"' . $this->prm->tareaclc . '"
                });
            ');
        }
    }

    public function VerificaContratos()
    {
        /*
        Lo que hace esta funcion es cargar los contratos disponibles para la obra que selecciona la UR
        Cuando se selecciona subtipo de CLC para las CLC´S  F,G y H .El  switch que se encuentra en la parte de abajo solo existe el caso para
        H porque como son contables, solo debe existir la afectacion autorizada para que se muestren los contratos disponibles a vincular a la CLC
        Para el caso de F y G  ahi si debe existir el compromiso y la afectacion autorizada
         */
        $this->prm->ipu_id = (isset($this->prm->ipu_id) && $this->prm->ipu_id != '') ? $this->prm->ipu_id : '';

        $this->setContenedor('BancoContratos');
        $Banco = new stdClass();
        $Banco->id = 'ipu_id';
        $Banco->class = 'limpiar habilitar';
        $mix = '';
        switch ($this->prm->tipo) {
            case 'H':
            case 'I':
                $Banco->sql = '
                SELECT if(ipu_id is null,"X",ipu_id )ipu_id,
                if(ipu_numeroContrato is null,"SIN CONTRATO",ipu_numeroContrato )ipu_numeroContrato
                FROM  scp_Afectacion a
                INNER JOIN scp_Contable using(gpo_id,ur_id,clc_id,afe_id)
                INNER JOIN ' . BD_SINPRES . '.cat_InversionPublica i on i.ipu_id=a.afe_ipu_id
                WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                AND   ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                AND   epg_id = ' . $_SESSION[_S]['clasAdm-epg'] . '
                AND  eaf_id="A"
                AND eip_id=2
                AND clc_id IN(0' . (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? ',' . $this->prm->clc_id : '') . ')
                group by gpo_id,ur_id,ipu_id,ipu_numeroContrato;
                ';
                break;
            default:
                $Banco->sql = '
                    SELECT
                    if(com.ipu_id is null
                    ,"X"
                    ,com.ipu_id) AS ipu_id,
                    if(com.ipu_numeroContrato is null
                    ,"SIN CONTRATO"
                    ,com.ipu_numeroContrato) AS ipu_numeroContrato
                    FROM  scp_Afectacion
                    INNER JOIN scp_Presupuestal USING (gpo_id,ur_id,clc_id,afe_id,epg_id)
                    INNER JOIN scp_Compromiso com USING (gpo_id,ur_id,cmp_id,epg_id,tcl_id)
                    INNER JOIN (
                        SELECT ipu_id
                        FROM ' . BD_SINPRES . '.cat_InversionPublica
                        INNER JOIN ' . BD_SINPRES . '.vin_UnidadResponsable USING (vur_id)
                        INNER JOIN (
                            SELECT uur_id
                            FROM ' . BD_SINPRES . '.vin_UnidadResponsable
                            WHERE
                            gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                            AND ur_id= ' . $_SESSION[_S]['clasAdm-ur'] . '
                            AND vur_ejercicio = ' . APP_EJERCICIO_ANTERIOR . '
                        ) b USING (uur_id)
                    )v USING (ipu_id)
                    WHERE
                    gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . '
                    AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . '
                    AND epg_id = ' . $_SESSION[_S]['clasAdm-epg'] . '
                    AND eaf_id="A"
                    AND clc_id IN(0' . (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? ',' . $this->prm->clc_id : '') . ')
                    group by gpo_id,ur_id,ipu_id,ipu_numeroContrato;
                    ';
                break;
        }

        $Sql = $Banco->sql;
        $Banco->label = 'Vincular Contrato';
        $Banco->textSinRegistro = 'OBRA SIN CONTRATO';
        $Banco->comoAtr = 'ipu_numeroContrato';
        $Banco->change = '
            if( $(this).val() != ""){
                comunica("' . $this->pantalla . '",{
                        metodo: "cargaDatosObra",
                        "tipo":"' . $this->prm->tipo . '",
                        "stc_id":"' . $this->prm->stc_id . '",
                        "Garantias":$("#DocGarantias").is(":checked"),
                        "ipu_id":$("#ipu_id").val(),
                        "ipu_numeroContrato":$(this).find("option:selected").attr("ipu_numeroContrato")
                });
            }
        ';

        $Banco->enRow = 1;
        if ($this->prm->ipu_id != '') {
            $Banco->selecciona = $this->prm->ipu_id;
        }
        $Banco = $this->ponCombo($Banco);

        $clase = '';
        $nr = $this->getNumeroRegistros();
        if ($nr == 0) {
            $clase = 'error';
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql(' ' . $Sql . ' ');
        $conexion->consultar();
        $conexion->desconectar();
        $rw = $conexion->extraer();

        if ($nr == 0 || $rw['ipu_id'] == 'X') {
            switch ($this->prm->tipo) {
                case 'F':
                    $mix = '
                        $("#BancoContratos").empty();
                        $("#finiquito").val("02").attr("disabled","disabled").change().selectpicker("refresh");
                    ';
                    break;
                case 'G':
                    $mix = '
                        $("#BancoContratos").empty();
                        $("#finiquito").val("00").attr("disabled","disabled").change().selectpicker("refresh");
                    ';
                    break;
            }
        }
        $this->mixScript(' ' . $mix . ' ');
        $this->mixHtml('
                <div class="row">
                    <fieldset class="col-md-12 col-xs-12 ' . $clase . ' ">
                        ' . $Banco . '
                    </fieldset>
                </div>

            ');
    }

    public function cargaDatosObra()
    {
        $this->conectar();
        $this->setSql('
            SELECT oa_id,oa_nombre,oa_numObra,oa_' . APP_EJERCICIO_ANTERIOR . ', mun_nombre,loc_nombre,oa_origen,
            ore_oficioaut,sft_nombre, ter_nombre,cfi_anio
            FROM cat_ObraActividad
            INNER JOIN enc_UbicacionGeografica USING(eug_id)
            INNER JOIN cat_Region USING (reg_id)
            INNER JOIN cat_Distrito USING (reg_id,dto_id)
            INNER JOIN cat_Municipio USING (reg_id,dto_id,mun_id)
            INNER JOIN cat_Localidad USING (reg_id,dto_id,mun_id,loc_id)
            INNER JOIN enc_Programatico USING (gpo_id,ur_id,prg_id,spr_id,py_id,oa_id)
            INNER JOIN poa_OrigenRecurso USING (gpo_id, ur_id, epg_id)
            INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
            INNER JOIN ' . BD_SINPRES . '.cat_Subfuente' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id,fte_id,sft_id,ori_id,sor_id)
            INNER JOIN ' . BD_SINPRES . '.cat_TipoRecurso USING (ter_id)
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND prg_id = :prg_id
            AND spr_id = :spr_id
            AND py_id = :py_id
            AND oa_id = :oa_id;
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'  => $_SESSION[_S]['clasAdm-ur'],
            ':prg_id' => $_SESSION[_S]['clasAdm-prg'],
            ':spr_id' => $_SESSION[_S]['clasAdm-spr'],
            ':py_id'  => $_SESSION[_S]['clasAdm-py'],
            ':oa_id'  => $_SESSION[_S]['clasAdm-oa']];
        $this->consultar($params);
        $datos = $this->extraer();
        $datos['oa_nombre'] = str_replace(['"', "\n\r", "\r", "\n"], '', $datos['oa_nombre']);
        $datos['mun_nombre'] = str_replace('"', '', $datos['mun_nombre']);
        $datos['loc_nombre'] = str_replace('"', '', $datos['loc_nombre']);

        $this->setSql('
            SELECT ue_nombre
            FROM cat_UnidadEjecutora
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND ue_id = :ue_id;
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['clasAdm-gpo'],
            ':ur_id'  => $_SESSION[_S]['clasAdm-ur'],
            ':ue_id'  => $_SESSION[_S]['clasAdm-ue']];
        $this->consultar($params);
        $beneficiario = $this->extraer();
        $this->desconectar();

        $nombreObra = str_replace(["\r\n", "\r", "\n"], '', $datos['oa_nombre']);

        $this->mixScript('
            $("#cumplimiento").val("' . $datos['oa_id'] . ' - ' . $nombreObra . '");
            $("#numobra").val("' . $datos['oa_numObra'] . '");
            $("#referencia").val("' . $datos['oa_' . APP_EJERCICIO_ANTERIOR] . '");
            $("#municipio").val("' . str_replace('"', '', $beneficiario['ue_nombre']) . '");
            $("#oficioaut").val("' . $datos['ore_oficioaut'] . '");
            $("#subfuente").val("' . $datos['sft_nombre'] . '");
            $("#aportacion").val("' . $datos['ter_nombre'] . '");
            $("#ejercicio").val("' . $datos['cfi_anio'] . '");

            var concepto = "";
            concepto = concepto.replace("' . mb_strtoupper($nombreObra . ' - ' . $datos['mun_nombre'] . ' - ' . $datos['loc_nombre'], 'UTF-8') . '","");
            $("#conceptoObra").val(concepto + "' . mb_strtoupper($nombreObra . ' - ' . $datos['mun_nombre'] . ' - ' . $datos['loc_nombre'], 'UTF-8') . '");
            $("#conceptoObra").blur(function(){
                var concepto = $("#conceptoObra").val();
                $("#conceptoObra").val(concepto + " ' . mb_strtoupper($datos['oa_nombre'] . ' - ' . $datos['mun_nombre'] . ' - ' . $datos['loc_nombre'], 'UTF-8') . '");
            }).focus(function(){
                var concepto = $("#conceptoObra").val();
                concepto = concepto.replace("' . mb_strtoupper($datos['oa_nombre'] . ' - ' . $datos['mun_nombre'] . ' - ' . $datos['loc_nombre'], 'UTF-8') . '","");
                $("#conceptoObra").val(concepto);
            });
        ');

        if (($this->prm->tipo == 'F' && $this->prm->stc_id != 02) || ($this->prm->tipo == 'G' && $this->prm->stc_id == 01) || ($this->prm->tipo == 'H' && $this->prm->stc_id == 00)) {

            if (($this->prm->tipo == 'F' && $this->prm->stc_id != 02 && empty($this->prm->Garantias)) || ($this->prm->tipo == 'G' && $this->prm->stc_id == 01) || ($this->prm->tipo == 'H' && $this->prm->stc_id == 00)) {
                $contratos = ValidaGarantias::ValidaGarantia($_SESSION[_S]['clasAdm-epg'], $this->prm->tipo, $this->prm->stc_id, $this->prm->ipu_id);
                if (isset($contratos->valido) && $contratos->valido == false) {
                    switch ($contratos->error) {
                        case 3:
                            $this->mixScript(' notify(" ' . $contratos->mensaje . '","warning"); ');
                            break;
                        default:
                            $this->mixScript(' notify(" ' . $contratos->mensaje . '","warning"); ');
                            break;
                    }
                }
            }

            $conexion2 = new AccesoDatos();
            $conexion2->conectar();

            /* SI LA CLC ES DE CONTRATO Y DE SUBTIPO FINIQUITO TIENE QUE APLICAR LAS GARANTIAS, PERO SI EN EL CHECK ELIJIERON DOCUMENTOS ENVAIDOS EN EJERCICIOS PASADOS
            LA VALIDACIÓN NO APLICA PARA LA ESTIMACIÓN QUE ES EL SUBTIPO 00 Y SE VA AL ELSE PARA QUE CARGUE EL OFICIO DE AUTORIZACIÓN Y EL NOMBRE DEL CONTRATO.*/

            if (($this->prm->tipo == 'F' && $this->prm->stc_id != 02 && $this->prm->stc_id != 00)
                || ($this->prm->tipo == 'G' && $this->prm->stc_id == 01)
                || ($this->prm->tipo == 'H' && $this->prm->stc_id == 00)
            ) {
                $conexion2->setSql(
                    '
                    SELECT gpo_id,ur_id,ipu_numeroContrato,ipu_id,epg_id,bg.bga_id,obr.dur_id,obr.dur_id,
                    bga_numeroDocumento,dur_nombreOriginal,
                    tan_id,tga_id,tga_nombre,stg_id,stg_nombre
                    FROM enc_InversionObra obr
                    INNER JOIN ' . BD_SINPRES . '.cat_InversionPublica USING ( ipu_id )
                    INNER JOIN ' . BD_SINPRES . '.enc_InversionObras USING(ipu_id,epg_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_DocumentosUr du ON obr.dur_id= du.dur_id
                    LEFT JOIN ' . BD_SINPRES . '.enc_ContratoGarantia USING(ipu_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_BancoGarantia bg USING(bga_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia USING(tga_id,stg_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_TipoGarantia USING(tga_id)
                    LEFT JOIN ' . BD_SINPRES . '.enc_GarantiaDocumento gd ON bg.bga_id=gd.bga_id
                    WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND epg_id = :epg_id
                    AND ipu_id= :ipu_id
                    AND ipu_numeroContrato= :ipu_numero
                    AND tan_id in(1,2,3,4,5);'
                );
            } else {
                $sql = '
                    SELECT
                    gpo_id,ur_id,ipu_numeroContrato,ipu_id,epg_id,obr.dur_id,
                    dur_nombreOriginal,tga_id,tga_nombre,stg_id,stg_nombre,bga_numeroDocumento
                    FROM enc_InversionObra obr
                    INNER JOIN ' . BD_SINPRES . '.cat_InversionPublica USING ( ipu_id )
                    INNER JOIN ' . BD_SINPRES . '.enc_InversionObras USING(ipu_id,epg_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_DocumentosUr du ON obr.dur_id= du.dur_id
                    LEFT JOIN ' . BD_SINPRES . '.enc_ContratoGarantia USING(ipu_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_BancoGarantia USING(bga_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_SubtipoGarantia USING(tga_id,stg_id)
                    LEFT JOIN ' . BD_SINPRES . '.cat_TipoGarantia USING(tga_id)
                    WHERE gpo_id = :gpo_id
                    AND ur_id = :ur_id
                    AND epg_id = :epg_id
                    AND ipu_id= :ipu_id
                    AND ipu_numeroContrato= :ipu_numero;
                ';
                $conexion2->setSql($sql);
            }
            $params = [
                ':gpo_id'     => $_SESSION[_S]['clasAdm-gpo'],
                ':ur_id'      => $_SESSION[_S]['clasAdm-ur'],
                ':epg_id'     => $_SESSION[_S]['clasAdm-epg'],
                ':ipu_id'     => $this->prm->ipu_id,
                ':ipu_numero' => $this->prm->ipu_numeroContrato];
            $conexion2->consultar($params);
            $conexion2->desconectar();

            if ($conexion2->getNumeroRegistros() != 0) {
                while ($contrato = $conexion2->extraer()) {
                    $this->mixScript('
                        $("#obracontrato").val("' . $contrato['ipu_numeroContrato'] . '").attr("readonly",true);
                        $("#oficioaut").val("' . $contrato['dur_nombreOriginal'] . '").attr("readonly",true);
                    ');

                    switch ($contrato['tga_id']) {
                        case 'A':
                            $this->mixScript('
                                $("#anticipo").val("' . $contrato['bga_numeroDocumento'] . '").attr("readonly",true);
                            ');
                            break;
                        case 'C':
                            $this->mixScript('
                                $("#fianza").val("' . $contrato['bga_numeroDocumento'] . '").attr("readonly",true);
                            ');
                            break;
                        case 'V':
                            if ($this->prm->tipo == 'F' && $this->prm->stc_id = 01) {
                                $this->mixScript('  $("#vicios").val("' . $contrato['bga_numeroDocumento'] . '").attr("readonly",true); ');
                            }
                            break;
                    }
                }
            }
        }
    }

    private function ClcDesfadas()
    {
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $this->conectar();
        $this->setSql('
            SELECT clc_id
            FROM scp_Clc
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id !=0
            AND ecl_id < 3
            ANd month(clc_fechaElaboracion) < month( now() );
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['gpo_id'],
            ':ur_id'  => $_SESSION[_S]['ur_id']];
        $this->consultar($params);

        $r = '(';
        while ($datos = $this->extraer()) {
            if ($r == '(') {
                $r .= $datos['clc_id'];
            } else {
                $r .= ',' . $datos['clc_id'];
            }
        }
        $r .= ')';

        if ($r != '()') {
            $this->setSql('
                SET
                @bit_observacion = "LIBERA DOCUMENTO PDP POR CLC DESFASADA",
                @usr_id = :usr ,
                @usr_ip = INET_ATON(:ip);
            ');
            $params = [
                ':usr' => $user,
                ':ip'  => $ip];
            $this->consultar($params);
            $this->setSql('
                UPDATE cat_BancoDocumentos doc, (
                    SELECT gpo_id,ur_id,bdo_id
                    FROM scp_Contable a
                    INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
                    WHERE gpo_id= ? and ur_id= ? and clc_id in ' . $r . '
                    GROUP BY bdo_id
                    UNION ALL
                    SELECT gpo_id,ur_id,bdo_id
                    FROM scp_Presupuestal a
                    INNER JOIN cat_BancoDocumentos b USING ( gpo_id,ur_id,bdo_id )
                    WHERE gpo_id= ? and ur_id= ? and clc_id in ' . $r . '
                    GROUP BY bdo_id) AS b
                SET bdo_pdp= "N"
                WHERE doc.gpo_id=b.gpo_id
                AND doc.ur_id=b.ur_id
                AND doc.bdo_id=b.bdo_id;
            ');
            $params = [$_SESSION[_S]['gpo_id'], $_SESSION[_S]['ur_id'], $_SESSION[_S]['gpo_id'], $_SESSION[_S]['ur_id']];
            $this->consultar($params);
        }
        $this->setSql('
            SET
            @usr_id = :usr,
            @usr_ip = INET_ATON(:ip),
            @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
        ');
        $params = [
            ':usr' => $this->usr_id,
            ':ip'  => $this->usr_ip];
        $this->consultar($params);
        $this->setSql('
            UPDATE scp_Clc set ecl_id=1
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id !=0
            ANd ecl_id < 3
            ANd month(clc_fechaElaboracion) < month( now() )
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['gpo_id'],
            ':ur_id'  => $_SESSION[_S]['ur_id']];
        $this->consultar($params);

        $this->setSql('
            SELECT gpo_id,ur_id,clc_id,afe_id
            FROM scp_Afectacion
            INNER JOIN scp_Clc USING(gpo_id,ur_id,clc_id)
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id !=0
            AND ecl_id < 3
            AND month(clc_fechaElaboracion) < month( now() )
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['gpo_id'],
            ':ur_id'  => $_SESSION[_S]['ur_id']];
        $this->consultar($params);

        while ($reg = $this->extraer()) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            $conexion->setSql('
                SET @usr_id = :usr,
                @usr_ip = INET_ATON(:ip),
                @bit_observacion = "CLC Desfasadas";
            ');
            $params = [
                ':usr' => $user,
                ':ip'  => $ip];
            $conexion->consultar($params);
            $conexion->setSql('
                UPDATE scp_Afectacion
                SET clc_id = 0
                WHERE gpo_id = :gpo_id
                AND ur_id = :ur_id
                AND clc_id = :clc_id
                AND afe_id = :afe_id
            ');
            $params = [
                ':gpo_id' => $reg['gpo_id'],
                ':ur_id'  => $reg['ur_id'],
                ':clc_id' => $reg['clc_id'],
                ':afe_id' => $reg['afe_id']];
            $conexion->consultar($params);
            $conexion->desconectar();
        }

        $this->setSql('
            SET @usr_id = :usr,
            @usr_ip = INET_ATON(:ip),
            @bit_observacion="UPDATE DESDE [' . __FUNCTION__ . '][' . __LINE__ . ']";
        ');
        $params = [
            ':usr' => $this->usr_id,
            ':ip'  => $this->usr_ip];
        $this->consultar($params);

        $this->setSql('
            UPDATE scp_Clc set ecl_id=20
            WHERE gpo_id = :gpo_id
            AND ur_id = :ur_id
            AND clc_id !=0
            AND ecl_id < 3
            AND ecl_id = 1
            AND month(clc_fechaElaboracion) < month( now() )
        ');
        $params = [
            ':gpo_id' => $_SESSION[_S]['gpo_id'],
            ':ur_id'  => $_SESSION[_S]['ur_id']];
        $this->consultar($params);

        $this->desconectar();
        $this->mixScript('
            notify( "Se han inhabilitado las CLC\'s\npuedes cancelar las afectaciones que estas contenian", "success");
        ');
    }

    private function ValidarClcAjuste()
    {
        if ($this->prm->clc_original == '') {
            $this->mixScript('
                notify( "Ingresa el número de CLC original", "warning");
            ');
        } else {
            $this->conectar();
            $this->consultar();
            $this->setSql('
                SELECT gpo_id,ur_id,clc_id
                FROM scp_MaestroRectificacion a
                JOIN scp_Clc using(gpo_id,ur_id,clc_id)
                WHERE gpo_id= :gpo_id
                AND ur_id= :ur_id
                AND clc_id= :clc_id
                AND tcl_id=:tcl_id
                AND a.ecl_id=6;
            ');
            $params = [
                ':gpo_id' => $_SESSION[_S]['gpo_id'],
                ':ur_id'  => $_SESSION[_S]['ur_id'],
                ':clc_id' => $this->prm->clc_original,
                ':tcl_id' => $this->prm->tipo];
            $this->consultar($params);
            $this->desconectar();
            if ($this->getNumeroRegistros() != 0) {
                $this->conectar();
                $this->setSql('
                    SELECT clc_fechaElaboracion
                    FROM scp_Clc
                    WHERE gpo_id= :gpo_id
                    AND ur_id= :ur_id
                    AND clc_id= :clc_id;
                ');
                $params = [
                    ':gpo_id' => $_SESSION[_S]['gpo_id'],
                    ':ur_id'  => $_SESSION[_S]['ur_id'],
                    ':clc_id' => $this->prm->clc_original];
                $this->consultar($params);
                $datosR = $this->extraer();
                $this->desconectar();
                $fechaAjuste = new StdClass();
                $fechaAjuste->id = 'fechaAjuste';
                $fechaAjuste->label = 'Fecha de Elaboracion ';
                $fechaAjuste->enDiv = true;
                $fechaAjuste->readonly = true;
                $fechaAjuste->value = $datosR['clc_fechaElaboracion'];
                $this->setContenedor('contFechaAjuste');
                $this->mixHtml($this->ponInput($fechaAjuste));
            } else {
                $this->mixScript('$("#contFechaAjuste").empty();
                    notify( "No puedes crear una CLC de ajuste de la CLC ' . $this->prm->clc_original . ' no ha sido rectificada o no es del tipo correcto", "warning");');
                $this->main();
            }
        }
    }

    private function ValidarRectificacion()
    {
        if ($this->prm->rectificacion == '') {
            $this->mixScript('
                notify( "Ingresa el número de Rectificación", "error");
                ');
        } else {
            $this->conectar();
            $this->setSql('
                SELECT  rct_id
                FROM scp_MaestroRectificacion
                WHERE gpo_id= :gpo_id
                AND ur_id= :ur_id
                AND  rct_id= :rct_id
                AND  ecl_id=6 ;
            ');
            $params = [
                ':gpo_id' => $_SESSION[_S]['gpo_id'],
                ':ur_id'  => $_SESSION[_S]['ur_id'],
                ':rct_id' => $this->prm->rectificacion];
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() != 0) {
                $this->mixScript('notify( "La  Rectificación ' . $this->prm->rectificacion . ' es correcta ", "success");');
            } else {
                $this->mixScript('
                    $("#inputRect").remove();
                    $("#clcRect").prop("checked",false);
                    notify( "No puedes crear una CLC de  la  Rectificación ' . $this->prm->rectificacion . ' , no ha sido aplicada  o no existe ", "error");
                    ');
                return false;
            }
        }
    }


    public function ValidaAnticipoPorAmortizar($tcl_id)
    {
        $retorno = new stdClass();
        $retorno->valido = true;
        $retorno->mensaje = '';
        //SE VALIDAN LOS TIPOD DE CLC C-PAGOS A TERCEROS, F-CONTRATO Y G-ADMINISTRACIÓN DIRECTA
        if (in_array($tcl_id, ['C','F','G'])) {
            $this->conectar();
            $this->setSql('
                WITH 
                    amortizacion AS(
                        SELECT epg_id, ecf_id, SUM(ncc_retencion) AS amortizando
                        FROM scp_Afectacion
                        JOIN scp_Contable USING(gpo_id, ur_id, clc_id, afe_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND clc_id  = :clc_id
                        AND (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) IN ( (2,1,1,9,4,005), (2,1,1,9,4,001), (1,1,3,4,2,001), (1,1,3,1,0,001) )
                        GROUP BY epg_id, ecf_id
                    ),
                    obrasCfiInversion AS(
                        SELECT
                            gpo_id, ur_id, epg_id, ecf_id, epg_2025, oa_origen,
                            CONCAT(gpo_id, ur_id, oa.ue_id,"-", prg_id,"-", spr_id,"-", py_id,"-", oa_id)AS epg_Clave,
                            vigente - ejercido AS porEjercer, SUM(ncp_percepcionNeto)AS aEjercer, IFNULL(amortizando,0)AS amortizando
                        FROM cat_ObraActividad AS oa
                        JOIN enc_Programatico USING( gpo_id, ur_id, prg_id, spr_id, py_id, oa_id )
                        JOIN poa_OrigenRecurso AS ore USING( gpo_id, ur_id, epg_id )
                        LEFT JOIN amortizacion USING(epg_id, ecf_id)
                        JOIN( 
                            SELECT epg_id, ecf_id, SUM(pre_vigente)AS vigente, SUM(pre_ejercido)AS ejercido
                            FROM presupuestoVigente
                            JOIN presupuestoEjercido USING(epg_id, ecf_id, pg_id, pe_id, tcp_id)
                            GROUP BY epg_id, ecf_id 
                        )AS p USING(epg_id, ecf_id)
                        JOIN scp_Presupuestal USING(gpo_id, ur_id, epg_id, ecf_id)
                        WHERE gpo_id = :gpo_id AND ur_id = :ur_id AND clc_id  = :clc_id
                        GROUP BY epg_id, ecf_id
                    ),
                    clcsContable AS(
                        SELECT DISTINCT
                            gpo_id, ur_id, epg_id, ecf_id,
                            clc_id, afe_id, ncc_id, tcl_id, ecl_id, ecl_nombre,
                            cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id, cta_cuenta, cta_nombre, 
                            ncc_percepcion, ncc_retencion, ncc_concepto
                        FROM obrasCfiInversion
                        JOIN scp_Afectacion USING( gpo_id, ur_id, epg_id, ecf_id)
                        JOIN scp_Clc USING( gpo_id, ur_id, clc_id, ecf_id, tcl_id )
                        JOIN cat_EstadoClc USING(ecl_id)
                        JOIN scp_Contable USING( gpo_id, ur_id, clc_id, afe_id )
                        JOIN cg_PlanCuentas USING(cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id,cgc_id, cta_id)
                        WHERE ( ecl_id BETWEEN 6 AND 19 ) AND tcl_id = "H" AND ncc_percepcion > 0 
                        OR( (tcl_id != "H" AND ncc_retencion > 0) AND (ger_id,cgp_id,rub_id,cgcu_id,cgsc_id,cgc_id) IN ( (2,1,1,9,4,005), (2,1,1,9,4,001), (1,1,3,4,2,001), (1,1,3,1,0,001) ) )  
                    ),
                    rectificacionAnticipos AS(
                        SELECT epg_id, ecf_id, SUM(rc.ncc_percepcion) AS anticiposRectificado
                        FROM clcsContable
                        JOIN scp_RectificacionContable AS rc USING(gpo_id, ur_id, clc_id, afe_id, ncc_id)
                        JOIN scp_MaestroRectificacion AS mr USING(gpo_id, ur_id, rct_id, clc_id)
                        WHERE tcl_id = "H" AND mr.ecl_id = 6
                        GROUP BY epg_id, ecf_id
                    ),
                    anticipos AS(
                            SELECT epg_id, ecf_id, SUM(ncc_percepcion)AS anticipo, SUM(ncc_retencion)AS retencion, IFNULL(anticiposRectificado,0) AS anticiposRectificado
                            FROM clcsContable
                            LEFT JOIN rectificacionAnticipos USING( epg_id, ecf_id )
                            WHERE tcl_id = "H"
                            GROUP BY epg_id, ecf_id
                    ),
                    rectificacionAmortizaciones AS(
                            SELECT epg_id, ecf_id, SUM(rc.ncc_retencion) AS amortizacionRectificada
                            FROM clcsContable
                            JOIN scp_RectificacionContable AS rc USING(gpo_id, ur_id, clc_id, afe_id, ncc_id)
                            JOIN scp_MaestroRectificacion AS mr USING(gpo_id, ur_id, rct_id, clc_id)
                            WHERE tcl_id != "H" AND mr.ecl_id = 6
                            GROUP BY epg_id, ecf_id    
                    ),
                    amortizaciones AS(
                            SELECT epg_id, ecf_id, SUM(ncc_percepcion)AS percepcion, SUM(ncc_retencion)AS amortizacion, IFNULL(amortizacionRectificada,0) AS amortizacionRectificada
                            FROM clcsContable
                            LEFT JOIN rectificacionAmortizaciones USING( epg_id, ecf_id )
                            WHERE tcl_id != "H" 
                            GROUP BY epg_id, ecf_id
                    )

                SELECT 
                    epg_Clave,
                    ( ( IFNULL(anticipo, 0)- IFNULL(anticiposRectificado,0) ) + IFNULL(coo_anticipo,0) ) - ( ( IFNULL(amortizacion,0) - IFNULL(amortizacionRectificada,0) ) - IFNULL(reembolso,0) ) AS porAmortizar,
                    porEjercer - aEjercer AS saldoParaAmortizar
                FROM obrasCfiInversion
                LEFT JOIN cat_ComplementoObra USING(gpo_id, ur_id, epg_id, ecf_id)
                LEFT JOIN anticipos USING(epg_id, ecf_id)
                LEFT JOIN amortizaciones USING(epg_id, ecf_id)
                LEFT JOIN(
                    SELECT epg_id, ecf_id, SUM(rao_importe)AS reembolso
                    FROM scp_ReintegroAnticipoObra
                    GROUP BY epg_id, ecf_id
                )rem USING(epg_id, ecf_id)
                HAVING saldoParaAmortizar < porAmortizar;
            ');
            $parametros = [
                ':gpo_id' => $this->gpo_id,
                ':ur_id'  => $this->ur_id,
                ':clc_id' => $this->prm->clc_id,
            ];
            $this->consultar($parametros);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $retorno->valido = false;
                while ($row = $this->extraer()) {
                    $retorno->mensaje .= '<tr><td>' . $this->prm->clc_id . '</td><td>No es posible ejercer el importe solicitado para la obra ' . $row['epg_Clave'] . ', debido a que existe un anticipo de obra pendiente de amortizar por: ' . number_format($row['porAmortizar'], 2) . '</td></tr>';
                }
            }
        }//fin if

        return $retorno;
    } // Fin getValidaFoliosDRH

}
if (isset($_SESSION[_S]['prm']['S']['2']['2'])) {
    new ControladorClc();
} else {
    WebResponse::sinPermisos();
}
