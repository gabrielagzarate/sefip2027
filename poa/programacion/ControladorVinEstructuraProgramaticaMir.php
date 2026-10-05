<?php

/**
 * ControladorVinEstructuraProgramaticaMir
 *
 * Permite el registro de las metas en las obras/actividades
 * de los programas menores a 900
 *
 * PHP version 7
 *
 * @category  Consulta
 * @package   Package
 * @author    Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * @Collaborators Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * @updated    13/05/2026
 * @copyright 2018 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 */
declare (strict_types=1);

session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/EstadoVinculacion.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/sp_RegistroMetaActividad.php';
require_once dirname(__FILE__) . '/../../ent/poa/programacion/sp_RegistroMetaActividadTrimestre.php';
/**
 * Clase ControladorVinEstructuraProgramaticaMir
 *
 * Permite registrar las metas de las obras/actividades
 *
 *
 * PHP version 7.0
 *
 * @category  Class
 * @package   Package
 * @author    Adriana Santiago Morales <adriana.morales@finanzasoaxaca.gob.mx>
 * @copyright 2018 Secretaria de Finanzas del Gobierno del Estado de Oaxaca
 * @license   http://www.fsf.org/licensing/licenses/gpl.txt GPL 2 or later
 */
class ControladorVinEstructuraProgramaticaMir extends WebResponse
{
    public $ur;
    public $ue;
    public $presupuesto;
    public $edoVin;
    public $edoVinUR;
    public $nivelRevision;
    public $permisoUR_Alta;
    public $permisoUr_Revision;
    public $permisoSefin_Revision;
    public $permisoSefin_Validacion;
    public $usrNvj;
    public $usrIp;
    public $usrId;
    public $clasAdmGpo;
    public $clasAdmUr;
    public $clasAdmUe;
    public $permisoVinculacion;
    public $permisoVinculacionValSefin;
    public $enPoa;
    public $en1Enero;
    public $evi;
    /**
     *Constructor
     *
     *@return Json
     */
    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();
        $this->pantalla = 'poa/programacion/ControladorVinEstructuraProgramaticaMir.php';

