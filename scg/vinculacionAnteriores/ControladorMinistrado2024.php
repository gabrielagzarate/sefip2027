<?php
//librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/getDocumento.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/pintaClc.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaMinistrado.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/2024/vinculacionMinistrado2024.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2024/sp_MinistradoAutorizado2024.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/2024/sp_MinistradoCancelado2024.php';

class ControladorMinistrado2024 extends WebResponse
{
    public $escritura;
    public $gpo_id;
    public $ur_id;
    public $ue_id;
    public $gpo_id_2024;
    public $ur_id_2024;
    public $usr_ip;
    public $usr_id;
    public $origen;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['17']['14'][TAREA_ESCRITURA]) ? true : false;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = false;
        }

        $sql = '
            SELECT
                gpo_id_2024 AS gpo_id, ur_id_2024 AS ur_id
                FROM ' . BD_SINPRES . '.vin_UnidadResponsable_2024_2025
                WHERE gpo_id_2025 = ? AND ur_id_2025 = ?
                LIMIT 1;
        ';
        $params = [$this->gpo_id, $this->ur_id];
        $conexion = new AccesoDatos();
        $conexion->setSql($sql);
        $conexion->conectar();
        $conexion->consultar($params);
        $data = $conexion->extraer();
        $conexion->desconectar();
        $this->gpo_id_2024 = $data['gpo_id'];
        $this->ur_id_2024 = $data['ur_id'];
        unset($conexion);

        $this->usr_ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip']
            != "::1") ? $_SESSION[_S]['ses_ip'] : 'ERROR';
        $this->usr_id = $_SESSION[_S]['usr_id'];

        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion') {
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");
        }

        switch ($this->metodo) {
            case 'DibujaPantalla':$this->DibujaPantalla();
                break;
            case 'MuestraDatos':$this->MuestraDatos();
                break;
            case 'Documento':$this->Documento();
                break;
            case 'GeneraPoliza':$this->GeneraPoliza();
                break;
            case 'PonBarraBotones':$this->PonBarraBotones();
                break;
            default:
                opcionConteo($_POST['opcion']['sis_id'],
                    $_POST['opcion']['men_id'], $_POST['opcion']['opc_id']);
                if ($this->origen == 'clc') {
                    $this->PonBarraBotones();
                } else {
                    if ($this->gpo_id . $this->ur_id == 801) {
                        $this->main();
                    } else {
                        $this->PonBarraBotones();
                        $this->prm->seleccion = "clc";
                    }
                }
                break;
        }

        $this->returnJson();
    }

    private function main()
    {
        $html = '
            <center>
                    <div  id="vinculacionClc" style="" class="boton_opcion">
                            <div class="boton_opcion_titulo">VINCULACIÓN <br> POR CLC</div>
                            <div class="price">
                                <div class="center validafoliour">
                                    <img src="img/VinculacionClc.png">
                                </div>
                            </div>
                    </div>

                    <!--
                    <div style="display:table-cell; width:5px;"></div>

                    <div id="vinculacionFederal" class="boton_opcion">
                        <div class="boton_opcion_titulo">RECURSO FEDERAL</div>
                        <div class="price">
                            <div class="center validafoliour">
                                <img src="img/VinculacionClcRF.png">
                            </div>
                        </div>
                    </div>

                    <div style="display:table-cell; width:5px;"></div>

                    <div id="vinculacionPlaneacion" class="boton_opcion">
                        <div class="boton_opcion_titulo">VINCULACION<br/>PLANEACION</div>
                        <div class="price">
                            <div class="center validafoliour">
                                <img src="img/VinculacionClcPL.png">
                            </div>
                        </div>
                    </div>

                    <div style="display:table-cell; width:5px;"></div>

                    -->
                    ' . ($this->gpo_id . $this->ur_id == 801 ? '
                    <div id="vinculacionPensiones" class="boton_opcion">
                        <div class="boton_opcion_titulo">PENSIONES</div>
                        <div class="price">
                            <div class="center validafoliour">
                                <img src="img/VinculacionClcPensiones.png">
                            </div>
                        </div>
                    </div>
                    ' : '') . '

            </center>
            ';

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->class = "me-2";
        $btnRegresa->color = "gris";
        $btnRegresa->icon = "fa-solid fa-circle-lef me-1";

        $this->mixHtml($html);
        $this->mixScript('
                $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
                $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
                //ES DE LA TABLA tes_BancoDocumentoMinistrado
                $("#vinculacionClc").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "PonBarraBotones",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "clc"
                    });
                });

                //ES DE LA TABLA tes_MinistradoPlaneacion
                //mip_tipoRecurso = 1
                $("#vinculacionFederal").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "tes",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "federal"
                    });
                });

                $("#vinculacionPlaneacion").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "tes",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "planeacion"
                    });
                });
                ' . ($this->gpo_id . $this->ur_id == 801 ? '
                $("#vinculacionPensiones").click(function(){
                    comunica("' . $this->pantalla . '",{
                        metodo: "PonBarraBotones",
                        origen: "' . $this->origen . '",
                        mes: "' . $this->prm->mes . '",
                        seleccion: "pensiones"
                    });
                });
                ' : '') . '
                $("#btnRegresa").click(function(){
                    $("nav").menusinpres("clickMenu");
                });
            ');
    }

    private function PonBarraBotones()
    {
        if (isset($_SESSION[_S]['s24-nvj']) && $_SESSION[_S]['s24-nvj'] < 3) {
            $this->mixScript('$("#toggleCA").hide();'); // ocultamos la unidad responsable si el nivel de usuario es menor a 3
        }
        // Botones y opciones que se dibujarán en la barra de botones
        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->class = "btn btn-outline-success";

        $clcFiltro = false;
        $bdoFiltro = false;

        switch ($this->origen) {
            case "clc":
                $clcFiltro = true;
                break;
            case "bdo":
                $bdoFiltro = true;
                break;
            default:
                if (isset($this->prm->clcFiltro) && $this->prm->clcFiltro == 1) {
                    $clcFiltro = true;
                }
                if (isset($this->prm->bdoFiltro) && $this->prm->bdoFiltro == 1) {
                    $bdoFiltro = true;
                }
                break;
        }

        $botones = '
                <div class="row">
                    ' . (($this->gpo_id . $this->ur_id == 801 && isset($this->prm->seleccion)
            && $this->prm->seleccion == 'pensiones') ? '<fieldset class="col-md-1" style="padding-right: 1px !important;">
                        <input id="inputGur" placeHolder="UR" type="text" maxlength=3 class="form-control" value="' . (isset($this->prm->gur)
            ? $this->prm->gur : "") . '"/>
                    </fieldset>' : '') . '
                    <fieldset class="col-md-1" style="padding-right: 1px !important;">
                        <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control" value="' . ($clcFiltro
            ? $this->prm->clc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_id" placeHolder="# Documento" maxlength=7 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputRfc_id" placeHolder="RFC" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->rfc_id : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                        <input id="inputBdo_referencia" placeHolder="Referencia" maxlength=20 class="form-control" value="' . ($bdoFiltro
            ? $this->prm->bdo_referencia : "") . '"/>
                    </fieldset>
                    <fieldset class="col-md-2" style="padding-right: 1px !important;">
                    <!--  <button id="excelMinistrado2024" class="btn btn-success ladda-button" data-style="slide-left"><i class="glyphicon glyphicon-download-alt"></i></button> -->
                    </fieldset>
                </div>
            ';

        $this->agregaTools($botones);
        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('
            SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
            INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ?
            AND msc_id NOT IN ( 0, 13 )
        ');
        $params = [$this->gpo_id, $this->ur_id];
        $this->consultar($params);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) {
                $inicio = 'class="active"';
                $active = 'active';
            } else {
                $inicio = '';
                $active = '';
            }

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                        <div></div><a class="nav-link ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
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

        // Dibujamos el contenido
        switch ($this->origen) {
            case 'opcion':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
            case 'clc':
                $this->mixScript('
                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                ');
                break;
        }

        $this->mixScript('
                $("#opciones").click( function(e){
                    comunica("' . $this->pantalla . '", {
                        metodo: "Filtro",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });
                });

                $("#btnLimpiar").click( function(e){
                    $("#inputGur").val("");
                    $("#inputClc_id").val("");
                    $("#inputBdo_id").val("");
                    $("#inputRfc_id").val("");
                    $("#inputBdo_referencia").val("");

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });

                $("#excelMinistrado2024").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/vinculacion2024/exp_Ministrado2024.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T"))
                        .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                    $("body").append(form);
                    form.submit();
                });

                $("#imprimirMinistrado2024").click( function(e) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/scg/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "6" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "T" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "seleccion").attr("value", "' . (isset($this->prm->seleccion)
            ? $this->prm->seleccion : "") . '" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", ' . $this->gpo_id_2024 . $this->ur_id_2024 . ' ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_id").attr("value", $("#inputBdo_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputRfc_id").attr("value", $("#inputRfc_id").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo_referencia").attr("value", $("#inputBdo_referencia").val() ))

                        $("body").append(form);
                        form.submit();
                });

                // Programamos una combinación de ESC ESC para disparar el botón REGRESAR
                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#btnHomeMenu").click();
                    e.stopPropagation();
                });

            ');

        $this->DibujaPantalla();
    }

    private function DibujaPantalla()
    {
        $html = '<div id="myGridMinistrado2024" ></div>';
        $this->mixHtml($html);

        $this->mixScript('
                $("#page-tabs a").click( function(e){
                    e.preventDefault();
                    var seleccionado = $(this);
                    $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("li").addClass("active");
                    $("#page-tabs a").each(function (index) { $(this).removeClass("active"); });
                    seleccionado.closest("a").addClass("active");

                    comunica("' . $this->pantalla . '",{
                        metodo: "MuestraDatos",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        gur: $("#inputGur").val(),
                        clc_id: $("#inputClc_id").val(),
                        bdo_id: $("#inputBdo_id").val(),
                        rfc_id: $("#inputRfc_id").val(),
                        bdo_referencia: $("#inputBdo_referencia").val()
                    });

                    e.stopPropagation();
                    return false;
                });
            ');

        $this->mixScript('
                //$("#tituloPantalla").hide();
                // Para que el GRID se ajuste al espacio disponible en pantalla
                $("#myGridMinistrado2024").height(
                    $(document).height() -
                    $("header").outerHeight() -
                    $("#sticky").outerHeight() -
                    $("footer").outerHeight() - 315
                );

                var sortcol = "CLAVE";
                var sortdir = 1;
                var searchString = "";

                function myFilter(item, args){
                    if( item["d"] === null || item["c"] === null ) { return false }
                    // Si empieza con numero ... buscamos en la cuenta
                    strNumeros = "0123456789";
                    if( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ){
                        return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                    // si no ... buscamos en el texto
                    }else{
                        if( args.searchString != "" && item["d"].toLowerCase().indexOf(args.searchString) == -1 ){
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
                    return ( dataMinistrado2024[row].epl == "C" || dataMinistrado2024[row].epl == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
                }

                function FormatoMC(row, cell, value, columnDef, dataContext){
                    if( value.substring( 1 ) == "A" ){
                        return value.substring( 0, 1 );
                    }else{
                        return "<span title=\"La póliza corresponde a la cancelación de la ministración que fué autorizada en diferente mes\">" + value.substring( 0, 1 ) + "<span style=\"color:red\">" + value.substring( 1 ) + "</span></span>";
                    }
                }

                function updateFilter(){
                    dataViewMinistrado2024.setFilterArgs({ searchString: searchString });
                    dataViewMinistrado2024.refresh();
                }

                function FormatoMoneda(row, cell, value, columnDef, dataContext){
                    // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
                    strIni = ""
                    strFin = ""

                    if( value === null || value === "" || !(value != 0) ){
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
                function loadData( json ){
                    dataMinistrado2024 = [];

                    i = 0;
                    for( var key in json ){
                        //console.log( key + "->" + json[key]["id"] );
                        var d = (dataMinistrado2024[i] = {});
                        d["id"] = json[key].id;
                        d["gur"] = json[key].gpo_id + json[key].ur_id;
                        d["afe_id"] = json[key].afe_id;
                        d["bdo_id"] = json[key].bdo_id;
                        d["tdf_id"] = json[key].tdf_id;
                        d["rfc_id"] = json[key].rfc_id;
                        d["bdo_referencia"] = json[key].bdo_referencia;
                        d["bdo_concepto"] = json[key].bdo_concepto;
                        d["tdf_id_ministrado"] = json[key].tdf_id_ministrado;
                        d["bdm_referencia"] = json[key].bdm_referencia;
                        d["importe"] = json[key].bdo_importe;
                        d["estado"] = json[key].estado;
                        d["fechaAutorizacion"] = json[key].fechaAutorizacion;
                        d["fechaCancelacion"] = json[key].fechaCancelacion;
                        d["polizaAutorizado"] = json[key].polizaAutorizado;
                        d["fechaAutorizado"] = json[key].fechaAutorizado;
                        d["polizaCancelado"] = json[key].polizaCancelado;
                        d["fechaCancelado"] = json[key].fechaCancelado;
                        d["agrupador"] = json[key].agrupador;
                        i++;
                    }

                    //console.table( dataMinistrado2024 );

                    dataViewMinistrado2024.setItems(dataMinistrado2024);

                }

                var dataViewMinistrado2024;
                var dataGridMinistrado2024;
                var columns = [
                    //{id: "gur", name: "UR", field: "gur", minWidth: 25, sortable: true },
                    {id: "afe", name: "AFE", field: "afe_id", minWidth: 40, maxWidth: 60, sortable: true },
                    {id: "bdo", name: "DOC", field: "bdo_id", minWidth: 60, maxWidth: 60, sortable: true },
                    {id: "tdf", name: "TDF", field: "tdf_id", sortable: true },
                    {id: "rfc", name: "RFC", field: "rfc_id", minWidth: 100, maxWidth: 120, sortable: true },
                    {id: "referencia", name: "REFERENCIA", field: "bdo_referencia", minWidth: 50, sortable: true },
                    {id: "concepto", name: "CONCEPTO", field: "bdo_concepto", sortable: true },
                    {id: "tdf_ministrado", name: "TDF", field: "tdf_id_ministrado", minWidth: 50, sortable: true },
                    {id: "referenciaMinistrado2016", name: "REFERENCIA MINISTRADO", field: "bdm_referencia", minWidth: 100, maxWidth: 150, sortable: true },
                    {id: "importe", name: "IMPORTE", field: "importe", minWidth: 80, maxWidth: 100, sortable: true, headerCssClass: "alineaDerecha", cssClass: "alineaDerecha", formatter: FormatoMoneda },
                    {id: "estado", name: "ESTADO", field: "estado", minWidth: 40, maxWidth: 60, sortable: true },
                    {id: "fechaAutorizacion", name: "AUTORIZACIÓN", field: "fechaAutorizacion", minWidth: 70, maxWidth: 100, sortable: true },
                    {id: "fechaCancelacion", name: "CANCELACIÓN", field: "fechaCancelacion", minWidth: 70, maxWidth: 100, sortable: true },
                    {id: "polizaAutorizado", name: "POL AUT.", field: "polizaAutorizado", minWidth: 70, maxWidth: 70, sortable: true },
                    {id: "fechaAutorizado", name: "FECHA", field: "fechaAutorizado", minWidth: 70, maxWidth: 80, sortable: true },
                    {id: "polizaCancelado", name: "POL CAN.", field: "polizaCancelado", minWidth: 70, maxWidth: 70, sortable: true },
                    {id: "fechaCancelado", name: "FECHA", field: "fechaCancelado", minWidth: 70, maxWidth: 80, sortable: true }
                ];

                var options = {
                    enableCellNavigation: true,
                    enableColumnReorder: false,
                    forceFitColumns: true
                    /*autoHeight:true
                    esto y la funcion $("#myGridMinistrado2024").height() arriba controlan si el grid se limita o no a la pantalla
                    */
                };

                dataViewMinistrado2024 = new Slick.Data.DataView( { inlineFilters: false });
                dataGridMinistrado2024 = new Slick.Grid("#myGridMinistrado2024", dataViewMinistrado2024, columns, options);

                dataGridMinistrado2024.setSelectionModel(new Slick.RowSelectionModel());

                dataViewMinistrado2024.onRowCountChanged.subscribe(function( e, args ){
                    dataGridMinistrado2024.updateRowCount();
                    dataGridMinistrado2024.render();
                });

                dataViewMinistrado2024.onRowsChanged.subscribe(function( e, args ){
                    dataGridMinistrado2024.invalidateRows(args.rows);
                    dataGridMinistrado2024.render();
                });

                dataGridMinistrado2024.onSort.subscribe(function( e, args ){
                    sortdir = args.sortAsc ? 1 : -1;
                    sortcol = args.sortCol.field;
                    dataViewMinistrado2024.sort(comparer, args.sortAsc);
                });

                // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
                $("#myModalMinistrado2024").on("hide.bs.modal", function (){
                    $("#declaraRangoMinistrado2024").remove();
                });

                dataGridMinistrado2024.onDblClick.subscribe(function(e){
                    // Obtenemos el array de los datos de la linea actual
                    row = dataGridMinistrado2024.getDataItem(dataGridMinistrado2024.getActiveCell().row);

                    // Si no hay nada que selecionar, no hacemos nada
                    if( dataGridMinistrado2024.getSelectedRows() == "" ){
                        e.stopPropagation();
                        return false;
                    }

                    row = dataGridMinistrado2024.getDataItem(dataGridMinistrado2024.getActiveCell().row);
                    console.log( row.gur + "-" + row.afe_id + "-" + row.bdo_id );
                    var var_clcFiltro = 1;
                    var var_bdoFiltro = 1;
                    if( $("#inputClc_id").val() == "" ){
                        var_clcFiltro = 2;
                    }
                    if( $("#inputBdo_id").val() == "" && $("#inputRfc_id").val() == "" && $("#inputBdo_referencia").val() == "" ){
                        var_bdoFiltro = 2;
                    }

                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs a").parent(".active").children("a").attr("mes"),
                        clc_id: $("#inputClc_id").val(),
                        clcFiltro: var_clcFiltro,
                        gur: row.gur,
                        afe_id: row.afe_id,
                        bdo_id: row.bdo_id,
                        tdf_id: row.tdf_id,
                        rfc_id: row.rfc_id,
                        bdo_referencia: row.bdo_referencia,
                        bdoFiltro: var_bdoFiltro,
                        agrupador: row.agrupador
                    });

                    e.stopPropagation();
                });

            ');

        if ($this->origen == 'clc') {
            $this->mixScript('
                    $("#page-tabs").hide();
                    $("#inputBdo_id").hide();
                    $("#inputRfc_id").hide();
                    $("#inputBdo_referencia").hide();
                ');
        }
    }

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
                dataViewMinistrado2024.beginUpdate();
                dataViewMinistrado2024.getItems().length = 0
                dataGridMinistrado2024.invalidateAllRows();
                loadData( ' . $data . ' );
                dataViewMinistrado2024.setFilterArgs({ searchString: searchString });
                dataViewMinistrado2024.setFilter(myFilter);
                dataViewMinistrado2024.endUpdate();
                dataGridMinistrado2024.render();
            ');

        return false;
    }

    private function DameInformacion()
    {
        $pensiones = (isset($this->prm->seleccion) && $this->prm->seleccion == 'pensiones' ? true : false);
        $gur = ($pensiones ? (isset($this->prm->gur) ? $this->prm->gur : '') : $this->gpo_id_2024 . $this->ur_id_2024);
        $clc_id = $this->prm->clc_id; //Informacion de acuerdo al mes
        $bdo_id = $this->prm->bdo_id; //Informacion de acuerdo al mes
        $rfc_id = $this->prm->rfc_id; //Informacion de acuerdo al mes
        $bdo_referencia = $this->prm->bdo_referencia; //Informacion de acuerdo al mes
        $periodo['mesDesde'] = $this->prm->mes; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $this->prm->mes; //Informacion de acuerdo al mes

        $reporte['tipo'] = 1;
        $programatico['epg_id'] = '';
        $ClaveFinanciamiento['ecf_id'] = '';
        $ClaveFinanciamiento['ter_id'] = '';

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

        $datos = vinculacionMinistrado2024($this->gpo_id, $this->ur_id,
            $this->ue_id, '', $periodo, $programatico, $ClaveFinanciamiento,
            $reporte, $detalle, 'T', $niveles,
            array("gur" => $gur, "clc_id" => $clc_id, "bdo_id" => $bdo_id, "rfc_id" => $rfc_id,
                "bdo_referencia" => $bdo_referencia, "pensiones" => $pensiones));

        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = $this->gpo_id AND ur_id = $this->ur_id ");
        $conexion->consultar();

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        $clc_id_documento = (isset($this->prm->clc_id) ? $this->prm->clc_id : '');
        $bdo_id_documento = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');

        $clc_array = array();

        $generaPolizaAutorizado = false;
        $generaPolizaCancelado = false;

        $fechaAutorizado = '';
        $fechaCancelado = '';

        $estadoMesCancelado = false;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresa->color = 'gris';

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $documento = getDocumento($gpo_id_documento, $ur_id_documento,
            $bdo_id_documento, $this->prm->rfc_id, $this->prm->bdo_referencia,
            array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "tdf_id" => $this->prm->tdf_id, "mmc_id" => "T"));
        $html = '<legend>Análisis del documento 2024 a generar la póliza del ministrado</legend>';
        $html .= '
                <div>
                    <fieldset class="col-md-12 col-xs-12">
                        <legend>Información del Documento</legend>
                        <table class="table table-bordered">
                            <tr class="success">
                                <th>Proveedor</th>
                                <th>Referencia</th>
                                <th>Concepto</th>
                                <th>Percepción Presupuestal</th>
                                <th>Percepción Contable</th>
                                <th>Retención Contable</th>
                                <th>Fecha</th>
                            </tr>
                            <tr>
                                <td>' . $documento->rfc_id . "-" . $documento->nombreProveedor . '</td>
                                <td>' . $documento->tipoDocumentoFuente . "-" . $documento->referencia . '</td>
                                <td>' . $documento->concepto . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionPresupuestal, 2) . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->percepcionContable, 2) . '</td>
                                <td width="10%" align="right"><span style="float:left">$</span>' . number_format($documento->retencionContable, 2) . '</td>
                                <td>' . $documento->fechaDocumento . '</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';
        $tipoministrado = 2;
        //preguntamos el estado de los meses contables en funcion de las fechas de la ministracion
        if(!empty($documento->detalleMinistrado2024)){
            foreach ($documento->detalleMinistrado2024 as $key => $value) {
                if ($clc_id_documento != '') {
                    if ($clc_id_documento == $value->clc_id) {
                        $clc_array[$value->clc_id] = $value->clc_id;
                    }
                } else {
                    $clc_array[$value->clc_id] = $value->clc_id;
                }

                if ($this->prm->agrupador == $value->agrupador) {
                    $tipoministrado = $value->tipoMinistracion;
                    if ($value->tipoMinistracion == 1) {
                        $generaPolizaAutorizado = true;
                        $fechaAutorizado = $value->fechaAutorizado;
                    }
                    $estadoMesAutorizado = $mesContable[str_pad($value->mesAutorizado, 2, '0', STR_PAD_LEFT)]['ems_id'];
                    if ($value->estado == 'C') {
                        if ($generaPolizaAutorizado) {
                            $generaPolizaCancelado = true;
                            $fechaCancelado = $value->fechaCancelado;
                        }
                        $estadoMesCancelado = $mesContable[str_pad($value->mesCancelado,
                            2, '0', STR_PAD_LEFT)]['ems_id'];
                    }
                }
            } //*/

            /* INFORMACIÓN DE LA CLC EN LA QUE ESTA EL DOCUMENTO */
            if (count($clc_array) != 0) {
                $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>CLC en la que esta el documento
                            <span id="menclc" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                    ';
                foreach ($clc_array as $clc_id) {
                    $clcDocumento = pintaClc($gpo_id_documento, $ur_id_documento,
                        $clc_id,
                        array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "bdo_id" => $bdo_id_documento, "todo" => true));
                    $html .= $clcDocumento->html;
                }
                $html .= '
                        </fieldset></br>
                    ';
            }

            /* DATOS DE LA MINISTRACIÓN */
            $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Datos de la Ministración
                            <span id="ministrado" style="font-size:10px; color:rgba(0,0,0,.6)"></span>
                        </legend>
                ';
            $ministrado = pintaMinistrado($gpo_id_documento, $ur_id_documento,
                $bdo_id_documento,
                array("ejercicio" => intval(APP_EJERCICIO_ANTERIOR), "clc_id" => $clc_id_documento));
            $html .= $ministrado->html;
            $html .= '
                    </fieldset></br>
                ';
            if ($tipoministrado == 1) {
                /*             * *********************************** Información de la Póliza Contable ************************************************************** */
                $polizas = getPolizaAgrupador($this->gpo_id, $this->ur_id,
                    $this->prm->agrupador, '', intval(APP_EJERCICIO_ANTERIOR));

                $polizaAut = new stdClass;
                $botonGeneraPa = '';

                //preguntamos los datos de la póliza contable de autorización
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'A') {
                            $polizaAutorizado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
                            $polizaAut->valido = $polizaAutorizado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaAut->valido)) {
                    $polizaAut->valido = false;
                }
                //si existe una póliza
                if (isset($polizaAutorizado)) {
                    foreach ($polizaAutorizado as $key => $value) {
                        if ($value->valido) {
                            $polizaAutorizadoHtml = pintaPoliza($polizaAutorizado[$key]);
                            $html .= $polizaAutorizadoHtml->html;
                            //si el mes NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesAutorizado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaA';
                                    $btnGenera->name = 'btnReGeneraPolizaA';
                                    $btnGenera->text = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->value = 'Re-Genera Póliza de Autorización';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPa = $this->ponBoton($btnGenera);

                                    $this->mixScript('
                                        $("#btnReGeneraPolizaA").click(function(){
                                            alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '", "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                                function(){
                                                    comunica("' . $this->pantalla . '",{
                                                        metodo: "GeneraPoliza",
                                                        origen: "' . $this->origen . '",
                                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                        mes: ' . $this->prm->mes . ',
                                                        gur: ' . $this->prm->gur . ',
                                                        clc_id: "' . $this->prm->clc_id . '",
                                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                                        bdo_id: ' . $this->prm->bdo_id . ',
                                                        tdf_id: "' . $this->prm->tdf_id . '",
                                                        rfc_id: "' . $this->prm->rfc_id . '",
                                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                        gpo_id: "' . $value->gpo_id . '",
                                                        ur_id: "' . $value->ur_id . '",
                                                        ue_id: "' . $value->ue_id . '",
                                                        fecha: "' . $fechaAutorizado . '",
                                                        agrupador: "' . $this->prm->agrupador . '",
                                                        tmc_id: "A"
                                                    });
                                                },
                                                function(){},
                                            ).set("defaultFocus","cancel").set("labels", {ok:"Proceder", cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                                }
                            } elseif ($estadoMesAutorizado == 'A') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesAutorizado == 'C') {
                                $botonGeneraPa = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                            }
                        }
                    }
                }

                $polizaCan = new stdClass;
                $botonGeneraPc = '';

                //preguntamos los datos de la póliza contable de cancelación
                if ($polizas->valido) {
                    foreach ($polizas->polizas as $key => $value) {
                        if ($value->tipoMomento == 'C') {
                            $polizaCancelado[$key] = getPoliza($value->gpo_id,
                                $value->ur_id, $value->ue_id, $value->msc_id,
                                $value->pot_id, $value->prf_id, $value->pma_id);
                            $polizaCan->valido = $polizaCancelado[$key]->valido;
                        }
                    }
                }
                if (!isset($polizaCan->valido)) {
                    $polizaCan->valido = false;
                }

                //si existe tal póliza
                if (isset($polizaCancelado)) {
                    foreach ($polizaCancelado as $key => $value) {
                        if ($value->valido) {
                            $polizaCanceladoHtml = pintaPoliza($polizaCancelado[$key]);
                            $html .= $polizaCanceladoHtml->html;
                            //si el mes cpntable NO esta cerrado
                            if ($this->escritura) {
                                if ($estadoMesCancelado == 'A') {
                                    //podra regenerar la póliza contable
                                    $btnGenera = new stdClass();
                                    $btnGenera->id = 'btnReGeneraPolizaC';
                                    $btnGenera->name = 'btnReGeneraPolizaC';
                                    $btnGenera->text = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->value = 'Re-Genera Póliza de Cancelación';
                                    $btnGenera->color = 'verde';
                                    $btnGenera->icon = 'tasks';
                                    $botonGeneraPc = $this->ponBoton($btnGenera);

                                    $this->mixScript('
                                        $("#btnReGeneraPolizaC").click(function(){
                                        alertify.confirm("RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '","Esta acción re construira la póliza de cancelación como fue generada por el sistema de manera automatica, desea proceder?<h5 style=\"display:inline\"></h5> ",
                                            function(){
                                                comunica("' . $this->pantalla . '",{
                                                    metodo: "GeneraPoliza",
                                                    origen: "' . $this->origen . '",
                                                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                    mes: ' . $this->prm->mes . ',
                                                    gur: ' . $this->prm->gur . ',
                                                    clc_id: "' . $this->prm->clc_id . '",
                                                    clcFiltro: ' . $this->prm->clcFiltro . ',
                                                    bdo_id: ' . $this->prm->bdo_id . ',
                                                    tdf_id: "' . $this->prm->tdf_id . '",
                                                    rfc_id: "' . $this->prm->rfc_id . '",
                                                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                                    gpo_id: "' . $value->gpo_id . '",
                                                    ur_id: "' . $value->ur_id . '",
                                                    ue_id: "' . $value->ue_id . '",
                                                    fecha: "' . $fechaCancelado . '",
                                                    agrupador: "' . $this->prm->agrupador . '",
                                                    tmc_id: "C"
                                                });
                                            },
                                            function(){},
                                        ).set("defaultFocus","cancel").set("labels", {ok:"Proceder", cancel:"Cancelar"});
                                        });
                                    ');
                                } else {
                                    $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado </div>';
                                }
                            } elseif ($estadoMesCancelado == 'A') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">Regenerar Póliza</div>';
                            } elseif ($estadoMesCancelado == 'C') {
                                $botonGeneraPc = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                            }
                        }
                    }
                }

                $botones = '';

                if ($this->escritura) {
                    //si deberia generar la póliza y NO existe
                    if ($generaPolizaAutorizado && !$polizaAut->valido) {
                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaA';
                        $btnGeneraPoliza->text = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->value = 'Genera Póliza de Autorización';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);
                        //si el mes contable se encuentra abierto
                        if ($estadoMesAutorizado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        fecha: "' . $fechaAutorizado . '",
                                        agrupador: "' . $this->prm->agrupador . '",
                                        tmc_id: "A"
                                    });
                                });
                            ');
                        } else {
                            //en caso contrario, debera pedir la fecha donde debe generarse
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Autorización';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';

                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = true;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = true;

                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes del Ministrado2024 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                            ' . $this->ponInput($inputFecha) . '
                                        </center>
                                        <hr>
                                            ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                            ' . $this->ponBoton($btnCancelar) . '
                                    </div>
                                </div>
                            ';

                            $this->mixScript('
                                $("#btnGeneraPolizaA").click(function(){
                                    if($("#bg_capfecha").length != 0 ){
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });

                                $("#btnGeneraPolizaCerrado").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "A"
                                    });
                                });

                                $("#btnCancelar").click(function(){
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    } //*/
                } elseif ($estadoMesAutorizado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesAutorizado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
                }

                if ($this->escritura) {
                    if ($generaPolizaAutorizado && $polizaAut->valido && $generaPolizaCancelado && !$polizaCan->valido) {

                        $btnGeneraPoliza = new stdClass();
                        $btnGeneraPoliza->id = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->name = 'btnGeneraPolizaC';
                        $btnGeneraPoliza->text = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->value = 'Genera Póliza de Cancelación';
                        $btnGeneraPoliza->color = 'verde';
                        $btnGeneraPoliza->icon = 'ok';

                        $botones = $this->ponBoton($btnGeneraPoliza);

                        if ($estadoMesCancelado == 'A') {
                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: "' . $fechaCancelado . '",
                                        tmc_id: "C"
                                    });
                                });
                            ');
                        } else {
                            $btnGeneraPolizaCerrado = new stdClass();
                            $btnGeneraPolizaCerrado->id = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->name = 'btnGeneraPolizaCerrado';
                            $btnGeneraPolizaCerrado->text = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->value = 'Genera Póliza de Cancelación';
                            $btnGeneraPolizaCerrado->color = 'verde';
                            $btnGeneraPolizaCerrado->icon = 'ok';

                            $inputFecha = new stdClass();
                            $inputFecha->id = "cap_fecha";
                            $inputFecha->name = "control";
                            $inputFecha->class = 'fecha';
                            $inputFecha->label = '';
                            $inputFecha->enDiv = true;
                            $inputFecha->formato = 'fecha';
                            $inputFecha->labelClass = '_32 txtL';
                            $inputFecha->required = true;

                            $btnCancelar = new stdClass();
                            $btnCancelar->id = 'btnCancelar';
                            $btnCancelar->icon = 'block';
                            $btnCancelar->name = 'nuevo';
                            $btnCancelar->color = 'rojo';
                            $btnCancelar->text = 'Cancelar';
                            $btnCancelar->value = 'Cancelar';

                            $html .= '
                                <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                    <div id="capfecha">
                                        <legend>Mes de cancelación del Ministrado2024 esta cerrado, capture una nueva fecha</legend>
                                        <center>
                                        ' . $this->ponInput($inputFecha) . '
                                        </center>
                                        <hr>
                                        ' . $this->ponBoton($btnGeneraPolizaCerrado) . '
                                        ' . $this->ponBoton($btnCancelar) . '
                                    </div>
                                </div>
                            ';

                            $this->mixScript('
                                $("#btnGeneraPolizaC").click(function(){
                                    if($("#bg_capfecha").length != 0 ){
                                        $(this).hide();
                                        $("#bg_capfecha").show();
                                    }
                                });

                                $("#btnGeneraPolizaCerrado").click(function(){
                                    comunica("' . $this->pantalla . '",{
                                        metodo: "GeneraPoliza",
                                        origen: "' . $this->origen . '",
                                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                        mes: ' . $this->prm->mes . ',
                                        gur: ' . $this->prm->gur . ',
                                        clc_id: "' . $this->prm->clc_id . '",
                                        clcFiltro: ' . $this->prm->clcFiltro . ',
                                        bdo_id: ' . $this->prm->bdo_id . ',
                                        tdf_id: "' . $this->prm->tdf_id . '",
                                        rfc_id: "' . $this->prm->rfc_id . '",
                                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                                        bdoFiltro: ' . $this->prm->bdoFiltro . ',
                                        agrupador: "' . $this->prm->agrupador . '",
                                        fecha: $("#cap_fecha").val(),
                                        tmc_id: "C"
                                    });
                                });

                                $("#btnCancelar").click(function(){
                                    $("#bg_capfecha").hide();
                                    $("#btnGeneraPoliza").show();
                                });
                            ');
                        }
                    } //*/
                } elseif ($estadoMesCancelado == 'A') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
                } elseif ($estadoMesCancelado == 'C') {
                    $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de cancelación esta cerrado</div>';
                }

                $this->agregaTools($botones);
            } /// tipo de ministrado
        }
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        if(isset($botonGeneraPa)){
            $this->mixHtml($botonGeneraPa);
        }
        $this->setContenedor('botonC');
        if(isset($botonGeneraPc)){
            $this->mixHtml($botonGeneraPc);
        }

        $this->mixScript('
                $("#btnRegresa").click(function(){
                    comunica("' . $this->pantalla . '", {
                        metodo: "PonBarraBotones",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: ' . $this->prm->mes . ',
                        gur: ' . $this->prm->gur . ',
                        clc_id: "' . $this->prm->clc_id . '",
                        clcFiltro: ' . $this->prm->clcFiltro . ',
                        bdo_id: ' . $this->prm->bdo_id . ',
                        tdf_id: "' . $this->prm->tdf_id . '",
                        rfc_id: "' . $this->prm->rfc_id . '",
                        bdo_referencia: "' . $this->prm->bdo_referencia . '",
                        bdoFiltro: ' . $this->prm->bdoFiltro . '
                    });
                });
            ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_documento = substr($this->prm->gur, 0, 1);
        $ur_id_documento = substr($this->prm->gur, 1, 2);
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $gpo_id_documento;
            $fechaValidar->ur_id = $ur_id_documento;
            $fechaValidar->fecha = $fecha;
            $fechaValidar->anio = APP_EJERCICIO;
            $res = validaFecha($fechaValidar);

            if (!$res->valido) {
                $this->mixScript('notify("error:' . $res->mensaje . '","error");');
                return false;
            }
            if (!$res->anio) {
                $this->mixScript('notify("El año es incorrecto","error");');
                return false;
            }
            if ($res->edoMesContable == 'C') {
                $this->mixScript('notify("El mes esta cerrado","error");');
                return false;
            }
            $fecha = $res->fechaMysql;
        } else {
            $this->mixScript('notify("Error en la fecha","error");');
            return false;
        }

        $bandera = 0;
        $mensajesp = "ERROR";

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $generaPolizaMinistrado = new sp_MinistradoCancelado2024();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA MINISTRACIÓN " . APP_EJERCICIO_ANTERIOR);
            $generaPolizaMinistrado->setEjercicio(intval(APP_EJERCICIO_ANTERIOR));
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        } else {
            $generaPolizaMinistrado = new sp_MinistradoAutorizado2024();
            $generaPolizaMinistrado->setGpo_id($gpo_id_documento);
            $generaPolizaMinistrado->setUr_id($ur_id_documento);
            $generaPolizaMinistrado->setBdm_agrupador($this->prm->agrupador);
            $generaPolizaMinistrado->setPma_fecha($fecha);
            $generaPolizaMinistrado->setTmc_id($this->prm->tmc_id);
            $generaPolizaMinistrado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DE LA MINISTRACIÓN " . APP_EJERCICIO_ANTERIOR);
            $generaPolizaMinistrado->setEjercicio(intval(APP_EJERCICIO_ANTERIOR));
            $generaPolizaMinistrado->setSis_id("SCG");
            $generaPolizaMinistrado->execute(false, false);
            $bandera = $generaPolizaMinistrado->getBandera();
            $mensajesp = $generaPolizaMinistrado->getMensaje();
        }

        $mensaje = "";
        $tipo = "";

        if ($bandera != 1) {
            $mensaje = "ERROR:" . $mensajesp;
            $tipo = "error";
        } else {
            $mensaje = $mensajesp;
            $tipo = "success";
        }

        if (isset($this->prm->tmc_id) && $this->prm->tmc_id == 'C') {
            $this->mixScript('
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "C"
                });
            ');
        } else {
            $this->mixScript('
                notify("' . $mensaje . '","' . $tipo . '");
                comunica("' . $this->pantalla . '",{
                    metodo: "Documento",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    clc_id: "' . $this->prm->clc_id . '",
                    clcFiltro: ' . $this->prm->clcFiltro . ',
                    bdo_id: ' . $this->prm->bdo_id . ',
                    tdf_id: "' . $this->prm->tdf_id . '",
                    rfc_id: "' . $this->prm->rfc_id . '",
                    bdo_referencia: "' . $this->prm->bdo_referencia . '",
                    bdoFiltro: ' . $this->prm->bdoFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "A"
                });
            ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['17']['17']['1'])) {
    new ControladorMinistrado2024();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
