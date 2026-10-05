<?php

/**
 * Description of ControladorExcepcionesRecursoFederal
 *
 * @author ?
 * @update Vargas Gomez, Roberto <roberto.vargas@finanzasoaxaca.gob.mx>
 * Created: 16-ene-2019
 * Comment:
 * Se implementa verificación de si el usuario tiene los permisos
 * Regla de Negocio:
 * Esta pantalla pregunta por permisos
 *
 * */

session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';

class ControladorExcepcionesRecursoFederal extends WebResponse
{
    public function __construct()
    {
        parent::__construct();
        $this->pantalla = 'mga/scp/ControladorExcepcionesRecursoFederal.php';

        switch ($_SESSION[_S]['nvj_id']) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                switch ($this->metodo) {
                    case 'ActualizarExcepciones':
                        $this->ActualizarExcepciones();
                        break;
                    case 'AgregarExcepciones':
                        $this->AgregarExcepciones();
                        break;
                    case 'llenarTabla':
                        $this->llenarTabla();
                        break;
                    case 'TipoClc':
                        $this->TipoClc();
                        break;
                    case 'SubtipoClc':
                        $this->SubtipoClc();
                        break;
                    case 'DatosUr':
                        $this->DatosUr();
                        break;
                    default:
                        $this->main();
                        break;
                }
                break;
            default:
                break;
        }
        $this->returnJson();
    }

    public function main()
    {
        $this->agregaTools('&nbsp;');
        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnregresarindex';
        $btnregresarindex->name = 'btnregresarindex';
        $btnregresarindex->class = 'regresar mb-1';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->color = 'gris';
        $btnregresarindex->value = 'Regresar';
        /* BOTON DONDE SE GENERAN LAS CLC'S SOLO UR */

        $botones = '
            <div style="width:100%; padding-left:35%; padding-bottom:120px;">
                <div titpp="Telefonía convencional" class="boton_opcion" id="bloquear">
                    <div class="boton_opcion_titulo mb-4" ><strong> Agregar Excepción </strong>
                    </div>
                    <div>
                        <strong><img class = "mb-4" src="img/AgregarExcepcion.png"><br/></strong>
                        <strong><small></small></strong> 
                    </div>
                </div>
                <div titpp="Telefonía convencional" class="boton_opcion" id="desbloquear">
                    <div class="boton_opcion_titulo  mb-4" ><strong>Eliminar Excepción</strong>
                    </div>
                    <div>
                        <strong><img class = "mb-4" src="img/QuitarExcepcion.png"><br/></strong>
                        <strong><small></small></strong> 
                    </div>
                </div>
                <div titpp="Telefonía convencional" class="boton_opcion" id="consultar">
                    <div class="boton_opcion_titulo mb-4" ><strong>Consulta Excepción</strong>
                    </div>
                    <div>
                        <strong><img class = "mb-4" src="img/ConsultaExcepciones.png"><br/></strong>
                        <strong><small></small></strong> 
                    </div>
                </div>
            </div>';

        $this->mixHtml($botones);

        $this->mixScript('
            $("#desbloquear").click(function(){
                comunica("mga/scp/ControladorExcepcionesRecursoFederal.php",{
                metodo:"AgregarExcepciones",
                tarea:"eliminar"
                });
                return false;
            });

            $("#bloquear").click(function(){
                comunica("mga/scp/ControladorExcepcionesRecursoFederal.php",{
                metodo:"AgregarExcepciones",
                tarea:"agregar"
                });
                return false;
            });

            $("#consultar").click(function(){
                comunica("mga/scp/ControladorExcepcionesRecursoFederal.php",{
                metodo:"AgregarExcepciones",
                tarea:"consultar"
                });
                return false;
            });
        ');
    }

    private function AgregarExcepciones()
    {
        $tarea = (isset($this->prm->tarea)) ? $this->prm->tarea : '';
        $html = '';
        $tmp = '';
        $script = $this->getScript();
        $wh = '';
        if ($_SESSION[_S]['uni_id'] == 17) {
            $wh = 'dep_id in(44,45,46)';
            $select =
                'CASE dep_id 
                WHEN 44 then "NATIVIDAD"
                WHEN 45 then "GABRIELA"
                WHEN 46 then "ALBERTO" ';
        } else {
            $wh = 'dep_id in(48,49)';
            $select = '
                CASE dep_id 
                WHEN 48 then "AMARILÍS"
                WHEN 49 then "ALEJANDRINO" ';
        }

        $depto = new stdClass();
        $depto->id = 'depto';
        $depto->class = 'limpiar mb-3';
        $depto->sql = 'select dep_id,' . $select . ' END as dep_nombre  FROM cat_Departamento WHERE ' . $wh . ';';
        $depto->label = 'SELECCIONA EL DEPARTAMENTO';
        $depto->enRow = 1;
        $depto->change = '
            if( $(this).val() != "" ){
                $("#tcl_id").removeAttr("disabled");
                comunica("' . $this->pantalla . '",{
                metodo:"TipoClc",
                dep_id:$("#depto").val()
                });
            }';

        $cvefin = new stdClass();
        $cvefin->id = 'cvefin';
        $cvefin->class = 'limpiar';
        $cvefin->sql = '
            SELECT ecf_clave,cfi_nombre,ecf_id 
            FROM enc_ClaveFinanciamiento
            INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
            INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id)
            WHERE ter_id = ? AND sts_id = ?;';

        $cvefin->label = 'AGREGAR CLAVE DE FINANCIAMIENTO ';
        $cvefin->name = 'ecf_clave[]';
        $cvefin->enDiv = true;
        $cvefin->detonar = false;
        $cvefin->mensaje = false;
        $cvefin->multiple = 'multiple';
        $cvefin->comoAtr = 'ecf_id';
        $cvefin->search = true;
        $cvefin->params = ['B', 1];

        $comboClc = new stdClass();
        $comboClc->sql = '
            SELECT "X" tcl_id,"SELECCIONAR TODOS LOS REGISTROS" as tcl_nombre
            UNION ALL    
            SELECT tcl_id,tcl_nombre FROM enc_FiltroClc 
            INNER JOIN cat_TipoClc USING(tcl_id)
                GROUP BY tcl_id';

        $comboClc->label = 'SELECCIONA EL TIPO DE CLC';
        $comboClc->id = 'tcl_id';
        $comboClc->name = 'tcl_id[]';
        $comboClc->enDiv = true;
        $comboClc->detonar = false;
        $comboClc->mensaje = false;
        $comboClc->multiple = 'multiple';
        $comboClc->comoAtr = 'tcl_nombre';
        $comboClc->search = true;
        $comboClc->change = '
            if ($(this).val() != "") {
                if ($("#tcl_id").val() == "X") {
                    $("#tcl_id").attr("disabled", "disabled");
                } else {
                    var arr = $("#tcl_id").val();
                    var bandera = 0;
                    var todos = 0;
                    if (arr.length > 0) {
                        for (i = 0; i <= arr.length; i++) {
                            if (arr[i] == "") {
                                bandera = 1;
                            }
                            if (arr[i] == "X") {
                                todos = 1;
                            }
                        }

                        if (bandera && todos) {
                            notify("Selección no válida para el Tipo de CLC, verifique", "warning");
                            return false;
                        } else if(todos){
                            $("#tcl_id").attr("disabled", "disabled");
                        } else {
                            $("#tcl_id").find("option[value=X]").attr("disabled", "disabled");
                        }
                    } else {
                        $("#tcl_id").find("option[value=X]").attr("disabled", "disabled");
                    }
                }
                if (!bandera) {
                    $("#stc_id").removeAttr("disabled");
                    comunica("' . $this->pantalla . '", {
                        metodo: "SubtipoClc",
                        tcl_id: $("#tcl_id").val(),
                        dep_id: $("#depto").val(),
                        tarea: "' . $tarea . '"
                    });
                    $("#tcl_id").selectpicker("refresh");
                }
            } else {
                notify("Selección no válida para el Tipo de CLC, verifique", "warning");
                return false;
            }';

        $stc_id = new stdClass();
        $stc_id->sql = '
            SELECT "X" stc_id,"SELECCIONAR TODOS LOS REGISTROS" as stc_nombre
                UNION ALL
                SELECT concat(tcl_id,"_",stc_id) as stc_id,stc_nombre 
                FROM enc_FiltroClc 
                INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                GROUP BY tcl_id,stc_id,stc_nombre;';

        $stc_id->label = 'SELECCIONA EL SUBTIPO DE CLC';
        $stc_id->id = 'stc_id';
        $stc_id->class = 'mb-4';
        $stc_id->name = 'stc_id[]';
        $stc_id->enDiv = true;
        $stc_id->detonar = false;
        $stc_id->mensaje = false;
        $stc_id->multiple = 'multiple';
        $stc_id->search = true;
        $stc_id->change = '
            if( $(this).val() != "" ){
                if($("#stc_id").val() == "X"){
                    $("#stc_id").attr("disabled","disabled");
                }else{
                    $("#stc_id").find("option[value=X]").attr("disabled","disabled");
                }
                $("#stc_id").selectpicker("refresh");
            }';

        /* COMBO DE LAS UNIDADES REPONSABLES */
        $combour = new stdClass();
        $combour->sql = '
            SELECT 0 AS ur_id,"TODOS LOS REGISTROS" as ur_nombre,"" ur_clave
            UNION ALL
                SELECT concat(gpo_id,ur_id)ur_id,ur_nombre,ur_clave
                FROM enc_FiltroClc 
                INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                INNER JOIN aux_UnidadResponsable USING(gpo_id,ur_id)    
                GROUP BY gpo_id,ur_id,ur_nombre,ur_clave;';

        $combour->id = 'ur_id';
        $combour->label = 'SELECCIONA LA UNIDAD RESPONSABLE';
        $combour->name = 'ur_id[]';
        $combour->enDiv = true;
        $combour->detonar = false;
        $combour->mensaje = false;
        $combour->multiple = 'multiple';
        $combour->comoAtr = 'ur_clave';
        $combour->search = true;
        $combour->change = '
            if( $(this).val() != "" ){
                if($("#ur_id").val() == "0"){
                    $("#ur_id").attr("disabled","disabled");
                }else{
                    $("#ur_id").find("option[value=0]").attr("disabled","disabled");
                }
                $("#ur_id").selectpicker("refresh");
            }';

        $btnMostrar = $AgregarExcep = $btnConsulta = '';
        if ($this->prm->tarea != 'consultar') {

            $btnConsulta = new stdClass();
            $btnConsulta->id = 'btnConsulta';
            $btnConsulta->class = 'Buscar me-1';
            $btnConsulta->value = 'Mostrar Registros ';
            $btnConsulta->text = 'Buscar';
            $btnConsulta->color = 'gris';
            $btnConsulta->icon = 'search me-2';
            $btnConsulta = $this->ponBoton($btnConsulta);

            $AgregarExcep = new stdClass();
            $AgregarExcep->id = 'AgregarExcep';
            if ($this->prm->tarea == 'agregar') {
                $AgregarExcep->value = 'Agregar Excepción';
                $AgregarExcep->icon = 'plus me-2';
                $AgregarExcep->color = 'azul';
            } else {
                $AgregarExcep->value = ' Eliminar Excepción';
                $AgregarExcep->icon = 'times';
                $AgregarExcep->color = 'rojo';
            }

            $AgregarExcep = $this->ponBoton($AgregarExcep);
        }

        if ($this->prm->tarea == 'consultar') {

            $btnMostrar = new stdClass();
            $btnMostrar->id = 'btnMostrar';
            $btnMostrar->class = 'Buscar me-1';
            $btnMostrar->value = 'Mostrar Registros ';
            $btnMostrar->text = 'Buscar';
            $btnMostrar->color = 'gris';
            $btnMostrar->icon = 'search me-2';
            $btnMostrar = $this->ponBoton($btnMostrar);
        }

        $this->mixHtml($tmp);

        $html = '';
        $html .= '
            <div class="row col-md-12 col-xs-12">
                <div class="col-md-6 col-xs-6">
                    ' . $this->templateCombo($depto) . '
                </div>
                <div class="col-md-6 col-xs-6">
                ' . $this->templateCombo($combour) . '
                 </div>
            </div>
            <div class="row col-md-12 col-xs-12">
                <div class="col-md-6 col-xs-6">
                    ' . $this->templateCombo($cvefin) . '
                </div>
                <div class="col-md-6 col-xs-6">
                ' . $this->templateCombo($comboClc) . '
                 </div>
            </div>
            <div class="row col-md-12 col-xs-12 mb-n5">
                <div class="col-md-6 col-xs-6">
                    ' . $this->templateCombo($stc_id) . '
                </div>
            </div>
            <div  class="row col-md-12 col-xs-12 ">
                <div class="col-md-12 col-xs-12 d-grid gap-2 d-md-flex justify-content-md-end mb-4">
                    ' . $btnConsulta . '
                    ' . $AgregarExcep . '
                    ' . $btnMostrar . '
                </div>
            </div>';

        if ($this->prm->tarea != 'consultar') {
            $html .= '
                <div align="right" class="row col-md-12 col-xs-12">
                    <div class="col-md-12 col-xs-12">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <td style="color:#FF4000">
                                    <input  disabled type="checkbox" style="margin-right:7px;" name="check1" id="myCheck"><strong>SELECCIONAR TODOS LOS REGISTROS</strong></input>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>';
        }

        $script = $this->getScript();
        $tabla = new stdClass();

        $tabla->id = 'tbl_ListCLC';
        $tabla->tools = $html;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->tarea = $tarea;
        $tabla->comunica->metodo = 'llenarTabla';
        $tabla->comunica->dep_id = '{$("#depto").val()}';
        $tabla->comunica->tipo = '{$("#tcl_id").val()}';
        $tabla->comunica->stc_id = '{$("#stc_id").val()}';
        $tabla->comunica->ur_id = '{$("#ur_id").val()}';
        $tabla->comunica->ecf_clave = '{$("#cvefin").val()}';
        $tabla->prmSeleccion = ['gpo_id', 'ur_id', 'tcl_id', 'stc_id', 'ecf_id'];
        $tabla->multiseleccionable = true;
        $tabla->numFilas = 400;
        $tabla->filasAmostrar = [10, 20, 50, 100, 150, 400];

        $tr = [];

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'CLAVE';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'NOMBRE';
        array_push($tr, $th);

        if ($this->prm->tarea == 'consultar') {
            $th = new stdClass();
            $th->texto = 'FECHA ALTA';
            array_push($tr, $th);
            $th = new stdClass();
            $th->texto = 'FECHA BAJA';
            array_push($tr, $th);
        }

        $tabla->cuerpo = [];

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['gpo_id', 'ur_id'];
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'tcl_nombre';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'stc_id';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'stc_nombre';
        array_push($tabla->cuerpo, $td);

        $td = new stdClass();
        $td->campoTxt = 'cfi_clave';
        array_push($tabla->cuerpo, $td);
        $td = new stdClass();
        $td->campoTxt = 'cfi_nombre';
        array_push($tabla->cuerpo, $td);
        if ($this->prm->tarea == 'consultar') {
            $td = new stdClass();
            $td->campoTxt = 'FechaAlta';
            array_push($tabla->cuerpo, $td);
            $td = new stdClass();
            $td->campoTxt = 'FechaBaja';
            array_push($tabla->cuerpo, $td);
        }

        $tabla->encabezado = [$tr];
        $this->mixHtml($this->wrPonTabla($tabla));
        $this->recargaCombo($depto);
        $this->recargaCombo($cvefin);
        $this->recargaCombo($comboClc);
        $this->recargaCombo($stc_id);
        $this->recargaCombo($combour);

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnRegresarIndex';
        $btnRegresarIndex->icon = 'solid fa-circle-left me-2';
        $btnRegresarIndex->class = 'btn btn-test regresar me-1';
        $btnRegresarIndex->value = 'Regresar';
        $btnRegresarIndex->color = 'gris';
        $btnRegresarIndex->text = 'Regresar';

        $this->mixScript('
            $("#btnRegresarIndex").click (function(){
                comunica("mga/scp/ControladorExcepcionesRecursoFederal.php",{metodo:"main"});
                return false;
            });
        ');

        $ListadoExcel = '';
        if ($this->prm->tarea == 'consultar') {
            $ListadoExcel = new stdClass();
            $ListadoExcel->id = 'ListadoExcel';
            $ListadoExcel->color = 'verde';
            $ListadoExcel->class = '';
            $ListadoExcel->icon = 'file-excel me-2';
            $ListadoExcel->value = 'Excel';
            $ListadoExcel->click = '
                if($("#depto").val()=="" || $("#depto").val()==null){
                    notify("Elije el departamento","warning");
                    return false;
                }else{
                    var form = $("<form>")
                        .attr("action", "rep/excel/scp/reportes/rep_ExcepcionesRecursoFederal.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .attr("id", "enviaPost")
                        .append($("<input>").attr("type", "hidden").attr("name", "depto").val( $("#depto").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "stc_id").val( $("#stc_id").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "ur_id").val( $("#ur_id").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "cvefin").val( $("#cvefin").val()))
                        .append($("<input>").attr("type", "hidden").attr("name", "tipo").val( $("#tcl_id").val()));
                    $("body").append(form);
                    form.submit();
                    form.remove();
                }';

            $ListadoExcel = $this->ponBoton($ListadoExcel);
        }

        $this->agregaTools(' ' . $this->ponBoton($btnRegresarIndex) . ' ' . $ListadoExcel . ' ');
        $this->mixScript($script . '
            $("#AgregarExcep").click(function() {
                if (seleccionados = $("#tbl_ListCLC").tabla("getFilaSeleccion")) {
                    if ($("#myCheck").is(":checked")) {
                        if ($("tr.multiseleccionable.seleccionable").length == $("tr.multiseleccionable.seleccionable.seleccionado").length) {
                            $("#myCheck").attr("checked", true);
                        } else {
                            $("#myCheck").removeAttr("checked");
                        }
                    }
                    if ($("#cvefin").val() == "" || $("#cvefin").val() == null) {
                        notify("Elige una Clave de Financiamiento", "warning");
                        return false;
                    }
                    if ($("#depto").val() == "" || $("#depto").val() == null) {
                        notify("Elige una Departamento", "warning");
                        return false;
                    }
                    if ($("#ur_id").val() == "" || $("#ur_id").val() == null) {
                        notify("Elige una Unidad Responsable", "warning");
                        return false;
                    }
                    if ($("#tcl_id").val() == "" || $("#tcl_id").val() == null) {
                        notify("Tipo de CLC", "warning");
                        return false;
                    }
                    if ($("#stc_id").val() == "" || $("#stc_id").val() == null) {
                        notify("Elige un Subtipo de CLC", "warning");
                        return false;
                    }
                    comunica("' . $this->pantalla . '", {
                        metodo: "ActualizarExcepciones",
                        registros: $("#myCheck").is(":checked"),
                        depto: $("#depto").val(),
                        ecf_clave: $("#cvefin").val(),
                        tipo: $("#tcl_id").val(),
                        stc_id: $("#stc_id").val(),
                        ur_id: $("#ur_id").val(),
                        tarea: "' . $this->prm->tarea . '",
                        datos: seleccionados
                    });
                }
            });

            $("#myCheck").click(function() {
                $("tr.multiseleccionable.seleccionable.seleccionado td:first-child").click();
                if ($("#myCheck").is(":checked")) {
                    $("tr.multiseleccionable.seleccionable td:first-child").click();
                }
            });

            $("#btnConsulta").click(function() {
                if ($("#cvefin").val() == "" || $("#cvefin").val() == null) {
                    notify("Elige una Clave de Financiamiento", "warning");
                    return false;
                }
                if ($("#depto").val() == "" || $("#depto").val() == null) {
                    notify("Elige una Departamento", "warning");
                    return false;
                }
                if ($("#ur_id").val() == "" || $("#ur_id").val() == null) {
                    notify("Elige una Unidad Responsable", "warning");
                    return false;
                }
                if ($("#tcl_id").val() == "" || $("#tcl_id").val() == null) {
                    notify("Elige un Tipo de CLC", "warning");
                    return false;
                }
                if ($("#stc_id").val() == "" || $("#stc_id").val() == null) {
                    notify("Elige un Subtipo de CLC", "warning");
                    return false;
                } else {
                    ' . $this->wrLlamaLlenaTabla('tbl_ListCLC') . '
                }
            });

            $("#btnMostrar").click(function() {
                if ($("#depto").val() == "" || $("#depto").val() == null) {
                    notify("Elige una Departamento", "warning");
                    return false;
                }
                if ($("#ur_id").val() == "" || $("#ur_id").val() == null) {
                    notify("Elige una unidad Responsable", "warning");
                    return false;
                }
                if ($("#cvefin").val() == "" || $("#ur_id").val() == null) {
                    notify("Elige una Clave de Financiamiento", "warning");
                    return false;
                }
                if ($("#tcl_id").val() == "" || $("#ur_id").val() == null) {
                    notify("Elige un Tipo Clc", "warning");
                    return false;
                }
                if ($("#stc_id").val() == "" || $("#ur_id").val() == null) {
                    notify("Elige un Subtipo Clc", "warning");
                    return false;
                } else {
                    ' . $this->wrLlamaLlenaTabla('tbl_ListCLC') . '
                }
            });
        ');
    }

    private function llenarTabla()
    {
        if ($this->prm->tipo[0] == '') {
            $this->mixScript('
                notify("Selección no válida para el Tipo de CLC, verifique", "warning");
            ');
            return false;
        }
        if ($this->prm->stc_id[0] == '') {
            $this->mixScript('
                notify("Selección no válida para el Subtipo de CLC, verifique", "warning");
            ');
            return false;
        }
        $wf = '';
        $filtro = '';
        $where = '';
        $esp = '';
        $paramswf = [];
        if ($this->prm->dep_id != '') {
            $wf .= ($wf == '') ? 'WHERE dep_id = ? ' : ' AND dep_id = ? ';
            array_push($paramswf, $this->prm->dep_id);
        }
        if ($this->prm->tipo != '' && $this->prm->stc_id != '') {
            if ($this->prm->stc_id[0] == 'X') {
                if (count($this->prm->tipo) > 0) {
                    if ($this->prm->tipo[0] == 'X') {
                        $filtro = '';
                    } else {
                        $bind = '';
                        foreach ($this->prm->tipo as $key => $value) {
                            $bind .= '?, ';
                            array_push($paramswf, $value);
                        }
                        $bind = substr($bind, 0, -2);
                        $wf .= ($wf == '') ? ' WHERE tcl_id in (' . $bind . ') ' : ' AND tcl_id in (' . $bind . ') ';
                    }
                }
            } else {
                $bind = '';
                foreach ($this->prm->stc_id as $dato) {
                    $d = explode('_', $dato);
                    $bind .= '(? , ?), ';
                    array_push($paramswf, $d[0], $d[1]);
                }
                $bind = substr($bind, 0, -2);
                $wf .= ($wf == '') ? '  AND (tcl_id,stc_id) in (' . $bind . ')  ' : '  AND (tcl_id,stc_id) in (' . $bind . ')  ';
            }
        } else {
            if ($this->prm->tarea != 'consultar') {
                return false;
            }
        }

        if ($this->prm->ur_id != '') {
            if (count($this->prm->ur_id) > 0) {
                if ($this->prm->ur_id[0] == '0') {
                    $wf .= '';
                } else {
                    $bind = '';
                    foreach ($this->prm->ur_id as $key => $value) {
                        $bind .= '?, ';
                        array_push($paramswf, $value);
                    }
                    $bind = substr($bind, 0, -2);
                    $wf .= ($wf == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in (' . $bind . ') ' : 'AND CONCAT(gpo_id,ur_id) in (' . $bind . ') ';
                }
            }
        }

        if ($this->prm->ecf_clave != '') {
            if (count($this->prm->ecf_clave) > 0) {
                if ($this->prm->ecf_clave[0] == '0') {
                    $wf .= '';
                } else {
                    $bind = '';
                    foreach ($this->prm->ecf_clave as $key => $value) {
                        $bind .= '?, ';
                        array_push($paramswf, $value);
                    }
                    $bind = substr($bind, 0, -2);
                    $wf .= ($wf == '') ? 'WHERE  ecf_clave  in (' . $bind . ') ' : ' AND ecf_clave in (' . $bind . ') ';
                }
            }
        }

        if ($this->prm->dep_id == '46') {
            $esp = ' AND CONCAT(gpo_id,ur_id) NOT IN ("116","901","902") ';
        }

        $this->conectar();

        switch ($this->prm->tarea) {
            case 'agregar':
                $sql = '
                    SELECT 
                        gpo_id,ur_id,
                        ur_clave,ur_nombre,
                        tcl_id,tcl_nombre,
                        stc_id,stc_nombre,
                        excepcion,enc_ClaveFinanciamiento.ecf_id,
                        cfi_clave,cfi_nombre 
                    FROM enc_FiltroClc 
                        INNER JOIN cat_TipoClc USING (tcl_id)
                        INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                        LEFT  JOIN (SELECT 
                                gpo_id,ur_id,
                                tcl_id,stc_id,
                                dep_id,ecf_id AS excepcion
                            FROM enc_ExcepcionesRecursoFederal
                                INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                            WHERE 
                                dep_id=44 
                                AND (tcl_id,stc_id) in (("A",01))  AND CONCAT(gpo_id,ur_id) in ("101")  
                                AND ecf_clave in("AEABA0116")
                        )ex USING (gpo_id,ur_id,tcl_id,stc_id,dep_id)
                        ,enc_ClaveFinanciamiento
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id)
                        ' . $wf . '
                    ' . $where . '
                        AND  ter_id="B"
                        AND enc_ClaveFinanciamiento.sts_id = 1
                        ' . $esp . '
                    GROUP BY gpo_id,ur_id,tcl_id,stc_id,ecf_id,stc_nombre,excepcion,cfi_clave,cfi_nombre
                    HAVING ISNULL(excepcion);
                ';
                break;
            case 'eliminar':
                $sql = '
                    SELECT 
                        gpo_id,ur_id,
                        ur_clave,ur_nombre,
                        tcl_id,tcl_nombre,
                        stc_id,stc_nombre,
                        ecf_id,cfi_clave,
                        cfi_nombre 
                    FROM  enc_ExcepcionesRecursoFederal
                        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                        INNER JOIN cat_TipoClc USING (tcl_id)
                        INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING (cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        ' . $wf . ' 
                    ' . $where . '
                     GROUP BY gpo_id,ur_id,tcl_id,stc_id,dep_id,ecf_id,stc_nombre,cfi_clave,cfi_nombre
                    ';
                break;
            case 'consultar':
                $sql = '
                    SELECT 
                        gpo_id,ur_id,
                        ur_nombre,tcl_id,
                        tcl_nombre,stc_id,
                        stc_nombre,cfi_clave,
                        cfi_nombre,ecf_id,
                        dep_id,
                        MAX(IF(bit_observacion = "EXCEPCIÓN AGREGADA"
                            , DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S")
                            ,"")) AS FechaAlta,
                        IF(MAX(IF(bit_observacion = "EXCEPCIÓN AGREGADA"
                            , DATE_FORMAT(b.usr_fecha
                            ,"%Y/%m/%d %H:%i:%S"),"")) < MAX(IF(bit_observacion = "EXCEPCIÓN ELIMINADA"
                                ,DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S")
                                ,"")
                            ),
                        MAX(IF(bit_observacion = "EXCEPCIÓN ELIMINADA"
                            ,DATE_FORMAT(b.usr_fecha,"%Y/%m/%d %H:%i:%S")
                            ,"")), "") AS FechaBaja 
                    FROM enc_ExcepcionesRecursoFederal
                        RIGHT JOIN ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal b USING (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id)
                        INNER JOIN cat_UnidadResponsable USING (gpo_id,ur_id)
                        INNER JOIN cat_TipoClc USING (tcl_id)
                        INNER JOIN cat_SubtipoClc USING (tcl_id,stc_id)
                        INNER JOIN enc_ClaveFinanciamiento USING (ecf_id)
                        INNER JOIN ' . BD_SINPRES . '.cat_ClaveFinanciamiento' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id,cfi_id,cfi_anio)
                        ' . $wf . '
                    ' . $where . '
                    GROUP BY gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,stc_nombre,cfi_clave,cfi_nombre
                    ORDER BY gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id;
                    ';
                break;
        }

        $this->setSql($sql);
        $this->setParams($paramswf);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tbl_ListCLC';
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 400;
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('notify("No hay registros","error");');
        } else {
            $this->mixScript('$("#myCheck").removeAttr("disabled");');
        }
    }

    public function ActualizarExcepciones()
    {
        $wh = $wf = '';
        $this->prm->registros = isset($this->prm->registros) ? $this->prm->registros : 0;
        $this->prm->tarea = isset($this->prm->tarea) ? $this->prm->tarea : '';
        $this->prm->depto = isset($this->prm->depto) ? $this->prm->depto : '';
        $ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != '::1') ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';
        $user = $_SESSION[_S]['usr_id'];
        $filtro = '';
        $esp = '';

        if ($this->prm->depto != '') {
            $wf .= ($wf == '') ? 'WHERE dep_id=' . $this->prm->depto . ' ' : ' AND dep_id=' . $this->prm->depto . ' ';
        }

        if ($this->prm->registros == 1) { //Se selecciona 1 cuando se seleccionan todos los registros en la tabla

            if ($this->prm->stc_id[0] == 'X' && $this->prm->stc_id != '') {    // la primer opcion son todas lOs subtipo

                if (count($this->prm->tipo) > 0) {

                    if ($this->prm->tipo[0] == 'X' && $this->prm->tipo != '') {
                        $filtro = '';
                    } else {
                        $wf .= ($wf == '') ? ' WHERE tcl_id in("' . implode('","', $this->prm->tipo) . '") ' : ' AND tcl_id in("' . implode('","', $this->prm->tipo) . '") ';
                    }
                }
            } else {
                if (count($this->prm->stc_id) > 0 && $this->prm->stc_id != '') {
                    foreach ($this->prm->stc_id as $dato) {
                        $d = explode('_', $dato);
                        $filtro .= '("' . $d[0] . '",' . $d[1] . '),';
                    }

                    $wf .= ($wf == '') ? '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ' : '  AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ')  ';
                }
            }

            if (count($this->prm->ur_id) > 0 && $this->prm->ur_id != '') {
                if ($this->prm->ur_id[0] == '0') {
                    $wf .= '';
                } else {
                    $wf .= ($wf == '') ? 'WHERE  CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ' : 'AND CONCAT(gpo_id,ur_id) in("' . implode('","', $this->prm->ur_id) . '") ';
                }
            }

            if (count($this->prm->ecf_clave) > 0 && $this->prm->ecf_clave != '') {
                $wf .= ($wf == '') ? 'WHERE  ecf_clave  in("' . implode('","', $this->prm->ecf_clave) . '") ' : 'AND ecf_clave  in("' . implode('","', $this->prm->ecf_clave) . '") ';
            }

            if ($this->prm->depto == '46') {
                $esp = ' AND CONCAT(gpo_id,ur_id) NOT IN ("116","901","902") ';
            }

            if ($this->prm->tarea == 'agregar') {
                $this->conectar();
                $this->setSql(' 
                    INSERT INTO enc_ExcepcionesRecursoFederal (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,usr_id)
                    SELECT gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,' . $user . '  
                    FROM(
                        SELECT gpo_id,ur_id,tcl_id,stc_id,enc_ClaveFinanciamiento.ecf_id,excepcion,dep_id,' . $user . ' 
                        FROM  enc_FiltroClc
                        LEFT  JOIN 
                        (
                            SELECT gpo_id,ur_id,tcl_id,stc_id,dep_id,ecf_id as excepcion
                            FROM enc_ExcepcionesRecursoFederal
                            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                            ' . $wf . '     
                        )ex using(gpo_id,ur_id,tcl_id,stc_id,dep_id)
                        ,enc_ClaveFinanciamiento
                        INNER JOIN ' . BD_SINPRES . '.cat_SuborigenRecurso' . APP_EJERCICIO . ' USING(cff_id, fte_id, sft_id, ori_id, sor_id)
                        ' . $wf . ' ' . $esp . '
                        GROUP BY gpo_id,ur_id,tcl_id,stc_id,dep_id,ecf_id,excepcion
                        HAVING ISNULL(excepcion)
                    )a;
                ');
                $this->consultar(); // insertamos en la tabla de excepciones
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal (gpo_id, ur_id, tcl_id, stc_id, ecf_id, dep_id, bit_observacion, usr_ip, usr_id, usr_fecha)
                    SELECT gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,"EXCEPCIÓN AGREGADA" as bit_observacion,INET_ATON("' . $ip . '") as usr_ip,' . $user . ' as usr_id,NOW() as usr_fecha
                    FROM enc_ExcepcionesRecursoFederal
                            INNER JOIN enc_ClaveFinanciamiento USING(ecf_id)
                            ' . $wf . ' ' . $esp . ';
                ');

                $this->consultar();    // insertamos en la bitacora
                $this->desconectar();
                $this->mixScript(' notify( "Las Excepciones se han ingresado correctamente","success");');
            }

            if ($this->prm->tarea == 'eliminar') {
                $this->conectar();
                $this->setSql('
                    INSERT INTO ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                    SELECT gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,"EXCEPCIÓN ELIMINADA" as bit_observacion,INET_ATON("' . $ip . '") as usr_ip,' . $user . ' as usr_id,NOW() as usr_fecha
                    FROM enc_ExcepcionesRecursoFederal  
                    INNER JOIN enc_ClaveFinanciamiento using(ecf_id) 
                    ' . $wf . ' 
                    GROUP BY gpo_id,ur_id,tcl_id,stc_id,dep_id,ecf_id; 
                ');
                $this->consultar(); // insertamos en la bitacora la eliminacion de de la excepcion
                $this->setSql(' 
                    DELETE enc_ExcepcionesRecursoFederal 
                    FROM enc_ExcepcionesRecursoFederal 
                    INNER JOIN enc_ClaveFinanciamiento using(ecf_id)
                    ' . $wf . ' 
                ');
                $this->consultar(); // eliminamos la excepcion
                $this->desconectar();
                $this->mixScript(' notify( "Las Excepciones se han eliminado correctamente","success");');
            }

            $this->main();
        } else {

            if ($this->prm->tarea == 'agregar') {
                if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
                    foreach ($this->prm->datos as $key => $val) {
                        $this->conectar();
                        $this->setSql(
                            '
                            INSERT INTO enc_ExcepcionesRecursoFederal (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id, usr_id)
                            VALUES (' . $val->gpo_id . ',' . $val->ur_id . ',"' . $val->tcl_id . '",' . $val->stc_id . ',' . $val->ecf_id . ',' . $this->prm->depto . ',' . $user . ')'
                        );
                        $this->consultar();

                        $sql = 'INSERT INTO ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                                values (' . $val->gpo_id . ',' . $val->ur_id . ',"' . $val->tcl_id . '",' . $val->stc_id . ',' . $val->ecf_id . ',' . $this->prm->depto . ',"EXCEPCIÓN AGREGADA",INET_ATON("' . $ip . '"),' . $user . ',NOW());';
                        $this->setSql($sql);
                        $this->consultar();
                        $this->desconectar();
                    }
                }
                $this->mixScript(' notify( "Las Excepciones se han ingresado correctamente","success");');
            }

            if ($this->prm->tarea == 'eliminar') {
                if (count($this->prm->datos) > 0 && $this->prm->datos != '') {
                    foreach ($this->prm->datos as $key => $val) {
                        $this->conectar();
                        $sql = '
                            INSERT INTO ' . BD_BITACORA . '.bit_ExcepcionRecursoFederal (gpo_id,ur_id,tcl_id,stc_id,ecf_id,dep_id,bit_observacion,usr_ip,usr_id,usr_fecha)
                                select ' . $val->gpo_id . ',' . $val->ur_id . ',"' . $val->tcl_id . '",' . $val->stc_id . ',' . $val->ecf_id . ',' . $this->prm->depto . ',"EXCEPCIÓN ELIMINADA",INET_ATON("' . $ip . '"),' . $user . ',NOW() FROM enc_ExcepcionesRecursoFederal
                                WHERE ecf_id=' . $val->ecf_id . ' AND  gpo_id =' . $val->gpo_id . ' AND ur_id = ' . $val->ur_id . ' AND tcl_id ="' . $val->tcl_id . '" AND stc_id = ' . $val->stc_id . ' AND dep_id = ' . $this->prm->depto . '; ;';
                        $this->setSql($sql);
                        $this->consultar();
                        $this->setSql('
                            DELETE FROM enc_ExcepcionesRecursoFederal
                            WHERE ecf_id=' . $val->ecf_id . ' AND  gpo_id =' . $val->gpo_id . ' AND ur_id = ' . $val->ur_id . ' AND tcl_id ="' . $val->tcl_id . '" AND stc_id = ' . $val->stc_id . ' AND dep_id = ' . $this->prm->depto . ';
                        ');
                        $this->consultar();
                        $this->desconectar();
                    }
                }
                $this->mixScript(' notify( "Las Excepciones se han eliminado correctamente","success");');
            }
            $this->main();
        }
    }

    private function TipoClc()
    {
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';
        $where = '';
        if ($this->prm->dep_id != '') {
            $combo = new stdClass();
            $combo->sql = '
                SELECT "X" tcl_id,"SELECCIONAR TODOS LOS REGISTROS" as tcl_nombre
                UNION ALL
                SELECT tcl_id,tcl_nombre FROM enc_FiltroClc
                INNER JOIN cat_TipoClc USING(tcl_id)
                WHERE dep_id = ?
                GROUP BY tcl_id,dep_id';
            $combo->id = 'tcl_id';
            $combo->detonar = false;
            $combo->name = 'tcl_id[]';
            $combo->params = [$this->prm->dep_id];
            $this->recargaCombo($combo);
        } else {
            $this->mixScript(' alert("Elige el departamento");');
            return;
        }
    }

    private function SubtipoClc()
    {
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';
        $where = $filtro = '';

        if (count($this->prm->tcl_id) > 0 && $this->prm->tcl_id != '') {
            if ($this->prm->tcl_id[0] == 'X') {
                $filtro = '';
            } else {
                $filtro = ' AND tcl_id in("' . implode('","', $this->prm->tcl_id) . '") ';
            }
            $combo = new stdClass();
            $combo->sql = '
                SELECT "X" stc_id,"SELECCIONAR TODOS LOS REGISTROS" as stc_nombre
                UNION ALL
                SELECT concat(tcl_id,"_",stc_id) as stc_id,stc_nombre
                            FROM enc_FiltroClc 
                            INNER JOIN cat_SubtipoClc USING(tcl_id,stc_id)
                            WHERE dep_id = ? ' . $filtro . ' 
                            GROUP BY tcl_id,stc_id,stc_nombre;';
            $combo->id = 'stc_id';
            $combo->detonar = false;
            $combo->name = 'stc_id[]';
            $combo->params = [$this->prm->dep_id];
            $this->recargaCombo($combo);
        } else {
            $this->mixScript(' alert("Elige un Tipo CLC");');
            return;
        }
    }

    private function DatosUr()
    {
        $where = $filtro = '';
        $this->prm->dep_id = (isset($this->prm->dep_id)) ? $this->prm->dep_id : '';

        if ($this->prm->stc_id[0] == 'X') {
            if (count($this->prm->tcl_id) > 0) {
                if ($this->prm->tcl_id[0] == 'X') {
                    $filtro = '';
                } else {
                    $filtro = ' AND tcl_id in("' . implode('","', $this->prm->tcl_id) . '") ';
                }
            }
        } else {

            foreach ($this->prm->stc_id as $dato) {
                $d = explode('_', $dato);
                $filtro .= '("' . $d[0] . '",' . $d[1] . '),';
            }

            $filtro = ' AND (tcl_id,stc_id) in (' . trim($filtro, ',') . ') ';
        }

        $combo = new stdClass();
        $combo->sql = '
            SELECT 0 AS ur_id,"TODOS LOS REGISTROS" as ur_nombre
            UNION ALL
                SELECT concat(gpo_id,ur_id)ur_id,ur_nombre
                FROM enc_FiltroClc 
                INNER JOIN cat_UnidadResponsable USING(gpo_id,ur_id)
                WHERE dep_id = ? ' . $filtro . '
                GROUP BY gpo_id,ur_id;';

        $combo->id = 'ur_id';
        $combo->params = [$this->prm->dep_id];
        $combo->detonar = false;
        $combo->detonar = false;
        $combo->name = 'ur_id[]';
        $this->recargaCombo($combo);
    }
}

//Verifica si el usuario tiene los permisos
if (isset($_SESSION[_S]['prm']['M']['7']['3']['78'])) {
    new ControladorExcepcionesRecursoFederal();
} else {
    WebResponse::sinPermisos();
}
