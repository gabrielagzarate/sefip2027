<?php

/*
  Mostramos en pantalla una póliza, sin opciones para modificarla
  Usada para mostrar una póliza totalmente de orden, o cancelada.   Es decir, cuando ninguna parte de la misma permita la edición
 */

if (!isset($_SESSION)) {
    session_start();
}
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
//require_once dirname(__FILE__).'/../../lib/funcionesSCG.php';
require_once dirname(__FILE__) . '/../../lib/mysql_escape_mimic.php';

class ControladorMuestraPoliza extends WebResponse
{
    public function __construct()
    { //Constructor
        parent::__construct();

        switch ($this->metodo) {
            case 'SeleccionaTabulador':
                $this->SeleccionaTabulador();
                break;
            case 'dibujaSeccionEncabezado':
                $this->dibujaSeccionEncabezado();
                break;
            case 'dibujaSeccionDetalle':
                $this->dibujaSeccionDetalle();
                break;
            case 'cargaPoliza':
                $this->cargaPoliza();
                break;
            case 'dameTotales':
                $this->dameTotales();
                break;
            default:
                $this->main();
                break;
        }
        $this->returnJson();
    }

    private function main()
    {

        // Pantalla
        $msc_id = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m
            : '');
        $pot_id = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p
            : '');
        $prf_id = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r
            : '');
        $numero = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n
            : '');
        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue
            : '');

        // modo póliza: 1=normal, 2=registro, 3=mayor, 4=sumarizado V, 5=sumarizado V + R
        $mp = ((isset($this->prm->mp) && $this->prm->mp != '') ? $this->prm->mp
            : '');

        // Para saber desde qué pantalla nos llamaron y regresar correctamente
        $pantallaOrigen = ((isset($this->prm->ori) && $this->prm->ori != '') ? $this->prm->ori
            : '');

        // Marcos: 08/abr/2015
        // Por el filtro en el libro diario por clc, afectacion, etc., es probable que el
        // mes de la línea de la póliza sea diferente al mes en el que están trabajando (combo)
        $mesPolizaActual = ((isset($this->prm->mpa) && $this->prm->mpa != '')
            ? $this->prm->mpa : '99');  // Numero inexistente de mes en BD para cuando venga vacío y así nunca lo encuentre
        // Si el mes de la línea de la póliza es diferente al que tienen seleccionado en
        // el combo, el de la línea toma prioridad
        if ($mesPolizaActual != 99 && $mesPolizaActual != $msc_id) {
            $msc_id = $mesPolizaActual;
        }

        // Para ajustar un poco los espacios ... porque los veo muy separados
        $this->mixstyle('
            .ui-widget-overlay {
                background: #aaaaaa url(images/ui-bg_flat_0_aaaaaa_40x100.png) 50% 50% repeat-x !important;
                opacity: 100 !important;
                filter:Alpha(Opacity=100) !important;
            }
        ');
        $this->mixStyle('
            .form-group{margin-bottom:12px!important}
            legend{font-size:14px!important;margin-bottom:3px!important; font-weight:bold;}
            .form-control:focus { border-color: rgba(82, 168, 236, 1); box-shadow: 0 1px 1px }
            .modal-header{padding:8px!important; background: #0074D9; color:white;}
            .modal-footer{padding:10px 20px!important}
            .close { color:white; opacity: 0.8;};
        ');

        $this->mixScript('
            function notify(d,e,f){d=typeof d!=="undefined"?d:"Notificacion";e=typeof e!=="undefined"?e:"success";f=typeof f!=="undefined"?f:3000;if($("#notification").length<1){
            $("body").append("<div id=\"notification\" class=\"information\"><span>Notificacion</span><a class=\"close\" href=\"#\">x</a></div>")
            }$notification=$("#notification");$notificationSpan=$("#notification span");$notificationClose=$("#notification a.close");$notificationSpan.text(d);$notificationClose.click(function(a){a.preventDefault();$notification.css("top","-50px")});$notification.css("top","-50px").stop().removeClass().addClass(e).animate({top:0},500,function(){$notification.delay(f).animate({top:"-50px"},500)})};
        ');

        $this->setContenedor('cgPantallaCompleta');

        // Tabuladores
        $html = <<<HTML
            <div class="modal-content" style="box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);  border-radius: 5px; border: 2px solid #9d2449">
                <div class="modal-body" style="padding:24px">
                    <div id="areaBotones" style="margin-bottom:8px">
                        <button id="homeMenuMP" data-placement="bottom" title="Cerrar ventana" class="btn btn-outline-secondary me-2">
                            <i class="fa-solid fa-circle-left me-2"></i>Regresar
                        </button>
                        <button id="idPolizaTemporal" style="float: right; display:none;" data-placement="bottom" class="btn btn-outline-secondary disabled"># Temporal</button>
                        <span style="float: right;"></span>
                        <button id="EsChequeCancelado" style="float: right; display:none;" data-placement="bottom" class="btn btn-outline-secondary disabled">Cheque Cancelado</button>
                        <span style="height:6px; display:block"></span>
                    
                        <div id="page-tabs">
                            <ul class="nav nav-tabs">
                                <li class="active me-2">
                                    <a class="btn secciones" style="text-decoration: underline; border: 1px solid #999" href="1"> Encabezado </a></li>
                                <li class="last me-2">
                                    <a class="btn secciones" style="text-decoration: underline; border: 1px solid #999" href="2">Detalle</a><div class="last"></div>
                                </li>
                                <li class="">
                                    <input type="text" class="soloDetalle btn" style="float: right; width:12em; text-align: right; display:none;" id="sumaHaber" name="sumaHaber" disabled value="" style="text-align:right" placeholder="Suma Haber"/>
                                </li>
                                <li class="">
                                    <input type="text" class="soloDetalle btn" style="float: right; width:12em; text-align: right; display:none;" id="sumaDebe" name="sumaDebe" disabled value="" style="text-align:right" placeholder="Suma Debe"/>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div id="EncabezadoPoliza" style="overflow-y: auto; overflow-x: hidden; visibility: none;"></div>
                    <div id="DetallePoliza"    style="overflow-y: auto; overflow-x: hidden;"></div>
                </div>
            </div>
        HTML;
        $this->mixHtml($html);

        $this->mixScript('
            // Grabamos la UE que usaremos ...
            $("#EncabezadoPoliza").attr( "ue", "' . $ue . '" );
            $("#cgPantallaCompleta").show()
            $(document).on("click", "#homeMenuMP", function (e) {
                if ( "' . $pantallaOrigen . '" != "bancoDocumentos" ) { $(".navbar").show(); }
                
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosCuentas]").html("");
                //$("div[aria-describedby=cargaDatosProveedor]").html("");
                        //$("#myGridProveedores").html("");
                        //$("#cargaDatosProveedor div[class=slick-cell]"").remove();
                $(document).off("click", "#homeMenuMP" );
                // Por si se quedó el tooltip al darle click al botón mostrar
                var tooltips = $(".tooltip").not(".in");
                if (tooltips) {
                    tooltips.remove();
                }
                    ' . ($pantallaOrigen == 'bancoDocumentos' ? '$("#bdPantallaCompleta").show();' : '') . '
                $("#cgPantallaCompleta").empty();
                $("#cgPantallaCompleta").html("").hide();
            });
        ');

        // Marcos: 02/feb/2016
        // Esto estaba en varias llamadas comunica(), pero como ya se puede dibujar en más de un contenedor,
        // evitamos llamadas al servidor
        $this->dibujaSeccionEncabezado();
        $this->dibujaSeccionDetalle($ue, $pot_id, $prf_id, $msc_id, $numero);
        $this->seleccionaTabulador();

        $this->mixScript('
                $(".navbar").hide();

                $("#page-tabs a").click( function(e) {
                    $(".secciones").removeClass("bg-light");
                    $(this).addClass("bg-light");

                    e.preventDefault();
                    $("#page-tabs li").each(function (index) { $(this).removeClass("active"); });
                    $(this).closest("li").addClass("active");
                    comunica("scg/poliza/ControladorMuestraPoliza.php",{ metodo : "SeleccionaTabulador", "seccion" : $(this).attr("href") });
                    return false;
                });
            ');

        // Después de preparar la pantalla ... si es un cambio ... cargamos los datos de la póliza
        // --------------------------------------------------------------------------------------------------------------
        $this->mixScript('
            $("#homeMenuMP").prop("md", "1");
            
            // Buscamos un LibroDiario en la pantalla de selección
            // ===========================================================================
            function CargaDetallePoliza( _pot, _prf, _num, _mes, _ue ) {
                $("#tabla-tbl-muestraPoliza tbody").html("");
                const tbody = $("#tabla-tbl-muestraPoliza tbody");
            
                $.ajax({
                    data : { "p": _pot, "r": _prf, "n": _num, "m": _mes, "ue": _ue, "o": "true", "mp": "' . $mp . '" },
                    url:  "lib/scg/poliza/dameDetallePoliza.php",
                    type: "post",
                    success: function ( response ) {
                        var json = $.parseJSON(response);
                        data = [];
                        for (var i = 0; i < json.length; i++) {
                                d = (data[i] = {});
                                id = json[i].nL;
                                g = json[i].g;
                                n1 =  json[i].mayor;
                                cta = json[i].cta;
                                nom = json[i].nom;
                                d =   json[i].d;
                                h =   json[i].h;
                                df =  json[i].df;
                                nL =  json[i].nL;
                                nat = json[i].nat;
                                usr = json[i].modif;                      
                        }
                            
                        /*********** AQUI AGRUPAMOS POR CUENTAS ******************/
                        const datosFiltrados = json;
                
                        // Agrupar por campo "mayor"
                        const grupos = {};
                        datosFiltrados.forEach(item => {
                        if (!grupos[item.mayor]) grupos[item.mayor] = [];
                            grupos[item.mayor].push(item);
                        });

                        // Renderizar grupos
                        for (let mayor in grupos) {
                            const nombreGrupo = `${mayor}`;
                            const cd = `${mayor}`;
                            const groupId = `group-`+cd.split(".")[0]+cd.split(".")[1]+cd.split(".")[2]+cd.split(".")[3];

                            // Encabezado del grupo
                            const headerRow = document.createElement("tr");
                            headerRow.classList.add("group-header", "table-active");
                            headerRow.setAttribute("data-group", groupId);
                            headerRow.innerHTML = `
                                <td colspan="7">
                                    <strong><span class="toggle-icon">▼</span> ${nombreGrupo}</strong>
                                </td>
                            `;

                            tbody.append(headerRow);

                            //
                            const formatoMoneda = new Intl.NumberFormat("es-MX", {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });

                            // Filas del grupo
                            grupos[mayor].forEach(item => {
                                const row = document.createElement("tr");
                                row.classList.add("group-row", groupId, "seleccionable");
                                row.style.display = "table-row";
                                row.setAttribute("data-id", item.g);
                                row.setAttribute("data-cta", item.cta);
                                row.setAttribute("data-nom", item.nom);
                                row.setAttribute("data-d",  (item.d == 0) ? "" : formatoMoneda.format(item.d));
                                row.setAttribute("data-h", (item.h == 0) ? "" : formatoMoneda.format(item.h));
                                row.setAttribute("data-df", item.df);
                                row.setAttribute("data-usr", (item.usr == undefined) ? "" : item.usr);

                                row.innerHTML = `
                                    <td></td>
                                    <td>${item.cta}</td>
                                    <td>${item.nom}</td>
                                    <td style="text-align:right;">${(item.d == 0) ? "" : formatoMoneda.format(item.d)}</td>
                                    <td style="text-align:right;">${(item.h == 0) ? "" : formatoMoneda.format(item.h)}</td>
                                    <td>${item.df}</td>
                                    <td>${(item.usr == undefined) ? "" : item.usr}</td>
                                `;

                                row.addEventListener("click", function () {
                                    document.querySelectorAll(".seleccionable").forEach(r => r.classList.remove("seleccionado"));
                                    this.classList.add("seleccionado");
                                });

                                tbody.append(row);
                            });

                            // Expandir/contraer
                            headerRow.addEventListener("click", () => {
                                const rows = document.querySelectorAll(`.${groupId}`);
                                const visible = rows[0].style.display !== "none";
                                rows.forEach(r => r.style.display = visible ? "none" : "table-row");

                                const icon = headerRow.querySelector(".toggle-icon");
                                icon.textContent = visible ? "▶" : "▼";
                            });
                        }
                    }
                });
            };

        ');
    }

    // Activamos la seccion de captura de póliza que hayan seleccionado en el tab
    // ----------------------------------------------------------------------------------------------------------------
    private function SeleccionaTabulador()
    {

        $seccion = (isset($this->prm->seccion) ? $this->prm->seccion : '1');

        if ($seccion == '1') {
            $this->mixScript('
                $().addClass("bg-light");
                setTimeout( function() { $("#EncabezadoPoliza").height( $(window).height() - $("#areaBotones").height() - 95 ) }, 50 );
                $("#EncabezadoPoliza").attr("style", "visibility: visible");
                $("#DetallePoliza").attr("style", "display: none");
                $("#sumaDebe").css("display", "none" );
                $("#sumaHaber").css("display", "none" );
                ');
        } else {
            $this->mixScript('
                // Sólo permitimos ver el detalle una vez grabado los datos del encabezado
                if ( $("#homeMenuMP").prop("md") == 1 ) {
                    setTimeout( function() { $("#DetallePoliza").height( $(window).height() - $("#areaBotones").height() - 95 ) }, 50 );
                    $("#DetallePoliza").attr("style", "visibility: visible");
                    $("#EncabezadoPoliza").attr("style", "display: none");
                    $("#sumaDebe").css("display", "inline" );
                    $("#sumaHaber").css("display", "inline" );
                    // Recalculamos la suma del debe y del haber
                    recalcularSumas( 0 )
                    
                }
            ');
        }
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Dibujamos la pantalla del encabezado de la póliza
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function dibujaSeccionEncabezado()
    {

        $this->setContenedor('EncabezadoPoliza');

        $this->mixHtml('
        <form action="" class="" id="formaEncabezadoPoliza">
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    <legend>Tipo Póliza</legend>
                    <input type="text" name="pot_id" id="pot_id" class="form-control" value="" disabled>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <legend>Prefijo</legend>
                    <input type="text" name="prf_id" id="prf_id" class="form-control" value="" disabled>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <legend>Número</legend>
                    <input type="text" name="pol_numero" id="pol_numero" class="form-control" value="" disabled>
                </fieldset>
                <fieldset class="col-md-2 col-xs-2">
                    <legend>Fecha</legend>
                    <div class="form-group ">
                        <input name="fecha" id="fecha" type="text" class="form-control" disabled>
                    </div>
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    <legend>Agrupación</legend>
                    <input type="text" name="apg_id" id="apg_id" class="form-control" value="" disabled>
                </fieldset>
            </div>

            <div class="row">
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Momento Contable</legend>
                    <div class="form-group ">
                        <input name="mmc_id" id="mmc_id" type="text" class="form-control" disabled>
                    </div>
                </fieldset>
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Tipo de Momento Contable</legend>
                    <div class="form-group ">
                        <input name="tmc_id" id="tmc_id" type="text" class="form-control" disabled>
                    </div>
                </fieldset>
            </div>
            
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    <legend>Fecha Registro</legend>
                    <input type="text" name="usr_fecha" id="usr_fecha" class="form-control" value="" disabled>
                </fieldset>
                <fieldset class="col-md-4 col-xs-4">
                    <legend>Usuario Registro</legend>
                    <div class="form-group ">
                        <input name="usr_id" id="usr_id" type="text" class="form-control" disabled>
                    </div>
                </fieldset>
            </div>
            
            <div class="row">
                <fieldset class="col-md-12 col-xs-12">
                    <legend>Descripción de la Póliza</legend>
                    <textarea name="concepto" id="concepto" rows="5" class=" form-control " disabled style="text-transform:none !important; resize: none;" tabindex="140"></textarea>
                </fieldset>
            </div>
            
            <!--
            <div class="row">
                <fieldset class="col-md-2 col-xs-2">
                    <button id="verInfoSCP" data-placement="bottom" title="Origen Presupuestal de la póliza contable" class="btn btn-outline-secondary me-1"><i class="fa-solid fa-eye me-2"></i>Origen Presupuestal</button>
                </fieldset>
            </div>
            -->
        </form>
        ');

        $js = <<<JS

            // Mostramos la información presupuestal que dió origen al asiento contable
            $("#verInfoSCP").click(function(e) {
                e.preventDefault();

                // Si no hay ningún documento seleccionado, no hacemos nada
                if ( gridLibroDiario.getActiveCell() == null ) { return false };
                
                // Obtenemos el array de los datos de la linea actual
                row = gridLibroDiario.getDataItem(gridLibroDiario.getActiveCell().row);
                
                // Si no hay nada que selecionar, no hacemos nada
                if ( gridLibroDiario.getSelectedRows() == "" || row.pot_id == "" ) {
                    e.stopPropagation();
                    return false;
                }
                
                // Dependiendo del momento contable mostraremos información del presupuesto
                if ( row.mc.substring( 0, 1 ) == "C" ) {
                    comunica("lib/muestraModalCompromiso.php", { "c" : $("#pol_clc").val() } );
                }
                if ( row.mc.substring( 0, 1 ) == "D" ) {
                    comunica("lib/muestraModalDevengado.php", { "id" : $("#pol_clc").val(), "apg" : $("#apg_id").val().substr( 0, 3 ) } );
                }
                if ( row.mc.substring( 0, 1 ) == "E" || row.mc.substring( 0, 1 ) == "T" ) {
                    comunica("lib/muestraModalCLC.php", { "id" : $("#pol_clc").val(), "mmc" : row.mc.substring( 0, 1 ) } );
                }

                e.stopPropagation();
            });

            setTimeout( function() { $( "#concepto" ).focus() }, 100 );
        JS;
        $this->mixScript($js);
    }

    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    // Dibujamos la pantalla donde modifican/adicionar un registro a la póliza
    // ------------------------------------------------------------------------------------------------------------------------
    // ------------------------------------------------------------------------------------------------------------------------
    private function DibujaSeccionDetalle($ue, $pot_id, $prf_id, $msc_id, $numero)
    {

        $ue = (isset($ue)) ? $ue : '001';  // Unidad Ejecutora
        // DETERMINAMOS SI VIENE DESDE EL LIBRO DIARIO (Y POR LO TANTO ES UN CAMBIO)
        $msc_id = ((isset($msc_id) && $msc_id != '') ? $msc_id : '');
        $pot_id = ((isset($pot_id) && $pot_id != '') ? $pot_id : '');
        $prf_id = ((isset($prf_id) && $prf_id != '') ? $prf_id : '');
        $numero = ((isset($numero) && $numero != '') ? $numero : '');
        $ue = ((isset($ue) && $ue != '') ? $ue : '');

        $this->setContenedor('DetallePoliza');
        $this->mixHtml('<div id="myGridDetallePoliza" style="width:100%;height:200px;">
            <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                <table id="tabla-tbl-muestraPoliza" class="table table-striped table-bordered">
                    <div class="col-md-12 row" style="text-align:center">
                        <thead class="sticky-top">
                            <tr class="warning" role="row" style="text-align:center">
                                <th class="warning" role="columnheader" style="width:90px !important;"></th>
                                <th class="warning" role="columnheader" >Cuenta Contable</th>
                                <th class="warning" role="columnheader">Nombre</th>
                                <th class="warning" role="columnheader">Debe</th>
                                <th class="warning" role="columnheader">Haber</th>
                                <th class="warning" role="columnheader">Docto Fte</th>
                                <th class="warning" role="columnheader"></th>
                            </tr>
                        </thead>
                    </div>
                    <tbody></tbody>
                </table>
            </div>        
        </div>');

        $js = <<<JS
            // Suma el debe y el haber...
            function recalcularSumas( intGrabaEnMaestro ){
                var nTotalDebe = 0;
                var nTotalHaber = 0;
                
                // Preguntamos en la base de datos
                comunica( "scg/poliza/ControladorMuestraPoliza.php", {
                    "metodo" : "dameTotales",
                    "ue"     : $("#EncabezadoPoliza").attr("ue"),
                    "p"      : $("#idPolizaTemporal").attr( "t" ),
                    "r"      : $("#idPolizaTemporal").attr( "p" ),
                    "n"      : $("#idPolizaTemporal").attr( "n" ),
                    "m"      : $("#idPolizaTemporal").attr( "m" ),
                    "op"	 : $("#homeMenuMP").attr("op"),
                    "gM"	 : intGrabaEnMaestro
                });
                return false;
            }
        
            function FormatoMoneda(row, cell, value, columnDef, dataContext) {

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
                    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
                
                }

            }
            // 11131.438.001.001.0001			18
            // 11131.438.001.001.123456789		23
            // 11131.438.001.001.01.0001		20
            function FormatearCuenta(row, cell, value, columnDef, dataContext) {
                switch( value.length ) {
                    case 18:
                        return value.substr( 0, 5 ) + " " + value.substr( 5, 3 ) + " " + value.substr( 8, 3 ) + " " + value.substr( 11, 3 ) + " " + value.substr( 14, 4 );
                        break;
                    case 23:
                        return value.substr( 0, 5 ) + " " + value.substr( 5, 3 ) + " " + value.substr( 8, 3 ) + " " + value.substr( 11, 3 ) + " " + value.substr( 14, 9 );
                        break;
                    case 20:
                        return value.substr( 0, 5 ) + " " + value.substr( 5, 3 ) + " " + value.substr( 8, 3 ) + " " + value.substr( 11, 3 ) + " " + value.substr( 14, 2 ) + " " + value.substr( 16, 4 );
                        break;
                    default:
                        return value;
                        break;
                }
            }
            
            function CheckmarkFormatter(row, cell, value, columnDef, dataContext) {
                return ( value == "1" ) ? "<img src=\'css/images/punto.png\'>" : "";
            }            
        JS;

        // Cargamos los datos de la póliza
        // --------------------------------------------------------------------------------------------------------------
        $this->mixScript($js
            . 'comunica("scg/poliza/ControladorMuestraPoliza.php", { metodo : "cargaPoliza",
                m : "' . $msc_id . '",
                p : "' . $pot_id . '",
                r : "' . $prf_id . '",
                n : "' . $numero . '",
                ue: "' . $ue . '"
            });
        ');
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Cuando se trata de un cambio ... cargamos la póliza que quieren modificar
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function cargaPoliza()
    {

        $msc_id = $this->getPrm('m', '');
        $pot_id = $this->getPrm('p', '');
        $prf_id = $this->getPrm('r', '');
        $numero = $this->getPrm('n', '');
        $ue = $this->getPrm('ue', '');

        // Obtenemos los datos del maestro
        // ----------------------------------------------------------------------------------------------------------------
        $this->conectar();
        $this->setSql('SELECT ap.apg_id, ap.apg_nombre, prf.prf_id, prf.prf_nombre, m.pma_id,
            m.epl_id, pot.pot_nombre, DATE_FORMAT( m.pma_fecha, "%d-%m-%Y" ) AS fecha, m.pma_concepto, m.pma_debe,
            m.mmc_id, mc.mmc_nombre, m.tmc_id, tmc.tmc_nombre,
            CONCAT("(",IFNULL(u1.usr_usuario, "SIN USUARIO"),") ",IFNULL(u1.usr_nombre, "")," ",IFNULL(u1.usr_paterno, "")," ",IFNULL(u1.usr_materno, "")) AS nombreAlta,
            DATE_FORMAT( m.usr_fecha, "%d-%m-%Y" ) AS usr_fecha
            FROM cg_PolizaMaestro m
                INNER JOIN cat_AgrupacionPoliza ap USING( apg_id )
                INNER JOIN cat_PrefijoPoliza prf USING( prf_id )
                INNER JOIN cat_TipoPoliza pot USING( pot_id )
                LEFT JOIN ctrlUsuario.cat_Usuario u1 ON m.usr_id = u1.usr_Id
                LEFT  JOIN cat_MomentoContable mc USING( mmc_id )
                LEFT  JOIN cat_TipoMomentoContable tmc USING( tmc_id )
            WHERE m.gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND m.ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND m.ue_id = ' . $ue . '
            AND m.pot_id = "' . $pot_id . '" AND m.prf_id = "' . $prf_id . '" AND m.msc_id = ' . $msc_id . ' AND m.pma_id = ' . $numero . ';');

        $this->consultar();
        $this->desconectar();

        $row = $this->extraer();

        // Tratamiento para el concepto, por si trae comillas, acentos y cosas así
        $concepto = mysql_escape_mimic($row['pma_concepto']);

        $this->mixScript('
        
            // Para indicar que es un cambio
            $("#homeMenuMP").attr( "op", "C" );
            
            // Cargamos en Maestro
            $("#apg_id").val("' . $row['apg_id'] . ' - ' . $row['apg_nombre'] . '");
            $("#prf_id").val("' . $row['prf_id'] . ' - ' . $row['prf_nombre'] . '");
            $("#pol_numero").val("' . $row['pma_id'] . '");
            
            $("#mmc_id").val("' . $row['mmc_id'] . ' - ' . $row['mmc_nombre'] . '");
            $("#tmc_id").val("' . $row['tmc_id'] . ' - ' . $row['tmc_nombre'] . '");
            
            $("#usr_fecha").val("' . $row['usr_fecha'] . '");
            $("#usr_id").val("' . $row['nombreAlta'] . '");
            
            if( "' . $row['epl_id'] . '" == "H" ) {
                $("#EsChequeCancelado").css("display", "inline" );
            } else {
                $("#EsChequeCancelado").css("display", "none" );
            }
            
            $("#pot_id").val("' . $row['pot_nombre'] . '");
            $("#fecha").val("' . $row['fecha'] . '");
            
            $("#concepto").val("' . $concepto . '");
            
            // Cargamos el detalle   
            CargaDetallePoliza( "' . $pot_id . '", "' . $prf_id . '", ' . $numero . ', ' . $msc_id . ', "' . $ue . '" ); 
            
            $("#idPolizaTemporal").text( "Póliza : ' . $pot_id . '-' . $prf_id . $numero . '" ).css("display", "inline");
            $("#idPolizaTemporal").attr( "t", "' . $pot_id . '" );
            $("#idPolizaTemporal").attr( "p", "' . $prf_id . '" );
            $("#idPolizaTemporal").attr( "n", ' . $numero . ');
            $("#idPolizaTemporal").attr( "m", "' . $msc_id . '" );
            $("#formaDetallePoliza").css("display", "inline" );

            $("#grabarEncabezado").hide();
            $("#recuperarPoliza").hide();
            $("#page-tabs a[href=\"2\"]").click();
            $("#formaDetallePoliza").css("display", "inline" );
            
        ');
    }

    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    // Obtenemos los totales generales ( En la visualizacion de la póliza, solo mostramos en pantalla, no actualizamos
    // la tabla del maestro )
    // ----------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------
    private function dameTotales()
    {

        $ue = ((isset($this->prm->ue) && $this->prm->ue != '') ? $this->prm->ue : '');
        $p = ((isset($this->prm->p) && $this->prm->p != '') ? $this->prm->p : '');
        $r = ((isset($this->prm->r) && $this->prm->r != '') ? $this->prm->r : '');
        $n = ((isset($this->prm->n) && $this->prm->n != '') ? $this->prm->n : '');
        $m = ((isset($this->prm->m) && $this->prm->m != '') ? $this->prm->m : '');

        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Obtenemos la suma del debe y haber de la póliza solicitada
        $sql = 'SELECT SUM(lineas) AS lineas, IFNULL( SUM( debe ), 0 ) AS debe, IFNULL( SUM( haber ), 0 ) AS haber FROM
            ( SELECT "1" AS o, COUNT(*) AS lineas, SUM( pdt_debe ) AS debe, SUM( pdt_haber ) AS haber FROM cg_PolizaDetalle
            WHERE gpo_id = ' . $_SESSION[_S]['clasAdm-gpo'] . ' AND ur_id = ' . $_SESSION[_S]['clasAdm-ur'] . ' AND ue_id = ' . $ue . ' AND pot_id = "' . $p . '" AND prf_id = "' . $r . '" AND msc_id = ' . $m . ' AND pma_id = ' . $n .
            ') AS origen;';


        $conexion->setSql($sql);
        $conexion->consultar();
        $conexion->desconectar();

        // Obtenemos la variable del número de líneas que hay en el grid ...

        if ($conexion->getNumeroRegistros() > 0) {

            $row = $conexion->extraer();

            // Llenamos los campos con la información de la línea seleccionada
            $this->mixScript('
                nTotalDebe = ' . $row['debe'] . ';
                nTotalHaber = ' . $row['haber'] . ';
                nRows  = ' . $row['lineas'] . ';

                $("#sumaDebe").val( formatNumber(nTotalDebe,"","") );
                $("#sumaHaber").val( formatNumber(nTotalHaber,"","") );
                
                // Si las sumas son diferentes, remarcamos y en rojo
                if ( formatNumber(nTotalDebe,"","") != formatNumber(nTotalHaber,"","") ) {
                    $("#sumaDebe").css({ "color":"red", "font-weight":"bold"});
                    $("#sumaHaber").css({ "color":"red", "font-weight":"bold"});
                } else {
                    $("#sumaDebe").css({ "color":"black", "font-weight":"normal"});
                    $("#sumaHaber").css({ "color":"black", "font-weight":"normal"});
                }
            ');
        }
        $this->mixScript('window.espera?.stop();');
        return false;
    }
}
//-------------------------------//
new ControladorMuestraPoliza();
//---------------------------------//
