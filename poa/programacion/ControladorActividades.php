<?php

/*
 * Description of ControladorActividades
 * Objetivo: Registrar las actividades y obras un programa.
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Created: 08-Mayo-2019
 * Migrated: Abril-2022
 * Regla de Negocio
 * Permisos:
 */
$desdeMenu = true;
require_once dirname(__FILE__) . '/../../lib/constantes.php';
if (!isset($_SESSION[_S])) {
    session_start();
    $desdeMenu = true;
}

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../srv/Ped.php';
require_once dirname(__FILE__) . '/../../srv/PesEstrategia.php';
require_once dirname(__FILE__) . '/../../srv/ProgramaPresupuestario.php';
require_once dirname(__FILE__) . '/../../srv/CatFuncional.php';
require_once dirname(__FILE__) . '/../../srv/UbicacionGeografica.php';
require_once dirname(__FILE__) . '/../../ent/ObraActividad.php';
require_once dirname(__FILE__) . '/../../ent/HistorialMensajes.php';
require_once dirname(__FILE__) . '/../../ent/BitObraActividad.php';
require_once dirname(__FILE__) . '/../../srv/CatPep.php';
require_once dirname(__FILE__) . '/../../srv/Pin.php';
require_once dirname(__FILE__) . '/../../srv/PoliticasTransversales.php';
require_once dirname(__FILE__) . '/../../ent/poa/mir/MetaActividadMir.php';

class ControladorActividades extends WebResponse
{
    public $nvj_id;
    public $escritura;
    public $valida;
    public $s_ue;
    public $clasAdm_gpo;
    public $clasAdm_ur;
    public $clasAdm_prg;
    public $clasAdm_spr;
    public $clasAdm_py;
    public $clasAdm_oa;
    public $clasAdm_epg;
    public $clasAdm_ue;
    public $ssc_id;
    public $s_dir;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $usr_id;
    public $ip;
    public $enPoa;

