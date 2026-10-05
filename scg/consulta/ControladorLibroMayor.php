<?php
/*
  Mostramos en pantalla la balanza de comprobación por mes, a primer nivel
  La balanza acumulada, el detalle de cuántos niveles y todo eso, será en opciones de reporte
 */

declare(strict_types=1);
//inicio de sesion y llamada a las librerias
session_start();
require_once dirname(__FILE__) . '/../../lib/opcionConteo.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/scg/consulta/contable.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../lib/MesNombre.php';
require_once dirname(__FILE__) . '/../../lib/redondear.php';
require_once dirname(__FILE__) . '/../../ent/UnidadResponsable.php';

class ControladorLibroMayor extends WebResponse
{

    private bool $lectura;
    private $gpo_id;
    private $ur_id;
    private $ue_id;
    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        // Determinamos si el usuario actual tiene permisos de lectura/escritura en la base de datos
        $this->lectura = isset($_SESSION[_S]['prm']['C']['4']['2']['1']) ? TRUE
            : FALSE;
        $this->gpo_id = (int) $_SESSION[_S]['clasAdm-gpo'];
        $this->ur_id = (int)$_SESSION[_S]['clasAdm-ur'];
        if (isset($_SESSION[_S]['clasAdm-ue']) && $_SESSION[_S]['clasAdm-ue'] != '') {
            $this->ue_id = (int) $_SESSION[_S]['clasAdm-ue'];
        } else {
            $this->ue_id = null;
        }

        switch ($this->metodo) {
            case 'dibujaPantalla':
                $this->dibujaPantalla();
                break;
            case 'muestraDatos':
                $this->muestraDatos();
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
                $this->main();
                break;
        }

