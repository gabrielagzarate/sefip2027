<?php
//Inicio de sesion y llamada a las librerias
declare (strict_types = 1);
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/AgrupacionPoliza.php';
class ControladorAgrupacionPoliza extends WebResponse
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
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['5']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['5']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaAgrupacionPoliza':
                $this->llenaTablaAgrupacionPoliza();
                break;
            case 'addAgrupacionPoliza':
                $this->addAgrupacionPoliza();
                break;
            case 'abcAgrupacionPoliza':
                $this->abcAgrupacionPoliza();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }
    /*     * *********************************************** Función principal del Controlador*********************************************** */

    private function main()
    {
        $tablaAgrupacionPoliza = new stdClass();
        $tablaAgrupacionPoliza->id = 'tablaAgrupacionPoliza';
        $tablaAgrupacionPoliza->titulo = 'Agrupación de Pólizas';
        $tablaAgrupacionPoliza->comunica = new stdClass();
        $tablaAgrupacionPoliza->comunica->pantalla = $this->pantalla;
        $tablaAgrupacionPoliza->comunica->metodo = 'llenaTablaAgrupacionPoliza';
        $tablaAgrupacionPoliza->numFilas = 100;
        $tablaAgrupacionPoliza->filasAmostrar = array(5, 10, 20, 50, 200);
        //Encabezados
        $tr = array();
        if ($this->escritura) {
            $th = new stdClass();
            $th->align = 'left';
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
        $th->texto = 'CLAVE';
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
            $td->attr->apg_id = 'apg_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaClave";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->apg_id = 'apg_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'apg_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'apg_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'apg_alias';
        array_push($trbody, $td);

        $tablaAgrupacionPoliza->encabezado = array($tr);
        $tablaAgrupacionPoliza->cuerpo = ($trbody);
        //Se agregan los elementos
        $btn_regresarIndex = '';
        $btn_addAgrupacionPoliza = '<button id="btn_addAgrupacionPoliza" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar nueva Clave"><i class="fa-solid fa-plus me-1"></i>Nueva</button>';
        $btn_repAgrupacionPoliza = '<button id="btn_repAgrupacionPoliza" type="button" form="frm_agrupacionPoliza" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Agrupación de póliza"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>';
        $btn_expAgrupacionPoliza = '<button id="btn_expAgrupacionPoliza" type="button" form="frm_agrupacionPoliza" class="btn btn-outline-success me-2"  title="Exportar a excel Catálogo de Agrupación de póliza"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addAgrupacionPoliza . $btn_repAgrupacionPoliza . $btn_expAgrupacionPoliza);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repAgrupacionPoliza . $btn_expAgrupacionPoliza);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaAgrupacionPoliza) . '</div>');

        $this->llenaTablaAgrupacionPoliza();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addAgrupacionPoliza").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addAgrupacionPoliza",
                        apg_id: $(this).attr("apg_id")
                    });
                });
            ');
        }

        $this->mixScript('
            $(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#btn_repAgrupacionPoliza").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))
                    $("body").append(form);
                form.submit();
            });
            $("#btn_expAgrupacionPoliza").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))
                    $("body").append(form);
                form.submit();
            });
            $("#btn_regresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }
    /*     * *********************************************** LLENA la Tabla de Tipo de Documento Fuente ************************ */

    private function llenaTablaAgrupacionPoliza()
    {
        $sql = "
            SELECT apg_id, apg_nombre, apg_alias
            FROM cat_AgrupacionPoliza;
        ";

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAgrupacionPoliza';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addAgrupacionPoliza",
                    apg_id: $(this).attr("apg_id")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var poliza = $(this).attr("apg_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("apg_id")+"</strong>" + " ¿Desea continuar?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcAgrupacionPoliza",
                            abc: "b",
                            apg_id: poliza
                            });
                    }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }
    /*     * *********************************************** ALTA de nuevo Tipo de Documento Fuente ************************ */

    private function addAgrupacionPoliza()
    {
        $datos = null;
        $abc = '';
        $accion = "Agregar";
        if (isset($this->prm->apg_id)) {
            $datos = new AgrupacionPoliza($this->prm);
            $abc = 'c';
            $accion = "Actualizar";
        } else {
            $datos = new AgrupacionPoliza();
            $abc = 'a';
        }

        $inp_Clave = new stdClass();
        $inp_Clave->id = 'inp_Clave';
        $inp_Clave->label = 'Clave';
        $inp_Clave->placeholder = 'CLAVE';
        $inp_Clave->enDiv = true;
        $inp_Clave->required = true;
        $inp_Clave->maxLength = 4;

        $inp_Nombre = new stdClass();
        $inp_Nombre->id = 'inp_Nombre';
        $inp_Nombre->label = 'Nombre';
        $inp_Nombre->placeholder = 'NOMBRE';
        $inp_Nombre->enDiv = true;
        $inp_Nombre->required = true;
        $inp_Nombre->maxLength = 9;

        $inp_Alias = new stdClass();
        $inp_Alias->id = 'inp_Alias';
        $inp_Alias->label = 'Alias';
        $inp_Alias->placeholder = 'ALIAS';
        $inp_Alias->enDiv = true;
        $inp_Alias->required = true;
        $inp_Alias->maxLength = 4;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getApg_id();
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

        $btn_regresaAgrupacionPoliza = '<button id="btn_regresaAgrupacionPoliza" type="button" class="btn btn-outline-secondary me-2" title="Regresar al catálogo de tipo de Doc. Fuente"><i class="fa-solid fa-circle-left me-1"></i>Regresar</button>';
        $btn_guardarAgrupacionPoliza = '<button id="btn_guardarAgrupacionPoliza" type="button" form="GuardarTdF" class="btn btn-outline-success me-2"  title="Guardar catálogo de tipo de Doc. Fuente"><i class="fa-solid fa-floppy-disk me-1"></i>' . $accion . '</button>';

        $this->agregaTools($btn_regresaAgrupacionPoliza . $btn_guardarAgrupacionPoliza);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardarAgrupacionPoliza").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "abcAgrupacionPoliza",
                    abc: "' . $abc . '",
                    apg_id: $("#inp_Clave").val(),
                    apg_nombre: $("#inp_Nombre").val(),
                    apg_alias: $("#inp_Alias").val()
                },true);
            });

            $("#btn_regresaAgrupacionPoliza").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }
    /*     * *********************************************** ABC de Tipo de Documento Fuente ************************ */

    private function abcAgrupacionPoliza()
    {
        switch ($this->prm->abc) {
            case 'a':
                $sql = "
                    SELECT apg_id
                    FROM cat_AgrupacionPoliza
                    WHERE apg_id = :apg_id;
                ";
                $params = [':apg_id' => $this->prm->apg_id];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($params);
                $data = $this->extraer();
                $this->desconectar();
                if ($data != '') {
                    $this->mixScript('notify("La clave ' . $this->prm->apg_id . ' ya está registrada en la base de datos","error");');
                } else {
                    AgrupacionPoliza::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo: "main"
                        });
                    ');
                }
                break;
            case 'b':
                AgrupacionPoliza::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","warning");');
                $this->llenaTablaAgrupacionPoliza();
                break;
            case 'c':
                $AgrupacionPoliza = new AgrupacionPoliza();
                $AgrupacionPoliza->setApg_id($this->prm->apg_id);
                $AgrupacionPoliza->select();
                AgrupacionPoliza::abc($this->prm);

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
if (isset($_SESSION[_S]['prm']['C']['2']['5'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorAgrupacionPoliza();
} else {
    WebResponse::sinPermisos();
}
