<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/reporte/validacionPoliza.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorPolizaErronea extends WebResponse
{
    public $lectura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['10']['10']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'mesHasta':
                $this->mesHasta();
                break;
            case 'filtros':
                $this->filtros();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'], $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->PonBarraBotones();
                break;
        }
        $this->returnJson();
    }

    public function PonBarraBotones()
    {
        // Botones y opciones que se dibujarán en la barra de botones
        $botones = '
            <div class="form-inline">
                <a id="opciones" opcion="#myModalPolizaErronea" data-placement="bottom" role="button" class="btn btn-outline-secondary" data-toggle="modal"><i class="fa-solid fa-bars me-2"></i>Opciones</a>
            </div>
        ';
        // Dibujamos la barra de botones
        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                INNER JOIN cg_MesContable USING(msc_id)
            WHERE gpo_id = ? AND ur_id = ? ;
        ');
        $this->consultar(array($this->gpo_id, $this->ur_id));
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') {
                $inicio = 'class="selected"';
            } else {
                $inicio = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <div></div><a class="meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                </li>
            ';

            $contenedor .= '
                <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
            ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        // Dibujamos el contenido
        $this->mixScript('
            // Ocultamos la interfaz de SINPRES, para aprovechar más el espacio de pantalla
            ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $("nav").hide();
            $(".modulos").hide();
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");

            // Para que el contenedor use todo el ancho de pantalla
            if($(".container").css("max-width") == "1170px") {
                $(".container").css("max-width", "100%");
            }

            // Botón regresar
            $(document).on("click", "#btnHomeMenu", function (e) {
                e.preventDefault();
                $("nav").show();
                $(".modulos").show();
                $("#tituloPantalla").show();
                $("#sticky-anchor").hide();
                $("#sticky").hide();
                $(".cabecera").show();
                $("#mainContainer").css("margin-top","40px");

                $("#ayuda").addClass("gris");
                $("#tip").addClass("gris");
                $("nav").menusinpres("clickMenu");
                $(document).off("click", "#btnHomeMenu");
                $("nav").menusinpres("clickMenu");
            });

            $("#opciones").click(function(e){
                console.log($("#cveFin_id").val() + "xx");
                comunica("' . $this->pantalla . '", {
                    metodo: "filtros",
                    mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                    acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0)
                });
            });

            // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
            Mousetrap.bind("esc esc", function(e) {
                e.preventDefault();
                $("#btnHomeMenu").click();
                e.stopPropagation();
            });
        ');

        //limpia el area del contenido
        $this->setContenedor("contenedor");
        $html = '<div id="" style="padding-top:5px;"></div>';
        $this->mixHtml($html);

    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? AND msc_id >= ? ";
        $comboMesHasta->params = array($this->gpo_id, $this->ur_id, $this->prm->mes);
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->comoAtr = 'msc_nombre';
        $comboMesHasta->width = "150px";
        if (isset($this->prm->acumulado)) {
            $comboMesHasta->selecciona = $this->prm->acumulado;
        } else {
            $comboMesHasta->selecciona = $this->prm->mes;
        }
        $this->mixHtml($this->ponCombo($comboMesHasta));
    }

    public function filtros()
    {

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? ;";
        $comboMesDesde->params = array($this->gpo_id, $this->ur_id);
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';
        $comboMesDesde->change = '
            console.log($("#comboMesDesde").val());
            comunica("' . $this->pantalla . '", {
                metodo: "mesHasta",
                mes: $(this).val()
            });
        ';

        $comboClaveFinanciamiento = new stdClass();
        $comboClaveFinanciamiento->sql = "
            SELECT ecf_id, cfi_nombre, cfi_clave
            FROM poa_OrigenRecurso
                INNER JOIN enc_ClaveFinanciamiento ecf USING(ecf_id)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2024 cf USING(cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio)
                INNER JOIN sinpres.cat_ClaveFinanciamiento2024 c USING(cff_id, fte_id, sft_id, ori_id, sor_id)
            WHERE gpo_id = ? AND ur_id = ?
            GROUP BY fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio;
        ";
        $comboClaveFinanciamiento->params = array($this->gpo_id,$this->ur_id);
        $comboClaveFinanciamiento->id = "comboClaveFinanciamiento";
        $comboClaveFinanciamiento->width = "150px";
        $comboClaveFinanciamiento->camposText = "cfi_clave,cfi_nombre";
        $comboClaveFinanciamiento->camposVal = "ecf_id";
        $comboClaveFinanciamiento->soloText = true;
        $comboClaveFinanciamiento->label = "Clave Financiamiento";

        $ue = new stdClass();
        $ue->label = "";

        $comboReporte = new stdClass();
        $comboReporte->id = 'tipoReporte';
        $comboReporte->name = 'tipoReporte';
        $comboReporte->opciones = array("" => "Seleccione una opción de la lista", 1 => "Sólo la UR Actual", 2 => "Todas las UR de la Carga de Trabajo");

        $html = '
            <div id="myModalPolizaErronea" class="modal fade modal-al80porciento" data-keyboard="false" role="dialog" data-bs-backdrop="static" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background:#9d2449; color:white;">
                            <h4 class="modal-title">Generar Reporte de las Pólizas Erróneas</h4>
                            <button id="cerrarModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="" id="frmPolizaErronea">
                                <div class="row">
                                    <fieldset class="col-8" style="padding-right: 1px !important;">
                                        <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                    </fieldset>
                                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '<fieldset class="col-8" style="padding-right: 1px !important;">
                                        <legend>Tipo de Reporte</legend>
                                            ' . $this->ponCombo($comboReporte) . '
                                    </fieldset>' : '') . '
                                </div><br />

                                <div id="periodo" style="display:inline">
                                    <legend>Periodo</legend>
                                    <div class="row row-cols-2">
                                        <fieldset class="col-3">
                                            ' . $this->ponCombo($comboMesDesde) . '
                                        </fieldset>
                                        <fieldset id="mhasta" class="col-3">
                                        </fieldset>
                                    </div>
                                </div><br />
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="imprimir" class="btn btn-outline-danger me-2" data-style="slide-left"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                        </div>
                    </div>
                </div>
            </div>
        ';

        $html .= '<div id="myGrid" ></div>';
        $this->setContenedor("paraModales");
        $this->mixHtml($html);

        $this->mixStyle('
            #cogs table tr:hover{
                background-color:#CCFF99 !important;
            }
        ');

        $this->mixScript('
            $("#myModalPolizaErronea").modal("show");
            v = $("#titulo_reporte");
            v.append($("#btnimprime"))
                .append($("#btnexporta"))
                .append($("#btntabla"))
                .append($("<hr/>"));

            $("#tipoReporte").change(function(e){
                var tipoReporte;
                tipoReporte = (typeof($("#tipoReporte").val()) == "undefined" ? 1:($("#tipoReporte").val()));
                console.log(tipoReporte);
                if(tipoReporte == 1){
                    if($("#checkEP").is(":checked")){
                        $("#EP").css("display", "inline");
                    }else{
                        $("#EP").css("display", "none");
                    }
                    if($("#checkCF").is(":checked")) {
                        $("#CF").css("display", "inline");
                        comunica("' . $this->pantalla . '", {
                            metodo: "claveFinanciamiento",
                            mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                            acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0),
                            programatico: $("#comboprogramatico").val(),
                            federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                            estatal: ($("#checkEstatal").prop("checked") ? 1 : 0),
                            checkCF: ($("#checkCF").prop("checked") ? 1 : 0),
                            cveFin: $("#cveFin_id").val(),
                            tipoReporte: tipoReporte
                        });
                    }else{
                        $("#CF").css("display", "none");
                    }
                    $("#comboue").removeAttr("disabled");
                }else{
                    $("#EP").css("display", "none");
                    if($("#checkCF").is(":checked")) {
                        $("#CF").css("display", "inline");
                        comunica("' . $this->pantalla . '", {
                            metodo: "claveFinanciamiento",
                            mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                            acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0),
                            programatico: "",
                            federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                            estatal: ($("#checkEstatal").prop("checked") ? 1 : 0),
                            checkCF: ($("#checkCF").prop("checked") ? 1 : 0),
                            cveFin: $("#cveFin_id").val(),
                            tipoReporte: tipoReporte
                        });
                    }else{
                        $("#CF").css("display", "none");
                    }
                    $("#comboue").attr("disabled","disabled");
                }
            });

            $("#checkEP").change(function(e){
                var tipoReporte;
                tipoReporte = (typeof($("#tipoReporte").val()) == "undefined" ? 1:($("#tipoReporte").val()));
                console.log(tipoReporte + "x");
                if($(this).is(":checked") && tipoReporte == 1){
                    $("#EP").css("display", "inline");
                    comunica("' . $this->pantalla . '", {
                        metodo: "obraActividad",
                        mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                        acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0),
                        checkEP: ($("#checkEP").prop("checked") ? 1 : 0),
                        programatico: $("#comboprogramatico").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0),
                        checkCF: ($("#checkCF").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val()
                    });
                }else{
                    $("#EP").css("display", "none");
                }
            });

            $("#checkCF").change(function(e){
                var tipoReporte;
                tipoReporte = (typeof($("#tipoReporte").val()) == "undefined" ? 1:($("#tipoReporte").val()));
                console.log(tipoReporte);
                if($(this).is(":checked")) {
                    $("#CF").css("display", "inline");
                    var tipoReporte;
                    tipoReporte = (typeof($("#tipoReporte").val()) == "undefined" ? 1:($("#tipoReporte").val()));
                    console.log(tipoReporte);
                    comunica("' . $this->pantalla . '", {
                        metodo: "claveFinanciamiento",
                        mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                        acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0),
                        programatico: $("#comboprogramatico").val(),
                        federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                        estatal: ($("#checkEstatal").prop("checked") ? 1 : 0),
                        checkCF: ($("#checkCF").prop("checked") ? 1 : 0),
                        cveFin: $("#cveFin_id").val(),
                        tipoReporte: tipoReporte
                    });
                }else{
                    $("#CF").css("display", "none");
                }
            });

            $("#imprimir").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "10"))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "10"))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipoReporte").attr("value", $("#tipoReporte").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGrupos").attr("value", $("#inputGrupos").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputExcluir").attr("value", $("#inputExcluir").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#comboue").find("option:selected").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEnMiles").attr("value", ($("#checkEnMiles").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkSinMov").attr("value", ($("#checkSinMovimientos").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkAgrupadoUr").attr("value", ($("#checkAgrupadoUr").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#comboMesDesde").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesHasta").attr("value", $("#comboMesHasta").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkUR").attr("value", ($("#checkUR").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEP").attr("value", ($("#checkEP").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkTG").attr("value", ($("#checkTG").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkIP").attr("value", ($("#checkIP").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkPG").attr("value", ($("#checkPG").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkPE").attr("value", ($("#checkPE").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkTR").attr("value", ($("#checkTR").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkCF").attr("value", ($("#checkCF").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkRFC").attr("value", ($("#checkRFC").prop("checked") ? 1 : 0)))
                    .append($("<input>").attr("type", "hidden").attr("name", "programatico").attr("value", $("#comboprogramatico").val()))
                    .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin_id").val()))

                $("body").append(form);
                form.submit();
            });

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalPolizaErronea").on("hide.bs.modal", function () {
                console.log("borrado");
                $("#paraModales").html("");
            });
        ');
    }

}

//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['10']['10']['1'])) {
    new ControladorPolizaErronea();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
