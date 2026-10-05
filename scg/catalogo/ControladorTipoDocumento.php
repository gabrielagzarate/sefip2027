<?php
//Inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/TipoDocumentoFuente.php';

class ControladorTipoDocumento extends WebResponse
{
    public $lectura;
    public $escritura;
    public $gpo;
    public $ur_id;
    public $nivel;
    public $ue_id;

    public function __construct()
    {
        //Mandar a llamar Constructor Base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['3']['1']) ? true : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['3']['2']) ? true : false;
        $this->gpo = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaTipoDocumentoFuente':$this->llenaTablaTipoDocumentoFuente();
                break;
            case 'addTipoDocumentoFuente':$this->addTipoDocumentoFuente();
                break;
            case 'abcTipoDocumentoFuente':$this->abcTipoDocumentoFuente();
                break;
            default:$this->main();
                break;
        }

        $this->returnJson();
    }

    /************************************************* Función principal del Controlador************************************************/
    private function main()
    {

        $tablaTipoDocumentoFuente = new stdClass();
        $tablaTipoDocumentoFuente->id = 'tablaTipoDocumentoFuente';
        $tablaTipoDocumentoFuente->titulo = 'Tipo de Documento Fuente';
        $tablaTipoDocumentoFuente->comunica = new stdClass();
        $tablaTipoDocumentoFuente->comunica->pantalla = $this->pantalla;
        $tablaTipoDocumentoFuente->comunica->metodo = 'llenaTablaTipoDocumentoFuente';

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
        $th->aling = 'left';
        $th->width = '15%';
        $th->texto = 'ALIAS';
        array_push($tr, $th);

        //Se inicia el cuerpo de la Tabla
        $trbody = array();
        if ($this->escritura) {
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EditaCuenta";
            $td->tipo = "boton";
            $td->icono = "pencil";
            $td->attr = new stdClass();
            $td->attr->tdf_id = 'tdf_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaCta";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->tdf_id = 'tdf_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tdf_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tdf_nombre';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'tdf_alias';
        array_push($trbody, $td);

        $tablaTipoDocumentoFuente->encabezado = array($tr);
        $tablaTipoDocumentoFuente->cuerpo = ($trbody);

        //Se agregan los elementos
        $btn_regresarIndex = '';
        $btn_addTipoDocumentoFuente = '<button id="btn_addTipoDocumentoFuente" type="button" class="btn btn-outline-primary me-2 nuevo" title="Registrar nueva Clave"><i class="fa-solid fa-plus me-1"></i>Nueva</button>';
        $btn_repTipoDocumentoFuente = '<button id="btn_repTipoDocumentoFuente" type="button" form="frm_TipoDocumentoFuente" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Tipo de Documento Fuente"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>';
        $btn_expTipoDocumentoFuente = '<button id="btn_expTipoDocumentoFuente" type="button" form="frm_TipoDocumentoFuente" class="btn btn-outline-success me-2"  title="Exportar a excel Catálogo de Tipo de Documento Fuente"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresarIndex . $btn_addTipoDocumentoFuente . $btn_repTipoDocumentoFuente . $btn_expTipoDocumentoFuente);
        } else {
            $this->agregaTools($btn_regresarIndex . $btn_repTipoDocumentoFuente . $btn_expTipoDocumentoFuente);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaTipoDocumentoFuente) . '</div>');

        $this->llenaTablaTipoDocumentoFuente();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
                ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addTipoDocumentoFuente").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addTipoDocumentoFuente",
                        tdf_id: $(this).attr("tdf_id")
                    });
                });
            ');
        }
        $this->mixScript('$(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("#btn_repTipoDocumentoFuente").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                $("body").append(form);
                form.submit();
            });
            $("#btn_expTipoDocumentoFuente").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                $("body").append(form);
                form.submit();
            });
            $("#btn_regresarIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }

    /************************************************* LLENA la Tabla de Tipo de Documento Fuente *************************/
    private function llenaTablaTipoDocumentoFuente()
    {
        $sql = "
            SELECT tdf_id, tdf_nombre, tdf_alias
            FROM cat_TipoDocumentoFuente;
        ";

        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaTipoDocumentoFuente';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".EditaCuenta").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addTipoDocumentoFuente",
                    tdf_id: $(this).attr("tdf_id")
                });
                return false;
            });

            $(".EliminaCta").click(function() {
                var tipo = $(this).attr("tdf_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("tdf_id")+"</strong>" + " ¿Desea continuar?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcTipoDocumentoFuente",
                        abc: "b",
                        tdf_id: tipo
                        });
                }, function (){}).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            });
        ');
    }

    /************************************************* ALTA de nuevo Tipo de Documento Fuente *************************/
    private function addTipoDocumentoFuente()
    {
        $datos = null;
        $abc = '';

        if (isset($this->prm->tdf_id)) {
            $datos = new TipoDocumentoFuente($this->prm);
            $abc = 'c';
        } else {
            $datos = new TipoDocumentoFuente();
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
        $inp_Nombre->maxLength = 34;

        $inp_Alias = new stdClass();
        $inp_Alias->id = 'inp_Alias';
        $inp_Alias->label = 'Alias';
        $inp_Alias->placeholder = 'ALIAS';
        $inp_Alias->enDiv = true;
        $inp_Alias->required = true;
        $inp_Alias->maxLength = 6;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getTdf_id();
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

        $btn_regresaTipoDocumentoFuente = '<button id="btn_regresaTipoDocumentoFuente" type="button" class="btn btn-outline-secondary me-2" title="Regresar al Catálogo de Tipo de Documento Fuente"><i class="fa-solid fa-circle-left me-1"></i>Regresar</button>';
        $btn_guardarTipoDocumentoFuente = '<button id="btn_guardarTipoDocumentoFuente" type="button" form="GuardarTipoDocumentoFuente" class="btn btn-outline-success me-2"  title="Agregar clave de Tipo de Documento Fuente"><i class="fa-solid fa-floppy-disk me-1"></i>Agregar</button>';

        $this->agregaTools($btn_regresaTipoDocumentoFuente . $btn_guardarTipoDocumentoFuente);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardarTipoDocumentoFuente").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "abcTipoDocumentoFuente",
                    abc: "' . $abc . '",
                    tdf_id: $("#inp_Clave").val(),
                    tdf_nombre: $("#inp_Nombre").val(),
                    tdf_alias: $("#inp_Alias").val()
                },true);
            });

            $("#btn_regresaTipoDocumentoFuente").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');

    }

    /************************************************* ABC de Tipo de Documento Fuente *************************/
    private function abcTipoDocumentoFuente()
    {
        switch ($this->prm->abc) {
            case 'a':
                $sql = "
                    SELECT tdf_id
                    FROM cat_TipoDocumentoFuente
                    WHERE tdf_id = :tdf_id;
                ";
                $param = [':tdf_id' => $this->prm->tdf_id];

                $this->setSql($sql);
                $this->conectar();
                $this->consultar($param);
                $this->desconectar();
                $data = $this->extraer();

                if ($data) {
                    $this->mixScript('notify("La clave ' . $this->prm->tdf_id . ' ya está registrada en la base de datos","error");');
                } else {
                    TipoDocumentoFuente::abc($this->prm);
                    $this->mixScript('
                        notify("Nuevo tipo de documento fuente agregado","success");
                        comunica("' . $this->pantalla . '",{
                        metodo:"main"}
                        );
                    ');
                }
                break;
            case 'b':
                TipoDocumentoFuente::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","warning");');
                $this->llenaTablaTipoDocumentoFuente();
                break;
            case 'c':
                $TipoDocumentoFuente = new TipoDocumentoFuente();
                $TipoDocumentoFuente->setTdf_id($this->prm->tdf_id);
                $TipoDocumentoFuente->select();
                TipoDocumentoFuente::abc($this->prm);

                $this->mixScript('
                    notify("Registro actualizado exitosamente","success");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }
    }

}

if (isset($_SESSION[_S]['prm']['C']['2']['3'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorTipoDocumento();
} else {
    WebResponse::sinPermisos();
}
