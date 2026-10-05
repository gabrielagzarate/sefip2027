<?php

/**
 * Description of ControladorTesProgramacion
 *
 * @author ?
 * Collaborators: Adriana Santiago Morales <adriana.santiago@dgtid.oaxaca.gob.mx>
 * Update: 10-Oct-2022
 * Comment: -----
 * Objective:
 * Regla de Negocio:

 * Permits: Esta pantalla tiene permisos de lectura y escritura.
 * */
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/pantallasModales.php';
require_once dirname(__FILE__) . '/../../ent/tes/propuesta/temPropuestaClc.php';
require_once dirname(__FILE__) . '/../../ent/tes/storeProcedure/sp_GenerarPropuesta.php';
require_once dirname(__FILE__) . '/../models/clcs2017.php';
require_once dirname(__FILE__) . '/../models/clcs2018.php';
require_once dirname(__FILE__) . '/../models/clcs2019.php';
require_once dirname(__FILE__) . '/../models/clcs2020.php';
require_once dirname(__FILE__) . '/../models/clcs2021.php';
require_once dirname(__FILE__) . '/../models/clcs2022.php';
require_once dirname(__FILE__) . '/../models/clcs2023.php';
require_once dirname(__FILE__) . '/../models/clcs2024.php';
require_once dirname(__FILE__) . '/../models/clcs2025.php';
require_once dirname(__FILE__) . '/../models/clcs2026.php';
require_once dirname(__FILE__) . "/../../lib/tes/webServicesSiox.php";
require_once dirname(__FILE__) . '/../../ent/tes/linea/LineaCaptura.php';

class ControladorTesProgramacion extends WebResponse
{
    public $escritura;
    public $nivelJerarquico;
    public $usuario_id;
    public $metodoPago;
    public $ejercicio;
    public $ip;