        $this->returnJson();
    }

    public function main()
    {
        $html = $this->getTablaLibroMayor();
        $this->ponBarraBotones();
        $this->setContenedor('contenedor');
        $clc = ((isset($this->prm->clc) && $this->prm->clc != "") ? $this->prm->clc : "");
        $this->mixHtml($html);
    }

    public function PonBarraBotones()
    {

        $botones = '
            <div class="col-md-12 col-xs-12 row gap-1">
                
                ' . ($this->gpo_id . $this->ur_id == 116 ? '<fieldset class="col-md-1 col-xs-1" style="padding-right: 1px !important;">
                    <input id="inputGur" placeHolder="UR" type="text" maxlength=3 class="form-control" value="' . (isset($this->prm->gur)
            ? $this->prm->gur : "") . '"/>
                </fieldset>
                ' : '') . '<fieldset class="col-md-1 col-xs-1" style="padding-right: 1px !important;">
                    <input id="inputClc_id" placeHolder="CLC" type="text" maxlength=6 class="form-control" value="' . (isset($this->prm->clc_id)
            ? $this->prm->clc_id : "") . '"/>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                    <input id="inputCuentaMayor" placeholder="Mayor" type="text" maxlength=12  class="form-control F1Catalogo"></input>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                    <input id="inputCuentaSefin" placeholder="Sefin" type="text" maxlength=3 class="form-control"></input>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                    <input id="inputCuentaRegistro" placeholder="Registro" type="text" maxlength=9 class="form-control"></input>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                    <div class="input-group input-group-sm">
                        <input id="ClaveFinan" placeholder="Clave de Financiamiento" type="text" maxlength=9 class="form-control F1Catalogo" disabled></input>
                        <span class="input-group-btn">
                            <button id="mostrarClafinan" type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-list-ul"></i></button>
                        </span>
                    </div>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2" style="padding-right: 1px !important;">
                    <button id="imprimirLibroMayor" class="btn btn-outline-danger ladda-button" data-style="slide-left"><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                    <button id="excelLibroMayor" class="btn btn-outline-success ladda-button" data-style="slide-left"><i class="fa-solid fa-file-excel me-2"></i>Excel</button>
                </fieldset>
            </div>
            <span style="height:6px; display:block"></span>
        ';

        $this->setContenedor("toolsPantalla");
        $this->mixScript('$("#toolsPantalla").removeClass("d-flex");');
        $this->mixHtml($botones);
        // Tabuladores
        $pestana = "";
        $contenedor = "";
        $inicio = "";
        $this->conectar();
        $this->setSql('SELECT msc_id, msc_nombre, msc_alias, ems_id AS estado
            FROM cat_MesContable
                    INNER JOIN cg_MesContable USING( msc_id )
            WHERE gpo_id = ? AND ur_id = ? ');
        $this->consultar([$this->gpo_id, $this->ur_id]);
        $this->desconectar();

        while ($row = $this->extraer()) {
            if ($pestana == '') $inicio = 'class="active"';
            else $inicio = '';

            $pestana = str_replace('ultimo', '', $pestana);
            $pestana = str_replace('lastdiv', '', $pestana);
            $pestana .= '
                <li opcion="' . $row['msc_id'] . '" ' . $inicio . ' ultimo>
                    <div></div><a class="nav-link active" mes="' . $row['msc_id'] . '"> ' . $row['msc_alias'] . ' </a> lastdiv
                </li>
            ';
            $contenedor .= '
                <div  id="contenedor' . $row['msc_id'] . '" style="display:none" class="contenedores"></div>
            ';
        }

        $pestana = str_replace('ultimo', 'class="last"', $pestana);
        $pestana = str_replace('lastdiv', '<div class="last"></div>', $pestana);

        $this->mixHtml('
            <div class="col-md-12 col-xs-12" id="page-tabs"><ul class="nav">
                ' . $pestana . '
                </ul>
            </div>
        ');
         $this->mixScript('
            $.getScript( "js/scg/fields-formater.js", function(data, textStatus, jqxhr){ });
        '); 
        $this->mixScript('
                $("nav").hide();
                $(".modulos").hide();
                ' .
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
                $(document).on("click", "#sefipSidebar li", function (e){
                    e.preventDefault();
                    $("nav").show();
                    $(".modulos").show();
                    $("#sticky-anchor").hide();
                    $("#sticky").hide();
                    ' .
            (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide();'
                : '') . '
                    $("#mainContainer").css( "margin-top","40px" );
                    // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                    $("div[aria-describedby=cargaDatosMayor]").html("");
                    $("div[aria-describedby=cargaDatosCgCveFin]").html("");
                    $("#ayuda").addClass("gris");
                    $("#tip").addClass("gris");
                    $("#contenedor").html("");
                    //$("nav").menusinpres("clickMenu");
                    $(document).off("click", "#sefipSidebar li" );
                    return false;
                });
                
                // F1 muestra el catálogo */
                $("#inputCuentaMayor").keydown( function(e) {
                    var key = e.charCode ? e.charCode : e.keyCode ? e.keyCode : 0;
                    if ( key == 112 ) {
                        e.preventDefault();
                        mayorCuenta = "inputCuentaMayor";
                        mayorNombre = "";
                        dlg.dialog("open");
                        e.stopPropagation();
                    }
                });
                
                $("#excelLibroMayor").click( function(e) {
                    if( $("#inputCuentaMayor").val().length > 8 && $("#inputCuentaMayor").val().length<=12){
                        var arrCta ;
                        cta = $("#inputCuentaMayor").val();
                        var nNiveles;
                        arrCta = cta.split( "." );
                                  // Checamos cuántos niveles tiene
                        nNiveles = arrCta.length;
                        if(nNiveles == 5){

                            if( $.isNumeric(arrCta[0])==true &&
                                $.isNumeric(arrCta[1])==true &&
                                $.isNumeric(arrCta[2])==true &&
                                $.isNumeric(arrCta[3])== true &&
                                $.isNumeric(arrCta[4])==true){

                                    var form = $("<form>")
                                    .attr("action", "rep/excel/scg/consulta/exp_LibroMayor.php")
                                    .attr("method", "post")
                                    .attr("target", "_blank")
                                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "4" ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))

                                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaMayor").attr("value", $("#inputCuentaMayor").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaSefin").attr("value", $("#inputCuentaSefin").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaRegistro").attr("value", $("#inputCuentaRegistro").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", $("#ClaveFinan").attr("ecf") ))
                                $("body").append(form);
                                form.submit();
                            }else{
                                notify( "Debe escribir la cuenta a nivel de mayor solo numeros separados por puntos", "warning");
                            }
                        }else{
                            notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos", "warning");
                        }

                    }else{
                        notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos  ", "warning");
                    }
                });
                
                $("#imprimirLibroMayor").click( function(e) {
                        if( $("#inputCuentaMayor").val().length > 8 && $("#inputCuentaMayor").val().length<=12){
                        var arrCta ;
                        cta = $("#inputCuentaMayor").val();
                        var nNiveles;
                        arrCta = cta.split( "." );
                                  // Checamos cuántos niveles tiene
                        nNiveles = arrCta.length;
                        if(nNiveles == 5){

                            if( $.isNumeric(arrCta[0])==true &&
                                $.isNumeric(arrCta[1])==true &&
                                $.isNumeric(arrCta[2])==true &&
                                $.isNumeric(arrCta[3])== true &&
                                $.isNumeric(arrCta[4])==true){

                                    var form = $("<form>")
                                    .attr("action", "rep/doc/scg/consulta/reporte.php")
                                    .attr("method", "post")
                                    .attr("target", "_blank")
                                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "4" ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "2" ))

                                    .append($("<input>").attr("type", "hidden").attr("name", "comboMesDesde").attr("value", $("#page-tabs a").parent(".active").children("a").attr("mes") ))

                                    .append($("<input>").attr("type", "hidden").attr("name", "inputGur").attr("value", $("#inputGur").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputClc_id").attr("value", $("#inputClc_id").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaMayor").attr("value", $("#inputCuentaMayor").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaSefin").attr("value", $("#inputCuentaSefin").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "inputCuentaRegistro").attr("value", $("#inputCuentaRegistro").val() ))
                                    .append($("<input>").attr("type", "hidden").attr("name", "ecf_id").attr("value", $("#ClaveFinan").attr("ecf") ))

                                $("body").append(form);
                                form.submit();
                            }else{
                                notify( "Debe escribir la cuenta a nivel de mayor solo numeros separados por puntos", "warning");
                            }
                        }else{
                            notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos", "warning");
                        }
                    }else{
                        notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos  ", "warning");
                    }
                });
                // F1 muestra el catálogo */
                $("#mostrarClafinan").click( function(e) {
                    cveFinID= "ClaveFinan";
                    cveFinNom = "";
                    $("#ClaveFinan").val("");
                    $("#ClaveFinan").attr("ecf","");
                    e.preventDefault();
                    dlgCveFin.dialog("open");
                });

                $("#inputCuentaMayor").blur( function(e) {
                   // Mínimo 5 dígitos
                    $("#inputCuentaMayor").val( preparaCuentaContable( $("#inputCuentaMayor").val(), 1 ) );
                });       
            ');
        $this->dibujaPantalla();
    }

    public function dibujaPantalla()
    {
        $this->setContenedor("contenedor");

        // Dibujamos la pantalla que permite buscar una cuenta a nivel de mayor
        $this->mixHtml(DibujaPantallaBuscarCuentaMayorHTML());
        $this->mixScript(DibujaPantallaBuscarCuentaMayorJS());
        // Dibujamos la pantalla que permite buscar y seleccionar Claves de Financiamiento
        // --------------------------------------------------------------------------------------------------------------
        $es116 = false;
        if ($this->gpo_id . $this->ur_id == 116) {
            $es116 = true;
        }
        $this->mixHtml(DibujaPantallaBuscarCgClaveFinaciamientoHTML(false, $es116));
        $this->mixScript(DibujaPantallaBuscarCgClaveFinaciamientoJS());

        $this->mixScript('
            $("#tituloPantalla").hide();

            $("#page-tabs a").click( function(e){
                e.preventDefault();
                var seleccionado = $(this);
                var cta="";
                $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                seleccionado.closest("li").addClass("active");

                    arrCta = cta.split( "." );
                    // Checamos cuántos niveles tiene
                    nNiveles = arrCta.length;
                if( $("#inputCuentaMayor").val().length > 8 && $("#inputCuentaMayor").val().length<=12){
                    var arrCta ;
                        cta = $("#inputCuentaMayor").val();
                    var nNiveles;
                    arrCta = cta.split( "." );
                                // Checamos cuántos niveles tiene
                    nNiveles = arrCta.length;
                    if(nNiveles == 5){

                        if( $.isNumeric(arrCta[0])==true &&
                            $.isNumeric(arrCta[1])==true &&
                            $.isNumeric(arrCta[2])==true &&
                            $.isNumeric(arrCta[3])== true &&
                            $.isNumeric(arrCta[4])==true){
                            
                                comunica("' . $this->pantalla . '",{
                                    metodo: "muestraDatos",
                                    comboMesDesde: $(this).attr("mes"),
                                    inputGur: $("#inputGur").val(),
                                    inputClc_id: $("#inputClc_id").val(),
                                    inputCuentaMayor: $("#inputCuentaMayor").val(),
                                    inputCuentaSefin: $("#inputCuentaSefin").val(),
                                    inputCuentaRegistro: $("#inputCuentaRegistro").val(),
                                    ecf:$("#ClaveFinan").attr("ecf")
                                });
                        }else{
                                notify( "Debe escribir la cuenta a nivel de mayor solo numeros separados por puntos", "warning");
                        }
                    }else{
                        notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos", "warning");
                    }

                }else{
                    notify( "Debe escribir la cuenta a nivel de mayor separando cada nivel con puntos  ", "warning");
                }
                
                $("#detalle").attr("msc", seleccionado.attr("mes") );
                
                e.stopPropagation();
                return false;
            });
            
            var balanzaDetalle = 0;
            
            var sortcol = "CUENTA";
            var sortdir = 1;
            var searchString = "";
            
            function myFilter(item, args) {
                if( item["c"] === null || item["n"] === null ){ return false }
                
                // Si NO es de la balanza detalle ...
                if( args["mayor"] === undefined ){
                    // Si empieza con numero ... buscamos en la cuenta
                    strNumeros = "0123456789";
                    if( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ){
                        return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                    // si no ... buscamos en el texto
                    }else{
                        if(args.searchString != "" && item["n"].toLowerCase().indexOf(args.searchString) == -1 ){
                            return false;
                        }
                    }
                // En la balanza detalle, filtramos por niveles a mostrar
                }else{
                    // todo
                    if( args["todo"] == true ){ return true; }
                    if( args["mayor"] == true ){ if ( item["c"].trim().length == 9 ) return true }
                    // Agrupador
                    if( args["agrupador"] == true ){ if ( item["c"].trim().length == 13 ) return true }
                    // Registro
                    if( args["registro"] == true ){ if ( item["c"].trim().length > 13 ) return true }
                    return false;
                }
                return true;
            }
            
            function comparer(a, b){
                var x = a[sortcol], y = b[sortcol];
                return (x == y ? 0 : (x > y ? 1 : -1));
            }
              
            
        ');
        $html = $this->getTablaLibroMayor();
            $this->mixHtml($html);
    }
    /* Regresamos en formato JSON del mes seleccionado, a nivel de mayor */

    private function DameLibroMayor($comboMesDesde, $inputCuentaMayor, $inputCuentaSefin, $inputCuentaRegistro, $inputGur, $inputClc_id, $ecf)
    {

        $reporte['tipo'] = 1;
        $reporte['agrupador'] = 1;

        $periodo['mesDesde'] = $comboMesDesde; //Informacion de acuerdo al mes

        $busqueda['tipo'] = 1;
        $busqueda['cuentaMayor'] = $inputCuentaMayor;
        $busqueda['cuentaSefin'] = $inputCuentaSefin;
        $busqueda['cuentaRegistro'] = $inputCuentaRegistro;
        $programatico['epg_id'] = '';

        $this->datosLibroMayor(
            $this->gpo_id,
            $this->ur_id,
            $this->ue_id,
            '',
            $periodo,
            '',
            $ecf,
            $reporte,
            FALSE,
            '',
            '',
            array("busqueda" => $busqueda, "gur" => $inputGur, "clc_id" => $inputClc_id)
        );

        return false; //json_encode($datos);
    }
    /* Mostramos la información de un sistema específico */

    private function muestraDatos()
    {
        // Armamos los parámetros para pedir correctamente el libro Mayor
        $comboMesDesde = ((isset($_POST['comboMesDesde']) && trim($_POST['comboMesDesde'])
            != '') ? $_POST['comboMesDesde'] : 99);

        $inputGur = ((isset($_POST['inputGur']) && trim($_POST['inputGur']) != '')
            ? $_POST['inputGur'] : '');
        $inputClc_id = ((isset($_POST['inputClc_id']) && trim($_POST['inputClc_id'])
            != '') ? $_POST['inputClc_id'] : '');
        $inputCuentaMayor = ((isset($_POST['inputCuentaMayor']) && trim($_POST['inputCuentaMayor'])
            != '') ? $_POST['inputCuentaMayor'] : '');
        $inputCuentaSefin = ((isset($_POST['inputCuentaSefin']) && trim($_POST['inputCuentaSefin'])
            != '') ? $_POST['inputCuentaSefin'] : '');
        $inputCuentaRegistro = ((isset($_POST['inputCuentaRegistro']) && trim($_POST['inputCuentaRegistro'])
            != '') ? $_POST['inputCuentaRegistro'] : '');
        $ecf = ((isset($_POST['ecf']) && trim($_POST['ecf']) != '') ? $_POST['ecf']
            : '');
        $this->DameLibroMayor(
            $comboMesDesde,
            $inputCuentaMayor,
            $inputCuentaSefin,
            $inputCuentaRegistro,
            $inputGur,
            $inputClc_id,
            $ecf
        );

        return false;
    }

    private function datosLibroMayor($gpo_id, $ur_id, $ue_id, $pot_id, $periodo, $programatico,
    $claveFinanciamiento, $reporte, $detalle, $mmc_id, $niveles,
    $parametroExtra = array()) {
        $periodoSql = "AND msc_id = " . $periodo['mesDesde'];
        $cuentamayor = $parametroExtra['busqueda']['cuentaMayor'];

        $arrCta = explode(".", $cuentamayor);
        $elementos = count($arrCta);
        if ($elementos != 5) {
            return false;
        }
        $cuentaMayorSql = "AND x.ger_id = ? AND x.cgp_id = ? AND x.rub_id = ? AND x.cgcu_id = ? AND x.cgsc_id = ? ";
        $cuentaSefinSql = $parametroExtra['busqueda']['cuentaSefin'] != '' ? "AND x.cgc_id = '" . $parametroExtra['busqueda']['cuentaSefin'] . "' "
        : '';
        $cuentaRegistroSql = $parametroExtra['busqueda']['cuentaRegistro'] ? "AND x.cta_id = '" . $parametroExtra['busqueda']['cuentaRegistro'] . "' "
        : '';
        $gurSql = '';
        $clc_idSql = '';
        $cvefinSql = (($claveFinanciamiento != '') ? " AND ecf_id = " . $claveFinanciamiento . " " : "");
        $parametros = [$arrCta[0], $arrCta[1], $arrCta[2], $arrCta[3], $arrCta[4]];

        if ($parametroExtra['gur'] != '') {
            $gurSql = "AND pdx_gpo_id_vinculacion = " . substr($parametroExtra['gur'],
                0, 1) . " ";
            $gurSql .= "AND pdx_ur_id_vinculacion = " . substr($parametroExtra['gur'],
                1, 2) . " ";
        }
        if ($parametroExtra['clc_id'] != '') {
            $clc_idSql = "AND pdx_clc_id_vinculacion = " . $parametroExtra['clc_id'] . " ";
        }

        // Armamos la consulta
        $sql = "
                SELECT m.gpo_id, m.ur_id, ur.ur_clave, ur.ur_nombre,
                    x.cta_gpo_id, x.cta_ur_id, x.cta_ue_id,
                    pc.cta_cuenta,
                    x.epg_id, oa.prg_id, oa.spr_id, oa.py_id, oa.oa_id, CONCAT(ep.epg_clave,' ') AS epg_clave, oa.oa_nombre,
                    tg.tg_id, tg.tg_nombre,
                    i.idp_id, i.idp_nombre,
                    pg.pg_id, pg.pg_nombre,
                    pe.pe_id, pe.pe_nombre,
                    tr.ter_id, tr.ter_nombre,
                    ef.ecf_id, ef.ecf_clave, cf.cfi_nombre,
                    pdx_clc_id_vinculacion AS clc_id,
                    pdx_afe_id_vinculacion AS afe_id,
                    pdx_cmp_id_vinculacion AS cmp_id,
                    pdx_bdo_id_vinculacion AS bdo_id,
                    CONCAT( pdx_gpo_id_vinculacion, pdx_ur_id_vinculacion ) AS gur,
                    pdx_ejercicio,
                    CONCAT( pot_id, '-', prf_id, pma_id ) AS poliza, pma_fecha,
                    rfc_id, tdf_id, pdx_referencia, pdx_fechaReferencia,
                    pdx_debe, pdx_haber,
                    pdx_id AS id
                FROM " . BD_CTRL . ".cg_PolizaMaestro m
                    INNER JOIN " . BD_CTRL . ".cg_PolizaDetalleAuxiliar x USING( gpo_id, ur_id, ue_id, msc_id, pot_id, prf_id, pma_id )
                    INNER JOIN " . BD_CTRL . ".cg_PlanCuentas pc USING( cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id )
                    LEFT JOIN " . BD_CTRL . ".cat_UnidadResponsable ur USING( gpo_id, ur_id )
                    LEFT JOIN " . BD_CTRL . ".cat_PartidaGenerica pg USING( pg_id )
                    LEFT JOIN " . BD_CTRL . ".cat_PartidaEspecifica pe USING( pg_id, pe_id )
                    LEFT JOIN " . BD_CTRL . ".cat_IdentificadorPartida i USING( idp_id )
                    LEFT JOIN " . BD_CTRL . ".cat_TipoGasto tg ON pe.tg_id = tg.tg_id
                    LEFT JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento ef USING( ecf_id )
                    LEFT JOIN " . BD_SINPRES . ".cat_ClaveFinanciamiento" . APP_EJERCICIO . " cf USING( cff_id,fte_id, sft_id, ori_id, sor_id, cfi_id, cfi_anio )
                    LEFT JOIN " . BD_SINPRES . ".cat_SuborigenRecurso" . APP_EJERCICIO . " cs USING( cff_id,fte_id, sft_id, ori_id, sor_id )
                    LEFT JOIN " . BD_SINPRES . ".cat_TipoRecurso tr USING( ter_id )
                    LEFT JOIN " . BD_CTRL . ".enc_Programatico ep USING( epg_id )
                    LEFT JOIN " . BD_CTRL . ".cat_ObraActividad oa ON ep.gpo_id = oa.gpo_id AND ep.ur_id = oa.ur_id AND ep.prg_id = oa.prg_id AND ep.spr_id = oa.spr_id AND ep.py_id = oa.py_id AND ep.oa_id = oa.oa_id
                " . (($reporte['tipo'] == 2 && $_SESSION[_S]['nvj_id'] < 6) ? "   INNER JOIN " . BD_CTRL . ".cat_CargaTrabajo t ON t.gpo_id = m.gpo_id AND t.ur_id = m.ur_id
                WHERE t.usr_id = " . $_SESSION[_S]['usr_id'] . "
                    " : "WHERE m.gpo_id = $gpo_id AND m.ur_id = $ur_id ") . "
                    " . (($ue_id != '') ? "AND x.cta_ue_id = $ue_id " : "") . "
                    $periodoSql
                    $cuentaMayorSql
                    $cuentaSefinSql
                    $cuentaRegistroSql
                    $gurSql
                    $clc_idSql
                    $cvefinSql
                    AND m.epl_id NOT IN ( 'C', 'H' )
                ORDER BY cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id, pma_fecha, nat_id
            ";

        $this->setSql($sql);
        $this->setParams($parametros);

        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = '2000';
        $nav->idTabla = 'tbl-libroMayor';
        if ($this->wrLlenaTabla($nav) == 0) {
            $this->mixScript('
                notify("No se encontraron polizas con los criterios seleccionados","warning");');
        }
        $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );
    }

    private function getTablaLibroMayor(): string
    {
        $tabla = new stdClass();
        $tabla->esCompacta = true;
        $tabla->cabeceraFija = true;
        $tabla->inputFiltro = "buscar";
        $tabla->id = 'tbl-libroMayor';
        $tabla->seleccionable = true;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = '';

        $tr = array();
        $th = new stdClass();
        $th->texto = 'UE';
        $th->width = '90px';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'POLIZA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TDF';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA REFERENCIA';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'AFE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CMP';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BDO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DEBE';
        array_push($tr, $th);      

        $th = new stdClass();
        $th->texto = 'HABER';
        array_push($tr, $th);
        
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cta_ue_id']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['poliza']; 
        $td->align= "center"; 
        $td->jsCallback = 'PolFizaFormatter';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pma_fecha']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['rfc_id']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['tdf_id'];
        $td->align= "center"; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pdx_referencia']; 
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pdx_fechaReferencia']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['clc_id']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['afe_id']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['cmp_id']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['bdo_id']; 
        $td->align= "center";
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pdx_debe']; 
        $td->align= "right";
        $td->jsCallback = 'FormatoMoneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = new stdClass();
        $td->campoTxt->campos = ['pdx_haber'];
        $td->align= "right"; 
        $td->jsCallback = 'FormatoMoneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        return $this->wrPonTabla($tabla);
    }

}
if (isset($_SESSION[_S]['prm']['C']['4']['2']['1'])) {
    new ControladorLibroMayor();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
