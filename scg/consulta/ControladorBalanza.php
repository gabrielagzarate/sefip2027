<?php
/*
  Mostramos en pantalla la balanza de comprobación por mes, a primer nivel
  La balanza acumulada, el detalle de cuántos niveles y todo eso, será en opciones de reporte
 */

//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scg/consulta/balanza.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionFinanciamiento.php';
require_once dirname(__FILE__) . '/../../srv/ClasificacionAdministrativaB.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorBalanza extends WebResponse
{
    //Constructor
    private bool $lectura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        // Determinamos si el usuario actual tiene permisos de lectura / escritura en la base de datos
        $this->lectura = isset($_SESSION[_S]['prm']['C']['4']['3']['1']) ? true : false;
        $this->gpo_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-gpo'] : $_SESSION[_S]['gpo_id'];
        $this->ur_id = $_SESSION[_S]['nvj_id'] > 2 ? $_SESSION[_S]['clasAdm-ur'] : $_SESSION[_S]['ur_id'];
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
            case 'mesHasta':
                $this->mesHasta();
                break;
            case 'tipoRecurso':
                $this->tipoRecurso();
                break;
            case 'filtros':
                $this->filtros();
                break;
            case 'buscarClc':
                $this->buscarClc();
                break;
            case 'buscarBdo':
                $this->buscarBdo();
                break;
            case 'PonBarraBotones':
                $this->PonBarraBotones();
                break;
            case 'ValidaCuenta':
                $this->ValidaCuenta();
                break;
            case 'ValidaCuentaContable':
                $this->ValidaCuentaContable();
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
        $botones = '
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <button id="opciones" opcion="#myModalBalanza" data-bs-target="#myModalBalanza" data-placement="bottom" type="button" class="btn btn-outline-secondary me-1" data-bs-toggle="myModalBalanza" title="Modal de opciones"><i class="fa-solid fa-bars me-2"></i>
                        Opciones
                    </button>
                    <button id="detalle" data-placement="bottom" title="Mostrar la balanza a todos los niveles de la Cuenta seleccionada" class="btn btn-outline-warning me-1"><i class="fa-solid fa-heart me-2"></i>
                        Detalle
                    </button>
                    
                    <div class="input-group" style="float: right; width:250px">
                        <input id="buscar" type="text" class="form-control" placeholder="Texto a buscar ..." aria-label="buscar" aria-describedby="addon-wrapping">
                        <span class="input-group-text" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                </div>
            </div>
            <span style="height:6px; display:block"></span>
        ';

        $this->setContenedor("toolsPantalla");
        $this->mixScript('$("#toolsPantalla").removeClass("d-flex");');
        $this->mixHtml($botones);
        

        $this->setContenedor("contenedor");
        // Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $contador = 0;
        $this->conectar();
        $this->setSql('SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                LEFT JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ? ');
        $this->consultar([$this->gpo_id, $this->ur_id]);
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
                    <div></div>
                    <a role="tab" data-bs-toggle="tab" class="nav-link ' . $active . ' meses" mes="' . $row['msc_id'] . '" estado="' . $row['estado'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
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
            <div id="page-tabs"><ul class="nav nav-tabs" role="tablist">
            ' . $pestana . '
            </ul></div><br>
        ');

        $this->mixScript('
            $("nav").hide();
            $(".modulos").hide();' .
            (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                : '') . '
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");
            $("div[aria-describedby=cargaDatosMayor]").html("");
                $("div[aria-describedby=cargaDatosCgCveFin]").html("");
            if( $( ".container" ).css( "max-width" ) == "1170px" ){
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $(".meses").click( function(e){
                $( "li" ).removeClass("active");
                $(this).parent( "li" ).addClass("active");
            });
            
            $(document).on("click", "#sefipSidebar li", function (e){
                e.preventDefault();
                $("nav").show();
                $(".modulos").show();
                $("#sticky-anchor").hide();
                $("#sticky").hide();
                $(".cabecera").show();
                $("#mainContainer").css( "margin-top","40px" );
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosMayor]").html("");
                $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                $("#ayuda").addClass("gris");
                $("#tip").addClass("gris");
                $("#contenedor").html("");
                $("nav").menusinpres("clickMenu");
                $(document).off("click", "#sefipSidebar li" );
                return false;
            });
        
            //Mostrar el listado de Apertura desde un inicio
            /*$(document).ready(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    comboMesDesde: $(this).attr("mes")
                });
            });*/

            $("#opciones").click( function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "filtros",
                    mes: $("#page-tabs a").parent(".active").children("a").attr("mes")
                });
            });
            
            $("#detalle").click(function(e){
                e.preventDefault();              
                var cuenta =  $(".seleccionado td:nth-child(1)").text();
                if ( cuenta == "" ) { e.stopPropagation();   return false; }
                // Sólo de nivel de registro ( 1.1.3 ), ya que NO paginamos y puede regresar muchos registros
                if( cuenta.trim().length == 5 ){
                  comunica("scg/consulta/ControladorBalanzaDetalle.php", {
                        mes: $("#detalle").attr("msc"),
                        cuenta: cuenta
                    });
                        //load_viewer("BalanzaDetalle","DETALLE","div","",1030,true);
                }else{
                    notify("El detalle sólo se muestra para cuentas de tercer nivel (rubro)", "warning");
                }
                e.stopPropagation();
                return false;
            });           
        ');

        $this->dibujaPantalla();
    }

    public function dibujaPantalla()
    {
        $html = '<div id="myGridBalanza" class="tabla-contenedor col-md-12" style="overflow: scroll;">
                <table id="balanza" class="table table-striped table-bordered">
                        <thead class="sticky-top">
                            <tr class="warning" role="row">
                                <th rowspan="2" class="warning" role="columnheader" style="width:90px !important; text-align:center;">CUENTA</th>
                                <th rowspan="2" class="warning" role="columnheader" style= "text-align:center;">DESCRIPCIÓN</th>
                                <th colspan="2" class="warning" role="columnheader" style= "text-align:center;">SALDOS INICIALES</th>
                                <th colspan="2" class="warning" role="columnheader" style= "text-align:center;">MOVIMIENTOS</th>
                                <th colspan="2" class="warning" role="columnheader" style= "text-align:center;">SALDOS FINALES</th>
                                <th rowspan="2" class="warning" role="columnheader" style= "text-align:center;">NAT</th>
                            </tr>
                            <tr>
                                <th style= "text-align:center;">DEBE</th>
                                <th style= "text-align:center;">HABER</th>
                                <th style= "text-align:center;">DEBE</th>
                                <th style= "text-align:center;">HABER</th>
                                <th style= "text-align:center;">DEBE</th>
                                <th style= "text-align:center;">HABER</th>
                            </tr>
                        </thead>
                    <tbody></tbody>
                </table>
        </div>';
        $this->setContenedor("contenedor");
        $this->mixScript('$("div[aria-describedby=cargaDatosMayor]").remove();
                $("div[aria-describedby=cargaDatosCgCveFin]").remove();');
        // Dibujamos la pantalla que permite buscar una cuenta a nivel de mayor
        $this->mixHtml(DibujaPantallaBuscarCuentaMayorHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaMayorJS("SI"));
        // Dibujamos la pantalla que permite buscar y seleccionar Claves de Financiamiento
        // --------------------------------------------------------------------------------------------------------------
        $es116 = false;
        if ($this->gpo_id . $this->ur_id == 116) {
            $es116 = true;
        }
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(false, $es116));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());
        $this->mixHtml($html);

        $this->mixStyle('

            .tabla-contenedor table > tbody > tr.coloreaBalanza > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        $this->mixScript('
            $("#tituloPantalla").hide();
            
            // Para que el GRID se ajuste al espacio disponible en pantalla
            $("#myGridBalanza").height(
                $(document).height() -
                $("header").outerHeight() -
                $("#sticky").outerHeight() -
                $("footer").outerHeight() - 330
            );
            
            // -------------------------------------------------------------------------------------
            $("#page-tabs a").click( function(e) {
                e.preventDefault();
                var seleccionado = $(this);
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");
                var sinMov = $("#checkMovimiento").length > 0 ? ( $("#checkMovimiento").prop("checked" ) ? 1 : 0 ):1;
                comunica("' . $this->pantalla . '",{
                    metodo: "muestraDatos",
                    comboMesDesde: $(this).attr("mes")
                });
                                
                $("#detalle").attr("msc", seleccionado.attr("mes") );
                
                e.stopPropagation();
                return false;
            });
            
            var balanzaDetalle = 0;
            
            var sortcol = "CUENTA";
            var sortdir = 1;
            var searchString = "";
            
            function myFilter(item, args) {
                if( item["cuenta"] === null || item["n"] === null ){ return false }
                
                // Si NO es de la balanza detalle ...
                if( args["mayor"] === undefined ){
                    // Si empieza con numero ... buscamos en la cuenta
                    strNumeros = "0123456789";
                    if ( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ) {
                        return ( item["cuenta"].substring(0,args.searchString.length) == args.searchString );
                    // si no ... buscamos en el texto
                    } else {
                        if (args.searchString != "" && item["n"].toLowerCase().indexOf(args.searchString) == -1 ){
                            return false;
                        }
                    }
                // En la balanza detalle, filtramos por niveles a mostrar
                }else{
                    // todo
                    if( args["todo"] == true ) { return true; }
                    if( args["mayor"] == true ) { if ( item["cuenta"].trim().length == 9 ) return true }
                    // Agrupador
                    if( args["agrupador"] == true ) { if ( item["cuenta"].trim().length == 13 ) return true }
                    // Registro
                    if( args["registro"] == true ) { if ( item["cuenta"].trim().length > 13 ) return true }
                    return false;
                }
                return true;
            }
            
            function comparer(a, b){
                var x = a[sortcol], y = b[sortcol];
                return (x == y ? 0 : (x > y ? 1 : -1));
            }
            
            // ESC limpia el campo
            $("#buscar").keyup(function (e) {
                if (e.which == 27) { this.value = ""; }
                var value = $(this).val().toLowerCase();
                $("#balanza tbody>tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            function FormatoMoneda(row, cell, value, columnDef, dataContext) {
                // Le quitamos todas las comas que pudiera traer
                value = value.toString();
                strIni = ""
                strFin = ""
                
                if ( balanzaDetalle == 0 ) {
                    datos = dataBalanza[row];
                } else {
                    datos = dataBalanzaDetalle[row];
                }
                
                // Si es la línea de totales, se muestra en negrita
                if ( datos.n == "SUMAS TOTALES" ) {
                    strIni += "<b>";
                    strFin += "</b>";
                }
                
                // Si la suma de DEBE y HABER no son iguales, toda la línea se mostrará en color rojo
                if ( datos.n == "SUMAS TOTALES" ) {
                    if( ( datos.sid != datos.sih ) || ( datos.md != datos.mh ) || ( datos.sfd != datos.sfh )) {
                        strIni = "<font color=#FF0000>";
                        strFin = "</font>";
                    }
                }
                
                // Si el importe es negativo, aparerá en rojo
                if ( value < 0 ) {
                    strIni = "<font color=#FF0000>";
                    strFin = "</font>";
                }
                
                value = value.toString();
                if (value === null || value === "" || !(value != 0)) {
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
                }
            }
            
            function formatoPropio(row, cell, value, columnDef, dataContext) {
                if ( balanzaDetalle == 0 ) {
                    datos = dataBalanza[row];
                } else {
                    datos = dataBalanzaDetalle[row];
                }
                // Cuando se trate de la columna de totales ... ponemos en negrita
                if ( datos.n == "SUMAS TOTALES" ) {
                    return "<b>" + value + "</b>";
                } else {
                    return value;
                }
            }
                   
            // Cargamos la información a mostrar
            // ---------------------------------------------------------------------------------------------------
            function loadData( json ) {
            
                $("#balanza tbody").html("");
                var colorDebeInicial = "#000000";
                var colorHaberInicial = "#000000";
                var colorHaberPeriodo = "#000000";
                var colorDebePeriodo = "#000000";
                var colorHaberFinal = "#000000";
                var colorDebeFinal = "#000000";
                var negritas = "";
                var alinear = "left";

                const tbody = $("#balanza tbody");
                dataBalanza = [];
                i = 0;
                for( var key in json ){
                    const formatoMoneda = new Intl.NumberFormat("es-MX", {
                        style: "currency",
                        currency: "MXN",
                    });
                    var d = (dataBalanza[i] = {});
                    d["id"] = json[key].key;
                    cuenta = json[key].cuenta;
                    nombre = json[key].nombre.trim().toUpperCase();
                    pdx_debeInicial = (json[key].pdx_debeInicial == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_debeInicial).replace("$", "$ ");
                    pdx_haberInicial = (json[key].pdx_haberInicial == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_haberInicial).replace("$", "$ ");
                    pdx_debePeriodo = (json[key].pdx_debePeriodo == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_debePeriodo).replace("$", "$ ");
                    pdx_haberPeriodo = (json[key].pdx_haberPeriodo == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_haberPeriodo).replace("$", "$ ");
                    pdx_debeFinal = (json[key].pdx_debeFinal == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_debeFinal).replace("$", "$ ");
                    pdx_haberFinal = (json[key].pdx_haberFinal == 0) ? "-" : 
                        formatoMoneda.format(json[key].pdx_haberFinal).replace("$", "$ ");
                    cgm_naturaleza = json[key].cgm_naturaleza;                  

                    switch(nombre){
                       case "TOTAL": 
                            if( (json[key].pdx_debeInicial != json[key].pdx_haberInicial ) || ( json[key].pdx_debePeriodo != json[key].pdx_haberPeriodo ) || ( json[key].pdx_debeFinal != json[key].pdx_haberFinal )) {
                                colorDebeInicial = "#FF0000";
                                colorHaberInicial = "#FF0000";
                                colorDebePeriodo = "#FF0000";
                                colorHaberPeriodo = "#FF0000";
                                colorDebeFinal = "#FF0000";
                                colorHaberFinal = "#FF0000";
                                negritas = "bold";
                                alinear = "center";
                            }else{
                                colorDebeInicial = "#000000";
                                colorHaberInicial = "#000000";
                                colorDebePeriodo = "#000000";
                                colorHaberPeriodo = "#000000";
                                colorDebeFinal = "#000000";
                                colorHaberFinal = "#000000";                            
                                negritas = "bold";
                                alinear = "center";
                            }
                        break;
                        default:                           
                            if(json[key].pdx_debeInicial < 0){
                                colorDebeInicial = "#FF0000";
                            }else{
                                colorDebeInicial = "#000000";
                            }

                            if(json[key].pdx_haberInicial < 0){
                                colorHaberInicial = "#FF0000";
                            }else{
                                colorHaberInicial = "#000000";
                            }

                            if(json[key].pdx_debePeriodo < 0){
                                colorDebePeriodo = "#FF0000";
                            }else{
                                colorDebePeriodo = "#000000";
                            }

                            if((json[key].pdx_haberPeriodo < 0) && (nombre !== "TOTAL")){
                                colorHaberPeriodo = "#FF0000";
                            }else{
                                colorHaberPeriodo = "#000000";
                            }

                            if(json[key].pdx_debeFinal < 0){
                                colorDebeFinal = "#FF0000";
                            }else{
                                colorDebeFinal = "#000000";
                            }

                            if(json[key].pdx_haberFinal < 0){
                                colorHaberFinal = "#FF0000";
                            }else{
                                colorHaberFinal = "#000000";
                            }
                        break;                        
                    }
                    
                    tbody.append(
                        "<tr class=\' seleccionable \'><td>" 
                        + cuenta + "</td><td style =\' text-align:"+alinear+"; font-weight:"+negritas+";\'>" 
                        + nombre + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap; color:"+colorDebeInicial+";\'>" 
                        + pdx_debeInicial + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap; color:"+colorHaberInicial+";\'>" 
                        + pdx_haberInicial + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap; color:"+colorDebePeriodo+";\'>"
                        + pdx_debePeriodo + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap; color:"+colorHaberPeriodo+";\'>" 
                        + pdx_haberPeriodo + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap; color:"+colorDebeFinal+";\'>" 
                        + pdx_debeFinal + "</td><td style = \' text-align: right; font-weight:"+negritas+"; white-space: nowrap;  color:"+colorHaberFinal+";\'>" 
                        + pdx_haberFinal + "</td><td style = \' text-align: center; white-space: nowrap; \'>" 
                        + cgm_naturaleza
                        + "</td></tr>");

                    i++;
                } 
                
                $(document).ready(function() {
                    $("#balanza tbody tr").hover(
                        function() {
                            $(this).addClass("coloreaBalanza");
                        },
                        function() {                      
                            $(this).removeClass("coloreaBalanza"); 
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
    /* Regresamos en formato JSON la balanza del mes seleccionado, a nivel de mayor
      ---------------------------------------------------------------------------------------------------- */

    private function DameBalanzaPrimerNivel($comboMesDesde)
    {

        $niveles[0] = 3;
        $niveles[1] = 1;
        $niveles[2] = 1;
        $niveles[3] = 1;
        $niveles[4] = -1;
        $niveles[5] = -1;
        $niveles[6] = -1;
        $niveles[7] = -1;
        $niveles[8] = -1;

        $reporte['tipo'] = 1;
        $reporte['agrupador'] = 1;

        $periodo['mesDesde'] = $comboMesDesde; //Informacion de acuerdo al mes
        $periodo['mesHasta'] = $comboMesDesde; //Informacion de acuerdo al mes

        $busqueda['tipo'] = 1;
        $busqueda['cuentaDesde'] = 10000;
        $busqueda['cuentaHasta'] = 99999;

        $claveFinanciamiento['ecf_id'] = '';
        $claveFinanciamiento['ter_id'] = '';

        //implode($periodo)

        $parametroExtra['busqueda'] = $busqueda;
        $parametroExtra['miles'] = false;
        $parametroExtra['movimiento'] = true;
        $parametroExtra['ejercicio'] = APP_EJERCICIO;

        $datos = balanza($this->gpo_id, $this->ur_id, $this->ue_id, '', $periodo, '', $claveFinanciamiento, $reporte, false, '', $niveles, $parametroExtra);
        return json_encode($datos);
    }
    /* Mostramos la información de un sistema específico
      ---------------------------------------------------------------------------------------------------- */

    private function muestraDatos()
    {

        // Armamos los parámetros para pedir correctamente la balanza
        $comboMesDesde = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde']) != "") ? $_POST['comboMesDesde'] : 99);

        $data = $this->DameBalanzaPrimerNivel($comboMesDesde);

        $this->mixScript('            
            loadData( ' . $data . ' );
        ');

        unset($data);   // liberamos la memoria del array intermedio
    }

    private function mesHasta()
    {
        $this->setContenedor('mhasta');
        if (isset($this->prm->mes)) {
            $mes = $this->prm->mes;

            $comboMesHasta = new stdClass();
            $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable WHERE msc_id >= " . $mes;
            $comboMesHasta->id = "comboMesHasta";
            $comboMesHasta->width = "150px";
            $comboMesHasta->label = "hasta";
            $comboMesHasta->selecciona = $mes;

            $this->mixHtml($this->ponCombo($comboMesHasta));
        }
    }

    private function buscarClc()
    {

        $gpo_id = (isset($this->prm->gur) && $this->prm->gur != '' ? substr($this->prm->gur, 0, 1) : $this->gpo_id);
        $ur_id = (isset($this->prm->gur) && $this->prm->gur != '' ? substr($this->prm->gur, 1, 2) : $this->ur_id);
        $clc_id = (isset($this->prm->clc_id) && $this->prm->clc_id != '' ? $this->prm->clc_id : '');

        if ($clc_id != '') {
            $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
            $contable->conectar();
            $contable->setSql("SELECT clc_concepto
                FROM scp_Clc
                WHERE gpo_id = ? AND ur_id = ?
                    AND clc_id = ? ");
            $contable->consultar([$gpo_id, $ur_id, $clc_id]);
            $contable->desconectar();
            $concepto = $contable->extraer();
            if ($contable->getNumeroRegistros() > 0) {
                $concepto = preg_replace(
                    "[\n|\r|\n\r]",
                    " ",
                    $concepto['clc_concepto']
                );
                $this->mixScript('
                    $("#inputConceptoClc").val( "' . $concepto . '" );
                ');
            }
        }
    }

    private function buscarBdo()
    {

        $gpo_id = (isset($this->prm->gur) && $this->prm->gur != '' ? substr($this->prm->gur, 0, 1) : $this->gpo_id);
        $ur_id = (isset($this->prm->gur) && $this->prm->gur != '' ? substr($this->prm->gur, 1, 2) : $this->ur_id);
        $bdo_id = (isset($this->prm->bdo_id) && $this->prm->bdo_id != '' ? $this->prm->bdo_id : '');
        if ($bdo_id != '') {
            $contable = new AccesoDatos(); //variable de konexion para los saldos iniciales
            $contable->conectar();
            $contable->setSql("SELECT CONCAT( rfc_id, '-', bdo_referencia ) AS concepto
                FROM cat_BancoDocumentos
                WHERE gpo_id = ? AND ur_id = ? AND bdo_id = ? ");
            $contable->consultar([$gpo_id, $ur_id, $bdo_id]);
            $contable->desconectar();
            $concepto = $contable->extraer();
            if ($contable->getNumeroRegistros() > 0) {
                $concepto = preg_replace("[\n|\r|\n\r]", " ", $concepto['concepto']);
                $this->mixScript('
                    $("#inputConceptoBdo").val( "' . $concepto . '" );
                ');
            }
        }
    }

    private function tipoRecurso()
    {
        $this->setContenedor('con_cvefin');

        $cveFin = new stdClass();
        $cveFin->gpo_id = $this->gpo_id;
        $cveFin->ur_id = $this->ur_id;
        $this->ue_id ? $cveFin->ue_id = $this->ue_id : '';
        $cveFin->filtro = 'OrigenRecurso';
        $cveFin->federal = ((isset($this->prm->federal) && $this->prm->federal == 1) ? true : false);
        $cveFin->estatal = ((isset($this->prm->estatal) && $this->prm->estatal == 1) ? true : false);
        $tipoRecurso = (($cveFin->federal == 1 || $cveFin->estatal == 1) ? 1 : 2);
        $cveFin->tipoReporte = (isset($this->prm->tipoReporte) ? $this->prm->tipoReporte : 1);
        if ($tipoRecurso == 1)
            $this->mixHtml(ClasificacionFinanciamiento::creaComboCveFin($cveFin));
        else $this->mixHtml('<div></div>');
    }

    public function filtros()
    {

        $comboMesDesde = new stdClass();
        $comboMesDesde->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable";
        $comboMesDesde->id = "comboMesDesde";
        $comboMesDesde->label = "Desde";
        $comboMesDesde->comoAtr = 'msc_nombre';
        $comboMesDesde->selecciona = 1;
        $comboMesDesde->width = '150px';
        $comboMesDesde->change = '
            if($(this).val() ==""){
                notify( "Favor seleccionar un mes válido", "warning");
                return false;
            }else{
                comunica("' . $this->pantalla . '", {
                    metodo: "mesHasta",
                    mes: $(this).val()
                });
            }
        ';

        $comboMesHasta = new stdClass();
        $comboMesHasta->sql = "SELECT msc_id, msc_nombre FROM cat_MesContable;";
        $comboMesHasta->id = "comboMesHasta";
        $comboMesHasta->width = "150px";
        $comboMesHasta->label = "Hasta";
        $comboMesHasta->selecciona = date("m");

        $comboClaveFinanciamiento = new stdClass();
        $comboClaveFinanciamiento->sql = "SELECT ecf_id, cfi_nombre, cfi_clave
            FROM poa_OrigenRecurso
                INNER JOIN enc_ClaveFinanciamiento ecf USING( ecf_id )
                INNER JOIN sinpres.cat_ClaveFinanciamiento cf USING( fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio )
                INNER JOIN sinpres.cat_ClasificacionSuborigen c USING( fte_id, sft_id, ori_id, sor_id, cso_id )
            WHERE gpo_id = " . $this->gpo_id . " AND ur_id = " . $this->ur_id . "
            GROUP BY fte_id, sft_id, ori_id, sor_id, cso_id, cfi_id, cfi_anio
        ";

        $comboClaveFinanciamiento->id = "comboClaveFinanciamiento";
        $comboClaveFinanciamiento->width = "150px";
        $comboClaveFinanciamiento->camposText = "cfi_clave,cfi_nombre";
        $comboClaveFinanciamiento->camposVal = "ecf_id";
        $comboClaveFinanciamiento->soloText = true;
        $comboClaveFinanciamiento->label = "Clave Financiamiento";

        $ue = new stdClass();
        $ue->label = "";

        $html = '
            <div id="myModalBalanza" class="modal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header" style="background: #9d2449">
                            <h4 class="modal-title" id="myModalLabel" style="color:white;">Generar Balanza de Comprobación</h4>
                            <button type="button" class="cerrar btn-close" data-bs-dismiss="myModalLabel" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="" class="" id="AddMod">
                                <div class="col-md-12 col-xs-12 mb-4">
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <legend>Unidad Ejecutora ...</legend>' . ClasificacionAdministrativa::clasAdminUe($ue) . '
                                        </fieldset>' .
            (($_SESSION[_S]['nvj_id'] >= 3) ? // Sólo los usuarios de nivel jerárquico 3 para arriba verán ésta opción
                '<fieldset class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>En Miles de Pesos</legend>
                                            <input name="checkMiles" id="checkMiles" type="checkbox" value=1> Importes en Miles de Pesos </input>
                                        </fieldset>
                                        <fieldset class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>Tipo de Reporte</legend>
                                            <select name="tipoReporte" id="tipoReporte" class="dropdown form-control dropdown-toggle" data-bs-toggle="dropdown">
                                                <option value="1">Sólo la UR Actual</option>
                                                <option value="2">Todas las UR de la Carga de Trabajo</option>
                                                ' . (($_SESSION[_S]['nvj_id'] >= 5)
                    ? '<option value="3">Limitar qué incluir y qué no</option>' : "") . '
                                                </select>
                                        </fieldset>' : '') .
            '</div>
                                </div>' .
            (($_SESSION[_S]['nvj_id'] >= 5) ? // Sólo los usuarios de nivel jerárquico 5 para arriba verán ésta opción
                '<div class="col-md-12 col-xs-12 mb-4">
                                    <div class="row">
                                        <fieldset class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>Grupos a incluir</legend>
                                            <input name="inputGrupos" id="inputGrupos" type="text" value="1,2,3,7,8" class="form-control paso" disabled />
                                        </fieldset>
                                        <fieldset class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>URs a excluir</legend>
                                            <input name="inputExcluir" id="inputExcluir" type="text" value="" class="form-control paso" disabled />
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <legend>Encabezado Personalizado</legend>
                                            <input name="inputNombreEspecial" id="inputNombreEspecial" type="text" value="" placeholder="Ejem: SECTOR CENTRAL" class="form-control paso" disabled />
                                        </fieldset>
                                    </div>
                                </div>' : '') .
            '<div class="col-md-12 col-xs-12 mb-4">
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <div id="periodo" class="row">
                                                <legend>Periodo</legend>
                                                <fieldset class="col-md-6 col-xs-6" style="display:inline">
                                                    ' . $this->ponCombo($comboMesDesde) . '
                                                </fieldset>
                                                <fieldset id="mhasta" class="col-md-6 col-xs-6" style="display:inline">
                                                    ' . $this->ponCombo($comboMesHasta) . '
                                                </fieldset>
                                            </div>
                                        </fieldset>
                                        <fieldset class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>Cuentas sin Movimientos</legend>
                                            <input id="checkMovimiento" name="checkMovimiento" type="checkbox" value=0 checked>Incluir aún si no tuvo Movimientos</input>
                                            
                                            <fieldset id="chkOtros" class="col-md-6 col-xs-6 mt-4" style="padding-right: 1px !important;">
                                                <legend>Otros</legend>
                                                ' . ($this->gpo_id == 1 && $this->ur_id == 16 ? '
                                                <input id="checkGur" name="checkGur" type="checkbox" value=0>UR</input>' : '') . '
                                                <input id="checkClc" name="checkClc" type="checkbox" value=0>CLC</input>
                                                <input id="checkBdo" name="checkBdo" type="checkbox" value=0>Documento</input>
                                            </fieldset>
                                            
                                        </fieldset>
                                        <fieldset name="chkHistorico" id="chkHistorico" class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>Ejercicios Anteriores</legend>
                                            <input id="check2023" class="aniosanteriores" name="check2023" type="checkbox" value=0>2023</input>
                                            <input id="check2022" class="aniosanteriores" name="check2022" type="checkbox" value=0>2022</input>
                                            <input id="check2021" class="aniosanteriores" name="check2021" type="checkbox" value=0>2021</input>
                                            <input id="check2020" class="aniosanteriores" name="check2020" type="checkbox" value=0>2020</input>
                                            <input id="check2019" class="aniosanteriores" name="check2019" type="checkbox" value=0>2019</input>
                                            <input id="check2018" class="aniosanteriores" name="check2018" type="checkbox" value=0>2018</input>                                            
                                            <fieldset >
                                                <input id="check2017" class="aniosanteriores" name="check2017" type="checkbox" value=0>2017</input>
                                                <input id="check2016" class="aniosanteriores" name="check2016" type="checkbox" value=0>2016</input>
                                                <input id="check2015"  class="aniosanteriores" name="check2015" type="checkbox" value=0>2015</input>
                                            </fieldset>
                                        </fieldset>
                                        
                                        <fieldset id="chkAgrupadoUr" style="display:none" class="col-md-3 col-xs-3" style="padding-right: 1px !important;">
                                            <legend>Agrupado por UR</legend>
                                            <input id="checkAgrupadoUr" name="checkAgrupadoUr" type="checkbox" value=0>Separar cada UR</input>
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="col-md-12 col-xs-12 mb-4">
                                    <div class="row" id="opcionCuenta" >
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <legend>Cuenta Desde</legend>
                                            <div class="row">
                                                <fieldset class="col-md-4 col-xs-4" >
                                                    <input  id="inputCuentaDesde" name="inputCuentaDesde" type="text" placeholder="Cuenta" maxlength=25 class="form-control F1Catalogo" tabindex="100">
                                                </fieldset>
                                                <fieldset class="col-md-8 col-xs-8" >
                                                    <input id="inputNombreDesde" name="inputNombreDesde" type="text" class="form-control" disabled>
                                                </fieldset>
                                            </div>
                                        </fieldset>
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <legend>Cuenta Hasta</legend>
                                            <div class="row">
                                                <fieldset class="col-md-4 col-xs-4">
                                                    <input id="inputCuentaHasta" name="inputCuentaHasta" type="text" placeholder="Cuenta" maxlength=25 class="form-control F1Catalogo" tabindex="100">
                                                </fieldset>
                                                <fieldset class="col-md-8 col-xs-8">
                                                    <input id="inputNombreHasta" type="text" class="form-control" disabled>
                                                </fieldset>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <div class="row" id="opcionGur" style="display:none">
                                    <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                                        <legend>UR</legend>
                                        <input id="inputGur" name="inputGur" type="text" placeholder="UR" maxlength=5 class="form-control" tabindex="100">
                                    </fieldset>
                                    <div class="col-xs-4">
                                        <legend></legend>
                                        <input id="inputConceptoGur" type="text" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="row" id="opcionClc" style="display:none">
                                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                                        <legend>CLC</legend>
                                        <input id="inputClc" name="inputClc" type="text" placeholder="CLC" maxlength=5 class="form-control" tabindex="100">
                                    </fieldset>
                                    <div class="col-xs-4">
                                        <legend></legend>
                                        <input id="inputConceptoClc" type="text" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="row" id="opcionBdo" style="display:none">
                                    <fieldset class="col-xs-2" style="padding-right: 1px !important;">
                                        <legend>Documento</legend>
                                        <input id="inputBdo" name="inputBdo" type="text" placeholder="BDO" maxlength=5 class="form-control" tabindex="100">
                                    </fieldset>
                                    <div class="col-xs-4">
                                        <legend></legend>
                                        <input id="inputConceptoBdo" type="text" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12 col-xs-12 mb-4">
                                    <div class="row">
                                        <fieldset class="col-md-6 col-xs-6" style="padding-right: 1px !important;">
                                            <legend>Niveles a Incluir</legend>
                                            <input id="checkNivel1" name="checkNivel1" type="checkbox" value=1 readonly="readonly" checked onclick="javascript: return false;">1</input>
                                            <input id="checkNivel2" name="checkNivel2" type="checkbox" value=1>2</input>
                                            <input id="checkNivel3" name="checkNivel3" type="checkbox" value=1>3</input>
                                            <input id="checkNivel4" name="checkNivel4" type="checkbox" value=1>4</input>
                                            <div id="chkNivel5" style="display:inline"><input id="checkNivel5" name="checkNivel5" type="checkbox" value=1>5</input></div>
                                            <div id="chkNivel6" style="display:inline"><input id="checkNivel6" name="checkNivel6" type="checkbox" value=1>6</input></div>
                                            ' . ($_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] == '116' ? '
                                            <div id="chkNivel7" style="display:inline"><input id="checkNivel7" name="checkNivel6" type="checkbox" value=1>7</input></div> ' : '') . '
                                            <div id="chkNivelR" style="display:inline"><input id="checkNivelR" name="checkNivelR" type="checkbox" value=1>Registro</input></div>
                                        </fieldset>
                                        <fieldset id="tRec" class="col-md-4 col-xs-4" style="padding-right: 1px !important;" style="display:inline">
                                            <legend>Tipo de Recurso</legend>
                                            <div class="input-group input-group-sm">
                                            <input id="checkFederal" name="checkFederal" type="checkbox" value=1>Federal</input>
                                            <input id="checkEstatal" name="checkEstatal" type="checkbox" value=1>Estatal</input>
                                            </div>

                                            <div class="input-group input-group-sm">
                                                <input id="cveFin_id" lastSearch="" name="cveFin_id" type="text"  placeholder="Clave de Financamiento" maxlength=12 class="form-control F1Catalogo" tabindex="10" disabled>
                                                <span class="input-group-btn">
                                                    <button id="mostrarClaveFin" type="submit" class="btn btn-default"><i class="fa-solid fa-list-ul"></i></button>
                                                </span>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button id="imprimirBalanza" class="btn btn-outline-danger me-1" data-style="slide-left"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                            <button id="excelBalanza" class="btn btn-outline-success me-1" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>EXCEL</button>
                        </div>
                    </div>
                </div>
            </div>
        ';
        $this->setContenedor("paraModales");
        $this->mixHtml($html);

        $this->mixStyle('
            #cogs table tr:hover{
                background-color:#CCFF99 !important;
            }
            .modal-open { overflow: scroll; }
        ');

        $this->mixScript('
            $("#myModalBalanza").modal();
            $("#myModalBalanza").show();
            
            v = $("#titulo_reporte");
            v.append($("#btnimprime"))
                .append($("#btnexporta"))
                .append($("#btntabla"))
                .append($("<hr/>") );
            
            $("#checkFormato2015").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#chkNivel5").css("display", "none" );
                    $("#chkNivel6").css("display", "none" );
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "none" );
                    ' : '') . '
                    $("#chkOtros").css("display", "none" );
                    $("#chkAgrupadoUr").css("display", "none" );
                    $("#chkHistorico").css("display", "none" );
                } else {
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    $("#chkHistorico").css("display", "inline" );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                        $("#chkOtros").css("display", "none" );
                        $("#chkNivelR").css("display", "none" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                        $("#chkOtros").css("display", "inline" );
                        $("#chkNivelR").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    $("#chkHistorico").css("display", "inline" );
                    ') . '
                }
            });
            //2022
            $("#check2022").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            //2021
            $("#check2021").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            $("#check2020").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            $("#check2019").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            $("#check2018").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);

                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            $("#check2017").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    $("#check2015").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            $("#check2016").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    
                    $("#check2017").prop("checked", false);
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    $("#chkNivel8").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });
            //2015
            $("#check2015").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#check2022").prop("checked", false);
                    $("#check2021").prop("checked", false);
                    $("#check2020").prop("checked", false);
                    $("#check2019").prop("checked", false);
                    $("#check2018").prop("checked", false);
                    $("#check2017").prop("checked", false);
                    $("#check2016").prop("checked", false);
                    
                    $("#chkNivel5").css("display", "inline" );
                    $("#chkNivel6").css("display", "inline" );
                    ' . ($this->gpo_id . $this->ur_id == '116' ? '
                    $("#chkNivel7").css("display", "inline" );
                    ' : '') . '
                    $("#tRec").css("display", "none" );
                    $("#con_cvefin").css("display", "none" );
                    $("#chkFormato2015").css("display", "none" );
                    $("#chkOtros").css("display", "none" );
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    console.log( $("#tipoReporte").val() );
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkAgrupadoUr").css("display", "inline" );
                    }else{
                        $("#chkAgrupadoUr").css("display", "none" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "none" );
                    ') . '
                } else {
                    $("#tRec").css("display", "inline" );
                    $("#con_cvefin").css("display", "inline" );
                    if( $("#checkAgrupadoUr").is(":checked") ) {
                        $("#chkFormato2015").css("display", "none" );
                    } else {
                        $("#chkFormato2015").css("display", "inline" );
                    }
                    ' . (($_SESSION[_S]['nvj_id'] > 2) ? '
                    if( $("#tipoReporte").val() != 1 ){
                        $("#chkOtros").css("display", "none" );
                    }else{
                        $("#chkOtros").css("display", "inline" );
                    }
                    ' : '
                    $("#chkOtros").css("display", "inline" );
                    ') . '
                }
            });

            
            $("#checkAgrupadoUr").change(function(e){
                if( $(this).is(":checked") ) {
                    $("#chkFormato2015").css("display", "none" );
                }else{
                    $("#chkFormato2015").css("display", "inline" );
                }
            });
            
            $("#checkNivelGur").change(function(e){
                if( $(this).is(":checked") ){
                }
            });
            
            $("#checkGur").change(function(e){
                if( $(this).is(":checked") ){
                    $("#chkFormato2015").css("display", "none" );
                    $("#opcionCuenta").css("display", "none" );
                    $("#opcionGur").css("display", "inline" );
                    $("#opcionClc").css("display", "none" );
                    $("#opcionBdo").css("display", "none" );
                    $("#checkBdo").prop("checked", false);
                    $("#checkCuenta").prop("checked", false);
                    $("#tRec").css("display", "none" );
                    $("#chkHistorico").css("display", "none" );
                }else{
                    $("#chkFormato2015").css("display", "inline" );
                    $("#opcionCuenta").css("display", "inline" );
                    $("#opcionGur").css("display", "none" );
                    $("#tRec").css("display", "inline" );
                    $("#chkHistorico").css("display", "inline" );
                }
            });
            
            $("#checkClc").change(function(e){
                if( $(this).is(":checked") ){
                    $("#chkFormato2015").css("display", "none" );
                    $("#opcionCuenta").css("display", "none" );
                    $("#opcionGur").css("display", "none" );
                    $("#opcionClc").css("display", "inline" );
                    $("#opcionBdo").css("display", "none" );
                    $("#checkBdo").prop("checked", false);
                    $("#checkCuenta").prop("checked", false);
                    $("#tRec").css("display", "none" );
                    $("#chkHistorico").css("display", "none" );
                }else{
                    $("#chkFormato2015").css("display", "inline" );
                    $("#opcionCuenta").css("display", "inline" );
                    $("#opcionClc").css("display", "none" );
                    $("#tRec").css("display", "inline" );
                    $("#chkHistorico").css("display", "inline" );
                }
            });
            
            $("#inputClc").blur(function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "buscarClc",
                    gur: $("#inputGur").val(),
                    clc_id: $("#inputClc").val()
                });
            });
            
            $("#inputBdo").blur(function(e){
                comunica("' . $this->pantalla . '", {
                    metodo: "buscarBdo",
                    gur: $("#inputGur").val(),
                    bdo_id: $("#inputBdo").val()
                });
            });
            
            $("#checkBdo").change(function(e){
                if( $(this).is(":checked") ){
                    $("#chkFormato2015").css("display", "none" );
                    $("#opcionCuenta").css("display", "none" );
                    $("#opcionGur").css("display", "none" );
                    $("#opcionClc").css("display", "none" );
                    $("#opcionBdo").css("display", "inline" );
                    $("#checkClc").prop("checked", false);
                    $("#checkCuenta").prop("checked", false);
                    $("#tRec").css("display", "none" );
                    $("#chkHistorico").css("display", "none" );
                }else{
                    $("#chkFormato2015").css("display", "inline" );
                    $("#opcionCuenta").css("display", "inline" );
                    $("#opcionBdo").css("display", "none" );
                    $("#tRec").css("display", "inline" );
                    $("#chkHistorico").css("display", "inline" );
                }
            });
            
            $("#tipoReporte").change(function(e){
                if( $("#tipoReporte").val() != 1 ){
                    $("#chkAgrupadoUr").css("display", "inline" );
                    $("#chkOtros").css("display", "none" );
                    $("#chkNivelR").css("display", "none" );
                }else{
                    $("#chkAgrupadoUr").css("display", "none" );
                    $("#chkOtros").css("display", "inline" );
                    $("#chkNivelR").css("display", "inline" );
                }
            });
            
            $("#tipoReporte").click(function(){
                var nOpcion = $(this).val();
                if( nOpcion == 3 ){
                    $(".paso").removeAttr("disabled");
                }else{
                    $(".paso").attr("disabled", "disabled");
                }
            });
            
            $("#cveFin_id").focus( function(e) {
                cveFinID= "cveFin_id";
                cveFinNom = "";
            });

            // F1 muestra el catálogo
            $("#cveFin_id").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlgCveFin.dialog("open");
                }
            });
                $("#mostrarClaveFin").click( function(e) {
                                    cveFinID= "cveFin_id";
                cveFinNom = "";
                e.preventDefault();
                dlgCveFin.dialog("open");
            });
        ');

        $this->mixScript('
            $("#myModalBalanza").on("shown.bs.modal", function () {
                setTimeout( function() { $( "#inputCuentaDesde" ).focus().select() }, 100 );
            });

            $(".cerrar").click(function(){ $("#myModalBalanza").remove(); });
            
            $("#inputCuentaDesde").focus( function(e) {
                mayorCuenta = "inputCuentaDesde";
                mayorNombre = "inputNombreDesde";
            });
            $("#inputCuentaHasta").focus( function(e) {
                mayorCuenta = "inputCuentaHasta";
                mayorNombre = "inputNombreHasta";
            });
            // La cuenta final solo puede ser mayor o igual a la inicial
            $("#inputCuentaDesde").blur( function(e) {
                if ( $("#inputCuentaHasta").val() < $("#inputCuentaDesde").val() ) { $("#inputCuentaHasta").val( $("#inputCuentaDesde").val() ); }
                // Actualizamos el nombre
                var regexSubCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}$/;
                var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;
                var cuentadesde =$("#inputCuentaDesde").val().trim();
                //var cuentahasta =$("#inputCuentaHasta").val().trim();
                if( regexSubCuenta.test(cuentadesde)){
                    try {
                        comunica("' . $this->pantalla . '",{ 
                            metodo: "ValidaCuentaContable",
                            cta_id: $("#inputCuentaDesde").val(),
                            campo:"inputNombreDesde"
                        });
                    } catch(err) { /* nada */ }
                }
                if(regexPlanCuenta.test(cuentadesde)) {
                        comunica("' . $this->pantalla . '",{ metodo: "ValidaCuenta",
                        cta_id: $("#inputCuentaDesde").val(),
                        campo:"inputNombreDesde"
                    });
                }
            });
            $("#inputCuentaHasta").blur( function(e) {
                if ( $("#inputCuentaHasta").val() < $("#inputCuentaDesde").val() ) { $("#inputCuentaHasta").val( $("#inputCuentaDesde").val() ); }
                // Actualizamos el nombre
                    var regexSubCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}$/;
                    var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;
                    var cuentahasta =$("#inputCuentaHasta").val().trim();
                if( regexSubCuenta.test(cuentahasta)){
                    try {
                        comunica("' . $this->pantalla . '",{ 
                            metodo: "ValidaCuentaContable",
                            cta_id: $("#inputCuentaHasta").val(),
                            campo:"inputNombreHasta"
                        });
                    } catch(err) { /* nada */ }
                }
                if (regexPlanCuenta.test(cuentahasta)) {
                        comunica("' . $this->pantalla . '",{ metodo: "ValidaCuenta",
                        cta_id: $("#inputCuentaHasta").val(),
                        campo:"inputNombreHasta"
                    });
                }

            });
            // F1 muestra el catálogo
            $("#inputCuentaDesde").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                    e.stopPropagation();
                }
            });
            $("#inputCuentaHasta").keydown( function(e) {
                var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                if ( key == 112 ) {
                    e.preventDefault();
                    dlg.dialog("open");
                    e.stopPropagation();
                }
            });
            
            $("#excelBalanza").click( function(e) {
                // Si piden un encabezado especial ...
                if( $("#tipoReporte").val() == 3 && $("#inputNombreEspecial").val().trim() != 0 ) {
                    nombreEspecial = $("#inputNombreEspecial").val();
                }else{
                    nombreEspecial = "";
                }
                var cuentadesde =$("#inputCuentaDesde").val().trim();
                var cuentahasta =$("#inputCuentaHasta").val().trim();
                var regexSubCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}$/;
                var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;
                var sienviar = 1;
                var nivelRegistro = 0;
                if (cuentadesde != "" ){
                        if( regexSubCuenta.test(cuentadesde) && regexSubCuenta.test(cuentahasta) ){
                            sienviar=1;
                    }else if( regexPlanCuenta.test(cuentadesde) && regexPlanCuenta.test(cuentahasta) ){
                        $("#checkNivelR").attr("checked", true);
                        sienviar=1;
                        nivelRegistro=1;
                    }
                    else {
                        sienviar=0;
                    }
                }
                if( $(".aniosanteriores").is(":checked") && nivelRegistro == 1 ){
                    notify( "Favor de escribir la cuenta a Nivel de Mayor para años anteriores", "warning");
                    return false;
                }
                    if (sienviar == 1){

                        if($("#cveFin_id").val()=="" ){
                            $("#cveFin_id").attr("ecf","");
                        }
                        var form = $("<form>")
                                .attr("action", "rep/excel/scg/consulta/exp_Balanza.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                                .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "4" ))
                                .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                                .append($("<input>").attr("type", "hidden").attr("name", "nombreEspecial").attr("value", nombreEspecial ))

                                .append($("<input>").attr("type", "hidden").attr("name", "tipoReporte" ).attr("value", $("#tipoReporte").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputGrupos" ).attr("value", $("#inputGrupos").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputExcluir" ).attr("value", $("#inputExcluir").val() ))

                                .append($("<input>").attr("type", "hidden").attr("name", "checkMiles" ).attr("value", ( $("#checkMiles").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkMovimiento" ).attr("value", ( $("#checkMovimiento").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkAgrupadoUr" ).attr("value", ( $("#checkAgrupadoUr").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkFormato2015").attr("value", ( $("#checkFormato2015").is(":checked") ? 1 : 0 ) ))

                                .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#comboMesDesde").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "comboMesHasta").attr("value", $("#comboMesHasta").val() ))

                                .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaDesde").attr("value", $("#inputCuentaDesde").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaHasta").attr("value", $("#inputCuentaHasta").val() ))

                                .append($("<input>").attr("type", "hidden").attr("name", "checkGur" ).attr("value", ( $("#checkGur").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))

                                .append($("<input>").attr("type", "hidden").attr("name", "checkClc" ).attr("value", ( $("#checkClc").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputClc").attr("value", $("#inputClc").val() ))

                                .append($("<input>").attr("type", "hidden").attr("name", "checkBdo" ).attr("value", ( $("#checkBdo").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "inputBdo").attr("value", $("#inputBdo").val() ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2022").attr("value", ( $("#check2022").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2021").attr("value", ( $("#check2021").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2019").attr("value", ( $("#check2019").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2020").attr("value", ( $("#check2020").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2018").attr("value", ( $("#check2018").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2017").attr("value", ( $("#check2017").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2016").attr("value", ( $("#check2016").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2015").attr("value", ( $("#check2015").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2014").attr("value", ( $("#check2014").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2013").attr("value", ( $("#check2013").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "check2012").attr("value", ( $("#check2012").prop("checked" ) ? 1 : 0 ) ))

                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel1").attr("value", ( $("#checkNivel1").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel2").attr("value", ( $("#checkNivel2").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel3").attr("value", ( $("#checkNivel3").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel4").attr("value", ( $("#checkNivel4").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel5").attr("value", ( $("#checkNivel5").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel6").attr("value", ( $("#checkNivel6").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivel7").attr("value", ( $("#checkNivel7").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivelR").attr("value", ( $("#checkNivelR").prop("checked") ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkNivelGur").attr("value", ( $("#checkNivelGur").prop("checked") ? 1 : 0 ) ))

                                .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin_id").attr("ecf") ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkFederal" ).attr("value", ( $("#checkFederal").prop("checked" ) ? 1 : 0 ) ))
                                .append($("<input>").attr("type", "hidden").attr("name", "checkEstatal" ).attr("value", ( $("#checkEstatal").prop("checked" ) ? 1 : 0 ) ))

                            $("body").append(form);
                            form.submit();
                    } else{
                        notify( "Favor de escribir la cuenta a Nivel de Mayor o Nivel de Registro,solo números separados por puntos", "warning");
                    }
            });
            
            $("#imprimirBalanza").click( function(e) {
                console.log("imprimiendo balanza");
                // Si piden un encabezado especial ...
                if ( $("#tipoReporte").val() == 3 && $("#inputNombreEspecial").val().trim() != 0 ) {
                    nombreEspecial = $("#inputNombreEspecial").val();
                } else {
                    nombreEspecial = "";
                }
                var cuentadesde =$("#inputCuentaDesde").val().trim();
                var cuentahasta =$("#inputCuentaHasta").val().trim();
                var regexSubCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}$/;
                var regexPlanCuenta = /^\d{1}\.\d{1}\.\d{1}\.\d{1,2}\.\d{1,2}\.\d{3}\.(?:[a-zA-Z0-9]){4,9}$/;
                var sienviar = 1;
                var nivelRegistro = 0;
                if (cuentadesde != "" ){
                        if( regexSubCuenta.test(cuentadesde) && regexSubCuenta.test(cuentahasta) ){
                            sienviar=1;
                    }else if( regexPlanCuenta.test(cuentadesde) && regexPlanCuenta.test(cuentahasta) ){
                        
                        $("#checkNivelR").attr("checked", true);
                        nivelRegistro=1;
                        sienviar=1;
                    }
                    else {
                        sienviar=0;
                    }
                }

                if($("#checkClc").is(":checked") && $("#inputConceptoClc").val() == ""){
                    notify( "La CLC #" + $("#inputClc").val() + " no existe", "warning");
                    return false;
                }

                if($("#checkBdo").is(":checked") && $("#inputConceptoBdo").val() == ""){
                    notify( "No se encuentran movimientos del BDO " + $("#inputBdo").val(), "warning");
                    return false;
                }

                if( $(".aniosanteriores").is(":checked") && nivelRegistro == 1 ){
                    notify( "Favor de escribir la cuenta a Nivel de Mayor para años anteriores", "warning");
                    return false;
                }
                if (sienviar == 1){
                    if($("#cveFin_id").val()=="" ){
                        $("#cveFin_id").attr("ecf","");
                    }
                    var form = $("<form>")
                            .attr("action", "rep/doc/scg/consulta/reporte.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "4" ))
                            .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "3" ))

                            .append($("<input>").attr("type", "hidden").attr("name", "nombreEspecial").attr("value", nombreEspecial ))

                            .append($("<input>").attr("type", "hidden").attr("name", "tipoReporte" ).attr("value", $("#tipoReporte").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputGrupos" ).attr("value", $("#inputGrupos").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputExcluir" ).attr("value", $("#inputExcluir").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "checkMiles" ).attr("value", ( $("#checkMiles").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkMovimiento" ).attr("value", ( $("#checkMovimiento").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkAgrupadoUr" ).attr("value", ( $("#checkAgrupadoUr").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkFormato2015").attr("value", ( $("#checkFormato2015").is(":checked") ? 1 : 0 ) ))

                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#comboMesDesde").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "comboMesHasta").attr("value", $("#comboMesHasta").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaDesde").attr("value", $("#inputCuentaDesde").val() ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaHasta").attr("value", $("#inputCuentaHasta").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "checkGur" ).attr("value", ( $("#checkGur").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "checkClc" ).attr("value", ( $("#checkClc").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputClc").attr("value", $("#inputClc").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "checkBdo" ).attr("value", ( $("#checkBdo").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "inputBdo").attr("value", $("#inputBdo").val() ))

                            .append($("<input>").attr("type", "hidden").attr("name", "check2022").attr("value", ( $("#check2022").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2021").attr("value", ( $("#check2021").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2020").attr("value", ( $("#check2020").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2019").attr("value", ( $("#check2019").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2018").attr("value", ( $("#check2018").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2017").attr("value", ( $("#check2017").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2016").attr("value", ( $("#check2016").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2015").attr("value", ( $("#check2015").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2014").attr("value", ( $("#check2014").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2013").attr("value", ( $("#check2013").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "check2012").attr("value", ( $("#check2012").prop("checked" ) ? 1 : 0 ) ))

                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel1").attr("value", ( $("#checkNivel1").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel2").attr("value", ( $("#checkNivel2").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel3").attr("value", ( $("#checkNivel3").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel4").attr("value", ( $("#checkNivel4").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel5").attr("value", ( $("#checkNivel5").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel6").attr("value", ( $("#checkNivel6").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivel7").attr("value", ( $("#checkNivel7").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivelR").attr("value", ( $("#checkNivelR").prop("checked") ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkNivelGur").attr("value", ( $("#checkNivelGur").prop("checked") ? 1 : 0 ) ))

                            .append($("<input>").attr("type", "hidden").attr("name", "cveFin").attr("value", $("#cveFin_id").attr("ecf") ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkFederal" ).attr("value", ( $("#checkFederal").prop("checked" ) ? 1 : 0 ) ))
                            .append($("<input>").attr("type", "hidden").attr("name", "checkEstatal" ).attr("value", ( $("#checkEstatal").prop("checked" ) ? 1 : 0 ) ))

                    $("body").append(form);
                    form.submit();
                } else{
                    notify( "Favor de escribir la cuenta a Nivel de Mayor o Nivel de Registro,solo números separados por puntos", "warning");
                }
            });
            
            // Al cerrar la ventana limpiamos el ID del script, para no saturar el DOM
            $("#myModalBalanza").on("hide.bs.modal", function () {
                console.log("borrado");
                $("#paraModales").html("");
            });

            $("#chkFed").change(function(e){
                    if( $("#chkFed").prop("checked") ) {
                    $("#checkFederal").prop("checked", true);
                    }else{
                    $("#checkFederal").prop("checked", false);
                    }
                    $("#cveFin_id").attr("ecf","");
                    $("#cveFin_id").val("");
                });
                $("#chkEst").change(function(e){
                    if( $("#chkFed").prop("checked") ) {
                    $("#checkEstatal").prop("checked", true);
                    }else{
                    $("#checkEstatal").prop("checked", false);
                    }
                    $("#cveFin_id").attr("ecf","");
                    $("#cveFin_id").val("");
                });

            $("#checkFederal").change(function(e){
                if( $("#checkFederal").prop("checked") ) {
                    $("#chkFed").prop("checked", true);
                    }else{
                    $("#chkFed").prop("checked", false);
                    }
                    $("#cveFin_id").attr("ecf","");
                    $("#cveFin_id").val("");
                });
                $("#checkEstatal").change(function(e){
                    if( $("#checkEstatal").prop("checked") ) {
                    $("#chkEst").prop("checked", true);
                    }else{
                    $("#chkEst").prop("checked", false);
                    }
                    $("#cveFin_id").attr("ecf","");
                    $("#cveFin_id").val("");
                });

                $("#inputCuentaDesde").blur( function(e) {
                    // Mínimo 5 dígitos
                    $("#inputCuentaDesde").val( preparaCuentaContable( $("#inputCuentaDesde").val(), 1 ) );
                });

                $("#inputCuentaHasta").blur( function(e) {
                    // Mínimo 5 dígitos
                    $("#inputCuentaHasta").val( preparaCuentaContable( $("#inputCuentaHasta").val(), 1 ) );
                });
        ');
    }

    private function ValidaCuenta()
    {

        $arrayCuenta = explode('.', $this->prm->cta_id);
        $campo = $this->prm->campo;

        $cgm = $arrayCuenta[0] . $arrayCuenta[1] . $arrayCuenta[2] . $arrayCuenta[3] . $arrayCuenta[4];

        if (strlen($cgm) >= 5) {

            $ger = substr($cgm, 0, 1);
            $cgp = substr($cgm, 1, 1);
            $rub = substr($cgm, 2, 1);
            $cgcu = substr($cgm, 3, 1);
            $cgsc = substr($cgm, 4);

            if (isset($arrayCuenta[5])) $cgc = $arrayCuenta[5];

            if (isset($arrayCuenta[6])) {
                $cgq = $arrayCuenta[6];
                if (strlen($cgq) > 2) $cta = $cgq;
            }

            if (isset($arrayCuenta[7])) $cta = $arrayCuenta[7];

            // Todos los elementos DEBEN ser números, si no truena el SQL
            if (
                !is_numeric($ger) || !is_numeric($cgp) || !is_numeric($rub) || !is_numeric($cgcu)
                || !is_numeric($cgsc)
            ) {
                $this->mixScript('
                    notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
                ');
                return false;
            }

            $sql = '';

            if (strlen($cgq . $cta) == 6) $cta = $cgq . $cta;

            $params = [];

            switch (true){
                case strlen($cta) >= 4:
                    $ue = getSession('clasAdm-ue');
                    $params = [
                        getSession('clasAdm-gpo'), getSession('clasAdm-ur'),
                        $ger, $cgp, $rub, $cgcu, $cgsc, $cgc, $cta
                    ];
                    $sql = '
                    select cta_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgc_id, cta_id, cta_ue_id
                    from cg_PlanCuentas
                    where cta_gpo_id = ? AND cta_ur_id = ?
                        AND ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?
                        AND cgc_id = ? AND cta_id = ? ' . ($ue ? ' AND cta_ue_id = ? ': '') . '';
                    if ($ue) $params[] = $ue;
                    break;
                case strlen($cgq) == 2:
                    $params = [ $ger, $cgp, $rub, $cgcu, $cgsc, $cgc, $cgq];
                    $sql = 'SELECT cgq_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgq_id
                    from cg_CuentaQuinto
                    where ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ?
                        AND cgc_id = ?
                        AND cgq_id = ? ';
                    break;
                case strlen($cgc) == 3:
                    $params = [ $ger, $cgp, $rub, $cgcu, $cgsc, $cgc];
                    $sql = 'SELECT  cgc_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id, cgc_id
                    FROM cg_CuentaCuarto
                    WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? AND cgc_id = ?';
                    break;
                case strlen($cgm) >= 5:
                    $params = [ $ger, $cgp, $rub, $cgcu, $cgsc];
                    $sql = '
                    select cgsc_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id
                    from cat_CgSubcuenta
                    where ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? ';
                    break;
            }

            if ($sql != '') {
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->desconectar();

                if (!($row = $this->extraer())) {
                    $this->mixScript('$("#' . $campo . '").val("' . $row['cuenta'] . '");');
                } else {
                    $this->mixScript('$("#' . $campo . '").val("' . $row['cuenta'] . '");');
                }
            } else {
                $this->mixScript('$("#' . $campo . '").val("");');
            }
        }
    }

    private function ValidaCuentaContable()
    {

        $arrayCuenta = explode('.', $this->prm->cta_id);
        $campo = $this->prm->campo;
        $cgm = $arrayCuenta[0] . $arrayCuenta[1] . $arrayCuenta[2] . $arrayCuenta[3] . $arrayCuenta[4];

        if (strlen($cgm) >= 4) {

            $ger = substr($cgm, 0, 1);
            $cgp = substr($cgm, 1, 1);
            $rub = substr($cgm, 2, 1);
            $cgcu = substr($cgm, 3, 1);
            $cgsc = substr($cgm, 4);
            
            // Todos los elementos DEBEN ser números, si no truena el SQL
            if (
                !is_numeric($ger) || !is_numeric($cgp) || !is_numeric($rub) || !is_numeric($cgcu) || !is_numeric($cgsc)
            ) {
                $this->mixScript('
                    notify("Todos los elementos de la cuenta de mayor DEBEN ser números");
                ');
                    return false;
            }

            $params = [ $ger, $cgp, $rub, $cgcu, $cgsc];
            $sql = '
                SELECT cgsc_nombre AS cuenta,ger_id,cgp_id,rub_id,cgcu_id,cgsc_id
                FROM cat_CgSubcuenta
                WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? ';

            if ($sql != '') {
                $this->conectar();
                $this->setSql($sql);
                $this->consultar($params);
                $this->desconectar();

                if (!($row = $this->extraer())) {
                    $this->mixScript('$("#' . $campo . '").val("' . $row['cuenta'] . '");');
                } else {
                    $this->mixScript('$("#' . $campo . '").val("' . $row['cuenta'] . '");');
                }
            } else {
                $this->mixScript('$("#' . $campo . '").val("");');
            }
        }
    }
}
if (isset($_SESSION[_S]['prm']['C']['4']['3']['1'])) {
    new ControladorBalanza();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
