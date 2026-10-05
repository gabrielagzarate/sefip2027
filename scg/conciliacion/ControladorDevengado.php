<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/conciliacion/conciliacion.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorDevengado extends WebResponse
{
    private $lectura;
    private $gpo_id;
    private $ur_id;
    private $nivel;
    private $ue_id;

    public function __construct()
    {
        parent::__construct();

        //Permisos de acceso a las tareas del controlador
        $this->lectura = isset($_SESSION[_S]['prm']['C']['7']['3']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }
        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
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
            <div>
                <a id="opciones" opcion="#myModalDevengado" data-placement="bottom" role="button" class="btn btn-outline-secondary me-2" data-toggle="modal"><i class="fa-solid fa-bars me-1"></i>Opciones</a>
                <input id="checkAcumulado" class = "me-1" name="checkAcumulado" type="checkbox" value=1>Acumulado</input>
            </div>
        ';

        $this->mixstyle('.F1Catalogo:focus { background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top lightgreen;}');
        $this->mixStyle('
            .form-group{margin-bottom:12px!important}
            legend{font-size:18px!important;margin-bottom:10px!important}
            .modal-header{padding:8px!important; background: #0074D9; color:white;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        ');
        // Dibujamos la barra de botones
        $this->agregaTools($botones);

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $contador = 0;
        $params = [$this->gpo_id,$this->ur_id];
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                    INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ?
                AND msc_id NOT IN ( 0, 13 )
        ');
        $this->consultar($params);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') {
                $inicio = 'class="selected"';
            } else {
                $inicio = '';
            }

            $active = ($contador == 0) ? 'active' : '';
            $selected = ($contador == 0) ? 'selected' : '';
            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                <li class="nav-item ' . $active . ' ' . $selected . '" opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <div></div><a role="tab" data-bs-toggle="tab" class="nav-link ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                </li>
            ';

            $contenedor .= '
                <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
            ';
            $contador++;
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
            <div id="page-tabs"><ul class="nav nav-tabs">
            ' . $pestana . '
            </ul></div><br>
        ');

        // Dibujamos el contenido
        $this->mixScript('
            // Para que el contenedor use todo el ancho de pantalla
            if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $(".meses").click( function(e){
                $( "li" ).removeClass("active");
                $(this).parent( "li" ).addClass("active");
            });

            $("#opciones").click( function(e){
                console.log( $("#cveFin_id").val() + "xx" );
                comunica("' . $this->pantalla . '", {
                    metodo: "filtros",
                    mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                    acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 )
                });
            });

            $("#checkAcumulado").change(function(e){
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                    acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 )
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
        $html = '<div id="myGridReporteDevengado" >
            <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                <table id="tabla-tbl-devengado" class="table table-striped table-bordered">
                    <div class="col-md-12 row" style="text-align:center">
                        <thead class="sticky-top">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader" style="width:90px !important;">Partida Genérica</th>
                                <th class="warning" role="columnheader">Nombre Partida</th>
                                <th class="warning" role="columnheader">Clave Financiamiento</th>
                                <th class="warning" role="columnheader">Nombre Clave</th>
                                <th class="warning" role="columnheader">Contabilidad</th>
                                <th class="warning" role="columnheader" style= "text-align:right">Presupuesto</th>
                                <th class="warning" role="columnheader" style= "text-align:right">Rectificado</th>
                                <th class="warning" role="columnheader" style= "text-align:right">Dif</th>
                            </tr>
                        </thead>
                    </div>
                    <tbody></tbody>
                </table>
            </div>
        </div>';
        $this->mixHtml($html);

        $this->mixScript('
            $("#myGridReporteDevengado").height($(myGridReporteDevengado).height() + 350);
            $("#tabla-tbl-devengado thead").css("z-index", "100");
            $("#myGridReporteDevengado").on("mouseenter", function(){
                $(this).css("overflow", "initial");
            });
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("selected"); });
                seleccionado.closest("li").addClass("selected");
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 )
                });
                e.stopPropagation();
                return false;
            });

            function loadData( json ){
                $("#tabla-tbl-devengado tbody").html("");
                const tbody = $("#tabla-tbl-devengado tbody");
                dataReporteDevengado = [];
                i = 0;
                for( var key in json ){
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                        style: "currency",
                        currency: "MXN",
                        currencyDisplay: "symbol"
                    });
                    var d = (dataReporteDevengado[i] = {});
                    d["id"] = json[key].key;
                    pg_id = json[key].pg_id;
                    pg_nombre = json[key].pg_nombre;
                    ecf_id = json[key].ecf_id;
                    cfi_clave = json[key].cfi_clave;
                    cfi_nombre = json[key].cfi_nombre;
                    pdx_importe = (json[key].pdx_importe == 0) ? "" : formatoMoneda.format(json[key].pdx_importe);
                    pre_importe = (json[key].pre_importe == 0) ? "" : formatoMoneda.format(json[key].pre_importe);
                    rec_importe = (json[key].rec_importe == 0) ? "" : formatoMoneda.format(json[key].rec_importe);
                    diferencia = (json[key].diferencia == 0) ? "" : formatoMoneda.format(json[key].diferencia);
                    tbody.append("<tr class=\'seleccionable\'><td>" + pg_id + "</td><td>" + pg_nombre + "</td><td>" + cfi_clave + "</td><td>" + cfi_nombre + "</td><td style=text-align:right>" + pdx_importe + "</td><td style=text-align:right>" + pre_importe + "</td><td style=text-align:right>" + rec_importe + "</td><td style=text-align:right>" + diferencia + "</td></tr>");
                    i++;
                }
                
            }

        ');
    }

    private function muestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
                loadData( ' . $data . ' );
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

        $datos = conciliacion($this->gpo_id, $this->ur_id, $this->ue_id, '', $periodo, $programatico, $claveFinanciamiento, $reporte, $detalle, 'D', $niveles);

        return json_encode($datos);
    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        $params = [$this->gpo_id,$this->ur_id,$this->prm->mes];
        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING( msc_id ) WHERE gpo_id = ? AND ur_id = ? AND msc_id NOT IN(0, 13) AND msc_id >= ?";
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->label = "hasta";
        $comboMesHasta->comoAtr = 'msc_nombre';
        $comboMesHasta->width = "150px";
        $comboMesHasta->params = $params;
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
        $cveFin->federal = ((isset($this->prm->federal) && $this->prm->federal == 1) ? true : false);
        $cveFin->estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1) ? true : false);
        $cveFin->tipoReporte = (isset($this->prm->tipoReporte) ? $this->prm->tipoReporte : 1);
        $cveFin->change = false;
        if ($this->prm->checkCF == 1) {
            $this->mixHtml(ClasificacionFinanciamiento::creaComboCveFin($cveFin));
        } else {
            $this->mixHtml('<div></div>');
        }
    }

    public function filtros()
    {
        $params = [$this->gpo_id,$this->ur_id];
        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable INNER JOIN cg_MesContable USING( msc_id ) WHERE gpo_id = ? AND msc_id NOT IN(0, 13) AND ur_id = ?";
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->params = $params;
        $comboMesDesde->selecciona = (($this->prm->acumulado == 1) ? 1 : $this->prm->mes);
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
            SELECT ecf_id, MAX(cfi_nombre) AS cfi_nombre, MAX(cfi_clave) AS cfi_clave
            FROM poa_OrigenRecurso
                INNER JOIN enc_ClaveFinanciamiento ecf USING( ecf_id )
                INNER JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO ." cf USING( cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
            WHERE gpo_id = ? AND ur_id = ?
            GROUP BY cff_id, fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio
        ";
        $comboClaveFinanciamiento->id = "cveFin_id";
        $comboClaveFinanciamiento->width = "150px";
        $comboClaveFinanciamiento->camposText = "cfi_clave,cfi_nombre";
        $comboClaveFinanciamiento->camposVal = "ecf_id";
        $comboClaveFinanciamiento->soloText = true;
        $comboClaveFinanciamiento->label = "Clave Financiamiento";
        $comboClaveFinanciamiento->params = $params;

        $ue = new stdClass();
        $ue->label = "";

        $html = '
            <div id="myModalDevengado" class="modal fade modal-al80porciento" data-keyboard="false" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color:#9d2449">
                            <h4 class="modal-title">Generar Reporte de Conciliación del Devengado(8.2.5.0.0)</h4>
                            <button id="cerrarModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    <div class="modal-body">
                        <form action="" class="" id="frmDevengado">
                            <div class="row">
                                <fieldset class="col-6" style="padding-right: 1px !important;">
                                    <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                </fieldset>
                                ' . (($_SESSION[_S]['nvj_id'] > 2) ? '<fieldset class="col-3" style="padding-right: 1px !important;">
                                    <legend>Tipo de Reporte</legend>
                                        <select name="tipoReporte" id="tipoReporte" class="form-select">
                                            <option value="1">Sólo la UR Actual</option>
                                            <option value="2">Todas las UR de la Carga de Trabajo</option>
                                        </select>
                                </fieldset>' : '') . '
                            </div><br />

                            <div id="periodo" style="display:inline">
                                <legend>Periodo</legend>
                                    <div class="row row-cols-2">
                                        <fieldset class=" col-3">
                                            ' . $this->ponCombo($comboMesDesde) . '
                                        </fieldset>
                                        <fieldset id="mhasta" class="col-3">
                                        </fieldset>
                                    </div>
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
                                    <input id="checkTR" name="checkTR" type="checkbox" value=1>&nbsp;TR</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkCF" name="checkCF" type="checkbox" value=1 checked >&nbsp;CDF</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>
                            </div><br />

                            <div class="row" id="EP" style="display:none">
                                <fieldset id="con_obraActividad" class="col-xs-4" style="padding-right: 1px !important;">
                                </fieldset>
                            </div><br />

                            <div class="row" id="TR" style="display:none">
                                <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                                    <legend>Tipo de Recurso</legend>
                                    <input id="checkFederal" name="checkFederal" type="checkbox" value=1>&nbsp;Federal</input>&nbsp;&nbsp;&nbsp;
                                    <input id="checkEstatal" name="checkEstatal" type="checkbox" value=1>&nbsp;Estatal</input>&nbsp;&nbsp;&nbsp;
                                </fieldset>
                            </div><br />

                            <div class="row row-cols-2" id="CF">
                                <fieldset id="con_cvefin" class="col-4" style="padding-right: 1px !important;">
                                ' . $this->ponCombo($comboClaveFinanciamiento) . '
                                </fieldset>
                            </div><br />

                            <div class="row">
                                <fieldset class="col-xs-6" style="padding-right: 1px !important;">
                                    <input id="mmc_id" name="mmc_id" value="D" style="visibility:hidden"></input>
                                </fieldset>
                            </div><br />

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button id="excel" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>Exportar</button>
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
            $("#myModalDevengado").modal("show");
            v = $("#titulo_reporte");
            v.append($("#btnimprime"))
                .append($("#btnexporta"))
                .append($("#btntabla"))
                .append($("<hr/>") );

            $("#tipoReporte").change(function(e){
                var tipoReporte;
                tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                console.log( tipoReporte );
                if( tipoReporte == 1 ){
                    if( $("#checkEP").is(":checked") ){
                        $("#EP").css("display", "inline" );
                    }else{
                        $("#EP").css("display", "none" );
                    }
                    if( $("#checkCF").is(":checked") ) {
                        $("#CF").css("display", "inline" );
                    }else{
                        $("#CF").css("display", "none" );
                    }
                    $("#comboue").removeAttr("disabled");
                }else{
                    $("#EP").css("display", "none" );
                    if( $("#checkCF").is(":checked") ) {
                        $("#CF").css("display", "inline" );
                    }else{
                        $("#CF").css("display", "none" );
                    }
                    $("#comboue").attr("disabled","disabled");
                }
            });

            $("#checkEP").change(function(e){
                var tipoReporte;
                tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                console.log( tipoReporte + "x" );
                if( $(this).is(":checked") && tipoReporte == 1 ){
                    $("#EP").css("display", "inline" );
                    comunica("' . $this->pantalla . '", {
                        metodo: "obraActividad",
                        mes: $("#page-tabs a").parent(".selected").children("a").attr("mes"),
                        acumulado: ( $("#checkAcumulado").prop("checked") ? 1 : 0 ),
                        checkEP: ( $("#checkEP").prop("checked") ? 1 : 0 ),
                        programatico: $("#comboprogramatico").val(),
                        federal: ( $("#checkFederal").prop("checked") ? 1 : 0 ),
                        estatal: ( $("#checkEstatal").prop("checked") ? 1 : 0 ),
                        checkCF: ( $("#checkCF").prop("checked") ? 1 : 0 ),
                        cveFin: $("#cveFin_id").val()
                    });
                }else{
                    $("#EP").css("display", "none" );
                }
            });

            $("#checkTR").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#TR").css("display", "inline" );
                }else{
                    $("#TR").css("display", "none" );
                }
            });

            $("#checkFederal").change(function(e){
                var tipoReporte;
                tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                console.log( tipoReporte );
                if( $("#checkCF").is(":checked") ) {
                    $("#CF").css("display", "inline" );
                }else{
                    $("#CF").css("display", "none" );
                }
            });

            $("#checkEstatal").change(function(e){
                var tipoReporte;
                tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                console.log( tipoReporte );
                if( $("#checkCF").is(":checked") ) {
                    $("#CF").css("display", "inline" );
                }else{
                    $("#CF").css("display", "none" );
                }
            });

            $("#checkCF").change(function(e){
                var tipoReporte;
                tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                console.log( tipoReporte );
                if( $(this).is(":checked") ) {
                    $("#CF").css("display", "inline" );
                    var tipoReporte;
                    tipoReporte = ( typeof( $("#tipoReporte").val() ) == "undefined" ? 1:( $("#tipoReporte").val() ) );
                    console.log( tipoReporte );
                }else{
                    $("#CF").css("display", "none" );
                }
            });

            $("#excel").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/conciliacion/exp_Conciliacion.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "7" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

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

                    .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", $("#mmc_id").val() ))

                    .append($("<input>").attr("type", "hidden").attr("name", "programatico").attr("value", $("#comboprogramatico").val() ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkFederal" ).attr("value", ( $("#checkFederal").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "checkEstatal" ).attr("value", ( $("#checkEstatal").prop("checked" ) ? 1 : 0 ) ))
                    .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin_id").val() ))

                $("body").append(form);
                form.submit();
            });

            $("#imprimir").click(function(){
                $("#frmDevengado").attr("action","rep/doc/scg/conciliacion/rep_Conciliacion.php").submit();
            });

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalDevengado").on("hide.bs.modal", function () {
                console.log("borrado");
                $("#paraModales").html("");
                comunica("' . $this->pantalla . '");
            });
        ');
    }
}

//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['7']['3']['1'])) {
    new ControladorDevengado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