        //Permisos de acceso a las tareas del controlador
        $this->ur = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 2
            && isset($_SESSION[_S]['prm']['A']['2']['6']) ? true : false);

        $this->ue = (isset($_SESSION[_S]['nvj_id']) && $_SESSION[_S]['nvj_id'] == 1 ? true : false);
        $this->presupuesto = $this->presupuesto = (isset($_SESSION[_S]['ssc_id']) && $_SESSION[_S]['ssc_id'] == 3 ? true : false);
        $this->edoVin = (isset($this->prm->edoVin) && $this->prm->edoVin != '' ? $this->prm->edoVin : '');
        $this->nivelRevision = (isset($this->prm->nivelRevision) && $this->prm->nivelRevision != '' ? $this->prm->nivelRevision : '');

        //Permisos del metodo main - 28-05-2019
        $this->permisoUR_Alta = (isset($_SESSION[_S]['prm']['A']['2']['6']['93']) && ($_SESSION[_S]['nvj_id'] == 1 || $_SESSION[_S]['nvj_id'] == 2)) ? true : false;

        $this->permisoUr_Revision = (isset($_SESSION[_S]['prm']['A']['2']['6']['94']) && ($_SESSION[_S]['nvj_id'] == 2 && $_SESSION[_S]['prf_id'] == 'T')) ? true : false;

        $this->permisoSefin_Revision = (isset($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['nvj_id'] >= 3)) ? true : false;

        $this->permisoSefin_Validacion = ((isset($_SESSION[_S]['ssc_id']) && isset($_SESSION[_S]['prm']['A']['2']['6']['96'])) && ($_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['nvj_id'] >= 3)) ? true : false;

        //Permisos usuario - 28-05-2019
        $this->usrNvj = $_SESSION[_S]['nvj_id'];
        $this->usrIp = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $this->usrId = $_SESSION[_S]['usr_id'];

        //Clasificacion administrativa - 28-05-2019
        $this->clasAdmGpo = (isset($_SESSION[_S]['clasAdm-gpo']) && $_SESSION[_S]['clasAdm-gpo'] != '') ?
        $_SESSION[_S]['clasAdm-gpo'] : '';
        $this->clasAdmUr = (isset($_SESSION[_S]['clasAdm-ur']) && $_SESSION[_S]['clasAdm-ur'] != '') ?
        $_SESSION[_S]['clasAdm-ur'] : '';
        $this->clasAdmUe = (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '')
        ? $_SESSION[_S]['clasAdm-ue'] : '';

        //Permisos - 28-05-2019
        $this->permisoVinculacion = isset($_SESSION[_S]['prm']['A'][2][6]) ? true : false;
        $this->permisoVinculacionValSefin = isset($_SESSION[_S]['prm']['A'][2][6][96]) ? true : false;

        $this->enPoa = APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59';
        $this->en1Enero = '31-12-' . APP_EJERCICIO_ANTERIOR . ' 23:59:59';

        switch ($this->metodo) {
            case 'tablaEstructuraProgramatica':
                $this->tablaEstructuraProgramatica();
                break;
            case 'llenaTablaEstructuraProgramatica':
                $this->llenaTablaEstructuraProgramatica();
                break;
            case 'vinculacion':
                $this->vinculacion();
                break;
            case 'metas':
                $this->metas();
                break;
            case 'muestraMetas':
                $this->muestraMetas();
                break;
            case 'validaMeta':
                $this->validaMeta();
                break;
            case 'agregaMeta':
                $this->agregaMeta();
                break;
            case 'eliminaMeta':
                $this->eliminaMeta();
                break;
            case 'editaMeta':
                $this->editaMeta();
                break;
            case 'rechazarVinculacion':
                $this->rechazarVinculacion();
                break;
            case 'revisarVinculacion':
                $this->revisarVinculacion();
                break;
            case 'tablaVinculacionRead':
                $this->tablaVinculacionRead();
                break;
            case 'llenaTablaVinculacionRead':
                $this->llenaTablaVinculacionRead();
                break;
            case 'actualizaEstadoVinculacion':
                $this->actualizaEstadoVinculacion();
                break;
            case 'addMensajeRechazo':
                $this->addMensajeRechazo();
                break;
            case 'historialRechazo':
                $this->historialRechazo();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    /**
     *Función principal del Controlador
     *Muestra las opciones segun los permisos, nivel  y perfil del usuario,
     *UR,SEFIN
     */
    public function main()
    {
        $this->mixScript('$("#techos").show();');
        $botones = '<div style="width:100%; padding-left:30%; padding-bottom:50px;">';
        //Boton de alta de Metas de la obra actividad
        if ($_SESSION[_S]['gpo_id'] . $_SESSION[_S]['ur_id'] == 114) {
            if ($this->permisoUR_Alta) {

                $botones .= '<div class="boton_opcion altaVinculacion" titpp="Vinculación">';
                $botones .= '<div class="boton_opcion_titulo" style="font-weight:bold;">UR</div>';
                $botones .= '<div>';
                $botones .= '<div class="center altaVinculacion"><img style="width:40px;" src="img/Alta_Folio.png" /><br/>
                                <strong><small>Vinculación</small> </strong>
                            </div>';
                $botones .= '</div>';
                $botones .= '</div><div style="width:10px; display:table-cell"></div>';
            }
            //Boton de revisión de Vinculación de Metas
            if ($this->permisoUr_Revision) {
                $botones .= '<div class="boton_opcion revisaVinculacionUr" titpp="Revision de Vinculación">';
                $botones .= '<div class="boton_opcion_titulo" style="font-weight:bold;">UR</div>';
                $botones .= '<div>';
                $botones .= '<div class="center revisaVinculacionUr"> <img style="width:40px;" src="img/Revision_Folio.png" /><br/>
                                <strong><small>Revision de Vinculación</small> </strong>
                            </div>';
                $botones .= '</div>';
                $botones .= '</div><div style="width:10px; display:table-cell"></div>';
            }
        } else {
            if ($this->permisoUR_Alta || $this->permisoUr_Revision) {
                $this->mixScript('notify("La UR se encuentra inhabilitada para el uso de está opción","error");');
            }
        }
        //Boton de revisión de Vinculación de Metas
        if ($this->permisoSefin_Revision) {
            $botones .= '<div class="boton_opcion revisaVinculacionSefin" titpp="Revision de Vinculación">';
            $botones .= '<div class="boton_opcion_titulo" style="font-weight:bold;">SEFIN</div>';
            $botones .= '<div>';
            $botones .= '<div class="center revisaVinculacionSefin"> <img style="width:40px;" src="img/Validacion_Folio.png" /><br/>
                            <strong><small>Revision de Vinculación</small> </strong>
                        </div>';
            $botones .= '</div>';
            $botones .= '</div><div style="width:10px; display:table-cell"></div>';
        }
        //Boton de revisión de Vinculación de Metas
        if ($this->permisoSefin_Validacion) {
            $botones .= '<div class="boton_opcion validacionSefin" titpp="Valida Vinculación">';
            $botones .= '<div class="boton_opcion_titulo" style="font-weight:bold;">SEFIN</div>';
            $botones .= '<div>';
            $botones .= '<div class="center validacionSefin"> <img style="width:40px;" src="img/Autoriza_Folio.png" /><br/>
                            <strong><small>Valida Vinculación</small> </strong>
                        </div>';
            $botones .= '</div>';
            $botones .= '</div><div style="width:10px; display:table-cell"></div>';
        }

        $this->mixHtml($botones);
        $this->agregaTools('');
        $this->mixScript('
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
            $(".altaVinculacion").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaEstructuraProgramatica",
                });
                return false;
            });

            $(".revisaVinculacionUr").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVinculacionRead",
                    nivelRevision:"validacionUR"
                });
                return false;
            });
            $(".revisaVinculacionSefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVinculacionRead",
                    nivelRevision: "revisionSEFIN"
                });
                return false;
            });
            $(".validacionSefin").click(function(){
                comunica("' . $this->pantalla . '",{
                    metodo: "tablaVinculacionRead",
                    nivelRevision: "validacionSEFIN"
                });
                return false;
            });

        ');

        $this->mixScript('
            var indicadores  = {};
            var prodIntermedio = [];
            var indicadorRegistrar = [];
            var registraMeta = false;
            function creaTr(obj,prm){
                obj = $("#"+obj);
                indicadores[prm["ind_id"]] = prm;
                if( $("#indicador"+prm["ind_id"]).length == 1 ){
                    $("#indicador"+prm["ind_id"]).remove();
                }
                if(prm["epg_id"] == 0){
                    if(prodIntermedio.length == 0 || indicadorRegistrar.length == 0){
                        prodIntermedio[0] = prm["pdi_id"];
                        indicadorRegistrar[0] = prm["ind_id"];
                        registraMeta = true;
                    } else {
                        if(prodIntermedio[0] == prm["pdi_id"])
                        {
                            //Obtiene tamaño
                            var i = indicadorRegistrar.length;
                            indicadorRegistrar[i] = prm["ind_id"];
                            registraMeta = true;
                        } else {
                            registraMeta = false;
                            $("#metasActividad").modal("hide");
                            notify("ESTE TIPO DE OBRA NO PUEDE TENER MAS DE UN PRODUCTO INTERMEDIO", "error", );
                        }
                    }
                } else {
                    registraMeta = true;
                }

                if(registraMeta){
                    this.tr = $("<tr/>").attr("id","indicador"+prm["ind_id"]).attr("hidden","true");
                    this.ver = $("<td/>").append(
                        $("<span/>").addClass("btn btn-xs btn-success ").html("ver").click(function(){
                            comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                                metodo:"muestraMetas",
                                ind_id:prm["ind_id"],
                                pdi_id:prm["pdi_id"],
                                umv_id:prm["umv_id"],
                                tiv_nombre:prm["tiv_nombre"],
                                descripcion:["descripcion"],
                                trim_1:prm["t1"],
                                trim_2:prm["t2"],
                                trim_3:prm["t3"],
                                trim_4:prm["t4"],
                                pii_id:prm["pii_id"],
                                epg_id:prm["epg_id"],
                                gpo_id:prm["gpo_id"],
                                ur_id:prm["ur_id"],
                                prgs_id:prm["prgs_id"],
                                sprs_id:prm["sprs_id"],
                                py_id:prm["py_id"],
                                oa_id:prm["oa_id"],
                                edoVin:prm["edoVin"],
                            });
                        })
                    ).appendTo(this.tr);
                    this.eliminar = $("<td/>").append(
                        $("<span/>").addClass("btn  btn-xs btn-danger").html("eliminar").click(function(){
                            alertify.confirm(
                            "<h5> ELIMINAR META </h5>",
                            "¿Confirma que desea eliminar la meta?",
                            function(){
                                for(i=0; i<=indicadorRegistrar.length-1; i++){
                                    if(indicadorRegistrar[i]== prm["ind_id"])
                                    {
                                        indicadorRegistrar.splice(i,1);
                                    }
                                }
                                comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                                    metodo:"eliminaMeta",
                                    pii_id:prm["pii_id"],
                                    ind_id:prm["ind_id"],
                                    gpo_id:prm["gpo_id"],
                                    ur_id:prm["ur_id"],
                                    prgs_id:prm["prgs_id"],
                                    sprs_id:prm["sprs_id"],
                                    epg_id:prm["epg_id"]
                                });
                            },
                            function(){},
                        ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                        })
                    ).appendTo(this.tr);
                    this.inidicador = $("<td/>").append(
                        $("<input/>").attr("type","hidden").val("9").attr("name","nombre[]")
                    ).html(prm["nombre_ind"]).appendTo(this.tr);
                    this.medidaIndicador = $("<td/>").html(prm["umi_id"]).appendTo(this.tr);
                    this.tipoValor = $("<td/>").html(prm["tiv_id"]).appendTo(this.tr);
                    this.medida = $("<td/>").html(prm["umv_id"]).appendTo(this.tr);
                    this.meta = $("<td/>").html(prm["original"]).appendTo(this.tr);
                    this.indC = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["ind_id"] ).appendTo(this.tr);

                    this.c1 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["umv_id"] ).appendTo(this.tr);

                    this.c2 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["descripcion"]).appendTo(this.tr);
                    this.c3 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["t1"] ).appendTo(this.tr);
                    this.c4 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["t2"] ).appendTo(this.tr);
                    this.c5 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["t3"] ).appendTo(this.tr);
                    this.c6 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["t4"] ).appendTo(this.tr);
                    this.c7 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["pdi_id"] ).appendTo(this.tr);


                    obj.append(this.tr);
                    $("#metasActividad").modal("hide");
                }
            }

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
        ');
    }

    /**
     *Método que muestra la lista de los Encuadres Programáticos creados por la UR
     *
     *@return null
     */
    private function tablaEstructuraProgramatica()
    {
        $html = '';
        $this->mixScript('bloquearClasAdmin();');
        //Boton Regresar
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'circle-left me-2';
        $btnRegresar->class = 'btn btn-outline-secondary me-2';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"main"
            });
        ';
        $estadoSeleccionado = isset($this->prm->edoVin) ? $this->prm->edoVin : '';
        //Combo de estado de la vinculación
        $combo = new stdClass();
        $combo->id = 'evi_id';
        $combo->label = 'Filtro por estado';
        $combo->change = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaEstructuraProgramatica",
                edoVin:$(this).val()
            });
        ';
        $combo->sql = '
            SELECT
                evi_id AS e,
                evi_nombre
            FROM cat_EstadoVinculacion; ';
        if ($estadoSeleccionado != '') {
            $combo->selecciona = $estadoSeleccionado;
        }

        $tabla = new stdClass();
        $tabla->id = 'tbl-EstructuraProgramatica';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;

        //Definición de columnas
        $tr = [];
        //1.-Programa
        $th = new stdClass();
        $th->texto = 'PG';
        $th->width = '5px';
        array_push($tr, $th);

        //2.-Subprograma
        $th = new stdClass();
        $th->texto = 'SP';
        $th->width = '5px';
        array_push($tr, $th);

        //3.- Proyecto
        $th = new stdClass();
        $th->texto = 'PY';
        $th->width = '5px';
        array_push($tr, $th);

        //4.-Número de Obra/Actividad
        $th = new stdClass();
        $th->texto = 'O/A';
        $th->width = '5px';
        array_push($tr, $th);

        //5.-Descripción de la Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Descripción';
        $th->width = '5px';
        array_push($tr, $th);

        //6.-Tipo de la Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Tipo de Actividad';
        $th->width = '5px';
        array_push($tr, $th);

        //7.-Vincular
        $th = new stdClass();
        $th->texto = 'Vincular';
        $th->width = '5px';
        array_push($tr, $th);

        //8.-Enviar a validar
        //SI TIENE EL PERMISO DE VALIDAR Y SI ES NIVEL SUPERIOR A 2
        if ($this->usrNvj == 2 && $this->permisoVinculacion) {
            $th = new stdClass();
            $th->texto = 'Solicitar Revision';
            $th->width = '5px';
            array_push($tr, $th);
        }
        //9.-Enviar a validar
        $th = new stdClass();
        $th->texto = 'Status';
        $th->width = '30px';
        array_push($tr, $th);

        $tabla->encabezado = [$tr];

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaEstructuraProgramatica';
        $tabla->comunica->evi_id = '{$("#evi_id").val()}';
        $trbody = [];

        //1.-Programa
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);

        //2.-Subprograma
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);

        //3.-Proyecto
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);

        //4.-Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);

        //5.-Nombre Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);

        //6.-Tipo de Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);

        //7.-Habilita el botón de vinculación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'vinculacion';
        $td->casos->tipoCondicion = '==';
        $td->casos->valor = '0';

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'paper-plane';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->evi_id = 'evi_id';
        $td->casos->verdadero->attr->gpo_id = 'gpo_id';
        $td->casos->verdadero->attr->ur_id = 'ur_id';
        $td->casos->verdadero->attr->prgs_id = 'prg_id';
        $td->casos->verdadero->attr->sprs_id = 'spr_id';
        $td->casos->verdadero->attr->py_id = 'py_id';
        $td->casos->verdadero->attr->oa_id = 'oa_id';
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->class = 'vinculacionMir btn-outline-info';

        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'paper-plane';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->evi_id = 'evi_id';
        $td->casos->falso->attr->gpo_id = 'gpo_id';
        $td->casos->falso->attr->ur_id = 'ur_id';
        $td->casos->falso->attr->prgs_id = 'prg_id';
        $td->casos->falso->attr->sprs_id = 'spr_id';
        $td->casos->falso->attr->py_id = 'py_id';
        $td->casos->falso->attr->oa_id = 'oa_id';
        $td->casos->falso->attr->epg_id = 'epg_id';
        $td->casos->falso->class = 'vinculacionMir btn-outlines-uccess';

        array_push($trbody, $td);

        //8.-Enviar a validar
        switch ($this->usrNvj) {
            case 2: //UR
                if ($this->permisoVinculacion) {
                    $td = new stdClass();
                    $td->casos = new stdClass();
                    $td->casos->campo = 'toa_id';
                    $td->casos->valor = '1';
                    $td->casos->ands = [];
                    $and = new stdClass();
                    $and->campo = 'evi_id';
                    $and->operado = '||';
                    $and->valor = '1,3';
                    $and->valorTipo = 'numero';
                    array_push($td->casos->ands, $and);
                    $td->casos->verdadero = new stdClass();
                    $td->casos->verdadero->tipo = 'boton';
                    $td->casos->verdadero->color = 'black';
                    $td->casos->verdadero->icono = 'paper-plane';
                    $td->casos->verdadero->attr = new stdClass();
                    $td->casos->verdadero->attr->epg_id = 'epg_id';
                    $td->casos->verdadero->class = 'revisarVinculacion';
                    array_push($trbody, $td);
                } else {
                    $td = new stdClass();
                    $td->campoTxt = 'vacio';
                    array_push($tabla->cuerpo, $td);
                }
                break;
        }

        //9.-Estatus
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'evi_id';
        $td->casos->tipoCondicion = '||';
        $td->casos->valor = '1,2,3,4,5,6,7,8,9';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'bolita';
        $td->casos->verdadero->linea = 'evi_color';
        $td->casos->verdadero->color = 'evi_color';
        $td->casos->verdadero->campoTxt = 'evi_id';
        $td->casos->verdadero->lbl = 'evi_nombre';
        $td->casos->verdadero->title = 'evi_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        $this->mixHtml('
            <div id = "div_estados" class="col-md-11 col-xs-11" style = "display:none"></div>

                ' . $this->ponCombo($combo) . '
            </div>
        ');
        $this->mixHtml('
            <div id = "div_clave" class="col-md-12 col-xs-12" style = "display:none">' . $html . '</div>

                ' . $this->wrPonTabla($tabla) . '
            </div>
        ');
        $btnRegresa = $this->ponBoton($btnRegresar);
        $this->agregaTools($btnRegresa);
        $this->mixScript('
            $("#btnRegresar").removeClass("btn btn-default");
            $("#btnRegresar").addClass("btn btn-outline-secondary me-2");
        ');
        $this->llenaTablaEstructuraProgramatica();
    }

    //Método que contiene la consulta que llena el listado encuadres programáticos
    private function llenaTablaEstructuraProgramatica()
    {
        $esUR = $this->ur;
        $esUE = $this->ue;
        $sql = '';
        $filtro = '';
        $params = ['gpo' => $this->clasAdmGpo, 'ur' => $this->clasAdmUr];

        $this->prm->edoVin = isset($this->prm->edoVin) ? $this->prm->edoVin : (isset($this->prm->evi_id) ? $this->prm->evi_id : '');

        if (isset($this->prm->edoVin) && $this->prm->edoVin != 'null' && $this->prm->edoVin != '') {
            $params['evi'] = $this->prm->edoVin;
            $filtro = ' WHERE evi_id = :evi ;';
        }
        if ($esUR) {
            $sql = '
                SELECT
                    epg_id,gpo_id,ur_id,
                    prg_id,spr_id,py_id,
                    oa_id,oa_nombre,toa_id,
                    vinculacion,
                    IF(toa_id = 1,"SUB.PRESUPUESTO","SUB. PLANEACION") AS toa_nombre,
                    evi_id,evi_color,evi_nombre
                FROM (
                    SELECT
                        epg_id,gpo_id,ur_id,
                        prg_id,spr_id,py_id,
                        oa_id,oa_nombre,toa_id,
                        (
                            SELECT
                                count(epg_id)
                                FROM cat_MetaActividad
                                WHERE epg_id = e.epg_id
                                LIMIT 1) AS vinculacion,
                        (
                            SELECT
                                evi_id
                            FROM cat_MetaActividad
                            WHERE epg_id = e.epg_id
                            LIMIT 1) AS evi_id
                    FROM cat_ObraActividad
                        INNER JOIN enc_Programatico e USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    WHERE oa_id != 0
                        AND gpo_id = :gpo
                        AND ur_id = :ur
                        AND ((eoa_id = 5 AND toa_id = 2) OR (eoa_id = 4 AND toa_id = 1))
                        AND prg_id = 0
                        ) AS t
                    LEFT JOIN cat_EstadoVinculacion USING (evi_id)
                ' . $filtro . '
                ';
        } elseif ($esUE) {
            $params['ue'] = $this->prm->clasAdmUe;
            $sql = '
                SELECT
                    epg_id,gpo_id,ur_id,
                    prg_id,spr_id,py_id,
                    oa_id,oa_nombre,toa_id,
                    vinculacion,
                    IF(toa_id = 1,"SUB.PRESUPUESTO","SUB. PLANEACION") AS toa_nombre,
                    evi_id,evi_color,evi_nombre
                FROM (
                    SELECT
                        epg_id,gpo_id,ur_id,
                        prg_id,spr_id,py_id,
                        oa_id,oa_nombre,toa_id,
                        (
                            SELECT
                                count(epg_id)
                            FROM cat_MetaActividad
                            WHERE epg_id = e.epg_id
                            LIMIT 1) AS vinculacion,
                        (
                            SELECT
                                evi_id
                            FROM cat_MetaActividad
                            WHERE epg_id = e.epg_id
                            LIMIT 1) AS evi_id
                    FROM cat_ObraActividad
                        INNER JOIN enc_Programatico e USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                    WHERE oa_id != 0
                        AND ((eoa_id = 5 AND toa_id = 2) OR (eoa_id = 4 AND toa_id = 1))
                        AND gpo_id = :gpo
                        AND ur_id = :ur
                        AND ue_id = :ue
                        AND prg_id = 0
                    ) AS t
                    LEFT JOIN cat_EstadoVinculacion USING(evi_id)
                ' . $filtro . '
                ';
        }
        $this->conectar();
        $this->setSql($sql);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-EstructuraProgramatica';
        $this->setParams($params);
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros" ,"warning");');
        }

        $this->mixScript('
            $(".vinculacionMir").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"vinculacion",
                    evi_id:$(this).attr("evi_id"),
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id"),
                    py_id:$(this).attr("py_id"),
                    oa_id:$(this).attr("oa_id"),
                    epg_id:$(this).attr("epg_id"),
                    edoVin:"' . $this->prm->edoVin . '",
                });
                return false;
            });

            $(".revisarVinculacion").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"revisarVinculacion",
                    epg_id:$(this).attr("epg_id"),
                    evi_id:2,
                });
                return false;
            });
        ');
    }

    private function vinculacion()
    {
        $epg = $this->prm->epg_id;
        $gpo = $this->prm->gpo_id;
        $ur = $this->prm->ur_id;
        $prg = $this->prm->prgs_id;
        $spr = $this->prm->sprs_id;
        $py = $this->prm->py_id;
        $oa = $this->prm->oa_id;
        $urSefin = '';
        $lectura = ($this->prm->evi_id == 0 || $this->prm->evi_id == '' || $this->prm->evi_id == 1 || $this->prm->evi_id == 3) ? false : true;
        $metodo = isset($this->prm->nivelRevision) ? 'tablaVinculacionRead' : 'tablaEstructuraProgramatica';
        $nivRevision = isset($this->prm->nivelRevision) ? $this->prm->nivelRevision : '';

        if (isset($this->prm->unidadResponsable)) {
            $urSefin = 'unidadResponsable:' . $this->prm->unidadResponsable . ',';
        }

        //Boton Regresar
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'circle-left me-2';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"' . $metodo . '",
                edoVin:"' . $this->prm->edoVin . '",
                nivelRevision:"' . $nivRevision . '",
                ' . $urSefin . '
            });
        ';
        $btnRegresar = $this->ponBoton($btnRegresar);

        //Boton historial rechazo
        $btnHistorialRechazo = new stdClass();
        $btnHistorialRechazo->id = 'verHistorial';
        $btnHistorialRechazo->icon = 'list-alt me-2';
        $btnHistorialRechazo->class = 'btn btn-outline-secondary me-1';
        $btnHistorialRechazo->text = 'Historial de Rechazo';
        $btnHistorialRechazo->value = 'Historial de Rechazo';
        $btnHistorialRechazo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"historialRechazo",
                epg_id:"' . $this->prm->epg_id . '"
            });
            return false;
        ';
        $btnHistorial = $this->ponBoton($btnHistorialRechazo);
        $da = new AccesoDatos();
        $da->conectar();

        $anyValue = '';
        if ($_SERVER['SERVER_NAME'] == 'sefip.finanzasoaxaca.gob.mx') {
            $anyValue = '';
        } elseif ($_SERVER['SERVER_NAME'] == 'uat-sefip.finanzasoaxaca.gob.mx') {
            $anyValue = '';
        } else {
            $anyValue = 'ANY_VALUE';
        }

        //Obtenemos los indicadores de la meta o actividad
        $params = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
            $this->prm->prgs_id,
            $this->prm->sprs_id,
            $this->prm->py_id,
            $this->prm->oa_id,
        ];
        $da->setSql('
            SELECT
                pii_id,pdi_id,ind_id,
                ind_definicion,
                concat(ind_id," - ",ind_nombre) as nombre_ind,
                ctInd.umv_id,
                ctv.tiv_nombre,
                cumi.umi_nombre,
                ctInd.umv_nombre,cs.pdf_id,gpo_id,
                ur_id,prg_id,spr_id,
                py_id,oa_id,pii_id AS pii,
                epg_id AS epg,
                #MAX(mac_descripcion) AS mac_descripcion,
                epg_id,
                MAX(mac_original) AS mac_original, MAX(mac_modificado) AS mac_modificado,
                (SELECT
                        mact_original
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id=1) AS trim1,
                (SELECT
                        mact_modificado
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg AND tri_id = 1) AS mod_trim1,
                (SELECT
                        mact_original
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 2) AS trim2,
                (SELECT
                        mact_modificado
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 2) AS mod_trim2,
                (SELECT
                        mact_original
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 3) AS trim3,
                (SELECT
                        mact_modificado
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 3) AS mod_trim3,
                (SELECT
                        mact_original
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 4) AS trim4,
                (SELECT
                        mact_modificado
                    FROM cat_MetaActividadTrimestre AS ct
                    WHERE ct.pii_id = pii
                        AND ct.epg_id = epg
                        AND tri_id = 4) AS mod_trim4
            FROM enc_Programatico enc
                INNER JOIN cat_MetaActividad ma USING (epg_id)
                INNER JOIN cat_Subprograma cs USING (gpo_id,ur_id,prg_id,spr_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
                INNER JOIN cat_Indicador i USING (ind_id)
                INNER JOIN cat_TipoValor ctv USING (tiv_id)
                INNER JOIN cat_UnidadMedidaIndicador cumi USING (umi_id)
                INNER JOIN cat_UnidadMedidaVariable ctInd USING (umv_id)
            WHERE enc.gpo_id = ?
                AND enc.ur_id = ?
                AND prg_id = ?
                AND spr_id = ?
                AND py_id = ?
                AND oa_id = ?
                AND i.nim_id = 4
            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
            ORDER BY ' . $anyValue . '(ma.usr_fecha);
        ');

        $da->consultar($params);
        $da->desconectar();
        $fecha1Enero = strtotime($this->en1Enero);
        $fechaActual = strtotime(date('d-m-Y H:i:00', time()));
        $filasIndicadores = '';
        unset($boton);
        while ($r = $da->extraer()) {
            $boton = new stdClass();
            $boton->id = 'cve_' . $r['pii_id'];
            $boton->color = 'rojo';
            $boton->value = 'eliminar';
            $boton->class = 'btn-xs btn-danger';
            $boton->click = '
                alertify.confirm(
                    "<h5> ELIMINAR META </h5>",
                    "¿Confirma que desea eliminar la meta?",
                    function(){
                        comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                            metodo:"eliminaMeta",
                            pii_id:' . $r['pii_id'] . ',
                            ind_id:' . $r['ind_id'] . ',
                            gpo_id:"' . $gpo . '",
                            ur_id:"' . $ur . '",
                            prgs_id:"' . $prg . '",
                            sprs_id:"' . $spr . '",
                            epg_id:"' . $epg . '"
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            ';
            $boton = ($lectura) ? '' : $this->ponBoton($boton);
            $edicion = ($lectura) ? '1' : '0';
            $boton2 = new stdClass();
            $boton2->value = 'ver';
            $boton2->id = 'edit_' . $r['ind_id'];
            $boton2->class = 'btn-xs btn-success';
            $boton2->click = '
                    comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                        metodo:"muestraMetas",
                        lectura:' . $edicion . ',
                        ind_id:' . $r['ind_id'] . ',
                        pdi_id:' . $r['pdi_id'] . ',
                        umv_id:' . $r['umv_id'] . ',
                        tiv_nombre:"' . $r['tiv_nombre'] . '",
                        pii_id:' . $r['pii_id'] . ',
                        epg_id:"' . $epg . '",
                        gpo_id:"' . $gpo . '",
                        ur_id:"' . $ur . '",
                        prgs_id:"' . $prg . '",
                        sprs_id:"' . $spr . '",
                        py_id:"' . $this->prm->py_id . '",
                        oa_id:"' . $this->prm->oa_id . '",
                        edoVin:"' . $this->prm->edoVin . '",
                        ';
            if ($fechaActual <= $fecha1Enero) {
                $boton2->click .= '
                        trim_1:' . $r['trim1'] . ',
                        trim_2:' . $r['trim2'] . ',
                        trim_3:' . $r['trim3'] . ',
                        trim_4:' . $r['trim4'] . '
                        });';
            } else {
                $boton2->click .= '
                        trim_1:' . $r['mod_trim1'] . ',
                        trim_2:' . $r['mod_trim2'] . ',
                        trim_3:' . $r['mod_trim3'] . ',
                        trim_4:' . $r['mod_trim4'] . '
                    });';
            }
            $filasIndicadores .= '
            <tr id="indicador' . $r['ind_id'] . '">
            <td>' . $this->ponBoton($boton2) . '</td>';
            if ($this->usrNvj <= 2) {
                $filasIndicadores .= '<td>' . $boton . '</td>';
            }

            $filasIndicadores .= '<td>' . $r['nombre_ind']
                . '</td><center><td>' . $r['umi_nombre']
                . '</center></td><td><center>' . $r['tiv_nombre']
                . '</center></td><td>' . $r['umv_nombre'] . '</td>';
            if ($fechaActual <= $fecha1Enero) {
                $filasIndicadores .= '<td><center>' . number_format(floatval($r['mac_original']), 2) . '</center></td>';
            } else {
                $filasIndicadores .= '<td><center>' . number_format(floatval($r['mac_modificado']), 2) . '</center></td>';
            }
            $filasIndicadores .= '
            <input name="ind' . $r['ind_id'] . '[]" value="' . $r['ind_id'] . '" type="hidden"></input>
            <input name="ind' . $r['ind_id'] . '[]" value="' . $r['umv_nombre'] . '" type="hidden"></input>
            <input name="ind' . $r['ind_id'] . '[]" value="' . $r['tiv_nombre'] . '" type="hidden"></input>
            <input name="ind' . $r['ind_id'] . '[]" value="' . $r['nombre_ind'] . '" type="hidden"></input>';
            if ($fechaActual <= $fecha1Enero) {
                $filasIndicadores .= '<input name="ind' . $r['ind_id'] . '[]" value="' . $r['trim1'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['trim2'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['trim3'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['trim4'] . '" type="hidden"></input>';
            } else {
                $filasIndicadores .= '<input name="ind' . $r['ind_id'] . '[]" value="' . $r['mod_trim1'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['mod_trim2'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['mod_trim3'] . '" type="hidden"></input>
                <input name="ind' . $r['ind_id'] . '[]" value="' . $r['mod_trim4'] . '" type="hidden"></input>';
            }
            $filasIndicadores .= '<input name="ind' . $r['ind_id'] . '[]" value="' . $r['pdi_id'] . '" type="hidden"></input>
            </tr>';
        }

        //Verifica si el trimestre está aperturado
        $da->conectar();
        $params = [$this->prm->gpo_id, $this->prm->ur_id];
        $da->setSql('
            SELECT
                nim_id
            FROM mir_TrimestreUr
            WHERE gpo_id = ?
                AND ur_id= ?
                AND nim_id = 4
                AND etr_id = "A";
        ');

        $da->consultar($params);
        $numR = $da->getNumeroRegistros();
        $da->desconectar();

        $botonMetas = '';
        //Verifica el estado de la vinculación
        if ($numR > 0 && !$lectura && ($this->ur || $this->ue)) {
            $botonMetas = new stdClass();
            $botonMetas->id = 'botonMetas';
            $botonMetas->color = 'azul';
            $botonMetas->icon = 'plus';
            $botonMetas->title = 'Agregar Indicador';
            $botonMetas->type = 'button';
            $botonMetas->class = 'me-2';
            $botonMetas->click = '
            if($("#pdi_id").val()!=""){
                comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                    metodo:"metas",
                    ' . (($epg * 1 != 0) ? 'epg_id:"' . $epg . '"' : 'epg_id:0') . ',
                    pdi_id:$("#pdi_id").find("option:selected").val(),
                    gpo_id:"' . $gpo . '",
                    ur_id:"' . $ur . '",
                    prgs_id:"' . $prg . '",
                    sprs_id:"' . $spr . '",
                    py_id:"' . $py . '",
                    oa_id:"' . $oa . '",
                    edoVin:"' . $this->prm->edoVin . '",
                    lectura:' . (($lectura) ? $lectura : 0) . '
                });
            }else{
                notify("Favor de seleccionar Actividad MIR","error");
            }
            ';
            $botonMetas = $this->ponBoton($botonMetas);
        } elseif ($numR == 0) {
            $botonMetas = '<div class="alert alert-info alert-dismissable mb-4"><h5><strong>TRIMESTRE CERRADO PARA CAPTURA DE METAS</strong></h5></div>';
        }

        $and = '';
        $params = ['prgs' => $this->prm->prgs_id, 'sprs' => $this->prm->sprs_id];
        if ($lectura && $this->getPdi_id($this->prm->epg_id) != '') {
            $params['pdi'] = $this->getPdi_id($this->prm->epg_id);
            $and = ' AND pdi_id = :pdi ';
        } elseif ($this->getPdi_id($this->prm->epg_id) != '') {
            $params['pdi'] = $this->getPdi_id($this->prm->epg_id);
            $and = ' AND ((vp.sts_id = 1 AND e.sts_id = 1 ) OR (pdi_id = :pdi)) ';
        } else {
            $and = ' AND vp.sts_id = 1 AND e.sts_id = 1 ';
        }
        $actividadMIR = new stdClass();
        $actividadMIR->id = 'pdi_id';
        $actividadMIR->label = 'Actividad MIR';
        $actividadMIR->class = 'mb-4';
        $actividadMIR->selecciona = $this->getPdi_id($this->prm->epg_id);
        $actividadMIR->sql = '
            SELECT
                pdi_id,pdi_mir
            FROM cat_Subprogramas
                INNER JOIN enc_UrSubprogramas es USING (prgs_id,sprs_id)
                ' . (isset($this->prm->prgs_id) && $this->prm->prgs_id == 0 ? 'LEFT' : 'INNER') . ' JOIN vin_UrProgramaMir USING(prgs_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio vp USING (pdf_id)
                INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                INNER JOIN vin_ProductoIntermedioIndicador e USING (pdf_id,pdi_id)
            WHERE prgs_id = :prgs
                AND es.sprs_id = :sprs
                ' . $and . '
            GROUP BY pdf_id,pdi_id;
        ';
        $actividadMIR->params = $params;
        $actMir = $this->ponCombo($actividadMIR);

        $nombrePrograma = $this->getObra($epg, 'prg_nombre');
        $nombreSubprograma = $this->getObra($epg, 'spr_nombre');

        $programa = new stdClass();
        $programa->disabled = true;
        $programa->id = 'nombrePrograma';
        $programa->label = 'Programa';
        $programa->maxlength = 600;
        $programa->required = true;
        $programa->placeholder = 'Escriba la descripcion';
        $programa->text = $nombrePrograma;
        $programa = $this->ponTextarea($programa);

        $subPrograma = new stdClass();
        $subPrograma->disabled = true;
        $subPrograma->id = 'nombreSubprograma';
        $subPrograma->label = 'Subprograma';
        $subPrograma->maxlength = 600;
        $subPrograma->required = true;
        $subPrograma->placeholder = 'Escriba la descripcion';
        $subPrograma->text = $nombreSubprograma;
        $subPrograma = $this->ponTextarea($subPrograma);

        $this->agregaTools($btnRegresar . ' ' . $btnHistorial);
        $this->mixHtml('
            <div class="row">
                <div class="col-md-7 col-xs-7">
                <legend align="left">Vinculación EP - MIR</legend>
                ' . $programa . '
                ' . $subPrograma . '
                ' . $actMir . '
                ' . $botonMetas
            . 'Indicadores

                        <table id ="tbl_indicadores" class="table table-striped"><thead><tr><th>Ver/Editar</th>'
            . ($this->usrNvj <= 2 ? '<th>Eliminar</th>' : '') .
            '<th>Indicador</th><th><center>Unidad de<br>Medida<br>Indicador</center></th><th>Tipo Valor</th><th><center>Unidad de<br>Medida<br>Variable</center></th>
                        <th><center>Meta Anual</center></th></thead><tbody id="filasIndicadores">' . $filasIndicadores . '</tbody></table>
                </div>
            </div>
        ');
        $this->mixScript('
            $("#btnRegresar").removeClass("btn btn-default");
            $("#btnRegresar").addClass("btn btn-outline-secondary me-1");
            $("#verHistorial").removeClass("btn btn-default");
            $("#verHistorial").addClass("btn btn-outline-secondary me-1");
        ');
    }

    public function getPdi_id($epg_id)
    {
        $params = [$epg_id];
        $numPdi = '';
        $this->setSql('
            SELECT
                pdi_id
            FROM enc_Programatico
                INNER JOIN cat_MetaActividad USING (epg_id)
                INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
                WHERE epg_id = ?;
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 0) {
            $pdi = $this->extraer();
            $numPdi = $pdi['pdi_id'];
        }
        return $numPdi;
    }

    public function getObra($epg_id, $campo)
    {
        $params = [$epg_id];
        $this->setSql('
            SELECT
                ' . $campo . '
            FROM enc_Programatico
                INNER JOIN cat_Programa USING (gpo_id,ur_id,prg_id)
                INNER JOIN cat_Subprograma USING (gpo_id,ur_id,prg_id,spr_id)
            WHERE epg_id = ?;
        ');

        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $registro = $this->extraer();
        return $registro[$campo];
    }

    private function metas()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $epg_id = (empty($this->prm->epg_id)) ? 0 : $this->prm->epg_id;
        $gpo = $this->prm->gpo_id;
        $ur = $this->prm->ur_id;
        $prg = $this->prm->prgs_id;
        $spr = $this->prm->sprs_id;
        $py = $this->prm->py_id;
        $oa = $this->prm->oa_id;
        $change = '';
        $edoVin = $this->prm->edoVin;
        //consultamos el id de la vinculacion
        $this->conectar();
        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->prgs_id];
        $this->setSql('
            SELECT
                vpm_id
            FROM vin_UrProgramaMir
            WHERE gpo_id = ?
                AND ur_id = ?
                AND prgs_id = ?;
        ');
        $this->consultar($params);
        $this->desconectar();
        $vpm = $this->extraer();

        //si recibimos el epg_id entonces consultamos los datos de la meta
        $modificarMeta = true;

        $activado = 'warning';
        //va a ser lectura cuando el nivel sea mayor a 2 o si el estado de la vinculación es validada o enviada a validacion
        $lectura = (isset($this->prm->lectura) ? $this->prm->lectura : 0);

        $pdi = $this->prm->pdi_id;

        $params = [$this->prm->prgs_id, $this->prm->sprs_id, $pdi];

        $actividadMIR = new stdClass();
        $actividadMIR->id = 'pdi_id';
        $actividadMIR->label = 'Actividad MIR';
        $actividadMIR->required = true;
        $actividadMIR->sql = '
            SELECT
                pdi_id,pdi_mir,pdf_id
            FROM cat_Subprogramas
                INNER JOIN enc_UrSubprogramas es USING (prgs_id,sprs_id)
                ' . ($this->prm->prgs_id && $this->prm->prgs_id == 0 ? 'LEFT' : 'INNER') . ' JOIN vin_UrProgramaMir USING(prgs_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio USING (pdf_id)
                INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                INNER JOIN vin_ProductoIntermedioIndicador e USING (pdf_id,pdi_id)
            WHERE prgs_id = ?
                AND es.sprs_id = ?
                AND pdi_id = ?
            GROUP BY pdf_id,pdi_id;
        ';
        $actividadMIR->params = $params;
        $actividadMIR = $this->ponCombo($actividadMIR);

        $indicador = new stdClass();
        $indicador->id = 'ind_id';
        $indicador->label = 'Indicador';
        $indicador->required = true;

        $indicador->change = '
            $("#umv_id").val($(this).find("option:selected").attr("umv_nombre"));
            $("#umi_id").val($(this).find("option:selected").attr("umi_nombre"));
            $("#tiv_id").val($(this).find("option:selected").attr("tiv_nombre"));
            $("#descripcion").val($(this).find("option:selected").attr("ind_definicion"));
            $("#nombre_ind").val($(this).find("option:selected").attr("nombre_ind"));
            $("#ori_trimestre1").val("0.00");
            $("#ori_trimestre2").val("0.00");
            $("#ori_trimestre3").val("0.00");
            $("#ori_trimestre4").val("0.00");
            $("#original").val("0.00");
            $("#originalT").val("0.00");
        ';

        $indicador->sql = '
            SELECT
                ind_id,
                ind_nombre,
                concat(ind_id," - ",ind_nombre) as nombre_ind,
                ind_definicion,
                umv_id,umv_nombre,
                tiv_id,
                tiv_nombre,
                umi_id,
                umi_nombre,
                pdf_id,pdi_id,pii_id
            FROM cat_Indicador ind
                INNER JOIN vin_ProductoIntermedioIndicador e USING (nim_id,ind_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio vp USING (pdf_id,pdi_id)
                INNER JOIN cat_Subprogramas USING (pdf_id)
                INNER JOIN cat_TipoValor ctv USING (tiv_id)
                INNER JOIN cat_UnidadMedidaIndicador cumi USING (umi_id)
                INNER JOIN cat_UnidadMedidaVariable USING (umv_id)
            WHERE prgs_id = ?
                AND sprs_id = ?
                AND pdi_id = ?
                AND nim_id = 4
                AND vp.sts_id = 1
                AND e.sts_id = 1
            GROUP BY pdi_id,pii_id,ind_id;
        ';
        $indicador->comoAtr = 'ind_definicion,umv_nombre,umv_id,umi_nombre,umi_id,tiv_id,tiv_nombre,pdf_id,pdi_id,pii_id,nombre_ind';
        $indicador->params = $params;
        if (!is_null($this->comboValorUnico)) {
            $indicador->detonar = true;
        }
        if (!empty($this->prm->pdi_id)) {
            $indicador = $this->ponCombo($indicador);
        } else {
            $indicador = $this->templateCombo($indicador);
        }

        if (!is_null($this->comboValorUnico)) {
            $change = '$("#ind_id").change();';
        }

        $unidadMedida = new stdClass();
        $unidadMedida->id = 'umi_id';
        $unidadMedida->required = true;
        $unidadMedida->label = 'Unidad de Medida Indicador';
        $unidadMedida->readonly = true;
        $unidadMedida = $this->ponInput($unidadMedida);

        $unidadMedidaVariable = new stdClass();
        $unidadMedidaVariable->id = 'umv_id';
        $unidadMedidaVariable->required = true;
        $unidadMedidaVariable->label = 'Unidad de Medida Variable';
        $unidadMedidaVariable->readonly = true;
        $unidadMedidaVariable = $this->ponInput($unidadMedidaVariable);

        $tipoValor = new stdClass();
        $tipoValor->id = 'tiv_id';
        $tipoValor->required = true;
        $tipoValor->label = 'Tipo Valor';
        $tipoValor->readonly = true;
        $tipoValor = $this->ponInput($tipoValor);

        $form = new stdClass();
        $form->id = 'metas';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMeta';
        $form->datosExtra = new stdClass();

        $this->prm->pdf_id = '"{$("#ind_id").find("option:selected").attr("pdf_id")}"';
        $form->datosExtra->epg_id = (!empty($this->prm->epg_id) ? $this->prm->epg_id : 0);
        $form->datosExtra->pdf_id = '"{$("#ind_id").find("option:selected").attr("pdf_id")}"';
        $form->datosExtra->pdi_id = '"{$("#ind_id").find("option:selected").attr("pdi_id")}"';
        $form->datosExtra->umv_id = '"{$("#ind_id").find("option:selected").attr("umv_id")}"';
        $form->datosExtra->umv_nombre = '"{$("#umv_id").val()}"';
        $form->datosExtra->umi_id = '"{$("#ind_id").find("option:selected").attr("umi_id")}"';
        $form->datosExtra->umi_nombre = '"{$("#umi_id").val()}"';
        $form->datosExtra->tiv_id = '"{$("#ind_id").find("option:selected").attr("tiv_id")}"';
        $form->datosExtra->tiv_nombre = '"{$("#tiv_id").val()}"';
        $form->datosExtra->nim_id = 4;
        $form->datosExtra->vpm_id = $vpm['vpm_id'];

        $form->datosExtra->metodo = 'abcMeta';
        $form->datosExtra->modificarMeta = $modificarMeta;

        $form = new form($form);

        //Al registrar un meta los campos de los trimestres tiene un valor igual a cero
        $original = [0, 0, 0, 0];

        $anual = new stdClass();
        $anual->id = 'original';
        $anual->required = true;
        $anual->label = 'Anual';
        $anual->readonly = true;
        $anual->formato = 'numero';
        $anual->value = number_format(0, 2);
        $anual = $this->ponInput($anual);

        $total = new stdClass();
        $total->id = 'originalT';
        $total->required = true;
        $total->readonly = true;
        $total->formato = 'numero';
        $total->value = number_format(0, 2);
        $total = $this->ponInput($total);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->label = 'Descripción';
        $descripcion->readonly = true;
        $descripcion = $this->ponInput($descripcion);

        $trimestres = '';
        $trimestreLabel = ['Primer', 'Segundo', 'Tercer', 'Cuarto'];
        $modalMetas = '<div id="metasActividad" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Complemento de Actividades</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">

                            <div class="row">
                                <div class="col-md-6 col-xs-6">
                                    ' . $actividadMIR . '
                                </div>
                                <div id ="Ind" class="col-md-6 col-xs-6">
                                    ' . $indicador . '
                                </div>
                            </div>
                            <div class="row">
                                <fieldset id ="Ume" class="col-md-8 col-xs-8">
                                    ' . $unidadMedidaVariable . '
                                </fieldset>
                                <fieldset class="col-md-4 col-xs-4">
                                    ' . $anual . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    ' . $descripcion . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-6 col-xs-6">
                                    ' . $unidadMedida . '
                                </fieldset>
                                <fieldset class="col-md-6 col-xs-6">
                                    ' . $tipoValor . '
                                </fieldset>
                            </div>
                            <br/>
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <h3 class="panel-title"><b>Calendario</b></h3>
                                </fieldset>
                            </div><br/>
                            <div class="row"><!--Calendario de Metas-->';
        for ($x = 0; $x <= 3; $x++) {
            if (!$lectura) {
                $trimestre = new stdClass();
                $trimestre->required = true;
                $trimestre->id = 'ori_trimestre' . ($x + 1);
                $trimestre->enDiv = true;
                $trimestre->value = number_format($original[$x], 2);
                $trimestre->formato = 'numero';
                $trimestre->class = 'trim';
                $trimestre = $this->ponInput($trimestre);
                $activado = 'warning';
            } else {
                $trimestre = new stdClass();
                $trimestre->required = true;
                $trimestre->id = 'ori_trimestre' . ($x + 1);
                $trimestre->enDiv = true;
                $trimestre->value = number_format($original[$x], 2);
                $trimestre->formato = 'numero';
                $trimestre->class = 'trim';
                $trimestre->readonly = true;
                $trimestre = $this->ponInput($trimestre);
                $activado = 'info';
            }
            $trimestres .= '
                                <div class="panel panel-' . $activado . ' col" >
                                    <div class="panel-heading"> <h3 class="panel-title">' . $trimestreLabel[$x] . ' Trimestre</h3> </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead> <tr><th></th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td class="trim">' . $trimestre . '</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ';
            if ($x == 3) {
                $trimestres .= '
                                <div class="panel panel-info col" >
                                    <div class="panel-heading"> <h3 class="panel-title">Total</h3> </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead> <tr><th></th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td class="trim">' . $total . '</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ';
            }
        }
        $modalMetas .= $trimestres . '
                    </div>
                    <div class="modal-footer">';
        if ($modificarMeta) {
            $modalMetas .= '  <button  class="btn btn-outline-success" id="save" >Aceptar</button>';
        }

        $modalMetas .= '<button type="button" class="btn btn-outline-danger" id="cancel" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modalMetas);

        $this->mixScript('
            var metasArray = new Array();
            ' . $change . '
            $("#save").click(function(){

                if ($("#pdi_id").val() == "" || $("#pdi_id").val() == "null") {
                    notify("Favor de seleccionar Indicador","error");
                    return false;
                }

                var prm = new Array();

                prm["ind_id"] = $("#ind_id").find("option:selected").val();//Indicador
                prm["pdi_id"] = $("#pdi_id").val();//Actividad Mir
                prm["umv_id"] = $("#umv_id").val();//Unidad de Medida
                prm["umi_id"] = $("#umi_id").val();//Unidad de Medida Indicador
                prm["tiv_id"] = $("#tiv_id").val();//Tipo valor
                prm["tiv_nombre"] = $("#tiv_id").val();//Unidad de Medida Indicador
                prm["descripcion"] = $("#descripcion").val();//Descripción
                prm["nombre_ind"] = $("#ind_id").find("option:selected").attr("nombre_ind");//NombreIndicador
                prm["original"] = $("#original").val();
                prm["originalT"] = $("#originalT").val();
                prm["t1"] = $("#ori_trimestre1").val();//trimestre 1
                prm["t2"] = $("#ori_trimestre2").val();//trimestre 2
                prm["t3"] = $("#ori_trimestre3").val();//trimestre 3
                prm["t4"] = $("#ori_trimestre4").val();//trimestre 4
                prm["pii_id"] = $("#ind_id").find("option:selected").attr("pii_id");//vinculacion
                prm["epg_id"] = "' . $epg_id . '";
                prm["gpo_id"] = "' . $gpo . '";
                prm["ur_id"] = "' . $ur . '";
                prm["prgs_id"] = "' . $prg . '";
                prm["sprs_id"] = "' . $spr . '";
                prm["py_id"] = "' . $py . '";
                prm["oa_id"] = "' . $oa . '";
                prm["edoVin"] = "' . $edoVin . '";

                var anual;
                anual = $("#original").val();
                if( prm["ind_id"]==""){
                    notify("No hay Indicador Seleccionado","warning");
                    return false;
                }

                if($("#indicador"+prm["ind_id"]).length == 1){
                    notify("El indicador Seleccionado ya ha sido agregado","warning");
                    return false;
                }
                if(anual == 0.00){
                    notify("El el valor anual debe ser mayor a cero","error");
                    return false;
                }

                if(($("#ori_trimestre1").val().substring(0,1) == "-") || ($("#ori_trimestre2").val().substring(0,1) == "-")
                    || ($("#ori_trimestre3").val().substring(0,1) == "-") || $("#ori_trimestre4").val().substring(0,1) == "-"){
                    notify("Los valores de los trimestres no pueden ser negativos","error");
                    return false;
                }
                
                if(' . $epg_id . ' > 0){
                    comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                        metodo:"validaMeta",
                        epg_id:"' . ($epg_id * 1) . '",
                        trim1:$("#ori_trimestre1").val(),
                        trim2:$("#ori_trimestre2").val(),
                        trim3:$("#ori_trimestre3").val(),
                        trim4:$("#ori_trimestre4").val(),
                        tiv_id:$("#tiv_id").val(),
                        umi_id:$("#umi_id").val(),
                        ind_id: $("#ind_id").find("option:selected").val(),
                        pii_id: $("#ind_id").find("option:selected").attr("pii_id"),
                    });
                }
                creaTr("tbl_indicadores",prm);
                return false;
            });

            $("#metasActividad").modal().show();
            $(".cerrar").click(function(){ $("#metasActividad").modal().remove(); });
            $("#cancel").click(function(){ $("#metasActividad").modal().remove(); });
            $("#metasActividad").on("hidden.bs.modal", function (e) {

                $("#metasActividad").modal().remove();

            });

            $(".trim").blur(
                function(){
                    total = 0;
                    trim = 0;
                    $(".trim").each(function(){
                        if($(this).val()=="")$(this).val(0);
                        total += unformatNumber( $(this).val() ) * 1 ;
                    });
                    var tipoValorIndicador = $("#tiv_id").val();
                    console.log(tipoValorIndicador);
                    if (tipoValorIndicador == "FLUJO") {
                        $("#original").val(total).blur();
                        $("#originalT").val(total).blur();
                    } else if(tipoValorIndicador == "ACUMULADO") {
                        $("#original").val(unformatNumber($("#ori_trimestre4").val())).blur();
                        $("#originalT").val(unformatNumber($("#ori_trimestre4").val())).blur();
                    }                                  
                }
            );

            if(metasArray.length>0) {
                var i=0;
            }

            if(metasArray.length>0 && metasArray[i].pdi_id==' . $this->prm->pdi_id . ' && ' . $lectura . '==0){
                for(var index in metasArray) {
                    if(index.length>0){
                        $("#pdi_id").val(metasArray[index].pdi_id).change();
                        $("#ind_id").val(metasArray[index].ind_id).change();
                        $("#umv_id").val(metasArray[index].umv_nombre);
                        $("#original").val(metasArray[index].original);
                        $("#descripcion").val(metasArray[index].descripcion);
                        $("#nombre_ind").val(metasArray[index].nombre_ind);
                        $("#ori_trimestre1").val(metasArray[index].ori_trimestre1);
                        $("#ori_trimestre2").val(metasArray[index].ori_trimestre2);
                        $("#ori_trimestre3").val(metasArray[index].ori_trimestre3);
                        $("#ori_trimestre4").val(metasArray[index].ori_trimestre4);
                    }
                }
            }


        ');
    }

    //Método que verifica la captura de la meta en avance de gestión
    public function capturaTrimestre($trimestre, $epg_id, $pii_id, $campo = 'mact_capturaAvance')
    {
        $params = [$epg_id,$pii_id,$trimestre];
        $this->conectar();
        $this->setSql('
            SELECT
                ' . $campo . '
            FROM cat_MetaActividadTrimestre
            WHERE epg_id = ?
                AND pii_id = ?
                AND tri_id = ?;
        ');
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return $datos[$campo];
        } else {
            return null;
        }

    }
    //Método que obtiene el estado del trimestre para avance de gestión
    public function estadoTrimestre($trimestre, $epg_id, $pii_id, $campo = 'e.etr_id1')
    {
        $this->conectar();
        $params = [$epg_id, $pii_id, $trimestre];
        $this->setSql('
            SELECT
                ' . $campo . '
            FROM cat_MetaActividadTrimestre
                INNER JOIN enc_Programatico using(epg_id)
                INNER JOIN mir_TrimestreUr e using(gpo_id, ur_id)
            WHERE epg_id= ?
                AND pii_id = ?
                AND e.tri_id = ?
                AND e.nim_id = 4
            GROUP BY pii_id,epg_id;
        ');
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            return $datos['etr_id1'];
        } else {
            return null;
        }

    }

    private function muestraMetas()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $change = '';
        $epg_id = $this->prm->epg_id;
        $pii_id = $this->prm->pii_id;

        $gpo = $this->prm->gpo_id;
        $ur = $this->prm->ur_id;
        $prg = $this->prm->prgs_id;
        $spr = $this->prm->sprs_id;
        $py = $this->prm->py_id;
        $oa = $this->prm->oa_id;

        $trimestreAbierto = false;
        //va a ser lectura cuando el nivel sea mayor a 2 o si el estado de la vinculación es validada o enviada a validacion
        $lectura = (isset($this->prm->lectura) ? $this->prm->lectura : 0);
        $this->conectar();
        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->prgs_id];
        $this->setSql('
            SELECT
                vpm_id
            FROM vin_UrProgramaMir
            WHERE gpo_id = ?
                AND ur_id = ?
                AND prgs_id = ?;
        ');
        $this->consultar($params);
        $this->desconectar();
        $vpm = $this->extraer();

        //si recibimos el epg_id entonces consultamos los datos de la meta
        $modificarMeta = true;
        if ($epg_id != 0) {
            $this->conectar();
            $this->setSql('
                SELECT
                    ' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacion","' . BD_CTRL . '") AS existe;
            ');
            $this->consultar();
            $this->desconectar();
            $tabla = $this->extraer();
            if ($tabla['existe'] == 1) {
                $this->conectar();
                $params = [$epg_id];
                $this->setSql('
                    SELECT
                        epg_id
                    FROM scp_DetalleModificacion
                    WHERE epg_id = ?
                    LIMIT 1;');
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $modificarMeta = false;
                }

            }
            $this->conectar();
            $this->setSql('
                SELECT
                    ' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacionMeta","' . BD_CTRL . '") AS existe;
            ');
            $this->consultar();
            $this->desconectar();
            $tabla = $this->extraer();
            if ($tabla['existe'] == 1) {
                $this->conectar();
                $params = [$epg_id];
                $this->setSql('
                    SELECT
                        epg_id FROM scp_DetalleModificacionMeta
                    WHERE epg_id = ?
                    LIMIT 1;');
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $modificarMeta = false;
                }

            }

            $this->conectar();
            $params = [$epg_id, $pii_id];
            $this->setSql('
                SELECT
                    mac_triEvaluacionInicial
                FROM cat_MetaActividad
                WHERE epg_id = ?
                    AND pii_id = ?;
            ');
            $this->consultar($params);
            $this->desconectar();
            $mes = $this->extraer();
            $numR = $this->getNumeroRegistros();
            if ($numR > 0) {
                $this->conectar();
                $params = [$this->prm->gpo_id, $this->prm->ur_id, $mes['mac_triEvaluacionInicial']];
                //Verifica si esta abierto el trimestre en que fue registrado
                $this->setSql('
                    SELECT
                        nim_id
                    FROM mir_TrimestreUr
                    WHERE gpo_id = ?
                        AND ur_id = ?
                        AND nim_id = 4
                        AND tri_id = ?
                        AND etr_id="A";
                ');
                $this->consultar($params);
                $this->desconectar();
                $numR = $this->getNumeroRegistros();
                if ($numR > 0) {
                    $trimestreAbierto = true;
                }
                unset($numR);
            }

            if (!$trimestreAbierto || !$modificarMeta) {
                $lectura = true;
            }

        }
        $activado = 'warning';

        $pdi = $this->prm->pdi_id;

        $actividadMIR = new stdClass();
        $actividadMIR->id = 'pdi_idM';
        $actividadMIR->label = 'Actividad MIR';
        $actividadMIR->required = true;
        $actividadMIR->disabled = true;
        $actividadMIR->sql = '
            SELECT
                pdi_id AS pdi_idM,
                pdi_mir,
                pdf_id
            FROM cat_Subprogramas
                INNER JOIN enc_UrSubprogramas es USING(prgs_id,sprs_id)
                ' . (isset($this->prm->prgs_id) && $this->prm->prgs_id == 0 ? 'LEFT' : 'INNER') . ' JOIN vin_UrProgramaMir USING(prgs_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio USING (pdf_id)
                INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                INNER JOIN vin_ProductoIntermedioIndicador e USING (pdf_id,pdi_id)
            WHERE prgs_id = ?
                AND es.sprs_id = ?
                AND pdi_id = ?
            GROUP BY pdf_id,pdi_id;
        ';
        $actividadMIR->params = [$this->prm->prgs_id, $this->prm->sprs_id, $pdi];
        $actividadMIR = $this->ponCombo($actividadMIR);

        $indicador = new stdClass();
        $indicador->id = 'ind_id';
        $indicador->label = 'Indicador';
        $indicador->required = true;
        $indicador->disabled = true;
        $indicador->selecciona = $this->prm->ind_id;
        $indicador->change = '
            $("#umv_id").val($(this).find("option:selected").attr("umv_nombre"));
            $("#umi_id").val($(this).find("option:selected").attr("umi_nombre"));
            $("#tiv_id").val($(this).find("option:selected").attr("tiv_nombre"));
            $("#descripcion").val($(this).find("option:selected").attr("ind_definicion"));
        ';

        $indicador->sql = '
            SELECT
                ind_id,
                ind_nombre,
                ind_definicion,
                umv_id,
                umv_nombre,
                tiv_id,
                tiv_nombre,
                umi_id,
                umi_nombre,
                pdf_id,
                pdi_id
            FROM cat_Indicador ind
                INNER JOIN vin_ProductoIntermedioIndicador e USING (nim_id,ind_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio vp USING (pdf_id,pdi_id)
                INNER JOIN cat_Subprogramas USING (pdf_id)
                INNER JOIN cat_TipoValor ctv USING (tiv_id)
                INNER JOIN cat_UnidadMedidaIndicador cumi USING (umi_id)
                INNER JOIN cat_UnidadMedidaVariable USING (umv_id)
            WHERE prgs_id = ?
                AND sprs_id = ?
                AND pdi_id = ?
                AND nim_id = 4
                AND vp.sts_id = 1
                AND e.sts_id = 1
            GROUP BY pdi_id,ind_id;
        ';
        $indicador->params = [$this->prm->prgs_id, $this->prm->sprs_id, $pdi];
        $indicador->comoAtr = 'ind_definicion,umv_nombre,umv_id,umi_nombre,umi_id,tiv_id,tiv_nombre,pdf_id,pdi_id';
        if (!is_null($this->comboValorUnico)) {
            $indicador->detonar = true;
        }

        if (!empty($this->prm->pdi_id)) {
            $indicador = $this->ponCombo($indicador);
        } else {
            $indicador = $this->templateCombo($indicador);
        }

        if (!is_null($this->comboValorUnico)) {
            $change = '$("#ind_id").change();';
        }

        $unidadMedida = new stdClass();
        $unidadMedida->id = 'umi_id';
        $unidadMedida->required = true;
        $unidadMedida->label = 'Unidad de Medida Indicador';
        $unidadMedida->readonly = true;
        $unidadMedida = $this->ponInput($unidadMedida);

        $unidadMedidaVariable = new stdClass();
        $unidadMedidaVariable->id = 'umv_id';
        $unidadMedidaVariable->required = true;
        $unidadMedidaVariable->label = 'Unidad de Medida Variable';
        $unidadMedidaVariable->readonly = true;
        $unidadMedidaVariable = $this->ponInput($unidadMedidaVariable);

        $tipoValor = new stdClass();
        $tipoValor->id = 'tiv_id';
        $tipoValor->required = true;
        $tipoValor->label = 'Tipo Valor';
        $tipoValor->readonly = true;
        $tipoValor = $this->ponInput($tipoValor);

        $form = new stdClass();
        $form->id = 'metas';
        $form->controlador = $this->pantalla;
        $form->metodo = 'abcMeta';
        $form->datosExtra = new stdClass();

        $this->prm->pdf_id = '"{$("#ind_id").find("option:selected").attr("pdf_id")}"';
        $form->datosExtra->epg_id = (!empty($this->prm->epg_id) ? $this->prm->epg_id : 0);
        $form->datosExtra->pdf_id = '"{$("#ind_id").find("option:selected").attr("pdf_id")}"';
        $form->datosExtra->pdi_idM = '"{$("#pdi_idM").find("option:selected").attr("pdi_idM")}"';
        $form->datosExtra->umv_id = '"{$("#ind_id").find("option:selected").attr("umv_id")}"';
        $form->datosExtra->umv_nombre = '"{$("#umv_id").val()}"';
        $form->datosExtra->umi_id = '"{$("#ind_id").find("option:selected").attr("umi_id")}"';
        $form->datosExtra->umi_nombre = '"{$("#umi_id").val()}"';
        $form->datosExtra->tiv_id = '"{$("#ind_id").find("option:selected").attr("tiv_id")}"';
        $form->datosExtra->tiv_nombre = '"{$("#tiv_id").val()}"';
        $form->datosExtra->nim_id = 4;
        $form->datosExtra->vpm_id = $vpm['vpm_id'];

        $form->datosExtra->metodoCtrlAct = 'abcMeta';
        $form->datosExtra->modificarMeta = $modificarMeta;

        $form = new form($form);

        $t1 = str_replace(',', '', $this->prm->trim_1);
        $t2 = str_replace(',', '', $this->prm->trim_2);
        $t3 = str_replace(',', '', $this->prm->trim_3);
        $t4 = str_replace(',', '', $this->prm->trim_4);

        $original = [$t1, $t2, $t3, $t4];
        $valorAnual = 0;
        if ($this->prm->tiv_nombre == 'FLUJO') {
            $valorAnual = $t1 + $t2 + $t3 + $t4;
        } elseif ($this->prm->tiv_nombre == 'ACUMULADO') {
            if ($t4 > 0) {
                $valorAnual = floatval($t4);
            }
        }

        $estadoTrimestre = [
            $this->estadoTrimestre(1, $epg_id, $pii_id, 'e.etr_id1'),
            $this->estadoTrimestre(2, $epg_id, $pii_id, 'e.etr_id1'),
            $this->estadoTrimestre(3, $epg_id, $pii_id, 'e.etr_id1'),
            $this->estadoTrimestre(4, $epg_id, $pii_id, 'e.etr_id1'),
        ];
        $capturadoTrimestre = [
            $this->capturaTrimestre(1, $epg_id, $pii_id, 'mact_capturaAvance'),
            $this->capturaTrimestre(2, $epg_id, $pii_id, 'mact_capturaAvance'),
            $this->capturaTrimestre(3, $epg_id, $pii_id, 'mact_capturaAvance'),
            $this->capturaTrimestre(4, $epg_id, $pii_id, 'mact_capturaAvance'),
        ];

        $anual = new stdClass();
        $anual->id = 'original';
        $anual->required = true;
        $anual->label = 'Anual';
        $anual->readonly = true;
        $anual->formato = 'numero';
        $anual->value = number_format($valorAnual, 2);
        $anual = $this->ponInput($anual);

        $total = new stdClass();
        $total->id = 'originalT';
        $total->required = true;
        $total->readonly = true;
        $total->formato = 'numero';
        $total->value = number_format($valorAnual, 2);
        $total = $this->ponInput($total);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->label = 'Descripción';
        $descripcion->readonly = true;
        $descripcion = $this->ponInput($descripcion);

        $trimestreLabel = ['Primer', 'Segundo', 'Tercer', 'Cuarto'];
        $modalMetas = '<div id="metasActividad" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Complemento de Actividades</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">

                            <div class="row">
                                <div class="col-md-6 col-xs-6">
                                    ' . $actividadMIR . '
                                </div>
                                <div id ="Ind" class="col-md-6 col-xs-6">
                                    ' . $indicador . '
                                </div>
                            </div>
                            <div class="row">
                                <fieldset id ="Ume" class="col-md-8 col-xs-8">
                                    ' . $unidadMedidaVariable . '
                                </fieldset>
                                <fieldset class="col-md-4 col-xs-4">
                                    ' . $anual . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    ' . $descripcion . '
                                </fieldset>
                            </div>
                            <div class="row">
                                <fieldset class="col-md-6 col-xs-6">
                                    ' . $unidadMedida . '
                                </fieldset>
                                <fieldset class="col-md-6 col-xs-6">
                                    ' . $tipoValor . '
                                </fieldset>
                            </div>
                            <br/>
                            <div class="row">
                                <fieldset class="col-md-12 col-xs-12">
                                    <h3 class="panel-title"><b>Calendario</b></h3>
                                </fieldset>
                            </div><br/>
                            <div class="row"><!--Calendario de Metas-->';
        $trimestres = '';
        for ($x = 0; $x <= 3; $x++) {
            if (!$lectura && (($estadoTrimestre[$x] == 'A' && $capturadoTrimestre[$x] == 0) || ($capturadoTrimestre[$x] == 0 && $estadoTrimestre[$x] == 'C'))) {
                $trimestre = new stdClass();
                $trimestre->required = true;
                $trimestre->id = 'ori_trimestre' . ($x + 1);
                $trimestre->enDiv = true;
                $trimestre->value = number_format(floatval($original[$x]), 2);
                $trimestre->formato = 'numero';
                $trimestre->class = 'trim';
                $trimestre = $this->ponInput($trimestre);
                $activado = 'warning';
            } else {
                $trimestre = new stdClass();
                $trimestre->required = true;
                $trimestre->id = 'ori_trimestre' . ($x + 1);
                $trimestre->enDiv = true;
                $trimestre->value = number_format(floatval($original[$x]), 2);
                $trimestre->formato = 'numero';
                $trimestre->class = 'trim';
                $trimestre->readonly = true;
                $trimestre = $this->ponInput($trimestre);
                $activado = 'info';
            }
            $trimestres .= '
                                <div class="panel panel-' . $activado . ' col" >
                                    <div class="panel-heading"> <h3 class="panel-title">' . $trimestreLabel[$x] . ' Trimestre</h3> </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead> <tr><th></th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td class="trim">' . $trimestre . '</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ';
            if ($x == 3) {
                $trimestres .= '
                                <div class="panel panel-info col" >
                                    <div class="panel-heading"> <h3 class="panel-title">Total</h3> </div>
                                    <div class="panel-body">
                                        <table class="table table-condensed">
                                            <thead> <tr><th></th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td class="trim">' . $total . '</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            ';
            }
        }
        $modalMetas .= $trimestres . '

                    </div>
                    <div class="modal-footer">';
        if (!$lectura && $modificarMeta) {
            $modalMetas .= '  <button  class="btn btn-outline-success" id="registraMeta" >Aceptar</button>';
            $modalMetas .= '<button type="button" class="btn btn-outline-danger" id="cancel" data-dismiss="modal">Cancelar</button>';
        }
        $modalMetas .= '          </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modalMetas);

        $this->mixScript('
            ' . $change . '
            var metasArray = new Array();
            $("#registraMeta").click(function(){

                var prm = new Array();
                var metasArray = new Array();

                prm["ind_id"] = $("#ind_id").find("option:selected").val();//Indicador
                prm["pdi_id"] = $("#pdi_idM").val();//Actividad Mir
                prm["umv_id"] = $("#umv_id").val();//Unidad de Medida
                prm["descripcion"] = $("#descripcion").val();//Descripción
                prm["original"] = $("#original").val();
                prm["t1"] = $("#ori_trimestre1").val();//trimestre 1
                prm["t2"] = $("#ori_trimestre2").val();//trimestre 2
                prm["t3"] = $("#ori_trimestre3").val();//trimestre 3
                prm["t4"] = $("#ori_trimestre4").val();//trimestre 4
                prm["pii_id"] = "' . $pii_id . '";
                prm["epg_id"] = "' . $epg_id . '";
                prm["edoVin"] = "' . $this->prm->edoVin . '";

                var anual;
                anual = $("#original").val();
                if( prm["ind_id"]==""){
                    notify("No hay Indicador Seleccionado","warning");
                    return false;
                }

                if(anual == 0.00){
                    notify("El el valor anual debe ser mayor a cero","error");
                    return false;
                } else if(($("#ori_trimestre1").val().substring(0,1) == "-") || ($("#ori_trimestre2").val().substring(0,1) == "-")
                    || ($("#ori_trimestre3").val().substring(0,1) == "-") || $("#ori_trimestre4").val().substring(0,1) == "-"){
                    notify("Los valores de los trimestres no pueden ser negativos","error");
                    return false;
                } else if($("#indicador"+prm["ind_id"]).length == 1){
                    $("#indicador"+prm["ind_id"]).remove();
                }

                if(' . $epg_id . ' > 0){
                    comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                        metodo:"editaMeta",
                        epg_id:' . ($epg_id * 1) . ',
                        trim1:$("#ori_trimestre1").val(),
                        trim2:$("#ori_trimestre2").val(),
                        trim3:$("#ori_trimestre3").val(),
                        trim4:$("#ori_trimestre4").val(),
                        pii_id:' . $pii_id . ',
                        gpo_id:"' . $gpo . '",
                        ur_id:"' . $ur . '",
                        prgs_id:"' . $prg . '",
                        sprs_id:"' . $spr . '",
                        py_id:"' . $py . '",
                        oa_id:"' . $oa . '",
                        edoVin:"' . $this->prm->edoVin . '"
                    });
                }
                creaTr("tbl_indicadores",prm);
                $("#metasActividad1").modal().remove();
                return false;
            });

            $("#metasActividad").modal().show();
            $(".cerrar").click(function(){ $("#metasActividad").modal().remove(); });
            $("#cancel").click(function(){ $("#metasActividad").modal().remove(); });
            $("#metasActividad").on("hidden.bs.modal", function (e) {
                $("#metasActividad").modal().remove();
            });

            $(".trim").blur(
                function(){
                    total = 0;
                    trim = 0;
                    $(".trim").each(function(){
                        if($(this).val()=="")$(this).val(0);
                      total += unformatNumber( $(this).val() ) * 1 ;
                    });
                    console.log(total);
                    console.log(trim);
                    var tipoValorIndicador = $("#tiv_id").val();
                    console.log(tipoValorIndicador);
                    if (tipoValorIndicador == "FLUJO") {
                        $("#original").val(total).blur();
                        $("#originalT").val(total).blur();
                    } else if(tipoValorIndicador == "ACUMULADO") {
                            $("#original").val(unformatNumber($("#ori_trimestre4").val())).blur();
                            $("#originalT").val(unformatNumber($("#ori_trimestre4").val())).blur();
                    }
                }
            );

            //parece ser que metasArray es codigo obsoleto 03-06-2019 -- Inicio

            if(metasArray.length>0) {
                var i=0;
            }

            if(metasArray.length>0 && metasArray[i].pdi_id==' . $this->prm->pdi_id . ' && ' . $lectura . '==0){
                for(var index in metasArray) {
                    if(index.length>0){
                        $("#pdi_id").val(metasArray[index].pdi_id).change();
                        $("#ind_id").val(metasArray[index].ind_id).change();
                        $("#umv_id").val(metasArray[index].umv_nombre);
                        $("#tiv_id").val(metasArray[index].tiv_nombre);
                        $("#umi_id").val(metasArray[index].umi_nombre);
                        $("#original").val(metasArray[index].original);
                        $("#descripcion").val(metasArray[index].descripcion);
                        $("#ori_trimestre1").val(metasArray[index].ori_trimestre1);
                        $("#ori_trimestre2").val(metasArray[index].ori_trimestre2);
                        $("#ori_trimestre3").val(metasArray[index].ori_trimestre3);
                        $("#ori_trimestre4").val(metasArray[index].ori_trimestre4);
                    }
                }
            }

            //------------------------------------------------------

        ');
    }

    private function validaMeta()
    {
        $gpo = $this->clasAdmGpo;
        $ur = $this->clasAdmUr;
        $indicador = $this->prm->ind_id;
        $params = [$gpo,$ur,$indicador];
        $encuadres = '';
        $this->setSql('
            SELECT concat(prg_id,"-",spr_id,"-",py_id,"-",oa_id) as encuadre
            FROM enc_Programatico
            INNER JOIN cat_MetaActividad using (epg_id)
            INNER JOIN vin_ProductoIntermedioIndicador using (pii_id)
            WHERE gpo_id = ? and ur_id = ? and ind_id = ?
            ORDER BY oa_id;
        ');
        $this->conectar();
        $this->consultar($params);
        $numR = $this->getNumeroRegistros();
        if ($numR > 0) {
            while ($r = $this->extraer()) {
                $encuadres .= $r['encuadre'] . ', ';
            }
            $encuadres = substr($encuadres, 0, -2);
            $mensaje = 'El indicador ' . $indicador . ' se encuentra vinculado a la estructura ' . $encuadres . ' de la UR ' . $this->clasAdmGpo . $this->clasAdmUr;

            $this->mixScript('
                notify("' . $mensaje . '","error");
                 $("#indicador"+' . $indicador . ').remove();
            ');

        } else {

            $this->mixScript('
                comunica("poa/programacion/ControladorVinEstructuraProgramaticaMir.php",{
                    metodo:"agregaMeta",
                    epg_id:"' . $this->prm->epg_id . '",
                    trim1:"' . $this->prm->trim1 . '",
                    trim2:"' . $this->prm->trim2 . '",
                    trim3:"' . $this->prm->trim3 . '",
                    trim4:"' . $this->prm->trim4 . '",
                    ind_id: "' . $this->prm->ind_id . '",
                    pii_id:"' . $this->prm->pii_id . '",
                    tiv_id:"' . $this->prm->tiv_id . '",
                });
            ');
        }

    }

    private function agregaMeta()
    {
        $fecha1Enero = strtotime($this->en1Enero);
        $fechaActual = strtotime(date('d-m-Y H:i:00', time()));
        $error = false;
        $gpo = $this->clasAdmGpo;
        $ur = $this->clasAdmUr;
        $epg = $this->prm->epg_id;
        $pii = $this->prm->pii_id;
        $ind_id = $this->prm->ind_id;
        $tipoValor = $this->prm->tiv_id;
        $trim1 = str_replace(',', '', $this->prm->trim1);
        $trim2 = str_replace(',', '', $this->prm->trim2);
        $trim3 = str_replace(',', '', $this->prm->trim3);
        $trim4 = str_replace(',', '', $this->prm->trim4);
        $anual = 0;
        if ($tipoValor == 'FLUJO') {
            $anual = $trim1 + $trim2 + $trim3 + $trim4;
        } elseif ($tipoValor == 'ACUMULADO') {
            $anual = $trim4;
        }

        //Estado de la vinculacion
        $evi_id = $this->edoVinculacion($epg);
        if ($evi_id == 0) {
            //Verifica si hay registros de la vinculación en la bitácora
            $evi_id = $this->edoVinculacionBitacora($epg);
        }
        //Verifica el estado de la vinculacion y la toma de la bitácora
        //Verifica si ya esta marcada alguna meta
        $this->conectar();
        $params = [$epg];
        $this->setSql('
            SELECT
                mac_agrupadorPresupuestal
            FROM cat_MetaActividad
            WHERE epg_id = ?
                AND mac_agrupadorPresupuestal = 1;');
        $this->consultar($params);
        $numR = $this->getNumeroRegistros();
        $agrupadorPresupuestal = ($numR == 0) ? 1 : 2;

        if ($fechaActual <= $fecha1Enero) {
            $this->prm->modificado = '0';
            $this->prm->original = $anual;
            $this->prm->ori_trimestre1 = $trim1;
            $this->prm->ori_trimestre2 = $trim2;
            $this->prm->ori_trimestre3 = $trim3;
            $this->prm->ori_trimestre4 = $trim4;
            $this->prm->mod_trimestre1 = '0';
            $this->prm->mod_trimestre2 = '0';
            $this->prm->mod_trimestre3 = '0';
            $this->prm->mod_trimestre4 = '0';
        } else {
            $this->prm->modificado = $anual;
            $this->prm->original = $anual;
            $this->prm->ori_trimestre1 = $trim1;
            $this->prm->ori_trimestre2 = $trim2;
            $this->prm->ori_trimestre3 = $trim3;
            $this->prm->ori_trimestre4 = $trim4;
            $this->prm->mod_trimestre1 = $trim1;
            $this->prm->mod_trimestre2 = $trim2;
            $this->prm->mod_trimestre3 = $trim3;
            $this->prm->mod_trimestre4 = $trim4;
        }
        $trimestres = [
            'trim1' => $this->prm->ori_trimestre1, 'trim2' => $this->prm->ori_trimestre2,
            'trim3' => $this->prm->ori_trimestre3, 'trim4' => $this->prm->ori_trimestre4,
        ];

        $trimeMod = [
            'trim1' => $this->prm->mod_trimestre1, 'trim2' => $this->prm->mod_trimestre2,
            'trim3' => $this->prm->mod_trimestre3, 'trim4' => $this->prm->mod_trimestre4,
        ];

        //Registra metas
        $trimestreMeta = (int)$this->trimestreActual();
        $registraMetaAnual = new sp_RegistroMetaActividad();
        $registraMetaAnual->setGpo_id($gpo);
        $registraMetaAnual->setUr_id($ur);
        $registraMetaAnual->setPii_id($pii);
        $registraMetaAnual->setEpg_id($epg);
        $registraMetaAnual->setAbc('I');
        $registraMetaAnual->setTri_id($trimestreMeta);
        $registraMetaAnual->setSis_id('POA');
        $registraMetaAnual->setBit_observacion('REGISTRO DE META DESDE PANTALLA A_2_6');
        $registraMetaAnual->setEjercicio(APP_EJERCICIO);
        $registraMetaAnual->conectar();
        $params = [$this->usrIp, $this->usrId];
        $registraMetaAnual->setSql('SET @usr_ip =INET_ATON(?), @usr_id = ?,
            @bit_observacion="META AGREGADA DESDE PANTALLA A_2_6",@mac_anio = 0, @mac_valor = 0, @desdeEPMir=1,
            @tipoCalendario="N", @agrupadorPresupuestal=' . $agrupadorPresupuestal . ', @evi_id=' . $evi_id . ';');
        $registraMetaAnual->consultar($params);
        $registraMetaAnual->execute(false, false);
        //Si el registro es correcto, registrar las metas trimestrales

        if ($registraMetaAnual->getBandera()) {
            for ($i = 1; $i <= 4; $i++) {
                $registraMetaTrimestral = new sp_RegistroMetaActividadTrimestre();
                $registraMetaTrimestral->setGpo_id($gpo);
                $registraMetaTrimestral->setUr_id($ur);
                $registraMetaTrimestral->setPii_id($pii);
                $registraMetaTrimestral->setEpg_id($epg);
                $registraMetaTrimestral->setTri_id($i);
                $registraMetaTrimestral->setMact_original($trimestres['trim' . $i . '']);
                $registraMetaTrimestral->setMact_modificado($trimeMod['trim' . $i . '']);
                $registraMetaTrimestral->setMact_alcanzado('0');
                $registraMetaTrimestral->setAbc('I');
                $registraMetaTrimestral->setMacm_ValorDeseado('0');
                $registraMetaTrimestral->setSis_id('POA');
                $registraMetaTrimestral->setBit_observacion('REGISTRO DE META DESDE PANTALLA A_2_6');
                $registraMetaTrimestral->setEjercicio(APP_EJERCICIO);
                $registraMetaTrimestral->execute();
                if (!$registraMetaTrimestral->getBandera()) {
                    $error = true;
                    $mensaje = $registraMetaTrimestral->getMensaje();
                    break;
                }
            }
        } else {
            $error = true;
            $mensaje = $registraMetaAnual->getMensaje();
        }

        //Muesta mensaje de error
        if ($error) {
            //Elimina la meta de la vista
            $this->mixScript('
                $("#indicador"+' . $ind_id . ').remove();
                notify(" ' . $mensaje . '", "error",
                );
            ');
        } else {

            $this->mixScript('
                $("#metasActividad").modal().remove();
                $("#indicador"+' . $ind_id . ').removeAttr("hidden");
                notify("Meta agregada", "success",
                );
            ');
        }
    }
    private function editaMeta()
    {
        //Bandera para la edicion de la meta, se declara en true
        $bandera = true;
        $anual = 0;
        $error = false;
        $mensajeDevengado = '';

        $fecha1Enero = strtotime($this->en1Enero);
        $fechaActual = strtotime(date('d-m-Y H:i:00', time()));

        $gpo = $this->prm->gpo_id;
        $ur = $this->prm->ur_id;
        $prg = $this->prm->prgs_id;
        $spr = $this->prm->sprs_id;
        $py = $this->prm->py_id;
        $oa = $this->prm->oa_id;

        $epg = $this->prm->epg_id;
        $pii = $this->prm->pii_id;
        $evi_id = $this->getEdoVinculacion($epg, $pii);
        $trim1 = str_replace(',', '', $this->prm->trim1);
        $trim2 = str_replace(',', '', $this->prm->trim2);
        $trim3 = str_replace(',', '', $this->prm->trim3);
        $trim4 = str_replace(',', '', $this->prm->trim4);

        //Arreglo
        $array_original = [
            $trim1,
            $trim2,
            $trim3,
            $trim4,
        ];

        $a = new AccesoDatos();
        $a->conectar();
        $a->setSql('
            SELECT
                ' . BD_SINPRES . '.fn_tableExists("presupuestoDevengado","' . APP_EJERCICIO . '") AS existe;
                ');
        $a->consultar();
        $tabla = $a->extraer();
        if ($tabla['existe'] == 1) {
            $params = [$gpo, $ur, $epg];
            $a->setSql('
                SELECT
                    IF(!isnull(sum(dev_ene+dev_feb+dev_mar)),
                        sum(dev_ene+dev_feb+dev_mar),
                        0) AS trimestre1,
                    IF(!isnull(sum(dev_abr+dev_may+dev_jun)),
                        sum(dev_abr+dev_may+dev_jun),
                        0) AS trimestre2,
                    IF(!isnull(sum(dev_jul+dev_ago+dev_sep)),
                        sum(dev_jul+dev_ago+dev_sep),
                        0) AS trimestre3,
                    IF(!isnull(sum(dev_oct+dev_nov+dev_dic)),
                        sum(dev_oct+dev_nov+dev_dic),
                        0) AS trimestre4
                FROM presupuestoDevengado
                WHERE gpo_id = ?
                    AND ur_id = ?
                    AND epg_id= ?
            ');
            $a->consultar($params);
            $trimestres = $a->extraer();
            $mensaje = '';
            for ($x = 0; $x <= 3; $x++) {
                if ($trimestres['trimestre' . ($x + 1) . ''] != 0 && $array_original[$x] == 0) {
                    $bandera = false;
                    $mensajeDevengado = 'El trimestre ' . ($x + 1) . ' no puede quedar en cero, ya que tiene recurso devengado';
                    break;
                }
            }
        }

        //Cierra la conexion
        $a->desconectar();
        //Verifica que la bandera para la edición de la meta sea true
        if ($bandera) {
            if ($fechaActual <= $fecha1Enero) {
                $this->prm->modificado = '0';
                $this->prm->original = $anual;
                $this->prm->ori_trimestre1 = $trim1;
                $this->prm->ori_trimestre2 = $trim2;
                $this->prm->ori_trimestre3 = $trim3;
                $this->prm->ori_trimestre4 = $trim4;
                $this->prm->mod_trimestre1 = '0';
                $this->prm->mod_trimestre2 = '0';
                $this->prm->mod_trimestre3 = '0';
                $this->prm->mod_trimestre4 = '0';
            } else {
                $this->prm->modificado = $anual;
                $this->prm->original = $anual;
                $this->prm->ori_trimestre1 = $trim1;
                $this->prm->ori_trimestre2 = $trim2;
                $this->prm->ori_trimestre3 = $trim3;
                $this->prm->ori_trimestre4 = $trim4;
                $this->prm->mod_trimestre1 = $trim1;
                $this->prm->mod_trimestre2 = $trim2;
                $this->prm->mod_trimestre3 = $trim3;
                $this->prm->mod_trimestre4 = $trim4;
            }
            $trimestres = [
                'trim1' => $this->prm->ori_trimestre1, 'trim2' => $this->prm->ori_trimestre2,
                'trim3' => $this->prm->ori_trimestre3, 'trim4' => $this->prm->ori_trimestre4,
            ];

            $trimeMod = [
                'trim1' => $this->prm->mod_trimestre1, 'trim2' => $this->prm->mod_trimestre2,
                'trim3' => $this->prm->mod_trimestre3, 'trim4' => $this->prm->mod_trimestre4,
            ];

            for ($i = 1; $i <= 4; $i++) {
                $a = new AccesoDatos();
                $a->conectar();
                $params = [$epg, $pii, $i];
                $a->setSql('
                    SELECT
                        mact_alcanzado
                    FROM cat_MetaActividadTrimestre
                    WHERE epg_id = ?
                        AND pii_id = ?
                        AND tri_id = ?;
                ');
                $a->consultar($params);
                $a->desconectar();
                $datosMeta = $a->extraer();
                $mact_alcanzado = $datosMeta['mact_alcanzado'];

                $editaMetaTrimestral = new sp_RegistroMetaActividadTrimestre();
                $editaMetaTrimestral->setGpo_id($gpo);
                $editaMetaTrimestral->setUr_id($ur);
                $editaMetaTrimestral->setPii_id($pii);
                $editaMetaTrimestral->setEpg_id($epg);
                $editaMetaTrimestral->setTri_id($i);
                $editaMetaTrimestral->setMact_original('"' . $trimestres['trim' . $i . ''] . '"');
                $editaMetaTrimestral->setMact_modificado('"' . $trimeMod['trim' . $i . ''] . '"');
                $editaMetaTrimestral->setMact_alcanzado($mact_alcanzado);
                $editaMetaTrimestral->setAbc('U');
                $editaMetaTrimestral->setMacm_ValorDeseado('0');
                $editaMetaTrimestral->setSis_id('POA');
                $editaMetaTrimestral->setBit_observacion('META EDITADA DESDE PANTALLA A_2_6');
                $editaMetaTrimestral->setEjercicio(APP_EJERCICIO);
                $editaMetaTrimestral->execute();
                if (!$editaMetaTrimestral->getBandera()) {
                    $error = true;
                    $mensaje = $editaMetaTrimestral->getMensaje();
                    break;
                }
            }
            //Muesta mensaje de error
            if ($error) {
                $this->mixScript('
                    notify(" ' . $mensaje . '", "error",
                    );
                ');
            } else {
                $this->mixScript('
                    $("#metasActividad").modal().remove();
                    notify("Meta editada", "success",
                    );
                    comunica("' . $this->pantalla . '",{
                        metodo:"vinculacion",
                        gpo_id:"' . $gpo . '",
                        ur_id:"' . $ur . '",
                        prgs_id:"' . $prg . '",
                        sprs_id:"' . $spr . '",
                        epg_id:"' . $epg . '",
                        evi_id:"' . $evi_id . '",
                        py_id:"' . $py . '",
                        oa_id:"' . $oa . '",
                        edoVin:"' . $this->prm->edoVin . '",

                    });
                ');
            }
        } else {
            $this->mixScript('
                notify(' . $mensajeDevengado . ',"error");
            ');
        }
    }

    //Metodo que obtiene el estado de la vinculacion
    private function getEdoVinculacion($epg_id, $pii_id)
    {
        $params = [$epg_id, $pii_id];
        $this->setSql('
            SELECT
                evi_id
            FROM cat_MetaActividad
            WHERE epg_id = ?
                AND pii_id = ?
            GROUP BY evi_id;
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $registro = $this->extraer();
        return $registro['evi_id'];
    }

    //Método que elimina la actividad
    private function eliminaMeta()
    {
        $epg = $this->prm->epg_id;
        $pii = $this->prm->pii_id;
        $ind_id = $this->prm->ind_id;
        $actualizaAgrupadorPresupuestal = '';
        $mensaje = 'Meta eliminada';
        $fechaActual = strtotime(date('Y-m-d H:i:s'));
        $fechaPoa = strtotime($this->enPoa);
        $fechaInicioEjercicio = strtotime(APP_EJERCICIO . '-01-01 00:00:00');
        //Estado de la vinculación
        $evi_id = $this->getEdoVinculacion($epg, $pii);

        if ($epg > 0) {
            $eliminar = true;
            $ejercicio = false;
            $poa = false;

            $this->conectar();
            $params = [$epg, $pii];
            $this->setSql('
                SELECT
                    mac_agrupadorPresupuestal,
                    usr_fecha
                FROM cat_MetaActividad
                WHERE epg_id = ?
                    AND pii_id = ?;
            ');
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();
            $fechaCreacion = strtotime((string) $datos['usr_fecha']);
            $agrupadorPresupuestal = $datos['mac_agrupadorPresupuestal'];
            //Verifica si estamos en el ejercicio
            if ($fechaActual >= $fechaInicioEjercicio) {

                if ($fechaCreacion <= $fechaPoa) {
                    $ejercicio = true;
                    $eliminar = false;
                    $mensaje = 'No es posible eliminar, Meta registrada durante el POA';
                }
            }
            //Verificar si aun se esta en el POA
            if ($fechaActual <= $fechaPoa) {
                $poa = true;
            }

            //Si estamos durante el ejercicio, apartir del 2027-01-01, la meta capturada antes del 2026-12-31 no se puede eliminar
            if (($ejercicio && !$eliminar) || ($poa && !$eliminar)) {
                $this->mixScript('
                notify("' . $mensaje . '", "warning",
                );');
            }

            //Verifica si tiene adecuacion de metas
            $this->conectar();
            $this->setSql('
                    SELECT
                        ' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacion","' . APP_EJERCICIO . '") AS existe;
            ');
            $this->consultar();
            $tabla = $this->extraer();
            if ($tabla['existe'] == 1) {
                $this->conectar();
                $params = [$epg];
                $this->setSql('
                    SELECT
                        epg_id
                    FROM scp_DetalleModificacion
                    WHERE epg_id = ?
                    LIMIT 1
                ;');
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $eliminar = false;
                }

                $mensaje = 'No es posible eliminar, hay modificación de metas en Adecuaciones Presupuestales';
            }
            $this->conectar();
            $this->setSql('
                SELECT
                    ' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacionMeta","' . APP_EJERCICIO . '") AS existe;
            ');
            $this->consultar();
            $tabla = $this->extraer();
            if ($tabla['existe'] == 1) {
                $this->conectar();
                $params = [$epg];
                $this->setSql('
                    SELECT
                        epg_id
                    FROM scp_DetalleModificacionMeta
                    WHERE epg_id = ?
                    LIMIT 1;
                ');
                $this->consultar($params);
                $this->desconectar();
                if ($this->getNumeroRegistros() > 0) {
                    $eliminar = false;
                }

                $mensaje = 'No es posible eliminar, hay modificación de metas en el Administrador de Adecuacion de Metas';
            }

            //Elimina la meta
            if ($eliminar) {

                if ($agrupadorPresupuestal == 1) {
                    $this->conectar();
                    $params = [$epg, $pii];
                    $this->setSql('
                        SELECT
                            pii_id
                        FROM cat_MetaActividad
                        WHERE epg_id = ?
                            AND pii_id!= ?
                        ORDER BY usr_fecha ASC
                        LIMIT 1;
                    ');
                    $this->consultar($params);
                    $this->desconectar();
                    $datos = $this->extraer();
                    if ($this->getNumeroRegistros() > 0) {
                        $actualizaAgrupadorPresupuestal = ' ,@act_pii_agrupadorPresupuestal=' . $datos['pii_id'];
                    }
                }
                $trimestreMeta = (int)$this->trimestreActual();
                $eliminaMeta = new sp_RegistroMetaActividad();
                $eliminaMeta->setGpo_id($this->clasAdmGpo);
                $eliminaMeta->setUr_id($this->clasAdmUr);
                $eliminaMeta->setPii_id($pii);
                $eliminaMeta->setEpg_id($epg);
                /*$eliminaMeta->setTipoCalendario('N');
                $eliminaMeta->setAgrupadorPresupuestal(2);
                $eliminaMeta->setEvi_id($evi_id);*/
                $eliminaMeta->setAbc('D');
                $eliminaMeta->setTri_id($trimestreMeta);
                $eliminaMeta->setSis_id('POA');
                $eliminaMeta->setBit_observacion('ELIMINACION DE META DESDE PANTALLA A_2_6');
                $eliminaMeta->setEjercicio(APP_EJERCICIO);
                $eliminaMeta->conectar();
                $params = [$this->usrIp, $this->usrId];
                $eliminaMeta->setSql('SET @usr_ip =INET_ATON(?), @usr_id = ?,
                    @bit_observacion="META ELIMINADA DESDE PANTALLA A_2_6",@mac_anio =0 , @mac_valor = 0, @desdeEPMir=1,
                    @tipoCalendario="N", @agrupadorPresupuestal=2, @evi_id=' . $evi_id . '
                    ' . $actualizaAgrupadorPresupuestal . ';');
                $eliminaMeta->consultar($params);
                $eliminaMeta->execute(false, false);

                if (!$eliminaMeta->getBandera()) {
                    $this->mixScript('
                        notify(" ' . $eliminaMeta->getMensaje() . '", "error",
                        );
                    ');
                } else {

                    $this->mixScript('
                        $("#indicador"+' . $ind_id . ').remove();
                        notify("Meta eliminada", "success",
                        );
                    ');
                }
            } else {
                $this->mixScript('
                    notify("' . $mensaje . '", "warning",
                    );
                ');
            }
        } else {
            $this->mixScript('
                $("#indicador"+' . $ind_id . ').remove();
            ');
        }
    }
    //Método que convierte el texto a mayúsculas
    public function strtMayus($variable)
    {
        $variable = strtr(strtoupper($variable), 'àèìòùáéíóúçñäëïöü', 'ÀÈÌÒÙÁÉÍÓÚÇÑÄËÏÖÜ');
        return $variable;
    }
    //Método que cambia el estado de la vinculación en el rechazo
    private function rechazarVinculacion()
    {
        //Definición de variables
        $mensaje = 'La vinculación se ha rechazado';
        $edo1 = '';
        $edo2 = '';
        $rechazarVinculacion = false;
        $ur = '';
        $sefin = '';

        if (isset($this->prm->gpo_id) && isset($this->prm->ur_id) && $this->prm->nivelRevision == 'validacionUR') {
            $ur = 'gpo_id:"' . $this->prm->gpo_id . '" ,
            ur_id:"' . $this->prm->ur_id . ' ",';
        }
        if ((isset($this->prm->unidadResponsable) && $this->prm->unidadResponsable != '') && $this->prm->nivelRevision != 'validacionUR') {
            $sefin = 'unidadResponsable:' . $this->prm->unidadResponsable . ',';
        }
        //Obtiene el estado actualiza de la vinculacion

        //Verifica el nivel de la revisión
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $estadoVinculacion = 3;
                //Variable que indica los estados en los que se puede realizar la actualización de la vinculación
                $edo1 = 2;
                $edo2 = 5;
                break;
            case 'revisionSEFIN':
                $estadoVinculacion = 5;
                //Variable que indica los estados en los que se puede realizar la actualización de la vinculación
                $edo1 = 4;
                $edo2 = 7;
                break;
            case 'validacionSEFIN':
                $estadoVinculacion = 7;
                //Variable que indica los estados en los que se puede realizar la actualización de la vinculación
                $edo1 = 6;
                $edo2 = 8;
                break;
        }

        ///Obtiene el estado actual de la vinculacion
        $edoVinculacion = $this->edoVinculacion($this->prm->epg_id);
        //Valida el estado actual de la vinculacion
        if ($edoVinculacion == 0) {
            $mensaje = 'La vinculación no tiene metas';
        } elseif ($edoVinculacion == $edo1 || $edoVinculacion == $edo2) {
            $rechazarVinculacion = true;
        } elseif ($edoVinculacion != $edo1 || $edoVinculacion != $edo2) {
            $mensaje = 'Proceso cancelado, la solicitud se realizo con anterioridad';
        }

        if ($rechazarVinculacion) {
            //Convierte la observación a mayusculas
            $observacion = $this->strtMayus($this->prm->observacion);
            $ip = $this->usrIp;
            //Actualiza el estado de la vinculación
            $trimestreMeta = (int)$this->trimestreActual();
            $actualizaEstadoVinculacion = new sp_RegistroMetaActividad();
            $actualizaEstadoVinculacion->setGpo_id($this->clasAdmGpo);
            $actualizaEstadoVinculacion->setUr_id($this->clasAdmUr);
            $actualizaEstadoVinculacion->setPii_id(2);
            $actualizaEstadoVinculacion->setEpg_id($this->prm->epg_id);
            /*$actualizaEstadoVinculacion->setTipoCalendario('N');
            $actualizaEstadoVinculacion->setAgrupadorPresupuestal(2);
            $actualizaEstadoVinculacion->setEvi_id($estadoVinculacion);*/
            $actualizaEstadoVinculacion->setAbc('U');
            $actualizaEstadoVinculacion->setTri_id($trimestreMeta);
            $actualizaEstadoVinculacion->setSis_id('POA');
            $actualizaEstadoVinculacion->setBit_observacion($observacion);
            $actualizaEstadoVinculacion->setEjercicio(APP_EJERCICIO);
            $actualizaEstadoVinculacion->conectar();
            $params = [$ip, $this->usrId];
            $actualizaEstadoVinculacion->setSql('SET @usr_ip =INET_ATON(?), @usr_id = ?,
                @bit_observacion="VINCULACIÓN ENVIADA AL TITULAR PARA SU REVISIÓN",@mac_anio = 0, @mac_valor = 0,
                @desde_mir = NULL, @desde_vinculacion = 1, @desdeEPMir=1,
                @tipoCalendario="N", @agrupadorPresupuestal=2, @evi_id=' . $estadoVinculacion . ';
            ');
            $actualizaEstadoVinculacion->consultar($params);
            $actualizaEstadoVinculacion->execute(false, false);

            if (!$actualizaEstadoVinculacion->getBandera()) {
                $this->mixScript('
                    notify(" ' . $actualizaEstadoVinculacion->getMensaje() . '", "error",);
                ');
            } else {
                $this->mixScript('
                    notify("' . $mensaje . '", "success",);
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaVinculacionRead",
                        nivelRevision:"' . $this->prm->nivelRevision . '",
                        edoVin:"' . $this->prm->evi_id . '",
                        ' . $ur . '
                        ' . $sefin . '
                    });
                ');
            }
        } else {
            $this->mixScript('
                notify("' . $mensaje . '",error,);
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaVinculacionRead",
                    nivelRevision:"' . $this->prm->nivelRevision . '",
                    ' . $ur . '
                    ' . $sefin . '
                });
            ');
        }
    }

    //Verifica el estado de la vinculación
    private function edoVinculacion($epg_id)
    {
        $this->conectar();
        $params = [$epg_id];
        $this->setSql('
            SELECT
                evi_id
            FROM cat_MetaActividad
            WHERE epg_id = ?
            GROUP BY epg_id,evi_id
            ');
        $this->consultar($params);
        $this->desconectar();
        $registro = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            return $registro['evi_id'];
        } else {
            return 0;
        }

        //Cero indica que no tiene registro de metas
    }

    //Verifica el estado de la vinculación
    private function edoVinculacionBitacora($epg_id)
    {
        $this->conectar();
        $params = [$epg_id];
        $this->setSql('
            SELECT
                evi_id
            FROM ' . BD_BITACORA . '.bit_MetaActividad
            WHERE epg_id = ?
            ORDER BY usr_fecha DESC
            LIMIT 1
        ');
        $this->consultar($params);
        $this->desconectar();
        $registro = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            return $registro['evi_id'];
        } else {
            return 1;
        }

        //Cero indica que no tiene registro de metas
    }

    //Método cambien el estado de la vinculación de acuerdo al estado de la revisión
    private function revisarVinculacion()
    {
        //Deberia de validar que el estado de la vinculación este en 1 o en 3
        $edoVinculacion = $this->edoVinculacion($this->prm->epg_id);
        $mensaje = '';
        $actualizaRegistro = false;
        $estadoVinculacion = new EstadoVinculacion();
        $estadoVinculacion->setEvi_id(2);
        $estadoVinculacion->select();

        if ($edoVinculacion == 0) {
            $mensaje = 'La vinculación no tiene metas';
        } elseif ($edoVinculacion == 1 || $edoVinculacion == 3) {
            $actualizaRegistro = true;
        } elseif ($edoVinculacion != 1 and $edoVinculacion != 3) {
            $mensaje = 'Proceso cancelado, la solicitud se realizo con anterioridad';
        }
        if ($actualizaRegistro) {
            //Instanciamos el estado de la vinculacion
            $trimestreMeta = (int)$this->trimestreActual();
            $estadoVinculacion = new EstadoVinculacion();
            $estadoVinculacion->setEvi_id(2);
            $estadoVinculacion->select();
            //Actualiza el estado de la vinculación
            $actualizaEstadoVinculacion = new sp_RegistroMetaActividad();
            $actualizaEstadoVinculacion->setGpo_id($this->clasAdmGpo);
            $actualizaEstadoVinculacion->setUr_id($this->clasAdmUr);
            $actualizaEstadoVinculacion->setPii_id(2);
            $actualizaEstadoVinculacion->setEpg_id($this->prm->epg_id);
            $actualizaEstadoVinculacion->setAbc('U');
            $actualizaEstadoVinculacion->setTri_id($trimestreMeta);
            $actualizaEstadoVinculacion->setSis_id('POA');
            $actualizaEstadoVinculacion->setBit_observacion('' . $estadoVinculacion->getNombre() . '');
            $actualizaEstadoVinculacion->setEjercicio(APP_EJERCICIO);
            $actualizaEstadoVinculacion->conectar();
            $params = [$this->usrIp, $this->usrId];
            $actualizaEstadoVinculacion->setSql('SET @usr_ip =INET_ATON(?), @usr_id = ?,
                @bit_observacion="VINCULACIÓN ENVIADA AL TITULAR PARA SU REVISIÓN",@mac_anio = 0, @mac_valor = 0,@desde_mir = NULL, @desde_vinculacion = 1, @desdeEPMir=1, @tipoCalendario="N", @agrupadorPresupuestal=2, @evi_id=' . $this->prm->evi_id . ';
                ');
            $actualizaEstadoVinculacion->consultar($params);
            $actualizaEstadoVinculacion->execute(false, false);

            if (!$actualizaEstadoVinculacion->getBandera()) {
                $this->mixScript('
                    notify(" ' . $actualizaEstadoVinculacion->getMensaje() . '", "error",
                    );
                ');
            } else {
                $this->mixScript('
                    notify("Solicitud enviada a revisión", "success",
                    );
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaEstructuraProgramatica"
                    });
                ');
            }
        } else {
            $this->mixScript('
                    notify("' . $mensaje . '", "error",
                );
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaEstructuraProgramatica"
                });
            ');
        }
    }
    //Método que agrega mensaje de rechazo al rechazar al vinculación
    private function addMensajeRechazo()
    {
        $this->setContenedor('Mensaje');

        $mensajeRechazo = new stdClass();
        $mensajeRechazo->id = 'mensajeRechazo';
        $mensajeRechazo->required = true;
        $mensajeRechazo->enDiv = true;
        $mensajeRechazo->maxlength = 500;
        $mensajeRechazo->width = '300px';
        $mensajeRechazo->height = '100px';
        $mensajeRechazo->style = 'text-transform:uppercase';

        $ur = '';
        if (isset($this->prm->gpo_id) && isset($this->prm->ur_id)) {
            $ur = 'gpo_id:"' . $this->prm->gpo_id . '" ,
            ur_id:"' . $this->prm->ur_id . ' "';
        }

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-x">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Motivo de rechazo</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="Mensaje">
                        ' . $this->ponTextArea($mensajeRechazo) . '
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-outline-success"" id="btnGuardarMensaje" form="">Aceptar</button>
                        <button type="button" class="btn cerrar btn-outline-danger" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#btnGuardarMensaje").click(function(){
                valido = true;
                $("#Mensaje").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!this.checkValidity()){
                        $(this).before(\'<div class="form-validation-field-2formError parentFormlogin-form formError" style="display:none; left: 90%; width: 100%;  padding-top: 100px; margin-top: -35px; opacity: 0.87; position: absolute; "><div class="formErrorContent">\'+this.validationMessage+\'</div><div class="formErrorArrow"><div class="line10"><!-- --></div><div class="line9"><!-- --></div><div class="line8"><!-- --></div><div class="line7"><!-- --></div><div class="line6"><!-- --></div><div class="line5"><!-- --></div><div class="line4"><!-- --></div><div class="line3"><!-- --></div><div class="line2"><!-- --></div><div class="line1"><!-- --></div></div></div>\');
                    }
                });
                $( ".formError" ).show("pulsate");
                setTimeout(function() { $( ".formError" ).hide("puff"); }, 4000 );
                setTimeout(function() { $( ".formError" ).remove(); }, 4500 );

                if(valido){
                    comunica("' . $this->pantalla . '",{
                        metodo:"rechazarVinculacion",
                        epg_id:"' . $this->prm->epg_id . '",
                        observacion:$("#mensajeRechazo").val(),
                        nivelRevision:"' . $this->prm->nivelRevision . '",
                        evi_id: $("#evi_id").val(),
                        unidadResponsable:$("#unidadR").val(),
                        ' . $ur . '
                    });
                    $("#myModal").modal().remove();
                }

            });
        ');
    }
    //Método que muestra el listado de folios para validar/autorizar, es de solo lectura
    private function tablaVinculacionRead()
    {
        $html = '';

        //Boton Regresar
        $btnRegresar = new stdClass();
        $btnRegresar->id = 'btnRegresar';
        $btnRegresar->icon = 'circle-left me-2';
        $btnRegresar->class = 'btn btn-test regresar';
        $btnRegresar->text = 'Regresar';
        $btnRegresar->value = 'Regresar';
        $btnRegresar->click = '
            comunica("' . $this->pantalla . '",{
                metod:"main"
            });
        ';
        //Filtro seleccionado
        $filtroEstadoVinculacionSeleccionado = isset($this->prm->edoVin) ? $this->prm->edoVin : '';
        $ur_filtro = '';
        if (isset($this->prm->gpo_id) && isset($this->prm->ur_id)) {
            $ur_filtro = 'gpo_id:"' . $this->prm->gpo_id . '",
                        ur_id:"' . $this->prm->ur_id . '"';
        }

        //Combo de estado de la vinculación
        $combo = new stdClass();
        $combo->id = 'evi_id';
        $combo->label = 'Filtro por estado';
        $combo->change = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaVinculacionRead",
                edoVin:$(this).val(),
                nivelRevision:"' . $this->prm->nivelRevision . '",
                unidadResponsable:$("#unidadR").val(),
                ' . $ur_filtro . '
            });
        ';
        $where = '';
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $where .= '
                    WHERE evi_id IN (2,5)
                ';
                break;

            case 'revisionSEFIN':
                $where .= '
                    WHERE evi_id IN (4,7)
                ';
                break;
            case 'validacionSEFIN':
                $where .= '
                    WHERE evi_id IN (6,8)
                ';
        }

        $combo->sql = 'SELECT evi_id as e,evi_nombre FROM cat_EstadoVinculacion
                    ' . $where . ';';

        if ($filtroEstadoVinculacionSeleccionado != '') {
            $combo->selecciona = $filtroEstadoVinculacionSeleccionado;
        }

        //Combo UR
        if ($this->prm->nivelRevision == 'revisionSEFIN' || $this->prm->nivelRevision == 'validacionSEFIN') {
            $filtroURSeleccionado = isset($this->prm->unidadResponsable) ? $this->prm->unidadResponsable : '';
            $comboUR = new StdClass();
            $comboUR->sql = 'SELECT concat(gpo_id,ur_id),ur_nombre
                FROM cat_UnidadResponsable
                WHERE gpo_id = 1 and ur_id = 14';
            $comboUR->id = 'unidadR';
            $comboUR->name = 'nombreUR';
            $comboUR->placeholder = 'Filtro por Unidad Responsable';
            $comboUR->enDiv = true;
            $comboUR->label = 'Filtro por Unidad Responsable';
            $comboUR->search = true;
            $comboUR->change = '
            comunica("' . $this->pantalla . '",{
                metodo: "llenaTablaVinculacionRead",
                unidadResponsable:$(this).val(),
                nivelRevision:"' . $this->prm->nivelRevision . '",
                evi_id:$("#evi_id").val(),
            });';
            if ($filtroURSeleccionado != '') {
                $comboUR->selecciona = $filtroURSeleccionado;
            }
        }

        $tabla = new stdClass();
        $tabla->id = 'tbl-tablaVinculacionRead';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->nivelRevision = (!empty($this->nivelRevision) ? $this->nivelRevision : '');
        unset($param);
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];

        //Definición de columnas
        $tr = [];
        //Programa
        $th = new stdClass();
        $th->texto = 'PG';
        $th->width = '5px';
        array_push($tr, $th);

        //Subprograma
        $th = new stdClass();
        $th->texto = 'SP';
        $th->width = '5px';
        array_push($tr, $th);

        //Proyecto
        $th = new stdClass();
        $th->texto = 'PY';
        $th->width = '5px';
        array_push($tr, $th);

        //Número de Obra/Actividad
        $th = new stdClass();
        $th->texto = 'O/A';
        $th->width = '5px';
        array_push($tr, $th);

        //Descripción de la Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Descripción';
        $th->width = '5px';
        array_push($tr, $th);

        //Tipo de la Obra/Actividad
        $th = new stdClass();
        $th->texto = 'Tipo de Actividad';
        $th->width = '5px';
        array_push($tr, $th);

        //Vincular
        $th = new stdClass();
        $th->texto = 'Vincular';
        $th->width = '5px';
        array_push($tr, $th);

        //Columna de Autorización/validación
        $th = new stdClass();
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $th->texto = 'Enviar a SEFIN Vinculación';
                break;
            case 'revisionSEFIN':
                $th->texto = 'Solicitar Validación SEFIN';
                break;
            case 'validacionSEFIN':
                $th->texto = 'Validar SEFIN';
                break;
        }
        $th->width = '60px';
        array_push($tr, $th);

        //Columna de Rechazo
        $th = new stdClass();
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $th->texto = 'Rechazar Envio';
                break;
            case 'revisionSEFIN':
                $th->texto = 'Rechazar Envio SEFIN';
                break;
            case 'validacionSEFIN':
                $th->texto = 'Rechazar Envio';
                break;
        }
        $th->width = '60px';
        array_push($tr, $th);

        //Enviar a validar
        $th = new stdClass();
        $th->texto = 'Status';
        $th->width = '30px';
        array_push($tr, $th);

        //Desvalidar
        if (
            $this->presupuesto && $this->permisoVinculacionValSefin
            && $this->prm->nivelRevision == 'validacionSEFIN'
        ) {
            $th = new stdClass();
            $th->texto = 'Desvalidar';
            $th->width = '30px';
            array_push($tr, $th);
        }

        $tabla->encabezado = [$tr];

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaVinculacionRead';
        $tabla->comunica->evi_id = '{$("#evi_id").val()}';
        $tabla->comunica->nivelRevision = $this->prm->nivelRevision;
        if (isset($this->prm->gpo_id) && $this->prm->ur_id) {
            $tabla->comunica->gpo_id = $this->prm->gpo_id;
            $tabla->comunica->ur_id = $this->prm->ur_id;
        }
        $trbody = [];

        //Programa
        $td = new stdClass();
        $td->campoTxt = 'prg_id';
        array_push($trbody, $td);

        //Subprograma
        $td = new stdClass();
        $td->campoTxt = 'spr_id';
        array_push($trbody, $td);

        //Proyecto
        $td = new stdClass();
        $td->campoTxt = 'py_id';
        array_push($trbody, $td);

        //Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($trbody, $td);

        //Nombre Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($trbody, $td);

        //Tipo de Obra/Actividad
        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($trbody, $td);

        //Habilita el botón de vinculación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'vinculacion';
        $td->casos->tipoCondicion = '==';
        $td->casos->valor = '0';

        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'eye';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->evi_id = 'evi_id';
        $td->casos->verdadero->attr->gpo_id = 'gpo_id';
        $td->casos->verdadero->attr->ur_id = 'ur_id';
        $td->casos->verdadero->attr->prgs_id = 'prg_id';
        $td->casos->verdadero->attr->sprs_id = 'spr_id';
        $td->casos->verdadero->attr->py_id = 'py_id';
        $td->casos->verdadero->attr->oa_id = 'oa_id';
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->class = 'vinculacionMir btn-outline-info';

        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'eye';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->evi_id = 'evi_id';
        $td->casos->falso->attr->gpo_id = 'gpo_id';
        $td->casos->falso->attr->ur_id = 'ur_id';
        $td->casos->falso->attr->prgs_id = 'prg_id';
        $td->casos->falso->attr->sprs_id = 'spr_id';
        $td->casos->falso->attr->py_id = 'py_id';
        $td->casos->falso->attr->oa_id = 'oa_id';
        $td->casos->falso->attr->epg_id = 'epg_id';
        $td->casos->falso->class = 'vinculacionMir btn-outline-success';

        array_push($trbody, $td);

        //Columna autorización/validación
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'evi_id';
        $td->casos->tipoCondicion = '||';
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $td->casos->valor = '2,5';
                break;
            case 'revisionSEFIN':
                $td->casos->valor = '4,7';
                break;
            case 'validacionSEFIN':
                $td->casos->valor = '6';
                break;
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'black';
        $td->casos->verdadero->icono = 'paper-plane';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->attr->nivelRevision = $this->prm->nivelRevision;
        $td->casos->verdadero->class = 'enviarSEFIN';
        array_push($trbody, $td);

        //Columna Rechazo
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'evi_id';
        $td->casos->tipoCondicion = '||';
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $td->casos->valor = '2,5';
                break;
            case 'revisionSEFIN':
                $td->casos->valor = '4,7';
                break;
            case 'validacionSEFIN':
                $td->casos->valor = '6';
                break;
        }
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->icono = 'remove';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->epg_id = 'epg_id';
        $td->casos->verdadero->attr->nivelRevision = $this->prm->nivelRevision;
        $td->casos->verdadero->class = 'rechazarEnvio btn-outline-danger';
        array_push($trbody, $td);

        //Estatus
        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'evi_id';
        $td->casos->tipoCondicion = '||';
        $td->casos->valor = '1,2,3,4,5,6,7,8';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'bolita';
        $td->casos->verdadero->linea = 'evi_color';
        $td->casos->verdadero->color = 'evi_color';
        $td->casos->verdadero->campoTxt = 'evi_id';
        $td->casos->verdadero->lbl = 'evi_nombre';
        $td->casos->verdadero->title = 'evi_nombre';
        array_push($trbody, $td);

        //Desvalidar
        if (
            $this->presupuesto && $this->permisoVinculacionValSefin
            && $this->prm->nivelRevision == 'validacionSEFIN'
        ) {
            $td = new stdClass();
            $td->casos = new stdClass();
            $td->casos->campo = 'evi_id';
            $td->casos->tipoCondicion = '==';
            $td->casos->valor = '8';
            $td->casos->verdadero = new stdClass();
            $td->casos->verdadero->tipo = 'boton';
            $td->casos->verdadero->icono = 'paper-plane';
            $td->casos->verdadero->color = 'amarillo';
            $td->casos->verdadero->attr = new stdClass();
            $td->casos->verdadero->attr->epg_id = 'epg_id';
            $td->casos->verdadero->attr->nivelRevision = $this->prm->nivelRevision;
            $td->casos->verdadero->class = 'rechazarEnvio';
            array_push($trbody, $td);
        }

        $tabla->encabezado = [$tr];
        $tabla->cuerpo = $trbody;
        if ($this->prm->nivelRevision == 'validacionUR') {
            $this->mixHtml('
                <div id = "div_estados" class="col-md-11 col-xs-11" style = "display:none"></div>
                    ' . $this->ponCombo($combo) . '
                </div>
            ');
        }
        if ($this->prm->nivelRevision == 'revisionSEFIN' || $this->prm->nivelRevision == 'validacionSEFIN') {
            $this->mixHtml('
                <div class="row class="col-md-12 col-xs-12 "">
                    <fieldset class="col-md-6 col-xs-6" id="">
                        ' . $this->ponCombo($comboUR) . '
                    </fieldset>
                    <fieldset id = "div_ur" class="col-md-6 col-xs-6" id="">
                        ' . $this->ponCombo($combo) . '
                    </fieldset>
                </div>
            ');
        }
        $this->mixHtml('
            <div id = "div_clave" class="col-md-12 col-xs-12" style = "display:none">' . $html . '</div>

                ' . $this->wrPonTabla($tabla) . '
            </div>
            <div class="row">
                <fieldset id="Mensaje" class="cold-md-12 col-xs-12">
                </fieldset>
            </div>
        ');
        $btnRegresa = $this->ponBoton($btnRegresar);
        $this->agregaTools($btnRegresa);
        $this->mixScript('
            $("#btnRegresar").removeClass("btn btn-default");
            $("#btnRegresar").addClass("btn btn-outline-secondary me-1");
        ');

    }

    //Método que llena la tabla del listado de folios para validar o autorizar
    private function llenaTablaVinculacionRead()
    {
        $where = '';
        $and = '';
        $sefin = '';
        $this->prm->edoVin = isset($this->prm->edoVin) ? $this->prm->edoVin : (isset($this->prm->evi_id) ? $this->prm->evi_id : '');
        $filtroEvi = '';
        $filtroUR = '';
        $params = [];


        if (isset($this->prm->edoVin) && $this->prm->edoVin != 'null' && $this->prm->edoVin != '') {
            $params = ['evi' => $this->prm->edoVin];
            $filtroEvi = ' WHERE evi_id = :evi ';
        }
        if (isset($this->prm->unidadResponsable) && $this->prm->unidadResponsable != 'null' && $this->prm->unidadResponsable != '') {
            $params['gpo'] = substr($this->prm->unidadResponsable, -3, 1);
            $params['ur'] = substr($this->prm->unidadResponsable, -2);
            $filtroUR = ' AND gpo_id = :gpo AND ur_id = :ur; ';
            $sefin = '
                edoVin: $("#evi_id").val(),
                unidadResponsable:$("#unidadR").val(),';
        }

        $ur = '';
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $params['gpo'] = $this->clasAdmGpo;
                $params['ur'] = $this->clasAdmUr;
                $and = 'AND gpo_id = :gpo
                        AND ur_id = :ur ';
                $where .= '
                    WHERE evi_id IN (2,5)
                ';
                break;

            case 'revisionSEFIN':
                $where .= '
                    WHERE evi_id IN (4,7)
                ';
                if (isset($this->prm->gpo_id) && isset($this->prm->ur_id)) {
                    $params['gpo'] = $this->prm->gpo_id;
                    $params['ur'] = $this->prm->ur_id;
                    $and = ' AND gpo_id = :gpo AND ur_id = :ur ';
                    $ur = 'gpo_id:' . $this->prm->gpo_id . ',
                        ur_id:' . $this->prm->ur_id;
                }
                break;
            case 'validacionSEFIN':
                $where .= '
                    WHERE evi_id IN (6,8)
                ';
                if (isset($this->prm->gpo_id) && isset($this->prm->ur_id)) {
                    $params['gpo'] = $this->prm->gpo_id;
                    $params['ur'] = $this->prm->ur_id;
                    $and = ' AND gpo_id = :gpo AND ur_id = :ur ';
                    $ur = 'gpo_id:' . $this->prm->gpo_id . ',
                        ur_id:' . $this->prm->ur_id;
                }
        }
        $sql = '
            SELECT
                epg_id,gpo_id,ur_id,
                prg_id,spr_id,py_id,
                oa_id,oa_nombre,toa_id,
                vinculacion,
                IF(toa_id = 1,"SUB.PRESUPUESTO","SUB. PLANEACION") AS toa_nombre,
                evi_id,evi_color,evi_nombre
            FROM (
                SELECT
                    epg_id,gpo_id,ur_id,
                    prg_id,spr_id,py_id,
                    oa_id,oa_nombre,toa_id,
                    (
                        SELECT
                            count(epg_id)
                        FROM cat_MetaActividad
                        WHERE epg_id = e.epg_id
                        LIMIT 1) AS vinculacion,
                    (
                        SELECT
                            evi_id
                        FROM cat_MetaActividad
                        WHERE epg_id = e.epg_id
                        LIMIT 1) AS evi_id
                FROM cat_ObraActividad
                    INNER JOIN enc_Programatico e USING (gpo_id, ur_id, prg_id, spr_id, py_id, oa_id)
                WHERE oa_id != 0
                    ' . $and . '
                    AND toa_id = 1
                    AND prg_id = 0
                ) AS t
                LEFT JOIN cat_EstadoVinculacion USING (evi_id)';

        $sql .= ($filtroEvi != '') ? $filtroEvi : $where;
        $sql .= $filtroUR;
        $this->setSql($sql);
        $this->setParams($params);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl-tablaVinculacionRead';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".vinculacionMir").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"vinculacion",
                    evi_id:$(this).attr("evi_id"),
                    gpo_id:$(this).attr("gpo_id"),
                    ur_id:$(this).attr("ur_id"),
                    prgs_id:$(this).attr("prgs_id"),
                    sprs_id:$(this).attr("sprs_id"),
                    py_id:$(this).attr("py_id"),
                    oa_id:$(this).attr("oa_id"),
                    epg_id:$(this).attr("epg_id"),
                    edoVin:"' . $this->prm->edoVin . '",
                    nivelRevision:"' . $this->prm->nivelRevision . '",
                    ' . $sefin . '
                });
                return false;
            });

            $(".enviarSEFIN").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"actualizaEstadoVinculacion",
                    epg_id:$(this).attr("epg_id"),
                    nivelRevision:"' . $this->prm->nivelRevision . '",
                    evi_id:$("#evi_id").val(),
                    unidadResponsable:$("#unidadR").val(),
                    ' . $ur . '
                    ' . $sefin . '
                });
                return false;
            });

            $(".rechazarEnvio").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo:"addMensajeRechazo",
                    epg_id:$(this).attr("epg_id"),
                    nivelRevision:"' . $this->prm->nivelRevision . '",
                    ' . $ur . '
                });
                return false;
            });
        ');
    }

    //Método registra la actualización del estado de la vinculación, cuando se envía a revisón
    private function actualizaEstadoVinculacion()
    {
        //Definición de variables
        $estado = '';
        $mensaje = '';
        $edo1 = '';
        $edo2 = '';
        $filtros = '';
        $actualizaVinculacion = false;

        //Verifica la UR
        if (isset($this->prm->gpo_id) && isset($this->prm->ur_id) && $this->nivelRevision == 'validacionUR') {
            $filtros = 'gpo_id:"' . $this->prm->gpo_id . '" ,
                        ur_id:"' . $this->prm->ur_id . ' "';
        }

        //Verifica si hay filtros seleccionados
        if ((isset($this->prm->unidadResponsable) || isset($this->prm->evi_id))
            && $this->prm->nivelRevision != 'validacionUR') {
            $filtros = 'unidadResponsable:"' . $this->prm->unidadResponsable . '" ,';
        }

        //Verifica el nivel de la revisión
        switch ($this->prm->nivelRevision) {
            case 'validacionUR':
                $estado = 4;
                $mensaje = 'Vinculación enviada a Presupuesto';
                //Variable que indica los estados en los que se puede realizar la actualización de la vinculación
                $edo1 = 2;
                $edo2 = 5;
                break;
            case 'revisionSEFIN':
                $estado = 6;
                $mensaje = 'Vinculación enviada a Jefe de Departamento';
                //Variable que indica los estados en los que se puede realizar la actualización de la vinculación
                $edo1 = 4;
                $edo2 = 7;
                //Valida si hay filtros seleccionados
                if (isset($this->prm->gpo_id) && $this->prm->ur_id) {
                    $filtros = 'gpo_id:"' . $this->prm->gpo_id . '" ,
                                ur_id:"' . $this->prm->ur_id . ' "';
                }
                break;
            case 'validacionSEFIN':
                $estado = 8;
                $mensaje = 'Vinculación validada por Presupuesto';
                //Variable que indica el estado en que se puede realizar la actualización de la vinculación
                $edo1 = 6;
        }

        //Obtiene el estado actual de la vinculacion
        $edoVinculacion = $this->edoVinculacion($this->prm->epg_id);
        //Valida el estado de la vinculacion
        if ($edoVinculacion == 0) {
            $mensaje = 'La vinculación no tiene metas';
        } elseif (($this->prm->nivelRevision == 'validacionSEFIN' && $edoVinculacion == $edo1)
            || ($edoVinculacion == $edo1 || $edoVinculacion == $edo2)) {
            $actualizaVinculacion = true;
        } elseif (($this->prm->nivelRevision == 'validacionSEFIN' && $edoVinculacion != $edo1)
            || ($edoVinculacion != $edo1 || $edoVinculacion != $edo2)) {
            $mensaje = 'Proceso cancelado, la solicitud se realizo con anterioridad';
        }

        if ($actualizaVinculacion) {
            //Instanciamos el estado de la vinculacion
            $estadoVinculacion = new EstadoVinculacion();
            $estadoVinculacion->setEvi_id(2);
            $estadoVinculacion->select();
            $ip = $this->usrIp;
            //Actualiza el estado de la vinculación
            $trimestreMeta = (int)$this->trimestreActual();
            $actualizaEstadoVinculacion = new sp_RegistroMetaActividad();
            $actualizaEstadoVinculacion->setGpo_id($this->clasAdmGpo);
            $actualizaEstadoVinculacion->setUr_id($this->clasAdmUr);
            $actualizaEstadoVinculacion->setPii_id(2);
            $actualizaEstadoVinculacion->setEpg_id($this->prm->epg_id);
            $actualizaEstadoVinculacion->setAbc('U');
            $actualizaEstadoVinculacion->setTri_id($trimestreMeta);
            $actualizaEstadoVinculacion->setSis_id('POA');
            $actualizaEstadoVinculacion->setBit_observacion('' . $estadoVinculacion->getNombre() . '');
            $actualizaEstadoVinculacion->setEjercicio(APP_EJERCICIO);
            $actualizaEstadoVinculacion->conectar();
            $params = [$ip, $this->usrId];
            $actualizaEstadoVinculacion->setSql('SET @usr_ip =INET_ATON(?), @usr_id = ?,
                @bit_observacion="VINCULACIÓN ENVIADA AL TITULAR PARA SU REVISIÓN",@mac_anio = 0,
                @mac_valor = 0,@desde_mir = NULL, @desde_vinculacion = 1, @desdeEPMir=1,
            @tipoCalendario="N", @agrupadorPresupuestal=2, @evi_id=' . $estado . ';');
            $actualizaEstadoVinculacion->consultar($params);
            $actualizaEstadoVinculacion->execute(false, false);

            if (!$actualizaEstadoVinculacion->getBandera()) {
                $this->mixScript('
                    notify(" ' . $actualizaEstadoVinculacion->getMensaje() . '", "error",
                    );
                ');
            } else {
                $this->mixScript('
                    notify("' . $mensaje . '", "success",
                    );
                    comunica("' . $this->pantalla . '",{
                        metodo:"tablaVinculacionRead",
                        nivelRevision:"' . $this->prm->nivelRevision . '",
                        edoVin:"' . $this->prm->evi_id . '",
                        ' . $filtros . '
                    });
                ');
            }
        } else {
            $this->mixScript('
                    notify("' . $mensaje . '", "error",
                );
                comunica("' . $this->pantalla . '",{
                    metodo:"tablaVinculacionRead",
                    nivelRevision:"' . $this->prm->nivelRevision . '",
                    edoVin:"' . $this->prm->evi_id . '",
                    ' . $filtros . '
                });
            ');
        }
    }

    //Método que muestra el historial del rechazo del encuadre programatico
    private function historialRechazo()
    {
        $body = '';
        $this->setContenedor('divContenedorGeneralModal');
        $params = [$this->prm->epg_id];
        //Consulta que obtiene el historial
        $sql = "
            SELECT
                DATE_FORMAT(m.usr_fecha,'%d/%m/%Y') AS fecha,
                m.evi_idActual,e.evi_nombre,
                m.bit_observacion,u.usr_id,
                u.usr_nombre,u.usr_paterno,u.usr_materno
            FROM " . BD_BITACORA . '.bit_MetaActividad m
                INNER JOIN cat_EstadoVinculacion e ON e.evi_id = m.evi_idActual
                INNER JOIN ' . BD_USUARIO . ".cat_Usuario u ON u.usr_id = m.usr_id
            WHERE epg_id = ?
                AND m.bit_Observacion NOT LIKE '%DESDE PANTALLA A_2_6%'
                AND evi_idActual IN(3,5,7);
        ";

        $this->conectar();
        $this->setSql($sql);
        $this->consultar($params);
        $this->desconectar();
        while ($row = $this->extraer()) {
            $body .= '
            <tr>
                <td>' . $row['fecha'] . '</td>
                <td>' . $row['evi_idActual'] . ' - ' . $row['evi_nombre'] . '</td>
                <td>' . $row['bit_observacion'] . '</td>
                <td>' . $row['usr_id'] . ' - ' . $row['usr_nombre'] . ' ' . $row['usr_paterno'] . ' ' . $row['usr_nombre'] . '</td>
            </tr>
            ';
        }

        $modalHistorial = '
            <div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel"></h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <table class="table table table-bordered table-hover">
                                    <thead>
                                        <tr class="success">
                                            <th style="text-align:center;"
                                            colspan="5"
                                            style="">Historial de Rechazo</th>
                                        </tr>
                                        <tr>
                                            <th width="15%">Fecha</th>
                                            <th width="35%">Situación</th>
                                            <th width="35%">Comentario</th>
                                            <th width="15%">Usuario</th>
                                        </tr>
                                    </thead>
                                    <tbody>' . $body . '</tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn cerrar btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            </div>

        ';
        $this->mixHtml($modalHistorial);
        $this->mixScript('
            $("#myModal").modal().show();
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            });
        ');
    }

    //Método que obtiene y regresa el trimestre actual
    private function trimestreActual()
    {
        $fechaActual = date('Y-m-d H:i:s');
        $fechaPoa = APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59';
        $mirInicial = 0;
        $fechaInicioTrimestre1 = APP_EJERCICIO . '-01-01 00:00:00';
        $mirTrimestre1 = 1;
        $fechaInicioTrimestre2 = APP_EJERCICIO . '-04-01 00:00:00';
        $mirTrimestre2 = 2;
        $fechaInicioTrimestre3 = APP_EJERCICIO . '-07-01 00:00:00';
        $mirTrimestre3 = 3;
        $fechaIncioTrimestre4 = APP_EJERCICIO . '-10-01 00:00:00';
        $mirTrimestre4 = 4;
        $fechaTrimestreActual = 0;

        //Verifica en que trimestre de la MIR están las metas
        if ($fechaActual <= $fechaPoa) {
            $trimestreActual = $mirInicial;
        }
        if ($fechaActual >= $fechaInicioTrimestre1 && $fechaActual < $fechaInicioTrimestre2) {
            $trimestreActual = $mirTrimestre1;
        }
        if ($fechaActual >= $fechaInicioTrimestre2 && $fechaActual < $fechaInicioTrimestre3) {
            $trimestreActual = $mirTrimestre2;
        }
        if ($fechaActual >= $fechaInicioTrimestre3 && $fechaActual < $fechaIncioTrimestre4) {
            $trimestreActual = $mirTrimestre3;
        }
        if ($fechaActual >= $fechaIncioTrimestre4) {
            $trimestreActual = $mirTrimestre4;
        }

        return $trimestreActual;
    }
}
//Permisos del usuario que puede ver este controlador
if (isset($_SESSION[_S]['prm']['A']['2']['6']['1'])) {
    new ControladorVinEstructuraProgramaticaMir();
} else {
    WebResponse::sinPermisos();
}
