<?php
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/banco/banco.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
set_time_limit(0);

class ControladorBancoEgreso extends WebResponse
{
    public $lectura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;

    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['8']['5']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        switch ($this->metodo) {
            case 'dibujaPantalla':
                $this->dibujaPantalla();
                break;
            case 'muestraDatos':
                $this->muestraDatos();
                break;
            case 'claveFinanciamiento':
                $this->claveFinanciamiento();
                break;
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
                opcionConteo(
                    $_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'],
                    $_POST['opcion']['opc_id']
                );
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

    public function PonBarraBotones()
    {
        $botones = '
            <div>
                <a id="opciones" opcion="#myModalOpciones" data-placement="bottom" title="Configurar las opciones de la conciliacion" role="button" class="btn btn-outline-secondary" data-toggle="modal"><i class="fa fa-bars me-1"></i>Opciones</a>
            </div>
            <!-- Un espacio separador, solamente... -->
            <span style="height:6px;"></span>
            <span id="contenedorReporteDiario"></span>
        ';

        // Dibujamos la barra de botones
        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        // Dibujamos el contenido
        $this->mixScript('
            ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();' : '') . '
                // Ocultamos la interfaz de SINPRES, para aprovechar más el espacio de pantalla
                $("nav").hide();
                $(".modulos").hide();
                $("#sticky-anchor").show();
                $("#sticky").show();
                $("#mainContainer").css("margin-top", "");

                comunica("' . $this->pantalla . '",{
                    metodo: "dibujaPantalla",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes")
                });

                // Para que el contenedor use todo el ancho de pantalla
                if( $( ".container" ).css( "max-width" ) == "1170px" ){
                    $( ".container" ).css( "max-width", "100%" );
                }
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                // Botón regresar
                $(document).on("click", "#btnHomeMenu", function (e){
                    e.preventDefault();
                    $("nav").show();
                    $(".modulos").show();
                    $("#tituloPantalla").show();
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();
                    $(".cabecera").show();
                    $("#mainContainer").css( "margin-top","40px" );
                    $("#ayuda").addClass("gris");
                    $("#tip").addClass("gris");
                    $("nav").menusinpres("clickMenu");
                    $(document).off("click", "#btnHomeMenu" );
                    $("nav").menusinpres("clickMenu");
                });

                $("#opciones").click( function(e){
                    console.log( $("#cveFin_id").val() + "xx" );
                    comunica("' . $this->pantalla . '", {
                        metodo: "filtros",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes")
                    });
                });

                $("#checkAcumulado").change(function(e){
                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 ),
                        cveFin: $("#cveFin_id").val(),
                        federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                        estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                    });

                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e){
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });
            ');
    }

    public function dibujaPantalla()
    {
        $html = '<div id="myGridReportEgreso" >
                    <table class="table table-bordered table-hover" style="font-family:arial; font-size:11px" id="tableIngreso" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>BANCO</th>
                                <th>CUENTA</th>
                                <th>ALIAS/NOMBRE</th>
                                <th>FECHA</th>
                                <th>TIPO DE DOCUMENTO</th>
                                <th>REFERENCIA</th>
                                <th>IMPORTE</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>';
        $this->setContenedor("contenedor");

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
            LEFT JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ?');
        $this->consultar([$this->gpo_id, $this->ur_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') {
                $inicio = 'class="active"';
                $active = 'nav-link active';
            } else {
                $inicio = '';
                $active = 'nav-link';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li role="presentation" opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a class=" ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs">
                    <ul class="nav nav-tabs">
                        ' . $pestana . '
                    </ul>
                </div>
            ');
        //fin tabuladores
        $this->mixHtml($html);

        $this->mixScript('
            $("#tituloPantalla").hide();

            // Para que el GRID se ajuste al espacio disponible en pantalla
            $("#myGridReporteEgreso").height(
                $(document).height() -
                $("header").outerHeight() -
                $("#sticky").outerHeight() -
                $("footer").outerHeight() - 315
            );

            comunica("' . $this->pantalla . '",{
                metodo: "muestraDatos",
                mes: 00,
                diario: ( $("#checkDiario").prop("checked") ? 1 : 0 ),
                ingresos: ( $("#checkEgresos").prop("checked") ? 1 : 0 ),
                egresos: ( $("#checkEgresos").prop("checked") ? 1 : 0 ),
                cveFin: $("#cveFin_id").val(),
                federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
            });

            // -------------------------------------------------------------------------------------
            $("#page-tabs a").click( function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    diario: ( $("#checkDiario").prop("checked") ? 1 : 0 ),
                    ingresos: ( $("#checkEgresos").prop("checked") ? 1 : 0 ),
                    egresos: ( $("#checkEgresos").prop("checked") ? 1 : 0 ),
                    cveFin: $("#cveFin_id").val(),
                    federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                    estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                });

                $("#imprimir").attr("cat", seleccionado.attr("href") );
                $("#imprimir").attr("rep", seleccionado.text() );
                e.stopPropagation();
                return false;
            });

            var sortcol = "CLAVE";
            var sortdir = 1;
            var searchString = "";

            function myFilter(item, args) {
                if( item["d"] === null || item["c"] === null ){ return false }
                // Si empieza con numero ... buscamos en la cuenta
                strNumeros = "0123456789";
                if( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ){
                    return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                // si no ... buscamos en el texto
                } else {
                    if (args.searchString != "" && item["d"].toLowerCase().indexOf(args.searchString) == -1 ){
                        return false;
                    }
                }
                return true;
            }

            function comparer(a, b){
                var x = a[sortcol], y = b[sortcol];
                return (x == y ? 0 : (x > y ? 1 : -1));
            }

            $("#buscar").keyup(function (e){
                // ESC limpia el campo
                if (e.which == 27) { this.value = ""; }
                searchString = this.value.toLowerCase();
                updateFilter();
            });

            function FormatoConcepto(row, cell, value, columnDef, dataContext){
                // Cuando sea cancelada ... también tachamos el texto
                return ( dataReporteEgreso[row].epl == "C" || dataReporteEgreso[row].epl == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
            }

            function FormatoMC(row, cell, value, columnDef, dataContext){
                if ( value.substring( 1 ) == "A" ) {
                    return value.substring( 0, 1 );
                } else {
                    return "<span title=\"La póliza corresponde a la cancelación de un compromiso que fué autorizado en diferente mes\">" + value.substring( 0, 1 ) + "<span style=\"color:red\">" + value.substring( 1 ) + "</span></span>";
                }
            }

            $("#myModalOpciones").on("shown.bs.modal", function () {
                setTimeout( function() { $( "#cuentaDesde" ).focus().select() }, 100 );
            });

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalOpciones").on("hide.bs.modal", function () {
                $("#declaraRango").remove();
            });
            ');
    }

    private function muestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
            $("#tableIngreso").dataTable().fnDestroy();
            var datos = ' . $data . ';
            $(document).ready(function() {
                $("#tableIngreso").DataTable({
                    "sScrollX": "100%",
                    "sScrollXInner": "100%",
                    "aaSorting": [],
                    "bProcessing": true,
                    "select": true,
                    "sDom": "lfrtip",
                    "language": {
                        "lengthMenu": "Mostrar _MENU_",
                        "zeroRecords": "Sin resultados",
                        "info": "Página _PAGE_ de _PAGES_",
                        "infoEmpty": "No hay datos",
                        "processing": "procesando...",
                        "infoFiltered": "(Filtrando de _MAX_ registros)",
                        "search": "Buscar:",
                        "paginate": {
                        "previous": "Anterior",
                        "next": "Siguiente",
                        "first": "Primera",
                        "last": "Última"
                        }
                    },
                    "data": datos.data,
                    "columns": [
                        {"data": "banco_nombre"},
                        {"data": "banco_cuenta"},
                        {"data": "banco_alias"},
                        {"data": "fechaReferencia"},
                        {"data": "tdf_id"},
                        {"data": "referencia"},
                        {"data": "pdx_importe"}
                    ],
                    "columnDefs": [
                        {
                            render: function (data, type, full, meta) {
                            return "<div style=\"white-space:nowrap; overflow:hidden; text-overflow:ellipsis;\">" + data + "</div>";
                            },
                            targets: 2
                        },
                        {
                            render: function (data, type, full, meta) {
                            return "<div class=\"number\">" + $.number(data, 2, ".", ",") + "</div>";
                            },
                            targets: 6
                        }
                    ],
                    "scrollX": true,
                    "scrollY": "35vh",
                    "scrollCollapse": true,
                    "responsive": true,
                    "autoWidth": true,
                    "fixedColumns": true,
                    "lengthMenu": [[250, 500, 750, 1000], [250, 500, 750, 1000]]
                } );
            } );
        ');

        return false;
    }

    private function DameInformacion()
    {

        $pot_id = 'E';
        $periodo['mesDesde'] = $this->prm->mes; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $this->prm->mes; //Informacion de acuerdo al mes

        $reporte['tipo'] = 1;
        $claveFinanciamiento['ecf_id'] = ''; // busqueda por clave de financiamiento
        $claveFinanciamiento['ter_id'] = '';

        $niveles[9] = 9;
        $niveles[8] = 0;
        $niveles[7] = 0;
        $niveles[6] = 6;
        $niveles[5] = 0;
        $niveles[4] = 0;
        $niveles[3] = 0;
        $niveles[2] = 0;
        $niveles[1] = 1;

        $detalle = false;

        $datos = banco($this->gpo_id, $this->ur_id, $this->ue_id, $pot_id, $periodo, '', $claveFinanciamiento, $reporte, $detalle, '', $niveles);

        return json_encode($datos);
    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING( msc_id ) WHERE gpo_id = ? AND ur_id = ? AND msc_id >= ?";
            $comboMesHasta->id = "comboMesHasta";
            $comboMesHasta->width = "150px";
            $comboMesHasta->label = "hasta";
            $comboMesHasta->selecciona = $this->prm->mes;
            $comboMesHasta->params = [$this->gpo_id, $this->ur_id, $this->prm->mes];

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

    private function claveFinanciamiento()
    {
        $this->setContenedor('con_cvefin');
        $cveFin = new stdClass();
        $cveFin->gpo_id = $this->gpo_id;
        $cveFin->ur_id = $this->ur_id;
        $this->ue_id ? $cveFin->ue_id = $this->ue_id : '';
        $cveFin->filtro = 'OrigenRecurso';
        $cveFin->federal = ((isset($this->prm->federal) && $this->prm->federal == 1) ? true : false);
        $cveFin->estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1) ? true : false);
        $cveFin->tipoReporte = (isset($this->prm->tipoReporte) ? $this->prm->tipoReporte  : 1);
        $cveFin->change = false;

        if ($this->prm->checkCF == 1) {
            $this->mixHtml(ClasificacionFinanciamiento::creaComboCveFin($cveFin));
        } else {
            $this->mixHtml('<div></div>');
        }
    }

    public function filtros()
    {
        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING( msc_id ) WHERE gpo_id = ? AND ur_id = ?" ;
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';
        $comboMesDesde->params = [$this->gpo_id, $this->ur_id];
        $comboMesDesde->change = '
            comunica("' . $this->pantalla . '", {
                metodo: "mesHasta",
                mes: $(this).val()
            });
        ';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING( msc_id ) WHERE gpo_id = ? AND ur_id = ?";
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->width = "150px";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->selecciona = $this->prm->mes;
        $comboMesHasta->params = [$this->gpo_id, $this->ur_id];

        $ue = new stdClass();
        $ue->label = "";

        $html = '
            <div id="myModalOpciones" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color:#9d2449">
                            <h4 class="modal-title">Generar Reporte Bancario Egresos</h4>
                            <button id="cerrarModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    <div class="modal-body">
                        <form action="" class="" id="AddMod">
                            <div class="row">
                                <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                                    <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                </fieldset>
                            </div><br />

                            <div class="row" id="periodo" >
                                <legend>Periodo</legend>
                                <fieldset class="col-md-6">
                                    ' . $this->ponCombo($comboMesDesde) . '
                                </fieldset>
                                <fieldset id="mhasta" class="col-md-6">
                                    ' . $this->ponCombo($comboMesHasta) . '
                                </fieldset>
                            </div><br />

                            <div class="row">
                                <fieldset id="tRec" class="col-xs-2" style="padding-right: 1px !important;" style="display:inline">
                                    <legend>Tipo de Recurso</legend>
                                    <input id="checkFederal" name="checkFederal" type="checkbox" value=1>&nbsp;Federal</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkEstatal" name="checkEstatal" type="checkbox" value=1>&nbsp;Estatal</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>

                                <fieldset id="con_cvefin" class="col-xs-4" style="padding-right: 1px !important;">
                                </fieldset>
                            </div><br />

                            <div class="row">
                                <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                                    <input id="pot_id" name="pot_id" value="E" style="visibility:hidden"></input>
                                </fieldset>
                            </div><br />

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button id="excel" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>
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
            $("#myModalOpciones").modal().show();
            $("#cerrarModal").click(function(){ $("#myModalOpciones").hide(); });

            v = $("#titulo_reporte");
            v.append($("#btnimprime"))
                .append($("#btnexporta"))
                .append($("#btntabla"))
                .append($("<hr/>") );

            $("#checkFederal").change(function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "tipoRecurso",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                    acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 ),
                    cveFin: $("#cveFin_id").val(),
                    federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                    estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                });

                comunica("srv/ClasificacionFinanciamiento.php",{
                    metodoCveFin: "creaComboCveFin",
                    gpo_id: ' . $this->gpo_id . ',
                    ur_id: ' . $this->ur_id . ',
                    filtro: "OrigenRecurso",
                    federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                    estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                });

            });

            $("#checkEstatal").change(function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "tipoRecurso",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                    acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 ),
                    cveFin: $("#cveFin_id").val(),
                    federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                    estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                });

                comunica("srv/ClasificacionFinanciamiento.php",{
                    metodoCveFin: "creaComboCveFin",
                    gpo_id: ' . $this->gpo_id . ',
                    ur_id: ' . $this->ur_id . ',
                    filtro: "OrigenRecurso",
                    federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                    estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 )
                });

            });

            $("#excel").click(function(){
                console.log( $("#comboue").find("option:selected").val() );
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/banco/exp_Banco.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "8" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "5" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "tipoReporte" ).attr("value", $("#tipoReporte").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputGrupos" ).attr("value", $("#inputGrupos").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "inputExcluir" ).attr("value", $("#inputExcluir").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "ue").attr("value", $("#comboue").find("option:selected").val() ) )
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEnMiles" ).attr("value", ( $("#checkEnMiles").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkSinMov" ).attr("value", ( $("#checkSinMovimientos").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkAgrupadoUr" ).attr("value", ( $("#checkAgrupadoUr").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#comboMesDesde").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesHasta").attr("value", $("#comboMesHasta").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkUR").attr("value", ( $("#checkUR").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEP").attr("value", ( $("#checkEP").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkTG").attr("value", ( $("#checkTG").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkIP").attr("value", ( $("#checkIP").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkPG").attr("value", ( $("#checkPG").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkPE").attr("value", ( $("#checkPE").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkTR").attr("value", ( $("#checkTR").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkCF").attr("value", ( $("#checkCF").prop("checked") ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "pot_id").attr("value", $("#pot_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin_id").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkFederal" ).attr("value", ( $("#checkFederal").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEstatal" ).attr("value", ( $("#checkEstatal").prop("checked" ) ? 1 : 0 ) ))
                $("body").append(form);
                form.submit();
            });
            $("#imprimir").click(function(){
                $("#frmOpciones").attr("action","rep/doc/scg/banco/rep_Banco.php").submit();
            });
        ');
    }
}

if (isset($_SESSION[_S]['prm']['C']['8']['5']['1'])) {
    new ControladorBancoEgreso();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
