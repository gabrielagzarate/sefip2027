<?php
//Inicio de sesion y llamada a las librerias
declare (strict_types = 1);
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/catalogo/AgrupacionCuentas.php';

class ControladorAgrupacionCuentas extends WebResponse
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
        $this->lectura = isset($_SESSION[_S]['prm']['C']['2']['2']['1']) ? true
        : false;
        $this->escritura = isset($_SESSION[_S]['prm']['C']['2']['2']['2']) ? true
        : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'llenaTablaAgrupacionCuentas':
                $this->llenaTablaAgrupacionCuentas();
                break;
            case 'addAgrupacionCuentas':
                $this->addAgrupacionCuentas();
                break;
            case 'abcAgrupacionCuentas':
                $this->abcAgrupacionCuentas();
                break;
            default:$this->main();
                break;
        }

        $this->returnJson();
    }
    /*     * *********************************************** Función principal del Controlador*********************************************** */

    private function main()
    {
        $tablaAgrupacionCuentas = new stdClass();
        $tablaAgrupacionCuentas->id = 'tablaAgrupacionCuentas';
        $tablaAgrupacionCuentas->titulo = 'Agrupación de Cuentas';
        $tablaAgrupacionCuentas->comunica = new stdClass();
        $tablaAgrupacionCuentas->comunica->pantalla = $this->pantalla;
        $tablaAgrupacionCuentas->comunica->metodo = 'llenaTablaAgrupacionCuentas';

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
        $th->width = '20%';
        $th->texto = 'CLAVE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->align = 'left';
        $th->width = '30%';
        $th->texto = 'NOMBRE';
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
            $td->attr->agc_id = 'agc_id';
            array_push($trbody, $td);
            $td = new stdClass();
            $td->align = 'left';
            $td->class = "EliminaClave btn";
            $td->color = "rojo";
            $td->tipo = "boton";
            $td->icono = "trash";
            $td->attr = new stdClass();
            $td->attr->agc_id = 'agc_id';
            array_push($trbody, $td);
        }
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'agc_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->align = 'left';
        $td->campoTxt = 'agc_nombre';
        array_push($trbody, $td);

        $tablaAgrupacionCuentas->encabezado = array($tr);
        $tablaAgrupacionCuentas->cuerpo = ($trbody);
        $btn_regresaIndex = '';
        //Se agregan los elementos
        $btn_regresaIndex = '';
        $btn_addAgrupacionCuentas = '<button id="btn_addAgrupacionCuentas" type="button" class="btn btn-outline-primary nuevo me-2" title="Registrar nueva Clave"><i class="fa-solid fa-plus me-1"></i>Nueva</button>';
        $btn_repAgrupacionCuentas = '<button id="btn_repAgrupacionCuentas" type="button" form="frm_agrupacionCuentas" class="btn btn-outline-danger me-2"  title="Imprimir Catálogo de Agrupación de Cuentas"><i class="fa-solid fa-file-pdf me-1"></i>PDF</button>';
        $btn_expAgrupacionCuentas = '<button id="btn_expAgrupacionCuentas" type="button" form="frm_agrupacionCuentas" class="btn btn-outline-success me-2"  title="Exportar a excel Catálogo de Agrupación de Cuentas"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>';

        //Se pintan los elementos en patalla
        if ($this->escritura) {
            $this->agregaTools($btn_regresaIndex . $btn_addAgrupacionCuentas . $btn_repAgrupacionCuentas . $btn_expAgrupacionCuentas);
        } else {
            $this->agregaTools($btn_regresaIndex . $btn_repAgrupacionCuentas . $btn_expAgrupacionCuentas);
        }

        $this->mixHtml('<div class="col-md-12 col-xs-12">' . $this->wrPonTabla($tablaAgrupacionCuentas) . '</div>');
        $this->llenaTablaAgrupacionCuentas();
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        if ($this->escritura) {
            $this->mixScript('
                $("#btn_addAgrupacionCuentas").click(function() {
                    comunica("' . $this->pantalla . '",{
                        metodo: "addAgrupacionCuentas",
                        agc_id: $(this).attr("agc_id")
                    });
                });
            ');
        }
        $this->mixScript('
            $(".tabla-footer").hide();
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );

            $("#btn_repAgrupacionCuentas").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))
                $("body").append(form);
                form.submit();
            });

            $("#btn_expAgrupacionCuentas").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/catalogo/exp_Catalogo.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "2" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))

                $("body").append(form);
                form.submit();
            });
            $("#btn_regresaIndex").click (function(){
                $("nav").menusinpres("clickMenu");
                return false;
            });
        ');
    }
    /***************************** LLENA la Tabla Agrupacion de Cuentas ************************ */

    private function llenaTablaAgrupacionCuentas()
    {
        $sql = "
            SELECT agc_id, agc_nombre
            FROM cat_AgrupacionCuentas
        ";
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tablaAgrupacionCuentas';
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : -1;
        $this->wrLlenaTabla($nav);
        $label = '<i class="fa fa-times"></i> Cancelar';
        $this->mixScript('
            $(".EditaClave").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "addAgrupacionCuentas",
                    agc_id: $(this).attr("agc_id")
                });
                return false;
            });

            $(".EliminaClave").click(function() {
                var agrupacion = $(this).attr("agc_id");
                alertify.confirm("<h5>ELIMINAR CLAVE DEL CATÁLOGO</h5>", "Está a punto de eliminar la clave: "+"<strong>"+$(this).attr("agc_id")+"</strong>" + " ¿Desea continuar?",
                function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "abcAgrupacionCuentas",
                        abc: "b",
                        agc_id: agrupacion
                    });
                },function(){}).set("defaultFocus", "cancel").set("labels", {ok:"Aceptar", cancel:"Cancelar"});
            });
        ');
    }
    /*     * *********************************************** ALTA de nueva Agrupacion de Cuentas ************************ */

    private function addAgrupacionCuentas()
    {
        $datos = null;
        $abc = '';
        $accion='Añadir';

        if (isset($this->prm->agc_id)) {
            $datos = new AgrupacionCuentas($this->prm);
            $abc = 'c';
            $accion='Guardar';
        } else {
            $datos = new AgrupacionCuentas();
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
        $inp_Nombre->maxLength = 20;

        $inp_Descripcion = new stdClass();
        $inp_Descripcion->id = 'inp_Descripcion';
        $inp_Descripcion->label = 'Descripcion';
        $inp_Descripcion->placeholder = 'DESCRIPCION';
        $inp_Descripcion->enDiv = true;
        $inp_Descripcion->required = true;
        $inp_Descripcion->maxLength = 38;

        if ($abc == 'c') {
            $inp_Clave->readonly = "readonly";
            $inp_Clave->value = $datos->getAgc_id();
            $inp_Nombre->value = $datos->getNombre();
            $inp_Descripcion->value = $datos->getDescripcion();
        }
        //Se agregan los elementos
        $inp_Clave = $this->ponInput($inp_Clave);
        $inp_Nombre = $this->ponInput($inp_Nombre);
        $inp_Descripcion = $this->ponInput($inp_Descripcion);

        //Se pintan los elementos en patalla
        $html = '
            <div class="container">
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Clave . '
                </div>
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Nombre . '
                </div>
                <div class="row col-md-6 col-xs-6 mb-3">
                    ' . $inp_Descripcion . '
                </div>
            </div>
        ';

        $btn_regresaAgrupacionCuentas = '<button id="btn_regresaAgrupacionCuentas" type="button" class="btn btn-outline-secondary me-2" title="Regresar al Catálogo de Agrupación de Cuentas"><i class="fa-solid fa-circle-left me-1"></i>Regresar</button>';
        $btn_guardarAgrupacionCuentas = '<button id="btn_guardarAgrupacionCuentas" type="button" form="GuardarTdF" class="btn btn-outline-success me-2"  title="Guardar Catálogo de Agrupación de Cuentas"><i class="fa-solid fa-floppy-disk me-1"></i>'.$accion.'</button>';

        $this->agregaTools($btn_regresaAgrupacionCuentas . $btn_guardarAgrupacionCuentas);

        $this->mixHtml($html);
        $this->mixScript('
            $("#btn_guardarAgrupacionCuentas").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "abcAgrupacionCuentas",
                    abc: "' . $abc . '",
                    agc_id: $("#inp_Clave").val(),
                    nombre: $("#inp_Nombre").val(),
                    descripcion: $("#inp_Descripcion").val(),
                },true);
            });

            $("#btn_regresaAgrupacionCuentas").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "main"
                });
                return false;
            });
        ');
    }
    /*     * *********************************************** abc de Agrupacion de Cuentas ************************ */

    private function abcAgrupacionCuentas()
    {
        switch ($this->prm->abc) {
            case 'a':
                $sql = "
                    SELECT agc_id
                    FROM cat_AgrupacionCuentas
                    WHERE agc_id = :agc_id
                ";
                $param = [':agc_id' => $this->prm->agc_id];

                $this->setSql($sql);
                $this->conectar();
                $this->consultar($param);
                $data = $this->extraer();
                $this->desconectar();
                if ($data != "") {
                    $this->mixScript('notify("La clave ' . $this->prm->agc_id . ' ya está registrada en la base de datos","error");');
                } else {
                    AgrupacionCuentas::abc($this->prm);
                    $this->mixScript('
                        notify("El registro se ha agregado correctamente","success");
                        comunica("' . $this->pantalla . '",{
                            metodo: "main"
                        });
                    ');
                }
                break;
            case 'b':
                AgrupacionCuentas::abc($this->prm);
                $this->mixScript('notify("El registro se ha eliminado correctamente","error");');
                $this->llenaTablaAgrupacionCuentas();
                break;
            case 'c':
                $AgrupacionCuentas = new AgrupacionCuentas();
                $AgrupacionCuentas->setAgc_id($this->prm->agc_id);
                $AgrupacionCuentas->select();
                AgrupacionCuentas::abc($this->prm);

                $this->mixScript('
                    notify("El registro se actualizó exitosamente","warning");
                    comunica("' . $this->pantalla . '",{
                        metodo: "main"
                    });
                ');
                break;
        }
    }
}
//linea 6 dic 2017

if (isset($_SESSION[_S]['prm']['C']['2']['2'])) { // Permisos del usuario que puede ver este controlador.
    new ControladorAgrupacionCuentas();
} else {
    WebResponse::sinPermisos();
}
