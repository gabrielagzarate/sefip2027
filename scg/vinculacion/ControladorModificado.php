<?php
//librerias
DEFINE("LINEAS_POR_PAGINA", 1000);
session_start();
require_once dirname(__FILE__) . '/../../lib/getFolio.php';
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/scg/vinculacion/vinculacionModificado.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPoliza.php';
require_once dirname(__FILE__) . '/../../lib/scg/getPolizaAgrupador.php';
require_once dirname(__FILE__) . '/../../lib/scg/pintaPoliza.php';
require_once dirname(__FILE__) . '/../../lib/validaFecha.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';
require_once dirname(__FILE__) . '/../../ent/scg/vinculacion/vinculacion/sp_Modificado.php';

class ControladorModificado extends WebResponse
{
    private $escritura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    private $nivel;
    private $origen;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();
        //Permisos de acceso a las tareas del controlador
        $this->escritura = isset($_SESSION[_S]['prm']['C']['5']['9']['2']) ? TRUE : FALSE;
        $this->gpo_id = $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = $_SESSION[_S]['clasAdm-ur'];
        $this->nivel = $_SESSION[_S]['nvj_id'];
        $this->ue_id = getSession('clasAdm-ue', true);

        if ($this->nivel < 3) {
            $this->mixScript('
                $("#toggleCA").hide();
            ');
        }
        $this->origen = isset($this->prm->origen) ? $this->prm->origen : 'opcion';
        if ($this->origen == 'opcion')
            $this->prm->mes = isset($this->prm->mes) ? $this->prm->mes : DATE("m");

        switch ($this->metodo) {
            case 'DibujaPantalla':
                $this->DibujaPantalla();
                break;
            case 'MuestraDatos':
                $this->MuestraDatos();
                break;
            case 'Documento':
                $this->Documento();
                break;
            case 'ObraActividad':
                $this->ObraActividad();
                break;
            case 'ClaveFinanciamiento':
                $this->ClaveFinanciamiento();
                break;
            case 'MesHasta':
                $this->MesHasta();
                break;
                // case 'Filtro':
                //     $this->Filtro();
                //     break;
            case 'MuestraDatosPagina':
                $this->MuestraDatosPagina();
                break;
            case 'GeneraPoliza':
                $this->GeneraPoliza();
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

    private function PonBarraBotones()
    {
        // Botones y opciones que se dibujarán en la barra de botones
        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->type = 'button';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = 'btnLimpiar';
        $btnLimpiar->name = 'btnLimpiar';
        $btnLimpiar->text = 'Limpiar';
        $btnLimpiar->value = 'Limpiar';
        $btnLimpiar->type = 'button';
        $btnLimpiar->icon = "refresh me-2";
        $btnLimpiar->color = "cielo";
        $btnLimpiar->class = "me-1";

        $omoFiltro = FALSE;
        $modFiltro = FALSE;

        if (isset($this->prm->omoFiltro) && $this->prm->omoFiltro == 1) {
            $omoFiltro = TRUE;
        }
        if (isset($this->prm->modFiltro) && $this->prm->modFiltro == 1) {
            $modFiltro = TRUE;
        }

        $botones = '
            <div class="d-flex flex-row">
                <fieldset class="col-xs-1 me-1" style="padding-right: 1px !important;">
                    <input id="inputOmo_id" placeHolder="ORIGEN" type="text" maxlength=6 class="form-control" value="' . ($omoFiltro ? $this->prm->omo_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <input id="inputMod_id" placeHolder="FOLIO" class="form-control" value="' . ($modFiltro ? $this->prm->mod_id : "") . '"/>
                </fieldset>
                <fieldset class="col-xs-2 me-1" style="padding-right: 1px !important;">
                    <button id="excelModificado" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i> Excel</button>
                </fieldset>
            </div>
        ';

        $this->agregaTools($botones);

        // dibujamos los Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ?
                AND msc_id NOT IN ( 0, 13 )
        ');
        $this->consultar([$this->gpo_id, $this->ur_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($this->prm->mes == $row['msc_id']) $inicio = 'active';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);

            $pestana .= '
                    <li class="nav-item" opcion="' . $row['msc_id'] . '" ultimo>
                        <a class="nav-link ' . $inicio . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                    </li>
                ';

            $contenedor .= '
                    <div id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
                ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
                <div id="page-tabs"><ul class="nav nav-tabs" role="tablist">
                ' . $pestana . '
                </ul></div><br>
            ');

        // Dibujamos el contenido
        $this->mixScript('
            comunica("' . $this->pantalla . '",{
                metodo: "MuestraDatos",
                origen: "' . $this->origen . '",
                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                mes: $("#page-tabs li").children(".active").attr("mes"),
                gur: $("#inputGur").val(),
                omo_id: $("#inputOmo_id").val(),
                mod_id: $("#inputMod_id").val()
            });
        ');

        $this->mixScript('
            $("#opciones").click( function(e){
                //console.log( $("#cveFin_id").val() + "xx" );
                comunica("' . $this->pantalla . '", {
                    metodo: "Filtro",
                    origen: "' . $this->origen . '",
                    mes: $("#page-tabs li").children(".active").attr("mes"),
                    gur: $("#inputGur").val(),
                    omo_id: $("#inputOmo_id").val(),
                    mod_id: $("#inputMod_id").val()
                });
            });

            $("#btnLimpiar").click( function(e){
                $("#inputGur").val("");
                $("#inputOmo_id").val("");
                $("#inputMod_id").val("");

                e.preventDefault();
                var seleccionado = $(".meses");
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    origen: "' . $this->origen . '",
                    gur: $("#inputGur").val(),
                    omo_id: $("#inputOmo_id").val(),
                    mod_id: $("#inputMod_id").val()
                });

                e.stopPropagation();
                return false;
            });

            $("#excelModificado").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/excel/scg/vinculacion/exp_Modificado.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "9" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "M" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputOmo_id").attr("value", $("#inputOmo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputMod_id").attr("value", $("#inputMod_id").val() ))

                    $("body").append(form);
                    form.submit();
            });

            $("#imprimirModificado").click( function(e) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/reporte.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "5" ))
                        .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "9" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "mmc_id").attr("value", "M" ))

                        .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs li").children(".active").attr("mes") ))

                        .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputOmo_id").attr("value", $("#inputOmo_id").val() ))
                        .append($("<input>").attr("type", "hidden").attr("name", "inputMod_id").attr("value", $("#inputMod_id").val() ))

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
        $html = '<div id="myGridModificado" class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                <table id="modificado" class="table table-striped table-bordered">
                        <thead class="sticky-top" style="z-index:100;">
                            <tr class="warning" role="row">
                                <th class="warning" role="columnheader" style="width:2px; text-align:center;">ORIGEN</th>
                                <th class="warning" role="columnheader" style="width:3px; text-align:center;">FOLIO</th>
                                <th class="warning" role="columnheader" style="text-align:center;">CONCEPTO</th>
                                <th class="warning" role="columnheader" style="width:5px; text-align:center;">AMPLIACION</th>
                                <th class="warning" role="columnheader" style="width:5px; text-align:center;">REDUCCION</th>
                                <th class="warning" role="columnheader" style="width:2px; text-align:center;">ESTADO</th>
                                <th class="warning" role="columnheader" style="width:7px; text-align:center;">AUTORIZACION</th>
                                <th class="warning" role="columnheader" style="width:2px !important; text-align:center;">POL. AUT.</th>     
                                <th class="warning" role="columnheader" style="width:5px; text-align:center;">FECHA</th>
                                <th class="warning" role="columnheader" style="width:2px !important; text-align:center;"></th> 
                            </tr>
                        </thead>
                    <tbody></tbody>
                </table></div>';
        $this->mixHtml($html);

         $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaModificado > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');


        $this->mixScript('
            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li a").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("a").addClass("active");

                comunica("' . $this->pantalla . '",{
                    metodo: "MuestraDatos",
                    mes: seleccionado.attr( "mes" ),
                    origen: "' . $this->origen . '",
                    gur: $("#inputGur").val(),
                    omo_id: $("#inputOmo_id").val(),
                    mod_id: $("#inputMod_id").val()
                });

                e.stopPropagation();
                return false;
            });
        ');

        $this->mixScript('
            // Para que el GRID se ajuste al espacio disponible en pantalla
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
                return ( dataModificado[row].epl == "C" || dataModificado[row].epl == "H"  ) ? "<del><i>" + value + "</i></del>" : "<span title=\"" + value + "\">" + value + "</span>";
            }

            function FormatoMC(row, cell, value, columnDef, dataContext){
                if( value.substring( 1 ) == "A" ){
                    return value.substring( 0, 1 );
                }else{
                    return "<span title=\"La póliza corresponde a la cancelación de una afectación que fué autorizado en diferente mes\">" + value.substring( 0, 1 ) + "<span style=\"color:red\">" + value.substring( 1 ) + "</span></span>";
                }
            }

            function updateFilter(){
                dataViewModificado.setFilterArgs({ searchString: searchString });
                dataViewModificado.refresh();
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
                $("#modificado tbody").html("");
                const tbody = $("#modificado tbody");
                dataModificado = [];
                i = 0;
                for(var key in json){
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    var d = (dataModificado[i] = {});
                    d["id"] = json[key].key;
                    gur = json[key].gpo_id + json[key].ur_id;
                    mod_id = json[key].mod_id;
                    omo_id = json[key].omo_id;
                    concepto = json[key].mod_concepto;
                    ampliacion = (json[key].mod_ampliacion == 0) ? "" : 
                        formatoMoneda.format(json[key].mod_ampliacion);
                    reduccion = (json[key].mod_reduccion == 0) ? "" : 
                        formatoMoneda.format(json[key].mod_reduccion);
                    estado = json[key].estado;
                    fechaAutorizacion = (json[key].fechaAutorizacion == null) ? "" : json[key].fechaAutorizacion;
                    polizaAutorizado = (json[key].polizaAutorizado == null) ? "" : json[key].polizaAutorizado;
                    fechaAutorizado = (json[key].fechaAutorizado == null) ? "" : json[key].fechaAutorizado;
                    agrupador = json[key].agrupador;


                    tbody.append(
                        "<tr class=\' seleccionable \'><td  style = \' text-align: center; white-space: nowrap; \'>" 
                        + omo_id + "</td><td>" 
                        + mod_id + "</td><td style = \' white-space: pre-wrap; \'>" 
                        + concepto + "</td><td style = \' text-align: right; white-space: pre-wrap; \'>" 
                        + ampliacion + "</td><td style = \' text-align: right; white-space: nowrap; \'>"
                        + reduccion  + "</td><td style = \' text-align: center; white-space: nowrap; \'>"
                        + estado + "</td><td style = \' text-align: center; white-space: nowrap; \'>" 
                        + fechaAutorizacion + "</td><td style = \' text-align: center; white-space: nowrap; \'>" 
                        + polizaAutorizado + "</td><td style = \' text-align: center; white-space: nowrap; \'>"
                        + fechaAutorizado + "</td><td style = \' text-align: center; white-space: nowrap; \'>"
                        + agrupador + "</td><td style=\'display:none;\'>" + gur + "</td></tr>");

                    i++;
                }      

                $(document).ready(function() { 
                    $("#modificado tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaModificado");
                        },
                        function() {                      
                            $(this).removeClass("coloreaModificado"); 
                        }
                    ); 
                    
                    $(".seleccionable").click(function() {                                             
                        $(".seleccionable").css("font-weight","normal");
                        $(".seleccionable").removeClass("seleccionado");
                        $(this).addClass("seleccionado");
                        $(this).css("font-weight","bold");
                    });
                });
            }

            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalModificado").on("hide.bs.modal", function (){
                $("#declaraRangoModificado").remove();
            });

            // Para obtener el valor de una celda al presionar doble click en ella
            // --------------------------------------------------------------------------------------------------------------        
            $(document).ready(function() { 
                $("#modificado").on("dblclick", "tbody>tr", function() {
                    // Obtener el índice de la fila (comenzando desde 0)
                    var rowIndex = $(this).index();

                    // Obtener el contenido de la fila
                    var rowData = [];
                    $(this).find("td").each(function() {
                        rowData.push($(this).text());
                    });

                    var omo_id = rowData[0];
                    var mod_id = rowData[1];
                    var agrupador = rowData[9];
                    var gur = rowData[10];

                    var var_omoFiltro = 1;
                    var var_modFiltro = 1;
                    if( $("#inputOmo_id").val() == "" ){
                        var_omoFiltro = 2;
                    }
                    if( $("#inputMod_id").val() == "" ){
                        var_modFiltro = 2;
                    }

                    comunica("' . $this->pantalla . '", {
                        metodo: "Documento",
                        origen: "' . $this->origen . '",
                        seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                        mes: $("#page-tabs li").children(".active").attr("mes"),
                        omo_id: omo_id,
                        omoFiltro: var_omoFiltro,
                        gur: gur,
                        mod_id: mod_id,
                        modFiltro: var_modFiltro,
                        agrupador: agrupador
                    });
                });
            });        

            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();
        ');
    }

    private function MuestraDatos()
    {
        $data = $this->DameInformacion();

        $this->mixScript('
                loadData( ' . $data . ' );
            ');

        return false;
    }

    private function DameInformacion()
    {

        $omo_id = (isset($this->prm->omo_id) ? $this->prm->omo_id : '');
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $mod_id = '';
        if (isset($this->prm->mod_id) && !empty($this->prm->mod_id)) {
            $folios = explode(",", str_replace(' ', '', $this->prm->mod_id));
            foreach ($folios as $mod) {
                $mod_id .= $mod . ',';
            }
            $mod_id = substr($mod_id, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

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

        $detalle = FALSE;
        $paginado = TRUE;
        $registroInicial = 0;
        $datos = vinculacionModificado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'M',
            $niveles,
            array("omo_id" => strtoupper($omo_id), "gur" => $gur, "mod_id" => $mod_id),
            $paginado,
            $registroInicial,
            LINEAS_POR_PAGINA
        );

        $nTotalPages = 0;
        if ($paginado == true) {

            $nTotalPages = $datos[0]['gpo_id'];
            unset($datos[0]);
        }
        if ($nTotalPages > 1 && $paginado == true) {
            $this->mixScript('
                $("#ModificadoPaginador").attr("last", 1 );
                var options = {
                    numberOfPages: 5,
                    currentPage: 1,
                    totalPages: ' . $nTotalPages . ',
                    containerClass: "pull-right",
                    listContainerClass: "nav nav-tabs",
                    itemContainerClass: function ( type, page, current ) { return (page === current) ? "active" : "pointer-cursor"; },
                    onPageClicked: function(e,originalEvent,type,page){
                        // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                        if ( $("#ModificadoPaginador").attr("last") != page ) {
                            comunica("' . $this->pantalla . '",{
                                metodo: "MuestraDatosPagina",
                                mes: $("#page-tabs li").children(".active").attr("mes"),
                                origen: "' . $this->origen . '",
                                gur: $("#inputGur").val(),
                                clc_id: $("#inputClc_id").val(),
                                bdo_id: $("#inputBdo_id").val(),
                                rfc_id: $("#inputRfc_id").val(),
                                bdo_referencia: $("#inputBdo_referencia").val(),
                                paginado: true,
                                numpagina: page ,
                                numreg: ' . LINEAS_POR_PAGINA . '
                            });
                            $("#ModificadoPaginador").attr("last", page );
                        }
                    }
                }
                console.log(options);
                $("#ModificadoPaginador").bootstrapPaginator(options);
            ');
        } else {
            $this->mixScript('$("#ModificadoPaginador").html("")');
        }
        return json_encode($datos);
    }

    public function MuestraDatosPagina()
    {
        $data = $this->DameInformacionPagina();

        $this->mixScript('
            loadData( ' . $data . ' );
        ');

        return false;
    }

    private function DameInformacionPagina()
    {

        $omo_id = (isset($this->prm->omo_id) ? $this->prm->omo_id : '');
        $gur = (isset($this->prm->gur) ? $this->prm->gur : $this->gpo_id . $this->ur_id);
        $mod_id = '';
        if (isset($this->prm->mod_id) && !empty($this->prm->mod_id)) {
            $folios = explode(",", str_replace(' ', '', $this->prm->mod_id));
            foreach ($folios as $mod) {
                $mod_id .= $mod . ',';
            }
            $mod_id = substr($mod_id, 0, -1);
        }
        $periodo['mesDesde'] = (isset($this->prm->mes) ? $this->prm->mes : '');
        $periodo['mesHasta'] = (isset($this->prm->mes) ? $this->prm->mes : '');

        $bdo_id = (isset($this->prm->bdo_id) ? $this->prm->bdo_id : '');
        $rfc_id = (isset($this->prm->rfc_id) ? $this->prm->rfc_id : '');
        $bdo_referencia = (isset($this->prm->bdo_referencia) ? $this->prm->bdo_referencia
            : '');
        $paginado = (isset($this->prm->paginado) ? $this->prm->paginado : false);
        $numpagina = (isset($this->prm->numpagina) ? $this->prm->numpagina : 1);
        $numreg = (isset($this->prm->numreg) ? $this->prm->numreg : 0);
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

        $detalle = FALSE;
        $nInicial = (($numpagina - 1) * LINEAS_POR_PAGINA);   // Como aqui cargamos la pagina inicialmente, es la primera
        $datos = vinculacionModificado(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            $programatico,
            $ClaveFinanciamiento,
            $reporte,
            $detalle,
            'M',
            $niveles,
            array("omo_id" => $omo_id, "gur" => $gur, "mod_id" => $mod_id),
            $paginado,
            $nInicial,
            LINEAS_POR_PAGINA
        );

        if ($paginado == true) {
            unset($datos[0]);
        }


        return json_encode($datos);
    }

    private function Documento()
    {
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql("SELECT msc_id, ems_id FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ");
        $conexion->consultar([$this->gpo_id, $this->ur_id]);

        while ($dataRow = $conexion->extraer()) {
            $mesContable[$dataRow['msc_id']] = $dataRow;
        }

        $gpo_id_folio = substr($this->prm->gur, 0, 1);
        $ur_id_folio = substr($this->prm->gur, 1, 2);
        $omo_id_folio = (isset($this->prm->omo_id) ? $this->prm->omo_id : '');
        $mod_id_folio = (isset($this->prm->mod_id) ? $this->prm->mod_id : '');

        $generaPolizaAutorizado = FALSE;

        $btnRegresa = new stdClass();
        $btnRegresa->id = 'btnRegresa';
        $btnRegresa->name = 'btnRegresa';
        $btnRegresa->text = 'Regresar';
        $btnRegresa->value = 'Regresar';
        $btnRegresa->icon = 'solid fa-circle-left me-2';
        $btnRegresa->color = 'gris';
        $btnRegresa->class = "me-1";

        $botones = $this->ponBoton($btnRegresa);
        $this->agregaTools($botones);

        $folio = getFolio(
            $gpo_id_folio,
            $ur_id_folio,
            $omo_id_folio,
            $mod_id_folio,
            array("ejercicio" => APP_EJERCICIO, "provision" => 2)
        );
        $html = '<legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Análisis del folio a generar la póliza del modificado</legend>';
        $html .= '
                <div class="px-3">
                    <fieldset class="col-md-12 col-xs-12">
                        <legend style="border-bottom: 1px solid #e5e5e5; margin-bottom: 22px;">Información del Folio</legend>
                        <table class="table table-bordered">
                            <tr class="success" style="background-color: #dff0d8 !important;">
                                <th>MODALIDAD</th>
                                <th>ORIGEN</th>
                                <th>FOLIO</th>
                                <th>TIPO DE ADECUACIÓN</th>
                                <th>CONCEPTO</th>
                                <th>SUBSECRETARÍA</th>
                                <th>TIPO DE FOLIO</th>
                                <th>CLAVE FINANCIAMIENTO</th>
                                <th>FECHA AUTORIZACIÓN</th>
                            </tr>
                            <tr>
                                <td>' . $folio->modalidad . '</td>
                                <td>' . $folio->origen . '</td>
                                <td>' . $folio->folio . '</td>
                                <td>' . $folio->tmd_id . '-' . $folio->tipoAdecuacion . '</td>
                                <td>' . $folio->concepto . '</td>
                                <td>' . $folio->subsecretaria . '</td>
                                <td>' . $folio->tipoFolio . '</td>
                                <td>' . $folio->cvefin . '</td>
                                <td>' . $folio->fechaAutorizacion . '</td>
                            </tr>
                        </table>
                    </fieldset><br/>
            ';

        /* Detalle de la Adecuación presupuestal */
        $html .= '
                    <fieldset class="col-md-12 col-xs-12 ejercido_bloque">
                        <legend>Detalle de la Adecuación Presupuestal</legend>
            ';

        $html .= '
            <table id="table_detalle" class="table table-bordered">
                <tr class="warning">
                    <table class="table table-bordered">
                        <tr class="warning">
                            <th>CLAVE PRESUPUESTAL</th>
                            <th>TIPO CLAVE</th>
                            <th>MOV</th>
                            <th>AMPLIACIÓN</th>
                            <th>REDUCCIÓN</th>
                            <th>ENE</th>
                            <th>FEB</th>
                            <th>MAR</th>
                            <th>ABR</th>
                            <th>MAY</th>
                            <th>JUN</th>
                            <th>JUL</th>
                            <th>AGO</th>
                            <th>SEP</th>
                            <th>OCT</th>
                            <th>NOV</th>
                            <th>DIC</th>
                        </tr>';

        foreach ($folio->detalleModificado as $key => $value) {
            $html .= '
                <tr>
                    <td>' . $value->pre_claveCompleta . '</td>
                    <td align="center">' . $value->tipoClave . '</td>
                    <td>' . $value->mov_id . '</td>
                    <td>' . number_format($value->ampliacion, 2) . '</td>
                    <td>' . number_format($value->reduccion, 2) . '</td>
                    <td>' . number_format($value->ene, 2) . '</td>
                    <td>' . number_format($value->feb, 2) . '</td>
                    <td>' . number_format($value->mar, 2) . '</td>
                    <td>' . number_format($value->abr, 2) . '</td>
                    <td>' . number_format($value->may, 2) . '</td>
                    <td>' . number_format($value->jun, 2) . '</td>
                    <td>' . number_format($value->jul, 2) . '</td>
                    <td>' . number_format($value->ago, 2) . '</td>
                    <td>' . number_format($value->sep, 2) . '</td>
                    <td>' . number_format($value->oct, 2) . '</td>
                    <td>' . number_format($value->nov, 2) . '</td>
                    <td>' . number_format($value->dic, 2) . '</td>
                </tr>
            ';
        }

        $html .= '
                        </table>
                    </tr>
                </table>
            ';

        $html .= '
                    </fieldset></br>
            ';

        /*         * *********************************** Información de la Póliza Contable ************************************************************** */
        $fechaAutorizado = '';
        if ($folio->tmd_id != 'C') {
            $generaPolizaAutorizado = TRUE;
            $fechaAutorizado = $folio->fechaAutorizacion;
            $estadoMesAutorizado = $mesContable[str_pad(
                $folio->mesAutorizado,
                2,
                '0',
                STR_PAD_LEFT
            )]['ems_id'];
        }

        $polizas = getPolizaAgrupador(
            $this->gpo_id,
            $this->ur_id,
            $this->prm->agrupador
        );

        $polizaAut = new stdClass;
        $botonGeneraPa = '';

        //preguntamos los datos de la póliza contable de autorización
        if ($polizas->valido) {
            foreach ($polizas->polizas as $key => $value) {
                //echo "<pre>"; print_r($value); echo "</pre>";//die();
                if ($value->tipoMomento == 'A') {
                    $polizaAutorizado[$key] = getPoliza(
                        $value->gpo_id,
                        $value->ur_id,
                        $value->ue_id,
                        $value->msc_id,
                        $value->pot_id,
                        $value->prf_id,
                        $value->pma_id
                    );
                    $polizaAut->valido = $polizaAutorizado[$key]->valido;
                }
            }
        }
        if (!isset($polizaAut->valido)) {
            $polizaAut->valido = FALSE;
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
                            $btnGenera->icon = 'solid fa-check me-2';
                            $btnGenera->class = 'me-1';
                            $botonGeneraPa = $this->ponBoton($btnGenera);

                            $this->mixScript('
                                $("#btnReGeneraPolizaA").click(function(){
                                    alertify.confirm(
                                        "RE HACER LA POLIZA ' . (isset($value->poliza) ? $value->poliza : '') . '",
                                        "Esta acción re construira la póliza de autorización como fue generada por el sistema de manera automatica, desea proceder?",
                                        function(){
                                            comunica("' . $this->pantalla . '",{
                                                metodo: "GeneraPoliza",
                                                origen: "' . $this->origen . '",
                                                seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                                                mes: ' . $this->prm->mes . ',
                                                gur: ' . $this->prm->gur . ',
                                                omo_id: "' . $this->prm->omo_id . '",
                                                omoFiltro: ' . $this->prm->omoFiltro . ',
                                                mod_id: ' . $this->prm->mod_id . ',
                                                modFiltro: ' . $this->prm->modFiltro . ',
                                                gpo_id: "' . $value->gpo_id . '",
                                                ur_id: "' . $value->ur_id . '",
                                                ue_id: "' . $value->ue_id . '",
                                                fecha: "' . $fechaAutorizado . '",
                                                agrupador: "' . $this->prm->agrupador . '",
                                                tmc_id: "A"
                                            });
                                        },
                                            undefined
                                    ).set("labels", {
                                        ok: "Proceder", cancel: "Cancelar"
                                    });
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
                $btnGeneraPoliza->icon = 'solid fa-check me-2';
                $btnGeneraPoliza->class = 'me-1';

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
                                omo_id: "' . $this->prm->omo_id . '",
                                omoFiltro: ' . $this->prm->omoFiltro . ',
                                mod_id: ' . $this->prm->mod_id . ',
                                modFiltro: ' . $this->prm->modFiltro . ',
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
                    $btnGeneraPolizaCerrado->icon = 'solid fa-check me-2';
                    $btnGeneraPolizaCerrado->class = 'me-1';

                    $inputFecha = new stdClass();
                    $inputFecha->id = "cap_fecha";
                    $inputFecha->name = "control";
                    $inputFecha->class = 'fecha';
                    $inputFecha->label = '';
                    $inputFecha->enDiv = TRUE;
                    $inputFecha->formato = 'fecha';
                    $inputFecha->labelClass = '_32 txtL';
                    $inputFecha->required = TRUE;

                    $btnCancelar = new stdClass();
                    $btnCancelar->id = 'btnCancelar';
                    $btnCancelar->icon = 'times me-2';
                    $btnCancelar->name = 'nuevo';
                    $btnCancelar->color = 'rojo';
                    $btnCancelar->text = 'Cancelar';
                    $btnCancelar->value = 'Cancelar';

                    $html .= '
                            <div id="bg_capfecha" style="position:absolute; background-color:rgba(0,0,0,.3); width:100%; height:100%; top:0px; left:0px; display:none;">
                                <div id="capfecha">
                                    <legend>Mes del Modificado esta cerrado, capture una nueva fecha</legend>
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
                                omo_id: "' . $this->prm->omo_id . '",
                                omoFiltro: ' . $this->prm->omoFiltro . ',
                                mod_id: ' . $this->prm->mod_id . ',
                                modFiltro: ' . $this->prm->modFiltro . ',
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
            }
        } elseif (isset($estadoMesAutorizado)){
            if ($estadoMesAutorizado == 'A') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">Generar Póliza</div>';
            } elseif ($estadoMesAutorizado == 'C') {
                $botones = '<div style="display:inline-block; color:#900; padding:10px;">El mes de autorización esta cerrado</div>';
            }
        }

        $this->agregaTools($botones);
        $this->mixHtml($html);

        $this->setContenedor('botonA');
        $this->mixHtml($botonGeneraPa);

        $this->mixScript('
            $("#btnRegresa").click(function(){
                comunica("' . $this->pantalla . '", {
                    metodo: "PonBarraBotones",
                    origen: "' . $this->origen . '",
                    seleccion: "' . (isset($this->prm->seleccion) ? $this->prm->seleccion : "") . '",
                    mes: ' . $this->prm->mes . ',
                    gur: ' . $this->prm->gur . ',
                    omo_id: "' . $this->prm->omo_id . '",
                    omoFiltro: ' . $this->prm->omoFiltro . ',
                    mod_id: ' . $this->prm->mod_id . ',
                    modFiltro: ' . $this->prm->modFiltro . '
                });
            });
        ');
    }

    private function GeneraPoliza()
    {
        $gpo_id_folio = substr($this->prm->gur, 0, 1);
        $ur_id_folio = substr($this->prm->gur, 1, 2);
        $omo_id_folio = $this->prm->omo_id;
        $mod_id_folio = $this->prm->mod_id;
        if (isset($this->prm->fecha) && $this->prm->fecha != '') {
            $fecha = $this->prm->fecha;
            $fechaValidar = new stdClass();
            $fechaValidar->gpo_id = $gpo_id_folio;
            $fechaValidar->ur_id = $ur_id_folio;
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

        $generaPolizaModificado = new sp_Modificado();
        $generaPolizaModificado->setGpo_id($gpo_id_folio);
        $generaPolizaModificado->setUr_id($ur_id_folio);
        $generaPolizaModificado->setMod_id($mod_id_folio);
        $generaPolizaModificado->setOmo_id($omo_id_folio);
        $generaPolizaModificado->setPma_fecha($fecha);
        $generaPolizaModificado->setBit_observacion("PÓLIZA GENERADA DESDE LA PANTALLA DE VINCULACIÓN DEL MODIFICADO");
        $generaPolizaModificado->setEjercicio(APP_EJERCICIO);
        $generaPolizaModificado->setSis_id("SCG");
        $generaPolizaModificado->execute(false, false);
        $bandera = $generaPolizaModificado->getBandera();
        $mensajesp = $generaPolizaModificado->getMensaje();


        if ($bandera != 1) {
            $mensaje = $mensajesp;
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
                    omo_id: "' . $this->prm->omo_id . '",
                    omoFiltro: ' . $this->prm->omoFiltro . ',
                    mod_id: ' . $this->prm->mod_id . ',
                    modFiltro: ' . $this->prm->modFiltro . ',
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
                    omo_id: "' . $this->prm->omo_id . '",
                    omoFiltro: ' . $this->prm->omoFiltro . ',
                    mod_id: ' . $this->prm->mod_id . ',
                    modFiltro: ' . $this->prm->modFiltro . ',
                    agrupador: "' . $this->prm->agrupador . '",
                    fecha: "' . $fecha . '",
                    tmc_id: "A"
                });
            ');
        }
    }
}
//Permisos de acceso al controlador
if (isset($_SESSION[_S]['prm']['C']['5']['9']['1'])) {
    new ControladorModificado();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
