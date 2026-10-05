<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/conciliacion/conciliacion42210.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';

class Controlador42210 extends WebResponse
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
        $this->lectura = isset($_SESSION[_S]['prm']['C']['10']['7']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != "") {
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
            case 'obraActividad':
                $this->obraActividad();
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
            default:
                opcionConteo($_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                $this->ponBarraBotones();
                break;
        }

        $this->returnJson();
    }

    public function ponBarraBotones()
    {
        // Botones y opciones que se dibujarán en la barra de botones
        $botones = '
                <div class="form-inline">
                    <a id="opciones" opcion="#myModal42210" data-placement="bottom" role="button" class="btn btn-outline-secondary" data-toggle="modal"><i class="fa-solid fa-bars me-2"></i>Opciones</a>
                    &nbsp;&nbsp;&nbsp;
                    <input id="checkAcumulado" name="checkAcumulado" type="checkbox" value=1>&nbsp;Acumulado</input>&nbsp;&nbsp;&nbsp;
                </div>
            ';
        // Dibujamos la barra de botones
        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        // Dibujamos el contenido
        $this->mixScript('
                // Ocultamos la interfaz de SINPRES, para aprovechar más el espacio de pantalla
                ' . (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
            : '') . '
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                $("nav").hide();
                $(".modulos").hide();
                $("#sticky-anchor").show();
                $("#sticky").show();
                $("#mainContainer").css("margin-top", "");

                $("div[aria-describedby=cargaDatosCgCveFin]").html("");

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
                    console.log($("#ClaveFinan").attr("ecf") + "xx");
                    comunica("' . $this->pantalla . '", {
                        metodo: "filtros",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0)
                    });
                });

                $("#checkAcumulado").change(function(e){
                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
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

        $this->dibujaPantalla();
    }

    public function dibujaPantalla()
    {
        $this->setContenedor("contenedor");
        //borra el HTML de la modal de clave financiamiento
        $this->mixScript('$("div[aria-describedby=cargaDatosCgCveFin]").remove();');
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML());
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                INNER JOIN cg_MesContable USING(msc_id)
            WHERE gpo_id = ' . $this->gpo_id . ' AND ur_id = ' . $this->ur_id . '
        ');
        $this->consultar();
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') {
                $inicio = 'class="active"';
                $active = 'active';
            } else {
                $inicio = '';
                $active = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li role="presentation" opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a role="tab" class="nav-link '.$active.' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs"><ul class="nav nav-tabs">
                ' . $pestana . '
                </ul></div>
            ');
        //fin dibujar tabuladores

        $html = '<div id="myGridReporte42210" class="tabla-contenedor col-md-12">
                <table id="reporte-42210" class="table table-striped table-bordered">
                        <thead class="sticky-top" style="z-index:100;">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader" style="text-align:left;">PARTIDA GENÉRICA</th>
                                <th class="warning" role="columnheader" >NOMBRE PARTIDA</th>
                                <th class="warning" role="columnheader" style= "text-align:left;">CLAVE DE FINANCIAMIENTO</th>
                                <th class="warning" role="columnheader" >NOMBRE CLAVE</th>
                                <th class="warning" role="columnheader" style= "text-align:right;">ESTATAL</th>
                                <th class="warning" role="columnheader" style= "text-align:right;">FEDERAL</th>
                                <th class="warning" role="columnheader" style= "text-align:right;">TOTAL</th>
                            </tr>
                        </thead>
                    <tbody></tbody>
                </table>                
        </div>';
        $this->mixHtml($html);
        
        $this->mixStyle('
            .tabla-contenedor table > tbody > tr.colorea42210 > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
                $("#tituloPantalla").hide();

                // Para que el GRID se ajuste al espacio disponible en pantalla
                $("#myGridReporte42210").height(
                    $(document).height() -
                    $("header").outerHeight() -
                    $("#sticky").outerHeight() -
                    $("footer").outerHeight() - 120
                );

                // -------------------------------------------------------------------------------------
                $("#page-tabs a").click(function(e) {
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("li").addClass("active");
                    $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("a").addClass("active");

                    comunica("' . $this->pantalla . '",{
                        metodo: "muestraDatos",
                        mes: seleccionado.attr("mes"),
                        acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0)
                    });

                    e.stopPropagation();
                    return false;
                });

                var sortcol = "CLAVE";
                var sortdir = 1;
                var searchString = "";

                function myFilter(item, args) {
                    if(item["d"] === null || item["c"] === null){ return false }
                    // Si empieza con numero ... buscamos en la cuenta
                    strNumeros = "0123456789";
                    if(args.searchString != "" && strNumeros.indexOf(args.searchString.substring(0,1)) !== -1){
                        return (item["c"].substring(0,args.searchString.length) == args.searchString);
                    // si no ... buscamos en el texto
                    }else{
                        if(args.searchString != "" && item["d"].toLowerCase().indexOf(args.searchString) == -1){
                            return false;
                        }
                    }
                    return true;
                }

                function comparer(a, b) {
                    var x = a[sortcol], y = b[sortcol];
                    return (x == y ? 0 : (x > y ? 1 : -1));
                }

                $("#buscar").keyup(function (e) {
                    // ESC limpia el campo
                    if (e.which == 27) { this.value = ""; }
                    searchString = this.value.toLowerCase();
                    updateFilter();
                });

                function FormatoConcepto(row, cell, value, columnDef, dataContext) {
                    // Cuando sea cancelada ... también tachamos el texto
                    return (dataReporte42210[row].epl == "C" || dataReporte42210[row].epl == "H" ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
                }

                function FormatoMC(row, cell, value, columnDef, dataContext) {
                    if (value.substring(1) == "A") {
                        return value.substring(0, 1);
                    } else {
                        return "<span title=\"La póliza corresponde a la cancelación de un compromiso que fué autorizado en diferente mes\">" + value.substring(0, 1) + "<span style=\"color:red\">" + value.substring(1) + "</span></span>";
                    }
                }

                function updateFilter() {
                    dataViewReporte42210.setFilterArgs({ searchString: searchString });
                    dataViewReporte42210.refresh();
                }

                function FormatoMoneda(row, cell, value, columnDef, dataContext) {

                    // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
                    strIni = ""
                    strFin = ""

                    if(value === null || value === "" || !(value != 0)){
                        return "";
                    } else {
                        var n = value,

                        c = 2,
                        d = ".",
                        t = ","
                        s = n < 0 ? "-" : "",
                        i = parseInt(n = Math.abs(+n || 0).toFixed(2)) + "",
                        j = (j = i.length) > 3 ? j % 3 : 0;

                        return strIni + s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + strFin;

                        /*return "$" + Number(value).toFixed(2);*/
                    }
                }

                // Cargamos la información a mostrar
                // ---------------------------------------------------------------------------------------------------
                function loadData(json) {
                    $("#reporte-42210 tbody").html("");
                    const tbody = $("#reporte-42210 tbody");
                    dataReporte42210 = [];
                    i = 0;
                    for(var key in json){
                        const formatoMoneda = new Intl.NumberFormat("es-MX", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        var d = (dataReporte42210[i] = {});
                            d["id"] = json[key].key;
                            pg_id = json[key].pg_id;
                            pg_nombre= json[key].pg_nombre
                            ecf_id = json[key].efc_id;
                            cfi_clave = json[key].cfi_clave;
                            cfi_nombre= json[key].cfi_nombre;
                            estatal = (json[key].estatal == 0) ? "0.00" : 
                                formatoMoneda.format(json[key].estatal);
                            federal = (json[key].federal == 0) ? "0.00" : 
                                formatoMoneda.format(json[key].federal);
                            total = (json[key].total == 0) ? "0.00" : 
                                formatoMoneda.format(json[key].total);

                        tbody.append(
                            "<tr class=\' seleccionable \'><td>" 
                            + pg_id + "</td><td>" 
                            + pg_nombre + "</td><td style = \' white-space: nowrap; \'>" 
                            + cfi_clave + "</td><td style = \' white-space: pre-wrap; \'>" 
                            + cfi_nombre + "</td><td style = \' text-align: right; white-space: nowrap; \'>"
                            + estatal + "</td><td style = \' text-align: right; white-space: nowrap; \'>"
                            + federal + "</td><td style = \' text-align: center; white-space: nowrap; \'>" 
                            + total + "</td></tr>");
                        i++;
                    }

                    $(document).ready(function() {
                        $("#reporte-42210 tbody tr").hover(
                            function() {
                                $(this).addClass("colorea42210");
                            },
                            function() {                      
                                $(this).removeClass("colorea42210"); 
                            }
                        ); 
                    
                        $(".seleccionable").click(function() {                                             
                            $(".seleccionable").css("font-weight","normal");
                            $(".seleccionable").removeClass("seleccionado");
                            $(this).addClass("seleccionado");
                            $(this).css("font-weight","bold");
                        });
                    });

                    $(".panel-heading.header").remove();
                    $(".panel-footer").remove();
                    $(".tabla-footer").remove();

                }
            ');
    }

    private function muestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
            loadData(' . $data . ');
        ');

        return false;
    }

    private function DameInformacion()
    {

        if ($this->prm->acumulado != 0) {
            $periodo['mesDesde'] = 1; //Informacion de acuerdo al mes
        } else {
            $periodo['mesDesde'] = $this->prm->mes; //Informacion de acuerdo al mes
        }
        $periodo['mesHasta'] = $this->prm->mes; //Informacion de acuerdo al mes

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $claveFinanciamiento['ecf_id'] = ''; 
        $claveFinanciamiento['ter_id'] = '';

        $niveles[10] = 0;
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

        $datos = conciliacion42210($this->gpo_id, $this->ur_id, $this->ue_id,
            '', $periodo, $programatico, $claveFinanciamiento, $reporte,
            $detalle, 'D', $niveles);

        return json_encode($datos);
    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? AND msc_id >= ? ";
        $comboMesHasta->params = array($this->gpo_id,$this->ur_id,$this->prm->mes);
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->label = "hasta";
        $comboMesHasta->comoAtr = 'msc_nombre';
        $comboMesHasta->width = "150px";
        if (isset($this->prm->acumulado)) {
            $comboMesHasta->selecciona = $this->prm->acumulado;
        } else {
            $comboMesHasta->selecciona = $this->prm->mes;
        }
        $this->mixHtml($this->ponCombo($comboMesHasta));
    }

    private function obraActividad()
    {
        $this->setContenedor('con_obraActividad');

        $prog = new stdClass();
        $prog->gpo_id = $this->gpo_id;
        $prog->ur_id = $this->ur_id;
        $this->ue_id ? $prog->ue_id = $this->ue_id : '';
        $prog->filtro = 'programatico';
        if ($this->prm->checkEP == 1) {
            $this->mixHtml(ClasificacionAdministrativa::listarEpg($prog));
        } else {
            $this->mixHtml('<div></div>');
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
        $cveFin->federal = ((isset($this->prm->federal) && $this->prm->federal == 1)
            ? 1 : 2);
        $cveFin->estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1)
            ? 1 : 2);
        $cveFin->tipoReporte = (isset($this->prm->tipoReporte) ? $this->prm->tipoReporte
            : 1);
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
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING(msc_id) WHERE gpo_id = ? AND ur_id = ? ";
        $comboMesDesde->params = array($this->gpo_id,$this->ur_id);
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = (($this->prm->acumulado == 1) ? 1 : $this->prm->mes);
        $comboMesDesde->width = '150px';
        $comboMesDesde->change = '
                console.log($("#comboMesDesde").val());
                comunica("' . $this->pantalla . '", {
                    metodo: "mesHasta",
                    mes: $(this).val()
                });
            ';
        $ue = new stdClass();
        $ue->label = "";

        $html = '
            <div id="myModal42210" class="modal" data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #9d2449">
                            <h4 class="modal-title" style="color:white;">Generar Reporte de la cuenta 4.2.2.1.0</h4>
                            <button id="cerrarModal" type="button" class="btn-close close" data-dismiss="modal" aria-hidden="true"></button>                            
                        </div>
                    <div class="modal-body">
                        <form action="" class="" id="frm42210">
                            <div class="row">
                                <fieldset class="col-md-6" style="padding-right: 1px !important;">
                                    <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                </fieldset>
                                ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                                <fieldset class="col-md-4" style="padding-right: 1px !important;">
                                    <legend>Tipo de Reporte</legend>
                                        <select name="tipoReporte" id="tipoReporte" class="form-control">
                                            <option value="1">Sólo la UR Actual</option>
                                            <option value="2">Todas las UR de la Carga de Trabajo</option>
                                        </select>
                                </fieldset>
                                ' : '') . '
                            </div><br />

                            <div class="row" id="periodo">
                                        <legend>Periodo</legend>
                                        <fieldset class="col-md-6 col-xs-6">
                                            ' . $this->ponCombo($comboMesDesde) . '
                                        </fieldset>
                                        <fieldset id="mhasta" class="col-md-6 col-xs-6">
                                        </fieldset>
                            </div><br />

                            <div class="row">
                                <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                                    <legend>Niveles a Incluir</legend>
                                    <input id="checkUR" name="checkUR" type="checkbox" value=1 readonly="readonly" checked onclick="javascript: return false;">&nbsp;UR</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkEP" name="checkEP" type="checkbox" value=1>&nbsp;EPG</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkTG" name="checkTG" type="checkbox" value=1>&nbsp;TG</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkIP" name="checkIP" type="checkbox" value=1>&nbsp;IDP</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkPG" name="checkPG" type="checkbox" value=1 checked >&nbsp;PG</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkPE" name="checkPE" type="checkbox" value=1>&nbsp;PE</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkCF" name="checkCF" type="checkbox" value=1 checked >&nbsp;CDF</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkRFC" name="checkRFC" type="checkbox" value=1 checked >&nbsp;RFC</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>
                            </div><br />

                            <div class="row" id="EP" style="display:none">
                                <fieldset id="con_obraActividad" class="col-md-6" style="padding-right: 1px !important;">
                                </fieldset>
                            </div><br />

                            <div class="row" id="CF">

                                <fieldset class="col-md-6" style="padding-right: 1px !important;">
                                    <legend>Tipo de Recurso</legend>
                                    <div class="input-group input-group-sm">
                                        <input id="ClaveFinan" placeholder="Clave de Financiamiento" type="text" maxlength=9 class="form-control F1Catalogo" disabled></input>
                                        <span class="input-group-btn">
                                            <button id="mostrarClafinan" type="submit" class="btn btn-outline-secondary"><i class="fa fa-th"></i></button>
                                        </span>
                                    </div>
                                </fieldset>
                            </div><br />

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button id="excel" class="btn btn-outline-success" data-style="slide-left"><i class="fa-solid fa-file-excel me-1"></i>Excel</button>
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
                $("#myModal42210").modal().show();
                $("#cerrarModal").click(function(){ $("#myModal42210").remove(); });

                v = $("#titulo_reporte");
                v.append($("#btnimprime"))
                    .append($("#btnexporta"))
                    .append($("#btntabla"))
                    .append($("<hr/>"));

                $("#tipoReporte").change(function(e){
                    console.log("tipo reporte");
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

                        }else{
                            $("#CF").css("display", "none");
                        }
                        $("#comboue").removeAttr("disabled");
                    }else{
                        $("#EP").css("display", "none");
                        if($("#checkCF").is(":checked")) {
                            $("#CF").css("display", "inline");

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
                            mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                            acumulado: ($("#checkAcumulado").prop("checked") ? 1 : 0),
                            checkEP: ($("#checkEP").prop("checked") ? 1 : 0),
                            programatico: $("#comboprogramatico").val(),
                            federal: ($("#checkFederal").prop("checked") ? 1 : 0),
                            estatal: ($("#checkEstatal").prop("checked") ? 1 : 0),
                            checkCF: ($("#checkCF").prop("checked") ? 1 : 0),
                            cveFin: $("#ClaveFinan").attr("ecf")
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

                    }else{
                        $("#CF").css("display", "none");
                    }
                });

                $("#excel").click(function(){
                    console.log($("#ClaveFinan").attr("ecf") + 5);
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/reporte/exp_42210.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C"))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "10"))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "7"))

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
                        .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#ClaveFinan").attr("ecf")))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimir").click(function(){
                    $("#frm42210").attr("action","rep/doc/scg/reporte/rep_42210.php").submit();
                });

                // F1 muestra el catálogo */
                /*$("#ClaveFinan").keydown( function(e) {
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        dlgCveFin.dialog("open");
                    }
                });*/

                $("#mostrarClafinan").click( function(e) {
                    cveFinID= "ClaveFinan";
                    cveFinNom = "NomFin";
                    $("#ClaveFinan").val("");
                    $("#ClaveFinan").attr("ecf","");
                    e.preventDefault();
                    dlgCveFin.dialog("open");
                });

                $("#chkFed").change(function(e){
                    console.log("entro chkFed");
                    if( $("#chkFed").prop("checked") ) {
                        $("#checkFederal").prop("checked", true);
                    }else{
                        $("#checkFederal").prop("checked", false);
                    }
                    $("#ClaveFinan").attr("ecf","");
                    $("#ClaveFinan").val("");
                    });
                $("#chkEst").change(function(e){
                    if( $("#chkFed").prop("checked") ) {
                        $("#checkEstatal").prop("checked", true);
                    }else{
                        $("#checkEstatal").prop("checked", false);
                    }
                    $("#ClaveFinan").attr("ecf","");
                    $("#ClaveFinan").val("");
                    });

                // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
                $("#myModal42210").on("hide.bs.modal", function () {
                    console.log("borrado");
                    $("#paraModales").html("");
                    //$("#declaraRango42210").remove();
                });
            ');
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['10']['7']['1'])) {
    new Controlador42210();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
