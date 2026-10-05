<?php
/*--------------------------------------------------------------------------------------------------------
* ControladorVinculacionPorClc
*
* Elaboró: Marcos
*
* Muestra una relación de CLCs de un mes determinado del presupuesto, y en las columnas de los momentos contables
* muestra los documentos fuente que dieron origen a pólizas contables.   Como pueden ser varios documentos y por lo
* tanto varias pólizas por una CLC y un momento contable, se mostrarán en una ventana emergente las pólizas de cada uno de ellos
--------------------------------------------------------------------------------------------------------*/
session_start();
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';

class ControladorVinculacionPorClc extends WebResponse
{
    private $PermisosCompromiso;
    private $PermisosDevengado;
    private $PermisosEjercido;
    private $PermisosMinistrado;
    private $PermisosPagado;
    private $PermisosPagado3ros;
    private $ejercicio;

    //Constructor
    public function __construct()
    {
        //mandar a llamar constructor base
        parent::__construct();

        // Cuando tengan permisos de lectura para una opción de vinculación, les mostramos la columna
        $this->PermisosCompromiso = isset($_SESSION[_S]['prm']['C']['5']['1']['1']) ? 1 : 0;
        $this->PermisosDevengado  = isset($_SESSION[_S]['prm']['C']['5']['2']['1']) ? 1 : 0;
        $this->PermisosEjercido   = isset($_SESSION[_S]['prm']['C']['5']['3']['1']) ? 1 : 0;
        $this->PermisosMinistrado = isset($_SESSION[_S]['prm']['C']['5']['5']['1']) ? 1 : 0;
        $this->PermisosPagado     = isset($_SESSION[_S]['prm']['C']['5']['6']['1']) ? 1 : 0;
        $this->PermisosPagado3ros = isset($_SESSION[_S]['prm']['C']['5']['7']['1']) ? 1 : 0;
        $this->ejercicio = APP_EJERCICIO;

        switch ($this->metodo) {
            case 'verUsoAfectacion':
                $this->verUsoAfectacion();
                break;
            default:
                $this->PonBarraBotones();
                break;
        }

        $this->returnJson();
    }

