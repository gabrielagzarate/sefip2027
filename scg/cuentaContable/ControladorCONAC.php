<?php
/*
Controlar los catálogos de la CONAC, que arman la cuenta de mayor de una cuenta contable:
GENERO + GRUPO + RUBRO + CUENTA + SUBCUENTA
 */

//inicio de sesion y llamada a las librerias
declare (strict_types = 1);
session_start();
require_once dirname(__FILE__) . '/../../lib/constantes.php';
require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/Genero.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/Rubro.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/CgGrupo.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/CgCuenta.php';
require_once dirname(__FILE__) . '/../../ent/scg/cuentaContable/CgSubcuenta.php';

class ControladorCONAC extends WebResponse
{

    public $lectura;
    public $escritura;
    public $usr_id;
    public $metodo;

    //Constructor
    public function __construct()
    {

        // Como no es una pantalla que se llame de otra, sino sólo se llama desde el menú, mando el $_POST
        $this->lectura = $this->tienePermisos(TAREA_LECTURA, $_POST);
        $this->escritura = $this->tienePermisos(TAREA_ESCRITURA, $_POST);
        $this->usr_id = $_SESSION[_S]['usr_id'];
        //mandar a llamar constructor base
        parent::__construct();

        switch ($this->metodo) {
            case "dibujaPantalla":
                $this->dibujaPantalla();
                break;
            case "PreparaVentanaCaptura":
                $this->PreparaVentanaCaptura();
                break;
            case "llenaCombo":
                $this->llenaCombo();
                break;
            case "PonClaveEnCombos":
                $this->PonClaveEnCombos();
                break;
            case "muestraDatos":
                $this->muestraDatos();
                break;
            case "modCONAC":
                if ($this->escritura) {
                    $this->modCONAC();
                }
                break;
            case "addCONAC":
                if ($this->escritura) {
                    $this->addCONAC();
                }
                break;
            case "eliminar":
                if ($this->escritura) {
                    $this->eliminar();
                }
                break;
            case "PonBarraBotones":
                    $this->PonBarraBotones();
                    break;
            default:
                $this->main();//Se sustituye main quien dibujará la estructura inicial
                break;
        }
        $this->returnJson();
    }

    //Se crear función para la pantalla inicial
    public function main()
    {

        $this->ponBarraBotones();
        
        $this->setContenedor('contenedor');
        
    }

    public function PonBarraBotones()
    {
        $botones = '
            ' . (($this->escritura) ? '
            <button id="adicionar" title="Adicionar registro en el catálogo actual" type="button" class="btn btn-outline-primary me-1"><i class="fa-solid fa-plus me-2"></i>Nuevo</button>
            <button data-placement="bottom" id="modificar" title="Modificar el registro actual" type="button" class="btn btn-outline-secondary me-1"><i class="fa fa-pencil me-2"></i>Modificar</button>
            <button id="eliminar" title="Eliminar concepto actual (sólo si aún no se ha usado)" class="btn btn-outline-danger me-1"><i class="fa-solid fa-trash me-2"></i>Eliminar</button>
            ' : '') . '
                <button id="imprimir" title="Imprimir catálogo actual" class="btn btn-outline-danger me-1" cat=""><i class="fa-solid fa-file-pdf me-2"></i>PDF</button>
                <button id="excel" title="Exportar a Excel catálogo actual" class="btn btn-outline-success me-1" cat=""><i class="fa-solid fa-file-excel me-2"></i>Excel</button>
                <div class="input-group" style="float:right;position:absolute;right:100px; width:200px">
                    <input id="buscar" type="text" class="form-control" placeholder="Texto a buscar ..." >
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
                <span style="height:6px;"></span>
        ';

        $this->setContenedor("toolsPantalla");
        $this->mixHtml($botones);

        $this->mixScript('
            $("nav").hide();
            $(".modulos").hide();' .
            (($_SESSION[_S]['nvj_id'] < 3) ? '$(".cabecera").hide(); $("#toggleCA").hide(); '
                : '') . '
            $("#sticky-anchor").show();
            $("#sticky").show();
            $("#mainContainer").css("margin-top", "");
            comunica("' . $this->pantalla . '",{
                "metodo":"dibujaPantalla"
            });
            if ( $( ".container" ).css( "max-width" ) == "1170px" ) {
                $( ".container" ).css( "max-width", "100%" );
            }
            $("#otraInfoFooter").attr( "original", $("#otraInfoFooter").text() );
            $("#otraInfoFooter").text("UR ACTUAL : ' . $_SESSION[_S]['clasAdm-gpo'] . $_SESSION[_S]['clasAdm-ur'] . '" );
            $(document).on("click", "#homeMenu", function (e) {
                e.preventDefault();
                $("nav").show();
                $(".modulos").show();
                $("#tituloPantalla").show();
                $("#sticky-anchor").hide();
                $("#sticky").hide();
                $(".cabecera").show();
                $("#mainContainer").css( "margin-top","40px" );
                // Limpiamos los Dialog() de ayuda de cuentas y proveedores, para que no se bloqueen x acumularse varios
                $("div[aria-describedby=cargaDatosMayor]").html("");
                $("#ayuda").addClass("gris");
                $("#tip").addClass("gris");
                $(document).off("click", "#homeMenu" );
                $("nav").menusinpres("clickMenu");
            });

            $("#imprimir").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/doc/scg/reporte.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $("#imprimir").attr("rep") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    $("body").append(form);
                    if ($("#imprimir").attr("rep") == undefined) {
                        notify("Seleccionar Nivel para imprimir","warning");
                        return false;
                    }
                form.submit();
            });

