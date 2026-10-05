<?php

/**
 * Description of ControladorConsultaClcsFondo
 *
 * @author ?
 * Collaborators: Juan Carlos Rojas Garcia <juan.rojas@finanzasoaxaca.gob.mx>
 * Created: 14-Feb-2019
 * Migrated: 18-Feb-2019
 * Comment: -----
 * Objective:
 * Regla de Negocio:     
 * Permits:    
 * */
session_start();

require_once dirname(__FILE__) . '/../../lib/WebResponse.php';
require_once dirname(__FILE__) . '/../../lib/constantes.php';

class ControladorConsultaClcsFondo extends WebResponse
{
    protected $nvj_id;
    protected $usr_id;

    public function __construct()
    {
        parent::__construct();

        $this->nvj_id = isset($_SESSION[_S]['nvj_id']);
        $this->usr_id = $_SESSION[_S]['usr_id'];

        switch ($this->nvj_id) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
                switch ($this->metodo) {
                    case 'pintaTablaConsultaCLC':
                        $this->pintaTablaConsultaCLC();
                        break;
                    case 'llenaTablaConsultaClc':
                        $this->llenaTablaConsultaClc();
                        break;
                    case 'pintaTablaDetalleClc':
                        $this->pintaTablaDetalleClc();
                        break;
                    case 'llenaTablaDetalleClc':
                        $this->llenaTablaDetalleClc();
                        break;
                    default:
                        $this->consultaClc();
                        break;
                }
                break;
        }
        $this->returnJson();
    }

    private function consultaClc()
    {
        $buscar = isset($this->prm->buscar) ? $this->prm->buscar : false;
        $busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : '';

        //crea el boton de regreso
        $this->mixScript('   
            DesactivaAtajosMenu();
            Mousetrap.bind("esc esc", function(e) { e.preventDefault(); $("#homeMenu").click(); e.stopPropagation(); });            
            
            $("#homeMenu").click( function(e) {
                $("#contenedor").html("");
                $("#toolsPantalla").html("");
                ActivaAtajosMenu();
            });
        ');

        $btnExcel = new stdClass();
        $btnExcel->id = "ListadoExcel";
        $btnExcel->color = "";
        $btnExcel->value = "&nbsp;Excel";
        $btnExcel->class = "btnImp btn-outline-success";
        $btnExcel->icon = 'fa-solid fa-file-excel me-2';
        $btnExcel->name = "prueba";
        $btnExcel->click = '          
        printCmp();
        ';
        $this->agregaTools($this->ponBoton($btnExcel));

        $inputEjercicio = new StdClass();
        $datos = array();
        array_push($datos, APP_EJERCICIO);
        array_push($datos, "2025");
        array_push($datos, "2024");
        array_push($datos, "2023");
        array_push($datos, "2022");
        array_push($datos, "2021");
        array_push($datos, "2020");
        $inputEjercicio->opciones = $datos;
        $inputEjercicio->id = 'ejercicio';
        $inputEjercicio->name = 'ejercicio';
        $inputEjercicio->required = true;
        $inputEjercicio->mensaje = 'SELECCIONE EL EJERCICIO';
        $inputEjercicio->label = 'EJERCICIO';

        $inputDocumento = new StdClass();
        $datos = array();
        array_push($datos, "TODOS");
        array_push($datos, "OTB-CERE");
        array_push($datos, "OP-CERECO");
        $inputDocumento->opciones = $datos;
        $inputDocumento->id = 'documento';
        $inputDocumento->name = 'documento';
        $inputDocumento->label = 'DOCUMENTO';

        $clc = new StdClass();
        $clc->id = 'clc';
        $clc->name = 'clc';
        $clc->placeholder = 'NUMERO CLC';
        $clc->enDiv = true;
        $clc->label = 'NUMERO CLC';

        $inputNumeroUR = new StdClass();
        $inputNumeroUR->id = 'numerour';
        $inputNumeroUR->name = 'numerour';
        $inputNumeroUR->placeholder = 'NUMERO UR';
        $inputNumeroUR->enDiv = true;
        $inputNumeroUR->label = 'NUMERO UR';
        $inputNumeroUR->formato = 'entero';


        $inputFolio = new StdClass();
        $inputFolio->id = 'folio';
        $inputFolio->name = 'folio';
        $inputFolio->placeholder = 'NUMERO FOLIO';
        $inputFolio->enDiv = true;
        $inputFolio->label = 'NUMERO FOLIO';

        $inputBeneficiario = new StdClass();
        $inputBeneficiario->id = 'beneficiario';
        $inputBeneficiario->name = 'beneficiario';
        $inputBeneficiario->placeholder = 'BENEFICIARIO';
        $inputBeneficiario->enDiv = true;
        $inputBeneficiario->label = 'BENEFICIARIO';

        $inputConcepto = new StdClass();
        $inputConcepto->id = 'concepto';
        $inputConcepto->name = 'concepto';
        $inputConcepto->placeholder = 'CONCEPTO';
        $inputConcepto->enDiv = true;
        $inputConcepto->label = 'CONCEPTO';

        $inputLote = new StdClass();
        $inputLote->id = 'lote';
        $inputLote->name = 'lote';
        $inputLote->placeholder = 'LOTE AUTORIZADO';
        $inputLote->enDiv = true;
        $inputLote->label = 'LOTE AUTORIZADO';

        $inputClaveBancaria = new StdClass();
        $inputClaveBancaria->sql = 'SELECT cbs_id,cbs_alias FROM ' . BD_TESORERIA . '.ban_CuentaBancariaSefin ';
        $inputClaveBancaria->id = 'claveBancaria';
        $inputClaveBancaria->name = 'claveBancaria';
        $inputClaveBancaria->placeholder = 'CTA ORIGEN';
        $inputClaveBancaria->enDiv = true;
        $inputClaveBancaria->label = 'CTA ORIGEN';

        $INPUTCuentaDestino = new StdClass();
        $INPUTCuentaDestino->id = 'cuentaDestino';
        $INPUTCuentaDestino->name = 'cuentaDestino';
        $INPUTCuentaDestino->placeholder = 'CTA DESTINO';
        $INPUTCuentaDestino->enDiv = true;
        $INPUTCuentaDestino->label = 'CTA DESTINO';

        $inputClaveFinanciamiento = new StdClass();
        $inputClaveFinanciamiento->sql = 'SELECT ecf_clave FROM  ' . BD_CTRL . '.enc_ClaveFinanciamiento';
        $inputClaveFinanciamiento->id = 'claveFinanciamiento';
        $inputClaveFinanciamiento->name = 'claveFinanciamiento';
        $inputClaveFinanciamiento->placeholder = 'CLAVE FINANCIAMIENTO';
        $inputClaveFinanciamiento->enDiv = true;
        $inputClaveFinanciamiento->label = 'CLAVE FINANCIAMIENTO';

        $inputLoteEgreso = new StdClass();
        $inputLoteEgreso->id = 'loteEgreso';
        $inputLoteEgreso->name = 'loteEgreso';
        $inputLoteEgreso->placeholder = 'LOTE EGRESO';
        $inputLoteEgreso->enDiv = true;
        $inputLoteEgreso->label = 'LOTE EGRESO';


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

        $inputNombreUR = new StdClass();
        $inputNombreUR->sql = "SELECT concat(gpo_id,ur_id),ur_nombre FROM " . BD_CTRL . ".cat_UnidadResponsable WHERE gpo_id>0";
        $inputNombreUR->id = 'nombreUR';
        $inputNombreUR->name = 'nombreUR';
        $inputNombreUR->placeholder = 'NOMBRE UR';
        $inputNombreUR->enDiv = true;
        $inputNombreUR->label = 'NOMBRE UR';

        $inputEstadoClc = new StdClass();
        $inputEstadoClc->sql = "SELECT ecl_id,ecl_nombre FROM " . BD_CTRL . ".cat_EstadoClc";
        $inputEstadoClc->id = 'estadoClc';
        $inputEstadoClc->name = 'estadoClc';
        $inputEstadoClc->placeholder = 'ESTADO CLC';
        $inputEstadoClc->enDiv = true;
        $inputEstadoClc->label = 'ESTADO CLC';

        $inputEstatal = new StdClass();
        $datos = array();
        array_push($datos, "TODOS");
        array_push($datos, "ESTATAL");
        array_push($datos, "FEDERAL");
        $inputEstatal->opciones = $datos;
        $inputEstatal->id = 'estatal';
        $inputEstatal->name = 'estatal';
        $inputEstatal->placeholder = 'ORIGEN';
        $inputEstatal->enDiv = true;
        $inputEstatal->label = 'ORIGEN';

        $inputRFC = new stdClass();
        $inputRFC->id = "rfc";
        $inputRFC->camposText = 'rfc_nombre,rfc_paterno,rfc_materno,rfc_ejercicio ';
        $inputRFC->required = true;
        $inputRFC->tabla = 'cat_Proveedor';
        $inputRFC->name = "control";
        $inputRFC->autocomplete = true;
        $inputRFC->placeholder = "rfc del proveedor";
        $inputRFC->campoVal = "rfc_id";
        $inputRFC->label = "R.F.C del proveedor";
        $inputRFC->desdecaracter = 4;
        $inputRFC->maxlength = 13;
        $inputRFC->enDiv  = true;
        $inputRFC->tablas = " INNER JOIN " . BD_SINPRES . ".cat_Proveedores USING ( rfc_id )";

        $inputProveedor = new stdClass();
        $inputProveedor->id = "proveedorNombre";
        $inputProveedor->camposText = 'rfc_nombre,rfc_paterno,rfc_materno,rfc_ejercicio ';
        $inputProveedor->required = true;
        $inputProveedor->tabla = 'cat_Proveedor';
        $inputProveedor->name = "proveedorNombre";
        $inputProveedor->autocomplete = true;
        $inputProveedor->placeholder = "nombre del proveedor";
        $inputProveedor->campoVal = "rfc_nombre";
        $inputProveedor->label = "Nombre del proveedor";
        $inputProveedor->desdecaracter = 4;
        $inputProveedor->maxlength = 13;
        $inputProveedor->enDiv  = true;
        $inputProveedor->tablas = " INNER JOIN " . BD_SINPRES . ".cat_Proveedores USING ( rfc_id )";

        $btnBuscar = new stdClass();
        $btnBuscar->id = "buscar";
        $btnBuscar->name = "buscar";
        $btnBuscar->value = "Buscar";
        $btnBuscar->class = "btn btn-outline-secondary me-1";
        $btnBuscar->icon = "fa fa-search me-2";
        $btnBuscar->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"pintaTablaConsultaCLC",
                documento:$("#documento option:selected").text().split(" ")[0].trim(),
                anio:$("#ejercicio option:selected").text().split(" ")[2].trim(), 
                clc:$("#clc").val(),
                folio:$("#folio").val(),
                numerour:$("#numerour").val(),
                importeMin:$("#importeMin").val(),
                importeMax:$("#importeMax").val(),
                inputFechaMin:$("#fechaMin").val(),
                inputFechaMax:$("#fechaMax").val(),
                beneficiario:$("#beneficiario").val(),
                concepto:$("#concepto").val(),
                lote:$("#lote").val(),
                claveBancaria:$("#claveBancaria").val(),
                claveFinanciamiento:$("#claveFinanciamiento").val(),
                loteEgreso:$("#loteEgreso").val(),
                cuentaDestino:$("#cuentaDestino").val(),
                nombreUR:$("#nombreUR").val(),
                estadoClc:$("#estadoClc").val(),
                estatal:$("#estatal").val(),
                rfc: $("#rfc").val()
            })';

        $btnLimpiarCampo = new stdClass();
        $btnLimpiarCampo->id = "limpiarCampos";
        $btnLimpiarCampo->name = "limpiarCampos";
        $btnLimpiarCampo->value = "limpiar Campos";
        $btnLimpiarCampo->click = '
            comunica("' . $this->pantalla . '",{
                metodo:"consultaClc",
            })';

        $this->mixScript('
        function printCmp() {
        var form = $("<form>")
        .attr("action", "rep/excel/scg/fondos/exp_ConsultaClcFondo.php")
        .attr("method", "post")
        .attr("target", "_blank")
        .attr("id", "enviaPost")
        .append($("<input>").attr("type", "hidden").attr("name", "documento").val($("#documento option:selected").text().split(" ")[0].trim()))
        .append($("<input>").attr("type", "hidden").attr("name", "ejercicio").val($("#ejercicio option:selected").text().split(" ")[2].trim()))
        .append($("<input>").attr("type", "hidden").attr("name", "clc").val($("#clc").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "folio").val($("#folio").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "numerour").val($("#numerour").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "importeMax").val($("#importeMax").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "importeMin").val($("#importeMin").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "fechaMax").val($("#fechaMax").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "fechaMin").val($("#fechaMin").val()))

        .append($("<input>").attr("type", "hidden").attr("name", "beneficiario").val($("#beneficiario").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "concepto").val($("#concepto").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "lote").val($("#lote").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "claveBancaria").val($("#claveBancaria").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "claveFinanciamiento").val($("#claveFinanciamiento").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "loteEgreso").val($("#loteEgreso").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "cuentaDestino").val($("#cuentaDestino").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "nombreUR").val($("#nombreUR").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "estadoClc").val($("#estadoClc").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "estatal").val($("#estatal").val()))
        .append($("<input>").attr("type", "hidden").attr("name", "rfc").val($("#rfc").val()))
        
        $("body").append(form);          
        form.submit();
                }
            ');

        //generamos el html con los elementos a utilizar y los tributos adecuados
        $html = '            
            <div class="row class="col-md-12 col-xs-12 "">
                <legend>CONSULTA ESTADO CLC
                </legend>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputEjercicio) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputDocumento) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($clc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputNumeroUR) . '
                </fieldset>
            </div>
            <div class="row class="col-md-12 col-xs-12 "">
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputFolio) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputBeneficiario) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputConcepto) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputLote) . '
                </fieldset>
            </div>
            <div class="row class="col-md-12 col-xs-12 "">
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputNombreUR) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputEstadoClc) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputEstatal) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputRFC) . '
                </fieldset>
                
            </div>
            <div class="row class="col-md-12 col-xs-12 "">
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputClaveBancaria) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($INPUTCuentaDestino) . '
                </fieldset>
                <fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponCombo($inputClaveFinanciamiento) . '
                </fieldset>
                <!--fieldset class="col-md-3 col-xs-3" id="">
                    ' . $this->ponInput($inputLoteEgreso) . '
                </fieldset-->
            </div>
            <div class="row class="col-md-12 col-xs-12 "">
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
                <fieldset class="col-md-2 col-xs-2" id="">
                    ' . $this->ponBoton($btnBuscar) . '
                </fieldset>
                
                </div>  
                <div class="row">
                <fieldset id="divTabla" class="col-md-12 col-xs-12">
                </fieldset>
            </div>  
        ';
        $this->mixHtml($html);

        if ($buscar && !empty($busqueda)) {
            $busquedaArr = explode('|', $busqueda);
            $anio = $busquedaArr[0];
            $documento = $busquedaArr[1];
            $clc = $busquedaArr[2];
            $numerour = $busquedaArr[3];
            $folio = $busquedaArr[4];
            $importeMin = $busquedaArr[5];
            $importeMax = $busquedaArr[6];
            $fechaMin = $busquedaArr[7];
            $fechaMax = $busquedaArr[8];
            $beneficiario = $busquedaArr[9];
            $concepto = $busquedaArr[10];
            $lote = $busquedaArr[11];
            $claveBancaria = $busquedaArr[12];
            $claveFinanciamiento = $busquedaArr[13];
            $loteEgreso = $busquedaArr[14];
            $cuentaDestino = $busquedaArr[15];
            $nombreUR = $busquedaArr[16];
            $estadoClc = $busquedaArr[17];
            $estatal = $busquedaArr[18];

            $this->mixScript('
                $("#anio").val("' . $anio . '");
                $("#documento").val("' . $documento . '");
                $("#clc").val("' . $clc . '");
                $("#numerour").val("' . $numerour . '");
                $("#folio").val("' . $folio . '");
                $("#importeMin").val("' . $importeMin . '");
                $("#importeMax").val("' . $importeMax . '");
                $("#fechaMin").val("' . $fechaMin . '");
                $("#fechaMax").val("' . $fechaMax . '");
                $("#beneficiario").val("' . $beneficiario . '");
                $("#concepto").val("' . $concepto . '");
                $("#lote").val("' . $lote . '");
                $("#claveBancaria").val("' . $claveBancaria . '");
                $("#claveFinanciamiento").val("' . $claveFinanciamiento . '");
                $("#loteEgreso").val("' . $loteEgreso . '");
                $("#cuentaDestino").val("' . $cuentaDestino . '");
                $("#nombreUR").val("' . $nombreUR . '");
                $("#estadoClc").val("' . $estadoClc . '");
                $("#estatal").val("' . $estatal . '");

                $(".selectpicker").selectpicker("refresh");
            ');
        }
    }

    private function pintaTablaConsultaCLC()
    {

        $anio = isset($this->prm->anio) ? $this->prm->anio : "";

        $this->setContenedor('divTabla');
        $tabla = new stdClass();

        $tabla->id = 'tbl-ConsultaClcs';
        $tabla->titulo = 'Listado de CLCs';
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaConsultaClc';
        $tabla->comunica->anio = '{$("#ejercicio option:selected").text().split(" ")[2].trim()}';
        $tabla->comunica->documento = '{$("#documento option:selected").text().split(" ")[0].trim()}';
        $tabla->comunica->clc = '{$("#clc").val()}';
        $tabla->comunica->numerour = '{$("#numerour").val()}';
        $tabla->comunica->folio = '{$("#folio").val()}';
        $tabla->comunica->importeMin = '{$("#importeMin").val()}';
        $tabla->comunica->importeMax = '{$("#importeMax").val()}';
        $tabla->comunica->inputFechaMin = '{$("#fechaMin").val()}';
        $tabla->comunica->inputFechaMax = '{$("#fechaMax").val()}';
        $tabla->comunica->beneficiario = '{$("#beneficiario").val()}';
        $tabla->comunica->concepto = '{$("#concepto").val()}';
        $tabla->comunica->lote = '{$("#lote").val()}';
        $tabla->comunica->claveBancaria = '{$("#claveBancaria").val()}';
        $tabla->comunica->claveFinanciamiento = '{$("#claveFinanciamiento").val()}';
        $tabla->comunica->loteEgreso = '{$("#loteEgreso").val()}';
        $tabla->comunica->cuentaDestino = '{$("#cuentaDestino").val()}';
        $tabla->comunica->nombreUR = '{$("#nombreUR").val()}';
        $tabla->comunica->estadoClc = '{$("#estadoClc").val()}';
        $tabla->comunica->estatal = '{$("#estatal").val()}';
        $tabla->comunica->rfc = '{$("#rfc").val()}';

        $tr = array();

        $th = new stdClass();
        $th->texto = 'DETALLE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA GENERACION';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA PAGO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA DE AUTORIZACIÓN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA GENERACIÓN CERE/CERECO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'UR';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CLC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FOLIO';
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
        $th->texto = 'CTA ORIGEN';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'BANCO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA DESTINO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'LOTE EGRESO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CVE.FIN.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'ESTADO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'MODO.';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'TIPO';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RESPONSABLE';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RFC';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CTA NOMBRE';
        array_push($tr, $th);

        //CUERPO DE LA TABLA
        $trbody = array();

        $td = new stdClass();
        $td->tipo = 'boton';
        $td->icono = 'fa fa-eye';
        $td->class = 'detalleClc center-block';
        $td->name = 'detalleClc';
        $td->attr = new stdClass();
        $td->attr->gpo_id = 'gpo_id';
        $td->attr->ur_id = 'ur_id';
        $td->attr->clc_id = 'clc_id';
        $td->attr->ejercicio = 'ejercicio';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lote_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_fechaElaboracion';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'lot_fechaPago';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bdm_fechaAutorizado';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_generacionCereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_cereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_beneficiario';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_concepto';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'iba_abreviatura';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bur_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'loe_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ecf_clave';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'estado';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'mpc_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'clc_tipoCereCo';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'ur_nombre';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'cbs_nombre';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;
        $this->mixHtml($this->wrPonTabla($tabla));

        $this->llenaTablaConsultaClc();
    }

    private function llenaTablaConsultaClc()
    {
        $sqlCtrlAnterior = "";
        $estado2018 = "";
        $anio = isset($this->prm->anio) ? $this->prm->anio : "";
        $estado = ($anio == APP_EJERCICIO) ? " WHERE C.ecl_id > 9 " : " WHERE ant.ecl_id > 9";
        $estadoCtrl2018 = 'AND ((C.ecl_id  BETWEEN 1 AND 9) OR (C.ecl_id  BETWEEN 16 AND 21))';
        $documento = isset($this->prm->documento) ? $this->prm->documento : "";
        $clc = isset($this->prm->clc) ? $this->prm->clc : "";
        $numerour = isset($this->prm->numerour) ? $this->prm->numerour : "";
        $folio = isset($this->prm->folio) ? $this->prm->folio : "";
        $importeMin = isset($this->prm->importeMin) ? $this->prm->importeMin : "";
        $importeMax = isset($this->prm->importeMax) ? $this->prm->importeMax : "";
        $fechaMin = isset($this->prm->inputFechaMin) ? $this->prm->inputFechaMin : "";
        $fechaMax = isset($this->prm->inputFechaMax) ? $this->prm->inputFechaMax : "";

        $beneficiario = isset($this->prm->beneficiario) ? $this->prm->beneficiario : "";
        $concepto = isset($this->prm->concepto) ? $this->prm->concepto : "";
        $lote = isset($this->prm->lote) ? $this->prm->lote : "";
        $claveBancaria = isset($this->prm->claveBancaria) ? $this->prm->claveBancaria : "";
        $claveFinanciamiento = isset($this->prm->claveFinanciamiento) ? $this->prm->claveFinanciamiento : "";
        $loteEgreso = isset($this->prm->loteEgreso) ? $this->prm->loteEgreso : "";
        $cuentaDestino = isset($this->prm->cuentaDestino) ? $this->prm->cuentaDestino : "";

        $nombreUR = isset($this->prm->nombreUR) ? $this->prm->nombreUR : "";
        $estadoClc = isset($this->prm->estadoClc) ? $this->prm->estadoClc : "";
        $estatal = isset($this->prm->estatal) ? $this->prm->estatal : "";
        $rfc = isset($this->prm->rfc) ? $this->prm->rfc : "";

        $busqueda = $anio . '|' . $documento . '|' . $clc . '|' . $numerour . '|' . $folio . '|' . $importeMin . '|' . $importeMax . '|' . $fechaMin . '|' . $fechaMax . '|' . $beneficiario . '|' . $concepto . '|' . $lote . '|' . $claveBancaria . '|' . $claveFinanciamiento . '|' . $loteEgreso . '|' . $cuentaDestino . '|' . $nombreUR . '|' . $estadoClc . '|' . $estatal . '|' . $rfc;

        $importeMax = trim($importeMax, " $");
        $importeMin = trim($importeMin, " $");
        $importeMax = str_replace(',', '', $importeMax);
        $importeMin = str_replace(',', '', $importeMin);

        $importeMin = (float)$importeMin;
        $importeMax = (float)$importeMax;
        $importeMin = $importeMin == 0 ? '' : "$importeMin";
        $importeMax = $importeMax == 0 ? '' : "$importeMax";

        if ($fechaMin != "") {
            $arrFecha = explode("/", $fechaMin);
            $fechaMin = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }
        if ($fechaMax != "") {
            $arrFecha = explode("/", $fechaMax);
            $fechaMax = $arrFecha[2] . "/" . $arrFecha[1] . "/" . $arrFecha[0];
        }

        $sqlExtra = "";

        if ($beneficiario != '') {
            $sqlExtra .= " AND C.clc_beneficiario like '%" . $beneficiario . "%' ";
        }

        if ($concepto != '') {
            $sqlExtra .= " AND C.clc_concepto like '%" . $concepto . "%' ";
        }
        if ($lote != '') {
            $nuevoLote = "";
            $tok = strtok($lote, ",");
            while ($tok !== false) {
                $nuevoLote .= "'" . $tok . "',";
                $tok = strtok(",");
            }
            $nuevoLote = substr($nuevoLote, 0, -1);
            $sqlExtra .= " AND CONCAT( IF ( m.fpa_id = 1, 'T', IF (m.fpa_id = 2, 'C', IF (m.fpa_id = 3, 'L', 'I')) ), LPAD(m.msc_id, 2, '0'), LPAD(m.lot_id, 4, '0'), SUBSTR(m.lot_ejercicio, 3) ) in (" . $nuevoLote . ") ";
        }
        if ($claveBancaria != '') {
            $sqlExtra .= " AND CB.cbs_id ='" . $claveBancaria . "' ";
        }
        if ($cuentaDestino != '') {
            $sqlExtra .= " AND C.bur_id ='" . $cuentaDestino . "' ";
        }
        if ($claveFinanciamiento != '') {
            if ($anio == 2017) {
                $sqlExtra .= " AND CV.ecf_clave ='" . $claveFinanciamiento . "' ";
            } else {
                $sqlExtra .= " AND CF.ecf_clave ='" . $claveFinanciamiento . "' ";
            }
        }
        if ($loteEgreso != '') {
            $sqlExtra .= " AND LE.loe_clave ='" . $loteEgreso . "' ";
        }
        if ($folio != '') {
            $sqlExtra .= " AND CX.clc_cereCo IN (" . $folio . ") ";
        }
        if ($importeMin != '' && $importeMax == '') {
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) >= $importeMin ";
        }
        if ($importeMin == '' && $importeMax != '') {
            $sqlExtra .= " AND (C.clc_percepcion - C.clc_retencion) <= $importeMax ";
        }
        if ($importeMin != '' && $importeMax != '') {
            $sqlExtra .= " AND ((C.clc_percepcion - C.clc_retencion) BETWEEN $importeMin AND $importeMax) ";
        }

        if ($fechaMin != '' && $fechaMax == '') {
            $sqlExtra .= " AND C.clc_fechaElaboracion >= '$fechaMin' ";
        }
        if ($fechaMin == '' && $fechaMax != '') {
            $sqlExtra .= " AND C.clc_fechaElaboracion <= '$fechaMax' ";
        }
        if ($fechaMin != '' && $fechaMax != '') {
            $sqlExtra .= " AND (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59') ";
            if ($anio != APP_EJERCICIO) {
                $sqlExtra .= " AND CF.ecf_clave ='" . $claveFinanciamiento . "' ";
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " (C.clc_fechaElaboracion BETWEEN '$fechaMin 00:00:00' AND '$fechaMax 23:59:59')";
            }
        }

        if ($clc != "") {
            $sqlExtra .= " AND C.clc_id in (" . $clc . ") ";
            if ($anio != APP_EJERCICIO) {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= " C.clc_id in (" . $clc . ") ";
            }
        }
        if ($numerour != "") {
            $sqlExtra .= " AND CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $numerour;
            if ($estadoClc == "") {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= ' CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $numerour;
            }
        }
        if ($documento != 0) {
            $sqlExtra .= " AND clc_tipoCereCo= " . $documento;
        }
        if ($nombreUR != "") {
            $sqlExtra .= " AND  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =" . $nombreUR;
            if ($anio != APP_EJERCICIO) {
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= ' CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $nombreUR;
            }
        }
        if ($estadoClc != "") {
            if ($anio == APP_EJERCICIO) {
                $sqlExtra .= " AND  C.ecl_id =" . $estadoClc;
                $estado = 'WHERE C.ecl_id = ' . $estadoClc;
            } else {
                $sqlCtrlAnterior = " AND IF (ant.ecl_id IS NOT NULL, ant.ecl_id, C.ecl_id) =" . $estadoClc;
                $estado = 'WHERE ant.ecl_id = ' . $estadoClc;
                $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                $estado2018 .= 'C.ecl_id =' . $estadoClc;
                if ($numerour != "") {
                    $estado2018 .= (strlen($estado2018) == 0) ? 'WHERE ' : ' AND ';
                    $estado2018 .= '  CONCAT(C.gpo_id, LPAD( C.ur_id,2,0)) =' . $numerour;
                }
            }
        }
        if ($rfc != "") {
            $sqlExtra .= " AND  doc.rfc_id='" . $rfc . "'";
        }
        switch ($estatal) {
            case 1:
                $sqlExtra .= " AND  CB.ter_id ='A'";
                break;
            case 2:
                $sqlExtra .= " AND  CB.ter_id ='B'";
                break;
        }
        //separamos la consulta en propuestas, lotes
        switch ($anio) {
            case 2017:
                //buscamos en propuesta
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,C.ur_id) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        C.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        m.lote_id AS lote_id,
                        CB.cbs_id,
                        CV.ecf_clave,
                        LE.loe_clave,
                        CB.cbs_nombre,
                        (IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        m.lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        m.bdm_fechaAutorizado
                    FROM (
                        SELECT scp.gpo_id, scp.ur_id, scp.clc_id, scp.bur_id,scp.mpc_id,ecf_id,
                            clc_percepcion, clc_retencion, clc_concepto, clc_fechaElaboracion, clc_tipoCereCo, clc_beneficiario,
                                
                            ecl_id,
                            MP.mpc_nombre
                        FROM " . BD_CTRL_2017 . ".scp_Clc scp         
                        INNER JOIN " . BD_CTRL_2017 . ".cat_ModoPagoClc MP using(mpc_id)
                        GROUP BY gpo_id, ur_id, clc_id
                    )C
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    Inner join (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2017 . ".scp_Presupuestal
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                            AND ant.ejr_id_clc = " . $anio . "
                                        union all 
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2017 . ".scp_Contable 
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                            AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, 
                                bdm_referencia,
                                bdm_fechaAutorizado
                        FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                        WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = " . $anio . "
                    UNION ALL
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, lot_id AS lote_id, 
                            lot_fechaPago,'' AS fpa_id, msc_id, lot_id, '' AS lot_ejercicio, 
                            bdm_referencia,
                            bdm_fechaAutorizado
                        FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria
                            LEFT JOIN " . BD_CTRL_2017 . ".tes_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                        WHERE bdm_estado = 'A'
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN using (ecf_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)                   
                    LEFT JOIN " . BD_CTRLANT . ".scp_Clc ant on (ant.gpo_id=C.gpo_id and ant.ur_id=C.ur_id and ant.clc_id = C.clc_id)
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . " 
                    " . $sqlExtra . " 
                    " . $sqlCtrlAnterior . " 
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id
                    HAVING bdm_fechaAutorizado !='' 

                    UNION
                    
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,C.ur_id) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        C.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2017 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        '' AS lote_id,
                        CB.cbs_id,
                        CV.ecf_clave,
                        LE.loe_clave,
                        CB.cbs_nombre,
                        (IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        '' AS lot_fechaPago,
                        doc.rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        m.bdm_fechaAutorizado
                    FROM (
                        SELECT scp.gpo_id, scp.ur_id, scp.clc_id, scp.bur_id,scp.mpc_id,ecf_id,
                            clc_percepcion, clc_retencion, clc_concepto, clc_fechaElaboracion, clc_tipoCereCo, clc_beneficiario,
                                
                            ecl_id,
                            MP.mpc_nombre
                        FROM " . BD_CTRL_2017 . ".scp_Clc scp         
                        INNER JOIN " . BD_CTRL_2017 . ".cat_ModoPagoClc MP using(mpc_id)
                        GROUP BY gpo_id, ur_id, clc_id
                    )C
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                    INNER JOIN (
                        select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2017 . ".scp_Presupuestal
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                            AND ant.ejr_id_clc = " . $anio . "
                                        union all 
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from " . BD_CTRL_2017 . ".scp_Contable 
                            INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                            AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                    LEFT JOIN (
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id,
                                bdm_referencia,
                                bdm_fechaAutorizado
                        FROM " . BD_CTRLANT . ".min_BancoDocumentoMinistrado                            
                        WHERE bdm_estado = 'A'
                            AND ejr_id_clc=" . $anio . "
                    UNION ALL
                        SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                            bdm_referencia,
                            bdm_fechaAutorizado
                        FROM " . BD_CTRL_2017 . ".tes_ClcLoteTesoreria
                            LEFT JOIN " . BD_CTRL_2017 . ".tes_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                        WHERE bdm_estado = 'A'
                    )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_DetalleLoteEgresos as DE using(gpo_id,ur_id,clc_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".scp_LoteEgresos as LE using(loe_dia,loe_mes,loe_anio,loe_cereCo,loe_id)
                    INNER JOIN " . BD_CTRL_2017 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_BancosSefinCveFin BN using (ecf_id)
                    LEFT JOIN " . BD_CTRL_2017 . ".enc_ClaveFinanciamiento CV using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB on (BN.bsf_id = CB.cbs_id)
                    LEFT JOIN " . BD_CTRLANT . ".scp_Clc ant on (ant.gpo_id=C.gpo_id and ant.ur_id=C.ur_id and ant.clc_id = C.clc_id)
                    WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . " 
                    " . $sqlExtra . " 
                    " . $sqlCtrlAnterior . " 
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id
                    ORDER BY gpo_id, ur_id, clc_id;";
                break;
            case 2018:
                $ctrlAnterior = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2018 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER join (
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            from " . BD_CTRL_2018 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            union all 
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            from " . BD_CTRL_2018 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        left join " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                $sqlCtrl2018 = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        MAX(IF (LOT.fpa_id is not null, CONCAT( IF ( LOT.fpa_id = 1, 'T', IF (LOT.fpa_id = 2, 'C', 'L') ), LPAD(LOT.msc_id, 2, '0'), LPAD(LOT.lot_id, 4, '0'), SUBSTR(LOT.lot_ejercicio, 3) ),'')) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(LOT.lot_fechaPago) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura
                    FROM " . BD_CTRL_2018 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRL_2018 . ".cat_EstadoClc EC on C.ecl_id = EC.ecl_id
                        INNER join (
                            select gpo_id,ur_id,clc_id,rfc_id from " . BD_CTRL_2018 . ".scp_Presupuestal
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                            " . $estado2018 . "                            
                                    union all 
                            select gpo_id,ur_id,clc_id,rfc_id from " . BD_CTRL_2018 . ".scp_Contable 
                            INNER JOIN " . BD_CTRL_2018 . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                            " . $estado2018 . "
                            
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2018 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2018 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2018 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                        LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 
                        " . $estadoCtrl2018 . "                        
                        " . $sqlExtra . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id";
                $sql = "                        
                    " . $ctrlAnterior . "                    
                ";
                break;
            case 2019:
                $ctrlAnterior = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2018 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(LOT.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                    FROM " . BD_CTRL_2019 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2019 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            from " . BD_CTRL_2019 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            union all 
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            from " . BD_CTRL_2019 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2019 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia, bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2019 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2019 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2019 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                        LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_fechaAutorizado";
                $sql = "                        
                    " . $ctrlAnterior . "                    
                ";
                break;
            case 2020:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2020 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2020 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2020 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2020 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2020 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2020 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2020 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2020 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2020 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2020 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            case 2021:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2021 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2021 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2021 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2021 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2021 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2021 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2021 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2021 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2021 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2021 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            case 2022:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2022 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2022 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2022 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2022 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2022 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2022 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2022 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2022 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2022 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            case 2023:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2023 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2023 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2023 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2023 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2023 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2023 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2023 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            case 2024:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2024 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2024 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2024 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2024 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2024 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2024 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2024 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2024 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2024 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            case 2025:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,
                        IF (ant.ecl_id IS NULL,
                            (CONCAT(C.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = C.ecl_id))),
                            (CONCAT(ant.ecl_id, ' - ',(SELECT ecl_nombre FROM " . BD_CTRL_2025 . ".cat_EstadoClc WHERE ecl_id = ant.ecl_id)))
                        ) AS estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(DATE_FORMAT(m.lot_fechaPago,'%d-%m-%Y')) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM " . BD_CTRL_2025 . ".scp_Clc C 
                        INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                        INNER JOIN " . BD_CTRL_2025 . ".cat_EstadoClc EC on ant.ecl_id = EC.ecl_id
                        INNER JOIN (
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2025 . ".scp_Presupuestal
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                            UNION ALL 
                            SELECT gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id 
                            FROM " . BD_CTRL_2025 . ".scp_Contable 
                                INNER JOIN " . BD_CTRLANT . ".scp_Clc ant USING(gpo_id,ur_id,clc_id)
                                " . $estado . "
                                AND ant.ejr_id_clc = " . $anio . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN " . BD_CTRL_2025 . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                    LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                    LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,
                                    bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                                LEFT JOIN " . BD_CTRLANT . ".min_BancoDocumentoMinistrado USING(gpo_id, ur_id, clc_id)
                                LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . " )
                            WHERE bdm_estado = 'A'
                                AND LO.lot_ejercicio = " . $anio . " AND ejr_id_clc = " . $anio . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                        INNER JOIN " . BD_CTRL_2025 . ".cat_ModoPagoClc MP using(mpc_id)
                        INNER JOIN " . BD_CTRL_2025 . ".enc_ClaveFinanciamiento CF using(ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                        LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                        LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                        INNER JOIN " . BD_CTRL_2025 . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                        WHERE C.clc_id > 0 AND ant.ejr_id_clc = " . $anio . "
                        " . $sqlExtra . "
                        " . $sqlCtrlAnterior . "
                        GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.fpa_id, m.msc_id, m.lot_ejercicio, m.lot_id, m.bdm_referencia, CB.cbs_id, m.bdm_fechaAutorizado";
                break;
            default:
                $sql = "
                    SELECT 
                        C.gpo_id,C.ur_id,C.clc_id,C.bur_id,
                        CASE
                            WHEN C.clc_percepcion > C.clc_retencion THEN (C.clc_percepcion - C.clc_retencion) 
                            WHEN C.clc_percepcion < C.clc_retencion THEN  '0'
                            WHEN C.clc_percepcion = C.clc_retencion THEN  '0'
                        END AS importe,C.clc_concepto,
                        CX.clc_cereCo ,
                        CONCAT(C.gpo_id,LPAD(C.ur_id,2,0)) AS ur,
                        DATE_FORMAT(C.clc_fechaElaboracion,'%d-%m-%Y')clc_fechaElaboracion,
                        MP.mpc_nombre ,
                        if (C.clc_tipoCereCo=1,'CE','CO' ) AS clc_tipoCereCo,
                        C.clc_beneficiario,                       
                        CONCAT(EC.ecl_id,'-',EC.ecl_nombre) as estado,
                        MAX(m.lote_id) AS lote_id,
                        MAX(CB.cbs_id) AS cbs_id,
                        CF.ecf_clave,
                        '' AS loe_clave,
                        MAX(IF (CB.cbs_nombre is null,'SIN CUENTA',cbs_nombre))AS cbs_nombre,
                        UR.ur_nombre,
                        MAX(LOT.lot_fechaPago) AS lot_fechaPago,
                        MAX(doc.rfc_id) AS rfc_id,
                        YEAR(C.clc_fechaElaboracion) AS ejercicio,
                        DATE_FORMAT(CX.clc_generacionCereCo,'%d-%m-%Y')clc_generacionCereCo,
                        MAX(iba_abreviatura) AS iba_abreviatura,
                        m.bdm_fechaAutorizado
                        FROM ctrl" . $anio . ".scp_Clc C 
                        INNER JOIN ctrl" . $anio . ".cat_EstadoClc EC on C.ecl_id = EC.ecl_id
                        LEFT JOIN (
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from ctrl" . $anio . ".scp_Presupuestal
                            INNER JOIN ctrl" . $anio . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                                union all 
                            select gpo_id,ur_id,clc_id, afe_id, bdo_id, rfc_id from ctrl" . $anio . ".scp_Contable 
                            INNER JOIN ctrl" . $anio . ".scp_Clc C USING(gpo_id,ur_id,clc_id)
                            " . $estado . "
                        )as doc using (gpo_id,ur_id,clc_id)
                        LEFT JOIN ctrl" . $anio . ".scp_ClcAuxiliar CX using(gpo_id,ur_id,clc_id)
                        LEFT JOIN (
                            SELECT gpo_id, ur_id, clc_id, afe_id, bdo_id, 
                                CONCAT(CASE LO.fpa_id WHEN 1 THEN 'T' WHEN 2 THEN 'C' ELSE 'L' END, LPAD(LO.msc_id, 2, '0'), 
                                LPAD(LO.lot_id, 4, '0'), SUBSTR(LO.lot_ejercicio, 3)) AS lote_id,
                                LOT.lot_fechaPago, LO.fpa_id, LO.msc_id, LO.lot_id, LO.lot_ejercicio, bdm_referencia,bdm_fechaAutorizado
                            FROM " . BD_TESORERIA . ".lot_LoteClc LO
                            LEFT JOIN " . BD_TESORERIA . ".min_BancoDocumentoMinistrado_2026 USING(gpo_id, ur_id, clc_id)
                            LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT ON (LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio AND LOT.lot_ejr = " . APP_EJERCICIO . ")
                            WHERE bdm_estado = 'A'
                            AND LO.lot_ejercicio = " . APP_EJERCICIO . "
                        )m USING(gpo_id, ur_id, clc_id, afe_id, bdo_id)
                    INNER JOIN " . BD_CTRL . ".cat_ModoPagoClc MP using(mpc_id)
                    INNER JOIN " . BD_CTRL . ".enc_ClaveFinanciamiento CF using(ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".vin_SefinClaveFinanciamiento  using (ecf_id)
                    LEFT JOIN " . BD_TESORERIA . ".ban_CuentaBancariaSefin CB using (cbs_id)
                    LEFT JOIN " . BD_TESORERIA . ".cat_InstitucionBancaria ib using (iba_id)
                    INNER JOIN " . BD_CTRL . ".cat_UnidadResponsable UR using(gpo_id,ur_id)
                    LEFT JOIN " . BD_TESORERIA . ".lot_LoteClc LO ON UR.gpo_id=LO.gpo_id AND UR.ur_id=LO.ur_id AND C.clc_id=LO.clc_id AND YEAR(C.clc_fechaElaboracion)=LO.lot_ejercicio
                    LEFT JOIN " . BD_TESORERIA . ".lot_Lote LOT on LO.fpa_id=LOT.fpa_id and LO.msc_id=LOT.msc_id and LO.lot_id=LOT.lot_id and LO.lot_ejercicio=LOT.lot_ejercicio and LOT.lot_ejr = " . APP_EJERCICIO . "
                    WHERE C.clc_id > 0 
                    " . $sqlExtra . "
                    GROUP BY C.gpo_id, C.ur_id, C.clc_id, m.bdm_fechaAutorizado";
                break;
        }
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->idTabla = "tbl-ConsultaClcs";
        $nav->nf = isset($this->prm->nav->nf) ? $this->prm->nav->nf : 50;
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $(".detalleClc").attr("busqueda", "' . $busqueda . '");
            $(".detalleClc").click(function() {
                comunica("' . $this->pantalla . '",{
                    metodo: "pintaTablaDetalleClc",
                    gpo_id: $(this).attr("gpo_id"),
                    ur_id: $(this).attr("ur_id"),
                    clc_id: $(this).attr("clc_id"),
                    ejercicio: $(this).attr("ejercicio"),
                    busqueda: $(this).attr("busqueda")
                });
            });
        ');
    }

    private function pintaTablaDetalleClc()
    {
        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : '';
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : '';
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';
        $this->prm->busqueda = isset($this->prm->busqueda) ? $this->prm->busqueda : '';

        if (empty($this->prm->gpo_id) || empty($this->prm->ur_id) || empty($this->prm->clc_id) || empty($this->prm->ejercicio)) {
            $this->mixScript('
                notify("Faltan datos", "Error");
            ');
            return;
        }

        $btnRegresarIndex = new stdClass();
        $btnRegresarIndex->id = 'btnregresarindex';
        $btnRegresarIndex->name = 'btnregresarindex';
        $btnRegresarIndex->text = 'Regresar';
        $btnRegresarIndex->value = '&nbsp;&nbsp;Regresar';
        $btnRegresarIndex->type = 'button';
        $btnRegresarIndex->icon = 'fa-solid fa-circle-left me-1';
        $btnRegresarIndex->class = 'btn btn-outline-secondary me-2';
        $btnRegresarIndex->click = '
            $("#toolsPantalla").html("");
            comunica("' . $this->pantalla . '",{
                metodo: "consultaClc",
                buscar: true,
                busqueda: "' . $this->prm->busqueda . '"
            });
        ';

        $btnReporteExcel = new stdClass();
        $btnReporteExcel->id = 'btnReporteExcel';
        $btnReporteExcel->name = 'btnReporteExcel';
        $btnReporteExcel->text = 'Exportar Excel';
        $btnReporteExcel->value = '&nbsp;Excel';
        $btnReporteExcel->type = 'button';
        $btnReporteExcel->class = 'btn-info';
        $btnReporteExcel->icon = 'glyphicon glyphicon-list';

        $this->agregaTools($this->ponBoton($btnRegresarIndex));

        /**
         * TABLA
         */
        $tabla = new stdClass();

        $tabla->id = 'tablaDetalleClc';
        $tabla->titulo = 'DETALLE DE LA CLC ' . $this->prm->clc_id;
        $tabla->comunica = new stdClass();
        $tabla->comunica->pantalla = $this->pantalla;
        $tabla->comunica->metodo = 'llenaTablaDetalleClc';
        $tabla->comunica->gpo_id = $this->prm->gpo_id;
        $tabla->comunica->ur_id = $this->prm->ur_id;
        $tabla->comunica->clc_id = $this->prm->clc_id;
        $tabla->comunica->ejercicio = $this->prm->ejercicio;

        /**
         * CABECERA DE LA TABLA
         */
        $tr = array();

        $th = new stdClass();
        $th->texto = 'AFECTACION';
        $th->width = '4rem';
        $th->align = 'center';
        //array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'DOCUMENTO';
        $th->width = '4rem';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'CONCEPTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'REFERENCIA';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'FECHA DOCUMENTO';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'R.F.C PROVEEDOR';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'NOMBRE PROVEEDOR';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'IMPORTE';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'PERCEPCIÓN';
        $th->align = 'center';
        array_push($tr, $th);

        $th = new stdClass();
        $th->texto = 'RETENCIÓN';
        $th->align = 'center';
        array_push($tr, $th);

        /**
         * CUERPO DE LA TABLA
         */
        $trbody = array();

        $td = new stdClass();
        $td->campoTxt = 'afe_id';
        $td->align = 'center';
        //array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'bdo_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'concepto';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'referencia';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'fechaReferencia';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'rfc_id';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'proveedor';
        $td->align = 'center';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'importe';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'percepcion';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $td = new stdClass();
        $td->campoTxt = 'retencion';
        $td->align = 'right';
        $td->formato = 'moneda';
        array_push($trbody, $td);

        $tabla->encabezado = array($tr);
        $tabla->cuerpo = $trbody;

        $this->mixHtml($this->wrPonTabla($tabla));
        $this->mixScript('$("#btnregresarindex").removeClass("btn-default");');

        $this->llenaTablaDetalleClc();
    }

    private function llenaTablaDetalleClc()
    {

        $this->prm->gpo_id = isset($this->prm->gpo_id) ? $this->prm->gpo_id : '';
        $this->prm->ur_id = isset($this->prm->ur_id) ? $this->prm->ur_id : '';
        $this->prm->clc_id = isset($this->prm->clc_id) ? $this->prm->clc_id : '';
        $this->prm->ejercicio = isset($this->prm->ejercicio) ? $this->prm->ejercicio : '';

        if (empty($this->prm->gpo_id) || empty($this->prm->ur_id) || empty($this->prm->clc_id) || empty($this->prm->ejercicio)) {
            $this->mixScript('
                notify("Faltan datos", "Error");
            ');
            return;
        }

        $this->conectar();
        $sql = '
            SELECT s.gpo_id, s.ur_id, s.clc_id, s.bdo_id, max(s.afe_id)afe_id, max(s.id)id, max(s.concepto)concepto, max(s.referencia)referencia, max(s.fechaReferencia)fechaReferencia, s.rfc_id,
                max(s.importe)importe, max(s.iva)iva, max(s.neto)neto, 
                max(s.cuenta)cuenta, max(s.proveedor)proveedor, max(s.bdo_referencia)bdo_referencia
            FROM (';

        switch ($this->prm->ejercicio) {
            case 2017:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2017 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2017 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2017 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2017 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2017 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2017 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2018:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2018 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2018 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2018 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2018 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2018 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2018 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2019:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2019 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2019 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2019 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2019 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2019 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2019 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2020:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2020 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2020 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2020 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2020 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2020 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2020 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2021:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2021 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2021 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2021 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2021 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2021 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2021 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2022:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2022 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2022 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2022 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2022 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2022 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2022 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2023:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2023 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2023 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2023 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2023 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2023 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2023 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2024:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, (CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2024 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2024 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2024 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2024 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2024 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2024 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            case 2025:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS importe, 0.00 AS iva, (CAST(ncc_percepcion AS SIGNED) - CAST(ncc_retencion AS SIGNED)) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2025 . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL_2025 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2025 . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL_2025 . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL_2025 . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL_2025 . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
            default:
                $sql .= '
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncc_id AS id,
                        ncc_concepto AS concepto, ncc_referencia AS referencia,
                        ncc_fechaReferencia AS fechaReferencia,
                        (ncc_percepcion - ncc_retencion) AS importe, 0.00 AS iva, (ncc_percepcion - ncc_retencion) AS neto,
                        cta_cuenta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL . '.scp_Contable AS c
                    INNER JOIN ' . BD_CTRL . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL . '.cg_PlanCuentas USING (cta_gpo_id, cta_ur_id, cta_ue_id, ger_id, cgp_id, rub_id, cgcu_id, cgsc_id, cgc_id, cta_id)
                    UNION ALL
                    SELECT c.gpo_id, c.ur_id, c.clc_id, bdo_id, afe_id, rfc_id, ncp_id AS id,
                        ncp_concepto AS concepto, ncp_referencia AS referencia,
                        ncp_fechaReferencia AS fechaReferencia,
                        ncp_percepcionBruto AS importe, ncp_percepcionIva AS iva, ncp_percepcionNeto AS neto,
                        pre_claveCompleta AS cuenta,
                        CONCAT(p.rfc_nombre, " ", p.rfc_paterno, " ", p.rfc_materno) AS proveedor,
                        "" AS bdo_referencia
                    FROM ' . BD_CTRL . '.scp_Presupuestal AS c
                    INNER JOIN ' . BD_CTRL . '.cat_Proveedor USING (rfc_id)
                    INNER JOIN ' . BD_SINPRES . '.cat_Proveedores AS p USING (rfc_id)
                    INNER JOIN ' . BD_CTRL . '.presupuesto USING (gpo_id, ur_id, ue_id, epg_id, pg_id, pe_id, ecf_id, tcp_id)';
                break;
        }

        $sql .= '
            ) AS s
            WHERE gpo_id = ?
            AND ur_id = ?
            AND clc_id = ?
            GROUP BY s.bdo_id, s.rfc_id
            ORDER BY afe_id, id';

        $paraDetalleClc = [$this->prm->gpo_id, $this->prm->ur_id, $this->prm->clc_id];
        $this->setParams($paraDetalleClc);
        $this->setSql($sql);
        $nav = isset($this->prm->nav) ? $this->prm->nav : new stdClass();
        $nav->nf = -1;
        $nav->idTabla = "tablaDetalleClc";
        $this->wrLlenaTabla($nav);

        $this->mixScript('
            $("#btnReporteExcel").click(function() {
                imprimeReporteClc();
            });

            function imprimeReporteClc() {
                var form = $("<form>")
                        .attr("action", "rep/excel/scg/fondos/exp_ConsultaClcFondoDetalleClc.php")
                        .attr("method", "post").attr("target", "_blank")
                        .attr("id", "formDetLote")
                    .append($("<input>")
                        .attr("type", "hidden")
                        .attr("name", "gpo_id")
                        .val("' . $this->prm->gpo_id . '"))
                    .append($("<input>")
                        .attr("type", "hidden")
                        .attr("name", "ur_id")
                        .val("' . $this->prm->ur_id . '"))
                    .append($("<input>")
                        .attr("type", "hidden")
                        .attr("name", "clc_id").
                        val("' . $this->prm->clc_id . '"))
                    .append($("<input>")
                        .attr("type", "hidden")
                        .attr("name", "ejercicio").
                        val("' . $this->prm->ejercicio . '"))
                    $("body").append(form);
                    form.submit();
                    form.remove();
            }
        ');
    }
}

if (isset($_SESSION[_S]['prm']['C']['12']['12']['1'])) {
    new ControladorConsultaClcsFondo();
} else {
    $error = new WebResponse();
    $error->sinPermisos();
}
