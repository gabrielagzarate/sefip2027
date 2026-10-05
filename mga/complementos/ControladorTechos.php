<?php

/**
 * Description of ControladorTechos
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>, ABN
 * Created: 13-Agt-2018
 * Updated: May-2024
 * Comment: -----
 * Objective: Asignarle un techo presupuestal a una unidad responsable de una fuente de financiamiento y la clase de gasto.
                Se generan reportes en Excel y PDF de los techos presupuestales asignados.
 * Regla de Negocio: -----
 * Permits: Esta pantalla tiene permisos de escritura.
 * */
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorTechos extends WebResponse
{
    protected $escritura;
    protected $usr_id;
    protected $adm_gpo;
    protected $adm_ur;
    public function __construct()
    {   //Constructor
        parent::__construct();

        $this->escritura = isset($_SESSION[_S]['prm']['M']['4']['1']['2']) ? true : false;

        //variables de session
        $this->usr_id = (isset($_SESSION[_S]['usr_id']) ? $_SESSION[_S]['usr_id'] : '');
        $this->adm_gpo = (isset($_SESSION[_S]['clasAdm-gpo']) ? $_SESSION[_S]['clasAdm-gpo'] : '');
        $this->adm_ur = (isset($_SESSION[_S]['clasAdm-ur']) ? $_SESSION[_S]['clasAdm-ur'] : '');

        switch ($this->metodo) {
            case 'ActualizaTecho':
                $this->ActualizaTecho();
                break;
            case 'llenaTablaTechos':
                $this->llenaTablaTechos();
                break;
            case 'agregarTecho':
                $this->agregarTecho();
                break;
            case 'nuevoTecho':
                $this->nuevoTecho();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    } //fin __construct

    /* -----------------------------------------------------------------------------------
    Función principal del dibujado de pantalla
    ------------------------------------------------------------------------------------------------ */
    private function main()
    {
        // Cuando cambie la ur, refrescamos la pantalla
        $this->mixScript(
            ClasificacionAdministrativa::onChangeUr('
                comunica("' . $this->pantalla . '", {"metodo":""} );
                ') . '$("#combour").attr("disabled",false);
                $("#combour").selectpicker("refresh");
                $("#clasadminue").html("");
                $("#clasadminprg").html("");
                $("#clasadminspr").html("");
                $("#clasadminpy").html("");
                $("#clasadminoa").html("");
            '
        );

        $botones = '';
        $soloLectura = true;
        // Usuarios con permiso de escritura en ésta pantalla pueden crear nuevos Techos
        if ($this->escritura == true) {
            $botones .= '
                <button id="nuevoTecho" type="button" class="btn btn-outline-primary me-1" title="Registrar un nuevo Techo Presupuestal"><i class="fa-solid fa-plus me-2"></i>Nuevo
                </button>';
            $soloLectura = false;
        }
        $botones .= '
            <button id="printTec" type="button" class="btn btn-outline-danger me-1" title="Imprimir techos"><i class="fa-solid fa-file-pdf me-2"></i>PDF
            </button>
            <button id="printTecExcel" type="button" class="btn btn-outline-success me-1" title="Imprimir techos en excel"><i class="fa-solid fa-file-excel me-2"></i>Excel
            </button>';
        $this->agregaTools($botones);

        $this->mixStyle('
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
            .close { color:black; opacity: 0.8;};
        ');

        $tabla = new stdClass();
        $tabla->id = 'tablaTechos';
        $tabla->esCompacta = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodoCtrlAct = 'llenaTablaTecho';

        $tr = [];
        $th = new stdClass();
        $th->texto = 'Gran Fuente de Financiamiento';
        $th->colspan = '2';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Clase de Gasto';
        $th->colspan = '2';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '200px';
        $th->texto = 'Techo Presupuestal';
        $th->align = 'right';
        array_push($tr, $th);
        $th = new stdClass();
        $th->width = '5px';
        $th->texto = 'Actualizar';
        array_push($tr, $th);
        $tabla->encabezado = [$tr];
        $tabla->cuerpo = [];
        $td = new stdClass();
        $td->campoTxt = 'ter_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ter_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clg_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'clg_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->tipo = 'text';
        $td->campoTxt = 'tec_techoPresupuestal';
        $td->class = 'techos form-control';
        $td->attr = new stdClass();
        $td->attr->tec_id = 'tec_id';
        if ($soloLectura == true) {
            $td->attr->disabled = $soloLectura;
        }
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->tipo = 'boton';
        $td->class = 'actualizaTechos';
        $td->icono = 'refresh';
        $td->disabled = true;
        $td->attr = new stdClass();
        $td->attr->tec_id = 'tec_id';
        if ($soloLectura == true) {
            $td->attr->disabled = $soloLectura;
        }
        array_push($tabla->cuerpo, $td);

        $fuente_financiamiento = new stdClass();
        $fuente_financiamiento->id = 'ter_id2';

        $fuente_financiamiento->sql = 'SELECT tr.ter_id,tr.ter_nombre FROM ' . BD_SINPRES . '.cat_TipoRecurso tr';
        $fuente_financiamiento->label = 'Tipo de recurso';
        $fuente_financiamiento->change = '
            comunica("' . $this->pantalla . '",{
            metodo:"llenaTablaTechos",
            ter_id2:$(this).val(),
            clg_id2:$("#clg_id2").val()
            });
        ';
        $clg = new stdClass();
        $clg->id = 'clg_id2';
        $clg->sql = 'SELECT cg.clg_id, cg.clg_nombre FROM cat_ClaseGasto cg;';
        $clg->label = 'Clase de gasto';
        $clg->change = '
            comunica("' . $this->pantalla . '",{
            metodo:"llenaTablaTechos",
            ter_id2:$("#ter_id2").val(),
            clg_id2:$(this).val()
            });
        ';

        $clg = $this->ponCombo($clg);

        $this->mixHtml(
            '<div class="tab-content">
                <div class="tab-pane active" id="prg1">
                    <div class="row">
                        <fieldset class="col-md-12">
                            <legend>' . $this->ponCombo($fuente_financiamiento) . '</legend>
                            <legend class="mb-4">
                                ' . $clg . '
                            </legend>
                            ' . $this->wrPonTabla($tabla) . '
                        </fieldset>
                    </div>
                </div>
            </div>' .
            $this->wrPonTabla($tabla) .
            '<div id ="dialogTecho"></div>'
        );
        $this->llenaTablaTechos();

        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
                //location.reload();
                $("nav").menusinpres("clickMenu");
            });
            
            $("#nuevoTecho").click( function(e) {
                comunica("' . $this->pantalla . '",{ metodo: "agregarTecho" });
            });
            
            $(".holaMundo").click(function() {
                $("#toggleCAx").click();
            });

            $("#regresarT").click(function(){
                $("nav").menusinpres("clickMenu");
            });

            $("#printTec").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/poa/rep_MgaComplementos.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ter_id").attr("value", $("#ter_id2").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "clg_id").attr("value", $("#clg_id2").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ccd_id").attr("value", 1))

                $("body").append(form);
                form.submit();
                form.remove();
            });

            $("#printTecExcel").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/mga/exp_Techos.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "ter_id").attr("value", $("#ter_id2").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "clg_id").attr("value", $("#clg_id2").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ur_id").attr("value", $("#combour").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", 1))

                $("body").append(form);
                form.submit();
                form.remove();
            });
        ');
    } // fin main
    /* --------------------------------------------------------------------------------------------------
        Llenamos los datos de techos de la UR
    ---------------------------------------------------------------------------------------------- */
    private function llenaTablaTechos()
    {
        $prm = [];
        $prm[':adm_gpo'] = (!is_null($this->adm_gpo) ? $this->adm_gpo : 'null');
        $prm[':adm_ur'] = (!is_null($this->adm_ur) ? $this->adm_ur : 'null');

        $where_additional = '';
        if (!empty($this->prm->ter_id2) && empty($this->prm->clg_id2)) {
            $prm[':ter_id2'] = $this->prm->ter_id2;
            $where_additional .= ' AND ter_id= :ter_id2 ';
        } elseif (empty($this->prm->ter_id2) && !empty($this->prm->clg_id2)) {
            $prm[':clg_id2'] = $this->prm->clg_id2;
            $where_additional .= ' AND clg_id= :clg_id2 ';
        } elseif (!empty($this->prm->ter_id2) && !empty($this->prm->clg_id2)) {
            $prm[':ter_id2'] = $this->prm->ter_id2;
            $prm[':clg_id2'] = $this->prm->clg_id2;
            $where_additional .= ' AND ter_id= :ter_id2 AND clg_id= :clg_id2';
        }

        $this->setSql('
            SELECT t.tec_id,ter.ter_id,ter.ter_nombre,clg.clg_id,clg.clg_nombre,t.tec_techoPresupuestal
            FROM cat_Techos t
            JOIN ' . BD_SINPRES . '.cat_TipoRecurso ter USING(ter_id)
            JOIN cat_ClaseGasto clg USING(clg_id)
            WHERE t.gpo_id = :adm_gpo AND 
                t.ur_id = :adm_ur 
                ' . $where_additional . '
            ORDER BY ter.ter_id, clg.clg_id;
        ');
        $this->setParams($prm);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = '999';
        $nav->idTabla = 'tablaTechos';
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".tabla-footer").remove();
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
        
            $(".techos").keypress(function(event){
                var anularTecla = false;
                var esPermitido = Array.toString().indexOf([0,8], event.which) >= 0;
                if(event.which == 46)  anularTecla = $(this).val().indexOf(".") >= 0;
                else anularTecla = ((event.which < 48 ||  event.which > 57) && !esPermitido) ;
                if( anularTecla ) event.preventDefault();
            });
            
            $(".techos").blur(function(){
                $(this).val( formatNumber( $(this).val() ,"$  ") );
            });

            $(".techos").focus(function(){
                $(this).val( unformatNumber( $(this).val() ) );
            });
            
            $(".techos").blur().css("text-align","end");
            $(".actualizaTechos").click(function(){
                comunica("' . $this->pantalla . '",{metodo:"ActualizaTecho",tec_id:$(this).attr("tec_id"), techo : unformatNumber( $(".techos[tec_id="+ $(this).attr("tec_id") + "]").val() ) });
            });
        ');
    } //fin llenaTablaTechos

    /* ------------------------------------------------------------------------------
        Actualizamos el importe de un Techo Presupuestal
    ---------------------------------------------------------------------------------------- */
    private function ActualizaTecho()
    {
        $this->conectar();
        $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
        $bitacora = 'ACTUALIZADO DESDE PANTALLA OPCIÓN M-4-1';
        $params = [$ip, $bitacora, $_SESSION[_S]['usr_id']];
        $bit = 'SET @usr_ip =INET_ATON( ? ), @bit_observacion = ? , @usr_id = ?;';
        $this->setSql($bit);
        $this->consultar($params);

        $prm = [$this->prm->techo, $this->prm->tec_id];
        $this->setSql('UPDATE cat_Techos SET tec_techoPresupuestal = ? WHERE tec_id = ? ;');
        $this->consultar($prm);
        $this->desconectar();
        $this->mixScript('notify("Techo Presupuestal Actualizado");');
    } //fin ActualizaTecho

    /* -----------------------------------------------------------------------------------------------------
        Agregar un nuevo Techo Financiero
    -------------------------------------------------------------------------------------------------- */
    private function agregarTecho()
    {
        $this->setContenedor('dialogTecho');
        $form = new stdClass();
        $form->id = 'techoForm';
        $form->controlador = $this->pantalla;
        $form->metodo = 'nuevoTecho';
        $form = new form($form);

        $combo = new stdClass();
        $combo->id = 'ter_id';
        $combo->required = true;
        $combo->sql = 'SELECT tr.ter_id, tr.ter_nombre FROM  ' . BD_SINPRES . '.cat_TipoRecurso tr;';
        $fte = $this->ponCombo($combo);

        $combo = new stdClass();
        $combo->id = 'clg_id';
        $combo->required = true;
        $combo->sql = 'SELECT clg.clg_id, clg.clg_nombre FROM cat_ClaseGasto clg;';
        $tei = $this->ponCombo($combo);

        $input = new stdClass();
        $input->id = 'tec_techoPresupuestal';
        $input->class = 'form-control';
        $input->formato = 'moneda';
        $input->style = 'text-align: end;';
        $input->positivo = true;
        $input->value = '0';
        $input->maxlength = 20;

        $form->mixHtml('
            <div class="row">
                <fieldset class="col-md-4 col-xs-4">
                    <legend>Gran fuente de Financiamiento</legend>
                    ' . $fte . '
                </fieldset>
                <fieldset class="col-md-5 col-xs-5">
                    <legend>Clase de Gasto</legend>
                    ' . $tei . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Techo Presupuestal</legend>
                    ' . $this->ponInput($input) . '
                </fieldset>
            </div>
        ');
        $this->mixHtml('
            <div id="myModalMeta" class="modal " data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog shadow-lg modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color:#9d2449;">
                            <h4 class="modal-title" id="myModalLabel">Nuevo Techo Financiero</h4>
                            <button type="button" class="close cerrar btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                <i class="fa fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                        ' . $form->ponForm() . '
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-success me-1" id="btnAceptarMeta" form="techoForm"><i class="fa-solid fa-floppy-disk me-2"></i>
                                Guardar
                            </button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        ');
        $this->mixScript('
            $("#myModalMeta").modal().show();
            setTimeout( function() { $( "#ter_id" ).focus() }, 200 );		
            $(".cerrar").click(function(){ $("#myModalMeta").remove(); }); 
            Mousetrap.bind("esc esc", function(e) { 
                e.preventDefault(); 
                $(".cerrar").click();
                Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });
                e.stopPropagation();
            });
        ');
    } //fin agregarTecho

    /* -----------------------------------------------------------------------------------
        Grabar un nuevo Techo Financiero
    ------------------------------------------------------------------------------- */
    private function nuevoTecho()
    {
        $prm = [':ter_id' => $this->prm->ter_id,
            ':clg_id'     => $this->prm->clg_id,
            ':adm_gpo'    => $this->adm_gpo,
            ':adm_ur'     => $this->adm_ur];
        $this->setSql('
            SELECT t.tec_id FROM cat_Techos t 
            WHERE t.ter_id = :ter_id AND t.clg_id = :clg_id 
            AND t.gpo_id = :adm_gpo AND t.ur_id = :adm_ur;
        ');
        $this->conectar();
        $this->consultar($prm);
        $this->desconectar();

        if ($this->getNumeroRegistros() == 0) {

            $this->conectar();
            $ip = (isset($_SESSION[_S]['ses-ip']) && $_SESSION[_S]['ses-ip'] != '::1') ? $_SESSION[_S]['ses-ip'] : '127.0.0.1';
            $bitacora = 'AGREGADO DESDE PANTALLA OPCIÓN M-4-1';
            $params = [$ip, $bitacora, $_SESSION[_S]['usr_id']];
            $bit = 'SET @usr_ip =INET_ATON( ? ), @bit_observacion = ? , @usr_id = ?;';
            $this->setSql($bit);
            $this->consultar($params);

            $prm[':tec_techo'] = $this->prm->tec_techoPresupuestal;
            $prm[':usr_id'] = $this->usr_id;
            $this->setSql('
                INSERT INTO cat_Techos
                    (gpo_id,ur_id,ter_id,clg_id,tec_techoPresupuestal,usr_id )
                VALUES ( :adm_gpo, :adm_ur, :ter_id, :clg_id, :tec_techo, :usr_id);
            ');
            $this->consultar($prm);
            $this->mixScript('$("#myModalMeta").remove();notify("Techo registrado correctamente");');
            $this->llenaTablaTechos();
        } else {
            $this->mixScript('notify("Este techo ya existe en el sistema", "error");');
        }
    } //fin nuevoTecho
} //fin class

if (isset($_SESSION[_S]['prm']['M']['4']['1'])) {
    new ControladorTechos();
} else {
    WebResponse::sinPermisos();
}