    private function PonBarraBotones()
    {
        $mesActual = ((isset($this->prm->mes) && $this->prm->mes != "") ? $this->prm->mes : "");
        //Nombre del Mes
        $arrMeses = ["Enero" => "1", "Febrero" => "2", "Marzo" => "3", "Abril" => "4", "Mayo" => "5", "Junio" => "6", "Julio" => "7", "Agosto" => "8", "Septiembre" => "9", "Octubre" => "10", "Noviembre" => "11", "Diciembre" => "12"];
        $nombreMes = array_search($mesActual, $arrMeses,true);
        $tipo = ((isset($this->prm->tipo) && $this->prm->tipo != "") ? $this->prm->tipo : "0");
        $arrTipos = ["TODOS LOS TIPOS DE CLC" => "0", "SERVICIOS PERSONALES" => "A", "GASTOS DE OPERACIÓN" => "B", "PAGO A TERCEROS" => "C", "ACUERDOS DE MINISTRACIÓN" => "D", "ANTICIPOS -FONDO ROTATORIO" => "E", "CONTRATO" => "F", "ADMINISTRACIÓN DIRECTA" => "G", "ANTICIPO DE OBRA" => "H", "MUNICIPIOS" => "I", "DEUDA PÚBLICA" => "J", "BURSATILIZACIÓN" => "K", "TRANSFERENCIAS" => "L", "PAGO A TERCEROS (OTROS)" => "M", "RECURSO ESTATAL" => "OA", "RECURSO FEDERAL" => "OB", "TRADICIONAL" => "1", "LINEA CAPTURA" => "2", "SIN FLUJO DE EFECTIVO" => "3", "CADENA PRODUCTIVA" => "4", "PAGO DIRECTO A PROVEEDOR" => "5"];
        $nombreTipo = array_search($tipo, $arrTipos,true);
        $edo = ((isset($this->prm->edo) && $this->prm->edo != "") ? $this->prm->edo : "0");
        $arrEdos = ["TODOS LOS ESTADOS" => "0", "ENVIADA A SEFIN" => "3", "CERE O CERECO CON PAGO TRANSFERIDO" => "17", "CLC PAGADA" => "18", "CLC INUTILIZABLE" => "20", "CLC CANCELADA" => "21"];
        $nombrEdo = array_search($edo, $arrEdos,true);
        $buscarFiltro = ((isset($this->prm->buscar) && $this->prm->buscar != "") ? $this->prm->buscar : "");

        // Determinamos el status de los meses
        $conexion =  new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql('SELECT msc_id, LOWER(msc_nombre) AS nombre, ems_id FROM cat_MesContable LEFT OUTER JOIN
            (SELECT * FROM cg_MesContable WHERE gpo_id = ? AND ur_id = ? ) AS s
            USING (msc_id)
            WHERE msc_id BETWEEN 1 AND 12
            ORDER BY msc_id');
        $conexion->consultar([
            getSession('clasAdm-gpo'),
            getSession('clasAdm-ur'),
        ]);
        $conexion->desconectar();

        $html = '<div id="areaBotones" sytle="width:300px;">
                        <div class="row">
                            <div id="muestraclasificacion" class="col-md-11 col-xs-11"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-9" style="padding: 5px 6px;">
                                <div class="btn-group me-1">
                                    <button id="mesActual" ma=0 class="btn btn-default btn-test dropdown-toggle" data-bs-toggle="dropdown" data-placement="right" title="Mes de la fecha de envío">
                                    Mes
                                    <span class="caret"></span></button>
                                    <ul id="mesActualSelect" class="dropdown-menu">';
                                        $strStatusMes = "";
                                        while ($row = $conexion->extraer()) {
                                            // En una variable meteremos el status para ponerlo en el DOM y usarlo desde ahí
                                            $strStatusMes .= (($row['ems_id'] == 'A') ? '.' : ' ');
                                            // Seguimos con el dibujado de la pagina normal
                                            $html .= '<li><a href=' . intval($row['msc_id']) . ' class="dropdown-item mesContable">' .
                                                (($row['ems_id'] == 'A') ? '<i class="fa fa-pencil me-2"></i>' : '<i class="me-4"></i>') . ucfirst($row['nombre']) . '</a></li>';
                                        }
                           $html .= '</ul>
                                </div>
                                <div class="btn-group me-1">
                                    <button id="tipoClcActual" data-placement="right" title="Filtrar por Tipo de CLC" ta=0 class="btn btn-default btn-test dropdown-toggle" data-bs-toggle="dropdown">
                                    Tipo
                                    <span class="caret"></span></button>
                                    <ul class="dropdown-menu scrollable-menu" style="height: auto; max-height: 300px; overflow-x: hidden;">';
                                    // Obtenemos todos los tipos de CLC
                                        $conexion->conectar();
                                        $conexion->setSql('SELECT tcl_id, tcl_nombre FROM cat_TipoClc ORDER BY tcl_id;');
                                        $conexion->consultar();

                                        $html .= '<li><a href=0 class="dropdown-item tipoClc">TODOS LOS TIPOS DE CLC</a></li>';
                                        $html .= '<li class="separador"></li>';
                                        while ($row = $conexion->extraer()) {
                                            // Seguimos con el dibujado de la pagina normal
                                            $html .= '<li><a href=' . $row['tcl_id'] . ' class="dropdown-item tipoClc">' .
                                            $row['tcl_id'] . " - " . $row['tcl_nombre'] . '</a></li>';
                                        }

                                        // Agregamos el origen del recurso: Federal o Estatal
                                        $html .= '<li class="separador"></li>';
                                        $html .= '<li><a href="OA" class="dropdown-item tipoClc">RECURSO ESTATAL</a></li>';
                                        $html .= '<li><a href="OB" class="dropdown-item tipoClc">RECURSO FEDERAL</a></li>';
                                        $html .= '<li class="separador"></li>';

                                        // Agregamos los modos de pago de la CLC
                                        $conexion->setSql('SELECT mpc_id, mpc_nombre FROM cat_ModoPagoClc ORDER BY mpc_id;');
                                        $conexion->consultar();
                                        while ($row = $conexion->extraer()) {
                                            // Seguimos con el dibujado de la pagina normal
                                            $html .= '<li><a class="dropdown-item tipoClc" href=' . $row['mpc_id'] . '>' .
                                            $row['mpc_id'] . " - " . $row['mpc_nombre'] . '</a></li>';
                                        }

                           $html .= '</ul>
                        </div>
                        <div class="btn-group me-1">
                            <button id="edoActual" ea=0 data-placement="right" title="Filtrar por Estado de la CLC" class="btn btn-default btn-test dropdown-toggle" data-bs-toggle="dropdown">
                            Estado
                            <span class="caret"></span></button>
                            <ul class="dropdown-menu">';
                                // Obtenemos los status de las CLC de 2 para arriba
                                // Marcos: 12/feb/2018
                                // Cambiaron los estados de la clc, así que ajustamos sus valores para
                                // el ejercicio 2018
                                $conexion->setSql('SELECT ecl_id, ecl_nombre FROM cat_EstadoClc WHERE ecl_id IN (3, 18, 21, 17, 20) ORDER BY ecl_id;');
                                $conexion->consultar();
                                $conexion->desconectar();

                                $html .= '<li><a href=0 class="dropdown-item estadoClc">TODOS LOS ESTADOS</a></li>';
                                $html .= '<li class="separador"></li>';
                                while ($row = $conexion->extraer()) {
                                    // Seguimos con el dibujado de la pagina normal
                                    $html .= '<li><a href=' . intval($row['ecl_id']) . ' class="dropdown-item estadoClc">' .
                                    intval($row['ecl_id']) . " - " . $row['ecl_nombre'] . '</a></li>';
                                }
                   $html .= '</ul> 
                        </div>
                        <div class="btn-group me-1">
                            <button id="btnWTF" class="btn btn-danger btn-test dropdown-toggle me-1" data-bs-toggle="dropdown" style="display:none" cat="">WTF&nbsp;&nbsp;<span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" id="WTFdiario" data-placement="right" title="Diario por CLC, como en la escuela">WTF Libro Diario</a></li>
                                <li><a class="dropdown-item" href="#" id="WTFtMayor" data-placement="right" title="Ts de Mayor, como en la escuela">WTF Ts de Mayor</a></li>
                            </ul>
                        </div>

                        <div class="btn-group me-1">
                            <button class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown" cat=""><i class="fa fa-print me-2"></i><span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item imprimir" href="clc" data-placement="right" title="Consultar la versión impresa de la CLC seleccionada">CLC Actual</a></li>
                                <li><a class="dropdown-item" href="#" id="reporte"  data-placement="right" title="Reporte de CLCs y las Pólizas Contables Vinculadas" >Relación de CLCs y Pólizas</a></li>
                            </ul>
                        </div>
                    <!-- Menu Excel -->
                    <div class="btn-group me-1">
                        <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" cat="" id="btnExcel" disabled><i class="fa fa-file-excel me-2"></i><span class="caret"></span></button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" id="reporteExcel"  data-placement="right" title="Reporte de CLCs y las Pólizas Contables Vinculadas" >Relación de CLCs y Pólizas</a></li>
                        </ul>
                    </div>

                    <a id="UsoAfectaciones" data-placement="right" title="Mostrar dónde se han usado las afectaciones relacionadas con ésta CLC" role="button" class="btn btn-outline-secondary me-1" data-bs-toggle="modal"><i class="fa fa-eye"></i></a>

                    <a id="libroDiario" data-placement="right" title="Mostrar en el Libro Diario todas las pólizas relacionadas con ésta CLC" role="button" class="btn btn-outline-secondary me-1" data-bs-toggle="modal"><i class="fa fa-book"></i></a>

                    <button id="mostrarV" data-placement="right" title="Buscar y mostrar el catálogo solicitado" class="btn btn-outline-secondary muestraPlanCuentas ladda-button me-1" data-style="expand-left" cat="" tabindex="30" disabled><i class="fa fa-search me-2"></i>Mostrar</button>
                </div>

                <div class="col-md-3 col-xs-3" style="padding: 5px 28px;">
                    <div id="filtrarEnPantalla" style="float:right;" class="input-group input-group-sm" style="width:250px">
                        <input id="buscar" tp="0" type="text" class="form-control" placeholder="Texto o importe a buscar ..." >
                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                    </div>
                </div>
                
                <span style="height:6px; display:block"></span>
            </div>';


        $this->setContenedor("toolsPantalla");
        $this->mixHtml($html);
        $this->mixStyle('.separador { margin: 8px 1px; margin-bottom: 8px; border-top: 1.5px solid rgba(var(--app-component-color-rgb), 0.5); }');


    /*------------------------------------------------------------------------
        DIBUJAMOS TABLA
    -------------------------------------------------------------------------*/
        $html = '<div id="gridVinculacion">
                    <div class="tabla-contenedor col-md-12" style="overflow: scroll; max-height: 400px;">
                        <table id="tabla-tbl-vinculacion" class="table table-striped table-bordered table-hover">
                            <div class="col-md-12 row" style="text-align:center">
                                <thead class="sticky-top" style="z-index:100;">
                                    <tr class="warning" role="row">
                                        <th class="warning" role="columnheader" style= "text-align:center">TIPO</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">CLC</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">ORI</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">CVE. FIN.</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">CLABE INTERB.</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">CONCEPTO</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">EDO</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">METODO PAGO</th>
                                        <th class="warning" role="columnheader" style= "text-align:center">IMPORTE</th>
                                       '. (($this->PermisosCompromiso == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">C</th>' : '')
                                        . (($this->PermisosDevengado == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">D</th>' : '')
                                        . (($this->PermisosEjercido == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">E</th>' : '')
                                        . (($this->PermisosMinistrado == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">M</th>' : '')
                                        . (($this->PermisosPagado == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">P</th>' : '')
                                        . (($this->PermisosPagado3ros == 1) ? '<th class="warning" role="columnheader" style= "text-align:center">PT</th>' : ''). '
                                    </tr>
                                </thead>
                            </div>
                                <tbody></tbody>
                        </table>
                    </div>  
                    
                    <div class="tabla-footer">
                        <div class="p-2 border flex-wrap"> 
                            <div class="d-flex flex-row">
                                 <div class="flex-grow-1 p-1">
                                    <label id="rA-tbl-vinculacion"></label>
                                    <label id="rB-tbl-vinculacion"></label> 
                                    <label id="rTotal-tbl-vinculacion"></label>
                                </div>
                                <div id="VinculacionPaginador" class="flex-grow-2 justify-content-center p-1" ></div>
                            </div>
                        </div>
                    </div>
                    
                    <!--
                    <div class="tabla-footer">   
                        <div class="p-2 border flex-wrap">
                            <div class="d-flex flex-row">
                                <div class="flex-grow-1 p-1">
                                    Registros del <label id="rA-tbl-vinculacion">1</label>
                                    al <label id="rB-tbl-vinculacion">0</label> 
                                    de <label id="rTotal-tbl-vinculacion">0</label>
                                </div>
                                <div class="flex-grow-2 justify-content-center p-1">
                                    <a class="btn btn-outline-secondary btn-sm first disabled btnNavFPtbl-vinculacion">Inicio</a>
                                    <a class="btn btn-outline-secondary btn-sm previous disabled btnNavFPtbl-vinculacion">Anterior</a>
                                    <span id="btnsNavegaciontbl-vinculacion" class="btn-group"></span>
                                    <a class="btn btn-outline-secondary btn-sm next disabled btnNavNLtbl-vinculacion">Siguiente</a>
                                    <a class="btn btn-outline-secondary last btn-sm disabled btnNavNLtbl-vinculacion">Final</a>
                                </div>
                                <div id="tabla_footer_sizes" class="flex-grow-1">
                                    <div class="pull-right">
                                        <div class="d-flex justify-content-end p-1" id="tabla_footer_sizes">
                                            <button class="btn btn-outline-secondary btn-sm">+</button>
                                            <button class="btn btn-outline-secondary btn-sm">-</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                <form class="pull-right">
                                    <div class="d-flex justify-content-end p-1">
                                        <small for="numFilastbl-vinculacion" class="me-1">Registros a mostrar:</small>
                                        <select id="numFilastbl-vinculacion" class="btn btn-sm btn-outline-secondary">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="-1">Todos</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>-->
                </div>';

            $html .= '<div id="myModalDocto" class="modal fade in modal-al95porciento" data-keyboard="false" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false">
                        <div class="modal-dialog"">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button id="cerrarModalDocto" type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title">Registrar Documento</h4>
                                </div>
                            <div id="bodyModalDocto" class="modal-body"></div>
                            </div>
                        </div>
                    </div>';

        $this->setContenedor("contenedor");
        $this->mixHtml($html);
         $this->mixStyle('
            .tabla-contenedor table > tbody > tr.coloreaVinculacion > td {
                background-color: #000;
                background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAoCAYAAAAPOoFWAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAPZJREFUeNq81tsOgjAMANB2ov7/7ypaN7IlIwi9rGuT8QSc9EIDAsAznxvY4pXPKr05RUE5MEVB+TyWfCEl9LZApYopCmo9C4FKSMtYoI8Bwv79aQJU4l6hXXCZrQbokJEksxHo9KMOgc6w1atHXM8K9DVC7FQnJ0i8iK3QooGgbnyKgMDygBWyYFZoqx4qS27KqLZJjA1D0jK6QJcYEQEiWv9PGkTsbqxQ8oT+ZtZB6AkdsJnQDnMoHXHLGKOgDYuCWmYhEERCI5gaamW0bnHdA3k2ltlIN+2qKRyCND0bhqSYCyTB3CAOc4WusBEIpkeBuPgJMAAX8Hs1NfqHRgAAAABJRU5ErkJggg==") repeat-x scroll left top rgb(185, 107, 107);
            }
        ');

        //Desde la pantalla de Libro Diario
        if ($mesActual != "") {
            $this->mixScript('
                $(document).ready(function() {
                    //MES
                    $("#mesActual").html( "'.$nombreMes.'" + "<span class=\"caret\"></span>" );
                    $("#mesActual").attr( "ma", "'.$mesActual.'");
                    //TIPO
                    $("#tipoClcActual").html( "'.$nombreTipo.'" + "<span class=\"caret\"></span>" );
                    $("#tipoClcActual").attr("ta","'.$tipo.'");
                    //ESTADO
                    $("#edoActual").html( "'.$nombrEdo.'" + "<span class=\"caret\"></span>" );
                    $("#edoActual").attr("ea","'.$edo.'");                 
                    //Desactivando botones
                    if($("#mesActual").attr("ma") > 0) {
                        $("#mostrarV").removeAttr("disabled");
                        $("#btnExcel").removeAttr("disabled");
                    }
                    //Carga de información
                    CargaVinculacion();
                });
                
            ');
        }
        $this->mixScript('
            //Para que el inpiut buscar se alinie a la derecha
            $("#toolsPantalla").removeClass("d-flex");

            Mousetrap.bind("w t f", function() {
                if ($("#btnWTF").attr("style").search("none") != -1) {
                    $("#btnWTF").css("display", "block");
                } else {
                    $("#btnWTF").css("display", "none");
                }
            });

            $("#mesActualSelect li").on("click", function() {
                if($("#mesActual").attr("ma") > 0) {
                    $("#btnExcel").removeAttr("disabled");
                    $("#mostrarV").removeAttr("disabled");
                }
            });

            // Mostramos los documentos que cumplan con los criterios seleccionados
            $("#mostrarV").click(function(e){
                e.preventDefault()
                
                if($("#mesActual").attr("ma") == 0) {
                    notify("Debe seleccionar el mes","warning");
                    return false;
                }

                window.espera = Ladda.create(this);   window.espera.start();   // Mostramos icono de espera ...
                $("#buscar").attr("tp","0");
                $("#buscar").attr("ps","");
                CargaVinculacion();
                window.espera.stop();
                setTimeout(function() { $("#buscar").focus() }, 100);
                e.stopPropagation();
                return false;
            });

            // Imprimir la clc actual
            $(".imprimir").click(function(e) {
                e.preventDefault();

                if ($(".seleccionado").length > 0) {
                
                    // Obtenemos el array de los datos de la linea actual
                    var rowData = [];
                    $(".seleccionado").find("td").each(function() {
                        rowData.push($(this).text());
                    });

                    var clc = rowData[1];
                    var ecl = rowData[6];
                    var ur = rowData[15];

                    let ur_actual = $("#combour").val();

                    //Se modificó el if para comprobar el nivel del usuario y su UR
                    if ((ur == ur_actual ||  "' . ($_SESSION[_S]['nvj_id'] >= 3) . '") && ecl != 20) {
                        var form = $("<form>")
                            .attr("action", "rep/doc/rep_clc.php")
                            .attr("method", "post")
                            .attr("target", "_blank")
                            .append($("<input>").attr("type", "hidden").attr("name", "clc_id").attr("value", clc))
                            .append($("<input>").attr("type", "hidden").attr("name", "scg_vc").attr("value", 1))
                            .append($("<input>").attr("type", "hidden").attr("name", "modo").attr("value", $(this).attr("href")))
                        $("body").append(form);
                        form.submit();
                    } else if (ecl == 20) {
                        notify("La CLC es inutilizable", "error");
                    } else {
                        notify("No puedes consultar CLCs de URs diferentes a la tuya", "warning");
                    }
                    }else{
                        notify("No existe ninguna CLC Actual seleccionada", "warning");
                    }
                e.stopPropagation();
            });

            // Reporte de CLCs y sus pólizas vinculadas
            $("#reporte").click(function(e) {
                e.preventDefault();
               
                if($("#mesActual").attr("ma") == 0) {
                    notify("Debe seleccionar el mes del que desea de la Relación de CLCs y las Pólizas Contables Vinculadas","warning");
                    return false;
                }

                $.ajax({
                    data : {
                        "msc"   : $("#mesActual").attr("ma"),
                        "tip"   : $("#tipoClcActual").attr("ta"),
                        "edo"   : $("#edoActual").attr("ea"),
                        "p"     : $("#buscar").attr("ps"),
                        "gpoClc": "1"
                    },
                    url:  "lib/scg/vinculacion/dameVinculacionPorClc.php",
                    type: "post",
                    success: function (response) {

                        if (response.trim().length > 0) {

                            var form = $("<form>")
                                .attr("action", "rep/doc/scg/vinculacion/rep_VinculacionPorClc.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "clc").attr("value", response))
                                .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", $("#mesActual").attr("ma")))
                            $("body").append(form);
                            form.submit();

                        }else{
                            notify("No se encontraron CLCs para la generación del reporte Relación de CLCs y las Pólizas Contables Vinculadas","warning");
                        }
                    }
                });
                e.stopPropagation();
            });

            // Reporte de CLCs y sus pólizas vinculadas Excel
            $("#reporteExcel").click(function(e) {
                e.preventDefault();
                
                if($("#mesActual").attr("ma") == 0) {
                    notify("Debe seleccionar el mes del que desea  de la Relación de CLCs y las Pólizas Contables Vinculadas","warning");
                    return false;
                }

                $.ajax({
                    data : {
                        "msc"   : $("#mesActual").attr("ma"),
                        "tip"   : $("#tipoClcActual").attr("ta"),
                        "edo"   : $("#edoActual").attr("ea"),
                        "p"     : $("#buscar").attr("ps"),
                        "gpoClc": "1"
                    },
                    url:  "lib/scg/vinculacion/dameVinculacionPorClc.php",
                    type: "post",
                    success: function (response) {

                        if (response.trim().length > 0) {

                            var form = $("<form>")
                                .attr("action", "rep/excel/scg/vinculacion/exp_VinculacionPorClc.php")
                                .attr("method", "post")
                                .attr("target", "_blank")
                                .append($("<input>").attr("type", "hidden").attr("name", "clc").attr("value", response))
                                .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", $("#mesActual").attr("ma")))
                            $("body").append(form);
                            form.submit();

                        }else{
                            notify("No se encontraron CLCs para la generación del reporte Relación de CLCs y Pólizas","warning");
                        }
                    }
                });
                e.stopPropagation();
            });

            $("#WTFdiario").click(function(e){
                e.preventDefault();

                // Si no hay una CLC seleccionada, no hacemos nada
                if (gridVinculacion.getActiveCell() == null) { return false };

                // Obtenemos el array de los datos de la linea actual
                row = gridVinculacion.getDataItem(gridVinculacion.getActiveCell().row);
                clc = row.clc;
                let ur_actual = $("#combour").val();

                if (row.ur == ur_actual) {
                    var form = $("<form>")
                        .attr("action", "rep/doc/scg/vinculacion/rep_WTFdiario.php")
                        .attr("method", "post")
                        .attr("target", "_blank")
                        .append($("<input>").attr("type", "hidden").attr("name", "clc_id").attr("value", clc))
                    $("body").append(form);
                    form.submit();
                } else {
                    notify("No puedes consultar CLCs de URs diferentes a la tuya", "warning");
                }
                e.stopPropagation();
            });


            $("#WTFtMayor").click(function(e){
                e.preventDefault();

                // Si no hay una CLC seleccionada, no hacemos nada
                if (gridVinculacion.getActiveCell() == null) { return false };

                // Obtenemos el array de los datos de la linea actual
                row = gridVinculacion.getDataItem(gridVinculacion.getActiveCell().row);
                let ur_actual = $("#combour").val();

                if (row.ur == ur_actual) {
                    comunica("lib/WTFtMayor.php", { clc: row.clc });
                } else {
                    notify("No puedes consultar CLCs de URs diferentes a la tuya", "warning");
                }
                e.stopPropagation();
            });

            $("#exportarV").click(function(){
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/vinculacion/exp_ExportaConciliacionVinculacionCLC.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "mes").attr("value", $("#mesActual").attr("ma")))
                    .append($("<input>").attr("type", "hidden").attr("name", "tip").attr("value", $("#tipoClcActual").attr("ta")))
                    .append($("<input>").attr("type", "hidden").attr("name", "edo").attr("value", $("#edoActual").attr("ea")))
                $("body").append(form);
                form.submit();
                form.remove();
            });

            $(".mesContable").click(function(e) {
                $("#mesActual").html($(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>");
                $("#mesActual").attr("ma", $(this).attr("href"));
                e.preventDefault();
            });

            $(".tipoClc").click(function(e) {
                $("#tipoClcActual").html($(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>");
                $("#tipoClcActual").attr("ta", $(this).attr("href"));
                e.preventDefault();
            });

            $(".estadoClc").click(function(e) {
                $("#edoActual").html($(this).text().trim() + "&nbsp;&nbsp;<span class=\"caret\"></span>");
                $("#edoActual").attr("ea", $(this).attr("href"));
                e.preventDefault();
            });

            /* Para filtrar el resultado en la tabla de vinculación */
            $("#buscar").on("keyup", function(e){                     
                var value = $(this).val().toLowerCase();
                $("#tabla-tbl-vinculacion tbody>tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $("#UsoAfectaciones").click(function(e) {
                notify("Esta opción no esta disponible","warning");
                return false;
            });           

            // Buscamos y cargamos los documentos que cumplan con los criterios solicitados
            // ===========================================================================
            function CargaVinculacion(l) {
                $("#tabla-tbl-vinculacion tbody").html("");
                const tbody = $("#tabla-tbl-vinculacion tbody");

                var eclFormato;
                var mpFormato;
                var cmpComprometido;
                var tclDevengado;
                var tclEjercido;
                var ministrado;
                var tclPagado;
                var tclPagadoTercero;

                dataVinculacion = [];
                dataFiltrado = [];
                $("#buscar").val("");

                var miURL = "lib/scg/vinculacion/dameVinculacionPorClc.php";

                if ($("#buscar").attr("tp") == "0") {
                    var bolPrimeraBusqueda = true;
                } else {
                    var bolPrimeraBusqueda = false;
                }
                      
                $.ajax({
                    data : {
                        "msc"   : $("#mesActual").attr("ma"),
                        "tip"   : $("#tipoClcActual").attr("ta"),
                        "edo"   : $("#edoActual").attr("ea"),
                        "p"     : $("#buscar").attr("ps")
                    },
                    url:  miURL,
                    type: "post",
                    success: function (response) {
                        var json = $.parseJSON(response);
                        /* Empezamos a partir del 1 porque en la 0 traemos el total de los Documentos */
                        for (var i = 1; i < json.length; i++) {
                                const formatoMoneda = new Intl.NumberFormat("es-MX", {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                });

                                id = i;
                                tcl = json[i].tcl;
                                clc = json[i].clc;
                                ori =  json[i].ori;
                                cf =  json[i].cf;
                                ci =  json[i].ci;
                                con = json[i].con;
                                ecl = json[i].edo;
                                mp =  json[i].mp;
                                imp = (json[i].imp == 0) ? "" : formatoMoneda.format(json[i].imp);
                                cmp = json[i].cmp;
                                ur = json[i].ur;  
                                
                                
                                switch (ecl) {
                                    case 3:
                                    case 4:
                                    case 5:
                                    case 6:
                                    case 7:
                                    case 8:
                                    case 9:
                                    case 10:
                                    case 11:
                                    case 12:
                                    case 14:
                                        eclFormato = "<span title=\"" + ecl + "\">3</span>";
                                    break;
                                default:
                                        eclFormato = "<span title=\"" + ecl + "\">" + ecl + "</span>";
                                    break;
                                }

                                switch (mp) {
                                    case 3:
                                    case 4:
                                    case 5:
                                    case 6:
                                    case 7:
                                    case 8:
                                    case 9:
                                    case 10:
                                    case 11:
                                    case 12:
                                    case 14:
                                        mpFormato = "<span title=\"" + mp + "\">3</span>";
                                    break;
                                default:
                                        mpFormato = "<span title=\"" + mp + "\">" + mp + "</span>";
                                    break;
                                }

                                /******** PARA LA COLUMNA DEL COMPROMETIDO**********/
                                var columnaC = "C";
                                let ur_actual = $("#combour").val();

                                if (ur != ur_actual) {
                                    if (cmp == 0) {
                                        cmpComprometido = "";
                                    } else {
                                        switch (columnaC) {
                                            case "C":   cmpComprometido = "";   break;
                                        }
                                    }
                                } else {
                                    if (cmp == 0) {
                                        cmpComprometido  = "";
                                    } else {
                                        var clc_solo_contables = "CDEHM";
                                        switch (columnaC) {
                                            case "C":
                                               cmpComprometido = "<a href=\"#\" title=\"Muestra los registros del Comprometido\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                            break;
                                        }
                                    }
                                }
                                /**********************************************/

                                /******** PARA LA COLUMNA DEL DEVENGADO **********/
                                var columnaD = "D";

                                if (ur != ur_actual) {
                                    if (tcl == 0) {
                                        tclDevengado = "";
                                    } else {
                                        switch (columnaD) {
                                            case "D":   tclDevengado = "";   break;
                                        }
                                    }
                                } else {
                                    if (tcl == 0) {
                                        tclDevengado  = "";
                                    } else {
                                        var clc_solo_contables = "CDEHM";
                                        switch (columnaD) {
                                            case "D":
                                                // Si son CLCs que sólo manejan cuentas contables, no mostramos nada
                                                if (clc_solo_contables.indexOf(tcl) != -1) {
                                                    tclDevengado =  "";
                                                } else {
                                                    tclDevengado = "<a href=\"#\" title=\"Muestra los registros del Devengado\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                                }
                                            break;
                                        }
                                    }
                                }
                                /**********************************************/

                                /******** PARA LA COLUMNA DEL EJERCIDO **********/
                                var columnaE = "E";

                                if (ur != ur_actual) {
                                    if (tcl == 0) {
                                        tclEjercido = "";
                                    } else {
                                        switch (columnaE) {
                                            case "E":   tclEjercido = "";   break;
                                        }
                                    }
                                } else {
                                    if (tcl == 0) {
                                        tclEjercido  = "";
                                    } else {
                                        var clc_solo_contables = "CDEHM";
                                        switch (columnaE) {
                                            case "E":
                                                // Si son CLCs que sólo manejan cuentas contables, no mostramos nada
                                                if (clc_solo_contables.indexOf(tcl) != -1) {
                                                    tclEjercido =  "";
                                                } else {
                                                    tclEjercido = "<a href=\"#\" title=\"Muestra los registros del Devengado\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                                }
                                            break;
                                        }
                                    }
                                }
                                /**********************************************/

                                /******** PARA LA COLUMNA DEL MINISTRADO **********/
                                var columnaM = "M";
                                var valueM = "1";
                                ministrado = "";

                                if( clc != ""){
                                    if (ur != ur_actual) {
                                        if ( valueM == 0) {
                                            ministrado = "";
                                        } else {
                                            switch (columnaM) {
                                                case "M":
                                                    ministrado = "<a href=\"#\" title=\"Muestra los registros del Ministrado\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                                break;
                                            }
                                        }
                                    }else{
                                        ministrado = "<a href=\"#\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                    }
                                }

                                /******** PARA LA COLUMNA DEL PAGADO **********/
                                var columnaP = "P";

                                if (ur != ur_actual) {
                                    if (tcl == 0) {
                                        tclPagado = "";
                                    } else {
                                        switch (columnaP) {
                                            case "P":   tclPagado = "";   break;
                                        }
                                    }
                                } else {
                                    switch (columnaP) {
                                        case "P":
                                            // CLCs de pagos a terceros NO muestran éste botón
                                            if (tcl == "C") {
                                                tclPagado = "";
                                            } else {
                                                tclPagado = "<a href=\"#\" title=\"Muestra los registros del Pagado\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                            }
                                        break;
                                    }
                                }
                                /**********************************************/

                                /******** PARA LA COLUMNA DEL PAGO A TERCEROS **********/
                                var columnaPT = "PT";

                                if (ur != ur_actual) {
                                    if (tcl == 0) {
                                        tclPagadoTercero = "";
                                    } else {
                                        switch (columnaPT) {
                                            case "PT":   tclPagadoTercero = "";   break;
                                        }
                                    }
                                } else {
                                    switch (columnaPT) {
                                        case "PT":
                                            // CLCs de pagos a terceros NO muestran éste botón
                                            if (tcl != "C") {
                                                tclPagadoTercero = "";
                                            } else {
                                                tclPagadoTercero = "<a href=\"#\" title=\"Muestra los registros del Pagado a Terceros\" class=\"btn btn-outline-secondary me-1\"><i class=\"visualizar\"></i></a>";
                                            }
                                        break;
                                    }
                                }
                                /**********************************************/

                            if( clc != ""){
                                tbody.append(
                                    "<tr class=\'seleccionable\'><td style = \' text-align: center; white-space: nowrap\'>" 
                                        + tcl + "</td><td style = \' text-align: left; white-space: nowrap\'>" 
                                        + clc + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                        + ori + "</td><td>" 
                                        + cf + "</td><td style = \' text-align: left; white-space: nowrap\'>"
                                        + ci + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                        + con + "</td><td style = \' text-align: center; white-space: nowrap\'>" 
                                        + eclFormato + "</td><td style = \' text-align: left; white-space: pre-wrap\'>" 
                                        + mpFormato + "</td><td style = \' text-align: right; white-space: nowrap\'>" 
                                        + imp + "</td><td id= \'C\' style = \' text-align: right; white-space: nowrap\'>" 
                                        + cmpComprometido + "</td><td id= \'D\'style = \' text-align: right; white-space: nowrap\'>" 
                                        + tclDevengado + "</td><td id= \'E\' style = \' text-align: right; white-space: nowrap\'>" 
                                        + tclEjercido + "</td><td id= \'M\' style = \' text-align: right; white-space: nowrap\'>" 
                                        + ministrado + "</td><td id= \'P\' style = \' text-align: right; white-space: nowrap\'>" 
                                        + tclPagado + "</td><td id= \'PT\' style = \' text-align: right; white-space: nowrap\'>" 
                                        + tclPagadoTercero + "</td><td style = \' text-align: right; display:none; white-space: nowrap\'>" 
                                        + ur + "</td><td style = \' text-align: right; display:none; white-space: nowrap\'>" 
                                        + cmp + "</td><td style = \' text-align: right; display:none; white-space: nowrap\'>" 
                                        + ecl + "</td></tr>");
                            }else{
                                notify("No se encontraron CLCs con los criterios seleccionados","warning");
                            }
                        }

                        $(document).ready(function() {
                            /****************************/
                            $(".visualizar").addClass("fa fa-eye");     

                            /****************************/
                            /*$("#tabla-tbl-vinculacion tbody tr").hover(
                                function() {
                                    $(this).addClass("coloreaVinculacion");
                                },
                                function() {                      
                                    $(this).removeClass("coloreaVinculacion"); 
                                }
                            );*/

                            /********************************/
                            $(".seleccionable").click(function() {                                             
                                $(".seleccionable").css("font-weight","normal");
                                $(".seleccionable").removeClass("seleccionado");
                                $(this).addClass("seleccionado");
                                $(this).css("font-weight","bold");
                            });


                        
                            /**************************/
                            $("#tabla-tbl-vinculacion td").on("dblclick", function() {
                                var columnActual = $(this).attr("id");
                                let ur_actual = $("#combour").val();
                                var ur = ur_actual;
                                var num_tcl = $(this).parents("tr").find("td").eq(0).text();
                                var num_clc = $(this).parents("tr").find("td").eq(1).text();
                                var num_ur = $(this).parents("tr").find("td").eq(15).text();
                                var num_cmp = $(this).parents("tr").find("td").eq(16).text();
                                var num_ecl = $(this).parents("tr").find("td").eq(17).text();
                                var clc_solo_contables = "CDEHM";

                                //COMPROMETIDO
                                if (columnActual == "C") {
                                    if (num_cmp != "0") {
                                        if (ur != "801" || (ur == "801" && num_ur == ur)) {
                                            if (num_clc.trim() != "") {
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorComprometido.php", {
                                                    metodo: "PonBarraBotones",
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }
                                        } else {
                                            notify("La CLC no corresponde a tu Unidad Responsable", "warning");
                                        }
                                    } else {
                                        notify("No hay información del Comprometido", "warning");
                                    }
                                }

                                //DEVENGO
                                if (columnActual == "D") {
                                    if (ur != "801" || (ur == "801" && num_ur == ur)) {
                                        if ((clc_solo_contables.indexOf(num_tcl) == -1)) {
                                            if (num_clc.trim() != "") {
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorDevengado.php", {
                                                    metodo: "PonBarraBotones",
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }
                                        } else {
                                            notify("CLCs sólo de cuentas contables no vinculan el Devengado", "warning");
                                        }
                                    } else {
                                        notify("La CLC no corresponde a tu Unidad Responsable", "warning");
                                    }
                                }

                                // EJERCIDO
                                if (columnActual == "E") {
                                    if (ur != "801" || (ur == "801" && num_ur == ur)) {
                                        if ((clc_solo_contables.indexOf(num_tcl) == -1)) {
                                            if (num_clc.trim() != "") {
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorEjercido.php", {
                                                    metodo: "PonBarraBotones",
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }
                                        } else {
                                            notify("CLCs sólo de cuentas contables no vinculan el Ejercido", "warning");
                                        }
                                    } else {
                                        notify("La CLC no corresponde a tu Unidad Responsable", "warning");
                                    }
                                }

                                // MINISTRADO
                                if (columnActual == "M") {
                                    if (num_clc.trim() != "") {
                                        window.espera = Ladda.create(this);   window.espera.start();
                                        comunica("scg/vinculacion/ControladorMinistrado.php", {
                                            metodo: "PonBarraBotones",
                                            mes: $("#mesActual").attr("ma"),
                                            clc_id: num_clc,
                                            tipo: $("#tipoClcActual").attr("ta"),
                                            edo: $("#edoActual").attr("ea"),
                                            p: $("#buscar").attr("ps"),
                                            origen: "clc"
                                        });
                                    }
                                }

                                //PAGADO
                                if (columnActual == "P") {
                                    if (num_clc.trim() != "") {
                                        if (num_tcl != "C") {
                                            if (num_ecl == 18){
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorPagado.php", {
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }else{
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorRegistroPagado.php", {
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }
                                        }
                                    }
                                }

                                // PAGOS A TERCEROS
                                if (columnActual == "PT") {
                                    if (num_clc.trim() != "") {
                                        if (num_tcl == "C") {
                                            if (num_ecl == 18){
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorPagadoTercero.php", {
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,
                                                    tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }else{
                                                window.espera = Ladda.create(this);   window.espera.start();
                                                comunica("scg/vinculacion/ControladorRegistroPagadoTercero.php", {
                                                    mes: $("#mesActual").attr("ma"),
                                                    clc_id: num_clc,tipo: $("#tipoClcActual").attr("ta"),
                                                    edo: $("#edoActual").attr("ea"),
                                                    p: $("#buscar").attr("ps"),
                                                    origen: "clc"
                                                });
                                            }
                                        }
                                    }
                                }
                            });
                        });



                        if (bolPrimeraBusqueda) {
                            $("#buscar").attr("tp", json[0].tcl);   // Guardamos el numero total de paginas en un campo de la pantalla
                        }

                        if (parseInt($("#buscar").attr("tp")) > 1) {
                            // Para que respete la paginación, definimos como página actual la última seleccionada
                            if (typeof $("#buscar").attr("upc") === "undefined") {
                                var cPage = 1
                            } else {
                                var cPage = parseInt($("#buscar").attr("upc"));
                            }
                            var options = {
                                numberOfPages: 5,
                                currentPage: cPage,
                                totalPages: $("#buscar").attr("tp"),
                                itemContainerClass: function (type, page, current) { return (page === current) ? "active" : "pointer-cursor"; },
                                onPageClicked: function(e,originalEvent,type,page){
                                    e.stopImmediatePropagation();
                                    // Grabamos la ultima página a la que le dieron click
                                    $("#buscar").attr("upc",page);
                                    // No hacemos nada si vuelven a seleccionar la misma página que ya está seleccionada
                                    const last = $("#buscar").attr("last") || 0;
                                    if ($("#VinculacionPaginador").attr("last") != page) {
                                        $("#buscar").attr("ps",page);
                                        CargaVinculacion()
                                        $("#VinculacionPaginador").attr("last", page);
                                        setTimeout(function() { $("#buscar").focus() }, 100);
                                    }
                                }
                            }
                            $("#VinculacionPaginador").bootstrapPaginator(options);
                            $("#VinculacionPaginador").addClass("pagination");
                            $("#VinculacionPaginador li").addClass("page-item")
                            $("#VinculacionPaginador li a").addClass("page-link cursor-pointer").css("margin", "0px");
                        } else {
                            $("#VinculacionPaginador").html("");
                        }

                        window.espera.stop();
                    }
                });

            };  
            
            $(".cerrar").click(function() {
                $("#myModalDocto").hide();
            });          

            // Llamamos al Libro Diario para ver todas las pólizas relacionadas con ésta CLC
            $("#libroDiario").click(function(e) {
                e.preventDefault();

                // Obtener el índice de la fila (comenzando desde 0)
                var rowIndex = $(this).index();

                // Obtener el contenido de la fila
                var rowData = [];
                $(".seleccionado").find("td").each(function() {
                    rowData.push($(this).text());
                });
                var rowData_clc = rowData[1];
                $("#buscar").attr("tp",  rowData[0]);

                if(rowData_clc){
                    comunica("scg/consulta/ControladorLibroDiario.php", {clc: rowData_clc, mesActual: $("#mesActual").attr("ma"), tipo: $("#tipoClcActual").attr("ta"), edo: $("#edoActual").attr("ea"), p: $("#buscar").attr("tp"),pantallaVin:false});
                }else{
                    notify("Debe seleccionar alguna CLC para mostrar en el Libro Diario las pólizas relacionadas","warning");
                }
            });
        ');        
    }  
    /*-------------------------------------------------------------------------------------------------------------------------------------------
        Mostraremos dónde se han usado las afectaciones de la CLC actual
    -------------------------------------------------------------------------------------------------------------------------------------------*/
    private function verUsoAfectacion()
    {
        $tabla = new stdClass();
        $tabla->id = 'tblUsoAfectacion';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaUsoAfectacion';

        $tr = array();
        $th = new stdClass();
        $th->texto = '#Afe';
        $th->width = '100px';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = '#Clc';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'EdoAfe';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'FdoRot';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Percepcion';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Retención';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Elaboración';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Autorización';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Cancelación';
        array_push($tr, $th);
        $th = new stdClass();
        $th->texto = 'Estado';
        array_push($tr, $th);

        $trbody = array();
        //contenido clave
        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'clc';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'eaf_id';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_fondoRotatorio';
        $td->align = 'center';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_percepcion';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_retencion';
        $td->formato = 'moneda';
        $td->align = 'right';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_fechaElaboracion';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_fechaAutorizado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'afe_fechaCancelado';
        array_push($trbody, $td);
        $td = new stdClass();
        $td->campoTxt = 'ecl_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->setContenedor('scpPantallaCompleta');

        $this->mixHtml('
                <div id="myModalMeta" class="modal fade modal-al95porciento in" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="false" style="display: block;">
                    <div class="modal-dialog">
                        <br /><br /><br /><br />
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close cerrar" data-dismiss="modal" aria-hidden="true">x</button>
                                <h4 class="modal-title" id="myModalLabel">Muestra dónde se han usado las afectaciones de la CLC actual</h4>
                            </div>
                            <div class="modal-body">
                                <div class="row">' . $this->wrPonTabla($tabla) . '</div>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>
            ');

        $this->mixScript('
                //$("#vclcPantallaCompleta").hide()

                Mousetrap.bind("esc esc", function(e) {
                    e.preventDefault();
                    $("#homeMenu").click();
                    $("#myModalMeta").remove();
                    e.stopPropagation();
                });

                $(".cerrar").click(function(){
                    $("#myModalMeta").remove();
                    //$("#vclcPantallaCompleta").show()
                });

            ');

        $this->llenaUsoAfectacion();
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------
Llenamos la tabla del Uso de una Afectación
-------------------------------------------------------------------------------------------------------------------------------------------*/
    private function llenaUsoAfectacion()
    {

        $clc = ((isset($this->prm->clc) && $this->prm->clc != 0) ? $this->prm->clc : 9876543210);
        $sqlParams = [
            ':gpo' => getSession('clasAdm-gpo'),
            ':ur' => getSession('clasAdm-ur'),
            ':clc' => $clc
        ]; 
        $sql = 
            "SELECT afe.afe_id, GROUP_CONCAT(DISTINCT ba.clc_id) AS clc, MAX(afe.eaf_id) AS eaf_id,
                MAX(afe_fondoRotatorio) AS afe_fondoRotatorio, MAX(afe_percepcion) AS afe_percepcion,
                MAX(afe_retencion) AS afe_retencion,
                DATE_FORMAT(MAX(afe_fechaElaboracion), '%d-%m %H:%i:%s') AS afe_fechaElaboracion,
                DATE_FORMAT(MAX(afe_fechaAutorizado), '%d-%m %H:%i:%s') AS afe_fechaAutorizado,
                DATE_FORMAT(MAX(afe_fechaCancelado), '%d-%m %H:%i:%s') AS afe_fechaCancelado,
                0, ecl.ecl_nombre
            FROM ctrl{$this->ejercicio}Bitacora.bit_Afectacion ba
            JOIN scp_Afectacion afe ON ba.gpo_id = afe.gpo_id AND ba.ur_id = afe.ur_id AND ba.afe_id = afe.afe_id
            JOIN scp_Clc clc ON ba.gpo_id = clc.gpo_id AND ba.ur_id = clc.ur_id AND ba.clc_id = clc.clc_id
            JOIN
            (
                SELECT gpo_id, ur_id, afe_id
                FROM scp_Afectacion
                WHERE gpo_id = :gpo
                    AND ur_id = :ur
                    AND clc_id = :clc
            ) sca ON ba.gpo_id = sca.gpo_id AND ba.ur_id = sca.ur_id AND ba.afe_id = sca.afe_id
            JOIN cat_EstadoClc ecl USING(ecl_id)
            WHERE ba.gpo_id = :gpo
                AND ba.ur_id = :ur
                AND ba.clc_id <> 0
            GROUP BY ba.afe_id, ba.clc_id
            ORDER BY ba.afe_id, ba.clc_id;";

        $this->bindParams($sqlParams);
        $this->setSql($sql);
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->idTabla = 'tblUsoAfectacion';
        $nav->nf = '999';   // No usaremos paginación en ésta pantalla
        $this->wrLlenaTabla($nav);

        $this->mixScript('$(".panel-heading.header").remove();
                    $(".panel-footer").remove();
                    $(".tabla-footer").remove();');
    }
}

// permisos del usuario que puede ver este controlador
if (isset($_SESSION[_S]['prm']['C']['5']['10']['1'])) {
    new ControladorVinculacionPorClc();
} else {
    WebResponse::sinPermisos();
}