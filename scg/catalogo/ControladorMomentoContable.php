<?php
//Inicio de sesion y llamada a las librerias
declare (strict_types = 1);
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/MomentoContable.php';

class ControladorMomentoContable extends WebResponse
{
    public $lectura;
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $nivel;
    public $ue_id;

    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['7']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['7']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaMomentoContable':
                $this->llenaTablaMomentoContable();
                break;
            case 'addMomentoContable':
                $this->addMomentoContable();
                break;
            case 'abcMomentoContable':
                $this->abcMomentoContable();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    /************************************************** Función principal del Controlador************************************************/

    private function main()
    {

        $tablaMomentoContable = new stdClass();
        $tablaMomentoContable->id = 'tablaMomentoContable';
        $tablaMomentoContable->titulo = 'Momentos Contables';
        $tablaMomentoContable->comunica = new stdClass();
        $tablaMomentoContable->comunica->pantalla = $this->pantalla;
        $tablaMomentoContable->comunica->metodo = 'llenaTablaMomentoContable';

        //Encabezados
        $tr = array();
        if ($this->escritura) {
            $th = new stdClass();
            $th->align = 'center';
            $th->width = '5%';
            $th->texto = 'Editar';
            array_push($tr, $th);
            $th = new stdClass();
            $th->align = 'left';
            $th->width = '5%';
            $th->texto = 'Eliminar';
            array_push($tr, $th);
        }
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '15%';
        $th->texto = 'MC';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '20%';
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '15%';
        $th->texto = 'ALIAS';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        if ($this->escritura) {
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EditaClave";
            $td->tipo = "boton";
            $td->icono = "pencil";
            $td->attr = new stdClass();
            $td->attr->mmc_id = 'mmc_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaClave me-2";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->mmc_id = 'mmc_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'mmc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'mmc_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'mmc_alias';
        array_push($trbody, $td);

        $tablaMomentoContable->encabezado = array($tr);
        $tablaMomentoContable->cuerpo = ($trbody);

        //Se agregan los elementos
        $btnRegresarIndex = '';
        $btn_addMomentoContable = '
            <button id="btn_addMomentoContable" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar Nueva Clave">
                <i class="fa-solid fa-plus me-1"></i>Nueva
            </button>';
        $btn_impMomentoContable = '
            <button id="btn_repMomentoContable" type="button" form="frm_momentoContable" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Momento Contable">
                <i class="fa-solid fa-file-pdf me-1"></i>PDF
            </button>';
        $btn_expMomentoContable = '
            <button id="btn_expMomentoContable" type="button" form="frm_momentoContable" class="btn btn-outline-success me-2" title="Exportar a excel Catálogo de Momento Contable">
                <i class="fa-solid fa-file-excel me-1"></i>Excel
            </button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btnRegresarIndex . $btn_addMomentoContable . $btn_impMomentoContable . $btn_expMomentoContable);
        } else {
            $this->agregaTools($btnRegresarIndex . $btn_impMomentoContable . $btn_expMomentoContable);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaMomentoContable) . '</div>');
        $this->llenaTablaMomentoContable();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addMomentoContable").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addMomentoContable",
                        mmc_id: $(this).attr("mmc_id")
                    });
                });
            ');
        }
        $this->mixScript('
            $(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#btn_repMomentoContable").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "7" ))

                $("body").append(form);
                form.submit();
            });

            $("#btn_expMomentoContable").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "7" ))

                $("body").append(form);
                form.submit();
            });
            $("#btnRegresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }
    /*     * *********************************************** LLENA la Tabla de Tipo de Documento Fuente ************************ */

    private function llenaTablaMomentoContable()
    {
        $sql = "SELECT mmc_id, mmc_nombre, mmc_alias FROM cat_MomentoContable";
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaMomentoContable';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addMomentoContable",
                    mmc_id: $(this).attr("mmc_id")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var momento = $(this).attr("mmc_id");

                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("mmc_id")+"</strong>" + " ¿Desea continuar?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcMomentoContable",
                            abc: "b",
                            mmc_id: momento
                            });
                    }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
    ');
    }
    /****************** ALTA de nuevo Tipo de Documento Fuente ****************** */

    private function addMomentoContable()
    {
        $datos = null;
        $abc = '';
        $accion = "Añadir";
        if (isset($this->prm->mmc_id)) {
            $datos = new MomentoContable($this->prm);
            $abc = 'c';
            $accion = "Guardar";
        } else {
            $datos = new MomentoContable();
            $abc = 'a';
        }

        $inp_Clave = new stdClass();
        $inp_Clave->id = 'inp_Clave';
        $inp_Clave->label = 'Clave';
        $inp_Clave->placeholder = 'CLAVE';
        $inp_Clave->enDiv = true;
        $inp_Clave->required = true;
        $inp_Clave->maxLength = 1;

        $inp_Nombre = new stdClass();
        $inp_Nombre->id = 'inp_Nombre';
        $inp_Nombre->label = 'Nombre';
        $inp_Nombre->placeholder = 'NOMBRE';
        $inp_Nombre->enDiv = true;
        $inp_Nombre->required = true;
        $inp_Nombre->maxLength = 15;

        $inp_Alias = new stdClass();
        $inp_Alias->id = 'inp_Alias';
        $inp_Alias->label = 'Alias';
        $inp_Alias->placeholder = 'ALIAS';
        $inp_Alias->enDiv = true;
        $inp_Alias->required = true;
        $inp_Alias->maxLength = 3;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getMmc_id();
            $inp_Nombre->value = $datos->getNombre();
            $inp_Alias->value = $datos->getAlias();
        }
        //Se agregan los elementos
        $inp_Clave = $this->ponInput($inp_Clave);
        $inp_Nombre = $this->ponInput($inp_Nombre);
        $inp_Alias = $this->ponInput($inp_Alias);

        //Se pintan los elementos en patalla
        $html = '
            <div class="container-fluid">
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Clave . '
                </div>
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Nombre . '
                </div>
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Alias . '
                </div>
            </div>
        ';

        $btn_regresaMomentoContable = '
            <button id="btn_regresaMomentoContable" type="button" class="btn btn-outline-secondary me-2" title="Regresar al Catálogo de Momento Contable">
                <i class="fa-solid fa-circle-left me-1"></i>Regresar
            </button>';
        $btn_guardarMomentoContable = '
            <button id="btn_guardarMomentoContable" type="button" form="GuardarTdF" class="btn btn-outline-success me-2"  title="Guardar Catálogo Momento Contable">
                <i class="fa-solid fa-floppy-disk me-1"></i>' . $accion . '
            </button>';

        $this->agregaTools($btn_regresaMomentoContable . $btn_guardarMomentoContable);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardarMomentoContable").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "abcMomentoContable",
                    abc: "' . $abc . '",
                    mmc_id: $("#inp_Clave").val(),
                    mmc_nombre: $("#inp_Nombre").val(),
                    mmc_alias: $("#inp_Alias").val()
                },true);
            });
            $("#btn_regresaMomentoContable").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }
    /*     * *********************************************** ABC de Tipo de Documento Fuente ************************ */

    private function abcMomentoContable()
    {
        switch ($this->prm->abc) {
            case 'a':
                $sql = "SELECT mmc_id FROM cat_MomentoContable WHERE mmc_id = :mmc_id";
                $param = [':mmc_id' => $this->prm->mmc_id];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($param);
                $data = $this->extraer();
                $this->desconectar();

                if ($data != '') {
                    $this->mixScript('notify("La Clave ' . $this->prm->mmc_id . ' ya está registrada en la base de datos","error");');
                } else {
                    $sql = "SELECT max(mmc_orden) as orden FROM cat_MomentoContable";
                    $this->setSql($sql);
                    $this->conectar();
                    $this->consultar();
                    $data = $this->extraer();
                    $this->desconectar();
                    if ($data['orden'] != '') {
                        $this->prm->mmc_orden = $data['orden'] + 1;
                    } else {
                        $this->prm->mmc_orden = 1;
                    }

                    MomentoContable::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo:"main"
                        });
                    ');
                }
                break;
            case 'b':
                $sql = "SELECT mmc_id FROM cat_TipoMomentoContable WHERE mmc_id = :mmc_id";
                $param = [':mmc_id' => $this->prm->mmc_id];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($param);
                $data = $this->extraer();
                $this->desconectar();
                if ($data != '') {
                    $this->mixScript('notify("La clave ' . $this->prm->mmc_id . ' ya está en uso y no puede ser eliminada.","error");');
                } else {
                    MomentoContable::abc($this->prm);
                    $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                    $this->llenaTablaMomentoContable();
                }
                break;
            case 'c':
                $MomentoContable = new MomentoContable();
                $MomentoContable->setMmc_id($this->prm->mmc_id);
                $MomentoContable->select();
                MomentoContable::abc($this->prm);

                $this->mixScript('
                    notify("El registro se actualizó exitosamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }
    }
}
if (isset($_SESSION[_S]['prm']['C']['2']['7'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorMomentoContable();
} else {
    WebResponse::sinPermisos();
}