    public function __construct()
    {
        parent::__construct();
        $this->pantalla = "tes/tesoreria/ControladorTesProgramacion.php";
        $this->escritura = isset($_SESSION[_S]['prm']['T']['3']['1']['2']);
        $this->nivelJerarquico = $_SESSION[_S]['nvj_id'];
        $this->usuario_id = $_SESSION[_S]['usr_id'];
        $this->metodoPago = !isset($_SESSION[_S]['prm']['metodoPago']) ? 1 : $_SESSION[_S]['prm']['metodoPago'];
        $this->ejercicio = !isset($_SESSION[_S]['prm']['ejercicio']) ? APP_EJERCICIO : $_SESSION[_S]['prm']['ejercicio'];
        $this->ip = (isset($_SESSION[_S]['ses_ip']) && $_SESSION[_S]['ses_ip'] != "::1") ? $_SESSION[_S]['ses_ip'] : '127.0.0.1';

        switch ($this->nivelJerarquico) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                switch ($this->metodo) {
                    case 'llenaTablaClc':
                        $this->llenaTablaClc();
                        break;
                    case 'agregaClc':
                        $this->agregaClc();
                        break;
                    case 'generarPropuesta':
                        $this->generarPropuesta();
                        break;
                    case 'pintaDetallesClc':
                        $this->pintaDetallesClc();
                        break;
                    case 'quitarClc':
                        $this->quitarClc();
                        break;
                    case 'eliminarDetalleCompleto':
                        $this->eliminarDetalleCompleto();
                        $this->mainProgramacion();
                        break;
                    case 'rellenaSubTipo':
                        $this->rellenaSubTipo();
                        break;
                    case 'pintaTabla':
                        $this->pintaTabla();
                        break;
                    case 'limpiarDatos':
                        $this->limpiarDatos();
                        break;
                    case 'limpiarCampos':
                        $this->limpiarCampos();
                        break;
                    case 'liberarClcOtros':
                        $this->liberarClcOtros();
                        break;
                    case 'agregaTodo':
                        $this->agregaTodo();
                        break;
                    case 'mostrarModal':
                        $this->mostrarModal();
                        break;
                    case 'llenaTablaDetallesClc':
                        $this->llenaTablaDetallesClc();
                        break;
                    case 'buscaSiox':
                        $this->buscaSiox();
                        break;
                    case 'mainProgramacion':
                    default:
                        $this->mainProgramacion();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    public function mainProgramacion()
    {
        $this->agregaTools('');
        //Crea el boton de regreso
        $this->mixScript('
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });

            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');
        $params = [$this->usuario_id];
        //consultamos las detalles de la propuesta
        $sql = "SELECT FORMAT(sum(p.pro_importeClc),2) importe,count(p.clc_id) contador, p.pro_ejercicio
          FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
          WHERE p.usr_id=? GROUP BY p.pro_ejercicio";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar($params);
        $contador = 0;
        $actual_tlo = $this->metodoPago;
        if ($conexion->getNumeroRegistros() > 0) {
            $row = $conexion->extraer();
            $importe = $row['importe'];
            $contador = $row['contador'];
            $actual_tlo = $this->metodoPago;
            $ejercicio = $row['pro_ejercicio'];
            $this->ejercicio = $ejercicio;

            $btnDetallesPropuesta = new stdClass();
            $btnDetallesPropuesta->id = 'btnDetallesPropuesta';
            $btnDetallesPropuesta->name = 'btnDetallesPropuesta';
            $btnDetallesPropuesta->text = 'DetallesPropuesta';
            $btnDetallesPropuesta->value = $contador . ' $' . $importe;
            $btnDetallesPropuesta->type = 'button';
            $btnDetallesPropuesta->icon = "fa-solid fa-magnifying-glass me-2";
            $btnDetallesPropuesta->color = 'cielo';
            $btnDetallesPropuesta->click = '
                comunica("' . $this->pantalla . '",{
                  metodo:"pintaDetallesClc",
                  ejercicio: $("#anio option:selected").text().split(" ")[2].trim()
                })';

            $btnGuardarPropuesta = new stdClass();
            $btnGuardarPropuesta->id = 'btnGuardarPropuesta';
            $btnGuardarPropuesta->name = 'btnGuardarPropuesta';
            $btnGuardarPropuesta->text = 'Generar Propuesta';
            $btnGuardarPropuesta->value = 'Generar Propuesta';
            $btnGuardarPropuesta->type = 'button';
            $btnGuardarPropuesta->class = "btn btn-outline-success me-1 ";
            $btnGuardarPropuesta->icon = "fa-solid fa-share-from-square me-2";
            $btnGuardarPropuesta->click = '
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                $(this).attr("disabled",true);
                comunica("' . $this->pantalla . '",{
                    metodo:"generarPropuesta"
                })';
            if ($contador > 0 && $this->escritura) {
                $this->agregaTools($this->ponBoton($btnGuardarPropuesta));
                $this->agregaTools($this->ponBoton($btnDetallesPropuesta));
            } else {
                $this->mixScript('
                    $("#toolsPantalla").html("");
                ');
            }
        }
        $inputAnio = new StdClass();
        $anio = array();
        if ($contador > 0) {
            array_push($anio, $ejercicio);
        } else {
            //Agrega del año actual al año 2021 al arreglo de años
            for ($i = APP_EJERCICIO; $i >= 2021; $i--) {
                array_push($anio, $i);
            }
            if ($this->serverProduccion) {
                //Agrega del año 2020 al 2017 al arreglo de años
                for ($i = 2020; $i >= 2017; $i--) {
                    array_push($anio, $i);
                }
            }
        }
        $inputAnio->opciones = $anio;
        $inputAnio->id = 'anio';
        $inputAnio->name = 'anio';
        $inputAnio->required = true;
        $inputAnio->search = true;
        $inputAnio->mensaje = 'SELECCIONE EL EJERCICIO';
        $inputAnio->label = 'EJERCICIO';

        $inputEstado = new StdClass();
        $estados = array();
        array_push($estados, 'PENDIENTES');

        $inputEstado->opciones = $estados;
        $inputEstado->id = 'estado';
        $inputEstado->name = 'estado';
        $inputEstado->required = true;
        $inputEstado->mensaje = 'SELECCIONE EL ESTADO';
        $inputEstado->label = 'ESTADO';

        $inputModo = new StdClass();
        $inputModo->sql = "SELECT * FROM cat_ModoPagoClc";
        if ($contador > 0) {
            //Bloqueamos los datos
            $inputModo->sql = "SELECT mpc_id,mpc_nombre from " . BD_CTRL . ".cat_ModoPagoClc where mpc_id=?";
            $inputModo->params = [$this->metodoPago];
        }
        $inputModo->search = true;
        $inputModo->id = 'modo';
        $inputModo->name = 'modo';
        $inputModo->required = true;
        $inputModo->mensaje = 'SELECCIONE EL MODO';
        $inputModo->label = 'MODO';
        $inputModo->selecciona = $actual_tlo;
        $inputModo->change = '
            if ($(this).val() == 4 || $(this).val() == 5) {
                $("#rfc_id").removeAttr("disabled");
                $("#rfc_id").attr("enabled","enabled");
                $("#proveedor").removeAttr("disabled");
                $("#proveedor").attr("enabled","enabled");
            } else {
                $("#rfc_id").removeAttr("enabled");
                $("#rfc_id").attr("disabled","disabled");
                $("#proveedor").removeAttr("enabled");
                $("#proveedor").attr("disabled","disabled");
            }
        ';

        $inputDocumentos = new StdClass();
        $documento = array();
        array_push($documento, 'TODOS');
        array_push($documento, 'OTB-CERE');
        array_push($documento, 'OP-CERECO');
        $inputDocumentos->opciones = $documento;
        $inputDocumentos->id = 'documento';
        $inputDocumentos->name = 'documento';
        $inputDocumentos->required = true;
        $inputDocumentos->search = true;
        $inputDocumentos->mensaje = 'SELECCIONE EL DOCUMENTO';
        $inputDocumentos->label = 'DOCUMENTO';

        $inputTipo = new StdClass();
        $inputTipo->id = 'tipo';
        $inputTipo->sql = 'select tcl_id,tcl_nombre from cat_TipoClc';
        $inputTipo->search = true;
        $inputTipo->label = 'TIPO';
        $inputTipo->change = '
            if ($(this).val() != "") {
                comunica("' . $this->pantalla . '",{
                    metodo:"rellenaSubTipo",
                    tipo:$("#tipo").val()
                })
            }';

        $inputSubTipo = new StdClass();
        $inputSubTipo->id = 'subtipo';
        $inputSubTipo->name = 'subtipo';
        $inputSubTipo->enDiv = true;
        $inputSubTipo->label = 'SUBTIPO';

        $inputImpuesto = new StdClass();
        $impuesto = array();
        array_push($impuesto, 'Seleccionar');
        array_push($impuesto, 'ISR');
        array_push($impuesto, 'IMSS');
        array_push($impuesto, 'ISSSTE');
        array_push($impuesto, 'FONACOT');
        array_push($impuesto, 'FONACOT');
        array_push($impuesto, 'INFONAVIT');
        $inputImpuesto->opciones = $impuesto;
        $inputImpuesto->id = 'impuesto';
        $inputImpuesto->name = 'impuesto';
        $inputImpuesto->search = true;
        $inputImpuesto->label = 'IMPUESTO OP';
        $inputImpuesto->mensaje = 'SELECCIONAR';

        $clc = new StdClass();
        $clc->id = 'clc';
        $clc->name = 'clc';
        $clc->placeholder = 'NUMERO CLC';
        $clc->enDiv = true;
        $clc->label = 'NUMERO CLC';

        $ordenes = new StdClass();
        $ordenes->id = 'orden';
        $ordenes->name = 'orden';
        $ordenes->placeholder = 'NUMERO ORDEN';
        $ordenes->enDiv = true;
        $ordenes->label = 'NUMERO ORDEN';

        $inputNumeroUR = new StdClass();
        $inputNumeroUR->id = 'numerour';
        $inputNumeroUR->name = 'numerour';
        $inputNumeroUR->placeholder = 'NUMERO UR';
        $inputNumeroUR->enDiv = true;
        $inputNumeroUR->label = 'NUMERO UR';
        $inputNumeroUR->formato = 'entero';
        $inputNumeroUR->maxlength = '3';

        $inputNombreUR = new StdClass();
        $inputNombreUR->id = 'nombreur';
        $inputNombreUR->name = 'nombreur';
        $inputNombreUR->sql = "SELECT CONCAT(gpo_id,LPAD(ur_id,2,'0'))as ur,ur_nombre from " . BD_CTRL . ".cat_UnidadResponsable WHERE gpo_id !=0";
        $inputNombreUR->search = true;
        $inputNombreUR->placeholder = 'UR';
        $inputNombreUR->enDiv = true;
        $inputNombreUR->label = 'UR';

        $rfc_input = new stdClass();
        $rfc_input->id = "rfc_id";
        $rfc_input->camposText = 'rfc_nombre';
        $rfc_input->required = true;
        $rfc_input->tabla = 'cat_Proveedor';
        $rfc_input->name = "control";
        $rfc_input->autocomplete = true;
        $rfc_input->placeholder = "F1: Catalogo";
        $rfc_input->campoVal = "rfc_id";
        $rfc_input->label = "R.F.C del proveedor";
        $rfc_input->desdecaracter = 4;
        $rfc_input->maxlength = 13;
        $rfc_input->enDiv = true;
        $rfc_input->class = 'F1Catalogo';
        $rfc_input->tablas = " INNER JOIN " . BD_SINPRES . ".cat_Proveedores USING ( rfc_id )";
        $rfc_input->blur = '
            if ($("#rfc_id").val() == "") {
                $("#proveedor").val("");
            }
        ';

        $inputProveedor = new StdClass();
        $inputProveedor->id = 'proveedor';
        $inputProveedor->name = 'proveedor';
        $inputProveedor->placeholder = 'NOMBRE PROVEEDOR';
        $inputProveedor->enDiv = true;
        $inputProveedor->label = 'NOMBRE PROVEEDOR';

        $inputBeneficiario = new StdClass();
        $inputBeneficiario->id = 'beneficiario';
        $inputBeneficiario->name = 'beneficiario';
        $inputBeneficiario->placeholder = 'NOMBRE BENEFICIARIO';
        $inputBeneficiario->enDiv = true;
        $inputBeneficiario->label = 'NOMBRE BENEFICIARIO';

        $inputConcepto = new StdClass();
        $inputConcepto->id = 'concepto';
        $inputConcepto->name = 'concepto';
        $inputConcepto->placeholder = 'CONCEPTO';
        $inputConcepto->enDiv = true;
        $inputConcepto->label = 'CONCEPTO';

        $inputImporteMin = new StdClass();
        $inputImporteMin->id = 'importeMin';
        $inputImporteMin->name = 'importeMin';
        $inputImporteMin->placeholder = 'MINIMO';
        $inputImporteMin->enDiv = true;
        $inputImporteMin->label = 'IMPORTE MINIMO';
        $inputImporteMin->formato = 'moneda';

        $inputImporteMax = new StdClass();
        $inputImporteMax->id = 'importeMax';
        $inputImporteMax->name = 'importeMax';
        $inputImporteMax->placeholder = 'MAXIMO';
        $inputImporteMax->enDiv = true;
        $inputImporteMax->label = 'IMPORTE MAXIMO';
        $inputImporteMax->formato = 'moneda';

        $inputFechaMin = new StdClass();
        $inputFechaMin->id = 'fechaMin';
        $inputFechaMin->name = 'fechaMin';
        $inputFechaMin->placeholder = 'MINIMA';
        $inputFechaMin->enDiv = true;
        $inputFechaMin->label = 'FECHA MINIMA';
        $inputFechaMin->formato = 'fecha';

        $inputFechaMax = new StdClass();
        $inputFechaMax->id = 'fechaMax';
        $inputFechaMax->name = 'fechaMax';
        $inputFechaMax->placeholder = 'MAXIMA';
        $inputFechaMax->enDiv = true;
        $inputFechaMax->label = 'FECHA MAXIMA';
        $inputFechaMax->formato = 'fecha';

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->color = "gris";
        $btnBuscar->icon = "fa fa-search me-1";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTabla",
                anio:$("#anio option:selected").text().split(" ")[2].trim(),
                estado:$("#estado").val(),
                modo:$("#modo").val(),
                documento:$("#documento").val(),
                tipo:$("#tipo").val(),
                subtipo:$("#subtipo").val(),
                impuesto:$("#impuesto").val(),
                clc:$("#clc").val(),
                orden:$("#orden").val(),
                numerour:$("#numerour").val(),
                nombreur:$("#nombreur").val(),
                rfc:$("#rfc_id").val(),
                proveedor:$("#proveedor").val(),
                beneficiario:$("#beneficiario").val(),
                concepto:$("#concepto").val(),
                importeMin:$("#importeMin").val(),
                importeMax:$("#importeMax").val(),
                fechaMin:$("#fechaMin").val(),
                fechaMax:$("#fechaMax").val()
            })';

        $btnLimpiarCampo = new stdClass();
        $btnLimpiarCampo->id = "limpiarCampos";
        $btnLimpiarCampo->name = "limpiarCampos";
        $btnLimpiarCampo->value = "Limpiar Campos";
        $btnLimpiarCampo->color = "amarillo";
        $btnLimpiarCampo->icon = "fa fa-trash me-1";
        $btnLimpiarCampo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"limpiarCampos",
            })';

        $btnLimpiar = new stdClass();
        $btnLimpiar->id = "limpiar";
        $btnLimpiar->name = "limpiar";
        $btnLimpiar->value = "Limpiar Clc";
        $btnLimpiar->color = "amarillo";
        $btnLimpiar->icon = "fa fa-trash me-1";

        $btnLimpiar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"limpiarDatos",
            })';
        $btnAgregaTodo = new stdClass();
        $btnAgregaTodo->id = "btn_agregaTodo";
        $btnAgregaTodo->name = "btn_agregaTodo";
        $btnAgregaTodo->value = "Agregar Todo";
        $btnAgregaTodo->color = "verde";
        $btnAgregaTodo->icon = "fa-solid fa-plus me-1";
        $btnAgregaTodo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"mostrarModal",
                anio:$("#anio option:selected").text().split(" ")[2].trim(),
                estado:$("#estado").val(),
                modo:$("#modo").val(),
                documento:$("#documento").val(),
                tipo:$("#tipo").val(),
                subtipo:$("#subtipo").val(),
                impuesto:$("#impuesto").val(),
                clc:$("#clc").val(),
                orden:$("#orden").val(),
                numerour:$("#numerour").val(),
                nombreur:$("#nombreur").val(),
                rfc:$("#rfc_id").val(),
                proveedor:$("#proveedor").val(),
                beneficiario:$("#beneficiario").val(),
                concepto:$("#concepto").val(),
                importeMin:$("#importeMin").val(),
                importeMax:$("#importeMax").val(),
                fechaMin:$("#fechaMin").val(),
                fechaMax:$("#fechaMax").val()
            })';

        //Boton de excel
        $btnAgreaTd = $this->ponBoton($btnAgregaTodo);
        if (!$this->escritura) {
            $btnAgreaTd = "";
        }
        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "verde";
        $btnExcel->value = "Excel";
        $btnExcel->icon = "fa-solid fa-file-excel me-1";
        $btnExcel->name = "prueba";
        $btnExcel->click = '
        printCmp();
        ';
        $this->mixScript('
        function printCmp(){
        var form = $("<form>")
        .attr("action", "rep/excel/tes/tesoreria/exp_ProgramacionClcsPendientes.php")
        .attr("method", "post")
        .attr("target", "_blank")
        .attr("id", "enviaPost")
        .append($("<input>").attr("type", "hidden").attr("name", "anio").val( $("#anio option:selected").text().split(" ")[2].trim()))
        .append($("<input>").attr("type", "hidden").attr("name", "estado").val( $("#estado").val() ))
        .append($("<input>").attr("type", "hidden").attr("name", "modo").val( $("#modo").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "documento").val( $("#documento").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "tipo").val( $("#tipo").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "subtipo").val( $("#subtipo").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "impuesto").val( $("#impuesto").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "clc").val( $("#clc").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "orden").val( $("#orden").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "numerour").val( $("#numerour").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "nombreur").val( $("#nombreur").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "rfc").val( $("#rfc_id").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "proveedor").val( $("#proveedor").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "beneficiario").val( $("#beneficiario").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "concepto").val( $("#concepto").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "importeMin").val( $("#importeMin").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "importeMax").val( $("#importeMax").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "fechaMin").val( $("#fechaMin").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "fechaMax").val( $("#fechaMax").val()))
        $("body").append(form);
        form.submit();
        form.remove();
                }
            ');
        //Generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '
            <div class="row mb-4 class="col-md-12 col-xs-12">
              <legend>FILTROS DE BUSQUEDA
              </legend>
              <fieldset class="col-md-3 col-xs-3 " id="">
                  ' . $this->ponCombo($inputAnio) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponCombo($inputEstado) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponCombo($inputModo) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponCombo($inputDocumentos) . '
              </fieldset>
            </div>

            <div class="row mb-4 class="col-md-12 col-xs-12"">
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponCombo($inputTipo) . '
              </fieldset>
              <fieldset id="divListaSubtipos" class="col-md-3 col-xs-3">
                  </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="" >
                  ' . $this->ponCombo($inputImpuesto) . '
              </fieldset>

            </div>

            <div class="row mb-4">

              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponCombo($inputNombreUR) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($clc) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($ordenes) . '
              </fieldset>
            </div>
            <div class="row mb-4">
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($rfc_input) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($inputProveedor) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($inputConcepto) . '
              </fieldset>
              <fieldset class="col-md-3 col-xs-3" id="">
                  ' . $this->ponInput($inputBeneficiario) . '
              </fieldset>
            </div>

            <div class="row mb-4">
            <fieldset class="col-md-3 col-xs-3" id="">
                ' . $this->ponInput($inputImporteMin) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3" id="">
                ' . $this->ponInput($inputImporteMax) . '
            </fieldset>
            <fieldset class="col-md-3 col-xs-3" id="">
                ' . $this->ponInput($inputFechaMin) . '
            </fieldset>

            <fieldset class="col-md-3 col-xs-3" id="">
                ' . $this->ponInput($inputFechaMax) . '
            </fieldset>
            </div>
            <div class="row">

            </div>
            <div class="row ">
            <fieldset id="divProveedor" class="col-md-6 col-xs-6">
            </fieldset>
            </div>
            <div class="row mb-4">
            <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnBuscar) . '
            </fieldset>
            <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnLimpiarCampo) . '
            </fieldset>
            <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnLimpiar) . '
            </fieldset>
            <fieldset class="col-md-2 col-xs-2" id="">
                ' . $btnAgreaTd . '
            </fieldset>
            <fieldset class="col-md-2 col-xs-2" id="">
                ' . $this->ponBoton($btnExcel) . '
            </fieldset>
            </div>
            <div class="row">
            <fieldset id="divTabla" class="col-md-12 col-xs-12">
            </div>
            <div id="divModalTablaClc"></div>
        ';
        $this->mixHtml($html);
        // Dibujamos la pantalla que permite buscar un proveedor
        // --------------------------------------------------------------------------------------------------------------
        $this->mixScript('
            $("#rfc_id").focus( function(e) {
                rfcCampo = "rfc_id";
                rfcBeneficiario = "proveedor";
            });
            
            ' . $this->ponF1Proveedores('rfc_id', [], 'proveedor') . '
        ');
    }

    private function agregaTodo()
    {
        $this->mixScript('
            $("#modalTablaClc").modal("hide");
        ');
        $this->prm->anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $this->prm->estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $this->prm->modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $this->prm->documento = isset($this->prm->documento) ? $this->prm->documento : "";
        $this->prm->tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $this->prm->subtipo = isset($this->prm->subtipo) ? $this->prm->subtipo : "";
        $this->prm->impuesto = isset($this->prm->impuesto) ? $this->prm->impuesto : "";
        $this->prm->clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $this->prm->orden = isset($this->prm->orden) ? $this->prm->orden : "";
        $this->prm->numerour = isset($this->prm->numerour) ? $this->prm->numerour : "";
        $this->prm->nombreur = isset($this->prm->nombreur) ? $this->prm->nombreur : "";
        $this->prm->rfc = isset($this->prm->rfc) ? $this->prm->rfc : "";
        $this->prm->proveedor = isset($this->prm->proveedor) ? $this->prm->proveedor : "";
        $this->prm->beneficiario = isset($this->prm->beneficiario) ? $this->prm->beneficiario : "";
        $this->prm->concepto = isset($this->prm->concepto) ? $this->prm->concepto : "";
        $this->prm->importeMin = isset($this->prm->importeMin) ? trim($this->prm->importeMin, " $ ") : "";
        $this->prm->importeMax = isset($this->prm->importeMax) ? trim($this->prm->importeMax, " $ ") : "";
        $this->prm->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : "";
        $this->prm->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : "";
        $this->prm->importeMin = str_replace(',', '', $this->prm->importeMin);
        $this->prm->importeMax = str_replace(',', '', $this->prm->importeMax);
        $sql = "";

        if ($this->prm->fechaMin != "") {
            $arrFecha = explode("/", $this->prm->fechaMin);
            $this->prm->fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($this->prm->fechaMax != "") {
            $arrFecha = explode("/", $this->prm->fechaMax);
            $this->prm->fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }

        switch ($this->prm->modo) {
            case 5: //Caso proveedores
                switch ($this->prm->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($this->prm);
                        break;
                }
                break;
            default: //Demas casos
                switch ($this->prm->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($this->prm);
                        break;
                }
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $i = 1;
        while ($row = $conexion->extraer()) {
            $this->prm->abc = "a";
            $this->prm->ejercicio = $this->prm->anio;
            $this->prm->gpo_id = $row['GPO_ID'];
            $this->prm->ur_id = $row['UR_ID'];
            $this->prm->clc_id = $row['CLC'];
            $this->prm->importeClc = $row['IMPORTE'];
            $this->prm->bur_id = $row['DESTINO'];
            $this->prm->pro_id = $this->usuario_id . date("mdhis") . $i;
            $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id, $this->prm->ejercicio];
            $sql = "SELECT pro_ejercicio,gpo_id,ur_id,clc_id
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc
                    WHERE gpo_id=?
                        AND ur_id=?
                        AND clc_id=?
                        AND pro_ejercicio=?";
            $this->setSql($sql);
            $this->conectar();
            $this->consultar($params);
            //Antes de insertar preguntamos si existen coinsidencias
            if ($this->getNumeroRegistros() > 0) {
                $this->mixScript('notify("CLC OCUPADA ","ERROR")');
            } else {
                TemporalPropuestaClc::abc($this->prm);
            }
            $i++;
        }

        $sql =
            "SELECT
                    FORMAT(sum(p.pro_importeClc),2) importe, count(p.clc_id) contador, c.mpc_id
                FROM
                    " . BD_TESORERITEM . ".tem_PropuestaClc p ";
        switch ($this->prm->anio) {
            case 2017:
                $sql .= "INNER JOIN " . BD_CTRL_2017 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2018:
                $sql .= "INNER JOIN " . BD_CTRL_2018 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2019:
                $sql .= "INNER JOIN " . BD_CTRL_2019 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2020:
                $sql .= "INNER JOIN " . BD_CTRL_2020 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2021:
                $sql .= "INNER JOIN " . BD_CTRL_2021 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2022:
                $sql .= "INNER JOIN " . BD_CTRL_2022 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2023:
                $sql .= "INNER JOIN " . BD_CTRL_2023 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2024:
                $sql .= "INNER JOIN " . BD_CTRL_2024 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2025:
                $sql .= "INNER JOIN " . BD_CTRL_2025 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2026:
                $sql .= "INNER JOIN " . BD_CTRL . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
        }

        $sql .= "WHERE p.usr_id=?
            GROUP BY c.mpc_id";

        $this->setSql($sql);
        $this->conectar();
        $this->consultar([$this->usuario_id]);
        $row = $this->extraer();
        $importe = ($this->getNumeroRegistros() > 0) ? $row['importe'] : 0;
        $contador = ($this->getNumeroRegistros() > 0) ? $row['contador'] : 0;

        $btnDetallesPropuesta = new stdClass();
        $btnDetallesPropuesta->id = 'btnDetallesPropuesta';
        $btnDetallesPropuesta->name = 'btnDetallesPropuesta';
        $btnDetallesPropuesta->text = 'Propuesta';
        $btnDetallesPropuesta->value = $contador . ' $' . $importe;
        $btnDetallesPropuesta->type = 'button';
        $btnDetallesPropuesta->icon = 'fa-solid fa-magnifying-glass me-2';
        $btnDetallesPropuesta->color = 'cielo';
        $btnDetallesPropuesta->click = '
                comunica("' . $this->pantalla . '",{
                    metodo:"pintaDetallesClc",
                    ejercicio: $("#anio option:selected").text().split(" ")[2].trim()
                })';

        //Boton guardarpropuesta
        $btnGuardarPropuesta = new stdClass();
        $btnGuardarPropuesta->id = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->name = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->text = 'Generar Propuesta';
        $btnGuardarPropuesta->value = '&nbsp;&nbsp;Generar Propuesta';
        $btnGuardarPropuesta->type = 'button';
        $btnGuardarPropuesta->class = 'btn btn-outline-success me-1';
        $btnGuardarPropuesta->icon = 'fa fa-fa-solid fa-share-from-square me-2';
        $btnGuardarPropuesta->click = '
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                $(this).attr("disabled",true);
                comunica("' . $this->pantalla . '",{
                    metodo:"generarPropuesta"
                })';

        if ($contador > 0 && $this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardarPropuesta));
            $this->agregaTools($this->ponBoton($btnDetallesPropuesta));
        }

        $this->llenaTablaClc();
    }

    private function pintaTabla()
    {
        //Si el modo de pago cambio borramos todos los datos;
        if ($this->metodoPago != $this->prm->modo) {
            $this->eliminarDetalleCompleto();
        }
        $_SESSION[_S]['prm']['metodoPago'] = $this->prm->modo == 0 ? 1 : $this->prm->modo;
        $this->ejercicio = isset($this->prm->anio) ? $this->prm->anio : APP_EJERCICIO;

        $this->setContenedor('divTabla');
        $tabla = new stdClass();
        $tabla->multiseleccionable = true;
        $tabla->prmSeleccion = array('GPO_ID');
        if ($_SESSION[_S]['prm']['metodoPago'] == 2 && $this->ejercicio == APP_EJERCICIO) {
            $tabla->casoSeleccionable = new stdClass();
            $tabla->casoSeleccionable->campo = "pintaDiff";
            $tabla->casoSeleccionable->valor = "0";
            $tabla->casoSeleccionable->operador = "==";
            $tabla->casoSeleccionable->colorEnFalso = "rgba(254, 0, 0, 0.20)";
        }
        $tabla->id = 'tbl-clcs';
        $tabla->titulo = 'Listado de CLCs Pendientes';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaClc';
        $tabla->comunica->anio = '{$("#anio option:selected").text().split(" ")[2].trim()}';
        $tabla->comunica->estado = '{$("#estado").val()}';
        $tabla->comunica->modo = '{$("#modo").val()}';
        $tabla->comunica->documento = '{$("#documento").val()}';
        $tabla->comunica->tipo = '{$("#tipo").val()}';
        $tabla->comunica->subtipo = '{$("#subtipo").val()}';
        $tabla->comunica->impuesto = '{$("#impuesto").val()}';
        $tabla->comunica->clc = '{$("#clc").val()}';
        $tabla->comunica->orden = '{$("#orden").val()}';
        $tabla->comunica->numerour = '{$("#numerour").val()}';
        $tabla->comunica->nombreur = '{$("#nombreur").val()}';
        $tabla->comunica->rfc = '{$("#rfc_id").val()}';
        $tabla->comunica->proveedor = '{$("#proveedor").val()}';
        $tabla->comunica->beneficiario = '{$("#beneficiario").val()}';
        $tabla->comunica->concepto = '{$("#concepto").val()}';
        $tabla->comunica->importeMin = '{$("#importeMin").val()}';
        $tabla->comunica->importeMax = '{$("#importeMax").val()}';
        $tabla->comunica->fechaMin = '{$("#fechaMin").val()}';
        $tabla->comunica->fechaMax = '{$("#fechaMax").val()}';

        $tr = array();

        if ($this->escritura) {
            $th = new stdClass();
            $th->texto = 'AGREGAR';
            array_push($tr, $th);
        }

        $th = new stdClass();
        $th->texto = 'FE. ELA.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DIAS';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODO.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ORDEN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'T';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'SUBTIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        array_push($tr, $th);

        if ($this->metodoPago != 5) {
            $th = new stdClass();
            $th->texto = 'BENEFICIARIO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CONCEPTO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CTA CARGO';
            array_push($tr, $th);

            if ($this->metodoPago == 2) {
                $th = new stdClass();
                $th->texto = 'IMPORTE CLC';
                array_push($tr, $th);

                $th = new stdClass();
                $th->texto = 'IMPORTE LINEA';
                array_push($tr, $th);

                $th = new stdClass();
                $th->texto = 'VIGENCIA';
                array_push($tr, $th);
            } else {
                $th = new stdClass();
                $th->texto = 'IMPORTE';
                array_push($tr, $th);
            }

            $th = new stdClass();
            $th->texto = 'CVE_FIN';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CVE_NOMBRE';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'DET';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'INFO';
            array_push($tr, $th);
        } else {
            $th = new stdClass();
            $th->texto = 'RFC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'PROVEEDOR';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CONCEPTO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'TOTAL CLC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'TOTAL DOC';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'NCP';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'CUENTA ABONO';
            array_push($tr, $th);

            $th = new stdClass();
            $th->texto = 'INFO';
            array_push($tr, $th);
        }

        //CUERPO DE LA TABLA
        $trbody = array();

        if ($this->escritura) {
            $td = new stdClass();
            $td->tipo = 'boton';
            $td->icono = "plus";
            $td->color = "azul";
            $td->class = "addClcprop";
            $td->name = "IDCLC";
            $td->id = "IDCLC";
            $td->attr = new stdClass();
            $td->attr->UR_ID = "UR_ID";
            $td->attr->GPO_ID = "GPO_ID";
            $td->attr->CLC = "CLC";
            $td->attr->FOLIO = "FOLIO";
            $td->attr->BENEFICIARIO = "BENEFICIARIO";
            $td->attr->IMPORTE = "IMPORTE";
            $td->attr->CARGO = "CARGO";
            $td->attr->DESTINO = "DESTINO";
            $td->attr->TIPO = "TIPO";
            $td->attr->CONCEPTO = "CONCEPTO";
            $td->attr->CVE_FIN = "CVE_FIN";
            $td->attr->CVE_NOMBRE = "CVE_NOMBRE";
            $td->attr->TLO_ID = "TLO_ID";

            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'FECHA_ELABORACION';
        array_push($trbody, $td);

        if ($_SESSION[_S]['prm']['metodoPago'] == 2) {
            $td = new stdClass();
            $td->campoTxt = 'diferenciaDias';
            array_push($trbody, $td);
        } else {
            $td = new stdClass();
            $td->campoTxt = 'DIFERENCIA';
            array_push($trbody, $td);
        }

        $td = new stdClass();
        $td->campoTxt = 'MODO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'UR';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CLC';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'FOLIO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'TIPO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOMBRE_TIPO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'NOMBRE_SUBTIPO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'RESPONSABLE';
        array_push($trbody, $td);

        if ($this->metodoPago != 5) {

            $td = new stdClass();
            $td->campoTxt = 'BENEFICIARIO';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'CONCEPTO';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'CARGO';
            array_push($trbody, $td);

            if ($this->metodoPago == 2) {
                $td = new stdClass();
                $td->campoTxt = 'IMPORTE';
                $td->formato = 'moneda';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->campoTxt = 'importeLineas';
                $td->formato = 'moneda';
                array_push($trbody, $td);

                $td = new stdClass();
                $td->campoTxt = 'lca_fechaVencimiento';
                array_push($trbody, $td);
            } else {

                $td = new stdClass();
                $td->campoTxt = 'IMPORTE';
                $td->formato = 'moneda';
                array_push($trbody, $td);
            }

            $td = new stdClass();
            $td->campoTxt = 'CVE_FIN';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'CVE_NOMBRE';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'det';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'info';
            array_push($trbody, $td);
        } else {
            $td = new stdClass();
            $td->campoTxt = 'RFC';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'NOMBREPROVEEDOR';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'CONCEPTO';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'IMPORTE';
            $td->formato = 'moneda';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'IMPORTEPR';
            $td->formato = 'moneda';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'NCP_ID';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'DESTINO';
            array_push($trbody, $td);

            $td = new stdClass();
            $td->campoTxt = 'info';
            array_push($trbody, $td);
        }

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaClc();
    }

    private function liberarClcOtros()
    {
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";

        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id, $this->prm->ejercicio];
        $sql = "delete from " . BD_TESORERITEM . ".tem_PropuestaClc where  gpo_id=? and ur_id=? and clc_id=? and pro_ejercicio=?";
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($params);
    }
    private function llenaTablaClc()
    {
        //Listado de variables uno
        $datosClc = new stdClass();
        $datosClc->anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $datosClc->estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $datosClc->modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $datosClc->documento = isset($this->prm->documento) ? $this->prm->documento : "";
        $datosClc->tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $datosClc->subtipo = isset($this->prm->subtipo) ? $this->prm->subtipo : "";
        $datosClc->impuesto = isset($this->prm->impuesto) ? $this->prm->impuesto : "";
        $datosClc->clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $datosClc->orden = isset($this->prm->orden) ? $this->prm->orden : "";
        $datosClc->numerour = isset($this->prm->numerour) ? $this->prm->numerour : "";
        $datosClc->nombreur = isset($this->prm->nombreur) ? $this->prm->nombreur : "";
        $datosClc->rfc = isset($this->prm->rfc) ? $this->prm->rfc : "";
        $datosClc->proveedor = isset($this->prm->proveedor) ? $this->prm->proveedor : "";
        $datosClc->beneficiario = isset($this->prm->beneficiario) ? $this->prm->beneficiario : "";
        $datosClc->concepto = isset($this->prm->concepto) ? $this->prm->concepto : "";
        $datosClc->importeMin = isset($this->prm->importeMin) ? trim($this->prm->importeMin, " $ ") : "";
        $datosClc->importeMax = isset($this->prm->importeMax) ? trim($this->prm->importeMax, " $ ") : "";
        $datosClc->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : "";
        $datosClc->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : "";
        $datosClc->importeMin = str_replace(',', '', $datosClc->importeMin);
        $datosClc->importeMax = str_replace(',', '', $datosClc->importeMax);
        $sql = "";

        if ($datosClc->fechaMin != "") {
            $arrFecha = explode("/", $datosClc->fechaMin);
            $datosClc->fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($datosClc->fechaMax != "") {
            $arrFecha = explode("/", $datosClc->fechaMax);
            $datosClc->fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }

        switch ($datosClc->modo) {
            case 5: //Caso proveedores
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                }
                break;
            case 4: //Caso cadena productiva
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClcCadenaProductiva($datosClc);
                        break;
                }
                break;
            case 2: //Lineas de captura
                //Realizar la busqueda de las lineas
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2018:
                        $this->buscaSioxAnterior(2018);
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2019:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2019);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2020:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2020);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2021:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2021);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2022:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2022);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2023:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2023);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2024:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2024);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2025:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSioxAnterior(2025);
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2026:
                        //Consultamos en siox todas clc que no tienen linea
                        $this->buscaSiox();
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaLineas($datosClc);
                        break;
                }
                break;
            default: //Demas casos
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                }
                break;
        }

        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-clcs";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);
        $this->mixScript('
            $(".addClcprop").click(function(){

                comunica("' . $this->pantalla . '",{
                    metodo:"agregaClc",
                    ejercicio: $("#anio option:selected").text().split(" ")[2].trim(),
                    anio: $("#anio option:selected").text().split(" ")[2].trim(),
                    ur_id: $(this).attr("UR_ID"),
                    gpo_id: $(this).attr("GPO_ID"),
                    clc_id: $(this).attr("CLC"),
                    bur_id: $(this).attr("DESTINO"),
                    importe: $(this).attr("IMPORTE"),
                    tlo_id: $(this).attr("TLO_ID"),
                    anio: $("#anio").val(),
                    estado: $("#estado").val(),
                    modo: $("#modo").val(),
                    documento: $("#documento").val(),
                    tipo: $("#tipo").val(),
                    subtipo: $("#subtipo").val(),
                    impuesto: $("#impuesto").val(),
                    clc: $("#clc").val(),
                    orden: $("#orden").val(),
                    numerour: $("#numerour").val(),
                    nombreur: $("#nombreur").val(),
                    rfc: $("#rfc_id").val(),
                    proveedor: $("#proveedor").val(),
                    beneficiario: $("#beneficiario").val(),
                    concepto: $("#concepto").val(),
                    importeMin: $("#importeMin").val(),
                    importeMax: $("#importeMax").val(),
                    fechaMin: $("#fechaMin").val(),
                    fechaMax: $("#fechaMax").val()
                });

            });
        ');
    }

    private function agregaClc()
    {
        $this->prm->abc = "a";
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : APP_EJERCICIO;
        $this->prm->anio = $this->prm->ejercicio;
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";
        $this->prm->importeClc = isset($this->prm->importe) ? $this->prm->importe : "";
        $this->prm->bur_id = isset($this->prm->bur_id) ? $this->prm->bur_id : "";
        $this->prm->pro_id = $this->usuario_id . date("mdhis");

        $params = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id, $this->prm->ejercicio];
        $sql = "SELECT pro_ejercicio,gpo_id,ur_id,clc_id" .
            " FROM " . BD_TESORERITEM . ".tem_PropuestaClc" .
            " WHERE gpo_id=?" .
            " and ur_id=?" .
            " and clc_id=?" .
            " and pro_ejercicio=?";
        $this->setSql($sql);
        $this->conectar();
        $this->consultar($params);
        //Antes de insertar preguntamos si existen coinsidencias
        if ($this->getNumeroRegistros() > 0) {
            $this->mixScript('
                alertify.confirm(
                    "<h5> LIBERAR CLC </h5>",
                    "¿La CLC esta ocupada por otro usuario desea liberarla?",
                    function(){
                        comunica("' . $this->pantalla . '",{
                            metodo:"liberarClcOtros",
                            gpo_id: ' . $this->prm->gpo_id . ',
                            ur_id: ' . $this->prm->ur_id . ',
                            clc_id: ' . $this->prm->clc_id . ',
                            ejercicio: ' . $this->prm->ejercicio . '
                        });
                    },
                    function(){},
                ).set("defaultFocus", "cancel").set("labels", {ok:"Liberar", cancel:"Cancelar"});
            ');
        } else {
            TemporalPropuestaClc::abc($this->prm);
        }

        $sql =
            "SELECT
                FORMAT(sum(p.pro_importeClc),2) importe, count(p.clc_id) contador, c.mpc_id
            FROM
                " . BD_TESORERITEM . ".tem_PropuestaClc p ";
        switch ($this->prm->ejercicio) {
            case 2017:
                $sql .= "INNER JOIN " . BD_CTRL_2017 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2018:
                $sql .= "INNER JOIN " . BD_CTRL_2018 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2019:
                $sql .= "INNER JOIN " . BD_CTRL_2019 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2020:
                $sql .= "INNER JOIN " . BD_CTRL_2020 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2021:
                $sql .= "INNER JOIN " . BD_CTRL_2021 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2022:
                $sql .= "INNER JOIN " . BD_CTRL_2022 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2023:
                $sql .= "INNER JOIN " . BD_CTRL_2023 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2024:
                $sql .= "INNER JOIN " . BD_CTRL_2024 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2025:
                $sql .= "INNER JOIN " . BD_CTRL_2025 . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
            case 2026:
                $sql .= "INNER JOIN " . BD_CTRL . ".scp_Clc c using (gpo_id,ur_id,clc_id)";
                break;
        }

        $sql .= "WHERE p.usr_id=?
            GROUP BY c.mpc_id";

        $this->setSql($sql);
        $this->conectar();
        $this->consultar([$this->usuario_id]);
        $row = $this->extraer();
        $importe = ($this->getNumeroRegistros() > 0) ? $row['importe'] : 0;
        $contador = ($this->getNumeroRegistros() > 0) ? $row['contador'] : 0;

        $btnDetallesPropuesta = new stdClass();
        $btnDetallesPropuesta->id = 'btnDetallesPropuesta';
        $btnDetallesPropuesta->name = 'btnDetallesPropuesta';
        $btnDetallesPropuesta->text = 'Propuesta';
        $btnDetallesPropuesta->value = $contador . ' $' . $importe;
        $btnDetallesPropuesta->type = 'button';
        $btnDetallesPropuesta->icon = "fa-solid fa-magnifying-glass me-2";
        $btnDetallesPropuesta->color = 'cielo';
        $btnDetallesPropuesta->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaDetallesClc",
                ejercicio: $("#anio option:selected").text().split(" ")[2].trim()
            })';

        //Boton guardarpropuesta
        $btnGuardarPropuesta = new stdClass();
        $btnGuardarPropuesta->id = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->name = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->text = 'Generar Propuesta';
        $btnGuardarPropuesta->value = 'Generar Propuesta';
        $btnGuardarPropuesta->type = 'button';
        $btnGuardarPropuesta->class = "btn btn-outline-success me-1";
        $btnGuardarPropuesta->icon = "fa fa-fa-solid fa-share-from-square me-2";
        $btnGuardarPropuesta->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            $(this).attr("disabled",true);
            comunica("' . $this->pantalla . '",{
                metodo:"generarPropuesta"
            })';

        if ($contador > 0 && $this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardarPropuesta));
            $this->agregaTools($this->ponBoton($btnDetallesPropuesta));
        }

        $this->llenaTablaClc();
    }

    private function generarPropuesta()
    {
        $sql = "SELECT FORMAT(sum(pro_importeClc),2) importe,count(clc_id) contador, pro_ejercicio
            FROM " . BD_TESORERITEM . ".tem_PropuestaClc
            WHERE usr_id=?
            GROUP BY pro_ejercicio";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$this->usuario_id]);
        $row = $conexion->extraer();
        $contador = $row['contador'];
        $ejercicio = $row['pro_ejercicio'];
        if ($contador == 0) {
            $this->mixScript('
                alertify.alert(
                    "",
                    "<h5>No hay CLC seleccionadas</h5>"
                );
                $("#btnGuardarPropuesta").attr("disabled",false);
            ');
        } else {
            $datosSP = new stdClass();
            $datosSP->metodo_pago = $this->metodoPago;
            $datosSP->ejecicio = $ejercicio;
            $datosSP->bit_observacion = 'Insertado desde pantalla T_3_1';
            $datosSP->sis_id = "TES";
            $params = [$this->usuario_id, $this->ip];
            $sql = "set @usr_id=? , @usr_ip=INET_ATON(?) , @bit_observacion='Actualizacion'";
            $this->conectar();
            $this->setSql($sql);
            $this->consultar($params);
            $respuesta = sp_GenerarPropuesta::call($datosSP);
            $mensaje = str_replace("'", '', $respuesta->mensaje);
            $this->mixScript("notify('" . $mensaje . "');");
            $this->metodoPago = 1;
            $this->mainProgramacion();
        }
    }

    private function pintaDetallesClc()
    {
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";

        $btnregresarindex = new stdClass();
        $btnregresarindex->id = 'btnRegresar';
        $btnregresarindex->name = 'btnRegresar';
        $btnregresarindex->text = 'Regresar';
        $btnregresarindex->value = 'Regresar';
        $btnregresarindex->type = 'button';
        $btnregresarindex->class = "btn btn-outline-secondary me-2";
        $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
        $btnregresarindex->click = '
        // Regresa a la pantalla anterior
        $("#contenedor").html("");
        $("#toolsPantalla").html("");
        comunica("' . $this->pantalla . '",{
        metodo: "mainProgramacion"
        });
        ';
        $this->agregaTools($this->ponBoton($btnregresarindex));

        //Boton guardarpropuesta
        $sql = "SELECT FORMAT(sum(p.pro_importeClc),2) importe,count(p.clc_id) contador
          FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
          WHERE p.usr_id=?";

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$this->usuario_id]);
        $row = $conexion->extraer();
        $importe = $row['importe'];
        $contador = $row['contador'];

        $btnGuardarPropuesta = new stdClass();
        $btnGuardarPropuesta->id = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->name = 'btnGuardarPropuesta';
        $btnGuardarPropuesta->text = 'Generar Propuesta';
        $btnGuardarPropuesta->value = 'Generar Propuesta $' . $importe;
        $btnGuardarPropuesta->type = 'button';
        $btnGuardarPropuesta->class = "btn btn-outline-secondary btn btn-outline-success me-1 ";
        $btnGuardarPropuesta->icon = "fa fa-fa-solid fa-share-from-square me-2";
        $btnGuardarPropuesta->click = '
        $("#contenedor").html("");
        $("#toolsPantalla").html("");
        $(this).attr("disabled",true);
        comunica("' . $this->pantalla . '",{
            metodo:"generarPropuesta"
        })';
        if ($contador > 0 && $this->escritura) {
            $this->agregaTools($this->ponBoton($btnGuardarPropuesta));
        }

        $btnEliminaDetalles = new stdClass();
        $btnEliminaDetalles->id = 'btnEliminaDetalles';
        $btnEliminaDetalles->name = 'btnEliminaDetalles';
        $btnEliminaDetalles->text = 'Eliminar';
        $btnEliminaDetalles->value = 'Eliminar';
        $btnEliminaDetalles->type = 'button';
        $btnEliminaDetalles->class = "btn btn-outline-danger me-2";
        $btnEliminaDetalles->icon = "fa-solid fa-trash me-1";
        $btnEliminaDetalles->click = '
            // Regresa a la pantalla anterior
            alertify.confirm(
                "<h5> ELIMINAR CLCS DE LA PROPUESTA </h5>",
                "¿Realmente desea eliminar todos las CLCs de la propuesta?",
                function(){
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    comunica("' . $this->pantalla . '",{
                        metodo:"eliminarDetalleCompleto"
                    });
                },
                function(){},
            ).set("defaultFocus", "cancel").set("labels", {ok:"Eliminar", cancel:"Cancelar"});
            ';
        if ($this->escritura) {
            $this->agregaTools($this->ponBoton($btnEliminaDetalles));
        }

        //Inicio de la tabla
        $tabla = new stdClass();
        $tabla->id = 'tbl-detallesclcs';
        $tabla->titulo = 'Listado de CLCs seleccionadas';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetallesClc';
        $tabla->comunica->ejercicio = $this->prm->ejercicio;
        $tr = array();

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ORDEN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BENEFICIARIO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA CARGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CV_FIN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ELIMINAR';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'UR';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CLC';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ORDEN';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'IMPORTE';
        $td->formato = "moneda";

        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'BENEFICIARIO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CONCEPTO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CTACARGO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CTADESTINO';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'CV_FIN';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = "fa-solid fa-trash me-1";
        $td->class = "quitarClc";
        $td->color = 'rojo';
        $td->name = "btn-quitarClc";
        $td->id = "btn-quitarClc";
        $td->attr = new stdClass();
        $td->attr->GPO_ID = "GPO_ID";
        $td->attr->UR_ID = "UR_ID";
        $td->attr->CLC = "CLC";
        $td->attr->ejercicio = $this->prm->ejercicio;

        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->llenaTablaDetallesClc();

        $this->mixScript('
            if($(".ui-dialog").length>0){
                $(".ui-dialog").remove();
            }
        ');
    }

    private function llenaTablaDetallesClc()
    {
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : "";
        //Pintamos la tabla
        switch ($this->prm->ejercicio) {
            case 2017:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    concat(v.fte_id,v.sft_id,v.ori_id,v.sor_id,v.cso_id,v.cfi_id,v.cfi_anio) CV_FIN,
                    b.bsf_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2017 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2018:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2018 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2019:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2019 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2020:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2020 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2021:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2021 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2022:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2022 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2023:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2023 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2024:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2024 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2025:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL_2025 . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
            case 2026:
                $sql = "SELECT
                    p.gpo_id GPO_ID,
                    p.ur_id UR_ID,
                    concat(p.gpo_id,p.ur_id) UR,
                    p.clc_id CLC,
                    p.pro_importeClc IMPORTE,
                    x.clc_cereCo ORDEN,
                    c.clc_beneficiario BENEFICIARIO,
                    c.clc_concepto CONCEPTO,
                    v.ecf_clave CV_FIN,
                    b.cbs_id CTACARGO,
                    c.bur_id CTADESTINO
                    FROM " . BD_TESORERITEM . ".tem_PropuestaClc p
                    INNER JOIN " . BD_CTRL . ".scp_Clc c using(gpo_id,ur_id,clc_id)
                    INNER JOIN " . BD_CTRL . ".scp_ClcAuxiliar x USING(gpo_id, ur_id, clc_id)
                    INNER JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento b USING (ecf_id)
                    INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento v using(ecf_id)
                    WHERE p.usr_id=?;";
                break;
        }

        $this->conectar();
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = (isset($this->prm->nav->nf)) ? $this->prm->nav->nf : 5;
        $nav->idTabla = "tbl-detallesclcs";
        $this->setParams([$this->usuario_id]);
        $this->wrLlenaTabla($nav);
        $this->mixScript('
          $(".quitarClc").click(function(){
            comunica("' . $this->pantalla . '",{
              metodo: "quitarClc",
              gpo_id:        $(this).attr("GPO_ID"),
              ur_id:        $(this).attr("UR_ID"),
              clc_id:        $(this).attr("CLC"),
              ejercicio:        $(this).attr("ejercicio")

            });
          });
      ');
    }

    private function quitarClc()
    {
        $this->prm->abc = "b";
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : "";
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : "";
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : "";
        //Leer el año segun varible de session
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : APP_EJERCICIO;
        TemporalPropuestaClc::abc($this->prm);

        $sql = "SELECT FORMAT(sum(pro_importeClc),2) importe,count(clc_id) contador FROM " . BD_TESORERITEM . ".tem_PropuestaClc where usr_id=?;";
        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar([$this->usuario_id]);
        $row = $conexion->extraer();
        $importe = $row['importe'];
        $contador = $row['contador'];

        if ($contador == 0) {
            $this->mixScript('
                $("#toolsPantalla").html("");
            ');
            $this->mainProgramacion();
        } else {

            //Boton guardarpropuesta
            $btnregresarindex = new stdClass();
            $btnregresarindex->id = 'btnRegresar';
            $btnregresarindex->name = 'btnRegresar';
            $btnregresarindex->text = 'Regresar';
            $btnregresarindex->value = 'Regresar';
            $btnregresarindex->type = 'button';
            $btnregresarindex->class = "btn btn-outline-secondary me-2";
            $btnregresarindex->icon = "fa-solid fa-circle-left me-1";
            $btnregresarindex->click = '
            // Regresa a la pantalla anterior
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
            metodo: "mainProgramacion"
            });
            ';
            $this->agregaTools($this->ponBoton($btnregresarindex));

            $btnGuardarPropuesta = new stdClass();
            $btnGuardarPropuesta->id = 'btnGuardarPropuesta';
            $btnGuardarPropuesta->name = 'btnGuardarPropuesta';
            $btnGuardarPropuesta->text = 'Generar Propuesta';
            $btnGuardarPropuesta->value = 'Generar Propuesta $' . $importe;
            $btnGuardarPropuesta->type = 'button';
            $btnGuardarPropuesta->class = "'btn btn-outline-success me-2 ";
            $btnGuardarPropuesta->icon = "fa fa-share";
            $btnGuardarPropuesta->click = '
            $("#contenedor").html("");
            $("#toolsPantalla").html("");
            $(this).attr("disabled",true);
            comunica("' . $this->pantalla . '",{
            metodo:"generarPropuesta"
            })';
            if ($contador > 0) {
                $this->agregaTools($this->ponBoton($btnGuardarPropuesta));
            }

            $btnEliminaDetalles = new stdClass();
            $btnEliminaDetalles->id = 'btnEliminaDetalles';
            $btnEliminaDetalles->name = 'btnEliminaDetalles';
            $btnEliminaDetalles->text = 'Eliminar';
            $btnEliminaDetalles->value = 'Eliminar';
            $btnEliminaDetalles->type = 'button';
            $btnEliminaDetalles->class = "btn-outline-danger me-2h ";
            $btnEliminaDetalles->icon = "fa-solid fa-trash me-1";
            $btnEliminaDetalles->click = '
                // Regresa a la pantalla anterior
                if (confirm("¿Realmente desea eliminar todos las CLCs de la propuesta?")) {
                    $("#contenedor").html("");
                    $("#toolsPantalla").html("");
                    comunica("' . $this->pantalla . '",{
                        metodo: "eliminarDetalleCompleto"
                    });
                }
                ';
            $this->agregaTools($this->ponBoton($btnEliminaDetalles));
            $this->llenaTablaDetallesClc();
        }
    }

    private function eliminarDetalleCompleto()
    {
        $sql = "DELETE FROM " . BD_TESORERITEM . ".tem_PropuestaClc WHERE usr_id=?;";
        $this->conectar();
        $this->setSql($sql);
        $this->consultar([$this->usuario_id]);
        $this->metodoPago = 1;
    }

    private function rellenaSubTipo()
    {
        $this->setContenedor('divListaSubtipos');
        $this->prm->tipo = (isset($this->prm->tipo)) ? $this->prm->tipo : "";
        $inputSubTipo = new StdClass();
        $inputSubTipo->id = 'subtipo';
        $inputSubTipo->name = 'subtipo';
        $inputSubTipo->enDiv = true;
        $inputSubTipo->label = 'SUBTIPO';
        $inputSubTipo->sql = "SELECT stc_id,stc_nombre from cat_SubtipoClc where tcl_id=?;";
        $inputSubTipo->params = [$this->prm->tipo];
        $this->mixHtml($this->ponCombo($inputSubTipo));
    }

    private function limpiarDatos()
    {
        $this->eliminarDetalleCompleto();
        $this->mainProgramacion();
    }
    private function limpiarCampos()
    {

        $this->mainProgramacion();
    }
    private function buscaSiox()
    {
        $sql = "SELECT gpo_id,ur_id,clc_id,doc.bdo_id,doc.lca_folio,lca_LineaCaptura,lca_fechaVencimiento,
                    IF (lca_LineaCaptura is null,0,1) as existe
                FROM " . BD_CTRL . ".scp_Clc  as c
                INNER JOIN (
                    SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id, MIN(clc_id) clc_id,bdo_id,MIN(ncp_referencia) as lca_folio
                    FROM " . BD_CTRL . ".scp_Presupuestal AS pro
                     GROUP BY bdo_id
                    UNION ALL
                    SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id, MIN(clc_id) clc_id,bdo_id,MIN(ncc_referencia) as lca_folio
                    FROM " . BD_CTRL . ".scp_Contable AS pro
                    GROUP BY bdo_id
                 ) as doc using (gpo_id,ur_id,clc_id)
                LEFT JOIN " . BD_TESORERIA . ".lca_LineaCaptura as lc on lc.lca_folio = doc.lca_folio
                where c.mpc_id=2 and ecl_id=10 and lca_lineaCaptura IS NULL ";

        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql($sql);
        $conexion2->consultar();

        $cadLineas = "";
        $cadFolio = "";
        $cadVH = "";
        while ($row = $conexion2->extraer()) {
            //2.- ordenamos por tipo de folio

            if ($row['existe'] == 0) {
                if (is_numeric($row['lca_folio'])) {
                    if (strlen($row['lca_folio']) == 19) {
                        $cadLineas .= "'" . $row['lca_folio'] . "',";
                    } else if (strlen($row['lca_folio']) == 11) {
                        $cadFolio .= "'" . $row['lca_folio'] . "',";
                    }
                } else {
                    $lc = substr($row['lca_folio'], 0, 1);
                    if ($lc == 'V') {
                        $cadVH = $cadVH . "'" . $row['lca_folio'] . "',";
                    }
                }
            }
        }
        if (strlen($cadLineas) > 0) {
            $cadLineas = substr($cadLineas, 0, -1);
        }
        if (strlen($cadFolio) > 0) {
            $cadFolio = substr($cadFolio, 0, -1);
        }
        if (strlen($cadVH) > 0) {
            $cadVH = substr($cadVH, 0, -1);
        }
        if (!empty($cadFolio)) {
            $respuestaFolios = ws_Consulta_Linea($cadFolio, 1);
        }

        if (!empty($cadLineas)) {
            $respuestaLineas = ws_Consulta_Linea($cadLineas, 2);
        }

        if (!empty($cadVH)) {
            $respuestaVH = ws_Consulta_Linea($cadVH, 3);
        }

        //3.- mandamos los arreglos
        //4.- los arreglos recibidos se almacenan en la tabla lineacaptura
        if (!empty($respuestaFolios)) {
            $arrayLc = json_decode($respuestaFolios);
        }

        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = $lc->folio;
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = APP_EJERCICIO;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";

                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";

                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "
                SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }

        if (!empty($respuestaLineas)) {
            $arrayLc = json_decode($respuestaLineas);
        }

        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = substr($lc->folio, 0, 11);
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = APP_EJERCICIO;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";
                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";
                //Para el caso lineas de captura se realiza una segunda consulta de existencia
                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "
                SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }

        if (!empty($respuestaVH)) {
            $arrayLc = json_decode($respuestaVH);
        }

        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = $lc->folio;
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = APP_EJERCICIO;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";

                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";
                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "
                SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }
    }

    private function buscaSioxAnterior($anio)
    {
        $sql = "SELECT gpo_id,ur_id,clc_id,doc.bdo_id,doc.lca_folio,lca_LineaCaptura,lca_fechaVencimiento,
                    IF (lca_LineaCaptura is null,0,1) as existe
                FROM ctrl" . $anio . ".scp_Clc  as c
                INNER JOIN (
                    SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id, MIN(clc_id) clc_id,bdo_id,MIN(ncp_referencia) as lca_folio
                    FROM ctrl" . $anio . ".scp_Presupuestal AS pro
                    GROUP BY bdo_id
                    UNION ALL
                    SELECT MIN(gpo_id) gpo_id,MIN(ur_id) ur_id, MIN(clc_id) clc_id,bdo_id,MIN(ncc_referencia) as lca_folio
                    FROM ctrl" . $anio . ".scp_Contable AS pro
                    GROUP BY bdo_id
                 ) as doc using (gpo_id,ur_id,clc_id)
                LEFT JOIN " . BD_TESORERIA . ".lca_LineaCaptura as lc on lc.lca_folio = doc.lca_folio
                where c.mpc_id=2 and ecl_id=10 and lca_lineaCaptura IS NULL ";

        $conexion2 = new AccesoDatos();
        $conexion2->conectar();
        $conexion2->setSql($sql);
        $conexion2->consultar();

        $cadLineas = "";
        $cadFolio = "";
        $cadVH = "";
        while ($row = $conexion2->extraer()) {
            //2.- ordenamos por tipo de folio

            if ($row['existe'] == 0) {
                if (is_numeric($row['lca_folio'])) {
                    if (strlen($row['lca_folio']) == 19) {
                        $cadLineas .= "'" . $row['lca_folio'] . "',";
                    } else if (strlen($row['lca_folio']) == 11) {
                        $cadFolio .= "'" . $row['lca_folio'] . "',";
                    }
                } else {
                    $lc = substr($row['lca_folio'], 0, 1);
                    if ($lc == 'V') {
                        $cadVH = $cadVH . "'" . $row['lca_folio'] . "',";
                    }
                }
            }
        }
        if (strlen($cadLineas) > 0) {
            $cadLineas = substr($cadLineas, 0, -1);
        }
        if (strlen($cadFolio) > 0) {
            $cadFolio = substr($cadFolio, 0, -1);
        }
        if (strlen($cadVH) > 0) {
            $cadVH = substr($cadVH, 0, -1);
        }

        $respuestaFolios = ws_Consulta_Linea($cadFolio, 1);
        $respuestaLineas = ws_Consulta_Linea($cadLineas, 2);
        $respuestaVH = ws_Consulta_Linea($cadVH, 3);

        //3.- Mandamos los arreglos
        //4.- Los arreglos recibidos se almacenan en la tabla lineacaptura
        $arrayLc = json_decode($respuestaFolios);
        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = $lc->folio;
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = $anio;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";

                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";

                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }
        $arrayLc = json_decode($respuestaLineas);
        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = substr($lc->folio, 0, 11);
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = $anio;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";
                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";
                //Para el caso lineas de captura se realiza una segunda consulta de existencia
                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "
                SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }
        $arrayLc = json_decode($respuestaVH);
        if (!empty($arrayLc)) {
            foreach ($arrayLc as $j => $lc) {
                $linea = new stdClass();
                $linea->lca_folio = $lc->folio;
                $linea->lineaCaptura = $lc->lineaCaptura;
                $linea->fechaGeneracion = $lc->fechaGeneracion;
                $linea->fechaVencimiento = $lc->fechaVencimiento;
                $linea->importe = $lc->importe;
                $linea->cuentaAbono = $lc->clabe;
                $linea->ejr_id = $anio;
                $linea->tlc_id = "1";
                $linea->elc_id = "1";

                $linea->idConvenioBanco = $lc->idConvenioBanco;
                $linea->abc = "a";
                //Antes de insertar verificamos que no exista la linea
                $conexionlinea = new AccesoDatos();
                $conexionlinea->conectar();
                $newSQLLinea = "
                SELECT lca_lineaCaptura FROM " . BD_TESORERIA . ".lca_LineaCaptura WHERE lca_lineaCaptura=?";
                $conexionlinea->setSql($newSQLLinea);
                $conexionlinea->consultar([$lc->lineaCaptura]);
                $bandera = 0;
                while ($datosLinea = $conexionlinea->extraer()) {
                    $bandera = 1;
                }
                if ($bandera == 0) {
                    LineaCaptura::abc($linea);
                }
            }
        }
    }

    private function mostrarModal()
    {
        $datosClc = new stdClass();
        $datosClc->anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $datosClc->estado = isset($this->prm->estado) ? $this->prm->estado : "";
        $datosClc->modo = isset($this->prm->modo) ? $this->prm->modo : "";
        $datosClc->documento = isset($this->prm->documento) ? $this->prm->documento : "";
        $datosClc->tipo = isset($this->prm->tipo) ? $this->prm->tipo : "";
        $datosClc->subtipo = isset($this->prm->subtipo) ? $this->prm->subtipo : "";
        $datosClc->impuesto = isset($this->prm->impuesto) ? $this->prm->impuesto : "";
        $datosClc->clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $datosClc->orden = isset($this->prm->orden) ? $this->prm->orden : "";
        $datosClc->numerour = isset($this->prm->numerour) ? $this->prm->numerour : "";
        $datosClc->nombreur = isset($this->prm->nombreur) ? $this->prm->nombreur : "";
        $datosClc->rfc = isset($this->prm->rfc) ? $this->prm->rfc : "";
        $datosClc->proveedor = isset($this->prm->proveedor) ? $this->prm->proveedor : "";
        $datosClc->beneficiario = isset($this->prm->beneficiario) ? $this->prm->beneficiario : "";
        $datosClc->concepto = isset($this->prm->concepto) ? $this->prm->concepto : "";
        $datosClc->importeMin = isset($this->prm->importeMin) ? trim($this->prm->importeMin, " $ ") : "";
        $datosClc->importeMax = isset($this->prm->importeMax) ? trim($this->prm->importeMax, " $ ") : "";
        $datosClc->fechaMin = isset($this->prm->fechaMin) ? $this->prm->fechaMin : "";
        $datosClc->fechaMax = isset($this->prm->fechaMax) ? $this->prm->fechaMax : "";
        $datosClc->importeMax = str_replace(',', '', $datosClc->importeMax);
        $datosClc->importeMin = str_replace(',', '', $datosClc->importeMin);
        $sql = "";

        if ($datosClc->fechaMin != "") {
            $arrFecha = explode("/", $datosClc->fechaMin);
            $datosClc->fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($datosClc->fechaMax != "") {
            $arrFecha = explode("/", $datosClc->fechaMax);
            $datosClc->fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }

        switch ($datosClc->modo) {
            case 5: //Caso proveedores
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                    case 2026:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClcProveedor($datosClc);
                        break;
                }
                break;
            case 2: //Lineas de captura
                //Realiar la busqueda de las lineas
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2026:
                        //Consultamos en siox todas las clc´s que no tienen linea
                        $this->buscaSiox();
                        //Extraemos la fecha menor de la vinculacion con las lineas
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaLineas($datosClc);
                        break;
                }
                break;
            default: //Demas casos
                switch ($datosClc->anio) {
                    case 2017:
                        $metodoConsulta = new clcs2017();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2018:
                        $metodoConsulta = new clcs2018();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2019:
                        $metodoConsulta = new clcs2019();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2020:
                        $metodoConsulta = new clcs2020();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2021:
                        $metodoConsulta = new clcs2021();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2022:
                        $metodoConsulta = new clcs2022();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2023:
                        $metodoConsulta = new clcs2023();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2024:
                        $metodoConsulta = new clcs2024();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2025:
                        $metodoConsulta = new clcs2025();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                    case 2026:
                    default:
                        $metodoConsulta = new clcs2026();
                        $sql = $metodoConsulta->sqlLlenaTablaClc($datosClc);
                        break;
                }
                break;
        }

        $conexion = new AccesoDatos();
        $conexion->conectar();
        $conexion->setSql($sql);
        $conexion->consultar();
        $numeroClc = 0;
        $totalClc = 0;
        while ($row = $conexion->extraer()) {
            $totalClc += $row['IMPORTE'];
            $numeroClc++;
        }

        //Consultamos las lineas
        $this->setContenedor('divModalTablaClc');
        $this->mixHtml('
                    <div id="modalTablaClc" class="modal fade in"  role="dialog" data-backdrop="false">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">CLCs seleccionadas</h4>
                                    <button type="button" class="close btnCancelarTablaClc btn btn-xs btn-danger" data-dismiss="modal" aria-hidden="true">
                                        <i class="fa fa-xmark"></i>
                                    </button>

                                </div>
                                <div class="modal-body">
                                    Numero de CLCs: ' . $numeroClc . '
                                    <div class="row">
                                        <div class="col-md-6">
                                           Total del importe: $ ' . number_format($totalClc, 2, '.', ',') . '
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button id="btnGuardarTablaClc" type="button" class="btn btn-outline-success">Aceptar</button>
                                    <button type="button" class="btn btn-outline-danger btnCancelarTablaClc">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                ');
        $this->mixScript('
                    $(".btnCancelarTablaClc").click(function () {
                        $("#modalTablaClc").modal("hide");
                    });
                    $("#btnGuardarTablaClc").click(function () {
                            comunica("' . $this->pantalla . '", {
                                metodo: "agregaTodo",
                                anio:$("#anio option:selected").text().split(" ")[2].trim(),
                                estado:$("#estado").val(),
                                modo:$("#modo").val(),
                                documento:$("#documento").val(),
                                tipo:$("#tipo").val(),
                                subtipo:$("#subtipo").val(),
                                impuesto:$("#impuesto").val(),
                                clc:$("#clc").val(),
                                orden:$("#orden").val(),
                                numerour:$("#numerour").val(),
                                nombreur:$("#nombreur").val(),
                                rfc:$("#rfc_id").val(),
                                proveedor:$("#proveedor").val(),
                                beneficiario:$("#beneficiario").val(),
                                concepto:$("#concepto").val(),
                                importeMin:$("#importeMin").val(),
                                importeMax:$("#importeMax").val(),
                                fechaMin:$("#fechaMin").val(),
                                fechaMax:$("#fechaMax").val()
                            }, "modalTablaClc");
                    });
                    $("#modalTablaClc").modal("show");
                ');
    }
} //Fin del constructor

if (isset($_SESSION[_S]['prm']['T']['3']['1']['1'])) {
    new ControladorTesProgramacion();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
