<?php
declare (strict_types = 1);
//Inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/AgrupacionDocumentoFuente.php';
class ControladorAgrupacionDocumento extends WebResponse
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
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['4']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['4']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaAgrupacionDocumentoFuente':
                $this->llenaTablaAgrupacionDocumentoFuente();
                break;
            case 'addAgrupacionDocumentoFuente':
                $this->addAgrupacionDocumentoFuente();
                break;
            case 'ABCAgrupacionDocumentoFuente':
                $this->ABCAgrupacionDocumentoFuente();
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
        $tablaAgrupacionDocumentoFuente = new stdClass();
        $tablaAgrupacionDocumentoFuente->id = 'tablaAgrupacionDocumentoFuente';
        $tablaAgrupacionDocumentoFuente->titulo = 'Agrupación de Documentos Fuente';
        $tablaAgrupacionDocumentoFuente->comunica = new stdClass();
        $tablaAgrupacionDocumentoFuente->comunica->pantalla = $this->pantalla;
        $tablaAgrupacionDocumentoFuente->comunica->metodo = 'llenaTablaAgrupacionDocumentoFuente';
        //
        $tablaAgrupacionDocumentoFuente->filasAmostrar = array(5, 10, 20, 50, 200);
        $tablaAgrupacionDocumentoFuente->numFilas = 100;
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
            $td->attr->adf_id = 'adf_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaClave";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->adf_id = 'adf_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'adf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'adf_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'adf_alias';
        array_push($trbody, $td);

        $tablaAgrupacionDocumentoFuente->encabezado = array($tr);
        $tablaAgrupacionDocumentoFuente->cuerpo = ($trbody);
        //Se agregan los elementos
        $btn_regresarIndex = '';
        $btn_addAgrupacionDocumentoFuente = '<button id="btn_addAgrupacionDocumentoFuente" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar nueva clave"><i class="fa-solid fa-plus me-1"></i>Nueva</button>';
        $btn_repAgrupacionDocumentoFuente = '<button id="btn_repAgrupacionDocumentoFuente" type="button" form="frm_agrupacionDocumentoFuente" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Agrupación de Documentos Fuente"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>';
        $btn_expAgrupacionDocumentoFuente = '<button id="btn_expAgrupacionDocumentoFuente" type="button" form="frm_agrupacionDocumentoFuente" class="btn btn-outline-success me-2"  title="Exportar a excel Catálogo de Agrupación de Documentos Fuente"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addAgrupacionDocumentoFuente . $btn_repAgrupacionDocumentoFuente . $btn_expAgrupacionDocumentoFuente);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repAgrupacionDocumentoFuente . $btn_expAgrupacionDocumentoFuente);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaAgrupacionDocumentoFuente) . '</div>');
        $this->llenaTablaAgrupacionDocumentoFuente();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addAgrupacionDocumentoFuente").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addAgrupacionDocumentoFuente",
                        adf_id: $(this).attr("adf_id")
                    });
                });
            ');
        }
        $this->mixScript('
            $(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#btn_repAgrupacionDocumentoFuente").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "4" ))

                $("body").append(form);
                form.submit();
            });

            $("#btn_expAgrupacionDocumentoFuente").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "4" ))

                $("body").append(form);
                form.submit();
            });

            $("#btn_regresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }
    /*     * *********************************************** LLENA la Tabla de Agrupación de Documento Fuente ************************ */

    private function llenaTablaAgrupacionDocumentoFuente()
    {
        $sql = "
            SELECT adf_id, adf_nombre, adf_alias
            FROM cat_AgrupacionDocumentoFuente;
        ";
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAgrupacionDocumentoFuente';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1; //$nav->nf = 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addAgrupacionDocumentoFuente",
                    adf_id: $(this).attr("adf_id")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var agrupacion = $(this).attr("adf_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "La clave " + "<strong>"+$(this).attr("adf_id")+"</strong> será eliminado del catálogo " + "¿Desea continuar?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "ABCAgrupacionDocumentoFuente",
                        abc: "b",
                        adf_id: agrupacion
                    });
                }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }
    /*     * *********************************************** ALTA de nuev Agrupación de Documento Fuente ************************ */

    private function addAgrupacionDocumentoFuente()
    {
        $datos = null;
        $abc = '';
        if (isset($this->prm->adf_id)) {
            $datos = new AgrupacionDocumentoFuente($this->prm);
            $abc = 'c';
        } else {
            $datos = new AgrupacionDocumentoFuente();
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
        $inp_Nombre->maxLength = 12;

        $inp_Alias = new stdClass();
        $inp_Alias->id = 'inp_Alias';
        $inp_Alias->label = 'Alias';
        $inp_Alias->placeholder = 'ALIAS';
        $inp_Alias->enDiv = true;
        $inp_Alias->required = true;
        $inp_Alias->maxLength = 6;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getAdf_id();
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

        $btn_regresaAgrupacionDocumentoFuente = '<button id="btn_regresaAgrupacionDocumentoFuente" type="button" class="btn btn-outline-secondary me-2" title="Regresar al Catálogo de Agrupación de Documentos Fuente"><i class="fa-solid fa-circle-left me-1"></i>Regresar</button>';
        $btn_guardaAgrupacionDocumentoFuente = '<button id="btn_guardaAgrupacionDocumentoFuente" type="button" form="GuardarAdF" class="btn btn-outline-success me-2"  title="Agregar Catálogo de Agrupación de Documentos Fuente"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>';

        $this->agregaTools($btn_regresaAgrupacionDocumentoFuente . $btn_guardaAgrupacionDocumentoFuente);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardaAgrupacionDocumentoFuente").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "ABCAgrupacionDocumentoFuente",
                    abc: "' . $abc . '",
                    adf_id: $("#inp_Clave").val(),
                    adf_nombre: $("#inp_Nombre").val(),
                    adf_alias: $("#inp_Alias").val()
                },true);
            });

            $("#btn_regresaAgrupacionDocumentoFuente").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }
    /*     * *********************************************** ABC de Agrupación de Documento Fuente ************************ */

    private function ABCAgrupacionDocumentoFuente()
    {
        switch ($this->prm->abc) {
            case 'a':

                $sql = "
                    SELECT adf_id
                    FROM cat_AgrupacionDocumentoFuente
                    WHERE adf_id = :adf_id;
                ";
                $param = [':adf_id' => $this->prm->adf_id];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($param);
                $this->desconectar();
                $data = $this->extraer();
                if ($data != '') {
                    $this->mixScript('notify("La Clave ' . $this->prm->adf_id . ' ya está registrada en la base de datos","error");');
                } else {
                    AgrupacionDocumentoFuente::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo: "main"
                        });
                    ');
                }
                break;
            case 'b':
                AgrupacionDocumentoFuente::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                $this->llenaTablaAgrupacionDocumentoFuente();
                break;
            case 'c':
                $AgrupacionDocumentoFuente = new AgrupacionDocumentoFuente();
                $AgrupacionDocumentoFuente->setAdf_id($this->prm->adf_id);
                $AgrupacionDocumentoFuente->select();
                AgrupacionDocumentoFuente::abc($this->prm);

                $this->mixScript('notify("El registro se actualizó exitosamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }
    }
}
if (isset($_SESSION[_S]['prm']['C']['2']['4'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorAgrupacionDocumento();
} else {
    WebResponse::sinPermisos();
}