            $("#excel").click( function(e) {
                var form = $("<form>")
                    .attr("action", "rep/excel/scg/cuentaContable/exp_Conac.php")
                    .attr("method", "post")
                    .attr("target", "_blank")
                    .append($("<input>").attr("type", "hidden").attr("name", "sis_id").attr("value", "C" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "men_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "opc_id").attr("value", "1" ))
                    .append($("<input>").attr("type", "hidden").attr("name", "seccion").attr("value", $("#imprimir").attr("rep") ))
                    .append($("<input>").attr("type", "hidden").attr("name", "buscar").attr("value", $("#buscar").val() ))
                    $("body").append(form);
                    if ($("#imprimir").attr("rep") == undefined) {
                        notify("Seleccionar Nivel para imprimir","warning");
                        return false;
                    }
                form.submit();
            });
        ');

        if ($this->escritura) {
            $this->mixScript('
                $("#modificar").click( function(e) {
                    e.preventDefault();
                    // Si NO está seleccionado ningún catálogo, no hacemos nada
                    if ( $("#imprimir").attr("cat") == undefined || $("#imprimir").attr("cat") == "" ) {
                        notify("Selecciona el nivel CONAC", "warning");
                        return false;
                    }
                    // Obtenemos el array de los datos de la linea actual
                     row = $("#tbl-conac").tabla("getFilaSeleccion");

                    if (!row) { e.preventDefault(); return false;};
                    
                    if (row) {
                        const modal = new bootstrap.Modal(document.getElementById("myModal"));
                        modal.show();
                        $("#grabar").attr("op", "mod");
                        comunica("' . $this->pantalla . '",{
                            "metodo":"PreparaVentanaCaptura",
                            "cat": $("#imprimir").attr("cat" ),
                            "opc": "mod",
                            "id": row.cve,
                            "c": row.cve,
                            "n": row.nom,
                            "des":row.des
                        });
                        comunica("' . $this->pantalla . '",{
                            "metodo":"PonClaveEnCombos",
                            "cat": $("#imprimir").attr("cat" ),
                            "clave":  row.cve,
                            "opc":"mod"
                        });
                    }
                    $("#modificar").addClass("disabled");
                });

                $("#adicionar").click( function(e) {
                    // Si NO está seleccionado ningún catálogo, no hacemos nada
                    if ( $("#imprimir").attr("cat") == undefined || $("#imprimir").attr("cat") == "" ) {
                        notify("Selecciona el nivel CONAC", "warning");
                        return false;
                    }
                    // Obtenemos el array de los datos de la linea actual
                    // Si es un valor editable ...
                    $("#grabar").attr("op", "add");
                        const modal = new bootstrap.Modal(document.getElementById("myModal"));
                        modal.show();
                        comunica("' . $this->pantalla . '",{
                        "metodo":"PreparaVentanaCaptura",
                        "cat": $("#imprimir").attr("cat" ),
                        "opc": "add"
                    });
                });

                $("#eliminar").click(function(e){
                    // Obtenemos el array de los datos de la linea actual
                    e.preventDefault();
                    const row = $("#tbl-conac").tabla("getFilaSeleccion");
                    /* console.log(row); */
                    if (!row) { e.stopPropagation(); return false };
                    // Si es un valor editable ...
                    if (row) {
                        try {
                            alertify.confirm("ELIMINAR REGISTRO","Se eliminará DEFINITIVAMENTE el registro <br><strong>" + row.cve + " : " + row.nom + "</strong> <br>¿Confirma que desea eliminarlo?",
                                function(){
                                    comunica( "'.$this->pantalla.'", { 
                                        metodo:"eliminar",
                                        tabla:$("#imprimir").attr("cat"),
                                        c:row.cve,
                                        id:row.cve 
                                    });
                                }, 
                                function(){ }
                            ).set("labels",{ok:"ELIMINAR",cancel:"Cancelar Operación"});
                        } catch(e) {
                            alert( e );
                        }
                    } else {
                        e.preventDefault();
                        return false;
                    }
                    $("#eliminar").addClass("disabled");
                });
            ');
        }
    }

    public function dibujaPantalla()
    {   //Se agrega tabla a la pantalla
        $html = $this->getTablaConac();
        $this->setContenedor("contenedor");

        $this->mixHtml('
            <div id="page-tabs">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class ="nav-link active" data-bs-toggle="tab" href="ger"> GÉNERO </a>
                    </li>
                    <li class="nav-item">
                        <a class ="nav-link" data-bs-toggle="tab" href="cgp"> GRUPO </a>
                    </li>
                    <li class="nav-item">
                        <a class ="nav-link" data-bs-toggle="tab" href="rub"> RUBRO </a>
                    </li>
                    <li class="nav-item">
                        <a class ="nav-link" data-bs-toggle="tab" href="cgcu"> CUENTA </a>
                    </li>
                    <li class="nav-item">
                        <a class ="nav-link" data-bs-toggle="tab" href="cgsc"> SUBCUENTA </a>
                        <div class="last"></div>
                    </li>
                </ul>
            </div><br>');

        $this->dibujaVentanaCaptura();
        $this->mixHtml($html);

        $this->mixScript('
            $("#tituloPantalla").hide();
            // Para que el GRID se ajuste al espacio disponible en pantalla
            $("#myGrid").on("mouseenter", function(){
                $(this).css("overflow", "initial");
             });
            // -------------------------------------------------------------------------------------

            //Mostramos el listado del Género
            $(document).ready(function() {
                comunica("' . $this->pantalla . '",{
                    "metodo":"muestraDatos",
                     tabla: "ger"
                });
            });       

            $(".nav-link").on("click", function(e) {
                e.stopPropagation();

                const seleccionado = $(this);
                const seleccion = seleccionado.attr("href");

                $("#page-tabs li").removeClass("active");

                seleccionado.closest("li").addClass("active");

                comunica("' . $this->pantalla . '",{
                    "metodo":"muestraDatos",
                     tabla: seleccion
                });

                 $("#imprimir")
                     .attr("cat", seleccion )
                     .attr("rep", seleccionado.text());
                
                $("#modificar").addClass("disabled");
                $("#eliminar").addClass("disabled");
                e.stopPropagation();
                return false;


                return false;
            });

            var sortcol = "CLAVE";
            var sortdir = 1;
            var searchString = "";

            function myFilter(item, args) {
                if ( item["d"] === null || item["c"] === null ) {
                    return false
                }
                // Si empieza con numero ... buscamos en la cuenta
                strNumeros = "0123456789";
                if ( args.searchString != "" && strNumeros.indexOf( args.searchString.substring(0,1) ) !== -1 ) {
                    return ( item["c"].substring(0,args.searchString.length) == args.searchString );
                // si no ... buscamos en el texto
                } else {
                    if (args.searchString != "" && item["d"].toLowerCase().indexOf(args.searchString) == -1 ){
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
                // Sólo si es número preparamos la cadena ...
                var esNumero = ( this.value.match(/^[0-9]+$/) != null );
                if ( esNumero ) {
                    searchString = preparaCuentaContable( this.value.toLowerCase(), 1 );
                } else {
                    searchString = this.value.toLowerCase();
                }
                updateFilter();
            });

            function updateFilter() {
                dataView.setFilterArgs({ searchString: searchString });
                dataView.refresh();
            }

            var dataView;
            var grid;
            var data = [];
            var columns = [
                {id: "c",  name: "CLAVE",  field: "c", maxWidth: 100, minWidth: 100, sortable: true },
                {id: "d",  name: "NOMBRE", field: "d", minWidth: 600, sortable: true },
                {id: "nat", name: "NATURALEZA", field: "nat", minWidth: 120,sortable: true },
                {id: "str", name: "ESTRUCTURA", field: "str", minWidth: 120, sortable: true },
                {id: "t",   name: "TIPO", field: "t", minWidth: 100, sortable: true },
                {id: "p",   name: "POSICION", field: "p", minWidth: 100,sortable: true }
            ];

            var options = {
                enableCellNavigation: true,
                enableColumnReorder: false,
                forceFitColumns: true
                /*autoHeight:true
                esto y la funcion $("#myGrid").height() arriba controlan si el grid se limita o no a la pantalla*/
            };

            // Cargamos la información a mostrar
            // ---------------------------------------------------------------------------------------------------
            function loadData( json ) {

                data = [];
                json = json;

                for (var i = 0; i < json.length; i++) {
                    var d = (data[i] = {});
                    d["id"] = json[i].id;
                    d["c"]  = json[i].c;
                    d["d"]  = json[i].d;
                    d["des"]  = json[i].des;
                    d["nat"]  = json[i].nat;
                    d["str"]  = json[i].str;
                    d["t"]  = json[i].t;
                    d["p"]  = json[i].p;
                }
                dataView.setItems(data);
            }
            $("#tbody-tabla-tbl-conac").on("click", "tr.seleccionable", function () {
                if ($(this).hasClass("seleccionado")){
                    $("#modificar").removeClass("disabled");
                    $("#eliminar").removeClass("disabled");
                }
        });

            dataView = new Slick.Data.DataView( { inlineFilters: false });
        ');
    }


/* Se agrega función para crear la estructura de la tabla principal*/

private function getTablaConac()
{
    $tabla = new stdClass();
    $tabla->esCompacta = true;
    $tabla->inputFiltro = "buscar";
    $tabla->id = 'tbl-conac';
    $tabla->seleccionable = true;
    $tabla->comunica = new stdClass();
    $tabla->comunica->pantalla = $this->pantalla;
    $tabla->comunica->metodo = 'muestraDatos';

    $tr = array();
    $th = new stdClass();
    $th->texto = 'CLAVE';
    $th->width = '90px';
    array_push($tr, $th);

    $th = new stdClass();
    $th->texto = 'NOMBRE';
    $th->width = '90px';
    array_push($tr, $th);

    $th = new stdClass();
    $th->texto = 'NATURALEZA';
    $th->width = '90px';
    array_push($tr, $th);

    $th = new stdClass();
    $th->texto = 'ESTRUCTURA';
    $th->width = '90px';
    array_push($tr, $th);

    $th = new stdClass();
    $th->texto = 'TIPO';
    $th->width = '90px';
    array_push($tr, $th);

    $th = new stdClass();
    $th->texto = 'POSICIÓN';
    $th->width = '90px';
    array_push($tr, $th);

    $trbody = array();

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['cve']; 
    array_push($trbody, $td);

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['nom'];
    array_push($trbody, $td);

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['nat'];
    array_push($trbody, $td);

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['str'];
    array_push($trbody, $td);

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['t'];
    array_push($trbody, $td);

    $td = new stdClass();
    $td->campoTxt = new stdClass();
    $td->campoTxt->campos = ['p']; 
    array_push($trbody, $td);

    $tabla->encabezado = array($tr);
    $tabla->cuerpo = $trbody;
    return $this->wrPonTabla($tabla);
}

    /* Regresamos en formato JSON el catalogo CONAC de la tabla actualmente seleccionada
    ---------------------------------------------------------------------------------------------------- */

    private function DameCatalogoCONAC($sis_id, $ayd_id)
    {

        switch ($sis_id) {
            case "cgp":
                $sql = 'SELECT cgp_cuenta AS cve, cgp_nombre AS nom,cgp_descripcion as des,"" AS nat, "" AS str, "" AS t, "" AS p FROM '.BD_CTRL.'.cat_CgGrupo ORDER BY ger_id, cgp_id;';
                break;
            case "rub":
                $sql = 'SELECT rub_cuenta AS cve, rub_nombre AS nom,rub_descripcion as des,"" AS nat, "" AS str, "" AS t, "" AS p FROM '.BD_CTRL.'.cat_Rubro ORDER BY ger_id, cgp_id, rub_id;';
                break;
            case "cgcu":
                $sql = 'SELECT cgcu_cuenta AS cve, cgcu_nombre AS nom,cgcu_descripcion as des,"" AS nat, "" AS str, "" AS t, "" AS p FROM '.BD_CTRL.'.cat_CgCuenta ORDER BY ger_id, cgp_id, rub_id, cgcu_id;';
                break;
            case "cgsc":
                $sql = 'SELECT cgsc_cuenta AS cve, cgsc_nombre AS nom,cgsc_descripcion as des,CASE nat_id WHEN "D" THEN "Deudora" ELSE "Acreedora" END AS nat,
                        CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS str,CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS t,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS p
                            FROM '.BD_CTRL.'.cat_CgSubcuenta
                                INNER JOIN '.BD_CTRL.'.cat_EstructuraCuenta USING (ecu_id)
                                INNER JOIN '.BD_CTRL.'.cat_TipoCuenta USING (tcu_id)
                                INNER JOIN '.BD_CTRL.'.cat_PosicionCuenta USING (pcu_id)
                                    ORDER BY ger_id, cgp_id, rub_id, cgcu_id, cgsc_id;';
                break;
            // Por default, genero
            default:
                $sql = 'SELECT ger_cuenta AS cve, ger_nombre AS nom,ger_descripcion as des,"" AS nat, "" AS str, "" AS t, "" AS p FROM '.BD_CTRL.'.cat_Genero ORDER BY ger_id;';
                break;
        }

        // En el ejercicio 2012 los campos se llamaban distinto ... lo ajustamos aqui
        if (APP_EJERCICIO == "2012") {
            $sql = str_replace("_nombre", "_descripcion", $sql);
        }

        $this->conectar();
        $this->setSql($sql);
        
        //crear variable nav
        
        $nav = (isset($this->prm->nav)) ? $this->prm->nav : new stdClass();
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : -1;
        $nav->idTabla = 'tbl-conac';

        $this->mixScript('
            $(".panel-heading.header").remove();
            $(".panel-footer").remove();
            $(".tabla-footer").remove();'
        );

        if (($this->wrLlenaTabla($nav) == 0) && ($this->prm->gpo_id != '' && $this->prm->ur_id != '')) {
            $this->mixScript('$("#buscar").click();');
        }
        
        $nRegistros = $this->getNumeroRegistros();

        if ($nRegistros <= 0) {
            return json_encode(array('id' => '0', 'c' => '', 'd' => '', 'des' => ''));
        } else {

            $data = array();
            while ($row = $this->extraer()) {
                array_push($data,
                    array(
                        'id' => $row['cve'],
                        'c' => $row['cve'],
                        'd' => $row['nom'],
                        'des' => $row['des'],
                        'nat' => $row['nat'],
                        'str' => $row['str'],
                        't' => $row['t'],
                        'p' => $row['p'],
                    ));
            }

            return json_encode($data);
        }
    }
    /* Mostramos la información de un sistema específico
    ---------------------------------------------------------------------------------------------------- */

    private function muestraDatos()
    {

        $data=$this->DameCatalogoCONAC($this->prm->tabla, null);

        return false;
    }
    /* Eliminamos un concepto de un catálogo específico
    La idea es dejarle a la base de datos checar si se ha usado o no ... a traves el error en caso de que
    falle alguna foreign key
    ---------------------------------------------------------------------------------------------------- */

    private function eliminar()
    {

        $cta = $this->prm->c;
        $clasecuenta = '';
        switch ($this->prm->tabla) {
            case "cgp":
                $arrCta = explode(".", $cta);
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $sql = 'DELETE FROM '.BD_CTRL.'.cat_CgGrupo WHERE cgp_cuenta ="' . $this->prm->c . '"';
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->abc = 'b';
                $clasecuenta = new CgGrupo();
                $sql2 = 'SELECT ger_id from '.BD_CTRL.'.cat_Rubro WHERE ger_id =? AND cgp_id=? LIMIT 1;';
                $parametros = [$genero,$grupo];
                break;
            case "rub":
                $arrCta = explode(".", $cta);
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $rubro = $arrCta[2];
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->abc = 'b';
                $clasecuenta = new Rubro();
                $sql2 = '
                    SELECT ger_id from '.BD_CTRL.'.cat_CgCuenta
                    WHERE ger_id = ? AND cgp_id= ?
                    AND  rub_id=? LIMIT 1;';
                $parametros = [$genero,$grupo,$rubro];
                break;
            case "cgcu":
                $arrCta = explode(".", $cta);
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $rubro = $arrCta[2];
                $cuenta = $arrCta[3];
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->cgcu_id = $cuenta;
                $entidad->abc = 'b';
                $clasecuenta = new CgCuenta();
                $sql2 = '
                    SELECT ger_id from '.BD_CTRL.'.cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id= ?
                    AND  rub_id=? AND cgcu_id =? LIMIT 1;';
                $parametros = [$genero,$grupo,$rubro,$cuenta];
                break;
            case "cgsc":
                $arrCta = explode(".", $cta);
                $genero = $arrCta[0];
                $grupo = $arrCta[1];
                $rubro = $arrCta[2];
                $cuenta = $arrCta[3];
                $subcuenta = $arrCta[4];
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->cgp_id = $grupo;
                $entidad->rub_id = $rubro;
                $entidad->cgcu_id = $cuenta;
                $entidad->cgsc_id = $subcuenta;
                $entidad->abc = 'b';
                $clasecuenta = new CgSubcuenta();
                $sql2 = "
                    SELECT ger_id FROM (
                        (SELECT ger_id FROM ".BD_CTRL.".cg_CuentaCuarto WHERE ger_id = ? AND cgp_id=?
                            AND  rub_id=? AND cgcu_id = ? AND cgsc_id= ? LIMIT 1)
                        UNION
                            (SELECT ger_id FROM ".BD_CTRL.".vin_Cog WHERE ger_id = ? AND cgp_id= ?
                                AND  rub_id=1 AND cgcu_id =? AND cgsc_id= ? LIMIT 1)
                            )a LIMIT 1;";
                    $parametros = [$genero,$grupo,$rubro,$cuenta,$subcuenta,$genero,$grupo,$cuenta,$subcuenta];
                break;
            // Por default, genero
            default:
                $arrCta = explode(".", $cta);
                $genero = $arrCta[0];
                $entidad = new stdClass();
                $entidad->ger_id = $genero;
                $entidad->abc = 'b';
                $clasecuenta = new Genero();
                $sql2 = 'SELECT ger_id from '.BD_CTRL.'.cat_CgGrupo WHERE ger_id =? LIMIT 1;';
                $parametros = [$genero];
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        //Consultamos si existen registro en un nivel superior
        $numreg = 0;
        $conexion->setSql($sql2);
        $conexion->consultar($parametros);
        $numreg = $conexion->getNumeroRegistros();

        $conexion->desconectar();
        if ($numreg > 0) {
            $this->mixScript('
                notify("No se puede eliminar el registro seleccionado", "error" );
            ');
            return false;
        }

        $clasecuenta->abc($entidad);
        $this->mixScript('
            notify("Se ha eliminado el registro solicitado", "success" );
            comunica("' . $this->pantalla . '",{"metodo":"muestraDatos", "tabla": "'.$this->prm->tabla.'"});
        ');
        return false;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla BASICA donde modificaremos los datos del catálogo actual
// ( Lo hago así para que exista el DIV y el #id cuando se dibuje la pantalla )
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dibujaVentanaCaptura()
    {

        // Para ajustar un poco los espacios ... porque los veo muy separados
        $this->mixStyle('
            .form-group{margin-bottom:12px!important}
            legend{font-size:18px!important;margin-bottom:10px!important}
            .modal-header{padding:8px!important; background: #B6C2C9; color:black;}
            .modal-footer{padding:10px 20px!important}
        ');

        $this->mixHtml('
            <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #9d2449;">
                            <h5 class="modal-title">Modal title</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        </div>
                        <div class="modal-footer">
                            <button id="grabar" class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i>Grabar</button>
                        </div>
                    </div>
                </div>
            </div>
        ');

        $this->mixScript('
            $("#myModal").on("hide.bs.modal", function () {
                $("#addModCONAC").remove();
            });

            $("#grabar").click( function(e) {
                if ( $("#grabar").attr("op") == "mod" ) {
                    // Obtenemos el array de los datos de la linea actual (que es la que estamos editando)
                    const row = $("#tbl-conac").tabla("getFilaSeleccion");
                    comunica("' . $this->pantalla . '",{
                        "metodo":"modCONAC",
                        "cat": $("#imprimir").attr("cat" ),
                        "c": $("#clave").val(),
                        "gen": $("#genero").val(),
                        "grup": $("#grupo").val(),
                        "rub": $("#rubro").val(),
                        "cta": $("#cuenta").val(),
                        "n": $("#nombre").val(),
                        "nt": $("#naturaleza").val(),
                        "es": $("#estructura").val(),
                        "ef": $("#edoFin").val(),
                        "po": $("#posicion").val(),
                        "des":$("#descripcion").val(),
                        "id": row.cve,
                        "cuentacomp": row.cve
                    });
                } else {
                    comunica("' . $this->pantalla . '",{
                        "metodo":"addCONAC",
                        "cat": $("#imprimir").attr("cat" ),
                        "c": $("#clave").val(),
                        "gen": $("#genero").val(),
                        "grup": $("#grupo").val(),
                        "rub": $("#rubro").val(),
                        "cta": $("#cuenta").val(),
                        "n": $("#nombre").val(),
                        "des":$("#descripcion").val(),
                        "nt": $("#naturaleza").val(),
                        "es": $("#estructura").val(),
                        "ef": $("#edoFin").val(),
                        "po": $("#posicion").val()
                    });
                }
            });
        ');
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla ESPECIFICA donde modificaremos los datos del catálogo actual
// Lo hago así porque dependiendo del catálogo es como se dibujará y se comportará la forma
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function PreparaVentanaCaptura()
    {
        $strId = ((isset($this->prm->id)) ? $this->prm->id : "");
        $strCve = ((isset($this->prm->c)) ? $this->prm->c : "");
        $strNom = ((isset($this->prm->n)) ? $this->prm->n : "");
        $strDes = ((isset($this->prm->des)) ? $this->prm->des : "");
        $arrCta = explode(".", $strCve);
        $genero = (isset($arrCta[0]) ? $arrCta[0] : "");
        $grupo = (isset($arrCta[1]) ? $arrCta[1] : "");
        $rubro = (isset($arrCta[2]) ? $arrCta[2] : "");
        $cuenta = (isset($arrCta[3]) ? $arrCta[3] : "");
        $subcuenta = (isset($arrCta[4]) ? $arrCta[4] : "");
        $maxlengthnombre = 0;
        $maxlengthdescripcion = 0;
        // En el 2016, tomaremos el campo _cuenta para mostrar en pantalla... y está separado por puntos
        // para la edición le quitaremos los puntos
        $strCve = str_replace(".", "", $strCve);

        switch ($this->prm->cat) {
            case "cgp":
                $maxCompleta = 1;
                $maxlengthnombre = 85;
                $maxlengthdescripcion = 413;
                $strCve = $grupo;
                break;
            case "rub":
                $maxCompleta = 2;
                $maxlengthnombre = 141;
                $maxlengthdescripcion = 652;
                $strCve = $rubro;
                break;
            case "cgcu":
                $maxCompleta = 2;
                $maxlengthnombre = 200;
                $maxlengthdescripcion = 591;
                $strCve = $cuenta;
                break;
            case "cgsc":
                $maxCompleta = 2;
                $maxlengthnombre = 150;
                $maxlengthdescripcion = 652;
                $strCve = $subcuenta;
                if ($this->prm->opc == "mod") {
                    $conexion = new AccesoDatos();
                    $conexion->conectar();
                    $sql2 = '
                        SELECT nat_id,ecu_id,tcu_id,pcu_id from '.BD_CTRL.'.cat_CgSubcuenta
                        WHERE ger_id = ? AND cgp_id= ?
                        AND  rub_id= ? AND cgcu_id = ?
                        AND cgsc_id= ? LIMIT 1;';
                    $parametros = [$genero,$grupo,$rubro,$cuenta,$subcuenta];
                    $conexion->setSql($sql2);
                    $conexion->consultar($parametros);
                    $datoscuenta = $conexion->extraer();
                    $naturaleza = $datoscuenta['nat_id'];
                    $estructura = $datoscuenta['ecu_id'];
                    $edofinan = $datoscuenta['tcu_id'];
                    $posicion = $datoscuenta['pcu_id'];
                    $conexion->desconectar();
                    unset($conexion);
                }
                break;
            // Por default, genero
            default:
                $maxCompleta = 1;
                $maxlengthnombre = 32;
                $maxlengthdescripcion = 325;
                $strCve = $genero;
                break;
        }

        $cmbGer = $this->dameCombo("genero", "Selecciona el Género", "",
            "SELECT ger_id, ger_" . ((APP_EJERCICIO == "2012")
                ? "descripcion" : "nombre") . " FROM ".BD_CTRL.".cat_Genero ORDER BY ger_id;");
        $cmbCgp = $this->dameComboVacio("grupo", "Selecciona el Grupo");
        $cmbRub = $this->dameComboVacio("rubro", "Selecciona el Rubro");
        $cmbCgcu = $this->dameComboVacio("cuenta", "Selecciona la Cuenta");

        // Naturaleza
        $cmbNat = $this->dameCombo("naturaleza", "Selecciona  Naturaleza", "",
            "SELECT nat_id,nat_nombre FROM ".BD_CTRL.".cat_NaturalezaCuenta ");
        // Estructura
        $cmbEst = $this->dameCombo("estructura", "Selecciona  Estructura", "",
            "SELECT ecu_id,ecu_nombre FROM ".BD_CTRL.".cat_EstructuraCuenta ");
        // Estado Financiero
        $cmbEdoFin = $this->dameCombo("edoFin", "Selecciona  Edo. Financiero",
            "", "SELECT tcu_id,tcu_nombre FROM ".BD_CTRL.".cat_TipoCuenta ");
        // Posición en el Estado Financiero
        $cmbPos = $this->dameCombo("posicion", "Selecciona  Posición", "",
            "SELECT pcu_id,pcu_nombre FROM ".BD_CTRL.".cat_PosicionCuenta ");
        $html = '<form action="" class="" id="AddMod">' .
            ((strpos("[cgp.rub.cgcu.cgsc]", $this->prm->cat) > 0) ? '
            <div class="row">
                <fieldset class="col-xs-2"><legend></legend></fieldset>
                <fieldset class="col-md-8 col-xs-8"><legend>Selecciona la Clasificación</legend></fieldset>
            </div><br />' : '') .
            ((strpos("[cgp.rub.cgcu.cgsc]", $this->prm->cat) > 0) ? '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2"><h5>Genero</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbGer . '</fieldset>
            </div><br />' : '') .
            ((strpos("[rub.cgcu.cgsc]", $this->prm->cat) > 0) ? '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2"><h5>Grupo</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbCgp . '</fieldset>
            </div><br />' : '') .
            ((strpos("[cgcu.cgsc]", $this->prm->cat) > 0) ? '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2"><h5>Rubro</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbRub . '</fieldset>
            </div><br />' : '') .
            ((strpos("[cgsc]", $this->prm->cat) > 0) ? '
            <div class="row">
                <fieldset class="col-md-2 col-xs-2"><h5>Cuenta</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbCgcu . '</fieldset>
            </div><br />

            <div class="row">
                <fieldset class="col-md-3 col-xs-3"><h5>Naturaleza</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbNat . '</fieldset>
            </div><br />
            <div class="row">
                <fieldset class="col-md-3 col-xs-3"><h5>Estructura</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbEst . '</fieldset>
            </div><br />
            <div class="row">
                <fieldset class="col-md-3 col-xs-3"><h5>Edo. Financiero</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbEdoFin . '</fieldset>
            </div><br />
            <div class="row">
                <fieldset class="col-md-3 col-xs-3"><h5>Posición</h5></fieldset>
                <fieldset class="col-md-8 col-xs-8">' . $cmbPos . '</fieldset>
            </div><br />

            ' : '') . '
            <div class="row">
                <fieldset class="col-md-3 col-xs-3">
                    <legend>Clave</legend>
                    <div class="form-group ">
                        <input type="text" name="clave" cve="' . $strId . '" placeholder="Clave" maxlength="' . $maxCompleta . '" value="' . $strCve . '" id="clave" class="form-control">
                    </div>
                </fieldset>
                <fieldset class="col-md-8 col-xs-8">
                    <legend>Nombre</legend>
                    <div class="form-group ">
                        <input type="text" name="nombre" placeholder="Nombre de la clave" maxlength="' . $maxlengthnombre . '" value="' . $strNom . '" id="nombre" class="form-control">
                    </div>
                </fieldset>
            </div>
            <div class="row">
                <fieldset class="col-md-10 col-xs-10">
                    <legend>Descripción </legend>
                    <div class="form-group ">
                        <input type="text" name="nombre" placeholder="Descripción de la clave" maxlength="' . $maxlengthdescripcion . '"  value="' . $strDes . '" id="descripcion" class="form-control">
                    </div>
                </fieldset>
            </div>
        </form>';

        // Para que no marque error, le quitamos los tabuladores, y avances de línea
        $html = str_replace('"', '\"', $html);

        $html = str_replace("\t", "", $html);

        $html = str_replace("\n", "", $html);

        $html = str_replace("\r", "", $html);

        $this->setIdScript('addModCONAC');
        $this->mixScript('
            $(".modal-header").css("background-color", "#9d2449");
            $(".modal-title").text( "' . (($this->prm->opc == "mod") ? "Modificar registro"
            : "Adicionar registro") . '" );
            $(".modal-body").empty();
            $(".modal-body").append( "' . $html . '" );
            $("#genero").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#grupo").empty();   $("#rubro").empty();   $("#cuenta").empty();
                    $("#clave").val("");
                } else {' .
            ((strpos("[rub.cgcu.cgsc]", $this->prm->cat) > 0) ? '
                    comunica("' . $this->pantalla . '",{
                        "metodo":"llenaCombo",
                        "cmb": "grupo",
                        "gen": this.value,
                        "msg": "Selecciona el Grupo"
                    });
                    ' : '') . '
                    $("#rubro").empty();   $("#cuenta").empty();
                }
            });
            $("#grupo").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#rubro").empty();   $("#cuenta").empty();
                    $("#clave").val( $("#clave").val().substring( 0, 1 ) );
                } else {' .
            ((strpos("[cgcu.cgsc]", $this->prm->cat) > 0) ? '
                    comunica("' . $this->pantalla . '",{
                        "metodo":"llenaCombo",
                        "cmb": "rubro",
                        "gen": $("#genero").val(),
                        "cgp": $("#grupo").val(),
                        "msg": "Selecciona el Rubro" });
                    ' : '') . '
                    $("#cuenta").empty();
                }
            });
            $("#rubro").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#cuenta").empty();
                    $("#clave").val( $("#clave").val().substring( 0, 2 ) );
                } else {' .
            ((strpos("[cgsc]", $this->prm->cat) > 0) ? '
                    comunica("' . $this->pantalla . '",{
                        "metodo":"llenaCombo",
                        "cmb": "cuenta",
                        "gen": $("#genero").val(),
                        "cgp": $("#grupo").val(),
                        "rub": $("#rubro").val(),
                        "msg": "Selecciona la Cuenta" });
                    ' : '') . '
                }
            });

            $("#edoFin").change( function() {
                nSel = this.value;
                if ( nSel == "" ) {
                    $("#posicion").empty().html("<select name=\"posicion\" id=\"posicion\" class=\"form-control\"></select>");
                } else if ( nSel == "B" ) {
                    $("#posicion").empty().html("<select name=\"posicion\" id=\"posicion\" class=\"form-control\"><option selected value=\"A\">A - ACTIVO</option><option value=\"P\">P - PASIVO</option><option value=\"H\">H - HACIENDA PÚBLICA</option><option value=\"O\">O - ORDEN</option></select>");
                } else {
                    $("#posicion").empty().html("<select name=\"posicion\" id=\"posicion\" class=\"form-control\"><option selected value=\"I\">I - INGRESOS</option><option value=\"E\">E - EGRESOS</option></select>");
                }
            });


            $( document ).ready(function() { ' .
            (($this->prm->opc == "mod") ?
                'setTimeout( function() { $( "#clave" ).focus().blur().prop("disabled", true); $( "#nombre" ).focus() }, 100 ); '
                . ((strpos("[cgsc]", $this->prm->cat) > 0) ?
                    '$("#naturaleza option[value=' . $naturaleza . ']").attr("selected","selected");
                    $("#estructura option[value=' . $estructura . ']").attr("selected","selected");
                    $("#edoFin option[value=' . $edofinan . ']").attr("selected","selected");
                    $("#posicion option[value=' . $posicion . ']").attr("selected","selected");'
                    : '') :
                'setTimeout( function() { $( "#clave" ).focus() }, 100 );  '
            ) . '
            });
        ');
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla ESPECIFICA donde modificaremos los datos del catálogo actual
// Lo hago así porque dependiendo del catálogo es como se dibujará y se comportará la forma
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dameCombo($id, $mensaje, $seleccionado, $sql)
    {

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<select name="' . $id . '" id="' . $id . '" disabled class="form-control"><option value="X">ERROR. NO EXISTE INFORMACION</option></select>';
                break;
            case 1:
                $strCombo = '<select name="' . $id . '" id="' . $id . '" disabled class="form-control">';
                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                }
                $strCombo .= '</select>';
                break;
            default:
                $strCombo = '<select name="' . $id . '" id="' . $id . '" class="form-select">';
                if ($numRegistros > 1 and $mensaje != "") {
                    $strCombo .= '<option value="">' . $mensaje . '</option>';
                }

                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                }
                $strCombo .= '</select>';
                break;
        }

        return $strCombo;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Llenamos un combo basandonos en la seleccion del actual
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function llenaCombo($combo = "", $varGen = "", $varGpo = "",
        $varRub = "", $varCta = "", $mens = "") {

        $combo = (($combo != "") ? $combo : ((isset($this->prm->cmb) ? $this->prm->cmb
            : "")));
        $genero = (($varGen != "") ? $varGen : ((isset($this->prm->gen) ? $this->prm->gen
            : "")));
        $grupo = (($varGpo != "") ? $varGpo : ((isset($this->prm->cgp) ? $this->prm->cgp
            : "")));
        $rubro = (($varRub != "") ? $varRub : ((isset($this->prm->rub) ? $this->prm->rub
            : "")));
        $cuenta = (($varCta != "") ? $varCta : ((isset($this->prm->cta) ? $this->prm->cta
            : "")));
        $mens = (($mens != "") ? $mens : ((isset($this->prm->msg) ? $this->prm->msg
            : "")));

        switch ($combo) {
            case "grupo":
                $sql = "SELECT cgp_id, cgp_" . ((APP_EJERCICIO == "2012") ? "descripcion" : "nombre") . " FROM ".BD_CTRL.".cat_CgGrupo WHERE ger_id = ? ORDER BY cgp_id;";
                $parametros = [$genero];
                break;
            case "rubro":
                $sql = "SELECT rub_id, rub_" . ((APP_EJERCICIO == "2012") ? "descripcion" : "nombre") . " FROM ".BD_CTRL.".cat_Rubro WHERE ger_id = ? AND cgp_id = ? ORDER BY rub_id;";
                $parametros = [$genero,$grupo];
                break;
            case "cuenta":
                $sql = "SELECT cgcu_id, cgcu_" . ((APP_EJERCICIO == "2012") ? "descripcion" : "nombre") . " FROM ".BD_CTRL.".cat_CgCuenta WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? ORDER BY cgcu_id;";
                $parametros = [$genero,$grupo,$rubro];
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($parametros);
        $numRegistros = $conexion->getNumeroRegistros();
        $conexion->desconectar();

        switch ($numRegistros) {
            case 0:
                $strCombo = '<option value="">ERROR. NO EXISTE INFORMACION</option>';
                break;
            case 1:
                $strCombo = '';
                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                }
                break;
            default:
                $strCombo = '<option value="">' . $mens . '</option>';
                while ($row = $conexion->sacar()) {
                    $strCombo .= '<option value="' . $row[0] . '">' . $row[0] . " - " . $row[1] . '</option>';
                }
                break;
        }

        // Para que no marque error, le quitamos los tabuladores, y avances de línea
        $strCombo = str_replace('"', '\"', $strCombo);

        $strCombo = str_replace("\t", "", $strCombo);

        $strCombo = str_replace("\n", "", $strCombo);

        $strCombo = str_replace("\r", "", $strCombo);

        $this->mixScript('
            $("#' . $combo . '").empty().append("' . $strCombo . '");
        ');
        if ($combo == 'rubro' && $numRegistros == 1) {
            $this->mixScript('
                if ($("#cuenta").length == 1){
                    $("#rubro").change();
                }
            ');
        }
        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Si escriben directamente la clave, llenamos los combos con esos valores ...
// MENOS el de GENERO, porque ese de por si ya está lleno con los valores ...
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function PonClaveEnCombos()
    {
        $cta = $this->prm->clave;
        $arrCta = explode(".", $cta);

        $genero = (isset($arrCta[0]) ? $arrCta[0] : "");
        $grupo = (isset($arrCta[1]) ? $arrCta[1] : "");
        $rubro = (isset($arrCta[2]) ? $arrCta[2] : "");
        $cuenta = (isset($arrCta[3]) ? $arrCta[3] : "");
        $subcuenta = (isset($arrCta[4]) ? $arrCta[4] : "");
        $modificacion = (isset($this->prm->opc) ? $this->prm->opc : "");
        // Cuando toque poner el combo de GRUPO ...
        if (strpos("[rub.cgcu.cgsc]", $this->prm->cat) > 0) {
            if ($genero != "") {
                $this->llenaCombo("grupo", $genero, "", "", "",
                    "Selecciona el Grupo");
            }
        }
        // Cuando toque poner el combo de RUBRO ...
        if (strpos("[cgcu.cgsc]", $this->prm->cat) > 0) {
            if ($genero != "" and $grupo != "") {
                $this->llenaCombo("rubro", $genero, $grupo, "", "",
                    "Selecciona el Rubro");
            }
        }
        // Cuando toque poner el combo de CUENTA ...
        if (strpos("[cgsc]", $this->prm->cat) > 0) {
            if ($genero != "" and $grupo != "" and $rubro != "") {
                $this->llenaCombo("cuenta", $genero, $grupo, $rubro, "",
                    "Selecciona el Rubro");
            }
        }

        $this->mixScript('
            try {
                $("#genero").val( ' . $genero . ' );
                $("#grupo").val( ' . $grupo . ' );
                $("#rubro").val( ' . $rubro . ' );
                $("#cuenta").val( ' . $cuenta . ' );
                ' . (($modificacion == "mod") ?
                    '$("#genero").prop( "disabled", true );
                    $("#grupo").prop( "disabled", true );
                    $("#rubro").prop( "disabled", true );
                    $("#cuenta").prop( "disabled", true );' :
                    '$("#genero").prop( "disabled", false );
                    $("#grupo").prop( "disabled", false );
                    $("#rubro").prop( "disabled", false );
                    $("#cuenta").prop( "disabled", false );'
                ) . '
            } catch( error ) {
                // nada
            }
        ');
        if ($modificacion == "mod") {
            $this->mixScript('
                try {
                    $("#genero").val( ' . $genero . ' );
                    $("#grupo").val( ' . $grupo . ' );
                    $("#rubro").val( ' . $rubro . ' );
                    $("#cuenta").val( ' . $cuenta . ' );
                } catch( error ) {
                    // nada
                }
            ');
        }
        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Dibujamos la pantalla ESPECIFICA donde modificaremos los datos del catálogo actual
// Lo hago así porque dependiendo del catálogo es como se dibujará y se comportará la forma
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function dameComboVacio($id, $mensaje)
    {
        $strCombo = '<select name="' . $id . '" id="' . $id . '" class="form-control overflow: scroll"></select>';
        return $strCombo;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Grabamos un cambio en el catálogo actual
// parametros: cat, clave, nom, opc
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function modCONAC()
    {
     
        $cuentacomp = $this->prm->cuentacomp;
        $cuentacomp = str_replace(",", "", $cuentacomp);

        $naturaleza = (isset($this->prm->nt) ? $this->prm->nt : "");
        $estructura = (isset($this->prm->es) ? $this->prm->es : "");
        $estadoFinan = (isset($this->prm->ef) ? $this->prm->ef : "");
        $posicion = (isset($this->prm->po) ? $this->prm->po : "");

        $arrCta = explode(".", $cuentacomp);
        $genero = (isset($arrCta[0]) ? $arrCta[0] : "");
        $grupo = (isset($arrCta[1]) ? $arrCta[1] : "");
        $rubro = (isset($arrCta[2]) ? $arrCta[2] : "");
        $cuenta = (isset($arrCta[3]) ? $arrCta[3] : "");
        $subcuenta = (isset($arrCta[4]) ? $arrCta[4] : "");

        $this->prm->n = strtoupper($this->prm->n);

        $descripcion = ((isset($this->prm->des)) ? strtoupper($this->prm->des)
            : "");

        // Validamos el ancho de la clave de acuerdo al catálogo
        // Validamos el ancho de la clave de acuerdo al catálogo
        $anchos = array("ger" => 1, "cgp" => 1, "rub" => 2, "cgcu" => 2, "cgsc" => 2); // 2016 cgsc puede ser de 5 o 6 digitos
        $anchos2 = array("ger" => 1, "cgp" => 1, "rub" => 1, "cgcu" => 1, "cgsc" => 1);
        // Si no es el ancho exacto ...
        if ((strlen($this->prm->c) != $anchos[$this->prm->cat]) &&
            (strlen($this->prm->c) != $anchos2[$this->prm->cat])
        ) {
            $this->mixScript('
                notify( "La clave DEBE tener una longitud de ' . $anchos[$this->prm->cat] . ' dígito(s)", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 ); /* x q en cambios, la clave esta deshabilitada */
            ');
            return false;
        }

        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la clave NO puede quedar vacío", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
            ');
            return false;
        }
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la clave NO puede quedar vacío", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
            ');
            return false;
        }
        $ejecuta = 1;

        switch ($this->prm->cat) {
            case "cgp":
                $entidadgrupo = new stdClass();
                $entidadgrupo->ger_id = $genero;
                $entidadgrupo->cgp_id = $grupo;
                $entidadgrupo->nombre = $this->prm->n;
                $entidadgrupo->descripcion = $descripcion;
                $entidadgrupo->abc = 'c';
                CgGrupo::abc($entidadgrupo);
                $sql2 = 'SELECT ger_id from '.BD_CTRL.'.cat_Rubro WHERE ger_id = ? AND cgp_id= ? LIMIT 1;';
                $parametros = [$genero,$grupo];
                break;
            case "rub":
                $entidadrubro = new stdClass();
                $entidadrubro->ger_id = $genero;
                $entidadrubro->cgp_id = $grupo;
                $entidadrubro->rub_id = $rubro;
                $entidadrubro->nombre = $this->prm->n;
                $entidadrubro->descripcion = $descripcion;
                $entidadrubro->abc = 'c';
                Rubro::abc($entidadrubro);
                $sql2 = 'SELECT ger_id from '.BD_CTRL.'.cat_CgCuenta WHERE ger_id = ? AND cgp_id= ?
                    AND  rub_id= ? LIMIT 1;';
                $parametros = [$genero,$grupo,$rubro];
                break;
            case "cgcu":
                $entidadcgCuenta = new stdClass();
                $entidadcgCuenta->ger_id = $genero;
                $entidadcgCuenta->cgp_id = $grupo;
                $entidadcgCuenta->rub_id = $rubro;
                $entidadcgCuenta->cgcu_id = $cuenta;
                $entidadcgCuenta->nombre = $this->prm->n;
                $entidadcgCuenta->descripcion = $descripcion;
                $entidadcgCuenta->abc = 'c';
                CgCuenta::abc($entidadcgCuenta);
                $sql2 = '
                    SELECT ger_id from '.BD_CTRL.'.cat_CgSubcuenta
                    WHERE ger_id = ? AND cgp_id= ?
                    AND  rub_id= ? AND cgcu_id = ? LIMIT 1;';
                $parametros = [$genero,$grupo,$rubro,$cuenta];
                break;
            case "cgsc":
                if ($naturaleza == "" || $estructura == "" || $estadoFinan == ""
                    || $posicion == "") {
                    $ejecuta = 0;
                }
                $entidadCgSubCuenta = new stdClass();
                $entidadCgSubCuenta->ger_id = $genero;
                $entidadCgSubCuenta->cgp_id = $grupo;
                $entidadCgSubCuenta->rub_id = $rubro;
                $entidadCgSubCuenta->cgcu_id = $cuenta;
                $entidadCgSubCuenta->cgsc_id = $subcuenta;
                $entidadCgSubCuenta->nombre = $this->prm->n;
                $entidadCgSubCuenta->descripcion = $descripcion;
                $entidadCgSubCuenta->nat_id = $naturaleza;
                $entidadCgSubCuenta->ecu_id = $estructura;
                $entidadCgSubCuenta->tcu_id = $estadoFinan;
                $entidadCgSubCuenta->pcu_id = $posicion;
                $entidadCgSubCuenta->abc = 'c';
                if ($ejecuta == 1) {
                    CgSubcuenta::abc($entidadCgSubCuenta);
                }
                $sql2 = '
                    SELECT cgsc_cuenta AS cve, cgsc_nombre AS nom,cgsc_descripcion as des,CASE nat_id WHEN "D" THEN "Deudora" ELSE "Acreedora" END AS nat,
                    CONCAT(LEFT(ecu_nombre,1),LOWER(SUBSTRING(ecu_nombre,2)))AS str,CONCAT(LEFT(tcu_nombre,1),LOWER(SUBSTRING(tcu_nombre,2))) AS t,CONCAT(LEFT(pcu_nombre,1),LOWER(SUBSTRING(pcu_nombre,2))) AS p
                        FROM '.BD_CTRL.'.cat_CgSubcuenta
                            INNER JOIN '.BD_CTRL.'.cat_EstructuraCuenta USING (ecu_id)
                            INNER JOIN '.BD_CTRL.'.cat_TipoCuenta USING (tcu_id)
                            INNER JOIN '.BD_CTRL.'.cat_PosicionCuenta USING (pcu_id)
                            WHERE ger_id = ? AND cgp_id = ? AND rub_id = ? AND cgcu_id = ? AND cgsc_id = ? LIMIT 1;';
                $parametros = [$genero,$grupo,$rubro,$cuenta,$subcuenta];
                break;
            // Por default, genero
            default:
                $entidadGenero = new stdClass();
                $entidadGenero->ger_id = $genero;
                $entidadGenero->nombre = $this->prm->n;
                $entidadGenero->descripcion = $descripcion;
                $entidadGenero->abc = 'c';
                Genero::abc($entidadGenero);
                $sql2 = 'SELECT ger_id from '.BD_CTRL.'.cat_CgGrupo WHERE ger_id = ? LIMIT 1;';
                $parametros = [$genero];
                break;
        }

        if ($ejecuta == 1) {
            $conexion = new AccesoDatos();
            $conexion->conectar();
            if ($this->prm->cat == "cgsc") {
                $conexion->setSql($sql2);
                $conexion->consultar($parametros);
                $subgcuent = $conexion->extraer();
                $naturaleza = $subgcuent['nat'];
                $estructura = $subgcuent['str'];
                $estadoFinan = $subgcuent['t'];
                $posicion = $subgcuent['p'];
                $conexion->desconectar();
            }

            $this->mixScript('
                notify("Se ha actualizado la información", "success" );
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModal"));
                modal.hide();
                comunica("' . $this->pantalla . '",{"metodo":"muestraDatos", "tabla": "'.$this->prm->cat.'"});
            ');
        } else {
            $this->mixScript('
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModal"));
                modal.hide();
                notify("NO se ha podido actualizar la información", "warning" );
            ');
        }
        return true;
    }

// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
// Registramos una NUEVA clave, en el catálogo correspondiente
// parametros: cat, clave, nom
// ------------------------------------------------------------------------------------------------------------------------
// ------------------------------------------------------------------------------------------------------------------------
    private function addCONAC()
    {   
        $existe = false;
        $genero = (isset($this->prm->gen)) ? $this->prm->gen : "";
        $grupo = (isset($this->prm->grup)) ? $this->prm->grup : "";

        $rubro = (isset($this->prm->rub)) ? $this->prm->rub : "";
        $cuenta = (isset($this->prm->cta)) ? $this->prm->cta : "";

        if ($this->prm->cat == "ger") {
            $genero = $this->prm->c;
            $sqlValida = 'SELECT ger_id from '.BD_CTRL.'.cat_Genero WHERE ger_id= :genero';
            $param = [':genero' => $genero];
        }
        if ($this->prm->cat == "cgp") {
            $grupo = $this->prm->c;
            $sqlValida = 'SELECT cgp_cuenta from '.BD_CTRL.'.cat_CgGrupo WHERE cgp_cuenta= :grupo';
            $param = ['grupo' => $genero . '.' . $grupo];
        }
        if ($this->prm->cat == "rub") {
            $rubro = $this->prm->c;
            $sqlValida = 'SELECT rub_cuenta from '.BD_CTRL.'.cat_Rubro WHERE rub_cuenta= :rubro';
            $param = ['rubro' => $genero . '.' . $grupo . '.'. $rubro];
        }
        if ($this->prm->cat == "cgcu") {
            $cuenta = $this->prm->c;
            $sqlValida = 'SELECT cgcu_cuenta from '.BD_CTRL.'.cat_CgCuenta WHERE cgcu_cuenta= :cuenta';
            $param = ['cuenta' => $genero . '.' . $grupo . '.'. $rubro . '.' . $cuenta];
        }
        if ($this->prm->cat == "cgsc") {
            $subcuenta = $this->prm->c;
            $sqlValida = 'SELECT cgsc_cuenta from '.BD_CTRL.'.cat_CgSubcuenta WHERE cgsc_cuenta= :subcuenta';
            $param = ['subcuenta' => $genero . '.' . $grupo . '.'. $rubro . '.' . $cuenta . '.' . $subcuenta];
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $numreg = 0;
        $conexion->setSql($sqlValida);
        $conexion->consultar($param);
        $numreg = $conexion->getNumeroRegistros();
        $conexion->desconectar();
        if ($numreg > 0){
            $existe = true;
        }

        // Todos los elementos DEBEN ser números, si no truena el SQL
        if (!is_numeric($this->prm->c)) {
            $this->mixScript('
                notify("Todos los elementos de la cuenta de mayor DEBEN ser números","error");
                window.espera.stop();
            ');
            return false;
        }
        $this->prm->n = strtoupper($this->prm->n);
        $descripcion = ((isset($this->prm->des)) ? strtoupper($this->prm->des)
            : "");
        // Validamos el ancho de la clave de acuerdo al catálogo
        $anchos = array("ger" => 1, "cgp" => 1, "rub" => 2, "cgcu" => 2, "cgsc" => 2); // 2016 cgsc puede ser de 5 o 6 digitos
        $anchos2 = array("ger" => 1, "cgp" => 1, "rub" => 1, "cgcu" => 1, "cgsc" => 1);
        // Si no es el ancho exacto ...
        if ((strlen($this->prm->c) != $anchos[$this->prm->cat]) &&
            (strlen($this->prm->c) != $anchos2[$this->prm->cat])
        ) {
            $this->mixScript('
                notify( "La clave DEBE tener una longitud maxima de  ' . $anchos[$this->prm->cat] . ' dígito(s)", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 ); /* x q en cambios, la clave esta deshabilitada */
            ');
            return false;
        }

        // Obvio ... el concepto no puede quedar vacío
        if (strlen($this->prm->n) == 0) {
            $this->mixScript('
                notify( "El nombre de la clave NO puede quedar vacío", "error" );
                setTimeout( function() { $( "#nombre" ).focus().select() }, 100 );
            ');
            return false;
        }

        // Si estamos guardando una SUBCUENTA, también deben venir los parámetros de
        // naturaleza, estructura, estado financiero y posición
        if ($this->prm->cat == "cgsc") {
            // Naturaleza
            if (!($this->prm->nt == "D" || $this->prm->nt == "H")) {
                $this->mixScript('
                    notify( "La naturaleza de la cuenta sólo puede ser Deudora o Acreedora", "error" );
                    setTimeout( function() { $( "#naturaleza" ).focus().select() }, 100 );
                ');
                return false;
            }
            // Estructura
            if (!($this->prm->es == "1" || $this->prm->es == "2" || $this->prm->es
                == "3")) {
                $this->mixScript('
                    notify( "La estructura de la cuenta no es válida", "error" );
                    setTimeout( function() { $( "#estructura" ).focus().select() }, 100 );
                ');
                return false;
            }
            // Estado financiero
            if (!($this->prm->ef == "B" || $this->prm->ef == "R")) {
                $this->mixScript('
                    notify( "El Estado Financiero donde se usa la cuenta no es válido", "error" );
                    setTimeout( function() { $( "#edoFin" ).focus().select() }, 100 );
                ');
                return false;
            }
            // Posición para Balance
            if ($this->prm->ef == "B") {
                if (!($this->prm->po == "A" || $this->prm->po == "P" || $this->prm->po
                    == "H" || $this->prm->po == "O")) {
                    $this->mixScript('
                        notify( "Sólo puede ser cuenta de Activo, Pasivo, Hacienda Pública u Orden", "error" );
                        setTimeout( function() { $( "#posicion" ).focus().select() }, 100 );
                    ');
                    return false;
                }
            }
            // Posición para Resultados
            if ($this->prm->ef == "R") {
                if (!($this->prm->po == "I" || $this->prm->po == "E")) {
                    $this->mixScript('
                        notify( "Sólo puede ser cuenta de Ingresos o Egresos", "error" );
                        setTimeout( function() { $( "#posicion" ).focus().select() }, 100 );
                    ');
                    return false;
                }
            }
        }
        $conexion = new AccesoDatos();
        $conexion->conectar();

        // Todo ésto lo metemos en una transacción
        // --------------------------------------------------------------------------------------------------------------
        $conexion->setSql('START TRANSACTION;');
        $conexion->consultar();
        $cuentamuestra = "";
        $tabalmustra = "";
        switch ($this->prm->cat) {
            case "cgp":
                if (!$existe)
                {
                    $entidadgrupo = new stdClass();
                    $entidadgrupo->ger_id = $genero;
                    $entidadgrupo->cgp_id = $grupo;
                    $entidadgrupo->nombre = $this->prm->n;
                    $entidadgrupo->descripcion = $descripcion;
                    $entidadgrupo->abc = 'a';
                    CgGrupo::abc($entidadgrupo);
                    $cuentamuestra = $genero . "." . $grupo;
                    $tabalmustra = "cgp";
                }
                break;
            case "rub":
                if (!$existe)
                {
                    $entidadrubro = new stdClass();
                    $entidadrubro->ger_id = $genero;
                    $entidadrubro->cgp_id = $grupo;
                    $entidadrubro->rub_id = $rubro;
                    $entidadrubro->nombre = $this->prm->n;
                    $entidadrubro->descripcion = $descripcion;
                    $entidadrubro->abc = 'a';
                    Rubro::abc($entidadrubro);
                    $cuentamuestra = $genero . "." . $grupo . "." . $rubro;
                    $tabalmustra = "rub";
                }
                break;
            case "cgcu":
                if (!$existe)
                {
                    $entidadcgCuenta = new stdClass();
                    $entidadcgCuenta->ger_id = $genero;
                    $entidadcgCuenta->cgp_id = $grupo;
                    $entidadcgCuenta->rub_id = $rubro;
                    $entidadcgCuenta->cgcu_id = $cuenta;
                    $entidadcgCuenta->nombre = $this->prm->n;
                    $entidadcgCuenta->descripcion = $descripcion;
                    $entidadcgCuenta->abc = 'a';
                    CgCuenta::abc($entidadcgCuenta);
                    $cuentamuestra = $genero . "." . $grupo . "." . $rubro . "." . $cuenta;
                    $tabalmustra = "cgcu";
                }
                break;
            case "cgsc":
                if (!$existe)
                {
                    $entidadCgSubCuenta = new stdClass();
                    $entidadCgSubCuenta->ger_id = $genero;
                    $entidadCgSubCuenta->cgp_id = $grupo;
                    $entidadCgSubCuenta->rub_id = $rubro;
                    $entidadCgSubCuenta->cgcu_id = $cuenta;
                    $entidadCgSubCuenta->cgsc_id = $subcuenta;
                    $entidadCgSubCuenta->nombre = $this->prm->n;
                    $entidadCgSubCuenta->descripcion = $descripcion;
                    $entidadCgSubCuenta->nat_id = $this->prm->nt;
                    $entidadCgSubCuenta->ecu_id = $this->prm->es;
                    $entidadCgSubCuenta->tcu_id = $this->prm->ef;
                    $entidadCgSubCuenta->pcu_id = $this->prm->po;
                    $entidadCgSubCuenta->abc = 'a';
                    CgSubcuenta::abc($entidadCgSubCuenta);
                    $cuentamuestra = $genero . "." . $grupo . "." . $rubro . "." . $cuenta . "." . $subcuenta;
                    $tabalmustra = "cgsc";
                }
                break;
            // Por default, genero
            default: //modificar jercicio actual a 2018
                if (!$existe)
                {
                    $entidadGenero = new stdClass();
                    $entidadGenero->ger_id = $genero;
                    $entidadGenero->nombre = $this->prm->n;
                    $entidadGenero->descripcion = $descripcion;
                    $entidadGenero->abc = 'a';
                    Genero::abc($entidadGenero);
                    $cuentamuestra = $genero;
                    $tabalmustra = "ger";
                }
                break;
        }
        // Terminamos la transacción
        // --------------------------------------------------------------------------------------------------------------
        $conexion->setSql('COMMIT;');
        $conexion->consultar();
        $conexion->desconectar();
        $this->mixScript('
            // Actualizamos el grid
            dataView.addItem({
                "id":  "' . $cuentamuestra . '",
                "c":   "' . $cuentamuestra . '",
                "d":   "' . $this->prm->n . '",
                "des":  "' . $descripcion . '"
            });
            dataView.refresh();
            comunica("' . $this->pantalla . '",{"metodo":"muestraDatos", "tabla": "'.$this->prm->cat.'"});
            // Terminamos
            if ("'.!$existe.'"){
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModal"));
                modal.hide();
                notify("Registro realizado exitosamente", "success" );
            }else {
                notify("El registro ya existe", "warning" );
                const modal = bootstrap.Modal.getInstance(document.querySelector("#myModal"));
                modal.hide();
                }
        ');
        return true;
    }
}
// Si no tiene permisos de lectura, no entra a la opción
if (WebResponse::tienePermisos(TAREA_LECTURA, $_POST)) {
    new ControladorCONAC();
} else {
    WebResponse::sinPermisos();
}

// ----------------------------------------------------------------------------------------------------------------
// FIN DEL CÓDIGO
// ----------------------------------------------------------------------------------------------------------------