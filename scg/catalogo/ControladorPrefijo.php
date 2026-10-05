<?php
//Inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/PrefijoPoliza.php';
class ControladorPrefijo extends WebResponse
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
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['6']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['6']['2']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaPrefijo':
                $this->llenaTablaPrefijo();
                break;
            case 'addPrefijo':
                $this->addPrefijo();
                break;
            case 'abcPrefijo':
                $this->abcPrefijo();
                break;
            default:
                $this->main();
                break;
        }

        $this->returnJson();
    }

    /************************************************* Función principal del Controlador************************************************/
    private function main()
    {
        $tablaPrefijo = new stdClass();
        $tablaPrefijo->id = 'tablaPrefijo';
        $tablaPrefijo->titulo = 'Prefijos';
        $tablaPrefijo->comunica = new stdClass();
        $tablaPrefijo->comunica->pantalla = $this->pantalla;
        $tablaPrefijo->comunica->metodo = 'llenaTablaPrefijo';

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
            $td->attr->prf_id = 'prf_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaClave";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->prf_id = 'prf_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'prf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'prf_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'prf_alias';
        array_push($trbody, $td);

        $tablaPrefijo->encabezado = array($tr);
        $tablaPrefijo->cuerpo = ($trbody);

        //Se agregan los elementos
        $btn_regresarIndex = '';
        $btn_addPrefijo = '<button id="btn_addPrefijo" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar nueva Clave"><i class="fa-solid fa-plus me-1"></i>Nueva</button>';
        $btn_repPrefijo = '<button id="btn_repPrefijo" type="button" form="frm_prefijo" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Prefijo"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>';
        $btn_expPrefijo = '<button id="btn_expPrefijo" type="button" form="frm_prefijo" class="btn btn-outline-success me-2"  title="Exportar a excel Catálogo de Prefijo"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addPrefijo . $btn_repPrefijo . $btn_expPrefijo);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repPrefijo . $btn_expPrefijo);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaPrefijo) . '</div>');
        $this->llenaTablaPrefijo();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addPrefijo").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addPrefijo",
                        prf_id: $(this).attr("prf_id")
                    });
                });
            ');
        }
        $this->mixScript('$(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                $("#btn_repPrefijo").click(function(){
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "6" ))

                    $("body").append(form);
                    form.submit();
                });
                $("#btn_expPrefijo").click(function(){
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "6" ))

                    $("body").append(form);
                    form.submit();
                });
                $("#btn_regresarIndex").click (function(){
                    $("nav").menusinpres("clickMenu");
                    return false;
                });
            ');
    }

    /************************************************* LLENA la Tabla de Prefijo *************************/
    private function llenaTablaPrefijo()
    {
        $sql = "
            SELECT prf_id, prf_nombre, prf_alias
            FROM cat_PrefijoPoliza;
        ";
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaPrefijo';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addPrefijo",
                    prf_id: $(this).attr("prf_id")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var prefijo = $(this).attr("prf_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("prf_id")+"</strong>" + " ¿Desea continuar?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo: "abcPrefijo",
                            abc: "b",
                            prf_id: prefijo
                            });
                    }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }

    /************************************************* ALTA de nuevo Prefijo *************************/
    private function addPrefijo()
    {
        $datos = null;
        $abc = '';
        $accion = "Añadir";
        if (isset($this->prm->prf_id)) {
            $datos = new PrefijoPoliza($this->prm);
            $abc = 'c';
            $accion = "Guardar";
        } else {
            $datos = new PrefijoPoliza();
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
        $inp_Nombre->maxLength = 6;

        $inp_Alias = new stdClass();
        $inp_Alias->id = 'inp_Alias';
        $inp_Alias->label = 'Alias';
        $inp_Alias->placeholder = 'ALIAS';
        $inp_Alias->enDiv = true;
        $inp_Alias->required = true;
        $inp_Alias->maxLength = 3;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getPrf_id();
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

        $btn_regresaPrefijo = '<button id="btn_regresaPrefijo" type="button" class="btn btn-outline-secondary me-2" title="Regresar al Catálogo Prefijo"><i class="fa-solid fa-circle-left me-1"></i>Regresar</button>';
        $btn_guardarPrefijo = '<button id="btn_guardarPrefijo" type="button" form="GuardarTdF" class="btn btn-outline-success  me-2"  title="Guardar Catálogo Prefijo"><i class="fa-solid fa-floppy-disk me-1"></i>' . $accion . '</button>';

        $this->agregaTools($btn_regresaPrefijo . $btn_guardarPrefijo);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardarPrefijo").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "abcPrefijo",
                    abc: "' . $abc . '",
                    prf_id: $("#inp_Clave").val(),
                    prf_nombre: $("#inp_Nombre").val(),
                    prf_alias: $("#inp_Alias").val()
                },true);
            });
            $("#btn_regresaPrefijo").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }

    /************************************************* ABC de Tipo de Documento Fuente *************************/
    private function abcPrefijo()
    {
        switch ($this->prm->abc) {
            case 'a':
                $sql = "
                    SELECT prf_id
                    FROM cat_PrefijoPoliza
                    WHERE prf_id = :prf_id;
                ";
                $params = [':prf_id' => $this->prm->prf_id];
                $this->setSql($sql);
                $this->conectar();
                $this->consultar($params);
                $data = $this->extraer();
                $this->desconectar();
                if ($data != '') {
                    $this->mixScript('notify("La clave ' . $this->prm->prf_id . ' ya está registrada en la base de datos","error");');
                } else {
                    PrefijoPoliza::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo: "main"
                        });
                    ');
                }
                break;
            case 'b':
                PrefijoPoliza::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                $this->llenaTablaPrefijo();
                break;
            case 'c':
                $PrefijoPoliza = new PrefijoPoliza();
                $PrefijoPoliza->setPrf_id($this->prm->prf_id);
                $PrefijoPoliza->select();
                PrefijoPoliza::abc($this->prm);

                $this->mixScript('
                    notify("La clave ' . $this->prm->prf_id . ' se actualizó exitosamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }
    }
}

if (isset($_SESSION[_S]['prm']['C']['2']['6'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorPrefijo();
} else {
    WebResponse::sinPermisos();
}