    public function __construct()
    {
        parent::__construct();

        $this->pantalla = 'poa/programacion/ControladorActividades.php';
        $this->nvj_id = isset($_SESSION[_S]['nvj_id']) ? $_SESSION[_S]['nvj_id'] : false;
        $this->escritura = isset($_SESSION[_S]['prm']['A']['2']['2']['2']) ? true : false;
        $this->valida = isset($_SESSION[_S]['prm']['A']['2']['2']['3']) ? true : false;
        $this->s_ue = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : false;
        $this->clasAdm_gpo = isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : null;
        $this->clasAdm_ur = isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : null;
        $this->clasAdm_prg = isset($_SESSION[_S]['clasAdm-prg']) ? $_SESSION[_S]['clasAdm-prg'] : null;
        $this->clasAdm_spr = isset($_SESSION[_S]['clasAdm-spr']) ? $_SESSION[_S]['clasAdm-spr'] : null;
        $this->clasAdm_py = isset($_SESSION[_S]['clasAdm-py']) ? $_SESSION[_S]['clasAdm-py'] : null;
        $this->clasAdm_oa = isset($_SESSION[_S]['clasAdm-oa']) ? $_SESSION[_S]['clasAdm-oa'] : false;
        $this->clasAdm_epg = isset($_SESSION[_S]['clasAdm-epg']) ? $_SESSION[_S]['clasAdm-epg'] : false;
        $this->clasAdm_ue = isset($_SESSION[_S]['clasAdm-ue']) ? $_SESSION[_S]['clasAdm-ue'] : false;
        $this->ssc_id = isset($_SESSION[_S]['ssc_id']) ? $_SESSION[_S]['ssc_id'] : null;
        $this->s_dir = isset($_SESSION[_S]['dir_id']) ? $_SESSION[_S]['dir_id'] : null;
        $this->gpo_id = $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['ur_id'];
        $this->ue_id = isset($_SESSION[_S]['ue_id']) ? $_SESSION[_S]['ue_id'] : null;
        $this->usr_id = $_SESSION[_S]['usr_id'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $this->enPoa = APP_EJERCICIO_ANTERIOR . '-12-31 23:59:59';

        $this->metodo = (isset($this->prm->metodoCtrlAct)) ? $this->prm->metodoCtrlAct : '';

        switch ($this->metodo) {
            case 'addModActividad':
                $this->addModActividad();
                break;
            case 'llenaTablaActividad':
                $this->llenaTablaActividad();
                break;
            case 'abcActividad':
                $this->abcActividad();
                break;
            case 'agregaCveFin':
                $this->agregaCveFin();
                break;
            case 'muestraCveFin':
                $this->muestraCveFin();
                break;
            case 'eliminaCveFin':
                $this->eliminaCveFin();
                break;
            case 'aValidar':
                $this->aValidar();
                break;
            case 'validarOa':
                $this->validarOa();
                break;
            case 'observacion':
                $this->mensaje();
                break;
            case 'verHistorial':
                $this->verHistorial();
                break;
            case 'muestraPedTransversal':
                $this->muestraPedTransversal();
                break;
            case 'agregaPolitica':
                $this->agregaPolitica();
                break;
            case 'eliminaPolitica':
                $this->eliminaPolitica();
                break;
            case 'muestraPlanInstitucional':
                $this->muestraPlanInstitucional();
                break;
            case 'agregaPi':
                $this->agregaPi();
                break;
            case 'eliminaPi':
                $this->eliminaPi();
                break;
            case 'metas':
                $this->metas();
                break;
            case 'verMetaIndicador':
                $this->verMetaIndicador();
                break;
            case 'eliminaMeta':
                $this->eliminaMeta();
                break;
            case 'validaMeta':
                $this->validaMeta();
                break;
            case 'agregaMeta':
                $this->agregaMeta();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $this->mixScript('$("#techos").show();');
        $tabla = new stdClass();
        $tabla->id = 'tablaActividades';
        $tabla->numFilas = 100;
        $tabla->filasAmostrar = [5, 10, 50, 100, 150];
        $form = new stdClass();
        $form->id = 'newActividad';
        $form->controlador = $this->pantalla;
        $form->datosExtra = new stdClass();
        $form->datosExtra->metodoCtrlAct = 'addModActividad';

        $form = new form($form);
        $nuevo = '';

        if ($this->nvj_id <= 2 && $this->escritura) {
            $nuevo = '<button id="newAct" type="submit" form="newActividad"'
                . 'class="btn btn-outline-primary me-1" title="nuevo">'
                . '<i class="fa fa-plus me-2"></i>Nuevo</button>';
        }

        $btn_imprimir = '<button id="printAct" type="submit" form="printActividad"'
            . ' class="btn btn-outline-danger me-1" title="Imprimir estructura de actividades">'
            . '<i class="fa-solid fa-file-pdf me-2"></i>PDF</button>';

        $combo = new stdClass();
        $combo->id = 'eoa_id';
        $combo->label = 'Filtro por estado';
        $combo->change = $this->wrLlamaLlenaTabla('tablaActividades');
        $combo->sql = 'SELECT eoa_id,eoa_nombre FROM cat_EstadoObraActividad;';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'ver/editar';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Número';
        $th->width = '5px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Nombre';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'Tipo de Actividad';
        array_push($tr, $th);

        switch ($this->nvj_id) {
            case 2:
                $th = new stdClass();
                $th->texto = 'Enviar a validación';
                $th->width = '150px';
                array_push($tr, $th);
                break;
        }

        $th = new stdClass();
        $th->texto = 'Status';
        array_push($tr, $th);

        if ($this->nvj_id > 2 && $this->valida) {
            $th = new stdClass();
            $th->texto = 'desvalidar';
            $th->width = '5px';
            array_push($tr, $th);
        }

        $tabla->encabezado = [$tr];
        $tabla->tools = $this->templateCombo($combo);

        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodoCtrlAct = 'llenaTablaActividad';
        $tabla->comunica->eoa_id = '{$("#eoa_id").val()}';
        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->casos = new stdClass();
        $td->casos->campo = 'eoa_id';
        $td->casos->tipoCondicion = '||';
        #se realizan ajustes al estado solo se modifica el 4 en este caso
        $td->casos->valor = '1,3';
        $td->casos->verdadero = new stdClass();
        $td->casos->verdadero->tipo = 'boton';
        $td->casos->verdadero->color = 'gris';
        $td->casos->verdadero->icono = 'pencil';
        $td->casos->verdadero->attr = new stdClass();
        $td->casos->verdadero->attr->oa_id = 'oa_id';
        $td->casos->verdadero->class = 'verActividad';
        $td->casos->falso = new stdClass();
        $td->casos->falso->tipo = 'boton';
        $td->casos->falso->icono = 'eye';
        $td->casos->falso->attr = new stdClass();
        $td->casos->falso->attr->oa_id = 'oa_id';
        $td->casos->falso->class = 'verActividad';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'oa_id';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'oa_nombre';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'toa_nombre';
        array_push($tabla->cuerpo, $td);

        switch ($this->nvj_id) {
            case 2: //UR
                if ($this->escritura) {
                    $td = new stdClass();
                    $td->casos = new stdClass();
                    $td->casos->campo = 'eoa_id';
                    $td->casos->tipoCondicion = '||';
                    $td->casos->valor = '1,3';
                    $td->casos->verdadero = new stdClass();
                    $td->casos->verdadero->tipo = 'boton';
                    $td->casos->verdadero->icono = 'paper-plane';
                    $td->casos->verdadero->attr = new stdClass();
                    $td->casos->verdadero->attr->oa_id = 'oa_id';
                    $td->casos->verdadero->attr->eoa_id = 'eoa_id';
                    $td->casos->verdadero->attr->sts_id = 'sts_id';
                    $td->casos->verdadero->class = 'enviaValidar';
                    array_push($tabla->cuerpo, $td);
                } else {
                    $td = new stdClass();
                    $td->campoTxt = 'vacio';
                    array_push($tabla->cuerpo, $td);
                }
                break;
        }

        $td = new stdClass();
        $td->tipo = 'bolita';
        $td->linea = 'eoa_color';
        $td->color = 'eoa_color';
        $td->campoTxt = 'eoa_id';
        $td->lbl = 'eoa_nombre';
        $td->title = 'eoa_nombre';

        array_push($tabla->cuerpo, $td);

        if ($this->nvj_id > 2 && $this->valida) {
            switch ($this->ssc_id) {
                case 2:
                    $td = new stdClass();
                    $td->casos = new stdClass();
                    $td->casos->campo = 'eoa_id';
                    $td->casos->valor = '5'; /*antes 6*/
                    $td->casos->ands = [];
                    $and = new stdClass();
                    $and->campo = 'toa_id';
                    $and->operador = '==';
                    $and->valor = 2;
                    $and->valorTipo = 'numero';
                    array_push($td->casos->ands, $and);
                    $td->casos->verdadero = new stdClass();
                    $td->casos->verdadero->tipo = 'boton';
                    $td->casos->verdadero->icono = 'paper-plane';
                    $td->casos->verdadero->color = 'amarillo';
                    $td->casos->verdadero->attr = new stdClass();
                    $td->casos->verdadero->attr->oa_id = 'oa_id';
                    $td->casos->verdadero->attr->toa_id = 'toa_id';
                    $td->casos->verdadero->attr->gpo_id = 'gpo_id';
                    $td->casos->verdadero->attr->ur_id = 'ur_id';
                    $td->casos->verdadero->attr->prg_id = 'prg_id';
                    $td->casos->verdadero->attr->spr_id = 'spr_id';
                    $td->casos->verdadero->attr->py_id = 'py_id';
                    $td->casos->verdadero->attr->eoa_id = 'eoa_id';
                    $td->casos->verdadero->class = 'desvalidar';
                    break;
                case 3:
                    $td = new stdClass();
                    $td->casos = new stdClass();
                    $td->casos->campo = 'eoa_id';
                    $td->casos->valor = '4'; /*antes 7*/
                    $td->casos->verdadero = new stdClass();
                    $td->casos->verdadero->tipo = 'boton';
                    $td->casos->verdadero->icono = 'paper-plane';
                    $td->casos->verdadero->color = 'amarillo';
                    $td->casos->verdadero->attr = new stdClass();
                    $td->casos->verdadero->attr->oa_id = 'oa_id';
                    $td->casos->verdadero->attr->toa_id = 'toa_id';
                    $td->casos->verdadero->attr->gpo_id = 'gpo_id';
                    $td->casos->verdadero->attr->ur_id = 'ur_id';
                    $td->casos->verdadero->attr->prg_id = 'prg_id';
                    $td->casos->verdadero->attr->spr_id = 'spr_id';
                    $td->casos->verdadero->attr->py_id = 'py_id';
                    $td->casos->verdadero->attr->eoa_id = 'eoa_id';
                    $td->casos->verdadero->class = 'desvalidar';
                    break;
            }

            array_push($tabla->cuerpo, $td);
        }

        $this->mixHtml($this->wrPonTabla($tabla));

        $this->agregaTools($nuevo . $btn_imprimir);
        $this->mixHtml(
            $form->ponForm() . '<div id="contenedorDialogoDesvalida"/>'
        );
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
                        if(prodIntermedio[0] == prm["pdi_id"]){
                            //Obtiene tamaño
                            var i = indicadorRegistrar.length;
                            indicadorRegistrar[i] = prm["ind_id"];
                            registraMeta = true;
                        } else {
                            registraMeta = false;
                            $("#metasActividad").modal().remove();
                            notify("ESTE TIPO DE OBRA NO PUEDE TENER MAS DE UN PRODUCTO INTERMEDIO", "error", );
                        }
                    }
                } else {
                    registraMeta = true;
                }

                if(registraMeta){
                    this.tr = $("<tr/>").attr("id","indicador"+prm["ind_id"]).attr("hidden","true");
                    this.ver = $("<td/>").append(
                        $("<span/>").addClass("btn btn-xs btn-success").html("ver").click(function(){
                            comunica("poa/programacion/ControladorActividades.php",{
                                metodoCtrlAct:"metas",
                                ind_id:prm["ind_id"],
                                pdi_id:prm["pdi_id"],
                                umv_id:prm["umv_id"],
                                tiv_nombre:prm["tiv_nombre"],
                                descripcion:["descripcion"],
                                pii_id:prm["pii_id"],
                                epg_id:prm["epg_id"],
                                gpo_id:prm["gpo_id"],
                                ur_id:prm["ur_id"],
                                prgs_id:prm["prgs_id"],
                                sprs_id:prm["sprs_id"],
                                py_id:prm["py_id"],
                                oa_id:prm["oa_id"],  
                                lectura:1,
                            });
                        })
                    ).appendTo(this.tr);
                    this.eliminar = $("<td/>").append(
                        $("<span/>").addClass("btn btn-xs btn-danger").html("eliminar").click(function(){
                            alertify.confirm(
                            "<h5> ELIMINAR META</h5>",
                            "¿Confirma que desea eliminar la meta?",
                            function(){
                                for(i=0; i<=indicadorRegistrar.length-1; i++){
                                    console.log("para eliminar la meta");
                                    if(indicadorRegistrar[i]== prm["ind_id"])
                                    {
                                        document.getElementById("indicador"+prm["ind_id"]).remove();
                                        indicadorRegistrar.splice(i,1);
                                    }
                                }

                                comunica("poa/programacion/ControladorActividades.php",{
                                    metodoCtrlAct:"eliminaMeta",
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
                        .attr("value",prm["pdi_id"] ).appendTo(this.tr);
                    this.c4 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["pii_id"] ).appendTo(this.tr);
                    this.c5 = $("<input />").attr("type", "hidden")
                        .attr("name", "ind"+prm["ind_id"]+"[]")
                        .attr("value",prm["macm_id"] ).appendTo(this.tr);

                    obj.append(this.tr);
                    $("#metasActividad").modal().remove();
                    document.getElementById("indicador"+prm["ind_id"]).removeAttribute("hidden");
                }
            }

            $("#eoa_id").change(function(){
                if(validarRequeridos(".clasAdminCombo")){
                    ' . $this->wrLlamaLlenaTabla('tablaActividades') . '
                }
                else{
                    return false;
                }
            });

            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $("#printAct").click(function(){
            var form = $("<form>")
                .attr("action", "rep/doc/poa/programacion/rep_poaProgramacion.php")
                .attr("method", "post")
                .attr("target", "_blank")
                .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "prg_id").attr("value", $("#comboprg").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "spr_id").attr("value", $("#combosp").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "py_id").attr("value", $("#combopy").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "eoa_id").attr("value", $("#eoa_id").val()))
                .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 2))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');

        $this->recargaCombo($combo);
        switch ($this->metodo) {
            case 'abcActividad':
            case 'mainBack':
                $this->prm->eoa_id = null;
                $this->llenaTablaActividad();
                $this->mixScript('desbloquearClasAdmin();');
                break;
            default:
                $clasAdmin = new stdClass();
                $clasAdmin->required = true;
                $clasAdmin->form = 'newActividad';
                $clasAdmin->comboUe = false;
                $clasAdmin->comboOa = false;
                $clasAdmin->filtro = 'actividades';
                $clasAdmin->change = $this->wrLlamaLlenaTabla('tablaActividades');
                $this->mixScript(ClasificacionAdministrativa::muestraClasAdmin($clasAdmin)); //se comento ya que por el momento no esta funcional.
                break;
        }
        $this->mixScript('
            var espacio = document.createElement("br");
            var div = document.getElementById("tablaActividades").getElementsByTagName("div")[8];
            document.getElementById("tablaActividades").insertBefore(espacio,div);
        ');
        $this->mixScript('notify("Recuerda que debes seleccionar la Estructura Programática como primer paso","success",false);');
    }

    private function llenaTablaActividad()
    {
        $filtro = '';
        $params = [
            ':grupo'       => (!is_null($this->clasAdm_gpo) ? $this->clasAdm_gpo : 'null'),
            ':ur'          => (!is_null($this->clasAdm_ur) ? $this->clasAdm_ur : 'null'),
            ':programa'    => (!is_null($this->clasAdm_prg) ? $this->clasAdm_prg : 'null'),
            ':supPrograma' => (!is_null($this->clasAdm_spr) ? $this->clasAdm_spr : 'null'),
            ':proyecto'    => (!is_null($this->clasAdm_py) ? $this->clasAdm_py : 'null'),
        ];
        if (!empty($this->prm->eoa_id)) {
            $filtro = ' AND eoa_id = :eoa ';
            $params[':eoa'] = $this->prm->eoa_id;
        }
        if ($this->nvj_id > 1) {
        } else {
            $filtro .= ' AND ( isnull(ue_id) OR ue_id = :ue) ';
            $params[':ue'] = $this->s_ue;
        }

        $this->setSql('
        SELECT gpo_id,ur_id,prg_id,spr_id,py_id, oa_id,oa_nombre,sts_id,eoa_id,eoa_nombre,eoa_color,
        if(toa_id = 1, "SUB. PRESUPUESTO","SUB. PLANEACIÓN") toa_nombre,toa_id,
        "" vacio
            FROM cat_ObraActividad
            JOIN cat_TipoObraActividad USING(toa_id)
            JOIN cat_EstadoObraActividad USING(eoa_id)
            WHERE gpo_id = :grupo AND
                ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id !=0 ' . $filtro . '
            ORDER BY oa_id;
        ');
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaActividades';
        $this->setParams($params);
        $this->wrLlenaTabla($nav);
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 100;

        $desvalidarEgresos = '';
        if (!is_null($this->ssc_id)) {
            $desvalidarEgresos = ' && ' . $this->ssc_id . ' == 3';
        }

        $this->mixScript('
            $(".verActividad").click(function(){
                if(validarRequeridos(".clasAdminCombo")){
                    comunica("' . $this->pantalla . '",{metodoCtrlAct:"addModActividad",oa_id:$(this).attr("oa_id")});
                }
                else{
                    return false;
                }
            });
            $(".enviaValidar").click(function(){
                var filtro = $("#eoa_id").val();
                comunica("' . $this->pantalla . '",{metodoCtrlAct:"aValidar",oa_id:$(this).attr("oa_id"),eoa_id:$(this).attr("eoa_id"),filtro:filtro});
            });

            $(".desvalidar").click(function(){
                var oaId = $(this).attr("oa_id") ;
                $("#divContenedorGeneralModal").html(
                    $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal").attr("data-bs-backdrop","static")
                        .attr("data-bs-keyboard","false").attr("aria-labelledby","staticBackdropLabel").attr("aria-hidden","false")
                        .html(
                            $("<div/>").addClass("modal-dialog")
                                .html(
                                    $("<div/>").addClass("modal-content")
                                        .html(
                                            $("<div/>").addClass("modal-header")
                                            .html(
                                                $("<h4/>").addClass("modal-title").text("Agregar motivo de rechazo"))
                                                .append( $("<button/>")
                                                .addClass("close cerrar btn btn-xs btn-danger")
                                                .attr("type","button").attr("type","button").attr("data-dismiss","modal").attr("aria-hidden","true")
                                                    .append( $("<i/>").addClass("fa fa-xmark")).click(function(){ $("#myModal").modal().remove(); return false;}) )
                                        ).append(
                                            $("<div/>").addClass("modal-body")
                                                .html( $("<div/>").addClass("from-group")
                                                    .html( $("<label/>").attr("for","rP").text("Motivo de rechazo:") )
                                                    .append( $("<textarea/>").attr("name","rP").attr("id","rP").attr("maxlength","1000").attr("required","1").addClass("form-control") )
                                            )
                                        ).append( $("<div/>").addClass("modal-footer")
                                            .html( $("<button/>").attr("id","btnGuardarObs").addClass("btn btn-outline-success").text("Aceptar")
                                                .click(function(){
                                                    comunica("poa/programacion/ControladorActividades.php",{
                                                        metodoCtrlAct:"validarOa",
                                                        mensaje:$("#rP").val(),
                                                        oa_id: oaId,
                                                        eoa_id:(($(".desvalidar").attr("eoa_id")==4 && $(".desvalidar").attr("toa_id")==2 ' . $desvalidarEgresos . ') ? 5:3),
                                                        gpo_id:$(".desvalidar").attr("gpo_id"),
                                                        ur_id:$(".desvalidar").attr("ur_id"),
                                                        prg_id:$(".desvalidar").attr("prg_id"),
                                                        spr_id:$(".desvalidar").attr("spr_id"),
                                                        py_id:$(".desvalidar").attr("py_id")
                                                    });
                                                    $("#myModal").modal().remove();
                                                    return false;
                                                })
                                            )
                                            .append( $("<button/>").addClass("btn btn-outline-danger").text("Cancelar").click(function(){ $("#myModal").modal().remove(); return false;}) )
                                        )
                                )
                        )
                );
            });
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
        ');
    }

    private function addModActividad()
    {
        // MARCOS: Si viene de la pantalla de previsiones, guardamos lo que venga en las variables de sesión
        $pp = (isset($this->prm->pp) ? '1' : '0');

        if ($pp == 1) {
            $this->clasAdm_prg = $this->prm->comboprg;
            $this->clasAdm_spr = $this->prm->combosp;
            $this->clasAdm_py = $this->prm->combopy;
            $this->clasAdm_oa = null;
            $this->clasAdm_epg = null;
        }

        $form = new stdClass();
        $form->id = 'datosGenerales';
        $form->controlador = 'poa/programacion/ControladorActividades.php';
        $form->datosExtra = new stdClass();
        $form->datosExtra->abc = 'a';
        $form->datosExtra->cvesFin = '"{cveFinArray}"';
        $form->datosExtra->PoliticasT = '"{objetoEpt()}"';
        $form->datosExtra->metas = '"{objetoMetas()}"';
        $form->datosExtra->arrayPI = '"{ArraysObjetivosPi}"';
        $form->datosExtra->metodoCtrlAct = 'abcActividad';
        $form->datosExtra->pp = $pp;
        $guardar = new stdClass();
        $guardar->value = 'Guardar';
        $guardar->form = 'datosGenerales';
        $guardar->id = 'guardaActividad';
        $guardar->type = 'submit';
        $guardar->color = 'azul';
        $oa = new ObraActividad();
        $oa->setGpo_id($this->clasAdm_gpo);
        $oa->setUr_id($this->clasAdm_ur);
        $oa->setPrg_id($this->clasAdm_prg);
        $oa->setSpr_id($this->clasAdm_spr);
        $oa->setPy_id($this->clasAdm_py);
        if (!empty($this->prm->oa_id)) {
            $oa->setOa_id($this->prm->oa_id);
            $oa->select();
            $form->datosExtra->abc = 'c';
            $form->datosExtra->oa_id = $this->prm->oa_id;
            $form->datosExtra->eoa_id = $oa->getEoa_id();
            $form->datosExtra->epg_id = $oa->getEpg();
        }

        $proyecto = isset($this->prm->combopy) ? (int)$this->prm->combopy : 0;
        if ($proyecto > 0 && empty($this->prm->oa_id)) {
            $this->mixScript('
                notify("No se permiten registrar obras de Sub. Planeación","warning");
            ');
            return false;
        }

        $form = new form($form);
        $prm = new stdClass();
        $prm->pp = $pp;
        $prm->comboprg = $this->prm->comboprg ?? '';
        $prm->combosp = $this->prm->combosp ?? '';
        $oaResponse = $this->datosActividad($oa, $prm); // $pp indica si es un programa de previsión

        $form->mixHtml($oaResponse->getHtml());
        $oaResponse->setContenedor('toolsPantalla.tools');
        $this->agregaTools($oaResponse->getHtml());
        $this->mixHtml($form->ponForm());
        $this->mixScript($oaResponse->getScript() . '

            bloquearClasAdmin();
            
            function objetoEpt(){
                var stringObj = "[";
                for(var index in ept) {
                    if(index>0)
                        stringObj += ",";
                    stringObj += "{";
                    var obj = ept[index];
                    c = 0;
                    for(var i in obj) {
                        if( c > 0 )
                            stringObj += ",";
                        stringObj += \'"\' + i + \'"\' + \':"\' + obj[i] + \'"\';
                        c++;
                }
                console.log();
                stringObj += "}";
                }
                stringObj += "]";
                return stringObj;
            }

            function objetoMetas(){
                var stringMetas = "[";
                for(var index in metasArray) {
                    if(index>0)
                        stringMetas += ",";
                    stringMetas += "{";
                    var m = metasArray[index];
                    a = 0;
                    for(var i in m) {
                        if( a > 0 )
                            stringMetas += ",";
                        stringMetas += \'"\' + i + \'"\' + \':"\' + m[i] + \'"\';
                        a++;
                    }
                    console.log();
                    stringMetas += "}";
                }
                stringMetas += "]";
                return stringMetas;
            }

            document.getElementById("datosGenerales").getElementsByTagName("div")[5].setAttribute("id","cbue");
            $("#cbue").removeClass("col-md-9");
            $("#cbue").addClass("col-md-12");

        ');
    }

    public function validaVinculacionEPMir($prm)
    {
        $existenRegistros = false;
        $metaRegistrada = false;
        $html = '<table class="table table table-bordered table-hover" >
            <tr class="warning">
                    <th class="warning" style="text-align:center" align="left" colspan="1"> Mensaje</th>
                    <th class="warning" style="text-align:center" align="left" colspan="5">Acción a realizar</th>
            </tr>
        ';
        $filasmesajes = '';

        foreach ($prm as $val => $value) {
            $numMeta = 1;
            $marcadoPrespuestal = ($numMeta == 1) ? 1 : 2;
            if (is_array($value) && substr($val, 0, 3) == 'ind') {
                $valorindicador = new stdClass();
                $valorindicador->pii_id = $value[4];
                $valorindicador->macm_id = $value[5];
                //Validamos si la meta ya esta registrada
                $this->conectar();
                $this->setSql('
                    SELECT concat(prg_id,"-",spr_id,"-",py_id,"-",oa_id," de la UR ",gpo_id,ur_id) as encuadre, ind_definicion as indicador
                    FROM vin_ObraMir
                     INNER JOIN enc_Programatico USING (epg_id)
                     INNER JOIN vin_ProductoIntermedioIndicador vpfpi using (pii_id)
                    INNER JOIN cat_Indicador ci using (ind_id)
                     WHERE pii_id = ? and macm_id = ?
                ');
                $this->consultar([$value[4],$value[5]]);

                if ((int) $this->getNumeroRegistros() > 0) {
                    $existenRegistros = true;
                    $datos = $this->extraer();
                    $metaRegistrada = true;
                    $filasmesajes .= '<tr style="background-color:rgb(255,0,0,.20);">';
                    $filasmesajes .= '<td> El indicador ' . $datos['indicador'] . ', ya se encuentra registrado en la estructura ' . $datos['encuadre'] . '</td>';
                    $filasmesajes .= '<td>Para guardar la Actividad, deberá elminiar el indicador</td>';
                    $filasmesajes .= '</tr>';
                }

                $numMeta++;
            }
        }

        if ($filasmesajes != '') {
            $html .= $filasmesajes . '</table>';
            $html = '
                <div id="myModalMetasRegistradas" class="modal " id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xs">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="myModalMetasRegistradasLabel">Validación de Vinculación EP-MIR</h4>
                                <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                    <i class="fa fa-xmark"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $html . '</div>
                                <div class="modal-footer">
                                </div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ';
        }
        $resultado = ['metaRegistrada' => $metaRegistrada, 'html' => $html];
        return $resultado;
    }

    private function abcActividad()
    {
        if ($this->prm->abc == 'a') {

            //Verifiamos el estado de la mir;
            $gpo = $_SESSION[_S]['clasAdm-gpo'];
            $ur = $_SESSION[_S]['clasAdm-ur'];
            $prg = $_SESSION[_S]['clasAdm-prg'];
            $prg = (int) $prg;
            $estadoMirValidado = true;
            if ($prg > 99 && $prg < 900) {
                $this->conectar();
                $this->setSql('
                    SELECT emi_id
                    FROM vin_ProgramasMir
                    INNER JOIN cat_Mir using (mir_id)
                    WHERE prgs_id  = ?
                ');
                $params = [$prg];
                $this->consultar($params);
                if ($this->getNumeroRegistros() > 0) {
                    $emi = $this->extraer();
                    $emi_id = $emi['emi_id'];

                    if ($emi_id != 9) {
                        $estadoMirValidado = false;
                    }
                }
            }
            if (!$estadoMirValidado) {
                $this->mixScript(
                    'notify("No se puede registrar la Actividad/Obra, la MIR del programa ' . $prg . ' no está validada", "error");
                '
                );
                return false;
            }
            if ($this->prm->PoliticasT == '[]') {
                $this->mixScript('notify("Agrega una política transversal", "success");');
                return false;
            } else {
                if (is_string($this->prm->PoliticasT)) {
                    $PoliticasT = json_decode($this->prm->PoliticasT);
                }

                $sumaPorcentaje = 0;
                foreach ($PoliticasT as $ept_id) {
                    $sumaPorcentaje += $ept_id->ept_porcentaje;
                }
                $diferencia = abs($sumaPorcentaje - 100);
                if ($sumaPorcentaje == 0) {
                    $this->mixScript('notify("El porcentaje debe ser mayor 0.00%", "success");');
                    return false;
                } elseif ($diferencia != 0) {
                    $this->mixScript('notify("Te hace falta ' . $diferencia . '%, debes de agregar otra política transversal.", "success");');
                    return false;
                }
            }

            $epmir = $this->validaVinculacionEPMir($this->prm);
            if ($epmir['metaRegistrada']) {
                $html = $epmir['html'];
                $this->setContenedor('divContenedorGeneralModal');
                $this->mixHtml($html);
                $this->mixScript('
                    $("#myModalMetasRegistradas").modal().show();
                    $("#myModalMetasRegistradas").on("hidden.bs.modal", function (e) {
                        $("#myModalMetasRegistradas").remove();             
                    });
                    $(".cerrar").click(function(){ 
                        $("#myModalMetasRegistradas").remove(); 
                    });
                ');
                return false;
            }
        }

        $indicadores = [];
        foreach ($this->prm as $val => $value) {
            $numMeta = 1;
            $marcadoPrespuestal = ($numMeta == 1) ? 1 : 2;
            if (is_array($value) && substr($val, 0, 3) == 'ind') {
                $valorindicador = new stdClass();
                $valorindicador->pii_id = $value[4];
                $valorindicador->macm_id = $value[5];
                array_push($indicadores, $valorindicador);
                unset($valorindicador);
            }
            $numMeta++;
        }
        $this->prm->metas = $indicadores;
        $this->prm->eug_id = UbicacionGeografica::getEug_id();
        $this->prm->epe_id = $_SESSION[_S]['ped']->epe;
        $this->prm->epods_id = $_SESSION[_S]['ped']->epods_id;
        $this->prm->eps_id = $_SESSION[_S]['pes']->eps;
        $this->prm->efn_id = $_SESSION[_S]['catfuncional']->efn;
        $this->prm->epp_id = $_SESSION[_S]['pp']->epp;
        $this->prm->epc_id = $_SESSION[_S]['pep']->epc;
        $this->selectEncProgramatico();
        switch ($this->prm->abc) {
            case 'a':
                $this->prm->nombre = preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $this->prm->nombre);
                $mensaje = ' guardada exitosamente';
                $observacion = ' CAPTURADA CON PED:' . $this->prm->epe_id . ',ODS:' . $this->prm->epods_id . ',PES:' . $this->prm->eps_id . ',FUNCIONAL:' . $this->prm->efn_id . ',SECTORIAL:' . $this->prm->epc_id . ',PROGRAMA PRESUPUESTARIO:' . $this->prm->epp_id . ',';
                $this->prm->eoa_id = 1;
                break;
            case 'c':
                $this->prm->nombre = preg_replace("/\r\n+|\r+|\n+|\t+/i", '', $this->prm->nombre);
                $observacion = ' MODIFICADO CON PED:' . $this->prm->epe_id . ',ODS:' . $this->prm->epods_id . ',PES:' . $this->prm->eps_id . ',FUNCIONAL:' . $this->prm->efn_id . ',SECTORIAL:' . $this->prm->epc_id . ',PROGRAMA PRESUPUESTARIO:' . $this->prm->epp_id . ',';
                $mensaje = ' modificada exitosamente';
                $this->prm->eoa_id = $this->prm->eoa_id;
                break;
            case 'b':
                $mensaje = ' eliminada exitosamente';
                $observacion = ' ELIMINADA DESDE PANTALLA A_2_2';
                $epg = $this->prm->epg_id;

                //Valida si hay registros en las cedulas de costeo
                $existeCosteo = $this->verificaCosteo($epg);
                if ($existeCosteo) {
                    $mensaje = 'No se puede eliminar la Actividad ya que tiene cedulas de costeo capturadas';
                    $this->mixScript('
                        notify(" ' . $mensaje . ' ","error")
                    ');
                    return false;
                }

                break;
        }

        if ($mensaje != '') {
            $this->prm->ue_id = $this->clasAdm_ue;
            $this->prm->mensaje = $observacion;

            $oa_id = ObraActividad::abc($this->prm);
            $this->prm->oa_id = $oa_id;

            $this->prm->oa_id = ($this->prm->abc == 'a') ? $oa_id : $this->prm->oa_id;
            $tipo = ((isset($this->prm->toa_id) && $this->prm->toa_id == 1) ? 'Actividad ' : 'Obra ');
            $this->mixScript('notify("' . $tipo . $this->prm->oa_id . $mensaje . '", "success");');
            $this->prm->pp = (isset($this->prm->pp) ? $this->prm->pp : 0);
            if (isset($this->prm->pp) && $this->prm->pp == 0) {
                if (empty($this->prm->noMain)) {
                    $this->main();
                }
            } else {
                $this->mixScript('
                    comunica("mga/ControladorPrevisiones.php",{
                        metodo:"ponTablaActividades",
                            prg_id:' . $this->clasAdm_prg . ',
                            spr_id:' . $this->clasAdm_spr . '
                    });
                ');
            }
        }
    }

    private function agregaCveFin()
    {
        $a = new AccesoDatos();
        $a->conectar();
        $params = [':ecf_id' => $this->prm->ecf_id];
        $a->setSql('SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE ecf_id= :ecf_id;');
        $a->consultar($params);
        $a->desconectar();
        $ecvefin = $a->extraer();
        unset($this->prm->ecf_id);

        $this->prm->ecf_id = $ecvefin['ecf_id'];
        $boton = new stdClass();
        $boton->title = 'Eliminar clave';
        $boton->id = 'cve_' . $this->prm->ecf_id;
        $boton->color = 'rojo';
        $boton->icon = 'trash-can';
        $fila = '
            $("#filasCvesFin").append(
                $("<tr>").attr("id","cvefinFila-' . $this->prm->ecf_id . '")
                    .html("<td>' . $ecvefin['cfi_clave'] . '</td>")
                    .append("<td>' . $ecvefin['cfi_nombre'] . '</td>")
                    .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
            );';
        $this->mixScript('$("#myModal").modal().remove();');
        if (!empty($this->prm->epg_id)) {
            $params = [':ecf' => $this->prm->ecf_id, ':epg' => $this->prm->epg_id];
            $this->conectar();
            $this->setSql('SELECT  ecf_id FROM poa_OrigenRecurso WHERE ecf_id= :ecf AND epg_id= :epg;');
            $this->consultar($params);
            $this->desconectar();
            if ($this->getNumeroRegistros() > 0) {
                $this->mixScript(' notify("La clave ya esta agregada","success");');
            } else {
                $params = [':usr' => $this->usr_id];
                $this->conectar();
                $this->setSql('set @usr_ip=' . BD_SINPRES . '.fn_get_ip(), @usr_id= :usr');
                $this->consultar($params);

                $params = [
                    ':gpo' => $this->clasAdm_gpo,
                    ':ur'  => $this->clasAdm_ur,
                    ':epg' => $this->prm->epg_id,
                    ':ecf' => $this->prm->ecf_id,
                    ':usr' => $this->usr_id,
                ];
                $this->setSql('
                    INSERT INTO poa_OrigenRecurso(gpo_id, ur_id, epg_id, ecf_id, ore_folioModif, ore_folioLib, ore_oficioLib, ore_oficioAut, ore_idObra, usr_id)
                    VALUES(:gpo, :ur, :epg, :ecf, 0, 0, "", "", "", :usr);
                ');
                $this->consultar($params);
                $this->desconectar();
                $this->mixScript('$("#myModal").modal().remove();');

                $DatosOa = new ObraActividad($this->prm);
                $this->selectEncProgramatico();
                $this->prm->nombre = $DatosOa->getNombre();
                $this->prm->observacion = $DatosOa->getObservacion();
                $this->prm->ue_id = $DatosOa->getUe_id();
                $this->prm->anticipo = $DatosOa->getAnticipo();
                $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
                $this->prm->amortizacion = $DatosOa->getAmortizacion();
                $this->prm->eug_id = $DatosOa->getEug_id();
                $this->prm->eoa_id = $DatosOa->getEoa_id();
                $this->prm->toa_id = $DatosOa->getToa_id();
                $this->prm->pbf_id = $DatosOa->getPbf_id();
                $this->prm->sts_id = $DatosOa->getSts_id();
                $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Agregada');
                $this->mixScript('
                    ' . $fila . '
                    $("#cve_' . $this->prm->ecf_id . '").click(function(){
                        alertify.confirm(
                            "<h5> ELIMINAR CLAVE DE FINANCIAMIENTO </h5>",
                            "¿Confirma que desea eliminar la clave de financiamiento?",
                            function(){
                                comunica("poa/programacion/ControladorActividades.php",{
                                    metodoCtrlAct:"eliminaCveFin",
                                    ecf_id:"' . $this->prm->ecf_id . '",
                                    epg_id:' . $this->prm->epg_id . '
                                    });
                            },
                            function(){},
                        ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                    });
                ');
            }
        } else {
            $this->mixScript('
                if(cveFinArray.indexOf("' . $this->prm->ecf_id . '") < 0){
                    cveFinArray.push("' . $this->prm->ecf_id . '");
                    ' . $fila . '
                    $("#cve_' . $this->prm->ecf_id . '").click(function(){
                        $("#cvefinFila-' . $this->prm->ecf_id . '").remove();
                        cveFinArray.splice( cveFinArray.indexOf("' . $this->prm->ecf_id . '"),1);
                    });
                }else notify("La clave ya esta agregada","success");
            ');
        }
    }

    private function muestraCveFin()
    {
        // Se agrego porque marcaba que un objeto se estaba creando vacio.
        if (!isset($_SESSION[_S]['cvefin'])) {
            $_SESSION[_S]['cvefin'] = new stdClass();
        }

        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : '';
        $this->setContenedor('divContenedorGeneralModal');
        $texto = (!isset($this->prm->lectura)) ? 'Clave de Financiamiento' : 'Clave de Financiamiento';

        $and = '';
        $parametros = [];
        if (date('Y') == APP_EJERCICIO_ANTERIOR) {
            $and = ' AND e.cfi_anio = :anio';
            $parametros[':anio'] = substr(APP_EJERCICIO, -2);
        }

        $comboCF = new stdClass();
        $comboCF->label = 'Clave de Financiamiento ';
        $comboCF->id = 'ecf_id';
        $comboCF->class = 'cmbClaveFinanciamiento';
        $comboCF->camposVal = 'ecf_id';
        $comboCF->required = true;
        $comboCF->sql = '
            SELECT DISTINCT cfi_clave, cfi_nombre, ecf_id
            FROM ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . '
            JOIN enc_ClaveFinanciamiento e USING (cff_id,fte_id,sft_id,ori_id,sor_id,cfi_id,cfi_anio)
            WHERE e.sts_id =  1 ' . $and . '
            AND CASE WHEN year(now())<' . APP_EJERCICIO . ' THEN
                ecf_poa = 1
                ELSE
                ecf_poa = ecf_poa
            END;
        ';
        if ($and != '') {
            $comboCF->params = $parametros;
        }
        $comboCF->comoAtr = 'ecf_id';
        $comboCF->search = true;
        $this->recargaCombo($comboCF);

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">' . $texto . '</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formCveFin">
                        ' . $this->ponCombo($comboCF) . '
                        </form>
                    </div>
                    <div class="modal-footer">';
        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnAceptarCvfFin" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPt" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';

        $this->mixHtml($modal);

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            })
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPt").click(function(){ $("#myModal").modal().remove(); });

            $("#btnAceptarCvfFin").click(function(){
                valido = ( $("#ecf_id").val() != "null" );
                ok = true;

                $("#formCveFin").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                });
                if(valido && ok){
                    callbackComunica = function(){
                        $("#modalClaveFinanciamiento").empty();
                    };
                    comunica("poa/programacion/ControladorActividades.php",{
                        metodoCtrlAct: "agregaCveFin",
                        ecf_id:$("#ecf_id").val()
                        ' . $epg_id . '
                    });
                    ' . (!isset($this->prm->epg_id) ? '$("#myModal").modal().remove();' : '') . '
                }
            });
        ');
    }

    private function eliminaCveFin()
    {
        $this->clasAdm_oa = null;
        $params = [':epg' => $this->prm->epg_id];
        $this->setSql('SELECT DISTINCT ecf_id FROM poa_OrigenRecurso WHERE epg_id = :epg ;');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        if ($this->getNumeroRegistros() > 1) {
            $this->conectar();
            $this->setSql('
                SELECT ' . BD_SINPRES . '.fn_tableExists("presupuesto","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe2, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3;
                ');
            $this->consultar();
            $tabla = $this->extraer();

            if ($tabla['existe'] == 1 && $tabla['existe2'] == 1 && $tabla['existe3'] == 1) {
                $params = [':epg' => $this->prm->epg_id, ':ecf' => $this->prm->ecf_id];
                $this->setSql('
                    SELECT count(gpo_id) AS registros FROM poa_Alimentacion
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM poa_Arrendamiento
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM poa_OtrasPartidas
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM poa_SeguroFianza
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT  count(gpo_id) AS registros FROM poa_ServiciosPersonales
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM poa_ServicioVigilancia
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM poa_TelefoniaCelular
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    -- UNION SELECT count(gpo_id) AS registros FROM poa_VehiculoMaquina
                    -- WHERE epg_id = :epg AND ecf_id = :ecf
                    UNION SELECT count(gpo_id) AS registros FROM presupuesto
                    WHERE epg_id = :epg AND ecf_id = :ecf
                    ORDER BY registros DESC
                    ;
                ');
                $this->consultar($params);
                $this->desconectar();
                $totalRegistros = $this->extraer();
                $numRegistros = $totalRegistros['registros'];
                if ($numRegistros == 0) {
                    $this->mixScript(' $("#cvefinFila-' . $this->prm->ecf_id . '").remove(); ');
                    $this->conectar();
                    $params = [':usr' => $this->usr_id];
                    $this->setSql('set @usr_ip= ' . BD_SINPRES . '.fn_get_ip(), @bit_observacion = "CLAVE DE FINANANCIAMIENTO ELIMINADA DESDE PANTALLA A_2_2", @usr_id= :usr');
                    $this->consultar($params);
                    $params = [':epg' => $this->prm->epg_id, ':ecf' => $this->prm->ecf_id];
                    $this->setSql('DELETE FROM poa_OrigenRecurso WHERE epg_id = :epg AND ecf_id = :ecf ;');
                    $this->consultar($params);
                    $this->desconectar();
                    $DatosOa = new ObraActividad($this->prm);
                    $this->selectEncProgramatico();
                    $this->prm->nombre = $DatosOa->getNombre();
                    $this->prm->observacion = $DatosOa->getObservacion();
                    $this->prm->ue_id = $DatosOa->getUe_id();
                    $this->prm->anticipo = $DatosOa->getAnticipo();
                    $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
                    $this->prm->amortizacion = $DatosOa->getAmortizacion();
                    $this->prm->eug_id = $DatosOa->getEug_id();
                    $this->prm->eoa_id = $DatosOa->getEoa_id();
                    $this->prm->toa_id = $DatosOa->getToa_id();
                    $this->prm->pbf_id = $DatosOa->getPbf_id();
                    $this->prm->sts_id = $DatosOa->getSts_id();
                    $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Eliminada');
                } else {
                    $this->mixScript('notify("No se puede eliminar la clave de financiamiento ya que tiene cedulas capturadas","warning");');
                }
            } else {
                $params = [':usr' => $this->usr_id];
                $this->setSql('set @usr_ip = ' . BD_SINPRES . '.fn_get_ip(), @bit_observacion="CLAVE DE FINANANCIAMIENTO ELIMINADA DESDE PANTALLA A_2_2", @usr_id = :usr');
                $this->consultar($params);
                $this->mixScript(' $("#cvefinFila-' . $this->prm->ecf_id . '").remove(); ');
                $params = [':epg' => $this->prm->epg_id, ':ecf' => $this->prm->ecf_id];
                $this->setSql('DELETE FROM poa_OrigenRecurso WHERE epg_id = :epg AND ecf_id = :ecf;');
                $this->consultar($params);
                $this->desconectar();

                $DatosOa = new ObraActividad($this->prm);

                $this->selectEncProgramatico();

                $this->prm->nombre = $DatosOa->getNombre();
                $this->prm->observacion = $DatosOa->getObservacion();
                $this->prm->ue_id = $DatosOa->getUe_id();
                $this->prm->anticipo = $DatosOa->getAnticipo();
                $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
                $this->prm->amortizacion = $DatosOa->getAmortizacion();
                $this->prm->eug_id = $DatosOa->getEug_id();
                $this->prm->eoa_id = $DatosOa->getEoa_id();
                $this->prm->toa_id = $DatosOa->getToa_id();
                $this->prm->pbf_id = $DatosOa->getPbf_id();
                $this->prm->sts_id = $DatosOa->getSts_id();

                $this->registrarBitacora(': CveFin ' . $this->prm->ecf_id . ' Eliminada');
            }
        } else {
            $this->mixScript('notify("No se puede quedar sin Clave de Financiamiento","warning");');
        }
    }

    private function aValidar()
    {
        $params = [
            ':grupo'       => $this->clasAdm_gpo,
            ':ur'          => $this->clasAdm_ur,
            ':programa'    => $this->clasAdm_prg,
            ':supPrograma' => $this->clasAdm_spr,
            ':proyecto'    => $this->clasAdm_py,
            ':oa'          => $this->prm->oa_id,
        ];

        $this->setSql('
            SELECT COUNT(epg_id) n
            FROM enc_Programatico enc
            JOIN vin_ObraMir
            USING(epg_id)
            WHERE
                enc.gpo_id = :grupo AND
                enc.ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id = :oa
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $metasMir = $this->extraer();

        $this->setSql('
            SELECT COUNT(ecf_id) n
            FROM enc_Programatico enc
            JOIN poa_OrigenRecurso
            USING(epg_id)
            WHERE
                enc.gpo_id = :grupo AND
                enc.ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id = :oa
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $cvesFin = $this->extraer();

        $this->setSql('
                SELECT ue_id
                FROM cat_ObraActividad
                WHERE
                gpo_id = :grupo AND
                ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id = :oa
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $ue = $this->extraer();

        $this->setSql('
            SELECT sum(vpt.ept_porcentaje) as porcentaje
            FROM vin_ProgramaticoTransversal vpt
            INNER JOIN enc_Programatico ep using(epg_id)
            where
                ep.prg_id= :programa AND
                ep.spr_id = :supPrograma AND
                ep.py_id = :proyecto AND
                ep.oa_id = :oa AND
                ep.gpo_id = :grupo AND
                ep.ur_id = :ur
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();
        $tet = $this->extraer();

        //
        if ($this->clasAdm_prg > 99 && $this->clasAdm_prg < 900) {
            if ($metasMir['n'] * 1 > 0) {

            } else {
                $this->mixScript('notify("No puede mandar a validar una actividad/Obra sin metas MIR","warning");');
                return false;
            }

            //Verifiamos el estado de la mir;
            $gpo = $_SESSION[_S]['clasAdm-gpo'];
            $ur = $_SESSION[_S]['clasAdm-ur'];
            $prg = $_SESSION[_S]['clasAdm-prg'];
            $this->conectar();
            $this->setSql('
                SELECT emi_id
                FROM vin_ProgramasMir
                INNER JOIN cat_Mir using (mir_id)
                WHERE prgs_id  = ?
            ');
            $params = [$prg];
            $this->consultar($params);
            if ($this->getNumeroRegistros() > 0) {
                $emi = $this->extraer();
                $emi_id = $emi['emi_id'];

                if ($emi_id != 9) {
                    $this->mixScript(
                        'notify("No se puede enviar a validar la Actividad/Obra ya que la MIR de la UR ' . $gpo . $ur . ' del programa ' . $prg . ' no está valida", "warning");
                    '
                    );
                    return false;
                }
            }
        }

        //Se valida que el porcentaje del eje transversal sea 100% al momento de enviar a validar
        if ($tet['porcentaje'] == 100) {
            if (!empty($ue['ue_id'])) {
                if ($cvesFin['n'] * 1 > 0) {
                    $eoa_idFiltro = (isset($this->prm->filtro)) ? $this->prm->filtro : null;
                    $this->prm->gpo_id = $this->clasAdm_gpo;
                    $this->prm->ur_id = $this->clasAdm_ur;
                    $this->prm->prg_id = $this->clasAdm_prg;
                    $this->prm->spr_id = $this->clasAdm_spr;
                    $this->prm->py_id = $this->clasAdm_py;

                    if (isset($this->prm->eoa_id) && $this->prm->eoa_id == 3) {
                        $this->prm->eoa_id = 2;
                    } else {
                        $this->prm->eoa_id = 2;
                    }

                    $this->prm->abc = 'c';

                    if (isset($this->prm->observacion)) {
                        unset($this->prm->observacion);
                    }

                    $this->prm->abc = 'c';
                    $observacion = $this->prm->oa_id . ' ENVIADA A VALIDACION';
                    $this->prm->mensaje = $observacion;
                    ObraActividad::abc($this->prm);
                    $this->mixScript('notify("Actividad enviado a validacion","success");');
                    $this->prm->eoa_id = $eoa_idFiltro;
                    $this->llenaTablaActividad();
                } else {
                    $this->mixScript('notify("No puede mandar a validar una actividad sin clave de financiamiento","warning");');
                }
            } else {
                $this->mixScript('notify("No puede mandar a validar una actividad sin registrar la UE","warning");');
            }
        } else {
            $this->mixScript('notify("El porcentaje de las Políticas Transversales no es igual a 100 %","warning");');
        }
    }

    private function validarOa()
    {
        $this->prm->abc = '';
        $actividad = new ObraActividad($this->prm);
        $epg_id = $actividad->getEpg();
        $this->prm->gpo_id = $actividad->getGpo_id();
        $this->prm->ur_id = $actividad->getUr_id();
        $this->prm->prg_id = $actividad->getPrg_id();
        $this->prm->spr_id = $actividad->getSpr_id();
        $this->prm->abc = 'c';
        if (isset($this->prm->observacion)) {
            unset($this->prm->observacion);
        }

        $this->prm->abc = 'c';
        $mje_id = '';

        if ($this->prm->eoa_id == 3) {
            //Valida si hay registros en las cedulas de costeo
            $existeCosteo = $this->verificaCosteo($epg_id);
            if ($existeCosteo) {
                $mensaje = 'No se puede rechazar o desvalidar la Actividad ya que tiene cedulas de costeo capturadas y/o Presupuesto';
                $this->mixScript('
                    notify(" ' . $mensaje . ' ","error")
                ');
                return false;
            }
            //Valida si hay registro de folios en adecuaciones presupuestarias y/o adecuaciones de metas
            $validaRegistrosDeFolios = $this->verificaModificacionMetas($epg_id, ' rechazar o desvalidar Actividad');
            if (!$validaRegistrosDeFolios['sinRegistros']) {
                $this->mixScript('
                    notify(" ' . $validaRegistrosDeFolios['mensaje'] . ' ","error")
                ');
                return false;
            }
        }

        if (isset($this->prm->eoa_id) && ($this->prm->eoa_id == 3 || ($this->ssc_id == 3 && $this->prm->eoa_id == 5))) {
            $mje_id = $this->mensaje();
        }

        switch ($this->prm->eoa_id) {
            case 3:
                $observacion = 'Rechazado - Mensaje: ' . $mje_id;
                $this->mixScript('$("#myModal").modal().remove();');
                break;
            case 2:
                $observacion = 'Enviado a Revision';
                break;
            case 5:
                $observacion = 'Validado por ' . (($this->ssc_id == 2) ? 'Planeación' : 'Presupuesto');
                break;
            case 4:
                $observacion = 'Validado por ' . (($this->ssc_id == 2) ? 'Planeación' : 'Presupuesto');
                break;
        }
        $this->prm->mensaje = $observacion;

        if ($this->prm->eoa_id == 4) {
            //Registramos la metas que se encuentran en la vinculacion obra mir
            $this->conectar();
            $this->setSql('SELECT pii_id,epg_id FROM vin_ObraMir
                WHERE epg_id = :epg AND (pii_id,epg_id) not in(
                SELECT pii_id,epg_id FROM cat_MetaActividad
                WHERE epg_id = :epg)
            ');
            $this->consultar(['epg' => $epg_id]);
            $trimestreMeta = (int)$this->trimestreActual();
            while ($r = $this->extraer()) {
                $params = [':observacion' => $observacion,':ip' => $this->ip, ':usr' => $this->usr_id];
                $con = new AccesoDatos();
                $con->conectar();
                $con->setSql('
                    SET @bit_observacion = :observacion, 
                    @usr_ip = INET_ATON(:ip), @usr_id = :usr;');
                $con->consultar($params);
                $con->setSql('CALL sp_RegistroMetaActividad' . APP_EJERCICIO . '(?, ?, ?, ?, "I",?, @usr_ip, @usr_id);');
                $con->consultar([$this->prm->gpo_id,$this->prm->ur_id,$r['pii_id'],$epg_id,$trimestreMeta]);
                $con->setSql('SELECT @banderaPoa, @mensajePoa;');
                $con->consultar();
                $res = $con->extraer();
                if (!$res['@banderaPoa']) {
                    $this->mixScript('
                        notify("' . $res['@mensajePoa'] . '","'
                            . ($res['@banderaPoa'] ? 'success' : 'error') . '")
                    ');
                    return false;
                }
            }

            ObraActividad::abc($this->prm);

            if (empty($this->prm->regresar)) {
                $this->mixScript('notify("' . $observacion . '","warning");');
                if ($this->prm->eoa_id == 3) {
                    $this->prm->eoa_id = null;
                    $this->llenaTablaActividad();
                } else {
                    $this->metodo = 'mainBack';
                    $this->main();
                }
            } else {
                $this->mixScript($this->prm->regresar);
            }

        } else {
            ObraActividad::abc($this->prm);

            if (empty($this->prm->regresar)) {
                $this->mixScript('notify("' . $observacion . '","warning");');
                if ($this->prm->eoa_id == 3) {
                    $this->prm->eoa_id = null;
                    $this->llenaTablaActividad();
                } else {
                    $this->metodo = 'mainBack';
                    $this->main();
                }
            } else {
                $this->mixScript($this->prm->regresar);
            }
        }


    }

    private function verificaModificacionMetas($epg, $accion)
    {
        $sinRegistrosEnFolios = true;
        $mensaje = '';
        //Verifica si tiene adecuacion de metas
        $this->conectar();
        $this->setSql('
            SELECT ' . BD_SINPRES . '.fn_tableExists("scp_DetalleModificacion","' . APP_EJERCICIO . '") AS existe;
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
                $sinRegistrosEnFolios = false;
                $mensaje = 'No es posible ' . $accion . ', hay folios de Adecuaciones Presupuestales de la Actividad';
            }
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
                $sinRegistrosEnFolios = false;
                $mensaje = 'No es posible ' . $accion . ', hay modificación de metas en el Administrador de Adecuacion de Metas';
            }

        }

        $valida = ['sinRegistros' => $sinRegistrosEnFolios, 'mensaje' => $mensaje];

        return $valida;
    }

    private function verificaCosteo($epg)
    {
        $existeCosteo = false;
        $this->conectar();
        $this->setSql('
            SELECT ' . BD_SINPRES . '.fn_tableExists("presupuesto","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe2, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3;
            ');
        $this->consultar();
        $tabla = $this->extraer();
        if ($tabla['existe'] == 1 && $tabla['existe2'] == 1 && $tabla['existe3'] == 1) {
            $params = [':epg' => $epg * 1];
            $this->setSql('
                SELECT count(gpo_id) AS registros FROM poa_Alimentacion
                WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM poa_Arrendamiento
                WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM poa_OtrasPartidas
                WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM poa_SeguroFianza
                WHERE epg_id = :epg
                UNION SELECT  count(gpo_id) AS registros FROM poa_ServiciosPersonales
                WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM poa_ServicioVigilancia
                WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM poa_TelefoniaCelular
                WHERE epg_id = :epg
                -- UNION SELECT count(gpo_id) AS registros FROM poa_VehiculoMaquina
                -- WHERE epg_id = :epg
                -- UNION SELECT count(gpo_id) AS registros FROM cat_ParqueVehicular
                -- WHERE epg_id = :epg
                UNION SELECT count(gpo_id) AS registros FROM presupuestoOriginal
                WHERE epg_id = :epg AND pre_original > 0
                ORDER BY registros DESC
                ;
            ');
            $this->consultar($params);
            $this->desconectar();
            $totalRegistros = $this->extraer();
            $numRegistros = (int) $totalRegistros['registros'];
            if ($numRegistros > 0) {
                $existeCosteo = true;
            }
        }

        return $existeCosteo;
    }

    private function mensaje()
    {
        $ip = $this->ip;
        if (empty($this->prm->epg_id)) {
            $this->prm->gpo_id = $this->clasAdm_gpo;
            $this->prm->ur_id = $this->clasAdm_ur;
            $this->prm->prg_id = $this->clasAdm_prg;
            $this->prm->spr_id = $this->clasAdm_spr;
            $this->prm->py_id = $this->clasAdm_py;
        }
        $this->prm->abc = '';
        $actividad = new ObraActividad($this->prm);
        $mensaje = new stdClass();
        $mensaje->gpo_id = $this->gpo_id;
        $mensaje->ur_id = $this->ur_id;

        $mensaje->remitente = $this->usr_id;
        $mensaje->descripcion = $this->prm->mensaje;
        if (isset($this->prm->eoa_id)) {
            $mensaje->asunto = 'Motivo de rechazo';
        } else {
            $mensaje->asunto = 'Observación para la actividad';
            $this->mixScript('notify("Observación registrada","success");');
        }
        $a = $actividad->getObservacion();
        $b = $actividad->getDescripcion();
        $mensaje->respuesta = ($a > $b) ? $a : $b;
        $mensaje->fechaEnvio = date('Y/m/d H:i:s');
        $mensaje->eme_id = 1;
        $mensaje->abc = 'a';

        $mje_id = HistorialMensajes::abc($mensaje);

        $consulta = new AccesoDatos();
        $consulta->conectar();
        $params = [':ip' => $ip, ':usr' => $this->usr_id];
        $sql = 'SET @usr_ip =INET_ATON(:ip)';
        $sql .= ',@bit_observacion="SE AGREGA ' . (isset($this->prm->eoa_id) ? 'MENSAJE DE RECHAZO ' . $mje_id . '' : 'OBSERVACIÓN ' . $mje_id . '') . '"';
        $sql .= ',@usr_id = :usr ;';
        $consulta->setSql($sql);
        $consulta->consultar($params);

        $params = [
            ':descripcion' => $mje_id,
            ':eoa'         => $this->prm->eoa_id,
            ':grupo'       => $actividad->getGpo_id(),
            ':ur'          => $actividad->getUr_id(),
            ':programa'    => $actividad->getPrg_id(),
            ':supPrograma' => $actividad->getSpr_id(),
            ':proyecto'    => $actividad->getPy_id(),
            ':oa'          => $actividad->getOa_id(),
        ];
        $sql2 = '';
        if (isset($this->prm->eoa_id)) {
            $params[':eoa'] = $this->prm->eoa_id;
            $sql2 = '
                UPDATE cat_ObraActividad
                SET oa_descripcion = :descripcion, eoa_id= :eoa
                WHERE gpo_id = :grupo AND
                ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id = :oa ;';
        } else {
            $sql2 = '
                UPDATE cat_ObraActividad
                SET oa_observacion = :descripcion
                WHERE gpo_id = :grupo AND
                ur_id = :ur AND
                prg_id = :programa AND
                spr_id = :supPrograma AND
                py_id = :proyecto AND
                oa_id = :oa ;';
        }
        $consulta->setSql($sql2);
        $consulta->consultar($params);
        $consulta->desconectar();
        $this->prm->abc = 'c';
        return $mje_id;
    }

    private function verHistorial()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $mensaje = new HistorialMensajes();
        $mensaje->setMje_id($this->prm->mje_id);
        $mensaje->select();
        $historial = $mensaje->getHistorial();
        $mensajes = '';
        $remitente = '';
        $tipo = '';
        foreach ($historial as $mensaje) {
            $remitente = ($remitente == '') ? $mensaje->getMje_remitente() : $remitente;
            if ($remitente != $mensaje->getMje_remitente()) {
                $tipo = ($tipo == '') ? 'reply' : '';
            }
            if ($this->prm->tipo == 'observacion' || ($this->prm->tipo == 'rechazo' && $mensaje->getMje_asunto() == 'MOTIVO DE RECHAZO')) {
                $mensajes .= '
                    <div class="msg ' . $tipo . '">
                        <img src="img/user-2.png">
                        <div class="mensajes">
                            <h3><small>' . $mensaje->getMje_fechaEnvio() . '</small><label>' . $mensaje->getNombreRemitente() . '</label><span> - ' . $mensaje->getMje_asunto() . '</span></h3>
                            <p>' . $mensaje->getMje_descripcion() . '</p>
                        </div>
                    </div>
                ';
            }

            $remitente = $mensaje->getMje_remitente();
        }
        $this->mixStyle('
            .msg {
                position: relative;
                margin-bottom: 20px;
            }
            .msg>img {
                left: 26px;
                right: auto;
            }
            .msg.reply>img {
                right: 26px;
                left: auto;
            }
            .msg .mensajes {
                color: #707070;
                background: #fff;
                position: relative;
                padding: 15px;
                margin-left: 80px;
                border: 1px solid #ccc;
                border-radius: 3px;
                box-shadow: 0 2px 3px rgba(0,0,0,0.23);
            }
            .reply .mensajes {
                margin-left: 0;
                margin-right: 80px;
            }
            .msg .mensajes:after {
            display: block;
            position: absolute;
            height: 17px;
            width: 11px;
            left: -11px;
            top: 6px;
            content: "";
            background: url("img/content-arrow.png");
            }
            .reply .mensajes:after {
                left: auto;
                right: -11px;
                background: url("img/content-arrow-right.png");
            }
            .msg .mensajes h3, .msg .mensajes h3 label {
                font-size: 12px;
                color: #2e2e2e;
            }
            .msg .mensajes h3 {
                padding: 5px 0;
                padding-top: 0;
                border-bottom: 1px solid #d6d6d6;
            }
            .msg .mensajes h3 span{
                background: none !important;
                height:auto !important;
                float: none !important;
                width: auto !important;
            }
            .msg .mensajes h3 span, .msg .mensajes h3 small {
            font-size: 11px;
            font-weight: normal;
            color: #7a7a7a;
            }
            .msg .mensajes h3 small {
                float: right;
            }
            .msg .mensajes p {
                margin: 15px 0 0 0;
            }
            .msg>img {
                left: 26px;
                right: auto;
            }
            .msg>img {
                display: block;
                position: absolute;
                padding: 5px;
                right: 0;
                border: 1px solid #bdbdbd;
                border-radius: 3px;
            }
        ');

        $this->mixHtml('
            <div id="myModalMeta" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">Historial ' . ($this->prm->tipo == 'rechazo' ? 'Rechazo' : 'Observación') . '</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                        <div class="row">' . $mensajes . '</div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn cerrar btn-outline-secondary me-2" data-dismiss="modal">cerrar</button>
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myModalMeta").modal().show();
            $(".cerrar").click(function(){ $("#myModalMeta").modal().remove(); });
        ');
    }

    private function selectEncProgramatico()
    {
        if (!empty($this->prm->epg_id)) {
            $params = [':epg' => $this->prm->epg_id];
            $this->setSql('
                SELECT  gpo_id,ur_id,prg_id,spr_id,py_id,oa_id,eoa_id
                FROM enc_Programatico
                INNER JOIN cat_ObraActividad  USING ( gpo_id,ur_id,prg_id,spr_id,py_id,oa_id )
                WHERE  epg_id = :epg;
            ');
            $this->conectar();
            $this->consultar($params);
            $this->desconectar();
            $datos = $this->extraer();
            if ($this->getNumeroRegistros() > 0) {
                $this->prm->gpo_id = $datos['gpo_id'];
                $this->prm->ur_id = $datos['ur_id'];
                $this->prm->prg_id = $datos['prg_id'];
                $this->prm->spr_id = $datos['spr_id'];
                $this->prm->py_id = $datos['py_id'];
                $this->prm->oa_id = $datos['oa_id'];
                $this->prm->eoa_id = $datos['eoa_id'];
            }
        } else {
            $this->prm->gpo_id = $this->clasAdm_gpo;
            $this->prm->ur_id = $this->clasAdm_ur;
            $this->prm->prg_id = $this->clasAdm_prg;
            $this->prm->spr_id = $this->clasAdm_spr;
            $this->prm->py_id = $this->clasAdm_py;
            $this->prm->eoa_id = 1;
        }
    }

    private function registrarBitacora($observacion)
    {
        $this->prm->observacion = 'Actividad ' . $observacion;
        $this->prm->abc = 'a';
        BitObraActividad::abc($this->prm);
    }

    public static function datosActividad($actividad, $parametros)
    {
        //Definimos a qué pantalla regresaremos, dependiendo de cómo llegamos aquí
        $pantallaRetorno = 'poa/programacion/ControladorActividades';
        $pp = $parametros->pp;
        $comboprg = (isset($parametros->comboprg) ? $parametros->comboprg : '');
        $combosp = (isset($parametros->combosp) ? $parametros->combosp : '');
        if (isset($parametros->pp)) {
            if ($parametros->pp == 1) {
                $pantallaRetorno = 'mga/ControladorPrevisiones.php';
            }
        }

        $prm = (func_num_args() > 1) ? func_get_arg(1) : new stdClass();

        $filasCveFin = '';
        $filasIndicadores = '';
        $prmNewCveFin = '';
        $guardar = '';
        $regresar = '';
        $botonDel = '';
        $botonDelete = '';
        $botonClickValidarPes = '';
        $botonClickValidaEgresos = '';
        $botonClicHistorial = '';
        $botonHistorial = '';
        $botonClicHistorial2 = '';
        $botonHistorial2 = '';
        $botonClicGuardaObs = '';
        $botonGuardarObservacion = '';
        $validarEgresos = '';
        $guardarPes = '';
        $validaPes = '';
        $epg = $actividad->getEpg();
        $ug = new stdClass();
        $catFun = new stdClass();
        $da = new WebResponse();
        $nombre = new stdClass();
        $actividadMIR = new stdClass();

        $tipo = new stdClass();
        $tipo->sql = '';
        $hombres = new stdClass();
        $mujeres = new stdClass();
        $total = new stdClass();
        $ue = new stdClass();
        $ue->horizontal = 3;
        $ue->ue_id = $actividad->getUe_id();
        $lectura = (
            (($actividad->getToa_id() != '' && $actividad->getToa_id() != 1) || $_SESSION[_S]['nvj_id'] > 2) ||
            (
                (!is_null($_SESSION[_S]['clasAdm-py']) && !empty($_SESSION[_S]['clasAdm-py']) && !isset($_SESSION[_S]['prm']['A']['2']['2'])) ||
                (!isset($_SESSION[_S]['prm']['A']['2']['1']) && is_null($_SESSION[_S]['clasAdm-py']))
            ) || ($_SESSION[_S]['ssc_id'] == 2) || $_SESSION[_S]['nvj_id'] >= 3 ||
            (
                (
                    $actividad->getEoa_id() != 0 &&
                    $actividad->getEoa_id() != 1 &&
                    $actividad->getEoa_id() != 3
                )
            )
        );

        if ($pp == 1) {
            $lectura = false;
        }

        $params = [':epg' => $epg];
        $campoImporte = $_SESSION[_S]['nvj_id'] >= 5 && $_SESSION[_S]['ssc_id'] == 2 && $_SESSION[_S]['dir_id'] == 4;
        if ($epg * 1 != 0) {

            $da->setSql('SELECT ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3;');
            $da->conectar();
            $da->consultar();
            $d = $da->extraer();

            if ($d['existe'] == 1 && $d['existe3'] == 1) {
                $da->setSql('
                    SELECT count(gpo_id) as registros FROM poa_Alimentacion      WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_Arrendamiento      WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_OtrasPartidas      WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_SeguroFianza       WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_ServiciosPersonales    WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_ServicioVigilancia     WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM poa_TelefoniaCelular   WHERE epg_id = :epg
                    -- UNION SELECT count(gpo_id) as registros FROM poa_VehiculoMaquina    WHERE epg_id = :epg
                    -- UNION SELECT count(gpo_id) as registros FROM cat_ParqueVehicular    WHERE epg_id = :epg
                    UNION SELECT count(gpo_id) as registros FROM presupuesto     WHERE epg_id = :epg
                    ORDER BY registros DESC;
                ');
                $da->conectar();
                $da->consultar($params);
                $totalRegistros = $da->extraer();
                $numeroRegistro = $totalRegistros['registros'];
                $da->desconectar();

                $con = new AccesoDatos();
                $con->setSql('SELECT ' . BD_SINPRES . '.fn_tableExists("poa_Alimentacion","' . BD_CTRL . '") AS existe, ' . BD_SINPRES . '.fn_tableExists("poa_OtrasPartidas","' . BD_CTRL . '") AS existe3;');
                $con->conectar();
                $con->consultar();
                $d = $con->extraer();
                if ($d['existe'] == 1 && $d['existe3'] == 1) {
                    $eliminarSi = (($numeroRegistro == 0) ? true : false);
                } else {
                    $eliminarSi = true;
                }

                $con->desconectar();
            } else {
                $eliminarSi = true;
            }
        }

        $da->setSql('
            SELECT c.ecf_id, CONCAT(d.cff_id, d.fte_id, d.sft_id, d.ori_id, d.sor_id, d.cfi_id, d.cfi_anio) cveFin ,
            d.cfi_nombre, b.ore_idObra, b.ore_oficioAut
            FROM poa_OrigenRecurso AS b
            JOIN enc_ClaveFinanciamiento AS c USING (ecf_id)
            JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' as d USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            WHERE b.epg_id = :epg
            GROUP BY b.epg_id, c.ecf_id,d.cfi_nombre,b.ore_idObra,b.ore_oficioAut;
        ');

        $da->conectar();
        $da->consultar($params);
        $da->desconectar();

        while ($r = $da->extraer()) {
            $boton = new stdClass();
            $boton->title = 'Eliminar clave';
            $boton->id = 'cve_' . $r['ecf_id'];
            $boton->color = 'rojo';
            $boton->icon = 'trash-can';
            $boton->click = '
                alertify.confirm(
                    "<h5> ELIMINAR CLAVE DE FINANCIAMIENTO </h5>",
                    "¿Confirma que desea eliminar la clave de financiamiento?",
                    function(){
                        comunica("poa/programacion/ControladorActividades.php",{
                            metodoCtrlAct:"eliminaCveFin",
                            epg_id:' . ($epg * 1) . ',
                            ecf_id:' . ($r['ecf_id'] * 1) . '
                            });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            ';
            $boton = ($lectura) ? '' : $da->ponBoton($boton);
            if ($campoImporte) {
                $boton = '$ ' . number_format($r['ecf_id'], 2);
            }

            $filasCveFin .= '<tr id="cvefinFila-' . ($r['ecf_id'] * 1) . '"><td>' . $r['cveFin'] . '</td><td>' . $r['cfi_nombre'] . '</td>' . (($actividad->getToa_id() == 2) ? '<td>' . $r['ore_oficioAut'] . '</td><td>' . $r['ore_idObra'] . '</td>' : '') . '<td>' . $boton . '</td>';
        }

        $botonClickValidar = $botonClickRechazar = $clickEliminarAct = $clickRegresarAct = '';

        if ($lectura) {
            $catFun->lectura = true;
            $tipo->sql = 'WHERE toa_id = :toa;';
            $tipo->params = [':toa' => $actividad->getToa_id()];
            $actividadMIR->readonly = true;
            $hombres->readonly = true;
            $mujeres->readonly = true;
            $total->readonly = true;
            $botonCveFin = '';
            $ug->lectura = true;
            $ue->lectura = true;
        } else {
            $botonCveFin = new stdClass();
            $botonCveFin->id = 'cveFinPlus';
            $botonCveFin->color = 'azul';
            $botonCveFin->icon = 'plus';
            $botonCveFin->title = 'Agregar clave';
            $botonCveFin->class = 'me-2';
            $botonCveFin->click = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"muestraCveFin"' . $prmNewCveFin . ',epg_id:' . ($epg * 1) . '});';
            $botonCveFin = $da->ponBoton($botonCveFin);

            if ($epg * 1 != 0) {
                $botonDel = new stdClass();
                $botonDel->value = ' Eliminar';
                $botonDel->id = 'eliminaActividad';
                $botonDel->color = 'rojo';
                $botonDel->icon = 'trash';
                $botonDel->class = 'me-2';
                $callbackComunica = (empty($prm->regresar)) ? 'console.info("Actividad Eliminada");' : $prm->regresar;

                $queEliminaActividad = '¿Confirma que desar eliminar la actividad ' . $actividad->getOa_id() . '?';
                $clickEliminarAct = '
                    alertify.confirm(
                        "<h5> ELIMINAR ACTIVIDAD </h5>",
                        "' . $queEliminaActividad . '",
                        function(){
                            comunica("poa/programacion/ControladorActividades.php",{
                                    metodoCtrlAct:"abcActividad",
                                    pp: ' . $pp . ',
                                    epg_id:' . ($epg * 1) . ',
                                    abc:"b" ' . ((empty($prm->regresar)) ? '' : ',noMain:true') . '
                            });
                                ' . $callbackComunica . '
                        },
                        function(){},
                    ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});

                ';
                $botonDelete = (($eliminarSi) ? '<button id="eliminaActividad" type="button" class="btn btn-outline-danger btn-sm me-1" title="Eliminar"><i class="fa-solid fa-trash me-2"></i>Eliminar</button>' : '');
            }

            $guardar = '<button id="guardaActividad" type="submit" form="datosGenerales" class="btn btn-outline-success me-1" title="Guardar"><i class="fa-solid fa-floppy-disk me-2"></i>Guardar</button>';
        }

        $boton = new stdClass();
        $boton->value = ' Regresar';
        $boton->icon = 'circle-left';
        $boton->id = 'regresarAct';

        //FUNCIONALIDAD PARA PANTALLA DE PREVISIONES
        if ($pp == 1) {
            $clickRegresarAct = 'comunica("' . $pantallaRetorno . '",{metodo:"ponTablaActividades", prg_id:' . $comboprg . ',spr_id:' . $combosp . ' });';
        } else {
            $clickRegresarAct = (empty($prm->regresar)) ? 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"mainBack"});' : $prm->regresar;
        }
        $regresar = '<button id="regresarAct" type="button" class="btn btn-outline-secondary me-1" title="Regresar"><i class="fa fa-circle-left me-2"></i>Regresar</button> ';

        //Si se esta agregando/modificando desde el Modulo de Alta de Previsiones debe permitir editar el nombre de la Actividad
        if ($pp != 1) {
            //Verifica si es usuario de presupuesto o planeacion y/o el estado de la obra actividad, para bloquear campos
            if ((!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) ||
                ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2)
            ) {
                $nombre->disabled = true;
                $hombres->disabled = true;
                $mujeres->disabled = true;
            }
        }

        $nombre->id = 'nombre';
        $nombre->label = 'Nombre';
        $nombre->maxlength = 600;
        $nombre->required = true;
        $nombre->placeholder = 'Escriba la descripcion';
        $nombre->text = $actividad->getNombre();
        $nombre = $da->ponTextarea($nombre);

        $and = '';
        $params = ['prgs' => $_SESSION[_S]['clasAdm-prg'], 'sprs' => $_SESSION[_S]['clasAdm-spr']];
        if ($lectura && $actividad->getPdi_id($epg) != '') {
            $params['pdi'] = $actividad->getPdi_id($epg);
            $and = ' AND pdi_id = :pdi ';
        } elseif ($actividad->getPdi_id($epg) != '') {
            $params['pdi'] = $actividad->getPdi_id($epg);
            $and = ' AND ((vp.sts_id = 1 AND e.sts_id = 1 ) OR (pdi_id = :pdi)) ';
        } else {
            $and = ' AND vp.sts_id = 1 AND e.sts_id = 1 ';
        }

        $actividadMIR->id = 'pdi_id';
        $actividadMIR->label = 'Actividad MIR';
        $actividadMIR->class = 'mb-4';
        $actividadMIR->selecciona = $actividad->getPdi_id($epg);
        $actividadMIR->sql = '
            SELECT
                pdi_id,pdi_mir
            FROM cat_Subprogramas
                INNER JOIN enc_UrSubprogramas es USING (prgs_id,sprs_id)
                ' . (isset($_SESSION[_S]['clasAdm-prg']) && $_SESSION[_S]['clasAdm-prg'] == 0 ? 'LEFT' : 'INNER') . ' JOIN vin_UrProgramaMir USING(prgs_id)
                INNER JOIN vin_ProductoFinalProductoIntermedio vp USING (pdf_id)
                INNER JOIN cat_ProductoIntermedio USING (pdi_id)
                INNER JOIN vin_ProductoIntermedioIndicador e USING (pdf_id,pdi_id)
            WHERE prgs_id = :prgs
                AND es.sprs_id = :sprs
                ' . $and . '
            GROUP BY pdf_id,pdi_id;
        ';
        $actividadMIR->params = $params;

        $botonMetas = new stdClass();
        $botonMetas->id = 'botonMetas';
        $botonMetas->color = 'azul';
        $botonMetas->icon = 'plus';
        $botonMetas->title = 'Agregar Indicador';
        $botonMetas->type = 'button';
        $botonMetas->class = 'me-2';
        $botonMetas->click = '
            if($("#pdi_id").val()!=""){
                comunica("poa/programacion/ControladorActividades.php",{
                    metodoCtrlAct:"metas",
                    ' . (($epg * 1 != 0) ? 'epg_id:"' . $epg . '"' : 'epg_id:0') . ',
                    pdi_id:$("#pdi_id").find("option:selected").val(),
                    gpo_id:"' . $_SESSION[_S]['clasAdm-gpo'] . '",
                    ur_id:"' . $_SESSION[_S]['clasAdm-ur'] . '",
                    prgs_id:"' . $_SESSION[_S]['clasAdm-prg'] . '",
                    sprs_id:"' . $_SESSION[_S]['clasAdm-spr'] . '",
                    lectura:' . (($lectura) ? $lectura : 0) . '
                });
            }else{
                notify("Favor de seleccionar Actividad MIR","error");
            }
        ';

        $seleccion = '';

        $tipo->id = 'toa_id';
        $tipo->name = 'toa_id';

        //SI ES PROYECTO, ENTONCES ES DE PLANEACIÓN SINO DE EGRESOS
        if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] != 00) {
            $tipo->sql = 'SELECT  toa_id, toa_nombre
                FROM(
                    SELECT 1 toa_id,"SUB. EGRESOS" toa_nombre
                    UNION
                    SELECT 2 toa_id,"SUB. PLANEACIÓN" toa_nombre
                )noTipo ' . $tipo->sql;
            $seleccion = 2;
            $tipo->disabled = true;
        } elseif (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] == 00) {
            $tipo->sql = 'SELECT  toa_id, toa_nombre
                FROM(
                    SELECT 1 toa_id,"SUB. PRESUPUESTO" toa_nombre
                    ' . (($pp == 1) ? ' UNION SELECT 2 toa_id,"SUB. PLANEACIÓN" toa_nombre ' : '') . '
                )noTipo ' . $tipo->sql;
        }

        $seleccion = ($pp == 1) ? 1 : $seleccion;

        #TIPO DE PROYECTO O ACTIVIDAD 1 - GASTOS OPERACIÓN, 2 INVERSIÓN
        $tipo->required = true;
        $tipo->camposText = 'toa_nombre';
        $tipo->selecciona = (($actividad->getToa_id() != '') ? $actividad->getToa_id() : $seleccion);
        $tipo->enDiv = true;
        $tipo = $da->ponRadio($tipo);

        $hombres->id = 'hombres';
        $hombres->formato = 'entero';
        $hombres->required = true;
        $hombres->label = 'Hombres';
        $hombres->maxlength = 10;
        $hombres->enDiv = true;
        $hombres->value = $actividad->getHombres() * 1;
        $hombres->blur = '
            $("#total").val( ($("#mujeres").val() *1 ) + ( $(this).val() * 1) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $hombres = $da->ponInput($hombres);
        $mujeres->id = 'mujeres';
        $mujeres->formato = 'entero';
        $mujeres->required = true;
        $mujeres->label = 'Mujeres';
        $mujeres->maxlength = 10;
        $mujeres->enDiv = true;
        $mujeres->value = $actividad->getMujeres() * 1;
        $mujeres->blur = '
            $("#total").val( ( $("#hombres").val() *1 ) + ( $(this).val() * 1 ) );
            if($("#total").val() * 1 == 0 )
            $("#total").removeAttr("readonly");
            else
            $("#total").attr("readonly",true);
        ';
        $mujeres = $da->ponInput($mujeres);
        $total->id = 'total';
        $total->formato = 'entero';
        $total->required = true;
        $total->label = 'Total';
        $total->enDiv = true;
        $total->horizontal = 3;
        $total->value = $actividad->getTotal() * 1;
        if ($total->value == 0) {
            $total->value = ($actividad->getMujeres() * 1) + ($actividad->getHombres() * 1);
        }

        $total->blur = '
            if( $(this).val() * 1 == 0 ){
                $("#mujeres,#hombres").removeAttr("readonly");
            }else if($("#mujeres").val()!=0 || $("#hombres").val()!=0){
                if($("#total").val()!= ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1)){
                    $("#mujeres").val(0);
                    $("#hombres").val(0);
                    $("#mujeres,#hombres").attr("readonly",true);
                }else{
                    $("#total").val( ($("#mujeres").val() *1 ) + ( $("#hombres").val() * 1) );
                    $("#total").attr("readonly",true);
                    $("#mujeres,#hombres").removeAttr("readonly");
                }
            }else{
                $("#mujeres").val(0);
                $("#hombres").val(0);
                $("#mujeres,#hombres").attr("readonly",true);
            }
            ';
        $total = $da->ponInput($total);
        $catFun->fin_id = $actividad->getFin_id();
        $catFun->fun_id = $actividad->getFun_id();
        $catFun->sfu_id = $actividad->getSfu_id();

        #LA UBICACIÓN GEOGRAFICA PREDETERMINADA PARA LAS ACTIVIDADES DE PREVISIÓN ES : ESTATAL
        if ($pp == 1) {
            $da->setSql('SELECT eug_id FROM enc_UbicacionGeografica WHERE reg_id = 9;');
            $da->conectar();
            $da->consultar();
            $ubica = $da->extraer();
            $da->desconectar();
            $ug->eug_id = $ubica['eug_id'];
            $ug->lectura = true;
        } else {
            $ug->eug_id = $actividad->getEug_id();
        }

        $datoEpg_id = (($epg * 1 != 0) ? ',epg_id:' . ($epg * 1) . '' : '');
        $filasPoliticasT = '';

        #POLITICAS TRANSVERSALES
        $botonPedt = new stdClass();
        $botonPedt->id = 'botonPedPlus';
        $botonPedt->color = 'azul';
        $botonPedt->icon = 'plus';
        $botonPedt->title = 'Agregar';
        $botonPedt->class = 'me-2';
        $botonPedt->click = 'comunica("poa/programacion/ControladorActividades.php",{ metodoCtrlAct:"muestraPedTransversal"' . $datoEpg_id . ' });';

        $botonPedTrans = '';

        #PLAN INSTITUCIONAL
        $botonPI = new stdClass();
        $botonPI->id = 'botonPIPlus';
        $botonPI->color = 'azul';
        $botonPI->icon = 'plus';
        $botonPI->title = 'Agregar';
        $botonPI->class = 'me-2';
        $botonPI->click = 'comunica("poa/programacion/ControladorActividades.php",{ metodoCtrlAct:"muestraPlanInstitucional"' . $datoEpg_id . ' });';

        $filasPlanI = '';
        $botonPlanInsticional = '';

        #PES
        $pes = new stdClass();
        #PED
        $ped = new stdClass();
        $ped->cargaEncuadres = true;
        #PROGRAMA PRESUPUESTARIO
        $prpr = new stdClass();
        #PLAN ESPECIAL
        $pep = new stdClass();
        $informativo = false;
        $botonEliminar = '';
        $actMir = '';
        $tablaIndicadores = '';
        if ($_SESSION[_S]['clasAdm-prg'] >= 100 and $_SESSION[_S]['clasAdm-prg'] <= 899) { //100-899 Programas orientados a resultados
            if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] == 00) {
                $actMir = '<legend>Vinculación EP-MIR</legend>';
                $actMir .= $da->ponCombo($actividadMIR);
                if (!$lectura) {
                    $actMir .= $da->ponBoton($botonMetas);
                }
                $tablaIndicadores = '
                    Indicadores
                    <table id ="tbl_indicadores" class="table table-striped">
                        <thead>
                            <tr>
                            <th>Ver</th>'
                            . ($_SESSION[_S]['nvj_id'] <= 2 ? '<th>Eliminar</th>' : '') . '
                            <th>Indicador</th>
                            <th><center>Unidad de<br>Medida<br>Indicador</center></th>
                            <th>Tipo Valor</th>
                            <th><center>Unidad de<br>Medida<br>Variable</center></th>';
            }
            #Se mostrará de manera informativa los encuadres vinculados a nivel programa, y unicamente permitirá complementar información adicional.
            $params = [
                ':programa'    => $_SESSION[_S]['clasAdm-prg'],
                ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
            ];
            $da->setSql('
                SELECT epo_id,spe_id,obp_id,ple_id,ope_id,efn_id,epp_id,epods_id
                FROM vin_Programa
                INNER JOIN cat_Subprogramas USING (prgs_id)
                WHERE prgs_id = :programa
                AND sprs_id= :supPrograma
            ');

            $da->getSql();
            $da->conectar();
            $da->consultar($params);
            $datos = $da->extraer();
            $da->desconectar();
            $pd = new WebResponse();

            if ($da->getNumeroRegistros() > 0) {
                $informativo = true;
                $ped->epods_id = $datos['epods_id'];
                $pes->spe_id = $datos['spe_id'];
                $pes->obp_id = $datos['obp_id'];
                $pes->sectorDisabled = true;
                $pes->objetivoDisabled = true;
                $pep->ple_id = $datos['ple_id'];
                $pep->ope_id = $datos['ope_id'];
                $pep->planDisabled = true;
                $pep->objetivoDisabled = true;
                $catFun->efn_id = $datos['efn_id'];
                $prpr->epp_id = $datos['epp_id'];
                $params = [':epo' => $datos['epo_id']];
                $pd->setSql('
                    SELECT eje_id, tem_id, obj_id FROM enc_PedObjetivo
                    WHERE epo_id = :epo ;
                ');
                $pd->conectar();
                $pd->consultar($params);
                $regs = $pd->extraer();
                if ($pd->getNumeroRegistros() > 0) {
                    $ped->eje_id = $regs['eje_id'];
                    $ped->tem_id = $regs['tem_id'];
                    $ped->obj_id = $regs['obj_id'];
                }

                $prpr->lectura = $catFun->lectura = $ped->lecturapr = true;
                $pes->lectura = $pep->lectura = false;
            }

            $eps_id = '';
            if (!empty($epg)) {
                $params = [':epg' => $epg];
                $pd->setSql('
                    SELECT * FROM vin_Programatico
                    WHERE epg_id = :epg ;
                ');
                $pd->conectar();
                $pd->consultar($params);
                $regs = $pd->extraer();
                if ($pd->getNumeroRegistros() > 0) {
                    $ped->epe_id = $regs['epe_id'];
                    $pep->epc_id = $regs['epc_id'];
                    $eps_id = $regs['eps_id'];
                    $pes->eps_id = $eps_id;
                    $params = [':eps' => $regs['eps_id']];
                    $pd->setSql('
                        SELECT spe_id, obp_id FROM enc_Pes
                        WHERE eps_id = :eps ;
                    ');
                    $pd->conectar();
                    $pd->consultar($params);
                    $regs = $pd->extraer();
                    if ($pd->getNumeroRegistros() > 0) {
                        $pes->obp_id = $regs['obp_id'];
                    }
                }
                //Verifica el ssc_id de la session si es planeacion o presupuesto los combos solo serán de lectura
                if (!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) {
                    $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                } elseif ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 2) {
                    $ped->lectura = true;
                    $pes->eps_id = $eps_id;
                    $pes->lectura = true;
                }

                $params = [
                    ':grupo'       => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur'          => $_SESSION[_S]['clasAdm-ur'],
                    ':programa'    => $_SESSION[_S]['clasAdm-prg'],
                    ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
                    ':proyecto'    => $_SESSION[_S]['clasAdm-py'],
                    ':oa'          => $actividad->getOa_id(),
                ];
                $da->setSql('
                    SELECT elt.etla_id,lat.lact_id,lat.lact_Clave,vp.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vp using(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc elt using(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat using(lact_id)
                    WHERE ep.gpo_id = :grupo
                    AND ep.ur_id = :ur
                    AND ep.prg_id = :programa
                    AND ep.spr_id = :supPrograma
                    AND ep.py_id = :proyecto
                    AND ep.oa_id = :oa;
                ');

                $da->conectar();
                $da->consultar($params);

                $da->desconectar();
                while ($r = $da->extraer()) {
                    $boton = new stdClass();
                    $boton->title = 'Eliminar política transversal';
                    $boton->id = 'ept_' . $r['etla_id'];
                    $boton->color = 'rojo';
                    $boton->icon = 'trash-can';
                    $boton->click = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"eliminaPolitica",etla_id:' . $r['etla_id'] . ',epg_id:"' . $epg . '"}); ';
                    $botonEliminar = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($boton) : '');
                    $filasPoliticasT .= '<tr class="registro" id="filasPoliticasT-' . ($r['etla_id']) . '"><td>' . $r['lact_Clave'] . '</td><td>' . $r['ept_porcentaje'] . '</td><td>' . $botonEliminar . '</td>';
                }
                if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] == 00) {
                    $da->conectar();
                    //Obtenemos los indicadores de la meta o actividad
                    if ($actividad->getEoa_id() == 4) {
                        $da->setSql('
                            SELECT
                                pii_id,pdi_id,ind_id,
                                concat(ind_id, " - ",ind_nombre) as ind_nombre,
                                ctInd.umv_id,
                                ctv.tiv_nombre,
                                cumi.umi_nombre,
                                ctInd.umv_nombre,cs.pdf_id,gpo_id,
                                ur_id,prg_id,spr_id,
                                py_id,oa_id,pii_id AS pii,
                                gpo_id as gpo, ur_id as ur,
                                (SELECT
                                    macm_id
                                FROM cat_MetaActividadMir mm
                                WHERE mm.pii_id = pii
                                AND mm.gpo_id = gpo
                                AND mm.ur_id = ur) AS macm_id,
                                epg_id
                            FROM enc_Programatico enc
                                INNER JOIN cat_MetaActividad ma USING (epg_id)
                                INNER JOIN cat_Subprograma cs USING (gpo_id,ur_id,prg_id,spr_id)
                                INNER JOIN vin_ProductoIntermedioIndicador USING (pii_id)
                                INNER JOIN cat_Indicador i USING (ind_id)
                                INNER JOIN cat_TipoValor ctv USING (tiv_id)
                                INNER JOIN cat_UnidadMedidaIndicador cumi USING (umi_id)
                                INNER JOIN cat_UnidadMedidaVariable ctInd USING (umv_id)
                            WHERE enc.gpo_id = :grupo
                                AND enc.ur_id = :ur
                                AND prg_id = :programa
                                AND spr_id = :supPrograma
                                AND py_id = :proyecto
                                AND oa_id = :oa
                                AND i.nim_id = 4
                            GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
                            ORDER BY ma.usr_fecha             
                        ');
                    } else {
                        $da->setSql('
                            SELECT
                            ma.pii_id,pdi_id,ind_id,
                            concat(ind_id, " - ",ind_nombre) as ind_nombre,
                            ctInd.umv_id,
                            ctv.tiv_nombre,
                            cumi.umi_nombre,
                            ctInd.umv_nombre,cs.pdf_id,enc.gpo_id,
                            enc.ur_id,enc.prg_id,enc.spr_id,
                            py_id,oa_id, ma.pii_id AS pii,
                            enc.gpo_id as gpo, enc.ur_id as ur,
                            ma.macm_id
                        FROM enc_Programatico enc
                            INNER JOIN vin_ObraMir vom USING (epg_id)
                            INNER JOIN cat_MetaActividadMir ma USING (macm_id)
                            INNER JOIN cat_Subprograma cs ON cs.gpo_id = enc.gpo_id AND cs.ur_id = enc.ur_id AND cs.prg_id = enc.prg_id AND cs.spr_id = enc.spr_id
                            INNER JOIN vin_ProductoIntermedioIndicador vp ON vp.pii_id = ma.pii_id
                            INNER JOIN cat_Indicador i USING (ind_id)
                            INNER JOIN cat_TipoValor ctv USING (tiv_id)
                            INNER JOIN cat_UnidadMedidaIndicador cumi USING (umi_id)
                            INNER JOIN cat_UnidadMedidaVariable ctInd USING (umv_id)
                        WHERE enc.gpo_id = :grupo
                                AND enc.ur_id = :ur
                                AND enc.prg_id = :programa
                                AND enc.spr_id = :supPrograma
                                AND enc.py_id = :proyecto
                                AND enc.oa_id = :oa
                                AND i.nim_id = 4
                        GROUP BY pii_id,cs.pdf_id,ind_id,ind_id
                        ORDER BY ma.usr_fecha;           
                        ');
                    }

                    $da->consultar($params);
                    $da->desconectar();
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
                                    comunica("poa/programacion/ControladorActividades.php",{
                                        metodoCtrlAct:"eliminaMeta",
                                        pii_id:' . $r['pii_id'] . ',
                                        ind_id:' . $r['ind_id'] . ',
                                        gpo_id:' . $r['gpo_id'] . ',
                                        ur_id:' . $r['ur_id'] . ',
                                        prgs_id:' . $r['prg_id'] . ',
                                        sprs_id:' . $r['spr_id'] . ',
                                        epg_id:"' . $epg . '",
                                    });
                                },
                                function(){},
                            ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
                        ';
                        $boton = ($lectura) ? '' : $da->ponBoton($boton);

                        $boton2 = new stdClass();
                        $boton2->value = 'ver';
                        $boton2->id = 'edit_' . $r['ind_id'];
                        $boton2->class = 'btn-xs btn-success';
                        $boton2->click = '
                                comunica("poa/programacion/ControladorActividades.php",{
                                    metodoCtrlAct:"metas",
                                    nim_id: 4,
                                    ind_id:' . $r['ind_id'] . ',
                                    eri_id:' . $r['pii_id'] . ',
                                    lectura: 1,
                                    pdf_id:' . $r['pdf_id'] . ',
                                    pdi_id:' . $r['pdi_id'] . ',
                                    umv_id:' . $r['umv_id'] . ',
                                    tiv_nombre:"' . $r['tiv_nombre'] . '",
                                    pii_id:' . $r['pii_id'] . ',
                                    epg_id:"' . $epg . '",
                                    gpo_id:' . $r['gpo_id'] . ',
                                    ur_id:' . $r['ur_id'] . ',
                                    prgs_id:' . $r['prg_id'] . ',
                                    sprs_id:' . $r['spr_id'] . ',
                                    py_id:' . $r['py_id'] . ',
                                    oa_id:' . $r['oa_id'] . ',
                                });';
                        $boton2 = $da->ponBoton($boton2);
                        $filasIndicadores .= '
                        <tr id="indicador' . $r['ind_id'] . '">
                        <td>' . $boton2 . '</td>';
                        if ($_SESSION[_S]['nvj_id'] <= 2) {
                            $filasIndicadores .= '<td>' . $boton . '</td>';
                        }

                        $filasIndicadores .= '<td>' . $r['ind_nombre']
                            . '</td><center><td>' . $r['umi_nombre']
                            . '</center></td><td><center>' . $r['tiv_nombre']
                            . '</center></td><td>' . $r['umv_nombre'] . '</td>';
                        $filasIndicadores .= '
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['ind_id'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['umv_nombre'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['tiv_nombre'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['ind_nombre'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['gpo_id'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['ur_id'] . '" type="hidden"></input>
                        <input name="ind' . $r['ind_id'] . '[]" value="' . $r['macm_id'] . '" type="hidden"></input>
                        ';
                        $filasIndicadores .= '<input name="ind' . $r['ind_id'] . '[]" value="' . $r['pdi_id'] . '" type="hidden"></input>
                        </tr>';
                    }

                    $tablaIndicadores .= '<tbody id="filasIndicadores">' . $filasIndicadores . '</tbody>
                    </table>';
                }
            } else {
                if (!is_null($_SESSION[_S]['clasAdm-py']) && $_SESSION[_S]['clasAdm-py'] == 00) {
                    $tablaIndicadores .= '<tbody id="filasIndicadores"></tbody>
                    </table>';
                }
            }
            $botonPedTrans = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura)) ? $da->ponBoton($botonPedt) : '');

            //Obtiene el Plan Institucional del Programa para mostrarlo
            $da->setSql('
                    SELECT epi_id,pi_nombre, Descripcion
                    FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id)
                    JOIN cat_PlanInstitucional USING (pi_id)
                    JOIN vin_ProgramaPI USING (epi_id)
                    WHERE prgs_id = ?;
                ');
            $params = [$_SESSION[_S]['clasAdm-prg']];
            $da->conectar();
            $da->consultar($params);
            $da->desconectar();

            while ($r = $da->extraer()) {
                $filasPlanI .= '<tr class="registro" id="filasPin-' . $r['epi_id'] . '"><td>' . $r['pi_nombre'] . '</td><td>' . $r['Descripcion'] . '</td><td></td>';
            }
            $botonPlanInsticional = '';
        } else { #900-999 Gastos no asignables a programas.
            $hombres = $mujeres = $total = '';

            // Si es de programas de actividades centrales de gestión o gastos no asignables a programas no hereda la vinvulación del programa a la actividad.

            if (!empty($epg)) {
                $params = [
                    ':grupo'       => $_SESSION[_S]['clasAdm-gpo'],
                    ':ur'          => $_SESSION[_S]['clasAdm-ur'],
                    ':programa'    => $_SESSION[_S]['clasAdm-prg'],
                    ':subPrograma' => $_SESSION[_S]['clasAdm-spr'],
                    ':proyecto'    => $_SESSION[_S]['clasAdm-py'],
                    ':oa'          => $actividad->getOa_id(),
                ];
                $da->setSql('
                    SELECT * FROM enc_Programatico
                    INNER JOIN vin_Programatico using(epg_id)
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND prg_id = :programa
                    AND spr_id = :subPrograma
                    AND py_id = :proyecto
                    AND oa_id = :oa ;
                ');

                $da->conectar();
                $da->consultar($params);
                $datos = $da->extraer();
                if ($da->getNumeroRegistros() > 0) {
                    //Verifica el s22ssc de la session si es planeacion o presupuesto los combos solo serán de lectura
                    if (!empty($_SESSION[_S]['ssc_id']) && ($_SESSION[_S]['ssc_id']) == 2 || ($_SESSION[_S]['ssc_id']) == 3) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                    } elseif ($actividad->getEoa_id() == 4 || $actividad->getEoa_id() == 5 || $actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 2) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = true;
                    }

                    //Si se esta agregando/modificando desde el Modulo de Alta de Previsiones debe permitir cambiar los combos
                    if ($pp == 1) {
                        $prpr->lectura = $ped->lectura = $pes->lectura = $pep->lectura = $catFun->lectura = false;
                    }

                    $ped->epe_id = $datos['epe_id'];
                    $ped->epods_id = $datos['epods_id'];
                    $pes->eps_id = $datos['eps_id'];
                    $pep->epc_id = $datos['epc_id'];
                    $catFun->efn_id = $datos['efn_id'];
                    $prpr->epp_id = $datos['epp_id'];
                }

                $da->setSql('
                    SELECT elt.etla_id,lat.lact_id,lat.lact_Clave,vp.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vp using(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc elt using(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat using(lact_id)
                    WHERE ep.gpo_id = :grupo
                    AND ep.ur_id = :ur
                    AND ep.prg_id = :programa
                    AND ep.spr_id = :subPrograma
                    AND ep.py_id = :proyecto
                    AND ep.oa_id = :oa ;
                ');
                $da->conectar();
                $da->consultar($params);

                $da->desconectar();
                while ($r = $da->extraer()) {
                    $boton = new stdClass();
                    $boton->title = 'Eliminar política transversal';
                    $boton->id = 'ept_' . $r['etla_id'];
                    $boton->color = 'rojo';
                    $boton->icon = 'trash-can';
                    $boton->click = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"eliminaPolitica",etla_id:' . $r['etla_id'] . ',epg_id:"' . $epg . '"}); ';
                    $botonEliminar = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($boton) : '');
                    $filasPoliticasT .= '<tr class="registro" id="filasPoliticasT-' . ($r['etla_id']) . '"><td>' . $r['lact_Clave'] . '</td><td>' . $r['ept_porcentaje'] . '</td><td>' . $botonEliminar . '</td>';
                }

                //Obtiene el Plan Institucional de la Actividad para mostrarlo
                $da->setSql('
                        SELECT epi_id,pi_nombre, Descripcion
                        FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id)
                        JOIN cat_PlanInstitucional USING (pi_id)
                        JOIN vin_ProgramaticoInstitucional USING (epi_id)
                        WHERE epg_id = ?;
                    ');
                $params = [$epg];
                $da->conectar();
                $da->consultar($params);
                $da->desconectar();

                while ($r = $da->extraer()) {
                    $botonEliminaPI = new stdClass();
                    $botonEliminaPI->title = 'Eliminar Plan  Institucional';
                    $botonEliminaPI->id = 'epin_' . $r['epi_id'];
                    $botonEliminaPI->color = 'rojo';
                    $botonEliminaPI->icon = 'trash-can';
                    $botonEliminaPI->click = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"eliminaPi",epg_id:"' . $epg . '",epi_id:"' . $r['epi_id'] . '"}); ';
                    $botonDeletePI = ((($_SESSION[_S]['nvj_id'] <= 2) && (!$lectura) && $actividad->getToa_id() != 2) ? $da->ponBoton($botonEliminaPI) : '');
                    $filasPlanI .= '<tr class="registro" id="filasPin-' . $r['epi_id'] . '"><td>' . $r['pi_nombre'] . '</td><td>' . $r['Descripcion'] . '</td><td>' . $botonDeletePI . '</td>';
                }

            }

            $botonPedTrans = ((($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2) && (!$lectura)) || ($prm->pp == 1) ? $da->ponBoton($botonPedt) : '');
            $botonPlanInsticional = ((($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2) && (!$lectura)) || ($prm->pp == 1) ? $da->ponBoton($botonPI) : '');
        }

        $actividadEspecial = '<div class="row col-md-9">
                    <legend>' . $botonPlanInsticional . 'Plan Institucional</legend>
                    <div id="modalPI"></div>
                        <table class="table table-striped"><thead><tr><th>Plan</th><th>Objetivo PI</th>' . ($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2 ? '<th>Eliminar</th>' : '') . '</thead><tbody id="filasPlanI">' . $filasPlanI . '</tbody>
                        </table>
                    </div>';

        $titulo = (isset($prm->es) ? 'Tipo de proyecto' : 'Tipo de Actividad');
        $da->mixHtml('
            <div class="row col-md-12">
                <div class="row col-md-12">
                    <div class="col-md-6 col-xs-6 mb-4">
                        <legend align="left">Datos Generales</legend>
                        ' . $titulo
            . $tipo
            . ClasificacionAdministrativa::clasAdminUe($ue)
            . $nombre . '
                    </div>
                    </div>
                    <div class="col-md-6 col-xs-6">
                    ' . $actividadEspecial . '
                </div>
            </div>
            <div class="row mb-4">
            <!--aqui iria la vinculación-->
            <div class="col-md-6 col-xs-6">
                <div class="col-md-' . (($campoImporte) ? '7' : '9') . ' col-xs-12 inline-block">
                    <legend>' . $botonCveFin . 'Claves de Financiamiento</legend>
                    <div id="modalClaveFinanciamiento"></div>
                    <table class="table table-striped"><thead><tr><th>Clave</th><th>Nombre</th>' .
            (($actividad->getToa_id() == 2) ? '<th>Oficio de Autorizacion</th><th>IDOBRA</th>' : '') .
            (($campoImporte) ? '<th>Importe' : '<th>Eliminar') .
            '</th></thead><tbody id="filasCvesFin">' . $filasCveFin . '</tbody></table>
                </div>
            </div>
            <div class="col-md-6 col-xs-6">
            ' . UbicacionGeografica::ponUbicacionGeografica($ug) . '
            </div>
            </div>
            <div class="row" ' .
            // Si es una actividad de previsiones, existirá pero no verán la Ubicación Geográfica
            // ( la dejamos porque es un campo obligatorio )
            (($pp == 1) ? ' style="display:none" ' : '')
            . '>
            </div>
            <button type="submit" name="guardaActividad2" id="guardaActividad2" form hidden="hidden"></button>
        ');

        $da->mixHtml(
            '<div class="row">' .
            '<div class="col-md-6">' .
            Ped::ponPed($ped) .
            '</div>
                <div class="col-md-6">' .
            Pep::ponPep($pep) .
            '</div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">' .
            Pes::ponPes($pes) .
            '</div>
                <div class="col-md-6">' .
            CatalogoFuncional::ponCatalogoFuncional($catFun) .
            '</div>
            </div>
            <div class="row">
                <div class="col-md-6">' .
            ProgramaPresupuestario::ponProgramaPres($prpr) .
            '</div>
                <div class="col-md-6 col-xs-6">
                    <div class="row col-md-9">
                    <legend>' . $botonPedTrans . 'Políticas Transversales</legend>
                    <div id="modalPedTransversal"></div>
                        <table class="table table-striped"><thead><tr><th>Línea de Acción</th><th>%</th>' . ($_SESSION[_S]['nvj_id'] <= 2 && $actividad->getToa_id() != 2 ? '<th>Eliminar</th>' : '') . '</thead><tbody id="filasPoliticasT">' . $filasPoliticasT . '</tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-xs-6"><br>
                    ' . $actMir . '
                    ' . $tablaIndicadores . '
                </div>
                <div class="col-md-6 col-xs-6">
                </div>
            </div>'
        );
        $mensajeRechazo = $observacion = '';
        $validar = '';
        $rechazar = $validarEgresos = '';

        //SECCIÓN DE VALIDACIÓN
        if ($pp != 1) {
            if ($_SESSION[_S]['nvj_id'] >= 3) {
                $mensaje = new HistorialMensajes();
                $mensaje->setMje_id($actividad->getObservacion());
                $mensaje->select();
                $observacion = new stdClass();
                $observacion->id = 'observaciones';
                $observacion->enDiv = true;
                $observacion->maxlength = 1000;
                $observacion->disabled = true;
                $observacion->texto = $mensaje->getMje_descripcion();
                $saveObservacion = '';
                if ($actividad->getEoa_id() != '' && ($actividad->getEoa_id() == 2 || $actividad->getEoa_id() == 5)) {
                    if (
                        $actividad->getToa_id() == 1 && $_SESSION[_S]['ssc_id'] == 3 && $actividad->getOa_id() != 0 &&
                        isset($_SESSION[_S]['prm']['A']['2']['2']['3'])
                    ) {
                        $boton = new stdClass();
                        $boton->id = 'validando';
                        $boton->color = 'verde';
                        $boton->icon = 'ok';
                        $boton->value = ' Validar Obra/Actividad ';
                        $botonClickValidar = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"validarOa",epg_id:' . ($epg * 1) . ' ' . ((empty($prm->regresar)) ? '' : ",regresar:'" . $prm->regresar . "'") . ' ' . (($actividad->getToa_id() == 1) ? ',eoa_id:4' : (($actividad->getToa_id() == 2) ? ",eoa_id:'5'" : '')) . '});';

                        $validar = '<button id="validando" type="button" class="btn btn-outline-success me-1" title=" Validar Obra/Actividad"><i class="fa fa-check me-2"></i>Validar Obra/Actividad</button>';

                        $boton = new stdClass();
                        $boton->id = 'rechazar';
                        $boton->color = 'rojo';
                        $boton->icon = 'remove';
                        $boton->value = ' Rechazar Obra/Actividad';
                        $botonClickRechazar = '
                        $("#divContenedorGeneralModal").html(
                            $("<div/>").css("display","block").attr("tabindex","-1").addClass("modal").attr("id","myModal").attr("data-bs-backdrop","static").attr("data-bs-keyboard","false").attr("aria-labelledby","staticBackdropLabel").attr("aria-hidden","false")
                                .html(
                                    $("<div/>").addClass("modal-dialog")
                                        .html(
                                            $("<div/>").addClass("modal-content")
                                                .html(
                                                    $("<div/>").addClass("modal-header")
                                                        .html( $("<h4/>").addClass("modal-title").text("Agregar motivo de rechazo") )
                                                        .append( $("<button/>").addClass("close cerrar btn btn-xs btn-danger").attr("float","right").attr("type","button").append( $("<i/>").addClass("fa fa-xmark")).click(function(){ $("#myModal").modal().remove(); return false;}) )
                                                    ).append(
                                                        $("<div/>").addClass("modal-body")
                                                            .html( $("<div/>").addClass("from-group")
                                                                .html( $("<label/>").attr("for","rP").text("Motivo de rechazo:") )
                                                                .append( $("<textarea/>").attr("name","rP").attr("id","rP").attr("maxlength","1000").attr("required","1").addClass("form-control") )
                                                        )
                                                    ).append( $("<div/>").addClass("modal-footer")
                                                    .html( $("<button/>").attr("id","btnGuardarObs").addClass("btn btn-outline-success").text("Aceptar")
                                                    .click(function(){
                                                            callbackComunica = function(){
                                                                comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"mainBack"});
                                                            };
                                                            comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"validarOa",mensaje:$("#rP").val(),epg_id:' . ($epg * 1) . ',eoa_id:3 ' . ((empty($prm->regresar)) ? '' : ",regresar:'" . $prm->regresar . "'") . '});
                                                            $("#myModal").modal().remove();
                                                            return false;
                                                        })
                                                    )
                                                    .append( $("<button/>").addClass("btn btn-outline-danger").text("Cancelar").click(function(){ $("#myModal").modal().remove(); return false;}) )
                                                )
                                        )
                                )
                        );
                        $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
                        ';
                        $rechazar = '<button id="rechazar" type="button" class="btn btn-outline-danger me-1" title=" Rechazar' . (isset($prm->es) ? ' Proyecto ' : ' Obra/Actividad ') . '"><i class="fa fa-remove me-2"></i>Rechazar' . (isset($prm->es) ? ' Proyecto ' : ' Obra/Actividad ') . '</button>';

                        $observacion->readonly = true;
                        $observacion->disabled = true;
                    } else {
                        //SI NO ESTA ENVIADA A VALIDACIÓN NO SE PUEDE CAPTURAR OBSERVACIÓN
                        if ($actividad->getEoa_id() != 2 && $actividad->getEoa_id() != 5) {
                            $observacion->readonly = true;
                        }
                    }
                }

                if ($actividad->getEoa_id() != 0 && ($actividad->getEoa_id() == 6)) {
                    if ($actividad->getToa_id() == 2 && $_SESSION[_S]['ssc_id'] == 3 && $_SESSION[_S]['nvj_id'] >= 3 && $actividad->getOa_id() != 0 && isset($_SESSION[_S]['prm']['A']['2']['2']['3'])) {
                        $boton = new stdClass();
                        $boton->id = 'validandoEgresos';
                        $boton->color = 'verde';
                        $boton->icon = 'ok';
                        $boton->value = ' Validar Obra/Actividad ';
                        $botonClickValidaEgresos = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"validarOa",epg_id:' . ($epg * 1) . ',eoa_id:4 ' . ((empty($prm->regresar)) ? '' : ",regresar:'" . $prm->regresar . "'") . '});';
                        $validarEgresos = '<button id="validandoEgresos" type="button" class="btn btn-sm me-1" title="Validar Obra/Actividad"><i class="fa fa-ok me-2"></i>Validar Obra/Actividad</button>';

                        $saveObservacion = new stdClass();
                        $saveObservacion->value = ' Enviar';
                        $saveObservacion->icon = 'comment';
                        $saveObservacion->color = 'azul';
                        $saveObservacion->id = 'saveO';
                        $botonClicGuardaObs = '
                            comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"observacion",mensaje:$("#observaciones").val(),epg_id:' . ($epg * 1) . ' ' . ((empty($prm->regresar)) ? '' : ",regresar:'" . $prm->regresar . "'") . '});
                        ';
                        $botonGuardarObservacion = '<button id="saveO" type="button" class="btn btn-success btn-sm me-1" title=" Enviar"><i class="fa fa-comment me-2"></i>Enviar</button>';
                    }
                }

                $boton = new stdClass();
                $boton->value = 'Historial Observaciones';
                $boton->icon = 'list-alt';
                $boton->id = 'verHistorial';
                $botonClicHistorial = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"verHistorial",tipo:"observacion",mje_id:' . ($actividad->getObservacion()) . '});';
                $observacion = '
                <div class="col-md-6 col-xs-12">
                <fieldset class="col-md-9 col-xs-12 inline-block"><legend>Observación:</legend>' . $da->ponTextArea($observacion) . '</div></fieldset>';
                $botonHistorial = '<button id="verHistorial" type="button" class="btn btn-outline-secondary me-1" title=" Historial Observaciones"><i class="fa fa-list-alt me-2"></i>Historial Observaciones</button>';
            }

            if ($actividad->getDescripcion() != 0) {
                $mensaje = new HistorialMensajes();
                $mensaje->setMje_id($actividad->getDescripcion());
                $mensaje->select();
                $mensajeRechazo = new stdClass();
                $mensajeRechazo->id = 'rechazo';
                $mensajeRechazo->enDiv = true;
                $mensajeRechazo->maxlength = 1000;
                $mensajeRechazo->readOnly = 100;
                $mensajeRechazo->texto = $mensaje->getMje_descripcion();
                $boton = new stdClass();
                $boton->value = ' Historial de Rechazo';
                $boton->icon = 'list-alt';
                $boton->id = 'verHistorial2';
                $botonClicHistorial2 = 'comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"verHistorial",tipo:"rechazo",mje_id:' . ($actividad->getDescripcion()) . '});';
                $botonHistorial2 = '<button id="verHistorial2" type="button" class="btn btn-outline-secondary me-1" title=" Historial de Rechazo"><i class="fa fa-list-alt me-2"></i>Historial de Rechazo</button>';

                $mensajeRechazo = '
                <div class="col-md-6 col-xs-12">
                    <fieldset class="col-md-9 col-xs-12 inline-block">
                        <legend>Motivo de rechazo</legend>
                        ' . $da->ponTextArea($mensajeRechazo) . '
                    </fieldset>
                </div>
            ';
            }
        }

        $da->agregaTools($regresar . $guardar . $botonDelete . $validar . $validarEgresos . $rechazar . $guardarPes . $validaPes . $botonHistorial . $botonHistorial2 . $botonGuardarObservacion . '<div id="divContenedorGeneralModal"></div>');
        $da->mixHtml('<div class="row">' . $observacion . $mensajeRechazo . '</div>');

        $da->mixScript('
            var cveFinArray = [];
            var metasArray = [];
            var politicasArray = [];
            var ept = [];
            ArraysObjetivosPi = [];
            var encPI = [];
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#regresarAct").click(); e.stopPropagation(); });


            $("#eliminaActividad").click(function(){
                ' . $clickEliminarAct . '
            });
            $("#regresarAct").click(function(){
                ' . $clickRegresarAct . '
            });

            $("#validando").click(function(){
                ' . $botonClickValidar . '
            });

            $("#rechazar").click(function(){
                ' . $botonClickRechazar . '
            });

            $("#Validar").click(function(){
                ' . $botonClickValidarPes . '
            });

            $("#validandoEgresos").click(function(){
                ' . $botonClickValidaEgresos . '
            });

            $("#verHistorial").click(function(){
                ' . $botonClicHistorial . '
            });

            $("#verHistorial2").click(function(){
                ' . $botonClicHistorial2 . '
            });
            $("#saveO").click(function(){
                ' . $botonClicGuardaObs . '
            });
        ');
        return $da;
    }

    private function getEjePED()
    {
        $eje_id = 0;
        $params = [
            ':programa'    => $_SESSION[_S]['clasAdm-prg'],
            ':supPrograma' => $_SESSION[_S]['clasAdm-spr'],
        ];
        $this->conectar();
        $this->setSql('
                SELECT epo_id
                FROM vin_Programa
                INNER JOIN cat_Subprogramas USING (prgs_id)
                WHERE prgs_id = :programa
                AND sprs_id = :supPrograma ;
            ');
        $this->consultar($params);
        $datos = $this->extraer();
        $this->desconectar();

        if ($this->getNumeroRegistros() > 0) {
            $this->conectar();
            $params = [':epo' => $datos['epo_id']];
            $this->setSql('
                SELECT eje_id, tem_id, obj_id FROM enc_PedObjetivo
                WHERE epo_id = :epo ;
            ');
            $this->consultar($params);
            $regs = $this->extraer();
            if ($this->getNumeroRegistros() > 0) {
                $eje_id = $regs['eje_id'];
            }
        }
        return $eje_id;
    }

    //Valida si la Unidad Ejecutora tiene Ejes Transversales Asignados
    private function getEjeT()
    {
        $tieneEjesTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur'    => $this->ur_id,
                ':ue'    => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EjeTransSupueDos
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue ;
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneEjesTransversales = true;
            }
        }
        return $tieneEjesTransversales;
    }

    //Valida si la Unidad Ejecutora tiene Ejes Estrategias Asignadas
    private function getEtraT()
    {
        $tieneEstrategiasTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur'    => $this->ur_id,
                ':ue'    => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EstrategiaTransversalSupDos
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue ;
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneEstrategiasTransversales = true;
            }
        }
        return $tieneEstrategiasTransversales;
    }

    //Valida si la Unidad Ejecutora tiene Lineas de Acción Asignadas
    private function getLinAccT()
    {
        $tieneLineasAccionTransversales = false;
        if (!empty($this->ue_id)) {
            $this->conectar();
            $params = [
                ':grupo' => $this->gpo_id,
                ':ur'    => $this->ur_id,
                ':ue'    => $this->ue_id,
            ];
            $this->setSql('
                    SELECT *
                    FROM enc_EjeTransLineaAcc
                    WHERE gpo_id = :grupo
                    AND ur_id = :ur
                    AND ue_id = :ue
                ');
            $this->consultar($params);
            $this->desconectar();

            if ($this->getNumeroRegistros() > 0) {
                $tieneLineasAccionTransversales = true;
            }
        }
        return $tieneLineasAccionTransversales;
    }

    private function muestraPedTransversal()
    {
        $eje_id = $this->getEjePED();
        $PoliticasT = new stdClass();
        $PoliticasT->eje_id = $eje_id;
        $PoliticasT->lectura = true;
        $PoliticasT->ueEjeT = $this->getEjeT();
        $PoliticasT->ueEtraT = $this->getEtraT();
        $PoliticasT->ueLinAccT = $this->getLinAccT();

        $this->setContenedor('divContenedorGeneralModal');

        $texto = (!isset($this->prm->lectura)) ? 'Agregar Política transversal' : 'Política transversal';

        $inputPorcentaje = new stdClass();
        $inputPorcentaje->id = 'ept_porcentaje';
        $inputPorcentaje->label = 'Ponderación porcentual';
        $inputPorcentaje->maxlength = 4;
        $inputPorcentaje->formato = 'numero';
        $inputPorcentaje->size = 4;
        $inputPorcentaje->enDiv = true;
        if (isset($this->prm->ept_porcentaje) && $this->prm->ept_porcentaje != '') {
            $inputPorcentaje->value = $this->prm->ept_porcentaje;
            $inputPorcentaje->disabled = true;
        }
        $inputPorcentaje->required = true;
        $ept_porcentaje = $this->ponInput($inputPorcentaje);
        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">' . $texto . '</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="PoliticaTransversal">
                        <div class="row">
                        ' . PoliticasT::ponPoliticasT($PoliticasT) . $ept_porcentaje . '
                        </form>
                    </div>
                    <div class="modal-footer">';
        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnGuardarPolitica" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPt" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modal);

        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : '';

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {
                $("#myModal").modal().remove();
            })
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPt").click(function(){ $("#myModal").modal().remove(); });

            $("#btnGuardarPolitica").click(function(){
                valido = ( $("#lact_id").val() != "null" );
                ok = true;
                var r=0;

                var diferencia=0;
                for(var q=0;q<ept.length;q++)
                {
                    if(typeof(ept[q]["ept_porcentaje"])=="number") r+=ept[q]["ept_porcentaje"];
                }
                diferencia = (100 - r);

                if($("#ept_porcentaje").val().replace(",","").substring(0,1) == "-"){
                    ok = false;
                    alertify.alert(
                        "",
                        "<h5>El valor de la ponderación no puede ser negativo</h5>"
                    );
                } else if($("#ept_porcentaje").val().replace(",","") == 0.00){
                    ok = false;
                    alertify.alert(
                        "",
                        "<h5>El porcentaje debe ser mayor 0.00%</h5>"
                    );
                } else if($("#ept_porcentaje").val().replace(",","") > diferencia && $("#lact_id").val()!=5){
                    ok = false;
                    if(diferencia==0)
                        alertify.alert(
                            "",
                            "<h5>La suma de las Líneas de Acción ya tiene 100%</h5>"
                        );
                    else
                        alertify.alert(
                            "",
                            "<h5>Solo puedes poner maximo: "+diferencia+"</h5>"
                        );
                }

                $("#PoliticaTransversal").find(":required").each(function(){
                    valido = valido && this.checkValidity();
                    if(!valido){
                        if ($("#ejt_id").val() == "" || $("#etra_id").val() == "" || $("#lact_id").val() == "") {
                            if (!valido) {
                            }
                        }
                    }
                });
                if(valido && ok){
                    callbackComunica = function(){
                        $("#modalPedTransversal").empty();
                    };
                    comunica("poa/programacion/ControladorActividades.php",{
                        metodoCtrlAct: "agregaPolitica",
                        ejt_id:$("#ejt_id").val(),
                        eje_id:$("#eje_id").val(),
                        etra_id:$("#etra_id").val(),
                        lact_id:$("#lact_id").val(),
                        ept_porcentaje:$("#ept_porcentaje").val()
                        ' . $epg_id . '
                    });
                    ' . (!isset($this->prm->epg_id) ? '$("#myModal").modal().remove();' : '') . '
                }
            });
        ');
    }

    private function agregaPolitica()
    {
        $this->prm->etla_id = $_SESSION[_S]['politicasT']->etla;
        $a = new AccesoDatos();
        $a->conectar();
        $params = [':etla' => $this->prm->etla_id];
        $a->setSql('SELECT lact_Clave
                FROM enc_EjeTransLineaAcc
                INNER JOIN cat_LineaAccionTransversal USING(lact_id)
                WHERE etla_id = :etla ;');
        $a->consultar($params);
        $a->desconectar();
        $ept = $a->extraer();

        $boton = new stdClass();
        $boton->title = 'Eliminar Política Transversal';
        $boton->id = 'ept_' . $this->prm->etla_id;
        $boton->color = 'rojo';
        $boton->icon = 'trash-can';
        $fila = '
            $("#filasPoliticasT").append(
                $("<tr>").attr("id","filasPoliticasT-' . $this->prm->etla_id . '")
                .attr("class","registro")
                .append("<td>' . $ept['lact_Clave'] . '</td>")
                .append("<td>' . $this->prm->ept_porcentaje . '</td>")
                .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
            );';
        if (isset($this->prm->epg_id) && $this->prm->epg_id != '') {
            $b = new AccesoDatos();
            $b->conectar();
            $params = [
                ':grupo' => $this->clasAdm_gpo,
                ':ur'    => $this->clasAdm_ur,
                ':epg'   => $this->prm->epg_id,
                ':etla'  => $this->prm->etla_id,
            ];
            $b->setSql('SELECT etl.etla_id
                    FROM
                        enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat USING(lact_id)
                    WHERE ep.gpo_id = :grupo and ep.ur_id = :ur and ep.epg_id = :epg and  vpt.etla_id = :etla;');
            $b->consultar($params);
            $b->desconectar();
            if ($b->getNumeroRegistros() > 0) {
                $this->mixScript('
                    alertify.alert(
                        "",
                        "<h5>El eje ya está agregado</h5>"
                    );
                ');
            } else {
                $c = new AccesoDatos();
                $c->conectar();
                $params = [
                    ':grupo' => $this->clasAdm_gpo,
                    ':ur'    => $this->clasAdm_ur,
                    ':epg'   => $this->prm->epg_id,
                ];
                $c->setSql('SELECT etl.etla_id
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                    INNER JOIN cat_LineaAccionTransversal USING(lact_id)
                    WHERE ep.gpo_id = :grupo and ep.ur_id = :ur and ep.epg_id = :epg;');
                $c->consultar($params);
                $c->desconectar();

                if (($this->prm->etla_id == 5 && $c->getNumeroRegistros() > 0)) {
                    $cped = new AccesoDatos();
                    $cped->conectar();
                    $cped->setSql('SELECT etl.etla_id, vpt.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vpt USING(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc etl USING(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat USING(lact_id)
                    WHERE ep.gpo_id = :grupo and ep.ur_id = :ur and ep.epg_id = :epg;');
                    $cped->consultar($params);
                    $cped->desconectar();
                    $row = $cped->extraer();
                    $eptids = '';
                    do {
                        $eptids .= $row['etla_id'] . ' - porcentaje ' . $row['ept_porcentaje'] . ',';
                    } while ($row = $cped->extraer());

                    //Eimina eje transversal
                    $d = new AccesoDatos();
                    $params = [':epg' => $this->prm->epg_id];
                    $d->conectar();
                    $d->setSql('DELETE FROM vin_ProgramaticoTransversal WHERE epg_id = :epg;');
                    $d->consultar($params);
                    $d->desconectar();

                    $DatosOa = new ObraActividad($this->prm);

                    //Agrega eje transversal
                    $epgT = new AccesoDatos();
                    $epgT->conectar();
                    $params = [
                        ':epg'        => $this->prm->epg_id,
                        ':etla'       => $this->prm->etla_id,
                        ':porcentaje' => number_format($this->prm->ept_porcentaje, 2),
                        ':usr'        => $this->usr_id,
                    ];
                    $epgT->setSql('INSERT INTO vin_ProgramaticoTransversal (epg_id,etla_id,epp_porcentaje,usr_id)
                                VALUES(:epg, :etla, :porcentaje, :usr)');
                    $epgT->consultar($params);
                    $epgT->desconectar();

                    $this->prm->gpo_id = $DatosOa->getGpo_id();
                    $this->prm->ur_id = $DatosOa->getUr_id();
                    $this->prm->prg_id = $DatosOa->getPrg_id();
                    $this->prm->spr_id = $DatosOa->getSpr_id();
                    $this->prm->py_id = $DatosOa->getPy_id();
                    $this->prm->oa_id = $DatosOa->getOa_id();
                    $this->prm->eoa_id = $DatosOa->getEoa_id();

                    $this->prm->nombre = $DatosOa->getNombre();
                    $this->prm->observacion = $DatosOa->getObservacion();
                    $this->prm->ue_id = $DatosOa->getUe_id();
                    $this->prm->anticipo = $DatosOa->getAnticipo();
                    $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
                    $this->prm->amortizacion = $DatosOa->getAmortizacion();
                    $this->prm->eug_id = $DatosOa->getEug_id();
                    $this->prm->eoa_id = $DatosOa->getEoa_id();
                    $this->prm->toa_id = $DatosOa->getToa_id();
                    $this->prm->pbf_id = $DatosOa->getPbf_id();
                    $this->prm->sts_id = $DatosOa->getSts_id();
                    $this->registrarBitacora('Ejes transversales tenia: ' . $eptids . ' y agrego ' . $this->prm->etla_id . ' - porcentaje' . $this->prm->ept_porcentaje . '');

                    $remove = '';
                    if (!empty($this->prm->etla_id)) {
                        if ($this->prm->etla_id == 5) {
                            $remove .= '$(".registro").remove();';
                        } else {
                            $remove .= '$("#filasPoliticasT-5").remove();';
                        }
                    }

                    $this->mixScript('
                            ' . $remove . '
                            ' . $fila . '
                            $("#ept_' . $this->prm->etla_id . '").click(function(){
                            if(politicasArray.length > 1)
                            $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
                            comunica("' . $this->pantalla . '",{metodoCtrlAct:"eliminaPolitica",epg_id:"' . $this->prm->epg_id . '",etla_id:' . $this->prm->etla_id . '});
                        });
                    ');
                } else {
                    $e = new AccesoDatos();
                    $e->conectar();
                    $params = [
                        ':grupo' => $this->clasAdm_gpo,
                        ':ur'    => $this->clasAdm_ur,
                        ':epg'   => $this->prm->epg_id,
                    ];
                    $e->setSql('SELECT etl.etla_id, vpt.ept_porcentaje
                    FROM enc_Programatico ep
                    INNER JOIN vin_ProgramaticoTransversal vpt using(epg_id)
                    INNER JOIN enc_EjeTransLineaAcc etl using(etla_id)
                    INNER JOIN cat_LineaAccionTransversal lat using(lact_id)
                    WHERE ep.gpo_id = :grupo and ep.ur_id = :ur and
                        ep.epg_id = :epg ;');
                    $e->consultar($params);
                    $e->desconectar();
                    $row = $e->extraer();

                    $eptids = '';
                    if ($e->getNumeroRegistros() > 0) {
                        do {
                            $eptids .= $row['etla_id'] . ' - porcentaje ' . $row['ept_porcentaje'] . ',';
                        } while ($row = $e->extraer());
                    }

                    $DatosOa = new ObraActividad($this->prm);

                    $porcent = new AccesoDatos();
                    $porcent->conectar();
                    $params = [':epg' => $this->prm->epg_id];
                    $porcent->setSql('
                        SELECT SUM(ept_porcentaje) as sumaPorcentaje
                        FROM vin_ProgramaticoTransversal
                        WHERE epg_id = :epg;
                    ');
                    $porcent->consultar($params);
                    $porcent->desconectar();
                    $totalPorcentaje = $porcent->extraer();
                    $ban = true;
                    $diferencia = 0;
                    $sumaTotalPorcentaje = $totalPorcentaje['sumaPorcentaje'] ?? 0;
                    $diferencia = (100.00 - (float) number_format(($sumaTotalPorcentaje), 2, '.', ''));
                    $diferencia = number_format($diferencia, 2, '.', '');
                    if (number_format($this->prm->ept_porcentaje, 2) == 0.00) {
                        $ban = false;
                        $this->mixScript('
                            alertify.alert("",
                            "<h5>El porcentaje debe ser mayor 0.00%</h5>"
                            );
                        ');
                    } elseif ($totalPorcentaje['sumaPorcentaje'] == 100) {
                        $ban = false;
                        $this->mixScript('
                            alertify.alert(
                                "",
                                "<h5>La suma de las lineas de acción ya tiene 100%</h5>"
                            );
                        ');
                    } elseif ($this->prm->ept_porcentaje > $diferencia) {
                        $ban = false;
                        $this->mixScript('
                            alertify.alert(
                                "",
                                "<h5>Solo puedes poner maximo ' . $diferencia . '</h5>"
                            );
                        ');
                    }

                    if ($ban) {
                        $epgT = new AccesoDatos();
                        $epgT->conectar();
                        $params = [
                            ':epg'        => $this->prm->epg_id,
                            ':etla'       => $this->prm->etla_id,
                            ':porcentaje' => number_format($this->prm->ept_porcentaje, 2),
                            ':usr'        => $this->usr_id,
                        ];
                        $epgT->setSql('INSERT INTO vin_ProgramaticoTransversal (epg_id,etla_id,ept_porcentaje,usr_id)
                                VALUES(:epg, :etla, :porcentaje, :usr)');
                        $epgT->consultar($params);
                        $epgT->desconectar();

                        $this->prm->gpo_id = $DatosOa->getGpo_id();
                        $this->prm->ur_id = $DatosOa->getUr_id();
                        $this->prm->prg_id = $DatosOa->getPrg_id();
                        $this->prm->spr_id = $DatosOa->getSpr_id();
                        $this->prm->py_id = $DatosOa->getPy_id();
                        $this->prm->oa_id = $DatosOa->getOa_id();
                        $this->prm->eoa_id = $DatosOa->getEoa_id();

                        $this->prm->nombre = $DatosOa->getNombre();
                        $this->prm->observacion = $DatosOa->getObservacion();
                        $this->prm->ue_id = $DatosOa->getUe_id();
                        $this->prm->anticipo = $DatosOa->getAnticipo();
                        $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
                        $this->prm->amortizacion = $DatosOa->getAmortizacion();
                        $this->prm->eug_id = $DatosOa->getEug_id();
                        $this->prm->eoa_id = $DatosOa->getEoa_id();
                        $this->prm->toa_id = $DatosOa->getToa_id();
                        $this->prm->pbf_id = $DatosOa->getPbf_id();
                        $this->prm->sts_id = $DatosOa->getSts_id();

                        $this->registrarBitacora('Ejes transversales tenia: ' . $eptids . ' y agrego ' . $this->prm->etla_id . ' - porcentaje ' . $this->prm->ept_porcentaje . '');

                        $remove = '';
                        if (!empty($this->prm->ejt_id)) {
                            if ($this->prm->ejt_id == 5) {
                                $remove .= '$(".registro").remove();';
                            } else {
                                $remove .= '$("#filasPoliticasT-5").remove();';
                            }
                        }

                        $this->mixScript('
                                ' . $remove . '
                                ' . $fila . '
                                $("#botonPedPlus").attr("disabled",false);
                                $("#ept_' . $this->prm->etla_id . '").click(function(){
                                    comunica("' . $this->pantalla . '",{metodoCtrlAct:"eliminaPolitica",epg_id:"' . $this->prm->epg_id . '",etla_id:' . $this->prm->etla_id . '});
                                });
                                $("#myModal").modal().remove();
                        ');
                    }
                }
            }

            $this->mixScript('
                $("#e_' . $this->prm->etla_id . '").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodoCtrlAct:"muestraPedTransversal",
                        etla_id:' . $this->prm->etla_id . ',
                        ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                        lectura:"true"
                    });
                });
            ');
        } else {
            $this->mixScript('
                if(politicasArray.indexOf("05") < 0){
                    if(($("#filasPoliticasT-01").length ==1 || $("#filasPoliticasT-02").length ==1 || $("#filasPoliticasT-03").length ==1 || $("#filasPoliticasT-04").length ==1) && ' . $this->prm->ejt_id . ' == "05"){
                        $("#botonPedPlus").attr("disabled","disabled");
                        for(var i = politicasArray.length - 1; i >= 0; i--) {
                            $(".registro").remove();
                            politicasArray.splice(i, 1);
                            ept.splice(i, 1);
                        }
                        politicasArray.push("' . $this->prm->etla_id . '");
                        ept.push({"etla_id":"' . $this->prm->etla_id . '","ept_porcentaje":"' . $this->prm->ept_porcentaje . '"});
                        ' . $fila . '

                        $("#ept_05").click(function(){
                            $("#filasPoliticasT-05").remove();
                            politicasArray.splice( politicasArray.indexOf("05"),1);
                            ept.splice( ept.indexOf("' . $this->prm->etla_id . '"),1);
                            $("#botonPedPlus").attr("disabled",false);
                        });

                        $("#e_' . $this->prm->etla_id . '").click(function(){
                            comunica("' . $this->pantalla . '",{
                                metodoCtrlAct:"muestraPedTransversal",
                                etla_id:"' . $this->prm->etla_id . '",
                                ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                                lectura:"true"
                            });
                        });
                    }else{
                        if(politicasArray.indexOf("' . $this->prm->etla_id . '") < 0 ){
                            if(' . $this->prm->etla_id . ' == "05")
                                $("#botonPedPlus").attr("disabled","disabled");
                                politicasArray.push("' . $this->prm->etla_id . '");

                                ept.push({"etla_id":"' . $this->prm->etla_id . '","ept_porcentaje":' . $this->prm->ept_porcentaje . '});
                                    ' . $fila . '
                                $("#ept_' . $this->prm->etla_id . '").click(function(){
                                    $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
                                    ept.splice( politicasArray.indexOf("' . $this->prm->etla_id . '"),1);
                                    politicasArray.splice( politicasArray.indexOf("' . $this->prm->etla_id . '"),1);
                                });

                                $("#ept_05").click(function(){
                                    $("#filasPoliticasT-05").remove();
                                    politicasArray.splice( politicasArray.indexOf(05),1);
                                    ept.splice( ept.indexOf(' . $this->prm->etla_id . '),1);
                                    $("#botonPedPlus").attr("disabled",false);
                                });

                                $("#e_' . $this->prm->etla_id . '").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodoCtrlAct:"muestraPedTransversal",
                                        etla_id:' . $this->prm->etla_id . ',
                                        ept_porcentaje:' . $this->prm->ept_porcentaje . ',
                                        lectura:"true"
                                    });
                                });
                        }else
                            alertify.alert(
                                "",
                                "<h5>La línea de acción ya está agregada</h5>"
                            );

                    }
                }else $("#botonPedPlus").attr("disabled","disabled");
            ');
        }
    }

    private function eliminaPolitica()
    {
        $params = [
            ':epg'  => $this->prm->epg_id,
            ':etla' => $this->prm->etla_id,
        ];
        $this->setSql('DELETE FROM vin_ProgramaticoTransversal WHERE epg_id = :epg and etla_id = :etla;');
        $this->mixScript('
            $("#filasPoliticasT-' . $this->prm->etla_id . '").remove();
        ');
        $this->conectar();
        $this->consultar($params);
        $this->desconectar();

        $DatosOa = new ObraActividad($this->prm);

        $this->prm->gpo_id = $DatosOa->getGpo_id();
        $this->prm->ur_id = $DatosOa->getUr_id();
        $this->prm->prg_id = $DatosOa->getPrg_id();
        $this->prm->spr_id = $DatosOa->getSpr_id();
        $this->prm->py_id = $DatosOa->getPy_id();
        $this->prm->oa_id = $DatosOa->getOa_id();
        $this->prm->eoa_id = $DatosOa->getEoa_id();

        $this->prm->nombre = $DatosOa->getNombre();
        $this->prm->observacion = $DatosOa->getObservacion();
        $this->prm->ue_id = $DatosOa->getUe_id();
        $this->prm->anticipo = $DatosOa->getAnticipo();
        $this->prm->anticipo2026 = $DatosOa->getAnticipo2026();
        $this->prm->amortizacion = $DatosOa->getAmortizacion();
        $this->prm->eug_id = $DatosOa->getEug_id();
        $this->prm->eoa_id = $DatosOa->getEoa_id();
        $this->prm->toa_id = $DatosOa->getToa_id();
        $this->prm->pbf_id = $DatosOa->getPbf_id();
        $this->prm->sts_id = $DatosOa->getSts_id();

        $this->registrarBitacora('Eliminó Política transversal: ' . $this->prm->etla_id . '');
    }

    private function metas()
    {
        $this->setContenedor('divContenedorGeneralModal');
        $epg_id = (empty($this->prm->epg_id)) ? 0 : $this->prm->epg_id;
        $gpo = $this->prm->gpo_id;
        $ur = $this->prm->ur_id;
        $prg = $this->prm->prgs_id;
        $spr = $this->prm->sprs_id;
        $change = '';
        $edoVin = isset($this->prm->edoVin) ? $this->prm->edoVin : 0;
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
        $lectura = (isset($this->prm->lectura)) ? $this->prm->lectura : 0;

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
        if ($lectura) {
            $actividadMIR->disabled = true;
        }
        $actividadMIR = $this->ponCombo($actividadMIR);

        $indicador = new stdClass();
        $indicador->id = 'ind_id';
        $indicador->label = 'Indicador';
        $indicador->required = true;
        if ($lectura) {
            $indicador->selecciona = $this->prm->ind_id;
            $indicador->disabled = true;
        }
        $indicador->change = '
            $("#umv_id").val($(this).find("option:selected").attr("umv_nombre"));
            $("#umi_id").val($(this).find("option:selected").attr("umi_nombre"));
            $("#tiv_id").val($(this).find("option:selected").attr("tiv_nombre"));
            $("#descripcion").val($(this).find("option:selected").attr("ind_definicion"));
            $("#nombre_ind").val($(this).find("option:selected").attr("nombre_ind"));
        ';

        $indicador->sql = '
            SELECT
                ind_id,
                ind_nombre,
                concat(ind_id," - ",
                ind_nombre) as nombre_ind,
                ind_definicion,
                umv_id,umv_nombre,
                tiv_id,
                tiv_nombre,
                umi_id,
                umi_nombre,
                pdf_id,pdi_id,pii_id,
                pii_id as pii,
                (SELECT macm_id FROM cat_MetaActividadMir 
                    WHERE gpo_id = ' . $gpo . ' and ur_id = ' . $ur . ' 
                    and pii_id = pii
                ) AS macm_id,
                (SELECT macm_id FROM cat_MetaActividadMir 
                    WHERE gpo_id = ' . $gpo . ' and ur_id = ' . $ur . ' 
                    and pii_id = pii
                ) AS macm,
                (select macm_id FROM vin_ObraMir
                    WHERE macm_id = macm
                ) as metaActividad,
                (select epg_id from vin_ObraMir
                    WHERE macm_id = macm
                ) as epg,
                (   SELECT concat(prg_id,"-",spr_id,"-",py_id,"-",oa_id," de la UR ",gpo_id,ur_id) as encuadre
                    FROM enc_Programatico
                    WHERE epg_id = epg
                ) as encuadre,
                (
                    SELECT sum(macm_denominadorT1+macm_denominadorT2+macm_denominadorT3+macm_denominadorT4) 
                    FROM aux_HistoricoMetaActividadMir WHERE macm_id = macm
                ) as total
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
        $indicador->comoAtr = 'ind_definicion,umv_nombre,umv_id,umi_nombre,umi_id,tiv_id,tiv_nombre,pdf_id,pdi_id,pii_id,macm_id,metaActividad,encuadre,total,nombre_ind';
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

        $form = new form($form);

        //Al registrar un meta los campos de los trimestres tiene un valor igual a cero
        $original = [0, 0, 0, 0];

        $metasIndicador = new stdClass();
        $metasIndicador->title = 'Ver Metas Del Indicador';
        $metasIndicador->value = 'Ver Metas Del Indicador';
        $metasIndicador->id = 'metasIndicador';
        $metasIndicador->color = 'azul';
        $metasIndicador->icono = 'eyes';
        $metasIndicador = $this->ponBoton($metasIndicador);

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

        $modalMetas = '
            <div id="metasActividad" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog shadow-lg modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Complemento de Actividades</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                            <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                            <div id = "modalMetaIndicador"></div>
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
                                <fieldset class="col-md-4 col-xs-4"><br>
                                    ' . $metasIndicador . '
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
                            <br/>';

        $modalMetas .= '
                    </div>
                    <div class="modal-footer">';
        if (!$lectura) {
            $modalMetas .= '
                    <button  class="btn btn-outline-success" id="save" >Aceptar</button>
                    <button type="button" class="btn btn-outline-danger" id="cancel" data-dismiss="modal">Cancelar</button>
                </div>';
        }

        $modalMetas .= '
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
                prm["pdf_id"] = $("#ind_id").find("option:selected").attr("pdf_id");//Producto final
                prm["pii_id"] = $("#ind_id").find("option:selected").attr("pii_id");//
                prm["macm_id"] = $("#ind_id").find("option:selected").attr("macm_id");//
                prm["epg_id"] = "' . $epg_id . '";
                prm["gpo_id"] = "' . $gpo . '";
                prm["ur_id"] = "' . $ur . '";
                prm["prgs_id"] = "' . $prg . '";
                prm["sprs_id"] = "' . $spr . '";
 
                if( prm["ind_id"]==""){
                    notify("No hay Indicador Seleccionado","warning");
                    return false;
                }

                if ($("#ind_id").find("option:selected").attr("macm_id") == undefined) {
                    notify("El indicador no tiene registro de metas en la MIR","warning");
                    return false;
                } 
                    
                const fechaActual = new Date();
                const anioActual = fechaActual.getFullYear();

                if (anioActual == 2025) {
                    var total  = $("#ind_id").find("option:selected").attr("total");
                    if (total == 0) {
                        notify("El indicador tiene registros en 0 en todos los trimestres","warning");
                        return false;
                    }
                }
                
                if($("#indicador"+prm["ind_id"]).length == 1){
                    notify("El indicador Seleccionado ya ha sido agregado","warning");
                    return false;
                }

                if($("#ind_id").find("option:selected").attr("metaActividad") == undefined){
                } else {
                    var encuadre = $("#ind_id").find("option:selected").attr("encuadre");
                    notify("La meta MIR del indicador seleccionado ya se encuentra registrado en la estructura " + encuadre,"warning");
                    return false;
                }
                
                if(' . $epg_id . ' > 0){
                    comunica("poa/programacion/ControladorActividades.php",{
                        metodoCtrlAct:"validaMeta",
                        epg_id:"' . ($epg_id * 1) . '",
                        tiv_id: $("#tiv_id").val(),
                        umi_id: $("#umi_id").val(),
                        ind_id: $("#ind_id").find("option:selected").val(),
                        pii_id: $("#ind_id").find("option:selected").attr("pii_id"),
                        macm_id: $("#ind_id").find("option:selected").attr("macm_id"),
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

            $("#metasIndicador").click(function(){
                if($("#ind_id").val() != ""){
                    comunica("poa/programacion/ControladorActividades.php",{
                        metodoCtrlAct:"verMetaIndicador",
                        nim_id:4,
                        ind_id: $("#ind_id").find("option:selected").val(),
                        eri_id: $("#ind_id").find("option:selected").attr("pii_id"),
                        lectura: 1,
                        ur:"' . $gpo . $ur . '",
                        pdf_id: $("#ind_id").find("option:selected").attr("pdf_id"),  
                    });
                } else {
                    notify("No hay Indicador Seleccionado","warning");
                }
                return false;
            });

        ');
    }

    private function verMetaIndicador()
    {
        $this->prm->pii_id = $this->prm->eri_id;
        $this->prm->gpo_id = substr($this->prm->ur, 0, 1);
        $this->prm->ur_id = substr($this->prm->ur, 1, 2);
        $this->prm->tri_id = (int) $this->trimestreActual();
        $this->prm->macm_id = 0;

        $this->conectar();
        $this->setSql(
            '
            SELECT macm_id
            FROM cat_MetaActividadMir
            WHERE gpo_id = ?
            AND ur_id = ?
            AND pii_id = ?
            ;'
        );
        $parametros = [
            $this->prm->gpo_id,
            $this->prm->ur_id,
            $this->prm->pii_id,
        ];
        $this->consultar($parametros);
        $idMeta = $this->extraer();
        if ($this->getNumeroRegistros() > 0) {
            $this->prm->macm_id = $idMeta['macm_id'];
        } else {
            $this->mixScript('notify("El indicador no tiene registro de metas en la MIR", "warning")');
            return false;
        }
        $this->desconectar();
        $this->setContenedor('modalMetaIndicador');

        $this->conectar();
        $this->setSql(
            'SELECT "S" AS tiv, tiv_id, ind_nombre, ind_definicion,
            1 AS frm_id, umi_id, nim_id, ind_definicionMIR, ind_nombreMIR,
            umv_id,umv_nombre, ind_formulaCalculo
            FROM cat_Indicador
            INNER JOIN cat_UnidadMedidaVariable USING(umv_id)
            WHERE nim_id = ?
            AND ind_id = ?;'
        );
        $parametros = [
            4,
            $this->prm->ind_id,
        ];
        $this->consultar($parametros);
        $datos = $this->extraer();

        $tiv = $datos['tiv'];

        $nom_ind = $datos['ind_nombre'];
        $nom_MIR = $datos['ind_nombreMIR'];
        $umi = $datos['umi_id'];
        $umv = $datos['umv_id'];
        $unom = $datos['umv_nombre'];
        $calculo = $datos['ind_formulaCalculo'];

        $metaActividad = new MetaActividadMir($this->prm);
        $historicoMeta = new HistoricoMetaActividadMir($this->prm);

        $descripcion = new stdClass();
        $descripcion->id = 'descripcion';
        $descripcion->required = true;
        $descripcion->enDiv = true;
        $descripcion->maxlength = 500;
        $descripcion->width = '300px';
        $descripcion->height = '100px';
        $descripcion->disabled = true;
        $descripcion->texto = $nom_MIR;

        $variable = new stdClass();
        $variable->id = 'umvariable';
        $variable->required = true;
        $variable->enDiv = true;
        $variable->maxlength = 500;
        $variable->width = '300px';
        $variable->height = '100px';
        $variable->disabled = true;
        $variable->texto = $nom_ind;

        $metodocal = new stdClass();
        $metodocal->id = 'calculo_mod';
        $metodocal->required = true;
        $metodocal->enDiv = true;
        $metodocal->maxlength = 100;
        $metodocal->width = '300px';
        $metodocal->height = '100px';
        $metodocal->disabled = true;
        $metodocal->texto = $calculo;

        $medida = new stdClass();
        $medida->id = 'umi_mod';
        $medida->required = true;
        $medida->enDiv = true;
        $medida->maxlength = 100;
        $medida->width = '300px';
        $medida->height = '100px';
        $medida->disabled = true;
        $medida->texto = $umv . '-' . $unom;

        //***********************LINEA BASE***************************************//
        $macLb = new stdClass();
        $macLb->required = true;
        $macLb->id = 'lb';
        $macLb->enDiv = true;
        $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
        $macLb->maxlength = 4;
        $macLb->disabled = true;
        $macLb->formato = 'entero';

        $macLbValor = new stdClass();
        $macLbValor->required = true;
        $macLbValor->id = 'lbValor';
        $macLbValor->enDiv = true;
        $macLbValor->value = $metaActividad->getlbValor();
        $macLbValor->disabled = true;
        $macLbValor->formato = 'numero';

        $macLbIndicador = new stdClass();
        $macLbIndicador->required = true;
        $macLbIndicador->id = 'lbIndicador';
        $macLbIndicador->enDiv = true;
        $macLbIndicador->value = $metaActividad->getLbIndicador();
        $macLbIndicador->disabled = true;
        $macLbIndicador->formato = 'numero';

        //***********************AÑO ANTERIOR***************************************//
        $macAnterior = new stdClass();
        $macAnterior->required = true;
        $macAnterior->id = 'anterior';
        $macAnterior->enDiv = true;
        $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macAnterior->maxlength = 4;
        $macAnterior->disabled = true;
        $macAnterior->formato = 'entero';

        $macAnteriorValor = new stdClass();
        $macAnteriorValor->required = true;
        $macAnteriorValor->id = 'anteriorValor';
        $macAnteriorValor->enDiv = true;
        $macAnteriorValor->value = !empty($metaActividad->getAnteriorvalor()) ? $metaActividad->getAnteriorvalor() : 0;
        $macAnteriorValor->disabled = true;
        $macAnteriorValor->formato = 'numero';

        $macOriginal = new stdClass();
        $macOriginal->required = true;
        $macOriginal->id = 'macm_original';
        $macOriginal->enDiv = true;
        $macOriginal->decimales = 4;
        $macOriginal->value = $historicoMeta->getOriginal();
        $macOriginal->disabled = true;
        $macOriginal->formato = 'numero';

        $macAnteriorIndicador = new stdClass();
        $macAnteriorIndicador->required = true;
        $macAnteriorIndicador->id = 'anteriorIndicador';
        $macAnteriorIndicador->enDiv = true;
        $macAnteriorIndicador->value = $historicoMeta->getAnteriorindicador();
        $macAnteriorIndicador->formato = 'numero';
        $macAnteriorIndicador->disabled = true;
        $macAnteriorIndicador->decimales = 4;

        //****************************CALENDARIO 1 GENERAL*************************************//

        $trimestre1 = new stdClass();
        $trimestre1->id = 'ori_trimestre1';
        $trimestre1->enDiv = true;
        $trimestre1->label = '1° Trimestre';
        $trimestre1->value = $metaActividad->getOriTrimestre(1);
        $trimestre1->formato = 'numero';
        $trimestre1->disabled = true;
        $trimestre1->positivo = true;

        $trimestre2 = new stdClass();
        $trimestre2->required = true;
        $trimestre2->id = 'ori_trimestre2';
        $trimestre2->enDiv = true;
        $trimestre2->label = '2° Trimestre';
        $trimestre2->value = $metaActividad->getOriTrimestre(2);
        $trimestre2->formato = 'numero';
        $trimestre2->disabled = true;
        $trimestre2->positivo = true;

        $trimestre3 = new stdClass();
        $trimestre3->required = true;
        $trimestre3->id = 'ori_trimestre3';
        $trimestre3->enDiv = true;
        $trimestre3->label = '3° Trimestre';
        $trimestre3->value = $metaActividad->getOriTrimestre(3);
        $trimestre3->formato = 'numero';
        $trimestre3->disabled = true;
        $trimestre3->positivo = true;

        $trimestre4 = new stdClass();
        $trimestre4->required = true;
        $trimestre4->id = 'ori_trimestre4';
        $trimestre4->enDiv = true;
        $trimestre4->label = '4° Trimestre';
        $trimestre4->value = $metaActividad->getOriTrimestre(4);
        $trimestre4->formato = 'numero';
        $trimestre4->disabled = true;
        $trimestre4->positivo = true;

        $total = new stdClass();
        $total->disabled = true;
        $total->id = 'ori_total';
        $total->enDiv = true;
        $total->label = 'Total';
        $total->decimales = 4;

        //****************************CALENDARIO 1*************************************//

        $trimestre1Cal1 = new stdClass();
        $trimestre1Cal1->id = 'numA_trimestre1';
        $trimestre1Cal1->enDiv = true;
        $trimestre1Cal1->label = '1° Trimestre';
        $trimestre1Cal1->formato = 'numero';
        $trimestre1Cal1->disabled = true;
        $trimestre1Cal1->positivo = true;

        $trimestre2Cal1 = new stdClass();
        $trimestre2Cal1->required = true;
        $trimestre2Cal1->id = 'numA_trimestre2';
        $trimestre2Cal1->enDiv = true;
        $trimestre2Cal1->label = '2° Trimestre';
        $trimestre2Cal1->formato = 'numero';
        $trimestre2Cal1->disabled = true;
        $trimestre2Cal1->positivo = true;

        $trimestre3Cal1 = new stdClass();
        $trimestre3Cal1->required = true;
        $trimestre3Cal1->id = 'numA_trimestre3';
        $trimestre3Cal1->enDiv = true;
        $trimestre3Cal1->label = '3° Trimestre';
        $trimestre3Cal1->formato = 'numero';
        $trimestre3Cal1->disabled = true;
        $trimestre3Cal1->positivo = true;

        $trimestre4Cal1 = new stdClass();
        $trimestre4Cal1->required = true;
        $trimestre4Cal1->id = 'numA_trimestre4';
        $trimestre4Cal1->enDiv = true;
        $trimestre4Cal1->label = '4° Trimestre';
        $trimestre4Cal1->formato = 'numero';
        $trimestre4Cal1->disabled = true;
        $trimestre4Cal1->positivo = true;

        $totalCal1 = new stdClass();
        $totalCal1->disabled = true;
        $totalCal1->id = 'numA_total';
        $totalCal1->enDiv = true;
        $totalCal1->label = 'Total';
        $totalCal1->decimales = 4;

        //****************************CALENDARIO 2*************************************//
        $trimestre1Cal2 = new stdClass();
        $trimestre1Cal2->id = 'denA_trimestre1';
        $trimestre1Cal2->enDiv = true;
        $trimestre1Cal2->label = '1° Trimestre';
        $trimestre1Cal2->formato = 'numero';
        $trimestre1Cal2->disabled = false;
        $trimestre1Cal2->positivo = true;

        $trimestre2Cal2 = new stdClass();
        $trimestre2Cal2->required = true;
        $trimestre2Cal2->id = 'denA_trimestre2';
        $trimestre2Cal2->enDiv = true;
        $trimestre2Cal2->label = '2° Trimestre';
        $trimestre2Cal2->formato = 'numero';
        $trimestre2Cal2->disabled = true;
        $trimestre2Cal2->positivo = true;

        $trimestre3Cal2 = new stdClass();
        $trimestre3Cal2->required = true;
        $trimestre3Cal2->id = 'denA_trimestre3';
        $trimestre3Cal2->enDiv = true;
        $trimestre3Cal2->label = '3° Trimestre';
        $trimestre3Cal2->formato = 'numero';
        $trimestre3Cal2->disabled = false;
        $trimestre3Cal2->positivo = true;

        $trimestre4Cal2 = new stdClass();
        $trimestre4Cal2->required = true;
        $trimestre4Cal2->id = 'denA_trimestre4';
        $trimestre4Cal2->enDiv = true;
        $trimestre4Cal2->label = '4° Trimestre';
        $trimestre4Cal2->formato = 'numero';
        $trimestre4Cal2->disabled = true;
        $trimestre4Cal2->positivo = true;

        $totalCal2 = new stdClass();
        $totalCal2->disabled = true;
        $totalCal2->id = 'denA_total';
        $totalCal2->enDiv = true;
        $totalCal2->label = 'Total';
        $totalCal2->formato = 'numero';
        $totalCal2->decimales = 4;

        //********************************LINEA BASE Numerador Denominador****************************************************//

        $macNumerador = new stdClass();
        $macNumerador->required = true;
        $macNumerador->id = 'numerador';
        $macNumerador->enDiv = true;
        $macNumerador->label = 'Numerador';
        $macNumerador->disabled = true;
        $macNumerador->formato = 'numero';

        $macDenominador = new stdClass();
        $macDenominador->required = true;
        $macDenominador->id = 'denominador';
        $macDenominador->enDiv = true;
        $macDenominador->label = 'Denominador';
        $macDenominador->disabled = true;
        $macDenominador->formato = 'numero';

        //********************************VALOR DESEADO Y PROGRAMADO (RAZON Y TASA) *********************//

        $macDeseadoNumerador = new stdClass();
        $macDeseadoNumerador->required = true;
        $macDeseadoNumerador->id = 'deseadoNumerador';
        $macDeseadoNumerador->enDiv = true;
        $macDeseadoNumerador->label = 'Numerador';
        $macDeseadoNumerador->formato = 'numero';
        $macDeseadoNumerador->disabled = true;

        $macDeseadoDenominador = new stdClass();
        $macDeseadoDenominador->required = true;
        $macDeseadoDenominador->id = 'deseadoDenominador';
        $macDeseadoDenominador->enDiv = true;
        $macDeseadoDenominador->label = 'Denominador';
        $macDeseadoDenominador->formato = 'numero';
        $macDeseadoDenominador->disabled = true;

        //********************************RESULTADO PARCIAL *********************//
        $resParcialT1 = new stdClass();
        $resParcialT1->id = 'rpar1_trimestre1';
        $resParcialT1->enDiv = true;
        $resParcialT1->label = '1° Trimestre';
        $resParcialT1->disabled = true;
        $resParcialT1->decimales = 4;

        $resParcialT2 = new stdClass();
        $resParcialT2->id = 'rpar2_trimestre2';
        $resParcialT2->enDiv = true;
        $resParcialT2->label = '2° Trimestre';
        $resParcialT2->disabled = true;
        $resParcialT2->decimales = 4;

        $resParcialT3 = new stdClass();
        $resParcialT3->id = 'rpar3_trimestre3';
        $resParcialT3->enDiv = true;
        $resParcialT3->label = '3° Trimestre';
        $resParcialT3->disabled = true;
        $resParcialT3->decimales = 4;

        $resParcialT4 = new stdClass();
        $resParcialT4->id = 'rpar4_trimestre4';
        $resParcialT4->enDiv = true;
        $resParcialT4->label = '4° Trimestre';
        $resParcialT4->disabled = true;
        $resParcialT4->decimales = 4;

        $resParcialTo = new stdClass();
        $resParcialTo->id = 'totalParcialA';
        $resParcialTo->enDiv = true;
        $resParcialTo->label = 'Total';
        $resParcialTo->disabled = true;
        $resParcialTo->decimales = 4;
        //********************************FIN RESULTADO PARCIAL *********************//

        $macT0 = new stdClass();
        $macT0->required = true;
        $macT0->id = 't0';
        $macT0->enDiv = true;
        $macT0->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;
        $macT0->maxlength = 4;
        $macT0->formato = 'entero';
        $macT0->label = 'AÑO T0';
        $macT0->disabled = true;

        $macT0Valor = new stdClass();
        $macT0Valor->required = true;
        $macT0Valor->id = 't0Valor';
        $macT0Valor->enDiv = true;
        $macT0Valor->formato = 'numero';
        $macT0Valor->label = 'VALOR AÑO T0';
        $macT0Valor->disabled = true;

        $macT1 = new stdClass();
        $macT1->required = true;
        $macT1->id = 't1';
        $macT1->enDiv = true;
        $macT1->value = !empty($metaActividad->getAnio()) ? $metaActividad->getAnio() : APP_EJERCICIO;
        $macT1->maxlength = 4;
        $macT1->formato = 'entero';
        $macT1->label = 'AÑO T1';
        $macT1->disabled = true;

        $macT1Valor = new stdClass();
        $macT1Valor->required = true;
        $macT1Valor->id = 't1Valor';
        $macT1Valor->enDiv = true;
        $macT1Valor->formato = 'numero';
        $macT1Valor->label = 'VALOR AÑO T1';
        $macT1Valor->disabled = true;

        //*********************************************************************************************//
        //semestral
        if ($historicoMeta->getOriginal() == 0) {
            $trimestre1->value = $historicoMeta->getModificadoT1();
            $trimestre2->value = $historicoMeta->getModificadoT2();
            $trimestre3->value = $historicoMeta->getModificadoT3();
            $trimestre4->value = $historicoMeta->getModificadoT4();
        }

        if ($this->prm->tri_id == 0) {
            $trimestre1Cal1->value = $historicoMeta->getNumeradorT1();
            $trimestre2Cal1->value = $historicoMeta->getNumeradorT2();
            $trimestre3Cal1->value = $historicoMeta->getNumeradorT3();
            $trimestre4Cal1->value = $historicoMeta->getNumeradorT4();
        }

        if ($this->prm->tri_id > 0) {
            //Durante el ejercicio se tomará el alcanzado
            $this->setSql('
                SELECT cma.pii_id as pii, cma.epg_id as epg,
                (
                    SELECT mac_alcanzado FROM cat_MetaActividadTrimestre cmat 
                    WHERE cmat.pii_id = pii AND cmat.epg_id = epg AND cmat.tri_id = 1
                ) as trim1,
                (
                    SELECT mac_alcanzado FROM cat_MetaActividadTrimestre cmat 
                    WHERE cmat.pii_id = pii AND cmat.epg_id = epg AND cmat.tri_id = 2
                ) as trim2,
                (
                    SELECT mac_alcanzado FROM cat_MetaActividadTrimestre cmat 
                    WHERE cmat.pii_id = pii AND cmat.epg_id = epg AND cmat.tri_id = 3
                ) as trim3,
                (
                    SELECT mac_alcanzado FROM cat_MetaActividadTrimestre cmat 
                    WHERE cmat.pii_id = pii AND cmat.epg_id = epg AND cmat.tri_id = 4
                ) as trim4
                FROM cat_MetaActividad as cma
                where cma.macm_id  = ?;
            ');
            $this->consultar([$this->prm->macm_id]);
            if ($this->getNumeroRegistros() > 0) {
                $trim = $this->extraer();
                $trimestre1Cal1->value = $trim['trim1'];
                $trimestre2Cal1->value = $trim['trim2'];
                $trimestre3Cal1->value = $trim['trim3'];
                $trimestre4Cal1->value = $trim['trim4'];
            } else {
                $trimestre1Cal1->value = 0.0000;
                $trimestre2Cal1->value = 0.0000;
                $trimestre3Cal1->value = 0.0000;
                $trimestre4Cal1->value = 0.0000;
            }
        }

        $trimestre1Cal2->value = $historicoMeta->getDenominadorT1();
        $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();
        $trimestre3Cal2->value = $historicoMeta->getDenominadorT3();
        $trimestre4Cal2->value = $historicoMeta->getDenominadorT4();

        if ($datos['tiv_id'] == 1) {
            $macDeseadoNumerador->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value, 4);
        } elseif ($datos['tiv_id'] == 2) {
            $macDeseadoNumerador->value = number_format($trimestre4Cal1->value, 4);
            $macDeseadoDenominador->value = number_format($trimestre4Cal2->value, 4);
        }

        $macT1Valor->value = number_format($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value, 4);
        $lb = '';
        $lbValor = '';
        $lbIndicador = '';

        $anterior = '';
        $anteriorValor = '';
        $anteriorIndicador = '';

        $original = '';

        $numerador = '';
        $denominador = '';
        $deseadoNumerador = '';
        $deseadoDenominador = '';
        $t0 = '';
        $t0Valor = '';
        $t1 = '';
        $t1Valor = '';

        $trim1Cal1 = $trim2Cal1 = $trim3Cal1 = $trim4Cal1 = '';
        $trim1Cal2 = $trim2Cal2 = $trim3Cal2 = $trim4Cal2 = '';
        $tCal2 = $resParcial1 = $resParcial2 = $resParcial3 = $resParcial4 = $totalParcial = '';
        $trim1 = $trim2 = $trim3 = $trim4 = '';
        $legendCal1 = $legendCal2 = $legendCalT = '';

        switch ($umi) {
            case 2:
            case 3:
            case 4: // PORCENTAJE, PROMEDIO Y RAZÓN
                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {
                    if ($umi == 2) {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format((($trimestre1Cal1->value / $trimestre1Cal2->value) * 100), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format((($trimestre2Cal1->value / $trimestre2Cal2->value) * 100), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format((($trimestre3Cal1->value / $trimestre3Cal2->value) * 100), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format((($trimestre4Cal1->value / $trimestre4Cal2->value) * 100), 4) : '0');
                    } else {
                        $resParcialT1->value = (($trimestre1Cal2->value != 0) ? number_format(($trimestre1Cal1->value / $trimestre1Cal2->value), 4) : '0');
                        $resParcialT2->value = (($trimestre2Cal2->value != 0) ? number_format(($trimestre2Cal1->value / $trimestre2Cal2->value), 4) : '0');
                        $resParcialT3->value = (($trimestre3Cal2->value != 0) ? number_format(($trimestre3Cal1->value / $trimestre3Cal2->value), 4) : '0');
                        $resParcialT4->value = (($trimestre4Cal2->value != 0) ? number_format(($trimestre4Cal1->value / $trimestre4Cal2->value), 4) : '0');

                    }

                    if ($datos['tiv_id'] == 1) {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($macOriginal->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($macOriginal->value), 4);
                            $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        }
                    } else {
                        if ($umi == 2) {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4) . '%';
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        } else {
                            $resParcialTo->value = number_format(($resParcialT4->value), 4);
                            $totalCal1->value = number_format(($trimestre4Cal1->value), 4);
                            $totalCal2->value = number_format(($trimestre4Cal2->value), 4);
                        }

                    }
                } else {
                    if ($umi == 2) {
                        $resParcialT1->value = '0%';
                        $resParcialT2->value = '0%';
                        $resParcialT3->value = '0%';
                        $resParcialT4->value = '0%';
                        $resParcialTo->value = '0%';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    } else {
                        $resParcialT1->value = '0';
                        $resParcialT2->value = '0';
                        $resParcialT3->value = '0';
                        $resParcialT4->value = '0';
                        $resParcialTo->value = '0';
                        $totalCal1->value = '0';
                        $totalCal2->value = '0';
                    }
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALOR DESEADO';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>Numerador</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>Denominador</small></legend></td>
                    </tr>
                ';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';
                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;

                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor LB';
                $macLbIndicador->disabled = true;
                $macOriginal->label = APP_EJERCICIO;
                $macOriginal->disabled = true;

                $macNumerador->value = $historicoMeta->getNumerador();
                $macDenominador->value = $historicoMeta->getDenominador();
                $trimestre2Cal2->value = $historicoMeta->getDenominadorT2();

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $numerador = '<td>' . $this->ponInput($macNumerador) . '</td>';
                $denominador = '<td>' . $this->ponInput($macDenominador) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';

                $deseadoNumerador = '<td>' . $this->ponInput($macDeseadoNumerador) . '</td>';
                $deseadoDenominador = '<td>' . $this->ponInput($macDeseadoDenominador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';

                break;
            case 5: // TASA

                if ($trimestre1Cal1->value != null && $trimestre2Cal1->value != null && $trimestre3Cal1->value != null && $trimestre4Cal1->value != null && $trimestre1Cal2->value != null &&
                    $trimestre2Cal2->value != null && $trimestre3Cal2->value != null && $trimestre4Cal2->value != null) {

                    $resParcialT1->value = (($trimestre1Cal1->value != 0 || $trimestre1Cal2->value != 0) ? number_format(((($trimestre1Cal1->value / $trimestre1Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT2->value = (($trimestre2Cal1->value != 0 || $trimestre2Cal2->value != 0) ? number_format(((($trimestre2Cal1->value / $trimestre2Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT3->value = (($trimestre3Cal1->value != 0 || $trimestre3Cal2->value != 0) ? number_format(((($trimestre3Cal1->value / $trimestre3Cal2->value) - 1) * 100), 4) : '0');
                    $resParcialT4->value = (($trimestre4Cal1->value != 0 || $trimestre4Cal2->value != 0) ? number_format(((($trimestre4Cal1->value / $trimestre4Cal2->value) - 1) * 100), 4) : '0');

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($trimestre1Cal1->value + $trimestre2Cal1->value + $trimestre3Cal1->value + $trimestre4Cal1->value), 4);
                        $totalCal2->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                        $resParcialTo->value = (($totalCal1->value != 0 || $totalCal2->value != 0) ? number_format(((($totalCal1->value / $totalCal2->value) - 1) * 100), 4) . '%' : '0%');
                        $macT0Valor->value = number_format(($trimestre1Cal2->value + $trimestre2Cal2->value + $trimestre3Cal2->value + $trimestre4Cal2->value), 4);
                    } else {
                        $resParcialTo->value = $resParcialT4->value . '%';
                        $totalCal1->value = (number_format(($trimestre4Cal1->value), 4));
                        $totalCal2->value = (number_format(($trimestre4Cal2->value), 4));
                        $macT0Valor->value = (number_format(($trimestre4Cal2->value), 4));
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $totalCal1->value = '0';
                    $totalCal2->value = '0';
                }
                $legendp = 'LÍNEA BASE';
                $legend = 'VALORES PROGRAMADOS';
                $legendCal1 = '
                    <tr>
                        <td colspan=5><legend><small>T1</small></legend></td>
                    </tr>
                ';
                $legendCal2 = '
                    <tr>
                        <td colspan=5><legend><small>T0</small></legend></td>
                    </tr>
                ';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';

                $macLb->value = !empty($metaActividad->getLb()) ? $metaActividad->getLb() : APP_EJERCICIO;
                $macAnterior->value = !empty($metaActividad->getAnterior()) ? $metaActividad->getAnterior() : APP_EJERCICIO;

                $macLb->label = 'AÑO T-1';
                $macLbValor->label = 'VALOR AÑO T-1';
                $macAnterior->label = 'AÑO T0';
                $macAnteriorValor->label = 'VALOR AÑO T0';
                $macLbIndicador->label = 'VALOR INDICADOR';
                $macAnteriorIndicador->label = 'VALOR INDICADOR';
                $macOriginal->label = 'VALOR AÑO T1';

                $macLbIndicador->disabled = true;
                $macOriginal->disabled = true;
                $macAnteriorIndicador->disabled = true;

                $anterior = '<td>' . $this->ponInput($macAnterior) . '</td>';
                $anteriorValor = '<td>' . $this->ponInput($macAnteriorValor) . '</td>';
                $anteriorIndicador = '<td>' . $this->ponInput($macAnteriorIndicador) . '</td>';

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $lbValor = '<td>' . $this->ponInput($macLbValor) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';

                $t0 = '<td>' . $this->ponInput($macT0) . '</td>';
                $t0Valor = '<td>' . $this->ponInput($macT0Valor) . '</td>';
                $t1 = '<td>' . $this->ponInput($macT1) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1Cal1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2Cal1) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3Cal1) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4Cal1) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $trim1Cal2 = '<td>' . $this->ponInput($trimestre1Cal2) . '</td>';
                $trim2Cal2 = '<td>' . $this->ponInput($trimestre2Cal2) . '</td>';
                $trim3Cal2 = '<td>' . $this->ponInput($trimestre3Cal2) . '</td>';
                $trim4Cal2 = '<td>' . $this->ponInput($trimestre4Cal2) . '</td>';
                $tCal2 = '<td>' . $this->ponInput($totalCal2) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($resParcialTo) . '</td>';
                break;
            default:
                if ($trimestre1->value != null && $trimestre2->value != null && $trimestre3->value != null && $trimestre4->value != null) {
                    $resParcialT1->value = number_format(($trimestre1->value), 4);
                    $resParcialT2->value = number_format(($trimestre2->value), 4);
                    $resParcialT3->value = number_format(($trimestre3->value), 4);
                    $resParcialT4->value = number_format(($trimestre4->value), 4);

                    if ($datos['tiv_id'] == 1) {
                        $totalCal1->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT1->value + $resParcialT2->value + $resParcialT3->value + $resParcialT4->value), 4);
                    } else {
                        $totalCal1->value = number_format(($resParcialT4->value), 4);
                        $total->value = number_format(($resParcialT4->value), 4);
                    }
                    $resParcialT1->value .= '%';
                    $resParcialT2->value .= '%';
                    $resParcialT3->value .= '%';
                    $resParcialT4->value .= '%';
                } else {
                    $resParcialT1->value = '0%';
                    $resParcialT2->value = '0%';
                    $resParcialT3->value = '0%';
                    $resParcialT4->value = '0%';
                    $resParcialTo->value = '0%';
                    $total->value = '0%';
                }
                $eanio = 'Año';
                $legendp = 'Línea Base';
                $legend = 'Valor Programado Anual';
                $legendCalT = '
                    <tr>
                        <td colspan=5><legend><small>Resultado parcial</small></legend></td>
                    </tr>
                ';
                $macLb->label = 'Año';
                $macLbIndicador->label = 'Valor';

                $macOriginal->disabled = true;

                $lb = '<td>' . $this->ponInput($macLb) . '</td>';
                $lbIndicador = '<td>' . $this->ponInput($macLbIndicador) . '</td>';
                $original = '<td>' . $this->ponInput($macOriginal) . '</td>';

                $trim1Cal1 = '<td>' . $this->ponInput($trimestre1) . '</td>';
                $trim2Cal1 = '<td>' . $this->ponInput($trimestre2) . '</td>';
                $trim3Cal1 = '<td>' . $this->ponInput($trimestre3) . '</td>';
                $trim4Cal1 = '<td>' . $this->ponInput($trimestre4) . '</td>';
                $tCal1 = '<td>' . $this->ponInput($totalCal1) . '</td>';

                $resParcial1 = '<td>' . $this->ponInput($resParcialT1) . '</td>';
                $resParcial2 = '<td>' . $this->ponInput($resParcialT2) . '</td>';
                $resParcial3 = '<td>' . $this->ponInput($resParcialT3) . '</td>';
                $resParcial4 = '<td>' . $this->ponInput($resParcialT4) . '</td>';
                $totalParcial = '<td>' . $this->ponInput($total) . '</td>';

                break;
        }

        $this->mixHtml('
            <div id="myModalMetaIndicador" class="modal ">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalMetaIndicadorLabel">Meta de Indicador ' . $this->prm->ind_id . ' de nivel Actividad</h4>
                            <button id="close" type="button" class="btn-close" data-dismiss="modal" aria-hidden="true"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                             <fieldset class="col-md-12 col-xs-12">
                                <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DEL INDICADOR</label>
                                ' . $this->ponTextArea($descripcion) . '
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12">
                                <label for="ind_nombre" class="col-sm-3 control-label">NOMBRE DE LA VARIABLE</label>
                                ' . $this->ponTextArea($variable) . '
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12">
                                <label for="ind_nombre" class="col-sm-3 control-label">MÉTODO DE CÁLCULO</label>
                                ' . $this->ponTextArea($metodocal) . '
                            </fieldset>
                            <fieldset class="col-md-12 col-xs-12">
                                <label for="ind_nombre" class="col-sm-3 control-label">UNIDAD DE MEDIDA</label>
                                ' . $this->ponTextArea($medida) . '
                            </fieldset>
                            <fieldset class="col-md-8 col-xs-8">
                                <legend><small>' . $legendp . '</small></legend>
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            ' . $lb . $lbValor . $anterior . $anteriorValor . $numerador . $denominador . $lbIndicador . '
                                        </tr>
                                    </tbody>
                                </table>
                                <legend><small>' . $legend . '</small></legend>
                                <table class="table table-bordered table-striped ">
                                    <tbody>
                                        <tr>
                                            ' . $deseadoNumerador . $deseadoDenominador . $t0 . $t0Valor . $t1 . $original . $anteriorIndicador . '
                                        </tr>
                                    </tbody>
                                </table>
                            </fieldset>

                            <table class="table table-bordered table-striped ">
                                <tbody>
                                    ' . $legendCal1 . '
                                    <tr>
                                        ' . $trim1Cal1 . $trim2Cal1 . $trim3Cal1 . $trim4Cal1 . $tCal1 . '
                                    </tr>
                                    ' . $legendCal2 . '
                                    <tr>
                                        ' . $trim1Cal2 . $trim2Cal2 . $trim3Cal2 . $trim4Cal2 . $tCal2 . '
                                    </tr>
                                    ' . $legendCalT . '
                                    <tr>
                                        ' . $resParcial1 . $resParcial2 . $resParcial3 . $resParcial4 . $totalParcial . '
                                    </tr>

                                </tbody>
                            </table>
                            </div>
                            <div class="modal-footer">
                                <button id="cerrar" type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myModalMetaIndicador").on("hidden.bs.modal", function (e) {
                $("#myModalMetaIndicador").remove();
            });
            $("#cerrar").click(function(){ $("#myModalMetaIndicador").remove(); });
            $("#close").click(function(){ $("#myModalMetaIndicador").hide(); });
            $("#myModalMetaIndicador").modal().show();
        ');

        $this->mixScript('
            var calculoLBIndicador=0;
            var calculoVPIndicador=0;

            switch(' . $umi . '){
                case 2:case 3:case 4:    // RAZÓN
                    $("#numerador,#denominador").blur(function() {
                        if($("#denominador").val() != 0){
                            if(' . $umi . ' == 2){ //porcentaje
                               calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) ) * 100;
                            }else{ // razón
                                calculoLBIndicador=( unformatNumber($("#numerador").val()) / unformatNumber($("#denominador").val()) );
                            }
                            $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                        }else{
                            $("#lbIndicador").val(0);
                        }
                        if($("#lb").val() != ' . APP_EJERCICIO . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else if($("#lb").val() != ' . APP_EJERCICIO . ' && $("#numerador").val() != 0 && $("#denominador").val() != 0) {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });

                    //calendario 1
                    $("#numA_trimestre1,#numA_trimestre2,#numA_trimestre3,#numA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $umi . ' == 2){
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = (numATrimestre1 / denATrimestre1)*100;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = (numATrimestre2 / denATrimestre2)*100;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = (numATrimestre3 / denATrimestre3)*100;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = (numATrimestre4 / denATrimestre4)*100;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = numATrimestre1 / denATrimestre1;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = numATrimestre2 / denATrimestre2;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = numATrimestre3 / denATrimestre3;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = numATrimestre4 / denATrimestre4;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#deseadoNumerador").val(numATotal.toFixed(4));
                            if(' . $umi . ' == 2){
                                $("#macm_original").val(((numATotal / denATotal)*100).toFixed(4));
                                $("#totalParcialA").val(((numATotal / denATotal)*100).toFixed(4) + "%");
                            } else {
                                $("#macm_original").val((numATotal / denATotal).toFixed(4));
                                $("#totalParcialA").val((numATotal / denATotal).toFixed(4));
                            }
                        } else {
                            if(' . $umi . ' == 2){
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            } else {
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4));
                            }
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#deseadoNumerador").val(numATotal.toFixed(4));
                            $("#macm_original").val(porc_trimestre4.toFixed(4));
                        }

                        if(' . $umi . ' == 2){
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                        } else {
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4));
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4));
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4));
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4));
                        }

                    });

                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $umi . ' == 2){
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = (numATrimestre1 / denATrimestre1)*100;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = (numATrimestre2 / denATrimestre2)*100;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = (numATrimestre3 / denATrimestre3)*100;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = (numATrimestre4 / denATrimestre4)*100;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        } else {
                            if($("#denA_trimestre1").val() != 0 ) {
                                porc_trimestre1 = numATrimestre1 / denATrimestre1;
                            } else {
                                porc_trimestre1 = 0;
                            }
                            if($("#denA_trimestre2").val() != 0 ) {
                                porc_trimestre2 = numATrimestre2 / denATrimestre2;
                            } else {
                                porc_trimestre2 = 0;
                            }
                            if($("#denA_trimestre3").val() != 0 ) {
                                porc_trimestre3 = numATrimestre3 / denATrimestre3;
                            } else {
                                porc_trimestre3 = 0;
                            }
                            if($("#denA_trimestre4").val() != 0 ) {
                                porc_trimestre4 = numATrimestre4 / denATrimestre4;
                            } else {
                                porc_trimestre4 = 0;
                            }
                        }

                        if(' . $datos['tiv_id'] . ' == 1){

                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            if(' . $umi . ' == 2){
                                $("#macm_original").val(((numATotal / denATotal)*100).toFixed(4));
                                $("#totalParcialA").val(((numATotal / denATotal)*100).toFixed(4) + "%");
                            } else {
                                $("#macm_original").val((numATotal / denATotal).toFixed(4));
                                $("#totalParcialA").val((numATotal / denATotal).toFixed(4));
                            }
                        } else {
                            if(' . $umi . ' == 2){
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            } else {
                                $("#totalParcialA").val(porc_trimestre4.toFixed(4));
                            }
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#deseadoDenominador").val(denATotal.toFixed(4));
                            $("#macm_original").val(porc_trimestre4.toFixed(4));
                        }

                        if(' . $umi . ' == 2){
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                        } else {
                            $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4));
                            $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4));
                            $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4));
                            $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4));
                        }
                    });

                break;
                case 5: // TASA
                    $("#anteriorValor,#lbValor").blur(function(){
                        calculoLBIndicador=((unformatNumber($("#anteriorValor").val()) / unformatNumber($("#lbValor").val()))-1) * 100;
                        $("#lbIndicador").val(calculoLBIndicador.toFixed(4));
                    });

                    //calendario 1
                    $("#numA_trimestre1,#numA_trimestre2,#numA_trimestre3,#numA_trimestre4").blur(function() {

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }

                        $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });


                    //calendario 2
                    $("#denA_trimestre1,#denA_trimestre2,#denA_trimestre3,#denA_trimestre4").blur(function(){

                        var numATrimestre1 = unformatNumber($("#numA_trimestre1").val());
                        var numATrimestre2 = unformatNumber($("#numA_trimestre2").val());
                        var numATrimestre3 = unformatNumber($("#numA_trimestre3").val());
                        var numATrimestre4 = unformatNumber($("#numA_trimestre4").val());
                        var denATrimestre1 = unformatNumber($("#denA_trimestre1").val());
                        var denATrimestre2 = unformatNumber($("#denA_trimestre2").val());
                        var denATrimestre3 = unformatNumber($("#denA_trimestre3").val());
                        var denATrimestre4 = unformatNumber($("#denA_trimestre4").val());

                        if($("#denA_trimestre1").val() != 0 ) {
                            porc_trimestre1 = (((numATrimestre1 / denATrimestre1)-1)*100);
                        } else {
                            porc_trimestre1 = 0;
                        }
                        if($("#denA_trimestre2").val() != 0 ) {
                            porc_trimestre2 = (((numATrimestre2 / denATrimestre2)-1)*100);
                        } else {
                            porc_trimestre2 = 0;
                        }
                        if($("#denA_trimestre3").val() != 0 ) {
                            porc_trimestre3 = (((numATrimestre3 / denATrimestre3)-1)*100);
                        } else {
                            porc_trimestre3 = 0;
                        }
                        if($("#denA_trimestre4").val() != 0 ) {
                            porc_trimestre4 = (((numATrimestre4 / denATrimestre4)-1)*100);
                        } else {
                            porc_trimestre4 = 0;
                        }

                        numATotal = numATrimestre1 + numATrimestre2 + numATrimestre3 + numATrimestre4;
                        denATotal = denATrimestre1 + denATrimestre2 + denATrimestre3 + denATrimestre4;

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#numA_total").val(numATotal.toFixed(4));
                            $("#denA_total").val(denATotal.toFixed(4));
                            $("#totalParcialA").val((((numATotal / denATotal)-1)*100).toFixed(4) + "%");
                            $("#macm_original").val(numATotal.toFixed(4));
                            $("#anteriorIndicador").val((((numATotal / denATotal)-1)*100).toFixed(4));
                            $("#t0Valor").val(denATotal.toFixed(4));
                        } else {
                            $("#totalParcialA").val(porc_trimestre4.toFixed(4) + "%");
                            $("#numA_total").val(numATrimestre4.toFixed(4));
                            $("#denA_total").val(denATrimestre4.toFixed(4));
                            $("#macm_original").val(numATrimestre4.toFixed(4));
                            $("#anteriorIndicador").val(porc_trimestre4.toFixed(4));
                            $("#t0Valor").val(denATrimestre4.toFixed(4));
                        }
                        $("#rpar1_trimestre1").val(porc_trimestre1.toFixed(4) + "%");
                        $("#rpar2_trimestre2").val(porc_trimestre2.toFixed(4) + "%");
                        $("#rpar3_trimestre3").val(porc_trimestre3.toFixed(4) + "%");
                        $("#rpar4_trimestre4").val(porc_trimestre4.toFixed(4) + "%");
                    });

                break;
                default: // todas las demás unidades de medida del indicador

                    $("#ori_trimestre1,#ori_trimestre2,#ori_trimestre3,#ori_trimestre4").blur(function() {

                        $("#rpar1_trimestre1").val($("#ori_trimestre1").val());
                        $("#rpar2_trimestre2").val($("#ori_trimestre2").val());
                        $("#rpar3_trimestre3").val($("#ori_trimestre3").val());
                        $("#rpar4_trimestre4").val($("#ori_trimestre4").val());

                        if(' . $datos['tiv_id'] . ' == 1){
                            $("#numA_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val())+ unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#ori_total").val(formatNumber(unformatNumber($("#ori_trimestre1").val()) + unformatNumber($("#ori_trimestre2").val()) + unformatNumber($("#ori_trimestre3").val()) + unformatNumber($("#ori_trimestre4").val())));
                            $("#macm_original").val( formatNumber( unformatNumber( $("#ori_trimestre1").val() ) + unformatNumber( $("#ori_trimestre2").val() ) + unformatNumber( $("#ori_trimestre3").val() ) + unformatNumber( $("#ori_trimestre4").val() ) ) );
                        } else {
                            $("#ori_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#numA_total").val(unformatNumber($("#ori_trimestre4").val()));
                            $("#macm_original").val(unformatNumber($("#ori_trimestre4").val()));
                        }
                    });

                    break;
            }
                    $("#lb").blur(function(){
                        if($("#lb").val() != ' . APP_EJERCICIO . ') {
                            $("#numerador").attr("disabled",false);
                            $("#denominador").attr("disabled",false);
                        }
                    });

                    if($("#lb").val() == ' . APP_EJERCICIO . ') {
                        $("#numerador").attr("disabled",true);
                        $("#denominador").attr("disabled",true);
                        $("#numerador").val(0);
                        $("#denominador").val(0);
                    }

                    $("#saveMeta").click(function(){
                        if($("#lb").val() != ' . APP_EJERCICIO . ' && $("#numerador").val() == 0 && $("#denominador").val() == 0) {
                            $("#saveMeta").attr("disabled",true);
                            alertify.alert("IMPORTANTE", "El valor de la Linea Base no puede ser 0, escriba una meta mayor", 
                                function(){}
                            ).set("labels", {ok:"Aceptar"});
                        } else {
                            $("#saveMeta").attr("disabled",false);
                        }
                    });
        ');
    }

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
        $eoa = 0;
        $actividad = new ObraActividad();
        $actividad->setGpo_id($this->clasAdm_gpo);
        $actividad->setUr_id($this->clasAdm_ur);
        $actividad->setPrg_id($this->clasAdm_prg);
        $actividad->setSpr_id($this->clasAdm_spr);
        $actividad->setPy_id($this->clasAdm_py);
        if (!empty($this->prm->oa_id)) {
            $oa->setOa_id($this->prm->oa_id);
            $oa->select();
            $eoa = $actividad->getToa_id();
        }

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

            //Verificamos si ya se registro la meta en cat_MetaActividad
            if ($this->getNumeroRegistros() > 0) {
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

                //Si estamos durante el ejercicio, apartir del 2026-01-01, la meta capturada antes del 2025-12-31 no se puede eliminar
                if (($ejercicio && !$eliminar) || ($poa && !$eliminar)) {
                    $this->mixScript('
                    notify("' . $mensaje . '", "warning",
                    );');
                }

                //Verifica si tiene adecuacion de metas
                $validaRegistrosDeFolios = $this->verificaModificacionMetas($epg, 'eliminar');
                if (!$validaRegistrosDeFolios['sinRegistros']) {
                    $eliminar = false;
                    $mensaje = $validaRegistrosDeFolios['mensaje'];
                }
            } else {

                $eliminar = false;
                //Se elimina la meta del temporal
                $this->conectar();
                $params = [$epg, $pii];
                $this->setSql('
                    DELETE FROM vin_ObraMir
                    WHERE epg_id = ?
                        AND pii_id = ?;
                ');
                $this->consultar($params);
                $this->desconectar();

                $this->mixScript('
                    $("#indicador"+' . $ind_id . ').remove();
                    notify("Meta eliminada", "success",
                    );
                ');
            }

            //Elimina la meta de cat_MetaActividad
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

                $this->conectar();
                $this->setSql('SELECT pii_id,epg_id FROM vin_ObraMir WHERE epg_id = :epg
                ');
                $this->consultar(['epg' => $epg]);
                $trimestreMeta = (int)$this->trimestreActual();
                while ($r = $this->extraer()) {
                    $observacion = 'Elimina meta actividad desde pantalla A_2_2';
                    $params = [':observacion' => $observacion,':ip' => $this->ip, ':usr' => $this->usr_id];
                    $this->conectar();
                    $this->setSql('
                        SET @bit_observacion = :observacion, 
                        @usr_ip = INET_ATON(:ip), @usr_id = :usr
                        ' . $actualizaAgrupadorPresupuestal . ';');
                    $this->consultar($params);
                    $this->setSql('CALL sp_RegistroMetaActividad' . APP_EJERCICIO . '(?, ?, ?, ?, "D", ?, @usr_ip, @usr_id);');
                    $this->consultar([$this->prm->gpo_id,$this->prm->ur_id,$r['pii_id'],$epg,$trimestreMeta]);
                    $this->setSql('SELECT @banderaPoa, @mensajePoa;');
                    $this->consultar();
                    $res = $this->extraer();
                    $this->mixScript('
                    notify("' . $res['@mensajePoa'] . '","'
                        . ($res['@banderaPoa'] ? 'success' : 'error') . '")
                    ');

                    if (!$res['@banderaPoa']) {
                        return false;
                    } else {
                        $this->mixScript('
                            $("#indicador"+' . $ind_id . ').remove();
                        ');
                    }
                }
            }
        } else {
            $this->mixScript('
                $("#indicador"+' . $ind_id . ').remove();
            ');
        }
    }

    //Valida meta
    private function validaMeta()
    {
        $gpo = $this->clasAdm_gpo;
        $ur = $this->clasAdm_ur;
        $indicador = (int) $this->prm->ind_id;
        $epg_id = $this->prm->epg_id;
        $params = [$gpo,$ur,$indicador];
        $encuadres = '';
        $this->setSql('
            SELECT concat(prg_id,"-",spr_id,"-",py_id,"-",oa_id) as encuadre
            FROM enc_Programatico
            INNER JOIN vin_ObraMir using (epg_id)
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

            $mensaje = 'La meta MIR del indicador ' . $indicador . ' ya se encuentra registrado en la estructura ' . $encuadres . ' de la UR ' . $this->clasAdm_gpo . $this->clasAdm_ur . ', no se permite vincular de nuevo.';
            $this->mixScript('
                alertify.alert("' . $mensaje . '");
                $("#indicador"+' . $indicador . ').remove();
            ');
        } else {
            $metaRegistrada = false;
            $agregaMeta = true;
            //Verificamos si existen metas registradas
            $this->setSql('
                SELECT pdi_id 
                FROM vin_ObraMir
                INNER JOIN vin_ProductoIntermedioIndicador using (pii_id)
                WHERE epg_id = ?
                GROUP BY pdi_id;
            ');
            $this->conectar();
            $this->consultar([$epg_id]);
            $numR = $this->getNumeroRegistros();
            if ($numR > 0) {
                $metaRegistrada = true;
                $reg = $this->extraer();
                $actividadMirRegistrada = (int) $reg['pdi_id'];
            }

            //Verificamos la actividad MIR a registrar
            $this->setSql('
                SELECT pdi_id 
                FROM vin_ProductoIntermedioIndicador
                WHERE pii_id = ?
            ');
            $this->conectar();
            $this->consultar([$this->prm->pii_id]);
            $numR = $this->getNumeroRegistros();
            if ($numR > 0) {
                $reg = $this->extraer();
                $actividadMir = (int) $reg['pdi_id'];
            }

            if ($metaRegistrada) {
                if ($actividadMirRegistrada != $actividadMir) {
                    $agregaMeta = false;

                    $this->mixScript('
                        $("#indicador"+' . $indicador . ').remove();
                        notify("ESTE TIPO DE OBRA NO PUEDE TENER MAS DE UN PRODUCTO INTERMEDIO", "error", );
                    ');
                }

            }

            if ($agregaMeta) {
                $this->mixScript('
                    comunica("poa/programacion/ControladorActividades.php",{
                        metodoCtrlAct:"agregaMeta",
                        epg_id:"' . $this->prm->epg_id . '",
                        ind_id: "' . $this->prm->ind_id . '",
                        pii_id:"' . $this->prm->pii_id . '",
                        macm_id:"' . $this->prm->macm_id . '",
                    });
                ');
            }
        }

    }

    //Agrega la meta de a la vinculacion obra mir
    private function agregaMeta()
    {
        $error = false;
        $gpo = $this->clasAdm_gpo;
        $ur = $this->clasAdm_ur;
        $epg = $this->prm->epg_id;
        $pii = $this->prm->pii_id;
        $ind_id = $this->prm->ind_id;
        $macm_id = $this->prm->macm_id;

        $params = [$pii,$epg,$macm_id,$_SESSION[_S]['usr_id']];
        //Verifica si ya esta marcada alguna meta
        $this->conectar();
        $this->setSql(
            'INSERT INTO vin_ObraMir (pii_id,epg_id,macm_id,usr_id) VALUES(?, ?, ?, ?);'
        );
        $this->consultar($params);

        if ($this->getFilasAfectadas() > 0) {
            $this->mixScript('
                $("#metasActividad").modal().remove();
                $("#indicador"+' . $ind_id . ').removeAttr("hidden");
                notify("Meta agregada", "success",
                );
            ');
        } else {
            //Elimina la meta de la vista
            $this->mixScript('
                $("#indicador"+' . $ind_id . ').remove();
                notify(" ' . $mensaje . '", "error",
                );
            ');
        }
    }

    public function capturaTrimestre($trimestre, $epg_id, $pii_id, $campo = 'mact_capturaAvance')
    {
        $this->conectar();
        $params = [
            ':epg' => $epg_id,
            ':pii' => $pii_id,
            ':tri' => $trimestre,
        ];
        $this->setSql('
            SELECT ' . $campo . ' FROM cat_MetaActividadTrimestre
            WHERE epg_id = :epg
            AND pii_id = :pii
            AND tri_id = :tri ;
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

    public function estadoTrimestre($trimestre, $epg_id, $pii_id, $campo = 'e.etr_id1')
    {
        $this->conectar();
        $params = [
            ':epg' => $epg_id,
            ':pii' => $pii_id,
            ':tri' => $trimestre,
        ];
        $this->setSql('
            SELECT ' . $campo . ' FROM cat_MetaActividadTrimestre
            INNER JOIN enc_Programatico using(epg_id)
            INNER JOIN mir_TrimestreUr e using(gpo_id, ur_id)
            WHERE epg_id= :epg AND pii_id = :pii
            AND e.tri_id = :tri
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

    private function getPdfid($prm)
    {
        $this->conectar();
        $params = [
            ':grupo'       => $this->clasAdm_gpo,
            ':ur'          => $this->clasAdm_ur,
            ':programa'    => $this->clasAdm_prg,
            ':subPrograma' => $this->clasAdm_spr,
        ];
        $this->setSql('
            SELECT pdf_id FROM
            cat_Subprograma
            WHERE gpo_id = :grupo
                AND ur_id = :ur
                AND prg_id = :programa
                AND spr_id = :subPrograma
            ');
        $this->consultar($params);
        $r = $this->extraer();
        $this->desconectar();
        return $r['pdf_id'];
    }

    private function UrPrograma()
    {
        $this->conectar();
        $params = [
            ':grupo'    => $this->clasAdm_gpo,
            ':ur'       => $this->clasAdm_ur,
            ':programa' => $this->clasAdm_prg,
        ];
        $this->setSql('
            SELECT upm_id FROM
            vin_UrProgramaMir
            WHERE gpo_id = :grupo AND ur_id = :ur AND prgs_id = :programa;
        ');
        $this->consultar($params);
        $r = $this->extraer();
        $this->desconectar();
        return $r['upm_id'];
    }

    private function muestraPlanInstitucional()
    {
        $this->setContenedor('divContenedorGeneralModal');

        $modal = '<div id="myModal" class="modal" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">Plan Institucional</h4>
                        <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="PlanIstitucional">
                        <div class="row">
                        ' . Pin::ponPin() . '
                        </form>
                    </div>
                    <div class="modal-footer">';

        if (!isset($this->prm->lectura)) {
            $modal .= '<button type="submit" class="btn btn-outline-success" id="btnGuardarPlanI" form="">Aceptar</button>';
        }

        $modal .= '<button type="button" class="btn btn-outline-danger" id="cancelaPI" data-dismiss="modal">Cancelar</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>';
        $this->mixHtml($modal);

        $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : '';

        $this->mixScript('
            $("#myModal").modal().show();
            $("#myModal").on("hidden.bs.modal", function (e) {$("#myModal").modal().remove();});
            $(".cerrar").click(function(){ $("#myModal").modal().remove(); });
            $("#cancelaPI").click(function(){ $("#myModal").modal().remove(); });

            $("#btnGuardarPlanI").click(function(){
                $("#modalPI").empty();
                if ($("#objpi_id").val() != "") {
                    comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct: "agregaPi"' . $epg_id . '});
                    $("#myModal").modal().remove();
                }
            });
        ');
    }

    private function agregaPi()
    {
        $this->prm->epi_id = $_SESSION[_S]['pin']->epi;
        if ($this->prm->epi_id) {
            $epg_id = (isset($this->prm->epg_id)) ? ',epg_id:' . $this->prm->epg_id . '' : '';
            $a = new AccesoDatos();
            $a->conectar();
            $a->setSql('SELECT epi_id,pi_nombre, Descripcion
                FROM enc_PlanInstitucional epi JOIN  cat_ObjetivoPI  USING (pi_id, objpi_id)
                JOIN cat_PlanInstitucional USING (pi_id) WHERE epi_id = ?;');
            $params = [$this->prm->epi_id];
            $a->consultar($params);
            $a->desconectar();
            $datos = $a->extraer();

            $boton = new stdClass();
            $boton->title = 'Eliminar Plan Institucional';
            $boton->id = 'epin_' . $this->prm->epi_id;
            $boton->color = 'rojo';
            $boton->icon = 'trash-can';
            if ($this->prm->prgs_id) {
                $boton->click = '';
            }
            $fila = '
                $("#filasPlanI").append(
                    $("<tr>").attr("id","filasPlanI-' . $this->prm->epi_id . '")
                    .attr("class","registro")
                    .append("<td>' . $datos['pi_nombre'] . '</td>")
                    .append("<td>' . $datos['Descripcion'] . '</td>")
                    .append("<td>' . addslashes($this->ponBoton($boton)) . '</td>")
                );';
            $this->mixScript('
                if(ArraysObjetivosPi.indexOf("05") < 0){
                    if(($("#filasPlanI-01").length ==1 || $("#filasPlanI-02").length ==1 || $("#filasPlanI-03").length ==1 || $("#filasPlanI-04").length ==1)){
                        $("#botonPIPlus").attr("disabled","disabled");
                        for(var i = ArraysObjetivosPi.length - 1; i >= 0; i--) {
                            $(".registro").remove();
                            ArraysObjetivosPi.splice(i, 1);
                            encPI.splice(i, 1);
                        }
                        ArraysObjetivosPi.push("' . $this->prm->epi_id . '");
                        encPI.push({"epi_id":"' . $this->prm->epi_id . '","plan":"' . $datos['pi_nombre'] . '","objetivo":"' . $datos['Descripcion'] . '"});
                        ' . $fila . '


                    }else{
                        if(ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '") < 0 ){
                                ArraysObjetivosPi.push("' . $this->prm->epi_id . '");
                                encPI.push({"epi_id":"' . $this->prm->epi_id . '","plan":"' . $datos['pi_nombre'] . '","objetivo":"' . $datos['Descripcion'] . '"});
                                    ' . $fila . '
                                $("#epin_' . $this->prm->epi_id . '").click(function(){
                                    $("#filasPlanI-' . $this->prm->epi_id . '").remove();
                                    encPI.splice( ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '"),1);
                                    ArraysObjetivosPi.splice( ArraysObjetivosPi.indexOf("' . $this->prm->epi_id . '"),1);
                                    comunica("poa/programacion/ControladorActividades.php",{metodoCtrlAct:"eliminaPi",epi_id:"' . $this->prm->epi_id . ',' . $epg_id . '"});
                                });

                        }else{
                            alertify.alert("","<h5>El Plan ya está agregado</h5>");
                            }
                    }
                }else {$("#botonPIPlus").attr("disabled","disabled");}
            ');
            if (isset($this->prm->epg_id) && $this->prm->epg_id != '') {
                $sql = 'INSERT INTO vin_ProgramaticoInstitucional (epg_id,epi_id, usr_id) VALUES(?, ?, ?);';
                $params = [$this->prm->epg_id, $this->prm->epi_id, $_SESSION[_S]['usr_id']];
                $a->conectar();
                $a->setSql($sql);
                $a->consultar($params);
            }
        }
    }

    private function eliminaPi()
    {
        if ($this->prm->epg_id) {
            $params = [$this->prm->epg_id, $this->prm->epi_id];
            $a = new AccesoDatos();
            $a->setSql('DELETE FROM vin_ProgramaticoInstitucional WHERE epg_id = ? and epi_id = ?;');
            $this->mixScript('
                $("#filasPin-' . $this->prm->epi_id . '").remove();
            ');
            $a->conectar();
            $a->consultar($params);
            $a->desconectar();
        }
    }
}
if (isset($_SESSION[_S]['prm']['A']['2']['2']['1'])) {
    if (isset($_POST['metodoCtrlAct']) || $desdeMenu) {
        new ControladorActividades();
    }
} else {
    WebResponse::sinPermisos();
}
